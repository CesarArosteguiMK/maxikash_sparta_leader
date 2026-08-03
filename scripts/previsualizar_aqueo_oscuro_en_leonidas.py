"""Renderiza una previsualización QA del Áqueo oscuro sobre Leónidas.

No modifica el GLB modular ni publica el casco. El candidato negro legado se
coloca usando el ancho y el centro del contrato de ajuste bloqueado.
"""

from __future__ import annotations

import json
import math
import os
from pathlib import Path

import bpy
from mathutils import Vector


ROOT = Path(__file__).resolve().parents[1]
LEONIDAS = ROOT / "public" / "assets" / "models" / "leonidas" / "leonidas-spartan-modular-v2.glb"
AQUEO = Path(
    os.environ.get(
        "LEONIDAS_AQUEO_PREVIEW_ASSET",
        os.fspath(ROOT / "storage" / "leonidas-helmet-designs" / "sculpted" / "helmet-aqueo-oscuro-sculpted-v7.glb"),
    )
)
FIT_CONTRACT = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "helmet-fit-contracts.json"
OUTPUT_ROOT = Path(
    os.environ.get(
        "LEONIDAS_AQUEO_PREVIEW_OUTPUT",
        os.fspath(ROOT / "storage" / "leonidas-helmet-designs" / "aqueo-fit-preview-v1"),
    )
)
REPORT = OUTPUT_ROOT / "aqueo-fit-preview-v1-report.json"


def reset_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def bounds(objects: list[bpy.types.Object]) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for obj in objects for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[axis] for point in corners) for axis in range(3)))
    maximum = Vector(tuple(max(point[axis] for point in corners) for axis in range(3)))
    return minimum, maximum


def look_at(obj: bpy.types.Object, target: Vector) -> None:
    obj.rotation_euler = (target - obj.location).to_track_quat("-Z", "Y").to_euler()


def add_area(
    name: str,
    location: Vector,
    energy: float,
    size: float,
    target: Vector,
) -> None:
    data = bpy.data.lights.new(name, "AREA")
    data.energy = energy
    data.shape = "DISK"
    data.size = size
    obj = bpy.data.objects.new(name, data)
    bpy.context.scene.collection.objects.link(obj)
    obj.location = location
    look_at(obj, target)


def configure_render() -> bpy.types.Object:
    scene = bpy.context.scene
    scene.render.engine = "BLENDER_EEVEE"
    scene.render.resolution_x = 900
    scene.render.resolution_y = 1100
    scene.render.resolution_percentage = 100
    scene.render.image_settings.file_format = "PNG"
    scene.view_settings.look = "AgX - Medium High Contrast"
    scene.world.color = (0.012, 0.020, 0.034)
    camera_data = bpy.data.cameras.new("AqueoPreviewCamera")
    camera_data.type = "ORTHO"
    camera = bpy.data.objects.new("AqueoPreviewCamera", camera_data)
    bpy.context.scene.collection.objects.link(camera)
    scene.camera = camera
    lighting_target = Vector((0.0, 0.02, 1.05))
    add_area("Key", Vector((-2.1, -2.6, 2.6)), 520.0, 2.2, lighting_target)
    add_area("Fill", Vector((2.0, -1.5, 1.6)), 230.0, 1.8, lighting_target)
    add_area("Rim", Vector((0.4, 2.4, 2.3)), 420.0, 1.6, lighting_target)
    return camera


def render(
    camera: bpy.types.Object,
    name: str,
    target: Vector,
    direction: Vector,
    distance: float,
    ortho_scale: float,
) -> None:
    camera.data.ortho_scale = ortho_scale
    camera.location = target + direction.normalized() * distance
    look_at(camera, target)
    bpy.context.scene.render.filepath = os.fspath(OUTPUT_ROOT / f"{name}.png")
    bpy.ops.render.render(write_still=True)


OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
contract_document = json.loads(FIT_CONTRACT.read_text(encoding="utf-8"))
contract = contract_document["contracts"]["aqueo_oscuro"]
target_bounds = contract["approvedDomeReference"]["shellBoundsWorldMeters"]
target_min = Vector(target_bounds["min"])
target_max = Vector(target_bounds["max"])
target_size = target_max - target_min
target_center = (target_min + target_max) * 0.5

reset_scene()
bpy.ops.import_scene.gltf(filepath=os.fspath(LEONIDAS))
leonidas_objects = set(bpy.context.scene.objects)
parts = {obj.name: obj for obj in leonidas_objects}

for hidden_name in ("LeonidasHelmet", "LeonidasHair", "LeonidasShield", "LeonidasSpear"):
    obj = parts.get(hidden_name)
    if obj:
        obj.hide_render = True
        obj.hide_viewport = True

