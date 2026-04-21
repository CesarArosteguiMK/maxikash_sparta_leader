-- Ejecutar después de agregar la columna en persona (referencia; ajustar si ya existe).
ALTER TABLE __SPARTA_SECRET_REDACTED__.persona
    ADD COLUMN curp VARCHAR(18) NULL DEFAULT NULL
    COMMENT 'CURP México (18 caracteres alfanuméricos)'
    AFTER apellidom;
