# Servicios locales (orquestador)

Scripts para levantar o apagar **todos** los procesos auxiliares (Node + API Python en 8000, Docker opcional) desde una sola carpeta.

**Ruta:** `backend/services/servicios-locales/`

## Instalar dependencias Node (separado del arranque)

`npm install` **no** se ejecuta al abrir `iniciar-agente.bat` ni el orquestador: solo levanta el proceso.

| Archivo | Descripcion |
|---------|-------------|
| `instalar-todos-deps-node.bat` | Hace `npm install` en documentacion-candidato, segundometro-agent, correos-primeros-pagos-agent y gastos-cobranza-agent; al final ejecuta `API\launcher\instalar-agente.bat /SILENT` (pip global + `requirements.txt`). Una ventana, un solo `pause` al final. |
| `instalar-agente.bat` | En **cada** carpeta de agente: instalacion detallada; en correos tambien ayuda con `.env` desde `.env.example`. |

Primera vez en una maquina: ejecute **`instalar-todos-deps-node.bat`** o cada `instalar-agente.bat` por carpeta; luego ya puede usar solo **iniciar** / **iniciar-todos**.

## Arrancar

| Archivo | Descripcion |
|---------|-------------|
| `iniciar-todos-los-servicios.bat` | Consola con resumen; agentes se abren **minimizados** en la barra (no ocultos del todo). |
| `iniciar-todos-los-servicios.ps1` | Lógica (lo llama el `.bat`): arranca y valida puertos (3001, 3100, 3110, 3120, 8000), avisando si alguno no levantó en el tiempo esperado. |
| `iniciar-todos-los-servicios-oculto.vbs` | **Sin ventanas:** ni la consola del orquestador ni las de agentes/API (usa `-SinVentanas` en el `.ps1`). Si algo falla, use el `.bat` para ver mensajes. |

**Qué inicia** (rutas relativas a `backend/`):

1. **3001** - API Node `API/documentacion-candidato/`
2. **3100** - Agente `services/segundometro-agent/`
3. **3110** - Agente `services/correos-primeros-pagos-agent/` (con el `.bat` minimizada en la barra; con el `.vbs` oculto **no** deberia verse)
4. **3120** - Agente `services/gastos-cobranza-agent/` (reporte cobranza, worker EC, carga lista negra, descargo estatus 3)
5. **8000** - API Python en `API/` mediante `iniciar-agente-oculto.ps1` (sin ventana CMD; mismo efecto que `iniciar-agente-oculto.vbs`). Docker no es obligatorio.

**Requisitos:** Node.js; dependencias npm y Python de la API instaladas antes ^(ver seccion anterior^); Python 3.10+ y Tesseract en Windows para la API local ^(detalle en `API\REQUISITOS_API_LOCAL.md`^).

## Detener

| Archivo | Descripcion |
|---------|-------------|
| `cerrar-todos-los-servicios.bat` | Cierra 3001, 3100, 3110, 3120; libera el puerto 8000 ^(`API\launcher\cerrar-agente.ps1`^); y `docker compose down` en `API` si Docker esta activo. |
| `cerrar-todos-los-servicios.ps1` | Logica. |
| `cerrar-todos-los-servicios-oculto.vbs` | Sin ventana. |

## Notas

- Los `.bat` resuelven la carpeta `backend` como **dos niveles arriba** de esta carpeta (`services\servicios-locales`).
- Puertos: 3001 / 3100 / 3110 / 3120 / 8000 no se solapan entre si.
- Apache/XAMPP no forman parte de estos scripts.
