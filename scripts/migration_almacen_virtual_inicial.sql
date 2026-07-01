CREATE TABLE IF NOT EXISTS av_ubicaciones (
  id_ubicacion INT UNSIGNED NOT NULL AUTO_INCREMENT,
  clave_ubicacion VARCHAR(50) NOT NULL,
  nombre_ubicacion VARCHAR(150) NOT NULL,
  tipo_ubicacion ENUM('almacen','agencia','cedis','taller','piso_venta','patio','otro') NOT NULL DEFAULT 'almacen',
  estado VARCHAR(100) NULL,
  municipio VARCHAR(120) NULL,
  direccion VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_ubicacion),
  UNIQUE KEY ux_av_ubicaciones_clave (clave_ubicacion),
  KEY idx_av_ubicaciones_tipo_activo (tipo_ubicacion, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS av_unidades (
  id_unidad BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  folio_unidad VARCHAR(32) NOT NULL,
  id_celula TINYINT UNSIGNED NOT NULL,
  id_origen BIGINT UNSIGNED NULL,
  id_credito BIGINT NULL,
  vin VARCHAR(17) NULL,
  no_motor VARCHAR(24) NULL,
  placas VARCHAR(20) NULL,
  marca VARCHAR(100) NULL,
  modelo VARCHAR(100) NULL,
  anio SMALLINT UNSIGNED NULL,
  color VARCHAR(50) NULL,
  kilometraje INT UNSIGNED NULL,
  tipo_unidad VARCHAR(50) NULL,
  categoria VARCHAR(50) NULL,
  cilindraje VARCHAR(50) NULL,
  estatus_inventario VARCHAR(50) NOT NULL DEFAULT 'pendiente_recepcion',
  id_ubicacion_actual INT UNSIGNED NULL,
  fecha_ingreso_virtual DATETIME NULL,
  creado_por INT NULL,
  actualizado_por INT NULL,
  fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id_unidad),
  UNIQUE KEY ux_av_unidades_folio (folio_unidad),
  UNIQUE KEY ux_av_unidades_origen (id_celula, id_origen),
  KEY idx_av_unidades_celula_estatus (id_celula, estatus_inventario),
  KEY idx_av_unidades_credito (id_credito),
  KEY idx_av_unidades_vin (vin),
  KEY idx_av_unidades_motor (no_motor),
  KEY idx_av_unidades_placas (placas),
  KEY idx_av_unidades_ubicacion (id_ubicacion_actual),
  CONSTRAINT fk_av_unidades_ubicacion_actual
    FOREIGN KEY (id_ubicacion_actual) REFERENCES av_ubicaciones(id_ubicacion)
    ON DELETE SET NULL,
  CONSTRAINT chk_av_unidades_id_celula CHECK (id_celula IN (1, 2))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS av_movimientos (
  id_movimiento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_unidad BIGINT UNSIGNED NOT NULL,
  tipo_movimiento VARCHAR(50) NOT NULL,
  estatus_anterior VARCHAR(50) NULL,
  estatus_nuevo VARCHAR(50) NULL,
  id_ubicacion_origen INT UNSIGNED NULL,
  id_ubicacion_destino INT UNSIGNED NULL,
  comentario TEXT NULL,
  id_usuario INT NULL,
  nombre_usuario VARCHAR(150) NULL,
  fecha_movimiento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_movimiento),
  KEY idx_av_movimientos_unidad_fecha (id_unidad, fecha_movimiento),
  KEY idx_av_movimientos_tipo_fecha (tipo_movimiento, fecha_movimiento),
  CONSTRAINT fk_av_movimientos_unidad
    FOREIGN KEY (id_unidad) REFERENCES av_unidades(id_unidad)
    ON DELETE RESTRICT,
  CONSTRAINT fk_av_movimientos_ubicacion_origen
    FOREIGN KEY (id_ubicacion_origen) REFERENCES av_ubicaciones(id_ubicacion)
    ON DELETE SET NULL,
  CONSTRAINT fk_av_movimientos_ubicacion_destino
    FOREIGN KEY (id_ubicacion_destino) REFERENCES av_ubicaciones(id_ubicacion)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS av_evidencias (
  id_evidencia BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_unidad BIGINT UNSIGNED NOT NULL,
  etapa VARCHAR(50) NOT NULL,
  tipo_evidencia ENUM('foto','video','documento','firma','otro') NOT NULL DEFAULT 'foto',
  slot VARCHAR(80) NOT NULL,
  titulo VARCHAR(150) NULL,
  url TEXT NOT NULL,
  mime_type VARCHAR(100) NULL,
  tamano_bytes BIGINT UNSIGNED NULL,
  estatus ENUM('pendiente','recibido','validado','rechazado','reemplazado','eliminado') NOT NULL DEFAULT 'recibido',
  id_usuario_alta INT NULL,
  nombre_usuario_alta VARCHAR(150) NULL,
  fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evidencia),
  KEY idx_av_evidencias_unidad_etapa (id_unidad, etapa),
  KEY idx_av_evidencias_slot (slot),
  KEY idx_av_evidencias_estatus (estatus),
  CONSTRAINT fk_av_evidencias_unidad
    FOREIGN KEY (id_unidad) REFERENCES av_unidades(id_unidad)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS av_bitacora (
  id_bitacora BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_unidad BIGINT UNSIGNED NOT NULL,
  modulo VARCHAR(60) NULL,
  accion VARCHAR(120) NOT NULL,
  detalle TEXT NULL,
  payload_json LONGTEXT NULL,
  id_usuario INT NULL,
  nombre_usuario VARCHAR(150) NULL,
  fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_bitacora),
  KEY idx_av_bitacora_unidad_fecha (id_unidad, fecha_alta),
  CONSTRAINT fk_av_bitacora_unidad
    FOREIGN KEY (id_unidad) REFERENCES av_unidades(id_unidad)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS av_codigos_verificacion (
  id_codigo BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_unidad BIGINT UNSIGNED NOT NULL,
  tipo_codigo ENUM('ingreso_almacen','traspaso','reenvio','excepcion') NOT NULL DEFAULT 'ingreso_almacen',
  codigo VARCHAR(32) NOT NULL,
  estatus ENUM('generado','usado','vencido','cancelado') NOT NULL DEFAULT 'generado',
  generado_por INT NULL,
  usado_por INT NULL,
  fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion DATETIME NULL,
  fecha_uso DATETIME NULL,
  intentos SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  observaciones TEXT NULL,
  PRIMARY KEY (id_codigo),
  UNIQUE KEY ux_av_codigos_codigo (codigo),
  KEY idx_av_codigos_unidad (id_unidad),
  KEY idx_av_codigos_estatus (estatus),
  CONSTRAINT fk_av_codigos_unidad
    FOREIGN KEY (id_unidad) REFERENCES av_unidades(id_unidad)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO av_ubicaciones (clave_ubicacion, nombre_ubicacion, tipo_ubicacion)
VALUES ('SIN_ASIGNAR', 'Sin asignar', 'otro')
ON DUPLICATE KEY UPDATE nombre_ubicacion = VALUES(nombre_ubicacion);

INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES (139, 'Almacen Virtual', 'Motos Adjudicadas', 'Motos Adjudicadas > Almacen Virtual', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  pestana = VALUES(pestana),
  descripcion = VALUES(descripcion),
  activo = VALUES(activo);
