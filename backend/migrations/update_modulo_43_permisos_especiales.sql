-- ============================================================
-- Mover "Gestionar permisos" (id 43) a la pestaña Permisos especiales
-- para que aparezca en el modal y se pueda asignar desde ahí.
-- ============================================================

UPDATE modulos_web
SET pestana = 'Permisos especiales',
    descripcion = 'Permite ver el botón Permisos en Gestión y Bajas y editar los permisos de otros usuarios.'
WHERE id = 43;
