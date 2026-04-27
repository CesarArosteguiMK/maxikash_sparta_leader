-- Veredictos de validación (Atención a clientes / modal Evidencias)
-- Ejecutar una sola vez en la base de datos. Si la columna ya existe, el ALTER falla (omitir o comentar).

ALTER TABLE `adj_evidencia`
  ADD COLUMN `val_atn` TINYINT(1) NULL DEFAULT NULL COMMENT 'NULL=pendiente,1=aceptar,2=rechazar' AFTER `url`,
  ADD COLUMN `comentario_atn` VARCHAR(2000) NULL DEFAULT NULL AFTER `val_atn`;
