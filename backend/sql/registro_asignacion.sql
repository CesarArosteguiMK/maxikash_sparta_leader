-- Historial de asignaciones por crédito (id_credito).
-- Fechas en zona horaria CDMX; fecha_eliminacion NULL = asignación activa.
-- Solo puede haber un registro activo por crédito.
--
-- Endpoint: POST /sabueso/getHistorialAsignacionCredito
-- Body: { "id_credito": 123 }
-- Ejemplo respuesta:
-- {
--   "success": true,
--   "asignado_actual": "JOSE HERNANDEZ",
--   "estado": "con_historial",
--   "historial": [
--     { "persona": "Sky Logic", "desde": "2026-01-29 07:02", "hasta": "2026-01-29 10:15", "duracion_humana": "3 horas" },
--     { "persona": "JOSE HERNANDEZ", "desde": "2026-01-29 11:00", "hasta": "2026-02-04 14:30", "duracion_humana": "6 días" }
--   ]
-- }

CREATE TABLE IF NOT EXISTS registro_asignacion (
    id                  INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_credito          INT          NOT NULL,
    persona_asignada    VARCHAR(150) NOT NULL,
    fecha_asignacion    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_eliminacion   DATETIME     NULL,
    KEY idx_registro_credito (id_credito),
    KEY idx_registro_activo (id_credito, fecha_eliminacion)
);
