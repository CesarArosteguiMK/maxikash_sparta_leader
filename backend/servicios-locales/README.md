# Servicios locales (orquestador)

Scripts para levantar o apagar **todos** los procesos auxiliares (Node + Docker) desde una sola carpeta.

**Ruta:** `backend/servicios-locales/`

## Instalar dependencias Node (separado del arranque)

`npm install` **no** se ejecuta al abrir `iniciar-agente.bat` ni el orquestador: solo levanta el proceso.

| Archivo | Descripcion |
|---------|-------------|
| `instalar-todos-deps-node.bat` | Hace `npm install` en documentacion-candidato, segundometro-agent, correos-primeros-pagos-agent y gastos-cobranza-agent (una ventana, un solo `pause` al final). |
| `instalar-agente.bat` | En **cada** carpeta de agente: instalacion detallada; en correos tambien ayuda con `.env` desde `.env.example`. |

Primera vez en una maquina: ejecute **`instalar-todos-deps-node.bat`** o cada `instalar-agente.bat` por carpeta; luego ya puede usar solo **iniciar** / **iniciar-todos**.

## Arrancar

| Archivo | Descripcion |
|---------|-------------|
| `iniciar-todos-los-servicios.bat` | Consola con resumen; agentes se abren **minimizados** en la barra (no ocultos del todo). |
| `iniciar-todos-los-servicios.ps1` | Logica (lo llama el `.bat`). |
| `iniciar-todos-los-servicios-oculto.vbs` | **Sin ventanas:** ni la consola del orquestador ni las de agentes/Docker (usa `-SinVentanas` en el `.ps1`). Si algo falla, use el `.bat` para ver mensajes. |

**Qué inicia** (rutas relativas a `backend/`):

1. **3001** - API Node `API/documentacion-candidato/`
2. **3100** - Agente `services/segundometro-agent/`
3. **3110** - Agente `services/correos-primeros-pagos-agent/` (con el `.bat` minimizada en la barra; con el `.vbs` oculto **no** deberia verse)
4. **3120** - Agente `services/gastos-cobranza-agent/` (reporte cobranza, worker EC, carga lista negra, descargo estatus 3)
5. **8000** - Docker en `API/` si Docker responde; si no, aviso en consola.

**Requisitos:** Node.js; dependencias npm instaladas antes ^(ver seccion anterior^); Docker para la API 8000.

## Detener

| Archivo | Descripcion |
|---------|-------------|
| `cerrar-todos-los-servicios.bat` | Cierra 3001, 3100, 3110, 3120 y `docker compose down` en `API` si aplica. |
| `cerrar-todos-los-servicios.ps1` | Logica. |
| `cerrar-todos-los-servicios-oculto.vbs` | Sin ventana. |

## Notas

- Los `.bat` calculan solos la carpeta `backend` (padre de `servicios-locales`); no hace falta mover esta carpeta.
- Puertos: 3001 / 3100 / 3110 / 3120 / 8000 no se solapan entre si.
- Apache/XAMPP no forman parte de estos scripts.
