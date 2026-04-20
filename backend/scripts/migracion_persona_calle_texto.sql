-- Permitir captura libre de calle cuando no exista catálogo nivel 4.
ALTER TABLE __SPARTA_SECRET_REDACTED__.persona
  ADD COLUMN domicilio_calle_texto VARCHAR(180) NULL DEFAULT NULL AFTER id_div_nivel4;
