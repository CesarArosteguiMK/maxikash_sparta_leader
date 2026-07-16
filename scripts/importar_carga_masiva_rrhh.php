<?php

declare(strict_types=1);

use Core\Database;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

ini_set('memory_limit', '1024M');
set_time_limit(0);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

function text_value(mixed $value, int $max = 500): string
{
    if ($value instanceof RichText) {
        $value = $value->getPlainText();
    }
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    if (is_float($value) && floor($value) === $value) {
        $value = sprintf('%.0f', $value);
    }
    $text = trim((string)($value ?? ''));
    if ($text === '' || str_starts_with($text, '=')) {
        return '';
    }
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    $key = normalized_key($text);
    if (in_array($key, ['N A', 'NA', 'S N', 'SN', 'NO APLICA', 'NO APLICA N A', 'NULL', 'NINGUNO', 'NINGUNA'], true)) {
        return '';
    }
    return mb_substr(trim($text), 0, $max);
}

function normalized_key(mixed $value): string
{
    $text = trim((string)($value ?? ''));
    if ($text === '') {
        return '';
    }
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtoupper($ascii !== false ? $ascii : $text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function compact_key(mixed $value): string
{
    return str_replace(' ', '', normalized_key($value));
}

function employee_key(mixed $value): string
{
    $key = compact_key($value);
    if ($key !== '' && ctype_digit($key)) {
        return ltrim($key, '0') ?: '0';
    }
    return $key;
}

function blank(mixed $value): bool
{
    return trim((string)($value ?? '')) === '';
}

function date_value(mixed $value): ?string
{
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    if (is_int($value) || is_float($value)) {
        try {
            return ExcelDate::excelToDateTimeObject((float)$value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }
    $text = text_value($value, 40);
    if ($text === '') {
        return null;
    }
    foreach (['!Y-m-d', '!d/m/Y', '!d-m-Y', '!m/d/Y'] as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $text);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $date->format('Y-m-d');
        }
    }
    return null;
}

function decimal_value(mixed $value): ?float
{
    if (is_int($value) || is_float($value)) {
        return (float)$value;
    }
    $text = text_value($value, 60);
    if ($text === '') {
        return null;
    }
    $text = str_replace([',', '$', ' '], '', $text);
    $text = preg_replace('/[^0-9.\-]/', '', $text) ?? '';
    return $text === '' ? null : (float)$text;
}

function cell(Worksheet $sheet, int $row, int $column): mixed
{
    return $sheet->getCell(Coordinate::stringFromColumnIndex($column) . $row)->getValue();
}

function identifier(mixed $value, int $max): string
{
    return strtoupper(str_replace(' ', '', text_value($value, $max)));
}

function valid_curp(string $curp): bool
{
    return $curp === '' || (bool)preg_match('/^[A-Z][AEIOUX][A-Z]{2}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/', $curp);
}

function valid_rfc(string $rfc): bool
{
    return $rfc === '' || (bool)preg_match('/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/u', $rfc);
}

function record_from_row(Worksheet $sheet, int $row): array
{
    $sex = normalized_key(cell($sheet, $row, 18));
    if ($sex === '') {
        $sex = normalized_key(cell($sheet, $row, 19));
    }
    $sex = match ($sex) {
        'F', 'FEMENINO', 'MUJER' => 'Femenino',
        'M', 'MASCULINO', 'HOMBRE' => 'Masculino',
        default => text_value(cell($sheet, $row, 19), 20),
    };

    return [
        'row' => $row,
        'registro_patronal' => text_value(cell($sheet, $row, 1), 120),
        'codigo_contpac' => text_value(cell($sheet, $row, 2), 80),
        'nombre_completo' => text_value(cell($sheet, $row, 3), 260),
        'fecha_ingreso' => date_value(cell($sheet, $row, 4)),
        'puesto_texto' => text_value(cell($sheet, $row, 5), 180),
        'departamento_texto' => text_value(cell($sheet, $row, 6), 180),
        'area_texto' => text_value(cell($sheet, $row, 7), 180),
        'direccion_organizacional' => text_value(cell($sheet, $row, 8), 180),
        'ubicacion_laboral' => text_value(cell($sheet, $row, 9), 180),
        'municipio_laboral' => text_value(cell($sheet, $row, 10), 180),
        'jefe_directo_texto' => text_value(cell($sheet, $row, 11), 220),
        'curp' => identifier(cell($sheet, $row, 12), 18),
        'nss' => identifier(cell($sheet, $row, 13), 20),
        'rfc' => identifier(cell($sheet, $row, 14), 20),
        'entidad_federativa_rfc' => text_value(cell($sheet, $row, 15), 120),
        'codigo_postal' => identifier(cell($sheet, $row, 16), 12),
        'fecha_nacimiento' => date_value(cell($sheet, $row, 17)),
        'sexo' => $sex,
        'telefono' => identifier(cell($sheet, $row, 20), 30),
        'correo' => strtolower(text_value(cell($sheet, $row, 21), 160)),
        'domicilio' => text_value(cell($sheet, $row, 22), 500),
        'carta_no_credito' => text_value(cell($sheet, $row, 23), 120),
        'tipo_credito' => text_value(cell($sheet, $row, 24), 80),
        'numero_credito' => text_value(cell($sheet, $row, 25), 80),
        'monto_descontar' => decimal_value(cell($sheet, $row, 26)),
        'carta_no_nomina_bbva' => text_value(cell($sheet, $row, 27), 120),
        'id_banco' => ctype_digit(identifier(cell($sheet, $row, 28), 20)) ? (int)identifier(cell($sheet, $row, 28), 20) : null,
        'nombre_banco' => text_value(cell($sheet, $row, 29), 120),
        'numero_cuenta' => identifier(cell($sheet, $row, 30), 40),
        'clabe' => identifier(cell($sheet, $row, 31), 30),
        'contactos' => [
            ['nombre' => text_value(cell($sheet, $row, 32), 220), 'parentesco' => text_value(cell($sheet, $row, 33), 80), 'numero' => identifier(cell($sheet, $row, 34), 30)],
            ['nombre' => text_value(cell($sheet, $row, 35), 220), 'parentesco' => text_value(cell($sheet, $row, 36), 80), 'numero' => identifier(cell($sheet, $row, 37), 30)],
        ],
        'beneficiarios' => [
            ['nombre' => text_value(cell($sheet, $row, 38), 220), 'parentesco' => text_value(cell($sheet, $row, 39), 80), 'numero' => identifier(cell($sheet, $row, 40), 30)],
            ['nombre' => text_value(cell($sheet, $row, 41), 220), 'parentesco' => text_value(cell($sheet, $row, 42), 80), 'numero' => identifier(cell($sheet, $row, 43), 30)],
        ],
    ];
}

function add_index(array &$indexes, string $type, string $key, int $id): void
{
    if ($key === '') {
        return;
    }
    $indexes[$type][$key][$id] = true;
}

function build_indexes(array $people): array
{
    $indexes = array_fill_keys(['codigo', 'curp', 'rfc', 'nss', 'correo', 'nombre'], []);
    foreach ($people as $person) {
        $id = (int)$person['id'];
        add_index($indexes, 'codigo', employee_key($person['codigo_contpac'] ?? ''), $id);
        add_index($indexes, 'codigo', employee_key($person['codigo_contpaq'] ?? ''), $id);
        add_index($indexes, 'curp', compact_key($person['curp'] ?? ''), $id);
        add_index($indexes, 'rfc', compact_key($person['rfc_persona'] ?? ''), $id);
        add_index($indexes, 'rfc', compact_key($person['rfc_rrhh'] ?? ''), $id);
        add_index($indexes, 'nss', compact_key($person['nss'] ?? ''), $id);
        add_index($indexes, 'correo', strtolower(trim((string)($person['correo'] ?? ''))), $id);
        add_index($indexes, 'nombre', normalized_key($person['nombre_completo'] ?? ''), $id);
    }
    return $indexes;
}

function ids_for(array $indexes, string $type, string $key): array
{
    return $key === '' ? [] : array_map('intval', array_keys($indexes[$type][$key] ?? []));
}

function match_person(array $indexes, array $record): array
{
    $strong = [
        'codigo' => employee_key($record['codigo_contpac']),
        'curp' => compact_key($record['curp']),
        'rfc' => compact_key($record['rfc']),
        'nss' => compact_key($record['nss']),
    ];
    $strongIds = [];
    $reasons = [];
    $ambiguousSets = [];
    foreach ($strong as $type => $key) {
        $ids = ids_for($indexes, $type, $key);
        if (count($ids) === 1) {
            $strongIds[$ids[0]] = true;
            $reasons[] = $type;
        } elseif (count($ids) > 1) {
            $ambiguousSets[$type] = $ids;
        }
    }
    if (count($strongIds) > 1) {
        return [null, 'conflicto_identificadores', array_keys($strongIds)];
    }
    if (count($strongIds) === 1) {
        $candidate = (int)array_key_first($strongIds);
        foreach ($ambiguousSets as $type => $ids) {
            if (!in_array($candidate, $ids, true)) {
                return [null, 'conflicto_identificadores', [$type => $ids, 'coincidencia_unica' => $candidate]];
            }
        }
        return [$candidate, implode('+', $reasons), []];
    }
    if ($ambiguousSets !== []) {
        $intersection = null;
        foreach ($ambiguousSets as $ids) {
            $intersection = $intersection === null ? $ids : array_values(array_intersect($intersection, $ids));
        }
        if (count($intersection ?? []) === 1) {
            return [(int)$intersection[0], 'interseccion_identificadores', []];
        }
        return [null, 'ambiguo:identificadores', $ambiguousSets];
    }

    $emailIds = ids_for($indexes, 'correo', strtolower($record['correo']));
    if (count($emailIds) === 1) {
        return [$emailIds[0], 'correo', []];
    }
    if (count($emailIds) > 1) {
        return [null, 'ambiguo:correo', $emailIds];
    }
    $nameIds = ids_for($indexes, 'nombre', normalized_key($record['nombre_completo']));
    if (count($nameIds) === 1) {
        return [$nameIds[0], 'nombre', []];
    }
    if (count($nameIds) > 1) {
        return [null, 'ambiguo:nombre', $nameIds];
    }
    return [null, 'sin_match', []];
}

function changed_fill(array &$current, array $incoming, array $comparators, array &$conflicts): array
{
    $updates = [];
    foreach ($incoming as $field => $value) {
        if ($value === null || blank($value)) {
            continue;
        }
        $existing = $current[$field] ?? null;
        if (blank($existing)) {
            $updates[$field] = $value;
            $current[$field] = $value;
            continue;
        }
        if (isset($comparators[$field])) {
            $normalizer = $comparators[$field];
            if ($normalizer($existing) !== $normalizer($value)) {
                $conflicts[$field] = ['actual' => $existing, 'excel' => $value];
            }
        }
    }
    return $updates;
}

function sql_update(Database $db, string $table, string $idField, int $id, array $updates, bool $apply): void
{
    if (!$apply || $updates === []) {
        return;
    }
    $sets = [];
    $params = ['row_id' => $id];
    foreach ($updates as $field => $value) {
        $sets[] = "`$field` = :f_$field";
        $params["f_$field"] = $value;
    }
    $db->CRUD("UPDATE `$table` SET " . implode(', ', $sets) . " WHERE `$idField` = :row_id", $params);
}

function related_map(array $rows, string $nameField): array
{
    $map = [];
    foreach ($rows as $row) {
        $map[(int)$row['id_persona']][normalized_key($row[$nameField] ?? '')] = $row;
    }
    return $map;
}

function issue(array &$issues, array $record, string $type, string $detail, mixed $extra = null): void
{
    $item = ['fila' => $record['row'], 'nombre' => $record['nombre_completo'], 'tipo' => $type, 'detalle' => $detail];
    if ($extra !== null) {
        $item['datos'] = $extra;
    }
    $issues[] = $item;
}

$path = '';
$apply = false;
$reportPath = '';
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif (str_starts_with($arg, '--report=')) {
        $reportPath = substr($arg, 9);
    } elseif ($path === '') {
        $path = $arg;
    }
}
if ($path === '' || !is_readable($path)) {
    fwrite(STDERR, "Uso: php scripts/importar_carga_masiva_rrhh.php <archivo.xlsx> [--apply] [--report=archivo.json]\n");
    exit(1);
}

$reader = PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
$reader->setReadDataOnly(true);
$book = $reader->load($path);
$sheet = $book->getSheetByName('Hoja1') ?? $book->getSheet(0);

$db = new Database();
$peopleRows = $db->queryAll("
    SELECT p.*, rr.codigo_contpaq, rr.rfc AS rfc_rrhh, rr.nss,
           p.rfc AS rfc_persona,
           CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo
    FROM persona p
    LEFT JOIN persona_datos_rrhh rr ON rr.id_persona = p.id
");
$people = [];
foreach ($peopleRows as $row) {
    $people[(int)$row['id']] = $row;
}
$indexes = build_indexes($peopleRows);
$rrhh = [];
foreach ($db->queryAll('SELECT * FROM persona_datos_rrhh') as $row) {
    $rrhh[(int)$row['id_persona']] = $row;
}
$phones = related_map($db->queryAll('SELECT * FROM telefonos_persona'), 'numero');
$emails = related_map($db->queryAll('SELECT * FROM correos_persona'), 'correo');
$addresses = related_map($db->queryAll('SELECT * FROM domicilio_persona'), 'domicilio_texto');
$contacts = related_map($db->queryAll('SELECT * FROM contacto_persona_emergencia'), 'nombre_contacto');
$beneficiaries = related_map($db->queryAll('SELECT * FROM persona_beneficiario_fallecimiento'), 'nombre_beneficiario');
$bankAccounts = [];
foreach ($db->queryAll('SELECT * FROM persona_cuenta_bancaria') as $row) {
    $id = (int)$row['id_persona'];
    foreach ([$row['clabe'] ?? '', $row['numero_cuenta'] ?? ''] as $value) {
        if (compact_key($value) !== '') {
            $bankAccounts[$id][compact_key($value)] = $row;
        }
    }
}
$credits = [];
foreach ($db->queryAll('SELECT * FROM persona_credito_laboral') as $row) {
    $key = normalized_key($row['tipo_credito'] ?? '') . '|' . compact_key($row['numero_credito'] ?? '');
    $credits[(int)$row['id_persona']][$key] = $row;
}

$stats = array_fill_keys([
    'filas_leidas', 'personas_encontradas', 'sin_match', 'ambiguas', 'filas_invalidas',
    'persona_actualizada', 'rrhh_insertado', 'rrhh_actualizado', 'telefonos_insertados',
    'correos_insertados', 'domicilios_insertados', 'cuentas_insertadas', 'cuentas_actualizadas',
    'creditos_insertados', 'contactos_insertados', 'contactos_actualizados',
    'beneficiarios_insertados', 'beneficiarios_actualizados', 'conflictos_campos'
], 0);
$issues = [];
$matchedIds = [];

if ($apply) {
    $db->beginTransaction();
}
try {
    for ($rowNumber = 2; $rowNumber <= $sheet->getHighestDataRow(); $rowNumber++) {
        $record = record_from_row($sheet, $rowNumber);
        if ($record['nombre_completo'] === '' && $record['codigo_contpac'] === '' && $record['curp'] === '') {
            continue;
        }
        $stats['filas_leidas']++;
        if (!valid_curp($record['curp'])) {
            $stats['filas_invalidas']++;
            issue($issues, $record, 'curp_invalida', 'La CURP del Excel no tiene un formato válido.', $record['curp']);
            $record['curp'] = '';
        }
        if (!valid_rfc($record['rfc'])) {
            $stats['filas_invalidas']++;
            issue($issues, $record, 'rfc_invalido', 'El RFC del Excel no tiene un formato válido.', $record['rfc']);
            $record['rfc'] = '';
        }

        [$idPersona, $matchReason, $matchData] = match_person($indexes, $record);
        if ($idPersona === null) {
            if (str_starts_with($matchReason, 'ambiguo:') || $matchReason === 'conflicto_identificadores') {
                $stats['ambiguas']++;
            } else {
                $stats['sin_match']++;
            }
            issue($issues, $record, $matchReason, 'No se actualizó la fila porque no identifica de forma inequívoca a una persona.', $matchData);
            continue;
        }
        if (isset($matchedIds[$idPersona]) && $matchedIds[$idPersona] !== normalized_key($record['nombre_completo'])) {
            $stats['ambiguas']++;
            issue($issues, $record, 'persona_repetida_conflictiva', 'Dos filas distintas del Excel apuntan a la misma persona.', ['id_persona' => $idPersona]);
            continue;
        }
        $matchedIds[$idPersona] = normalized_key($record['nombre_completo']);
        $stats['personas_encontradas']++;

        $person = &$people[$idPersona];
        $fieldConflicts = [];
        $personUpdates = changed_fill($person, [
            'codigo_contpac' => $record['codigo_contpac'],
            'curp' => $record['curp'],
            'rfc' => $record['rfc'],
            'correo' => $record['correo'],
            'telefono_uno' => $record['telefono'],
            'fecha_ingreso' => $record['fecha_ingreso'],
            'domicilio_calle_texto' => $record['domicilio'],
            'codigo_postal' => $record['codigo_postal'],
        ], [
            'codigo_contpac' => 'employee_key', 'curp' => 'compact_key', 'rfc' => 'compact_key',
            'correo' => fn($v) => strtolower(trim((string)$v)),
        ], $fieldConflicts);
        if ($personUpdates !== []) {
            $stats['persona_actualizada']++;
            sql_update($db, 'persona', 'id', $idPersona, $personUpdates, $apply);
        }

        $birth = $record['fecha_nacimiento'];
        $rrhhIncoming = [
            'registro_patronal' => $record['registro_patronal'],
            'codigo_contpaq' => $record['codigo_contpac'],
            'puesto_texto' => $record['puesto_texto'],
            'departamento_texto' => $record['departamento_texto'],
            'area_texto' => $record['area_texto'],
            'direccion_organizacional' => $record['direccion_organizacional'],
            'ubicacion_laboral' => $record['ubicacion_laboral'],
            'municipio_laboral' => $record['municipio_laboral'],
            'jefe_directo_texto' => $record['jefe_directo_texto'],
            'rfc' => $record['rfc'],
            'nss' => $record['nss'],
            'entidad_federativa_rfc' => $record['entidad_federativa_rfc'],
            'anio' => $birth ? (int)substr($birth, 0, 4) : null,
            'mes' => $birth ? (int)substr($birth, 5, 2) : null,
            'dia' => $birth ? (int)substr($birth, 8, 2) : null,
            'fecha_nacimiento' => $birth,
            'sexo' => $record['sexo'],
            'carta_no_credito' => $record['carta_no_credito'],
            'carta_no_nomina_bbva' => $record['carta_no_nomina_bbva'],
        ];
        if (!isset($rrhh[$idPersona])) {
            $rrhh[$idPersona] = ['id_persona' => $idPersona];
            $rrhhUpdates = changed_fill($rrhh[$idPersona], $rrhhIncoming, [], $fieldConflicts);
            if ($rrhhUpdates !== []) {
                $stats['rrhh_insertado']++;
                if ($apply) {
                    $columns = array_keys($rrhhUpdates);
                    $params = ['id_persona' => $idPersona];
                    foreach ($rrhhUpdates as $field => $value) $params[$field] = $value;
                    $db->CRUD(
                        'INSERT INTO persona_datos_rrhh (`id_persona`, `' . implode('`, `', $columns) . '`) VALUES (:id_persona, :' . implode(', :', $columns) . ')',
                        $params
                    );
                }
            }
        } else {
            $rrhhUpdates = changed_fill($rrhh[$idPersona], $rrhhIncoming, [
                'codigo_contpaq' => 'employee_key', 'rfc' => 'compact_key', 'nss' => 'compact_key',
            ], $fieldConflicts);
            if ($rrhhUpdates !== []) {
                $stats['rrhh_actualizado']++;
                sql_update($db, 'persona_datos_rrhh', 'id_persona', $idPersona, $rrhhUpdates, $apply);
            }
        }

        foreach ($fieldConflicts as $field => $values) {
            $stats['conflictos_campos']++;
            issue($issues, $record, 'conflicto_campo', "Se conservó el valor actual de $field.", ['id_persona' => $idPersona] + $values);
        }

        $phoneKey = normalized_key($record['telefono']);
        if ($phoneKey !== '' && !isset($phones[$idPersona][$phoneKey])) {
            $stats['telefonos_insertados']++;
            if ($apply) $db->CRUD("INSERT INTO telefonos_persona (id_persona, numero, tipo, estatus) VALUES (:id, :numero, 'Personal', 'Activo')", ['id' => $idPersona, 'numero' => $record['telefono']]);
            $phones[$idPersona][$phoneKey] = ['numero' => $record['telefono']];
        }
        $emailKey = normalized_key($record['correo']);
        if ($emailKey !== '' && !isset($emails[$idPersona][$emailKey])) {
            $stats['correos_insertados']++;
            if ($apply) $db->CRUD("INSERT INTO correos_persona (id_persona, correo, tipo, estatus) VALUES (:id, :correo, 'Personal', 'Activo')", ['id' => $idPersona, 'correo' => $record['correo']]);
            $emails[$idPersona][$emailKey] = ['correo' => $record['correo']];
        }
        $addressKey = normalized_key($record['domicilio']);
        if ($addressKey !== '' && !isset($addresses[$idPersona][$addressKey])) {
            $stats['domicilios_insertados']++;
            if ($apply) $db->CRUD("INSERT INTO domicilio_persona (id_persona, domicilio_texto, codigo_postal, tipo, estatus) VALUES (:id, :domicilio, :cp, 'Particular', 'Activo')", ['id' => $idPersona, 'domicilio' => $record['domicilio'], 'cp' => $record['codigo_postal']]);
            $addresses[$idPersona][$addressKey] = ['domicilio_texto' => $record['domicilio']];
        }

        $accountKey = compact_key($record['clabe']) ?: compact_key($record['numero_cuenta']);
        if ($accountKey !== '') {
            $account = $bankAccounts[$idPersona][$accountKey] ?? null;
            if ($account === null) {
                $stats['cuentas_insertadas']++;
                if ($apply) $db->CRUD("INSERT INTO persona_cuenta_bancaria (id_persona, clabe, numero_cuenta, id_banco, nombre_banco, estatus) VALUES (:id, :clabe, :cuenta, :id_banco, :banco, 'Activo')", ['id' => $idPersona, 'clabe' => $record['clabe'], 'cuenta' => $record['numero_cuenta'], 'id_banco' => $record['id_banco'], 'banco' => $record['nombre_banco']]);
                $account = ['id' => $apply ? $db->lastInsertId() : -1, 'clabe' => $record['clabe'], 'numero_cuenta' => $record['numero_cuenta'], 'id_banco' => $record['id_banco'], 'nombre_banco' => $record['nombre_banco']];
            } else {
                $accountConflicts = [];
                $accountUpdates = changed_fill($account, ['clabe' => $record['clabe'], 'numero_cuenta' => $record['numero_cuenta'], 'id_banco' => $record['id_banco'], 'nombre_banco' => $record['nombre_banco']], [], $accountConflicts);
                if ($accountUpdates !== []) {
                    $stats['cuentas_actualizadas']++;
                    sql_update($db, 'persona_cuenta_bancaria', 'id', (int)$account['id'], $accountUpdates, $apply);
                }
            }
            foreach ([compact_key($record['clabe']), compact_key($record['numero_cuenta'])] as $key) if ($key !== '') $bankAccounts[$idPersona][$key] = $account;
        }

        $creditKey = normalized_key($record['tipo_credito']) . '|' . compact_key($record['numero_credito']);
        if (($record['tipo_credito'] !== '' || $record['numero_credito'] !== '' || $record['monto_descontar'] !== null) && !isset($credits[$idPersona][$creditKey])) {
            $stats['creditos_insertados']++;
            if ($apply) $db->CRUD("INSERT INTO persona_credito_laboral (id_persona, tipo_credito, numero_credito, monto_descontar, estatus) VALUES (:id, :tipo, :numero, :monto, 'Activo')", ['id' => $idPersona, 'tipo' => $record['tipo_credito'], 'numero' => $record['numero_credito'], 'monto' => $record['monto_descontar']]);
            $credits[$idPersona][$creditKey] = true;
        }

        foreach ($record['contactos'] as $contact) {
            $key = normalized_key($contact['nombre']);
            if ($key === '') continue;
            if (!isset($contacts[$idPersona][$key])) {
                $stats['contactos_insertados']++;
                if ($apply) $db->CRUD("INSERT INTO contacto_persona_emergencia (id_persona, nombre_contacto, parentesco, numero, estatus) VALUES (:id, :nombre, :parentesco, :numero, 'Activo')", ['id' => $idPersona] + $contact);
                $contacts[$idPersona][$key] = ['id' => $apply ? $db->lastInsertId() : -1, 'nombre_contacto' => $contact['nombre'], 'parentesco' => $contact['parentesco'], 'numero' => $contact['numero']];
            } else {
                $currentContact = &$contacts[$idPersona][$key];
                $dummy = [];
                $updates = changed_fill($currentContact, ['parentesco' => $contact['parentesco'], 'numero' => $contact['numero']], [], $dummy);
                if ($updates !== []) {
                    $stats['contactos_actualizados']++;
                    sql_update($db, 'contacto_persona_emergencia', 'id', (int)$currentContact['id'], $updates, $apply);
                }
            }
        }
        foreach ($record['beneficiarios'] as $beneficiary) {
            $key = normalized_key($beneficiary['nombre']);
            if ($key === '') continue;
            if (!isset($beneficiaries[$idPersona][$key])) {
                $stats['beneficiarios_insertados']++;
                if ($apply) $db->CRUD("INSERT INTO persona_beneficiario_fallecimiento (id_persona, nombre_beneficiario, parentesco, numero, porcentaje, estatus) VALUES (:id, :nombre, :parentesco, :numero, NULL, 'Activo')", ['id' => $idPersona] + $beneficiary);
                $beneficiaries[$idPersona][$key] = ['id' => $apply ? $db->lastInsertId() : -1, 'nombre_beneficiario' => $beneficiary['nombre'], 'parentesco' => $beneficiary['parentesco'], 'numero' => $beneficiary['numero']];
            } else {
                $currentBeneficiary = &$beneficiaries[$idPersona][$key];
                $dummy = [];
                $updates = changed_fill($currentBeneficiary, ['parentesco' => $beneficiary['parentesco'], 'numero' => $beneficiary['numero']], [], $dummy);
                if ($updates !== []) {
                    $stats['beneficiarios_actualizados']++;
                    sql_update($db, 'persona_beneficiario_fallecimiento', 'id', (int)$currentBeneficiary['id'], $updates, $apply);
                }
            }
        }
    }
    if ($apply) {
        $db->commit();
    }
} catch (Throwable $e) {
    if ($apply && $db->inTransaction()) {
        $db->rollback();
    }
    fwrite(STDERR, "La carga fue revertida: {$e->getMessage()}\n");
    exit(2);
}

$verification = [];
$verifiedIds = array_map('intval', array_keys($matchedIds));
if ($verifiedIds !== []) {
    $idParams = [];
    $idTokens = [];
    foreach ($verifiedIds as $index => $id) {
        $key = "verify_id_$index";
        $idParams[$key] = $id;
        $idTokens[] = ":$key";
    }
    $verification = $db->queryOne("
        SELECT COUNT(*) AS personas,
               SUM(TRIM(COALESCE(p.curp, '')) <> '') AS con_curp,
               SUM(TRIM(COALESCE(NULLIF(p.rfc, ''), rr.rfc, '')) <> '') AS con_rfc,
               SUM(TRIM(COALESCE(rr.nss, '')) <> '') AS con_nss,
               SUM(EXISTS(SELECT 1 FROM persona_cuenta_bancaria cb WHERE cb.id_persona = p.id)) AS con_cuenta_bancaria,
               SUM(EXISTS(SELECT 1 FROM contacto_persona_emergencia ce WHERE ce.id_persona = p.id)) AS con_contacto_emergencia,
               SUM(EXISTS(SELECT 1 FROM persona_beneficiario_fallecimiento bf WHERE bf.id_persona = p.id)) AS con_beneficiario
        FROM persona p
        LEFT JOIN persona_datos_rrhh rr ON rr.id_persona = p.id
        WHERE p.id IN (" . implode(', ', $idTokens) . ")
    ", $idParams) ?? [];
}

$report = [
    'modo' => $apply ? 'aplicado' : 'simulacion',
    'archivo' => basename($path),
    'hoja' => $sheet->getTitle(),
    'estadisticas' => $stats,
    'verificacion_bd' => $verification,
    'incidencias' => $issues,
];
if ($reportPath !== '') {
    file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
