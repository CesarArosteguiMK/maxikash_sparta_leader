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

final class BajasMaxiReadFilter implements IReadFilter
{
    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return Coordinate::columnIndexFromString((string)$columnAddress) <= 80;
    }
}

function usage(): void
{
    fwrite(STDERR, "Uso: php scripts/importar_bajas_maxi_26.php <archivo.xlsx> [--apply] [--from-row=N] [--to-row=N] [--no-issues] [--list-new]\n");
    fwrite(STDERR, "Por defecto corre en dry-run y no escribe en la base.\n");
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
    if ($text === '') {
        return '';
    }
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

function phone_value(mixed $value, int $max = 20): string
{
    $text = clean($value, 80);
    if ($text === '') {
        return '';
    }
    if (preg_match('/\d[\d\s\-\(\)]{6,}\d/u', $text, $m)) {
        $digits = preg_replace('/\D+/', '', $m[0]) ?? '';
        if (strlen($digits) > 10) {
            $digits = substr($digits, 0, 10);
        }
        return mb_substr($digits !== '' ? $digits : trim($m[0]), 0, $max);
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
        'ATENCION A CLIENTES' => 'Atención a Clientes',
        'COBRANZA COPORATIVO' => 'COBRANZA CORPORATIVO',
        default => $text,
    };
}

function fecha(mixed $value): ?string
{
    if ($value instanceof DateTimeInterface) {
        $year = (int)$value->format('Y');
        return ($year >= 1900 && $year <= 2100) ? $value->format('Y-m-d') : null;
    }
    if (is_int($value) || is_float($value)) {
        try {
            $date = ExcelDate::excelToDateTimeObject((float)$value);
            $year = (int)$date->format('Y');
            return ($year >= 1900 && $year <= 2100) ? $date->format('Y-m-d') : null;
        } catch (Throwable) {
            return null;
        }
    }
    $text = clean($value, 40);
    if ($text === '') {
        return null;
    }
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $text, $m)) {
        if ((int)$m[1] < 1900 || (int)$m[1] > 2100) {
            return null;
        }
        return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
    }
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2}|\d{4})$/', $text, $m)) {
        $year = (int)$m[3];
        if ($year < 100) {
            $year += $year > 40 ? 1900 : 2000;
        }
        return ($year >= 1900 && $year <= 2100) ? sprintf('%04d-%02d-%02d', $year, (int)$m[2], (int)$m[1]) : null;
    }
    $ts = strtotime($text);
    if (!$ts) {
        return null;
    }
    $year = (int)date('Y', $ts);
    return ($year >= 1900 && $year <= 2100) ? date('Y-m-d', $ts) : null;
}

