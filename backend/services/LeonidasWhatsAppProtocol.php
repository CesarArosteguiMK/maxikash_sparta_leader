<?php

namespace Services;

final class LeonidasWhatsAppProtocol
{
    public static function challenge(array $query, string $verifyToken): ?string
    {
        $mode = (string) ($query['hub_mode'] ?? $query['hub.mode'] ?? '');
        $token = (string) ($query['hub_verify_token'] ?? $query['hub.verify_token'] ?? '');
        $challenge = (string) ($query['hub_challenge'] ?? $query['hub.challenge'] ?? '');

        if ($verifyToken === '' || $mode !== 'subscribe' || !hash_equals($verifyToken, $token)) {
            return null;
        }

        return $challenge;
    }

    public static function firmaValida(string $body, string $signature, string $appSecret): bool
    {
        if ($body === '' || $signature === '' || $appSecret === '') {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $body, $appSecret);
        return hash_equals($expected, trim($signature));
    }

    public static function extraerMensajes(string $body): array
    {
        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('El webhook de WhatsApp no contiene JSON valido.');
        }

        $mensajes = [];
        foreach ((array) ($payload['entry'] ?? []) as $entry) {
            foreach ((array) ($entry['changes'] ?? []) as $change) {
                $value = is_array($change['value'] ?? null) ? $change['value'] : [];
                $profileByPhone = [];
                foreach ((array) ($value['contacts'] ?? []) as $contact) {
                    $waId = self::soloDigitos((string) ($contact['wa_id'] ?? ''));
                    if ($waId !== '') {
                        $profileByPhone[$waId] = trim((string) ($contact['profile']['name'] ?? ''));
                    }
                }

                foreach ((array) ($value['messages'] ?? []) as $message) {
                    if (!is_array($message)) {
                        continue;
                    }
                    $from = self::soloDigitos((string) ($message['from'] ?? ''));
                    $type = strtolower(trim((string) ($message['type'] ?? '')));
                    $text = $type === 'text'
                        ? trim((string) ($message['text']['body'] ?? ''))
                        : '';
                    $mensajes[] = [
                        'id' => trim((string) ($message['id'] ?? '')),
                        'from' => $from,
                        'profile_name' => (string) ($profileByPhone[$from] ?? ''),
                        'type' => $type,
                        'text' => $text,
                        'timestamp' => (int) ($message['timestamp'] ?? 0),
                    ];
                }
            }
        }

        return $mensajes;
    }

    public static function clavesTelefono(string $phone): array
    {
        $digits = self::soloDigitos($phone);
        if ($digits === '') {
            return [];
        }

        $keys = [$digits];
        if (strlen($digits) >= 10) {
            $local = substr($digits, -10);
            $keys[] = $local;
            $keys[] = '52' . $local;
            $keys[] = '521' . $local;
        }

        return array_values(array_unique($keys));
    }

    public static function telefonosCoinciden(string $left, string $right): bool
    {
        return count(array_intersect(self::clavesTelefono($left), self::clavesTelefono($right))) > 0;
    }

    public static function comando(string $text): ?string
    {
        $normalized = self::normalizarTexto($text);
        if (in_array($normalized, ['confirmar', 'confirmo', 'si confirmar', 'si confirmo'], true)) {
            return 'confirmar';
        }
        if (in_array($normalized, ['cancelar', 'cancela', 'no cancelar', 'descartar'], true)) {
            return 'cancelar';
        }
        return null;
    }

    public static function textoRespuesta(array $response): string
    {
        $text = trim((string) ($response['mensaje'] ?? $response['respuesta'] ?? ''));
        if ($text === '') {
            $text = 'Procesé tu solicitud, pero no recibí una respuesta legible. Intenta formularla de nuevo.';
        }

        if (is_array($response['propuesta'] ?? null)
            && !empty($response['propuesta']['requiere_confirmacion'])) {
            $text .= "\n\nPara ejecutar la acción responde *CONFIRMAR*. Para descartarla responde *CANCELAR*.";
        }

        return self::limitarTexto($text);
    }

    private static function soloDigitos(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: '';
    }

    private static function normalizarTexto(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = is_string($ascii) ? $ascii : $value;
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?: '';
        return trim(preg_replace('/\s+/', ' ', $value) ?: '');
    }

    private static function limitarTexto(string $text): string
    {
        $max = 3900;
        if (mb_strlen($text, 'UTF-8') <= $max) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $max - 40, 'UTF-8'))
            . "\n\nRespuesta recortada. Pide el detalle por partes.";
    }
}
