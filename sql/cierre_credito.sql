-- ══════════════════════════════════════════════════════════════
-- MÓDULO: Cierre de Crédito
-- Tabla: estatus_cierre_final
-- Descripción: Registra el estatus de cierre final de créditos.
--              Dos vistas operativas: "En Proceso" y "Enviado Finalizado".
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `estatus_cierre_final` (
    `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_credito`            INT UNSIGNED NOT NULL COMMENT 'ID del crédito en tbl_segundometro_semana',
    `nombre_cliente`        VARCHAR(255)  NOT NULL DEFAULT '',
    `estatus`               ENUM('en_proceso', 'enviado_finalizado') NOT NULL DEFAULT 'en_proceso'
                                COMMENT 'en_proceso = pestaña En Proceso | enviado_finalizado = pestaña Enviado Finalizado',
    `fecha_alta`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_alta`          VARCHAR(120) NOT NULL DEFAULT '' COMMENT 'Usuario Sparta que creó el registro',
    `fecha_actualizacion`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `usuario_actualizacion` VARCHAR(120) NOT NULL DEFAULT '' COMMENT 'Último usuario que modificó el registro',
    PRIMARY KEY (`id`),
    INDEX `idx_id_credito` (`id_credito`),
    INDEX `idx_estatus`    (`estatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Módulo Cierre de Crédito — estatus de cierre final por crédito';
