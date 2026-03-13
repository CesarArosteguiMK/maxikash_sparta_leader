# Agente Segundómetro (despliegue manual)

**Importante:** La carpeta **`segundometro-agent/`** y su contenido (API Node, `.env`, `keys`, etc.) **no deben subirse al repositorio** desde ninguna máquina: ni tu PC, ni el servidor, ni otras. Está en **`.gitignore`**; no hagas `git add` de esa ruta. El Shell Segundómetro (vistas PHP en el repo) sí se versiona; el agente se despliega a mano.

Para instalarla en el servidor hay que **copiarla a mano** (USB, ZIP, SCP). Dentro de esa carpeta está el checklist completo:

**`segundometro-agent/README_DESPLIEGUE_SERVIDOR.md`**

Copia esa carpeta desde la máquina de desarrollo donde ya funciona el agente; el README viaja con la carpeta.

---

## `SEGUNDOMETRO_ESTADO_BD_URL` (evitar error HTML / no JSON)

El agente hace **POST** a PHP (`segundometro/estadoReportesAgente`). Si la URL apunta mal, la respuesta es **HTML** (`<!doctype...`) y el fallback auto-copy no dispara.

**Qué necesitas definir:** la URL **exacta** hasta `index.php` que uses para Sparta, más el query:

`?url=segundometro/estadoReportesAgente`

**Ejemplo** si entras así: `http://localhost:8086/segundometro/shell` (misma base + `index.php?url=...`):

```env
SEGUNDOMETRO_ESTADO_BD_URL=http://localhost:8086/index.php?url=segundometro/estadoReportesAgente
```

**Ejemplo servidor** `http://34.51.95.211/segundometro/shell` (agente en la misma máquina que PHP):

```env
SEGUNDOMETRO_ESTADO_BD_URL=http://127.0.0.1/index.php?url=segundometro/estadoReportesAgente
```

Si el agente está en **otra** máquina y debe llamar por red al servidor:

```env
SEGUNDOMETRO_ESTADO_BD_URL=http://34.51.95.211/index.php?url=segundometro/estadoReportesAgente
```

**Ejemplo XAMPP** con proyecto en subcarpeta `sparta___SPARTA_SECRET_REDACTED__/public`:

```env
SEGUNDOMETRO_ESTADO_BD_URL=http://localhost/sparta___SPARTA_SECRET_REDACTED__/public/index.php?url=segundometro/estadoReportesAgente
```

Pon eso en **`segundometro-agent/.env`** (junto a `server.js`) y reinicia el agente. Si en `config.ini` tienes `key` en `[segundometro_agent]`, añade también `SEGUNDOMETRO_AGENT_KEY=...` en el mismo `.env`.

### Autodetección (sin cambiar .env entre PC y servidor)

El agente **prueba varias URLs en orden** hasta que una devuelve JSON de `estadoReportesAgente`:

1. Si defines **una sola** `SEGUNDOMETRO_ESTADO_BD_URL`, se usa **primero** y si falla (HTML, timeout, etc.) sigue con:
   - `http://localhost:8086/index.php?url=segundometro/estadoReportesAgente`
   - `http://127.0.0.1:8086/...`
   - `http://localhost/...`
   - `http://127.0.0.1/...`
2. Si defines **varias separadas por coma**, solo se usan esas (en orden), sin autolist.
3. La URL que funciona se **cachea en memoria**; al arrancar verás una línea `[estado BD] URL automática: ...`.

Así en localhost (8086) y en servidor (80) suele bastar con **dejar el .env vacío** en `SEGUNDOMETRO_ESTADO_BD_URL` o con una sola URL preferida; no hace falta editar al cambiar de máquina salvo que tu PHP esté en host/puerto raros (entonces pon esa URL primera o única en el .env).

---

## Cómo recibir los cambios en el servidor (pull)

Los archivos del **Shell Segundómetro** (controlador, vistas, .gitignore, este README) **sí están en el repo**. Para que al hacer **`git pull`** se actualicen en el servidor:

1. **En tu PC:** haz **commit** y **push** de los archivos que ya están en staging (p. ej. `Segundometro.php`, `.gitignore`, `README_AGENTE_SEGUNDOMETRO.md`).
2. **En el servidor:** entra a la carpeta del proyecto y ejecuta **`git pull`**. Los archivos del repo se actualizarán donde corresponda.

Si en el servidor **`git pull`** falla por permisos (ej. "Permission denied" al escribir archivos), en **Linux** puedes dar permiso de escritura a la carpeta del repo antes del pull:

```bash
# En la raíz del proyecto (donde está .git)
chmod -R u+w .
git pull
```

En **Windows** (servidor), asegúrate de que el usuario que ejecuta Git tenga permisos de escritura sobre la carpeta del proyecto.

---

## Cómo llevar cambios a la API (agente Node)

La carpeta **`segundometro-agent/`** **no está en el repo**. Los cambios del Shell (aviso de descarga, etc.) ya llegan con **git pull** porque están en el controlador PHP. Los cambios **dentro del agente** (p. ej. `server.js`, `.env`) hay que llevarlos a mano:

- **Desde tu PC al servidor:** copia la carpeta `backend/services/segundometro-agent/` por USB, ZIP, SCP o rsync y reemplaza (o solo los archivos que cambiaron: `server.js`, `lib/`, etc.).
- No uses `git push` para el agente; está en `.gitignore` a propósito.
