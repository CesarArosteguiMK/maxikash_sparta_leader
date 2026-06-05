<?php

declare(strict_types=1);

use Core\Database;
use Models\Vacaciones;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__) . '/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/core/Database.php';
require dirname(__DIR__) . '/core/Model.php';
require dirname(__DIR__) . '/models/Vacaciones.php';

const VAC_MESES = [
    'Enero' => 1,
    'Febrero' => 2,
    'Marzo' => 3,
    'Abril' => 4,
    'Mayo' => 5,
    'Junio' => 6,
    'Julio' => 7,
    'Agosto' => 8,
    'Septiembre' => 9,
    'Octubre' => 10,
    'Noviembre' => 11,
    'Diciembre' => 12,
];

function uso(): void
{
    fwrite(STDERR, "Uso: php backend/tools/importar_vacaciones_excel.php <archivo.xlsx> [--year=2025] [--dry-run]\n");
}

function normalizarTexto(string $s): string
{
    return Vacaciones::normalizarNombre($s);
}

function parseFechaExcel(mixed $v): ?string
{
    if ($v instanceof DateTimeInterface) {
        return $v->format('Y-m-d');
    }
    if (is_int($v) || is_float($v)) {
        if ((float) $v > 20000 && (float) $v < 80000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $v)->format('Y-m-d');
            } catch (Throwable $e) {
                return null;
            }
        }
        return null;
    }

    $s = trim((string) $v);
    if ($s === '') {
        return null;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $s)) {
        return substr($s, 0, 10);
    }
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $s, $m)) {
        return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
    }
    $t = strtotime($s);

    return $t !== false ? date('Y-m-d', $t) : null;
}

function numeroCelda(mixed $v): float
{
    if (is_int($v) || is_float($v)) {
        return (float) $v;
    }
    $s = trim((string) $v);
    if ($s === '') {
        return 0.0;
    }
    $s = str_replace([',', ' '], ['', ''], $s);

    return is_numeric($s) ? (float) $s : 0.0;
}

function celda(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, int $col, int $row, bool $formula = false): mixed
{
    $coord = Coordinate::stringFromColumnIndex($col) . $row;
    $cell = $ws->getCell($coord);
    if (!$formula) {
        return $cell->getValue();
    }
    try {
        return $cell->getCalculatedValue();
    } catch (Throwable $e) {
        return $cell->getOldCalculatedValue() ?? $cell->getValue();
    }
}

$path = '';
$dryRun = false;
$anio = 2025;
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if ($arg === '--dry-run') {
        $dryRun = true;
    } elseif (preg_match('/^--year=(\d{4})$/', $arg, $m)) {
        $anio = (int) $m[1];
    } elseif ($path === '') {
        $path = $arg;
    }
}

if ($path === '' || !is_readable($path)) {
    uso();
    exit(1);
}

$fuente = 'excel_vacaciones_' . $anio;
$db = new Database();
Vacaciones::asegurarTablas();

