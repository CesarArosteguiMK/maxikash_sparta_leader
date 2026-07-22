<?php
/**
 * Añade al Excel dos columnas calculadas desde la API S2 (estado de cuenta):
 *
 * 1) SOBRE COBRO GC: si el libro tiene columna «SALDO APLICABLE A GC» y en esa fila el valor es ≤ tope ($250),
 *    el exceso es 0 (aunque en S2 haya notas GC que sumen más). Si ese saldo es > tope, el exceso sigue siendo
 *    max(0, suma(montos notas GC en S2) − tope × número de notas GC). Sin columna de saldo aplicable, solo la fórmula S2.
 *
 * 2) SOBRANTE (EXT.): solo si saldoVencidoExtemporaneos > 0 (hay “deuda” vencida en extemporáneos).
 *    Entonces: suma de extemporáneos de datosPagos en la última fecha (fechaValor) en que hubo pago
 *    a extemporáneos, menos saldoVencidoExtemporaneos (ej. mismo día 116+1000 − 634 = 482).
 *    Si saldoVencidoExtemporaneos es 0, el sobrante es 0 (no se usa el histórico acumulado de ext.).
 *
 * Requiere columna de crédito (encabezado CREDITO o ID CREDITO). Opcional: detecta SALDO APLICABLE A GC
 * solo para ubicar columnas; los valores nuevos vienen de S2, no de esa celda.
 *
 * Uso:
 *   php enrich_gc_excel.php --input="C:\ruta\archivo.xlsx"
 *   php enrich_gc_excel.php --input="..." --output="C:\ruta\salida.xlsx" --fecha-corte=2026-03-27
 *
 * Token: mismo config.local.env que ec-webhook-worker (TOKEN, ENDPOINT opcional).
 *
 * Mismo pipeline que worker.php (validación MX/GT, S2, auditoría, cruce gastos_cobranza), salvo:
 *   --solo-columnas     solo API S2 + columnas Excel: no carga backend, no BD, no auditoría, no MX/GT
 *   --solo-s2           no escribe BD (solo Excel desde S2); sigue auditoría y validación MX/GT si aplica
 *   --no-auditoria      no inserta en auditoria___SPARTA_SECRET_REDACTED__
 *   --saltar-chequeo-pais
 *   --max-creditos=50   (solo primeros N créditos con ID válido; prueba sin recorrer todo el libro)
 *   --omitir-primeros=1215  (reanudar: ignora los primeros N créditos con ID válido en orden de fila; no los modifica)
 *   --chat              notificaciones a Google Chat (GOOGLE_CHAT_WEBHOOK_URL en config.local.env), hitos 25/50/75/100%
 *   EC_WORKER_AUDITORIA_USUARIO (por defecto ec-gc-excel-enrich)
 */

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/** Concepto S2 para notas de gasto de cobranza (debe definirse antes del exit() del flujo principal). */
const CONCEPTO_GC = 'NOTA DE DE CARGO GASTOS DE COBRANZA';

date_default_timezone_set('America/Mexico_City');

$baseDir = dirname(__FILE__);
$workerEnv = dirname($baseDir) . DIRECTORY_SEPARATOR . 'ec-webhook-worker' . DIRECTORY_SEPARATOR . 'config.local.env';
loadEnvFile($workerEnv);
require_once dirname($baseDir) . DIRECTORY_SEPARATOR . 'ec-shared' . DIRECTORY_SEPARATOR . 'ec_estado_cuenta_pipeline.php';

$opts = getopt('', [
    'input:',
    'output:',
    'fecha-corte:',
    'delay-ms:',
    'dry-run',
    'tope-gc:',
    'solo-s2',
    'solo-columnas',
    'no-auditoria',
    'saltar-chequeo-pais',
    'max-creditos:',
    'omitir-primeros:',
    'chat',
    'help',
]);
if ($opts === false || isset($opts['help'])) {
    fwrite(STDERR, "Uso: php enrich_gc_excel.php --input=archivo.xlsx [--output=salida.xlsx] [--fecha-corte=Y-m-d] [--delay-ms=400] [--max-creditos=N] [--omitir-primeros=N] [--dry-run] [--tope-gc=250] [--solo-columnas|--solo-s2] [--no-auditoria] [--saltar-chequeo-pais] [--chat]\n");
    exit(isset($opts['help']) ? 0 : 1);
}

