<?php

namespace Controllers;

use Core\Controller;
use Services\LeonidasAccessService;
use Services\LeonidasAgentService;
use Services\LeonidasAssistantService;
use Services\LeonidasMediaService;
use Services\LeonidasMessagingService;
use Services\LeonidasRealtimeTtsService;
use Services\LeonidasSpreadsheetService;
use Services\LeonidasTtsService;

class Leonidas extends Controller
{
    public function conversar(): void
    {
        $this->exigirAccesoLeonidas();
        $payload = $this->payloadJson();
        $mensaje = is_string($payload['mensaje'] ?? null) ? $payload['mensaje'] : '';
        $archivoToken = is_string($payload['archivo_token'] ?? null) ? trim($payload['archivo_token']) : null;

        try {
            $servicio = new LeonidasAssistantService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => $servicio->conversar($mensaje, $archivoToken),
            ]);
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\DomainException $error) {
            http_response_code(403);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Conversacion fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'Leonidas no pudo procesar la solicitud. No se realizo ningun cambio.']);
        }
    }

    public function adjuntar(): void
    {
        try {
            $actor = $this->exigirAccesoLeonidas();
            if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
                throw new \InvalidArgumentException('Este endpoint requiere una solicitud POST.');
            }
            $archivo = is_array($_FILES['archivo'] ?? null) ? $_FILES['archivo'] : [];
            $carga = (new LeonidasSpreadsheetService())->guardarCarga($archivo, (int) $actor['actor_id']);
            self::respuestaJSON(['success' => true, 'respuesta' => $carga]);
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\RuntimeException $error) {
            http_response_code(401);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Carga de Excel fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo adjuntar el Excel. No se realizo ningun cambio.']);
        }
    }

    public function confirmar(): void
    {
        $this->exigirAccesoLeonidas();
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
        $this->exigirAccesoLeonidas();
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
        $this->exigirAccesoLeonidas();
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
            $actor = $this->exigirAccesoLeonidas();
            $this->payloadJson();
            $servicio = new LeonidasMessagingService();
            $agente = new LeonidasAgentService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => [
                    'entrega' => $servicio->obtenerEntrega((int) $actor['actor_id']),
                    'novedades' => $servicio->obtenerNovedadesRemitente((int) $actor['actor_id']),
                    'entrada_segura' => $agente->entradaSeguraPendiente((int) $actor['actor_id']),
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
            $actor = $this->exigirAccesoLeonidas();
            $payload = $this->payloadJson();
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

    public function voz(): void
    {
        try {
            $this->exigirAccesoLeonidas();
            $payload = $this->payloadJson();
            $texto = is_string($payload['texto'] ?? null) ? $payload['texto'] : '';
            $servicio = new LeonidasTtsService();
            self::respuestaJSON([
                'success' => true,
                'respuesta' => $servicio->sintetizar($texto),
            ]);
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\RuntimeException $error) {
            http_response_code(503);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Voz fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'La voz de Leonidas no esta disponible en este momento.']);
        }
    }

    public function vozTiempoReal(): void
    {
        try {
            $this->exigirAccesoLeonidas();
            $payload = $this->payloadJson();
            $texto = is_string($payload['texto'] ?? null) ? $payload['texto'] : '';

            set_time_limit(60);
            @ini_set('zlib.output_compression', '0');
            @ini_set('output_buffering', '0');
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            ob_implicit_flush(true);
            header('Content-Type: application/x-ndjson; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('X-Accel-Buffering: no');
            header('X-Content-Type-Options: nosniff');

            $emit = static function (array $event): void {
                $line = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if ($line !== false) {
                    echo $line . "\n";
                    @flush();
                }
            };

            $emit(['type' => 'meta', 'sample_rate' => 24000]);
            $servicio = new LeonidasRealtimeTtsService();
            $metadata = $servicio->transmitir($texto, static function (string $delta) use ($emit): void {
                $emit(['type' => 'audio', 'delta' => $delta]);
            });
            $emit(['type' => 'done', 'metadata' => $metadata]);
            exit;
        } catch (\Throwable $error) {
            error_log('[Leonidas] Voz en tiempo real fallida: ' . $error->getMessage());
            if (!headers_sent()) {
                http_response_code($error instanceof \InvalidArgumentException ? 422 : 503);
                header('Content-Type: application/x-ndjson; charset=utf-8');
                header('Cache-Control: no-cache, no-store, must-revalidate');
            }
            echo json_encode([
                'type' => 'error',
                'message' => $error->getMessage(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
            @flush();
            exit;
        }
    }

    public function audio(): void
    {
        try {
            $this->exigirAccesoLeonidas();
            $token = is_string($_GET['token'] ?? null) ? trim($_GET['token']) : '';
            $servicio = new LeonidasTtsService();
            $audio = $servicio->obtenerAudio($token);
            header('Content-Type: ' . $audio['mime']);
            header('Content-Length: ' . strlen($audio['body']));
            header('Cache-Control: private, max-age=3600');
            header('X-Content-Type-Options: nosniff');
            echo $audio['body'];
            exit;
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\RuntimeException $error) {
            http_response_code(410);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Audio fallido: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo reproducir la voz de Leonidas.']);
        }
    }

    public function estadoMedio(): void
    {
        try {
            $actor = $this->exigirAccesoLeonidas();
            $payload = $this->payloadJson();
            $token = is_string($payload['token'] ?? null) ? trim($payload['token']) : '';
            $respuesta = (new LeonidasMediaService())->estado($token, (int) ($actor['actor_id'] ?? 0));
            self::respuestaJSON(['success' => true, 'respuesta' => $respuesta]);
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\DomainException $error) {
            http_response_code(403);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\RuntimeException $error) {
            http_response_code(410);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Estado multimedia fallido: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo consultar la generacion multimedia.']);
        }
    }

    public function medio(): void
    {
        try {
            $actor = $this->exigirAccesoLeonidas();
            $token = is_string($_GET['token'] ?? null) ? trim($_GET['token']) : '';
            $archivo = (new LeonidasMediaService())->obtener($token, (int) ($actor['actor_id'] ?? 0));
            $nombre = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) ($archivo['name'] ?? 'leonidas-media.bin'));
            $descargar = (string) ($_GET['download'] ?? '') === '1';

            header('Content-Type: ' . (string) $archivo['mime']);
            header('Content-Length: ' . strlen((string) $archivo['body']));
            header('Cache-Control: private, no-store, max-age=0');
            header('X-Content-Type-Options: nosniff');
            header(
                'Content-Disposition: ' . ($descargar ? 'attachment' : 'inline')
                . '; filename="' . ($nombre !== '' ? $nombre : 'leonidas-media.bin') . '"'
            );
            echo $archivo['body'];
            exit;
        } catch (\InvalidArgumentException $error) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\DomainException $error) {
            http_response_code(403);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\RuntimeException $error) {
            http_response_code(410);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\Throwable $error) {
            error_log('[Leonidas] Entrega multimedia fallida: ' . $error->getMessage());
            http_response_code(500);
            self::respuestaJSON(['success' => false, 'error' => 'No se pudo entregar el archivo multimedia.']);
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

    private function exigirAccesoLeonidas(): array
    {
        try {
            return LeonidasAccessService::actorAutorizado();
        } catch (\DomainException $error) {
            http_response_code(403);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        } catch (\RuntimeException $error) {
            http_response_code(401);
            self::respuestaJSON(['success' => false, 'error' => $error->getMessage()]);
        }

        return [];
    }
}
