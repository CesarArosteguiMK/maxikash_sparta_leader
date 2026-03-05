-- Token para confirmación de alta en nómina desde el correo (enlace Sí/No).
-- Ejecutar en la misma base donde está la tabla candidatos.

CREATE TABLE IF NOT EXISTS candidato_confirmacion_alta_token (
  token VARCHAR(64) NOT NULL PRIMARY KEY,
  id_candidato INT NOT NULL,
  creado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expira DATETIME NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  respuesta ENUM('si','no') NULL COMMENT 'si=alta en gestión, no=solo registro',
  fecha_uso DATETIME NULL,
  INDEX idx_id_candidato (id_candidato),
  INDEX idx_expira (expira)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
