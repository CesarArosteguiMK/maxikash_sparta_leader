# app/services/__SPARTA_SECRET_REDACTED___analyzer.py
"""
Valida PDF de estado de cuenta bancario.

- En subida: la única validación para ACEPTAR el documento es que sea banco físico (no digital).
  Si el PDF es escaneado (sin capa de texto), se usa OCR de inmediato: RapidOCR si está instalado
  (pip install rapidocr-onnxruntime), si no Tesseract, para poder decidir aceptar/rechazar.
- Una vez subido: la respuesta se guarda (verificacion_calidad_json) y en el tooltip se muestra
  la validación profunda: nombre del banco, nombre del dueño del estado de cuenta, y si es el mismo
  nombre que el del ticket.
"""
import re
import io
from typing import Optional, Dict, Any, List, Tuple
from loguru import logger

try:
    import fitz
    PYMUPDF_AVAILABLE = True
except ImportError:
    PYMUPDF_AVAILABLE = False

# RapidOCR opcional para PDFs escaneados (sin texto extraíble)
_RAPIDOCR_EC: Any = None

def _get_rapidocr_ec():
    global _RAPIDOCR_EC
    if _RAPIDOCR_EC is not None:
        return _RAPIDOCR_EC if _RAPIDOCR_EC is not False else None
    try:
        try:
            from rapidocr_onnxruntime import RapidOCR
        except ImportError:
            from rapidocr import RapidOCR
        _RAPIDOCR_EC = RapidOCR()
        logger.info("RapidOCR cargado para estado de cuenta (PDF escaneado)")
        return _RAPIDOCR_EC
    except ImportError:
        _RAPIDOCR_EC = False
        return None


def _texto_ocr_imagen(image_png_bytes: bytes) -> Optional[str]:
    """Extrae texto de una imagen PNG: RapidOCR o Tesseract."""
    engine = _get_rapidocr_ec()
    if engine:
        try:
            out = engine(image_png_bytes)
            result = out[0] if isinstance(out, (list, tuple)) and len(out) > 0 else out
            if not result:
                return None
            lineas = [str(item[1]).strip() for item in result if isinstance(item, (list, tuple)) and len(item) >= 2 and item[1]]
            if not lineas:
                lineas = [item.strip() for item in result if isinstance(item, str)]
            return "\n".join(lineas) if lineas else None
        except Exception as e:
            logger.debug(f"RapidOCR __SPARTA_SECRET_REDACTED__: {e}")
    try:
        import pytesseract
        from PIL import Image
        img = Image.open(io.BytesIO(image_png_bytes))
        if img.mode != "RGB":
            img = img.convert("RGB")
        return pytesseract.image_to_string(img, config="--oem 3 --psm 3 -l spa+eng").strip() or None
    except Exception as e:
        logger.debug(f"Tesseract __SPARTA_SECRET_REDACTED__: {e}")
        return None


# Bancos físicos permitidos (México). Coincidencia por nombre o patrones en el texto.
BANCOS_FISICOS_PATRONES: List[Dict[str, Any]] = [
    {"nombre": "BBVA", "patrones": [r"BBVA", r"BANCO\s+BBVA", r"BBVA\s+BANCOMER"]},
    {"nombre": "Banorte", "patrones": [r"BANORTE", r"BANCO\s+BANORTE", r"GRUPO\s+FINANCIERO\s+BANORTE"]},
    {"nombre": "Santander", "patrones": [r"SANTANDER", r"BANCO\s+SANTANDER"]},
    {"nombre": "Citibanamex", "patrones": [r"CITIBANAMEX", r"CITI\s+BANAMEX", r"BANAMEX"]},
    {"nombre": "Scotiabank", "patrones": [r"SCOTIABANK", r"SCOTIA\s+INVERLAT"]},
    {"nombre": "HSBC", "patrones": [r"HSBC", r"BANCO\s+HSBC"]},
    {"nombre": "Banco Azteca", "patrones": [r"BANCO\s+AZTECA", r"AZTECA"]},
    {"nombre": "Banco del Bienestar", "patrones": [r"BANCO\s+DEL\s+BIENESTAR", r"BIENESTAR"]},
    {"nombre": "Bancoppel", "patrones": [r"BANCOPPEL", r"BANCO\s+COPPEL"]},
    {"nombre": "Afirme", "patrones": [r"AFIRME", r"BANCO\s+AFIRME"]},
    {"nombre": "Banregio", "patrones": [r"BANREGIO", r"BANCO\s+BANREGIO"]},
    {"nombre": "Inbursa", "patrones": [r"INBURSA", r"BANCO\s+INBURSA"]},
    {"nombre": "Banco Regional", "patrones": [r"BANCO\s+REGIONAL", r"REGIONAL\s+BANCO"]},
    {"nombre": "Mifel", "patrones": [r"MIFEL", r"BANCO\s+MIFEL"]},
]

