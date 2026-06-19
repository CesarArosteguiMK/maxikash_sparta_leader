<?php
error_reporting(E_ERROR | E_PARSE);
require_once __DIR__ . '/backend/config/config.php';
define('RAIZ', __DIR__ . '/backend');
define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));

spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $parts = explode('\\', $class);
    $base = array_shift($parts);
    $file = implode('/', $parts) . '.php';
    $map = [
        'Core' => RAIZ . '/core/' . $file,
        'Models' => RAIZ . '/models/' . $file,
    ];
    if (isset($map[$base]) && is_file($map[$base])) {
        require_once $map[$base];
    }
});

$db = new \Core\Database();
$row = $db->queryOne("SELECT token, id_candidato FROM candidato_documento_token ORDER BY id DESC LIMIT 1");
echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
