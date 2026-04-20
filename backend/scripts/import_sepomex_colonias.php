<?php

/**
 * Importa asentamientos (colonias, barrios, etc.) desde el catálogo SEPOMEX (cpdescarga.txt)
 * hacia divisiones_administrativas nivel 3, colgando de cada municipio (nivel 2).
 *
 * SEPOMEX no incluye calles por colonia; nivel 4 queda para otros catálogos o captura libre.
 *
 * Descarga oficial (actualizar periódicamente):
 *   https://www.correosdemexico.gob.mx/datosabiertos/cp/cpdescarga.txt
 *   (si 403, descargar desde el navegador y guardar el .txt localmente)
 *
 * Uso:
 *   php backend/scripts/import_sepomex_colonias.php "C:\ruta\cpdescarga.txt" [--dry-run] [--pais=1] [--limite=5000]
 *
 * Emparejamiento municipio:
 *   1) Clave compuesta INEGI c_estado (2 dígitos) + c_mnpio (3 dígitos) vs codigo_interno en nivel 1 y 2
 *   2) Si falla: nombre de estado (SEPOMEX d_estado) + nombre municipio (D_mnpio) normalizado vs tu catálogo
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo puede ejecutarse en CLI.\n");
    exit(1);
}

set_time_limit(0);

require_once dirname(__DIR__) . '/cli_bootstrap.php';

use Core\Database;

$argvList = array_slice($argv, 1);
$file = null;
$dryRun = false;
$idPais = 1;
$limite = 0;

foreach ($argvList as $i => $arg) {
    if ($arg === '--dry-run') {
        $dryRun = true;
        continue;
    }
    if (preg_match('/^--pais=(\d+)$/', $arg, $m)) {
        $idPais = (int) $m[1];
        continue;
    }
    if (preg_match('/^--limite=(\d+)$/', $arg, $m)) {
        $limite = (int) $m[1];
        continue;
    }
    if ($arg !== '' && $file === null && strpos($arg, '--') !== 0) {
        $file = $arg;
    }
}

if ($file === null || !is_readable($file)) {
    fwrite(STDERR, "Uso: php import_sepomex_colonias.php <ruta_cpdescarga.txt> [--dry-run] [--pais=1] [--limite=N]\n");
    exit(1);
}

function normClave(string $s, int $len): string
{
    $n = preg_replace('/\D/', '', $s) ?? '';
    return str_pad($n !== '' ? $n : '0', $len, '0', STR_PAD_LEFT);
}

function normTexto(string $s): string
{
    $s = function_exists('mb_strtolower')
        ? mb_strtolower(trim($s), 'UTF-8')
        : strtolower(trim($s));
    $repl = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
    ];
    $s = strtr($s, $repl);
    $s = preg_replace('/\s+/', ' ', $s) ?? '';

    return $s;
}

function toUtf8(string $s): string
{
    if ($s === '') {
        return '';
    }
    if (preg_match('//u', $s) === 1) {
        return $s;
    }
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($s, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');
    }
    if (function_exists('iconv')) {
        $conv = @iconv('Windows-1252', 'UTF-8//IGNORE', $s);
        if ($conv !== false) {
            return $conv;
        }
    }

    return utf8_encode($s);
}

/** @return array{0: array<string,int>, 1: array<string,int>, 2: array<string,int>, 3: array<int,array{id_pais:int,id_tipo:int}>} */
function construirIndicesMunicipios(Database $db, int $idPais): array
{
    $rows = $db->queryAll(
        'SELECT m.id AS mid, m.nombre AS mnombre, m.codigo_interno AS mci, m.id_pais AS mpais, m.id_tipo AS mtipo,
                e.id AS eid, e.nombre AS enombre, e.codigo_interno AS eci
         FROM divisiones_administrativas m
         INNER JOIN divisiones_administrativas e ON e.id = m.id_padre
         WHERE m.nivel = 2 AND e.nivel = 1 AND m.id_pais = :p',
        ['p' => $idPais]
    );

    $porInegi = [];
    $porEstadoNombreMun = [];
    $porEstadoIdNombreMun = [];
    $metaMunicipio = [];

    foreach ($rows as $r) {
        $eid = (int) $r['eid'];
        $mid = (int) $r['mid'];
        $eci = trim((string) ($r['eci'] ?? ''));
        $mci = trim((string) ($r['mci'] ?? ''));
        if ($eci !== '' && $mci !== '') {
            $k = normClave($eci, 2) . '_' . normClave($mci, 3);
            if (!isset($porInegi[$k])) {
                $porInegi[$k] = $mid;
            }
        }
        $k2 = normTexto((string) $r['enombre']) . '|' . normTexto((string) $r['mnombre']);
        if (!isset($porEstadoNombreMun[$k2])) {
            $porEstadoNombreMun[$k2] = $mid;
        }
        $k3 = $eid . '|' . normTexto((string) $r['mnombre']);
        if (!isset($porEstadoIdNombreMun[$k3])) {
            $porEstadoIdNombreMun[$k3] = $mid;
        }
        if (!isset($metaMunicipio[$mid])) {
            $metaMunicipio[$mid] = [
                'id_pais' => (int) ($r['mpais'] ?? $idPais),
                'id_tipo' => (int) ($r['mtipo'] ?? 0),
            ];
        }
    }

    return [$porInegi, $porEstadoNombreMun, $porEstadoIdNombreMun, $metaMunicipio];
}

