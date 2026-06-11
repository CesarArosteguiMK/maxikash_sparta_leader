# app/api/routes.py
"""
Endpoints de la API REST.
"""
import asyncio
import re
import time
from fastapi import APIRouter, UploadFile, File, HTTPException, Depends, Query, Body, Form
from fastapi.security.api_key import APIKeyHeader
from typing import Optional, List, Dict, Any
from loguru import logger

from app.models.schemas import (
    VerificacionResponse, VerificacionCalidadResponse, TipoDocumento, ErrorResponse, ComprobanteResponse,
    CheckOCR, CheckForense
)
from app.services.verificacion import VerificacionService
from app.services.comprobante_analyzer import ComprobanteAnalyzer
from app.utils.nss_validator import validar_nss, extraer_nss_de_pdf
from app.utils.curp_validator import validar_curp, extraer_curp_de_pdf
from app.services.document_crosscheck import (
    extraer_datos_curp_pdf, extraer_datos_nss_pdf,
    extraer_datos_constancia_fiscal, extraer_datos_acta_nacimiento,
    extraer_informacion_ingresos_fad,
    extraer_datos_motocicleta_factura,
    validacion_cruzada,
    es_documento_nss, es_tarjeta_nss, es_documento_curp, es_documento_constancia_fiscal, es_documento_acta_nacimiento,
    pdf_paginas_a_png_bytes,
)
from app.core.config import get_settings

try:
    import fitz
    PYMUPDF_AVAILABLE = True
except ImportError:
    PYMUPDF_AVAILABLE = False

router = APIRouter()
settings = get_settings()

api_key_header = APIKeyHeader(name=settings.api_key_header, auto_error=False)

EXTENSIONES_PERMITIDAS = {"jpg", "jpeg", "png", "webp", "tiff"}
EXTENSIONES_COMPROBANTE = {"jpg", "jpeg", "png", "webp", "tiff", "pdf"}
MAX_SIZE_BYTES = settings.max_image_size_mb * 1024 * 1024


def _normalizar_texto_precheck(texto: str) -> str:
    """Normaliza texto OCR para detección rápida de identificación oficial."""
    if not texto:
        return ""
    reemplazos = {
        "Á": "A", "É": "E", "Í": "I", "Ó": "O", "Ú": "U", "Ü": "U", "Ñ": "N",
        "á": "A", "é": "E", "í": "I", "ó": "O", "ú": "U", "ü": "U", "ñ": "N",
    }
    for src, dst in reemplazos.items():
        texto = texto.replace(src, dst)
    return re.sub(r"\s+", " ", texto.upper()).strip()


def _indicadores_identificacion(texto: str) -> Dict[str, bool]:
    """Devuelve señales textuales suficientes para precheck rápido de ID."""
    t = _normalizar_texto_precheck(texto)
    lineas_mrz = re.findall(r"[A-Z0-9<]{20,}", t)
    tiene_mrz = bool(lineas_mrz and any("<<" in l or l.count("<") >= 2 for l in lineas_mrz))
    tiene_mrz_ine = bool(
        re.search(r"(?:^|\s)(?:ID\s*<?\s*MEX|IDMEX|I\s*<\s*MEX|I<MEX)|IDMEX", t)
        or (tiene_mrz and "MEX" in t and not re.search(r"\bP\s*<\s*[A-Z]{3}|P<[A-Z]{3}", t))
    )
    tiene_ine = bool(re.search(r"INSTITUTO\s+NACIONAL\s+ELECTORAL|CREDENCIAL\s+PARA\s+VOT", t) or tiene_mrz_ine)
    tiene_elector = bool(re.search(r"CLAVE\s*DE\s*ELECTOR|CLAVEDEELECTOR|CLAVE.{0,4}ELECTOR|SECCION\s*\d|ELECTORAL|VOTAR", t))
    tiene_curp = bool(re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d", t))
    tiene_inm = bool(re.search(r"INSTITUTO\s+NACIONAL\s+DE\s+MIGRACION|MIGRACION|INM|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|RESIDENTE\s+(TEMPORAL|PERMANENTE)|NUE\s*[.:]?\s*\d", t))
    tiene_senales_pasaporte = bool(re.search(r"PASAPORTE|PASSPORT|PASSAPORT|PASAPORTE\s*NO|NO\.?\s*DE\s*PASAPORTE", t))
    tiene_campos_pasaporte = bool(
        re.search(r"ISSUE\s+DATE|EXPIRY\s+DATE|DATE\s+DE\s+D[EI][LÍI]V|DATE\s+D['E]XP|HOLDER'?S\s+SIGNATURE|PLACE\s+OF\s+BIRTH", t)
    )
    tiene_mrz_pasaporte = bool(re.search(r"\bP\s*<\s*[A-Z]{3}|P<[A-Z]{3}", t))
    tiene_pasaporte = bool(
        tiene_senales_pasaporte
        or tiene_mrz_pasaporte
        or (tiene_mrz and tiene_campos_pasaporte)
        or (re.search(r"ESTADOS\s+UNIDOS\s+MEXICANOS", t) and re.search(r"CLAVE\s+DEL\s+PAIS|MEX\b|PASAPORTE", t))
    )
    if tiene_mrz_ine and not tiene_senales_pasaporte:
        tiene_pasaporte = False
    return {
        "mrz": tiene_mrz,
        "ine": tiene_ine,
        "elector": tiene_elector,
        "curp": tiene_curp,
        "inm_residencia": tiene_inm,
        "pasaporte": tiene_pasaporte,
    }


def _parece_identificacion_oficial(texto: str) -> bool:
    indicadores = _indicadores_identificacion(texto)
    if indicadores["ine"] or indicadores["inm_residencia"] or indicadores["pasaporte"]:
        return True
    if indicadores["mrz"] and not indicadores["curp"]:
        return True
    return indicadores["curp"] and indicadores["elector"]


def _extraer_texto_pdf_rapido(pdf_bytes: bytes, max_paginas: int = 2) -> Dict[str, Any]:
    """
    Extrae texto mínimo para precheck. Primero usa capa de texto del PDF; si no hay,
    renderiza máximo 2 páginas a baja resolución y aplica OCR rápido.
    """
    if not PYMUPDF_AVAILABLE or not pdf_bytes or len(pdf_bytes) < 100:
        return {"texto": "", "paginas": 0, "modo": "sin_pymupdf"}

    texto = ""
    paginas = 0
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        paginas = doc.page_count
        for i, page in enumerate(doc):
            if i >= max_paginas:
                break
            texto += page.get_text() + "\n"
        if texto.strip():
            doc.close()
            return {"texto": texto, "paginas": paginas, "modo": "texto_pdf"}

        service = VerificacionService()
        # Pasaportes/IDs fotografiados suelen venir girados o con mucho margen blanco.
        # Probar primero la zona de contenido evita gastar OCR en toda la hoja.
        if paginas > 0:
            try:
                page = doc[0]
                rect = page.rect
                clip = fitz.Rect(0, rect.height * 0.20, rect.width, rect.height * 0.92)
                pix = page.get_pixmap(dpi=145, clip=clip)
                img_bytes = pix.tobytes("png")
                texto_crop = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1800)
                if _parece_identificacion_oficial(texto_crop):
                    doc.close()
                    return {"texto": texto_crop, "paginas": paginas, "modo": "ocr_zona_contenido_145dpi"}
                texto = texto_crop or ""
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: crop inicial falló: {e}")
        for dpi, max_ancho in ((95, 1000), (150, 1400)):
            texto_full = ""
            for i, page in enumerate(doc):
                if i >= max_paginas:
                    break
                pix = page.get_pixmap(dpi=dpi)
                img_bytes = pix.tobytes("png")
                texto_full += service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=max_ancho) + "\n"
            texto_total = (texto or "") + "\n" + texto_full
            if _parece_identificacion_oficial(texto_total):
                doc.close()
                return {"texto": texto_total, "paginas": paginas, "modo": f"ocr_rapido_{dpi}dpi"}
            texto = texto_total

        # Fallback ligero para INE escaneada: enfoca zona de datos/clave sin correr validación profunda.
        if paginas > 0:
            try:
                page = doc[0]
                pix = page.get_pixmap(dpi=120)
                img_bytes = pix.tobytes("png")
                texto_dedicado = service.ocr_analyzer._extraer_mrz_dedicado(img_bytes)
                curp_zona = service.ocr_analyzer._extraer_curp_zona_dedicada(img_bytes)
                texto = (texto or "") + "\n" + (texto_dedicado or "")
                if curp_zona:
                    texto += "\nCURP " + curp_zona
                if _parece_identificacion_oficial(texto):
                    doc.close()
                    return {"texto": texto, "paginas": paginas, "modo": "ocr_zona_datos_120dpi"}
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: fallback zona datos falló: {e}")

        # Pasaporte/ID fotografiado dentro de una página: suele venir con mucho margen blanco.
        # Recortar la franja central-inferior evita que el OCR lea solo el fondo.
        if paginas > 0:
            try:
                page = doc[0]
                rect = page.rect
                clip = fitz.Rect(0, rect.height * 0.22, rect.width, rect.height * 0.90)
                for dpi in (160, 220):
                    pix = page.get_pixmap(dpi=dpi, clip=clip)
                    img_bytes = pix.tobytes("png")
                    texto_crop = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=2200)
                    texto_total = (texto or "") + "\n" + (texto_crop or "")
                    if _parece_identificacion_oficial(texto_total):
                        doc.close()
                        return {"texto": texto_total, "paginas": paginas, "modo": f"ocr_zona_contenido_{dpi}dpi"}
                    texto = texto_total
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: fallback zona contenido falló: {e}")
        doc.close()
        return {"texto": texto, "paginas": paginas, "modo": "ocr_rapido"}
    except Exception as e:
        logger.warning(f"precheck_identificacion_pdf: error leyendo PDF: {e}")
        return {"texto": texto, "paginas": paginas, "modo": "error"}


def _texto_pdf_embebido_rapido(pdf_bytes: bytes, max_paginas: int = 2) -> Dict[str, Any]:
    """Lee solo texto embebido del PDF, sin OCR, para no bloquear con escaneos."""
    if not PYMUPDF_AVAILABLE:
        return {"texto": "", "paginas": 0, "modo": "pymupdf_no_disponible"}
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        paginas = len(doc)
        texto = []
        for i, page in enumerate(doc):
            if i >= max_paginas:
                break
            texto.append(page.get_text("text") or "")
        doc.close()
        return {"texto": "\n".join(texto), "paginas": paginas, "modo": "texto_pdf_embebido"}
    except Exception as e:
        logger.warning(f"texto_pdf_embebido_rapido: error leyendo PDF: {e}")
        return {"texto": "", "paginas": 0, "modo": "error"}


