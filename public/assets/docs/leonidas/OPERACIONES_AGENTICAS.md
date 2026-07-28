# Operaciones agenticas de Leonidas

Este documento describe el contrato transversal de los ejecutores conectados a Sparta.

## Ciclo obligatorio

1. Detectar una intención registrada; no convertir una pregunta ambigua en escritura.
2. Recopilar un campo faltante por turno y validar tipo, formato y pertenencia.
3. Verificar el permiso del módulo antes de consultar el estado y otra vez al confirmar.
4. Leer el estado actual mediante el modelo o servicio oficial.
5. Mostrar una vista previa con acción, objetivo, datos relevantes y reversión disponible.
6. Exigir confirmación explícita.
7. Calcular una llave de idempotencia y no repetir la misma operación verificada.
8. Ejecutar mediante el modelo o API oficial; no usar SQL generado por el modelo de lenguaje.
9. Volver a leer la fuente correspondiente.
10. Declarar éxito únicamente si la verificación coincide.
11. Emitir un comprobante sanitizado con acción, actor, fecha, resultado, verificación y llave.
12. Conservar auditoría sin prompts completos, contraseñas, NIP, tokens ni rutas internas.

## Autorización reforzada

`viatico_aprobar`, `viatico_registrar_pago`, `condonacion_aprobar`, `cierre_aprobar` y `cierre_enviar_cartera` exigen dos personas distintas:

- la primera confirma la solicitud y obtiene un código `LEO-`;
- la segunda necesita el permiso funcional correspondiente;
- la segunda revisa un estado actualizado y confirma otra vista previa;
- el servidor rechaza que ambas autorizaciones pertenezcan al mismo actor;
- el código expira a las 24 horas y solo puede reclamarse una vez.

Los permisos especiales creados para separar estas facultades son `Autorizar viáticos con Leonidas`,
`Registrar pagos de viáticos con Leonidas` y `Autorizar condonaciones con Leonidas`. Deben asignarse
únicamente a los perfiles responsables mediante la administración normal de módulos de Sparta.

Registrar un pago de viáticos conserva la referencia de un pago efectuado por el sistema financiero; Leonidas no inicia transferencias bancarias.

## Adjuntos

Los adjuntos se vinculan al actor, caducan, verifican extensión, MIME, tamaño y hash, y solo se materializan en el módulo al ejecutar. Se admiten PDF, imágenes, video, Word, CSV y Excel según la acción. Tokens, rutas absolutas y secretos no aparecen en comprobantes.

## Reversión y cancelación

- Cancelar una vista previa no escribe nada.
- `ticket_asignar` puede revertirse con `ticket_desasignar`, incluyendo el historial auxiliar por crédito.
- `ticket_cerrar` se revierte mediante `ticket_reabrir` únicamente si el último movimiento fue cierre.
- `despacho_asignar_credito` tiene como operación compensatoria `despacho_desasignar_credito`.
- Las rutas pueden cancelarse únicamente con el permiso especial y las reglas del modelo.
- Una condonación ya aplicada, un cierre enviado a Cartera o un pago registrado no se revierten automáticamente: requieren el procedimiento funcional del área.

## Preguntas reales de acción

- Crea un ticket para el crédito 12345 y asígnalo a la persona 80.
- Adjunta esta evidencia al ticket 321 y agrega el seguimiento.
- Reordena las direcciones del crédito 12345 como 91, 88, 103.
- Importa este Excel de asignaciones de Despachos.
- Confirma la recepción de la unidad 50 y después inicia su revisión mecánica.
- Crea el traspaso de la unidad 50 a la ubicación 8.
- Crea una ruta de Tracking, adjunta evidencia o cancélala con motivo.
- Crea una solicitud de viáticos, adjunta comprobante y envíala a autorización.
- Prepara la condonación del crédito 12345, envíala y solicita doble autorización.
- Prepara el cierre, envíalo a Vo.Bo., apruébalo y verifica el resultado financiero.

## Componentes

- `LeonidasOperationalService`: intención, conversación guiada, vistas previas, ejecución y verificación.
- `LeonidasOperationStore`: idempotencia, comprobantes y doble autorización.
- `LeonidasAttachmentService`: adjuntos temporales y materialización segura.
- `LeonidasFinancialWorkflowService`: estados de Viáticos y Condonaciones conectados a las fuentes oficiales.
- `LeonidasTrackingEvidenceService`: carga limitada a la API oficial de Tracking.

Las reglas funcionales continúan marcadas como pendientes en `REVISIONES.json` hasta que cada responsable de área las valide.
