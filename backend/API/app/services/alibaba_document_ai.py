"""Alibaba/Qwen document validation adapter.

This module is intentionally isolated from the upload/storage flow. It only
extracts and evaluates document content, returning dictionaries that the
existing API routes can map to their current response shapes.
"""
from __future__ import annotations

import base64
import io
import json
import re
import time
import unicodedata
import urllib.error
import urllib.request
from dataclasses import dataclass
from datetime import datetime
from typing import Any, Dict, List, Optional, Set
from zoneinfo import ZoneInfo

import fitz
from PIL import Image, ImageFilter, ImageOps

from app.utils.curp_validator import validar_curp
from app.utils.nss_validator import validar_nss


CDMX_TZ = ZoneInfo("America/Mexico_City")

DOCUMENT_TYPES = {
    "identificacion_oficial",
    "ine",
    "residencia_permanente",
    "residencia_temporal",
    "pasaporte_mexicano",
    "pasaporte_extranjero",
    "acta_nacimiento",
    "solicitud___SPARTA_SECRET_REDACTED__",
    "cv",
    "comprobante_domicilio",
    "nss",
    "constancia_fiscal",
    "infonavit_fonacot",
    "carta_no_adeudo",
    "__SPARTA_SECRET_REDACTED__",
    "curp",
    "desconocido",
}

EXPECTED_LABELS = {
    "identificacion_oficial": "identificacion oficial",
    "acta_nacimiento": "acta de nacimiento",
    "solicitud___SPARTA_SECRET_REDACTED__": "solicitud interna",
    "cv": "CV o solicitud de trabajo",
    "comprobante_domicilio": "comprobante de domicilio",
    "nss": "numero de seguridad social",
    "constancia_fiscal": "constancia de situacion fiscal",
    "infonavit_fonacot": "retencion FONACOT o INFONAVIT",
    "carta_no_adeudo": "carta de no adeudo",
    "__SPARTA_SECRET_REDACTED__": "estado de cuenta",
    "curp": "CURP",
}

DOCUMENT_ALIASES = {
    "solicitud_interna": {"solicitud_interna", "solicitud___SPARTA_SECRET_REDACTED__"},
    "solicitud___SPARTA_SECRET_REDACTED__": {"solicitud_interna", "solicitud___SPARTA_SECRET_REDACTED__"},
    "identificacion_oficial": {
        "identificacion_oficial",
        "ine",
        "residencia_permanente",
        "residencia_temporal",
        "pasaporte_mexicano",
        "pasaporte_extranjero",
    },
    "cv": {"cv", "solicitud___SPARTA_SECRET_REDACTED__"},
    "infonavit_fonacot": {"infonavit_fonacot", "carta_no_adeudo"},
}

BANCOS_DIGITALES = {
    "nu",
    "nubank",
    "klar",
    "uala",
    "mercado pago",
    "mercadopago",
    "spin",
    "spin by oxxo",
    "hey banco",
    "cuenca",
    "albo",
    "fondeadora",
    "stori",
    "broxel",
    "pagando",
    "digital bank",
    "banco digital",
}

QUICK_PROMPT = """
Eres un verificador rapido de documentos para MaxiKash.

Analiza las imagenes del documento. Puede haber una o varias paginas.

Objetivo:
- Identificar el tipo real de documento.
- Extraer campos visibles sin inventar datos.
- Decidir si el archivo sirve para el campo que se esta cargando.

Devuelve SOLO JSON valido, sin markdown y sin explicaciones.

Tipos permitidos:
- identificacion_oficial
- ine
- residencia_permanente
- residencia_temporal
- pasaporte_mexicano
- pasaporte_extranjero
- acta_nacimiento
- solicitud___SPARTA_SECRET_REDACTED__
- cv
- comprobante_domicilio
- nss
- constancia_fiscal
- infonavit_fonacot
- carta_no_adeudo
- __SPARTA_SECRET_REDACTED__
- curp
- desconocido

Reglas de lectura:
1. Haz lectura visual en dos pasadas: primero identifica el tipo y secciones del
   documento; despues lee campos rotulados y valida longitudes/formato.
2. No inventes datos. Si un campo no se ve, usa null.
3. No marques el documento como correcto solo porque la imagen sea clara. Si falta
   un dato requerido para la regla documental, reportalo en observaciones o marca
   evidencia_insuficiente=true.
4. Usa fechas en formato YYYY-MM-DD. Si solo ves mes y anio, usa YYYY-MM-01.
5. Si detectas INE, pasaporte o residencia, clasifica el subtipo especifico.
   Un pasaporte de cualquier pais es identificacion oficial valida; clasificalo
   como pasaporte_extranjero cuando el pais/nacionalidad no sea Mexico.
6. Si el documento tiene frente y reverso, indica si ambos aparecen. Para
   pasaportes no exijas frente y reverso; basta la hoja de datos legible.
7. Si la imagen esta borrosa, cortada o ilegible, baja la confianza.
8. Para estado de cuenta, acepta caratulas, anexos o contratos bancarios como Libreton BBVA si muestran banco, titular y CLABE o cuenta.
9. Para constancia_fiscal, extrae actividad_economica, regimen_fiscal y regimenes_fiscales si aparecen.
10. Para constancia_fiscal, fecha_emision debe ser la fecha del bloque "Lugar y Fecha de Emision"; no uses fecha de inicio de operaciones ni fecha de inicio de regimen.
11. La "Constancia de la Clave Unica de Registro de Poblacion" de RENAPO/gob.mx es tipo curp, no constancia_fiscal.
12. Para CURP, fecha_emision debe ser la fecha visible de expedicion/emision del documento; no inventes fechas futuras.
13. Para comprobante_domicilio, busca fechas como periodo facturado, periodo de consumo, fecha limite de pago, fecha de corte o emision. En recibos CFE, si no hay fecha_emision usa fecha limite de pago como fecha_vencimiento.
14. Para solicitud___SPARTA_SECRET_REDACTED__, no confundas campos: "Clave Unica de Registro de Poblacion" es CURP, "Registro Federal de Contribuyentes" es RFC y "Numero de seguridad social" es NSS.
15. Para nss, extrae solo un numero de 11 digitos asociado a IMSS/NSS. No uses folio de solicitud, cadena original, numero de serie ni codigo QR como NSS.
16. CURP debe tener 18 caracteres alfanumericos, RFC 12 o 13 caracteres y NSS 11 digitos. Si dudas entre letras/numeros como O/0, I/1, G/6 o R/P, usa el valor con mayor evidencia visual; si la duda afecta el valor final, usa null y explicalo.
17. Para carta_no_adeudo, el nombre_completo debe ser el nombre de la persona que declara/no adeuda. Si solo aparece en la linea manuscrita de "Nombre completo y firma", leelo con mucho cuidado; si no estas seguro, usa null y explica la duda.
18. Para carta_no_adeudo, no uses "A quien corresponda", nombre de empresa, empleador, beneficiario, entidad emisora o texto de la firma como nombre_completo.
19. Para carta_no_adeudo, confirma si el formato fue llenado y firmado. firma_detectada=true solo si hay trazo manuscrito o firma digital visible. Una linea impresa vacia, una raya sin nombre o el texto "Nombre completo y firma" no cuentan como firma. Si la linea esta vacia, si solo aparece la plantilla sin nombre, o si no hay firma/trazo manuscrito, marca nombre_y_firma_lleno=false, firma_detectada=false y evidencia_insuficiente=true.
20. Para infonavit_fonacot, acepta documentos reales de retencion, reporte mensual,
    aviso, estado de cuenta o descuento FONACOT/INFONAVIT cuando muestren credito,
    descuento, saldo, periodo o numero de credito. En esos casos NO exijas firma:
    la firma solo aplica cuando el documento real sea una carta_no_adeudo.
21. Para CURP escaneada de RENAPO, enfocate visualmente en la zona superior:
    "Clave:" contiene la CURP y "Nombre" contiene el nombre completo. La clave
    suele estar en letras grandes debajo de "Clave". No uses codigos de barras,
    QR, folios ni textos legales como CURP.
22. En CURP, si dudas entre O/0 o I/1 en las ultimas posiciones, devuelve el
    valor solo si mantiene formato oficial de 18 caracteres. Si la duda queda
    sin resolver, pon curp=null y explica la duda; no inventes el valor.
23. Para solicitud___SPARTA_SECRET_REDACTED__ escaneada o manuscrita, reconoce el formato por el
    titulo "SOLICITUD DE EMPLEO MAXIKASH", el logo MaxiKash y las secciones
    "DATOS PERSONALES", "DOCUMENTACION", "CONTACTOS DE EMERGENCIA" o similares.
    Clasificala como solicitud___SPARTA_SECRET_REDACTED__ aunque algunos campos manuscritos no se
    puedan leer completos.
24. En solicitud___SPARTA_SECRET_REDACTED__, la CURP puede estar escrita en cuadros separados bajo
    "Clave Unica del Registro de Poblacion". Lee los caracteres de izquierda a
    derecha y reconstruye 18 caracteres solo si hay suficiente evidencia visual.

Estructura exacta:
{
  "tipo_documento": "uno_de_los_tipos_permitidos",
  "subtipo": null,
  "confianza_lectura": "alta|media|baja",
  "calidad_imagen": "buena|regular|mala",
  "paginas_analizadas": 0,
  "frente_reverso": {
    "frente_detectado": false,
    "reverso_detectado": false
  },
  "campos": {
    "nombre_completo": null,
    "curp": null,
    "rfc": null,
    "fecha_nacimiento": null,
    "clave_elector": null,
    "numero_documento": null,
    "fecha_expedicion": null,
    "fecha_vencimiento": null,
    "fecha_emision": null,
    "domicilio": null,
    "nacionalidad": null,
    "banco": null,
    "titular_cuenta": null,
    "clabe": null,
    "numero_cuenta": null,
    "periodo___SPARTA_SECRET_REDACTED___inicio": null,
    "periodo___SPARTA_SECRET_REDACTED___fin": null,
    "nss": null,
    "folio": null,
    "entidad_emisora": null,
    "actividad_economica": null,
    "regimen_fiscal": null,
    "regimenes_fiscales": [],
    "firma_detectada": null,
    "nombre_y_firma_lleno": null
  },
  "observaciones": [],
  "evidencia_insuficiente": false
}
"""


