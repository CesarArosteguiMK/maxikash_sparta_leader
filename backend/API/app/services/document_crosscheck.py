# app/services/document_crosscheck.py
"""
Validación cruzada de documentos: compara CURP y nombre entre
identificación oficial, constancia CURP, constancia fiscal, NSS y acta de nacimiento.
"""
import re
import io
import unicodedata
from functools import lru_cache
from typing import Optional, Dict, Any, List, Tuple
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

try:
    from app.services.ocr_local import extraer_texto_paddle
except Exception:
    extraer_texto_paddle = None

_RAPIDOCR_ENGINE_DC = None


def pdf_paginas_a_png_bytes(pdf_bytes: bytes, dpi: int = 150, max_paginas: Optional[int] = None) -> List[bytes]:
    """Convierte cada página del PDF a bytes PNG. Para uso en verificación de ID oficial."""
    if not PYMUPDF_AVAILABLE:
        return []
    if not pdf_bytes or len(pdf_bytes) < 100:
        return []
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        out = []
        for i, page in enumerate(doc):
            if max_paginas is not None and i >= max_paginas:
                break
            pix = page.get_pixmap(dpi=dpi)
            out.append(pix.tobytes("png"))
        doc.close()
        return out
    except Exception as e:
        logger.warning(f"pdf_paginas_a_png_bytes: {e}")
        return []


def _get_rapidocr_dc():
    """Carga RapidOCR una sola vez; instanciarlo por imagen vuelve lenta la validacion."""
    global _RAPIDOCR_ENGINE_DC
    if _RAPIDOCR_ENGINE_DC is not None:
        return _RAPIDOCR_ENGINE_DC if _RAPIDOCR_ENGINE_DC is not False else None
    try:
        try:
            from rapidocr_onnxruntime import RapidOCR
        except ImportError:
            from rapidocr import RapidOCR
        _RAPIDOCR_ENGINE_DC = RapidOCR()
        return _RAPIDOCR_ENGINE_DC
    except Exception:
        _RAPIDOCR_ENGINE_DC = False
        return None


def _texto_ocr_imagen(image_png_bytes: bytes) -> Optional[str]:
    """Extrae texto de una imagen PNG: RapidOCR si está disponible, si no Tesseract."""
    if extraer_texto_paddle is not None:
        try:
            texto_paddle = extraer_texto_paddle(image_png_bytes)
            if texto_paddle and texto_paddle.strip():
                return texto_paddle
        except Exception:
            pass
    try:
        engine = _get_rapidocr_dc()
        if engine is not None:
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


def _texto_ocr_imagen_ligero(image_png_bytes: bytes, max_ancho: int = 1700) -> Optional[str]:
    """OCR rapido sin Paddle para validaciones documentales de bajo costo."""
    if TESSERACT_AVAILABLE:
        try:
            _configurar_tesseract()
            img_cv = cv2.imdecode(np.frombuffer(image_png_bytes, np.uint8), cv2.IMREAD_COLOR)
            if img_cv is not None:
                h, w = img_cv.shape[:2]
                if w > max_ancho:
                    scale = max_ancho / w
                    img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale, interpolation=cv2.INTER_AREA)
                gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
                clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8)).apply(gray)
                texto = pytesseract.image_to_string(Image.fromarray(clahe), config="--oem 3 --psm 6 -l spa+eng")
                if texto.strip():
                    return texto.strip()
        except Exception:
            pass
    try:
        engine = _get_rapidocr_dc()
        if engine is not None:
            out = engine(image_png_bytes)
            result = out[0] if isinstance(out, (list, tuple)) and len(out) > 0 else out
            if result:
                lineas = [str(item[1]).strip() for item in result if isinstance(item, (list, tuple)) and len(item) >= 2 and item[1]]
                if not lineas:
                    lineas = [item.strip() for item in result if isinstance(item, str)]
                texto = "\n".join(lineas).strip()
                if texto:
                    return texto
    except Exception:
        pass
    return None


@lru_cache(maxsize=32)
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
        imagenes = pdf_paginas_a_png_bytes(pdf_bytes, dpi=200, max_paginas=max_paginas)
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


def _parsear_fecha_sat_emision(texto: str) -> Optional[datetime]:
    """Extrae fecha de emision SAT aun cuando OCR pegue palabras o parta lineas."""
    normal = _normalizar_texto_sat(texto)
    compacto = _compactar_texto_sat(texto)
    meses = {k.upper(): v for k, v in MESES_ES.items()}
    candidatas: List[datetime] = []
    for mes_nombre, mes_num in meses.items():
        patron_normal = rf"\bA\s+([0-3O]?\d)\s+DE\s+{mes_nombre}\s+DE\s+[^0-9]{{0,55}}(\d{{4}})"
        for m in re.finditer(patron_normal, normal):
            dia_txt = m.group(1).replace("O", "0")
            try:
                fecha = datetime(int(m.group(2)), mes_num, int(dia_txt))
                if 2000 <= fecha.year <= datetime.now().year + 1:
                    candidatas.append(fecha)
            except ValueError:
                continue
        patron_compacto = rf"A([0-3O]?\d)DE{mes_nombre}DE[A-Z0-9]{{0,55}}?(\d{{4}})"
        for m in re.finditer(patron_compacto, compacto):
            dia_txt = m.group(1).replace("O", "0")
            try:
                fecha = datetime(int(m.group(2)), mes_num, int(dia_txt))
                if 2000 <= fecha.year <= datetime.now().year + 1:
                    candidatas.append(fecha)
            except ValueError:
                continue
    if candidatas:
        hoy = datetime.now()
        no_futuras = [f for f in candidatas if f <= hoy + timedelta(days=1)]
        return max(no_futuras or candidatas)
    candidatas = []
    if compacto:
        idx = compacto.find("FECHADEEMISION")
        ventana = compacto[idx:idx + 650] if idx >= 0 else compacto[:1200]
        for mes_nombre, mes_num in meses.items():
            for m in re.finditer(rf"([0-3O]?\d)DE{mes_nombre}DE[A-Z]{{0,20}}?(\d{{4}})", ventana):
                dia_txt = m.group(1).replace("O", "0")
                try:
                    fecha = datetime(int(m.group(2)), mes_num, int(dia_txt))
                    if 2000 <= fecha.year <= datetime.now().year + 1:
                        candidatas.append(fecha)
                except ValueError:
                    continue
    if candidatas:
        hoy = datetime.now()
        no_futuras = [f for f in candidatas if f <= hoy + timedelta(days=1)]
        return max(no_futuras or candidatas)

    fecha = _parsear_fecha_espanol(texto)
    if fecha:
        return fecha
    return None


