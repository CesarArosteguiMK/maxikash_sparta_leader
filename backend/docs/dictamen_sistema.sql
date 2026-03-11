-- Tabla para el "Dictamen del Sistema" (verificacion automatica de visitas).
-- Ejecutar en el esquema __SPARTA_SECRET_REDACTED__ (misma BD donde esta ticket).
-- Version validada en MySQL: COMMENT en una sola linea por columna evita errores de parseo.

CREATE TABLE IF NOT EXISTS dictamen_sistema (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket            INT NOT NULL,
    id_dictamen          INT NOT NULL COMMENT 'ID del dictamen que origino la revision',
    id_credito           INT NULL,
    id_gestor            INT NULL COMMENT 'id persona del gestor asignado al ticket',
    nombre_gestor        VARCHAR(200) NULL,
    gestiones_al_enviar  INT NOT NULL DEFAULT 0 COMMENT 'Total de gestiones al enviar el dictamen',
    gestiones_al_revisar INT NULL COMMENT 'Total de gestiones al momento de la revision 12 h',
    resultado            VARCHAR(40) NOT NULL DEFAULT 'pendiente' COMMENT 'pendiente | no_visito | visito_campo | visito_telefonico | distancia_lejana | sin_coordenadas',
    detalle              TEXT NULL COMMENT 'JSON con detalle de distancias y gestiones',
    fecha_envio_dictamen DATETIME NULL COMMENT 'Fecha en que se envio el dictamen al gestor',
    fecha_revision       DATETIME NULL COMMENT 'Fecha en que el sistema genero el dictamen',
    fecha_creacion       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ds_ticket   (id_ticket),
    INDEX idx_ds_dictamen (id_dictamen),
    INDEX idx_ds_credito  (id_credito)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dictamen automatico del sistema';
