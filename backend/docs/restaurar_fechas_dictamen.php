<?php
/**
 * Restaura fecha_actualizacion del dictamen a NOW() para que el plazo de 12h
 * cuente desde este momento (botón robot oculto hasta que pasen 12h reales).
 * Útil después de pruebas con adelantar_plazo_dictamen.php.
 *
 * ⚠️ No recupera la fecha original de envío; reinicia el reloj desde “ahora”.
 * Si el dictamen ya tiene resultado en dictamen_sistema (no_visito, etc.),
 * solo se actualiza dictamen para ocultar el botón; el histórico en dictamen_sistema no se borra.
 *
 * Uso:
 *   php backend/docs/restaurar_fechas_dictamen.php        → todos enviado_al_gestor
 *   php backend/docs/restaurar_fechas_dictamen.php 101    → solo id_ticket 101
 */
chdir(dirname(__DIR__));
define('RAIZ', dirname(__DIR__));
require_once RAIZ . '/core/Database.php';

$db = new Core\Database();
$arg = isset($argv[1]) ? trim($argv[1]) : '';

if ($arg === '') {
    $db->CRUD("UPDATE dictamen SET fecha_actualizacion = NOW() WHERE estado = 'enviado_al_gestor'");
    echo "OK: fecha_actualizacion = NOW() en todos los dictamen enviado_al_gestor.\n";
} else {
    $tid = (int) $arg;
    $row = $db->queryOne(
        "SELECT id FROM dictamen WHERE id_ticket = :tid AND estado = 'enviado_al_gestor' ORDER BY id DESC LIMIT 1",
        ['tid' => $tid]
    );
    if (!$row) {
        echo "No hay dictamen enviado para id_ticket=$tid\n";
        exit(2);
    }
    $db->CRUD("UPDATE dictamen SET fecha_actualizacion = NOW() WHERE id = :id", ['id' => (int) $row['id']]);
    echo "OK: ticket $tid — plazo 12h cuenta desde ahora.\n";
}
echo "Recargar Panel Admin (F5).\n";
