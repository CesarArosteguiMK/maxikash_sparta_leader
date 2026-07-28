<?php
declare(strict_types=1);

/**
 * Importa un archivo histórico de Motos Adjudicadas al listado de tickets concluidos.
 *
 * Uso:
 *   C:\xampp\php\php.exe scripts\importar_historico_motos_adjudicadas.php "C:\ruta\archivo.xlsx" --dry-run
 *   C:\xampp\php\php.exe scripts\importar_historico_motos_adjudicadas.php "C:\ruta\archivo.xlsx"
 *
 * La importación es idempotente: al volver a ejecutar el mismo archivo, se omiten
 * créditos que ya existen para no alterar operaciones activas ni duplicar el histórico.
 */

require_once __DIR__ . '/../backend/bootstrap_composer.php';
sparta_require_composer_autoload();
require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

const ETIQUETA_CARGA = 'Carga masiva historico';
const FECHA_REFERENCIA = '2026-07-26 12:00:00';
const PREFIJO_FOLIO = 'HMA-20260726-';
const ARCHIVO_FUENTE = 'Historico_MotosAdjudicadas_07-26 (2).xlsx';

function texto($value, ?int $maxLength = null): ?string
{
    if ($value === null) {
        return null;
    }
    $value = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
    if ($value === '' || in_array(strtoupper($value), ['N/A', '#N/A', 'NA', 'NULL'], true)) {
        return null;
    }
    if ($maxLength !== null && mb_strlen($value) > $maxLength) {
        $value = mb_substr($value, 0, $maxLength);
    }
    return $value;
}

function fechaCelda($cell): string
{
    $value = $cell->getValue();
    if (is_numeric($value) && (float) $value > 0 && ExcelDate::isDateTime($cell)) {
        return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d H:i:s');
    }
    $raw = texto($value);
    if ($raw === null || $raw === '00:00:00') {
        return FECHA_REFERENCIA;
    }
    foreach (['Y-m-d H:i:s', 'Y-m-d', 'd/m/Y H:i:s', 'd/m/Y'] as $format) {
        $date = DateTimeImmutable::createFromFormat('!' . $format, $raw);
        if ($date instanceof DateTimeImmutable) {
            return $date->format('Y-m-d H:i:s');
        }
    }
    return FECHA_REFERENCIA;
}

function celda($sheet, int $column, int $row)
{
    return $sheet->getCell(Coordinate::stringFromColumnIndex($column) . $row);
}

function columnasEsperadas(array $headers): void
{
    $expectedByPosition = [
        0 => 'CREDITO', 1 => 'NOMBRE DE CLIENTE', 2 => 'SERIE', 3 => 'MARCA',
        4 => 'MODELO', 6 => 'COLOR', 7 => 'KILOMETRAJE', 8 => 'ULTIMO GESTOR',
        9 => 'DIRECCI', 10 => 'LUGAR DE RESGUARDO', 11 => 'LUGAR DE RESGUARDO2',
        12 => 'NO. MOTOR', 13 => 'FECHA DE ADJUDICACI',
    ];
    foreach ($expectedByPosition as $position => $column) {
        $header = strtoupper((string) ($headers[$position] ?? ''));
        if (!str_contains($header, $column)) {
            throw new RuntimeException('El archivo no contiene la columna esperada: ' . $column);
        }
    }
}

function existentesPorCredito(Database $db, array $credits): array
{
    $result = [];
    foreach (array_chunk(array_values(array_unique($credits)), 500) as $chunk) {
        $params = [];
        $holders = [];
        foreach ($chunk as $index => $credit) {
            $key = 'credito_' . $index;
            $holders[] = ':' . $key;
            $params[$key] = $credit;
        }
        foreach ($db->queryAll('SELECT DISTINCT id_credito FROM adj_operacion WHERE id_credito IN (' . implode(', ', $holders) . ')', $params) as $row) {
            $result[(int) $row['id_credito']] = true;
        }
    }
    return $result;
}

function insertarLote(Database $db, array $records): int
{
    if ($records === []) {
        return 0;
    }
    $columns = array_keys($records[0]);
    $values = [];
    $params = [];
    foreach ($records as $rowIndex => $record) {
        $holders = [];
        foreach ($columns as $column) {
            $key = 'r' . $rowIndex . '_' . $column;
            $holders[] = ':' . $key;
            $params[$key] = $record[$column];
        }
        $values[] = '(' . implode(', ', $holders) . ')';
    }
    $sql = 'INSERT INTO adj_operacion (`' . implode('`, `', $columns) . '`) VALUES ' . implode(', ', $values);
    return $db->CRUD($sql, $params);
}

