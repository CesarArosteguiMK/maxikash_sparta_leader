<?php
/**
 * Diagnostico/aplicacion: calcula la parcialidad correcta de gastos_cobranza como numero de cuota.
 *
 * Por defecto no actualiza BD. Con --apply actualiza solo parcialidad, con backup previo.
 *
 * Uso:
 *   php gdc_parcialidad_worker.php --id-credito=12345
 *   php gdc_parcialidad_worker.php --id-credito=12345,67890 --fecha-corte=2026-04-27
 *   php gdc_parcialidad_worker.php --file=C:\ruta\creditos.txt --out=C:\ruta\reporte.csv
 *   php gdc_parcialidad_worker.php --limit=20 --verbose
 *   php gdc_parcialidad_worker.php --apply --limit=0
 *   php gdc_parcialidad_worker.php --apply --skip-backup --limit=0
 *   php gdc_parcialidad_worker.php --apply --skip-backup --limit=0 --omitir-primeros=6457
 *   php gdc_parcialidad_worker.php --apply --skip-backup --limit=0 --desde-id-credito=1785381
 */

declare(strict_types=1);

use Core\DatabaseSegundometro;

const CONCEPTO_GDC_PAR = 'NOTA DE DE CARGO GASTOS DE COBRANZA';
const TOLERANCIA_MONTO = 1.0;

date_default_timezone_set('America/Mexico_City');

$baseDir = dirname(__FILE__);
loadEnvFile($baseDir . DIRECTORY_SEPARATOR . 'ec-webhook-worker' . DIRECTORY_SEPARATOR . 'config.local.env');
require_once $baseDir . DIRECTORY_SEPARATOR . 'ec-shared' . DIRECTORY_SEPARATOR . 'ec_estado_cuenta_pipeline.php';
ecWorkerBootstrapBackend($baseDir);

$opts = getopt('', [
    'id-credito:',
    'file:',
    'fecha-corte:',
    'limit:',
    'out:',
    'delay-ms:',
    'omitir-primeros:',
    'desde-id-credito:',
    'verbose',
    'apply',
    'skip-backup',
    'help',
]);

if ($opts === false || isset($opts['help'])) {
    imprimirUso();
    exit(isset($opts['help']) ? 0 : 1);
}

$fechaCorte = isset($opts['fecha-corte']) ? trim((string) $opts['fecha-corte'], " \t\"'") : '';
if ($fechaCorte === '') {
    $fechaCorte = trim((string) (getenv('FECHA_CORTE') ?: ''), " \t\"'");
}
if ($fechaCorte === '') {
    $fechaCorte = date('Y-m-d');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCorte)) {
    fwrite(STDERR, "Fecha de corte invalida (YYYY-MM-DD): {$fechaCorte}\n");
    exit(1);
}

$token = getenv('TOKEN') ?: '';
$endpoint = getenv('ENDPOINT') ?: 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
if ($token === '') {
    fwrite(STDERR, "Falta TOKEN en config.local.env o variables de entorno.\n");
    exit(1);
}

$apply = array_key_exists('apply', $opts);
$skipBackup = array_key_exists('skip-backup', $opts);
$limit = isset($opts['limit'])
    ? max(0, (int) $opts['limit'])
    : ($apply ? 0 : 50);
$delayMs = isset($opts['delay-ms']) ? max(0, (int) $opts['delay-ms']) : 300;
$omitirPrimeros = isset($opts['omitir-primeros']) ? max(0, (int) $opts['omitir-primeros']) : 0;
$desdeIdCredito = isset($opts['desde-id-credito']) ? max(0, (int) $opts['desde-id-credito']) : 0;
$verbose = array_key_exists('verbose', $opts);

if ($desdeIdCredito > 0 && $omitirPrimeros > 0) {
    fwrite(STDERR, "Use solo uno: --desde-id-credito o --omitir-primeros (el indice depende del total actual en BD).\n");
    exit(1);
}

