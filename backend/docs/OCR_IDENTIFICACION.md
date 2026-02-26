# OCR para documento de identificación oficial

La subida del documento **IDENTIFICACIÓN OFICIAL** (INE, Pasaporte, Residencia Temporal/Permanente) puede validarse por OCR si en el servidor está instalado **Tesseract OCR**.

## Qué hace la validación

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
