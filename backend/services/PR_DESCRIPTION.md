# PR: Pipeline predicción localización e intención (3 capas)

## Descripción breve

Implementación del sistema de predicción de localización e intención en 3 capas: Motor determinístico → Interpretación IA → Verificador IA. Las probabilidades (domicilio/trabajo/otro) las calcula **solo** el motor; la IA interpreta y verifica sin inventar probabilidades.

## Puntos de aceptación

- [x] Tests unitarios: `backend/tests/Unit/Services/` (normalización, penalizaciones GPS, fallback IA, formato salida).
- [x] `php backend/services/ejemplo_uso_pipeline.php` produce JSON con claves: `predicciones_finales`, `confianza_global`, `plan_operativo`, `prediccion_intencion`, `riesgos`, `trazabilidad`, `verificacion`.
- [x] `predicciones_finales` suman 100 ±0.01.
- [x] `prediccion_intencion.evidencia` referencia al menos un id de `datosParaMotor` (u0, u1, u2 en el ejemplo).
- [x] Sabueso sigue retornando `json_legacy` (compatibilidad con el modal / Lectura IA).

## Archivos nuevos / modificados

- **Nuevos:** `backend/services/LocationScoringService.php`, `IAInterpretationService.php`, `IAVerificationService.php`, `PipelineCache.php`, `LocationAuditLogger.php`, `ejemplo_uso_pipeline.php`, `README.md`, `PR_DESCRIPTION.md`.
- **Nuevos:** `backend/storage/cache/.gitkeep`, `backend/storage/logs/.gitkeep`.
- **Nuevos:** `backend/tests/Unit/Services/LocationScoringServiceTest.php`, `IAInterpretationServiceTest.php`, `IAVerificationServiceTest.php`, `PipelineOutputTest.php`.
- **Modificados:** `backend/controllers/Sabueso.php` (orquestación pipeline, cache, audit log), `composer.json` (require-dev phpunit, autoload Services), `phpunit.xml`.

## Ejemplo de salida (ejemplo_uso_pipeline.php)

```json
{
    "predicciones_finales": { "domicilio": 75.71, "trabajo": 20.49, "otro": 3.8 },
    "confianza_global": 0.75,
    "plan_operativo": [ "Revisar mapa de ubicaciones", "Revisar historial de gestiones" ],
    "prediccion_intencion": {
        "accion": "Visita domiciliaria o revisión manual según motor",
        "evidencia": [ "u0", "u1", "u2" ],
        "nota": "Fallback desde trazabilidad del motor."
    },
    "riesgos": [],
    "trazabilidad": { "motor": {...}, "interpretacion_ok": false, "verificacion_ok": false },
    "verificacion": { "veracity_score": 75, "suspected_test": false, "evidencias_validadas": [...], "claims_no_soportados": [] }
}
```

## Cómo ejecutar tests

```bash
composer install
./vendor/bin/phpunit
```