$ids = [];
if (isset($opts['id-credito'])) {
    $ids = parseIdsOption($opts['id-credito']);
}
if (isset($opts['file'])) {
    if ($ids !== []) {
        fwrite(STDERR, "Use solo uno: --id-credito o --file.\n");
        exit(1);
    }
    $ids = loadCreditIds((string) $opts['file']);
}

try {
    $db = new DatabaseSegundometro();
    if ($ids === []) {
        $ids = obtenerIdsCreditoConGdc($db, $limit);
        if ($ids === []) {
            fwrite(STDERR, "No se encontraron creditos con gastos_cobranza.\n");
            exit(1);
        }
        if ($limit > 0) {
            echo "[gdc-parcialidad] Sin --id-credito/--file: se toman {$limit} credito(s) como muestra segura.\n";
        } else {
            echo "[gdc-parcialidad] Sin --id-credito/--file y --limit=0: se procesaran todos los creditos con gastos_cobranza.\n";
        }
    } elseif ($limit > 0) {
        $ids = array_slice($ids, 0, $limit);
    }

    $totalOriginal = count($ids);
    if ($desdeIdCredito > 0) {
        $nAntes = $totalOriginal;
        $ids = array_values(array_filter($ids, static fn($id): bool => (int) $id >= $desdeIdCredito));
        $totalOriginal = count($ids);
        if ($totalOriginal === 0) {
            fwrite(STDERR, "--desde-id-credito ({$desdeIdCredito}) no deja ningun credito en la lista.\n");
            exit(1);
        }
        echo "[gdc-parcialidad] Reanudacion por Id_credito>={$desdeIdCredito}: {$totalOriginal} credito(s) (lista completa tenia {$nAntes}).\n";
    } elseif ($omitirPrimeros > 0) {
        if ($omitirPrimeros >= $totalOriginal) {
            fwrite(STDERR, "--omitir-primeros ({$omitirPrimeros}) es mayor o igual al total de creditos ({$totalOriginal}).\n");
            exit(1);
        }
        $ids = array_values(array_slice($ids, $omitirPrimeros));
        echo "[gdc-parcialidad] Reanudacion: omitidos {$omitirPrimeros} credito(s); restantes " . count($ids) . ".\n";
    }

    $backupTable = null;
    if ($apply) {
        if ($skipBackup) {
            echo "[gdc-parcialidad] APPLY activo. Se omite backup por --skip-backup.\n";
        } else {
            $backupTable = crearBackupParcialidad($db);
            echo "[gdc-parcialidad] APPLY activo. Backup creado: {$backupTable}\n";
        }
    }

    $outPath = isset($opts['out']) && trim((string) $opts['out']) !== ''
        ? trim((string) $opts['out'], " \t\"'")
        : $baseDir . DIRECTORY_SEPARATOR . 'gdc_parcialidad_diagnostico_' . date('Ymd_His') . '.csv';

    $fp = fopen($outPath, 'wb');
    if ($fp === false) {
        fwrite(STDERR, "No se pudo crear CSV: {$outPath}\n");
        exit(1);
    }

    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, [
        'id_gastos_cobranza',
        'Id_credito',
        'parcialidad_actual',
        'parcialidad_calculada',
        'estatus',
        'detalle',
        'semana',
        'periodo_inicio',
        'periodo_fin',
        'monto_local',
        'nota_fecha_s2',
        'nota_monto_s2',
        'nota_indice_s2',
        'cuota_id_cargo',
        'cuota_fecha_vencimiento',
        'cuota_concepto',
        'metodo_calculo',
    ], ';');

    $totalCreditos = count($ids);
    $resumen = [
        'ok' => 0,
        'sin_cambio' => 0,
        'sin_nota_s2' => 0,
        'sin_cuota_generadora' => 0,
        'ambigua' => 0,
        'error_s2' => 0,
        'sin_gastos' => 0,
    ];
    $filasTotal = 0;
    $filasActualizadas = 0;

    foreach ($ids as $idx => $idCredito) {
        $n = $idx + 1;
        echo "[{$n}/{$totalCreditos}] Credito {$idCredito} ... ";

        $gastos = obtenerGastosPorCredito($db, $idCredito);
        if ($gastos === []) {
            $resumen['sin_gastos']++;
            echo "sin gastos\n";
            continue;
        }

        $s2 = consultarEstadoCuentaS2($endpoint, $token, $idCredito, $fechaCorte);
        if (empty($s2['success'])) {
            $resumen['error_s2']++;
            $detalle = (string) ($s2['error'] ?? 'Error S2');
            echo "ERROR S2: {$detalle}\n";
            foreach ($gastos as $gasto) {
                escribirFilaDiagnostico($fp, $gasto, [
                    'estatus' => 'error_s2',
                    'detalle' => $detalle,
                ]);
                $filasTotal++;
            }
            pausaMs($delayMs);
            continue;
        }

        $estadoCuenta = $s2['estadoCuenta'] ?? [];
        $contexto = prepararContextoS2(is_array($estadoCuenta) ? $estadoCuenta : []);
        $usadas = [];
        $cambiosCredito = 0;

        foreach ($gastos as $gasto) {
            $diag = diagnosticarGasto($gasto, $contexto, $usadas);
            $estatus = $diag['estatus'];
            if (!isset($resumen[$estatus])) {
                $resumen[$estatus] = 0;
            }
            $resumen[$estatus]++;
            if ($estatus === 'ok') {
                $cambiosCredito++;
            }
            if ($apply && diagnosticoAplicable($gasto, $diag)) {
                $filasActualizadas += actualizarParcialidadGasto(
                    $db,
                    (int) $gasto['id_gastos_cobranza'],
                    (int) $diag['parcialidad_calculada']
                );
            }
            escribirFilaDiagnostico($fp, $gasto, $diag);
            $filasTotal++;

            if ($verbose) {
                $idGasto = (int) ($gasto['id_gastos_cobranza'] ?? 0);
                $actual = (int) ($gasto['parcialidad'] ?? 0);
                $calc = $diag['parcialidad_calculada'] ?? '';
                echo "\n    gasto {$idGasto}: actual={$actual}, calc={$calc}, {$estatus}";
            }
        }

        echo $verbose ? "\n" : '';
        echo "filas=" . count($gastos) . ", cambios={$cambiosCredito}\n";
        pausaMs($delayMs);
    }

    fclose($fp);

    echo "[gdc-parcialidad] CSV: {$outPath}\n";
    echo "[gdc-parcialidad] Filas diagnosticadas: {$filasTotal}\n";
    echo "[gdc-parcialidad] Filas actualizadas: {$filasActualizadas}\n";
    if ($backupTable !== null) {
        echo "[gdc-parcialidad] Backup: {$backupTable}\n";
    }
    echo "[gdc-parcialidad] Resumen: " . json_encode($resumen, JSON_UNESCAPED_UNICODE) . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "[FATAL] " . $e->getMessage() . "\n");
    exit(1);
}