def _normalizar_nombre(nombre: str) -> str:
    nombre = unicodedata.normalize("NFKD", nombre.upper().strip())
    nombre = "".join(c for c in nombre if not unicodedata.combining(c))
    nombre = re.sub(r"[^A-Z\s]", "", nombre)
    return re.sub(r"\s+", " ", nombre).strip()


def _normalizar_texto_sat(texto: str) -> str:
    if not texto:
        return ""
    texto = unicodedata.normalize("NFKD", texto.upper())
    texto = "".join(c for c in texto if not unicodedata.combining(c))
    texto = texto.replace("Ã", "A").replace("Ã‰", "E").replace("Ã", "I").replace("Ã“", "O").replace("Ãš", "U")
    return re.sub(r"\s+", " ", texto).strip()


def _compactar_texto_sat(texto: str) -> str:
    return re.sub(r"[^A-Z0-9]+", "", _normalizar_texto_sat(texto))


def _texto_parece_constancia_fiscal(texto: str) -> bool:
    normal = _normalizar_texto_sat(texto)
    compacto = _compactar_texto_sat(texto)
    if not compacto:
        return False
    return bool(
        "CONSTANCIADESITUACIONFISCAL" in compacto
        or "CEDULADEIDENTIFICACIONFISCAL" in compacto
        or ("SERVICIODEADMINISTRACIONTRIBUTARIA" in compacto and "RFC" in compacto)
        or ("HACIENDA" in normal and "RFC" in compacto and "CURP" in compacto)
    )


@lru_cache(maxsize=8)
def _texto_constancia_fiscal_pdf(pdf_bytes: bytes, max_paginas: int = 2) -> str:
    """Texto para CSF: usa capa nativa si sirve; si no, OCR rapido a baja resolucion."""
    texto = _texto_de_pdf(pdf_bytes)
    if _texto_parece_constancia_fiscal(texto):
        return texto
    if not PYMUPDF_AVAILABLE or not pdf_bytes or len(pdf_bytes) < 100:
        return texto or ""
    texto_ocr = ""
    for dpi in (120, 100):
        imagenes = pdf_paginas_a_png_bytes(pdf_bytes, dpi=dpi, max_paginas=max_paginas)
        if not imagenes:
            continue
        partes: List[str] = []
        for img_bytes in imagenes:
            t = _texto_ocr_imagen_ligero(img_bytes)
            if t:
                partes.append(t)
        texto_ocr = "\n".join(partes).strip()
        if _texto_parece_constancia_fiscal(texto_ocr):
            return texto_ocr
    return texto_ocr or texto or ""


def _nombres_coinciden(n1: str, n2: str) -> bool:
    """Compara dos nombres: coincide si comparten al menos 2 palabras significativas (>2 chars)."""
    if not n1 or not n2:
        return False
    p1 = {w for w in _normalizar_nombre(n1).split() if len(w) > 2}
    p2 = {w for w in _normalizar_nombre(n2).split() if len(w) > 2}
    comunes = p1 & p2
    if len(comunes) >= 2:
        return True

    def casi_igual_ocr(a: str, b: str) -> bool:
        if a == b:
            return True
        if min(len(a), len(b)) < 5 or abs(len(a) - len(b)) > 1:
            return False
        # OCR suele perder o agregar una letra al inicio: JIMENEZ -> IMENEZ, OSVALDO -> COSVALDO.
        if a.endswith(b) or b.endswith(a):
            return True
        if len(a) != len(b):
            return False
        diferencias = sum(1 for ca, cb in zip(a, b) if ca != cb)
        return diferencias <= 1

    usados = set(comunes)
    coincidencias = len(comunes)
    for w1 in p1 - comunes:
        for w2 in p2 - comunes:
            if w2 in usados:
                continue
            if casi_igual_ocr(w1, w2):
                usados.add(w2)
                coincidencias += 1
                break
    return coincidencias >= 2


@lru_cache(maxsize=64)
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


# Palabras / tokens que no son número de motor (CFDI, pedimento, sellos, totales)
_MOTOR_ETIQUETAS_FALSAS = frozenset({
    "PEDIMENTO", "PEDIMENTOS", "ENSAMBLE", "FECHA", "ENTRADA", "REPUVE",
    "SERIAL", "IMPORTACION", "IMPORTACIÓN", "ADUANA", "COVE", "CERTIFICADO",
    "FACTURA", "FOLIO", "NUMERO", "NÚMERO", "NÚMEROS", "MEXICO", "MÉXICO",
    "TRANSFERENCIA", "ELECTRONICA", "ELECTRÓNICA", "OBJETO", "IMPUESTO",
    "SUBTOTAL", "MXN", "USD", "RFC", "SAT", "CFDI", "PLACAS",
    "VEHICULO", "VEHÍCULO", "MOTOCICLETA", "BAJAJ", "VENTO", "HONDA",
})

