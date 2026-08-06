<?php

$source = file_get_contents(dirname(__DIR__) . '/backend/views/cierre_credito_consulta.php');
if ($source === false) {
    fwrite(STDERR, "No se pudo leer la vista de Cierre de Credito.\n");
    exit(1);
}

foreach ([
    'id="barraGeneral-wrap"',
    'function ccActualizarVisibilidadBuscador(targetSelector)',
    "barraWrap.classList.toggle('d-none', !isNaN(total) && total === 0)",
    "if (wrap) wrap.classList.add('d-none')",
    "if (empty) empty.classList.add('d-none')",
    'cc-empty-state text-center',
] as $esperado) {
    if (strpos($source, $esperado) === false) {
        fwrite(STDERR, "Falta normalizar el estado vacio de Peticiones: {$esperado}.\n");
        exit(1);
    }
}

echo "CierreCreditoEmptyStateUiTest OK\n";
