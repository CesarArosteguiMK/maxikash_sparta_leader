<?php

/**
 * Arranque mínimo para scripts CLI (misma autoload que la app web).
 * Uso: require __DIR__ . '/cli_bootstrap.php';
 */
declare(strict_types=1);

if (!defined('RAIZ')) {
    define('RAIZ', __DIR__);
}

require_once RAIZ . '/bootstrap_composer.php';
sparta_require_composer_autoload();

spl_autoload_register(function (string $archivo): void {
    if (strpos($archivo, 'PhpOffice\\') === 0 ||
        strpos($archivo, 'ZipStream\\') === 0 ||
        strpos($archivo, 'Psr\\') === 0) {
        return;
    }
    $archivo = str_replace('\\', '/', $archivo);
    $parts = explode('/', $archivo, 2);
    $top = $parts[0];
    $tail = $parts[1] ?? '';
    static $dirMap = [
        'Models' => 'models',
        'Controllers' => 'controllers',
        'Core' => 'core',
        'Libs' => 'libs',
        'Services' => 'services',
    ];
    $dir = $dirMap[$top] ?? strtolower($top);
    $rel = $tail !== '' ? $dir . '/' . $tail : $dir;
    $ruta = RAIZ . '/' . $rel . '.php';
    if (!is_file($ruta)) {
        throw new RuntimeException("Autoload CLI no encontró: {$ruta}");
    }
    require_once $ruta;
});
