-- Bitácora (chat) por ticket: quién dejó el mensaje (id_persona).
-- Evidencias por ticket: imágenes subidas por persona, con comentario opcional.
-- Relaciones: 1 ticket → muchas evidencias; 1 ticket → muchos mensajes chat; cada mensaje/evidencia → 1 persona.
-- Ejecutar contra la misma BD donde está la tabla ticket (__SPARTA_SECRET_REDACTED__).
--
-- Desde MySQL: SOURCE ruta/completa/backend/sql/sabueso_chat_evidencias.sql;

-- Tabla chat: mensajes de la bitácora por ticket (relación ticket → muchos mensajes; mensaje → persona)
CREATE TABLE IF NOT EXISTS chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_persona INT NOT NULL COMMENT 'Persona que escribe el mensaje',
    mensaje VARCHAR(2000) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_chat_ticket (id_ticket),
    KEY idx_chat_persona (id_persona),
    KEY idx_chat_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla ticket_evidencia: imágenes de evidencia por ticket (relación ticket → muchas evidencias; evidencia → persona)
CREATE TABLE IF NOT EXISTS ticket_evidencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_persona INT NOT NULL COMMENT 'Persona que subió la evidencia',
    ruta_archivo VARCHAR(500) NOT NULL COMMENT 'Ruta relativa en uploads',
    nombre_original VARCHAR(255) NOT NULL,
    comentario VARCHAR(500) NULL DEFAULT NULL COMMENT 'Comentario opcional al subir la foto',
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_evidencia_ticket (id_ticket),
    KEY idx_evidencia_persona (id_persona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Si la tabla ticket_evidencia ya existía sin comentario, agregar la columna:
-- ALTER TABLE ticket_evidencia ADD COLUMN comentario VARCHAR(500) NULL DEFAULT NULL COMMENT 'Comentario opcional al subir la foto' AFTER nombre_original;
