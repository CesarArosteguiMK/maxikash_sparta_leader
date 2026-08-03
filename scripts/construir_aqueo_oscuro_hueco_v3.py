"""Construye un Áqueo oscuro vestible sobre la cúpula anatómica validada.

El candidato automático se usa sólo como donante del penacho. La cúpula y la
máscara facial se construyen como piezas huecas independientes con holgura
respecto a ``LeonidasHeadUnderlay``. La salida permanece aislada en QA.
"""

from __future__ import annotations

import json
import math
import os
import sys
from pathlib import Path

import bmesh
import bpy
from mathutils import Vector

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as preview


ROOT = Path(__file__).resolve().parents[1]
DOME = ROOT / "public/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb"
DONOR = ROOT / "storage/leonidas-helmet-reconstruction/hunyuan3d-2mv/helmet-aqueo-oscuro-hunyuan-mv-v2-high.glb"
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-hollow-v3"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-hollow-v3.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-hollow-v3.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-hollow-v3-report.json"

CX = -0.01588049
MASK_CENTER = Vector((CX, 0.050, 1.208))
MASK_RADII = Vector((0.086, 0.142, 0.130))
MASK_THICKNESS = 0.0035
FACE_FRONT_LIMIT_Y = -0.077526
MASK_FRONT_AT_CENTER_Y = MASK_CENTER.y - MASK_RADII.y


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def bounds(obj: bpy.types.Object) -> tuple[Vector, Vector]:
    values = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
    return (
        Vector(tuple(min(point[axis] for point in values) for axis in range(3))),
        Vector(tuple(max(point[axis] for point in values) for axis in range(3))),
    )


def apply_modifier(obj: bpy.types.Object, modifier: bpy.types.Modifier) -> None:
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=modifier.name)
    obj.select_set(False)


def rounded_cube(
    name: str,
    location: tuple[float, float, float],
    dimensions: tuple[float, float, float],
    *,
    bevel: float,
    rotation_y: float = 0.0,
) -> bpy.types.Object:
    bpy.ops.mesh.primitive_cube_add(location=location, rotation=(0.0, rotation_y, 0.0))
    obj = bpy.context.object
    obj.name = name
    obj.dimensions = dimensions
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    modifier = obj.modifiers.new("RoundedOpening", "BEVEL")
    modifier.width = bevel
    modifier.segments = 6
    apply_modifier(obj, modifier)
    return obj


def boolean_difference(target: bpy.types.Object, cutter: bpy.types.Object, name: str) -> None:
    modifier = target.modifiers.new(name, "BOOLEAN")
    modifier.operation = "DIFFERENCE"
    modifier.solver = "EXACT"
    modifier.object = cutter
    apply_modifier(target, modifier)
    bpy.data.objects.remove(cutter, do_unlink=True)


def make_face_mask(steel: bpy.types.Material) -> bpy.types.Object:
    """Máscara convexa con T limpia y 14.5 mm delante de nariz/barba."""
    bpy.ops.mesh.primitive_uv_sphere_add(
        segments=128,
        ring_count=80,
        location=MASK_CENTER,
        scale=MASK_RADII,
    )
    mask = bpy.context.object
    mask.name = "AqueoDarkV3_AnatomicalFaceMask"
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)

    # Dos ventanas oculares independientes preservan el nasal central.
    eye_z = 1.245
    left_eye = rounded_cube(
        "AqueoDarkV3_LeftEyeCutter",
        (CX - 0.036, MASK_FRONT_AT_CENTER_Y, eye_z),
        (0.052, 0.180, 0.023),
        bevel=0.009,
        rotation_y=math.radians(-6.0),
    )
    right_eye = rounded_cube(
        "AqueoDarkV3_RightEyeCutter",
        (CX + 0.036, MASK_FRONT_AT_CENTER_Y, eye_z),
        (0.052, 0.180, 0.023),
        bevel=0.009,
        rotation_y=math.radians(6.0),
    )
    boolean_difference(mask, left_eye, "OpenLeftEye")
    boolean_difference(mask, right_eye, "OpenRightEye")

    # Boca/barba abierta; el puente nasal permanece como una pieza continua.
    mouth = rounded_cube(
        "AqueoDarkV3_MouthCutter",
        (CX, MASK_FRONT_AT_CENTER_Y, 1.158),
        (0.060, 0.180, 0.084),
        bevel=0.015,
    )
    boolean_difference(mask, mouth, "OpenMouthAndBeard")

    # La semiesfera posterior e inferior no forman parte de la máscara.
    bm = bmesh.new()
    bm.from_mesh(mask.data)
    remove = []
    for face in bm.faces:
        center = mask.matrix_world @ face.calc_center_median()
        if center.y > 0.018 or center.z < 1.105:
            remove.append(face)
    bmesh.ops.delete(bm, geom=remove, context="FACES")
    bm.to_mesh(mask.data)
    bm.free()
    mask.data.update()

    solidify = mask.modifiers.new("RealShellThickness", "SOLIDIFY")
    solidify.thickness = MASK_THICKNESS
    solidify.offset = 0.0
    solidify.use_rim = True
    apply_modifier(mask, solidify)
    bevel = mask.modifiers.new("ForgedRoundedEdges", "BEVEL")
    bevel.width = 0.00135
    bevel.segments = 3
    bevel.limit_method = "ANGLE"
    apply_modifier(mask, bevel)
    mask.data.materials.append(steel)
    for polygon in mask.data.polygons:
        polygon.use_smooth = True
    return mask


