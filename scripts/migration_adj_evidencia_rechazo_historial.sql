CREATE TABLE IF NOT EXISTS adj_evidencia_rechazo_historial (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_operacion INT UNSIGNED NOT NULL,
    id_evidencia INT UNSIGNED NOT NULL,
    slot VARCHAR(50) NOT NULL,
    url_vieja_rechazada VARCHAR(500) NOT NULL,
    url_nueva VARCHAR(500) DEFAULT NULL,
    fecha_rechazo DATETIME NOT NULL,
    id_usuario_rechazo INT DEFAULT NULL,
    fecha_url_nueva DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_adj_ev_rechazo_operacion (id_operacion),
    KEY idx_adj_ev_rechazo_evidencia (id_evidencia),
    KEY idx_adj_ev_rechazo_slot (slot),
    KEY idx_adj_ev_rechazo_fecha (fecha_rechazo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
