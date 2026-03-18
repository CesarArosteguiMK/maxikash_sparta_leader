<?php
/**
 * Crea la tabla formulario_validacion_pregunta en la BD.
 * Preguntas predefinidas (globales) y personalizadas (por usuario), con flag activa para marcar/desmarcar.
 * Ejecutar una vez:
 *
 *   php backend/scripts/create_tabla_formulario_validacion_pregunta.php
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

$sql = "CREATE TABLE IF NOT EXISTS formulario_validacion_pregunta (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(30) NOT NULL COMMENT 'abierta, cerrada, multiple, si_no, escala, fecha, numero',
    texto TEXT NOT NULL,
    opciones JSON DEFAULT NULL COMMENT 'Para cerrada/multiple: array de strings',
    indice_correcto INT UNSIGNED DEFAULT NULL COMMENT 'Para cerrada: índice de la opción correcta',
    indices_correctos JSON DEFAULT NULL COMMENT 'Para multiple: array de índices correctos',
    escala_min VARCHAR(100) DEFAULT NULL,
    escala_max VARCHAR(100) DEFAULT NULL,
    num_min DECIMAL(15,4) DEFAULT NULL,
    num_max DECIMAL(15,4) DEFAULT NULL,
    es_predefinida TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=predefinida global, 0=personalizada',
    activa TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=incluida en cuestionario, 0=desmarcada',
    orden INT NOT NULL DEFAULT 0,
    id_persona_creador INT UNSIGNED DEFAULT NULL COMMENT 'NULL para predefinidas del sistema',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_predefinida (es_predefinida),
    INDEX idx_activa (activa),
    INDEX idx_creador (id_persona_creador),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->CRUD($sql);
    echo "Tabla formulario_validacion_pregunta creada o ya existía. Listo.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error al crear tabla: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
