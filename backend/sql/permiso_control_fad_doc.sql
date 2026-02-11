-- Módulo "Permisos especiales" > Descargar videos de FAD_DOC
-- ID 22: permite descargar los videos de firma del documento FAD_DOC desde el modal de Documentación.
INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES (22, 'Descargar videos de FAD_DOC', 'Permisos especiales', 'Permite descargar los videos de firma y confirmación del documento FAD_DOC. En Documentación, al abrir un FAD_DOC, quien tenga este permiso verá el botón Descargar en cada video; sin el permiso solo puede reproducir.', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  pestana = VALUES(pestana),
  descripcion = VALUES(descripcion),
  activo = VALUES(activo);
