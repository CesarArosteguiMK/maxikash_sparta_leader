"""Motores OCR locales gratuitos, con fallback silencioso.

Orden recomendado:
1. PaddleOCR si esta instalado (mejor OCR local general).
2. RapidOCR/Tesseract se mantienen en los servicios existentes como fallback.
"""
import io
import os
from typing import Optional, Any

from loguru import logger

_PADDLE_ENGINE: Any = None


def _get_paddleocr():
    global _PADDLE_ENGINE
    if _PADDLE_ENGINE is not None:
        return _PADDLE_ENGINE if _PADDLE_ENGINE is not False else None
    try:
        api_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
        local_home = os.path.join(api_dir, ".paddle_home")
        paddlex_cache = os.path.join(api_dir, ".paddlex_cache_runtime")
        paddle_cache = os.path.join(local_home, ".cache", "paddle")
        for path in (local_home, paddlex_cache, paddle_cache):
            os.makedirs(path, exist_ok=True)
        os.environ["USERPROFILE"] = local_home
        os.environ["HOME"] = local_home
        os.environ["XDG_CACHE_HOME"] = os.path.join(local_home, ".cache")
        os.environ["PADDLE_HOME"] = paddle_cache
        os.environ["PADDLE_PDX_CACHE_HOME"] = paddlex_cache
        os.environ.setdefault("PADDLE_PDX_ENABLE_MKLDNN_BYDEFAULT", "False")
        os.environ.setdefault("FLAGS_use_mkldnn", "0")
        os.environ.setdefault("FLAGS_use_onednn", "0")
        from paddleocr import PaddleOCR  # type: ignore

        try:
            _PADDLE_ENGINE = PaddleOCR(use_angle_cls=True, lang="es", show_log=False)
        except Exception:
            try:
                _PADDLE_ENGINE = PaddleOCR(lang="es")
            except Exception:
                _PADDLE_ENGINE = PaddleOCR()
        return _PADDLE_ENGINE
    except Exception as e:
        logger.debug(f"PaddleOCR no disponible: {e}")
        _PADDLE_ENGINE = False
        return None


def _lineas_paddle(result) -> list[str]:
    lineas: list[str] = []
    if not result:
        return lineas
    for bloque in result:
        if isinstance(bloque, dict):
            rec_texts = bloque.get("rec_texts")
            if isinstance(rec_texts, list):
                lineas.extend(str(t).strip() for t in rec_texts if t)
            continue
        items = bloque if isinstance(bloque, list) else [bloque]
        for item in items:
            if not isinstance(item, (list, tuple)) or len(item) < 2:
                continue
            data = item[1]
            texto = None
            if isinstance(data, (list, tuple)) and data:
                texto = data[0]
            elif isinstance(data, str):
                texto = data
            if texto:
                lineas.append(str(texto).strip())
    return [l for l in lineas if l]


def extraer_texto_paddle(image_bytes: bytes) -> Optional[str]:
    engine = _get_paddleocr()
    if engine is None or not image_bytes:
        return None
    try:
        import numpy as np
        from PIL import Image

        img = Image.open(io.BytesIO(image_bytes))
        if img.mode != "RGB":
            img = img.convert("RGB")
        arr = np.array(img)
        try:
            result = engine.ocr(arr, cls=True)
        except TypeError:
            result = engine.ocr(arr)
        lineas = _lineas_paddle(result)
        return "\n".join(lineas) if lineas else None
    except Exception as e:
        logger.debug(f"PaddleOCR fallo en imagen: {e}")
        return None
