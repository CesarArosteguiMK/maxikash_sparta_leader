-- Reactivacion auditada de ofertas de convenio.
-- Ajusta IDs 145/146 si en tu ambiente ya estan ocupados.

CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.convenio_reactivacion_peticion (
    id INT NOT NULL AUTO_INCREMENT,
    id_credito INT NOT NULL,
    id_convenio_origen INT NOT NULL,
    id_producto_convenio INT NOT NULL,
    estatus ENUM('pendiente','aprobada','ejecutada','descartada','descartado') NOT NULL DEFAULT 'pendiente',
    motivo_solicitud VARCHAR(300) NULL,
    usuario_solicita VARCHAR(120) NOT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_resuelve VARCHAR(120) NULL,
    fecha_resolucion DATETIME NULL,
    comentario_resolucion VARCHAR(300) NULL,
    id_convenio_nuevo INT NULL,
    PRIMARY KEY (id),
    KEY idx_crp_credito_producto_estatus (id_credito, id_producto_convenio, estatus),
    KEY idx_crp_origen (id_convenio_origen),
    KEY idx_crp_nuevo (id_convenio_nuevo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE __SPARTA_SECRET_REDACTED__.convenio_reactivacion_peticion
    MODIFY COLUMN estatus ENUM('pendiente','aprobada','ejecutada','descartada','descartado') NOT NULL DEFAULT 'pendiente';

ALTER TABLE __SPARTA_SECRET_REDACTED__.convenio_cliente
    ADD COLUMN es_reactivado TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1 cuando el convenio se genero desde una oferta reactivada',
    ADD COLUMN id_convenio_origen INT NULL
        COMMENT 'Convenio anterior que origino la reactivacion',
    ADD COLUMN id_peticion_reactivacion INT NULL
        COMMENT 'Peticion de reactivacion consumida al crear este convenio',
    ADD COLUMN reactivacion_numero INT NULL
        COMMENT 'Numero secuencial de reactivacion para este credito/producto',
    ADD COLUMN motivo_reactivacion VARCHAR(300) NULL,
    ADD COLUMN usuario_reactiva VARCHAR(120) NULL,
    ADD COLUMN fecha_reactivacion DATETIME NULL,
    ADD INDEX idx_cc_reactivado (es_reactivado, id_credito),
    ADD INDEX idx_cc_peticion_reactivacion (id_peticion_reactivacion);

INSERT INTO __SPARTA_SECRET_REDACTED__.modulos_web (id, pestana, nombre, descripcion, activo)
VALUES
    (145, 'Permisos especiales', 'Solicitar reactivacion de oferta', 'Permite solicitar la reactivacion de ofertas de convenio desde Cierre de Credito.', 1),
    (146, 'Permisos especiales', 'Autorizar reactivacion de oferta', 'Permite autorizar/reactivar ofertas de convenio directamente desde Cierre de Credito.', 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    pestana = VALUES(pestana),
    descripcion = VALUES(descripcion),
    activo = VALUES(activo);
