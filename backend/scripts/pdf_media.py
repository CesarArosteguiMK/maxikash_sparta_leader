#!/usr/bin/env python3
"""
Inspecciona o extrae medios embebidos (vídeo/audio) de un PDF.
Uso:
  python pdf_media.py --inspect /ruta/al/archivo.pdf
  python pdf_media.py --extract /ruta/al/archivo.pdf [--page N] --outdir /ruta/salida
Requiere: PyMuPDF (pip install pymupdf)
"""
import argparse
import json
import os
import sys

try:
    import fitz  # PyMuPDF
except ImportError:
    sys.stderr.write("Error: PyMuPDF no instalado. Ejecuta: pip install pymupdf\n")
    sys.exit(2)


# Tipos de anotación que pueden contener medios (PDF/PyMuPDF: FileAttachment, Sound, Movie, RichMedia; números pueden variar)
MEDIA_ANNOT_NUMS = {15, 16, 17, 18, 22, 23}
# Tamaño mínimo para considerar "medio real" (evitar placeholders/firmas vacías)
MIN_BYTES_PARA_MEDIA = 10240  # 10 KB en inspección; la extracción usa 100 KB


def _annot_type(annot):
    """Devuelve (type_num, type_name). PyMuPDF: annot.type puede ser (int, str) o int."""
    t = getattr(annot, "type", None)
    if t is None:
        return -1, ""
    if isinstance(t, (list, tuple)) and len(t) >= 2:
        return int(t[0]) if t[0] is not None else -1, str(t[1] or "")
    if isinstance(t, (list, tuple)) and len(t) >= 1:
        return int(t[0]) if t[0] is not None else -1, ""
    if isinstance(t, int):
        return t, _annot_type_name(t)
    return -1, str(t)


def _annot_type_name(t):
    return {22: "Movie", 23: "RichMedia", 17: "FileAttachment", 18: "Sound", 15: "FileAttachment", 16: "Sound"}.get(t, "")


def _annot_tiene_medio(annot, min_bytes=MIN_BYTES_PARA_MEDIA):
    """True si la anotación tiene datos de archivo embebido con tamaño >= min_bytes."""
    data = None
    if getattr(annot, "file_get", None):
        try:
            data = annot.file_get()
        except Exception:
            pass
    if data is None and getattr(annot, "get_file", None):
        try:
            data = annot.get_file()
        except Exception:
            pass
    return bool(data and len(data) >= min_bytes)


def paginas_con_media(doc):
    """Devuelve lista de números de página (1-based) que tienen vídeo/audio embebido. Escanea cada página."""
    paginas = set()
    num_paginas = len(doc)
    # 1) Archivos embebidos a nivel documento → suelen estar asociados a la primera página
    try:
        if doc.embfile_names():
            paginas.add(1)
    except Exception:
        pass
    # 2) Recorrer cada página y comprobar anotaciones de medio (y contenido extraíble)
    for i in range(num_paginas):
        page = doc[i]
        page_num_1based = i + 1
        try:
            # page.annots() puede ser generador; convertimos a lista para no consumirlo mal
            annots = list(page.annots()) if hasattr(page, "annots") else []
        except Exception:
            annots = []
        for annot in annots:
            try:
                num, name = _annot_type(annot)
                es_tipo_medio = (
                    num in MEDIA_ANNOT_NUMS
                    or "Movie" in (name or "")
                    or "RichMedia" in (name or "")
                    or "File" in (name or "")
                    or "Sound" in (name or "")
                )
                if es_tipo_medio:
                    paginas.add(page_num_1based)
                    break
                # Por si el tipo no viene bien: comprobar si tiene archivo embebido con contenido
                if _annot_tiene_medio(annot):
                    paginas.add(page_num_1based)
                    break
            except Exception:
                continue
    return sorted(paginas)


