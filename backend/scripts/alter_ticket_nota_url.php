<?php
/**
 * Añade a la tabla ticket columnas nota y url_direccion (para validaciones y otros).
 * La URL se guarda completa (TEXT), no se recorta.
 *
 * Uso: php backend/scripts/alter_ticket_nota_url.php [esquema]
 * Ejemplo: php backend/scripts/alter_ticket_nota_url.php __SPARTA_SECRET_REDACTED__
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
    'nota'          => "ALTER TABLE ticket ADD COLUMN nota TEXT NULL DEFAULT NULL AFTER contacto_email",
    'url_direccion' => "ALTER TABLE ticket ADD COLUMN url_direccion TEXT NULL DEFAULT NULL AFTER nota",
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

echo "Listo. Columnas nota y url_direccion en ticket.\n";
exit(0);
