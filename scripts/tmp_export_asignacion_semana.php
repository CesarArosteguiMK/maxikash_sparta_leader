<?php
/**
 * TEMPORAL: Exporta asignación desde tbl_segundometro_histo para una semana ISO (ej. Semana 17-2026).
 * Por defecto genera CSV (UTF-8 BOM) — Excel lo abre sin problema y evita agotar RAM en tablas grandes.
 * Opción --xlsx intenta Excel vía PhpSpreadsheet (solo si pocas filas; si falla por memoria, usar CSV).
 *
 * Uso:
 *   php scripts/tmp_export_asignacion_semana.php
 *   php scripts/tmp_export_asignacion_semana.php --semana=17 --anio=2026
 *   php scripts/tmp_export_asignacion_semana.php --etiqueta="Semana 17-2026" --out=C:\temp\asig.csv
 *   php scripts/tmp_export_asignacion_semana.php --xlsx --max-xlsx=8000
 *
 * Borrar cuando ya no se necesite.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Solo CLI.\n");
    exit(1);
}

ini_set('memory_limit', '2048M');
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

function hasFlag(array $argv, string $flag): bool
{
    return in_array($flag, $argv, true);
}

$root = dirname(__DIR__);
define('RAIZ', $root . DIRECTORY_SEPARATOR . 'backend');
if (!defined('SPARTA_PROJECT_ROOT')) {
    define('SPARTA_PROJECT_ROOT', $root);
}
if (!defined('SPARTA_UPLOADS_ROOT')) {
    define('SPARTA_UPLOADS_ROOT', $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
}

$configIni = RAIZ . '/config/config.ini';
if (!is_readable($configIni)) {
    fwrite(STDERR, "No se encontró config.ini\n");
    exit(1);
}
define('CONFIGURACION', parse_ini_file($configIni));

error_reporting(E_ALL);
require_once $root . '/backend/bootstrap_composer.php';
sparta_require_composer_autoload();

spl_autoload_register(function ($archivo) {
    if (strpos($archivo, 'PhpOffice\\') === 0 ||
        strpos($archivo, 'ZipStream\\') === 0 ||
        strpos($archivo, 'Psr\\') === 0) {
        return;
    }
    $archivo = str_replace('\\', '/', $archivo);
    $parts = explode('/', $archivo, 2);
    $top = $parts[0];
    $tail = $parts[1] ?? '';
    static $dirMap = [
        'Models' => 'models',
        'Controllers' => 'controllers',
        'Core' => 'core',
        'Libs' => 'libs',
        'Services' => 'services',
    ];
    $dir = $dirMap[$top] ?? strtolower($top);
    $rel = $tail !== '' ? $dir . '/' . $tail : $dir;
    $ruta = RAIZ . '/' . $rel . '.php';
    if (!file_exists($ruta)) {
        throw new Exception('Autoload no encontró: ' . $ruta);
    }
    require_once $ruta;
});

require_once RAIZ . '/config/config.php';

use Core\DatabaseSegundometro;

$etiquetaArg = argValue($argv, '--etiqueta', null);
$semanaNum = argValue($argv, '--semana', '17');
$anio = argValue($argv, '--anio', '2026');

if ($etiquetaArg !== null && trim($etiquetaArg) !== '') {
    $etiqueta = trim($etiquetaArg);
} else {
    $n = (int) $semanaNum;
    $y = (int) $anio;
    if ($n < 1 || $n > 53 || $y < 2000 || $y > 2100) {
        fwrite(STDERR, "--semana / --anio inválidos.\n");
        exit(1);
    }
    $etiqueta = 'Semana ' . $n . '-' . $y;
}

$variantes = [$etiqueta];
if (preg_match('/Semana\s+(\d+)\s*[-–]\s*(\d{4})/iu', $etiqueta, $m)) {
    $compact = (int) $m[1] . '-' . (int) $m[2];
    $variantes[] = $compact;
    $variantes[] = 'Semana ' . $compact;
}
$variantes = array_values(array_unique(array_filter($variantes, static fn ($v) => trim((string) $v) !== '')));

$wantXlsx = hasFlag($argv, '--xlsx');
$maxXlsx = (int) (argValue($argv, '--max-xlsx', '15000') ?? '15000');
if ($maxXlsx < 100) {
    $maxXlsx = 100;
}

$out = argValue($argv, '--out', null);
$defaultExt = $wantXlsx ? 'xlsx' : 'csv';
if ($out === null || $out === '') {
    $safe = preg_replace('/[^\w\-]/', '_', $etiqueta);
    $out = $root . DIRECTORY_SEPARATOR . 'tmp_asignacion_' . $safe . '_' . date('Y-m-d_His') . '.' . $defaultExt;
}

try {
    $db = new DatabaseSegundometro();
} catch (Throwable $e) {
    fwrite(STDERR, 'Conexión: ' . $e->getMessage() . "\n");
    exit(1);
}

$semIn = [];
$semParams = [];
foreach ($variantes as $i => $lab) {
    $k = 's' . $i;
    $semIn[] = ':' . $k;
    $semParams[$k] = $lab;
}

$sql = 'SELECT * FROM `tbl_segundometro_histo` WHERE CAST(`SEMANA` AS CHAR CHARACTER SET utf8mb4) IN (' . implode(', ', $semIn) . ') ORDER BY `Id_credito` ASC, `fecha_hora_insert` DESC';

try {
    $filas = $db->queryAll($sql, $semParams);
} catch (Throwable $e) {
    fwrite(STDERR, 'Consulta: ' . $e->getMessage() . "\n");
    exit(1);
}

$n = count($filas);
echo "Filas leídas: {$n}\n";

if ($filas === []) {
    fwrite(STDERR, "Sin datos para SEMANA en: " . implode(', ', $variantes) . "\n");
    exit(2);
}

$columnasSql = array_keys($filas[0]);

if ($wantXlsx && $n <= $maxXlsx) {
    require_once RAIZ . '/libs/PhpSpreadsheet/PhpSpreadsheet.php';
    $columnas = [];
    foreach ($columnasSql as $campo) {
        $columnas[] = PHPSpreadsheet::ColumnaExcel(
            $campo,
            $campo,
            ['estilo' => PHPSpreadsheet::GetEstilosExcel('texto_izquierda')]
        );
    }
    $filasExcel = [];
    foreach ($filas as $r) {
        $fila = [];
        foreach ($columnasSql as $campo) {
            $v = $r[$campo] ?? null;
            if ($v === null) {
                $fila[$campo] = '';
            } elseif (is_scalar($v)) {
                $fila[$campo] = (string) $v;
            } else {
                $fila[$campo] = json_encode($v, JSON_UNESCAPED_UNICODE);
            }
        }
        $filasExcel[] = $fila;
    }
    $titulo = 'Asignación — ' . $etiqueta . ' — ' . $n . ' filas';
    try {
        $libro = PHPSpreadsheet::GeneraExcel('Asignacion', $titulo, $columnas, $filasExcel);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro);
        if (!str_ends_with(strtolower($out), '.xlsx')) {
            $out .= '.xlsx';
        }
        $writer->save($out);
    } catch (Throwable $e) {
        fwrite(STDERR, 'Excel falló (' . $e->getMessage() . "). Use sin --xlsx para CSV.\n");
        exit(1);
    }
    echo "OK (xlsx): {$out}\n";
    exit(0);
}

if ($wantXlsx && $n > $maxXlsx) {
    fwrite(STDERR, "Demasiadas filas ({$n}) para --xlsx con este límite ({$maxXlsx}). Generando CSV...\n");
    if (preg_match('/\.xlsx$/i', $out)) {
        $out = preg_replace('/\.xlsx$/i', '.csv', $out);
    }
}

$fh = fopen($out, 'wb');
if ($fh === false) {
    fwrite(STDERR, "No se pudo escribir: {$out}\n");
    exit(1);
}
fprintf($fh, "\xEF\xBB\xBF");
fputcsv($fh, $columnasSql);
foreach ($filas as $r) {
    $line = [];
    foreach ($columnasSql as $c) {
        $v = $r[$c] ?? null;
        if ($v === null) {
            $line[] = '';
        } elseif (is_scalar($v)) {
            $line[] = (string) $v;
        } else {
            $line[] = json_encode($v, JSON_UNESCAPED_UNICODE);
        }
    }
    fputcsv($fh, $line);
}
fclose($fh);

echo "OK (CSV, abrir con Excel): {$out}\n";
echo 'Variantes SEMANA: ' . implode(', ', $variantes) . "\n";