function imprimirUso(): void
{
    fwrite(STDERR, "Uso: php gdc_parcialidad_worker.php [--apply] [--skip-backup] [--omitir-primeros=N|--desde-id-credito=ID] [--id-credito=ID[,ID]] [--file=creditos.txt] [--fecha-corte=YYYY-MM-DD] [--limit=N] [--out=reporte.csv] [--delay-ms=N] [--verbose]\n");
    fwrite(STDERR, "  --desde-id-credito=ID  Reanuda procesando solo Id_credito>=ID (recomendado si cambia el total de creditos en BD).\n");
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
        // El agente puede inyectar secretos desde SPARTA_ENV_FILE. Un valor
        // vacío de la plantilla local no debe borrar ese valor heredado.
        if ($k === '' || $v === '') {
            continue;
        }
        $existente = getenv($k);
        if ($existente !== false && trim((string) $existente) !== '') {
            continue;
        }
        putenv($k . '=' . $v);
    }
}

/**
 * @return int[]
 */
function parseIdsOption($raw): array
{
    $parts = is_array($raw) ? $raw : preg_split('/[,\s]+/', (string) $raw);
    $ids = [];
    foreach ((array) $parts as $part) {
        $id = (int) trim((string) $part);
        if ($id > 0) {
            $ids[$id] = true;
        }
    }

    return array_keys($ids);
}

