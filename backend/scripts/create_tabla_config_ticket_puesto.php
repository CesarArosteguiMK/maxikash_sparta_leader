<?php
/**
 * Crea la tabla config_ticket_puesto en la BD __SPARTA_SECRET_REDACTED__.
 * Asociación puesto (Sparta) -> funciones de ticket (sabueso, viaticos, etc.).
 * Ejecutar una vez:
 *
 *   php backend/scripts/create_tabla_config_ticket_puesto.php
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
putenv('DB_ESQUEMA=__SPARTA_SECRET_REDACTED__');
putenv('DB_NAME=__SPARTA_SECRET_REDACTED__');
putenv('DB_USUARIO=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_USER=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_PASSWORD=' . trim($dbConfig['PASSWORD'] ?? ''));
putenv('DB_PASS=' . trim($dbConfig['PASSWORD'] ?? ''));

require_once $raiz . '/core/Database.php';

$db = new \Core\Database();

$sql = "CREATE TABLE IF NOT EXISTS config_ticket_puesto (
    id_puesto INT UNSIGNED NOT NULL,
    funcion_ticket VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_puesto, funcion_ticket),
    INDEX idx_funcion (funcion_ticket)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->CRUD($sql);
    echo "Tabla config_ticket_puesto creada o ya existía. Listo.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al crear tabla: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