/** @return array{0: array<string,int>, 1: array<string,int>} */
function construirIndicesEstados(Database $db, int $idPais): array
{
    $rows = $db->queryAll(
        'SELECT id, nombre, codigo_interno
         FROM divisiones_administrativas
         WHERE nivel = 1 AND id_pais = :p AND activo = 1',
        ['p' => $idPais]
    );
    $porClave = [];
    $porNombre = [];
    foreach ($rows as $r) {
        $id = (int) $r['id'];
        $ci = trim((string) ($r['codigo_interno'] ?? ''));
        if ($ci !== '') {
            $porClave[normClave($ci, 2)] = $id;
        }
        $porNombre[normTexto((string) $r['nombre'])] = $id;
    }

    return [$porClave, $porNombre];
}

/** @return array<string,true> */
function construirIndiceColoniasExistentes(Database $db, int $idPais): array
{
    $rows = $db->queryAll(
        'SELECT id_padre, nombre, IFNULL(codigo_interno, \'\') AS cp
         FROM divisiones_administrativas
         WHERE nivel = 3 AND id_pais = :p AND activo = 1',
        ['p' => $idPais]
    );
    $set = [];
    foreach ($rows as $r) {
        $k = (int) ($r['id_padre'] ?? 0) . "\t" . normTexto((string) ($r['nombre'] ?? '')) . "\t" . trim((string) ($r['cp'] ?? ''));
        $set[$k] = true;
    }

    return $set;
}

/** Aliases comunes SEPOMEX / INEGI vs nombres en catálogo local */
function aliasEstadoSepomex(string $dEstado): array
{
    $n = normTexto($dEstado);
    $aliases = [
        'veracruz de ignacio de la llave' => ['veracruz de ignacio de la llave', 'veracruz'],
        'michoacan de ocampo' => ['michoacan de ocampo', 'michoacan', 'michoacán'],
        'coahuila de zaragoza' => ['coahuila de zaragoza', 'coahuila'],
        'ciudad de mexico' => ['ciudad de mexico', 'cdmx', 'distrito federal'],
    ];
    $out = [$n];
    if (isset($aliases[$n])) {
        $out = array_merge($out, $aliases[$n]);
    }

    return array_unique($out);
}

function resolverEstadoId(array $estPorClave, array $estPorNombre, string $cEstado, string $dEstado): ?int
{
    $k = normClave($cEstado, 2);
    if (isset($estPorClave[$k])) {
        return $estPorClave[$k];
    }
    foreach (aliasEstadoSepomex($dEstado) as $alias) {
        $nk = normTexto($alias);
        if ($nk !== '' && isset($estPorNombre[$nk])) {
            return $estPorNombre[$nk];
        }
    }

    return null;
}

function resolverMunicipioId(
    array $munInegi,
    array $munEstNombre,
    array $munEstIdNombre,
    array $estPorClave,
    array $estPorNombre,
    string $cEstado,
    string $cMnpio,
    string $dEstado,
    string $dMnpio
): ?int {
    $inegi = normClave($cEstado, 2) . '_' . normClave($cMnpio, 3);
    if (isset($munInegi[$inegi])) {
        return $munInegi[$inegi];
    }
    $eid = resolverEstadoId($estPorClave, $estPorNombre, $cEstado, $dEstado);
    if ($eid === null) {
        return null;
    }
    $k = $eid . '|' . normTexto($dMnpio);
    if (isset($munEstIdNombre[$k])) {
        return $munEstIdNombre[$k];
    }
    foreach (aliasEstadoSepomex($dEstado) as $alias) {
        $k2 = normTexto($alias) . '|' . normTexto($dMnpio);
        if (isset($munEstNombre[$k2])) {
            return $munEstNombre[$k2];
        }
    }

    return null;
}

