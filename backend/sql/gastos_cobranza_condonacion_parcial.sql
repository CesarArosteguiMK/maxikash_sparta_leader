-- Condonación parcial de gastos de cobranza
-- Base de datos: __SPARTA_SECRET_REDACTED__ (ejecutar en el servidor donde está la tabla gastos_cobranza).
-- Añade dos columnas junto a monto_valor para guardar condonación parcial y motivo.

ALTER TABLE `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
  ADD COLUMN condonacion_parcial_monto DECIMAL(10,2) NULL DEFAULT NULL AFTER monto_valor,
  ADD COLUMN condonacion_parcial_motivo TEXT NULL DEFAULT NULL AFTER condonacion_parcial_monto;
