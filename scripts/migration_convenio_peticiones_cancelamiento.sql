-- ════════════════════════════════════════════════════════════
-- MIGRACIÓN: Módulo Peticiones de Cancelamiento de Convenios
-- Agrega dos columnas a convenio_cliente:
--   motivo_cancelamiento          – razón capturada por el gestor al solicitar cancelación
--   solicitud_cancelamiento_fecha – timestamp en que se registró la solicitud
-- Con estas columnas se diferencia entre:
--   estatus = 'activo' + solicitud_cancelamiento_fecha IS NULL  → convenio normal
--   estatus = 'activo' + solicitud_cancelamiento_fecha IS NOT NULL → pendiente de autorizar
--   estatus = 'cancelado'                                         → ya autorizado/cancelado
-- ════════════════════════════════════════════════════════════

ALTER TABLE __SPARTA_SECRET_REDACTED__.convenio_cliente
    ADD COLUMN `motivo_cancelamiento` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
        COMMENT 'Motivo capturado por el gestor al solicitar la cancelación del convenio'
        AFTER `usuario_cancela`,
    ADD COLUMN `solicitud_cancelamiento_fecha` datetime DEFAULT NULL
        COMMENT 'Fecha en que el gestor envió la solicitud de cancelación (pendiente de autorizar)'
        AFTER `motivo_cancelamiento`;

-- Índice para consultar rápidamente las peticiones pendientes
ALTER TABLE __SPARTA_SECRET_REDACTED__.convenio_cliente
    ADD INDEX `idx_cc_solicitud_cancelamiento` (`solicitud_cancelamiento_fecha`);

-- Módulo web 60: Peticiones de Cancelamiento (pestaña en Cierre de Crédito)
-- NOTA: registrar en la tabla modulos_web con id=60, nombre="Peticiones Cancelamiento Convenios"
-- INSERT INTO modulos_web (id, nombre, descripcion) VALUES (60, 'Peticiones Cancelamiento Convenios', 'Autorizar o denegar solicitudes de cancelación de convenios');
