# app/core/config.py
"""Configuración central. Carga desde .env; valores por defecto permiten arranque sin .env."""
from pydantic_settings import BaseSettings
from functools import lru_cache
from typing import List

class Settings(BaseSettings):
    # App
    app_name: str = "Doc Verificacion API"
    app_version: str = "1.0.0"
    debug: bool = False
    secret_key: str = "dev-secret-cambiar-en-produccion"

    # API Key (para integración con Sparta Ledger / PHP)
    api_key_header: str = "X-API-Key"
    master_api_key: str = "sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key"

    # Base de Datos (opcional para solo verificación; dejar default si no se usa BD)
    database_url: str = "sqlite:///./temp_verificacion.db"
    database_pool_size: int = 10
    database_max_overflow: int = 20

    # Redis (opcional)
    redis_url: str = "redis://localhost:6379/0"

    # Tesseract (en Windows usar PATH o definir TESSERACT_CMD en .env con ruta completa)
    tesseract_cmd: str = "/usr/bin/tesseract"


    # Google Vision (opcional)
    use_google_vision: bool = False
    google_application_credentials: str = ""

    # ML
    ml_model_path: str = "./models/efficientnet_documentos.pth"
    ml_model_threshold: float = 0.5
    use_ml_classifier: bool = False

    # Rate Limiting
    rate_limit_per_minute: int = 30
    rate_limit_per_day: int = 500

    # Imágenes
    max_image_size_mb: int = 10
    allowed_extensions: str = "jpg,jpeg,png,webp,tiff"
    image_max_width: int = 4000
    image_max_height: int = 4000

    # Umbrales
    umbral_real: int = 75
    umbral_revision: int = 70

    # Temp
    temp_upload_dir: str = "/tmp/doc_verificacion"
    delete_temp_after_seconds: int = 300

    # CORS (permitir origen del frontend PHP)
    cors_origins: str = "http://localhost:3000,http://localhost,http://127.0.0.1"

    @property
    def allowed_extensions_list(self) -> List[str]:
        return [ext.strip() for ext in self.allowed_extensions.split(",")]

    @property
    def cors_origins_list(self) -> List[str]:
        return [o.strip() for o in self.cors_origins.split(",")]

    # Pesos del score multicapa (suman 1.0)
    pesos_score: dict = {
        "metadatos": 0.15,
        "forense": 0.20,
        "geometria": 0.15,
        "ocr_campos": 0.30,
        "codigo_barras": 0.10,
        "ml_classifier": 0.10,
    }

    class Config:
        env_file = ".env"
        env_file_encoding = "utf-8"
        case_sensitive = False


@lru_cache()
def get_settings() -> Settings:
    """Singleton de configuración."""
    return Settings()
