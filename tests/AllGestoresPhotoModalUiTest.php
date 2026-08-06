<?php

$source = file_get_contents(dirname(__DIR__) . '/backend/views/all_gestores.php');
if ($source === false) {
    fwrite(STDERR, "No se pudo leer la vista de gestores.\n");
    exit(1);
}

foreach ([
    '#modalGestionFotoUsuario .modal-header .btn-close',
    'position: static !important;',
    'inset: auto !important;',
    'transform: none !important;',
    'margin: 0 0 0 auto !important;',
    'align-self: center;',
    'background-position: center;',
] as $esperado) {
    if (strpos($source, $esperado) === false) {
        fwrite(STDERR, "Falta proteger el cierre del modal de foto: {$esperado}.\n");
        exit(1);
    }
}

echo "AllGestoresPhotoModalUiTest OK\n";
