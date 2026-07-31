"""Geometría procedural original para el equipamiento de Leónidas."""

from math import cos, pi, radians, sin

import bmesh
import bpy
import numpy as np
from mathutils import Matrix, Vector


def create_fixed_material(
    name,
    color,
    metalness=0.0,
    roughness=0.6,
    emission=None,
    emission_strength=0.0,
):
    """Crea un material que no participa en el selector de vestuario."""
    material = bpy.data.materials.new(name)
    material.use_nodes = True
    shader = material.node_tree.nodes.get('Principled BSDF')
    shader.inputs['Base Color'].default_value = (*color, 1.0)
    shader.inputs['Metallic'].default_value = metalness
    shader.inputs['Roughness'].default_value = roughness
    if emission is not None:
        shader.inputs['Emission Color'].default_value = (*emission, 1.0)
        shader.inputs['Emission Strength'].default_value = emission_strength
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


def add_cube(mesh, center, dimensions, material_index, angle=0.0):
    before = set(mesh.faces)
    transform = (
        Matrix.Translation(Vector(center))
        @ Matrix.Rotation(angle, 4, 'Z')
        @ Matrix.Diagonal(Vector((
            dimensions[0],
            dimensions[1],
            dimensions[2],
            1.0,
        )))
    )
    bmesh.ops.create_cube(mesh, size=1.0, matrix=transform)
    mark_faces(set(mesh.faces) - before, material_index)


def add_beam(mesh, first, second, width, depth, material_index, z):
    first = Vector(first)
    second = Vector(second)
    delta = second - first
    midpoint = (first + second) * 0.5
    add_cube(
        mesh,
        (midpoint.x, midpoint.y, z),
        (delta.length + width * 0.42, width, depth),
        material_index,
        angle=np.arctan2(delta.y, delta.x),
    )


def add_elliptic_disc(
    mesh,
    center,
    radius_x,
    radius_y,
    front_z,
    back_z,
    material_index,
    segments=64,
    dome=0.0,
):
    center_x, center_y = center
    dome_direction = 1.0 if front_z > back_z else -1.0
    front_center = mesh.verts.new((
        center_x,
        center_y,
        front_z + dome_direction * dome,
    ))
    back_center = mesh.verts.new((center_x, center_y, back_z))
    front_ring = []
    back_ring = []
    for index in range(segments):
        angle = 2.0 * pi * index / segments
        x = center_x + radius_x * cos(angle)
        y = center_y + radius_y * sin(angle)
        front_ring.append(mesh.verts.new((x, y, front_z)))
        back_ring.append(mesh.verts.new((x, y, back_z)))
    faces = []
    for index in range(segments):
        next_index = (index + 1) % segments
        faces.append(mesh.faces.new((
            front_center,
            front_ring[index],
            front_ring[next_index],
        )))
        faces.append(mesh.faces.new((
            back_center,
            back_ring[next_index],
            back_ring[index],
        )))
        faces.append(mesh.faces.new((
            front_ring[index],
            back_ring[index],
            back_ring[next_index],
            front_ring[next_index],
        )))
    mark_faces(faces, material_index, smooth=True)


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
    segments=64,
):
    center_x, center_y = center
    front_outer = []
    front_inner = []
    back_outer = []
    back_inner = []
    for index in range(segments):
        angle = 2.0 * pi * index / segments
        cosine = cos(angle)
        sine = sin(angle)
        front_outer.append(mesh.verts.new((
            center_x + outer_x * cosine,
            center_y + outer_y * sine,
            front_z,
        )))
        front_inner.append(mesh.verts.new((
            center_x + inner_x * cosine,
            center_y + inner_y * sine,
            front_z - 0.003,
        )))
        back_outer.append(mesh.verts.new((
            center_x + outer_x * cosine,
            center_y + outer_y * sine,
            back_z,
        )))
        back_inner.append(mesh.verts.new((
            center_x + inner_x * cosine,
            center_y + inner_y * sine,
            back_z,
        )))
    faces = []
    for index in range(segments):
        next_index = (index + 1) % segments
        faces.extend((
            mesh.faces.new((
                front_outer[index],
                front_outer[next_index],
                front_inner[next_index],
                front_inner[index],
            )),
            mesh.faces.new((
                back_outer[next_index],
                back_outer[index],
                back_inner[index],
                back_inner[next_index],
            )),
            mesh.faces.new((
                front_outer[index],
                back_outer[index],
                back_outer[next_index],
                front_outer[next_index],
            )),
            mesh.faces.new((
                front_inner[next_index],
                back_inner[next_index],
                back_inner[index],
                front_inner[index],
            )),
        ))
    mark_faces(faces, material_index, smooth=True)


def add_rivet(mesh, center, radius, material_index):
    before = set(mesh.faces)
    transform = (
        Matrix.Translation(Vector(center))
        @ Matrix.Diagonal(Vector((radius, radius, radius * 0.46, 1.0)))
    )
    bmesh.ops.create_icosphere(
        mesh,
        subdivisions=2,
        radius=1.0,
        matrix=transform,
    )
    mark_faces(set(mesh.faces) - before, material_index, smooth=True)


