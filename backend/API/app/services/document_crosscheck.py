# app/services/document_crosscheck.py
"""
Validación cruzada de documentos: compara CURP y nombre entre
identificación oficial, constancia CURP, constancia fiscal, NSS y acta de nacimiento.
"""
import re
import io
from typing import Optional, Dict, Any, List
from datetime import datetime, timedelta
from loguru import logger

try:
    import fitz
    PYMUPDF_AVAILABLE = True
except ImportError:
    PYMUPDF_AVAILABLE = False

try:
    import pytesseract
    from PIL import Image
    import cv2
    import numpy as np
    TESSERACT_AVAILABLE = True
except ImportError:
    TESSERACT_AVAILABLE = False

from app.utils.curp_validator import validar_curp, extraer_datos_curp
from app.core.config import get_settings


def pdf_paginas_a_png_bytes(pdf_bytes: bytes, dpi: int = 150) -> List[bytes]:
    """Convierte cada página del PDF a bytes PNG. Para uso en verificación de ID oficial."""
    if not PYMUPDF_AVAILABLE:
        return []
    if not pdf_bytes or len(pdf_bytes) < 100:
        return []
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        out = []
        for page in doc:
            pix = page.get_pixmap(dpi=dpi)
            out.append(pix.tobytes("png"))
        doc.close()
        return out
    except Exception as e:
        logger.warning(f"pdf_paginas_a_png_bytes: {e}")
        return []


def _texto_ocr_imagen(image_png_bytes: bytes) -> Optional[str]:
    """Extrae texto de una imagen PNG: RapidOCR si está disponible, si no Tesseract."""
    try:
        try:
            from rapidocr_onnxruntime import RapidOCR
        except ImportError:
            try:
                from rapidocr import RapidOCR
            except ImportError:
                RapidOCR = None
        if RapidOCR is not None:
            engine = RapidOCR()
            out = engine(image_png_bytes)
            result = out[0] if isinstance(out, (list, tuple)) and len(out) > 0 else out
            if not result:
                return None
            lineas = [str(item[1]).strip() for item in result if isinstance(item, (list, tuple)) and len(item) >= 2 and item[1]]
            if not lineas:
                lineas = [item.strip() for item in result if isinstance(item, str)]
            return "\n".join(lineas) if lineas else None
    except Exception:
        pass
    if TESSERACT_AVAILABLE:
        try:
            img = Image.open(io.BytesIO(image_png_bytes))
            if img.mode != "RGB":
                img = img.convert("RGB")
            return pytesseract.image_to_string(img, config="--oem 3 --psm 3 -l spa+eng").strip() or None
        except Exception:
            pass
    return None


def texto_de_pdf_con_ocr(pdf_bytes: bytes, max_paginas: int = 3) -> str:
    """Extrae texto del PDF; si no hay capa de texto (escaneado), usa OCR (RapidOCR o Tesseract)."""
    if not PYMUPDF_AVAILABLE or not pdf_bytes or len(pdf_bytes) < 100:
        return ""
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        texto = ""
        for i, page in enumerate(doc):
            if i >= max_paginas:
                break
            texto += page.get_text() + "\n"
        doc.close()
        if texto.strip():
            return texto
        imagenes = pdf_paginas_a_png_bytes(pdf_bytes, dpi=200)
        if not imagenes:
            return ""
        for img_bytes in imagenes[:max_paginas]:
            t = _texto_ocr_imagen(img_bytes)
            if t:
                texto += t + "\n"
        return texto
    except Exception as e:
        logger.warning(f"texto_de_pdf_con_ocr: {e}")
        return ""


MESES_ES = {
    "enero": 1, "febrero": 2, "marzo": 3, "abril": 4,
    "mayo": 5, "junio": 6, "julio": 7, "agosto": 8,
    "septiembre": 9, "octubre": 10, "noviembre": 11, "diciembre": 12,
}


def _parsear_fecha_espanol(texto: str) -> Optional[datetime]:
    """Extrae fecha de texto como '02 de marzo de 2026' o '06 DE ENERO DE 2026'."""
    pat = r"(\d{1,2})\s+de\s+([a-záéíóú]+)\s+de\s+(\d{4})"
    m = re.search(pat, texto, re.IGNORECASE)
    if not m:
        return None
    dia = int(m.group(1))
    mes_str = m.group(2).lower()
    anio = int(m.group(3))
    mes = MESES_ES.get(mes_str)
    if not mes:
        return None
    try:
        return datetime(anio, mes, dia)
    except ValueError:
        return None


def _normalizar_nombre(nombre: str) -> str:
    nombre = nombre.upper().strip()
    nombre = re.sub(r"[^A-Z\s]", "", nombre)
    return re.sub(r"\s+", " ", nombre).strip()


def _nombres_coinciden(n1: str, n2: str) -> bool:
    """Compara dos nombres: coincide si comparten al menos 2 palabras significativas (>2 chars)."""
    if not n1 or not n2:
        return False
    p1 = {w for w in _normalizar_nombre(n1).split() if len(w) > 2}
    p2 = {w for w in _normalizar_nombre(n2).split() if len(w) > 2}
    comunes = p1 & p2
    return len(comunes) >= 2


def _texto_de_pdf(pdf_bytes: bytes) -> str:
    """Extrae todo el texto de un PDF. Devuelve '' si falla."""
    if not PYMUPDF_AVAILABLE:
        return ""
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        texto = ""
        for page in doc:
            texto += page.get_text() + "\n"
        doc.close()
        return texto or ""
    except Exception:
        return ""


