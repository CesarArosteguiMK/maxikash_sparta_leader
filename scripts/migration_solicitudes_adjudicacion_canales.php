<?php

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$db = new Database();
$columnas = [
    'vin' => 'CHAR(17) NULL AFTER motivo',
    'tipo_asignacion' => 'VARCHAR(30) NULL AFTER vin',
    'id_persona_gestor' => 'INT NULL AFTER tipo_asignacion',
    'nombre_gestor' => 'VARCHAR(180) NULL AFTER id_persona_gestor',
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
    ['tabla' => 'adj_solicitud', 'indice' => 'idx_adj_solicitud_gestor']
);
if (!$indice) {
    $db->CRUD('ALTER TABLE adj_solicitud ADD KEY idx_adj_solicitud_gestor (id_persona_gestor)');
}

echo json_encode([
    'success' => true,
    'columns' => array_keys($columnas),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
