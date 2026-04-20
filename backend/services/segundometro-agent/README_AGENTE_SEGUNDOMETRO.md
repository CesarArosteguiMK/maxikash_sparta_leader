# Agente Segundómetro

**Repositorio:** El código del agente (`backend/services/segundometro-agent/`, salvo secretos) **sí va en Git** (`git pull` / `git push`).

**Qué no sube nunca:**

- **`keys/`** — la llave SSH (p. ej. `jesusssh4.unknown`) está ignorada en `.gitignore` del raíz. En cada PC/servidor hay que **copiar la llave a mano** a `segundometro-agent/keys/`. Ver `keys/README.md`.
- **`.env`** — ignorado en el raíz del proyecto (credenciales locales).

Para despliegue en servidor sigue siendo útil el checklist **`segundometro-agent/README_DESPLIEGUE_SERVIDOR.md`** si existe en tu copia.

### Guía rápida de uso (día a día)

En la carpeta del agente (la que se copia a mano) está **`USO.md`**: pasos cuando ya está todo instalado (arranque con `.vbs` o `.bat`, cuándo reinstalar dependencias, cómo detener).

### Scripts en `segundometro-agent/` (Windows)

| Archivo | Uso |
|--------|-----|
| `instalar-agente.bat` | **Solo cuando haga falta:** primera vez, o si cambió `package.json` / dependencias. Ejecuta `npm install`. |
| `iniciar-agente.bat` | Arranca Node **sin ventana** del proceso del agente (sigue pudiendo verse brevemente la consola del `.bat` hasta el mensaje de confirmación). |
| `iniciar-agente.ps1` | Lo llama el `.bat`; no hace falta ejecutarlo a mano. |
| `iniciar-agente-oculto.vbs` | Lanza `iniciar-agente.bat` **sin mostrar la consola** (útil si molesta la ventana negra al doble clic). |
| `cerrar-agente.ps1` | Detiene el proceso que escucha en el **puerto 3100** (no mata otros `node.exe`). Lo invocan `cerrar-agente.bat` y el `.vbs`. |
| `cerrar-agente.bat` | Cierra el agente y **muestra** en consola si había algo escuchando en 3100 o no. |
| `cerrar-agente-oculto.vbs` | Cierra el agente **sin ventana** (parar el agente). |

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

1. **En tu PC:** haz **commit** y **push** de los archivos que ya están en staging (p. ej. `Segundometro.php`, `.gitignore`, `segundometro-agent/README_AGENTE_SEGUNDOMETRO.md`).
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

Los cambios de código (`server.js`, `lib/`, `.bat`, etc.) llegan con **`git pull`** en el servidor. Tras el pull, si hace falta: **`npm install`** o `instalar-agente.bat`, y comprobar **`.env`** y **`keys/jesusssh4.unknown`** (no vienen del repo; cópielos en cada máquina).
