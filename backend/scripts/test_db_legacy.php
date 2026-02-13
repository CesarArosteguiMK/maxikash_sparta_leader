<?php
require_once __DIR__ . '/../core/DatabaseLegacy.php';

try {
    $db = new \Core\DatabaseLegacy();
    echo "Conexión exitosa a la base de datos.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>