def extraer_informacion_ingresos_fad(pdf_bytes: bytes) -> Dict[str, Any]:
    """
    Extrae la sección 'Información de Ingresos' del FAD_DOC (contrato firmado).
    Se busca al final del documento (últimas 2 páginas). Devuelve el texto desde
    ese título hasta el final de la página, para mostrar en Sabueso (donde trabaja).
    """
    resultado = {"texto_seccion": "", "empresa": None, "empleado": None, "ingreso_mensual_neto": None, "telefono": None, "encontrado": False}
    if not PYMUPDF_AVAILABLE:
        return resultado
    if not pdf_bytes or len(pdf_bytes) < 100:
        return resultado
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        if len(doc) == 0:
            doc.close()
            return resultado
        # Últimas 2 páginas (la sección suele estar al final)
        texto = ""
        inicio_pag = max(0, len(doc) - 2)
        for i in range(inicio_pag, len(doc)):
            texto += doc[i].get_text() + "\n"
        doc.close()
    except Exception as e:
        logger.warning(f"extraer_informacion_ingresos_fad: {e}")
        return resultado

    # Buscar "Información de Ingresos" (con o sin tilde)
    marcas = ("INFORMACION DE INGRESOS", "INFORMACIÓN DE INGRESOS")
    texto_upper = texto.upper().replace("Í", "I").replace("Ó", "O")
    idx = -1
    marca_usada = ""
    for marca in marcas:
        idx = texto_upper.find(marca)
        if idx >= 0:
            marca_usada = marca
            break
    if idx >= 0:
        # Desde el final del título hasta el final del texto
        bloque = texto[idx + len(marca_usada):].strip()
        # Solo hasta "Referencia(s)": actividad laboral, sin la sección de referencias
        lineas_raw = [ln.strip() for ln in bloque.split("\n") if ln.strip()]
        lineas = []
        for ln in lineas_raw:
            ln_upper = ln.upper().replace("Í", "I").replace("Ó", "O")
            # Cortar al llegar a la sección Referencia(s), solo mantener Actividad laboral
            if re.match(r"^REFERENCIA(S)?\s*(PERSONALES|BANCARIAS|COMERCIALES)?\s*$", ln_upper) or ln_upper.startswith("REFERENCIA"):
                break
            lineas.append(ln)
        # Limitar a un tamaño razonable
        lineas = lineas[:50]
        resultado["texto_seccion"] = "\n".join(lineas)
        resultado["encontrado"] = True
        # Extraer campos típicos del FAD (ACTIVIDAD LABORAL)
        etiquetas_valor = [
            ("NOMBRE DE LA EMPRESA O NEGOCIO", "empresa"),
            ("NOMBRE DE LA EMPRESA", "empresa"),
            ("EMPRESA O NEGOCIO", "empresa"),
            ("EMPRESA", "empresa"),
            ("LUGAR DE TRABAJO", "empresa"),
            ("DONDE TRABAJA", "empresa"),
            ("EMPLEADO", "empleado"),
            ("TRABAJA POR SU CUENTA", "empleado"),  # a veces mismo valor
            ("INGRESO MENSUAL NETO", "ingreso_mensual_neto"),
            ("INGRESO MENSUAL", "ingreso_mensual_neto"),
            ("TEL A 10 DIGITOS", "telefono"),
            ("TEL 1", "telefono"),
            ("TELEFONO", "telefono"),
            ("TELÉFONO", "telefono"),
        ]
        for ln in lineas[:35]:
            ln_upper = ln.upper()
            for etiqueta, campo in etiquetas_valor:
                if etiqueta in ln_upper:
                    partes = re.split(r"\s*[:\-]\s*", ln, maxsplit=1)
                    if len(partes) >= 2 and partes[1].strip():
                        valor = partes[1].strip()[:500]
                        if campo == "telefono" and not re.search(r"\d{10}", valor):
                            valor = re.sub(r"\D", "", valor)
                            if len(valor) >= 10:
                                valor = valor[:10]
                        if valor and (campo != "empleado" or not resultado.get("empleado")):
                            resultado[campo] = valor
                    break
    return resultado


def es_documento_nss(pdf_bytes: bytes) -> bool:
    """True si el PDF es uno de los tres formatos aceptados del IMSS: (1) vigencia de derechos,
    (2) constancia de asignación/homoclave NSS, (3) constancia de semanas cotizadas. No acepta la tarjeta NSS (imprimir/recortar).
    """
    t = texto_de_pdf_con_ocr(pdf_bytes).upper()
    t = t.replace("Á", "A").replace("É", "E").replace("Í", "I").replace("Ó", "O").replace("Ú", "U")
    if not t:
        return False
    # Rechazar tarjeta NSS (formato "imprimir y recortar" / asignación o localización de número)
    if "IMPRIME Y RECORTA" in t or "ASIGNACION O LOCALIZACION" in t:
        return False
    # Debe ser documento del IMSS
    if "INSTITUTO MEXICANO DEL SEGURO SOCIAL" not in t and "IMSS" not in t:
        return False
    # (1) Vigencia de derechos (descarga desde IMSS Digital)
    if "VIGENCIA DE DERECHOS" in t or "CONSTANCIA DE VIGENCIA" in t:
        return True
    # (2) Constancia con NSS: asignación, homoclave o número de seguridad social (formato NSS.pdf)
    if "ASIGNACION DE NUMERO DE SEGURIDAD SOCIAL" in t or "ASIGNACIÓN DE NÚMERO DE SEGURIDAD SOCIAL" in t:
        return True
    if "NUMERO DE SEGURIDAD SOCIAL" in t or "NÚMERO DE SEGURIDAD SOCIAL" in t:
        return True
    if "HOMOCLAVE" in t or re.search(r"IMSS-\d{2}-\d{3}", t):
        return True
    # (3) Constancia de semanas cotizadas (formato NSS3.pdf)
    if "CONSTANCIA DE SEMANAS COTIZADAS" in t or "HISTORIAL DE REGISTROS AFILIATORIOS" in t:
        return True
    if "SEMANAS COTIZADAS" in t and "IMSS" in t:
        return True
    return False


