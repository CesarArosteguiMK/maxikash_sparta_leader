<?php

declare(strict_types=1);

require dirname(__DIR__) . '/backend/core/Database.php';
require dirname(__DIR__) . '/backend/core/Model.php';
require dirname(__DIR__) . '/backend/models/MotosAdjudicadas.php';

$idOperacion = null;
$limite = 500;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--operacion=(\d+)$/', $arg, $m)) {
        $idOperacion = (int) $m[1];
    }
    if (preg_match('/^--limite=(\d+)$/', $arg, $m)) {
        $limite = (int) $m[1];
    }
}

$modelo = new \Models\MotosAdjudicadas();
$resultado = $modelo->migrarRespaldosEvidenciasFirebase($idOperacion, $limite);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
