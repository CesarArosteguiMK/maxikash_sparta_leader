"""Repara el Áqueo oscuro conservando detalle escultórico donde sí funciona.

La cúpula procede exclusivamente del ajuste aprobado. Del candidato escultórico
se extraen la máscara frontal y la cresta, se descarta su bóveda defectuosa y se
añade una placa frontal convexa. El resultado sigue siendo QA y no productivo.
"""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path

import bmesh
import bpy
from mathutils import Vector

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as stage02


ROOT = Path(__file__).resolve().parents[1]
DONOR = ROOT / "storage" / "leonidas-helmet-designs" / "sculpted" / "helmet-aqueo-oscuro-sculpted-v7.glb"
DOME = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "leonidas-helmet-dome-stage-01.glb"
OUTPUT_ROOT = ROOT / "storage" / "leonidas-helmet-designs" / "aqueo-dark-realistic-rebuild"
OUTPUT_GLB = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "leonidas-aqueo-dark-realistic-rebuild.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-realistic-rebuild.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-realistic-rebuild-report.json"


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def bounds(obj: bpy.types.Object) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
    return (
        Vector(tuple(min(point[axis] for point in corners) for axis in range(3))),
        Vector(tuple(max(point[axis] for point in corners) for axis in range(3))),
    )


def fit_donor(obj: bpy.types.Object) -> float:
    source_min, source_max = bounds(obj)
    source_size = source_max - source_min
    source_center = (source_min + source_max) * 0.5
    target_min = Vector((-0.108488, -0.033433, 1.178995))
    target_max = Vector((0.076727, 0.135278, 1.339379))
    target_center = (target_min + target_max) * 0.5
    uniform_scale = (target_max.x - target_min.x) / source_size.x
    source_eye_z = source_max.z - source_size.z * 0.507
    obj.scale = (uniform_scale,) * 3
    obj.location.x = target_center.x - source_center.x * uniform_scale
    obj.location.y = target_center.y - source_center.y * uniform_scale
    obj.location.z = 1.245 - source_eye_z * uniform_scale
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    obj.select_set(False)
    return uniform_scale


def reduce_and_extract(obj: bpy.types.Object) -> dict[str, int]:
    before = len(obj.data.vertices)
    # La escultura contiene casi tres millones de vértices. Esta reducción
    # conserva la silueta y el relieve pero hace viable una pieza QA web.
    modifier = obj.modifiers.new("QA_DetailReduction", "DECIMATE")
    modifier.decimate_type = "COLLAPSE"
    modifier.ratio = 0.105
    modifier.use_collapse_triangulate = True
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=modifier.name)
    obj.select_set(False)
    after_decimate = len(obj.data.vertices)

    mesh = bmesh.new()
    mesh.from_mesh(obj.data)
    remove = []
    for vertex in mesh.verts:
        x, y, z = vertex.co
        is_crest = abs(x - stage02.CX) < 0.052 and z > 1.306
        is_faceguard = y < -0.006 and z < 1.328
        if not (is_crest or is_faceguard):
            remove.append(vertex)
    bmesh.ops.delete(mesh, geom=remove, context="VERTS")
    loose = [vertex for vertex in mesh.verts if not vertex.link_faces]
    if loose:
        bmesh.ops.delete(mesh, geom=loose, context="VERTS")
    bmesh.ops.recalc_face_normals(mesh, faces=list(mesh.faces))
    mesh.to_mesh(obj.data)
    mesh.free()
    obj.data.update()
    return {
        "sourceVertices": before,
        "decimatedVertices": after_decimate,
        "extractedVertices": len(obj.data.vertices),
    }


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    materials = stage02.palette()

    bpy.ops.import_scene.gltf(filepath=os.fspath(DONOR))
    donor = max((obj for obj in bpy.context.scene.objects if obj.type == "MESH"), key=lambda item: len(item.data.vertices))
    donor.name = "AqueoDark_SculptedMaskAndCrest"
    scale = fit_donor(donor)
    donor_stats = reduce_and_extract(donor)
    donor.data.materials.clear()
    donor.data.materials.append(materials["steel"])

    bpy.ops.import_scene.gltf(filepath=os.fspath(DOME))
    proxy = next((obj for obj in bpy.context.scene.objects if obj.name.startswith("LeonidasHeadFitProxy")), None)
    shell = next((obj for obj in bpy.context.scene.objects if obj.name.startswith("LeonidasHelmetDomeStage01")), None)
    if proxy:
        bpy.data.objects.remove(proxy, do_unlink=True)
    if shell is None:
        raise RuntimeError("No se encontró la cúpula calibrada")
    shell.name = "AqueoDark_ApprovedShell"
    shell.data.materials.clear()
    shell.data.materials.append(materials["steel_soft"])

    forehead = stage02.forehead_patch("AqueoDark_ConvexForeheadRepair", materials["steel"])
    # Riel doble del contrato visual, separado del pelo reconstruido.
    rails = []
    for lateral, radius in ((-0.018, 0.0032), (0.018, 0.0032)):
        points = []
        for index in range(44):
            y = -0.020 + 0.205 * index / 43.0
            points.append((stage02.CX + lateral, y, stage02.crest_base_z(y) + 0.003))
        rails.append(stage02.curve_object(f"AqueoDark_RealisticRail_{lateral:+.3f}", points, radius, materials["steel_soft"], resolution=3))
        rails.append(stage02.curve_object(f"AqueoDark_RealisticRailTrim_{lateral:+.3f}", [(x, y - 0.001, z + 0.003) for x, y, z in points], 0.0007, materials["edge"], resolution=3))
    stage02.convert_curves(rails)

    helmet_objects = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
    for obj in helmet_objects:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-realistic-rebuild"
        obj["leonidasProductionReady"] = False
        obj["leonidasLegacyShellReused"] = False
        obj["leonidasApprovedDome"] = obj == shell

    bpy.ops.object.select_all(action="DESELECT")
    for obj in helmet_objects:
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
        "contract": "realistic-aqueo-reference-4-view",
        "outputAsset": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "approvedDome": str(DOME.relative_to(ROOT)).replace("\\", "/"),
        "sculptDonor": str(DONOR.relative_to(ROOT)).replace("\\", "/"),
        "donorUniformScale": round(scale, 8),
        "donor": donor_stats,
        "architecture": {
            "shell": "approved-stage-01-dome",
            "forehead": "new-convex-repair",
            "maskAndCrest": "spatially-extracted-high-detail-sculpt",
            "rails": "new-double-sagittal-rails",
        },
        "productionIntegrated": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
