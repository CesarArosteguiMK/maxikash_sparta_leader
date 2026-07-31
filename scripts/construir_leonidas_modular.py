"""Construye un prototipo modular de Leonidas a partir de los modelos locales.

El FBX conserva el personaje, rig y animación actuales. El GLB alternativo solo
aporta las superficies anatómicas que el FBX no contiene debajo del casco y la
pechera. El resultado debe revisarse visualmente antes de habilitarse.
"""

import os
import sys
from math import cos, pi, radians, sin

import bmesh
import bpy
import numpy as np
from mathutils import Matrix, Vector


PROJECT_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
SCRIPT_ROOT = os.path.dirname(__file__)
if SCRIPT_ROOT not in sys.path:
    sys.path.insert(0, SCRIPT_ROOT)

from leonidas_equipment import build_corporate_shield, build_spartan_spear


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
    protected_original_faces = set()
    protected_metal_faces = set()

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
        protected_metal = (
            foot_ratio > 0.12
            or (
                metal_limb_ratio > 0.08
                and center.z < 0.48
            )
        )
        protected_original = (
            not protected_metal
            and (
                skin_ratio >= 0.5
                or protected_upper_leg
                or (center.z > 1.0 and head_ratio > 0.18)
            )
        )
        if protected_metal:
            protected_metal_faces.add(polygon.index)
        elif protected_original:
            protected_original_faces.add(polygon.index)

        if protected_metal:
            # El modelo no tiene pie desnudo: esta zona corresponde a la bota
            # y su puntera. Debe pintarse completa, no por manchas del atlas.
            role = 'metal'
        elif protected_original:
            role = 'original'
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

    # Una isla UV puede contener piel y vestuario. Las protecciones por cara
    # se aplican al final para que el voto de la isla jamás pinte anatomía ni
    # deje una puntera o greba con el color del faldón.
    for face_index in protected_original_faces:
        obj.data.polygons[face_index].material_index = material_indices['original']
    for face_index in protected_metal_faces:
        obj.data.polygons[face_index].material_index = material_indices['metal']
    counts = {role: 0 for role in roles}
    for polygon in obj.data.polygons:
        counts[roles[polygon.material_index]] += 1

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
    """Construye una abertura en T angular y un penacho de crin volumétrico."""
    minimum, maximum = mesh_vertex_bounds(obj)
    size = maximum - minimum
    center_x = (minimum.x + maximum.x) * 0.5
    half_width = size.x * 0.5
    inverse = obj.matrix_world.inverted()
    old_vertex_count = len(obj.data.vertices)

    visor = bpy.data.materials.new('LeonidasVisorMaterial')
    visor.use_nodes = True
    visor.use_backface_culling = False
    shader = visor.node_tree.nodes.get('Principled BSDF')
    shader.inputs['Base Color'].default_value = (0.008, 0.012, 0.018, 1.0)
    shader.inputs['Metallic'].default_value = 0.04
    shader.inputs['Roughness'].default_value = 0.72
    obj.data.materials.append(visor)
    visor_index = len(obj.data.materials) - 1

    crest_materials = []
    for name, color in (
        ('LeonidasCrestRed', (0.34, 0.008, 0.014, 1.0)),
        ('LeonidasCrestDark', (0.24, 0.004, 0.008, 1.0)),
        ('LeonidasCrestHighlight', (0.41, 0.014, 0.020, 1.0)),
    ):
        material = bpy.data.materials.new(name)
        material.use_nodes = True
        material.use_backface_culling = False
        crest_shader = material.node_tree.nodes.get('Principled BSDF')
        crest_shader.inputs['Base Color'].default_value = color
        crest_shader.inputs['Metallic'].default_value = 0.01
        crest_shader.inputs['Roughness'].default_value = 0.78
        obj.data.materials.append(material)
        crest_materials.append(len(obj.data.materials) - 1)

    mesh = bmesh.new()
    mesh.from_mesh(obj.data)

    def curved_front_y(x_position, z_position):
        normalized_x = (x_position - center_x) / max(half_width, 0.0001)
        normalized_z = (
            z_position - (minimum.z + size.z * 0.52)
        ) / max(size.z, 0.0001)
        return (
            minimum.y
            - size.y * 0.002
            + size.y * 0.11 * normalized_x * normalized_x
            + size.y * 0.035 * normalized_z * normalized_z
        )

    visor_outline = (
        (0.050, 0.610),
        (0.635, 0.630),
        (0.585, 0.580),
        (0.175, 0.548),
        (0.300, 0.265),
        (0.072, 0.292),
        (0.055, 0.565),
    )
    visor_center_x = sum(point[0] for point in visor_outline) / len(visor_outline)
    visor_center_z = sum(point[1] for point in visor_outline) / len(visor_outline)

    def point_in_outline(x_ratio, z_ratio):
        inside = False
        previous = len(visor_outline) - 1
        for current, (current_x, current_z) in enumerate(visor_outline):
            previous_x, previous_z = visor_outline[previous]
            vertical_delta = previous_z - current_z
            crosses = (
                (current_z > z_ratio) != (previous_z > z_ratio)
                and abs(vertical_delta) > 0.000001
                and x_ratio
                < (
                    (previous_x - current_x)
                    * (z_ratio - current_z)
                    / vertical_delta
                    + current_x
                )
            )
            if crosses:
                inside = not inside
            previous = current
        return inside

    # Se retiran caras reales de la máscara frontal. La oscuridad se colocará
    # al fondo de la cavidad, no encima del metal.
    opening_faces = []
    front_limit = minimum.y + size.y * 0.34
    for face in list(mesh.faces):
        sample = obj.matrix_world @ face.calc_center_median()
        x_ratio = abs(sample.x - center_x) / max(half_width, 0.0001)
        z_ratio = (sample.z - minimum.z) / max(size.z, 0.0001)
        should_open = (
            sample.y <= front_limit
            and point_in_outline(x_ratio, z_ratio)
        )
        if should_open:
            opening_faces.append(face)
    if opening_faces:
        bmesh.ops.delete(mesh, geom=opening_faces, context='FACES')

    def add_visor_half(sign):
        # Una máscara frontal limpia cubre la triangulación irregular del corte.
        # Tras ella, un segundo borde se inclina hacia la cámara oscura.
        bezel_outer_vertices = []
        bezel_inner_vertices = []
        cavity_vertices = []
        cavity_world_points = []
        cavity_depth = size.y * 0.035
        for x_ratio, z_ratio in visor_outline:
            bezel_outer_x_ratio = visor_center_x + (
                x_ratio - visor_center_x
            ) * 1.13
            bezel_outer_z_ratio = visor_center_z + (
                z_ratio - visor_center_z
            ) * 1.13
            bezel_inner_x_ratio = visor_center_x + (
                x_ratio - visor_center_x
            ) * 0.86
            bezel_inner_z_ratio = visor_center_z + (
                z_ratio - visor_center_z
            ) * 0.86
            cavity_x_ratio = visor_center_x + (
                x_ratio - visor_center_x
            ) * 0.76
            cavity_z_ratio = visor_center_z + (
                z_ratio - visor_center_z
            ) * 0.76

            bezel_outer_x = (
                center_x + sign * half_width * bezel_outer_x_ratio
            )
            bezel_outer_z = minimum.z + size.z * bezel_outer_z_ratio
            bezel_inner_x = (
                center_x + sign * half_width * bezel_inner_x_ratio
            )
            bezel_inner_z = minimum.z + size.z * bezel_inner_z_ratio
            cavity_x = center_x + sign * half_width * cavity_x_ratio
            cavity_z = minimum.z + size.z * cavity_z_ratio

            bezel_outer_y = curved_front_y(
                bezel_outer_x,
                bezel_outer_z,
            )
            bezel_inner_y = curved_front_y(
                bezel_inner_x,
                bezel_inner_z,
            )
            cavity_y = curved_front_y(cavity_x, cavity_z)
            bezel_outer_world = Vector((
                bezel_outer_x,
                bezel_outer_y - size.y * 0.006,
                bezel_outer_z,
            ))
            bezel_inner_world = Vector((
                bezel_inner_x,
                bezel_inner_y - size.y * 0.006,
                bezel_inner_z,
            ))
            cavity_world = Vector((
                cavity_x,
                cavity_y + cavity_depth,
                cavity_z,
            ))
            bezel_outer_vertices.append(
                mesh.verts.new(inverse @ bezel_outer_world)
            )
            bezel_inner_vertices.append(
                mesh.verts.new(inverse @ bezel_inner_world)
            )
            cavity_vertices.append(mesh.verts.new(inverse @ cavity_world))
            cavity_world_points.append(cavity_world)

        center = sum(cavity_world_points, Vector()) / len(cavity_world_points)
        center_vertex = mesh.verts.new(inverse @ center)
        black_faces = 0
        wall_faces = 0
        bezel_faces = 0
        for index in range(len(cavity_vertices)):
            following = (index + 1) % len(cavity_vertices)
            if sign < 0:
                face_vertices = [
                    center_vertex,
                    cavity_vertices[index],
                    cavity_vertices[following],
                ]
            else:
                face_vertices = [
                    center_vertex,
                    cavity_vertices[following],
                    cavity_vertices[index],
                ]
            face = mesh.faces.new(face_vertices)
            face.material_index = visor_index
            black_faces += 1

            bezel = mesh.faces.new([
                bezel_outer_vertices[index],
                bezel_outer_vertices[following],
                bezel_inner_vertices[following],
                bezel_inner_vertices[index],
            ])
            bezel.material_index = 0
            bezel.smooth = True
            bezel_faces += 1

            wall = mesh.faces.new([
                bezel_inner_vertices[index],
                bezel_inner_vertices[following],
                cavity_vertices[following],
                cavity_vertices[index],
            ])
            wall.material_index = 0
            wall.smooth = True
            wall_faces += 1
        return black_faces, wall_faces, bezel_faces

    left_visor_faces, left_wall_faces, left_bezel_faces = add_visor_half(-1)
    right_visor_faces, right_wall_faces, right_bezel_faces = add_visor_half(1)
    visor_faces = left_visor_faces + right_visor_faces
    visor_wall_faces = left_wall_faces + right_wall_faces
    visor_bezel_faces = left_bezel_faces + right_bezel_faces

    # El penacho ya no es una placa. Una base baja sigue la cúpula y sostiene
    # cintas de crin solapadas, con puntas desiguales y caída hacia atrás.
    crest_sections = 37
    crest_faces = 0
    rail_height = size.z * 0.050
    rail_half_width = half_width * 0.19
    crest_profile = []
    rail_rings = []
    for section in range(crest_sections):
        progress = section / (crest_sections - 1)
        base_y = minimum.y + size.y * (0.115 + progress * 1.09)
        dome_falloff = pow(progress * 2 - 1, 2)
        base_z = maximum.z - size.z * (
            0.058 + 0.178 * dome_falloff
        )
        arch = sin(pi * progress)
        end_bias = 1.0 - pow(abs(progress * 2 - 1), 0.78)
        strand_height = size.z * (
            0.045
            + 0.395 * pow(max(arch, 0.0), 0.70)
            + 0.026 * end_bias
        )
        top_y = base_y + size.y * (
            0.028
            + 0.085
            * pow(max(arch, 0.0), 0.72)
            * (0.62 + 0.38 * progress)
        )
        crest_profile.append((
            progress,
            base_y,
            base_z,
            top_y,
            strand_height,
            arch,
        ))
        rail_rings.append([
            mesh.verts.new(inverse @ Vector((
                center_x - rail_half_width,
                base_y,
                base_z,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x + rail_half_width,
                base_y,
                base_z,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x - rail_half_width * 0.84,
                base_y,
                base_z + rail_height,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x + rail_half_width * 0.84,
                base_y,
                base_z + rail_height,
            ))),
        ])

    for section in range(crest_sections - 1):
        current = rail_rings[section]
        following = rail_rings[section + 1]
        for indices in (
            (2, 3, 3, 2),
            (0, 2, 2, 0),
            (1, 3, 3, 1),
        ):
            face = mesh.faces.new([
                current[indices[0]],
                current[indices[1]],
                following[indices[2]],
                following[indices[3]],
            ])
            face.material_index = crest_materials[1]
            face.smooth = True
            crest_faces += 1

    for ring in (rail_rings[0], rail_rings[-1]):
        face = mesh.faces.new([ring[0], ring[2], ring[3], ring[1]])
        face.material_index = crest_materials[1]
        crest_faces += 1

    strand_rows = (-0.76, -0.38, 0.0, 0.38, 0.76)
    section_spacing = size.y * 1.09 / (crest_sections - 1)
    for section, profile in enumerate(crest_profile):
        progress, base_y, base_z, top_y, strand_height, arch = profile
        strand_half_width = half_width * (
            0.19 + 0.055 * pow(max(arch, 0.0), 0.70)
        )
        for row_index, row in enumerate(strand_rows):
            irregularity = sin(
                (section * 1.91) + (row_index * 2.73)
            )
            base_world = Vector((
                center_x + strand_half_width * row,
                base_y + size.y * irregularity * 0.006,
                base_z + rail_height * 0.34,
            ))
            tip_world = Vector((
                center_x + strand_half_width * row * 0.64,
                top_y + size.y * irregularity * 0.012,
                base_z
                + strand_height
                + size.z * irregularity * 0.014,
            ))
            base_half_x = half_width * (
                0.044 if row_index == 2 else 0.039
            )
            tip_half_x = base_half_x * 0.58
            base_half_y = section_spacing * 0.78
            tip_half_y = section_spacing * 0.28
            world_vertices = (
                Vector((
                    base_world.x - base_half_x,
                    base_world.y - base_half_y,
                    base_world.z,
                )),
                Vector((
                    base_world.x + base_half_x,
                    base_world.y - base_half_y,
                    base_world.z,
                )),
                Vector((
                    base_world.x + base_half_x,
                    base_world.y + base_half_y,
                    base_world.z,
                )),
                Vector((
                    base_world.x - base_half_x,
                    base_world.y + base_half_y,
                    base_world.z,
                )),
                Vector((
                    tip_world.x - tip_half_x,
                    tip_world.y - tip_half_y,
                    tip_world.z,
                )),
                Vector((
                    tip_world.x + tip_half_x,
                    tip_world.y - tip_half_y,
                    tip_world.z,
                )),
                Vector((
                    tip_world.x + tip_half_x,
                    tip_world.y + tip_half_y,
                    tip_world.z,
                )),
                Vector((
                    tip_world.x - tip_half_x,
                    tip_world.y + tip_half_y,
                    tip_world.z,
                )),
            )
            vertices = [
                mesh.verts.new(inverse @ point)
                for point in world_vertices
            ]
            if (section + row_index) % 11 == 0:
                material_index = crest_materials[2]
            elif section % 6 == 0:
                material_index = crest_materials[1]
            else:
                material_index = crest_materials[0]
            for indices in (
                (0, 1, 2, 3),
                (4, 7, 6, 5),
                (0, 4, 5, 1),
                (1, 5, 6, 2),
                (2, 6, 7, 3),
                (3, 7, 4, 0),
            ):
                face = mesh.faces.new([
                    vertices[index] for index in indices
                ])
                face.material_index = material_index
                face.smooth = True
                crest_faces += 1

    mesh.to_mesh(obj.data)
    mesh.free()

    new_vertex_indices = list(range(old_vertex_count, len(obj.data.vertices)))
    head_group = obj.vertex_groups.get('mixamorig:Head')
    if head_group is None:
        head_group = obj.vertex_groups.new(name='mixamorig:Head')
    head_group.add(new_vertex_indices, 1.0, 'REPLACE')

    obj['leonidasHelmetVisorFaces'] = visor_faces
    obj['leonidasHelmetOpeningFaces'] = len(opening_faces)
    obj['leonidasHelmetVisorWallFaces'] = visor_wall_faces
    obj['leonidasHelmetBezelFaces'] = visor_bezel_faces
    obj['leonidasHelmetCrestFaces'] = crest_faces
    print('LEONIDAS_HELMET_VISOR', {
        'visor_faces': visor_faces,
        'opening_faces': len(opening_faces),
        'wall_faces': visor_wall_faces,
        'bezel_faces': visor_bezel_faces,
        'crest_faces': crest_faces,
    })


