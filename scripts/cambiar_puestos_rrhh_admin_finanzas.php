<?php

declare(strict_types=1);

use Core\Database;
use Models\CapHum;
use Models\CapHumRrhh;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';
require dirname(__DIR__) . '/backend/core/Model.php';
require dirname(__DIR__) . '/backend/models/CapHum.php';
require dirname(__DIR__) . '/backend/models/CapHumRrhh.php';

function norm_key(mixed $value): string
{
    $text = trim((string)($value ?? ''));
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtoupper($text);
    $text = str_replace('&', 'Y', $text);
    $text = preg_replace('/[^A-Z0-9]+/', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
}

function pais_id_mexico(Database $db): int
{
    $rows = $db->queryAll("SELECT id, nombre, codigo_iso FROM __SPARTA_SECRET_REDACTED__.paises");
    foreach ($rows as $row) {
        if (norm_key($row['nombre'] ?? '') === 'MEXICO' || norm_key($row['codigo_iso'] ?? '') === 'MX') {
            return (int)$row['id'];
        }
    }
    return 1;
}

function find_person(Database $db, string $fullName): array
{
    $target = norm_key($fullName);
    $rows = $db->queryAll("
        SELECT
            p.id,
            p.numero_empleado,
            p.estatus,
            CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo
        FROM __SPARTA_SECRET_REDACTED__.persona p
        WHERE UPPER(TRIM(COALESCE(p.estatus, ''))) <> 'BAJA'
        ORDER BY p.id
    ");

    $matches = [];
    foreach ($rows as $row) {
        if (norm_key($row['nombre_completo'] ?? '') === $target) {
            $matches[] = $row;
        }
    }

    return $matches;
}

function find_by_name(Database $db, string $sql, array $params, string $name): int
{
    $target = norm_key($name);
    $rows = $db->queryAll($sql, $params);
    foreach ($rows as $row) {
        if (norm_key($row['nombre'] ?? '') === $target) {
            return (int)($row['id'] ?? 0);
        }
    }
    return 0;
}

function unique_position_key(Database $db, string $name, int $departmentId): string
{
    $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($name)) ?: trim($name);
    $base = strtoupper($base);
    $base = preg_replace('/[^A-Z0-9]+/', '-', $base);
    $base = trim((string)$base, '-');
    if ($base === '') {
        $base = 'PUESTO';
    }

    for ($i = 1; $i < 10000; $i++) {
        $suffix = '-' . $departmentId . ($i === 1 ? '' : '-' . $i);
        $clave = substr($base, 0, max(1, 50 - strlen($suffix))) . $suffix;
        $exists = $db->queryOne(
            "SELECT id FROM __SPARTA_SECRET_REDACTED__.puesto WHERE clave = :clave LIMIT 1",
            ['clave' => $clave]
        );
        if (!$exists) {
            return $clave;
        }
    }

    throw new RuntimeException('No se pudo generar clave para el puesto ' . $name);
}

function ensure_position(Database $db, array $target, int $idPais, bool $apply, array &$log): array
{
    $idDireccion = find_by_name(
        $db,
        "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion WHERE id_pais = :id_pais",
        ['id_pais' => $idPais],
        $target['direccion']
    );
    if ($idDireccion <= 0) {
        $log[] = 'Crear direccion: ' . $target['direccion'];
        if (!$apply) {
            return ['id_direccion' => 0, 'id_area' => 0, 'id_departamento' => 0, 'id_puesto' => 0];
        }
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.direcciones_organizacion (nombre, activo, id_pais) VALUES (:nombre, 1, :id_pais)",
            ['nombre' => $target['direccion'], 'id_pais' => $idPais]
        );
        $idDireccion = $db->lastInsertId();
    }

    $idArea = find_by_name(
        $db,
        "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional WHERE id_pais = :id_pais",
        ['id_pais' => $idPais],
        $target['area']
    );
    if ($idArea <= 0) {
        $log[] = 'Crear area: ' . $target['area'];
        if (!$apply) {
            return ['id_direccion' => $idDireccion, 'id_area' => 0, 'id_departamento' => 0, 'id_puesto' => 0];
        }
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.departamento_organizacional (nombre, activo, id_pais) VALUES (:nombre, 1, :id_pais)",
            ['nombre' => $target['area'], 'id_pais' => $idPais]
        );
        $idArea = $db->lastInsertId();
    }

    if ($apply) {
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_direcciones (id_direccion, id_departamento_organizacional, activo)
             VALUES (:id_direccion, :id_area, 1)
             ON DUPLICATE KEY UPDATE id_direccion = VALUES(id_direccion), activo = 1",
            ['id_direccion' => $idDireccion, 'id_area' => $idArea]
        );
    }

    $idDepartamento = find_by_name(
        $db,
        "SELECT id, nombre
           FROM __SPARTA_SECRET_REDACTED__.departamento
          WHERE id_pais = :id_pais
            AND id_departamento_organizacional = :id_area",
        ['id_pais' => $idPais, 'id_area' => $idArea],
        $target['departamento']
    );
    if ($idDepartamento <= 0) {
        $log[] = 'Crear departamento: ' . $target['area'] . ' > ' . $target['departamento'];
        if (!$apply) {
            return ['id_direccion' => $idDireccion, 'id_area' => $idArea, 'id_departamento' => 0, 'id_puesto' => 0];
        }
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.departamento (nombre, activo, img_url, id_pais, id_departamento_organizacional)
             VALUES (:nombre, 1, NULL, :id_pais, :id_area)",
            ['nombre' => $target['departamento'], 'id_pais' => $idPais, 'id_area' => $idArea]
        );
        $idDepartamento = $db->lastInsertId();
    }

    $idPuesto = find_by_name(
        $db,
        "SELECT id, nombre FROM __SPARTA_SECRET_REDACTED__.puesto WHERE departamento_id = :id_departamento",
        ['id_departamento' => $idDepartamento],
        $target['puesto']
    );
    if ($idPuesto <= 0) {
        $log[] = 'Crear puesto: ' . $target['departamento'] . ' > ' . $target['puesto'];
        if (!$apply) {
            return ['id_direccion' => $idDireccion, 'id_area' => $idArea, 'id_departamento' => $idDepartamento, 'id_puesto' => 0];
        }
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.puesto (clave, nombre, nivel, activo, departamento_id, es_jefe, descripcion)
             VALUES (:clave, :nombre, 0, 1, :id_departamento, 1, NULL)",
            [
                'clave' => unique_position_key($db, $target['puesto'], $idDepartamento),
                'nombre' => $target['puesto'],
                'id_departamento' => $idDepartamento,
            ]
        );
        $idPuesto = $db->lastInsertId();
    }

    return [
        'id_direccion' => $idDireccion,
        'id_area' => $idArea,
        'id_departamento' => $idDepartamento,
        'id_puesto' => $idPuesto,
    ];
}