def _rechazo_identificacion_por_texto_equivocado(texto: str, paginas: int, modo: str, inicio: float) -> Optional[Dict[str, Any]]:
    """Rechaza rapido PDFs que claramente pertenecen a otro campo documental."""
    texto_upper = (texto or "").upper()
    if not texto_upper.strip():
        return None
    indicadores = _indicadores_identificacion(texto_upper)
    if _parece_identificacion_oficial(texto_upper):
        return None
    reglas = [
        (
            r"ASIGNACI[OÓ]N\s+O\s+LOCALIZACI[OÓ]N|N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b",
            "nss_en_identificacion",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
            "curp_en_identificacion",
            "El documento es una constancia CURP. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|\bSAT\b",
            "constancia_fiscal_en_identificacion",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICADO\s+DE\s+NACIMIENTO",
            "acta_en_identificacion",
            "El documento es un acta de nacimiento. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"ESTADO\s+DE\s+CUENTA|CUENTA\s+CLABE|NO\.?\s+DE\s+CUENTA|N[UÚ]MERO\s+DE\s+CUENTA|TARJETA\s+N[OÓ]MINA|BBVA|BANCOMER|SANTANDER|BANORTE|HSBC|SCOTIABANK|BANCO\s+AZTECA|NU\s+M[EÉ]XICO",
            "__SPARTA_SECRET_REDACTED___en_identificacion",
            "El documento corresponde a estado de cuenta bancario. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"COMPROBANTE\s+DE\s+DOMICILIO|RECIBO\s+DE\s+(LUZ|AGUA|TEL[EÉ]FONO)|COMISI[OÓ]N\s+FEDERAL\s+DE\s+ELECTRICIDAD|\bCFE\b|TELMEX|IZZI|TOTALPLAY",
            "comprobante_domicilio_en_identificacion",
            "El documento corresponde a comprobante de domicilio. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"SOLICITUD\s+(INTERNA|DE\s+EMPLEO|EMPLEO|DE\s+TRABAJO|TRABAJO)|CURR.?CULUM|CURRICULUM\s+VITAE|EXPERIENCIA\s+LABORAL",
            "solicitud_en_identificacion",
            "El documento corresponde a solicitud/CV. En este campo solo se acepta identificacion oficial.",
        ),
    ]
    for patron, motivo, mensaje in reglas:
        if re.search(patron, texto_upper):
            return _respuesta_rechazo(
                motivo,
                mensaje,
                paginas=paginas,
                indicadores=indicadores,
                modo=modo,
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
    return None


def _detectar_documento_equivocado_identificacion_pdf(pdf_bytes: bytes, inicio: float) -> Optional[Dict[str, Any]]:
    """Precheck barato: texto embebido y OCR de primera pagina a baja resolucion."""
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    rechazo = _rechazo_identificacion_por_texto_equivocado(
        info.get("texto") or "",
        int(info.get("paginas") or 0),
        "documento_equivocado_texto_embebido",
        inicio,
    )
    if rechazo:
        return rechazo
    if not PYMUPDF_AVAILABLE:
        return None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        paginas = doc.page_count
        if paginas <= 0:
            doc.close()
            return None
        page = doc[0]
        pix = page.get_pixmap(dpi=105)
        img_bytes = pix.tobytes("png")
        doc.close()
        service = VerificacionService()
        texto = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1000)
        return _rechazo_identificacion_por_texto_equivocado(
            texto,
            paginas,
            "documento_equivocado_ocr_rapido",
            inicio,
        )
    except Exception as e:
        logger.debug(f"detectar documento equivocado identificacion fallo: {e}")
        return None


