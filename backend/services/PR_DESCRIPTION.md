# PR: Predictor conductual + integración completa (pipeline prediccion-intencion-v2)

## Descripción breve

Añade el **BehaviorPredictionService** (predictor conductual determinístico) al pipeline de predicción y lo integra en Sabueso (analizarIA), Resumen Ubicaciones IA y salida final. Las probabilidades las calcula **solo** el motor; la IA interpreta y verifica sin inventar probabilidades; el predictor añade evento probable, confianza y ventana temporal.

## Criterios de aceptación (must pass)

1. **Todos los tests unitarios pasan** (PHPUnit).
2. **`php backend/services/ejemplo_uso_pipeline.php`** devuelve JSON con claves:
   - `predicciones_finales`, `confianza_global`, `plan_operativo`, `prediccion_conductual`, `riesgos`, `trazabilidad`, `verificacion`.
3. **`predicciones_finales`** suman 100 ±0.01.
4. **`prediccion_conductual.evidencias`** referencia al menos 1 id válido de `datosParaMotor` o `datosReales`.
5. **IAInterpretationService** y Resumen Ubicaciones IA incorporan `prediccion_conductual` respetando la regla: **IA no recalcula probabilidades**.
6. **Audit log** genera una entrada por ejecución con `hash_input_motor` y resumen de `prediccion_conductual` (evento_probable, confianza_evento, evidencias).
7. **Rama:** `feature/pipeline-prediccion-intencion-v2`. PR con este `PR_DESCRIPTION.md` y ejemplo de salida.

## Archivos nuevos / modificados

- **Nuevo:** `backend/services/BehaviorPredictionService.php` (contrato: predecirIntencionAcreditado, salida evento_probable, confianza_evento, indicadores, ventana_tiempo_estimada, explicacion_deterministica, evidencias).
- **Modificados:** `backend/controllers/Sabueso.php` (integración predictor después de verificación, cache con prediccion_conductual, audit con prediccion_conductual, json_legacy con prediccion_conductual).
- **Modificados:** `backend/services/IAInterpretationService.php` (parámetro opcional prediccion_conductual; prompt: no recalcular probabilidades).
- **Modificados:** `backend/services/IAVerificationService.php` (enriquecerConEvidenciasPredictor: validar evidencias del predictor, añadir a claims_no_soportados si no existen).
- **Nuevo:** `backend/tests/Unit/Services/BehaviorPredictionServiceTest.php` (testPredictPagoProximoRegular, testPredictIncertidumbreClienteIrregular, testDeterminismo, testSalidaContieneClavesRequeridas).
- **Modificados:** `backend/tests/Unit/Services/PipelineOutputTest.php` (incluye prediccion_conductual, testPipelineIncludesBehaviorPrediction).
- **Modificados:** `backend/tests/Unit/Services/IAVerificationServiceTest.php` (testPredictEvidenceValidation).
- **Modificados:** `backend/services/ejemplo_uso_pipeline.php` (llamada a BehaviorPredictionService, salida prediccion_conductual).
- **Modificados:** `backend/services/README.md` (contrato BehaviorPredictionService, flujo, criterios).
- **Modificados:** `backend/services/PR_DESCRIPTION.md` (este archivo).

## Ejemplo de salida (ejemplo_uso_pipeline.php)

```json
{
  "predicciones_finales": { "domicilio": 79.00, "trabajo": 18.00, "otro": 3.00 },
  "confianza_global": 0.65,
  "plan_operativo": ["Revisar mapa de ubicaciones", "Confirmar horarios con gestiones"],
  "prediccion_conductual": {
    "evento_probable": "pago_proximo",
    "confianza_evento": 72.50,
    "indicadores": {
      "intervalo_promedio_pago": 7.0,
      "desviacion_intervalos": 0.5,
      "frecuencia_gestiones": 6,
      "recencia_gps": 2
    },
    "ventana_tiempo_estimada": { "desde_horas": 24, "hasta_horas": 72 },
    "explicacion_deterministica": "Pagos cada 7 días con baja desviación; últimas gestiones positivas; GPS en zona comercial.",
    "evidencias": ["p12", "g34", "u0"]
  },
  "riesgos": ["GPS antiguo (>90d)"],
  "trazabilidad": { "motor": {...}, "interpretacion_ok": false, "verificacion_ok": false },
  "verificacion": { "veracity_score": 65, "suspected_test": false, "evidencias_validadas": [...], "claims_no_soportados": [] }
}
```

## Cómo ejecutar tests y ejemplo

```bash
composer install
php vendor/bin/phpunit --testdox
php backend/services/ejemplo_uso_pipeline.php
```

Adjuntar al PR: salida de `phpunit --testdox` y JSON de `ejemplo_uso_pipeline.php`.
