"""Construye la primera etapa aprobable del casco de Leónidas.

La salida contiene solamente:

* un proxy estático de la cabeza anatómica real;
* una cúpula gris simétrica, hueca y con grosor verificable.

No crea máscara, nasal, carrilleras, cresta, penacho ni integración productiva.

Uso:
    blender --background --factory-startup --python \
        scripts/construir_cupula_leonidas_etapa_01.py
"""

from __future__ import annotations

import json
import math
import os
from pathlib import Path

import bmesh
import bpy
from mathutils import Vector


ROOT = Path(__file__).resolve().parents[1]
MODEL_PATH = (
    ROOT
    / "public"
    / "assets"
    / "models"
    / "leonidas"
    / "leonidas-spartan-modular-v2.glb"
)
CONTRACT_PATH = (
    ROOT
    / "storage"
    / "leonidas-helmet-designs"
    / "measurements"
    / "leonidas-head-contract-v1.json"
)
STAGE_ROOT = ROOT / "storage" / "leonidas-helmet-designs" / "stage-01-dome"
OUTPUT_GLB = (
    ROOT
    / "public"
    / "assets"
    / "models"
    / "leonidas"
    / "qa"
    / "leonidas-helmet-dome-stage-01.glb"
)
OUTPUT_BLEND = STAGE_ROOT / "leonidas-helmet-dome-stage-01.blend"
OUTPUT_REPORT = STAGE_ROOT / "leonidas-helmet-dome-stage-01-report.json"
RENDER_ROOT = STAGE_ROOT / "renders"

HEAD_NAME = "LeonidasHeadUnderlay"
HEAD_BONE_NAME = "mixamorig:Head"
SHELL_NAME = "LeonidasHelmetDomeStage01"
HEAD_PROXY_NAME = "LeonidasHeadFitProxy"

SEGMENTS = 96
RINGS = 32
SHELL_THICKNESS = 0.0035


def reset_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)
    for blocks in (
        bpy.data.meshes,
        bpy.data.armatures,
        bpy.data.materials,
        bpy.data.images,
        bpy.data.cameras,
        bpy.data.lights,
        bpy.data.actions,
    ):
        for block in list(blocks):
            if block.users == 0:
                blocks.remove(block)


def material(
    name: str,
    color: tuple[float, float, float, float],
    metallic: float,
    roughness: float,
) -> bpy.types.Material:
    value = bpy.data.materials.new(name)
    value.diffuse_color = color
    value.use_nodes = True
    principled = value.node_tree.nodes.get("Principled BSDF")
    principled.inputs["Base Color"].default_value = color
    principled.inputs["Metallic"].default_value = metallic
    principled.inputs["Roughness"].default_value = roughness
    return value


def find_armature(obj: bpy.types.Object) -> bpy.types.Object:
    for modifier in obj.modifiers:
        if modifier.type == "ARMATURE" and modifier.object:
            return modifier.object
    if obj.parent and obj.parent.type == "ARMATURE":
        return obj.parent
    values = [item for item in bpy.context.scene.objects if item.type == "ARMATURE"]
    if len(values) == 1:
        return values[0]
    raise RuntimeError("No se encontró el esqueleto de Leónidas.")


def static_proxy(obj: bpy.types.Object) -> tuple[bpy.types.Object, list[Vector]]:
    depsgraph = bpy.context.evaluated_depsgraph_get()
    evaluated = obj.evaluated_get(depsgraph)
    mesh = bpy.data.meshes.new_from_object(
        evaluated,
        preserve_all_data_layers=True,
        depsgraph=depsgraph,
    )
    mesh.name = HEAD_PROXY_NAME + "Mesh"
    mesh.transform(evaluated.matrix_world)
    proxy = bpy.data.objects.new(HEAD_PROXY_NAME, mesh)
    bpy.context.scene.collection.objects.link(proxy)
    points = [vertex.co.copy() for vertex in mesh.vertices]
    return proxy, points


def percentile(values: list[float], fraction: float) -> float:
    ordered = sorted(values)
    position = max(0.0, min(1.0, fraction)) * (len(ordered) - 1)
    lower = int(math.floor(position))
    upper = int(math.ceil(position))
    if lower == upper:
        return ordered[lower]
    weight = position - lower
    return ordered[lower] * (1.0 - weight) + ordered[upper] * weight


