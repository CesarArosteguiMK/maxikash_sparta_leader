"""Project four approved concept views onto a reconstructed helmet mesh.

This creates vertex colors, not a camera-facing decal. Every surface receives
color from the view whose camera direction best matches its normal.
"""

from __future__ import annotations

import argparse
import json
import math
import sys
from pathlib import Path

import bpy
import numpy as np
from mathutils import Vector


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("input_glb", type=Path)
    parser.add_argument("views_dir", type=Path)
    parser.add_argument("output_glb", type=Path)
    parser.add_argument("--target-faces", type=int, default=180_000)
    parser.add_argument("--palette", choices=("aqueo", "atico"), required=True)
    argv = sys.argv[sys.argv.index("--") + 1 :] if "--" in sys.argv else []
    return parser.parse_args(argv)


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def load_image(path: Path) -> tuple[np.ndarray, tuple[int, int, int, int], np.ndarray]:
    image = bpy.data.images.load(str(path), check_existing=False)
    width, height = image.size
    values = np.empty(width * height * 4, dtype=np.float32)
    image.pixels.foreach_get(values)
    # Blender stores rows bottom-to-top. Flip to conventional image coordinates.
    rgb = values.reshape((height, width, 4))[::-1, :, :3]

    side = max(4, int(width * 0.055))
    left = rgb[:, :side].mean(axis=1)
    right = rgb[:, -side:].mean(axis=1)
    progress = np.linspace(0.0, 1.0, width, dtype=np.float32)[None, :, None]
    background = left[:, None, :] * (1.0 - progress) + right[:, None, :] * progress
    difference = np.linalg.norm(rgb - background, axis=2)
    luminance = rgb @ np.array([0.2126, 0.7152, 0.0722], dtype=np.float32)
    bg_luminance = background @ np.array([0.2126, 0.7152, 0.0722], dtype=np.float32)
    saturation = rgb.max(axis=2) - rgb.min(axis=2)
    mask = (
        (difference > 0.050)
        | (np.abs(luminance - bg_luminance) > 0.045)
        | (saturation > 0.16)
    )
    ys, xs = np.where(mask)
    if len(xs) < 100:
        bbox = (0, 0, width - 1, height - 1)
    else:
        x0, x1 = np.percentile(xs, (0.7, 99.3))
        y0, y1 = np.percentile(ys, (0.7, 99.3))
        pad_x = width * 0.012
        pad_y = height * 0.012
        bbox = (
            max(0, int(math.floor(x0 - pad_x))),
            max(0, int(math.floor(y0 - pad_y))),
            min(width - 1, int(math.ceil(x1 + pad_x))),
            min(height - 1, int(math.ceil(y1 + pad_y))),
        )
    return rgb, bbox, background


def bilinear(image: np.ndarray, x: float, y: float) -> np.ndarray:
    height, width, _ = image.shape
    x = min(width - 1.0, max(0.0, x))
    y = min(height - 1.0, max(0.0, y))
    x0, y0 = int(math.floor(x)), int(math.floor(y))
    x1, y1 = min(width - 1, x0 + 1), min(height - 1, y0 + 1)
    tx, ty = x - x0, y - y0
    return (
        image[y0, x0] * (1.0 - tx) * (1.0 - ty)
        + image[y0, x1] * tx * (1.0 - ty)
        + image[y1, x0] * (1.0 - tx) * ty
        + image[y1, x1] * tx * ty
    )


def bilinear_many(image: np.ndarray, x: np.ndarray, y: np.ndarray) -> np.ndarray:
    height, width, _ = image.shape
    x = np.clip(x, 0.0, width - 1.0)
    y = np.clip(y, 0.0, height - 1.0)
    x0 = np.floor(x).astype(np.int32)
    y0 = np.floor(y).astype(np.int32)
    x1 = np.minimum(width - 1, x0 + 1)
    y1 = np.minimum(height - 1, y0 + 1)
    tx = (x - x0)[:, None]
    ty = (y - y0)[:, None]
    return (
        image[y0, x0] * (1.0 - tx) * (1.0 - ty)
        + image[y0, x1] * tx * (1.0 - ty)
        + image[y1, x0] * (1.0 - tx) * ty
        + image[y1, x1] * tx * ty
    )


