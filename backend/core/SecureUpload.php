<?php

namespace Core;

/**
 * Helper para subida segura de archivos: validación MIME real (finfo),
 * nombre generado en servidor (UUID + extensión), permisos 0755.
 */
class SecureUpload
{
    public const MIME_PDF = ['application/pdf'];
    public const MIME_IMAGES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    public const MIME_DOC = [
        'application/msword',                                                                 // .doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'             // .docx
    ];
    public const MIME_PDF_OR_IMAGES = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    public const MIME_DESPACHO = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    /**
     * Obtiene el MIME real del archivo (finfo).
     */
    public static function getMimeType(string $tmpPath): ?string
    {
        if (!is_file($tmpPath) || !is_readable($tmpPath)) {
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }
        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
        return is_string($mime) ? $mime : null;
    }

    /**
     * Valida que el MIME real del archivo esté en la lista permitida.
     */
    public static function validateMime(string $tmpPath, array $allowedMimes): bool
    {
        $mime = self::getMimeType($tmpPath);
        return $mime !== null && in_array($mime, $allowedMimes, true);
    }

    /**
     * Devuelve extensión segura a partir del MIME (no confiar en nombre del cliente).
     */
    public static function extensionFromMime(string $mime): string
    {
        $map = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];
        return $map[$mime] ?? 'bin';
    }

    /**
     * Genera un nombre de archivo seguro en el servidor (UUID + extensión).
     */
    public static function generateSafeFilename(string $extension): string
    {
        $ext = preg_replace('/[^a-z0-9]/', '', strtolower($extension));
        if ($ext === '') {
            $ext = 'bin';
        }
        $uuid = bin2hex(random_bytes(16));
        return $uuid . '.' . $ext;
    }

    /**
     * Crea el directorio si no existe, con permisos 0755 (no 0777).
     */
    public static function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
    }
}
