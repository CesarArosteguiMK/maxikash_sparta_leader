"""Lista componentes conectados de la malla principal de un GLB."""

from __future__ import annotations

import json
import sys
from pathlib import Path

import bmesh
import bpy
from mathutils import Vector


def main() -> None:
    args = sys.argv[sys.argv.index("--") + 1 :] if "--" in sys.argv else []
    if not args:
        raise SystemExit("Falta el GLB")
    source = Path(args[0]).resolve()
    bpy.ops.import_scene.gltf(filepath=str(source))
    obj = max(
        (item for item in bpy.context.scene.objects if item.type == "MESH"),
        key=lambda item: len(item.data.vertices),
    )
    bm = bmesh.new()
    bm.from_mesh(obj.data)
    unseen = set(bm.verts)
    components = []
    while unseen:
        seed = unseen.pop()
        stack = [seed]
        vertices = [seed]
        while stack:
            current = stack.pop()
            for edge in current.link_edges:
                other = edge.other_vert(current)
                if other in unseen:
                    unseen.remove(other)
                    stack.append(other)
                    vertices.append(other)
        coordinates = [vertex.co for vertex in vertices]
        minimum = Vector(tuple(min(co[axis] for co in coordinates) for axis in range(3)))
        maximum = Vector(tuple(max(co[axis] for co in coordinates) for axis in range(3)))
        faces = {face for vertex in vertices for face in vertex.link_faces}
        components.append(
            {
                "vertices": len(vertices),
                "faces": len(faces),
                "min": [round(value, 6) for value in minimum],
                "max": [round(value, 6) for value in maximum],
                "extent": [round(value, 6) for value in maximum - minimum],
                "center": [round(value, 6) for value in (minimum + maximum) * 0.5],
            }
        )
    bm.free()
    components.sort(key=lambda item: item["vertices"], reverse=True)
    print("MESH_COMPONENTS " + json.dumps(components, ensure_ascii=False))


if __name__ == "__main__":
    main()