def build_clean_helmet(obj):
    """Reemplaza por completo el casco heredado con geometría simétrica limpia."""
    minimum, maximum = mesh_vertex_bounds(obj)
    size = maximum - minimum
    center_x = (minimum.x + maximum.x) * 0.5
    center_y = (minimum.y + maximum.y) * 0.5
    half_width = size.x * 0.5
    inverse = obj.matrix_world.inverted()

    obj.data.clear_geometry()
    metal_index = 0

    visor = bpy.data.materials.new('LeonidasVisorMaterial')
    visor.use_nodes = True
    visor.use_backface_culling = False
    visor_shader = visor.node_tree.nodes.get('Principled BSDF')
    visor_shader.inputs['Base Color'].default_value = (
        0.004,
        0.006,
        0.009,
        1.0,
    )
    visor_shader.inputs['Metallic'].default_value = 0.0
    visor_shader.inputs['Roughness'].default_value = 0.88
    obj.data.materials.append(visor)
    visor_index = len(obj.data.materials) - 1

    crest_materials = []
    for name, color in (
        ('LeonidasCrestRed', (0.34, 0.008, 0.014, 1.0)),
        ('LeonidasCrestDark', (0.22, 0.004, 0.008, 1.0)),
        ('LeonidasCrestHighlight', (0.42, 0.014, 0.020, 1.0)),
    ):
        material = bpy.data.materials.new(name)
        material.use_nodes = True
        material.use_backface_culling = False
        shader = material.node_tree.nodes.get('Principled BSDF')
        shader.inputs['Base Color'].default_value = color
        shader.inputs['Metallic'].default_value = 0.0
        shader.inputs['Roughness'].default_value = 0.82
        obj.data.materials.append(material)
        crest_materials.append(len(obj.data.materials) - 1)

    mesh = bmesh.new()
    shell_faces = 0
    opening_faces = 0
    dome_center_z = minimum.z + size.z * 0.60
    radius_x = half_width * 0.96
    radius_y = size.y * 0.58
    radius_z = size.z * 0.46
    dome_center_y = center_y + size.y * 0.035
    longitude_segments = 32
    latitude_segments = 12

    top = mesh.verts.new(inverse @ Vector((
        center_x,
        dome_center_y,
        dome_center_z + radius_z,
    )))
    rings = []
    for latitude in range(1, latitude_segments + 1):
        theta = 2.16 * latitude / latitude_segments
        ring = []
        for longitude in range(longitude_segments):
            phi = 2 * pi * longitude / longitude_segments
            ring.append(mesh.verts.new(inverse @ Vector((
                center_x + radius_x * sin(theta) * sin(phi),
                dome_center_y + radius_y * sin(theta) * cos(phi),
                dome_center_z + radius_z * cos(theta),
            ))))
        rings.append(ring)

    for longitude in range(longitude_segments):
        following = (longitude + 1) % longitude_segments
        face = mesh.faces.new([
            top,
            rings[0][longitude],
            rings[0][following],
        ])
        face.material_index = metal_index
        face.smooth = True
        shell_faces += 1

    for latitude in range(1, latitude_segments):
        theta_mid = 2.16 * (latitude + 0.5) / latitude_segments
        previous_ring = rings[latitude - 1]
        current_ring = rings[latitude]
        for longitude in range(longitude_segments):
            following = (longitude + 1) % longitude_segments
            phi_mid = 2 * pi * (longitude + 0.5) / longitude_segments
            is_front = cos(phi_mid) < -0.43
            is_face_opening = is_front and theta_mid > 0.72
            if is_face_opening:
                opening_faces += 1
                continue
            face = mesh.faces.new([
                previous_ring[longitude],
                current_ring[longitude],
                current_ring[following],
                previous_ring[following],
            ])
            face.material_index = metal_index
            face.smooth = True
            shell_faces += 1

    # La máscara queda alojada dentro de la silueta de la cúpula. El fondo
    # negro se retrasa lo suficiente para producir sombra real sin convertir
    # la careta en una caja que sobresalga del rostro.
    front_y = minimum.y + size.y * 0.105
    cavity_y = minimum.y + size.y * 0.165
    cavity_outline = (
        (-0.70, 0.16),
        (0.70, 0.16),
        (0.70, 0.66),
        (-0.70, 0.66),
    )
    cavity_vertices = [
        mesh.verts.new(inverse @ Vector((
            center_x + half_width * x_ratio,
            cavity_y,
            minimum.z + size.z * z_ratio,
        )))
        for x_ratio, z_ratio in cavity_outline
    ]
    cavity_face = mesh.faces.new(cavity_vertices)
    cavity_face.material_index = visor_index
    visor_faces = 1

    def add_panel(points, depth_ratio=0.025):
        # Los extremos laterales se retrasan progresivamente: así cada panel
        # abraza la cúpula y deja de parecer una placa plana pegada al rostro.
        panel_depths = [
            size.y * 0.060 * pow(abs(x_ratio), 1.55)
            for x_ratio, _ in points
        ]
        front_vertices = [
            mesh.verts.new(inverse @ Vector((
                center_x + half_width * x_ratio,
                front_y + panel_depths[index],
                minimum.z + size.z * z_ratio,
            )))
            for index, (x_ratio, z_ratio) in enumerate(points)
        ]
        back_vertices = [
            mesh.verts.new(inverse @ Vector((
                center_x + half_width * x_ratio,
                front_y
                + panel_depths[index]
                + size.y * depth_ratio
                + size.y * 0.072 * pow(abs(x_ratio), 1.20),
                minimum.z + size.z * z_ratio,
            )))
            for index, (x_ratio, z_ratio) in enumerate(points)
        ]
        # La cámara observa el frente desde -Y. La cara visible debe apuntar
        # hacia -Y para recibir la iluminación frontal correctamente.
        front_face = mesh.faces.new(list(reversed(front_vertices)))
        front_face.material_index = metal_index
        faces = 1
        back_face = mesh.faces.new(back_vertices)
        back_face.material_index = metal_index
        faces += 1
        for index in range(len(points)):
            following = (index + 1) % len(points)
            side = mesh.faces.new([
                front_vertices[index],
                front_vertices[following],
                back_vertices[following],
                back_vertices[index],
            ])
            side.material_index = metal_index
            faces += 1
        return faces

    right_brow = (
        (0.070, 0.680),
        (0.730, 0.650),
        (0.650, 0.585),
        (0.110, 0.575),
    )
    right_cheek = (
        (0.135, 0.545),
        (0.650, 0.570),
        (0.760, 0.470),
        (0.500, 0.150),
        (0.180, 0.220),
        (0.180, 0.460),
    )

    def mirror(points):
        return tuple((-x_ratio, z_ratio) for x_ratio, z_ratio in reversed(points))

    bezel_faces = 0
    bezel_faces += add_panel(right_brow)
    bezel_faces += add_panel(mirror(right_brow))
    bezel_faces += add_panel(right_cheek, depth_ratio=0.032)
    bezel_faces += add_panel(mirror(right_cheek), depth_ratio=0.032)
    bezel_faces += add_panel((
        (-0.090, 0.680),
        (0.090, 0.680),
        (0.075, 0.400),
        (0.105, 0.180),
        (-0.105, 0.180),
        (-0.075, 0.400),
    ), depth_ratio=0.035)

    # Borde inferior y placas temporales cierran el contorno sin cubrir las
    # ranuras negras de ojos y mejillas.
    wall_faces = 0
    wall_faces += add_panel((
        (-0.505, 0.165),
        (0.505, 0.165),
        (0.430, 0.105),
        (-0.430, 0.105),
    ), depth_ratio=0.025)
    wall_faces += add_panel((
        (-0.735, 0.470),
        (-0.610, 0.675),
        (-0.760, 0.650),
        (-0.820, 0.400),
    ), depth_ratio=0.025)
    wall_faces += add_panel((
        (0.610, 0.675),
        (0.735, 0.470),
        (0.820, 0.400),
        (0.760, 0.650),
    ), depth_ratio=0.025)

    # Base continua del penacho.
    crest_sections = 43
    crest_faces = 0
    rail_height = size.z * 0.050
    rail_half_width = half_width * 0.27
    crest_profile = []
    rail_rings = []
    for section in range(crest_sections):
        progress = section / (crest_sections - 1)
        base_y = minimum.y + size.y * (0.10 + progress * 1.10)
        dome_falloff = pow(progress * 2 - 1, 2)
        base_z = maximum.z - size.z * (
            0.050 + 0.180 * dome_falloff
        )
        arch = sin(pi * progress)
        end_bias = 1.0 - pow(abs(progress * 2 - 1), 0.78)
        strand_height = size.z * (
            0.040
            + 0.430 * pow(max(arch, 0.0), 0.70)
            + 0.024 * end_bias
        )
        top_y = base_y + size.y * (
            0.024
            + 0.078
            * pow(max(arch, 0.0), 0.72)
            * (0.62 + 0.38 * progress)
        )
        crest_profile.append((
            progress,
            base_y,
            base_z,
            top_y,
            strand_height,
            arch,
        ))
        rail_rings.append([
            mesh.verts.new(inverse @ Vector((
                center_x - rail_half_width,
                base_y,
                base_z,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x + rail_half_width,
                base_y,
                base_z,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x - rail_half_width * 0.84,
                base_y,
                base_z + rail_height,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x + rail_half_width * 0.84,
                base_y,
                base_z + rail_height,
            ))),
        ])

    for section in range(crest_sections - 1):
        current = rail_rings[section]
        following = rail_rings[section + 1]
        for indices in (
            (2, 3, 3, 2),
            (0, 2, 2, 0),
            (1, 3, 3, 1),
        ):
            face = mesh.faces.new([
                current[indices[0]],
                current[indices[1]],
                following[indices[2]],
                following[indices[3]],
            ])
            face.material_index = crest_materials[1]
            face.smooth = True
            crest_faces += 1

    strand_rows = (-0.90, -0.60, -0.30, 0.0, 0.30, 0.60, 0.90)
    section_spacing = size.y * 1.10 / (crest_sections - 1)
    for section, profile in enumerate(crest_profile):
        progress, base_y, base_z, top_y, strand_height, arch = profile
        strand_half_width = half_width * (
            0.27 + 0.070 * pow(max(arch, 0.0), 0.70)
        )
        for row_index, row in enumerate(strand_rows):
            irregularity = sin(
                (section * 1.91) + (row_index * 2.73)
            )
            base_world = Vector((
                center_x + strand_half_width * row,
                base_y + size.y * irregularity * 0.004,
                base_z + rail_height * 0.32,
            ))
            tip_world = Vector((
                center_x + strand_half_width * row * 0.70,
                top_y + size.y * irregularity * 0.008,
                base_z
                + strand_height
                + size.z * irregularity * 0.010,
            ))
            base_half_x = half_width * (
                0.040 if row_index == 3 else 0.034
            )
            tip_half_x = base_half_x * 0.60
            base_half_y = section_spacing * 0.80
            tip_half_y = section_spacing * 0.30
            world_vertices = (
                Vector((base_world.x - base_half_x, base_world.y - base_half_y, base_world.z)),
                Vector((base_world.x + base_half_x, base_world.y - base_half_y, base_world.z)),
                Vector((base_world.x + base_half_x, base_world.y + base_half_y, base_world.z)),
                Vector((base_world.x - base_half_x, base_world.y + base_half_y, base_world.z)),
                Vector((tip_world.x - tip_half_x, tip_world.y - tip_half_y, tip_world.z)),
                Vector((tip_world.x + tip_half_x, tip_world.y - tip_half_y, tip_world.z)),
                Vector((tip_world.x + tip_half_x, tip_world.y + tip_half_y, tip_world.z)),
                Vector((tip_world.x - tip_half_x, tip_world.y + tip_half_y, tip_world.z)),
            )
            vertices = [
                mesh.verts.new(inverse @ point)
                for point in world_vertices
            ]
            material_index = crest_materials[0]
            if (section + row_index) % 13 == 0:
                material_index = crest_materials[2]
            elif section % 7 == 0:
                material_index = crest_materials[1]
            for indices in (
                (0, 1, 2, 3),
                (4, 7, 6, 5),
                (0, 4, 5, 1),
                (1, 5, 6, 2),
                (2, 6, 7, 3),
                (3, 7, 4, 0),
            ):
                face = mesh.faces.new([
                    vertices[index] for index in indices
                ])
                face.material_index = material_index
                face.smooth = True
                crest_faces += 1

    bmesh.ops.recalc_face_normals(mesh, faces=list(mesh.faces))
    mesh.to_mesh(obj.data)
    mesh.free()

    for group in list(obj.vertex_groups):
        obj.vertex_groups.remove(group)
    head_group = obj.vertex_groups.new(name='mixamorig:Head')
    head_group.add(
        [vertex.index for vertex in obj.data.vertices],
        1.0,
        'REPLACE',
    )
    obj['leonidasHelmetVisorFaces'] = visor_faces
    obj['leonidasHelmetOpeningFaces'] = opening_faces
    obj['leonidasHelmetVisorWallFaces'] = wall_faces
    obj['leonidasHelmetBezelFaces'] = bezel_faces
    obj['leonidasHelmetCrestFaces'] = crest_faces
    obj['leonidasHelmetShellFaces'] = shell_faces
    print('LEONIDAS_CLEAN_HELMET', {
        'shell_faces': shell_faces,
        'opening_faces': opening_faces,
        'visor_faces': visor_faces,
        'wall_faces': wall_faces,
        'bezel_faces': bezel_faces,
        'crest_faces': crest_faces,
    })


