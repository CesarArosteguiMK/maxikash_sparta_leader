<?php

namespace Services;

require_once __DIR__ . '/../core/DatabaseCliSupport.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/DatabaseLegacy.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/MotosAdjudicadas.php';

/**
 * Diagnostico cruzado de Motos Adjudicadas entre Sparta y Legacy.
 *
 * Las consultas del modelo de lenguaje nunca son la fuente de verdad. Este
 * servicio vuelve a leer ambas bases antes de proponer y antes de ejecutar.
 */
class LeonidasMotosAdjudicadasService
{
    public const ACTION_ENVIAR_EVIDENCIAS = 'moto_enviar_evidencias';
    public const ACTION_FORZAR_EVIDENCIAS = 'moto_forzar_evidencias';
    public const ACTION_GUARDAR_DATOS_MOTO = 'moto_guardar_datos';

    private const SESSION_DRAFT = 'leonidas_motos_datos_borrador';
    private const DRAFT_TTL_SECONDS = 1800;
    private const CAMPOS_CAPTURA_MOTO = [
        'marca' => ['etiqueta' => 'la marca', 'db' => 'moto_marca'],
        'serie' => ['etiqueta' => 'el número de serie (VIN)', 'db' => 'moto_no_serie'],
        'modelo' => ['etiqueta' => 'el modelo', 'db' => 'moto_modelo'],
        'anio' => ['etiqueta' => 'el año', 'db' => 'moto_anio'],
        'color' => ['etiqueta' => 'el color', 'db' => 'moto_color'],
        'motor' => ['etiqueta' => 'el número de motor', 'db' => 'moto_no_motor'],
        'placas' => ['etiqueta' => 'las placas', 'db' => 'moto_placas'],
        'kilometraje' => ['etiqueta' => 'el kilometraje', 'db' => 'kilometraje'],
        'llave_fisica' => ['etiqueta' => 'si tiene llave física', 'db' => 'tiene_llave_fisica'],
        'placa_fisica' => ['etiqueta' => 'si tiene placa física', 'db' => 'la_moto_tiene_placa_fisica'],
        'tarjeta_circulacion' => ['etiqueta' => 'si tiene tarjeta de circulación física', 'db' => 'tiene_tarjeta_de_circulacion_en_fisico'],
        'lugar_resguardo' => ['etiqueta' => 'el lugar de resguardo', 'db' => 'log_lugar_otro'],
        'responsable' => ['etiqueta' => 'el responsable de resguardo', 'db' => 'responsable_entrega'],
        'telefono' => ['etiqueta' => 'el teléfono del responsable', 'db' => 'log_telefono'],
        'direccion' => ['etiqueta' => 'la dirección de resguardo', 'db' => 'log_direccion'],
    ];

    private \Core\Database $db;
    private ?\Core\DatabaseLegacy $legacy = null;
    private ?string $legacyError = null;

    public function __construct(?\Core\Database $db = null, ?\Core\DatabaseLegacy $legacy = null)
    {
        $this->db = $db ?? new \Core\Database();
        if ($legacy !== null) {
            $this->legacy = $legacy;
            return;
        }

        try {
            $this->legacy = new \Core\DatabaseLegacy();
        } catch (\Throwable $e) {
            $this->legacyError = $e->getMessage();
            error_log('[LeonidasMotosAdjudicadasService] Legacy no disponible: ' . $e->getMessage());
        }
    }

    public static function accionesEjecutables(): array
    {
        return [
            self::ACTION_ENVIAR_EVIDENCIAS,
            self::ACTION_FORZAR_EVIDENCIAS,
            self::ACTION_GUARDAR_DATOS_MOTO,
        ];
    }

    public static function puedeEjecutar(string $accion): bool
    {
        return in_array($accion, self::accionesEjecutables(), true);
    }

    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        $capturaDatosMoto = $this->resolverCapturaDatosMoto($mensaje, $normalizado, $contexto);
        if ($capturaDatosMoto !== null) {
            return $capturaDatosMoto;
        }

        $consultaSemana = $this->extraerSemanaMotos($normalizado);
        $consultaAsignacion = preg_match(
            '/\b(quien|a quien|asignado|asignada|responsable)\b.*\b(credito|id)\b|\b(credito|id)\b.*\b(asignado|asignada|responsable)\b/u',
            $normalizado
        ) === 1;
        $moverEvidencias = str_contains($normalizado, 'evidencia')
            && preg_match('/\b(mueve|mover|envia|enviar|pasa|pasar|manda|mandar|forzar|fuerza)\b/u', $normalizado) === 1;

        if ($consultaSemana === null && !$consultaAsignacion && !$moverEvidencias) {
            return null;
        }
        if (empty($contexto['permisos_agente']['motos'])) {
            return $this->respuesta(
                'No puedo consultar ni modificar Motos Adjudicadas porque tu perfil no tiene acceso a ese modulo.',
                'agente_denegado'
            );
        }

        if ($consultaSemana !== null) {
            return $this->resolverCreditosSemana($consultaSemana['semana'], $consultaSemana['anio']);
        }

        $idCredito = $this->extraerCredito($mensaje);
        if ($idCredito <= 0) {
            return $this->respuesta('Necesito el ID numerico del credito para consultar Sparta y Legacy.', 'agente_pregunta');
        }

        $diagnostico = $this->diagnosticar($idCredito);
        if (empty($diagnostico['operacion']) && empty($diagnostico['legacy']['tasks'])) {
            return $this->respuesta(
                'No encontre el credito ' . $idCredito . ' en Motos Adjudicadas de Sparta ni en las tareas de Legacy. No realice cambios.',
                'agente_diagnostico'
            );
        }

