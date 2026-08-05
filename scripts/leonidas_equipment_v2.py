"""Equipamiento PBR original de Leonidas: escudo convexo y lanza forjada.

Este modulo no importa modelos externos. Construye las dos piezas desde cero,
genera sus texturas PBR dentro de Blender y conserva los contratos de nodos que
usa la aplicacion web.
"""

from math import cos, pi, radians, sin

import bmesh
import bpy
import numpy as np
from mathutils import Matrix, Vector


def _smooth_noise(size, seed):
    rng = np.random.default_rng(seed)
    noise = rng.random((size, size), dtype=np.float32)
    for _ in range(5):
        noise = (
            noise
            + np.roll(noise, 1, axis=0)
            + np.roll(noise, -1, axis=0)
            + np.roll(noise, 1, axis=1)
            + np.roll(noise, -1, axis=1)
        ) / 5.0
    return noise


def _surface_maps(kind, base_color, roughness, size=128):
    noise = _smooth_noise(size, sum(ord(char) for char in kind) + 84)
    u, v = np.meshgrid(
        np.linspace(0.0, 1.0, size, endpoint=False),
        np.linspace(0.0, 1.0, size, endpoint=False),
    )
    if kind == 'wood':
        grain = (
            0.50
            + 0.28 * np.sin(v * 95.0 + np.sin(u * 12.0) * 2.8 + noise * 4.0)
            + 0.12 * np.sin(v * 280.0 + noise * 7.0)
        )
        height = np.clip(grain * 0.72 + noise * 0.28, 0.0, 1.0)
        factor = 0.54 + height * 0.76
        rough = np.clip(roughness + (noise - 0.5) * 0.20, 0.35, 0.92)
    elif kind == 'leather':
        pores = np.abs(noise - 0.5) * 2.0
        height = np.clip(0.42 + noise * 0.42 - pores * 0.16, 0.0, 1.0)
        factor = 0.70 + noise * 0.38
        rough = np.clip(roughness + (noise - 0.5) * 0.16, 0.46, 0.96)
    else:
        brushed = 0.5 + 0.24 * np.sin(v * 420.0 + noise * 3.0)
        scratches = np.where(np.sin((u + noise * 0.025) * 760.0) > 0.985, 0.15, 0.0)
        height = np.clip(0.52 + (noise - 0.5) * 0.32 + brushed * 0.08 - scratches, 0.0, 1.0)
        factor = 0.78 + height * 0.30
        rough = np.clip(roughness + (noise - 0.5) * 0.18 + scratches * 0.5, 0.08, 0.62)

    base = np.asarray(base_color, dtype=np.float32)
    albedo = np.ones((size, size, 4), dtype=np.float32)
    albedo[:, :, :3] = np.clip(base[None, None, :] * factor[:, :, None], 0.0, 1.0)
    roughness_map = np.ones((size, size, 4), dtype=np.float32)
    roughness_map[:, :, :3] = rough[:, :, None]

    gradient_y, gradient_x = np.gradient(height)
    normal_strength = 5.0 if kind == 'wood' else 3.0 if kind == 'leather' else 2.0
    normal = np.dstack((
        -gradient_x * normal_strength,
        -gradient_y * normal_strength,
        np.ones_like(height),
    ))
    normal /= np.maximum(np.linalg.norm(normal, axis=2, keepdims=True), 1e-6)
    normal_map = np.ones((size, size, 4), dtype=np.float32)
    normal_map[:, :, :3] = normal * 0.5 + 0.5
    return albedo, roughness_map, normal_map


def _packed_image(name, pixels, non_color=False):
    height, width, _ = pixels.shape
    image = bpy.data.images.new(name, width=width, height=height, alpha=True)
    image.pixels.foreach_set(pixels.reshape(-1))
    if non_color:
        image.colorspace_settings.name = 'Non-Color'
    image.pack()
    return image


