<?php
/**
 * Auditoria de posibles usuarios duplicados en __SPARTA_SECRET_REDACTED__.persona.
 *
 * Uso:
 *   C:\xampp\php\php.exe scripts/auditar_persona_duplicados.php
 *   C:\xampp\php\php.exe scripts/auditar_persona_duplicados.php --limit=5000 --sim-threshold=88 --out=scripts/salida_duplicados.csv
 *
 * Opciones:
 *   --limit=N             Limita cantidad de filas leidas (0 = sin limite, default 0)
 *   --sim-threshold=N     Umbral de similitud para nombres (0..100, default 88)
 *   --out=RUTA.csv        Exporta hallazgos a CSV
 *
 * Variables de entorno opcionales:
 *   DB_HOST, DB_PUERTO, DB_NAME, DB_USER, DB_PASSWORD
 */

declare(strict_types=1);

ini_set('memory_limit', '512M');
set_time_limit(0);

function argValue(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $arg) {
        if (strpos($arg, $name . '=') === 0) {
            return substr($arg, strlen($name) + 1);
        }
    }

    return $default;
}

function cleanText(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    $value = mb_strtolower($value, 'UTF-8');
    $value = str_replace(
        ['á', 'à', 'ä', 'â', 'ã', 'é', 'è', 'ë', 'ê', 'í', 'ì', 'ï', 'î', 'ó', 'ò', 'ö', 'ô', 'õ', 'ú', 'ù', 'ü', 'û', 'ñ'],
        ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n'],
        $value
    );
    $value = preg_replace('/[^a-z0-9\s]/', ' ', $value) ?? '';
    $value = preg_replace('/\s+/', ' ', $value) ?? '';

    return trim($value);
}

function onlyDigits(?string $value): string
{
    $digits = preg_replace('/\D+/', '', (string) $value);
    if ($digits === null) {
        return '';
    }

    return trim($digits);
}

function detectColumns(PDO $pdo, string $schema, string $table): array
{
    $sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['schema' => $schema, 'table' => $table]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return array_values(array_map('strval', is_array($rows) ? $rows : []));
}

function pickFirstColumn(array $available, array $candidates): ?string
{
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $available, true)) {
            return $candidate;
        }
    }

    return null;
}

function nameSimilarity(string $a, string $b): float
{
    if ($a === '' || $b === '') {
        return 0.0;
    }
    similar_text($a, $b, $percent);

    return (float) $percent;
}

function printSectionTitle(string $title): void
{
    echo "\n";
    echo str_repeat('=', 90) . "\n";
    echo $title . "\n";
    echo str_repeat('=', 90) . "\n";
}

function printGroupedResults(string $title, array $groups, int $maxGroups = 25): void
{
    printSectionTitle($title);
    if (count($groups) === 0) {
        echo "Sin hallazgos.\n";
        return;
    }

    $shown = 0;
    foreach ($groups as $group) {
        if ($shown >= $maxGroups) {
            $remaining = count($groups) - $shown;
            echo "... ($remaining grupos adicionales omitidos en pantalla)\n";
            break;
        }
        $shown++;
        echo "- Motivo: {$group['reason']}\n";
        echo "  Coincidencia: {$group['match_value']}\n";
        echo "  Registros: " . count($group['rows']) . "\n";
        foreach ($group['rows'] as $r) {
            $line = sprintf(
                "    id=%s | nombre=%s | ap1=%s | ap2=%s | fecha_nac=%s | doc=%s | correo=%s | telefono=%s",
                (string) ($r['id'] ?? ''),
                (string) ($r['nombre'] ?? ''),
                (string) ($r['ap1'] ?? ''),
                (string) ($r['ap2'] ?? ''),
                (string) ($r['fecha_nacimiento'] ?? ''),
                (string) ($r['doc'] ?? ''),
                (string) ($r['email'] ?? ''),
                (string) ($r['phone'] ?? '')
            );
            echo $line . "\n";
        }
    }
}

function appendCsvRows(array &$out, string $type, string $reason, string $matchValue, array $rows): void
{
    foreach ($rows as $r) {
        $out[] = [
            'tipo' => $type,
            'motivo' => $reason,
            'coincidencia' => $matchValue,
            'id' => (string) ($r['id'] ?? ''),
            'nombre' => (string) ($r['nombre'] ?? ''),
            'apellido_paterno' => (string) ($r['ap1'] ?? ''),
            'apellido_materno' => (string) ($r['ap2'] ?? ''),
            'fecha_nacimiento' => (string) ($r['fecha_nacimiento'] ?? ''),
            'documento' => (string) ($r['doc'] ?? ''),
            'correo' => (string) ($r['email'] ?? ''),
            'telefono' => (string) ($r['phone'] ?? ''),
        ];
    }
}

