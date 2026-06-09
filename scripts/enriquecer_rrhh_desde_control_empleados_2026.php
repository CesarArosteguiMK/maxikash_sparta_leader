<?php

declare(strict_types=1);

use Core\Database;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Models\CapHum;
use Models\CapHumRrhh;

ini_set('memory_limit', '1536M');
set_time_limit(0);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';
require dirname(__DIR__) . '/backend/core/Model.php';
require dirname(__DIR__) . '/backend/models/CapHum.php';
require dirname(__DIR__) . '/backend/models/CapHumRrhh.php';

final class EnriquecerRrhhReadFilter implements IReadFilter
{
    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return Coordinate::columnIndexFromString((string)$columnAddress) <= 61;
    }
}

function txt(mixed $value): string
{
    if ($value instanceof RichText) {
        $value = $value->getPlainText();
    }
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }
    $text = trim((string)($value ?? ''));
    if (str_starts_with($text, '=')) {
        return '';
    }
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function keyn(mixed $value): string
{
    $text = txt($value);
    if ($text === '') return '';
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtoupper($text);
    $text = str_replace('&', 'Y', $text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function clean(mixed $value, int $max = 255): string
{
    $text = txt($value);
    $key = keyn($text);
    if ($key === '' || in_array($key, ['N A', 'NA', 'S N', 'SN', 'NO APLICA', 'NULL', 'NONE'], true)) {
        return '';
    }
    return mb_substr($text, 0, $max);
}

function employee_key(mixed $value): string
{
    $key = keyn($value);
    if ($key !== '' && ctype_digit($key)) {
        return ltrim($key, '0') ?: '0';
    }
    return $key;
}

function org_text(mixed $value): string
{
    $text = clean($value, 180);
    return match (keyn($text)) {
        'COBRANZA COPORATIVO' => 'COBRANZA CORPORATIVO',
        default => $text,
    };
}

function fecha(mixed $value): ?string
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
    $text = clean($value, 40);
    if ($text === '') return null;
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $text, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
    }
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2}|\d{4})$/', $text, $m)) {
        $year = (int)$m[3];
        if ($year < 100) $year += $year > 40 ? 1900 : 2000;
        return sprintf('%04d-%02d-%02d', $year, (int)$m[2], (int)$m[1]);
    }
    $ts = strtotime($text);
    return $ts ? date('Y-m-d', $ts) : null;
}

function decimal(mixed $value): ?float
{
    if (is_int($value) || is_float($value)) return (float)$value;
    $text = clean($value, 40);
    if ($text === '') return null;
    $text = str_replace([',', '$', 'Q', 'q', ' '], '', $text);
    $text = preg_replace('/[^0-9.\-]/', '', $text) ?? '';
    return $text === '' ? null : (float)$text;
}

function header_map(Worksheet $ws, int $row): array
{
    $map = [];
    $max = Coordinate::columnIndexFromString($ws->getHighestColumn());
    for ($col = 1; $col <= $max; $col++) {
        $key = keyn($ws->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue());
        if ($key !== '') $map[$key] = $col;
    }
    return $map;
}

function val(Worksheet $ws, array $header, int $row, array|string $names): mixed
{
    foreach ((array)$names as $name) {
        $key = keyn($name);
        if (isset($header[$key])) {
            return $ws->getCell(Coordinate::stringFromColumnIndex($header[$key]) . $row)->getValue();
        }
    }
    return null;
}

function cval(Worksheet $ws, int $row, int $col): mixed
{
    return $ws->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue();
}

function pais_id(Database $db, string $pais): int
{
    foreach ($db->queryAll("SELECT id, nombre, codigo_iso FROM __SPARTA_SECRET_REDACTED__.paises") as $row) {
        if (keyn($row['nombre'] ?? '') === keyn($pais) || keyn($row['codigo_iso'] ?? '') === keyn($pais)) {
            return (int)$row['id'];
        }
    }
    return 1;
}

function split_nombre(string $nombres): array
{
    return [trim($nombres), ''];
}

