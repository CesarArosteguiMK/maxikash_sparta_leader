<?php

declare(strict_types=1);

use Core\Database;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Models\CapHum;
use Models\CapHumRrhh;

ini_set('memory_limit', '1536M');
set_time_limit(0);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/EnvLoader.php';
require dirname(__DIR__) . '/backend/core/Database.php';
require dirname(__DIR__) . '/backend/core/Model.php';
require dirname(__DIR__) . '/backend/models/CapHum.php';
require dirname(__DIR__) . '/backend/models/CapHumRrhh.php';

\Core\EnvLoader::load();

function usage(): void
{
    fwrite(STDERR, "Uso: php scripts/importar_control_empleados_2026.php <archivo.xlsx> [--apply]\n");
    fwrite(STDERR, "Por defecto corre en dry-run y no escribe en la base.\n");
}

final class ControlEmpleadosReadFilter implements IReadFilter
{
    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        $columnIndex = Coordinate::columnIndexFromString((string)$columnAddress);
        if ($worksheetName === 'catálogo' || $worksheetName === 'catalogo') {
            return $columnIndex <= 15;
        }

        return $columnIndex <= 61;
    }
}

function text_value(mixed $value): string
{
    if ($value instanceof RichText) {
        $value = $value->getPlainText();
    }
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }

    $text = trim((string)($value ?? ''));
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function norm_key(mixed $value): string
{
    $text = text_value($value);
    if ($text === '') {
        return '';
    }

    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtoupper($text);
    $text = str_replace(['&'], ['Y'], $text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function clean_text(mixed $value, int $max = 255): string
{
    $text = text_value($value);
    if (str_starts_with($text, '=')) {
        return '';
    }
    $key = norm_key($text);
    if ($key === '' || in_array($key, ['N A', 'NA', 'S N', 'SN', 'NO APLICA', 'NULL', 'NONE'], true)) {
        return '';
    }

    return mb_substr($text, 0, $max);
}

function canonical_org_text(mixed $value): string
{
    $text = clean_text($value, 180);
    $aliases = [
        'COBRANZA COPORATIVO' => 'COBRANZA CORPORATIVO',
        'COBRANZA CORPORATIVO' => 'COBRANZA CORPORATIVO',
    ];

    $key = norm_key($text);
    return $aliases[$key] ?? $text;
}

function parse_date_value(mixed $value): ?string
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

    $text = clean_text($value, 40);
    if ($text === '') {
        return null;
    }
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $text, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
    }
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $text, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
    }

    $ts = strtotime($text);
    return $ts ? date('Y-m-d', $ts) : null;
}

function parse_decimal_value(mixed $value): ?float
{
    if (is_int($value) || is_float($value)) {
        return (float)$value;
    }
    $text = clean_text($value, 40);
    if ($text === '') {
        return null;
    }

    $text = str_replace([',', '$', 'Q', 'q', ' '], '', $text);
    $text = preg_replace('/[^0-9.\-]/', '', $text) ?? '';
    if ($text === '' || $text === '-' || $text === '.') {
        return null;
    }

    return (float)$text;
}

function header_map(Worksheet $ws, int $headerRow): array
{
    $map = [];
    $maxCol = Coordinate::columnIndexFromString($ws->getHighestColumn());
    for ($col = 1; $col <= $maxCol; $col++) {
        $key = norm_key($ws->getCell(Coordinate::stringFromColumnIndex($col) . $headerRow)->getValue());
        if ($key !== '') {
            $map[$key] = $col;
        }
    }
    return $map;
}

function col_value(Worksheet $ws, array $header, int $row, array|string $names): mixed
{
    foreach ((array)$names as $name) {
        $key = norm_key($name);
        if (isset($header[$key])) {
            return $ws->getCell(Coordinate::stringFromColumnIndex($header[$key]) . $row)->getValue();
        }
    }
    return null;
}

function put_unique_map(array &$map, string $key, int $id): void
{
    if ($key === '' || $id <= 0) {
        return;
    }
    if (!isset($map[$key])) {
        $map[$key] = $id;
        return;
    }
    if ((int)$map[$key] !== $id) {
        $map[$key] = 0;
    }
}

function pais_id(Database $db, string $pais): int
{
    $rows = $db->queryAll("SELECT id, nombre, codigo_iso FROM __SPARTA_SECRET_REDACTED__.paises");
    $paisKey = norm_key($pais);
    foreach ($rows as $row) {
        if (norm_key($row['nombre'] ?? '') === $paisKey || norm_key($row['codigo_iso'] ?? '') === $paisKey) {
            return (int)$row['id'];
        }
    }
    foreach ($rows as $row) {
        if (norm_key($row['nombre'] ?? '') === 'MEXICO' || norm_key($row['codigo_iso'] ?? '') === 'MX') {
            return (int)$row['id'];
        }
    }
    return 1;
}

function username_base(string $correo, string $nombres, string $apellidop): string
{
    $base = '';
    if ($correo !== '' && str_contains($correo, '@')) {
        $base = substr($correo, 0, strpos($correo, '@'));
    }
    if ($base === '') {
        $base = trim($nombres . ' ' . $apellidop);
    }

    $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base) ?: $base;
    $base = strtolower($base);
    $base = preg_replace('/[^a-z0-9]+/', '', $base) ?? '';
    return substr($base !== '' ? $base : 'usuario', 0, 34);
}

function unique_username(string $base, array &$used): string
{
    $candidate = substr($base, 0, 40);
    if (!isset($used[$candidate])) {
        $used[$candidate] = true;
        return $candidate;
    }

    for ($i = 2; $i < 100000; $i++) {
        $suffix = (string)$i;
        $candidate = substr($base, 0, max(1, 40 - strlen($suffix))) . $suffix;
        if (!isset($used[$candidate])) {
            $used[$candidate] = true;
            return $candidate;
        }
    }

    throw new RuntimeException('No se pudo generar un usuario unico.');
}

