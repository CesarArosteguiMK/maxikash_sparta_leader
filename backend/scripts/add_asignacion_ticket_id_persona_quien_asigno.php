<?php
/**
 * Añade id_persona_quien_asigno a asignacion_ticket (quién ejecutó la asignación, p. ej. jefe territorial).
 *
 * Base de datos: __SPARTA_SECRET_REDACTED__ por defecto (no [database] ESQUEMA). Ver bootstrap_db_tickets.php.
 *
 *   php backend/scripts/add_asignacion_ticket_id_persona_quien_asigno.php
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
echo "Migración asignacion_ticket → base de datos: {$esquema}\n";

try {
    $row = $db->queryOne(
        "SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'asignacion_ticket' AND COLUMN_NAME = 'id_persona_quien_asigno' LIMIT 1"
    );
    if (!empty($row)) {
        echo "La columna id_persona_quien_asigno ya existe en asignacion_ticket.\n";
        exit(0);
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Error comprobando columna: " . $e->getMessage() . "\n");
    exit(1);
}

try {
    $db->CRUD(
        'ALTER TABLE asignacion_ticket ADD COLUMN id_persona_quien_asigno INT UNSIGNED NULL DEFAULT NULL COMMENT \'Quién asignó (p. ej. jefe territorial)\' AFTER id_persona_asignada'
    );
    echo "Columna id_persona_quien_asigno agregada correctamente.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Error en ALTER TABLE: " . $e->getMessage() . "\n");
    exit(1);
}
