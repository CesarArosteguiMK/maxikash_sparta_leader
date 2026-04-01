<?php

/**
 * Carga una sola vez vendor/autoload.php de la raíz del repositorio (Composer).
 * Ubicación de este archivo: backend/bootstrap_composer.php → raíz = dirname(__DIR__).
 */
function sparta_project_root(): string
{
    static $root = null;
    if ($root === null) {
        $root = dirname(__DIR__);
    }

    return $root;
}

function sparta_require_composer_autoload(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $path = sparta_project_root() . '/vendor/autoload.php';
    if (!is_readable($path)) {
        throw new RuntimeException(
            'No se encontró Composer en la raíz del proyecto. Ejecute: composer install. Buscado: ' . $path
        );
    }
    require_once $path;
    $loaded = true;
}