def extraer_medios(doc, outdir, pagina_filtro=None):
    """
    Extrae todos los medios embebidos a outdir.
    pagina_filtro: si es int (1-based), solo extrae medios asociados a esa página.
    Devuelve lista de dicts { "nombre": "...", "path": "ruta/completa" }.
    """
    os.makedirs(outdir, exist_ok=True)
    resultados = []
    idx = 0

    # 1) Archivos embebidos a nivel documento
    try:
        for name in doc.embfile_names():
            if pagina_filtro is not None and pagina_filtro != 1:
                continue
            try:
                data = doc.embfile_get(name)
                if not data or len(data) < 102400:
                    continue
                ext = _ext_from_name(name)
                if ext == ".bin":
                    ext = _ext_from_magic(data)
                fn = f"media_{idx}{ext}"
                path = os.path.join(outdir, fn)
                with open(path, "wb") as f:
                    f.write(data)
                resultados.append({"nombre": fn, "path": path})
                idx += 1
            except Exception:
                continue
    except Exception:
        pass

    # 2) Anotaciones por página (FileAttachment, Movie, RichMedia, Sound)
    for i in range(len(doc)):
        page_num = i + 1
        if pagina_filtro is not None and page_num != pagina_filtro:
            continue
        page = doc[i]
        try:
            for annot in page.annots():
                try:
                    num, name = _annot_type(annot)
                    if num not in MEDIA_ANNOT_NUMS and "Movie" not in name and "RichMedia" not in name and "File" not in name and "Sound" not in name:
                        continue
                    data = None
                    fname = None
                    if hasattr(annot, "file_get"):
                        try:
                            data = annot.file_get()
                        except Exception:
                            pass
                    if data is None and hasattr(annot, "get_file"):
                        try:
                            data = annot.get_file()
                        except Exception:
                            pass
                    if hasattr(annot, "file_info") and callable(annot.file_info):
                        try:
                            info = annot.file_info()
                            if isinstance(info, dict) and "filename" in info:
                                fname = info["filename"]
                        except Exception:
                            pass
                    # Ignorar datos vacíos o muy pequeños (ej. firma/placeholder que no es video real; mínimo 100 KB)
                    if data and len(data) >= 102400:
                        ext = _ext_from_name(fname or "")
                        if ext == ".bin":
                            ext = _ext_from_magic(data)
                        fn = f"media_p{page_num}_{idx}{ext}"
                        path = os.path.join(outdir, fn)
                        with open(path, "wb") as f:
                            f.write(data)
                        resultados.append({"nombre": fn, "path": path})
                        idx += 1
                except Exception:
                    continue
        except Exception:
            continue

    return resultados


def _ext_from_name(name):
    n = (name or "").lower()
    if ".mp4" in n or n.endswith("mp4"):
        return ".mp4"
    if ".webm" in n or n.endswith("webm"):
        return ".webm"
    if ".mp3" in n or n.endswith("mp3"):
        return ".mp3"
    if ".wav" in n or n.endswith("wav"):
        return ".wav"
    if ".m4a" in n or n.endswith("m4a"):
        return ".m4a"
    if ".mov" in n or n.endswith("mov"):
        return ".mov"
    if ".avi" in n or n.endswith("avi"):
        return ".avi"
    return ".bin"


def _ext_from_magic(data):
    """Infiere la extensión por los magic bytes del archivo. Evita .bin cuando el contenido es video/audio conocido."""
    if not data or len(data) < 12:
        return ".bin"
    head = bytes(data[:min(64, len(data))])
    # MP4/M4A: ftyp en offset 4 (isom, mp41, M4V, etc.)
    if len(head) >= 8 and head[4:8] == b"ftyp":
        return ".mp4"
    # WebM/Matroska: EBML 0x1A 0x45 0xDF 0xA3
    if head[:4] == bytes([0x1A, 0x45, 0xDF, 0xA3]):
        return ".webm"
    # ID3 (MP3 con tag)
    if head[:3] == b"ID3":
        return ".mp3"
    # MP3 frame sync (sin ID3): 0xFF 0xFB o 0xFF 0xFA
    if len(head) >= 2 and head[0] == 0xFF and (head[1] & 0xE0) == 0xE0:
        return ".mp3"
    # RIFF WAVE
    if head[:4] == b"RIFF" and len(head) >= 8 and head[8:12] == b"WAVE":
        return ".wav"
    # Ogg (OggS)
    if head[:4] == b"OggS":
        return ".ogg"
    return ".bin"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--inspect", metavar="PDF", help="Ruta al PDF; imprime JSON con paginasConMedia")
    ap.add_argument("--extract", metavar="PDF", help="Ruta al PDF para extraer medios")
    ap.add_argument("--page", type=int, default=None, help="Solo extraer medios de esta página (1-based)")
    ap.add_argument("--outdir", default=None, help="Carpeta de salida para --extract")
    args = ap.parse_args()

    if args.inspect:
        path = os.path.abspath(args.inspect)
        if not os.path.isfile(path):
            sys.stderr.write("Archivo no encontrado: " + path + "\n")
            sys.exit(1)
        doc = fitz.open(path)
        try:
            paginas = paginas_con_media(doc)
            print(json.dumps({"paginasConMedia": paginas}))
        finally:
            doc.close()
        return

    if args.extract:
        path = os.path.abspath(args.extract)
        if not os.path.isfile(path):
            sys.stderr.write("Archivo no encontrado: " + path + "\n")
            sys.exit(1)
        if not args.outdir:
            sys.stderr.write("--extract requiere --outdir\n")
            sys.exit(1)
        outdir = os.path.abspath(args.outdir)
        doc = fitz.open(path)
        try:
            archivos = extraer_medios(doc, outdir, args.page)
            # Devolver rutas relativas a outdir para que PHP pueda servirlas
            out = [{"nombre": a["nombre"], "path": a["path"]} for a in archivos]
            print(json.dumps({"archivos": out}))
        finally:
            doc.close()
        return

    sys.stderr.write("Usa --inspect o --extract\n")
    sys.exit(1)


if __name__ == "__main__":
    main()
