<?php
declare(strict_types=1);

require __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$apply = in_array('--apply', $argv, true);
$sepomexFile = __DIR__ . '/../backend/storage/tmp/sepomex_cpdescarga.txt';
$inegiDir = __DIR__ . '/../backend/storage/tmp/inegi_mgem';

if (!is_file($sepomexFile)) {
    fwrite(STDERR, "No existe el archivo SEPOMEX: {$sepomexFile}\n");
    exit(1);
}
if (!is_dir($inegiDir)) {
    fwrite(STDERR, "No existe el directorio INEGI: {$inegiDir}\n");
    exit(1);
}

function norm_cat(string $value): string
{
    $value = trim($value);
    $value = strtr($value, [
        'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A', 'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
        'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E', 'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
        'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I', 'Î' => 'I', 'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
        'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O', 'Ô' => 'O', 'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
        'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U', 'Û' => 'U', 'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
        'Ñ' => 'N', 'ñ' => 'n',
    ]);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if ($ascii !== false) {
        $value = $ascii;
    }
    $value = strtoupper($value);
    $value = preg_replace('/[^A-Z0-9]+/', ' ', $value) ?? $value;
    return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
}

function utf8_cat(string $value): string
{
    if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
        return $value;
    }
    $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
    return $converted === false ? $value : $converted;
}

function nombre_asentamiento(string $nombre, string $tipo): string
{
    $nombre = trim($nombre);
    $tipo = trim($tipo);
    if ($tipo !== '' && norm_cat($tipo) !== 'COLONIA') {
        return $nombre . ' (' . $tipo . ')';
    }
    return $nombre;
}

function calidad_nombre_cat(string $nombre): int
{
    preg_match_all('/[^\x00-\x7F]/u', $nombre, $matches);
    return count($matches[0] ?? []);
}

function pdo_from_db(Database $db): PDO
{
    $ref = new ReflectionClass($db);
    $prop = $ref->getProperty('db');
    $prop->setAccessible(true);
    $pdo = $prop->getValue($db);
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('No se pudo obtener la conexión PDO.');
    }
    return $pdo;
}

function read_inegi_municipios(string $dir): array
{
    $municipios = [];
    foreach (glob($dir . '/*.json') ?: [] as $file) {
        $json = json_decode(file_get_contents($file) ?: '', true);
        foreach (($json['datos'] ?? []) as $row) {
            $cveEnt = str_pad((string) ($row['cve_ent'] ?? ''), 2, '0', STR_PAD_LEFT);
            $cveMun = str_pad((string) ($row['cve_mun'] ?? ''), 3, '0', STR_PAD_LEFT);
            $nombre = trim((string) ($row['nomgeo'] ?? ''));
            if ($cveEnt !== '' && $cveMun !== '' && $nombre !== '') {
                $municipios[$cveEnt][$cveMun] = $nombre;
            }
        }
    }
    ksort($municipios);
    foreach ($municipios as &$rows) {
        ksort($rows);
    }
    unset($rows);
    return $municipios;
}

function read_sepomex_resumen(string $file): array
{
    $fh = fopen($file, 'rb');
    if (!$fh) {
        throw new RuntimeException("No se pudo abrir SEPOMEX: {$file}");
    }
    fgets($fh); // aviso legal
    fgetcsv($fh, 0, '|'); // encabezado

    $municipios = [];
    $asentamientos = [];
    $lineas = 0;
    while (($row = fgetcsv($fh, 0, '|')) !== false) {
        if (count($row) < 15) {
            continue;
        }
        $lineas++;
        $cp = trim(utf8_cat((string) $row[0]));
        $asenta = trim(utf8_cat((string) $row[1]));
        $tipo = trim(utf8_cat((string) $row[2]));
        $municipio = trim(utf8_cat((string) $row[3]));
        $estado = trim(utf8_cat((string) $row[4]));
        $cEstado = str_pad(trim((string) $row[7]), 2, '0', STR_PAD_LEFT);
        $cMun = str_pad(trim((string) $row[11]), 3, '0', STR_PAD_LEFT);
        $idAsenta = trim((string) $row[12]);
        if ($cEstado === '' || $cMun === '' || $cp === '' || $asenta === '') {
            continue;
        }
        $municipios[$cEstado][$cMun] = ['estado' => $estado, 'municipio' => $municipio];
        $nombre = nombre_asentamiento($asenta, $tipo);
        $key = $cEstado . '|' . $cMun . '|' . $cp . '|' . norm_cat($nombre);
        $entry = [
            'c_estado' => $cEstado,
            'c_mun' => $cMun,
            'cp' => $cp,
            'nombre' => $nombre,
            'id_asenta' => $idAsenta,
        ];
        if (!isset($asentamientos[$key]) || calidad_nombre_cat($nombre) > calidad_nombre_cat((string) $asentamientos[$key]['nombre'])) {
            $asentamientos[$key] = $entry;
        }
    }
    fclose($fh);
    return ['lineas' => $lineas, 'municipios' => $municipios, 'asentamientos' => $asentamientos];
}

