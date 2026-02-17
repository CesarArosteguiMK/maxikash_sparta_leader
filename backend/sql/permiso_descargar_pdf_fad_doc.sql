-- Módulo "Permisos especiales" > Descargar documento PDF FAD_DOC
-- ID 24: permite ver el botón "Descargar" en la barra del visor PDF cuando el documento abierto es FAD_DOC y descargar el PDF completo.
INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES (24, '📥 Descargar PDF FAD_DOC', 'Permisos especiales', 'Permite descargar el documento PDF FAD_DOC completo. En Documentación, al abrir un FAD_DOC, quien tenga este permiso verá el botón de descarga (ícono descarga) en la barra del visor.', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  pestana = VALUES(pestana),
  descripcion = VALUES(descripcion),
  activo = VALUES(activo);
