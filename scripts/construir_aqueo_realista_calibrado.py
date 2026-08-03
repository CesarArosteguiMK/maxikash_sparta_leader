"""Crea un candidato Áqueo realista y calibrado desde las cuatro referencias.

Usa la reconstrucción multivista de alta resolución como escultura base, reduce
su altura exagerada, la ajusta al contrato anatómico de Leónidas y abre la zona
inferior de la T facial. No se integra en producción.
"""

from __future__ import annotations

import json
import os
from pathlib import Path

import bpy
from mathutils import Vector


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "storage" / "leonidas-helmet-reconstruction" / "hunyuan3d-2mv" / "helmet-aqueo-oscuro-hunyuan-mv-v2-high.glb"
OUTPUT_ROOT = ROOT / "storage" / "leonidas-helmet-designs" / "aqueo-realistic-calibrated"
OUTPUT_GLB = ROOT / "public" / "assets" / "models" / "leonidas" / "qa" / "leonidas-aqueo-realistic-calibrated.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-realistic-calibrated.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-realistic-calibrated-report.json"
CX = -0.01588049


def clear_scene() -> None:
    bpy.ops.object.select_all(action="SELECT")
    bpy.ops.object.delete(use_global=False)


def object_bounds(obj: bpy.types.Object) -> tuple[Vector, Vector]:
    corners = [obj.matrix_world @ Vector(corner) for corner in obj.bound_box]
    return (
        Vector(tuple(min(point[axis] for point in corners) for axis in range(3))),
        Vector(tuple(max(point[axis] for point in corners) for axis in range(3))),
    )


def material() -> bpy.types.Material:
    mat = bpy.data.materials.new("AqueoRealistic_BlackenedForgedSteel")
    color = (0.012, 0.018, 0.028, 1.0)
    mat.diffuse_color = color
    mat.metallic = 0.84
    mat.roughness = 0.36
    mat.use_nodes = True
    shader = mat.node_tree.nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = color
    shader.inputs["Metallic"].default_value = 0.84
    shader.inputs["Roughness"].default_value = 0.36
    return mat


def fit_to_leonidas(obj: bpy.types.Object) -> dict[str, float]:
    source_min, source_max = object_bounds(obj)
    source_size = source_max - source_min
    source_center = (source_min + source_max) * 0.5
    source_eye_z = source_max.z - source_size.z * 0.507

    # La reconstrucción original resultaba 43 cm alta una vez colocada. Este
    # ajuste preserva una holgura lateral de 1.6 cm y comprime sólo profundidad
    # y altura para respetar el cráneo real, sin deformar la máscara frontal.
    sx = 0.180
    sy = 0.160
    sz = 0.170
    obj.scale = (sx, sy, sz)
    obj.location.x = CX - source_center.x * sx
    # La profundidad de la fuente incluye todo el penacho posterior y no debe
    # centrarse como si fuera el cráneo. Se bloquea el plano facial a 9.5 cm,
    # 1.75 cm por delante del vértice nasal de la pose realmente renderizada.
    # delante del origen, dejando la máscara cerca de nariz y barba.
    obj.location.y = -0.095 - source_min.y * sy
    obj.location.z = 1.245 - source_eye_z * sz
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.transform_apply(location=True, rotation=True, scale=True)
    obj.select_set(False)
    return {"x": sx, "y": sy, "z": sz}


def open_lower_t(obj: bpy.types.Object) -> dict[str, object]:
    """Abre la zona boca/barba con un corte continuo de borde redondeado."""
    bpy.ops.mesh.primitive_cube_add(location=(CX, -0.058, 1.126))
    cutter = bpy.context.object
    cutter.name = "Aqueo_QA_MouthOpeningCutter"
    cutter.dimensions = (0.053, 0.120, 0.104)
    bpy.context.view_layer.objects.active = cutter
    cutter.select_set(True)
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    bevel = cutter.modifiers.new("RoundedOpening", "BEVEL")
    bevel.width = 0.012
    bevel.segments = 5
    bpy.ops.object.modifier_apply(modifier=bevel.name)
    modifier = obj.modifiers.new("CleanLowerTOpening", "BOOLEAN")
    modifier.operation = "DIFFERENCE"
    modifier.solver = "EXACT"
    modifier.object = cutter
    bpy.context.view_layer.objects.active = obj
    obj.select_set(True)
    bpy.ops.object.modifier_apply(modifier=modifier.name)
    obj.select_set(False)
    bpy.data.objects.remove(cutter, do_unlink=True)
    return {
        "method": "exact-rounded-boolean",
        "center": [CX, -0.058, 1.126],
        "dimensions": [0.053, 0.120, 0.104],
    }


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(SOURCE))
    helmet = max((obj for obj in bpy.context.scene.objects if obj.type == "MESH"), key=lambda item: len(item.data.vertices))
    helmet.name = "LeonidasAqueoRealisticCalibrated"
    scale = fit_to_leonidas(helmet)
    opening = {"method": "deferred-until-scale-approval"}
    helmet.data.materials.clear()
    helmet.data.materials.append(material())
    for polygon in helmet.data.polygons:
        polygon.use_smooth = True
    helmet["leonidasHelmetCandidate"] = True
    helmet["leonidasHelmetStage"] = "aqueo-realistic-calibrated"
    helmet["leonidasProductionReady"] = False
    helmet["leonidasReferenceContract"] = "aqueo-realistic-four-view"

    bpy.ops.object.select_all(action="DESELECT")
    helmet.select_set(True)
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
    minimum, maximum = object_bounds(helmet)
    report = {
        "status": "qa-candidate-not-production",
        "source": str(SOURCE.relative_to(ROOT)).replace("\\", "/"),
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "nonUniformCalibration": scale,
        "lowerTOpening": opening,
        "boundsWorld": {
            "min": [round(value, 6) for value in minimum],
            "max": [round(value, 6) for value in maximum],
            "extent": [round(value, 6) for value in maximum - minimum],
        },
        "productionIntegrated": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
