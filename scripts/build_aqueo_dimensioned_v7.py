"""Build the symmetric, continuous Aqueo helmet used by the QA laboratory."""

from pathlib import Path

import bmesh
import bpy
from mathutils import Vector


ROOT = Path(__file__).resolve().parents[1]
DETAIL_SOURCE = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "helmet-aqueo-oscuro-preview.glb"
STRUCTURE_SOURCE = ROOT / "storage" / "leonidas-helmet-designs" / "v1" / "helmet-aqueo-oscuro-standalone-v2.glb"
OUTPUT = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "helmet-aqueo-dimensioned-v7.glb"


def bounds(objects: list[bpy.types.Object]) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for obj in objects for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[index] for point in corners) for index in range(3)))
    maximum = Vector(tuple(max(point[index] for point in corners) for index in range(3)))
    return (minimum + maximum) * 0.5, maximum - minimum


def mirror_approved_half(obj: bpy.types.Object) -> None:
    """Keep the complete left reconstruction and mirror it across the sagittal plane."""
    mesh = bmesh.new()
    mesh.from_mesh(obj.data)
    bmesh.ops.bisect_plane(
        mesh,
        geom=list(mesh.verts) + list(mesh.edges) + list(mesh.faces),
        dist=0.0005,
        plane_co=(0.0, 0.0, 0.0),
        plane_no=(1.0, 0.0, 0.0),
        use_snap_center=True,
        clear_outer=True,
        clear_inner=False,
    )
    bmesh.ops.remove_doubles(mesh, verts=list(mesh.verts), dist=0.0005)
    mesh.to_mesh(obj.data)
    mesh.free()
    obj.data.update()

    modifier = obj.modifiers.new("SagittalSymmetry", "MIRROR")
    modifier.use_axis[0] = True
    modifier.use_clip = True
    modifier.use_mirror_merge = True
    modifier.merge_threshold = 0.001
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=modifier.name)
    obj.select_set(False)


def blackened_steel() -> bpy.types.Material:
    """One physical material removes skin-colored pixels baked into the AI mesh."""
    result = bpy.data.materials.new("Aqueo_BlackenedSteel")
    result.use_nodes = True
    shader = result.node_tree.nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = (0.035, 0.050, 0.075, 1.0)
    shader.inputs["Metallic"].default_value = 0.88
    shader.inputs["Roughness"].default_value = 0.31
    return result


bpy.ops.wm.read_factory_settings(use_empty=True)
bpy.ops.import_scene.gltf(filepath=str(DETAIL_SOURCE))
details = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
detail_material = blackened_steel()
for detail in details:
    mirror_approved_half(detail)
    detail.data.materials.clear()
    detail.data.materials.append(detail_material)
detail_center, detail_size = bounds(details)

bpy.ops.import_scene.gltf(filepath=str(STRUCTURE_SOURCE))
structure = [obj for obj in bpy.context.scene.objects if obj.type == "MESH" and obj not in details]
contract_center, contract_size = bounds(structure)

structural_names = {
    "Aqueo_Shell",
    "Aqueo_CranialCap",
    "Aqueo_NeckGuard",
    "Aqueo_LowerBand",
    "Aqueo_Forehead",
    "Aqueo_ForeheadTrim",
}
structural_parts = [obj for obj in structure if obj.name in structural_names]
for obj in [item for item in structure if item not in structural_parts]:
    bpy.data.objects.remove(obj, do_unlink=True)

# Preserve an invisible copy of the approved head volume as the stable fit
# reference. The visible dome is then allowed to advance toward the forehead
# without moving or rescaling the mask, crest, or the whole helmet assembly.
shell = next(obj for obj in structural_parts if obj.name == "Aqueo_Shell")
cranial_cap = next(obj for obj in structural_parts if obj.name == "Aqueo_CranialCap")
cranial_cap.data.materials.clear()
cranial_cap.data.materials.append(detail_material)
fit_reference = shell.copy()
fit_reference.data = shell.data.copy()
fit_reference.name = "Aqueo_FitReference"
bpy.context.scene.collection.objects.link(fit_reference)
# El casquete se centra sobre el cráneo real, no sobre la cola posterior de la
# bóveda dimensional. Así cubre sien y coronilla sin agrandar la máscara.
cranial_cap.location.y -= 0.55
cranial_cap.location.z += 0.10
structural_parts.append(fit_reference)

axis_scale = Vector(
    tuple(contract_size[index] / max(detail_size[index], 1e-6) for index in range(3))
)
for obj in details:
    obj.scale = axis_scale
    obj.location = contract_center - Vector(
        tuple(detail_center[index] * axis_scale[index] for index in range(3))
    )
    obj.location.y -= 0.72
    obj["leonidasQaRole"] = "aqueo-symmetric-detailed-exterior"

for obj in structural_parts:
    if obj.name == "Aqueo_FitReference":
        obj["leonidasQaRole"] = "aqueo-fit-reference"
        obj["leonidasFitNode"] = True
    else:
        obj["leonidasQaRole"] = (
            "aqueo-dimensional-shell" if obj.name == "Aqueo_Shell" else "aqueo-structural-underlay"
        )
        obj["leonidasFitNode"] = False

selection = details + structural_parts
bpy.ops.object.select_all(action="DESELECT")
for obj in selection:
    obj.select_set(True)
bpy.context.view_layer.objects.active = fit_reference

OUTPUT.parent.mkdir(parents=True, exist_ok=True)
bpy.ops.export_scene.gltf(
    filepath=str(OUTPUT),
    export_format="GLB",
    use_selection=True,
    export_animations=False,
    export_skins=False,
    export_morph=False,
    export_extras=True,
    export_yup=True,
)
print(
    "AQUEO_V7 "
    f"output={OUTPUT} axis_scale={tuple(round(value, 4) for value in axis_scale)} "
    f"structural_parts={len(structural_parts)}"
)
