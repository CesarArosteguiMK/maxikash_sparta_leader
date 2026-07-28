<?php

namespace Services;

use Core\Database;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Interprets spreadsheet uploads for Leonidas. It never writes during preview:
 * the file is parsed again and its hash is verified immediately before apply.
 */
final class LeonidasSpreadsheetService
{
    private const SESSION_KEY = 'leonidas_spreadsheet_uploads';
    private const TTL = 1800;
    private const MAX_BYTES = 10_485_760;
    private const MAX_ROWS = 2500;

    /** @var array<string,callable> */
    private array $adapters;

    public function __construct(array $adapters = [])
    {
        $defaults = [
            'personas' => static function (): array {
                $db = new Database();
                return $db->queryAll("SELECT id, numero_empleado, codigo_contpac, curp, estatus,
                    TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) nombre_completo
                    FROM estado_cuenta.persona");
            },
            'estructura_importar' => static fn(array $filas, int $actorId, bool $aplicar): array =>
                \Models\CapHum::importarCambiosEstructuraPorExternalId($filas, $actorId, $aplicar),
            'salarios_importar' => static fn(array $filas, int $actorId): array =>
                \Models\CapHum::importarSalariosSensiblesPorCurp($filas, $actorId),
        ];
        $this->adapters = $adapters + $defaults;
    }

