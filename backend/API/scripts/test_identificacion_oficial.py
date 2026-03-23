"""
Prueba interna: identificación oficial (frente/reverso) sobre pruebas_OCR/identificacion_oficial.pdf
Convierte las páginas del PDF a imágenes y ejecuta VerificacionService.verificar (igual que validar-expediente).
Ejecutar desde backend/API: python -m scripts.test_identificacion_oficial
"""
import asyncio
import os
import sys

# Raíz del proyecto Sparta Ledger (subir desde backend/API/scripts)
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", ".."))
sys.path.insert(0, os.path.join(ROOT, "backend", "API"))

PRUEBAS_OCR = os.path.join(ROOT, "pruebas_OCR")
PDF_PATH = os.path.join(PRUEBAS_OCR, "identificacion_oficial.pdf")


def pdf_paginas_a_imagenes(pdf_path: str, dpi: int = 200):
    """Abre el PDF y devuelve una lista de bytes PNG por página. Por defecto 200 DPI para mejor OCR en escaneos."""
    try:
        import fitz
    except ImportError:
        print("ERROR: PyMuPDF (fitz) no instalado. pip install pymupdf")
        return []
    if not os.path.isfile(pdf_path):
        return []
    with open(pdf_path, "rb") as f:
        pdf_bytes = f.read()
    doc = fitz.open(stream=pdf_bytes, filetype="pdf")
    imagenes = []
    for page in doc:
        pix = page.get_pixmap(dpi=dpi)
        img_bytes = pix.tobytes("png")
        imagenes.append(img_bytes)
    doc.close()
    return imagenes


