<?php

namespace Controllers;

use Core\Controller;
use Services\LeonidasAssistantService;
use Services\LeonidasMessagingService;

class Leonidas extends Controller
{
    public function conversar(): void
    {
        $payload = $this->payloadJson();
        $mensaje = is_string($payload['mensaje'] ?? null) ? $payload['mensaje'] : '';

        try {
            $servicio = new LeonidasAssistantService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => $servicio->conversar($mensaje),
            ]);
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Conversacion fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'Leonidas no pudo procesar la solicitud. No se realizo ningun cambio.']);
        }
    }

    public function confirmar(): void
    {
        $payload = $this->payloadJson();
        $token = is_string($payload['token'] ?? null) ? trim($payload['token']) : '';

        try {
            $servicio = new LeonidasAssistantService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => $servicio->confirmar($token),
            ]);
        } catch (\RuntimeException $error) {
            http_response_code(409);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Confirmacion fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo confirmar la solicitud. No se realizo ningun cambio.']);
        }
    }

    public function cancelar(): void
    {
        $payload = $this->payloadJson();
        $token = is_string($payload['token'] ?? null) ? trim($payload['token']) : '';

        try {
            $servicio = new LeonidasAssistantService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => $servicio->cancelar($token),
            ]);
        } catch (\RuntimeException $error) {
            http_response_code(409);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Cancelacion fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo cancelar la solicitud.']);
        }
    }

    public function editarMensaje(): void
    {
        $payload = $this->payloadJson();
        $token = is_string($payload['token'] ?? null) ? trim($payload['token']) : '';

        try {
            $servicio = new LeonidasAssistantService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => $servicio->editarMensaje($token),
            ]);
        } catch (\RuntimeException $error) {
            http_response_code(409);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Reedicion de mensaje fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo preparar la reedicion del mensaje.']);
        }
    }

    public function bandeja(): void
    {
        try {
            $this->payloadJson();
            $actor = LeonidasMessagingService::actorSesion();
            $servicio = new LeonidasMessagingService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => [
                    'entrega' => $servicio->obtenerEntrega((int) $actor['actor_id']),
                    'novedades' => $servicio->obtenerNovedadesRemitente((int) $actor['actor_id']),
                ],
            ]);
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\RuntimeException $error) {
            http_response_code(401);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Bandeja fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo consultar la mensajeria de Leonidas.']);
        }
    }

    public function responder(): void
    {
        try {
            $payload = $this->payloadJson();
            $actor = LeonidasMessagingService::actorSesion();
            $mensajeId = (int) ($payload['mensaje_id'] ?? 0);
            $tipo = is_string($payload['tipo'] ?? null) ? trim($payload['tipo']) : '';
            $contenido = is_string($payload['contenido'] ?? null) ? trim($payload['contenido']) : '';
            $servicio = new LeonidasMessagingService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => $servicio->responder(
                    (int) $actor['actor_id'],
                    $mensajeId,
                    $tipo,
                    $contenido
                ),
            ]);
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\RuntimeException $error) {
            http_response_code(409);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Respuesta de mensaje fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo registrar la respuesta. Intenta nuevamente.']);
        }
    }

    private function payloadJson(): array
    {
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            throw new \InvalidArgumentException('Este endpoint requiere una solicitud POST.');
        }

        $raw = file_get_contents('php://input');
        $payload = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('La solicitud no contiene JSON valido.');
        }

        return $payload;
    }
}
