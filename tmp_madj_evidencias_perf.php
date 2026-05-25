<?php
error_reporting(E_ERROR | E_PARSE);

define('RAIZ', __DIR__ . '/backend');
define('SPARTA_PROJECT_ROOT', __DIR__);
define('SPARTA_UPLOADS_ROOT', __DIR__ . '/public/uploads');
define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('CONTROLADORES', RAIZ . '/controllers');
define('LIBRERIAS', RAIZ . '/libs');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');

require_once __DIR__ . '/backend/bootstrap_composer.php';
sparta_require_composer_autoload();

spl_autoload_register(function ($archivo) {
    if (strpos($archivo, 'PhpOffice\\') === 0 ||
        strpos($archivo, 'ZipStream\\') === 0 ||
        strpos($archivo, 'Psr\\') === 0) {
        return;
    }

    $archivo = str_replace('\\', '/', $archivo);
    $parts = explode('/', $archivo, 2);
    $top = $parts[0];
    $tail = $parts[1] ?? '';
    $dirMap = [
        'Models' => 'models',
        'Controllers' => 'controllers',
        'Core' => 'core',
        'Libs' => 'libs',
        'Services' => 'services',
    ];
    $dir = $dirMap[$top] ?? strtolower($top);
    $rel = $tail !== '' ? $dir . '/' . $tail : $dir;
    $ruta = RAIZ . '/' . $rel . '.php';
    if (!file_exists($ruta)) {
        throw new Exception("Autoload no encontro: $ruta");
    }
    require_once $ruta;
});

require_once RAIZ . '/config/config.php';

$m = new Models\AtencionClientes();
$tests = [
    'bandeja_sin_sync' => fn() => $m->obtenerRecibidos(false),
    'conteos' => fn() => $m->obtenerConteosPestanasEvidencias(),
    'aprobados' => fn() => $m->obtenerEvidenciasAprobadas(),
    'correcciones' => fn() => $m->obtenerEvidenciasCorrecciones(),
];

foreach ($tests as $name => $fn) {
    $t = microtime(true);
    $res = $fn();
    $ms = round((microtime(true) - $t) * 1000, 1);
    $count = is_array($res) ? count($res) : -1;
    echo $name . "\t" . $ms . "ms\tcount=" . $count . PHP_EOL;
}
