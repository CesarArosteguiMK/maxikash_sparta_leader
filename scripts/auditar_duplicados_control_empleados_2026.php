<?php

declare(strict_types=1);

use Core\Database;

require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

function d_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $arg) {
        if ($arg === $name) return '1';
        if (str_starts_with($arg, $name . '=')) return substr($arg, strlen($name) + 1);
    }
    return $default;
}

function d_txt(mixed $value): string
{
    $text = trim((string)($value ?? ''));
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

function d_key(mixed $value): string
{
    $text = d_txt($value);
    if ($text === '') return '';
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtoupper($text);
    $text = str_replace('&', 'Y', $text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function d_nombre(array $row): string
{
    return d_txt(implode(' ', array_filter([
        $row['nombres'] ?? '',
        $row['segundo_nombre'] ?? '',
        $row['apellidop'] ?? '',
        $row['apellidom'] ?? '',
    ], static fn($v) => d_txt($v) !== '')));
}

function d_firma_nombre(string $nombre): string
{
    $tokens = array_values(array_filter(
        preg_split('/\s+/', d_key($nombre)) ?: [],
        static fn($token) => strlen($token) > 1
    ));
    sort($tokens, SORT_STRING);
    return implode(' ', $tokens);
}

function d_numero_generado(mixed $numero): bool
{
    $num = d_txt($numero);
    return $num !== '' && ctype_digit($num) && (int)$num >= 1200 && (int)$num < 100000;
}

function d_tiene_jefe(array $row): bool
{
    return (int)($row['id_jefe'] ?? 0) > 0 || d_txt($row['jefe_directo_texto'] ?? '') !== '';
}

function d_score_conservar(array $row, array $excelCodes): int
{
    $score = 0;
    if (d_tiene_jefe($row)) $score += 35;
    if (!d_numero_generado($row['numero_empleado'] ?? '')) $score += 25;
    if (d_txt($row['curp'] ?? '') !== '') $score += 20;
    if (d_txt($row['rfc'] ?? '') !== '') $score += 15;
    if (d_txt($row['nss'] ?? '') !== '') $score += 15;
    if (d_txt($row['correo'] ?? '') !== '') $score += 10;

    $numero = d_txt($row['numero_empleado'] ?? '');
    $contpaq = d_txt($row['codigo_contpaq'] ?? '');
    if ($numero !== '' && isset($excelCodes[$numero])) $score += 10;
    if ($contpaq !== '' && isset($excelCodes[$contpaq])) $score += 8;

    $score -= (int)min(20, max(0, (int)($row['id'] ?? 0) - 1300) / 20);
    return (int)$score;
}

function d_persona_por_numero(Database $db, string $numero): ?array
{
    $row = $db->queryOne("
        SELECT
            p.id,
            p.numero_empleado,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            p.user_name,
            p.correo,
            p.curp,
            p.telefono_uno,
            p.telefono_dos,
            p.estatus,
            rr.codigo_contpaq,
            rr.rfc,
            rr.nss,
            rr.fecha_nacimiento,
            rr.sexo,
            rr.entidad_federativa_rfc,
            pp.nombre AS nombre_puesto,
            dep.nombre AS nombre_departamento,
            jefe.numero_empleado AS numero_jefe,
            CONCAT_WS(' ', jefe.nombres, jefe.segundo_nombre, jefe.apellidop, jefe.apellidom) AS nombre_jefe
        FROM __SPARTA_SECRET_REDACTED__.persona p
        LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
        LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
        LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento dep ON dep.id = pp.departamento_id
        LEFT JOIN (
            SELECT a.id_persona, a.id_jefe
            FROM __SPARTA_SECRET_REDACTED__.asigna_jefe a
            INNER JOIN (
                SELECT id_persona, MAX(id) AS mid
                FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                GROUP BY id_persona
            ) m ON m.id_persona = a.id_persona AND m.mid = a.id
        ) aj ON aj.id_persona = p.id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.persona jefe ON jefe.id = aj.id_jefe
        WHERE TRIM(p.numero_empleado) = :numero
        LIMIT 1
    ", ['numero' => $numero]);
    if (!$row) return null;
    $row['nombre_completo'] = d_nombre($row);
    return $row;
}

function d_corregir_duplicado_forzado(Database $db, string $bajaNumero, string $conservarNumero): array
{
    $baja = d_persona_por_numero($db, $bajaNumero);
    $conservar = d_persona_por_numero($db, $conservarNumero);
    if (!$baja) throw new RuntimeException("No existe persona con numero_empleado {$bajaNumero}.");
    if (!$conservar) throw new RuntimeException("No existe persona a conservar con numero_empleado {$conservarNumero}.");

    $copiados = [];
    $db->beginTransaction();
    try {
        $camposPersona = ['correo', 'curp', 'telefono_uno', 'telefono_dos'];
        $set = [];
        $params = ['id' => (int)$conservar['id']];
        foreach ($camposPersona as $campo) {
            if (d_txt($conservar[$campo] ?? '') === '' && d_txt($baja[$campo] ?? '') !== '') {
                $set[] = "{$campo} = :{$campo}";
                $params[$campo] = d_txt($baja[$campo]);
                $copiados[] = "persona.{$campo}";
            }
        }
        if ($set) {
            $db->CRUD('UPDATE __SPARTA_SECRET_REDACTED__.persona SET ' . implode(', ', $set) . ' WHERE id = :id', $params);
        }

        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.persona_datos_rrhh (id_persona, created_at)
            SELECT :id_persona, NOW()
            WHERE NOT EXISTS (
                SELECT 1 FROM __SPARTA_SECRET_REDACTED__.persona_datos_rrhh WHERE id_persona = :id_persona
            )
        ", ['id_persona' => (int)$conservar['id']]);

        $camposRrhh = ['codigo_contpaq', 'rfc', 'nss', 'fecha_nacimiento', 'sexo', 'entidad_federativa_rfc'];
        $set = [];
        $params = ['id_persona' => (int)$conservar['id']];
        foreach ($camposRrhh as $campo) {
            if (d_txt($conservar[$campo] ?? '') === '' && d_txt($baja[$campo] ?? '') !== '') {
                $set[] = "{$campo} = :{$campo}";
                $params[$campo] = d_txt($baja[$campo]);
                $copiados[] = "persona_datos_rrhh.{$campo}";
            }
        }
        if ($set) {
            $set[] = 'updated_at = NOW()';
            $db->CRUD('UPDATE __SPARTA_SECRET_REDACTED__.persona_datos_rrhh SET ' . implode(', ', $set) . ' WHERE id_persona = :id_persona', $params);
        }

        $db->CRUD("
            UPDATE __SPARTA_SECRET_REDACTED__.persona
            SET estatus = 'Baja', force_logout = 1
            WHERE id = :id
        ", ['id' => (int)$baja['id']]);

        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }

    return [
        'baja' => [
            'id' => (int)$baja['id'],
            'numero_empleado' => $baja['numero_empleado'] ?? '',
            'nombre' => $baja['nombre_completo'] ?? '',
        ],
        'conservado' => [
            'id' => (int)$conservar['id'],
            'numero_empleado' => $conservar['numero_empleado'] ?? '',
            'nombre' => $conservar['nombre_completo'] ?? '',
        ],
        'campos_copiados_si_estaban_vacios' => $copiados,
    ];
}

function d_load_excel_signatures(string $path): array
{
    if ($path === '' || !is_readable($path)) return [];
    $records = json_decode((string)file_get_contents($path), true);
    if (!is_array($records)) return [];

    $out = [];
    foreach ($records as $r) {
        if (!is_array($r)) continue;
        $nombre = d_txt($r['nombre_completo'] ?? '');
        $sig = d_firma_nombre($nombre);
        if ($sig === '') continue;
        $code = d_txt($r['codigo_contpaq'] ?? '');
        $out[$sig]['nombres'][] = $nombre;
        if ($code !== '') $out[$sig]['codigos'][$code] = true;
    }
    return $out;
}

$apply = d_arg($_SERVER['argv'] ?? [], '--apply') === '1';
$onlyId = (int)(d_arg($_SERVER['argv'] ?? [], '--id', '0') ?? 0);
$onlyNumero = d_txt(d_arg($_SERVER['argv'] ?? [], '--numero', '') ?? '');
$forzarBajaNumero = d_txt(d_arg($_SERVER['argv'] ?? [], '--baja-numero', '') ?? '');
$forzarConservarNumero = d_txt(d_arg($_SERVER['argv'] ?? [], '--conservar-numero', '') ?? '');
$excelPath = d_arg($_SERVER['argv'] ?? [], '--excel-json', 'storage/tmp/control_empleados_2026_auditoria.json') ?? '';

$db = new Database();
$excel = d_load_excel_signatures($excelPath);

if ($apply && $forzarBajaNumero !== '' && $forzarConservarNumero !== '') {
    echo json_encode([
        'modo' => 'APPLY-FORZADO',
        'resultado' => d_corregir_duplicado_forzado($db, $forzarBajaNumero, $forzarConservarNumero),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

$objetivoSinFiltro = null;
if ($onlyId > 0 || $onlyNumero !== '') {
    $whereObjetivo = $onlyId > 0 ? 'p.id = :id' : 'TRIM(p.numero_empleado) = :numero';
    $paramsObjetivo = $onlyId > 0 ? ['id' => $onlyId] : ['numero' => $onlyNumero];
    $objetivoSinFiltro = $db->queryOne("
        SELECT
            p.id,
            p.numero_empleado,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            p.user_name,
            p.correo,
            p.curp,
            p.estatus,
            rr.codigo_contpaq,
            rr.rfc,
            rr.nss,
            rr.id_jefe,
            rr.jefe_directo_texto,
            pp.nombre AS nombre_puesto,
            dep.nombre AS nombre_departamento,
            jefe.numero_empleado AS numero_jefe,
            CONCAT_WS(' ', jefe.nombres, jefe.segundo_nombre, jefe.apellidop, jefe.apellidom) AS nombre_jefe
        FROM __SPARTA_SECRET_REDACTED__.persona p
        LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
        LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
        LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento dep ON dep.id = pp.departamento_id
        LEFT JOIN (
            SELECT a.id_persona, a.id_jefe
            FROM __SPARTA_SECRET_REDACTED__.asigna_jefe a
            INNER JOIN (
                SELECT id_persona, MAX(id) AS mid
                FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                GROUP BY id_persona
            ) m ON m.id_persona = a.id_persona AND m.mid = a.id
        ) aj ON aj.id_persona = p.id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.persona jefe ON jefe.id = aj.id_jefe
        WHERE {$whereObjetivo}
        LIMIT 1
    ", $paramsObjetivo);
    if ($objetivoSinFiltro) {
        $objetivoSinFiltro['nombre_completo'] = d_nombre($objetivoSinFiltro);
        $objetivoSinFiltro['firma_nombre'] = d_firma_nombre($objetivoSinFiltro['nombre_completo']);
    }
}

$rows = $db->queryAll("
    SELECT
        p.id,
        p.numero_empleado,
        p.nombres,
        p.segundo_nombre,
        p.apellidop,
        p.apellidom,
        p.user_name,
        p.correo,
        p.curp,
        p.estatus,
        rr.codigo_contpaq,
        rr.rfc,
        rr.nss,
        rr.id_jefe,
        rr.jefe_directo_texto,
        GROUP_CONCAT(DISTINCT pp.nombre ORDER BY pp.nombre SEPARATOR ' | ') AS nombre_puesto,
        GROUP_CONCAT(DISTINCT dep.nombre ORDER BY dep.nombre SEPARATOR ' | ') AS nombre_departamento,
        jefe.numero_empleado AS numero_jefe,
        CONCAT_WS(' ', jefe.nombres, jefe.segundo_nombre, jefe.apellidop, jefe.apellidom) AS nombre_jefe
    FROM __SPARTA_SECRET_REDACTED__.persona p
    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona_datos_rrhh rr ON rr.id_persona = p.id
    LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
    LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
    LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento dep ON dep.id = pp.departamento_id
    LEFT JOIN (
        SELECT a.id_persona, a.id_jefe
        FROM __SPARTA_SECRET_REDACTED__.asigna_jefe a
        INNER JOIN (
            SELECT id_persona, MAX(id) AS mid
            FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
            GROUP BY id_persona
        ) m ON m.id_persona = a.id_persona AND m.mid = a.id
    ) aj ON aj.id_persona = p.id
    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona jefe ON jefe.id = aj.id_jefe
    WHERE COALESCE(p.estatus, '') <> 'Baja'
    GROUP BY
        p.id,
        p.numero_empleado,
        p.nombres,
        p.segundo_nombre,
        p.apellidop,
        p.apellidom,
        p.user_name,
        p.correo,
        p.curp,
        p.estatus,
        rr.codigo_contpaq,
        rr.rfc,
        rr.nss,
        rr.id_jefe,
        rr.jefe_directo_texto,
        jefe.numero_empleado,
        jefe.nombres,
        jefe.segundo_nombre,
        jefe.apellidop,
        jefe.apellidom
");

$groups = [];
foreach ($rows as $row) {
    $nombre = d_nombre($row);
    $sig = d_firma_nombre($nombre);
    if ($sig === '') continue;
    $row['nombre_completo'] = $nombre;
    $row['firma_nombre'] = $sig;
    $groups[$sig][] = $row;
}

$hallazgos = [];
$inspeccion = null;
if ($onlyId > 0 || $onlyNumero !== '') {
    $target = null;
    foreach ($groups as $items) {
        foreach ($items as $item) {
            $matchId = $onlyId > 0 && (int)$item['id'] === $onlyId;
            $matchNumero = $onlyNumero !== '' && d_txt($item['numero_empleado'] ?? '') === $onlyNumero;
            if ($matchId || $matchNumero) {
                $target = $item;
                break 2;
            }
        }
    }
    if ($target) {
        $targetTokens = array_values(array_filter(preg_split('/\s+/', (string)$target['firma_nombre']) ?: []));
        $similares = [];
        foreach ($groups as $items) {
            foreach ($items as $item) {
                if ((int)$item['id'] === $onlyId) continue;
                $tokens = array_values(array_filter(preg_split('/\s+/', (string)$item['firma_nombre']) ?: []));
                $overlap = count(array_intersect($targetTokens, $tokens));
                if ($overlap >= max(2, min(3, count($targetTokens)))) {
                    $item['tokens_en_comun'] = $overlap;
                    $similares[] = $item;
                }
            }
        }
        usort($similares, static fn($a, $b) => ((int)$b['tokens_en_comun'] <=> (int)$a['tokens_en_comun']) ?: ((int)$a['id'] <=> (int)$b['id']));
        $inspeccion = [
            'objetivo' => $target,
            'similares' => array_slice($similares, 0, 20),
        ];
    }
}
foreach ($groups as $sig => $items) {
    if (count($items) < 2) continue;
        if ($onlyId > 0 && !array_filter($items, static fn($r) => (int)$r['id'] === $onlyId)) continue;
        if ($onlyNumero !== '' && !array_filter($items, static fn($r) => d_txt($r['numero_empleado'] ?? '') === $onlyNumero)) continue;

    $excelCodes = $excel[$sig]['codigos'] ?? [];
    $scored = [];
    foreach ($items as $item) {
        $item['score_conservar'] = d_score_conservar($item, $excelCodes);
        $scored[] = $item;
    }
    usort($scored, static fn($a, $b) => ($b['score_conservar'] <=> $a['score_conservar']) ?: ((int)$a['id'] <=> (int)$b['id']));

    $conservar = $scored[0];
    $candidatosBaja = [];
    foreach (array_slice($scored, 1) as $item) {
        $seguro = d_numero_generado($item['numero_empleado'] ?? '')
            && !d_tiene_jefe($item)
            && ((int)$conservar['score_conservar'] - (int)$item['score_conservar']) >= 20;
        if ($onlyId > 0 && (int)$item['id'] === $onlyId) $seguro = true;
        if ($onlyNumero !== '' && d_txt($item['numero_empleado'] ?? '') === $onlyNumero) $seguro = true;
        $item['seguro_baja'] = $seguro;
        $candidatosBaja[] = $item;
    }

    $hallazgos[] = [
        'firma_nombre' => $sig,
        'excel_nombres' => array_values(array_unique($excel[$sig]['nombres'] ?? [])),
        'conservar' => $conservar,
        'candidatos_baja' => $candidatosBaja,
    ];
}

$aplicados = [];
if ($apply) {
    foreach ($hallazgos as $h) {
        foreach ($h['candidatos_baja'] as $candidato) {
            if (empty($candidato['seguro_baja'])) continue;
            $id = (int)$candidato['id'];
            $db->CRUD("UPDATE __SPARTA_SECRET_REDACTED__.persona SET estatus = 'Baja' WHERE id = :id", ['id' => $id]);
            $aplicados[] = [
                'id' => $id,
                'numero_empleado' => $candidato['numero_empleado'] ?? '',
                'nombre' => $candidato['nombre_completo'] ?? '',
            ];
        }
    }
}

echo json_encode([
    'modo' => $apply ? 'APPLY' : 'DRY-RUN',
    'solo_id' => $onlyId ?: null,
    'solo_numero' => $onlyNumero !== '' ? $onlyNumero : null,
    'grupos_duplicados' => count($hallazgos),
    'aplicados_baja' => count($aplicados),
    'aplicados' => $aplicados,
    'objetivo_sin_filtro' => $objetivoSinFiltro,
    'inspeccion' => $inspeccion,
    'hallazgos' => $hallazgos,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
