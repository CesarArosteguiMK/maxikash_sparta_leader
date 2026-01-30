-- Asignación de tickets: se usa la tabla asignacion_ticket (id_ticket, id_persona_asignada, fecha_asignacion, fecha_liberacion, activo).
-- No es necesario agregar columna a la tabla ticket; el Panel Admin Sabueso lee el "Asignado a" desde asignacion_ticket.
-- Si en tu BD ya existe asignacion_ticket (como en el diagrama), no hace falta ejecutar el CREATE de abajo.

CREATE TABLE IF NOT EXISTS asignacion_ticket (
    id_asignacion       INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_ticket           INT NOT NULL,
    id_persona_asignada INT NOT NULL,
    fecha_asignacion    DATETIME NULL,
    fecha_liberacion    DATETIME NULL,
    activo              TINYINT DEFAULT 1,
    KEY idx_asignacion_ticket (id_ticket),
    KEY idx_asignacion_persona (id_persona_asignada)
);
