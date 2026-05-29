<?php

namespace Controllers;

use Core\Controller;
use Models\Adjudicacion as AdjudicacionDAO;
use Models\MotosAdjudicadas as MotosAdjudicadasDAO;

class MotosAdjudicadas extends Controller
{
    private const MODULO_REEMPLAZAR_EVIDENCIA_GESTOR = 79;

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new MotosAdjudicadasDAO();
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
     * GET /MotosAdjudicadas/monitoreoAdjudicaciones
     */
    public function monitoreoAdjudicaciones()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Monitoreo - Motos Adjudicadas ' . $emp);
        return self::render('motos_adjudicadas_monitoreo');
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
                echo json_encode([
                    'success' => false,
                    'message' => is_array($decoded)
                        ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? 'Los rechazos se guardaron, pero no se pudo enviar la notificacion.')
                        : ($resp['error'] ?: 'Los rechazos se guardaron, pero no se pudo enviar la notificacion.'),
                    'http_code' => $resp['http_code'],
                    'api_response' => $decoded ?: $resp['body'],
                    'rechazos' => $rechazos,
                    'destinatarios_probados' => $erroresDestinatarios,
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode(array_merge([
                'success' => true,
                'message' => 'Evidencias rechazadas correctamente.',
                'http_code' => $resp['http_code'],
                'rechazos' => $rechazos,
                'destinatario' => $destinatarioUsado,
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