function decimal(mixed $value): ?float
{
    if (is_int($value) || is_float($value)) {
        return (float)$value;
    }
    $text = clean($value, 40);
    if ($text === '') {
        return null;
    }
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
        if ($key !== '' && !isset($map[$key])) {
            $map[$key] = $col;
        }
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

function record_from_sheet(Worksheet $ws, array $header, int $row, int $idPais): array
{
    $nombres = clean(val($ws, $header, $row, 'NOMBRE (S)'), 120);
    $apellidop = clean(val($ws, $header, $row, ['A. PATERNO', 'APELLIDO PATERNO']), 120);
    $apellidom = clean(val($ws, $header, $row, ['A. MATERNO', 'APELLIDO MATERNO']), 120);
    $nombreCompleto = clean(val($ws, $header, $row, 'NOMBRE/APELLIDOS'), 260);
    if ($nombreCompleto === '') {
        $nombreCompleto = trim($nombres . ' ' . $apellidop . ' ' . $apellidom);
    }
    $nombreGuardado = trim($nombres . ' ' . $apellidop . ' ' . $apellidom);
    $nombreApellidosGuardado = trim($apellidop . ' ' . $apellidom . ' ' . $nombres);

    $sexoRaw = val($ws, $header, $row, 'SEXO');
    $sexoKey = keyn($sexoRaw);
    if (!in_array($sexoKey, ['F', 'FEMENINO', 'M', 'MASCULINO'], true)) {
        $sexoRaw = cval($ws, $row, 29);
        $sexoKey = keyn($sexoRaw);
    }
    $fechaBaja = fecha(val($ws, $header, $row, 'FECHA DE BAJA'));
    $motivo = clean(val($ws, $header, $row, 'MOTIVO DE BAJA RH'), 500);
    $statusNomina = clean(val($ws, $header, $row, 'STATUS NOMINA'), 250);
    $status = clean(val($ws, $header, $row, ['STATUS', 'STATUS RH']), 250);
    $ultimoDia = clean(val($ws, $header, $row, ['ULTIMO DIA LABORADO', 'ULTIMO DIA LABORADO O DESCUENTOS']), 250);

    return [
        'sheet' => $ws->getTitle(),
        'row' => $row,
        'id_pais' => $idPais,
        'nombre_completo' => $nombreCompleto,
        'nombre_guardado' => $nombreGuardado,
        'nombre_apellidos_guardado' => $nombreApellidosGuardado,
        'nombres' => $nombres,
        'segundo_nombre' => '',
        'apellidop' => $apellidop,
        'apellidom' => $apellidom,
        'fecha_ingreso' => fecha(val($ws, $header, $row, ['FECHA DE INGRESO', 'FECHA IMSS ALTA'])),
        'fecha_contpaq' => fecha(val($ws, $header, $row, 'FECHA CONTPAC')),
        'fecha_imss_alta' => fecha(val($ws, $header, $row, 'FECHA IMSS ALTA')),
        'puesto' => org_text(val($ws, $header, $row, 'PUESTO')),
        'departamento' => org_text(val($ws, $header, $row, 'DEPARTAMENTO')),
        'area' => org_text(val($ws, $header, $row, 'AREA')),
        'direccion' => org_text(val($ws, $header, $row, ['DIRECCION ORGANIZACIONAL', 'DIRECCION'])),
        'ubicacion_laboral' => clean(val($ws, $header, $row, ['UBICACION LABORAL', 'UBICACION']), 180),
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
        'entidad_federativa_rfc' => clean(val($ws, $header, $row, ['ENTIDAD FEDERATIVA / RFC', 'ENTIDAD FEDERATIVA']), 120),
        'codigo_postal' => clean(val($ws, $header, $row, 'CP'), 12),
        'fecha_nacimiento' => fecha(val($ws, $header, $row, 'FECHA DE NACIMIENTO')),
        'sexo' => match ($sexoKey) {
            'F', 'FEMENINO' => 'Femenino',
            'M', 'MASCULINO' => 'Masculino',
            default => '',
        },
        'telefono' => phone_value(val($ws, $header, $row, 'NO. TELEFONICO')),
        'correo' => clean(val($ws, $header, $row, 'CORREO ELECTRONICO'), 160),
        'domicilio' => clean(val($ws, $header, $row, ['DOMICILIO PARTICULAR', 'DOMICILIO']), 500),
        'registro_patronal' => clean(val($ws, $header, $row, 'REGISTRO PATRONAL'), 120),
        'codigo_contpaq' => clean(val($ws, $header, $row, ['CODIGO CONTPAC', 'CODIGO CONTA', 'No. EMPLEADO']), 80),
        'carta_no_credito' => clean(val($ws, $header, $row, 'CARTA DE "NO CREDITO"'), 120),
        'credito_infonavit_fonacot' => clean(val($ws, $header, $row, 'CREDITO INFONAVIT/ FONACOT'), 80),
        'no_credito' => clean(val($ws, $header, $row, 'NO. DE CREDITO'), 80),
        'monto_descontar' => decimal(val($ws, $header, $row, 'MONTO A DESCONTAR')),
        'carta_no_nomina_bbva' => clean(val($ws, $header, $row, 'CARTA "NO NOMINA EN BBVA"'), 120),
        'id_banco' => clean(val($ws, $header, $row, 'BANCO'), 20),
        'nombre_banco' => clean(val($ws, $header, $row, 'NOMBRE DE BANCO'), 120),
        'numero_cuenta' => clean(val($ws, $header, $row, 'NO. CUENTA'), 40),
        'clabe' => clean(val($ws, $header, $row, ['CLABE INTERBANCARIA', 'CLABEINTERBANCARIA']), 30),
        'contacto1' => clean(val($ws, $header, $row, 'CONTACTO DE EMERGENCIA 1') ?: cval($ws, $row, 42), 220),
        'parentesco1' => clean(cval($ws, $row, isset($header[keyn('CONTACTO DE EMERGENCIA 1')]) ? $header[keyn('CONTACTO DE EMERGENCIA 1')] + 1 : 43), 80),
        'telefono_contacto1' => phone_value(cval($ws, $row, isset($header[keyn('CONTACTO DE EMERGENCIA 1')]) ? $header[keyn('CONTACTO DE EMERGENCIA 1')] + 2 : 44)),
        'observaciones' => clean(trim(implode(' | ', array_filter([
            clean(val($ws, $header, $row, 'OBSERVACIONES'), 3000),
            clean(val($ws, $header, $row, 'OBSERVACIONES RH'), 3000),
        ]))), 5000),
        'fecha_baja' => $fechaBaja,
        'motivo_baja' => $motivo !== '' ? $motivo : 'Baja importada',
        'descripcion_baja' => trim(implode(' | ', array_filter([
            $statusNomina !== '' ? 'Status nomina: ' . $statusNomina : '',
            $status !== '' ? 'Status: ' . $status : '',
            $ultimoDia !== '' ? 'Ultimo dia laborado: ' . $ultimoDia : '',
        ]))),
    ];
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
    return substr($base !== '' ? $base : 'baja', 0, 34);
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
    throw new RuntimeException('No se pudo generar usuario unico.');
}

function add_index(array &$indexes, string $type, string $key, array $person): void
{
    if ($key === '' || str_ends_with($key, '|')) {
        return;
    }
    foreach ($indexes[$type][$key] ?? [] as $existing) {
        if ((int)($existing['id'] ?? 0) === (int)($person['id'] ?? 0)) {
            return;
        }
    }
    $indexes[$type][$key][] = $person;
}

function load_people_indexes(Database $db): array
{
    $indexes = [
        'curp' => [],
        'rfc' => [],
        'nss' => [],
        'correo' => [],
        'codigo_contpaq' => [],
        'numero_empleado' => [],
        'nombre' => [],
        'usernames' => [],
    ];
    $rows = $db->queryAll("
        SELECT p.id, COALESCE(p.id_pais, 1) AS id_pais, p.curp, p.rfc AS persona_rfc, p.correo,
               p.numero_empleado, p.codigo_contpac, p.user_name, p.estatus,
               CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
               CONCAT_WS(' ', p.apellidop, p.apellidom, p.nombres, p.segundo_nombre) AS nombre_apellidos,
               rr.codigo_contpaq, rr.rfc, rr.nss
        FROM __SPARTA_SECRET_REDACTED__.persona p
        LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
    ");

    foreach ($rows as $row) {
        $pais = (int)($row['id_pais'] ?? 1);
        add_index($indexes, 'curp', keyn($row['curp'] ?? ''), $row);
        add_index($indexes, 'rfc', keyn($row['rfc'] ?: ($row['persona_rfc'] ?? '')), $row);
        add_index($indexes, 'nss', keyn($row['nss'] ?? ''), $row);
        add_index($indexes, 'correo', strtolower(clean($row['correo'] ?? '', 160)), $row);
        add_index($indexes, 'codigo_contpaq', $pais . '|' . employee_key($row['codigo_contpaq'] ?: ($row['codigo_contpac'] ?? '')), $row);
        add_index($indexes, 'numero_empleado', $pais . '|' . employee_key($row['numero_empleado'] ?? ''), $row);
        add_index($indexes, 'nombre', $pais . '|' . keyn($row['nombre_completo'] ?? ''), $row);
        add_index($indexes, 'nombre', $pais . '|' . keyn($row['nombre_apellidos'] ?? ''), $row);
        $user = clean($row['user_name'] ?? '', 80);
        if ($user !== '') {
            $indexes['usernames'][$user] = true;
        }
    }
    return $indexes;
}

function index_imported_person(array &$indexes, int $idPersona, array $r): void
{
    if ($idPersona <= 0) {
        return;
    }

    $pais = (int)($r['id_pais'] ?? 1);
    $person = [
        'id' => $idPersona,
        'id_pais' => $pais,
        'curp' => $r['curp'] ?? '',
        'persona_rfc' => $r['rfc'] ?? '',
        'rfc' => $r['rfc'] ?? '',
        'nss' => $r['nss'] ?? '',
        'correo' => $r['correo'] ?? '',
        'numero_empleado' => $r['codigo_contpaq'] ?? '',
        'codigo_contpac' => $r['codigo_contpaq'] ?? '',
        'codigo_contpaq' => $r['codigo_contpaq'] ?? '',
        'nombre_completo' => $r['nombre_completo'] ?? '',
        'user_name' => '',
        'estatus' => 'Baja',
    ];

    add_index($indexes, 'curp', keyn($person['curp']), $person);
    add_index($indexes, 'rfc', keyn($person['rfc']), $person);
    add_index($indexes, 'nss', keyn($person['nss']), $person);
    add_index($indexes, 'correo', strtolower(clean($person['correo'], 160)), $person);
    add_index($indexes, 'codigo_contpaq', $pais . '|' . employee_key($person['codigo_contpaq']), $person);
    add_index($indexes, 'numero_empleado', $pais . '|' . employee_key($person['numero_empleado']), $person);
    add_index($indexes, 'nombre', $pais . '|' . keyn($person['nombre_completo']), $person);
    add_index($indexes, 'nombre', $pais . '|' . keyn($r['nombre_guardado'] ?? ''), $person);
    add_index($indexes, 'nombre', $pais . '|' . keyn($r['nombre_apellidos_guardado'] ?? ''), $person);
    add_index($indexes, 'nombre', $pais . '|' . keyn(trim(($r['apellidop'] ?? '') . ' ' . ($r['apellidom'] ?? '') . ' ' . ($r['nombres'] ?? '') . ' ' . ($r['segundo_nombre'] ?? ''))), $person);
}

function find_person(array $indexes, array $r): array
{
    $checks = [
        ['curp', keyn($r['curp']), 'CURP'],
        ['rfc', keyn($r['rfc']), 'RFC'],
        ['nss', keyn($r['nss']), 'NSS'],
        ['correo', strtolower($r['correo']), 'correo'],
        ['codigo_contpaq', (int)$r['id_pais'] . '|' . employee_key($r['codigo_contpaq']), 'codigo CONTPAC'],
        ['numero_empleado', (int)$r['id_pais'] . '|' . employee_key($r['codigo_contpaq']), 'numero empleado'],
        ['nombre', (int)$r['id_pais'] . '|' . keyn($r['nombre_completo']), 'nombre'],
        ['nombre', (int)$r['id_pais'] . '|' . keyn($r['nombre_guardado'] ?? ''), 'nombre'],
        ['nombre', (int)$r['id_pais'] . '|' . keyn($r['nombre_apellidos_guardado'] ?? ''), 'nombre'],
    ];
    foreach ($checks as [$type, $key, $reason]) {
        if ($key === '' || str_ends_with($key, '|')) {
            continue;
        }
        $matches = $indexes[$type][$key] ?? [];
        if (count($matches) === 1) {
            return [$matches[0], $reason];
        }
        if (count($matches) > 1) {
            return [null, 'ambiguo por ' . $reason];
        }
    }
    return [null, 'nuevo'];
}

function date_parts(?string $date): array
{
    if (!$date) {
        return [null, null, null];
    }
    [$y, $m, $d] = array_map('intval', explode('-', $date));
    return [$y ?: null, $m ?: null, $d ?: null];
}

function catalog_id(Database $db, string $sql, array $params, string $name): int
{
    if ($name === '') {
        return 0;
    }
    foreach ($db->queryAll($sql, $params) as $row) {
        if (keyn($row['nombre'] ?? '') === keyn($name)) {
            return (int)$row['id'];
        }
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

function changed(mixed $a, mixed $b): bool
{
    return trim((string)($a ?? '')) !== trim((string)($b ?? ''));
}

function upsert_person(Database $db, array $r, ?array $person, array $catalog, array &$usernames, bool $apply): int
{
    $fechaRegistro = CapHum::fechaHoraCdmx();
    $codigo = $r['codigo_contpaq'];
    if ($person) {
        $id = (int)$person['id'];
        if ($apply) {
            $db->CRUD("
                UPDATE __SPARTA_SECRET_REDACTED__.persona
                SET nombres = :nombres,
                    segundo_nombre = :segundo_nombre,
                    apellidop = :apellidop,
                    apellidom = :apellidom,
                    curp = :curp,
                    rfc = :rfc,
                    numero_empleado = :numero_empleado,
                    codigo_contpac = :codigo_contpac,
                    correo = :correo,
                    telefono_uno = :telefono,
                    fecha_ingreso = :fecha_ingreso,
                    id_pais = :id_pais,
                    domicilio_calle_texto = :domicilio,
                    codigo_postal = :codigo_postal,
                    estatus = 'Baja'
                WHERE id = :id
            ", [
                'id' => $id,
                'nombres' => $r['nombres'],
                'segundo_nombre' => $r['segundo_nombre'],
                'apellidop' => $r['apellidop'],
                'apellidom' => $r['apellidom'],
                'curp' => $r['curp'] ?: null,
                'rfc' => $r['rfc'] ?: null,
                'numero_empleado' => $codigo ?: ($person['numero_empleado'] ?? null),
                'codigo_contpac' => $codigo ?: ($person['codigo_contpac'] ?? null),
                'correo' => $r['correo'],
                'telefono' => $r['telefono'],
                'fecha_ingreso' => $r['fecha_ingreso'],
                'id_pais' => $r['id_pais'],
                'domicilio' => $r['domicilio'],
                'codigo_postal' => $r['codigo_postal'],
            ]);
        }
        return $id;
    }

    $username = unique_username(username_base($r['correo'], $r['nombres'], $r['apellidop']), $usernames);
    if (!$apply) {
        return 0;
    }
    $db->CRUD("
        INSERT INTO __SPARTA_SECRET_REDACTED__.persona
            (nombres, segundo_nombre, apellidop, apellidom, curp, rfc, numero_empleado, codigo_contpac,
             correo, telefono_uno, telefono_dos, estatus, user_name, password, fecha_ingreso, fecha_registro,
             id_pais, domicilio_calle_texto, codigo_postal)
        VALUES
            (:nombres, :segundo_nombre, :apellidop, :apellidom, :curp, :rfc, :numero_empleado, :codigo_contpac,
             :correo, :telefono, '', 'Baja', :user_name, :password, :fecha_ingreso, :fecha_registro,
             :id_pais, :domicilio, :codigo_postal)
    ", [
        'nombres' => $r['nombres'],
        'segundo_nombre' => $r['segundo_nombre'],
        'apellidop' => $r['apellidop'],
        'apellidom' => $r['apellidom'],
        'curp' => $r['curp'] ?: null,
        'rfc' => $r['rfc'] ?: null,
        'numero_empleado' => $codigo,
        'codigo_contpac' => $codigo,
        'correo' => $r['correo'],
        'telefono' => $r['telefono'],
        'user_name' => $username,
        'password' => '__SPARTA_PASSWORD_REDACTED__',
        'fecha_ingreso' => $r['fecha_ingreso'],
        'fecha_registro' => $fechaRegistro,
        'id_pais' => $r['id_pais'],
        'domicilio' => $r['domicilio'],
        'codigo_postal' => $r['codigo_postal'],
    ]);
    return $db->lastInsertId();
}

function upsert_rrhh(Database $db, int $idPersona, array $r, array $catalog, bool $apply): void
{
    [$anio, $mes, $dia] = date_parts($r['fecha_nacimiento']);
    if (!$apply) {
        return;
    }
    $db->CRUD("
        INSERT INTO __SPARTA_SECRET_REDACTED__.persona_datos_rrhh
            (id_persona, registro_patronal, codigo_contpaq, fecha_contpaq, fecha_imss_alta,
             id_departamento, id_area, id_puesto, puesto_texto, departamento_texto, area_texto,
             direccion_organizacional, ubicacion_laboral, municipio_laboral, jefe_directo_texto,
             sueldo_neto, sueldo_quincenal, sueldo_bruto, salario_diario, sbc,
             rfc, nss, entidad_federativa_rfc, anio, mes, dia, fecha_nacimiento, sexo,
             carta_no_credito, carta_no_nomina_bbva, observaciones, updated_at)
        VALUES
            (:id_persona, :registro_patronal, :codigo_contpaq, :fecha_contpaq, :fecha_imss_alta,
             :id_departamento, :id_area, :id_puesto, :puesto_texto, :departamento_texto, :area_texto,
             :direccion_organizacional, :ubicacion_laboral, :municipio_laboral, :jefe_directo_texto,
             :sueldo_neto, :sueldo_quincenal, :sueldo_bruto, :salario_diario, :sbc,
             :rfc, :nss, :entidad_federativa_rfc, :anio, :mes, :dia, :fecha_nacimiento, :sexo,
             :carta_no_credito, :carta_no_nomina_bbva, :observaciones, :updated_at)
        ON DUPLICATE KEY UPDATE
            registro_patronal=VALUES(registro_patronal),
            codigo_contpaq=VALUES(codigo_contpaq),
            fecha_contpaq=VALUES(fecha_contpaq),
            fecha_imss_alta=VALUES(fecha_imss_alta),
            id_departamento=VALUES(id_departamento),
            id_area=VALUES(id_area),
            id_puesto=VALUES(id_puesto),
            puesto_texto=VALUES(puesto_texto),
            departamento_texto=VALUES(departamento_texto),
            area_texto=VALUES(area_texto),
            direccion_organizacional=VALUES(direccion_organizacional),
            ubicacion_laboral=VALUES(ubicacion_laboral),
            municipio_laboral=VALUES(municipio_laboral),
            jefe_directo_texto=VALUES(jefe_directo_texto),
            sueldo_neto=VALUES(sueldo_neto),
            sueldo_quincenal=VALUES(sueldo_quincenal),
            sueldo_bruto=VALUES(sueldo_bruto),
            salario_diario=VALUES(salario_diario),
            sbc=VALUES(sbc),
            rfc=VALUES(rfc),
            nss=VALUES(nss),
            entidad_federativa_rfc=VALUES(entidad_federativa_rfc),
            anio=VALUES(anio),
            mes=VALUES(mes),
            dia=VALUES(dia),
            fecha_nacimiento=VALUES(fecha_nacimiento),
            sexo=VALUES(sexo),
            carta_no_credito=VALUES(carta_no_credito),
            carta_no_nomina_bbva=VALUES(carta_no_nomina_bbva),
            observaciones=VALUES(observaciones),
            updated_at=VALUES(updated_at)
    ", [
        'id_persona' => $idPersona,
        'registro_patronal' => $r['registro_patronal'],
        'codigo_contpaq' => $r['codigo_contpaq'],
        'fecha_contpaq' => $r['fecha_contpaq'],
        'fecha_imss_alta' => $r['fecha_imss_alta'],
        'id_departamento' => $catalog['id_departamento'] ?: null,
        'id_area' => $catalog['id_area'] ?: null,
        'id_puesto' => $catalog['id_puesto'] ?: null,
        'puesto_texto' => $r['puesto'],
        'departamento_texto' => $r['departamento'],
        'area_texto' => $r['area'],
        'direccion_organizacional' => $r['direccion'],
        'ubicacion_laboral' => $r['ubicacion_laboral'],
        'municipio_laboral' => $r['municipio_laboral'],
        'jefe_directo_texto' => $r['jefe_directo_texto'],
        'sueldo_neto' => $r['sueldo_neto'],
        'sueldo_quincenal' => $r['sueldo_quincenal'],
        'sueldo_bruto' => $r['sueldo_bruto'],
        'salario_diario' => $r['salario_diario'],
        'sbc' => $r['sbc'],
        'rfc' => $r['rfc'],
        'nss' => $r['nss'],
        'entidad_federativa_rfc' => $r['entidad_federativa_rfc'],
        'anio' => $anio,
        'mes' => $mes,
        'dia' => $dia,
        'fecha_nacimiento' => $r['fecha_nacimiento'],
        'sexo' => $r['sexo'],
        'carta_no_credito' => $r['carta_no_credito'],
        'carta_no_nomina_bbva' => $r['carta_no_nomina_bbva'],
        'observaciones' => $r['observaciones'],
        'updated_at' => CapHum::fechaHoraCdmx(),
    ]);
}

function exists_one(Database $db, string $sql, array $params): bool
{
    return (bool)$db->queryOne($sql, $params);
}

function insert_related(Database $db, int $idPersona, array $r, bool $apply, array &$stats): void
{
    if ($r['telefono'] !== '' && !exists_one($db, "SELECT id FROM __SPARTA_SECRET_REDACTED__.telefonos_persona WHERE id_persona=:id AND numero=:numero LIMIT 1", ['id' => $idPersona, 'numero' => $r['telefono']])) {
        $stats['telefonos']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.telefonos_persona (id_persona, numero, tipo, estatus) VALUES (:id, :numero, 'Personal', 'Activo')", ['id' => $idPersona, 'numero' => $r['telefono']]);
    }
    if ($r['correo'] !== '' && !exists_one($db, "SELECT id FROM __SPARTA_SECRET_REDACTED__.correos_persona WHERE id_persona=:id AND correo=:correo LIMIT 1", ['id' => $idPersona, 'correo' => $r['correo']])) {
        $stats['correos']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.correos_persona (id_persona, correo, tipo, estatus) VALUES (:id, :correo, 'Personal', 'Activo')", ['id' => $idPersona, 'correo' => $r['correo']]);
    }
    if ($r['domicilio'] !== '' && !exists_one($db, "SELECT id FROM __SPARTA_SECRET_REDACTED__.domicilio_persona WHERE id_persona=:id AND domicilio_texto=:dom LIMIT 1", ['id' => $idPersona, 'dom' => $r['domicilio']])) {
        $stats['domicilios']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.domicilio_persona (id_persona, domicilio_texto, codigo_postal, tipo, estatus) VALUES (:id, :dom, :cp, 'Particular', 'Activo')", ['id' => $idPersona, 'dom' => $r['domicilio'], 'cp' => $r['codigo_postal']]);
    }
    if (($r['clabe'] !== '' || $r['numero_cuenta'] !== '' || $r['nombre_banco'] !== '')
        && !exists_one($db, "SELECT id FROM __SPARTA_SECRET_REDACTED__.persona_cuenta_bancaria WHERE id_persona=:id AND (clabe=:clabe OR numero_cuenta=:cuenta) LIMIT 1", ['id' => $idPersona, 'clabe' => $r['clabe'], 'cuenta' => $r['numero_cuenta']])) {
        $stats['cuentas']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.persona_cuenta_bancaria (id_persona, clabe, numero_cuenta, id_banco, nombre_banco, estatus) VALUES (:id, :clabe, :cuenta, :id_banco, :banco, 'Activo')", ['id' => $idPersona, 'clabe' => $r['clabe'], 'cuenta' => $r['numero_cuenta'], 'id_banco' => is_numeric($r['id_banco']) ? (int)$r['id_banco'] : null, 'banco' => $r['nombre_banco']]);
    }
    if (($r['credito_infonavit_fonacot'] !== '' || $r['no_credito'] !== '' || $r['monto_descontar'] !== null)
        && !exists_one($db, "SELECT id FROM __SPARTA_SECRET_REDACTED__.persona_credito_laboral WHERE id_persona=:id AND COALESCE(numero_credito,'')=:numero LIMIT 1", ['id' => $idPersona, 'numero' => $r['no_credito']])) {
        $stats['creditos']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.persona_credito_laboral (id_persona, tipo_credito, numero_credito, monto_descontar, estatus) VALUES (:id, :tipo, :numero, :monto, 'Activo')", ['id' => $idPersona, 'tipo' => $r['credito_infonavit_fonacot'], 'numero' => $r['no_credito'], 'monto' => $r['monto_descontar']]);
    }
    if ($r['contacto1'] !== '' && !exists_one($db, "SELECT id FROM __SPARTA_SECRET_REDACTED__.contacto_persona_emergencia WHERE id_persona=:id AND nombre_contacto=:nombre LIMIT 1", ['id' => $idPersona, 'nombre' => $r['contacto1']])) {
        $stats['contactos']++;
        if ($apply) $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.contacto_persona_emergencia (id_persona, nombre_contacto, parentesco, numero, estatus) VALUES (:id, :nombre, :parentesco, :numero, 'Activo')", ['id' => $idPersona, 'nombre' => $r['contacto1'], 'parentesco' => $r['parentesco1'], 'numero' => $r['telefono_contacto1']]);
    }
}

function ensure_position_and_baja(Database $db, int $idPersona, array $r, array $catalog, bool $apply, array &$stats): void
{
    if ((int)$catalog['id_puesto'] > 0 && !exists_one($db, "SELECT id FROM __SPARTA_SECRET_REDACTED__.asigna_puesto WHERE id_persona=:id AND id_puesto=:puesto LIMIT 1", ['id' => $idPersona, 'puesto' => $catalog['id_puesto']])) {
        $stats['puestos']++;
        if ($apply) {
            $db->CRUD("INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto (id, id_persona, id_puesto, fecha_asignacion, activo) VALUES (DEFAULT, :id, :puesto, :fecha, 1)", ['id' => $idPersona, 'puesto' => $catalog['id_puesto'], 'fecha' => ($r['fecha_ingreso'] ?: CapHum::fechaHoraCdmx())]);
        }
    }

    $fechaBaja = $r['fecha_baja'] ?: substr(CapHum::fechaHoraCdmx(), 0, 10);
    if (!exists_one($db, "SELECT id FROM __SPARTA_SECRET_REDACTED__.baja_persona WHERE id_persona=:id AND DATE(fecha_baja)=:fecha LIMIT 1", ['id' => $idPersona, 'fecha' => $fechaBaja])) {
        $stats['bajas_registradas']++;
        if ($apply) {
            $db->CRUD("
                INSERT INTO __SPARTA_SECRET_REDACTED__.baja_persona (id_persona, motivo, fecha_baja, descripcion, usuario_baja)
                VALUES (:id, :motivo, :fecha_baja, :descripcion, :usuario_baja)
            ", [
                'id' => $idPersona,
                'motivo' => $r['motivo_baja'],
                'fecha_baja' => $fechaBaja . ' 00:00:00',
                'descripcion' => $r['descripcion_baja'],
                'usuario_baja' => 'importacion_bajas_maxi_26',
            ]);
        }
    }
}

