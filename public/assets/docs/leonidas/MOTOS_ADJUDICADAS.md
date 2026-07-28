# Motos Adjudicadas: conocimiento operativo de Leonidas

Dominio: `motos_adjudicadas`.

## Propósito y conceptos

Gestiona créditos adjudicados, asignación de responsable, recuperación, datos físicos de la motocicleta, evidencias, dictamen, recepción, inventario, tracking e historial de estatus.

## Reglas de negocio

- Toda operación parte de un ID de crédito válido.
- Antes de asignar se diagnostican el crédito, su estatus y la asignación vigente.
- El responsable debe ser una persona activa y autorizada para el flujo.
- La captura de motocicleta valida cada dato; serie, motor, año, kilometraje y teléfono tienen formatos controlados.
- Enviar evidencias usa el flujo normal. Forzar evidencias requiere permiso especial y nunca debe confundirse con el envío ordinario.
- Un desbloqueo de dictamen requiere diagnóstico, autorización, NIP y confirmación; Leonidas no conserva ni muestra el NIP.
- Los datos de Sparta, Legacy, S2 y Segundómetro pueden diferir. Leonidas debe presentar el cruce antes de proponer un cambio.

## Fuentes autorizadas

- `sparta_principal`: asignaciones, datos de moto, evidencias, recepción, inventario y tracking.
- `legacy`: tareas, dictámenes, responsables e historial operativo.
- `s2_estado_cuenta`: situación financiera del crédito.
- `segundometro`: referencias de cartera y bucket cuando apliquen.

## Permisos

La consulta necesita `motos`. El forzado de estatus requiere `motos_override_estatus`. Las asignaciones conservan el permiso operativo correspondiente.

## Preguntas reales que debe responder

- ¿Quién tiene asignado el crédito 12345?
- Dame el diagnóstico cruzado del crédito 12345.
- ¿Qué datos y evidencias faltan para esta moto?
- ¿Qué pasó con el dictamen y qué componente está bloqueado?
- Genera el reporte de motos de la semana 27.

## Ejecutores disponibles

- `moto_asignar`
- `moto_guardar_datos`
- `moto_enviar_evidencias`
- `moto_forzar_evidencias`
- `almacen_confirmar_recepcion`
- `almacen_iniciar_revision`
- `almacen_finalizar_revision`
- `almacen_crear_traspaso`
- `almacen_confirmar_entrega`
- `tracking_crear_ruta`
- `tracking_actualizar_ruta`
- `tracking_cancelar_ruta`
- `tracking_adjuntar_evidencia`

Los ejecutores de Almacén validan la unidad y su transición actual. Los de Tracking escriben en el modelo o la API oficial y verifican la ruta después de ejecutar. Cada ejecutor valida nuevamente permisos y estado al confirmar para evitar aplicar una propuesta obsoleta.
