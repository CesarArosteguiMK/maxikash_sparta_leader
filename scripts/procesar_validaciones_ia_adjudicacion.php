<?php

declare(strict_types=1);

use Models\MotosAdjudicadas;

require dirname(__DIR__) . '/backend/core/Database.php';
require dirname(__DIR__) . '/backend/core/Model.php';
require dirname(__DIR__) . '/backend/services/AnthropicMotoConditionClient.php';
require dirname(__DIR__) . '/backend/models/Adjudicacion.php';
require dirname(__DIR__) . '/backend/models/MotosAdjudicadas.php';

$limite = 20;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--limite=(\d+)$/', $arg, $m)) {
        $limite = max(1, min(100, (int) $m[1]));
    }
}

$modelo = new MotosAdjudicadas();
$pendientes = $modelo->obtenerValidacionesEstadoMotoPendientes($limite);
$resultados = [];
foreach ($pendientes as $pendiente) {
    $idOperacion = (int) ($pendiente['id_operacion'] ?? 0);
    if ($idOperacion > 0) $resultados[] = $modelo->procesarValidacionEstadoMotoClaude($idOperacion);
}

echo json_encode([
    'success' => true,
    'pendientes_encontradas' => count($pendientes),
    'resultados' => $resultados,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
