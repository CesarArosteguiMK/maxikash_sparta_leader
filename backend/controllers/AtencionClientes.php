<?php
namespace Controllers;

use Core\Controller;
use Models\AtencionClientes as AtencionClientesModel;

class AtencionClientes extends Controller
{
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
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
        $admin = $usuarioId === 1 || in_array(1, $modulos, true);

        return [
            'cancelar' => $admin || in_array(AtencionClientesModel::MODULO_MA_CANCELAR_VISTO_BUENO, $modulos, true),
            'blacklist' => $admin || in_array(AtencionClientesModel::MODULO_MA_ENVIAR_BLACKLIST, $modulos, true),
            'ver' => $admin || in_array(AtencionClientesModel::MODULO_MA_VER_BLACKLIST, $modulos, true),
            'liberar' => $admin || in_array(AtencionClientesModel::MODULO_MA_LIBERAR_BLACKLIST, $modulos, true),
        ];
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

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    public function consulta(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', 'Retenciones · Atención a clientes ' . $emp);
        $this->render('atencion_clientes_consulta');
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

            $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
            $resultado = $this->model->cancelarVistoBuenoOperacion(
                (int) ($body['id_operacion'] ?? 0),
                $tipo,
                (string) ($body['motivo'] ?? ''),
                (string) ($body['comentario'] ?? ''),
                $idUsuario,
                $this->nombreUsuarioSesion()
            );
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
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para liberar BlackList.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $body = json_decode(file_get_contents('php://input'), true);
            if (!is_array($body)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cuerpo de solicitud invalido.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
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

        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id'] ?? 0);
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