$apply = in_array('--apply', $_SERVER['argv'] ?? [], true);
$targets = [
    [
        'persona' => 'JORGE ALBERTO DEL ANGEL MORALES',
        'direccion' => 'ADMINISTRACION Y FINANZAS',
        'area' => 'RECURSOS HUMANOS',
        'departamento' => 'RECURSOS HUMANOS',
        'puesto' => 'SUBDIRECTOR',
    ],
    [
        'persona' => 'OWEN BRAYAN RUIZ GONZALEZ',
        'direccion' => 'ADMINISTRACION Y FINANZAS',
        'area' => 'RECURSOS HUMANOS',
        'departamento' => 'GESTION DE TALENTO',
        'puesto' => 'ANALISTA',
    ],
    [
        'persona' => 'NATHALY MONSERRAT GOMEZ TEPATLAN',
        'direccion' => 'ADMINISTRACION Y FINANZAS',
        'area' => 'RECURSOS HUMANOS',
        'departamento' => 'GESTION DE TALENTO',
        'puesto' => 'ANALISTA',
    ],
    [
        'persona' => 'ELVIRA PASCUAL OCHOA',
        'direccion' => 'ADMINISTRACION Y FINANZAS',
        'area' => 'RECURSOS HUMANOS',
        'departamento' => 'GESTION DE TALENTO',
        'puesto' => 'ANALISTA',
    ],
];

