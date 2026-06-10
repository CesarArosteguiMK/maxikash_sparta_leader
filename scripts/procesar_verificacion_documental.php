<?php

declare(strict_types=1);

define('RAIZ', dirname(__DIR__) . '/backend');

if (!defined('SPARTA_PROJECT_ROOT')) {
    define('SPARTA_PROJECT_ROOT', dirname(RAIZ));
}
if (!defined('SPARTA_UPLOADS_ROOT')) {
    define('SPARTA_UPLOADS_ROOT', dirname(__DIR__) . '/public/uploads');
}

if (function_exists('date_default_timezone_set')) {
    @date_default_timezone_set('America/Mexico_City');
}

@ini_set('memory_limit', '1024M');
@ini_set('max_execution_time', '0');

define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('CONTROLADORES', RAIZ . '/controllers');
define('LIBRERIAS', RAIZ . '/libs');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');
define('LOGIN', 'Login');
define('VISTA_DEFECTO', 'Inicio');
define('METODO_DEFECTO', 'index');

require_once RAIZ . '/bootstrap_composer.php';
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
    if (!file_exists($ruta)) {
        throw new Exception("Autoload no encontro: $ruta");
    }
    require_once $ruta;
});

require_once RAIZ . '/config/config.php';

$args = $argv ?? [];
$once = in_array('--once', $args, true);
$max = 10;
foreach ($args as $i => $arg) {
    if ($arg === '--max' && isset($args[$i + 1])) {
        $max = max(1, min(100, (int) $args[$i + 1]));
    }
}
if ($once) {
    $max = 1;
}

$controller = new \Controllers\CapHum();
$procesados = 0;

for ($i = 0; $i < $max; $i++) {
    $res = $controller->procesarSiguienteVerificacionDocumentalJob();
    echo json_encode($res, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    if (empty($res['procesado'])) {
        break;
    }
    $procesados++;
    if ($once) {
        break;
    }
    usleep(250000);
}

exit($procesados > 0 ? 0 : 0);
