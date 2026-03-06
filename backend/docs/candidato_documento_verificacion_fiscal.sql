-- Migración: columna verificacion_fiscal_json en candidato_documento
-- Para guardar el resultado de la API verificar-constancia-fiscal-documento (vigencia, Asalariado, Régimen)
-- y mostrarlo en el modal Documentación como tooltip.
-- Ejecutar en el mismo esquema donde está candidato_documento.

ALTER TABLE candidato_documento
ADD COLUMN verificacion_fiscal_json TEXT NULL
COMMENT 'JSON con resultado de verificación constancia fiscal';
