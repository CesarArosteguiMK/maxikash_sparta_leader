<?php
/**
 * Bucle opcional: ejecuta enviar_primeros_pagos_lunes.php cada N segundos.
 *
 * Alternativa al Programador de tareas (Windows) que recomienda el propio cron.
 * Misma lógica de horarios, slots y estado; respeta PrimerosPagosAutoSwitch.
 *
 * Uso:
 *   C:\xampp\php\php.exe loop_enviar_primeros_pagos_lunes.php
 *
 * Parada ordenada: crear el flag (o ejecutar cerrar-loop-correos-primeros-pagos.bat):
 *   backend/storage/runtime/primeros_pagos/loop_primeros_pagos_stop.flag
 *
 * El intervalo aquí solo controla cada cuánto se *despierta* el PHP del cron.
 * La hora de CDMX para slots la fija enviar_primeros_pagos_lunes.php (America/Mexico_City).
 */

date_default_timezone_set('America/Mexico_City');

$intervalSeconds = 600;
$stopFlag = dirname(__DIR__) . '/storage/runtime/primeros_pagos/loop_primeros_pagos_stop.flag';
$childScript = __DIR__ . '/enviar_primeros_pagos_lunes.php';

if (!is_file($childScript)) {
    fwrite(STDERR, "[loop] No existe enviar_primeros_pagos_lunes.php\n");
    exit(1);
}

require_once __DIR__ . '/PrimerosPagosAutoSwitch.php';

$php = PHP_BINARY;
if ($php === '' || !is_file($php)) {
    $php = 'php';
}

echo "[loop] Primeros pagos — correo automático. Intervalo {$intervalSeconds}s (~10 min). Horarios CDMX en el script del cron. Ctrl+C o flag de parada.\n";

while (true) {
    if (is_file($stopFlag)) {
        @unlink($stopFlag);
        echo "[loop] Parada solicitada (flag).\n";
        break;
    }

    if (PrimerosPagosAutoSwitch::isEnabled()) {
        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($childScript);
        passthru($cmd, $code);
    }

    sleep($intervalSeconds);
}

exit(0);
