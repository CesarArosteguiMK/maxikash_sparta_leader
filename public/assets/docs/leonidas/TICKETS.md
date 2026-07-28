# Tickets: conocimiento operativo de Leonidas

Dominio: `tickets`.

## Propósito y conceptos

Incluye tickets de Sabueso, incidencias, aclaraciones, asignación, conversación, dictamen, evidencias, prórrogas, ilocalizables, reconsulta de pagos e indicadores.

## Reglas de negocio

- El identificador principal es el folio; también puede buscarse por crédito, solicitante, responsable, estatus o periodo.
- Estatus actual, responsable e historial se muestran como campos separados.
- Una conversación o evidencia pertenece al ticket y no debe atribuirse a otro folio por coincidencia de crédito.
- Las aclaraciones financieras deben contrastarse con S2 cuando dependen de pagos o saldo.
- Los reportes semanales deben declarar periodo, filtros, total y fuente.
- Crear, asignar, cerrar o agregar seguimiento son escrituras; no deben ejecutarse con una consulta ambigua.

## Fuentes autorizadas

- `sparta_principal`: tickets, estatus, responsables, conversación, evidencias e indicadores.
- `sabueso`: verificaciones y operación propia del servicio.
- `legacy`: contexto de cobranza.
- `s2_estado_cuenta`: contraste de pagos.

## Permisos

La sesión requiere `tickets`. Consultas cruzadas conservan los permisos de créditos, Legacy o estado de cuenta.

## Preguntas reales que debe responder

- ¿Cuál es el estatus del ticket 123?
- ¿Quién lo tiene asignado y desde cuándo?
- Muéstrame el historial y las evidencias disponibles.
- ¿Cuántas prórrogas siguen abiertas esta semana?
- ¿Qué tickets necesitan reconsulta de pagos?

## Ejecutores disponibles

- `ticket_crear`
- `ticket_asignar`
- `ticket_desasignar`: quita al responsable y cierra el historial activo de asignación por crédito.
- `ticket_seguimiento`
- `ticket_adjuntar_evidencia`
- `ticket_cerrar`
- `ticket_reabrir`: solo restaura tickets cuyo último movimiento fue un cierre; nunca recupera una eliminación deliberada.
- `viatico_crear`
- `viatico_adjuntar_comprobante`
- `viatico_enviar_autorizacion`
- `viatico_aprobar`: requiere dos personas autorizadas distintas.
- `viatico_rechazar`
- `viatico_registrar_pago`: registra la referencia de un pago ya realizado por el sistema financiero y exige doble autorización.

Todos consultan el estado vigente, presentan vista previa, bloquean duplicados y generan comprobante tras verificar la fuente.
