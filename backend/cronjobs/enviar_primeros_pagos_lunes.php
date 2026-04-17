<?php
/**
 * CRONJOB: Envío automático de "Primeros pagos — Lunes de Cierre"
 *
 * Regla de calendario (CDMX): no se envían correos los **lunes**; el envío automático
 * opera de **martes a domingo**. El lunes el script termina en silencio (exit 0),
 * salvo `--force` para pruebas.
 *
 * Horarios objetivo (America/Mexico_City), siempre en formato 24 h:
 *   Mañana: 07:40, 09:40, 11:40
 *   Tarde/noche: 13:40, 14:40, 16:40, 18:40, 20:40, 23:50
 * (16:40 = 4:40 p. m., 18:40 = 6:40 p. m., 20:40 = 8:40 p. m., 23:50 = 11:50 p. m.)
 *
 * Uso manual:
 *   C:\xampp\php\php.exe ...\enviar_primeros_pagos_lunes.php --force
 *   C:\xampp\php\php.exe ...\enviar_primeros_pagos_lunes.php --dry-run
 *     (solo muestra qué haría un run real: slot CDMX, destinatarios, interruptor; no envía correo)
 *
 * Recomendado en Programador de tareas (Windows):
 *   Una tarea cada 1–5 minutos ejecutando este archivo (misma instancia para todos los horarios).
 *
 * Alternativa (sin Programador de tareas): bucle PHP + scripts en esta carpeta:
 *   loop_enviar_primeros_pagos_lunes.php, iniciar-loop-correos-primeros-pagos.bat,
 *   iniciar-loop-correos-primeros-pagos-oculto.vbs, cerrar-loop-correos-primeros-pagos.bat
 *
 * Ventana por slot: desde HH:MM del slot hasta justo antes del siguiente slot (CDMX).
 * Así, si la tarea corre tarde (ej. 17:03), aún puede enviarse el correo del slot 16:40
 * si ese día no se había registrado éxito para 16:40.
 */

/** Todos los horarios de slots (07:40, 09:40, …) y date('H:i') son CDMX, no la hora del SO del servidor. */
date_default_timezone_set('America/Mexico_City');

$projectRoot = dirname(__DIR__);

if (!defined('RAIZ')) {
    define('RAIZ', $projectRoot);
}

$envFile = dirname($projectRoot) . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            $eq = strpos($line, '=');
            if ($eq === false) continue;
            $key = trim(substr($line, 0, $eq));
            if ($key === '' || strpos($key, 'MAIL_') !== 0) continue;
            $value = trim(str_replace(["\r", "\n"], '', substr($line, $eq + 1)));
            if (preg_match('/^["\'](.+)["\']\s*$/', $value, $m)) {
                $value = trim($m[1]);
            }
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

define('CONFIGURACION', parse_ini_file(RAIZ . '/config/config.ini'));
define('CONTROLADORES', RAIZ . '/controllers');
define('LIBRERIAS', RAIZ . '/libs');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');

require_once dirname(__DIR__) . '/bootstrap_composer.php';
sparta_require_composer_autoload();

spl_autoload_register(function ($archivo) {
    if (strpos($archivo, 'PhpOffice\\') === 0 ||
        strpos($archivo, 'ZipStream\\') === 0 ||
        strpos($archivo, 'Psr\\') === 0) {
        return;
    }
    $archivo = str_replace('\\', '/', $archivo);
    $ruta = RAIZ . "/$archivo.php";
    if (file_exists($ruta)) {
        require_once $ruta;
    }
});

require_once RAIZ . '/config/config.php';
require_once __DIR__ . '/PrimerosPagosAutoSwitch.php';

$args = getopt('', ['force', 'dry-run']);
$force = isset($args['force']);
$dryRun = isset($args['dry-run']);

if (!$dryRun && !$force && !PrimerosPagosAutoSwitch::isEnabled()) {
    echo "[INFO] Envío automático desactivado (interruptor en servidor).\n";
    exit(0);
}

/** ISO 8601 en America/Mexico_City: 1=lunes … 7=domingo */
$dowCdmx = (int) date('N');
$esLunesSinForzar = ($dowCdmx === 1 && !$force);