$input = isset($opts['input']) ? trim((string) $opts['input'], " \t\"'") : '';
if ($input === '' || !is_readable($input)) {
    fwrite(STDERR, "Falta --input= o no se puede leer el archivo.\n");
    exit(1);
}

$output = isset($opts['output']) ? trim((string) $opts['output'], " \t\"'") : '';
if ($output === '') {
    $pi = pathinfo($input);
    $output = ($pi['dirname'] ?? '.') . DIRECTORY_SEPARATOR . ($pi['filename'] ?? 'salida') . '_enriquecido.xlsx';
}

$fechaCorte = isset($opts['fecha-corte']) ? trim((string) $opts['fecha-corte']) : '';
if ($fechaCorte === '') {
    $fechaCorte = trim((string) (getenv('FECHA_CORTE') ?: ''), " \t\"'");
}
if ($fechaCorte === '') {
    $fechaCorte = date('Y-m-d');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCorte)) {
    fwrite(STDERR, "fecha-corte inválida: {$fechaCorte}\n");
    exit(1);
}

$delayMs = isset($opts['delay-ms']) ? max(0, (int) $opts['delay-ms']) : 400;
$dryRun = array_key_exists('dry-run', $opts);
$soloColumnas = array_key_exists('solo-columnas', $opts);
$soloS2 = array_key_exists('solo-s2', $opts);
if ($soloColumnas) {
    $soloS2 = true;
}
$chat = array_key_exists('chat', $opts);
$noAuditoria = array_key_exists('no-auditoria', $opts);
$saltarChequeoPais = array_key_exists('saltar-chequeo-pais', $opts);
$usuarioAuditoria = trim((string) (getenv('EC_WORKER_AUDITORIA_USUARIO') ?: 'ec-gc-excel-enrich'), " \t\"'");
$maxCreditos = isset($opts['max-creditos']) ? max(0, (int) $opts['max-creditos']) : 0;
$omitirPrimeros = isset($opts['omitir-primeros']) ? max(0, (int) $opts['omitir-primeros']) : 0;
$topeGc = isset($opts['tope-gc']) ? (float) $opts['tope-gc'] : 250.0;
if ($topeGc <= 0) {
    $topeGc = 250.0;
}

$token = getenv('TOKEN') ?: '';
$endpoint = getenv('ENDPOINT') ?: 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
if ($token === '' && !$dryRun) {
    fwrite(STDERR, "Falta TOKEN (config.local.env del ec-webhook-worker).\n");
    exit(1);
}

$webhookUrl = trim((string) (getenv('GOOGLE_CHAT_WEBHOOK_URL') ?: ''), " \t\"'");
if ($chat && !$dryRun && $webhookUrl === '') {
    fwrite(STDERR, "Falta GOOGLE_CHAT_WEBHOOK_URL para usar --chat (p. ej. en config.local.env del ec-webhook-worker).\n");
    exit(1);
}

$autoload = spartaLedgerRoot($baseDir) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_readable($autoload)) {
    fwrite(STDERR, "No se encontró vendor/autoload.php (ejecute composer install en la raíz del proyecto).\n");
    exit(1);
}
require_once $autoload;

if (!$dryRun && !$soloColumnas) {
    ecWorkerBootstrapBackend($baseDir);
}
if (!$dryRun && $soloColumnas) {
    fwrite(STDERR, "[enrich_gc_excel] Modo --solo-columnas: solo API S2 + Excel; sin backend, BD, auditoría ni validación MX/GT.\n");
}
if (!$dryRun && !$soloColumnas && !$soloS2) {
    fwrite(STDERR, "[enrich_gc_excel] Tras S2 OK se aplicará cruce en BD (gastos_cobranza), igual que worker.php. Use --solo-s2 para solo Excel.\n");
}
if (!$dryRun && !$soloColumnas && !$noAuditoria) {
    fwrite(STDERR, "[enrich_gc_excel] Auditoría en auditoria___SPARTA_SECRET_REDACTED__ (usuario «{$usuarioAuditoria}»).\n");
}
if ($chat && !$dryRun) {
    fwrite(STDERR, "[enrich_gc_excel] Notificaciones a Google Chat activadas (hitos de progreso).\n");
}
if ($maxCreditos > 0) {
    fwrite(STDERR, "[enrich_gc_excel] Límite de prueba: solo se procesan los primeros {$maxCreditos} crédito(s) con ID válido.\n");
}
if ($omitirPrimeros > 0) {
    fwrite(STDERR, "[enrich_gc_excel] Reanudación: se omitirán los primeros {$omitirPrimeros} crédito(s) con ID válido (no se consulta S2 ni se escriben celdas en esas filas).\n");
    fwrite(STDERR, "[enrich_gc_excel] Aviso: si la corrida anterior no guardó el .xlsx, esas filas quedarán vacías en las columnas nuevas; el worker sí puede reanudar con seguridad porque la BD ya registró lo anterior.\n");
}

