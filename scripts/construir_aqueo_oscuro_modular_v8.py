"""Áqueo oscuro modular: cúpula limpia, rostro libre y crin sagital real.

No reutiliza la máscara histórica. Cada pieza frontal es una placa hueca
independiente situada delante de la anatomía; por ello la cara conserva
siempre el material de piel de Leónidas.
"""

from __future__ import annotations

import json
import math
import os
import random
import sys
from pathlib import Path

import bpy
from mathutils import Vector

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as preview
import construir_aqueo_oscuro_hueco_v3 as v3
import construir_aqueo_oscuro_original_v6 as v6


ROOT = Path(__file__).resolve().parents[1]
DOME = ROOT / "public/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb"
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-modular-v8"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-modular-v8.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-modular-v8.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-modular-v8-report.json"
CX = v3.CX


def clear_scene():
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def aged_steel():
    return v6.make_material(
        "AqueoV8_AgedBlackSteel",
        (0.012, 0.016, 0.023, 1.0),
        0.80,
        0.44,
        78.0,
        0.032,
    )


def horsehair():
    return v6.make_material(
        "AqueoV8_BlackHorsehair",
        (0.0012, 0.0015, 0.0019, 1.0),
        0.0,
        0.93,
        210.0,
        0.08,
    )


def assign(obj, material):
    obj.data.materials.clear()
    obj.data.materials.append(material)
    for polygon in obj.data.polygons:
        polygon.material_index = 0
        polygon.use_smooth = True


def mirror_outline(points):
    return [(2.0 * CX - x, z) for x, z in points]


def create_face_armor(steel):
    """Placas con hueco facial real; no existe una careta pintada."""
    parts = []

    # Nasal forjado, angosto y separado de la piel.
    nasal = v3.curved_plate(
        "AqueoDarkV8_ForgedNasal",
        [
            (CX - 0.0105, 1.276),
            (CX + 0.0105, 1.276),
            (CX + 0.0080, 1.196),
            (CX, 1.174),
            (CX - 0.0080, 1.196),
        ],
        steel,
    )
    parts.append(nasal)

    # Alas sobre las cejas. Dejan ojos, nariz, boca y barba descubiertos.
    left_brow = [
        (CX - 0.011, 1.263),
        (CX - 0.057, 1.258),
        (CX - 0.076, 1.241),
        (CX - 0.064, 1.230),
        (CX - 0.026, 1.239),
        (CX - 0.010, 1.247),
    ]
    parts.append(v3.curved_plate("AqueoDarkV8_LeftBrowWing", left_brow, steel))
    parts.append(
        v3.curved_plate("AqueoDarkV8_RightBrowWing", mirror_outline(left_brow), steel)
    )

    # Carrilleras laterales afinadas; el borde interior acompaña la barba.
    left_cheek = [
        (CX - 0.081, 1.247),
        (CX - 0.066, 1.228),
        (CX - 0.060, 1.197),
        (CX - 0.047, 1.153),
        (CX - 0.058, 1.128),
        (CX - 0.078, 1.145),
        (CX - 0.089, 1.196),
        (CX - 0.091, 1.230),
    ]
    parts.append(v3.curved_plate("AqueoDarkV8_LeftCheekGuard", left_cheek, steel))
    parts.append(
        v3.curved_plate("AqueoDarkV8_RightCheekGuard", mirror_outline(left_cheek), steel)
    )

    # Puentes de sien dan continuidad hacia la cúpula sin cubrir la cara.
    parts.append(v3.side_bridge("AqueoDarkV8_LeftTemple", -1.0, steel))
    parts.append(v3.side_bridge("AqueoDarkV8_RightTemple", 1.0, steel))
    return parts


