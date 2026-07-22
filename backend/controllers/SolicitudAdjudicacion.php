<?php

namespace Controllers;

use Core\Controller;
use Models\Adjudicacion as AdjudicacionDAO;
use Models\SolicitudAdjudicacion as SolicitudDAO;

class SolicitudAdjudicacion extends Controller
{
    private SolicitudDAO $model;
    private AdjudicacionDAO $creditos;

    public function __construct()
    {
        parent::__construct();
        $this->model = new SolicitudDAO();
        $this->creditos = new AdjudicacionDAO();
    }

    public function atc(): void
    {
        if (!$this->autorizarAtc(false)) {
            return;
        }
        self::set('titulo', 'ATC - Solicitud de Adjudicacion');
        self::set('solicitudes_tablas_disponibles', $this->model->tablasDisponibles());
        self::render('solicitud_adjudicacion_atc');
    }

    public function buscarCredito(): void
    {
        if (!$this->autorizarAtc(true)) {
            return;
        }
        $body = $this->payload();
        $idCredito = (int) ($body['id_credito'] ?? $body['valor'] ?? 0);
        if ($idCredito <= 0) {
            $this->json(['success' => false, 'message' => 'Captura un ID de credito valido.']);
            return;
        }
        try {
            $this->json($this->creditos->buscarCreditoPorId($idCredito));
        } catch (\Throwable $e) {
            error_log('[SolicitudAdjudicacion] Error al consultar crédito: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No se pudo consultar el crédito.']);
        }
    }

    public function crear(): void
    {
        if (!$this->autorizarAtc(true)) {
            return;
        }
        $body = $this->payload();
        $body['canal'] = 'ATC';
        [$actorId, $actorNombre] = $this->actor();
        $this->json($this->model->crear($body, $actorId, $actorNombre));
    }

    public function listar(): void
    {
        if (!$this->autorizarAtc(true)) {
            return;
        }
        [$actorId] = $this->actor();
        $this->json($this->model->listarPorSolicitante($actorId, ['q' => $_GET['q'] ?? '']));
    }

    public function detalle($id = 0): void
    {
        if (!$this->autorizarAtc(true)) {
            return;
        }
        [$actorId] = $this->actor();
        $row = $this->model->obtenerPorId((int) $id, $actorId);
        $this->json($row
            ? ['success' => true, 'solicitud' => $row]
            : ['success' => false, 'message' => 'Solicitud no encontrada.']);
    }

    private function payload(): array
    {
        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : [];
        return is_array($body) && $body !== [] ? $body : $_POST;
    }

    /** @return array{0:int,1:string} */
    private function actor(): array
    {
        $id = (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        $nombre = trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario Sparta'));
        return [$id, $nombre];
    }

    private function autorizarAtc(bool $json): bool
    {
        $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        if (in_array(69, $modulos, true)) {
            return true;
        }

        http_response_code(403);
        if ($json) {
            $this->json(['success' => false, 'message' => 'No tienes permiso para operar solicitudes ATC.']);
        } else {
            echo '<div class="alert alert-danger m-4">No tienes permiso para operar solicitudes ATC.</div>';
        }
        return false;
    }

    private function json(array $response): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
