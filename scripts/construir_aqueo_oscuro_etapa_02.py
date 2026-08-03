"""Reconstruye el Áqueo oscuro sobre la cúpula calibrada de Leónidas.

Esta etapa no reutiliza la superficie del candidato v7. Conserva la cúpula
aprobada y añade una máscara fina y una cresta sagital independientes. El GLB
resultante es exclusivamente QA; no se modifica el manifiesto productivo.
"""

from __future__ import annotations

import json
import math
import os
from pathlib import Path

import bpy
from mathutils import Vector


ROOT = Path(__file__).resolve().parents[1]
DOME = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "leonidas-helmet-dome-stage-01.glb"
LEONIDAS = ROOT / "public" / "assets" / "models" / "leonidas" / "leonidas-spartan-modular-v2.glb"
OUTPUT_ROOT = ROOT / "storage" / "leonidas-helmet-designs" / "aqueo-dark-stage-02"
OUTPUT_GLB = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "leonidas-aqueo-dark-stage-02.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-stage-02.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-stage-02-report.json"

CX = -0.01588049
MASK_FRONT_Y = -0.0405


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def make_material(
    name: str,
    color: tuple[float, float, float, float],
    metallic: float,
    roughness: float,
    *,
    noise_scale: float = 0.0,
    bump_strength: float = 0.0,
    bump_distance: float = 0.0005,
) -> bpy.types.Material:
    mat = bpy.data.materials.new(name)
    mat.diffuse_color = color
    mat.metallic = metallic
    mat.roughness = roughness
    mat.use_nodes = True
    nodes = mat.node_tree.nodes
    links = mat.node_tree.links
    shader = nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = color
    shader.inputs["Metallic"].default_value = metallic
    shader.inputs["Roughness"].default_value = roughness
    if noise_scale and bump_strength:
        noise = nodes.new("ShaderNodeTexNoise")
        noise.inputs["Scale"].default_value = noise_scale
        noise.inputs["Detail"].default_value = 3.2
        noise.inputs["Roughness"].default_value = 0.58
        bump = nodes.new("ShaderNodeBump")
        bump.inputs["Strength"].default_value = bump_strength
        bump.inputs["Distance"].default_value = bump_distance
        links.new(noise.outputs["Fac"], bump.inputs["Height"])
        links.new(bump.outputs["Normal"], shader.inputs["Normal"])
    return mat


def palette() -> dict[str, bpy.types.Material]:
    return {
        "steel": make_material(
            "AqueoDark_BlackenedSteel",
            (0.018, 0.026, 0.040, 1.0),
            0.86,
            0.29,
            noise_scale=115.0,
            bump_strength=0.075,
            bump_distance=0.00032,
        ),
        "steel_soft": make_material(
            "AqueoDark_SoftSteel",
            (0.030, 0.041, 0.058, 1.0),
            0.82,
            0.34,
            noise_scale=85.0,
            bump_strength=0.045,
            bump_distance=0.00028,
        ),
        "edge": make_material(
            "AqueoDark_AgedBronzeEdge",
            (0.16, 0.070, 0.018, 1.0),
            0.82,
            0.33,
            noise_scale=92.0,
            bump_strength=0.04,
            bump_distance=0.00018,
        ),
        "hair": make_material(
            "AqueoDark_CrestHair",
            (0.0015, 0.0022, 0.0035, 1.0),
            0.05,
            0.88,
            noise_scale=190.0,
            bump_strength=0.20,
            bump_distance=0.00050,
        ),
        "hair_highlight": make_material(
            "AqueoDark_CrestHairHighlight",
            (0.006, 0.008, 0.011, 1.0),
            0.05,
            0.84,
        ),
    }


def mask_y(x: float, z: float, offset: float = 0.0) -> float:
    """Frente convexa continua que envuelve la cara sin tocar la piel."""
    side = abs(x - CX) / 0.082
    vertical = max(0.0, min(1.0, (1.255 - z) / 0.15))
    return MASK_FRONT_Y + 0.0105 * side * side + 0.0022 * vertical + offset