function unique_employee_number(string $preferred, array &$used, int &$max): string
{
    $preferred = clean_text($preferred, 40);
    if ($preferred !== '' && !isset($used[$preferred])) {
        $used[$preferred] = true;
        return $preferred;
    }

    do {
        $max++;
        $candidate = (string)$max;
    } while (isset($used[$candidate]));

    $used[$candidate] = true;
    return $candidate;
}

function load_existing_people(Database $db): array
{
    $rows = $db->queryAll("
        SELECT
            p.id,
            COALESCE(p.id_pais, 1) AS id_pais,
            p.curp,
            p.correo,
            p.user_name,
            p.numero_empleado,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            r.codigo_contpaq,
            r.rfc,
            r.nss
        FROM __SPARTA_SECRET_REDACTED__.persona p
        LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh r ON r.id_persona = p.id
    ");

    $maps = [
        'by_curp' => [],
        'by_rfc' => [],
        'by_nss' => [],
        'by_email' => [],
        'by_employee' => [],
        'by_contpaq_country' => [],
        'by_name_country' => [],
        'usernames' => [],
        'employee_numbers' => [],
        'max_employee_number' => 0,
    ];

    foreach ($rows as $row) {
        $id = (int)($row['id'] ?? 0);
        $pais = (int)($row['id_pais'] ?? 1);
        $fullName = trim(implode(' ', array_filter([
            text_value($row['nombres'] ?? ''),
            text_value($row['segundo_nombre'] ?? ''),
            text_value($row['apellidop'] ?? ''),
            text_value($row['apellidom'] ?? ''),
        ])));

        put_unique_map($maps['by_curp'], norm_key($row['curp'] ?? ''), $id);
        put_unique_map($maps['by_rfc'], norm_key($row['rfc'] ?? ''), $id);
        put_unique_map($maps['by_nss'], norm_key($row['nss'] ?? ''), $id);
        put_unique_map($maps['by_email'], strtolower(clean_text($row['correo'] ?? '', 180)), $id);
        put_unique_map($maps['by_employee'], clean_text($row['numero_empleado'] ?? '', 40), $id);
        put_unique_map($maps['by_contpaq_country'], $pais . '|' . clean_text($row['codigo_contpaq'] ?? '', 80), $id);
        put_unique_map($maps['by_name_country'], $pais . '|' . norm_key($fullName), $id);

        $user = clean_text($row['user_name'] ?? '', 80);
        if ($user !== '') {
            $maps['usernames'][$user] = true;
        }

        $employee = clean_text($row['numero_empleado'] ?? '', 40);
        if ($employee !== '') {
            $maps['employee_numbers'][$employee] = true;
            if (preg_match('/^\d+$/', $employee)) {
                $maps['max_employee_number'] = max($maps['max_employee_number'], (int)$employee);
            }
        }
    }

    return $maps;
}

function find_existing_id(array $record, array $existing): array
{
    $checks = [
        ['curp', norm_key($record['curp'] ?? ''), 'CURP'],
        ['rfc', norm_key($record['rfc'] ?? ''), 'RFC'],
        ['nss', norm_key($record['nss'] ?? ''), 'NSS'],
        ['email', strtolower(clean_text($record['correo'] ?? '', 180)), 'correo'],
        ['contpaq_country', (int)$record['id_pais'] . '|' . clean_text($record['codigo_contpaq'] ?? '', 80), 'codigo CONTPAQ + pais'],
        ['employee', clean_text($record['codigo_contpaq'] ?? '', 40), 'numero de empleado'],
        ['name_country', (int)$record['id_pais'] . '|' . norm_key($record['nombre_completo'] ?? ''), 'nombre + pais'],
    ];

    foreach ($checks as [$mapName, $key, $reason]) {
        if ($key === '' || str_ends_with($key, '|')) {
            continue;
        }
        $map = $existing['by_' . $mapName] ?? [];
        if (!array_key_exists($key, $map)) {
            continue;
        }
        $id = (int)$map[$key];
        if ($id > 0) {
            return [$id, $reason];
        }
        if (in_array($reason, ['CURP', 'RFC', 'NSS'], true)) {
            continue;
        }
        return [-1, 'coincidencia multiple por ' . $reason];
    }

    return [0, ''];
}

function load_position_levels(Spreadsheet $spreadsheet): array
{
    $levels = [];
    $ws = $spreadsheet->getSheetByName('catálogo') ?: $spreadsheet->getSheetByName('catalogo');
    if (!$ws) {
        return $levels;
    }

    for ($row = 1; $row <= $ws->getHighestDataRow(); $row++) {
        $name = clean_text($ws->getCell('N' . $row)->getValue(), 180);
        $level = $ws->getCell('O' . $row)->getValue();
        if ($name !== '' && is_numeric($level)) {
            $levels[norm_key($name)] = (int)$level;
        }
    }

    return $levels;
}

function catalog_key(int $pais, string ...$parts): string
{
    return $pais . '|' . implode('|', array_map('norm_key', $parts));
}

function load_catalog(Database $db): array
{
    $catalog = [
        'direcciones' => [],
        'areas' => [],
        'departamentos' => [],
        'puestos' => [],
        'clave_puestos' => [],
    ];

    foreach ($db->queryAll("SELECT id, nombre, COALESCE(id_pais, 1) AS id_pais FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion") as $row) {
        $catalog['direcciones'][catalog_key((int)$row['id_pais'], (string)$row['nombre'])] = (int)$row['id'];
    }

    foreach ($db->queryAll("
        SELECT a.id, a.nombre, COALESCE(a.id_pais, 1) AS id_pais, ad.id_direccion
        FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional a
        LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_direcciones ad
               ON ad.id_departamento_organizacional = a.id
              AND COALESCE(ad.activo, 1) = 1
    ") as $row) {
        $key = catalog_key((int)$row['id_pais'], (string)$row['nombre']);
        $catalog['areas'][$key] = [
            'id' => (int)$row['id'],
            'id_direccion' => isset($row['id_direccion']) ? (int)$row['id_direccion'] : 0,
        ];
    }

    foreach ($db->queryAll("
        SELECT id, nombre, COALESCE(id_pais, 1) AS id_pais, id_departamento_organizacional
        FROM __SPARTA_SECRET_REDACTED__.departamento
    ") as $row) {
        $catalog['departamentos'][catalog_key(
            (int)$row['id_pais'],
            (string)$row['id_departamento_organizacional'],
            (string)$row['nombre']
        )] = (int)$row['id'];
    }

    foreach ($db->queryAll("SELECT id, clave, nombre, departamento_id FROM __SPARTA_SECRET_REDACTED__.puesto") as $row) {
        $catalog['puestos'][(int)$row['departamento_id'] . '|' . norm_key($row['nombre'] ?? '')] = (int)$row['id'];
        $clave = clean_text($row['clave'] ?? '', 80);
        if ($clave !== '') {
            $catalog['clave_puestos'][$clave] = true;
            $catalog['clave_puestos'][norm_key($clave)] = true;
        }
    }

    return $catalog;
}

function unique_position_key(string $name, int $departmentId, array &$used): string
{
    $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($name)) ?: trim($name);
    $base = strtoupper($base);
    $base = preg_replace('/[^A-Z0-9]+/', '-', $base);
    $base = trim((string)$base, '-');
    if ($base === '') {
        $base = 'PUESTO';
    }

    $suffix = '-' . $departmentId;
    $key = substr($base, 0, max(1, 50 - strlen($suffix))) . $suffix;
    if (!isset($used[$key]) && !isset($used[norm_key($key)])) {
        $used[$key] = true;
        $used[norm_key($key)] = true;
        return $key;
    }

    for ($i = 2; $i < 10000; $i++) {
        $suffix = '-' . $departmentId . '-' . $i;
        $key = substr($base, 0, max(1, 50 - strlen($suffix))) . $suffix;
        if (!isset($used[$key]) && !isset($used[norm_key($key)])) {
            $used[$key] = true;
            $used[norm_key($key)] = true;
            return $key;
        }
    }

    throw new RuntimeException('No se pudo generar clave para el puesto ' . $name);
}

function find_id_by_normalized_name(Database $db, string $sql, array $params, string $name): int
{
    $rows = $db->queryAll($sql, $params);
    $target = norm_key($name);
    foreach ($rows as $row) {
        if (norm_key($row['nombre'] ?? '') === $target) {
            return (int)($row['id'] ?? 0);
        }
    }
    return 0;
}

function ensure_catalog(
    Database $db,
    array $record,
    bool $apply,
    array &$catalog,
    array &$stats,
    array &$plannedCatalogs,
    array $positionLevels
): array {
    $pais = (int)$record['id_pais'];
    $direccion = $record['direccion'];
    $area = $record['area'];
    $departamento = $record['departamento'];
    $puesto = $record['puesto'];

    $dirKey = catalog_key($pais, $direccion);
    if (!isset($catalog['direcciones'][$dirKey])) {
        $stats['catalogo_direcciones_crear']++;
        $plannedCatalogs['direcciones'][$dirKey] = $direccion;
        if ($apply) {
            $id = find_id_by_normalized_name(
                $db,
                "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion WHERE id_pais = :id_pais",
                ['id_pais' => $pais],
                $direccion
            );
            if ($id <= 0) {
                $db->CRUD(
                    "INSERT IGNORE INTO __SPARTA_SECRET_REDACTED__.direcciones_organizacion (nombre, activo, id_pais) VALUES (:nombre, 1, :id_pais)",
                    ['nombre' => $direccion, 'id_pais' => $pais]
                );
                $id = find_id_by_normalized_name(
                    $db,
                    "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion WHERE id_pais = :id_pais",
                    ['id_pais' => $pais],
                    $direccion
                );
            }
            $catalog['direcciones'][$dirKey] = $id > 0 ? $id : $db->lastInsertId();
        } else {
            $catalog['direcciones'][$dirKey] = -1 * max(1, $stats['catalogo_direcciones_crear']);
        }
    }
    $idDireccion = (int)$catalog['direcciones'][$dirKey];

    $areaKey = catalog_key($pais, $area);
    if (!isset($catalog['areas'][$areaKey])) {
        $stats['catalogo_areas_crear']++;
        $plannedCatalogs['areas'][$areaKey] = $direccion . ' > ' . $area;
        if ($apply) {
            $idArea = find_id_by_normalized_name(
                $db,
                "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional WHERE id_pais = :id_pais",
                ['id_pais' => $pais],
                $area
            );
            if ($idArea <= 0) {
                $db->CRUD(
                    "INSERT IGNORE INTO __SPARTA_SECRET_REDACTED__.departamento_organizacional (nombre, activo, id_pais) VALUES (:nombre, 1, :id_pais)",
                    ['nombre' => $area, 'id_pais' => $pais]
                );
                $idArea = find_id_by_normalized_name(
                    $db,
                    "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional WHERE id_pais = :id_pais",
                    ['id_pais' => $pais],
                    $area
                );
                if ($idArea <= 0) {
                    $idArea = $db->lastInsertId();
                }
            }
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_direcciones (id_direccion, id_departamento_organizacional, activo)
                 VALUES (:id_direccion, :id_area, 1)
                 ON DUPLICATE KEY UPDATE id_direccion = VALUES(id_direccion), activo = 1",
                ['id_direccion' => $idDireccion, 'id_area' => $idArea]
            );
            $catalog['areas'][$areaKey] = ['id' => $idArea, 'id_direccion' => $idDireccion];
        } else {
            $catalog['areas'][$areaKey] = [
                'id' => -1 * max(1, $stats['catalogo_areas_crear']),
                'id_direccion' => $idDireccion,
            ];
        }
    } elseif ($apply && $idDireccion > 0 && (int)($catalog['areas'][$areaKey]['id_direccion'] ?? 0) !== $idDireccion) {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_direcciones (id_direccion, id_departamento_organizacional, activo)
             VALUES (:id_direccion, :id_area, 1)
             ON DUPLICATE KEY UPDATE id_direccion = VALUES(id_direccion), activo = 1",
            [
                'id_direccion' => $idDireccion,
                'id_area' => (int)$catalog['areas'][$areaKey]['id'],
            ]
        );
        $catalog['areas'][$areaKey]['id_direccion'] = $idDireccion;
    }
    $idArea = (int)$catalog['areas'][$areaKey]['id'];

    $deptKey = catalog_key($pais, (string)$idArea, $departamento);
    if (!isset($catalog['departamentos'][$deptKey])) {
        $stats['catalogo_departamentos_crear']++;
        $plannedCatalogs['departamentos'][$deptKey] = $direccion . ' > ' . $area . ' > ' . $departamento;
        if ($apply) {
            $id = find_id_by_normalized_name(
                $db,
                "SELECT id, nombre
                   FROM __SPARTA_SECRET_REDACTED__.departamento
                  WHERE id_pais = :id_pais
                    AND id_departamento_organizacional = :id_area",
                ['id_pais' => $pais, 'id_area' => $idArea],
                $departamento
            );
            if ($id <= 0) {
                $db->CRUD(
                    "INSERT IGNORE INTO __SPARTA_SECRET_REDACTED__.departamento (nombre, activo, img_url, id_pais, id_departamento_organizacional)
                     VALUES (:nombre, 1, NULL, :id_pais, :id_area)",
                    ['nombre' => $departamento, 'id_pais' => $pais, 'id_area' => $idArea]
                );
                $id = find_id_by_normalized_name(
                    $db,
                    "SELECT id, nombre
                       FROM __SPARTA_SECRET_REDACTED__.departamento
                      WHERE id_pais = :id_pais
                        AND id_departamento_organizacional = :id_area",
                    ['id_pais' => $pais, 'id_area' => $idArea],
                    $departamento
                );
            }
            $catalog['departamentos'][$deptKey] = $id > 0 ? $id : $db->lastInsertId();
        } else {
            $catalog['departamentos'][$deptKey] = -1 * max(1, $stats['catalogo_departamentos_crear']);
        }
    }
    $idDepartamento = (int)$catalog['departamentos'][$deptKey];

    $puestoKey = $idDepartamento . '|' . norm_key($puesto);
    if (!isset($catalog['puestos'][$puestoKey])) {
        $stats['catalogo_puestos_crear']++;
        $plannedCatalogs['puestos'][$puestoKey] = $direccion . ' > ' . $area . ' > ' . $departamento . ' > ' . $puesto;
        if ($apply) {
            $id = find_id_by_normalized_name(
                $db,
                "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.puesto WHERE departamento_id = :id_departamento",
                ['id_departamento' => $idDepartamento],
                $puesto
            );
            if ($id <= 0) {
                $clave = unique_position_key($puesto, $idDepartamento, $catalog['clave_puestos']);
                $nivel = $positionLevels[norm_key($puesto)] ?? 0;
                $db->CRUD(
                    "INSERT INTO __SPARTA_SECRET_REDACTED__.puesto (clave, nombre, nivel, activo, departamento_id, es_jefe, descripcion)
                     VALUES (:clave, :nombre, :nivel, 1, :departamento_id, 1, NULL)",
                    [
                        'clave' => $clave,
                        'nombre' => $puesto,
                        'nivel' => $nivel,
                        'departamento_id' => $idDepartamento,
                    ]
                );
                $id = $db->lastInsertId();
            }
            $catalog['puestos'][$puestoKey] = $id;
        } else {
            $catalog['puestos'][$puestoKey] = -1 * max(1, $stats['catalogo_puestos_crear']);
        }
    }

    return [
        'id_direccion' => $idDireccion,
        'id_area' => $idArea,
        'id_departamento' => $idDepartamento,
        'id_puesto' => (int)$catalog['puestos'][$puestoKey],
    ];
}

