"""
Smoke import: intenta cargar la app FastAPI tal como lo haria uvicorn.
Si funciona, imprime SMOKE_OK. Si no, imprime SMOKE_ERR + traceback.
Lo invocan los .bat / .ps1 del launcher para detectar errores ANTES de
arrancar uvicorn (asi el usuario ve el problema real, no un proceso que
muere en silencio).
"""
import os
import sys
import traceback
import warnings

# Para el launcher solo importa si la app carga o no carga. Una advertencia
# de FastAPI/Pydantic no debe impedir que uvicorn arranque.
warnings.simplefilter("ignore", Warning)

# Cuando el script vive en launcher\, asegurar que la carpeta API este en sys.path.
_THIS = os.path.dirname(os.path.abspath(__file__))
_API_DIR = os.path.dirname(_THIS)
if _API_DIR not in sys.path:
    sys.path.insert(0, _API_DIR)

try:
    from app.main import app  # noqa: F401
except SystemExit:
    raise
except BaseException:
    print("SMOKE_ERR")
    traceback.print_exc()
    sys.exit(1)
else:
    print("SMOKE_OK")
    sys.exit(0)
