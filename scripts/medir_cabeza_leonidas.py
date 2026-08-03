"""Mide la cabeza anatómica real de Leónidas y publica un contrato de ajuste.

Uso:
    blender --background --factory-startup --python scripts/medir_cabeza_leonidas.py

El contrato se obtiene de ``LeonidasHeadUnderlay`` con el esqueleto en pose de
reposo. No usa el casco original ni candidatos anteriores como referencia.
"""

from __future__ import annotations

import json
import math
import os
from pathlib import Path

import bpy
from mathutils import Matrix, Vector


PROJECT_ROOT = Path(__file__).resolve().parents[1]
MODEL_PATH = (
    PROJECT_ROOT
    / "public"
    / "assets"
    / "models"
    / "leonidas"
    / "leonidas-spartan-modular-v2.glb"
)
OUTPUT_PATH = (
    PROJECT_ROOT
    / "storage"
    / "leonidas-helmet-designs"
    / "measurements"
    / "leonidas-head-contract-v1.json"
)

HEAD_OBJECT_NAME = "LeonidasHeadUnderlay"
HEAD_BONE_NAME = "mixamorig:Head"
MODEL_UNIT = "meter"


def reset_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    for blocks in (
        bpy.data.meshes,
        bpy.data.armatures,
        bpy.data.materials,
        bpy.data.images,
        bpy.data.actions,
    ):
        for block in list(blocks):
            if block.users == 0:
                blocks.remove(block)


def rounded(value: float, digits: int = 6) -> float:
    return round(float(value), digits)


def vector_list(value: Vector, digits: int = 6) -> list[float]:
    return [rounded(axis, digits) for axis in value]


def matrix_rows(value: Matrix, digits: int = 8) -> list[list[float]]:
    return [[rounded(axis, digits) for axis in row] for row in value]


def percentile(values: list[float], fraction: float) -> float:
    ordered = sorted(values)
    if not ordered:
        raise RuntimeError("No hay vértices para calcular percentiles.")
    position = max(0.0, min(1.0, fraction)) * (len(ordered) - 1)
    lower = int(math.floor(position))
    upper = int(math.ceil(position))
    if lower == upper:
        return ordered[lower]
    weight = position - lower
    return ordered[lower] * (1.0 - weight) + ordered[upper] * weight


def bounds(points: list[Vector]) -> tuple[Vector, Vector]:
    minimum = Vector(tuple(min(point[axis] for point in points) for axis in range(3)))
    maximum = Vector(tuple(max(point[axis] for point in points) for axis in range(3)))
    return minimum, maximum


def robust_bounds(
    points: list[Vector], lower: float = 0.005, upper: float = 0.995
) -> tuple[Vector, Vector]:
    axes = [[point[axis] for point in points] for axis in range(3)]
    minimum = Vector(tuple(percentile(axis, lower) for axis in axes))
    maximum = Vector(tuple(percentile(axis, upper) for axis in axes))
    return minimum, maximum


def dimensions(minimum: Vector, maximum: Vector) -> dict[str, object]:
    size = maximum - minimum
    center = (minimum + maximum) * 0.5
    return {
        "min": vector_list(minimum),
        "max": vector_list(maximum),
        "center": vector_list(center),
        "extent": {
            "x": rounded(size.x),
            "y": rounded(size.y),
            "z": rounded(size.z),
        },
    }


def horizontal_slices(
    points: list[Vector], minimum_z: float, maximum_z: float
) -> list[dict[str, object]]:
    height = maximum_z - minimum_z
    slices = []
    for ratio in (0.42, 0.50, 0.58, 0.66, 0.74, 0.82, 0.90, 0.96):
        center_z = minimum_z + height * ratio
        half_band = height * 0.018
        band = [point for point in points if abs(point.z - center_z) <= half_band]
        if len(band) < 8:
            continue
        xs = [point.x for point in band]
        ys = [point.y for point in band]
        slices.append(
            {
                "height_ratio": ratio,
                "z": rounded(center_z),
                "samples": len(band),
                "x_robust_min": rounded(percentile(xs, 0.02)),
                "x_robust_max": rounded(percentile(xs, 0.98)),
                "front_y_robust": rounded(percentile(ys, 0.02)),
                "back_y_robust": rounded(percentile(ys, 0.98)),
            }
        )
    return slices


def evaluated_world_vertices(obj: bpy.types.Object) -> list[Vector]:
    depsgraph = bpy.context.evaluated_depsgraph_get()
    evaluated = obj.evaluated_get(depsgraph)
    mesh = evaluated.to_mesh(preserve_all_data_layers=True, depsgraph=depsgraph)
    try:
        return [evaluated.matrix_world @ vertex.co for vertex in mesh.vertices]
    finally:
        evaluated.to_mesh_clear()


def find_armature(obj: bpy.types.Object) -> bpy.types.Object:
    for modifier in obj.modifiers:
        if modifier.type == "ARMATURE" and modifier.object:
            return modifier.object
    if obj.parent and obj.parent.type == "ARMATURE":
        return obj.parent
    armatures = [item for item in bpy.context.scene.objects if item.type == "ARMATURE"]
    if len(armatures) == 1:
        return armatures[0]
    raise RuntimeError("No se pudo identificar el esqueleto de Leónidas.")


def connected_component_sizes(mesh: bpy.types.Mesh) -> list[int]:
    parent = list(range(len(mesh.vertices)))
    component_size = [1] * len(mesh.vertices)

    def find(index: int) -> int:
        while parent[index] != index:
            parent[index] = parent[parent[index]]
            index = parent[index]
        return index

    def join(first: int, second: int) -> None:
        first_root = find(first)
        second_root = find(second)
        if first_root == second_root:
            return
        if component_size[first_root] < component_size[second_root]:
            first_root, second_root = second_root, first_root
        parent[second_root] = first_root
        component_size[first_root] += component_size[second_root]

    for edge in mesh.edges:
        join(edge.vertices[0], edge.vertices[1])
    counts: dict[int, int] = {}
    for index in range(len(mesh.vertices)):
        root = find(index)
        counts[root] = counts.get(root, 0) + 1
    return sorted(counts.values(), reverse=True)


