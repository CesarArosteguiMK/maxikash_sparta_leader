<?php

namespace Services;

class LeonidasTtsService
{
    private const MAX_TEXT_LENGTH = 580;
    private const MAX_REQUESTS_PER_MINUTE = 12;
    private const MAX_AUDIO_BYTES = 6_291_456;

    /** @return array{audio_url:string,voice:string,model:string,cached:bool} */
    public function sintetizar(string $text): array
    {
        $text = $this->normalizeText($text);
        if ($text === '') {
            throw new \InvalidArgumentException('No hay texto para reproducir.');
        }

        $config = $this->config();
        if ($config['api_key'] === '' || $config['endpoint'] === '' || !function_exists('curl_init')) {
            throw new \RuntimeException('La voz de Leonidas no esta configurada en el servidor.');
        }

        $this->purgeCache();
        $hash = hash('sha256', $config['model'] . '|' . $config['voice'] . '|' . $text);
        $cachedToken = (string) ($_SESSION['leonidas_tts_hashes'][$hash] ?? '');
        $cached = $cachedToken !== '' ? ($_SESSION['leonidas_tts_audio'][$cachedToken] ?? null) : null;
        if (is_array($cached) && (int) ($cached['expires_at'] ?? 0) > time() + 60) {
            return $this->response($cachedToken, $config, true);
        }

        $this->assertRateLimit();
        $payload = json_encode([
            'model' => $config['model'],
            'input' => [
                'text' => $text,
                'voice' => $config['voice'],
                'language_type' => $config['language'],
                'instructions' => $config['instructions'],
                'optimize_instructions' => true,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            throw new \RuntimeException('No se pudo preparar el texto para la voz.');
        }

        // La sintesis puede tardar varios segundos. Liberamos el candado de la
        // sesion para no bloquear el chat, el buzon ni otras peticiones Sparta.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $curl = curl_init($config['endpoint']);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $config['api_key'],
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 45,
        ]);
        $body = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = (string) curl_error($curl);
        curl_close($curl);

        $provider = is_string($body) ? json_decode($body, true) : null;
        $audioUrl = is_array($provider) ? trim((string) ($provider['output']['audio']['url'] ?? '')) : '';
        if ($httpCode < 200 || $httpCode >= 300 || $audioUrl === '') {
            $providerMessage = is_array($provider)
                ? trim((string) ($provider['message'] ?? $provider['code'] ?? ''))
                : '';
            error_log('[Leonidas] TTS unavailable. HTTP=' . $httpCode . ' error=' . $curlError . ' provider=' . $providerMessage);
            throw new \RuntimeException('La voz de Leonidas no esta disponible en este momento.');
        }

        $this->assertProviderAudioUrl($audioUrl);
        if (session_status() !== PHP_SESSION_ACTIVE && session_id() !== '') {
            session_start();
        }
        $expiresAt = (int) ($provider['output']['audio']['expires_at'] ?? 0);
        if ($expiresAt <= time() + 60) {
            $expiresAt = time() + 3600;
        }

        $token = bin2hex(random_bytes(24));
        $_SESSION['leonidas_tts_audio'][$token] = [
            'url' => $audioUrl,
            'expires_at' => min($expiresAt, time() + 86400),
            'hash' => $hash,
        ];
        $_SESSION['leonidas_tts_hashes'][$hash] = $token;
        $this->trimCache();

        return $this->response($token, $config, false);
    }

    /** @return array{body:string,mime:string} */
    public function obtenerAudio(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{48}$/', $token)) {
            throw new \InvalidArgumentException('El identificador de audio no es valido.');
        }

        $this->purgeCache();
        $entry = $_SESSION['leonidas_tts_audio'][$token] ?? null;
        if (!is_array($entry) || (int) ($entry['expires_at'] ?? 0) <= time()) {
            throw new \RuntimeException('El audio expiro. Solicita nuevamente la respuesta.');
        }

