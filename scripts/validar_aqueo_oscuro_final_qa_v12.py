"""Valida el Áqueo QA v12 contra la cabeza posada de Leónidas."""

from __future__ import annotations

import os
import sys
from pathlib import Path

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import validar_aqueo_oscuro_original_v6 as validator


ROOT = Path(__file__).resolve().parents[1]
validator.HELMET = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-final-qa-v12.glb"
validator.REPORT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-final-qa-v12/leonidas-aqueo-dark-final-qa-v12-collision-report.json"


if __name__ == "__main__":
    validator.main()