def es_documento_curp(pdf_bytes: bytes) -> bool:
    """True si el PDF es constancia de CURP (RENAPO)."""
    t = _texto_de_pdf(pdf_bytes).upper()
    t = t.replace("Á", "A").replace("É", "E").replace("Í", "I").replace("Ó", "O").replace("Ú", "U")
    if not t:
        return False
    # Constancia + Clave única / CURP / registro de población
    if "CONSTANCIA" in t and ("CLAVE UNICA" in t or "CURP" in t or "REGISTRO DE POBLACION" in t):
        return True
    # Estados Unidos Mexicanos + Constancia (formato oficial)
    if "ESTADOS UNIDOS MEXICANOS" in t and "CONSTANCIA" in t:
        return True
    # RENAPO (emisor) + constancia o CURP
    if "RENAPO" in t and ("CONSTANCIA" in t or "CURP" in t):
        return True
    return False


def es_documento_constancia_fiscal(pdf_bytes: bytes) -> bool:
    """True si el PDF es constancia de situación fiscal (SAT)."""
    t = _texto_de_pdf(pdf_bytes).upper()
    t = t.replace("Á", "A").replace("É", "E").replace("Í", "I").replace("Ó", "O").replace("Ú", "U")
    if not t:
        return False
    if "CONSTANCIA DE SITUACION FISCAL" in t:
        return True
    if "CEDULA DE IDENTIFICACION FISCAL" in t:
        return True
    return False


def es_documento_acta_nacimiento(pdf_bytes: bytes) -> bool:
    """True si el PDF parece acta/certificado de nacimiento (texto identificativo)."""
    t = _texto_de_pdf(pdf_bytes).upper()
    t = t.replace("Á", "A").replace("É", "E").replace("Í", "I").replace("Ó", "O").replace("Ú", "U")
    if not t:
        return False
    # Frases exactas
    if "ACTA DE NACIMIENTO" in t or "CERTIFICADO DE NACIMIENTO" in t or "CERTIDIFCADO DE NACIMIENTO" in t:
        return True
    if "CERTIFICACION DE NACIMIENTO" in t:
        return True
    # Variantes: acta + nacimiento, certificado + nacimiento, registro civil + nacimiento
    if ("ACTA" in t or "ACTA N" in t or "ACTA NUMERO" in t) and "NACIMIENTO" in t:
        return True
    if "CERTIFICADO" in t and "NACIMIENTO" in t:
        return True
    if "REGISTRO CIVIL" in t and "NACIMIENTO" in t:
        return True
    if ("INSCRIPCION" in t or "INSCRITO" in t or "SE INSCRIBE" in t) and "NACIMIENTO" in t:
        return True
    if "NACIDO" in t and ("REGISTRO" in t or "ACTA" in t or "LIBRO" in t or "FOLIO" in t):
        return True
    return False


def extraer_datos_curp_pdf(pdf_bytes: bytes) -> Dict[str, Any]:
    """Extrae nombre, CURP y fecha de emisión de la constancia CURP (PDF RENAPO).
    Estructura del PDF RENAPO:
      - Línea con nombre completo (LAZARO RAUDEL GONZALEZ LEYVA) aparece antes de 'Clave:'
      - Línea con CURP (18 chars)
      - 'Ciudad de México, a DD de MES de YYYY'
    """
    resultado = {"nombre": None, "curp": None, "fecha_emision": None,
                 "es_reciente": None, "meses_antiguedad": None}
    if not PYMUPDF_AVAILABLE:
        return resultado
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        texto = ""
        for page in doc:
            texto += page.get_text() + "\n"
        doc.close()
    except Exception:
        return resultado

    lineas = [ln.strip() for ln in texto.split("\n") if ln.strip()]

    compacto = texto.upper().replace(" ", "").replace("\n", "")
    for m in re.finditer(r"[A-Z]{4}[A-Z0-9]{14}", compacto):
        cand = m.group()[:18]
        if len(cand) == 18 and validar_curp(cand)[0]:
            resultado["curp"] = cand
            break

    skip_words = {"PRESENTE", "SECRETARIA", "ROSA", "ICELA", "AGRADEZCO", "ESTAMOS",
                  "NUESTRO", "LOS", "EL", "LA", "DE", "EN", "DATOS", "TELCURP",
                  "TRAMITE", "DOCUMENTO"}
    for ln in lineas:
        limpio = _normalizar_nombre(ln)
        partes = limpio.split()
        if 3 <= len(partes) <= 6 and all(len(p) >= 2 and p.isalpha() for p in partes):
            if partes[0] not in skip_words and partes[-1] not in skip_words:
                resultado["nombre"] = limpio
                break

    fecha = _parsear_fecha_espanol(texto)
    if fecha:
        resultado["fecha_emision"] = fecha.strftime("%d/%m/%Y")
        hoy = datetime.now()
        diff = hoy - fecha
        meses = diff.days / 30.44
        resultado["meses_antiguedad"] = round(meses, 1)
        resultado["es_reciente"] = meses <= 3.0

    return resultado