def add_cylinder(
    mesh,
    center,
    radius,
    depth,
    material_index,
    vertices=20,
):
    before = set(mesh.faces)
    transform = (
        Matrix.Translation(Vector(center))
        @ Matrix.Rotation(radians(90), 4, 'X')
    )
    bmesh.ops.create_cone(
        mesh,
        cap_ends=True,
        cap_tris=False,
        segments=vertices,
        radius1=radius,
        radius2=radius,
        depth=depth,
        matrix=transform,
    )
    mark_faces(set(mesh.faces) - before, material_index, smooth=True)


def add_extruded_polygon(
    mesh,
    points,
    front_z,
    back_z,
    material_index,
):
    front = [mesh.verts.new((x, y, front_z)) for x, y in points]
    back = [mesh.verts.new((x, y, back_z)) for x, y in points]
    faces = [
        mesh.faces.new(front),
        mesh.faces.new(list(reversed(back))),
    ]
    for index in range(len(points)):
        next_index = (index + 1) % len(points)
        faces.append(mesh.faces.new((
            front[index],
            back[index],
            back[next_index],
            front[next_index],
        )))
    mark_faces(faces, material_index)


def build_corporate_shield(world_matrix, create_palette_material):
    """Escudo original ovalado, tecnológico y corporativo."""
    field = create_fixed_material(
        'LeonidasShieldCircuitField',
        (0.018, 0.027, 0.043),
        metalness=0.42,
        roughness=0.34,
    )
    rim = create_fixed_material(
        'LeonidasShieldRim',
        (0.22, 0.25, 0.29),
        metalness=0.78,
        roughness=0.31,
    )
    blue = create_fixed_material(
        'LeonidasShieldPrimary',
        (0.00, 0.075, 0.46),
        metalness=0.46,
        roughness=0.28,
    )
    yellow = create_fixed_material(
        'LeonidasShieldSecondary',
        (0.66, 0.69, 0.08),
        metalness=0.35,
        roughness=0.32,
    )
    circuit = create_fixed_material(
        'LeonidasShieldCircuitGlow',
        (0.025, 0.32, 0.72),
        metalness=0.12,
        roughness=0.24,
        emission=(0.03, 0.42, 1.0),
        emission_strength=5.2,
    )
    grip = create_fixed_material(
        'LeonidasShieldRearLeather',
        (0.105, 0.046, 0.022),
        metalness=0.02,
        roughness=0.62,
    )
    obj, mesh = new_equipment_object(
        'LeonidasShield',
        'shield',
        (field, rim, blue, yellow, circuit, grip),
        world_matrix,
    )

    # En la pose nativa la mano izquierda descansa en x=0.278, y=0.722.
    # El desplazamiento pequeño deja ver el torso sin separar el agarre.
    center = (0.31, 0.70)
    add_elliptic_disc(
        mesh,
        center,
        radius_x=0.285,
        radius_y=0.375,
        front_z=0.125,
        back_z=0.065,
        material_index=0,
        dome=0.010,
    )
    add_elliptic_ring(
        mesh,
        center,
        outer_x=0.305,
        outer_y=0.397,
        inner_x=0.263,
        inner_y=0.353,
        front_z=0.154,
        back_z=0.050,
        material_index=1,
    )

    for index in range(16):
        angle = 2.0 * pi * index / 16
        add_rivet(
            mesh,
            (
                center[0] + 0.284 * cos(angle),
                center[1] + 0.374 * sin(angle),
                0.162,
            ),
            radius=0.009,
            material_index=1,
        )

    circuit_rows = (-0.245, -0.19, -0.135, -0.08, 0.08, 0.135, 0.19, 0.245)
    for side in (-1, 1):
        for row_index, row in enumerate(circuit_rows):
            outer_x = center[0] + side * (
                0.214 - 0.018 * abs(row) / 0.245
            )
            inner_x = center[0] + side * (
                0.145 - 0.007 * (row_index % 3)
            )
            middle_y = center[1] + row + (
                0.017 if row_index % 2 == 0 else -0.017
            )
            route = (
                (outer_x, center[1] + row),
                (center[0] + side * 0.19, center[1] + row),
                (center[0] + side * 0.17, middle_y),
                (inner_x, middle_y),
            )
            for first, second in zip(route, route[1:]):
                add_beam(
                    mesh,
                    first,
                    second,
                    width=0.0045,
                    depth=0.006,
                    material_index=4,
                    z=0.164,
                )
            add_rivet(
                mesh,
                (route[-1][0], route[-1][1], 0.168),
                radius=0.006,
                material_index=4,
            )

    knot_blue = (
        ((center[0] - 0.142, center[1] + 0.035), (center[0] - 0.045, center[1] + 0.135)),
        ((center[0] - 0.045, center[1] + 0.135), (center[0] + 0.055, center[1] + 0.035)),
        ((center[0] + 0.055, center[1] + 0.035), (center[0] - 0.035, center[1] - 0.058)),
    )
    knot_yellow = (
        ((center[0] + 0.142, center[1] - 0.035), (center[0] + 0.045, center[1] - 0.135)),
        ((center[0] + 0.045, center[1] - 0.135), (center[0] - 0.055, center[1] - 0.035)),
        ((center[0] - 0.055, center[1] - 0.035), (center[0] + 0.035, center[1] + 0.058)),
    )
    for first, second in knot_blue:
        add_beam(
            mesh,
            first,
            second,
            width=0.054,
            depth=0.022,
            material_index=2,
            z=0.181,
        )
    for first, second in knot_yellow:
        add_beam(
            mesh,
            first,
            second,
            width=0.054,
            depth=0.022,
            material_index=3,
            z=0.194,
        )

    # El reverso también está resuelto: abrazadera de antebrazo y asa rígida
    # alineada con la posición real de la mano izquierda.
    for offset_y in (-0.055, 0.055):
        add_beam(
            mesh,
            (center[0] - 0.075, center[1] + offset_y),
            (center[0] + 0.055, center[1] + offset_y),
            width=0.022,
            depth=0.014,
            material_index=5,
            z=0.050,
        )

    # El primer prototipo tapaba desde hombros hasta tobillos. Se conserva la
    # proporción ovalada de la referencia, pero se reduce alrededor del agarre
    # para que proteja el torso sin ocultar la postura ni los pies.
    shield_scale = 0.66
    shield_depth_scale = 0.72
    shield_center_z = 0.10
    for vertex in mesh.verts:
        vertex.co.x = center[0] + (vertex.co.x - center[0]) * shield_scale
        vertex.co.y = center[1] + (vertex.co.y - center[1]) * shield_scale
        vertex.co.z = (
            shield_center_z
            + (vertex.co.z - shield_center_z) * shield_depth_scale
        )

    bmesh.ops.recalc_face_normals(mesh, faces=list(mesh.faces))
    mesh.to_mesh(obj.data)
    mesh.free()
    obj['leonidasShieldRivets'] = 16
    obj['leonidasShieldCircuitRoutes'] = len(circuit_rows) * 2
    obj['leonidasShieldEmblem'] = 'corporate-interlock'
    obj['leonidasShieldRearGrip'] = True
    return obj


