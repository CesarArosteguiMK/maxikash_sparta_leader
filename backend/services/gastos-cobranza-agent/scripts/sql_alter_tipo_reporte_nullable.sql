-- __SPARTA_SECRET_REDACTED__ / cobranza_gc_verificacion_semana
--
-- OBLIGATORIO para la carga lista negra (Shell Gastos cobranza / agente):
-- el script inserta tipo_reporte = NULL. Si la columna es ENUM NOT NULL, MySQL puede
-- guardar 'falta_aplicar' por defecto en lugar de NULL (comportamiento incorrecto).
--
-- Ejecutar una vez en el servidor de mega-reporte:
ALTER TABLE `cobranza_gc_verificacion_semana`
  MODIFY COLUMN `tipo_reporte` ENUM('error', 'falta_aplicar') NULL DEFAULT NULL;
