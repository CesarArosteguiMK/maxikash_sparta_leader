<?php
/**
 * ⚠️ SOLO PRUEBAS / DESARROLLO — NO usar en producción con datos reales.
 * Adelanta fecha_actualizacion del dictamen para forzar botón robot (plazo vencido).
 *
 * Para volver a “tiempo normal” en tickets ya probados, usar restaurar_fechas_dictamen.php
 * o reenviar dictamen (no recomendado); los nuevos envíos guardan la fecha real solas.
 *
 * Uso: php backend/docs/adelantar_plazo_dictamen.php [id_ticket|all]
 */
chdir(dirname(__DIR__));
define('RAIZ', dirname(__DIR__));
require_once RAIZ . '/core/Database.php';

$db = new Core\Database();
$idTicket = isset($argv[1]) ? trim($argv[1]) : '';

if ($idTicket === '' || $idTicket === 'all') {
    $db->CRUD("UPDATE dictamen SET fecha_actualizacion = DATE_SUB(NOW(), INTERVAL 48 HOUR) WHERE estado = 'enviado_al_gestor'");
    $db->CRUD("UPDATE dictamen_sistema SET fecha_envio_dictamen = DATE_SUB(NOW(), INTERVAL 48 HOUR)");
    echo "OK (PRUEBA): dictámenes y dictamen_sistema adelantados 48h.\n";
} else {
    $tid = (int) $idTicket;
    $row = $db->queryOne(
        "SELECT id FROM dictamen WHERE id_ticket = :tid AND estado = 'enviado_al_gestor' ORDER BY id DESC LIMIT 1",
        ['tid' => $tid]
    );
    if (!$row) {
        echo "No hay dictamen enviado_al_gestor para id_ticket=$tid\n";
        exit(2);
    }
    $db->CRUD("UPDATE dictamen SET fecha_actualizacion = DATE_SUB(NOW(), INTERVAL 48 HOUR) WHERE id = :id", ['id' => (int) $row['id']]);
    $db->CRUD("UPDATE dictamen_sistema SET fecha_envio_dictamen = DATE_SUB(NOW(), INTERVAL 48 HOUR) WHERE id_ticket = :tid", ['tid' => $tid]);
    echo "OK (PRUEBA): ticket $tid adelantado 48h.\n";
}
