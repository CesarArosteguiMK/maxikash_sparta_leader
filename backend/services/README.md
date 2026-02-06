# Servicios de predicción de localización (3 capas)

Arquitectura del sistema de predicción de localización de acreditados: **determinístico**, **auditable** y **robusto frente a fallos de IA**.

## Contratos JSON (entrada/salida)

### LocationScoringService::calcularProbabilidadLocalizacion(array $datosParaMotor)

**Input `datosParaMotor`:**
```json
{
  "pagos_count": 51,
  "ubicaciones": [
    { "id": "u0", "etiqueta": "Punto de interés", "cantidad_registros": 12, "ultima_fecha": "2026-01-08T22:45:28" },
    { "id": "u1", "etiqueta": "Otro", "cantidad_registros": 6, "ultima_fecha": "2025-12-31T03:59:16" }
  ],
  "gestiones": [
    { "id": "g0", "fecha": "2026-01-27T22:03:34", "tipo": "Pago Recibido" }
  ]
}
```

**Output:**
```json
{
  "domicilio": 65.50,
  "trabajo": 25.20,
  "otro": 9.30,
  "trazabilidad": { "candidatos": [...], "scores_raw": [...], "pesos_usados": {...} },
  "motor_confidence": 72.50
}
```
Reglas: `domicilio + trabajo + otro = 100` (±0.01). Determinista, sin `rand()`.

### IAInterpretationService::interpretar(resultadoMotor, llamarLLM, contextoMinimo)

**Output:**
```json
{
  "resumen": "string",
  "acciones_recomendadas": ["string"],
  "riesgos_detectados": ["string"],
  "patrones_conductuales": ["string"],
  "prediccion_intencion": { "accion": "string", "evidencia": ["id_evidencia"], "nota": "string" }
}
```
Si falla la LLM, se devuelve fallback mínimo; `evidencia` referencia ids de candidatos del motor.

### IAVerificationService::verificar(datosReales, resultadoMotor, interpretacionIA, llamarLLM)

**Output:**
```json
{
  "veracity_score": 75,
  "suspected_test": false,
  "evidencias_validadas": [],
  "claims_no_soportados": []
}
```
Si falla la LLM: `motor_confidence < 10` → `suspected_test` true.  
`enriquecerConEvidenciasPredictor(datosReales, resultadoMotor, prediccion_conductual, verificacion)` añade a `claims_no_soportados` las evidencias del predictor que no existan en datosReales/resultadoMotor.

### BehaviorPredictionService::predecirIntencionAcreditado(resultadoMotor, datosReales, historial_temporal)

Predictor **determinístico** (sin rand()). Predice evento futuro del acreditado.

**Input `historial_temporal` (opcional):** `{ "fechas_pago": ["YYYY-MM-DD", ...], "gestiones": [...], "gps": [...] }`. Si `fechas_pago` no se pasa, se intenta extraer de gestiones (tipo contiene "Pago").

**Output:**
```json
{
  "evento_probable": "pago_proximo",
  "confianza_evento": 72.50,
  "indicadores": {
    "intervalo_promedio_pago": 7.0,
    "desviacion_intervalos": 0.5,
    "frecuencia_gestiones": 6,
    "recencia_gps": 2,
    "variabilidad_ubicacion": 2
  },
  "ventana_tiempo_estimada": { "desde_horas": 24, "hasta_horas": 72 },
  "explicacion_deterministica": "Evento: pago_proximo. Indicadores: ... Evidencias (ids): p12, g34, u0",
  "evidencias": ["p12", "g34", "u0"]
}
```
Valores posibles de `evento_probable`: `pago_proximo`, `retraso_pago`, `evasión_contacto`, `visita_domiciliaria_exitosa`, `visita_domiciliaria_fallida`, `pago_en_caja`, `cambio_ubicacion_habitual`, `insuficiente_datos`.  
Si datos insuficientes: `evento_probable: 'insuficiente_datos'`, `confianza_evento < 30`.

### SpatialAnalyticsService (analítica geoespacial, sin IA)

**Fórmula Haversine (distancias en metros):**
- `a = sin²(Δlat/2) + cos(lat1)·cos(lat2)·sin²(Δlon/2)`
- `c = 2·atan2(√a, √(1−a))`
- `d = R·c` (R = 6 371 000 m)

**Métodos:**
- `calcularDistanciasCasa(ubicacionesUsuario, domicilio)` → `[ { distancia_m, lat, lng }, ... ]`
- `ultimaUbicacionApp(eventosGPS, domicilio?)` → `{ lat, lng, timestamp, distancia_a_casa_m? }`
- `aperturasUltimosDias(eventosGPS, dias=5)` → `{ total_aperturas, aperturas_por_ubicacion, ubicaciones_distintas, resumen_por_dia }`

