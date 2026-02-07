# PR: Modal Analítica IA con datos validados y scrim

## Resumen

- **Endpoint**: `GET /api/analitica/interpretacion?id_credito=NNN&id_ticket=0` devuelve JSON ya validado (sin texto libre de IA).
- **Modal**: Al pulsar "Analizar" se abre el modal con scrim, se hace fetch a ese endpoint y se rellena la plantilla con el schema.
- **Validaciones**: Confianza 0..100 calculada en backend; si gestión es crítica (<30%) y confianza >80% el front muestra "Análisis en revisión — datos inconsistentes" y reintenta.
- **Gestores**: Se muestra la lista completa de `sections.gestion.gestores` con: nombre, visitas_totales, visitas_fuera_rango, distancia_promedio, cumplimiento_individual.

## Cómo probar

1. **Sesión**: Iniciar sesión en la app y abrir el panel Sabueso (Rastreo).
2. **Rastreo**: Abrir un crédito con "Iniciar rastreo" (por ejemplo uno con id_credito conocido).
3. **Analizar**: Pulsar el botón "Analizar" en la caja "Análisis con IA".
4. **Modal**: Debe abrirse el modal "Analítica IA" con fondo oscuro (scrim), confianza general, resumen, secciones Cliente / Gestión / Pagos y tabla de todos los gestores.
5. **Cerrar**: Cerrar con el botón "Cerrar" o ESC; el scrim desaparece y el scroll del body se restaura.

## Endpoint

- **URL**: `GET /api/analitica/interpretacion?id_credito=NNN&id_ticket=0`
- **Requiere**: Sesión activa (login).
- **Respuesta** (schema mínimo):

```json
{
  "success": true,
  "overall_confidence": 65,
  "summary": "El cliente mantiene pagos activos. La gestión en campo es regular.",
  "sections": {
    "cliente": { "state": "positivo", "pct": 75, "text": "Domicilio confirmado. Actividad en ubicación principal." },
    "gestion": {
      "state": "deficiente",
      "pct": 45,
      "text": "El desempeño en campo es regular.",
      "worst_gestor": { "nombre": "FRANCO MONTES", "distancia_promedio": 7.7, "visitas_fuera_rango": 2, "motivo": "..." },
      "gestores": [
        { "nombre": "FRANCO MONTES", "visitas_totales": 10, "visitas_fuera_rango": 2, "distancia_promedio": 7.7, "cumplimiento_individual": 80 }
      ]
    },
    "pagos": { "state": "positivoModerado", "pct": 80, "text": "Hábito de pago activo." }
  },
  "missing_data": [],
  "recommended_actions": [{ "accion": "...", "prioridad": "alta", "justificacion": "..." }],
  "status": "ok"
}
```

- **status**: `ok` (IA + sin corrección), `fixed_by_rule` (IA con corrección por reglas), `fallback` (sin IA, solo reglas).

## Test rápido del endpoint

Desde la raíz del proyecto, con un `id_credito` de prueba (y sesión activa si se usa navegador):

```bash
# Con curl (sustituir PHPSESSID por la cookie de sesión tras iniciar sesión en el navegador)
curl -s "http://localhost/api/analitica/interpretacion?id_credito=1600&id_ticket=0" -b "PHPSESSID=TU_SESION"
```

Validar schema con el script PHP:

```bash
php backend/tests/test_interpretacion_endpoint.php 1600
```

El script comprueba: `success`, `overall_confidence` 0..100, `summary`, `sections.cliente/gestion/pagos` (state, pct, text), `sections.gestion.gestores`, `status` (ok|fixed_by_rule|fallback), `missing_data`, `recommended_actions`.

## Reglas aplicadas (backend)

- **Re-evaluación global**: Si la IA devuelve gestión crítica pero confianza >0.80, el backend ya corrige la confianza (AnaliticaInterpretarService::reevaluacionGlobalCoherencia).
- **Bloqueo en front**: Si aun así llega `gestion.pct < 30` y `overall_confidence > 80`, el front no pinta conclusiones y muestra "Análisis en revisión — datos inconsistentes. Reintentando..." y vuelve a llamar al endpoint.
- **status fallback**: Cuando no hay LLM o falla, se devuelve `status: "fallback"` y la UI muestra el badge "Análisis por reglas (sin IA)" y no conclusiones arriesgadas.

## Criterios de aceptación

- [x] Al abrir modal con id_credito de prueba, aparecen datos correctos (confidence ≤80 si gestión es crítico).
- [x] Scrim bloquea fondo y evita scroll body mientras modal abierto.
- [x] Listado de gestores con al menos una fila; para datos con FRANCO MONTES, distancia_promedio ≈ 7.7 km.
- [x] Si backend devuelve `status: fallback`, la UI lo muestra claramente (badge "Análisis por reglas (sin IA)").

## Archivos modificados/creados

- `backend/controllers/Api.php`: acción `interpretacion`, método `buildSchemaInterpretacion()`.
- `backend/controllers/Sabueso.php`: botón Analizar llama a `/api/analitica/interpretacion`, validación contradicción, render desde schema, lista de gestores; Lectura de IA soporta schema nuevo y antiguo.
- `backend/views/sabueso_paneladmin.php`: CSS scrim y modal centrado; body overflow hidden/restore en modal hijo.
- `backend/tests/test_interpretacion_endpoint.php`: script de validación del schema.
- `docs/ANALITICA_IA_PR.md`: este README.
