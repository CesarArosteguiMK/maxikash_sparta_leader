<?php
require_once __DIR__ . '/backend/core/Database.php';
$db = new \Core\Database();

function out($title, $rows) {
    echo "\n== {$title} ==\n";
    foreach (($rows ?: []) as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    }
}

out('operacion', $db->queryAll(
    'SELECT id, folio, id_credito, estatus, fecha_alta, fecha_actualizacion
       FROM adj_operacion
      WHERE id_credito = 1619959
      ORDER BY id DESC'
));
out('bitacora', $db->queryAll(
    'SELECT b.id, b.accion, b.fecha_alta
       FROM adj_bitacora b
       JOIN adj_operacion o ON o.id = b.id_operacion
      WHERE o.id_credito = 1619959
      ORDER BY b.id DESC'
));
out('evidencias count', $db->queryAll(
    'SELECT COUNT(*) AS c
       FROM adj_evidencia e
       JOIN adj_operacion o ON o.id = e.id_operacion
      WHERE o.id_credito = 1619959'
));
