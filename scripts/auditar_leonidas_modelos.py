"""Audita los modelos fuente de Leonidas dentro de Blender.

Uso:
    blender --background --factory-startup --python scripts/auditar_leonidas_modelos.py

El script no modifica los archivos fuente ni guarda un .blend.
"""

import json
import os
import sys

import bpy
from mathutils import Vector


PROJECT_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
MODEL_ROOT = os.path.join(PROJECT_ROOT, 'public', 'assets', 'models', 'leonidas')


def reset_scene():
    bpy.ops.object.select_all(action='SELECT')
    bpy.ops.object.delete(use_global=False)
    for collection in (
        bpy.data.meshes,
        bpy.data.armatures,
        bpy.data.materials,
        bpy.data.images,
        bpy.data.actions,
    ):
        for block in list(collection):
            if block.users == 0:
                collection.remove(block)


def import_model(filename):
    path = os.path.join(MODEL_ROOT, filename)
    if filename.lower().endswith('.fbx'):
        bpy.ops.wm.fbx_import(filepath=path)
    else:
        bpy.ops.import_scene.gltf(filepath=path)
    return path


def connected_component_sizes(mesh, weld_positions=False):
    parent = list(range(len(mesh.vertices)))
    sizes = [1] * len(mesh.vertices)

    def find(vertex):
        while parent[vertex] != vertex:
            parent[vertex] = parent[parent[vertex]]
            vertex = parent[vertex]
        return vertex

    def join(first, second):
        first_root = find(first)
        second_root = find(second)
        if first_root == second_root:
            return
        if sizes[first_root] < sizes[second_root]:
            first_root, second_root = second_root, first_root
        parent[second_root] = first_root
        sizes[first_root] += sizes[second_root]

    for edge in mesh.edges:
        join(edge.vertices[0], edge.vertices[1])
    if weld_positions:
        owners = {}
        for vertex in mesh.vertices:
            key = tuple(round(axis, 6) for axis in vertex.co)
            previous = owners.get(key)
            if previous is None:
                owners[key] = vertex.index
            else:
                join(previous, vertex.index)

    components = {}
    for vertex in range(len(mesh.vertices)):
        root = find(vertex)
        components[root] = components.get(root, 0) + 1
    return sorted(components.values(), reverse=True)


def object_bounds(obj):
    corners = [obj.matrix_world @ Vector(vector) for vector in obj.bound_box]
    return {
        'min': [round(min(corner[axis] for corner in corners), 6) for axis in range(3)],
        'max': [round(max(corner[axis] for corner in corners), 6) for axis in range(3)],
    }


def audit(filename):
    reset_scene()
    path = import_model(filename)
    objects = []
    for obj in bpy.context.scene.objects:
        item = {
            'name': obj.name,
            'type': obj.type,
            'parent': obj.parent.name if obj.parent else None,
        }
        if obj.type == 'MESH':
            mesh = obj.data
            components = connected_component_sizes(mesh)
            welded_components = connected_component_sizes(mesh, weld_positions=True)
            item.update({
                'vertices': len(mesh.vertices),
                'edges': len(mesh.edges),
                'polygons': len(mesh.polygons),
                'connected_components': len(components),
                'largest_components': components[:12],
                'position_welded_components': len(welded_components),
                'largest_position_welded_components': welded_components[:24],
                'uv_layers': [layer.name for layer in mesh.uv_layers],
                'materials': [
                    slot.material.name if slot.material else None
                    for slot in obj.material_slots
                ],
                'vertex_groups': len(obj.vertex_groups),
                'armature_modifiers': [
                    modifier.object.name
                    for modifier in obj.modifiers
                    if modifier.type == 'ARMATURE' and modifier.object
                ],
                'bounds': object_bounds(obj),
            })
        elif obj.type == 'ARMATURE':
            item.update({
                'bones': len(obj.data.bones),
                'bone_names': [bone.name for bone in obj.data.bones],
                'bounds': object_bounds(obj),
            })
        objects.append(item)

    return {
        'file': path,
        'objects': objects,
        'actions': [
            {
                'name': action.name,
                'frame_start': round(action.frame_range[0], 3),
                'frame_end': round(action.frame_range[1], 3),
            }
            for action in bpy.data.actions
        ],
    }


reports = [
    audit('leonidas-spartan-rigged.fbx'),
    audit('leonidas-spartan-free.glb'),
]
print('LEONIDAS_AUDIT_BEGIN')
print(json.dumps(reports, ensure_ascii=False, indent=2))
print('LEONIDAS_AUDIT_END')
sys.stdout.flush()
