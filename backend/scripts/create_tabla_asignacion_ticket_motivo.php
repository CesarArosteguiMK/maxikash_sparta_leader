<?php
/**
 * Crea tabla para guardar el "motivo" en cada reasignación de gestor.
 *
 * Base: __SPARTA_SECRET_REDACTED__ por defecto (no [database] ESQUEMA). Ver bootstrap_db_tickets.php.
 *
 * Ejecutar una vez:
 *   php backend/scripts/create_tabla_asignacion_ticket_motivo.php
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
echo "Tabla asignacion_ticket_motivo → base de datos: {$esquema}\n";

$sql1 = "CREATE TABLE IF NOT EXISTS asignacion_ticket_motivo (
    id_motivo INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_asignacion_ticket INT UNSIGNED NOT NULL,
    id_persona_capo INT UNSIGNED NOT NULL,
    id_persona_gestor INT UNSIGNED NOT NULL,
    campo VARCHAR(10) DEFAULT NULL,
    motivo TEXT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_asignacion (id_asignacion_ticket),
    INDEX idx_capo (id_persona_capo),
    INDEX idx_gestor (id_persona_gestor),
    INDEX idx_campo (campo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->CRUD($sql1);
    echo "Tabla asignacion_ticket_motivo creada o ya existía.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Error al crear tabla asignacion_ticket_motivo: " . $e->getMessage() . "\n");
    exit(1);
}
