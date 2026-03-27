<?php
/**
 * Worker independiente: consulta estado de cuenta en S2 (un crédito por llamada)
 * y notifica resultado a Google Chat vía webhook entrante.
 *
 * Uso:
 *   php worker.php
 *   php worker.php --file=ruta/creditos.txt
 *   php worker.php --ids-xlsx="ruta\archivo.xlsx" [--ids-xlsx-column="ID CREDITO"]
 *   php worker.php --dry-run
 *   php worker.php --no-chat
 *   php worker.php --chat-each   (notifica cada crédito a Chat; por defecto solo progreso % + resumen)
 *   php worker.php --solo-s2     (solo consulta S2 y Chat habitual; sin cruce gastos_cobranza)
 *   php worker.php --fecha-corte=2026-03-25   (opcional; por defecto hoy o FECHA_CORTE en .env)
 *   php worker.php --omitir-primeros=1216     (reanudar: salta los primeros N id(s) del Excel o .txt, en orden)
 *   php worker.php --no-auditoria   (no inserta en auditoria___SPARTA_SECRET_REDACTED__)
 *   php worker.php --saltar-chequeo-pais   (no valida MX vs Guatemala antes de S2)
 *
 * Paridad con el menú «Estado de cuenta» (Consulta + POST):
 *   - Validación MX/GT vía Empresa (mismo criterio que validarCredito); use --saltar-chequeo-pais para omitir.
 *   - Llamada API S2 (estadocuenta) con fecha de corte.
 *   - Comprueba que la respuesta traiga idCredito en estadoCuenta (como validarCredito).
 *   - Registro en auditoria___SPARTA_SECRET_REDACTED__ (usuario configurable); use --no-auditoria para omitir.
 *   - Cruce gastos_cobranza (__SPARTA_SECRET_REDACTED__): Models\EstadoCuenta::procesarGastosCobranzaDesdeNotas
 *     salvo --solo-s2.
 * Lo que NO hace el worker (solo muestra la pantalla, no escribe en mega-reporte salvo el cruce anterior):
 *   armado de tabla de cuotas, motor v2/legacy, contracargos/reembolsos en UI, notas crédito visuales,
 *   carga de direcciones/referencias/notas internas para la vista, gestión externa MX, etc.
 *
 * Progreso en Chat (sin --no-chat): mensaje inicial, luego 25% / 50% / 75% / 100%, y resumen final.
 *
 * Config: copiar config.example.env a config.local.env o exportar variables de entorno.
 */

declare(strict_types=1);

$baseDir = dirname(__FILE__);
loadEnvFile($baseDir . '/config.local.env');
require_once $baseDir . '/../ec-shared/ec___SPARTA_SECRET_REDACTED___pipeline.php';

$opts = getopt('', [
    'file:',
    'ids-xlsx:',
    'ids-xlsx-column:',
    'dry-run',
    'no-chat',
    'delay:',
    'chat-each',
    'solo-s2',
    'fecha-corte:',
    'no-auditoria',
    'saltar-chequeo-pais',
    'omitir-primeros:',
]);
if ($opts === false) {
    $opts = [];
}

$idsXlsxOpt = isset($opts['ids-xlsx']) ? trim((string) $opts['ids-xlsx'], " \t\"'") : '';
$idsXlsxCol = isset($opts['ids-xlsx-column'])
    ? trim((string) $opts['ids-xlsx-column'], " \t\"'")
    : 'ID CREDITO';
$fileOpt = isset($opts['file']) ? trim((string) $opts['file'], " \t\"'") : '';

if ($idsXlsxOpt !== '' && $fileOpt !== '') {
    fwrite(STDERR, "Use solo uno: --ids-xlsx o --file.\n");
    exit(1);
}