function saveCsv(string $path, array $rows): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $fp = fopen($path, 'wb');
    if ($fp === false) {
        throw new RuntimeException('No se pudo crear el archivo CSV: ' . $path);
    }
    $headers = ['tipo', 'motivo', 'coincidencia', 'id', 'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'documento', 'correo', 'telefono'];
    fputcsv($fp, $headers);
    foreach ($rows as $row) {
        $line = [];
        foreach ($headers as $h) {
            $line[] = (string) ($row[$h] ?? '');
        }
        fputcsv($fp, $line);
    }
    fclose($fp);
}

$limit = max(0, (int) (argValue($argv, '--limit', '0') ?? '0'));
$simThreshold = (float) (argValue($argv, '--sim-threshold', '88') ?? '88');
$simThreshold = max(0.0, min(100.0, $simThreshold));
$outCsv = argValue($argv, '--out', null);

$dbHost = getenv('DB_HOST') ?: '__SPARTA_HOST_REDACTED__';
$dbPort = getenv('DB_PUERTO') ?: '3306';
$dbName = getenv('DB_NAME') ?: '__SPARTA_SECRET_REDACTED__';
$dbUser = getenv('DB_USER') ?: '__SPARTA_SECRET_REDACTED__';
$dbPass = getenv('DB_PASSWORD') ?: '__SPARTA_PASSWORD_REDACTED__';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_TIMEOUT => 8,
]);

$columns = detectColumns($pdo, $dbName, 'persona');
if (count($columns) === 0) {
    throw new RuntimeException('No se encontraron columnas en tabla persona. Verifica esquema/credenciales.');
}

$idCol = pickFirstColumn($columns, ['id', 'id_persona']);
$nameCol = pickFirstColumn($columns, ['nombre', 'nombres']);
$ap1Col = pickFirstColumn($columns, ['apellido_paterno', 'primer_apellido', 'apellido1', 'apellido', 'apellidop', 'ap_paterno']);
$ap2Col = pickFirstColumn($columns, ['apellido_materno', 'segundo_apellido', 'apellido2', 'apellidom', 'ap_materno']);
$birthCol = pickFirstColumn($columns, ['fecha_nacimiento', 'f_nacimiento', 'nacimiento']);
$docCol = pickFirstColumn($columns, ['dpi', 'curp', 'cedula', 'identificacion', 'documento', 'no_documento']);
$mailCol = pickFirstColumn($columns, ['email', 'correo', 'correo_electronico', 'mail']);
$phoneCol = pickFirstColumn($columns, ['telefono', 'telefono1', 'celular', 'movil']);

if ($idCol === null || $nameCol === null || $ap1Col === null) {
    throw new RuntimeException('No se encontraron columnas minimas (id, nombre, apellido_paterno/primer_apellido).');
}

$selectCols = [$idCol, $nameCol, $ap1Col];
foreach ([$ap2Col, $birthCol, $docCol, $mailCol, $phoneCol] as $col) {
    if ($col !== null) {
        $selectCols[] = $col;
    }
}
$selectCols = array_values(array_unique($selectCols));
$sql = 'SELECT ' . implode(', ', array_map(static fn($c) => "`{$c}`", $selectCols)) . ' FROM `persona`';
if ($limit > 0) {
    $sql .= ' LIMIT ' . (int) $limit;
}
$rows = $pdo->query($sql)->fetchAll();

echo "Filas leidas de persona: " . count($rows) . "\n";
echo "Umbral similitud: {$simThreshold}%\n";
echo "Columnas usadas: " . implode(', ', $selectCols) . "\n";

$normalized = [];
foreach ($rows as $r) {
    $normalized[] = [
        'id' => (string) ($r[$idCol] ?? ''),
        'nombre' => trim((string) ($r[$nameCol] ?? '')),
        'ap1' => trim((string) ($r[$ap1Col] ?? '')),
        'ap2' => $ap2Col !== null ? trim((string) ($r[$ap2Col] ?? '')) : '',
        'fecha_nacimiento' => $birthCol !== null ? substr((string) ($r[$birthCol] ?? ''), 0, 10) : '',
        'doc' => $docCol !== null ? trim((string) ($r[$docCol] ?? '')) : '',
        'email' => $mailCol !== null ? trim((string) ($r[$mailCol] ?? '')) : '',
        'phone' => $phoneCol !== null ? trim((string) ($r[$phoneCol] ?? '')) : '',
    ];
}

$exactGroups = [];
$byKey = [];

foreach ($normalized as $r) {
    $docNorm = cleanText($r['doc']);
    if ($docNorm !== '' && strlen($docNorm) >= 6) {
        $k = 'DOC|' . $docNorm;
        $byKey[$k][] = $r;
    }
    $emailNorm = cleanText($r['email']);
    if ($emailNorm !== '' && strpos($emailNorm, '@') !== false) {
        $k = 'EMAIL|' . $emailNorm;
        $byKey[$k][] = $r;
    }
    $phoneNorm = onlyDigits($r['phone']);
    if ($phoneNorm !== '' && strlen($phoneNorm) >= 8) {
        $k = 'PHONE|' . $phoneNorm;
        $byKey[$k][] = $r;
    }
    $nameDob = cleanText($r['nombre'] . ' ' . $r['ap1'] . ' ' . $r['ap2']) . '|' . $r['fecha_nacimiento'];
    if ($r['fecha_nacimiento'] !== '' && strlen(str_replace(' ', '', $nameDob)) > 10) {
        $k = 'NAME_DOB|' . $nameDob;
        $byKey[$k][] = $r;
    }
}

