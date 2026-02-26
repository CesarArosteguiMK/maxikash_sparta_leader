-- ============================================================
-- Agregar módulo Candidatos a modulos_web (Capital Humano)
-- Ejecutar en el esquema __SPARTA_SECRET_REDACTED__ (o el que use la app)
-- ============================================================
-- Columnas usadas en la app: id, nombre, pestana, descripcion, activo
-- ============================================================

INSERT IGNORE INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES (42, 'Candidatos', 'Capital Humano', 'Candidatos a empleados', 1);
