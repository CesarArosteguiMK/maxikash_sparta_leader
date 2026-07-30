"""Construye un prototipo modular de Leonidas a partir de los modelos locales.

El FBX conserva el personaje, rig y animación actuales. El GLB alternativo solo
aporta las superficies anatómicas que el FBX no contiene debajo del casco y la
pechera. El resultado debe revisarse visualmente antes de habilitarse.
"""

import os
from math import radians

import bmesh
import bpy
import numpy as np
from mathutils import Matrix, Vector


PROJECT_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
MODEL_ROOT = os.path.join(PROJECT_ROOT, 'public', 'assets', 'models', 'leonidas')
OUTPUT_PATH = os.path.join(MODEL_ROOT, 'leonidas-spartan-modular-v2.glb')


def normalized(name):
    return ''.join(character for character in name.lower() if character.isalnum())


def object_bounds(obj):
    points = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
    minimum = Vector(tuple(min(point[axis] for point in points) for axis in range(3)))
    maximum = Vector(tuple(max(point[axis] for point in points) for axis in range(3)))
    return minimum, maximum


def mesh_vertex_bounds(obj):
    points = [obj.matrix_world @ vertex.co for vertex in obj.data.vertices]
    minimum = Vector(tuple(min(point[axis] for point in points) for axis in range(3)))
    maximum = Vector(tuple(max(point[axis] for point in points) for axis in range(3)))
    return minimum, maximum


def load_pixels(path):
    image = bpy.data.images.load(path, check_existing=False)
    width, height = image.size
    values = np.empty(width * height * 4, dtype=np.float32)
    image.pixels.foreach_get(values)
    return width, height, values.reshape((height, width, 4))


def clean_current_material(obj):
    material = bpy.data.materials.new('LeonidasOriginal')
    material.use_nodes = True
    nodes = material.node_tree.nodes
    links = material.node_tree.links
    nodes.clear()
    output = nodes.new('ShaderNodeOutputMaterial')
    shader = nodes.new('ShaderNodeBsdfPrincipled')
    color = nodes.new('ShaderNodeTexImage')
    color.image = bpy.data.images.load(
        os.path.join(MODEL_ROOT, 'leonidas-spartan-color.webp'),
        check_existing=True,
    )
    color.image.colorspace_settings.name = 'sRGB'
    normal_texture = nodes.new('ShaderNodeTexImage')
    normal_texture.image = bpy.data.images.load(
        os.path.join(MODEL_ROOT, 'leonidas-spartan-normal.webp'),
        check_existing=True,
    )
    normal_texture.image.colorspace_settings.name = 'Non-Color'
    normal_map = nodes.new('ShaderNodeNormalMap')
    normal_map.inputs['Strength'].default_value = 0.32
    shader.inputs['Roughness'].default_value = 0.66
    links.new(color.outputs['Color'], shader.inputs['Base Color'])
    links.new(normal_texture.outputs['Color'], normal_map.inputs['Color'])
    links.new(normal_map.outputs['Normal'], shader.inputs['Normal'])
    links.new(shader.outputs['BSDF'], output.inputs['Surface'])
    obj.data.materials.clear()
    obj.data.materials.append(material)


def skin_mask(pixels):
    rgb = pixels[:, :, :3] * 255.0
    red = rgb[:, :, 0]
    green = rgb[:, :, 1]
    blue = rgb[:, :, 2]
    alpha = pixels[:, :, 3] * 255.0
    cb = 128 - 0.168736 * red - 0.331264 * green + 0.5 * blue
    cr = 128 + 0.5 * red - 0.418688 * green - 0.081312 * blue
    return (
        (alpha >= 12)
        & (red > 72)
        & (green > 31)
        & (blue > 22)
        & (red > green * 1.04)
        & (green >= blue * 0.76)
        & (blue >= green * 0.66)
        & (cb > 70)
        & (cb < 142)
        & (cr > 132)
        & (cr < 184)
    )


def current_arm_skin_reference(obj):
    """Mide solamente la piel usada realmente por los brazos del FBX."""
    width, height, pixels = load_pixels(
        os.path.join(MODEL_ROOT, 'leonidas-spartan-color.webp')
    )
    uv_layer = obj.data.uv_layers.active.data
    group_names = {group.index: normalized(group.name) for group in obj.vertex_groups}
    samples = []
    for polygon in obj.data.polygons:
        arm_weight = 0.0
        total_weight = 0.0
        for vertex_index in polygon.vertices:
            for assignment in obj.data.vertices[vertex_index].groups:
                name = group_names.get(assignment.group, '')
                total_weight += assignment.weight
                if (
                    'upperarm' in name
                    or 'lowerarm' in name
                    or 'forearm' in name
                    or 'leftarm' in name
                    or 'rightarm' in name
                ):
                    arm_weight += assignment.weight
        if total_weight <= 0 or arm_weight / total_weight < 0.42:
            continue
        for loop_index in polygon.loop_indices:
            pixel = sample_texture(
                pixels,
                width,
                height,
                uv_layer[loop_index].uv,
            )
            if is_skin(pixel):
                samples.append(np.array(pixel[:3], dtype=np.float32) / 255.0)
    if len(samples) < 24:
        raise RuntimeError('No se pudo medir suficiente piel de los brazos.')
    return np.percentile(np.stack(samples), 70, axis=0)


