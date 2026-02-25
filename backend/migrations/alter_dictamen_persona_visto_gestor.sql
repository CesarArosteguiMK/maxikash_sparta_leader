-- ============================================================
-- Migración: Registrar quién abrió el dictamen (gestor)
-- Fecha: 2025-02-25
-- ============================================================
-- Añade id_persona_visto_gestor para guardar quién abrió el dictamen
-- junto con fecha_visto_gestor (fecha y hora).
-- ============================================================

ALTER TABLE dictamen
    ADD COLUMN id_persona_visto_gestor INT UNSIGNED NULL
    COMMENT 'Persona (gestor) que abrió el dictamen'
    AFTER fecha_visto_gestor;