**Ejemplo entrada:** `ubicacionesUsuario` = `[ ['lat'=>19.43,'lng'=>-99.13], ... ]`, `domicilio` = `['lat'=>19.43,'lng'=>-99.13]`.  
**Ejemplo salida:** `distancias_a_casa: [ { distancia_m: 0, lat: 19.43, lng: -99.13 } ]`.

### TemporalPaymentsService (análisis temporal de pagos, sin IA)

**Método:** `analizarPagos(pagos)` con `pagos` = `[ ['fecha'=>'Y-m-d'], ... ]`.

**Salida:** `total_pagos`, `intervalo_promedio_dias`, `desviacion_intervalos`, `dia_mas_frecuente` (lunes..domingo), `consistencia_dia` (0..1), `patron_pago` (`regular` si CV = desviación/intervalo_promedio < 0.35, sino `irregular`).

**Fórmulas:** intervalo entre fechas consecutivas en días; desviación estándar de intervalos; día más frecuente por `date('N')`; consistencia = (cantidad en día dominante) / total_pagos.

### GestorComplianceService (cumplimiento gestor, sin IA)

**Método:** `verificarCercaniaGestor(eventosGestor, ubicacionesUsuario)`.

**Salida:** `visitas_cercanas` (distancia mínima a usuario < 100 m), `visitas_lejanas`, `porcentaje_cumplimiento`, `alertas` (ej. sin eventos gestor, posible visita simulada).

**Regla:** Haversine entre cada evento del gestor y cada ubicación del usuario; si mínima distancia ≤ 100 m → visita cercana.

### Formato final del pipeline (Sabueso / ejemplo_uso_pipeline.php)

```json
{
  "predicciones_finales": { "domicilio": 79.00, "trabajo": 18.00, "otro": 3.00 },
  "confianza_global": 0.65,
  "plan_operativo": ["Revisar mapa de ubicaciones", "Confirmar horarios con gestiones"],
  "prediccion_conductual": {
    "evento_probable": "pago_proximo",
    "confianza_evento": 72.50,
    "indicadores": { "intervalo_promedio_pago": 7.0, "desviacion_intervalos": 0.5, "frecuencia_gestiones": 6, "recencia_gps": 2 },
    "ventana_tiempo_estimada": { "desde_horas": 24, "hasta_horas": 72 },
    "explicacion_deterministica": "Pagos cada 7 días con baja desviación; últimas gestiones positivas; GPS en zona comercial.",
    "evidencias": ["p12", "g34", "u0"]
  },
  "prediccion_intencion": { "accion": "string", "evidencia": ["id"], "nota": "string" },
  "riesgos": ["GPS antiguo (>90d)"],
  "trazabilidad": {},
  "verificacion": { "veracity_score": 65, "suspected_test": false, "evidencias_validadas": [], "claims_no_soportados": [] },
  "analitica_espacial": { "distancias_a_casa": [], "ultima_apertura": {}, "aperturas_ultimos_5_dias": {} },
  "analitica_pagos": { "patron_pago": "regular", "intervalo_promedio_dias": 7, "consistencia_dia": 0.85 },
  "cumplimiento_gestor": { "porcentaje_cumplimiento": 75, "alertas": [] }
}
```
`predicciones_finales` las calcula **solo** el motor; la IA no modifica probabilidades. `prediccion_conductual` es la salida del predictor determinístico. `prediccion_intencion.evidencia` debe referenciar ids de `datosParaMotor` o del predictor. `analitica_espacial`, `analitica_pagos` y `cumplimiento_gestor` son **100% determinísticos** (sin IA); la IA solo los interpreta en resúmenes y recomendaciones.

## Arquitectura