def harmonize_skin_material(obj, current):
    """Iguala cabeza y torso con los píxeles usados por los brazos visibles."""
    target_reference = current_arm_skin_reference(current)

    for slot in obj.material_slots:
        source_material = slot.material
        if not source_material or normalized(source_material.name) != 'body':
            continue
        source_node = next(
            (
                node
                for node in source_material.node_tree.nodes
                if node.type == 'TEX_IMAGE' and node.image
            ),
            None,
        )
        if not source_node:
            continue
        image = source_node.image
        width, height = image.size
        values = np.empty(width * height * 4, dtype=np.float32)
        image.pixels.foreach_get(values)
        values = values.reshape((height, width, 4))
        source_selection = skin_mask(values)
        if not np.any(source_selection):
            raise RuntimeError('No se pudo medir el tono de piel de la cabeza donante.')

        source_reference = np.percentile(
            values[:, :, :3][source_selection],
            70,
            axis=0,
        )
        # La cabeza donante recibe más luz frontal que sus valores de textura
        # sugieren. Esta compensación se obtuvo comparando el render final de
        # rostro y brazos, no el atlas completo.
        render_calibration = np.array([0.76, 0.75, 0.77], dtype=np.float32)
        scale = np.clip(
            target_reference
            / np.maximum(source_reference, 0.001)
            * render_calibration,
            0.82,
            1.45,
        )
        corrected = values.copy()
        corrected[:, :, :3][source_selection] = np.clip(
            corrected[:, :, :3][source_selection] * scale,
            0.0,
            1.0,
        )

        generated = bpy.data.images.new(
            'LeonidasSkinMatched',
            width=width,
            height=height,
            alpha=True,
        )
        generated.colorspace_settings.name = 'sRGB'
        generated.pixels.foreach_set(corrected.reshape(-1))
        generated.pack()

        material = source_material.copy()
        material.name = 'LeonidasSkin'
        material.use_nodes = True
        for node in material.node_tree.nodes:
            if node.type == 'TEX_IMAGE' and node.image == image:
                node.image = generated
        slot.material = material
        print(
            'LEONIDAS_SKIN_MATCH',
            {
                'source': [round(float(value), 4) for value in source_reference],
                'target': [round(float(value), 4) for value in target_reference],
                'scale': [round(float(value), 4) for value in scale],
            },
        )


def sample_texture(pixels, width, height, uv):
    x = max(0, min(width - 1, int((uv.x % 1.0) * width)))
    y = max(0, min(height - 1, int((uv.y % 1.0) * height)))
    return tuple(int(round(value * 255)) for value in pixels[y, x, :4])


def is_skin(pixel):
    red, green, blue, alpha = pixel
    if alpha < 12:
        return False
    cb = 128 - 0.168736 * red - 0.331264 * green + 0.5 * blue
    cr = 128 + 0.5 * red - 0.418688 * green - 0.081312 * blue
    return (
        red > 72
        and green > 31
        and blue > 22
        and red > green * 1.04
        and green >= blue * 0.76
        and blue >= green * 0.66
        and 70 < cb < 142
        and 132 < cr < 184
    )


def texture_material(pixel):
    """Clasifica el acabado original sin usar el color elegido por el usuario."""
    red_byte, green_byte, blue_byte, alpha = pixel
    if alpha < 12:
        return 'original'
    red = red_byte / 255.0
    green = green_byte / 255.0
    blue = blue_byte / 255.0
    maximum = max(red, green, blue)
    minimum = min(red, green, blue)
    saturation = (maximum - minimum) / maximum if maximum > 0 else 0.0
    luminance = red * 0.299 + green * 0.587 + blue * 0.114
    neutral_metal = saturation < 0.2 and luminance > 0.28
    bright_bronze = (
        red > green * 1.04
        and green > blue * 1.08
        and luminance > 0.48
        and saturation < 0.5
    )
    leather = (
        red > green * 1.035
        and green > blue * 1.025
        and 0.12 < luminance < 0.58
        and saturation > 0.12
    )
    if neutral_metal or bright_bronze:
        return 'metal'
    if leather:
        return 'secondary'
    return 'primary'


def create_palette_material(name, role, metalness, roughness):
    """Material sin atlas: el navegador cambia únicamente esta pieza."""
    material = bpy.data.materials.new(name)
    material.use_nodes = True
    material['leonidasPalette'] = role
    shader = material.node_tree.nodes.get('Principled BSDF')
    shader.inputs['Base Color'].default_value = (1.0, 1.0, 1.0, 1.0)
    shader.inputs['Metallic'].default_value = metalness
    shader.inputs['Roughness'].default_value = roughness
    return material


def uv_face_components(obj):
    """Agrupa caras que pertenecen a la misma isla UV."""
    polygons = obj.data.polygons
    uvs = obj.data.uv_layers.active.data
    parents = list(range(len(polygons)))

    def find(index):
        while parents[index] != index:
            parents[index] = parents[parents[index]]
            index = parents[index]
        return index

    def join(first, second):
        first_root = find(first)
        second_root = find(second)
        if first_root != second_root:
            parents[second_root] = first_root

    edge_owners = {}
    for polygon in polygons:
        loop_indices = list(polygon.loop_indices)
        for offset, first_loop in enumerate(loop_indices):
            second_loop = loop_indices[(offset + 1) % len(loop_indices)]
            first_uv = uvs[first_loop].uv
            second_uv = uvs[second_loop].uv
            first = (round(first_uv.x, 5), round(first_uv.y, 5))
            second = (round(second_uv.x, 5), round(second_uv.y, 5))
            key = tuple(sorted((first, second)))
            owner = edge_owners.get(key)
            if owner is None:
                edge_owners[key] = polygon.index
            else:
                join(polygon.index, owner)

    components = {}
    for polygon in polygons:
        components.setdefault(find(polygon.index), []).append(polygon.index)
    return sorted(components.values(), key=len, reverse=True)


