-- =====================================================================
-- Tabla: cierre_credito_seguimiento
-- Base de datos: __SPARTA_SECRET_REDACTED__  (Database principal)
-- Propósito: Registro operacional del workflow de Cierre de Crédito.
--   - Tab 1 (Enviados Finalizados): convenios 'completado' listos para cierre
--   - Tab 2 (En Proceso): gestor 2 valida documentos y envía email a cartera
-- =====================================================================

CREATE TABLE IF NOT EXISTS cierre_credito_seguimiento (
    id                    INT            NOT NULL AUTO_INCREMENT,
    id_credito            INT            NOT NULL,
    nombre_cliente        VARCHAR(255)   NOT NULL,
    estatus               ENUM('en_proceso','enviado_cartera')
                                         NOT NULL DEFAULT 'en_proceso',
    usuario_alta          VARCHAR(100)   NOT NULL,
    usuario_actualizacion VARCHAR(100)   NOT NULL,
    fecha_envio_cartera   DATETIME       NULL     DEFAULT NULL,
    email_destino_cartera VARCHAR(150)   NULL     DEFAULT NULL,
    fecha_alta            DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                  ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_id_credito (id_credito),
    INDEX idx_estatus    (estatus)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Workflow de cierre de crédito — validación y envío a cartera';
