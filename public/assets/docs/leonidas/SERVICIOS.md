# Servicios: conocimiento operativo de Leonidas

Dominio: `servicios`.

## Propósito y conceptos

Supervisa agentes y servicios locales que alimentan procesos como Segundómetro, correos de primeros pagos, cartera y Gastos de Cobranza. Distingue disponibilidad, puerto en escucha, health check, proceso, última ejecución, logs y autoinicio.

## Reglas de negocio

- Consultar estado no modifica el servicio y no requiere confirmación.
- Iniciar, detener o reiniciar sí modifica el entorno y necesita propuesta, confirmación y auditoría.
- Solo pueden controlarse servicios definidos en la lista del servidor.
- Leonidas nunca acepta comandos libres, ejecutables, rutas, scripts o puertos proporcionados en el mensaje.
- Después de una acción debe comprobar nuevamente el estado; lanzar un proceso no equivale a confirmar que quedó disponible.
- Un puerto ocupado puede pertenecer a otro proceso y debe diagnosticarse antes de reiniciar.
- Los logs se resumen sin exponer secretos, tokens, credenciales o datos personales.

## Fuentes autorizadas

- `sparta_principal`: catálogo y permisos.
- `segundometro`: estado del servicio relacionado.
- `gastos_cobranza`: agente y ejecución operativa.
- Verificaciones locales permitidas por el servidor.

## Permisos

La consulta requiere `servicios` o el permiso funcional del servicio. El control necesita `servicios_locales`, reservado a personas con acceso permanente autorizado a Leonidas.

## Preguntas reales que debe responder

- ¿Está arriba el agente de Segundómetro?
- ¿Cuándo fue la última ejecución de primeros pagos?
- ¿Qué error reciente tiene Gastos de Cobranza?
- Prepara el reinicio del servicio de cartera.
- ¿El health check responde aunque el puerto esté abierto?

## Ejecutores disponibles

- `servicio_local_control`: operaciones permitidas `iniciar`, `detener` y `reiniciar` sobre servicios conocidos.

No existe un ejecutor de comandos generales.
