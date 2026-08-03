"""Render QA del Áqueo oscuro reconstruido sobre Leónidas."""

from __future__ import annotations

import os
import sys
from pathlib import Path


SCRIPT_DIR = Path(__file__).resolve().parent
ROOT = SCRIPT_DIR.parent
sys.path.insert(0, os.fspath(SCRIPT_DIR))

import construir_aqueo_oscuro_etapa_02 as stage02


asset = Path(
    os.environ.get(
        "LEONIDAS_REALISTIC_PREVIEW_ASSET",
        os.fspath(ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "leonidas-aqueo-dark-realistic-rebuild.glb"),
    )
)
output = Path(
    os.environ.get(
        "LEONIDAS_REALISTIC_PREVIEW_OUTPUT",
        os.fspath(ROOT / "storage" / "leonidas-helmet-designs" / "aqueo-dark-realistic-rebuild" / "preview"),
    )
)
output.mkdir(parents=True, exist_ok=True)
stage02.OUTPUT_ROOT = output
stage02.REPORT = output / "preview-report.json"
stage02.OUTPUT_GLB = asset
stage02.render_views(asset)
