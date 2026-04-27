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

if (!function_exists('sparta_url_publica_desde_repositorio')) {
    /**
     * Ajusta rutas guardadas como /uploads/... para usarse en <img src>, <video> o iframes.
     * En una subcarpeta (p. ej. /sparta___SPARTA_SECRET_REDACTED__/public) la raíz de sitio no es la carpeta pública;
     * sin este prefijo el navegador pide http://localhost/uploads/... (404) en vez de
     * .../sparta___SPARTA_SECRET_REDACTED__/public/uploads/...
     */
    function sparta_url_publica_desde_repositorio(string $ruta): string
    {
        $r = trim($ruta);
        if ($r === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $r)) {
            return $r;
        }
        $r = str_replace('\\', '/', $r);
        if ($r[0] !== '/') {
            $r = '/' . ltrim($r, '/');
        }
        if (preg_match('#^/public/uploads/#i', $r)) {
            $r = substr($r, strlen('/public'));
        }
        if (!preg_match('#^/uploads/#i', $r)) {
            return $r[0] === '/' ? $r : '/' . $r;
        }

        if (php_sapi_name() === 'cli' || empty($_SERVER['SCRIPT_NAME'])) {
            return $r;
        }

        $script = str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']);
        if ($script === '' || $script[0] !== '/') {
            $script = '/' . ltrim($script, '/');
        }
        $publicBase = rtrim(dirname($script), '/');
        if ($publicBase === '.' || $publicBase === '') {
            $publicBase = '';
        }
        // Rescate: en algunas peticiones (p. ej. XAMPP) dirname devuelve "/" y se pierde /proyecto/public
        if ($publicBase === '' && !empty($_SERVER['REQUEST_URI'])) {
            $ru = (string) $_SERVER['REQUEST_URI'];
            if (preg_match('#^/([^/]+/public)(?:/|\?|#|$)#', $ru, $m)) {
                $publicBase = '/' . trim($m[1], '/');
            }
        }
        return ($publicBase === '' ? '' : $publicBase) . $r;
    }
}
