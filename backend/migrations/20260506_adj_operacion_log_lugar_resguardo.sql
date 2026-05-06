-- Mis adjudicaciones: lugar de resguardo (combo) + texto si "otro"
-- Ejecutar en la BD de adjudicación / adj_operacion según el entorno.

ALTER TABLE adj_operacion
    ADD COLUMN log_lugar_resguardo VARCHAR(32) NULL DEFAULT NULL COMMENT 'mi_domicilio|sucursal|otro' AFTER log_estado,
    ADD COLUMN log_lugar_otro VARCHAR(200) NULL DEFAULT NULL COMMENT 'Si lugar=otro: especificar' AFTER log_lugar_resguardo;
