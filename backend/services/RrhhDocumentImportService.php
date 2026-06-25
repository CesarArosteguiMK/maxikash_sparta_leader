<?php

namespace Services;

use Core\SecureUpload;
use Models\CapHum as CapHumDAO;

class RrhhDocumentImportService
{
    private const DOCUMENTO_RFC = 10;
    private const DOCUMENTO_CONSTANCIA_FISCAL = 22;
    private const MODULO_DOCUMENTO_RRHH_BASE = 3000;
    private const BATCH_TTL_SECONDS = 86400;

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

            if ($extension !== 'pdf') {
                continue;
            }

            $fuentes[] = [
                'tipo' => 'upload',
                'tmp' => $tmp,
                'ruta_relativa' => $rutaRelativa !== '' ? $rutaRelativa : (string) $nombreOriginal,
                'nombre_original' => (string) $nombreOriginal,
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
                $dest = $dir . DIRECTORY_SEPARATOR . 'src_' . $idx . '.pdf';
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
                $razon = 'La coincidencia de persona requiere revision.';
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
                if ($extension !== 'pdf') {
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
        ];
        foreach ($fallback as $id => $nombre) {
            if (!isset($byId[$id])) {
                $byId[$id] = ['id' => $id, 'clave' => '', 'nombre' => $nombre];
            }
        }

        return $byId;
    }

    private function catalogoParaRespuesta(array $catalogo): array
    {
        $out = array_values(array_filter($catalogo, function ($doc) {
            return $this->puedeUsarTipoDocumentoRrhh((int) ($doc['id'] ?? 0));
        }));
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
        $targetTokens = $this->tokens($targetNorm);
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
            $score = max((float) $scoreSimilar, (float) $scoreTokens);
            if ($targetKey !== '' && $targetKey === ($persona['token_key'] ?? '')) {
                $score = 100.0;
            }
            if ($score >= 70) {
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
        $n = $this->normalizarTexto(pathinfo($contexto, PATHINFO_FILENAME) . ' ' . $contexto);
        $id = null;

        if (preg_match('/\bvalidacion\s+sat\b|\bopinion\s+sat\b/', $n)) {
            $id = 30;
        } elseif (preg_match('/\bcontrato\s+firmado\b|\bcontrat[a-z0-9]*\b|\bcontratpo\b|\bcontato\b|\bcontra\s+to\b|\bcontra\s+ta\b/', $n)) {
            $id = 28;
        } elseif (preg_match('/\bsolicitud\s+interna\b|\bsolicitud\b/', $n)) {
            $id = 17;
        } elseif (preg_match('/\bcv\b|\bcurriculum\b|\bcurriculo\b|\bsolicitud\s+de\s+trabajo\b/', $n)) {
            $id = 18;
        } elseif (preg_match('/\bcsf\b|\bconstancia\b.*\bfiscal\b|\bsituacion\s+fiscal\b/', $n)) {
            $id = 22;
        } elseif (preg_match('/\bsemanas?\s+cotizadas?\b|\bconstancia\s+de\s+semanas\b|\bsemanas\s+del\s+asegurado\b|\bhistorial\s+laboral\b/', $n)) {
            $id = 33;
        } elseif (preg_match('/\bnss\b|\binss\b|\bseguridad\s+social\b/', $n)) {
            $id = 23;
        } elseif (preg_match('/\bfonacot\b|\binfonavit\b|\bretencion\b|\bno\s+credito\b|\bno\s+creditos\b|\bno\s+adeudo\b|\bno\s+adeudos\b|\bcarta\s+(?:de\s+)?no\s+creditos?\b/', $n)) {
            $id = 24;
        } elseif (preg_match('/\bbbva\b|\bbanorte\b|\bsantander\b|\bbanamex\b|\bcitibanamex\b|\bazteca\b|\bbajio\b|\bbanregio\b|\bestado\s+de\s+cuenta\b|\bclabe\b|\bcuenta\s+bancaria\b|\bbanco\b/', $n)) {
            $id = 25;
        } elseif (preg_match('/\bine\b|\bidentificacion\b|\bidentificacion\s+oficial\b|\bpasaporte\b|\bfm3\b/', $n)) {
            $id = 9;
        } elseif (preg_match('/\bcomprobante\b.*\bdomicilio\b|\bdomicilio\b/', $n)) {
            $id = 11;
        } elseif (preg_match('/\bacta\b.*\bnacimiento\b/', $n)) {
            $id = 12;
        } elseif (preg_match('/\bcertificado\b.*\bestudios\b|\bestudios\b/', $n)) {
            $id = 13;
        } elseif (preg_match('/\breferencia\b|\breferencias\s+laborales\b/', $n)) {
            $id = 14;
        } elseif (preg_match('/\breingreso\b/', $n)) {
            $id = 16;
        } elseif (preg_match('/\bcomprobante\b.*\bdeposito\b.*\bfiniq(?:uito)?\b|\bdeposito\b.*\bfiniq(?:uito)?\b|\bdocumento\s+baja\b|\bfiniquito\b|\bfiniq\b|\brenuncia\b|\brescision\b|\bresicision\b/', $n)) {
            $id = 15;
        } elseif (preg_match('/\brfc\b/', $n)) {
            $id = 10;
        } elseif (preg_match('/\bcurp\b/', $n)) {
            $id = 8;
        }

        if ($id === null) {
            return null;
        }

        return $catalogo[$id] ?? ['id' => $id, 'nombre' => 'Documento ' . $id, 'clave' => ''];
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

        $nombreFinal = SecureUpload::generateSafeFilename('pdf');
        $rutaFinal = $directorio . $nombreFinal;
        $tmpParaValidar = null;
        $limpiarTmp = false;

        if (($fuente['tipo'] ?? '') === 'upload') {
            $tmpParaValidar = (string) ($fuente['tmp'] ?? '');
        } elseif (($fuente['tipo'] ?? '') === 'zip' || ($fuente['tipo'] ?? '') === 'zip_nested') {
            $tmpParaValidar = $this->extraerZipATemporal($fuente);
            $limpiarTmp = true;
        }

        if (!$tmpParaValidar || !is_file($tmpParaValidar) || !SecureUpload::validateMime($tmpParaValidar, SecureUpload::MIME_PDF)) {
            if ($limpiarTmp && $tmpParaValidar && is_file($tmpParaValidar)) {
                @unlink($tmpParaValidar);
            }
            return ['success' => false, 'mensaje' => 'El archivo no parece ser un PDF valido.'];
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
            return ['success' => false, 'mensaje' => 'No se pudo guardar el PDF en uploads.'];
        }

        $resultado = CapHumDAO::guardarDocumentosPersona($idPersona, $idDocumento, [$nombreFinal]);
        if (empty($resultado['success'])) {
            @unlink($rutaFinal);
            return ['success' => false, 'mensaje' => $resultado['mensaje'] ?? 'No se pudo registrar el documento en BD.'];
        }

        return ['success' => true, 'archivo' => $nombreFinal];
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
