# app/utils/nss_validator.py
"""
Validador del NSS (Número de Seguridad Social) IMSS México.
Valida formato de 11 dígitos y el dígito verificador (algoritmo tipo Luhn).
Estructura: 2 subdelegación + 2 año inscripción + 2 año nacimiento + 4 consecutivo + 1 verificador.
"""
import re
from typing import Tuple, Optional

try:
    import fitz
    PYMUPDF_AVAILABLE = True
except ImportError:
    PYMUPDF_AVAILABLE = False


def validar_nss(nss: str) -> Tuple[bool, str]:
    """
    Valida que el NSS tenga 11 dígitos y que el dígito verificador sea correcto.
    Retorna (True, "NSS válido") o (False, mensaje de error).
    """
    if not nss:
        return False, "NSS vacío"

    digitos = "".join(c for c in nss.strip() if c.isdigit())
    if len(digitos) != 11:
        return False, f"NSS debe tener 11 dígitos, tiene {len(digitos)}"

    # Multiplicadores 1-2-1-2-1-2-1-2-1-2 para los primeros 10 dígitos
    multiplicadores = (1, 2, 1, 2, 1, 2, 1, 2, 1, 2)
    suma = 0
    for i in range(10):
        d = int(digitos[i])
        prod = d * multiplicadores[i]
        if prod > 9:
            prod = (prod // 10) + (prod % 10)
        suma += prod

    # Dígito verificador: siguiente decena menos suma (ej: suma=66 -> 70-66=4)
    esperado = (10 - (suma % 10)) % 10
    real = int(digitos[10])

    if real != esperado:
        return False, f"Dígito verificador incorrecto (esperado {esperado}, recibido {real})"

    return True, "NSS válido"


def formatear_nss(nss: str) -> str:
    """Devuelve el NSS normalizado (solo 11 dígitos) o cadena vacía si no es válido."""
    digitos = "".join(c for c in (nss or "").strip() if c.isdigit())
    return digitos if len(digitos) == 11 else ""


def extraer_nss_de_pdf(pdf_bytes: bytes) -> Optional[str]:
    """
    Extrae el primer NSS de 11 dígitos encontrado en un PDF (vigencia de derechos IMSS o constancia NSS.pdf).
    Si el PDF es escaneado (sin capa de texto), usa OCR vía document_crosscheck.
    Retorna el número como string o None si no se encuentra.
    """
    if not PYMUPDF_AVAILABLE:
        return None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        texto = ""
        for page in doc:
            texto += page.get_text() + "\n"
        doc.close()
        if not texto.strip():
            from app.services.document_crosscheck import texto_de_pdf_con_ocr
            texto = texto_de_pdf_con_ocr(pdf_bytes)
        match = re.search(r"\b(\d{11})\b", texto)
        return match.group(1) if match else None
    except Exception:
        return None
