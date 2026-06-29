# 🔍 Sistema de Verificación de Documentos Mexicanos

API para verificar autenticidad de documentos oficiales (INE, Residencias INM)
usando análisis de imagen multicapa.

---

## 📁 Estructura del Proyecto

En `backend/API/` conviven:

- **Esta API (Python / FastAPI)** — verificación de documentos (p. ej. puerto **8001** según `.env`).
- **`documentacion-candidato/`** — microservicio **Node.js** para listado rápido de documentación de candidatos (puerto **3001** por defecto). Ver su [README](documentacion-candidato/README.md).
- **Arranque conjunto** — scripts en [`../services/servicios-locales/`](../services/servicios-locales/README.md) para iniciar/detener API Python local o Docker (8001), documentación candidato (3001), Segundómetro (3100) y agente de correos (3110).
- **Scripts Windows (instalar / arrancar / detener la API en 8001)** — carpeta [`launcher/`](launcher/) (`.bat`, `.ps1`, `.vbs`). Pruebas OCR sueltas: [`scripts/dev/`](scripts/dev/).

```
doc-verificacion/
├── .cursorrules                    ← Reglas para Cursor AI (LEER PRIMERO)
├── .env.example                    ← Variables de entorno (copiar como .env)
├── requirements.txt                ← Dependencias Python
├── launcher/                         ← Scripts Windows: instalar-agente, iniciar/cerrar, Docker
├── scripts/dev/                      ← Utilidades Python opcionales (OCR, verificar-calidad)
├── app/
│   ├── main.py                     ← FastAPI app + middlewares
│   ├── api/
│   │   └── routes.py               ← Endpoints REST
│   ├── core/
│   │   └── config.py               ← Configuración (Settings)
│   ├── models/
│   │   └── schemas.py              ← Pydantic schemas
│   ├── services/
│   │   ├── verificacion.py         ← ORQUESTADOR PRINCIPAL ⭐
│   │   ├── metadata_analyzer.py    ← Capa 1: Metadatos EXIF
│   │   ├── forense_analyzer.py     ← Capa 2: ELA + Moiré
│   │   ├── geometry_analyzer.py    ← Capa 3: Proporciones
│   │   ├── ocr_analyzer.py         ← Capa 4: OCR + Validación
│   │   ├── barcode_analyzer.py     ← Capa 5: QR / Barcodes
│   │   └── ml_classifier.py        ← Capa 6: ML (PENDIENTE ⚠️)
│   └── utils/
│       └── curp_validator.py       ← Validador CURP oficial
├── test_verificacion.py            ← Pruebas manuales / smoke (raíz API)
├── Dockerfile              ← Imagen autocontenida (Tesseract + RapidOCR + deps)
├── docker-compose.yml      ← Solo servicio API (sin Postgres/Redis en compose)
```

---

## 🚀 Inicio Rápido

### 1. Requisitos del Sistema

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y tesseract-ocr tesseract-ocr-spa libzbar0 libgl1-mesa-glx

# macOS
brew install tesseract tesseract-lang zbar

# Windows
# Descargar Tesseract: https://github.com/UB-Mannheim/tesseract/wiki
# Descargar ZBar: http://zbar.sourceforge.net/
```

### 2. Configuración

```bash
# Clonar o copiar el proyecto
cd doc-verificacion

# Crear entorno virtual
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate

# Instalar dependencias
pip install -r requirements.txt

# Configurar variables de entorno
cp .env.example .env
# Editar .env con tus valores
```

### 3. Levantar en local sin Docker (recomendado en el día a día)

Requisitos y un solo comando de instalación: **[REQUISITOS_API_LOCAL.md](REQUISITOS_API_LOCAL.md)**.

```bat
cd backend\API
launcher\instalar-agente.bat
launcher\iniciar-agente-oculto.vbs
```

Sin ventanas: **`launcher\iniciar-agente-oculto.vbs`** (o `.\launcher\iniciar-agente-oculto.ps1` desde PowerShell oculto). Con consola para ver errores: **`launcher\iniciar-agente.bat`**.

Equivalente en PowerShell: `.\launcher\instalar-api-consola.ps1` y luego el arranque oculto o `.\launcher\iniciar-agente.bat`. La API queda en **http://127.0.0.1:8001** (ajustable en `.env`). Documentación: **http://127.0.0.1:8001/docs**.

Para solo la línea de comandos tras tener el `venv`:

```bash
# Desde backend/API (no es obligatorio PostgreSQL ni Redis para solo verificación)
uvicorn app.main:app --reload --host 0.0.0.0 --port 8001
```

### 4. Levantar con Docker (opcional, todo incluido en imagen)

La imagen incluye Tesseract, RapidOCR, libzbar y dependencias del contenedor. En Windows, el atajo del repo es **`launcher\Iniciar-API-Verificacion-Docker.bat`**.

```bash
# Desde backend/API
cd backend/API

