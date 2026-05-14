-- =============================================================================
-- Migración: Módulo de Tracking y Planeación de Rutas para Recolección de Motos Adjudicadas
-- Esquema destino : __SPARTA_SECRET_REDACTED__
-- Fecha           : 2025
-- =============================================================================

-- ─────────────────────────────────────────────────────────────────────────────
-- Tabla principal de rutas de recolección
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `asigna_horas_tracking` (
    `id_ruta`             INT           NOT NULL AUTO_INCREMENT,
    `nombre_ruta`         VARCHAR(100)  NOT NULL,
    `estado`              VARCHAR(100)  NULL,
    `municipio`           VARCHAR(100)  NULL,
    `fecha_programada`    DATE          NOT NULL   COMMENT 'Mínimo: hoy + 2 días',
    `estatus_ruta`        ENUM(
                              'borrador',
                              'pendiente_confirmacion',
                              'lista_envio',
                              'enviada',
                              'en_proceso',
                              'concluida',
                              'cancelada'
                          )             NOT NULL DEFAULT 'borrador',
    `creado_por`          INT           NULL       COMMENT 'id de persona que creó la ruta',
    `fecha_creacion`      DATETIME      NULL,
    `fecha_actualizacion` DATETIME      NULL,
    PRIMARY KEY (`id_ruta`),
    KEY `idx_tracking_estatus`    (`estatus_ruta`),
    KEY `idx_tracking_fecha`      (`fecha_programada`),
    KEY `idx_tracking_estado_mun` (`estado`, `municipio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Rutas de recolección de motos adjudicadas';

-- ─────────────────────────────────────────────────────────────────────────────
-- Detalle: créditos asignados a cada ruta
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `asigna_horas_tracking_detalle` (
    `id_detalle`                  INT           NOT NULL AUTO_INCREMENT,
    `id_ruta`                     INT           NOT NULL,
    `id_credito`                  INT           NULL,
    `modelo`                      VARCHAR(100)  NULL,
    `bin`                         VARCHAR(100)  NULL COMMENT 'BIN / NIV / VIN de la moto',
    `estado`                      VARCHAR(100)  NULL,
    `municipio`                   VARCHAR(100)  NULL,
    `direccion`                   VARCHAR(200)  NULL,
    `latitud`                     DECIMAL(10,7) NULL,
    `longitud`                    DECIMAL(10,7) NULL,
    `orden_ruta`                  INT           NULL DEFAULT 0,
    `estatus_confirmacion_gestor` ENUM(
                                      'pendiente',
                                      'confirmado',
                                      'rechazado',
                                      'en_revision'
                                  )             NOT NULL DEFAULT 'pendiente',
    `estatus_recoleccion`         VARCHAR(50)   NULL,
    PRIMARY KEY (`id_detalle`),
    CONSTRAINT `fk_tracking_det_ruta`
        FOREIGN KEY (`id_ruta`) REFERENCES `asigna_horas_tracking` (`id_ruta`) ON DELETE CASCADE,
    KEY `idx_tracking_det_credito` (`id_credito`),
    KEY `idx_tracking_det_orden`   (`id_ruta`, `orden_ruta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Créditos (motos) asignados a cada ruta de recolección';

-- ─────────────────────────────────────────────────────────────────────────────
-- Usuarios responsables de cada ruta (N:M — multi-select)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `asigna_horas_tracking_usuarios` (
    `id`          INT NOT NULL AUTO_INCREMENT,
    `id_ruta`     INT NOT NULL,
    `id_usuario`  INT NOT NULL COMMENT 'id de persona asignada como recolector',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_tracking_usr_ruta`
        FOREIGN KEY (`id_ruta`) REFERENCES `asigna_horas_tracking` (`id_ruta`) ON DELETE CASCADE,
    UNIQUE KEY `ux_tracking_usr` (`id_ruta`, `id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Usuarios responsables de recolección por ruta';