def finish_sculpted_helmet(obj, head_reference=None):
    """Reconstruye un casco corintio ceñido a la anatomía real."""
    source_minimum, source_maximum = mesh_vertex_bounds(obj)
    source_size = source_maximum - source_minimum
    source_center = (source_minimum + source_maximum) * 0.5
    inverse = obj.matrix_world.inverted()

    if head_reference is not None:
        head_minimum, head_maximum = mesh_vertex_bounds(head_reference)
    else:
        head_minimum, head_maximum = source_minimum, source_maximum
    head_size = head_maximum - head_minimum
    head_center = (head_minimum + head_maximum) * 0.5

    # El volumen se calcula desde la cabeza, no desde el casco defectuoso.
    # Sólo se deja 9–15 % de holgura para metal, acolchado y movimiento.
    minimum = Vector((
        head_center.x - head_size.x * 0.59,
        head_minimum.y - head_size.y * 0.055,
        head_minimum.z - head_size.z * 0.045,
    ))
    size = Vector((
        head_size.x * 1.18,
        head_size.y * 1.11,
        head_size.z * 1.12,
    ))
    maximum = minimum + size
    center_x = head_center.x
    half_width = size.x * 0.5
    original_faces = len(obj.data.polygons)
    # La carcasa esculpida original tiene mucha más resolución y una silueta
    # posterior bastante mejor que cualquier cúpula procedural. Se ajusta por
    # eje a la cabeza real para conservar su topología sin heredar su tamaño.
    target_shell_size = Vector((
        head_size.x * 1.15,
        head_size.y * 1.075,
        head_size.z * 1.08,
    ))
    target_shell_center = Vector((
        head_center.x,
        head_center.y + head_size.y * 0.005,
        head_center.z + head_size.z * 0.010,
    ))
    shell_fit = Vector((
        target_shell_size.x / max(source_size.x, 0.0001),
        target_shell_size.y / max(source_size.y, 0.0001),
        target_shell_size.z / max(source_size.z, 0.0001),
    ))
    for vertex in obj.data.vertices:
        world = obj.matrix_world @ vertex.co
        delta = world - source_center
        fitted = target_shell_center + Vector((
            delta.x * shell_fit.x,
            delta.y * shell_fit.y,
            delta.z * shell_fit.z,
        ))
        vertex.co = inverse @ fitted
    obj.data.update()
    helmet_scale = shell_fit.x
    helmet_lift = 0.0

    visor = bpy.data.materials.new('LeonidasVisorMaterial')
    visor.use_nodes = True
    visor.use_backface_culling = False
    visor_shader = visor.node_tree.nodes.get('Principled BSDF')
    visor_shader.inputs['Base Color'].default_value = (
        0.003,
        0.004,
        0.006,
        1.0,
    )
    visor_shader.inputs['Metallic'].default_value = 0.0
    visor_shader.inputs['Roughness'].default_value = 0.92
    obj.data.materials.append(visor)
    visor_index = len(obj.data.materials) - 1

    patina = create_palette_material(
        'LeonidasHelmetPatina',
        'metal',
        metalness=0.44,
        roughness=0.56,
    )
    patina['leonidasTone'] = 0.70
    patina['leonidasRoughnessOffset'] = 0.12
    obj.data.materials.append(patina)
    patina_index = len(obj.data.materials) - 1

    highlight = create_palette_material(
        'LeonidasHelmetHighlight',
        'metal',
        metalness=0.66,
        roughness=0.30,
    )
    highlight['leonidasTone'] = 1.05
    highlight['leonidasRoughnessOffset'] = -0.10
    obj.data.materials.append(highlight)
    highlight_index = len(obj.data.materials) - 1

    mask_steel = bpy.data.materials.new('LeonidasHelmetMaskSteel')
    mask_steel.use_nodes = True
    mask_steel_shader = mask_steel.node_tree.nodes.get('Principled BSDF')
    mask_steel_shader.inputs['Base Color'].default_value = (
        0.090,
        0.105,
        0.125,
        1.0,
    )
    mask_steel_shader.inputs['Metallic'].default_value = 0.88
    mask_steel_shader.inputs['Roughness'].default_value = 0.40
    obj.data.materials.append(mask_steel)
    mask_steel_index = len(obj.data.materials) - 1

    mask_edge = bpy.data.materials.new('LeonidasHelmetMaskEdge')
    mask_edge.use_nodes = True
    mask_edge_shader = mask_edge.node_tree.nodes.get('Principled BSDF')
    mask_edge_shader.inputs['Base Color'].default_value = (
        0.56,
        0.62,
        0.70,
        1.0,
    )
    mask_edge_shader.inputs['Metallic'].default_value = 0.94
    mask_edge_shader.inputs['Roughness'].default_value = 0.23
    obj.data.materials.append(mask_edge)
    mask_edge_index = len(obj.data.materials) - 1

    crest_materials = []
    for name, color in (
        ('LeonidasCrestRed', (0.31, 0.006, 0.010, 1.0)),
        ('LeonidasCrestDark', (0.18, 0.003, 0.006, 1.0)),
        ('LeonidasCrestHighlight', (0.40, 0.012, 0.016, 1.0)),
    ):
        material = bpy.data.materials.new(name)
        material.use_nodes = True
        material.use_backface_culling = False
        shader = material.node_tree.nodes.get('Principled BSDF')
        shader.inputs['Base Color'].default_value = color
        shader.inputs['Metallic'].default_value = 0.0
        shader.inputs['Roughness'].default_value = 0.86
        obj.data.materials.append(material)
        crest_materials.append(len(obj.data.materials) - 1)

    mesh = bmesh.new()
    mesh.from_mesh(obj.data)
    old_vertex_count = len(mesh.verts)

    # El recurso antiguo mezcla corona y rostro en una sola topología. Se
    # descarta para impedir que sobrevivan sienes, nariz o mejillas estampadas.
    # La sustitución no es una esfera simple: más abajo se construye una
    # carcasa forjada continua de alta densidad y ajustada a la anatomía.
    front_relief_faces = list(mesh.faces)
    detached_relief_faces = []
    mesh.clear()
    shell_faces = 0

    # El frente queda abierto: la anatomía real permanece visible dentro de
    # la cúpula y nunca recibe el material del casco.
    panel_front_y = head_minimum.y - head_size.y * 0.045
    panel_faces = 0

    def curved_panel_y(x_ratio, z_ratio):
        return (
            panel_front_y
            + size.y * 0.125 * pow(abs(x_ratio), 1.70)
            + size.y * 0.010 * pow(abs(z_ratio - 0.50), 1.35)
        )

    def add_curved_panel(
        points,
        material_index=0,
        depth_ratio=0.026,
        edge_material_index=None,
        front_offset_ratio=0.0,
    ):
        """Crea una placa forjada curva, cerrada y con canto independiente."""
        nonlocal panel_faces
        front_vertices = []
        back_vertices = []
        front_world = []
        back_world = []
        for x_ratio, z_ratio in points:
            y = curved_panel_y(x_ratio, z_ratio) + (
                size.y * front_offset_ratio
            )
            front_point = Vector((
                center_x + half_width * x_ratio,
                y,
                minimum.z + size.z * z_ratio,
            ))
            back_point = Vector((
                front_point.x,
                y + size.y * (
                    depth_ratio + 0.010 * abs(x_ratio)
                ),
                front_point.z,
            ))
            front_world.append(front_point)
            back_world.append(back_point)
            front_vertices.append(mesh.verts.new(inverse @ front_point))
            back_vertices.append(mesh.verts.new(inverse @ back_point))

        count = len(points)
        front = mesh.faces.new(list(reversed(front_vertices)))
        front.material_index = material_index
        front.smooth = True
        back = mesh.faces.new(back_vertices)
        back.material_index = material_index
        back.smooth = True
        panel_faces += 2
        for index in range(count):
            following = (index + 1) % count
            side = mesh.faces.new([
                front_vertices[index],
                front_vertices[following],
                back_vertices[following],
                back_vertices[index],
            ])
            side.material_index = (
                edge_material_index
                if edge_material_index is not None
                else material_index
            )
            panel_faces += 1

    def mirror_panel(points):
        return tuple(
            (-x_ratio, z_ratio)
            for x_ratio, z_ratio in reversed(points)
        )

    def add_integrated_corinthian_mask():
        """Máscara curva continua con aberturas reales, no una placa pegada."""
        nonlocal panel_faces
        columns = 72
        rows = 56
        minimum_z_ratio = 0.10
        maximum_z_ratio = 0.80
        minimum_x_ratio = -0.91
        maximum_x_ratio = 0.91
        front_vertices = {}
        back_vertices = {}

        def point_ratios(column, row):
            return (
                minimum_x_ratio
                + (maximum_x_ratio - minimum_x_ratio) * column / columns,
                minimum_z_ratio
                + (maximum_z_ratio - minimum_z_ratio) * row / rows,
            )

        def outline_half_width(z_ratio):
            if z_ratio < 0.18:
                return 0.36 + (z_ratio - minimum_z_ratio) * 0.80
            if z_ratio < 0.34:
                return 0.42 + (z_ratio - 0.18) * 2.88
            if z_ratio < 0.62:
                return 0.88 - (z_ratio - 0.34) * 0.12
            return 0.846 - (z_ratio - 0.62) * 0.94

        def active_at(x_ratio, z_ratio):
            absolute_x = abs(x_ratio)
            if absolute_x > outline_half_width(z_ratio):
                return False
            eye_center = 0.610 - max(absolute_x - 0.15, 0.0) * 0.105
            eye_opening = (
                0.14 < absolute_x < 0.70
                and abs(z_ratio - eye_center) < 0.052
            )
            vertical_opening = (
                absolute_x < 0.105
                and 0.16 < z_ratio < 0.610
            )
            return not (eye_opening or vertical_opening)

        def cell_active(column, row):
            x_ratio = (
                minimum_x_ratio
                + (maximum_x_ratio - minimum_x_ratio)
                * (column + 0.5)
                / columns
            )
            z_ratio = (
                minimum_z_ratio
                + (maximum_z_ratio - minimum_z_ratio)
                * (row + 0.5)
                / rows
            )
            return active_at(x_ratio, z_ratio)

        def mask_vertex(column, row, back=False):
            cache = back_vertices if back else front_vertices
            key = (column, row)
            if key in cache:
                return cache[key]
            x_ratio, z_ratio = point_ratios(column, row)
            y = curved_panel_y(x_ratio, z_ratio)
            if back:
                y += size.y * (0.036 + 0.008 * abs(x_ratio))
            vertex = mesh.verts.new(inverse @ Vector((
                center_x + half_width * x_ratio,
                y,
                minimum.z + size.z * z_ratio,
            )))
            cache[key] = vertex
            return vertex

        edge_offsets = (
            (-1, 0),
            (1, 0),
            (0, -1),
            (0, 1),
        )
        edge_corners = (
            ((0, 0), (0, 1)),
            ((1, 1), (1, 0)),
            ((1, 0), (0, 0)),
            ((0, 1), (1, 1)),
        )

        for row in range(rows):
            for column in range(columns):
                if not cell_active(column, row):
                    continue
                corners = (
                    (column, row),
                    (column + 1, row),
                    (column + 1, row + 1),
                    (column, row + 1),
                )
                boundary = any(
                    column + offset_x < 0
                    or column + offset_x >= columns
                    or row + offset_z < 0
                    or row + offset_z >= rows
                    or not cell_active(
                        column + offset_x,
                        row + offset_z,
                    )
                    for offset_x, offset_z in edge_offsets
                )
                material_index = (
                    mask_edge_index
                    if boundary
                    else mask_steel_index
                )
                front = mesh.faces.new(list(reversed([
                    mask_vertex(corner_column, corner_row)
                    for corner_column, corner_row in corners
                ])))
                front.material_index = material_index
                front.smooth = True
                back = mesh.faces.new([
                    mask_vertex(corner_column, corner_row, True)
                    for corner_column, corner_row in corners
                ])
                back.material_index = mask_steel_index
                back.smooth = True
                panel_faces += 2

                for edge_index, (offset_x, offset_z) in enumerate(
                    edge_offsets
                ):
                    neighbor_column = column + offset_x
                    neighbor_row = row + offset_z
                    if (
                        0 <= neighbor_column < columns
                        and 0 <= neighbor_row < rows
                        and cell_active(neighbor_column, neighbor_row)
                    ):
                        continue
                    first_offset, second_offset = edge_corners[edge_index]
                    first = (
                        column + first_offset[0],
                        row + first_offset[1],
                    )
                    second = (
                        column + second_offset[0],
                        row + second_offset[1],
                    )
                    wall = mesh.faces.new((
                        mask_vertex(*first),
                        mask_vertex(*second),
                        mask_vertex(*second, True),
                        mask_vertex(*first, True),
                    ))
                    wall.material_index = mask_edge_index
                    panel_faces += 1

    def add_forged_corinthian_mask():
        """Construye placas angulares siguiendo la referencia de Esparta."""
        left_cheek = (
            (-0.90, 0.62),
            (-0.67, 0.56),
            (-0.18, 0.57),
            (-0.17, 0.43),
            (-0.22, 0.18),
            (-0.48, 0.08),
            (-0.83, 0.27),
            (-0.98, 0.43),
        )
        right_cheek = mirror_panel(left_cheek)
        nasal_guard = (
            (-0.13, 0.70),
            (0.13, 0.70),
            (0.11, 0.43),
            (0.075, 0.29),
            (0.0, 0.21),
            (-0.075, 0.29),
            (-0.11, 0.43),
        )
        left_brow = (
            (-0.91, 0.65),
            (-0.14, 0.66),
            (-0.17, 0.58),
            (-0.64, 0.53),
            (-0.86, 0.57),
        )
        right_brow = mirror_panel(left_brow)
        crown_ridge = (
            (-0.055, 0.91),
            (0.055, 0.91),
            (0.15, 0.70),
            (0.08, 0.60),
            (0.0, 0.54),
            (-0.08, 0.60),
            (-0.15, 0.70),
        )

        for cheek, tone in (
            (left_cheek, mask_steel_index),
            (right_cheek, patina_index),
        ):
            add_curved_panel(
                cheek,
                tone,
                depth_ratio=0.022,
                edge_material_index=mask_edge_index,
            )
        add_curved_panel(
            nasal_guard,
            highlight_index,
            depth_ratio=0.020,
            edge_material_index=mask_edge_index,
            front_offset_ratio=-0.010,
        )
        for brow in (left_brow, right_brow):
            add_curved_panel(
                brow,
                mask_edge_index,
                depth_ratio=0.014,
                edge_material_index=mask_steel_index,
                front_offset_ratio=-0.014,
            )
        add_curved_panel(
            crown_ridge,
            mask_edge_index,
            depth_ratio=0.014,
            edge_material_index=mask_steel_index,
            front_offset_ratio=-0.012,
        )

    add_integrated_corinthian_mask()
    add_curved_panel(
        (
            (-0.055, 0.94),
            (0.055, 0.94),
            (0.16, 0.78),
            (0.11, 0.68),
            (0.0, 0.60),
            (-0.11, 0.68),
            (-0.16, 0.78),
        ),
        mask_edge_index,
        depth_ratio=0.014,
        edge_material_index=mask_steel_index,
        front_offset_ratio=-0.012,
    )

    # Cámara oscura detrás de las aberturas: oculta ojos y nariz sin alterar
    # ni recolorear la cara anatómica. Está unos milímetros detrás del metal.
    visor_y = panel_front_y + head_size.y * 0.025
    def add_visor_insert(points):
        vertices = [
            mesh.verts.new(inverse @ Vector((
                center_x + half_width * x_ratio,
                visor_y,
                minimum.z + size.z * z_ratio,
            )))
            for x_ratio, z_ratio in points
        ]
        face = mesh.faces.new(list(reversed(vertices)))
        face.material_index = visor_index

    add_visor_insert((
        (-0.70, 0.535),
        (-0.15, 0.590),
        (-0.15, 0.645),
        (-0.70, 0.590),
    ))
    add_visor_insert((
        (0.15, 0.590),
        (0.70, 0.535),
        (0.70, 0.590),
        (0.15, 0.645),
    ))
    add_visor_insert((
        (-0.105, 0.16),
        (0.105, 0.16),
        (0.105, 0.61),
        (-0.105, 0.61),
    ))
    visor_faces = 3

    # Corona corintia de alta densidad. El perfil es un elipsoide alargado,
    # abierto sólo en el frente inferior; 96 segmentos eliminan los reflejos
    # cuadrados de la versión facetada y el faldón posterior protege la nuca.
    dome_faces = 0
    dome_segments = 96
    dome_latitudes = 38
    dome_center_y = head_center.y + head_size.y * 0.012
    dome_center_z = head_center.z + head_size.z * 0.005
    dome_radius_x = head_size.x * 0.575
    dome_radius_y = head_size.y * 0.555
    dome_radius_z = head_size.z * 0.565
    dome_rings = []
    theta_start = 0.035
    theta_end = 2.49

    for latitude in range(dome_latitudes):
        progress = latitude / (dome_latitudes - 1)
        theta = theta_start + (theta_end - theta_start) * progress
        sin_theta = sin(theta)
        cos_theta = cos(theta)
        ring = []
        for segment in range(dome_segments):
            phi = -pi + 2.0 * pi * segment / dome_segments
            front_factor = max(cos(phi), 0.0)
            # Martillado extremadamente sutil: rompe el brillo perfecto sin
            # deformar la silueta ni producir facetas visibles.
            hammered = (
                1.0
                + 0.0035
                * sin(segment * 0.73 + latitude * 1.17)
                * sin_theta
            )
            nape_drop = (
                head_size.z
                * 0.040
                * max(-cos(phi), 0.0)
                * pow(progress, 2.2)
            )
            ring.append(mesh.verts.new(inverse @ Vector((
                center_x
                + dome_radius_x * sin_theta * sin(phi) * hammered,
                dome_center_y
                - dome_radius_y * sin_theta * cos(phi) * hammered,
                dome_center_z + dome_radius_z * cos_theta - nape_drop,
            ))))
        dome_rings.append(ring)

    for latitude in range(dome_latitudes - 1):
        current = dome_rings[latitude]
        following_ring = dome_rings[latitude + 1]
        progress = (latitude + 0.5) / (dome_latitudes - 1)
        for segment in range(dome_segments):
            following = (segment + 1) % dome_segments
            phi = -pi + 2.0 * pi * (segment + 0.5) / dome_segments
            # A partir de la frente se abre una ventana que enlaza con la
            # careta. Laterales, coronilla y nuca siguen siendo una pieza.
            if progress > 0.48 and abs(phi) < 0.66:
                continue
            face = mesh.faces.new([
                current[segment],
                current[following],
                following_ring[following],
                following_ring[segment],
            ])
            face.material_index = mask_steel_index
            face.smooth = True
            dome_faces += 1

    # Cierra el borde inferior posterior para que el perfil no termine como
    # una lámina sin espesor cuando el usuario gira el avatar.
    last_ring = dome_rings[-1]
    inner_ring = []
    for segment, outer_vertex in enumerate(last_ring):
        outer_world = obj.matrix_world @ outer_vertex.co
        radial = Vector((
            outer_world.x - center_x,
            outer_world.y - dome_center_y,
            0.0,
        ))
        if radial.length > 0.0001:
            radial.normalize()
        inner_world = outer_world - radial * head_size.x * 0.020
        inner_world.z += head_size.z * 0.018
        inner_ring.append(mesh.verts.new(inverse @ inner_world))
    for segment in range(dome_segments):
        following = (segment + 1) % dome_segments
        edge = mesh.faces.new([
            last_ring[segment],
            last_ring[following],
            inner_ring[following],
            inner_ring[segment],
        ])
        edge.material_index = mask_edge_index
        edge.smooth = True
        dome_faces += 1
    shell_faces = dome_faces

    crest_sections = 49
    profile = []
    rail_rings = []
    rail_half_width = half_width * 0.25
    for section in range(crest_sections):
        progress = section / (crest_sections - 1)
        arch = pow(max(sin(pi * progress), 0.0), 0.70)
        edge = pow(abs(progress * 2 - 1), 1.55)
        base_y = minimum.y + size.y * (0.08 + progress * 1.08)
        base_z = maximum.z - size.z * (0.040 + 0.155 * edge)
        rail_height = size.z * 0.045
        profile.append((progress, arch, base_y, base_z, rail_height))
        rail_rings.append([
            mesh.verts.new(inverse @ Vector((
                center_x - rail_half_width,
                base_y,
                base_z,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x + rail_half_width,
                base_y,
                base_z,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x - rail_half_width * 0.88,
                base_y,
                base_z + rail_height,
            ))),
            mesh.verts.new(inverse @ Vector((
                center_x + rail_half_width * 0.88,
                base_y,
                base_z + rail_height,
            ))),
        ])

    crest_faces = 0
    for section in range(crest_sections - 1):
        current = rail_rings[section]
        following = rail_rings[section + 1]
        for indices in (
            (2, 3, 3, 2),
            (0, 2, 2, 0),
            (1, 3, 3, 1),
        ):
            face = mesh.faces.new([
                current[indices[0]],
                current[indices[1]],
                following[indices[2]],
                following[indices[3]],
            ])
            face.material_index = 0
            face.smooth = True
            crest_faces += 1

    # Crin continua con sección arqueada. La base es estrecha, la parte alta
    # se abre como abanico y el último tercio cae hacia atrás. Así el frente
    # deja de ser un rectángulo y el perfil deja de parecer una escoba rígida.
    plume_cross_segments = 12
    plume_rings = []
    for progress, arch, base_y, base_z, rail_height in profile:
        tail = max((progress - 0.70) / 0.30, 0.0)
        front_taper = min(progress / 0.10, 1.0)
        rear_taper = min((1.0 - progress) / 0.22, 1.0)
        end_taper = max(min(front_taper, rear_taper), 0.04)
        width_taper = 0.34 + 0.66 * pow(end_taper, 0.65)
        bottom_half_width = (
            half_width * (0.24 + 0.035 * arch) * width_taper
        )
        top_half_width = (
            half_width * (0.49 + 0.085 * arch) * width_taper
        )
        plume_height = (
            size.z
            * (0.105 + 0.335 * arch)
            * (0.18 + 0.82 * end_taper)
        )
        tail_y = size.y * (0.30 * tail * tail)
        tail_drop = size.z * (0.24 * tail * tail)
        ring = []
        for cross in range(plume_cross_segments + 1):
            cross_ratio = -1.0 + 2.0 * cross / plume_cross_segments
            fiber_ridge = (
                0.010
                * sin(pi * cross_ratio * plume_cross_segments)
                * end_taper
            )
            rounded_edge = 0.16 * pow(abs(cross_ratio), 1.55)
            bottom_world = Vector((
                center_x + bottom_half_width * cross_ratio,
                base_y + tail_y,
                base_z + rail_height * 0.46 - tail_drop * 0.42,
            ))
            top_world = Vector((
                center_x + top_half_width * cross_ratio,
                base_y
                + size.y * 0.055 * arch * (0.45 + 0.55 * progress)
                + tail_y,
                base_z
                + rail_height
                + plume_height * (1.0 - rounded_edge + fiber_ridge)
                - tail_drop,
            ))
            ring.append((
                mesh.verts.new(inverse @ bottom_world),
                mesh.verts.new(inverse @ top_world),
            ))
        plume_rings.append(ring)

    for section in range(crest_sections - 1):
        current = plume_rings[section]
        following = plume_rings[section + 1]
        for cross in range(plume_cross_segments):
            material_index = crest_materials[0]
            if cross % 5 == 2:
                material_index = crest_materials[2]
            elif cross % 3 == 0:
                material_index = crest_materials[1]
            bottom = mesh.faces.new([
                current[cross][0],
                following[cross][0],
                following[cross + 1][0],
                current[cross + 1][0],
            ])
            bottom.material_index = crest_materials[1]
            bottom.smooth = True
            top = mesh.faces.new([
                current[cross][1],
                current[cross + 1][1],
                following[cross + 1][1],
                following[cross][1],
            ])
            top.material_index = material_index
            top.smooth = True
            crest_faces += 2

        for cross in (0, plume_cross_segments):
            side = mesh.faces.new([
                current[cross][0],
                current[cross][1],
                following[cross][1],
                following[cross][0],
            ])
            if section % 8 in (0, 1):
                side.material_index = crest_materials[2]
            elif section % 4 == 0:
                side.material_index = crest_materials[1]
            else:
                side.material_index = crest_materials[0]
            side.smooth = True
            crest_faces += 1

    for ring, reverse in (
        (plume_rings[0], False),
        (plume_rings[-1], True),
    ):
        for cross in range(plume_cross_segments):
            vertices = [
                ring[cross][0],
                ring[cross + 1][0],
                ring[cross + 1][1],
                ring[cross][1],
            ]
            if reverse:
                vertices.reverse()
            face = mesh.faces.new(vertices)
            face.material_index = (
                crest_materials[2]
                if cross % 4 == 1
                else crest_materials[0]
            )
            face.smooth = True
            crest_faces += 1

    bmesh.ops.recalc_face_normals(mesh, faces=list(mesh.faces))
    mesh.to_mesh(obj.data)
    mesh.free()

    # Casco, interior y penacho son rígidos con respecto a la cabeza.
    # Reasignar todos los vértices evita deformaciones al reproducir la
    # animación y conserva íntegra la carcasa esculpida.
    for group in list(obj.vertex_groups):
        obj.vertex_groups.remove(group)
    head_group = obj.vertex_groups.new(name='mixamorig:Head')
    head_group.add(
        [vertex.index for vertex in obj.data.vertices],
        1.0,
        'REPLACE',
    )

    obj['leonidasHelmetOriginalFaces'] = original_faces
    obj['leonidasHelmetOpenFace'] = True
    obj['leonidasHelmetVisorFaces'] = visor_faces
    obj['leonidasHelmetFrontReliefRemoved'] = len(front_relief_faces)
    obj['leonidasHelmetDetachedReliefRemoved'] = len(
        detached_relief_faces
    )
    obj['leonidasHelmetPanelFaces'] = panel_faces
    obj['leonidasHelmetDomeFaces'] = dome_faces
    obj['leonidasHelmetConstruction'] = 'dense-shell-forged-mask'
    obj['leonidasHelmetFaceOpening'] = 'corinthian-t-slots'
    obj['leonidasHelmetCrestFaces'] = crest_faces
    obj['leonidasHelmetScale'] = helmet_scale
    obj['leonidasHelmetLift'] = helmet_lift
    obj['leonidasHelmetFitHeadWidthRatio'] = (
        dome_radius_x * 2.0 / max(head_size.x, 0.0001)
    )
    obj['leonidasHelmetFitHeadDepthRatio'] = (
        dome_radius_y * 2.0 / max(head_size.y, 0.0001)
    )
    print('LEONIDAS_SCULPTED_HELMET', {
        'original_faces': original_faces,
        'open_face': True,
        'visor_faces': visor_faces,
        'front_relief_removed': len(front_relief_faces),
        'detached_relief_removed': len(detached_relief_faces),
        'panel_faces': panel_faces,
        'crest_faces': crest_faces,
        'scale': helmet_scale,
        'lift': helmet_lift,
        'head_width_ratio': dome_radius_x * 2.0 / head_size.x,
        'head_depth_ratio': dome_radius_y * 2.0 / head_size.y,
    })


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


