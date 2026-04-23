-- =============================================================================
-- Índices SUGERIDOS para `tbl_segundometro_histo` (__SPARTA_SECRET_REDACTED__)
-- Uso: DBeaver, usuario con permiso INDEX.
-- Si "Duplicate key" / "already exists": el índice ya existe, omitir esa sentencia.
-- Nota: NO uses ALGORITHM/LOCK con CREATE INDEX en muchos servidores (error 1064);
--       la forma compatible es el CREATE INDEX simple de abajo.
-- =============================================================================

USE `__SPARTA_SECRET_REDACTED__`;

-- 1) Ventana por `fecha_hora_insert`
CREATE INDEX idx_histo_fecha_hora_insert
  ON tbl_segundometro_histo (fecha_hora_insert);

-- 2) `SEMANA` + `Fecha_primer_vencimiento`
--    Sin prefijos (N): si usas SEMANA(100) y la columna es más corta, no es string, o es
--    DATE, MySQL devuelve error 1089. El índice compuesto con columnas completas es lo más seguro.
CREATE INDEX idx_histo_semana_fecha_primer_venc
  ON tbl_segundometro_histo (SEMANA, Fecha_primer_vencimiento);

-- ---------------------------------------------------------------------------
-- Opcional (MySQL 5.6+): misma lógica con ALTER + online DDL, si en tu versión
-- aplica. Si falla, quédate solo con el bloque de arriba.
-- ---------------------------------------------------------------------------
-- ALTER TABLE tbl_segundometro_histo
--   ADD INDEX idx_histo_fecha_hora_insert (fecha_hora_insert),
--   ALGORITHM=INPLACE, LOCK=NONE;
-- ALTER TABLE tbl_segundometro_histo
--   ADD INDEX idx_histo_semana_fecha_primer_venc (SEMANA, Fecha_primer_vencimiento),
--   ALGORITHM=INPLACE, LOCK=NONE;
--
-- Si el índice 2) falla por TEXT sin longitud: (SEMANA(191), Fecha_primer_vencimiento) — ajusta 191 al max del índice.
-- Plan B: dos índices separados
--   CREATE INDEX idx_histo_solo_semana ON tbl_segundometro_histo (SEMANA(191));
--   CREATE INDEX idx_histo_solo_fpv      ON tbl_segundometro_histo (Fecha_primer_vencimiento);
