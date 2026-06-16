<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/DatabaseCliSupport.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/DatabaseMaxiProd.php';
require_once __DIR__ . '/../models/Atlas.php';

$dryRun = !in_array('--run', $argv ?? [], true);
$inicio = microtime(true);
$input = ['dry_run' => $dryRun ? 1 : 0];
foreach (($argv ?? []) as $arg) {
    if (preg_match('/^--limit=(\d+)$/', $arg, $m) || preg_match('/^--lote=(\d+)$/', $arg, $m)) {
        $input['limit'] = (int)$m[1];
    } elseif (preg_match('/^--after_id=(\d+)$/', $arg, $m)) {
        $input['after_id'] = (int)$m[1];
    } elseif ($arg === '--reset-cursor') {
        $input['reset_cursor'] = 1;
    }
}

$resultado = \Models\Atlas::sincronizarCreditosOfertaMexico($input);
$datos = $resultado['datos'] ?? [];
$datos['duracion_segundos'] = round(microtime(true) - $inicio, 3);
$datos['modo'] = $dryRun ? 'dry_run' : 'ejecucion';

echo json_encode([
    'success' => (bool)($resultado['success'] ?? false),
    'mensaje' => $resultado['mensaje'] ?? '',
    'datos' => $datos,
    'error' => $resultado['error'] ?? null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;

exit(($resultado['success'] ?? false) ? 0 : 1);
