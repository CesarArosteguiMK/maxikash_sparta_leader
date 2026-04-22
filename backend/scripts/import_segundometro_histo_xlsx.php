<?php
/**
 * Importa filas de un Excel (.xlsx) a `tbl_segundometro_histo` (__SPARTA_SECRET_REDACTED__).
 *
 * Uso (desde la raíz del proyecto o con ruta absoluta al PHP de XAMPP):
 *   php backend/scripts/import_segundometro_histo_xlsx.php "C:\ruta\SEMANA 16 PARA JON.xlsx"
 *   php backend/scripts/import_segundometro_histo_xlsx.php archivo.xlsx --dry-run
 *   php backend/scripts/import_segundometro_histo_xlsx.php archivo.xlsx --with-pk-id
 *
 * Por defecto NO inserta la columna id_segundometro_histo (deja autoincremento).
 * Omite columnas del Excel que no existan en la lista oficial (p. ej. Cierre_Ajustado2).
 */

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__) . '/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/core/DatabaseSegundometro.php';

/** Columnas en el mismo orden que el INSERT masivo del proyecto (sin id). */
const COLUMNAS_HISTO = [
    'KT', 'Id_credito', 'Id_cliente', 'Nombre_cliente', 'Fecha_nacimiento', 'Genero', 'Estado_civil', 'Celular',
    'Saldo_vencido_inicio', 'Sucursal', 'Status_credito', 'Numero_amortizaciones', 'Monto_otorgado', 'Cuota',
    'Fecha_inicio', 'Fecha_primer_vencimiento', 'Fecha_ultimo_vencimiento', 'Referencia_stp', 'Dias_mora',
    'Dias_mora_max', 'Num_cuotas_pagadas', 'Saldo_total_capital', 'Saldo_para_liquidar_hoy', 'Abonos_total',
    'Abonos_numero', 'Codigo_postal_1', 'Estado_1', 'Bucket_Morosidad', 'Dias_mora_ajustado',
    'Dias_mora_ajustado_2', 'Bucket_Morosidad_Real', 'Avance_Pago_Plazo', 'Monto_otorgado_2', 'Rango_Monto',
    'Bucket_Morosidad_Final', 'Delincuencia_jueves', 'dias_moda_martes', 'bucket_inicio_jueves',
    'bucket_corte_martes', 'bucket_actual', 'delincuencia_martes', 'fecha_ultimo_abono_efectivo_actual',
    'fecha_ultimo_abono_efectivo_domingo_1', 'fecha_ultimo_abono_efectivo_domingo_2',
    'fecha_ultimo_abono_efectivo_domingo_3', 'fecha_ultimo_abono_efectivo_domingo_4', 'pago_acreditado',
    'Dia_pago_moda', 'Saldo_total_capital_cierre', 'Dias_mora_cierre', 'Domicilio_Completo',
    'Gestor_Asignado', 'Jefe_de_Plaza', 'Zonal', 'Territorial', 'Dias_mora_Lunes_07_30',
    'Dias_mora_Lunes_09_30', 'Dias_mora_Lunes_11_30', 'Dias_mora_Lunes_13_30',
    'Dias_mora_Lunes_14_30', 'Dias_mora_Lunes_16_30', 'Dias_mora_Lunes_18_30',
    'Dias_mora_Lunes_20_30', 'Dias_mora_Lunes_23_50', 'Dias_mora_Martes_07_30',
    'Dias_mora_Martes_09_30', 'Dias_mora_Martes_11_30', 'Dias_mora_Martes_13_30',
    'Dias_mora_Martes_14_30', 'Dias_mora_Martes_16_30', 'Dias_mora_Martes_18_30',
    'Dias_mora_Martes_20_30', 'Dias_mora_Martes_23_50', 'Dias_mora_Miercoles_07_30',
    'Dias_mora_Miercoles_09_30', 'Dias_mora_Miercoles_11_30', 'Dias_mora_Miercoles_13_30',
    'Dias_mora_Miercoles_14_30', 'Dias_mora_Miercoles_16_30', 'Dias_mora_Miercoles_18_30',
    'Dias_mora_Miercoles_20_30', 'Dias_mora_Miercoles_23_50', 'Dias_mora_Jueves_07_30',
    'Dias_mora_Jueves_09_30', 'Dias_mora_Jueves_11_30', 'Dias_mora_Jueves_13_30',
    'Dias_mora_Jueves_14_30', 'Dias_mora_Jueves_16_30', 'Dias_mora_Jueves_18_30',
    'Dias_mora_Jueves_20_30', 'Dias_mora_Jueves_23_50', 'Dias_mora_Viernes_07_30',
    'Dias_mora_Viernes_09_30', 'Dias_mora_Viernes_11_30', 'Dias_mora_Viernes_13_30',
    'Dias_mora_Viernes_14_30', 'Dias_mora_Viernes_16_30', 'Dias_mora_Viernes_18_30',
    'Dias_mora_Viernes_20_30', 'Dias_mora_Viernes_23_50', 'Dias_mora_Sabado_07_30',
    'Dias_mora_Sabado_09_30', 'Dias_mora_Sabado_11_30', 'Dias_mora_Sabado_13_30',
    'Dias_mora_Sabado_14_30', 'Dias_mora_Sabado_16_30', 'Dias_mora_Sabado_18_30',
    'Dias_mora_Sabado_20_30', 'Dias_mora_Sabado_23_50', 'Dias_mora_Domingo_07_30',
    'Dias_mora_Domingo_09_30', 'Dias_mora_Domingo_11_30', 'Dias_mora_Domingo_13_30',
    'Dias_mora_Domingo_14_30', 'Dias_mora_Domingo_16_30', 'Dias_mora_Domingo_18_30',
    'Dias_mora_Domingo_20_30', 'Dias_mora_Domingo_23_50', 'Dias_mora_cierre_semana',
    'Observaciones', 'Cierre_Actual', 'Saldo_vencido_actualizado', 'Ajuste', 'Ghost',
    'Fecha_ultimo_pago_efectivo', 'Cuotas_vencidas', 'Cuotas_devengadas', 'Calle_adicional_1',
    'Num_exterior_adicional_1', 'Num_interior_adicional_1', 'Cp_adicional_2', 'Colonia_adicional_1',
    'Estado_adicional_2', 'Ciudad_adicional_1', 'Municipio_adicional_1', 'Coordenada_fat',
    'Direccion_maps', 'Donde_firma', 'D_asenta', 'D_mnpio', 'D_estado', 'Codigo_postal_adicional_3',
    'Direccion', 'Calle_numero', 'Colonia_adicional_2', 'Ciudad_adicional_2', 'Estado_adicional_3',
    'Calle_numero_adic', 'Codigo_postal_adic', 'Adicionales_colonia', 'Municipio_delegacion',
    'Entidad_1', 'Calle_adicional_2', 'Num_exterior_adicional_2', 'Num_interior_adicional_2',
    'Cp_adicional_3', 'Colonia_adicional_3', 'Estado_adicional_4', 'Ciudad_adicional_3',
    'Municipio_adicional_2', 'Tipo_de_contacto', 'Medio_de_contacto', 'Gestiones', 'Ultimo_Dictamen',
    'Promesas_Totales', 'Promesas_cumplidas', 'Promesa_Vigente', 'Promesa_Rota', 'Dia_de_la_prom',
    'Promesa_de_pago', 'Monto_abono_efectivo', 'Bucket_ajustado_ghost', 'Variable_3', 'Variable_4',
    'Variable_5', 'Variable_6', 'Variable_7', 'Variable_8', 'Variable_9', 'Variable_10',
    'SEMANA', 'fecha_hora_insert', 'reporte_lock',
];

