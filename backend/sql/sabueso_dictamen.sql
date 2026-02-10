-- Dictamen por ticket: mensajes de dictamen (igual estructura que bitácora/chat).
-- Relación: 1 ticket → muchos mensajes dictamen; cada mensaje → 1 persona.
-- Ejecutar contra la misma BD donde está la tabla ticket (__SPARTA_SECRET_REDACTED__).
--
-- Desde MySQL: SOURCE ruta/completa/backend/sql/sabueso_dictamen.sql;

CREATE TABLE IF NOT EXISTS dictamen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_persona INT NOT NULL COMMENT 'Persona que escribe el dictamen',
    mensaje VARCHAR(2000) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_dictamen_ticket (id_ticket),
    KEY idx_dictamen_persona (id_persona),
    KEY idx_dictamen_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
