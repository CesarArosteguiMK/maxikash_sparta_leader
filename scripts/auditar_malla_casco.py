"""Audita topología y dimensiones de un GLB de casco en Blender.

Uso:
    blender --background --factory-startup --python scripts/auditar_malla_casco.py -- archivo.glb
"""

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
        raise SystemExit("Falta la ruta del GLB")
    source = Path(args[0]).resolve()
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    bpy.ops.import_scene.gltf(filepath=str(source))
    report: dict[str, object] = {"source": str(source), "objects": []}
    for obj in [item for item in bpy.context.scene.objects if item.type == "MESH"]:
        bm = bmesh.new()
        bm.from_mesh(obj.data)
        bm.edges.ensure_lookup_table()
        bounds = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
        minimum = [min(point[axis] for point in bounds) for axis in range(3)]
        maximum = [max(point[axis] for point in bounds) for axis in range(3)]
        report["objects"].append(
            {
                "name": obj.name,
                "vertices": len(bm.verts),
                "faces": len(bm.faces),
                "boundary_edges": sum(1 for edge in bm.edges if edge.is_boundary),
                "non_manifold_edges": sum(1 for edge in bm.edges if not edge.is_manifold),
                "bounds_min": minimum,
                "bounds_max": maximum,
                "extent": [maximum[i] - minimum[i] for i in range(3)],
            }
        )
        bm.free()
    print("HELMET_MESH_AUDIT " + json.dumps(report, ensure_ascii=False))


if __name__ == "__main__":
    main()
