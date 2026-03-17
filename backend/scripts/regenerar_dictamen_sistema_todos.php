<?php
/**
 * Regenera el dictamen del sistema para todos los tickets que tengan registro
 * en dictamen_sistema. Usa la descripción actual del dictamen (p. ej. ya reparada)
 * y recalcula resultado/detalle/cobertura.
 *
 * Uso:
 *   php regenerar_dictamen_sistema_todos.php
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

echo "Regenerando dictamen del sistema para todos los tickets con registro (desde 2026-03-10)...\n";

$db = new \Core\Database();
$rows = $db->queryAll(
    "SELECT ds.id_ticket FROM dictamen_sistema ds INNER JOIN ticket t ON t.id_ticket = ds.id_ticket WHERE t.fecha_creacion >= '2026-03-10 00:00:00' ORDER BY ds.id_ticket"
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
