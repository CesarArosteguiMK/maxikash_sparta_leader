<?php

require dirname(__DIR__) . '/vendor/autoload.php';
define('RAIZ', dirname(__DIR__) . '/backend');
spl_autoload_register(static function (string $class): void {
    $path = RAIZ . '/' . str_replace('\\', '/', $class) . '.php';
    if (is_readable($path)) require_once $path;
});

$_SESSION = [];
$path = 'C:/Users/amigo_j9s4pcx/Downloads/Templete.xlsx';
$token = 'dryrun';
$_SESSION['leonidas_spreadsheet_uploads'][$token] = [
    'actor_id' => 878,
    'nombre' => basename($path),
    'ruta' => $path,
    'hash' => hash_file('sha256', $path),
    'expira_en' => time() + 600,
];
$service = new Services\LeonidasSpreadsheetService([
    'estructura_importar' => static fn(array $rows, int $actor, bool $apply): array => [
        'success' => true,
        'datos' => ['detalles' => []],
    ],
]);
$response = $service->analizar($token, 'actualiza la estructura', [
    'actor_id' => 878,
    'permisos_agente' => ['estructura' => true],
]);
echo json_encode([
    'mensaje' => $response['mensaje'],
    'total' => $response['reporte']['total'],
    'propuesta' => isset($response['propuesta_especificacion']),
    'problemas' => array_values(array_filter(
        $response['reporte']['filas'],
        static fn(array $row): bool => $row['estado'] === 'error'
    )),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