def assign_body_semantic_materials(obj):
    """Separa piel, tela, cuero y metal por polígonos reales.

    La versión anterior pintaba píxeles del atlas compartido y podía afectar
    piel u otras piezas que reutilizaban la misma zona UV. Al asignar el rol
    directamente a cada cara, un color jamás cruza a otra pieza.
    """
    original = obj.data.materials[0]
    primary = create_palette_material(
        'LeonidasPrimary',
        'primary',
        metalness=0.06,
        roughness=0.72,
    )
    secondary = create_palette_material(
        'LeonidasSecondary',
        'secondary',
        metalness=0.03,
        roughness=0.68,
    )
    metal = create_palette_material(
        'LeonidasMetal',
        'metal',
        metalness=0.52,
        roughness=0.42,
    )
    obj.data.materials.clear()
    for material in (original, primary, secondary, metal):
        obj.data.materials.append(material)

    width, height, pixels = load_pixels(
        os.path.join(MODEL_ROOT, 'leonidas-spartan-color.webp')
    )
    uv_layer = obj.data.uv_layers.active.data
    group_names = {
        group.index: normalized(group.name)
        for group in obj.vertex_groups
    }
    material_indices = {
        'original': 0,
        'primary': 1,
        'secondary': 2,
        'metal': 3,
    }
    face_roles = {}

    for polygon in obj.data.polygons:
        center = Vector((0.0, 0.0, 0.0))
        scores = {}
        for vertex_index in polygon.vertices:
            vertex = obj.data.vertices[vertex_index]
            center += obj.matrix_world @ vertex.co
            for assignment in vertex.groups:
                name = group_names.get(assignment.group, '')
                scores[name] = scores.get(name, 0.0) + assignment.weight
        center /= len(polygon.vertices)
        total = sum(scores.values()) or 1.0

        def ratio(*needles):
            return sum(
                weight
                for name, weight in scores.items()
                if any(needle in name for needle in needles)
            ) / total

        head_ratio = ratio('head', 'neck')
        torso_ratio = ratio('spine', 'shoulder', 'clavicle', 'upperarm')
        hips_ratio = ratio('hips', 'pelvis')
        upper_leg_ratio = ratio('upleg', 'thigh')
        foot_ratio = ratio('foot', 'toe')
        metal_limb_ratio = sum(
            weight
            for name, weight in scores.items()
            if (
                'forearm' in name
                or 'lowerarm' in name
                or 'calf' in name
                or 'foot' in name
                or 'toe' in name
                or (
                    'leg' in name
                    and 'upleg' not in name
                    and 'thigh' not in name
                )
            )
        ) / total

        samples = [
            sample_texture(pixels, width, height, uv_layer[index].uv)
            for index in polygon.loop_indices
        ]
        skin_ratio = (
            sum(1 for pixel in samples if is_skin(pixel))
            / max(len(samples), 1)
        )
        average_pixel = tuple(
            int(round(sum(pixel[channel] for pixel in samples) / len(samples)))
            for channel in range(4)
        )
        source_role = texture_material(average_pixel)

        protected_upper_leg = (
            center.z < 0.56
            and upper_leg_ratio > 0.34
            and upper_leg_ratio > hips_ratio * 1.25
        )

        if foot_ratio > 0.24:
            # El modelo no tiene pie desnudo: esta zona corresponde a la bota
            # y su puntera. Debe pintarse completa, no por manchas del atlas.
            role = 'metal'
        elif skin_ratio >= 0.5 or protected_upper_leg:
            role = 'original'
        elif center.z > 1.0 and head_ratio > 0.18:
            role = 'original'
        elif (
            metal_limb_ratio > 0.34
            and (center.z < 0.70 or center.z > 0.76)
        ):
            role = 'metal'
        elif (
            0.42 < center.z < 0.84
            and hips_ratio + upper_leg_ratio > 0.08
        ):
            role = source_role
        elif torso_ratio > 0.22:
            role = (
                'metal'
                if source_role == 'metal'
                else 'secondary'
            )
        elif source_role == 'metal':
            role = 'metal'
        else:
            role = 'original'

        face_roles[polygon.index] = role

    roles = ('original', 'primary', 'secondary', 'metal')
    counts = {role: 0 for role in roles}
    island_summary = []
    for island in uv_face_components(obj):
        votes = {
            role: sum(
                1
                for face_index in island
                if face_roles[face_index] == role
            )
            for role in roles
        }
        original_ratio = votes['original'] / len(island)
        non_original = max(
            ('primary', 'secondary', 'metal'),
            key=lambda role: votes[role],
        )
        if (
            original_ratio >= 0.68
            and votes['original'] >= votes[non_original]
        ):
            role = 'original'
        elif non_original == 'secondary' and len(island) > 80:
            # El cuero grande conserva su tono original. El segundo color se
            # reserva para correas, ribetes y acentos discretos.
            role = 'original'
        elif votes[non_original] > 0:
            role = non_original
        else:
            role = 'original'
        for face_index in island:
            obj.data.polygons[face_index].material_index = material_indices[role]
        counts[role] += len(island)
        island_summary.append({
            'faces': len(island),
            'role': role,
            **votes,
        })
    print('LEONIDAS_BODY_UV_ISLANDS', island_summary[:30])

    obj['leonidasSemanticFaces'] = ':'.join(
        f'{role}={counts[role]}'
        for role in ('original', 'primary', 'secondary', 'metal')
    )
    print('LEONIDAS_SEMANTIC_MATERIALS', counts)


def assign_solid_palette_material(obj, role):
    material = create_palette_material(
        f'Leonidas{role.title()}',
        role,
        metalness=0.52 if role == 'metal' else 0.04,
        roughness=0.42 if role == 'metal' else 0.56,
    )
    obj.data.materials.clear()
    obj.data.materials.append(material)
    for polygon in obj.data.polygons:
        polygon.material_index = 0


def is_confident_skin(pixel):
    red, green, blue, alpha = pixel
    return (
        is_skin(pixel)
        and alpha >= 12
        and blue >= green * 0.78
        and red <= green * 1.48
    )


