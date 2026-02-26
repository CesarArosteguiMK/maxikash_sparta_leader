-- ============================================================
-- Migración: Crear tabla ticket_evidencia (evidencias/fotos del ticket)
-- Fecha: 2025-02-25
-- ============================================================
-- Usada por: Sabueso panel admin (dictamen), subirEvidenciaTicket, getDictamenDetalle (gestor).
-- Si la tabla ya existe, ejecutar solo los ALTER que falten.
-- ============================================================

CREATE TABLE IF NOT EXISTS ticket_evidencia (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT UNSIGNED NOT NULL COMMENT 'FK al ticket',
    id_persona INT UNSIGNED NOT NULL COMMENT 'Quien subió la evidencia',
    ruta_archivo VARCHAR(255) NOT NULL COMMENT 'Ruta relativa ej: sabueso_evidencias/ev_1_2_xxx.jpg',
    nombre_original VARCHAR(255) NULL COMMENT 'Nombre del archivo original',
    fecha_subida DATETIME NOT NULL,
    INDEX idx_ticket_evidencia_id_ticket (id_ticket),
    INDEX idx_ticket_evidencia_id_persona (id_persona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Evidencias/fotos asociadas a tickets (dictamen, rastreo)';

-- Opcional: si más adelante se quiere vincular evidencia a un dictamen concreto:
-- ALTER TABLE ticket_evidencia ADD COLUMN id_dictamen INT UNSIGNED NULL DEFAULT NULL AFTER id_ticket;
-- CREATE INDEX idx_ticket_evidencia_id_dictamen ON ticket_evidencia (id_dictamen);
