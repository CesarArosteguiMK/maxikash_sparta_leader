<?php

$root = dirname(__DIR__);
$controller = file_get_contents($root . '/backend/controllers/CapHum.php');
$model = file_get_contents($root . '/backend/models/Candidatos.php');

if ($controller === false || $model === false) {
    fwrite(STDERR, "No se pudieron leer los archivos del flujo de reevaluacion.\n");
    exit(1);
}

$checks = [
    'bloqueo de solicitudes duplicadas' => 'if (!idC || docModalReevaluationRequestInFlight) return;',
    'bloqueo de todos los botones visibles' => 'document.querySelectorAll("#modalDocumentacionCandidato .btn-reintentar-verif-expediente',
    'boton deshabilitado durante el proceso' => 'disabled title=\"La reevaluacion esta en proceso\"',
    'espera compatible con una lectura documental larga' => 'DOC_MODAL_PROCESS_TIMEOUT_MS = 10 * 60 * 1000',
    'fallo real cuando no arranca el worker' => "\$res['success'] = false;",
    'validacion del codigo de salida del lanzador' => 'return $exitCode === 0;',
    'recuperacion de documentos guardados en BD' => 'resolverRutaDocumentoCandidatoParaAnalisis',
    'limpieza de copias temporales' => 'array_unique($temporalesAnalisis)',
];

foreach ($checks as $descripcion => $needle) {
    if (strpos($controller, $needle) === false) {
        fwrite(STDERR, "Falta proteccion: {$descripcion}.\n");
        exit(1);
    }
}

if (strpos($model, 'tomarSiguienteJobVerificacionDocumental(int $staleMinutes = 15)') === false) {
    fwrite(STDERR, "El worker todavia puede duplicar trabajos documentales largos.\n");
    exit(1);
}

echo "CapHumReevaluationFlowTest OK\n";
