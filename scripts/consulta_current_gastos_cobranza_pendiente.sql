/*
Clientes/creditos en current con gastos de cobranza pendientes.

Definicion usada:
  - Current: tbl_segundometro_semana.Cierre_Actual = 'a) Current'
  - Gasto pendiente: gastos_cobranza.condonado = 0 y estatus_pago IN (0, 1)
      estatus_pago = 0 -> pendiente total
      estatus_pago = 1 -> pago parcial, aun pendiente
*/

SELECT
    s.Id_credito,
    s.Id_cliente,
    s.Nombre_cliente,
    s.Celular,
    s.Sucursal,
    s.Status_credito,
    s.Bucket_Morosidad_Real,
    s.Cierre_Actual,
    s.Dias_mora,
    s.Dias_mora_ajustado,
    s.Saldo_vencido_inicio,
    COUNT(gc.id_gastos_cobranza) AS gastos_pendientes,
    SUM(gc.monto_valor) AS monto_gc_original,
    SUM(COALESCE(gc.condonacion_parcial_monto, 0)) AS monto_condonado_parcial,
    SUM(
        CASE
            WHEN COALESCE(gc.estatus_pago, 0) = 1
                THEN COALESCE(gc.monto_parcial_pagado, 0)
            ELSE 0
        END
    ) AS monto_pagado_parcial,
    SUM(
        GREATEST(
            COALESCE(gc.monto_valor, 0)
            - COALESCE(gc.condonacion_parcial_monto, 0)
            - CASE
                WHEN COALESCE(gc.estatus_pago, 0) = 1
                    THEN COALESCE(gc.monto_parcial_pagado, 0)
                ELSE 0
              END,
            0
        )
    ) AS saldo_gc_pendiente,
    MIN(gc.periodo_inicio) AS primer_periodo_gc,
    MAX(gc.periodo_fin) AS ultimo_periodo_gc,
    GROUP_CONCAT(gc.id_gastos_cobranza ORDER BY gc.periodo_inicio, gc.id_gastos_cobranza) AS ids_gastos_cobranza
FROM `__SPARTA_SECRET_REDACTED__`.tbl_segundometro_semana s
INNER JOIN `__SPARTA_SECRET_REDACTED__`.gastos_cobranza gc
        ON gc.Id_credito = CAST(s.Id_credito AS UNSIGNED)
WHERE s.Cierre_Actual = 'a) Current'
  AND (gc.condonado IS NULL OR gc.condonado = 0)
  AND (gc.estatus_pago IS NULL OR gc.estatus_pago IN (0, 1))
GROUP BY
    s.Id_credito,
    s.Id_cliente,
    s.Nombre_cliente,
    s.Celular,
    s.Sucursal,
    s.Status_credito,
    s.Bucket_Morosidad_Real,
    s.Cierre_Actual,
    s.Dias_mora,
    s.Dias_mora_ajustado,
    s.Saldo_vencido_inicio
HAVING saldo_gc_pendiente > 0
ORDER BY saldo_gc_pendiente DESC, s.Id_credito ASC;
