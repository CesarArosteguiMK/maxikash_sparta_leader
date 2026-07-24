<?php

namespace Services;

class LeonidasMediaService
{
    private GeminiMediaClient $client;
    private string $storagePath;
    private LeonidasArtifactBuilder $artifacts;

    public function __construct(
        ?GeminiMediaClient $client = null,
        ?string $storagePath = null,
        ?LeonidasArtifactBuilder $artifacts = null
    )
    {
        $this->client = $client ?? new GeminiMediaClient();
        $this->storagePath = $storagePath ?? dirname(__DIR__) . '/storage/leonidas_media';
        $this->artifacts = $artifacts ?? new LeonidasArtifactBuilder($this->client);
    }

    /** @param array<string, mixed> $contexto @return array<string, mixed>|null */
    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        $tipo = $this->detectarTipo($normalizado);
        if ($tipo === null) {
            return null;
        }
        $actorId = (int) ($contexto['actor_id'] ?? 0);
        if ($actorId <= 0) {
            return $this->error('No pude identificar al usuario que solicita la generación.');
        }
        $prompt = $this->extraerPrompt($mensaje, $tipo);
        if (mb_strlen($prompt, 'UTF-8') < 4) {
            return [
                'mensaje' => 'Describe qué quieres que genere y agrega el estilo, ambiente o intención que prefieras.',
                'tipo' => 'media_solicitar_prompt',
            ];
        }

