<?php
/**
 * Quita filas de tbl_histo_primeros_pagos (o SPARTA_PP_HISTO_TABLA) que NO entran
 * en la cartera del histórico (misma regla que PrimerosPagosHistoricoSegundometro).
 *
 * Sin argumentos: solo cuenta (dry-run).
 *   php purgar_histo_primeros_pagos_fuera_cartera.php
 * Borrar de verdad:
 *   php purgar_histo_primeros_pagos_fuera_cartera.php --execute
 * Máximo N etiquetas de semana distintas (las más recientes primero):
 *   php purgar_histo_primeros_pagos_fuera_cartera.php --execute --max=30
 * Depuración (totales por etiqueta vs cartera, sin borrar):
 *   php purgar_histo_primeros_pagos_fuera_cartera.php --verbose
 */
$projectRoot = dirname(__DIR__);
if (!defined('RAIZ')) {
    define('RAIZ', $projectRoot);
}
define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('CONTROLADORES', RAIZ . '/controllers');
define('LIBRERIAS', RAIZ . '/libs');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');

require_once dirname(__DIR__) . '/bootstrap_composer.php';
sparta_require_composer_autoload();

spl_autoload_register(function ($archivo) {
    if (strpos($archivo, 'PhpOffice\\') === 0 ||
        strpos($archivo, 'ZipStream\\') === 0 ||
        strpos($archivo, 'Psr\\') === 0) {
        return;
    }
    $archivo = str_replace('\\', '/', $archivo);
    $ruta = RAIZ . "/$archivo.php";
    if (file_exists($ruta)) {
        require_once $ruta;
    }
});

require_once RAIZ . '/config/config.php';

$execute = in_array('--execute', $argv, true);
$dry = !$execute;
$verbose = in_array('--verbose', $argv, true);
$max = null;
foreach ($argv as $a) {
    if (preg_match('/^--max=(\d+)$/', $a, $m)) {
        $max = (int) $m[1];
    }
}

$r = \Models\PrimerosPagosHistoricoSegundometro::purgarFilasFueraCarteraHistorico($dry, $max, ['verbose' => $verbose]);
echo json_encode($r, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
exit(($r['success'] ?? false) ? 0 : 1);