CROSSCHECK_PROMPT = """
Eres un auditor documental senior de Capital Humano para MaxiKash.

Vas a recibir imagenes y/o lecturas IA previas de hasta 10 documentos del
expediente de un candidato. Cada grupo inicia con una linea de texto que indica
la clave del documento, su nombre esperado, el nombre del archivo y cuantas
paginas tiene el PDF original.

Objetivo:
1. Leer los documentos visibles sin inventar datos.
2. Comparar el nombre registrado del candidato contra los nombres leidos.
3. Comparar CURP, RFC, NSS, domicilio y datos bancarios cuando aparezcan en mas
   de un documento.
4. Aplicar reglas documentales basicas:
   - Solicitud interna: debe tener minimo 2 paginas.
   - CURP: no mayor a 2 meses si hay fecha visible de emision/expedicion.
   - Constancia fiscal: debe tener minimo 2 paginas, no mayor a 2 meses y
     regimen de Sueldos y Salarios e Ingresos Asimilados a Salarios.
   - Comprobante de domicilio: no mayor a 3 meses si hay fecha visible.
   - Estado de cuenta: debe ser banco fisico; se aceptan caratulas/libreton
     BBVA si muestran titular y CLABE o cuenta. No aceptar bancos digitales
     como Nu, Mercado Pago o Klar.
   - Hoja de retencion FONACOT/INFONAVIT o carta de no adeudo: si es carta
     de no adeudo, debe estar llenada con nombre del candidato y firma/trazo
     manuscrito. Una plantilla en blanco no cumple aunque sea legible.
     Si es hoja, reporte, aviso, estado o retencion real FONACOT/INFONAVIT
     con credito, adeudo, descuento, saldo, periodo o numero de credito,
     NO exijas firma ni nombre escrito a mano; ese documento cumple como
     retencion y no debe evaluarse como carta de no adeudo.
5. Si solo el comprobante de domicilio esta a nombre de otra persona, no lo
   marques como error de identidad; compara domicilio y deja observacion.
6. Si solo el nombre registrado esta disponible fuera de los documentos, no
   exijas que el sistema haya capturado CURP/RFC; compara CURP/RFC/NSS entre
   los documentos que si los muestren.
7. No uses RFC del comprobante_domicilio para validar identidad ni para
   compararlo contra RFC del candidato. En recibos de servicios como CFE,
   agua, gas o telefono, el RFC visible puede pertenecer al proveedor.
8. Para RFC de persona fisica, compara principalmente la base de identidad
   (primeros 10 caracteres: iniciales + fecha). Si la base coincide y solo
   cambia/falta la homoclave final por lectura OCR/IA, marcala como aviso,
   no como diferencia critica.
9. Para CURP y NSS, compara solo valores completos y validos: CURP de 18
   caracteres con formato oficial y NSS de 11 digitos. Si una lectura no es
   valida o es dudosa, no la uses como diferencia; reporta que falta lectura
   confiable. Si dos valores validos de CURP o NSS difieren, marcala como
   diferencia critica.
10. Distingue siempre entre:
   - solicitud___SPARTA_SECRET_REDACTED__ o solicitud_interna: formato interno de MaxiKash.
   - cv: CV personal o solicitud de trabajo general.
   Si una solicitud de trabajo general esta cargada en solicitud_interna,
   reportala como tipo incorrecto y recomienda moverla a CV o solicitud de trabajo.
11. Si recibes una lectura previa marcada como motor_v1, pdf_text u OCR local,
    usala como respaldo confiable cuando el documento visual coincide con el
    tipo esperado. No vuelvas a dejar "lectura pendiente" para ese documento
    si la lectura previa ya contiene tipo_detectado valido o CURP valida.
12. Para CURP escaneada RENAPO, usa la clave grande debajo de "Clave:" y el
    nombre debajo de "Nombre". Ignora codigos de barras, QR y folios.
13. Para solicitud interna MaxiKash manuscrita, el objetivo minimo es confirmar
    que sea el formato interno y que tenga las paginas requeridas. Si la letra
    manuscrita impide leer CURP o RFC, no la rechaces por eso si esos datos ya
    fueron confirmados en otros documentos.

Devuelve SOLO JSON valido, sin markdown.

Claves obligatorias de documentos:
solicitud_interna, cv, acta_nacimiento, curp, identificacion_oficial,
comprobante_domicilio, constancia_fiscal, nss, hoja_retencion, __SPARTA_SECRET_REDACTED__.

Estructura exacta:
{
  "dictamen": "aprobado|requiere_revision|rechazado",
  "confianza": 0,
  "resumen_final": "texto profesional en una o dos frases",
  "datos_referencia": {
    "nombre_registro": null,
    "nombre_principal_documentos": null,
    "curp_principal": null,
    "rfc_principal": null,
    "nss_principal": null
  },
  "documentos": {
    "solicitud_interna": {
      "estado": "coincide|requiere_revision|no_coincide|no_leido",
      "tipo_detectado": null,
      "archivo": null,
      "paginas_pdf": null,
      "nombre": null,
      "curp": null,
      "rfc": null,
      "nss": null,
      "fecha_nacimiento": null,
      "fecha_emision": null,
      "domicilio": null,
      "banco": null,
      "clabe": null,
      "numero_cuenta": null,
      "regimen_fiscal": null,
      "mensaje": null,
      "observaciones": []
    }
  },
  "comparaciones": [
    {
      "categoria": "Nombre|CURP|RFC|NSS|Domicilio|Regla documental|Banco",
      "etiqueta": "descripcion breve",
      "documento_a": "clave o Registro",
      "valor_a": null,
      "documento_b": "clave o Documento",
      "valor_b": null,
      "coincide": true,
      "severidad": "ok|aviso|critico",
      "mensaje": "texto claro para Capital Humano"
    }
  ],
  "coincidencias": {
    "total": 0,
    "ok": 0,
    "fallas": 0
  },
  "alertas": [],
  "recomendaciones": []
}

Reglas para el dictamen:
- aprobado: no hay comparaciones criticas fallidas y las reglas obligatorias se cumplen.
- requiere_revision: hay datos ambiguos, faltantes o alertas no criticas.
- rechazado: hay identidad que no coincide, documento vencido, tipo incorrecto o regla obligatoria incumplida.

El resumen_final debe sonar profesional. Ejemplo si todo esta bien:
"La informacion recibida es consistente entre los documentos revisados, cumple con las reglas documentales establecidas y corresponde al candidato registrado."

Si algo falla, menciona que documento(s) no coinciden y con que campo:
"El expediente requiere correccion: la Constancia de situacion fiscal no coincide en RFC con la solicitud interna y la CURP presentada excede la antiguedad permitida."
"""