def plate(
    name: str,
    points: list[tuple[float, float]],
    mat: bpy.types.Material,
    *,
    thickness: float = 0.0034,
    bevel: float = 0.0014,
) -> bpy.types.Object:
    count = len(points)
    front = [(x, mask_y(x, z, -0.0008), z) for x, z in points]
    back = [(x, mask_y(x, z, thickness), z) for x, z in points]
    verts = front + back
    faces: list[tuple[int, ...]] = [tuple(range(count)), tuple(range(count, count * 2))[::-1]]
    for index in range(count):
        following = (index + 1) % count
        faces.append((index, following, following + count, index + count))
    mesh = bpy.data.meshes.new(f"{name}_Mesh")
    mesh.from_pydata(verts, [], faces)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.scene.collection.objects.link(obj)
    obj.data.materials.append(mat)
    bevel_modifier = obj.modifiers.new("ForgedEdge", "BEVEL")
    bevel_modifier.width = bevel
    bevel_modifier.segments = 3
    bevel_modifier.limit_method = "ANGLE"
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=bevel_modifier.name)
    obj.select_set(False)
    for polygon in obj.data.polygons:
        polygon.use_smooth = False
    return obj


def curve_object(
    name: str,
    points: list[tuple[float, float, float]],
    radius: float,
    mat: bpy.types.Material,
    *,
    cyclic: bool = False,
    resolution: int = 2,
) -> bpy.types.Object:
    curve = bpy.data.curves.new(f"{name}_Curve", "CURVE")
    curve.dimensions = "3D"
    curve.resolution_u = resolution
    curve.bevel_depth = radius
    curve.bevel_resolution = 3
    spline = curve.splines.new("BEZIER")
    spline.bezier_points.add(len(points) - 1)
    for point, coordinate in zip(spline.bezier_points, points):
        point.co = coordinate
        point.handle_left_type = "AUTO"
        point.handle_right_type = "AUTO"
    spline.use_cyclic_u = cyclic
    obj = bpy.data.objects.new(name, curve)
    bpy.context.scene.collection.objects.link(obj)
    obj.data.materials.append(mat)
    return obj


def trim(name: str, points: list[tuple[float, float]], mat: bpy.types.Material) -> bpy.types.Object:
    coordinates = [(x, mask_y(x, z, -0.0023), z) for x, z in points]
    return curve_object(name, coordinates, 0.00052, mat, cyclic=True)


def mirrored(points: list[tuple[float, float]]) -> list[tuple[float, float]]:
    return [(2.0 * CX - x, z) for x, z in points][::-1]


def dome_front_y(x: float, z: float, offset: float = -0.0012) -> float:
    """Superficie frontal de la elipsoide exterior aprobada."""
    center_y = 0.0509225
    center_z = 1.259187
    rx = 0.092608
    ry = 0.084356
    rz = 0.080192
    radial = ((x - CX) / rx) ** 2 + ((z - center_z) / rz) ** 2
    return center_y - ry * math.sqrt(max(0.025, 1.0 - radial)) + offset


def forehead_patch(name: str, mat: bpy.types.Material) -> bpy.types.Object:
    """Placa convexa que continúa la cúpula y termina sobre las cejas."""
    outline = [
        (CX - 0.071, 1.254),
        (CX - 0.057, 1.282),
        (CX - 0.027, 1.302),
        (CX, 1.310),
        (CX + 0.027, 1.302),
        (CX + 0.057, 1.282),
        (CX + 0.071, 1.254),
        (CX + 0.061, 1.242),
        (CX + 0.029, 1.235),
        (CX + 0.014, 1.226),
        (CX, 1.239),
        (CX - 0.014, 1.226),
        (CX - 0.029, 1.235),
        (CX - 0.061, 1.242),
    ]
    center = (CX, dome_front_y(CX, 1.266, -0.0030), 1.266)
    front = [(x, dome_front_y(x, z, -0.0020), z) for x, z in outline]
    back = [(x, dome_front_y(x, z, 0.0015), z) for x, z in outline]
    count = len(outline)
    vertices = [center] + front + back
    front_start = 1
    back_start = 1 + count
    faces: list[tuple[int, ...]] = []
    for index in range(count):
        following = (index + 1) % count
        faces.append((0, front_start + index, front_start + following))
        faces.append((back_start + index, back_start + following, back_start + ((index + 2) % count)))
        faces.append((front_start + index, back_start + index, back_start + following, front_start + following))
    mesh = bpy.data.meshes.new(f"{name}_Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.scene.collection.objects.link(obj)
    obj.data.materials.append(mat)
    bevel = obj.modifiers.new("ForgedEdge", "BEVEL")
    bevel.width = 0.0012
    bevel.segments = 3
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=bevel.name)
    obj.select_set(False)
    for polygon in obj.data.polygons:
        polygon.use_smooth = True
    return obj


