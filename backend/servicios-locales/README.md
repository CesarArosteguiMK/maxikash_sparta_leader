# Servicios locales (orquestador)

Scripts para levantar o apagar **todos** los procesos auxiliares (Node + Docker) desde una sola carpeta.

**Ruta:** `backend/servicios-locales/`

## Arrancar

| Archivo | Descripcion |
|---------|-------------|
| `iniciar-todos-los-servicios.bat` | Consola con resumen; al final pide una tecla. |
| `iniciar-todos-los-servicios.ps1` | Logica (lo llama el `.bat`). |
| `iniciar-todos-los-servicios-oculto.vbs` | Sin ventana; util para acceso directo en el escritorio. |

**Qué inicia** (rutas relativas a `backend/`):

1. **3001** - API Node `API/documentacion-candidato/`
2. **3100** - Agente `services/segundometro-agent/`
3. **3110** - Agente `services/correos-primeros-pagos-agent/` (ventana visible para logs)
4. **8000** - Docker en `API/` si Docker responde; si no, aviso en consola.

**Requisitos:** Node.js; `npm install` / `instalar-agente.bat` en cada agente; Docker para la API 8000.

## Detener

| Archivo | Descripcion |
|---------|-------------|
| `cerrar-todos-los-servicios.bat` | Cierra 3001, 3100, 3110 y `docker compose down` en `API` si aplica. |
| `cerrar-todos-los-servicios.ps1` | Logica. |
| `cerrar-todos-los-servicios-oculto.vbs` | Sin ventana. |

## Notas

- Los `.bat` calculan solos la carpeta `backend` (padre de `servicios-locales`); no hace falta mover esta carpeta.
- Puertos: 3001 / 3100 / 3110 / 8000 no se solapan entre si.
- Apache/XAMPP no forman parte de estos scripts.
