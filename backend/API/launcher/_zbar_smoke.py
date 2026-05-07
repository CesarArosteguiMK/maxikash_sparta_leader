"""Smoke test pyzbar tras preparar DLLs locales (usa mismo cwd que la API)."""
import os
import sys
from pathlib import Path

api_dir = Path(__file__).resolve().parents[1]
os.chdir(api_dir)
if str(api_dir) not in sys.path:
    sys.path.insert(0, str(api_dir))

from app.core.zbar_local import ensure_local_zbar_dlls, zbar_decode_import_ok

ensure_local_zbar_dlls()
if not zbar_decode_import_ok():
    sys.exit(1)
sys.exit(0)
