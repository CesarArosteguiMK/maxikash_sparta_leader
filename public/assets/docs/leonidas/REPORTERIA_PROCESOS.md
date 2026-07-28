# Reportería, indicadores y procesos periódicos

## Reportería

`Reporteria` reúne reportes de Capital Humano, Campo, Call Center, comparativas, asignación, direcciones, avance de bucket, Legacy, Sabueso, primeros pagos, cartera y vencimientos.

Reglas:

- Todo reporte declara fuente, filtros, periodo, corte y total.
- Descargar Excel no modifica el conjunto de datos.
- Comparativas requieren periodos equivalentes.
- La asignación de direcciones sí modifica prioridad y debe distinguirse de un reporte.
- Los procesos que envían correo son comunicaciones y requieren autorización.

Preguntas reales:

- Dame el reporte de bajas de Capital Humano.
- Compara el avance semanal.
- ¿Qué créditos explican el avance del bucket?
- Descarga los primeros pagos de la semana actual.
- ¿A quién se envía el reporte programado?

## Primeros Pagos

`PrimerosPagosS2` ejecuta el proceso especializado. Reportería contiene históricos, semanas, comparativos, jerarquías, vencimientos y destinatarios.

Reglas:

- Primer pago y vencimiento son eventos distintos.
- Semana actual, histórico y siguiente semana deben etiquetarse.
- Un envío programado y un envío manual conservan auditoría separada.
- El resultado de S2 se atribuye a su corte.

## Segundómetro

`Segundometro` administra monitoreo, streaming, ventanas, archivos, cortes históricos, reportes, diagnósticos SSH y estado del agente.

Reglas:

- Consultar estado, listar y descargar son lecturas.
- Copiar, eliminar, truncar o ejecutar ahora son acciones administrativas.
- Truncar datos nunca debe convertirse en un ejecutor conversacional genérico.
- Estado del agente, hora del servidor y estado del reporte son mediciones diferentes.
- Los diagnósticos no exponen host, usuario, llave o token.

## Analítica e indicadores

`Analitica` y `Api` funcionan como entradas de reportes, mientras `LeonidasAnaliticaService` y la consulta semántica generan resultados validados.

Reglas:

- El modelo ayuda a seleccionar y explicar; el servidor calcula.
- Las métricas operativas no se reescriben alterando cifras.
- Una gráfica conserva los mismos datos que la tabla.

## Sabueso y reportes

Sabueso contiene estadísticas, detalle por día, gestor y reporte semanal, además de reconsulta de pago e ilocalizables.

Reglas:

- Reconsultar un pago no significa que el pago exista.
- Ilocalizable es un resultado operativo con historial.
- Los reportes por gestor respetan alcance y permisos.

## Fuentes, permisos y ejecutores

Fuentes: `sparta_principal`, `legacy`, `segundometro`, `s2_estado_cuenta`, `sabueso` y `gastos_cobranza`.

Leonidas genera consultas, tablas, gráficas y archivos autorizados. El control de servicios se limita a `servicio_local_control`; procesos administrativos destructivos de Segundómetro no están conectados como ejecutores.