def extraer_datos_nss_pdf(pdf_bytes: bytes) -> Dict[str, Any]:
    """Extrae nombre, CURP y NSS del PDF de constancia IMSS.
    Estructura del PDF IMSS:
      Línea 7: nombre(s) (ej. LAZARO RAUDEL)
      Línea 8: primer apellido (ej. GONZALEZ)
      Línea 9: segundo apellido (ej. LEYVA)
      Línea 11: fecha nacimiento (ej. 12/01/1996)
      Línea 13: CURP
      Línea 19: NSS (11 dígitos)
      También en cadena original: 'Nombre o Razon Social:LAZARO RAUDEL GONZALEZ LEYVA'
    """
    resultado = {"nombre": None, "curp": None, "nss": None, "fecha_nacimiento": None}
    if not PYMUPDF_AVAILABLE:
        return resultado
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        texto = ""
        for page in doc:
            texto += page.get_text() + "\n"
        doc.close()
        if not texto.strip():
            texto = texto_de_pdf_con_ocr(pdf_bytes) or ""
    except Exception:
        return resultado

    compacto = texto.upper().replace(" ", "").replace("\n", "")
    for m in re.finditer(r"[A-Z]{4}[A-Z0-9]{14}", compacto):
        cand = m.group()[:18]
        if len(cand) == 18 and validar_curp(cand)[0]:
            resultado["curp"] = cand
            break

    nss_m = re.search(r"\b(\d{11})\b", texto)
    if nss_m:
        resultado["nss"] = nss_m.group(1)

    fn_m = re.search(r"(\d{2}/\d{2}/\d{4})", texto)
    if fn_m:
        resultado["fecha_nacimiento"] = fn_m.group(1)

    razon_m = re.search(
        r"(?:Nombre\s*o\s*Razon\s*Social)\s*:\s*([A-Z\s\n]+?)(?:\||Curp|curp|CURP)",
        texto, re.IGNORECASE
    )
    if razon_m:
        resultado["nombre"] = _normalizar_nombre(razon_m.group(1))

    if not resultado["nombre"]:
        lineas = [ln.strip() for ln in texto.split("\n") if ln.strip()]
        etiquetas_imss = {"Instituto Mexicano", "El Instituto", "Folio",
                          "Fecha de solicitud", "Homoclave", "Nombre(s):",
                          "Primer apellido:", "Segundo apellido:", "Sexo:",
                          "Lugar de nacimiento:", "CURP:", "Hombre", "Mujer",
                          "NACIDO EN"}
        partes = []
        for ln in lineas:
            if any(ln.startswith(e) for e in etiquetas_imss):
                continue
            if re.match(r"^\d", ln) or len(ln) < 3 or "/" in ln:
                continue
            limpio = _normalizar_nombre(ln)
            palabras = limpio.split()
            if 1 <= len(palabras) <= 3 and all(w.isalpha() and len(w) >= 2 for w in palabras):
                if re.search(r"instituto|seguro|social|folio|homoclave|formato|hace|constar|siguiente", ln, re.I):
                    continue
                partes.append(limpio)
                if len(partes) >= 3:
                    break
        if len(partes) >= 2:
            resultado["nombre"] = " ".join(partes)

    return resultado


def extraer_datos_constancia_fiscal(pdf_bytes: bytes) -> Dict[str, Any]:
    """Extrae nombre, CURP, RFC, fecha de emisión, vigencia (máx 2 meses),
    actividad económica Asalariado y régimen Sueldos y Salarios de la constancia fiscal (SAT).
    Estructura del PDF SAT:
      Página 1: Cédula + "Lugar y Fecha de Emisión" (ej. NEZAHUALCOYOTL, MEXICO A 20 DE FEBRERO DE 2026)
      Página 2: Actividades Económicas (debe incluir "Asalariado") y Regímenes (debe incluir "Sueldos y Salarios")
    """
    resultado = {
        "nombre": None, "curp": None, "rfc": None,
        "fecha_emision": None, "es_reciente": None, "meses_antiguedad": None,
        "vigencia_ok": None,
        "actividad_economica_asalariado": False,
        "regimen_sueldos_salarios": False,
    }
    if not PYMUPDF_AVAILABLE:
        return resultado
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        texto = ""
        for page in doc:
            texto += page.get_text() + "\n"
        doc.close()
    except Exception:
        return resultado

    lineas = [ln.strip() for ln in texto.split("\n")]
    texto_upper = texto.upper()
    texto_norm = texto_upper.replace("Á", "A").replace("É", "E").replace("Í", "I").replace("Ó", "O").replace("Ú", "U")

    curp_m = re.search(r"CURP:\s*\n?\s*([A-Z0-9]{18})", texto, re.IGNORECASE)
    if curp_m:
        cand = curp_m.group(1).upper()
        if validar_curp(cand)[0]:
            resultado["curp"] = cand

    rfc_m = re.search(r"RFC:\s*\n?\s*([A-Z0-9]{12,13})", texto, re.IGNORECASE)
    if rfc_m:
        resultado["rfc"] = rfc_m.group(1).upper()

    nombre_val = apellido1 = apellido2 = None
    for i, ln in enumerate(lineas):
        if re.match(r"Nombre\s*\(?s?\)?:", ln, re.IGNORECASE):
            if i + 1 < len(lineas):
                nombre_val = lineas[i + 1].strip()
        elif re.match(r"Primer\s+Apellido:", ln, re.IGNORECASE):
            if i + 1 < len(lineas):
                apellido1 = lineas[i + 1].strip()
        elif re.match(r"Segundo\s+Apellido:", ln, re.IGNORECASE):
            if i + 1 < len(lineas):
                apellido2 = lineas[i + 1].strip()

    partes = []
    if nombre_val:
        partes.append(nombre_val)
    if apellido1:
        partes.append(apellido1)
    if apellido2:
        partes.append(apellido2)
    if partes:
        resultado["nombre"] = _normalizar_nombre(" ".join(partes))

    fecha = _parsear_fecha_espanol(texto)
    if fecha:
        resultado["fecha_emision"] = fecha.strftime("%d/%m/%Y")
        hoy = datetime.now()
        diff = hoy - fecha
        meses = diff.days / 30.44
        resultado["meses_antiguedad"] = round(meses, 1)
        resultado["es_reciente"] = meses <= 2.0
        resultado["vigencia_ok"] = meses <= 2.0

    # Actividad Económica: debe decir "Asalariado" (tabla Actividades Económicas, normalmente pág 2)
    if "ASALARIADO" in texto_norm:
        resultado["actividad_economica_asalariado"] = True

    # Régimen: debe incluir "Régimen de Sueldos y Salarios e Ingresos Asimilados a Salarios"
    if "SUELDOS Y SALARIOS" in texto_norm or "SUELDOS Y SALARIOS E INGRESOS ASIMILADOS" in texto_norm:
        resultado["regimen_sueldos_salarios"] = True

    return resultado


