# Cobranza de campo, despachos y seguimiento

Esta ficha reúne componentes de operación de campo relacionados con créditos, rutas, gestores y despachos.

## Despachos

`Despachos` permite asignar y desasignar créditos, cambiar estatus, consultar cartera asignada, guardar comentarios, manejar documentos e importar asignaciones desde Excel. También expone la vista Mi Gestión.

Reglas:

- Despacho, gestor y crédito deben resolverse con identificadores válidos.
- Asignar y desasignar son escrituras auditables.
- Una importación debe mostrar coincidencias, errores y duplicados antes de aplicar.
- Los documentos conservan tipo, origen y permisos.
- Los convenios del crédito se consultan como contexto; no se modifican desde el flujo de Despachos.

Preguntas reales:

- ¿Qué créditos tiene este despacho?
- ¿Cuál es el historial de gestores del crédito?
- ¿Qué documentos están cargados?
- ¿Qué filas fallarían en este Excel de asignación?

## Gestión de Campo

`GestionCampo` expone inicio, evaluación y listado. `Gestiones` contiene seguimiento. Estos componentes describen actividad operativa; no sustituyen los pagos o saldos de S2.

Reglas:

- Una gestión debe conservar crédito, gestor, fecha, resultado y origen.
- Evaluar una gestión no equivale a modificar el estado financiero.
- Los indicadores de campo declaran periodo y población.

## Indicadores

`indicadores` calcula KPI, gestiones y eficiencia por ventanas, intensidad, promesas de pago, cartera inicial, matrices de bucket, espartanos y auditoría.

Reglas:

- Eficiencia necesita numerador, denominador y periodo.
- Las ventanas 1 a 7 y 8 a 21 no deben mezclarse.
- Una promesa de pago no se cuenta como pago realizado.
- Una matriz de buckets requiere corte de origen y destino.

## Viáticos

`Viaticos` dispone de panel administrativo y se integra con Tickets/Sabueso para solicitudes y seguimiento.

Reglas:

- Solicitar, autorizar, comprobar, rechazar y cerrar un viático son estados distintos.
- Montos, comprobantes y datos personales requieren permisos del módulo.
- Leonidas puede reconocer y explicar Viáticos, pero no tiene un ejecutor propio para autorizar o pagar.

## Fuentes, permisos y ejecutores

Fuentes: `sparta_principal`, `legacy`, `geografia`, `segundometro` y `s2_estado_cuenta` cuando se contraste el crédito.

La sesión necesita los módulos correspondientes de Despachos, Campo, Indicadores o Viáticos. Leonidas no tiene ejecutores generales para estas escrituras; puede consultar mediante pasarelas autorizadas, explicar y abrir las pantallas.
