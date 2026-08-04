"""Construye el Aqueo oscuro v15 con crin sagital y ajuste anatomico real.

Parte de la mascara escultorica multivista, elimina la crin volumetrica que se
veia como un bloque frontal y la sustituye por una cresta independiente: fina
de frente, arqueada de perfil y compuesta por fibras visibles.
"""

from __future__ import annotations

import json
import math
import os
import random
import sys
from pathlib import Path

import bmesh
import bpy
from mathutils import Vector

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as preview
import construir_aqueo_oscuro_produccion_v14 as v14


ROOT = Path(__file__).resolve().parents[1]
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-production-v15"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-production-v15.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-production-v15.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-production-v15-report.json"


def remove_legacy_crest(obj: bpy.types.Object) -> int:
    """Retira la masa superior y la cola trasera del donante reconstruido."""
    mesh = obj.data
    bm = bmesh.new()
    bm.from_mesh(mesh)
    doomed = [
        vertex
        for vertex in bm.verts
        if vertex.co.z > 1.302
        or (vertex.co.y > 0.086 and vertex.co.z > 1.095)
    ]
    removed = len(doomed)
    bmesh.ops.delete(bm, geom=doomed, context="VERTS")
    bm.to_mesh(mesh)
    bm.free()
    mesh.update(calc_edges=True)
    return removed


def poly_curve(
    name: str,
    points: list[tuple[float, float, float]],
    radius: float,
    material: bpy.types.Material,
    resolution: int = 1,
) -> bpy.types.Object:
    data = bpy.data.curves.new(name + "Curve", "CURVE")
    data.dimensions = "3D"
    data.resolution_u = 1
    data.bevel_depth = radius
    data.bevel_resolution = resolution
    data.resolution_u = 1
    spline = data.splines.new("POLY")
    spline.points.add(len(points) - 1)
    for point, coordinate in zip(spline.points, points):
        point.co = (*coordinate, 1.0)
    obj = bpy.data.objects.new(name, data)
    bpy.context.scene.collection.objects.link(obj)
    obj.data.materials.append(material)
    return obj


def plume_outer(y: float) -> float:
    t = max(0.0, min(1.0, (y + 0.060) / 0.205))
    return 1.354 + 0.082 * math.sin(math.pi * (t ** 0.92)) - 0.018 * t


def plume_base(y: float) -> float:
    t = max(0.0, min(1.0, (y + 0.060) / 0.205))
    return 1.302 + 0.015 * math.sin(math.pi * t) - 0.006 * t