_COLOR_PARTIDO_OCR = (
    (re.compile(r"(COLOR\s*:\s*)AMARILL\s*\n\s*O(\s*,\s*AÑO)", re.IGNORECASE), r"\1AMARILLO\2"),
    (re.compile(r"(COLOR\s*:\s*)AMARILL\s*\n\s*A(\s*,\s*AÑO)", re.IGNORECASE), r"\1AMARILLA\2"),
    (re.compile(r"(COLOR\s*:\s*)ROJ\s*\n\s*O(\s*,\s*AÑO)", re.IGNORECASE), r"\1ROJO\2"),
    (re.compile(r"(COLOR\s*:\s*)ROJ\s*\n\s*A(\s*,\s*AÑO)", re.IGNORECASE), r"\1ROJA\2"),
    (re.compile(r"(COLOR\s*:\s*)PLATEAD\s*\n\s*O(\s*,\s*AÑO)", re.IGNORECASE), r"\1PLATEADO\2"),
    (re.compile(r"(COLOR\s*:\s*)PLATEAD\s*\n\s*A(\s*,\s*AÑO)", re.IGNORECASE), r"\1PLATEADA\2"),
    (re.compile(r"(COLOR\s*:\s*)BLANC\s*\n\s*O(\s*,\s*AÑO)", re.IGNORECASE), r"\1BLANCO\2"),
    (re.compile(r"(COLOR\s*:\s*)BLANC\s*\n\s*A(\s*,\s*AÑO)", re.IGNORECASE), r"\1BLANCA\2"),
)

_MOTOR_LINE_LABEL = re.compile(
    r"^(NO\.?\s*DE\s*MOTOR|N[ÚU]M(?:ERO)?\s*DE\s*MOTOR)(\s*[:#\-]\s*)?(.*)$",
    re.IGNORECASE,
)

_VIN_CHARSET_RE = re.compile(r"^[A-HJ-NPR-Z0-9]+$")


def _preprocesar_texto_factura(texto: str) -> Tuple[str, str, List[str]]:
    """Normaliza Unicode; conserva saltos de línea (tablas CFDI)."""
    if not texto:
        return "", "", []
    t = unicodedata.normalize("NFKC", texto)
    t = t.replace("\ufb01", "fi").replace("\ufb02", "fl")
    texto_norm = re.sub(r"[ \t]+", " ", t).replace("\r", "\n")
    lineas = [ln.strip() for ln in texto_norm.split("\n")]
    lineas = [ln for ln in lineas if ln]
    texto_upper = texto_norm.upper()
    return texto_norm, texto_upper, lineas


def _cfdi_recortar_cola_certificado(texto_upper: str) -> str:
    """Evita tomar VIN/códigos de CADENA ORIGINAL / SELLO / firmas largas."""
    low = texto_upper.lower()
    cut = len(texto_upper)
    for marker in ("cadena original", "sello digital del cfdi"):
        i = low.find(marker)
        if i != -1 and i >= 400:
            cut = min(cut, i)
    return texto_upper[:cut] if cut < len(texto_upper) else texto_upper


def _vin_normalizado_valido(cand: str) -> bool:
    u = re.sub(r"\s+", "", cand.upper())
    if len(u) < 8 or len(u) > 17:
        return False
    return bool(_VIN_CHARSET_RE.match(u))


def _valor_motor_plausible(cand: str, vin: str) -> bool:
    """Filtra PEDIMENTO y similares; prioriza cadenas tipo KD26B007277."""
    if not cand:
        return False
    u = re.sub(r"\s+", "", cand.upper())
    if vin and u == vin:
        return False
    if u in _MOTOR_ETIQUETAS_FALSAS:
        return False
    if len(u) < 4 or len(u) > 24:
        return False
    if not re.match(r"^[A-Z0-9\-]+$", u):
        return False
    if not re.search(r"\d", u):
        return False
    if re.match(r"^\d{1,2}[/-]\d{1,2}[/-]\d{2,4}$", u):
        return False
    return True


