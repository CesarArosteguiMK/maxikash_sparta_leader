<?php

namespace Services;

/**
 * Cliente limitado al endpoint oficial de evidencias del Tracking.
 */
final class LeonidasTrackingEvidenceService
{
    public function adjuntarRuta(int $idRuta, array $archivo, string $mensaje = ''): array
    {
        if ($idRuta <= 0 || !is_file((string) ($archivo['ruta_absoluta'] ?? ''))) {
            return ['success' => false, 'message' => 'Ruta o archivo inválido.'];
        }
        if (!function_exists('curl_init') || !class_exists('\CURLFile')) {
            return ['success' => false, 'message' => 'cURL no está disponible para enviar la evidencia al Tracking.'];
        }
        $cfg = $this->config();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '') {
            return ['success' => false, 'message' => 'La API oficial de Tracking no está configurada.'];
        }
        $token = $this->jwt($cfg);
        if ($token === '') {
            return ['success' => false, 'message' => 'No fue posible autenticar la API oficial de Tracking.'];
        }

        $post = [
            'archivo' => new \CURLFile(
                (string) $archivo['ruta_absoluta'],
                (string) ($archivo['mime_type'] ?? 'application/octet-stream'),
                (string) ($archivo['nombre_original'] ?? 'evidencia')
            ),
            'id_ruta' => (string) $idRuta,
        ];
        if (trim($mensaje) !== '') {
            $post['mensaje'] = mb_substr(trim($mensaje), 0, 1000);
        }
        $paths = [
            "/api/tracking/rutas/{$idRuta}/chat/archivos",
            "/api/tracking/rutas/{$idRuta}/chats/general/archivos",
            "/api/tracking/chats/ruta/{$idRuta}/archivos",
            "/api/tracking/chats/rutas/{$idRuta}/archivos",
            "/api/tracking/chats/route/{$idRuta}/archivos",
        ];
        $ultima = [];
        foreach ($paths as $path) {
            $ultima = $this->multipart($this->url($cfg['base_url'], $path), $post, [
                'X-API-Key: ' . $cfg['api_key'],
                'Authorization: Bearer ' . $token,
            ]);
            if (!$this->notFound($ultima)) {
                break;
            }
        }
        $data = json_decode((string) ($ultima['body'] ?? ''), true);
        $ok = (int) ($ultima['http_code'] ?? 0) >= 200 && (int) ($ultima['http_code'] ?? 0) < 300;
        return [
            'success' => $ok,
            'message' => $ok
                ? 'Evidencia adjuntada en la API oficial de Tracking.'
                : (string) ($data['mensaje'] ?? $data['detail'] ?? $ultima['error'] ?? 'La API de Tracking rechazó la evidencia.'),
            'id_ruta' => $idRuta,
            'respuesta_api' => is_array($data) ? $data : [],
        ];
    }

    private function config(): array
    {
        $cfg = function_exists('config_api_load_from_db') ? config_api_load_from_db() : [];
        return [
            'base_url' => trim((string) ($cfg['TRACKING_BASE_URL'] ?? getenv('TRACKING_BASE_URL') ?: '')),
            'api_key' => trim((string) ($cfg['TRACKING_API_KEY'] ?? getenv('TRACKING_API_KEY') ?: '')),
            'user' => trim((string) ($cfg['TRACKING_GESTOR_USER'] ?? getenv('TRACKING_GESTOR_USER') ?: '')),
            'pass' => trim((string) ($cfg['TRACKING_GESTOR_PASS'] ?? getenv('TRACKING_GESTOR_PASS') ?: '')),
        ];
    }

    private function jwt(array $cfg): string
    {
        if (!empty($_SESSION['_trk_chat_jwt'])
            && (int) ($_SESSION['_trk_chat_jwt_exp'] ?? 0) > time() + 300
        ) {
            return (string) $_SESSION['_trk_chat_jwt'];
        }
        $body = json_encode(['username' => $cfg['user'], 'password' => $cfg['pass']]);
        $respuesta = $this->jsonRequest(
            $this->url($cfg['base_url'], '/api/login'),
            (string) $body,
            ['Content-Type: application/json', 'X-API-Key: ' . $cfg['api_key']]
        );
        if ((int) ($respuesta['http_code'] ?? 0) !== 200) {
            return '';
        }
        $data = json_decode((string) ($respuesta['body'] ?? ''), true);
        $token = is_array($data) ? (string) ($data['access_token'] ?? $data['token'] ?? '') : '';
        if ($token !== '') {
            $_SESSION['_trk_chat_jwt'] = $token;
            $_SESSION['_trk_chat_jwt_exp'] = time() + 3300;
        }
        return $token;
    }

    private function jsonRequest(string $url, string $body, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw = curl_exec($ch);
        $error = $raw === false ? curl_error($ch) : '';
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $code, 'body' => $raw === false ? '' : (string) $raw, 'error' => $error];
    }

    private function multipart(string $url, array $post, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw = curl_exec($ch);
        $error = $raw === false ? curl_error($ch) : '';
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['http_code' => $code, 'body' => $raw === false ? '' : (string) $raw, 'error' => $error];
    }

    private function url(string $base, string $path): string
    {
        $base = rtrim($base, '/');
        if (substr(strtolower($base), -4) === '/api' && str_starts_with($path, '/api/')) {
            $path = substr($path, 4);
        }
        return $base . $path;
    }

    private function notFound(array $response): bool
    {
        $data = json_decode((string) ($response['body'] ?? ''), true);
        return (int) ($response['http_code'] ?? 0) === 404
            && is_array($data)
            && strtolower((string) ($data['detail'] ?? '')) === 'not found';
    }
}
