# API de verificación de documentos — requisitos (sin Docker)

Uso diario recomendado: **Python local (global) + uvicorn** en el puerto **8000**. Docker sigue disponible como alternativa (`Iniciar-API-Verificacion-Docker.bat`).

## Qué instalar en Windows (manual)

1. **Python 3.10 o superior (64 bits)**
   - [python.org/downloads](https://www.python.org/downloads/)
   - Al instalar, marcar **“Add python.exe to PATH”**.
   - Con **Python 3.14**, `requirements.txt` usa rangos flexibles (`scikit-image`, `torch`, `pydantic`, etc.) para que pip baje **ruedas**; versiones fijas muy antiguas obligan a **compilar** y fallan si no tienes Visual Studio / Rust.
   - Si alguna dependencia falla en global, puede reintentar con `venv` usando `instalar-agente.bat /VENV`.

### ¿Por qué existe la opción `venv`?

Para equipos que prefieren aislamiento, las dependencias de la API (FastAPI, OpenCV, torch, etc.) se pueden dejar dentro de `backend\API\venv`, sin mezclar con el Python global. En este proyecto, el modo por defecto quedó en **global** para mantener el flujo histórico.

2. **Tesseract OCR** (no se instala con `pip`)
   - [Instalador Windows (UB Mannheim)](https://github.com/UB-Mannheim/tesseract/wiki)
   - Incluir idioma **español** si el instalador lo ofrece.
   - Sin Tesseract, el OCR de la API puede fallar o degradarse según el flujo.

3. **Opcional:** ZBar u otras dependencias nativas si el proyecto las requiere en tu entorno (ver comentarios en `requirements.txt` / README principal).

## Un solo comando: dependencias Python

Desde la carpeta `backend\API`:

| Método | Comando |
|--------|---------|
| **Batch (global, recomendado en este proyecto)** | `instalar-agente.bat` |
| **Batch global sin pausas** | `instalar-agente.bat /SILENT` |
| **Batch en venv (opcional)** | `instalar-agente.bat /VENV` |
| **PowerShell global** | `powershell -NoProfile -ExecutionPolicy Bypass -File .\instalar-api-consola.ps1` |
| **PowerShell en venv (opcional)** | `powershell -NoProfile -ExecutionPolicy Bypass -File .\instalar-api-consola.ps1 -Venv` |

Esto actualiza `pip` y ejecuta `pip install -r requirements.txt` en **global** por defecto. Si no existe `.env` y sí `.env.example`, se copia `.env`.

## Arranque

- **Sin ninguna ventana (recomendado si no quiere ver consola):** doble clic en `iniciar-agente-oculto.vbs` — llama a `iniciar-agente-oculto.ps1` (uvicorn oculto). Si algo falla, revise `logs\api_oculto_startup.log` o use el `.bat`.
- Consola visible (mensajes y pausas en error): `iniciar-agente.bat`
- Documentación interactiva: [http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs) (tras levantar el servicio)

## Detener

- `cerrar-agente.bat` o `powershell -File .\cerrar-agente.ps1` (libera el puerto **8000**).

## Instalar todo (Node + esta API)

En `backend\servicios-locales\`, **`instalar-todos-deps-node.bat`** hace `npm install` en los agentes Node y, al final, llama a `API\instalar-agente.bat /SILENT` para las dependencias Python.

## Docker (opcional)

Si prefieres contenedores (Tesseract y dependencias dentro de la imagen): `Iniciar-API-Verificacion-Docker.bat` y el flujo `docker compose` descrito en `README.md` de esta carpeta.
