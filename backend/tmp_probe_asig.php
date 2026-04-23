<?php

require __DIR__ . '/core/DatabaseCliSupport.php';
require __DIR__ . '/core/Database.php';
require __DIR__ . '/core/DatabaseLegacy.php';
require __DIR__ . '/core/DatabaseSegundometro.php';
require __DIR__ . '/models/AsignacionTablero.php';

try {
    echo "paso: iniciar\n";
    flush();
    $d1 = new \Core\DatabaseLegacy();
    echo "paso: legacy ok\n";
    flush();
    $d2 = new \Core\Database();
    echo "paso: __SPARTA_SECRET_REDACTED__ ok\n";
    flush();
    $d3 = new \Core\DatabaseSegundometro();
    echo "paso: mega ok\n";
    flush();
    $r = \Models\AsignacionTablero::obtenerPortafolioAutomatico();
    $filas = is_array($r['filas'] ?? null) ? count($r['filas']) : -1;
    echo "OK filas={$filas}\n";
} catch (\Throwable $e) {
    echo "ERR: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