function record(Worksheet $ws, array $header, string $sheet, int $row, int $idPais): array
{
    $esGuatemala = str_contains($sheet, 'GUATEMALA');
    $nombresExcel = clean(val($ws, $header, $row, 'NOMBRE (S)'), 120);
    [$nombres, $segundo] = split_nombre($nombresExcel);
    $apellidop = clean(val($ws, $header, $row, 'A. PATERNO'), 120);
    $apellidom = clean(val($ws, $header, $row, 'A. MATERNO'), 120);
    $nombreCompleto = clean(val($ws, $header, $row, 'NOMBRE/APELLIDOS'), 260);
    if ($nombreCompleto === '') {
        $nombreCompleto = trim($nombresExcel . ' ' . $apellidop . ' ' . $apellidom);
    }

    return [
        'sheet' => $sheet,
        'row' => $row,
        'id_pais' => $idPais,
        'nombre_completo' => $nombreCompleto,
        'nombres' => $nombres,
        'segundo_nombre' => $segundo,
        'apellidop' => $apellidop,
        'apellidom' => $apellidom,
        'fecha_ingreso' => fecha(val($ws, $header, $row, 'FECHA DE INGRESO')),
        'fecha_contpaq' => fecha(val($ws, $header, $row, 'FECHA CONTPAC')),
        'fecha_imss_alta' => fecha(val($ws, $header, $row, 'FECHA IMSS ALTA')),
        'puesto' => org_text(val($ws, $header, $row, 'PUESTO')),
        'departamento' => org_text(val($ws, $header, $row, 'DEPARTAMENTO')),
        'area' => org_text(val($ws, $header, $row, 'AREA')),
        'direccion' => org_text(val($ws, $header, $row, ['DIRECCION ORGANIZACIONAL', 'DIRECCION'])),
        'ubicacion_laboral' => clean(val($ws, $header, $row, 'UBICACION LABORAL'), 180),
        'municipio_laboral' => clean(val($ws, $header, $row, 'MUNICIPIO'), 180),
        'jefe_directo_texto' => clean(val($ws, $header, $row, 'JEFE DIRECTO'), 220),
        'sueldo_neto' => decimal(val($ws, $header, $row, 'SUELDO NETO')),
        'sueldo_quincenal' => decimal(val($ws, $header, $row, 'SUELDO QUINCENAL')),
        'sueldo_bruto' => decimal(val($ws, $header, $row, ['SUELDO BRUTO', 'SUELDO BASE (BRUTO)'])),
        'salario_diario' => decimal(val($ws, $header, $row, 'SALARIO DIARIO')),
        'sbc' => decimal(val($ws, $header, $row, 'SBC')),
        'curp' => strtoupper(clean(val($ws, $header, $row, 'CURP'), 18)),
        'nss' => clean(val($ws, $header, $row, ['NSS', 'IGSS']), 20),
        'rfc' => strtoupper(clean(val($ws, $header, $row, ['RFC', 'RTU']), 20)),
        'entidad_federativa_rfc' => clean(val($ws, $header, $row, 'ENTIDAD FEDERATIVA / RFC'), 120),
        'codigo_postal' => clean(val($ws, $header, $row, 'CP'), 12),
        'fecha_nacimiento' => fecha(val($ws, $header, $row, 'FECHA DE NACIMIENTO')),
        'sexo' => match (keyn(val($ws, $header, $row, 'SEXO'))) {
            'F', 'FEMENINO' => 'Femenino',
            'M', 'MASCULINO' => 'Masculino',
            default => clean(val($ws, $header, $row, 'SEXO'), 20),
        },
        'telefono' => clean(cval($ws, $row, $esGuatemala ? 22 : 36), 30),
        'correo' => clean(cval($ws, $row, $esGuatemala ? 23 : 37), 160),
        'domicilio' => clean(cval($ws, $row, $esGuatemala ? 24 : 38), 500),
        'registro_patronal' => clean(val($ws, $header, $row, 'REGISTRO PATRONAL'), 120),
        'codigo_contpaq' => clean(val($ws, $header, $row, 'CODIGO CONTPAC'), 80),
        'carta_no_credito' => clean(val($ws, $header, $row, 'CARTA DE "NO CREDITO"'), 120),
        'credito_infonavit_fonacot' => clean(val($ws, $header, $row, 'CREDITO INFONAVIT/ FONACOT'), 80),
        'no_credito' => clean(val($ws, $header, $row, 'NO. DE CREDITO'), 80),
        'monto_descontar' => decimal(val($ws, $header, $row, 'MONTO A DESCONTAR')),
        'carta_no_nomina_bbva' => clean(val($ws, $header, $row, 'CARTA "NO NOMINA EN BBVA"'), 120),
        'id_banco' => clean(cval($ws, $row, $esGuatemala ? 25 : 44), 20),
        'nombre_banco' => clean(cval($ws, $row, $esGuatemala ? 26 : 45), 120),
        'numero_cuenta' => clean(cval($ws, $row, $esGuatemala ? 27 : 46), 40),
        'clabe' => clean(cval($ws, $row, $esGuatemala ? 28 : 47), 30),
        'contacto1' => $esGuatemala ? '' : clean(cval($ws, $row, 48), 220),
        'parentesco1' => $esGuatemala ? '' : clean(cval($ws, $row, 49), 80),
        'telefono_contacto1' => $esGuatemala ? '' : clean(cval($ws, $row, 50), 30),
        'contacto2' => $esGuatemala ? '' : clean(cval($ws, $row, 51), 220),
        'parentesco2' => $esGuatemala ? '' : clean(cval($ws, $row, 52), 80),
        'telefono_contacto2' => $esGuatemala ? '' : clean(cval($ws, $row, 53), 30),
        'sueldo_bruto_letra' => clean(val($ws, $header, $row, 'SUELDO BRUTO-LETRA'), 255),
        'observaciones' => clean(val($ws, $header, $row, 'OBSERVACIONES'), 5000),
    ];
}

