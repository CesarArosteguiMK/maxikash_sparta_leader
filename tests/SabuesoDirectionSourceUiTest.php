<?php

$script = file_get_contents(dirname(__DIR__) . '/backend/controllers/SabuesoPaneladminScriptChunk.php');
$controller = file_get_contents(dirname(__DIR__) . '/backend/controllers/Sabueso.php');

if ($script === false || $controller === false) {
    fwrite(STDERR, "No se pudo leer el codigo de Sabueso.\n");
    exit(1);
}

foreach ([
    "Direcci\\u00f3n registrada en MaxiProd",
    "Archivo de INE no utilizado",
    "fuentes.indexOf('INE / persona') !== -1",
] as $esperado) {
    if (strpos($script, $esperado) === false) {
        fwrite(STDERR, "Falta distinguir la fuente de la direccion en la interfaz: {$esperado}.\n");
        exit(1);
    }
}

if (strpos($controller, "'ine_persona', 'MaxiProd / persona'") === false) {
    fwrite(STDERR, "El backend no identifica MaxiProd como fuente de la direccion de persona.\n");
    exit(1);
}

if (strpos($script, "return 'Direcci\\u00f3n del INE'") !== false) {
    fwrite(STDERR, "La interfaz todavia presenta la direccion de MaxiProd como lectura del INE.\n");
    exit(1);
}

echo "SabuesoDirectionSourceUiTest OK\n";
