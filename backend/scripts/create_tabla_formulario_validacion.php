<?php
/**
 * Crea la tabla formulario_validacion y agrega id_formulario a formulario_validacion_pregunta.
 * Ejecutar una vez:
 *   php backend/scripts/create_tabla_formulario_validacion.php
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

$sql1 = "CREATE TABLE IF NOT EXISTS formulario_validacion (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    id_persona_creador INT UNSIGNED DEFAULT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo),
    INDEX idx_creador (id_persona_creador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->CRUD($sql1);
    echo "Tabla formulario_validacion creada o ya existía.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al crear formulario_validacion: " . $e->getMessage() . "\n");
    exit(1);
}

// Agregar id_formulario a formulario_validacion_pregunta si no existe
try {
    $col = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'formulario_validacion_pregunta' AND COLUMN_NAME = 'id_formulario' LIMIT 1");
    if (empty($col)) {
        $db->CRUD("ALTER TABLE formulario_validacion_pregunta ADD COLUMN id_formulario INT UNSIGNED DEFAULT NULL AFTER id, ADD INDEX idx_id_formulario (id_formulario)");
        echo "Columna id_formulario agregada a formulario_validacion_pregunta.\n";
    } else {
        echo "Columna id_formulario ya existía en formulario_validacion_pregunta.\n";
    }
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al alterar preguntas: " . $e->getMessage() . "\n");
    exit(1);
}

// Columna descripcion en formulario_validacion
try {
    $col = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'formulario_validacion' AND COLUMN_NAME = 'descripcion' LIMIT 1");
    if (empty($col)) {
        $db->CRUD("ALTER TABLE formulario_validacion ADD COLUMN descripcion TEXT DEFAULT NULL AFTER nombre");
        echo "Columna descripcion agregada a formulario_validacion.\n";
    } else {
        echo "Columna descripcion ya existía.\n";
    }
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al agregar descripcion: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Listo.\n";
exit(0);