const COLUMNAS_FECHA = [
    'Fecha_nacimiento', 'Fecha_inicio', 'Fecha_primer_vencimiento', 'Fecha_ultimo_vencimiento',
    'fecha_ultimo_abono_efectivo_actual', 'fecha_ultimo_abono_efectivo_domingo_1',
    'fecha_ultimo_abono_efectivo_domingo_2', 'fecha_ultimo_abono_efectivo_domingo_3',
    'fecha_ultimo_abono_efectivo_domingo_4', 'Fecha_ultimo_pago_efectivo', 'fecha_hora_insert',
];

final class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 1;
    private int $endRow = 1;

    public function setRows(int $startRow, int $endRow): void
    {
        $this->startRow = $startRow;
        $this->endRow = $endRow;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}

function spanishLooseDateToMysql(string $s): ?string
{
    $s = trim($s);
    if ($s === '') {
        return null;
    }
    $meses = [
        'enero' => 'January', 'febrero' => 'February', 'marzo' => 'March', 'abril' => 'April',
        'mayo' => 'May', 'junio' => 'June', 'julio' => 'July', 'agosto' => 'August',
        'septiembre' => 'September', 'octubre' => 'October', 'noviembre' => 'November', 'diciembre' => 'December',
    ];
    $low = mb_strtolower($s);
    foreach ($meses as $es => $en) {
        if (mb_stripos($low, $es) !== false) {
            $low = str_ireplace($es, $en, $low);
            break;
        }
    }
    $low = preg_replace('/^(lunes|martes|mi[ée]rcoles|jueves|viernes|s[áa]bado|domingo),?\s*/iu', '', $low);
    $low = preg_replace('/\s+de\s+/i', ' ', $low);
    $t = strtotime($low);
    if ($t === false) {
        return null;
    }

    return date('Y-m-d', $t);
}

