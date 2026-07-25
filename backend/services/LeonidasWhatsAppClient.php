<?php

namespace Services;

use Core\EnvLoader;

final class LeonidasWhatsAppClient
{
    public function enviarTexto(string $phone, string $text): void
    {
        EnvLoader::load();
        $enabled = filter_var(getenv('WHATSAPP_CLOUD_ENABLED') ?: '0', FILTER_VALIDATE_BOOL);
        $token = trim((string) getenv('WHATSAPP_ACCESS_TOKEN'));
        $phoneNumberId = trim((string) getenv('WHATSAPP_PHONE_NUMBER_ID'));
        $version = trim((string) (getenv('WHATSAPP_GRAPH_VERSION') ?: 'v23.0'));

        if (!$enabled) {
            throw new \RuntimeException('WhatsApp Cloud API esta deshabilitado en la configuracion.');
        }
        if ($token === '' || $phoneNumberId === '') {
            throw new \RuntimeException('Falta configurar el token o el identificador del numero de WhatsApp.');
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            rawurlencode($version),
            rawurlencode($phoneNumberId)
        );
        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => preg_replace('/\D+/', '', $phone),
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $text,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false || $status < 200 || $status >= 300) {
            $detail = $error !== '' ? $error : (string) $body;
            error_log('[Leonidas WhatsApp] Meta rechazo el mensaje: HTTP ' . $status . ' ' . $detail);
            throw new \RuntimeException('Meta no acepto la respuesta de WhatsApp.');
        }
    }
}