def make_brow_ridge(steel: bpy.types.Material) -> bpy.types.Object:
    """Refuerzo frontal que une visualmente cúpula y máscara."""
    points = []
    for index in range(25):
        t = index / 24.0
        x = CX - 0.072 + 0.144 * t
        z = 1.279 + 0.010 * (1.0 - ((x - CX) / 0.072) ** 2)
        normalized_x = (x - MASK_CENTER.x) / MASK_RADII.x
        normalized_z = (z - MASK_CENTER.z) / MASK_RADII.z
        radial = max(0.0, 1.0 - normalized_x**2 - normalized_z**2)
        y = MASK_CENTER.y - MASK_RADII.y * math.sqrt(radial) - 0.0016
        points.append((x, y, z))
    return preview.curve_object(
        "AqueoDarkV3_BrowReinforcement",
        points,
        0.0024,
        steel,
        resolution=3,
    )


def fit_donor(obj: bpy.types.Object) -> None:
    minimum, maximum = bounds(obj)
    size = maximum - minimum
    center = (minimum + maximum) * 0.5
    eye_z = maximum.z - size.z * 0.507
    scale = Vector((0.205, 0.160, 0.170))
    obj.scale = scale
    obj.location.x = CX - center.x * scale.x
    obj.location.y = -0.105 - minimum.y * scale.y
    obj.location.z = 1.245 - eye_z * scale.z
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    obj.select_set(False)


def extract_plume(hair: bpy.types.Material) -> bpy.types.Object:
    before = set(bpy.context.scene.objects)
    bpy.ops.import_scene.gltf(filepath=os.fspath(DONOR))
    donor = max(
        (obj for obj in bpy.context.scene.objects if obj not in before and obj.type == "MESH"),
        key=lambda item: len(item.data.vertices),
    )
    fit_donor(donor)
    bm = bmesh.new()
    bm.from_mesh(donor.data)
    remove = [face for face in bm.faces if face.calc_center_median().z < 1.326]
    bmesh.ops.delete(bm, geom=remove, context="FACES")
    bm.to_mesh(donor.data)
    bm.free()
    donor.data.update()
    donor.name = "AqueoDarkV3_ReferencePlume"
    donor.data.materials.clear()
    donor.data.materials.append(hair)
    for polygon in donor.data.polygons:
        polygon.use_smooth = True
    return donor


def make_fit_reference(shell: bpy.types.Object) -> bpy.types.Object:
    fit = shell.copy()
    fit.data = shell.data.copy()
    fit.name = "Aqueo_FitReference"
    bpy.context.scene.collection.objects.link(fit)
    fit["leonidasQaRole"] = "aqueo-fit-reference"
    return fit


def mesh_audit(obj: bpy.types.Object) -> dict[str, int]:
    bm = bmesh.new()
    bm.from_mesh(obj.data)
    result = {
        "vertices": len(bm.verts),
        "faces": len(bm.faces),
        "boundaryEdges": sum(1 for edge in bm.edges if edge.is_boundary),
        "nonManifoldEdges": sum(1 for edge in bm.edges if not edge.is_manifold),
    }
    bm.free()
    return result


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    materials = preview.palette()
    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    shell = next(obj for obj in bpy.context.scene.objects if obj.name.startswith("LeonidasHelmetDomeStage01"))
    proxy = next((obj for obj in bpy.context.scene.objects if obj.name.startswith("LeonidasHeadFitProxy")), None)
    if proxy:
        bpy.data.objects.remove(proxy, do_unlink=True)
    shell.name = "AqueoDarkV3_ValidatedHollowDome"
    shell.data.materials.clear()
    shell.data.materials.append(materials["steel_soft"])
    fit_reference = make_fit_reference(shell)
    mask = make_face_mask(materials["steel"])
    brow = make_brow_ridge(materials["edge"])
    plume = extract_plume(materials["hair"])
    preview.convert_curves([brow])
    objects = [shell, fit_reference, mask, brow, plume]
    for obj in objects:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-hollow-v3"
        obj["leonidasProductionReady"] = False
        obj["leonidasHeadBone"] = "mixamorig:Head"

    bpy.ops.object.select_all(action="DESELECT")
    for obj in objects:
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
    report = {
        "status": "qa-candidate-not-production",
        "sourceOfFit": str(DOME.relative_to(ROOT)).replace("\\", "/"),
        "donorUse": "plume-only",
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "headExactWidthMeters": 0.153510,
        "domeOuterWidthMeters": 0.185215,
        "maskFrontAtCenterMeters": MASK_FRONT_AT_CENTER_Y,
        "faceFrontLimitMeters": FACE_FRONT_LIMIT_Y,
        "centralClearanceMeters": FACE_FRONT_LIMIT_Y - MASK_FRONT_AT_CENTER_Y,
        "maskThicknessMeters": MASK_THICKNESS,
        "audits": {obj.name: mesh_audit(obj) for obj in (shell, mask)},
        "productionIntegrated": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    # El visor de ajuste sólo existe para el laboratorio; no debe aparecer en renders.
    fit_reference.hide_render = True
    preview.OUTPUT_ROOT = OUTPUT_ROOT / "preview"
    preview.REPORT = preview.OUTPUT_ROOT / "preview-report.json"
    preview.OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    preview.render_views(OUTPUT_GLB)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
