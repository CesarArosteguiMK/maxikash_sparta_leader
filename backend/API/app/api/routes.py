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
    VerificacionResponse, VerificacionCalidadResponse, TipoDocumento, ErrorResponse, ComprobanteResponse
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
    es_documento_nss, es_documento_curp, es_documento_constancia_fiscal, es_documento_acta_nacimiento,
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
    tiene_ine = bool(re.search(r"INSTITUTO\s+NACIONAL\s+ELECTORAL|CREDENCIAL\s+PARA\s+VOT", t))
    tiene_elector = bool(re.search(r"CLAVE\s*DE\s*ELECTOR|CLAVEDEELECTOR|CLAVE.{0,4}ELECTOR|SECCION\s*\d|ELECTORAL|VOTAR", t))
    tiene_curp = bool(re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d", t))
    tiene_inm = bool(re.search(r"INSTITUTO\s+NACIONAL\s+DE\s+MIGRACION|MIGRACION|INM|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|RESIDENTE\s+(TEMPORAL|PERMANENTE)|NUE\s*[.:]?\s*\d", t))
    return {
        "mrz": tiene_mrz,
        "ine": tiene_ine,
        "elector": tiene_elector,
        "curp": tiene_curp,
        "inm_residencia": tiene_inm,
    }


def _parece_identificacion_oficial(texto: str) -> bool:
    indicadores = _indicadores_identificacion(texto)
    if indicadores["mrz"] or indicadores["ine"] or indicadores["inm_residencia"]:
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
        for dpi, max_ancho in ((95, 1000), (150, 1400)):
            texto = ""
            for i, page in enumerate(doc):
                if i >= max_paginas:
                    break
                pix = page.get_pixmap(dpi=dpi)
                img_bytes = pix.tobytes("png")
                texto += service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=max_ancho) + "\n"
            if _parece_identificacion_oficial(texto):
                doc.close()
                return {"texto": texto, "paginas": paginas, "modo": f"ocr_rapido_{dpi}dpi"}

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
        doc.close()
        return {"texto": texto, "paginas": paginas, "modo": "ocr_rapido"}
    except Exception as e:
        logger.warning(f"precheck_identificacion_pdf: error leyendo PDF: {e}")
        return {"texto": texto, "paginas": paginas, "modo": "error"}


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

    try:
        inicio = time.time()
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

        if check.es_reciente is False:
            resultado = "RECHAZADO"
            recomendacion = prefijo + "tiene mas de 3 meses de antigüedad. Se requiere máximo 3 meses."
        elif check.es_reciente is None and not check.fecha_documento:
            resultado = "RECHAZADO"
            if any("no se pudo extraer texto" in (a or "").lower() for a in (check.alertas or [])):
                recomendacion = prefijo + "no se pudo extraer texto legible del PDF para validar la fecha. Sube un PDF digital o una imagen más clara."
            else:
                recomendacion = prefijo + "no se pudo verificar la fecha del documento."
        elif score >= 75 and check.es_reciente is True:
            resultado = "APROBADO"
            recomendacion = f"Comprobante de {tipo_label} válido y reciente. Puede procesarse." if tipo_label else "Comprobante válido y reciente. Puede procesarse."
        elif score >= 50:
            resultado = "REVISION_MANUAL"
            recomendacion = "Comprobante requiere revisión manual."

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
    description="Sube el PDF de vigencia de derechos del IMSS (imss.gob.mx), constancia de vigencia FF-IMSS-012, o constancia de semanas cotizadas. No se acepta la tarjeta NSS (imprimir y recortar). Retorna nss_extraido, valido y mensaje.",
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
    if not es_documento_nss(file_bytes):
        return {"nss_extraido": None, "valido": False, "mensaje": "El documento no es uno de los formatos aceptados (vigencia de derechos, constancia de vigencia o constancia de semanas cotizadas del IMSS). No se acepta la tarjeta NSS. Suba el PDF desde imss.gob.mx."}
    nss_extraido = extraer_nss_de_pdf(file_bytes)
    if nss_extraido is None:
        return {"nss_extraido": None, "valido": False, "mensaje": "No se encontró un NSS de 11 dígitos en el documento. Sube la constancia de NSS del IMSS."}
    valido, mensaje = validar_nss(nss_extraido)
    datos_nss = extraer_datos_nss_pdf(file_bytes)
    nombre = datos_nss.get("nombre") if isinstance(datos_nss, dict) else None
    return {"nss_extraido": nss_extraido, "valido": valido, "mensaje": mensaje, "nombre": nombre}


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
    if es_documento_nss(file_bytes):
        return {"valido": False, "mensaje": "El documento es una constancia de NSS del IMSS. En este campo solo se acepta el acta de nacimiento certificada en PDF."}
    datos = extraer_datos_acta_nacimiento(file_bytes)
    # Muy flexible: si el extractor obtuvo fecha o nombre (incluso con baja confianza, ej. escaneos/manuscritos), aceptar
    if datos.get("fecha_nacimiento") or datos.get("nombre"):
        return {"valido": True, "mensaje": "Acta de nacimiento verificada.", "nombre": datos.get("nombre"), "fecha_nacimiento": datos.get("fecha_nacimiento")}
    # Si hay texto extraído y coincide con acta por encabezados, aceptar
    if datos.get("texto_extraido") and es_documento_acta_nacimiento(file_bytes):
        return {"valido": True, "mensaje": "Acta de nacimiento verificada.", "nombre": datos.get("nombre"), "fecha_nacimiento": datos.get("fecha_nacimiento")}
    # Documento con texto que parece acta pero no se pudo extraer fecha/nombre (ej. manuscrito muy cerrado)
    if es_documento_acta_nacimiento(file_bytes):
        return {"valido": True, "mensaje": "Acta de nacimiento verificada (revisión manual recomendada).", "nombre": None, "fecha_nacimiento": None}
    if datos.get("texto_extraido"):
        return {"valido": False, "mensaje": "No se pudo leer nombre ni fecha en el documento. Sube el PDF del acta certificada (evita imágenes muy borrosas o manuscritas)."}
    return {"valido": False, "mensaje": "El documento no es un acta de nacimiento. Sube el PDF del acta certificada."}


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
    if es_documento_nss(file_bytes):
        return {"valido": False, "mensaje": "El documento es la constancia de NSS del IMSS. En este campo solo se acepta la constancia de situación fiscal del SAT (descargada del portal del SAT)."}
    if not es_documento_constancia_fiscal(file_bytes):
        return {"valido": False, "mensaje": "El documento no es una constancia de situación fiscal del SAT. Sube el PDF descargado del portal del SAT."}
    datos = extraer_datos_constancia_fiscal(file_bytes)

    # Vigencia: máximo 2 meses desde "Lugar y Fecha de Emisión"
    meses = datos.get("meses_antiguedad")
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
    if not datos.get("actividad_economica_asalariado"):
        return {
            "valido": False,
            "mensaje": "La constancia debe tener Actividad Económica 'Asalariado'. Verifica que en el PDF aparezca Asalariado en la sección Actividades Económicas.",
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
            "mensaje": "La constancia debe incluir 'Régimen de Sueldos y Salarios e Ingresos Asimilados a Salarios' en la sección Regímenes (normalmente en la segunda página).",
            "fecha_emision": datos.get("fecha_emision"),
            "meses_antiguedad": meses,
            "vigencia_ok": meses is None or meses <= 2.0,
            "actividad_asalariado": True,
            "regimen_sueldos_salarios": False,
        }

    if not (datos.get("rfc") or (datos.get("curp") and validar_curp(datos["curp"])[0])):
        return {"valido": False, "mensaje": "El documento no parece ser una constancia de situación fiscal del SAT. Sube el PDF descargado del portal del SAT."}

    return {
        "valido": True,
        "mensaje": "Constancia fiscal verificada.",
        "rfc": datos.get("rfc"),
        "curp": datos.get("curp"),
        "fecha_emision": datos.get("fecha_emision"),
        "meses_antiguedad": meses,
        "vigencia_ok": True,
        "actividad_asalariado": True,
        "regimen_sueldos_salarios": True,
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
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de estado de cuenta")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    from app.services.__SPARTA_SECRET_REDACTED___analyzer import validar___SPARTA_SECRET_REDACTED___pdf
    resultado = validar___SPARTA_SECRET_REDACTED___pdf(file_bytes)
    return resultado


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
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    if es_documento_nss(file_bytes):
        return {"curp_extraido": None, "valido": False, "mensaje": "El documento es la constancia de NSS del IMSS. En este campo solo se acepta la constancia de CURP del RENAPO.", "es_reciente": None, "meses_antiguedad": None, "fecha_emision": None}
    if es_documento_constancia_fiscal(file_bytes):
        return {"curp_extraido": None, "valido": False, "mensaje": "El documento es una constancia de situación fiscal (SAT). En este campo solo se acepta la constancia de CURP del RENAPO.", "es_reciente": None, "meses_antiguedad": None, "fecha_emision": None}
    if not es_documento_curp(file_bytes):
        return {"curp_extraido": None, "valido": False, "mensaje": "El documento no es una constancia de CURP. Sube el PDF de la constancia del RENAPO.", "es_reciente": None, "meses_antiguedad": None, "fecha_emision": None}
    curp_extraido = extraer_curp_de_pdf(file_bytes)
    if curp_extraido is None:
        return {"curp_extraido": None, "valido": False, "mensaje": "No se encontró un CURP válido en el documento. Sube la constancia de CURP del RENAPO.",
                "es_reciente": None, "meses_antiguedad": None, "fecha_emision": None}
    valido, mensaje = validar_curp(curp_extraido)
    datos = extraer_datos_curp_pdf(file_bytes)
    nombre = datos.get("nombre") if isinstance(datos, dict) else None
    return {
        "curp_extraido": curp_extraido,
        "valido": valido,
        "mensaje": mensaje,
        "nombre": nombre,
        "es_reciente": datos.get("es_reciente"),
        "meses_antiguedad": datos.get("meses_antiguedad"),
        "fecha_emision": datos.get("fecha_emision"),
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

    extraido = await asyncio.to_thread(_extraer_texto_pdf_rapido, file_bytes, 2)
    texto = extraido.get("texto") or ""
    indicadores = _indicadores_identificacion(texto)
    valido = _parece_identificacion_oficial(texto)
    paginas = int(extraido.get("paginas") or 0)

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

    imagenes = pdf_paginas_a_png_bytes(file_bytes, dpi=150)
    if not imagenes:
        return {
            "aceptado": True,
            "notas": ["No se pudo procesar el PDF para revisión de calidad. Revisar identificación manualmente."],
            "detalle_frente": None,
            "detalle_reverso": None,
        }

    service = VerificacionService()
    tipo = TipoDocumento.RESIDENCIA_TEMPORAL
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
        res_frente = await service.verificar(imagenes[0], tipo)
        forense_f = res_frente.checks.forense
        ocr_f = res_frente.checks.ocr_campos
        detalle_frente = {
            "calidad_foto": forense_f.calidad_foto,
            "brillo_excesivo": forense_f.brillo_excesivo,
            "porcentaje_sobreexpuesto": forense_f.porcentaje_sobreexpuesto,
            "borroso": forense_f.borroso,
            "alertas": forense_f.alertas or [],
            "nombre_ocr": ocr_f.nombre_ocr if ocr_f else None,
            "curp_ocr": ocr_f.curp.get("valor") if ocr_f and ocr_f.curp else None,
        }
        # Nota por calidad (texto amigable, una sola vez)
        texto_calidad = _texto_calidad(forense_f.calidad_foto)
        if texto_calidad:
            notas.append("Revisar identificación oficial: sistema detectó " + texto_calidad)
        # Alertas forense (evitar duplicar si ya se dijo algo similar)
        for a in (forense_f.alertas or []):
            if a and a.strip() and a not in notas:
                notas.append("Revisar identificación oficial: " + a.strip())
        if res_frente.recomendacion and res_frente.recomendacion.strip() and res_frente.recomendacion not in notas:
            notas.append(res_frente.recomendacion.strip())
        for a in (res_frente.alertas_globales or []):
            if a and a.strip() and not any(a.strip() in n for n in notas):
                notas.append(a.strip())

        # Página 2 = reverso (si existe)
        if len(imagenes) > 1:
            res_reverso = await service.verificar(imagenes[1], tipo)
            forense_r = res_reverso.checks.forense
            ocr_r = res_reverso.checks.ocr_campos
            detalle_reverso = {
                "calidad_foto": forense_r.calidad_foto,
                "brillo_excesivo": forense_r.brillo_excesivo,
                "borroso": forense_r.borroso,
                "alertas": forense_r.alertas or [],
                "mrz_nombre": ocr_r.mrz_nombre_completo if ocr_r else None,
                "mrz_fecha_nac": ocr_r.mrz_fecha_nacimiento if ocr_r else None,
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
    tipo_documento: Optional[TipoDocumento] = Query(
        TipoDocumento.RESIDENCIA_TEMPORAL,
        description="Tipo de documento de identificación"
    ),
    api_key: str = Depends(verificar_api_key)
):
    # Resolver frente y reverso: desde PDF (páginas 1 y 2) o desde archivos separados
    frente_bytes: Optional[bytes] = None
    reverso_bytes: Optional[bytes] = None

    if identificacion_pdf and identificacion_pdf.filename and identificacion_pdf.filename.lower().endswith(".pdf"):
        pdf_bytes = await identificacion_pdf.read()
        if pdf_bytes and len(pdf_bytes) >= 100:
            imagenes = await asyncio.to_thread(pdf_paginas_a_png_bytes, pdf_bytes, 150)
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
        res_frente, res_reverso = await asyncio.gather(
            service.verificar(frente_bytes, tipo_documento),
            service.verificar(reverso_bytes, tipo_documento),
        )

        ocr_f = res_frente.checks.ocr_campos
        ocr_r = res_reverso.checks.ocr_campos
        calidad = res_frente.checks.forense.calidad_foto

        # Estos PDFs pueden disparar OCR. Ejecutarlos en paralelo evita sumar sus tiempos uno tras otro.
        datos_curp, datos_nss, datos_fiscal, datos_acta = await asyncio.gather(
            asyncio.to_thread(extraer_datos_curp_pdf, curp_pdf_bytes) if curp_pdf_bytes else asyncio.sleep(0, result=None),
            asyncio.to_thread(extraer_datos_nss_pdf, nss_pdf_bytes) if nss_pdf_bytes else asyncio.sleep(0, result=None),
            asyncio.to_thread(extraer_datos_constancia_fiscal, fiscal_pdf_bytes) if fiscal_pdf_bytes else asyncio.sleep(0, result=None),
            asyncio.to_thread(extraer_datos_acta_nacimiento, acta_pdf_bytes) if acta_pdf_bytes else asyncio.sleep(0, result=None),
        )

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
            "identificacion_frente_score": res_frente.score_autenticidad,
            "identificacion_reverso_score": res_reverso.score_autenticidad,
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
