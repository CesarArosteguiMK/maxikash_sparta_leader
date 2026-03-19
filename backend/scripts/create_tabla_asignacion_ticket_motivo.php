<?php
/**
 * Crea tabla para guardar el "motivo" en cada reasignación de gestor.
 *
 * Ejecutar una vez:
 *   php backend/scripts/create_tabla_asignacion_ticket_motivo.php
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
putenv('DB_HOST=' . trim($dbConfig['SERVIDOR'] ?? ''));
putenv('DB_PUERTO=' . trim($dbConfig['PUERTO'] ?? '3306'));

// En este proyecto, la mayoría de tablas usan el esquema/DB "__SPARTA_SECRET_REDACTED__".
putenv('DB_ESQUEMA=' . trim($dbConfig['ESQUEMA'] ?? '__SPARTA_SECRET_REDACTED__'));
putenv('DB_NAME=' . trim($dbConfig['ESQUEMA'] ?? '__SPARTA_SECRET_REDACTED__'));
putenv('DB_USUARIO=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_USER=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_PASSWORD=' . trim($dbConfig['PASSWORD'] ?? ''));
putenv('DB_PASS=' . trim($dbConfig['PASSWORD'] ?? ''));

require_once $raiz . '/core/Database.php';

$db = new \Core\Database();

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

echo "Listo.\n";
exit(0);