$spreadsheet = IOFactory::load($input);
$sheet = $spreadsheet->getActiveSheet();
$maxRow = (int) $sheet->getHighestDataRow();
$maxColStr = $sheet->getHighestDataColumn();
$maxColIdx = Coordinate::columnIndexFromString($maxColStr);

[$headerRow, $colCredito, $colSaldoAplicable] = encontrarColumnasGc($sheet, $maxRow, $maxColIdx);
if ($headerRow === null || $colCredito === null) {
    fwrite(STDERR, "No se encontró columna CREDITO ni ID CREDITO en las primeras filas.\n");
    exit(1);
}

$totalTodos = contarCreditosValidosEnHoja($sheet, $headerRow, $maxRow, $colCredito, 0);
$omitirEfectivo = min($omitirPrimeros, $totalTodos);
$restante = max(0, $totalTodos - $omitirEfectivo);
if ($maxCreditos > 0) {
    $totalCreditos = min($restante, $maxCreditos);
} else {
    $totalCreditos = $restante;
}
if ($totalCreditos <= 0) {
    fwrite(STDERR, "No hay créditos que procesar (omitir-primeros demasiado alto o libro sin IDs válidos).\n");
    exit(1);
}
$milestoneSent = [];

if ($chat && !$dryRun && $totalCreditos > 0 && $webhookUrl !== '') {
    $modoDesc = $soloColumnas
        ? 'Solo columnas Excel desde API S2 (sin BD, sin auditoría, sin validación MX/GT).'
        : ($soloS2 ? 'S2 + Excel; sin cruce gastos_cobranza en BD.' : 'S2 + Excel + auditoría y cruce BD según flags.');
    $base = basename($input);
    $ini = "*Excel GC (enriquecimiento)*\n\n"
        . "*Inicio:* se procesa `{$base}`.\n"
        . "Total de id(s) de crédito: *{$totalCreditos}*.\n"
        . "_{$modoDesc}_\n"
        . "_Fecha de corte:_ {$fechaCorte}.";
    postGoogleChat($webhookUrl, $ini);
}

$nuevaCol1 = $maxColIdx + 1;
$nuevaCol2 = $maxColIdx + 2;
$letra1 = Coordinate::stringFromColumnIndex($nuevaCol1);
$letra2 = Coordinate::stringFromColumnIndex($nuevaCol2);

$titulo1 = "SOBRE COBRO\nGC (tope $" . number_format($topeGc, 0, '.', '') . ")";
$titulo2 = "SOBRANTE\n(PAGO EXT. −\nVENC. EXT.)";

$sheet->setCellValue($letra1 . $headerRow, $titulo1);
$sheet->setCellValue($letra2 . $headerRow, $titulo2);
$sheet->getStyle($letra1 . $headerRow)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle($letra2 . $headerRow)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle($letra1 . $headerRow)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C00000']],
]);
$sheet->getStyle($letra2 . $headerRow)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
]);
$sheet->getRowDimension($headerRow)->setRowHeight(48);

$procesados = 0;
$errores = 0;
$bdErrors = 0;
$cuentaCreditos = 0;
$seenValid = -1;

