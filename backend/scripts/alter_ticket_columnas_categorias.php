<?php
/**
 * Añade a la tabla ticket columnas para guardar datos por categoría (plantilla, viáticos, etc.).
 * Ejecutar una vez después de tener la tabla ticket y categoria_gestion.
 *
 * Uso:
 *   php backend/scripts/alter_ticket_columnas_categorias.php [esquema]
 *
 * Si la tabla ticket está en otra BD que no es la de config.ini, indicar el esquema:
 *   php backend/scripts/alter_ticket_columnas_categorias.php __SPARTA_SECRET_REDACTED__
 *
 * Columnas:
 *   tipo_categoria     - Tipo (plantilla, viático, validación, aclaración, tipo solicitud)
 *   asunto             - Asunto (atención al cliente)
 *   prioridad_categoria - Prioridad del formulario (atención al cliente: alta/media/baja)
 *   contacto_telefono  - Teléfono de contacto
 *   contacto_email     - Correo de contacto
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
$esquema = isset($argv[1]) && trim($argv[1]) !== '' ? trim($argv[1]) : trim($dbConfig['ESQUEMA'] ?? '__SPARTA_SECRET_REDACTED__');
putenv('DB_SERVIDOR=' . trim($dbConfig['SERVIDOR'] ?? ''));
putenv('DB_ESQUEMA=' . $esquema);
putenv('DB_USUARIO=' . trim($dbConfig['USUARIO'] ?? ''));
putenv('DB_PASSWORD=' . trim($dbConfig['PASSWORD'] ?? ''));
echo "Usando esquema: " . $esquema . "\n";

require_once $raiz . '/core/Database.php';

$db = new \Core\Database();

$columnas = [
    'tipo_categoria'      => "ALTER TABLE ticket ADD COLUMN tipo_categoria VARCHAR(150) NULL DEFAULT NULL AFTER categoria_gestion",
    'asunto'              => "ALTER TABLE ticket ADD COLUMN asunto VARCHAR(255) NULL DEFAULT NULL AFTER tipo_categoria",
    'prioridad_categoria' => "ALTER TABLE ticket ADD COLUMN prioridad_categoria VARCHAR(50) NULL DEFAULT NULL AFTER asunto",
    'contacto_telefono'   => "ALTER TABLE ticket ADD COLUMN contacto_telefono VARCHAR(50) NULL DEFAULT NULL AFTER prioridad_categoria",
    'contacto_email'      => "ALTER TABLE ticket ADD COLUMN contacto_email VARCHAR(100) NULL DEFAULT NULL AFTER contacto_telefono",
];

foreach ($columnas as $nombre => $sql) {
    try {
        $db->CRUD($sql);
        echo "Columna {$nombre} añadida a ticket.\n";
    } catch (\Throwable $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "Columna {$nombre} ya existía. Omitiendo.\n";
        } else {
            fwrite(STDERR, "Error en {$nombre}: " . $e->getMessage() . "\n");
            exit(1);
        }
    }
}

echo "Listo. Columnas de categorías en ticket actualizadas.\n";
exit(0);
