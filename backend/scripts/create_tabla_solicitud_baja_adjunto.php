<?php
/**
 * Crea la tabla solicitud_baja_adjunto para múltiples archivos por solicitud de baja.
 * Ejecutar una vez:
 *
 *   php backend/scripts/create_tabla_solicitud_baja_adjunto.php
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
$esquema = trim($dbConfig['ESQUEMA'] ?? '__SPARTA_SECRET_REDACTED__');
putenv('DB_SERVIDOR=' . trim($dbConfig['SERVIDOR'] ?? ''));
putenv('DB_ESQUEMA=' . $esquema);
putenv('DB_USUARIO=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_PASSWORD=' . trim($dbConfig['PASSWORD'] ?? ''));

require_once $raiz . '/core/Database.php';

$db = new \Core\Database();

$sql = "CREATE TABLE IF NOT EXISTS solicitud_baja_adjunto (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_solicitud_baja INT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    INDEX idx_solicitud (id_solicitud_baja)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->CRUD($sql);
    echo "Tabla solicitud_baja_adjunto creada o ya existía. Listo.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al crear tabla: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
