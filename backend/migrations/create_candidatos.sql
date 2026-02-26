-- ============================================================
-- Migración: Tabla candidatos (Capital Humano)
-- Candidatos a empleados: misma forma que Gestión, con Agregar candidato
-- ============================================================

CREATE TABLE IF NOT EXISTS candidatos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    segundo_nombre VARCHAR(100) NULL,
    apellidop VARCHAR(100) NOT NULL,
    apellidom VARCHAR(100) NULL,
    email VARCHAR(150) NULL,
    telefono VARCHAR(20) NULL,
    id_puesto INT UNSIGNED NULL COMMENT 'Puesto de interés (FK puesto)',
    id_departamento INT UNSIGNED NULL COMMENT 'Área de interés (FK departamento)',
    estatus VARCHAR(50) NOT NULL DEFAULT 'Por evaluar' COMMENT 'Por evaluar | En entrevista | Contratado | Descartado',
    notas TEXT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_candidatos_estatus (estatus),
    INDEX idx_candidatos_fecha (fecha_registro),
    INDEX idx_candidatos_puesto (id_puesto),
    INDEX idx_candidatos_departamento (id_departamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Candidatos a empleados (Capital Humano)';