$path = '';
$apply = false;
$fromRowArg = 0;
$toRowArg = 0;
$showIssues = true;
$listNew = false;
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif ($arg === '--no-issues') {
        $showIssues = false;
    } elseif ($arg === '--list-new') {
        $listNew = true;
    } elseif (str_starts_with($arg, '--from-row=')) {
        $fromRowArg = max(0, (int) substr($arg, strlen('--from-row=')));
    } elseif (str_starts_with($arg, '--to-row=')) {
        $toRowArg = max(0, (int) substr($arg, strlen('--to-row=')));
    } elseif ($path === '') {
        $path = $arg;
    }
}

if ($path === '' || !is_readable($path)) {
    usage();
    exit(1);
}

$db = new Database();
if ($apply) {
    CapHumRrhh::asegurarTablas($db);
}

$stats = [
    'leidas' => 0,
    'omitidas_vacias' => 0,
    'omitidas_incompletas' => 0,
    'ambiguas' => 0,
    'existentes_actualizar' => 0,
    'nuevas_insertar' => 0,
    'actualizadas' => 0,
    'insertadas' => 0,
    'rrhh' => 0,
    'telefonos' => 0,
    'correos' => 0,
    'domicilios' => 0,
    'cuentas' => 0,
    'creditos' => 0,
    'contactos' => 0,
    'puestos' => 0,
    'bajas_registradas' => 0,
    'catalogo_sin_match' => 0,
];
$issues = [];
$idMexico = pais_id($db, 'Mexico');
$people = load_people_indexes($db);

