"""Ajusta la escultura multivista aprobada a la cabeza real de Leonidas.

Esta version no vuelve a dibujar la mascara ni la crin con primitivas. Conserva
la escultura detallada y corrige el error historico: aplicar una escala uniforme
a una reconstruccion cuyas proporciones fuente no coinciden con el avatar.
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
import construir_aqueo_oscuro_produccion_v13 as v13


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "storage/leonidas-helmet-designs/sculpted/helmet-aqueo-oscuro-sculpted-v7.glb"
DOME = ROOT / "public/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb"
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-production-v14"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-production-v14.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-production-v14.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-production-v14-report.json"

# Dimensiones obtenidas del contrato anatomico y verificadas contra la cupula.
# La profundidad NO puede heredar la escala del ancho: ese era el defecto de
# los candidatos anteriores y producia una boveda enorme y una cara flotante.
TARGET_CENTER_X = -0.01588049
TARGET_CENTER_Y = 0.0150
TARGET_EYE_Z = 1.2250
SCALE_X = 0.2020
SCALE_Y = 0.1450
SCALE_Z = 0.1830
# La escultura contiene muchas islas finas en crin y ornamentos. El decimador
# destructivo abre agujeros; la version maestra conserva el 100 % del detalle.
# La copia web se optimizara despues de la aprobacion visual.
DECIMATE_RATIO = 1.0
WEB_VOXEL_SIZE = 0.00125


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def bounds(objects: list[bpy.types.Object]) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for obj in objects for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[axis] for point in corners) for axis in range(3)))
    maximum = Vector(tuple(max(point[axis] for point in corners) for axis in range(3)))
    return minimum, maximum


def apply_anatomical_fit(objects: list[bpy.types.Object]) -> dict[str, object]:
    source_min, source_max = bounds(objects)
    source_extent = source_max - source_min
    source_center = (source_min + source_max) * 0.5
    source_eye_z = source_max.z - source_extent.z * 0.507

    container = bpy.data.objects.new("AqueoV14_AnatomicalFit", None)
    bpy.context.scene.collection.objects.link(container)
    roots = [obj for obj in objects if obj.parent is None]
    for obj in roots:
        world = obj.matrix_world.copy()
        obj.parent = container
        obj.matrix_world = world
    container.scale = (SCALE_X, SCALE_Y, SCALE_Z)
    container.location = (
        TARGET_CENTER_X - source_center.x * SCALE_X,
        TARGET_CENTER_Y - source_center.y * SCALE_Y,
        TARGET_EYE_Z - source_eye_z * SCALE_Z,
    )
    bpy.context.view_layer.update()

    # Hornea la transformacion por eje en cada malla. El GLB queda en el mismo
    # sistema de coordenadas del avatar y no depende de offsets secretos.
    for obj in objects:
        world = obj.matrix_world.copy()
        obj.parent = None
        obj.matrix_world = world
        bpy.context.view_layer.objects.active = obj
        obj.select_set(True)
        bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
        obj.select_set(False)
    bpy.data.objects.remove(container, do_unlink=True)
    fitted_min, fitted_max = bounds(objects)
    return {
        "sourceBounds": {"min": list(source_min), "max": list(source_max), "extent": list(source_extent)},
        "axisScale": [SCALE_X, SCALE_Y, SCALE_Z],
        "targetCenterXY": [TARGET_CENTER_X, TARGET_CENTER_Y],
        "targetEyeZ": TARGET_EYE_Z,
        "fittedBounds": {"min": list(fitted_min), "max": list(fitted_max), "extent": list(fitted_max - fitted_min)},
    }


def reduce_for_web(meshes: list[bpy.types.Object]) -> list[dict[str, int]]:
    stats = []
    for obj in meshes:
        before = len(obj.data.vertices)
        if before > 100_000:
            bpy.context.view_layer.objects.active = obj
            obj.select_set(True)
            obj.data.remesh_voxel_size = WEB_VOXEL_SIZE
            obj.data.remesh_voxel_adaptivity = 0.015
            bpy.ops.object.voxel_remesh()
            obj.select_set(False)
        for polygon in obj.data.polygons:
            polygon.use_smooth = True
        stats.append({"object": obj.name, "before": before, "after": len(obj.data.vertices)})
    return stats


def import_approved_shell(steel: bpy.types.Material) -> tuple[bpy.types.Object, bpy.types.Object]:
    before = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    imported = [obj for obj in bpy.context.scene.objects if obj not in before and obj.type == "MESH"]
    reference = next(obj for obj in imported if obj.name.startswith("LeonidasHelmetDomeStage01"))
    for obj in imported:
        if obj != reference:
            bpy.data.objects.remove(obj, do_unlink=True)
    reference.name = "Aqueo_FitReference"
    reference.hide_render = True
    reference.hide_viewport = True
    shell = reference.copy()
    shell.data = reference.data.copy()
    shell.name = "AqueoV14_ApprovedClosedShell"
    shell.hide_render = False
    shell.hide_viewport = False
    bpy.context.scene.collection.objects.link(shell)
    shell.data.materials.clear()
    shell.data.materials.append(steel)
    for polygon in shell.data.polygons:
        polygon.material_index = 0
        polygon.use_smooth = True
    return reference, shell


def production_materials() -> tuple[bpy.types.Material, bpy.types.Material]:
    steel = preview.make_material(
        "AqueoV14_OpaqueBlackenedSteel",
        (0.009, 0.015, 0.025, 1.0),
        0.84,
        0.30,
        noise_scale=94.0,
        bump_strength=0.035,
        bump_distance=0.00018,
    )
    bronze = preview.make_material(
        "AqueoV14_AgedBronzeEdge",
        (0.145, 0.065, 0.020, 1.0),
        0.78,
        0.34,
        noise_scale=110.0,
        bump_strength=0.025,
        bump_distance=0.00012,
    )
    steel.surface_render_method = "DITHERED"
    steel.diffuse_color[3] = 1.0
    return steel, bronze


def make_forehead_closure(steel: bpy.types.Material, bronze: bpy.types.Material) -> list[bpy.types.Object]:
    outline = [
        (TARGET_CENTER_X - 0.071, 1.266),
        (TARGET_CENTER_X - 0.054, 1.289),
        (TARGET_CENTER_X, 1.305),
        (TARGET_CENTER_X + 0.054, 1.289),
        (TARGET_CENTER_X + 0.071, 1.266),
        (TARGET_CENTER_X + 0.061, 1.244),
        (TARGET_CENTER_X + 0.038, 1.237),
        (TARGET_CENTER_X + 0.017, 1.239),
        (TARGET_CENTER_X, 1.250),
        (TARGET_CENTER_X - 0.017, 1.239),
        (TARGET_CENTER_X - 0.038, 1.237),
        (TARGET_CENTER_X - 0.061, 1.244),
    ]
    plate = v13.face_plate("AqueoV14_IntegratedForeheadPlate", outline, steel, 0.0045)
    for vertex in plate.data.vertices:
        vertex.co.y -= 0.067
    plate.data.update()
    border = v13.curve_from_xz(
        "AqueoV14_ForeheadBronzeEdge", outline, 0.00062, bronze, cyclic=True, front_offset=-0.0042
    )
    preview.convert_curves([border])
    for vertex in border.data.vertices:
        vertex.co.y -= 0.067
    border.data.update()
    return [plate, border]


def make_anatomical_forehead(steel: bpy.types.Material) -> bpy.types.Object:
    """Frontal curvo en cuadricula, continuo entre calota y borde ocular."""
    rows = 15
    columns = 21
    front: list[tuple[float, float, float]] = []
    back: list[tuple[float, float, float]] = []
    for row in range(rows):
        t = row / (rows - 1)
        # La placa cubre solamente el frontal anatomico. Las versiones previas
        # bajaban hasta el centro de los ojos y se leian como un visor plano.
        half_width = 0.060 - 0.016 * (t ** 1.25)
        center_y = -0.079 + 0.032 * (t ** 1.15)
        for column in range(columns):
            u = -1.0 + 2.0 * column / (columns - 1)
            x = TARGET_CENTER_X + u * half_width
            z = (
                1.243
                + 0.064 * t
                - 0.008 * ((1.0 - t) ** 2) * ((1.0 - abs(u)) ** 1.35)
            )
            # El centro sobresale; los extremos regresan hacia las sienes.
            y = center_y + 0.0115 * (abs(u) ** 1.8)
            front.append((x, y, z))
            back.append((x, y + 0.0038, z))

    vertices = front + back
    layer_size = rows * columns
    faces: list[tuple[int, ...]] = []
    for layer in (0, layer_size):
        for row in range(rows - 1):
            for column in range(columns - 1):
                a = layer + row * columns + column
                b = a + 1
                c = a + columns + 1
                d = a + columns
                faces.append((a, b, c, d) if layer == 0 else (d, c, b, a))
    # Cierra los cuatro cantos para que la placa tenga grosor real.
    boundaries = [
        [column for column in range(columns)],
        [(rows - 1) * columns + column for column in range(columns)],
        [row * columns for row in range(rows)],
        [row * columns + columns - 1 for row in range(rows)],
    ]
    for boundary in boundaries:
        for index in range(len(boundary) - 1):
            a = boundary[index]
            b = boundary[index + 1]
            faces.append((a, a + layer_size, b + layer_size, b))

    mesh = bpy.data.meshes.new("AqueoV14_AnatomicalForeheadMesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update(calc_edges=True)
    obj = bpy.data.objects.new("AqueoV14_AnatomicalForehead", mesh)
    bpy.context.scene.collection.objects.link(obj)
    obj.data.materials.append(steel)
    bevel = obj.modifiers.new("AqueoV14_ForeheadSoftEdge", "BEVEL")
    bevel.width = 0.0011
    bevel.segments = 3
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=bevel.name)
    obj.select_set(False)
    for polygon in obj.data.polygons:
        polygon.use_smooth = True
    return obj


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(SOURCE))
    source_objects = list(bpy.context.scene.objects)
    meshes = [obj for obj in source_objects if obj.type == "MESH"]
    if not meshes:
        raise RuntimeError("La escultura Aqueo v7 no contiene mallas")
    for index, obj in enumerate(meshes):
        obj.name = f"AqueoV14_SculptedHelmet_{index:02d}"
    fit = apply_anatomical_fit(meshes)
    reduction = reduce_for_web(meshes)
    steel, bronze = production_materials()
    for obj in meshes:
        obj.data.materials.clear()
        obj.data.materials.append(steel)
        for polygon in obj.data.polygons:
            polygon.material_index = 0
            polygon.use_smooth = True
    fit_reference, approved_shell = import_approved_shell(steel)
    meshes.append(approved_shell)
    forehead = make_anatomical_forehead(steel)
    meshes.append(forehead)

    for obj in [*meshes, fit_reference]:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-production-v14"
        obj["leonidasProductionReady"] = True
        obj["leonidasHeadBone"] = "mixamorig:Head"
        obj["leonidasAxisCalibrated"] = True

    bpy.ops.object.select_all(action="DESELECT")
    for obj in [*meshes, fit_reference]:
        obj.select_set(True)
    bpy.context.view_layer.objects.active = meshes[0]
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
        "stage": "aqueo-dark-production-v14",
        "status": "production-candidate",
        "source": str(SOURCE.relative_to(ROOT)).replace("\\", "/"),
        "fit": fit,
        "reduction": reduction,
        "architecture": [
            "approved-multiview-sculpture-preserved",
            "independent-width-depth-height-calibration",
            "real-physical-eye-and-beard-openings",
            "continuous-back-and-top-shell",
            "sculpted-sagittal-horsehair",
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
