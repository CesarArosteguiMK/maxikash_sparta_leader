"""Construye el Áqueo oscuro v4 como un casco hueco de superficie continua.

La reconstrucción multivista se conserva como exterior escultórico completo.
La cabeza de Leónidas no se elimina cortando vértices visibles: se excava una
cavidad elipsoidal desde el interior y se valida después contra la pose real.
La salida es únicamente para el laboratorio QA.
"""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path

import bmesh
import bpy
from mathutils import Vector

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as preview


ROOT = Path(__file__).resolve().parents[1]
SOURCE = (
    ROOT
    / "storage/leonidas-helmet-reconstruction/hunyuan3d-2mv"
    / "helmet-aqueo-oscuro-hunyuan-mv-v2-high.glb"
)
DOME = ROOT / "public/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb"
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-hollow-v4"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-hollow-v4.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-hollow-v4.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-hollow-v4-report.json"

# Contrato bloqueado de la cúpula aprobada. El exterior v4 utiliza exactamente
# ese ancho; el penacho no participa en el cálculo del ajuste.
CX = -0.01588049
TARGET_OUTER_MIN_X = -0.108488
TARGET_OUTER_MAX_X = 0.076727
TARGET_FRONT_Y = -0.108
TARGET_BACK_Y = 0.155
TARGET_EYE_Z = 1.245
TARGET_TOTAL_HEIGHT = 0.288

# La pose que se muestra en el laboratorio adelanta la cara respecto de REST.
# Este volumen contiene esa pose con holgura y deja pared metálica exterior.
CAVITY_CENTER = Vector((CX, 0.014, 1.190))
CAVITY_RADII = Vector((0.088, 0.104, 0.140))


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def bounds(obj: bpy.types.Object) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
    return (
        Vector(tuple(min(point[axis] for point in corners) for axis in range(3))),
        Vector(tuple(max(point[axis] for point in corners) for axis in range(3))),
    )


def apply_modifier(obj: bpy.types.Object, modifier: bpy.types.Modifier) -> None:
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=modifier.name)
    obj.select_set(False)


def blackened_steel() -> bpy.types.Material:
    mat = bpy.data.materials.new("AqueoV4_BlackenedForgedSteel")
    color = (0.012, 0.019, 0.031, 1.0)
    mat.diffuse_color = color
    mat.metallic = 0.88
    mat.roughness = 0.31
    mat.use_nodes = True
    nodes = mat.node_tree.nodes
    links = mat.node_tree.links
    shader = nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = color
    shader.inputs["Metallic"].default_value = 0.88
    shader.inputs["Roughness"].default_value = 0.31
    noise = nodes.new("ShaderNodeTexNoise")
    noise.inputs["Scale"].default_value = 94.0
    noise.inputs["Detail"].default_value = 4.1
    noise.inputs["Roughness"].default_value = 0.62
    bump = nodes.new("ShaderNodeBump")
    bump.inputs["Strength"].default_value = 0.055
    bump.inputs["Distance"].default_value = 0.00023
    links.new(noise.outputs["Fac"], bump.inputs["Height"])
    links.new(bump.outputs["Normal"], shader.inputs["Normal"])
    return mat


def crest_hair() -> bpy.types.Material:
    mat = bpy.data.materials.new("AqueoV4_DenseBlackHorsehair")
    color = (0.006, 0.008, 0.011, 1.0)
    mat.diffuse_color = color
    mat.metallic = 0.04
    mat.roughness = 0.68
    mat.use_nodes = True
    shader = mat.node_tree.nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = color
    shader.inputs["Metallic"].default_value = 0.04
    shader.inputs["Roughness"].default_value = 0.68
    return mat


def fit_exterior(obj: bpy.types.Object) -> dict[str, float]:
    source_min, source_max = bounds(obj)
    source_extent = source_max - source_min
    source_center = (source_min + source_max) * 0.5
    source_eye_z = source_max.z - source_extent.z * 0.507

    sx = (TARGET_OUTER_MAX_X - TARGET_OUTER_MIN_X) / source_extent.x
    sy = (TARGET_BACK_Y - TARGET_FRONT_Y) / source_extent.y
    sz = TARGET_TOTAL_HEIGHT / source_extent.z
    obj.scale = (sx, sy, sz)
    obj.location.x = CX - source_center.x * sx
    obj.location.y = TARGET_FRONT_Y - source_min.y * sy
    obj.location.z = TARGET_EYE_Z - source_eye_z * sz
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    obj.select_set(False)
    return {"x": sx, "y": sy, "z": sz}


def reduce_for_web(obj: bpy.types.Object) -> dict[str, int]:
    before = len(obj.data.vertices)
    modifier = obj.modifiers.new("PreserveSculptWebReduction", "DECIMATE")
    modifier.decimate_type = "COLLAPSE"
    modifier.ratio = 0.30
    modifier.use_collapse_triangulate = True
    apply_modifier(obj, modifier)
    return {"sourceVertices": before, "reducedVertices": len(obj.data.vertices)}


