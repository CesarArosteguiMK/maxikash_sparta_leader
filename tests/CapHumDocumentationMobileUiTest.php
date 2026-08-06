<?php

$view = file_get_contents(dirname(__DIR__) . '/backend/views/candidatos.php');
$controller = file_get_contents(dirname(__DIR__) . '/backend/controllers/CapHum.php');
if ($view === false || $controller === false) {
    fwrite(STDERR, "No se pudo leer la interfaz de documentacion de candidatos.\n");
    exit(1);
}

foreach ([
    'modal-dialog-scrollable modal-documentacion-dialog',
    'width: 100%;',
    'max-width: 100%;',
    'height: 100dvh;',
    '.doc-candidato-item',
    'flex-direction: column;',
    'overflow-wrap: anywhere;',
    'width: 42px;',
] as $esperado) {
    if (strpos($view, $esperado) === false) {
        fwrite(STDERR, "Falta una regla responsive del modal: {$esperado}.\n");
        exit(1);
    }
}

foreach ([
    'doc-candidato-item',
    'doc-candidato-title-row',
    'doc-candidato-status',
    'doc-candidato-actions',
    'doc-candidato-pendiente',
] as $esperado) {
    if (strpos($controller, $esperado) === false) {
        fwrite(STDERR, "Falta estructura movil en una tarjeta documental: {$esperado}.\n");
        exit(1);
    }
}

if (strpos($view, 'style="max-width: 85%;"') !== false) {
    fwrite(STDERR, "El ancho de escritorio sigue forzado en celulares.\n");
    exit(1);
}

echo "CapHumDocumentationMobileUiTest OK\n";
