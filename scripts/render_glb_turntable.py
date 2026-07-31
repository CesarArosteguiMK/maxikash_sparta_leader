from __future__ import annotations

import math
import sys
from pathlib import Path

import bpy
from mathutils import Vector


def arguments() -> tuple[Path, Path, bool]:
    if "--" not in sys.argv:
        raise SystemExit("Usage: blender -b --python render_glb_turntable.py -- input.glb output_dir")
    args = sys.argv[sys.argv.index("--") + 1 :]
    if len(args) not in (2, 3):
        raise SystemExit("Expected input GLB, output directory, and optional --neutral")
    neutral = len(args) == 3 and args[2] == "--neutral"
    if len(args) == 3 and not neutral:
        raise SystemExit(f"Unknown option: {args[2]}")
    return Path(args[0]).resolve(), Path(args[1]).resolve(), neutral


def look_at(obj: bpy.types.Object, target: Vector) -> None:
    obj.rotation_euler = (target - obj.location).to_track_quat("-Z", "Y").to_euler()


def add_area(name: str, location: Vector, energy: float, size: float, target: Vector) -> None:
    data = bpy.data.lights.new(name=name, type="AREA")
    data.energy = energy
    data.shape = "DISK"
    data.size = size
    obj = bpy.data.objects.new(name, data)
    bpy.context.scene.collection.objects.link(obj)
    obj.location = location
    look_at(obj, target)


def main() -> None:
    source, output_dir, neutral = arguments()
    output_dir.mkdir(parents=True, exist_ok=True)

    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    bpy.ops.import_scene.gltf(filepath=str(source))

    meshes = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
    if not meshes:
        raise RuntimeError(f"No mesh objects found in {source}")

    if neutral:
        material = bpy.data.materials.new("QA_NeutralBronze")
        material.diffuse_color = (0.24, 0.105, 0.035, 1.0)
        material.use_nodes = True
        principled = material.node_tree.nodes.get("Principled BSDF")
        principled.inputs["Base Color"].default_value = (0.24, 0.105, 0.035, 1.0)
        principled.inputs["Metallic"].default_value = 0.82
        principled.inputs["Roughness"].default_value = 0.28
        for obj in meshes:
            obj.data.materials.clear()
            obj.data.materials.append(material)

    points = [obj.matrix_world @ Vector(corner) for obj in meshes for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[i] for point in points) for i in range(3)))
    maximum = Vector(tuple(max(point[i] for point in points) for i in range(3)))
    center = (minimum + maximum) * 0.5
    dimensions = maximum - minimum
    model_size = max(dimensions)
    distance = max(model_size * 2.15, 1.0)

    scene = bpy.context.scene
    scene.render.engine = "BLENDER_EEVEE"
    scene.render.resolution_x = 640
    scene.render.resolution_y = 640
    scene.render.resolution_percentage = 100
    scene.render.image_settings.file_format = "PNG"
    scene.render.film_transparent = False
    scene.render.image_settings.color_mode = "RGBA"
    scene.view_settings.look = "AgX - Medium High Contrast"
    scene.world.color = (0.018, 0.024, 0.035)

    camera_data = bpy.data.cameras.new("QA_Camera")
    camera_data.lens = 58
    camera = bpy.data.objects.new("QA_Camera", camera_data)
    scene.collection.objects.link(camera)
    scene.camera = camera

    add_area(
        "Key",
        center + Vector((-model_size * 1.8, -model_size * 2.0, model_size * 1.7)),
        140,
        model_size * 2.1,
        center,
    )
    add_area(
        "Fill",
        center + Vector((model_size * 1.8, -model_size * 1.2, model_size * 0.4)),
        75,
        model_size * 2.4,
        center,
    )
    add_area(
        "Rim",
        center + Vector((0, model_size * 2.0, model_size * 1.5)),
        110,
        model_size * 1.8,
        center,
    )

    views = {
        "front": 0,
        "right": 90,
        "back": 180,
        "left": 270,
    }
    for name, degrees in views.items():
        radians = math.radians(degrees)
        camera.location = center + Vector(
            (
                math.sin(radians) * distance,
                -math.cos(radians) * distance,
                model_size * 0.02,
            )
        )
        look_at(camera, center)
        scene.render.filepath = str(output_dir / f"{name}.png")
        bpy.ops.render.render(write_still=True)

    print(f"Rendered {len(views)} views to {output_dir}")


if __name__ == "__main__":
    main()
