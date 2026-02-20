-- Tabla reingresos: registro de reingreso de personal que estaba de baja.
-- Relación con persona (id_persona). Fecha en zona CDMX se maneja desde la app.
-- Los PDF se guardan en carga_documento_persona con id_documento = 16 (Documento Reingreso).

CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.reingresos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    fecha_reingreso DATETIME NOT NULL COMMENT 'Fecha y hora de reingreso (CDMX)',
    motivo_reingreso VARCHAR(255) NOT NULL COMMENT 'Motivo del reingreso (catálogo)',
    descripcion_reingreso TEXT NULL COMMENT 'Descripción adicional del reingreso',
    usuario_reingreso VARCHAR(150) NOT NULL COMMENT 'Usuario que registra el reingreso',
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reingresos_persona (id_persona),
    INDEX idx_reingresos_fecha (fecha_reingreso),
    CONSTRAINT fk_reingresos_persona FOREIGN KEY (id_persona) REFERENCES __SPARTA_SECRET_REDACTED__.persona(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nota: Si existe catálogo de tipos de documento (ej. tabla que define id 15 = Baja),
-- agregar registro para id 16 = Documento Reingreso. Si no existe, carga_documento_persona
-- usa id_documento numérico; usar 16 para reingresos y crear carpeta uploads/reingresos.