def _configurar_tesseract():
    """Configura pytesseract con la ruta correcta de Tesseract."""
    s = get_settings()
    cmd = getattr(s, "tesseract_cmd", None) or "tesseract"
    pytesseract.pytesseract.tesseract_cmd = cmd


_PREPROCESOS = [
    {"nombre": "clahe_psm6", "psm": 6},
    {"nombre": "otsu_psm6", "psm": 6},
    {"nombre": "adaptive_psm4", "psm": 4},
    {"nombre": "clahe_psm3", "psm": 3},
    {"nombre": "denoise_psm6", "psm": 6},
    {"nombre": "sharp_psm6", "psm": 6},
]


def _preprocesar_imagen(img_cv: np.ndarray, metodo: str) -> np.ndarray:
    gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)

    if metodo.startswith("clahe"):
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        return clahe.apply(gray)
    elif metodo.startswith("otsu"):
        _, thresh = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        return thresh
    elif metodo.startswith("adaptive"):
        return cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                     cv2.THRESH_BINARY, 31, 10)
    elif metodo.startswith("denoise"):
        denoised = cv2.fastNlMeansDenoising(gray, None, 12, 7, 21)
        clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
        return clahe.apply(denoised)
    elif metodo.startswith("sharp"):
        blurred = cv2.GaussianBlur(gray, (0, 0), 3)
        sharp = cv2.addWeighted(gray, 1.5, blurred, -0.5, 0)
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        return clahe.apply(sharp)
    return gray


def _ocr_multi_pasada(img_cv: np.ndarray, lang: str = "spa") -> List[str]:
    """Ejecuta OCR con múltiples estrategias y devuelve todos los textos."""
    textos = []
    escalas = [1.0, 1.5]
    for esc in escalas:
        if esc != 1.0:
            img_esc = cv2.resize(img_cv, None, fx=esc, fy=esc, interpolation=cv2.INTER_CUBIC)
        else:
            img_esc = img_cv
        for cfg in _PREPROCESOS:
            try:
                procesada = _preprocesar_imagen(img_esc, cfg["nombre"])
                txt = pytesseract.image_to_string(
                    Image.fromarray(procesada),
                    config=f"--oem 3 --psm {cfg['psm']} -l {lang}"
                )
                if txt.strip():
                    textos.append(txt.upper())
            except Exception:
                continue
    return textos


_PATRONES_FECHA_NACIMIENTO = [
    r"(?:NACI[OÓ]|FECHA\s*DE\s*NACIMIENTO|NACIDO|BORN)[^0-9]{0,30}(\d{1,2})\s*(?:DE\s+)?([A-ZÁÉÍÓÚ]+)\s*(?:DE\s+)?(\d{4})",
    r"(?:NACI[OÓ]|NACIDO|BORN)\s*(?:EL\s*)?(?:D[IÍ]A\s*)?(\d{1,2})\s*DE\s*([A-ZÁÉÍÓÚ]+)\s*DE\s*(\d{4})",
    r"FECHA\s*DE\s*NACIMIENTO[^0-9]{0,10}(\d{1,2})\s*[-/.]\s*(\d{1,2})\s*[-/.]\s*(\d{4})",
    r"(\d{1,2})\s*[-/.]\s*(\d{1,2})\s*[-/.]\s*(\d{4})",
]

_PATRONES_NOMBRE_ACTA = [
    r"(?:NOMBRE\s*\(?S?\)?\s*(?:Y\s*APELLIDOS?)?)\s*[:;\s]+\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{5,80})",
    r"(?:REGISTRAD[OA]|INSCRIPT[OA]|PRESENTAD[OA])\s*[:;\s]+\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{5,80})",
    r"(?:SE\s+LE\s+PUS[OÓ]\s+(?:POR\s+)?NOMBRE)\s*[:;\s]*\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{3,60})",
    r"(?:NACIDO|NACIDA)\s*[:;\s]+\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{5,80})",
    r"NOMBRE\s*DEL\s*(?:REGISTRAD[OA]|INSCRIT[OA]|NACID[OA])\s*[:;\s]+\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{5,80})",
]

_STOP_WORDS_ACTA = {"ANTE", "EN", "EL", "LA", "LOS", "LAS", "DEL", "MUNICIPIO",
                    "ESTADO", "CIUDAD", "PROVINCIA", "REGISTRO", "CIVIL", "ACTA",
                    "CERTIFICADO", "INSCRIPCION", "QUE", "COMPARECIO", "CERTIFICO",
                    "CON", "NUMERO", "FOLIO", "LIBRO", "TOMO", "LUGAR", "SEXO",
                    "FECHA", "DATOS", "PARA"}


def _parsear_fecha_de_texto(texto: str) -> Optional[str]:
    """Busca una fecha de nacimiento en un texto OCR."""
    for pat in _PATRONES_FECHA_NACIMIENTO:
        for m in re.finditer(pat, texto):
            dia = m.group(1)
            g2 = m.group(2)
            anio = m.group(3)
            anio_int = int(anio)
            if anio_int < 1900 or anio_int > 2025:
                continue
            if g2.isdigit():
                mes_num = int(g2)
                if 1 <= mes_num <= 12:
                    return f"{int(dia):02d}/{mes_num:02d}/{anio}"
            else:
                mes_num = MESES_ES.get(g2.lower())
                if mes_num:
                    return f"{int(dia):02d}/{mes_num:02d}/{anio}"

    fn_m = re.search(r"(\d{2})[/.-](\d{2})[/.-](\d{4})", texto)
    if fn_m:
        anio_int = int(fn_m.group(3))
        if 1900 <= anio_int <= 2025:
            return f"{fn_m.group(1)}/{fn_m.group(2)}/{fn_m.group(3)}"
    return None


