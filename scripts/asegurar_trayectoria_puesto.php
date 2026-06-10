<?php

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';
require_once __DIR__ . '/../backend/core/Model.php';
require_once __DIR__ . '/../backend/models/CapHum.php';

$db = new Core\Database();
Models\CapHum::asegurarTablaTrayectoriaPuesto($db);
$insertados = Models\CapHum::sembrarTrayectoriaPuestosActuales($db);
$fechasActualizadas = Models\CapHum::actualizarFechasTrayectoriaDesdeAsignaPuesto($db);

$tabla = $db->queryOne('SHOW CREATE TABLE __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria');

if (!$tabla) {
    fwrite(STDERR, "No se pudo verificar la tabla persona_puesto_trayectoria." . PHP_EOL);
    exit(1);
}

echo "OK tabla creada/verificada: " . ($tabla['Table'] ?? 'persona_puesto_trayectoria') . PHP_EOL;
echo "Movimientos base insertados: " . $insertados . PHP_EOL;
echo "Fechas de asignacion actualizadas: " . $fechasActualizadas . PHP_EOL;

$resumen = $db->queryOne("
    SELECT
        COUNT(*) AS movimientos,
        COUNT(DISTINCT id_persona) AS personas
    FROM __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria
");

echo "Movimientos registrados en tabla: " . (int)($resumen['movimientos'] ?? 0) . PHP_EOL;
echo "Personas con trayectoria: " . (int)($resumen['personas'] ?? 0) . PHP_EOL;
