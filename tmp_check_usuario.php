<?php
require 'backend/bootstrap_composer.php';
$db = new Core\Database();
$r = $db->queryAll('DESCRIBE USUARIO');
foreach ($r as $row) {
    echo implode(' | ', $row) . PHP_EOL;
}
