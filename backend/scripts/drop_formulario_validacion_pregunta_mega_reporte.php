<?php
/**
 * Elimina formulario_validacion_pregunta solo en la BD __SPARTA_SECRET_REDACTED__ (no debe vivir ahí).
 * Ejecutar una vez:
 *   php backend/scripts/drop_formulario_validacion_pregunta_mega_reporte.php
 */
date_default_timezone_set('America/Mexico_City');
$raiz = dirname(__DIR__);
$config = @parse_ini_file($raiz . '/config/config.ini', true);
if (empty($config['database'])) {
    fwrite(STDERR, "Error: config.ini [database]\n");
    exit(1);
}
$d = $config['database'];
$host = trim($d['SERVIDOR'] ?? '');
$port = trim($d['PUERTO'] ?? '3306');
$user = trim($d['USUARIO'] ?? '');
$pass = trim($d['PASSWORD'] ?? '');
$dbname = '__SPARTA_SECRET_REDACTED__';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec('DROP TABLE IF EXISTS formulario_validacion_pregunta');
    echo "OK: DROP TABLE formulario_validacion_pregunta en {$dbname}.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
exit(0);