function record_from_sheet(Worksheet $ws, array $header, string $sheetName, int $row, int $idPais): array
{
    $nombres = clean_text(col_value($ws, $header, $row, 'NOMBRE (S)'), 120);
    $apellidop = clean_text(col_value($ws, $header, $row, 'A. PATERNO'), 120);
    $apellidom = clean_text(col_value($ws, $header, $row, 'A. MATERNO'), 120);
    $nombreCompleto = clean_text(col_value($ws, $header, $row, 'NOMBRE/APELLIDOS'), 260);
    if ($nombreCompleto === '') {
        $nombreCompleto = trim($nombres . ' ' . $apellidop . ' ' . $apellidom);
    }

    return [
        'sheet' => $sheetName,
        'row' => $row,
        'id_pais' => $idPais,
        'nombres' => $nombres,
        'segundo_nombre' => '',
        'apellidop' => $apellidop,
        'apellidom' => $apellidom,
        'nombre_completo' => $nombreCompleto,
        'fecha_ingreso' => parse_date_value(col_value($ws, $header, $row, 'FECHA DE INGRESO')),
        'fecha_contpaq' => parse_date_value(col_value($ws, $header, $row, 'FECHA CONTPAC')),
        'fecha_imss_alta' => parse_date_value(col_value($ws, $header, $row, 'FECHA IMSS ALTA')),
        'puesto' => canonical_org_text(col_value($ws, $header, $row, 'PUESTO')),
        'departamento' => canonical_org_text(col_value($ws, $header, $row, 'DEPARTAMENTO')),
        'area' => canonical_org_text(col_value($ws, $header, $row, 'AREA')),
        'direccion' => canonical_org_text(col_value($ws, $header, $row, ['DIRECCION ORGANIZACIONAL', 'DIRECCION'])),
        'ubicacion_laboral' => clean_text(col_value($ws, $header, $row, 'UBICACION LABORAL'), 180),
        'municipio_laboral' => clean_text(col_value($ws, $header, $row, 'MUNICIPIO'), 180),
        'jefe_directo_texto' => clean_text(col_value($ws, $header, $row, 'JEFE DIRECTO'), 220),
        'sueldo_neto' => parse_decimal_value(col_value($ws, $header, $row, 'SUELDO NETO')),
        'sueldo_quincenal' => parse_decimal_value(col_value($ws, $header, $row, 'SUELDO QUINCENAL')),
        'sueldo_bruto' => parse_decimal_value(col_value($ws, $header, $row, ['SUELDO BRUTO', 'SUELDO BASE (BRUTO)'])),
        'salario_diario' => parse_decimal_value(col_value($ws, $header, $row, 'SALARIO DIARIO')),
        'sbc' => parse_decimal_value(col_value($ws, $header, $row, 'SBC')),
        'curp' => strtoupper(clean_text(col_value($ws, $header, $row, 'CURP'), 18)),
        'nss' => clean_text(col_value($ws, $header, $row, ['NSS', 'IGSS']), 20),
        'rfc' => strtoupper(clean_text(col_value($ws, $header, $row, ['RFC', 'RTU']), 20)),
        'entidad_federativa_rfc' => clean_text(col_value($ws, $header, $row, 'ENTIDAD FEDERATIVA / RFC'), 120),
        'codigo_postal' => clean_text(col_value($ws, $header, $row, 'CP'), 12),
        'fecha_nacimiento' => parse_date_value(col_value($ws, $header, $row, 'FECHA DE NACIMIENTO')),
        'sexo' => clean_text(col_value($ws, $header, $row, 'SEXO'), 20),
        'telefono' => clean_text(col_value($ws, $header, $row, 'NO. TELEFONICO'), 30),
        'correo' => clean_text(col_value($ws, $header, $row, 'CORREO ELECTRONICO'), 160),
        'domicilio' => clean_text(col_value($ws, $header, $row, 'DOMICILIO PARTICULAR'), 500),
        'registro_patronal' => clean_text(col_value($ws, $header, $row, 'REGISTRO PATRONAL'), 120),
        'codigo_contpaq' => clean_text(col_value($ws, $header, $row, 'CODIGO CONTPAC'), 80),
        'carta_no_credito' => clean_text(col_value($ws, $header, $row, 'CARTA DE "NO CREDITO"'), 120),
        'credito_infonavit_fonacot' => clean_text(col_value($ws, $header, $row, 'CREDITO INFONAVIT/ FONACOT'), 80),
        'no_credito' => clean_text(col_value($ws, $header, $row, 'NO. DE CREDITO'), 80),
        'monto_descontar' => parse_decimal_value(col_value($ws, $header, $row, 'MONTO A DESCONTAR')),
        'carta_no_nomina_bbva' => clean_text(col_value($ws, $header, $row, 'CARTA "NO NOMINA EN BBVA"'), 120),
        'nombre_banco' => clean_text(col_value($ws, $header, $row, 'NOMBRE DE BANCO'), 120),
        'numero_cuenta' => clean_text(col_value($ws, $header, $row, 'NO. CUENTA'), 40),
        'clabe' => clean_text(col_value($ws, $header, $row, 'CLABE INTERBANCARIA'), 30),
        'sueldo_bruto_letra' => clean_text(col_value($ws, $header, $row, 'SUELDO BRUTO-LETRA'), 255),
        'observaciones' => clean_text(col_value($ws, $header, $row, 'OBSERVACIONES'), 5000),
    ];
}