foreach ($byKey as $k => $items) {
    if (count($items) < 2) {
        continue;
    }
    $parts = explode('|', $k, 2);
    $kind = $parts[0] ?? 'OTRO';
    $value = $parts[1] ?? '';
    $reason = match ($kind) {
        'DOC' => 'Documento repetido',
        'EMAIL' => 'Correo repetido',
        'PHONE' => 'Telefono repetido',
        'NAME_DOB' => 'Nombre completo + fecha nacimiento repetido',
        default => 'Coincidencia exacta',
    };
    $exactGroups[] = [
        'reason' => $reason,
        'match_value' => $value,
        'rows' => $items,
    ];
}

usort($exactGroups, static function (array $a, array $b): int {
    return count($b['rows']) <=> count($a['rows']);
});

$buckets = [];
foreach ($normalized as $r) {
    $ap1 = cleanText($r['ap1']);
    $ap2 = cleanText($r['ap2']);
    $name = cleanText($r['nombre']);
    if ($ap1 === '' || $name === '') {
        continue;
    }
    $firstNameToken = explode(' ', $name)[0] ?? $name;
    $bucketKey = $ap1 . '|' . $ap2 . '|' . $firstNameToken[0];
    $buckets[$bucketKey][] = $r;
}

$possiblePairs = [];
foreach ($buckets as $bucketRows) {
    $n = count($bucketRows);
    if ($n < 2) {
        continue;
    }
    if ($n > 120) {
        continue;
    }
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            $a = $bucketRows[$i];
            $b = $bucketRows[$j];
            if ((string) $a['id'] === (string) $b['id']) {
                continue;
            }
            $fullA = cleanText($a['nombre'] . ' ' . $a['ap1'] . ' ' . $a['ap2']);
            $fullB = cleanText($b['nombre'] . ' ' . $b['ap1'] . ' ' . $b['ap2']);
            $sim = nameSimilarity($fullA, $fullB);

            $sameAp1 = cleanText($a['ap1']) !== '' && cleanText($a['ap1']) === cleanText($b['ap1']);
            $sameAp2 = cleanText($a['ap2']) !== '' && cleanText($a['ap2']) === cleanText($b['ap2']);
            $sameDob = ($a['fecha_nacimiento'] !== '' && $a['fecha_nacimiento'] === $b['fecha_nacimiento']);

            $reason = '';
            if ($sim >= $simThreshold && ($sameAp1 || $sameAp2)) {
                $reason = 'Nombre parecido + apellido coincidente';
            } elseif ($sameAp1 && $sameAp2) {
                $reason = 'Dos apellidos iguales';
            }
            if ($reason === '') {
                continue;
            }

            $ida = (string) $a['id'];
            $idb = (string) $b['id'];
            if (strcmp($ida, $idb) <= 0) {
                $pairKey = $ida . '|' . $idb;
            } else {
                $pairKey = $idb . '|' . $ida;
            }
            $possiblePairs[$pairKey] = [
                'reason' => $reason . ($sameDob ? ' + misma fecha nacimiento' : ''),
                'match_value' => sprintf('similitud=%.1f%%', $sim),
                'rows' => [$a, $b],
                'score' => $sim + ($sameDob ? 10 : 0) + (($sameAp1 && $sameAp2) ? 5 : 0),
            ];
        }
    }
}

$possibleGroups = array_values($possiblePairs);
usort($possibleGroups, static function (array $a, array $b): int {
    return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
});

printSectionTitle('RESUMEN');
echo 'Coincidencias exactas (fuertes): ' . count($exactGroups) . " grupos\n";
echo 'Coincidencias posibles (similitud): ' . count($possibleGroups) . " pares\n";

printGroupedResults('COINCIDENCIAS EXACTAS', $exactGroups, 30);
printGroupedResults('COINCIDENCIAS POSIBLES', $possibleGroups, 40);

$csvRows = [];
foreach ($exactGroups as $g) {
    appendCsvRows($csvRows, 'exacta', (string) $g['reason'], (string) $g['match_value'], $g['rows']);
}
foreach ($possibleGroups as $g) {
    appendCsvRows($csvRows, 'posible', (string) $g['reason'], (string) $g['match_value'], $g['rows']);
}

if ($outCsv !== null && trim($outCsv) !== '') {
    $csvPath = $outCsv;
    if (!preg_match('/^[A-Za-z]:[\\\\\\/]/', $csvPath)) {
        $csvPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($csvPath, '\\/');
    }
    saveCsv($csvPath, $csvRows);
    echo "\nCSV generado: {$csvPath}\n";
}

echo "\nFin de auditoria.\n";

