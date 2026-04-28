<?php
/**
 * Borra **todas** las filas de una etiqueta `SEMANA` en `tbl_histo_primeros_pagos` (o `SPARTA_PP_HISTO_TABLA`).
 *
 * Caso de uso: ya existía «Semana 17-2026» en destino y se quiere **reemplazar** con un nuevo volcado
 * desde `tbl_segundometro_histo`. El purge `purgar_histo_primeros_pagos_fuera_cartera.php` solo quita
 * filas fuera de la ventana de cartera, no borra filas que el reporte sigue considerando válidas.
 *
 * Solo cuenta (dry-run):
 *   php borrar_semana_histo_primeros_pagos.php --semana="Semana 17-2026"
 * Borrar de verdad:
 *   php borrar_semana_histo_primeros_pagos.php --semana="Semana 17-2026" --execute
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

$semana = null;
foreach ($argv as $a) {
    if (preg_match('/^--semana=(.+)$/u', $a, $m) === 1) {
        $semana = $m[1];
        break;
    }
}
if ($semana === null || trim($semana) === '') {
    fwrite(STDERR, "Indique --semana=\"Semana NN-AAAA\" (ej. --semana=\"Semana 17-2026\"). Opcional: --execute para borrar.\n");
    exit(1);
}

$execute = in_array('--execute', $argv, true);
$dry = !$execute;

$r = \Models\PrimerosPagosHistoricoSegundometro::borrarTodasLasFilasPorEtiquetaSemana($semana, $dry);
echo json_encode($r, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
exit(($r['success'] ?? false) ? 0 : 1);
