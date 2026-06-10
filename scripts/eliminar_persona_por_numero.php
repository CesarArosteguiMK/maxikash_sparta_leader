<?php

declare(strict_types=1);

use Core\Database;

require dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';
require dirname(__DIR__) . '/backend/core/Database.php';

function e_arg(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $arg) {
        if ($arg === $name) return '1';
        if (str_starts_with($arg, $name . '=')) return substr($arg, strlen($name) + 1);
    }
    return $default;
}

function e_ident(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

$numero = trim((string)(e_arg($_SERVER['argv'] ?? [], '--numero', '') ?? ''));
$apply = e_arg($_SERVER['argv'] ?? [], '--apply') === '1';

if ($numero === '') {
    fwrite(STDERR, "Uso: php scripts/eliminar_persona_por_numero.php --numero=1515 [--apply]\n");
    exit(1);
}

$schema = getenv('DB_NAME') ?: getenv('DB_ESQUEMA') ?: '__SPARTA_SECRET_REDACTED__';
$db = new Database();

$persona = $db->queryOne("
    SELECT id, numero_empleado, nombres, segundo_nombre, apellidop, apellidom, estatus
    FROM __SPARTA_SECRET_REDACTED__.persona
    WHERE TRIM(numero_empleado) = :numero
    LIMIT 1
", ['numero' => $numero]);

if (!$persona) {
    echo json_encode([
        'modo' => $apply ? 'APPLY' : 'DRY-RUN',
        'numero' => $numero,
        'encontrado' => false,
        'mensaje' => 'No existe una persona con ese numero_empleado.',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

$idPersona = (int)$persona['id'];
$nombre = trim(implode(' ', array_filter([
    $persona['nombres'] ?? '',
    $persona['segundo_nombre'] ?? '',
    $persona['apellidop'] ?? '',
    $persona['apellidom'] ?? '',
])));

$idPersonaTables = $db->queryAll("
    SELECT c.TABLE_NAME AS tabla
    FROM INFORMATION_SCHEMA.COLUMNS c
    INNER JOIN INFORMATION_SCHEMA.TABLES t
            ON t.TABLE_SCHEMA = c.TABLE_SCHEMA
           AND t.TABLE_NAME = c.TABLE_NAME
           AND t.TABLE_TYPE = 'BASE TABLE'
    WHERE c.TABLE_SCHEMA = :schema
      AND c.COLUMN_NAME = 'id_persona'
      AND c.TABLE_NAME <> 'persona'
    ORDER BY c.TABLE_NAME
", ['schema' => $schema]);

$referenceColumns = $db->queryAll("
    SELECT c.TABLE_NAME AS tabla, c.COLUMN_NAME AS columna
    FROM INFORMATION_SCHEMA.COLUMNS c
    INNER JOIN INFORMATION_SCHEMA.TABLES t
            ON t.TABLE_SCHEMA = c.TABLE_SCHEMA
           AND t.TABLE_NAME = c.TABLE_NAME
           AND t.TABLE_TYPE = 'BASE TABLE'
    WHERE c.TABLE_SCHEMA = :schema
      AND c.COLUMN_NAME IN ('id_jefe', 'id_persona_baja', 'id_persona_cubre')
      AND c.TABLE_NAME <> 'persona'
    ORDER BY c.TABLE_NAME, c.COLUMN_NAME
", ['schema' => $schema]);

$resumen = [];
foreach ($idPersonaTables as $row) {
    $tabla = (string)$row['tabla'];
    $count = $db->queryOne(
        'SELECT COUNT(*) AS total FROM ' . e_ident($schema) . '.' . e_ident($tabla) . ' WHERE id_persona = :id',
        ['id' => $idPersona]
    );
    $total = (int)($count['total'] ?? 0);
    if ($total > 0) {
        $resumen[] = [
            'accion' => 'delete',
            'tabla' => $tabla,
            'columna' => 'id_persona',
            'filas' => $total,
        ];
    }
}

foreach ($referenceColumns as $row) {
    $tabla = (string)$row['tabla'];
    $columna = (string)$row['columna'];
    $count = $db->queryOne(
        'SELECT COUNT(*) AS total FROM ' . e_ident($schema) . '.' . e_ident($tabla) . ' WHERE ' . e_ident($columna) . ' = :id',
        ['id' => $idPersona]
    );
    $total = (int)($count['total'] ?? 0);
    if ($total > 0) {
        $resumen[] = [
            'accion' => 'set_null',
            'tabla' => $tabla,
            'columna' => $columna,
            'filas' => $total,
        ];
    }
}

if ($apply) {
    $db->beginTransaction();
    try {
        foreach ($referenceColumns as $row) {
            $tabla = (string)$row['tabla'];
            $columna = (string)$row['columna'];
            $db->CRUD(
                'UPDATE ' . e_ident($schema) . '.' . e_ident($tabla) .
                ' SET ' . e_ident($columna) . ' = NULL WHERE ' . e_ident($columna) . ' = :id',
                ['id' => $idPersona]
            );
        }

        foreach ($idPersonaTables as $row) {
            $tabla = (string)$row['tabla'];
            $db->CRUD(
                'DELETE FROM ' . e_ident($schema) . '.' . e_ident($tabla) . ' WHERE id_persona = :id',
                ['id' => $idPersona]
            );
        }

        $db->CRUD('DELETE FROM __SPARTA_SECRET_REDACTED__.persona WHERE id = :id', ['id' => $idPersona]);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

echo json_encode([
    'modo' => $apply ? 'APPLY' : 'DRY-RUN',
    'numero' => $numero,
    'encontrado' => true,
    'persona' => [
        'id' => $idPersona,
        'numero_empleado' => $persona['numero_empleado'] ?? '',
        'nombre' => $nombre,
        'estatus' => $persona['estatus'] ?? '',
    ],
    'acciones' => $resumen,
    'eliminado' => $apply,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
