<?php
namespace Controllers;

use Core\Controller;
use Models\AtencionClientes as AtencionClientesModel;
use Models\SolicitudAdjudicacion as SolicitudAdjudicacionModel;

class AtencionClientes extends Controller
{
    private const MODULO_ATC_SOLICITUDES = 205;

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new AtencionClientesModel();
    }

    private function permisosEvidenciasBlacklist(): array
    {
        $this->model->asegurarPermisosBlacklist();
        $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        $usuarioId = $this->usuarioSesionId();
        $admin = $usuarioId === 1 || in_array(1, $modulos, true);

        return [
            'cancelar' => $admin || in_array(AtencionClientesModel::MODULO_MA_CANCELAR_VISTO_BUENO, $modulos, true) || in_array(3037, $modulos, true),
            'blacklist' => $admin || in_array(AtencionClientesModel::MODULO_MA_ENVIAR_BLACKLIST, $modulos, true) || in_array(3038, $modulos, true),
            'ver' => $admin || in_array(AtencionClientesModel::MODULO_MA_VER_BLACKLIST, $modulos, true) || in_array(3039, $modulos, true),
            'liberar' => $admin || in_array(AtencionClientesModel::MODULO_MA_LIBERAR_BLACKLIST, $modulos, true) || in_array(3040, $modulos, true),
        ];
    }

    private function usuarioSesionId(): int
    {
        return (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
    }

    private function nombreUsuarioSesion(): string
    {
        foreach (['nombre_usuario', 'nombre', 'usuario', 'email'] as $key) {
            $valor = trim((string) ($_SESSION[$key] ?? ''));
            if ($valor !== '') {
                return $valor;
            }
        }
        return 'SISTEMA';
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
            return ['http_code' => 0, 'body' => '', 'error' => 'cURL no esta disponible en este servidor.'];
        }

        $cfg = $this->pushLegacyConfig();
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
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
            'error' => $err ?: '',
        ];
    }

    private function notificarCancelacionEvidenciasLegacy(int $idOperacion, string $tipoCancelacion, string $motivo, string $comentario): array
    {
        $cfg = $this->pushLegacyConfig();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '') {
            return ['success' => false, 'message' => 'Servicio de notificaciones no configurado.'];
        }

        $prep = $this->model->prepararPayloadCancelacionEvidenciasLegacy($idOperacion, $tipoCancelacion, $motivo, $comentario);
        if (empty($prep['success'])) {
            return ['success' => false, 'message' => (string) ($prep['message'] ?? 'No se pudo preparar la notificacion.')];
        }

        $payloadBase = is_array($prep['payload'] ?? null) ? $prep['payload'] : [];
        $destinatarios = is_array($prep['destinatarios'] ?? null) ? $prep['destinatarios'] : [];
        if ($destinatarios === [] && $payloadBase !== []) {
            $destinatarios[] = [
                'user_id_legacy' => (int) ($payloadBase['user_id_legacy'] ?? 0),
                'external_id' => (string) ($payloadBase['external_id'] ?? ''),
                'nombre' => '',
                'origen' => 'payload',
            ];
        }

        $esBlacklist = $tipoCancelacion === 'blacklist';
        $pushBasePayload = [
            'titulo' => $esBlacklist ? 'Moto adjudicada bloqueada' : 'Visto Bueno denegado',
            'mensaje' => 'No se tiene Visto Bueno para adjudicar la Moto. Si tienes cualquier duda, contacta a tu lider.',
            'evento' => $esBlacklist ? 'moto_adjudicada_blacklist' : 'visto_bueno_denegado',
            'data' => [
                'type' => $esBlacklist ? 'moto_adjudicada_blacklist' : 'visto_bueno_denegado',
                'screen' => 'MotoDetalle',
                'tab' => 'Recoleccion',
                'id_operacion' => (int) ($payloadBase['id_operacion'] ?? $idOperacion),
                'id_credito' => (int) ($payloadBase['id_credito'] ?? 0),
                'estatus' => (string) ($payloadBase['estatus'] ?? ''),
                'motivo' => (string) ($payloadBase['motivo'] ?? $motivo),
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

        $ok = $resp['http_code'] >= 200 && $resp['http_code'] < 300;
        return [
            'success' => $ok,
            'http_code' => $resp['http_code'],
            'message' => $ok
                ? 'Notificacion enviada al gestor.'
                : (is_array($decoded)
                    ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? 'No se pudo enviar la notificacion.')
                    : ($resp['error'] ?: 'No se pudo enviar la notificacion.')),
            'destinatario' => $destinatarioUsado,
            'destinatarios_probados' => $erroresDestinatarios,
            'api_response' => is_array($decoded) ? $decoded : $resp['body'],
        ];
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    public function consulta(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', 'ATC · Retenciones ' . $emp);
        header('Location: /AtencionClientes/atc?tab=retenciones', true, 302);
        exit;
    }

    /** Centro unificado de Atencion a Clientes. */
    public function atc(): void
    {
        $tab = strtolower(trim((string) ($_GET['tab'] ?? 'retenciones')));
        if (!in_array($tab, ['retenciones', 'solicitud'], true)) {
            $tab = 'retenciones';
        }

        if ($tab === 'solicitud') {
            $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
            if (!in_array(self::MODULO_ATC_SOLICITUDES, $modulos, true)) {
                http_response_code(403);
                echo '<div class="alert alert-danger m-4">No tienes permiso para operar solicitudes de ATC.</div>';
                return;
            }
        }

        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $solicitudes = new SolicitudAdjudicacionModel();
        $this->set('titulo', 'ATC - Atencion a Clientes ' . $emp);
        $this->set('atc_pestana_inicial', $tab);
        $this->set('solicitudes_tablas_disponibles', $solicitudes->tablasDisponibles());
        $this->render('atencion_clientes_atc');
    }

    /**
     * GET /AtencionClientes/evidencias
     * 1.- Evidencias (mismo controlador que Retenciones).
     */
    public function evidencias(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '1.- Evidencias · Atención a clientes ' . $emp);
        $this->set('aev_permisos_blacklist', $this->permisosEvidenciasBlacklist());
        $this->render('atencion_clientes_evidencias');
    }

    /**
     * GET /AtencionClientes/recuperacion
     * 2.- Recuperación (seguimiento por etapa de pipeline).
     */
    public function recuperacion(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '2.- Recuperación · Atención a clientes ' . $emp);
        $this->render('atencion_clientes_recuperacion');
    }

    /**
     * GET /AtencionClientes/cierreDocumentacion
     * 3.-Cartera.
     */
    public function cierreDocumentacion(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '3.- Cierre documentación · Atención a clientes ' . $emp);
        $this->render('atencion_clientes_cierre_documentacion');
    }

    /**
     * GET /AtencionClientes/recepcion
     * 4.- Recepción.
     */
    public function recepcion(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '4.- Recepción · Atención a clientes ' . $emp);
        $this->render('atencion_clientes_recepcion');
    }

    // =========================================================================
    // API: ENTRANTES
    // =========================================================================

    public function obtenerEntrantes(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerEntrantes();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    // =========================================================================
    // API: 1.- EVIDENCIAS (listas por estatus; mismo modelo)
    // =========================================================================

    public function obtenerRecibidos(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            // Sincroniza dictamenes 13 de MaxikashApp antes de listar Evidencias.
            // La bandeja fuerza la revision para no dejar envios recientes esperando el intervalo.
            $datos = $this->model->obtenerRecibidos(true);
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[AtencionClientes/obtenerRecibidos] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerAprobadosEvidencias(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerEvidenciasAprobadas();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerCorreccionesEvidencias(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerEvidenciasCorrecciones();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /AtencionClientes/obtenerConteosEvidencias
     * Conteos para badges de Bandeja / Aprobados / Correcciones sin cargar cada lista completa.
     */
    public function obtenerConteosEvidencias(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $conteos = $this->model->obtenerConteosPestanasEvidencias();
            echo json_encode(['success' => true, 'conteos' => $conteos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los conteos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    /** GET /AtencionClientes/obtenerConteosRecuperacion — badges pestañas vista 3 */
    public function obtenerBlacklistEvidencias(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $permisos = $this->permisosEvidenciasBlacklist();
            if (empty($permisos['ver'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para consultar BlackList.'], JSON_UNESCAPED_UNICODE);
                return;
            }
            $datos = $this->model->obtenerEvidenciasBlacklist();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[AtencionClientes/obtenerBlacklistEvidencias] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener BlackList.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cancelarVistoBuenoEvidencias(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $body = json_decode(file_get_contents('php://input'), true);
            if (!is_array($body)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cuerpo de solicitud invalido.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $tipo = trim((string) ($body['tipo_cancelacion'] ?? ''));
            $permisos = $this->permisosEvidenciasBlacklist();
            if ($tipo === 'blacklist' && empty($permisos['blacklist'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para enviar a BlackList.'], JSON_UNESCAPED_UNICODE);
                return;
            }
            if ($tipo !== 'blacklist' && empty($permisos['cancelar'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para denegar Visto Bueno.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $idUsuario = $this->usuarioSesionId();
            $resultado = $this->model->cancelarVistoBuenoOperacion(
                (int) ($body['id_operacion'] ?? 0),
                $tipo,
                (string) ($body['motivo'] ?? ''),
                (string) ($body['comentario'] ?? ''),
                $idUsuario,
                $this->nombreUsuarioSesion()
            );
            if (!empty($resultado['success'])) {
                try {
                    $push = $this->notificarCancelacionEvidenciasLegacy(
                        (int) ($body['id_operacion'] ?? 0),
                        $tipo,
                        (string) ($body['motivo'] ?? ''),
                        (string) ($body['comentario'] ?? '')
                    );
                    $resultado['push_success'] = (bool) ($push['success'] ?? false);
                    $resultado['push_message'] = (string) ($push['message'] ?? '');
                    $resultado['push_http_code'] = $push['http_code'] ?? null;
                    $resultado['push_destinatario'] = $push['destinatario'] ?? null;
                    $resultado['push_destinatarios_probados'] = $push['destinatarios_probados'] ?? [];
                } catch (\Throwable $pushError) {
                    error_log('[AtencionClientes/cancelarVistoBuenoEvidencias/push] ' . $pushError->getMessage());
                    $resultado['push_success'] = false;
                    $resultado['push_message'] = 'La operacion se cancelo, pero no se pudo enviar la notificacion push.';
                    $resultado['push_http_code'] = 0;
                    $resultado['push_destinatario'] = null;
                    $resultado['push_destinatarios_probados'] = [];
                }
            }
            if (empty($resultado['success'])) {
                http_response_code(422);
            }
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[AtencionClientes/cancelarVistoBuenoEvidencias] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo cancelar la operacion.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function liberarBlacklistEvidencias(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $permisos = $this->permisosEvidenciasBlacklist();
            if (empty($permisos['liberar'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para reactivar operaciones canceladas o en BlackList.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $body = json_decode(file_get_contents('php://input'), true);
            if (!is_array($body)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cuerpo de solicitud invalido.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $idUsuario = $this->usuarioSesionId();
            $resultado = $this->model->liberarBlacklist(
                (int) ($body['blacklist_id'] ?? 0),
                (string) ($body['motivo'] ?? ''),
                $idUsuario,
                $this->nombreUsuarioSesion()
            );
            if (empty($resultado['success'])) {
                http_response_code(422);
            }
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[AtencionClientes/liberarBlacklistEvidencias] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo liberar BlackList.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerConteosRecuperacion(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $conteos = $this->model->obtenerConteosPestanasRecuperacion();
            echo json_encode(['success' => true, 'conteos' => $conteos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los conteos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    /** GET /AtencionClientes/obtenerConteosCierreDocumentacion — badges vista 4 */
    public function obtenerConteosCierreDocumentacion(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $conteos = $this->model->obtenerConteosPestanasCierreDocumentacion();
            echo json_encode(['success' => true, 'conteos' => $conteos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los conteos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    /** GET /AtencionClientes/obtenerConteosRecepcion — badges vista 5 */
    public function obtenerConteosRecepcion(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $conteos = $this->model->obtenerConteosPestanasRecepcion();
            echo json_encode(['success' => true, 'conteos' => $conteos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los conteos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    // =========================================================================
    // API: 3.- RECUPERACIÓN (listas por estatus de pipeline)
    // =========================================================================

    public function obtenerRecuperacionCierreDocumentado(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerRecuperacionCierreDocumentado();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerRecuperacionRecepcion(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerRecuperacionRecepcion();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerRecuperacionEnTransito(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerRecuperacionEnTransito();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerDictaminadosRecuperacion(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerDictaminadosRecuperacionLista();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerDictaminadosCierreDocumentacion(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerDictaminadosCierreDocumentacionLista();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtenerDictaminadosRecepcion(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerOperacionesDictamenPorEstatusPipeline('Recepción', true);
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    // =========================================================================
    // API: PENDIENTES
    // =========================================================================

    public function obtenerPendientes(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerPendientes();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    // =========================================================================
    // API: DICTAMINADOS
    // =========================================================================

    public function obtenerDictaminados(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerDictaminados();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos.'], JSON_UNESCAPED_UNICODE);
        }
    }

    // =========================================================================
    // API: REGISTRAR DICTAMEN
    // =========================================================================

    public function dictaminar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true);

        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cuerpo de solicitud inválido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $idOperacion = (int) ($body['id_operacion'] ?? 0);
        if ($idOperacion <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de operación inválido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $idUsuario = $this->usuarioSesionId();
        $resultado = $this->model->registrarDictamen($idOperacion, $body, $idUsuario);

        if (!$resultado['success']) {
            http_response_code(422);
        }

        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }

    // =========================================================================
    // API: OBTENER DICTAMEN (para modal de resumen)
    // =========================================================================

    public function obtenerDictamen(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $idOperacion = (int) ($_GET['id'] ?? 0);

        if ($idOperacion <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $historialLlamadas = $this->model->obtenerHistorialDictamenesLlamadaRetenciones($idOperacion);
        $dictamen           = $this->model->obtenerUltimoDictamenLlamadaRetenciones($idOperacion);
        if (!$dictamen) {
            $dictamen = $this->model->obtenerDictamen($idOperacion);
        }

        if (!$dictamen) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Dictamen no encontrado.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'success'             => true,
            'dictamen'            => $dictamen,
            'historial_llamadas'  => $historialLlamadas,
        ], JSON_UNESCAPED_UNICODE);
    }
}
