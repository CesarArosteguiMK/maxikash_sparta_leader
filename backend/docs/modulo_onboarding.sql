-- Módulo Onboarding (id 44) para permisos de nuevos colaboradores que solo ven menú Onboarding.
-- Ejecutar en la misma base donde está modulos_web (normalmente __SPARTA_SECRET_REDACTED__).
-- Coincide con la estructura: id, pestana, nombre, descripcion, activo.

INSERT INTO modulos_web (id, pestana, nombre, descripcion, activo)
VALUES (44, 'Onboarding', 'Curso Onboarding', 'Onboarding > Curso Onboarding', 1)
ON DUPLICATE KEY UPDATE pestana = 'Onboarding', nombre = 'Curso Onboarding', descripcion = 'Onboarding > Curso Onboarding', activo = 1;