function date_parts(?string $date): array
{
    if (!$date) {
        return [null, null, null];
    }
    [$y, $m, $d] = array_map('intval', explode('-', $date));
    return [$y ?: null, $m ?: null, $d ?: null];
}

function apply_permissions_for_position(Database $db, int $idPersona, int $idPuesto): int
{
    $rows = $db->queryAll(
        "SELECT pp.modulo_web_id
           FROM __SPARTA_SECRET_REDACTED__.permisos_puesto pp
           INNER JOIN __SPARTA_SECRET_REDACTED__.modulos_web mw
                   ON mw.id = pp.modulo_web_id
                  AND COALESCE(mw.activo, 1) = 1
                  AND LOWER(TRIM(COALESCE(mw.pestana, ''))) <> 'permisos especiales'
          WHERE pp.id_puesto = :id_puesto
            AND pp.activo = 1",
        ['id_puesto' => $idPuesto]
    );

    $inserted = 0;
    foreach ($rows as $row) {
        $moduleId = (int)($row['modulo_web_id'] ?? 0);
        if ($moduleId <= 0) {
            continue;
        }
        $exists = $db->queryOne(
            "SELECT id FROM __SPARTA_SECRET_REDACTED__.asigna_modulo_web WHERE usuario_id = :id_persona AND modulo_web_id = :id_modulo LIMIT 1",
            ['id_persona' => $idPersona, 'id_modulo' => $moduleId]
        );
        if ($exists) {
            continue;
        }
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_modulo_web (usuario_id, modulo_web_id) VALUES (:id_persona, :id_modulo)",
            ['id_persona' => $idPersona, 'id_modulo' => $moduleId]
        );
        $inserted++;
    }

    return $inserted;
}