function rows_by_norm(array $rows): array
{
    $idx = [];
    foreach ($rows as $row) {
        $idx[norm_cat((string) $row['nombre'])] = $row;
    }
    return $idx;
}

function find_municipio_candidate(array $rows, string $officialName, array $aliases, array $usedIds): ?array
{
    $officialNorm = norm_cat($officialName);
    $byNorm = rows_by_norm($rows);
    if (isset($byNorm[$officialNorm]) && empty($usedIds[(int) $byNorm[$officialNorm]['id']])) {
        return $byNorm[$officialNorm];
    }
    foreach ($aliases as $alias) {
        $aliasNorm = norm_cat($alias);
        if (isset($byNorm[$aliasNorm]) && empty($usedIds[(int) $byNorm[$aliasNorm]['id']])) {
            return $byNorm[$aliasNorm];
        }
    }

    $candidates = [];
    foreach ($rows as $row) {
        $id = (int) $row['id'];
        if (!empty($usedIds[$id])) {
            continue;
        }
        $currentNorm = norm_cat((string) $row['nombre']);
        if ($currentNorm === '' || strlen($currentNorm) < 5) {
            continue;
        }
        if (str_contains($officialNorm, $currentNorm) || str_contains($currentNorm, $officialNorm)) {
            $candidates[] = $row;
        }
    }
    return count($candidates) === 1 ? $candidates[0] : null;
}

$stateNameByCve = [
    '01' => 'Aguascalientes',
    '02' => 'Baja California',
    '03' => 'Baja California Sur',
    '04' => 'Campeche',
    '05' => 'Coahuila',
    '06' => 'Colima',
    '07' => 'Chiapas',
    '08' => 'Chihuahua',
    '09' => 'Ciudad de México',
    '10' => 'Durango',
    '11' => 'Guanajuato',
    '12' => 'Guerrero',
    '13' => 'Hidalgo',
    '14' => 'Jalisco',
    '15' => 'Estado de México',
    '16' => 'Michoacán',
    '17' => 'Morelos',
    '18' => 'Nayarit',
    '19' => 'Nuevo León',
    '20' => 'Oaxaca',
    '21' => 'Puebla',
    '22' => 'Querétaro',
    '23' => 'Quintana Roo',
    '24' => 'San Luis Potosí',
    '25' => 'Sinaloa',
    '26' => 'Sonora',
    '27' => 'Tabasco',
    '28' => 'Tamaulipas',
    '29' => 'Tlaxcala',
    '30' => 'Veracruz',
    '31' => 'Yucatán',
    '32' => 'Zacatecas',
];

$municipioAliases = [
    '09-004' => ['Cuajimalpa'],
];

$inegiMunicipios = read_inegi_municipios($inegiDir);
$sepomex = read_sepomex_resumen($sepomexFile);

$db = new Database();
$pdo = pdo_from_db($db);

$states = $db->queryAll(
    "SELECT id, nombre, codigo_iso, codigo_interno
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
     WHERE id_pais = 1 AND nivel = 1 AND activo = 1"
);
$statesByName = rows_by_norm($states);
$stateIdByCve = [];
foreach ($stateNameByCve as $cve => $nombre) {
    $row = $statesByName[norm_cat($nombre)] ?? null;
    if (!$row) {
        throw new RuntimeException("No se encontró el estado base {$nombre} ({$cve}) en divisiones_administrativas.");
    }
    $stateIdByCve[$cve] = (int) $row['id'];
}

$currentMunicipios = $db->queryAll(
    "SELECT id, id_padre, nombre, codigo_interno, activo
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
     WHERE id_pais = 1 AND nivel = 2"
);
$currentByState = [];
foreach ($currentMunicipios as $row) {
    $currentByState[(int) $row['id_padre']][] = $row;
}

$plan = [
    'modo' => $apply ? 'APLICAR' : 'DIAGNOSTICO',
    'inegi_municipios' => array_sum(array_map('count', $inegiMunicipios)),
    'sepomex_lineas' => $sepomex['lineas'],
    'sepomex_asentamientos_unicos' => count($sepomex['asentamientos']),
    'municipios_insertar' => 0,
    'municipios_actualizar' => 0,
    'municipios_desactivar' => 0,
    'municipios_sin_colonias_sepomex' => 0,
];

$municipioIdByCode = [];
$municipiosOficialesNormPorEstado = [];
$updatesMunicipios = [];
$insertsMunicipios = [];
$usedMunicipioIds = [];

