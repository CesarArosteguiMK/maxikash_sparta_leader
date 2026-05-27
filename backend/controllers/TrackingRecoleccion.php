<?php

namespace Controllers;

use Core\Controller;
use Models\TrackingRecoleccion as TrackingModel;

class TrackingRecoleccion extends Controller
{
    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    public function index()
    {
        self::set('titulo', 'Tracking Recolección — Motos Adjudicadas');
        if (defined('GOOGLE_MAPS_API_KEY')) {
            self::set('google_maps_api_key_js', GOOGLE_MAPS_API_KEY);
        }
        // Chat Operativo — pasar URL base de WebSocket al frontend (no se expone la API key)
        $trkCfg = $this->_trkChatConfig();
        if ($trkCfg['base_url'] !== '') {
            $wsBase = preg_replace('/^https:/i', 'wss:', preg_replace('/^http:/i', 'ws:', rtrim($trkCfg['base_url'], '/')));
            self::set('tracking_chat_ws_base_url', $wsBase);
            self::set('tracking_api_base_url', rtrim($trkCfg['base_url'], '/'));
        }
        return self::render('tracking_recoleccion');
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
    private function _trkChatCurl(string $url, string $method, string $body, array $headers): array
    {
        if (!function_exists('curl_init')) {
            return ['http_code' => 0, 'body' => '', 'error' => 'cURL no disponible.'];
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
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
            self::respuestaJSON(['success' => false, 'mensaje' => 'El archivo supera el lÃ­mite de 100 MB.', 'codigo_http' => 413]);
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
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Servicio de tracking no disponible. Intenta de nuevo en unos minutos.',
                'codigo_http' => $httpCode,
                'servicio_no_disponible' => true,
                'error' => $resp['error'] ?? null,
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

    /**
     * GET /TrackingRecoleccion/obtenerCatalogoAgenciasTransportistas
     */
    public function obtenerCatalogoAgenciasTransportistas()
    {
        try {
            $model = new TrackingModel();
            self::respuestaJSON(self::respuesta(true, null, $model->obtenerCatalogoAgenciasTransportistas()));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al obtener agencias y transportistas.', null, $e->getMessage()));
        }
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
     *              tipo_transportista, id_transportista, id_agencia_tracking, creditos:[], id_ruta? }
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
            self::respuestaJSON($model->cancelarRuta($idRuta, $motivo, $idUsuario));
        } catch (\Throwable $e) {
            self::respuestaJSON(self::respuesta(false, 'Error al cancelar la ruta.', null, $e->getMessage()));
        }
    }
}
