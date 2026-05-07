<?php
/**
 * CLI: envía por correo el Excel del reporte Gastos Cobranza (misma SMTP [mail] que Reporteria).
 * Lo invoca el agente Node tras un /run exitoso.
 *
 * Uso:
 *   php enviar_reporte_gc_excel.php /ruta/absoluta/reporte_cobranza_DD-MM-YYYY.xlsx
 *
 * Destinatarios: si GASTOS_GC_REPORTE_MAIL_TO está definida y no vacía, tiene prioridad (correos separados por coma).
 * Si no, usa la lista guardada en Shell Gastos Cobranza → Administrar correos (`backend/storage/config/gastos_cobranza_destinatarios.json`).
 * Si ese archivo no existe o no hay activos, cae al mismo par por defecto que antes en código.
 */

declare(strict_types=1);

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
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }
            $key = trim(substr($line, 0, $eq));
            if ($key === '') {
                continue;
            }
            if (strpos($key, 'MAIL_') !== 0 && strpos($key, 'GASTOS_GC_REPORTE_') !== 0) {
                continue;
            }
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

$xlsx = isset($argv[1]) ? trim((string) $argv[1]) : '';
if ($xlsx === '' || !is_file($xlsx)) {
    fwrite(STDERR, "[ERROR] Indique ruta absoluta a un .xlsx existente.\n");
    exit(1);
}

/**
 * Destinatarios: si existe GASTOS_GC_REPORTE_MAIL_TO (no vacía), tiene prioridad (despliegues/cron).
 * Si no, la lista guardada desde Shell Gastos Cobranza (`storage/config/gastos_cobranza_destinatarios.json`).
 */
$rawTo = getenv('GASTOS_GC_REPORTE_MAIL_TO');
$dest = [];
if ($rawTo !== false && trim($rawTo) !== '') {
    foreach (explode(',', $rawTo) as $em) {
        $em = strtolower(trim((string) $em));
        if ($em !== '') {
            $dest[] = $em;
        }
    }
    $dest = array_values(array_unique($dest));
} else {
    $dest = \Controllers\Gastoscobranza::destinatariosActivosReporteGcDesdeArchivo();
}

try {
    $reporteria = new \Controllers\Reporteria();
    $r = $reporteria->enviarCorreoReporteGastosCobranza($dest, $xlsx);
    if (!empty($r['success'])) {
        echo '[OK] ' . ($r['mensaje'] ?? 'Enviado') . "\n";
        exit(0);
    }
    $msg = $r['mensaje'] ?? 'Error desconocido';
    fwrite(STDERR, '[ERROR] ' . $msg . "\n");
    exit(1);
} catch (\Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . "\n");
    exit(1);
}
