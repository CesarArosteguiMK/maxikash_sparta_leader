-- ============================================================
-- Agregar tipo de documento a candidato_documento (los 10 requeridos)
-- ============================================================
ALTER TABLE candidato_documento
ADD COLUMN tipo_documento VARCHAR(120) NOT NULL DEFAULT '' AFTER id_candidato;
