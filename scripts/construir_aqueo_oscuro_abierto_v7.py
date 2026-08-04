"""Reconstruye el Áqueo oscuro con un visor anatómico realmente abierto.

Esta iteración conserva la carcasa ajustada de v6, pero no confía en un
booleano sobre la malla histórica no-manifold. Retira por clasificación
espacial todos los polígonos frontales que invaden la cara de Leónidas. La
piel nunca comparte material con el casco: el nasal es una pieza aparte.
"""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path

import bmesh

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_original_v6 as v6


ROOT = Path(__file__).resolve().parents[1]
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-open-v7"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-open-v7.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-open-v7.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-open-v7-report.json"


def remove_embedded_original_anatomy(shell):
    """Abre la cara por coordenadas, sin depender de booleanos frágiles."""
    before_vertices = len(shell.data.vertices)
    before_faces = len(shell.data.polygons)
    bm = bmesh.new()
    bm.from_mesh(shell.data)
    bm.faces.ensure_lookup_table()

    remove = []
    for face in bm.faces:
        center = face.calc_center_median()
        x = abs(center.x - v6.CX)
        y = center.y
        z = center.z

        # El frente de la cabeza está hacia Y negativo. El recorte incluye
        # frente, ojos, nariz, boca y barba, y conserva sienes y bóveda.
        if 1.095 < z < 1.303 and y < 0.018:
            if z > 1.258:
                half_width = 0.064
            elif z > 1.170:
                half_width = 0.073
            else:
                half_width = 0.061
            if x < half_width:
                remove.append(face)

    if remove:
        bmesh.ops.delete(bm, geom=remove, context="FACES")
    loose = [vertex for vertex in bm.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(bm, geom=loose, context="VERTS")
    if bm.faces:
        bmesh.ops.recalc_face_normals(bm, faces=list(bm.faces))
    bm.to_mesh(shell.data)
    bm.free()
    shell.data.update()
    return {
        "method": "spatial-face-pruning",
        "beforeVertices": before_vertices,
        "afterVertices": len(shell.data.vertices),
        "beforeFaces": before_faces,
        "afterFaces": len(shell.data.polygons),
    }


def make_material(name, color, metallic, roughness, noise_scale, bump_strength):
    """Acero negro envejecido y crin mate, evitando el plástico brillante."""
    if "Steel" in name:
        return v6._original_make_material(
            name,
            (0.010, 0.014, 0.020, 1.0),
            0.78,
            0.46,
            72.0,
            0.036,
        )
    return v6._original_make_material(
        name,
        (0.0015, 0.0018, 0.0022, 1.0),
        0.0,
        0.92,
        185.0,
        0.13,
    )


def extract_crest(obj):
    stats = v6._original_extract_crest(obj)
    # Vista frontal estrecha; el arco se desarrolla en profundidad, como en
    # la referencia. Se rebaja el bloque delantero sin girar la cresta.
    for vertex in obj.data.vertices:
        vertex.co.x = v6.CX + (vertex.co.x - v6.CX) * 0.72
        if vertex.co.y < 0.015:
            vertex.co.z -= 0.008 * min(1.0, (0.015 - vertex.co.y) / 0.035)
    obj.data.update()
    stats["frontWidthCorrection"] = 0.72
    return stats


def main():
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    v6.OUTPUT_ROOT = OUTPUT_ROOT
    v6.OUTPUT_GLB = OUTPUT_GLB
    v6.OUTPUT_BLEND = OUTPUT_BLEND
    v6.REPORT = REPORT
    v6.remove_embedded_original_anatomy = remove_embedded_original_anatomy
    v6.make_material = make_material
    v6.extract_crest = extract_crest
    v6.main()

    report = json.loads(REPORT.read_text(encoding="utf-8"))
    report["stage"] = "aqueo-dark-open-v7"
    report["architecture"] = "open-anatomical-shell-independent-nasal-sagittal-crest"
    report["faceMaterialContract"] = "avatar-skin-only; no helmet polygons over face"
    report["productionIntegrated"] = False
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


if __name__ == "__main__":
    v6._original_make_material = v6.make_material
    v6._original_extract_crest = v6.extract_crest
    main()