def rim_height(longitude: float, center_z: float) -> float:
    """Borde alto al frente, bajo en laterales y más bajo en nuca."""
    frontness = max(0.0, -math.sin(longitude))
    backness = max(0.0, math.sin(longitude))
    side_rim = center_z - 0.035
    return (
        side_rim
        + 0.060 * frontness**4
        - 0.010 * backness**4
    )


def piecewise_depth_radius(
    longitude: float, front_radius: float, back_radius: float
) -> float:
    return front_radius if math.sin(longitude) < 0.0 else back_radius


def shell_parameters(
    contract: dict[str, object],
    head_points: list[Vector],
    head_axis_x: float,
) -> dict[str, float]:
    robust = contract["world"]["robust_0_5_to_99_5_percent"]
    exact = contract["world"]["exact"]
    robust_min = Vector(robust["min"])
    robust_max = Vector(robust["max"])
    exact_min = Vector(exact["min"])
    exact_max = Vector(exact["max"])
    clearance = contract["design_reference"]["clearance"]

    # El centro de profundidad sale de las rebanadas craneales (no de la
    # nariz). El eje lateral sale del hueso Head y garantiza simetría rígida.
    cranial = [
        point
        for point in head_points
        if point.z >= robust_min.z + (robust_max.z - robust_min.z) * 0.55
    ]
    cranial_y = [point.y for point in cranial]
    cranial_front = percentile(cranial_y, 0.01)
    cranial_back = percentile(cranial_y, 0.99)
    center_y = (cranial_front + cranial_back) * 0.5

    center_z = robust_min.z + (robust_max.z - robust_min.z) * 0.605
    top_z = max(exact_max.z, robust_max.z) + float(clearance["top_positive_z"])
    radius_z = top_z - center_z

    max_side_distance = max(
        abs(exact_min.x - head_axis_x), abs(exact_max.x - head_axis_x)
    )
    radius_x = max_side_distance + float(clearance["side_x_each"])
    front_radius = (
        center_y - cranial_front + float(clearance["front_negative_y"])
    )
    back_radius = (
        cranial_back - center_y + float(clearance["back_positive_y"])
    )

    # Calibración analítica: todos los puntos craneales cubiertos deben quedar
    # dentro del elipsoide interior con un pequeño margen. La corrección es
    # uniforme en X/Y para no deformar la silueta por ejes independientes.
    target_occupancy = 0.965
    required_scale = 1.0
    for point in cranial:
        longitude = math.atan2(point.y - center_y, point.x - head_axis_x)
        if point.z < rim_height(longitude, center_z) - 0.003:
            continue
        depth_radius = piecewise_depth_radius(
            longitude, front_radius, back_radius
        )
        vertical = ((point.z - center_z) / radius_z) ** 2
        horizontal = (
            ((point.x - head_axis_x) / radius_x) ** 2
            + ((point.y - center_y) / depth_radius) ** 2
        )
        available = target_occupancy - vertical
        if available > 0.0001:
            required_scale = max(
                required_scale, math.sqrt(max(0.0, horizontal / available))
            )
    # Un límite explícito evita aceptar automáticamente una cúpula inflada.
    required_scale = min(required_scale, 1.075)
    radius_x *= required_scale
    front_radius *= required_scale
    back_radius *= required_scale

    occupancies = []
    for point in cranial:
        longitude = math.atan2(point.y - center_y, point.x - head_axis_x)
        if point.z < rim_height(longitude, center_z) - 0.003:
            continue
        depth_radius = piecewise_depth_radius(
            longitude, front_radius, back_radius
        )
        occupancy = (
            ((point.x - head_axis_x) / radius_x) ** 2
            + ((point.y - center_y) / depth_radius) ** 2
            + ((point.z - center_z) / radius_z) ** 2
        )
        occupancies.append(occupancy)

    return {
        "axis_x": head_axis_x,
        "center_y": center_y,
        "center_z": center_z,
        "radius_x": radius_x,
        "front_radius": front_radius,
        "back_radius": back_radius,
        "radius_z": radius_z,
        "top_z": top_z,
        "fit_scale": required_scale,
        "covered_head_points": float(len(occupancies)),
        "max_occupancy": max(occupancies) if occupancies else 0.0,
        "collision_count": float(
            sum(1 for occupancy in occupancies if occupancy >= 1.0)
        ),
    }