def make_cavity_cutter() -> bpy.types.Object:
    bpy.ops.mesh.primitive_uv_sphere_add(
        segments=96,
        ring_count=64,
        location=CAVITY_CENTER,
    )
    cutter = bpy.context.object
    cutter.name = "AqueoV4_AnatomicalCavityCutter"
    cutter.scale = CAVITY_RADII
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)

    # La abertura inferior debe atravesar la base para que el resultado sea un
    # casco vestible y no un sólido con una burbuja encerrada.
    bpy.ops.mesh.primitive_cube_add(location=(CX, 0.025, 1.055))
    entry = bpy.context.object
    entry.name = "AqueoV4_LowerEntryCutter"
    entry.dimensions = (0.176, 0.220, 0.170)
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    bevel = entry.modifiers.new("RoundedLowerEntry", "BEVEL")
    bevel.width = 0.018
    bevel.segments = 7
    apply_modifier(entry, bevel)

    boolean = cutter.modifiers.new("JoinLowerEntry", "BOOLEAN")
    boolean.operation = "UNION"
    boolean.solver = "EXACT"
    boolean.object = entry
    apply_modifier(cutter, boolean)
    bpy.data.objects.remove(entry, do_unlink=True)
    return cutter


def excavate_cavity(obj: bpy.types.Object) -> None:
    cutter = make_cavity_cutter()
    boolean = obj.modifiers.new("ExcavateValidatedHeadCavity", "BOOLEAN")
    boolean.operation = "DIFFERENCE"
    boolean.solver = "EXACT"
    boolean.object = cutter
    apply_modifier(obj, boolean)
    bpy.data.objects.remove(cutter, do_unlink=True)


def assign_regions(obj: bpy.types.Object, metal: bpy.types.Material, hair: bpy.types.Material) -> None:
    obj.data.materials.clear()
    obj.data.materials.append(metal)
    obj.data.materials.append(hair)
    for polygon in obj.data.polygons:
        center = polygon.center
        is_upper_crest = center.z > 1.333 and abs(center.x - CX) < 0.072
        is_rear_mane = center.y > 0.122 and abs(center.x - CX) < 0.065 and center.z > 1.205
        polygon.material_index = 1 if (is_upper_crest or is_rear_mane) else 0
        polygon.use_smooth = True


def make_fit_reference() -> bpy.types.Object:
    before = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    imported = [obj for obj in bpy.context.scene.objects if obj not in before and obj.type == "MESH"]
    shell = next(obj for obj in imported if obj.name.startswith("LeonidasHelmetDomeStage01"))
    for obj in imported:
        if obj != shell:
            bpy.data.objects.remove(obj, do_unlink=True)
    shell.name = "Aqueo_FitReference"
    shell["leonidasQaRole"] = "aqueo-fit-reference"
    shell.hide_render = True
    return shell


def mesh_audit(obj: bpy.types.Object) -> dict[str, int]:
    bm = bmesh.new()
    bm.from_mesh(obj.data)
    audit = {
        "vertices": len(bm.verts),
        "faces": len(bm.faces),
        "boundaryEdges": sum(1 for edge in bm.edges if edge.is_boundary),
        "nonManifoldEdges": sum(1 for edge in bm.edges if not edge.is_manifold),
    }
    bm.free()
    return audit


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(SOURCE))
    helmet = max(
        (obj for obj in bpy.context.scene.objects if obj.type == "MESH"),
        key=lambda item: len(item.data.vertices),
    )
    helmet.name = "AqueoDarkV4_ContinuousWearableShell"
    calibration = fit_exterior(helmet)
    reduction = reduce_for_web(helmet)
    excavate_cavity(helmet)
    assign_regions(helmet, blackened_steel(), crest_hair())
    fit_reference = make_fit_reference()

    for obj in (helmet, fit_reference):
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-hollow-v4"
        obj["leonidasProductionReady"] = False
        obj["leonidasHeadBone"] = "mixamorig:Head"

    bpy.ops.object.select_all(action="DESELECT")
    helmet.select_set(True)
    fit_reference.select_set(True)
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
        "stage": "aqueo-dark-hollow-v4",
        "architecture": "continuous-sculpted-exterior-with-boolean-anatomical-cavity",
        "source": str(SOURCE.relative_to(ROOT)).replace("\\", "/"),
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "calibration": calibration,
        "reduction": reduction,
        "cavity": {
            "center": list(CAVITY_CENTER),
            "radii": list(CAVITY_RADII),
            "frontLimit": CAVITY_CENTER.y - CAVITY_RADII.y,
        },
        "boundsWorld": {
            "min": [round(value, 6) for value in minimum],
            "max": [round(value, 6) for value in maximum],
            "extent": [round(value, 6) for value in maximum - minimum],
        },
        "audit": mesh_audit(helmet),
        "productionIntegrated": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    fit_reference.hide_render = True
    preview.OUTPUT_ROOT = OUTPUT_ROOT / "preview"
    preview.REPORT = preview.OUTPUT_ROOT / "preview-report.json"
    preview.OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    preview.render_views(OUTPUT_GLB)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
