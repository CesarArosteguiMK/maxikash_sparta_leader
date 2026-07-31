from __future__ import annotations

import sys
from pathlib import Path

import bpy


if "--" not in sys.argv:
    raise SystemExit(
        "Usage: blender -b --python extract_glb_object.py -- source.glb object_name output.glb"
    )
args = sys.argv[sys.argv.index("--") + 1 :]
if len(args) != 3:
    raise SystemExit("Expected source GLB, object name, and output GLB")

source = Path(args[0]).resolve()
object_name = args[1]
output = Path(args[2]).resolve()
output.parent.mkdir(parents=True, exist_ok=True)

bpy.ops.object.select_all(action="SELECT")
bpy.ops.object.delete(use_global=False)
bpy.ops.import_scene.gltf(filepath=str(source))

target = bpy.data.objects.get(object_name)
if target is None or target.type != "MESH":
    available = [obj.name for obj in bpy.context.scene.objects if obj.type == "MESH"]
    raise RuntimeError(f"Mesh {object_name!r} not found. Available: {available}")

world_matrix = target.matrix_world.copy()
target.parent = None
target.matrix_world = world_matrix

bpy.ops.object.select_all(action="DESELECT")
target.select_set(True)
bpy.context.view_layer.objects.active = target
bpy.ops.export_scene.gltf(
    filepath=str(output),
    export_format="GLB",
    use_selection=True,
    export_apply=True,
)
print(output)
