-- ============================================================
-- Migración: Tabla de notificaciones (campana en navbar)
-- Fecha: 2025-02-25
-- ============================================================
-- Tipos: ticket_levantado, dictamen_enviado, dictamen_revisado
-- ============================================================

CREATE TABLE IF NOT EXISTS notificacion (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_persona INT UNSIGNED NOT NULL COMMENT 'Persona que recibe la notificación',
    tipo VARCHAR(40) NOT NULL COMMENT 'ticket_levantado | dictamen_enviado | dictamen_revisado',
    mensaje VARCHAR(500) NOT NULL COMMENT 'Texto a mostrar (ej. Ticket nuevo levantado por José García)',
    id_ticket INT UNSIGNED NULL,
    leida TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=no leída, 1=leída',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notif_persona_leida (id_persona, leida),
    INDEX idx_notif_fecha (fecha_creacion),
    INDEX idx_notif_ticket (id_ticket)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Notificaciones para la campana del navbar (Sabueso: tickets y dictámenes)';
