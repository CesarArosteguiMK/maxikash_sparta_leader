"""
Prueba estado de cuenta: imprime nombre del banco y propietario para PDFs de prueba.
Ejecutar desde backend/API: python -m scripts.test___SPARTA_SECRET_REDACTED__
O con ruta: python -m scripts.test___SPARTA_SECRET_REDACTED__ ruta/a/Estado-1.pdf
"""
import os
import sys

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", ".."))
sys.path.insert(0, os.path.join(ROOT, "backend", "API"))

from app.services.__SPARTA_SECRET_REDACTED___analyzer import validar___SPARTA_SECRET_REDACTED___pdf
from app.services.__SPARTA_SECRET_REDACTED___analyzer import _texto_de_pdf

# Por defecto: pruebas_OCR al mismo nivel que sparta___SPARTA_SECRET_REDACTED__ (o dentro de sparta___SPARTA_SECRET_REDACTED__)
PRUEBAS_OCR = os.path.join(ROOT, "pruebas_OCR")
if not os.path.isdir(PRUEBAS_OCR):
    PRUEBAS_OCR = os.path.join(ROOT, "..", "pruebas_OCR")

PDFS_DEFAULT = [
    os.path.join(PRUEBAS_OCR, "Estado-1.pdf"),
    os.path.join(PRUEBAS_OCR, "__SPARTA_SECRET_REDACTED__2.pdf"),
]


def main():
    debug_texto = "--debug" in sys.argv or os.environ.get("ESTADO_CUENTA_DEBUG") == "1"
    if debug_texto and "--debug" in sys.argv:
        sys.argv.remove("--debug")

    if len(sys.argv) > 1:
        paths = [os.path.abspath(p) for p in sys.argv[1:] if not p.startswith("--")]
    else:
        paths = PDFS_DEFAULT

    print("=" * 60)
    print("Prueba: Estado de cuenta — Banco y propietario")
    print("=" * 60)

    for path in paths:
        print(f"\nPDF: {path}")
        print(f"Existe: {os.path.isfile(path)}")
        if not os.path.isfile(path):
            print("  -> No encontrado, omitiendo.")
            continue

        with open(path, "rb") as f:
            pdf_bytes = f.read()
        print(f"Tamaño: {len(pdf_bytes)} bytes")

        if debug_texto:
            texto = _texto_de_pdf(pdf_bytes)
            print("--- Texto extraído (OCR/texto) ---")
            print(repr(texto[:4000]) if len(texto) > 4000 else repr(texto))
            print("--- Fin texto ---")

        resultado = validar___SPARTA_SECRET_REDACTED___pdf(pdf_bytes)

        print("--- Resultado ---")
        print(f"  Nombre del banco:      {resultado.get('banco_detectado') or '—'}")
        print(f"  Propietario (titular): {resultado.get('nombre_propietario') or '—'}")
        print(f"  Es banco físico:       {resultado.get('es_banco_fisico')}")
        print(f"  Tiene datos titular:  {resultado.get('tiene_datos_titular')}")
        print(f"  Válido (aceptado):     {resultado.get('valido')}")
        print(f"  Mensaje:               {resultado.get('mensaje') or '—'}")
        print()

    print("=" * 60)
    return 0


if __name__ == "__main__":
    sys.exit(main())
