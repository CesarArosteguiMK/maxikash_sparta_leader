-- ============================================================
-- Token único para que el candidato suba documentos por link (tipo Google Forms)
-- ============================================================
CREATE TABLE IF NOT EXISTS candidato_documento_token (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_candidato INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_token (token),
    KEY idx_id_candidato (id_candidato),
    CONSTRAINT fk_cdt_candidato FOREIGN KEY (id_candidato) REFERENCES candidatos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Token único por candidato para link de subida de documentos';

-- ============================================================
-- Documentos subidos por el candidato mediante el link
-- ============================================================
CREATE TABLE IF NOT EXISTS candidato_documento (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_candidato INT UNSIGNED NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(512) NOT NULL,
    fecha_carga DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_id_candidato (id_candidato),
    CONSTRAINT fk_cd_candidato FOREIGN KEY (id_candidato) REFERENCES candidatos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Documentos subidos por candidato vía link único';
