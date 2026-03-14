<?php
/**
 * Repara descripciones de dictámenes que contienen short links de Google Maps
 * (maps.app.goo.gl / goo.gl/maps). Expande las URLs y guarda el texto con la
 * URL final para que el análisis de coordenadas funcione correctamente.
 *
 * Uso:
 *   php reparar_dictamen_shortlinks.php         → reparar y actualizar BD
 *   php reparar_dictamen_shortlinks.php --dry-run → solo simular (no escribe)
 *
 * Conecta a la BD __SPARTA_SECRET_REDACTED__ (servidor/usuario/contraseña desde config.ini [database]).
 *
 * Requiere: PHP con extensiones PDO, pdo_mysql, curl.
 */

date_default_timezone_set('America/Mexico_City');

$raiz = dirname(__DIR__);
$configFile = $raiz . '/config/config.ini';
if (!is_file($configFile)) {
    fwrite(STDERR, "Error: No se encontró config.ini en {$raiz}/config/\n");
    exit(1);
}
$config = @parse_ini_file($configFile, true);
if (empty($config['database'])) {
    fwrite(STDERR, "Error: No existe sección [database] en config.ini\n");
    exit(1);
}
$dbConfig = $config['database'];
// Tabla dictamen está en la BD __SPARTA_SECRET_REDACTED__ (servidor/usuario/contraseña desde config.ini)
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
require_once $raiz . '/core/Model.php';
require_once $raiz . '/models/Gestiones.php';
require_once $raiz . '/models/Ticket.php';

$dryRun = in_array('--dry-run', array_slice($argv, 1), true);

echo "Dictámenes con short links (maps.app.goo.gl / goo.gl/maps)\n";
echo "Modo: " . ($dryRun ? "simulación (no se modifica la BD)" : "reparación real") . "\n\n";

try {
    $result = \Models\Ticket::repararDictamenesConShortLinks($dryRun);
} catch (\Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}

$total = (int)($result['total_candidates'] ?? 0);
$updated = (int)($result['updated'] ?? 0);
$skipped = (int)($result['skipped'] ?? 0);
$errors = $result['errors'] ?? [];

echo "Candidatos encontrados: {$total}\n";
echo "Actualizados: {$updated}\n";
echo "Sin cambios (ya normalizados o sin expansión): {$skipped}\n";
if (!empty($errors)) {
    echo "Errores: " . count($errors) . "\n";
    foreach ($errors as $err) {
        $id = isset($err['id']) ? $err['id'] : '—';
        echo "  - id={$id} " . ($err['error'] ?? '') . "\n";
    }
}

// Regenerar dictamen del sistema para los tickets cuya descripción se reparó (actualizar resultado/detalle)
$idTicketsActualizados = $result['id_tickets_actualizados'] ?? [];
if (!$dryRun && !empty($idTicketsActualizados)) {
    echo "\nRegenerando dictamen del sistema para " . count($idTicketsActualizados) . " ticket(s)...\n";
    $regeneradosOk = 0;
    $regeneradosFail = 0;
    foreach ($idTicketsActualizados as $idTicket) {
        $gen = \Models\Ticket::generarDictamenSistema((int)$idTicket, true);
        if (!empty($gen['success'])) {
            $regeneradosOk++;
        } else {
            $regeneradosFail++;
            echo "  - Ticket {$idTicket}: " . ($gen['mensaje'] ?? $gen['error'] ?? 'error') . "\n";
        }
    }
    echo "Dictamen del sistema regenerado: {$regeneradosOk} OK, {$regeneradosFail} fallos.\n";
}

echo "\n" . ($dryRun ? "Ejecuta sin --dry-run para aplicar los cambios." : "Listo.") . "\n";
exit(empty($errors) ? 0 : 1);
