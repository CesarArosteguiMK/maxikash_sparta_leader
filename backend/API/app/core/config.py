# app/core/config.py
"""Configuración central. Carga desde .env; valores por defecto permiten arranque sin .env."""
import os
import sys
from pathlib import Path
from pydantic import field_validator
from pydantic_settings import BaseSettings, SettingsConfigDict
from functools import lru_cache
from typing import List


def _default_tesseract_cmd() -> str:
    # Raíz de esta API: .../backend/API  (este archivo está en app/core/config.py)
    api_root = Path(__file__).resolve().parents[2]
    portable = (
        api_root / "tools" / "tesseract.exe",
        api_root / "tools" / "Tesseract-OCR" / "tesseract.exe",
    )
    if sys.platform == "win32":
        for p in portable:
            if p.is_file():
                return str(p)
        win_path = r"C:\Program Files\Tesseract-OCR\tesseract.exe"
        if os.path.isfile(win_path):
            return win_path
        win_path86 = r"C:\Program Files (x86)\Tesseract-OCR\tesseract.exe"
        if os.path.isfile(win_path86):
            return win_path86
    else:
        for p in portable:
            if p.is_file():
                return str(p)
    return "tesseract"


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        case_sensitive=False,
        extra="ignore",
    )

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

    # Tesseract (en Windows auto-detecta; definir TESSERACT_CMD en .env para override)
    tesseract_cmd: str = _default_tesseract_cmd()

    @field_validator("tesseract_cmd", mode="after")
    @classmethod
    def _tesseract_cmd_resuelve_si_invalido(cls, v: str) -> str:
        s = (v or "").strip().strip('"')
        if sys.platform == "win32" and s.startswith("/"):
            # Típico .env copiado de Linux (Docker/ejemplo)
            s = _default_tesseract_cmd()
        p = Path(s) if s else None
        if p is not None and p.is_file():
            return str(p.resolve())
        auto = Path(_default_tesseract_cmd())
        if auto.is_file():
            return str(auto.resolve())
        return s or "tesseract"

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

    # CORS (permitir origen del frontend PHP / Sparta Ledger)
    cors_origins: str = "http://localhost:3000,http://localhost,http://localhost:8086,http://127.0.0.1,http://127.0.0.1:8086"

    # Motor IA documental. Valores:
    # - legacy: usa el motor OCR actual.
    # - alibaba: usa Alibaba/Qwen para la validacion rapida (compatibilidad).
    # - gemini: usa Google Gemini para lectura documental y cruces.
    doc_ai_engine: str = "legacy"
    doc_ai_legacy_fallback: bool = True
    doc_ai_quick_timeout_seconds: int = 35
    doc_ai_quick_max_pages: int = 3
    doc_ai_quick_dpi: int = 150
    doc_ai_quick_cache_enabled: bool = True
    doc_ai_quick_cache_dir: str = ""
    doc_ai_quick_cache_ttl_seconds: int = 2592000
    doc_ai_crosscheck_mode: str = "rules"
    doc_ai_crosscheck_timeout_seconds: int = 90
    doc_ai_crosscheck_max_pages_per_document: int = 2
    doc_ai_crosscheck_dpi: int = 135

    # Alibaba Model Studio (OpenAI compatible)
    alibaba_api_key: str = ""
    alibaba_base_url: str = ""
    alibaba_model: str = "qwen3.5-flash"
    alibaba_crosscheck_model: str = "qwen3.7-plus"
    alibaba_crosscheck_fallback_models: str = ""
    alibaba_fallback_models: str = ""
    alibaba_retry_delays: str = "0,1"

    # Google Gemini API
    gemini_api_key: str = ""
    gemini_base_url: str = "https://generativelanguage.googleapis.com/v1beta"
    gemini_model: str = "gemini-3.5-flash"
    gemini_crosscheck_model: str = "gemini-3.5-flash"
    gemini_crosscheck_fallback_models: str = "gemini-3.1-flash-lite"
    gemini_fallback_models: str = "gemini-3.1-flash-lite"
    gemini_retry_delays: str = "0,1"

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

@lru_cache()
def get_settings() -> Settings:
    """Singleton de configuración."""
    return Settings()