for ($r = $headerRow + 1; $r <= $maxRow; $r++) {
    $coordId = Coordinate::stringFromColumnIndex($colCredito) . $r;
    $rawId = $sheet->getCell($coordId)->getCalculatedValue();
    $idCredito = parseIdCredito($rawId);
    if ($idCredito === null) {
        continue;
    }
    $seenValid++;
    if ($seenValid < $omitirEfectivo) {
        continue;
    }
    if ($maxCreditos > 0 && $cuentaCreditos >= $maxCreditos) {
        break;
    }
    $cuentaCreditos++;

    echo "[{$cuentaCreditos}/{$totalCreditos}] Crédito {$idCredito} ... ";

    if ($dryRun) {
        $sheet->setCellValue($letra1 . $r, '');
        $sheet->setCellValue($letra2 . $r, '');
        $procesados++;
        echo "dry-run\n";
        continue;
    }

    if (!$soloColumnas) {
        $pre = ecWorkerValidarTerritorioCredito($idCredito, $saltarChequeoPais);
        if (!$pre['ok']) {
            if (!$noAuditoria) {
                \Models\EstadoCuenta::registrarAuditoria(
                    $usuarioAuditoria,
                    $idCredito,
                    $fechaCorte,
                    0,
                    $pre['error'] ?? null
                );
            }
            $sheet->setCellValue($letra1 . $r, 'SKIP');
            $sheet->setCellValue($letra2 . $r, '—');
            $errores++;
            $errPre = (string) ($pre['error'] ?? '');
            echo "SKIP: {$errPre}\n";
            fwrite(STDERR, "[{$idCredito}] SKIP: " . ($pre['error'] ?? '') . "\n");
            $procesados++;
            aplicarFormatoTextoRelleno($sheet, $letra1 . $r, 'FFE6E6');
            aplicarFormatoTextoRelleno($sheet, $letra2 . $r, 'DDEBF7');
            if ($chat && $webhookUrl !== '' && $totalCreditos > 0) {
                maybePostProgressMilestones($webhookUrl, $milestoneSent, $cuentaCreditos, $totalCreditos);
            }
            if ($delayMs > 0 && $r < $maxRow) {
                usleep($delayMs * 1000);
            }
            continue;
        }
    }

    $result = consultarEstadoCuentaS2($endpoint, $token, $idCredito, $fechaCorte);
    if (!$soloColumnas && !$noAuditoria) {
        \Models\EstadoCuenta::registrarAuditoria(
            $usuarioAuditoria,
            $idCredito,
            $fechaCorte,
            !empty($result['success']) ? 1 : 0,
            $result['success'] ? null : ($result['error'] ?? null)
        );
    }

    if (!$result['success']) {
        $sheet->setCellValue($letra1 . $r, '—');
        $sheet->setCellValue($letra2 . $r, '—');
        $errores++;
        echo "ERROR API\n";
        fwrite(STDERR, "[{$idCredito}] Error API: " . ($result['error'] ?? '') . "\n");
        $procesados++;
    } else {
        $ec = $result['estadoCuenta'] ?? null;
        if (!is_array($ec)) {
            $sheet->setCellValue($letra1 . $r, '—');
            $sheet->setCellValue($letra2 . $r, '—');
            $errores++;
            echo "estadoCuenta inválido\n";
        } else {
            if (!$soloS2) {
                $notas = $result['datosNotasCargos'] ?? [];
                if (!is_array($notas)) {
                    $notas = [];
                }
                try {
                    \Models\EstadoCuenta::procesarGastosCobranzaDesdeNotas($notas, $idCredito, true);
                } catch (\Throwable $e) {
                    $bdErrors++;
                    fwrite(STDERR, "[{$idCredito}] ERROR BD (Excel sí se calcula): " . $e->getMessage() . "\n");
                }
            }
            $saldoApFila = null;
            if ($colSaldoAplicable !== null) {
                $coordSaldo = Coordinate::stringFromColumnIndex($colSaldoAplicable) . $r;
                $saldoApFila = parseMontoSaldoAplicableCelda($sheet->getCell($coordSaldo)->getCalculatedValue());
            }
            [$exceso, $sobrante] = calcularMetricasGc($ec, $topeGc, $saldoApFila);
            $sheet->setCellValue($letra1 . $r, $exceso);
            $sheet->setCellValue($letra2 . $r, $sobrante);
            echo "OK\n";
        }
        $procesados++;
    }

    aplicarFormatoMonedaRelleno($sheet, $letra1 . $r, 'FFE6E6');
    aplicarFormatoMonedaRelleno($sheet, $letra2 . $r, 'DDEBF7');

    if ($chat && $webhookUrl !== '' && $totalCreditos > 0) {
        maybePostProgressMilestones($webhookUrl, $milestoneSent, $cuentaCreditos, $totalCreditos);
    }

    if ($delayMs > 0 && $r < $maxRow) {
        usleep($delayMs * 1000);
    }
}

