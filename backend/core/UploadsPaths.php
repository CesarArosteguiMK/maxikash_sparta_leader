<?php

/**
 * Carpeta única de archivos subidos: {raíz del repo}/public/uploads
 * RAIZ apunta a backend/; la raíz del repo es dirname(RAIZ).
 */

if (!function_exists('sparta_project_root')) {
    function sparta_project_root(): string
    {
        if (defined('SPARTA_PROJECT_ROOT')) {
            return SPARTA_PROJECT_ROOT;
        }
        if (defined('RAIZ')) {
            return dirname(RAIZ);
        }

        return dirname(__DIR__, 2);
    }
}

if (!function_exists('sparta_uploads_root')) {
    function sparta_uploads_root(): string
    {
        if (defined('SPARTA_UPLOADS_ROOT')) {
            return SPARTA_UPLOADS_ROOT;
        }

        return sparta_project_root() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
    }
}

if (!function_exists('sparta_uploads_join')) {
    /**
     * @param string ...$parts segmentos bajo uploads/ (sin "..")
     */
    function sparta_uploads_join(string ...$parts): string
    {
        $acc = rtrim(sparta_uploads_root(), DIRECTORY_SEPARATOR . '/\\');
        foreach ($parts as $p) {
            if ($p === '' || $p === '.') {
                continue;
            }
            if (strpos($p, '..') !== false) {
                continue;
            }
            $p = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p);
            $acc .= DIRECTORY_SEPARATOR . trim($p, DIRECTORY_SEPARATOR . '/\\');
        }

        return $acc;
    }
}

if (!function_exists('sparta_uploads_resolve_relative')) {
    /**
     * Resuelve una ruta relativa guardada en BD (solo bajo uploads/), con comprobación de path traversal.
     */
    function sparta_uploads_resolve_relative(string $relativePath): ?string
    {
        $relativePath = str_replace('\\', '/', trim($relativePath, "/\\"));
        if ($relativePath === '' || strpos($relativePath, '..') !== false) {
            return null;
        }
        $full = sparta_uploads_root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $resolved = realpath($full);
        $root = realpath(sparta_uploads_root());
        if ($resolved === false || $root === false) {
            return null;
        }
        $resolvedNorm = strtolower(str_replace('\\', '/', $resolved));
        $rootNorm = strtolower(str_replace('\\', '/', $root));
        if ($resolvedNorm !== $rootNorm && strpos($resolvedNorm, $rootNorm . '/') !== 0) {
            return null;
        }

        return $resolved;
    }
}