function insert_person(Database $db, array $record): int
{
    $fecha = CapHum::fechaHoraCdmx();
    $db->CRUD("
        INSERT INTO __SPARTA_SECRET_REDACTED__.persona
            (nombres, segundo_nombre, apellidop, apellidom, curp, numero_empleado, correo, telefono_uno, telefono_dos,
             estatus, user_name, password, fecha_ingreso, fecha_registro, id_pais, domicilio_calle_texto, codigo_postal)
        VALUES
            (:nombres, :segundo_nombre, :apellidop, :apellidom, :curp, :numero_empleado, :correo, :telefono_uno, '',
             'Activo', :user_name, :password, :fecha_ingreso, :fecha_registro, :id_pais, :domicilio, :codigo_postal)
    ", [
        'nombres' => $record['nombres'],
        'segundo_nombre' => $record['segundo_nombre'],
        'apellidop' => $record['apellidop'],
        'apellidom' => $record['apellidom'],
        'curp' => $record['curp'] !== '' ? $record['curp'] : null,
        'numero_empleado' => $record['numero_empleado'],
        'correo' => $record['correo'],
        'telefono_uno' => $record['telefono'],
        'user_name' => $record['usuario'],
        'password' => $record['password'],
        'fecha_ingreso' => $record['fecha_ingreso'],
        'fecha_registro' => $fecha,
        'id_pais' => $record['id_pais'],
        'domicilio' => $record['domicilio'],
        'codigo_postal' => $record['codigo_postal'],
    ]);

    $idPersona = $db->lastInsertId();
    if ($idPersona <= 0) {
        throw new RuntimeException('No se obtuvo id_persona para ' . $record['nombre_completo']);
    }

    $db->CRUD(
        "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto (id, id_persona, id_puesto, fecha_asignacion, activo)
         VALUES (DEFAULT, :id_persona, :id_puesto, :fecha, 1)",
        ['id_persona' => $idPersona, 'id_puesto' => $record['catalogo']['id_puesto'], 'fecha' => $fecha]
    );

    [$anio, $mes, $dia] = date_parts($record['fecha_nacimiento']);
    $db->CRUD("
        INSERT INTO __SPARTA_SECRET_REDACTED__.persona_datos_rrhh
            (id_persona, registro_patronal, codigo_contpaq, fecha_contpaq, fecha_imss_alta,
             id_departamento, id_area, id_puesto, id_jefe, puesto_texto, departamento_texto, area_texto,
             direccion_organizacional, ubicacion_laboral, municipio_laboral, jefe_directo_texto,
             sueldo_neto, sueldo_quincenal, sueldo_bruto, salario_diario, sbc,
             rfc, nss, entidad_federativa_rfc, anio, mes, dia, fecha_nacimiento, sexo,
             tipo_sangre, alergias, enfermedades_cronicas, enfermedades_hereditarias, medicamentos_actuales,
             discapacidad_condicion, observaciones_medicas, carta_no_credito, carta_no_nomina_bbva,
             sueldo_bruto_letra, observaciones)
        VALUES
            (:id_persona, :registro_patronal, :codigo_contpaq, :fecha_contpaq, :fecha_imss_alta,
             :id_departamento, :id_area, :id_puesto, NULL, :puesto_texto, :departamento_texto, :area_texto,
             :direccion_organizacional, :ubicacion_laboral, :municipio_laboral, :jefe_directo_texto,
             :sueldo_neto, :sueldo_quincenal, :sueldo_bruto, :salario_diario, :sbc,
             :rfc, :nss, :entidad_federativa_rfc, :anio, :mes, :dia, :fecha_nacimiento, :sexo,
             '', '', '', '', '', '', '', :carta_no_credito, :carta_no_nomina_bbva,
             :sueldo_bruto_letra, :observaciones)
    ", [
        'id_persona' => $idPersona,
        'registro_patronal' => $record['registro_patronal'],
        'codigo_contpaq' => $record['codigo_contpaq'],
        'fecha_contpaq' => $record['fecha_contpaq'],
        'fecha_imss_alta' => $record['fecha_imss_alta'],
        'id_departamento' => $record['catalogo']['id_departamento'],
        'id_area' => $record['catalogo']['id_area'],
        'id_puesto' => $record['catalogo']['id_puesto'],
        'puesto_texto' => $record['puesto'],
        'departamento_texto' => $record['departamento'],
        'area_texto' => $record['area'],
        'direccion_organizacional' => $record['direccion'],
        'ubicacion_laboral' => $record['ubicacion_laboral'],
        'municipio_laboral' => $record['municipio_laboral'],
        'jefe_directo_texto' => $record['jefe_directo_texto'],
        'sueldo_neto' => $record['sueldo_neto'],
        'sueldo_quincenal' => $record['sueldo_quincenal'],
        'sueldo_bruto' => $record['sueldo_bruto'],
        'salario_diario' => $record['salario_diario'],
        'sbc' => $record['sbc'],
        'rfc' => $record['rfc'],
        'nss' => $record['nss'],
        'entidad_federativa_rfc' => $record['entidad_federativa_rfc'],
        'anio' => $anio,
        'mes' => $mes,
        'dia' => $dia,
        'fecha_nacimiento' => $record['fecha_nacimiento'],
        'sexo' => $record['sexo'],
        'carta_no_credito' => $record['carta_no_credito'],
        'carta_no_nomina_bbva' => $record['carta_no_nomina_bbva'],
        'sueldo_bruto_letra' => $record['sueldo_bruto_letra'],
        'observaciones' => $record['observaciones'],
    ]);

    if ($record['telefono'] !== '') {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.telefonos_persona (id_persona, numero, tipo, estatus) VALUES (:id_persona, :numero, 'Personal', 'Activo')",
            ['id_persona' => $idPersona, 'numero' => $record['telefono']]
        );
    }
    if ($record['correo'] !== '') {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.correos_persona (id_persona, correo, tipo, estatus) VALUES (:id_persona, :correo, 'Personal', 'Activo')",
            ['id_persona' => $idPersona, 'correo' => $record['correo']]
        );
    }
    if ($record['domicilio'] !== '') {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.domicilio_persona (id_persona, domicilio_texto, codigo_postal, tipo, estatus)
             VALUES (:id_persona, :domicilio, :codigo_postal, 'Particular', 'Activo')",
            ['id_persona' => $idPersona, 'domicilio' => $record['domicilio'], 'codigo_postal' => $record['codigo_postal']]
        );
    }
    if ($record['clabe'] !== '' || $record['numero_cuenta'] !== '' || $record['nombre_banco'] !== '') {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.persona_cuenta_bancaria (id_persona, clabe, numero_cuenta, id_banco, nombre_banco, estatus)
             VALUES (:id_persona, :clabe, :numero_cuenta, NULL, :nombre_banco, 'Activo')",
            [
                'id_persona' => $idPersona,
                'clabe' => $record['clabe'],
                'numero_cuenta' => $record['numero_cuenta'],
                'nombre_banco' => $record['nombre_banco'],
            ]
        );
    }
    if ($record['credito_infonavit_fonacot'] !== '' || $record['no_credito'] !== '' || $record['monto_descontar'] !== null) {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.persona_credito_laboral (id_persona, tipo_credito, numero_credito, monto_descontar, estatus)
             VALUES (:id_persona, :tipo_credito, :numero_credito, :monto_descontar, 'Activo')",
            [
                'id_persona' => $idPersona,
                'tipo_credito' => $record['credito_infonavit_fonacot'],
                'numero_credito' => $record['no_credito'],
                'monto_descontar' => $record['monto_descontar'],
            ]
        );
    }

    apply_permissions_for_position($db, $idPersona, (int)$record['catalogo']['id_puesto']);
    CapHum::registrarCambiosTrayectoriaPuestos(
        $db,
        $idPersona,
        [],
        CapHum::puestosActivosTrayectoria($db, $idPersona),
        null,
        'importacion_control_empleados_2026'
    );

    return $idPersona;
}

