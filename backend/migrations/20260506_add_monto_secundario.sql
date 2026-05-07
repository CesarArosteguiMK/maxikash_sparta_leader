-- Migración: agregar monto_secundario a convenio_cliente_amortizacion
-- Fecha: 2026-05-06
-- Propósito: registrar el monto de pagos secundarios (complementarios) que
-- completaron el déficit de una cuota parcial.
-- Ejemplo: cuota $2,060 → pago primario $2,000 + pago secundario $60 = pagado
--          monto_pagado = 2060.00  |  monto_secundario = 60.00

ALTER TABLE convenio_cliente_amortizacion
    ADD COLUMN monto_secundario DECIMAL(10,2) NULL DEFAULT NULL
    AFTER monto_pagado;
