"""Publish the complete Aqueo helmet using its shell as the fit contract.

This deliberately exports one coherent helmet assembly. Decorative parts may
never define the avatar fit; ``Aqueo_Shell`` is the only dimensional reference.
"""

from pathlib import Path

import bpy


ROOT = Path(__file__).resolve().parents[1]
SOURCE = (
    ROOT
    / "storage"
    / "leonidas-helmet-designs"
    / "v1"
    / "helmet-aqueo-oscuro-standalone-v2.glb"
)
OUTPUT = (
    ROOT
    / "public"
    / "assets"
    / "models"
    / "leonidas"
    / "qa"
    / "helmet-aqueo-dimensioned-v5.glb"
)


bpy.ops.wm.read_factory_settings(use_empty=True)
bpy.ops.import_scene.gltf(filepath=str(SOURCE))

meshes = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
shell = next((obj for obj in meshes if obj.name == "Aqueo_Shell"), None)
if shell is None:
    raise RuntimeError("The Aqueo_Shell dimensional contract is missing")

for obj in meshes:
    obj["leonidasQaRole"] = (
        "aqueo-dimensional-shell" if obj is shell else "aqueo-fitted-detail"
    )
    obj["leonidasFitNode"] = obj is shell

bpy.ops.object.select_all(action="DESELECT")
for obj in meshes:
    obj.select_set(True)
bpy.context.view_layer.objects.active = shell

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
print(f"AQUEO_V5 output={OUTPUT} meshes={len(meshes)} fit_node={shell.name}")