def create_mask(materials: dict[str, bpy.types.Material]) -> list[bpy.types.Object]:
    nose = [
        (CX - 0.012, 1.244),
        (CX + 0.012, 1.244),
        (CX + 0.010, 1.188),
        (CX, 1.174),
        (CX - 0.010, 1.188),
    ]
    guard_left = [
        (CX - 0.088, 1.246),
        (CX - 0.062, 1.236),
        (CX - 0.025, 1.232),
        (CX - 0.014, 1.225),
        (CX - 0.020, 1.216),
        (CX - 0.044, 1.222),
        (CX - 0.068, 1.224),
        (CX - 0.071, 1.201),
        (CX - 0.071, 1.170),
        (CX - 0.072, 1.145),
        (CX - 0.081, 1.130),
        (CX - 0.091, 1.146),
        (CX - 0.097, 1.184),
        (CX - 0.097, 1.224),
    ]

    shapes = [
        ("AqueoDark_MaskGuardL", guard_left),
        ("AqueoDark_MaskGuardR", mirrored(guard_left)),
        ("AqueoDark_MaskNose", nose),
    ]
    created: list[bpy.types.Object] = [forehead_patch("AqueoDark_MaskForehead", materials["steel"])]
    for name, points in shapes:
        created.append(plate(name, points, materials["steel"]))
        created.append(trim(f"{name}_Trim", points, materials["edge"]))

    return created


def profile_slab(
    name: str,
    yz_points: list[tuple[float, float]],
    half_widths: list[float],
    mat: bpy.types.Material,
) -> bpy.types.Object:
    if len(yz_points) != len(half_widths):
        raise ValueError("Cada punto del perfil requiere un semiancho")
    left = [(CX - width, y, z) for (y, z), width in zip(yz_points, half_widths)]
    right = [(CX + width, y, z) for (y, z), width in zip(yz_points, half_widths)]
    count = len(yz_points)
    vertices = left + right
    faces: list[tuple[int, ...]] = [tuple(range(count)), tuple(range(count, count * 2))[::-1]]
    for index in range(count):
        following = (index + 1) % count
        faces.append((index, following, following + count, index + count))
    mesh = bpy.data.meshes.new(f"{name}_Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.scene.collection.objects.link(obj)
    obj.data.materials.append(mat)
    bevel = obj.modifiers.new("SoftHairSilhouette", "BEVEL")
    bevel.width = 0.0025
    bevel.segments = 3
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=bevel.name)
    obj.select_set(False)
    for polygon in obj.data.polygons:
        polygon.use_smooth = True
    return obj


def crest_base_z(y: float) -> float:
    # Sigue la coronilla aprobada, no el volumen del candidato antiguo.
    t = max(0.0, min(1.0, (y + 0.020) / 0.205))
    return 1.337 + 0.025 * math.sin(math.pi * t) - 0.036 * (t**1.7)


def crest_top_z(y: float) -> float:
    t = max(0.0, min(1.0, (y + 0.030) / 0.225))
    return crest_base_z(y) + 0.060 + 0.026 * math.sin(math.pi * t)


