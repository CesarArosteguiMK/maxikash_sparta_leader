CREATE TABLE IF NOT EXISTS leonidas_apariencia_usuario (
    persona_id INT NOT NULL,
    tema VARCHAR(32) NOT NULL DEFAULT 'corporativo',
    color_principal CHAR(7) NOT NULL,
    color_secundario CHAR(7) NOT NULL,
    color_metal CHAR(7) NOT NULL,
    casco_visible TINYINT(1) NOT NULL DEFAULT 1,
    pechera_visible TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (persona_id),
    KEY idx_leonidas_apariencia_tema (tema)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE leonidas_apariencia_usuario
    ADD COLUMN IF NOT EXISTS casco_visible TINYINT(1) NOT NULL DEFAULT 1 AFTER color_metal,
    ADD COLUMN IF NOT EXISTS pechera_visible TINYINT(1) NOT NULL DEFAULT 1 AFTER casco_visible;
