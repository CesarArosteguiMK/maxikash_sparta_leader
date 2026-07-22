# Evaluacion tecnica: desarrollador junior y medio-avanzado

Tecnologias objetivo: PHP 8+, MySQL 8+ y SQL parametrizado.

Tiempo sugerido: 90 a 120 minutos. Puntaje total: 90 puntos, 10 por ejercicio.

## Instrucciones para la persona candidata

- Explica brevemente tus decisiones y supuestos.
- Prioriza legibilidad, validacion de entradas y manejo de errores.
- No uses datos reales, secretos ni consultas concatenadas con entrada de usuario.
- Puedes usar pseudocodigo cuando el enunciado no dependa de una tecnologia especifica.

## Pruebas de logica

### 1. Junior: ausencias validas

Implementa una funcion `validarAusencia(string $inicio, string $fin): array`.

Recibe dos fechas en formato `YYYY-MM-DD` y debe devolver:

- `valida: true` si ambas fechas son validas y `fin` es igual o posterior a `inicio`.
- `valida: false` y un mensaje claro si falta una fecha, el formato es invalido o la fecha final es anterior a la inicial.
- `dias: N` con la cantidad de dias de ausencia incluyendo ambos extremos. Por ejemplo, del `2026-07-04` al `2026-07-05` son `2` dias.

Incluye al menos cinco casos de prueba.

### 2. Intermedio: consolidacion de eventos

Recibes un arreglo de eventos con esta forma:

```php
[
    ['credito' => 100, 'tipo' => 'asignado', 'fecha' => '2026-07-20 09:00:00'],
    ['credito' => 100, 'tipo' => 'gestionado', 'fecha' => '2026-07-20 11:00:00'],
    ['credito' => 200, 'tipo' => 'asignado', 'fecha' => '2026-07-20 10:00:00'],
]
```

Escribe una funcion que devuelva un resumen por credito con:

- Fecha de la primera asignacion.
- Fecha de la ultima gestion.
- Estatus `Pendiente` si no existe gestion posterior a la asignacion; de lo contrario `Atendido`.

Los eventos pueden llegar desordenados, repetidos o con fechas invalidas. Decide y documenta como los manejas.

### 3. Medio-avanzado: deteccion de solapamientos

Un gestor no puede tener dos ausencias que se traslapen. Cada ausencia tiene `id`, `id_gestor`, `inicio` y `fin`.

Implementa una funcion que reciba las ausencias de varios gestores y devuelva los pares de registros que se traslapan por cada gestor. Dos registros se traslapan si comparten al menos un dia; por ejemplo `04-07` y `07-10` se traslapan el dia 07.

La solucion debe evitar comparar innecesariamente ausencias de gestores distintos y debe explicar su complejidad aproximada.

## Pruebas de base de datos

Usa estas tablas simplificadas:

```sql
persona(id, nombres, apellidop, estatus, id_departamento, fecha_ingreso)
departamento(id, nombre)
credito(id, id_cliente, estatus, saldo_capital, fecha_alta)
pago(id, id_credito, monto, fecha_pago, estatus)
asignacion_credito(id, id_credito, id_gestor, estatus, fecha_alta, fecha_baja)
```

### 4. Junior: consulta de personal activo

Escribe una consulta que muestre el nombre completo, departamento y fecha de ingreso de las personas activas. Debe incluir personas sin departamento con el texto `Sin departamento`, ordenar por departamento y despues por apellido.

### 5. Intermedio: resumen de pagos por credito

Escribe una consulta que devuelva para cada credito activo:

- ID del credito.
- Saldo de capital.
- Numero de pagos aplicados.
- Suma de pagos aplicados durante julio de 2026.
- Fecha del ultimo pago aplicado.

Incluye creditos sin pagos y no cuentes pagos cuyo estatus sea distinto de `Aplicado`.

Indica que indice(s) agregarias para que la consulta escale.

### 6. Medio-avanzado: reasignacion atomica

Diseña las sentencias SQL, dentro de una transaccion, para reasignar un credito de un gestor a otro.

Reglas:

- Solo puede existir una asignacion activa (`estatus = 'Activa'`) por credito.
- Debe cerrarse la asignacion activa actual antes de crear la nueva.
- Si el credito ya pertenece al gestor solicitado, no se deben duplicar registros.
- Debe ser segura ante dos solicitudes concurrentes.

