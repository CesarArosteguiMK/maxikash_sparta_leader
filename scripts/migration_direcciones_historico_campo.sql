CREATE TABLE IF NOT EXISTS direcciones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_credito BIGINT UNSIGNED NOT NULL,

  orden_direccion SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  tipo_direccion ENUM('principal','secundaria','terciaria','adicional') NOT NULL DEFAULT 'principal',
  es_principal TINYINT(1) NOT NULL DEFAULT 0,

  codigo_postal VARCHAR(20) NULL,
  calle_numero VARCHAR(255) NULL,
  direccion VARCHAR(500) NULL,
  colonia VARCHAR(255) NULL,
  ciudad VARCHAR(255) NULL,
  estado VARCHAR(255) NULL,

  telefono_celular VARCHAR(40) NULL,
  referencia_1 VARCHAR(255) NULL,
  parentesco_referencia_1 VARCHAR(120) NULL,
  telefono_referencia_1 VARCHAR(40) NULL,
  referencia_2 VARCHAR(255) NULL,
  parentesco_referencia_2 VARCHAR(120) NULL,
  telefono_referencia_2 VARCHAR(40) NULL,
  etapa VARCHAR(120) NULL,

  origen VARCHAR(80) NOT NULL DEFAULT 'historico_direcciones_campo',
  origen_detalle VARCHAR(80) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_direcciones_credito_orden (id_credito, orden_direccion),
  KEY idx_direcciones_credito_principal (id_credito, es_principal, activo),
  KEY idx_direcciones_cp (codigo_postal),
  KEY idx_direcciones_estado_ciudad (estado, ciudad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