/**
 * @return int[]
 */
function loadCreditIds(string $path): array
{
    $path = trim($path, " \t\"'");
    if (!is_readable($path)) {
        fwrite(STDERR, "No se puede leer archivo: {$path}\n");
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return [];
    }
    $ids = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        foreach (preg_split('/[,\s]+/', $line) as $part) {
            $id = (int) $part;
            if ($id > 0) {
                $ids[$id] = true;
            }
        }
    }

    return array_keys($ids);
}

/**
 * @return int[]
 */
function obtenerIdsCreditoConGdc(DatabaseSegundometro $db, int $limit): array
{
    $limitSql = $limit > 0 ? " LIMIT {$limit}" : '';
    $rows = $db->queryAll(
        "SELECT DISTINCT Id_credito
         FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
         WHERE Id_credito IS NOT NULL
         ORDER BY Id_credito ASC{$limitSql}"
    );

    return array_values(array_filter(array_map(static fn($r) => (int) ($r['Id_credito'] ?? 0), $rows)));
}

/**
 * @return list<array<string,mixed>>
 */
function obtenerGastosPorCredito(DatabaseSegundometro $db, int $idCredito): array
{
    return $db->queryAll(
        "SELECT
            id_gastos_cobranza,
            Id_credito,
            SEMANA,
            periodo_inicio,
            periodo_fin,
            parcialidad,
            monto_valor,
            Fecha_primer_vencimiento,
            cuota,
            COALESCE(estatus_pago, 0) AS estatus_pago,
            COALESCE(condonado, 0) AS condonado,
            fecha_pago,
            created_at
         FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
         WHERE Id_credito = :id
         ORDER BY periodo_inicio ASC, id_gastos_cobranza ASC",
        ['id' => $idCredito]
    );
}

/**
 * @return array{notas:list<array<string,mixed>>, cuotas:list<array<string,mixed>>}
 */
function prepararContextoS2(array $estadoCuenta): array
{
    $notas = [];
    foreach (($estadoCuenta['datosNotasCargos'] ?? []) as $idx => $nota) {
        if (!is_array($nota)) {
            continue;
        }
        $concepto = mb_strtoupper(trim((string) ($nota['concepto'] ?? '')));
        if ($concepto !== CONCEPTO_GDC_PAR) {
            continue;
        }
        $fecha = normalizarFecha($nota['fechaMovimiento'] ?? ($nota['fechaVencimiento'] ?? null));
        $monto = safeFloat($nota['monto'] ?? 0);
        if ($fecha === null || $monto <= 0) {
            continue;
        }
        $notas[] = [
            'idx' => $idx,
            'fecha' => $fecha,
            'monto' => round($monto, 2),
            'idCargo' => safeInt($nota['idCargo'] ?? null),
            'raw' => $nota,
        ];
    }
    usort($notas, static fn($a, $b) => [$a['fecha'], $a['idx']] <=> [$b['fecha'], $b['idx']]);

    $cuotas = [];
    foreach (($estadoCuenta['datosCargos'] ?? []) as $cargo) {
        if (!is_array($cargo)) {
            continue;
        }
        $concepto = trim((string) ($cargo['concepto'] ?? ''));
        $upper = mb_strtoupper($concepto);
        if (strpos($upper, 'CUOTA SEMANAL') === false) {
            continue;
        }
        $fecha = normalizarFecha($cargo['fechaVencimiento'] ?? ($cargo['fechaVenc'] ?? null));
        $idCargo = safeInt($cargo['idCargo'] ?? null);
        $numeroCuota = extraerNumeroCuota($concepto);
        if ($fecha === null || $idCargo <= 0 || $numeroCuota === null) {
            continue;
        }
        $cuotas[] = [
            'idCargo' => $idCargo,
            'fecha' => $fecha,
            'numero' => $numeroCuota,
            'concepto' => $concepto,
            'monto' => round(safeFloat($cargo['monto'] ?? 0), 2),
        ];
    }
    usort($cuotas, static fn($a, $b) => $a['idCargo'] <=> $b['idCargo']);

    return ['notas' => $notas, 'cuotas' => $cuotas];
}

