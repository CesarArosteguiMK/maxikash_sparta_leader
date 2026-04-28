<?php

namespace Controllers;

use Core\Controller;
use Models\MotosAdjudicadas as MotosAdjudicadasDAO;

class MotosAdjudicadas extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new MotosAdjudicadasDAO();
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

    // =========================================================================
    // API — PIPELINE
    // =========================================================================

    /**
     * GET /MotosAdjudicadas/obtenerOperaciones
     * Devuelve todas las tarjetas del kanban como JSON.
     */
    public function obtenerOperaciones()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $ops = $this->model->obtenerPipeline();
            echo json_encode(['success' => true, 'operaciones' => $ops]);
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
     * GET /MotosAdjudicadas/misAdjudicaciones
     */
    public function misAdjudicaciones()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Mis Adjudicaciones ' . $emp);
        return self::render('mis_adjudicaciones');
    }

    /**
     * POST /MotosAdjudicadas/obtenerEvidenciasCredito
     * Body JSON: { "id_credito": 12345, "nombre_cliente": "Juan Pérez" }
     * Obtiene (o crea si no existe) la operación del pipeline asociada al crédito
     * y devuelve sus evidencias y observaciones para el modal de Mis Adjudicaciones.
     */
    public function obtenerEvidenciasCredito()
    {
        header('Content-Type: application/json; charset=utf-8');

        $body          = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito     = (int)  ($body['id_credito']     ?? 0);
        $nombreCliente = trim(  ($body['nombre_cliente'] ?? ''));

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        $idUsuario = (int) ($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);

        try {
            $result = $this->model->obtenerOCrearOperacion($idCredito, $nombreCliente, $idUsuario);
            if (!empty($result['success']) && !empty($result['detalle']['evidencias']) && is_array($result['detalle']['evidencias'])) {
                foreach ($result['detalle']['evidencias'] as &$ev) {
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
            echo json_encode($result);
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
     */
    public function obtenerMisAdjudicaciones()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idPersona = $_SESSION['usuario_id'] ?? null;
        if (!$idPersona) {
            echo json_encode(['success' => false, 'message' => 'Sesión no identificada.']);
            return;
        }

        try {
            $creditos = $this->model->obtenerMisAdjudicaciones((int) $idPersona);
            echo json_encode(['success' => true, 'creditos' => $creditos]);
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
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /MotosAdjudicadas/confirmarCierreDocumentacionEnS2
     * Body JSON: { "id_operacion": 12 } — vista 4 Cartera confirma alta del cierre en S2.
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
