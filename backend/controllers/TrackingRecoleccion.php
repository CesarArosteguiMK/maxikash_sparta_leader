<?php

namespace Controllers;

use Core\Controller;
use Models\TrackingRecoleccion as TrackingModel;
use Models\ConfigMotosAdj;

class TrackingRecoleccion extends Controller
{
    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    private function prepararVistaTracking(string $titulo = 'Tracking Recoleccion - Motos Adjudicadas'): void
    {
        self::set('titulo', 'Tracking Recolección — Motos Adjudicadas');
        self::set('google_maps_api_key_js', $this->googleMapsKey());
        self::set('tracking_dias_minimos_programacion', ConfigMotosAdj::obtenerDiasMinimosRuta());
        $puedeCancelarRutas = false;
        try {
            $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
            $puedeCancelarRutas = (new TrackingModel())->usuarioPuedeCancelarRutasTracking($idUsuario);
        } catch (\Throwable $e) {
            $puedeCancelarRutas = false;
        }
        self::set('tracking_puede_cancelar_rutas', $puedeCancelarRutas);
        self::set('titulo', $titulo);
        // Chat Operativo — pasar URL base de WebSocket al frontend (no se expone la API key)
        $trkCfg = $this->_trkChatConfig();
        if ($trkCfg['base_url'] !== '') {
            $wsBase = preg_replace('/^https:/i', 'wss:', preg_replace('/^http:/i', 'ws:', rtrim($trkCfg['base_url'], '/')));
            self::set('tracking_chat_ws_base_url', $wsBase);
            self::set('tracking_api_base_url', rtrim($trkCfg['base_url'], '/'));
        }
        return;
    }

    private function googleMapsKey(): string
    {
        $key = defined('GOOGLE_MAPS_API_KEY') ? trim((string) GOOGLE_MAPS_API_KEY) : '';
        if ($key !== '') {
            return $key;
        }

        $env = getenv('GOOGLE_MAPS_API_KEY');
        if ($env !== false && trim((string) $env) !== '') {
            return trim((string) $env);
        }

        try {
            if (function_exists('config_api_load_from_db')) {
                $cfg = config_api_load_from_db();
                $key = trim((string) ($cfg['GOOGLE_MAPS_API_KEY'] ?? ''));
            }
        } catch (\Throwable $e) {
            $key = '';
        }

        return $key;
    }

    public function index()
    {
        self::set('titulo', 'Tracking Recoleccion');
        return self::render('tracking_recoleccion');
    }

    public function rutas()
    {
        $this->prepararVistaTracking('Tracking Recoleccion - Rutas registradas');
        self::set('tracking_initial_section', 'rutas');
        return self::render('tracking_rutas');
    }

    public function planeacion()
    {
        $this->prepararVistaTracking('Tracking Recoleccion - Planeacion de rutas');
        self::set('tracking_initial_section', 'creditos');
        return self::render('tracking_planeacion');
    }

    public function creditos()
    {
        $this->prepararVistaTracking('Tracking Recoleccion - Creditos disponibles');
        self::set('tracking_initial_section', 'creditos');
        return self::render('tracking_creditos');
    }

    public function borradores()
    {
        $this->prepararVistaTracking('Tracking Recoleccion - Borradores');
        self::set('tracking_initial_section', 'borradores');
        return self::render('tracking_borradores');
    }

    public function cedisTransportistas()
    {
        $this->prepararVistaTracking('Tracking Recoleccion - CEDIS y Transportistas');
        self::set('tracking_initial_section', 'catalogos');
        return self::render('tracking_CEDIS_transportistas');
    }

    public function administracionTransportistas()
    {
        $this->prepararVistaTracking('Tracking Recoleccion - Administracion de transportistas');
        self::set('tracking_initial_section', 'operacion');
        return self::render('tracking_admin_transportistas');
    }

    // =========================================================================
    // CHAT OPERATIVO — helpers privados
    // =========================================================================

    /**
     * Carga la configuración del Chat Operativo desde la tabla config_api (o variables de entorno).
     * Claves esperadas: TRACKING_BASE_URL, TRACKING_API_KEY, TRACKING_GESTOR_USER, TRACKING_GESTOR_PASS
     */
    private function _trkChatConfig(): array
    {
        $cfg = function_exists('config_api_load_from_db') ? config_api_load_from_db() : [];
        return [
            'base_url'     => trim((string)($cfg['TRACKING_BASE_URL']     ?? getenv('TRACKING_BASE_URL')     ?: '')),
            'api_key'      => trim((string)($cfg['TRACKING_API_KEY']      ?? getenv('TRACKING_API_KEY')      ?: '')),
            'gestor_user'  => trim((string)($cfg['TRACKING_GESTOR_USER']  ?? getenv('TRACKING_GESTOR_USER')  ?: '')),
            'gestor_pass'  => trim((string)($cfg['TRACKING_GESTOR_PASS']  ?? getenv('TRACKING_GESTOR_PASS')  ?: '')),
        ];
    }

    /**
     * Realiza una petición HTTP con cURL a la API de tracking.
     * Devuelve ['http_code' => int, 'body' => string].
     */
    private function _trkChatCurl(string $url, string $method, string $body, array $headers, int $timeout = 15): array
    {
        if (!function_exists('curl_init')) {
            return ['http_code' => 0, 'body' => '', 'error' => 'cURL no disponible.'];
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => max(5, $timeout),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false, // ajustar según entorno
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($body !== '') curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $raw      = curl_exec($ch);
        $curlErr  = $raw === false ? curl_error($ch) : '';
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $httpCode, 'body' => ($raw === false ? '' : (string)$raw), 'error' => $curlErr];
    }

    private function _trkTrackingBuildUrl(string $baseUrl, string $path): string
    {
        $base = rtrim($baseUrl, '/');
        if (substr(strtolower($base), -4) === '/api' && substr($path, 0, 5) === '/api/') {
            $path = substr($path, 4);
        }
        return $base . $path;
    }

    private function _trkTrackingCurlFallback(string $baseUrl, array $paths, string $method, string $body, array $headers): array
    {
        $last = ['http_code' => 0, 'body' => '', 'error' => 'Sin rutas para consultar.'];
        foreach ($paths as $path) {
            $url = $this->_trkTrackingBuildUrl($baseUrl, $path);
            $resp = $this->_trkChatCurl($url, $method, $body, $headers);
            $resp['path'] = $path;
            $data = json_decode((string)($resp['body'] ?? ''), true);
            $isEndpointNotFound = (int)($resp['http_code'] ?? 0) === 404
                && is_array($data)
                && strtolower((string)($data['detail'] ?? '')) === 'not found';
            $last = $resp;
            if (!$isEndpointNotFound) {
                return $resp;
            }
        }
        return $last;
    }