$reader = IOFactory::createReaderForFile($path);
$reader->setReadDataOnly(true);
if (method_exists($reader, 'setReadEmptyCells')) {
    $reader->setReadEmptyCells(false);
}
$reader->setReadFilter(new BajasMaxiReadFilter());
$sheetName = 'BAJAS MAXI 26';
$headerRow = 11;
$startRow = 12;
$availableSheets = $reader->listWorksheetNames($path);
if (in_array('BAJAS 25', $availableSheets, true)) {
    $sheetName = 'BAJAS 25';
    $headerRow = 5;
    $startRow = 6;
}
$reader->setLoadSheetsOnly([$sheetName]);
$book = $reader->load($path);
$ws = $book->getSheetByName($sheetName);
if (!$ws) {
    fwrite(STDERR, "No se encontro la hoja {$sheetName}.\n");
    exit(1);
}

$header = header_map($ws, $headerRow);
$records = [];
$catalogCache = [];
$fromRow = $fromRowArg > 0 ? max($startRow, $fromRowArg) : $startRow;
$toRow = $toRowArg > 0 ? min($ws->getHighestDataRow(), $toRowArg) : $ws->getHighestDataRow();
for ($row = $fromRow; $row <= $toRow; $row++) {
    $r = record_from_sheet($ws, $header, $row, $idMexico);
    if ($r['nombre_completo'] === '' && $r['codigo_contpaq'] === '' && $r['curp'] === '' && $r['rfc'] === '') {
        $stats['omitidas_vacias']++;
        continue;
    }
    $stats['leidas']++;
    $missing = [];
    foreach (['nombre_completo', 'nombres', 'apellidop'] as $field) {
        if (($r[$field] ?? '') === '') {
            $missing[] = $field;
        }
    }
    if ($missing) {
        $stats['omitidas_incompletas']++;
        if (count($issues) < 50) {
            $issues[] = "Fila {$row} incompleta: " . implode(', ', $missing);
        }
        continue;
    }

    [$person, $reason] = find_person($people, $r);
    if (!$person && str_starts_with($reason, 'ambiguo')) {
        $stats['ambiguas']++;
        if (count($issues) < 50) {
            $issues[] = "Fila {$row} ambigua ({$reason}): {$r['nombre_completo']}";
        }
        continue;
    }
        if ($person) {
            $stats['existentes_actualizar']++;
        } else {
            $stats['nuevas_insertar']++;
            if ($listNew && count($issues) < 100) {
                $issues[] = "Nueva fila {$row}: {$r['nombre_completo']} | codigo={$r['codigo_contpaq']} | curp={$r['curp']} | rfc={$r['rfc']} | correo={$r['correo']}";
            }
        }

    $catalogKey = implode('|', [$r['id_pais'], keyn($r['direccion']), keyn($r['area']), keyn($r['departamento']), keyn($r['puesto'])]);
    if (!isset($catalogCache[$catalogKey])) {
        $catalogCache[$catalogKey] = resolve_catalog($db, $r);
        if ((int)$catalogCache[$catalogKey]['id_puesto'] <= 0) {
            $stats['catalogo_sin_match']++;
            if (count($issues) < 50) {
                $issues[] = "Catalogo sin puesto exacto fila {$row}: {$r['direccion']} > {$r['area']} > {$r['departamento']} > {$r['puesto']}";
            }
        }
    }
    $records[] = [$r, $person, $catalogCache[$catalogKey]];
}

