"""Valida estructura, simetría y aislamiento de la cúpula etapa 01."""

from __future__ import annotations

import json
import os
from pathlib import Path

import bmesh
import bpy


ROOT = Path(__file__).resolve().parents[1]
MODEL_PATH = (
    ROOT
    / "public"
    / "assets"
    / "models"
    / "leonidas"
    / "qa"
    / "leonidas-helmet-dome-stage-01.glb"
)
REPORT_PATH = (
    ROOT
    / "storage"
    / "leonidas-helmet-designs"
    / "stage-01-dome"
    / "leonidas-helmet-dome-stage-01-validation.json"
)

SHELL_NAME = "LeonidasHelmetDomeStage01"
HEAD_NAME = "LeonidasHeadFitProxy"


bpy.ops.object.select_all(action="SELECT")
bpy.ops.object.delete(use_global=False)
bpy.ops.import_scene.gltf(filepath=os.fspath(MODEL_PATH))

shell = bpy.data.objects.get(SHELL_NAME)
head = bpy.data.objects.get(HEAD_NAME)
if shell is None or shell.type != "MESH":
    raise RuntimeError(f"Falta {SHELL_NAME}.")
if head is None or head.type != "MESH":
    raise RuntimeError(f"Falta {HEAD_NAME}.")

forbidden_tokens = ("mask", "nasal", "cheek", "crest", "plume", "penacho")
forbidden_objects = [
    obj.name
    for obj in bpy.context.scene.objects
    if any(token in obj.name.lower() for token in forbidden_tokens)
]

bm = bmesh.new()
bm.from_mesh(shell.data)
non_manifold_edges = [edge.index for edge in bm.edges if not edge.is_manifold]
component_count = 0
remaining = set(bm.verts)
while remaining:
    component_count += 1
    stack = [remaining.pop()]
    while stack:
        vertex = stack.pop()
        for edge in vertex.link_edges:
            neighbor = edge.other_vert(vertex)
            if neighbor in remaining:
                remaining.remove(neighbor)
                stack.append(neighbor)
bm.free()

axis_x = float(shell.get("leonidasSymmetryAxisWorldX", 0.0))
precision = 5
coordinates = {
    (
        round(vertex.co.x - axis_x, precision),
        round(vertex.co.y, precision),
        round(vertex.co.z, precision),
    )
    for vertex in shell.data.vertices
}
matched = 0
for relative_x, y, z in coordinates:
    reflected = (round(-relative_x, precision), y, z)
    if reflected in coordinates:
        matched += 1
symmetry_ratio = matched / max(len(coordinates), 1)

checks = {
    "exact_object_set": sorted(obj.name for obj in bpy.context.scene.objects)
    == sorted((HEAD_NAME, SHELL_NAME)),
    "forbidden_parts_absent": not forbidden_objects,
    "production_ready_false": shell.get("leonidasProductionReady") is False,
    "mask_false": shell.get("leonidasHasMask") is False,
    "crest_false": shell.get("leonidasHasCrest") is False,
    "thickness_3_5_mm": abs(
        float(shell.get("leonidasShellThickness", 0.0)) - 0.0035
    )
    < 0.00001,
    "single_connected_component": component_count == 1,
    "closed_manifold": len(non_manifold_edges) == 0,
    "symmetric": symmetry_ratio >= 0.999,
    "fit_verified": shell.get("leonidasFitVerified") is True,
    "no_fit_collisions": int(shell.get("leonidasFitCollisionCount", -1)) == 0,
    "occupancy_below_shell": float(shell.get("leonidasMaxOccupancy", 2.0)) < 1.0,
}

report = {
    "asset": str(MODEL_PATH.relative_to(ROOT)).replace("\\", "/"),
    "objects": [obj.name for obj in bpy.context.scene.objects],
    "shell": {
        "vertices": len(shell.data.vertices),
        "edges": len(shell.data.edges),
        "polygons": len(shell.data.polygons),
        "connected_components": component_count,
        "non_manifold_edges": len(non_manifold_edges),
        "symmetry_ratio": round(symmetry_ratio, 6),
    },
    "forbidden_objects": forbidden_objects,
    "checks": checks,
    "valid": all(checks.values()),
}
REPORT_PATH.parent.mkdir(parents=True, exist_ok=True)
REPORT_PATH.write_text(
    json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
)
print("LEONIDAS_DOME_STAGE_01_VALIDATION_BEGIN")
print(json.dumps(report, ensure_ascii=False, indent=2))
print("LEONIDAS_DOME_STAGE_01_VALIDATION_END")
if not report["valid"]:
    raise RuntimeError("La cúpula etapa 01 no cumple el contrato estructural.")
