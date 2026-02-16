-- Módulo "Permisos especiales" > Consulta Estado de Cuenta por fecha
-- ID 23: permite seleccionar una fecha de corte personalizada al consultar un Estado de Cuenta.
-- Sin este permiso, la fecha de corte siempre es la fecha actual (hoy).
INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
VALUES (23, 'Fecha de corte personalizada', 'Permisos especiales', 'Permite seleccionar una fecha de corte personalizada al consultar Estado de Cuenta. El usuario podrá elegir una fecha pasada y la consulta mostrará los datos hasta esa fecha.', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  pestana = VALUES(pestana),
  descripcion = VALUES(descripcion),
  activo = VALUES(activo);