function parseHeader(array $parts): ?array
{
    $map = [];
    foreach ($parts as $i => $h) {
        $map[strtolower(trim($h))] = $i;
    }
    if (isset($map['d_codigo'], $map['d_asenta'], $map['d_mnpio'], $map['d_estado'], $map['c_estado'], $map['c_mnpio'])) {
        return $map;
    }

    return null;
}

function extraerCampo(array $parts, ?array $idx, string $clave, int $fallback): string
{
    $lk = strtolower($clave);
    if ($idx !== null) {
        foreach ($idx as $hk => $i) {
            if (strtolower((string) $hk) === $lk) {
                return trim((string) ($parts[$i] ?? ''));
            }
        }
    }

    return trim((string) ($parts[$fallback] ?? ''));
}

function ejecutarInsertConReintento(Database $db, string $sql, array $params, int $maxIntentos = 6): int
{
    $intento = 0;
    while (true) {
        try {
            return (int) $db->CRUD($sql, $params);
        } catch (Throwable $e) {
            $intento++;
            $msg = $e->getMessage();
            $esBloqueo = (strpos($msg, '1205') !== false || strpos($msg, '1213') !== false || stripos($msg, 'Lock wait timeout') !== false || stripos($msg, 'Deadlock') !== false);
            if (!$esBloqueo || $intento >= $maxIntentos) {
                throw $e;
            }
            usleep(250000 * $intento);
        }
    }
}

/**
 * @param array<int,array{id_pais:int,id_tipo:int,id_padre:int,nombre:string,cp:string}> $rows
 */
function insertarBatchColonias(Database $db, array $rows): int
{
    if (!$rows) {
        return 0;
    }
    $valuesSql = [];
    $params = [];
    foreach ($rows as $i => $r) {
        $valuesSql[] = "(:id_pais_{$i}, :id_tipo_{$i}, :id_padre_{$i}, :nombre_{$i}, NULL, :cp_{$i}, 3, 1, NOW())";
        $params["id_pais_{$i}"] = $r['id_pais'];
        $params["id_tipo_{$i}"] = $r['id_tipo'];
        $params["id_padre_{$i}"] = $r['id_padre'];
        $params["nombre_{$i}"] = $r['nombre'];
        $params["cp_{$i}"] = $r['cp'];
    }
    $sql = 'INSERT INTO divisiones_administrativas
        (id_pais, id_tipo, id_padre, nombre, codigo_iso, codigo_interno, nivel, activo, created_at)
        VALUES ' . implode(",\n", $valuesSql);

    return ejecutarInsertConReintento($db, $sql, $params);
}

$db = new Database();
[$estPorClave, $estPorNombre] = construirIndicesEstados($db, $idPais);
[$munInegi, $munEstNombre, $munEstIdNombre, $metaMunicipio] = construirIndicesMunicipios($db, $idPais);
$coloniasExistentes = construirIndiceColoniasExistentes($db, $idPais);

$fh = fopen($file, 'rb');
if ($fh === false) {
    fwrite(STDERR, "No se pudo abrir el archivo.\n");
    exit(1);
}

$firstLine = fgets($fh);
if ($firstLine === false) {
    fwrite(STDERR, "Archivo vacío.\n");
    exit(1);
}
$firstParts = explode('|', rtrim($firstLine, "\r\n"));
$colIndex = parseHeader($firstParts);
$useFirstLine = $colIndex !== null;
if (!$useFirstLine) {
    rewind($fh);
}

$filasNuevas = 0;
$filasEvaluadas = 0;
$omitidosDup = 0;
$omitidosSinMunicipio = 0;
$omitidosExistente = 0;
$omitidosLinea = 0;
$vistos = [];

$batchSize = 500;
$batchRows = [];

$n = 0;
if (!$dryRun) {
    $db->beginTransaction();
}