# Bancos / fintechs digitales NO permitidos para estado de cuenta.
BANCOS_DIGITALES_PATRONES: List[str] = [
    r"\bNU\s+BANCO\b", r"\bNU\s+BANK\b", r"\bNUBANK\b", r"\bNU\s*BANCO\b",
    r"\bUAL[ÁA]\b", r"\bUALA\b", r"\bUALA\s+BANCO\b",
    r"\bKLAR\b", r"\bKLAR\s+FINANCIERA\b",
    r"\bSTORI\b", r"\bSTORI\s+CARD\b",
    r"\bRAPPI\s+CARD\b", r"\bRAPPIPAY\b",
    r"\bMERCADO\s+PAGO\b", r"\bMERCADOPAGO\b",
    r"\bDIDI\s+PAY\b", r"\bDIDI\s+CASH\b",
    r"\bFAMSO\b", r"\bALBO\b", r"\bVEXI\b",
    r"\bBAIT\b", r"\bCUENCA\b", r"\bMANDARINA\b",
]

# Patrones que sugieren "hoja de datos del titular" (nombre, dirección, CLABE, cuenta).
PATRONES_DATOS_TITULAR: List[str] = [
    r"NOMBRE\s*(?:DEL\s+)?TITULAR",
    r"TITULAR\s*(?:DE\s+LA\s+)?CUENTA",
    r"DOMICILIO\s*(?:FISCAL)?\s*:",
    r"DIRECCI[OÓ]N\s*:",
    r"CLABE\s*(?:INTERBANCARIA)?\s*:?\s*\d{18}",
    r"CLABE\s*[:\s]",
    r"N[UÚ]MERO\s+DE\s+CUENTA\s*[:\s]?\s*\d{10,}",
    r"CUENTA\s+BANCARIA\s*[:\s]",
    r"RFC\s*[:\s]",
    r"CURP\s*[:\s]",
    r"ESTADO\s+DE\s+CUENTA\s+.*(?:TITULAR|NOMBRE|DOMICILIO)",
    r"DATOS\s+DE\s+CLIENTE|DATOSDECLIENTE",
    r"NOMBRE[S]?\s+COMPLETO[S]?\s+COMO\s+APARECE\s+EN\s+LA\s+IDENTIFICACI[OÓ]N",
]

# Patrones para extraer nombre del propietario del estado de cuenta.
# Prioridad: sección DATOS DE CLIENTE (carátula Banorte, etc.) para no confundir con beneficiario.
PATRONES_NOMBRE_PROPIETARIO: List[str] = [
    # Carátula Banorte / formatos similares: "DATOS DE CLIENTE" ... "Nombre(s) completo(s) como aparece en la Identificación" → siguiente línea
    r"DATOS\s+DE\s+CLIENTE[\s\S]{0,400}?NOMBRE[S]?\s+COMPLETO[S]?\s+COMO\s+APARECE\s+EN\s+LA\s+IDENTIFICACI[OÓ]N\s*[:\s]*\n\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑa-záéíóúñ\s]{4,60})",
    r"CLIENTE[\s\S]{0,200}?NOMBRE[S]?\s+COMPLETO[S]?\s+COMO\s+APARECE[\s\S]{0,100}?\n\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{8,55})\s*\n",
    # Misma etiqueta en una sola línea (OCR): "Nombre completo como aparece... MARCO ANTONIO NUÑEZ"
    r"NOMBRE[S]?\s+COMPLETO[S]?\s+COMO\s+APARECE\s+EN\s+LA\s+IDENTIFICACI[OÓ]N\s*[:\s]+([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑa-záéíóúñ\s]{4,60})(?=\s*\n|$)",
    # BBVA estilo: R.F.C XXXXX luego línea con nombre en mayúsculas
    r"R\.?F\.?C\.?\s*[A-Z0-9]+\s*\n\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{10,55})\s*\n",
    # Etiqueta explícita (Nombre del Titular:, Titular:, etc.)
    r"(?:NOMBRE\s*(?:DEL\s+)?TITULAR|TITULAR\s*(?:DE\s+LA\s+)?CUENTA|CUENTA\s+A\s+NOMBRE\s+DE)\s*[:\s]+\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑa-záéíóúñ\s]{4,60})",
    r"(?:TITULAR|PROPIETARIO|NOMBRE)\s*[:\s]+\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑa-záéíóúñ\s]{4,60})",
    # Nombre en línea sola entre datos del banco y dirección (ej. LAZARO RAUDEL GONZALEZ LEYVA)
    r"(?:CLABE|R\.F\.C|RFC|NO\.\s+DE\s+CUENTA)[\s\S]{0,300}?\n\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{8,55})\s*\n\s*(?:\d{2,4}[A-Z]?|\d+\s|SUCURSAL|DIRECCION|CALZ\.|CALLE|AV\.|COL\.)",
    # Línea en mayúsculas seguida de número de dirección o CP
    r"\n\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{10,50})\s*\n\s*(?:\d{2,4}[A-Z]?\s|\d{5}|CP\s|SUCURSAL|CALZ\.|CALLE)",
    r"([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?:\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+){2,})\s*(?:\n|$|\s+CLABE|\s+DOMICILIO|\s+RFC)",
]