$db = new Database();
if ($apply) {
    CapHumRrhh::asegurarTablas($db);
}
$idPais = pais_id_mexico($db);
$log = [];
$resolved = [];

foreach ($targets as $target) {
    $matches = find_person($db, $target['persona']);
    if (count($matches) !== 1) {
        $log[] = 'ERROR persona no unica: ' . $target['persona'] . ' coincidencias=' . count($matches);
        continue;
    }
    $catalog = ensure_position($db, $target, $idPais, false, $log);
    $resolved[] = ['persona' => $matches[0], 'target' => $target, 'catalog' => $catalog];
}

echo 'Modo: ' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;
foreach ($resolved as $item) {
    echo sprintf(
        "- %s (#%s) -> %s / %s / %s / %s\n",
        $item['persona']['nombre_completo'],
        $item['persona']['id'],
        $item['target']['direccion'],
        $item['target']['area'],
        $item['target']['departamento'],
        $item['target']['puesto']
    );
}
foreach (array_unique($log) as $line) {
    echo $line . PHP_EOL;
}

if (!$apply) {
    exit(count($resolved) === count($targets) ? 0 : 1);
}
if (count($resolved) !== count($targets)) {
    fwrite(STDERR, "Abortado: no se resolvieron todas las personas.\n");
    exit(1);
}

$db->beginTransaction();
try {
    foreach ($resolved as $item) {
        $target = $item['target'];
        $catalog = ensure_position($db, $target, $idPais, true, $log);
        $idPersona = (int)$item['persona']['id'];
        $idPuesto = (int)$catalog['id_puesto'];
        if ($idPuesto <= 0) {
            throw new RuntimeException('No se pudo resolver puesto para ' . $target['persona']);
        }

        $puestosAntes = CapHum::puestosActivosTrayectoria($db, $idPersona);
        $db->CRUD(
            "UPDATE __SPARTA_SECRET_REDACTED__.asigna_puesto SET activo = 0 WHERE id_persona = :id_persona AND COALESCE(activo, 1) = 1",
            ['id_persona' => $idPersona]
        );
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto (id, id_persona, id_puesto, fecha_asignacion, activo)
             VALUES (DEFAULT, :id_persona, :id_puesto, :fecha, 1)",
            ['id_persona' => $idPersona, 'id_puesto' => $idPuesto, 'fecha' => CapHum::fechaHoraCdmx()]
        );
        $db->CRUD(
            "INSERT INTO __SPARTA_SECRET_REDACTED__.persona_datos_rrhh
                (id_persona, id_departamento, id_area, id_puesto, puesto_texto, departamento_texto, area_texto, direccion_organizacional)
             VALUES
                (:id_persona, :id_departamento, :id_area, :id_puesto, :puesto, :departamento, :area, :direccion)
             ON DUPLICATE KEY UPDATE
                id_departamento = VALUES(id_departamento),
                id_area = VALUES(id_area),
                id_puesto = VALUES(id_puesto),
                puesto_texto = VALUES(puesto_texto),
                departamento_texto = VALUES(departamento_texto),
                area_texto = VALUES(area_texto),
                direccion_organizacional = VALUES(direccion_organizacional)",
            [
                'id_persona' => $idPersona,
                'id_departamento' => $catalog['id_departamento'],
                'id_area' => $catalog['id_area'],
                'id_puesto' => $idPuesto,
                'puesto' => $target['puesto'],
                'departamento' => $target['departamento'],
                'area' => $target['area'],
                'direccion' => $target['direccion'],
            ]
        );

        CapHum::registrarCambiosTrayectoriaPuestos(
            $db,
            $idPersona,
            $puestosAntes,
            CapHum::puestosActivosTrayectoria($db, $idPersona),
            null,
            'ajuste_manual_rrhh_admin_finanzas'
        );
    }

    $db->commit();
    echo "Cambios aplicados correctamente.\n";
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, 'Error, transaccion revertida: ' . $e->getMessage() . PHP_EOL);
    exit(2);
}
