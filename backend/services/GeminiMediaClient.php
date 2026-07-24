<?php

namespace Services;

/**
 * Server-side transport for Gemini image/video generation and Vertex Lyria.
 * Provider credentials and temporary download URLs never reach the browser.
 */
class GeminiMediaClient
{
    /** @param array<string, mixed> $schema @return array<string, mixed> */
    public function generateStructuredJson(string $prompt, array $schema): array
    {
        $config = $this->geminiConfig();
        if ($config['api_key'] === '') {
            return $this->failure('Gemini no está configurado para generar contenido estructurado.');
        }

        $response = $this->jsonRequest(
            'POST',
            $config['base_url'] . '/models/' . rawurlencode($config['text_model']) . ':generateContent',
            [
                'systemInstruction' => [
                    'parts' => [[
                        'text' => 'Responde exclusivamente con JSON válido conforme al esquema. ' .
                            'No inventes datos operativos, personas, cifras ni resultados que no estén en la solicitud.',
                    ]],
                ],
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.15,
                    'responseMimeType' => 'application/json',
                    'responseJsonSchema' => $schema,
                ],
            ],
            ['x-goog-api-key: ' . $config['api_key']],
            120
        );
        if (empty($response['success'])) {
            return $response;
        }

        $text = '';
        foreach ((array) ($response['data']['candidates'][0]['content']['parts'] ?? []) as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $text .= $part['text'];
            }
        }
        $text = preg_replace('/^\s*```(?:json)?\s*|\s*```\s*$/i', '', trim($text)) ?? trim($text);
        $data = json_decode($text, true);
        if (!is_array($data)) {
            return $this->failure('Gemini respondió, pero no devolvió JSON estructurado válido.');
        }

        return [
            'success' => true,
            'data' => $data,
            'model' => $config['text_model'],
        ];
    }

    /** @return array<string, mixed> */
    public function generateImage(string $prompt): array
    {
        $config = $this->geminiConfig();
        if ($config['api_key'] === '') {
            return $this->failure('Gemini no está configurado para generar imágenes.');
        }

        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'responseModalities' => ['TEXT', 'IMAGE'],
            ],
        ];
        $response = $this->jsonRequest(
            'POST',
            $config['base_url'] . '/models/' . rawurlencode($config['image_model']) . ':generateContent',
            $payload,
            ['x-goog-api-key: ' . $config['api_key']],
            120
        );
        if (!$response['success']) {
            return $response;
        }

        foreach ((array) ($response['data']['candidates'][0]['content']['parts'] ?? []) as $part) {
            $inline = is_array($part) ? ($part['inlineData'] ?? $part['inline_data'] ?? null) : null;
            if (!is_array($inline) || trim((string) ($inline['data'] ?? '')) === '') {
                continue;
            }
            $body = base64_decode((string) $inline['data'], true);
            if (!is_string($body) || $body === '') {
                continue;
            }
            return [
                'success' => true,
                'body' => $body,
                'mime' => (string) ($inline['mimeType'] ?? $inline['mime_type'] ?? 'image/png'),
                'model' => $config['image_model'],
            ];
        }

        return $this->failure('Gemini respondió, pero no devolvió una imagen.');
    }

    /** @return array<string, mixed> */
    public function startVideo(string $prompt): array
    {
        $config = $this->geminiConfig();
        if ($config['api_key'] === '') {
            return $this->failure('Gemini no está configurado para generar videos.');
        }

        $response = $this->jsonRequest(
            'POST',
            $config['base_url'] . '/models/' . rawurlencode($config['video_model']) . ':predictLongRunning',
            [
                'instances' => [['prompt' => $prompt]],
                'parameters' => [
                    'aspectRatio' => '16:9',
                    'resolution' => '720p',
                    'durationSeconds' => 8,
                    'generateAudio' => true,
                    'sampleCount' => 1,
                ],
            ],
            ['x-goog-api-key: ' . $config['api_key']],
            60
        );
        if (!$response['success']) {
            return $response;
        }

        $operation = trim((string) ($response['data']['name'] ?? ''));
        if ($operation === '') {
            return $this->failure('Gemini no devolvió el identificador del video.');
        }
        return [
            'success' => true,
            'operation' => $operation,
            'model' => $config['video_model'],
        ];
    }

    /** @return array<string, mixed> */
    public function pollVideo(string $operation): array
    {
        $config = $this->geminiConfig();
        $operation = ltrim(trim($operation), '/');
        if ($config['api_key'] === '' || $operation === '') {
            return $this->failure('No se puede consultar el estado del video.');
        }

        $response = $this->jsonRequest(
            'GET',
            $config['base_url'] . '/' . $operation,
            null,
            ['x-goog-api-key: ' . $config['api_key']],
            30
        );
        if (!$response['success']) {
            return $response;
        }
        if (empty($response['data']['done'])) {
            return ['success' => true, 'done' => false];
        }
        if (isset($response['data']['error'])) {
            return $this->failure(
                'Gemini no pudo completar el video: ' .
                trim((string) ($response['data']['error']['message'] ?? 'error desconocido'))
            );
        }

        $video = $this->findVideoResult((array) ($response['data']['response'] ?? []));
        if (isset($video['body']) && is_string($video['body']) && $video['body'] !== '') {
            return [
                'success' => true,
                'done' => true,
                'body' => $video['body'],
                'mime' => (string) ($video['mime'] ?? 'video/mp4'),
            ];
        }
        $uri = trim((string) ($video['uri'] ?? ''));
        if ($uri === '') {
            return $this->failure('Gemini terminó el proceso, pero no entregó el archivo de video.');
        }
        $download = $this->binaryRequest($uri, ['x-goog-api-key: ' . $config['api_key']], 120);
        if (!$download['success']) {
            return $download;
        }
        return [
            'success' => true,
            'done' => true,
            'body' => $download['body'],
            'mime' => $download['mime'] !== '' ? $download['mime'] : 'video/mp4',
        ];
    }

    /** @return array<string, mixed> */
    public function generateMusic(string $prompt): array
    {
        $config = $this->vertexConfig();
        if ($config['project_id'] === '' || $config['access_token'] === '') {
            return $this->failure(
                'La generación musical requiere Vertex AI. Configura GOOGLE_APPLICATION_CREDENTIALS ' .
                'con una cuenta de servicio autorizada, o una identidad de Google Cloud con acceso a Vertex AI. ' .
                'La API key de Gemini por sí sola no habilita Lyria.'
            );
        }

        $url = 'https://' . $config['location'] . '-aiplatform.googleapis.com/v1/projects/' .
            rawurlencode($config['project_id']) . '/locations/' . rawurlencode($config['location']) .
            '/publishers/google/models/' . rawurlencode($config['music_model']) . ':predict';
        $response = $this->jsonRequest(
            'POST',
            $url,
            ['instances' => [['prompt' => $prompt]]],
            ['Authorization: Bearer ' . $config['access_token']],
            120
        );
        if (!$response['success']) {
            return $response;
        }

        $audio = $this->findAudioResult((array) ($response['data']['predictions'] ?? []));
        if ($audio === null) {
            return $this->failure('Lyria respondió, pero no devolvió una pista de audio.');
        }
        return [
            'success' => true,
            'body' => $audio,
            'mime' => 'audio/wav',
            'model' => $config['music_model'],
        ];
    }

    /** @return array<string, mixed> */
    private function findVideoResult(array $node): array
    {
        foreach ($node as $key => $value) {
            if (is_string($value) && in_array((string) $key, ['uri', 'videoUri'], true)) {
                return ['uri' => $value];
            }
            if (is_string($value) && in_array((string) $key, ['data', 'bytesBase64Encoded'], true)) {
                $decoded = base64_decode($value, true);
                if (is_string($decoded) && $decoded !== '') {
                    return ['body' => $decoded, 'mime' => 'video/mp4'];
                }
            }
            if (is_array($value)) {
                $found = $this->findVideoResult($value);
                if ($found !== []) {
                    return $found;
                }
            }
        }
        return [];
    }

    /** @param array<mixed> $node */
    private function findAudioResult(array $node): ?string
    {
        foreach ($node as $key => $value) {
            if (is_string($value) && in_array((string) $key, ['audioContent', 'bytesBase64Encoded', 'data'], true)) {
                $decoded = base64_decode($value, true);
                if (is_string($decoded) && $decoded !== '') {
                    return $decoded;
                }
            }
            if (is_array($value)) {
                $found = $this->findAudioResult($value);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        return null;
    }

    /** @param array<string, mixed>|null $payload @param list<string> $headers */
    private function jsonRequest(string $method, string $url, ?array $payload, array $headers, int $timeout): array
    {
        if (!function_exists('curl_init')) {
            return $this->failure('La extensión cURL de PHP no está disponible.');
        }
        $curl = curl_init($url);
        $httpHeaders = array_merge(['Accept: application/json'], $headers);
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $httpHeaders,
            CURLOPT_SSL_VERIFYPEER => defined('OPENAI_SSL_VERIFY') ? (bool) OPENAI_SSL_VERIFY : true,
        ];
        if ($payload !== null) {
            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (!is_string($encoded)) {
                return $this->failure('No se pudo preparar la solicitud multimedia.');
            }
            $options[CURLOPT_POSTFIELDS] = $encoded;
            $httpHeaders[] = 'Content-Type: application/json';
            $options[CURLOPT_HTTPHEADER] = $httpHeaders;
        }
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = (string) curl_error($curl);
        curl_close($curl);
        if (!is_string($body)) {
            return $this->failure('No se pudo conectar con Gemini. ' . $error);
        }
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return $this->failure('El proveedor devolvió una respuesta no válida.');
        }
        if ($status < 200 || $status >= 300 || isset($data['error'])) {
            $message = trim((string) ($data['error']['message'] ?? 'HTTP ' . $status));
            return $this->failure('Gemini: ' . $message);
        }
        return ['success' => true, 'data' => $data];
    }

    /** @param list<string> $headers @return array<string, mixed> */
    private function binaryRequest(string $url, array $headers, int $timeout): array
    {
        if (!$this->isAllowedDownloadUrl($url)) {
            return $this->failure('El proveedor devolvio una ubicacion de descarga no permitida.');
        }

        $curl = curl_init($url);
        if ($curl === false) {
            return $this->failure('No se pudo iniciar la descarga del archivo generado.');
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => defined('OPENAI_SSL_VERIFY') ? (bool) OPENAI_SSL_VERIFY : true,
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $mime = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $error = (string) curl_error($curl);
        curl_close($curl);
        if (!is_string($body) || $status < 200 || $status >= 300) {
            return $this->failure('No se pudo descargar el archivo generado. ' . $error);
        }
        return ['success' => true, 'body' => $body, 'mime' => $mime];
    }

    private function isAllowedDownloadUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https') {
            return false;
        }

        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
        foreach (['googleapis.com', 'googleusercontent.com'] as $allowedHost) {
            if ($host === $allowedHost || str_ends_with($host, '.' . $allowedHost)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, string> */
    private function geminiConfig(): array
    {
        $env = $this->readEnvFile(dirname(__DIR__) . '/API/.env');
        return [
            'api_key' => $this->value('GEMINI_API_KEY', $env),
            'base_url' => rtrim($this->value('GEMINI_BASE_URL', $env) ?: 'https://generativelanguage.googleapis.com/v1beta', '/'),
            'text_model' => $this->value('LEONIDAS_TEXT_MODEL', $env)
                ?: ($this->value('GEMINI_MODEL', $env) ?: 'gemini-2.5-flash'),
            'image_model' => $this->value('LEONIDAS_IMAGE_MODEL', $env) ?: 'gemini-3.1-flash-image',
            'video_model' => $this->value('LEONIDAS_VIDEO_MODEL', $env) ?: 'veo-3.1-generate-preview',
        ];
    }

    /** @return array<string, string> */
    private function vertexConfig(): array
    {
        $env = $this->readEnvFile(dirname(__DIR__) . '/API/.env');
        $credentials = $this->loadServiceAccount($this->value('GOOGLE_APPLICATION_CREDENTIALS', $env));
        $projectId = $this->value('VERTEX_PROJECT_ID', $env);
        if ($projectId === '' && is_array($credentials)) {
            $projectId = trim((string) ($credentials['project_id'] ?? ''));
        }
        $accessToken = $this->value('VERTEX_ACCESS_TOKEN', $env);
        if ($accessToken === '' && is_array($credentials)) {
            $accessToken = $this->serviceAccountAccessToken($credentials);
        }
        if ($accessToken === '') {
            $accessToken = $this->metadataAccessToken();
        }
        if ($projectId === '') {
            $projectId = $this->metadataProjectId();
        }
        return [
            'project_id' => $projectId,
            'location' => $this->value('VERTEX_LOCATION', $env) ?: 'us-central1',
            'access_token' => $accessToken,
            'music_model' => $this->value('LEONIDAS_MUSIC_MODEL', $env) ?: 'lyria-002',
        ];
    }

    /** @return array<string, mixed>|null */
    private function loadServiceAccount(string $configuredPath): ?array
    {
        if ($configuredPath === '') {
            return null;
        }
        $path = $configuredPath;
        if (!$this->isAbsolutePath($path)) {
            $path = dirname(__DIR__) . '/API/' . ltrim(str_replace('\\', '/', $path), '/');
        }
        if (!is_readable($path)) {
            return null;
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) && ($decoded['type'] ?? '') === 'service_account' ? $decoded : null;
    }

    /** @param array<string, mixed> $credentials */
    private function serviceAccountAccessToken(array $credentials): string
    {
        $cachePath = dirname(__DIR__) . '/storage/.vertex-ai-token.json';
        $cached = is_readable($cachePath)
            ? json_decode((string) file_get_contents($cachePath), true)
            : null;
        if (
            is_array($cached)
            && trim((string) ($cached['access_token'] ?? '')) !== ''
            && (int) ($cached['expires_at'] ?? 0) > time() + 120
        ) {
            return trim((string) $cached['access_token']);
        }

        $email = trim((string) ($credentials['client_email'] ?? ''));
        $privateKey = (string) ($credentials['private_key'] ?? '');
        $tokenUri = trim((string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token'));
        if (
            $email === ''
            || $privateKey === ''
            || !in_array($tokenUri, ['https://oauth2.googleapis.com/token', 'https://www.googleapis.com/oauth2/v4/token'], true)
        ) {
            return '';
        }

        $issuedAt = time();
        $header = $this->base64Url((string) json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $this->base64Url((string) json_encode([
            'iss' => $email,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => $tokenUri,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ]));
        $unsigned = $header . '.' . $claims;
        $signature = '';
        if (!openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return '';
        }

        $response = $this->formRequest($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $unsigned . '.' . $this->base64Url($signature),
        ]);
        $token = trim((string) ($response['access_token'] ?? ''));
        if ($token === '') {
            return '';
        }

        $directory = dirname($cachePath);
        if (!is_dir($directory)) {
            @mkdir($directory, 0770, true);
        }
        @file_put_contents($cachePath, json_encode([
            'access_token' => $token,
            'expires_at' => $issuedAt + max(300, (int) ($response['expires_in'] ?? 3600)),
        ], JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($cachePath, 0600);
        return $token;
    }

    /** @param array<string, string> $fields @return array<string, mixed> */
    private function formRequest(string $url, array $fields): array
    {
        if (!function_exists('curl_init')) {
            return [];
        }
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_SSL_VERIFYPEER => defined('OPENAI_SSL_VERIFY') ? (bool) OPENAI_SSL_VERIFY : true,
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $decoded = is_string($body) ? json_decode($body, true) : null;
        return $status >= 200 && $status < 300 && is_array($decoded) ? $decoded : [];
    }

    private function metadataAccessToken(): string
    {
        $data = $this->metadataRequest('/instance/service-accounts/default/token');
        return trim((string) ($data['access_token'] ?? ''));
    }

    private function metadataProjectId(): string
    {
        $data = $this->metadataRequest('/project/project-id', false);
        return trim((string) ($data['value'] ?? ''));
    }

    /** @return array<string, mixed> */
    private function metadataRequest(string $path, bool $json = true): array
    {
        if (!function_exists('curl_init')) {
            return [];
        }
        $curl = curl_init('http://metadata.google.internal/computeMetadata/v1' . $path);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => 350,
            CURLOPT_TIMEOUT_MS => 800,
            CURLOPT_HTTPHEADER => ['Metadata-Flavor: Google'],
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!is_string($body) || $status < 200 || $status >= 300) {
            return [];
        }
        if (!$json) {
            return ['value' => $body];
        }
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function isAbsolutePath(string $path): bool
    {
        return (bool) preg_match('/^(?:[A-Za-z]:[\\\\\\/]|[\\\\\\/]{2}|\\/)/', $path);
    }

    /** @param array<string, string> $env */
    private function value(string $name, array $env): string
    {
        $value = getenv($name);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }
        return trim((string) ($env[$name] ?? ''));
    }

    /** @return array<string, string> */
    private function readEnvFile(string $path): array
    {
        if (!is_readable($path)) {
            return [];
        }
        $env = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (preg_match('/^\s*([A-Z_][A-Z0-9_]*)\s*=\s*(.*)\s*$/', $line, $matches)) {
                $env[$matches[1]] = trim($matches[2], " \t\n\r\0\x0B\"'");
            }
        }
        return $env;
    }

    /** @return array<string, mixed> */
    private function failure(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
