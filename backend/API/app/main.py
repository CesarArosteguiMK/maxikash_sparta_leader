# app/main.py
"""
Punto de entrada principal de la API FastAPI.
"""
# pyzbar debe tener DLLs locales antes de importar rutas/servicios que lo usen.
from app.core.zbar_local import ensure_local_zbar_dlls

ensure_local_zbar_dlls()

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.openapi.docs import get_redoc_html
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded
from loguru import logger
import sys
import os

from app.api.routes import router
from app.core.config import get_settings

settings = get_settings()

# ---- Configurar Logger ----
logger.remove()
logger.add(
    sys.stdout,
    format="<green>{time:HH:mm:ss}</green> | <level>{level}</level> | {message}",
    level="DEBUG" if settings.debug else "INFO"
)
logger.add(
    "logs/api_{time:YYYY-MM-DD}.log",
    rotation="1 day",
    retention="30 days",
    level="INFO"
)

# ---- Rate Limiter ----
limiter = Limiter(key_func=get_remote_address)

# ---- FastAPI App ----
app = FastAPI(
    title=settings.app_name,
    version=settings.app_version,
    description="""
    ## API de Verificación de Documentos Mexicanos (Sparta Ledger)

    Verifica la autenticidad de documentos oficiales mexicanos usando análisis multicapa:

    - **INE** (Credencial para Votar)
    - **Residencia Temporal** (INM)
    - **Residencia Temporal Acumulativa** (INM)
    - **Residencia Permanente** (INM)

    ### Tecnología
    - Error Level Analysis (ELA)
    - Detección de moiré y screenshots
    - OCR con validación de CURP y campos
    - Verificación de códigos QR/Barcode
    - Clasificador ML (opcional)
    """,
    docs_url="/docs",
    redoc_url=None,  # ReDoc se sirve en ruta custom con CDN alternativo (ver abajo)
)

# ---- Middlewares ----
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origins_list,
    allow_credentials=True,
    allow_methods=["GET", "POST", "OPTIONS"],
    allow_headers=["*"],
)

# Rate limiting
app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

# ---- Incluir rutas ----
app.include_router(router, prefix="/api/v1")


# ReDoc con CDN alternativo (cdn.redoc.ly suele quedar en blanco en algunos entornos)
@app.get("/redoc", include_in_schema=False)
async def redoc_html():
    return get_redoc_html(
        openapi_url=app.openapi_url,
        title=app.title + " - ReDoc",
        redoc_js_url="https://cdn.jsdelivr.net/npm/redoc@2.1.3/bundles/redoc.standalone.js",
        redoc_favicon_url="https://fastapi.tiangolo.com/img/favicon.png",
    )


@app.on_event("startup")
async def startup():
    """Inicialización al arrancar."""
    import tempfile
    temp_dir = settings.temp_upload_dir or os.path.join(tempfile.gettempdir(), "doc_verificacion")
    os.makedirs(temp_dir, exist_ok=True)
    os.makedirs("logs", exist_ok=True)
    logger.info(f"{settings.app_name} v{settings.app_version} iniciado")
    logger.info(f"Docs: http://localhost:8001/docs")


@app.on_event("shutdown")
async def shutdown():
    logger.info("Servidor detenido")