function add_person_index(array &$indexes, string $type, string $key, array $person): void
{
    if ($key === '' || str_ends_with($key, '|')) return;
    $indexes[$type][$key][] = $person;
}

function build_person_indexes(array $rows): array
{
    $indexes = [
        'numero_empleado' => [],
        'codigo_contpaq' => [],
        'curp' => [],
        'rfc' => [],
        'nss' => [],
        'correo' => [],
        'nombre' => [],
    ];
    foreach ($rows as $p) {
        $pais = (int)($p['id_pais'] ?? 1);
        add_person_index($indexes, 'numero_empleado', $pais . '|' . employee_key($p['numero_empleado'] ?? ''), $p);
        add_person_index($indexes, 'codigo_contpaq', $pais . '|' . clean($p['codigo_contpaq'] ?? '', 80), $p);
        add_person_index($indexes, 'curp', keyn($p['curp'] ?? ''), $p);
        add_person_index($indexes, 'rfc', keyn($p['rfc'] ?? ''), $p);
        add_person_index($indexes, 'nss', keyn($p['nss'] ?? ''), $p);
        add_person_index($indexes, 'correo', strtolower(clean($p['correo'] ?? '', 160)), $p);
        add_person_index($indexes, 'nombre', $pais . '|' . keyn($p['nombre_completo'] ?? ''), $p);
    }
    return $indexes;
}

function find_person(array $indexes, array $r): array
{
    $checks = [
        ['curp', keyn($r['curp'])],
        ['rfc', keyn($r['rfc'])],
        ['nss', keyn($r['nss'])],
        ['correo', strtolower($r['correo'])],
        ['codigo_contpaq', (int)$r['id_pais'] . '|' . $r['codigo_contpaq']],
        ['numero_empleado', (int)$r['id_pais'] . '|' . employee_key($r['codigo_contpaq'])],
        ['nombre', (int)$r['id_pais'] . '|' . keyn($r['nombre_completo'])],
    ];
    foreach ($checks as [$reason, $target]) {
        if ($target === '' || str_ends_with($target, '|')) continue;
        $matches = $indexes[$reason][$target] ?? [];
        if (count($matches) === 1) return [$matches[0], $reason];
        if (count($matches) > 1 && !in_array($reason, ['curp', 'rfc', 'nss'], true)) return [null, 'ambiguo:' . $reason];
    }
    return [null, 'sin_match'];
}

function catalog_id(Database $db, string $sql, array $params, string $name): int
{
    foreach ($db->queryAll($sql, $params) as $row) {
        if (keyn($row['nombre'] ?? '') === keyn($name)) return (int)$row['id'];
    }
    return 0;
}

function resolve_catalog(Database $db, array $r): array
{
    $dir = catalog_id($db, "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion WHERE id_pais = :pais", ['pais' => $r['id_pais']], $r['direccion']);
    $area = catalog_id($db, "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional WHERE id_pais = :pais", ['pais' => $r['id_pais']], $r['area']);
    $dep = $area > 0 ? catalog_id($db, "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.departamento WHERE id_pais = :pais AND id_departamento_organizacional = :area", ['pais' => $r['id_pais'], 'area' => $area], $r['departamento']) : 0;
    $puesto = $dep > 0 ? catalog_id($db, "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.puesto WHERE departamento_id = :dep", ['dep' => $dep], $r['puesto']) : 0;
    return ['id_direccion' => $dir, 'id_area' => $area, 'id_departamento' => $dep, 'id_puesto' => $puesto];
}

function is_blank(mixed $value): bool
{
    return trim((string)($value ?? '')) === '';
}

