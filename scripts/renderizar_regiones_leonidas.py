"""Renderiza las regiones semánticas que usa el recoloreado de Leonidas."""

import os
from collections import Counter

import bpy
import numpy as np
from mathutils import Vector


PROJECT_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
MODEL_ROOT = os.path.join(PROJECT_ROOT, 'public', 'assets', 'models', 'leonidas')
OUTPUT_ROOT = os.path.join(PROJECT_ROOT, 'output')
REGION = {
    'original': 0,
    'primary': 1,
    'secondary': 2,
    'metal': 3,
    'helmet': 4,
    'chest': 5,
}


def normalized(name):
    return ''.join(character for character in name.lower() if character.isalnum())


def look_at(obj, target):
    obj.rotation_euler = (Vector(target) - obj.location).to_track_quat('-Z', 'Y').to_euler()


def load_pixels(path):
    image = bpy.data.images.load(path, check_existing=False)
    width, height = image.size
    pixels = np.empty(width * height * 4, dtype=np.float32)
    image.pixels.foreach_get(pixels)
    return width, height, pixels.reshape((height, width, 4))


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


def looks_metal(pixel):
    red_byte, green_byte, blue_byte, alpha = pixel
    if alpha < 12:
        return False
    red, green, blue = (red_byte / 255, green_byte / 255, blue_byte / 255)
    maximum = max(red, green, blue)
    minimum = min(red, green, blue)
    saturation = (maximum - minimum) / maximum if maximum else 0
    luminance = red * 0.299 + green * 0.587 + blue * 0.114
    neutral = saturation < 0.28 and luminance > 0.16
    bronze = (
        red > blue * 1.08
        and red >= green * 0.94
        and blue < green * 0.86
        and luminance > 0.2
        and saturation < 0.7
    )
    return neutral or bronze


def classify_face(obj, polygon, pixels, width, height, group_names, uv_layer):
    scores = {}
    center = Vector((0, 0, 0))
    for vertex_index in polygon.vertices:
        vertex = obj.data.vertices[vertex_index]
        center += obj.matrix_world @ vertex.co
        for group in vertex.groups:
            name = group_names.get(group.group, '')
            scores[name] = scores.get(name, 0) + group.weight
    center /= len(polygon.vertices)
    average_uv = Vector((0, 0))
    for loop_index in polygon.loop_indices:
        average_uv += uv_layer[loop_index].uv
    average_uv /= len(polygon.loop_indices)
    pixel = sample_texture(pixels, width, height, average_uv)
    if is_skin(pixel):
        return REGION['original']

    head_weight = 0
    torso_weight = 0
    hips_weight = 0
    metal_limb_weight = 0
    total_weight = sum(scores.values()) or 1
    for bone_name, weight in scores.items():
        if 'head' in bone_name or 'neck' in bone_name:
            head_weight += weight
        if 'spine' in bone_name or 'shoulder' in bone_name or 'upperarm' in bone_name:
            torso_weight += weight
        if 'hips' in bone_name or 'upleg' in bone_name:
            hips_weight += weight
        if (
            'forearm' in bone_name
            or ('leg' in bone_name and 'upleg' not in bone_name)
            or 'foot' in bone_name
            or 'toe' in bone_name
        ):
            metal_limb_weight += weight

    head_ratio = head_weight / total_weight
    torso_ratio = torso_weight / total_weight
    hips_ratio = hips_weight / total_weight
    metal_limb_ratio = metal_limb_weight / total_weight
    vertical = center.z
    depth = -center.y
    if vertical > 1.08 and head_ratio > 0.25:
        return REGION['helmet']
    if 0.72 < vertical < 1.17 and abs(center.x) < 0.5 and torso_ratio > 0.18:
        return REGION['chest']
    if metal_limb_ratio > 0.36 and (vertical < 0.68 or vertical > 0.72):
        return REGION['metal']
    if hips_ratio > 0.3 and vertical < 0.82:
        return REGION['primary']
    if torso_ratio > 0.22:
        return REGION['secondary']
    if looks_metal(pixel):
        return REGION['metal']
    return REGION['secondary'] if depth > -0.16 else REGION['original']


