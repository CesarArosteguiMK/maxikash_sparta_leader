-- Permiso especial para cancelar convenios directamente desde Crear Convenio.
-- Mantiene intacto el flujo existente de solicitud/autorizacion de cancelamiento.

INSERT INTO __SPARTA_SECRET_REDACTED__.modulos_web (id, pestana, nombre, descripcion, activo)
VALUES (
    151,
    'Permisos especiales',
    'Cancelamiento directo',
    'Permite cancelar convenios activos directamente capturando motivo, sin enviar la solicitud a Peticiones.',
    1
)
ON DUPLICATE KEY UPDATE
    pestana = VALUES(pestana),
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    activo = VALUES(activo);