def create_pbr_material(
    name,
    color,
    metalness=0.0,
    roughness=0.6,
    surface=None,
    emission=None,
    emission_strength=0.0,
):
    material = bpy.data.materials.new(name)
    material.use_nodes = True
    nodes = material.node_tree.nodes
    links = material.node_tree.links
    shader = nodes.get('Principled BSDF')
    shader.inputs['Base Color'].default_value = (*color, 1.0)
    shader.inputs['Metallic'].default_value = metalness
    shader.inputs['Roughness'].default_value = roughness
    if emission is not None:
        shader.inputs['Emission Color'].default_value = (*emission, 1.0)
        shader.inputs['Emission Strength'].default_value = emission_strength
    if surface:
        albedo, roughness_map, normal_map = _surface_maps(surface, color, roughness)
        albedo_node = nodes.new('ShaderNodeTexImage')
        roughness_node = nodes.new('ShaderNodeTexImage')
        normal_node = nodes.new('ShaderNodeTexImage')
        normal_converter = nodes.new('ShaderNodeNormalMap')
        albedo_node.image = _packed_image(name + 'Albedo', albedo)
        roughness_node.image = _packed_image(name + 'Roughness', roughness_map, True)
        normal_node.image = _packed_image(name + 'Normal', normal_map, True)
        normal_converter.inputs['Strength'].default_value = 0.46 if surface == 'wood' else 0.32
        links.new(albedo_node.outputs['Color'], shader.inputs['Base Color'])
        links.new(roughness_node.outputs['Color'], shader.inputs['Roughness'])
        links.new(normal_node.outputs['Color'], normal_converter.inputs['Color'])
        links.new(normal_converter.outputs['Normal'], shader.inputs['Normal'])
    return material


def new_equipment_object(name, role, materials, world_matrix):
    data = bpy.data.meshes.new(name + 'Mesh')
    obj = bpy.data.objects.new(name, data)
    bpy.context.scene.collection.objects.link(obj)
    obj.matrix_world = world_matrix.copy()
    obj['leonidasPart'] = role
    obj['leonidasProcedural'] = True
    for material in materials:
        data.materials.append(material)
    return obj, bmesh.new()


def mark_faces(faces, material_index, smooth=False):
    for face in faces:
        face.material_index = material_index
        face.smooth = smooth


def add_box(mesh, center, dimensions, material_index, angle=0.0):
    before = set(mesh.faces)
    transform = (
        Matrix.Translation(Vector(center))
        @ Matrix.Rotation(angle, 4, 'Z')
        @ Matrix.Diagonal(Vector((dimensions[0], dimensions[1], dimensions[2], 1.0)))
    )
    bmesh.ops.create_cube(mesh, size=1.0, matrix=transform)
    mark_faces(set(mesh.faces) - before, material_index)


def add_beam(mesh, first, second, width, depth, material_index, z):
    first = Vector(first)
    second = Vector(second)
    delta = second - first
    midpoint = (first + second) * 0.5
    add_box(
        mesh,
        (midpoint.x, midpoint.y, z),
        (delta.length + width * 0.35, width, depth),
        material_index,
        angle=np.arctan2(delta.y, delta.x),
    )


def add_y_cone(mesh, center, radius_bottom, radius_top, depth, material_index, vertices=32):
    before = set(mesh.faces)
    transform = Matrix.Translation(Vector(center)) @ Matrix.Rotation(radians(90), 4, 'X')
    bmesh.ops.create_cone(
        mesh,
        cap_ends=True,
        cap_tris=False,
        segments=vertices,
        radius1=radius_bottom,
        radius2=radius_top,
        depth=depth,
        matrix=transform,
    )
    mark_faces(set(mesh.faces) - before, material_index, smooth=True)


def add_rivet(mesh, center, radius, material_index):
    before = set(mesh.faces)
    transform = (
        Matrix.Translation(Vector(center))
        @ Matrix.Diagonal(Vector((radius, radius, radius * 0.44, 1.0)))
    )
    bmesh.ops.create_icosphere(mesh, subdivisions=2, radius=1.0, matrix=transform)
    mark_faces(set(mesh.faces) - before, material_index, smooth=True)


