<?php

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$db = new Database();
$columna = $db->queryOne(
    "SELECT 1 AS existe FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'adj_solicitud'
        AND COLUMN_NAME = 'evidencia_perfil' LIMIT 1"
);
if (!$columna) {
    $db->CRUD("ALTER TABLE adj_solicitud
               ADD COLUMN evidencia_perfil VARCHAR(30) NOT NULL DEFAULT 'etapa2_2026' AFTER canal");
}

$sql = file_get_contents(__DIR__ . '/migration_gestion_campo_etapa2.sql');
if ($sql === false) {
    fwrite(STDERR, "No se pudo leer la migración de Etapa 2.\n");
    exit(1);
}
foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    $db->CRUD($statement);
}

$tablas = $db->queryAll(
    "SELECT TABLE_NAME FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME IN ('adj_gestion_campo', 'adj_gestion_campo_evento')
      ORDER BY TABLE_NAME"
) ?: [];
echo json_encode(['success' => count($tablas) === 2, 'tablas' => array_column($tablas, 'TABLE_NAME')], JSON_PRETTY_PRINT) . PHP_EOL;

