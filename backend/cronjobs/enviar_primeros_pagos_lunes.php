<?php
/**
 * CRONJOB: Envío automático de "Primeros pagos — Lunes de Cierre"
 *
 * Horarios objetivo (America/Mexico_City):
 * 07:40, 09:40, 11:40, 13:40, 14:40, 16:40, 18:40, 20:40, 23:50
 *
 * Uso manual:
 *   C:\xampp\php\php.exe C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\enviar_primeros_pagos_lunes.php --force
 *
 * Recomendado en Programador de tareas (Windows):
 *   Crear 9 tareas diarias, una por horario, ejecutando este archivo.
 */

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
define('LIBRERIAS', RAIZ . '/Libs');
define('MODELOS', RAIZ . '/models');
define('VISTAS', RAIZ . '/views');
define('COMPONENTES', RAIZ . '/components');

require_once LIBRERIAS . '/PhpSpreadsheet/vendor/autoload.php';

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

$args = getopt('', ['force']);
$force = isset($args['force']);

if (!$force && !PrimerosPagosAutoSwitch::isEnabled()) {
    echo "[INFO] Envío automático desactivado (interruptor en servidor).\n";
    exit(0);
}

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

    // Resolver slot objetivo:
    // - exacto si coincide HH:MM
    // - o tolerancia hasta 4 minutos después (para tareas cada 5 minutos desfasadas)
    $slotObjetivo = null;
    if ($force) {
        $slotObjetivo = $ahora;
    } else {
        if (in_array($ahora, $horariosPermitidos, true)) {
            $slotObjetivo = $ahora;
        } else {
            $ahoraTs = strtotime($hoy . ' ' . $ahora . ':00');
            $candidatos = [];
            foreach ($horariosPermitidos as $h) {
                $slotTs = strtotime($hoy . ' ' . $h . ':00');
                if ($slotTs <= $ahoraTs) {
                    $candidatos[$h] = $ahoraTs - $slotTs;
                }
            }
            if (!empty($candidatos)) {
                asort($candidatos); // menor diferencia primero
                $nearest = array_key_first($candidatos);
                $diff = $candidatos[$nearest];
                if ($diff >= 0 && $diff <= 240) { // 4 minutos
                    $slotObjetivo = $nearest;
                }
            }
        }
    }

    if (!$force && $slotObjetivo === null) {
        echo "[INFO] Hora {$ahora} fuera de ventana programada (sin slot aplicable).\n";
        exit(0);
    }

    if (!$force && isset($estado['slots'][$slotObjetivo]) && ($estado['slots'][$slotObjetivo]['status'] ?? '') === 'success') {
        echo "[INFO] Slot {$slotObjetivo} ya enviado previamente hoy.\n";
        exit(0);
    }

    if (!$force && is_file($stateFile)) {
        $ultima = trim((string)@file_get_contents($stateFile));
        if ($ultima === $slotObjetivo) {
            echo "[INFO] Ya se envió en esta ventana CDMX ({$slotObjetivo}).\n";
            exit(0);
        }
    }

    $reporteria = new \Controllers\Reporteria();
    $resultado = $reporteria->enviarCorreoVencimientosLunesProgramado($destinatarios, $asunto);
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

