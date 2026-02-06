-- Soft delete de tickets: registrar quién y cuándo eliminó/cerró.
-- Panel "Cerrado/Eliminado" muestra tickets con fecha_eliminacion no nula.
-- Fechas en zona horaria CDMX.
-- Ejecutar una sola vez; si las columnas ya existen, omitir o comentar.

ALTER TABLE ticket ADD COLUMN fecha_eliminacion DATETIME NULL DEFAULT NULL;
ALTER TABLE ticket ADD COLUMN id_persona_elimino INT NULL DEFAULT NULL;
ALTER TABLE ticket ADD KEY idx_ticket_fecha_eliminacion (fecha_eliminacion);
