<?php

require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$db = new Database();
$tabla = 'asignacion_ticket';
$indexName = 'idx_asig_ticket_persona_fecha';
$targetCols = ['id_ticket', 'id_persona_asignada', 'fecha_asignacion'];

try {
    $rows = $db->queryAll("SHOW INDEX FROM {$tabla}");
} catch (\Exception $e) {
    fwrite(STDERR, "Error al leer índices de {$tabla}: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$indexes = [];
foreach ($rows as $r) {
    $keyName = isset($r['Key_name']) ? (string)$r['Key_name'] : '';
    $seq = isset($r['Seq_in_index']) ? (int)$r['Seq_in_index'] : 0;
    $col = isset($r['Column_name']) ? (string)$r['Column_name'] : '';
    if ($keyName === '' || $seq < 1 || $col === '') {
        continue;
    }
    if (!isset($indexes[$keyName])) {
        $indexes[$keyName] = [];
    }
    $indexes[$keyName][$seq] = $col;
}

foreach ($indexes as $name => $colsBySeq) {
    ksort($colsBySeq);
    $cols = array_values($colsBySeq);
    if ($cols === $targetCols) {
        echo "OK: ya existe un índice compuesto equivalente ({$name}) en {$tabla}." . PHP_EOL;
        exit(0);
    }
}

try {
    $db->CRUD(
        "ALTER TABLE {$tabla} ADD INDEX {$indexName} (id_ticket, id_persona_asignada, fecha_asignacion)"
    );
    echo "OK: índice {$indexName} creado en {$tabla}." . PHP_EOL;
    exit(0);
} catch (\Exception $e) {
    fwrite(STDERR, "Error al crear índice {$indexName}: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