def sample_view_many(
    view_name: str,
    coordinates: np.ndarray,
    minimum: np.ndarray,
    maximum: np.ndarray,
    image: np.ndarray,
    bbox: tuple[int, int, int, int],
) -> np.ndarray:
    spans = np.maximum(maximum - minimum, 1e-6)
    if view_name == "front":
        u = (coordinates[:, 0] - minimum[0]) / spans[0]
    elif view_name == "back":
        u = (maximum[0] - coordinates[:, 0]) / spans[0]
    elif view_name == "right":
        u = (coordinates[:, 1] - minimum[1]) / spans[1]
    else:
        u = (maximum[1] - coordinates[:, 1]) / spans[1]
    v = (maximum[2] - coordinates[:, 2]) / spans[2]
    x0, y0, x1, y1 = bbox
    x = x0 + np.clip(u, 0.0, 1.0) * (x1 - x0)
    y = y0 + np.clip(v, 0.0, 1.0) * (y1 - y0)
    return bilinear_many(image, x, y)


def sample_view(
    view_name: str,
    point: Vector,
    bounds: tuple[Vector, Vector],
    image: np.ndarray,
    bbox: tuple[int, int, int, int],
) -> np.ndarray:
    minimum, maximum = bounds
    if view_name == "front":
        u = (point.x - minimum.x) / max(maximum.x - minimum.x, 1e-6)
    elif view_name == "back":
        u = (maximum.x - point.x) / max(maximum.x - minimum.x, 1e-6)
    elif view_name == "right":
        u = (point.y - minimum.y) / max(maximum.y - minimum.y, 1e-6)
    else:  # left
        u = (maximum.y - point.y) / max(maximum.y - minimum.y, 1e-6)
    v = (maximum.z - point.z) / max(maximum.z - minimum.z, 1e-6)
    x0, y0, x1, y1 = bbox
    x = x0 + min(1.0, max(0.0, u)) * (x1 - x0)
    y = y0 + min(1.0, max(0.0, v)) * (y1 - y0)
    return bilinear(image, x, y)


def normal_weights(normal: Vector) -> dict[str, float]:
    # Directions point from the model toward the four cameras.
    raw = {
        "front": max(0.0, -normal.y),
        "right": max(0.0, normal.x),
        "back": max(0.0, normal.y),
        "left": max(0.0, -normal.x),
    }
    powered = {name: value**5 for name, value in raw.items()}
    total = sum(powered.values())
    if total < 1e-8:
        return {"front": 0.25, "right": 0.25, "back": 0.25, "left": 0.25}
    return {name: value / total for name, value in powered.items()}


def create_vertex_material(name: str) -> bpy.types.Material:
    material = bpy.data.materials.new(name)
    material.use_nodes = True
    nodes = material.node_tree.nodes
    links = material.node_tree.links
    shader = nodes.get("Principled BSDF")
    vertex = nodes.new("ShaderNodeVertexColor")
    vertex.layer_name = "HelmetProjectedColor"
    links.new(vertex.outputs["Color"], shader.inputs["Base Color"])
    shader.inputs["Metallic"].default_value = 0.72
    shader.inputs["Roughness"].default_value = 0.39
    return material


