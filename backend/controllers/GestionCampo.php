<?php

namespace Controllers;

use Core\Controller;
use Models\GestionCampo as GestionCampoDAO;
use Models\MotosAdjudicadas as MotosAdjudicadasDAO;
use Services\LegacyPushService;

class GestionCampo extends Controller
{
    private GestionCampoDAO $model;
    private MotosAdjudicadasDAO $motos;
    private LegacyPushService $push;

    public function __construct()
    {
        parent::__construct();
        $this->model = new GestionCampoDAO();
        $this->motos = new MotosAdjudicadasDAO();
        $this->push = new LegacyPushService();
    }

    public function iniciar(): void
    {
        if (!$this->autorizar()) return;
        $body = $this->payload();
        [$actorId, $actorNombre] = $this->actor();
        $resultado = $this->model->iniciarDesdeLuzVerde((int) ($body['id_solicitud'] ?? 0), $actorId, $actorNombre);
        $gestion = is_array($resultado['gestion'] ?? null) ? $resultado['gestion'] : [];
        if (!empty($resultado['success']) && empty($gestion['notificacion_luz_verde_at'])) {
            $preparacion = $this->motos->prepararPayloadAprobacionEvidenciasAtencion((int) ($gestion['id_operacion'] ?? 0));
            $notificacion = !empty($preparacion['success'])
                ? $this->push->enviarLuzVerde($preparacion)
                : ['success' => false, 'message' => $preparacion['message'] ?? 'No se encontró al gestor.'];
            $this->model->registrarResultadoNotificacion((int) ($gestion['id'] ?? 0), $notificacion, $actorId, $actorNombre);
            $resultado['notificacion'] = $notificacion;
        }
        $this->json($resultado);
    }

    public function evaluar($id = 0): void
    {
        if (!$this->autorizar()) return;
        $this->json($this->model->evaluarEvidencias((int) $id));
    }

    public function listar(): void
    {
        if (!$this->autorizar()) return;
        $this->json(['success' => true, 'rows' => $this->model->listarPendientes()]);
    }

    private function autorizar(): bool
    {
        $modulos = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        if (array_intersect([63, 70], $modulos)) return true;
        http_response_code(403);
        $this->json(['success' => false, 'message' => 'No tienes permiso para operar la gestión de campo.']);
        return false;
    }

    private function payload(): array
    {
        $body = json_decode(file_get_contents('php://input'), true);
        return is_array($body) ? $body : $_POST;
    }

    private function actor(): array
    {
        return [
            (int) ($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0),
            trim((string) ($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? 'Usuario Sparta')),
        ];
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
