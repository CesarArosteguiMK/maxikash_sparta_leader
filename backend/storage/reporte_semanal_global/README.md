# Reporte semanal global (Sabueso)

Aquí se guardan **snapshots JSON** del reporte semanal (`reporte_semanal_global_v2_YYYY-MM-DD.json`), donde la fecha es el **lunes** de la semana analizada.

- **Primera carga**: se genera el reporte (llamadas a API de estado de cuenta) y se escribe el archivo.
- **Siguientes consultas**: se sirve **desde este archivo** (rápido, sin volver a llamar a la API para todos los créditos).
- **Volver a verificar** un crédito: actualiza la fila en el JSON y el resumen, sin regenerar todo.

Para **forzar regeneración completa** de una semana, borra el `.json` de esa fecha y vuelve a abrir el reporte.

Los `.json` están en `.gitignore` (no se suben al repo).
