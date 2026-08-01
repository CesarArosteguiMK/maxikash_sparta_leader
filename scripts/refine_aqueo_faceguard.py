"""Build a true three-dimensional faceguard for the Achaean helmet sculpt.

The multiview reconstruction is retained for the dome, crest and rear spine.
Only the ambiguous facial area is covered by a deliberately modelled assembly:
matte inner padding, separate brow/cheek plates, a nose guard and metal edging.
Nothing in this script touches the Leonidas avatar or its active manifests.
"""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

import bpy
import numpy as np


SOURCE_COORDINATES: np.ndarray | None = None


def args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("input_glb", type=Path)
    parser.add_argument("output_glb", type=Path)
    argv = sys.argv[sys.argv.index("--") + 1 :] if "--" in sys.argv else []
    return parser.parse_args(argv)


def material(name: str, color: tuple[float, float, float, float], metallic: float, roughness: float):
    mat = bpy.data.materials.new(name)
    mat.diffuse_color = color
    mat.use_nodes = True
    shader = mat.node_tree.nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = color
    shader.inputs["Metallic"].default_value = metallic
    shader.inputs["Roughness"].default_value = roughness
    return mat


def front_y(x: float, z: float, offset: float = 0.0) -> float:
    """Continuous fitted envelope for the reconstructed facial shell.

    Measurements from the source place the forehead around -0.84, the lower
    centre around -0.68 and the temples around -0.50. A smooth quadratic wrap
    preserves those depths without the folds produced by per-vertex snapping.
    """
    vertical = min(1.0, max(0.0, (z + 0.75) / 1.30))
    centre = -0.68 - 0.16 * vertical
    temple_recession = 2.20 * x * x
    return centre + temple_recession + offset


def plate(name: str, points: list[tuple[float, float]], depth: float, mat) -> bpy.types.Object:
    count = len(points)
    front = [(x, front_y(x, z, -0.018), z) for x, z in points]
    back = [(x, front_y(x, z, depth), z) for x, z in points]
    vertices = front + back
    faces: list[tuple[int, ...]] = [tuple(range(count)), tuple(range(count, count * 2))[::-1]]
    for index in range(count):
        following = (index + 1) % count
        faces.append((index, following, following + count, index + count))
    mesh = bpy.data.meshes.new(f"{name}_Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.collection.objects.link(obj)
    obj.data.materials.append(mat)
    bevel = obj.modifiers.new("ForgedEdge", "BEVEL")
    bevel.width = 0.010
    bevel.segments = 3
    bevel.limit_method = "ANGLE"
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=bevel.name)
    obj.select_set(False)
    return obj


def trim(name: str, points: list[tuple[float, float]], mat, radius: float = 0.007) -> bpy.types.Object:
    curve = bpy.data.curves.new(f"{name}_Curve", "CURVE")
    curve.dimensions = "3D"
    curve.resolution_u = 2
    curve.bevel_depth = radius
    curve.bevel_resolution = 3
    spline = curve.splines.new("POLY")
    spline.points.add(len(points))
    closed = points + [points[0]]
    for target, (x, z) in zip(spline.points, closed):
        target.co = (x, front_y(x, z, -0.029), z, 1.0)
    obj = bpy.data.objects.new(name, curve)
    bpy.context.collection.objects.link(obj)
    obj.data.materials.append(mat)
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.convert(target="MESH")
    obj.select_set(False)
    return obj


def mirrored(points: list[tuple[float, float]]) -> list[tuple[float, float]]:
    return [(-x, z) for x, z in points][::-1]


def main() -> None:
    global SOURCE_COORDINATES
    options = args()
    source = options.input_glb.resolve()
    output = options.output_glb.resolve()
    output.parent.mkdir(parents=True, exist_ok=True)

    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    bpy.ops.import_scene.gltf(filepath=str(source))
    original = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
    if not original:
        raise RuntimeError("The source GLB contains no mesh")
    source_obj = max(original, key=lambda item: len(item.data.vertices))
    coordinates = np.empty(len(source_obj.data.vertices) * 3, dtype=np.float32)
    source_obj.data.vertices.foreach_get("co", coordinates)
    SOURCE_COORDINATES = coordinates.reshape((-1, 3))

    iron = material("Aqueo_Blackened_Iron", (0.018, 0.026, 0.040, 1.0), 0.86, 0.27)
    padding = material("Aqueo_Inner_Shadow", (0.0025, 0.003, 0.0045, 1.0), 0.05, 0.91)
    gold = material("Aqueo_Aged_Gold_Edge", (0.34, 0.19, 0.055, 1.0), 0.78, 0.31)

    inner = [
        (-0.292, 0.285), (0.292, 0.285), (0.342, 0.065), (0.325, -0.610),
        (0.190, -0.790), (0.0, -0.875), (-0.190, -0.790), (-0.325, -0.610),
        (-0.342, 0.065),
    ]
    forehead = [
        (-0.302, 0.430), (0.0, 0.545), (0.302, 0.430), (0.284, 0.205),
        (0.108, 0.120), (0.0, 0.205), (-0.108, 0.120), (-0.284, 0.205),
    ]
    brow_left = [(-0.303, 0.205), (-0.075, 0.139), (-0.063, 0.045), (-0.190, -0.018), (-0.333, 0.035)]
    nose = [(-0.052, 0.188), (0.052, 0.188), (0.045, -0.285), (0.0, -0.410), (-0.045, -0.285)]
    cheek_left = [
        (-0.334, 0.018), (-0.192, -0.026), (-0.095, -0.105), (-0.100, -0.505),
        (-0.180, -0.725), (-0.296, -0.807), (-0.353, -0.630),
    ]

    created = [plate("Faceguard_Inner_Padding", inner, 0.022, padding)]
    for name, shape in (
        ("Faceguard_Forehead", forehead),
        ("Faceguard_Brow_L", brow_left),
        ("Faceguard_Brow_R", mirrored(brow_left)),
        ("Faceguard_Nose", nose),
        ("Faceguard_Cheek_L", cheek_left),
        ("Faceguard_Cheek_R", mirrored(cheek_left)),
    ):
        created.append(plate(name, shape, 0.030, iron))
        created.append(trim(f"{name}_GoldTrim", shape, gold))

    for obj in original:
        obj["leonidasHelmetIntegrated"] = False
        obj["leonidasHelmetCandidate"] = True
    for obj in created:
        obj["leonidasFaceguardPart"] = True
        obj["leonidasHelmetIntegrated"] = False

    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.export_scene.gltf(
        filepath=str(output),
        export_format="GLB",
        use_selection=True,
        export_animations=False,
        export_skins=False,
        export_morph=False,
        export_extras=True,
        export_yup=True,
    )
    print(f"AQUEO_FACEGUARD_OUTPUT {output}")


if __name__ == "__main__":
    main()