function prefer(mixed $current, mixed $new): mixed
{
    return is_blank($current) && !is_blank($new) ? $new : $current;
}

function null_if_blank(mixed $value): mixed
{
    return is_blank($value) ? null : $value;
}

function same_db_value(mixed $current, mixed $new): bool
{
    if (is_blank($current) && is_blank($new)) return true;
    if (is_numeric($current) && is_numeric($new)) {
        return abs((float)$current - (float)$new) < 0.00001;
    }
    return trim((string)($current ?? '')) === trim((string)($new ?? ''));
}

function params_changed(array $current, array $params, array $skip = []): bool
{
    foreach ($params as $key => $value) {
        if (in_array($key, $skip, true)) continue;
        if (!same_db_value($current[$key] ?? null, $value)) return true;
    }
    return false;
}

function active_position(Database $db, int $idPersona): ?array
{
    return $db->queryOne("
        SELECT ap.id_puesto, p.nombre AS puesto
        FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
        LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto p ON p.id = ap.id_puesto
        WHERE ap.id_persona = :id AND COALESCE(ap.activo, 1) = 1
        ORDER BY ap.id DESC
        LIMIT 1
    ", ['id' => $idPersona]);
}

function map_rows_by_id(array $rows, string $key): array
{
    $map = [];
    foreach ($rows as $row) {
        $map[(int)$row[$key]] = $row;
    }
    return $map;
}

function load_active_positions(Database $db): array
{
    $map = [];
    foreach ($db->queryAll("
        SELECT ap.id_persona, ap.id_puesto, p.nombre AS puesto
        FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
        LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto p ON p.id = ap.id_puesto
        WHERE COALESCE(ap.activo, 1) = 1
        ORDER BY ap.id DESC
    ") as $row) {
        $id = (int)$row['id_persona'];
        if (!isset($map[$id])) $map[$id] = $row;
    }
    return $map;
}

function load_related_indexes(Database $db): array
{
    $related = [
        'telefonos' => [],
        'correos' => [],
        'domicilios' => [],
        'cuentas' => [],
        'creditos' => [],
        'contactos' => [],
    ];
    foreach ($db->queryAll("SELECT id_persona, numero FROM __SPARTA_SECRET_REDACTED__.telefonos_persona") as $row) {
        $related['telefonos'][(int)$row['id_persona']][keyn($row['numero'] ?? '')] = true;
    }
    foreach ($db->queryAll("SELECT id_persona, correo FROM __SPARTA_SECRET_REDACTED__.correos_persona") as $row) {
        $related['correos'][(int)$row['id_persona']][strtolower(clean($row['correo'] ?? '', 160))] = true;
    }
    foreach ($db->queryAll("SELECT id_persona, domicilio_texto FROM __SPARTA_SECRET_REDACTED__.domicilio_persona") as $row) {
        $related['domicilios'][(int)$row['id_persona']][keyn($row['domicilio_texto'] ?? '')] = true;
    }
    foreach ($db->queryAll("SELECT id_persona, clabe, numero_cuenta FROM __SPARTA_SECRET_REDACTED__.persona_cuenta_bancaria") as $row) {
        $id = (int)$row['id_persona'];
        foreach ([$row['clabe'] ?? '', $row['numero_cuenta'] ?? ''] as $value) {
            $key = keyn($value);
            if ($key !== '') $related['cuentas'][$id][$key] = true;
        }
    }
    foreach ($db->queryAll("SELECT DISTINCT id_persona FROM __SPARTA_SECRET_REDACTED__.persona_credito_laboral") as $row) {
        $related['creditos'][(int)$row['id_persona']] = true;
    }
    foreach ($db->queryAll("SELECT id_persona, nombre_contacto FROM __SPARTA_SECRET_REDACTED__.contacto_persona_emergencia") as $row) {
        $related['contactos'][(int)$row['id_persona']][keyn($row['nombre_contacto'] ?? '')] = true;
    }
    return $related;
}

function upsert_related(Database $db, int $idPersona, array $r, bool $apply, array &$stats, array &$related): void
{
    $telefonoKey = keyn($r['telefono']);
    if ($r['telefono'] !== '' && !isset($related['telefonos'][$idPersona][$telefonoKey])) {
        $stats['telefonos']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.telefonos_persona (id_persona, numero, tipo, estatus) VALUES (:id, :numero, 'Personal', 'Activo')", ['id' => $idPersona, 'numero' => $r['telefono']]);
        $related['telefonos'][$idPersona][$telefonoKey] = true;
    }
    $correoKey = strtolower($r['correo']);
    if ($r['correo'] !== '' && !isset($related['correos'][$idPersona][$correoKey])) {
        $stats['correos']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.correos_persona (id_persona, correo, tipo, estatus) VALUES (:id, :correo, 'Personal', 'Activo')", ['id' => $idPersona, 'correo' => $r['correo']]);
        $related['correos'][$idPersona][$correoKey] = true;
    }
    $domKey = keyn($r['domicilio']);
    if ($r['domicilio'] !== '' && !isset($related['domicilios'][$idPersona][$domKey])) {
        $stats['domicilios']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.domicilio_persona (id_persona, domicilio_texto, codigo_postal, tipo, estatus) VALUES (:id, :dom, :cp, 'Particular', 'Activo')", ['id' => $idPersona, 'dom' => $r['domicilio'], 'cp' => $r['codigo_postal']]);
        $related['domicilios'][$idPersona][$domKey] = true;
    }
    $clabeKey = keyn($r['clabe']);
    $cuentaKey = keyn($r['numero_cuenta']);
    $cuentaExiste = ($clabeKey !== '' && isset($related['cuentas'][$idPersona][$clabeKey])) || ($cuentaKey !== '' && isset($related['cuentas'][$idPersona][$cuentaKey]));
    if (($r['clabe'] !== '' || $r['numero_cuenta'] !== '' || $r['nombre_banco'] !== '') && !$cuentaExiste) {
        $stats['cuentas']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.persona_cuenta_bancaria (id_persona, clabe, numero_cuenta, id_banco, nombre_banco, estatus) VALUES (:id, :clabe, :cuenta, :id_banco, :banco, 'Activo')", ['id' => $idPersona, 'clabe' => $r['clabe'], 'cuenta' => $r['numero_cuenta'], 'id_banco' => is_numeric($r['id_banco']) ? (int)$r['id_banco'] : null, 'banco' => $r['nombre_banco']]);
        if ($clabeKey !== '') $related['cuentas'][$idPersona][$clabeKey] = true;
        if ($cuentaKey !== '') $related['cuentas'][$idPersona][$cuentaKey] = true;
    }
    if ($r['credito_infonavit_fonacot'] !== '' || $r['no_credito'] !== '' || $r['monto_descontar'] !== null) {
        if (!isset($related['creditos'][$idPersona])) {
            $stats['creditos']++;
            if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.persona_credito_laboral (id_persona, tipo_credito, numero_credito, monto_descontar, estatus) VALUES (:id, :tipo, :numero, :monto, 'Activo')", ['id' => $idPersona, 'tipo' => $r['credito_infonavit_fonacot'], 'numero' => $r['no_credito'], 'monto' => $r['monto_descontar']]);
            $related['creditos'][$idPersona] = true;
        }
    }
    foreach ([1, 2] as $idx) {
        $nombre = $r["contacto{$idx}"] ?? '';
        if ($nombre === '') continue;
        $contactoKey = keyn($nombre);
        if (isset($related['contactos'][$idPersona][$contactoKey])) {
            continue;
        }
        $stats['contactos']++;
        if ($apply) {
            $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.contacto_persona_emergencia (id_persona, nombre_contacto, parentesco, numero, estatus) VALUES (:id, :nombre, :parentesco, :numero, 'Activo')", [
                'id' => $idPersona,
                'nombre' => $nombre,
                'parentesco' => $r["parentesco{$idx}"] ?? '',
                'numero' => $r["telefono_contacto{$idx}"] ?? '',
            ]);
        }
        $related['contactos'][$idPersona][$contactoKey] = true;
    }
}