foreach ($inegiMunicipios as $cEstado => $municipios) {
    $stateId = $stateIdByCve[$cEstado] ?? null;
    if (!$stateId) {
        continue;
    }
    foreach ($municipios as $cMun => $nombre) {
        $codigo = $cEstado . '-' . $cMun;
        $tipo = $cEstado === '09' ? 2 : 3;
        $norm = norm_cat($nombre);
        $row = find_municipio_candidate($currentByState[$stateId] ?? [], $nombre, $municipioAliases[$codigo] ?? [], $usedMunicipioIds);
        $municipiosOficialesNormPorEstado[$stateId][$norm] = true;
        foreach (($municipioAliases[$codigo] ?? []) as $alias) {
            $municipiosOficialesNormPorEstado[$stateId][norm_cat($alias)] = true;
        }
        if ($row) {
            $id = (int) $row['id'];
            $usedMunicipioIds[$id] = true;
            $municipioIdByCode[$codigo] = $id;
            $municipiosOficialesNormPorEstado[$stateId][norm_cat((string) $row['nombre'])] = true;
            if ((string) ($row['nombre'] ?? '') !== $nombre || (string) ($row['codigo_interno'] ?? '') !== $codigo || (int) ($row['activo'] ?? 1) !== 1) {
                $updatesMunicipios[] = compact('id', 'nombre', 'codigo', 'tipo');
            }
        } else {
            $insertsMunicipios[] = compact('stateId', 'nombre', 'codigo', 'tipo');
        }
    }
}

foreach ($currentByState as $stateId => $rows) {
    foreach ($rows as $row) {
        $id = (int) $row['id'];
        if ((int) ($row['activo'] ?? 1) === 1 && empty($usedMunicipioIds[$id])) {
            $plan['municipios_desactivar']++;
        }
    }
}

foreach ($inegiMunicipios as $cEstado => $municipios) {
    foreach (array_keys($municipios) as $cMun) {
        if (empty($sepomex['municipios'][$cEstado][$cMun])) {
            $plan['municipios_sin_colonias_sepomex']++;
        }
    }
}

$plan['municipios_insertar'] = count($insertsMunicipios);
$plan['municipios_actualizar'] = count($updatesMunicipios);

echo json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

if (!$apply) {
    if ($insertsMunicipios) {
        echo "\nMunicipios a insertar (primeros 40):\n";
        foreach (array_slice($insertsMunicipios, 0, 40) as $row) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }
    }
    exit(0);
}

$stamp = date('Ymd_His');
$backupTable = 'divisiones_administrativas_backup_' . $stamp;

