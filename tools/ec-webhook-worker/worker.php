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
 *   php worker.php --no-reintento-errores   (no ejecuta segunda pasada solo sobre id(s) que fallaron por S2 o BD)
 *   Tras la 2.ª pasada, si aún hay fallos en esos id(s), se escribe ec_worker_errores_reintento_YYYYMMDD_HHMMSS.csv
 *   (misma carpeta que el Excel con --ids-xlsx) y una línea ERRORES_REINTENTO_CSV= en stdout para el agente UI.
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
 * Progreso: en Chat (sin --no-chat) mensaje inicial, hitos 25/50/75/100% y resumen; en consola/log del agente,
 * avance por cada porcentaje entero (1%…100%) además del detalle por crédito [n/total].
 *
 * Config: copiar config.example.env a config.local.env o exportar variables de entorno.
 */

declare(strict_types=1);

// Con salida a tubería (agente Node), PHP suele bufferizar stdout: la UI no ve avance hasta el fin.
@ini_set('output_buffering', '0');
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '1');
while (ob_get_level() > 0) {
    ob_end_flush();
}

/**
 * Fuerza envío inmediato de líneas al proceso padre (log del agente).
 */
function ec_worker_stdout_flush(): void
{
    if (function_exists('fflush')) {
        @fflush(\STDOUT);
    }
}

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
    'no-reintento-errores',
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
$noReintentoErrores = array_key_exists('no-reintento-errores', $opts);
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
    echo "[ec-webhook-worker] Lote S2: {$total} id(s), fecha corte {$fechaCorte}. "
        . "En este log: avance cada 1% (línea propia) + detalle por crédito; Chat sigue con hitos 25/50/75/100%.\n";
    ec_worker_stdout_flush();
}

$ok = 0;
$fail = 0;
$milestoneSent = [];
/** Último % entero ya impreso en consola (progreso lineal, distinto al Chat). */
$logAvanceUltPct = -1;
/** @var int[] */
$reintentoPorS2Fallo = [];
/** @var int[] */
$reintentoPorBdFallo = [];

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
                        $reintentoPorBdFallo[] = (int) $idCredito;
                        echo 'OK (S2); ERROR BD: ' . $e->getMessage() . "\n";
                    }
                }
                if ($chatEach) {
                    postGoogleChat($webhookUrl, $line);
                }
            } else {
                $fail++;
                $reintentoPorS2Fallo[] = (int) $idCredito;
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
    if (!$dryRun) {
        maybePostProgressMilestones($webhookUrl, $milestoneSent, $processed, $total, $noChat);
        ec_worker_echo_avance_log_lineal($processed, $total, $logAvanceUltPct);
    }
    ec_worker_stdout_flush();

    if ($delayMs > 0 && $idx < $total - 1) {
        usleep($delayMs * 1000);
    }
}

/** @var array<int, array{tipo: string, detalle: string}> id_credito => fila para CSV (solo tras 2.ª pasada si sigue mal) */
$erroresTrasReintento = [];

