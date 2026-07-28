# Analítica: conocimiento operativo de Leonidas

Dominio: `analitica`.

## Propósito y conceptos

Analítica explica y compara métricas de cartera, buckets, pagos, campo, asignación y desempeño. Distingue bucket actual, de nacimiento y de cierre; corte, periodo, población, movimiento y crédito explicativo.

## Reglas de negocio

- Toda métrica debe incluir definición, fuente, periodo o fecha de corte y filtros relevantes.
- No se comparan cortes distintos sin mostrar sus fechas.
- Una diferencia agregada debe poder descomponerse en los créditos o movimientos que la explican cuando la fuente lo permita.
- Conteo, suma, promedio y porcentaje no son intercambiables; la unidad debe conservarse.
- Los resultados operativos generados por el servidor no se reformulan de forma que cambie su valor.
- Una gráfica se genera a partir de datos consultados y autorizados; nunca desde números inventados por el modelo.
- Si se mezclan Segundómetro, Legacy, S2 o Sparta, cada serie debe conservar su procedencia.

## Fuentes autorizadas

- `segundometro`: buckets, transiciones, morosidad y cortes.
- `legacy`: gestiones, cartera, tareas, asignaciones y pagos reflejados.
- `sparta_principal`: catálogos, operación y reportes integrados.
- `s2_estado_cuenta`: pagos y situación financiera.
- `gastos_cobranza`: indicadores de cargos.

## Permisos

Las capacidades se separan en `analitica`, `bucket`, `comparativas`, `segundometro`, `primeros_pagos` y `gastos_cobranza`. Leonidas solo usa los conjuntos habilitados para la sesión.

## Preguntas reales que debe responder

- Compara el bucket 4 de esta semana contra la anterior.
- ¿Qué créditos explican la diferencia del corte?
- Grafica los candidatos por etapa.
- ¿Cuántos pagos puntuales hubo en julio?
- Muéstrame el avance por gestor de campo.
- ¿Cuál es la tendencia de gastos de cobranza?

## Ejecutores disponibles

El dominio es de lectura, diagnóstico, reporte y visualización. No tiene ejecutores de escritura. Exportar o descargar requiere el flujo autorizado correspondiente, pero no altera la fuente.
