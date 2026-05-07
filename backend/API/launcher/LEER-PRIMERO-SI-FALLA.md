# Si la API no levanta en el servidor — léeme primero

Esta carpeta `launcher\` ahora trae herramientas para ver **exactamente** por
qué la API no arranca, sin necesidad de RDP/consola interactiva. Sigue estos
pasos en este orden.

---

## 0) Si el doctor dice `ModuleNotFoundError: No module named '_socket'`

Ese error significa que el **Python portable** dentro de
`backend\API\tools\PythonPortable\` está **incompleto** (le faltan `.pyd` en
`DLLs\`). Ni `pip` ni `ensurepip` pueden funcionar porque no se puede
importar `socket`.

Solución (1-3 minutos, descarga ~25 MB desde python.org):

```
launcher\Bootstrap-Python.bat
```

Esto:

1. Descarga el Python 3.12 **embeddable amd64** oficial.
2. Lo extrae sobre `tools\PythonPortable\` (sustituyendo lo roto).
3. Habilita `import site` en el archivo `_pth` para que `pip` funcione.
4. Descarga `get-pip.py` y deja `pip` operativo.

A partir de ahí, el botón **API** del panel web (o el flujo
`Iniciar-API-Verificacion.bat`) instalará todas las dependencias normalmente.

> Desde el botón web también se ejecuta automáticamente el bootstrap si
> detecta que `_socket`/`ssl` no cargan, así que en muchos casos no hace falta
> hacer nada manual.

---

## 1) Ejecuta el DOCTOR (lo más importante)

Doble clic:

```
launcher\Diagnosticar-API.bat
```

Esto:

1. Revisa **Python** (versión, arquitectura, venv vs global).
2. Revisa **pip**.
3. Importa **uno por uno** los paquetes que la API necesita y dice cuáles
   faltan o están rotos (DLL, versión incompatible, etc.).
4. Hace un **smoke import** (`from app.main import app`). Esto reproduce el
   error real que hace que `uvicorn` muera silenciosamente en oculto.
5. Revisa **Tesseract OCR**, **VC++ Redistributable** y **zbar DLL**.
6. Comprueba **`.env`**, carpeta **`logs\`** y **puerto 8000**.
7. Te da un resumen con `[OK] / [WARN] / [ERR]` y **acciones recomendadas**.
8. Guarda log completo en `backend\API\logs\doctor-YYYYMMDD-HHMMSS.log`.

Códigos de salida:

| Código | Significado                                              |
|--------|----------------------------------------------------------|
| 0      | Todo OK, la API debe arrancar                             |
| 1      | Hay errores **bloqueantes** (lee el resumen)              |
| 2      | Solo avisos (puede arrancar pero hay algo a revisar)      |

### Auto-fix

```
launcher\Diagnosticar-API.bat /FIX
```

Aplica arreglos seguros: crea `.env`, crea `logs\`, agrega `TESSERACT_CMD`
si lo encuentra, etc.

```
launcher\Diagnosticar-API.bat /INSTALL
```

Hace lo de `/FIX` **y además** reinstala con `pip` cada paquete que faltó al
importarse (uno por uno, con log).

```
launcher\Diagnosticar-API.bat /KILL
```

Mata cualquier proceso que esté ocupando el puerto 8000 antes de seguir.

---

## 2) Si quieres ver el error EN VIVO

Doble clic:

```
launcher\iniciar-agente-foreground.bat
```

Arranca `uvicorn` en **esta misma consola**, sin ocultar nada. Si la app
revienta, verás el traceback completo en pantalla. Cuando quieras detener:
`Ctrl+C` o cierra la ventana.

> **Importante**: este modo es para depurar. Si cierras la ventana, se cierra
> la API. Para producción se sigue usando `iniciar-agente-oculto.vbs` /
> `iniciar-agente.bat`.

---

## 3) Arranque normal (oculto)

Doble clic en cualquiera de estos:

- `launcher\iniciar-agente-oculto.vbs`  → arranca **sin ventana**.
- `launcher\iniciar-agente.bat`         → arranca y cierra la consola.

A diferencia de la versión vieja, ahora estos:

- Hacen **smoke import** ANTES de lanzar uvicorn (no se queda colgado en
  silencio si falta un paquete).
- Capturan stdout y stderr de uvicorn a:
  - `backend\API\logs\uvicorn-stdout.log`
  - `backend\API\logs\uvicorn-stderr.log`
- Esperan hasta 20 s a que el puerto 8000 quede en LISTENING.
- Si no levanta, vuelcan las últimas líneas del error en
  `backend\API\logs\api_oculto_startup.log` y `iniciar-agente.bat`
  abre **automáticamente** el doctor.

Por lo tanto, si ya no funciona, abre estos archivos y mándamelos:

```
backend\API\logs\api_oculto_startup.log
backend\API\logs\uvicorn-stderr.log
backend\API\logs\doctor-*.log         (el más reciente)
```

---

## 4) Causas más comunes (y cómo se ven en el log)

### A. Paquete Python faltante o roto

Síntoma típico en `uvicorn-stderr.log` o en el doctor:

```
ModuleNotFoundError: No module named 'pytesseract'
ImportError: cannot import name 'find_loader' from 'pkgutil'
ImportError: DLL load failed while importing cv2
```

Solución:

```
launcher\Diagnosticar-API.bat /INSTALL
```

Si sigue fallando con `find_loader`, fuerza la actualización:

```
py -3 -m pip install --upgrade "pytesseract>=0.3.13"
```

### B. Python demasiado nuevo (3.13 / 3.14) sin compilador

Síntoma: `pdf417decoder`, `pyzbar`, `torch` o `pydantic-core` fallan al
compilar (`error: Microsoft Visual C++ 14.0 or greater is required`).

Solución más rápida: instalar **Python 3.12 64-bit** y ejecutar de nuevo
`instalar-agente.bat /VENV`. Sale más barato que pelear con Build Tools.

### C. Falta Visual C++ Redistributable

Síntoma: `DLL load failed while importing cv2 / pyzbar / torch`.

Solución: instalar
[`vc_redist.x64.exe`](https://aka.ms/vs/17/release/vc_redist.x64.exe).

### D. pyzbar sin `libzbar`

Síntoma: `ImportError: Unable to find zbar shared library`.

Solución portable (recomendada para este proyecto):

1. Copia estas DLLs en `backend/API/tools/zbar/bin/`:
   - `libzbar-64.dll`
   - `libiconv.dll`
2. Ejecuta `launcher\bootstrap-zbar-local.ps1` (deja lista la carpeta local).
3. Reinicia la API.

Evita depender de `C:\Windows\System32` para que el despliegue sea
autocontenido dentro de `backend/API`.

### E. Tesseract OCR no instalado

Síntoma: la API arranca, pero al verificar documentos:
`pytesseract.pytesseract.TesseractNotFoundError`.

Solución:
[Tesseract para Windows (UB-Mannheim)](https://github.com/UB-Mannheim/tesseract/wiki).
Instala en `C:\Program Files\Tesseract-OCR\`. El doctor con `/FIX`
agrega `TESSERACT_CMD` al `.env` automáticamente.

### F. Puerto 8000 ocupado por un uvicorn viejo

Síntoma: `[Errno 10048] error while attempting to bind on address`.

Solución:

```
launcher\cerrar-agente.bat
```

o, desde el doctor:

```
launcher\Diagnosticar-API.bat /KILL
```

### G. ExecutionPolicy de PowerShell bloqueando los .ps1

Síntoma: `cannot be loaded because running scripts is disabled on this system`.

Nuestros `.bat` usan `-ExecutionPolicy Bypass`, así que casi nunca pasa.
Si pasa con un `.ps1` ejecutado a mano:

```
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

### H. Antivirus / Defender

Algunos antivirus matan procesos creados con `Start-Process -WindowStyle
Hidden`. Si el doctor dice que todo está OK pero el oculto sigue cayendo,
añade exclusión para:

- La carpeta `backend\API\` completa.
- El ejecutable Python en uso (lo verás en el doctor sección 3).

---

## 5) Reinstalar desde cero (si nada funciona)

```
launcher\cerrar-agente.bat
launcher\instalar-agente.bat /VENV
launcher\Diagnosticar-API.bat
launcher\iniciar-agente-foreground.bat   (para verificar)
launcher\iniciar-agente-oculto.vbs       (para arranque normal)
```

`instalar-agente.bat` ahora guarda **todo** lo que hace pip en
`backend\API\logs\instalar-YYYYMMDD-HHMMSS.log`. Si revienta, te muestra
las últimas líneas en pantalla.
