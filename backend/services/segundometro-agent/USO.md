# Cómo usar el agente Segundómetro (ya instalado)

Cuando **ya ejecutaste** `instalar-agente.bat` al menos una vez y existe la carpeta `node_modules`, el día a día es solo **arrancar** el agente. PHP (Apache/XAMPP) debe estar corriendo para usar el Shell en el navegador.

---

## Uso normal (cada vez que encienden la PC o hace falta el agente)

1. **Arrancar XAMPP** (Apache; y MySQL si lo usa el proyecto).
2. **Arrancar el agente** — elegir **una** opción:
   - **Recomendado si no quieren ver ventana negra:** doble clic en **`iniciar-agente-oculto.vbs`**.
     No suele abrirse consola; el agente queda en segundo plano.
   - **Alternativa:** doble clic en **`iniciar-agente.bat`**.
     Puede verse un momento la ventana con el mensaje de confirmación y luego se cierra sola (tras unos segundos).

Para **detener** el agente sin Administrador de tareas: **`cerrar-agente-oculto.vbs`** (sin ventana). Equivale a liberar el puerto **3100** (solo ese proceso). También existen **`cerrar-agente.bat`** y **`cerrar-agente.ps1`** si quieren ver el mensaje en consola o depurar.
3. **Abrir el Shell** en el navegador (ruta típica de Sparta), por ejemplo:
   `…/segundometro/shell`
   En la interfaz debería indicar que el **agente está en línea** (puerto **3100** por defecto).

Si el agente **ya estaba** corriendo (puerto 3100 ocupado), el `.bat` lo indica y **no** lanza un segundo proceso.

### “Funcionó una vez y al volver al Shell / F5 ya está fuera de línea”

Eso casi siempre significa que el **proceso Node del agente se cerró** (el puerto **3100** ya no escucha). No es que el menú de Sparta “desconecte” al agente al navegar: el agente es un programa aparte en tu PC.

Causas frecuentes:

1. **Bug corregido en versiones recientes del agente:** la pantalla del Shell llama a `/health` y enseguida a `/auto-copy`. Si las APIs de hora en internet (`worldtimeapi` / `timeapi.io`) fallan y en `.env` no está `ALLOW_LOCAL_TIME_FALLBACK=1`, una versión anterior podía **tirar todo el proceso Node** al atender `/auto-copy`. Solución: **actualizar** `server.js` del agente o poner en `.env` del agente: `ALLOW_LOCAL_TIME_FALLBACK=1`.
2. **Cerraron la consola** donde alguien había lanzado `node server.js` a mano (al cerrar la ventana, muere el proceso).
3. **Ejecutaron** `cerrar-agente-oculto.vbs` / `cerrar-agente.bat` sin querer.
4. **Revisar logs** del agente tras arrancar con el `.bat` / `.ps1` actualizado: carpeta `data/agente-node-out.log` y `data/agente-node-err.log`.

Comprobación rápida: **Administrador de tareas** → buscar **`node.exe`** → si no hay ninguno escuchando en el puerto configurado, vuelvan a ejecutar **`iniciar-agente-oculto.vbs`**.

---

## Cuándo volver a usar `instalar-agente.bat`

**No** hace falta cada día. Solo cuando:

- Es la **primera vez** en esa carpeta, o
- Cambió **`package.json`** o alguien agregó/actualizó dependencias del agente.

Después de instalar, seguir con el paso “Uso normal” usando `iniciar-agente-oculto.vbs` o `iniciar-agente.bat`.

---

## Cómo comprobar que el agente está activo

- En el **Shell Segundómetro** debería mostrarse estado **en línea** / agente activo.
- Opcional: en el navegador o con otra herramienta, comprobar que algo escucha en **`http://127.0.0.1:3100`** (según configuración del `server.js`).

---

## Cómo detener el agente

- **Recomendado:** doble clic en **`cerrar-agente-oculto.vbs`** (no muestra ventana). Cierra el proceso que escucha en el puerto **3100**.
- **Alternativa con mensajes:** **`cerrar-agente.bat`** (llama a `cerrar-agente.ps1`).
- Si hace falta a mano: **Administrador de tareas** → **`node.exe`** del agente → **Finalizar tarea**, o **reiniciar** el equipo.

---

## Resumen rápido

| Situación | Qué ejecutar |
|-----------|----------------|
| Día a día, todo ya instalado | `iniciar-agente-oculto.vbs` **o** `iniciar-agente.bat` |
| Parar el agente | `cerrar-agente-oculto.vbs` **o** `cerrar-agente.bat` |
| Actualizaron dependencias / nueva copia del agente | `instalar-agente.bat` y luego el arranque de arriba |
| Ver errores al arrancar | Ejecutar `iniciar-agente.bat` a mano (se ven mensajes en consola) |

---

## Más detalle técnico (despliegue y URLs)

En el repositorio: `backend/services/README_AGENTE_SEGUNDOMETRO.md`.