def add_elliptic_ring(
    mesh,
    center,
    outer_x,
    outer_y,
    inner_x,
    inner_y,
    front_z,
    back_z,
    material_index,
    segments=96,
):
    cx, cy = center
    front_outer = []
    front_inner = []
    back_outer = []
    back_inner = []
    for index in range(segments):
        angle = 2.0 * pi * index / segments
        c = cos(angle)
        s = sin(angle)
        front_outer.append(mesh.verts.new((cx + outer_x * c, cy + outer_y * s, front_z)))
        front_inner.append(mesh.verts.new((cx + inner_x * c, cy + inner_y * s, front_z - 0.002)))
        back_outer.append(mesh.verts.new((cx + outer_x * c, cy + outer_y * s, back_z)))
        back_inner.append(mesh.verts.new((cx + inner_x * c, cy + inner_y * s, back_z)))
    faces = []
    for index in range(segments):
        nxt = (index + 1) % segments
        faces.extend((
            mesh.faces.new((front_outer[index], front_outer[nxt], front_inner[nxt], front_inner[index])),
            mesh.faces.new((back_outer[nxt], back_outer[index], back_inner[index], back_inner[nxt])),
            mesh.faces.new((front_outer[index], back_outer[index], back_outer[nxt], front_outer[nxt])),
            mesh.faces.new((front_inner[nxt], back_inner[nxt], back_inner[index], front_inner[index])),
        ))
    mark_faces(faces, material_index, smooth=True)


def add_convex_plate(
    mesh,
    center,
    radius_x,
    radius_y,
    front_z,
    back_z,
    bulge,
    material_index,
    segments=96,
    rings=8,
):
    cx, cy = center
    front_center = mesh.verts.new((cx, cy, front_z + bulge))
    front_rings = []
    for ring_index in range(1, rings + 1):
        ratio = ring_index / rings
        z = front_z + bulge * (1.0 - ratio * ratio)
        front_rings.append([
            mesh.verts.new((
                cx + radius_x * ratio * cos(2.0 * pi * index / segments),
                cy + radius_y * ratio * sin(2.0 * pi * index / segments),
                z,
            ))
            for index in range(segments)
        ])
    back_center = mesh.verts.new((cx, cy, back_z - bulge * 0.15))
    back_ring = [
        mesh.verts.new((
            cx + radius_x * cos(2.0 * pi * index / segments),
            cy + radius_y * sin(2.0 * pi * index / segments),
            back_z,
        ))
        for index in range(segments)
    ]
    faces = []
    for index in range(segments):
        nxt = (index + 1) % segments
        faces.append(mesh.faces.new((front_center, front_rings[0][index], front_rings[0][nxt])))
        for ring_index in range(rings - 1):
            faces.append(mesh.faces.new((
                front_rings[ring_index][index],
                front_rings[ring_index + 1][index],
                front_rings[ring_index + 1][nxt],
                front_rings[ring_index][nxt],
            )))
        faces.append(mesh.faces.new((back_center, back_ring[nxt], back_ring[index])))
        faces.append(mesh.faces.new((
            front_rings[-1][index], back_ring[index], back_ring[nxt], front_rings[-1][nxt]
        )))
    mark_faces(faces, material_index, smooth=True)