    /**
     * Obtiene el JWT de la API de tracking y lo cachea en sesión por ~55 min.
     * Retorna '' si no hay configuración o falla el login.
     */
    private function _trkChatObtenerJwt(): string
    {
        // Devolver token cacheado si sigue vigente (margen de 5 min)
        if (!empty($_SESSION['_trk_chat_jwt'])
            && !empty($_SESSION['_trk_chat_jwt_exp'])
            && (int)$_SESSION['_trk_chat_jwt_exp'] > time() + 300
        ) {
            return (string)$_SESSION['_trk_chat_jwt'];
        }

        $cfg = $this->_trkChatConfig();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '') return '';

        $loginUrl = rtrim($cfg['base_url'], '/') . '/api/login';
        // El body de login sigue el formato estándar del backend FastAPI de tracking.
        // Si el endpoint usa un campo distinto a "username"/"password", actualiza aquí.
        $loginBody = json_encode([
            'username' => $cfg['gestor_user'],
            'password' => $cfg['gestor_pass'],
        ]);

        $resp = $this->_trkChatCurl($loginUrl, 'POST', $loginBody, [
            'Content-Type: application/json',
            'X-API-Key: ' . $cfg['api_key'],
        ]);

        if ($resp['http_code'] !== 200) return '';

        $data  = json_decode($resp['body'], true) ?: [];
        // Soporte para "access_token" (OAuth2 estándar) o "token" (custom)
        $token = $data['access_token'] ?? $data['token'] ?? '';

        if ($token !== '') {
            $_SESSION['_trk_chat_jwt']     = $token;
            $_SESSION['_trk_chat_jwt_exp'] = time() + 3300; // ~55 min
        }