def _parsear_nombre_de_texto(texto: str) -> Optional[str]:
    """Busca nombre del registrado en un texto OCR."""
    for pat in _PATRONES_NOMBRE_ACTA:
        m = re.search(pat, texto)
        if m:
            raw = m.group(1).strip()
            limpio = _normalizar_nombre(raw)
            palabras = limpio.split()
            filtradas = []
            for p in palabras:
                if p in _STOP_WORDS_ACTA:
                    break
                if len(p) >= 2:
                    filtradas.append(p)
            if len(filtradas) >= 2:
                return " ".join(filtradas[:6])
    return None


def _votar_mejor(candidatos: List[str], total_pasadas: int) -> tuple:
    """Devuelve (valor_ganador, confianza 0-100).
    La confianza se calcula contra el total de pasadas OCR, no solo las que
    detectaron algo. Así 1 detección en 18 pasadas = ~6%, no 100%.
    """
    if not candidatos:
        return None, 0
    from collections import Counter
    conteo = Counter(candidatos)
    ganador, votos = conteo.most_common(1)[0]
    denominador = max(total_pasadas, len(candidatos))
    confianza = round((votos / denominador) * 100)
    return ganador, confianza


def extraer_datos_acta_nacimiento(pdf_bytes: bytes) -> Dict[str, Any]:
    """Extrae nombre y fecha de nacimiento del acta de nacimiento.
    Usa OCR multi-pasada con votación: ejecuta múltiples estrategias de
    preprocesamiento, extrae fechas/nombres de cada una, y el valor con
    más votos gana. Esto compensa errores aleatorios del OCR.
    """
    resultado = {
        "nombre": None, "fecha_nacimiento": None, "texto_extraido": False,
        "confianza_fecha": 0, "confianza_nombre": 0, "pasadas_ocr": 0,
    }

    if not PYMUPDF_AVAILABLE:
        logger.warning("PyMuPDF no disponible para acta de nacimiento")
        return resultado

    try:
        _configurar_tesseract()
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        imagenes_cv: List[np.ndarray] = []
        texto_digital = ""

        for page in doc:
            txt = page.get_text()
            if txt.strip() and len(txt.strip()) > 30:
                texto_digital += txt + "\n"
                continue

            if not TESSERACT_AVAILABLE:
                logger.warning("Tesseract no disponible para OCR de acta escaneada")
                continue

            imgs = page.get_images(full=True)
            if not imgs:
                pix = page.get_pixmap(dpi=300)
                img_bytes_raw = pix.tobytes("png")
                nparr = np.frombuffer(img_bytes_raw, np.uint8)
                img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                if img_cv is not None:
                    imagenes_cv.append(img_cv)
                continue

            for img_info in imgs:
                xref = img_info[0]
                try:
                    base = doc.extract_image(xref)
                    img_raw = base["image"]
                    nparr = np.frombuffer(img_raw, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    if img_cv is None:
                        continue
                    h, w = img_cv.shape[:2]
                    if w < 1200:
                        scale = max(1400 / w, 2.0)
                        img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale,
                                            interpolation=cv2.INTER_CUBIC)
                    imagenes_cv.append(img_cv)
                except Exception as img_err:
                    logger.debug(f"Error extrayendo imagen xref={xref}: {img_err}")

        doc.close()
    except Exception as e:
        logger.error(f"Error procesando acta de nacimiento: {e}")
        return resultado

    if texto_digital.strip() and len(texto_digital.strip()) > 50:
        resultado["texto_extraido"] = True
        fecha = _parsear_fecha_de_texto(texto_digital.upper())
        nombre = _parsear_nombre_de_texto(texto_digital.upper())
        if fecha:
            resultado["fecha_nacimiento"] = fecha
            resultado["confianza_fecha"] = 95
        if nombre:
            resultado["nombre"] = nombre
            resultado["confianza_nombre"] = 95
        resultado["pasadas_ocr"] = 0
        return resultado

    if not imagenes_cv:
        return resultado

    fechas_candidatas: List[str] = []
    nombres_candidatos: List[str] = []
    total_pasadas = 0

    for img_cv in imagenes_cv:
        textos = _ocr_multi_pasada(img_cv)
        total_pasadas += len(textos)

        for txt in textos:
            fecha = _parsear_fecha_de_texto(txt)
            if fecha:
                fechas_candidatas.append(fecha)
            nombre = _parsear_nombre_de_texto(txt)
            if nombre:
                nombres_candidatos.append(nombre)

    resultado["texto_extraido"] = True
    resultado["pasadas_ocr"] = total_pasadas
    logger.info(
        f"Acta nacimiento multi-pasada: {total_pasadas} pasadas, "
        f"{len(fechas_candidatas)} fechas, {len(nombres_candidatos)} nombres"
    )

    fecha_final, conf_fecha = _votar_mejor(fechas_candidatas, total_pasadas)
    nombre_final, conf_nombre = _votar_mejor(nombres_candidatos, total_pasadas)

    resultado["fecha_nacimiento"] = fecha_final
    resultado["confianza_fecha"] = conf_fecha

    if conf_nombre >= 15 and nombre_final:
        resultado["nombre"] = nombre_final
        resultado["confianza_nombre"] = conf_nombre
    else:
        resultado["nombre"] = None
        resultado["confianza_nombre"] = conf_nombre

    return resultado


