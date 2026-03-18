<?php
/**
 * Permite que id_credito sea NULL en la tabla ticket (para tickets por categoría sin crédito).
 * Ejecutar una vez:
 *
 *   php backend/scripts/alter_ticket_id_credito_null.php
 */

date_default_timezone_set('America/Mexico_City');

$raiz = dirname(__DIR__);
$configFile = $raiz . '/config/config.ini';
if (!is_file($configFile)) {
    fwrite(STDERR, "Error: No se encontró config.ini\n");
    exit(1);
}
$config = @parse_ini_file($configFile, true);
if (empty($config['database'])) {
    fwrite(STDERR, "Error: No existe sección [database] en config.ini\n");
    exit(1);
}
$dbConfig = $config['database'];
putenv('DB_SERVIDOR=' . trim($dbConfig['SERVIDOR'] ?? ''));
putenv('DB_ESQUEMA=' . trim($dbConfig['ESQUEMA'] ?? '__SPARTA_SECRET_REDACTED__'));
putenv('DB_USUARIO=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_PASSWORD=' . trim($dbConfig['PASSWORD'] ?? ''));

require_once $raiz . '/core/Database.php';

$db = new \Core\Database();

// Permitir NULL en id_credito (tickets por categoría plantilla, viáticos, etc. no llevan crédito).
// Si su columna es INT (no UNSIGNED), use: MODIFY COLUMN id_credito INT NULL DEFAULT NULL
$sql = "ALTER TABLE ticket MODIFY COLUMN id_credito INT UNSIGNED NULL DEFAULT NULL";

try {
    $db->CRUD($sql);
    echo "Columna id_credito en ticket ahora acepta NULL. Listo.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