def assign_chest_semantic_materials(obj):
    """Evita que la pechera pinte piel cercana de hombros o axilas."""
    original = obj.data.materials[0]
    metal = create_palette_material(
        'LeonidasChestMetal',
        'metal',
        metalness=0.52,
        roughness=0.42,
    )
    obj.data.materials.clear()
    obj.data.materials.append(original)
    obj.data.materials.append(metal)
    width, height, pixels = load_pixels(
        os.path.join(MODEL_ROOT, 'leonidas-spartan-color.webp')
    )
    uv_layer = obj.data.uv_layers.active.data
    initial = {}
    for polygon in obj.data.polygons:
        samples = [
            sample_texture(pixels, width, height, uv_layer[index].uv)
            for index in polygon.loop_indices
        ]
        skin_ratio = (
            sum(1 for pixel in samples if is_confident_skin(pixel))
            / max(len(samples), 1)
        )
        initial[polygon.index] = 'original' if skin_ratio >= 0.5 else 'metal'

    counts = {'original': 0, 'metal': 0}
    for island in uv_face_components(obj):
        original_votes = sum(
            1 for face_index in island
            if initial[face_index] == 'original'
        )
        role = (
            'original'
            if original_votes / len(island) >= 0.42
            else 'metal'
        )
        material_index = 0 if role == 'original' else 1
        for face_index in island:
            obj.data.polygons[face_index].material_index = material_index
        counts[role] += len(island)
    obj['leonidasChestSemanticFaces'] = (
        f"original={counts['original']}:metal={counts['metal']}"
    )
    print('LEONIDAS_CHEST_SEMANTIC_MATERIALS', counts)


def add_helmet_visor(obj):
    """Añade una visera negra en T sin recortar ni colorear el rostro."""
    minimum, maximum = mesh_vertex_bounds(obj)
    size = maximum - minimum
    center_x = (minimum.x + maximum.x) * 0.5
    half_width = size.x * 0.5
    front_y = minimum.y - size.y * 0.012
    inverse = obj.matrix_world.inverted()
    old_vertex_count = len(obj.data.vertices)

    visor = bpy.data.materials.new('LeonidasVisorMaterial')
    visor.use_nodes = True
    shader = visor.node_tree.nodes.get('Principled BSDF')
    shader.inputs['Base Color'].default_value = (0.008, 0.012, 0.018, 1.0)
    shader.inputs['Metallic'].default_value = 0.15
    shader.inputs['Roughness'].default_value = 0.26
    obj.data.materials.append(visor)
    visor_index = len(obj.data.materials) - 1

    mesh = bmesh.new()
    mesh.from_mesh(obj.data)

    def add_quad(points):
        vertices = [
            mesh.verts.new(inverse @ Vector(point))
            for point in points
        ]
        face = mesh.faces.new(vertices)
        face.material_index = visor_index

    eye_top = minimum.z + size.z * 0.635
    eye_inner = minimum.z + size.z * 0.545
    eye_outer = minimum.z + size.z * 0.575
    inner_x = half_width * 0.10
    outer_x = half_width * 0.73
    add_quad([
        (center_x - outer_x, front_y, eye_top),
        (center_x - inner_x, front_y, eye_top - size.z * 0.015),
        (center_x - inner_x, front_y, eye_inner),
        (center_x - outer_x, front_y, eye_outer),
    ])
    add_quad([
        (center_x + inner_x, front_y, eye_top - size.z * 0.015),
        (center_x + outer_x, front_y, eye_top),
        (center_x + outer_x, front_y, eye_outer),
        (center_x + inner_x, front_y, eye_inner),
    ])
    nasal_top = eye_inner + size.z * 0.025
    nasal_bottom = minimum.z + size.z * 0.205
    nasal_half = half_width * 0.105
    add_quad([
        (center_x - nasal_half, front_y, nasal_top),
        (center_x + nasal_half, front_y, nasal_top),
        (center_x + nasal_half * 0.62, front_y, nasal_bottom),
        (center_x - nasal_half * 0.62, front_y, nasal_bottom),
    ])
    mesh.to_mesh(obj.data)
    mesh.free()

    new_vertex_indices = list(range(old_vertex_count, len(obj.data.vertices)))
    head_group = obj.vertex_groups.get('mixamorig:Head')
    if head_group is None:
        head_group = obj.vertex_groups.new(name='mixamorig:Head')
    head_group.add(new_vertex_indices, 1.0, 'REPLACE')
    obj['leonidasHelmetVisorFaces'] = 3
    print('LEONIDAS_HELMET_VISOR', {'faces': 3})


def equipment_faces(obj):
    mesh = obj.data
    uv_layer = mesh.uv_layers.active.data
    group_names = {group.index: normalized(group.name) for group in obj.vertex_groups}
    width, height, pixels = load_pixels(
        os.path.join(MODEL_ROOT, 'leonidas-spartan-color.webp')
    )
    labels = {}
    for polygon in mesh.polygons:
        center = Vector((0, 0, 0))
        scores = {}
        for vertex_index in polygon.vertices:
            vertex = mesh.vertices[vertex_index]
            center += obj.matrix_world @ vertex.co
            for group in vertex.groups:
                name = group_names.get(group.group, '')
                scores[name] = scores.get(name, 0) + group.weight
        center /= len(polygon.vertices)
        average_uv = Vector((0, 0))
        for loop_index in polygon.loop_indices:
            average_uv += uv_layer[loop_index].uv
        average_uv /= len(polygon.loop_indices)
        if is_skin(sample_texture(pixels, width, height, average_uv)):
            labels[polygon.index] = 'other'
            continue
        total = sum(scores.values()) or 1
        head_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'head' in name or 'neck' in name
        ) / total
        torso_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'spine' in name or 'shoulder' in name or 'upperarm' in name
        ) / total
        if center.z > 1.08 and head_ratio > 0.25:
            labels[polygon.index] = 'helmet'
        elif 0.72 < center.z < 1.17 and abs(center.x) < 0.5 and torso_ratio > 0.18:
            labels[polygon.index] = 'chest'
        else:
            labels[polygon.index] = 'other'

    parent = list(range(len(mesh.polygons)))

    def find(face):
        while parent[face] != face:
            parent[face] = parent[parent[face]]
            face = parent[face]
        return face

    def join(first, second):
        first_root = find(first)
        second_root = find(second)
        if first_root != second_root:
            parent[second_root] = first_root

    owners = {}
    for polygon in mesh.polygons:
        for loop_index in polygon.loop_indices:
            uv = uv_layer[loop_index].uv
            key = (round(uv.x, 5), round(uv.y, 5))
            owner = owners.get(key)
            if owner is None:
                owners[key] = polygon.index
            else:
                join(polygon.index, owner)

    islands = {}
    for face_index, label in labels.items():
        island = islands.setdefault(find(face_index), {'faces': [], 'helmet': 0, 'chest': 0})
        island['faces'].append(face_index)
        if label in ('helmet', 'chest'):
            island[label] += 1

    helmet = set()
    chest = set()
    for island in islands.values():
        size = len(island['faces'])
        if island['helmet'] / size >= 0.3:
            helmet.update(island['faces'])
        elif island['chest'] / size >= 0.3:
            chest.update(island['faces'])
    return helmet, chest