def _extraer_vin_robusto(texto_upper_completo: str, lineas: List[str]) -> str:
    """Etiquetas + VIN de 17 + línea siguiente a SERIAL; ignora cola CFDI."""
    principal = _cfdi_recortar_cola_certificado(texto_upper_completo)
    candidatos: List[Tuple[str, int]] = []

    pats_etiqueta = [
        r"(?:NO\.?\s*DE\s*SERIE|N[ÚU]M(?:ERO)?\s*(?:DE\s*)?SERIE)\s*[:#\-]?\s*([A-HJ-NPR-Z0-9]{8,17})\b",
        r"(?:NUMERO\s*SERIAL|N[ÚU]M(?:ERO)?\s*SERIAL)\s*[:#\-]?\s*([A-HJ-NPR-Z0-9]{8,17})\b",
        r"(?:\bVIN\b|\bNIV\b)\s*[:#\-]?\s*([A-HJ-NPR-Z0-9]{8,17})\b",
        r"(?:CHASIS|CHASSIS)\s*[:#\-]?\s*([A-HJ-NPR-Z0-9]{8,17})\b",
    ]
    for pat in pats_etiqueta:
        for m in re.finditer(pat, principal, re.IGNORECASE):
            cand = re.sub(r"\s+", "", m.group(1).upper())
            if _vin_normalizado_valido(cand):
                candidatos.append((cand, 120))

    for m in re.finditer(r"\b([A-HJ-NPR-Z0-9]{17})\b", principal):
        cand = m.group(1).upper()
        candidatos.append((cand, 80))

    lineas_up = [ln.upper() for ln in lineas]
    etiqueta_serial = re.compile(
        r"^(NO\.?\s*DE\s*SERIE|N[ÚU]M(?:ERO)?\s*(?:DE\s*)?SERIE|NUMERO\s*SERIAL|N[ÚU]M(?:ERO)?\s*SERIAL|\bVIN\b|\bNIV\b)$",
        re.IGNORECASE,
    )
    for i, ln in enumerate(lineas_up):
        if etiqueta_serial.match(ln.strip()):
            for j in range(i + 1, min(i + 6, len(lineas_up))):
                for tok in re.findall(r"\b([A-HJ-NPR-Z0-9]{8,17})\b", lineas_up[j]):
                    cand = tok.upper()
                    if _vin_normalizado_valido(cand):
                        candidatos.append((cand, 100))
                        break

    if not candidatos:
        return ""

    mejor: Dict[str, int] = {}
    for cand, score in candidatos:
        mejor[cand] = max(mejor.get(cand, 0), score)

    def _prio(c: str) -> Tuple[int, int]:
        return (mejor.get(c, 0), len(c))

    return max(mejor.keys(), key=_prio)


def _extraer_motor_desde_lineas(lineas: List[str], vin: str) -> str:
    """Etiqueta NO. MOTOR + valores en líneas siguientes (tablas Vento/CFDI)."""
    lineas_up = [ln.upper() for ln in lineas]
    stop_hdr = re.compile(
        r"^(SUBTOTAL|TOTAL|IVA|CADENA\s+ORIGINAL|SELLO\s+DIGITAL|FORMA\s+DE\s+PAGO|"
        r"METODO\s+DE\s+PAGO|TIPO\s+DE\s+COMPROBANTE|OBJETO\s+DE\s+IMPUESTO)$",
        re.IGNORECASE,
    )

    for i, raw in enumerate(lineas_up):
        mm = _MOTOR_LINE_LABEL.match(raw.strip())
        if not mm:
            continue
        rest = (mm.group(3) or "").strip()
        rest_compact = re.sub(r"\s+", "", rest)
        if rest_compact and _valor_motor_plausible(rest_compact, vin):
            return rest_compact.upper()

        for j in range(i + 1, min(i + 35, len(lineas_up))):
            seg = lineas_up[j].strip()
            if not seg:
                continue
            if stop_hdr.match(seg):
                break
            if re.match(r"^\$[\d,.]+$", seg) or re.match(r"^\d{1,2}[/-]\d{1,2}[/-]\d{2,4}$", seg):
                continue
            for token in re.findall(r"\b([A-Z0-9\-]{4,24})\b", seg):
                if _valor_motor_plausible(token, vin):
                    return re.sub(r"\s+", "", token).upper()
    return ""


def _extraer_motor_tras_linea_vin(lineas: List[str], vin: str) -> str:
    """Tabla con fila VIN y motor debajo (CFDI impreso)."""
    if not vin:
        return ""
    vin_compact = vin.replace(" ", "")
    lineas_up = [ln.upper() for ln in lineas]
    for i, ln in enumerate(lineas_up):
        if vin_compact in re.sub(r"\s+", "", ln):
            for j in range(i + 1, min(i + 12, len(lineas_up))):
                for tok in re.findall(r"\b([A-Z0-9\-]{6,22})\b", lineas_up[j]):
                    if _valor_motor_plausible(tok, vin) and tok.upper() != vin.upper():
                        return re.sub(r"\s+", "", tok).upper()
    return ""


def _extraer_motor_regex(texto_upper: str, vin: str) -> str:
    pats_motor = [
        r"(?:NO\.?\s*DE\s*MOTOR|N[ÚU]M(?:ERO)?\s*DE\s*MOTOR)\s*[:#\-]?\s*([A-Z0-9\-]{4,24})(?=\s|$|[,.;])",
        r"(?:NO\.?\s*DE\s*MOTOR|N[ÚU]M(?:ERO)?\s*DE\s*MOTOR)\s*[:#\-]?\s*\n\s*([A-Z0-9\-]{6,24})\s*(?=\n|$)",
        r"(?:^|\n)\s*MOTOR\s*[:#\-]?\s*([A-Z0-9\-]{4,24})\s*(?:\n|$)",
        r"(?:NO\.?\s*MOTOR|MOTOR\s*NO\.?)\s*[:#\-]?\s*([A-Z0-9\-]{4,24})(?=\s|$|[,.;\n])",
    ]
    principal = _cfdi_recortar_cola_certificado(texto_upper)
    for pat in pats_motor:
        for m in re.finditer(pat, principal, re.IGNORECASE | re.MULTILINE):
            cand = re.sub(r"\s+", "", m.group(1).upper())
            if _valor_motor_plausible(cand, vin):
                return cand
    return ""


def _extraer_motor_fallback_post_vin(texto_upper: str, vin: str) -> str:
    """Último recurso: tokens alfanuméricos tras el VIN."""
    if not vin:
        return ""
    principal = _cfdi_recortar_cola_certificado(texto_upper)
    pos = principal.find(vin)
    if pos < 0:
        return ""
    tail = principal[pos + len(vin): pos + len(vin) + 900]
    patrones = (
        r"\b([A-Z]{1,10}\d[A-Z0-9\-]{4,19})\b",
        r"\b(\d{2,4}[A-Z]{2,6}\d{3,10}[A-Z0-9]*)\b",
        r"\b([A-Z]{2,6}\d{6,12})\b",
    )
    for pat in patrones:
        for m in re.finditer(pat, tail):
            cand = re.sub(r"\s+", "", m.group(1).upper())
            if _valor_motor_plausible(cand, vin):
                return cand
    return ""


