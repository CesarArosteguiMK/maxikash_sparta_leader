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
     * GET /MotosAdjudicadas/obtenerDetalle/{id}
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