$personasPorNombre = [];
foreach ($db->queryAll("
    SELECT
        p.id,
        p.fecha_ingreso,
        CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo
    FROM __SPARTA_SECRET_REDACTED__.persona p
") as $p) {
    $key = normalizarTexto((string) $p['nombre_completo']);
    if ($key === '') {
        continue;
    }
    $personasPorNombre[$key][] = [
        'id' => (int) $p['id'],
        'fecha_ingreso' => (string) $p['fecha_ingreso'],
        'nombre_completo' => (string) $p['nombre_completo'],
    ];
}

$spreadsheet = IOFactory::load($path);
$historial = [];
$stats = [
    'resumen_filas' => 0,
    'sin_fecha_ingreso' => 0,
    'sin_nombre' => 0,
    'sin_match' => 0,
    'duplicados_nombre' => 0,
    'raw_upsert' => 0,
    'saldos_upsert' => 0,
    'solicitudes_historicas' => 0,
    'dias_historicos' => 0,
];

$resolverPersona = static function (string $nombre) use ($personasPorNombre, &$stats): ?int {
    $key = normalizarTexto($nombre);
    if ($key === '') {
        $stats['sin_nombre']++;
        return null;
    }
    $matches = $personasPorNombre[$key] ?? [];
    if (count($matches) === 1) {
        return (int) $matches[0]['id'];
    }
    if (count($matches) > 1) {
        $stats['duplicados_nombre']++;
        return null;
    }
    $stats['sin_match']++;
    return null;
};

$resumen = $spreadsheet->getSheetByName('Resumen');
if ($resumen) {
    $maxRow = $resumen->getHighestDataRow();
    for ($row = 4; $row <= $maxRow; $row++) {
        $fechaIngreso = parseFechaExcel(celda($resumen, 2, $row));
        $nombre = trim((string) celda($resumen, 3, $row));
        if ($nombre === '' && $fechaIngreso === null) {
            continue;
        }
        $stats['resumen_filas']++;
        if ($fechaIngreso === null) {
            $stats['sin_fecha_ingreso']++;
            continue;
        }
        $idPersona = $resolverPersona($nombre);
        if (!$dryRun) {
            Vacaciones::upsertResumenExcelRaw(
                $db,
                $fuente,
                $row,
                $idPersona,
                $nombre,
                normalizarTexto($nombre),
                $fechaIngreso,
                $anio,
                numeroCelda(celda($resumen, 4, $row, true)),
                numeroCelda(celda($resumen, 5, $row, true)),
                numeroCelda(celda($resumen, 6, $row, true))
            );
        }
        $stats['raw_upsert']++;
        if (!$idPersona) {
            continue;
        }
        if (!$dryRun) {
            Vacaciones::upsertSaldoExcel(
                $db,
                $fuente,
                $idPersona,
                $nombre,
                $fechaIngreso,
                $anio,
                numeroCelda(celda($resumen, 4, $row, true)),
                numeroCelda(celda($resumen, 5, $row, true)),
                numeroCelda(celda($resumen, 6, $row, true)),
                $row
            );
        }
        $stats['saldos_upsert']++;
    }
}

foreach (VAC_MESES as $sheetName => $mes) {
    $ws = $spreadsheet->getSheetByName($sheetName);
    if (!$ws) {
        continue;
    }
    $maxRow = $ws->getHighestDataRow();
    $maxCol = Coordinate::columnIndexFromString($ws->getHighestDataColumn());
    $colsDia = [];
    for ($col = 3; $col <= $maxCol; $col++) {
        $dia = (int) celda($ws, $col, 2);
        if ($dia >= 1 && $dia <= 31 && checkdate($mes, $dia, $anio)) {
            $colsDia[$col] = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
        }
    }

    for ($row = 4; $row <= $maxRow; $row++) {
        $fechaIngreso = parseFechaExcel(celda($ws, 1, $row));
        $nombre = trim((string) celda($ws, 2, $row));
        if ($nombre === '' && $fechaIngreso === null) {
            continue;
        }
        if ($fechaIngreso === null) {
            continue;
        }
        $idPersona = $resolverPersona($nombre);
        if (!$idPersona) {
            continue;
        }

        foreach ($colsDia as $col => $fecha) {
            $valor = strtoupper(trim((string) celda($ws, $col, $row)));
            if ($valor === 'V') {
                $historial[$idPersona][$fecha] = $fecha;
            }
        }
    }
}

foreach ($historial as $idPersona => $fechas) {
    sort($fechas);
    if (!$dryRun) {
        $db->beginTransaction();
        try {
            $dias = Vacaciones::upsertHistoricoExcel($db, $fuente, (int) $idPersona, array_values($fechas), $anio);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollback();
            throw $e;
        }
    } else {
        $dias = count($fechas);
    }
    $stats['solicitudes_historicas']++;
    $stats['dias_historicos'] += $dias;
}

$spreadsheet->disconnectWorksheets();
echo json_encode([
    'success' => true,
    'dry_run' => $dryRun,
    'fuente' => $fuente,
    'stats' => $stats,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
