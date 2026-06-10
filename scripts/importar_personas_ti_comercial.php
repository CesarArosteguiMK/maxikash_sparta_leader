<?php

declare(strict_types=1);

use Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

function usage(): void
{
    fwrite(STDERR, "Uso: php scripts/importar_personas_ti_comercial.php <archivo.xlsx> [--apply]\n");
}

function text_value(mixed $value): string
{
    if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
        $value = $value->getPlainText();
    }
    $text = trim((string) ($value ?? ''));
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
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function username_base(string $correo, string $nombre, string $apellidop): string
{
    $base = '';
    if ($correo !== '' && str_contains($correo, '@')) {
        $base = substr($correo, 0, strpos($correo, '@'));
    }
    if ($base === '') {
        $base = trim($nombre . ' ' . $apellidop);
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
    for ($i = 2; $i < 10000; $i++) {
        $suffix = (string) $i;
        $candidate = substr($base, 0, 40 - strlen($suffix)) . $suffix;
        if (!isset($used[$candidate])) {
            $used[$candidate] = true;
            return $candidate;
        }
    }
    throw new RuntimeException('No se pudo generar un usuario unico.');
}

function unique_employee_number(string $preferred, array &$used, int &$max): string
{
    $preferred = preg_replace('/\D+/', '', $preferred) ?? '';
    if ($preferred !== '' && !isset($used[$preferred])) {
        $used[$preferred] = true;
        return $preferred;
    }
    do {
        $max++;
        $candidate = (string) $max;
    } while (isset($used[$candidate]));
    $used[$candidate] = true;
    return $candidate;
}

function parse_date(mixed $value): ?string
{
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    if (is_int($value) || is_float($value)) {
        try {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }
    $text = text_value($value);
    if ($text === '') {
        return null;
    }
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $text, $m)) {
        return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
    }
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $text, $m)) {
        return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
    }
    $ts = strtotime($text);
    return $ts ? date('Y-m-d', $ts) : null;
}

function find_header(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, array $wanted): array
{
    $wanted = array_fill_keys(array_map('norm_key', $wanted), true);
    $maxCol = Coordinate::columnIndexFromString($ws->getHighestColumn());
    for ($row = 1; $row <= min(30, $ws->getHighestDataRow()); $row++) {
        $map = [];
        $hits = 0;
        for ($col = 1; $col <= $maxCol; $col++) {
            $key = norm_key($ws->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue());
            if ($key === '') {
                continue;
            }
            $map[$key] = $col;
            if (isset($wanted[$key])) {
                $hits++;
            }
        }
        if ($hits >= 5) {
            return [$row, $map];
        }
    }
    return [0, []];
}

function col_value(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, array $header, int $row, string $name): mixed
{
    $key = norm_key($name);
    if (!isset($header[$key])) {
        return null;
    }
    return $ws->getCell(Coordinate::stringFromColumnIndex($header[$key]) . $row)->getValue();
}

function pais_id(Database $db, string $pais): int
{
    $paisKey = norm_key($pais);
    try {
        $rows = $db->queryAll('SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.paises WHERE activo = 1 OR activo IS NULL');
    } catch (Throwable) {
        $rows = $db->queryAll('SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.paises');
    }
    foreach ($rows as $row) {
        if (norm_key($row['nombre'] ?? '') === $paisKey) {
            return (int) $row['id'];
        }
    }
    foreach ($rows as $row) {
        if (norm_key($row['nombre'] ?? '') === 'MEXICO') {
            return (int) $row['id'];
        }
    }
    return 1;
}

