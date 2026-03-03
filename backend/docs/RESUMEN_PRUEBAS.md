# Resumen de ejecución de pruebas – Sparta Ledger

**Fecha:** 26 feb 2026  
**Comando general:** ejecución de todas las pruebas encontradas en el proyecto.

---

## 1. Tests Python (API verificación) – `backend/API/test_verificacion.py`

**Comando:**  
`cd backend/API; python -m pytest test_verificacion.py -v --tb=short`

**Resultado:** 13 passed, 7 warnings

### Salida completa

```
============================= test session starts =============================
platform win32 -- Python 3.14.3, pytest-9.0.2, pluggy-1.6.0
rootdir: C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\API
plugins: anyio-4.12.1, asyncio-1.3.0

test_verificacion.py::TestNSSValidator::test_nss_valido_03239629730 PASSED [  7%]
test_verificacion.py::TestNSSValidator::test_nss_invalido_digito_verificador PASSED [ 15%]
test_verificacion.py::TestNSSValidator::test_nss_vacio PASSED            [ 23%]
test_verificacion.py::TestNSSValidator::test_extraer_nss_de_pdf PASSED   [ 30%]
test_verificacion.py::TestCURPValidator::test_curp_valido PASSED         [ 38%]
test_verificacion.py::TestCURPValidator::test_curp_longitud_incorrecta PASSED [ 46%]
test_verificacion.py::TestCURPValidator::test_curp_vacio PASSED          [ 53%]
test_verificacion.py::TestCURPValidator::test_curp_extrae_datos PASSED   [ 61%]
test_verificacion.py::TestCURPValidator::test_curp_femenino PASSED      [ 69%]
test_verificacion.py::TestAPIEndpoints::test_health_check PASSED         [ 76%]
test_verificacion.py::TestAPIEndpoints::test_tipos_documento PASSED      [ 84%]
test_verificacion.py::TestAPIEndpoints::test_verificar_sin_api_key PASSED [ 92%]
test_verificacion.py::TestAPIEndpoints::test_verificar_sin_imagen PASSED [100%]

======================= 13 passed, 7 warnings in 1.07s ========================
```

### Tests incluidos

| Clase | Test | Descripción |
|-------|------|-------------|
| **TestNSSValidator** | test_nss_valido_03239629730 | NSS 03239629730 (NSS.pdf) válido |
| | test_nss_invalido_digito_verificador | NSS __SPARTA_PASSWORD_REDACTED__01 rechazado por dígito verificador |
| | test_nss_vacio | NSS vacío rechazado |
| | test_extraer_nss_de_pdf | Extrae 03239629730 desde pruebas_OCR/NSS.pdf |
| **TestCURPValidator** | test_curp_valido | CURP GOCA850612HDFMPL09 válido |
| | test_curp_longitud_incorrecta | CURP corto rechazado |
| | test_curp_vacio | CURP vacío rechazado |
| | test_curp_extrae_datos | Extrae sexo y año del CURP |
| | test_curp_femenino | CURP femenino MAGL900101MDFRMR09 |
| **TestAPIEndpoints** | test_health_check | GET /api/v1/health → 200, status ok |
| | test_tipos_documento | GET /api/v1/tipos-documento → 5 tipos |
| | test_verificar_sin_api_key | POST /verificar sin key → 403 |
| | test_verificar_sin_imagen | POST /verificar sin imagen → 422 |

### Warnings (no fallos)

- **Pydantic:** `class-based config` deprecado; usar `ConfigDict` (app/models/schemas.py, app/core/config.py).
- **FastAPI:** `example` deprecado; usar `examples` (app/main.py).
- **FastAPI:** `on_event("startup")` / `on_event("shutdown")` deprecados; usar lifespan.

---

## 2. Tests PHP – Interpretar (sin PHPUnit) – `backend/tests/run_interpretar_tests.php`

**Comando:**  
`c:\xampp\php\php.exe backend/tests/run_interpretar_tests.php`

**Resultado:** 0 OK, 4 FAIL

### Salida completa