def add_ridged_blade(mesh, shaft_x, y_base, y_tip, half_width, center_z, material_index):
    span = y_tip - y_base
    levels = (
        (0.00, half_width * 0.34, 0.006),
        (0.18, half_width * 0.80, 0.010),
        (0.38, half_width, 0.013),
        (0.67, half_width * 0.62, 0.010),
        (0.88, half_width * 0.26, 0.006),
        (1.00, half_width * 0.035, 0.002),
    )
    front = []
    back = []
    for ratio, width, ridge in levels:
        y = y_base + span * ratio
        front.append((
            mesh.verts.new((shaft_x - width, y, center_z - 0.0015)),
            mesh.verts.new((shaft_x, y, center_z - ridge)),
            mesh.verts.new((shaft_x + width, y, center_z - 0.0015)),
        ))
        back.append((
            mesh.verts.new((shaft_x - width, y, center_z + 0.0015)),
            mesh.verts.new((shaft_x, y, center_z + ridge)),
            mesh.verts.new((shaft_x + width, y, center_z + 0.0015)),
        ))
    faces = []
    for index in range(len(levels) - 1):
        nxt = index + 1
        faces.extend((
            mesh.faces.new((front[index][0], front[index][1], front[nxt][1], front[nxt][0])),
            mesh.faces.new((front[index][1], front[index][2], front[nxt][2], front[nxt][1])),
            mesh.faces.new((back[index][1], back[index][0], back[nxt][0], back[nxt][1])),
            mesh.faces.new((back[index][2], back[index][1], back[nxt][1], back[nxt][2])),
            mesh.faces.new((front[index][0], front[nxt][0], back[nxt][0], back[index][0])),
            mesh.faces.new((front[index][2], back[index][2], back[nxt][2], front[nxt][2])),
        ))
    faces.extend((
        mesh.faces.new((front[0][0], back[0][0], back[0][1], front[0][1])),
        mesh.faces.new((front[0][1], back[0][1], back[0][2], front[0][2])),
        mesh.faces.new((front[-1][0], front[-1][1], back[-1][1], back[-1][0])),
        mesh.faces.new((front[-1][1], front[-1][2], back[-1][2], back[-1][1])),
    ))
    mark_faces(faces, material_index)


def shield_surface_z(point, center, radius_x, radius_y, front_z, bulge):
    normalized = (
        ((point[0] - center[0]) / radius_x) ** 2
        + ((point[1] - center[1]) / radius_y) ** 2
    )
    return front_z + bulge * max(0.0, 1.0 - normalized)


def finalize_mesh(obj, mesh, bevel_width):
    bmesh.ops.recalc_face_normals(mesh, faces=list(mesh.faces))
    mesh.to_mesh(obj.data)
    mesh.free()
    for candidate in bpy.context.scene.objects:
        candidate.select_set(False)
    obj.select_set(True)
    bpy.context.view_layer.objects.active = obj
    bpy.ops.object.mode_set(mode='EDIT')
    bpy.ops.mesh.select_all(action='SELECT')
    bpy.ops.uv.smart_project(angle_limit=radians(66), island_margin=0.025)
    bpy.ops.object.mode_set(mode='OBJECT')
    bevel = obj.modifiers.new(obj.name + 'EdgeSoftening', 'BEVEL')
    bevel.width = bevel_width
    bevel.segments = 2
    bevel.limit_method = 'ANGLE'