@dataclass
class RenderedPage:
    data: bytes
    mime_type: str = "image/jpeg"


def normalize_text(value: Optional[str]) -> str:
    if not value:
        return ""
    value = unicodedata.normalize("NFKD", str(value))
    value = "".join(ch for ch in value if not unicodedata.combining(ch))
    value = re.sub(r"[^A-Za-z0-9]+", " ", value).strip().upper()
    return re.sub(r"\s+", " ", value)


def today_cdmx():
    return datetime.now(CDMX_TZ).date()


def parse_date(value: Optional[str]):
    if not value:
        return None
    value = str(value).strip()
    for fmt in ("%Y-%m-%d", "%d/%m/%Y", "%d-%m-%Y"):
        try:
            return datetime.strptime(value, fmt).date()
        except ValueError:
            pass
    month_names = {
        "ENE": 1,
        "ENERO": 1,
        "FEB": 2,
        "FEBRERO": 2,
        "MAR": 3,
        "MARZO": 3,
        "ABR": 4,
        "ABRIL": 4,
        "MAY": 5,
        "MAYO": 5,
        "JUN": 6,
        "JUNIO": 6,
        "JUL": 7,
        "JULIO": 7,
        "AGO": 8,
        "AGOSTO": 8,
        "SEP": 9,
        "SEPT": 9,
        "SEPTIEMBRE": 9,
        "OCT": 10,
        "OCTUBRE": 10,
        "NOV": 11,
        "NOVIEMBRE": 11,
        "DIC": 12,
        "DICIEMBRE": 12,
    }
    normalized = normalize_text(value)
    match = re.search(r"\b(\d{1,2})\s+(?:DE\s+)?([A-Z]+)\s+(?:DE\s+)?(\d{2,4})\b", normalized)
    if not match:
        return None
    day = int(match.group(1))
    month = month_names.get(match.group(2))
    year = int(match.group(3))
    if year < 100:
        year += 2000 if year < 70 else 1900
    if not month:
        return None
    try:
        return datetime(year, month, day).date()
    except ValueError:
        return None


def months_since(value) -> Optional[int]:
    if not value:
        return None
    today = today_cdmx()
    months = (today.year - value.year) * 12 + (today.month - value.month)
    if today.day < value.day:
        months -= 1
    return max(0, months)


def compatible_document_types(expected_doc_type: Optional[str]) -> Set[str]:
    if not expected_doc_type:
        return set()
    return DOCUMENT_ALIASES.get(expected_doc_type, {expected_doc_type})


def user_document_name(doc_type: Optional[str]) -> str:
    labels = {
        "identificacion_oficial": "identificacion oficial",
        "ine": "INE",
        "residencia_permanente": "residencia permanente",
        "residencia_temporal": "residencia temporal",
        "pasaporte_mexicano": "pasaporte mexicano",
        "pasaporte_extranjero": "pasaporte extranjero",
        "acta_nacimiento": "acta de nacimiento",
        "solicitud___SPARTA_SECRET_REDACTED__": "solicitud interna",
        "cv": "CV",
        "comprobante_domicilio": "comprobante de domicilio",
        "nss": "NSS",
        "constancia_fiscal": "constancia fiscal",
        "infonavit_fonacot": "retencion FONACOT o INFONAVIT",
        "carta_no_adeudo": "carta de no adeudo",
        "__SPARTA_SECRET_REDACTED__": "estado de cuenta",
        "curp": "CURP",
        "desconocido": "documento no identificado",
    }
    return labels.get(doc_type or "", doc_type or "documento")