def main() -> None:
    args = parse_args()
    source = args.input_glb.resolve()
    views_dir = args.views_dir.resolve()
    output = args.output_glb.resolve()
    output.parent.mkdir(parents=True, exist_ok=True)

    clear_scene()
    bpy.ops.import_scene.gltf(filepath=str(source))
    meshes = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
    if not meshes:
        raise RuntimeError("No mesh found")
    if len(meshes) > 1:
        bpy.ops.object.select_all(action="DESELECT")
        for obj in meshes:
            obj.select_set(True)
        bpy.context.view_layer.objects.active = max(meshes, key=lambda item: len(item.data.polygons))
        bpy.ops.object.join()
    obj = next(item for item in bpy.context.scene.objects if item.type == "MESH")
    obj.name = f"LeonidasHelmet_{args.palette.title()}_Candidate"

    face_count = len(obj.data.polygons)
    if face_count > args.target_faces:
        print(f"DECIMATE_START faces={face_count} target={args.target_faces}", flush=True)
        modifier = obj.modifiers.new("ReviewDecimation", "DECIMATE")
        modifier.ratio = max(0.01, args.target_faces / face_count)
        modifier.use_collapse_triangulate = True
        bpy.context.view_layer.objects.active = obj
        bpy.ops.object.modifier_apply(modifier=modifier.name)
        print(f"DECIMATE_DONE faces={len(obj.data.polygons)}", flush=True)

    obj.data.transform(obj.matrix_world)
    obj.matrix_world.identity()
    obj.data.update()
    vertex_count = len(obj.data.vertices)
    coordinates = np.empty(vertex_count * 3, dtype=np.float32)
    normals = np.empty(vertex_count * 3, dtype=np.float32)
    obj.data.vertices.foreach_get("co", coordinates)
    obj.data.vertices.foreach_get("normal", normals)
    coordinates = coordinates.reshape((-1, 3))
    normals = normals.reshape((-1, 3))
    normal_lengths = np.linalg.norm(normals, axis=1, keepdims=True)
    normals /= np.maximum(normal_lengths, 1e-8)
    minimum_array = coordinates.min(axis=0)
    maximum_array = coordinates.max(axis=0)
    minimum = Vector(tuple(float(value) for value in minimum_array))
    maximum = Vector(tuple(float(value) for value in maximum_array))

    views = {}
    metadata = {}
    for view_name in ("front", "right", "back", "left"):
        path = views_dir / f"{view_name}.png"
        image, bbox, background = load_image(path)
        views[view_name] = (image, bbox, background)
        metadata[view_name] = {"path": str(path), "bbox": list(bbox), "size": [image.shape[1], image.shape[0]]}

    obj.data.calc_loop_triangles()
    obj.data.color_attributes.remove(obj.data.color_attributes.get("HelmetProjectedColor")) if obj.data.color_attributes.get("HelmetProjectedColor") else None
    colors = obj.data.color_attributes.new(
        name="HelmetProjectedColor",
        type="BYTE_COLOR",
        domain="POINT",
    )
    fallback = np.array((0.035, 0.045, 0.060) if args.palette == "aqueo" else (0.31, 0.13, 0.035), dtype=np.float32)
    raw_weights = np.stack(
        (
            np.maximum(0.0, -normals[:, 1]),
            np.maximum(0.0, normals[:, 0]),
            np.maximum(0.0, normals[:, 1]),
            np.maximum(0.0, -normals[:, 0]),
        ),
        axis=1,
    ) ** 5
    weight_totals = raw_weights.sum(axis=1, keepdims=True)
    flat = weight_totals[:, 0] < 1e-8
    raw_weights[flat] = 0.25
    weight_totals[flat] = 1.0
    weights = raw_weights / weight_totals
    color = np.zeros((vertex_count, 3), dtype=np.float32)
    accepted = np.zeros((vertex_count, 1), dtype=np.float32)
    red_evidence = np.zeros(vertex_count, dtype=np.float32)
    for view_index, view_name in enumerate(("front", "right", "back", "left")):
        image, bbox, _background = views[view_name]
        sampled = sample_view_many(view_name, coordinates, minimum_array, maximum_array, image, bbox)
        luminance = sampled @ np.array([0.2126, 0.7152, 0.0722], dtype=np.float32)
        valid = (luminance >= 0.006).astype(np.float32)[:, None]
        weighted = weights[:, view_index : view_index + 1] * valid
        color += sampled * weighted
        accepted += weighted
        if args.palette == "atico":
            red_score = np.clip(
                (sampled[:, 0] - np.maximum(sampled[:, 1], sampled[:, 2]) - 0.012) / 0.11,
                0.0,
                1.0,
            )
            red_score *= valid[:, 0]
            red_score *= 0.30 + 0.70 * np.sqrt(weights[:, view_index])
            red_evidence = np.maximum(red_evidence, red_score)
    valid_total = accepted[:, 0] > 1e-5
    color[valid_total] /= accepted[valid_total]
    color[~valid_total] = fallback
    luminance = color @ np.array([0.2126, 0.7152, 0.0722], dtype=np.float32)
    if args.palette == "aqueo":
        gold = (
            (color[:, 0] > color[:, 1] * 1.12)
            & (color[:, 1] > color[:, 2] * 1.12)
            & (luminance > 0.055)
        )
        iron = ~gold
        iron_level = np.clip(luminance, 0.025, 0.23)
        iron_target = np.stack(
            (iron_level * 0.62, iron_level * 0.78, iron_level),
            axis=1,
        )
        color[iron] = color[iron] * 0.22 + iron_target[iron] * 0.78
        color = np.maximum(color, np.array((0.008, 0.010, 0.014), dtype=np.float32))
    else:
        cavity = luminance < 0.018
        hair_strength = np.clip((red_evidence - 0.16) / 0.50, 0.0, 1.0)
        hair_strength = hair_strength * hair_strength * (3.0 - 2.0 * hair_strength)
        metal = ~cavity
        bronze_level = np.clip(luminance * 0.92 + 0.025, 0.045, 0.56)
        bronze_target = np.stack(
            (bronze_level, bronze_level * 0.50, bronze_level * 0.19),
            axis=1,
        )
        color[metal] = color[metal] * 0.30 + bronze_target[metal] * 0.70
        hair_level = np.clip(luminance * 0.68 + 0.045, 0.055, 0.34)
        hair_target = np.stack(
            (hair_level, hair_level * 0.115, hair_level * 0.075),
            axis=1,
        )
        color = color * (1.0 - hair_strength[:, None]) + hair_target * hair_strength[:, None]
        color[cavity] = np.minimum(color[cavity], np.array((0.012, 0.009, 0.006), dtype=np.float32))
    rgba = np.ones((vertex_count, 4), dtype=np.float32)
    rgba[:, :3] = np.clip(color, 0.0, 1.0)
    colors.data.foreach_set("color", rgba.reshape(-1))
    print(f"PROJECTION_DONE vertices={vertex_count}", flush=True)

    obj.data.materials.clear()
    obj.data.materials.append(create_vertex_material(f"LeonidasHelmet_{args.palette.title()}_ProjectedPBR"))
    obj["leonidasCandidate"] = True
    obj["leonidasHelmetProjection"] = "four-view-vertex-color"
    obj["leonidasHelmetIntegrated"] = False
    obj["leonidasHelmetPalette"] = args.palette

    bpy.ops.object.select_all(action="DESELECT")
    obj.select_set(True)
    bpy.context.view_layer.objects.active = obj
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
    metadata["mesh"] = {
        "vertices": len(obj.data.vertices),
        "faces": len(obj.data.polygons),
        "bounds_min": list(minimum),
        "bounds_max": list(maximum),
    }
    metadata_path = output.with_suffix(".projection.json")
    metadata_path.write_text(json.dumps(metadata, indent=2), encoding="utf-8")
    print(f"PROJECTED_HELMET output={output} metadata={metadata_path} faces={len(obj.data.polygons)}")


if __name__ == "__main__":
    main()