def _fusionar_color_generico(texto_upper: str) -> str:
    """Une COLOR: prefijo + salto + sufijo,, AÑO."""
    mapa_glue = {
        ("AMARILL", "O"): "AMARILLO",
        ("AMARILL", "A"): "AMARILLA",
        ("PLATEAD", "O"): "PLATEADO",
        ("PLATEAD", "A"): "PLATEADA",
        ("CHAMPAN", "Y"): "CHAMPANA",
        ("CHAMPAN", "O"): "CHAMPANA",
    }
    out = texto_upper
    for (pre, suf), full in mapa_glue.items():
        rx = re.compile(
            rf"(COLOR\s*:\s*){pre}\s*\n\s*{suf}(\s*,\s*AÑO)",
            re.IGNORECASE,
        )
        out = rx.sub(r"\1" + full + r"\2", out)
    return out


def _normalizar_color_ocr(color: str) -> str:
    """Corrige cortes OCR comunes."""
    if not color:
        return color
    low = re.sub(r"\s+", " ", color.strip()).lower()
    mapa = {
        "amarill o": "Amarillo",
        "amarill a": "Amarilla",
        "amarillo o": "Amarillo",
        "roj o": "Rojo",
        "roj a": "Roja",
        "azul l": "Azul",
        "platead o": "Plateado",
        "platead a": "Plateada",
        "blanc o": "Blanco",
        "blanc a": "Blanca",
        "negr o": "Negro",
        "negr a": "Negra",
        "verd e": "Verde",
    }
    if low in mapa:
        return mapa[low]
    low_ns = re.sub(r"\s+", "", low)
    mapa_ns = {k.replace(" ", ""): v for k, v in mapa.items()}
    if low_ns in mapa_ns:
        return mapa_ns[low_ns]
    return color


def _extraer_color_robusto(texto_upper: str) -> str:
    """COLOR en descripción CFDI, etiqueta COLOR, catálogo (orden por longitud)."""
    colores_largo_primero = sorted([
        "PLATEADO", "PLATEADA", "AMARILLO", "AMARILLA", "NARANJA", "GRANATE",
        "NEGRO", "NEGRA", "BLANCO", "BLANCA", "ROJO", "ROJA", "AZUL",
        "GRIS", "VERDE", "MORADO", "MORADA", "DORADO", "DORADA", "CHAMPANA",
        "CAFE", "CAFÉ", "BEIGE", "VINO", "GUINDA", "PLATA",
    ], key=len, reverse=True)

    def limpiar(valor: str) -> str:
        return re.sub(r"\s+", " ", valor or "").strip()

    m_desc = re.search(
        r"(?:MARCA|MODELO|TIPO|MOTOCICLETA)[^\n]{0,220}?\bCOLOR\s*:\s*([A-ZÁÉÍÓÚÜÑ][A-ZÁÉÍÓÚÜÑ\s]{1,42}?)"
        r"(?=\s*[,;]|\s+AÑO\s|\s+ANIO\s|\n|$)",
        texto_upper,
        re.IGNORECASE | re.DOTALL,
    )
    if m_desc:
        cand = limpiar(m_desc.group(1))
        cand = re.sub(
            r"\b(MODELO|MARCA|TIPO|SERIE|VIN|MOTOR|AÑO|ANIO|PLACAS|FACTURA|FOLIO)\b.*$",
            "",
            cand,
            flags=re.IGNORECASE,
        )
        cand = limpiar(cand)
        if cand and re.match(r"^[A-ZÁÉÍÓÚÜÑ\s]+$", cand, re.IGNORECASE):
            return _normalizar_color_ocr(cand.title())

    m_color = re.search(
        r"(?:\bCOLOR\b)\s*[:#\-]?\s*([A-ZÁÉÍÓÚÜÑ][A-ZÁÉÍÓÚÜÑ\s]{1,42}?)"
        r"(?=\s*[,.;\n]|\s+(?:AÑO|ANIO|MODELO|MARCA|TIPO|MOTOR|SERIE)\b|$)",
        texto_upper,
        re.IGNORECASE | re.DOTALL,
    )
    if m_color:
        cand = limpiar(m_color.group(1))
        cand = re.sub(
            r"\b(MODELO|SERIE|VIN|MOTOR|AÑO|ANIO|PLACAS|FACTURA|FOLIO)\b.*$",
            "",
            cand,
            flags=re.IGNORECASE,
        )
        cand = limpiar(cand)
        if cand and re.match(r"^[A-ZÁÉÍÓÚÜÑ\s]+$", cand, re.IGNORECASE):
            return _normalizar_color_ocr(cand.title())

    tu = texto_upper.replace("Á", "A").replace("É", "E").replace("Í", "I").replace("Ó", "O").replace("Ú", "U")
    for c in colores_largo_primero:
        if re.search(rf"\b{re.escape(c)}\b", tu):
            return c.title()

    return ""