        $url = trim((string) ($entry['url'] ?? ''));
        $this->assertProviderAudioUrl($url);
        // La descarga del WAV tampoco debe mantener bloqueada la sesion.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Accept: audio/*'],
        ]);
        $body = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $mime = trim((string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE));
        $curlError = (string) curl_error($curl);
        curl_close($curl);

        if (!is_string($body) || $body === '' || $httpCode < 200 || $httpCode >= 300) {
            error_log('[Leonidas] TTS audio download failed. HTTP=' . $httpCode . ' error=' . $curlError);
            throw new \RuntimeException('No se pudo descargar el audio de Leonidas.');
        }
        if (strlen($body) > self::MAX_AUDIO_BYTES) {
            throw new \RuntimeException('El audio generado excede el limite permitido.');
        }
        if (!str_starts_with(strtolower($mime), 'audio/')) {
            $mime = 'audio/wav';
        }

        return ['body' => $body, 'mime' => $mime];
    }

    private function normalizeText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/https?:\/\/\S+/iu', '', $text) ?? $text;
        $text = preg_replace('/[`*_#>|~]+/u', ' ', $text) ?? $text;
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if (mb_strlen($text, 'UTF-8') > self::MAX_TEXT_LENGTH) {
            $text = rtrim(mb_substr($text, 0, self::MAX_TEXT_LENGTH - 3, 'UTF-8')) . '...';
        }
        return $text;
    }

    private function assertRateLimit(): void
    {
        $now = time();
        $requests = array_values(array_filter(
            is_array($_SESSION['leonidas_tts_requests'] ?? null) ? $_SESSION['leonidas_tts_requests'] : [],
            static fn ($timestamp): bool => (int) $timestamp > $now - 60
        ));
        if (count($requests) >= self::MAX_REQUESTS_PER_MINUTE) {
            throw new \RuntimeException('Leonidas alcanzo el limite temporal de voz. Espera un momento.');
        }
        $requests[] = $now;
        $_SESSION['leonidas_tts_requests'] = $requests;
    }

    private function purgeCache(): void
    {
        $entries = is_array($_SESSION['leonidas_tts_audio'] ?? null) ? $_SESSION['leonidas_tts_audio'] : [];
        $hashes = is_array($_SESSION['leonidas_tts_hashes'] ?? null) ? $_SESSION['leonidas_tts_hashes'] : [];
        foreach ($entries as $token => $entry) {
            if (!is_array($entry) || (int) ($entry['expires_at'] ?? 0) <= time()) {
                unset($entries[$token]);
                if (is_array($entry) && isset($entry['hash'])) {
                    unset($hashes[(string) $entry['hash']]);
                }
            }
        }
        $_SESSION['leonidas_tts_audio'] = $entries;
        $_SESSION['leonidas_tts_hashes'] = $hashes;
    }

    private function trimCache(): void
    {
        $entries = $_SESSION['leonidas_tts_audio'] ?? [];
        if (!is_array($entries) || count($entries) <= 16) {
            return;
        }
        while (count($entries) > 16) {
            $token = array_key_first($entries);
            $entry = $entries[$token] ?? null;
            unset($entries[$token]);
            if (is_array($entry) && isset($entry['hash'])) {
                unset($_SESSION['leonidas_tts_hashes'][(string) $entry['hash']]);
            }
        }
        $_SESSION['leonidas_tts_audio'] = $entries;
    }

    private function assertProviderAudioUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)
            || ($host !== 'aliyuncs.com' && !str_ends_with($host, '.aliyuncs.com'))) {
            throw new \RuntimeException('El proveedor devolvio una ubicacion de audio no permitida.');
        }
    }

    /** @param array<string, string> $config
     *  @return array{audio_url:string,voice:string,model:string,cached:bool}
     */
    private function response(string $token, array $config, bool $cached): array
    {
        return [
            'audio_url' => '/Leonidas/audio?token=' . rawurlencode($token),
            'voice' => $config['voice'],
            'model' => $config['model'],
            'cached' => $cached,
        ];
    }

    /** @return array{api_key:string,endpoint:string,model:string,voice:string,language:string,instructions:string} */
    private function config(): array
    {
        $variables = [];
        $allowed = [
            'ALIBABA_API_KEY',
            'ALIBABA_BASE_URL',
            'ALIBABA_TTS_BASE_URL',
            'ALIBABA_TTS_MODEL',
            'ALIBABA_TTS_VOICE',
            'ALIBABA_TTS_LANGUAGE',
            'ALIBABA_TTS_INSTRUCTIONS',
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

        $endpoint = trim((string) ($variables['ALIBABA_TTS_BASE_URL'] ?? ''));
        if ($endpoint === '') {
            $base = rtrim(trim((string) ($variables['ALIBABA_BASE_URL'] ?? '')), '/');
            $base = preg_replace('#/compatible-mode/v1$#', '', $base) ?? $base;
            $endpoint = $base === '' ? '' : $base . '/api/v1/services/aigc/multimodal-generation/generation';
        }

        return [
            'api_key' => trim((string) ($variables['ALIBABA_API_KEY'] ?? '')),
            'endpoint' => $endpoint,
            'model' => trim((string) ($variables['ALIBABA_TTS_MODEL'] ?? 'qwen3-tts-instruct-flash')),
            'voice' => trim((string) ($variables['ALIBABA_TTS_VOICE'] ?? 'Vincent')),
            'language' => trim((string) ($variables['ALIBABA_TTS_LANGUAGE'] ?? 'Spanish')),
            'instructions' => trim((string) ($variables['ALIBABA_TTS_INSTRUCTIONS']
                ?? 'Speak in natural Latin American Spanish with a deep, warm adult male voice. Sound confident, approachable and human. Use subtle emotion, clear diction, conversational pacing and natural pauses. Avoid robotic cadence, shouting, exaggerated theater and an artificial announcer tone.')),
        ];
    }
}
