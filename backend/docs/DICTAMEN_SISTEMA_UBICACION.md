# Dónde se guardan los registros del Dictamen del Sistema

## Base de datos

- **Esquema:** el mismo que el resto de Sabueso/tickets (p. ej. `__SPARTA_SECRET_REDACTED__`).
- **Tabla:** `dictamen_sistema`

Cada vez que se **envía un dictamen al gestor**, se inserta o actualiza una fila con:

| Columna | Contenido |
|--------|-----------|
| `id_ticket` | Ticket al que aplica |
| `id_dictamen` | Dictamen que disparó la revisión |
| `id_credito` | Crédito del ticket |
| `id_gestor` / `nombre_gestor` | Gestor evaluado |
| `gestiones_al_enviar` | Conteo de gestiones en el momento del envío |
| `gestiones_al_revisar` | Conteo al generar (después de 12 h) |
| `resultado` | `pendiente`, `no_visito`, `visito_campo`, etc. |
| `detalle` | JSON (distancias, gestiones nuevas, mensajes) |
| `fecha_envio_dictamen` | Cuándo se envió el dictamen (CDMX) |
| `fecha_revision` | Cuándo el sistema generó el dictamen (CDMX) |
| `fecha_creacion` | Alta del registro |

## Consultas útiles

```sql
-- Por ticket
SELECT * FROM dictamen_sistema WHERE id_ticket = 101 ORDER BY id DESC LIMIT 1;

-- Todos los ya revisados (no pendientes)
SELECT id_ticket, resultado, fecha_revision FROM dictamen_sistema
WHERE resultado != 'pendiente' ORDER BY fecha_revision DESC;
```

## API (para reusar en otro módulo)

- **Leer:** `POST /sabueso/getDictamenSistema` con body JSON `{ "id_ticket": 101 }`
- **Generar/ejecutar lógica:** `POST /sabueso/generarDictamenSistema` (solo módulo 19)

Los datos vienen en `resp.datos.dictamen_sistema` (y `detalle_parsed` si el backend lo adjunta).

## Código

- Modelo: `backend/models/Ticket.php` — `guardarSnapshotDictamenSistema`, `generarDictamenSistema`, `getDictamenSistema`
- Controlador: `backend/controllers/Sabueso.php` — mismos nombres de acción