Explica que bloqueo o restriccion de base de datos usarias y por que.

## Pruebas de codigo

### 7. Junior: normalizacion y consulta segura

Implementa un endpoint PHP que reciba `q` por GET y busque personas por nombre o numero de empleado.

Requisitos:

- `q` debe tener entre 2 y 80 caracteres despues de limpiar espacios.
- Debe usar PDO con parametros preparados.
- Debe devolver JSON con maximo 20 resultados.
- No debe exponer CURP, RFC, NSS, contrasenas ni otros datos sensibles.
- Debe devolver errores con HTTP 400 para entradas invalidas y HTTP 500 para errores inesperados.

### 8. Intermedio: paginacion y filtros permitidos

Implementa una funcion PHP que liste creditos con filtros opcionales `estatus`, `gestor_id`, `fecha_desde`, `fecha_hasta`, `page` y `per_page`.

Requisitos:

- `per_page` debe limitarse entre 1 y 100.
- La pagina minima es 1.
- La consulta debe ser parametrizada.
- El ordenamiento solo puede aceptar `id`, `fecha_alta` o `saldo_capital`, y direccion `ASC` o `DESC`.
- Devuelve `items`, `page`, `per_page` y `total`.

Explica como evitarias que `ORDER BY` sea vulnerable a inyeccion SQL.

### 9. Medio-avanzado: operacion idempotente con auditoria

Implementa el diseno de un servicio PHP para registrar un pago recibido desde una integracion externa.

Entrada:

```json
{
  "referencia_externa": "PAGO-20260722-001",
  "id_credito": 123,
  "monto": 1500.00,
  "fecha_pago": "2026-07-22"
}
```

Requisitos:

- La misma referencia no debe aplicar el pago dos veces, incluso con reintentos simultaneos.
- Debe validar que el credito exista y que el monto sea positivo.
- Debe guardar una bitacora con el resultado, sin almacenar datos sensibles innecesarios.
- Debe usar transaccion y responder de manera consistente si el pago ya existia.

Incluye el esquema o las restricciones que necesites, pseudocodigo PHP y la estrategia de manejo de errores.

## Guia de evaluacion interna

### Criterios generales

- 4 puntos: resultado correcto para los casos normales.
- 3 puntos: validaciones, errores y casos limite.
- 2 puntos: seguridad, legibilidad y mantenibilidad.
- 1 punto: pruebas, explicacion de decisiones o rendimiento.

### Respuestas esperadas

| Ejercicio | Elementos minimos esperados |
| --- | --- |
| 1 | `DateTimeImmutable`, comparacion correcta, rango inclusivo y manejo de fechas inexistentes. |
| 2 | Ordenar o conservar maxima/minima fecha por credito, ignorar o reportar eventos invalidos y no asumir el orden de entrada. |
| 3 | Agrupar por gestor y ordenar por inicio; detectar solapamientos con una pasada por grupo. |
| 4 | `LEFT JOIN`, `COALESCE`, filtro de estatus y orden determinista. |
| 5 | `LEFT JOIN` condicionado a pagos aplicados, agregaciones correctas e indice compuesto que incluya `id_credito`, `estatus` y fecha. |
| 6 | Transaccion, `SELECT ... FOR UPDATE`, indice unico o mecanismo equivalente para impedir dos activas, e idempotencia cuando ya pertenece al gestor. |
| 7 | `trim`, limites, `LIKE` parametrizado, seleccion explicita de columnas no sensibles y codigos HTTP correctos. |
| 8 | Lista blanca para campos de orden, parametros para valores, `LIMIT/OFFSET` controlados y consulta de total coherente. |
| 9 | Indice unico para `referencia_externa`, transaccion, respuesta idempotente y auditoria separada del dato de negocio. |

## Interpretacion del resultado

- 0 a 39: requiere acompanamiento intensivo en fundamentos.
- 40 a 59: perfil junior con bases funcionales.
- 60 a 74: junior fuerte o intermedio inicial.
- 75 a 90: intermedio con capacidad para trabajo autonomo en funcionalidades acotadas.