async def main_async():
    from app.services.verificacion import VerificacionService
    from app.services.document_crosscheck import validacion_cruzada
    from app.models.schemas import TipoDocumento

    pdf_path = PDF_PATH
    if len(sys.argv) > 1:
        arg = sys.argv[1].strip()
        if os.path.isabs(arg) and os.path.isfile(arg):
            pdf_path = arg
        elif os.path.isfile(os.path.join(PRUEBAS_OCR, arg)):
            pdf_path = os.path.join(PRUEBAS_OCR, arg)
        else:
            pdf_path = arg

    print("=" * 60)
    print("Prueba: Identificación oficial (PDF -> frente/reverso)")
    print("=" * 60)
    print(f"PDF: {pdf_path}")
    print(f"Existe: {os.path.isfile(pdf_path)}")
    if not os.path.isfile(pdf_path):
        print("ERROR: No se encontró el archivo.")
        return 1

    imagenes = pdf_paginas_a_imagenes(pdf_path)
    if not imagenes:
        print("ERROR: No se pudieron extraer imágenes del PDF.")
        return 1

    print(f"Páginas extraídas: {len(imagenes)}")
    for i, img in enumerate(imagenes):
        print(f"  Página {i + 1}: {len(img)} bytes PNG")
    print()

    service = VerificacionService()
    # Auto-detectar tipo de documento (INE vs Residencia) en lugar de forzar RESIDENCIA_TEMPORAL
    tipo = None

    # Página 1 = frente, página 2 = reverso (si existe)
    frente_bytes = imagenes[0]
    reverso_bytes = imagenes[1] if len(imagenes) > 1 else None

    print("--- Verificación FRENTE ---")
    res_frente = await service.verificar(frente_bytes, tipo)
    ocr_f = res_frente.checks.ocr_campos
    forense_f = res_frente.checks.forense
    print(f"  Tipo detectado: {res_frente.documento_tipo}")
    print(f"  Score autenticidad: {res_frente.score_autenticidad}")
    print(f"  Resultado: {res_frente.resultado}")
    print(f"  Calidad foto (resumen): {forense_f.calidad_foto}")
    print(f"  Detalle forense:")
    print(f"    - Brillo excesivo: {forense_f.brillo_excesivo}")
    print(f"    - % pixeles sobreexpuestos: {forense_f.porcentaje_sobreexpuesto}")
    print(f"    - Borroso: {forense_f.borroso}")
    if forense_f.alertas:
        for a in forense_f.alertas:
            print(f"    - Alerta: {a}")
    if ocr_f:
        print(f"  OCR:")
        print(f"    - CURP: {ocr_f.curp.get('valor') if ocr_f.curp else None}")
        print(f"    - Nombre: {ocr_f.nombre_ocr}")
        print(f"    - Fecha nac. (CURP): {ocr_f.fecha_nacimiento_curp}")
        if getattr(ocr_f, 'alertas', None):
            for a in ocr_f.alertas:
                print(f"    - Alerta OCR: {a}")
    if res_frente.recomendacion:
        print(f"  Recomendacion: {res_frente.recomendacion}")
    if res_frente.alertas_globales:
        for a in res_frente.alertas_globales:
            print(f"  Alerta global: {a}")
    print()

    if reverso_bytes:
        print("--- Verificación REVERSO ---")
        res_reverso = await service.verificar(reverso_bytes, tipo)
        ocr_r = res_reverso.checks.ocr_campos
        forense_r = res_reverso.checks.forense
        print(f"  Score autenticidad: {res_reverso.score_autenticidad}")
        print(f"  Resultado: {res_reverso.resultado}")
        print(f"  Calidad foto: {forense_r.calidad_foto} (brillo_excesivo={forense_r.brillo_excesivo}, borroso={forense_r.borroso})")
        if ocr_r:
            print(f"  MRZ nombre: {ocr_r.mrz_nombre_completo}")
            print(f"  MRZ fecha nac.: {ocr_r.mrz_fecha_nacimiento}")
        print()

        # Validación cruzada (como en validar-expediente)
        resultado = validacion_cruzada(
            id_frente_curp=ocr_f.curp.get("valor") if ocr_f and ocr_f.curp else None,
            id_frente_nombre=ocr_f.nombre_ocr if ocr_f else None,
            id_frente_fecha_nac_curp=ocr_f.fecha_nacimiento_curp if ocr_f else None,
            id_reverso_mrz_nombre=ocr_r.mrz_nombre_completo if ocr_r else None,
            id_reverso_mrz_fecha_nac=ocr_r.mrz_fecha_nacimiento if ocr_r else None,
            calidad_foto=res_frente.checks.forense.calidad_foto,
            datos_curp_pdf=None,
            datos_nss=None,
            datos_fiscal=None,
            datos_acta=None,
        )
        print("--- Validación cruzada (frente + reverso) ---")
        print(f"  Todo coincide: {resultado.get('todo_coincide')}")
        print(f"  Foto rechazada: {resultado.get('foto_rechazada')}")
        print(f"  Alertas: {resultado.get('alertas', [])}")
        if resultado.get("comparaciones"):
            for k, v in resultado["comparaciones"].items():
                print(f"  {k}: {v}")

        # --- RESUMEN: por que fue rechazado / que tiene sentido ---
        print()
        print("=" * 60)
        print("RESUMEN (por que dio RECHAZADO)")
        print("=" * 60)
        foto_rechazada = resultado.get("foto_rechazada")
        calidad = forense_f.calidad_foto
        alertas = resultado.get("alertas", [])

        if foto_rechazada:
            print("El documento fue RECHAZADO por CALIDAD DE IMAGEN del frente.")
            if calidad == "revisar_brillo":
                print("- Motivo: La imagen del frente tiene exceso de brillo o luz (sobreexposicion).")
                print("  El analizador forense detecto demasiados pixeles muy claros, tipico de:")
                print("  reflejos, flash, o foto tomada con mucha luz directa sobre el plastico del INE.")
            elif calidad == "revisar_borroso":
                print("- Motivo: La imagen del frente esta borrosa o desenfocada.")
            elif "brillo" in (calidad or "") and "borroso" in (calidad or ""):
                print("- Motivo: La imagen combina brillo excesivo y desenfoque.")
            else:
                print(f"- Calidad reportada: {calidad}")
            print()
            print("Sugerencia: Volver a tomar la foto del FRENTE del documento sin reflejos,")
            print("con buena iluminacion indirecta y sin flash directo sobre el plastico.")
        else:
            print("El documento NO fue rechazado por foto; revisar alertas de coincidencia.")
            for a in alertas:
                print(f"  - {a}")

        if not foto_rechazada and resultado.get("todo_coincide"):
            print("Datos frente/reverso coherentes (nombre/fecha).")
        elif reverso_bytes and ocr_f and ocr_r:
            print()
            print("Datos extraidos (para contexto):")
            print(f"  Nombre en frente (OCR): {ocr_f.nombre_ocr}")
            print(f"  Nombre en reverso (MRZ): {ocr_r.mrz_nombre_completo}")
            print("  (Si coinciden parcialmente, la validacion cruzada los considera iguales.)")
        print("=" * 60)
    else:
        print("(Solo 1 página: no hay reverso para validación cruzada.)")

    print("=" * 60)
    return 0


def main():
    return asyncio.run(main_async())


if __name__ == "__main__":
    sys.exit(main())