$path = '';
$apply = false;
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif ($path === '') {
        $path = $arg;
    }
}

if ($path === '' || !is_readable($path)) {
    usage();
    exit(1);
}

$stats = [
    'filas_activas_leidas' => 0,
    'omitidas_incompletas' => 0,
    'omitidas_existentes' => 0,
    'omitidas_ambiguas' => 0,
    'listas_para_insertar' => 0,
    'insertadas' => 0,
    'jefes_resueltos' => 0,
    'jefes_no_resueltos' => 0,
    'catalogo_direcciones_crear' => 0,
    'catalogo_areas_crear' => 0,
    'catalogo_departamentos_crear' => 0,
    'catalogo_puestos_crear' => 0,
];
$issues = [];
$plannedCatalogs = [
    'direcciones' => [],
    'areas' => [],
    'departamentos' => [],
    'puestos' => [],
];

$db = new Database();
if ($apply) {
    CapHumRrhh::asegurarTablas($db);
}

$idMexico = pais_id($db, 'Mexico');
$idGuatemala = pais_id($db, 'Guatemala');
$existing = load_existing_people($db);
$catalog = load_catalog($db);
$planningCatalog = $catalog;

$reader = IOFactory::createReaderForFile($path);
$reader->setReadDataOnly(true);
$reader->setReadFilter(new ControlEmpleadosReadFilter());
$reader->setLoadSheetsOnly(['ACTIVOS MAXI 2026', 'ACTIVOS GUATEMALA 2026', 'ACTIVOS FURIA 26', 'catálogo']);
$spreadsheet = $reader->load($path);
$positionLevels = load_position_levels($spreadsheet);

