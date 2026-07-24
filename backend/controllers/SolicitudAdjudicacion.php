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
        if (!$this->autorizarCanal('ATC', false)) {
            return;
        }
        self::set('titulo', 'ATC - Solicitud de Adjudicacion');
        self::set('solicitudes_tablas_disponibles', $this->model->tablasDisponibles());
        self::render('solicitud_adjudicacion_atc');
    }

    public function despachos(): void
    {
        if (!$this->autorizarCanal('DESPACHOS', false)) {
            return;
        }
        self::set('titulo', 'Despachos - Solicitud de Adjudicacion');
        self::set('solicitudes_tablas_disponibles', $this->model->tablasDisponibles());
        self::render('solicitud_adjudicacion_despachos');
    }

    public function callCenter(): void
    {
        if (!$this->autorizarCanal('CALLCENTER', false)) {
            return;
        }
        self::set('titulo', 'Call Center - Solicitud de Adjudicacion');
        self::set('solicitudes_tablas_disponibles', $this->model->tablasDisponibles());
        self::render('solicitud_adjudicacion_callcenter');
    }

    public function buscarCredito(): void
    {
        if (!$this->autorizarCanal('ATC', true)) {
            return;
        }
        $this->buscarCreditoRespuesta();
    }

    public function buscarCreditoDespachos(): void
    {
        if (!$this->autorizarCanal('DESPACHOS', true)) {
            return;
        }
        $this->buscarCreditoRespuesta();
    }

    private function buscarCreditoRespuesta(): void
    {
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
        if (!$this->autorizarCanal('ATC', true)) {
            return;
        }
        $this->crearPorCanal('ATC');
    }

    public function crearCallCenter(): void
    {
        if (!$this->autorizarCanal('CALLCENTER', true)) {
            return;
        }
        $this->crearPorCanal('CALLCENTER');
    }

    public function crearDespachos(): void
    {
        if (!$this->autorizarCanal('DESPACHOS', true)) {
            return;
        }
        $this->crearPorCanal('DESPACHOS');
    }

    private function crearPorCanal(string $canal): void
    {
        $body = $this->payload();
        $body['canal'] = $canal;
        [$actorId, $actorNombre] = $this->actor();
        $this->json($this->model->crear($body, $actorId, $actorNombre));
    }

    public function listar(): void
    {
        if (!$this->autorizarCanal('ATC', true)) {
            return;
        }
        [$actorId] = $this->actor();
        $this->json($this->model->listarPorSolicitante($actorId, ['q' => $_GET['q'] ?? ''], 'ATC'));
    }

    public function listarDespachos(): void
    {
        if (!$this->autorizarCanal('DESPACHOS', true)) {
            return;
        }
        [$actorId] = $this->actor();
        $this->json($this->model->listarPorSolicitante($actorId, ['q' => $_GET['q'] ?? ''], 'DESPACHOS'));
    }

    public function listarCallCenter(): void
    {
        if (!$this->autorizarCanal('CALLCENTER', true)) {
            return;
        }
        [$actorId] = $this->actor();
        $this->json($this->model->listarPorSolicitante($actorId, ['q' => $_GET['q'] ?? ''], 'CALLCENTER'));
    }

    public function detalle($id = 0): void
    {
        if (!$this->autorizarCanal('ATC', true)) {
            return;
        }
        $this->detalleRespuesta((int) $id);
    }

    public function detalleDespachos($id = 0): void
    {
        if (!$this->autorizarCanal('DESPACHOS', true)) {
            return;
        }
        $this->detalleRespuesta((int) $id);
    }

    public function detalleCallCenter($id = 0): void
    {
        if (!$this->autorizarCanal('CALLCENTER', true)) {
            return;
        }
        $this->detalleRespuesta((int) $id);
    }

    private function detalleRespuesta(int $id): void
    {
        [$actorId] = $this->actor();
        $row = $this->model->obtenerPorId($id, $actorId);
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

    private function autorizarCanal(string $canal, bool $json): bool
    {
        $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        $permisos = [
            'ATC' => [69],
            'CALLCENTER' => [35],
            'DESPACHOS' => [20, 45],
        ];
        if (array_intersect($permisos[$canal] ?? [], $modulos)) {
            return true;
        }

        http_response_code(403);
        $etiqueta = $canal === 'CALLCENTER' ? 'Call Center' : ucfirst(strtolower($canal));
        if ($json) {
            $this->json(['success' => false, 'message' => 'No tienes permiso para operar solicitudes de ' . $etiqueta . '.']);
        } else {
            echo '<div class="alert alert-danger m-4">No tienes permiso para operar solicitudes de ' . htmlspecialchars($etiqueta) . '.</div>';
        }
        return false;
    }

    private function json(array $response): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