def _field_text(fields: Dict[str, Any], *keys: str) -> str:
    parts: List[str] = []
    for key in keys:
        value = fields.get(key)
        if isinstance(value, list):
            parts.extend(str(item) for item in value if item)
        elif value:
            parts.append(str(value))
    return " ".join(parts)


def _bool_from_field(value: Any) -> Optional[bool]:
    if isinstance(value, bool):
        return value
    if value is None:
        return None
    text = normalize_text(str(value)).strip()
    if text in {"1", "TRUE", "SI", "S", "YES", "Y", "OK", "PRESENTE", "DETECTADO", "LLENO"}:
        return True
    if text in {"0", "FALSE", "NO", "N", "AUSENTE", "NO DETECTADO", "VACIO", "VACIA", "SIN FIRMA", "SIN NOMBRE"}:
        return False
    return None


def _blank_like_name(value: Any) -> bool:
    text = normalize_text(str(value or ""))
    if not text:
        return True
    placeholders = {
        "NOMBRE COMPLETO",
        "NOMBRE COMPLETO Y FIRMA",
        "FIRMA",
        "A QUIEN CORRESPONDA",
        "ATENTAMENTE",
    }
    if text in placeholders:
        return True
    return text.startswith("NOMBRE COMPLETO") or text.startswith("FIRMA")


def _has_sueldos_salarios(fields: Dict[str, Any]) -> bool:
    text = normalize_text(_field_text(fields, "regimen_fiscal", "regimenes_fiscales", "actividad_economica"))
    compact = re.sub(r"[^A-Z0-9]+", "", text)
    return bool(
        "SUELDOS Y SALARIOS" in text
        or "SUELDOS Y SALARIOS E INGRESOS ASIMILADOS" in text
        or "REGIMEN DE SUELDOS Y SALARIOS" in text
        or "SUELDOSYSALARIOS" in compact
    )


def _has_actividad_asalariado(fields: Dict[str, Any]) -> bool:
    return "ASALARIADO" in normalize_text(_field_text(fields, "actividad_economica", "regimen_fiscal", "regimenes_fiscales"))


def is_digital_bank(bank: Optional[str]) -> bool:
    bank_norm = normalize_text(bank).lower()
    return any(normalize_text(x).lower() in bank_norm for x in BANCOS_DIGITALES)


def extract_json(text: str) -> Dict[str, Any]:
    text = (text or "").strip()
    try:
        return json.loads(text)
    except json.JSONDecodeError:
        start = text.find("{")
        end = text.rfind("}")
        if start >= 0 and end > start:
            return json.loads(text[start : end + 1])
        raise


def _jpeg_bytes_from_image(img: Image.Image, max_side: int = 1800) -> bytes:
    img = img.convert("RGB")
    img.thumbnail((max_side, max_side), Image.LANCZOS)
    out = io.BytesIO()
    img.save(out, format="JPEG", quality=82, optimize=True)
    return out.getvalue()


def _enhance_document_crop(img: Image.Image) -> Image.Image:
    """Improve contrast for visual AI without turning the whole flow into OCR."""
    gray = ImageOps.grayscale(img.convert("RGB"))
    gray = ImageOps.autocontrast(gray)
    gray = gray.filter(ImageFilter.SHARPEN)
    return Image.merge("RGB", (gray, gray, gray))


def _safe_crop_ratio(img: Image.Image, box: tuple[float, float, float, float]) -> Optional[Image.Image]:
    w, h = img.size
    left = max(0, min(w - 1, int(w * box[0])))
    top = max(0, min(h - 1, int(h * box[1])))
    right = max(left + 1, min(w, int(w * box[2])))
    bottom = max(top + 1, min(h, int(h * box[3])))
    if right - left < 220 or bottom - top < 160:
        return None
    return img.crop((left, top, right, bottom))


def render_identificacion_assistida(file_bytes: bytes, filename: str, dpi: int) -> List[RenderedPage]:
    """Create a few rotated/cropped views for INE-style IDs.

    Some INE scans arrive sideways. The regular render is still sent, but these
    assisted views give the visual model a clean look at the name/CURP block.
    """
    ext = (filename.rsplit(".", 1)[-1] if "." in filename else "").lower()
    images: List[Image.Image] = []
    render_dpi = max(180, min(240, int(dpi or 180) + 40))
    try:
        if ext == "pdf":
            doc = fitz.open(stream=file_bytes, filetype="pdf")
            if doc.page_count < 1:
                doc.close()
                return []
            matrix = fitz.Matrix(render_dpi / 72, render_dpi / 72)
            pix = doc[0].get_pixmap(matrix=matrix, alpha=False)
            images.append(Image.frombytes("RGB", [pix.width, pix.height], pix.samples))
            doc.close()
        else:
            with Image.open(io.BytesIO(file_bytes)) as img:
                images.append(img.convert("RGB"))
    except Exception:
        return []

    if not images:
        return []

    source = images[0]
    variants: List[RenderedPage] = []
    for rotation in (90, -90):
        rotated = source.rotate(rotation, expand=True)
        enhanced = _enhance_document_crop(rotated)
        variants.append(RenderedPage(_jpeg_bytes_from_image(enhanced, max_side=1800)))

        # INE data normally lives around the left/central band once upright.
        for box in ((0.03, 0.12, 0.73, 0.90), (0.14, 0.26, 0.67, 0.72)):
            crop = _safe_crop_ratio(rotated, box)
            if crop is not None:
                variants.append(RenderedPage(_jpeg_bytes_from_image(_enhance_document_crop(crop), max_side=1600)))
                break

    return variants[:4]


def render_input(file_bytes: bytes, filename: str, max_pages: int, dpi: int) -> tuple[List[RenderedPage], int]:
    ext = (filename.rsplit(".", 1)[-1] if "." in filename else "").lower()
    if ext == "pdf":
        doc = fitz.open(stream=file_bytes, filetype="pdf")
        page_count = int(doc.page_count or 0)
        pages: List[RenderedPage] = []
        matrix = fitz.Matrix(dpi / 72, dpi / 72)
        for idx in range(min(page_count, max_pages)):
            page = doc[idx]
            pix = page.get_pixmap(matrix=matrix, alpha=False)
            img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
            pages.append(RenderedPage(_jpeg_bytes_from_image(img)))
        doc.close()
        return pages, page_count
    with Image.open(io.BytesIO(file_bytes)) as img:
        return [RenderedPage(_jpeg_bytes_from_image(img))], 1


