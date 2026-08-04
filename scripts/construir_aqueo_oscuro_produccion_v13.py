"""Construye un casco aqueo oscuro cerrado y vestible para Leonidas.

La calota procede de la cupula anatomica validada. La mascara, los bordes y
el penacho se modelan como piezas independientes para conservar huecos reales
en ojos y barba, materiales creibles y una silueta legible desde 360 grados.
"""

from __future__ import annotations

import json
import math
import os
import sys
from pathlib import Path

import bpy

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as stage
import construir_aqueo_oscuro_hueco_v3 as hollow


ROOT = Path(__file__).resolve().parents[1]
DOME = ROOT / "public/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb"
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-production-v13"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-production-v13.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-production-v13.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-production-v13-report.json"

CX = stage.CX


def material(
    name: str,
    color: tuple[float, float, float, float],
    metallic: float,
    roughness: float,
    noise_scale: float = 0.0,
    bump_strength: float = 0.0,
) -> bpy.types.Material:
    result = stage.make_material(
        name,
        color,
        metallic,
        roughness,
        noise_scale=noise_scale,
        bump_strength=bump_strength,
        bump_distance=0.00025,
    )
    return result


def palette() -> dict[str, bpy.types.Material]:
    return {
        "steel": material(
            "AqueoV13_BlackenedForgedSteel",
            (0.010, 0.016, 0.026, 1.0),
            0.84,
            0.31,
            92.0,
            0.055,
        ),
        "steel_soft": material(
            "AqueoV13_BlackSteelRaisedPanel",
            (0.022, 0.031, 0.046, 1.0),
            0.78,
            0.35,
            72.0,
            0.035,
        ),
        "bronze": material(
            "AqueoV13_AgedBronzeTrim",
            (0.155, 0.074, 0.025, 1.0),
            0.78,
            0.34,
            105.0,
            0.035,
        ),
        "hair": material(
            "AqueoV13_BlackHorsehair",
            (0.0015, 0.0020, 0.0030, 1.0),
            0.02,
            0.88,
            185.0,
            0.20,
        ),
    }


def assign(obj: bpy.types.Object, mat: bpy.types.Material, smooth: bool = True) -> None:
    obj.data.materials.clear()
    obj.data.materials.append(mat)
    for polygon in obj.data.polygons:
        polygon.material_index = 0
        polygon.use_smooth = smooth


def face_y(x: float, z: float, offset: float = 0.0) -> float:
    lateral = abs(x - CX) / 0.095
    vertical = max(0.0, min(1.0, (1.26 - z) / 0.17))
    return -0.0440 + lateral * lateral * 0.008 + vertical * 0.0015 + offset


def face_plate(
    name: str,
    outline: list[tuple[float, float]],
    mat: bpy.types.Material,
    thickness: float = 0.0036,
) -> bpy.types.Object:
    count = len(outline)
    front = [(x, face_y(x, z, -0.0015), z) for x, z in outline]
    back = [(x, face_y(x, z, thickness), z) for x, z in outline]
    vertices = front + back
    faces: list[tuple[int, ...]] = [
        tuple(range(count)),
        tuple(range(count, count * 2))[::-1],
    ]
    for index in range(count):
        following = (index + 1) % count
        faces.append((index, following, following + count, index + count))
    mesh = bpy.data.meshes.new(name + "Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update(calc_edges=True)
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.scene.collection.objects.link(obj)
    obj.data.materials.append(mat)
    bevel = obj.modifiers.new("ForgedRoundEdge", "BEVEL")
    bevel.width = 0.0013
    bevel.segments = 3
    bevel.limit_method = "ANGLE"
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=bevel.name)
    obj.select_set(False)
    return obj


def curve_from_xz(
    name: str,
    outline: list[tuple[float, float]],
    radius: float,
    mat: bpy.types.Material,
    *,
    cyclic: bool = False,
    front_offset: float = -0.0033,
) -> bpy.types.Object:
    points = [(x, face_y(x, z, front_offset), z) for x, z in outline]
    return stage.curve_object(name, points, radius, mat, cyclic=cyclic, resolution=3)


