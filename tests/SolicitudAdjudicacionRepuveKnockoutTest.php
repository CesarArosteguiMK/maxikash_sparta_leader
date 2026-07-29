<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/core/Controller.php';
require_once __DIR__ . '/../backend/controllers/SolicitudAdjudicacion.php';

use Controllers\SolicitudAdjudicacion;

$reflection = new ReflectionClass(SolicitudAdjudicacion::class);
$controller = $reflection->newInstanceWithoutConstructor();
$clasificar = $reflection->getMethod('clasificarResultadoRepuve');
$clasificar->setAccessible(true);

$casos = [
    'sin reporte de robo permite continuar' => [
        ['repuve' => ['estado' => 'OK', 'mensaje' => 'Sin reporte de robo']],
        'SIN_REPORTE_ROBO',
        false,
    ],
    'consulta en proceso no permite avanzar' => [
        ['repuve' => ['estado' => 'PROCESANDO']],
        'PENDIENTE_REPUVE',
        false,
    ],
    'error obliga revision manual' => [
        ['repuve' => ['estado' => 'ERROR'], 'message' => 'Servicio no exitoso'],
        'VALIDACION_MANUAL_REPUVE',
        true,
    ],
];

foreach ($casos as $nombre => [$payload, $estadoEsperado, $manualEsperado]) {
    $resultado = $clasificar->invoke($controller, $payload);
    if (($resultado['estado'] ?? '') !== $estadoEsperado
        || (bool) ($resultado['requiere_validacion_manual'] ?? false) !== $manualEsperado) {
        fwrite(STDERR, "Fallo en {$nombre}: " . json_encode($resultado) . PHP_EOL);
        exit(1);
    }
}

echo "SolicitudAdjudicacionRepuveKnockoutTest OK\n";