# Cadenas que no son nombres de persona (descartar al extraer propietario)
NO_ES_NOMBRE: List[str] = [
    "FECHA DE CORTE", "FECHA DE PAGO", "LIMITE DE PAGO", "CORTE A PARTIR", "FECHA", "CORTE",
    "ESTADO DE CUENTA", "RESUMEN DE", "TARJETA DE", "NUMERO DE CUENTA",
    "CLABE", "DOMICILIO", "RFC", "CURP", "SALDO", "TOTAL", "SUBTOTAL",
    "BANCO", "BANCOMER", "BBVA", "SANTANDER", "BANORTE",
    "REPRESENTACION", "PROPORCIONEN", "MECANISMO", "TRANS", "TRAVES", "DE LA", "DEL ",
]


def _normalizar_nombre_ocr(nombre: str) -> str:
    """Convierte nombre en mayúsculas concatenadas (OCR) a palabras: MARCOANTONIONUNEZGONZALEZ -> MARCO ANTONIO NUÑEZ GONZALEZ."""
    s = nombre.strip()
    if not s or " " in s or len(s) < 10:
        return s
    if not re.match(r"^[A-ZÁÉÍÓÚÑ]+$", s):
        return s
    vocales = set("AEIOUÁÉÍÓÚ")
    out = []
    run = 0
    for i, c in enumerate(s):
        if i > 0 and (c.isupper() or c in "ÁÉÍÓÚÑ"):
            prev = s[i - 1]
            # Vocal + consonante (nueva palabra): 6+ letras, pero no si la siguiente es Z (evitar GONZALE|Z)
            if run >= 6 and prev in vocales and c not in vocales and c != "Z":
                out.append(" ")
                run = 0
            # Vocal + vocal: solo partir si palabra tiene 5 letras (MARCO|A), no en medio (ANTONIO)
            elif run == 5 and prev in vocales and c in vocales:
                out.append(" ")
                run = 0
            elif run >= 5 and prev in "ZÑ":
                out.append(" ")
                run = 0
        out.append(c)
        run += 1
    return "".join(out).strip()


def _texto_de_pdf(pdf_bytes: bytes, max_paginas: int = 3) -> str:
    """Extrae texto de las primeras páginas del PDF. Si no hay capa de texto (escaneado), usa OCR."""
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
        # PDF sin capa de texto (escaneado): convertir páginas a imagen y usar OCR
        from app.services.document_crosscheck import pdf_paginas_a_png_bytes
        imagenes = pdf_paginas_a_png_bytes(pdf_bytes, dpi=200, max_paginas=max_paginas)
        if not imagenes:
            return ""
        for img_bytes in imagenes[:max_paginas]:
            t = _texto_ocr_imagen(img_bytes)
            if t:
                texto += t + "\n"
        return texto
    except Exception as e:
        logger.warning(f"__SPARTA_SECRET_REDACTED___analyzer: error leyendo PDF: {e}")
        return ""


def _detectar_banco(texto: str) -> Tuple[Optional[str], bool]:
    """
    Detecta nombre del banco y si es físico (True) o digital (False).
    Returns (nombre_banco, es_fisico). Si no detecta, (None, False).
    """
    # Primero comprobar si es banco digital (rechazar)
    for pat in BANCOS_DIGITALES_PATRONES:
        m = re.search(pat, texto, re.IGNORECASE)
        if m:
            nombre = m.group(0).strip() if m else "Banco digital"
            return (nombre, False)
    # Luego buscar banco físico
    for banco in BANCOS_FISICOS_PATRONES:
        for pat in banco["patrones"]:
            if re.search(pat, texto, re.IGNORECASE):
                return (banco["nombre"], True)
    return (None, False)