$sheet->getColumnDimension($letra1)->setWidth(18);
$sheet->getColumnDimension($letra2)->setWidth(20);

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($output);

echo "Listo: {$procesados} fila(s) con ID; errores API/skip: {$errores}; errores BD: {$bdErrors}. Guardado: {$output}\n";

if ($chat && !$dryRun && $totalCreditos > 0 && $webhookUrl !== '') {
    postGoogleChat($webhookUrl, "*Progreso:* *100%* — Excel GC: *{$totalCreditos}/{$totalCreditos}* crédito(s) recorridos.");
    $resumen = "*Excel GC: resumen*\n"
        . "Filas con ID: *{$procesados}* · Errores API/skip: *{$errores}* · Errores BD: *{$bdErrors}*\n"
        . "`{$output}`";
    postGoogleChat($webhookUrl, $resumen);
}

exit($errores > 0 ? 2 : 0);

// -------------------------------------------------------------------------
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

function normHeader(string $s): string
{
    return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $s)));
}

/**
 * @return array{0:?int, 1:?int, 2:?int} [filaEncabezado, colCredito, colSaldoAplicable|null]
 */
function encontrarColumnasGc(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $maxRow, int $maxColIdx): array
{
    $lim = min(50, $maxRow);
    for ($r = 1; $r <= $lim; $r++) {
        $colCred = null;
        $colSaldo = null;
        for ($c = 1; $c <= $maxColIdx; $c++) {
            $coord = Coordinate::stringFromColumnIndex($c) . $r;
            $txt = normHeader((string) $sheet->getCell($coord)->getValue());
            if ($txt === 'CREDITO' || $txt === 'ID CREDITO' || $txt === 'ID_CREDITO') {
                $colCred = $c;
            }
            if (
                $txt === 'SALDO APLICABLE A GC'
                || (strpos($txt, 'SALDO APLICABLE') !== false && strpos($txt, 'GC') !== false)
            ) {
                $colSaldo = $c;
            }
        }
        if ($colCred !== null) {
            return [$r, $colCred, $colSaldo];
        }
    }

    return [null, null, null];
}

function parseIdCredito(mixed $raw): ?int
{
    if ($raw === null || $raw === '') {
        return null;
    }
    if (is_numeric($raw)) {
        $n = (int) round((float) $raw);

        return $n > 0 ? $n : null;
    }
    $s = preg_replace('/[^\d]/', '', (string) $raw);

    return ($s !== '' && strlen($s) <= 15) ? (int) $s : null;
}

function contarCreditosValidosEnHoja(
    \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
    int $headerRow,
    int $maxRow,
    int $colCredito,
    int $maxCreditos
): int {
    $n = 0;
    for ($r = $headerRow + 1; $r <= $maxRow; $r++) {
        $coordId = Coordinate::stringFromColumnIndex($colCredito) . $r;
        $rawId = $sheet->getCell($coordId)->getCalculatedValue();
        $idCredito = parseIdCredito($rawId);
        if ($idCredito === null) {
            continue;
        }
        $n++;
        if ($maxCreditos > 0 && $n >= $maxCreditos) {
            break;
        }
    }

    return $n;
}

/**
 * Monto de la celda «SALDO APLICABLE A GC» (número o texto con $ / comas).
 */
function parseMontoSaldoAplicableCelda(mixed $raw): ?float
{
    if ($raw === null || $raw === '') {
        return null;
    }
    if (is_numeric($raw)) {
        $v = round((float) $raw, 2);

        return $v >= 0 ? $v : null;
    }
    $s = trim((string) $raw);
    $s = str_replace(['$', ' ', "\xc2\xa0"], '', $s);
    if ($s === '') {
        return null;
    }
    $s = str_replace(',', '', $s);
    if ($s === '' || !is_numeric($s)) {
        return null;
    }
    $v = round((float) $s, 2);

    return $v >= 0 ? $v : null;
}

/**
 * @param ?float $saldoAplicableGc valor de la columna Excel «SALDO APLICABLE A GC» en la misma fila; null si no hay columna o celda vacía
 * @return array{0: float, 1: float} exceso sobre tope, sobrante extemporáneo
 */
