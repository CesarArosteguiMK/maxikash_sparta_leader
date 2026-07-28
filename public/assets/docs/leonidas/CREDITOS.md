# Créditos: conocimiento operativo de Leonidas

Dominio: `creditos`.

## Propósito y conceptos

El dominio reúne el estado financiero y operativo de un crédito: cliente, saldo, pagos, cuotas, mora, cargos, bucket, gestiones y documentación relacionada. El identificador principal es el número o ID de crédito. La fecha de corte modifica la lectura histórica y nunca debe asumirse si el usuario pide un día específico.

## Reglas de negocio

- Para un estado de cuenta se requiere el ID del crédito; la fecha de corte es opcional.
- Saldo, pagos, mora y cuotas deben provenir de S2 o de un adaptador aprobado, nunca de una estimación.
- Los cargos de cobranza, gestiones y bucket pueden provenir de fuentes distintas y deben atribuirse por separado.
- Una diferencia entre fuentes se presenta como diagnóstico; Leonidas no debe ocultarla ni decidir cuál es correcta sin evidencia.
- Consultar un crédito es solo lectura. Reactivar una tarea móvil es una modificación y exige propuesta, confirmación y auditoría.
- Si S2 no está disponible, Leonidas informa el error y no reutiliza un saldo antiguo como si fuera actual.

## Fuentes autorizadas

- `s2_estado_cuenta`: saldo, pagos, cuotas, mora y fecha de corte.
- `legacy`: tareas, asignaciones, gestiones y operación histórica.
- `sparta_principal`: documentación, relaciones operativas y permisos.
- `segundometro`: bucket y cortes cuando la consulta lo requiera.

## Permisos

La sesión necesita `estado_cuenta` o `aclaraciones_credito` para consultar el dominio. La información sensible adicional conserva los permisos del módulo de origen.

## Preguntas reales que debe responder

- ¿Cuál es el saldo y la siguiente cuota del crédito 12345?
- Muéstrame los pagos del crédito 12345 al 15 de julio.
- ¿Qué bucket tiene y quién lo gestiona?
- ¿Por qué no coincide el pago de S2 con la gestión de Legacy?
- Diagnostica la tarea móvil del crédito 12345.

## Ejecutores disponibles

- `cartera_reactivar_tarea_movil`: reactiva únicamente el flujo móvil soportado después de diagnóstico y confirmación.
- `condonacion_preparar`: crea un borrador sin afectar todavía la fuente financiera.
- `condonacion_enviar`: envía el borrador al estado pendiente de autorización.
- `condonacion_aprobar`: aplica la condonación mediante el modelo oficial; exige dos actores autorizados distintos.
- `condonacion_rechazar`: rechaza la solicitud con motivo obligatorio.
- `cierre_preparar`: crea o reactiva el seguimiento oficial de cierre.
- `cierre_enviar_autorizacion`: envía el cierre al Vo.Bo. de Dirección de Cobranza.
- `cierre_aprobar`: aprueba el Vo.Bo. con doble autorización.
- `cierre_rechazar`: rechaza el Vo.Bo. con comentario obligatorio.
- `cierre_enviar_cartera`: envía el cierre a Cartera con doble autorización.

Las consultas de verificación financiera comparan el flujo con la fuente final. Leonidas no modifica saldos, pagos ni mora.
