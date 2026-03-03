# OCR para documento de identificación oficial

La subida del documento **IDENTIFICACIÓN OFICIAL** (INE, Pasaporte, Residencia Temporal/Permanente) se valida de dos formas (en orden):

1. **API de verificación de documentos** (Python, en `backend/API`): si en `backend/config/config.ini` está configurada la sección `[doc_verificacion]` con `api_url` y `api_key`, y el archivo es una **imagen** (JPG, PNG, WEBP, TIFF), se envía a la API. Si el resultado es **RECHAZADO**, el documento no se guarda. Si es **ORIGINAL** o **REVISION_MANUAL**, se acepta.
2. **Fallback OCR local (Tesseract):** si la API no está configurada, no responde o el archivo es PDF/DOC/DOCX, se usa la clase `OcrIdentidad` (Tesseract) como antes.

## Configuración de la API de verificación

En `backend/config/config.ini`:

```ini
[doc_verificacion]
api_url = "http://127.0.0.1:8000/api/v1/verificar"
api_key = "sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key"
```

- **api_url:** URL base del endpoint de verificación (sin barra final).
- **api_key:** debe coincidir con la configurada en la API Python (`master_api_key` o variable de entorno).

Para levantar la API (desde `backend/API`): ver `backend/API/README.md` (venv, `uvicorn app.main:app --host 0.0.0.0 --port 8000`, o Docker).

### Configuración en servidor (rutas/URLs)

En **servidor** (cuando la app no se abre por localhost), configura en `backend/config/config.ini`:

- **[app] base_url**  
  URL pública con la que abren la aplicación. Se usa para el **enlace de subida de documentos** que se envía al candidato.  
  Ejemplos: `https://tudominio.com`, `https://tudominio.com/sparta___SPARTA_SECRET_REDACTED__/public`.  
  Si está vacía, se usa el host de la petición (puede fallar detrás de proxy o en puertos no estándar).

- **[doc_verificacion] api_url**  
  URL completa donde corre la API de verificación (Python).  
  En servidor: `http://localhost:8000/api/v1/verificar` si la API corre en el mismo equipo, o `https://api.tudominio.com/api/v1/verificar` si está en otro servicio.

## Qué hace la validación (OCR local / Tesseract)

- **Extrae texto** del documento (imagen o primera página del PDF).
- **Detecta el tipo** de documento: INE, Pasaporte, Residencia Temporal, Residencia Temporal (acumulativa), Residencia Permanente.
- **Valida formato CURP** si se detecta uno en el texto.
- **Opcional:** si el candidato tiene CURP guardado en el sistema, se comprueba que coincida con el del documento.

Si el tipo no es uno de los permitidos o el CURP tiene formato inválido, el documento se rechaza y no se guarda.

## Requisitos en el servidor

### 1. Tesseract OCR (obligatorio para validar)

- **Windows (XAMPP):** instalar desde [GitHub - UB-Mannheim/tesseract](https://github.com/UB-Mannheim/tesseract/wiki) y añadir la carpeta `tesseract` al `PATH`, o indicar la ruta en código.
- **Linux:** `sudo apt install tesseract-ocr tesseract-ocr-spa`
- **Idioma:** se usa `spa` (español). Si hace falta: `tesseract-ocr-spa` o paquete equivalente.

Si Tesseract **no** está instalado, el documento se acepta igual y no se hace validación OCR (no se rechaza por falta de Tesseract).

### 2. PDF (opcional)

Para documentos en PDF se convierte la primera página a imagen antes de pasar a Tesseract. Una de estas opciones:

- **Imagick (recomendado):** extensión PHP `imagick` con soporte PDF.
- **Poppler:** comando `pdftoppm` en el PATH (por ejemplo paquete `poppler-utils` en Linux).

Si no hay ninguna, solo se puede validar por OCR cuando el candidato sube **imagen** (JPG/PNG); los PDF se aceptan sin validación OCR.

## Dónde está el código

- **Clase de OCR y validación:** `backend/core/OcrIdentidad.php`
- **Uso en subida:** `backend/controllers/CapHum.php`, método `subirDocumentosCandidatoProcesar`, solo para el documento tipo **IDENTIFICACIÓN OFICIAL** (archivo 5).

## Tipos de identificación permitidos

- INE (credencial para votar)
- Pasaporte
- Residencia Temporal
- Residencia Temporal (acumulativa)
- Residencia Permanente

Si el texto extraído no permite reconocer ninguno de estos tipos, el documento se rechaza con un mensaje indicando que debe ser INE, Pasaporte o Residencia y que la imagen sea legible.

## Validación de CURP con el candidato

Si en el futuro se guarda el CURP del candidato en base de datos (por ejemplo en la tabla `candidatos`), la clase ya está preparada para comparar el CURP del documento con el del candidato; solo hay que pasar los datos del candidato (incluido `curp`) al llamar a `validarDocumentoIdentidad`.
