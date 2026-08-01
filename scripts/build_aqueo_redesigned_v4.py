"""Build the QA Aqueo helmet against a stable shell dimension contract."""

from pathlib import Path

import bpy
from mathutils import Vector


ROOT = Path(__file__).resolve().parents[1]
DETAIL_SOURCE = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "helmet-aqueo-oscuro-preview.glb"
SHELL_SOURCE = ROOT / "storage" / "leonidas-helmet-designs" / "v1" / "helmet-aqueo-oscuro-standalone-v2.glb"
OUTPUT = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "helmet-aqueo-redesigned-v4.glb"


def bounds(objects: list[bpy.types.Object]) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for obj in objects for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[index] for point in corners) for index in range(3)))
    maximum = Vector(tuple(max(point[index] for point in corners) for index in range(3)))
    return (minimum + maximum) * 0.5, maximum - minimum


bpy.ops.wm.read_factory_settings(use_empty=True)
bpy.ops.import_scene.gltf(filepath=str(DETAIL_SOURCE))
detail_objects = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
detail_center, detail_size = bounds(detail_objects)

bpy.ops.import_scene.gltf(filepath=str(SHELL_SOURCE))
procedural_objects = [obj for obj in bpy.context.scene.objects if obj.type == "MESH" and obj not in detail_objects]
contract_center, contract_size = bounds(procedural_objects)

shell_names = {"Aqueo_Shell", "Aqueo_NeckGuard", "Aqueo_LowerBand"}
shell_objects = [obj for obj in procedural_objects if obj.name in shell_names]
for obj in [item for item in procedural_objects if item not in shell_objects]:
    bpy.data.objects.remove(obj, do_unlink=True)

# Map the detailed reconstruction into the same complete volume used by the
# shell contract. The non-uniform transform is intentional: it corrects the
# reconstruction's compressed width and excessive depth before integration.
axis_scale = Vector(
    tuple(contract_size[index] / max(detail_size[index], 1e-6) for index in range(3))
)
for obj in detail_objects:
    obj.scale = axis_scale
    obj.location = contract_center - Vector(
        tuple(detail_center[index] * axis_scale[index] for index in range(3))
    )
    # La máscara detallada debe quedar delante de la bóveda de ajuste. Este
    # desplazamiento forma parte del activo y evita mover el casco completo
    # hacia fuera de la cabeza durante la integración.
    obj.location.y -= 0.72
    obj["leonidasQaRole"] = "aqueo-detailed-exterior"

for obj in shell_objects:
    obj["leonidasQaRole"] = "aqueo-dimensional-shell"

bpy.ops.object.select_all(action="DESELECT")
for obj in detail_objects + shell_objects:
    obj.select_set(True)
bpy.context.view_layer.objects.active = detail_objects[0]

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
    "AQUEO_V4 "
    f"output={OUTPUT} axis_scale={tuple(round(value, 4) for value in axis_scale)} "
    f"shell_objects={len(shell_objects)}"
)
