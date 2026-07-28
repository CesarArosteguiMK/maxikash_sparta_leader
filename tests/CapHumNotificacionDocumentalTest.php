<?php

require_once __DIR__ . '/../backend/core/Model.php';
require_once __DIR__ . '/../backend/models/CapHumNotificacionDocumental.php';

use Models\CapHumNotificacionDocumental;

$esperados = [
    [2026, 1, 'Semanas cotizadas 2026 - 1 semestre'],
    [2026, 2, 'Semanas cotizadas 2026 - 2 semestre'],
    [2027, 1, 'Semanas cotizadas 2027 - 1 semestre'],
];

foreach ($esperados as [$anio, $semestre, $esperado]) {
    $actual = CapHumNotificacionDocumental::nombrePeriodo($anio, $semestre);
    if ($actual !== $esperado) {
        fwrite(STDERR, "Nombre incorrecto: {$actual}\n");
        exit(1);
    }
}

echo "CapHumNotificacionDocumentalTest OK\n";
