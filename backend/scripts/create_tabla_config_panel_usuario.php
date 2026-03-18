<?php
/**
 * Crea la tabla config_panel_usuario en la BD __SPARTA_SECRET_REDACTED__.
 * Asociación usuario (persona) -> paneles admin a los que tiene acceso.
 * Ejecutar una vez:
 *
 *   php backend/scripts/create_tabla_config_panel_usuario.php
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

$sql = "CREATE TABLE IF NOT EXISTS config_panel_usuario (
    clave_panel VARCHAR(80) NOT NULL,
    id_persona INT UNSIGNED NOT NULL,
    PRIMARY KEY (clave_panel, id_persona),
    INDEX idx_clave (clave_panel),
    INDEX idx_persona (id_persona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->CRUD($sql);
    echo "Tabla config_panel_usuario creada o ya existía. Listo.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al crear tabla: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
