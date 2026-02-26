-- ============================================================
-- Migración: Adaptar tabla dictamen al nuevo flujo (Sabueso)
-- Fecha: 2025-02-25
-- ============================================================
-- Antes: dictamen guardaba solo mensajes tipo chat (id_ticket, id_persona, mensaje, fecha_creacion).
-- Ahora: dictamen tiene tipo (combo), descripción, estado (borrador/enviado), y evidencia vinculada.
-- La columna mensaje se elimina; se usa solo descripcion.
--
-- Ejecutar en MySQL/MariaDB. Si alguna columna ya existe, comentar la línea correspondiente.
-- ============================================================

-- Nuevas columnas en dictamen
ALTER TABLE dictamen
    ADD COLUMN tipo VARCHAR(50) NULL COMMENT 'localizado | no_localizado | promesa_pago | otro' AFTER id_persona,
    ADD COLUMN descripcion TEXT NULL COMMENT 'Descripción obligatoria del dictamen' AFTER tipo,
    ADD COLUMN estado VARCHAR(30) NOT NULL DEFAULT 'borrador' COMMENT 'borrador | enviado_al_gestor' AFTER descripcion,
    ADD COLUMN fecha_actualizacion DATETIME NULL COMMENT 'Última actualización (borrador o envío)' AFTER fecha_creacion,
    ADD COLUMN fecha_visto_gestor DATETIME NULL COMMENT 'Cuándo el gestor vio el dictamen (para tooltip en fila)' AFTER fecha_actualizacion;

-- Eliminar columna mensaje (ya no se usa; el texto va en descripcion).
-- Si la columna no existe, omitir o comentar la siguiente línea:
ALTER TABLE dictamen DROP COLUMN mensaje;

-- Opcional: vincular evidencias de ticket a un dictamen concreto.
-- Descomentar si quieres que las fotos del formulario de dictamen se guarden con id_dictamen:
--
-- ALTER TABLE ticket_evidencia
--     ADD COLUMN id_dictamen INT UNSIGNED NULL DEFAULT NULL COMMENT 'FK a dictamen.id cuando la evidencia es de un dictamen' AFTER id_ticket;
--
-- CREATE INDEX idx_ticket_evidencia_id_dictamen ON ticket_evidencia (id_dictamen);