def removable_shell_faces(obj):
    """Separa las carcasas completas que deben desaparecer con cada control."""
    group_names = {group.index: normalized(group.name) for group in obj.vertex_groups}
    head = set()
    torso = set()
    for polygon in obj.data.polygons:
        center = Vector((0, 0, 0))
        scores = {}
        for vertex_index in polygon.vertices:
            vertex = obj.data.vertices[vertex_index]
            center += obj.matrix_world @ vertex.co
            for assignment in vertex.groups:
                name = group_names.get(assignment.group, '')
                scores[name] = scores.get(name, 0) + assignment.weight
        center /= len(polygon.vertices)
        total = sum(scores.values()) or 1
        head_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'head' in name or 'neck' in name
        ) / total
        spine_ratio = sum(
            weight for name, weight in scores.items() if 'spine' in name
        ) / total
        shoulder_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'shoulder' in name or 'clavicle' in name
        ) / total
        arm_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'upperarm' in name
            or 'lowerarm' in name
            or 'forearm' in name
            or 'leftarm' in name
            or 'rightarm' in name
        ) / total

        if center.z > 1.0 and head_ratio > 0.18:
            head.add(polygon.index)
        elif (
            0.59 < center.z < 1.21
            and abs(center.x) < 0.28
            and spine_ratio + shoulder_ratio > 0.13
            and spine_ratio + shoulder_ratio >= arm_ratio * 1.35
        ):
            torso.add(polygon.index)
    return head, torso


def keep_faces(source, face_indices, name, role):
    result = source.copy()
    result.data = source.data.copy()
    result.animation_data_clear()
    result.name = name
    result.data.name = name + 'Mesh'
    bpy.context.scene.collection.objects.link(result)
    mesh = bmesh.new()
    mesh.from_mesh(result.data)
    mesh.faces.ensure_lookup_table()
    keep = set(face_indices)
    remove = [face for face in mesh.faces if face.index not in keep]
    bmesh.ops.delete(mesh, geom=remove, context='FACES')
    loose = [vertex for vertex in mesh.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(mesh, geom=loose, context='VERTS')
    mesh.to_mesh(result.data)
    mesh.free()
    result['leonidasPart'] = role
    return result


def transform_donor_body(donor, current):
    donor_min, donor_max = mesh_vertex_bounds(donor)
    current_min, current_max = mesh_vertex_bounds(current)
    donor_center = (donor_min + donor_max) * 0.5
    current_center = (current_min + current_max) * 0.5
    donor_sizes = donor_max - donor_min
    donor_up_axis = max(range(3), key=lambda axis: donor_sizes[axis])
    donor_depth_axis = next(
        axis for axis in range(3) if axis not in (0, donor_up_axis)
    )
    vertical_scale = (
        (current_max.z - current_min.z)
        / donor_sizes[donor_up_axis]
    )
    current_inverse = current.matrix_world.inverted()
    first_debug = None
    for vertex in donor.data.vertices:
        world = donor.matrix_world @ vertex.co
        target = Vector((
            current_center.x + (world.x - donor_center.x) * vertical_scale * 0.88,
            current_center.y
            - (world[donor_depth_axis] - donor_center[donor_depth_axis])
            * vertical_scale * 0.82
            + 0.006,
            current_min.z
            + (world[donor_up_axis] - donor_min[donor_up_axis])
            * vertical_scale,
        ))
        new_coordinate = current_inverse @ target
        if first_debug is None:
            first_debug = (
                tuple(round(value, 4) for value in world),
                tuple(round(value, 4) for value in target),
                tuple(round(value, 4) for value in new_coordinate),
            )
        vertex.co = new_coordinate
    donor.matrix_world = current.matrix_world.copy()
    print('LEONIDAS_TRANSFORM_SAMPLE', first_debug)


def remap_vertex_groups(obj):
    direct = {
        'rootjoint': 'mixamorig:Hips',
        'pelvis01': 'mixamorig:Hips',
        'spine01014': 'mixamorig:Spine',
        'spine02015': 'mixamorig:Spine1',
        'spine03016': 'mixamorig:Spine2',
        'neck01017': 'mixamorig:Neck',
        'head018': 'mixamorig:Head',
        'claviclel040': 'mixamorig:LeftShoulder',
        'upperarml041': 'mixamorig:LeftArm',
        'lowerarml042': 'mixamorig:LeftForeArm',
        'handl043': 'mixamorig:LeftHand',
        'clavicler019': 'mixamorig:RightShoulder',
        'upperarmr020': 'mixamorig:RightArm',
        'lowerarmr021': 'mixamorig:RightForeArm',
        'handr022': 'mixamorig:RightHand',
        'thighl02': 'mixamorig:LeftUpLeg',
        'calfl03': 'mixamorig:LeftLeg',
        'footl04': 'mixamorig:LeftFoot',
        'balll05': 'mixamorig:LeftToeBase',
        'thighr08': 'mixamorig:RightUpLeg',
        'calfr09': 'mixamorig:RightLeg',
        'footr010': 'mixamorig:RightFoot',
        'ballr011': 'mixamorig:RightToeBase',
        'lowerarmtwist01l058': 'mixamorig:LeftForeArm',
        'upperarmtwist01l059': 'mixamorig:LeftArm',
        'lowerarmtwist01r038': 'mixamorig:RightForeArm',
        'upperarmtwist01r039': 'mixamorig:RightArm',
        'thightwist01l07': 'mixamorig:LeftUpLeg',
        'calftwist01l06': 'mixamorig:LeftLeg',
        'thightwist01r013': 'mixamorig:RightUpLeg',
        'calftwist01r012': 'mixamorig:RightLeg',
    }
    for group in obj.vertex_groups:
        target = direct.get(normalized(group.name))
        if target:
            group.name = target


def attach_to_current_rig(obj, current, armature):
    world_matrix = current.matrix_world.copy()
    obj.parent = armature
    obj.matrix_parent_inverse = armature.matrix_world.inverted()
    obj.matrix_world = world_matrix
    for modifier in list(obj.modifiers):
        if modifier.type == 'ARMATURE':
            obj.modifiers.remove(modifier)
    modifier = obj.modifiers.new('LeonidasRig', 'ARMATURE')
    modifier.object = armature


def crop_anatomy(source, name, role, predicate):
    result = source.copy()
    result.data = source.data.copy()
    result.name = name
    result.data.name = name + 'Mesh'
    bpy.context.scene.collection.objects.link(result)
    mesh = bmesh.new()
    mesh.from_mesh(result.data)
    mesh.faces.ensure_lookup_table()
    remove = []
    for face in mesh.faces:
        center_world = result.matrix_world @ face.calc_center_median()
        if not predicate(center_world):
            remove.append(face)
    bmesh.ops.delete(mesh, geom=remove, context='FACES')
    loose = [vertex for vertex in mesh.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(mesh, geom=loose, context='VERTS')
    mesh.to_mesh(result.data)
    mesh.free()
    result['leonidasPart'] = role
    return result


def anatomy_faces(source, mode):
    names = {group.index: normalized(group.name) for group in source.vertex_groups}
    selected = set()
    for polygon in source.data.polygons:
        center = Vector((0, 0, 0))
        scores = {}
        for vertex_index in polygon.vertices:
            vertex = source.data.vertices[vertex_index]
            center += source.matrix_world @ vertex.co
            for assignment in vertex.groups:
                name = names.get(assignment.group, '')
                scores[name] = scores.get(name, 0) + assignment.weight
        center /= len(polygon.vertices)
        total = sum(scores.values()) or 1
        head_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'head' in name or 'neck' in name
        ) / total
        spine_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'spine' in name or 'neck' in name
        ) / total
        shoulder_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'clavicle' in name or 'shoulder' in name
        ) / total
        arm_ratio = sum(
            weight
            for name, weight in scores.items()
            if 'upperarm' in name
            or 'lowerarm' in name
        ) / total
        if mode == 'head':
            if center.z > 1.0 and abs(center.x) < 0.31 and head_ratio > 0.24:
                selected.add(polygon.index)
        elif (
            0.56 < center.z < 1.24
            and abs(center.x) < 0.38
            and spine_ratio + shoulder_ratio > 0.16
            and spine_ratio + shoulder_ratio >= arm_ratio * 0.72
        ):
            selected.add(polygon.index)
    return selected


