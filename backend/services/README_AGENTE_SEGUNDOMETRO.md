# Agente Segundómetro (despliegue manual)

La carpeta **`segundometro-agent/`** está en **`.gitignore`**: no se sube al repositorio.

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
