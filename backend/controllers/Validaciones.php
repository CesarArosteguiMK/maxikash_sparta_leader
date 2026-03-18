<?php

namespace Controllers;

use Core\Controller;
use Core\TicketsPanelModuloHelper;
use Models\FormularioValidacionPregunta as PreguntaDAO;

/**
 * Panel administración de tickets de Validaciones (URL y script propios).
 */
class Validaciones extends Controller
{
    public function paneladmin()
    {
        TicketsPanelModuloHelper::renderModuloPanel($this, 'validaciones');
    }

    /**
     * GET lista de preguntas para el panel Formularios (predefinidas + personalizadas del usuario).
     */
    public function getPreguntasFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => []]);
            return;
        }
        try {
            $datos = PreguntaDAO::listarParaPanel($personaId);
            echo json_encode(['success' => true, 'mensaje' => 'OK', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al listar.', 'datos' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST guardar o actualizar una pregunta (body JSON).
     */
    public function guardarPreguntaFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        if (!is_array($datos)) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
            return;
        }
        $r = PreguntaDAO::guardar($datos, $personaId);
        echo json_encode($r);
    }

    /**
     * POST marcar/desmarcar pregunta (body JSON: id, activa).
     */
    public function togglePreguntaFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $id = (int) ($datos['id'] ?? 0);
        $activa = isset($datos['activa']) ? (int) $datos['activa'] : 0;
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de pregunta inválido.']);
            return;
        }
        $r = PreguntaDAO::toggleActiva($id, $activa, $personaId);
        echo json_encode($r);
    }

    /**
     * POST eliminar pregunta personalizada (body JSON: id).
     */
    public function eliminarPreguntaFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $id = (int) ($datos['id'] ?? 0);
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de pregunta inválido.']);
            return;
        }
        $r = PreguntaDAO::eliminar($id, $personaId);
        echo json_encode($r);
    }
}
