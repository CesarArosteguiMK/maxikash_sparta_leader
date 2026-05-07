-- Recrear convenio id=377 para crédito 1092796 (ALFONZO SANTIAGO PACHECO)
-- Borrar convenio anterior antes de insertar:
--   DELETE FROM convenio_cliente_amortizacion WHERE id_convenio_cliente = 377;
--   DELETE FROM convenio_cliente WHERE id = 377;
--
-- NOTA: se puede insertar con estatus='completado' o 'activo'.
-- Con el fix del 2026-05-06, el branch 'completado' también corre
-- auto-conciliación S2 para filas en estatus 'pendiente'/'vencido'.

INSERT INTO __SPARTA_SECRET_REDACTED__.convenio_cliente
(id, id_credito, id_producto_convenio, id_producto_convenio_detalle, tipo, condonacion_aplicada,
 nombre_cliente, bucket_morosidad_real, dias_mora, avance_pago_plazo, adeudo_total_original,
 base_calculo, porcentaje_descuento, descuento_monto, monto_adicional, total_a_pagar,
 pago_inicial_monto, numero_semanas, frecuencia, pago_semanal, fecha_acuerdo, fecha_primer_pago,
 fecha_ultimo_pago, fecha_cancelacion, numero_semana_cancelacion, usuario_cancela, pdf_adjunto,
 estatus, id_celula, fecha_incumplimiento, usuario_alta, fecha_alta, usuario_modifica,
 fecha_modifica, tipo_calendario)
VALUES
(377, 1092796, 4, 5, 'estandar', NULL,
 'ALFONZO SANTIAGO PACHECO', 'g) 61 a 90 dias', 79, '41-60%', 25758.23,
 NULL, 45.00, 9615.43, 0.00, 11752.20,
 NULL, 2, 'semanal', 5876.10, '2026-04-29', '2026-05-07',
 '2026-05-14', NULL, NULL, NULL, '/uploads/convenios/convenio_1092796_20260429_122549.pdf',
 'completado', 1, NULL, 'JOSE MARTIN PEREZ', '2026-04-29 18:18:36', NULL,
 '2026-04-29 18:28:04', 'semanal');
