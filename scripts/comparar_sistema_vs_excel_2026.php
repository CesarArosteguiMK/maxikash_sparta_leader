<?php

declare(strict_types=1);

use Core\Database;

require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

function cmp_txt(mixed $value): string
{
    $text = trim((string)($value ?? ''));
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function cmp_key(mixed $value): string
{
    $text = cmp_txt($value);
    if ($text === '') return '';
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtoupper($text);
    $text = str_replace('&', 'Y', $text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function cmp_clean(mixed $value): string
{
    $text = cmp_txt($value);
    $key = cmp_key($text);
    if ($key === '' || in_array($key, ['N A', 'NA', 'S N', 'SN', 'NO APLICA', 'NULL', 'NONE'], true)) {
        return '';
    }
    return $text;
}

function cmp_employee_key(mixed $value): string
{
    $key = cmp_key($value);
    if ($key !== '' && ctype_digit($key)) {
        return ltrim($key, '0') ?: '0';
    }
    return $key;
}

function cmp_add_idx(array &$idx, string $type, string $key, array $row): void
{
    if ($key === '' || str_ends_with($key, '|')) return;
    $idx[$type][$key][] = $row;
}

function cmp_build_excel_idx(array $records): array
{
    $idx = [
        'numero_empleado' => [],
        'codigo_contpaq' => [],
        'curp' => [],
        'rfc' => [],
        'nss' => [],
        'correo' => [],
        'nombre' => [],
    ];
    foreach ($records as $r) {
        $pais = (int)($r['id_pais'] ?? 1);
        cmp_add_idx($idx, 'numero_empleado', $pais . '|' . cmp_employee_key($r['codigo_contpaq'] ?? ''), $r);
        cmp_add_idx($idx, 'codigo_contpaq', $pais . '|' . cmp_clean($r['codigo_contpaq'] ?? ''), $r);
        cmp_add_idx($idx, 'curp', cmp_key($r['curp'] ?? ''), $r);
        cmp_add_idx($idx, 'rfc', cmp_key($r['rfc'] ?? ''), $r);
        cmp_add_idx($idx, 'nss', cmp_key($r['nss'] ?? ''), $r);
        cmp_add_idx($idx, 'correo', strtolower(cmp_clean($r['correo'] ?? '')), $r);
        cmp_add_idx($idx, 'nombre', $pais . '|' . cmp_key($r['nombre_completo'] ?? ''), $r);
    }
    return $idx;
}

function cmp_find_excel_match(array $idx, array $p): array
{
    $checks = [
        ['curp', cmp_key($p['curp'] ?? '')],
        ['rfc', cmp_key($p['rfc'] ?? '')],
        ['nss', cmp_key($p['nss'] ?? '')],
        ['correo', strtolower(cmp_clean($p['correo'] ?? ''))],
        ['codigo_contpaq', (int)($p['id_pais'] ?? 1) . '|' . cmp_clean($p['codigo_contpaq'] ?? '')],
        ['numero_empleado', (int)($p['id_pais'] ?? 1) . '|' . cmp_employee_key($p['numero_empleado'] ?? '')],
        ['nombre', (int)($p['id_pais'] ?? 1) . '|' . cmp_key($p['nombre_completo'] ?? '')],
    ];
    foreach ($checks as [$reason, $target]) {
        if ($target === '' || str_ends_with($target, '|')) continue;
        $matches = $idx[$reason][$target] ?? [];
        if (count($matches) >= 1) return [$matches[0], $reason, count($matches)];
    }
    return [null, 'sin_match', 0];
}

$path = $_SERVER['argv'][1] ?? '';
if ($path === '' || !is_readable($path)) {
    fwrite(STDERR, "Uso: php scripts/comparar_sistema_vs_excel_2026.php <control.json>\n");
    exit(1);
}

$records = json_decode((string)file_get_contents($path), true);
if (!is_array($records)) {
    fwrite(STDERR, "JSON inválido.\n");
    exit(1);
}

$db = new Database();
$system = $db->queryAll("
    SELECT
        p.id,
        COALESCE(p.id_pais, 1) AS id_pais,
        p.numero_empleado,
        p.nombres,
        p.segundo_nombre,
        p.apellidop,
        p.apellidom,
        p.curp,
        p.correo,
        p.estatus,
        p.user_name,
        rr.codigo_contpaq,
        rr.rfc,
        rr.nss,
        COALESCE(pu.nombre, rr.puesto_texto, 'Sin puesto') AS puesto,
        COALESCE(dep.nombre, rr.departamento_texto, 'Sin departamento') AS departamento,
        COALESCE(area.nombre, rr.area_texto, 'Sin área') AS area,
        TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo
    FROM __SPARTA_SECRET_REDACTED__.persona p
    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
    LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap
           ON ap.id = (
                SELECT ap2.id
                FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap2
                WHERE ap2.id_persona = p.id
                  AND COALESCE(ap2.activo, 1) = 1
                ORDER BY ap2.id DESC
                LIMIT 1
           )
    LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pu ON pu.id = ap.id_puesto
    LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento dep ON dep.id = pu.departamento_id
    LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento_organizacional area ON area.id = dep.id_departamento_organizacional
    WHERE UPPER(TRIM(COALESCE(p.estatus, ''))) <> 'BAJA'
    ORDER BY p.id ASC
");

$idx = cmp_build_excel_idx($records);
$sheetCounts = [];
foreach ($records as $r) {
    $sheet = $r['sheet'] ?? 'Sin hoja';
    $sheetCounts[$sheet] = ($sheetCounts[$sheet] ?? 0) + 1;
}

$matched = [];
$sobran = [];
foreach ($system as $p) {
    [$record, $reason, $count] = cmp_find_excel_match($idx, $p);
    if ($record) {
        $matched[] = [
            'id' => (int)$p['id'],
            'nombre' => $p['nombre_completo'] ?? '',
            'match_por' => $reason,
            'excel_fila' => $record['row'] ?? null,
            'excel_hoja' => $record['sheet'] ?? '',
        ];
        continue;
    }
    $sobran[] = [
        'id' => (int)$p['id'],
        'numero_empleado' => $p['numero_empleado'] ?? '',
        'codigo_contpaq' => $p['codigo_contpaq'] ?? '',
        'nombre' => $p['nombre_completo'] ?? '',
        'usuario' => $p['user_name'] ?? '',
        'correo' => $p['correo'] ?? '',
        'curp' => $p['curp'] ?? '',
        'rfc' => $p['rfc'] ?? '',
        'nss' => $p['nss'] ?? '',
        'puesto' => $p['puesto'] ?? '',
        'departamento' => $p['departamento'] ?? '',
        'area' => $p['area'] ?? '',
        'estatus' => $p['estatus'] ?? '',
    ];
}

echo json_encode([
    'resumen' => [
        'excel_registros' => count($records),
        'excel_por_hoja' => $sheetCounts,
        'sistema_activos' => count($system),
        'sistema_con_match_excel' => count($matched),
        'sistema_sin_match_excel_sobran' => count($sobran),
    ],
    'sobran_en_sistema' => $sobran,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
