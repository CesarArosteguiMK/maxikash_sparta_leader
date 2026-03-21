# Agente Node — correos “Primeros pagos — Lunes de cierre”

Sustituye al **Programador de tareas de Windows**: un proceso **Node** queda en marcha y cada **~10 minutos** (por defecto) ejecuta el mismo PHP que el cron (`backend/cronjobs/enviar_primeros_pagos_lunes.php`). Los **horarios de envío** (07:40, 09:40, …) los calcula **solo ese PHP** con **America/Mexico_City (CDMX)**; no usa la hora del sistema operativo del servidor.

- Respeta el interruptor **“Auto horario”** en la app (archivo `backend/cronjobs/logs/primeros_pagos_auto_switch.json`). Si está apagado, no lanza PHP (ahorra CPU).
- **No** sustituye a Apache: solo llama a `php.exe` en la misma PC donde corre el agente.

## Pasos mínimos (resumen)

1. **Una vez:** doble clic en **`instalar-agente.bat`** (crea `.env` desde `.env.example` si no existe y ejecuta `npm install`).
2. En **Sparta** (usuario admin): en *Primeros pagos → Lunes de cierre*, activar **Auto horario**.
3. **Cada día que deba mandar correos:** doble clic en **`iniciar-agente-oculto.vbs`** (o el `.bat`).
4. En la misma pantalla verás el badge **“Agente: en línea”** si PHP puede hablar con **http://127.0.0.1:3110** (puerto del agente). Si dice **fuera de línea**, el proceso Node no está corriendo o el puerto cambió (`.env` / `config.ini`).

## Requisitos

- **Node.js** 18+ y **PHP** (CLI), p. ej. `C:\xampp\php\php.exe`.
- En `backend/config/config.ini` debe existir `[correos_primeros_pagos_agent]` con `url` al puerto del agente (por defecto `http://127.0.0.1:3110`) para que la **UI** pueda mostrar el estado.

## Instalación (una vez por carpeta)

Doble clic en **`instalar-agente.bat`** (copia `.env` desde `.env.example` si no existe y ejecuta `npm install`).

Edita `.env` si tu `php.exe` no está en la ruta por defecto. Si ya tenías `.env` con `INTERVAL_MS=90000`, cámbialo a `600000` (10 min) o bórralo para usar el default.

## Uso diario

| Acción | Archivo |
|--------|---------|
| Arrancar (ventana visible) | `iniciar-agente.bat` |
| Arrancar (sin consola) | `iniciar-agente-oculto.vbs` |
| Detener | `cerrar-agente.bat` |

Mientras el agente corre: **http://127.0.0.1:3110/** devuelve JSON (`ok`, `intervalMs`, etc.). Puerto: `.env` → `HTTP_PORT` (`0` = sin HTTP; entonces la UI no podrá marcar “en línea”).

## No mezclar

No uses a la vez en la **misma** PC: este agente **y** el bucle PHP (`loop_enviar_primeros_pagos_lunes.php`) **y** una tarea programada que ejecute el mismo cron.

### Solo agente: quitar Programador de tareas

1. Abre **Programador de tareas** → **Biblioteca del Programador de tareas**.
2. Busca la tarea que ejecuta `ejecutar_primeros_pagos.bat` (o similar cada pocos minutos).
3. **Deshabilitar** o **Eliminar**. Con el agente en marcha y **Auto horario** activo en la app, no la necesitás.

## Otras máquinas

Copiá la carpeta (`npm install` allí), `.env` con rutas correctas y la misma sección en `config.ini` si el puerto o host cambian.
