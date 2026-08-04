<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/backend/core/Controller.php';
require_once dirname(__DIR__) . '/backend/controllers/CapHum.php';

use Controllers\CapHum;

$controller = (new ReflectionClass(CapHum::class))->newInstanceWithoutConstructor();
$method = new ReflectionMethod($controller, 'docVerifNotaSinObservaciones');
$method->setAccessible(true);

$neutral = 'Revisión automática sin observaciones. Revisar identificación manualmente si lo considera necesario.';
if ($method->invoke($controller, $neutral) !== true) {
    fwrite(STDERR, "La nota historica sin observaciones todavia se interpreta como alerta.\n");
    exit(1);
}

if ($method->invoke($controller, 'No se detectó el reverso de la identificación.') !== false) {
    fwrite(STDERR, "Una observacion real no debe ocultarse.\n");
    exit(1);
}

$normalizar = new ReflectionMethod($controller, 'normalizarValidacionIaExpediente');
$normalizar->setAccessible(true);
$historica = $normalizar->invoke($controller, [
    'aceptado' => true,
    'revision_manual' => true,
    'notas' => [$neutral],
]);
if (($historica['revision_manual'] ?? null) !== false) {
    fwrite(STDERR, "La bandera historica de revision manual no se neutralizo.\n");
    exit(1);
}

$conHallazgo = $normalizar->invoke($controller, [
    'aceptado' => true,
    'revision_manual' => true,
    'notas' => [$neutral, 'No se detecto el reverso de la identificacion.'],
]);
if (($conHallazgo['revision_manual'] ?? null) !== true) {
    fwrite(STDERR, "Una observacion real debe conservar la revision manual.\n");
    exit(1);
}

$source = file_get_contents(dirname(__DIR__) . '/backend/controllers/CapHum.php');
if ($source === false || strpos($source, 'claveDocModalGlobal(s).indexOf("REVISION AUTOMATICA SIN OBSERVACIONES") !== -1') === false) {
    fwrite(STDERR, "La interfaz no filtra la nota historica sin observaciones.\n");
    exit(1);
}

if (strpos($source, 'esRevisionManualHistoricaSinObservaciones(x)') === false) {
    fwrite(STDERR, "La interfaz no neutraliza la bandera manual historica.\n");
    exit(1);
}

echo "CapHumIdentificationUiTest OK\n";
