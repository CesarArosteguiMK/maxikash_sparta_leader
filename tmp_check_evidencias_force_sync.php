<?php
require_once __DIR__ . '/backend/core/Database.php';
require_once __DIR__ . '/backend/core/DatabaseLegacy.php';
require_once __DIR__ . '/backend/core/Model.php';
require_once __DIR__ . '/backend/models/Adjudicacion.php';
require_once __DIR__ . '/backend/models/MotosAdjudicadas.php';
require_once __DIR__ . '/backend/models/AtencionClientes.php';

$stamp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta_madj_dictums_sync.stamp';
@touch($stamp);

$model = new \Models\AtencionClientes();
$rows = $model->obtenerRecibidos(true);
$found = null;
foreach ($rows as $row) {
    if ((int) ($row['id_credito'] ?? 0) === 1619959) {
        $found = $row;
        break;
    }
}

echo $found ? json_encode($found, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n" : "not found\n";
