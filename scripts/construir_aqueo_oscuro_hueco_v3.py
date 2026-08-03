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
MASK_FRONT_AT_CENTER_Y = -0.090
MASK_SAFE_OUTER_Y = -0.082


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


def face_y(x: float, z: float, offset: float = 0.0) -> float:
    """Curvatura frontal somera que conserva holgura en toda la máscara."""
    lateral = abs(x - CX) / 0.084
    vertical = abs(z - 1.220) / 0.106
    forehead = max(0.0, min(1.0, (z - 1.240) / 0.086))
    return (
        -0.090
        + 0.018 * lateral * lateral
        + 0.004 * vertical * vertical
        + 0.053 * forehead**1.45
        + offset
    )


def curved_plate(
    name: str,
    outline: list[tuple[float, float]],
    steel: bpy.types.Material,
) -> bpy.types.Object:
    """Placa hueca cuya cara sigue el elipsoide y no un plano pegado."""
    front = [(x, face_y(x, z, -0.0012), z) for x, z in outline]
    back = [(x, face_y(x, z, MASK_THICKNESS), z) for x, z in outline]
    count = len(outline)
    vertices = front + back
    faces: list[tuple[int, ...]] = [
        tuple(range(count)),
        tuple(range(count, count * 2))[::-1],
    ]
    for index in range(count):
        following = (index + 1) % count
        faces.append((index, following, following + count, index + count))
    mesh = bpy.data.meshes.new(f"{name}Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.materials.append(steel)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.scene.collection.objects.link(obj)
    bevel = obj.modifiers.new("ForgedRoundedEdges", "BEVEL")
    bevel.width = 0.00135
    bevel.segments = 4
    bevel.limit_method = "ANGLE"
    apply_modifier(obj, bevel)
    for polygon in obj.data.polygons:
        polygon.use_smooth = True
    return obj


def side_bridge(
    name: str,
    side: float,
    steel: bpy.types.Material,
) -> bpy.types.Object:
    """Cierra la sien entre máscara frontal y cúpula sin tocar la cabeza."""
    center_x = CX + side * 0.085
    half_thickness = 0.0022
    profile = [
        (-0.071, 1.200),
        (-0.058, 1.255),
        (-0.038, 1.309),
        (0.018, 1.312),
        (0.030, 1.225),
        (0.018, 1.184),
    ]
    front = [(center_x - half_thickness, y, z) for y, z in profile]
    back = [(center_x + half_thickness, y, z) for y, z in profile]
    count = len(profile)
    vertices = front + back
    faces: list[tuple[int, ...]] = [
        tuple(range(count)),
        tuple(range(count, count * 2))[::-1],
    ]
    for index in range(count):
        following = (index + 1) % count
        faces.append((index, following, following + count, index + count))
    mesh = bpy.data.meshes.new(f"{name}Mesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.materials.append(steel)
    mesh.update()
    obj = bpy.data.objects.new(name, mesh)
    bpy.context.scene.collection.objects.link(obj)
    bevel = obj.modifiers.new("ForgedRoundedEdges", "BEVEL")
    bevel.width = 0.0012
    bevel.segments = 3
    apply_modifier(obj, bevel)
    for polygon in obj.data.polygons:
        polygon.use_smooth = True
    return obj


def make_face_mask(steel: bpy.types.Material) -> bpy.types.Object:
    """Carcasa facial continua con una T controlada y nasal independiente."""
    x_steps = 88
    z_steps = 106
    x_min = CX - 0.090
    x_max = CX + 0.090
    z_min = 1.115
    z_max = 1.326

    def half_width(z: float) -> float:
        if z >= 1.274:
            t = (z - 1.274) / (z_max - 1.274)
            return 0.084 * (1.0 - t) + 0.055 * t
        if z <= 1.165:
            t = (z - z_min) / (1.165 - z_min)
            return 0.065 * (1.0 - t) + 0.084 * t
        return 0.084

    def surface_y(x: float, z: float) -> float:
        lateral = abs(x - CX) / 0.084
        vertical = abs(z - 1.220) / 0.106
        forehead = max(0.0, min(1.0, (z - 1.240) / 0.086))
        return (
            -0.090
            + 0.018 * lateral * lateral
            + 0.004 * vertical * vertical
            + 0.053 * forehead**1.45
        )

    vertices: list[tuple[float, float, float]] = []
    for zi in range(z_steps + 1):
        z = z_min + (z_max - z_min) * zi / z_steps
        for xi in range(x_steps + 1):
            x = x_min + (x_max - x_min) * xi / x_steps
            vertices.append((x, surface_y(x, z), z))

    def index(xi: int, zi: int) -> int:
        return zi * (x_steps + 1) + xi

    faces: list[tuple[int, int, int, int]] = []
    for zi in range(z_steps):
        z0 = z_min + (z_max - z_min) * zi / z_steps
        z1 = z_min + (z_max - z_min) * (zi + 1) / z_steps
        zc = (z0 + z1) * 0.5
        width = half_width(zc)
        for xi in range(x_steps):
            x0 = x_min + (x_max - x_min) * xi / x_steps
            x1 = x_min + (x_max - x_min) * (xi + 1) / x_steps
            xc = (x0 + x1) * 0.5
            dx = abs(xc - CX)
            top_at_x = z_max - 0.034 * min(1.0, dx / 0.084) ** 1.8
            inside_silhouette = dx <= width and zc <= top_at_x
            eye_opening = False
            for side in (-1.0, 1.0):
                eye_center_x = CX + side * 0.036
                local_x = (xc - eye_center_x) / 0.030
                eye_line = 1.222 - side * 0.045 * (xc - eye_center_x)
                local_z = (zc - eye_line) / 0.0115
                if local_x * local_x + local_z * local_z <= 1.0:
                    eye_opening = True
            vertical_opening = zc <= 1.219 and dx <= 0.029
            if inside_silhouette and not eye_opening and not vertical_opening:
                faces.append(
                    (
                        index(xi, zi),
                        index(xi + 1, zi),
                        index(xi + 1, zi + 1),
                        index(xi, zi + 1),
                    )
                )

    mesh = bpy.data.meshes.new("AqueoDarkV3_ContinuousFaceShellMesh")
    mesh.from_pydata(vertices, [], faces)
    mesh.materials.append(steel)
    mesh.update()
    mask = bpy.data.objects.new("AqueoDarkV3_ContinuousFaceShell", mesh)
    bpy.context.scene.collection.objects.link(mask)
    bm = bmesh.new()
    bm.from_mesh(mask.data)
    loose = [vertex for vertex in bm.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(bm, geom=loose, context="VERTS")
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
    bevel.segments = 4
    bevel.limit_method = "ANGLE"
    apply_modifier(mask, bevel)
    for polygon in mask.data.polygons:
        polygon.use_smooth = True

    nasal = [
        (CX - 0.010, 1.239),
        (CX + 0.010, 1.239),
        (CX + 0.009, 1.183),
        (CX, 1.168),
        (CX - 0.009, 1.183),
    ]
    nose = curved_plate("AqueoDarkV3_Nasal", nasal, steel)
    left_bridge = side_bridge("AqueoDarkV3_LeftTempleBridge", -1.0, steel)
    right_bridge = side_bridge("AqueoDarkV3_RightTempleBridge", 1.0, steel)
    bpy.ops.object.select_all(action="DESELECT")
    for part in (mask, nose, left_bridge, right_bridge):
        part.select_set(True)
    bpy.context.view_layer.objects.active = mask
    bpy.ops.object.join()
    mask = bpy.context.object
    mask.name = "AqueoDarkV3_AnatomicalFaceMask"
    return mask


def make_brow_ridge(steel: bpy.types.Material) -> bpy.types.Object:
    """Refuerzo frontal que une visualmente cúpula y máscara."""
    points = []
    for index in range(25):
        t = index / 24.0
        x = CX - 0.072 + 0.144 * t
        z = 1.279 + 0.010 * (1.0 - ((x - CX) / 0.072) ** 2)
        y = face_y(x, z, -0.0016)
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
    # El donante genera un penacho exagerado. Se compacta alrededor de su base
    # sin alterar la cúpula ni usar el penacho para escalar el casco.
    for vertex in bm.verts:
        vertex.co.x = CX + (vertex.co.x - CX) * 0.62
        vertex.co.z = 1.326 + (vertex.co.z - 1.326) * 0.78
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


def expand_dome_for_render_pose(shell: bpy.types.Object) -> None:
    """Compensa la diferencia entre REST y la pose real del laboratorio."""
    center = Vector((CX, 0.0509225, 1.259187))
    factors = Vector((1.035, 1.055, 1.035))
    for vertex in shell.data.vertices:
        delta = vertex.co - center
        vertex.co = center + Vector(
            (
                delta.x * factors.x,
                delta.y * factors.y,
                delta.z * factors.z,
            )
        )
    shell.data.update()
    shell["leonidasRenderPoseExpansion"] = tuple(factors)


def enlarge_dome_face_opening(shell: bpy.types.Object) -> None:
    """Retira el borde REST que cruza frente y sienes en la pose renderizada."""
    cutter = rounded_cube(
        "AqueoDarkV3_DomeFaceOpeningCutter",
        (CX, -0.060, 1.239),
        (0.172, 0.165, 0.106),
        bevel=0.011,
    )
    boolean_difference(shell, cutter, "RenderPoseFaceOpening")


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
    expand_dome_for_render_pose(shell)
    enlarge_dome_face_opening(shell)
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
        "minimumFaceClearanceMeters": FACE_FRONT_LIMIT_Y - (MASK_SAFE_OUTER_Y + MASK_THICKNESS),
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
