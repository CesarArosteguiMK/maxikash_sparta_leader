<?php

namespace Services;

/**
 * Adjuntos temporales de Leonidas vinculados al actor y con caducidad.
 */
final class LeonidasAttachmentService
{
    private const SESSION_KEY = 'leonidas_operational_uploads';
    private const TTL = 1800;
    private const MAX_BYTES = 20_971_520;

    private const EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'webp',
        'doc', 'docx', 'xls', 'xlsx', 'csv',
        'mp4', 'mov', 'webm',
    ];

    public function guardarCarga(array $archivo, int $actorId): array
    {
        $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('Selecciona un archivo válido para adjuntar.');
        }
        $tmp = (string) ($archivo['tmp_name'] ?? '');
        $nombre = $this->nombreSeguro((string) ($archivo['name'] ?? 'archivo'));
        $size = (int) ($archivo['size'] ?? 0);
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONS, true)) {
            throw new \InvalidArgumentException('Formato no permitido. Usa PDF, imagen, video, Word, CSV o Excel.');
        }
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new \InvalidArgumentException('El archivo está vacío o supera el límite de 20 MB.');
        }
        if ($actorId <= 0 || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('No se pudo validar el origen seguro del archivo.');
        }

        $mime = $this->mime($tmp);
        if (!$this->mimeCompatible($extension, $mime)) {
            throw new \InvalidArgumentException('El contenido del archivo no coincide con su extensión.');
        }

        $directorio = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta_leonidas_adjuntos';
        if (!is_dir($directorio) && !mkdir($directorio, 0700, true) && !is_dir($directorio)) {
            throw new \RuntimeException('No se pudo preparar el almacenamiento temporal.');
        }
        $this->limpiarTemporalesHuerfanos($directorio);

        $token = bin2hex(random_bytes(18));
        $destino = $directorio . DIRECTORY_SEPARATOR . $token . '.' . $extension;
        if (!move_uploaded_file($tmp, $destino)) {
            throw new \RuntimeException('No se pudo guardar temporalmente el archivo.');
        }

        $cargas = $this->cargasVigentes($actorId);
        $cargas[$token] = [
            'actor_id' => $actorId,
            'nombre' => $nombre,
            'ruta' => $destino,
            'extension' => $extension,
            'mime' => $mime,
            'tamano_bytes' => $size,
            'hash' => hash_file('sha256', $destino),
            'expira_en' => time() + self::TTL,
            'materializaciones' => [],
        ];
        $_SESSION[self::SESSION_KEY] = $cargas;

        return [
            'token' => $token,
            'nombre' => $nombre,
            'tipo' => $this->tipo($extension),
            'mime' => $mime,
            'expira_en' => $cargas[$token]['expira_en'],
        ];
    }

    public function existe(string $token, int $actorId): bool
    {
        try {
            $this->metadata($token, $actorId);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function metadata(string $token, int $actorId): array
    {
        $cargas = $this->cargasVigentes($actorId);
        $meta = is_array($cargas[$token] ?? null) ? $cargas[$token] : null;
        if (!$meta || !is_file((string) ($meta['ruta'] ?? ''))) {
            throw new \RuntimeException('El adjunto expiró o ya no está disponible.');
        }
        if (!hash_equals((string) ($meta['hash'] ?? ''), (string) hash_file('sha256', (string) $meta['ruta']))) {
            throw new \RuntimeException('El adjunto cambió después de su carga y fue rechazado.');
        }
        return $meta;
    }

    public function rutaTemporal(string $token, int $actorId, array $tipos = []): string
    {
        $meta = $this->metadata($token, $actorId);
        $tipo = $this->tipo((string) ($meta['extension'] ?? ''));
        if ($tipos && !in_array($tipo, $tipos, true)) {
            throw new \InvalidArgumentException('El tipo de adjunto no es válido para esta operación.');
        }
        return (string) $meta['ruta'];
    }

    public function materializar(string $token, int $actorId, string $scope): array
    {
        $meta = $this->metadata($token, $actorId);
        $scope = preg_replace('/[^a-z0-9_-]+/i', '_', strtolower(trim($scope))) ?: 'operaciones';
        $materializadas = is_array($meta['materializaciones'] ?? null) ? $meta['materializaciones'] : [];
        if (!empty($materializadas[$scope]) && is_file((string) ($materializadas[$scope]['ruta_absoluta'] ?? ''))) {
            return $materializadas[$scope];
        }

        $raiz = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'leonidas' . DIRECTORY_SEPARATOR . $scope . DIRECTORY_SEPARATOR . date('Y-m');
        if (!is_dir($raiz) && !mkdir($raiz, 0750, true) && !is_dir($raiz)) {
            throw new \RuntimeException('No se pudo preparar el directorio definitivo del adjunto.');
        }
        $archivo = date('Ymd_His') . '_' . substr((string) $meta['hash'], 0, 12) . '.'
            . (string) $meta['extension'];
        $destino = $raiz . DIRECTORY_SEPARATOR . $archivo;
        if (!is_file($destino) && !copy((string) $meta['ruta'], $destino)) {
            throw new \RuntimeException('No se pudo conservar el adjunto en el módulo correspondiente.');
        }
        $url = '/uploads/leonidas/' . $scope . '/' . date('Y-m') . '/' . rawurlencode($archivo);
        $info = [
            'url' => $url,
            'ruta_publica' => $url,
            'ruta_absoluta' => $destino,
            'nombre_original' => (string) $meta['nombre'],
            'mime_type' => (string) $meta['mime'],
            'tamano_bytes' => (int) $meta['tamano_bytes'],
            'tipo_evidencia' => $this->tipoEvidencia((string) $meta['extension']),
            'hash' => (string) $meta['hash'],
        ];

        $cargas = $this->cargasVigentes($actorId);
        if (isset($cargas[$token])) {
            $cargas[$token]['materializaciones'][$scope] = $info;
            $_SESSION[self::SESSION_KEY] = $cargas;
        }
        return $info;
    }

    private function cargasVigentes(int $actorId): array
    {
        $cargas = is_array($_SESSION[self::SESSION_KEY] ?? null) ? $_SESSION[self::SESSION_KEY] : [];
        foreach ($cargas as $token => $meta) {
            if (!is_array($meta)
                || (int) ($meta['actor_id'] ?? 0) !== $actorId
                || (int) ($meta['expira_en'] ?? 0) < time()
            ) {
                if (is_array($meta) && is_file((string) ($meta['ruta'] ?? ''))) {
                    @unlink((string) $meta['ruta']);
                }
                unset($cargas[$token]);
            }
        }
        $_SESSION[self::SESSION_KEY] = $cargas;
        return $cargas;
    }

    private function nombreSeguro(string $nombre): string
    {
        $nombre = basename(str_replace('\\', '/', trim($nombre)));
        $nombre = preg_replace('/[^\pL\pN._ -]+/u', '_', $nombre) ?: 'archivo';
        return mb_substr($nombre, 0, 180);
    }

    private function mime(string $ruta): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return strtolower((string) ($finfo->file($ruta) ?: 'application/octet-stream'));
    }

    private function mimeCompatible(string $extension, string $mime): bool
    {
        $map = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'xls' => ['application/vnd.ms-excel', 'application/octet-stream', 'application/x-ole-storage'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
            'csv' => ['text/plain', 'text/csv', 'application/csv'],
            'mp4' => ['video/mp4', 'application/octet-stream'],
            'mov' => ['video/quicktime', 'application/octet-stream'],
            'webm' => ['video/webm', 'application/octet-stream'],
        ];
        return in_array($mime, $map[$extension] ?? [], true);
    }

    private function tipo(string $extension): string
    {
        if (in_array($extension, ['xls', 'xlsx', 'csv'], true)) return 'hoja_calculo';
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) return 'imagen';
        if (in_array($extension, ['mp4', 'mov', 'webm'], true)) return 'video';
        return 'documento';
    }

    private function tipoEvidencia(string $extension): string
    {
        $tipo = $this->tipo($extension);
        return $tipo === 'imagen' ? 'foto' : ($tipo === 'video' ? 'video' : 'documento');
    }

    private function limpiarTemporalesHuerfanos(string $directorio): void
    {
        $limite = time() - self::TTL - 300;
        $archivos = glob(rtrim($directorio, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') ?: [];
        foreach (array_slice($archivos, 0, 500) as $archivo) {
            if (is_file($archivo) && (int) @filemtime($archivo) < $limite) {
                @unlink($archivo);
            }
        }
    }
}
