<?php
/**
 * Deja en "pendiente" los dictámenes del sistema que tienen un resultado guardado
 * pero cuya ventana de 12 h aún no ha vencido (se evaluaron antes de tiempo).
 *
 * Uso:
 *   php reset_pendiente_si_ventana_activa.php
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

$db = new \Core\Database();
$rows = $db->queryAll(
    "SELECT id, id_ticket, resultado, fecha_envio_dictamen, detalle FROM dictamen_sistema " .
    "WHERE resultado IS NOT NULL AND resultado <> '' AND resultado NOT IN ('pendiente', 'prorroga_activa', 'intensidad_activa')"
);
$tz = new \DateTimeZone('America/Mexico_City');
$now = new \DateTime('now', $tz);
$reseteados = 0;
foreach ($rows as $r) {
    $id = (int)($r['id'] ?? 0);
    $fechaInicio = trim((string)($r['fecha_envio_dictamen'] ?? ''));
    $detalle = $r['detalle'] ?? '';
    $det = is_string($detalle) && $detalle !== '' ? json_decode($detalle, true) : [];
    $esProrroga = is_array($det) && isset($det['prorroga']) && is_array($det['prorroga']) && !empty($det['prorroga']['otorgada']);
    if ($esProrroga && !empty($det['prorroga']['fecha_otorgada'])) {
        $fechaInicio = trim((string)$det['prorroga']['fecha_otorgada']);
    }
    if ($fechaInicio === '') {
        continue;
    }
    try {
        $inicio = new \DateTime($fechaInicio, $tz);
        $fin = clone $inicio;
        $fin->modify('+12 hours');
        if ($now->getTimestamp() < $fin->getTimestamp()) {
            $db->CRUD("UPDATE dictamen_sistema SET resultado = 'pendiente', detalle = NULL WHERE id = :id", ['id' => $id]);
            $reseteados++;
        }
    } catch (\Throwable $e) {
        // ignorar fila
    }
}
echo "Dictámenes del sistema con ventana aún activa reseteados a pendiente: {$reseteados}\n";
exit(0);