$horariosPermitidos = [
    '07:40', '09:40', '11:40', '13:40', '14:40', '16:40', '18:40', '20:40', '23:50'
];

$ahora = date('H:i');

// Evitar envíos duplicados en la misma ventana HH:MM (por reintentos o múltiples tareas)
$stateDir = __DIR__ . '/logs';
if (!is_dir($stateDir)) {
    @mkdir($stateDir, 0755, true);
}
$stateFile = $stateDir . '/primeros_pagos_last_send.txt';
$stateJson = $stateDir . '/primeros_pagos_estado.json';
$marcaActual = date('Y-m-d H:i');

$destinatarios = [
    'roman.jimenez@__SPARTA_SECRET_REDACTED__.mx',
    'marlon.flores@__SPARTA_SECRET_REDACTED__.mx',
    'hector.ruiz@__SPARTA_SECRET_REDACTED__.mx',
    'guillermo.garcia@__SPARTA_SECRET_REDACTED__.mx',
    '__SPARTA_SECRET_REDACTED__@__SPARTA_SECRET_REDACTED__.mx',
    'josealberto.hernandez@__SPARTA_SECRET_REDACTED__.mx',
    'josue.aldrete@__SPARTA_SECRET_REDACTED__.mx',
    'erika.ortiz@__SPARTA_SECRET_REDACTED__.mx',
    'lrgonzalez033@gmail.com', // verificación / monitoreo
];

$asunto = 'Primeros pagos — Lunes de Cierre';