```
FAIL testClienteSinGps: Missing key: one_line_summary
FAIL testPromesaVencida: Missing key: predictions
FAIL testBajaEficaciaGestores: Se esperaba baja eficacia de gestores
FAIL testSchemaSalida: Missing key: one_line_summary

Total: 0 OK, 4 FAIL
```

### Interpretación

- El script espera que `AnaliticaInterpretarService::interpretar()` devuelva en `data`:
  - `one_line_summary`
  - `predictions` (array)
  - y el resto del schema (sections, evidence_references, etc.).
- La implementación actual del servicio parece devolver otro formato (sin `one_line_summary`, sin `predictions` en la forma esperada), por eso fallan los asserts.
- **Acción sugerida:** alinear el contrato de salida de `interpretar()` con lo que esperan estos tests, o actualizar los tests al schema real del servicio.

---

## 3. Test endpoint interpretación – `backend/tests/test_interpretacion_endpoint.php`

**Comando (requiere servidor web y sesión):**  
`php backend/tests/test_interpretacion_endpoint.php [id_credito]`  
o con curl:  
`curl -s "http://localhost:8086/api/analitica/interpretacion?id_credito=1600" -b "PHPSESSID=..."`

**Estado:** No ejecutado en esta pasada (depende de servidor en 8086 y sesión activa).  
El script valida el schema JSON de la respuesta (success, overall_confidence 0..100, summary, sections con cliente/gestion/pagos, status, missing_data, recommended_actions).

---

## 4. PHPUnit (tests Unit)

**Configuración:** `phpunit.xml` en la raíz, tests en `backend/tests` (incluye `backend/tests/Unit/Services/*Test.php`).

**Comando intentado:**  
`c:\xampp\php\php.exe vendor/bin/phpunit`

**Resultado:** No ejecutable en este entorno: en `vendor` solo hay `composer` y `autoload.php`; no está instalado `phpunit/phpunit` (falta `composer install` con dependencias de desarrollo).

**Tests Unit que existen (para cuando PHPUnit esté instalado):**

- `IAVerificationServiceTest.php` – fallback cuando LLM falla, motor_confidence < 10, evidencias del predictor.
- `AnaliticaInterpretarServiceTest.php`
- `BehaviorPredictionServiceTest.php`
- `GestorComplianceServiceTest.php`
- `IAInterpretationServiceTest.php`
- `LocationScoringServiceTest.php`
- `PipelineOutputTest.php`
- `SpatialAnalyticsServiceTest.php`
- `TemporalPaymentsServiceTest.php`

**Para ejecutarlos:**  
`composer install` (o `composer install --dev`) en la raíz del proyecto y luego:  
`c:\xampp\php\php.exe vendor/bin/phpunit`

---

## 5. Cambio realizado en los tests Python

- Los tests de API usaban `AsyncClient(app=app, base_url="http://test")`, que en httpx reciente ya no es válido.
- Se actualizó a `httpx.ASGITransport(app=app)` y `httpx.AsyncClient(transport=..., base_url="http://testserver")` para que los 4 tests de API pasen.
- Se añadieron 4 tests de NSS (válido 03239629730, inválido, vacío, extracción desde NSS.pdf).

---

## Resumen final

| Archivo / suite | Resultado | Notas |
|-----------------|-----------|--------|
| **backend/API/test_verificacion.py** | 13 passed, 7 warnings | NSS, CURP y API OK; warnings de deprecación Pydantic/FastAPI |
| **backend/tests/run_interpretar_tests.php** | 4 FAIL | Schema de salida de `interpretar()` distinto al esperado |
| **backend/tests/test_interpretacion_endpoint.php** | No ejecutado | Requiere servidor y sesión |
| **PHPUnit (backend/tests/Unit/)** | No ejecutado | Falta `composer install` para tener phpunit |

Si quieres, el siguiente paso puede ser: (1) proponer cambios concretos en `AnaliticaInterpretarService` o en `run_interpretar_tests.php` para que los 4 tests pasen, o (2) dejar indicaciones para ejecutar PHPUnit y el test de interpretación con servidor y sesión.