def _rechazo_curp_por_texto_equivocado(texto: str, inicio: float, modo: str) -> Optional[Dict[str, Any]]:
    """Rechaza documentos claramente equivocados antes del extractor CURP pesado."""
    texto_upper = (texto or "").upper()
    if not texto_upper.strip():
        return None
    if re.search(r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N", texto_upper):
        return None
    reglas = [
        (
            r"PASAPORTE|CREDENCIAL\s+PARA\s+VOTAR|INSTITUTO\s+NACIONAL\s+ELECTORAL|\bINE\b|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|INSTITUTO\s+NACIONAL\s+DE\s+MIGRACI[OÓ]N|\bINM\b",
            "identificacion_en_curp",
            "El documento corresponde a identificacion oficial. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
        (
            r"SOLICITUD\s+(INTERNA|DE\s+EMPLEO|EMPLEO|DE\s+TRABAJO|TRABAJO)|CURR.?CULUM|CURRICULUM\s+VITAE|EXPERIENCIA\s+LABORAL",
            "solicitud_en_curp",
            "El documento corresponde a solicitud/CV. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
        (
            r"ASIGNACI[OÓ]N\s+O\s+LOCALIZACI[OÓ]N|N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b",
            "nss_en_curp",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|\bSAT\b",
            "constancia_fiscal_en_curp",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICADO\s+DE\s+NACIMIENTO",
            "acta_en_curp",
            "El documento es un acta de nacimiento. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
    ]
    for patron, motivo, mensaje in reglas:
        if re.search(patron, texto_upper):
            return _respuesta_rechazo(
                motivo,
                mensaje,
                curp_extraido=None,
                es_reciente=None,
                meses_antiguedad=None,
                fecha_emision=None,
                modo=modo,
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
    return None


def _detectar_documento_equivocado_curp_pdf(pdf_bytes: bytes, inicio: float) -> Optional[Dict[str, Any]]:
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    rechazo = _rechazo_curp_por_texto_equivocado(
        info.get("texto") or "",
        inicio,
        "documento_equivocado_texto_embebido",
    )
    if rechazo:
        return rechazo
    if not PYMUPDF_AVAILABLE:
        return None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        if doc.page_count <= 0:
            doc.close()
            return None
        page = doc[0]
        pix = page.get_pixmap(dpi=105)
        img_bytes = pix.tobytes("png")
        doc.close()
        service = VerificacionService()
        texto = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1000)
        return _rechazo_curp_por_texto_equivocado(
            texto,
            inicio,
            "documento_equivocado_ocr_rapido",
        )
    except Exception as e:
        logger.debug(f"detectar documento equivocado curp fallo: {e}")
        return None


def _rechazo___SPARTA_SECRET_REDACTED___por_texto_equivocado(texto: str, inicio: float, modo: str) -> Optional[Dict[str, Any]]:
    """Rechaza documentos claramente equivocados antes del OCR pesado de estado de cuenta."""
    texto_upper = _normalizar_texto_precheck(texto)
    reglas = [
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|\bSAT\b",
            "constancia_fiscal_en___SPARTA_SECRET_REDACTED__",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
            "curp_en___SPARTA_SECRET_REDACTED__",
            "El documento es una constancia CURP. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b|CONSTANCIA\s+.*NSS|TARJETA\s+NSS",
            "nss_en___SPARTA_SECRET_REDACTED__",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICAD[AO]\s+DE\s+NACIMIENTO",
            "acta_en___SPARTA_SECRET_REDACTED__",
            "El documento es un acta de nacimiento. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"SOLICITUD\s+DE\s+EMPLEO|CURRICULUM|CURR[IÍ]CULO|EXPERIENCIA\s+LABORAL",
            "solicitud_en___SPARTA_SECRET_REDACTED__",
            "El documento corresponde a solicitud/CV. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"CREDENCIAL\s+PARA\s+VOTAR|INSTITUTO\s+NACIONAL\s+ELECTORAL|PASAPORTE|PASSPORT|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|INSTITUTO\s+NACIONAL\s+DE\s+MIGRACI[OÓ]N",
            "identificacion_en___SPARTA_SECRET_REDACTED__",
            "El documento corresponde a identificación oficial. En este campo solo se acepta estado de cuenta bancario.",
        ),
    ]
    for patron, motivo, mensaje in reglas:
        if re.search(patron, texto_upper):
            return _respuesta_rechazo(
                motivo,
                mensaje,
                modo=modo,
                tiempo_ms=int((time.time() - inicio) * 1000),
                banco_detectado=None,
                nombre_propietario=None,
                clabe=None,
                es_banco_fisico=False,
                tiene_datos_titular=False,
            )
    return None


def _detectar_documento_equivocado___SPARTA_SECRET_REDACTED___pdf(pdf_bytes: bytes, inicio: float) -> Optional[Dict[str, Any]]:
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    rechazo = _rechazo___SPARTA_SECRET_REDACTED___por_texto_equivocado(
        info.get("texto") or "",
        inicio,
        "documento_equivocado_texto_embebido",
    )
    if rechazo:
        return rechazo
    if not PYMUPDF_AVAILABLE:
        return None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        if doc.page_count <= 0:
            doc.close()
            return None
        page = doc[0]
        pix = page.get_pixmap(dpi=105)
        img_bytes = pix.tobytes("png")
        doc.close()
        service = VerificacionService()
        texto = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1000)
        return _rechazo___SPARTA_SECRET_REDACTED___por_texto_equivocado(
            texto,
            inicio,
            "documento_equivocado_ocr_rapido",
        )
    except Exception as e:
        logger.debug(f"detectar documento equivocado __SPARTA_SECRET_REDACTED__ fallo: {e}")
    return None


def _rechazo_por_texto_para_campo(texto: str, campo: str, mensaje_campo: str, **extra: Any) -> Optional[Dict[str, Any]]:
    """Rechazo barato cuando el PDF claramente pertenece a otro campo documental."""
    texto_upper = _normalizar_texto_precheck(texto)
    parece_nss_texto = bool(
        re.search(
            r"INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b|N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|\bNSS\b|"
            r"VIGENCIA\s+DE\s+DERECHOS|CONSTANCIA\s+DE\s+VIGENCIA|SEMANAS\s+COTIZADAS|HISTORIAL\s+DE\s+REGISTROS\s+AFILIATORIOS",
            texto_upper,
        )
    )
    parece_fiscal_texto = bool(
        re.search(
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|C[ÉE]DULA\s+DE\s+IDENTIFICACI[OÓ]N\s+FISCAL|"
            r"REGISTRO\s+FEDERAL\s+DE\s+CONTRIBUYENTES|\bRFC\b|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|\bSAT\b|IDCIF",
            texto_upper,
        )
    )
    reglas = [
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|\bSAT\b",
            "constancia_fiscal",
            "El documento es una constancia fiscal SAT.",
        ),
        (
            r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
            "curp",
            "El documento es una constancia CURP.",
        ),
        (
            r"N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b|CONSTANCIA\s+.*NSS|TARJETA\s+NSS",
            "nss",
            "El documento corresponde a NSS del IMSS.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICAD[AO]\s+DE\s+NACIMIENTO",
            "acta",
            "El documento es un acta de nacimiento.",
        ),
        (
            r"ESTADO\s+DE\s+CUENTA|CUENTA\s+CLABE|NO\.?\s+DE\s+CUENTA|CLABE\s+INTERBANCARIA|BBVA|BANCOMER|SANTANDER|BANORTE|HSBC|SCOTIABANK",
            "__SPARTA_SECRET_REDACTED__",
            "El documento corresponde a estado de cuenta bancario.",
        ),
        (
            r"CREDENCIAL\s+PARA\s+VOTAR|INSTITUTO\s+NACIONAL\s+ELECTORAL|PASAPORTE|PASSPORT|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|INSTITUTO\s+NACIONAL\s+DE\s+MIGRACI[OÓ]N",
            "identificacion",
            "El documento corresponde a identificación oficial.",
        ),
        (
            r"SOLICITUD\s+DE\s+EMPLEO|CURRICULUM|CURR[IÍ]CULO|EXPERIENCIA\s+LABORAL",
            "solicitud",
            "El documento corresponde a solicitud/CV.",
        ),
    ]
    for patron, tipo, base_msg in reglas:
        if tipo == campo:
            continue
        if campo == "nss" and tipo == "curp" and parece_nss_texto:
            continue
        if campo == "constancia_fiscal" and tipo == "curp" and parece_fiscal_texto:
            continue
        if re.search(patron, texto_upper):
            return _respuesta_rechazo(
                f"{tipo}_en_{campo}",
                f"{base_msg} {mensaje_campo}",
                **extra,
            )
    return None


def _respuesta_comprobante_rechazo(mensaje: str, inicio: float) -> ComprobanteResponse:
    return ComprobanteResponse(
        tipo_comprobante=None,
        score_validacion=0,
        resultado="RECHAZADO",
        nombre_titular=None,
        direccion=None,
        fecha_documento=None,
        es_reciente=None,
        meses_antiguedad=None,
        empresa=None,
        alertas=[mensaje],
        recomendacion=mensaje,
        tiempo_proceso_ms=int((time.time() - inicio) * 1000),
    )


def _nombre_archivo_indica_otro_documento(nombre_archivo: str, campo: str, mensaje_campo: str, **extra: Any) -> Optional[Dict[str, Any]]:
    nombre = _normalizar_texto_precheck(nombre_archivo or "")
    reglas = [
        (r"CONSTANCIA.*(SF|FISCAL|SAT)|SITUACION\s+FISCAL|\bCSF\b", "constancia_fiscal", "El archivo parece ser una constancia fiscal SAT."),
        (r"\bCURP\b|CONSTANCIA.*CURP", "curp", "El archivo parece ser una constancia CURP."),
        (r"\bNSS\b|SEGURO\s+SOCIAL", "nss", "El archivo parece corresponder a NSS del IMSS."),
        (r"ACTA.*NACIMIENTO|NACIMIENTO", "acta", "El archivo parece ser un acta de nacimiento."),
        (r"ESTADO.*CUENTA|CUENTA.*BANC", "__SPARTA_SECRET_REDACTED__", "El archivo parece ser un estado de cuenta bancario."),
        (r"IDENTIFICACI[OÓ]N|IDENTIFICACION|INDENTIFICACION|INE|PASAPORTE|PASSPORT|RESIDENCIA", "identificacion", "El archivo parece ser una identificación oficial."),
        (r"SOLICITUD|CURRICULUM|CURR[IÍ]CULO|CV", "solicitud", "El archivo parece corresponder a solicitud/CV."),
    ]
    for patron, tipo, base_msg in reglas:
        if tipo == campo:
            continue
        if re.search(patron, nombre):
            return _respuesta_rechazo(
                f"{tipo}_en_{campo}_nombre_archivo",
                f"{base_msg} {mensaje_campo}",
                **extra,
            )
    return None


def _nombre_archivo_rechazo_comprobante(nombre_archivo: str, inicio: float) -> Optional[ComprobanteResponse]:
    rechazo = _nombre_archivo_indica_otro_documento(
        nombre_archivo,
        "comprobante_domicilio",
        "En este campo debe subir un comprobante de domicilio.",
    )
    if rechazo:
        return _respuesta_comprobante_rechazo(rechazo.get("mensaje") or "El archivo no corresponde a comprobante de domicilio.", inicio)
    return None


def _rechazo_comprobante_domicilio_rapido(pdf_bytes: bytes, inicio: float) -> Optional[ComprobanteResponse]:
    """Evita mandar documentos claramente equivocados al analizador de comprobante."""
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    texto = info.get("texto") or ""
    texto_upper = _normalizar_texto_precheck(texto)
    reglas = [
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|C[ÉE]DULA\s+DE\s+IDENTIFICACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT",
            "Este documento es una constancia fiscal SAT, no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
        (
            r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
            "Este documento es una constancia CURP, no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
        (
            r"N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b|CONSTANCIA\s+.*NSS|TARJETA\s+NSS",
            "Este documento corresponde a NSS del IMSS, no a comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICAD[AO]\s+DE\s+NACIMIENTO",
            "Este documento es un acta de nacimiento, no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
        (
            r"SOLICITUD\s+DE\s+EMPLEO|CURRICULUM|CURR[IÍ]CULO|EXPERIENCIA\s+LABORAL",
            "Este documento corresponde a solicitud/CV, no a comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
    ]
    for patron, mensaje in reglas:
        if re.search(patron, texto_upper):
            return _respuesta_comprobante_rechazo(mensaje, inicio)
    try:
        indicadores = _indicadores_identificacion(texto_upper)
        if any(indicadores.get(k) for k in ("ine", "elector", "inm_residencia", "pasaporte", "mrz")):
            return _respuesta_comprobante_rechazo(
                "Este documento es una identificación oficial, no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
                inicio,
            )
        if len(texto_upper) < 80 or not any(indicadores.get(k) for k in ("ine", "elector", "inm_residencia", "pasaporte", "mrz")):
            texto_id = _extraer_texto_identificacion_pdf(pdf_bytes, max_paginas=1, max_chars=2500, timeout_s=2.5)
            indicadores_id = _indicadores_identificacion(texto_id)
            if any(indicadores_id.get(k) for k in ("ine", "elector", "inm_residencia", "pasaporte", "mrz")):
                return _respuesta_comprobante_rechazo(
                    "Este documento es una identificación oficial, no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
                    inicio,
                )
    except Exception as e:
        logger.debug(f"rechazo comprobante rapido fallo: {e}")
    return None


async def verificar_api_key(api_key: str = Depends(api_key_header)):
    """Verifica que el request tenga una API key válida."""
    if not api_key or api_key != settings.master_api_key:
        raise HTTPException(status_code=403, detail="API Key inválida o faltante")
    return api_key


def validar_imagen(file: UploadFile) -> None:
    """Valida extensión y tamaño de la imagen."""
    extension = file.filename.split(".")[-1].lower() if file.filename else ""
    if extension not in EXTENSIONES_PERMITIDAS:
        raise HTTPException(
            status_code=400,
            detail=f"Formato no permitido. Use: {', '.join(EXTENSIONES_PERMITIDAS)}"
        )


async def _ejecutar_pdf_timeout(nombre: str, func, *args, timeout: int = 8, fallback: Optional[Dict[str, Any]] = None):
    """Ejecuta extracción de PDF sin dejar que una OCR difícil bloquee el expediente."""
    try:
        return await asyncio.wait_for(asyncio.to_thread(func, *args), timeout=timeout)
    except asyncio.TimeoutError:
        logger.warning(f"{nombre}: timeout tras {timeout}s; se enviará a revisión manual")
        data = {"revision_manual": True, "timeout": True, "mensaje": f"{nombre} no se pudo leer a tiempo."}
        if fallback:
            data.update(fallback)
        return data
    except Exception as e:
        logger.warning(f"{nombre}: error en extracción PDF: {e}")
        data = {"revision_manual": True, "error": str(e), "mensaje": f"{nombre} no se pudo leer automáticamente."}
        if fallback:
            data.update(fallback)
        return data


def _respuesta_rechazo(motivo: str, mensaje: str, **extra) -> Dict[str, Any]:
    data: Dict[str, Any] = {
        "valido": False,
        "revision_manual": False,
        "rechazado": True,
        "motivo_rechazo": motivo,
        "mensaje": mensaje,
    }
    data.update(extra)
    return data


@router.post(
    "/verificar",
    response_model=VerificacionResponse,
    summary="Verificar autenticidad de documento",
    description="""
    Analiza una imagen de documento oficial mexicano y determina si es original,
    requiere revisión manual, o debe ser rechazado.

    **Documentos soportados:**
    - INE / Credencial para Votar (nueva y anterior)
    - Residencia Temporal INM
    - Residencia Temporal Acumulativa INM
    - Residencia Permanente INM

    **Capas de análisis:**
    1. Metadatos del archivo (15%)
    2. Análisis forense ELA + moiré (20%)
    3. Geometría y proporciones (15%)
    4. OCR + validación de campos (30%)
    5. Códigos QR / Barcode (10%)
    6. Clasificador ML (10%)
    """,
    tags=["Verificación"]
)
async def verificar_documento(
    imagen: UploadFile = File(..., description="Imagen del documento (JPG, PNG, WEBP, TIFF)"),
    tipo_documento: Optional[TipoDocumento] = Query(
        None,
        description="Tipo de documento. Si no se especifica, se auto-detecta."
    ),
    api_key: str = Depends(verificar_api_key)
):
    """
    Endpoint principal de verificación de documentos.
    """
    validar_imagen(imagen)
    image_bytes = await imagen.read()

    if len(image_bytes) == 0:
        raise HTTPException(status_code=400, detail="Imagen vacía")

    if len(image_bytes) > MAX_SIZE_BYTES:
        raise HTTPException(
            status_code=413,
            detail=f"Imagen muy grande. Máximo {settings.max_image_size_mb}MB"
        )

    logger.info(f"Verificando documento - tipo: {tipo_documento} - tamaño: {len(image_bytes)/1024:.1f}KB")

    try:
        service = VerificacionService()
        resultado = await service.verificar(image_bytes, tipo_documento)
        return resultado
    except Exception as e:
        logger.exception(f"Error en verificación: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Error interno procesando el documento: {str(e)}"
        )


@router.post(
    "/verificar-calidad",
    response_model=VerificacionCalidadResponse,
    summary="Verificación ligera de calidad de imagen (identificación)",
    description="""
    Primera revisión rápida: comprueba que la imagen no esté borrosa,
    sin exceso de brillo/reflejos y que parezca un documento.
    Si se envía **lado_esperado** (frente o reverso), además valida que la imagen
    corresponda a ese lado (MRZ = reverso, CURP/CREDENCIAL = frente).
    """,
    tags=["Verificación"]
)
async def verificar_calidad_documento(
    imagen: UploadFile = File(..., description="Imagen del documento (frente o reverso)"),
    lado_esperado: Optional[str] = Query(None, description="Si es 'frente' o 'reverso', se valida que la imagen sea de ese lado"),
    api_key: str = Depends(verificar_api_key)
):
    validar_imagen(imagen)
    image_bytes = await imagen.read()
    if len(image_bytes) == 0:
        raise HTTPException(status_code=400, detail="Imagen vacía")
    if len(image_bytes) > MAX_SIZE_BYTES:
        raise HTTPException(
            status_code=413,
            detail=f"Imagen muy grande. Máximo {settings.max_image_size_mb}MB"
        )
    if lado_esperado is not None and lado_esperado not in ("frente", "reverso"):
        lado_esperado = None
    try:
        service = VerificacionService()
        return await service.verificar_calidad(image_bytes, lado_esperado=lado_esperado)
    except Exception as e:
        logger.exception(f"Error en verificación calidad: {e}")
        return VerificacionCalidadResponse(
            ok=False,
            mensaje="No se pudo verificar la imagen. Intenta de nuevo.",
            alertas=[str(e)],
            lado_detectado=None,
        )


@router.post(
    "/verificar-comprobante",
    response_model=ComprobanteResponse,
    summary="Verificar comprobante de domicilio",
    description="""
    Analiza un comprobante de domicilio (PDF o imagen) y extrae:
    nombre del titular, dirección, fecha, tipo de servicio.
    Verifica que no tenga más de 3 meses de antigüedad.

    **Formatos soportados:** PDF, JPG, PNG
    **Tipos reconocidos:** CFE/Luz, Agua, Gas, Teléfono/Internet, Banco, Predial
    """,
    tags=["Comprobante"]
)
async def verificar_comprobante(
    documento: UploadFile = File(..., description="Comprobante de domicilio (PDF, JPG, PNG)"),
    api_key: str = Depends(verificar_api_key)
):
    extension = documento.filename.split(".")[-1].lower() if documento.filename else ""
    if extension not in EXTENSIONES_COMPROBANTE:
        raise HTTPException(
            status_code=400,
            detail=f"Formato no permitido. Use: {', '.join(EXTENSIONES_COMPROBANTE)}"
        )

    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    if len(file_bytes) > MAX_SIZE_BYTES:
        raise HTTPException(status_code=413, detail=f"Documento muy grande. Máximo {settings.max_image_size_mb}MB")

    logger.info(f"Verificando comprobante - archivo: {documento.filename} - tamaño: {len(file_bytes)/1024:.1f}KB")

    inicio = time.time()
    rechazo_nombre = _nombre_archivo_rechazo_comprobante(documento.filename or "", inicio)
    if rechazo_nombre:
        return rechazo_nombre
    if extension == "pdf":
        rechazo_rapido = _rechazo_comprobante_domicilio_rapido(file_bytes, inicio)
        if rechazo_rapido:
            return rechazo_rapido

    try:
        analyzer = ComprobanteAnalyzer()
        check = analyzer.analyze(file_bytes, documento.filename or "")
        tiempo_ms = int((time.time() - inicio) * 1000)

        score = round(check.score * 100)
        resultado = "RECHAZADO"
        recomendacion = "Comprobante no válido o no se pudo verificar."
        if check.alertas:
            recomendacion = check.alertas[0]

        tipo_label = ""
        if check.tipo_comprobante == "CFE_LUZ":
            tipo_label = "luz (CFE)"
        elif check.tipo_comprobante == "AGUA":
            tipo_label = "agua"
        elif check.tipo_comprobante == "GAS":
            tipo_label = "gas"
        elif check.tipo_comprobante == "TELEFONO_INTERNET":
            tipo_label = "teléfono o internet"
        elif check.tipo_comprobante == "BANCO":
            tipo_label = "banco"
        elif check.tipo_comprobante == "PREDIAL":
            tipo_label = "predial"
        elif check.tipo_comprobante:
            tipo_label = (check.tipo_comprobante or "").replace("_", " ").title()
        prefijo = f"Comprobante de {tipo_label} rechazado: " if tipo_label else "Comprobante rechazado: "
        alerta_principal = (check.alertas[0] if check.alertas else "") or ""
        es_documento_equivocado = alerta_principal.startswith("Este documento es ") or alerta_principal.startswith("Este documento no es ")

        if es_documento_equivocado:
            resultado = "RECHAZADO"
            recomendacion = alerta_principal
        elif check.es_reciente is False:
            resultado = "RECHAZADO"
            recomendacion = prefijo + "tiene más de 3 meses de antigüedad. Se requiere máximo 3 meses."
        elif check.es_reciente is None and not check.fecha_documento:
            resultado = "REVISION_MANUAL"
            if any("no se pudo extraer texto" in (a or "").lower() for a in (check.alertas or [])):
                recomendacion = "Comprobante recibido; no se pudo extraer texto legible para validar la fecha. Revisar manualmente."
            else:
                recomendacion = "Comprobante recibido; no se pudo verificar la fecha automáticamente. Revisar manualmente."
        elif score >= 75 and check.es_reciente is True:
            resultado = "APROBADO"
            recomendacion = f"Comprobante de {tipo_label} válido y reciente. Puede procesarse." if tipo_label else "Comprobante válido y reciente. Puede procesarse."
        elif score >= 50:
            resultado = "REVISION_MANUAL"
            recomendacion = "Comprobante requiere revisión manual."

        else:
            resultado = "REVISION_MANUAL"
            recomendacion = "Comprobante recibido; no se pudo verificar con suficiente confianza. Revisar manualmente."

        return ComprobanteResponse(
            tipo_comprobante=check.tipo_comprobante,
            score_validacion=score,
            resultado=resultado,
            nombre_titular=check.nombre_titular,
            direccion=check.direccion_detectada,
            fecha_documento=check.fecha_documento,
            es_reciente=check.es_reciente,
            meses_antiguedad=check.meses_antiguedad,
            empresa=check.empresa_detectada,
            alertas=check.alertas,
            recomendacion=recomendacion,
            tiempo_proceso_ms=tiempo_ms,
        )
    except Exception as e:
        logger.exception(f"Error en verificación de comprobante: {e}")
        raise HTTPException(status_code=500, detail=f"Error procesando comprobante: {str(e)}")


@router.post(
    "/verificar-nss",
    summary="Validar NSS (formato y dígito verificador)",
    description="Valida que un NSS tenga 11 dígitos y que el dígito verificador sea correcto. No consulta IMSS. Número de prueba desde NSS.pdf: 03239629730.",
    tags=["Utilidades"]
)
async def verificar_nss(
    body: dict = Body(..., example={"nss": "03239629730"}),
    api_key: str = Depends(verificar_api_key)
):
    """
    Recibe JSON con clave 'nss'. Retorna si el formato y dígito verificador son válidos.
    """
    nss = body.get("nss") if isinstance(body, dict) else None
    if nss is None:
        raise HTTPException(status_code=400, detail="Falta el campo 'nss' en el cuerpo")
    valido, mensaje = validar_nss(str(nss))
    return {"valido": valido, "mensaje": mensaje}


@router.post(
    "/verificar-nss-documento",
    summary="Extraer y validar NSS desde PDF (vigencia de derechos / constancia IMSS)",
    description="Sube el PDF de vigencia de derechos del IMSS (imss.gob.mx), constancia de vigencia FF-IMSS-012, o constancia de semanas cotizadas. La tarjeta NSS (imprimir y recortar) se detecta y se rechaza sin mandarla a revisión manual. Retorna nss_extraido, válido y mensaje.",
    tags=["Utilidades"]
)
async def verificar_nss_documento(
    documento: UploadFile = File(..., description="PDF de vigencia de derechos IMSS o constancia de NSS"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de vigencia de derechos del IMSS")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    rechazo_nombre = _nombre_archivo_indica_otro_documento(
        documento.filename or "",
        "nss",
        "En NSS debe subir constancia o vigencia de derechos del IMSS.",
        nss_extraido=None,
    )
    if rechazo_nombre:
        return rechazo_nombre
    info_rapida = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
    rechazo_rapido = _rechazo_por_texto_para_campo(
        info_rapida.get("texto") or "",
        "nss",
        "En NSS debe subir constancia o vigencia de derechos del IMSS.",
        nss_extraido=None,
    )
    if rechazo_rapido:
        return rechazo_rapido
    try:
        tarjeta_nss = await asyncio.wait_for(asyncio.to_thread(es_tarjeta_nss, file_bytes), timeout=4)
    except asyncio.TimeoutError:
        return {"nss_extraido": None, "valido": False, "revision_manual": True, "timeout": True, "mensaje": "No se pudo confirmar automáticamente el formato del NSS. Revisar manualmente."}

    if tarjeta_nss:
        nss_extraido = extraer_nss_de_pdf(file_bytes)
        datos_nss = extraer_datos_nss_pdf(file_bytes)
        return _respuesta_rechazo(
            "tarjeta_nss_no_aceptada",
            "No se acepta tarjeta NSS (imprimir y recortar). Debe subir constancia o vigencia de derechos del IMSS.",
            nss_extraido=nss_extraido,
            nombre=datos_nss.get("nombre") if isinstance(datos_nss, dict) else None,
            curp=datos_nss.get("curp") if isinstance(datos_nss, dict) else None,
            fecha_nacimiento=datos_nss.get("fecha_nacimiento") if isinstance(datos_nss, dict) else None,
        )

    try:
        parece_nss = await asyncio.wait_for(asyncio.to_thread(es_documento_nss, file_bytes), timeout=5)
    except asyncio.TimeoutError:
        return {"nss_extraido": None, "valido": False, "revision_manual": True, "timeout": True, "mensaje": "No se pudo confirmar automáticamente el formato del NSS. Revisar manualmente."}

    if parece_nss:
        nss_extraido = extraer_nss_de_pdf(file_bytes)
        if nss_extraido is None:
            return {"nss_extraido": None, "valido": False, "revision_manual": True, "mensaje": "No se encontró automáticamente un NSS de 11 dígitos. Revisar manualmente."}
        valido, mensaje = validar_nss(nss_extraido)
        datos_nss = extraer_datos_nss_pdf(file_bytes)
        nombre = datos_nss.get("nombre") if isinstance(datos_nss, dict) else None
        return {
            "nss_extraido": nss_extraido,
            "valido": valido,
            "mensaje": mensaje,
            "nombre": nombre,
            "curp": datos_nss.get("curp") if isinstance(datos_nss, dict) else None,
            "fecha_nacimiento": datos_nss.get("fecha_nacimiento") if isinstance(datos_nss, dict) else None,
        }

    if es_documento_curp(file_bytes):
        return _respuesta_rechazo(
            "documento_curp_en_nss",
            "El documento es una constancia CURP. En NSS debe subir constancia o vigencia de derechos del IMSS.",
            nss_extraido=None,
        )
    if es_documento_constancia_fiscal(file_bytes):
        return _respuesta_rechazo(
            "constancia_fiscal_en_nss",
            "El documento es una constancia fiscal SAT. En NSS debe subir constancia o vigencia de derechos del IMSS.",
            nss_extraido=None,
        )
    if es_documento_acta_nacimiento(file_bytes):
        return _respuesta_rechazo(
            "acta_en_nss",
            "El documento es un acta de nacimiento. En NSS debe subir constancia o vigencia de derechos del IMSS.",
            nss_extraido=None,
        )
    return {"nss_extraido": None, "valido": False, "revision_manual": True, "mensaje": "No se pudo confirmar automáticamente el formato del NSS. Revisar manualmente."}


@router.post(
    "/verificar-acta-documento",
    summary="Verificar que el PDF sea un acta de nacimiento",
    description="Sube un PDF de acta de nacimiento certificada. Verifica que contenga nombre y/o fecha de nacimiento propios del acta. Rechaza constancias de NSS u otros documentos.",
    tags=["Utilidades"]
)
async def verificar_acta_documento(
    documento: UploadFile = File(..., description="PDF de acta de nacimiento certificada"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de acta de nacimiento")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    inicio = time.time()
    texto_info = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
    texto_norm = _normalizar_texto_precheck(texto_info.get("texto") or "")

    if (
        ("INSTITUTO MEXICANO DEL SEGURO SOCIAL" in texto_norm or "IMSS" in texto_norm)
        and (
            "NUMERO DE SEGURIDAD SOCIAL" in texto_norm
            or "VIGENCIA DE DERECHOS" in texto_norm
            or "CONSTANCIA DE VIGENCIA" in texto_norm
            or "SEMANAS COTIZADAS" in texto_norm
            or "HISTORIAL DE REGISTROS AFILIATORIOS" in texto_norm
        )
    ):
        return _respuesta_rechazo(
            "nss_en_acta",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta acta de nacimiento certificada.",
            parece_acta=False,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )

    parece_acta = bool(
        "ACTA DE NACIMIENTO" in texto_norm
        or "CERTIFICADO DE NACIMIENTO" in texto_norm
        or "CERTIFICACION DE NACIMIENTO" in texto_norm
        or (("ACTA" in texto_norm or "REGISTRO CIVIL" in texto_norm) and "NACIMIENTO" in texto_norm)
    )
    if parece_acta:
        tiempo_ms = int((time.time() - inicio) * 1000)
        return {
            "valido": True,
            "mensaje": "Acta de nacimiento verificada.",
            "nombre": None,
            "fecha_nacimiento": None,
            "parece_acta": True,
            "modo_validacion": "texto_pdf_rapido",
            "tiempo_ms": tiempo_ms,
        }
    return {
        "valido": False,
        "revision_manual": True,
        "mensaje": "No se pudo confirmar automáticamente el acta de nacimiento. Revisar manualmente.",
        "parece_acta": False,
        "modo_validacion": "revision_manual_rapida",
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }
@router.post(
    "/verificar-constancia-fiscal-documento",
    summary="Verificar que el PDF sea constancia de situación fiscal (SAT)",
    description="Sube un PDF de constancia de situación fiscal del SAT. Verifica: vigencia (máx 2 meses desde fecha de emisión), actividad económica Asalariado y régimen Sueldos y Salarios.",
    tags=["Utilidades"]
)
async def verificar_constancia_fiscal_documento(
    documento: UploadFile = File(..., description="PDF de constancia de situación fiscal (SAT)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de constancia de situación fiscal")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    inicio = time.time()
    rechazo_nombre = _nombre_archivo_indica_otro_documento(
        documento.filename or "",
        "constancia_fiscal",
        "En este campo solo se acepta constancia de situación fiscal SAT.",
    )
    if rechazo_nombre:
        return rechazo_nombre
    info_rapida = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
    rechazo_rapido = _rechazo_por_texto_para_campo(
        info_rapida.get("texto") or "",
        "constancia_fiscal",
        "En este campo solo se acepta constancia de situación fiscal SAT.",
    )
    if rechazo_rapido:
        return rechazo_rapido
    try:
        datos = await asyncio.wait_for(
            asyncio.to_thread(extraer_datos_constancia_fiscal, file_bytes),
            timeout=8,
        )
    except asyncio.TimeoutError:
        return {
            "valido": False,
            "mensaje": "No se pudo leer la constancia a tiempo.",
            "timeout": True,
            "revision_manual": True,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }

    if not datos.get("parece_constancia_fiscal"):
        if es_tarjeta_nss(file_bytes) or await asyncio.to_thread(es_documento_nss, file_bytes):
            return _respuesta_rechazo(
                "nss_en_constancia_fiscal",
                "El documento corresponde a NSS del IMSS. En este campo solo se acepta constancia de situación fiscal SAT.",
            )
        if await asyncio.to_thread(es_documento_curp, file_bytes):
            return _respuesta_rechazo(
                "curp_en_constancia_fiscal",
                "El documento es una constancia CURP. En este campo solo se acepta constancia de situación fiscal SAT.",
            )
        if await asyncio.to_thread(es_documento_acta_nacimiento, file_bytes):
            return _respuesta_rechazo(
                "acta_en_constancia_fiscal",
                "El documento es un acta de nacimiento. En este campo solo se acepta constancia de situación fiscal SAT.",
            )
        return {"valido": False, "revision_manual": True, "mensaje": "El documento no se pudo confirmar automáticamente como constancia fiscal SAT."}

    # Vigencia: máximo 2 meses desde "Lugar y Fecha de Emisión"
    meses = datos.get("meses_antiguedad")
    if meses is not None and meses > 2.0:
        return _respuesta_rechazo(
            "constancia_fiscal_vencida",
            "La constancia no puede tener más de 2 meses de antigüedad. Descarga una nueva constancia en el portal del SAT.",
            fecha_emision=datos.get("fecha_emision"),
            meses_antiguedad=meses,
            vigencia_ok=False,
            actividad_asalariado=datos.get("actividad_economica_asalariado"),
            regimen_sueldos_salarios=datos.get("regimen_sueldos_salarios"),
        )
    if meses is not None and meses > 2.0:
        return {
            "valido": False,
            "mensaje": "La constancia no puede tener más de 2 meses de antigüedad. Descarga una nueva constancia en el portal del SAT.",
            "fecha_emision": datos.get("fecha_emision"),
            "meses_antiguedad": meses,
            "vigencia_ok": False,
            "actividad_asalariado": datos.get("actividad_economica_asalariado"),
            "regimen_sueldos_salarios": datos.get("regimen_sueldos_salarios"),
        }

    # Actividad económica debe ser "Asalariado"
    if not datos.get("actividad_economica_asalariado") and not datos.get("regimen_sueldos_salarios"):
        return {
            "valido": False,
            "revision_manual": True,
            "mensaje": "No se pudo confirmar automáticamente la actividad Asalariado. Revisar manualmente.",
            "fecha_emision": datos.get("fecha_emision"),
            "meses_antiguedad": meses,
            "vigencia_ok": meses is None or meses <= 2.0,
            "actividad_asalariado": False,
            "regimen_sueldos_salarios": datos.get("regimen_sueldos_salarios"),
        }

    # Régimen debe incluir "Régimen de Sueldos y Salarios e Ingresos Asimilados a Salarios"
    if not datos.get("regimen_sueldos_salarios"):
        return {
            "valido": False,
            "revision_manual": True,
            "mensaje": "No se pudo confirmar automáticamente el régimen de Sueldos y Salarios. Revisar manualmente.",
            "fecha_emision": datos.get("fecha_emision"),
            "meses_antiguedad": meses,
            "vigencia_ok": meses is None or meses <= 2.0,
            "actividad_asalariado": bool(datos.get("actividad_economica_asalariado")),
            "regimen_sueldos_salarios": False,
        }

    if not (datos.get("rfc") or (datos.get("curp") and validar_curp(datos["curp"])[0])):
        return {"valido": False, "revision_manual": True, "mensaje": "No se pudo confirmar automáticamente la constancia fiscal SAT. Revisar manualmente."}

    return {
        "valido": True,
        "mensaje": "Constancia fiscal verificada.",
        "rfc": datos.get("rfc"),
        "curp": datos.get("curp"),
        "fecha_emision": datos.get("fecha_emision"),
        "meses_antiguedad": meses,
        "vigencia_ok": True,
        "actividad_asalariado": bool(datos.get("actividad_economica_asalariado")),
        "regimen_sueldos_salarios": True,
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }


@router.post(
    "/verificar-estado-cuenta",
    summary="Verificar PDF de estado de cuenta (banco físico + hoja datos titular)",
    description="Sube un PDF de estado de cuenta. Solo se aceptan bancos físicos de México (BBVA, Banorte, Santander, etc.). Se rechazan neobancos (Nu, Ualá, Klar, etc.). El PDF debe incluir la hoja con datos del titular (nombre, dirección, CLABE, cuenta).",
    tags=["Utilidades"]
)
async def verificar___SPARTA_SECRET_REDACTED__(
    documento: UploadFile = File(..., description="PDF de estado de cuenta"),
    api_key: str = Depends(verificar_api_key)
):
    inicio = time.time()
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de estado de cuenta")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    try:
        rechazo = await asyncio.wait_for(
            asyncio.to_thread(_detectar_documento_equivocado___SPARTA_SECRET_REDACTED___pdf, file_bytes, inicio),
            timeout=3,
        )
        if rechazo:
            return rechazo
    except asyncio.TimeoutError:
        logger.debug("verificar___SPARTA_SECRET_REDACTED__: deteccion de documento equivocado agoto tiempo")
    except Exception as e:
        logger.debug(f"verificar___SPARTA_SECRET_REDACTED__: deteccion de documento equivocado fallo: {e}")
    from app.services.__SPARTA_SECRET_REDACTED___analyzer import validar___SPARTA_SECRET_REDACTED___pdf
    try:
        resultado = await asyncio.wait_for(
            asyncio.to_thread(validar___SPARTA_SECRET_REDACTED___pdf, file_bytes),
            timeout=8,
        )
        return resultado
    except asyncio.TimeoutError:
        return {
            "valido": False,
            "revision_manual": True,
            "timeout": True,
            "mensaje": "No se pudo leer el estado de cuenta a tiempo. Revisar manualmente.",
            "banco_detectado": None,
            "nombre_propietario": None,
            "clabe": None,
            "es_banco_fisico": False,
            "tiene_datos_titular": False,
        }


@router.post(
    "/fad/informacion-ingresos",
    summary="Extraer sección Información de Ingresos de FAD_DOC",
    description="Sube el PDF del contrato firmado (FAD_DOC). Extrae la sección 'Información de Ingresos' al final del documento para usar en Sabueso (donde trabaja).",
    tags=["Sabueso"]
)
async def fad_informacion_ingresos(
    documento: UploadFile = File(..., description="PDF FAD_DOC (contrato firmado)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF (FAD_DOC)")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    datos = extraer_informacion_ingresos_fad(file_bytes)
    return {
        "success": True,
        "encontrado": datos.get("encontrado", False),
        "texto_seccion": datos.get("texto_seccion") or "",
        "empresa": datos.get("empresa"),
        "empleado": datos.get("empleado"),
        "ingreso_mensual_neto": datos.get("ingreso_mensual_neto"),
        "telefono": datos.get("telefono"),
    }


@router.post(
    "/factura/datos-moto",
    summary="Extraer VIN, No. motor y color desde FACTURA",
    description="Sube FACTURA (PDF/JPG/PNG). Extrae No. de Serie (VIN), No. de Motor y Color para autollenado en Mis adjudicaciones.",
    tags=["Motos adjudicadas"]
)
async def factura_datos_moto(
    documento: UploadFile = File(..., description="Documento FACTURA (PDF/JPG/PNG)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename:
        raise HTTPException(status_code=400, detail="Se requiere un documento de FACTURA")

    ext = documento.filename.lower().rsplit(".", 1)[-1] if "." in documento.filename else ""
    if ext not in {"pdf", "jpg", "jpeg", "png"}:
        raise HTTPException(status_code=400, detail="Formato no soportado. Use PDF, JPG o PNG.")

    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")

    datos = extraer_datos_motocicleta_factura(file_bytes, documento.filename)
    return {
        "success": True,
        "encontrado": datos.get("encontrado", False),
        "vin": datos.get("vin"),
        "no_motor": datos.get("no_motor"),
        "color": datos.get("color"),
        "texto_fuente": datos.get("texto_fuente", ""),
    }


@router.post(
    "/verificar-curp-documento",
    summary="Extraer y validar CURP desde PDF (constancia RENAPO)",
    description="Sube un PDF de constancia de CURP (ej. CURP.pdf). Se extrae el CURP del texto y se valida. No acepta constancia fiscal ni otros documentos.",
    tags=["Utilidades"]
)
async def verificar_curp_documento(
    documento: UploadFile = File(..., description="PDF constancia de CURP (RENAPO, no constancia fiscal)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de constancia de CURP")
    nombre_archivo = (documento.filename or "").upper()
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    inicio = time.time()
    if re.search(r"SOLICITUD|CURR.?CUL|CURRICULUM|CV[\s_\-()]|IDENTIFICACI[OÓ]N|IDENTIFICACION|INDENTIFICACION|IDENTIF|INE|PASAPORTE|RESIDENCIA", nombre_archivo):
        return _respuesta_rechazo(
            "documento_no_curp",
            "El documento no corresponde a constancia CURP del RENAPO. En este campo solo se acepta CURP.",
            curp_extraido=None,
            es_reciente=None,
            meses_antiguedad=None,
            fecha_emision=None,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    info_inicial = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
    texto_inicial = _normalizar_texto_precheck(info_inicial.get("texto") or "")
    if texto_inicial:
        rechazo_inicial = _rechazo_curp_por_texto_equivocado(
            texto_inicial,
            inicio,
            "documento_equivocado_texto_embebido",
        )
        if rechazo_inicial:
            return rechazo_inicial
        if not re.search(r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N", texto_inicial):
            return _respuesta_rechazo(
                "documento_no_curp",
                "El documento no corresponde a constancia CURP del RENAPO. En este campo solo se acepta CURP.",
                curp_extraido=None,
                es_reciente=None,
                meses_antiguedad=None,
                fecha_emision=None,
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
    elif not re.search(r"\bCURP\b", nombre_archivo):
        return {
            "curp_extraido": None,
            "valido": False,
            "revision_manual": True,
            "mensaje": "No se pudo confirmar automáticamente que el PDF sea una constancia CURP del RENAPO. Revisar manualmente.",
            "es_reciente": None,
            "meses_antiguedad": None,
            "fecha_emision": None,
            "parece_curp": False,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    try:
        rechazo_equivocado = await asyncio.wait_for(
            asyncio.to_thread(_detectar_documento_equivocado_curp_pdf, file_bytes, inicio),
            timeout=5,
        )
        if rechazo_equivocado:
            return rechazo_equivocado
    except asyncio.TimeoutError:
        logger.warning("verificar_curp_documento: deteccion de documento equivocado agoto tiempo")
    if es_tarjeta_nss(file_bytes) or es_documento_nss(file_bytes):
        return _respuesta_rechazo(
            "nss_en_curp",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta constancia CURP del RENAPO.",
            curp_extraido=None,
            es_reciente=None,
            meses_antiguedad=None,
            fecha_emision=None,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if es_documento_constancia_fiscal(file_bytes):
        return _respuesta_rechazo(
            "constancia_fiscal_en_curp",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta constancia CURP del RENAPO.",
            curp_extraido=None,
            es_reciente=None,
            meses_antiguedad=None,
            fecha_emision=None,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if es_documento_acta_nacimiento(file_bytes):
        return _respuesta_rechazo(
            "acta_en_curp",
            "El documento es un acta de nacimiento. En este campo solo se acepta constancia CURP del RENAPO.",
            curp_extraido=None,
            es_reciente=None,
            meses_antiguedad=None,
            fecha_emision=None,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    parece_curp = es_documento_curp(file_bytes)
    if not parece_curp:
        info_texto = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
        texto_curp = _normalizar_texto_precheck(info_texto.get("texto") or "")
        if not re.search(r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N", texto_curp):
            return _respuesta_rechazo(
                "documento_no_curp",
                "El documento no corresponde a constancia CURP del RENAPO. En este campo solo se acepta CURP.",
                curp_extraido=None,
                es_reciente=None,
                meses_antiguedad=None,
                fecha_emision=None,
                parece_curp=parece_curp,
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
    try:
        datos = await asyncio.wait_for(
            asyncio.to_thread(extraer_datos_curp_pdf, file_bytes),
            timeout=25,
        )
    except asyncio.TimeoutError:
        return {
            "curp_extraido": None,
            "valido": False,
            "mensaje": "No se pudo leer el CURP a tiempo.",
            "timeout": True,
            "revision_manual": True,
            "es_reciente": None,
            "meses_antiguedad": None,
            "fecha_emision": None,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    curp_extraido = datos.get("curp") if isinstance(datos, dict) else None
    if not curp_extraido:
        curp_extraido = extraer_curp_de_pdf(file_bytes)
    if curp_extraido is None:
        return {
            "curp_extraido": None,
            "valido": False,
            "mensaje": "No se pudo leer automáticamente un CURP válido en el documento.",
            "revision_manual": True,
            "es_reciente": None,
            "meses_antiguedad": None,
            "fecha_emision": None,
            "parece_curp": parece_curp,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    valido, mensaje = validar_curp(curp_extraido)
    nombre = datos.get("nombre") if isinstance(datos, dict) else None
    if not valido:
        return {
            "curp_extraido": curp_extraido,
            "valido": False,
            "mensaje": mensaje,
            "nombre": nombre,
            "revision_manual": True,
            "es_reciente": datos.get("es_reciente"),
            "meses_antiguedad": datos.get("meses_antiguedad"),
            "fecha_emision": datos.get("fecha_emision"),
            "parece_curp": parece_curp,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    if not parece_curp:
        return _respuesta_rechazo(
            "documento_no_curp",
            "Se encontro una CURP, pero el PDF no corresponde a una constancia CURP del RENAPO.",
            curp_extraido=curp_extraido,
            nombre=nombre,
            es_reciente=datos.get("es_reciente"),
            meses_antiguedad=datos.get("meses_antiguedad"),
            fecha_emision=datos.get("fecha_emision"),
            parece_curp=parece_curp,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    return {
        "curp_extraido": curp_extraido,
        "valido": True,
        "mensaje": mensaje,
        "nombre": nombre,
        "es_reciente": datos.get("es_reciente"),
        "meses_antiguedad": datos.get("meses_antiguedad"),
        "fecha_emision": datos.get("fecha_emision"),
        "parece_curp": parece_curp,
        "revision_manual": False,
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }


@router.post(
    "/precheck-identificacion-pdf",
    summary="Precheck rápido de identificación oficial (PDF)",
    description="""
    Revisión rápida para la pantalla de subida de documentos.
    Solo valida que el PDF parezca una identificación oficial (INE o residencia),
    sin extraer datos finales ni ejecutar comparaciones profundas.
    """,
    tags=["Utilidades"]
)
async def precheck_identificacion_pdf(
    documento: UploadFile = File(..., description="PDF de identificación oficial"),
    api_key: str = Depends(verificar_api_key)
):
    inicio = time.time()
    nombre_archivo = (documento.filename or "").upper()
    if re.search(r"SOLICITUD|CURR.?CUL|CURRICULUM|CV[\s_\-()]", nombre_archivo):
        return _respuesta_rechazo(
            "solicitud_en_identificacion",
            "El documento corresponde a solicitud/CV. En este campo solo se acepta identificacion oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado_nombre_archivo",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de identificación oficial")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    if not file_bytes.startswith(b"%PDF-"):
        return {
            "valido": False,
            "mensaje": "El archivo no parece ser un PDF válido.",
            "paginas": 0,
            "indicadores": {},
            "modo": "firma_pdf",
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }

    try:
        rechazo_equivocado = await asyncio.wait_for(
            asyncio.to_thread(_detectar_documento_equivocado_identificacion_pdf, file_bytes, inicio),
            timeout=5,
        )
        if rechazo_equivocado:
            return rechazo_equivocado
    except asyncio.TimeoutError:
        logger.warning("precheck_identificacion_pdf: deteccion de documento equivocado agoto tiempo")

    if False and (es_tarjeta_nss(file_bytes) or es_documento_nss(file_bytes)):
        return _respuesta_rechazo(
            "nss_en_identificacion",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta identificación oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if False and es_documento_curp(file_bytes):
        return _respuesta_rechazo(
            "curp_en_identificacion",
            "El documento es una constancia CURP. En este campo solo se acepta identificación oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if False and es_documento_constancia_fiscal(file_bytes):
        return _respuesta_rechazo(
            "constancia_fiscal_en_identificacion",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta identificación oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if False and es_documento_acta_nacimiento(file_bytes):
        return _respuesta_rechazo(
            "acta_en_identificacion",
            "El documento es un acta de nacimiento. En este campo solo se acepta identificación oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )

    try:
        extraido = await asyncio.wait_for(
            asyncio.to_thread(_extraer_texto_pdf_rapido, file_bytes, 2),
            timeout=10,
        )
    except asyncio.TimeoutError:
        return {
            "valido": False,
            "revision_manual": True,
            "mensaje": "No se pudo revisar la identificación a tiempo. Revisar manualmente.",
            "paginas": 0,
            "indicadores": {},
            "modo": "timeout",
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    texto = extraido.get("texto") or ""
    indicadores = _indicadores_identificacion(texto)
    valido = _parece_identificacion_oficial(texto)
    paginas = int(extraido.get("paginas") or 0)
    texto_upper = texto.upper()
    if re.search(r"(?:^|\s)(?:ID\s*<?\s*MEX|IDMEX|I\s*<\s*MEX|I<MEX)|IDMEX", texto_upper):
        indicadores["ine"] = True
        indicadores["pasaporte"] = False
        valido = True

    if texto.strip() and not valido:
        if re.search(r"ASIGNACI[OÓ]N\s+O\s+LOCALIZACI[OÓ]N|N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b", texto_upper):
            return _respuesta_rechazo(
                "nss_en_identificacion",
                "El documento corresponde a NSS del IMSS. En este campo solo se acepta identificación oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
        if re.search(r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N", texto_upper):
            return _respuesta_rechazo(
                "curp_en_identificacion",
                "El documento es una constancia CURP. En este campo solo se acepta identificación oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
        if re.search(r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|\bSAT\b", texto_upper):
            return _respuesta_rechazo(
                "constancia_fiscal_en_identificacion",
                "El documento es una constancia fiscal SAT. En este campo solo se acepta identificación oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
        if re.search(r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICADO\s+DE\s+NACIMIENTO", texto_upper):
            return _respuesta_rechazo(
                "acta_en_identificacion",
                "El documento es un acta de nacimiento. En este campo solo se acepta identificación oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )

        if re.search(r"SOLICITUD\s+(INTERNA|DE\s+EMPLEO|EMPLEO|DE\s+TRABAJO|TRABAJO)|CURR.?CULUM|CURRICULUM\s+VITAE|EXPERIENCIA\s+LABORAL", texto_upper):
            return _respuesta_rechazo(
                "solicitud_en_identificacion",
                "El documento corresponde a solicitud/CV. En este campo solo se acepta identificacion oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )

    if paginas <= 0:
        mensaje = "No se pudo abrir el PDF para revisión rápida."
        valido = False
    elif valido:
        mensaje = "El PDF parece corresponder a una identificación oficial."
    elif texto.strip():
        mensaje = "El PDF no parece corresponder a una identificación oficial. Sube INE o residencia oficial."
    else:
        mensaje = "No se pudo leer suficiente texto del PDF. Sube una identificación oficial clara."

    return {
        "valido": valido,
        "revision_manual": not valido,
        "mensaje": mensaje,
        "paginas": paginas,
        "indicadores": indicadores,
        "modo": extraido.get("modo"),
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }


@router.post(
    "/verificar-calidad-identificacion-pdf",
    summary="Revisión de calidad de identificación oficial (PDF)",
    description="""
    Sube un PDF de identificación oficial (frente y reverso en 1 o 2 páginas).
    El documento **siempre se acepta**; el sistema devuelve **notas de revisión** (ej. exceso de brillo, borroso)
    para que Capital Humano revise manualmente en el modal de documentación.
    """,
    tags=["Utilidades"]
)
async def verificar_calidad_identificacion_pdf(
    documento: UploadFile = File(..., description="PDF de identificación oficial (frente y opcionalmente reverso)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de identificación oficial")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")

    inicio = time.time()
    imagenes = pdf_paginas_a_png_bytes(file_bytes, dpi=115, max_paginas=2)
    if not imagenes:
        return {
            "aceptado": True,
            "notas": ["No se pudo procesar el PDF para revisión de calidad. Revisar identificación manualmente."],
            "detalle_frente": None,
            "detalle_reverso": None,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }

    service = VerificacionService()
    notas: List[str] = []
    detalle_frente: Optional[Dict[str, Any]] = None
    detalle_reverso: Optional[Dict[str, Any]] = None

    def _texto_calidad(calidad: str) -> str:
        """Convierte calidad_foto a texto legible para las notas."""
        if not calidad or calidad == "ok":
            return ""
        if calidad == "revisar_brillo":
            return "posible exceso de brillo o reflejo en la imagen."
        if calidad == "revisar_borroso":
            return "posible imagen borrosa o desenfocada."
        if calidad == "revisar_brillo_y_borroso":
            return "posible exceso de brillo y/o imagen borrosa."
        return calidad.replace("_", " ") + "."

    try:
        # Página 1 = frente
        forense_f = await asyncio.to_thread(service.forense_analyzer.analyze, imagenes[0])
        detalle_frente = {
            "calidad_foto": forense_f.calidad_foto,
            "brillo_excesivo": forense_f.brillo_excesivo,
            "porcentaje_sobreexpuesto": forense_f.porcentaje_sobreexpuesto,
            "borroso": forense_f.borroso,
            "alertas": forense_f.alertas or [],
            "nombre_ocr": None,
            "curp_ocr": None,
        }
        # Nota por calidad (texto amigable, una sola vez)
        texto_calidad = _texto_calidad(forense_f.calidad_foto)
        if texto_calidad:
            notas.append("Revisar identificación oficial: sistema detectó " + texto_calidad)
        # Alertas forense (evitar duplicar si ya se dijo algo similar)
        for a in (forense_f.alertas or []):
            if a and a.strip() and a not in notas:
                notas.append("Revisar identificación oficial: " + a.strip())

        # Página 2 = reverso (si existe)
        if len(imagenes) > 1:
            forense_r = await asyncio.to_thread(service.forense_analyzer.analyze, imagenes[1])
            detalle_reverso = {
                "calidad_foto": forense_r.calidad_foto,
                "brillo_excesivo": forense_r.brillo_excesivo,
                "borroso": forense_r.borroso,
                "alertas": forense_r.alertas or [],
                "mrz_nombre": None,
                "mrz_fecha_nac": None,
            }
            texto_rev = _texto_calidad(forense_r.calidad_foto)
            if texto_rev and not any("reverso" in n.lower() for n in notas):
                notas.append("Reverso: sistema detectó " + texto_rev)
            for a in (forense_r.alertas or []):
                if a and a.strip() and not any(a.strip() in n for n in notas):
                    notas.append("Reverso: " + a.strip())

        if not notas:
            notas.append("Revisión automática sin observaciones. Revisar identificación manualmente si lo considera necesario.")
    except Exception as e:
        logger.exception(f"verificar_calidad_identificacion_pdf: {e}")
        notas = ["Error al procesar la revisión de calidad. Revisar identificación manualmente."]
        detalle_frente = None
        detalle_reverso = None

    return {
        "aceptado": True,
        "notas": notas,
        "detalle_frente": detalle_frente,
        "detalle_reverso": detalle_reverso,
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }


@router.post(
    "/validar-expediente",
    summary="Validación cruzada de expediente completo",
    description="""
    Identificación oficial: se puede enviar de dos formas:
    - **Opción A:** Un solo PDF (identificacion_pdf) con frente y reverso en 1 o 2 páginas. Página 1 = frente, página 2 = reverso (si existe).
    - **Opción B:** Dos imágenes por separado (frente y reverso).

    El resto del expediente: CURP.pdf, NSS.pdf, constancia_fiscal.pdf, acta_nacimiento.pdf (opcionales).

    **Reglas:**
    - Si la foto de la identificación tiene brillo excesivo o está borrosa → RECHAZADA.
    - CURP y nombre se comparan entre: ID, CURP.pdf, constancia fiscal, NSS.
    - Fecha de nacimiento se compara entre: CURP (decodificado), MRZ reverso, acta.
    - La constancia CURP debe tener máximo 3 meses de descargada.
    """,
    tags=["Verificación"]
)
async def validar_expediente(
    frente: Optional[UploadFile] = File(None, description="Imagen del frente (opcional si se envía identificacion_pdf)"),
    reverso: Optional[UploadFile] = File(None, description="Imagen del reverso (opcional si se envía identificacion_pdf)"),
    identificacion_pdf: Optional[UploadFile] = File(None, description="PDF de identificación oficial (frente y reverso en 1 o 2 páginas)"),
    documento_curp: Optional[UploadFile] = File(None, description="PDF constancia CURP"),
    documento_nss: Optional[UploadFile] = File(None, description="PDF constancia NSS"),
    constancia_fiscal: Optional[UploadFile] = File(None, description="PDF constancia de situación fiscal"),
    acta_nacimiento: Optional[UploadFile] = File(None, description="PDF acta de nacimiento"),
    nombre_candidato_registro: Optional[str] = Form(None, description="Nombre registrado del candidato en Sparta Ledger"),
    tipo_documento_query: Optional[TipoDocumento] = Query(
        None,
        alias="tipo_documento",
        description="Tipo de identificación (query). Si no se envía, se usa Form o el valor por defecto.",
    ),
    tipo_documento_form: Optional[TipoDocumento] = Form(
        None,
        alias="tipo_documento",
        description="Tipo de identificación (campo form multipart). PHP lo envía con este nombre; coincide con ?tipo_documento= en query.",
    ),
    api_key: str = Depends(verificar_api_key)
):
    # Query tiene prioridad sobre Form; si ambos faltan, mantener default histórico (residencia).
    tipo_documento = (
        tipo_documento_query
        if tipo_documento_query is not None
        else (tipo_documento_form if tipo_documento_form is not None else TipoDocumento.RESIDENCIA_TEMPORAL)
    )

    # Resolver frente y reverso: desde PDF (páginas 1 y 2) o desde archivos separados
    frente_bytes: Optional[bytes] = None
    reverso_bytes: Optional[bytes] = None
    tiempos_fase: Dict[str, int] = {}

    if identificacion_pdf and identificacion_pdf.filename and identificacion_pdf.filename.lower().endswith(".pdf"):
        pdf_bytes = await identificacion_pdf.read()
        if pdf_bytes and len(pdf_bytes) >= 100:
            t_pdf = time.time()
            imagenes = await asyncio.to_thread(pdf_paginas_a_png_bytes, pdf_bytes, 150, 2)
            tiempos_fase["pdf_identificacion_a_imagenes_ms"] = int((time.time() - t_pdf) * 1000)
            if imagenes:
                frente_bytes = imagenes[0]
                reverso_bytes = imagenes[1] if len(imagenes) > 1 else imagenes[0]

    if not frente_bytes or not reverso_bytes:
        # Intentar desde frente/reverso por separado
        if frente and reverso:
            frente_bytes = await frente.read()
            reverso_bytes = await reverso.read()
        if not frente_bytes or not reverso_bytes:
            raise HTTPException(
                status_code=400,
                detail="Se requiere identificacion_pdf (PDF con frente y reverso) o bien frente y reverso como imágenes."
            )

    async def _leer_pdf(upload: Optional[UploadFile]) -> Optional[bytes]:
        if upload and upload.filename and upload.filename.lower().endswith(".pdf"):
            data = await upload.read()
            return data if data else None
        return None

    curp_pdf_bytes = await _leer_pdf(documento_curp)
    nss_pdf_bytes = await _leer_pdf(documento_nss)
    fiscal_pdf_bytes = await _leer_pdf(constancia_fiscal)
    acta_pdf_bytes = await _leer_pdf(acta_nacimiento)

    try:
        inicio = time.time()
        service = VerificacionService()

        async def _analizar_identificacion_para_cruce(img_bytes: bytes) -> Dict[str, Any]:
            """Analisis acotado para validacion cruzada: calidad + OCR de campos."""
            try:
                forense, ocr = await asyncio.wait_for(
                    asyncio.gather(
                        asyncio.to_thread(service.forense_analyzer.analyze, img_bytes),
                        asyncio.to_thread(service.ocr_analyzer.analyze, img_bytes, tipo_documento),
                    ),
                    timeout=45,
                )
                score = round(((forense.score or 0.0) * 0.4 + (ocr.score or 0.0) * 0.6) * 100)
                return {"score": score, "forense": forense, "ocr": ocr, "timeout": False}
            except asyncio.TimeoutError:
                return {
                    "score": 0,
                    "forense": CheckForense(
                        ok=False,
                        ela_score=0.0,
                        calidad_foto="revision_manual",
                        alertas=["La identificacion no se pudo analizar en el tiempo esperado."],
                        score=0.3,
                    ),
                    "ocr": CheckOCR(
                        ok=False,
                        alertas=["OCR de identificacion agoto el tiempo de espera."],
                        score=0.0,
                    ),
                    "timeout": True,
                }

        t_id = time.time()
        if frente_bytes == reverso_bytes:
            res_frente = await _analizar_identificacion_para_cruce(frente_bytes)
            res_reverso = res_frente
        else:
            res_frente, res_reverso = await asyncio.gather(
                _analizar_identificacion_para_cruce(frente_bytes),
                _analizar_identificacion_para_cruce(reverso_bytes),
            )
        tiempos_fase["identificacion_ocr_forense_ms"] = int((time.time() - t_id) * 1000)

        ocr_f = res_frente["ocr"]
        ocr_r = res_reverso["ocr"]
        calidad = res_frente["forense"].calidad_foto

        # Estos PDFs pueden disparar OCR. Ejecutarlos en paralelo evita sumar sus tiempos uno tras otro.
        t_pdfs = time.time()
        datos_curp, datos_nss, datos_fiscal, datos_acta = await asyncio.gather(
            _ejecutar_pdf_timeout("CURP", extraer_datos_curp_pdf, curp_pdf_bytes, timeout=5) if curp_pdf_bytes else asyncio.sleep(0, result=None),
            _ejecutar_pdf_timeout("NSS", extraer_datos_nss_pdf, nss_pdf_bytes, timeout=5) if nss_pdf_bytes else asyncio.sleep(0, result=None),
            _ejecutar_pdf_timeout(
                "Constancia fiscal",
                extraer_datos_constancia_fiscal,
                fiscal_pdf_bytes,
                timeout=10,
                fallback={"parece_constancia_fiscal": None},
            ) if fiscal_pdf_bytes else asyncio.sleep(0, result=None),
            _ejecutar_pdf_timeout(
                "Acta de nacimiento",
                lambda data: extraer_datos_acta_nacimiento(data, modo_rapido=True),
                acta_pdf_bytes,
                timeout=8,
                fallback={"parece_acta": None},
            ) if acta_pdf_bytes else asyncio.sleep(0, result=None),
        )
        tiempos_fase["pdfs_datos_ms"] = int((time.time() - t_pdfs) * 1000)

        t_cruce = time.time()
        resultado = validacion_cruzada(
            id_frente_curp=ocr_f.curp.get("valor") if ocr_f.curp else None,
            id_frente_nombre=ocr_f.nombre_ocr,
            id_frente_fecha_nac_curp=ocr_f.fecha_nacimiento_curp,
            id_reverso_mrz_nombre=ocr_r.mrz_nombre_completo,
            id_reverso_mrz_fecha_nac=ocr_r.mrz_fecha_nacimiento,
            calidad_foto=calidad,
            datos_curp_pdf=datos_curp,
            datos_nss=datos_nss,
            datos_fiscal=datos_fiscal,
            datos_acta=datos_acta,
            nombre_candidato_registro=nombre_candidato_registro,
        )
        tiempos_fase["cruce_reglas_ms"] = int((time.time() - t_cruce) * 1000)

        tiempo_ms = int((time.time() - inicio) * 1000)

        # Nombre: preferir MRZ (reverso) por ser estándar; si no, frente OCR
        nombre_ocr = (ocr_r.mrz_nombre_completo if ocr_r and ocr_r.mrz_nombre_completo else None) or (ocr_f.nombre_ocr if ocr_f else None)
        # Año nacimiento: de CURP (frente) o MRZ (reverso)
        fecha_nac = (ocr_f.fecha_nacimiento_curp if ocr_f else None) or (ocr_r.mrz_fecha_nacimiento if ocr_r else None)
        anio_nacimiento = None
        if fecha_nac:
            if isinstance(fecha_nac, str):
                m = re.search(r"(19|20)\d{2}", fecha_nac)
                if m:
                    anio_nacimiento = m.group(0)
            else:
                anio_nacimiento = str(fecha_nac)[:4] if len(str(fecha_nac)) >= 4 else None
        tipo_doc_val = tipo_documento.value if hasattr(tipo_documento, "value") else str(tipo_documento)

        return {
            **resultado,
            "identificacion_frente_score": res_frente["score"],
            "identificacion_reverso_score": res_reverso["score"],
            "identificacion_timeout": bool(res_frente.get("timeout") or res_reverso.get("timeout")),
            "nombre_ocr": nombre_ocr,
            "anio_nacimiento": anio_nacimiento,
            "tipo_documento": tipo_doc_val,
            "documentos_procesados": {
                "identificacion_frente": True,
                "identificacion_reverso": True,
                "curp_pdf": datos_curp is not None,
                "nss_pdf": datos_nss is not None,
                "constancia_fiscal": datos_fiscal is not None,
                "acta_nacimiento": datos_acta is not None,
            },
            "datos_extraidos": {
                "curp_pdf": datos_curp,
                "nss_pdf": datos_nss,
                "constancia_fiscal": datos_fiscal,
                "acta_nacimiento": datos_acta,
            },
            "tiempo_proceso_ms": tiempo_ms,
            "tiempos_fase_ms": tiempos_fase,
        }
    except Exception as e:
        logger.exception(f"Error en validación de expediente: {e}")
        raise HTTPException(status_code=500, detail=f"Error en validación: {str(e)}")


@router.get(
    "/tipos-documento",
    summary="Tipos de documento soportados",
    tags=["Utilidades"]
)
async def listar_tipos():
    """Lista los tipos de documentos que el sistema puede verificar."""
    return {
        "tipos_soportados": [
            {"codigo": "INE_NUEVA", "nombre": "INE / Credencial para Votar (2014 en adelante)", "emisor": "Instituto Nacional Electoral"},
            {"codigo": "INE_ANTERIOR", "nombre": "IFE / Credencial para Votar (anterior a 2014)", "emisor": "Instituto Federal Electoral"},
            {"codigo": "RESIDENCIA_TEMPORAL", "nombre": "Tarjeta de Residencia Temporal", "emisor": "Instituto Nacional de Migración"},
            {"codigo": "RESIDENCIA_TEMPORAL_ACUMULATIVA", "nombre": "Tarjeta de Residencia Temporal Acumulativa", "emisor": "Instituto Nacional de Migración"},
            {"codigo": "RESIDENCIA_PERMANENTE", "nombre": "Tarjeta de Residencia Permanente", "emisor": "Instituto Nacional de Migración"},
        ]
    }


@router.get("/health", tags=["Sistema"])
async def health_check():
    """Verifica que el servicio esté funcionando."""
    return {"status": "ok", "version": settings.app_version}
