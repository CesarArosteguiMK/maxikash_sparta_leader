<?php

require_once __DIR__ . '/../backend/core/Database.php';
require_once __DIR__ . '/../backend/core/Model.php';
require_once __DIR__ . '/../backend/models/Ticket.php';

use Core\Database;
use Models\Ticket;

$db = new Database();
$folio = $argv[1] ?? '';

if ($folio === '--zeros') {
    $rows = $db->queryAll(
        "SELECT ds.id, ds.id_ticket, t.folio, ds.id_dictamen, ds.resultado, ds.fecha_revision, LEFT(ds.detalle, 260) AS det
         FROM dictamen_sistema ds
         INNER JOIN ticket t ON t.id_ticket = ds.id_ticket
         WHERE ds.detalle LIKE '%\"direcciones_dictamen_total\":0%'
         ORDER BY ds.id DESC
         LIMIT 100"
    );
    echo "dictamen_sistema con direcciones_dictamen_total=0:\n";
    foreach ($rows as $r) {
        echo "id_ds={$r['id']} ticket={$r['id_ticket']} folio={$r['folio']} id_dictamen={$r['id_dictamen']} res={$r['resultado']} rev={$r['fecha_revision']}\n";
    }
    exit(0);
}

if ($folio === '--getds') {
    $idTicket = isset($argv[2]) ? (int)$argv[2] : 0;
    if ($idTicket < 1) {
        fwrite(STDERR, "Uso: php scripts/debug_dictamen_direcciones.php --getds <id_ticket>\n");
        exit(1);
    }
    $res = Ticket::getDictamenSistema($idTicket);
    $ds = $res['datos']['dictamen_sistema'] ?? null;
    if (!$ds) {
        echo "Sin dictamen_sistema para ticket {$idTicket}\n";
        exit(0);
    }
    $d = is_array($ds['detalle_parsed'] ?? null) ? $ds['detalle_parsed'] : [];
    echo "ticket={$idTicket} resultado={$ds['resultado']}\n";
    echo "direcciones_visitadas=" . (string)($d['direcciones_visitadas'] ?? 'null') . "\n";
    echo "direcciones_dictamen_total=" . (string)($d['direcciones_dictamen_total'] ?? 'null') . "\n";
    echo "visitadas_detalle=" . json_encode($d['direcciones_visitadas_detalle'] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
    echo "pendientes_detalle=" . json_encode($d['direcciones_pendientes_detalle'] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
    exit(0);
}

if ($folio === '') {
    fwrite(STDERR, "Uso: php scripts/debug_dictamen_direcciones.php <folio>  |  --zeros  |  --getds <id_ticket>\n");
    exit(1);
}

$ticket = $db->queryOne(
    "SELECT id_ticket, folio FROM ticket WHERE folio = :f OR folio = :f2 ORDER BY id_ticket DESC LIMIT 1",
    ['f' => $folio, 'f2' => '#' . ltrim($folio, '#')]
);

if (!$ticket) {
    echo "No se encontró ticket para folio {$folio}\n";
    exit(0);
}

$idTicket = (int)$ticket['id_ticket'];
echo "Ticket: {$idTicket} | Folio: {$ticket['folio']}\n\n";

$ds = $db->queryOne(
    "SELECT id, id_dictamen, resultado, fecha_envio_dictamen, fecha_revision FROM dictamen_sistema WHERE id_ticket = :tid ORDER BY id DESC LIMIT 1",
    ['tid' => $idTicket]
);
if ($ds) {
    echo "dictamen_sistema: id={$ds['id']} id_dictamen={$ds['id_dictamen']} resultado={$ds['resultado']} envio={$ds['fecha_envio_dictamen']}\n";
} else {
    echo "Sin dictamen_sistema\n";
}

$dicts = $db->queryAll(
    "SELECT id, estado, fecha_creacion, LEFT(descripcion, 260) AS desc_short FROM dictamen WHERE id_ticket = :tid ORDER BY id DESC LIMIT 5",
    ['tid' => $idTicket]
);
echo "\nÚltimos dictámenes (top 5):\n";
foreach ($dicts as $d) {
    $mark = ($ds && (int)$ds['id_dictamen'] === (int)$d['id']) ? '*' : ' ';
    echo "{$mark} id={$d['id']} estado={$d['estado']} fc={$d['fecha_creacion']}\n";
    echo "   desc: " . str_replace(["\r", "\n"], [' ', ' '], (string)$d['desc_short']) . "\n";
}

