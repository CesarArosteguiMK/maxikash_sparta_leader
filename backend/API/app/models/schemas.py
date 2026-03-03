# app/models/schemas.py
"""
Schemas Pydantic para request/response de la API.
"""
from pydantic import BaseModel, Field
from typing import Optional, List, Dict, Any
from enum import Enum


class TipoDocumento(str, Enum):
    INE_NUEVA = "INE_NUEVA"
    INE_ANTERIOR = "INE_ANTERIOR"
    RESIDENCIA_TEMPORAL = "RESIDENCIA_TEMPORAL"
    RESIDENCIA_TEMPORAL_ACUMULATIVA = "RESIDENCIA_TEMPORAL_ACUMULATIVA"
    RESIDENCIA_PERMANENTE = "RESIDENCIA_PERMANENTE"
    COMPROBANTE_DOMICILIO = "COMPROBANTE_DOMICILIO"
    DESCONOCIDO = "DESCONOCIDO"


class TipoComprobante(str, Enum):
    CFE_LUZ = "CFE_LUZ"
    AGUA = "AGUA"
    GAS = "GAS"
    TELEFONO_INTERNET = "TELEFONO_INTERNET"
    BANCO = "BANCO"
    PREDIAL = "PREDIAL"


class ResultadoVerificacion(str, Enum):
    ORIGINAL = "ORIGINAL"
    REVISION_MANUAL = "REVISION_MANUAL"
    RECHAZADO = "RECHAZADO"


class CheckMetadatos(BaseModel):
    ok: bool
    editor_detectado: Optional[str] = None
    es_screenshot: bool = False
    software: Optional[str] = None
    fecha_creacion: Optional[str] = None
    dispositivo: Optional[str] = None
    alertas: List[str] = []
    score: float = Field(ge=0.0, le=1.0)


class CheckForense(BaseModel):
    ok: bool
    ela_score: float = Field(description="Error Level Analysis - menor es mejor (<15 normal)")
    moire_detectado: bool = False
    pixeles_pantalla: bool = False
    ruido_natural: bool = True
    compresion_uniforme: bool = True
    brillo_excesivo: bool = False
    porcentaje_sobreexpuesto: float = Field(default=0.0, description="% de pixeles sobreexpuestos")
    borroso: bool = Field(False, description="Imagen con posible desenfoque (revisar captura)")
    calidad_foto: str = Field("ok", description="Resumen: ok | revisar_brillo | revisar_borroso | revisar_brillo_y_borroso")
    alertas: List[str] = []
    score: float = Field(ge=0.0, le=1.0)


class CheckGeometria(BaseModel):
    ok: bool
    aspecto_ratio_detectado: Optional[str] = None
    aspecto_ratio_esperado: Optional[str] = None
    proporcion_correcta: bool = False
    perspectiva_valida: bool = True
    alertas: List[str] = []
    score: float = Field(ge=0.0, le=1.0)


class CheckOCR(BaseModel):
    ok: bool
    curp: Optional[Dict[str, Any]] = None
    clave_elector: Optional[Dict[str, Any]] = None
    seccion_electoral: Optional[Dict[str, Any]] = None
    vigencia: Optional[Dict[str, Any]] = None
    numero_documento: Optional[Dict[str, Any]] = None
    tipo_residencia: Optional[Dict[str, Any]] = None
    fecha_expedicion: Optional[Dict[str, Any]] = None
    fecha_vencimiento: Optional[Dict[str, Any]] = None
    nombre_ocr: Optional[str] = Field(None, description="Nombre extraído del texto OCR (frente)")
    fecha_nacimiento_curp: Optional[str] = Field(None, description="Fecha nacimiento decodificada del CURP detectado")
    mrz_nombre_completo: Optional[str] = Field(None, description="Nombre completo del MRZ (reverso) para cotejar con frente")
    mrz_fecha_nacimiento: Optional[str] = Field(None, description="Fecha nacimiento del MRZ (ej. 12/01/1996) para cotejar con frente")
    cotejo_frente_reverso: Optional[Dict[str, Any]] = Field(None, description="Comparación automática frente vs reverso")
    campos_detectados: int = 0
    campos_validos: int = 0
    alertas: List[str] = []
    score: float = Field(ge=0.0, le=1.0)


class CheckCodigos(BaseModel):
    ok: bool
    qr_detectado: bool = False
    barcode_detectado: bool = False
    pdf417_detectado: bool = False
    contenido_valido: bool = False
    url_oficial: Optional[str] = None
    alertas: List[str] = []
    score: float = Field(ge=0.0, le=1.0)


class CheckComprobante(BaseModel):
    ok: bool
    tipo_comprobante: Optional[str] = None
    empresa_detectada: Optional[str] = None
    nombre_titular: Optional[str] = None
    direccion_detectada: Optional[str] = None
    fecha_documento: Optional[str] = None
    es_reciente: Optional[bool] = None
    meses_antiguedad: Optional[float] = None
    campos_detectados: int = 0
    campos_validos: int = 0
    alertas: List[str] = []
    score: float = Field(ge=0.0, le=1.0)


class ComprobanteResponse(BaseModel):
    tipo_comprobante: Optional[str] = None
    score_validacion: int = Field(ge=0, le=100)
    resultado: str
    nombre_titular: Optional[str] = None
    direccion: Optional[str] = None
    fecha_documento: Optional[str] = None
    es_reciente: Optional[bool] = None
    meses_antiguedad: Optional[float] = None
    empresa: Optional[str] = None
    alertas: List[str] = []
    recomendacion: str
    tiempo_proceso_ms: int


class CheckML(BaseModel):
    ok: bool
    probabilidad_real: float = Field(ge=0.0, le=1.0, default=0.0)
    probabilidad_falso: float = Field(ge=0.0, le=1.0, default=0.0)
    clase_predicha: Optional[str] = None
    modelo_disponible: bool = False
    score: float = Field(ge=0.0, le=1.0)


class Checks(BaseModel):
    metadatos: CheckMetadatos
    forense: CheckForense
    geometria: CheckGeometria
    ocr_campos: CheckOCR
    codigo_barras: CheckCodigos
    ml_classifier: CheckML


class VerificacionResponse(BaseModel):
    documento_tipo: TipoDocumento
    score_autenticidad: int = Field(ge=0, le=100, description="Score global 0-100")
    resultado: ResultadoVerificacion
    confianza: str = Field(description="ALTA/MEDIA/BAJA")
    tiempo_proceso_ms: int
    checks: Checks
    alertas_globales: List[str] = []
    recomendacion: str

    class Config:
        use_enum_values = True


class ErrorResponse(BaseModel):
    error: str
    detalle: Optional[str] = None
    codigo: str