def island_regions(obj, pixels, width, height):
    mesh = obj.data
    uv_layer = mesh.uv_layers.active.data
    group_names = {group.index: normalized(group.name) for group in obj.vertex_groups}
    face_regions = [
        classify_face(obj, polygon, pixels, width, height, group_names, uv_layer)
        for polygon in mesh.polygons
    ]
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

    uv_owners = {}
    for polygon in mesh.polygons:
        for loop_index in polygon.loop_indices:
            uv = uv_layer[loop_index].uv
            key = (round(uv.x, 5), round(uv.y, 5))
            owner = uv_owners.get(key)
            if owner is None:
                uv_owners[key] = polygon.index
            else:
                join(polygon.index, owner)

    islands = {}
    for face_index, region in enumerate(face_regions):
        root = find(face_index)
        island = islands.setdefault(root, {'faces': [], 'votes': [0] * 6})
        island['faces'].append(face_index)
        island['votes'][region] += 1

    resolved = [REGION['original']] * len(mesh.polygons)
    for island in islands.values():
        original_ratio = island['votes'][REGION['original']] / len(island['faces'])
        winning_region = max(range(1, 6), key=lambda region: island['votes'][region])
        winning_votes = island['votes'][winning_region]
        winning_ratio = winning_votes / len(island['faces'])
        strong_equipment = winning_region in (
            REGION['metal'],
            REGION['helmet'],
            REGION['chest'],
        ) and winning_ratio >= 0.3
        region = REGION['original']
        if original_ratio < 0.25 or strong_equipment:
            region = winning_region
        for face_index in island['faces']:
            resolved[face_index] = region
    return resolved


def material(name, color, metallic=0):
    value = bpy.data.materials.new(name)
    value.diffuse_color = (*color, 1)
    value.metallic = metallic
    value.roughness = 0.55
    value.use_nodes = True
    shader = value.node_tree.nodes.get('Principled BSDF')
    shader.inputs['Base Color'].default_value = (*color, 1)
    shader.inputs['Metallic'].default_value = metallic
    shader.inputs['Roughness'].default_value = 0.55
    return value


bpy.ops.object.select_all(action='SELECT')
bpy.ops.object.delete(use_global=False)
bpy.ops.wm.fbx_import(
    filepath=os.path.join(MODEL_ROOT, 'leonidas-spartan-rigged.fbx')
)
character = max(
    (obj for obj in bpy.context.scene.objects if obj.type == 'MESH'),
    key=lambda obj: len(obj.data.polygons),
)
width, height, pixels = load_pixels(
    os.path.join(MODEL_ROOT, 'leonidas-spartan-color.webp')
)
regions = island_regions(character, pixels, width, height)
print('REGION_COUNTS', dict(Counter(regions)))
materials = [
    material('Original', (0.34, 0.29, 0.27)),
    material('Principal', (0.0, 0.2, 1.0)),
    material('Secundario', (0.2, 0.9, 0.25)),
    material('Metal', (0.78, 0.86, 0.96), 0.8),
    material('Casco', (1.0, 0.05, 0.05), 0.65),
    material('Pechera', (1.0, 0.68, 0.0)),
]
character.data.materials.clear()
for value in materials:
    character.data.materials.append(value)
for polygon, region in zip(character.data.polygons, regions):
    polygon.material_index = region

world = bpy.context.scene.world
world.color = (0.035, 0.055, 0.09)
for location, energy, size in (
    ((2.5, -3.0, 4.0), 1300, 4.0),
    ((-2.0, -1.5, 2.4), 800, 3.0),
    ((0.0, 2.0, 2.8), 1000, 2.0),
):
    light_data = bpy.data.lights.new('Area', 'AREA')
    light_data.energy = energy
    light_data.shape = 'DISK'
    light_data.size = size
    light = bpy.data.objects.new('Area', light_data)
    light.location = location
    bpy.context.scene.collection.objects.link(light)
    look_at(light, (0, 0, 0.72))

camera_data = bpy.data.cameras.new('Camera')
camera = bpy.data.objects.new('Camera', camera_data)
camera.location = (0, -4.2, 0.72)
camera_data.lens = 68
camera_data.sensor_width = 36
look_at(camera, (0, 0, 0.69))
bpy.context.scene.collection.objects.link(camera)
bpy.context.scene.camera = camera

scene = bpy.context.scene
scene.render.engine = 'BLENDER_EEVEE'
scene.render.resolution_x = 720
scene.render.resolution_y = 820
scene.render.resolution_percentage = 100
scene.render.image_settings.file_format = 'PNG'
scene.render.film_transparent = False
os.makedirs(OUTPUT_ROOT, exist_ok=True)
scene.render.filepath = os.path.join(OUTPUT_ROOT, 'leonidas-regiones-actuales.png')
bpy.ops.render.render(write_still=True)
print(scene.render.filepath)
