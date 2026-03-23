# app/api/routes.py
"""
Endpoints de la API REST.
"""
from fastapi import APIRouter, UploadFile, File, HTTPException, Depends, Query
from fastapi.security.api_key import APIKeyHeader
from typing import Optional
from loguru import logger

from app.models.schemas import (
    VerificacionResponse, TipoDocumento, ErrorResponse
)
from app.services.verificacion import VerificacionService
from app.core.config import get_settings

router = APIRouter()
settings = get_settings()

# Seguridad API Key
api_key_header = APIKeyHeader(name=settings.api_key_header, auto_error=False)

EXTENSIONES_PERMITIDAS = {"jpg", "jpeg", "png", "webp", "tiff"}
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
    # Validar imagen
    validar_imagen(imagen)

    # Leer bytes
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


@router.get(
    "/tipos-documento",
    summary="Tipos de documento soportados",
    tags=["Utilidades"]
)
async def listar_tipos():
    """Lista los tipos de documentos que el sistema puede verificar."""
    return {
        "tipos_soportados": [
            {
                "codigo": "INE_NUEVA",
                "nombre": "INE / Credencial para Votar (2014 en adelante)",
                "emisor": "Instituto Nacional Electoral"
            },
            {
                "codigo": "INE_ANTERIOR",
                "nombre": "IFE / Credencial para Votar (anterior a 2014)",
                "emisor": "Instituto Federal Electoral"
            },
            {
                "codigo": "RESIDENCIA_TEMPORAL",
                "nombre": "Tarjeta de Residencia Temporal",
                "emisor": "Instituto Nacional de Migración"
            },
            {
                "codigo": "RESIDENCIA_TEMPORAL_ACUMULATIVA",
                "nombre": "Tarjeta de Residencia Temporal Acumulativa",
                "emisor": "Instituto Nacional de Migración"
            },
            {
                "codigo": "RESIDENCIA_PERMANENTE",
                "nombre": "Tarjeta de Residencia Permanente",
                "emisor": "Instituto Nacional de Migración"
            },
        ]
    }


@router.get("/health", tags=["Sistema"])
async def health_check():
    """Verifica que el servicio esté funcionando."""
    return {"status": "ok", "version": settings.app_version}
