-- Módulo "Permisos especiales" > "Permiso para registrar documentos"
-- Asignación/desasignación se hace en asigna_modulo_web (igual que el resto de módulos).
-- ID 21: usado en sesión para comprobar si el usuario puede ver el botón "Registrar" en el modal "Documento no registrado".
INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES (21, 'Permiso para registrar documentos', 'Permisos especiales', 'Permite registrar documentos en estado de cuenta desde el modal de documentación', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  pestana = VALUES(pestana),
  descripcion = VALUES(descripcion),
  activo = VALUES(activo);
