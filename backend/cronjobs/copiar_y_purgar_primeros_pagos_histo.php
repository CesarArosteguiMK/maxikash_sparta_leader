<?php
/**
 * Una sola corrida: (1) copia desde `tbl_segundometro_histo` → `tbl_histo_primeros_pagos`
 * (solo lectura en origen); (2) purga en destino filas fuera de cartera.
 *
 * Simulación (solo conteos de copia; no inserta ni purga):
 *   php copiar_y_purgar_primeros_pagos_histo.php --semana="Semana 18-2026"
 * Ejecutar copia + purga:
 *   php copiar_y_purgar_primeros_pagos_histo.php --semana="Semana 18-2026" --execute
 * Reemplazar en destino esas semanas y luego purgar:
 *   php copiar_y_purgar_primeros_pagos_histo.php --semana="Semana 18-2026" --execute --replace-dest
 * Varias semanas:
 *   php copiar_y_purgar_primeros_pagos_histo.php --semana="Semana 17-2026" --semana="Semana 18-2026" --execute
 * Lista ejemplo (16…12 2026):
 *   php copiar_y_purgar_primeros_pagos_histo.php --lista-ejemplo-2026 --execute
 *
 * Opciones del purge (solo con --execute):
 *   --max=30   --verbose
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
$verbose = in_array('--verbose', $argv, true);
$max = null;
foreach ($argv as $a) {
    if (preg_match('/^--max=(\d+)$/', $a, $m)) {
        $max = (int) $m[1];
    }
}

if ($semanas === []) {
    fwrite(STDERR, "Uso: --semana=\"Semana NN-AAAA\" (repetible) y/o --lista-ejemplo-2026. Añada --execute para copiar y purgar. Opcional: --replace-dest, --max=N, --verbose.\n");
    exit(1);
}

$salida = [
    'pipeline' => 'copia_segundometro_histo_primeros_pagos_luego_purga_fuera_cartera',
    'execute' => $execute,
];

$copia = \Models\PrimerosPagosHistoricoSegundometro::copiarDesdeSegundometroHistoHaciaPrimerosPagos(
    $semanas,
    !$execute,
    ['replace_dest' => $replaceDest]
);
$salida['copia'] = $copia;

if (!($copia['success'] ?? false)) {
    echo json_encode($salida, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

if (!$execute) {
    $salida['purga'] = null;
    $salida['nota'] = 'Sin --execute: solo simulación de copia. Con --execute se inserta y después se ejecuta la purga en destino.';
    echo json_encode($salida, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    exit(0);
}

$purga = \Models\PrimerosPagosHistoricoSegundometro::purgarFilasFueraCarteraHistorico(false, $max, ['verbose' => $verbose]);
$salida['purga'] = $purga;

echo json_encode($salida, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
exit(($purga['success'] ?? false) ? 0 : 1);
