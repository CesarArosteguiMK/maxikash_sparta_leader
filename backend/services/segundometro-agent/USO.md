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
