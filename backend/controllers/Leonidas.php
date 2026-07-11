<?php

namespace Controllers;

use Core\Controller;
use Services\LeonidasAssistantService;

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
