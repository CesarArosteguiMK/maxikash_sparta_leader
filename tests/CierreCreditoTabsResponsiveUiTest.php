<?php

$source = file_get_contents(dirname(__DIR__) . '/backend/views/cierre_credito_consulta.php');
if ($source === false) {
    fwrite(STDERR, "No se pudo leer la vista de Cierre de Credito.\n");
    exit(1);
}

foreach ([
    'flex-wrap: wrap !important;',
    'max-inline-size: 100%;',
    '.cc-nav-tabs .nav-item',
    'flex: 1 1 9.25rem;',
    'min-inline-size: 0;',
] as $esperado) {
    if (strpos($source, $esperado) === false) {
        fwrite(STDERR, "Falta la proteccion responsive de las pestanas: {$esperado}.\n");
        exit(1);
    }
}

echo "CierreCreditoTabsResponsiveUiTest OK\n";
