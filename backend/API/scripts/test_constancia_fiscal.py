"""
Prueba interna: extraer_datos_constancia_fiscal sobre pruebas_OCR/constancia_fiscal.pdf
Ejecutar desde backend/API: python -m scripts.test_constancia_fiscal
"""
import os
import sys

# Raíz del proyecto Sparta Ledger (subir desde backend/API)
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", ".."))
sys.path.insert(0, os.path.join(ROOT, "backend", "API"))

from app.services.document_crosscheck import (
    extraer_datos_constancia_fiscal,
    es_documento_constancia_fiscal,
)
from app.utils.curp_validator import validar_curp

PDF_PATH = os.path.join(ROOT, "pruebas_OCR", "constancia_fiscal.pdf")


def main():
    print("=" * 60)
    print("Prueba: Constancia fiscal (vigencia 2 meses + Asalariado + Régimen)")
    print("=" * 60)
    print(f"PDF: {PDF_PATH}")
    print(f"Existe: {os.path.isfile(PDF_PATH)}")
    if not os.path.isfile(PDF_PATH):
        print("ERROR: No se encontró el archivo.")
        return 1

    with open(PDF_PATH, "rb") as f:
        pdf_bytes = f.read()
    print(f"Tamaño: {len(pdf_bytes)} bytes\n")

    # ¿Es constancia fiscal?
    es_fiscal = es_documento_constancia_fiscal(pdf_bytes)
    print(f"es_documento_constancia_fiscal: {es_fiscal}\n")

    # Extraer datos
    datos = extraer_datos_constancia_fiscal(pdf_bytes)
    print("--- extraer_datos_constancia_fiscal ---")
    for k, v in datos.items():
        print(f"  {k}: {v}")
    print()

    # Validación lógica (misma que el endpoint)
    vigencia_ok = datos.get("meses_antiguedad") is None or datos.get("meses_antiguedad") <= 2.0
    actividad_ok = datos.get("actividad_economica_asalariado") is True
    regimen_ok = datos.get("regimen_sueldos_salarios") is True
    rfc_curp_ok = bool(datos.get("rfc") or (datos.get("curp") and validar_curp(datos["curp"])[0]))

    print("--- Validación (como en el endpoint) ---")
    print(f"  Vigencia <= 2 meses: {vigencia_ok} (meses_antiguedad={datos.get('meses_antiguedad')})")
    print(f"  Actividad Asalariado: {actividad_ok}")
    print(f"  Régimen Sueldos y Salarios: {regimen_ok}")
    print(f"  RFC/CURP presentes y válidos: {rfc_curp_ok}")
    print()

    valido = vigencia_ok and actividad_ok and regimen_ok and rfc_curp_ok
    print(f"Resultado final: {'VALIDO' if valido else 'RECHAZADO'}")
    print("=" * 60)
    return 0 if valido else 1


if __name__ == "__main__":
    sys.exit(main())
