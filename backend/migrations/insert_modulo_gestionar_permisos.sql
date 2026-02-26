-- ============================================================
-- Módulo especial: Gestionar permisos (solo quien lo tenga verá el botón Permisos en Gestión)
-- ============================================================

INSERT IGNORE INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES (43, 'Gestionar permisos', 'Capital Humano', 'Ver y editar permisos de otros usuarios (botón Permisos en Gestión)', 1);
