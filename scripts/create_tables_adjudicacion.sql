-- =============================================================================
-- TABLAS PARA EL MÓDULO DE ADJUDICACIÓN
-- Base de datos: __SPARTA_SECRET_REDACTED__
-- Fecha:         2026-04-22
-- =============================================================================
-- Ejecutar en este orden:
--   1. personal_adjudicacion
--   2. asigna_creditos_adjudicacion
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. personal_adjudicacion
--    Equivalente a la tabla `despachos` pero SIN tipo_persona y SIN id_celula.
--    Registra a las personas responsables de adjudicación.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `personal_adjudicacion` (
    `id`          INT            NOT NULL AUTO_INCREMENT,
    `id_persona`  INT            NOT NULL COMMENT 'FK → persona.id',
    `estatus`     VARCHAR(20)    NOT NULL DEFAULT 'Activo'  COMMENT 'Activo / Inactivo',
    `fecha_alta`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `numero_tel1` VARCHAR(20)    DEFAULT NULL,
    `numero_tel2` VARCHAR(20)    DEFAULT NULL,
    `correo_1`    VARCHAR(100)   DEFAULT NULL,
    `correo_2`    VARCHAR(100)   DEFAULT NULL,
    `direccion`   TEXT           DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_personal_adj_persona` (`id_persona`),
    CONSTRAINT `fk_personal_adj_persona`
        FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Responsables de adjudicación — equivalente a despachos sin tipo_persona ni id_celula';


-- -----------------------------------------------------------------------------
-- 2. asigna_creditos_adjudicacion
--    Equivalente a `asigna_creditos_despacho` pero referenciando
--    personal_adjudicacion y SIN celula / id_celula.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `asigna_creditos_adjudicacion` (
    `id`                  INT         NOT NULL AUTO_INCREMENT,
    `id_personal_adj`     INT         NOT NULL COMMENT 'FK → personal_adjudicacion.id',
    `id_credito`          INT         NOT NULL COMMENT 'ID del crédito asignado',
    `fecha_alta`          DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT 'Fecha de asignación',
    `alta`                INT         DEFAULT NULL  COMMENT 'FK → persona.id (quién asignó)',
    `estatus`             CHAR(1)     NOT NULL DEFAULT '1' COMMENT '1 = activo, 0 = inactivo',
    `fecha_baja`          DATETIME    DEFAULT NULL  COMMENT 'Fecha de desasignación',
    `baja`                INT         DEFAULT NULL  COMMENT 'FK → persona.id (quién desasignó)',
    PRIMARY KEY (`id`),
    KEY `idx_aca_id_personal_adj` (`id_personal_adj`),
    KEY `idx_aca_id_credito`      (`id_credito`),
    KEY `idx_aca_estatus`         (`estatus`),
    CONSTRAINT `fk_aca_personal_adj`
        FOREIGN KEY (`id_personal_adj`) REFERENCES `personal_adjudicacion` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_aca_alta_persona`
        FOREIGN KEY (`alta`) REFERENCES `persona` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_aca_baja_persona`
        FOREIGN KEY (`baja`) REFERENCES `persona` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Asignación de créditos para adjudicación — equivalente a asigna_creditos_despacho sin celula/id_celula';
