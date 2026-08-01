"""Build a continuous, dimensioned Aqueo helmet for Leonidas QA.

The procedural shell, forehead and brow plates form the structural volume.
The detailed reconstruction supplies the exterior mask and crest.  The shell
alone remains the fit node, so neither the mask nor the crest can resize it.
"""

from pathlib import Path

import bpy
from mathutils import Vector


ROOT = Path(__file__).resolve().parents[1]
DETAIL_SOURCE = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "helmet-aqueo-oscuro-preview.glb"
STRUCTURE_SOURCE = ROOT / "storage" / "leonidas-helmet-designs" / "v1" / "helmet-aqueo-oscuro-standalone-v2.glb"
OUTPUT = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "helmet-aqueo-dimensioned-v6.glb"


def bounds(objects: list[bpy.types.Object]) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for obj in objects for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[index] for point in corners) for index in range(3)))
    maximum = Vector(tuple(max(point[index] for point in corners) for index in range(3)))
    return (minimum + maximum) * 0.5, maximum - minimum


bpy.ops.wm.read_factory_settings(use_empty=True)
bpy.ops.import_scene.gltf(filepath=str(DETAIL_SOURCE))
details = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
detail_center, detail_size = bounds(details)

bpy.ops.import_scene.gltf(filepath=str(STRUCTURE_SOURCE))
structure = [obj for obj in bpy.context.scene.objects if obj.type == "MESH" and obj not in details]
contract_center, contract_size = bounds(structure)

structural_names = {
    "Aqueo_Shell",
    "Aqueo_NeckGuard",
    "Aqueo_LowerBand",
    "Aqueo_Forehead",
    "Aqueo_ForeheadTrim",
    "Aqueo_BrowL",
    "Aqueo_BrowLTrim",
    "Aqueo_BrowR",
    "Aqueo_BrowRTrim",
}
structural_parts = [obj for obj in structure if obj.name in structural_names]
for obj in [item for item in structure if item not in structural_parts]:
    bpy.data.objects.remove(obj, do_unlink=True)

axis_scale = Vector(
    tuple(contract_size[index] / max(detail_size[index], 1e-6) for index in range(3))
)
for obj in details:
    obj.scale = axis_scale
    obj.location = contract_center - Vector(
        tuple(detail_center[index] * axis_scale[index] for index in range(3))
    )
    # Put the detailed face ahead of the dimensional structure. The forehead
    # remains behind it and closes the exposed scalp without z-fighting.
    obj.location.y -= 0.72
    obj["leonidasQaRole"] = "aqueo-detailed-exterior"

for obj in structural_parts:
    obj["leonidasQaRole"] = (
        "aqueo-dimensional-shell" if obj.name == "Aqueo_Shell" else "aqueo-structural-underlay"
    )
    obj["leonidasFitNode"] = obj.name == "Aqueo_Shell"

selection = details + structural_parts
bpy.ops.object.select_all(action="DESELECT")
for obj in selection:
    obj.select_set(True)
bpy.context.view_layer.objects.active = next(obj for obj in structural_parts if obj.name == "Aqueo_Shell")

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
    "AQUEO_V6 "
    f"output={OUTPUT} axis_scale={tuple(round(value, 4) for value in axis_scale)} "
    f"structural_parts={len(structural_parts)}"
)