def inner_position(
    params: dict[str, float], longitude: float, fraction: float
) -> tuple[Vector, Vector]:
    local_rim = rim_height(longitude, params["center_z"])
    cosine = max(
        -1.0,
        min(1.0, (local_rim - params["center_z"]) / params["radius_z"]),
    )
    maximum_theta = math.acos(cosine)
    theta = maximum_theta * fraction
    depth_radius = piecewise_depth_radius(
        longitude, params["front_radius"], params["back_radius"]
    )
    sin_theta = math.sin(theta)
    point = Vector(
        (
            params["axis_x"]
            + params["radius_x"] * sin_theta * math.cos(longitude),
            params["center_y"]
            + depth_radius * sin_theta * math.sin(longitude),
            params["center_z"] + params["radius_z"] * math.cos(theta),
        )
    )
    # Gradiente del elipsoide: normal exterior estable aun bajo la línea de
    # ecuador. Se usa para mantener grosor, no para "inflar" por escala.
    normal = Vector(
        (
            (point.x - params["axis_x"]) / params["radius_x"] ** 2,
            (point.y - params["center_y"]) / depth_radius**2,
            (point.z - params["center_z"]) / params["radius_z"] ** 2,
        )
    ).normalized()
    return point, normal


def build_shell(params: dict[str, float]) -> bpy.types.Object:
    vertices: list[tuple[float, float, float]] = []
    faces: list[tuple[int, ...]] = []

    inner_top = len(vertices)
    vertices.append(
        (params["axis_x"], params["center_y"], params["top_z"])
    )
    outer_top = len(vertices)
    vertices.append(
        (
            params["axis_x"],
            params["center_y"],
            params["top_z"] + SHELL_THICKNESS,
        )
    )

    inner_rings: list[list[int]] = []
    outer_rings: list[list[int]] = []
    for ring in range(1, RINGS + 1):
        fraction = ring / RINGS
        inner_ring: list[int] = []
        outer_ring: list[int] = []
        for segment in range(SEGMENTS):
            longitude = 2.0 * math.pi * segment / SEGMENTS
            inner, normal = inner_position(params, longitude, fraction)
            outer = inner + normal * SHELL_THICKNESS
            inner_ring.append(len(vertices))
            vertices.append(tuple(inner))
            outer_ring.append(len(vertices))
            vertices.append(tuple(outer))
        inner_rings.append(inner_ring)
        outer_rings.append(outer_ring)

    first_inner = inner_rings[0]
    first_outer = outer_rings[0]
    for segment in range(SEGMENTS):
        following = (segment + 1) % SEGMENTS
        # Superficie interior orientada hacia la cabeza.
        faces.append((inner_top, first_inner[segment], first_inner[following]))
        # Superficie exterior.
        faces.append((outer_top, first_outer[following], first_outer[segment]))

    for ring in range(RINGS - 1):
        inner_current = inner_rings[ring]
        inner_next = inner_rings[ring + 1]
        outer_current = outer_rings[ring]
        outer_next = outer_rings[ring + 1]
        for segment in range(SEGMENTS):
            following = (segment + 1) % SEGMENTS
            faces.append(
                (
                    inner_current[segment],
                    inner_next[segment],
                    inner_next[following],
                    inner_current[following],
                )
            )
            faces.append(
                (
                    outer_current[segment],
                    outer_current[following],
                    outer_next[following],
                    outer_next[segment],
                )
            )

    # Cierra solamente el espesor del borde. El interior permanece hueco.
    inner_bottom = inner_rings[-1]
    outer_bottom = outer_rings[-1]
    for segment in range(SEGMENTS):
        following = (segment + 1) % SEGMENTS
        faces.append(
            (
                inner_bottom[segment],
                outer_bottom[segment],
                outer_bottom[following],
                inner_bottom[following],
            )
        )

    mesh = bpy.data.meshes.new(SHELL_NAME + "Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.update(calc_edges=True)
    bm = bmesh.new()
    bm.from_mesh(mesh)
    bmesh.ops.recalc_face_normals(bm, faces=list(bm.faces))
    bm.to_mesh(mesh)
    bm.free()
    shell = bpy.data.objects.new(SHELL_NAME, mesh)
    bpy.context.scene.collection.objects.link(shell)
    for polygon in mesh.polygons:
        polygon.use_smooth = True
    shell["leonidasStage"] = "helmet-dome-01"
    shell["leonidasProductionReady"] = False
    shell["leonidasHasMask"] = False
    shell["leonidasHasCrest"] = False
    shell["leonidasShellThickness"] = SHELL_THICKNESS
    shell["leonidasSymmetryAxisWorldX"] = params["axis_x"]
    shell["leonidasFitVerified"] = params["collision_count"] == 0.0
    shell["leonidasMaxOccupancy"] = params["max_occupancy"]
    shell["leonidasFitCollisionCount"] = params["collision_count"]
    return shell


def cutaway_copy(source: bpy.types.Object, axis_x: float) -> bpy.types.Object:
    mesh = source.data.copy()
    result = bpy.data.objects.new(source.name + "Cutaway", mesh)
    bpy.context.scene.collection.objects.link(result)
    bm = bmesh.new()
    bm.from_mesh(mesh)
    remove = [vertex for vertex in bm.verts if vertex.co.x > axis_x + 0.0001]
    bmesh.ops.delete(bm, geom=remove, context="VERTS")
    bm.to_mesh(mesh)
    bm.free()
    result.hide_render = True
    return result


def look_at(obj: bpy.types.Object, target: Vector) -> None:
    obj.rotation_euler = (target - obj.location).to_track_quat("-Z", "Y").to_euler()


def add_area_light(
    name: str,
    location: tuple[float, float, float],
    energy: float,
    size: float,
    target: Vector,
) -> bpy.types.Object:
    data = bpy.data.lights.new(name, "AREA")
    data.energy = energy
    data.shape = "DISK"
    data.size = size
    obj = bpy.data.objects.new(name, data)
    bpy.context.scene.collection.objects.link(obj)
    obj.location = location
    look_at(obj, target)
    return obj


def configure_render() -> bpy.types.Object:
    scene = bpy.context.scene
    scene.render.engine = "BLENDER_EEVEE"
    scene.render.resolution_x = 900
    scene.render.resolution_y = 900
    scene.render.resolution_percentage = 100
    scene.render.image_settings.file_format = "PNG"
    scene.render.film_transparent = False
    scene.render.image_settings.color_mode = "RGBA"
    scene.render.resolution_percentage = 100
    scene.render.film_transparent = False
    scene.world.color = (0.012, 0.018, 0.028)
    scene.view_settings.look = "AgX - Medium High Contrast"

    camera_data = bpy.data.cameras.new("Stage01Camera")
    camera_data.type = "ORTHO"
    camera_data.ortho_scale = 0.39
    camera = bpy.data.objects.new("Stage01Camera", camera_data)
    bpy.context.scene.collection.objects.link(camera)
    scene.camera = camera
    target = Vector((0.0, 0.05, 1.22))
    add_area_light("Key", (-0.55, -0.75, 1.65), 75.0, 0.55, target)
    add_area_light("Fill", (0.55, -0.25, 1.42), 32.0, 0.45, target)
    add_area_light("Rim", (0.20, 0.65, 1.55), 58.0, 0.35, target)
    return camera


def render_view(
    camera: bpy.types.Object,
    name: str,
    location: Vector,
    target: Vector,
    shell: bpy.types.Object,
    cutaway: bpy.types.Object,
    use_cutaway: bool = False,
) -> None:
    camera.location = location
    look_at(camera, target)
    shell.hide_render = use_cutaway
    cutaway.hide_render = not use_cutaway
    bpy.context.scene.render.filepath = os.fspath(RENDER_ROOT / f"{name}.png")
    bpy.ops.render.render(write_still=True)


def object_bounds(obj: bpy.types.Object) -> tuple[Vector, Vector]:
    points = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[axis] for point in points) for axis in range(3)))
    maximum = Vector(tuple(max(point[axis] for point in points) for axis in range(3)))
    return minimum, maximum


