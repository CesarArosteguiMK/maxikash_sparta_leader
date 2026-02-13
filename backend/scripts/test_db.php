<?php
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new \Core\Database();
    echo "Conexión exitosa a la base de datos (Database.php).\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>