        $mensajeDiagnostico = $this->formatearDiagnostico($diagnostico);
        if ($consultaAsignacion && !$moverEvidencias) {
            return $this->respuesta($mensajeDiagnostico . "\n\nNo realice cambios.", 'agente_diagnostico');
        }
        if (empty($diagnostico['legacy']['disponible'])) {
            return $this->respuesta(
                $mensajeDiagnostico
                    . "\n\nNo puedo preparar ni forzar el movimiento mientras Legacy no este disponible. "
                    . 'Debo revisar tasks, task_user_assignments, campaigns, users y dictums antes de proponer el cambio. No realice cambios.',
                'agente_diagnostico'
            );
        }

        $operacion = is_array($diagnostico['operacion'] ?? null) ? $diagnostico['operacion'] : [];
        if (!$operacion) {
            return $this->respuesta(
                $mensajeDiagnostico . "\n\nNo existe una operacion local de Motos Adjudicadas que pueda moverse. No realice cambios.",
                'agente_diagnostico'
            );
        }

        $tieneDatosMoto = !empty($operacion['datos_moto_at']);
        $evidencias = (int) ($operacion['evidencias_total'] ?? 0);
        if ($tieneDatosMoto && $evidencias > 0) {
            return [
                'mensaje' => $mensajeDiagnostico
                    . "\n\nEl credito cumple el flujo normal: tiene datos de motocicleta y " . $evidencias
                    . ' evidencia(s). Puedo enviar sus evidencias despues de tu confirmacion.',
                'tipo' => 'agente_propuesta',
                'propuesta_especificacion' => [
                    'accion' => self::ACTION_ENVIAR_EVIDENCIAS,
                    'resumen' => 'enviar a Evidencias el credito ' . $idCredito . ' por el flujo normal',
                    'payload' => $this->payload($diagnostico),
                ],
            ];
        }

        $faltantes = [];
        if (!$tieneDatosMoto) {
            $faltantes[] = 'datos de la motocicleta y ubicacion';
        }
        if ($evidencias <= 0) {
            $faltantes[] = 'evidencias cargadas';
        }
        $mensajeBloqueo = $mensajeDiagnostico
            . "\n\nNo puede enviarse a Evidencias por el flujo normal porque faltan "
            . implode(' y ', $faltantes) . '. No realice cambios.';

        if (empty($contexto['permisos_agente']['motos_override_estatus'])) {
            return $this->respuesta(
                $mensajeBloqueo
                    . "\n\nEl movimiento solicitado requiere forzar el estatus. Tu perfil no tiene el permiso especial "
                    . 'Posicionamiento de estatus (Override), por lo que no puedo ejecutarlo ni omitir el control. '
                    . 'Un administrador debe asignarte ese permiso y despues puedes repetir: "mueve el credito '
                    . $idCredito . ' a Evidencias".',
                'agente_denegado'
            );
        }

        $mensajeBloqueo .= "\n\nQuieres que fuerce la opcion de moverlo a Evidencias? "
            . 'La operacion quedara auditada y los datos faltantes seguiran marcados como pendientes.';

