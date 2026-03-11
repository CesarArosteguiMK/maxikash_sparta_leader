# Cómo probar el Dictamen del Sistema

## Importante: `id_ticket` vs folio

- **`id_ticket`** es el identificador numérico en BD y en el API (ej. `101`). Scripts, `dictamen_sistema`, botón robot y `generarDictamenSistema` trabajan **siempre con `id_ticket`**.
- El **folio** (ej. `TCK-0102`) es solo etiqueta de negocio / pantalla **y no se corresponde con el id** en estos flujos. Para probar, mirá el `id_ticket` en la URL, en la consola de red o en BD; **no uses el folio como si fuera el id**.

---

## Requisitos

1. Tabla `dictamen_sistema` creada (tu SQL que ya funciona).
2. Usuario con **módulo 19** (Panel Admin).
3. Un ticket con:
   - **Creación ≥ 10-mar-2026** (si no, el botón no sale ni el API deja generar).
   - **Crédito** asignado (`id_credito` en `ticket`).
   - Dictamen en estado **enviado al gestor** (tipo + descripción llenos, luego “Enviar al gestor”).

---

## Paso 1 — Comprobar que al enviar se guarda el snapshot

1. En Panel Admin, abre un ticket que cumpla lo de arriba.
2. Deja un dictamen con tipo y descripción y pulsa **Enviar al gestor**.
3. En MySQL:

```sql
-- Sustituye ID_TICKET por el id_ticket real
SELECT * FROM dictamen_sistema WHERE id_ticket = ID_TICKET ORDER BY id DESC LIMIT 1;
```

Debes ver una fila con `gestiones_al_enviar` ≥ 0, `resultado = 'pendiente'`, `fecha_envio_dictamen` reciente.

Si no hay fila: el ticket no tiene crédito o falló el conteo de gestiones (revisa logs PHP).

---

## Dónde se guarda todo

Ver **`DICTAMEN_SISTEMA_UBICACION.md`**: tabla **`dictamen_sistema`** en la BD del proyecto; API `getDictamenSistema` / `generarDictamenSistema` por si otro módulo necesita leer o disparar la lógica.

---

## Paso 2 — Solo desarrollo: simular que ya pasaron 12 horas

El botón robot solo aparece cuando `fecha_actualizacion` del dictamen (envío) tiene **más de 12 horas**.

1. Obtén el `id` del dictamen del ticket:

```sql
SELECT id, id_ticket, estado, fecha_actualizacion
FROM dictamen
WHERE id_ticket = ID_TICKET AND estado = 'enviado_al_gestor'
ORDER BY fecha_creacion DESC LIMIT 1;
```

2. Pon la fecha de envío **hace 13 horas**. Opción rápida por consola (ticket 101 u otro):

```bash
# Un ticket concreto
c:\xampp\php\php.exe backend\docs\adelantar_plazo_dictamen.php 101

# Todos los dictámenes enviados (48 h atrás, evita líos de zona horaria)
c:\xampp\php\php.exe backend\docs\adelantar_plazo_dictamen.php all
```

O manual en SQL:

```sql
-- Sustituye ID_DICTAMEN
UPDATE dictamen
SET fecha_actualizacion = DATE_SUB(NOW(), INTERVAL 13 HOUR)
WHERE id = ID_DICTAMEN;
```

3. Opcional: alinea también `fecha_envio_dictamen` en `dictamen_sistema` (no obligatorio para el botón, pero coherente):

```sql
UPDATE dictamen_sistema
SET fecha_envio_dictamen = DATE_SUB(NOW(), INTERVAL 13 HOUR)
WHERE id_ticket = ID_TICKET;
```

4. Recarga el **Panel Admin** (F5). En la fila del ticket debería aparecer el botón **amarillo con robot** junto a Cerrar/Eliminar.

---

## Paso 3 — Generar y ver el resultado