$path = '';
$apply = false;
$offset = 0;
$limit = null;
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if ($arg === '--apply') $apply = true;
    elseif (str_starts_with($arg, '--offset=')) $offset = max(0, (int)substr($arg, 9));
    elseif (str_starts_with($arg, '--limit=')) $limit = max(1, (int)substr($arg, 8));
    elseif ($path === '') $path = $arg;
}
if ($path === '' || !is_readable($path)) {
    fwrite(STDERR, "Uso: php scripts/enriquecer_rrhh_desde_control_empleados_2026.php <archivo.xlsx> [--apply]\n");
    exit(1);
}

$db = new Database();
if ($apply) CapHumRrhh::asegurarTablas($db);
$idMx = pais_id($db, 'Mexico');
$idGt = pais_id($db, 'Guatemala');
$stats = [
    'leidos' => 0, 'match' => 0, 'sin_match' => 0, 'ambiguos' => 0,
    'persona_actualizada' => 0, 'rrhh_actualizado' => 0, 'puestos_asignados_sin_puesto' => 0,
    'telefonos' => 0, 'correos' => 0, 'domicilios' => 0, 'cuentas' => 0, 'creditos' => 0, 'contactos' => 0,
];
$issues = [];

$records = [];
if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'json') {
    $records = json_decode((string)file_get_contents($path), true);
    if (!is_array($records)) {
        fwrite(STDERR, "El JSON de entrada no es válido.\n");
        exit(1);
    }
} else {
    $reader = IOFactory::createReaderForFile($path);
    $reader->setReadDataOnly(true);
    if (method_exists($reader, 'setReadEmptyCells')) {
        $reader->setReadEmptyCells(false);
    }
    $reader->setReadFilter(new EnriquecerRrhhReadFilter());
    $reader->setLoadSheetsOnly(['ACTIVOS MAXI 2026', 'ACTIVOS GUATEMALA 2026', 'ACTIVOS FURIA 26']);
    $book = $reader->load($path);
    $sheets = [
        ['ACTIVOS MAXI 2026', 11, $idMx],
        ['ACTIVOS GUATEMALA 2026', 12, $idGt],
        ['ACTIVOS FURIA 26', 11, $idMx],
    ];

    foreach ($sheets as [$name, $headerRow, $pais]) {
        $ws = $book->getSheetByName($name);
        if (!$ws) continue;
        $header = header_map($ws, $headerRow);
        for ($row = $headerRow + 1; $row <= $ws->getHighestDataRow(); $row++) {
            $r = record($ws, $header, $name, $row, $pais);
            if ($r['nombre_completo'] === '' && $r['puesto'] === '' && $r['departamento'] === '') continue;
            $records[] = $r;
        }
    }
}
if ($offset > 0 || $limit !== null) {
    $records = array_slice($records, $offset, $limit);
}