# Opcional: si no tienes .env, cópialo para poder ajustar MASTER_API_KEY, etc.
cp .env.example .env

# Construir y levantar (solo contenedor API)
docker compose up -d --build

# La API queda en http://localhost:8001
# Docs: http://localhost:8001/docs
# Health: http://localhost:8001/api/v1/health
```

Para solo construir la imagen y usarla en otro servidor:

```bash
docker compose build
# La imagen se puede exportar/importar o subir a un registry; en el servidor solo hace falta tener Docker y ejecutar el contenedor.
```

**Integración con Sparta Ledger (PHP):**
En `backend/config/config.ini` añade la sección `[doc_verificacion]` con `api_url` (ej. `http://127.0.0.1:8001/api/v1/verificar`) y `api_key` (mismo valor que `MASTER_API_KEY` o el default `sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key`). Al subir el documento "IDENTIFICACIÓN OFICIAL" (imagen), el controlador CapHum llamará a esta API; si el resultado es RECHAZADO, el documento no se guarda. Si la API no está disponible, se usa el fallback OCR local (Tesseract). Documentación de producto / notas internas en `public/assets/docs/` (Markdown servible vía `/assets/docs/…` si el servidor lo expone).

---

## 📡 Uso de la API

### Verificar un documento

```bash
curl -X POST http://localhost:8001/api/v1/verificar \
  -H "X-API-Key: TU_API_KEY" \
  -F "imagen=@/ruta/a/ine.jpg" \
  -F "tipo_documento=INE_NUEVA"
```

### Ejemplo con Python

```python
import httpx

with open("ine.jpg", "rb") as f:
    response = httpx.post(
        "http://localhost:8001/api/v1/verificar",
        headers={"X-API-Key": "tu_api_key"},
        files={"imagen": ("ine.jpg", f, "image/jpeg")},
        params={"tipo_documento": "INE_NUEVA"}
    )

resultado = response.json()
print(f"Score: {resultado['score_autenticidad']}/100")
print(f"Resultado: {resultado['resultado']}")
print(f"Recomendación: {resultado['recomendacion']}")
```

### Respuesta Ejemplo

```json
{
  "documento_tipo": "INE_NUEVA",
  "score_autenticidad": 87,
  "resultado": "ORIGINAL",
  "confianza": "ALTA",
  "tiempo_proceso_ms": 1240,
  "checks": {
    "metadatos": {
      "ok": true,
      "editor_detectado": null,
      "es_screenshot": false,
      "score": 0.95
    },
    "forense": {
      "ok": true,
      "ela_score": 8.3,
      "moire_detectado": false,
      "score": 0.90
    },
    "geometria": {
      "ok": true,
      "proporcion_correcta": true,
      "score": 0.85
    },
    "ocr_campos": {
      "ok": true,
      "curp": {"valor": "GOCA850612HDFMPL09", "valido": true},
      "vigencia": {"valor": 2027, "coherente": true},
      "campos_detectados": 4,
      "campos_validos": 4,
      "score": 0.88
    },
    "codigo_barras": {
      "ok": true,
      "qr_detectado": true,
      "contenido_valido": true,
      "score": 1.0
    },
    "ml_classifier": {
      "ok": true,
      "modelo_disponible": false,
      "score": 0.5
    }
  },
  "alertas_globales": [],
  "recomendacion": "Documento con alta probabilidad de ser original. Puede procesarse."
}
```

### NSS: solo número vs documento (extracción)

- **`POST /api/v1/verificar-nss`** — Solo valida un número que tú envías en JSON (`{"nss": "03239629730"}`). No subes ningún PDF; solo comprueba formato y dígito verificador.
- **`POST /api/v1/verificar-nss-documento`** — Aquí sí subes **el PDF** (vigencia de derechos IMSS, constancia de vigencia o constancia de semanas cotizadas desde imss.gob.mx). La API:
  1. Comprueba que el documento sea uno de los formatos aceptados (no tarjeta NSS).
  2. **Extrae el NSS del PDF** con OCR (Tesseract/RapidOCR).
  3. Valida formato y dígito verificador.
  4. Devuelve `nss_extraido`, `valido`, `mensaje` y, si puede, `nombre` del titular.

