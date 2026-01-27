CREATE TABLE __SPARTA_SECRET_REDACTED__.legiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    activo TINYINT DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO __SPARTA_SECRET_REDACTED__.legiones (id, nombre, descripcion, activo) VALUES
(1, 'Sabueso', 'Legión Sabueso', 1),
(2, 'Heraldo', 'Legión Heraldo', 1),
(3, 'Centinela', 'Legión Centinela', 1),
(4, 'Senturiones', 'Legión Senturiones', 1),
(5, 'Espartano', 'Legión Espartano', 1);

CREATE TABLE __SPARTA_SECRET_REDACTED__.asigna_legion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    id_legion INT NOT NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,
    activo TINYINT DEFAULT 1,
    usuario_asignacion VARCHAR(100) NULL,
    FOREIGN KEY (id_persona) REFERENCES __SPARTA_SECRET_REDACTED__.persona(id) ON DELETE CASCADE,
    FOREIGN KEY (id_legion) REFERENCES __SPARTA_SECRET_REDACTED__.legiones(id) ON DELETE RESTRICT,
    INDEX idx_persona (id_persona),
    INDEX idx_legion (id_legion),
    INDEX idx_activo (activo),
    INDEX idx_persona_activo (id_persona, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TRIGGER IF EXISTS trg_una_legion_activa;

CREATE TRIGGER trg_una_legion_activa
BEFORE INSERT ON __SPARTA_SECRET_REDACTED__.asigna_legion
FOR EACH ROW
BEGIN
    IF NEW.activo = 1 THEN
        UPDATE __SPARTA_SECRET_REDACTED__.asigna_legion
        SET activo = 0, fecha_fin = NOW()
        WHERE id_persona = NEW.id_persona
        AND activo = 1;
    END IF;
END;