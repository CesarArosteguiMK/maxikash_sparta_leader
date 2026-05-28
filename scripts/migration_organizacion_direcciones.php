<?php

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$db = new Database();
$sql = file_get_contents(__DIR__ . '/migration_organizacion_direcciones.sql');
if ($sql === false) {
    fwrite(STDERR, "No se pudo leer migration_organizacion_direcciones.sql\n");
    exit(1);
}

$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $statement) {
    if ($statement === '') {
        continue;
    }
    $db->CRUD($statement);
}

echo "Migracion de direcciones de organizacion aplicada.\n";