/**
 * @param array<int,true> $notasUsadas
 * @return array<string,mixed>
 */
function diagnosticarGasto(array $gasto, array $contexto, array &$notasUsadas): array
{
    $notaMatch = emparejarNotaGdc($gasto, $contexto['notas'], $notasUsadas);
    $nota = $notaMatch['nota'] ?? null;
    if ($nota !== null) {
        $notasUsadas[(int) $nota['idx']] = true;
    }

    $cuotaInfo = calcularCuotaGeneradora($gasto, $nota, $contexto['cuotas']);
    $calc = $cuotaInfo['parcialidad_calculada'] ?? null;

    $estatus = 'ok';
    $detalle = '';
    if (($notaMatch['estatus'] ?? '') === 'ambigua') {
        $estatus = 'ambigua';
        $detalle = $notaMatch['detalle'] ?? 'Mas de una nota S2 candidata';
    } elseif ($nota === null) {
        $estatus = 'sin_nota_s2';
        $detalle = 'No se encontro nota GDC S2 compatible';
    } elseif ($calc === null) {
        $estatus = 'sin_cuota_generadora';
        $detalle = 'No se encontro cuota semanal generadora';
    } elseif ((int) ($gasto['parcialidad'] ?? 0) === (int) $calc) {
        $estatus = 'sin_cambio';
        $detalle = 'La parcialidad actual ya coincide';
    } else {
        $detalle = 'Requiere ajuste de parcialidad';
    }

    return [
        'estatus' => $estatus,
        'detalle' => $detalle,
        'parcialidad_calculada' => $calc,
        'nota' => $nota,
        'cuota' => $cuotaInfo['cuota'] ?? null,
        'metodo_calculo' => $cuotaInfo['metodo'] ?? '',
        'nota_match_detalle' => $notaMatch['detalle'] ?? '',
    ];
}

/**
 * @param list<array<string,mixed>> $notas
 * @param array<int,true> $notasUsadas
 * @return array{estatus:string, nota?:array<string,mixed>, detalle:string}
 */
function emparejarNotaGdc(array $gasto, array $notas, array $notasUsadas): array
{
    $montoLocal = round(safeFloat($gasto['monto_valor'] ?? 0), 2);
    $inicio = normalizarFecha($gasto['periodo_inicio'] ?? null);
    $fin = normalizarFecha($gasto['periodo_fin'] ?? null);

    $candidatas = [];
    foreach ($notas as $nota) {
        $idx = (int) $nota['idx'];
        if (isset($notasUsadas[$idx])) {
            continue;
        }
        $montoDiff = abs(round((float) $nota['monto'] - $montoLocal, 2));
        if ($montoDiff > 10.0) {
            continue;
        }

        $fechaNota = (string) $nota['fecha'];
        $scoreFecha = scoreFechaNota($fechaNota, $inicio, $fin);
        if ($scoreFecha > 60.0) {
            continue;
        }
        $scoreMonto = $montoDiff <= TOLERANCIA_MONTO ? 0 : (2 + $montoDiff);
        $candidatas[] = [
            'nota' => $nota,
            'score' => $scoreFecha + $scoreMonto,
            'detalle' => "diff_monto={$montoDiff};score_fecha={$scoreFecha}",
        ];
    }

    if ($candidatas === []) {
        return ['estatus' => 'sin_nota_s2', 'detalle' => 'Sin candidatas por fecha/monto'];
    }

    usort($candidatas, static fn($a, $b) => $a['score'] <=> $b['score']);
    $best = $candidatas[0];
    $empates = array_filter($candidatas, static fn($c) => abs((float) $c['score'] - (float) $best['score']) < 0.0001);
    if (count($empates) > 1) {
        return [
            'estatus' => 'ambigua',
            'nota' => $best['nota'],
            'detalle' => 'Empate entre ' . count($empates) . ' notas candidatas; ' . $best['detalle'],
        ];
    }

    return ['estatus' => 'ok', 'nota' => $best['nota'], 'detalle' => $best['detalle']];
}

