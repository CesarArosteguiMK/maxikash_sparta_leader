-- Motivo de condonación: catálogo + columna en condonaciones_cobranza
-- Ejecutar en la misma base que usa la app para `condonaciones_cobranza` (DatabaseSegundometro).
-- Orden: 1) catálogo 2) ALTER. Si `id_motivo_condonacion` ya existe, omitir el ALTER (fallará y no es error en segundo despliegue).

-- 1) Catálogo
CREATE TABLE IF NOT EXISTS catalogo_motivos_condonacion (
    id INT NOT NULL PRIMARY KEY,
    motivo VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO catalogo_motivos_condonacion (id, motivo) VALUES
(1, 'Campaña Call Center'),
(2, 'Crédito liquidado'),
(3, 'Convenios'),
(4, 'Siniestros'),
(5, 'Error de sistema')
ON DUPLICATE KEY UPDATE motivo = VALUES(motivo);

-- 2) Columna (DEFAULT 1 = regla de negocio: históricos y nuevos sin dato)
ALTER TABLE condonaciones_cobranza
ADD COLUMN id_motivo_condonacion INT NOT NULL DEFAULT 1
AFTER id_credito;
