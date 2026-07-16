<?php

namespace Services;

class LeonidasRealtimeTtsService
{
    private const MAX_TEXT_LENGTH = 580;
    private const MAX_REQUESTS_PER_MINUTE = 12;
    private const MAX_MESSAGE_BYTES = 2_097_152;

    /**
     * Streams 16-bit little-endian mono PCM chunks as they arrive.
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

        $config = $this->config();
        if ($config['api_key'] === '') {
            throw new \RuntimeException('La voz de Leonidas no esta configurada en el servidor.');
        }

        $this->assertRateLimit();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $startedAt = microtime(true);
        $socket = $this->openSocket($config);
        $chunks = 0;
        $firstAudioMs = 0;
        $inputSent = false;
        $finishSent = false;

        try {
            while (true) {
                $message = $this->readMessage($socket);
                if ($message === null) {
                    break;
                }

                $event = json_decode($message, true);
                if (!is_array($event)) {
                    continue;
                }

                $type = (string) ($event['type'] ?? '');
                if ($type === 'session.created') {
                    $session = [
                        'voice' => $config['voice'],
                        'mode' => 'commit',
                        'language_type' => $config['language'],
                        'response_format' => 'pcm',
                        'sample_rate' => $config['sample_rate'],
                    ];
                    if (str_contains(strtolower($config['model']), 'instruct')) {
                        $session['instructions'] = $config['instructions'];
                        $session['optimize_instructions'] = true;
                    }
                    $this->sendJson($socket, [
                        'event_id' => $this->eventId(),
                        'type' => 'session.update',
                        'session' => $session,
                    ]);
                    continue;
                }

                if ($type === 'session.updated' && !$inputSent) {
                    $this->sendJson($socket, [
                        'event_id' => $this->eventId(),
                        'type' => 'input_text_buffer.append',
                        'text' => $text,
                    ]);
                    $this->sendJson($socket, [
                        'event_id' => $this->eventId(),
                        'type' => 'input_text_buffer.commit',
                    ]);
                    $inputSent = true;
                    continue;
                }

                if ($type === 'response.audio.delta') {
                    $delta = trim((string) ($event['delta'] ?? ''));
                    if ($delta !== '') {
                        if ($chunks === 0) {
                            $firstAudioMs = (int) round((microtime(true) - $startedAt) * 1000);
                        }
                        $chunks++;
                        $onAudio($delta);
                    }
                    continue;
                }

                if (($type === 'response.audio.done' || $type === 'response.done') && !$finishSent) {
                    $this->sendJson($socket, [
                        'event_id' => $this->eventId(),
                        'type' => 'session.finish',
                    ]);
                    $finishSent = true;
                    continue;
                }

                if ($type === 'session.finished') {
                    break;
                }

                if ($type === 'error') {
                    $message = trim((string) ($event['error']['message'] ?? $event['message'] ?? ''));
                    error_log('[Leonidas] Realtime TTS provider error: ' . $message);
                    throw new \RuntimeException('La voz en tiempo real no esta disponible en este momento.');
                }
            }
        } finally {
            if (is_resource($socket)) {
                @fclose($socket);
            }
        }

        if ($chunks === 0) {
            throw new \RuntimeException('El proveedor no devolvio audio en tiempo real.');
        }

        return [
            'sample_rate' => $config['sample_rate'],
            'voice' => $config['voice'],
            'model' => $config['model'],
            'chunks' => $chunks,
            'first_audio_ms' => $firstAudioMs,
        ];
    }

    /** @param array<string,mixed> $config @return resource */
    private function openSocket(array $config)
    {
        $host = 'dashscope-intl.aliyuncs.com';
        $path = '/api-ws/v1/realtime?model=' . rawurlencode((string) $config['model']);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $host,
                'SNI_enabled' => true,
            ],
        ]);
        $socket = @stream_socket_client(
            'tls://' . $host . ':443',
            $errorNumber,
            $errorMessage,
            8,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!is_resource($socket)) {
            error_log('[Leonidas] Realtime TTS connection failed: ' . $errorNumber . ' ' . $errorMessage);
            throw new \RuntimeException('No se pudo conectar la voz en tiempo real.');
        }

        stream_set_timeout($socket, 18);
        $key = base64_encode(random_bytes(16));
        $headers = [
            'GET ' . $path . ' HTTP/1.1',
            'Host: ' . $host,
            'Upgrade: websocket',
            'Connection: Upgrade',
            'Sec-WebSocket-Key: ' . $key,
            'Sec-WebSocket-Version: 13',
            'Authorization: Bearer ' . $config['api_key'],
            'User-Agent: Sparta-Leonidas/1.0',
        ];
        if ($config['workspace'] !== '') {
            $headers[] = 'X-DashScope-WorkSpace: ' . $config['workspace'];
        }
        $this->writeAll($socket, implode("\r\n", $headers) . "\r\n\r\n");

        // Read only through the HTTP delimiter. A larger fread can also consume
        // the provider's first WebSocket event and silently discard it.
        $response = '';
        while (!str_contains($response, "\r\n\r\n") && strlen($response) < 16384) {
            $part = fread($socket, 1);
            if (!is_string($part) || $part === '') {
                break;
            }
            $response .= $part;
        }

        if (!preg_match('#^HTTP/\d(?:\.\d)?\s+101\b#', $response)) {
            error_log('[Leonidas] Realtime TTS handshake rejected: ' . strtok($response, "\r\n"));
            fclose($socket);
            throw new \RuntimeException('El proveedor rechazo la conexion de voz en tiempo real.');
        }

        $expectedAccept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        if (!preg_match('/^Sec-WebSocket-Accept:\s*(.+)$/mi', $response, $matches)
            || !hash_equals($expectedAccept, trim($matches[1]))) {
            fclose($socket);
            throw new \RuntimeException('La conexion de voz en tiempo real no pudo validarse.');
        }

        return $socket;
    }

    /** @param resource $socket */
    private function sendJson($socket, array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('No se pudo preparar el evento de voz.');
        }
        $this->sendFrame($socket, $json, 0x1);
    }

    /** @param resource $socket */
    private function sendFrame($socket, string $payload, int $opcode): void
    {
        $length = strlen($payload);
        $header = chr(0x80 | ($opcode & 0x0f));
        if ($length <= 125) {
            $header .= chr(0x80 | $length);
        } elseif ($length <= 65535) {
            $header .= chr(0x80 | 126) . pack('n', $length);
        } else {
            $header .= chr(0x80 | 127) . pack('NN', 0, $length);
        }

        $mask = random_bytes(4);
        $masked = '';
        for ($index = 0; $index < $length; $index++) {
            $masked .= $payload[$index] ^ $mask[$index % 4];
        }
        $this->writeAll($socket, $header . $mask . $masked);
    }

    /** @param resource $socket */
    private function readMessage($socket): ?string
    {
        $message = '';
        $messageOpcode = null;

        while (true) {
            $head = $this->readExact($socket, 2);
            if ($head === null) {
                return null;
            }
            $first = ord($head[0]);
            $second = ord($head[1]);
            $final = ($first & 0x80) !== 0;
            $opcode = $first & 0x0f;
            $masked = ($second & 0x80) !== 0;
            $length = $second & 0x7f;

            if ($length === 126) {
                $extended = $this->readExact($socket, 2);
                if ($extended === null) return null;
                $length = (int) (unpack('nlength', $extended)['length'] ?? 0);
            } elseif ($length === 127) {
                $extended = $this->readExact($socket, 8);
                if ($extended === null) return null;
                $parts = unpack('Nhigh/Nlow', $extended);
                if ((int) ($parts['high'] ?? 0) !== 0) {
                    throw new \RuntimeException('El proveedor devolvio un bloque de audio demasiado grande.');
                }
                $length = (int) ($parts['low'] ?? 0);
            }

            if ($length > self::MAX_MESSAGE_BYTES) {
                throw new \RuntimeException('El proveedor devolvio un bloque de audio fuera de limite.');
            }
            $mask = $masked ? $this->readExact($socket, 4) : '';
            $payload = $length > 0 ? $this->readExact($socket, $length) : '';
            if ($payload === null || ($masked && $mask === null)) {
                return null;
            }
            if ($masked) {
                $decoded = '';
                for ($index = 0; $index < $length; $index++) {
                    $decoded .= $payload[$index] ^ $mask[$index % 4];
                }
                $payload = $decoded;
            }

            if ($opcode === 0x8) {
                return null;
            }
            if ($opcode === 0x9) {
                $this->sendFrame($socket, $payload, 0xA);
                continue;
            }
            if ($opcode === 0xA) {
                continue;
            }
            if ($opcode === 0x1 || $opcode === 0x2) {
                $messageOpcode = $opcode;
                $message = $payload;
            } elseif ($opcode === 0x0 && $messageOpcode !== null) {
                $message .= $payload;
            } else {
                continue;
            }

            if ($final) {
                return $messageOpcode === 0x1 ? $message : null;
            }
        }
    }

    /** @param resource $socket */
    private function readExact($socket, int $length): ?string
    {
        $buffer = '';
        while (strlen($buffer) < $length) {
            $part = fread($socket, $length - strlen($buffer));
            if (!is_string($part) || $part === '') {
                $meta = stream_get_meta_data($socket);
                if (($meta['timed_out'] ?? false) === true) {
                    throw new \RuntimeException('La voz en tiempo real excedio el tiempo de espera.');
                }
                return null;
            }
            $buffer .= $part;
        }
        return $buffer;
    }

    /** @param resource $socket */
    private function writeAll($socket, string $data): void
    {
        $offset = 0;
        $length = strlen($data);
        while ($offset < $length) {
            $written = fwrite($socket, substr($data, $offset));
            if (!is_int($written) || $written <= 0) {
                throw new \RuntimeException('Se interrumpio la conexion de voz en tiempo real.');
            }
            $offset += $written;
        }
    }

    private function eventId(): string
    {
        return 'event_' . bin2hex(random_bytes(12));
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

    /** @return array{api_key:string,workspace:string,model:string,voice:string,language:string,instructions:string,sample_rate:int} */
    private function config(): array
    {
        $variables = [];
        $allowed = [
            'ALIBABA_API_KEY',
            'ALIBABA_WORKSPACE_ID',
            'ALIBABA_TTS_REALTIME_MODEL',
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

        return [
            'api_key' => trim((string) ($variables['ALIBABA_API_KEY'] ?? '')),
            'workspace' => trim((string) ($variables['ALIBABA_WORKSPACE_ID'] ?? '')),
            'model' => trim((string) ($variables['ALIBABA_TTS_REALTIME_MODEL'] ?? 'qwen3-tts-flash-realtime')),
            'voice' => trim((string) ($variables['ALIBABA_TTS_VOICE'] ?? 'Vincent')),
            'language' => trim((string) ($variables['ALIBABA_TTS_LANGUAGE'] ?? 'Spanish')),
            'instructions' => trim((string) ($variables['ALIBABA_TTS_INSTRUCTIONS']
                ?? 'Speak in natural Latin American Spanish with a deep, warm adult male voice. Sound confident, approachable and human. Use subtle emotion, clear diction, conversational pacing and natural pauses. Avoid robotic cadence, shouting, exaggerated theater and an artificial announcer tone.')),
            'sample_rate' => 24000,
        ];
    }
}
