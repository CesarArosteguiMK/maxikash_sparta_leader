# Segundómetro Agent — Despliegue en servidor (sin Git)

Esta carpeta **no se sube al repositorio** (está en `.gitignore`). Para que funcione en el servidor hay que **copiarla a mano** (USB, ZIP interno, SCP, etc.) e instalarla en la máquina donde correrá el agente.

---

## 1. Qué copiar

| Qué | Notas |
|-----|--------|
| **Toda la carpeta** `segundometro-agent` | Incluye `server.js`, `lib/`, `public/`, `package.json` |
| **`node_modules`** | Opcional: puedes **no** copiarla y en el servidor ejecutar `npm install` |
| **`.env`** | Crear en el servidor (no compartir por correo sin cifrar) |
| **`keys/`** | Clave SSH que usa el agente (ej. `jesusssh4.unknown`) |
| **`data/`** | Opcional: si quieres conservar `auto-copy-config.json` y logs; si no, el agente la recrea |

---

## 2. Requisitos en el servidor

- **Node.js** (LTS recomendado).
- **Red saliente** si usas hora CDMX remota (worldtimeapi / timeapi.io). Si no hay internet, en `.env`:
  `ALLOW_LOCAL_TIME_FALLBACK=1` (menos preciso si el reloj del servidor está mal).
- **Acceso SSH** desde esa máquina al servidor de reportes (misma clave/rutas que ya probaste en tu PC).

---

## 3. Instalación rápida en el servidor

```bash
cd /ruta/donde/copiaste/segundometro-agent
npm install
```

Probar:

```bash
node server.js
```

Por defecto escucha en **http://127.0.0.1:3100** (o el `PORT` que pongas en `.env`).

En Windows: usar **`iniciar-agente.bat`** en la raíz del agente (doble clic). Ese script:
- busca `node.exe` en rutas fijas (sin depender del PATH),
- ejecuta `npm install` si hace falta,
- arranca `server.js`.

Solo hace falta tener **Node instalado una vez** antes; el resto lo hace el `.bat`.

---

## 4. Variables `.env` importantes

Crear `.env` en la raíz del agente (junto a `server.js`):

```env
PORT=3100

# URL que el AGENTE usa para consultar estado en BD (PHP).
# Debe ser alcanzable DESDE la máquina del agente (localhost si PHP está en el mismo servidor).
SEGUNDOMETRO_ESTADO_BD_URL=http://127.0.0.1/index.php?url=segundometro/estadoReportesAgente

# Misma clave que en backend/config/config.ini del proyecto (si configuraste segundometro_agent_key).
SEGUNDOMETRO_AGENT_KEY=tu_clave_secreta

# Si proteges el agente con API key:
# API_KEY=otra_clave

# SSH (ajustar al servidor)
# SSH_HOST=...
# SSH_USER=...
# SSH_KEY_PATH=./keys/jesusssh4.unknown
# REMOTE_DIR=/home/usuariossftp/s2/mega_reporte
# MONITOREAR_SCRIPT=/ruta/monitorear.sh

# Sin internet para hora CDMX remota:
# ALLOW_LOCAL_TIME_FALLBACK=1
```

Si `SEGUNDOMETRO_ESTADO_BD_URL` apunta mal, el Shell puede seguir mostrando estados BD porque PHP tiene **fallback a DAO**; pero el auto-copy/fallback del agente sí necesita que el agente llegue a ese endpoint.

---

## 5. Sparta Ledger en el mismo servidor

En `backend/config/config.ini` (en el servidor), sección que ya agregaste:

```ini
[segundometro_agent]
enabled = 1
url = "http://127.0.0.1:3100"
key = ""   ; si usas API_KEY en el agente, misma clave aquí
```

Si el agente corre en **otra** máquina, `url` debe ser la IP/hostname accesible **desde el PHP** (Apache) hacia el agente.

---

## 6. Dejar el agente corriendo

- **Windows**: Programador de tareas o NSSM para ejecutar `node server.js` al iniciar sesión/sistema.
- **Linux**: `systemd` unit con `Restart=always`.

Si se cierra el proceso, el Shell deja de listar/copiar vía agente hasta que lo vuelvan a levantar.

---

## 7. Checklist antes de dar por bueno

- [ ] `npm install` sin errores
- [ ] `node server.js` y en navegador `http://localhost:3100/health` → `success: true`
- [ ] Desde el servidor, el agente puede POST a `estadoReportesAgente` (misma key si aplica)
- [ ] En Sparta, menú Shell Segundómetro: listar archivos y estado BD sin error
- [ ] Monitorear abre stream (si aplica)

---

## 8. Resumen

**Sí**: la forma habitual es **copiar la carpeta en USB/ZIP**, pasarla al admin del servidor y que la pegue **fuera o dentro del árbol del proyecto** (donde tengan permisos), configurar `.env` y levantar el proceso. **No** hace falta subir esta carpeta a Git.
