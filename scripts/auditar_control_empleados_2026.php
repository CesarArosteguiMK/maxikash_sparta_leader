<?php

declare(strict_types=1);

use Core\Database;

require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

function a_txt(mixed $value): string
{
    $text = trim((string)($value ?? ''));
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function a_key(mixed $value): string
{
    $text = a_txt($value);
    if ($text === '') return '';
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtoupper($text);
    $text = str_replace('&', 'Y', $text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function a_clean(mixed $value): string
{
    $text = a_txt($value);
    $key = a_key($text);
    if ($key === '' || in_array($key, ['N A', 'NA', 'S N', 'SN', 'NO APLICA', 'NULL', 'NONE'], true)) {
        return '';
    }
    return $text;
}

function employee_key_a(mixed $value): string
{
    $key = a_key($value);
    if ($key !== '' && ctype_digit($key)) {
        return ltrim($key, '0') ?: '0';
    }
    return $key;
}

function add_idx(array &$idx, string $type, string $key, array $row): void
{
    if ($key === '' || str_ends_with($key, '|')) return;
    $idx[$type][$key][] = $row;
}

function build_idx(array $people): array
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
    foreach ($people as $p) {
        $pais = (int)($p['id_pais'] ?? 1);
        add_idx($idx, 'numero_empleado', $pais . '|' . employee_key_a($p['numero_empleado'] ?? ''), $p);
        add_idx($idx, 'codigo_contpaq', $pais . '|' . a_clean($p['codigo_contpaq'] ?? ''), $p);
        add_idx($idx, 'curp', a_key($p['curp'] ?? ''), $p);
        add_idx($idx, 'rfc', a_key($p['rfc'] ?? ''), $p);
        add_idx($idx, 'nss', a_key($p['nss'] ?? ''), $p);
        add_idx($idx, 'correo', strtolower(a_clean($p['correo'] ?? '')), $p);
        add_idx($idx, 'nombre', $pais . '|' . a_key($p['nombre_completo'] ?? ''), $p);
    }
    return $idx;
}

function exact_match(array $idx, array $r): array
{
    $checks = [
        ['curp', a_key($r['curp'] ?? '')],
        ['rfc', a_key($r['rfc'] ?? '')],
        ['nss', a_key($r['nss'] ?? '')],
        ['correo', strtolower(a_clean($r['correo'] ?? ''))],
        ['codigo_contpaq', (int)$r['id_pais'] . '|' . a_clean($r['codigo_contpaq'] ?? '')],
        ['numero_empleado', (int)$r['id_pais'] . '|' . employee_key_a($r['codigo_contpaq'] ?? '')],
        ['nombre', (int)$r['id_pais'] . '|' . a_key($r['nombre_completo'] ?? '')],
    ];
    foreach ($checks as [$reason, $target]) {
        if ($target === '' || str_ends_with($target, '|')) continue;
        $matches = $idx[$reason][$target] ?? [];
        if (count($matches) === 1) return [$matches[0], $reason, []];
        if (count($matches) > 1 && !in_array($reason, ['curp', 'rfc', 'nss'], true)) return [null, 'ambiguo:' . $reason, $matches];
    }
    return [null, 'sin_match', []];
}

function candidate_score(array $r, array $p): int
{
    $score = 0;
    if (employee_key_a($r['codigo_contpaq'] ?? '') !== '' && employee_key_a($r['codigo_contpaq'] ?? '') === employee_key_a($p['numero_empleado'] ?? '')) $score += 50;
    if (a_key($r['curp'] ?? '') !== '' && a_key($r['curp'] ?? '') === a_key($p['curp'] ?? '')) $score += 100;
    if (a_key($r['rfc'] ?? '') !== '' && a_key($r['rfc'] ?? '') === a_key($p['rfc'] ?? '')) $score += 80;
    if (a_key($r['nss'] ?? '') !== '' && a_key($r['nss'] ?? '') === a_key($p['nss'] ?? '')) $score += 80;
    if (strtolower(a_clean($r['correo'] ?? '')) !== '' && strtolower(a_clean($r['correo'] ?? '')) === strtolower(a_clean($p['correo'] ?? ''))) $score += 70;
    similar_text(a_key($r['nombre_completo'] ?? ''), a_key($p['nombre_completo'] ?? ''), $pct);
    $score += (int)round($pct / 2);
    return $score;
}

function top_candidates(array $r, array $people): array
{
    $candidates = [];
    foreach ($people as $p) {
        if ((int)($p['id_pais'] ?? 1) !== (int)($r['id_pais'] ?? 1)) continue;
        $score = candidate_score($r, $p);
        if ($score >= 35) {
            $candidates[] = [
                'score' => $score,
                'id' => (int)$p['id'],
                'numero_empleado' => $p['numero_empleado'] ?? '',
                'codigo_contpaq' => $p['codigo_contpaq'] ?? '',
                'nombre' => $p['nombre_completo'] ?? '',
                'estatus' => $p['estatus'] ?? '',
                'curp' => $p['curp'] ?? '',
                'rfc' => $p['rfc'] ?? '',
                'correo' => $p['correo'] ?? '',
            ];
        }
    }
    usort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);
    return array_slice($candidates, 0, 3);
}

function duplicated_name_cases(array $people): array
{
    $cases = [];
    foreach ($people as $p) {
        $nombres = a_txt($p['nombres'] ?? '');
        $segundo = a_txt($p['segundo_nombre'] ?? '');
        if ($nombres === '' || $segundo === '') continue;
        $nameTokens = preg_split('/\s+/', a_key($nombres)) ?: [];
        $secondTokens = preg_split('/\s+/', a_key($segundo)) ?: [];
        if (!$nameTokens || !$secondTokens) continue;
        $tail = array_slice($nameTokens, -count($secondTokens));
        if ($tail === $secondTokens) {
            $cases[] = [
                'id' => (int)$p['id'],
                'numero_empleado' => $p['numero_empleado'] ?? '',
                'nombres' => $nombres,
                'segundo_nombre' => $segundo,
                'apellidop' => $p['apellidop'] ?? '',
                'apellidom' => $p['apellidom'] ?? '',
                'nombre_actual' => $p['nombre_completo'] ?? '',
                'nombre_corregido' => trim($nombres . ' ' . ($p['apellidop'] ?? '') . ' ' . ($p['apellidom'] ?? '')),
            ];
        }
    }
    return $cases;
}

$path = '';
$fix = false;
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if ($arg === '--fix') $fix = true;
    elseif ($path === '') $path = $arg;
}
if ($path === '' || !is_readable($path)) {
    fwrite(STDERR, "Uso: php scripts/auditar_control_empleados_2026.php <control.json> [--fix]\n");
    exit(1);
}

