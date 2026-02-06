<?php

/**
 * Inserta o actualiza una API key en la tabla config_api (valor en texto plano).
 * Uso: php backend/config/set_api_key.php CLAVE "valor"
 * Ejemplo: php backend/config/set_api_key.php GEMINI_API_KEY "AIzaSy..."
 *
 * Requiere: tabla config_api con columna valor (backend/sql/config_api_plain.sql).
 * Si la tabla ya tiene valor_cifrado: ALTER TABLE config_api ADD COLUMN valor TEXT NULL;
 */

$baseDir = dirname(__DIR__);
if (!defined('RAIZ')) {
    define('RAIZ', $baseDir);
}

// Cargar Core\Database (CLI no usa index.php)
$dbPath = RAIZ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Database.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
}

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Solo uso por línea de comandos.\n");
    exit(1);
}

$clave = $argv[1] ?? '';
$valor = $argv[2] ?? '';

if ($clave === '' || $valor === '') {
    echo "Uso: php set_api_key.php CLAVE \"valor\"\n";
    echo "Ejemplo: php backend/config/set_api_key.php GEMINI_API_KEY \"AIzaSy...\"\n";
    echo "Claves admitidas: OPENAI_API_KEY, GEMINI_API_KEY, GOOGLE_MAPS_API_KEY, OPENAI_SSL_VERIFY\n";
    exit(1);
}

try {
    $db = new \Core\Database();
    // Tabla con columna valor (texto plano). Si existe valor_cifrado, usar columna valor.
    $db->CRUD(
        'INSERT INTO config_api (clave, valor) VALUES (:clave, :valor)
         ON DUPLICATE KEY UPDATE valor = :valor2',
        [
            'clave' => $clave,
            'valor' => $valor,
            'valor2' => $valor,
        ]
    );
    echo "OK: $clave actualizado en config_api.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Error BD: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Asegúrese de tener la tabla config_api con columna valor (backend/sql/config_api_plain.sql).\n");
    fwrite(STDERR, "Si la tabla tiene valor_cifrado: ALTER TABLE config_api ADD COLUMN valor TEXT NULL;\n");
    exit(1);
}