def create_horsehair_crest(hair):
    """Crin sagital hecha con fibras; estrecha al frente y arqueada de perfil."""
    rng = random.Random(7301)
    strands = []
    rows = 38
    lanes = 7
    for row in range(rows):
        u = row / (rows - 1)
        base_y = -0.028 + 0.214 * u
        base_z = 1.332 + 0.013 * math.sin(math.pi * u) - 0.010 * u
        silhouette = 0.102 + 0.028 * math.sin(math.pi * u) - 0.026 * u
        for lane in range(lanes):
            lateral = (lane - (lanes - 1) * 0.5) * 0.0044
            lateral += rng.uniform(-0.0012, 0.0012)
            height = silhouette * rng.uniform(0.91, 1.05)
            backward = 0.010 + 0.030 * u + rng.uniform(-0.004, 0.004)
            points = []
            for index in range(6):
                t = index / 5.0
                bend = t * t
                points.append(
                    (
                        CX + lateral + rng.uniform(-0.00045, 0.00045) * t,
                        base_y + backward * bend,
                        base_z + height * t - 0.006 * bend,
                    )
                )
            strand = preview.curve_object(
                f"AqueoDarkV8_Hair_{row:02d}_{lane:02d}",
                points,
                0.00070,
                hair,
                resolution=2,
            )
            strands.append(strand)
    preview.convert_curves(strands)
    bpy.ops.object.select_all(action="DESELECT")
    for strand in strands:
        strand.select_set(True)
    bpy.context.view_layer.objects.active = strands[0]
    bpy.ops.object.join()
    crest = bpy.context.object
    crest.name = "AqueoDarkV8_DenseSagittalHorsehair"
    return crest


def create_crest_rails(steel):
    rails = []
    for lateral in (-0.019, 0.019):
        points = []
        for index in range(48):
            u = index / 47.0
            y = -0.035 + 0.225 * u
            z = 1.330 + 0.015 * math.sin(math.pi * u) - 0.010 * u
            points.append((CX + lateral, y, z))
        rails.append(
            preview.curve_object(
                f"AqueoDarkV8_CrestRail_{lateral:+.3f}",
                points,
                0.0026,
                steel,
                resolution=3,
            )
        )
    preview.convert_curves(rails)
    return rails


def audit(obj):
    return {
        "vertices": len(obj.data.vertices),
        "faces": len(obj.data.polygons),
    }


def main():
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    shell = next(obj for obj in bpy.context.scene.objects if obj.name.startswith("LeonidasHelmetDomeStage01"))
    proxy = next((obj for obj in bpy.context.scene.objects if obj.name.startswith("LeonidasHeadFitProxy")), None)
    if proxy:
        bpy.data.objects.remove(proxy, do_unlink=True)
    shell.name = "AqueoDarkV8_ValidatedOpenDome"
    v3.expand_dome_for_render_pose(shell)

    fit_reference = shell.copy()
    fit_reference.data = shell.data.copy()
    fit_reference.name = "Aqueo_FitReference"
    fit_reference.hide_render = True
    bpy.context.scene.collection.objects.link(fit_reference)

    steel = aged_steel()
    hair = horsehair()
    assign(shell, steel)
    face_parts = create_face_armor(steel)
    crest = create_horsehair_crest(hair)
    rails = create_crest_rails(steel)
    objects = [shell, *face_parts, crest, *rails, fit_reference]
    for obj in objects:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-modular-v8"
        obj["leonidasProductionReady"] = False
        obj["leonidasHeadBone"] = "mixamorig:Head"

    bpy.ops.object.select_all(action="DESELECT")
    for obj in objects:
        obj.select_set(True)
    bpy.ops.export_scene.gltf(
        filepath=os.fspath(OUTPUT_GLB),
        export_format="GLB",
        use_selection=True,
        export_animations=False,
        export_skins=False,
        export_morph=False,
        export_extras=True,
        export_yup=True,
    )
    bpy.ops.wm.save_as_mainfile(filepath=os.fspath(OUTPUT_BLEND))

    report = {
        "status": "qa-candidate-not-production",
        "stage": "aqueo-dark-modular-v8",
        "architecture": "validated-open-dome-independent-face-armor-fiber-crest",
        "faceMaterialContract": "avatar skin is never part of helmet geometry",
        "defaultVisible": False,
        "audits": {obj.name: audit(obj) for obj in [shell, *face_parts, crest, *rails]},
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "productionIntegrated": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    preview.OUTPUT_ROOT = OUTPUT_ROOT / "preview"
    preview.REPORT = preview.OUTPUT_ROOT / "preview-report.json"
    preview.OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    preview.render_views(OUTPUT_GLB)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