def build_plume(
    hair: bpy.types.Material,
    hair_highlight: bpy.types.Material,
    bronze: bpy.types.Material,
) -> list[bpy.types.Object]:
    center_x = v14.TARGET_CENTER_X
    half_width = 0.0135
    side_profile = [
        (-0.060, plume_base(-0.060)),
        (-0.052, plume_outer(-0.052)),
        (-0.025, plume_outer(-0.025)),
        (0.015, plume_outer(0.015)),
        (0.055, plume_outer(0.055)),
        (0.095, plume_outer(0.095)),
        (0.128, plume_outer(0.128)),
        (0.145, plume_outer(0.145)),
        (0.145, plume_base(0.145)),
        (0.095, plume_base(0.095)),
        (0.045, plume_base(0.045)),
        (-0.010, plume_base(-0.010)),
    ]
    vertices = [
        (center_x + side * half_width, y, z)
        for side in (-1.0, 1.0)
        for y, z in side_profile
    ]
    count = len(side_profile)
    faces: list[tuple[int, ...]] = []
    faces.append(tuple(range(count - 1, -1, -1)))
    faces.append(tuple(range(count, count * 2)))
    for index in range(count):
        nxt = (index + 1) % count
        faces.append((index, nxt, count + nxt, count + index))
    mesh = bpy.data.meshes.new("AqueoV15SagittalPlumeCoreMesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update(calc_edges=True)
    core = bpy.data.objects.new("AqueoV15_SagittalPlumeCore", mesh)
    bpy.context.scene.collection.objects.link(core)
    core.data.materials.append(hair)
    bevel = core.modifiers.new("AqueoV15_PlumeSoftVolume", "BEVEL")
    bevel.width = 0.0028
    bevel.segments = 4
    bpy.context.view_layer.objects.active = core
    core.select_set(True)
    bpy.ops.object.modifier_apply(modifier=bevel.name)
    core.select_set(False)
    for polygon in core.data.polygons:
        polygon.use_smooth = True

    curves: list[bpy.types.Object] = []
    rng = random.Random(1507)
    # Fibras visibles en ambos costados. Siguen la direccion radial del penacho
    # y rompen el aspecto de bloque sin inflar el ancho frontal.
    for side in (-1.0, 1.0):
        x = center_x + side * (half_width + 0.0013)
        for index in range(72):
            t = index / 71
            y = -0.054 + 0.194 * t
            bottom = plume_base(y) + rng.uniform(0.001, 0.006)
            top = plume_outer(y) - rng.uniform(0.001, 0.008)
            bend = rng.uniform(-0.0022, 0.0022)
            curves.append(poly_curve(
                f"AqueoV15_SideFiber_{'L' if side < 0 else 'R'}_{index:02d}",
                [
                    (x, y - bend, bottom),
                    (x + side * rng.uniform(-0.0005, 0.0007), y, (bottom + top) * 0.5),
                    (x, y + bend, top),
                ],
                rng.uniform(0.00030, 0.00052),
                hair_highlight if index % 5 == 0 else hair,
            ))

    # El canto frontal es lo unico que debe verse desde el frente: estrecho,
    # vertical y compuesto por mechones, nunca una placa rectangular ancha.
    for index in range(26):
        t = index / 25
        x = center_x - half_width * 0.88 + half_width * 1.76 * t
        z0 = 1.307 + 0.004 * math.sin(math.pi * t)
        z1 = 1.356 + 0.010 * math.sin(math.pi * t)
        curves.append(poly_curve(
            f"AqueoV15_FrontFiber_{index:02d}",
            [(x, -0.0633, z0), (x, -0.0640, (z0 + z1) * 0.5), (x, -0.0628, z1)],
            0.00038,
            hair_highlight if index % 4 == 0 else hair,
        ))

    rail_points = [(center_x - half_width - 0.002, y, plume_base(y) - 0.001) for y in (-0.055, -0.02, 0.025, 0.07, 0.115, 0.142)]
    curves.append(poly_curve("AqueoV15_LeftBronzePlumeRail", rail_points, 0.00115, bronze, 2))
    rail_points = [(center_x + half_width + 0.002, y, plume_base(y) - 0.001) for y in (-0.055, -0.02, 0.025, 0.07, 0.115, 0.142)]
    curves.append(poly_curve("AqueoV15_RightBronzePlumeRail", rail_points, 0.00115, bronze, 2))

    bpy.ops.object.select_all(action="DESELECT")
    for curve in curves:
        curve.select_set(True)
    bpy.context.view_layer.objects.active = curves[0]
    bpy.ops.object.convert(target="MESH")
    converted = [obj for obj in bpy.context.selected_objects if obj.type == "MESH"]
    return [core, *converted]


def build_brow_trim(bronze: bpy.types.Material) -> list[bpy.types.Object]:
    cx = v14.TARGET_CENTER_X
    curves = [
        poly_curve(
            "AqueoV15_BrowTrim",
            [
                (cx - 0.063, -0.088, 1.243),
                (cx - 0.032, -0.094, 1.246),
                (cx, -0.098, 1.256),
                (cx + 0.032, -0.094, 1.246),
                (cx + 0.063, -0.088, 1.243),
            ],
            0.00078,
            bronze,
            2,
        ),
        poly_curve(
            "AqueoV15_NasalTrim",
            [(cx, -0.099, 1.255), (cx, -0.108, 1.220), (cx, -0.112, 1.177)],
            0.00072,
            bronze,
            2,
        ),
    ]
    bpy.ops.object.select_all(action="DESELECT")
    for curve in curves:
        curve.select_set(True)
    bpy.context.view_layer.objects.active = curves[0]
    bpy.ops.object.convert(target="MESH")
    return [obj for obj in bpy.context.selected_objects if obj.type == "MESH"]


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    v14.clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(v14.SOURCE))
    meshes = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
    if not meshes:
        raise RuntimeError("La escultura Aqueo v7 no contiene mallas")
    for index, obj in enumerate(meshes):
        obj.name = f"AqueoV15_SculptedFaceguard_{index:02d}"
    fit = v14.apply_anatomical_fit(meshes)
    reduction = v14.reduce_for_web(meshes)
    removed_vertices = sum(remove_legacy_crest(obj) for obj in meshes)

    steel, bronze = v14.production_materials()
    hair = preview.make_material(
        "AqueoV15_HorsehairBlack",
        (0.004, 0.006, 0.009, 1.0),
        0.05,
        0.88,
        noise_scale=125.0,
        bump_strength=0.065,
        bump_distance=0.00022,
    )
    hair_highlight = preview.make_material(
        "AqueoV15_HorsehairHighlight",
        (0.016, 0.020, 0.026, 1.0),
        0.08,
        0.72,
        noise_scale=150.0,
        bump_strength=0.05,
        bump_distance=0.00018,
    )
    for obj in meshes:
        obj.data.materials.clear()
        obj.data.materials.append(steel)
        for polygon in obj.data.polygons:
            polygon.material_index = 0
            polygon.use_smooth = True

    fit_reference, shell = v14.import_approved_shell(steel)
    forehead = v14.make_anatomical_forehead(steel)
    plume = build_plume(hair, hair_highlight, bronze)
    trim = build_brow_trim(bronze)
    export_objects = [*meshes, shell, forehead, *plume, *trim]

    for obj in [*export_objects, fit_reference]:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-production-v15"
        obj["leonidasProductionReady"] = True
        obj["leonidasHeadBone"] = "mixamorig:Head"
        obj["leonidasAxisCalibrated"] = True

    bpy.ops.object.select_all(action="DESELECT")
    for obj in [*export_objects, fit_reference]:
        obj.select_set(True)
    bpy.context.view_layer.objects.active = export_objects[0]
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
        "stage": "aqueo-dark-production-v15",
        "status": "production-candidate",
        "source": str(v14.SOURCE.relative_to(ROOT)).replace("\\", "/"),
        "fit": fit,
        "reduction": reduction,
        "removedLegacyCrestVertices": removed_vertices,
        "architecture": [
            "approved-closed-anatomical-shell",
            "sculpted-open-faceguard",
            "independent-sagittal-horsehair-plume",
            "narrow-front-wide-profile-crest",
            "longitudinal-visible-hair-fibers",
            "bronze-brow-and-plume-trim",
            "head-bone-anchor-contract",
        ],
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "defaultVisible": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    preview.OUTPUT_ROOT = OUTPUT_ROOT / "preview"
    preview.REPORT = preview.OUTPUT_ROOT / "preview-report.json"
    preview.OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    preview.render_views(OUTPUT_GLB)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