def create_crest(materials: dict[str, bpy.types.Material]) -> list[bpy.types.Object]:
    created: list[bpy.types.Object] = []
    rail_points = []
    for index in range(30):
        y = -0.020 + 0.205 * index / 29.0
        rail_points.append((CX, y, crest_base_z(y)))
    created.append(curve_object("AqueoDark_CrestRail", rail_points, 0.0055, materials["steel_soft"], resolution=3))
    created.append(
        curve_object(
            "AqueoDark_CrestRailTrim",
            [(x, y - 0.001, z + 0.0034) for x, y, z in rail_points],
            0.0010,
            materials["edge"],
            resolution=3,
        )
    )

    # Una sola malla de curvas densas. El penacho es pelo, no una placa.
    hair_curve = bpy.data.curves.new("AqueoDark_CrestHairFibres_Curve", "CURVE")
    hair_curve.dimensions = "3D"
    hair_curve.resolution_u = 2
    hair_curve.bevel_depth = 0.00052
    hair_curve.bevel_resolution = 2
    for longitudinal in range(62):
        t = longitudinal / 61.0
        root_y = -0.023 + 0.216 * t
        root_z = crest_base_z(root_y) + 0.001
        # Silueta más alta al centro y progresivamente descendente atrás.
        height = 0.055 + 0.032 * math.sin(math.pi * t)
        for lateral_index in range(7):
            lateral = (lateral_index - 3) / 3.0
            x = CX + lateral * (0.0128 - 0.0025 * abs(2.0 * t - 1.0))
            phase = longitudinal * 0.71 + lateral_index * 1.37
            lean = 0.009 + 0.013 * t
            tip_z = root_z + height * (1.0 - 0.045 * abs(lateral))
            spline = hair_curve.splines.new("BEZIER")
            spline.bezier_points.add(3)
            points = [
                (x, root_y, root_z),
                (x + 0.0007 * math.sin(phase), root_y + lean * 0.34, root_z + height * 0.34),
                (x - 0.0006 * math.cos(phase), root_y + lean * 0.68, root_z + height * 0.69),
                (x + 0.0005 * math.sin(phase * 1.4), root_y + lean, tip_z),
            ]
            for point, coordinate in zip(spline.bezier_points, points):
                point.co = coordinate
                point.handle_left_type = "AUTO"
                point.handle_right_type = "AUTO"
    hair_obj = bpy.data.objects.new("AqueoDark_CrestHairFibres", hair_curve)
    bpy.context.scene.collection.objects.link(hair_obj)
    hair_obj.data.materials.append(materials["hair"])
    created.append(hair_obj)
    return created


def convert_curves(objects: list[bpy.types.Object]) -> None:
    for obj in list(objects):
        if obj.type != "CURVE":
            continue
        bpy.context.view_layer.objects.active = obj
        obj.select_set(True)
        bpy.ops.object.convert(target="MESH")
        obj.select_set(False)


def object_bounds(objects: list[bpy.types.Object]) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for obj in objects for corner in obj.bound_box]
    return (
        Vector(tuple(min(point[axis] for point in corners) for axis in range(3))),
        Vector(tuple(max(point[axis] for point in corners) for axis in range(3))),
    )


def look_at(obj: bpy.types.Object, target: Vector) -> None:
    obj.rotation_euler = (target - obj.location).to_track_quat("-Z", "Y").to_euler()


def add_area(name: str, location: tuple[float, float, float], energy: float, size: float, target: Vector) -> None:
    data = bpy.data.lights.new(name, "AREA")
    data.energy = energy
    data.shape = "DISK"
    data.size = size
    obj = bpy.data.objects.new(name, data)
    bpy.context.scene.collection.objects.link(obj)
    obj.location = location
    look_at(obj, target)