$personRows = $db->queryAll("
    SELECT p.id, COALESCE(p.id_pais, 1) AS id_pais, p.curp, p.correo, p.numero_empleado,
           CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
           rr.codigo_contpaq, rr.rfc, rr.nss
    FROM __SPARTA_SECRET_REDACTED__.persona p
    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
    WHERE UPPER(TRIM(COALESCE(p.estatus, ''))) <> 'BAJA'
");
$personIndexes = build_person_indexes($personRows);
$personDataById = map_rows_by_id($db->queryAll("SELECT * FROM __SPARTA_SECRET_REDACTED__.persona"), 'id');
$rrhhByPerson = map_rows_by_id($db->queryAll("SELECT * FROM __SPARTA_SECRET_REDACTED__.persona_datos_rrhh"), 'id_persona');
$activeByPerson = load_active_positions($db);
$relatedIndexes = load_related_indexes($db);
$catalogCache = [];

if ($apply) $db->beginTransaction();
try {
    foreach ($records as $r) {
        $stats['leidos']++;
        [$person, $reason] = find_person($personIndexes, $r);
        if (!$person) {
            if (str_starts_with($reason, 'ambiguo:')) $stats['ambiguos']++;
            else $stats['sin_match']++;
            if (count($issues) < 25) $issues[] = "{$reason}: {$r['sheet']} fila {$r['row']} {$r['nombre_completo']}";
            continue;
        }
        $stats['match']++;
        $idPersona = (int)$person['id'];
        $catalogKey = implode('|', [$r['id_pais'], keyn($r['direccion']), keyn($r['area']), keyn($r['departamento']), keyn($r['puesto'])]);
        if (!isset($catalogCache[$catalogKey])) {
            $catalogCache[$catalogKey] = resolve_catalog($db, $r);
        }
        $catalog = $catalogCache[$catalogKey];
        $current = $personDataById[$idPersona] ?? [];
        $rrhh = $rrhhByPerson[$idPersona] ?? [];
        $active = $activeByPerson[$idPersona] ?? null;
        $sinPuestoActivo = !$active || (int)($active['id_puesto'] ?? 0) <= 0 || keyn($active['puesto'] ?? '') === 'SIN PUESTO';

        $personaParams = [
            'id' => $idPersona,
            'nombres' => prefer($current['nombres'] ?? '', $r['nombres']),
            'segundo_nombre' => prefer($current['segundo_nombre'] ?? '', $r['segundo_nombre']),
            'apellidop' => prefer($current['apellidop'] ?? '', $r['apellidop']),
            'apellidom' => prefer($current['apellidom'] ?? '', $r['apellidom']),
            'curp' => prefer($current['curp'] ?? '', $r['curp']),
            'correo' => prefer($current['correo'] ?? '', $r['correo']),
            'telefono_uno' => prefer($current['telefono_uno'] ?? '', $r['telefono']),
            'fecha_ingreso' => null_if_blank(prefer($current['fecha_ingreso'] ?? '', $r['fecha_ingreso'])),
            'domicilio_calle_texto' => prefer($current['domicilio_calle_texto'] ?? '', $r['domicilio']),
            'codigo_postal' => prefer($current['codigo_postal'] ?? '', $r['codigo_postal']),
        ];
        $personaChanged = params_changed($current, $personaParams, ['id']);
        if ($apply && $personaChanged) {
            $db->CRUD("UPDATE __SPARTA_SECRET_REDACTED__.persona SET nombres=:nombres, segundo_nombre=:segundo_nombre, apellidop=:apellidop, apellidom=:apellidom, curp=:curp, correo=:correo, telefono_uno=:telefono_uno, fecha_ingreso=:fecha_ingreso, domicilio_calle_texto=:domicilio_calle_texto, codigo_postal=:codigo_postal WHERE id=:id", $personaParams);
        }
        if ($personaChanged) $stats['persona_actualizada']++;

        $rrhhParams = [
            'id_persona' => $idPersona,
            'registro_patronal' => prefer($rrhh['registro_patronal'] ?? '', $r['registro_patronal']),
            'codigo_contpaq' => prefer($rrhh['codigo_contpaq'] ?? '', $r['codigo_contpaq']),
            'fecha_contpaq' => null_if_blank(prefer($rrhh['fecha_contpaq'] ?? '', $r['fecha_contpaq'])),
            'fecha_imss_alta' => null_if_blank(prefer($rrhh['fecha_imss_alta'] ?? '', $r['fecha_imss_alta'])),
            'id_departamento' => (int)($rrhh['id_departamento'] ?? 0) > 0 ? (int)$rrhh['id_departamento'] : ($catalog['id_departamento'] ?: null),
            'id_area' => (int)($rrhh['id_area'] ?? 0) > 0 ? (int)$rrhh['id_area'] : ($catalog['id_area'] ?: null),
            'id_puesto' => (int)($rrhh['id_puesto'] ?? 0) > 0 ? (int)$rrhh['id_puesto'] : ($catalog['id_puesto'] ?: null),
            'puesto_texto' => prefer($rrhh['puesto_texto'] ?? '', $r['puesto']),
            'departamento_texto' => prefer($rrhh['departamento_texto'] ?? '', $r['departamento']),
            'area_texto' => prefer($rrhh['area_texto'] ?? '', $r['area']),
            'direccion_organizacional' => prefer($rrhh['direccion_organizacional'] ?? '', $r['direccion']),
            'ubicacion_laboral' => prefer($rrhh['ubicacion_laboral'] ?? '', $r['ubicacion_laboral']),
            'municipio_laboral' => prefer($rrhh['municipio_laboral'] ?? '', $r['municipio_laboral']),
            'jefe_directo_texto' => prefer($rrhh['jefe_directo_texto'] ?? '', $r['jefe_directo_texto']),
            'sueldo_neto' => $rrhh['sueldo_neto'] ?? $r['sueldo_neto'],
            'sueldo_quincenal' => $rrhh['sueldo_quincenal'] ?? $r['sueldo_quincenal'],
            'sueldo_bruto' => $rrhh['sueldo_bruto'] ?? $r['sueldo_bruto'],
            'salario_diario' => $rrhh['salario_diario'] ?? $r['salario_diario'],
            'sbc' => $rrhh['sbc'] ?? $r['sbc'],
            'rfc' => prefer($rrhh['rfc'] ?? '', $r['rfc']),
            'nss' => prefer($rrhh['nss'] ?? '', $r['nss']),
            'entidad_federativa_rfc' => prefer($rrhh['entidad_federativa_rfc'] ?? '', $r['entidad_federativa_rfc']),
            'fecha_nacimiento' => null_if_blank(prefer($rrhh['fecha_nacimiento'] ?? '', $r['fecha_nacimiento'])),
            'sexo' => prefer($rrhh['sexo'] ?? '', $r['sexo']),
            'carta_no_credito' => prefer($rrhh['carta_no_credito'] ?? '', $r['carta_no_credito']),
            'carta_no_nomina_bbva' => prefer($rrhh['carta_no_nomina_bbva'] ?? '', $r['carta_no_nomina_bbva']),
            'sueldo_bruto_letra' => prefer($rrhh['sueldo_bruto_letra'] ?? '', $r['sueldo_bruto_letra']),
            'observaciones' => prefer($rrhh['observaciones'] ?? '', $r['observaciones']),
        ];
        [$anio, $mes, $dia] = $rrhhParams['fecha_nacimiento'] ? array_map('intval', explode('-', $rrhhParams['fecha_nacimiento'])) : [null, null, null];
        $rrhhParams += ['anio' => $anio, 'mes' => $mes, 'dia' => $dia];
        $rrhhChanged = !$rrhh || params_changed($rrhh, $rrhhParams, ['id_persona']);
        if ($apply && $rrhhChanged) {
            $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.persona_datos_rrhh (id_persona, registro_patronal, codigo_contpaq, fecha_contpaq, fecha_imss_alta, id_departamento, id_area, id_puesto, puesto_texto, departamento_texto, area_texto, direccion_organizacional, ubicacion_laboral, municipio_laboral, jefe_directo_texto, sueldo_neto, sueldo_quincenal, sueldo_bruto, salario_diario, sbc, rfc, nss, entidad_federativa_rfc, anio, mes, dia, fecha_nacimiento, sexo, carta_no_credito, carta_no_nomina_bbva, sueldo_bruto_letra, observaciones)
                VALUES (:id_persona, :registro_patronal, :codigo_contpaq, :fecha_contpaq, :fecha_imss_alta, :id_departamento, :id_area, :id_puesto, :puesto_texto, :departamento_texto, :area_texto, :direccion_organizacional, :ubicacion_laboral, :municipio_laboral, :jefe_directo_texto, :sueldo_neto, :sueldo_quincenal, :sueldo_bruto, :salario_diario, :sbc, :rfc, :nss, :entidad_federativa_rfc, :anio, :mes, :dia, :fecha_nacimiento, :sexo, :carta_no_credito, :carta_no_nomina_bbva, :sueldo_bruto_letra, :observaciones)
                ON DUPLICATE KEY UPDATE registro_patronal=VALUES(registro_patronal), codigo_contpaq=VALUES(codigo_contpaq), fecha_contpaq=VALUES(fecha_contpaq), fecha_imss_alta=VALUES(fecha_imss_alta), id_departamento=VALUES(id_departamento), id_area=VALUES(id_area), id_puesto=VALUES(id_puesto), puesto_texto=VALUES(puesto_texto), departamento_texto=VALUES(departamento_texto), area_texto=VALUES(area_texto), direccion_organizacional=VALUES(direccion_organizacional), ubicacion_laboral=VALUES(ubicacion_laboral), municipio_laboral=VALUES(municipio_laboral), jefe_directo_texto=VALUES(jefe_directo_texto), sueldo_neto=VALUES(sueldo_neto), sueldo_quincenal=VALUES(sueldo_quincenal), sueldo_bruto=VALUES(sueldo_bruto), salario_diario=VALUES(salario_diario), sbc=VALUES(sbc), rfc=VALUES(rfc), nss=VALUES(nss), entidad_federativa_rfc=VALUES(entidad_federativa_rfc), anio=VALUES(anio), mes=VALUES(mes), dia=VALUES(dia), fecha_nacimiento=VALUES(fecha_nacimiento), sexo=VALUES(sexo), carta_no_credito=VALUES(carta_no_credito), carta_no_nomina_bbva=VALUES(carta_no_nomina_bbva), sueldo_bruto_letra=VALUES(sueldo_bruto_letra), observaciones=VALUES(observaciones)", $rrhhParams);
        }
        if ($rrhhChanged) $stats['rrhh_actualizado']++;

        if ($sinPuestoActivo && (int)$catalog['id_puesto'] > 0) {
            $stats['puestos_asignados_sin_puesto']++;
            if ($apply) {
                $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto (id, id_persona, id_puesto, fecha_asignacion, activo) VALUES (DEFAULT, :id_persona, :id_puesto, :fecha, 1)", ['id_persona' => $idPersona, 'id_puesto' => $catalog['id_puesto'], 'fecha' => CapHum::fechaHoraCdmx()]);
            }
        }
        upsert_related($db, $idPersona, $r, $apply, $stats, $relatedIndexes);
    }
    if ($apply) $db->commit();
} catch (Throwable $e) {
    if ($apply) $db->rollback();
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
    exit(2);
}

echo 'Modo: ' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;
echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($issues) {
    echo "Observaciones:\n";
    foreach ($issues as $issue) echo '- ' . $issue . PHP_EOL;
}
