# Convenios: reglas operativas

## Elegibilidad

- El crédito debe existir en la fuente operativa y no tener otro convenio activo.
- Un convenio completado impide generar una oferta nueva.
- Las ofertas normales parten de 8 días de mora. El bucket del crédito debe estar incluido en los buckets del producto.
- El producto debe estar activo y el crédito debe cumplir el avance mínimo cuando el producto lo configure.
- La base de cálculo puede ser adeudo total o saldo total de capital, según el producto.
- Si el producto tuvo un convenio cancelado, la oferta queda bloqueada hasta aprobar una reactivación.

## Consulta por fecha

El calendario dinámico de S2 está temporalmente pausado. El módulo usa el corte operativo actual y conserva el código histórico detrás del interruptor `Convenios::CALENDARIO_DINAMICO_ACTIVO`; solo debe cambiarse a `true` cuando se solicite explícitamente reactivarlo.

## Plazo

No hay un máximo global de semanas. El plazo máximo depende del producto y del rango del monto adeudado. Si no existe una regla por monto, se usa el periodo final configurado en el producto.

## Reactivación

La reactivación habilita nuevamente una oferta para crear un convenio nuevo. No revive el convenio anterior. Requiere historial previo, no tener un convenio activo, solicitud, autorización y auditoría.

## Cancelación

La cancelación automática por vencimiento está desactivada. Las cuotas vencidas permanecen en seguimiento y continúan conciliándose contra S2.

La cancelación manual requiere un motivo. Puede pasar por solicitud y autorización o ejecutarse directamente cuando el usuario tiene el permiso especial correspondiente.

## Pagos y conciliación

Pendiente de conciliar significa que existe un comprobante cargado, pero el pago todavía no fue confirmado ni aplicado. La conciliación registra monto pagado, monto aplicado, sobrante, fecha, comentario y usuario. La búsqueda de pagos en S2 usa una ventana de 3 días antes a 6 días después de la fecha de la cuota.

## Modificación

Las condiciones comerciales y el calendario de un convenio activo no se editan libremente. El PDF puede reemplazarse y los comprobantes o conciliaciones se administran por separado. Para cambiar producto, descuento, plazo, pago inicial o fechas, debe cancelarse el convenio y generarse uno nuevo con una oferta recalculada; si corresponde, primero se aprueba la reactivación.
