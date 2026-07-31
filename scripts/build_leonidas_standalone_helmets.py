"""Build two standalone, review-only Leonidas helmet candidates.

The assets produced here are intentionally not added to Leonidas' manifest.
They are independent GLBs for visual approval before any avatar integration.

Coordinate contract:
    X: left/right
    Y: back/front (front is negative Y)
    Z: up
"""

from __future__ import annotations

import math
import random
import sys
from pathlib import Path

import bmesh
import bpy
from mathutils import Vector


ROOT = Path(__file__).resolve().parents[1]
OUTPUT_ROOT = ROOT / "storage" / "leonidas-helmet-designs" / "v1"


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    for datablocks in (
        bpy.data.meshes,
        bpy.data.curves,
        bpy.data.materials,
        bpy.data.cameras,
        bpy.data.lights,
    ):
        for datablock in list(datablocks):
            if datablock.users == 0:
                datablocks.remove(datablock)


def material(
    name: str,
    base: tuple[float, float, float, float],
    metallic: float,
    roughness: float,
    noise_scale: float = 0.0,
    noise_strength: float = 0.0,
) -> bpy.types.Material:
    result = bpy.data.materials.new(name)
    result.use_nodes = True
    nodes = result.node_tree.nodes
    links = result.node_tree.links
    shader = nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = base
    shader.inputs["Metallic"].default_value = metallic
    shader.inputs["Roughness"].default_value = roughness
    if noise_scale > 0 and noise_strength > 0:
        noise = nodes.new("ShaderNodeTexNoise")
        noise.inputs["Scale"].default_value = noise_scale
        noise.inputs["Detail"].default_value = 4.2
        noise.inputs["Roughness"].default_value = 0.72
        bump = nodes.new("ShaderNodeBump")
        bump.inputs["Strength"].default_value = noise_strength
        bump.inputs["Distance"].default_value = 0.045
        links.new(noise.outputs["Fac"], bump.inputs["Height"])
        links.new(bump.outputs["Normal"], shader.inputs["Normal"])
    return result


def palette() -> dict[str, bpy.types.Material]:
    return {
        "dark_metal": material(
            "Aqueo_BlackenedIron",
            (0.042, 0.057, 0.076, 1.0),
            0.92,
            0.24,
            7.5,
            0.16,
        ),
        "dark_metal_soft": material(
            "Aqueo_BlackenedIronSoft",
            (0.065, 0.078, 0.094, 1.0),
            0.86,
            0.34,
            11.0,
            0.10,
        ),
        "bronze": material(
            "Atico_AgedBronze",
            (0.39, 0.17, 0.042, 1.0),
            0.90,
            0.31,
            8.0,
            0.18,
        ),
        "bronze_dark": material(
            "Atico_RecessedBronze",
            (0.14, 0.052, 0.015, 1.0),
            0.78,
            0.43,
            10.0,
            0.14,
        ),
        "gold": material(
            "Helmet_EdgeBronze",
            (0.38, 0.17, 0.035, 1.0),
            0.94,
            0.22,
            13.0,
            0.08,
        ),
        "cavity": material(
            "Helmet_InteriorShadow",
            (0.002, 0.003, 0.004, 1.0),
            0.05,
            0.92,
        ),
        "black_hair": material(
            "Aqueo_CrestBlack",
            (0.006, 0.008, 0.011, 1.0),
            0.0,
            0.88,
            18.0,
            0.20,
        ),
        "black_hair_alt": material(
            "Aqueo_CrestBlackHighlight",
            (0.018, 0.021, 0.025, 1.0),
            0.0,
            0.84,
        ),
        "red_hair": material(
            "Atico_CrestCrimson",
            (0.19, 0.004, 0.006, 1.0),
            0.0,
            0.84,
            15.0,
            0.16,
        ),
        "red_hair_alt": material(
            "Atico_CrestCrimsonHighlight",
            (0.34, 0.012, 0.008, 1.0),
            0.0,
            0.79,
        ),
        "bone": material(
            "Aqueo_VertebraeBronze",
            (0.31, 0.135, 0.025, 1.0),
            0.78,
            0.39,
            9.0,
            0.12,
        ),
    }


def smooth_bevel(
    obj: bpy.types.Object,
    width: float,
    segments: int = 3,
    smooth: bool = True,
) -> bpy.types.Object:
    if smooth:
        for polygon in obj.data.polygons:
            polygon.use_smooth = True
    if width > 0:
        modifier = obj.modifiers.new("EdgeSoftening", "BEVEL")
        modifier.width = width
        modifier.segments = segments
        modifier.limit_method = "ANGLE"
    return obj


