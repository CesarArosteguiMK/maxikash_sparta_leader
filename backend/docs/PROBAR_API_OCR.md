# Cómo poner a prueba la API de verificación y el OCR

Guía para probar la API de verificación de documentos (Python) y el OCR “duro” de identificación (Tesseract + validación de campos).

---

## 1. Requisitos

- **Python 3.10+** con dependencias de `backend/API/requirements.txt`
- **Tesseract** instalado y en PATH (o `TESSERACT_CMD` en `.env` del API)
  - Windows: [Tesseract en UB-Mannheim](https://github.com/UB-Mannheim/tesseract/wiki)
- En `backend/config/config.ini` la sección `[doc_verificacion]` ya tiene:
  - `api_url = "http://127.0.0.1:8000/api/v1/verificar"`
  - `api_key = "sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key"`

---

## 2. Levantar la API

Desde la raíz del proyecto:

```bash
cd backend/API
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

En otro terminal puedes comprobar:

```bash
curl http://localhost:8000/api/v1/health
```

Documentación interactiva: **http://localhost:8000/docs**

---

## 3. Probar la API completa (todas las capas)

Con la API corriendo, envía una imagen de identificación (INE o residencia):

### PowerShell

```powershell
$apiKey = "sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key"
$rutaImagen = "C:\ruta\a\tu\ine.jpg"
curl.exe -X POST "http://127.0.0.1:8000/api/v1/verificar" `
  -H "X-API-Key: $apiKey" `
  -F "imagen=@$rutaImagen" `
  -F "tipo_documento=INE_NUEVA"
```

### Bash / Git Bash

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/verificar" \
  -H "X-API-Key: sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key" \
  -F "imagen=@/ruta/a/ine.jpg" \
  -F "tipo_documento=INE_NUEVA"
```

Tipos de documento: `INE_NUEVA`, `INE_ANTERIOR`, `RESIDENCIA_TEMPORAL`, `RESIDENCIA_PERMANENTE`, `RESIDENCIA_TEMPORAL_ACUMULATIVA`. Si omites `tipo_documento`, la API intenta auto-detectarlo.

En la respuesta verás `score_autenticidad`, `resultado` (ORIGINAL / REVISION_MANUAL / RECHAZADO) y el detalle de cada capa en `checks`, incluido `ocr_campos`.

---

## 4. Probar solo el OCR (“super duro” para identificar)

Para enfocarte en qué lee Tesseract y cómo se validan los campos (CURP, clave de elector, vigencia, etc.) **sin** levantar la API ni pasar por metadatos/forense/geometría:

### Script `probar_ocr.py`

Desde `backend/API`:

```bash
cd backend/API
python probar_ocr.py "C:\ruta\a\ine.jpg" INE_NUEVA
```

- **Primer argumento:** ruta a la imagen (JPG, PNG, etc.).
- **Segundo argumento (opcional):** tipo de documento. Si no lo pones, se usa `DESCONOCIDO` (validación genérica).

El script muestra:

1. **Texto extraído por Tesseract** (después del preprocesado): así ves si la imagen “difícil” se lee bien o qué caracteres fallan.
2. **Resultado de la validación OCR:** JSON con CURP, clave de elector, sección, vigencia (o los campos de residencia), score, alertas y si pasó o no.

Ejemplos para estresar el OCR:

- Fotos con poca luz o desenfocadas.
- INE con ángulo o reflejos.
- Recortes que cortan bordes del documento.
- Imágenes de baja resolución o muy comprimidas.

Así puedes ver en qué casos Tesseract no lee bien y qué campos fallan en la validación.

---

## 5. Probar desde la app PHP (flujo real)

1. Deja la API corriendo en el puerto 8000.
2. En **Capital Humano** → flujo de candidatos → **Subir documentos**.
3. Sube una imagen como **Identificación oficial** (INE o residencia).

Si la API está configurada en `config.ini`, el backend llamará a `/api/v1/verificar`; si el resultado es **RECHAZADO**, el documento no se guarda. Si la API no responde, se usa el fallback OCR local (Tesseract vía `OcrIdentidad`).

---

## Resumen

| Qué quieres probar | Cómo |
|--------------------|------|
| API completa (todas las capas) | Levantar API + `curl` a `/api/v1/verificar` con una imagen |
| Solo OCR y validación de campos | `python backend/API/probar_ocr.py ruta/imagen.jpg [TIPO]` |
| Flujo real en la app | API encendida + subir documento de identificación en Subir documentos |

Documentación relacionada: `backend/docs/OCR_IDENTIFICACION.md`, `backend/API/README.md`.
