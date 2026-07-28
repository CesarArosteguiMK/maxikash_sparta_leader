# Legacy: conocimiento operativo de Leonidas

Dominio: `legacy`.

## Propósito y conceptos

Legacy contiene operación histórica de cobranza: créditos, campañas, tareas, asignaciones, usuarios, dictámenes, gestiones y pagos reflejados. Sparta puede referenciar esa información, pero no debe tratar ambas bases como si fueran una sola.

## Reglas de negocio

- Toda respuesta identifica la entidad buscada: crédito, usuario, campaña, tarea, dictamen o rango de fechas.
- Los identificadores de Sparta y Legacy no se equiparan sin una relación verificable.
- Una asignación histórica no implica que la persona siga siendo responsable vigente.
- Los pagos reflejados en Legacy se contrastan con la fuente financiera cuando la pregunta sea de saldo o conciliación.
- Una consulta genérica utiliza planes de lectura validados; el modelo no genera ni ejecuta SQL libre.
- Columnas sensibles, credenciales y datos de conexión nunca se entregan al chat.
- Crear tareas o asignaciones solo es posible mediante un ejecutor específico de otro flujo y con auditoría.

## Fuentes autorizadas

- `legacy`: fuente principal del dominio.
- `sparta_principal`: correspondencias, permisos y procesos integrados.
- `s2_estado_cuenta`: contraste financiero cuando proceda.

## Permisos

La sesión requiere `legacy`. Si la consulta cruza otro dominio, también necesita el permiso de ese dominio.

## Preguntas reales que debe responder

- ¿Qué tareas ha tenido el crédito 12345?
- ¿Quién aparece como responsable vigente?
- ¿En qué campaña está y desde cuándo?
- ¿Qué dictámenes existen para este crédito?
- Compara las gestiones de Legacy con la asignación de Sparta.

## Ejecutores disponibles

No existe un ejecutor SQL general de escritura para Legacy. Los siguientes ejecutores limitados operan Despachos mediante su modelo oficial:

- `despacho_asignar_credito`
- `despacho_desasignar_credito`
- `despacho_cambiar_estatus`
- `despacho_importar_excel`
- `despacho_adjuntar_documento`

Leonidas consulta el estado antes y después. Las demás acciones concretas, como reactivar una tarea móvil o trabajar un dictamen de Motos, permanecen en sus flujos especializados.
