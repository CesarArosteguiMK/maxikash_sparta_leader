# PR: Analítica determinística (Ubicaciones, Pagos, Cumplimiento Gestor)

## Resumen

Se añaden **3 botones independientes** en la vista de detalle del acreditado (panel Sabueso / rastreo), sin tocar ni integrar con el botón de IA existente:

- **Analítica: Ubicaciones**
- **Analítica: Gestiones / Pagos**
- **Analítica: Cumplimiento Gestor**

Cada botón abre un **modal** que solicita al backend la analítica correspondiente vía **endpoints REST (JSON)**. Backend implementa servicios **determinísticos y auditables**, con cache opcional (24h) y opción **"Forzar actualización"** en cada modal.

No se usa IA/LLM para ningún cálculo; no hay referencias a "IA" en botones, modales ni etiquetas de esta funcionalidad.

---

## Cambios realizados

### Backend — Servicios

- **`backend/services/SpatialAnalyticsService.php`**  
  - `calcularDistanciasCasa(ubicacionesUsuario, domicilio)` → distancias en metros (Haversine).  
  - `ultimaUbicacionApp(eventosGPS, domicilio?, ubicacionesUsuario?)` → última apertura con `ubicacion_id` y `distancia_a_casa_m`.  
  - `aperturasUltimosDias(eventosGPS, dias, ubicacionesUsuario?, domicilio?)` → `total_aperturas`, `aperturas_por_ubicacion`, `resumen_por_dia`.  
  - `geofence_radius_m` configurable (default 100 m).

- **`backend/services/TemporalPaymentsService.php`**  
  - `analizarPagos(pagos)` → `total_pagos`, `intervalo_promedio_dias`, `desviacion_intervalos`, `dia_mas_frecuente`, `consistencia_dia`, `patron_pago` (`regular`|`irregular`|`insuficiente_datos`).  
  - Si `total_pagos < 3` → `patron_pago = 'insuficiente_datos'`.

- **`backend/services/GestorComplianceService.php`**  
  - `verificarCercaniaGestor(eventosGestor, ubicacionesUsuario, geofence_m?)` → `visitas_cercanas`, `visitas_lejanas` (enteros), `porcentaje_cumplimiento`, `detalles[]`, `alertas[]`.  
  - Eventos sin GPS o coordenadas inválidas → `alertas` y en `detalles` con `sin_gps: true`.

### Backend — API

- **`backend/controllers/Api.php`**  
  - `GET /api/analytics/{type}/{idCredito}` con `type` = `spatial` | `payments` | `compliance`.  
  - Query: `?force=true` (bypass cache), `?gestorId=...` (solo compliance).  
  - Cache en `backend/storage/cache/` (clave `analytics_{type}_{idCredito}`), TTL 24 h.  
  - Auditoría en `backend/storage/logs/location_audit.log` (JSON por línea; sin PII).

### Frontend

- **`backend/views/sabueso_paneladmin.php`**  
  - Tres botones debajo del header del rastreo: "Analítica: Ubicaciones", "Analítica: Gestiones / Pagos", "Analítica: Cumplimiento Gestor".  
  - Tres modales con título, botón "Forzar actualización" y cuerpo (tablas + resumen + "Ver más" JSON).

- **`public/assets/js/analytics-modals.js`**  
  - Abre modales, hace `GET /api/analytics/{type}/{idCredito}` (y `?force=true` al forzar).  
  - Renderiza resumen, tablas y sección JSON; usa `idCreditoRastreoActual` del contexto del rastreo.

### Tests

- **`backend/tests/Unit/Services/SpatialAnalyticsServiceTest.php`**  
  - Haversine, `calcularDistanciasCasa`, `ultimaUbicacionApp`, `aperturasUltimosDias` (incl. exclusión de eventos antiguos).

- **`backend/tests/Unit/Services/TemporalPaymentsServiceTest.php`**  
  - Patrón regular/irregular, día más frecuente, consistencia, vacío, un pago, **insuficiente_datos** con &lt; 3 pagos.