function scoreFechaNota(string $fechaNota, ?string $inicio, ?string $fin): float
{
    if ($inicio !== null && $fin !== null && $fechaNota >= $inicio && $fechaNota <= $fin) {
        return 0.0;
    }
    if ($inicio === null) {
        return 20.0;
    }
    $diff = abs(diasEntre($inicio, $fechaNota));
    if ($diff <= 10) {
        return 5.0 + $diff;
    }

    return 50.0 + $diff;
}

/**
 * @param ?array<string,mixed> $nota
 * @param list<array<string,mixed>> $cuotas
 * @return array{parcialidad_calculada:int|null, cuota:?array<string,mixed>, metodo:string}
 */
function calcularCuotaGeneradora(array $gasto, ?array $nota, array $cuotas): array
{
    if ($cuotas === []) {
        return ['parcialidad_calculada' => null, 'cuota' => null, 'metodo' => 'sin_cuotas_s2'];
    }

    $inicio = normalizarFecha($gasto['periodo_inicio'] ?? null);
    $fin = normalizarFecha($gasto['periodo_fin'] ?? null);
    $notaFecha = $nota['fecha'] ?? null;
    $notaIdCargo = isset($nota['idCargo']) ? (int) $nota['idCargo'] : 0;

    if ($notaIdCargo > 0) {
        foreach ($cuotas as $cuota) {
            if ((int) $cuota['idCargo'] === $notaIdCargo) {
                return [
                    'parcialidad_calculada' => (int) $cuota['numero'],
                    'cuota' => $cuota,
                    'metodo' => 'nota_idCargo',
                ];
            }
        }
    }

    if ($inicio !== null && $fin !== null) {
        $enPeriodo = array_values(array_filter($cuotas, static function ($cuota) use ($inicio, $fin) {
            return $cuota['fecha'] >= $inicio && $cuota['fecha'] <= $fin;
        }));
        if (count($enPeriodo) === 1) {
            $cuota = $enPeriodo[0];
            return [
                'parcialidad_calculada' => (int) $cuota['numero'],
                'cuota' => $cuota,
                'metodo' => 'cuota_en_periodo_gdc',
            ];
        }
    }

    if (is_string($notaFecha) && $notaFecha !== '') {
        $vencidas = array_values(array_filter($cuotas, static fn($cuota) => $cuota['fecha'] <= $notaFecha));
        if ($vencidas !== []) {
            $cuota = $vencidas[count($vencidas) - 1];
            return [
                'parcialidad_calculada' => (int) $cuota['numero'],
                'cuota' => $cuota,
                'metodo' => 'ultima_cuota_vencida_antes_nota',
            ];
        }
    }

    $referencia = $inicio ?? $notaFecha;
    if (is_string($referencia) && $referencia !== '') {
        $mejor = null;
        $mejorDiff = null;
        foreach ($cuotas as $cuota) {
            $diff = abs(diasEntre($referencia, (string) $cuota['fecha']));
            if ($mejor === null || $diff < $mejorDiff) {
                $mejor = $cuota;
                $mejorDiff = $diff;
            }
        }
        if ($mejor !== null) {
            return [
                'parcialidad_calculada' => (int) $mejor['numero'],
                'cuota' => $mejor,
                'metodo' => 'cuota_mas_cercana',
            ];
        }
    }

    return ['parcialidad_calculada' => null, 'cuota' => null, 'metodo' => 'sin_match'];
}