def build_corporate_shield(world_matrix, create_palette_material):
    _ = create_palette_material
    field = create_pbr_material(
        'LeonidasShieldCircuitField', (0.025, 0.040, 0.065), 0.56, 0.34, 'metal'
    )
    rim = create_pbr_material(
        'LeonidasShieldRim', (0.42, 0.48, 0.56), 0.72, 0.28, 'metal'
    )
    blue = create_pbr_material(
        'LeonidasShieldPrimary', (0.02, 0.22, 0.90), 0.58, 0.30, 'metal'
    )
    yellow = create_pbr_material(
        'LeonidasShieldSecondary', (0.95, 0.72, 0.07), 0.52, 0.31, 'metal'
    )
    circuit = create_pbr_material(
        'LeonidasShieldCircuitGlow',
        (0.018, 0.24, 0.62),
        0.22,
        0.24,
        emission=(0.025, 0.30, 0.92),
        emission_strength=1.65,
    )
    grip = create_pbr_material(
        'LeonidasShieldRearLeather', (0.075, 0.025, 0.009), 0.02, 0.74, 'leather'
    )
    obj, mesh = new_equipment_object(
        'LeonidasShield', 'shield', (field, rim, blue, yellow, circuit, grip), world_matrix
    )

    # Origen real del antebrazo izquierdo en el fotograma de calibracion.
    # La empunadura trasera coincide con el hueso para que el escudo rote
    # sobre el brazo y no orbite alrededor del hombro.
    center = (0.315748, 0.896423)
    radius_x = 0.205
    radius_y = 0.272
    front_z = -0.050
    back_z = -0.100
    bulge = 0.034
    add_convex_plate(mesh, center, radius_x, radius_y, front_z, back_z, bulge, 0)
    add_elliptic_ring(
        mesh,
        center,
        0.222,
        0.291,
        0.202,
        0.268,
        front_z + bulge + 0.016,
        back_z - 0.012,
        1,
    )
    add_elliptic_ring(
        mesh,
        center,
        0.207,
        0.274,
        0.197,
        0.261,
        front_z + bulge + 0.023,
        front_z + bulge - 0.004,
        1,
    )

    rivet_count = 20
    for index in range(rivet_count):
        angle = 2.0 * pi * index / rivet_count
        add_rivet(
            mesh,
            (
                center[0] + 0.211 * cos(angle),
                center[1] + 0.278 * sin(angle),
                front_z + bulge + 0.026,
            ),
            0.0046,
            1,
        )

    circuit_rows = (-0.172, -0.132, -0.092, -0.052, 0.052, 0.092, 0.132, 0.172)
    for side in (-1, 1):
        for row_index, row in enumerate(circuit_rows):
            outer = (center[0] + side * 0.158, center[1] + row)
            bend = (
                center[0] + side * (0.136 - 0.006 * (row_index % 2)),
                center[1] + row + (0.013 if row_index % 2 == 0 else -0.013),
            )
            inner = (
                center[0] + side * (0.112 - 0.006 * (row_index % 3)),
                bend[1],
            )
            route = (outer, (center[0] + side * 0.148, center[1] + row), bend, inner)
            for first, second in zip(route, route[1:]):
                z = (
                    shield_surface_z(first, center, radius_x, radius_y, front_z, bulge)
                    + shield_surface_z(second, center, radius_x, radius_y, front_z, bulge)
                ) * 0.5 + 0.0025
                add_beam(mesh, first, second, 0.0022, 0.0022, 4, z)
            add_rivet(
                mesh,
                (
                    inner[0],
                    inner[1],
                    shield_surface_z(inner, center, radius_x, radius_y, front_z, bulge) + 0.004,
                ),
                0.0032,
                4,
            )

    blue_path = (
        (center[0] - 0.091, center[1] + 0.026),
        (center[0] - 0.032, center[1] + 0.086),
        (center[0] + 0.035, center[1] + 0.019),
        (center[0] - 0.018, center[1] - 0.034),
    )
    yellow_path = (
        (center[0] + 0.091, center[1] - 0.026),
        (center[0] + 0.032, center[1] - 0.086),
        (center[0] - 0.035, center[1] - 0.019),
        (center[0] + 0.018, center[1] + 0.034),
    )
    for material_index, path, lift in ((2, blue_path, 0.010), (3, yellow_path, 0.014)):
        for first, second in zip(path, path[1:]):
            add_beam(mesh, first, second, 0.030, 0.006, material_index, front_z + bulge + lift)

    for offset_y in (-0.054, 0.054):
        add_beam(
            mesh,
            (center[0] - 0.078, center[1] + offset_y),
            (center[0] + 0.058, center[1] + offset_y),
            0.018,
            0.010,
            5,
            back_z - 0.009,
        )
    add_y_cone(mesh, (center[0] + 0.010, center[1], back_z - 0.027), 0.010, 0.010, 0.170, 5, 24)

    finalize_mesh(obj, mesh, 0.0014)
    obj['leonidasShieldRivets'] = rivet_count
    obj['leonidasShieldCircuitRoutes'] = len(circuit_rows) * 2
    obj['leonidasShieldEmblem'] = 'corporate-interlock'
    obj['leonidasShieldRearGrip'] = True
    obj['leonidasShieldConstruction'] = 'convex-pbr-v2'
    obj['leonidasShieldDimensions'] = '0.444x0.582'
    return obj


