-- Notas de revisión para identificación oficial (calidad: brillo, borroso, etc.).
-- Se guardan para mostrar en el modal y que Capital Humano revise manualmente.
-- El documento se acepta siempre; las notas son informativas.

ALTER TABLE candidato_documento
ADD COLUMN verificacion_calidad_json TEXT NULL
COMMENT 'JSON con notas de revisión (ej. exceso de brillo) para ID oficial';
