-- ═══════════════════════════════════════════════════════════════
-- Migración: Pestaña Cartera en Cierre de Crédito
-- ═══════════════════════════════════════════════════════════════

-- 1. Ampliar ENUM de estatus para incluir los nuevos valores de cartera
ALTER TABLE cierre_credito_seguimiento
    MODIFY COLUMN estatus ENUM(
        'en_proceso',
        'enviado_cartera',
        'descartado',
        'en_cola',
        'listo_envio',
        'notificado_cartera',
        'cerrado',
        'devuelto_cartera'
    ) NOT NULL DEFAULT 'en_proceso';

-- 2. Registrar el nuevo módulo de permisos especiales (id=59)
INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES
    (59, 'Cierre: Cartera', 'Cierre de crédito',
     'Pestaña Cartera en Cierre de Crédito — permite ver, cerrar y devolver convenios notificados por despachos',
     1)
ON DUPLICATE KEY UPDATE
    nombre      = VALUES(nombre),
    descripcion = VALUES(descripcion),
    activo      = VALUES(activo);

-- ═══════════════════════════════════════════════════════════════
-- NOTAS:
--  - El ALTER TABLE agrega 3 valores al ENUM sin afectar los existentes.
--  - Asignar modulos_web id=59 a los usuarios del equipo de Cartera
--    para que puedan acceder a la nueva pestaña.
-- ═══════════════════════════════════════════════════════════════