function esColumnaFecha(string $nombre): bool
{
    return in_array($nombre, COLUMNAS_FECHA, true)
        || preg_match('/^(fecha|Fecha_)/i', $nombre) === 1
        || str_contains(strtolower($nombre), 'fecha_');
}

/**
 * @return string|int|float|null valor para bind PDO
 */
function normalizarCelda(string $colDb, mixed $valor)
{
    if ($valor === null) {
        return null;
    }
    if (is_string($valor)) {
        $valor = trim($valor);
        if ($valor === '' || $valor === '-') {
            return null;
        }
    }

    if (is_float($valor) || is_int($valor)) {
        if (esColumnaFecha($colDb)) {
            $n = (float) $valor;
            if ($n > 20000 && $n < 80000) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject($n);

                    return $dt->format(str_contains($colDb, 'fecha_hora') ? 'Y-m-d H:i:s' : 'Y-m-d');
                } catch (\Throwable $e) {
                    // sigue
                }
            }
        }
        if ($colDb === 'Referencia_stp' && (abs($valor) >= 1e12 || is_float($valor))) {
            return sprintf('%.0f', (float) $valor);
        }

        return $valor;
    }

    if ($valor instanceof \DateTimeInterface) {
        return $valor->format(esColumnaFecha($colDb) && str_contains($colDb, 'fecha_hora') ? 'Y-m-d H:i:s' : 'Y-m-d');
    }

    $str = is_scalar($valor) ? (string) $valor : '';
    if ($str === '') {
        return null;
    }

    if (esColumnaFecha($colDb)) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $str)) {
            return substr($str, 0, 10);
        }
        $parsed = spanishLooseDateToMysql($str);

        return $parsed;
    }

    return $str;
}

/** @return array<int, string> índice 0-based columna Excel => nombre BD */
function leerCabeceras(string $path, XlsxReader $reader, ChunkReadFilter $filter): array
{
    $filter->setRows(1, 1);
    $reader->setReadFilter($filter);
    $ss = $reader->load($path);
    $ws = $ss->getActiveSheet();
    $lastCol = Coordinate::columnIndexFromString($ws->getHighestColumn());
    $map = [];
    for ($i = 1; $i <= $lastCol; $i++) {
        $letter = Coordinate::stringFromColumnIndex($i);
        $h = trim((string) $ws->getCell($letter . '1')->getValue());
        if ($h !== '') {
            $map[$i - 1] = $h;
        }
    }
    $ss->disconnectWorksheets();
    unset($ss);

    return $map;
}

/**
 * @param array<int, string> $excelColIndexToDbName
 * @return array<string, mixed>
 */
function filaExcelABind(
    \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
    int $row,
    array $excelColIndexToDbName,
    array $columnasInsertar
): array {
    $out = [];
    foreach ($columnasInsertar as $colDb) {
        $out[$colDb] = null;
    }
    foreach ($excelColIndexToDbName as $zeroIdx => $nombreExcel) {
        if (!in_array($nombreExcel, $columnasInsertar, true)) {
            continue;
        }
        $letter = Coordinate::stringFromColumnIndex($zeroIdx + 1);
        $raw = $ws->getCell($letter . $row)->getValue();
        $out[$nombreExcel] = normalizarCelda($nombreExcel, $raw);
    }

    return $out;
}

