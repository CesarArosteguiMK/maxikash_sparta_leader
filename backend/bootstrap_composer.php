<?php

/**
 * Carga una sola vez vendor/autoload.php de la raiz del repositorio (Composer).
 * Ubicacion de este archivo: backend/bootstrap_composer.php -> raiz = dirname(__DIR__).
 */
if (!function_exists('sparta_project_root')) {
    function sparta_project_root(): string
    {
        static $root = null;
        if ($root === null) {
            $root = dirname(__DIR__);
        }

        return $root;
    }
}

if (!function_exists('sparta_require_composer_autoload')) {
    function sparta_require_composer_autoload(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        $path = sparta_project_root() . '/vendor/autoload.php';
        if (!is_readable($path)) {
            throw new RuntimeException(
                'No se encontro Composer en la raiz del proyecto. Ejecute: composer install. Buscado: ' . $path
            );
        }

        require_once $path;
        $loaded = true;
    }
}
