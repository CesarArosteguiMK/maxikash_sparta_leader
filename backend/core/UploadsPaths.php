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

if (!function_exists('sparta_public_web_base')) {
    /**
     * Prefijo de ruta URL para la carpeta public (p. ej. "/sparta___SPARTA_SECRET_REDACTED__/public") o "" si el vhost document root es public.
     * Prioridad: [app] base_url en config.ini → dirname(SCRIPT_NAME) → REQUEST_URI → SCRIPT_FILENAME vs DOCUMENT_ROOT.
     */
    function sparta_public_web_base(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        $cached = '';

        if (defined('CONFIGURACION') && is_array(CONFIGURACION)) {
            $bu = trim((string) (CONFIGURACION['base_url'] ?? ''));
            if ($bu !== '') {
                if (preg_match('#^https?://[^/]+(/[^?\#]*?)(?:\?|\#|$)#i', $bu, $m)) {
                    $p = rtrim((string) ($m[1] ?? ''), '/');
                    if ($p !== '' && $p !== '/') {
                        $cached = $p;
                        return $cached;
                    }
                } elseif (isset($bu[0]) && $bu[0] === '/') {
                    $p = rtrim($bu, '/');
                    if ($p !== '' && $p !== '/') {
                        $cached = $p;
                        return $cached;
                    }
                }
            }
        }

        $publicBase = '';
        if (!empty($_SERVER['SCRIPT_NAME'] ?? null)) {
            $script = str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']);
            if ($script === '' || ($script[0] ?? '') !== '/') {
                $script = '/' . ltrim($script, '/');
            }
            $dir = dirname($script);
            if ($dir !== '/' && $dir !== '\\' && $dir !== '.' && $dir !== '') {
                $publicBase = rtrim($dir, '/');
            }
        }

        if ($publicBase === '' && !empty($_SERVER['REQUEST_URI'])) {
            $ru = (string) $_SERVER['REQUEST_URI'];
            if (preg_match('#^/(.+?/public)(?:/|\?|\#|$)#', $ru, $m)) {
                $publicBase = '/' . trim($m[1], '/');
            }
        }

        if ($publicBase === '' && !empty($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
            $sf = str_replace('\\', '/', (string) $_SERVER['SCRIPT_FILENAME']);
            $dr = rtrim(str_replace('\\', '/', (string) $_SERVER['DOCUMENT_ROOT']), '/');
            if ($dr !== '' && strpos($sf, $dr) === 0) {
                $rel = trim(substr($sf, strlen($dr)), '/');
                if (preg_match('#^(.+?/public)/index\.php$#i', $rel, $m)) {
                    $publicBase = '/' . trim(str_replace('\\', '/', $m[1]), '/');
                }
            }
        }

        $cached = $publicBase;
        return $cached;
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
        $r = str_replace('\\', '/', $r);
        // Normaliza host falso "uploads" (ej. //uploads/... o http://uploads/...)
        $r = preg_replace('#^https?://uploads(?=/|$)#i', '/uploads', $r);
        $r = preg_replace('#^/{2,}uploads(?=/|$)#i', '/uploads', $r);
        $r = preg_replace('#^/uploads/uploads/#i', '/uploads/', $r);
        if (preg_match('#^https?://#i', $r)) {
            $parts = @parse_url($r);
            if (is_array($parts) && isset($parts['host'], $parts['path']) && strcasecmp($parts['host'], 'uploads') === 0) {
                $pth = (string) $parts['path'];
                if ($pth === '' || $pth[0] !== '/') {
                    $pth = '/' . ltrim($pth, '/');
                }
                $r = '/uploads' . $pth
                    . (isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '')
                    . (isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '');
            } else {
                return $r;
            }
        }
        if ($r[0] !== '/') {
            $r = '/' . ltrim($r, '/');
        }
        if (preg_match('#^/public/uploads/#i', $r)) {
            $r = substr($r, strlen('/public'));
        }
        if (!preg_match('#^/uploads/#i', $r)) {
            return $r[0] === '/' ? $r : '/' . $r;
        }

        $publicBase = function_exists('sparta_public_web_base') ? sparta_public_web_base() : '';
        return ($publicBase === '' ? '' : $publicBase) . $r;
    }
}
