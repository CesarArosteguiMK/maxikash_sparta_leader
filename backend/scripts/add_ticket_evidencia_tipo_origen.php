<?php
/**
 * Añade ticket_evidencia.tipo_origen para separar:
 * - adjunto_ticket: fotos al levantar ticket (validaciones, viáticos, etc.)
 * - dictamen_sabueso: evidencias del flujo dictamen en panel Sabueso
 *
 * Esta migración actúa siempre sobre la base donde viven los tickets (__SPARTA_SECRET_REDACTED__),
 * no sobre [database] ESQUEMA del config.ini (que puede ser otra, p. ej. reportes).
 *
 * CMD (Windows + XAMPP):
 *
 *   c:\xampp\php\php.exe c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\scripts\add_ticket_evidencia_tipo_origen.php
 *
 * Otro nombre de base (raro): primer argumento o variable TICKET_DB_ESQUEMA, o en config.ini
 * [database] ESQUEMA_TICKETS = "nombre"
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
echo "Migración ticket_evidencia → base de datos: {$esquema}\n";

$tablaOk = $db->queryOne(
    "SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_evidencia' LIMIT 1"
);
if (empty($tablaOk)) {
    fwrite(
        STDERR,
        "No existe ticket_evidencia en \"{$esquema}\". Revisa el nombre de la base o usa:\n"
        . "  php .../add_ticket_evidencia_tipo_origen.php nombre_bd\n"
    );
    exit(1);
}

try {
    $col = $db->queryOne(
        "SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_evidencia' AND COLUMN_NAME = 'tipo_origen' LIMIT 1"
    );
    if (empty($col)) {
        $db->CRUD(
            "ALTER TABLE ticket_evidencia ADD COLUMN tipo_origen VARCHAR(32) NULL DEFAULT NULL COMMENT 'adjunto_ticket|dictamen_sabueso' AFTER nombre_original"
        );
        echo "Columna tipo_origen añadida.\n";
    } else {
        echo "Columna tipo_origen ya existe.\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'ALTER: ' . $e->getMessage() . "\n");
    exit(1);
}

try {
    $db->CRUD(
        "UPDATE ticket_evidencia SET tipo_origen = 'dictamen_sabueso' WHERE tipo_origen IS NULL AND SUBSTRING_INDEX(ruta_archivo, '/', -1) LIKE 'ev_%'"
    );
    $db->CRUD(
        "UPDATE ticket_evidencia SET tipo_origen = 'adjunto_ticket' WHERE tipo_origen IS NULL"
    );
    echo "Backfill tipo_origen aplicado.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'UPDATE: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "Listo.\n";
