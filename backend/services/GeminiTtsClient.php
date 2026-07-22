<?php

namespace Services;

class GeminiTtsClient
{
    private const DEFAULT_MODEL = 'gemini-3.1-flash-tts-preview';
    private const DEFAULT_VOICE = 'Algenib';
    private const DEFAULT_SAMPLE_RATE = 24000;
    private const MAX_RESPONSE_BYTES = 8_388_608;

    /** @return array{model:string,voice:string,sample_rate:int} */
    public function metadata(): array
    {
        $config = $this->config();
        return [
            'model' => $config['model'],
            'voice' => $config['voice'],
            'sample_rate' => self::DEFAULT_SAMPLE_RATE,
        ];
    }

    /** @return array{pcm:string,mime:string,sample_rate:int,model:string,voice:string} */
    public function sintetizar(string $text): array
    {
        $config = $this->config();
        $payload = $this->payload($text, $config);
        $lastError = 'Gemini no devolvio audio.';

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $curl = curl_init($this->endpoint($config, 'generateContent'));
            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'x-goog-api-key: ' . $config['api_key'],
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 6,
                CURLOPT_TIMEOUT => $config['timeout'],
                CURLOPT_MAXREDIRS => 0,
            ]);
            $body = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = (string) curl_error($curl);
            curl_close($curl);

            $decoded = is_string($body) ? json_decode($body, true) : null;
            $audio = is_array($decoded) ? $this->extractAudio($decoded) : null;
            if ($httpCode >= 200 && $httpCode < 300 && is_array($audio)) {
                $pcm = base64_decode($audio['data'], true);
                if (!is_string($pcm) || $pcm === '') {
                    throw new \RuntimeException('Gemini devolvio audio no valido.');
                }
                if (strlen($pcm) > self::MAX_RESPONSE_BYTES) {
                    throw new \RuntimeException('El audio generado excede el limite permitido.');
                }
                $sampleRate = $this->sampleRateFromMime($audio['mime']);
                return [
                    'pcm' => $pcm,
                    'mime' => $audio['mime'],
                    'sample_rate' => $sampleRate,
                    'model' => $config['model'],
                    'voice' => $config['voice'],
                ];
            }

            $lastError = $this->providerError($decoded, $httpCode, $curlError);
            if ($attempt < 2 && in_array($httpCode, [429, 500, 502, 503, 504], true)) {
                usleep(200000);
                continue;
            }
            break;
        }

        error_log('[Leonidas] Gemini TTS unavailable: ' . $lastError);
        throw new \RuntimeException('La voz de Leonidas no esta disponible en este momento.');
    }

    /**
     * Streams base64-encoded 16-bit little-endian mono PCM chunks.
     *
     * @param callable(string):void $onAudio
     * @return array{sample_rate:int,voice:string,model:string,chunks:int,first_audio_ms:int}
     */
    public function transmitir(string $text, callable $onAudio): array
    {
        $config = $this->config();
        $payload = $this->payload($text, $config);
        $startedAt = microtime(true);
        $chunks = 0;
        $firstAudioMs = 0;
        $sampleRate = self::DEFAULT_SAMPLE_RATE;
        $lastError = 'Gemini no devolvio audio en tiempo real.';

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $buffer = '';
            $providerBody = '';
            $callbackError = null;
            $streamFinished = false;

            $consumeLine = function (string $line) use (
                &$chunks,
                &$firstAudioMs,
                &$sampleRate,
                &$providerBody,
                &$callbackError,
                &$streamFinished,
                $startedAt,
                $onAudio
            ): void {
                $line = trim($line);
                if ($line === '') {
                    return;
                }
                if ($line === 'data: [DONE]') {
                    $streamFinished = true;
                    return;
                }
                if (!str_starts_with($line, 'data:')) {
                    $providerBody .= $line;
                    return;
                }

                $event = json_decode(ltrim(substr($line, 5)), true);
                if (!is_array($event)) {
                    return;
                }
                foreach (($event['candidates'] ?? []) as $candidate) {
                    if (is_array($candidate) && trim((string) ($candidate['finishReason'] ?? '')) !== '') {
                        $streamFinished = true;
                        break;
                    }
                }
                $audio = $this->extractAudio($event);
                if (!is_array($audio) || $audio['data'] === '') {
                    $providerBody .= json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
                    return;
                }

                try {
                    if ($chunks === 0) {
                        $firstAudioMs = (int) round((microtime(true) - $startedAt) * 1000);
                    }
                    $sampleRate = $this->sampleRateFromMime($audio['mime']);
                    $chunks++;
                    $onAudio($audio['data']);
                } catch (\Throwable $error) {
                    $callbackError = $error;
                }
            };

            $curl = curl_init($this->endpoint($config, 'streamGenerateContent') . '?alt=sse');
            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: text/event-stream',
                    'x-goog-api-key: ' . $config['api_key'],
                ],
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_CONNECTTIMEOUT => 6,
                CURLOPT_TIMEOUT => $config['timeout'],
                CURLOPT_MAXREDIRS => 0,
                CURLOPT_WRITEFUNCTION => static function ($curlHandle, string $incoming) use (
                    &$buffer,
                    $consumeLine,
                    &$callbackError,
                    &$streamFinished
                ): int {
                    $buffer .= str_replace("\r\n", "\n", $incoming);
                    while (($position = strpos($buffer, "\n")) !== false) {
                        $line = substr($buffer, 0, $position);
                        $buffer = substr($buffer, $position + 1);
                        $consumeLine($line);
                        if ($callbackError instanceof \Throwable || $streamFinished) {
                            return 0;
                        }
                    }
                    return strlen($incoming);
                },
            ]);

            $executed = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = (string) curl_error($curl);
            curl_close($curl);
            if ($buffer !== '') {
                $consumeLine($buffer);
            }
            if ($callbackError instanceof \Throwable) {
                throw $callbackError;
            }
            if (($executed !== false || $streamFinished)
                && $httpCode >= 200 && $httpCode < 300 && $chunks > 0) {
                return [
                    'sample_rate' => $sampleRate,
                    'voice' => $config['voice'],
                    'model' => $config['model'],
                    'chunks' => $chunks,
                    'first_audio_ms' => $firstAudioMs,
                ];
            }

            $decoded = $providerBody !== '' ? json_decode($providerBody, true) : null;
            $lastError = $this->providerError($decoded, $httpCode, $curlError);
            if ($chunks === 0 && $attempt < 2 && in_array($httpCode, [429, 500, 502, 503, 504], true)) {
                usleep(200000);
                continue;
            }
            break;
        }

        error_log('[Leonidas] Gemini realtime TTS unavailable: ' . $lastError);
        throw new \RuntimeException('La voz en tiempo real no esta disponible en este momento.');
    }

    public function pcmToWav(string $pcm, int $sampleRate = self::DEFAULT_SAMPLE_RATE): string
    {
        $dataSize = strlen($pcm);
        $byteRate = $sampleRate * 2;
        return 'RIFF'
            . pack('V', 36 + $dataSize)
            . 'WAVEfmt '
            . pack('VvvVVvv', 16, 1, 1, $sampleRate, $byteRate, 2, 16)
            . 'data'
            . pack('V', $dataSize)
            . $pcm;
    }

    /** @param array<string,mixed> $config */
    private function payload(string $text, array $config): string
    {
        $prompt = "SOLICITUD DE SINTESIS DE VOZ.\n"
            . "Direccion interpretativa: " . $config['instructions'] . "\n"
            . "Pronuncia exclusivamente el contenido situado despues de TRANSCRIPCION. "
            . "No leas estas instrucciones ni agregues comentarios.\n"
            . "TRANSCRIPCION:\n" . $text;

        $payload = json_encode([
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'responseModalities' => ['AUDIO'],
                'speechConfig' => [
                    'voiceConfig' => [
                        'prebuiltVoiceConfig' => [
                            'voiceName' => $config['voice'],
                        ],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            throw new \RuntimeException('No se pudo preparar el texto para la voz.');
        }
        return $payload;
    }

    /** @param array<string,mixed> $config */
    private function endpoint(array $config, string $method): string
    {
        return $config['base_url'] . '/models/' . rawurlencode($config['model']) . ':' . $method;
    }

    /** @param array<string,mixed> $response @return array{data:string,mime:string}|null */
    private function extractAudio(array $response): ?array
    {
        $candidates = $response['candidates'] ?? [];
        if (!is_array($candidates)) {
            return null;
        }
        foreach ($candidates as $candidate) {
            $parts = is_array($candidate) ? ($candidate['content']['parts'] ?? []) : [];
            if (!is_array($parts)) {
                continue;
            }
            foreach ($parts as $part) {
                if (!is_array($part)) {
                    continue;
                }
                $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
                if (!is_array($inline)) {
                    continue;
                }
                $data = trim((string) ($inline['data'] ?? ''));
                if ($data !== '') {
                    return [
                        'data' => $data,
                        'mime' => trim((string) ($inline['mimeType'] ?? $inline['mime_type'] ?? 'audio/l16; rate=24000; channels=1')),
                    ];
                }
            }
        }
        return null;
    }

    private function sampleRateFromMime(string $mime): int
    {
        return preg_match('/rate\s*=\s*(\d+)/i', $mime, $matches)
            ? max(8000, min(48000, (int) $matches[1]))
            : self::DEFAULT_SAMPLE_RATE;
    }

    /** @param array<string,mixed>|null $decoded */
    private function providerError(?array $decoded, int $httpCode, string $curlError): string
    {
        $message = is_array($decoded)
            ? trim((string) ($decoded['error']['message'] ?? $decoded['message'] ?? ''))
            : '';
        return 'HTTP=' . $httpCode . ' curl=' . $curlError . ' provider=' . $message;
    }

    /** @return array{api_key:string,base_url:string,model:string,voice:string,instructions:string,timeout:int} */
    private function config(): array
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('La extension cURL no esta disponible para la voz de Leonidas.');
        }

        $variables = [];
        $allowed = [
            'GEMINI_API_KEY',
            'GEMINI_BASE_URL',
            'GEMINI_TTS_MODEL',
            'GEMINI_TTS_VOICE',
            'GEMINI_TTS_INSTRUCTIONS',
            'GEMINI_TTS_TIMEOUT_SECONDS',
        ];
        $path = dirname(__DIR__) . '/API/.env';
        if (is_readable($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (!preg_match('/^\s*([A-Z_][A-Z0-9_]*)\s*=\s*(.*)\s*$/', $line, $matches)
                    || !in_array($matches[1], $allowed, true)) {
                    continue;
                }
                $variables[$matches[1]] = trim($matches[2], " \t\n\r\0\x0B\"'");
            }
        }

        $apiKey = trim((string) ($variables['GEMINI_API_KEY'] ?? ''));
        if ($apiKey === '') {
            throw new \RuntimeException('La voz de Leonidas no esta configurada en el servidor.');
        }

        $baseUrl = rtrim(trim((string) ($variables['GEMINI_BASE_URL'] ?? '')), '/');
        if ($baseUrl === '') {
            $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
        }
        if (!str_starts_with($baseUrl, 'https://generativelanguage.googleapis.com/')) {
            throw new \RuntimeException('La ubicacion configurada para Gemini TTS no esta permitida.');
        }

        return [
            'api_key' => $apiKey,
            'base_url' => $baseUrl,
            'model' => trim((string) ($variables['GEMINI_TTS_MODEL'] ?? self::DEFAULT_MODEL)) ?: self::DEFAULT_MODEL,
            'voice' => trim((string) ($variables['GEMINI_TTS_VOICE'] ?? self::DEFAULT_VOICE)) ?: self::DEFAULT_VOICE,
            'instructions' => trim((string) ($variables['GEMINI_TTS_INSTRUCTIONS']
                ?? 'Habla en espanol de Mexico con una voz masculina adulta, grave, firme, madura y calida. Suena como un comandante espartano sereno: seguro, directo y humano. Usa diccion clara, ritmo conversacional y pausas naturales. Evita sonar robotico, caricaturesco, teatral, agresivo o como locutor comercial.')),
            'timeout' => max(15, min(60, (int) ($variables['GEMINI_TTS_TIMEOUT_SECONDS'] ?? 45))),
        ];
    }
}
