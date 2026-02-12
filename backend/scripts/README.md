# Scripts auxiliares

## diagnostico_segundometro.php

Script de diagnóstico completo para el módulo Shell Segundómetro. Verifica:
- Funciones PHP disponibles (exec, shell_exec, etc.)
- Disponibilidad del comando `ssh`
- Existencia y permisos de la clave SSH
- Conectividad con el servidor remoto
- Directorios de la aplicación
- Configuración de SegundometroDAO.php

**Ejecución:**

Desde navegador:
```
http://tu-servidor/backend/scripts/diagnostico_segundometro.php
```

Desde línea de comandos:
```bash
cd backend/scripts
php diagnostico_segundometro.php
```

El script genera un reporte completo guardado en `backend/storage/logs/diagnostico_segundometro_[timestamp].txt` con todos los problemas encontrados.

---

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

### Si el servidor no encuentra Python (mensaje "Python no encontrado")

En el servidor, si `python` o `py` no están en el PATH, indique la ruta en **`backend/config/config.ini`**:

```ini
[pdf_media]
python_path = C:\Users\admin\AppData\Local\Programs\Python\Python312\python.exe
```

En Linux:

```ini
[pdf_media]
python_path = /usr/bin/python3
```

Obtener la ruta en Windows (ejecutar en CMD o PowerShell):  
`py -3 -c "import sys; print(sys.executable)"`