reset_scene()
bpy.ops.import_scene.gltf(filepath=os.fspath(MODEL_PATH))

head = bpy.data.objects.get(HEAD_OBJECT_NAME)
if head is None or head.type != "MESH":
    available = [obj.name for obj in bpy.context.scene.objects if obj.type == "MESH"]
    raise RuntimeError(
        f"Falta {HEAD_OBJECT_NAME}. Mallas disponibles: {available}"
    )

armature = find_armature(head)
head_bone = armature.data.bones.get(HEAD_BONE_NAME)
if head_bone is None:
    raise RuntimeError(f"Falta el hueso requerido {HEAD_BONE_NAME}.")

# Medimos una referencia neutral, sin una pose o animación accidental.
armature.data.pose_position = "REST"
if armature.animation_data:
    armature.animation_data.action = None
bpy.context.scene.frame_set(0)
bpy.context.view_layer.update()

world_points = evaluated_world_vertices(head)
world_minimum, world_maximum = bounds(world_points)
world_robust_minimum, world_robust_maximum = robust_bounds(world_points)

head_bone_world = armature.matrix_world @ head_bone.matrix_local
world_to_head_bone = head_bone_world.inverted()
bone_points = [world_to_head_bone @ point for point in world_points]
bone_minimum, bone_maximum = bounds(bone_points)
bone_robust_minimum, bone_robust_maximum = robust_bounds(bone_points)

robust_size = world_robust_maximum - world_robust_minimum
robust_center = (world_robust_minimum + world_robust_maximum) * 0.5

# El modelo usa X lateral, Y profundidad (frente hacia -Y) y Z vertical.
# Las holguras son deliberadamente pequeñas y separadas por dirección para
# que el diseño no vuelva a convertirse en una esfera sobredimensionada.
clearance = {
    "side_x_each": 0.010,
    "front_negative_y": 0.012,
    "back_positive_y": 0.014,
    "top_positive_z": 0.014,
    "minimum_shell_thickness": 0.0035,
}

contract = {
    "contract": "leonidas-head-fit",
    "version": 1,
    "source": {
        "asset": str(MODEL_PATH.relative_to(PROJECT_ROOT)).replace("\\", "/"),
        "object": HEAD_OBJECT_NAME,
        "armature": armature.name,
        "bone": HEAD_BONE_NAME,
        "scene_frame": 0,
        "pose": "REST",
        "units": MODEL_UNIT,
    },
    "coordinate_system": {
        "right": "+X",
        "left": "-X",
        "front": "-Y",
        "back": "+Y",
        "up": "+Z",
    },
    "mesh": {
        "vertices": len(head.data.vertices),
        "edges": len(head.data.edges),
        "polygons": len(head.data.polygons),
        "connected_component_sizes": connected_component_sizes(head.data),
    },
    "world": {
        "exact": dimensions(world_minimum, world_maximum),
        "robust_0_5_to_99_5_percent": dimensions(
            world_robust_minimum, world_robust_maximum
        ),
    },
    "head_bone_local": {
        "axes": {
            "right": "+X",
            "left": "-X",
            "up": "+Y",
            "front": "+Z",
            "back": "-Z",
        },
        "exact": dimensions(bone_minimum, bone_maximum),
        "robust_0_5_to_99_5_percent": dimensions(
            bone_robust_minimum, bone_robust_maximum
        ),
        "bone_to_world_matrix": matrix_rows(head_bone_world),
        "world_to_bone_matrix": matrix_rows(world_to_head_bone),
    },
    "design_reference": {
        "center_world": vector_list(robust_center),
        "center_head_bone_local": vector_list(
            (bone_robust_minimum + bone_robust_maximum) * 0.5
        ),
        "head_width": rounded(robust_size.x),
        "head_depth": rounded(robust_size.y),
        "head_height": rounded(robust_size.z),
        "horizontal_slices_world": horizontal_slices(
            world_points, world_robust_minimum.z, world_robust_maximum.z
        ),
        "clearance": clearance,
        "target_inner_width": rounded(
            robust_size.x + clearance["side_x_each"] * 2.0
        ),
        "target_inner_depth": rounded(
            robust_size.y
            + clearance["front_negative_y"]
            + clearance["back_positive_y"]
        ),
        "target_inner_top": rounded(
            world_robust_maximum.z + clearance["top_positive_z"]
        ),
    },
    "rules": [
        "La cabeza anatómica es la única referencia de ajuste.",
        "El casco original y los candidatos anteriores no aportan dimensiones.",
        "La simetría geométrica se mantiene respecto al eje X local del hueso Head.",
        "El rostro, la máscara y la cresta quedan fuera de la etapa de cúpula.",
        "Ninguna pieza se integra a producción sin aprobar vistas ortogonales.",
    ],
}

OUTPUT_PATH.parent.mkdir(parents=True, exist_ok=True)
OUTPUT_PATH.write_text(
    json.dumps(contract, ensure_ascii=False, indent=2) + "\n",
    encoding="utf-8",
)
print("LEONIDAS_HEAD_CONTRACT_BEGIN")
print(json.dumps(contract, ensure_ascii=False, indent=2))
print("LEONIDAS_HEAD_CONTRACT_END")
print("LEONIDAS_HEAD_CONTRACT_OUTPUT", OUTPUT_PATH)
