#!/usr/bin/env python3
"""
Script para probar solo la capa OCR sobre una imagen (INE, residencia, etc.).
Útil para poner a prueba el OCR con imágenes difíciles y ver qué lee Tesseract
y cómo se validan los campos.

Uso:
  cd backend/API
  python scripts/dev/probar_ocr.py ruta/a/imagen.jpg [TIPO]

TIPO opcional: INE_NUEVA | INE_ANTERIOR | RESIDENCIA_TEMPORAL | RESIDENCIA_PERMANENTE | RESIDENCIA_TEMPORAL_ACUMULATIVA
Si no se pasa, se usa DESCONOCIDO (validación genérica).
"""
import sys
import os
import json

# Raíz de la API (backend/API): este archivo está en scripts/dev/
_api_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
sys.path.insert(0, _api_root)

from app.services.ocr_analyzer import OCRAnalyzer
from app.models.schemas import TipoDocumento


def main():
    if len(sys.argv) < 2:
        print(__doc__)
        print("Ejemplo: python scripts/dev/probar_ocr.py C:\\fotos\\ine.jpg INE_NUEVA")
        sys.exit(1)

    path = sys.argv[1]
    if not os.path.isfile(path):
        print(f"Error: no existe el archivo: {path}")
        sys.exit(2)

    tipo_str = (sys.argv[2] if len(sys.argv) > 2 else "DESCONOCIDO").upper()
    try:
        tipo_doc = TipoDocumento(tipo_str)
    except ValueError:
        print(f"Tipo no válido: {tipo_str}. Usar: INE_NUEVA, INE_ANTERIOR, RESIDENCIA_TEMPORAL, RESIDENCIA_PERMANENTE, RESIDENCIA_TEMPORAL_ACUMULATIVA, DESCONOCIDO")
        sys.exit(3)

    with open(path, "rb") as f:
        image_bytes = f.read()

    analyzer = OCRAnalyzer()

    # 1) Texto crudo que ve Tesseract (después del preprocesado)
    print("=" * 60)
    print("TEXTO EXTRAÍDO POR TESSERACT (preprocesado)")
    print("=" * 60)
    raw = analyzer.extraer_texto_raw(image_bytes)
    print(raw or "(vacío)")
    print()

    # 2) Resultado de la validación OCR (campos, score, alertas)
    print("=" * 60)
    print(f"VALIDACIÓN OCR (tipo: {tipo_doc.value})")
    print("=" * 60)
    check = analyzer.analyze(image_bytes, tipo_doc)
    out = check.model_dump()
    print(json.dumps(out, indent=2, ensure_ascii=False))
    print()
    print(f"Score OCR: {check.score}  |  OK: {check.ok}  |  Campos: {check.campos_detectados} detectados, {check.campos_validos} válidos")


if __name__ == "__main__":
    main()