def build_openai_content(rendered_pages: List[RenderedPage], prompt_text: str) -> List[Dict[str, Any]]:
    content: List[Dict[str, Any]] = [{"type": "text", "text": prompt_text}]
    for page in rendered_pages:
        encoded = base64.b64encode(page.data).decode("ascii")
        content.append({
            "type": "image_url",
            "image_url": {"url": f"data:{page.mime_type};base64,{encoded}"},
        })
    return content


def build_openai_content_from_parts(parts: List[Dict[str, Any]], prompt_text: str) -> List[Dict[str, Any]]:
    content: List[Dict[str, Any]] = [{"type": "text", "text": prompt_text}]
    for part in parts:
        if part.get("type") == "text":
            content.append({"type": "text", "text": str(part.get("text") or "")})
            continue
        if part.get("type") == "image" and part.get("page"):
            page: RenderedPage = part["page"]
            encoded = base64.b64encode(page.data).decode("ascii")
            content.append({
                "type": "image_url",
                "image_url": {"url": f"data:{page.mime_type};base64,{encoded}"},
            })
    return content


def parse_model_chain(primary_model: str, fallback_models: Optional[str]) -> List[str]:
    chain: List[str] = []
    for raw in [primary_model, *((fallback_models or "").split(","))]:
        model = raw.strip()
        if model and model not in chain:
            chain.append(model)
    return chain or ["qwen3.5-flash"]


def parse_retry_delays(value: Optional[str]) -> List[int]:
    delays: List[int] = []
    for raw in str(value or "0").split(","):
        raw = raw.strip()
        if raw:
            delays.append(max(0, int(raw)))
    if not delays:
        return [0, 1]
    if delays == [0]:
        return [0, 1]
    return delays


def is_transient_error(exc: Exception) -> bool:
    text = str(exc).lower()
    return any(token in text for token in ("429", "500", "502", "503", "504", "timeout", "timed out", "rate limit", "eof", "ssl", "empty response", "invalid json", "expecting value"))


def summary_is_usable(summary: Any) -> bool:
    if not isinstance(summary, dict):
        return False
    marker = " ".join(
        str(summary.get(key) or "")
        for key in ("motor_ia", "modelo_ia", "fuente_lectura", "source")
    ).lower()
    return "alibaba" in marker or "motor_v2" in marker or "motor_v1" in marker or "pdf_text" in marker


def quick_result_to_summary(key: str, label: str, filename: str, result: Dict[str, Any]) -> Dict[str, Any]:
    extraction = result.get("extraction") if isinstance(result.get("extraction"), dict) else {}
    fields = extraction.get("campos") if isinstance(extraction.get("campos"), dict) else {}
    validation = result.get("validation") if isinstance(result.get("validation"), dict) else {}
    observations = extraction.get("observaciones") if isinstance(extraction.get("observaciones"), list) else []
    rfc_value = fields.get("rfc")
    if key == "comprobante_domicilio":
        rfc_value = None
    validation_payload = {
        "valido": validation.get("aprobado"),
        "rechazado": validation.get("aprobado") is False,
        "revision_manual": validation.get("requiere_revision_humana"),
        "mensaje": validation.get("mensaje_usuario"),
        "tipo_documento_detectado": extraction.get("tipo_documento"),
        "subtipo": extraction.get("subtipo"),
        "confianza_lectura": extraction.get("confianza_lectura"),
        "calidad_imagen": extraction.get("calidad_imagen"),
        "nombre": fields.get("nombre_completo"),
        "curp": fields.get("curp"),
        "curp_lectura_ia": fields.get("curp"),
        "rfc": rfc_value,
        "nss_extraido": fields.get("nss"),
        "nss_lectura_ia": fields.get("nss"),
        "fecha_nacimiento": fields.get("fecha_nacimiento"),
        "fecha_emision": fields.get("fecha_emision") or fields.get("fecha_expedicion"),
        "fecha_vencimiento": fields.get("fecha_vencimiento"),
        "direccion": fields.get("domicilio"),
        "domicilio": fields.get("domicilio"),
        "banco_detectado": fields.get("banco"),
        "nombre_propietario": fields.get("titular_cuenta") or fields.get("nombre_completo"),
        "clabe": fields.get("clabe"),
        "numero_cuenta": fields.get("numero_cuenta"),
        "actividad_economica": fields.get("actividad_economica"),
        "regimen_fiscal": fields.get("regimen_fiscal"),
        "regimenes_fiscales": fields.get("regimenes_fiscales"),
        "firma_detectada": fields.get("firma_detectada"),
        "nombre_y_firma_lleno": fields.get("nombre_y_firma_lleno"),
        "evidencia_insuficiente": extraction.get("evidencia_insuficiente"),
        "paginas_pdf": extraction.get("paginas_pdf") or extraction.get("paginas_analizadas"),
        "alertas": (validation.get("errores") or []) + (validation.get("advertencias") or []),
        "observaciones": observations,
    }
    validation_payload = {k: v for k, v in validation_payload.items() if v not in (None, "", [])}
    return {
        "key": key,
        "tipo_documento": label,
        "archivo": filename,
        "paginas_pdf": extraction.get("paginas_pdf") or extraction.get("paginas_analizadas"),
        "motor_ia": result.get("provider") or "alibaba",
        "modelo_ia": result.get("model"),
        "fuente_lectura": "motor_v2_prefinal",
        "validacion_previa": validation_payload,
    }


def compact_summary_for_prompt(summary: Dict[str, Any]) -> Dict[str, Any]:
    allowed = {
        "key",
        "tipo_documento",
        "archivo",
        "fecha_carga",
        "paginas_pdf",
        "motor_ia",
        "modelo_ia",
        "fuente_lectura",
        "validacion_previa",
    }
    compact = {k: v for k, v in summary.items() if k in allowed and v not in (None, "", [])}
    validation = compact.get("validacion_previa")
    if isinstance(validation, dict):
        compact["validacion_previa"] = {
            k: v
            for k, v in validation.items()
            if k
            in {
                "valido",
                "rechazado",
                "revision_manual",
                "mensaje",
                "nombre",
                "curp",
                "curp_lectura_ia",
                "rfc",
                "nss_extraido",
                "nss_lectura_ia",
                "banco_detectado",
                "nombre_propietario",
                "clabe",
                "numero_cuenta",
                "fecha_emision",
                "fecha_vencimiento",
                "meses_antiguedad",
                "vigencia_ok",
                "es_reciente",
                "actividad_asalariado",
                "regimen_sueldos_salarios",
                "firma_detectada",
                "nombre_y_firma_lleno",
                "evidencia_insuficiente",
                "tipo_identificacion",
                "fecha_documento",
                "direccion",
                "nombre_titular",
                "alertas",
                "notas",
                "observaciones",
                "indicadores",
                "paginas",
                "paginas_pdf",
            }
            and v not in (None, "", [])
        }
    return compact


