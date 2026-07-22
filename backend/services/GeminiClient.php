<?php

namespace Services;

/**
 * Shared Google Gemini transport for Sparta server-side services.
 * Credentials stay on the server and are never included in responses or logs.
 */
class GeminiClient
{
    /**
     * @param string|array<int, array<string, mixed>> $parts
     * @return array<string, mixed>
     */
    public function generate(
        string $system,
        $parts,
        int $maxTokens = 1500,
        bool $jsonResponse = false,
        float $temperature = 0.1,
        string $thinkingLevel = 'LOW'
    ): array {
        $config = $this->config();
        if ($config['api_key'] === '' || $config['base_url'] === '' || $config['model'] === '') {
            return $this->failure('Gemini no esta configurado. Agregue GEMINI_API_KEY y GEMINI_MODEL en backend/API/.env.');
        }
        if (!function_exists('curl_init')) {
            return $this->failure('La extension cURL de PHP no esta disponible.');
        }

        $contentParts = $this->normalizeParts($parts);
        if ($contentParts === []) {
            return $this->failure('La solicitud a Gemini no contiene texto ni archivos utilizables.');
        }

        $models = array_values(array_unique(array_filter(array_merge(
            [$config['model']],
            $config['fallback_models']
        ))));
        $lastMessage = 'Gemini no pudo responder en este momento.';
        foreach ($models as $modelIndex => $model) {
            $payload = $this->buildPayload(
                $system,
                $contentParts,
                $model,
                $maxTokens,
                $jsonResponse,
                $temperature,
                $thinkingLevel
            );
            if ($payload === '') {
                return $this->failure('No se pudo preparar la solicitud a Gemini.');
            }
            $url = $config['base_url'] . '/models/' . rawurlencode($model) . ':generateContent';
            $lastStatus = 0;
            foreach ($config['retry_delays'] as $delaySeconds) {
                if ($delaySeconds > 0) {
                    usleep((int) round($delaySeconds * 1000000));
                }
                $result = $this->request($url, $config['api_key'], $payload, $config['timeout']);
                if ($result['success']) {
                    $result['modelo'] = $model;
                    $result['fallback_used'] = $modelIndex > 0;
                    return $result;
                }
                $lastMessage = (string) ($result['mensaje'] ?? $lastMessage);
                $status = (int) ($result['http_code'] ?? 0);
                $lastStatus = $status;
                if (!in_array($status, [0, 408, 409, 429, 500, 502, 503, 504], true)) {
                    break;
                }
            }
            if (!$this->shouldTryFallback($lastStatus, $lastMessage)) {
                break;
            }
        }

        return $this->failure($lastMessage);
    }

    /**
     * @param array<int, array<string, mixed>> $contentParts
     */
    private function buildPayload(
        string $system,
        array $contentParts,
        string $model,
        int $maxTokens,
        bool $jsonResponse,
        float $temperature,
        string $thinkingLevel
    ): string {
        $generationConfig = [
            'maxOutputTokens' => max(100, min($maxTokens, 8192)),
        ];
        // Gemini 3.5+ rechazo/depreco los controles de muestreo clasicos.
        // Se conservan solo para modelos anteriores que todavia los aceptan.
        if ($this->supportsLegacySamplingControls($model)) {
            $generationConfig['temperature'] = max(0.0, min($temperature, 1.0));
        } else {
            $generationConfig['thinkingConfig'] = [
                'thinkingLevel' => $this->normalizeThinkingLevel($thinkingLevel),
            ];
        }
        if ($jsonResponse) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        $payload = json_encode([
            'system_instruction' => ['parts' => [['text' => $system]]],
            'contents' => [[
                'role' => 'user',
                'parts' => $contentParts,
            ]],
            'generationConfig' => $generationConfig,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($payload) ? $payload : '';
    }

    private function supportsLegacySamplingControls(string $model): bool
    {
        if (!preg_match('/gemini-(\d+)\.(\d+)/i', $model, $matches)) {
            return true;
        }
        $version = ((int) $matches[1] * 100) + (int) $matches[2];
        return $version < 305;
    }

    private function normalizeThinkingLevel(string $level): string
    {
        $normalized = strtoupper(trim($level));
        return in_array($normalized, ['LOW', 'MEDIUM', 'HIGH'], true) ? $normalized : 'LOW';
    }

    private function shouldTryFallback(int $status, string $message): bool
    {
        if (in_array($status, [0, 404, 408, 409, 429, 500, 502, 503, 504], true)) {
            return true;
        }
        if ($status !== 400) {
            return false;
        }
        $normalized = strtolower($message);
        return str_contains($normalized, 'model')
            && (str_contains($normalized, 'not found')
                || str_contains($normalized, 'not supported')
                || str_contains($normalized, 'unavailable'));
    }

    /** @return array<string, mixed> */
    private function request(string $url, string $apiKey, string $payload, int $timeout): array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'x-goog-api-key: ' . $apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => defined('OPENAI_SSL_VERIFY') ? (bool) OPENAI_SSL_VERIFY : true,
        ]);
        $body = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = (string) curl_error($curl);
        curl_close($curl);

