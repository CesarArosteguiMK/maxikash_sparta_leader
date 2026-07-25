<?php

namespace Controllers;

use Core\EnvLoader;
use Services\LeonidasWhatsAppProtocol;
use Services\LeonidasWhatsAppService;

final class LeonidasWhatsApp
{
    public function webhook(): void
    {
        EnvLoader::load();
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($method === 'GET') {
            $challenge = LeonidasWhatsAppProtocol::challenge(
                $_GET,
                trim((string) getenv('WHATSAPP_VERIFY_TOKEN'))
            );
            if ($challenge === null) {
                http_response_code(403);
                echo 'Verification failed';
                return;
            }
            header('Content-Type: text/plain; charset=utf-8');
            echo $challenge;
            return;
        }

        if ($method !== 'POST') {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }

        $body = (string) (file_get_contents('php://input') ?: '');
        $signature = (string) ($_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '');
        if (!LeonidasWhatsAppProtocol::firmaValida(
            $body,
            $signature,
            trim((string) getenv('WHATSAPP_APP_SECRET'))
        )) {
            http_response_code(401);
            echo 'Invalid signature';
            return;
        }

        try {
            (new LeonidasWhatsAppService())->procesar($body);
            http_response_code(200);
            echo 'EVENT_RECEIVED';
        } catch (\InvalidArgumentException $error) {
            error_log('[Leonidas WhatsApp] Payload invalido: ' . $error->getMessage());
            http_response_code(400);
            echo 'INVALID_PAYLOAD';
        } catch (\Throwable $error) {
            error_log('[Leonidas WhatsApp] Webhook fallido: ' . $error->getMessage());
            http_response_code(500);
            echo 'PROCESSING_ERROR';
        }
    }
}