def render_views(helmet_asset: Path) -> None:
    clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(LEONIDAS))
    original = set(bpy.context.scene.objects)
    by_name = {obj.name: obj for obj in original}
    for hidden in ("LeonidasHelmet", "LeonidasHair", "LeonidasShield", "LeonidasSpear"):
        if hidden in by_name:
            by_name[hidden].hide_render = True
            by_name[hidden].hide_viewport = True
    bpy.context.scene.frame_set(0)
    bpy.ops.import_scene.gltf(filepath=os.fspath(helmet_asset))
    helmet_objects = [obj for obj in bpy.context.scene.objects if obj not in original and obj.type == "MESH"]
    for obj in helmet_objects:
        if obj.name == "Aqueo_FitReference":
            obj.hide_render = True
            obj.hide_viewport = True

    scene = bpy.context.scene
    scene.render.engine = "BLENDER_EEVEE"
    scene.render.resolution_x = 760
    scene.render.resolution_y = 900
    scene.render.resolution_percentage = 100
    scene.render.image_settings.file_format = "PNG"
    scene.render.film_transparent = False
    scene.view_settings.look = "AgX - Medium High Contrast"
    scene.view_settings.exposure = -0.85
    scene.world.color = (0.010, 0.016, 0.027)

    camera_data = bpy.data.cameras.new("AqueoDarkStage02Camera")
    camera_data.type = "ORTHO"
    camera = bpy.data.objects.new("AqueoDarkStage02Camera", camera_data)
    scene.collection.objects.link(camera)
    scene.camera = camera
    light_target = Vector((CX, 0.035, 1.24))
    add_area("Key", (-1.7, -2.3, 2.35), 360.0, 1.55, light_target)
    add_area("Fill", (1.55, -1.25, 1.75), 130.0, 1.25, light_target)
    add_area("Rim", (0.3, 1.85, 2.1), 290.0, 1.2, light_target)
    add_area("Top", (-0.2, 0.15, 3.1), 110.0, 1.0, light_target)

    def render(name: str, target: Vector, direction: Vector, scale: float) -> None:
        camera.data.ortho_scale = scale
        camera.location = target + direction.normalized() * 3.0
        look_at(camera, target)
        scene.render.filepath = os.fspath(OUTPUT_ROOT / f"{name}.png")
        bpy.ops.render.render(write_still=True)

    head = Vector((CX, 0.055, 1.252))
    render("01-full-front", Vector((0.0, 0.03, 0.92)), Vector((0.0, -1.0, 0.02)), 1.95)
    render("02-head-front", head, Vector((0.0, -1.0, 0.01)), 0.48)
    render("03-head-three-quarter", head, Vector((0.70, -1.0, 0.06)), 0.50)
    render("04-head-profile", head, Vector((1.0, 0.0, 0.02)), 0.52)
    render("05-head-back", head, Vector((0.0, 1.0, 0.02)), 0.52)
    render("06-head-other-profile", head, Vector((-1.0, 0.0, 0.02)), 0.52)

    minimum, maximum = object_bounds(helmet_objects)
    report = {
        "status": "qa-candidate-not-production",
        "stage": "aqueo-dark-stage-02",
        "architecture": ["approved-calibrated-dome", "separate-face-mask", "separate-sagittal-crest"],
        "sourceDome": str(DOME.relative_to(ROOT)).replace("\\", "/"),
        "outputAsset": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "legacyV7GeometryReused": False,
        "helmetBoundsWorld": {
            "min": [round(value, 6) for value in minimum],
            "max": [round(value, 6) for value in maximum],
            "extent": [round(value, 6) for value in maximum - minimum],
        },
        "designLimits": {
            "approvedDomeExteriorWidthMeters": 0.185215,
            "maskMaximumFrontProjectionMeters": abs(MASK_FRONT_Y - (-0.033433)),
            "crestMaximumWidthMeters": 0.031,
            "crestMaximumHeightWorldMeters": 1.447,
        },
        "views": [
            "01-full-front.png",
            "02-head-front.png",
            "03-head-three-quarter.png",
            "04-head-profile.png",
            "05-head-back.png",
            "06-head-other-profile.png",
        ],
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    materials = palette()
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    imported = list(bpy.context.scene.objects)
    proxy = next((obj for obj in imported if obj.name.startswith("LeonidasHeadFitProxy")), None)
    shell = next((obj for obj in imported if obj.name.startswith("LeonidasHelmetDomeStage01")), None)
    if shell is None:
        raise RuntimeError("No se encontró la cúpula calibrada de etapa 01")
    if proxy is not None:
        bpy.data.objects.remove(proxy, do_unlink=True)
    shell.name = "AqueoDark_ShellApprovedDome"
    shell.data.materials.clear()
    shell.data.materials.append(materials["steel_soft"])
    helmet_objects: list[bpy.types.Object] = [shell]
    helmet_objects.extend(create_mask(materials))
    helmet_objects.extend(create_crest(materials))
    convert_curves(helmet_objects)
    helmet_objects = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
    for obj in helmet_objects:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-stage-02"
        obj["leonidasProductionReady"] = False
        obj["leonidasHeadBone"] = "mixamorig:Head"

    bpy.ops.object.select_all(action="DESELECT")
    for obj in helmet_objects:
        obj.select_set(True)
    bpy.ops.export_scene.gltf(
        filepath=os.fspath(OUTPUT_GLB),
        export_format="GLB",
        use_selection=True,
        export_animations=False,
        export_skins=False,
        export_morph=False,
        export_extras=True,
        export_yup=True,
    )
    bpy.ops.wm.save_as_mainfile(filepath=os.fspath(OUTPUT_BLEND))
    render_views(OUTPUT_GLB)
    print(f"AQUEO_DARK_STAGE_02_GLB {OUTPUT_GLB}")
    print(f"AQUEO_DARK_STAGE_02_REPORT {REPORT}")


if __name__ == "__main__":
    main()
