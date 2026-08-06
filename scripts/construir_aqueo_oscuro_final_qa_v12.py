"""Áqueo QA v12: ojos alineados y crin sagital negra, estrecha y mate."""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_escultura_v9 as v9
import construir_aqueo_oscuro_final_qa_v11 as v11
import construir_aqueo_oscuro_hueco_v3 as v3


ROOT = Path(__file__).resolve().parents[1]
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-final-qa-v12"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-final-qa-v12.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-final-qa-v12.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-final-qa-v12-report.json"


def open_face(obj):
    openings = []
    for side in (-1.0, 1.0):
        eye = v3.rounded_cube(
            f"AqueoV12_EyeOpening_{side:+.0f}",
            (v9.HEAD_CX + side * 0.034, -0.082, 1.190),
            (0.050, 0.155, 0.027),
            bevel=0.009,
        )
        v3.boolean_difference(obj, eye, f"AlignedEyeOpening_{side:+.0f}")
        openings.append({"kind": "eye", "side": side, "centerZ": 1.190})
    return openings


def assign_by_region(obj, steel, hair):
    # La cresta se estrecha sólo en X: mantiene el gran arco sagital de perfil.
    for vertex in obj.data.vertices:
        if vertex.co.z > 1.310:
            vertex.co.x = v9.HEAD_CX + (vertex.co.x - v9.HEAD_CX) * 0.72
            vertex.co.z = 1.310 + (vertex.co.z - 1.310) * 0.92
    obj.data.update()
    obj.data.materials.clear()
    obj.data.materials.append(steel)
    obj.data.materials.append(hair)
    for polygon in obj.data.polygons:
        polygon.material_index = 1 if polygon.center.z > 1.305 else 0
        polygon.use_smooth = True


def main():
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    v9.OUTPUT_ROOT = OUTPUT_ROOT
    v9.OUTPUT_GLB = OUTPUT_GLB
    v9.OUTPUT_BLEND = OUTPUT_BLEND
    v9.REPORT = REPORT
    v9.calibrate = v11.calibrate
    v9.open_face = open_face
    v9.assign_by_region = assign_by_region
    v9.main()
    report = json.loads(REPORT.read_text(encoding="utf-8"))
    report["stage"] = "aqueo-dark-final-qa-v12"
    report["architecture"] = "deep-wearable-sculpture-aligned-eyes-matte-sagittal-crest"
    report["crest"] = {"frontWidthScale": 0.72, "heightScale": 0.92, "material": "black horsehair"}
    report["productionIntegrated"] = False
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


if __name__ == "__main__":
    main()
