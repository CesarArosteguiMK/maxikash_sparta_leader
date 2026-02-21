-- Catálogo de tipos de documento para carga_documento_persona.
-- La FK fk_carga_documento exige que id_documento exista en documento(id).
-- Estructura: id, clave, nombre, obligatorio, activo.

INSERT INTO __SPARTA_SECRET_REDACTED__.documento (id, clave, nombre, obligatorio, activo)
VALUES
    (15, 'DOCUMENTO_BAJA', 'Documento baja', 0, 1),
    (16, 'DOCUMENTO_REINGRESO', 'Documento reingreso', 0, 1)
ON DUPLICATE KEY UPDATE
    clave = VALUES(clave),
    nombre = VALUES(nombre),
    obligatorio = VALUES(obligatorio),
    activo = VALUES(activo);
