<?php

namespace Controllers;

use Core\Controller;
use Models\Adjudicacion as AdjudicacionDAO;

class Adjudicacion extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new AdjudicacionDAO();
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    public function AsignacionCreditos()
    {
        $emp = defined('CONFIGURACION') && isset(CONFIGURACION['EMPRESA']) ? (string) CONFIGURACION['EMPRESA'] : '';
        self::set('titulo', 'Asignación de Créditos para Adjudicación ' . $emp);
        return self::render('asignacion_creditosAdjudicacion');
    }

    // =========================================================================
    // RESPONSABLES
    // =========================================================================

    /**
     * GET /Adjudicacion/obtenerListaResponsables
     */
    public function obtenerListaResponsables()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $responsables = $this->model->obtenerResponsables();
            echo json_encode([
                'success'      => true,
                'responsables' => $responsables,
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /Adjudicacion/obtenerDatosResponsable/{idPersona}
     */
    public function obtenerDatosResponsable($idPersona = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de persona inválido.']);
            return;
        }
        try {
            $datos = $this->model->obtenerDatosResponsable($idPersona);
            if (!$datos) {
                echo json_encode(['success' => false, 'message' => 'Responsable no encontrado.']);
                return;
            }
            echo json_encode(['success' => true, 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // BUSCAR CRÉDITO
    // =========================================================================

    /**
     * POST /Adjudicacion/buscarCredito
     * Body JSON: { "tipo": "id_credito", "valor": "12345" }
     */
    public function buscarCredito()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $valor = isset($body['valor']) ? (int) $body['valor'] : 0;

        if ($valor <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        try {
            $result = $this->model->buscarCreditoPorId($valor);

            // Solo créditos con statusCredito = "Vencido" pueden asignarse a adjudicación
            if ($result['success'] && isset($result['status_credito'])) {
                $status = trim($result['status_credito']);
                if (strcasecmp($status, 'Vencido') !== 0) {
                    echo json_encode([
                        'success'        => false,
                        'credito_regla'  => true,
                        'status_credito' => $status,
                        'message'        => "El crédito está en regla (estatus: {$status}). Solo se pueden adjudicar créditos con estatus \"Vencido\".",
                    ]);
                    return;
                }
            }

            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // ASIGNAR / DESASIGNAR
    // =========================================================================

    /**
     * POST /Adjudicacion/asignarCredito
     * Body JSON: { "id_persona": 1, "id_credito": 1637 }
     */
    public function asignarCredito()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idPersona = (int) ($body['id_persona'] ?? 0);
        $idCredito = (int) ($body['id_credito'] ?? 0);
        $usuarioId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);

        if ($idPersona <= 0 || $idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            return;
        }
        try {
            $result = $this->model->asignarCredito($idPersona, $idCredito, $usuarioId);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /Adjudicacion/desasignarCredito
     * Body JSON: { "id_credito": 1637 }
     */
    public function desasignarCredito()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);
        $usuarioId = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        try {
            $result = $this->model->desasignarCredito($idCredito, $usuarioId);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // CRÉDITOS ASIGNADOS
    // =========================================================================

    /**
     * GET /Adjudicacion/obtenerCreditosAsignados/{idPersona}
     */
    public function obtenerCreditosAsignados($idPersona = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de persona inválido.']);
            return;
        }
        try {
            $creditos = $this->model->obtenerCreditosAsignados($idPersona);
            echo json_encode(['success' => true, 'creditos' => $creditos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // HISTORIAL
    // =========================================================================

    /**
     * GET /Adjudicacion/historialCredito/{idCredito}
     */
    public function historialCredito($idCredito = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        $idCredito = (int) $idCredito;
        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }
        try {
            $historial = $this->model->obtenerHistorialCredito($idCredito);
            echo json_encode(['success' => true, 'historial' => $historial]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // COMENTARIOS
    // =========================================================================

    /**
     * POST /Adjudicacion/guardarComentarios
     * Body JSON: { "id_persona": 1, "comentario": "..." }
     */
    public function guardarComentarios()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idPersona = (int) ($body['id_persona'] ?? 0);
        $comentario = (string) ($body['comentario'] ?? '');

        if ($idPersona <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de persona inválido.']);
            return;
        }
        try {
            $ok = $this->model->guardarComentarios($idPersona, $comentario);
            echo json_encode(['success' => $ok]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // EXPORTAR EXCEL
    // =========================================================================

    /**
     * GET /Adjudicacion/exportarExcel/{idPersona}
     * (implementación pendiente — placeholder para no romper la ruta)
     */
    public function exportarExcel($idPersona = null)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            http_response_code(400);
            echo 'ID de persona inválido.';
            return;
        }
        // TODO: generar Excel con PhpSpreadsheet cuando se requiera
        http_response_code(501);
        echo 'Exportación Excel pendiente de implementación.';
    }
}
