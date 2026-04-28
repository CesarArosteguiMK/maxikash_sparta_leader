<?php
/**
 * Copia filas desde `tbl_segundometro_histo` hacia `tbl_histo_primeros_pagos` (o SPARTA_PP_HISTO_TABLA).
 * Solo lectura en origen; escritura en destino. Mapeo = SQL operativo acordado (subset de columnas + fechas).
 *
 * Semanas (repetible):
 *   php copiar_segundometro_histo_a_primeros_pagos.php --semana="Semana 16-2026" --semana="Semana 15-2026"
 * Lista de ejemplo (16…12 2026):
 *   php copiar_segundometro_histo_a_primeros_pagos.php --lista-ejemplo-2026
 * Simulación (conteos, sin INSERT):
 *   php copiar_segundometro_histo_a_primeros_pagos.php --lista-ejemplo-2026
 * Ejecutar copia (falla si ya hay filas en destino para esas semanas, salvo --replace-dest):
 *   php copiar_segundometro_histo_a_primeros_pagos.php --lista-ejemplo-2026 --execute
 * Reemplazar en destino las mismas etiquetas y volver a insertar:
 *   php copiar_segundometro_histo_a_primeros_pagos.php --semana="Semana 17-2026" --execute --replace-dest
 *
 * Después, recortar a cartera: `purgar_histo_primeros_pagos_fuera_cartera.php --execute`.
 * Todo en uno: `copiar_y_purgar_primeros_pagos_histo.php`.
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

$semanas = [];
foreach ($argv as $a) {
    if (preg_match('/^--semana=(.+)$/u', $a, $m) === 1) {
        $semanas[] = $m[1];
    }
}
if (in_array('--lista-ejemplo-2026', $argv, true)) {
    $semanas = array_merge($semanas, [
        'Semana 16-2026',
        'Semana 15-2026',
        'Semana 14-2026',
        'Semana 13-2026',
        'Semana 12-2026',
    ]);
}

$execute = in_array('--execute', $argv, true);
$replaceDest = in_array('--replace-dest', $argv, true);
$dry = !$execute;

if ($semanas === []) {
    fwrite(STDERR, "Indique --semana=\"Semana NN-AAAA\" (repetible) o --lista-ejemplo-2026. Opcional: --execute, --replace-dest.\n");
    exit(1);
}

$r = \Models\PrimerosPagosHistoricoSegundometro::copiarDesdeSegundometroHistoHaciaPrimerosPagos($semanas, $dry, [
    'replace_dest' => $replaceDest,
]);
echo json_encode($r, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
exit(($r['success'] ?? false) ? 0 : 1);
