-- ============================================================
-- Candidatos: guardar último resultado de verificación API (expediente)
-- Ejecutar una sola vez. Si la columna ya existe, no ejecutar.
-- ============================================================

ALTER TABLE candidatos
    ADD COLUMN ultima_verificacion_expediente TEXT NULL COMMENT 'JSON: resultado de validar-expediente (checks_ok, alertas, etc.)' AFTER notas;
