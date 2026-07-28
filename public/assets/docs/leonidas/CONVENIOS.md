# Convenios: conocimiento operativo de Leonidas

Dominio: `convenios`.

## Propósito y conceptos

Un convenio es un acuerdo de pago calculado sobre reglas vigentes del producto y del crédito. Distingue oferta, convenio activo, cuota, comprobante, pago conciliado, incumplimiento, cancelación y reactivación.

## Reglas de negocio

- El crédito debe existir y no tener otro convenio activo.
- Un convenio completado impide generar una oferta nueva.
- Las ofertas normales parten de 8 días de mora y el bucket debe estar permitido por el producto.
- La base puede ser adeudo total o saldo total de capital, según la configuración del producto.
- El plazo máximo depende del producto y del rango del monto; no existe un máximo global único.
- Pendiente de conciliar significa que existe comprobante, pero el pago aún no fue confirmado o aplicado.
- La búsqueda de pagos en S2 usa la ventana operativa definida por el módulo.
- La cancelación y la reactivación son procesos auditados. Reactivar habilita una oferta nueva; no revive el convenio anterior.
- Para cambiar condiciones comerciales de un convenio activo se cancela y se genera uno nuevo con una oferta recalculada.

Las reglas detalladas y vigentes se encuentran también en `public/assets/docs/CONVENIOS.md`.

## Fuentes autorizadas

- `sparta_principal`: productos, ofertas, convenios, cuotas, comprobantes y auditoría.
- `s2_estado_cuenta`: pagos, liquidación y respaldo financiero.
- `legacy`: referencias operativas del crédito.

## Permisos

La sesión necesita `convenio`. Cualquier creación, cancelación, conciliación o reactivación conserva además las autorizaciones del módulo.

## Preguntas reales que debe responder

- ¿El crédito 12345 es elegible para convenio?
- ¿Cuál es el plazo máximo y el calendario de esta oferta?
- ¿Qué pagos están pendientes de conciliar?
- ¿Por qué se canceló este convenio?
- ¿Este crédito necesita reactivación antes de una oferta nueva?

## Ejecutores disponibles

- `convenio_crear`: prepara y registra un convenio válido después de mostrar oferta y calendario para confirmación.

Las demás operaciones del módulo se explican o se abren en pantalla mientras no exista un ejecutor dedicado.
