<?php

$controller = file_get_contents(dirname(__DIR__) . '/backend/controllers/CapHum.php');
if ($controller === false) {
    fwrite(STDERR, "No se pudo leer la interfaz documental de candidatos.\n");
    exit(1);
}

foreach ([
    'resumirAlertasDocumentales(entradas, documentos)',
    'firmaCartaEstructurada',
    'firmaCartaEstructurada !== true',
    'faltaFirmaExplicita',
    'faltantesCarta.push("el nombre completo")',
    'resumirAlertasDocumentales(alertas.concat(Array.isArray(v.recomendaciones) ? v.recomendaciones : []), docs)',
] as $esperado) {
    if (strpos($controller, $esperado) === false) {
        fwrite(STDERR, "Falta la separacion entre nombre y firma en el dictamen: {$esperado}.\n");
        exit(1);
    }
}

echo "CapHumCartaNoAdeudoDictamenTest OK\n";