$sheets = [
    ['name' => 'ACTIVOS MAXI 2026', 'header' => 11, 'pais' => $idMexico],
    ['name' => 'ACTIVOS GUATEMALA 2026', 'header' => 12, 'pais' => $idGuatemala],
    ['name' => 'ACTIVOS FURIA 26', 'header' => 11, 'pais' => $idMexico],
];

$records = [];
foreach ($sheets as $sheetCfg) {
    $ws = $spreadsheet->getSheetByName($sheetCfg['name']);
    if (!$ws) {
        $issues[] = 'No se encontro la hoja ' . $sheetCfg['name'];
        continue;
    }

    $header = header_map($ws, (int)$sheetCfg['header']);
    for ($row = (int)$sheetCfg['header'] + 1; $row <= $ws->getHighestDataRow(); $row++) {
        $record = record_from_sheet($ws, $header, $sheetCfg['name'], $row, (int)$sheetCfg['pais']);
        if ($record['nombre_completo'] === '' && $record['puesto'] === '' && $record['departamento'] === '' && $record['area'] === '') {
            continue;
        }

        $stats['filas_activas_leidas']++;
        $missing = [];
        foreach (['nombre_completo', 'nombres', 'apellidop', 'puesto', 'departamento', 'area', 'direccion'] as $field) {
            if (($record[$field] ?? '') === '') {
                $missing[] = $field;
            }
        }
        if ($missing) {
            $stats['omitidas_incompletas']++;
            $issues[] = sprintf(
                'Fila incompleta: %s fila %d (%s).',
                $record['sheet'],
                $record['row'],
                implode(', ', $missing)
            );
            continue;
        }

        [$existingId, $existingReason] = find_existing_id($record, $existing);
        if ($existingId !== 0) {
            $stats['omitidas_existentes']++;
            continue;
        }
        if ($existingReason !== '') {
            $stats['omitidas_ambiguas']++;
            $issues[] = sprintf(
                'Coincidencia ambigua: %s fila %d - %s (%s).',
                $record['sheet'],
                $record['row'],
                $record['nombre_completo'],
                $existingReason
            );
            continue;
        }

        $record['catalogo'] = ensure_catalog($db, $record, false, $planningCatalog, $stats, $plannedCatalogs, $positionLevels);
        $record['usuario'] = unique_username(username_base($record['correo'], $record['nombres'], $record['apellidop']), $existing['usernames']);
        $record['numero_empleado'] = unique_employee_number($record['codigo_contpaq'], $existing['employee_numbers'], $existing['max_employee_number']);
        $record['password'] = getenv('TI_COMERCIAL_DEFAULT_PASSWORD') ?: '';
        $records[] = $record;
        $stats['listas_para_insertar']++;

        put_unique_map($existing['by_name_country'], (int)$record['id_pais'] . '|' . norm_key($record['nombre_completo']), -1 * count($records));
        if ($record['curp'] !== '') {
            put_unique_map($existing['by_curp'], norm_key($record['curp']), -1 * count($records));
        }
        if ($record['rfc'] !== '') {
            put_unique_map($existing['by_rfc'], norm_key($record['rfc']), -1 * count($records));
        }
        if ($record['nss'] !== '') {
            put_unique_map($existing['by_nss'], norm_key($record['nss']), -1 * count($records));
        }
        if ($record['correo'] !== '') {
            put_unique_map($existing['by_email'], strtolower($record['correo']), -1 * count($records));
        }
        if ($record['codigo_contpaq'] !== '') {
            put_unique_map($existing['by_contpaq_country'], (int)$record['id_pais'] . '|' . $record['codigo_contpaq'], -1 * count($records));
        }
    }
}

