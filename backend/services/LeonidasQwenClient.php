<?php

namespace Services;

/**
 * Minimal Qwen client for server-side planning. Provider credentials never
 * leave PHP and responses are accepted only when they contain valid JSON.
 */
class LeonidasQwenClient
{
    /** @return array<string, mixed>|null */
    public function json(string $system, string $prompt, int $maxTokens = 900): ?array
    {
        $config = $this->config();
        if ($config['api_key'] === '' || $config['base_url'] === '' || !function_exists('curl_init')) {
            return null;
        }

        $payload = json_encode([
            'model' => $config['model'],
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.05,
            'max_tokens' => max(200, min($maxTokens, 1800)),
            'response_format' => ['type' => 'json_object'],
        ], JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return null;
        }

        $curl = curl_init($config['base_url'] . '/chat/completions');
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['api_key'],
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 25,
        ]);
        $body = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = (string) curl_error($curl);
        curl_close($curl);

        if (!is_string($body) || $httpCode < 200 || $httpCode >= 300) {
            error_log('[Leonidas] Qwen planner unavailable. HTTP=' . $httpCode . ' error=' . $curlError);
            return null;
        }

        $providerResponse = json_decode($body, true);
        $content = is_array($providerResponse)
            ? trim((string) ($providerResponse['choices'][0]['message']['content'] ?? ''))
            : '';
        if ($content === '') {
            return null;
        }

        $content = trim(preg_replace('/^```(?:json)?|```$/mi', '', $content) ?? $content);
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            error_log('[Leonidas] Qwen planner returned invalid JSON.');
            return null;
        }

        $decoded['_modelo'] = $config['model'];
        return $decoded;
    }

    /** @return array{api_key:string,base_url:string,model:string} */
    private function config(): array
    {
        $variables = [];
        $path = dirname(__DIR__) . '/API/.env';
        if (is_readable($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (!preg_match('/^\s*([A-Z_][A-Z0-9_]*)\s*=\s*(.*)\s*$/', $line, $matches)) {
                    continue;
                }
                if (!in_array($matches[1], ['ALIBABA_API_KEY', 'ALIBABA_BASE_URL', 'ALIBABA_MODEL'], true)) {
                    continue;
                }
                $variables[$matches[1]] = trim($matches[2], " \t\n\r\0\x0B\"'");
            }
        }

        return [
            'api_key' => trim((string) ($variables['ALIBABA_API_KEY'] ?? '')),
            'base_url' => rtrim(trim((string) ($variables['ALIBABA_BASE_URL'] ?? '')), '/'),
            'model' => trim((string) ($variables['ALIBABA_MODEL'] ?? 'qwen3.5-flash')),
        ];
    }
}