foreach ($records as [$r, $person, $catalog]) {
    $attempt = 0;
    while (true) {
        $attempt++;
        $delta = [
            'actualizadas' => 0,
            'insertadas' => 0,
            'rrhh' => 0,
            'telefonos' => 0,
            'correos' => 0,
            'domicilios' => 0,
            'cuentas' => 0,
            'creditos' => 0,
            'contactos' => 0,
            'puestos' => 0,
            'bajas_registradas' => 0,
        ];
        try {
            if ($apply) {
                $db->beginTransaction();
            }

            $idPersona = upsert_person($db, $r, $person, $catalog, $people['usernames'], $apply);
            if ($person) {
                $delta['actualizadas']++;
                $idPersona = (int)$person['id'];
            } else {
                $delta['insertadas']++;
            }

            if ($idPersona > 0 || $apply) {
                upsert_rrhh($db, $idPersona, $r, $catalog, $apply);
                $delta['rrhh']++;
                insert_related($db, $idPersona, $r, $apply, $delta);
                ensure_position_and_baja($db, $idPersona, $r, $catalog, $apply, $delta);
            }

            if ($apply) {
                $db->commit();
                if (!$person && $idPersona > 0) {
                    index_imported_person($people, $idPersona, $r);
                }
            }
            foreach ($delta as $key => $value) {
                $stats[$key] += $value;
            }
            break;
        } catch (Throwable $e) {
            if ($apply && $db->inTransaction()) {
                try {
                    $db->rollback();
                } catch (Throwable $rollbackError) {
                    fwrite(STDERR, "No se pudo revertir transaccion activa: " . $rollbackError->getMessage() . PHP_EOL);
                }
            }
            $message = $e->getMessage();
            $isDeadlock = str_contains($message, '1213') || stripos($message, 'Deadlock') !== false || str_contains($message, '40001');
            if ($apply && $isDeadlock && $attempt < 4) {
                usleep(200000 * $attempt);
                continue;
            }
            fwrite(STDERR, "Error en {$r['nombre_completo']} fila {$r['row']}: " . $message . PHP_EOL);
            exit(2);
        }
    }
}

echo 'Modo: ' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;
echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($issues && $showIssues) {
    echo "Observaciones:\n";
    foreach ($issues as $issue) {
        echo '- ' . $issue . PHP_EOL;
    }
}
