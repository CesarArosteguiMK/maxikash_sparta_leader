"""Calibración QA final: cráneo contenido y ojos alineados, sin ventana bucal."""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path

import bpy
from mathutils import Vector

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_escultura_v9 as v9
import construir_aqueo_oscuro_hueco_v3 as v3


ROOT = Path(__file__).resolve().parents[1]
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-final-qa-v11"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-final-qa-v11.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-final-qa-v11.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-final-qa-v11-report.json"


def calibrate(obj):
    minimum, maximum = v9.bounds(obj)
    extent = maximum - minimum
    center = (minimum + maximum) * 0.5
    source_eye_z = maximum.z - extent.z * 0.507
    scale = Vector((0.205, 0.170, 0.150))
    obj.scale = scale
    obj.location.x = v9.HEAD_CX - center.x * scale.x
    obj.location.y = -0.145 - minimum.y * scale.y
    obj.location.z = 1.237 - source_eye_z * scale.z
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    obj.select_set(False)
    return {
        "scale": list(scale),
        "targetHeadCenterX": v9.HEAD_CX,
        "targetEyeZ": 1.237,
        "targetFrontY": -0.145,
        "faceFrontY": -0.077525,
        "frontEnvelope": 0.067475,
    }


def open_face(obj):
    openings = []
    for side in (-1.0, 1.0):
        eye = v3.rounded_cube(
            f"AqueoV11_EyeOpening_{side:+.0f}",
            (v9.HEAD_CX + side * 0.034, -0.082, 1.208),
            (0.050, 0.155, 0.025),
            bevel=0.009,
        )
        v3.boolean_difference(obj, eye, f"AlignedEyeOpening_{side:+.0f}")
        openings.append({"kind": "eye", "side": side, "centerZ": 1.208})
    return openings


def main():
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    v9.OUTPUT_ROOT = OUTPUT_ROOT
    v9.OUTPUT_GLB = OUTPUT_GLB
    v9.OUTPUT_BLEND = OUTPUT_BLEND
    v9.REPORT = REPORT
    v9.calibrate = calibrate
    v9.open_face = open_face
    v9.main()
    report = json.loads(REPORT.read_text(encoding="utf-8"))
    report["stage"] = "aqueo-dark-final-qa-v11"
    report["architecture"] = "deep-wearable-sculpture-anatomical-cavity-aligned-eye-openings"
    report["productionIntegrated"] = False
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


if __name__ == "__main__":
    main()
