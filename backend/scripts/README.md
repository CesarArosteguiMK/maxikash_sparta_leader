# Scripts auxiliares

## pdf_media.py

Inspecciona y extrae vídeos/audio embebidos de un PDF (anotaciones Movie, RichMedia, FileAttachment, archivos embebidos).

**Requisito:** Python 3 con PyMuPDF.

```bash
pip install pymupdf
```

- **Inspección (qué páginas tienen media):**
  `python pdf_media.py --inspect /ruta/al/archivo.pdf`
  Salida JSON: `{"paginasConMedia": [1, 3, 7]}`

- **Extracción:**
  `python pdf_media.py --extract /ruta/al/archivo.pdf --outdir /carpeta/salida [--page N]`
  Salida JSON: `{"archivos": [{"nombre": "media_0.mp4", "path": "..."}]}`

En Windows (XAMPP) suele usarse `python`; en Linux a veces `python3`.
