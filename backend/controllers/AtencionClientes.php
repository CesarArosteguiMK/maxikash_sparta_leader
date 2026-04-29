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

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    public function consulta(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '1.- Retenciones · Atención a clientes ' . $emp);
        $this->render('atencion_clientes_consulta');
    }

    /**
     * GET /AtencionClientes/evidencias
     * 2.- Evidencias (mismo controlador que 1.- Retenciones).
     */
    public function evidencias(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '2.- Evidencias · Atención a clientes ' . $emp);
        $this->render('atencion_clientes_evidencias');
    }

    /**
     * GET /AtencionClientes/recuperacion
     * 3.- Recuperación (seguimiento por etapa de pipeline).
     */
    public function recuperacion(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '3.- Recuperación · Atención a clientes ' . $emp);
        $this->render('atencion_clientes_recuperacion');
    }

    /**
     * GET /AtencionClientes/cierreDocumentacion
     * 4.- Cierre Documentación.
     */
    public function cierreDocumentacion(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '4.- Cierre documentación · Atención a clientes ' . $emp);
        $this->render('atencion_clientes_cierre_documentacion');
    }

    /**
     * GET /AtencionClientes/recepcion
     * 5.- Recepción.
     */
    public function recepcion(): void
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        $this->set('titulo', '5.- Recepción · Atención a clientes ' . $emp);
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
    // API: 2.- EVIDENCIAS (listas por estatus; mismo modelo)
    // =========================================================================

    public function obtenerRecibidos(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $datos = $this->model->obtenerRecibidos();
            echo json_encode(['success' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
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
            $datos = $this->model->obtenerOperacionesDictamenPorEstatusPipeline('Recepción');
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

        $dictamen = $this->model->obtenerDictamen($idOperacion);

        if (!$dictamen) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Dictamen no encontrado.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode(['success' => true, 'dictamen' => $dictamen], JSON_UNESCAPED_UNICODE);
    }
}