**Cómo probar la extracción desde el documento:**

1. Abre **http://localhost:8001/docs** (Swagger tiene "Try it out" para subir archivos).
2. Autoriza con la API Key: `sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key`.
3. Busca **POST /api/v1/verificar-nss-documento**.
4. Clic en **"Try it out"**.
5. En **documento**, clic en **"Choose File"** y selecciona un PDF de vigencia de derechos del IMSS o constancia de NSS (descargado de imss.gob.mx).
6. **Execute**. En la respuesta verás `nss_extraido`, `valido`, `mensaje` y `nombre`.

Desde PowerShell (si tienes el PDF en disco):

```powershell
$uri = "http://localhost:8001/api/v1/verificar-nss-documento"
$headers = @{ "X-API-Key" = "sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key" }
$form = @{ documento = Get-Item -Path "C:\ruta\a\tu\vigencia_derechos.pdf" }
Invoke-RestMethod -Uri $uri -Method Post -Headers $headers -Form $form
```

---

### Prioridad Alta

1. **`app/services/ml_classifier.py`** — NO EXISTE AÚN, debe crearse:
   ```python
   # Usar EfficientNet-B3 con torchvision
   # Input: image_bytes
   # Output: CheckML con probabilidades real/falso
   # Cargar modelo desde settings.ml_model_path
   # Si el archivo no existe, retornar score neutro (0.5)
   ```

2. **`app/models/database.py`** — NO EXISTE:
   ```python
   # SQLAlchemy models para:
   # - Tabla "verificaciones": id, timestamp, tipo_doc, score, resultado
   # - Tabla "api_keys": key_hash, nombre, activa, limite_diario
   # NO guardar la imagen original (LFPDPPP)
   ```

3. **`app/api/dependencies.py`** — NO EXISTE:
   ```python
   # Dependencias FastAPI:
   # - get_db(): sesión async de BD
   # - get_cache(): cliente Redis
   # - rate_limiter: verificar límites por API key
   ```

4. **Alembic** — Configurar migraciones:
   ```bash
   alembic init alembic
   # Crear migración inicial con los modelos de BD
   ```

### Prioridad Media

5. **Endpoint de estadísticas** (`GET /api/v1/estadisticas`):
   - Total verificaciones por día/semana
   - Distribución de resultados (ORIGINAL/REVISION/RECHAZADO)
   - Score promedio por tipo de documento

6. **Logging a BD**: después de cada verificación, guardar en tabla `verificaciones`

7. **Tests adicionales** en `tests/`:
   - Test de cada analyzer individualmente
   - Test con imágenes de prueba reales (fixtures)

### Prioridad Baja

8. **Webhook**: notificar resultado a URL externa cuando `resultado = RECHAZADO`
9. **Modo batch**: verificar múltiples imágenes en una sola llamada
10. **Cache de resultados**: Redis cache por hash de imagen (TTL 1 hora)

---

## 📊 Scores y Umbrales

| Rango | Resultado | Acción |
|-------|-----------|--------|
| 75-100 | ORIGINAL | Procesar automáticamente |
| 50-74 | REVISION_MANUAL | Enviar a agente humano |
| 0-49 | RECHAZADO | Bloquear, notificar |

---

## 🔐 Seguridad y Cumplimiento

- **LFPDPPP**: Las imágenes NO se almacenan. Solo el score y metadata.
- **API Keys**: Autenticación por header `X-API-Key`
- **Rate Limiting**: 30 req/min, 500 req/día por API key
- **HTTPS**: Siempre usar en producción (configurar nginx/cert)

---

## 📦 Variables de Entorno Importantes

| Variable | Descripción | Default |
|----------|-------------|---------|
| `MASTER_API_KEY` | API key principal | Recomendada (default en código para dev) |
| `DATABASE_URL` | Reservado en Settings; **no se usa** en rutas actuales | SQLite por defecto en código |
| `TESSERACT_CMD` | Ruta a tesseract | `/usr/bin/tesseract` |
| `USE_ML_CLASSIFIER` | Activar ML | `false` |
| `UMBRAL_REAL` | Score mínimo ORIGINAL | `75` |
| `UMBRAL_REVISION` | Score mínimo REVISION | `50` |

---

## 🧪 Tests

```bash
# Instalar dependencias de test
pip install pytest pytest-asyncio httpx pytest-cov

# Ejecutar tests
pytest tests/ -v

# Con cobertura
pytest tests/ -v --cov=app --cov-report=html
```