def _tiene_datos_titular(texto: str) -> bool:
    """Indica si el texto incluye sección típica de datos del titular (nombre, dirección, CLABE/cuenta)."""
    if not texto or len(texto.strip()) < 50:
        return False
    coincidencias = 0
    for pat in PATRONES_DATOS_TITULAR:
        if re.search(pat, texto, re.IGNORECASE):
            coincidencias += 1
    return coincidencias >= 2


def _extraer_nombre_propietario(texto: str) -> Optional[str]:
    """Extrae el nombre del propietario/titular del estado de cuenta."""
    if not texto or len(texto.strip()) < 20:
        return None

    def _es_nombre_valido(nombre: str) -> bool:
        nombre_upper = nombre.upper().strip()
        if len(nombre) < 6:
            return False
        if len(nombre.split()) > 6:
            return False
        for no in NO_ES_NOMBRE:
            if no in nombre_upper or nombre_upper.startswith(no) or nombre_upper.endswith(no):
                return False
        if re.search(r"CLABE|DOMICILIO|RFC|CURP|COL\.|C\.P\.|AV\.|CALLE|\d{5}|\d{2}/\d{2}/\d{4}", nombre, re.IGNORECASE):
            return False
        return True

    def _limpiar_y_validar(nombre: str) -> Optional[str]:
        nombre = re.sub(r"\s+", " ", nombre.strip())
        nombre = _normalizar_nombre_ocr(nombre)  # MARCOANTONIONUNEZGONZALEZ -> MARCO ANTONIO NUÑEZ GONZALEZ
        if len(nombre) > 80:
            nombre = nombre[:80].strip()
        return nombre if _es_nombre_valido(nombre) else None

    # 1) Preferir nombre en sección DATOS DE CLIENTE (carátula Banorte, etc.) para no tomar al beneficiario
    bloque_cliente = ""
    m_dat = re.search(r"DATOS\s*DE\s*CLIENTE|DATOSDECLIENTE", texto, re.IGNORECASE)
    m_ben = re.search(r"BENEFICIARIO[S]?\s*DESIGNADO|INFORMACI[OÓ]N\s*DE\s*LAS\s*OPERACIONES|INFORMACIONDELASOPERACIONES", texto, re.IGNORECASE)
    if m_dat:
        start = m_dat.start()
        end = m_ben.start() if m_ben and m_ben.start() > start else len(texto)
        bloque_cliente = texto[start:end]
    if bloque_cliente:
        for pat in [
            # OCR: *Nombre(s) completo(s) como aparece en la ldentificacion + líneas hasta nombre en MAYÚSCULAS sin espacios
            r"\*?NOMBRE\s*\(?s\)?\s*COMPLETO\s*\(?s\)?\s*COMO\s+APARECE\s+EN\s+LA\s+(?:I[D]?ENTIFICACI[OÓ]N|[lI]dentificacion)[\s\S]{0,250}?\n\s*([A-ZÁÉÍÓÚÑ]{10,60})\s*\n",
            r"NOMBRE[S]?\s*COMPLETO[S]?\s*COMO\s+APARECE\s+EN\s+LA\s+(?:I[D]?ENTIFICACI[OÓ]N|[lI]dentificacion)[\s\S]{0,250}?\n\s*([A-ZÁÉÍÓÚÑ]{10,60})\s*\n",
            # Nombre en línea siguiente directa (con o sin espacios)
            r"NOMBRE[S]?\s*COMPLETO[S]?\s*COMO\s+APARECE\s+EN\s+LA\s+(?:I[D]?ENTIFICACI[OÓ]N|ldentificacion)\s*[:\s]*\n\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{4,60})\s*\n",
            r"NOMBRE[S]?\s*COMPLETO[S]?\s*COMO\s+APARECE[\s\S]{0,80}?\n\s*([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{8,55})\s*\n",
            r"NOMBRE[S]?\s*COMPLETO[S]?\s*COMO\s+APARECE\s+EN\s+LA\s+IDENTIFICACI[OÓ]N\s*[:\s]+([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑa-záéíóúñ\s]{4,60})(?=\s*\n|$)",
        ]:
            m = re.search(pat, bloque_cliente, re.IGNORECASE)
            if m:
                nombre = _limpiar_y_validar(m.group(1))
                if nombre:
                    return nombre

    # 2) Resto de patrones en todo el texto
    for pat in PATRONES_NOMBRE_PROPIETARIO:
        m = re.search(pat, texto, re.IGNORECASE | re.MULTILINE)
        if m:
            nombre = _limpiar_y_validar(m.group(1))
            if nombre:
                return nombre
    return None


