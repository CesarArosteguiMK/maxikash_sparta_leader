-- Tabla perfil: datos extendidos y foto de perfil por persona.
-- Vinculada a persona por id_persona (1:1).
-- La foto se guarda en servidor y aquí solo la ruta (ej. /assets/img/fotos_perfil/123.jpg).

CREATE TABLE IF NOT EXISTS perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL UNIQUE,
    nombre VARCHAR(100) NULL,
    apellidopat VARCHAR(100) NULL,
    apellidomat VARCHAR(100) NULL,
    telefono VARCHAR(50) NULL,
    correo VARCHAR(120) NULL,
    direccion VARCHAR(255) NULL,
    username VARCHAR(80) NULL,
    pass VARCHAR(255) NULL COMMENT 'Hash de contraseña si se usa para cambio desde perfil',
    foto VARCHAR(255) NULL COMMENT 'Ruta relativa ej. /assets/img/fotos_perfil/{id_persona}.jpg',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_id_persona (id_persona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
