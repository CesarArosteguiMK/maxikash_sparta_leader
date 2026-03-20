# Bucle opcional: correos automáticos “Primeros pagos — Lunes de cierre”

El envío en horario lo resuelve **`enviar_primeros_pagos_lunes.php`**, que debe ejecutarse **varias veces al día** (ventanas por slot). En la mayoría de equipos internos eso se cubre con el **agente Node** (recomendado); solo hace falta **Programador de tareas** si no podéis tener ese proceso en marcha.

## Agente Node (recomendado; sustituye al Programador de tareas)

Carpeta: **`backend/services/correos-primeros-pagos-agent/`** — proceso Node que por defecto cada **~10 min** ejecuta el mismo PHP; estado en **http://127.0.0.1:3110/**. Los **slots en CDMX** los define el PHP del cron (`America/Mexico_City`), no la hora del SO. Ver **`README.md`** allí.

**Si antes teníais una tarea cada 1–5 min** que ejecutaba `ejecutar_primeros_pagos.bat`: **desactivadla o borradla** en el Programador de tareas. Si no, duplicáis ejecuciones y volveréis a ver ventanas CMD parpadeando. Con el agente solo no hace falta esa tarea.

---

Si preferís solo PHP (sin Node), podéis usar en su lugar:

| Archivo | Rol |
|--------|-----|
| `loop_enviar_primeros_pagos_lunes.php` | Cada ~90 s ejecuta el cron de envío **solo si** el interruptor “Auto correo” está activo en la app (`PrimerosPagosAutoSwitch`). |
| `iniciar-loop-correos-primeros-pagos.bat` | Arranca el bucle (ventana visible; útil para ver logs). |
| `iniciar-loop-correos-primeros-pagos-oculto.vbs` | Arranca el `.bat` sin mostrar consola. |
| `cerrar-loop-correos-primeros-pagos.bat` | Crea un flag para que el bucle termine en el siguiente ciclo. |

**Importante:** no conviene tener **a la vez** el Programador de tareas y este bucle disparando el mismo script: podrían competir por el mismo slot. Elegid **una** forma.

**Requisitos:** PHP CLI (p. ej. `C:\xampp\php\php.exe`), mismo proyecto y que en la vista de Primeros pagos — Lunes de cierre tengáis activado el envío automático.
