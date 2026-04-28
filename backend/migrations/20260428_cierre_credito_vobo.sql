-- Cierre de credito: soporte de flujo Vo.Bo (Direccion de Cobranza)
-- Ajusta tabla principal para guardar evidencia, comentario y marca de validacion.

ALTER TABLE cierre_credito_seguimiento
    ADD COLUMN vobo_comentario VARCHAR(500) NULL AFTER comentario_descarte,
    ADD COLUMN vobo_archivo VARCHAR(255) NULL AFTER vobo_comentario,
    ADD COLUMN vobo_validado_direccion TINYINT(1) NOT NULL DEFAULT 0 AFTER vobo_archivo,
    ADD COLUMN vobo_fecha_validacion DATETIME NULL AFTER vobo_validado_direccion;

ALTER TABLE cierre_credito_seguimiento
  MODIFY COLUMN estatus ENUM(
    'en_proceso',
    'enviado_finalizado',
    'enviado_cartera',
    'en_cola',
    'listo_envio',
    'descartado',
    'notificado_cartera',
    'cerrado',
    'devuelto_cartera',
    'envio_cobranza',
    'vo_bo_rechazado'
  ) NOT NULL DEFAULT 'en_proceso';