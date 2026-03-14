-- Asegura que el módulo 24 "Descargar PDF FAD_DOC" aparezca en la pestaña "Permisos especiales"
-- al asignar permisos a un usuario (CapHum > Gestión > editar perfil).
-- Ejecutar en la base de datos del proyecto (ej. __SPARTA_SECRET_REDACTED__).

-- 1) Si el módulo 24 ya existe: actualizar pestana y activo para que salga en "Permisos especiales"
UPDATE modulos_web
SET pestana = 'Permisos especiales',
    activo = 1
WHERE id = 24;

-- 2) Si el módulo 24 NO existe, ejecutar este INSERT (ajustar columnas si tu tabla es distinta):
-- INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
-- VALUES (24, 'Descargar PDF FAD_DOC', 'Permisos especiales',
--         'Permite descargar el documento PDF FAD_DOC completo. En Documentación, al abrir un FAD_DOC, quien tenga este permiso verá el botón de descarga.', 1);
