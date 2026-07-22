<?php

namespace Services;

class LeonidasTtsService
{
    private const MAX_TEXT_LENGTH = 580;
    private const MAX_REQUESTS_PER_MINUTE = 12;
    private const MAX_AUDIO_BYTES = 8_388_608;
    private const CACHE_TTL = 3600;

    /** @return array{audio_url:string,voice:string,model:string,cached:bool} */
    public function sintetizar(string $text): array
    {
        $text = $this->normalizeText($text);
        if ($text === '') {
            throw new \InvalidArgumentException('No hay texto para reproducir.');
        }

        $client = new GeminiTtsClient();
        $metadata = $client->metadata();
        $this->purgeCache();
        $hash = hash('sha256', 'gemini-tts-v1|' . $metadata['model'] . '|' . $metadata['voice'] . '|' . $text);
        $cachedToken = (string) ($_SESSION['leonidas_tts_hashes'][$hash] ?? '');
        $cached = $cachedToken !== '' ? ($_SESSION['leonidas_tts_audio'][$cachedToken] ?? null) : null;
        if (is_array($cached)
            && (int) ($cached['expires_at'] ?? 0) > time() + 60
            && is_file((string) ($cached['file'] ?? ''))) {
            return $this->response($cachedToken, $metadata, true);
        }

        $this->assertRateLimit();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $generated = $client->sintetizar($text);
        $wav = $client->pcmToWav($generated['pcm'], $generated['sample_rate']);
        if (strlen($wav) > self::MAX_AUDIO_BYTES) {
            throw new \RuntimeException('El audio generado excede el limite permitido.');
        }

        $directory = $this->cacheDirectory();
        $token = bin2hex(random_bytes(24));
        $file = $directory . DIRECTORY_SEPARATOR . $token . '.wav';
        if (file_put_contents($file, $wav, LOCK_EX) !== strlen($wav)) {
            @unlink($file);
            throw new \RuntimeException('No se pudo guardar temporalmente el audio de Leonidas.');
        }

        if (session_status() !== PHP_SESSION_ACTIVE && session_id() !== '') {
            session_start();
        }
        $_SESSION['leonidas_tts_audio'][$token] = [
            'file' => $file,
            'expires_at' => time() + self::CACHE_TTL,
            'hash' => $hash,
        ];
        $_SESSION['leonidas_tts_hashes'][$hash] = $token;
        $this->trimCache();

        return $this->response($token, $metadata, false);
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

        $file = (string) ($entry['file'] ?? '');
        $realFile = realpath($file);
        $realDirectory = realpath($this->cacheDirectory());
        if ($realFile === false || $realDirectory === false
            || !str_starts_with(strtolower($realFile), strtolower($realDirectory . DIRECTORY_SEPARATOR))) {
            throw new \RuntimeException('El audio temporal no esta disponible.');
        }

        $body = file_get_contents($realFile);
        if (!is_string($body) || $body === '') {
            throw new \RuntimeException('No se pudo leer el audio de Leonidas.');
        }
        if (strlen($body) > self::MAX_AUDIO_BYTES) {
            throw new \RuntimeException('El audio generado excede el limite permitido.');
        }
        return ['body' => $body, 'mime' => 'audio/wav'];
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

    private function cacheDirectory(): string
    {
        $directory = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'sparta_leonidas_tts';
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('No se pudo preparar el cache de voz de Leonidas.');
        }
        return $directory;
    }

    private function purgeCache(): void
    {
        $entries = is_array($_SESSION['leonidas_tts_audio'] ?? null) ? $_SESSION['leonidas_tts_audio'] : [];
        $hashes = is_array($_SESSION['leonidas_tts_hashes'] ?? null) ? $_SESSION['leonidas_tts_hashes'] : [];
        foreach ($entries as $token => $entry) {
            $expired = !is_array($entry) || (int) ($entry['expires_at'] ?? 0) <= time();
            $missing = is_array($entry) && !is_file((string) ($entry['file'] ?? ''));
            if ($expired || $missing) {
                if (is_array($entry)) {
                    $file = (string) ($entry['file'] ?? '');
                    if ($file !== '' && is_file($file)) {
                        @unlink($file);
                    }
                    if (isset($entry['hash'])) {
                        unset($hashes[(string) $entry['hash']]);
                    }
                }
                unset($entries[$token]);
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
            if (is_array($entry)) {
                $file = (string) ($entry['file'] ?? '');
                if ($file !== '' && is_file($file)) {
                    @unlink($file);
                }
                if (isset($entry['hash'])) {
                    unset($_SESSION['leonidas_tts_hashes'][(string) $entry['hash']]);
                }
            }
        }
        $_SESSION['leonidas_tts_audio'] = $entries;
    }

    /**
     * @param array{model:string,voice:string,sample_rate:int} $metadata
     * @return array{audio_url:string,voice:string,model:string,cached:bool}
     */
    private function response(string $token, array $metadata, bool $cached): array
    {
        return [
            'audio_url' => '/Leonidas/audio?token=' . rawurlencode($token),
            'voice' => $metadata['voice'],
            'model' => $metadata['model'],
            'cached' => $cached,
        ];
    }
}
