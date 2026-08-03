<?php

declare(strict_types=1);

require dirname(__DIR__) . '/backend/core/EnvLoader.php';
require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

use Core\Database;

$aplicar = in_array('--apply', $_SERVER['argv'] ?? [], true);
$mapeos = [
    3051 => ['nuevo' => 202, 'nombre' => 'Ventas'],
    3052 => ['nuevo' => 203, 'nombre' => 'Expedientes'],
];

$db = new Database();
$estadoInicial = $db->queryAll(
    'SELECT id, pestana, nombre, descripcion, activo
       FROM modulos_web
      WHERE id IN (202, 203, 3051, 3052)
      ORDER BY id'
);

if (!$aplicar) {
    echo json_encode([
        'modo' => 'diagnostico',
        'mensaje' => 'No se hicieron cambios. Agrega --apply para ejecutar la migracion.',
        'estado' => $estadoInicial,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    exit(0);
}

$pendientes = [];
foreach ($mapeos as $idViejo => $mapeo) {
    $idNuevo = (int) $mapeo['nuevo'];
    $nombreEsperado = (string) $mapeo['nombre'];
    $viejo = $db->queryOne(
        'SELECT id, nombre FROM modulos_web WHERE id = :id LIMIT 1',
        ['id' => $idViejo]
    );
    $nuevo = $db->queryOne(
        'SELECT id, nombre FROM modulos_web WHERE id = :id LIMIT 1',
        ['id' => $idNuevo]
    );

    if ($viejo && $nuevo) {
        throw new RuntimeException("Conflicto: existen simultaneamente los modulos $idViejo y $idNuevo.");
    }
    if ($viejo) {
        if ((string) $viejo['nombre'] !== $nombreEsperado) {
            throw new RuntimeException(
                "El modulo $idViejo no corresponde a $nombreEsperado; se cancela para no sobrescribir datos."
            );
        }
        $pendientes[$idViejo] = $mapeo;
        continue;
    }
    if (!$nuevo || (string) $nuevo['nombre'] !== $nombreEsperado) {
        throw new RuntimeException("No se encontro el modulo $nombreEsperado en $idViejo ni en $idNuevo.");
    }
}

if ($pendientes) {
    $db->beginTransaction();
    try {
        foreach ($pendientes as $idViejo => $mapeo) {
            $idNuevo = (int) $mapeo['nuevo'];
            $nombre = (string) $mapeo['nombre'];
            $nombreTemporal = '__migrando_modulo_' . $idViejo . '_' . $idNuevo;

            $db->CRUD(
                'UPDATE modulos_web SET nombre = :temporal WHERE id = :id AND nombre = :nombre',
                ['temporal' => $nombreTemporal, 'id' => $idViejo, 'nombre' => $nombre]
            );
            $db->CRUD(
                'INSERT INTO modulos_web (id, pestana, nombre, descripcion, activo)
                 SELECT :id_nuevo, pestana, :nombre, descripcion, activo
                   FROM modulos_web
                  WHERE id = :id_viejo',
                ['id_nuevo' => $idNuevo, 'nombre' => $nombre, 'id_viejo' => $idViejo]
            );
            $db->CRUD(
                'INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id)
                 SELECT DISTINCT viejo.usuario_id, :id_nuevo
                   FROM asigna_modulo_web viejo
                  WHERE viejo.modulo_web_id = :id_viejo
                    AND NOT EXISTS (
                        SELECT 1
                          FROM asigna_modulo_web nuevo
                         WHERE nuevo.usuario_id = viejo.usuario_id
                           AND nuevo.modulo_web_id = :id_nuevo_existente
                    )',
                [
                    'id_nuevo' => $idNuevo,
                    'id_viejo' => $idViejo,
                    'id_nuevo_existente' => $idNuevo,
                ]
            );
            $db->CRUD(
                'DELETE FROM asigna_modulo_web WHERE modulo_web_id = :id',
                ['id' => $idViejo]
            );
            $db->CRUD(
                'DELETE FROM modulos_web WHERE id = :id',
                ['id' => $idViejo]
            );
        }
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollback();
        }
        throw $e;
    }
}

$maximo = $db->queryOne('SELECT MAX(id) AS max_id FROM modulos_web');
$siguienteId = max(1, (int) ($maximo['max_id'] ?? 0) + 1);
$db->CRUD("ALTER TABLE modulos_web AUTO_INCREMENT = $siguienteId");
$secuencia = $db->queryOne(
    "SELECT AUTO_INCREMENT
       FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'modulos_web'"
);
$secuenciaActual = (int) ($secuencia['AUTO_INCREMENT'] ?? 0);

// MySQL 8 conserva de forma persistente el mayor contador ya utilizado y no
// permite bajarlo con AUTO_INCREMENT=N. Se crea una copia exacta y vacia,
// se repueblan sus filas y se intercambia por la original; asi el contador
// nace nuevamente desde MAX(id) + 1.
if ($secuenciaActual > $siguienteId) {
    $nombreFk = 'asigna_modulo_web_ibfk_2';
    $tablaNueva = 'modulos_web_ai_reset_tmp';
    $fkRetirada = false;

    $existe = $db->queryOne(
        'SELECT TABLE_NAME
           FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla',
        ['tabla' => $tablaNueva]
    );
    if ($existe) {
        throw new RuntimeException(
            "La tabla auxiliar $tablaNueva ya existe; se cancela para no sobrescribirla."
        );
    }

    $triggers = $db->queryAll(
        "SELECT TRIGGER_NAME
           FROM information_schema.TRIGGERS
          WHERE TRIGGER_SCHEMA = DATABASE()
            AND EVENT_OBJECT_TABLE = 'modulos_web'"
    );
    if ($triggers) {
        throw new RuntimeException('modulos_web tiene triggers; se cancela el intercambio para no perderlos.');
    }

    $relacionesSalientes = $db->queryAll(
        "SELECT CONSTRAINT_NAME
           FROM information_schema.KEY_COLUMN_USAGE
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'modulos_web'
            AND REFERENCED_TABLE_NAME IS NOT NULL"
    );
    if ($relacionesSalientes) {
        throw new RuntimeException('modulos_web tiene relaciones salientes no contempladas en la migracion.');
    }

    $resumenOriginal = $db->queryOne(
        'SELECT COUNT(*) AS total, COALESCE(SUM(id), 0) AS suma_ids FROM modulos_web'
    );
    $fkExiste = $db->queryOne(
        "SELECT CONSTRAINT_NAME
           FROM information_schema.REFERENTIAL_CONSTRAINTS
          WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = 'asigna_modulo_web'
            AND CONSTRAINT_NAME = :nombre
            AND REFERENCED_TABLE_NAME = 'modulos_web'",
        ['nombre' => $nombreFk]
    );
    $restaurarFk = (bool) $fkExiste;

    try {
        $db->CRUD("CREATE TABLE $tablaNueva LIKE modulos_web");
        $db->CRUD("TRUNCATE TABLE $tablaNueva");
        $db->CRUD("INSERT INTO $tablaNueva SELECT * FROM modulos_web");

        $resumenCopia = $db->queryOne(
            "SELECT COUNT(*) AS total, COALESCE(SUM(id), 0) AS suma_ids FROM $tablaNueva"
        );
        if ((string) ($resumenCopia['total'] ?? '') !== (string) ($resumenOriginal['total'] ?? '')
            || (string) ($resumenCopia['suma_ids'] ?? '') !== (string) ($resumenOriginal['suma_ids'] ?? '')) {
            throw new RuntimeException('La copia de modulos_web no coincide con la tabla original.');
        }

        $secuenciaCopia = $db->queryOne(
            'SELECT AUTO_INCREMENT
               FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla',
            ['tabla' => $tablaNueva]
        );
        if ((int) ($secuenciaCopia['AUTO_INCREMENT'] ?? 0) !== $siguienteId) {
            throw new RuntimeException('La copia no genero la secuencia esperada.');
        }

        if ($restaurarFk) {
            $db->CRUD("ALTER TABLE asigna_modulo_web DROP FOREIGN KEY $nombreFk");
            $fkRetirada = true;
        }

        // El nombre final debe crearse de nuevo: RENAME conserva el contador
        // historico que MySQL 8 asocia al objeto anterior.
        $db->CRUD('DROP TABLE modulos_web');
        $db->CRUD("CREATE TABLE modulos_web LIKE $tablaNueva");
        $db->CRUD('TRUNCATE TABLE modulos_web');
        $db->CRUD("INSERT INTO modulos_web SELECT * FROM $tablaNueva");

        $resumenFinal = $db->queryOne(
            'SELECT COUNT(*) AS total, COALESCE(SUM(id), 0) AS suma_ids FROM modulos_web'
        );
        if ((string) ($resumenFinal['total'] ?? '') !== (string) ($resumenOriginal['total'] ?? '')
            || (string) ($resumenFinal['suma_ids'] ?? '') !== (string) ($resumenOriginal['suma_ids'] ?? '')) {
            throw new RuntimeException('La tabla recreada no coincide con la copia verificada.');
        }

        if ($restaurarFk) {
            $db->CRUD(
                "ALTER TABLE asigna_modulo_web
                 ADD CONSTRAINT $nombreFk
                 FOREIGN KEY (modulo_web_id) REFERENCES modulos_web (id)
                 ON UPDATE NO ACTION ON DELETE NO ACTION"
            );
            $fkRetirada = false;
        }

        $huerfanas = $db->queryOne(
            'SELECT COUNT(*) AS total
               FROM asigna_modulo_web am
               LEFT JOIN modulos_web mw ON mw.id = am.modulo_web_id
              WHERE mw.id IS NULL'
        );
        if ((int) ($huerfanas['total'] ?? 0) !== 0) {
            throw new RuntimeException('Se detectaron asignaciones sin modulo despues del intercambio.');
        }

        $db->CRUD("DROP TABLE $tablaNueva");
    } catch (Throwable $e) {
        $originalExiste = $db->queryOne(
            "SELECT TABLE_NAME
               FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'modulos_web'"
        );
        $tablaNuevaExiste = $db->queryOne(
            'SELECT TABLE_NAME
               FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabla',
            ['tabla' => $tablaNueva]
        );

        if ($tablaNuevaExiste) {
            $resumenActual = $originalExiste
                ? $db->queryOne(
                    'SELECT COUNT(*) AS total, COALESCE(SUM(id), 0) AS suma_ids FROM modulos_web'
                )
                : null;
            $actualCompleta = $resumenActual
                && (string) ($resumenActual['total'] ?? '') === (string) ($resumenOriginal['total'] ?? '')
                && (string) ($resumenActual['suma_ids'] ?? '') === (string) ($resumenOriginal['suma_ids'] ?? '');

            if (!$actualCompleta) {
                if ($originalExiste) {
                    $db->CRUD('DROP TABLE modulos_web');
                }
                $db->CRUD("CREATE TABLE modulos_web LIKE $tablaNueva");
                $db->CRUD('TRUNCATE TABLE modulos_web');
                $db->CRUD("INSERT INTO modulos_web SELECT * FROM $tablaNueva");
                $originalExiste = ['TABLE_NAME' => 'modulos_web'];
            }
        }

        if ($fkRetirada && $originalExiste) {
            $db->CRUD(
                "ALTER TABLE asigna_modulo_web
                 ADD CONSTRAINT $nombreFk
                 FOREIGN KEY (modulo_web_id) REFERENCES modulos_web (id)
                 ON UPDATE NO ACTION ON DELETE NO ACTION"
            );
            $fkRetirada = false;
        }

        if ($tablaNuevaExiste && $originalExiste) {
            $db->CRUD("DROP TABLE $tablaNueva");
        }
        throw $e;
    }

    $secuencia = $db->queryOne(
        "SELECT AUTO_INCREMENT
           FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'modulos_web'"
    );
    $secuenciaActual = (int) ($secuencia['AUTO_INCREMENT'] ?? 0);
}

if ($secuenciaActual !== $siguienteId) {
    throw new RuntimeException(
        "No se pudo ajustar AUTO_INCREMENT: esperado $siguienteId, obtenido $secuenciaActual."
    );
}

$fkFinal = $db->queryOne(
    "SELECT CONSTRAINT_NAME
       FROM information_schema.REFERENTIAL_CONSTRAINTS
      WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND TABLE_NAME = 'asigna_modulo_web'
        AND CONSTRAINT_NAME = 'asigna_modulo_web_ibfk_2'
        AND REFERENCED_TABLE_NAME = 'modulos_web'"
);
if (!$fkFinal) {
    throw new RuntimeException('No se encontro la llave foranea de asigna_modulo_web a modulos_web.');
}

$huerfanasFinales = $db->queryOne(
    'SELECT COUNT(*) AS total
       FROM asigna_modulo_web am
       LEFT JOIN modulos_web mw ON mw.id = am.modulo_web_id
      WHERE mw.id IS NULL'
);
if ((int) ($huerfanasFinales['total'] ?? 0) !== 0) {
    throw new RuntimeException('Existen asignaciones de usuario sin un modulo_web valido.');
}

echo json_encode([
    'modo' => 'aplicado',
    'migrados' => array_keys($pendientes),
    'siguiente_id' => $secuenciaActual,
    'modulos' => $db->queryAll(
        'SELECT id, pestana, nombre, descripcion, activo
           FROM modulos_web
          WHERE id IN (202, 203, 3051, 3052)
          ORDER BY id'
    ),
    'asignaciones' => $db->queryAll(
        'SELECT modulo_web_id, COUNT(*) AS usuarios
           FROM asigna_modulo_web
          WHERE modulo_web_id IN (202, 203, 3051, 3052)
          GROUP BY modulo_web_id
          ORDER BY modulo_web_id'
    ),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
