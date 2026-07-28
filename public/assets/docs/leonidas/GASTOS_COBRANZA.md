# Gastos de Cobranza: conocimiento operativo de Leonidas

Dominio: `gastos_cobranza`.

## Propósito y conceptos

Reúne conceptos, cargos generados, recuperado, pendiente, condonado, responsables, archivos, reportes y el agente local que procesa la operación periódica.

## Reglas de negocio

- Toda cifra debe indicar periodo, concepto y unidad.
- Generado, recuperado, pendiente y condonado son estados diferentes y no se suman como si fueran la misma medida.
- La tendencia debe comparar periodos equivalentes.
- El estado del agente y el resultado financiero son datos distintos: un servicio activo no garantiza que una corrida haya terminado correctamente.
- Para diagnosticar una ejecución se revisan disponibilidad, última corrida, error y logs permitidos.
- El control local solo admite servicios y operaciones incluidos en una lista del servidor. Nunca acepta comandos, rutas o puertos escritos por el usuario o por la IA.
- Subir archivos permanece en el flujo del módulo mientras no exista un ejecutor dedicado.

## Fuentes autorizadas

- `gastos_cobranza`: cargos, recuperación, pendientes, condonaciones y procesos.
- `sparta_principal`: catálogos, permisos y referencias.
- `legacy`: contraste operativo cuando aplique.

## Permisos

La lectura requiere `gastos_cobranza`. Controlar el agente requiere además `servicios_locales`, reservado al acceso permanente autorizado.

## Preguntas reales que debe responder

- ¿Cuánto se generó, recuperó y quedó pendiente en julio?
- Grafica los gastos por concepto.
- ¿Cuándo terminó la última ejecución?
- ¿Qué error reporta el agente?
- ¿Está activo el servicio de Gastos de Cobranza?

## Ejecutores disponibles

- `servicio_local_control`: permite iniciar, detener o reiniciar únicamente el agente permitido, después de vista previa y confirmación.

Los archivos y ajustes de negocio se administran desde el módulo correspondiente.