def build_spartan_spear(world_matrix, create_palette_material):
    _ = create_palette_material
    wood = create_pbr_material(
        'LeonidasSpearWood', (0.30, 0.085, 0.018), 0.0, 0.58, 'wood'
    )
    metal = create_pbr_material(
        'LeonidasSpearMetal', (0.42, 0.50, 0.58), 0.72, 0.28, 'metal'
    )
    leather = create_pbr_material(
        'LeonidasSpearBinding', (0.12, 0.035, 0.008), 0.02, 0.70, 'leather'
    )
    edge = create_pbr_material(
        'LeonidasSpearEdge', (0.72, 0.80, 0.88), 0.82, 0.20, 'metal'
    )
    obj, mesh = new_equipment_object(
        'LeonidasSpear', 'spear', (wood, metal, leather, edge), world_matrix
    )
    # Centro horizontal del canal de agarre, calibrado contra el puño derecho
    # en vistas frontal, lateral y de tres cuartos.
    shaft_x = -0.437
    # Profundidad del hueco entre el pulgar y los dedos. No coincide con el
    # centro macizo de la palma: por eso evita el efecto de mano atravesada.
    shaft_z = -0.225
    # El asta se interrumpe dentro del puño. Los dos extremos entran en la
    # mano, pero no existe cilindro dentro de la palma; así los dedos pueden
    # ocluir el agarre sin que la vara atraviese la malla de la mano.
    lower_shaft_min = 0.300
    lower_shaft_max = 0.955
    upper_shaft_min = 1.025
    upper_shaft_max = 1.440
    add_y_cone(
        mesh,
        (shaft_x, (lower_shaft_min + lower_shaft_max) * 0.5, shaft_z),
        0.0104,
        0.0100,
        lower_shaft_max - lower_shaft_min,
        0,
        32,
    )
    add_y_cone(
        mesh,
        (shaft_x, (upper_shaft_min + upper_shaft_max) * 0.5, shaft_z),
        0.0100,
        0.0085,
        upper_shaft_max - upper_shaft_min,
        0,
        32,
    )

    # El encordado comienza encima de los nudillos, no sobre la palma.
    for index in range(7):
        y = 1.040 + index * 0.016
        add_y_cone(mesh, (shaft_x, y, shaft_z), 0.0121, 0.0121, 0.009, 2, 28)

    add_y_cone(mesh, (shaft_x, 1.414, shaft_z), 0.013, 0.020, 0.070, 1, 32)
    for y, radius in ((1.370, 0.015), (1.384, 0.0165), (1.398, 0.018)):
        add_y_cone(mesh, (shaft_x, y, shaft_z), radius, radius, 0.008, 3, 32)
    add_ridged_blade(mesh, shaft_x, 1.440, 1.675, 0.049, shaft_z, 1)

    edge_left = (
        (shaft_x - 0.017, 1.445),
        (shaft_x - 0.043, 1.530),
        (shaft_x - 0.020, 1.620),
        (shaft_x, 1.672),
    )
    edge_right = tuple((2 * shaft_x - x, y) for x, y in edge_left)
    for path in (edge_left, edge_right):
        for first, second in zip(path, path[1:]):
            add_beam(mesh, first, second, 0.0032, 0.003, 3, shaft_z - 0.014)

    # Regatón corto: termina a la altura de la suela y no continúa por
    # debajo de los pies del personaje.
    add_y_cone(mesh, (shaft_x, 0.315, shaft_z), 0.010, 0.014, 0.030, 1, 28)
    add_ridged_blade(mesh, shaft_x, 0.340, 0.300, 0.013, shaft_z, 1)

    finalize_mesh(obj, mesh, 0.0009)
    obj['leonidasSpearBlade'] = 'faceted-forged-steel'
    obj['leonidasSpearShaft'] = 'procedural-dark-wood'
    obj['leonidasSpearConstruction'] = 'forged-pbr-v2'
    obj['leonidasSpearLength'] = 1.375
    obj['leonidasSpearGrip'] = 'occluded-hand-channel-v2'
    return obj