def quick_prompt_for(expected_doc_type: Optional[str], nombre_candidato: Optional[str] = None) -> str:
    if not expected_doc_type:
        return QUICK_PROMPT
    nombre = str(nombre_candidato or "").strip()
    nombre_hint = ""
    if nombre:
        nombre_hint = (
            "\n\nNombre registrado del candidato para contraste visual: "
            + nombre
            + ". Usalo solo como referencia para leer campos manuscritos; no lo copies "
            "si la imagen no lo sostiene."
        )
    extra = ""
    if expected_doc_type == "solicitud___SPARTA_SECRET_REDACTED__":
        extra = (
            "\n\nInstruccion especial para solicitud interna: revisa los campos manuscritos "
            "del formulario. Extrae nombre_completo desde el encabezado/datos personales, "
            "CURP desde 'Clave Unica de Registro de Poblacion', RFC desde 'Registro Federal "
            "de Contribuyentes' y NSS desde 'Numero de seguridad social'. Si el nombre "
            "manuscrito coincide visualmente con el nombre registrado, devuelve el nombre "
            "normalizado como aparece en el registro."
        )
    elif expected_doc_type == "nss":
        extra = (
            "\n\nInstruccion especial para NSS: valida visualmente que el numero venga junto "
            "al texto IMSS/NSS/Numero de Seguridad Social. Ignora folios largos, cadena "
            "original, sellos y numeros de serie."
        )
    elif expected_doc_type == "infonavit_fonacot":
        extra = (
            "\n\nInstruccion especial para retencion FONACOT o INFONAVIT: este campo acepta "
            "dos familias validas de documento. 1) Hoja, aviso, reporte mensual, estado de "
            "cuenta o retencion real FONACOT/INFONAVIT cuando hay adeudo, credito, saldo, "
            "descuento, numero de credito o periodo. En ese caso clasifica como "
            "infonavit_fonacot y no exijas nombre escrito a mano ni firma. 2) Carta de no "
            "adeudo cuando la persona declara que no tiene credito; solo en este segundo "
            "caso clasifica como carta_no_adeudo y valida que este llenada con nombre y "
            "firma/trazo manuscrito."
        )
    elif expected_doc_type == "carta_no_adeudo":
        extra = (
            "\n\nInstruccion especial para carta de no adeudo: el documento puede traer el "
            "nombre solo manuscrito sobre 'Nombre completo y firma'. Lee esa linea con zoom "
            "mental y no cambies letras por parecido visual si no estas seguro. Si el nombre "
            "manuscrito coincide visualmente con el nombre registrado, devuelve el nombre "
            "normalizado como aparece en el registro. Si no es claro, deja nombre_completo "
            "en null y agrega observacion. El formato en blanco NO es valido: si no hay "
            "nombre escrito en la linea final o no hay firma/trazo manuscrito, devuelve "
            "nombre_y_firma_lleno=false, firma_detectada=false y evidencia_insuficiente=true."
        )
    elif expected_doc_type == "identificacion_oficial":
        extra = (
            "\n\nInstruccion especial para identificacion oficial/INE: puedes recibir la pagina "
            "original y tambien vistas asistidas rotadas o recortadas del mismo documento. "
            "Usa esas vistas para leer el bloque NOMBRE y la linea CURP. En INE, el bloque "
            "NOMBRE suele traer apellido paterno, apellido materno y nombres en lineas "
            "separadas; devuelve nombre_completo normalizado en orden natural si se lee con "
            "claridad. No tomes texto del holograma, QR, reverso, clave de elector ni folios "
            "como CURP. Si una letra/digito no es claro, deja ese campo en null y explicalo."
        )
    return (
        QUICK_PROMPT
        + "\n\nCampo que el usuario esta cargando: "
        + EXPECTED_LABELS.get(expected_doc_type, expected_doc_type)
        + ". Identifica el documento real; si no corresponde al campo, no lo fuerces."
        + nombre_hint
        + extra
    )


def _normalizar_curp_extraida(value: Any) -> Optional[str]:
    raw = re.sub(r"[^A-Za-z0-9]+", "", str(value or "")).upper()
    if not raw:
        return None
    candidates: List[str] = []
    if len(raw) == 18:
        candidates.append(raw)
    candidates.extend(m.group(0) for m in re.finditer(r"[A-Z]{4}[A-Z0-9]{14}", raw))
    for cand in candidates:
        if len(cand) == 18 and validar_curp(cand)[0]:
            return cand
    return None


def _normalizar_nss_extraido(value: Any) -> Optional[str]:
    digits = re.sub(r"\D+", "", str(value or ""))
    if len(digits) != 11:
        return None
    return digits if validar_nss(digits)[0] else None


def _limpiar_identificadores_extraidos(data: Dict[str, Any]) -> None:
    fields = data.get("campos")
    if not isinstance(fields, dict):
        return
    observations = data.setdefault("observaciones", [])
    if not isinstance(observations, list):
        observations = []
        data["observaciones"] = observations

    raw_curp = fields.get("curp")
    if raw_curp:
        curp = _normalizar_curp_extraida(raw_curp)
        if curp:
            fields["curp"] = curp
        else:
            fields["curp"] = None
            observations.append(f"CURP descartada por lectura no valida: {raw_curp}.")

    raw_nss = fields.get("nss")
    if raw_nss:
        nss = _normalizar_nss_extraido(raw_nss)
        if nss:
            fields["nss"] = nss
        else:
            fields["nss"] = None
            observations.append(f"NSS descartado por lectura no valida: {raw_nss}.")


