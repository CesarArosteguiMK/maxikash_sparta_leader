<?php
/**
 * Reprocesa usuarios Spartan activos de Campo/Cobranza que no existan,
 * estén dados de baja o tengan rol desalineado en Legacy.
 *
 * Uso:
 *   php backend/scripts/sincronizar_legacy_usuarios_pendientes.php 100
 */

define('SPARTA_PROJECT_ROOT', dirname(__DIR__, 2));
define('RAIZ', SPARTA_PROJECT_ROOT . '/backend');

require_once RAIZ . '/core/Database.php';
require_once RAIZ . '/core/DatabaseLegacy.php';
require_once RAIZ . '/core/Model.php';
require_once RAIZ . '/models/LegacyUserSync.php';

$limite = isset($argv[1]) ? (int)$argv[1] : 100;
$resultado = Models\LegacyUserSync::sincronizarPendientes($limite, 0);

echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
exit(($resultado['success'] ?? false) ? 0 : 1);
