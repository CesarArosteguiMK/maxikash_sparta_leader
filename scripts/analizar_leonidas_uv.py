"""Resume colores y pesos de las caras del FBX activo de Leonidas."""

import collections
import json
import os

import bpy
import numpy as np


PROJECT_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
MODEL_ROOT = os.path.join(PROJECT_ROOT, 'public', 'assets', 'models', 'leonidas')


def normalized(name):
    return ''.join(character for character in name.lower() if character.isalnum())


def image_pixels(path):
    image = bpy.data.images.load(path, check_existing=False)
    width, height = image.size
    pixels = np.empty(width * height * 4, dtype=np.float32)
    image.pixels.foreach_get(pixels)
    return width, height, pixels.reshape((height, width, 4))


def sample(pixels, width, height, uv):
    x = max(0, min(width - 1, int((uv.x % 1.0) * width)))
    y = max(0, min(height - 1, int((uv.y % 1.0) * height)))
    return tuple(int(round(channel * 255)) for channel in pixels[y, x, :4])


def skin(pixel):
    red, green, blue, alpha = pixel
    if alpha < 12:
        return False
    cb = 128 - 0.168736 * red - 0.331264 * green + 0.5 * blue
    cr = 128 + 0.5 * red - 0.418688 * green - 0.081312 * blue
    return (
        red > 58
        and green > 24
        and blue > 12
        and red > green * 1.015
        and green > blue * 0.66
        and 66 < cb < 145
        and 128 < cr < 184
    )


def quantized(pixel):
    return tuple(min(255, (channel // 24) * 24) for channel in pixel[:3])


bpy.ops.object.select_all(action='SELECT')
bpy.ops.object.delete(use_global=False)
bpy.ops.wm.fbx_import(
    filepath=os.path.join(MODEL_ROOT, 'leonidas-spartan-rigged.fbx')
)
mesh_object = max(
    (obj for obj in bpy.context.scene.objects if obj.type == 'MESH'),
    key=lambda obj: len(obj.data.polygons),
)
mesh = mesh_object.data
uv_layer = mesh.uv_layers.active.data
groups = {group.index: normalized(group.name) for group in mesh_object.vertex_groups}
width, height, pixels = image_pixels(
    os.path.join(MODEL_ROOT, 'leonidas-spartan-color.webp')
)

zones = {
    'head': collections.Counter(),
    'torso': collections.Counter(),
    'hips': collections.Counter(),
    'legs': collections.Counter(),
    'arms': collections.Counter(),
}
skin_counts = collections.Counter()
samples = collections.defaultdict(list)

for polygon in mesh.polygons:
    world_vertices = [
        mesh_object.matrix_world @ mesh.vertices[index].co
        for index in polygon.vertices
    ]
    center_z = sum(vertex.z for vertex in world_vertices) / len(world_vertices)
    center_x = sum(vertex.x for vertex in world_vertices) / len(world_vertices)
    average_uv = sum(
        (uv_layer[loop].uv for loop in polygon.loop_indices),
        uv_layer[polygon.loop_indices[0]].uv * 0,
    ) / len(polygon.loop_indices)
    pixel = sample(pixels, width, height, average_uv)
    if center_z > 1.08:
        zone = 'head'
    elif center_z > 0.76 and abs(center_x) < 0.36:
        zone = 'torso'
    elif center_z > 0.58:
        zone = 'hips'
    elif abs(center_x) < 0.2:
        zone = 'legs'
    else:
        zone = 'arms'
    zones[zone][quantized(pixel)] += 1
    if skin(pixel):
        skin_counts[zone] += 1
    if len(samples[zone]) < 24:
        samples[zone].append({
            'z': round(center_z, 4),
            'x': round(center_x, 4),
            'rgba': pixel,
        })

report = {
    zone: {
        'faces': sum(counter.values()),
        'skin_faces': skin_counts[zone],
        'top_colors': [
            {'rgb': color, 'faces': count}
            for color, count in counter.most_common(18)
        ],
    }
    for zone, counter in zones.items()
}
print('LEONIDAS_UV_BEGIN')
print(json.dumps(report, ensure_ascii=False, indent=2))
print('LEONIDAS_UV_END')
