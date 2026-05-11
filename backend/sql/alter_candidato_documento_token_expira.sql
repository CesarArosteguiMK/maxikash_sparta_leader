-- Vencimiento del enlace público de subida de documentos (alineado al plazo del correo).
-- Ejecutar una vez en la BD __SPARTA_SECRET_REDACTED__ (phpMyAdmin o cliente MySQL).

ALTER TABLE candidato_documento_token
  ADD COLUMN expira DATETIME NULL DEFAULT NULL COMMENT 'Fin de plazo CDMX (23:59:59 del último día hábil)' AFTER token;
