<?php

namespace Services;

final class LegacyPushService
{
    public function enviarLuzVerde(array $preparacion): array
    {
        $payloadBase = is_array($preparacion['payload'] ?? null) ? $preparacion['payload'] : [];
        $destinatarios = is_array($preparacion['destinatarios'] ?? null) ? $preparacion['destinatarios'] : [];
        if ($destinatarios === [] && $payloadBase !== []) {
            $destinatarios[] = $payloadBase;
        }
        $cfg = $this->config();
        if ($cfg['api_key'] === '') {
            return ['success' => false, 'message' => 'Servicio de notificaciones Legacy no configurado.'];
        }

        $errores = [];
        foreach ($destinatarios as $destinatario) {
            $userId = (int) ($destinatario['user_id_legacy'] ?? 0);
            $externalId = trim((string) ($destinatario['external_id'] ?? ''));
            if ($userId <= 0 || $externalId === '') continue;

            $respuesta = $this->post($cfg, [
                'user_id_legacy' => (string) $userId,
                'external_id' => $externalId,
                'titulo' => 'Luz Verde para recoger la moto',
                'mensaje' => 'La solicitud fue autorizada. Ya puedes iniciar la recolección de la moto.',
                'evento' => 'moto_luz_verde',
                'data' => [
                    'type' => 'moto_luz_verde',
                    'screen' => 'MotoDetalle',
                    'tab' => 'Recoleccion',
                    'id_operacion' => (int) ($payloadBase['id_operacion'] ?? 0),
                    'id_credito' => (int) ($payloadBase['id_credito'] ?? 0),
                    'luz_verde' => true,
                ],
            ]);
            if ($respuesta['success']) {
                return [
                    'success' => true,
                    'message' => 'Luz Verde notificada al gestor.',
                    'destinatario' => ['user_id_legacy' => $userId, 'external_id' => $externalId],
                    'http_code' => $respuesta['http_code'],
                ];
            }
            $errores[] = ['user_id_legacy' => $userId, 'external_id' => $externalId, 'message' => $respuesta['message']];
        }
        return ['success' => false, 'message' => 'No se pudo notificar la Luz Verde al gestor.', 'intentos' => $errores];
    }

    private function config(): array
    {
        $db = function_exists('config_api_load_from_db') ? config_api_load_from_db() : [];
        $value = static function (array $keys) use ($db): string {
            foreach ($keys as $key) {
                $candidate = trim((string) ($db[$key] ?? getenv($key) ?: ''));
                if ($candidate !== '') return $candidate;
            }
            return '';
        };
        $base = $value(['MOTOS_ADJUDICADAS_PUSH_BASE_URL']) ?: 'https://motosadjudicadas-601258367060.us-central1.run.app';
        return ['base_url' => rtrim($base, '/'), 'api_key' => $value(['MOTOS_ADJUDICADAS_API_KEY', 'MOTOS_ADJUDICADAS_TOKEN'])];
    }

    private function post(array $cfg, array $payload): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'http_code' => 0, 'message' => 'cURL no está disponible.'];
        }
        $ch = curl_init($cfg['base_url'] . '/api/push-notifications/legacy/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8', 'Accept: application/json', 'X-API-Key: ' . $cfg['api_key']],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $decoded = json_decode($raw === false ? '' : (string) $raw, true);
        return [
            'success' => $code >= 200 && $code < 300,
            'http_code' => $code,
            'message' => is_array($decoded)
                ? (string) ($decoded['message'] ?? $decoded['mensaje'] ?? $decoded['detail'] ?? '')
                : ($error ?: 'Respuesta inválida del servicio de notificaciones.'),
        ];
    }
}