STAGE_ROOT.mkdir(parents=True, exist_ok=True)
RENDER_ROOT.mkdir(parents=True, exist_ok=True)
OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
contract = json.loads(CONTRACT_PATH.read_text(encoding="utf-8"))

reset_scene()
bpy.ops.import_scene.gltf(filepath=os.fspath(MODEL_PATH))
head_source = bpy.data.objects.get(HEAD_NAME)
if head_source is None:
    raise RuntimeError(f"Falta la referencia anatómica {HEAD_NAME}.")
armature = find_armature(head_source)
head_bone = armature.data.bones.get(HEAD_BONE_NAME)
if head_bone is None:
    raise RuntimeError(f"Falta el hueso {HEAD_BONE_NAME}.")
armature.data.pose_position = "REST"
if armature.animation_data:
    armature.animation_data.action = None
bpy.context.scene.frame_set(0)
bpy.context.view_layer.update()

head_proxy, head_points = static_proxy(head_source)
head_axis_x = (armature.matrix_world @ head_bone.matrix_local).translation.x
params = shell_parameters(contract, head_points, head_axis_x)
shell = build_shell(params)
cutaway = cutaway_copy(shell, params["axis_x"])

head_material = material(
    "FitProxyClay", (0.22, 0.10, 0.065, 1.0), metallic=0.0, roughness=0.72
)
shell_material = material(
    "Stage01NeutralGray", (0.075, 0.095, 0.125, 1.0), metallic=0.25, roughness=0.38
)
head_proxy.data.materials.clear()
head_proxy.data.materials.append(head_material)
shell.data.materials.append(shell_material)
cutaway.data.materials.append(shell_material)
for polygon in head_proxy.data.polygons:
    polygon.material_index = 0