```
┌─────────────────────────────────────────────────────────────────┐
│                     Sabueso.php (orquestador)                     │
│  prepararDatosParaMotor() → ejecutarPipelinePrediccion()          │
└─────────────────────────────────────────────────────────────────┘
                                    │
        ┌───────────────────────────┼───────────────────────────┐
        ▼                           ▼                           ▼
┌───────────────┐         ┌───────────────────┐         ┌──────────────────┐
│ CAPA 1        │         │ CAPA 2             │         │ CAPA 3           │
│ Motor         │ ──────► │ Interpretación IA  │ ──────► │ Verificador IA   │
│ determinístico│         │ (solo interpreta)  │         │ (coherencia)     │
└───────────────┘         └───────────────────┘         └──────────────────┘
        │                           │                           │
        │                           │                           │
        ▼                           ▼                           ▼
┌───────────────────────────────────────────────────────────────────┐
│ BehaviorPredictionService (después de verificación)                │
│ evento_probable, confianza_evento, indicadores, ventana, evidencias│
└───────────────────────────────────────────────────────────────────┘
        │
        │  En paralelo (determinístico, sin IA):
        ▼
┌───────────────────────────────────────────────────────────────────┐
│ SpatialAnalyticsService │ TemporalPaymentsService │ GestorCompliance │
│ distancias_a_casa,      │ intervalo_promedio,     │ visitas_cercanas, │
│ ultima_apertura,        │ patron_pago,            │ porcentaje_      │
│ aperturas_ultimos_5_dias│ consistencia_dia       │ cumplimiento     │
└───────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
                    Respuesta: predicciones_finales,
                    prediccion_conductual, confianza_global,
                    plan_operativo, riesgos, trazabilidad,
                    analitica_espacial, analitica_pagos, cumplimiento_gestor
```

### Capa 1 – LocationScoringService (motor matemático)

- **Responsabilidad:** Calcular probabilidades de localización (domicilio, trabajo, otro) **sin usar IA**.
- **Entrada:** `pagos_count`, `ubicaciones`, `gestiones` (datos crudos).
- **Salida:** `domicilio`, `trabajo`, `otro` (float, suman 1), más `trazabilidad`.
- **Reglas:** Pesos configurables (pagos, GPS, gestiones, horario), penalización por datos antiguos (>30 días, >90 días).
- **Reproducibilidad:** 100% determinístico.

### Capa 2 – IAInterpretationService (interpretación IA)

- **Responsabilidad:** Explicar resultados, patrones conductuales, acciones recomendadas, riesgos. **Nunca calcula probabilidades.**
- **Entrada:** Solo resultado del motor (probabilidades y candidatos).
- **Salida:** `resumen`, `acciones_recomendadas`, `riesgos_detectados`, `patrones_conductuales`.
- **Fallback:** Si la IA falla, se devuelve interpretación mínima a partir del motor.

### Capa 3 – IAVerificationService (verificador IA)

- **Responsabilidad:** Validar coherencia entre datos reales, motor e interpretación. Detectar simulaciones, contradicciones, afirmaciones sin evidencia. Validar evidencias del predictor (`enriquecerConEvidenciasPredictor`).
- **Entrada:** `datosReales`, `resultadoMotor`, `interpretacionIA`.
- **Salida:** `veracity_score`, `suspected_test`, `evidencias_validadas`, `claims_no_soportados`.
- **Fallback:** Verificación determinística (reglas locales) si la IA falla.

### BehaviorPredictionService (predictor conductual)

- **Responsabilidad:** Predecir evento futuro (intención/acción) del acreditado de forma **determinística** (sin rand()).
- **Entrada:** `resultadoMotor`, `datosReales`, `historial_temporal` (opcional: fechas_pago, gestiones, gps).
- **Salida:** `evento_probable`, `confianza_evento` (0..100), `indicadores`, `ventana_tiempo_estimada`, `explicacion_deterministica`, `evidencias` (ids).
- **Reglas:** Si datos insuficientes → `evento_probable: 'insuficiente_datos'`, `confianza_evento < 30`. Fórmula de confianza documentada en código (normalización de desviación, recencia GPS, frecuencia gestiones, variabilidad ubicación).

### Servicios analíticos determinísticos (sin IA)

- **SpatialAnalyticsService:** Distancias Haversine a domicilio, última apertura de app, aperturas en últimos N días. Fórmula Haversine documentada en código.
- **TemporalPaymentsService:** Intervalo promedio entre pagos, desviación, día más frecuente, consistencia (0..1), patrón regular/irregular (CV &lt; 0.35).
- **GestorComplianceService:** Visitas del gestor &lt; 100 m a ubicaciones del usuario → cumplimiento %; alertas si sin datos o posible visita simulada.

La IA **solo interpreta** estos resultados (resúmenes, riesgos, recomendaciones); no calcula distancias, frecuencias ni cumplimiento.

## Integración en Sabueso.php

1. **analizarIA()**  
   - Llama a `ejecutarPipelinePrediccion($idCredito, $idTicket)`.  
   - Responde con `json_legacy` (mismo formato que el modal / Lectura IA).  
   - Si el pipeline lanza excepción, usa `fallbackAnalizarIA()`.