try {
    // Cargar estado diario
    $hoy = date('Y-m-d');
    $estado = ['date' => $hoy, 'slots' => []];
    if (is_file($stateJson)) {
        $tmp = json_decode((string)@file_get_contents($stateJson), true);
        if (is_array($tmp) && ($tmp['date'] ?? '') === $hoy) {
            $estado = $tmp;
            if (!is_array($estado['slots'] ?? null)) {
                $estado['slots'] = [];
            }
        }
    }

    // Slot activo: el último horario programado cuya ventana [slot, siguiente_slot) contiene $ahora.
    // Comparación HH:MM en 24h como string es segura entre slots del mismo día.
    // Ej.: a las 17:03 sigue vigente el slot 16:40 hasta las 18:40 (no solo 4 min después).
    $slotObjetivo = null;
    if ($force) {
        $slotObjetivo = $ahora;
    } else {
        $n = count($horariosPermitidos);
        for ($i = 0; $i < $n; $i++) {
            $slot = $horariosPermitidos[$i];
            $siguiente = ($i + 1 < $n) ? $horariosPermitidos[$i + 1] : '24:00';
            if (strcmp($ahora, $slot) < 0) {
                continue;
            }
            if (strcmp($ahora, $siguiente) >= 0) {
                continue;
            }
            $slotObjetivo = $slot;
            break;
        }
    }

    if (!$dryRun && !$force && $slotObjetivo === null) {
        echo "[INFO] Hora {$ahora} fuera de ventana programada (sin slot aplicable).\n";
        exit(0);
    }

    if (!$dryRun && !$force && isset($estado['slots'][$slotObjetivo]) && ($estado['slots'][$slotObjetivo]['status'] ?? '') === 'success') {
        echo "[INFO] Slot {$slotObjetivo} ya enviado previamente hoy.\n";
        exit(0);
    }

    if (!$dryRun && !$force && is_file($stateFile)) {
        $ultima = trim((string)@file_get_contents($stateFile));
        if ($ultima === $slotObjetivo) {
            echo "[INFO] Ya se envió en esta ventana CDMX ({$slotObjetivo}).\n";
            exit(0);
        }
    }

    if ($esLunesSinForzar && !$dryRun) {
        echo "[INFO] Lunes (America/Mexico_City): no se envían correos automáticos de primeros pagos. Calendario: martes a domingo. (Use --force solo para pruebas.)\n";
        exit(0);
    }

    if ($dryRun) {
        $swOn = PrimerosPagosAutoSwitch::isEnabled();
        echo "[DRY-RUN] No se envía ningún correo (solo simulación).\n";
        echo "  Zona horaria del cron: America/Mexico_City · Fecha " . date('Y-m-d') . " · Hora {$ahora}\n";
        if ($esLunesSinForzar) {
            echo "  Regla: los lunes no hay envío programado (martes–domingo CDMX). --force omite esta regla.\n";
        }
        echo "  Interruptor Auto horario: " . ($swOn ? 'ACTIVADO' : 'APAGADO') . "\n";
        echo "  Modo --force: " . ($force ? 'sí (slot simulado = hora actual)' : 'no') . "\n";
        echo "  Slot objetivo: " . ($slotObjetivo ?? '(ninguno)') . "\n";
        echo "  Asunto: {$asunto}\n";
        echo "  Destinatarios (" . count($destinatarios) . "):\n";
        foreach ($destinatarios as $em) {
            echo "    - {$em}\n";
        }
        $accion = '  → Con un run real ahora: ';
        if (!$swOn) {
            $accion .= 'no enviaría (interruptor apagado; el script sale al inicio).';
        } elseif ($esLunesSinForzar) {
            $accion .= 'no enviaría (lunes CDMX: sin correos automáticos; martes–domingo).';
        } elseif ($slotObjetivo === null && !$force) {
            $accion .= 'no enviaría (fuera de ventana de horarios programados).';
        } elseif (!$force && isset($estado['slots'][$slotObjetivo]) && ($estado['slots'][$slotObjetivo]['status'] ?? '') === 'success') {
            $accion .= 'no reenviaría (este slot ya figura como enviado hoy).';
        } elseif (!$force && is_file($stateFile)) {
            $ult = trim((string)@file_get_contents($stateFile));
            if ($ult === $slotObjetivo) {
                $accion .= 'no reenviaría (archivo de ventana ya marcado para este slot).';
            } else {
                $accion .= 'SÍ intentaría enviar el correo a la lista de arriba.';
            }
        } else {
            $accion .= 'SÍ intentaría enviar el correo a la lista de arriba.';
        }
        echo $accion . "\n";
        exit(0);
    }

    $reporteria = new \Controllers\Reporteria();
    $resultado = $reporteria->enviarCorreoVencimientosLunesProgramado($destinatarios, $asunto, $force);
    if (!empty($resultado['success'])) {
        $n = (int)($resultado['datos']['total_registros'] ?? 0);
        @file_put_contents($stateFile, $slotObjetivo, LOCK_EX);
        $estado['slots'][$slotObjetivo] = [
            'status' => 'success',
            'sent' => count($destinatarios),
            'total_registros' => $n,
            'executed_at' => date('c'),
            'runner_time' => $ahora,
        ];
        @file_put_contents($stateJson, json_encode($estado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        echo "[OK] Correo enviado. Slot {$slotObjetivo}. Registros del corte: {$n}\n";
        exit(0);
    }
    $msg = $resultado['mensaje'] ?? 'Error desconocido';
    $estado['slots'][$slotObjetivo] = [
        'status' => 'error',
        'error' => $msg,
        'executed_at' => date('c'),
        'runner_time' => $ahora,
    ];
    @file_put_contents($stateJson, json_encode($estado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    echo "[ERROR] {$msg}\n";
    exit(1);
} catch (\Throwable $e) {
    $hoy = date('Y-m-d');
    $estado = ['date' => $hoy, 'slots' => []];
    if (is_file($stateJson)) {
        $tmp = json_decode((string)@file_get_contents($stateJson), true);
        if (is_array($tmp) && ($tmp['date'] ?? '') === $hoy && is_array($tmp['slots'] ?? null)) {
            $estado = $tmp;
        }
    }
    $estado['slots'][$slotObjetivo ?? $ahora] = [
        'status' => 'error',
        'error' => $e->getMessage(),
        'executed_at' => date('c'),
        'runner_time' => $ahora,
    ];
    @file_put_contents($stateJson, json_encode($estado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    echo "[ERROR FATAL] " . $e->getMessage() . "\n";
    exit(1);
}

