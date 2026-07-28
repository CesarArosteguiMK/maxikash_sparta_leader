# Motos: cadena operativa completa

Esta ficha amplía `motos_adjudicadas` desde la solicitud y recuperación hasta almacén, revisión, piso de venta y tracking.

## Solicitud de Adjudicación

`SolicitudAdjudicacion` maneja solicitudes de Atención a Clientes, Call Center y Despachos, además de bandeja, detalle, responsables y asignación.

Reglas:

- La solicitud y la adjudicación efectiva son etapas diferentes.
- Cada origen conserva su flujo y sus datos.
- Asignar bandeja requiere un responsable válido y permiso.

## Atención a Clientes

`AtencionClientes` contiene consulta, evidencias, recuperación, cierre documental y recepción. El código separa entrantes, recibidos, aprobados, correcciones y blacklist.

Reglas:

- Aprobar evidencia, pedir corrección y liberar blacklist son decisiones distintas.
- Cierre documental y recepción física no se consideran la misma confirmación.
- Los conteos se presentan por etapa y corte.

## Configuración de Motos Adjudicadas

`ConfigMotosAdj` mantiene rutas, FAD, reglas, excepciones, pendientes y recordatorios. Configurar una regla puede cambiar el comportamiento futuro del flujo; Leonidas solo debe explicarla o abrir la pantalla mientras no exista un ejecutor dedicado.

## Almacén Virtual

`AlmacenVirtual` administra inventario, células, ubicaciones, unidades y pendientes provenientes de Motos Adjudicadas. El modelo contiene evidencias de ingreso, ficha de unidad, recepción, revisión mecánica y tablero Kanban.

Reglas:

- Crear una unidad desde Motos requiere evitar duplicados.
- Recepción, revisión mecánica, Kanban y piso de venta son estados distintos.
- Una ubicación debe existir y estar activa.
- Serie, motor y crédito son identificadores de reconciliación, no sustitutos automáticos.

## Recepción, revisión, piso de venta y traspasos

`MotosAdjudicadas` expone confirmación de recepción, inicio y final de revisión mecánica, envío a piso de venta, override de supervisor y órdenes de traspaso.

Reglas:

- Un override requiere permiso reforzado y motivo.
- Un traspaso separa creación de orden y confirmación de recepción.
- Enviar a piso de venta requiere que la unidad cumpla las etapas previas configuradas.

## Tracking de Recolección

`TrackingRecoleccion` administra rutas, planeación, créditos, borradores, CEDIS, agencias, transportistas, unidades, ubicación en vivo, chat, archivos y OTP.

Reglas:

- Planear, iniciar, actualizar y cancelar una ruta son acciones distintas.
- La ubicación actual y el historial tienen diferente granularidad.
- El OTP es temporal y no se almacena ni repite en el chat.
- Rechazar evidencia requiere conservar motivo y trazabilidad.
- Agencia, transportista, unidad y CEDIS son entidades independientes.

## API de motos

`MotosApi` consulta por crédito o serie, reporta estado y controla caché. Limpiar caché no modifica el registro de negocio, pero sigue siendo una acción administrativa.

## Fuentes, permisos y ejecutores

Fuentes: `sparta_principal`, `legacy`, `s2_estado_cuenta`, `segundometro` y servicios de tracking autorizados.

Ejecutores actuales de Leonidas: `moto_asignar`, `moto_guardar_datos`, `moto_enviar_evidencias` y `moto_forzar_evidencias`. El resto se consulta o se opera en su pantalla.