$dryRun = array_key_exists('dry-run', $opts);
$noChat = array_key_exists('no-chat', $opts);
$chatEach = array_key_exists('chat-each', $opts);
$soloS2 = array_key_exists('solo-s2', $opts);
$noAuditoria = array_key_exists('no-auditoria', $opts);
$saltarChequeoPais = array_key_exists('saltar-chequeo-pais', $opts);
$delayMs = isset($opts['delay']) ? max(0, (int) $opts['delay']) : (int) (getenv('DELAY_MS_BETWEEN_CREDITS') ?: 500);
$usuarioAuditoria = trim((string) (getenv('EC_WORKER_AUDITORIA_USUARIO') ?: 'ec-webhook-worker'), " \t\"'");

$token = getenv('TOKEN') ?: '';
$endpoint = getenv('ENDPOINT') ?: 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
$webhookUrl = getenv('GOOGLE_CHAT_WEBHOOK_URL') ?: '';

if ($token === '' && !$dryRun) {
    fwrite(STDERR, "Falta TOKEN en config.local.env o entorno.\n");
    exit(1);
}
if ($webhookUrl === '' && !$dryRun && !$noChat) {
    fwrite(STDERR, "Falta GOOGLE_CHAT_WEBHOOK_URL o use --no-chat.\n");
    exit(1);
}

$sourceDesc = '';
if ($idsXlsxOpt !== '') {
    $ids = loadCreditIdsFromExcel($idsXlsxOpt, $idsXlsxCol, $baseDir);
    $sourceDesc = 'Excel «' . basename($idsXlsxOpt) . '»';
} elseif ($fileOpt !== '') {
    $ids = loadCreditIds($fileOpt);
    $sourceDesc = 'archivo «' . basename($fileOpt) . '»';
} else {
    $creditosFile = getenv('CREDITOS_FILE') ?: $baseDir . '/creditos.txt';
    $ids = loadCreditIds($creditosFile);
    $sourceDesc = 'archivo «' . basename($creditosFile) . '»';
}

if (empty($ids)) {
    fwrite(STDERR, "No hay IDs válidos cargados.\n");
    exit(1);
}

$omitirPrimeros = isset($opts['omitir-primeros']) ? max(0, (int) $opts['omitir-primeros']) : 0;
$totalCargados = count($ids);
if ($omitirPrimeros > 0) {
    if ($omitirPrimeros >= $totalCargados) {
        fwrite(STDERR, "--omitir-primeros ({$omitirPrimeros}) es mayor o igual al total de id(s) ({$totalCargados}); no queda nada que procesar.\n");
        exit(1);
    }
    $ids = array_values(array_slice($ids, $omitirPrimeros));
    echo "[ec-webhook-worker] Reanudación: omitidos los primeros {$omitirPrimeros} id(s); quedan " . count($ids) . " por procesar.\n";
}

echo "[ec-webhook-worker] Créditos a procesar: " . count($ids) . ($dryRun ? " (dry-run)\n" : "\n");
if (!$dryRun && !$soloS2) {
    echo "[ec-webhook-worker] Tras S2 OK se aplicará cruce en BD (gastos_cobranza). Use --solo-s2 para omitir ese paso.\n";
}
if (!$dryRun && !$noAuditoria) {
    echo "[ec-webhook-worker] Auditoría por crédito en auditoria___SPARTA_SECRET_REDACTED__ (usuario «{$usuarioAuditoria}»). Use --no-auditoria para omitir.\n";
}
if (!$dryRun && $saltarChequeoPais) {
    echo "[ec-webhook-worker] Aviso: --saltar-chequeo-pais (no se valida Guatemala vs MX antes de S2).\n";
}

$total = count($ids);
$fechaCorteOpt = isset($opts['fecha-corte']) ? trim((string) $opts['fecha-corte'], " \t\"'") : '';
$fechaCorteEnv = trim((string) (getenv('FECHA_CORTE') ?: ''), " \t\"'");
$fechaCorte = $fechaCorteOpt !== '' ? $fechaCorteOpt : ($fechaCorteEnv !== '' ? $fechaCorteEnv : date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCorte)) {
    fwrite(STDERR, "Fecha de corte inválida (use YYYY-MM-DD): {$fechaCorte}\n");
    exit(1);
}

if (!$dryRun) {
    ecWorkerBootstrapBackend($baseDir);
}

$bdErrors = 0;

