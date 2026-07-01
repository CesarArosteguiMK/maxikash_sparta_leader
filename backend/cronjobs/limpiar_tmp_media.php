<?php
/**
 * CRONJOB: Limpia carpetas de medios extraídos (tmp_media) con más de 1 hora de antigüedad.
 *
 * Los videos/audio extraídos de PDFs FAD_DOC se guardan en backend/storage/tmp_media/<reqId>/.
 * Este script borra esas subcarpetas para no llenar el disco.
 *
 * Uso:
 *   php limpiar_tmp_media.php
 *
 * Programación recomendada (ejecutar cada 24 horas):
 *
 *   Linux (crontab -e):
 *     0 3 * * * cd /ruta/al/backend/cronjobs && php limpiar_tmp_media.php >> ../storage/logs/limpiar_tmp_media.log 2>&1
 *
 *   Windows (Programador de tareas):
 *     0 3 * * * (diario a las 03:00)
 *     Acción: Iniciar programa → php.exe
 *     Argumentos: "C:\ruta\backend\cronjobs\limpiar_tmp_media.php"
 */

date_default_timezone_set('America/Mexico_City');

$baseDir = __DIR__ . '/../storage/tmp_media';
$maxEdadSegundos = 3600; // 1 hora
$logDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta___SPARTA_SECRET_REDACTED___cron_logs';
$logFile = $logDir . DIRECTORY_SEPARATOR . 'limpiar_tmp_media.log';

if (!is_dir($baseDir)) {
    exit(0);
}

$baseReal = realpath($baseDir);
if ($baseReal === false) {
    exit(1);
}

$ahora = time();
$borrados = 0;
$errores = 0;

$entries = @scandir($baseDir);
if ($entries === false) {
    exit(1);
}

foreach ($entries as $nombre) {
    if ($nombre === '.' || $nombre === '..') {
        continue;
    }
    $ruta = $baseDir . DIRECTORY_SEPARATOR . $nombre;
    if (!is_dir($ruta)) {
        continue;
    }
    $realRuta = realpath($ruta);
    if ($realRuta === false || strpos($realRuta, $baseReal) !== 0) {
        continue;
    }
    $mtime = @filemtime($ruta);
    if ($mtime === false) {
        continue;
    }
    if ($ahora - $mtime <= $maxEdadSegundos) {
        continue;
    }
    $archivos = @glob($ruta . DIRECTORY_SEPARATOR . '*');
    if ($archivos !== false) {
        foreach ($archivos as $f) {
            if (@is_file($f) && @unlink($f)) {
                $borrados++;
            } else {
                $errores++;
            }
        }
    }
    if (@rmdir($ruta)) {
        // carpeta eliminada
    } else {
        $errores++;
    }
}

if ($borrados > 0 || $errores > 0) {
    if ((string) getenv('SPARTA_ENABLE_FILE_LOGS') === '1' && (is_dir($logDir) || @mkdir($logDir, 0755, true))) {
        $line = date('Y-m-d H:i:s') . " | carpetas revisadas, archivos borrados: $borrados, errores: $errores\n";
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}

exit(0);