// --- CLI ---
$argv = $_SERVER['argv'] ?? [];
$path = '';
$dry = false;
$withPk = false;
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--dry-run') {
        $dry = true;
    } elseif ($a === '--with-pk-id') {
        $withPk = true;
    } elseif ($a !== '' && $path === '') {
        $path = $a;
    }
}

if ($path === '' || !is_readable($path)) {
    fwrite(STDERR, "Uso: php import_segundometro_histo_xlsx.php <archivo.xlsx> [--dry-run] [--with-pk-id]\n");
    exit(1);
}

$reader = new XlsxReader();
$reader->setReadDataOnly(true);
$reader->setReadEmptyCells(false);
$filter = new ChunkReadFilter();

$info = $reader->listWorksheetInfo($path)[0];
$totalRows = (int) ($info['totalRows'] ?? 0);
if ($totalRows < 2) {
    fwrite(STDERR, "El archivo no tiene filas de datos.\n");
    exit(1);
}

$headers = leerCabeceras($path, $reader, $filter);
$pkNombreExcel = 'id_segundometro_histo';

$columnasInsertar = COLUMNAS_HISTO;
if ($withPk && in_array($pkNombreExcel, $headers, true)) {
    array_unshift($columnasInsertar, 'id_segundometro_histo');
}

// Comprobar que el Excel cubre las columnas mínimas
$faltan = array_diff(COLUMNAS_HISTO, $headers);
if ($faltan !== []) {
    fwrite(STDERR, 'Advertencia: faltan columnas en el Excel (se insertará NULL): ' . implode(', ', array_slice($faltan, 0, 15)) . (count($faltan) > 15 ? '...' : '') . "\n");
}

$excelColIndexToDbName = [];
foreach ($headers as $idx => $name) {
    $excelColIndexToDbName[$idx] = $name;
}

$chunkSize = 150;
$insertadas = 0;
$errores = 0;

$db = new \Core\DatabaseSegundometro();

$colsSql = array_map(static fn ($c) => '`' . str_replace('`', '``', $c) . '`', $columnasInsertar);
$sqlBase = 'INSERT INTO tbl_segundometro_histo (' . implode(',', $colsSql) . ') VALUES ';

for ($start = 2; $start <= $totalRows; $start += $chunkSize) {
    $end = min($start + $chunkSize - 1, $totalRows);
    // Solo este rango de filas (no desde la 1) para no cargar todo el libro en memoria.
    $filter->setRows($start, $end);
    $reader->setReadFilter($filter);
    $spreadsheet = $reader->load($path);
    $sheet = $spreadsheet->getActiveSheet();

    for ($row = max(2, $start); $row <= $end; $row++) {
        $bind = filaExcelABind($sheet, $row, $excelColIndexToDbName, $columnasInsertar);
        if ($withPk && isset($bind['id_segundometro_histo']) && ($bind['id_segundometro_histo'] === null || $bind['id_segundometro_histo'] === '')) {
            continue;
        }
        if (($bind['Id_credito'] ?? null) === null && ($bind['KT'] ?? '') === '') {
            continue;
        }

        $placeholders = [];
        $params = [];
        $i = 0;
        foreach ($columnasInsertar as $col) {
            $k = 'p' . $i++;
            $placeholders[] = ':' . $k;
            $v = $bind[$col] ?? null;
            $params[$k] = $v;
        }

        $sql = $sqlBase . '(' . implode(',', $placeholders) . ')';

        if ($dry) {
            if ($insertadas < 2) {
                echo "[dry-run] fila $row Id_credito=" . ($bind['Id_credito'] ?? '') . " SEMANA=" . ($bind['SEMANA'] ?? '') . "\n";
            }
            $insertadas++;
            continue;
        }

        try {
            $db->CRUD($sql, $params);
            $insertadas++;
        } catch (\Throwable $e) {
            $errores++;
            fwrite(STDERR, "Error fila $row: " . $e->getMessage() . "\n");
        }
    }

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
}

echo $dry ? "Simulación: {$insertadas} filas procesadas.\n" : "Insertadas (intentos OK): {$insertadas}. Errores: {$errores}.\n";