def extraer_datos_motocicleta_factura(file_bytes: bytes, filename: str = "") -> Dict[str, Any]:
    """
    Extrae VIN, No. de Motor y Color desde FACTURA (PDF o imagen).
    Pensado para CFDI (Vento, Bajaj, etc.), tablas con encabezados y OCR imperfecto.
    """
    out: Dict[str, Any] = {
        "encontrado": False,
        "vin": None,
        "no_motor": None,
        "color": None,
        "texto_fuente": "",
    }
    if not file_bytes:
        return out

    nombre = (filename or "").lower()
    if nombre.endswith(".pdf"):
        texto = texto_de_pdf_con_ocr(file_bytes, max_paginas=5)
    elif nombre.endswith((".txt", ".text")):
        try:
            texto = file_bytes.decode("utf-8")
        except UnicodeDecodeError:
            texto = file_bytes.decode("latin-1", errors="replace")
    else:
        texto = _texto_ocr_imagen(file_bytes) or ""

    if not texto:
        return out

    texto_norm, texto_upper, lineas = _preprocesar_texto_factura(texto)

    for rx, repl in _COLOR_PARTIDO_OCR:
        texto_upper = rx.sub(repl, texto_upper)
    texto_upper = _fusionar_color_generico(texto_upper)

    vin = _extraer_vin_robusto(texto_upper, lineas)

    no_motor = _extraer_motor_desde_lineas(lineas, vin)
    if not no_motor:
        no_motor = _extraer_motor_tras_linea_vin(lineas, vin)
    if not no_motor:
        no_motor = _extraer_motor_regex(texto_upper, vin)
    if not no_motor:
        no_motor = _extraer_motor_fallback_post_vin(texto_upper, vin)

    color = _extraer_color_robusto(texto_upper)

    if vin:
        out["vin"] = vin
    if no_motor:
        out["no_motor"] = no_motor
    if color:
        out["color"] = color
    out["encontrado"] = bool(vin or no_motor or color)
    out["texto_fuente"] = texto_norm[:2500]
    return out
def _normalizar_texto_nss(texto: str) -> str:
    t = (texto or "").upper()
    reemplazos = {
        "Ã": "A", "Ã‰": "E", "Ã": "I", "Ã“": "O", "Ãš": "U", "Ã‘": "N",
        "Ã¡": "A", "Ã©": "E", "Ã­": "I", "Ã³": "O", "Ãº": "U", "Ã±": "N",
    }
    for src, dst in reemplazos.items():
        t = t.replace(src, dst)
    t = unicodedata.normalize("NFKD", t)
    t = "".join(c for c in t if not unicodedata.combining(c))
    return re.sub(r"\s+", " ", t).strip()


def es_tarjeta_nss(pdf_bytes: bytes) -> bool:
    t = _normalizar_texto_nss(texto_de_pdf_con_ocr(pdf_bytes))
    if not t:
        return False
    if "INSTITUTO MEXICANO DEL SEGURO SOCIAL" not in t and "IMSS" not in t:
        return False
    return (
        "IMPRIME Y RECORTA" in t
        or "ASIGNACION O LOCALIZACION" in t
        or "TU NUMERO DE SEGURIDAD SOCIAL ES" in t
    )


def es_documento_nss(pdf_bytes: bytes) -> bool:
    """True si el PDF es uno de los tres formatos aceptados del IMSS: (1) vigencia de derechos,
    (2) constancia de asignación/homoclave NSS, (3) constancia de semanas cotizadas. No acepta la tarjeta NSS (imprimir/recortar).
    """
    if _texto_parece_constancia_fiscal(_texto_de_pdf(pdf_bytes)):
        return False
    t = _normalizar_texto_nss(texto_de_pdf_con_ocr(pdf_bytes))
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
    if not t.strip():
        t = texto_de_pdf_con_ocr(pdf_bytes, max_paginas=2).upper()
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
    texto = _texto_de_pdf(pdf_bytes)
    if texto.strip():
        return _texto_parece_constancia_fiscal(texto)
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