function escribirFilaDiagnostico($fp, array $gasto, array $diag): void
{
    $nota = $diag['nota'] ?? null;
    $cuota = $diag['cuota'] ?? null;
    fputcsv($fp, [
        (string) (int) ($gasto['id_gastos_cobranza'] ?? 0),
        (string) (int) ($gasto['Id_credito'] ?? 0),
        (string) (int) ($gasto['parcialidad'] ?? 0),
        (($diag['parcialidad_calculada'] ?? null) !== null) ? (string) (int) $diag['parcialidad_calculada'] : '',
        (string) ($diag['estatus'] ?? ''),
        (string) ($diag['detalle'] ?? ''),
        (string) ($gasto['SEMANA'] ?? ''),
        (string) ($gasto['periodo_inicio'] ?? ''),
        (string) ($gasto['periodo_fin'] ?? ''),
        number_format(safeFloat($gasto['monto_valor'] ?? 0), 2, '.', ''),
        is_array($nota) ? (string) ($nota['fecha'] ?? '') : '',
        is_array($nota) ? number_format((float) ($nota['monto'] ?? 0), 2, '.', '') : '',
        is_array($nota) ? (string) ($nota['idx'] ?? '') : '',
        is_array($cuota) ? (string) ($cuota['idCargo'] ?? '') : '',
        is_array($cuota) ? (string) ($cuota['fecha'] ?? '') : '',
        is_array($cuota) ? (string) ($cuota['concepto'] ?? '') : '',
        (string) ($diag['metodo_calculo'] ?? ''),
    ], ';');
}

function diagnosticoAplicable(array $gasto, array $diag): bool
{
    if (!isset($diag['parcialidad_calculada']) || $diag['parcialidad_calculada'] === null) {
        return false;
    }
    $estatus = (string) ($diag['estatus'] ?? '');
    if (in_array($estatus, ['ambigua', 'error_s2', 'sin_cuota_generadora'], true)) {
        return false;
    }
    $actual = (int) ($gasto['parcialidad'] ?? 0);
    $calculada = (int) $diag['parcialidad_calculada'];

    return $calculada > 0 && $actual !== $calculada;
}

function crearBackupParcialidad(DatabaseSegundometro $db): string
{
    $tabla = 'gastos_cobranza_parcialidad_backup_' . date('Ymd_His');
    $sql = "CREATE TABLE `__SPARTA_SECRET_REDACTED__`.`{$tabla}` AS
            SELECT
                NOW() AS backup_at,
                id_gastos_cobranza,
                Id_credito,
                SEMANA,
                periodo_inicio,
                periodo_fin,
                parcialidad,
                monto_valor,
                estatus_pago,
                condonado,
                fecha_pago,
                created_at
            FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza";
    $db->CRUD($sql);

    return '`__SPARTA_SECRET_REDACTED__`.' . $tabla;
}

function actualizarParcialidadGasto(DatabaseSegundometro $db, int $idGasto, int $parcialidad): int
{
    if ($idGasto <= 0 || $parcialidad <= 0) {
        return 0;
    }

    return (int) $db->CRUD(
        "UPDATE `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
         SET parcialidad = :parcialidad
         WHERE id_gastos_cobranza = :id_gasto
           AND (parcialidad IS NULL OR parcialidad <> :parcialidad_actual)",
        [
            'parcialidad' => $parcialidad,
            'id_gasto' => $idGasto,
            'parcialidad_actual' => $parcialidad,
        ]
    );
}

function extraerNumeroCuota(string $concepto): ?int
{
    if (preg_match('/(\d+)/', $concepto, $m)) {
        $n = (int) $m[1];
        return $n > 0 ? $n : null;
    }

    return null;
}

function normalizarFecha($raw): ?string
{
    if ($raw === null || $raw === '') {
        return null;
    }
    $ts = strtotime((string) $raw);
    if ($ts === false) {
        return null;
    }

    return date('Y-m-d', $ts);
}

function safeFloat($value, float $default = 0.0): float
{
    if ($value === null || $value === '') {
        return $default;
    }
    if (is_numeric($value)) {
        return (float) $value;
    }
    $s = str_replace(['$', ',', ' '], '', (string) $value);

    return is_numeric($s) ? (float) $s : $default;
}

function safeInt($value, int $default = 0): int
{
    if ($value === null || $value === '') {
        return $default;
    }

    return is_numeric($value) ? (int) $value : $default;
}

function diasEntre(string $a, string $b): int
{
    $ta = strtotime($a);
    $tb = strtotime($b);
    if ($ta === false || $tb === false) {
        return 9999;
    }

    return (int) round(($tb - $ta) / 86400);
}

function pausaMs(int $ms): void
{
    if ($ms > 0) {
        usleep($ms * 1000);
    }
}