$input = $argv[1] ?? '';
$dryRun = in_array('--dry-run', $argv, true);
if ($input === '' || !is_file($input)) {
    fwrite(STDERR, "Indica un archivo Excel existente.\n");
    exit(2);
}

$spreadsheet = IOFactory::load($input);
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestDataRow();
$highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
$headers = [];
for ($column = 1; $column <= $highestColumn; $column++) {
    $headers[] = celda($sheet, $column, 1)->getValue();
}
columnasEsperadas($headers);

$sourceRows = [];
$invalidRows = [];
for ($row = 2; $row <= $highestRow; $row++) {
    $credit = (int) (celda($sheet, 1, $row)->getValue() ?? 0);
    $client = texto(celda($sheet, 2, $row)->getValue(), 200);
    if ($credit <= 0 || $client === null) {
        $invalidRows[] = $row;
        continue;
    }
    $date = fechaCelda(celda($sheet, 14, $row));
    $sourceRows[] = [
        'folio' => PREFIJO_FOLIO . str_pad((string) ($row - 1), 6, '0', STR_PAD_LEFT),
        'id_credito' => $credit,
        'nombre_cliente' => $client,
        'estatus' => 'Recepción',
        'area_actual' => ETIQUETA_CARGA,
        'direccion_recoleccion' => texto(celda($sheet, 10, $row)->getValue(), 500),
        'marca' => texto(celda($sheet, 4, $row)->getValue(), 100),
        'modelo' => texto(celda($sheet, 5, $row)->getValue(), 100),
        'serie' => texto(celda($sheet, 3, $row)->getValue(), 100),
        'num_motor' => texto(celda($sheet, 13, $row)->getValue(), 100),
        'moto_marca' => texto(celda($sheet, 4, $row)->getValue(), 80),
        'moto_modelo' => texto(celda($sheet, 5, $row)->getValue(), 80),
        'moto_anio' => ($year = (int) (celda($sheet, 6, $row)->getValue() ?? 0)) >= 1900 && $year <= 2100 ? $year : null,
        'moto_color' => texto(celda($sheet, 7, $row)->getValue(), 40),
        'moto_no_serie' => texto(celda($sheet, 3, $row)->getValue(), 30),
        'moto_no_motor' => texto(celda($sheet, 13, $row)->getValue(), 30),
        'log_ubicacion' => texto(celda($sheet, 11, $row)->getValue(), 120),
        'log_direccion' => texto(celda($sheet, 10, $row)->getValue(), 200),
        'log_ciudad' => texto(celda($sheet, 11, $row)->getValue(), 80),
        'log_estado' => texto(celda($sheet, 12, $row)->getValue(), 60),
        'log_lugar_resguardo' => texto(celda($sheet, 11, $row)->getValue(), 32),
        'log_responsable' => texto(celda($sheet, 9, $row)->getValue(), 120),
        'kilometraje' => texto(celda($sheet, 8, $row)->getValue(), 40),
        'id_usuario_alta' => 1,
        'fecha_alta' => $date,
        'fecha_actualizacion' => $date,
        'fecha_llegada_almacen' => $date,
        'recepcion_ubicacion' => texto(celda($sheet, 11, $row)->getValue(), 255),
        'recepcion_observaciones' => 'Carga masiva historico. Fuente: ' . ARCHIVO_FUENTE . '; fila ' . $row . '. Fecha de adjudicacion: ' . $date . '.',
        'recepcion_confirmada_at' => $date,
    ];
}

$db = new Database();
$existingCredits = existentesPorCredito($db, array_column($sourceRows, 'id_credito'));
$toInsert = [];
$skippedExisting = [];
foreach ($sourceRows as $record) {
    if (isset($existingCredits[$record['id_credito']])) {
        $skippedExisting[] = $record['id_credito'];
        continue;
    }
    $toInsert[] = $record;
}

$summary = [
    'archivo' => basename($input),
    'hoja' => $sheet->getTitle(),
    'filas_leidas' => $highestRow - 1,
    'filas_validas' => count($sourceRows),
    'filas_invalidas' => $invalidRows,
    'creditos_existentes_omitidos' => count($skippedExisting),
    'registros_a_insertar' => count($toInsert),
    'etiqueta' => ETIQUETA_CARGA,
    'modo' => $dryRun ? 'dry-run' : 'aplicar',
];

if (!$dryRun) {
    $inserted = 0;
    $db->beginTransaction();
    try {
        foreach (array_chunk($toInsert, 150) as $batch) {
            $inserted += insertarLote($db, $batch);
        }
        $db->commit();
        $summary['insertados'] = $inserted;
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollback();
        }
        throw $error;
    }
}

echo json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
