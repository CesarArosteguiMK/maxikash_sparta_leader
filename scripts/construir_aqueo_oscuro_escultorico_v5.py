"""Compone el Áqueo oscuro v5 sin destruir su superficie visible.

Arquitectura:
* cúpula hueca y validada de la etapa 01;
* máscara facial extraída de la reconstrucción multivista y desplazada sólo
  donde la pose real necesita holgura;
* cresta sagital escultórica independiente.

La salida permanece aislada en el laboratorio QA.
"""

from __future__ import annotations

import json
import math
import os
import sys
from pathlib import Path

import bmesh
import bpy
from mathutils import Vector

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as preview
import construir_aqueo_oscuro_hueco_v3 as v3


ROOT = Path(__file__).resolve().parents[1]
SOURCE = (
    ROOT
    / "storage/leonidas-helmet-reconstruction/hunyuan3d-2mv"
    / "helmet-aqueo-oscuro-hunyuan-mv-v2-high.glb"
)
DOME = ROOT / "public/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb"
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-sculptural-v5"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-sculptural-v5.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-sculptural-v5.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-sculptural-v5-report.json"

CX = -0.01588049
TARGET_MIN_X = -0.108488
TARGET_MAX_X = 0.076727
TARGET_FRONT_Y = -0.108
TARGET_BACK_Y = 0.155
TARGET_EYE_Z = 1.245
TARGET_TOTAL_HEIGHT = 0.288


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


def fit_donor(obj: bpy.types.Object) -> dict[str, float]:
    minimum, maximum = bounds(obj)
    extent = maximum - minimum
    center = (minimum + maximum) * 0.5
    source_eye_z = maximum.z - extent.z * 0.507
    sx = (TARGET_MAX_X - TARGET_MIN_X) / extent.x
    sy = (TARGET_BACK_Y - TARGET_FRONT_Y) / extent.y
    sz = TARGET_TOTAL_HEIGHT / extent.z
    obj.scale = (sx, sy, sz)
    obj.location.x = CX - center.x * sx
    obj.location.y = TARGET_FRONT_Y - minimum.y * sy
    obj.location.z = TARGET_EYE_Z - source_eye_z * sz
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    obj.select_set(False)
    return {"x": sx, "y": sy, "z": sz}


def reduce_source(obj: bpy.types.Object) -> dict[str, int]:
    before = len(obj.data.vertices)
    modifier = obj.modifiers.new("SculpturalDetailReduction", "DECIMATE")
    modifier.decimate_type = "COLLAPSE"
    modifier.ratio = 0.36
    modifier.use_collapse_triangulate = True
    apply_modifier(obj, modifier)
    return {"sourceVertices": before, "reducedVertices": len(obj.data.vertices)}


