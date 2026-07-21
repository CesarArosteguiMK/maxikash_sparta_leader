<?php

declare(strict_types=1);

use Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__ . '/../backend/core/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

const ORIGEN_PLANTILLA = 'AEM_PENSIONAMAX';
const HOJAS_EMPRESA = [
    'AEM' => 'MaxiKash',
    'PENSIONAMAX' => 'Furia Motos',
];

// Son las tres altas del padron que no existian aun en Sparta. El Excel no trae
// columnas separadas para nombres y apellidos, por eso se documentan aqui.
const PERSONAS_NUEVAS = [
    'JURY090717MDFRMLA3' => ['nombres' => 'YOLANDA', 'segundo_nombre' => 'SHANTAL', 'apellidop' => 'JUAREZ', 'apellidom' => 'RAMIREZ'],
    'RIHA921119MDFVRN04' => ['nombres' => 'ANA', 'segundo_nombre' => 'GRISELL', 'apellidop' => 'RIVERA', 'apellidom' => 'HERNANDEZ'],
    'CUJA851102MDFRMD01' => ['nombres' => 'ADRIANA', 'segundo_nombre' => null, 'apellidop' => 'CRUZ', 'apellidom' => 'JIMENEZ'],
];

// El CURP de Luis Antonio tenia una letra capturada incorrectamente en Sparta.
const CORRECCIONES_CURP_AUTORIZADAS = [
    'REML820613HDFJRS09' => 'REML820613HDRJRS09',
];

function textoNormalizado(?string $valor): string
{
    $valor = trim((string) $valor);
    $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor) ?: $valor;
    $valor = strtoupper($valor);
    return preg_replace('/[^A-Z0-9]+/', ' ', $valor) ?? '';
}

function claveNombre(?string $valor): string
{
    $tokens = array_values(array_filter(explode(' ', textoNormalizado($valor))));
    sort($tokens, SORT_STRING);
    return implode(' ', $tokens);
}

function argumento(string $nombre): ?string
{
    global $argv;
    foreach ($argv as $argumento) {
        if (str_starts_with($argumento, '--' . $nombre . '=')) {
            return substr($argumento, strlen($nombre) + 3);
        }
    }
    return null;
}

