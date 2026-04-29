<?php
/**
 * Prueba de sintaxis contra MySQL/MariaDB: EXPLAIN <consulta del organigrama>.
 * Uso: php scripts/test_organigrama_sql_explain.php [nested|flat|both]
 *
 * No imprime credenciales; solo resultado de conexión y EXPLAIN.
 */
declare(strict_types=1);

$mode = $argv[1] ?? 'both';

$root = dirname(__DIR__);
$envPath = $root . '/.env';

/** @return array<string, string> */
function parseEnvFile(string $path): array
{
    if (!is_readable($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return [];
    }
    $out = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $eq = strpos($line, '=');
        if ($eq === false) {
            continue;
        }
        $key = trim(substr($line, 0, $eq));
        $rest = trim(substr($line, $eq + 1));
        if ($rest === '') {
            $out[$key] = '';
            continue;
        }
        $first = $rest[0];
        if ($first === '"' || $first === "'") {
            $q = $first;
            $buf = '';
            $len = strlen($rest);
            for ($i = 1; $i < $len; $i++) {
                $c = $rest[$i];
                if ($c === '\\' && $i + 1 < $len) {
                    $buf .= $rest[++$i];
                    continue;
                }
                if ($c === $q) {
                    $out[$key] = $buf;
                    continue 2;
                }
                $buf .= $c;
            }
            $out[$key] = $buf;
            continue;
        }
        $out[$key] = trim($rest, " \t\"'");
    }
    return $out;
}

/** Quita comentarios de línea -- al inicio (no toca literales). */
function stripLeadingSqlComments(string $sql): string
{
    $lines = preg_split("/\R/", $sql);
    if (!is_array($lines)) {
        return trim($sql);
    }
    $kept = [];
    foreach ($lines as $line) {
        $t = trim($line);
        if ($t === '') {
            continue;
        }
        if (str_starts_with($t, '--')) {
            continue;
        }
        $kept[] = $line;
    }
    return trim(implode("\n", $kept));
}

function runExplain(PDO $pdo, string $label, string $sqlFile): void
{
    echo "--- {$label} ({$sqlFile}) ---\n";
    if (!is_readable($sqlFile)) {
        echo "ERROR: archivo no legible.\n\n";
        return;
    }
    $raw = file_get_contents($sqlFile);
    if ($raw === false) {
        echo "ERROR: no se pudo leer.\n\n";
        return;
    }
    $sql = stripLeadingSqlComments($raw);
    if ($sql === '') {
        echo "ERROR: SQL vacío tras quitar comentarios.\n\n";
        return;
    }

    $explainSql = 'EXPLAIN ' . $sql;
    try {
        $pdo->query($explainSql);
        echo "OK: EXPLAIN ejecutó sin error de sintaxis (filas: al menos 1 plan).\n\n";
    } catch (PDOException $e) {
        $code = $e->getCode();
        $msg = $e->getMessage();
        echo "FALLO: {$msg}\n";
        echo "(código PDO: {$code})\n\n";
    }
}

$env = parseEnvFile($envPath);
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$name = $env['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

if ($name === '' || $user === '') {
    fwrite(STDERR, "Falta DB_NAME o DB_USER en .env ({$envPath}).\n");
    exit(1);
}

$dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 15,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, 'No se pudo conectar: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "Conexión OK a {$host}:{$port} / {$name}\n\n";

$nested = $root . '/scripts/organigrama_campo_roles_por_puesto.sql';
$flat = $root . '/scripts/organigrama_campo_roles_por_puesto_flat.sql';

if ($mode === 'nested' || $mode === 'both') {
    runExplain($pdo, 'nested (FROM subquery)', $nested);
}
if ($mode === 'flat' || $mode === 'both') {
    runExplain($pdo, 'flat (single SELECT)', $flat);
}

// Opcional: demuestra que el 1064 del usuario = solo enviar el WHERE (mismo mensaje que DBeaver/JDBC).
if (in_array('--demo-1064', $argv, true)) {
    echo "--- simulación envío parcial (solo WHERE …) ---\n";
    try {
        $pdo->query("WHERE p.estatus = 'Activo' AND UPPER(TRIM(COALESCE(p.user_name, ''))) <> 'REPORTERIA'");
        echo "ERROR: no debía ejecutarse.\n";
    } catch (PDOException $e) {
        echo "Esperado: " . $e->getMessage() . "\n";
    }
}
