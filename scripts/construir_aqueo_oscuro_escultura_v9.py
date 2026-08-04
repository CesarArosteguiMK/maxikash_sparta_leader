"""Convierte la escultura multivista del Áqueo en un casco vestible.

La forma exterior procede de las cuatro vistas aprobadas. Se calibra por eje,
se excava con la cabeza real de Leónidas y se abren ojos y boca físicamente;
la piel nunca recibe el material del casco.
"""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path

import bpy
from mathutils import Vector

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as preview
import construir_aqueo_oscuro_hueco_v3 as v3
import construir_aqueo_oscuro_original_v6 as v6


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "storage/leonidas-helmet-reconstruction/hunyuan3d-2mv/helmet-aqueo-oscuro-hunyuan-mv-v2-high.glb"
DOME = ROOT / "public/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb"
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-sculpture-v9"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-sculpture-v9.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-sculpture-v9.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-sculpture-v9-report.json"

HEAD_CX = (-0.08132071793079376 + 0.07200704514980316) * 0.5


def clear_scene():
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def bounds(obj):
    corners = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
    return (
        Vector(tuple(min(point[axis] for point in corners) for axis in range(3))),
        Vector(tuple(max(point[axis] for point in corners) for axis in range(3))),
    )


def calibrate(obj):
    minimum, maximum = bounds(obj)
    extent = maximum - minimum
    center = (minimum + maximum) * 0.5
    source_eye_z = maximum.z - extent.z * 0.507
    scale = Vector((0.200, 0.145, 0.150))
    obj.scale = scale
    obj.location.x = HEAD_CX - center.x * scale.x
    obj.location.y = -0.096 - minimum.y * scale.y
    obj.location.z = 1.237 - source_eye_z * scale.z
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    obj.select_set(False)
    return {
        "scale": list(scale),
        "targetHeadCenterX": HEAD_CX,
        "targetEyeZ": 1.237,
        "targetFrontY": -0.096,
    }


def cutter(name, location, dimensions, bevel):
    return v3.rounded_cube(name, location, dimensions, bevel=bevel)


def open_face(obj):
    openings = []
    eye_z = 1.236
    for side in (-1.0, 1.0):
        eye = cutter(
            f"AqueoV9_EyeOpening_{side:+.0f}",
            (HEAD_CX + side * 0.035, -0.064, eye_z),
            (0.052, 0.120, 0.026),
            0.008,
        )
        v3.boolean_difference(obj, eye, f"OpenEye_{side:+.0f}")
        openings.append({"kind": "eye", "side": side})

    mouth = cutter(
        "AqueoV9_MouthAndBeardOpening",
        (HEAD_CX, -0.062, 1.151),
        (0.057, 0.125, 0.090),
        0.013,
    )
    v3.boolean_difference(obj, mouth, "OpenMouthAndBeard")
    openings.append({"kind": "mouth-and-beard"})
    return openings


def materials():
    steel = v6.make_material(
        "AqueoV9_AgedBlackSteel",
        (0.010, 0.014, 0.021, 1.0),
        0.82,
        0.43,
        74.0,
        0.028,
    )
    hair = v6.make_material(
        "AqueoV9_BlackHorsehair",
        (0.0012, 0.0015, 0.0019, 1.0),
        0.0,
        0.92,
        185.0,
        0.10,
    )
    return steel, hair


def assign_by_region(obj, steel, hair):
    obj.data.materials.clear()
    obj.data.materials.append(steel)
    obj.data.materials.append(hair)
    for polygon in obj.data.polygons:
        center = polygon.center
        # La crin ocupa la región alta y posterior de la escultura.
        polygon.material_index = 1 if center.z > 1.315 and center.y > -0.030 else 0
        polygon.use_smooth = True


def main():
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(SOURCE))
    helmet = max(
        (obj for obj in bpy.context.scene.objects if obj.type == "MESH"),
        key=lambda item: len(item.data.vertices),
    )
    helmet.name = "AqueoDarkV9_WearableMultiviewSculpture"
    fit = calibrate(helmet)
    cavity = v6.carve_render_pose_cavity(helmet)
    openings = open_face(helmet)
    steel, hair = materials()
    assign_by_region(helmet, steel, hair)

    before = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    imported = [obj for obj in bpy.context.scene.objects if obj not in before and obj.type == "MESH"]
    fit_reference = next(obj for obj in imported if obj.name.startswith("LeonidasHelmetDomeStage01"))
    for obj in imported:
        if obj != fit_reference:
            bpy.data.objects.remove(obj, do_unlink=True)
    fit_reference.name = "Aqueo_FitReference"
    fit_reference.hide_render = True

    objects = [helmet, fit_reference]
    for obj in objects:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-sculpture-v9"
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
    minimum, maximum = bounds(helmet)
    report = {
        "status": "qa-candidate-not-production",
        "stage": "aqueo-dark-sculpture-v9",
        "architecture": "multiview-sculpture-anatomical-cavity-physical-face-openings",
        "fit": fit,
        "cavity": cavity,
        "openings": openings,
        "bounds": {"min": list(minimum), "max": list(maximum), "extent": list(maximum - minimum)},
        "faceMaterialContract": "openings reveal LeonidasHeadUnderlay; helmet has no skin material",
        "defaultVisible": False,
        "productionIntegrated": False,
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    preview.OUTPUT_ROOT = OUTPUT_ROOT / "preview"
    preview.REPORT = preview.OUTPUT_ROOT / "preview-report.json"
    preview.OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    preview.render_views(OUTPUT_GLB)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