function asegurarTabla(Database $db): void
{
    $db->CRUD("
        CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_plantilla_activa (
            curp VARCHAR(18) NOT NULL,
            id_persona INT NOT NULL,
            id_empresa INT NOT NULL,
            origen VARCHAR(64) NOT NULL,
            nombre_origen VARCHAR(255) NOT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            fecha_sincronizacion DATETIME NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (curp),
            KEY idx_plantilla_activa_persona (id_persona, activo),
            KEY idx_plantilla_activa_empresa (id_empresa, activo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function asegurarColumnaExterno(Database $db): void
{
    $columna = $db->queryOne("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'persona'
          AND COLUMN_NAME = 'es_externo'
        LIMIT 1
    ");

    if (!$columna) {
        $db->CRUD('ALTER TABLE persona ADD COLUMN es_externo TINYINT(1) NOT NULL DEFAULT 0 AFTER codigo_contpac');
    }
}

function cargarPlantilla(string $archivo, array $empresas): array
{
    $libro = IOFactory::load($archivo);
    $plantilla = [];

    foreach (HOJAS_EMPRESA as $hojaNombre => $empresaNombre) {
        $hoja = $libro->getSheetByName($hojaNombre);
        if ($hoja === null) {
            throw new RuntimeException("No existe la hoja {$hojaNombre}.");
        }
        if (!isset($empresas[$empresaNombre])) {
            throw new RuntimeException("No esta configurada la empresa {$empresaNombre}.");
        }

        $filas = $hoja->toArray(null, true, true, true);
        foreach (array_slice($filas, 1) as $fila) {
            $nombre = trim((string) ($fila['A'] ?? ''));
            $curp = strtoupper(trim((string) ($fila['B'] ?? '')));
            if ($nombre === '' && $curp === '') {
                continue;
            }
            if (!preg_match('/^[A-Z0-9]{18}$/', $curp)) {
                throw new RuntimeException("CURP invalida en {$hojaNombre}: {$curp} ({$nombre}).");
            }
            if (isset($plantilla[$curp])) {
                throw new RuntimeException("CURP duplicada en el archivo: {$curp}.");
            }
            $plantilla[$curp] = [
                'curp' => $curp,
                'nombre_origen' => $nombre,
                'clave_nombre' => claveNombre($nombre),
                'empresa' => $empresaNombre,
                'id_empresa' => (int) $empresas[$empresaNombre],
            ];
        }
    }

    return $plantilla;
}

$archivo = argumento('archivo');
$aplicar = in_array('--aplicar', $argv, true);
if ($archivo === null || !is_file($archivo)) {
    fwrite(STDERR, "Uso: php scripts/sincronizar_plantilla_activa_rrhh.php --archivo=RUTA.xlsx [--aplicar]" . PHP_EOL);
    exit(1);
}

try {
    $db = new Database();
    $empresasDb = $db->queryAll("SELECT id, nombre_comercial FROM estado_cuenta.rrhh_empresas WHERE activo = 1");
    $empresas = [];
    foreach ($empresasDb as $empresa) {
        $empresas[(string) $empresa['nombre_comercial']] = (int) $empresa['id'];
    }

    $plantilla = cargarPlantilla($archivo, $empresas);
    $personas = $db->queryAll("
        SELECT
            id, id_empresa, estatus, curp,
            TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre_completo
        FROM estado_cuenta.persona
    ");

    $porCurp = [];
    $porNombre = [];
    foreach ($personas as $persona) {
        $curp = strtoupper(trim((string) ($persona['curp'] ?? '')));
        if ($curp !== '') {
            $porCurp[$curp][] = $persona;
        }
        $clave = claveNombre((string) ($persona['nombre_completo'] ?? ''));
        if ($clave !== '') {
            $porNombre[$clave][] = $persona;
        }
    }

    $resueltos = [];
    $errores = [];
    $creaciones = 0;
    $reactivaciones = 0;
    $curpsCorregidos = 0;

    foreach ($plantilla as $curp => $fila) {
        $candidatos = $porCurp[$curp] ?? [];
        $encontradoPor = 'curp';
        $candidatosNombre = $porNombre[$fila['clave_nombre']] ?? [];
        $activosPorNombre = array_values(array_filter(
            $candidatosNombre,
            static fn(array $persona): bool => ($persona['estatus'] ?? '') === 'Activo'
        ));

        // Un reingreso puede conservar una baja historica con el mismo CURP y
        // una ficha activa nueva aun sin CURP. Siempre se privilegia la ficha
        // activa que coincide por nombre antes de reactivar la baja historica.
        $hayActivoPorCurp = array_filter(
            $candidatos,
            static fn(array $persona): bool => ($persona['estatus'] ?? '') === 'Activo'
        ) !== [];
        if (!$hayActivoPorCurp && $activosPorNombre !== []) {
            $candidatos = $activosPorNombre;
            $encontradoPor = 'nombre';
        } elseif ($candidatos === []) {
            $candidatos = $candidatosNombre;
            $encontradoPor = 'nombre';
        }

        usort($candidatos, static function (array $a, array $b) use ($fila): int {
            $prioridadA = (($a['estatus'] ?? '') === 'Activo' ? 0 : 10) + ((int) $a['id_empresa'] === $fila['id_empresa'] ? 0 : 1);
            $prioridadB = (($b['estatus'] ?? '') === 'Activo' ? 0 : 10) + ((int) $b['id_empresa'] === $fila['id_empresa'] ? 0 : 1);
            return $prioridadA <=> $prioridadB ?: ((int) $b['id'] <=> (int) $a['id']);
        });

        if ($candidatos === []) {
            if (!isset(PERSONAS_NUEVAS[$curp])) {
                $errores[] = "Sin persona para {$fila['nombre_origen']} ({$curp}).";
                continue;
            }
            $resueltos[] = $fila + ['id_persona' => 0, 'accion' => 'crear'];
            $creaciones++;
            continue;
        }

        $persona = $candidatos[0];
        $curpActual = strtoupper(trim((string) ($persona['curp'] ?? '')));
        if ($encontradoPor === 'nombre' && $curpActual !== '' && $curpActual !== $curp && (CORRECCIONES_CURP_AUTORIZADAS[$curp] ?? null) !== $curpActual) {
            $errores[] = "CURP distinto para {$fila['nombre_origen']}: Sparta {$curpActual}, Excel {$curp}.";
            continue;
        }

        $accion = (($persona['estatus'] ?? '') === 'Baja') ? 'reactivar' : 'actualizar';
        if ($accion === 'reactivar') {
            $reactivaciones++;
        }
        if ($curpActual !== $curp) {
            $curpsCorregidos++;
        }
        $resueltos[] = $fila + ['id_persona' => (int) $persona['id'], 'accion' => $accion];
    }

    $curpPorPersona = [];
    foreach ($resueltos as $fila) {
        $idPersona = (int) $fila['id_persona'];
        if ($idPersona <= 0) {
            continue;
        }
        if (isset($curpPorPersona[$idPersona]) && $curpPorPersona[$idPersona] !== $fila['curp']) {
            $errores[] = "La persona {$idPersona} coincide con dos CURP del archivo ({$curpPorPersona[$idPersona]} y {$fila['curp']}).";
            continue;
        }
        $curpPorPersona[$idPersona] = $fila['curp'];
    }

    $resumen = [
        'archivo' => basename($archivo),
        'plantilla_excel' => count($plantilla),
        'resueltos' => count($resueltos),
        'creaciones' => $creaciones,
        'reactivaciones' => $reactivaciones,
        'curps_a_completar_o_corregir' => $curpsCorregidos,
        'errores' => $errores,
        'modo' => $aplicar ? 'aplicar' : 'simulacion',
    ];

    if ($errores !== []) {
        echo json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        exit(2);
    }

    if (!$aplicar) {
        echo json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        exit(0);
    }

    // DDL provoca un commit implicito en MySQL, por eso debe ejecutarse antes
    // de abrir la transaccion que protege la sincronizacion del padron.
    asegurarTabla($db);
    asegurarColumnaExterno($db);

    $db->beginTransaction();
    try {
        $actualizaciones = [];
        $registrosPlantilla = [];
        foreach ($resueltos as $fila) {
            $idPersona = (int) $fila['id_persona'];
            if ($fila['accion'] === 'crear') {
                $datos = PERSONAS_NUEVAS[$fila['curp']];
                $db->CRUD(
                    "INSERT INTO estado_cuenta.persona
                        (id_empresa, nombres, segundo_nombre, apellidop, apellidom, curp, estatus, fecha_registro)
                     VALUES
                        (:id_empresa, :nombres, :segundo_nombre, :apellidop, :apellidom, :curp, 'Activo', NOW())",
                    $datos + ['id_empresa' => $fila['id_empresa'], 'curp' => $fila['curp']]
                );
                $idPersona = $db->lastInsertId();
            } else {
                $actualizaciones[$idPersona] = [
                    'id_empresa' => (int) $fila['id_empresa'],
                    'curp' => $fila['curp'],
                ];
            }

            $registrosPlantilla[] = $fila + ['id_persona_final' => $idPersona];
        }

        foreach (array_chunk($actualizaciones, 200, true) as $lote) {
            $ids = array_map('intval', array_keys($lote));
            $empresaCase = [];
            $curpCase = [];
            foreach ($lote as $idPersona => $datos) {
                $empresaCase[] = "WHEN {$idPersona} THEN " . (int) $datos['id_empresa'];
                $curpCase[] = "WHEN {$idPersona} THEN '" . $datos['curp'] . "'";
            }
            $db->CRUD(
                "UPDATE estado_cuenta.persona
                 SET id_empresa = CASE id " . implode(' ', $empresaCase) . " END,
                     curp = CASE id " . implode(' ', $curpCase) . " END,
                     estatus = 'Activo'
                 WHERE id IN (" . implode(',', $ids) . ")"
            );
        }

        foreach (array_chunk($registrosPlantilla, 200) as $indiceLote => $lote) {
            $valores = [];
            $parametros = [];
            foreach ($lote as $indice => $fila) {
                $sufijo = $indiceLote . '_' . $indice;
                $valores[] = "(:curp_{$sufijo}, " . (int) $fila['id_persona_final'] . ', ' . (int) $fila['id_empresa'] . ", :origen_{$sufijo}, :nombre_{$sufijo}, 1, NOW())";
                $parametros["curp_{$sufijo}"] = $fila['curp'];
                $parametros["origen_{$sufijo}"] = ORIGEN_PLANTILLA;
                $parametros["nombre_{$sufijo}"] = $fila['nombre_origen'];
            }
            $db->CRUD(
                "INSERT INTO estado_cuenta.rrhh_plantilla_activa
                    (curp, id_persona, id_empresa, origen, nombre_origen, activo, fecha_sincronizacion)
                 VALUES " . implode(',', $valores) . "
                 ON DUPLICATE KEY UPDATE
                    id_persona = VALUES(id_persona),
                    id_empresa = VALUES(id_empresa),
                    nombre_origen = VALUES(nombre_origen),
                    activo = 1,
                    fecha_sincronizacion = NOW()",
                $parametros
            );
        }

        $curpsSql = implode(',', array_map(static fn(string $curp): string => "'" . $curp . "'", array_keys($plantilla)));
        $db->CRUD(
            "UPDATE estado_cuenta.rrhh_plantilla_activa
             SET activo = 0, fecha_sincronizacion = NOW()
             WHERE origen = :origen
               AND curp NOT IN ({$curpsSql})",
            ['origen' => ORIGEN_PLANTILLA]
        );

        // El Excel es la fuente de plantilla vigente. Un activo que no aparece
        // en ese padrón se conserva en Sparta, pero queda identificado como
        // externo y no debe formar parte de los expedientes RR.HH.
        $db->CRUD("
            UPDATE estado_cuenta.persona p
            INNER JOIN estado_cuenta.rrhh_plantilla_activa pla
                ON pla.id_persona = p.id
               AND pla.activo = 1
            SET p.es_externo = 0
            WHERE p.estatus = 'Activo'
              AND COALESCE(p.es_externo, 0) <> 0
        ");
        $db->CRUD("
            UPDATE estado_cuenta.persona p
            LEFT JOIN estado_cuenta.rrhh_plantilla_activa pla
                ON pla.id_persona = p.id
               AND pla.activo = 1
            SET p.es_externo = 1
            WHERE p.estatus = 'Activo'
              AND pla.id_persona IS NULL
              AND COALESCE(p.es_externo, 0) = 0
        ");
        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }

    $resumen['resultado'] = 'Plantilla activa sincronizada correctamente.';
    echo json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
