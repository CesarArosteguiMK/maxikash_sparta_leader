"""Ejecuta el Motor V1 de semanas cotizadas para un único PDF."""

from __future__ import annotations

import json
import sys
from pathlib import Path


API_DIR = Path(__file__).resolve().parents[1]
if str(API_DIR) not in sys.path:
    sys.path.insert(0, str(API_DIR))

from app.services.semanas_cotizadas_analyzer import analizar_semanas_cotizadas


def main() -> int:
    if hasattr(sys.stdout, "reconfigure"):
        sys.stdout.reconfigure(encoding="utf-8")
    if len(sys.argv) != 2:
        print(json.dumps({"ok": False, "error": "Se requiere la ruta del PDF."}))
        return 2

    ruta = Path(sys.argv[1]).resolve()
    if not ruta.is_file() or ruta.suffix.lower() != ".pdf":
        print(json.dumps({"ok": False, "error": "El PDF no existe."}))
        return 3

    try:
        resultado = analizar_semanas_cotizadas(ruta.read_bytes())
    except Exception as exc:
        print(json.dumps({"ok": False, "error": str(exc)}, ensure_ascii=False))
        return 4

    print(json.dumps({"ok": True, "analisis": resultado}, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
