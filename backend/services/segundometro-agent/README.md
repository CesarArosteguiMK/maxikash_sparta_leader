# Agente Segundómetro

API HTTP que hace de intermediario entre **Sparta Ledger** y el servidor donde están los reportes de Segundómetro. Este agente tiene **su propia clave SSH** (.unknown) y ejecuta los comandos (listar, copiar, eliminar, descarga, monitorear) en el servidor remoto. El servidor donde corre PHP **no necesita** Plink, SSH ni permisos en carpetas; solo llamará a esta API por HTTP.

**Ahora:** se usa en modo pruebas sin tocar Sparta Ledger. Cuando todo funcione al 100%, se integrará el frontend/backend de Sparta Ledger para que llamen a esta API en lugar de ejecutar SSH desde PHP.

---

## Requisitos

- **Node.js 18+**
- **Clave SSH** del servidor de reportes (ej. `jesusssh4.unknown`) en una ruta accesible por este servicio (por ejemplo en esta carpeta o en `backend/config/ssh/`).

No hace falta tener instalado SSH, Plink ni nada en el sistema: la librería `ssh2` usa la clave directamente.

---

## Instalación (en tu máquina de pruebas)

```bash
cd backend/services/segundometro-agent
npm install
cp .env.example .env
```

Edita `.env` y ajusta al menos:

- **SSH_KEY_PATH**: ruta absoluta o relativa a esta carpeta donde está la clave `.unknown`.  
  Ejemplo en tu PC: `C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\config\ssh\jesusssh4.unknown`  
  O relativa: `../config/ssh/jesusssh4.unknown`
- **SSH_HOST**, **SSH_USER**, **REMOTE_DIR**, **MONITOREAR_SCRIPT** si difieren del ejemplo.

---

## Arrancar

```bash
npm start
```

Por defecto escucha en **http://localhost:3100**.

---

## Probar sin tocar Sparta Ledger

1. Abre en el navegador: **http://localhost:3100/test.html**
2. Usa los botones para:
   - **Health**: comprobar que el agente responde.
   - **Listar archivos**: debe devolver la misma lista que el Shell actual (hoy y ayer).
   - **Copiar**: prueba con un nombre de archivo real (o uno de la lista); opcionalmente indica destino.
   - **Eliminar**: solo para archivos con owner root (nosotros).
   - **Descargar**: descarga el .zip al equipo.
   - **Stream monitorear**: abre el stream en vivo del script (igual que el panel Monitorear).

Con **curl** (desde otra terminal):

```bash
curl http://localhost:3100/health
curl http://localhost:3100/files
curl -X POST http://localhost:3100/files/copy -d "nombre_archivo=mega_rpt_20260128_16_31_21.csv.zip"
curl -X DELETE "http://localhost:3100/files/mega_rpt_20260128_16_31_22.csv.zip"
curl -o descarga.zip "http://localhost:3100/files/mega_rpt_20260128_16_31_21.csv.zip/download"
```

---

## Endpoints (mismo contrato que usará Sparta Ledger)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | /health | Estado del agente |
| GET | /files | Lista de archivos (hoy y ayer), mismo formato JSON que PHP |
| POST | /files/copy | Body: `nombre_archivo`, opcional `nombre_destino` (si no se envía, se usa +1s) |
| DELETE | /files/:nombre | Elimina solo si owner es root |
| GET | /files/:nombre/download | Descarga el archivo (attachment) |
| GET | /stream/monitorear | SSE: stream de la salida de monitorear.sh |
| GET | /ventana-monitorear | Página HTML que consume el stream (para iframe del botón Monitorear) |
| GET | /diagnostico | Pruebas del agente: clave, conexión SSH, listar directorio (para el botón Diagnóstico) |

Si en `.env` defines **API_KEY**, todas las peticiones deben incluir el header `X-Api-Key: <valor>` (o `?api_key=<valor>`). Para pruebas puedes dejar API_KEY vacío.

---

## Integración futura con Sparta Ledger

Cuando el agente funcione al 100%:

1. En Sparta Ledger se configurará la **URL base del agente** (ej. `http://localhost:3100` o la URL del servidor donde se despliegue este agente).
2. El controlador Segundómetro (o un cliente dedicado) llamará a estos endpoints por HTTP en lugar de usar `SegundometroDAO::ejecutarSSH`, `copiarRemotoATemporal`, etc.
3. No se modificará nada de Sparta Ledger hasta que las pruebas con este agente estén OK.

---

## Dónde puede correr el agente

- **Pruebas:** en tu PC (localhost:3100), con la clave en tu carpeta del proyecto.
- **Producción:** en el **servidor donde están los reportes** (34.173.106.81) o en un jump host que ya tenga clave y acceso a ese servidor. Ahí el agente tendría la clave, SSH y permisos configurados una sola vez; el servidor Windows de Sparta Ledger solo haría HTTP a la URL del agente.
