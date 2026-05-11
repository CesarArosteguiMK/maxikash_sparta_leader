-- Agrega columnas de ubicación/domicilio al módulo de Candidatos.
-- Compatible con MySQL sin IF NOT EXISTS en ALTER TABLE.

ALTER TABLE candidatos
    ADD COLUMN id_div_nivel1 INT NULL AFTER id_pais,
    ADD COLUMN id_div_nivel2 INT NULL AFTER id_div_nivel1,
    ADD COLUMN id_div_nivel3 INT NULL AFTER id_div_nivel2,
    ADD COLUMN domicilio_calle_texto VARCHAR(180) NULL AFTER id_div_nivel3,
    ADD COLUMN domicilio_num_exterior VARCHAR(32) NULL AFTER domicilio_calle_texto,
    ADD COLUMN domicilio_num_interior VARCHAR(32) NULL AFTER domicilio_num_exterior;

ALTER TABLE candidatos
    ADD INDEX idx_candidatos_div_nivel1 (id_div_nivel1),
    ADD INDEX idx_candidatos_div_nivel2 (id_div_nivel2),
    ADD INDEX idx_candidatos_div_nivel3 (id_div_nivel3);