        return $token;
    }

    // =========================================================================
    // CHAT OPERATIVO — endpoints proxy
    // =========================================================================

    /**
     * GET /TrackingRecoleccion/chatObtenerToken
     * Devuelve un JWT fresco (o cacheado) al frontend para la conexión WebSocket.
     * La API key NUNCA se devuelve al navegador.
     */
    public function chatObtenerToken()
    {
        $token = $this->_trkChatObtenerJwt();
        if ($token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Servicio de chat no configurado.']);
            return;
        }
        // Calcular expiración aproximada para que el frontend renueve a tiempo
        $expiryMs = (int)(($_SESSION['_trk_chat_jwt_exp'] ?? (time() + 3300)) * 1000);
        self::respuestaJSON(['success' => true, 'token' => $token, 'expiry_ms' => $expiryMs]);
    }

    /**
     * GET /TrackingRecoleccion/chatInfo?id_detalle=N
     * Proxy: GET /api/tracking/chats/{id_detalle}
     */
    public function chatInfo()
    {
        $idDetalle = (int)($_GET['id_detalle'] ?? 0);
        if ($idDetalle <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_detalle requerido.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Chat no disponible (configuración incompleta).']);
            return;
        }

        $url  = rtrim($cfg['base_url'], '/') . "/api/tracking/chats/{$idDetalle}";
        $resp = $this->_trkChatCurl($url, 'GET', '', [
            'X-API-Key: '     . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * GET /TrackingRecoleccion/chatMensajes?id_detalle=N[&limit=50][&before_id=M]
     * Proxy: GET /api/tracking/chats/{id_detalle}/mensajes
     */
    public function chatMensajes()
    {
        $idDetalle = (int)($_GET['id_detalle'] ?? 0);
        if ($idDetalle <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_detalle requerido.']);
            return;
        }

        $limit    = min(200, max(1, (int)($_GET['limit']     ?? 50)));
        $beforeId = (int)($_GET['before_id'] ?? 0);

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Chat no disponible.']);
            return;
        }

        $qs  = "?limit={$limit}" . ($beforeId > 0 ? "&before_id={$beforeId}" : '');
        $url = rtrim($cfg['base_url'], '/') . "/api/tracking/chats/{$idDetalle}/mensajes{$qs}";
        $resp = $this->_trkChatCurl($url, 'GET', '', [
            'X-API-Key: '     . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * GET /TrackingRecoleccion/trackingEstadoRuta?id_ruta=X
     * Proxy: GET /api/tracking/rutas/{id_ruta}/estado
     */
    public function trackingEstadoRuta()
    {
        $idRuta = (int)($_GET['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_ruta requerido.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $url  = rtrim($cfg['base_url'], '/') . "/api/tracking/rutas/{$idRuta}/estado";
        $resp = $this->_trkChatCurl($url, 'GET', '', [
            'X-API-Key: '     . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * GET /TrackingRecoleccion/trackingLiveConfig
     * Devuelve datos necesarios para abrir el WebSocket live desde navegador.
     */
    public function trackingLiveConfig()
    {
        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking live no disponible.']);
            return;
        }

        $wsBase = preg_replace('/^https:/i', 'wss:', preg_replace('/^http:/i', 'ws:', rtrim($cfg['base_url'], '/')));
        $expiryMs = (int)(($_SESSION['_trk_chat_jwt_exp'] ?? (time() + 3300)) * 1000);
        self::respuestaJSON([
            'success'   => true,
            'ws_base'   => $wsBase,
            'token'     => $token,
            'api_key'   => $cfg['api_key'],
            'expiry_ms' => $expiryMs,
        ]);
    }

    /**
     * GET /TrackingRecoleccion/trackingUbicacionActual?id_ruta=X
     * Proxy: GET /api/tracking/rutas/{id_ruta}/ubicacion/actual
     */
    public function trackingUbicacionActual()
    {
        $idRuta = (int)($_GET['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_ruta requerido.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $url  = rtrim($cfg['base_url'], '/') . "/api/tracking/rutas/{$idRuta}/ubicacion/actual";
        $resp = $this->_trkChatCurl($url, 'GET', '', [
            'X-API-Key: '     . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * GET /TrackingRecoleccion/trackingUbicacionHistorial?id_ruta=X[&limit=300][&since=ISO]
     * Proxy: GET /api/tracking/rutas/{id_ruta}/ubicacion/historial
     */
    public function trackingUbicacionHistorial()
    {
        $idRuta = (int)($_GET['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_ruta requerido.']);
            return;
        }

        $limit = min(1000, max(1, (int)($_GET['limit'] ?? 300)));
        $since = trim((string)($_GET['since'] ?? ''));

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $qs = '?limit=' . $limit . ($since !== '' ? '&since=' . rawurlencode($since) : '');
        $url  = rtrim($cfg['base_url'], '/') . "/api/tracking/rutas/{$idRuta}/ubicacion/historial{$qs}";
        $resp = $this->_trkChatCurl($url, 'GET', '', [
            'X-API-Key: '     . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * POST /TrackingRecoleccion/chatEnviarMensaje
     * Body JSON: { id_detalle, mensaje, tipo_mensaje?, latitud?, longitud?, metadata? }
     * Proxy: POST /api/tracking/chats/{id_detalle}/mensajes
     */
    public function chatEnviarMensaje()
    {
        $raw  = (string)file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        $idDetalle = (int)($data['id_detalle'] ?? 0);
        if ($idDetalle <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_detalle requerido.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Chat no disponible.']);
            return;
        }

        // Construir body limpio para el tracking backend
        $payload = json_encode([
            'mensaje'          => (string)($data['mensaje']      ?? ''),
            'tipo_mensaje'     => (string)($data['tipo_mensaje'] ?? 'texto'),
            'tipo_actor'       => 'gestor',
            'nombre_remitente' => trim((string)($_SESSION['usuario_nombre'] ?? 'Gestor')),
            'latitud'          => $data['latitud']  ?? null,
            'longitud'         => $data['longitud'] ?? null,
            'metadata'         => $data['metadata'] ?? null,
        ]);

        $url  = rtrim($cfg['base_url'], '/') . "/api/tracking/chats/{$idDetalle}/mensajes";
        $resp = $this->_trkChatCurl($url, 'POST', $payload, [
            'Content-Type: application/json',
            'X-API-Key: '     . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * POST /TrackingRecoleccion/chatSubirArchivo
     * Multipart: id_detalle, archivo, mensaje?, latitud?, longitud?
     * Proxy: POST /api/tracking/chats/{id_detalle}/archivos
     */
    public function chatSubirArchivo()
    {
        $idDetalle = (int)($_POST['id_detalle'] ?? 0);
        if ($idDetalle <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_detalle requerido.']);
            return;
        }
        if (empty($_FILES['archivo']) || !is_uploaded_file($_FILES['archivo']['tmp_name'])) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Archivo requerido.']);
            return;
        }
        if ((int)($_FILES['archivo']['size'] ?? 0) > 100 * 1024 * 1024) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El archivo supera el límite de 100 MB.', 'codigo_http' => 413]);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Chat no disponible.']);
            return;
        }

        $url = rtrim($cfg['base_url'], '/') . "/api/tracking/chats/{$idDetalle}/archivos";
        if (!function_exists('curl_init') || !class_exists('\CURLFile')) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Carga de archivos no disponible en este servidor.']);
            return;
        }

        $file = $_FILES['archivo'];
        $post = [
            'archivo' => new \CURLFile(
                $file['tmp_name'],
                (string)($file['type'] ?? 'application/octet-stream'),
                (string)($file['name'] ?? 'archivo')
            ),
        ];
        foreach (['mensaje', 'latitud', 'longitud'] as $k) {
            if (isset($_POST[$k]) && trim((string)$_POST[$k]) !== '') {
                $post[$k] = (string)$_POST[$k];
            }
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_HTTPHEADER     => [
                'X-API-Key: ' . $cfg['api_key'],
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw      = curl_exec($ch);
        $curlErr  = $raw === false ? curl_error($ch) : '';
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->_trkChatRelayResponse([
            'http_code' => $httpCode,
            'body'      => ($raw === false ? '' : (string)$raw),
            'error'     => $curlErr,
        ]);
    }

    /**
     * Retransmite la respuesta del tracking backend al cliente web, normalizando
     * los códigos de error para que el JS pueda interpretarlos.
     */
    private function _trkChatRelayResponse(array $resp): void
    {
        $httpCode = $resp['http_code'];
        $data     = json_decode($resp['body'], true);
        http_response_code(200);

        if ($httpCode === 0 || $httpCode >= 500) {
            $backendMsg = '';
            if (is_array($data)) {
                $rawMsg = $data['mensaje'] ?? $data['message'] ?? $data['detail'] ?? $data['error'] ?? '';
                $backendMsg = is_scalar($rawMsg)
                    ? trim((string)$rawMsg)
                    : trim(json_encode($rawMsg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
            if ($backendMsg === '' && trim((string)($resp['error'] ?? '')) !== '') {
                $backendMsg = trim((string)$resp['error']);
            }
            if ($backendMsg === '' && trim((string)($resp['body'] ?? '')) !== '') {
                $backendMsg = mb_substr(trim((string)$resp['body']), 0, 500);
            }

            self::respuestaJSON([
                'success' => false,
                'mensaje' => $backendMsg !== ''
                    ? 'Servicio de tracking no disponible: ' . $backendMsg
                    : 'Servicio de tracking no disponible. Intenta de nuevo en unos minutos.',
                'codigo_http' => $httpCode,
                'servicio_no_disponible' => true,
                'error' => $resp['error'] ?? ($data['error'] ?? null),
                'detalle_tracking' => $backendMsg !== '' ? $backendMsg : null,
            ]);
        }

        // Si el body no es JSON válido, construir respuesta genérica
        if (!is_array($data)) {
            $data = [
                'success' => false,
                'mensaje' => match ($httpCode) {
                    401 => 'Sesión expirada. Recarga la página.',
                    403 => 'Sin acceso a este chat.',
                    404 => 'Tarea no encontrada.',
                    409 => 'Chat bloqueado o cerrado.',
                    500 => 'Error del servidor. Intenta más tarde.',
                    0   => 'Sin conexión con el servidor de chat.',
                    default => "Error inesperado (HTTP {$httpCode}).",
                },
            ];
        }

        // Inyectar código HTTP para que el JS pueda actuar (ej: 409 → deshabilitar input)
        $data['codigo_http'] = $httpCode;

        // Si el backend invalida el JWT, limpiar caché de sesión
        if ($httpCode === 401) {
            unset($_SESSION['_trk_chat_jwt'], $_SESSION['_trk_chat_jwt_exp']);
        }

        self::respuestaJSON($data);
    }

    // =========================================================================
    // API — CRÉDITOS PASO 2
    // =========================================================================

    /**
     * GET /TrackingRecoleccion/obtenerCreditosPaso2[?estado=X&municipio=Y]
     */
    public function obtenerCreditosPaso2()
    {
        $estado    = trim((string) ($_GET['estado']    ?? $_POST['estado']    ?? ''));
        $municipio = trim((string) ($_GET['municipio'] ?? $_POST['municipio'] ?? ''));

        try {
            $model   = new TrackingModel();
            $creditos = $model->obtenerCreditosPaso2(
                $estado    !== '' ? $estado    : null,
                $municipio !== '' ? $municipio : null
            );
            self::respuestaJSON(self::respuesta(true, null, $creditos));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener créditos.', null, $e->getMessage()));
        }
    }

    // =========================================================================
    // API — CATÁLOGOS
    // =========================================================================

    /**
     * GET /TrackingRecoleccion/obtenerEstados
     */
    public function obtenerEstados()
    {
        try {
            $model  = new TrackingModel();
            $estados = $model->obtenerEstados();
            self::respuestaJSON(self::respuesta(true, null, $estados));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener estados.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerMunicipios?estado=X
     */
    public function obtenerMunicipios()
    {
        $estado = trim((string) ($_GET['estado'] ?? $_POST['estado'] ?? ''));
        if ($estado === '') {
            self::respuestaJSON(self::respuesta(false, 'Parámetro estado requerido.'));
            return;
        }
        try {
            $model      = new TrackingModel();
            $municipios = $model->obtenerMunicipiosPorEstado($estado);
            self::respuestaJSON(self::respuesta(true, null, $municipios));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener municipios.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerUsuariosRecoleccion
     */
    public function obtenerUsuariosRecoleccion()
    {
        try {
            $model    = new TrackingModel();
            $usuarios = $model->obtenerUsuariosRecoleccion();
            self::respuestaJSON(self::respuesta(true, null, $usuarios));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener usuarios.', null, $e->getMessage()));
        }
    }

    private function leerBodyTracking(): array
    {
        $rawBody = file_get_contents('php://input');
        $data = [];
        if ($rawBody !== '' && $rawBody !== false) {
            $data = json_decode($rawBody, true) ?: [];
        }
        return !empty($data) ? $data : $_POST;
    }

    /**
     * POST /TrackingRecoleccion/validarNombreRuta
     * Body JSON: { nombre_ruta, id_ruta? }
     */
    public function validarNombreRuta()
    {
        $data = $this->leerBodyTracking();
        if (empty($data)) {
            $data = $_GET;
        }
        $nombre = trim((string) ($data['nombre_ruta'] ?? $data['nombre'] ?? ''));
        $idRuta = (int) ($data['id_ruta'] ?? 0);

        try {
            $model = new TrackingModel();
            self::respuestaJSON($model->validarNombreRutaDisponible($nombre, $idRuta));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al validar nombre de ruta.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerCatalogoAgenciasTransportistas
     */
    public function obtenerCatalogoAgenciasTransportistas()
    {
        try {
            $model = new TrackingModel();
            self::respuestaJSON(self::respuesta(true, null, $model->obtenerCatalogoAgenciasTransportistas()));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener CEDIS y transportistas.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerOperacionTransportistas
     */
    public function obtenerOperacionTransportistas()
    {
        try {
            $model = new TrackingModel();
            self::respuestaJSON(self::respuesta(true, null, $model->obtenerOperacionTransportistas()));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener operacion de transportistas.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/guardarAgenciaTracking
     */
    public function guardarAgenciaTracking()
    {
        try {
            $model = new TrackingModel();
            $result = $model->guardarAgenciaTracking($this->leerBodyTracking());
            self::respuestaJSON($result);
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al guardar CEDIS.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/cambiarEstadoAgenciaTracking
     */
    public function cambiarEstadoAgenciaTracking()
    {
        try {
            $data = $this->leerBodyTracking();
            $model = new TrackingModel();
            self::respuestaJSON($model->cambiarEstadoAgenciaTracking(
                (int) ($data['id_agencia'] ?? 0),
                (int) (($data['activo'] ?? 1) ? 1 : 0)
            ));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al actualizar CEDIS.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/guardarTransportistaTracking
     */
    public function guardarTransportistaTracking()
    {
        try {
            $model = new TrackingModel();
            $result = $model->guardarTransportistaTracking($this->leerBodyTracking());
            self::respuestaJSON($result);
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al guardar transportista.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/cambiarEstadoTransportistaTracking
     */
    public function cambiarEstadoTransportistaTracking()
    {
        try {
            $data = $this->leerBodyTracking();
            $model = new TrackingModel();
            self::respuestaJSON($model->cambiarEstadoTransportistaTracking(
                (int) ($data['id_transportista'] ?? 0),
                (int) (($data['activo'] ?? 1) ? 1 : 0)
            ));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al actualizar transportista.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/guardarUnidadTransportistaTracking
     */
    public function guardarUnidadTransportistaTracking()
    {
        try {
            $model = new TrackingModel();
            $result = $model->guardarUnidadTransportistaTracking($this->leerBodyTracking());
            self::respuestaJSON($result);
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al guardar unidad.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerCedisTracking
     * Proxy seguro: GET /api/tracking/cedis
     */
    public function obtenerCedisTracking()
    {
        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();

        if ($cfg['base_url'] !== '' && $cfg['api_key'] !== '' && $token !== '') {
            $url  = rtrim($cfg['base_url'], '/') . '/api/tracking/cedis';
            $resp = $this->_trkChatCurl($url, 'GET', '', [
                'X-API-Key: ' . $cfg['api_key'],
                'Authorization: Bearer ' . $token,
            ]);

            $httpCode = (int)($resp['http_code'] ?? 0);
            $data     = json_decode((string)($resp['body'] ?? ''), true);

            if ($httpCode === 401) {
                unset($_SESSION['_trk_chat_jwt'], $_SESSION['_trk_chat_jwt_exp']);
            }

            if ($httpCode > 0 && $httpCode < 500 && is_array($data)) {
                $cedis = $data['cedis']
                    ?? $data['datos']['cedis']
                    ?? $data['datos']
                    ?? $data['data']['cedis']
                    ?? $data['data']
                    ?? [];

                if (is_array($cedis)) {
                    $cedis = array_values(array_filter($cedis, static function ($item) {
                        if (!is_array($item)) return false;
                        return !isset($item['activo']) || (int)$item['activo'] === 1;
                    }));

                    self::respuestaJSON(self::respuesta(true, null, [
                        'cedis'      => $cedis,
                        'origen'     => 'api',
                        'codigo_http'=> $httpCode,
                    ]));
                    return;
                }
            }
        }

        try {
            $model = new TrackingModel();
            $catalogo = $model->obtenerCatalogoAgenciasTransportistas();
            $cedis = array_values(array_filter($catalogo['agencias'] ?? [], static function ($item) {
                if (!is_array($item)) return false;
                return !isset($item['activo']) || (int)$item['activo'] === 1;
            }));
            self::respuestaJSON(self::respuesta(true, 'CEDIS cargados desde respaldo local.', [
                'cedis'  => $cedis,
                'origen' => 'local_fallback',
            ]));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener CEDIS.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/trackingCedisDestino?id_ruta=N
     * Proxy seguro: GET /api/tracking/rutas/{id_ruta}/cedis-destino
     */
    public function trackingCedisDestino()
    {
        $idRuta = (int)($_GET['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_ruta requerido.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $paths = [
            "/api/tracking/rutas/{$idRuta}/cedis-destino",
            "/api/tracking/rutas/{$idRuta}/cedis_destino",
            "/api/tracking/cedis-destino/rutas/{$idRuta}",
            "/api/tracking/cedis_destino/rutas/{$idRuta}",
            "/api/tracking/cedis-destino/{$idRuta}",
            "/api/tracking/cedis_destino/{$idRuta}",
        ];
        $resp = $this->_trkTrackingCurlFallback($cfg['base_url'], $paths, 'GET', '', [
            'X-API-Key: ' . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * GET /TrackingRecoleccion/trackingCreditosSobreRuta?id_ruta=N
     * Proxy seguro: GET /api/tracking/rutas/{id_ruta}/creditos-sobre-ruta
     */
    public function trackingCreditosSobreRuta()
    {
        $idRuta = (int)($_GET['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_ruta requerido.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $params = [];
        $allowed = [
            'radio_km',
            'limit',
            'usar_ubicacion_actual',
            'incluir_detour',
            'estado',
            'municipio',
            'solo_con_coordenadas',
        ];
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $_GET)) {
                continue;
            }
            $value = trim((string)$_GET[$key]);
            if ($value !== '') {
                $params[$key] = $value;
            }
        }

        $qs = $params ? ('?' . http_build_query($params)) : '';
        $url = $this->_trkTrackingBuildUrl($cfg['base_url'], "/api/tracking/rutas/{$idRuta}/creditos-sobre-ruta{$qs}");
        $resp = $this->_trkChatCurl($url, 'GET', '', [
            'X-API-Key: ' . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * PATCH /TrackingRecoleccion/trackingCambiarCedisDestino
     * Body JSON: { id_ruta, id_cedis_destino, motivo }
     * Proxy seguro: PATCH /api/tracking/rutas/{id_ruta}/cedis-destino
     */
    public function trackingCambiarCedisDestino()
    {
        $raw  = (string)file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        $idRuta = (int)($data['id_ruta'] ?? 0);
        $idCedisDestino = (int)($data['id_cedis_destino'] ?? 0);
        $motivo = trim((string)($data['motivo'] ?? ''));

        if ($idRuta <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_ruta requerido.']);
            return;
        }
        if ($idCedisDestino <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Selecciona un CEDIS destino.']);
            return;
        }
        if ($motivo === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El motivo es obligatorio.']);
            return;
        }
        if (mb_strlen($motivo) > 200) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'El motivo no puede exceder 200 caracteres.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $body = json_encode([
            'id_cedis_destino' => $idCedisDestino,
            'motivo' => $motivo,
        ]);
        $paths = [
            "/api/tracking/rutas/{$idRuta}/cedis-destino",
            "/api/tracking/rutas/{$idRuta}/cedis_destino",
            "/api/tracking/cedis-destino/rutas/{$idRuta}",
            "/api/tracking/cedis_destino/rutas/{$idRuta}",
            "/api/tracking/cedis-destino/{$idRuta}",
            "/api/tracking/cedis_destino/{$idRuta}",
        ];
        $resp = $this->_trkTrackingCurlFallback($cfg['base_url'], $paths, 'PATCH', $body, [
            'Content-Type: application/json',
            'X-API-Key: ' . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * GET /TrackingRecoleccion/trackingOtpActivo?id_detalle=N
     * Proxy: GET /api/tracking/detalles/{id_detalle}/otp/activo
     */
    public function trackingOtpActivo()
    {
        $idDetalle = (int)($_GET['id_detalle'] ?? 0);
        if ($idDetalle <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_detalle requerido.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $url  = $this->_trkTrackingBuildUrl($cfg['base_url'], "/api/tracking/detalles/{$idDetalle}/otp/activo");
        $resp = $this->_trkChatCurl($url, 'GET', '', [
            'X-API-Key: ' . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * POST /TrackingRecoleccion/trackingGenerarOtp
     * Body JSON: { id_detalle, canal?, telefono_destino? }
     * Proxy: POST /api/tracking/detalles/{id_detalle}/otp/generar
     */
    public function trackingGenerarOtp()
    {
        $raw  = (string)file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];
        $idDetalle = (int)($data['id_detalle'] ?? 0);
        if ($idDetalle <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_detalle requerido.']);
            return;
        }

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $body = json_encode([
            'canal' => trim((string)($data['canal'] ?? 'manual')) ?: 'manual',
            'telefono_destino' => $data['telefono_destino'] ?? null,
        ]);
        $url  = $this->_trkTrackingBuildUrl($cfg['base_url'], "/api/tracking/detalles/{$idDetalle}/otp/generar");
        $resp = $this->_trkChatCurl($url, 'POST', $body, [
            'Content-Type: application/json',
            'X-API-Key: ' . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ]);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * GET /TrackingRecoleccion/obtenerTransportistasTracking?tipo=interno|externo&id_agencia=N
     */
    public function obtenerTransportistasTracking()
    {
        $tipo      = strtolower(trim((string) ($_GET['tipo'] ?? $_POST['tipo'] ?? '')));
        $idAgencia = (int) ($_GET['id_agencia'] ?? $_POST['id_agencia'] ?? 0);
        try {
            $model = new TrackingModel();
            self::respuestaJSON(self::respuesta(
                true,
                null,
                $model->obtenerTransportistasTracking(
                    in_array($tipo, ['interno', 'externo'], true) ? $tipo : null,
                    $idAgencia > 0 ? $idAgencia : null
                )
            ));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener transportistas.', null, $e->getMessage()));
        }
    }

    // =========================================================================
    // API — RUTAS
    // =========================================================================

    /**
     * POST /TrackingRecoleccion/guardarRuta
     * Body JSON: { nombre_ruta, estado, municipio, fecha_programada, modo,
     *              tipo_transportista, id_transportista, id_agencia_tracking, id_cedis_destino, creditos:[], id_ruta? }
     */
    public function guardarRuta()
    {
        $idUsuario  = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $rawBody    = file_get_contents('php://input');
        $data       = [];

        if ($rawBody !== '' && $rawBody !== false) {
            $data = json_decode($rawBody, true) ?: [];
        }
        // Fallback a POST fields
        if (empty($data)) {
            $data = $_POST;
            if (isset($_POST['creditos']) && is_string($_POST['creditos'])) {
                $data['creditos'] = json_decode($_POST['creditos'], true) ?: [];
            }
        }

        try {
            $model  = new TrackingModel();
            $result = $model->guardarRuta($data, $idUsuario);
            self::respuestaJSON($result);
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error inesperado al guardar la ruta.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/agregarCreditoRuta
     * Body JSON: { id_ruta, id_credito }
     */
    public function agregarCreditoRuta()
    {
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $data = $this->leerBodyTracking();
        $idRuta = (int) ($data['id_ruta'] ?? 0);
        $idCredito = (int) ($data['id_credito'] ?? 0);

        try {
            $model = new TrackingModel();
            self::respuestaJSON($model->agregarCreditoRutaExistente($idRuta, $idCredito, $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al agregar credito a la ruta.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/obtenerRutas
     * Body: { estado?, municipio? }
     */
    public function obtenerRutas()
    {
        $estado    = trim((string) ($_POST['estado']    ?? $_GET['estado']    ?? ''));
        $municipio = trim((string) ($_POST['municipio'] ?? $_GET['municipio'] ?? ''));

        try {
            $model = new TrackingModel();
            $rutas = $model->obtenerRutas(
                $estado    !== '' ? $estado    : null,
                $municipio !== '' ? $municipio : null
            );
            self::respuestaJSON(self::respuesta(true, null, $rutas));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener rutas.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerBorradores
     */
    public function obtenerBorradores()
    {
        try {
            $model = new TrackingModel();
            self::respuestaJSON(self::respuesta(true, null, $model->obtenerBorradores()));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener borradores.', null, $e->getMessage()));
        }
    }

    /**
     * GET /TrackingRecoleccion/obtenerDetalleRuta?id_ruta=N
     */
    public function obtenerDetalleRuta()
    {
        $idRuta = (int) ($_GET['id_ruta'] ?? $_POST['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de ruta requerido.'));
            return;
        }
        try {
            $model  = new TrackingModel();
            $detalle = $model->obtenerDetalleRuta($idRuta);
            if ($detalle === null) {
                self::respuestaJSON(self::respuesta(false, 'Ruta no encontrada.'));
                return;
            }
            self::respuestaJSON(self::respuesta(true, null, $detalle));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener detalle de ruta.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/calcularTiemposPlaneacionRuta?id_ruta=N
     * Proxy seguro: POST /api/tracking/rutas/{id_ruta}/planeacion/calcular-tiempos
     */
    public function calcularTiemposPlaneacionRuta()
    {
        $idRuta = (int)($_GET['id_ruta'] ?? $_POST['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'id_ruta requerido.']);
            return;
        }

        $raw  = (string)file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        $cfg   = $this->_trkChatConfig();
        $token = $this->_trkChatObtenerJwt();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || $token === '') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tracking no disponible.']);
            return;
        }

        $body = json_encode([
            'origen' => trim((string)($data['origen'] ?? 'cedis')) ?: 'cedis',
            'usar_gps_transportista' => !empty($data['usar_gps_transportista']),
            'fecha_salida' => trim((string)($data['fecha_salida'] ?? '')),
            'hora_salida' => trim((string)($data['hora_salida'] ?? '')),
            'inicio_jornada' => trim((string)($data['inicio_jornada'] ?? '10:00')),
            'fin_jornada' => trim((string)($data['fin_jornada'] ?? '19:00')),
            'min_por_parada' => max(1, (int)($data['min_por_parada'] ?? 45)),
            'traslado_entre_estados' => max(0, (int)($data['traslado_entre_estados'] ?? 1)),
            'persistir' => !empty($data['persistir']),
        ]);

        $url = $this->_trkTrackingBuildUrl(
            $cfg['base_url'],
            "/api/tracking/rutas/{$idRuta}/planeacion/calcular-tiempos"
        );
        $resp = $this->_trkChatCurl($url, 'POST', $body, [
            'Content-Type: application/json',
            'X-API-Key: ' . $cfg['api_key'],
            'Authorization: Bearer ' . $token,
        ], 90);

        $this->_trkChatRelayResponse($resp);
    }

    /**
     * GET /TrackingRecoleccion/obtenerPlaneacionRuta?id_ruta=N
     */
    public function obtenerPlaneacionRuta()
    {
        $idRuta = (int) ($_GET['id_ruta'] ?? $_POST['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de ruta requerido.'));
            return;
        }
        try {
            $model = new TrackingModel();
            self::respuestaJSON($model->obtenerPlaneacionRuta($idRuta));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener planeacion de ruta.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/guardarPlaneacionRuta
     * Body JSON: { id_ruta, items:[{id_detalle, fecha_recoleccion, orden_dia}], motivo? }
     */
    public function guardarPlaneacionRuta()
    {
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $data = $this->leerBodyTracking();
        try {
            $model = new TrackingModel();
            self::respuestaJSON($model->guardarPlaneacionRuta($data, $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al guardar planeacion de ruta.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/actualizarCoordenadasRuta
     * Body JSON: { id_ruta, creditos:[{id_detalle?, id_credito?, latitud, longitud, direccion?, estado?, municipio?}] }
     */
    public function actualizarCoordenadasRuta()
    {
        $data = $this->leerBodyTracking();
        try {
            $model = new TrackingModel();
            self::respuestaJSON($model->actualizarCoordenadasRuta($data));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al actualizar coordenadas de ruta.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/actualizarPlaneacionPunto
     * Body JSON: { id_ruta, id_detalle, fecha_recoleccion, orden_dia?, tipo_evento?, motivo }
     */
    public function actualizarPlaneacionPunto()
    {
        $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $data = $this->leerBodyTracking();
        try {
            $model = new TrackingModel();
            self::respuestaJSON($model->actualizarPlaneacionPunto($data, $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al actualizar planeacion del punto.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/eliminarBorrador
     * Body JSON: { id_ruta }
     */
    public function eliminarBorrador()
    {
        $data = $this->leerBodyTracking();
        if (empty($data)) {
            $data = $_GET;
        }
        $idRuta = (int) ($data['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            self::respuestaJSON(self::respuesta(false, 'ID de ruta requerido.'));
            return;
        }

        try {
            $model = new TrackingModel();
            self::respuestaJSON($model->eliminarBorrador($idRuta));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al eliminar borrador.', null, $e->getMessage()));
        }
    }

    private function trkPdfEsc($valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function trkPdfVal($valor, string $fallback = 'No disponible'): string
    {
        $txt = trim((string) ($valor ?? ''));
        return $this->trkPdfEsc($txt !== '' ? $txt : $fallback);
    }

    private function trkPdfHoraRuta(array $ruta): string
    {
        $hora = trim((string) ($ruta['act_hora_1'] ?? ''));
        if ($hora === '') {
            $hora = trim((string) ($ruta['hora_inicial'] ?? ''));
        }
        return $hora !== '' ? substr($hora, 0, 5) : 'No disponible';
    }

    private function trkPdfRutaHtml(array $ruta): string
    {
        $detalles = is_array($ruta['detalle'] ?? null) ? $ruta['detalle'] : [];
        $fechaGeneracion = date('d/m/Y H:i');
        $creadoPor = $ruta['creado_por_nombre'] ?? ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? '');
        $estatus = strtoupper((string) ($ruta['estatus_ruta'] ?? ''));
        $rows = '';

        foreach ($detalles as $i => $det) {
            $horasEstimadas = trim((string) ($det['fecha_eta'] ?? ''));
            $hi = trim((string) ($det['hora_eta_ini'] ?? ''));
            $hf = trim((string) ($det['hora_eta_fin'] ?? ''));
            if ($hi !== '' || $hf !== '') {
                $horasEstimadas .= ($horasEstimadas !== '' ? ' ' : '')
                    . trim('Inicio: ' . substr($hi, 0, 5) . ' / Llegada: ' . substr($hf, 0, 5));
            }
            $ubicacion = implode(' / ', array_filter([
                trim((string) ($det['estado'] ?? '')),
                trim((string) ($det['municipio'] ?? '')),
                trim((string) ($det['direccion'] ?? '')),
            ]));
            $rows .= '<tr>'
                . '<td class="num">' . $this->trkPdfEsc($det['orden_ruta'] ?? ($i + 1)) . '</td>'
                . '<td>' . $this->trkPdfVal($det['id_credito'] ?? '') . '</td>'
                . '<td>' . $this->trkPdfVal($det['nombre_cliente'] ?? '') . '</td>'
                . '<td>' . $this->trkPdfVal($det['modelo'] ?? '') . '</td>'
                . '<td>' . $this->trkPdfVal($det['bin'] ?? '') . '</td>'
                . '<td>' . $this->trkPdfVal($ubicacion) . '</td>'
                . '<td>' . $this->trkPdfVal($horasEstimadas) . '</td>'
                . '<td>' . $this->trkPdfVal($det['estatus_confirmacion_gestor'] ?? '') . '</td>'
                . '</tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="8" class="empty">Sin creditos registrados</td></tr>';
        }

        $cedisUbicacion = implode(' / ', array_filter([
            trim((string) ($ruta['cedis_destino_municipio'] ?? '')),
            trim((string) ($ruta['cedis_destino_estado'] ?? '')),
            trim((string) ($ruta['cedis_destino_codigo_postal'] ?? '')),
        ]));

        return '<!doctype html><html><head><meta charset="UTF-8"><style>
            body{font-family:dejavusans,sans-serif;color:#1f2937;font-size:10.5px;}
            .top{border-bottom:3px solid #0d9488;padding-bottom:10px;margin-bottom:14px;}
            h1{font-size:20px;margin:0;color:#17213a;letter-spacing:.4px;}
            h2{font-size:13px;margin:0 0 8px;color:#0f766e;text-transform:uppercase;}
            .muted{color:#64748b;}
            .folio{color:#0d9488;font-weight:bold;}
            .grid{width:100%;border-collapse:collapse;margin-bottom:12px;}
            .grid td{width:50%;vertical-align:top;padding:6px 8px;border:1px solid #dbe7ef;}
            .label{font-size:8.5px;color:#64748b;text-transform:uppercase;font-weight:bold;margin-bottom:2px;}
            .value{font-size:10.5px;color:#111827;}
            .pill{display:inline-block;background:#e6fffb;color:#0f766e;border:1px solid #7dd3c7;border-radius:10px;padding:2px 8px;font-weight:bold;}
            table.creditos{width:100%;border-collapse:collapse;margin-top:8px;}
            table.creditos th{background:#17213a;color:#fff;text-align:left;padding:6px;font-size:8.5px;text-transform:uppercase;}
            table.creditos td{border-bottom:1px solid #dbe7ef;padding:6px;vertical-align:top;}
            table.creditos tr:nth-child(even) td{background:#f8fbfd;}
            .num{text-align:center;font-weight:bold;color:#0d9488;}
            .empty{text-align:center;color:#64748b;padding:16px;}
            .footer{margin-top:14px;font-size:8.5px;color:#64748b;text-align:right;}
        </style></head><body>
            <div class="top">
                <h1>EVIDENCIA DE RUTA DE RECOLECCION</h1>
                <div class="muted">Generado: ' . $this->trkPdfEsc($fechaGeneracion) . '</div>
            </div>

            <table class="grid">
                <tr>
                    <td>
                        <div class="label">Ruta</div>
                        <div class="value"><span class="folio">#' . $this->trkPdfEsc($ruta['id_ruta'] ?? '') . '</span> ' . $this->trkPdfVal($ruta['nombre_ruta'] ?? '') . '</div>
                    </td>
                    <td>
                        <div class="label">Estatus</div>
                        <div class="value"><span class="pill">' . $this->trkPdfEsc($estatus ?: 'NO DISPONIBLE') . '</span></div>
                    </td>
                </tr>
                <tr>
                    <td><div class="label">Fecha de salida</div><div class="value">' . $this->trkPdfVal($ruta['fecha_programada_fmt'] ?? $ruta['fecha_programada'] ?? '') . '</div></td>
                    <td><div class="label">Hora de salida</div><div class="value">' . $this->trkPdfEsc($this->trkPdfHoraRuta($ruta)) . '</div></td>
                </tr>
                <tr>
                    <td><div class="label">Registrado por</div><div class="value">' . $this->trkPdfVal($creadoPor) . '</div></td>
                    <td><div class="label">Creacion / ultima actualizacion</div><div class="value">' . $this->trkPdfVal($ruta['fecha_creacion_fmt'] ?? '') . ' / ' . $this->trkPdfVal($ruta['fecha_actualizacion_fmt'] ?? '') . '</div></td>
                </tr>
            </table>

            <h2>Transportista</h2>
            <table class="grid">
                <tr>
                    <td><div class="label">Nombre</div><div class="value">' . $this->trkPdfVal($ruta['nombre_transportista'] ?? '') . '</div></td>
                    <td><div class="label">Tipo / empresa</div><div class="value">' . $this->trkPdfVal($ruta['tipo_transportista'] ?? '') . ' / ' . $this->trkPdfVal($ruta['transportista_empresa'] ?? '') . '</div></td>
                </tr>
                <tr>
                    <td><div class="label">CURP/RFC</div><div class="value">' . $this->trkPdfVal($ruta['transportista_curp_rfc'] ?? '') . '</div></td>
                    <td><div class="label">Puesto</div><div class="value">' . $this->trkPdfVal($ruta['transportista_puesto'] ?? '') . '</div></td>
                </tr>
                <tr>
                    <td><div class="label">Telefono</div><div class="value">' . $this->trkPdfVal($ruta['transportista_telefono'] ?? '') . '</div></td>
                    <td><div class="label">Email</div><div class="value">' . $this->trkPdfVal($ruta['transportista_email'] ?? '') . '</div></td>
                </tr>
            </table>

            <h2>CEDIS destino</h2>
            <table class="grid">
                <tr>
                    <td><div class="label">CEDIS</div><div class="value">' . $this->trkPdfVal($ruta['cedis_destino_nombre'] ?? '') . '</div></td>
                    <td><div class="label">Recibe</div><div class="value">' . $this->trkPdfVal($ruta['cedis_destino_encargado'] ?? '') . '</div></td>
                </tr>
                <tr>
                    <td><div class="label">Ubicacion</div><div class="value">' . $this->trkPdfVal($cedisUbicacion) . '</div></td>
                    <td><div class="label">Contacto</div><div class="value">' . $this->trkPdfVal($ruta['cedis_destino_telefono'] ?? '') . ' / ' . $this->trkPdfVal($ruta['cedis_destino_email'] ?? '') . '</div></td>
                </tr>
                <tr>
                    <td><div class="label">Direccion</div><div class="value">' . $this->trkPdfVal($ruta['cedis_destino_direccion'] ?? '') . '</div></td>
                    <td><div class="label">Horario</div><div class="value">' . $this->trkPdfVal($ruta['cedis_destino_horario'] ?? '') . '</div></td>
                </tr>
            </table>

            <h2>Creditos a recolectar</h2>
            <table class="creditos">
                <thead>
                    <tr>
                        <th>#</th><th>Credito</th><th>Cliente</th><th>Modelo</th>
                        <th>VIN</th><th>Ubicacion</th><th>Horas estimadas</th><th>Confirmacion</th>
                    </tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>
            <div class="footer">Sparta Ledger - Tracking Recoleccion</div>
        </body></html>';
    }

    /**
     * GET /TrackingRecoleccion/pdfEvidenciaRuta?id_ruta=N
     */
    public function pdfEvidenciaRuta()
    {
        $idRuta = (int) ($_GET['id_ruta'] ?? $_POST['id_ruta'] ?? 0);
        if ($idRuta <= 0) {
            http_response_code(400);
            echo 'ID de ruta requerido.';
            return;
        }

        try {
            $model = new TrackingModel();
            $ruta = $model->obtenerDetalleRuta($idRuta);
            if ($ruta === null) {
                http_response_code(404);
                echo 'Ruta no encontrada.';
                return;
            }
            if (function_exists('sparta_require_composer_autoload')) {
                sparta_require_composer_autoload();
            }
            if (!class_exists('\\Mpdf\\Mpdf')) {
                throw new \RuntimeException('mPDF no esta disponible.');
            }

            $mpdf = new \Mpdf\Mpdf([
                'format' => 'Letter',
                'tempDir' => sys_get_temp_dir(),
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);
            $mpdf->SetTitle('Evidencia ruta ' . (int) $idRuta);
            $mpdf->WriteHTML($this->trkPdfRutaHtml($ruta));
            $filename = 'EvidenciaRuta_' . date('d.m.Y_h.i_A') . '_No.' . (int) $idRuta . '.pdf';
            $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'No se pudo generar el PDF de evidencia.';
        }
    }

    /**
     * POST /TrackingRecoleccion/actualizarConfirmacionGestor
     * Body: { id_ruta, id_credito, estatus_confirmacion_gestor }
     */
    public function actualizarConfirmacionGestor()
    {
        $rawBody = file_get_contents('php://input');
        $data    = [];
        if ($rawBody !== '' && $rawBody !== false) {
            $data = json_decode($rawBody, true) ?: [];
        }
        if (empty($data)) {
            $data = $_POST;
        }
        $idRuta    = (int) ($data['id_ruta']    ?? 0);
        $idCredito = (int) ($data['id_credito'] ?? 0);
        $estatus   = trim((string) ($data['estatus_confirmacion_gestor'] ?? ''));

        if ($idRuta <= 0 || $idCredito <= 0 || $estatus === '') {
            self::respuestaJSON(self::respuesta(false, 'Parámetros incompletos.'));
            return;
        }
        try {
            $model  = new TrackingModel();
            $result = $model->actualizarConfirmacionGestor($idRuta, $idCredito, $estatus);
            self::respuestaJSON($result);
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al actualizar confirmación.', null, $e->getMessage()));
        }
    }

    /**
     * POST /TrackingRecoleccion/cancelarRuta
     * Body: { id_ruta, motivo_cancelacion }
     */
    public function cancelarRuta()
    {
        $rawBody = file_get_contents('php://input');
        $data    = [];
        if ($rawBody !== '' && $rawBody !== false) {
            $data = json_decode($rawBody, true) ?: [];
        }
        if (empty($data)) {
            $data = $_POST;
        }

        $idRuta = (int) ($data['id_ruta'] ?? 0);
        $motivo = trim((string) ($data['motivo_cancelacion'] ?? ''));
        if ($idRuta <= 0 || $motivo === '') {
            self::respuestaJSON(self::respuesta(false, 'Ruta y motivo de cancelación son obligatorios.'));
            return;
        }
        if (mb_strlen($motivo, 'UTF-8') > 200) {
            self::respuestaJSON(self::respuesta(false, 'El motivo de cancelación no puede exceder 200 caracteres.'));
            return;
        }

        try {
            $idUsuario = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
            $model     = new TrackingModel();
            $puedeCancelar = $model->usuarioPuedeCancelarRutasTracking($idUsuario);
            if (!$puedeCancelar) {
                self::respuestaJSON(self::respuesta(false, 'No tienes permiso para cancelar rutas registradas.'));
                return;
            }
            self::respuestaJSON($model->cancelarRuta($idRuta, $motivo, $idUsuario, true));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al cancelar la ruta.', null, $e->getMessage()));
        }
    }
}
