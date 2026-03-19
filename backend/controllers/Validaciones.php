<?php

namespace Controllers;

use Core\Controller;
use Core\TicketsPanelModuloHelper;
use Models\FormularioValidacionPregunta as PreguntaDAO;
use Models\FormularioValidacion as FormularioDAO;

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
     * Pantalla para agregar/editar preguntas de un formulario (id en URL).
     */
    public function formulario($id)
    {
        $idFormulario = (int) $id;
        $esModal = isset($_GET['modal']) && (int) $_GET['modal'] === 1;
        if ($idFormulario < 1) {
            header('Location: /validaciones/paneladmin');
            exit;
        }
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            header('Location: /');
            exit;
        }
        $formulario = FormularioDAO::obtenerPorId($idFormulario);
        if (!$formulario) {
            header('Location: /validaciones/paneladmin');
            exit;
        }
        $nombre = $formulario['nombre'] ?? 'Formulario';
        $descripcion = $formulario['descripcion'] ?? '';
        $this->set('idFormulario', $idFormulario);
        $this->set('nombreFormulario', $nombre);
        $this->set('descripcionFormulario', $descripcion);
        $this->set('mostrarFormularios', true);
        $this->set('titulo', 'Form Builder: ' . $nombre);
        $builderJsVer = @filemtime(dirname(RAIZ) . '/public/assets/js/form_builder_validacion.js') ?: time();
        if ($esModal) {
            $this->set('formBuilderEmbed', true);
            $this->set('formBuilderJsVer', $builderJsVer);
            $this->render('validaciones_formulario', true);
            return;
        }
        $this->set(
            'script',
            '<script>window.FORM_BUILDER_FORMULARIO_ID=' . $idFormulario . ';window.FORM_BUILDER_TITULO=' . json_encode($nombre, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) . ';window.FORM_BUILDER_DESCRIPCION=' . json_encode($descripcion, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) . ';</script>'
            . '<script src="/assets/js/form_builder_validacion.js?v=' . $builderJsVer . '"></script>'
        );
        $this->render('validaciones_formulario');
    }

    /**
     * GET lista de formularios (para el modal "Elegir formulario").
     */
    public function getFormularios()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => []]);
            return;
        }
        try {
            $datos = FormularioDAO::listar($personaId);
            echo json_encode(['success' => true, 'mensaje' => 'OK', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al listar.', 'datos' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST crear formulario (body JSON: nombre).
     */
    public function guardarFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = is_string($raw) ? json_decode($raw, true) : [];
        $nombre = isset($datos['nombre']) ? trim((string) $datos['nombre']) : '';
        if ($nombre === '') {
            echo json_encode(['success' => false, 'mensaje' => 'El nombre es obligatorio.']);
            return;
        }
        $r = FormularioDAO::crear($nombre, $personaId);
        echo json_encode($r);
    }

    /**
     * POST actualizar formulario (body JSON: id, nombre, descripcion).
     */
    public function actualizarFormulario()
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
        $nombre = isset($datos['nombre']) ? trim((string) $datos['nombre']) : '';
        $descripcion = isset($datos['descripcion']) ? trim((string) $datos['descripcion']) : '';
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $r = FormularioDAO::actualizar($id, $nombre, $descripcion, $personaId);
        echo json_encode($r);
    }

    /**
     * POST habilitar/inhabilitar formulario (body JSON: id, activo).
     */
    public function toggleFormulario()
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
        $activo = isset($datos['activo']) ? (int) $datos['activo'] : 0;
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $r = FormularioDAO::toggleActivo($id, $activo, $personaId);
        echo json_encode($r);
    }

    /**
     * POST eliminar formulario (body JSON: id).
     */
    public function eliminarFormulario()
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
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $r = FormularioDAO::eliminar($id, $personaId);
        echo json_encode($r);
    }

    /**
     * GET lista de preguntas para el panel Formularios (predefinidas + personalizadas).
     * Query opcional: id_formulario para filtrar por formulario.
     */
    public function getPreguntasFormulario()
    {
        header('Content-Type: application/json; charset=utf-8');
        $personaId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($personaId < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => []]);
            return;
        }
        $idFormulario = isset($_GET['id_formulario']) ? (int) $_GET['id_formulario'] : null;
        if ($idFormulario !== null && $idFormulario < 1) {
            $idFormulario = null;
        }
        try {
            $datos = PreguntaDAO::listarParaPanel($personaId, $idFormulario);
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