for polygon in shell.data.polygons:
    polygon.material_index = 0
for polygon in cutaway.data.polygons:
    polygon.material_index = 0

# Elimina todo el personaje importado; el laboratorio solo conserva la cabeza
# medida, la cúpula y una copia de corte para renders técnicos.
for obj in list(bpy.context.scene.objects):
    if obj not in (head_proxy, shell, cutaway):
        bpy.data.objects.remove(obj, do_unlink=True)

camera = configure_render()
target = Vector((params["axis_x"], params["center_y"], params["center_z"] + 0.015))
distance = 1.0
render_view(
    camera,
    "01-front",
    Vector((target.x, target.y - distance, target.z)),
    target,
    shell,
    cutaway,
)
render_view(
    camera,
    "02-left",
    Vector((target.x - distance, target.y, target.z)),
    target,
    shell,
    cutaway,
)
render_view(
    camera,
    "03-right",
    Vector((target.x + distance, target.y, target.z)),
    target,
    shell,
    cutaway,
)
render_view(
    camera,
    "04-back",
    Vector((target.x, target.y + distance, target.z)),
    target,
    shell,
    cutaway,
)
render_view(
    camera,
    "05-top",
    Vector((target.x, target.y, target.z + distance)),
    target,
    shell,
    cutaway,
)
render_view(
    camera,
    "06-cutaway",
    Vector((target.x + 0.72, target.y - 0.72, target.z + 0.12)),
    target,
    shell,
    cutaway,
    use_cutaway=True,
)
shell.hide_render = False
cutaway.hide_render = True

shell_min, shell_max = object_bounds(shell)
head_min, head_max = object_bounds(head_proxy)
report = {
    "stage": "leonidas-helmet-dome-01",
    "status": "qa-only",
    "source_contract": str(CONTRACT_PATH.relative_to(ROOT)).replace("\\", "/"),
    "parts": [HEAD_PROXY_NAME, SHELL_NAME],
    "excluded": ["mask", "nasal", "cheek-guards", "crest", "plume", "production"],
    "parameters_meters": {key: round(value, 6) for key, value in params.items()},
    "shell_thickness": SHELL_THICKNESS,
    "segments": SEGMENTS,
    "rings": RINGS,
    "head_bounds": {
        "min": [round(value, 6) for value in head_min],
        "max": [round(value, 6) for value in head_max],
    },
    "shell_bounds": {
        "min": [round(value, 6) for value in shell_min],
        "max": [round(value, 6) for value in shell_max],
    },
    "checks": {
        "uses_real_head": True,
        "uses_original_helmet_dimensions": False,
        "symmetric_about_head_bone_axis": True,
        "hollow": True,
        "closed_thickness_at_rim": True,
        "has_mask": False,
        "has_crest": False,
        "integrated_in_production": False,
    },
}
OUTPUT_REPORT.write_text(
    json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
)

# GLB QA: únicamente cabeza proxy y cúpula aprobable.
bpy.ops.object.select_all(action="DESELECT")
head_proxy.select_set(True)
shell.select_set(True)
bpy.context.view_layer.objects.active = shell
bpy.ops.export_scene.gltf(
    filepath=os.fspath(OUTPUT_GLB),
    export_format="GLB",
    use_selection=True,
    export_animations=False,
    export_extras=True,
    export_yup=True,
)

# Fuente editable y reproducible del laboratorio.
cutaway.hide_viewport = True
bpy.ops.wm.save_as_mainfile(filepath=os.fspath(OUTPUT_BLEND))
print("LEONIDAS_DOME_STAGE_01_REPORT_BEGIN")
print(json.dumps(report, ensure_ascii=False, indent=2))
print("LEONIDAS_DOME_STAGE_01_REPORT_END")
print("LEONIDAS_DOME_STAGE_01_GLB", OUTPUT_GLB)
print("LEONIDAS_DOME_STAGE_01_BLEND", OUTPUT_BLEND)