function calcularMetricasGc(array $estadoCuenta, float $topePorNota, ?float $saldoAplicableGc = null): array
{
    $notas = $estadoCuenta['datosNotasCargos'] ?? [];
    if (!is_array($notas)) {
        $notas = [];
    }

    $sumaGc = 0.0;
    $nNotas = 0;
    foreach ($notas as $n) {
        if (!is_array($n)) {
            continue;
        }
        $c = (string) ($n['concepto'] ?? '');
        if (mb_strtoupper(trim($c)) !== CONCEPTO_GC) {
            continue;
        }
        $sumaGc += (float) ($n['monto'] ?? 0);
        $nNotas++;
    }

    $exceso = $sumaGc - ($topePorNota * $nNotas);
    if ($exceso < 0) {
        $exceso = 0.0;
    }
    $exceso = round($exceso, 2);

    if ($saldoAplicableGc !== null && $saldoAplicableGc <= $topePorNota) {
        $exceso = 0.0;
    }

    $sobrante = calcularSobrantePagoExtMenosVencExt($estadoCuenta);

    return [$exceso, $sobrante];
}

/**
 * Sobrante = (extemporáneos pagados en el último día con abonos a ext.) − saldoVencidoExtemporaneos.
 * Si no hay vencido extemporáneo (0), no aplica la resta: devuelve 0 (evita inflar con suma histórica).
 */
function calcularSobrantePagoExtMenosVencExt(array $estadoCuenta): float
{
    $ds = $estadoCuenta['datosSaldos'] ?? [];
    if (!is_array($ds)) {
        $ds = [];
    }
    $vencExt = round((float) ($ds['saldoVencidoExtemporaneos'] ?? 0), 2);
    if ($vencExt <= 0) {
        return 0.0;
    }

    $pagos = $estadoCuenta['datosPagos'] ?? [];
    if (!is_array($pagos)) {
        $pagos = [];
    }

    $porFecha = [];
    foreach ($pagos as $p) {
        if (!is_array($p)) {
            continue;
        }
        $ext = (float) ($p['extemporaneos'] ?? 0);
        if ($ext <= 0) {
            continue;
        }
        $fd = trim((string) ($p['fechaValor'] ?? $p['fechaDeposito'] ?? ''));
        if ($fd === '') {
            continue;
        }
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $fd, $m)) {
            $norm = $m[1];
        } else {
            $ts = strtotime($fd);
            $norm = $ts !== false ? date('Y-m-d', $ts) : '';
        }
        if ($norm === '') {
            continue;
        }
        if (!isset($porFecha[$norm])) {
            $porFecha[$norm] = 0.0;
        }
        $porFecha[$norm] += $ext;
    }

    if ($porFecha === []) {
        return round(0.0 - $vencExt, 2);
    }

    $maxTs = null;
    $maxFecha = null;
    foreach (array_keys($porFecha) as $fe) {
        $ts = strtotime($fe . ' 12:00:00');
        if ($ts === false) {
            continue;
        }
        if ($maxTs === null || $ts > $maxTs) {
            $maxTs = $ts;
            $maxFecha = $fe;
        }
    }
    if ($maxFecha === null) {
        return round(0.0 - $vencExt, 2);
    }

    $sumaUltimoDia = round($porFecha[$maxFecha], 2);

    return round($sumaUltimoDia - $vencExt, 2);
}

function aplicarFormatoMonedaRelleno(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $coord, string $rgbFondo): void
{
    $sheet->getStyle($coord)->getNumberFormat()->setFormatCode('$#,##0.00');
    $sheet->getStyle($coord)->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rgbFondo]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
    ]);
}

function aplicarFormatoTextoRelleno(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $coord, string $rgbFondo): void
{
    $sheet->getStyle($coord)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
    $sheet->getStyle($coord)->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rgbFondo]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
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

/**
 * Hitos 25 / 50 / 75 % según avance real (floor).
 *
 * @param array<int, bool> $milestoneSent
 */
function maybePostProgressMilestones(string $webhookUrl, array &$milestoneSent, int $processed, int $total): void
{
    if ($total <= 0) {
        return;
    }
    $pctFloor = (int) floor((100 * $processed) / $total);
    $lastNew = null;
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
        $msg = "*Progreso (Excel GC):* al menos *{$lastNew}%* del lote (*{$processed}/{$total}* id(s) consultados en S2).";
        postGoogleChat($webhookUrl, $msg);
    }
}