try {
    while (($line = fgets($fh)) !== false) {
        $n++;
        if ($limite > 0 && $n > $limite) {
            break;
        }
        $line = rtrim($line, "\r\n");
        if ($line === '') {
            continue;
        }
        $parts = explode('|', $line);
        if (count($parts) < 12) {
            $omitidosLinea++;
            continue;
        }

        $d_codigo = trim(extraerCampo($parts, $colIndex, 'd_codigo', 0));
        $d_asenta = toUtf8(extraerCampo($parts, $colIndex, 'd_asenta', 1));
        $d_tipo_asenta = toUtf8(extraerCampo($parts, $colIndex, 'd_tipo_asenta', 2));
        $D_mnpio = toUtf8(extraerCampo($parts, $colIndex, 'd_mnpio', 3));
        $d_estado = toUtf8(extraerCampo($parts, $colIndex, 'd_estado', 4));
        $c_estado = trim(extraerCampo($parts, $colIndex, 'c_estado', 7));
        $c_mnpio = trim(extraerCampo($parts, $colIndex, 'c_mnpio', 11));

        if ($d_codigo === '' || $d_asenta === '') {
            $omitidosLinea++;
            continue;
        }

        $mid = resolverMunicipioId(
            $munInegi,
            $munEstNombre,
            $munEstIdNombre,
            $estPorClave,
            $estPorNombre,
            $c_estado,
            $c_mnpio,
            $d_estado,
            $D_mnpio
        );
        if ($mid === null) {
            $omitidosSinMunicipio++;
            continue;
        }

        $nombreBase = $d_asenta;
        if ($d_tipo_asenta !== '' && !preg_match('/^colonia$/iu', $d_tipo_asenta)) {
            $nombreBase = $d_asenta . ' (' . $d_tipo_asenta . ')';
        }
        $nombre = $nombreBase;
        $claveVisto = $mid . "\t" . normTexto($nombreBase) . "\t" . $d_codigo . "\t" . normTexto($d_tipo_asenta);
        if (isset($vistos[$claveVisto])) {
            $omitidosDup++;
            continue;
        }
        $vistos[$claveVisto] = true;

        $claveExistente = $mid . "\t" . normTexto($nombre) . "\t" . $d_codigo;
        if (isset($coloniasExistentes[$claveExistente])) {
            $omitidosExistente++;
            continue;
        }
        if (!isset($metaMunicipio[$mid])) {
            $omitidosSinMunicipio++;
            continue;
        }

        $filasEvaluadas++;
        if ($dryRun) {
            $filasNuevas++;
            $coloniasExistentes[$claveExistente] = true;
            continue;
        }

        $batchRows[] = [
            'id_pais' => $metaMunicipio[$mid]['id_pais'],
            'id_tipo' => $metaMunicipio[$mid]['id_tipo'],
            'id_padre' => $mid,
            'nombre' => $nombre,
            'cp' => $d_codigo,
        ];
        $coloniasExistentes[$claveExistente] = true;

        if (count($batchRows) >= $batchSize) {
            $rc = insertarBatchColonias($db, $batchRows);
            $filasNuevas += (int) $rc;
            $batchRows = [];
            fwrite(STDOUT, "… {$filasEvaluadas} líneas evaluadas, {$filasNuevas} inserts (rowCount acumulado)\n");
            $db->commit();
            $db->beginTransaction();
        }
    }

    if (!$dryRun && !empty($batchRows)) {
        $rc = insertarBatchColonias($db, $batchRows);
        $filasNuevas += (int) $rc;
        $batchRows = [];
    }

    if (!$dryRun) {
        $db->commit();
    }
} catch (Throwable $e) {
    if (!$dryRun) {
        $db->rollback();
    }
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
} finally {
    fclose($fh);
}

fwrite(STDOUT, $dryRun ? "[DRY-RUN] " : '');
fwrite(STDOUT, "Listo. Líneas que aplicarían / inserts ejecutados (rowCount): {$filasNuevas}\n");
fwrite(STDOUT, "Líneas con municipio resuelto (evaluadas): {$filasEvaluadas}\n");
fwrite(STDOUT, "Omitidos (sin municipio en tu catálogo): {$omitidosSinMunicipio}\n");
fwrite(STDOUT, "Omitidos (duplicado mismo CP+asentamiento en archivo): {$omitidosDup}\n");
fwrite(STDOUT, "Omitidos (ya existentes en BD): {$omitidosExistente}\n");
fwrite(STDOUT, "Omitidos (línea incompleta): {$omitidosLinea}\n");
fwrite(STDOUT, "\nSi sin_municipio es alto, revisa que divisiones nivel 1 y 2 tengan codigo_interno = claves INEGI (c_estado, c_mnpio) o nombres alineados con SEPOMEX.\n");
