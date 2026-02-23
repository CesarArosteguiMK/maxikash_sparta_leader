-- =============================================
-- MIGRACIÓN: Tabla paises + relaciones
-- Ejecutar en la base de datos __SPARTA_SECRET_REDACTED__
-- =============================================

USE __SPARTA_SECRET_REDACTED__;

-- 1. Crear tabla paises
CREATE TABLE IF NOT EXISTS paises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo_iso CHAR(2) NOT NULL COMMENT 'Código ISO 3166-1 alpha-2 para banderas (mx, gt, co)',
    activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar los 3 países iniciales
INSERT INTO paises (nombre, codigo_iso, activo) VALUES
    ('México',    'mx', 1),
    ('Guatemala', 'gt', 1),
    ('Colombia',  'co', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- 3. Agregar columna id_pais a persona (FK a paises)
ALTER TABLE persona
    ADD COLUMN id_pais INT NULL DEFAULT 1 COMMENT 'País sede de la persona',
    ADD CONSTRAINT fk_persona_pais FOREIGN KEY (id_pais) REFERENCES paises(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- 4. Agregar columna id_pais a departamento (para agrupar por país)
ALTER TABLE departamento
    ADD COLUMN id_pais INT NULL DEFAULT 1 COMMENT 'País al que pertenece el departamento',
    ADD CONSTRAINT fk_departamento_pais FOREIGN KEY (id_pais) REFERENCES paises(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- 5. Módulo de permisos para Países (Configuración)
INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES (41, 'Países', 'Configuración', 'Permite gestionar los países donde opera la empresa (agregar, activar/desactivar)', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  pestana = VALUES(pestana),
  descripcion = VALUES(descripcion),
  activo = VALUES(activo);

-- 6. Asignar módulo 41 a los usuarios que ya tienen acceso a Departamentos (módulo 10)
--    para que vean "Países" en Configuración
INSERT IGNORE INTO asigna_modulo_web (usuario_id, modulo_web_id)
SELECT usuario_id, 41
FROM asigna_modulo_web
WHERE modulo_web_id = 10;