def build_mask(mats: dict[str, bpy.types.Material]) -> list[bpy.types.Object]:
    # El frontal, el nasal y las carrilleras son piezas independientes. Asi los
    # ojos y la barba son huecos fisicos, no recortes booleanos fragiles ni una
    # T pintada sobre una placa que tape la cara.
    forehead_outline = [
        (CX - 0.080, 1.265),
        (CX - 0.055, 1.288),
        (CX, 1.302),
        (CX + 0.055, 1.288),
        (CX + 0.080, 1.265),
        (CX + 0.068, 1.239),
        (CX + 0.039, 1.232),
        (CX + 0.016, 1.239),
        (CX, 1.247),
        (CX - 0.016, 1.239),
        (CX - 0.039, 1.232),
        (CX - 0.068, 1.239),
    ]
    forehead = face_plate("AqueoV13_SculptedForehead", forehead_outline, mats["steel_soft"], 0.0045)

    left_cheek_outline = [
        (CX - 0.079, 1.258),
        (CX - 0.094, 1.239),
        (CX - 0.096, 1.198),
        (CX - 0.088, 1.155),
        (CX - 0.069, 1.116),
        (CX - 0.047, 1.100),
        (CX - 0.030, 1.112),
        (CX - 0.026, 1.143),
        (CX - 0.034, 1.176),
        (CX - 0.058, 1.189),
        (CX - 0.066, 1.207),
        (CX - 0.060, 1.229),
    ]
    right_cheek_outline = [(2 * CX - x, z) for x, z in left_cheek_outline][::-1]
    left_cheek = face_plate("AqueoV13_SculptedCheekLeft", left_cheek_outline, mats["steel"], 0.0042)
    right_cheek = face_plate("AqueoV13_SculptedCheekRight", right_cheek_outline, mats["steel"], 0.0042)

    nasal_outline = [
        (CX - 0.0115, 1.251),
        (CX + 0.0115, 1.251),
        (CX + 0.0100, 1.188),
        (CX + 0.0060, 1.172),
        (CX, 1.164),
        (CX - 0.0060, 1.172),
        (CX - 0.0100, 1.188),
    ]
    nasal = face_plate("AqueoV13_SculptedNasal", nasal_outline, mats["steel_soft"], 0.0042)
    for vertex in nasal.data.vertices:
        vertex.co.y -= 0.0032
    nasal.data.update()

    forehead_trim = curve_from_xz("AqueoV13_ForeheadBronzeTrim", forehead_outline, 0.00068, mats["bronze"], cyclic=True)
    cheek_trims = [
        curve_from_xz("AqueoV13_CheekBronzeTrimLeft", left_cheek_outline, 0.00066, mats["bronze"], cyclic=True),
        curve_from_xz("AqueoV13_CheekBronzeTrimRight", right_cheek_outline, 0.00066, mats["bronze"], cyclic=True),
    ]
    nasal_trim = curve_from_xz("AqueoV13_NasalBronzeTrim", nasal_outline, 0.00055, mats["bronze"], cyclic=True, front_offset=-0.0067)
    brow_left = [
        (CX - 0.068, 1.239),
        (CX - 0.047, 1.247),
        (CX - 0.025, 1.242),
        (CX - 0.015, 1.235),
    ]
    brow_right = [(2 * CX - x, z) for x, z in brow_left][::-1]
    brows = [
        curve_from_xz("AqueoV13_BrowRidgeL", brow_left, 0.0020, mats["steel_soft"]),
        curve_from_xz("AqueoV13_BrowRidgeR", brow_right, 0.0020, mats["steel_soft"]),
    ]
    # Borde inferior del ojo: completa la lectura corintia dejando el hueco
    # entero abierto y suficientemente ancho para que nunca invada el globo.
    lower_eye_rims = []
    for side in (-1.0, 1.0):
        points = [
            (CX + side * 0.064, 1.207),
            (CX + side * 0.047, 1.198),
            (CX + side * 0.027, 1.201),
            (CX + side * 0.016, 1.210),
        ]
        lower_eye_rims.append(curve_from_xz(
            f"AqueoV13_LowerEyeRim_{side:+.0f}", points, 0.0014, mats["steel_soft"], front_offset=-0.0045
        ))

    assign(forehead, mats["steel_soft"], smooth=False)
    assign(left_cheek, mats["steel"], smooth=False)
    assign(right_cheek, mats["steel"], smooth=False)
    assign(nasal, mats["steel_soft"], smooth=False)
    return [
        forehead,
        left_cheek,
        right_cheek,
        nasal,
        forehead_trim,
        *cheek_trims,
        nasal_trim,
        *brows,
        *lower_eye_rims,
    ]


def shell_arch_z(y: float) -> float:
    t = max(0.0, min(1.0, (y + 0.028) / 0.215))
    return 1.340 + 0.021 * math.sin(math.pi * t) - 0.026 * t


def crest_top_z(y: float) -> float:
    # Arco alto en el centro y caida suave en ambos extremos, como una crin
    # sagital tensada. La formula evita la antigua placa rectangular.
    t = max(0.0, min(1.0, (y + 0.025) / 0.215))
    return shell_arch_z(y) + 0.025 + 0.098 * math.sin(math.pi * t) ** 0.72