- **`backend/tests/Unit/Services/GestorComplianceServiceTest.php`**  
  - Visitas cercanas/lejanas (enteros), porcentaje cumplimiento, `detalles`, sin eventos gestor, sin ubicaciones usuario.

---

## Ejemplos de respuesta JSON

### GET /api/analytics/spatial/{idCredito}

```json
{
  "success": true,
  "data": {
    "distancias_a_casa": [
      {
        "id": "u0",
        "label": "Punto de interés",
        "distancia_m": 120.5,
        "visitas_count": 12,
        "ultima_fecha": "2026-01-08T22:45:28Z"
      }
    ],
    "ultima_apertura": {
      "lat": 19.4326,
      "lng": -99.1332,
      "timestamp": "2026-02-01T12:00:00Z",
      "distancia_a_casa_m": 50.23,
      "ubicacion_id": "u0"
    },
    "aperturas_ultimos_5_dias": {
      "total_aperturas": 4,
      "aperturas_por_ubicacion": [
        {
          "ubicacion_id": "u0",
          "label": "Casa",
          "count": 3,
          "distancia_a_casa_m": 10
        }
      ],
      "ubicaciones_distintas": 2,
      "resumen_por_dia": [
        { "fecha": "2026-02-01", "total": 1 }
      ]
    }
  },
  "cache_hit": false
}
```

### GET /api/analytics/payments/{idCredito}

```json
{
  "success": true,
  "data": {
    "total_pagos": 51,
    "intervalo_promedio_dias": 7,
    "desviacion_intervalos": 1.25,
    "dia_mas_frecuente": "lunes",
    "consistencia_dia": 0.85,
    "patron_pago": "regular"
  },
  "cache_hit": false
}
```

### GET /api/analytics/compliance/{idCredito}?gestorId=xyz

```json
{
  "success": true,
  "data": {
    "visitas_cercanas": 3,
    "visitas_lejanas": 1,
    "porcentaje_cumplimiento": 75,
    "detalles": [
      {
        "gestor_event_id": "g0",
        "timestamp": "2026-01-27T10:11:24Z",
        "distancia_m": 52.3,
        "ubicacion_id": "u0",
        "cerca": true
      }
    ],
    "alertas": []
  },
  "cache_hit": false
}
```

---

## Cómo ejecutar los tests

Desde la raíz del proyecto:

```bash
# Instalar dependencias (incl. PHPUnit) si hace falta
composer install

# Ejecutar todos los tests
./vendor/bin/phpunit

# Solo tests de servicios de analítica
./vendor/bin/phpunit backend/tests/Unit/Services/SpatialAnalyticsServiceTest.php
./vendor/bin/phpunit backend/tests/Unit/Services/TemporalPaymentsServiceTest.php
./vendor/bin/phpunit backend/tests/Unit/Services/GestorComplianceServiceTest.php
```

En Windows (PowerShell), si `php` está en el PATH:

```powershell
php vendor\bin\phpunit
php vendor\bin\phpunit backend\tests\Unit\Services\SpatialAnalyticsServiceTest.php
php vendor\bin\phpunit backend\tests\Unit\Services\TemporalPaymentsServiceTest.php
php vendor\bin\phpunit backend\tests\Unit\Services\GestorComplianceServiceTest.php
```

---

## Criterios de aceptación

- [x] Botones y modales implementados y funcionando en la vista de acreditado (rastreo).
- [x] Endpoints implementados y devuelven JSON según los contratos descritos.
- [x] Tests unitarios añadidos y pasando (tras `composer install`).
- [x] Cache 24 h y `?force=true` fuerzan recálculo.
- [x] Logs de auditoría en `backend/storage/logs/location_audit.log`.
- [x] Ninguna referencia a IA en botones, modales ni etiquetas de esta funcionalidad.