def _extraer_clabe(texto: str) -> Optional[str]:
    """Extrae una CLABE interbancaria de 18 digitos cuando el PDF la muestra."""
    if not texto or len(texto.strip()) < 20:
        return None

    patrones = [
        r"(?:CLABE\s*(?:INTERBANCARIA)?|CUENTA\s*CLABE)\s*[:#\-]?\s*([0-9\s\-]{18,30})",
        r"\b([0-9]{3}[\s\-]?[0-9]{3}[\s\-]?[0-9]{11,12})\b",
        r"\b([0-9][0-9\s\-]{16,28}[0-9])\b",
    ]
    for pat in patrones:
        for m in re.finditer(pat, texto, re.IGNORECASE):
            digitos = re.sub(r"\D+", "", m.group(1))
            if len(digitos) == 18:
                return digitos
    return None


def validar___SPARTA_SECRET_REDACTED___pdf(pdf_bytes: bytes) -> Dict[str, Any]:
    """
    Valida un PDF de estado de cuenta.
    Returns:
        valido: bool
        banco_detectado: str | None
        nombre_propietario: str | None
        clabe: str | None
        es_banco_fisico: bool
        tiene_datos_titular: bool
        mensaje: str
    """
    resultado = {
        "valido": False,
        "banco_detectado": None,
        "nombre_propietario": None,
        "clabe": None,
        "es_banco_fisico": False,
        "tiene_datos_titular": False,
        "mensaje": "",
    }
    texto = _texto_de_pdf(pdf_bytes)
    if not texto.strip():
        resultado["mensaje"] = (
            "No se pudo leer el contenido del PDF (ni texto ni OCR). "
            "Si es escaneado, instale RapidOCR: pip install rapidocr-onnxruntime, o Tesseract."
        )
        return resultado

    # Siempre intentar extraer banco y propietario para mostrar en tooltip
    banco, es_fisico = _detectar_banco(texto)
    resultado["banco_detectado"] = banco
    resultado["es_banco_fisico"] = es_fisico
    resultado["nombre_propietario"] = _extraer_nombre_propietario(texto)
    resultado["clabe"] = _extraer_clabe(texto)

    # Debe parecer estado de cuenta (mención típica) O tener banco físico detectado (OCR puede no leer la etiqueta)
    es___SPARTA_SECRET_REDACTED__ = bool(
        re.search(r"ESTADO\s+DE\s+CUENTA|STATE\s+OF\s+ACCOUNT|RESUMEN\s+DE\s+CUENTA", texto, re.IGNORECASE)
        or (es_fisico and banco)
    )
    if not es___SPARTA_SECRET_REDACTED__:
        resultado["mensaje"] = "El documento no parece ser un estado de cuenta bancario. Suba el PDF del estado de cuenta."
        return resultado

    if not es_fisico and banco:
        resultado["mensaje"] = (
            f"No se aceptan bancos o fintechs digitales ({banco}). "
            "Debe ser un estado de cuenta de un banco físico en México (BBVA, Banorte, Santander, Citibanamex, etc.)."
        )
        return resultado

    if not es_fisico and not banco:
        resultado["mensaje"] = (
            "No se pudo identificar el banco. Solo se aceptan estados de cuenta de bancos físicos en México "
            "(BBVA, Banorte, Santander, Citibanamex, Scotiabank, etc.)."
        )
        return resultado

    # En subida solo se exige banco físico; tiene_datos_titular se usa para el tooltip
    tiene_datos = _tiene_datos_titular(texto)
    resultado["tiene_datos_titular"] = tiene_datos

    # Aceptar si es banco físico (única validación en subida); datos completos para tooltip después
    resultado["valido"] = True
    if tiene_datos:
        resultado["mensaje"] = f"Estado de cuenta de {banco} verificado. Incluye datos del titular."
    else:
        resultado["mensaje"] = f"Estado de cuenta de {banco} aceptado. Recomendación: incluir la hoja con datos del titular (nombre, CLABE)."
    return resultado