2. **Flujo:**  
   `prepararDatosParaMotor` → `LocationScoringService::calcularProbabilidadLocalizacion` → `ejecutarAnaliticasDeterministicas` (SpatialAnalytics, TemporalPayments, GestorCompliance) → (cache) → `IAInterpretationService::interpretar` → preparar datos reales → `IAVerificationService::verificar` → `BehaviorPredictionService::predecirIntencionAcreditado` → `enriquecerConEvidenciasPredictor` → cache set / audit log → combinar y devolver `predicciones_finales`, `prediccion_conductual`, `analitica_espacial`, `analitica_pagos`, `cumplimiento_gestor`, `confianza_global`, `plan_operativo`, `riesgos`, `trazabilidad` y `json_legacy`.

3. **Resumen Ubicaciones IA:** `buildResultadoResumenUbicacionesLocal` incluye `analitica_espacial`, `analitica_pagos`, `cumplimiento_gestor`; `mergeResumenUbicacionesIALocal` los preserva en el JSON fusionado.

## Ejemplo de uso (desde código)

```php
// En un controlador o script
require_once __DIR__ . '/services/LocationScoringService.php';
require_once __DIR__ . '/services/IAInterpretationService.php';
require_once __DIR__ . '/services/IAVerificationService.php';

use Services\LocationScoringService;
use Services\IAInterpretationService;
use Services\IAVerificationService;

// 1) Datos (desde BD o API)
$data = [
    'pagos_count' => 51,
    'ubicaciones' => [
        ['texto' => 'Punto de interés', 'cantidad_registros' => 12, 'ultima_fecha' => '2026-01-08 22:45:28'],
        ['texto' => 'Menos frecuente', 'cantidad_registros' => 1, 'ultima_fecha' => '2025-12-30 23:22:16'],
    ],
    'gestiones' => [ /* array de gestiones con fecha_dispositivo o fecha_hora */ ],
];

// 2) Capa 1 – Motor
$motor = new LocationScoringService();
$resultadoMotor = $motor->calcularProbabilidadLocalizacion($data);
// $resultadoMotor['domicilio'], ['trabajo'], ['otro'], ['trazabilidad']

// 3) Capa 2 – Interpretación (requiere callable a Gemini)
$gemini = function ($sys, $parts, $max) {
    return (new \Controllers\Sabueso())->llamarGemini($sys, $parts, $max);
};
$interpretacion = (new IAInterpretationService())->interpretar($resultadoMotor, $gemini, "Crédito 123.");
// $interpretacion['resumen'], ['acciones_recomendadas'], ['riesgos_detectados'], ['patrones_conductuales']

// 4) Capa 3 – Verificación
$datosReales = [
    'pagos_count' => 51,
    'gps' => [ /* ... */ ],
    'gestiones' => [ /* ... */ ],
    'suspected_test' => false,
    'suspected_test_reasons' => [],
];
$verificacion = (new IAVerificationService())->verificar($datosReales, $resultadoMotor, $interpretacion, $gemini);
// $verificacion['veracity_score'], ['suspected_test'], ['evidencias_validadas'], ['claims_no_soportados']
```

## Configuración de pesos (Capa 1)

```php
$motor = new LocationScoringService([
    'weight_payments'   => 0.40,
    'weight_gps'        => 0.35,
    'weight_gestiones'  => 0.15,
    'weight_horario'    => 0.10,
    'payments_norm'     => 8.0,
    'gps_visits_norm'   => 6.0,
    'gestiones_norm'    => 8.0,
    'gps_penalty_30_days' => 0.5,
    'gps_penalty_90_days' => 0.2,
]);
```

## Ejecución de tests y ejemplo

```bash
# Instalar PHPUnit (desde la raíz del repo)
composer install

# Ejecutar tests
./vendor/bin/phpunit
# o: php vendor/bin/phpunit

# Ejemplo de pipeline (JSON con claves requeridas; no requiere API)
php backend/services/ejemplo_uso_pipeline.php
```

Tests: `backend/tests/Unit/Services/` (LocationScoringServiceTest, IAInterpretationServiceTest, IAVerificationServiceTest, PipelineOutputTest, BehaviorPredictionServiceTest, **SpatialAnalyticsServiceTest**, **TemporalPaymentsServiceTest**, **GestorComplianceServiceTest**).  
Audit: `backend/storage/logs/location_audit.log` (hash_input, resultado_motor, prediccion_conductual summary, verif_result, timestamp). Cache: 24h por hash de input; incluye `prediccion_conductual`.

## Criterios de éxito

- La IA **no inventa probabilidades**; solo las interpreta (Capa 2).
- El sistema **sigue funcionando si la IA falla** (fallbacks en Capa 2 y 3, y `fallbackAnalizarIA` en Sabueso).
- **Coherencia** entre motor, interpretación y verificación.
- **Extensible:** nuevos pesos o reglas en el motor sin tocar la IA.
- **Auditable:** `trazabilidad` incluye candidatos, scores y evidencias validadas.
