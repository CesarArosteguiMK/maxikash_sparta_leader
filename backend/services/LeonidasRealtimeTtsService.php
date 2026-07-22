<?php

namespace Services;

class LeonidasRealtimeTtsService
{
    private const MAX_TEXT_LENGTH = 580;
    private const MAX_REQUESTS_PER_MINUTE = 12;

    /**
     * Streams 16-bit little-endian mono PCM chunks as Gemini generates them.
     *
     * @param callable(string):void $onAudio receives a base64 PCM chunk
     * @return array{sample_rate:int,voice:string,model:string,chunks:int,first_audio_ms:int}
     */
    public function transmitir(string $text, callable $onAudio): array
    {
        $text = $this->normalizeText($text);
        if ($text === '') {
            throw new \InvalidArgumentException('No hay texto para reproducir.');
        }

        $this->assertRateLimit();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return (new GeminiTtsClient())->transmitir($text, $onAudio);
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
}
