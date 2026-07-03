<?php

namespace Controllers;

use Core\Controller;
use Models\Adjudicacion as AdjudicacionDAO;
use Models\AlmacenVirtual as InventarioMotosDAO;
use Models\MotosAdjudicadas as MotosAdjudicadasDAO;

class MotosAdjudicadas extends Controller
{
    private const MODULO_REEMPLAZAR_EVIDENCIA_GESTOR = 79;

    private $model;
    private $inventarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new MotosAdjudicadasDAO();
    }

    private function inventarioDao(): InventarioMotosDAO
    {
        if (!$this->inventarioModel instanceof InventarioMotosDAO) {
            $this->inventarioModel = new InventarioMotosDAO();
        }

        return $this->inventarioModel;
    }

    private function slotsEvidenciasAlmacenVirtual(): array
    {
        return [
            'foto_frontal' => 'ev_foto_frontal',
            'foto_lateral_derecha' => 'ev_foto_lateral_derecha',
            'foto_trasera' => 'ev_foto_trasera',
            'foto_lateral_izquierda' => 'ev_foto_lateral_izquierda',
            'foto_vin' => 'ev_foto_vin',
        ];
    }

    private function slotsEvidenciasRevisionMecanica(): array
    {
        return [
            'revision_mecanica' => 'rev_ev_mecanica',
            'revision_electrica' => 'rev_ev_electrica',
            'revision_estetica' => 'rev_ev_estetica',
        ];
    }

    private function guardarArchivosEvidenciasAlmacenVirtual(int $idUnidad): array
    {
        $archivos = [];
        if ($idUnidad <= 0) {
            return ['success' => false, 'message' => 'Unidad invalida.'];
        }

        if (!function_exists('sparta_uploads_join')) {
            require_once dirname(__DIR__) . '/core/UploadsPaths.php';
        }

        $dir = sparta_uploads_join('almacen_virtual', (string) $idUnidad, 'evidencias');
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['success' => false, 'message' => 'No se pudo crear la carpeta de evidencias.'];
        }

        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
        $maxBytes = 15 * 1024 * 1024;
        foreach ($this->slotsEvidenciasAlmacenVirtual() as $slot => $campo) {
            $file = $_FILES[$campo] ?? null;
            if (!$file || !isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ((int) $file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'No se pudo recibir el archivo de ' . $slot . '.'];
            }
            $size = (int) ($file['size'] ?? 0);
            if ($size <= 0 || $size > $maxBytes) {
                return ['success' => false, 'message' => 'El archivo de ' . $slot . ' excede el tamano permitido.'];
            }
            $tmp = (string) ($file['tmp_name'] ?? '');
            if ($tmp === '' || !is_uploaded_file($tmp)) {
                return ['success' => false, 'message' => 'Archivo temporal invalido para ' . $slot . '.'];
            }

            $ext = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
            $ext = preg_replace('/[^a-z0-9]/', '', $ext);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            if (!in_array($ext, $permitidas, true)) {
                return ['success' => false, 'message' => 'Formato no permitido para ' . $slot . '.'];
            }

            $mime = function_exists('mime_content_type') ? (string) @mime_content_type($tmp) : null;
            $filename = $slot . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destino = rtrim($dir, DIRECTORY_SEPARATOR . '/\\') . DIRECTORY_SEPARATOR . $filename;
            if (!@move_uploaded_file($tmp, $destino)) {
                return ['success' => false, 'message' => 'No se pudo guardar el archivo de ' . $slot . '.'];
            }

            $archivos[$slot] = [
                'url' => '/uploads/almacen_virtual/' . $idUnidad . '/evidencias/' . $filename,
                'mime_type' => $mime ?: null,
                'tamano_bytes' => $size,
                'tipo_evidencia' => 'foto',
            ];
        }

        return ['success' => true, 'archivos' => $archivos];
    }

    private function guardarArchivosRevisionMecanica(int $idUnidad): array
    {
        $archivos = [];
        if ($idUnidad <= 0) {
            return ['success' => false, 'message' => 'Unidad invalida.'];
        }

        if (!function_exists('sparta_uploads_join')) {
            require_once dirname(__DIR__) . '/core/UploadsPaths.php';
        }

        $dir = sparta_uploads_join('almacen_virtual', (string) $idUnidad, 'revision_mecanica');
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['success' => false, 'message' => 'No se pudo crear la carpeta de revision mecanica.'];
        }

        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'mp4', 'mov', 'webm'];
        $videoExt = ['mp4', 'mov', 'webm'];
        $maxBytes = 80 * 1024 * 1024;
        foreach ($this->slotsEvidenciasRevisionMecanica() as $slot => $campo) {
            $file = $_FILES[$campo] ?? null;
            if (!$file || !isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ((int) $file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'No se pudo recibir el archivo de ' . $slot . '.'];
            }
            $size = (int) ($file['size'] ?? 0);
            if ($size <= 0 || $size > $maxBytes) {
                return ['success' => false, 'message' => 'El archivo de ' . $slot . ' excede el tamano permitido.'];
            }
            $tmp = (string) ($file['tmp_name'] ?? '');
            if ($tmp === '' || !is_uploaded_file($tmp)) {
                return ['success' => false, 'message' => 'Archivo temporal invalido para ' . $slot . '.'];
            }

            $ext = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
            $ext = preg_replace('/[^a-z0-9]/', '', $ext);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            if (!in_array($ext, $permitidas, true)) {
                return ['success' => false, 'message' => 'Formato no permitido para ' . $slot . '.'];
            }

            $mime = function_exists('mime_content_type') ? (string) @mime_content_type($tmp) : null;
            $filename = $slot . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destino = rtrim($dir, DIRECTORY_SEPARATOR . '/\\') . DIRECTORY_SEPARATOR . $filename;
            if (!@move_uploaded_file($tmp, $destino)) {
                return ['success' => false, 'message' => 'No se pudo guardar el archivo de ' . $slot . '.'];
            }

            $archivos[$slot] = [
                'url' => '/uploads/almacen_virtual/' . $idUnidad . '/revision_mecanica/' . $filename,
                'mime_type' => $mime ?: null,
                'tamano_bytes' => $size,
                'tipo_evidencia' => in_array($ext, $videoExt, true) ? 'video' : 'foto',
            ];
        }

        return ['success' => true, 'archivos' => $archivos];
    }

    private function tieneModuloSesion(int $moduloId): bool
    {
        $mods = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        return in_array($moduloId, $mods, true);
    }

    private function etiquetaEvidenciaPorSlot(string $slot): string
    {
        $labels = [
            'fis_dacion_hoja_1' => 'Foto dacion hoja 1',
            'fis_dacion_hoja_2' => 'Foto dacion hoja 2',
            'fis_vin' => 'Foto NIV VIN',
            'fis_frontal' => 'Foto frontal',
            'fis_lateral_der' => 'Foto lateral derecha',
            'fis_trasera' => 'Foto trasera',
            'fis_lateral_izq' => 'Foto lateral izquierda',
            'fis_tacometro' => 'Foto tacometro',
            'fis_video_cliente_acuerdo' => 'Video cliente de acuerdo',
            'fis_360_encendida' => 'Video moto 360 encendida',
            'fis_video_vuelta_prueba' => 'Video vuelta de prueba',
            'fis_checklist' => 'Foto checklist',
            'doc_repuve' => 'Repuve',
            'doc_factura' => 'Factura',
        ];

        return $labels[$slot] ?? $slot;
    }

    private function slugArchivoEvidencia(string $texto): string
    {
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        $texto = preg_replace('/[^A-Za-z0-9]+/', '_', $texto);
        $texto = trim((string) $texto, '_');

        return $texto !== '' ? strtolower($texto) : 'evidencia';
    }

    private function extensionDesdeUrl(string $url, string $fallback = 'bin'): string
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: $url);
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);
        if ($ext === '' || strlen($ext) > 8) {
            return $fallback;
        }

        return $ext;
    }

    private function resolverArchivoEvidencia(string $url): ?array
    {
        if (!function_exists('sparta_uploads_resolve_relative')) {
            require_once dirname(__DIR__) . '/core/UploadsPaths.php';
        }

        $raw = trim(str_replace('\\', '/', $url));
        if ($raw === '') {
            return null;
        }

        $path = (string) (parse_url($raw, PHP_URL_PATH) ?: $raw);
        $uploadsPos = stripos($path, '/uploads/');
        if ($uploadsPos !== false) {
            $relative = substr($path, $uploadsPos + strlen('/uploads/'));
            $local = sparta_uploads_resolve_relative($relative);
            if ($local && is_file($local)) {
                return [
                    'path' => $local,
                    'temp' => false,
                    'ext' => $this->extensionDesdeUrl($raw, strtolower((string) pathinfo($local, PATHINFO_EXTENSION)) ?: 'bin'),
                ];
            }
        }

        if (!preg_match('#^https?://#i', $raw) && is_file($raw)) {
            return [
                'path' => $raw,
                'temp' => false,
                'ext' => $this->extensionDesdeUrl($raw, strtolower((string) pathinfo($raw, PATHINFO_EXTENSION)) ?: 'bin'),
            ];
        }

        if (!preg_match('#^https?://#i', $raw)) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'sp_ev_');
        if (!$tmp) {
            return null;
        }

        $ok = false;
        if (function_exists('curl_init')) {
            $fh = fopen($tmp, 'wb');
            if ($fh) {
                $ch = curl_init($raw);
                curl_setopt_array($ch, [
                    CURLOPT_FILE => $fh,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 90,
                    CURLOPT_FAILONERROR => true,
                    CURLOPT_USERAGENT => 'sparta-__SPARTA_SECRET_REDACTED__-evidencias/1.0',
                ]);
                $ok = curl_exec($ch) === true;
                curl_close($ch);
                fclose($fh);
            }
        } else {
            $ctx = stream_context_create(['http' => ['timeout' => 90]]);
            $data = @file_get_contents($raw, false, $ctx);
            if ($data !== false) {
                $ok = @file_put_contents($tmp, $data) !== false;
            }
        }

        if (!$ok || !is_file($tmp) || filesize($tmp) <= 0) {
            @unlink($tmp);
            return null;
        }

        return [
            'path' => $tmp,
            'temp' => true,
            'ext' => $this->extensionDesdeUrl($raw),
        ];
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    /**
     * GET /MotosAdjudicadas/pipeline
     */
    public function pipeline()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Operaciones - Pipeline ' . $emp);
        return self::render('operaciones_pipeline');
    }

    /**
     * GET /MotosAdjudicadas/almacenVirtual
     */
    public function almacenVirtual()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Almacen Virtual - Motos Adjudicadas ' . $emp);
        self::set('av_modulo_id', InventarioMotosDAO::moduloAlmacenVirtual());
        return self::render('almacen_virtual_menu');
    }

    /**
     * GET /MotosAdjudicadas/inventario
     */
    public function inventario()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Inventario - Almacen Virtual ' . $emp);
        self::set('av_modulo_id', InventarioMotosDAO::moduloAlmacenVirtual());
        return self::render('almacen_virtual');
    }

    /**
     * GET /MotosAdjudicadas/evidenciasCodigo
     */
    public function evidenciasCodigo()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Evidencias y Codigo - Almacen Virtual ' . $emp);
        self::set('av_modulo_id', InventarioMotosDAO::moduloAlmacenVirtual());
        return self::render('almacen_virtual_evidencias');
    }

    /**
     * GET /MotosAdjudicadas/recepcionAlmacen
     */
    public function recepcionAlmacen()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Recepcion de Almacen - Almacen Virtual ' . $emp);
        self::set('av_modulo_id', InventarioMotosDAO::moduloAlmacenVirtual());
        return self::render('almacen_virtual_recepcion');
    }

    /**
     * GET /MotosAdjudicadas/revisionMecanica
     */
    public function revisionMecanica()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Revision Mecanica - Almacen Virtual ' . $emp);
        self::set('av_modulo_id', InventarioMotosDAO::moduloAlmacenVirtual());
        self::set('av_revision_checklist', $this->inventarioDao()->obtenerChecklistRevisionMecanica());
        return self::render('almacen_virtual_revision_mecanica');
    }

    public function inventarioResumen()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->inventarioDao()->obtenerResumen(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el resumen de inventario.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function inventarioCelulas()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $rows = [];
            foreach ($this->inventarioDao()->obtenerCelulas() as $id => $nombre) {
                $rows[] = ['id_celula' => (int) $id, 'nombre' => $nombre];
            }
            echo json_encode(['success' => true, 'datos' => $rows], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'No se pudieron cargar las celulas.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function inventarioUbicaciones()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->inventarioDao()->listarUbicacionesActivas(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'No se pudieron cargar las ubicaciones.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function inventarioUnidades()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $filtros = [
                'q' => trim((string) ($_GET['q'] ?? '')),
                'id_celula' => (int) ($_GET['id_celula'] ?? 0),
                'estatus' => trim((string) ($_GET['estatus'] ?? '')),
                'id_ubicacion' => (int) ($_GET['id_ubicacion'] ?? 0),
                'page' => (int) ($_GET['page'] ?? 1),
                'limit' => (int) ($_GET['limit'] ?? 8),
            ];

            echo json_encode($this->inventarioDao()->listarUnidades($filtros), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudieron cargar las unidades.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function inventarioPendientesMotosAdjudicadas()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $filtros = [
                'q' => trim((string) ($_GET['q'] ?? '')),
                'limit' => (int) ($_GET['limit'] ?? 8),
                'page' => (int) ($_GET['page'] ?? 1),
            ];
            echo json_encode($this->inventarioDao()->listarPendientesMotosAdjudicadas($filtros), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudieron cargar pendientes de Motos Adjudicadas.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function inventarioSincronizarRecolectadas()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SISTEMA'));
        $limit = (int) ($_GET['limit'] ?? $_POST['limit'] ?? 200);

        try {
            echo json_encode(
                $this->inventarioDao()->sincronizarRecolectadasMotosAdjudicadas($idUsuario, $nombreUsuario, $limit),
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo sincronizar motos recolectadas.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function inventarioCrearDesdeMotosAdjudicadas()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'El alta manual por id_operacion esta deshabilitada. El inventario se alimenta automaticamente desde recolecciones confirmadas en Tracking.',
        ], JSON_UNESCAPED_UNICODE);
    }

    public function inventarioFichaUnidad()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $idUnidad = (int) ($_GET['id_unidad'] ?? 0);
            echo json_encode($this->inventarioDao()->obtenerFichaUnidad($idUnidad), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar la ficha de la unidad.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function evidenciasCodigoResumen()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->inventarioDao()->obtenerResumenEvidencias(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el resumen de evidencias.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function evidenciasCodigoUnidades()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $filtros = [
                'q' => trim((string) ($_GET['q'] ?? '')),
                'id_celula' => (int) ($_GET['id_celula'] ?? 0),
                'estatus' => trim((string) ($_GET['estatus'] ?? 'abiertas')),
                'page' => (int) ($_GET['page'] ?? 1),
                'limit' => (int) ($_GET['limit'] ?? 8),
            ];

            echo json_encode($this->inventarioDao()->listarEvidenciasUnidades($filtros), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudieron cargar unidades para evidencias.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function evidenciasCodigoGenerar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SISTEMA'));
        $idUnidad = (int) ($_POST['id_unidad'] ?? $_GET['id_unidad'] ?? 0);

        try {
            echo json_encode(
                $this->inventarioDao()->generarCodigoVerificacionUnidad($idUnidad, $idUsuario, $nombreUsuario),
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo generar el codigo.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function evidenciasCodigoValidar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SISTEMA'));
        $idUnidad = (int) ($_POST['id_unidad'] ?? 0);
        $datos = [
            'vin' => trim((string) ($_POST['vin'] ?? '')),
            'codigo_verificacion' => trim((string) ($_POST['codigo_verificacion'] ?? '')),
            'observaciones' => trim((string) ($_POST['observaciones'] ?? '')),
        ];

        try {
            $codigoOk = $this->inventarioDao()->codigoVerificacionPendienteValido($idUnidad, $datos['codigo_verificacion']);
            if (empty($codigoOk['success'])) {
                echo json_encode($codigoOk, JSON_UNESCAPED_UNICODE);
                return;
            }

            $archivos = $this->guardarArchivosEvidenciasAlmacenVirtual($idUnidad);
            if (empty($archivos['success'])) {
                echo json_encode($archivos, JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode(
                $this->inventarioDao()->validarEvidenciasCodigoUnidad(
                    $idUnidad,
                    $datos,
                    $archivos['archivos'] ?? [],
                    $idUsuario,
                    $nombreUsuario
                ),
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo validar evidencias y codigo.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function recepcionAlmacenResumen()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->inventarioDao()->obtenerResumenRecepcion(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el resumen de recepcion.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function recepcionAlmacenUnidades()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $filtros = [
                'q' => trim((string) ($_GET['q'] ?? '')),
                'id_celula' => (int) ($_GET['id_celula'] ?? 0),
                'estatus' => trim((string) ($_GET['estatus'] ?? 'abiertas')),
                'id_ubicacion' => (int) ($_GET['id_ubicacion'] ?? 0),
                'page' => (int) ($_GET['page'] ?? 1),
                'limit' => (int) ($_GET['limit'] ?? 8),
            ];

            echo json_encode($this->inventarioDao()->listarRecepcionUnidades($filtros), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudieron cargar unidades para recepcion.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function recepcionAlmacenConfirmar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SISTEMA'));
        $idUnidad = (int) ($_POST['id_unidad'] ?? 0);
        $datos = [
            'id_ubicacion' => (int) ($_POST['id_ubicacion'] ?? 0),
            'vin' => trim((string) ($_POST['vin'] ?? '')),
            'no_motor' => trim((string) ($_POST['no_motor'] ?? '')),
            'placas' => trim((string) ($_POST['placas'] ?? '')),
            'kilometraje' => trim((string) ($_POST['kilometraje'] ?? '')),
            'observaciones' => trim((string) ($_POST['observaciones'] ?? '')),
            'vin_coincide' => $_POST['vin_coincide'] ?? null,
            'evidencia_4_angulos' => $_POST['evidencia_4_angulos'] ?? null,
            'evidencia_vin' => $_POST['evidencia_vin'] ?? null,
            'documentos_completos' => $_POST['documentos_completos'] ?? null,
            'arranque_motor' => $_POST['arranque_motor'] ?? null,
            'sin_danos_mayores' => $_POST['sin_danos_mayores'] ?? null,
        ];

        try {
            echo json_encode(
                $this->inventarioDao()->confirmarRecepcionAlmacen($idUnidad, $datos, $idUsuario, $nombreUsuario),
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo confirmar la recepcion de almacen.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function revisionMecanicaResumen()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->inventarioDao()->obtenerResumenRevisionMecanica(),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el resumen de revision mecanica.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function revisionMecanicaUnidades()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $filtros = [
                'q' => trim((string) ($_GET['q'] ?? '')),
                'id_celula' => (int) ($_GET['id_celula'] ?? 0),
                'estatus' => trim((string) ($_GET['estatus'] ?? 'abiertas')),
                'id_ubicacion' => (int) ($_GET['id_ubicacion'] ?? 0),
                'page' => (int) ($_GET['page'] ?? 1),
                'limit' => (int) ($_GET['limit'] ?? 8),
            ];

            echo json_encode($this->inventarioDao()->listarRevisionMecanicaUnidades($filtros), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudieron cargar unidades para revision mecanica.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function revisionMecanicaIniciar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SISTEMA'));
        $idUnidad = (int) ($_POST['id_unidad'] ?? $_GET['id_unidad'] ?? 0);

        try {
            echo json_encode(
                $this->inventarioDao()->iniciarRevisionMecanica($idUnidad, $idUsuario, $nombreUsuario),
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo iniciar la revision mecanica.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function revisionMecanicaFinalizar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SISTEMA'));
        $idUnidad = (int) ($_POST['id_unidad'] ?? 0);
        $datos = [
            'diagnostico_general' => trim((string) ($_POST['diagnostico_general'] ?? '')),
            'comentario_mecanica' => trim((string) ($_POST['comentario_mecanica'] ?? '')),
            'comentario_electrica' => trim((string) ($_POST['comentario_electrica'] ?? '')),
            'comentario_estetica' => trim((string) ($_POST['comentario_estetica'] ?? '')),
            'otros_mecanica' => trim((string) ($_POST['otros_mecanica'] ?? '')),
            'otros_electrica' => trim((string) ($_POST['otros_electrica'] ?? '')),
            'otros_estetica' => trim((string) ($_POST['otros_estetica'] ?? '')),
            'dictamen' => trim((string) ($_POST['dictamen'] ?? '')),
            'items' => $_POST['items'] ?? [],
            'tipo_servicio' => $_POST['tipo_servicio'] ?? [],
        ];

        try {
            $archivos = $this->guardarArchivosRevisionMecanica($idUnidad);
            if (empty($archivos['success'])) {
                echo json_encode($archivos, JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode(
                $this->inventarioDao()->finalizarRevisionMecanica(
                    $idUnidad,
                    $datos,
                    $archivos['archivos'] ?? [],
                    $idUsuario,
                    $nombreUsuario
                ),
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo finalizar la revision mecanica.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /MotosAdjudicadas/listaDictamenes
     */
    public function listaDictamenes()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Motos Adjudicadas - Lista de Dictámenes ' . $emp);
        $gmk = defined('GOOGLE_MAPS_API_KEY') ? (string) GOOGLE_MAPS_API_KEY : '';
        self::set(
            'google_maps_api_key_js',
            json_encode($gmk, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        );
        return self::render('motos_adjudicadas_lista_dictamenes');
    }

    /**
     * GET /MotosAdjudicadas/campaniaNotificacionLegacy
     */
    public function campaniaNotificacionLegacy()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Campaña Notificación Legacy - Motos Adjudicadas ' . $emp);
        self::set('campaign_id_default', 'camp_' . date('Ymd_His'));
        return self::render('motos_adjudicadas_campania_notificacion_legacy');
    }

    /**
     * GET /MotosAdjudicadas/comentariosLegacy
     */
    public function comentariosLegacy()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Comentarios Legacy - Motos Adjudicadas ' . $emp);
        self::set('campaign_id_default', 'feedback_' . date('Ymd_His'));
        return self::render('motos_adjudicadas_comentarios_legacy');
    }

    /**
     * GET /MotosAdjudicadas/monitoreoAdjudicaciones
     */
    public function monitoreoAdjudicaciones()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Monitoreo - Motos Adjudicadas ' . $emp);
        return self::render('motos_adjudicadas_monitoreo');
    }

    /**
     * GET /MotosAdjudicadas/otpEmergenciaLegacy
     */
    public function otpEmergenciaLegacy()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'OTP DE EMERGENCIA - Motos Adjudicadas ' . $emp);
        return self::render('motos_adjudicadas_otp_emergencia');
    }

    /**
     * GET /MotosAdjudicadas/dashboard
     */
    public function dashboard()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Dashboard - Motos Adjudicadas ' . $emp);
        return self::render('motos_adjudicadas_dashboard');
    }

    /**
     * GET /MotosAdjudicadas/reporteria
     */
    public function reporteria()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Reporteria - Motos Adjudicadas ' . $emp);
        return self::render('motos_adjudicadas_reporteria');
    }

    /**
     * GET /MotosAdjudicadas/reporteSeguimiento
     */
    public function reporteSeguimiento()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Reporte de seguimiento - Motos Adjudicadas ' . $emp);
        return self::render('motos_adjudicadas_reporte_seguimiento');
    }

    /**
     * GET /MotosAdjudicadas/reporteHistoricoFlujo
     */
    public function reporteHistoricoFlujo()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Historico por etapas - Motos Adjudicadas ' . $emp);
        return self::render('motos_adjudicadas_reporte_historico_flujo');
    }

    /**
     * GET /MotosAdjudicadas/timelineCredito
     */
    public function timelineCredito()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Timeline por credito - Motos Adjudicadas ' . $emp);
        return self::render('motos_adjudicadas_timeline_credito');
    }

    /**
     * GET /MotosAdjudicadas/dashboardDatos
     */
    public function dashboardDatos()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->model->obtenerDashboardMotosAdjudicadas($_GET),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el dashboard de motos adjudicadas.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /MotosAdjudicadas/reporteSeguimientoDatos
     */
    public function reporteSeguimientoDatos()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->model->obtenerReporteSeguimientoMotosAdjudicadas($_GET),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el reporte de seguimiento.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /MotosAdjudicadas/reporteHistoricoFlujoDatos
     */
    public function reporteHistoricoFlujoDatos()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            echo json_encode([
                'success' => true,
                'datos' => $this->model->obtenerReporteHistoricoFlujoMotosAdjudicadas($_GET),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el historico por etapas.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /MotosAdjudicadas/reporteHistoricoFlujoExcel
     */
    public function reporteHistoricoFlujoExcel()
    {
        try {
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                http_response_code(500);
                echo 'El generador Excel no esta disponible.';
                return;
            }

            $datos = $this->model->obtenerReporteHistoricoFlujoMotosAdjudicadas($_GET);
            $rows = $this->madjHistoricoFlujoRowsExport($datos);

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Historico etapas');

            $headers = [
                'Etapa',
                'Operacion',
                'Credito',
                'Cliente',
                'Estatus',
                'Estado',
                'Municipio',
                'Unidad',
                'VIN',
                'Gestor',
                'Fecha de etapa',
                'Ultima actualizacion',
            ];

            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
            $borde = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'D9DEE3'],
                    ],
                ],
            ];

            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A1', 'Historico por etapas - Motos Adjudicadas');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '24334F']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ] + $borde);
            $sheet->getRowDimension(1)->setRowHeight(30);

            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i') . ' - America/Mexico_City');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => '697A8D']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FC']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ] + $borde);

            foreach ($headers as $idx => $header) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                $sheet->setCellValue($col . '4', $header);
            }
            $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '24334F']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF3FF']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ] + $borde);
            $sheet->getRowDimension(4)->setRowHeight(24);

            $fila = 5;
            foreach ($rows as $row) {
                $values = [
                    $row['etapa'],
                    $row['operacion'],
                    $row['credito'],
                    $row['cliente'],
                    $row['estatus'],
                    $row['estado'],
                    $row['municipio'],
                    $row['unidad'],
                    $row['vin'],
                    $row['gestor'],
                    $row['fecha_etapa'],
                    $row['fecha_actualizacion'],
                ];

                foreach ($values as $idx => $value) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                    $sheet->setCellValueExplicit(
                        $col . $fila,
                        $this->madjTextoReporte($value),
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
                }

                $fill = ($fila % 2 === 0) ? 'FFFFFF' : 'FBFCFE';
                $sheet->getStyle("A{$fila}:{$lastCol}{$fila}")->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '434A54']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ] + $borde);
                $sheet->getRowDimension($fila)->setRowHeight(28);
                $fila++;
            }

            if ($rows === []) {
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->setCellValue('A5', 'Sin registros para mostrar con los filtros actuales.');
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '697A8D']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ] + $borde);
                $fila = 6;
            }

            $widths = [24, 18, 14, 34, 18, 22, 24, 30, 24, 32, 20, 20];
            foreach ($widths as $idx => $width) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                $sheet->getColumnDimension($col)->setWidth($width);
            }

            $sheet->freezePane('A5');
            $sheet->setAutoFilter("A4:{$lastCol}" . max(4, $fila - 1));
            $sheet->setSelectedCells('A1');

            $filename = 'HistoricoEtapas_MotosAdjudicadas_' . date('Ymd_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'No se pudo generar el Excel: ' . $e->getMessage();
        }
    }

    /**
     * GET /MotosAdjudicadas/reporteHistoricoFlujoPdf
     */
    public function reporteHistoricoFlujoPdf()
    {
        try {
            if (!class_exists('\\Mpdf\\Mpdf')) {
                http_response_code(500);
                echo 'El generador PDF no esta disponible.';
                return;
            }

            $datos = $this->model->obtenerReporteHistoricoFlujoMotosAdjudicadas($_GET);
            $tmpDir = defined('RAIZ') ? (RAIZ . '/storage/tmp_mpdf') : sys_get_temp_dir();
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }

            $mpdf = new \Mpdf\Mpdf([
                'format' => 'A4-L',
                'orientation' => 'L',
                'tempDir' => $tmpDir,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_top' => 8,
                'margin_bottom' => 10,
            ]);
            $mpdf->SetTitle('Historico por etapas - Motos Adjudicadas');
            $mpdf->WriteHTML($this->madjHistoricoFlujoPdfHtml($datos));
            $filename = 'HistoricoEtapas_MotosAdjudicadas_' . date('Ymd_His') . '.pdf';
            $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'No se pudo generar el PDF: ' . $e->getMessage();
        }
    }

    private function madjTextoReporte($value): string
    {
        $txt = html_entity_decode((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
        $txt = strtr($txt, [
            'Ã¡' => 'a', 'Ã©' => 'e', 'Ã­' => 'i', 'Ã³' => 'o', 'Ãº' => 'u', 'Ã¼' => 'u', 'Ã±' => 'n',
            'Ã' => 'A', 'Ã‰' => 'E', 'Ã' => 'I', 'Ã“' => 'O', 'Ãš' => 'U', 'Ãœ' => 'U', 'Ã‘' => 'N',
            'Â·' => '-', 'â€”' => '-', 'â€“' => '-', 'â€¦' => '...', 'Â' => '',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);
        $txt = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $txt) ?? $txt;
        $txt = preg_replace('/\s+/u', ' ', $txt) ?? $txt;
        return trim($txt);
    }

    private function madjHistoricoFlujoRowsExport(array $datos): array
    {
        $rows = [];
        foreach ((array) ($datos['etapas'] ?? []) as $etapa) {
            $tituloEtapa = $this->madjTextoReporte($etapa['titulo'] ?? 'Sin etapa');
            foreach ((array) ($etapa['creditos'] ?? []) as $row) {
                $rows[] = [
                    'etapa' => $tituloEtapa,
                    'operacion' => $this->madjTextoReporte($row['folio'] ?? ('ADJ-' . (string) ($row['id_operacion'] ?? ''))),
                    'credito' => (string) ($row['id_credito'] ?? ''),
                    'cliente' => $this->madjTextoReporte($row['nombre_cliente'] ?? ''),
                    'estatus' => $this->madjTextoReporte($row['estatus'] ?? ''),
                    'estado' => $this->madjTextoReporte($row['estado'] ?? ''),
                    'municipio' => $this->madjTextoReporte($row['municipio'] ?? ''),
                    'unidad' => $this->madjTextoReporte($row['unidad'] ?? ''),
                    'vin' => $this->madjTextoReporte($row['vin'] ?? ''),
                    'gestor' => $this->madjTextoReporte($row['gestor_nombre'] ?? ''),
                    'fecha_etapa' => $this->madjTextoReporte($row['fecha_etapa_fmt'] ?? ''),
                    'fecha_actualizacion' => $this->madjFmtFechaReporte($row['fecha_actualizacion'] ?? null),
                ];
            }
        }

        return $rows;
    }

    private function madjFmtFechaReporte($fecha): string
    {
        $fecha = trim((string) ($fecha ?? ''));
        if ($fecha === '') {
            return 'Sin fecha';
        }
        $ts = strtotime($fecha);
        return $ts !== false ? date('d/m/Y H:i', $ts) : $this->madjTextoReporte($fecha);
    }

    private function madjHistoricoFlujoPdfHtml(array $datos): string
    {
        $resumen = is_array($datos['resumen'] ?? null) ? $datos['resumen'] : [];
        $etapas = is_array($datos['etapas'] ?? null) ? $datos['etapas'] : [];

        $etapasHtml = '';
        foreach ($etapas as $etapa) {
            $titulo = $this->madjTextoReporte($etapa['titulo'] ?? 'Sin etapa');
            $total = (int) ($etapa['total'] ?? 0);
            $items = '';
            foreach ((array) ($etapa['creditos'] ?? []) as $idx => $row) {
                if ($idx >= 120) {
                    $items .= '<tr><td colspan="5" class="muted">Se muestran los primeros 120 registros de esta etapa en PDF. Descarga el XLSX para ver el detalle completo.</td></tr>';
                    break;
                }
                $items .= '<tr>'
                    . '<td>' . $this->madjPdfEsc($row['folio'] ?? '') . '</td>'
                    . '<td>#' . $this->madjPdfEsc($row['id_credito'] ?? '') . '<br><span>' . $this->madjPdfEsc($row['nombre_cliente'] ?? '') . '</span></td>'
                    . '<td>' . $this->madjPdfEsc($row['estatus'] ?? '') . '</td>'
                    . '<td>' . $this->madjPdfEsc(trim(($row['estado'] ?? '') . ' / ' . ($row['municipio'] ?? ''), ' /')) . '</td>'
                    . '<td>' . $this->madjPdfEsc($row['fecha_etapa_fmt'] ?? '') . '</td>'
                    . '</tr>';
            }
            if ($items === '') {
                $items = '<tr><td colspan="5" class="muted">Sin creditos en esta etapa.</td></tr>';
            }

            $etapasHtml .= '<div class="stage-title">' . $this->madjPdfEsc($titulo) . ' <span>(' . number_format($total) . ')</span></div>'
                . '<table class="data"><thead><tr><th>Operacion</th><th>Credito / Cliente</th><th>Estatus</th><th>Ubicacion</th><th>Fecha etapa</th></tr></thead><tbody>'
                . $items
                . '</tbody></table>';
        }

        return '<!doctype html><html><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;color:#1f2937;font-size:9.5px}
            .hero{background:#24334f;color:#fff;padding:16px;border-radius:8px;margin-bottom:10px}
            .hero h1{font-size:19px;margin:0 0 4px}
            .hero p{margin:0;color:#dbeafe}
            .summary{width:100%;border-collapse:collapse;margin-bottom:10px}
            .summary td{border:1px solid #d8e2ef;padding:8px}
            .label{font-size:8px;color:#64748b;text-transform:uppercase;font-weight:bold}
            .value{font-size:14px;font-weight:bold;color:#0f172a}
            .stage-title{font-size:13px;font-weight:bold;color:#0f172a;margin:12px 0 5px}
            .stage-title span{color:#0f9d92}
            table.data{width:100%;border-collapse:collapse;page-break-inside:auto}
            table.data th{background:#eaf3ff;color:#24334f;font-size:8.5px;text-align:left;padding:6px;border:1px solid #d8e2ef}
            table.data td{padding:6px;border:1px solid #d8e2ef;vertical-align:top}
            table.data span{color:#64748b}
            .muted{color:#64748b}
        </style></head><body>
            <div class="hero">
                <h1>Historico por etapas - Motos Adjudicadas</h1>
                <p>Reporte generado el ' . date('d/m/Y H:i') . ' - America/Mexico_City</p>
            </div>
            <table class="summary">
                <tr>
                    <td><div class="label">Total historico</div><div class="value">' . number_format((int) ($resumen['total'] ?? 0)) . '</div></td>
                    <td><div class="label">Etapas</div><div class="value">' . number_format(count($etapas)) . '</div></td>
                    <td><div class="label">Tracking</div><div class="value">' . (!empty($resumen['tracking_disponible']) ? 'Disponible' : 'Pendiente') . '</div></td>
                </tr>
            </table>'
            . $etapasHtml .
        '</body></html>';
    }

    /**
     * GET /MotosAdjudicadas/timelineCreditoDatos?id_credito=N
     */
    public function timelineCreditoDatos()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idCredito = (int) ($_GET['id_credito'] ?? $_POST['id_credito'] ?? 0);

        try {
            echo json_encode(
                $this->model->obtenerTimelineCreditoMotosAdjudicadas($idCredito),
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo cargar el timeline del credito.',
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /MotosAdjudicadas/timelineCreditoPdf?id_credito=N
     */
    public function timelineCreditoPdf()
    {
        $idCredito = (int) ($_GET['id_credito'] ?? 0);
        if ($idCredito <= 0) {
            http_response_code(400);
            echo 'Indica un credito valido.';
            return;
        }

        try {
            $datos = $this->model->obtenerTimelineCreditoMotosAdjudicadas($idCredito);
            if (empty($datos['success'])) {
                http_response_code(404);
                echo (string) ($datos['message'] ?? 'No se encontro informacion para este credito.');
                return;
            }

            if (!class_exists('\\Mpdf\\Mpdf')) {
                http_response_code(500);
                echo 'El generador PDF no esta disponible.';
                return;
            }

            $tmpDir = defined('RAIZ') ? (RAIZ . '/storage/tmp_mpdf') : sys_get_temp_dir();
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }

            $mpdf = new \Mpdf\Mpdf([
                'format' => 'A4',
                'orientation' => 'P',
                'tempDir' => $tmpDir,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 12,
            ]);
            $mpdf->SetTitle('Timeline credito ' . $idCredito);
            $mpdf->WriteHTML($this->madjTimelinePdfHtml($datos));
            $filename = 'TimelineCredito_' . $idCredito . '_' . date('Ymd_His') . '.pdf';
            $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'No se pudo generar el PDF: ' . $e->getMessage();
        }
    }

    private function madjPdfEsc($value): string
    {
        return htmlspecialchars($this->madjTextoReporte($value), ENT_QUOTES, 'UTF-8');
    }

    private function madjTimelinePdfHtml(array $datos): string
    {
        $credito = is_array($datos['credito'] ?? null) ? $datos['credito'] : [];
        $unidad = is_array($credito['unidad'] ?? null) ? $credito['unidad'] : [];
        $ubicacion = is_array($credito['ubicacion'] ?? null) ? $credito['ubicacion'] : [];
        $contacto = is_array($credito['contacto'] ?? null) ? $credito['contacto'] : [];
        $finanzas = is_array($credito['finanzas'] ?? null) ? $credito['finanzas'] : [];
        $etapas = is_array($datos['etapas'] ?? null) ? $datos['etapas'] : [];
        $eventos = is_array($datos['eventos'] ?? null) ? $datos['eventos'] : [];
        $resumen = is_array($datos['resumen'] ?? null) ? $datos['resumen'] : [];

        $etapasHtml = '';
        foreach ($etapas as $etapa) {
            $estado = strtolower((string) ($etapa['estado'] ?? 'pendiente'));
            $badge = $estado === 'completado' ? 'ok' : ($estado === 'en_proceso' ? 'run' : 'wait');
            $etapasHtml .= '<tr>'
                . '<td><strong>' . $this->madjPdfEsc($etapa['titulo'] ?? '') . '</strong><br><span>' . $this->madjPdfEsc($etapa['descripcion'] ?? '') . '</span></td>'
                . '<td><span class="badge ' . $badge . '">' . $this->madjPdfEsc(str_replace('_', ' ', $estado)) . '</span></td>'
                . '<td>' . $this->madjPdfEsc($etapa['fecha_fmt'] ?? '') . '</td>'
                . '</tr>';
        }

        $eventosHtml = '';
        foreach ($eventos as $ev) {
            $evidenciaUrl = trim((string) ($ev['evidencia_url'] ?? ''));
            $evidenciaTitulo = trim((string) ($ev['evidencia_titulo'] ?? ''));
            $evidenciaHtml = '';
            if ($evidenciaUrl !== '') {
                $evidenciaHtml = '<br><a class="link" href="' . $this->madjPdfEsc($evidenciaUrl) . '">' . $this->madjPdfEsc($evidenciaTitulo !== '' ? $evidenciaTitulo : 'Abrir evidencia') . '</a>';
            }
            $eventosHtml .= '<tr>'
                . '<td>' . $this->madjPdfEsc($ev['fecha_fmt'] ?? '') . '</td>'
                . '<td><strong>' . $this->madjPdfEsc($ev['titulo'] ?? '') . '</strong><br><span>' . $this->madjPdfEsc($ev['descripcion'] ?? '') . '</span>' . $evidenciaHtml . '</td>'
                . '<td>' . $this->madjPdfEsc($ev['origen_label'] ?? $ev['origen'] ?? '') . '</td>'
                . '</tr>';
        }

        if ($eventosHtml === '') {
            $eventosHtml = '<tr><td colspan="3" class="muted">Sin eventos registrados.</td></tr>';
        }

        $unidadTxt = trim(implode(' ', array_filter([
            (string) ($unidad['marca'] ?? ''),
            (string) ($unidad['modelo'] ?? ''),
            (string) ($unidad['anio'] ?? ''),
        ])));
        if ($unidadTxt === '') {
            $unidadTxt = 'Sin unidad registrada';
        }

        $fichaMotoHtml = '<table class="grid">
                <tr>
                    <td><div class="label">Marca</div><div class="value">' . $this->madjPdfEsc($unidad['marca'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Modelo</div><div class="value">' . $this->madjPdfEsc($unidad['modelo'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Anio</div><div class="value">' . $this->madjPdfEsc($unidad['anio'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Color</div><div class="value">' . $this->madjPdfEsc($unidad['color'] ?? 'No disponible') . '</div></td>
                </tr>
                <tr>
                    <td colspan="2"><div class="label">VIN / Serie</div><div class="value">' . $this->madjPdfEsc($unidad['vin'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Motor</div><div class="value">' . $this->madjPdfEsc($unidad['motor'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Placas</div><div class="value">' . $this->madjPdfEsc($unidad['placas'] ?? 'No disponible') . '</div></td>
                </tr>
                <tr>
                    <td><div class="label">Factura marca</div><div>' . $this->madjPdfEsc($unidad['factura_marca'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Factura modelo</div><div>' . $this->madjPdfEsc($unidad['factura_modelo'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Factura serie</div><div>' . $this->madjPdfEsc($unidad['factura_serie'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Factura motor</div><div>' . $this->madjPdfEsc($unidad['factura_motor'] ?? 'No disponible') . '</div></td>
                </tr>
                <tr>
                    <td><div class="label">Responsable entrega</div><div>' . $this->madjPdfEsc($contacto['responsable_entrega'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Telefono contacto</div><div>' . $this->madjPdfEsc($contacto['telefono_contacto'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Dias mora</div><div>' . $this->madjPdfEsc($finanzas['dias_mora'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Adeudo total</div><div>' . $this->madjPdfEsc($finanzas['adeudo_total'] ?? 'No disponible') . '</div></td>
                </tr>
                <tr>
                    <td colspan="2"><div class="label">Direccion recoleccion</div><div>' . $this->madjPdfEsc($ubicacion['direccion_recoleccion'] ?? $ubicacion['direccion'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Lugar resguardo</div><div>' . $this->madjPdfEsc(trim(($ubicacion['lugar_resguardo'] ?? '') . ' / ' . ($ubicacion['lugar_otro'] ?? ''), ' /') ?: 'No disponible') . '</div></td>
                    <td><div class="label">Responsable resguardo</div><div>' . $this->madjPdfEsc($ubicacion['responsable_resguardo'] ?? 'No disponible') . '</div></td>
                </tr>
                <tr>
                    <td><div class="label">Telefono resguardo</div><div>' . $this->madjPdfEsc($ubicacion['telefono_resguardo'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Saldo capital</div><div>' . $this->madjPdfEsc($finanzas['saldo_capital'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Alta operacion</div><div>' . $this->madjPdfEsc($credito['fecha_alta_fmt'] ?? 'No disponible') . '</div></td>
                    <td><div class="label">Ultima actualizacion</div><div>' . $this->madjPdfEsc($credito['fecha_actualizacion_fmt'] ?? 'No disponible') . '</div></td>
                </tr>
            </table>';

        return '<!doctype html><html><head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;color:#1f2937;font-size:11px}
            .hero{background:#24334f;color:#fff;padding:18px;border-radius:10px;margin-bottom:14px}
            .hero h1{font-size:22px;margin:0 0 6px}
            .hero p{margin:0;color:#dbeafe}
            .grid{width:100%;border-collapse:collapse;margin-bottom:12px}
            .grid td{border:1px solid #d8e2ef;padding:8px;vertical-align:top}
            .label{font-size:9px;color:#64748b;text-transform:uppercase;font-weight:bold}
            .value{font-size:13px;font-weight:bold;color:#0f172a}
            table.data{width:100%;border-collapse:collapse;margin-top:8px}
            table.data th{background:#eef6ff;text-align:left;color:#334155;font-size:10px;padding:8px;border:1px solid #d8e2ef}
            table.data td{padding:8px;border:1px solid #d8e2ef;vertical-align:top}
            table.data span{color:#64748b}
            a.link{color:#0f766e;font-weight:bold;text-decoration:none}
            .badge{display:inline-block;border-radius:999px;padding:4px 8px;font-weight:bold;font-size:9px}
            .ok{background:#dcfce7;color:#166534}.run{background:#dbeafe;color:#1d4ed8}.wait{background:#f1f5f9;color:#475569}
            .section{font-size:15px;color:#0f172a;margin:16px 0 6px;font-weight:bold}
            .muted{color:#64748b}
        </style></head><body>
            <div class="hero">
                <h1>Timeline por credito</h1>
                <p>Expediente operativo consolidado de Motos Adjudicadas</p>
            </div>

            <table class="grid">
                <tr>
                    <td width="33%"><div class="label">Credito</div><div class="value">#' . $this->madjPdfEsc($credito['id_credito'] ?? '') . '</div></td>
                    <td width="33%"><div class="label">Operacion</div><div class="value">' . $this->madjPdfEsc($credito['folio'] ?? '') . '</div></td>
                    <td width="34%"><div class="label">Estatus</div><div class="value">' . $this->madjPdfEsc($credito['estatus'] ?? '') . '</div></td>
                </tr>
                <tr>
                    <td colspan="2"><div class="label">Cliente</div><div class="value">' . $this->madjPdfEsc($credito['nombre_cliente'] ?? '') . '</div></td>
                    <td><div class="label">Unidad</div><div class="value">' . $this->madjPdfEsc($unidadTxt) . '</div></td>
                </tr>
                <tr>
                    <td colspan="3"><div class="label">Ubicacion</div><div>' . $this->madjPdfEsc(trim(($ubicacion['estado'] ?? '') . ' / ' . ($ubicacion['municipio'] ?? '') . ' / ' . ($ubicacion['direccion'] ?? ''), ' /')) . '</div></td>
                </tr>
            </table>

            <div class="section">Ficha de motocicleta</div>
            ' . $fichaMotoHtml . '

            <div class="section">Resumen</div>
            <table class="grid">
                <tr>
                    <td><div class="label">Eventos</div><div class="value">' . $this->madjPdfEsc($resumen['total_eventos'] ?? 0) . '</div></td>
                    <td><div class="label">Evidencias</div><div class="value">' . $this->madjPdfEsc($resumen['total_evidencias'] ?? 0) . '</div></td>
                    <td><div class="label">Rutas</div><div class="value">' . $this->madjPdfEsc($resumen['total_rutas'] ?? 0) . '</div></td>
                </tr>
            </table>

            <div class="section">Etapas del proceso</div>
            <table class="data"><thead><tr><th>Etapa</th><th>Estatus</th><th>Fecha</th></tr></thead><tbody>' . $etapasHtml . '</tbody></table>

            <div class="section">Eventos del credito</div>
            <table class="data"><thead><tr><th width="22%">Fecha</th><th>Evento</th><th width="24%">Origen</th></tr></thead><tbody>' . $eventosHtml . '</tbody></table>
        </body></html>';
    }

    private function pushLegacyConfig(): array
    {
        $cfg = function_exists('config_api_load_from_db') ? config_api_load_from_db() : [];
        $leerValor = static function (array $keys) use ($cfg): string {
            foreach ($keys as $key) {
                $valor = trim((string) ($cfg[$key] ?? ''));
                if ($valor !== '') {
                    return $valor;
                }
                $env = getenv($key);
                if ($env !== false && trim((string) $env) !== '') {
                    return trim((string) $env);
                }
            }
            return '';
        };

        $baseUrl = $leerValor(['MOTOS_ADJUDICADAS_PUSH_BASE_URL']);
        if ($baseUrl === '') {
            $baseUrl = 'https://motosadjudicadas-601258367060.us-central1.run.app';
        }
        $apiKey = $leerValor(['MOTOS_ADJUDICADAS_API_KEY', 'MOTOS_ADJUDICADAS_TOKEN']);

        return [
            'base_url' => rtrim($baseUrl, '/'),
            'api_key' => $apiKey,
        ];
    }

    private function pushLegacyCurl(string $url, array $payload): array
    {
        if (!function_exists('curl_init')) {
            return [
                'http_code' => 0,
                'body' => '',
                'error' => 'cURL no esta disponible en este servidor.',
            ];
        }

        $cfg = $this->pushLegacyConfig();
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json',
                'X-API-Key: ' . $cfg['api_key'],
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'body' => $raw === false ? '' : (string) $raw,
            'error' => $err ?: null,
        ];
    }

    private function legacyApiUrl(array $cfg, string $path): string
    {
        $base = rtrim((string) ($cfg['base_url'] ?? ''), '/');
        $path = '/' . ltrim($path, '/');

        if (substr(strtolower($base), -4) === '/api' && substr($path, 0, 5) === '/api/') {
            $path = substr($path, 4);
        }

        return $base . $path;
    }

    private function legacyApiRequest(string $method, string $path, ?array $payload = null): array
    {
        $cfg = $this->pushLegacyConfig();
        if (($cfg['base_url'] ?? '') === '' || ($cfg['api_key'] ?? '') === '') {
            return [
                'http_code' => 0,
                'body' => '',
                'decoded' => null,
                'error' => 'Servicio Legacy no configurado. Configure MOTOS_ADJUDICADAS_API_KEY o MOTOS_ADJUDICADAS_TOKEN.',
            ];
        }
        if (!function_exists('curl_init')) {
            return [
                'http_code' => 0,
                'body' => '',
                'decoded' => null,
                'error' => 'cURL no esta disponible en este servidor.',
            ];
        }

        $method = strtoupper($method);
        $headers = [
            'Accept: application/json',
            'X-API-Key: ' . $cfg['api_key'],
        ];

        $ch = curl_init($this->legacyApiUrl($cfg, $path));
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json; charset=utf-8';
            $opts[CURLOPT_HTTPHEADER] = $headers;
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $body = $raw === false ? '' : (string) $raw;
        $decoded = json_decode($body, true);

        return [
            'http_code' => $httpCode,
            'body' => $body,
            'decoded' => is_array($decoded) ? $decoded : null,
            'error' => $err ?: null,
        ];
    }

    private function buscarValorRecursivo($data, array $keys): string
    {
        if (!is_array($data)) {
            return '';
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && trim((string) $data[$key]) !== '') {
                return trim((string) $data[$key]);
            }
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $found = $this->buscarValorRecursivo($value, $keys);
                if ($found !== '') {
                    return $found;
                }
            }
        }

        return '';
    }

    private function extraerCodigoEntregaLegacy(array $data): string
    {
        $codigo = $this->buscarValorRecursivo($data, ['codigo_entrega', 'codigoEntrega', 'codigo_acceso_legacy']);
        return preg_match('/^\d{6}$/', $codigo) ? $codigo : '';
    }

    private function estatusOperacionLegacy(array $data): string
    {
        return $this->buscarValorRecursivo($data, ['estatus_operativo', 'estatus', 'status', 'estado']);
    }

    private function mensajeBloqueoCodigoLegacy(array $data): string
    {
        $estatusRaw = $this->estatusOperacionLegacy($data);
        $estatus = function_exists('mb_strtolower') ? mb_strtolower($estatusRaw, 'UTF-8') : strtolower($estatusRaw);
        $estatus = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $estatus);

        if ($estatus === '') {
            return '';
        }
        if (strpos($estatus, 'entreg') !== false || strpos($estatus, 'finaliz') !== false || strpos($estatus, 'concluid') !== false || strpos($estatus, 'complet') !== false) {
            return 'La operacion ya fue entregada o finalizada; no permite generar codigo Legacy.';
        }
        if (strpos($estatus, 'cancel') !== false) {
            return 'La operacion esta cancelada; no permite generar codigo Legacy.';
        }

        return '';
    }

    private function consultarDetalleLegacyOperacion(int $idOperacion): array
    {
        $resp = $this->legacyApiRequest('GET', '/api/adjudicatd-motocycle/in-progress/' . rawurlencode((string) $idOperacion));
        $decoded = $resp['decoded'];
        $ok = $resp['http_code'] >= 200 && $resp['http_code'] < 300 && is_array($decoded);

        if (!$ok) {
            $mensaje = is_array($decoded)
                ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? '')
                : '';

            return [
                'success' => false,
                'message' => $mensaje !== '' ? $mensaje : ($resp['error'] ?: 'No se pudo consultar la operacion Legacy.'),
                'http_code' => $resp['http_code'],
                'detalle' => $decoded,
            ];
        }

        return [
            'success' => true,
            'message' => 'Operacion consultada correctamente.',
            'http_code' => $resp['http_code'],
            'detalle' => $decoded,
            'codigo_entrega' => $this->extraerCodigoEntregaLegacy($decoded),
            'estatus' => $this->estatusOperacionLegacy($decoded),
            'bloqueo' => $this->mensajeBloqueoCodigoLegacy($decoded),
        ];
    }

    private function persistirCodigoEntregaLegacy(int $idOperacion, string $codigo, bool $regenerar): array
    {
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SPARTA'));
        $payload = [
            'id_operacion' => $idOperacion,
            'codigo_entrega' => $codigo,
            'regenerar' => $regenerar,
            'origen' => 'sparta_otp_emergencia',
            'id_usuario_sparta' => $idUsuario,
            'nombre_usuario_sparta' => $nombreUsuario,
        ];

        $candidatos = [
            ['POST', '/api/moto-entrega-proceso-legacy/' . rawurlencode((string) $idOperacion) . '/codigo-entrega/generar'],
            ['PATCH', '/api/moto-entrega-proceso-legacy/' . rawurlencode((string) $idOperacion) . '/codigo-entrega'],
            ['POST', '/api/adjudicatd-motocycle/in-progress/' . rawurlencode((string) $idOperacion) . '/codigo-entrega'],
            ['PATCH', '/api/adjudicatd-motocycle/in-progress/' . rawurlencode((string) $idOperacion) . '/codigo-entrega'],
            ['POST', '/api/moto-entrega-proceso-legacy/' . rawurlencode((string) $idOperacion) . '/otp-emergencia'],
        ];

        $ultimo = null;
        foreach ($candidatos as [$method, $path]) {
            $resp = $this->legacyApiRequest($method, $path, $payload);
            $ultimo = $resp;
            $decoded = $resp['decoded'];
            if ($resp['http_code'] === 404 || $resp['http_code'] === 405) {
                continue;
            }

            if ($resp['http_code'] >= 200 && $resp['http_code'] < 300) {
                return [
                    'success' => true,
                    'message' => 'Codigo Legacy generado correctamente.',
                    'codigo_entrega' => $this->extraerCodigoEntregaLegacy(is_array($decoded) ? $decoded : []) ?: $codigo,
                    'http_code' => $resp['http_code'],
                    'backend' => $decoded,
                ];
            }

            $mensaje = is_array($decoded)
                ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? '')
                : '';

            return [
                'success' => false,
                'message' => $mensaje !== '' ? $mensaje : ($resp['error'] ?: 'No se pudo guardar el codigo Legacy.'),
                'http_code' => $resp['http_code'],
                'backend' => $decoded,
            ];
        }

        try {
            $local = $this->model->guardarCodigoEntregaLegacyLocal($idOperacion, $codigo, $idUsuario, $nombreUsuario);
            if (!empty($local['success'])) {
                $local['message'] = 'Codigo Legacy generado correctamente.';
                $local['backend_endpoint_missing'] = true;
                return $local;
            }
            if (!empty($local['message'])) {
                return [
                    'success' => false,
                    'message' => (string) $local['message'],
                    'fallback_local' => $local,
                    'http_code' => (int) ($ultimo['http_code'] ?? 0),
                    'backend' => $ultimo['decoded'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            // Se conserva el error operativo del servicio; el fallback local no debe romper el flujo.
        }

        return [
            'success' => false,
            'message' => 'No se pudo guardar el codigo Legacy. Intenta de nuevo o valida que la base permita guardar el codigo de acceso.',
            'http_code' => (int) ($ultimo['http_code'] ?? 0),
            'backend' => $ultimo['decoded'] ?? null,
        ];
    }

    private function resolverOperacionOtpLegacy(array $input): array
    {
        $idCredito = (int) ($input['id_credito'] ?? $input['idCredito'] ?? $input['credito'] ?? 0);
        if ($idCredito > 0) {
            $res = $this->model->obtenerOperacionOtpLegacyPorCredito($idCredito);
            if (empty($res['success']) || empty($res['operacion']['id_operacion'])) {
                return [
                    'success' => false,
                    'message' => $res['message'] ?? 'No se encontro una operacion para este credito.',
                ];
            }

            return [
                'success' => true,
                'id_operacion' => (int) $res['operacion']['id_operacion'],
                'id_credito' => (int) $res['operacion']['id_credito'],
                'operacion' => $res['operacion'],
                'origen_busqueda' => 'id_credito',
            ];
        }

        $idOperacion = (int) ($input['id_operacion'] ?? $input['idOperacion'] ?? 0);
        if ($idOperacion > 0) {
            $detalleLocal = $this->model->obtenerDetalle($idOperacion);
            return [
                'success' => true,
                'id_operacion' => $idOperacion,
                'id_credito' => (int) ($detalleLocal['id_credito'] ?? 0),
                'operacion' => [
                    'id_operacion' => $idOperacion,
                    'folio' => (string) ($detalleLocal['folio'] ?? ''),
                    'id_credito' => (int) ($detalleLocal['id_credito'] ?? 0),
                    'nombre_cliente' => trim((string) ($detalleLocal['nombre_cliente'] ?? '')),
                    'estatus' => (string) ($detalleLocal['estatus'] ?? ''),
                    'moto' => trim((string) (($detalleLocal['moto_marca'] ?? '') . ' ' . ($detalleLocal['moto_modelo'] ?? ''))),
                ],
                'origen_busqueda' => 'id_operacion',
            ];
        }

        return ['success' => false, 'message' => 'Indica un ID de credito valido.'];
    }

    public function consultarCodigoAccesoLegacy()
    {
        header('Content-Type: application/json; charset=utf-8');
        $resuelta = $this->resolverOperacionOtpLegacy($_GET);
        if (empty($resuelta['success'])) {
            echo json_encode(['success' => false, 'message' => $resuelta['message'] ?? 'Indica un ID de credito valido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $detalle = $this->consultarDetalleLegacyOperacion((int) $resuelta['id_operacion']);
        $detalle['id_credito'] = (int) ($resuelta['id_credito'] ?? 0);
        $detalle['operacion'] = $resuelta['operacion'] ?? [];
        echo json_encode($detalle, JSON_UNESCAPED_UNICODE);
    }

    public function generarCodigoAccesoLegacy()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
        if (!$body && !empty($_POST)) {
            $body = $_POST;
        }

        $resuelta = $this->resolverOperacionOtpLegacy($body);
        $regenerar = filter_var($body['regenerar'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (empty($resuelta['success'])) {
            echo json_encode(['success' => false, 'message' => $resuelta['message'] ?? 'Indica un ID de credito valido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $idOperacion = (int) $resuelta['id_operacion'];
        $detalle = $this->consultarDetalleLegacyOperacion($idOperacion);
        if (!empty($detalle['success'])) {
            if (!empty($detalle['bloqueo'])) {
                echo json_encode(['success' => false, 'message' => $detalle['bloqueo'], 'detalle' => $detalle, 'operacion' => $resuelta['operacion'] ?? []], JSON_UNESCAPED_UNICODE);
                return;
            }

            $codigoActivo = (string) ($detalle['codigo_entrega'] ?? '');
            if ($codigoActivo !== '' && !$regenerar) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Ya existe un codigo Legacy activo.',
                    'codigo_entrega' => $codigoActivo,
                    'codigo_activo' => true,
                    'requiere_confirmacion_regenerar' => true,
                    'detalle' => $detalle,
                    'id_credito' => (int) ($resuelta['id_credito'] ?? 0),
                    'operacion' => $resuelta['operacion'] ?? [],
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        } elseif ((int) ($detalle['http_code'] ?? 0) === 404) {
            echo json_encode(['success' => false, 'message' => 'Operacion Legacy no encontrada.', 'detalle' => $detalle, 'operacion' => $resuelta['operacion'] ?? []], JSON_UNESCAPED_UNICODE);
            return;
        }

        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $persistido = $this->persistirCodigoEntregaLegacy($idOperacion, $codigo, $regenerar);
        $persistido['id_credito'] = (int) ($resuelta['id_credito'] ?? 0);
        $persistido['operacion'] = $resuelta['operacion'] ?? [];
        echo json_encode($persistido, JSON_UNESCAPED_UNICODE);
    }

    private function normalizarListaIds($valor): array
    {
        if (is_array($valor)) {
            $items = $valor;
        } else {
            $items = preg_split('/[\s,;]+/', (string) $valor) ?: [];
        }

        $ids = [];
        foreach ($items as $item) {
            $id = trim((string) $item);
            if ($id === '') {
                continue;
            }
            $ids[$id] = $id;
        }

        return array_values($ids);
    }

    /**
     * POST /MotosAdjudicadas/enviarCampaniaNotificacionLegacy
     */
    public function enviarCampaniaNotificacionLegacy()
    {
        header('Content-Type: application/json; charset=utf-8');

        $cfg = $this->pushLegacyConfig();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Servicio de notificaciones no configurado. Configure MOTOS_ADJUDICADAS_TOKEN en config_api o en el entorno.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $titulo = trim((string) ($body['titulo'] ?? ''));
        $mensaje = trim((string) ($body['mensaje'] ?? ''));

        if ($titulo === '' || $mensaje === '') {
            echo json_encode(['success' => false, 'message' => 'Título y mensaje son obligatorios.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $data = $body['data'] ?? [];
        if (!is_array($data)) {
            echo json_encode(['success' => false, 'message' => 'El campo data debe ser un objeto JSON valido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (($data['type'] ?? '') === 'aviso_especial') {
            $data['screen'] = 'NotificacionEspecial';
        }

        $campaignId = trim((string) ($data['campaign_id'] ?? $body['campaign_id'] ?? ''));
        if ($campaignId === '') {
            $campaignId = 'camp_' . date('Ymd_His');
        }

        $payload = [
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'segmento' => 'all',
            'user_id_legacy' => $this->normalizarListaIds($body['user_id_legacy'] ?? []),
            'external_id' => $this->normalizarListaIds($body['external_id'] ?? []),
            'data' => array_merge([
                'type' => 'campaign',
                'screen' => 'Home',
            ], $data, [
                'campaign_id' => $campaignId,
            ]),
            'created_by' => trim((string) ($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? $_SESSION['usuario_nombre'] ?? 'sparta_backend')),
        ];

        $url = $cfg['base_url'] . '/api/push-campaigns/legacy/send';
        $resp = $this->pushLegacyCurl($url, $payload);
        $decoded = json_decode($resp['body'], true);

        if ($resp['http_code'] < 200 || $resp['http_code'] >= 300) {
            echo json_encode([
                'success' => false,
                'message' => is_array($decoded)
                    ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? 'No se pudo enviar la campaña.')
                    : ($resp['error'] ?: 'No se pudo enviar la campaña.'),
                'http_code' => $resp['http_code'],
                'api_response' => $decoded ?: $resp['body'],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Campaña enviada correctamente.',
            'http_code' => $resp['http_code'],
            'api_response' => is_array($decoded) ? $decoded : null,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /MotosAdjudicadas/enviarComentariosLegacy
     */
    public function enviarComentariosLegacy()
    {
        header('Content-Type: application/json; charset=utf-8');

        $cfg = $this->pushLegacyConfig();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Servicio de notificaciones no configurado. Configure MOTOS_ADJUDICADAS_TOKEN en config_api o en el entorno.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $appVersion = trim((string) ($body['app_version'] ?? ''));
        $campaignId = trim((string) ($body['campaign_id'] ?? ''));
        if ($campaignId === '') {
            $campaignId = 'feedback_' . date('Ymd_His');
        }

        $payload = [
            'titulo' => trim((string) ($body['titulo'] ?? 'Ayudanos a mejorar')),
            'mensaje' => trim((string) ($body['mensaje'] ?? 'Queremos conocer tu opinion sobre Legacy.')),
            'segmento' => 'all',
            'user_id_legacy' => $this->normalizarListaIds($body['user_id_legacy'] ?? []),
            'external_id' => $this->normalizarListaIds($body['external_id'] ?? []),
            'platform' => trim((string) ($body['platform'] ?? '')),
            'app_version' => $appVersion !== '' ? $appVersion : null,
            'data' => [
                'type' => 'legacy_feedback_request',
                'notification_type' => 'legacy_feedback_request',
                'screen' => 'LegacyFeedback',
                'campaign_id' => $campaignId,
                'feedback_campaign' => '1',
                'target_app_version' => $appVersion,
                'force_feedback' => '1',
            ],
            'created_by' => trim((string) ($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? $_SESSION['usuario_nombre'] ?? 'sparta_backend')),
        ];

        if ($appVersion === '') {
            unset($payload['app_version'], $payload['data']['target_app_version']);
        }

        if ($payload['titulo'] === '' || $payload['mensaje'] === '') {
            echo json_encode(['success' => false, 'message' => 'Titulo y mensaje son obligatorios.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($payload['platform'] === '') {
            unset($payload['platform']);
        }
        $url = $cfg['base_url'] . '/api/push-campaigns/legacy/send';
        $resp = $this->pushLegacyCurl($url, $payload);
        $decoded = json_decode($resp['body'], true);

        if ($resp['http_code'] < 200 || $resp['http_code'] >= 300) {
            echo json_encode([
                'success' => false,
                'message' => is_array($decoded)
                    ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? 'No se pudo enviar la campania de comentarios.')
                    : ($resp['error'] ?: 'No se pudo enviar la campania de comentarios.'),
                'http_code' => $resp['http_code'],
                'api_response' => $decoded ?: $resp['body'],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Campania de comentarios enviada correctamente.',
            'http_code' => $resp['http_code'],
            'api_response' => is_array($decoded) ? $decoded : null,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /MotosAdjudicadas/obtenerListaDictamenes
     */
    public function obtenerListaDictamenes()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
            if ($limit !== null && $limit <= 0) {
                $limit = null;
            }
            $offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
            $modoRapido = isset($_GET['rapido']) && (string) $_GET['rapido'] === '1';

            $result = $this->model->obtenerListaDictumsMotos($limit, $offset, $modoRapido);
            echo json_encode(
                [
                    'success'  => true,
                    'rows'     => $result['rows'],
                    'has_more' => $result['has_more'],
                ],
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/obtenerListaDictamenesCompleta
     * Segunda fase opcional para refrescar lista completa en background (sin repetir el mismo endpoint en Network).
     */
    public function obtenerListaDictamenesCompleta()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $result = $this->model->obtenerListaDictumsMotos(null, 0, false);
            echo json_encode(
                [
                    'success'  => true,
                    'rows'     => $result['rows'],
                    'has_more' => $result['has_more'],
                ],
                JSON_UNESCAPED_UNICODE
            );
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/resolverNombresClienteDictamenes?ids=1,2,3
     * Resuelve y cachea nombres por lote para la lista de dictámenes.
     */
    public function resolverNombresClienteDictamenes()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = trim((string) ($_GET['ids'] ?? ''));
            if ($raw === '') {
                echo json_encode(['success' => true, 'nombres' => []], JSON_UNESCAPED_UNICODE);
                return;
            }
            $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw)), static fn($v) => $v > 0)));
            if ($ids === []) {
                echo json_encode(['success' => true, 'nombres' => []], JSON_UNESCAPED_UNICODE);
                return;
            }
            $map = $this->model->resolverNombresClienteDictamenesPorCreditos($ids);
            echo json_encode(['success' => true, 'nombres' => $map], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — BUSCAR CRÉDITO EN ADJUDICACIÓN
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/buscarCredito
     * Body JSON: { "valor": 1637 }
     * Verifica asignación activa en asigna_creditos_adjudicacion y enriquece con S2.
     */
    public function buscarCredito()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $valor = (int) ($body['valor'] ?? 0);

        if ($valor <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        try {
            $result = $this->model->buscarCreditoEnAdjudicacion($valor);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — SUBIR EVIDENCIA
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/subirEvidencia  (multipart/form-data)
     * Fields: id_operacion, slot
     * File:   archivo
     */
    public function subirEvidencia()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idOperacion = (int) ($_POST['id_operacion'] ?? 0);
        $slot        = trim($_POST['slot'] ?? '');

        if ($idOperacion <= 0 || $slot === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $code = $_FILES['archivo']['error'] ?? -1;
            echo json_encode(['success' => false, 'message' => "Error de subida (código {$code})."]);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->subirEvidencia($idOperacion, $slot, $_FILES['archivo'], $idUsuario, $nombreUsuario);
            if ($result['success']) {
                $bit = $this->model->obtenerBitacora($idOperacion);
                $result['bitacora_entry'] = $bit[0] ?? null;
            }
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/reemplazarEvidenciaGestor  (multipart/form-data)
     * Permiso especial modulos_web 79. Reemplaza una evidencia física desde Atención.
     */
    public function reemplazarEvidenciaGestor()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->tieneModuloSesion(self::MODULO_REEMPLAZAR_EVIDENCIA_GESTOR)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para reemplazar evidencias.']);
            return;
        }

        $idOperacion = (int) ($_POST['id_operacion'] ?? 0);
        $slot        = trim($_POST['slot'] ?? '');

        if ($idOperacion <= 0 || $slot === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $code = $_FILES['archivo']['error'] ?? -1;
            echo json_encode(['success' => false, 'message' => "Error de subida (código {$code})."]);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->reemplazarEvidenciaGestor($idOperacion, $slot, $_FILES['archivo'], $idUsuario, $nombreUsuario);
            if ($result['success']) {
                $bit = $this->model->obtenerBitacora($idOperacion);
                $result['bitacora_entry'] = $bit[0] ?? null;
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // =========================================================================
    // API — PIPELINE
    // =========================================================================

    /**
     * GET /MotosAdjudicadas/obtenerOperaciones?q=texto&limit=500
     * Devuelve tarjetas del kanban con limite defensivo y busqueda server-side.
     */
    public function obtenerOperaciones()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $q = trim((string) ($_GET['q'] ?? ''));
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 500;
            $result = $this->model->obtenerPipeline($q, $limit);
            echo json_encode([
                'success' => true,
                'operaciones' => $result['rows'] ?? [],
                'total' => (int) ($result['total'] ?? 0),
                'limit' => (int) ($result['limit'] ?? $limit),
                'q' => $q,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/obtenerMonitoreoAdjudicaciones
     */
    public function obtenerMonitoreoAdjudicaciones()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $filtros = is_array($body['filtros'] ?? null) ? $body['filtros'] : $body;

        try {
            $rows = $this->model->obtenerMonitoreoAdjudicaciones($filtros);
            echo json_encode(['success' => true, 'datos' => $rows], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/buscarPersonasMonitoreo
     */
    public function buscarPersonasMonitoreo()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $buscar = trim((string) ($body['buscar'] ?? $body['q'] ?? ''));

        try {
            $rows = $this->model->buscarPersonasParaMonitoreo($buscar);
            echo json_encode(['success' => true, 'datos' => $rows], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/buscarDestinatariosCampaniaLegacy
     */
    public function buscarDestinatariosCampaniaLegacy()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $buscar = trim((string) ($body['buscar'] ?? $body['q'] ?? ''));

        try {
            $rows = $this->model->buscarDestinatariosCampaniaLegacy($buscar);
            echo json_encode(['success' => true, 'datos' => $rows], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * POST /MotosAdjudicadas/reasignarMonitoreoAdjudicacion
     */
    public function reasignarMonitoreoAdjudicacion()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $idPersona = (int) ($body['id_persona'] ?? 0);

        if ($idOperacion <= 0 || $idPersona <= 0) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->reasignarCreditoMonitoreo($idOperacion, $idPersona, $idUsuario, $nombreUsuario);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/obtenerDetalle/{id}?incluir_todas=1
     */
    public function obtenerDetalle($id = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int) $id;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }
        try {
            $detalle = $this->model->obtenerDetalle($id);
            if (!$detalle) {
                echo json_encode(['success' => false, 'message' => 'Operación no encontrada.']);
                return;
            }
            // Pipeline / Kanban: solo filas ya enviadas al pipeline (recibido).
            // incluir_todas=1 (Atención a clientes, refresco tras subir Repuve): incluye pendiente_envio.
            $incluirTodas = isset($_GET['incluir_todas'])
                && ($_GET['incluir_todas'] === '1' || $_GET['incluir_todas'] === 'true');
            if (!$incluirTodas) {
                $detalle['evidencias'] = array_values(
                    array_filter($detalle['evidencias'] ?? [], fn($e) => ($e['estatus'] ?? 'recibido') === 'recibido')
                );
            }
            echo json_encode(['success' => true, 'detalle' => $detalle]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/descargarEvidenciasSeleccionadas
     * Body JSON: { "id_operacion": 5, "slots": ["fis_frontal", "doc_repuve"] }
     */
    public function descargarEvidenciasSeleccionadas()
    {
        if (!class_exists('\ZipArchive')) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'El servidor no tiene habilitada la extension ZipArchive.']);
            return;
        }

        $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $slots = $body['slots'] ?? [];
        $slots = is_array($slots)
            ? array_values(array_unique(array_filter(array_map('strval', $slots))))
            : [];

        if ($idOperacion <= 0 || empty($slots)) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Selecciona al menos una evidencia valida.']);
            return;
        }

        $tmpZip = tempnam(sys_get_temp_dir(), 'sp_zip_');
        $temporales = [];
        if (!$tmpZip) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo crear el archivo temporal.']);
            return;
        }
        @unlink($tmpZip);
        $tmpZip .= '.zip';

        try {
            $detalle = $this->model->obtenerDetalle($idOperacion);
            if (!$detalle) {
                throw new \RuntimeException('Operacion no encontrada.');
            }

            $porSlot = [];
            foreach (($detalle['evidencias'] ?? []) as $ev) {
                if (!is_array($ev) || empty($ev['slot']) || empty($ev['url'])) {
                    continue;
                }
                $porSlot[(string) $ev['slot']] = $ev;
            }

            $zip = new \ZipArchive();
            if ($zip->open($tmpZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('No se pudo generar el ZIP.');
            }

            $agregadas = 0;
            foreach ($slots as $slot) {
                if (empty($porSlot[$slot]['url'])) {
                    continue;
                }
                $archivo = $this->resolverArchivoEvidencia((string) $porSlot[$slot]['url']);
                if (!$archivo || empty($archivo['path']) || !is_file($archivo['path'])) {
                    continue;
                }

                if (!empty($archivo['temp'])) {
                    $temporales[] = $archivo['path'];
                }

                $agregadas++;
                $label = $this->slugArchivoEvidencia($this->etiquetaEvidenciaPorSlot($slot));
                $ext = (string) ($archivo['ext'] ?? 'bin');
                $nombre = sprintf('%02d_%s.%s', $agregadas, $label, $ext);
                $zip->addFile($archivo['path'], $nombre);
            }

            $zip->close();

            if ($agregadas <= 0 || !is_file($tmpZip) || filesize($tmpZip) <= 0) {
                throw new \RuntimeException('No se pudo leer ningun archivo seleccionado.');
            }

            $idCredito = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($detalle['id_credito'] ?? $idOperacion));
            $downloadName = 'evidencias_' . ($idCredito !== '' ? $idCredito : $idOperacion) . '_' . date('Ymd_His') . '.zip';

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Content-Length: ' . filesize($tmpZip));
            header('Cache-Control: no-store, no-cache, must-revalidate');
            readfile($tmpZip);
        } catch (\Throwable $e) {
            if (is_file($tmpZip)) {
                @unlink($tmpZip);
            }
            foreach ($temporales as $tmp) {
                if (is_file($tmp)) {
                    @unlink($tmp);
                }
            }
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }

        if (is_file($tmpZip)) {
            @unlink($tmpZip);
        }
        foreach ($temporales as $tmp) {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }
        exit;
    }

    /**
     * GET /MotosAdjudicadas/recepcionResumenFinanciero?id_credito=N
     * Saldo capital y adeudo total desde API S2 (estado de cuenta).
     */
    public function recepcionResumenFinanciero()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idCred = (int) ($_GET['id_credito'] ?? 0);
        if ($idCred <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        try {
            $res = $this->model->obtenerResumenFinancieroEstadoCuentaS2($idCred);
            echo json_encode($res);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/obtenerResumenS2ModalDictamen?id_credito=N
     * Datos S2 (estado de cuenta) para la sección «S2» del modal Lista Dictámenes.
     */
    public function obtenerResumenS2ModalDictamen()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idCred = (int) ($_GET['id_credito'] ?? 0);
        if ($idCred <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        try {
            $res = $this->model->obtenerResumenS2ModalDictamen($idCred);
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/guardarSeguimientoMaDictamen
     * Body JSON: id_credito, comentarios (obligatorio), aplica (0|1) para recolección,
     * gestor_manual (bool): si true, id_persona_responsable es el elegido en catálogo; si false, se usa id_persona_responsable_default (Gestor Legacy).
     * La asignación llama a Adjudicacion::asignarCredito, que crea fila en personal_adjudicacion si aún no existe.
     * Persiste seguimiento en adj_s2_cache_dictamen (ma_seg_comentarios, ma_seg_aplica, ma_seg_actualizado_at).
     * Asignación a Mis adjudicaciones solo si aplica === 1.
     */
    public function guardarSeguimientoMaDictamen()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body                   = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito              = (int) ($body['id_credito'] ?? $body['id_dictum'] ?? 0);
        $comentarios            = trim((string) ($body['comentarios'] ?? ''));
        $idPersonaManual        = (int) ($body['id_persona_responsable'] ?? 0);
        $idPersonaDefault       = (int) ($body['id_persona_responsable_default'] ?? 0);
        $gestorManualRaw        = $body['gestor_manual'] ?? false;
        $gestorManual           = ($gestorManualRaw === true || $gestorManualRaw === 1 || $gestorManualRaw === '1');
        $aplicaRaw              = $body['aplica'] ?? null;
        $aplica                 = null;
        if ($aplicaRaw === 0 || $aplicaRaw === '0' || $aplicaRaw === false) {
            $aplica = 0;
        } elseif ($aplicaRaw === 1 || $aplicaRaw === '1' || $aplicaRaw === true) {
            $aplica = 1;
        }
        $datosTaskLegacy = [
            'lat'          => $body['lat'] ?? null,
            'lng'          => $body['lng'] ?? null,
            'lugar_aprox'  => trim((string) ($body['lugar_aprox'] ?? '')),
        ];

        $idPersonaResponsable = 0;
        $adj                  = new AdjudicacionDAO();
        if ($aplica === 1) {
            if ($gestorManual) {
                if ($idPersonaManual <= 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Seleccione un gestor en la lista (Elegir gestor) o guarde sin selección para usar el Gestor a cargo (Legacy).',
                    ], JSON_UNESCAPED_UNICODE);

                    return;
                }
                $idPersonaResponsable = $idPersonaManual;
            } else {
                if ($idPersonaDefault <= 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No hay Gestor a cargo (Legacy) vinculado a persona en Sparta. Revise dictum → users → número de empleado.',
                    ], JSON_UNESCAPED_UNICODE);

                    return;
                }
                $idPersonaResponsable = $idPersonaDefault;
            }
        }

        try {
            $result = $this->model->guardarSeguimientoMaDictamen($idCredito, $comentarios, $aplica);
            if (!empty($result['success']) && $idCredito > 0 && $aplica === 1) {
                $idUsuarioAlta = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
                if ($idUsuarioAlta > 0) {
                    $asig = $adj->asignarCredito($idPersonaResponsable, $idCredito, $idUsuarioAlta);
                    if (empty($asig['success']) && !empty($asig['message'])) {
                        $m = (string) $asig['message'];
                        if (stripos($m, 'ya está asignado a este responsable') !== false
                            || stripos($m, 'ya esta asignado a este responsable') !== false) {
                            $asig['success'] = true;
                            $asig['message'] = 'El crédito ya estaba asignado al responsable seleccionado.';
                        }
                    }
                    $result['asignacion'] = $asig;
                    if (!empty($asig['success'])) {
                        $taskLegacy = $this->model->crearTaskLegacyMotoAutorizada($idCredito, $idPersonaResponsable, $datosTaskLegacy);
                        $result['task_legacy'] = $taskLegacy;
                        $result['message'] = 'Seguimiento guardado. '
                            . ($asig['message'] ?? 'Crédito asignado correctamente; aparecerá en Mis adjudicaciones del responsable.');
                        $result['message'] .= ' ' . ($taskLegacy['message'] ?? 'Task legacy procesado.');
                    } else {
                        $result['message'] = 'Seguimiento guardado. '
                            . ($asig['message'] ?? 'No se pudo completar la asignación.');
                    }
                } else {
                    $result['asignacion'] = [
                        'success' => false,
                        'message' => 'No hay persona en sesión para registrar la asignación.',
                    ];
                    $result['message'] = 'Seguimiento guardado. '
                        . ($result['asignacion']['message'] ?? '');
                }
            } elseif (!empty($result['success']) && $idCredito > 0 && $aplica === 0) {
                $result['message'] = 'Seguimiento guardado. No aplica para recolección: el crédito no se asignó a Mis adjudicaciones.';
                $result['asignacion'] = [
                    'success'                  => true,
                    'omitida_por_recoleccion'  => true,
                    'message'                  => 'Sin asignación al indicar que no aplica para recolección.',
                ];
            } elseif (!empty($result['success'])) {
                $result['message'] = 'Seguimiento guardado.';
            }
            if (!empty($result['success'])) {
                $result['message'] = 'Seguimiento guardado.';
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/registrarLlegadaAlmacenRecepcion
     * Body JSON: { "id_operacion": 123 }
     */
    public function registrarLlegadaAlmacenRecepcion()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOp    = (int) ($body['id_operacion'] ?? 0);
        $idUsr   = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nomUsr  = trim((string) ($_SESSION['usuario_nombre'] ?? 'SISTEMA'));
        if ($idOp <= 0) {
            echo json_encode(['success' => false, 'message' => 'Operación inválida.']);
            return;
        }
        try {
            echo json_encode($this->model->registrarLlegadaAlmacenRecepcion($idOp, $idUsr, $nomUsr));
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/guardarRecepcionEstadoDocumento
     * Body JSON: { "id_operacion": 1, "documento": "dacion"|"tarjeta", "estado": "pending"|"missing" }
     */
    public function guardarRecepcionEstadoDocumento()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOp   = (int) ($body['id_operacion'] ?? 0);
        $doc    = trim((string) ($body['documento'] ?? ''));
        $estado = trim((string) ($body['estado'] ?? ''));
        $idUsr  = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nomUsr = trim((string) ($_SESSION['usuario_nombre'] ?? 'SISTEMA'));
        if ($idOp <= 0) {
            echo json_encode(['success' => false, 'message' => 'Operación inválida.']);
            return;
        }
        try {
            echo json_encode($this->model->guardarRecepcionEstadoDocumento($idOp, $doc, $estado, $idUsr, $nomUsr));
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/confirmarRecepcionAlmacen
     * Body JSON: { "id_operacion": 1, "ubicacion": "...", "observaciones": "..." }
     */
    public function confirmarRecepcionAlmacen()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOp   = (int) ($body['id_operacion'] ?? 0);
        $ubic   = trim((string) ($body['ubicacion'] ?? ''));
        $obs    = trim((string) ($body['observaciones'] ?? ''));
        $idUsr  = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nomUsr = trim((string) ($_SESSION['usuario_nombre'] ?? 'SISTEMA'));
        if ($idOp <= 0) {
            echo json_encode(['success' => false, 'message' => 'Operación inválida.']);
            return;
        }
        try {
            echo json_encode($this->model->confirmarRecepcionAlmacen($idOp, $ubic, $obs, $idUsr, $nomUsr));
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — CREAR OPERACIÓN
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/crearOperacion
     * Body JSON: {
     *   id_credito, nombre_cliente, responsable_entrega, telefono_contacto,
     *   direccion_recoleccion, marca, modelo, serie, num_motor, placas,
     *   dias_mora, saldo_capital, adeudo_total, area_actual
     * }
     */
    public function crearOperacion()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $idCredito     = (int) ($body['id_credito']     ?? 0);
        $nombreCliente = trim($body['nombre_cliente']   ?? '');

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        if ($nombreCliente === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre del cliente es obligatorio.']);
            return;
        }

        $idUsuario = (int) ($_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        try {
            $result = $this->model->crearOperacion($body, $idUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — CAMBIAR ESTATUS
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/cambiarEstatus
     * Body JSON: { "id": 5, "estatus": "Procesando IA" }
     */
    public function cambiarEstatus()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $id      = (int) ($body['id']     ?? 0);
        $estatus = trim($body['estatus']  ?? '');

        if ($id <= 0 || $estatus === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->cambiarEstatus($id, $estatus, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — OBSERVACIONES
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/agregarObservacion
     * Body JSON: { "id_operacion": 5, "etapa": "Recibido", "area": "Adjudicación", "texto": "..." }
     */
    public function agregarObservacion()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $etapa       = trim($body['etapa']          ?? '');
        $area        = trim($body['area']           ?? '');
        $texto       = trim($body['texto']          ?? '');

        if ($idOperacion <= 0 || $texto === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result        = $this->model->agregarObservacion($idOperacion, $etapa, $area, $idUsuario, $texto, $nombreUsuario);
            // Append last bitácora entry so the frontend can display it immediately
            if ($result['success']) {
                $accionBit = 'AGREGÓ ACCIÓN DE TRAMO: ' . mb_strtoupper(mb_substr($texto, 0, 60)) . (mb_strlen($texto) > 60 ? '…' : '');
                $bit = $this->model->obtenerBitacora($idOperacion);
                $result['bitacora_entry'] = $bit[0] ?? null;
            }
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // API — ELIMINAR OPERACIÓN
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/eliminarOperacion
     * Body JSON: { "id": 5 }
     */
    // =========================================================================
    // MIS ADJUDICACIONES
    // =========================================================================

    /**
     * POST /MotosAdjudicadas/enviarEvidencias
     * Body JSON: { "id_operacion": 5 }
     * Cambia todas las evidencias 'pendiente_envio' de la operación a 'recibido'.
     */
    public function enviarEvidencias()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }

        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->enviarEvidencias($idOperacion, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /MotosAdjudicadas/repuveConsulta
     * Consulta REPUVE por crédito (placa o VIN).
     */
    public function repuveConsulta()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Consulta REPUVE — Motos Adjudicadas ' . $emp);
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        self::set('limite_repuve', $this->model->obtenerInfoLimiteRepuve($idUsuario));
        return self::render('repuve_consulta');
    }

    /**
     * POST /MotosAdjudicadas/misAdjudicaciones
     */
    public function misAdjudicaciones()
    {
        header('Location: /AtencionClientes/evidencias', true, 302);
        exit;
    }

    /**
     * POST /MotosAdjudicadas/obtenerEvidenciasCredito
     * Body JSON: { "id_credito": 12345, "nombre_cliente": "Juan Pérez" }
     * Obtiene (o crea si no existe) la operación del pipeline asociada al crédito
     * y devuelve un detalle compacto para el modal de Mis Adjudicaciones.
     */
    public function obtenerEvidenciasCredito()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body          = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito     = (int)  ($body['id_credito']     ?? 0);
        $nombreCliente = trim(  ($body['nombre_cliente'] ?? ''));
        $rapido        = !empty($body['rapido']);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        try {
            $result = $rapido
                ? $this->model->obtenerDetalleRapidoPorCredito($idCredito)
                : $this->model->obtenerOCrearOperacion($idCredito, $nombreCliente, $idUsuario);

            if ($rapido && (empty($result['success']) || empty($result['detalle']))) {
                $result = $this->model->obtenerOCrearOperacion($idCredito, $nombreCliente, $idUsuario);
            }
            if (empty($result['success']) || empty($result['detalle']) || !is_array($result['detalle'])) {
                echo json_encode($result);
                return;
            }

            $detalle = $result['detalle'];

            if (!empty($detalle['evidencias']) && is_array($detalle['evidencias'])) {
                foreach ($detalle['evidencias'] as &$ev) {
                    if (!is_array($ev) || empty($ev['url'])) {
                        continue;
                    }
                    $u = str_replace('\\', '/', trim((string) $ev['url']));
                    $u = preg_replace('#^https?://uploads(?=/|$)#i', '/uploads', $u);
                    $u = preg_replace('#^/{2,}uploads(?=/|$)#i', '/uploads', $u);
                    $u = preg_replace('#^/uploads/uploads/#i', '/uploads/', $u);
                    if (function_exists('sparta_url_publica_desde_repositorio')) {
                        $u = sparta_url_publica_desde_repositorio($u);
                    }
                    $ev['url'] = $u;
                }
                unset($ev);
            }

            $historial = [];
            if (!empty($detalle['historial']) && is_array($detalle['historial'])) {
                foreach ($detalle['historial'] as $h) {
                    if (!is_array($h)) {
                        continue;
                    }
                    $historial[] = [
                        'id'            => isset($h['id']) ? (int) $h['id'] : 0,
                        'estatus_actual'=> (string) ($h['estatus_nuevo'] ?? $h['estatus_actual'] ?? ''),
                        'id_usuario'    => isset($h['id_usuario']) ? (int) $h['id_usuario'] : 0,
                        'fecha'         => (string) ($h['fecha'] ?? ''),
                    ];
                }
            }

            // Extraer campos de datos_moto guardados en adj_operacion
            $datosMoto = null;
            $camposMoto = [
                'moto_marca','moto_modelo','moto_anio','moto_color',
                'moto_no_serie','moto_no_motor','moto_placas',
                'kilometraje',
                'tiene_llave_fisica','tiene_tarjeta_de_circulacion_en_fisico','la_moto_tiene_placa_fisica',
                'llave_fisica','tarjeta_circulacion','placa_fisica',
                'log_direccion','log_ciudad',
                'log_estado','log_lugar_resguardo','log_lugar_otro','log_telefono',
                'responsable_entrega',
            ];
            $tieneDatosMoto = false;
            foreach ($camposMoto as $c) {
                if (!empty($detalle[$c])) {
                    $tieneDatosMoto = true;
                    break;
                }
            }
            if ($tieneDatosMoto) {
                $datosMoto = [];
                foreach ($camposMoto as $c) {
                    $datosMoto[$c] = $detalle[$c] ?? null;
                }
                $datosMoto['llave_fisica'] = $datosMoto['llave_fisica'] ?? ($detalle['tiene_llave_fisica'] ?? null);
                $datosMoto['tarjeta_circulacion'] = $datosMoto['tarjeta_circulacion'] ?? ($detalle['tiene_tarjeta_de_circulacion_en_fisico'] ?? null);
                $datosMoto['placa_fisica'] = $datosMoto['placa_fisica'] ?? ($detalle['la_moto_tiene_placa_fisica'] ?? null);
            }

            $detalleCompacto = [
                'id'                     => isset($detalle['id']) ? (int) $detalle['id'] : 0,
                'folio'                  => (string) ($detalle['folio'] ?? ''),
                'id_credito'             => isset($detalle['id_credito']) ? (int) $detalle['id_credito'] : $idCredito,
                'nombre_cliente'         => (string) ($detalle['nombre_cliente'] ?? $nombreCliente),
                'estatus'                => (string) ($detalle['estatus'] ?? ''),
                'saldo_capital'          => $detalle['saldo_capital'] ?? null,
                'adeudo_total'           => $detalle['adeudo_total'] ?? null,
                'id_usuario_alta'        => isset($detalle['id_usuario_alta']) ? (int) $detalle['id_usuario_alta'] : null,
                'fecha_alta'             => $detalle['fecha_alta'] ?? null,
                'fecha_actualizacion'    => $detalle['fecha_actualizacion'] ?? null,
                'fecha_llegada_almacen'  => $detalle['fecha_llegada_almacen'] ?? null,
                'recepcion_ubicacion'    => $detalle['recepcion_ubicacion'] ?? null,
                'recepcion_observaciones'=> $detalle['recepcion_observaciones'] ?? null,
                'recepcion_confirmada_at'=> $detalle['recepcion_confirmada_at'] ?? null,
                'fecha_alta_fmt'         => (string) ($detalle['fecha_alta_fmt'] ?? ''),
                'fecha_actualizacion_fmt'=> (string) ($detalle['fecha_actualizacion_fmt'] ?? ''),
                'datos_moto_fecha'       => (string) ($detalle['datos_moto_fecha'] ?? ''),
                'dias_en_pipeline'       => isset($detalle['dias_en_pipeline']) ? (int) $detalle['dias_en_pipeline'] : 0,
                'datos_moto'             => $datosMoto,
                'evidencias'             => is_array($detalle['evidencias'] ?? null) ? $detalle['evidencias'] : [],
                'observaciones'          => is_array($detalle['observaciones'] ?? null) ? $detalle['observaciones'] : [],
                'historial'              => $historial,
                'bitacora'               => is_array($detalle['bitacora'] ?? null) ? $detalle['bitacora'] : [],
                'ultimo_analista_evidencias' => is_array($detalle['ultimo_analista_evidencias'] ?? null) ? $detalle['ultimo_analista_evidencias'] : null,
                'ultimo_analista_nombre' => $detalle['ultimo_analista_nombre'] ?? null,
                'ultimo_analista_fecha'  => $detalle['ultimo_analista_fecha'] ?? null,
                'ultimo_analista_accion' => $detalle['ultimo_analista_accion'] ?? null,
                'ultimo_gestor_operacion'=> is_array($detalle['ultimo_gestor_operacion'] ?? null) ? $detalle['ultimo_gestor_operacion'] : null,
                'ultimo_gestor_nombre'   => $detalle['ultimo_gestor_nombre'] ?? null,
                'ultimo_gestor_fecha'    => $detalle['ultimo_gestor_fecha'] ?? null,
            ];

            echo json_encode(['success' => true, 'detalle' => $detalleCompacto]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function obtenerEvidenciasCreditosRapido()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids = $body['ids_credito'] ?? [];
        if (!is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'Lista de creditos invalida.']);
            return;
        }

        try {
            $detalles = $this->model->obtenerDetallesRapidosPorCreditos($ids);
            foreach ($detalles as &$wrap) {
                if (empty($wrap['detalle']['evidencias']) || !is_array($wrap['detalle']['evidencias'])) {
                    continue;
                }
                foreach ($wrap['detalle']['evidencias'] as &$ev) {
                    if (!is_array($ev) || empty($ev['url'])) {
                        continue;
                    }
                    $u = str_replace('\\', '/', trim((string) $ev['url']));
                    $u = preg_replace('#^https?://uploads(?=/|$)#i', '/uploads', $u);
                    $u = preg_replace('#^/{2,}uploads(?=/|$)#i', '/uploads', $u);
                    $u = preg_replace('#^/uploads/uploads/#i', '/uploads/', $u);
                    if (function_exists('sparta_url_publica_desde_repositorio')) {
                        $u = sparta_url_publica_desde_repositorio($u);
                    }
                    $ev['url'] = $u;
                }
                unset($ev);
            }
            unset($wrap);

            echo json_encode(['success' => true, 'detalles' => $detalles], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[MotosAdjudicadas/obtenerEvidenciasCreditosRapido] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener evidencias.']);
        }
    }

    /**
     * POST /MotosAdjudicadas/guardarDatosMoto
     * Body JSON: { "id_credito": 12345, "datos": { "moto_marca": "Honda", ... } }
     * Guarda los datos de la moto y logísticos en adj_operacion.
     */
    public function guardarDatosMoto()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);
        $datos     = $body['datos'] ?? [];

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        if (!is_array($datos) || empty($datos)) {
            echo json_encode(['success' => false, 'message' => 'No se recibieron datos.']);
            return;
        }

        $idUsuario     = (int)   ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = (string) ($_SESSION['nombre']    ?? $_SESSION['usuario']     ?? '');

        try {
            // Obtener id_operacion a partir de id_credito
            $op = $this->model->obtenerOCrearOperacion($idCredito, '', $idUsuario);
            if (empty($op['success']) || empty($op['detalle']['id'])) {
                echo json_encode(['success' => false, 'message' => $op['message'] ?? 'No se encontró la operación.']);
                return;
            }
            $idOperacion = (int) $op['detalle']['id'];

            $result = $this->model->guardarDatosMoto($idOperacion, $datos, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/consultarRepuveCredito
     * Body JSON: { "id_credito": 12345 }
     * Consulta REPUVE una sola vez por crédito y reutiliza el registro en BD.
     */
    public function consultarRepuveCredito()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        try {
            $result = $this->model->consultarRepuvePorCredito($idCredito, $idUsuario);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/obtenerDatosMotoFactura
     * Body JSON: { "id_credito": 12345 }
     * Extrae VIN, motor y color desde el documento FACTURA (si existe).
     */
    public function obtenerDatosMotoFactura()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        try {
            $result = $this->model->obtenerDatosMotoDesdeFactura($idCredito);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/ejecutarConsultaRepuve
     * Body JSON: { "id_credito": 12345, "tipo": "plate"|"vin", "valor": "..." }
     * Exige crédito con asignación activa en adjudicación.
     */
    public function ejecutarConsultaRepuve()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);
        $tipoRaw   = strtolower(trim((string) ($body['tipo'] ?? 'plate')));
        $tipo      = in_array($tipoRaw, ['plate', 'vin'], true) ? $tipoRaw : 'plate';
        $valor     = (string) ($body['valor'] ?? '');
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        try {
            $valCred = $this->model->buscarCreditoEnAdjudicacion($idCredito);
            if (empty($valCred['success'])) {
                echo json_encode($valCred, JSON_UNESCAPED_UNICODE);
                return;
            }

            $result = $this->model->consultarRepuveConCriterio($idCredito, $tipo, $valor, $idUsuario);
            $result['credito'] = [
                'id_credito'     => $idCredito,
                'nombre_cliente' => (string) ($valCred['nombre_cliente'] ?? ''),
            ];
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/obtenerResumenEvidenciasCreditos
     * Body JSON: { "ids_credito": [123, 456] }
     */
    public function obtenerResumenEvidenciasCreditos()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids  = $body['ids_credito'] ?? $body['ids'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            echo json_encode(['success' => true, 'resumen' => []]);
            return;
        }

        try {
            $resumen = $this->model->obtenerResumenEvidenciasPorCreditos($ids);
            echo json_encode(['success' => true, 'resumen' => $resumen]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET/POST /MotosAdjudicadas/obtenerMisAdjudicaciones
     * Query opcional: omitir_morosidad=1 — omite consulta a Segundómetro (respuesta más rápida; usar luego obtenerMorosidadMisCreditos).
     */
    public function obtenerMisAdjudicaciones()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idPersona = $_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? null;
        if (!$idPersona) {
            echo json_encode(['success' => false, 'message' => 'Sesión no identificada.']);
            return;
        }

        $omitirMorosidad = isset($_GET['omitir_morosidad'])
            && (string) $_GET['omitir_morosidad'] === '1';

        try {
            $pack              = $this->model->obtenerMisAdjudicaciones((int) $idPersona, !$omitirMorosidad);
            $creditos          = $pack['creditos'];
            $resumenEvidencias = $pack['resumen_evidencias'];
            echo json_encode([
                'success'            => true,
                'creditos'           => $creditos,
                'resumen_evidencias' => $resumenEvidencias,
                'morosidad_diferida' => $omitirMorosidad,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/obtenerMorosidadMisCreditos
     * Body JSON: { "ids_credito": [1, 2, 3] } — buckets desde Segundómetro (segunda fase de Mis adjudicaciones).
     */
    public function obtenerMorosidadMisCreditos()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids  = $body['ids_credito'] ?? $body['ids'] ?? [];

        if (!is_array($ids) || $ids === []) {
            echo json_encode(['success' => true, 'morosidad' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $morosidad = $this->model->obtenerMorosidadSegundometroPorCreditos(
                array_values(array_unique(array_filter(array_map('intval', $ids), static fn ($v) => $v > 0)))
            );
            echo json_encode(['success' => true, 'morosidad' => $morosidad], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    /**
     * POST /MotosAdjudicadas/guardarVeredictoEvidenciaAtn
     * Body JSON: { "id_operacion", "id_evidencia", "val_atn": 1|2, "comentario" }
     */
    public function guardarVeredictoEvidenciaAtn()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body         = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion  = (int) ($body['id_operacion'] ?? 0);
        $idEvidencia  = (int) ($body['id_evidencia'] ?? 0);
        $valAtn       = (int) ($body['val_atn'] ?? 0);
        $comentario   = (string) ($body['comentario'] ?? '');
        $idUsuario    = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        try {
            $result = $this->model->guardarVeredictoEvidenciaAtn(
                $idOperacion,
                $idEvidencia,
                $valAtn,
                $comentario,
                $idUsuario,
                $nombreUsuario
            );
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/enviarRechazosEvidenciasBulkLegacy
     * Registra el historial local de rechazos y envia una sola push agrupada.
     */
    public function enviarRechazosEvidenciasBulkLegacy()
    {
        header('Content-Type: application/json; charset=utf-8');

        $cfg = $this->pushLegacyConfig();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Servicio de notificaciones no configurado. Configure MOTOS_ADJUDICADAS_TOKEN en config_api o en el entorno.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $motivoGeneral = trim((string) ($body['motivo_general'] ?? 'Evidencias incompletas o borrosas.'));
        $evidencias = $body['evidencias'] ?? [];
        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'SISTEMA'));

        try {
            $prep = $this->model->prepararPayloadRechazoEvidenciasBulk(
                $idOperacion,
                is_array($evidencias) ? $evidencias : [],
                $idUsuario,
                $motivoGeneral
            );
            if (empty($prep['success'])) {
                echo json_encode($prep, JSON_UNESCAPED_UNICODE);
                return;
            }

            $payload = $prep['payload'] ?? [];
            $destinatarios = is_array($prep['destinatarios'] ?? null) ? $prep['destinatarios'] : [];
            if ($destinatarios === [] && is_array($payload)) {
                $destinatarios[] = [
                    'user_id_legacy' => (int) ($payload['user_id_legacy'] ?? 0),
                    'external_id' => (string) ($payload['external_id'] ?? ''),
                    'nombre' => '',
                    'origen' => 'payload',
                ];
            }
            $local = $this->model->registrarRechazosEvidenciasBulkLocal(
                is_array($payload) ? $payload : [],
                $idUsuario,
                $motivoGeneral,
                $nombreUsuario
            );
            if (empty($local['success'])) {
                echo json_encode($local, JSON_UNESCAPED_UNICODE);
                return;
            }

            $rechazos = is_array($local['rechazos'] ?? null) ? $local['rechazos'] : [];
            $slots = is_array($local['slots'] ?? null) ? $local['slots'] : [];
            $first = $rechazos[0] ?? [];
            $total = count($rechazos);
            $titulo = $total === 1 ? 'Evidencia rechazada' : 'Evidencias rechazadas';
            $mensaje = $total === 1
                ? 'Una evidencia fue rechazada. Toca para corregirla.'
                : $total . ' evidencias fueron rechazadas. Toca para corregirlas.';

            $pushBasePayload = [
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'evento' => 'evidencias_rechazadas',
                'data' => [
                    'type' => 'evidencias_rechazadas',
                    'screen' => 'MotoDetalle',
                    'tab' => 'Recoleccion',
                    'id_operacion' => (int) ($payload['id_operacion'] ?? 0),
                    'id_credito' => (int) ($payload['id_credito'] ?? 0),
                    'slots' => $slots,
                    'rechazos' => $rechazos,
                    'highlight_slot' => (string) ($first['slot'] ?? ''),
                    'highlight_evidencia_id' => (int) ($first['id_evidencia'] ?? 0),
                ],
            ];

            $url = $cfg['base_url'] . '/api/push-notifications/legacy/send';
            $resp = ['http_code' => 0, 'body' => '', 'error' => 'No se intento enviar la notificacion.'];
            $decoded = null;
            $destinatarioUsado = null;
            $erroresDestinatarios = [];
            foreach ($destinatarios as $destinatario) {
                $userIdLegacy = (int) ($destinatario['user_id_legacy'] ?? 0);
                $externalId = trim((string) ($destinatario['external_id'] ?? ''));
                if ($userIdLegacy <= 0 || $externalId === '') {
                    continue;
                }

                $pushPayload = array_merge($pushBasePayload, [
                    'user_id_legacy' => (string) $userIdLegacy,
                    'external_id' => $externalId,
                ]);
                $resp = $this->pushLegacyCurl($url, $pushPayload);
                $decoded = json_decode($resp['body'], true);
                $message = is_array($decoded)
                    ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? '')
                    : (string) ($resp['error'] ?? '');
                if ($resp['http_code'] >= 200 && $resp['http_code'] < 300) {
                    $destinatarioUsado = $destinatario;
                    break;
                }

                $erroresDestinatarios[] = [
                    'external_id' => $externalId,
                    'user_id_legacy' => $userIdLegacy,
                    'nombre' => (string) ($destinatario['nombre'] ?? ''),
                    'origen' => (string) ($destinatario['origen'] ?? ''),
                    'http_code' => $resp['http_code'],
                    'message' => $message,
                ];

                $sinDispositivo = stripos($message, 'No hay tokens activos') !== false
                    || stripos($message, 'tokens activos') !== false
                    || stripos($message, 'destinatario') !== false;
                if (!$sinDispositivo) {
                    break;
                }
            }

            if ($resp['http_code'] < 200 || $resp['http_code'] >= 300) {
                $pushMessage = is_array($decoded)
                    ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? 'Los rechazos se guardaron, pero no se pudo enviar la notificacion.')
                    : ($resp['error'] ?: 'Los rechazos se guardaron, pero no se pudo enviar la notificacion.');
                $sinTokensActivos = stripos($pushMessage, 'No hay tokens activos') !== false
                    || stripos($pushMessage, 'tokens activos') !== false
                    || stripos($pushMessage, 'destinatario') !== false;

                if ($sinTokensActivos) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Rechazos guardados. El destinatario no tiene notificaciones activas en MaxikashApp.',
                        'http_code' => $resp['http_code'],
                        'push_notificado' => false,
                        'push_warning' => true,
                        'push_message' => $pushMessage,
                        'api_response' => $decoded ?: $resp['body'],
                        'rechazos' => $rechazos,
                        'destinatarios_probados' => $erroresDestinatarios,
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                echo json_encode([
                    'success' => false,
                    'message' => $pushMessage,
                    'http_code' => $resp['http_code'],
                    'api_response' => $decoded ?: $resp['body'],
                    'rechazos' => $rechazos,
                    'destinatarios_probados' => $erroresDestinatarios,
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $pushTotalTokens = is_array($decoded) ? (int) ($decoded['total_tokens'] ?? 0) : 0;
            $pushNotificado = $pushTotalTokens > 0;

            echo json_encode(array_merge([
                'success' => true,
                'message' => $pushNotificado
                    ? 'Evidencias rechazadas correctamente.'
                    : 'Rechazos guardados. La novedad quedo en MaxikashApp, pero el destinatario no tiene push activo.',
                'http_code' => $resp['http_code'],
                'rechazos' => $rechazos,
                'destinatario' => $destinatarioUsado,
                'push_notificado' => $pushNotificado,
                'push_warning' => !$pushNotificado,
                'push_message' => is_array($decoded) ? (string) ($decoded['mensaje'] ?? $decoded['message'] ?? '') : '',
                'push_total_enviados' => is_array($decoded) ? ($decoded['push_total_enviados'] ?? $decoded['total_enviados'] ?? null) : null,
                'push_total_fallidos' => is_array($decoded) ? ($decoded['push_total_fallidos'] ?? $decoded['total_fallidos'] ?? null) : null,
            ], is_array($decoded) ? $decoded : []), JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * POST /MotosAdjudicadas/finalizarCierreValidacionEvidenciaAtn
     * Body JSON: { "id_operacion" } — al cerrar el modal: si hay rechazos en medios, pasa a Revisión Recuperaciones (no avanza a Procesando IA).
     */
    public function finalizarCierreValidacionEvidenciaAtn()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $idUsuario   = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->finalizarCierreValidacionEvidenciaAtn($idOperacion, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/enviarEvidenciasValidadasAtencion
     * Body JSON: { "id_operacion" } — único paso a Procesando IA (pestaña Aprobados).
     */
    public function enviarEvidenciasValidadasAtencion()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body            = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion     = (int) ($body['id_operacion'] ?? 0);
        $idUsuario       = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario   = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->enviarEvidenciasValidadasAtencion($idOperacion, $idUsuario, $nombreUsuario);
            if (!empty($result['success'])) {
                $cfg = $this->pushLegacyConfig();
                if ($cfg['base_url'] === '' || $cfg['api_key'] === '') {
                    $result['push_success'] = false;
                    $result['push_message'] = 'Servicio de notificaciones no configurado.';
                } else {
                    $prep = $this->model->prepararPayloadAprobacionEvidenciasAtencion($idOperacion);
                    if (empty($prep['success'])) {
                        $result['push_success'] = false;
                        $result['push_message'] = $prep['message'] ?? 'No se pudo preparar la notificacion.';
                    } else {
                        $payload = is_array($prep['payload'] ?? null) ? $prep['payload'] : [];
                        $destinatarios = is_array($prep['destinatarios'] ?? null) ? $prep['destinatarios'] : [];
                        if ($destinatarios === [] && $payload !== []) {
                            $destinatarios[] = [
                                'user_id_legacy' => (int) ($payload['user_id_legacy'] ?? 0),
                                'external_id' => (string) ($payload['external_id'] ?? ''),
                                'nombre' => '',
                                'origen' => 'payload',
                            ];
                        }

                        $pushBasePayload = [
                            'titulo' => 'Evidencias aprobadas',
                            'mensaje' => 'Sus evidencias han sido aprobadas. Toca para revisarlas.',
                            'evento' => 'evidencias_aprobadas',
                            'data' => [
                                'type' => 'evidencias_aprobadas',
                                'screen' => 'MotoDetalle',
                                'tab' => 'Recoleccion',
                                'id_operacion' => (int) ($payload['id_operacion'] ?? $idOperacion),
                                'id_credito' => (int) ($payload['id_credito'] ?? 0),
                                'approved' => true,
                                'evidence_status' => 'aprobadas',
                            ],
                        ];

                        $url = $cfg['base_url'] . '/api/push-notifications/legacy/send';
                        $resp = ['http_code' => 0, 'body' => '', 'error' => 'No se intento enviar la notificacion.'];
                        $decoded = null;
                        $destinatarioUsado = null;
                        $erroresDestinatarios = [];
                        foreach ($destinatarios as $destinatario) {
                            $userIdLegacy = (int) ($destinatario['user_id_legacy'] ?? 0);
                            $externalId = trim((string) ($destinatario['external_id'] ?? ''));
                            if ($userIdLegacy <= 0 || $externalId === '') {
                                continue;
                            }

                            $pushPayload = array_merge($pushBasePayload, [
                                'user_id_legacy' => (string) $userIdLegacy,
                                'external_id' => $externalId,
                            ]);
                            $resp = $this->pushLegacyCurl($url, $pushPayload);
                            $decoded = json_decode($resp['body'], true);
                            $message = is_array($decoded)
                                ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? '')
                                : (string) ($resp['error'] ?? '');
                            if ($resp['http_code'] >= 200 && $resp['http_code'] < 300) {
                                $destinatarioUsado = $destinatario;
                                break;
                            }

                            $erroresDestinatarios[] = [
                                'external_id' => $externalId,
                                'user_id_legacy' => $userIdLegacy,
                                'nombre' => (string) ($destinatario['nombre'] ?? ''),
                                'origen' => (string) ($destinatario['origen'] ?? ''),
                                'http_code' => $resp['http_code'],
                                'message' => $message,
                            ];

                            $sinDispositivo = stripos($message, 'No hay tokens activos') !== false
                                || stripos($message, 'tokens activos') !== false
                                || stripos($message, 'destinatario') !== false;
                            if (!$sinDispositivo) {
                                break;
                            }
                        }

                        $result['push_success'] = $resp['http_code'] >= 200 && $resp['http_code'] < 300;
                        $result['push_http_code'] = $resp['http_code'];
                        $result['push_destinatario'] = $destinatarioUsado;
                        $result['push_destinatarios_probados'] = $erroresDestinatarios;
                        $result['push_response'] = is_array($decoded) ? $decoded : $resp['body'];
                        if (!$result['push_success']) {
                            $result['push_message'] = is_array($decoded)
                                ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? 'Evidencias enviadas, pero no se pudo notificar al gestor.')
                                : ($resp['error'] ?: 'Evidencias enviadas, pero no se pudo notificar al gestor.');
                        }
                    }
                }
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * POST /MotosAdjudicadas/confirmarCierreDocumentacionEnS2
     * Body JSON: { "id_operacion": 12 } — vista 4: confirma cierre en S2 y envía la operación a Recepción (vista 5).
     */
    public function confirmarCierreDocumentacionEnS2()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body        = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        $idUsuario   = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->confirmarCierreDocumentacionEnS2($idOperacion, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/enviarRecuperacionACartera
     * Body JSON: { "id_operacion": 12, "comentarios": "..." } — Recuperación → Cartera (Cierre documentado).
     */
    public function enviarRecuperacionACartera()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body          = json_decode(file_get_contents('php://input'), true) ?? [];
        $idOperacion   = (int) ($body['id_operacion'] ?? 0);
        $comentarios   = trim((string) ($body['comentarios'] ?? ''));
        $idUsuario     = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombreUsuario = trim($_SESSION['usuario_nombre'] ?? 'SISTEMA');

        if ($idOperacion <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->enviarRecuperacionACartera($idOperacion, $comentarios, $idUsuario, $nombreUsuario);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function eliminarOperacion()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = (int) ($body['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.']);
            return;
        }

        try {
            $result = $this->model->eliminarOperacion($id);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