def position_components(source, face_indices):
    """Agrupa caras por posiciones soldadas, ignorando duplicados de costuras UV."""
    faces = set(face_indices)
    parent = {face_index: face_index for face_index in faces}

    def find(face_index):
        while parent[face_index] != face_index:
            parent[face_index] = parent[parent[face_index]]
            face_index = parent[face_index]
        return face_index

    def join(first, second):
        first_root = find(first)
        second_root = find(second)
        if first_root != second_root:
            parent[second_root] = first_root

    owners = {}
    for face_index in faces:
        polygon = source.data.polygons[face_index]
        for vertex_index in polygon.vertices:
            coordinate = source.data.vertices[vertex_index].co
            key = (
                round(coordinate.x, 5),
                round(coordinate.y, 5),
                round(coordinate.z, 5),
            )
            owner = owners.get(key)
            if owner is None:
                owners[key] = face_index
            else:
                join(face_index, owner)

    components = {}
    for face_index in faces:
        components.setdefault(find(face_index), set()).add(face_index)
    return sorted(components.values(), key=len, reverse=True)


def expand_underlay(obj, center_z, x_scale, z_scale):
    inverse = obj.matrix_world.inverted()
    for vertex in obj.data.vertices:
        world = obj.matrix_world @ vertex.co
        world.x *= x_scale
        world.z = center_z + (world.z - center_z) * z_scale
        vertex.co = inverse @ world


def adjust_head_underlay(obj):
    inverse = obj.matrix_world.inverted()
    pivot = Vector((0.0, 0.035, 1.19))
    rotation = Matrix.Rotation(radians(10), 4, 'X')
    for vertex in obj.data.vertices:
        world = obj.matrix_world @ vertex.co
        offset = world - pivot
        offset.x *= 1.15
        offset.y *= 1.07
        offset.z *= 1.04
        world = pivot + rotation @ offset
        world.z -= 0.038
        vertex.co = inverse @ world


