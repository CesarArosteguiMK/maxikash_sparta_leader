"""
Diagnóstico: extraer y mostrar todo el texto OCR de cada página
de identificacion_oficial2.pdf para ver por qué no se detecta tipo ni CURP.
Uso: desde backend/API: python scripts/diagnostico_pdf2.py
"""
import os
import sys

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", ".."))
sys.path.insert(0, os.path.join(ROOT, "backend", "API"))
PDF_PATH = os.path.join(ROOT, "pruebas_OCR", "identificacion_oficial2.pdf")

def main():
    import fitz
    import cv2
    import numpy as np
    from PIL import Image
    import pytesseract

    if not os.path.isfile(PDF_PATH):
        print("No existe:", PDF_PATH)
        return 1

    with open(PDF_PATH, "rb") as f:
        pdf_bytes = f.read()
    doc = fitz.open(stream=pdf_bytes, filetype="pdf")

    for page_num in range(len(doc)):
        print("=" * 60)
        print(f"PÁGINA {page_num + 1}")
        print("=" * 60)
        for dpi in [150, 200, 300]:
            pix = doc[page_num].get_pixmap(dpi=dpi)
            img_bytes = pix.tobytes("png")
            nparr = np.frombuffer(img_bytes, np.uint8)
            img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            if img is None:
                continue
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            t1 = pytesseract.image_to_string(gray, config="--oem 3 --psm 3 -l spa+eng").upper()
            clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
            enhanced = clahe.apply(gray)
            t2 = pytesseract.image_to_string(enhanced, config="--oem 3 --psm 3 -l spa+eng").upper()
            print(f"\n--- DPI {dpi} ---\n{t1[:1500]}")
            for kw in ["INE", "CREDENCIAL", "ELECTORAL", "MIGRACION", "NOMBRE", "CURP", "NUNEZ", "GONZALEZ"]:
                if kw in t1 or kw in t2:
                    print(f"  [DPI{dpi}] contiene: {kw}")
        print()
    doc.close()
    return 0

if __name__ == "__main__":
    sys.exit(main())
