<?php

namespace Services;

use Core\SecureUpload;
use Models\CapHum as CapHumDAO;

class RrhhDocumentImportService
{
    private const DOCUMENTO_RFC = 10;
    private const DOCUMENTO_CONSTANCIA_FISCAL = 22;
    private const DOCUMENTO_CONTRATO_FIRMADO = 28;
    private const DOCUMENTO_ARCHIVO_FAD = 29;
    private const DOCUMENTO_LLAVE_VECTOR = 31;
    private const MODULO_DOCUMENTO_RRHH_BASE = 3000;
    private const BATCH_TTL_SECONDS = 86400;
    private const DOCUMENTO_SENSIBLE_MAGIC = "SPARTA_RRHH_DOC_V1\n";

    private function puedeUsarTipoDocumentoRrhh(int $idDocumento): bool
    {
        if ($idDocumento <= 0) {
            return false;
        }

        $controlados = [
            8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 22, 23, 24, 25,
            27, 28, 29, 30, 31, 32, 33, 34, 35, 36,
        ];
        if (!in_array($idDocumento, $controlados, true)) {
            return true;
        }

        $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        return in_array(self::MODULO_DOCUMENTO_RRHH_BASE + $idDocumento, $modulos, true);
    }

    public function fuentesDesdeRequest(array $files, array $post): array
    {
        $archivos = $files['archivos'] ?? null;
        if (!$archivos || empty($archivos['name'])) {
            return [];
        }

        $names = is_array($archivos['name']) ? $archivos['name'] : [$archivos['name']];
        $tmpNames = is_array($archivos['tmp_name']) ? $archivos['tmp_name'] : [$archivos['tmp_name']];
        $errors = is_array($archivos['error']) ? $archivos['error'] : [$archivos['error']];
        $sizes = is_array($archivos['size']) ? $archivos['size'] : [$archivos['size']];
        $rutas = $post['rutas_relativas'] ?? [];
        if (!is_array($rutas)) {
            $rutas = [$rutas];
        }

        $fuentes = [];
        foreach ($names as $i => $nombreOriginal) {
            $tmp = $tmpNames[$i] ?? null;
            $error = $errors[$i] ?? UPLOAD_ERR_NO_FILE;
            if (!$tmp || $error !== UPLOAD_ERR_OK || !is_uploaded_file($tmp)) {
                continue;
            }

            $rutaRelativa = trim((string) ($rutas[$i] ?? $nombreOriginal));
            $extension = strtolower(pathinfo((string) $nombreOriginal, PATHINFO_EXTENSION));

            if ($extension === 'zip') {
                $fuentes = array_merge($fuentes, $this->fuentesDesdeZip($tmp, (string) $nombreOriginal));
                continue;
            }

            if (!in_array($extension, ['pdf', 'fad'], true)) {
                continue;
            }

            $fuentes[] = [
                'tipo' => 'upload',
                'tmp' => $tmp,
                'ruta_relativa' => $rutaRelativa !== '' ? $rutaRelativa : (string) $nombreOriginal,
                'nombre_original' => (string) $nombreOriginal,
                'extension' => $extension,
                'size' => (int) ($sizes[$i] ?? 0),
            ];
        }

        return array_values($fuentes);
    }

    public function documentosManualDesdePost(array $post): array
    {
        $manuales = $post['documentos_manual'] ?? [];
        if (!is_array($manuales)) {
            return [];
        }

        $out = [];
        foreach ($manuales as $sourceIndex => $idDocumento) {
            $sourceIndex = (int) $sourceIndex;
            $idDocumento = (int) $idDocumento;
            if ($sourceIndex >= 0 && $idDocumento > 0) {
                $out[$sourceIndex] = $idDocumento;
            }
        }

        return $out;
    }

    public function crearLoteTemporal(array $fuentes): array
    {
        $this->limpiarLotesTemporales();
        $batchId = bin2hex(random_bytes(16));
        $dir = $this->directorioLote($batchId);
        SecureUpload::ensureDir($dir);

        $zipCopies = [];
        $fuentesPersistentes = [];
        foreach ($fuentes as $idx => $fuente) {
            $persistente = $fuente;
            $tipo = (string) ($fuente['tipo'] ?? '');

            if ($tipo === 'upload') {
                $src = (string) ($fuente['tmp'] ?? '');
                if ($src === '' || !is_file($src)) {
                    continue;
                }
                $dest = $dir . DIRECTORY_SEPARATOR . 'src_' . $idx . '.' . $this->extensionFuente($fuente);
                if (!@copy($src, $dest)) {
                    continue;
                }
                $persistente['tmp'] = $dest;
                $persistente['cached'] = true;
            } elseif ($tipo === 'zip' || $tipo === 'zip_nested') {
                $zipTmp = (string) ($fuente['zip_tmp'] ?? '');
                if ($zipTmp === '' || !is_file($zipTmp)) {
                    continue;
                }
                if (!isset($zipCopies[$zipTmp])) {
                    $dest = $dir . DIRECTORY_SEPARATOR . 'zip_' . count($zipCopies) . '.zip';
                    if (!@copy($zipTmp, $dest)) {
                        continue;
                    }
                    $zipCopies[$zipTmp] = $dest;
                }
                $persistente['zip_tmp'] = $zipCopies[$zipTmp];
                $persistente['cached'] = true;
            }

            $fuentesPersistentes[] = $persistente;
        }

        if (empty($fuentesPersistentes)) {
            $this->eliminarDirectorio($dir);
            throw new \RuntimeException('No se pudo preparar el lote temporal de documentos.');
        }

        $manifest = [
            'batch_id' => $batchId,
            'created_at' => time(),
            'fuentes' => $fuentesPersistentes,
        ];
        file_put_contents($dir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($manifest, JSON_UNESCAPED_UNICODE));

        return [
            'batch_id' => $batchId,
            'fuentes' => $fuentesPersistentes,
        ];
    }

