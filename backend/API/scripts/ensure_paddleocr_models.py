"""Prepara PaddleOCR para uso offline/local desde la carpeta API.

Lo llama launcher/instalar-agente.bat despues de instalar requirements.txt.
Si es la primera vez, PaddleOCR descargara sus modelos gratuitos a:
  backend/API/.paddlex_cache_runtime
"""
from __future__ import annotations

import io
import os
import sys
from pathlib import Path


API_DIR = Path(__file__).resolve().parents[1]
os.environ["USERPROFILE"] = str(API_DIR / ".paddle_home")
os.environ["HOME"] = str(API_DIR / ".paddle_home")
os.environ["XDG_CACHE_HOME"] = str(API_DIR / ".paddle_home" / ".cache")
os.environ["PADDLE_HOME"] = str(API_DIR / ".paddle_home" / ".cache" / "paddle")
os.environ["PADDLE_PDX_CACHE_HOME"] = str(API_DIR / ".paddlex_cache_runtime")
os.environ.setdefault("PADDLE_PDX_ENABLE_MKLDNN_BYDEFAULT", "False")
os.environ.setdefault("FLAGS_use_mkldnn", "0")
os.environ.setdefault("FLAGS_use_onednn", "0")

for env_name in ("USERPROFILE", "XDG_CACHE_HOME", "PADDLE_HOME", "PADDLE_PDX_CACHE_HOME"):
    Path(os.environ[env_name]).mkdir(parents=True, exist_ok=True)

if str(API_DIR) not in sys.path:
    sys.path.insert(0, str(API_DIR))

from PIL import Image, ImageDraw, ImageFont  # noqa: E402

from app.services.ocr_local import extraer_texto_paddle  # noqa: E402


def main() -> int:
    img = Image.new("RGB", (900, 220), "white")
    draw = ImageDraw.Draw(img)
    try:
        font = ImageFont.truetype(r"C:\Windows\Fonts\arial.ttf", 42)
    except Exception:
        font = ImageFont.load_default()
    draw.text((30, 80), "CURP ABCD010203HDFXXX09", font=font, fill="black")

    buf = io.BytesIO()
    img.save(buf, format="PNG")
    texto = (extraer_texto_paddle(buf.getvalue()) or "").strip()
    if "CURP" not in texto:
        print("PADDLEOCR_WARN: motor instalado pero prueba OCR sin texto esperado.")
        print("PADDLEOCR_TEXT:", texto[:300])
        return 2

    print("PADDLEOCR_OK:", texto[:120])
    print("PADDLEOCR_CACHE:", os.environ["PADDLE_PDX_CACHE_HOME"])
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