        return [
            'mensaje' => $mensajeBloqueo,
            /*
                . "\n\n¿Quieres que fuerce la opcion de moverlo a Evidencias? La operacion quedara auditada y los datos faltantes seguiran marcados como pendientes.",
            */
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => [
                'accion' => self::ACTION_FORZAR_EVIDENCIAS,
                'resumen' => 'forzar el movimiento a Evidencias del credito ' . $idCredito,
                'payload' => $this->payload($diagnostico),
            ],
        ];
    }

    private function resolverCapturaDatosMoto(string $mensaje, string $normalizado, array $contexto): ?array
    {
        $borrador = $this->obtenerBorradorCaptura($contexto);
        if ($borrador !== null && preg_match('/^\s*(cancelar|cancela|olvidalo|salir)\s*[.!]?\s*$/u', $normalizado)) {
            $this->eliminarBorradorCaptura($contexto);
            return $this->respuesta('Entendido. Cancele la captura de datos de la motocicleta y no realice cambios.', 'agente_cancelado');
        }

        $camposRecibidos = $this->extraerCamposCapturaMoto($mensaje);
        $esPeticionCaptura = preg_match(
            '/\b(edita|editar|actualiza|actualizar|captura|capturar|registra|registrar|rellena|rellenar|guarda|guardar)\b.*'
                . '\b(moto|motocicleta|adjudicacion|formulario|datos)\b/u',
            $normalizado
        ) === 1 || count($camposRecibidos) >= 2;

        if ($borrador === null && !$esPeticionCaptura) {
            return null;
        }
        if (empty($contexto['permisos_agente']['motos'])) {
            return $this->respuesta(
                'No puedo consultar ni editar Motos Adjudicadas porque tu perfil no tiene acceso a ese modulo.',
                'agente_denegado'
            );
        }

        if ($borrador === null) {
            $idCredito = $this->extraerCreditoCaptura($mensaje);
            if ($idCredito <= 0) {
                return $this->respuesta(
                    'Necesito el ID numerico del credito cuya informacion de motocicleta deseas editar.',
                    'agente_pregunta'
                );
            }

            $diagnostico = $this->diagnosticar($idCredito);
            $operacion = is_array($diagnostico['operacion'] ?? null) ? $diagnostico['operacion'] : [];
            if (!$operacion) {
                return $this->respuesta(
                    'No encontre una operacion local de Motos Adjudicadas para el credito ' . $idCredito
                        . '. No realice cambios.',
                    'agente_diagnostico'
                );
            }

            $borrador = [
                'id_credito' => $idCredito,
                'id_operacion' => (int) ($operacion['id'] ?? 0),
                'estatus_esperado' => (string) ($operacion['estatus'] ?? ''),
                'valores' => [],
                'esperando' => null,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }

        $esperando = (string) ($borrador['esperando'] ?? '');
        if (!$camposRecibidos && $esperando !== '' && trim($mensaje) !== '') {
            $camposRecibidos[$esperando] = trim($mensaje);
        }

        foreach ($camposRecibidos as $campo => $valorCrudo) {
            $normalizadoCampo = $this->normalizarValorCapturaMoto($campo, (string) $valorCrudo);
            if (empty($normalizadoCampo['ok'])) {
                $borrador['esperando'] = $campo;
                $borrador['updated_at'] = time();
                $this->guardarBorradorCaptura($contexto, $borrador);
                return $this->respuesta(
                    (string) ($normalizadoCampo['mensaje'] ?? 'El dato no tiene un formato valido.')
                        . ' ' . $this->preguntaCampoCaptura($campo),
                    'agente_pregunta'
                );
            }
            $borrador['valores'][$campo] = $normalizadoCampo['valor'];
            $borrador['esperando'] = null;
        }

        $faltante = $this->primerCampoFaltanteCaptura((array) ($borrador['valores'] ?? []));
        if ($faltante !== null) {
            $borrador['esperando'] = $faltante;
            $borrador['updated_at'] = time();
            $this->guardarBorradorCaptura($contexto, $borrador);
            return $this->respuesta($this->preguntaCampoCaptura($faltante), 'agente_pregunta');
        }

        $borrador['esperando'] = null;
        $borrador['updated_at'] = time();
        $this->guardarBorradorCaptura($contexto, $borrador);
        $datos = $this->construirDatosDbCaptura((array) $borrador['valores']);
        $idCredito = (int) ($borrador['id_credito'] ?? 0);

        return [
            'mensaje' => $this->resumenCapturaMoto($idCredito, (array) $borrador['valores'])
                . "\n\nSi los datos son correctos, confirma para guardarlos. No se ha modificado ningun dato todavia.",
            'tipo' => 'agente_propuesta',
            'propuesta_especificacion' => [
                'accion' => self::ACTION_GUARDAR_DATOS_MOTO,
                'resumen' => 'guardar los datos de la motocicleta del credito ' . $idCredito,
                'payload' => [
                    'id_credito' => $idCredito,
                    'id_operacion' => (int) ($borrador['id_operacion'] ?? 0),
                    'estatus_esperado' => (string) ($borrador['estatus_esperado'] ?? ''),
                    'datos' => $datos,
                ],
            ],
        ];
    }

    private function extraerCamposCapturaMoto(string $mensaje): array
    {
        $campos = [];
        $lineas = preg_split('/\R/u', $mensaje) ?: [];
        foreach ($lineas as $linea) {
            if (preg_match('/^\s*([^:]{2,60})\s*:\s*(.*?)\s*$/u', $linea, $match) !== 1) {
                continue;
            }
            $campo = $this->mapearCampoCaptura($match[1]);
            if ($campo !== null) {
                $campos[$campo] = trim((string) $match[2]);
            }
        }
        return $campos;
    }

    private function mapearCampoCaptura(string $etiqueta): ?string
    {
        $etiqueta = preg_replace('/[^a-z0-9]+/u', ' ', $this->normalizar($etiqueta)) ?? '';
        $etiqueta = trim($etiqueta);

        return match (true) {
            str_contains($etiqueta, 'direccion') && str_contains($etiqueta, 'resguardo') => 'direccion',
            str_contains($etiqueta, 'lugar') && str_contains($etiqueta, 'resguardo') => 'lugar_resguardo',
            str_contains($etiqueta, 'tarjeta') && str_contains($etiqueta, 'circulacion') => 'tarjeta_circulacion',
            str_contains($etiqueta, 'llave') => 'llave_fisica',
            str_contains($etiqueta, 'placa') && str_contains($etiqueta, 'fisica') => 'placa_fisica',
            in_array($etiqueta, ['placa', 'placas'], true) => 'placas',
            str_contains($etiqueta, 'no motor') || str_contains($etiqueta, 'numero motor') || $etiqueta === 'motor' => 'motor',
            str_contains($etiqueta, 'serie') || str_contains($etiqueta, 'vin') => 'serie',
            str_contains($etiqueta, 'kilometraje') || $etiqueta === 'km' => 'kilometraje',
            str_contains($etiqueta, 'responsable') => 'responsable',
            str_contains($etiqueta, 'telefono') || str_contains($etiqueta, 'celular') => 'telefono',
            $etiqueta === 'marca' => 'marca',
            $etiqueta === 'modelo' => 'modelo',
            $etiqueta === 'ano' || $etiqueta === 'anio' => 'anio',
            $etiqueta === 'color' => 'color',
            default => null,
        };
    }

    private function normalizarValorCapturaMoto(string $campo, string $valor): array
    {
        $valor = trim($valor);
        $valorNormalizado = trim(preg_replace('/\s+/u', ' ', $this->normalizar($valor)) ?? '');
        $esVacioExplicito = preg_match(
            '/^(no|no hay|no tiene|ninguno|ninguna|sin dato|sin datos|no aplica|n\/?a|na|inexistente)$/u',
            $valorNormalizado
        ) === 1;

        if (in_array($campo, ['llave_fisica', 'placa_fisica', 'tarjeta_circulacion'], true)) {
            if ($esVacioExplicito || in_array($valorNormalizado, ['no', 'falso', '0'], true)) {
                return ['ok' => true, 'valor' => 'no'];
            }
            if (in_array($valorNormalizado, ['si', 's', 'yes', 'verdadero', '1'], true)) {
                return ['ok' => true, 'valor' => 'si'];
            }
            return ['ok' => false, 'mensaje' => 'Responde SI o NO para este campo.'];
        }

        if ($esVacioExplicito) {
            return ['ok' => true, 'valor' => ''];
        }
        if ($valor === '') {
            return ['ok' => false, 'mensaje' => 'El dato quedo vacio. Si no existe, responde NO.'];
        }

        if ($campo === 'serie') {
            $valor = strtoupper(preg_replace('/\s+/u', '', $valor) ?? '');
            if (preg_match('/^[A-HJ-NPR-Z0-9]{8,17}$/', $valor) !== 1) {
                return ['ok' => false, 'mensaje' => 'El numero de serie debe contener entre 8 y 17 caracteres validos.'];
            }
        } elseif ($campo === 'motor') {
            $valor = strtoupper(preg_replace('/\s+/u', '', $valor) ?? '');
            if (preg_match('/^[A-Z0-9\-]{4,24}$/', $valor) !== 1) {
                return ['ok' => false, 'mensaje' => 'El numero de motor debe contener de 4 a 24 letras, numeros o guiones.'];
            }
        } elseif ($campo === 'anio') {
            $anio = (int) preg_replace('/\D+/', '', $valor);
            if ($anio < 1900 || $anio > ((int) date('Y') + 2)) {
                return ['ok' => false, 'mensaje' => 'El ano de la motocicleta no es valido.'];
            }
            $valor = (string) $anio;
        } elseif ($campo === 'kilometraje') {
            $numero = str_replace([',', ' '], '', $valor);
            if (!is_numeric($numero) || (float) $numero < 0) {
                return ['ok' => false, 'mensaje' => 'El kilometraje debe ser un numero igual o mayor que cero.'];
            }
            $valor = (string) (0 + $numero);
        } elseif ($campo === 'telefono') {
            $valor = preg_replace('/\D+/', '', $valor) ?? '';
            if (strlen($valor) !== 10) {
                return ['ok' => false, 'mensaje' => 'El telefono debe contener 10 digitos.'];
            }
        } elseif ($campo === 'placas') {
            $valor = strtoupper(preg_replace('/\s+/u', '', $valor) ?? '');
        } else {
            $valor = trim(preg_replace('/\s+/u', ' ', $valor) ?? $valor);
        }

        return ['ok' => true, 'valor' => $valor];
    }

    private function primerCampoFaltanteCaptura(array $valores): ?string
    {
        foreach (array_keys(self::CAMPOS_CAPTURA_MOTO) as $campo) {
            if (!array_key_exists($campo, $valores)) {
                return $campo;
            }
        }
        return null;
    }

    private function preguntaCampoCaptura(string $campo): string
    {
        $etiqueta = (string) (self::CAMPOS_CAPTURA_MOTO[$campo]['etiqueta'] ?? 'el dato faltante');
        $referencia = match (true) {
            str_starts_with($etiqueta, 'el ') => 'del ' . mb_substr($etiqueta, 3),
            str_starts_with($etiqueta, 'la ') => 'de la ' . mb_substr($etiqueta, 3),
            str_starts_with($etiqueta, 'las ') => 'de las ' . mb_substr($etiqueta, 4),
            str_starts_with($etiqueta, 'si ') => 'que indica ' . $etiqueta,
            default => 'de ' . $etiqueta,
        };
        $pregunta = "Falta el dato {$referencia}, \u{00BF}cu\u{00E1}l ser\u{00ED}a?";
        if (in_array($campo, ['llave_fisica', 'placa_fisica', 'tarjeta_circulacion'], true)) {
            return $pregunta . ' Responde SI o NO.';
        }
        return $pregunta . " Si no existe, responde NO y lo dejar\u{00E9} vac\u{00ED}o.";
    }

    private function construirDatosDbCaptura(array $valores): array
    {
        $datos = [];
        foreach (self::CAMPOS_CAPTURA_MOTO as $campo => $configuracion) {
            if ($campo === 'lugar_resguardo') {
                continue;
            }
            $datos[(string) $configuracion['db']] = $valores[$campo] ?? '';
        }

        $lugar = trim((string) ($valores['lugar_resguardo'] ?? ''));
        $lugarNormalizado = $this->normalizar($lugar);
        if ($lugar === '') {
            $datos['log_lugar_resguardo'] = '';
            $datos['log_lugar_otro'] = '';
        } elseif (str_contains($lugarNormalizado, 'domicilio')) {
            $datos['log_lugar_resguardo'] = 'mi_domicilio';
            $datos['log_lugar_otro'] = '';
        } elseif (str_contains($lugarNormalizado, 'sucursal')) {
            $datos['log_lugar_resguardo'] = 'sucursal';
            $datos['log_lugar_otro'] = '';
        } else {
            $datos['log_lugar_resguardo'] = 'otro';
            $datos['log_lugar_otro'] = $lugar;
        }

        return $datos;
    }

    private function resumenCapturaMoto(int $idCredito, array $valores): string
    {
        $lineas = ['Vista previa de los datos de Adjudicacion de Motos del credito ' . $idCredito . ':'];
        foreach (self::CAMPOS_CAPTURA_MOTO as $campo => $configuracion) {
            $valor = (string) ($valores[$campo] ?? '');
            $lineas[] = '- ' . ucfirst((string) $configuracion['etiqueta']) . ': ' . ($valor !== '' ? $valor : 'sin dato');
        }
        return implode("\n", $lineas);
    }

    private function extraerCreditoCaptura(string $mensaje): int
    {
        $limpio = str_replace([',', '.'], '', $mensaje);
        if (preg_match('/\b(?:credito|id)\s*(?:>|:|#|-)?\s*(\d{4,12})\b/iu', $limpio, $match) === 1) {
            return (int) $match[1];
        }
        return $this->extraerCredito($mensaje);
    }

    private function obtenerBorradorCaptura(array $contexto): ?array
    {
        $this->asegurarSesionCaptura();
        $clave = $this->claveBorradorCaptura($contexto);
        $borrador = $_SESSION[self::SESSION_DRAFT][$clave] ?? null;
        if (!is_array($borrador)) {
            return null;
        }
        if (time() - (int) ($borrador['updated_at'] ?? 0) > self::DRAFT_TTL_SECONDS) {
            unset($_SESSION[self::SESSION_DRAFT][$clave]);
            return null;
        }
        return $borrador;
    }

    private function guardarBorradorCaptura(array $contexto, array $borrador): void
    {
        $this->asegurarSesionCaptura();
        $_SESSION[self::SESSION_DRAFT][$this->claveBorradorCaptura($contexto)] = $borrador;
    }

    private function eliminarBorradorCaptura(array $contexto): void
    {
        $this->asegurarSesionCaptura();
        unset($_SESSION[self::SESSION_DRAFT][$this->claveBorradorCaptura($contexto)]);
    }

    private function asegurarSesionCaptura(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!isset($_SESSION[self::SESSION_DRAFT]) || !is_array($_SESSION[self::SESSION_DRAFT])) {
            $_SESSION[self::SESSION_DRAFT] = [];
        }
    }

    private function claveBorradorCaptura(array $contexto): string
    {
        $actorId = (int) ($contexto['actor_id'] ?? 0);
        return $actorId > 0 ? 'actor_' . $actorId : 'session_' . session_id();
    }

    private function resolverCreditosSemana(int $semana, int $anio): array
    {
        if ($this->legacy === null) {
            return $this->respuesta(
                'No pude consultar las motos adjudicadas porque Legacy no esta disponible. '
                    . 'Detalle tecnico: ' . ($this->legacyError ?: 'conexion no inicializada') . '.',
                'agente_error'
            );
        }

        if ($semana < 1 || $semana > 53 || $anio < 2020 || $anio > 2100) {
            return $this->respuesta(
                'La semana o el anio no son validos. Indica una semana del 1 al 53 y el anio con cuatro digitos.',
                'agente_pregunta'
            );
        }

        $inicio = (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))
            ->setISODate($anio, $semana, 1)
            ->setTime(0, 0, 0);
        $fin = $inicio->modify('+7 days');

        try {
            $filas = $this->legacy->queryAll(
                "SELECT TRIM(t.credit_number) AS id_credito,
                        MAX(t.client_name) AS cliente,
                        MAX(t.id) AS task_id,
                        MAX(t.created_at) AS fecha_ingreso
                   FROM tasks t
                   INNER JOIN campaigns c ON c.id = t.campaign_id
                  WHERE LOWER(TRIM(c.name)) = 'motos adjudicadas'
                    AND c.deleted_at IS NULL
                    AND t.deleted_at IS NULL
                    AND t.created_at >= :inicio
                    AND t.created_at < :fin
                    AND TRIM(COALESCE(t.credit_number, '')) <> ''
                  GROUP BY TRIM(t.credit_number)
                  ORDER BY CAST(id_credito AS UNSIGNED), id_credito",
                [
                    'inicio' => $inicio->format('Y-m-d H:i:s'),
                    'fin' => $fin->format('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $e) {
            error_log('[LeonidasMotosAdjudicadasService] Error reporte semanal: ' . $e->getMessage());
            return $this->respuesta(
                'No pude generar el reporte de Motos Adjudicadas de la semana ' . $semana
                    . ' de ' . $anio . ' porque fallo la consulta de Legacy. No invente resultados.',
                'agente_error'
            );
        }

        $inicioTexto = $this->fechaCorta($inicio);
        $finTexto = $this->fechaCorta($fin->modify('-1 day'));
        if (!$filas) {
            return [
                'mensaje' => 'No encontre creditos ingresados a Motos Adjudicadas durante la semana '
                    . $semana . ' de ' . $anio . ' (' . $inicioTexto . ' al ' . $finTexto . '). '
                    . 'Consulte las tareas activas de la campania Motos Adjudicadas en Legacy.',
                'tipo' => 'agente_reporte',
                'reporte' => [
                    'modulo' => 'Motos Adjudicadas',
                    'semana' => $semana,
                    'anio' => $anio,
                    'total' => 0,
                    'filas' => [],
                ],
            ];
        }

        $ids = array_values(array_map(
            static fn(array $fila): string => trim((string) ($fila['id_credito'] ?? '')),
            $filas
        ));
        $lineas = [
            'Motos Adjudicadas de la semana ' . $semana . ' de ' . $anio
                . ' (' . $inicioTexto . ' al ' . $finTexto . ').',
            'Creditos unicos: ' . count($ids) . '.',
            'IDs: ' . implode(', ', $ids) . '.',
            '',
            'Fuente: tareas activas de Legacy pertenecientes a la campania Motos Adjudicadas, '
                . 'clasificadas por la fecha de creacion de la tarea. No mezcle campanias de asignacion masiva.',
        ];

        return [
            'mensaje' => implode("\n", $lineas),
            'tipo' => 'agente_reporte',
            'reporte' => [
                'modulo' => 'Motos Adjudicadas',
                'semana' => $semana,
                'anio' => $anio,
                'desde' => $inicio->format('Y-m-d'),
                'hasta' => $fin->modify('-1 day')->format('Y-m-d'),
                'total' => count($ids),
                'ids' => $ids,
                'filas' => $filas,
            ],
        ];
    }

    private function extraerSemanaMotos(string $normalizado): ?array
    {
        if (!str_contains($normalizado, 'moto') || !str_contains($normalizado, 'semana')) {
            return null;
        }
        if (preg_match('/\bsemana\s*(\d{1,2})(?:\s*(?:del|de)?\s*(20\d{2}))?\b/u', $normalizado, $match) !== 1) {
            return null;
        }

        return [
            'semana' => (int) $match[1],
            'anio' => isset($match[2]) && $match[2] !== '' ? (int) $match[2] : (int) date('o'),
        ];
    }

    private function fechaCorta(\DateTimeImmutable $fecha): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];
        return (int) $fecha->format('j') . ' de ' . $meses[(int) $fecha->format('n')];
    }

    public function ejecutar(string $accion, array $payload, array $contexto): array
    {
        if (!self::puedeEjecutar($accion)) {
            throw new \RuntimeException('Accion de Motos Adjudicadas no reconocida.');
        }
        if (empty($contexto['permisos_agente']['motos'])) {
            throw new \RuntimeException('Tu perfil ya no tiene acceso a Motos Adjudicadas.');
        }
        if ($accion === self::ACTION_FORZAR_EVIDENCIAS && empty($contexto['permisos_agente']['motos_override_estatus'])) {
            throw new \RuntimeException('Tu perfil ya no tiene el permiso Posicionamiento de estatus (Override).');
        }

        $idCredito = (int) ($payload['id_credito'] ?? 0);
        $idOperacionEsperado = (int) ($payload['id_operacion'] ?? 0);
        if ($idCredito <= 0 || $idOperacionEsperado <= 0) {
            throw new \RuntimeException('La confirmacion no contiene un credito y una operacion validos.');
        }

        $diagnostico = $this->diagnosticar($idCredito);
        $operacion = is_array($diagnostico['operacion'] ?? null) ? $diagnostico['operacion'] : [];
        if (!$operacion || (int) ($operacion['id'] ?? 0) !== $idOperacionEsperado) {
            throw new \RuntimeException('La operacion cambio despues de la vista previa. Vuelve a solicitar el diagnostico.');
        }

        $modelo = new \Models\MotosAdjudicadas();
        $actorId = (int) ($contexto['actor_id'] ?? 0);
        $actorNombre = trim((string) ($contexto['nombre'] ?? 'Leonidas'));

        if ($accion === self::ACTION_GUARDAR_DATOS_MOTO) {
            $estatusEsperado = trim((string) ($payload['estatus_esperado'] ?? ''));
            $estatusActual = trim((string) ($operacion['estatus'] ?? ''));
            if ($estatusEsperado !== '' && $estatusActual !== $estatusEsperado) {
                throw new \RuntimeException(
                    'El estatus de la operacion cambio despues de la vista previa. '
                    . 'Vuelve a solicitar la actualizacion para revisar los datos actuales.'
                );
            }

            $datos = is_array($payload['datos'] ?? null) ? $payload['datos'] : [];
            $camposPermitidos = [
                'moto_marca',
                'moto_no_serie',
                'moto_modelo',
                'moto_anio',
                'moto_color',
                'moto_no_motor',
                'moto_placas',
                'kilometraje',
                'tiene_llave_fisica',
                'la_moto_tiene_placa_fisica',
                'tiene_tarjeta_de_circulacion_en_fisico',
                'log_lugar_resguardo',
                'log_lugar_otro',
                'responsable_entrega',
                'log_telefono',
                'log_direccion',
            ];
            $datos = array_intersect_key($datos, array_flip($camposPermitidos));
            if (!$datos) {
                throw new \RuntimeException('La confirmacion no contiene datos de la motocicleta para guardar.');
            }

            $resultado = $modelo->guardarDatosMoto(
                $idOperacionEsperado,
                $datos,
                $actorId,
                $actorNombre,
                true,
                null,
                true
            );
            if (empty($resultado['success'])) {
                throw new \RuntimeException(
                    trim((string) ($resultado['message'] ?? 'No se pudieron guardar los datos de la motocicleta.'))
                );
            }

            $this->eliminarBorradorCaptura($contexto);
            return $this->respuesta(
                'Listo. Guarde los datos de Adjudicacion de Motos del credito ' . $idCredito
                    . '. La actualizacion quedo registrada en la bitacora.',
                'agente_ejecutado'
            );
        }

        if ($accion === self::ACTION_ENVIAR_EVIDENCIAS) {
            if (empty($operacion['datos_moto_at']) || (int) ($operacion['evidencias_total'] ?? 0) <= 0) {
                throw new \RuntimeException('El credito dejo de cumplir los requisitos del flujo normal. Vuelve a solicitar el diagnostico.');
            }
            $resultado = $modelo->enviarEvidencias($idOperacionEsperado, $actorId, $actorNombre);
            if (empty($resultado['success'])) {
                throw new \RuntimeException(trim((string) ($resultado['message'] ?? 'No se pudieron enviar las evidencias.')));
            }
            return $this->respuesta(
                'Listo. Envie las evidencias del credito ' . $idCredito . ' por el flujo normal y registre la accion en la bitacora.',
                'agente_ejecutado'
            );
        }

        if ($accion !== self::ACTION_FORZAR_EVIDENCIAS) {
            throw new \RuntimeException('La accion confirmada de Motos Adjudicadas no tiene un ejecutor habilitado.');
        }

        $resultado = $modelo->cambiarEstatus($idOperacionEsperado, 'Recibido', $actorId, $actorNombre, 'monitoreo');
        if (empty($resultado['success'])) {
            throw new \RuntimeException(trim((string) ($resultado['message'] ?? 'No se pudo forzar el movimiento.')));
        }

        return $this->respuesta(
            'Listo. Force el movimiento a Evidencias del credito ' . $idCredito
                . '. La accion quedo auditada. No se inventaron datos de motocicleta, evidencias ni dictamenes; esos faltantes siguen pendientes.',
            'agente_ejecutado'
        );
    }

    public function diagnosticar(int $idCredito): array
    {
        $operacion = $this->db->queryOne(
            "SELECT o.*,
                    COUNT(e.id) AS evidencias_total,
                    SUM(CASE WHEN e.estatus = 'pendiente_envio' THEN 1 ELSE 0 END) AS evidencias_pendientes,
                    SUM(CASE WHEN e.estatus = 'recibido' THEN 1 ELSE 0 END) AS evidencias_recibidas
               FROM adj_operacion o
               LEFT JOIN adj_evidencia e ON e.id_operacion = o.id
              WHERE o.id_credito = :id_credito
              GROUP BY o.id
              ORDER BY o.id DESC
              LIMIT 1",
            ['id_credito' => $idCredito]
        );
        $asignacion = $this->db->queryOne(
            "SELECT aca.id, aca.id_credito, aca.id_personal_adj, aca.estatus, aca.fecha_alta,
                    pa.id_persona,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                    p.numero_empleado
               FROM asigna_creditos_adjudicacion aca
               INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
               INNER JOIN persona p ON p.id = pa.id_persona
              WHERE aca.id_credito = :id_credito AND aca.estatus = '1'
              ORDER BY aca.id DESC
              LIMIT 1",
            ['id_credito' => $idCredito]
        );

        $legacy = [
            'disponible' => $this->legacy !== null,
            'error' => $this->legacyError,
            'tasks' => [],
            'assignments' => [],
            'dictums' => [],
            'motos_task' => null,
            'otra_task_activa' => null,
        ];
        if ($this->legacy !== null) {
            try {
                $legacy = array_merge($legacy, $this->consultarLegacy($idCredito));
            } catch (\Throwable $e) {
                $legacy['disponible'] = false;
                $legacy['error'] = $e->getMessage();
                error_log('[LeonidasMotosAdjudicadasService] Error consultando credito ' . $idCredito . ': ' . $e->getMessage());
            }
        }

        return [
            'id_credito' => $idCredito,
            'operacion' => $operacion,
            'asignacion_local' => $asignacion,
            'legacy' => $legacy,
        ];
    }

    private function consultarLegacy(int $idCredito): array
    {
        $tasks = $this->legacy->queryAll(
            "SELECT t.id AS task_id, t.credit_number, t.client_name,
                    t.status AS task_status, t.current_user_id,
                    t.deleted_at AS task_deleted_at,
                    t.created_at AS task_created_at, t.updated_at AS task_updated_at,
                    c.id AS campaign_id, c.name AS campaign_name,
                    c.start_date, c.end_date, c.deleted_at AS campaign_deleted_at,
                    cu.name AS current_user_name, cu.external_id AS current_user_external_id
               FROM tasks t
               LEFT JOIN campaigns c ON c.id = t.campaign_id
               LEFT JOIN users cu ON cu.id = t.current_user_id
              WHERE TRIM(t.credit_number) = :credito
              ORDER BY t.id DESC",
            ['credito' => (string) $idCredito]
        );

        $assignments = [];
        $dictums = [];
        $taskIds = array_values(array_filter(array_map('intval', array_column($tasks, 'task_id'))));
        if ($taskIds) {
            [$in, $params] = $this->placeholders($taskIds, 'task');
            $assignments = $this->legacy->queryAll(
                "SELECT tua.id, tua.task_id, tua.user_id,
                        u.name AS user_name, u.external_id, u.username,
                        tua.assigned_at, tua.unassigned_at
                   FROM task_user_assignments tua
                   LEFT JOIN users u ON u.id = tua.user_id
                  WHERE tua.task_id IN ({$in})
                  ORDER BY tua.task_id DESC, tua.id DESC",
                $params
            );
            $dictums = $this->legacy->queryAll(
                "SELECT d.id, d.task_id, d.opciondictamen_id, d.user_id,
                        u.name AS user_name, d.created_at, d.updated_at,
                        d.sent_at, d.valid_geofencing, d.handling_time,
                        CASE WHEN d.form_response IS NULL OR TRIM(d.form_response) = '' THEN 0 ELSE 1 END AS tiene_respuesta
                   FROM dictums d
                   LEFT JOIN users u ON u.id = d.user_id
                  WHERE d.task_id IN ({$in})
                  ORDER BY d.task_id DESC, d.id DESC",
                $params
            );
        }

        $asignacionesPorTarea = [];
        foreach ($assignments as $assignment) {
            $taskId = (int) ($assignment['task_id'] ?? 0);
            $asignacionesPorTarea[$taskId][] = $assignment;
        }
        foreach ($tasks as &$task) {
            $taskId = (int) ($task['task_id'] ?? 0);
            $task['active_assignment'] = $this->asignacionActiva($asignacionesPorTarea[$taskId] ?? []);
        }
        unset($task);

        $activas = array_values(array_filter($tasks, fn(array $task): bool => $this->tareaActiva($task)));
        $motosTask = null;
        $otraTask = null;
        foreach ($activas as $task) {
            if ($motosTask === null && $this->esCampaniaMotos((string) ($task['campaign_name'] ?? ''))) {
                $motosTask = $task;
                continue;
            }
            if ($otraTask === null && !$this->esCampaniaMotos((string) ($task['campaign_name'] ?? ''))) {
                $otraTask = $task;
            }
        }

        return [
            'disponible' => true,
            'error' => null,
            'tasks' => $tasks,
            'assignments' => $assignments,
            'dictums' => $dictums,
            'motos_task' => $motosTask,
            'otra_task_activa' => $otraTask,
        ];
    }

    private function formatearDiagnostico(array $diagnostico): string
    {
        $idCredito = (int) ($diagnostico['id_credito'] ?? 0);
        $operacion = is_array($diagnostico['operacion'] ?? null) ? $diagnostico['operacion'] : [];
        $local = is_array($diagnostico['asignacion_local'] ?? null) ? $diagnostico['asignacion_local'] : [];
        $legacy = is_array($diagnostico['legacy'] ?? null) ? $diagnostico['legacy'] : [];
        $motosTask = is_array($legacy['motos_task'] ?? null) ? $legacy['motos_task'] : [];
        $otraTask = is_array($legacy['otra_task_activa'] ?? null) ? $legacy['otra_task_activa'] : [];

        $cliente = trim((string) ($operacion['nombre_cliente'] ?? ''));
        if ($cliente === '') {
            $cliente = trim((string) ($motosTask['client_name'] ?? ($legacy['tasks'][0]['client_name'] ?? '')));
        }
        $lineas = ['Encontre el credito ' . $idCredito . ($cliente !== '' ? ' de ' . $this->nombre($cliente) : '') . '.'];
        $lineas[] = '';
        $lineas[] = 'Diagnostico cruzado:';

        if ($local) {
            $lineas[] = '- Sparta / Motos Adjudicadas: asignado a ' . $this->nombre((string) $local['nombre'])
                . ' (No. empleado ' . ($local['numero_empleado'] ?: 'sin dato') . ').';
        } else {
            $lineas[] = '- Sparta / Motos Adjudicadas: sin asignacion local activa.';
        }
        if ($operacion) {
            $lineas[] = '- Operacion Sparta: ' . (int) $operacion['id'] . ', estatus '
                . $this->etiquetaEstatus((string) ($operacion['estatus'] ?? '')) . ', '
                . (!empty($operacion['datos_moto_at']) ? 'con datos de motocicleta' : 'sin datos de motocicleta')
                . ' y ' . (int) ($operacion['evidencias_total'] ?? 0) . ' evidencia(s).';
        }

        if (empty($legacy['disponible'])) {
            $lineas[] = '- Legacy: no estuvo disponible durante esta consulta. La respuesta local se conserva, pero el diagnostico no esta completo.';
            return implode("\n", $lineas);
        }

        if ($motosTask) {
            $responsable = $this->responsableLegacy($motosTask);
            $lineas[] = '- Legacy / Motos Adjudicadas: tarea ' . (int) $motosTask['task_id']
                . ', campania ' . (string) ($motosTask['campaign_name'] ?? 'sin nombre')
                . ', estatus ' . ((string) ($motosTask['task_status'] ?? '') ?: 'sin dato')
                . ', usuario Legacy ' . ($responsable['id'] ?: 'sin dato') . ' (' . $responsable['nombre'] . ')'
                . ($responsable['assignment_id'] > 0 ? ', asignacion activa ' . $responsable['assignment_id'] : '') . '.';
        } else {
            $lineas[] = '- Legacy / Motos Adjudicadas: no encontre una tarea activa de esta campania.';
        }
        if ($otraTask) {
            $responsable = $this->responsableLegacy($otraTask);
            $lineas[] = '- Legacy / otro flujo activo: tarea ' . (int) $otraTask['task_id'] . ', campania '
                . (string) ($otraTask['campaign_name'] ?? 'sin nombre') . ', asignada a ' . $responsable['nombre']
                . '. Es otro flujo y no sustituye al responsable de Motos Adjudicadas.';
        }

        $dictums = is_array($legacy['dictums'] ?? null) ? $legacy['dictums'] : [];
        $lineas[] = '- Historial Legacy consultado: ' . count($legacy['tasks'] ?? []) . ' tarea(s), '
            . count($legacy['assignments'] ?? []) . ' asignacion(es) y ' . count($dictums) . ' dictamen(es).';
        $dictumsMotos = 0;
        $motosTaskId = (int) ($motosTask['task_id'] ?? 0);
        foreach ($dictums as $dictum) {
            if ((int) ($dictum['task_id'] ?? 0) === $motosTaskId && $motosTaskId > 0) {
                $dictumsMotos++;
            }
        }
        $lineas[] = '- Dictamenes Legacy: la tarea vigente de Motos tiene ' . $dictumsMotos
            . '; el credito acumula ' . count($dictums) . ' dictamen(es) en todas sus tareas historicas.';

        return implode("\n", $lineas);
    }

    private function payload(array $diagnostico): array
    {
        $operacion = $diagnostico['operacion'];
        $motosTask = $diagnostico['legacy']['motos_task'] ?? [];
        return [
            'id_credito' => (int) $diagnostico['id_credito'],
            'id_operacion' => (int) ($operacion['id'] ?? 0),
            'estatus_esperado' => (string) ($operacion['estatus'] ?? ''),
            'legacy_motos_task_id' => (int) ($motosTask['task_id'] ?? 0),
        ];
    }

    private function responsableLegacy(array $task): array
    {
        $assignment = is_array($task['active_assignment'] ?? null) ? $task['active_assignment'] : [];
        $nombre = trim((string) ($assignment['user_name'] ?? $task['current_user_name'] ?? ''));
        return [
            'id' => (int) ($assignment['user_id'] ?? $task['current_user_id'] ?? 0),
            'nombre' => $nombre !== '' ? $this->nombre($nombre) : 'sin responsable identificado',
            'assignment_id' => (int) ($assignment['id'] ?? 0),
        ];
    }

    private function asignacionActiva(array $assignments): ?array
    {
        foreach ($assignments as $assignment) {
            if (empty($assignment['unassigned_at'])) {
                return $assignment;
            }
        }
        return null;
    }

    private function tareaActiva(array $task): bool
    {
        return empty($task['task_deleted_at']) && empty($task['campaign_deleted_at']);
    }

    private function esCampaniaMotos(string $campaign): bool
    {
        return str_contains($this->normalizar($campaign), 'motos adjudicadas');
    }

    private function placeholders(array $ids, string $prefix): array
    {
        $params = [];
        $tokens = [];
        foreach ($ids as $index => $id) {
            $key = $prefix . '_' . $index;
            $tokens[] = ':' . $key;
            $params[$key] = (int) $id;
        }
        return [implode(',', $tokens), $params];
    }

    private function extraerCredito(string $mensaje): int
    {
        if (preg_match('/\b\d{4,12}\b/', str_replace([',', '.'], '', $mensaje), $match) !== 1) {
            return 0;
        }
        return (int) $match[0];
    }

    private function normalizar(string $valor): string
    {
        $valor = mb_strtolower(trim($valor), 'UTF-8');
        $valor = strtr($valor, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        if ($ascii !== false) {
            return $ascii;
        }
        return $valor;
    }

    private function nombre(string $valor): string
    {
        $valor = preg_replace('/\s+/u', ' ', trim($valor)) ?? trim($valor);
        return mb_convert_case(mb_strtolower($valor, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function etiquetaEstatus(string $estatus): string
    {
        return match (mb_strtolower(trim($estatus), 'UTF-8')) {
            'en_transito' => 'En transito',
            'recibido' => 'Recibido / Evidencias',
            default => $estatus !== '' ? $estatus : 'sin estatus',
        };
    }

    private function respuesta(string $mensaje, string $tipo): array
    {
        return ['mensaje' => $mensaje, 'tipo' => $tipo];
    }
}