def validate_quick_extracted(data: Dict[str, Any], expected_doc_type: Optional[str]) -> Dict[str, Any]:
    errors: List[str] = []
    warnings: List[str] = []
    _limpiar_identificadores_extraidos(data)
    doc_type = data.get("tipo_documento") or "desconocido"
    fields = data.get("campos") or {}
    confidence = data.get("confianza_lectura") or "baja"
    quality = data.get("calidad_imagen") or "mala"

    if expected_doc_type == "curp" and doc_type == "constancia_fiscal":
        has_tax_data = bool(fields.get("rfc") or _has_actividad_asalariado(fields) or _has_sueldos_salarios(fields))
        if fields.get("curp") and not has_tax_data:
            data["tipo_documento"] = "curp"
            doc_type = "curp"

    if expected_doc_type == "__SPARTA_SECRET_REDACTED__" and doc_type == "desconocido":
        if fields.get("banco") and (fields.get("clabe") or fields.get("numero_cuenta")) and not is_digital_bank(fields.get("banco")):
            data["tipo_documento"] = "__SPARTA_SECRET_REDACTED__"
            doc_type = "__SPARTA_SECRET_REDACTED__"

    if doc_type not in DOCUMENT_TYPES or doc_type == "desconocido":
        errors.append("No se pudo identificar el tipo de documento")

    accepted = compatible_document_types(expected_doc_type)
    if accepted and doc_type not in accepted:
        expected_label = EXPECTED_LABELS.get(expected_doc_type or "", expected_doc_type or "documento")
        errors.append(f"Este archivo parece {user_document_name(doc_type)}, pero se esperaba {expected_label}")

    if confidence == "baja" or quality == "mala" or data.get("evidencia_insuficiente"):
        warnings.append("El documento se lee con baja claridad")

    if (
        expected_doc_type == "identificacion_oficial"
        and doc_type in compatible_document_types("identificacion_oficial")
        and doc_type not in {"pasaporte_mexicano", "pasaporte_extranjero"}
    ):
        fr = data.get("frente_reverso") or {}
        if not fr.get("frente_detectado") or not fr.get("reverso_detectado"):
            warnings.append("No se detecto frente y reverso completos")

    if doc_type in compatible_document_types("identificacion_oficial"):
        if not fields.get("nombre_completo"):
            errors.append("No se pudo leer el nombre completo")
        exp = parse_date(fields.get("fecha_vencimiento"))
        if exp and exp < today_cdmx():
            errors.append(f"Identificacion vencida desde {exp.isoformat()}")

    if doc_type == "comprobante_domicilio":
        if not fields.get("domicilio"):
            errors.append("No se pudo leer el domicilio")
        issued = (
            parse_date(fields.get("fecha_emision"))
            or parse_date(fields.get("fecha_vencimiento"))
            or parse_date(fields.get("periodo___SPARTA_SECRET_REDACTED___fin"))
            or parse_date(fields.get("periodo___SPARTA_SECRET_REDACTED___inicio"))
        )
        months = months_since(issued)
        if months is None:
            errors.append("No se encontro fecha del comprobante")
        elif months > 3:
            errors.append(f"Comprobante vencido: {months} meses de antiguedad")

    if doc_type == "curp":
        if not fields.get("curp"):
            errors.append("No se pudo leer la CURP")
        issued = parse_date(fields.get("fecha_emision")) or parse_date(fields.get("fecha_expedicion"))
        months = months_since(issued)
        if months is None:
            warnings.append("No se encontro fecha de emision de CURP")
        elif months > 2:
            errors.append(f"CURP vencida: {months} meses de antiguedad")

    if doc_type == "constancia_fiscal":
        pages = int(data.get("paginas_pdf") or data.get("paginas_analizadas") or 0)
        if pages and pages < 2:
            errors.append("La constancia fiscal debe incluir sus 2 hojas completas")
        if not fields.get("rfc"):
            errors.append("No se pudo leer el RFC")
        issued = parse_date(fields.get("fecha_emision")) or parse_date(fields.get("fecha_expedicion"))
        months = months_since(issued)
        if months is None:
            errors.append("No se encontro fecha de emision de constancia fiscal")
        elif months > 2:
            errors.append(f"Constancia fiscal vencida: {months} meses de antiguedad")
        if not _has_actividad_asalariado(fields) and not _has_sueldos_salarios(fields):
            errors.append("No se pudo confirmar actividad Asalariado ni regimen de Sueldos y Salarios")
        elif not _has_sueldos_salarios(fields):
            errors.append("No se pudo confirmar el regimen de Sueldos y Salarios")

    if doc_type == "__SPARTA_SECRET_REDACTED__":
        bank = fields.get("banco")
        if not bank:
            errors.append("No se pudo identificar el banco")
        elif is_digital_bank(bank):
            errors.append(f"Banco digital no aceptado: {bank}")
        if not fields.get("clabe"):
            warnings.append("No se detecto CLABE")

    if doc_type == "nss" and not fields.get("nss"):
        errors.append("No se pudo leer el NSS")

    if doc_type == "carta_no_adeudo":
        declarant = fields.get("nombre_completo")
        firma_detectada = _bool_from_field(fields.get("firma_detectada"))
        nombre_y_firma_lleno = _bool_from_field(fields.get("nombre_y_firma_lleno"))
        evidencia_insuficiente = _bool_from_field(data.get("evidencia_insuficiente"))
        if _blank_like_name(declarant):
            errors.append("La carta de no adeudo no tiene el nombre completo del candidato")
        if evidencia_insuficiente is True:
            errors.append("La carta de no adeudo no tiene evidencia suficiente de nombre y firma")
        if nombre_y_firma_lleno is False:
            errors.append("La linea de nombre completo y firma no esta llenada")
        if firma_detectada is False:
            errors.append("La carta de no adeudo no esta firmada")
        if firma_detectada is None and nombre_y_firma_lleno is None:
            warnings.append("No se pudo confirmar automaticamente la firma de la carta")

    if errors:
        message = "No se puede cargar este documento: " + "; ".join(errors) + "."
    elif warnings:
        message = f"{user_document_name(doc_type)} detectado. Revisa: " + "; ".join(warnings) + "."
    else:
        message = f"{user_document_name(doc_type)} listo."

    return {
        "estado": "verificacion_rapida",
        "aprobado": not errors,
        "requiere_revision_humana": bool(warnings),
        "errores": errors,
        "advertencias": warnings,
        "mensaje_usuario": message,
        "fecha_validacion_cdmx": today_cdmx().isoformat(),
    }