def precompensate_rigid_pose(obj, armature, bone_name, seconds=0.45):
    """Conserva la orientación diseñada al evaluar la pose nativa inicial."""
    action = armature.animation_data.action if armature.animation_data else None
    start_frame = action.frame_range[0] if action else 1.0
    fps = bpy.context.scene.render.fps / bpy.context.scene.render.fps_base
    target_frame = start_frame + seconds * fps
    previous_frame = bpy.context.scene.frame_current
    bpy.context.scene.frame_set(int(target_frame), subframe=target_frame % 1.0)
    pose_bone = armature.pose.bones[bone_name]
    rest_bone = armature.data.bones[bone_name]
    deformation = pose_bone.matrix @ rest_bone.matrix_local.inverted()
    inverse = deformation.inverted()
    for vertex in obj.data.vertices:
        vertex.co = inverse @ vertex.co
    bpy.context.scene.frame_set(previous_frame)
    obj['leonidasPoseCompensationSeconds'] = seconds


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
print(
    'LEONIDAS_HEAD_BOUNDS',
    tuple(round(value, 4) for value in mesh_vertex_bounds(head_underlay)[0]),
    tuple(round(value, 4) for value in mesh_vertex_bounds(head_underlay)[1]),
)
finish_sculpted_helmet(helmet, head_underlay)
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
shield = build_corporate_shield(
    current_mesh.matrix_world,
    create_palette_material,
)
spear = build_spartan_spear(
    current_mesh.matrix_world,
    create_palette_material,
)
precompensate_rigid_pose(
    shield,
    current_armature,
    'mixamorig:LeftForeArm',
)
precompensate_rigid_pose(
    spear,
    current_armature,
    'mixamorig:RightHand',
)
rigid_bind(shield, 'mixamorig:LeftForeArm')
rigid_bind(spear, 'mixamorig:RightHand')

for obj in (
    body,
    helmet,
    chest,
    head_underlay,
    torso_underlay,
    hair,
    shield,
    spear,
):
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
for obj in (
    current_armature,
    body,
    helmet,
    chest,
    head_underlay,
    torso_underlay,
    hair,
    shield,
    spear,
):
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
        'shield_faces': len(shield.data.polygons),
        'spear_faces': len(spear.data.polygons),
    },
)
