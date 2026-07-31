from __future__ import annotations

import sys
from pathlib import Path

import bpy
import bmesh
from mathutils import Vector


if "--" not in sys.argv:
    raise SystemExit("Usage: blender -b --python inspect_glb.py -- model.glb")
source = Path(sys.argv[sys.argv.index("--") + 1]).resolve()

bpy.ops.object.select_all(action="SELECT")
bpy.ops.object.delete(use_global=False)
bpy.ops.import_scene.gltf(filepath=str(source))

for obj in bpy.context.scene.objects:
    if obj.type != "MESH":
        continue
    points = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[i] for point in points) for i in range(3)))
    maximum = Vector(tuple(max(point[i] for point in points) for i in range(3)))
    materials = [slot.material.name if slot.material else None for slot in obj.material_slots]
    print(
        f"MESH name={obj.name!r} vertices={len(obj.data.vertices)} "
        f"faces={len(obj.data.polygons)} min={tuple(round(v, 4) for v in minimum)} "
        f"max={tuple(round(v, 4) for v in maximum)} materials={materials}"
    )

    mesh = bmesh.new()
    mesh.from_mesh(obj.data)
    remaining = set(mesh.verts)
    components = []
    while remaining:
        seed = remaining.pop()
        stack = [seed]
        connected = {seed}
        while stack:
            current = stack.pop()
            for edge in current.link_edges:
                other = edge.other_vert(current)
                if other in remaining:
                    remaining.remove(other)
                    connected.add(other)
                    stack.append(other)
        world_points = [obj.matrix_world @ vertex.co for vertex in connected]
        component_min = Vector(tuple(min(point[i] for point in world_points) for i in range(3)))
        component_max = Vector(tuple(max(point[i] for point in world_points) for i in range(3)))
        components.append((len(connected), component_min, component_max))
    mesh.free()
    components.sort(key=lambda item: item[0], reverse=True)
    for index, (count, component_min, component_max) in enumerate(components[:20], start=1):
        print(
            f"  COMPONENT {index}: vertices={count} "
            f"min={tuple(round(v, 4) for v in component_min)} "
            f"max={tuple(round(v, 4) for v in component_max)}"
        )