if (!$dryRun && !$noChat && $total > 0) {
    $modoBd = $soloS2 ? 'Solo consulta API (sin escribir gastos_cobranza).' : 'Tras cada S2 OK se aplica el cruce en BD (gastos_cobranza), igual que la pantalla.';
    $ini = "*Estado de cuenta (lote S2)*\n\n"
        . "*Inicio:* se está procesando {$sourceDesc}.\n"
        . "Total de id(s) de crédito a consultar en la API: *{$total}*.\n"
        . "_{$modoBd}_\n"
        . "_Fecha de corte:_ {$fechaCorte}.";
    postGoogleChat($webhookUrl, $ini);
}

$ok = 0;
$fail = 0;
$milestoneSent = [];

foreach ($ids as $idx => $idCredito) {
    $n = $idx + 1;
    echo "[{$n}/{$total}] Crédito {$idCredito} ... ";

    if ($dryRun) {
        echo "dry-run (sin llamar S2 ni Chat)\n";
        $ok++;
    } else {
        $pre = ecWorkerValidarTerritorioCredito($idCredito, $saltarChequeoPais);
        if (!$pre['ok']) {
            $fail++;
            if (!$noAuditoria) {
                \Models\EstadoCuenta::registrarAuditoria(
                    $usuarioAuditoria,
                    $idCredito,
                    $fechaCorte,
                    0,
                    $pre['error'] ?? null
                );
            }
            $errPre = $pre['error'] ?? 'Validación territorio';
            echo "SKIP: {$errPre}\n";
            if ($chatEach) {
                postGoogleChat($webhookUrl, "EC #{$idCredito}: SKIP — {$errPre}");
            }
        } else {
            $result = consultarEstadoCuentaS2($endpoint, $token, $idCredito, $fechaCorte);
            if (!$noAuditoria) {
                \Models\EstadoCuenta::registrarAuditoria(
                    $usuarioAuditoria,
                    $idCredito,
                    $fechaCorte,
                    !empty($result['success']) ? 1 : 0,
                    $result['success'] ? null : ($result['error'] ?? null)
                );
            }
            if ($result['success']) {
                $ok++;
                $saldo = $result['saldo_resumen'] ?? '';
                $line = "EC #{$idCredito}: OK — {$fechaCorte}" . ($saldo !== '' ? " — {$saldo}" : '');
                if ($soloS2) {
                    echo "OK\n";
                } else {
                    $notasRaw = $result['datosNotasCargos'] ?? [];
                    $notas = is_array($notasRaw) ? $notasRaw : [];
                    try {
                        $cruce = \Models\EstadoCuenta::procesarGastosCobranzaDesdeNotas($notas, $idCredito, true);
                        $nG = count($cruce['gastos_procesados'] ?? []);
                        if (!empty($cruce['_sin_actualizacion'])) {
                            $causa = (string) ($cruce['_causa'] ?? '');
                            echo 'OK (S2); BD sin cambios' . ($causa !== '' ? " ({$causa})" : '') . "\n";
                        } elseif ($nG > 0) {
                            echo "OK (S2 + BD: {$nG} gasto(s))\n";
                        } else {
                            echo "OK (S2)\n";
                        }
                    } catch (\Throwable $e) {
                        $bdErrors++;
                        echo 'OK (S2); ERROR BD: ' . $e->getMessage() . "\n";
                    }
                }
                if ($chatEach) {
                    postGoogleChat($webhookUrl, $line);
                }
            } else {
                $fail++;
                $err = $result['error'] ?? 'Error desconocido';
                $line = "EC #{$idCredito}: ERROR — {$err}";
                echo "ERROR: {$err}\n";
                if ($chatEach) {
                    postGoogleChat($webhookUrl, $line);
                }
            }
        }
    }

    $processed = $idx + 1;
    if (!$dryRun && !$noChat) {
        maybePostProgressMilestones($webhookUrl, $milestoneSent, $processed, $total);
    }

    if ($delayMs > 0 && $idx < $total - 1) {
        usleep($delayMs * 1000);
    }
}