def validacion_cruzada(
    id_frente_curp: Optional[str],
    id_frente_nombre: Optional[str],
    id_frente_fecha_nac_curp: Optional[str],
    id_reverso_mrz_nombre: Optional[str],
    id_reverso_mrz_fecha_nac: Optional[str],
    calidad_foto: Optional[str],
    datos_curp_pdf: Optional[Dict[str, Any]],
    datos_nss: Optional[Dict[str, Any]],
    datos_fiscal: Optional[Dict[str, Any]],
    datos_acta: Optional[Dict[str, Any]],
) -> Dict[str, Any]:
    """
    Realiza la validación cruzada entre todos los documentos.

    Reglas:
    - CURP se compara entre: identificación, CURP.pdf, constancia_fiscal, NSS.
    - Nombre se compara entre todos los documentos.
    - Fecha de nacimiento se compara entre: CURP (decodificado), MRZ (reverso), acta.
    - Si CURP de ID no coincide con doc CURP pero nombres sí → se sobrémonta el CURP del documento.
    - Si calidad de foto es mala → se rechaza la foto.
    """
    alertas: List[str] = []
    comparaciones: Dict[str, Any] = {}
    rechazado = False

    if calidad_foto and calidad_foto != "ok":
        # Solo rechazar por calidad cuando es muy mala (brillo+borroso). Solo borroso o solo brillo = alerta pero no bloquear.
        if calidad_foto == "revisar_brillo_y_borroso":
            rechazado = True
            alertas.append("FOTO RECHAZADA: Imagen con brillo excesivo y borrosa. Vuelva a tomar la foto.")
        elif calidad_foto == "revisar_brillo":
            alertas.append("Revisar: imagen con bastante brillo o reflejo. Se recomienda retomar la foto para mejor lectura.")
        elif calidad_foto == "revisar_borroso":
            alertas.append("Revisar: imagen borrosa o desenfocada. Se recomienda retomar la foto para mejor lectura.")

    curp_definitivo = id_frente_curp
    curp_fuente = "identificacion_ocr" if id_frente_curp else None
    curp_doc = datos_curp_pdf.get("curp") if datos_curp_pdf else None

    if curp_doc and id_frente_curp:
        coincide = curp_doc == id_frente_curp
        comparaciones["curp_id_vs_documento"] = {
            "identificacion": id_frente_curp,
            "documento_curp": curp_doc,
            "coincide": coincide,
        }
        if not coincide:
            nombre_doc = datos_curp_pdf.get("nombre") if datos_curp_pdf else None
            nombre_id = id_frente_nombre
            if _nombres_coinciden(nombre_id, nombre_doc):
                curp_definitivo = curp_doc
                curp_fuente = "documento_curp (sobremontado: CURP de ID no coincide pero nombre sí)"
                alertas.append(
                    f"CURP de identificación ({id_frente_curp}) difiere del documento CURP ({curp_doc}). "
                    "Nombres coinciden → se usa el CURP del documento oficial."
                )
            else:
                alertas.append(
                    f"CURP de identificación ({id_frente_curp}) difiere del documento CURP ({curp_doc}) "
                    "y los nombres NO coinciden. Posible discrepancia de identidad."
                )
    elif curp_doc and not id_frente_curp:
        curp_definitivo = curp_doc
        curp_fuente = "documento_curp (ID no detectó CURP)"
        alertas.append("CURP no detectado en identificación. Se usa el CURP del documento oficial.")
        comparaciones["curp_id_vs_documento"] = {
            "identificacion": None,
            "documento_curp": curp_doc,
            "coincide": None,
            "nota": "CURP no detectado en ID, se usa documento",
        }

    if id_frente_nombre and id_reverso_mrz_nombre:
        # Si el "nombre" del frente son etiquetas de la credencial (CREDENCIAL, INSTITUTO, SECCION...), no es el titular
        u = (id_frente_nombre or "").upper()
        es_etiquetas_ine = sum(1 for k in ("CREDENCIAL", "INSTITUTO", "SECCION", "VIGENCIA", "NACIONAL", "ELECTORAL", "PARA VOTAR") if k in u) >= 2
        if es_etiquetas_ine:
            coincide = True
            comparaciones["nombre_frente_vs_reverso"] = {
                "frente": id_frente_nombre,
                "reverso_mrz": id_reverso_mrz_nombre,
                "coincide": True,
                "nota": "Nombre frente no legible (OCR leyó etiquetas de la credencial); se considera coincidente con MRZ.",
            }
        else:
            coincide = _nombres_coinciden(id_frente_nombre, id_reverso_mrz_nombre)
            comparaciones["nombre_frente_vs_reverso"] = {
                "frente": id_frente_nombre,
                "reverso_mrz": id_reverso_mrz_nombre,
                "coincide": coincide,
            }
            if not coincide:
                alertas.append("Nombre del frente no coincide con el nombre del MRZ (reverso).")

    if id_frente_fecha_nac_curp and id_reverso_mrz_fecha_nac:
        coincide = id_frente_fecha_nac_curp == id_reverso_mrz_fecha_nac
        comparaciones["fecha_nac_curp_vs_mrz"] = {
            "curp": id_frente_fecha_nac_curp,
            "mrz": id_reverso_mrz_fecha_nac,
            "coincide": coincide,
        }
        if not coincide:
            alertas.append("Fecha de nacimiento del CURP no coincide con la del MRZ.")

    nombre_ref = id_frente_nombre or (datos_curp_pdf.get("nombre") if datos_curp_pdf else None)

    if datos_curp_pdf and datos_curp_pdf.get("nombre") and nombre_ref:
        coincide = _nombres_coinciden(nombre_ref, datos_curp_pdf["nombre"])
        comparaciones["nombre_id_vs_curp_pdf"] = {
            "identificacion": nombre_ref,
            "documento_curp": datos_curp_pdf["nombre"],
            "coincide": coincide,
        }
        if not coincide:
            alertas.append("Nombre en identificación no coincide con nombre en constancia CURP.")

    if datos_curp_pdf:
        comparaciones["curp_pdf_frescura"] = {
            "fecha_emision": datos_curp_pdf.get("fecha_emision"),
            "es_reciente": datos_curp_pdf.get("es_reciente"),
            "meses_antiguedad": datos_curp_pdf.get("meses_antiguedad"),
        }
        if datos_curp_pdf.get("es_reciente") is False:
            alertas.append(
                f"Constancia CURP tiene {datos_curp_pdf.get('meses_antiguedad')} meses de antigüedad. "
                "Debe tener máximo 3 meses."
            )

    if datos_fiscal:
        if datos_fiscal.get("curp") and curp_definitivo:
            coincide = datos_fiscal["curp"] == curp_definitivo
            comparaciones["curp_vs_fiscal"] = {
                "curp_definitivo": curp_definitivo,
                "constancia_fiscal": datos_fiscal["curp"],
                "coincide": coincide,
            }
            if not coincide:
                alertas.append("CURP no coincide con la constancia fiscal.")
        if datos_fiscal.get("nombre") and nombre_ref:
            coincide = _nombres_coinciden(nombre_ref, datos_fiscal["nombre"])
            comparaciones["nombre_vs_fiscal"] = {
                "referencia": nombre_ref,
                "constancia_fiscal": datos_fiscal["nombre"],
                "coincide": coincide,
            }
            if not coincide:
                alertas.append("Nombre no coincide con la constancia fiscal.")

    if datos_nss:
        if datos_nss.get("curp") and curp_definitivo:
            coincide = datos_nss["curp"] == curp_definitivo
            comparaciones["curp_vs_nss"] = {
                "curp_definitivo": curp_definitivo,
                "nss_documento": datos_nss["curp"],
                "coincide": coincide,
            }
            if not coincide:
                alertas.append("CURP no coincide con el documento NSS.")
        if datos_nss.get("nombre") and nombre_ref:
            coincide = _nombres_coinciden(nombre_ref, datos_nss["nombre"])
            comparaciones["nombre_vs_nss"] = {
                "referencia": nombre_ref,
                "nss_documento": datos_nss["nombre"],
                "coincide": coincide,
            }
            if not coincide:
                alertas.append("Nombre no coincide con el documento NSS.")

    if datos_acta:
        conf_fecha = datos_acta.get("confianza_fecha", 0)
        conf_nombre = datos_acta.get("confianza_nombre", 0)
        pasadas = datos_acta.get("pasadas_ocr", 0)

        if datos_acta.get("nombre") and nombre_ref:
            coincide = _nombres_coinciden(nombre_ref, datos_acta["nombre"])
            comparaciones["nombre_vs_acta"] = {
                "referencia": nombre_ref,
                "acta_nacimiento": datos_acta["nombre"],
                "coincide": coincide,
                "confianza_ocr": conf_nombre,
            }
            if not coincide and conf_nombre >= 60:
                alertas.append("Nombre no coincide con el acta de nacimiento.")
            elif not coincide:
                alertas.append(
                    f"REVISAR: Nombre del acta difiere (confianza OCR: {conf_nombre}%). "
                    "Documento manuscrito/deteriorado, requiere revisión manual."
                )

        fecha_nac_ref = id_frente_fecha_nac_curp
        if not fecha_nac_ref and curp_definitivo:
            datos = extraer_datos_curp(curp_definitivo)
            fecha_nac_ref = datos.get("fecha_nacimiento")

        if datos_acta.get("fecha_nacimiento") and fecha_nac_ref:
            fecha_acta = datos_acta["fecha_nacimiento"]
            coincide_exacta = fecha_acta == fecha_nac_ref

            dia_mes_coincide = False
            anio_diff = None
            try:
                parts_acta = fecha_acta.split("/")
                parts_ref = fecha_nac_ref.split("/")
                dia_mes_coincide = (parts_acta[0] == parts_ref[0] and
                                    parts_acta[1] == parts_ref[1])
                anio_diff = abs(int(parts_acta[2]) - int(parts_ref[2]))
            except (IndexError, ValueError):
                pass

            if coincide_exacta:
                comparaciones["fecha_nac_vs_acta"] = {
                    "curp_fecha": fecha_nac_ref,
                    "acta_fecha": fecha_acta,
                    "coincide": True,
                    "confianza_ocr": conf_fecha,
                }
            elif dia_mes_coincide and anio_diff is not None and anio_diff <= 3:
                comparaciones["fecha_nac_vs_acta"] = {
                    "curp_fecha": fecha_nac_ref,
                    "acta_fecha": fecha_acta,
                    "coincide": True,
                    "revision_manual": True,
                    "confianza_ocr": conf_fecha,
                    "nota": (
                        f"Día y mes coinciden. Año difiere en {anio_diff} "
                        f"(posible error OCR, confianza {conf_fecha}%). "
                        "No bloquea validación."
                    ),
                }
                alertas.append(
                    f"REVISAR ACTA: Fecha {fecha_acta} vs CURP {fecha_nac_ref}. "
                    f"Día/mes coinciden, año difiere en {anio_diff}. "
                    f"Confianza OCR: {conf_fecha}%. Posible error de lectura en documento manuscrito."
                )
            else:
                comparaciones["fecha_nac_vs_acta"] = {
                    "curp_fecha": fecha_nac_ref,
                    "acta_fecha": fecha_acta,
                    "coincide": False,
                    "confianza_ocr": conf_fecha,
                }
                alertas.append(
                    f"Fecha de nacimiento del acta ({fecha_acta}) no coincide "
                    f"con CURP ({fecha_nac_ref}). Confianza OCR: {conf_fecha}%."
                )

        if pasadas > 0:
            comparaciones["acta_calidad_ocr"] = {
                "pasadas_ocr": pasadas,
                "confianza_fecha": conf_fecha,
                "confianza_nombre": conf_nombre,
                "requiere_revision_humana": conf_fecha < 70 or conf_nombre < 70,
            }

    checks_con_valor = [
        v for v in comparaciones.values()
        if isinstance(v, dict) and v.get("coincide") is not None
    ]
    total_checks = len(checks_con_valor)
    checks_ok = sum(1 for v in checks_con_valor if v.get("coincide") is True)
    todo_coincide = total_checks > 0 and checks_ok == total_checks and not rechazado

    return {
        "foto_rechazada": rechazado,
        "curp_definitivo": curp_definitivo,
        "curp_fuente": curp_fuente,
        "comparaciones": comparaciones,
        "checks_totales": total_checks,
        "checks_ok": checks_ok,
        "todo_coincide": todo_coincide,
        "alertas": alertas,
    }
