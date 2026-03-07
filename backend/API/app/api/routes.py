# app/api/routes.py
"""
Endpoints de la API REST.
"""
import asyncio
from fastapi import APIRouter, UploadFile, File, HTTPException, Depends, Query, Body
from fastapi.security.api_key import APIKeyHeader
from typing import Optional
from loguru import logger

import time
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
    validacion_cruzada,
    es_documento_nss, es_documento_curp, es_documento_constancia_fiscal, es_documento_acta_nacimiento,
)
from app.core.config import get_settings

router = APIRouter()
settings = get_settings()

api_key_header = APIKeyHeader(name=settings.api_key_header, auto_error=False)

EXTENSIONES_PERMITIDAS = {"jpg", "jpeg", "png", "webp", "tiff"}
EXTENSIONES_COMPROBANTE = {"jpg", "jpeg", "png", "webp", "tiff", "pdf"}
MAX_SIZE_BYTES = settings.max_image_size_mb * 1024 * 1024


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
            recomendacion = prefijo + f"tiene {check.meses_antiguedad or 0} meses de antigüedad. Se requiere máximo 3 meses."
        elif check.es_reciente is None and not check.fecha_documento:
            resultado = "RECHAZADO"
            recomendacion = prefijo + "no se pudo verificar la fecha o tiene más de 3 meses de antigüedad."
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
    summary="Extraer y validar NSS desde PDF (constancia IMSS)",
    description="Sube un PDF (ej. NSS.pdf). Se extrae el NSS de 11 dígitos del texto y se valida. Retorna nss_extraido, valido y mensaje.",
    tags=["Utilidades"]
)
async def verificar_nss_documento(
    documento: UploadFile = File(..., description="PDF de constancia de NSS (ej. NSS.pdf)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de constancia de NSS")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    if not es_documento_nss(file_bytes):
        return {"nss_extraido": None, "valido": False, "mensaje": "El documento no es la constancia de NSS del IMSS. Sube el PDF de la constancia de número de seguridad social."}
    nss_extraido = extraer_nss_de_pdf(file_bytes)
    if nss_extraido is None:
        return {"nss_extraido": None, "valido": False, "mensaje": "No se encontró un NSS de 11 dígitos en el documento. Sube la constancia de NSS del IMSS."}
    valido, mensaje = validar_nss(nss_extraido)
    return {"nss_extraido": nss_extraido, "valido": valido, "mensaje": mensaje}


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
    return {
        "curp_extraido": curp_extraido,
        "valido": valido,
        "mensaje": mensaje,
        "es_reciente": datos.get("es_reciente"),
        "meses_antiguedad": datos.get("meses_antiguedad"),
        "fecha_emision": datos.get("fecha_emision"),
    }


@router.post(
    "/validar-expediente",
    summary="Validación cruzada de expediente completo",
    description="""
    Sube todos los documentos del candidato y el sistema cruza CURP, nombre y fecha
    de nacimiento entre ellos. Documentos requeridos: frente y reverso de la
    identificación oficial. Documentos opcionales: CURP.pdf, NSS.pdf,
    constancia_fiscal.pdf, acta_nacimiento.pdf.

    **Reglas:**
    - Si la foto de la identificación tiene brillo excesivo o está borrosa → RECHAZADA.
    - CURP y nombre se comparan entre: ID, CURP.pdf, constancia fiscal, NSS.
    - Fecha de nacimiento se compara entre: CURP (decodificado), MRZ reverso, acta.
    - Si el CURP de la ID no coincide con el del documento CURP pero el nombre sí
      → se usa el CURP del documento oficial (sobremontado).
    - La constancia CURP debe tener máximo 3 meses de descargada.
    """,
    tags=["Verificación"]
)
async def validar_expediente(
    frente: UploadFile = File(..., description="Imagen del frente de la identificación"),
    reverso: UploadFile = File(..., description="Imagen del reverso de la identificación"),
    documento_curp: Optional[UploadFile] = File(None, description="PDF constancia CURP"),
    documento_nss: Optional[UploadFile] = File(None, description="PDF constancia NSS"),
    constancia_fiscal: Optional[UploadFile] = File(None, description="PDF constancia de situación fiscal"),
    acta_nacimiento: Optional[UploadFile] = File(None, description="PDF acta de nacimiento"),
    tipo_documento: Optional[TipoDocumento] = Query(
        TipoDocumento.RESIDENCIA_TEMPORAL,
        description="Tipo de documento de identificación"
    ),
    api_key: str = Depends(verificar_api_key)
):
    frente_bytes = await frente.read()
    reverso_bytes = await reverso.read()
    if not frente_bytes or not reverso_bytes:
        raise HTTPException(status_code=400, detail="Frente y reverso de la identificación son requeridos")

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

        datos_curp = await asyncio.to_thread(extraer_datos_curp_pdf, curp_pdf_bytes) if curp_pdf_bytes else None
        datos_nss = await asyncio.to_thread(extraer_datos_nss_pdf, nss_pdf_bytes) if nss_pdf_bytes else None
        datos_fiscal = await asyncio.to_thread(extraer_datos_constancia_fiscal, fiscal_pdf_bytes) if fiscal_pdf_bytes else None
        datos_acta = await asyncio.to_thread(extraer_datos_acta_nacimiento, acta_pdf_bytes) if acta_pdf_bytes else None

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
        )

        tiempo_ms = int((time.time() - inicio) * 1000)

        return {
            **resultado,
            "identificacion_frente_score": res_frente.score_autenticidad,
            "identificacion_reverso_score": res_reverso.score_autenticidad,
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
