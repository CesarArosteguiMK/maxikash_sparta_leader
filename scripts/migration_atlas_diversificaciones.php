<?php
require __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require __DIR__ . '/../backend/core/Database.php';

$db = new Core\Database();

$db->CRUD("
    CREATE TABLE IF NOT EXISTS atlas_catalogo_diversificaciones (
        id BIGINT NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(120) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uk_atlas_diversificaciones_nombre (nombre),
        KEY idx_atlas_diversificaciones_activo (activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
");

$db->CRUD("
    ALTER TABLE atlas_catalogo_diversificaciones
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
");

$cols = $db->queryAll("SHOW COLUMNS FROM atlas_catalogo_sucursales LIKE 'diversificacion_id'");
if (!$cols) {
    $db->CRUD("
        ALTER TABLE atlas_catalogo_sucursales
        ADD COLUMN diversificacion_id BIGINT NULL AFTER diversificacion,
        ADD KEY idx_atlas_sucursales_diversificacion_id (diversificacion_id)
    ");
}

$fk = $db->queryOne("
    SELECT CONSTRAINT_NAME
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'atlas_catalogo_sucursales'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
      AND CONSTRAINT_NAME = 'fk_atlas_sucursales_diversificacion'
    LIMIT 1
");
if (!$fk) {
    $db->CRUD("
        ALTER TABLE atlas_catalogo_sucursales
        ADD CONSTRAINT fk_atlas_sucursales_diversificacion
        FOREIGN KEY (diversificacion_id)
        REFERENCES atlas_catalogo_diversificaciones(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
    ");
}

$db->CRUD("
    INSERT INTO atlas_catalogo_diversificaciones (nombre, activo)
    SELECT DISTINCT TRIM(s.diversificacion), 1
    FROM atlas_catalogo_sucursales s
    WHERE s.diversificacion IS NOT NULL
      AND TRIM(s.diversificacion) <> ''
    ON DUPLICATE KEY UPDATE activo = VALUES(activo)
");

$db->CRUD("
    INSERT INTO atlas_catalogo_diversificaciones (nombre, activo)
    VALUES ('Sin diversificacion', 1)
    ON DUPLICATE KEY UPDATE activo = VALUES(activo)
");

$db->CRUD("
    UPDATE atlas_catalogo_sucursales s
    INNER JOIN atlas_catalogo_diversificaciones d
            ON d.nombre = TRIM(s.diversificacion)
    SET s.diversificacion_id = d.id
    WHERE s.diversificacion IS NOT NULL
      AND TRIM(s.diversificacion) <> ''
");

$db->CRUD("
    UPDATE atlas_catalogo_sucursales s
    INNER JOIN atlas_catalogo_diversificaciones d
            ON d.nombre = 'Sin diversificacion'
    SET s.diversificacion_id = d.id,
        s.diversificacion = d.nombre
    WHERE s.diversificacion_id IS NULL
");

echo "Migracion Atlas diversificaciones completada.\n";
