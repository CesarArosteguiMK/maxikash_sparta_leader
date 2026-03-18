<?php
/**
 * Crea las tablas para tickets simples: Plantilla, Atención al cliente, Validaciones,
 * Viáticos, Aplicaciones de pago, Crédito problemático, Aclaración de crédito.
 * Ejecutar una vez:
 *
 *   php backend/scripts/create_tablas_ticket_simples.php
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
putenv('DB_ESQUEMA=' . trim($dbConfig['ESQUEMA'] ?? '__SPARTA_SECRET_REDACTED__'));
putenv('DB_NAME=' . trim($dbConfig['ESQUEMA'] ?? '__SPARTA_SECRET_REDACTED__'));
putenv('DB_USUARIO=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_USER=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_PASSWORD=' . trim($dbConfig['PASSWORD'] ?? ''));
putenv('DB_PASS=' . trim($dbConfig['PASSWORD'] ?? ''));

require_once $raiz . '/core/Database.php';

$db = new \Core\Database();

$tablas = [
    'ticket_plantilla' => "CREATE TABLE IF NOT EXISTS ticket_plantilla (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_persona_creador INT UNSIGNED NOT NULL,
        tipo_plantilla VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        nombre_archivo_original VARCHAR(255) DEFAULT NULL,
        ruta_adjunto VARCHAR(500) DEFAULT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_creador (id_persona_creador),
        INDEX idx_fecha (fecha_creacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'ticket_atencion_cliente' => "CREATE TABLE IF NOT EXISTS ticket_atencion_cliente (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_persona_creador INT UNSIGNED NOT NULL,
        asunto VARCHAR(255) NOT NULL,
        descripcion TEXT NOT NULL,
        prioridad VARCHAR(20) NOT NULL DEFAULT 'media',
        contacto_telefono VARCHAR(50) DEFAULT NULL,
        contacto_email VARCHAR(100) DEFAULT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_creador (id_persona_creador),
        INDEX idx_fecha (fecha_creacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'ticket_validacion' => "CREATE TABLE IF NOT EXISTS ticket_validacion (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_persona_creador INT UNSIGNED NOT NULL,
        tipo_validacion VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        resultado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
        nombre_archivo_original VARCHAR(255) DEFAULT NULL,
        ruta_adjunto VARCHAR(500) DEFAULT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_creador (id_persona_creador),
        INDEX idx_fecha (fecha_creacion),
        INDEX idx_resultado (resultado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'ticket_viaticos' => "CREATE TABLE IF NOT EXISTS ticket_viaticos (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_persona_creador INT UNSIGNED NOT NULL,
        tipo_viatico VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        nombre_archivo_original VARCHAR(255) DEFAULT NULL,
        ruta_adjunto VARCHAR(500) DEFAULT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_creador (id_persona_creador),
        INDEX idx_fecha (fecha_creacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'ticket_aplicaciones_pago' => "CREATE TABLE IF NOT EXISTS ticket_aplicaciones_pago (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_persona_creador INT UNSIGNED NOT NULL,
        tipo_solicitud VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        nombre_archivo_original VARCHAR(255) DEFAULT NULL,
        ruta_adjunto VARCHAR(500) DEFAULT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_creador (id_persona_creador),
        INDEX idx_fecha (fecha_creacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'ticket_credito_problematico' => "CREATE TABLE IF NOT EXISTS ticket_credito_problematico (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_persona_creador INT UNSIGNED NOT NULL,
        tipo_solicitud VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        nombre_archivo_original VARCHAR(255) DEFAULT NULL,
        ruta_adjunto VARCHAR(500) DEFAULT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_creador (id_persona_creador),
        INDEX idx_fecha (fecha_creacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'ticket_aclaracion_credito' => "CREATE TABLE IF NOT EXISTS ticket_aclaracion_credito (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_persona_creador INT UNSIGNED NOT NULL,
        tipo_aclaracion VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        nombre_archivo_original VARCHAR(255) DEFAULT NULL,
        ruta_adjunto VARCHAR(500) DEFAULT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_creador (id_persona_creador),
        INDEX idx_fecha (fecha_creacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($tablas as $nombre => $sql) {
    try {
        $db->CRUD($sql);
        echo "Tabla {$nombre} creada o ya existía. Listo.\n";
    } catch (\Throwable $e) {
        fwrite(STDERR, "Error al crear tabla {$nombre}: " . $e->getMessage() . "\n");
        exit(1);
    }
}

echo "Todas las tablas de tickets simples están listas.\n";
exit(0);
