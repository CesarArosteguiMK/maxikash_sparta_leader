-- Ejecutar en __SPARTA_SECRET_REDACTED__ (servidor Mega / DatabaseSegundometro).
-- Registra quien guardó la aclaración desde el modal Estado de cuenta.

ALTER TABLE `cobranza_gc_verificacion_semana`
  ADD COLUMN `id_usuario_reporte` INT UNSIGNED NULL DEFAULT NULL
    COMMENT 'ID usuario Ledger (sesión) que registró la aclaración'
  AFTER `celula`;
