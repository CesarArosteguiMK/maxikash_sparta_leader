# Plantillas para candidatos

Copia aquí los PDFs que el candidato puede descargar:

1. **carta_no_adeudo_infonavit_fonacot.pdf**  
   Carta de no créditos INFONAVIT y FONACOT. El candidato la descarga, firma y sube si no tiene hoja de retención.

2. **solicitud_interna___SPARTA_SECRET_REDACTED__.pdf**  
   Plantilla de Solicitud Interna Maxikash **sin** campos de formulario. El candidato la descarga en blanco y la llena como quiera (a mano o en computadora).

3. **solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf**  
   Misma solicitud pero con **campos de formulario (AcroForm)**. Se usa para la opción "Llenar solicitud en línea" (abrir en el navegador). El CURP se reparte en los cuadros curp_1 a curp_18 si se rellena por código (pdftk).

Nombres exactos de archivo:
- `carta_no_adeudo_infonavit_fonacot.pdf`
- `solicitud_interna___SPARTA_SECRET_REDACTED__.pdf`
- `solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf` (para "Llenar solicitud en línea" cuando se sirve el PDF con AcroForm)

---

## Rellenar automáticamente la solicitud al descargar (AcroForm)

Para que **"Descargar solicitud (con tus datos)"** entregue un PDF con los campos del formulario ya llenos:

1. **Instalar pdftk** en el servidor:
   - Windows: [PDFtk Server](https://www.pdflabs.com/tools/pdftk-server/) y opcionalmente en `config/config.ini` sección `[pdf]` definir `pdftk_path` con la ruta al ejecutable (ej. `C:\Program Files (x86)\PDFtk Server\bin\pdftk.exe`).
   - Linux: `sudo apt install pdftk` (o el paquete equivalente).

2. **Nombres de los campos en el PDF**: el PDF con AcroForm debe tener campos con nombres que el sistema pueda mapear (p. ej. curp_1 … curp_18 para el CURP). Para ver los nombres exactos de tu PDF ejecuta en la carpeta de plantillas:
   ```bash
   pdftk "solicitud_interna___SPARTA_SECRET_REDACTED___AcroForm.pdf" dump_data_fields
   ```
   Busca las líneas `FieldName: ...` y anota los nombres.

3. **Mapeo campo PDF → dato del candidato**: en `backend/config/campos_solicitud_pdf.ini` (sección `[campos]`) se define qué campo del PDF recibe qué dato. Claves de candidato disponibles: `nombre_completo`, `nombres`, `segundo_nombre`, `apellidop`, `apellidom`, `email`, `telefono`, `puesto`, `departamento`, `fecha`.  
   Ejemplo: si en el PDF el campo se llama "Nombre Completo", añade en el .ini:
   ```ini
   Nombre Completo = nombre_completo
   ```

Si pdftk no está instalado o falla, el sistema usa el **fallback**: plantilla `solicitud_interna___SPARTA_SECRET_REDACTED__.pdf` (sin formulario) y escribe los 6 datos en posiciones fijas con mPDF, o genera un PDF simple con la lista de datos.

---

## Logo en el correo de postulación

El correo que se envía al candidato muestra un logo en la esquina superior derecha. Se usa el archivo **`public/assets/img/logo_correo.png`** (recomendado: PNG sin fondo para que no se vea mancha blanca). Si no existe, se busca `logo___SPARTA_SECRET_REDACTED__.png`, `Logotipo-Maxikash-Outline.webp` o `logo.svg`. Guarda tu logo en esa ruta.

---

## Solicitud de empleo completa (formulario con muchos campos)

Si quieres usar la **SOLICITUD DE EMPLEO MAXIKASH** (la que tiene Datos personales, Documentación, Estado de salud, Contactos de emergencia, Escolaridad, Datos económicos, etc.) y que se rellene sola al descargar:

1. **Opción A – PDF con campos de formulario (recomendado)**  
   Necesitas una versión PDF de esa solicitud donde cada recuadro sea un **campo de formulario** (AcroForm), con nombre interno (por ejemplo "nombre_completo", "curp", "rfc"). Eso se hace en Adobe Acrobat u otra herramienta que permita “Añadir campos de formulario” al PDF.  
   Si tienes ese PDF:
   - Sustituye `solicitud_interna___SPARTA_SECRET_REDACTED__.pdf` por ese archivo (o renómbralo a ese nombre).
   - En el código habría que usar una librería que rellene por nombre de campo (por ejemplo PDFtk o una librería PHP de rellenado de formularios PDF). Hoy el sistema solo escribe texto en posiciones fijas (x, y) sobre una plantilla, no por nombre de campo.

2. **Opción B – PDF solo como imagen/plantilla**  
   Si el PDF es solo una imagen o un PDF sin campos de formulario, rellenar “normal” cada recuadro implicaría definir muchas posiciones (x, y) en milímetros para cada dato, lo cual es laborioso y se desajusta si cambias el diseño del PDF.

**Comportamiento actual:**  
- Si la plantilla `solicitud_interna___SPARTA_SECRET_REDACTED__.pdf` se puede usar con mPDF (PDF 1.4), se rellenan solo 6 datos en posiciones fijas: nombre completo, correo, teléfono, puesto, departamento, fecha.  
- Si esa plantilla falla (por ejemplo PDF más nuevo), se genera un **PDF simple** con esos mismos 6 datos en lista, para que al menos el candidato tenga algo que descargar.

Para que la solicitud completa se rellene sola hace falta, en la práctica, la **Opción A** (PDF con campos de formulario) y luego añadir en el código el rellenado por nombre de campo.

---

## Ajustar posiciones del texto en la Solicitud Interna

Si al descargar la solicitud los datos no coinciden con los recuadros de tu PDF, edita en el controlador las posiciones (en milímetros):

**Archivo:** `backend/controllers/CapHum.php`  
**Método:** `descargarDocumentoCandidato`  
**Busca:** el array `$posiciones` dentro del bloque `if ($tipo === 'solicitud_interna')`.

Cada elemento tiene:
- **x**: margen izquierdo en mm (ej. 30).
- **y**: distancia desde el borde superior de la página en mm.
- **texto**: el valor que se imprime (no cambiar, viene de la BD).

Orden actual: nombre completo, email, teléfono, puesto, departamento, fecha.  
Ajusta solo `x` e `y` hasta que coincidan con tu plantilla. Ejemplo: si “Nombre” en tu PDF está más abajo, aumenta el primer `y` (p. ej. de 58 a 65).