def build_spartan_spear(world_matrix, create_palette_material):
    """Lanza original completa: asta, amarres, punta facetada y regatón."""
    wood = create_fixed_material(
        'LeonidasSpearWood',
        (0.105, 0.038, 0.014),
        metalness=0.0,
        roughness=0.66,
    )
    metal = create_fixed_material(
        'LeonidasSpearMetal',
        (0.30, 0.34, 0.38),
        metalness=0.86,
        roughness=0.27,
    )
    leather = create_fixed_material(
        'LeonidasSpearBinding',
        (0.055, 0.046, 0.04),
        metalness=0.04,
        roughness=0.54,
    )
    obj, mesh = new_equipment_object(
        'LeonidasSpear',
        'spear',
        (wood, metal, leather),
        world_matrix,
    )
    # La mano derecha está en x=-0.255 durante la pose nativa.
    shaft_x = -0.255
    shaft_z = 0.035
    add_cylinder(
        mesh,
        (shaft_x, 0.72, shaft_z),
        radius=0.0088,
        depth=1.30,
        material_index=0,
        vertices=20,
    )
    for y in (1.340, 1.356, 1.372):
        add_cylinder(
            mesh,
            (shaft_x, y, shaft_z),
            radius=0.014,
            depth=0.010,
            material_index=1,
            vertices=24,
        )
    for y in (0.688, 0.706, 0.724, 0.742):
        add_cylinder(
            mesh,
            (shaft_x, y, shaft_z),
            radius=0.0115,
            depth=0.010,
            material_index=2,
            vertices=20,
        )

    blade = (
        (shaft_x, 1.525),
        (shaft_x + 0.040, 1.420),
        (shaft_x + 0.014, 1.372),
        (shaft_x - 0.014, 1.372),
        (shaft_x - 0.040, 1.420),
    )
    add_extruded_polygon(
        mesh,
        blade,
        front_z=shaft_z - 0.014,
        back_z=shaft_z + 0.014,
        material_index=1,
    )
    add_beam(
        mesh,
        (shaft_x, 1.382),
        (shaft_x, 1.508),
        width=0.006,
        depth=0.008,
        material_index=1,
        z=shaft_z - 0.021,
    )

    butt = (
        (shaft_x, 0.015),
        (shaft_x + 0.025, 0.075),
        (shaft_x + 0.014, 0.115),
        (shaft_x - 0.014, 0.115),
        (shaft_x - 0.025, 0.075),
    )
    add_extruded_polygon(
        mesh,
        butt,
        front_z=shaft_z - 0.012,
        back_z=shaft_z + 0.012,
        material_index=1,
    )
    bmesh.ops.recalc_face_normals(mesh, faces=list(mesh.faces))
    mesh.to_mesh(obj.data)
    mesh.free()
    obj['leonidasSpearBlade'] = 'faceted-forged-steel'
    obj['leonidasSpearShaft'] = 'procedural-dark-wood'
    return obj
