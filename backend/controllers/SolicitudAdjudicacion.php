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

    public function bandeja(): void
    {
        if (!$this->autorizarCanal('BANDEJA', false)) {
            return;
        }
        self::set('titulo', 'Motos Adjudicadas - Bandeja de Solicitudes');
        self::set('solicitudes_tablas_disponibles', $this->model->tablasDisponibles());
        self::render('solicitud_adjudicacion_bandeja');
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

    public function listarBandeja(): void
    {
        if (!$this->autorizarCanal('BANDEJA', true)) {
            return;
        }
        $this->json($this->model->listarBandeja([
            'q' => $_GET['q'] ?? '',
            'canal' => $_GET['canal'] ?? '',
            'estatus' => $_GET['estatus'] ?? '',
        ]));
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

    public function detalleBandeja($id = 0): void
    {
        if (!$this->autorizarCanal('BANDEJA', true)) {
            return;
        }
        $row = $this->model->obtenerPorIdBandeja((int) $id);
        $this->json($row
            ? ['success' => true, 'solicitud' => $row]
            : ['success' => false, 'message' => 'Solicitud no encontrada.']);
    }

    public function responsablesBandeja(): void
    {
        if (!$this->autorizarCanal('BANDEJA', true)) {
            return;
        }
        try {
            $this->json([
                'success' => true,
                'rows' => $this->creditos->obtenerResponsables(),
            ]);
        } catch (\Throwable $e) {
            error_log('[SolicitudAdjudicacion::responsablesBandeja] ' . $e->getMessage());
            $this->json(['success' => false, 'rows' => [], 'message' => 'No se pudo cargar el catalogo de responsables.']);
        }
    }

    public function asignarBandeja(): void
    {
        if (!$this->autorizarCanal('BANDEJA', true)) {
            return;
        }
        $body = $this->payload();
        $idSolicitud = (int) ($body['id_solicitud'] ?? 0);
        $idPersona = (int) ($body['id_persona'] ?? 0);
        $comentario = trim((string) ($body['comentario'] ?? ''));
        if ($idSolicitud <= 0 || $idPersona <= 0) {
            $this->json(['success' => false, 'message' => 'Selecciona una solicitud y un responsable validos.']);
            return;
        }

        $solicitud = $this->model->obtenerPorIdBandeja($idSolicitud);
        if (!$solicitud) {
            $this->json(['success' => false, 'message' => 'La solicitud ya no esta disponible.']);
            return;
        }
        if (!$this->creditos->idPersonaEsResponsableActivo($idPersona)) {
            $this->json(['success' => false, 'message' => 'El responsable seleccionado no esta activo en Motos Adjudicadas.']);
            return;
        }
        $responsable = $this->creditos->obtenerDatosResponsable($idPersona);
        $nombreResponsable = trim((string) ($responsable['nombre_completo'] ?? ''));
        if ($nombreResponsable === '') {
            $this->json(['success' => false, 'message' => 'No se pudo identificar al responsable seleccionado.']);
            return;
        }

        [$actorId, $actorNombre] = $this->actor();
        $asignacion = $this->creditos->reasignarCredito(
            $idPersona,
            (int) $solicitud['id_credito'],
            $actorId
        );
        if (empty($asignacion['success'])) {
            $this->json([
                'success' => false,
                'message' => $asignacion['message'] ?? 'No se pudo completar la asignacion operativa.',
                'asignacion' => $asignacion,
            ]);
            return;
        }

        $resultado = $this->model->registrarAsignacionBandeja(
            $idSolicitud,
            $idPersona,
            $nombreResponsable,
            $actorId,
            $actorNombre,
            $comentario
        );
        $resultado['asignacion_operativa'] = $asignacion;
        $this->json($resultado);
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
            'BANDEJA' => [62, 63, 80],
        ];
        if (array_intersect($permisos[$canal] ?? [], $modulos)) {
            return true;
        }

        http_response_code(403);
        $etiqueta = $canal === 'CALLCENTER'
            ? 'Call Center'
            : ($canal === 'BANDEJA' ? 'la bandeja de solicitudes' : ucfirst(strtolower($canal)));
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