$resumen = buildResumenChatText($ok, $fail, $total, $fechaCorte, $bdErrors);
if ($fail === 0) {
    echo "Se procesaron correctamente {$ok}/{$total} id(s) de crédito. Fecha de corte: {$fechaCorte}.\n";
} else {
    echo "Correctos {$ok}/{$total}; fallaron por revisar {$fail}/{$total}. Fecha de corte: {$fechaCorte}.\n";
}
if ($bdErrors > 0) {
    echo "Advertencia: {$bdErrors} crédito(s) con error al aplicar cruce en BD (la consulta S2 había sido OK).\n";
}

$pctLine = "*Progreso:* *100%* — se han procesado *{$total}/{$total}* id(s) de crédito (consulta S2 completada).";
if (!$dryRun && !$noChat && $total > 0) {
    postGoogleChat($webhookUrl, $pctLine);
    postGoogleChat($webhookUrl, $resumen);
}

exit($fail > 0 ? 2 : 0);

/**
 * Hitos 25 / 50 / 75 % según avance real (floor). Como mucho un mensaje por crédito: el mayor umbral nuevo.
 *
 * @param array<int, bool> $milestoneSent
 */
function maybePostProgressMilestones(string $webhookUrl, array &$milestoneSent, int $processed, int $total): void
{
    if ($total <= 0) {
        return;
    }
    $pctFloor = (int) floor((100 * $processed) / $total);
    $lastNew  = null;
    foreach ([25, 50, 75] as $m) {
        if (!empty($milestoneSent[$m])) {
            continue;
        }
        if ($pctFloor >= $m) {
            $milestoneSent[$m] = true;
            $lastNew = $m;
        }
    }
    if ($lastNew !== null) {
        $msg = "*Progreso:* se ha alcanzado al menos el *{$lastNew}%* del lote (*{$processed}/{$total}* id(s) consultados en S2).";
        postGoogleChat($webhookUrl, $msg);
    }
}

function loadEnvFile(string $path): void
{
    if (!is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $p = strpos($line, '=');
        if ($p === false) {
            continue;
        }
        $k = trim(substr($line, 0, $p));
        $v = trim(substr($line, $p + 1));
        $v = trim($v, " \t\"'");
        if ($k !== '') {
            putenv($k . '=' . $v);
        }
    }
}

function resolvePhpSpreadsheetAutoload(string $baseDir): ?string
{
    $root = spartaLedgerRoot($baseDir);
    $candidates = [
        $root . '/backend/libs/PhpSpreadsheet/vendor/autoload.php',
        $root . '/backend/Libs/PhpSpreadsheet/vendor/autoload.php',
    ];
    foreach ($candidates as $p) {
        if (is_readable($p)) {
            return $p;
        }
    }

    return null;
}

function normalizarEncabezadoExcel(string $s): string
{
    return trim(preg_replace('/\s+/u', ' ', $s));
}

/**
 * @return int[]
 */