def _texto_parece_acta_nacimiento(texto: str) -> bool:
    """True si un texto OCR parece acta/certificado de nacimiento."""
    t = (texto or "").upper()
    t = unicodedata.normalize("NFKD", t)
    t = "".join(c for c in t if not unicodedata.combining(c))
    if not t:
        return False
    if "ACTA DE NACIMIENTO" in t or "CERTIFICADO DE NACIMIENTO" in t:
        return True
    if ("ACTA" in t or "CERTIFICADO" in t or "REGISTRO CIVIL" in t) and "NACIMIENTO" in t:
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
    if not texto.strip():
        texto = texto_de_pdf_con_ocr(pdf_bytes, max_paginas=2)

    lineas = [ln.strip() for ln in texto.split("\n") if ln.strip()]

    compacto = texto.upper().replace(" ", "").replace("\n", "")
    for m in re.finditer(r"[A-Z]{4}[A-Z0-9]{14}", compacto):
        cand = m.group()[:18]
        if len(cand) == 18 and validar_curp(cand)[0]:
            resultado["curp"] = cand
            break
    if not resultado["curp"]:
        texto_ocr = texto_de_pdf_con_ocr(pdf_bytes, max_paginas=2)
        if texto_ocr and texto_ocr.strip() and texto_ocr != texto:
            texto = texto + "\n" + texto_ocr
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
        "parece_constancia_fiscal": False,
    }
    if not PYMUPDF_AVAILABLE:
        return resultado
    texto = ""
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        texto_nativo = ""
        for page in doc:
            texto_nativo += page.get_text() + "\n"
        doc.close()
        texto = texto_nativo
    except Exception:
        texto = ""
    if not _texto_parece_constancia_fiscal(texto):
        texto_ocr = _texto_constancia_fiscal_pdf(pdf_bytes)
        if texto_ocr.strip():
            texto = texto_ocr
    resultado["parece_constancia_fiscal"] = _texto_parece_constancia_fiscal(texto)
    if not texto.strip():
        return resultado

    lineas = [ln.strip() for ln in texto.split("\n")]
    texto_upper = texto.upper()
    texto_norm = _normalizar_texto_sat(texto_upper)
    texto_compacto = _compactar_texto_sat(texto_upper)

    curp_m = re.search(r"CURP:\s*\n?\s*([A-Z0-9]{18})", texto, re.IGNORECASE)
    if curp_m:
        cand = curp_m.group(1).upper()
        if validar_curp(cand)[0]:
            resultado["curp"] = cand
    if not resultado["curp"]:
        for m in re.finditer(r"[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9]{2}", texto_compacto):
            cand = m.group(0).upper()
            if validar_curp(cand)[0]:
                resultado["curp"] = cand
                break

    rfc_m = re.search(r"RFC:\s*\n?\s*([A-Z0-9]{12,13})", texto, re.IGNORECASE)
    if rfc_m:
        resultado["rfc"] = rfc_m.group(1).upper()
    if not resultado["rfc"]:
        m = re.search(r"RFC([A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3})", texto_compacto)
        if not m:
            m = re.search(r"\b([A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3})\b", texto_norm)
        if m:
            resultado["rfc"] = m.group(1).upper()

    nombre_val = apellido1 = apellido2 = None
    m_nombre_inline = re.search(
        r"NOMBRE\s*\(?S?\)?\s*:\s*([A-ZÑ\s]{2,50}?)\s+PRIMER\s+APELLIDO\s*:\s*([A-ZÑ\s]{2,50}?)\s+SEGUNDO\s+APELLIDO\s*:\s*([A-ZÑ\s]{2,50}?)(?=\s+FECHA|\s+ESTATUS|\s+NOMBRE\s+COMERCIAL|\s+DATOS|$)",
        texto_norm,
    )
    if m_nombre_inline:
        nombre_val = m_nombre_inline.group(1).strip()
        apellido1 = m_nombre_inline.group(2).strip()
        apellido2 = m_nombre_inline.group(3).strip()
    for i, ln in enumerate(lineas):
        if re.match(r"Nombre\s*\(?s?\)?:", ln, re.IGNORECASE):
            m_inline = re.match(r"Nombre\s*\(?s?\)?:\s*(.+)$", ln, re.IGNORECASE)
            if m_inline and m_inline.group(1).strip():
                nombre_val = m_inline.group(1).strip()
            elif i + 1 < len(lineas):
                nombre_val = lineas[i + 1].strip()
        elif re.match(r"Primer\s+Apellido:", ln, re.IGNORECASE):
            m_inline = re.match(r"Primer\s+Apellido:\s*(.+)$", ln, re.IGNORECASE)
            if m_inline and m_inline.group(1).strip():
                apellido1 = m_inline.group(1).strip()
            elif i + 1 < len(lineas):
                apellido1 = lineas[i + 1].strip()
        elif re.match(r"Segundo\s+Apellido:", ln, re.IGNORECASE):
            m_inline = re.match(r"Segundo\s+Apellido:\s*(.+)$", ln, re.IGNORECASE)
            if m_inline and m_inline.group(1).strip():
                apellido2 = re.split(r"\bFecha\b|\bEstatus\b|\bNombre\s+Comercial\b|\bDatos\b", m_inline.group(1), flags=re.IGNORECASE)[0].strip()
            elif i + 1 < len(lineas):
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

    fecha = _parsear_fecha_sat_emision(texto)
    if fecha:
        resultado["fecha_emision"] = fecha.strftime("%d/%m/%Y")
        hoy = datetime.now()
        diff = hoy - fecha
        meses = diff.days / 30.44
        resultado["meses_antiguedad"] = round(meses, 1)
        resultado["es_reciente"] = meses <= 2.0
        resultado["vigencia_ok"] = meses <= 2.0

    # Actividad Económica: debe decir "Asalariado" (tabla Actividades Económicas, normalmente pág 2)
    if "ASALARIADO" in texto_norm or "ASALARIADO" in texto_compacto:
        resultado["actividad_economica_asalariado"] = True

    # Régimen: debe incluir "Régimen de Sueldos y Salarios e Ingresos Asimilados a Salarios"
    if "SUELDOS Y SALARIOS" in texto_norm or "SUELDOS Y SALARIOS E INGRESOS ASIMILADOS" in texto_norm or "SUELDOSYSALARIOS" in texto_compacto:
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


