<?php
/**
 * Regenera el dictamen del sistema para todos los tickets que tengan registro
 * en dictamen_sistema. Usa la descripción actual del dictamen (p. ej. ya reparada)
 * y recalcula resultado/detalle/cobertura.
 *
 * Uso:
 *   php regenerar_dictamen_sistema_todos.php --desde "YYYY-MM-DD HH:MM:SS" --hasta "YYYY-MM-DD HH:MM:SS"
 *
 * Campos que filtra:
 *   - Selecciona tickets (con dictamen_sistema existente) cuyo `ticket.fecha_creacion`
 *     cae dentro del rango [desde, hasta] (ambos en CDMX).
 *
 * Conecta a __SPARTA_SECRET_REDACTED__ (config.ini [database]).
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
require_once $raiz . '/core/DatabaseLegacy.php';
require_once $raiz . '/core/Model.php';
require_once $raiz . '/models/Gestiones.php';
require_once $raiz . '/models/Ticket.php';

// ------------------------------------------------------------
// RANGO (CDMX)
// ------------------------------------------------------------
$tz = new DateTimeZone('America/Mexico_City');

$desde = '2026-03-13 20:00:00';
$hasta = '2026-03-15 12:00:00';

for ($i = 1; $i < count($argv); $i++) {
    $arg = (string)($argv[$i] ?? '');
    if (strpos($arg, '--desde=') === 0) {
        $desde = substr($arg, strlen('--desde='));
        continue;
    }
    if ($arg === '--desde' && isset($argv[$i + 1])) {
        $desde = (string)$argv[++$i];
        continue;
    }
    if (strpos($arg, '--hasta=') === 0) {
        $hasta = substr($arg, strlen('--hasta='));
        continue;
    }
    if ($arg === '--hasta' && isset($argv[$i + 1])) {
        $hasta = (string)$argv[++$i];
        continue;
    }
}

try {
    $desdeDt = new DateTime($desde, $tz);
    $hastaDt = new DateTime($hasta, $tz);
    $desdeSql = $desdeDt->format('Y-m-d H:i:s');
    $hastaSql = $hastaDt->format('Y-m-d H:i:s');
} catch (Throwable $e) {
    fwrite(STDERR, "Error: rango inválido. Use --desde/--hasta con 'YYYY-MM-DD HH:MM:SS' (CDMX). Detalle: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Regenerando dictamen del sistema (dictamen_sistema existente) para tickets creados entre {$desdeSql} y {$hastaSql} (CDMX)...\n";

$db = new \Core\Database();
$rows = $db->queryAll(
    "SELECT ds.id_ticket
     FROM dictamen_sistema ds
     INNER JOIN ticket t ON t.id_ticket = ds.id_ticket
     WHERE t.fecha_creacion >= :desde AND t.fecha_creacion <= :hasta
     ORDER BY ds.id_ticket",
    ['desde' => $desdeSql, 'hasta' => $hastaSql]
);
$todos = [];
foreach ($rows as $r) {
    $tid = (int)($r['id_ticket'] ?? 0);
    if ($tid > 0) {
        $todos[$tid] = true;
    }
}
$ids = array_keys($todos);
$total = count($ids);
echo "Tickets a procesar: {$total}\n\n";

$ok = 0;
$fail = 0;
foreach ($ids as $i => $idTicket) {
    $gen = \Models\Ticket::generarDictamenSistema($idTicket, true);
    if (!empty($gen['success'])) {
        $ok++;
    } else {
        $fail++;
        echo "  Ticket {$idTicket}: " . ($gen['mensaje'] ?? $gen['error'] ?? 'error') . "\n";
    }
    if (($i + 1) % 50 === 0) {
        echo "  Procesados " . ($i + 1) . "/{$total}...\n";
    }
}

echo "\nListo. Regenerados: {$ok} OK, {$fail} fallos.\n";
exit($fail > 0 ? 1 : 0);