def create_hair_shell(head_obj):
    """Extrae cabello corto de la propia forma del cráneo, sin usar una esfera."""
    minimum, maximum = object_bounds(head_obj)
    size = maximum - minimum
    center = (minimum + maximum) * 0.5
    hair = head_obj.copy()
    hair.data = head_obj.data.copy()
    hair.animation_data_clear()
    hair.name = 'LeonidasHair'
    hair.data.name = 'LeonidasHairMesh'
    bpy.context.scene.collection.objects.link(hair)

    mesh = bmesh.new()
    mesh.from_mesh(hair.data)
    cut_height = maximum.z - size.z * 0.215
    inverse = hair.matrix_world.inverted()
    plane_co = inverse @ Vector((center.x, center.y, cut_height))
    plane_no = (
        hair.matrix_world.to_3x3().transposed() @ Vector((0.0, 0.0, 1.0))
    ).normalized()
    bmesh.ops.bisect_plane(
        mesh,
        geom=list(mesh.verts) + list(mesh.edges) + list(mesh.faces),
        dist=0.00001,
        plane_co=plane_co,
        plane_no=plane_no,
        clear_inner=True,
        clear_outer=False,
    )
    loose = [vertex for vertex in mesh.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(mesh, geom=loose, context='VERTS')
    half_width = max(size.x * 0.5, 0.001)
    for vertex in mesh.verts:
        world = hair.matrix_world @ vertex.co
        if abs(world.z - cut_height) <= size.z * 0.006:
            side = min(1.0, abs(world.x - center.x) / half_width)
            center_weight = 1.0 - side
            world.z += size.z * (
                0.045 * side ** 1.5
                - 0.022 * center_weight ** 1.6
            )
            vertex.co = inverse @ world
        else:
            side = min(1.0, abs(world.x - center.x) / half_width)
            height_ratio = max(
                0.0,
                min(1.0, (world.z - cut_height) / max(maximum.z - cut_height, 0.001)),
            )
            world.z += size.z * 0.012 * height_ratio * (1.0 - side) ** 1.5
            vertex.co = inverse @ world
    bmesh.ops.recalc_face_normals(mesh, faces=list(mesh.faces))
    for vertex in mesh.verts:
        if vertex.normal.length_squared > 0:
            world = hair.matrix_world @ vertex.co
            side = min(1.0, abs(world.x - center.x) / half_width)
            thickness = 0.0025 + 0.0045 * (1.0 - side) ** 2
            vertex.co += vertex.normal.normalized() * thickness
    mesh.to_mesh(hair.data)
    mesh.free()

    hair['leonidasPart'] = 'hair'
    hair.data.materials.clear()

    material = bpy.data.materials.new('LeonidasHairMaterial')
    material.use_nodes = True
    nodes = material.node_tree.nodes
    links = material.node_tree.links
    shader = nodes.get('Principled BSDF')
    texture = nodes.new('ShaderNodeTexImage')
    texture_image = bpy.data.images.new(
        'LeonidasHairTexture',
        width=256,
        height=256,
        alpha=True,
    )
    texture_image.colorspace_settings.name = 'sRGB'
    rows, columns = np.mgrid[0:256, 0:256]
    u = columns / 256.0
    v = rows / 256.0
    strands = (
        0.78
        + 0.08 * np.sin(2.0 * np.pi * (24.0 * u + 2.0 * v))
        + 0.055 * np.sin(2.0 * np.pi * (47.0 * u - 3.0 * v))
        + 0.025 * np.sin(2.0 * np.pi * 5.0 * v)
    )
    strands = np.clip(strands, 0.54, 1.0)
    hair_pixels = np.ones((256, 256, 4), dtype=np.float32)
    hair_pixels[:, :, 0] = 0.13 * strands
    hair_pixels[:, :, 1] = 0.040 * strands
    hair_pixels[:, :, 2] = 0.018 * strands
    texture_image.pixels.foreach_set(hair_pixels.reshape(-1))
    texture_image.pack()
    texture.image = texture_image
    links.new(texture.outputs['Color'], shader.inputs['Base Color'])
    shader.inputs['Roughness'].default_value = 0.88
    hair.data.materials.append(material)

    # Mechones cortos peinados hacia atrás: rompen la silueta lisa sin crear
    # una peluca voluminosa ni interferir con el casco.
    surface_points = [
        hair.matrix_world @ vertex.co
        for vertex in hair.data.vertices
    ]
    surface_min_y = min(point.y for point in surface_points)
    surface_max_y = max(point.y for point in surface_points)
    ridge_mesh = bmesh.new()
    ridge_mesh.from_mesh(hair.data)
    x_factors = (-0.72, -0.48, -0.24, 0.0, 0.24, 0.48, 0.72)
    for row_index, progress in enumerate((0.12, 0.29, 0.46, 0.63, 0.80)):
        ridge_y = (
            surface_min_y * (1.0 - progress)
            + surface_max_y * progress
        )
        for column_index, x_factor in enumerate(x_factors):
            wave = 0.025 * np.sin(
                row_index * 1.7 + column_index * 1.15
            )
            ridge_x = center.x + (x_factor + wave) * half_width * 0.72
            nearest = min(
                surface_points,
                key=lambda point: (
                    (point.x - ridge_x) ** 2
                    + (point.y - ridge_y) ** 2
                ),
            )
            center_volume = 1.0 - min(1.0, abs(x_factor))
            lift = (
                0.0018
                + 0.0035 * np.sin(np.pi * progress) * center_volume
            )
            scale_x = 0.0085 + 0.0012 * center_volume
            scale_y = 0.0145 + 0.0015 * np.sin(np.pi * progress)
            scale_z = 0.0052 + 0.0014 * center_volume
            sweep = radians(-14 + 3 * np.sin(column_index + row_index))
            world_matrix = (
                Matrix.Translation(Vector((
                    ridge_x,
                    ridge_y,
                    nearest.z + lift,
                )))
                @ Matrix.Rotation(sweep, 4, 'X')
                @ Matrix.Diagonal(Vector((
                    scale_x,
                    scale_y,
                    scale_z,
                    1.0,
                )))
            )
            bmesh.ops.create_icosphere(
                ridge_mesh,
                subdivisions=2,
                radius=1.0,
                matrix=inverse @ world_matrix,
            )
    bmesh.ops.recalc_face_normals(
        ridge_mesh,
        faces=list(ridge_mesh.faces),
    )
    ridge_mesh.to_mesh(hair.data)
    ridge_mesh.free()
    for polygon in hair.data.polygons:
        polygon.use_smooth = True
    return hair


def rigid_bind(obj, group_name):
    for group in list(obj.vertex_groups):
        obj.vertex_groups.remove(group)
    group = obj.vertex_groups.new(name=group_name)
    group.add(
        [vertex.index for vertex in obj.data.vertices],
        1.0,
        'REPLACE',
    )


bpy.ops.object.select_all(action='SELECT')
bpy.ops.object.delete(use_global=False)
bpy.ops.wm.fbx_import(
    filepath=os.path.join(MODEL_ROOT, 'leonidas-spartan-rigged.fbx')
)
current_armature = next(
    obj for obj in bpy.context.scene.objects if obj.type == 'ARMATURE'
)
current_mesh = max(
    (obj for obj in bpy.context.scene.objects if obj.type == 'MESH'),
    key=lambda obj: len(obj.data.polygons),
)
clean_current_material(current_mesh)
current_objects = set(bpy.context.scene.objects)

bpy.ops.import_scene.gltf(
    filepath=os.path.join(MODEL_ROOT, 'leonidas-spartan-free.glb')
)
donor_objects = [obj for obj in bpy.context.scene.objects if obj not in current_objects]
donor_body = max(
    (
        obj
        for obj in donor_objects
        if obj.type == 'MESH'
        and any(
            slot.material and normalized(slot.material.name) == 'body'
            for slot in obj.material_slots
        )
    ),
    key=lambda obj: len(obj.data.polygons),
)

helmet_faces, chest_faces = removable_shell_faces(current_mesh)
all_faces = set(range(len(current_mesh.data.polygons)))
body = keep_faces(
    current_mesh,
    all_faces - helmet_faces - chest_faces,
    'LeonidasBody',
    'body',
)
helmet = keep_faces(current_mesh, helmet_faces, 'LeonidasHelmet', 'helmet')
chest = keep_faces(current_mesh, chest_faces, 'LeonidasChest', 'chest')
assign_body_semantic_materials(body)
assign_solid_palette_material(helmet, 'metal')
assign_chest_semantic_materials(chest)
add_helmet_visor(helmet)

anatomy_source = donor_body.copy()
anatomy_source.data = donor_body.data.copy()
bpy.context.scene.collection.objects.link(anatomy_source)
print(
    'LEONIDAS_SOURCE_BOUNDS',
    'current',
    tuple(round(value, 4) for value in object_bounds(current_mesh)[0]),
    tuple(round(value, 4) for value in object_bounds(current_mesh)[1]),
    'donor',
    tuple(round(value, 4) for value in object_bounds(anatomy_source)[0]),
    tuple(round(value, 4) for value in object_bounds(anatomy_source)[1]),
)
transform_donor_body(anatomy_source, current_mesh)
remap_vertex_groups(anatomy_source)
attach_to_current_rig(anatomy_source, current_mesh, current_armature)
harmonize_skin_material(anatomy_source, current_mesh)
print(
    'LEONIDAS_ANATOMY_BOUNDS',
    tuple(round(value, 4) for value in mesh_vertex_bounds(anatomy_source)[0]),
    tuple(round(value, 4) for value in mesh_vertex_bounds(anatomy_source)[1]),
)
print(
    'LEONIDAS_ANATOMY_MATRIX',
    [[round(value, 4) for value in row] for row in anatomy_source.matrix_world],
)

head_candidates = anatomy_faces(anatomy_source, 'head')
torso_candidates = anatomy_faces(anatomy_source, 'torso')
head_components = position_components(anatomy_source, head_candidates)
torso_components = position_components(anatomy_source, torso_candidates)
full_anatomy_components = position_components(
    anatomy_source,
    set(range(len(anatomy_source.data.polygons))),
)
hair_faces = set()
if head_components:
    hair_seed = head_components[0]
    hair_faces = max(
        full_anatomy_components,
        key=lambda component: len(component & hair_seed),
    )
print(
    'LEONIDAS_ANATOMY_COMPONENTS',
    {
        'head': [len(component) for component in head_components[:12]],
        'torso': [len(component) for component in torso_components[:12]],
    },
)
head_underlay = keep_faces(
    anatomy_source,
    (
        set().union(hair_faces, *head_components[1:4])
        if len(head_components) >= 4
        else set().union(*head_components)
        if head_components
        else set()
    ),
    'LeonidasHeadUnderlay',
    'headUnderlay',
)
adjust_head_underlay(head_underlay)
hair = create_hair_shell(head_underlay)
torso_underlay = keep_faces(
    anatomy_source,
    set().union(*torso_components[:1]) if torso_components else set(),
    'LeonidasTorsoUnderlay',
    'torsoUnderlay',
)
expand_underlay(torso_underlay, center_z=0.9, x_scale=1.035, z_scale=1.0)
rigid_bind(head_underlay, 'mixamorig:Head')
rigid_bind(hair, 'mixamorig:Head')
rigid_bind(torso_underlay, 'mixamorig:Spine1')

for obj in (body, helmet, chest, head_underlay, torso_underlay, hair):
    attach_to_current_rig(obj, current_mesh, current_armature)

remove_names = {
    obj.name
    for obj in list(donor_objects) + [donor_body, anatomy_source, current_mesh]
    if obj
}
for name in remove_names:
    obj = bpy.data.objects.get(name)
    if obj:
        bpy.data.objects.remove(obj, do_unlink=True)

current_armature.name = 'LeonidasRig'
current_armature['leonidasRig'] = True
for obj in bpy.context.scene.objects:
    obj.select_set(False)
for obj in (current_armature, body, helmet, chest, head_underlay, torso_underlay, hair):
    obj.hide_viewport = False
    obj.hide_render = False
    obj.select_set(True)
bpy.context.view_layer.objects.active = current_armature

bpy.ops.export_scene.gltf(
    filepath=OUTPUT_PATH,
    export_format='GLB',
    use_selection=True,
    export_animations=True,
    export_skins=True,
    export_morph=True,
    export_extras=True,
    export_yup=True,
)
print('LEONIDAS_MODULAR_OUTPUT', OUTPUT_PATH)
print(
    'LEONIDAS_MODULAR_PARTS',
    {
        'body_faces': len(body.data.polygons),
        'helmet_faces': len(helmet.data.polygons),
        'chest_faces': len(chest.data.polygons),
        'head_underlay_faces': len(head_underlay.data.polygons),
        'torso_underlay_faces': len(torso_underlay.data.polygons),
        'hair_faces': len(hair.data.polygons),
    },
)