function loadCreditIdsFromExcel(string $ruta, string $nombreColumna, string $baseDir): array
{
    if (!is_readable($ruta)) {
        fwrite(STDERR, "No se puede leer el Excel: {$ruta}\n");
        return [];
    }
    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    if ($ext !== 'xlsx') {
        fwrite(STDERR, "Solo .xlsx: {$ruta}\n");
        return [];
    }
    $auto = resolvePhpSpreadsheetAutoload($baseDir);
    if ($auto === null) {
        fwrite(STDERR, "PhpSpreadsheet no encontrado en backend/libs/PhpSpreadsheet.\n");
        return [];
    }
    require_once $auto;

    $objetivo = normalizarEncabezadoExcel($nombreColumna);
    if ($objetivo === '') {
        fwrite(STDERR, "Nombre de columna vacío.\n");
        return [];
    }

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta);
        $sheet = $spreadsheet->getActiveSheet();
        $maxRow = (int) $sheet->getHighestDataRow();
        $maxColStr = $sheet->getHighestDataColumn();
        $maxColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxColStr);
    } catch (\Throwable $e) {
        fwrite(STDERR, "Error al leer Excel: " . $e->getMessage() . "\n");
        return [];
    }

    if ($maxRow < 1 || $maxColIdx < 1) {
        return [];
    }

    $headerRow = null;
    $colIdx = null;
    $limBusq = min(50, $maxRow);

    for ($r = 1; $r <= $limBusq; $r++) {
        for ($c = 1; $c <= $maxColIdx; $c++) {
            $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c) . $r;
            $raw = $sheet->getCell($coord)->getValue();
            $txt = normalizarEncabezadoExcel((string) $raw);
            if ($txt !== '' && strcasecmp($txt, $objetivo) === 0) {
                $headerRow = $r;
                $colIdx = $c;
                break 2;
            }
        }
    }

    if ($headerRow === null || $colIdx === null) {
        fwrite(STDERR, "No se encontró la columna de encabezado «{$nombreColumna}» (primeras filas).\n");
        return [];
    }

    $ids = [];
    for ($r = $headerRow + 1; $r <= $maxRow; $r++) {
        $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $r;
        $val = $sheet->getCell($coord)->getCalculatedValue();

        if ($val === null || $val === '') {
            continue;
        }
        if (is_numeric($val)) {
            $id = (int) round((float) $val);
            if ($id > 0) {
                $ids[] = $id;
            }
            continue;
        }
        $s = preg_replace('/[^\d]/', '', normalizarEncabezadoExcel((string) $val));
        if ($s !== '' && strlen($s) <= 20) {
            $ids[] = (int) $s;
        }
    }

    return array_values(array_unique($ids));
}

/**
 * @return int[]
 */
function loadCreditIds(string $path): array
{
    if (!is_readable($path)) {
        return [];
    }
    $raw = file($path, FILE_IGNORE_NEW_LINES);
    if ($raw === false) {
        return [];
    }
    $out = [];
    foreach ($raw as $line) {
        $line = trim($line);
        if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
            continue;
        }
        if (ctype_digit($line)) {
            $id = (int) $line;
            if ($id > 0) {
                $out[] = $id;
            }
        }
    }
    return array_values(array_unique($out));
}

/**
 * Mensaje único para Google Chat (markdown básico soportado por la API).
 */
function buildResumenChatText(int $ok, int $fail, int $total, string $fechaCorte, int $bdErrors = 0): string
{
    if ($total <= 0) {
        return 'No se evaluó ningún id de crédito.';
    }
    $fecha = $fechaCorte;
    $alertaBd = '';
    if ($bdErrors > 0) {
        $alertaBd = "\n\n_Alerta:_ *{$bdErrors}* crédito(s): S2 OK pero *falló el cruce en BD* (gastos_cobranza). Revisar consola.";
    }
    if ($fail === 0) {
        return (
            "*Estado de cuenta (lote S2)*\n\n"
            . "*Resumen final.* Procesamiento sin incidencias en API.\n"
            . "Se procesaron correctamente *{$ok}/{$total}* id(s) de crédito (S2 + cruce BD cuando aplica).\n\n"
            . "_Fecha de corte:_ {$fecha}."
            . $alertaBd
        );
    }

    return (
        "*Estado de cuenta (lote S2)*\n\n"
        . "*Resumen final.* Hubo incidencias.\n"
        . "Correctos: *{$ok}/{$total}* id(s).\n"
        . "Fallaron por revisar: *{$fail}/{$total}* id(s) (error de API o respuesta inválida).\n\n"
        . "_Fecha de corte:_ {$fecha}.\n"
        . "_Detalle:_ revisar consola o repetir los que marcaron ERROR."
        . $alertaBd
    );
}

function postGoogleChat(string $webhookUrl, string $text): bool
{
    $body = json_encode(['text' => $text], JSON_UNESCAPED_UNICODE);
    $ch = curl_init($webhookUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json; charset=UTF-8',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $res = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code < 200 || $code >= 300) {
        fwrite(STDERR, "Chat webhook HTTP {$code}: " . ($res !== false ? substr((string) $res, 0, 200) : '') . "\n");
        return false;
    }
    return true;
}