        try {
            if ($tipo === 'imagen') {
                return $this->generarImagen($prompt, $actorId);
            }
            if ($tipo === 'video') {
                return $this->iniciarVideo($prompt, $actorId);
            }
            if ($tipo === 'audio') {
                return $this->generarMusica($prompt, $actorId);
            }
            return $this->generarArtefacto($tipo, $prompt, $actorId, $contexto);
        } catch (\Throwable $error) {
            error_log('[LeonidasMedia] Generacion fallida: ' . $error->getMessage());
            return $this->error('No pude completar la generación multimedia. No se creó ningún archivo.');
        }
    }

    /** @return array<string, mixed> */
    public function estado(string $token, int $actorId): array
    {
        $metadata = $this->readMetadata($token, $actorId);
        if (($metadata['status'] ?? '') !== 'processing') {
            return ['mensaje' => '', 'tipo' => 'media_estado', 'medio' => $this->publicMedia($metadata)];
        }
        if (($metadata['kind'] ?? '') !== 'video') {
            throw new \RuntimeException('El archivo multimedia tiene un estado inválido.');
        }

        $result = $this->client->pollVideo((string) ($metadata['operation'] ?? ''));
        if (!$result['success']) {
            $metadata['status'] = 'error';
            $metadata['error'] = (string) ($result['message'] ?? 'No se pudo generar el video.');
            $this->writeMetadata($token, $metadata);
        } elseif (!empty($result['done'])) {
            $this->storeBody($token, $metadata, (string) $result['body'], (string) $result['mime']);
            $metadata = $this->readMetadata($token, $actorId);
        }

        return ['mensaje' => '', 'tipo' => 'media_estado', 'medio' => $this->publicMedia($metadata)];
    }

    /** @return array{body:string,mime:string,name:string} */
    public function obtener(string $token, int $actorId): array
    {
        $metadata = $this->readMetadata($token, $actorId);
        if (($metadata['status'] ?? '') !== 'ready') {
            throw new \RuntimeException('El archivo todavía no está disponible.');
        }
        $path = $this->filePath($token, (string) ($metadata['extension'] ?? 'bin'));
        if (!is_readable($path)) {
            throw new \RuntimeException('El archivo generado ya no está disponible.');
        }
        $body = file_get_contents($path);
        if (!is_string($body)) {
            throw new \RuntimeException('No se pudo leer el archivo generado.');
        }
        return [
            'body' => $body,
            'mime' => (string) ($metadata['mime'] ?? 'application/octet-stream'),
            'name' => (string) ($metadata['name'] ?? 'leonidas-generacion.bin'),
        ];
    }

    /** @return array<string, mixed> */
    private function generarImagen(string $prompt, int $actorId): array
    {
        $result = $this->client->generateImage($prompt);
        if (!$result['success']) {
            return $this->error((string) ($result['message'] ?? 'Gemini no pudo generar la imagen.'));
        }
        $metadata = $this->newMetadata('image', $prompt, $actorId, (string) ($result['model'] ?? 'Gemini'));
        $token = (string) $metadata['token'];
        $this->storeBody($token, $metadata, (string) $result['body'], (string) $result['mime']);
        return [
            'mensaje' => 'Listo. Generé la imagen con Gemini y la dejé disponible de forma privada.',
            'tipo' => 'media_imagen',
            'medio' => $this->publicMedia($this->readMetadata($token, $actorId)),
        ];
    }

    /** @return array<string, mixed> */
    private function iniciarVideo(string $prompt, int $actorId): array
    {
        $result = $this->client->startVideo($prompt);
        if (!$result['success']) {
            return $this->error((string) ($result['message'] ?? 'Gemini no pudo iniciar el video.'));
        }
        $metadata = $this->newMetadata('video', $prompt, $actorId, (string) ($result['model'] ?? 'Veo'));
        $metadata['operation'] = (string) $result['operation'];
        $this->writeMetadata((string) $metadata['token'], $metadata);
        return [
            'mensaje' => 'Empecé a generar el video con Veo. Puedes seguir conversando; actualizaré esta tarjeta cuando termine.',
            'tipo' => 'media_video',
            'medio' => $this->publicMedia($metadata),
        ];
    }

    /** @return array<string, mixed> */
    private function generarMusica(string $prompt, int $actorId): array
    {
        $result = $this->client->generateMusic($prompt);
        if (!$result['success']) {
            return $this->error((string) ($result['message'] ?? 'Lyria no pudo generar la música.'));
        }
        $metadata = $this->newMetadata('audio', $prompt, $actorId, (string) ($result['model'] ?? 'Lyria'));
        $token = (string) $metadata['token'];
        $this->storeBody($token, $metadata, (string) $result['body'], (string) $result['mime']);
        return [
            'mensaje' => 'Listo. Compuse la pista con Lyria y quedó disponible de forma privada.',
            'tipo' => 'media_audio',
            'medio' => $this->publicMedia($this->readMetadata($token, $actorId)),
        ];
    }

    /** @param array<string, mixed> $contexto @return array<string, mixed> */
    private function generarArtefacto(string $tipo, string $prompt, int $actorId, array $contexto): array
    {
        $requester = trim((string) (
            $contexto['actor_name']
            ?? $contexto['nombre_usuario']
            ?? $contexto['usuario']
            ?? ''
        ));
        if ($tipo === 'diagrama') {
            $result = $this->artifacts->buildDiagram($prompt);
            $kind = 'diagram';
            $message = 'Listo. Prepare el diagrama y puedes verlo o descargarlo.';
        } elseif ($tipo === 'pdf') {
            $result = $this->artifacts->buildPdf($prompt, $requester);
            $kind = 'pdf';
            $message = 'Listo. Prepare el PDF solicitado y ya esta disponible para descargar.';
        } else {
            $result = $this->artifacts->buildSpreadsheet($prompt, $requester);
            $kind = 'excel';
            $message = 'Listo. Prepare el archivo Excel solicitado y ya esta disponible para descargar.';
        }
        if (empty($result['success'])) {
            return $this->error((string) ($result['message'] ?? 'No pude preparar el archivo solicitado.'));
        }

        $metadata = $this->newMetadata($kind, $prompt, $actorId, (string) ($result['model'] ?? 'Gemini'));
        $token = (string) $metadata['token'];
        $this->storeBody(
            $token,
            $metadata,
            (string) ($result['body'] ?? ''),
            (string) ($result['mime'] ?? 'application/octet-stream'),
            (string) ($result['name_hint'] ?? $kind)
        );
        return [
            'mensaje' => $message,
            'tipo' => 'media_' . $kind,
            'medio' => $this->publicMedia($this->readMetadata($token, $actorId)),
        ];
    }

    private function detectarTipo(string $normalizado): ?string
    {
        if (
            preg_match('/\b(quiero saber|dime si|puedes|eres capaz|sabes)\b.*\b(generar|crear|hacer|componer)\b/u', $normalizado) === 1
        ) {
            return null;
        }
        $accion = preg_match(
            '/\b(genera|generame|crea|creame|haz|dibuja|compone|quiero|prepara|preparame|exporta|descarga|descargame)\b/u',
            $normalizado
        ) === 1;
        if (!$accion) {
            return null;
        }
        if (preg_match('/\b(imagen|foto|fotografia|ilustracion|poster|retrato)\b/u', $normalizado)) {
            return 'imagen';
        }
        if (preg_match('/\b(video|clip|animacion cinematica)\b/u', $normalizado)) {
            return 'video';
        }
        if (preg_match('/\b(musica|cancion|melodia|pista|soundtrack|audio musical)\b/u', $normalizado)) {
            return 'audio';
        }
        if (preg_match('/\b(diagrama|flujo|mapa de proceso|organigrama visual)\b/u', $normalizado)) {
            return 'diagrama';
        }
        if (preg_match('/\b(pdf|documento pdf|reporte pdf|informe pdf)\b/u', $normalizado)) {
            return 'pdf';
        }
        if (preg_match('/\b(excel|xlsx|hoja de calculo|archivo de excel|reporte en excel)\b/u', $normalizado)) {
            return 'excel';
        }
        return null;
    }

    private function extraerPrompt(string $mensaje, string $tipo): string
    {
        $prompt = preg_replace(
            '/^\s*(por favor\s+)?(genera|generame|crea|creame|haz|dibuja|compone|quiero|prepara|preparame|exporta|descarga|descargame)\s+(una?|un)?\s*' .
            '(imagen|foto|fotografia|ilustracion|poster|retrato|video|clip|animacion cinematica|musica|cancion|melodia|pista|soundtrack|audio musical|diagrama|flujo|mapa de proceso|organigrama visual|pdf|documento pdf|reporte pdf|informe pdf|excel|xlsx|hoja de calculo|archivo de excel|reporte en excel)?\s*(de|sobre|con)?\s*/iu',
            '',
            $mensaje
        );
        $prompt = trim(is_string($prompt) ? $prompt : $mensaje);
        return 'Crea ' . $tipo . ' para uso interno de Sparta. Solicitud del usuario: ' . $prompt;
    }

    /** @return array<string, mixed> */
    private function newMetadata(string $kind, string $prompt, int $actorId, string $model): array
    {
        $this->ensureStorage();
        $this->cleanup();
        return [
            'token' => bin2hex(random_bytes(24)),
            'owner' => $actorId,
            'kind' => $kind,
            'status' => 'processing',
            'model' => $model,
            'prompt_hash' => hash('sha256', $prompt),
            'created_at' => time(),
            'expires_at' => time() + 86400,
        ];
    }

    /** @param array<string, mixed> $metadata */
    private function storeBody(
        string $token,
        array $metadata,
        string $body,
        string $mime,
        string $nameHint = ''
    ): void
    {
        if ($body === '') {
            throw new \RuntimeException('El proveedor entregó un archivo vacío.');
        }
        $extension = $this->extensionForMime($mime, (string) ($metadata['kind'] ?? ''));
        if (file_put_contents($this->filePath($token, $extension), $body, LOCK_EX) === false) {
            throw new \RuntimeException('No se pudo guardar el archivo generado.');
        }
        $metadata['status'] = 'ready';
        $metadata['mime'] = $mime;
        $metadata['extension'] = $extension;
        $baseName = trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $nameHint), '-');
        if ($baseName === '') {
            $baseName = 'leonidas-' . ($metadata['kind'] ?? 'media');
        }
        $metadata['name'] = strtolower($baseName) . '-' . date('Ymd-His') . '.' . $extension;
        $metadata['size'] = strlen($body);
        unset($metadata['operation']);
        $this->writeMetadata($token, $metadata);
    }

    /** @param array<string, mixed> $metadata */
    private function publicMedia(array $metadata): array
    {
        $token = (string) ($metadata['token'] ?? '');
        $status = (string) ($metadata['status'] ?? 'error');
        $result = [
            'token' => $token,
            'tipo' => (string) ($metadata['kind'] ?? ''),
            'estado' => $status === 'ready' ? 'listo' : ($status === 'processing' ? 'procesando' : 'error'),
            'nombre' => (string) ($metadata['name'] ?? ''),
            'mime' => (string) ($metadata['mime'] ?? ''),
            'modelo' => (string) ($metadata['model'] ?? ''),
        ];
        if ($status === 'ready') {
            $result['url'] = '/Leonidas/medio?token=' . rawurlencode($token);
            $result['descarga_url'] = $result['url'] . '&download=1';
        } elseif ($status === 'error') {
            $result['error'] = (string) ($metadata['error'] ?? 'No se pudo completar la generación.');
        }
        return $result;
    }

    /** @return array<string, mixed> */
    private function readMetadata(string $token, int $actorId): array
    {
        if (!preg_match('/^[a-f0-9]{48}$/', $token)) {
            throw new \InvalidArgumentException('El identificador multimedia no es válido.');
        }
        $path = $this->metadataPath($token);
        if (!is_readable($path)) {
            throw new \RuntimeException('La generación multimedia no existe o ya expiró.');
        }
        $metadata = json_decode((string) file_get_contents($path), true);
        if (!is_array($metadata) || (int) ($metadata['owner'] ?? 0) !== $actorId) {
            throw new \DomainException('No tienes acceso a este archivo multimedia.');
        }
        if ((int) ($metadata['expires_at'] ?? 0) < time()) {
            throw new \RuntimeException('El archivo multimedia ya expiró.');
        }
        return $metadata;
    }

    /** @param array<string, mixed> $metadata */
    private function writeMetadata(string $token, array $metadata): void
    {
        $this->ensureStorage();
        $json = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || file_put_contents($this->metadataPath($token), $json, LOCK_EX) === false) {
            throw new \RuntimeException('No se pudo guardar el estado de la generación.');
        }
    }

    private function ensureStorage(): void
    {
        if (!is_dir($this->storagePath) && !mkdir($this->storagePath, 0770, true) && !is_dir($this->storagePath)) {
            throw new \RuntimeException('No se pudo preparar el almacenamiento multimedia.');
        }
    }

    private function cleanup(): void
    {
        foreach (glob($this->storagePath . '/*.json') ?: [] as $path) {
            $metadata = json_decode((string) @file_get_contents($path), true);
            if (!is_array($metadata) || (int) ($metadata['expires_at'] ?? 0) >= time()) {
                continue;
            }
            $token = basename($path, '.json');
            foreach (glob($this->storagePath . '/' . $token . '.*') ?: [] as $expired) {
                @unlink($expired);
            }
        }
    }

    private function extensionForMime(string $mime, string $kind): string
    {
        $mime = strtolower(trim(explode(';', $mime)[0]));
        $map = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'video/mp4' => 'mp4',
            'audio/wav' => 'wav',
            'audio/mpeg' => 'mp3',
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        ];
        if (isset($map[$mime])) {
            return $map[$mime];
        }
        return match ($kind) {
            'image' => 'png',
            'video' => 'mp4',
            'audio' => 'wav',
            'diagram' => 'svg',
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            default => 'bin',
        };
    }

    private function metadataPath(string $token): string
    {
        return $this->storagePath . '/' . $token . '.json';
    }

    private function filePath(string $token, string $extension): string
    {
        return $this->storagePath . '/' . $token . '.' . preg_replace('/[^a-z0-9]/', '', strtolower($extension));
    }

    /** @return array<string, mixed> */
    private function error(string $message): array
    {
        return ['mensaje' => $message, 'tipo' => 'media_error'];
    }
}
