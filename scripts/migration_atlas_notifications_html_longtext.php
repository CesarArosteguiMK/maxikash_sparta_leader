<?php
require __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require __DIR__ . '/../backend/core/Database.php';

$db = new Core\Database();

$db->CRUD("
    ALTER TABLE atlas_notifications
    MODIFY COLUMN html LONGTEXT NULL
");

echo "Migracion Atlas notifications html LONGTEXT completada.\n";
