# Créditos: submódulos y flujos complementarios

Esta ficha amplía el dominio `creditos` con componentes que existen en la aplicación y que no deben confundirse entre sí.

## Estado de Cuenta

El controlador `EstadoCuenta` consulta y valida créditos, presenta documentación, notas, gastos de cobranza, contactos, dictámenes, condonaciones parciales y complementos. También contiene un flujo específico para Guatemala.

Reglas:

- El saldo y los pagos se atribuyen a la fuente financiera y a su fecha de corte.
- Las notas y documentos no modifican el saldo.
- Un dictamen de llamada conserva contacto, resultado, motivo y usuario.
- Los documentos del cliente se protegen con las reglas del módulo.
- El flujo Guatemala se identifica explícitamente; no se mezcla con la cartera de México.

Preguntas reales:

- ¿Qué complementos tiene el estado de cuenta?
- ¿Qué dictámenes de llamada existen para este crédito?
- ¿Qué documentos y notas están asociados?
- ¿Cuál fue el historial de gastos de cobranza?

## Aclaración de crédito y aplicaciones de pago

`Aclaracioncredito` y `Aplicacionespago` tienen paneles administrativos especializados. Una aclaración investiga una diferencia; una aplicación de pago registra o corrige el tratamiento operativo correspondiente. Leonidas puede explicar y abrir estos paneles, pero no debe afirmar que aplicó un pago sin un ejecutor conectado y comprobante.

## Cierre de Crédito

`CierreCredito` separa en proceso, visto bueno, envío y finalización. El código contempla envío a Cartera, aprobación o rechazo, descarte, historial, reportes, peticiones de cancelación y reactivación de convenio.

Reglas:

- Cambiar de etapa es una escritura.
- Aprobar, rechazar, devolver o descartar exige estado vigente, motivo cuando aplique y auditoría.
- Cierre de crédito y cierre de convenio son conceptos relacionados, pero diferentes.
- Las peticiones de cancelación o reactivación conservan su autorización independiente.

## Condonaciones

`Condonaciones` permite consulta, historial, detalle, creación, cambio de estado, gastos de cobranza y estadísticas.

Reglas:

- Una condonación no equivale a un pago.
- Monto, concepto, estado, solicitante, autorizador e historial deben mostrarse por separado.
- Crear o cambiar estado requiere el permiso del módulo y confirmación.
- Leonidas no tiene todavía un ejecutor propio para condonaciones.

## Crédito problemático

`Creditoproblematico` expone un panel administrativo. Debe tratarse como clasificación operativa, no como juicio sobre la persona. Los detalles se consultan únicamente mediante el módulo autorizado.

## Fuentes, permisos y ejecutores

Fuentes habituales: `sparta_principal`, `s2_estado_cuenta`, `legacy` y `gastos_cobranza`.

Los permisos se heredan del submódulo consultado. Leonidas dispone de lectura de estado de cuenta y del ejecutor limitado `cartera_reactivar_tarea_movil`; no tiene ejecutores generales para aplicaciones de pago, cierre o condonaciones.