$records = json_decode((string)file_get_contents($path), true);
if (!is_array($records)) {
    fwrite(STDERR, "JSON inválido.\n");
    exit(1);
}

$db = new Database();
$people = $db->queryAll("
    SELECT p.id, COALESCE(p.id_pais, 1) AS id_pais, p.numero_empleado, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom,
           p.curp, p.correo, p.estatus,
           rr.codigo_contpaq, rr.rfc, rr.nss,
           TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo
    FROM __SPARTA_SECRET_REDACTED__.persona p
    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
    WHERE UPPER(TRIM(COALESCE(p.estatus, ''))) <> 'BAJA'
");
$allPeople = $db->queryAll("
    SELECT p.id, COALESCE(p.id_pais, 1) AS id_pais, p.numero_empleado, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom,
           p.curp, p.correo, p.estatus,
           rr.codigo_contpaq, rr.rfc, rr.nss,
           TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo
    FROM __SPARTA_SECRET_REDACTED__.persona p
    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
");
$idx = build_idx($people);
$allIdx = build_idx($allPeople);

$sin = [];
$amb = [];
foreach ($records as $r) {
    [$person, $reason, $matches] = exact_match($idx, $r);
    if ($person) continue;
    $entry = [
        'sheet' => $r['sheet'] ?? '',
        'row' => (int)($r['row'] ?? 0),
        'nombre' => $r['nombre_completo'] ?? '',
        'codigo_contpaq' => $r['codigo_contpaq'] ?? '',
        'curp' => $r['curp'] ?? '',
        'rfc' => $r['rfc'] ?? '',
        'correo' => $r['correo'] ?? '',
        'razon' => $reason,
    ];
    if (str_starts_with($reason, 'ambiguo:')) {
        $entry['matches'] = array_map(fn($m) => [
            'id' => (int)$m['id'],
            'numero_empleado' => $m['numero_empleado'] ?? '',
            'codigo_contpaq' => $m['codigo_contpaq'] ?? '',
            'nombre' => $m['nombre_completo'] ?? '',
        ], $matches);
        $amb[] = $entry;
    } else {
        [$inactiveMatch, $inactiveReason] = exact_match($allIdx, $r);
        if ($inactiveMatch) {
            $entry['coincidencia_no_activa'] = [
                'razon' => $inactiveReason,
                'id' => (int)$inactiveMatch['id'],
                'numero_empleado' => $inactiveMatch['numero_empleado'] ?? '',
                'codigo_contpaq' => $inactiveMatch['codigo_contpaq'] ?? '',
                'nombre' => $inactiveMatch['nombre_completo'] ?? '',
                'estatus' => $inactiveMatch['estatus'] ?? '',
            ];
        }
        $entry['candidatos'] = top_candidates($r, $people);
        $sin[] = $entry;
    }
}

$dup = duplicated_name_cases($people);
$fixed = 0;
if ($fix && $dup) {
    $db->beginTransaction();
    try {
        foreach ($dup as $case) {
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.persona SET segundo_nombre = '' WHERE id = :id",
                ['id' => $case['id']]
            );
            $fixed++;
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        fwrite(STDERR, "Error al corregir duplicados: " . $e->getMessage() . PHP_EOL);
        exit(2);
    }
}

echo json_encode([
    'modo' => $fix ? 'FIX' : 'DRY-RUN',
    'resumen' => [
        'registros_excel' => count($records),
        'sin_match' => count($sin),
        'ambiguos' => count($amb),
        'duplicados_nombre_detectados' => count($dup),
        'duplicados_nombre_corregidos' => $fixed,
    ],
    'sin_match' => $sin,
    'ambiguos' => $amb,
    'duplicados_nombre' => $dup,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