def build_crest(mats: dict[str, bpy.types.Material]) -> list[bpy.types.Object]:
    samples = [-0.025 + 0.215 * index / 18 for index in range(19)]
    bottom = [(y, shell_arch_z(y) + 0.004) for y in samples]
    top = [(y, crest_top_z(y)) for y in reversed(samples)]
    profile = [*bottom, *top]
    half_widths = [0.025] * len(profile)
    plume = stage.profile_slab("AqueoV13_SagittalHorsehairFan", profile, half_widths, mats["hair"])
    assign(plume, mats["hair"], smooth=True)

    rails = []
    base_points = [
        (CX, -0.026 + 0.215 * index / 32, shell_arch_z(-0.026 + 0.215 * index / 32))
        for index in range(33)
    ]
    for lateral in (-0.0145, 0.0145):
        rail_points = [(CX + lateral, y, z) for _, y, z in base_points]
        rails.append(stage.curve_object(
            f"AqueoV13_CrestRail_{lateral:+.4f}", rail_points, 0.0031, mats["steel_soft"], resolution=3
        ))
        rails.append(stage.curve_object(
            f"AqueoV13_CrestBronzeTrim_{lateral:+.4f}",
            [(x, y, z + 0.0032) for x, y, z in rail_points],
            0.00065,
            mats["bronze"],
            resolution=3,
        ))

    # Relieve longitudinal: da lectura de crin sin usar una placa lisa.
    fibre_curves = []
    for index in range(29):
        y = -0.018 + index * 0.0073
        base = shell_arch_z(y) + 0.006
        top = crest_top_z(y) - 0.003
        if top <= base:
            continue
        for side in (-1.0, -0.45, 0.0, 0.45, 1.0):
            fibre_curves.append(stage.curve_object(
                f"AqueoV13_HairRib_{index:02d}_{side:+.0f}",
                [(CX + side * 0.0240, y, base), (CX + side * 0.0244, y, top)],
                0.00034,
                mats["hair"],
                resolution=1,
            ))
    return [plume, *rails, *fibre_curves]


def build_rear_spine(mats: dict[str, bpy.types.Material]) -> list[bpy.types.Object]:
    created = []
    for index in range(6):
        z = 1.300 - index * 0.022
        y = 0.137 + index * 0.002
        bpy.ops.mesh.primitive_uv_sphere_add(segments=20, ring_count=12, location=(CX, y, z))
        center = bpy.context.object
        center.name = f"AqueoV13_RearSpine_{index:02d}"
        center.scale = (0.0095 - index * 0.00045, 0.0042, 0.0058)
        bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
        assign(center, mats["bronze"], smooth=True)
        created.append(center)
        for side in (-1.0, 1.0):
            bpy.ops.mesh.primitive_uv_sphere_add(
                segments=16,
                ring_count=10,
                location=(CX + side * (0.0085 - index * 0.00045), y - 0.0005, z),
            )
            knob = bpy.context.object
            knob.name = f"AqueoV13_RearSpineKnob_{index:02d}_{side:+.0f}"
            knob.scale = (0.0042, 0.0032, 0.0032)
            bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
            assign(knob, mats["bronze"], smooth=True)
            created.append(knob)
    return created


def bounds(objects: list[bpy.types.Object]):
    return stage.object_bounds(objects)


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    stage.clear_scene()
    mats = palette()
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    shell = next(obj for obj in bpy.context.scene.objects if obj.name.startswith("LeonidasHelmetDomeStage01"))
    proxy = next((obj for obj in bpy.context.scene.objects if obj.name.startswith("LeonidasHeadFitProxy")), None)
    if proxy is not None:
        bpy.data.objects.remove(proxy, do_unlink=True)
    shell.name = "AqueoV13_ClosedAnatomicalShell"
    assign(shell, mats["steel_soft"], smooth=True)

    fit_reference = shell.copy()
    fit_reference.data = shell.data.copy()
    fit_reference.name = "Aqueo_FitReference"
    fit_reference.hide_render = True
    fit_reference.hide_viewport = True
    bpy.context.scene.collection.objects.link(fit_reference)

    objects = [shell]
    objects.extend(build_mask(mats))
    objects.extend(build_crest(mats))
    objects.extend(build_rear_spine(mats))
    stage.convert_curves(objects)

    for obj in [*objects, fit_reference]:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-production-v13"
        obj["leonidasProductionReady"] = True
        obj["leonidasHeadBone"] = "mixamorig:Head"

    bpy.ops.object.select_all(action="DESELECT")
    for obj in [*objects, fit_reference]:
        obj.select_set(True)
    bpy.context.view_layer.objects.active = shell
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

    minimum, maximum = bounds(objects)
    report = {
        "stage": "aqueo-dark-production-v13",
        "status": "production-candidate",
        "architecture": [
            "closed-validated-anatomical-shell",
            "continuous-faceguard-with-physical-openings",
            "independent-sculpted-nasal",
            "straight-sagittal-horsehair-fan",
            "aged-bronze-edge-trim",
            "rear-spine-ornament",
            "head-bone-anchor-contract",
        ],
        "objects": [obj.name for obj in objects],
        "bounds": {
            "min": [round(value, 6) for value in minimum],
            "max": [round(value, 6) for value in maximum],
            "extent": [round(value, 6) for value in maximum - minimum],
        },
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "headBone": "mixamorig:Head",
        "defaultVisible": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    stage.OUTPUT_ROOT = OUTPUT_ROOT / "preview"
    stage.REPORT = stage.OUTPUT_ROOT / "preview-report.json"
    stage.OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    stage.render_views(OUTPUT_GLB)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