function load_catalog(Database $db): array
{
    $rows = $db->queryAll("
        SELECT
            dorg.id AS id_direccion,
            dorg.nombre AS direccion,
            area.id AS id_area,
            area.nombre AS area,
            depto.id AS id_departamento,
            depto.nombre AS departamento,
            puesto.id AS id_puesto,
            puesto.nombre AS puesto
        FROM __SPARTA_SECRET_REDACTED__.puesto puesto
        INNER JOIN __SPARTA_SECRET_REDACTED__.departamento depto
                ON depto.id = puesto.departamento_id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento_organizacional area
               ON area.id = depto.id_departamento_organizacional
        LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_direcciones ad
               ON ad.id_departamento_organizacional = area.id
              AND COALESCE(ad.activo, 1) = 1
        LEFT JOIN __SPARTA_SECRET_REDACTED__.direcciones_organizacion dorg
               ON dorg.id = ad.id_direccion
        WHERE COALESCE(puesto.activo, 1) = 1
          AND COALESCE(depto.activo, 1) = 1
    ");

    $full = [];
    $area = [];
    $dept = [];
    foreach ($rows as $row) {
        $record = [
            'id_direccion' => isset($row['id_direccion']) ? (int) $row['id_direccion'] : null,
            'id_area' => isset($row['id_area']) ? (int) $row['id_area'] : null,
            'id_departamento' => (int) $row['id_departamento'],
            'id_puesto' => (int) $row['id_puesto'],
        ];
        $kFull = norm_key($row['direccion']) . '|' . norm_key($row['area']) . '|' . norm_key($row['departamento']) . '|' . norm_key($row['puesto']);
        $kArea = norm_key($row['area']) . '|' . norm_key($row['departamento']) . '|' . norm_key($row['puesto']);
        $kDept = norm_key($row['departamento']) . '|' . norm_key($row['puesto']);
        $full[$kFull] = $record;
        $area[$kArea] = $record;
        $dept[$kDept][] = $record;
    }
    return [$full, $area, $dept];
}

function resolve_catalog(array $catalog, string $direccion, string $area, string $departamento, string $puesto): ?array
{
    [$full, $areaMap, $deptMap] = $catalog;
    $kFull = norm_key($direccion) . '|' . norm_key($area) . '|' . norm_key($departamento) . '|' . norm_key($puesto);
    if (isset($full[$kFull])) {
        return $full[$kFull];
    }
    $kArea = norm_key($area) . '|' . norm_key($departamento) . '|' . norm_key($puesto);
    if (isset($areaMap[$kArea])) {
        return $areaMap[$kArea];
    }
    $kDept = norm_key($departamento) . '|' . norm_key($puesto);
    if (isset($deptMap[$kDept]) && count($deptMap[$kDept]) === 1) {
        return $deptMap[$kDept][0];
    }
    return null;
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

$db = new Database();
$catalog = load_catalog($db);

$existingPeople = $db->queryAll("
    SELECT id, curp, correo, user_name, numero_empleado,
           CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) AS nombre_completo
    FROM __SPARTA_SECRET_REDACTED__.persona
");
$curps = [];
$emails = [];
$names = [];
$usernames = [];
$employeeNumbers = [];
$maxEmployeeNumber = 0;
$personByName = [];
foreach ($existingPeople as $p) {
    if (norm_key($p['curp'] ?? '') !== '') {
        $curps[norm_key($p['curp'])] = true;
    }
    if (norm_key($p['correo'] ?? '') !== '') {
        $emails[norm_key($p['correo'])] = true;
    }
    if (norm_key($p['nombre_completo'] ?? '') !== '') {
        $names[norm_key($p['nombre_completo'])] = true;
        $personByName[norm_key($p['nombre_completo'])] = (int) $p['id'];
    }
    if (text_value($p['user_name'] ?? '') !== '') {
        $usernames[text_value($p['user_name'])] = true;
    }
    $employeeNumber = preg_replace('/\D+/', '', text_value($p['numero_empleado'] ?? '')) ?? '';
    if ($employeeNumber !== '') {
        $employeeNumbers[$employeeNumber] = true;
        $maxEmployeeNumber = max($maxEmployeeNumber, (int) $employeeNumber);
    }
}

$sheets = ['ACTIVOS MAXI 2026', 'ACTIVOS GUATEMALA 2026', 'ACTIVOS FURIA 26'];
$headers = [
    'NOMBRE (S)', 'A. PATERNO', 'A. MATERNO', 'NOMBRE/APELLIDOS', 'PUESTO',
    'DEPARTAMENTO', 'AREA', 'DIRECCION ORGANIZACIONAL', 'JEFE DIRECTO', 'CURP',
    'NSS', 'RFC', 'CP', 'FECHA DE NACIMIENTO', 'SEXO', 'NO. TELEFONICO',
    'CORREO ELECTRONICO', 'DOMICILIO PARTICULAR', 'FECHA DE INGRESO',
    'REGISTRO PATRONAL', 'CODIGO CONTPAC', 'UBICACION LABORAL', 'MUNICIPIO',
];

$rawRows = [];
if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'json') {
    $json = file_get_contents($path);
    $decoded = json_decode($json ?: '', true);
    if (!is_array($decoded)) {
        fwrite(STDERR, "El JSON temporal no tiene el formato esperado.\n");
        exit(1);
    }
    $rawRows = $decoded;
} else {
    $reader = IOFactory::createReaderForFile($path);
    $reader->setReadDataOnly(true);
    if (method_exists($reader, 'setLoadSheetsOnly')) {
        $reader->setLoadSheetsOnly($sheets);
    }
    $spreadsheet = $reader->load($path);
    foreach ($sheets as $sheetName) {
        $ws = $spreadsheet->getSheetByName($sheetName);
        if (!$ws) {
            continue;
        }
        [$headerRow, $header] = find_header($ws, $headers);
        if (!$headerRow) {
            $rawRows[] = ['_issue' => "No se encontro encabezado en {$sheetName}."];
            continue;
        }
        for ($row = $headerRow + 1; $row <= $ws->getHighestDataRow(); $row++) {
            $rawRows[] = [
                'sheet' => $sheetName,
                'row' => $row,
                'nombres' => text_value(col_value($ws, $header, $row, 'NOMBRE (S)')),
                'apellidop' => text_value(col_value($ws, $header, $row, 'A. PATERNO')),
                'apellidom' => text_value(col_value($ws, $header, $row, 'A. MATERNO')),
                'nombre_completo' => text_value(col_value($ws, $header, $row, 'NOMBRE/APELLIDOS')),
                'puesto' => text_value(col_value($ws, $header, $row, 'PUESTO')),
                'departamento' => text_value(col_value($ws, $header, $row, 'DEPARTAMENTO')),
                'area' => text_value(col_value($ws, $header, $row, 'AREA')),
                'direccion' => text_value(col_value($ws, $header, $row, 'DIRECCION ORGANIZACIONAL')),
                'jefe_directo_texto' => text_value(col_value($ws, $header, $row, 'JEFE DIRECTO')),
                'curp' => text_value(col_value($ws, $header, $row, 'CURP')),
                'nss' => text_value(col_value($ws, $header, $row, 'NSS')),
                'rfc' => text_value(col_value($ws, $header, $row, 'RFC')),
                'cp' => text_value(col_value($ws, $header, $row, 'CP')),
                'fecha_nacimiento' => parse_date(col_value($ws, $header, $row, 'FECHA DE NACIMIENTO')),
                'sexo' => text_value(col_value($ws, $header, $row, 'SEXO')),
                'telefono' => text_value(col_value($ws, $header, $row, 'NO. TELEFONICO')),
                'correo' => text_value(col_value($ws, $header, $row, 'CORREO ELECTRONICO')),
                'domicilio' => text_value(col_value($ws, $header, $row, 'DOMICILIO PARTICULAR')),
                'fecha_ingreso' => parse_date(col_value($ws, $header, $row, 'FECHA DE INGRESO')),
                'registro_patronal' => text_value(col_value($ws, $header, $row, 'REGISTRO PATRONAL')),
                'codigo_contpaq' => text_value(col_value($ws, $header, $row, 'CODIGO CONTPAC')),
                'ubicacion_laboral' => text_value(col_value($ws, $header, $row, 'UBICACION LABORAL')),
                'municipio_laboral' => text_value(col_value($ws, $header, $row, 'MUNICIPIO')),
            ];
        }
    }
}

$candidates = [];
$stats = [
    'leidos' => 0,
    'omitidos_fuera_area' => 0,
    'omitidos_catalogo' => 0,
    'omitidos_duplicado' => 0,
    'insertables' => 0,
    'insertados' => 0,
];
$issues = [];

foreach ($rawRows as $raw) {
        if (isset($raw['_issue'])) {
            $issues[] = (string) $raw['_issue'];
            continue;
        }
        $sheetName = text_value($raw['sheet'] ?? '');
        $row = (int) ($raw['row'] ?? 0);
        $area = text_value($raw['area'] ?? '');
        $areaKey = norm_key($area);
        if (!in_array($areaKey, ['TI', 'COMERCIAL'], true)) {
            $stats['omitidos_fuera_area']++;
            continue;
        }

        $stats['leidos']++;
        $nombre = text_value($raw['nombres'] ?? '');
        $apellidop = text_value($raw['apellidop'] ?? '');
        $apellidom = text_value($raw['apellidom'] ?? '');
        $nombreCompleto = text_value($raw['nombre_completo'] ?? '');
        if ($nombreCompleto === '') {
            $nombreCompleto = trim($nombre . ' ' . $apellidop . ' ' . $apellidom);
        }
        if ($nombre === '' || $apellidop === '') {
            $parts = preg_split('/\s+/', $nombreCompleto) ?: [];
            if ($nombre === '' && count($parts) > 2) {
                $nombre = implode(' ', array_slice($parts, 0, -2));
            }
            if ($apellidop === '' && count($parts) > 1) {
                $apellidop = $parts[count($parts) - 2];
            }
            if ($apellidom === '' && count($parts) > 0) {
                $apellidom = $parts[count($parts) - 1];
            }
        }
        if (norm_key($nombreCompleto) === '') {
            continue;
        }

        $direccion = text_value($raw['direccion'] ?? '');
        $departamento = text_value($raw['departamento'] ?? '');
        $puesto = text_value($raw['puesto'] ?? '');
        $match = resolve_catalog($catalog, $direccion, $area, $departamento, $puesto);
        if (!$match) {
            $stats['omitidos_catalogo']++;
            $issues[] = "Sin catalogo: {$sheetName} fila {$row} - {$nombreCompleto} ({$direccion} > {$area} > {$departamento} > {$puesto})";
            continue;
        }

        $curp = strtoupper(str_replace(' ', '', text_value($raw['curp'] ?? '')));
        $correo = text_value($raw['correo'] ?? '');
        $nameKey = norm_key($nombreCompleto);
        $duplicate = ($curp !== '' && isset($curps[norm_key($curp)]))
            || ($correo !== '' && isset($emails[norm_key($correo)]))
            || isset($names[$nameKey]);
        if ($duplicate) {
            $stats['omitidos_duplicado']++;
            continue;
        }

        $usuario = unique_username(username_base($correo, $nombre, $apellidop), $usernames);
        $candidate = [
            'sheet' => $sheetName,
            'row' => $row,
            'nombres' => $nombre,
            'segundo_nombre' => '',
            'apellidop' => $apellidop,
            'apellidom' => $apellidom,
            'nombre_completo' => $nombreCompleto,
            'curp' => $curp !== '' ? substr($curp, 0, 18) : null,
            'nss' => text_value($raw['nss'] ?? ''),
            'rfc' => text_value($raw['rfc'] ?? ''),
            'cp' => text_value($raw['cp'] ?? ''),
            'sexo' => text_value($raw['sexo'] ?? ''),
            'telefono' => preg_replace('/\D+/', '', text_value($raw['telefono'] ?? '')) ?: '',
            'correo' => $correo,
            'domicilio' => text_value($raw['domicilio'] ?? ''),
            'fecha_ingreso' => parse_date($raw['fecha_ingreso'] ?? null),
            'fecha_nacimiento' => parse_date($raw['fecha_nacimiento'] ?? null),
            'registro_patronal' => text_value($raw['registro_patronal'] ?? ''),
            'codigo_contpaq' => text_value($raw['codigo_contpaq'] ?? ''),
            'direccion' => $direccion,
            'area' => $area,
            'departamento' => $departamento,
            'puesto' => $puesto,
            'ubicacion_laboral' => text_value($raw['ubicacion_laboral'] ?? ''),
            'municipio_laboral' => text_value($raw['municipio_laboral'] ?? ''),
            'jefe_directo_texto' => text_value($raw['jefe_directo_texto'] ?? ''),
            'usuario' => $usuario,
            'numero_empleado' => unique_employee_number(text_value($raw['codigo_contpaq'] ?? ''), $employeeNumbers, $maxEmployeeNumber),
            'id_pais' => str_contains($sheetName, 'GUATEMALA') ? pais_id($db, 'Guatemala') : pais_id($db, 'Mexico'),
            'catalogo' => $match,
        ];
        $candidates[] = $candidate;
        $curps[norm_key($candidate['curp'])] = true;
        if ($candidate['correo'] !== '') {
            $emails[norm_key($candidate['correo'])] = true;
        }
        $names[$nameKey] = true;
        $stats['insertables']++;
}

echo "Modo: " . ($apply ? "APPLY" : "DRY-RUN") . PHP_EOL;
echo json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
if ($issues) {
    echo "Problemas detectados:" . PHP_EOL;
    foreach (array_slice($issues, 0, 20) as $issue) {
        echo "- {$issue}" . PHP_EOL;
    }
    if (count($issues) > 20) {
        echo "- ... " . (count($issues) - 20) . " mas" . PHP_EOL;
    }
}

if (!$apply) {
    exit($stats['insertables'] > 0 ? 0 : 2);
}

if ($stats['insertables'] !== 89) {
    fwrite(STDERR, "Abortado: se esperaban 89 insertables y se detectaron {$stats['insertables']}.\n");
    exit(3);
}

$password = '__SPARTA_PASSWORD_REDACTED__';
$insertedByName = [];
$db->beginTransaction();
try {
    foreach ($candidates as $c) {
        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.persona
                (nombres, segundo_nombre, apellidop, apellidom, numero_empleado, correo, telefono_uno, telefono_dos,
                 estatus, user_name, password, fecha_ingreso, fecha_registro, id_pais, domicilio_calle_texto, codigo_postal, curp)
            VALUES
                (:nombres, :segundo_nombre, :apellidop, :apellidom, :numero_empleado, :correo, :telefono_uno, '',
                 'Activo', :user_name, :password, :fecha_ingreso, NOW(), :id_pais, :domicilio_calle_texto, :codigo_postal, :curp)
        ", [
            'nombres' => $c['nombres'],
            'segundo_nombre' => $c['segundo_nombre'],
            'apellidop' => $c['apellidop'],
            'apellidom' => $c['apellidom'],
            'numero_empleado' => $c['numero_empleado'],
            'correo' => $c['correo'],
            'telefono_uno' => $c['telefono'],
            'user_name' => $c['usuario'],
            'password' => $password,
            'fecha_ingreso' => $c['fecha_ingreso'],
            'id_pais' => $c['id_pais'],
            'domicilio_calle_texto' => $c['domicilio'],
            'codigo_postal' => $c['cp'],
            'curp' => $c['curp'],
        ]);
        $idPersona = $db->lastInsertId();
        if ($idPersona <= 0) {
            throw new RuntimeException('No se obtuvo id_persona para ' . $c['nombre_completo']);
        }
        $insertedByName[norm_key($c['nombre_completo'])] = $idPersona;
        $personByName[norm_key($c['nombre_completo'])] = $idPersona;

        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto (id, id_persona, id_puesto, fecha_asignacion, activo)
            VALUES (DEFAULT, :id_persona, :id_puesto, NOW(), 1)
        ", [
            'id_persona' => $idPersona,
            'id_puesto' => $c['catalogo']['id_puesto'],
        ]);

        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.persona_datos_rrhh
                (id_persona, registro_patronal, codigo_contpaq, fecha_imss_alta, id_departamento, id_area,
                 id_puesto, puesto_texto, departamento_texto, area_texto, direccion_organizacional,
                 ubicacion_laboral, municipio_laboral, jefe_directo_texto, rfc, nss, fecha_nacimiento, sexo,
                 tipo_sangre, alergias, enfermedades_cronicas, enfermedades_hereditarias, medicamentos_actuales,
                 discapacidad_condicion, observaciones_medicas, observaciones)
            VALUES
                (:id_persona, :registro_patronal, :codigo_contpaq, NULL, :id_departamento, :id_area,
                 :id_puesto, :puesto_texto, :departamento_texto, :area_texto, :direccion_organizacional,
                 :ubicacion_laboral, :municipio_laboral, :jefe_directo_texto, :rfc, :nss, :fecha_nacimiento, :sexo,
                 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', :observaciones)
        ", [
            'id_persona' => $idPersona,
            'registro_patronal' => $c['registro_patronal'],
            'codigo_contpaq' => $c['codigo_contpaq'],
            'id_departamento' => $c['catalogo']['id_departamento'],
            'id_area' => $c['catalogo']['id_area'],
            'id_puesto' => $c['catalogo']['id_puesto'],
            'puesto_texto' => $c['puesto'],
            'departamento_texto' => $c['departamento'],
            'area_texto' => $c['area'],
            'direccion_organizacional' => $c['direccion'],
            'ubicacion_laboral' => $c['ubicacion_laboral'],
            'municipio_laboral' => $c['municipio_laboral'],
            'jefe_directo_texto' => $c['jefe_directo_texto'],
            'rfc' => $c['rfc'],
            'nss' => $c['nss'],
            'fecha_nacimiento' => $c['fecha_nacimiento'],
            'sexo' => $c['sexo'],
            'observaciones' => 'Importado desde 2026-CONTROL DE EMPLEADOS (2).xlsx',
        ]);
        $stats['insertados']++;
    }

    foreach ($candidates as $c) {
        $idPersona = $insertedByName[norm_key($c['nombre_completo'])] ?? 0;
        $idJefe = $personByName[norm_key($c['jefe_directo_texto'])] ?? 0;
        if ($idPersona > 0 && $idJefe > 0 && $idPersona !== $idJefe) {
            $db->CRUD("
                INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_jefe (id, id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin)
                VALUES (DEFAULT, :id_persona, :id_jefe, NULL, NOW(), NOW())
            ", [
                'id_persona' => $idPersona,
                'id_jefe' => $idJefe,
            ]);
            $db->CRUD("
                UPDATE __SPARTA_SECRET_REDACTED__.persona_datos_rrhh
                   SET id_jefe = :id_jefe
                 WHERE id_persona = :id_persona
            ", [
                'id_persona' => $idPersona,
                'id_jefe' => $idJefe,
            ]);
        }
    }

    $db->commit();
    echo "Importacion completada. Insertados: {$stats['insertados']}" . PHP_EOL;
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, "Error en importacion, transaccion revertida: " . $e->getMessage() . PHP_EOL);
    exit(4);
}