# Congela una pose neutral del propio GLB, conservando la postura guardada en
# el modelo. No se altera la malla ni el esqueleto productivos.
bpy.context.scene.frame_set(0)
bpy.context.view_layer.update()

bpy.ops.import_scene.gltf(filepath=os.fspath(AQUEO))
candidate_objects = [obj for obj in bpy.context.scene.objects if obj not in leonidas_objects]
candidate_meshes = [obj for obj in candidate_objects if obj.type == "MESH"]
if not candidate_meshes:
    raise RuntimeError("El candidato Áqueo oscuro no contiene mallas.")
source_min, source_max = bounds(candidate_meshes)
source_size = source_max - source_min
source_center = (source_min + source_max) * 0.5

container = bpy.data.objects.new("AqueoDarkFitPreview", None)
bpy.context.scene.collection.objects.link(container)
for obj in [item for item in candidate_objects if item.parent is None]:
    world = obj.matrix_world.copy()
    obj.parent = container
    obj.matrix_world = world

# El ancho de la cúpula aprobada es el único factor de escala. La cresta y la
# máscara nunca participan en la medida objetivo.
uniform_scale = target_size.x / max(source_size.x, 0.000001)
container.scale = (uniform_scale, uniform_scale, uniform_scale)
container.location.x = target_center.x - source_center.x * uniform_scale
container.location.y = target_center.y - source_center.y * uniform_scale

# El origen vertical de la reconstrucción no representa el centro craneal.
# La línea ocular del multiview está cerca del 50.7 % de su altura; se alinea
# con la línea ocular de la cabeza anatómica medida. Es un valor QA explícito,
# no un ajuste productivo oculto.
source_eye_z = source_max.z - source_size.z * 0.507
target_eye_z = 1.245
container.location.z = target_eye_z - source_eye_z * uniform_scale
container["leonidasQaOnly"] = True
container["leonidasFitContract"] = "aqueo_oscuro:v1"
container["leonidasUniformScale"] = uniform_scale

# Material oscuro coherente: conserva las normales y el relieve del candidato,
# pero elimina variaciones de color procedentes de la reconstrucción.
black = bpy.data.materials.new("AqueoPreviewBlackenedSteel")
black.use_nodes = True
shader = black.node_tree.nodes.get("Principled BSDF")
shader.inputs["Base Color"].default_value = (0.012, 0.020, 0.032, 1.0)
shader.inputs["Metallic"].default_value = 0.82
shader.inputs["Roughness"].default_value = 0.30
for mesh in candidate_meshes:
    mesh.data.materials.clear()
    mesh.data.materials.append(black)
    for polygon in mesh.data.polygons:
        polygon.material_index = 0

camera = configure_render()
full_target = Vector((0.0, 0.03, 0.93))
head_target = Vector((target_center.x, target_center.y, 1.245))
render(camera, "01-full-front", full_target, Vector((0.0, -1.0, 0.02)), 4.0, 2.05)
render(camera, "02-head-front", head_target, Vector((0.0, -1.0, 0.02)), 3.0, 0.62)
render(camera, "03-head-three-quarter", head_target, Vector((0.72, -1.0, 0.10)), 3.0, 0.64)
render(camera, "04-head-profile", head_target, Vector((1.0, 0.0, 0.02)), 3.0, 0.66)

candidate_min, candidate_max = bounds(candidate_meshes)
report = {
    "status": "qa-preview-not-production",
    "fitContract": "aqueo_oscuro:v1",
    "leonidasAsset": str(LEONIDAS.relative_to(ROOT)).replace("\\", "/"),
    "candidateAsset": str(AQUEO.relative_to(ROOT)).replace("\\", "/"),
    "uniformScaleFromApprovedDomeWidth": round(uniform_scale, 6),
    "targetEyeWorldZ": target_eye_z,
    "sourceBounds": {
        "min": [round(value, 6) for value in source_min],
        "max": [round(value, 6) for value in source_max],
    },
    "previewBoundsWorld": {
        "min": [round(value, 6) for value in candidate_min],
        "max": [round(value, 6) for value in candidate_max],
    },
    "limitations": [
        "legacy candidate surface",
        "uniform QA placement only",
        "not rebuilt against approved dome",
        "not bound to Head bone",
        "not production ready",
    ],
}
REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
print("AQUEO_ON_LEONIDAS_PREVIEW_BEGIN")
print(json.dumps(report, ensure_ascii=False, indent=2))
print("AQUEO_ON_LEONIDAS_PREVIEW_END")