def keep_vertices(obj: bpy.types.Object, predicate) -> None:
    bm = bmesh.new()
    bm.from_mesh(obj.data)
    remove = [vertex for vertex in bm.verts if not predicate(vertex.co)]
    bmesh.ops.delete(bm, geom=remove, context="VERTS")
    loose = [vertex for vertex in bm.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(bm, geom=loose, context="VERTS")
    if bm.faces:
        bmesh.ops.recalc_face_normals(bm, faces=list(bm.faces))
    bm.to_mesh(obj.data)
    bm.free()
    obj.data.update()


def close_patch(obj: bpy.types.Object, thickness: float) -> None:
    solidify = obj.modifiers.new("WearablePlateThickness", "SOLIDIFY")
    solidify.thickness = thickness
    solidify.offset = -0.25
    solidify.use_rim = True
    apply_modifier(obj, solidify)
    bevel = obj.modifiers.new("ForgedSoftEdges", "BEVEL")
    bevel.width = 0.00075
    bevel.segments = 3
    bevel.limit_method = "ANGLE"
    apply_modifier(obj, bevel)


def extract_mask(source: bpy.types.Object) -> bpy.types.Object:
    mask = source.copy()
    mask.data = source.data.copy()
    mask.name = "AqueoDarkV5_SculptedAnatomicalMask"
    bpy.context.scene.collection.objects.link(mask)

    def keep(co: Vector) -> bool:
        side = abs(co.x - CX)
        # Sólo se conserva la piel exterior frontal. El volumen posterior del
        # donante es macizo y, si se incluye, forma una placa que tapa la cara.
        frontal = co.y < -0.067 and 1.120 < co.z < 1.322
        temple = side > 0.066 and co.y < -0.038 and 1.145 < co.z < 1.310
        return frontal or temple

    keep_vertices(mask, keep)
    # La máscara se adelanta como una pieza completa. No se aplana ni se
    # colapsa contra un plano: conserva la curvatura escultórica del donante.
    for vertex in mask.data.vertices:
        vertex.co.y -= 0.015
    mask.data.update()
    close_patch(mask, 0.0026)

    # Dos visores separados y una abertura inferior generan la T sin cortar el
    # puente nasal. Los bordes redondeados evitan el aspecto de recorte Paint.
    cutters: list[bpy.types.Object] = []
    for side in (-1.0, 1.0):
        cutter = v3.rounded_cube(
            f"AqueoDarkV5_EyeOpening_{side:+.0f}",
            (CX + side * 0.037, -0.096, 1.243),
            (0.056, 0.080, 0.022),
            bevel=0.0065,
            rotation_y=side * math.radians(7.0),
        )
        cutters.append(cutter)
    cutters.append(
        v3.rounded_cube(
            "AqueoDarkV5_LowerTOpening",
            (CX, -0.096, 1.181),
            (0.047, 0.080, 0.094),
            bevel=0.0075,
        )
    )
    for index, cutter in enumerate(cutters):
        v3.boolean_difference(mask, cutter, f"CleanTOpening{index + 1}")
    return mask


def make_nasal(steel: bpy.types.Material) -> bpy.types.Object:
    outline = [
        (CX - 0.0105, 1.252),
        (CX + 0.0105, 1.252),
        (CX + 0.0080, 1.190),
        (CX, 1.174),
        (CX - 0.0080, 1.190),
    ]
    nasal = v3.curved_plate("AqueoDarkV5_IntegratedNasal", outline, steel)
    for vertex in nasal.data.vertices:
        vertex.co.y -= 0.006
    nasal.data.update()
    return nasal


def extract_crest(source: bpy.types.Object) -> bpy.types.Object:
    crest = source.copy()
    crest.data = source.data.copy()
    crest.name = "AqueoDarkV5_DenseSagittalCrest"
    bpy.context.scene.collection.objects.link(crest)

    def keep(co: Vector) -> bool:
        upper_arch = co.z > 1.326 and abs(co.x - CX) < 0.074
        rear_tail = co.y > 0.106 and abs(co.x - CX) < 0.068 and co.z > 1.178
        return upper_arch or rear_tail

    keep_vertices(crest, keep)
    close_patch(crest, 0.0018)
    return crest


def material(
    name: str,
    color: tuple[float, float, float, float],
    metallic: float,
    roughness: float,
    *,
    noise_scale: float,
    bump_strength: float,
) -> bpy.types.Material:
    mat = bpy.data.materials.new(name)
    mat.diffuse_color = color
    mat.metallic = metallic
    mat.roughness = roughness
    mat.use_nodes = True
    nodes = mat.node_tree.nodes
    links = mat.node_tree.links
    shader = nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = color
    shader.inputs["Metallic"].default_value = metallic
    shader.inputs["Roughness"].default_value = roughness
    noise = nodes.new("ShaderNodeTexNoise")
    noise.inputs["Scale"].default_value = noise_scale
    noise.inputs["Detail"].default_value = 3.5
    noise.inputs["Roughness"].default_value = 0.60
    bump = nodes.new("ShaderNodeBump")
    bump.inputs["Strength"].default_value = bump_strength
    bump.inputs["Distance"].default_value = 0.00022
    links.new(noise.outputs["Fac"], bump.inputs["Height"])
    links.new(bump.outputs["Normal"], shader.inputs["Normal"])
    return mat


def assign_material(obj: bpy.types.Object, mat: bpy.types.Material) -> None:
    obj.data.materials.clear()
    obj.data.materials.append(mat)
    for polygon in obj.data.polygons:
        polygon.material_index = 0
        polygon.use_smooth = True


def mesh_audit(obj: bpy.types.Object) -> dict[str, int]:
    bm = bmesh.new()
    bm.from_mesh(obj.data)
    report = {
        "vertices": len(bm.verts),
        "faces": len(bm.faces),
        "boundaryEdges": sum(1 for edge in bm.edges if edge.is_boundary),
        "nonManifoldEdges": sum(1 for edge in bm.edges if not edge.is_manifold),
    }
    bm.free()
    return report


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(SOURCE))
    source = max(
        (obj for obj in bpy.context.scene.objects if obj.type == "MESH"),
        key=lambda item: len(item.data.vertices),
    )
    calibration = fit_donor(source)
    reduction = reduce_source(source)
    mask = extract_mask(source)
    crest = extract_crest(source)
    bpy.data.objects.remove(source, do_unlink=True)

    before = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    imported = [obj for obj in bpy.context.scene.objects if obj not in before and obj.type == "MESH"]
    shell = next(obj for obj in imported if obj.name.startswith("LeonidasHelmetDomeStage01"))
    for obj in imported:
        if obj != shell:
            bpy.data.objects.remove(obj, do_unlink=True)
    shell.name = "AqueoDarkV5_ValidatedHollowDome"
    fit_reference = v3.make_fit_reference(shell)
    v3.expand_dome_for_render_pose(shell)
    v3.enlarge_dome_face_opening(shell)

    steel = material(
        "AqueoV5_BlackenedForgedSteel",
        (0.011, 0.018, 0.030, 1.0),
        0.88,
        0.31,
        noise_scale=105.0,
        bump_strength=0.052,
    )
    hair = material(
        "AqueoV5_DenseBlackHorsehair",
        (0.006, 0.007, 0.010, 1.0),
        0.03,
        0.70,
        noise_scale=145.0,
        bump_strength=0.12,
    )
    assign_material(shell, steel)
    assign_material(mask, steel)
    assign_material(crest, hair)
    nasal = make_nasal(steel)

    objects = [shell, mask, nasal, crest, fit_reference]
    for obj in objects:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-sculptural-v5"
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
        "stage": "aqueo-dark-sculptural-v5",
        "architecture": [
            "validated-hollow-dome",
            "sculpted-anatomical-mask",
            "independent-dense-sagittal-crest",
        ],
        "calibration": calibration,
        "reduction": reduction,
        "audits": {obj.name: mesh_audit(obj) for obj in (shell, mask, nasal, crest)},
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
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
