"""Construye un prototipo modular de Leonidas a partir de los modelos locales.

El FBX conserva el personaje, rig y animación actuales. El GLB alternativo solo
aporta las superficies anatómicas que el FBX no contiene debajo del casco y la
pechera. El resultado debe revisarse visualmente antes de habilitarse.
"""

import os

import bmesh
import bpy
import numpy as np
from mathutils import Vector


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


def equipment_faces(obj):
    mesh = obj.data
    uv_layer = mesh.uv_layers.active.data
    group_names = {group.index: normalized(group.name) for group in obj.vertex_groups}
    width, height, pixels = load_pixels(
        os.path.join(MODEL_ROOT, 'leonidas-spartan-color.webp')
    )
    helmet = set()
    chest = set()
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
            helmet.add(polygon.index)
        elif 0.72 < center.z < 1.17 and abs(center.x) < 0.5 and torso_ratio > 0.18:
            chest.add(polygon.index)
    return helmet, chest


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
            + (world[donor_depth_axis] - donor_center[donor_depth_axis])
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
    }
    for group in obj.vertex_groups:
        target = direct.get(normalized(group.name))
        if target:
            group.name = target


def attach_to_current_rig(obj, current, armature):
    obj.parent = None
    obj.matrix_world = current.matrix_world.copy()
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

helmet_faces, chest_faces = equipment_faces(current_mesh)
all_faces = set(range(len(current_mesh.data.polygons)))
body = keep_faces(
    current_mesh,
    all_faces - helmet_faces - chest_faces,
    'LeonidasBody',
    'body',
)
helmet = keep_faces(current_mesh, helmet_faces, 'LeonidasHelmet', 'helmet')
chest = keep_faces(current_mesh, chest_faces, 'LeonidasChest', 'chest')
helmet['leonidasPalette'] = 'metal'
chest['leonidasPalette'] = 'metal'

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
print(
    'LEONIDAS_ANATOMY_BOUNDS',
    tuple(round(value, 4) for value in mesh_vertex_bounds(anatomy_source)[0]),
    tuple(round(value, 4) for value in mesh_vertex_bounds(anatomy_source)[1]),
)
print(
    'LEONIDAS_ANATOMY_MATRIX',
    [[round(value, 4) for value in row] for row in anatomy_source.matrix_world],
)

head_underlay = crop_anatomy(
    anatomy_source,
    'LeonidasHeadUnderlay',
    'headUnderlay',
    lambda center: center.z > 1.055 and abs(center.x) < 0.22,
)
torso_underlay = crop_anatomy(
    anatomy_source,
    'LeonidasTorsoUnderlay',
    'torsoUnderlay',
    lambda center: 0.68 < center.z < 1.14 and abs(center.x) < 0.255,
)

for obj in (body, helmet, chest, head_underlay, torso_underlay):
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
for obj in (current_armature, body, helmet, chest, head_underlay, torso_underlay):
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
    },
)