def _ocr_acta_rapida(img_cv: np.ndarray, lang: str = "spa") -> List[str]:
    """OCR corto para el boton de acta: pocas pasadas, suficiente para validar sin esperar minutos."""
    textos = []
    h, w = img_cv.shape[:2]
    if w > 1800:
        scale = 1800 / w
        img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale, interpolation=cv2.INTER_AREA)
    for cfg in ({"nombre": "clahe_psm6", "psm": 6}, {"nombre": "otsu_psm6", "psm": 6}):
        try:
            procesada = _preprocesar_imagen(img_cv, cfg["nombre"])
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
_PALABRAS_ETIQUETA_ACTA = {
    "NOMBRE", "NOMBRES", "PRIMER", "SEGUNDO", "APELLIDO", "APELLIDOS",
    "NACIONALIDAD", "CURP", "PERSONA", "REGISTRADA", "FILIACION", "DATOS",
    "MEXICANA", "MEXICANO", "SEXO", "FECHA", "NACIMIENTO",
}
_TERMINADORES_NOMBRE_ACTA = {"MEXICANA", "MEXICANO", "NACIONALIDAD", "CURP", "SEXO", "FECHA"}


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
    def limpiar_candidato(raw: str) -> Optional[str]:
        limpio = _normalizar_nombre(raw)
        palabras = limpio.split()
        filtradas = []
        etiquetas = 0
        for p in palabras:
            if p in _TERMINADORES_NOMBRE_ACTA:
                break
            if p in _PALABRAS_ETIQUETA_ACTA:
                etiquetas += 1
                continue
            if p in _STOP_WORDS_ACTA:
                break
            if len(p) >= 3:
                filtradas.append(p)
        if len(filtradas) < 2 or etiquetas >= 2:
            return None
        return " ".join(filtradas[:5])

    # En actas mexicanas escaneadas, el valor suele estar en la línea anterior
    # a las etiquetas "NOMBRE(S) / PRIMER APELLIDO / SEGUNDO APELLIDO".
    lineas = [ln.strip() for ln in texto.splitlines() if ln.strip()]
    for idx, linea in enumerate(lineas):
        linea_norm = _normalizar_nombre(linea)
        if "NOMBRE" in linea_norm and "APELLIDO" in linea_norm:
            for prev_idx in range(idx - 1, max(-1, idx - 4), -1):
                candidato = limpiar_candidato(lineas[prev_idx])
                if candidato:
                    return candidato

    for pat in _PATRONES_NOMBRE_ACTA:
        m = re.search(pat, texto)
        if m:
            candidato = limpiar_candidato(m.group(1).strip())
            if candidato:
                return candidato
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


def extraer_datos_acta_nacimiento(pdf_bytes: bytes, modo_rapido: bool = False) -> Dict[str, Any]:
    """Extrae nombre y fecha de nacimiento del acta de nacimiento.
    Usa OCR multi-pasada con votación: ejecuta múltiples estrategias de
    preprocesamiento, extrae fechas/nombres de cada una, y el valor con
    más votos gana. Esto compensa errores aleatorios del OCR.
    """
    resultado = {
        "nombre": None, "fecha_nacimiento": None, "texto_extraido": False,
        "confianza_fecha": 0, "confianza_nombre": 0, "pasadas_ocr": 0,
        "parece_acta": False,
    }

    if not PYMUPDF_AVAILABLE:
        logger.warning("PyMuPDF no disponible para acta de nacimiento")
        return resultado

    try:
        _configurar_tesseract()
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        imagenes_cv: List[np.ndarray] = []
        texto_digital = ""

        max_paginas = 1 if modo_rapido else doc.page_count
        for i, page in enumerate(doc):
            if i >= max_paginas:
                break
            txt = page.get_text()
            if txt.strip() and len(txt.strip()) > 30:
                texto_digital += txt + "\n"
                continue

            if not TESSERACT_AVAILABLE:
                logger.warning("Tesseract no disponible para OCR de acta escaneada")
                continue

            imgs = page.get_images(full=True)
            if not imgs:
                pix = page.get_pixmap(dpi=170 if modo_rapido else 300)
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
                    if modo_rapido and w > 1800:
                        scale = 1800 / w
                        img_cv = cv2.resize(img_cv, None, fx=scale, fy=scale,
                                            interpolation=cv2.INTER_AREA)
                    elif w < 1200:
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
        resultado["parece_acta"] = _texto_parece_acta_nacimiento(texto_digital)
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
    textos_ocr: List[str] = []
    total_pasadas = 0

    for img_cv in imagenes_cv:
        textos = _ocr_acta_rapida(img_cv) if modo_rapido else _ocr_multi_pasada(img_cv)
        textos_ocr.extend(textos)
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
    resultado["parece_acta"] = _texto_parece_acta_nacimiento("\n".join(textos_ocr))
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
    nombre_candidato_registro: Optional[str] = None,
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
                curp_fuente = "documento_curp (sobremontado: OCR de ID difiere pero nombre si)"
                comparaciones["curp_id_vs_documento"]["coincide"] = True
                comparaciones["curp_id_vs_documento"]["advertencia_ocr"] = True
                comparaciones["curp_id_vs_documento"]["nota"] = (
                    "La CURP leída en la identificación difiere del PDF oficial, "
                    "pero los nombres coinciden. Se toma la CURP del documento oficial."
                )
                # No se agrega a alertas: queda resuelto usando el PDF oficial cuando el nombre coincide.
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

    nombre_ref = id_reverso_mrz_nombre or id_frente_nombre or (datos_curp_pdf.get("nombre") if datos_curp_pdf else None)

    if nombre_candidato_registro and nombre_ref:
        coincide = _nombres_coinciden(nombre_candidato_registro, nombre_ref)
        comparaciones["nombre_registro_vs_documentos"] = {
            "registro_candidato": nombre_candidato_registro,
            "documentos": nombre_ref,
            "coincide": coincide,
        }
        if not coincide:
            alertas.append("Nombre registrado del candidato no coincide con el nombre leído en los documentos.")

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

        if datos_acta.get("nombre") and nombre_candidato_registro:
            coincide = _nombres_coinciden(nombre_candidato_registro, datos_acta["nombre"])
            comparaciones["nombre_registro_vs_acta"] = {
                "registro_candidato": nombre_candidato_registro,
                "acta_nacimiento": datos_acta["nombre"],
                "coincide": coincide,
                "confianza_ocr": conf_nombre,
            }
            if not coincide and conf_nombre >= 60:
                alertas.append("Nombre registrado del candidato no coincide con el acta de nacimiento.")
            elif not coincide:
                alertas.append(
                    f"REVISAR: Nombre registrado no coincide claramente con el acta "
                    f"(confianza OCR: {conf_nombre}%)."
                )

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
