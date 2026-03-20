# Envío automático — Primeros pagos (Lunes de Cierre)

## ¿Qué va en Git y qué no?

| En el proyecto (Git) | En el servidor (no va en Git) |
|----------------------|--------------------------------|
| `enviar_primeros_pagos_lunes.php` | Tarea programada de Windows o entrada `crontab` (Linux) |
| `PrimerosPagosAutoSwitch.php` | Archivo `logs/primeros_pagos_auto_switch.json` (interruptor) |
| `ejecutar_primeros_pagos.bat` (lanzador Windows) | Credenciales SMTP en `config.ini` |
| Script de instalación Windows (carpeta `windows/`) | |

**El interruptor del menú no crea la tarea en Windows.** Solo indica al script: “si me apagan, sal sin enviar”. Quien **dispara** el PHP cada pocos minutos es siempre el **sistema operativo** (Programador de tareas / cron).

### Windows — usar un `.bat` (recomendado si el programador pide un “ejecutable”)

Un archivo **`.php` no es ejecutable** solo: hace falta **`php.exe`**. Muchas herramientas solo permiten elegir un programa **`.exe` / `.bat`**.

1. En el Programador de tareas (o el software de tu jefe), **programa a ejecutar**:
   - `backend\cronjobs\ejecutar_primeros_pagos.bat`
   (ruta completa en el servidor, por ejemplo `C:\...\sparta___SPARTA_SECRET_REDACTED__\backend\cronjobs\ejecutar_primeros_pagos.bat`)
2. **Sin argumentos** (el `.bat` ya apunta al `.php` correcto).
3. **Frecuencia:** cada **1 a 5 minutos** (el script PHP decide si toca enviar según hora CDMX e interruptor).

El `.bat` intenta encontrar `php.exe` (XAMPP habitual, `PATH`). Si falla, define la variable de entorno **`PRIMEROS_PAGOS_PHP_EXE`** o edita el `.bat` y fija la ruta al final del archivo (línea comentada de ejemplo).

## ¿Por qué no se activa la tarea al encender el interruptor?

- Crear o habilitar tareas (`schtasks`) suele exigir **administrador** y no es algo que una petición web normal deba hacer (riesgo y permisos).
- En **Linux** pasaría algo parecido con `crontab`.
- Por eso la instalación de la tarea es **una sola vez**, por TI o por quien administre el servidor, no por el botón del menú.

## Windows — instalación recomendada (una vez)

1. Abrir **PowerShell como administrador**.
2. Ir a la carpeta `backend\cronjobs\windows` del proyecto.
3. Si tu PHP o la ruta del proyecto no son los de XAMPP por defecto, edita las variables al inicio de `instalar_tarea_primeros_pagos.ps1` o pásalas por parámetro (ver comentarios en el script).
4. Ejecutar:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
.\instalar_tarea_primeros_pagos.ps1
```

5. Opcional: para quitar la tarea, usar `desinstalar_tarea_primeros_pagos.ps1`.

Con la tarea creada, el PHP se ejecuta cada 5 minutos; el **interruptor** en la app sigue siendo la llave de “sí enviar / no enviar”.

## Linux — ejemplo `crontab`

```cron
*/5 * * * * /usr/bin/php /ruta/al/proyecto/backend/cronjobs/enviar_primeros_pagos_lunes.php >> /var/log/primeros_pagos_cron.log 2>&1
```

Ajustar ruta de `php` y del proyecto.

## Prueba manual

Doble clic o consola desde `backend\cronjobs` (misma carpeta que el `.bat`):

```text
ejecutar_primeros_pagos.bat --force
```

O directamente:

```text
php.exe ruta\al\proyecto\backend\cronjobs\enviar_primeros_pagos_lunes.php --force
```

`--force` ignora horario e interruptor (solo para diagnóstico).
