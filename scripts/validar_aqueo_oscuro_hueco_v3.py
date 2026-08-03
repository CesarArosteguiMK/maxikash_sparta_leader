"""Valida intersecciones reales entre cabeza y candidato Áqueo oscuro v3."""

from __future__ import annotations

import json
import os
from pathlib import Path

import bpy
from mathutils.bvhtree import BVHTree


ROOT = Path(__file__).resolve().parents[1]
LEONIDAS = ROOT / "public/assets/models/leonidas/leonidas-spartan-modular-v2.glb"
HELMET = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-hollow-v3.glb"
REPORT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-hollow-v3/leonidas-aqueo-dark-hollow-v3-collision-report.json"


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def tree_for(obj: bpy.types.Object, depsgraph: bpy.types.Depsgraph):
    evaluated = obj.evaluated_get(depsgraph)
    mesh = evaluated.to_mesh(preserve_all_data_layers=True, depsgraph=depsgraph)
    vertices = [evaluated.matrix_world @ vertex.co for vertex in mesh.vertices]
    polygons = [tuple(polygon.vertices) for polygon in mesh.polygons]
    tree = BVHTree.FromPolygons(vertices, polygons, all_triangles=False, epsilon=0.00005)
    evaluated.to_mesh_clear()
    return tree, vertices, polygons


def main() -> None:
    clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(LEONIDAS))
    bpy.context.scene.frame_set(0)
    head = next(obj for obj in bpy.context.scene.objects if obj.name == "LeonidasHeadUnderlay")
    original = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(HELMET))
    candidates = [
        obj
        for obj in bpy.context.scene.objects
        if obj not in original and obj.type == "MESH" and obj.name != "Aqueo_FitReference"
    ]
    depsgraph = bpy.context.evaluated_depsgraph_get()
    head_tree, _, _ = tree_for(head, depsgraph)
    intersections: dict[str, int] = {}
    collision_bounds: dict[str, dict[str, list[float]]] = {}
    for obj in candidates:
        candidate_tree, vertices, polygons = tree_for(obj, depsgraph)
        overlap = head_tree.overlap(candidate_tree)
        intersections[obj.name] = len(overlap)
        if overlap:
            centers = []
            for _, polygon_index in overlap:
                polygon = polygons[polygon_index]
                centers.append(sum((vertices[index] for index in polygon), vertices[polygon[0]] * 0.0) / len(polygon))
            collision_bounds[obj.name] = {
                "min": [min(center[axis] for center in centers) for axis in range(3)],
                "max": [max(center[axis] for center in centers) for axis in range(3)],
            }
    report = {
        "asset": str(HELMET.relative_to(ROOT)).replace("\\", "/"),
        "anatomy": "LeonidasHeadUnderlay",
        "frame": 0,
        "intersectionsByObject": intersections,
        "collisionBoundsByObject": collision_bounds,
        "totalTriangleIntersections": sum(intersections.values()),
        "valid": sum(intersections.values()) == 0,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print("AQUEO_V3_COLLISION_REPORT " + json.dumps(report, ensure_ascii=False))
    if not report["valid"]:
        raise SystemExit(2)


if __name__ == "__main__":
    main()