try {
    $pdo->exec("CREATE TABLE __SPARTA_SECRET_REDACTED__.`{$backupTable}` AS SELECT * FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas");

    $idx = $db->queryOne(
        "SELECT 1
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = '__SPARTA_SECRET_REDACTED__'
           AND TABLE_NAME = 'divisiones_administrativas'
           AND INDEX_NAME = 'idx_da_catalogo_lookup'
         LIMIT 1"
    );
    if (!$idx) {
        $pdo->exec(
            "CREATE INDEX idx_da_catalogo_lookup
             ON __SPARTA_SECRET_REDACTED__.divisiones_administrativas
             (id_pais, nivel, id_padre, activo, nombre, codigo_interno)"
        );
    }

    $db->beginTransaction();

    $tipoCol = $db->queryOne("SELECT id FROM __SPARTA_SECRET_REDACTED__.division_administrativa_tipos WHERE codigo = 'SETTLEMENT' LIMIT 1");
    if (!$tipoCol) {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.division_administrativa_tipos (nombre, codigo, nivel)
             VALUES ('Colonia / asentamiento', 'SETTLEMENT', 3)"
        );
        $tipoColonia = $db->lastInsertId();
    } else {
        $tipoColonia = (int) $tipoCol['id'];
    }

    foreach ($updatesMunicipios as $row) {
        $db->CRUD(
            "UPDATE __SPARTA_SECRET_REDACTED__.divisiones_administrativas
             SET nombre = :nombre, codigo_interno = :codigo, id_tipo = :tipo, activo = 1
             WHERE id = :id",
            [
                'nombre' => $row['nombre'],
                'codigo' => $row['codigo'],
                'tipo' => (int) $row['tipo'],
                'id' => (int) $row['id'],
            ]
        );
    }

    foreach ($insertsMunicipios as $row) {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.divisiones_administrativas
             (id_pais, id_tipo, id_padre, nombre, codigo_iso, codigo_interno, nivel, activo)
             VALUES (1, :tipo, :stateId, :nombre, NULL, :codigo, 2, 1)",
            [
                'tipo' => (int) $row['tipo'],
                'stateId' => (int) $row['stateId'],
                'nombre' => $row['nombre'],
                'codigo' => $row['codigo'],
            ]
        );
        $municipioIdByCode[$row['codigo']] = $db->lastInsertId();
    }

    foreach ($currentByState as $stateId => $rows) {
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if ((int) ($row['activo'] ?? 1) === 1 && empty($usedMunicipioIds[$id])) {
                $db->CRUD(
                    "UPDATE __SPARTA_SECRET_REDACTED__.divisiones_administrativas SET activo = 0 WHERE id = :id",
                    ['id' => $id]
                );
            }
        }
    }

    $refsPersona = $db->queryAll(
        "SELECT p.id AS persona_id, c.id_padre, c.nombre, c.codigo_interno
         FROM __SPARTA_SECRET_REDACTED__.persona p
         JOIN __SPARTA_SECRET_REDACTED__.divisiones_administrativas c ON c.id = p.id_div_nivel3
         WHERE p.id_div_nivel3 IS NOT NULL AND p.id_div_nivel3 > 0"
    );
    $refsCandidatos = $db->queryAll(
        "SELECT p.id AS candidato_id, c.id_padre, c.nombre, c.codigo_interno
         FROM __SPARTA_SECRET_REDACTED__.candidatos p
         JOIN __SPARTA_SECRET_REDACTED__.divisiones_administrativas c ON c.id = p.id_div_nivel3
         WHERE p.id_div_nivel3 IS NOT NULL AND p.id_div_nivel3 > 0"
    );

    $pdo->exec("DELETE FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas WHERE id_pais = 1 AND nivel >= 3");

    $batch = [];
    $inserted = 0;
    foreach ($sepomex['asentamientos'] as $row) {
        $codigoMun = $row['c_estado'] . '-' . $row['c_mun'];
        $parentId = $municipioIdByCode[$codigoMun] ?? null;
        if (!$parentId) {
            continue;
        }
        $batch[] = '(' . implode(',', [
            '1',
            (string) $tipoColonia,
            (string) (int) $parentId,
            $pdo->quote($row['nombre']),
            $pdo->quote(substr((string) $row['id_asenta'], 0, 10)),
            $pdo->quote($row['cp']),
            '3',
            '1',
        ]) . ')';
        if (count($batch) >= 500) {
            $pdo->exec(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.divisiones_administrativas
                 (id_pais, id_tipo, id_padre, nombre, codigo_iso, codigo_interno, nivel, activo)
                 VALUES " . implode(',', $batch)
            );
            $inserted += count($batch);
            $batch = [];
        }
    }
    if ($batch) {
        $pdo->exec(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.divisiones_administrativas
             (id_pais, id_tipo, id_padre, nombre, codigo_iso, codigo_interno, nivel, activo)
             VALUES " . implode(',', $batch)
        );
        $inserted += count($batch);
    }

    $newColonias = $db->queryAll(
        "SELECT id, id_padre, nombre, codigo_interno
         FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
         WHERE id_pais = 1 AND nivel = 3 AND activo = 1"
    );
    $coloniaIndex = [];
    foreach ($newColonias as $row) {
        $coloniaIndex[(int) $row['id_padre'] . '|' . norm_cat((string) $row['nombre']) . '|' . trim((string) $row['codigo_interno'])] = (int) $row['id'];
    }

    $personasActualizadas = 0;
    foreach ($refsPersona as $ref) {
        $key = (int) $ref['id_padre'] . '|' . norm_cat((string) $ref['nombre']) . '|' . trim((string) $ref['codigo_interno']);
        $newId = $coloniaIndex[$key] ?? null;
        $db->CRUD(
            "UPDATE __SPARTA_SECRET_REDACTED__.persona SET id_div_nivel3 = :newId WHERE id = :id",
            ['newId' => $newId, 'id' => (int) $ref['persona_id']]
        );
        $personasActualizadas++;
    }

    $candidatosActualizados = 0;
    foreach ($refsCandidatos as $ref) {
        $key = (int) $ref['id_padre'] . '|' . norm_cat((string) $ref['nombre']) . '|' . trim((string) $ref['codigo_interno']);
        $newId = $coloniaIndex[$key] ?? null;
        $db->CRUD(
            "UPDATE __SPARTA_SECRET_REDACTED__.candidatos SET id_div_nivel3 = :newId WHERE id = :id",
            ['newId' => $newId, 'id' => (int) $ref['candidato_id']]
        );
        $candidatosActualizados++;
    }

    if ($pdo->inTransaction()) {
        $db->commit();
    }
    echo json_encode([
        'ok' => true,
        'backup' => $backupTable,
        'municipios_insertados' => count($insertsMunicipios),
        'municipios_actualizados' => count($updatesMunicipios),
        'colonias_insertadas' => $inserted,
        'personas_colonia_remapeadas' => $personasActualizadas,
        'candidatos_colonia_remapeados' => $candidatosActualizados,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $db->rollback();
    }
    throw $e;
}
