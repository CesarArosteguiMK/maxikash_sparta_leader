<?php

declare(strict_types=1);

use Core\Database;

require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

function j_txt(mixed $value): string
{
    $text = trim((string)($value ?? ''));
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function j_key(mixed $value): string
{
    $text = j_txt($value);
    if ($text === '') return '';
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtoupper($text);
    $text = str_replace('&', 'Y', $text);
    $text = str_replace(
        ['JENNIFER', 'VELASCO', 'MAYORIAL', ' GTZ ', ' RDZ '],
        ['JENIFER', 'VELAZCO', 'MAYORAL', ' GUTIERREZ ', ' RODRIGUEZ '],
        ' ' . $text . ' '
    );
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function j_clean(mixed $value): string
{
    $text = j_txt($value);
    $key = j_key($text);
    if ($key === '' || in_array($key, ['N A', 'NA', 'S N', 'SN', 'NO APLICA', 'NULL', 'NONE'], true)) {
        return '';
    }
    return $text;
}

function j_employee_key(mixed $value): string
{
    $key = j_key($value);
    if ($key !== '' && ctype_digit($key)) {
        return ltrim($key, '0') ?: '0';
    }
    return $key;
}

function j_add_idx(array &$idx, string $type, string $key, array $row): void
{
    if ($key === '' || str_ends_with($key, '|')) return;
    $idx[$type][$key][] = $row;
}

function j_build_person_idx(array $people): array
{
    $idx = [
        'curp' => [],
        'rfc' => [],
        'nss' => [],
        'correo' => [],
        'codigo_contpaq' => [],
        'numero_empleado' => [],
        'nombre' => [],
    ];
    foreach ($people as $p) {
        $pais = (int)($p['id_pais'] ?? 1);
        j_add_idx($idx, 'curp', j_key($p['curp'] ?? ''), $p);
        j_add_idx($idx, 'rfc', j_key($p['rfc'] ?? ''), $p);
        j_add_idx($idx, 'nss', j_key($p['nss'] ?? ''), $p);
        j_add_idx($idx, 'correo', strtolower(j_clean($p['correo'] ?? '')), $p);
        j_add_idx($idx, 'codigo_contpaq', $pais . '|' . j_clean($p['codigo_contpaq'] ?? ''), $p);
        j_add_idx($idx, 'numero_empleado', $pais . '|' . j_employee_key($p['numero_empleado'] ?? ''), $p);
        j_add_idx($idx, 'nombre', $pais . '|' . j_key($p['nombre_completo'] ?? ''), $p);
    }
    return $idx;
}

function j_find_person(array $idx, array $r): array
{
    $checks = [
        ['curp', j_key($r['curp'] ?? '')],
        ['rfc', j_key($r['rfc'] ?? '')],
        ['nss', j_key($r['nss'] ?? '')],
        ['correo', strtolower(j_clean($r['correo'] ?? ''))],
        ['codigo_contpaq', (int)($r['id_pais'] ?? 1) . '|' . j_clean($r['codigo_contpaq'] ?? '')],
        ['numero_empleado', (int)($r['id_pais'] ?? 1) . '|' . j_employee_key($r['codigo_contpaq'] ?? '')],
        ['nombre', (int)($r['id_pais'] ?? 1) . '|' . j_key($r['nombre_completo'] ?? '')],
    ];
    foreach ($checks as [$reason, $target]) {
        if ($target === '' || str_ends_with($target, '|')) continue;
        $matches = $idx[$reason][$target] ?? [];
        if (count($matches) === 1) return [$matches[0], $reason];
        if (count($matches) > 1 && !in_array($reason, ['curp', 'rfc', 'nss'], true)) {
            return [null, 'ambiguo:' . $reason];
        }
    }
    return [null, 'sin_match'];
}

function j_latest_jefes(Database $db): array
{
    $map = [];
    foreach ($db->queryAll("
        SELECT aj.id_persona, aj.id_jefe, aj.id_vacante_jefe
        FROM __SPARTA_SECRET_REDACTED__.asigna_jefe aj
        INNER JOIN (
            SELECT id_persona, MAX(id) AS id
            FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
            GROUP BY id_persona
        ) ult ON ult.id = aj.id
    ") as $row) {
        $map[(int)$row['id_persona']] = $row;
    }
    return $map;
}

function j_resolve_jefe(string $jefeTexto, array $jefesByName, array $people): array
{
    $targetKey = j_key($jefeTexto);
    if ($targetKey === '') return [null, 'jefe_no_resuelto', []];

    $aliasJefes = [
        'ALEJANDRO PAEZ' => 'ALEJANDRO ROMULO PAEZ EUSEBIO',
    ];
    if (isset($aliasJefes[$targetKey])) {
        $aliasKey = j_key($aliasJefes[$targetKey]);
        $aliasMatches = $jefesByName[$aliasKey] ?? [];
        if (count($aliasMatches) === 1) return [$aliasMatches[0], 'alias', []];
        if (count($aliasMatches) > 1) return [null, 'jefe_ambiguo', $aliasMatches];
    }

    $exact = $jefesByName[$targetKey] ?? [];
    if (count($exact) === 1) return [$exact[0], 'exacto', []];
    if (count($exact) > 1) return [null, 'jefe_ambiguo', $exact];

    $tokens = array_values(array_filter(preg_split('/\s+/', $targetKey) ?: [], fn($t) => strlen($t) > 1));
    if (count($tokens) < 2) return [null, 'jefe_no_resuelto', []];

    $contains = [];
    foreach ($people as $person) {
        $nameKey = j_key($person['nombre_completo'] ?? '');
        if ($nameKey === '') continue;
        $ok = true;
        foreach ($tokens as $token) {
            if (!str_contains($nameKey, $token)) {
                $ok = false;
                break;
            }
        }
        if ($ok) $contains[] = $person;
    }
    if (count($contains) === 1) return [$contains[0], 'tokens', []];
    if (count($contains) > 1) return [null, 'jefe_ambiguo', $contains];

    $scored = [];
    foreach ($people as $person) {
        $nameKey = j_key($person['nombre_completo'] ?? '');
        if ($nameKey === '') continue;
        similar_text($targetKey, $nameKey, $pct);
        if ($pct >= 74) {
            $scored[] = ['score' => $pct, 'person' => $person];
        }
    }
    usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
    if (!$scored) return [null, 'jefe_no_resuelto', []];

    $best = $scored[0];
    $secondScore = $scored[1]['score'] ?? 0;
    if ($best['score'] >= 82 && ($best['score'] - $secondScore) >= 8) {
        return [$best['person'], 'fuzzy', []];
    }

    return [null, 'jefe_ambiguo', array_map(fn($row) => $row['person'], array_slice($scored, 0, 5))];
}

function j_fecha_cdmx(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
}

$path = '';
$apply = false;
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if ($arg === '--apply') $apply = true;
    elseif ($path === '') $path = $arg;
}
if ($path === '' || !is_readable($path)) {
    fwrite(STDERR, "Uso: php scripts/asignar_jefes_no_cobranza_desde_excel_2026.php <control.json> [--apply]\n");
    exit(1);
}

$records = json_decode((string)file_get_contents($path), true);
if (!is_array($records)) {
    fwrite(STDERR, "JSON inválido.\n");
    exit(1);
}

$db = new Database();
$people = $db->queryAll("
    SELECT p.id, COALESCE(p.id_pais, 1) AS id_pais, p.numero_empleado, p.curp, p.correo, p.estatus,
           rr.codigo_contpaq, rr.rfc, rr.nss, rr.id_jefe,
           TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo
    FROM __SPARTA_SECRET_REDACTED__.persona p
    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
    WHERE UPPER(TRIM(COALESCE(p.estatus, ''))) <> 'BAJA'
");
$personIdx = j_build_person_idx($people);
$peopleById = [];
$jefesByName = [];
foreach ($people as $p) {
    $id = (int)$p['id'];
    $peopleById[$id] = $p;
    $nameKey = j_key($p['nombre_completo'] ?? '');
    if ($nameKey !== '') $jefesByName[$nameKey][] = $p;
}
$latestJefes = j_latest_jefes($db);

$stats = [
    'registros_excel' => count($records),
    'omitidos_cobranza' => 0,
    'omitidos_sin_jefe_en_excel' => 0,
    'persona_no_resuelta' => 0,
    'persona_ambigua' => 0,
    'ya_tenia_jefe' => 0,
    'jefe_no_resuelto' => 0,
    'jefe_ambiguo' => 0,
    'jefe_es_la_misma_persona' => 0,
    'listos_para_asignar' => 0,
    'asignados' => 0,
];
$preview = [];
$pendientes = [];

if ($apply) $db->beginTransaction();
try {
    foreach ($records as $r) {
        if (j_key($r['direccion'] ?? '') === 'COBRANZA') {
            $stats['omitidos_cobranza']++;
            continue;
        }
        $jefeTexto = j_clean($r['jefe_directo_texto'] ?? '');
        if ($jefeTexto === '') {
            $stats['omitidos_sin_jefe_en_excel']++;
            continue;
        }

        [$persona, $reason] = j_find_person($personIdx, $r);
        if (!$persona) {
            if (str_starts_with($reason, 'ambiguo:')) $stats['persona_ambigua']++;
            else $stats['persona_no_resuelta']++;
            $pendientes[] = [
                'tipo' => $reason,
                'persona_excel' => $r['nombre_completo'] ?? '',
                'direccion' => $r['direccion'] ?? '',
                'jefe_excel' => $jefeTexto,
            ];
            continue;
        }

        $idPersona = (int)$persona['id'];
        $jefeActual = $latestJefes[$idPersona]['id_jefe'] ?? ($persona['id_jefe'] ?? null);
        if ((int)$jefeActual > 0) {
            $stats['ya_tenia_jefe']++;
            continue;
        }

        [$jefe, $jefeReason, $matchesJefe] = j_resolve_jefe($jefeTexto, $jefesByName, $people);
        if (!$jefe && $jefeReason === 'jefe_no_resuelto') {
            $stats['jefe_no_resuelto']++;
            $pendientes[] = [
                'tipo' => 'jefe_no_resuelto',
                'persona_id' => $idPersona,
                'persona' => $persona['nombre_completo'] ?? '',
                'direccion' => $r['direccion'] ?? '',
                'jefe_excel' => $jefeTexto,
            ];
            continue;
        }
        if (!$jefe && $jefeReason === 'jefe_ambiguo') {
            $stats['jefe_ambiguo']++;
            $pendientes[] = [
                'tipo' => 'jefe_ambiguo',
                'persona_id' => $idPersona,
                'persona' => $persona['nombre_completo'] ?? '',
                'direccion' => $r['direccion'] ?? '',
                'jefe_excel' => $jefeTexto,
                'matches' => array_map(fn($m) => ['id' => (int)$m['id'], 'nombre' => $m['nombre_completo'] ?? '', 'numero_empleado' => $m['numero_empleado'] ?? ''], $matchesJefe),
            ];
            continue;
        }
        $idJefe = (int)$jefe['id'];
        if ($idJefe === $idPersona) {
            $stats['jefe_es_la_misma_persona']++;
            continue;
        }

        $stats['listos_para_asignar']++;
        $item = [
            'persona_id' => $idPersona,
            'persona' => $persona['nombre_completo'] ?? '',
            'direccion' => $r['direccion'] ?? '',
            'jefe_id' => $idJefe,
            'jefe' => $jefe['nombre_completo'] ?? '',
            'match_jefe_por' => $jefeReason,
        ];
        if (count($preview) < 80) $preview[] = $item;

        if ($apply) {
            $fecha = j_fecha_cdmx();
            $db->CRUD("
                INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_jefe (id, id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin)
                VALUES (DEFAULT, :id_persona, :id_jefe, NULL, :fecha, :fecha)
            ", ['id_persona' => $idPersona, 'id_jefe' => $idJefe, 'fecha' => $fecha]);
            $db->CRUD("
                INSERT INTO __SPARTA_SECRET_REDACTED__.persona_datos_rrhh (id_persona, id_jefe, jefe_directo_texto)
                VALUES (:id_persona, :id_jefe, :jefe_texto)
                ON DUPLICATE KEY UPDATE id_jefe = VALUES(id_jefe), jefe_directo_texto = VALUES(jefe_directo_texto)
            ", ['id_persona' => $idPersona, 'id_jefe' => $idJefe, 'jefe_texto' => $jefeTexto]);
            $latestJefes[$idPersona] = ['id_persona' => $idPersona, 'id_jefe' => $idJefe, 'id_vacante_jefe' => null];
            $stats['asignados']++;
        }
    }

    if ($apply) $db->commit();
} catch (Throwable $e) {
    if ($apply) $db->rollback();
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
    exit(2);
}

echo json_encode([
    'modo' => $apply ? 'APPLY' : 'DRY-RUN',
    'stats' => $stats,
    'preview_asignaciones' => $preview,
    'pendientes' => array_slice($pendientes, 0, 120),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
