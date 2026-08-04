"""Construye un Áqueo oscuro coherente desde el casco original de Leónidas.

La carcasa original aporta la máscara y las carrilleras que ya fueron creadas
para su anatomía. Sólo se corrige su holgura para la pose del laboratorio, se
aplica acero ennegrecido y se añade una cresta sagital independiente.
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
ORIGINAL = ROOT / "storage/leonidas-helmet-reconstruction/original-base/leonidas-original-helmet-base.glb"
CREST_SOURCE = (
    ROOT
    / "storage/leonidas-helmet-reconstruction/hunyuan3d-2mv"
    / "helmet-aqueo-oscuro-hunyuan-mv-v2-high.glb"
)
DOME = ROOT / "public/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb"
LEONIDAS = ROOT / "public/assets/models/leonidas/leonidas-spartan-modular-v2.glb"
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-original-v6"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-original-v6.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-original-v6.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-original-v6-report.json"

CX = -0.01588049


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


def make_material(
    name: str,
    color: tuple[float, float, float, float],
    metallic: float,
    roughness: float,
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
    noise.inputs["Detail"].default_value = 3.4
    noise.inputs["Roughness"].default_value = 0.61
    bump = nodes.new("ShaderNodeBump")
    bump.inputs["Strength"].default_value = bump_strength
    bump.inputs["Distance"].default_value = 0.00020
    links.new(noise.outputs["Fac"], bump.inputs["Height"])
    links.new(bump.outputs["Normal"], shader.inputs["Normal"])
    return mat


def assign_material(obj: bpy.types.Object, mat: bpy.types.Material) -> None:
    obj.data.materials.clear()
    obj.data.materials.append(mat)
    for polygon in obj.data.polygons:
        polygon.material_index = 0
        polygon.use_smooth = True


def fit_original_shell(shell: bpy.types.Object) -> dict[str, object]:
    # El GLB original conserva una corrección Y-up en su jerarquía. Se hornea
    # la matriz mundial antes de editar vértices para no mezclar coordenadas
    # locales con las medidas anatómicas mundiales.
    world = shell.matrix_world.copy()
    shell.parent = None
    shell.matrix_world = world
    bpy.context.view_layer.objects.active = shell
    shell.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    shell.select_set(False)
    minimum, maximum = bounds(shell)
    center = (minimum + maximum) * 0.5
    factors = Vector((1.12, 1.12, 1.03))
    target_center = Vector((CX, center.y - 0.005, center.z))
    for vertex in shell.data.vertices:
        delta = vertex.co - center
        vertex.co = target_center + Vector(
            (delta.x * factors.x, delta.y * factors.y, delta.z * factors.z)
        )
    shell.data.update()
    return {
        "sourceCenter": list(center),
        "targetCenter": list(target_center),
        "factors": list(factors),
    }


def remove_embedded_original_anatomy(shell: bpy.types.Object) -> dict[str, int]:
    """Abre un visor limpio y retira el faldón de cuello del archivo antiguo."""
    before = len(shell.data.vertices)
    face = v3.rounded_cube(
        "AqueoDarkV6_CleanFaceWindow",
        (CX, -0.060, 1.195),
        (0.126, 0.190, 0.180),
        bevel=0.014,
    )
    v3.boolean_difference(shell, face, "ContinuousFaceWindow")
    neck = v3.rounded_cube(
        "AqueoDarkV6_CleanRearNeck",
        (CX, 0.105, 1.092),
        (0.190, 0.160, 0.100),
        bevel=0.012,
    )
    v3.boolean_difference(shell, neck, "CleanRearNeck")
    return {"beforeVertices": before, "afterVertices": len(shell.data.vertices)}


def create_nasal(steel: bpy.types.Material) -> bpy.types.Object:
    outline = [
        (CX - 0.011, 1.274),
        (CX + 0.011, 1.274),
        (CX + 0.0085, 1.190),
        (CX, 1.172),
        (CX - 0.0085, 1.190),
    ]
    nasal = v3.curved_plate("AqueoDarkV6_ForgedNasal", outline, steel)
    for vertex in nasal.data.vertices:
        vertex.co.y -= 0.007
    nasal.data.update()
    return nasal


def carve_render_pose_cavity(shell: bpy.types.Object) -> dict[str, object]:
    """Excava la cabeza posada dentro de la carcasa sin mover el exterior."""
    before_import = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(LEONIDAS))
    bpy.context.scene.frame_set(0)
    imported = [obj for obj in bpy.context.scene.objects if obj not in before_import]
    head = next(obj for obj in imported if obj.name == "LeonidasHeadUnderlay")
    depsgraph = bpy.context.evaluated_depsgraph_get()
    evaluated = head.evaluated_get(depsgraph)
    cutter_mesh = bpy.data.meshes.new_from_object(
        evaluated,
        preserve_all_data_layers=False,
        depsgraph=depsgraph,
    )
    cutter = bpy.data.objects.new("AqueoDarkV6_RenderPoseHeadCutter", cutter_mesh)
    bpy.context.scene.collection.objects.link(cutter)
    cutter.matrix_world = evaluated.matrix_world.copy()

    # Convex hull: elimina ojos, barba y pequeñas islas internas del modelo y
    # produce un molde cerrado adecuado para el booleano.
    world = cutter.matrix_world.copy()
    cutter.parent = None
    cutter.matrix_world = world
    bpy.context.view_layer.objects.active = cutter
    cutter.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    cutter.select_set(False)
    bm = bmesh.new()
    bm.from_mesh(cutter.data)
    result = bmesh.ops.convex_hull(bm, input=list(bm.verts), use_existing_faces=False)
    removable = list(result.get("geom_interior", [])) + list(result.get("geom_unused", []))
    removable = list(dict.fromkeys(removable))
    if removable:
        bmesh.ops.delete(bm, geom=removable, context="VERTS")
    if bm.faces:
        bmesh.ops.recalc_face_normals(bm, faces=list(bm.faces))
    bm.to_mesh(cutter.data)
    bm.free()
    cutter.data.update()

    minimum, maximum = bounds(cutter)
    center = (minimum + maximum) * 0.5
    for vertex in cutter.data.vertices:
        delta = vertex.co - center
        vertex.co = center + Vector((delta.x * 1.018, delta.y * 1.022, delta.z * 1.012))
    cutter.data.update()
    v3.boolean_difference(shell, cutter, "ExcavateRenderPoseHead")

    for obj in imported:
        if obj.name in bpy.data.objects:
            bpy.data.objects.remove(obj, do_unlink=True)
    return {
        "source": "LeonidasHeadUnderlay frame 0 convex hull",
        "clearanceScale": [1.018, 1.022, 1.012],
        "cutterBoundsMin": list(minimum),
        "cutterBoundsMax": list(maximum),
    }


def fit_crest_source(obj: bpy.types.Object) -> dict[str, float]:
    minimum, maximum = bounds(obj)
    extent = maximum - minimum
    center = (minimum + maximum) * 0.5
    source_eye_z = maximum.z - extent.z * 0.507
    sx = 0.21786129696441192
    sy = 0.15044552609129414
    sz = 0.14594747817148265
    obj.scale = (sx, sy, sz)
    obj.location.x = CX - center.x * sx
    obj.location.y = -0.108 - minimum.y * sy
    obj.location.z = 1.245 - source_eye_z * sz
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    obj.select_set(False)
    return {"x": sx, "y": sy, "z": sz}


def extract_crest(obj: bpy.types.Object) -> dict[str, int]:
    before = len(obj.data.vertices)
    modifier = obj.modifiers.new("CrestDetailReduction", "DECIMATE")
    modifier.decimate_type = "COLLAPSE"
    modifier.ratio = 0.26
    modifier.use_collapse_triangulate = True
    apply_modifier(obj, modifier)
    after = len(obj.data.vertices)

    bm = bmesh.new()
    bm.from_mesh(obj.data)
    remove = []
    for vertex in bm.verts:
        x, y, z = vertex.co
        upper_arch = z > 1.326 and abs(x - CX) < 0.074
        rear_tail = y > 0.106 and abs(x - CX) < 0.068 and z > 1.178
        if not (upper_arch or rear_tail):
            remove.append(vertex)
    bmesh.ops.delete(bm, geom=remove, context="VERTS")
    loose = [vertex for vertex in bm.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(bm, geom=loose, context="VERTS")
    for vertex in bm.verts:
        vertex.co.x = CX + (vertex.co.x - CX) * 0.50
        vertex.co.z = 1.326 + (vertex.co.z - 1.326) * 0.82
    if bm.faces:
        bmesh.ops.recalc_face_normals(bm, faces=list(bm.faces))
    bm.to_mesh(obj.data)
    bm.free()
    obj.data.update()
    obj.name = "AqueoDarkV6_DenseSagittalCrest"
    return {"sourceVertices": before, "reducedVertices": after, "crestVertices": len(obj.data.vertices)}


def create_rails(steel: bpy.types.Material) -> list[bpy.types.Object]:
    rails: list[bpy.types.Object] = []
    for lateral in (-0.021, 0.021):
        points = []
        for index in range(42):
            t = index / 41.0
            y = -0.015 + 0.190 * t
            z = 1.337 + 0.022 * math.sin(math.pi * t) - 0.024 * t
            points.append((CX + lateral, y, z))
        rail = preview.curve_object(
            f"AqueoDarkV6_CrestRail_{lateral:+.3f}",
            points,
            0.0030,
            steel,
            resolution=3,
        )
        rails.append(rail)
    preview.convert_curves(rails)
    return rails


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
    bpy.ops.import_scene.gltf(filepath=os.fspath(ORIGINAL))
    shell = max(
        (obj for obj in bpy.context.scene.objects if obj.type == "MESH"),
        key=lambda item: len(item.data.vertices),
    )
    for obj in list(bpy.context.scene.objects):
        if obj.type == "MESH" and obj != shell:
            bpy.data.objects.remove(obj, do_unlink=True)
    shell.name = "AqueoDarkV6_OriginalAnatomicalShell"
    shell_fit = fit_original_shell(shell)
    cleanup = remove_embedded_original_anatomy(shell)
    cavity = carve_render_pose_cavity(shell)

    before = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(CREST_SOURCE))
    crest = max(
        (obj for obj in bpy.context.scene.objects if obj not in before and obj.type == "MESH"),
        key=lambda item: len(item.data.vertices),
    )
    crest_fit = fit_crest_source(crest)
    crest_stats = extract_crest(crest)

    before = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    imported = [obj for obj in bpy.context.scene.objects if obj not in before and obj.type == "MESH"]
    fit_source = next(obj for obj in imported if obj.name.startswith("LeonidasHelmetDomeStage01"))
    for obj in imported:
        if obj != fit_source:
            bpy.data.objects.remove(obj, do_unlink=True)
    fit_source.name = "Aqueo_FitReference"
    fit_source.hide_render = True

    steel = make_material(
        "AqueoV6_BlackenedSteel",
        (0.012, 0.019, 0.031, 1.0),
        0.88,
        0.33,
        105.0,
        0.052,
    )
    hair = make_material(
        "AqueoV6_BlackHorsehair",
        (0.0025, 0.0030, 0.0040, 1.0),
        0.02,
        0.86,
        150.0,
        0.10,
    )
    assign_material(shell, steel)
    assign_material(crest, hair)
    nasal = create_nasal(steel)
    rails = create_rails(steel)
    objects = [shell, nasal, crest, *rails, fit_source]
    for obj in objects:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-original-v6"
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
        "stage": "aqueo-dark-original-v6",
        "architecture": "original-anatomical-shell-plus-independent-sagittal-crest",
        "shellFit": shell_fit,
        "embeddedAnatomyCleanup": cleanup,
        "renderPoseCavity": cavity,
        "crestFit": crest_fit,
        "crest": crest_stats,
        "audits": {obj.name: mesh_audit(obj) for obj in [shell, nasal, crest, *rails]},
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "productionIntegrated": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    fit_source.hide_render = True
    preview.OUTPUT_ROOT = OUTPUT_ROOT / "preview"
    preview.REPORT = preview.OUTPUT_ROOT / "preview-report.json"
    preview.OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    preview.render_views(OUTPUT_GLB)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
