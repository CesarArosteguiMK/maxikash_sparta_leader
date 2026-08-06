#!/usr/bin/env python3
"""Marca cada página faltante de un PDF de demostración sin alterar el original."""

from __future__ import annotations

import argparse
import io
import os
import tempfile
from pathlib import Path

from pypdf import PdfReader, PdfWriter
from pypdf._page import PageObject
from reportlab.pdfgen import canvas


MARKER = "DATOS FICTICIOS - DOCUMENTO DE DEMOSTRACIÓN - SIN VALIDEZ"


def overlay(width: float, height: float) -> PdfReader:
    buffer = io.BytesIO()
    pdf = canvas.Canvas(buffer, pagesize=(width, height))
    pdf.setFillColorRGB(1.0, 0.94, 0.94)
    pdf.rect(0, height - 27, width, 27, fill=1, stroke=0)
    pdf.setFillColorRGB(0.75, 0.0, 0.0)
    pdf.setFont("Helvetica-Bold", 7)
    # La marca va en el margen superior: algunos contratos tienen pies pares
    # e impares con recortes diferentes que pueden ocultar contenido agregado.
    pdf.drawCentredString(width / 2, height - 18, MARKER)
    pdf.save()
    buffer.seek(0)
    return PdfReader(buffer)


def stamp(path: Path, force: bool = False) -> int:
    reader = PdfReader(str(path))
    writer = PdfWriter()
    stamped = 0
    for page in reader.pages:
        width = float(page.mediabox.width)
        height = float(page.mediabox.height)
        # Crear una página limpia aísla estados gráficos incompletos que
        # algunos PDF exportados por Word dejan en páginas pares. Sin este
        # aislamiento el texto agregado puede existir, pero quedar recortado.
        composed = PageObject.create_blank_page(width=width, height=height)
        composed.merge_page(page)
        if force or MARKER not in (page.extract_text() or ""):
            composed.merge_page(overlay(width, height).pages[0])
            stamped += 1
        writer.add_page(composed)

    descriptor, temp_name = tempfile.mkstemp(prefix=path.stem + "-", suffix=".pdf", dir=path.parent)
    os.close(descriptor)
    temp_path = Path(temp_name)
    try:
        with temp_path.open("wb") as stream:
            writer.write(stream)
        temp_path.replace(path)
    finally:
        temp_path.unlink(missing_ok=True)
    return stamped


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("pdf", type=Path)
    parser.add_argument("--force", action="store_true", help="marca todas las páginas, aunque ya exista texto similar")
    args = parser.parse_args()
    path = args.pdf.resolve()
    if not path.is_file() or path.suffix.lower() != ".pdf":
        raise SystemExit("Se requiere un archivo PDF existente.")
    count = stamp(path, force=args.force)
    print(f"{path} | páginas marcadas: {count}")


if __name__ == "__main__":
    main()