    public function fuentesDesdeLoteTemporal(string $batchId): array
    {
        $batchId = trim($batchId);
        if (!preg_match('/^[a-f0-9]{32}$/', $batchId)) {
            return [];
        }

        $manifestPath = $this->directorioLote($batchId) . DIRECTORY_SEPARATOR . 'manifest.json';
        if (!is_file($manifestPath)) {
            return [];
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (!is_array($manifest)) {
            return [];
        }

        $createdAt = (int) ($manifest['created_at'] ?? 0);
        if ($createdAt > 0 && (time() - $createdAt) > self::BATCH_TTL_SECONDS) {
            $this->eliminarLoteTemporal($batchId);
            return [];
        }

        $fuentes = $manifest['fuentes'] ?? [];
        return is_array($fuentes) ? array_values($fuentes) : [];
    }

    public function eliminarLoteTemporal(string $batchId): void
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $batchId)) {
            return;
        }
        $this->eliminarDirectorio($this->directorioLote($batchId));
    }

    public function analizar(array $fuentes, array $documentosManual = []): array
    {
        $resPersonas = CapHumDAO::getPersonasParaImportacionDocumentos();
        if (empty($resPersonas['success'])) {
            throw new \RuntimeException($resPersonas['mensaje'] ?? 'No se pudo obtener la lista de personas.');
        }

        $personas = $this->prepararPersonas($resPersonas['datos'] ?? []);
        $resCatalogo = CapHumDAO::getCatalogoDocumentosImportacion();
        $catalogo = $this->prepararCatalogo($resCatalogo['datos'] ?? []);

        $items = [];
        foreach ($fuentes as $idx => $fuente) {
            [$carpetaPersona, $contextoDocumento] = $this->personaYContexto(
                (string) ($fuente['ruta_relativa'] ?? ''),
                (string) ($fuente['zip_nombre'] ?? '')
            );

            $match = $this->buscarPersona($carpetaPersona, $personas);
            $doc = $this->clasificarDocumento($contextoDocumento, $catalogo);
            $documentoOtrosAutomatico = false;
            $documentoManual = false;
            $idManual = (int) ($documentosManual[$idx] ?? 0);
            if ($idManual > 0 && isset($catalogo[$idManual])) {
                $doc = $catalogo[$idManual];
                $documentoManual = true;
            } elseif (!$doc && ($otros = $this->documentoOtros($catalogo))) {
                $doc = $otros;
                $documentoOtrosAutomatico = true;
            }

            $estado = 'listo';
            $razon = 'Listo para importar.';
            if ($documentoManual) {
                $razon = 'Tipo seleccionado manualmente.';
            } elseif ($documentoOtrosAutomatico) {
                $razon = 'Tipo no reconocido; se guardara como Otros.';
            }
            if (empty($match['encontrada'])) {
                $estado = 'persona_no_encontrada';
                $razon = 'No se encontro persona compatible.';
            } elseif (empty($match['segura'])) {
                $estado = 'persona_ambigua';
                $razon = 'La coincidencia de persona requiere revision: hay nombres parecidos o faltan datos para confirmarla.';
            } elseif (!$doc) {
                $estado = 'documento_no_reconocido';
                $razon = 'No se reconocio el tipo de documento.';
            } elseif (!$this->puedeUsarTipoDocumentoRrhh((int) ($doc['id'] ?? 0))) {
                $estado = 'documento_sin_permiso';
                $razon = 'No tienes permiso para importar este tipo de documento.';
            }

            $mejor = $match['mejor'] ?? null;
            $items[] = [
                'source_index' => $idx,
                'ruta' => (string) ($fuente['ruta_relativa'] ?? ''),
                'archivo' => (string) ($fuente['nombre_original'] ?? ''),
                'extension' => $this->extensionFuente($fuente),
                'size' => (int) ($fuente['size'] ?? 0),
                'carpeta_persona' => $carpetaPersona,
                'id_persona' => $mejor ? (int) ($mejor['id'] ?? 0) : null,
                'persona' => $mejor ? (string) ($mejor['nombre'] ?? '') : '',
                'numero_empleado' => $mejor ? (string) ($mejor['numero_empleado'] ?? '') : '',
                'estatus_persona' => $mejor ? (string) ($mejor['estatus'] ?? '') : '',
                'persona_activa' => $mejor ? (bool) ($mejor['activa'] ?? false) : null,
                'fecha_baja' => $mejor ? (string) ($mejor['fecha_baja'] ?? '') : '',
                'score_persona' => $mejor ? (float) ($mejor['score'] ?? 0) : 0,
                'alternativas' => $match['alternativas'] ?? [],
                'id_documento' => $doc ? (int) ($doc['id'] ?? 0) : null,
                'documento' => $doc ? (string) ($doc['nombre'] ?? '') : '',
                'documento_clave' => $doc ? (string) ($doc['clave'] ?? '') : '',
                'documento_manual' => $documentoManual,
                'documento_otros_automatico' => $documentoOtrosAutomatico,
                'estado' => $estado,
                'razon' => $razon,
            ];
        }

        $items = $this->marcarExistentesYDuplicados($items);

        return [
            'items' => $items,
            'resumen' => $this->resumen($items),
            'catalogo' => $this->catalogoParaRespuesta($catalogo),
        ];
    }

    public function importar(array $fuentes, array $documentosManual = []): array
    {
        $analisis = $this->analizar($fuentes, $documentosManual);
        $items = $analisis['items'] ?? [];
        $importados = 0;
        $errores = 0;

        foreach ($items as &$item) {
            if (($item['estado'] ?? '') !== 'listo') {
                continue;
            }

            $sourceIndex = (int) ($item['source_index'] ?? -1);
            if (!isset($fuentes[$sourceIndex])) {
                $item['estado'] = 'error';
                $item['razon'] = 'No se encontro el archivo fuente en la peticion.';
                $errores++;
                continue;
            }

            $guardado = $this->guardarFuente(
                $fuentes[$sourceIndex],
                (int) ($item['id_persona'] ?? 0),
                (int) ($item['id_documento'] ?? 0)
            );

            if (!empty($guardado['success'])) {
                $item['estado'] = 'importado';
                $item['razon'] = 'Documento importado correctamente.';
                $item['archivo_guardado'] = $guardado['archivo'] ?? '';
                $importados++;
            } else {
                $item['estado'] = 'error';
                $item['razon'] = $guardado['mensaje'] ?? 'No se pudo importar el documento.';
                $errores++;
            }
        }
        unset($item);

        $analisis['items'] = $items;
        $analisis['resumen'] = $this->resumen($items);
        $analisis['importados'] = $importados;
        $analisis['errores_importacion'] = $errores;

        return $analisis;
    }

    public function analizarParaPersona(array $fuentes, array $personaPerfil, array $documentosManual = []): array
    {
        $persona = $this->prepararPersonaPerfil($personaPerfil);
        if (empty($persona['id'])) {
            throw new \RuntimeException('No se pudo identificar al colaborador del perfil.');
        }

        $resCatalogo = CapHumDAO::getCatalogoDocumentosImportacion();
        $catalogo = $this->prepararCatalogo($resCatalogo['datos'] ?? []);

        $items = [];
        foreach ($fuentes as $idx => $fuente) {
            [$carpetaPersona, $contextoDocumento] = $this->personaYContexto(
                (string) ($fuente['ruta_relativa'] ?? ''),
                (string) ($fuente['zip_nombre'] ?? '')
            );

            $tieneCarpetaPersona = $this->rutaTieneCarpetaPersona((string) ($fuente['ruta_relativa'] ?? ''));
            $match = [
                'encontrada' => true,
                'segura' => true,
                'mejor' => $persona,
                'alternativas' => [],
            ];
            if ($tieneCarpetaPersona) {
                $matchCarpeta = $this->buscarPersona($carpetaPersona, [$persona]);
                $match['segura'] = !empty($matchCarpeta['encontrada']) && !empty($matchCarpeta['segura']);
                $match['alternativas'] = $matchCarpeta['alternativas'] ?? [];
                if (!empty($matchCarpeta['mejor'])) {
                    $match['mejor'] = $matchCarpeta['mejor'];
                }
            } else {
                $carpetaPersona = (string) ($persona['nombre'] ?? 'Mi perfil');
            }

            $doc = $this->clasificarDocumento($contextoDocumento, $catalogo);
            $documentoOtrosAutomatico = false;
            $documentoManual = false;
            $idManual = (int) ($documentosManual[$idx] ?? 0);
            if ($idManual > 0 && isset($catalogo[$idManual])) {
                $doc = $catalogo[$idManual];
                $documentoManual = true;
            } elseif (!$doc && ($otros = $this->documentoOtros($catalogo))) {
                $doc = $otros;
                $documentoOtrosAutomatico = true;
            }

            $estado = 'listo';
            $razon = 'Listo para importar a tu expediente.';
            if ($documentoManual) {
                $razon = 'Tipo seleccionado manualmente.';
            } elseif ($documentoOtrosAutomatico) {
                $razon = 'Tipo no reconocido; se guardara como Otros.';
            }
            if ($tieneCarpetaPersona && empty($match['segura'])) {
                $estado = 'persona_no_coincide';
                $razon = 'La carpeta "' . $carpetaPersona . '" no coincide con tu perfil. Revisa que la carpeta pertenezca a ' . (string) ($persona['nombre'] ?? 'tu usuario') . '.';
            } elseif (!$doc) {
                $estado = 'documento_no_reconocido';
                $razon = 'No se reconocio el tipo de documento.';
            }

            $mejor = $match['mejor'] ?? null;
            $items[] = [
                'source_index' => $idx,
                'ruta' => (string) ($fuente['ruta_relativa'] ?? ''),
                'archivo' => (string) ($fuente['nombre_original'] ?? ''),
                'extension' => $this->extensionFuente($fuente),
                'size' => (int) ($fuente['size'] ?? 0),
                'carpeta_persona' => $carpetaPersona,
                'id_persona' => $mejor ? (int) ($mejor['id'] ?? 0) : null,
                'persona' => $mejor ? (string) ($mejor['nombre'] ?? '') : '',
                'numero_empleado' => $mejor ? (string) ($mejor['numero_empleado'] ?? '') : '',
                'estatus_persona' => $mejor ? (string) ($mejor['estatus'] ?? '') : '',
                'persona_activa' => $mejor ? (bool) ($mejor['activa'] ?? false) : null,
                'fecha_baja' => $mejor ? (string) ($mejor['fecha_baja'] ?? '') : '',
                'score_persona' => $tieneCarpetaPersona && !empty($match['alternativas'][0])
                    ? (float) ($match['alternativas'][0]['score'] ?? 0)
                    : 100,
                'alternativas' => $match['alternativas'] ?? [],
                'id_documento' => $doc ? (int) ($doc['id'] ?? 0) : null,
                'documento' => $doc ? (string) ($doc['nombre'] ?? '') : '',
                'documento_clave' => $doc ? (string) ($doc['clave'] ?? '') : '',
                'documento_manual' => $documentoManual,
                'documento_otros_automatico' => $documentoOtrosAutomatico,
                'estado' => $estado,
                'razon' => $razon,
            ];
        }

        $items = $this->marcarExistentesYDuplicados($items);

        return [
            'items' => $items,
            'resumen' => $this->resumen($items),
            'catalogo' => $this->catalogoParaRespuesta($catalogo, false),
        ];
    }

    public function importarParaPersona(array $fuentes, array $personaPerfil, array $documentosManual = []): array
    {
        $analisis = $this->analizarParaPersona($fuentes, $personaPerfil, $documentosManual);
        $items = $analisis['items'] ?? [];
        $importados = 0;
        $errores = 0;

        foreach ($items as &$item) {
            if (($item['estado'] ?? '') !== 'listo') {
                continue;
            }

            $sourceIndex = (int) ($item['source_index'] ?? -1);
            if (!isset($fuentes[$sourceIndex])) {
                $item['estado'] = 'error';
                $item['razon'] = 'No se encontro el archivo fuente en la peticion.';
                $errores++;
                continue;
            }

            $guardado = $this->guardarFuente(
                $fuentes[$sourceIndex],
                (int) ($item['id_persona'] ?? 0),
                (int) ($item['id_documento'] ?? 0)
            );

            if (!empty($guardado['success'])) {
                $item['estado'] = 'importado';
                $item['razon'] = 'Documento importado correctamente.';
                $item['archivo_guardado'] = $guardado['archivo'] ?? '';
                $importados++;
            } else {
                $item['estado'] = 'error';
                $item['razon'] = $guardado['mensaje'] ?? 'No se pudo importar el documento.';
                $errores++;
            }
        }
        unset($item);

        $analisis['items'] = $items;
        $analisis['resumen'] = $this->resumen($items);
        $analisis['importados'] = $importados;
        $analisis['errores_importacion'] = $errores;

        return $analisis;
    }

    public function obtenerPdfTemporal(array $fuentes, int $sourceIndex): array
    {
        if (!isset($fuentes[$sourceIndex])) {
            return ['success' => false, 'mensaje' => 'No se encontro el archivo seleccionado.'];
        }

        $fuente = $fuentes[$sourceIndex];
        $tmp = null;
        $limpiar = false;
        if (($fuente['tipo'] ?? '') === 'upload') {
            $tmp = (string) ($fuente['tmp'] ?? '');
        } elseif (($fuente['tipo'] ?? '') === 'zip' || ($fuente['tipo'] ?? '') === 'zip_nested') {
            $tmp = $this->extraerZipATemporal($fuente);
            $limpiar = true;
        }

        if (!$tmp || !is_file($tmp) || !SecureUpload::validateMime($tmp, SecureUpload::MIME_PDF)) {
            if ($limpiar && $tmp && is_file($tmp)) {
                @unlink($tmp);
            }
            return ['success' => false, 'mensaje' => 'El archivo no parece ser un PDF valido.'];
        }

        return [
            'success' => true,
            'path' => $tmp,
            'limpiar' => $limpiar,
            'nombre' => (string) ($fuente['nombre_original'] ?? 'documento.pdf'),
        ];
    }

    private function fuentesDesdeZip(
        string $tmp,
        string $zipNombre,
        string $rutaPrefix = '',
        int $depth = 0,
        array $entryChain = [],
        ?string $rootTmp = null
    ): array
    {
        if (!class_exists('\ZipArchive')) {
            throw new \RuntimeException('La extension ZipArchive de PHP no esta disponible.');
        }

        $rootTmp = $rootTmp ?: $tmp;
        $zip = new \ZipArchive();
        if ($zip->open($tmp) !== true) {
            return [];
        }

        $fuentes = [];
        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (!$stat || empty($stat['name'])) {
                    continue;
                }
                $entryName = str_replace('\\', '/', (string) $stat['name']);
                if (substr($entryName, -1) === '/') {
                    continue;
                }
                $extension = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
                if ($extension === 'zip' && $depth < 3) {
                    $nestedTmp = $this->extraerEntradaZipATemporal($tmp, $entryName);
                    if (!$nestedTmp) {
                        continue;
                    }
                    try {
                        $nestedPrefix = trim($rutaPrefix !== '' ? $rutaPrefix . '/' . basename($entryName) : basename($entryName), '/');
                        $fuentes = array_merge(
                            $fuentes,
                            $this->fuentesDesdeZip(
                                $nestedTmp,
                                basename($entryName),
                                $nestedPrefix,
                                $depth + 1,
                                array_merge($entryChain, [$entryName]),
                                $rootTmp
                            )
                        );
                    } finally {
                        @unlink($nestedTmp);
                    }
                    continue;
                }
                if (!in_array($extension, ['pdf', 'fad'], true)) {
                    continue;
                }
                $chain = array_merge($entryChain, [$entryName]);
                $rutaRelativa = trim($rutaPrefix !== '' ? $rutaPrefix . '/' . $entryName : $entryName, '/');
                $fuentes[] = [
                    'tipo' => count($chain) > 1 ? 'zip_nested' : 'zip',
                    'zip_tmp' => $rootTmp,
                    'zip_nombre' => $zipNombre,
                    'entry' => $entryName,
                    'entry_chain' => $chain,
                    'ruta_relativa' => $rutaRelativa,
                    'nombre_original' => basename($entryName),
                    'extension' => $extension,
                    'size' => (int) ($stat['size'] ?? 0),
                ];
            }
        } finally {
            $zip->close();
        }

        return $fuentes;
    }

    private function prepararPersonas(array $personas): array
    {
        $out = [];
        foreach ($personas as $persona) {
            $nombre = trim(implode(' ', array_filter([
                $persona['nombres'] ?? '',
                $persona['segundo_nombre'] ?? '',
                $persona['apellidop'] ?? '',
                $persona['apellidom'] ?? '',
            ], static fn($v) => trim((string) $v) !== '')));
            $norm = $this->normalizarTexto($nombre);
            $tokens = $this->tokens($norm);
            sort($tokens);
            $estatus = trim((string) ($persona['estatus'] ?? ''));
            $estatusNorm = $this->normalizarTexto($estatus);
            $out[] = [
                'id' => (int) ($persona['id'] ?? 0),
                'numero_empleado' => (string) ($persona['numero_empleado'] ?? ''),
                'nombre' => $nombre,
                'estatus' => $estatus,
                'activa' => $estatusNorm !== 'baja',
                'fecha_baja' => (string) ($persona['fecha_baja'] ?? ''),
                'norm' => $norm,
                'tokens' => $tokens,
                'token_key' => implode(' ', $tokens),
            ];
        }

        return $out;
    }

    private function prepararPersonaPerfil(array $persona): array
    {
        $nombre = trim((string) ($persona['nombre_completo'] ?? ''));
        if ($nombre === '') {
            $nombre = trim(implode(' ', array_filter([
                $persona['nombres'] ?? '',
                $persona['segundo_nombre'] ?? '',
                $persona['apellidop'] ?? '',
                $persona['apellidom'] ?? '',
            ], static fn($v) => trim((string) $v) !== '')));
        }

        $norm = $this->normalizarTexto($nombre);
        $tokens = $this->tokens($norm);
        sort($tokens);
        $estatus = trim((string) ($persona['estatus'] ?? ''));
        $estatusNorm = $this->normalizarTexto($estatus);

        return [
            'id' => (int) ($persona['id'] ?? $persona['id_persona'] ?? 0),
            'numero_empleado' => (string) ($persona['numero_empleado'] ?? $persona['codigo_contpac'] ?? ''),
            'nombre' => $nombre,
            'estatus' => $estatus,
            'activa' => $estatusNorm !== 'baja',
            'fecha_baja' => (string) ($persona['fecha_baja'] ?? ''),
            'norm' => $norm,
            'tokens' => $tokens,
            'token_key' => implode(' ', $tokens),
        ];
    }

    private function prepararCatalogo(array $documentos): array
    {
        $byId = [];
        foreach ($documentos as $doc) {
            $id = (int) ($doc['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $byId[$id] = [
                'id' => $id,
                'clave' => (string) ($doc['clave'] ?? ''),
                'nombre' => (string) ($doc['nombre'] ?? ('Documento ' . $id)),
            ];
        }

        $fallback = [
            8 => 'CURP',
            9 => 'Identificacion Oficial (INE)',
            10 => 'RFC',
            11 => 'Comprobante de Domicilio',
            12 => 'Acta de Nacimiento',
            13 => 'Certificado de Estudios',
            14 => 'Referencias Laborales',
            15 => 'Documento baja',
            16 => 'Documento reingreso',
            17 => 'Solicitud interna',
            18 => 'CV o solicitud de trabajo',
            22 => 'Constancia de situacion fiscal',
            23 => 'Numero de seguridad social',
            24 => 'Hoja de retencion FONACOT o INFONAVIT',
            25 => 'Estado de cuenta',
            28 => 'Contrato firmado',
            29 => 'Archivo .FAD',
            30 => 'Validacion SAT',
            31 => 'Llave vector',
            32 => 'Prueba centavo',
            33 => 'Semanas cotizadas IMSS (segundos patrones)',
            34 => 'Documento incapacidad',
            35 => 'Documento permiso',
            36 => 'Documento falta',
        ];
        foreach ($fallback as $id => $nombre) {
            if (!isset($byId[$id])) {
                $byId[$id] = ['id' => $id, 'clave' => '', 'nombre' => $nombre];
            }
        }

        return $byId;
    }

    private function catalogoParaRespuesta(array $catalogo, bool $filtrarPermisos = true): array
    {
        $out = $filtrarPermisos
            ? array_values(array_filter($catalogo, function ($doc) {
                return $this->puedeUsarTipoDocumentoRrhh((int) ($doc['id'] ?? 0));
            }))
            : array_values($catalogo);
        usort($out, static fn($a, $b) => strcasecmp((string) ($a['nombre'] ?? ''), (string) ($b['nombre'] ?? '')));
        return $out;
    }

    private function documentoOtros(array $catalogo): ?array
    {
        foreach ($catalogo as $doc) {
            if (strtoupper((string) ($doc['clave'] ?? '')) === 'OTROS') {
                return $doc;
            }
            if ($this->normalizarTexto((string) ($doc['nombre'] ?? '')) === 'otros') {
                return $doc;
            }
        }

        return null;
    }

    private function personaYContexto(string $ruta, string $zipNombre = ''): array
    {
        $ruta = str_replace('\\', '/', trim($ruta, "/\\ \t\n\r\0\x0B"));
        $segmentos = array_values(array_filter(explode('/', $ruta), static fn($v) => trim($v) !== ''));
        $genericos = ['documentos' => true, 'documentacion' => true, 'expedientes' => true, 'expediente' => true, 'rrhh' => true, 'recursos humanos' => true];

        $rootIndex = 0;
        if (count($segmentos) > 1) {
            $primero = $this->normalizarTexto($segmentos[0]);
            if (isset($genericos[$primero])) {
                $rootIndex = 1;
            }
            if ($rootIndex === 0 && count($segmentos) > 2 && $this->esCarpetaRaizLote($segmentos[0], $segmentos[1])) {
                $rootIndex = 1;
            }
        }

        $persona = $segmentos[$rootIndex] ?? pathinfo($zipNombre ?: $ruta, PATHINFO_FILENAME);
        $contexto = implode(' ', array_slice($segmentos, $rootIndex + 1));
        if ($contexto === '') {
            $contexto = basename($ruta);
        }

        return [$this->limpiarNombreCarpeta($persona), $contexto];
    }

    private function rutaTieneCarpetaPersona(string $ruta): bool
    {
        $ruta = str_replace('\\', '/', trim($ruta, "/\\ \t\n\r\0\x0B"));
        $segmentos = array_values(array_filter(explode('/', $ruta), static fn($v) => trim($v) !== ''));
        if (count($segmentos) <= 1) {
            return false;
        }

        $genericos = ['documentos' => true, 'documentacion' => true, 'expedientes' => true, 'expediente' => true, 'rrhh' => true, 'recursos humanos' => true];
        $primero = $this->normalizarTexto($segmentos[0] ?? '');
        if (isset($genericos[$primero])) {
            return count($segmentos) > 2;
        }

        return !preg_match('/\.(pdf|fad)$/i', (string) ($segmentos[0] ?? ''));
    }

    private function esCarpetaRaizLote(string $primerSegmento, string $segundoSegmento): bool
    {
        $primero = $this->normalizarTexto($primerSegmento);
        $segundo = $this->normalizarTexto($segundoSegmento);
        if ($primero === '' || $segundo === '') {
            return false;
        }
        if (preg_match('/\.(pdf|zip)$/i', $segundoSegmento)) {
            return false;
        }

        $meses = 'enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|setiembre|octubre|noviembre|diciembre';
        if (preg_match('/\b(qna|quincena|nomina|lote|expediente|expedientes|documentos|documentacion|' . $meses . ')\b/u', $primero)) {
            return true;
        }

        return (bool) preg_match('/^\d+\s+[a-z]+(?:\s+[a-z]+){1,}$/u', $segundo);
    }

    private function limpiarNombreCarpeta(string $nombre): string
    {
        $nombre = preg_replace('/\.zip$/i', '', trim($nombre)) ?? trim($nombre);
        $nombre = preg_replace('/-\d{8}T\d{6}Z(?:-\d+)*$/i', '', $nombre) ?? $nombre;
        $nombre = preg_replace('/^\s*\d+\s*[\.\-_]?\s*/u', '', $nombre) ?? $nombre;
        return trim($nombre);
    }

    private function buscarPersona(string $nombreCarpeta, array $personas): array
    {
        $targetNorm = $this->normalizarTexto($nombreCarpeta);
        $tokensIgnorados = [
            'no' => true,
            'num' => true,
            'numero' => true,
            'empleado' => true,
            'codigo' => true,
            'contpac' => true,
            'external' => true,
            'externo' => true,
            'id' => true,
            'mxk' => true,
            'reingreso' => true,
            'reingresos' => true,
            'baja' => true,
            'bajas' => true,
            'alta' => true,
            'altas' => true,
            'documento' => true,
            'documentos' => true,
            'expediente' => true,
            'expedientes' => true,
            'rrhh' => true,
            'recursos' => true,
            'humanos' => true,
        ];
        $targetTokens = array_values(array_filter(
            $this->tokens($targetNorm),
            static fn($token) => !isset($tokensIgnorados[$token])
        ));
        sort($targetTokens);
        $targetKey = implode(' ', $targetTokens);

        if ($targetNorm === '' || empty($targetTokens)) {
            return ['encontrada' => false, 'segura' => false, 'mejor' => null, 'alternativas' => []];
        }

        $candidatos = [];
        foreach ($personas as $persona) {
            $scoreSimilar = 0.0;
            similar_text($targetNorm, (string) ($persona['norm'] ?? ''), $scoreSimilar);
            $tokensPersona = $persona['tokens'] ?? [];
            $common = count(array_intersect($targetTokens, $tokensPersona));
            $scoreTokens = count($targetTokens) > 0 ? ($common / count($targetTokens)) * 100 : 0;
            $scorePersonaTokens = count($tokensPersona) > 0 ? ($common / count($tokensPersona)) * 100 : 0;
            $score = max((float) $scoreSimilar, (float) $scoreTokens, (float) $scorePersonaTokens);
            if ($targetKey !== '' && $targetKey === ($persona['token_key'] ?? '')) {
                $score = 100.0;
            }
            $numeroEmpleado = preg_replace('/\D+/', '', (string) ($persona['numero_empleado'] ?? ''));
            if ($numeroEmpleado !== '' && in_array($numeroEmpleado, $targetTokens, true)) {
                $score = max($score, 99.0);
            }
            if ($score >= 82) {
                $candidatos[] = [
                    'id' => (int) ($persona['id'] ?? 0),
                    'numero_empleado' => (string) ($persona['numero_empleado'] ?? ''),
                    'nombre' => (string) ($persona['nombre'] ?? ''),
                    'estatus' => (string) ($persona['estatus'] ?? ''),
                    'activa' => (bool) ($persona['activa'] ?? false),
                    'fecha_baja' => (string) ($persona['fecha_baja'] ?? ''),
                    'score' => round($score, 1),
                ];
            }
        }

        usort($candidatos, static fn($a, $b) => ($b['score'] <=> $a['score']));
        $mejor = $candidatos[0] ?? null;
        $segundo = $candidatos[1] ?? null;
        $segura = false;
        if ($mejor) {
            $score = (float) ($mejor['score'] ?? 0);
            $delta = $segundo ? $score - (float) ($segundo['score'] ?? 0) : 100;
            $segura = $score >= 96 || ($score >= 92 && $delta >= 8);
        }

        return [
            'encontrada' => $mejor !== null,
            'segura' => $segura,
            'mejor' => $mejor,
            'alternativas' => array_slice($candidatos, 0, 3),
        ];
    }

    private function clasificarDocumento(string $contexto, array $catalogo): ?array
    {
        $id = $this->detectarTipoDocumentoPorPuntaje($contexto);

        if ($id === null) {
            return null;
        }

        return $catalogo[$id] ?? ['id' => $id, 'nombre' => 'Documento ' . $id, 'clave' => ''];
    }

    private function detectarTipoDocumentoPorPuntaje(string $contexto): ?int
    {
        $extension = strtolower(pathinfo($contexto, PATHINFO_EXTENSION));
        $texto = $this->textoDocumentoParaDeteccion($contexto);
        $scores = [];

        $add = static function (int $id, int $score) use (&$scores): void {
            $scores[$id] = ($scores[$id] ?? 0) + $score;
        };
        $has = static fn(string $pattern): bool => (bool) preg_match($pattern, $texto);

        if ($extension === 'fad' || $has('/\bfad\b|\barchivo\s+fad\b|\bfirma\s+fad\b/')) {
            $add(self::DOCUMENTO_ARCHIVO_FAD, 140);
        }

        $reglas = [
            8 => [
                100 => ['/(\bcurp\b|\bclave\s+unica\s+de\s+registro\s+de\s+poblacion\b)/'],
            ],
            9 => [
                95 => ['/(\bine\b|\bife\b|\binstituto\s+nacional\s+electoral\b|\bcredencial\s+para\s+votar\b|\bidentificacion\s+oficial\b|\bidentificaci[oó]n\s+oficial\b|\bpasaporte\b|\bfm2\b|\bfm3\b|\bcedula\s+profesional\b)/'],
            ],
            10 => [
                85 => ['/(\brfc\b|\bregistro\s+federal\s+de\s+contribuyentes\b)/'],
            ],
            11 => [
                100 => ['/(\bcomprobante\s+de\s+domicilio\b|\bdomicilio\b|\bdomiicilio\b|\bdomicilo\b|\bdomiciio\b|\brecibo\s+cfe\b|\bcfe\b|\btelmex\b|\bpredial\b|\bagua\b|\bluz\b|\bcomprobante\s+localizacion\b|\blocalizacion\b)/'],
            ],
            12 => [
                100 => ['/(\bacta\s+de\s+nacimiento\b|\bacta\b.*\bnacimiento\b|\bacta\b.*\bnacimeinto\b|\bacta\b.*\bnacimento\b|\bacta\b.*\bnacimineto\b|\bnacimiento\s+certificad[ao]\b|\bacta\s+nac\b)/'],
            ],
            13 => [
                85 => ['/(\bcertificado\s+de\s+estudios\b|\bcertificado\b.*\bestudios\b|\bconstancia\s+de\s+estudios\b|\btitulo\b|\bt[ií]tulo\b|\bcedula\s+profesional\b|\bc[eé]dula\s+profesional\b|\bestudios\b|\bescolaridad\b)/'],
            ],
            14 => [
                80 => ['/(\breferencias?\s+laborales?\b|\bcarta\s+laboral\b|\breferencia\b)/'],
            ],
            15 => [
                125 => ['/(\brenuncia\s+firmada\b|\brenuncia\s+frimada\b|\bfiniquito\b|\bfinquito\b|\bdefiniquito\b|\bfiniq\b|\bcomprobante\s+de\s+deposito\s+de\s+finiq|\bdeposito\b.*\bfiniq|\bconciliacion\b|\bconvenio\b|\brescisi[oó]n\b|\bresicision\b|\bdocumento\s+de\s+baja\b|\bdocumento\s+baja\b)/'],
            ],
            16 => [
                90 => ['/(\breingreso\b|\breingresos\b|\bdocumento\s+reingreso\b)/'],
            ],
            17 => [
                105 => ['/(\bsolicitud\s+interna\b|\bsolicitud\s+__SPARTA_SECRET_REDACTED__\b|\bsolicitud\s+de\s+empleo\s+__SPARTA_SECRET_REDACTED__\b|\bsolicitud\s+empleo\s+__SPARTA_SECRET_REDACTED__\b|\bsolicitud\s+mk\b|\bsolicitud_interna\b|\bsolicitud\s+intern[ao]\b)/'],
                80 => ['/(\bsolicitud\b|\bsoicitud\b|\bsolictud\b|\bsolicitu\b)/'],
            ],
            18 => [
                95 => ['/(\bcv\b|\bcurriculum\b|\bcurriculo\b|\bcurr[ií]culum\b|\bcurriculum\s+vitae\b|\bsolicitud\s+de\s+trabajo\b|\bsolicitud\s+trabajo\b|\bresume\b)/'],
            ],
            22 => [
                125 => ['/(\bconstancia\s+de\s+situacion\s+fiscal\b|\bconstancia\s+situacion\s+fiscal\b|\bsituacion\s+fiscal\b|\bc[eé]dula\s+de\s+identificaci[oó]n\s+fiscal\b|\bcif\b|\bcsf\b|\bsat\b)/'],
            ],
            23 => [
                110 => ['/(\bnss\b|\binss\b|\btarjeta\s*nss\d*\b|\bnss\d+\b|\bnumero\s+de\s+seguridad\s+social\b|\bn[uú]mero\s+de\s+seguridad\s+social\b|\bseguridad\s+social\b|\bimss\b.*\bnss\b)/'],
            ],
            24 => [
                105 => ['/(\bfonacot\b|\binfonavit\b|\bretenci[oó]n\b|\bhoja\s+de\s+retenci[oó]n\b|\bno\s+adeudo\b|\bno\s+adeudos\b|\bcarta\s+de\s+no\s+adeudo\b|\bcarta\s+de\s+no\s+adeudos\b|\bno\s+credito\b|\bno\s+creditos\b|\bno\s+creditps\b|\bsin\s+credito\b|\bsin\s+adeudo\b)/'],
            ],
            25 => [
                105 => ['/(\bestado\s+de\s+cuenta\b|\bedo\s+de\s+cuenta\b|\bedo\s+cuenta\b|\bcuenta\s+bancaria\b|\bclabe\b|\bclave\s+interbancaria\b|\bbanco\b|\bbbva\b|\bbancomer\b|\bbanorte\b|\bsantander\b|\bbanamex\b|\bcitibanamex\b|\bazteca\b|\bbajio\b|\bbanregio\b|\bhsbc\b|\bscotiabank\b|\binbursa\b|\bafirme\b|\bmultiva\b|\bmercado\s+pago\b|\bklar\b|\bnu\b)/'],
            ],
            28 => [
                115 => ['/(\bcontrato\s+firmado\b|\bcontrato\b|\bcontrat[a-z0-9]*\b|\bcontratpo\b|\bcontato\b|\bcontra\s+to\b|\bcontra\s+ta\b)/'],
            ],
            30 => [
                105 => ['/(\bvalidaci[oó]n\s+sat\b|\bvalidacion\s+rfc\b|\bopini[oó]n\s+sat\b|\bopinion\s+sat\b|\bvalidacion\s+fiscal\b|\bvalidaci[oó]n\s+fiscal\b|\b32d\b|\bcumplimiento\s+sat\b)/'],
                80 => ['/(\bvalidacion\b)/'],
            ],
            31 => [
                95 => ['/(\bllave\s+vector\b|\bvector\b|\bkey\s+vector\b|\bllave\b.*\bfad\b)/'],
            ],
            32 => [
                125 => ['/(\bprueba\s+centavo\b|\bcentavo\b|\bdeposito\s+centavo\b|\bdep[oó]sito\s+centavo\b)/'],
            ],
            33 => [
                110 => ['/(\bsemanas?\s+cotizadas?\b|\bsemanas?\s+imss\b|\bconstancia\s+de\s+semanas\b|\bsemanas\s+del\s+asegurado\b|\bhistorial\s+laboral\b|\bsegundos?\s+patrones?\b)/'],
            ],
            34 => [
                100 => ['/(\bincapacidad\b|\bincapacidades\b|\bsubsidio\s+imss\b|\bcertificado\s+de\s+incapacidad\b)/'],
            ],
            35 => [
                85 => ['/(\bpermiso\b|\bpermisos\b|\bpermiso\s+laboral\b|\bpermiso\s+sin\s+goce\b|\bpermiso\s+con\s+goce\b)/'],
            ],
            36 => [
                90 => ['/(\bfalta\b|\bfaltas\b|\bjustificante\b|\bjustificaci[oó]n\s+de\s+falta\b|\bausencia\b|\bausencias\b)/'],
            ],
        ];

        foreach ($reglas as $id => $grupos) {
            foreach ($grupos as $score => $patterns) {
                foreach ($patterns as $pattern) {
                    if ($has($pattern)) {
                        $add((int)$id, (int)$score);
                    }
                }
            }
        }

        $this->aplicarAjustesDeteccionDocumento($texto, $scores);

        if (empty($scores)) {
            return null;
        }

        arsort($scores);
        $topId = (int) array_key_first($scores);
        $topScore = (int) reset($scores);
        $secondScore = (int) (array_values($scores)[1] ?? 0);

        if ($topScore < 70) {
            return null;
        }
        if ($topScore < 100 && ($topScore - $secondScore) < 20) {
            return null;
        }

        return $topId;
    }

    private function textoDocumentoParaDeteccion(string $contexto): string
    {
        $texto = str_replace(['_', '-', '.', '(', ')', '[', ']', '{', '}', '+'], ' ', $contexto);
        $texto = preg_replace('/\b\d{1,4}\b/u', ' ', $texto) ?? $texto;
        return $this->normalizarTexto($texto);
    }

    private function aplicarAjustesDeteccionDocumento(string $texto, array &$scores): void
    {
        $penalizar = static function (int $id, int $score) use (&$scores): void {
            if (isset($scores[$id])) {
                $scores[$id] -= $score;
                if ($scores[$id] <= 0) {
                    unset($scores[$id]);
                }
            }
        };

        if (preg_match('/\bconstancia\b.*\bfiscal\b|\bsituacion\s+fiscal\b|\bsat\b|\bcif\b|\bcsf\b/', $texto)) {
            $penalizar(25, 70);
            $penalizar(10, 35);
        }
        if (preg_match('/\bsolicitud\s+de\s+trabajo\b|\bcv\b|\bcurriculum\b|\bcurriculo\b/', $texto)) {
            $penalizar(17, 60);
        }
        if (preg_match('/\bsolicitud\s+interna\b|\bsolicitud\s+__SPARTA_SECRET_REDACTED__\b/', $texto)) {
            $penalizar(18, 50);
        }
        if (preg_match('/\bcomprobante\b.*\bdomicilio\b|\brecibo\s+cfe\b|\bcfe\b|\btelmex\b|\bpredial\b/', $texto)) {
            $penalizar(25, 60);
        }
        if (preg_match('/\bcomprobante\b.*\bdeposito\b.*\bfiniq|\bfiniquito\b|\brenuncia\b/', $texto)) {
            $penalizar(25, 70);
        }
        if (preg_match('/\bno\s+adeudo\b|\bfonacot\b|\binfonavit\b/', $texto)) {
            $penalizar(15, 35);
            $penalizar(25, 45);
        }
        if (preg_match('/\bimss\b/', $texto) && !preg_match('/\bnss\b|\bseguridad\s+social\b|\bsemanas?\b|\bincapacidad\b/', $texto)) {
            $penalizar(23, 35);
            $penalizar(33, 35);
            $penalizar(34, 35);
        }
    }

    private function marcarExistentesYDuplicados(array $items): array
    {
        $idsPersonas = array_values(array_unique(array_filter(array_map(static function ($item) {
            return (int) ($item['id_persona'] ?? 0);
        }, $items))));
        $existentes = CapHumDAO::getDocumentosPersonaIndex($idsPersonas);
        $indexExistentes = !empty($existentes['success']) && is_array($existentes['datos'] ?? null) ? $existentes['datos'] : [];

        $vistos = [];
        $personasConConstanciaEnLote = [];
        foreach ($items as $itemLote) {
            if (($itemLote['estado'] ?? '') !== 'listo') {
                continue;
            }
            $idPersonaLote = (int) ($itemLote['id_persona'] ?? 0);
            $idDocumentoLote = (int) ($itemLote['id_documento'] ?? 0);
            if ($idPersonaLote > 0 && $idDocumentoLote === self::DOCUMENTO_CONSTANCIA_FISCAL) {
                $personasConConstanciaEnLote[$idPersonaLote] = true;
            }
        }
        $multiplesPermitidos = [14 => true, 15 => true, 16 => true];
        foreach ($items as &$item) {
            if (($item['estado'] ?? '') !== 'listo') {
                continue;
            }
            $idPersona = (int) ($item['id_persona'] ?? 0);
            $idDocumento = (int) ($item['id_documento'] ?? 0);
            if ($idPersona <= 0 || $idDocumento <= 0) {
                continue;
            }
            $permiteMultiple = !empty($multiplesPermitidos[$idDocumento])
                || strtoupper((string) ($item['documento_clave'] ?? '')) === 'OTROS'
                || $this->normalizarTexto((string) ($item['documento'] ?? '')) === 'otros';
            if (!$permiteMultiple && $idDocumento === self::DOCUMENTO_RFC && !empty($indexExistentes[$idPersona][self::DOCUMENTO_CONSTANCIA_FISCAL])) {
                $item['estado'] = 'ya_existe';
                $item['razon'] = 'La Constancia de situacion fiscal ya cubre el RFC.';
                continue;
            }
            if (!$permiteMultiple && $idDocumento === self::DOCUMENTO_RFC && !empty($personasConConstanciaEnLote[$idPersona])) {
                $item['estado'] = 'duplicado_lote';
                $item['razon'] = 'En este lote hay Constancia de situacion fiscal; cubre el RFC.';
                continue;
            }
            if (!$permiteMultiple && !empty($indexExistentes[$idPersona][$idDocumento])) {
                $item['estado'] = 'ya_existe';
                $item['razon'] = 'La persona ya tiene este tipo de documento cargado.';
                continue;
            }
            $key = $idPersona . ':' . $idDocumento;
            if (!$permiteMultiple && isset($vistos[$key])) {
                $item['estado'] = 'duplicado_lote';
                $item['razon'] = 'Hay otro archivo del mismo tipo para esta persona en el lote.';
                continue;
            }
            $vistos[$key] = true;
        }
        unset($item);

        return $items;
    }

    private function guardarFuente(array $fuente, int $idPersona, int $idDocumento): array
    {
        if ($idPersona <= 0 || $idDocumento <= 0) {
            return ['success' => false, 'mensaje' => 'Persona o documento invalido.'];
        }

        $carpeta = ($idDocumento === 15) ? 'bajas' : (($idDocumento === 16) ? 'reingresos' : 'documentos');
        $directorio = sparta_uploads_join($carpeta) . DIRECTORY_SEPARATOR;
        SecureUpload::ensureDir($directorio);

        $extension = $this->extensionFuente($fuente);
        $permiteFad = in_array($idDocumento, [self::DOCUMENTO_CONTRATO_FIRMADO, self::DOCUMENTO_ARCHIVO_FAD, self::DOCUMENTO_LLAVE_VECTOR], true);
        $soloFad = $idDocumento === self::DOCUMENTO_ARCHIVO_FAD;
        if ($extension === 'fad') {
            if (!$permiteFad) {
                return ['success' => false, 'mensaje' => 'Este tipo de documento no permite archivo .FAD.'];
            }
            $extensionFinal = 'fad';
        } elseif ($extension === 'pdf') {
            if ($soloFad) {
                return ['success' => false, 'mensaje' => 'Archivo .FAD solo permite documentos con extension .fad.'];
            }
            $extensionFinal = 'pdf';
        } else {
            return ['success' => false, 'mensaje' => 'Tipo de archivo no permitido.'];
        }

        $nombreFinal = SecureUpload::generateSafeFilename($extensionFinal);
        $rutaFinal = $directorio . $nombreFinal;
        $tmpParaValidar = null;
        $limpiarTmp = false;

        if (($fuente['tipo'] ?? '') === 'upload') {
            $tmpParaValidar = (string) ($fuente['tmp'] ?? '');
        } elseif (($fuente['tipo'] ?? '') === 'zip' || ($fuente['tipo'] ?? '') === 'zip_nested') {
            $tmpParaValidar = $this->extraerZipATemporal($fuente);
            $limpiarTmp = true;
        }

        if (!$tmpParaValidar || !is_file($tmpParaValidar)) {
            if ($limpiarTmp && $tmpParaValidar && is_file($tmpParaValidar)) {
                @unlink($tmpParaValidar);
            }
            return ['success' => false, 'mensaje' => 'No se encontro el archivo temporal para importar.'];
        }

        $valido = $extensionFinal === 'fad'
            ? $this->validarArchivoFad($tmpParaValidar)
            : SecureUpload::validateMime($tmpParaValidar, SecureUpload::MIME_PDF);
        if (!$valido) {
            if ($limpiarTmp && $tmpParaValidar && is_file($tmpParaValidar)) {
                @unlink($tmpParaValidar);
            }
            return ['success' => false, 'mensaje' => $extensionFinal === 'fad' ? 'El archivo no parece ser un .FAD valido.' : 'El archivo no parece ser un PDF valido.'];
        }

        if (($fuente['tipo'] ?? '') === 'upload' && is_uploaded_file($tmpParaValidar)) {
            $movido = move_uploaded_file($tmpParaValidar, $rutaFinal);
        } else {
            $movido = @rename($tmpParaValidar, $rutaFinal);
            if (!$movido) {
                $movido = @copy($tmpParaValidar, $rutaFinal);
                if (!empty($fuente['cached']) || $limpiarTmp) {
                    @unlink($tmpParaValidar);
                }
            }
        }

        if (!$movido) {
            if ($limpiarTmp && $tmpParaValidar && is_file($tmpParaValidar)) {
                @unlink($tmpParaValidar);
            }
            return ['success' => false, 'mensaje' => 'No se pudo guardar el archivo en uploads.'];
        }

        if ($this->esDocumentoSensibleRrhh($idDocumento)) {
            try {
                $this->cifrarArchivoSensibleEnSitio($rutaFinal);
                CapHumDAO::registrarAuditoriaDocumentoSensible([
                    'id_usuario' => (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0),
                    'id_persona' => $idPersona,
                    'id_documento_carga' => 0,
                    'id_documento' => $idDocumento,
                    'archivo' => $nombreFinal,
                    'accion' => 'importar',
                    'resultado' => 'autorizado',
                    'ip' => $this->obtenerIpCliente(),
                    'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                    'fecha_hora' => date('Y-m-d H:i:s'),
                    'detalle' => 'Archivo sensible importado y cifrado en disco',
                ]);
            } catch (\Throwable $e) {
                @unlink($rutaFinal);
                return ['success' => false, 'mensaje' => $e->getMessage()];
            }
        }

        $resultado = CapHumDAO::guardarDocumentosPersona($idPersona, $idDocumento, [$nombreFinal]);
        if (empty($resultado['success'])) {
            @unlink($rutaFinal);
            return ['success' => false, 'mensaje' => $resultado['mensaje'] ?? 'No se pudo registrar el documento en BD.'];
        }

        return ['success' => true, 'archivo' => $nombreFinal];
    }

    private function extensionFuente(array $fuente): string
    {
        $extension = strtolower(trim((string) ($fuente['extension'] ?? '')));
        if ($extension === '') {
            $extension = strtolower(pathinfo((string) ($fuente['nombre_original'] ?? $fuente['ruta_relativa'] ?? $fuente['entry'] ?? ''), PATHINFO_EXTENSION));
        }
        return in_array($extension, ['pdf', 'fad'], true) ? $extension : 'pdf';
    }

    private function validarArchivoFad(string $path): bool
    {
        $mime = SecureUpload::getMimeType($path);
        $mimesFad = ['application/octet-stream', 'text/plain', 'application/xml', 'text/xml', 'application/x-empty'];
        return $mime === null || in_array($mime, $mimesFad, true);
    }

    private function esDocumentoSensibleRrhh(int $idDocumento): bool
    {
        return in_array($idDocumento, [self::DOCUMENTO_CONTRATO_FIRMADO, self::DOCUMENTO_ARCHIVO_FAD], true);
    }

    private function obtenerIpCliente(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
            $valor = trim((string)($_SERVER[$key] ?? ''));
            if ($valor !== '') {
                return trim(explode(',', $valor)[0]);
            }
        }
        return '';
    }

    private function obtenerLlaveArchivoSensible(): string
    {
        $env = trim((string)(getenv('RRHH_DOCUMENT_ENCRYPTION_KEY') ?: ''));
        if ($env !== '') {
            $decoded = base64_decode($env, true);
            if (is_string($decoded) && strlen($decoded) >= 32) {
                return substr($decoded, 0, 32);
            }
            if (ctype_xdigit($env) && strlen($env) >= 64) {
                return substr((string)hex2bin(substr($env, 0, 64)), 0, 32);
            }
            return substr(hash('sha256', $env, true), 0, 32);
        }

        $configDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config';
        $keyFile = $configDir . DIRECTORY_SEPARATOR . 'rrhh_documents.key';
        if (!is_dir($configDir)) {
            @mkdir($configDir, 0770, true);
        }
        if (!is_file($keyFile)) {
            @file_put_contents($keyFile, base64_encode(random_bytes(32)), LOCK_EX);
            @chmod($keyFile, 0600);
        }
        $key = trim((string)@file_get_contents($keyFile));
        $decoded = base64_decode($key, true);
        if (!is_string($decoded) || strlen($decoded) < 32) {
            $decoded = random_bytes(32);
            @file_put_contents($keyFile, base64_encode($decoded), LOCK_EX);
            @chmod($keyFile, 0600);
        }
        return substr($decoded, 0, 32);
    }

    private function archivoSensibleEstaCifrado(string $ruta): bool
    {
        if (!is_file($ruta)) {
            return false;
        }
        $fh = @fopen($ruta, 'rb');
        if (!$fh) {
            return false;
        }
        $inicio = (string)@fread($fh, strlen(self::DOCUMENTO_SENSIBLE_MAGIC));
        @fclose($fh);
        return hash_equals(self::DOCUMENTO_SENSIBLE_MAGIC, $inicio);
    }

    private function cifrarArchivoSensibleEnSitio(string $ruta): void
    {
        if (!is_file($ruta) || $this->archivoSensibleEstaCifrado($ruta)) {
            return;
        }
        $plain = @file_get_contents($ruta);
        if (!is_string($plain)) {
            throw new \RuntimeException('No se pudo leer el documento sensible para cifrarlo.');
        }
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $this->obtenerLlaveArchivoSensible(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new \RuntimeException('No se pudo cifrar el documento sensible.');
        }
        $tmp = $ruta . '.enc_tmp_' . bin2hex(random_bytes(4));
        $payload = self::DOCUMENTO_SENSIBLE_MAGIC . base64_encode($iv . $tag . $cipher);
        if (@file_put_contents($tmp, $payload, LOCK_EX) === false) {
            throw new \RuntimeException('No se pudo escribir el documento sensible cifrado.');
        }
        @chmod($tmp, 0600);
        if (!@rename($tmp, $ruta)) {
            @unlink($tmp);
            throw new \RuntimeException('No se pudo reemplazar el documento sensible cifrado.');
        }
    }

    private function extraerZipATemporal(array $fuente): ?string
    {
        $chain = $fuente['entry_chain'] ?? [];
        if (!is_array($chain) || empty($chain)) {
            $chain = [(string) ($fuente['entry'] ?? '')];
        }

        return $this->extraerCadenaZipATemporal((string) ($fuente['zip_tmp'] ?? ''), array_values($chain));
    }

    private function extraerCadenaZipATemporal(string $zipPath, array $entryChain): ?string
    {
        $entry = (string) array_shift($entryChain);
        if ($entry === '') {
            return null;
        }

        $tmp = $this->extraerEntradaZipATemporal($zipPath, $entry);
        if (!$tmp || empty($entryChain)) {
            return $tmp;
        }

        try {
            return $this->extraerCadenaZipATemporal($tmp, $entryChain);
        } finally {
            @unlink($tmp);
        }
    }

    private function extraerEntradaZipATemporal(string $zipPath, string $entry): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return null;
        }

        $stream = $zip->getStream($entry);
        if (!$stream) {
            $zip->close();
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'rrhhdoc_');
        $out = $tmp ? fopen($tmp, 'wb') : false;
        if (!$tmp || !$out) {
            fclose($stream);
            $zip->close();
            return null;
        }

        stream_copy_to_stream($stream, $out);
        fclose($out);
        fclose($stream);
        $zip->close();

        return $tmp;
    }

    private function directorioBaseLotes(): string
    {
        $base = sparta_project_root() . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'tmp_rrhh_import';
        SecureUpload::ensureDir($base);
        return $base;
    }

    private function directorioLote(string $batchId): string
    {
        return $this->directorioBaseLotes() . DIRECTORY_SEPARATOR . $batchId;
    }

    private function limpiarLotesTemporales(): void
    {
        $base = $this->directorioBaseLotes();
        foreach (glob($base . DIRECTORY_SEPARATOR . '*') ?: [] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $manifest = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
            $manifestData = is_file($manifest) ? json_decode((string) file_get_contents($manifest), true) : [];
            $createdAt = is_array($manifestData)
                ? (int) ($manifestData['created_at'] ?? 0)
                : (int) @filemtime($dir);
            if ($createdAt <= 0 || (time() - $createdAt) > self::BATCH_TTL_SECONDS) {
                $this->eliminarDirectorio($dir);
            }
        }
    }

    private function eliminarDirectorio(string $dir): void
    {
        if ($dir === '' || !is_dir($dir)) {
            return;
        }
        $base = realpath($this->directorioBaseLotes());
        $target = realpath($dir);
        if (!$base || !$target || strpos(strtolower(str_replace('\\', '/', $target)), strtolower(str_replace('\\', '/', $base)) . '/') !== 0) {
            return;
        }
        foreach (scandir($target) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $target . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->eliminarDirectorio($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($target);
    }

    private function resumen(array $items): array
    {
        $resumen = [
            'total' => count($items),
            'listo' => 0,
            'importado' => 0,
            'persona_no_encontrada' => 0,
            'persona_ambigua' => 0,
            'persona_no_coincide' => 0,
            'documento_no_reconocido' => 0,
            'ya_existe' => 0,
            'duplicado_lote' => 0,
            'omitido' => 0,
            'error' => 0,
        ];

        foreach ($items as $item) {
            $estado = (string) ($item['estado'] ?? 'error');
            if (!isset($resumen[$estado])) {
                $resumen[$estado] = 0;
            }
            $resumen[$estado]++;
        }

        return $resumen;
    }

    private function normalizarTexto(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n',
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n',
        ]);
        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        return trim($value);
    }

    private function tokens(string $value): array
    {
        $tokens = array_values(array_filter(explode(' ', $value), static fn($v) => $v !== ''));
        return array_values(array_unique($tokens));
    }
}
