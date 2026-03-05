-- Migración: Cerrar proceso de candidato (Paso 2)
-- Ejecutar una vez en la base de datos donde está la tabla candidatos.
-- Añade columnas para registrar motivo y descripción del cierre.

ALTER TABLE candidatos
  ADD COLUMN proceso_cerrado TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = proceso cerrado desde modal',
  ADD COLUMN motivo_cierre VARCHAR(100) NULL COMMENT 'Motivo fijo del cierre',
  ADD COLUMN descripcion_cierre TEXT NULL COMMENT 'Descripción opcional',
  ADD COLUMN fecha_cierre DATETIME NULL COMMENT 'Fecha/hora del cierre';
