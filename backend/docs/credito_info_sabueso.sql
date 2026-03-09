-- Tabla para guardar información de ingresos (donde trabaja) extraída del FAD_DOC.
-- Solo para mostrar en Sabueso (modal Iniciar rastreo).
-- Ejecutar en el esquema __SPARTA_SECRET_REDACTED__ (misma BD donde está ticket).

CREATE TABLE IF NOT EXISTS credito_info_sabueso (
    id_credito INT UNSIGNED NOT NULL PRIMARY KEY,
    informacion_ingresos TEXT NULL COMMENT 'Texto sección Información de Ingresos del FAD_DOC',
    empresa VARCHAR(255) NULL COMMENT 'Nombre de la empresa o negocio (donde trabaja)',
    empleado VARCHAR(255) NULL COMMENT 'Empleado / actividad laboral',
    ingreso_mensual_neto VARCHAR(50) NULL COMMENT 'Ingreso mensual neto',
    telefono VARCHAR(20) NULL COMMENT 'Teléfono laboral',
    fecha_extraccion DATETIME NULL,
    created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT 'Datos extraídos para Sabueso (donde trabaja desde FAD_DOC)';

-- Si la tabla ya existía sin estas columnas, ejecutar:
-- ALTER TABLE credito_info_sabueso
--   ADD COLUMN empresa VARCHAR(255) NULL AFTER informacion_ingresos,
--   ADD COLUMN empleado VARCHAR(255) NULL AFTER empresa,
--   ADD COLUMN ingreso_mensual_neto VARCHAR(50) NULL AFTER empleado,
--   ADD COLUMN telefono VARCHAR(20) NULL AFTER ingreso_mensual_neto;