echo 'Modo: ' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;
echo json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
if ($issues) {
    echo 'Observaciones:' . PHP_EOL;
    foreach (array_slice($issues, 0, 40) as $issue) {
        echo '- ' . $issue . PHP_EOL;
    }
    if (count($issues) > 40) {
        echo '- ... ' . (count($issues) - 40) . ' mas' . PHP_EOL;
    }
}
foreach ($plannedCatalogs as $tipo => $items) {
    if (!$items) {
        continue;
    }
    echo 'Catalogo ' . $tipo . ' por crear:' . PHP_EOL;
    foreach (array_slice(array_values($items), 0, 40) as $item) {
        echo '- ' . $item . PHP_EOL;
    }
    if (count($items) > 40) {
        echo '- ... ' . (count($items) - 40) . ' mas' . PHP_EOL;
    }
}

if (!$apply) {
    exit(0);
}

$insertedByNameCountry = [];
$catalogApply = load_catalog($db);
$applyCatalogStats = [
    'catalogo_direcciones_crear' => 0,
    'catalogo_areas_crear' => 0,
    'catalogo_departamentos_crear' => 0,
    'catalogo_puestos_crear' => 0,
];
$applyPlannedCatalogs = [
    'direcciones' => [],
    'areas' => [],
    'departamentos' => [],
    'puestos' => [],
];
$db->beginTransaction();
try {
    foreach ($records as $idx => $record) {
        $record['catalogo'] = ensure_catalog(
            $db,
            $record,
            true,
            $catalogApply,
            $applyCatalogStats,
            $applyPlannedCatalogs,
            $positionLevels
        );
        $idPersona = insert_person($db, $record);
        $records[$idx]['id_persona_insertada'] = $idPersona;
        $insertedByNameCountry[(int)$record['id_pais'] . '|' . norm_key($record['nombre_completo'])] = $idPersona;
        $stats['insertadas']++;
    }

    $personNameMap = $existing['by_name_country'];
    foreach ($insertedByNameCountry as $key => $idPersona) {
        $personNameMap[$key] = $idPersona;
    }

    foreach ($records as $record) {
        $idPersona = (int)($record['id_persona_insertada'] ?? 0);
        $jefeText = $record['jefe_directo_texto'];
        if ($idPersona <= 0 || $jefeText === '') {
            continue;
        }
        $idJefe = (int)($personNameMap[(int)$record['id_pais'] . '|' . norm_key($jefeText)] ?? 0);
        if ($idJefe <= 0 || $idJefe === $idPersona) {
            $stats['jefes_no_resueltos']++;
            continue;
        }

        $fecha = CapHum::fechaHoraCdmx();
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_jefe (id, id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin)
             VALUES (DEFAULT, :id_persona, :id_jefe, NULL, :fecha, :fecha)",
            ['id_persona' => $idPersona, 'id_jefe' => $idJefe, 'fecha' => $fecha]
        );
        $db->CRUD(
            "UPDATE __SPARTA_SECRET_REDACTED__.persona_datos_rrhh SET id_jefe = :id_jefe WHERE id_persona = :id_persona",
            ['id_persona' => $idPersona, 'id_jefe' => $idJefe]
        );
        $stats['jefes_resueltos']++;
    }

    $db->commit();
    echo 'Importacion completada.' . PHP_EOL;
    echo json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, 'Error en importacion, transaccion revertida: ' . $e->getMessage() . PHP_EOL);
    exit(2);
}
