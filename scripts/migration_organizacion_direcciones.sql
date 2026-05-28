CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.direcciones_organizacion (
  id INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  id_pais INT NOT NULL DEFAULT 1,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY ux_direcciones_pais_nombre (id_pais, nombre),
  KEY idx_direcciones_pais_activo (id_pais, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.asigna_direcciones (
  id INT NOT NULL AUTO_INCREMENT,
  id_direccion INT NOT NULL,
  id_departamento_organizacional INT NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY ux_asigna_direccion_area (id_departamento_organizacional),
  KEY idx_asigna_direcciones_direccion (id_direccion),
  KEY idx_asigna_direcciones_area_activo (id_departamento_organizacional, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
