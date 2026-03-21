<?php
/**
 * Permite que id_credito sea NULL en la tabla ticket (para tickets por categoría sin crédito).
 * Ejecutar una vez:
 *
 *   php backend/scripts/alter_ticket_id_credito_null.php
 */

date_default_timezone_set('America/Mexico_City');

$raiz = dirname(__DIR__);
require_once __DIR__ . '/bootstrap_db_tickets.php';

try {
    $esquema = sparta_bootstrap_db_tickets(isset($argv) ? $argv : []);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

require_once $raiz . '/core/Database.php';

$db = new \Core\Database();
echo "ALTER ticket → base de datos: {$esquema}\n";

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
