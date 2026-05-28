<?php

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$db = new Database();

$areas = $db->queryAll(
    "SELECT id, id_pais
     FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional
     ORDER BY id_pais, id"
);

$contadorPorPais = [];
foreach ($areas as $area) {
    $idPais = (int)($area['id_pais'] ?? 1);
    $idArea = (int)($area['id'] ?? 0);
    if ($idArea <= 0) {
        continue;
    }

    if (!isset($contadorPorPais[$idPais])) {
        $contadorPorPais[$idPais] = 0;
    }

    $contadorPorPais[$idPais]++;
    $nombreDirección = 'Dirección ' . $contadorPorPais[$idPais];

    $db->CRUD(
        "INSERT IGNORE INTO __SPARTA_SECRET_REDACTED__.direcciones_organizacion (nombre, id_pais, activo)
         VALUES (:nombre, :id_pais, 1)",
        ['nombre' => $nombreDireccion, 'id_pais' => $idPais]
    );

    $Dirección = $db->queryOne(
        "SELECT id
         FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion
         WHERE id_pais = :id_pais AND nombre = :nombre
         LIMIT 1",
        ['id_pais' => $idPais, 'nombre' => $nombreDireccion]
    );

    $idDirección = (int)($direccion['id'] ?? 0);
    if ($idDirección <= 0) {
        continue;
    }

    $db->CRUD(
        "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_direcciones (id_direccion, id_departamento_organizacional, activo)
         VALUES (:id_direccion, :id_area, 1)
         ON DUPLICATE KEY UPDATE id_Dirección = VALUES(id_direccion), activo = 1, fecha_actualizacion = NOW()",
        ['id_direccion' => $idDireccion, 'id_area' => $idArea]
    );
}

$db->CRUD(
    "UPDATE __SPARTA_SECRET_REDACTED__.direcciones_organizacion dir
     LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_direcciones ad
       ON ad.id_Dirección = dir.id
      AND COALESCE(ad.activo, 1) = 1
     SET dir.activo = 0
     WHERE ad.id IS NULL
       AND LOWER(TRIM(dir.nombre)) IN ('Dirección general', 'direcciÃƒÂ³n general', 'direcciÃƒÆ’Ã‚Â³n general')"
);

echo "Direcciones reasignadas: una Dirección por area.\n";
