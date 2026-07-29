<?php

require_once __DIR__ . '/../backend/core/Model.php';
require_once __DIR__ . '/../backend/models/MotosAdjudicadas.php';

use Models\MotosAdjudicadas;

$reflection = new ReflectionClass(MotosAdjudicadas::class);
$model = $reflection->newInstanceWithoutConstructor();

$casos = [
    'reporte textual confirmado' => [
        'payload' => ['repuve_respuesta_api' => ['resultado' => ['data' => ['vehicle' => ['reporteDeRobo' => 'Con reporte de robo vigente']]]]],
        'esperado' => true,
    ],
    'bandera booleana confirmada' => [
        'payload' => ['repuve_respuesta_api' => ['resultado' => ['data' => ['vehicle' => ['vehiculoRobado' => true]]]]],
        'esperado' => true,
    ],
    'sin reporte de robo' => [
        'payload' => ['repuve_respuesta_api' => ['resultado' => ['data' => ['vehicle' => ['reporteDeRobo' => 'Sin reporte de robo']]]]],
        'esperado' => false,
    ],
    'confirmacion posterior prevalece' => [
        'payload' => ['repuve_respuesta_api' => ['resultado' => [
            'message' => 'Sin reporte en consulta previa',
            'data' => ['vehicle' => ['estatusRobo' => 'Reporte de robo activo']],
        ]]],
        'esperado' => true,
    ],
    'caida del proveedor' => [
        'payload' => ['repuve_respuesta_api' => ['resultado' => ['estado' => 'ERROR', 'message' => 'Service unavailable']]],
        'esperado' => false,
    ],
];

foreach ($casos as $nombre => $caso) {
    $resultado = $model->analizarReporteRoboRepuve($caso['payload']);
    if (($resultado['confirmado'] ?? null) !== $caso['esperado']) {
        fwrite(STDERR, "Fallo en {$nombre}: " . json_encode($resultado) . PHP_EOL);
        exit(1);
    }
}

echo "RepuveReporteRoboTest OK\n";