if (!$dryRun && !$noReintentoErrores) {
    $idsSegunda = array_values(array_unique(array_merge($reintentoPorS2Fallo, $reintentoPorBdFallo)));
    if ($idsSegunda !== []) {
        $n2 = count($idsSegunda);
        echo "[ec-webhook-worker] Segunda pasada: reintentando {$n2} id(s) (fallo S2 o error BD tras S2 OK)...\n";
        if (!$noChat) {
            postGoogleChat(
                $webhookUrl,
                "*Reintento automático:* se vuelve a procesar *{$n2}* id(s) que fallaron en la primera pasada."
            );
        }
        foreach ($idsSegunda as $j2 => $idCredito) {
            if ($delayMs > 0 && $j2 > 0) {
                usleep($delayMs * 1000);
            }
            echo '[reintento ' . ($j2 + 1) . "/{$n2}] Crédito {$idCredito} ... ";
            $pre = ecWorkerValidarTerritorioCredito($idCredito, $saltarChequeoPais);
            if (!$pre['ok']) {
                $errPre = $pre['error'] ?? 'Validación territorio';
                $erroresTrasReintento[(int) $idCredito] = [
                    'tipo' => 'Validación territorio',
                    'detalle' => $errPre,
                ];
                echo "SKIP: {$errPre}\n";
                continue;
            }
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
                $eraS2 = in_array((int) $idCredito, $reintentoPorS2Fallo, true);
                if ($eraS2) {
                    $fail--;
                    $ok++;
                }
                if ($soloS2) {
                    echo "OK\n";
                } else {
                    $notasRaw = $result['datosNotasCargos'] ?? [];
                    $notas = is_array($notasRaw) ? $notasRaw : [];
                    try {
                        $cruce = \Models\EstadoCuenta::procesarGastosCobranzaDesdeNotas($notas, $idCredito, true);
                        if (in_array((int) $idCredito, $reintentoPorBdFallo, true) && $bdErrors > 0) {
                            $bdErrors--;
                        }
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
                        $erroresTrasReintento[(int) $idCredito] = [
                            'tipo' => 'Error cruce BD (gastos_cobranza)',
                            'detalle' => $e->getMessage(),
                        ];
                        echo 'OK (S2); ERROR BD: ' . $e->getMessage() . "\n";
                    }
                }
            } else {
                $err = $result['error'] ?? 'Error desconocido';
                $erroresTrasReintento[(int) $idCredito] = [
                    'tipo' => 'Error API S2',
                    'detalle' => $err,
                ];
                echo "ERROR: {$err}\n";
            }
        }

        if ($erroresTrasReintento !== []) {
            ecWorkerWriteErroresReintentoCsv($idsXlsxOpt, $baseDir, $erroresTrasReintento);
        }
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
function maybePostProgressMilestones(
    string $webhookUrl,
    array &$milestoneSent,
    int $processed,
    int $total,
    bool $noChat
): void {
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
        if ($webhookUrl !== '' && !$noChat) {
            postGoogleChat($webhookUrl, $msg);
        }
    }
}

/**
 * Consola/agente: una línea cada vez que sube el porcentaje entero (1, 2, … 100), más lineal que el Chat.
 *
 * @param int $ultimoPctImpreso se actualiza con el último % ya mostrado (-1 = ninguno)
 */
function ec_worker_echo_avance_log_lineal(int $processed, int $total, int &$ultimoPctImpreso): void
{
    if ($total <= 0) {
        return;
    }
    $pct = (int) floor((100 * $processed) / $total);
    if ($processed < $total && $pct < 1) {
        return;
    }
    if ($pct === $ultimoPctImpreso) {
        return;
    }
    $ultimoPctImpreso = $pct;
    echo "[ec-webhook-worker] Avance: {$processed}/{$total} ({$pct}%)\n";
    ec_worker_stdout_flush();
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

/**
 * CSV con id_credito, tipo_error, detalle — solo cuando tras la 2.ª pasada siguen fallos.
 *
 * @param array<int, array{tipo: string, detalle: string}> $filasPorId
 */
function ecWorkerWriteErroresReintentoCsv(string $idsXlsxOpt, string $baseDir, array $filasPorId): void
{
    if ($filasPorId === []) {
        return;
    }
    $outDir = $idsXlsxOpt !== '' ? dirname($idsXlsxOpt) : $baseDir;
    if (!is_dir($outDir) || !is_writable($outDir)) {
        fwrite(STDERR, "[ec-webhook-worker] No se pudo escribir CSV de errores (directorio no escribible): {$outDir}\n");

        return;
    }
    $ts = date('Ymd_His');
    $nombreCsv = "ec_worker_errores_reintento_{$ts}.csv";
    $outPath = $outDir . DIRECTORY_SEPARATOR . $nombreCsv;
    $fp = fopen($outPath, 'wb');
    if ($fp === false) {
        fwrite(STDERR, "[ec-webhook-worker] No se pudo crear {$nombreCsv}\n");

        return;
    }
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, ['id_credito', 'tipo_error', 'detalle'], ';');
    ksort($filasPorId, SORT_NUMERIC);
    foreach ($filasPorId as $idCredito => $r) {
        fputcsv($fp, [(string) (int) $idCredito, $r['tipo'], $r['detalle']], ';');
    }
    fclose($fp);
    echo "[ec-webhook-worker] ERRORES_REINTENTO_CSV={$nombreCsv}\n";
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
