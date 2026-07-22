<?php

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$ruta = __DIR__ . '/migration_solicitudes_adjudicacion_etapa1.sql';
$sql = file_get_contents($ruta);
if ($sql === false) {
    fwrite(STDERR, "No se pudo leer {$ruta}.\n");
    exit(1);
}

$db = new Database();
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if ($statement !== '') {
        $db->CRUD($statement);
    }
}

$version = $db->queryOne('SELECT VERSION() AS version_mysql');
$tablas = $db->queryAll(
    "SELECT TABLE_NAME
       FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME IN ('adj_solicitud', 'adj_solicitud_historial')
      ORDER BY TABLE_NAME"
);

echo json_encode([
    'success' => count($tablas ?: []) === 2,
    'version_mysql' => $version['version_mysql'] ?? null,
    'tablas' => array_column($tablas ?: [], 'TABLE_NAME'),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;

