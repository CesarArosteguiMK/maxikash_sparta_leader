<?php
declare(strict_types=1);

require __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$db = new Database();

$esperados = [
    'Aguascalientes' => 11,
    'Baja California' => 7,
    'Baja California Sur' => 5,
    'Campeche' => 13,
    'Chiapas' => 124,
    'Chihuahua' => 67,
    'Coahuila' => 38,
    'Colima' => 10,
    'Durango' => 39,
    'Estado de México' => 125,
    'Guanajuato' => 46,
    'Guerrero' => 85,
    'Hidalgo' => 84,
    'Jalisco' => 125,
    'Michoacán' => 113,
    'Morelos' => 36,
    'Nayarit' => 20,
    'Nuevo León' => 51,
    'Oaxaca' => 570,
    'Puebla' => 217,
    'Querétaro' => 18,
    'Quintana Roo' => 11,
    'San Luis Potosí' => 59,
    'Sinaloa' => 20,
    'Sonora' => 72,
    'Tabasco' => 17,
    'Tamaulipas' => 43,
    'Tlaxcala' => 60,
    'Veracruz' => 212,
    'Yucatán' => 106,
    'Zacatecas' => 58,
    'Ciudad de México' => 16,
];

function out(string $titulo, array $rows): void
{
    echo "\n## {$titulo}\n";
    if (!$rows) {
        echo "(sin resultados)\n";
        return;
    }
    foreach ($rows as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    }
}

$resumen = $db->queryAll(
    "SELECT id_pais, nivel, id_tipo, COUNT(*) total, SUM(activo = 1) activos
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
     GROUP BY id_pais, nivel, id_tipo
     ORDER BY id_pais, nivel, id_tipo"
);
out('Resumen por país / nivel / tipo', $resumen);

$estados = $db->queryAll(
    "SELECT e.id, e.nombre AS estado, COUNT(m.id) AS municipios
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas e
     LEFT JOIN __SPARTA_SECRET_REDACTED__.divisiones_administrativas m
       ON m.id_padre = e.id AND m.nivel = 2 AND m.activo = 1
     WHERE e.id_pais = 1 AND e.nivel = 1 AND e.activo = 1
     GROUP BY e.id, e.nombre
     ORDER BY e.nombre"
);

$comparativo = [];
foreach ($estados as $estado) {
    $nombre = (string) $estado['estado'];
    $actual = (int) $estado['municipios'];
    $esperado = $esperados[$nombre] ?? null;
    $comparativo[] = [
        'estado' => $nombre,
        'actual' => $actual,
        'esperado' => $esperado,
        'diferencia' => $esperado === null ? null : $actual - $esperado,
    ];
}
out('Comparativo municipios/alcaldías por entidad', $comparativo);

$duplicados = $db->queryAll(
    "SELECT id_pais, nivel, id_padre,
            UPPER(TRIM(nombre)) AS nombre_norm,
            COALESCE(NULLIF(TRIM(codigo_interno), ''), '') AS codigo_norm,
            COUNT(*) AS total,
            SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY id), ',', 12) AS ids
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
     WHERE activo = 1
     GROUP BY id_pais, nivel, id_padre, nombre_norm, codigo_norm
     HAVING COUNT(*) > 1
     ORDER BY total DESC, nivel, nombre_norm
     LIMIT 80"
);
out('Duplicados exactos por padre/nivel/nombre/código', $duplicados);

$duplicadosNombre = $db->queryAll(
    "SELECT id_pais, nivel, id_padre,
            UPPER(TRIM(nombre)) AS nombre_norm,
            COUNT(*) AS total,
            SUBSTRING_INDEX(GROUP_CONCAT(CONCAT(id, ':', COALESCE(codigo_interno, '')) ORDER BY id), ',', 12) AS ids_codigos
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
     WHERE activo = 1
     GROUP BY id_pais, nivel, id_padre, nombre_norm
     HAVING COUNT(*) > 1
     ORDER BY total DESC, nivel, nombre_norm
     LIMIT 80"
);
out('Nombres repetidos por padre/nivel aunque cambie CP/código', $duplicadosNombre);

$huerfanos = $db->queryAll(
    "SELECT h.id, h.id_pais, h.nivel, h.id_tipo, h.id_padre, h.nombre, h.codigo_interno
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas h
     LEFT JOIN __SPARTA_SECRET_REDACTED__.divisiones_administrativas p ON p.id = h.id_padre
     WHERE h.id_padre IS NOT NULL
       AND (p.id IS NULL OR p.activo <> 1 OR p.id_pais <> h.id_pais OR p.nivel <> h.nivel - 1)
     ORDER BY h.nivel, h.id
     LIMIT 80"
);
out('Registros huérfanos o con padre de nivel incorrecto', $huerfanos);

$cpInvalidos = $db->queryAll(
    "SELECT id, id_padre, nombre, codigo_interno
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
     WHERE id_pais = 1 AND nivel = 3 AND activo = 1
       AND COALESCE(TRIM(codigo_interno), '') <> ''
       AND TRIM(codigo_interno) NOT REGEXP '^[0-9]{5}$'
     ORDER BY id
     LIMIT 80"
);
out('Colonias/asentamientos con código postal inválido', $cpInvalidos);

$nombresVacios = $db->queryAll(
    "SELECT id, id_pais, nivel, id_tipo, id_padre, nombre, codigo_interno
     FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
     WHERE activo = 1 AND TRIM(nombre) = ''
     ORDER BY nivel, id
     LIMIT 80"
);
out('Nombres vacíos', $nombresVacios);

$referencias = $db->queryAll(
    "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE REFERENCED_TABLE_SCHEMA = '__SPARTA_SECRET_REDACTED__'
       AND REFERENCED_TABLE_NAME = 'divisiones_administrativas'
     ORDER BY TABLE_NAME, COLUMN_NAME"
);
out('Llaves foráneas que apuntan al catálogo', $referencias);
