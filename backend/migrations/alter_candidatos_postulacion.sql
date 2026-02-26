-- ============================================================
-- Candidatos: campos para postulación (país, jefe, fecha, legión, usuario/contraseña, enviada)
-- Ejecutar una sola vez. Si alguna columna ya existe, comentar la línea correspondiente.
-- ============================================================

ALTER TABLE candidatos
    ADD COLUMN id_pais INT UNSIGNED NULL COMMENT 'País del candidato' AFTER telefono,
    ADD COLUMN id_posible_jefe INT UNSIGNED NULL COMMENT 'Posible jefe (persona.id)' AFTER id_departamento,
    ADD COLUMN fecha_postulacion DATE NULL COMMENT 'Fecha de postulación' AFTER id_posible_jefe,
    ADD COLUMN id_legion INT UNSIGNED NULL COMMENT 'Legión asignada' AFTER fecha_postulacion,
    ADD COLUMN usuario VARCHAR(50) NULL COMMENT 'Usuario para acceso' AFTER id_legion,
    ADD COLUMN contrasena VARCHAR(255) NULL COMMENT 'Contraseña' AFTER usuario,
    ADD COLUMN postulacion_enviada TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=postulación enviada por correo' AFTER contrasena,
    ADD COLUMN fecha_postulacion_enviada DATETIME NULL COMMENT 'Cuándo se envió la postulación' AFTER postulacion_enviada;
