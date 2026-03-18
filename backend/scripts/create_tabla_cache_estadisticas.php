<?php
/**
 * Crea la tabla cache_estadisticas en la BD indicada en config.ini [database] ESQUEMA
 * (misma que usa la app web). Ejecutar una vez:
 *
 *   php backend/scripts/create_tabla_cache_estadisticas.php
 *
 * La tabla permite reutilizar reporte semanal y detalle de gestor sin volver a llamar
 * la API de estado de cuenta. Si la tabla no existe, el código usa cache en disco como fallback.
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
// Usar mismo esquema que la app (persona, ticket, etc.). La app por defecto usa __SPARTA_SECRET_REDACTED__;
// config.ini ESQUEMA puede ser otra BD (ej. __SPARTA_SECRET_REDACTED__), por eso aquí forzamos __SPARTA_SECRET_REDACTED__.
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

$sql = "CREATE TABLE IF NOT EXISTS cache_estadisticas (
    cache_key VARCHAR(64) NOT NULL PRIMARY KEY,
    cache_value LONGTEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->CRUD($sql);
    echo "Tabla cache_estadisticas creada o ya existía. Listo.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al crear tabla: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