1. Clic en el botón robot → se abre el modal.
2. Si sigue en pendiente, clic en **Generar dictamen del sistema**.
3. Resultados posibles:
   - **No visitó** — no aumentó el número de gestiones desde el envío.
   - **Visitó (campo)** — hay gestiones nuevas y alguna con coordenadas a &lt;100 m de las del dictamen y tipo campo.
   - **Gestión telefónica** / **lejos** / **sin coordenadas** — según datos reales.

El modal muestra **fecha de revisión** (CDMX) y el detalle en JSON interpretado.

---

## Probar por consola (sin abrir el navegador)

Con el `id_ticket` listo (y dictamen ya enviado si vas a generar):

```bash
# Solo ver qué hay en dictamen_sistema para ese ticket
c:\xampp\php\php.exe scripts\probar_dictamen_sistema.php ID_TICKET

# Ejecutar la misma lógica que el botón robot (generar dictamen)
c:\xampp\php\php.exe scripts\probar_dictamen_sistema.php ID_TICKET --generar
```

El script usa la misma BD que la app. Si el ticket es anterior al 10-mar-2026, `--generar` responderá con el mensaje de rechazo.

---

## Probar el API sin UI (opcional)

Con sesión iniciada (cookie) o desde el mismo navegador donde ya estás en Panel Admin:

- **Consultar** (POST JSON):

```text
POST /sabueso/getDictamenSistema
Content-Type: application/json

{"id_ticket": ID_TICKET}
```

- **Generar** (solo módulo 19):

```text
POST /sabueso/generarDictamenSistema
Content-Type: application/json

{"id_ticket": ID_TICKET}
```

Respuesta `success: true` y `datos` con `resultado` y `detalle`.

---

## Probar que tickets viejos no muestran botón

1. Usa un ticket con `fecha_creacion` **antes del 10-mar-2026** (o actualízalo solo en prueba):

```sql
-- SOLO EN AMBIENTE DE PRUEBA
UPDATE ticket SET fecha_creacion = '2026-03-01 10:00:00' WHERE id_ticket = ID_TICKET;
```

2. Aunque el dictamen esté enviado y “pasadas” 12 h, **no** debe salir el botón.
3. Si llamas `generarDictamenSistema` con ese ticket, debe responder que solo aplica desde el 10-mar-2026.

---

## Resumen rápido

| Qué probar              | Cómo |
|-------------------------|------|
| Snapshot al enviar      | Enviar dictamen → `SELECT * FROM dictamen_sistema WHERE id_ticket = …` |
| Botón visible           | `UPDATE dictamen SET fecha_actualizacion = DATE_SUB(NOW(), INTERVAL 13 HOUR) WHERE id = …` → F5 Panel Admin |
| Generar dictamen        | Clic robot → Generar → leer modal |
| Ticket viejo sin botón  | Ticket con `fecha_creacion` &lt; 2026-03-10 |

Si algo falla, revisa `error_log` de PHP por líneas `dictamen_sistema snapshot error`.

---

## Volver a tiempo “normal” después de pruebas

Si usaste `adelantar_plazo_dictamen.php` y querés que el botón robot vuelva a depender de 12 h reales:

```bash
c:\xampp\php\php.exe backend\docs\restaurar_fechas_dictamen.php
```

Eso pone `dictamen.fecha_actualizacion = NOW()` en los enviados (el reloj cuenta desde ahora).

---

## Simulaciones (ocultas en producción)

El modal tiene un bloque **Simulaciones** oculto con `d-none`. Para reactivarlo: en `sabueso_paneladmin.php` quitar `d-none` del `#dictamenSistemaSimWrap`.

---

## Simulaciones solo vista (sin tocar BD) — legacy

En el modal **Dictamen del Sistema**, en el pie hay **«Simulaciones (prueba)»**: al desplegar aparecen botones que pintan cada resultado (**No visitó**, **Visitó campo**, **Telefónico**, **Lejos**, **Sin coordenadas**) con datos ficticios. **No guarda nada**; sirve para revisar textos y diseño antes de tener casos reales.
