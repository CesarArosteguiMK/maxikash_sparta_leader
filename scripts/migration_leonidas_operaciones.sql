-- Infraestructura durable para ejecutores operativos de Leonidas.
-- No almacena prompts completos, contraseñas, NIP ni tokens de adjuntos.

CREATE TABLE IF NOT EXISTS leonidas_operaciones (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    idempotency_key CHAR(64) NOT NULL,
    accion VARCHAR(120) NOT NULL,
    actor_id INT NOT NULL,
    payload_hash CHAR(64) NOT NULL,
    estado VARCHAR(30) NOT NULL,
    comprobante_json LONGTEXT NULL,
    error_resumen VARCHAR(500) NULL,
    creado_en DATETIME NOT NULL,
    terminado_en DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leonidas_operacion_idempotencia (idempotency_key),
    KEY idx_leonidas_operacion_actor (actor_id, creado_en),
    KEY idx_leonidas_operacion_accion (accion, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS leonidas_autorizaciones (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    accion VARCHAR(120) NOT NULL,
    payload_json LONGTEXT NOT NULL,
    payload_hash CHAR(64) NOT NULL,
    resumen VARCHAR(500) NOT NULL,
    primer_actor_id INT NOT NULL,
    segundo_actor_id INT NULL,
    estado VARCHAR(30) NOT NULL,
    comprobante_json LONGTEXT NULL,
    creado_en DATETIME NOT NULL,
    expira_en DATETIME NOT NULL,
    autorizado_en DATETIME NULL,
    terminado_en DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leonidas_autorizacion_codigo (codigo),
    KEY idx_leonidas_autorizacion_pendiente (estado, expira_en),
    KEY idx_leonidas_autorizacion_hash (payload_hash, primer_actor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS leonidas_viaticos_flujo (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_viatico INT NOT NULL,
    solicitante_id INT NOT NULL,
    monto DECIMAL(14,2) NOT NULL,
    moneda CHAR(3) NOT NULL DEFAULT 'MXN',
    estatus VARCHAR(40) NOT NULL,
    comprobante_hash CHAR(64) NULL,
    autorizado_por INT NULL,
    autorizado_en DATETIME NULL,
    motivo_rechazo VARCHAR(500) NULL,
    pagado_por INT NULL,
    pagado_en DATETIME NULL,
    referencia_pago VARCHAR(160) NULL,
    enviado_en DATETIME NULL,
    creado_en DATETIME NOT NULL,
    actualizado_en DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leonidas_viatico (id_viatico),
    KEY idx_leonidas_viatico_estado (estatus, actualizado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS leonidas_condonaciones_flujo (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    payload_json LONGTEXT NOT NULL,
    id_credito INT NOT NULL,
    monto DECIMAL(14,2) NOT NULL,
    creador_id INT NOT NULL,
    aprobador_id INT NULL,
    id_condonacion INT NULL,
    estatus VARCHAR(40) NOT NULL,
    motivo_rechazo VARCHAR(500) NULL,
    creado_en DATETIME NOT NULL,
    enviado_en DATETIME NULL,
    aprobado_en DATETIME NULL,
    actualizado_en DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leonidas_condonacion_codigo (codigo),
    KEY idx_leonidas_condonacion_estado (estatus, actualizado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO modulos_web (nombre, pestana, descripcion, activo)
SELECT 'Autorizar viáticos con Leonidas', 'Permisos especiales',
       'Permite otorgar una de las dos autorizaciones requeridas para viáticos.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM modulos_web WHERE LOWER(TRIM(nombre)) = LOWER('Autorizar viáticos con Leonidas')
);

INSERT INTO modulos_web (nombre, pestana, descripcion, activo)
SELECT 'Registrar pagos de viáticos con Leonidas', 'Permisos especiales',
       'Permite registrar la referencia de pago de un viático con doble autorización.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM modulos_web WHERE LOWER(TRIM(nombre)) = LOWER('Registrar pagos de viáticos con Leonidas')
);

INSERT INTO modulos_web (nombre, pestana, descripcion, activo)
SELECT 'Autorizar condonaciones con Leonidas', 'Permisos especiales',
       'Permite otorgar una de las dos autorizaciones requeridas para aplicar condonaciones.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM modulos_web WHERE LOWER(TRIM(nombre)) = LOWER('Autorizar condonaciones con Leonidas')
);
