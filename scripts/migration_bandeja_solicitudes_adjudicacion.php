<?php

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$db = new Database();
$columnas = [
    'id_persona_asignada' => 'INT NULL AFTER nombre_gestor',
    'nombre_persona_asignada' => 'VARCHAR(180) NULL AFTER id_persona_asignada',
    'asignada_por' => 'INT NULL AFTER nombre_persona_asignada',
    'asignada_por_nombre' => 'VARCHAR(180) NULL AFTER asignada_por',
    'fecha_asignacion' => 'DATETIME NULL AFTER asignada_por_nombre',
    'comentario_asignacion' => 'VARCHAR(1000) NULL AFTER fecha_asignacion',
];

foreach ($columnas as $nombre => $definicion) {
    $existe = $db->queryOne(
        'SELECT 1
           FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :tabla
            AND COLUMN_NAME = :columna
          LIMIT 1',
        ['tabla' => 'adj_solicitud', 'columna' => $nombre]
    );
    if (!$existe) {
        $db->CRUD("ALTER TABLE adj_solicitud ADD COLUMN {$nombre} {$definicion}");
    }
}

$indice = $db->queryOne(
    'SELECT 1
       FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :tabla
        AND INDEX_NAME = :indice
      LIMIT 1',
    ['tabla' => 'adj_solicitud', 'indice' => 'idx_adj_solicitud_asignada']
);
if (!$indice) {
    $db->CRUD(
        'ALTER TABLE adj_solicitud
         ADD KEY idx_adj_solicitud_asignada (id_persona_asignada, estatus)'
    );
}

echo json_encode([
    'success' => true,
    'columns' => array_keys($columnas),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