        if (!is_string($body)) {
            return $this->failure('No se pudo conectar con Gemini.' . ($curlError !== '' ? ' ' . $curlError : ''), $httpCode);
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return $this->failure('Gemini devolvio una respuesta no valida.', $httpCode);
        }
        if ($httpCode < 200 || $httpCode >= 300 || isset($decoded['error'])) {
            $providerMessage = trim((string) ($decoded['error']['message'] ?? ''));
            return $this->failure(
                $providerMessage !== '' ? 'Gemini: ' . $providerMessage : 'Gemini respondio con HTTP ' . $httpCode . '.',
                $httpCode
            );
        }

        $texts = [];
        foreach ((array) ($decoded['candidates'][0]['content']['parts'] ?? []) as $part) {
            if (is_array($part) && isset($part['text'])) {
                $texts[] = (string) $part['text'];
            }
        }
        $text = trim(implode('', $texts));
        if ($text === '') {
            $blockReason = trim((string) ($decoded['promptFeedback']['blockReason'] ?? ''));
            return $this->failure(
                $blockReason !== '' ? 'Gemini bloqueo la respuesta: ' . $blockReason : 'Gemini no devolvio contenido.',
                $httpCode
            );
        }

        return [
            'success' => true,
            'texto' => $text,
            'mensaje' => 'OK',
            'uso' => is_array($decoded['usageMetadata'] ?? null) ? $decoded['usageMetadata'] : [],
            'http_code' => $httpCode,
        ];
    }

    /**
     * @param string|array<int, array<string, mixed>> $parts
     * @return array<int, array<string, mixed>>
     */
    private function normalizeParts($parts): array
    {
        if (is_string($parts)) {
            $parts = [['text' => $parts]];
        }
        if (!is_array($parts)) {
            return [];
        }

        $normalized = [];
        foreach ($parts as $part) {
            if (!is_array($part)) {
                continue;
            }
            if (isset($part['text']) && trim((string) $part['text']) !== '') {
                $normalized[] = ['text' => (string) $part['text']];
                continue;
            }
            $inline = $part['inline_data'] ?? $part['inlineData'] ?? null;
            if (!is_array($inline)) {
                continue;
            }
            $mime = trim((string) ($inline['mime_type'] ?? $inline['mimeType'] ?? 'application/octet-stream'));
            $data = trim((string) ($inline['data'] ?? ''));
            if ($data === '') {
                continue;
            }
            $normalized[] = [
                'inline_data' => [
                    'mime_type' => $mime,
                    'data' => $data,
                ],
            ];
        }
        return $normalized;
    }

    /** @return array{api_key:string,base_url:string,model:string,fallback_models:array<int,string>,retry_delays:array<int,float>,timeout:int} */
    private function config(): array
    {
        $variables = $this->readEnvFile(dirname(__DIR__) . '/API/.env');
        $apiKey = $this->firstValue('GEMINI_API_KEY', $variables);
        $baseUrl = $this->firstValue('GEMINI_BASE_URL', $variables);
        $model = $this->firstValue('GEMINI_MODEL', $variables);
        $fallbacks = $this->firstValue('GEMINI_FALLBACK_MODELS', $variables);
        $retryDelays = $this->firstValue('GEMINI_RETRY_DELAYS', $variables);
        $timeout = $this->firstValue('GEMINI_TIMEOUT_SECONDS', $variables);

        $delays = array_values(array_filter(
            array_map(static function ($value): float {
                return max(0.0, min(5.0, (float) trim((string) $value)));
            }, explode(',', $retryDelays !== '' ? $retryDelays : '0,1')),
            static function (float $value, int $index): bool {
                return $index === 0 || $value > 0;
            },
            ARRAY_FILTER_USE_BOTH
        ));
        if ($delays === []) {
            $delays = [0.0];
        }

        return [
            'api_key' => $apiKey,
            'base_url' => rtrim($baseUrl !== '' ? $baseUrl : 'https://generativelanguage.googleapis.com/v1beta', '/'),
            'model' => $model !== '' ? $model : 'gemini-3.6-flash',
            'fallback_models' => array_values(array_filter(array_map(
                'trim',
                explode(',', $fallbacks !== '' ? $fallbacks : 'gemini-3.5-flash-lite,gemini-3.1-flash-lite')
            ))),
            'retry_delays' => $delays,
            'timeout' => max(10, min(120, $timeout !== '' ? (int) $timeout : 45)),
        ];
    }

    /** @param array<string, string> $variables */
    private function firstValue(string $name, array $variables): string
    {
        $environment = getenv($name);
        if (is_string($environment) && trim($environment) !== '') {
            return trim($environment);
        }
        if (isset($variables[$name]) && trim($variables[$name]) !== '') {
            return trim($variables[$name]);
        }
        if (defined($name)) {
            return trim((string) constant($name));
        }
        return '';
    }

    /** @return array<string, string> */
    private function readEnvFile(string $path): array
    {
        if (!is_readable($path)) {
            return [];
        }
        $variables = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (!preg_match('/^\s*([A-Z_][A-Z0-9_]*)\s*=\s*(.*)\s*$/', $line, $matches)) {
                continue;
            }
            $variables[$matches[1]] = trim($matches[2], " \t\n\r\0\x0B\"'");
        }
        return $variables;
    }

    /** @return array<string, mixed> */
    private function failure(string $message, int $httpCode = 0): array
    {
        return [
            'success' => false,
            'texto' => '',
            'mensaje' => $message,
            'modelo' => '',
            'uso' => [],
            'http_code' => $httpCode,
        ];
    }
}