class AlibabaDocumentAI:
    def __init__(
        self,
        api_key: str,
        base_url: str,
        model: str,
        fallback_models: str = "",
        retry_delays: str = "0",
        timeout_seconds: int = 35,
        max_pages: int = 3,
        dpi: int = 150,
    ) -> None:
        self.api_key = api_key
        self.base_url = base_url.rstrip("/")
        self.model = model
        self.fallback_models = fallback_models
        self.retry_delays = parse_retry_delays(retry_delays)
        self.timeout_seconds = max(5, int(timeout_seconds or 35))
        self.max_pages = max(1, int(max_pages or 3))
        self.dpi = max(90, int(dpi or 150))

    def enabled(self) -> bool:
        return bool(self.api_key and self.base_url and self.model)

    def _call_content(self, content: List[Dict[str, Any]], max_tokens: int = 1600) -> tuple[Dict[str, Any], Dict[str, Any], str, bool]:
        endpoint = self.base_url + "/chat/completions"
        last_exc: Optional[Exception] = None
        model_chain = parse_model_chain(self.model, self.fallback_models)
        for model_idx, current_model in enumerate(model_chain):
            for attempt, delay in enumerate(self.retry_delays, start=1):
                if delay:
                    time.sleep(delay)
                try:
                    payload = {
                        "model": current_model,
                        "messages": [{"role": "user", "content": content}],
                        "temperature": 0,
                        "max_tokens": max_tokens,
                        "response_format": {"type": "json_object"},
                    }
                    req = urllib.request.Request(
                        endpoint,
                        data=json.dumps(payload).encode("utf-8"),
                        headers={
                            "Authorization": f"Bearer {self.api_key}",
                            "Content-Type": "application/json",
                        },
                        method="POST",
                    )
                    with urllib.request.urlopen(req, timeout=self.timeout_seconds) as response:
                        body = json.loads(response.read().decode("utf-8"))
                    text = ((body.get("choices") or [{}])[0].get("message") or {}).get("content") or ""
                    if not str(text).strip():
                        raise RuntimeError("Alibaba empty response content")
                    try:
                        parsed = extract_json(text)
                    except Exception as exc:
                        raise RuntimeError(f"Alibaba invalid JSON response: {exc}") from exc
                    return parsed, body.get("usage") or {}, current_model, model_idx > 0
                except urllib.error.HTTPError as exc:
                    try:
                        detail = exc.read().decode("utf-8", errors="replace")
                    except Exception:
                        detail = str(exc)
                    last_exc = RuntimeError(f"Alibaba HTTP {exc.code}: {detail}")
                except Exception as exc:
                    last_exc = exc
                if last_exc and (not is_transient_error(last_exc) or attempt >= len(self.retry_delays)):
                    break
        raise last_exc or RuntimeError("No se pudo llamar a Alibaba")

    def _call(self, pages: List[RenderedPage], prompt_text: str) -> tuple[Dict[str, Any], Dict[str, Any], str, bool]:
        return self._call_content(build_openai_content(pages, prompt_text), max_tokens=1600)

    def quick_verify(self, file_bytes: bytes, filename: str, expected_doc_type: str, nombre_candidato: Optional[str] = None) -> Dict[str, Any]:
        start = time.time()
        pages, page_count = render_input(file_bytes, filename, self.max_pages, self.dpi)
        if expected_doc_type == "identificacion_oficial":
            pages = pages + render_identificacion_assistida(file_bytes, filename, self.dpi)
        prompt = quick_prompt_for(expected_doc_type, nombre_candidato)
        extracted, usage, actual_model, fallback_used = self._call(pages, prompt)
        extracted["paginas_analizadas"] = extracted.get("paginas_analizadas") or len(pages)
        extracted["paginas_pdf"] = page_count
        validation = validate_quick_extracted(extracted, expected_doc_type)
        return {
            "provider": "alibaba",
            "model": actual_model,
            "requested_model": self.model,
            "fallback_used": fallback_used,
            "usage": usage,
            "file_name": filename,
            "expected_doc_type": expected_doc_type,
            "extraction": extracted,
            "validation": validation,
            "elapsed_ms": int((time.time() - start) * 1000),
        }

    def crosscheck_expediente(self, documents: List[Dict[str, Any]], nombre_candidato: Optional[str] = None) -> Dict[str, Any]:
        start = time.time()
        parts: List[Dict[str, Any]] = []
        rendered_meta: List[Dict[str, Any]] = []
        nombre = str(nombre_candidato or "").strip()
        parts.append({
            "type": "text",
            "text": "Nombre registrado del candidato: " + (nombre if nombre else "NO CAPTURADO"),
        })

        for doc in documents:
            key = str(doc.get("key") or "").strip()
            label = str(doc.get("label") or key).strip()
            filename = str(doc.get("filename") or f"{key}.pdf").strip()
            file_bytes = doc.get("bytes") or b""
            if not key or not file_bytes:
                continue
            summary = doc.get("summary")
            if summary_is_usable(summary):
                compact = compact_summary_for_prompt(summary)
                page_count = int(compact.get("paginas_pdf") or 0)
                rendered_meta.append({
                    "key": key,
                    "label": label,
                    "filename": filename,
                    "pages_rendered": 0,
                    "pages_pdf": page_count or None,
                    "source": "lectura_rapida_ia",
                })
                parts.append({
                    "type": "text",
                    "text": (
                        f"\nDOCUMENTO: {key}\n"
                        f"Nombre esperado: {label}\n"
                        f"Archivo: {filename}\n"
                        f"Entrada usada: lectura IA previa guardada (sin reenviar paginas)\n"
                        "JSON lectura IA previa:\n"
                        + json.dumps(compact, ensure_ascii=False, separators=(",", ":"))
                    ),
                })
                continue

            page_count = None
            try:
                if filename.lower().endswith(".pdf"):
                    pdf_doc = fitz.open(stream=file_bytes, filetype="pdf")
                    page_count = int(pdf_doc.page_count or 0)
                    pdf_doc.close()
            except Exception:
                page_count = None
            rendered_meta.append({
                "key": key,
                "label": label,
                "filename": filename,
                "pages_rendered": 0,
                "pages_pdf": page_count,
                "source": "sin_lectura_rapida",
            })
            parts.append({
                "type": "text",
                "text": (
                    f"\nDOCUMENTO: {key}\n"
                    f"Nombre esperado: {label}\n"
                    f"Archivo: {filename}\n"
                    f"Paginas PDF originales: {page_count}\n"
                    "Entrada usada: sin lectura IA previa suficiente. "
                    "Para evitar bloqueo operativo no se reenviaron imagenes en el cruce final. "
                    "Marca este documento como no_leido o requiere_revision si falta informacion para compararlo."
                ),
            })

        content = build_openai_content_from_parts(parts, CROSSCHECK_PROMPT)
        extracted, usage, actual_model, fallback_used = self._call_content(content, max_tokens=3400)
        if not isinstance(extracted.get("documentos"), dict):
            extracted["documentos"] = {}
        if not isinstance(extracted.get("comparaciones"), list):
            extracted["comparaciones"] = []
        if not isinstance(extracted.get("alertas"), list):
            extracted["alertas"] = []
        if not isinstance(extracted.get("recomendaciones"), list):
            extracted["recomendaciones"] = []
        if not isinstance(extracted.get("coincidencias"), dict):
            total = len(extracted["comparaciones"])
            ok = sum(1 for c in extracted["comparaciones"] if isinstance(c, dict) and c.get("coincide") is True)
            extracted["coincidencias"] = {"total": total, "ok": ok, "fallas": max(0, total - ok)}
        extracted["documentos_enviados"] = rendered_meta

        return {
            "provider": "alibaba",
            "model": actual_model,
            "requested_model": self.model,
            "fallback_used": fallback_used,
            "usage": usage,
            "analysis": extracted,
            "elapsed_ms": int((time.time() - start) * 1000),
        }