    public function guardarCarga(array $archivo, int $actorId): array
    {
        $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException($this->mensajeErrorCarga($error));
        }
        $tmp = (string) ($archivo['tmp_name'] ?? '');
        $nombre = trim((string) ($archivo['name'] ?? 'archivo.xlsx'));
        $size = (int) ($archivo['size'] ?? 0);
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls'], true)) {
            throw new \InvalidArgumentException('El adjunto debe ser un archivo Excel .xlsx o .xls.');
        }
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new \InvalidArgumentException('El Excel esta vacio o supera el limite de 10 MB.');
        }

        $directorio = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta_leonidas';
        if (!is_dir($directorio) && !mkdir($directorio, 0700, true) && !is_dir($directorio)) {
            throw new \RuntimeException('No se pudo preparar el almacenamiento temporal del Excel.');
        }
        $token = bin2hex(random_bytes(18));
        $destino = $directorio . DIRECTORY_SEPARATOR . $token . '.' . $extension;
        if (!move_uploaded_file($tmp, $destino)) {
            throw new \RuntimeException('No se pudo guardar temporalmente el Excel.');
        }
        $cargas = $this->cargasVigentes($actorId);
        $cargas[$token] = [
            'actor_id' => $actorId,
            'nombre' => $nombre,
            'ruta' => $destino,
            'hash' => hash_file('sha256', $destino),
            'expira_en' => time() + self::TTL,
        ];
        $_SESSION[self::SESSION_KEY] = $cargas;
        return ['token' => $token, 'nombre' => $nombre, 'expira_en' => $cargas[$token]['expira_en']];
    }

    /**
     * Registra para análisis un Excel ya validado por LeonidasAttachmentService.
     * No mueve ni duplica el archivo; conserva el mismo token y hash.
     */
    public function adoptarCargaOperativa(string $token, array $meta, int $actorId): void
    {
        $extension = strtolower((string) ($meta['extension'] ?? ''));
        $ruta = (string) ($meta['ruta'] ?? '');
        if (!in_array($extension, ['xlsx', 'xls'], true) || !is_file($ruta)) {
            throw new \InvalidArgumentException('El adjunto no es un Excel compatible.');
        }
        if ((int) ($meta['actor_id'] ?? 0) !== $actorId) {
            throw new \RuntimeException('El Excel pertenece a otra sesión.');
        }
        $hash = (string) ($meta['hash'] ?? '');
        if ($hash === '' || !hash_equals($hash, (string) hash_file('sha256', $ruta))) {
            throw new \RuntimeException('El Excel cambió después de su validación.');
        }
        $cargas = $this->cargasVigentes($actorId);
        $cargas[$token] = [
            'actor_id' => $actorId,
            'nombre' => (string) ($meta['nombre'] ?? basename($ruta)),
            'ruta' => $ruta,
            'hash' => $hash,
            'expira_en' => min((int) ($meta['expira_en'] ?? time() + self::TTL), time() + self::TTL),
        ];
        $_SESSION[self::SESSION_KEY] = $cargas;
    }

    public function analizar(string $token, string $mensaje, array $contexto): array
    {
        $meta = $this->carga($token, (int) ($contexto['actor_id'] ?? 0));
        $operacion = $this->detectarOperacion($mensaje, $meta['ruta']);
        $analisis = $operacion === 'salarios'
            ? $this->analizarSalarios($meta, $contexto)
            : $this->analizarEstructura($meta, $contexto);

        $filasError = array_values(array_filter($analisis['filas'], static fn(array $fila): bool => ($fila['estado'] ?? '') === 'error'));
        $filasListas = array_values(array_filter($analisis['filas'], static fn(array $fila): bool => ($fila['estado'] ?? '') !== 'error'));
        $total = count($analisis['filas']);
        $errores = count($filasError);
        $mensajeRespuesta = sprintf(
            'Revise %d fila(s) de %s: %d lista(s) y %d con problema(s).',
            $total,
            $meta['nombre'],
            count($filasListas),
            $errores
        );
        if ($errores > 0) {
            $mensajeRespuesta .= ' No aplique ningun cambio. Corrige las filas indicadas y vuelve a adjuntar el archivo.';
        } elseif ($total === 0) {
            $mensajeRespuesta .= ' El archivo no contiene registros para procesar.';
        } else {
            $mensajeRespuesta .= $operacion === 'salarios'
                ? ' La actualizacion de salarios requiere confirmacion y Google Authenticator vigente.'
                : ' La estructura puede aplicarse de forma transaccional despues de tu confirmacion.';
        }

        $respuesta = [
            'mensaje' => $mensajeRespuesta,
            'tipo' => 'excel_prevalidacion',
            'fuente' => 'excel',
            'reporte' => [
                'titulo' => 'Diagnostico de ' . ($operacion === 'salarios' ? 'salarios' : 'estructura'),
                'total' => $total,
                'filas' => array_map([$this, 'filaReporte'], $analisis['filas']),
            ],
        ];
        if ($errores === 0 && $total > 0) {
            $respuesta['propuesta_especificacion'] = [
                'accion' => 'excel_aplicar',
                'resumen' => sprintf('Aplicar %d cambio(s) de %s desde %s', $total, $operacion, $meta['nombre']),
                'payload' => [
                    'archivo_token' => $token,
                    'operacion' => $operacion,
                    'hash' => $meta['hash'],
                ],
            ];
        }
        return $respuesta;
    }

    public function ejecutar(array $payload, array $contexto): array
    {
        $token = trim((string) ($payload['archivo_token'] ?? ''));
        $operacion = (string) ($payload['operacion'] ?? '');
        $meta = $this->carga($token, (int) ($contexto['actor_id'] ?? 0));
        if (!hash_equals((string) ($payload['hash'] ?? ''), (string) $meta['hash'])
            || !hash_equals((string) $meta['hash'], (string) hash_file('sha256', $meta['ruta']))) {
            throw new \RuntimeException('El Excel cambio despues de la vista previa. Adjuntalo nuevamente.');
        }
        if ($operacion === 'salarios') {
            $this->exigirPermiso($contexto, 'salarios');
            if (empty($contexto['salario_totp_vigente'])) {
                throw new \RuntimeException('Desbloquea salarios con Google Authenticator y vuelve a confirmar la carga.');
            }
            $analisis = $this->analizarSalarios($meta, $contexto);
            $this->exigirSinErrores($analisis);
            $resultado = ($this->adapters['salarios_importar'])($analisis['canonicas'], (int) $contexto['actor_id']);
        } else {
            $this->exigirPermiso($contexto, 'estructura');
            $analisis = $this->analizarEstructura($meta, $contexto);
            $this->exigirSinErrores($analisis);
            $resultado = ($this->adapters['estructura_importar'])($analisis['canonicas'], (int) $contexto['actor_id'], true);
        }
        if (empty($resultado['success'])) {
            throw new \RuntimeException((string) ($resultado['mensaje'] ?? 'No se pudo aplicar el Excel.'));
        }
        unset($_SESSION[self::SESSION_KEY][$token]);
        @unlink($meta['ruta']);
        return [
            'mensaje' => $operacion === 'salarios'
                ? 'Listo. Los salarios del Excel fueron actualizados, cifrados y auditados.'
                : 'Listo. La estructura del Excel fue actualizada y auditada.',
            'tipo' => 'agente_ejecucion_exitosa',
            'ejecucion' => ['accion' => 'excel_aplicar', 'datos' => $resultado['datos'] ?? null],
        ];
    }

    private function analizarEstructura(array $meta, array $contexto): array
    {
        $this->exigirPermiso($contexto, 'estructura');
        [$cabeceras, $datos] = $this->leer($meta['ruta']);
        $this->exigirColumnas($cabeceras, ['departamento'], 'estructura');
        if (!$this->tieneAlguna($cabeceras, ['puesto_legacy', 'puesto'])) {
            throw new \InvalidArgumentException('El Excel de estructura no contiene una columna puesto_legacy o puesto.');
        }
        $personas = $this->indicePersonas();
        $filas = [];
        $canonicas = [];
        foreach ($datos as $dato) {
            $resolucion = $this->resolverPersona($dato, $personas);
            if (!$resolucion['persona']) {
                $filas[] = ['fila' => $dato['_fila'], 'estado' => 'error', 'nombre' => $dato['nombre_completo'] ?? '', 'detalle' => $resolucion['error']];
                continue;
            }
            $persona = $resolucion['persona'];
            $canonica = [
                'fila' => $dato['_fila'],
                'external_id' => (string) ($persona['numero_empleado'] ?? ''),
                'nombre_completo' => $persona['nombre_completo'],
                'puesto_legacy' => trim((string) ($dato['puesto_legacy'] ?? $dato['puesto'] ?? '')),
                'departamento' => trim((string) ($dato['departamento'] ?? '')),
                'supervisor' => trim((string) ($dato['supervisor'] ?? '')),
                'subgerente' => trim((string) ($dato['subgerente'] ?? '')),
                'gerente' => trim((string) ($dato['gerente'] ?? '')),
            ];
            if ($canonica['external_id'] === '') {
                $filas[] = ['fila' => $dato['_fila'], 'estado' => 'error', 'nombre' => $persona['nombre_completo'], 'detalle' => 'La persona no tiene numero_empleado; no se usa codigo_contpac como sustituto.'];
                continue;
            }
            $canonicas[] = $canonica;
            $filas[] = ['fila' => $dato['_fila'], 'estado' => 'listo', 'nombre' => $persona['nombre_completo'], 'detalle' => 'Coincidencia unica; estructura lista para prevalidar.'];
        }
        if ($canonicas) {
            $vista = ($this->adapters['estructura_importar'])($canonicas, (int) $contexto['actor_id'], false);
            $plan = is_array($vista['datos'] ?? null) ? $vista['datos'] : [];
            foreach (($plan['detalles'] ?? []) as $detalle) {
                $estadoDetalle = (string) ($detalle['estado'] ?? '');
                if (!in_array($estadoDetalle, ['error', 'omitido'], true)) continue;
                $numeroFila = (int) ($detalle['fila'] ?? 0);
                $motivos = $detalle['errores']
                    ?? $detalle['mensajes']
                    ?? $detalle['avisos']
                    ?? ['La estructura no pudo validarse.'];
                foreach ($filas as &$fila) {
                    if ((int) $fila['fila'] === $numeroFila) {
                        $fila['estado'] = 'error';
                        $prefijo = $estadoDetalle === 'omitido' ? 'La fila no es aplicable. ' : '';
                        $fila['detalle'] = $prefijo . implode(' ', array_map('strval', (array) $motivos));
                    }
                }
                unset($fila);
            }
            if (empty($vista['success'])) {
                throw new \RuntimeException((string) ($vista['mensaje'] ?? 'No se pudo prevalidar la estructura.'));
            }
        }
        return ['filas' => $filas, 'canonicas' => $canonicas];
    }

    private function analizarSalarios(array $meta, array $contexto): array
    {
        $this->exigirPermiso($contexto, 'salarios');
        [$cabeceras, $datos] = $this->leer($meta['ruta']);
        if (!$this->tieneAlguna($cabeceras, ['salario', 'sueldo', 'salario_mensual', 'sueldo_mensual'])) {
            throw new \InvalidArgumentException('No encontre una columna de salario o sueldo en el Excel.');
        }
        $personas = $this->indicePersonas();
        $filas = [];
        $canonicas = [];
        foreach ($datos as $dato) {
            $resolucion = $this->resolverPersona($dato, $personas);
            if (!$resolucion['persona']) {
                $filas[] = ['fila' => $dato['_fila'], 'estado' => 'error', 'nombre' => $dato['nombre_completo'] ?? '', 'detalle' => $resolucion['error']];
                continue;
            }
            $persona = $resolucion['persona'];
            $salario = $dato['salario'] ?? $dato['sueldo'] ?? $dato['salario_mensual'] ?? $dato['sueldo_mensual'] ?? null;
            $normalizado = $this->salario($salario);
            if ($normalizado === null || trim((string) ($persona['curp'] ?? '')) === '') {
                $filas[] = ['fila' => $dato['_fila'], 'estado' => 'error', 'nombre' => $persona['nombre_completo'], 'detalle' => $normalizado === null ? 'Salario vacio o invalido.' : 'La persona no tiene CURP; el salario no puede vincularse de forma segura.'];
                continue;
            }
            $canonicas[] = ['fila' => $dato['_fila'], 'curp' => $persona['curp'], 'salario' => $normalizado];
            $filas[] = ['fila' => $dato['_fila'], 'estado' => 'listo', 'nombre' => $persona['nombre_completo'], 'detalle' => 'Identidad y salario validados.'];
        }
        return ['filas' => $filas, 'canonicas' => $canonicas];
    }

    private function leer(string $ruta): array
    {
        $libro = IOFactory::load($ruta);
        $hoja = $libro->getActiveSheet();
        $maxFila = min($hoja->getHighestDataRow(), self::MAX_ROWS + 1);
        $maxCol = Coordinate::columnIndexFromString($hoja->getHighestDataColumn());
        $cabeceras = [];
        for ($col = 1; $col <= $maxCol; $col++) {
            $cabeceras[$col] = $this->normalizarCabecera((string) $hoja->getCell([$col, 1])->getFormattedValue());
        }
        $filas = [];
        for ($fila = 2; $fila <= $maxFila; $fila++) {
            $dato = ['_fila' => $fila];
            $tieneDatos = false;
            foreach ($cabeceras as $col => $cabecera) {
                if ($cabecera === '') continue;
                $celda = $hoja->getCell([$col, $fila]);
                if ($celda->isFormula()) {
                    throw new \InvalidArgumentException('La fila ' . $fila . ' contiene formulas. Convierte el Excel a valores antes de cargarlo.');
                }
                $valor = $celda->getFormattedValue();
                $dato[$cabecera] = is_string($valor) ? trim($valor) : $valor;
                $tieneDatos = $tieneDatos || trim((string) $valor) !== '';
            }
            if ($tieneDatos) $filas[] = $dato;
        }
        if ($hoja->getHighestDataRow() > self::MAX_ROWS + 1) {
            throw new \InvalidArgumentException('El Excel supera el limite de ' . self::MAX_ROWS . ' registros.');
        }
        return [array_values(array_filter($cabeceras)), $filas];
    }

    private function indicePersonas(): array
    {
        $rows = ($this->adapters['personas'])();
        $indice = ['id' => [], 'numero_empleado' => [], 'codigo_contpac' => [], 'curp' => [], 'nombre' => [], 'nombre_firma' => []];
        foreach ($rows as $row) {
            $row['id'] = (int) $row['id'];
            foreach (['id', 'numero_empleado', 'codigo_contpac', 'curp'] as $campo) {
                $valor = $this->normalizarIdentificador($row[$campo] ?? '');
                if ($valor !== '') $indice[$campo][$valor][] = $row;
            }
            $nombre = $this->normalizarTexto((string) $row['nombre_completo']);
            if ($nombre !== '') {
                $indice['nombre'][$nombre][] = $row;
                $firma = $this->firmaNombre($nombre);
                if ($firma !== '') $indice['nombre_firma'][$firma][] = $row;
            }
        }
        return $indice;
    }

    private function resolverPersona(array $fila, array $indice): array
    {
        $mapa = [
            'id' => ['id_persona', 'persona_id'],
            'numero_empleado' => ['numero_empleado', 'external_id', 'externalid'],
            'codigo_contpac' => ['codigo_contpac', 'codigo_contapc', 'contpac'],
            'curp' => ['curp'],
            'nombre' => ['nombre_completo', 'nombre'],
        ];
        $candidatos = null;
        $personasPorId = [];
        $usados = [];
        foreach ($mapa as $tipo => $aliases) {
            foreach ($aliases as $alias) {
                $valor = trim((string) ($fila[$alias] ?? ''));
                if ($valor === '') continue;
                $clave = $tipo === 'nombre' ? $this->normalizarTexto($valor) : $this->normalizarIdentificador($valor);
                $encontradas = $indice[$tipo][$clave] ?? [];
                if ($tipo === 'nombre' && !$encontradas) {
                    $encontradas = $indice['nombre_firma'][$this->firmaNombre($clave)] ?? [];
                }
                $usados[] = $alias;
                if (!$encontradas) {
                    return ['persona' => null, 'error' => 'No encontre ninguna persona por ' . $alias . '.'];
                }
                $idsCriterio = [];
                foreach ($encontradas as $persona) {
                    $id = (int) $persona['id'];
                    $idsCriterio[$id] = true;
                    $personasPorId[$id] = $persona;
                }
                $candidatos = $candidatos === null
                    ? $idsCriterio
                    : array_intersect_key($candidatos, $idsCriterio);
            }
        }
        if (!$usados) {
            return ['persona' => null, 'error' => 'Falta identificar a la persona por nombre, ID, numero_empleado, CURP o Codigo CONTPAC.'];
        }
        if (!$candidatos) {
            return ['persona' => null, 'error' => 'Los identificadores de la fila apuntan a personas distintas. Verifica nombre, ID, numero_empleado y Codigo CONTPAC.'];
        }
        if (count($candidatos) > 1) {
            return ['persona' => null, 'error' => 'Los datos proporcionados coinciden con varias personas; agrega otro identificador para resolver la ambiguedad.'];
        }
        $id = (int) array_key_first($candidatos);
        return ['persona' => $personasPorId[$id], 'error' => null];
    }

    private function firmaNombre(string $nombre): string
    {
        $tokens = array_values(array_filter(preg_split('/\s+/u', $this->normalizarTexto($nombre)) ?: []));
        sort($tokens, SORT_STRING);
        return implode('|', $tokens);
    }

    private function detectarOperacion(string $mensaje, string $ruta): string
    {
        $normalizado = $this->normalizarTexto($mensaje);
        if (preg_match('/\b(salario|salarios|sueldo|sueldos)\b/u', $normalizado)) return 'salarios';
        if (preg_match('/\b(estructura|puesto|jefe|supervisor|subgerente|gerente|departamento)\b/u', $normalizado)) return 'estructura';
        [$cabeceras] = $this->leer($ruta);
        if ($this->tieneAlguna($cabeceras, ['salario', 'sueldo', 'salario_mensual', 'sueldo_mensual'])) return 'salarios';
        if ($this->tieneAlguna($cabeceras, ['puesto_legacy', 'departamento', 'supervisor'])) return 'estructura';
        throw new \InvalidArgumentException('Indica si deseas actualizar salarios o estructura; el formato no permite inferirlo con seguridad.');
    }

    private function carga(string $token, int $actorId): array
    {
        $cargas = $this->cargasVigentes($actorId);
        $meta = is_array($cargas[$token] ?? null) ? $cargas[$token] : null;
        if (!$meta || !is_file((string) ($meta['ruta'] ?? ''))) {
            throw new \RuntimeException('El Excel adjunto expiro o ya no esta disponible. Vuelve a adjuntarlo.');
        }
        return $meta;
    }

    private function cargasVigentes(int $actorId): array
    {
        $cargas = is_array($_SESSION[self::SESSION_KEY] ?? null) ? $_SESSION[self::SESSION_KEY] : [];
        foreach ($cargas as $token => $meta) {
            if (!is_array($meta) || (int) ($meta['actor_id'] ?? 0) !== $actorId || (int) ($meta['expira_en'] ?? 0) < time()) {
                if (is_array($meta) && is_file((string) ($meta['ruta'] ?? ''))) @unlink($meta['ruta']);
                unset($cargas[$token]);
            }
        }
        $_SESSION[self::SESSION_KEY] = $cargas;
        return $cargas;
    }

    private function exigirSinErrores(array $analisis): void
    {
        foreach ($analisis['filas'] as $fila) {
            if (($fila['estado'] ?? '') === 'error') {
                throw new \RuntimeException('La revalidacion encontro un problema en la fila ' . (int) $fila['fila'] . ': ' . $fila['detalle']);
            }
        }
        if (!$analisis['canonicas']) throw new \RuntimeException('El Excel no contiene filas aplicables.');
    }

    private function exigirPermiso(array $contexto, string $permiso): void
    {
        if (empty($contexto['permisos_agente'][$permiso])) {
            throw new \RuntimeException('Tu perfil no tiene el permiso requerido para esta carga de ' . $permiso . '.');
        }
    }

    private function exigirColumnas(array $cabeceras, array $requeridas, string $tipo): void
    {
        $faltan = array_values(array_filter($requeridas, static fn(string $campo): bool => !in_array($campo, $cabeceras, true)));
        if ($faltan) throw new \InvalidArgumentException('El Excel de ' . $tipo . ' no contiene: ' . implode(', ', $faltan) . '.');
    }

    private function tieneAlguna(array $cabeceras, array $opciones): bool
    {
        return (bool) array_intersect($cabeceras, $opciones);
    }

    private function normalizarCabecera(string $texto): string
    {
        $texto = $this->normalizarTexto($texto);
        $texto = preg_replace('/[^a-z0-9]+/', '_', $texto) ?? '';
        return trim($texto, '_');
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        $texto = is_string($ascii) ? $ascii : $texto;
        return trim(preg_replace('/\s+/', ' ', $texto) ?? $texto);
    }

    private function normalizarIdentificador($valor): string
    {
        $valor = trim((string) $valor);
        return preg_replace('/\.0$/', '', strtoupper($valor)) ?? strtoupper($valor);
    }

    private function salario($valor): ?string
    {
        $valor = str_replace([',', '$', ' '], '', trim((string) $valor));
        if ($valor === '' || !is_numeric($valor) || (float) $valor < 0) return null;
        return number_format((float) $valor, 2, '.', '');
    }

    private function filaReporte(array $fila): array
    {
        return [
            'nombre' => 'Fila ' . (int) ($fila['fila'] ?? 0) . ': ' . ((string) ($fila['nombre'] ?? '') ?: 'sin nombre'),
            'estado' => (string) ($fila['estado'] ?? 'error'),
            'detalle' => (string) ($fila['detalle'] ?? ''),
        ];
    }

    private function mensajeErrorCarga(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El Excel supera el tamano permitido.',
            UPLOAD_ERR_PARTIAL => 'El Excel se cargo parcialmente. Intenta de nuevo.',
            UPLOAD_ERR_NO_FILE => 'Selecciona un archivo Excel.',
            default => 'No se pudo recibir el Excel (codigo ' . $error . ').',
        };
    }
}