def create_open_dome(
    name: str,
    mat: bpy.types.Material,
    *,
    rx: float = 0.70,
    ry: float = 0.76,
    rz: float = 0.84,
    center_z: float = 0.18,
    brow_z: float = 0.44,
    front_cut_y: float = -0.46,
    bottom_z: float = -0.06,
) -> bpy.types.Object:
    bpy.ops.mesh.primitive_uv_sphere_add(segments=96, ring_count=64)
    obj = bpy.context.object
    obj.name = name
    obj.scale = (rx, ry, rz)
    obj.location.z = center_z
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)

    mesh = bmesh.new()
    mesh.from_mesh(obj.data)
    remove = []
    for face in mesh.faces:
        center = face.calc_center_median()
        front_opening = center.y < front_cut_y and center.z < brow_z
        bottom_opening = center.z < bottom_z
        if front_opening or bottom_opening:
            remove.append(face)
    bmesh.ops.delete(mesh, geom=remove, context="FACES")
    loose = [vertex for vertex in mesh.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(mesh, geom=loose, context="VERTS")
    bmesh.ops.recalc_face_normals(mesh, faces=list(mesh.faces))
    mesh.to_mesh(obj.data)
    mesh.free()

    obj.data.materials.append(mat)
    solidify = obj.modifiers.new("ForgedThickness", "SOLIDIFY")
    solidify.thickness = 0.038
    solidify.offset = -0.64
    smooth_bevel(obj, 0.014, 3)
    return obj


def curved_depth(x: float, y_front: float, curvature: float) -> float:
    return y_front + curvature * x * x


def curved_plate(
    name: str,
    points: list[tuple[float, float]],
    y_front: float,
    depth: float,
    curvature: float,
    mat: bpy.types.Material,
    bevel: float = 0.014,
) -> bpy.types.Object:
    count = len(points)
    vertices = []
    for x, z in points:
        vertices.append((x, curved_depth(x, y_front, curvature), z))
    for x, z in points:
        vertices.append((x, curved_depth(x, y_front + depth, curvature), z))

    faces = [tuple(range(count)), tuple(range(count, count * 2))[::-1]]
    for index in range(count):
        nxt = (index + 1) % count
        faces.append((index, nxt, count + nxt, count + index))

    mesh = bpy.data.meshes.new(f"{name}Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.validate(verbose=False)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.collection.objects.link(obj)
    obj.data.materials.append(mat)
    triangulate = obj.modifiers.new("StableTriangulation", "TRIANGULATE")
    triangulate.keep_custom_normals = True
    smooth_bevel(obj, bevel, 3, smooth=False)
    return obj


def poly_curve(
    name: str,
    points: list[tuple[float, float, float]],
    bevel: float,
    mat: bpy.types.Material,
    cyclic: bool = False,
    resolution: int = 3,
) -> bpy.types.Object:
    data = bpy.data.curves.new(name, "CURVE")
    data.dimensions = "3D"
    data.resolution_u = resolution
    data.bevel_depth = bevel
    data.bevel_resolution = 3
    spline = data.splines.new("BEZIER")
    spline.bezier_points.add(len(points) - 1)
    for point, coordinate in zip(spline.bezier_points, points):
        point.co = coordinate
        point.handle_left_type = "AUTO"
        point.handle_right_type = "AUTO"
    spline.use_cyclic_u = cyclic
    obj = bpy.data.objects.new(name, data)
    bpy.context.collection.objects.link(obj)
    obj.data.materials.append(mat)
    return obj


def plate_trim(
    name: str,
    points: list[tuple[float, float]],
    y_front: float,
    curvature: float,
    mat: bpy.types.Material,
    bevel: float = 0.010,
    cyclic: bool = True,
) -> bpy.types.Object:
    path = [
        (x, curved_depth(x, y_front - 0.012, curvature), z)
        for x, z in points
    ]
    return poly_curve(name, path, bevel, mat, cyclic=cyclic)


def mirror_points(points: list[tuple[float, float]]) -> list[tuple[float, float]]:
    return [(-x, z) for x, z in points[::-1]]


def elliptical_band(
    name: str,
    rx: float,
    ry: float,
    z: float,
    height: float,
    thickness: float,
    mat: bpy.types.Material,
    segments: int = 128,
) -> bpy.types.Object:
    vertices = []
    for index in range(segments):
        angle = 2.0 * math.pi * index / segments
        c, s = math.cos(angle), math.sin(angle)
        for radial, vertical in (
            (0.0, -height * 0.5),
            (0.0, height * 0.5),
            (-thickness, -height * 0.5),
            (-thickness, height * 0.5),
        ):
            vertices.append(((rx + radial) * c, (ry + radial) * s, z + vertical))
    faces = []
    for index in range(segments):
        nxt = (index + 1) % segments
        a, b = index * 4, nxt * 4
        faces.extend(
            [
                (a, b, b + 1, a + 1),
                (a + 3, b + 3, b + 2, a + 2),
                (a + 1, b + 1, b + 3, a + 3),
                (a + 2, b + 2, b, a),
            ]
        )
    mesh = bpy.data.meshes.new(f"{name}Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.collection.objects.link(obj)
    obj.data.materials.append(mat)
    return smooth_bevel(obj, 0.008, 3)


def rear_skirt(
    name: str,
    rx: float,
    ry: float,
    top_z: float,
    bottom_z: float,
    thickness: float,
    mat: bpy.types.Material,
    segments: int = 72,
) -> bpy.types.Object:
    """Create the separate rear/side neck guard instead of a spherical lower half."""
    vertices = []
    for index in range(segments):
        t = index / (segments - 1)
        angle = math.pi * t
        c, s = math.cos(angle), math.sin(angle)
        bottom_relief = 0.08 * math.sin(math.pi * t)
        for radius_offset, z in (
            (0.0, top_z),
            (0.0, bottom_z - bottom_relief),
            (-thickness, top_z),
            (-thickness, bottom_z - bottom_relief),
        ):
            vertices.append(((rx + radius_offset) * c, (ry + radius_offset) * s, z))
    faces = []
    for index in range(segments - 1):
        a, b = index * 4, (index + 1) * 4
        faces.extend(
            [
                (a, b, b + 1, a + 1),
                (a + 3, b + 3, b + 2, a + 2),
                (a + 1, b + 1, b + 3, a + 3),
                (a + 2, b + 2, b, a),
            ]
        )
    faces.extend([(0, 1, 3, 2), (segments * 4 - 4, segments * 4 - 2, segments * 4 - 1, segments * 4 - 3)])
    mesh = bpy.data.meshes.new(f"{name}Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.collection.objects.link(obj)
    obj.data.materials.append(mat)
    return smooth_bevel(obj, 0.012, 3)


def crest_base_z(t: float) -> float:
    return 0.90 + 0.18 * math.sin(math.pi * t)


def crest_outer_z(t: float, height: float, rear_drop: float) -> float:
    return (
        1.08
        + height * (math.sin(math.pi * t) ** 0.34)
        - rear_drop * (t**4)
    )


def crest_ribbon(
    name: str,
    y_min: float,
    y_max: float,
    x_half: float,
    vertical_width: float,
    mat: bpy.types.Material,
    samples: int = 72,
) -> bpy.types.Object:
    centers = []
    for index in range(samples):
        t = index / (samples - 1)
        y = y_min + (y_max - y_min) * t
        centers.append(Vector((0.0, y, crest_base_z(t))))
    vertices = []
    for index, center in enumerate(centers):
        before = centers[max(0, index - 1)]
        after = centers[min(samples - 1, index + 1)]
        tangent = (after - before).normalized()
        normal = Vector((0.0, -tangent.z, tangent.y)).normalized()
        for x, offset in (
            (-x_half, -vertical_width * 0.5),
            (-x_half, vertical_width * 0.5),
            (x_half, -vertical_width * 0.5),
            (x_half, vertical_width * 0.5),
        ):
            point = center + normal * offset
            point.x = x
            vertices.append(tuple(point))
    faces = []
    for index in range(samples - 1):
        a, b = index * 4, (index + 1) * 4
        faces.extend(
            [
                (a, b, b + 1, a + 1),
                (a + 3, b + 3, b + 2, a + 2),
                (a + 1, b + 1, b + 3, a + 3),
                (a + 2, b + 2, b, a),
            ]
        )
    faces.extend([(0, 2, 3, 1), (samples * 4 - 4, samples * 4 - 3, samples * 4 - 1, samples * 4 - 2)])
    mesh = bpy.data.meshes.new(f"{name}Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.collection.objects.link(obj)
    obj.data.materials.append(mat)
    return smooth_bevel(obj, 0.012, 3)


def hair_fan(
    name: str,
    y_min: float,
    y_max: float,
    width: float,
    height: float,
    rear_drop: float,
    mats: tuple[bpy.types.Material, bpy.types.Material],
    seed: int,
    y_count: int = 74,
    layers: int = 7,
) -> bpy.types.Object:
    rng = random.Random(seed)
    data = bpy.data.curves.new(name, "CURVE")
    data.dimensions = "3D"
    data.resolution_u = 1
    data.bevel_depth = 0.0062
    data.bevel_resolution = 2
    data.materials.append(mats[0])
    data.materials.append(mats[1])
    for yi in range(y_count):
        t = yi / (y_count - 1)
        y = y_min + (y_max - y_min) * t
        base_z = crest_base_z(t) + 0.035
        outer_z = crest_outer_z(t, height, rear_drop)
        for layer in range(layers):
            x_factor = (layer / max(layers - 1, 1) - 0.5) * 2.0
            x = x_factor * width * (0.72 + 0.28 * math.sin(math.pi * t))
            jitter = (rng.random() - 0.5) * 0.028
            spline = data.splines.new("POLY")
            spline.points.add(4)
            spline.material_index = (yi + layer) % 2
            for point_index in range(5):
                p = point_index / 4.0
                bow = math.sin(math.pi * p)
                point_y = y + (0.035 * (t - 0.5) + jitter) * bow
                point_x = x + (rng.random() - 0.5) * 0.016 * bow
                point_z = base_z * (1.0 - p) + outer_z * p
                point_z += 0.035 * math.sin(math.pi * p) * math.sin(5.0 * t + layer)
                spline.points[point_index].co = (point_x, point_y, point_z, 1.0)
    obj = bpy.data.objects.new(name, data)
    bpy.context.collection.objects.link(obj)
    return obj


def curve_ellipse(
    name: str,
    rx: float,
    ry: float,
    z: float,
    bevel: float,
    mat: bpy.types.Material,
    waves: int = 0,
    wave_height: float = 0.0,
    samples: int = 160,
) -> bpy.types.Object:
    points = []
    for index in range(samples):
        angle = 2.0 * math.pi * index / samples
        wave = wave_height * math.sin(waves * angle) if waves else 0.0
        points.append((rx * math.cos(angle), ry * math.sin(angle), z + wave))
    return poly_curve(name, points, bevel, mat, cyclic=True)


def create_front_cavity(name: str, mat: bpy.types.Material) -> bpy.types.Object:
    """A shallow interior liner visible only through the front openings."""
    points = [(-0.55, 0.35), (0.55, 0.35), (0.48, -0.62), (0.22, -0.78), (-0.22, -0.78), (-0.48, -0.62)]
    return curved_plate(name, points, -0.53, 0.018, 0.14, mat, 0.004)


def create_bone_vertebrae(mats: dict[str, bpy.types.Material]) -> list[bpy.types.Object]:
    pieces = []
    for index in range(7):
        z = 0.70 - index * 0.17
        scale = 1.0 - index * 0.055
        bpy.ops.mesh.primitive_ico_sphere_add(subdivisions=3, radius=0.13)
        core = bpy.context.object
        core.name = f"Aqueo_Verterbra_{index + 1:02d}_Core"
        core.scale = (0.66 * scale, 0.30, 0.44 * scale)
        core.location = (0.0, 0.875, z)
        bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
        core.data.materials.append(mats["bone"])
        smooth_bevel(core, 0.006, 2)
        pieces.append(core)
        for side in (-1.0, 1.0):
            bpy.ops.mesh.primitive_ico_sphere_add(subdivisions=2, radius=0.083)
            knob = bpy.context.object
            knob.name = f"Aqueo_Verterbra_{index + 1:02d}_{'L' if side < 0 else 'R'}"
            knob.scale = (0.78, 0.34, 0.42)
            knob.location = (side * 0.105 * scale, 0.877, z + 0.012)
            knob.rotation_euler[1] = side * math.radians(14)
            bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
            knob.data.materials.append(mats["bone"])
            pieces.append(knob)
    return pieces


def create_aqueo(mats: dict[str, bpy.types.Material]) -> list[bpy.types.Object]:
    objects: list[bpy.types.Object] = []
    objects.append(create_front_cavity("Aqueo_InteriorLiner", mats["cavity"]))
    objects.append(
        create_open_dome(
            "Aqueo_Shell",
            mats["dark_metal_soft"],
            brow_z=0.52,
            front_cut_y=-0.48,
            bottom_z=-0.07,
        )
    )
    objects.append(rear_skirt("Aqueo_NeckGuard", 0.70, 0.76, 0.10, -0.45, 0.038, mats["dark_metal_soft"]))
    curvature = 0.34
    y_front = -0.775

    forehead = [
        (-0.46, 0.68),
        (-0.31, 0.82),
        (0.0, 1.00),
        (0.31, 0.82),
        (0.46, 0.68),
        (0.39, 0.43),
        (0.14, 0.34),
        (0.0, 0.42),
        (-0.14, 0.34),
        (-0.39, 0.43),
    ]
    objects.append(curved_plate("Aqueo_Forehead", forehead, y_front, 0.052, curvature, mats["dark_metal"], 0.012))
    objects.append(plate_trim("Aqueo_ForeheadTrim", forehead, y_front, curvature, mats["gold"], 0.009))

    brow_left = [
        (-0.59, 0.43),
        (-0.14, 0.32),
        (-0.13, 0.18),
        (-0.34, 0.14),
        (-0.57, 0.20),
        (-0.66, 0.31),
    ]
    brow_right = mirror_points(brow_left)
    cheek_left = [
        (-0.64, 0.10),
        (-0.31, 0.06),
        (-0.17, -0.03),
        (-0.15, -0.26),
        (-0.10, -0.76),
        (-0.42, -0.90),
        (-0.64, -0.68),
        (-0.70, -0.27),
    ]
    cheek_right = mirror_points(cheek_left)
    nose = [
        (-0.105, 0.39),
        (0.105, 0.39),
        (0.085, -0.24),
        (0.0, -0.34),
        (-0.085, -0.24),
    ]
    for name, points in (
        ("Aqueo_BrowL", brow_left),
        ("Aqueo_BrowR", brow_right),
        ("Aqueo_CheekL", cheek_left),
        ("Aqueo_CheekR", cheek_right),
        ("Aqueo_NoseGuard", nose),
    ):
        objects.append(curved_plate(name, points, y_front - 0.012, 0.055, curvature, mats["dark_metal"], 0.012))
        objects.append(plate_trim(f"{name}Trim", points, y_front - 0.012, curvature, mats["gold"], 0.008))

    # A real lower rim, independent from the face plates.
    rim_points = [
        (-0.66, -0.67),
        (-0.43, -0.91),
        (-0.10, -0.78),
        (0.10, -0.78),
        (0.43, -0.91),
        (0.66, -0.67),
    ]
    objects.append(plate_trim("Aqueo_LowerRim", rim_points, y_front + 0.01, curvature, mats["gold"], 0.012, cyclic=False))

    objects.append(crest_ribbon("Aqueo_CrestRail", -0.78, 0.90, 0.115, 0.18, mats["dark_metal"], 84))
    objects.append(hair_fan("Aqueo_CrestFibres", -0.82, 0.98, 0.34, 0.98, 0.46, (mats["black_hair"], mats["black_hair_alt"]), 911, y_count=68, layers=25))
    for offset in (-0.055, 0.0, 0.055):
        braid = []
        for index in range(48):
            t = index / 47.0
            y = -0.72 + 1.48 * t
            z = crest_base_z(t) + 0.22 + 0.62 * (math.sin(math.pi * t) ** 0.45)
            braid.append((offset + 0.008 * math.sin(index * 1.7), y - 0.012, z))
        objects.append(poly_curve(f"Aqueo_CrestBraid_{offset:+.3f}", braid, 0.012, mats["dark_metal_soft"], cyclic=False))
    objects.extend(create_bone_vertebrae(mats))
    objects.append(curve_ellipse("Aqueo_LowerBand", 0.704, 0.764, 0.07, 0.014, mats["gold"]))
    return objects


def create_atico(mats: dict[str, bpy.types.Material]) -> list[bpy.types.Object]:
    objects: list[bpy.types.Object] = []
    objects.append(create_front_cavity("Atico_InteriorLiner", mats["cavity"]))
    objects.append(
        create_open_dome(
            "Atico_Shell",
            mats["bronze"],
            brow_z=0.40,
            front_cut_y=-0.43,
            bottom_z=-0.06,
        )
    )
    objects.append(rear_skirt("Atico_NeckGuard", 0.70, 0.76, 0.11, -0.42, 0.038, mats["bronze"]))
    objects.append(elliptical_band("Atico_BrowBand", 0.712, 0.772, 0.34, 0.14, 0.032, mats["bronze_dark"]))
    objects.append(curve_ellipse("Atico_BandTopTrim", 0.719, 0.779, 0.41, 0.010, mats["gold"]))
    objects.append(curve_ellipse("Atico_BandBottomTrim", 0.719, 0.779, 0.27, 0.010, mats["gold"]))
    objects.append(curve_ellipse("Atico_BandOrnament", 0.724, 0.784, 0.34, 0.007, mats["gold"], waves=12, wave_height=0.024))

    curvature = 0.30
    y_front = -0.755
    cheek_left = [
        (-0.61, 0.17),
        (-0.42, 0.12),
        (-0.34, -0.04),
        (-0.34, -0.56),
        (-0.43, -0.80),
        (-0.56, -0.68),
        (-0.64, -0.24),
    ]
    cheek_right = mirror_points(cheek_left)
    for name, points in (("Atico_CheekL", cheek_left), ("Atico_CheekR", cheek_right)):
        objects.append(curved_plate(name, points, y_front, 0.052, curvature, mats["bronze"], 0.014))
        objects.append(plate_trim(f"{name}Trim", points, y_front, curvature, mats["gold"], 0.010))

    # Small side neck guards, separate from the dome and cheek guards.
    side_guard = [(-0.72, 0.16), (-0.62, 0.05), (-0.61, -0.43), (-0.73, -0.55)]
    for name, points in (("Atico_SideGuardL", side_guard), ("Atico_SideGuardR", mirror_points(side_guard))):
        objects.append(curved_plate(name, points, -0.28, 0.075, 0.48, mats["bronze_dark"], 0.012))

    objects.append(crest_ribbon("Atico_CrestRail", -0.78, 0.92, 0.105, 0.20, mats["bronze_dark"], 88))
    objects.append(hair_fan("Atico_CrestFibres", -0.86, 1.00, 0.34, 0.90, 0.34, (mats["red_hair"], mats["red_hair_alt"]), 300, y_count=72, layers=27))
    # Raised rails make the side silhouette match the approved concept.
    for x in (-0.118, 0.118):
        points = []
        for index in range(72):
            t = index / 71.0
            y = -0.76 + 1.62 * t
            points.append((x, y, crest_base_z(t) + 0.105))
        objects.append(poly_curve(f"Atico_CrestRailTrim_{x:+.3f}", points, 0.012, mats["gold"], cyclic=False))
    return objects


def convert_curves_to_mesh(objects: list[bpy.types.Object]) -> None:
    for obj in list(objects):
        if obj.type != "CURVE":
            continue
        bpy.context.view_layer.objects.active = obj
        obj.select_set(True)
        bpy.ops.object.convert(target="MESH")
        obj.select_set(False)


def export_candidate(name: str, builder) -> Path:
    clear_scene()
    mats = palette()
    objects = builder(mats)
    convert_curves_to_mesh(objects)

    collection = bpy.data.collections.new(f"{name}_Collection")
    bpy.context.scene.collection.children.link(collection)
    for obj in objects:
        for source_collection in list(obj.users_collection):
            source_collection.objects.unlink(obj)
        collection.objects.link(obj)
        obj["leonidasCandidate"] = True
        obj["leonidasHelmetDesign"] = name

    output = OUTPUT_ROOT / f"{name}.glb"
    output.parent.mkdir(parents=True, exist_ok=True)
    bpy.ops.object.select_all(action="DESELECT")
    for obj in objects:
        obj.hide_viewport = False
        obj.hide_render = False
        obj.select_set(True)
    bpy.context.view_layer.objects.active = objects[0]
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
    print(f"HELMET_CANDIDATE {name} objects={len(objects)} output={output}")
    return output


def main() -> None:
    requested = sys.argv[sys.argv.index("--") + 1 :] if "--" in sys.argv else []
    target = requested[0] if requested else "all"
    if target in ("all", "aqueo"):
        export_candidate("helmet-aqueo-oscuro-standalone-v2", create_aqueo)
    if target in ("all", "atico"):
        export_candidate("helmet-atico-clasico-standalone-v2", create_atico)
    if target not in ("all", "aqueo", "atico"):
        raise SystemExit("Target must be all, aqueo, or atico")


if __name__ == "__main__":
    main()
