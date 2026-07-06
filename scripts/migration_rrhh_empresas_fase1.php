<?php

require_once __DIR__ . '/../backend/core/DatabaseCliSupport.php';
require_once __DIR__ . '/../backend/core/Database.php';

use Core\Database;

$db = new Database();

function tableExists(Database $db, string $table): bool
{
    $row = $db->queryOne(
        "SELECT 1 AS ok
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
         LIMIT 1",
        ['table' => $table]
    );
    return !empty($row);
}

function columnExists(Database $db, string $table, string $column): bool
{
    $row = $db->queryOne(
        "SELECT 1 AS ok
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
           AND COLUMN_NAME = :column
         LIMIT 1",
        ['table' => $table, 'column' => $column]
    );
    return !empty($row);
}

function indexExists(Database $db, string $table, string $index): bool
{
    $row = $db->queryOne(
        "SELECT 1 AS ok
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
           AND INDEX_NAME = :index
         LIMIT 1",
        ['table' => $table, 'index' => $index]
    );
    return !empty($row);
}

function addColumnIfMissing(Database $db, string $table, string $column, string $definition, ?string $indexSql = null): void
{
    if (!tableExists($db, $table)) {
        echo "[SKIP] No existe {$table}\n";
        return;
    }
    if (!columnExists($db, $table, $column)) {
        $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.`{$table}` ADD COLUMN `{$column}` {$definition}");
        echo "[OK] Columna {$table}.{$column} agregada\n";
    } else {
        echo "[OK] Columna {$table}.{$column} ya existe\n";
    }
    if ($indexSql !== null) {
        $db->CRUD($indexSql);
    }
}

$db->CRUD("
    CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.rrhh_grupos_corporativos (
        id INT NOT NULL AUTO_INCREMENT,
        clave VARCHAR(50) NOT NULL,
        nombre VARCHAR(150) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY ux_rrhh_grupo_clave (clave),
        UNIQUE KEY ux_rrhh_grupo_nombre (nombre)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "[OK] Tabla rrhh_grupos_corporativos lista\n";

$db->CRUD("
    CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.rrhh_empresas (
        id INT NOT NULL AUTO_INCREMENT,
        id_grupo INT NOT NULL,
        clave VARCHAR(50) NOT NULL,
        nombre_comercial VARCHAR(150) NOT NULL,
        razon_social VARCHAR(200) NULL,
        rfc VARCHAR(20) NULL,
        registro_patronal VARCHAR(50) NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY ux_rrhh_empresa_clave (clave),
        KEY idx_rrhh_empresa_grupo_activo (id_grupo, activo),
        CONSTRAINT fk_rrhh_empresa_grupo
            FOREIGN KEY (id_grupo) REFERENCES __SPARTA_SECRET_REDACTED__.rrhh_grupos_corporativos(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "[OK] Tabla rrhh_empresas lista\n";

$db->CRUD(
    "INSERT INTO __SPARTA_SECRET_REDACTED__.rrhh_grupos_corporativos (id, clave, nombre, activo)
     VALUES (1, 'GRUPO_MAXIKASH', 'Grupo Maxikash', 1)
     ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), activo = VALUES(activo)"
);
echo "[OK] Grupo Maxikash listo\n";

$db->CRUD(
    "INSERT INTO __SPARTA_SECRET_REDACTED__.rrhh_empresas
        (id, id_grupo, clave, nombre_comercial, razon_social, rfc, registro_patronal, activo)
     VALUES
        (1, 1, 'MAXIKASH', 'MaxiKash', 'MaxiKash', NULL, NULL, 1)
     ON DUPLICATE KEY UPDATE
        id_grupo = VALUES(id_grupo),
        nombre_comercial = VALUES(nombre_comercial),
        razon_social = VALUES(razon_social),
        activo = VALUES(activo)"
);
echo "[OK] Empresa MaxiKash lista\n";

$db->CRUD(
    "INSERT INTO __SPARTA_SECRET_REDACTED__.rrhh_empresas
        (id, id_grupo, clave, nombre_comercial, razon_social, rfc, registro_patronal, activo)
     VALUES
        (2, 1, 'FURIA_MOTOS', 'Furia Motos', 'PENSIONAMAX S.A.P.I DE C.V', NULL, 'Y5511130105', 1)
     ON DUPLICATE KEY UPDATE
        id_grupo = VALUES(id_grupo),
        nombre_comercial = VALUES(nombre_comercial),
        razon_social = VALUES(razon_social),
        registro_patronal = VALUES(registro_patronal),
        activo = VALUES(activo)"
);
echo "[OK] Empresa Furia Motos / Pensionamax lista\n";

$targets = [
    'persona' => "INT NOT NULL DEFAULT 1 AFTER id",
    'candidatos' => "INT NOT NULL DEFAULT 1 AFTER id",
    'direcciones_organizacion' => "INT NOT NULL DEFAULT 1 AFTER id",
    'departamento_organizacional' => "INT NOT NULL DEFAULT 1 AFTER id",
    'departamento' => "INT NOT NULL DEFAULT 1 AFTER id",
    'puesto' => "INT NOT NULL DEFAULT 1 AFTER id",
    'vacantes_personal' => "INT NOT NULL DEFAULT 1 AFTER id",
];

foreach ($targets as $table => $definition) {
    addColumnIfMissing(
        $db,
        $table,
        'id_empresa',
        $definition,
        indexExists($db, $table, 'idx_' . $table . '_empresa') || !tableExists($db, $table)
            ? null
            : "ALTER TABLE __SPARTA_SECRET_REDACTED__.`{$table}` ADD INDEX idx_{$table}_empresa (id_empresa)"
    );
}

foreach (array_keys($targets) as $table) {
    if (!tableExists($db, $table) || !columnExists($db, $table, 'id_empresa')) {
        continue;
    }
    $db->CRUD("UPDATE __SPARTA_SECRET_REDACTED__.`{$table}` SET id_empresa = 1 WHERE id_empresa IS NULL OR id_empresa = 0");
    echo "[OK] {$table}.id_empresa normalizado a MaxiKash cuando venia vacio\n";
}

echo "Migracion RRHH empresas fase 1 aplicada.\n";
