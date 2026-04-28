<?php
declare(strict_types=1);

date_default_timezone_set('America/Mexico_City');
$baseDir = __DIR__ . '/backend/services/gastos-cobranza-agent/tools/ec-webhook-worker';

function loadEnvFileSimple(string $path): void {
    if (!is_file($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $p = strpos($line, '=');
        if ($p === false) {
            continue;
        }
        $k = trim(substr($line, 0, $p));
        $v = trim(substr($line, $p + 1));
        $v = trim($v, " \t\"'");
        if ($k === '') {
            continue;
        }
        putenv($k . '=' . $v);
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }
}

loadEnvFileSimple($baseDir . '/config.local.env');

require_once $baseDir . '/../ec-shared/ec___SPARTA_SECRET_REDACTED___pipeline.php';
ecWorkerBootstrapBackend($baseDir);

$idsFile = $argv[1] ?? '';
$fechaCorte = $argv[2] ?? date('Y-m-d');
$outFile = $argv[3] ?? (__DIR__ . '/%TEMP%/gdc_no_declarados_' . date('Ymd_His') . '.csv');

if ($idsFile === '' || !is_file($idsFile)) {
    fwrite(STDERR, "Uso: php tmp_detectar_gdc_no_declarados.php <ids.txt> [YYYY-mm-dd] [salida.csv]\n");
    exit(1);
}

$token = getenv('TOKEN') ?: '';
$endpoint = getenv('ENDPOINT') ?: 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
if ($token === '') {
    fwrite(STDERR, "Falta TOKEN en entorno/config.local.env del worker.\n");
    exit(1);
}

$ids = [];
foreach (file($idsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (preg_match('/^\d+$/', $line)) $ids[] = (int)$line;
}
$ids = array_values(array_unique($ids));
if (!$ids) {
    fwrite(STDERR, "No hay IDs válidos en el archivo.\n");
    exit(1);
}

$db = new \Core\DatabaseSegundometro();
$fh = fopen($outFile, 'w');
fputcsv($fh, ['id_credito','fecha_corte','s2_ok','tiene_extemporaneos','monto_extemporaneos','tiene_nota_gc','monto_notas_gc','gc_rows_total_bd','gc_pendientes_modelo']);

$total = count($ids);
$hits = 0;
foreach ($ids as $i => $idCredito) {
    $r = consultarEstadoCuentaS2($endpoint, $token, $idCredito, $fechaCorte);
    if (empty($r['success'])) {
        echo '[' . ($i+1) . "/$total] $idCredito -> S2 ERROR\n";
        fputcsv($fh, [$idCredito,$fechaCorte,0,0,0,0,0,'ERR','ERR']);
        continue;
    }

    $ec = $r['estadoCuenta'] ?? [];
    $pagos = is_array($ec['datosPagos'] ?? null) ? $ec['datosPagos'] : [];
    $notas = is_array($ec['datosNotasCargos'] ?? null) ? $ec['datosNotasCargos'] : [];

    $montoExt = 0.0;
    foreach ($pagos as $p) {
        $montoExt += (float)($p['extemporaneos'] ?? 0);
    }
    $montoExt = round($montoExt, 2);
    $tieneExt = $montoExt > 0.009;

    $montoNotasGc = 0.0;
    foreach ($notas as $n) {
        $c = mb_strtoupper(trim((string)($n['concepto'] ?? '')));
        if (str_contains($c, 'GASTO') && str_contains($c, 'COBRANZA') && !str_contains($c, 'EXTEMPORANEO') && !str_contains($c, 'EXTEMPORÁNEO')) {
            $montoNotasGc += (float)($n['monto'] ?? 0);
        }
    }
    $montoNotasGc = round($montoNotasGc, 2);
    $tieneNotaGc = $montoNotasGc > 0.009;

    $row = $db->queryOne("SELECT COUNT(*) AS c FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza WHERE Id_credito = :id", ['id' => $idCredito]);
    $gcRows = (int)($row['c'] ?? 0);

    $resPend = \Models\EstadoCuenta::getGastosCobranza($idCredito);
    $gcPend = (!empty($resPend['success']) && is_array($resPend['datos'] ?? null)) ? count($resPend['datos']) : -1;

    $esSospechoso = ($gcRows === 0) && ($tieneExt || $tieneNotaGc);
    if ($esSospechoso) {
        $hits++;
        echo '[' . ($i+1) . "/$total] $idCredito -> SOSPECHOSO (sin BD, con señal S2)\n";
        fputcsv($fh, [$idCredito,$fechaCorte,1,$tieneExt?1:0,$montoExt,$tieneNotaGc?1:0,$montoNotasGc,$gcRows,$gcPend]);
    } else {
        echo '[' . ($i+1) . "/$total] $idCredito -> ok\n";
    }
}

fclose($fh);
echo "Terminado. Sospechosos: $hits. CSV: $outFile\n";
