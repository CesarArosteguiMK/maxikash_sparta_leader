"""Aqueo oscuro v16: conserva la crin escultorica y corrige su eje sagital.

La reconstruccion multivista contiene una crin organica util, pero venia unida
a la mascara y con el ancho de toda la cabeza. Esta version separa ambas zonas,
comprime solo la crin en X y conserva intactos su arco, fibras y cola trasera.
"""

from __future__ import annotations

import json
import os
import sys
from pathlib import Path

import bmesh
import bpy

sys.path.insert(0, os.fspath(Path(__file__).resolve().parent))
import construir_aqueo_oscuro_etapa_02 as preview
import construir_aqueo_oscuro_produccion_v14 as v14


ROOT = Path(__file__).resolve().parents[1]
OUTPUT_ROOT = ROOT / "storage/leonidas-helmet-designs/aqueo-dark-production-v16"
OUTPUT_GLB = ROOT / "public/assets/models/leonidas/qa/leonidas-aqueo-dark-production-v16.glb"
OUTPUT_BLEND = OUTPUT_ROOT / "leonidas-aqueo-dark-production-v16.blend"
REPORT = OUTPUT_ROOT / "leonidas-aqueo-dark-production-v16-report.json"
CREST_X_COMPRESSION = 0.235


def is_crest_vertex(co) -> bool:
    # Incluye el antiguo soporte superior para comprimirlo junto con la crin;
    # si permaneciera en la mascara formaria un visor horizontal flotante.
    return co.z > 1.264 or (co.y > 0.078 and co.z > 1.055)


def delete_vertices(obj: bpy.types.Object, predicate) -> int:
    bm = bmesh.new()
    bm.from_mesh(obj.data)
    doomed = [vertex for vertex in bm.verts if predicate(vertex.co)]
    removed = len(doomed)
    bmesh.ops.delete(bm, geom=doomed, context="VERTS")
    bm.to_mesh(obj.data)
    bm.free()
    obj.data.update(calc_edges=True)
    return removed


def split_faceguard_and_crest(
    obj: bpy.types.Object,
) -> tuple[bpy.types.Object, bpy.types.Object, dict[str, int]]:
    crest = obj.copy()
    crest.data = obj.data.copy()
    crest.name = "AqueoV16_SculptedSagittalCrest"
    bpy.context.scene.collection.objects.link(crest)
    removed_from_faceguard = delete_vertices(obj, is_crest_vertex)
    removed_from_crest = delete_vertices(crest, lambda co: not is_crest_vertex(co))
    cx = v14.TARGET_CENTER_X
    for vertex in crest.data.vertices:
        vertex.co.x = cx + (vertex.co.x - cx) * CREST_X_COMPRESSION
    crest.data.update()
    return obj, crest, {
        "removedFromFaceguard": removed_from_faceguard,
        "removedFromCrest": removed_from_crest,
        "crestVertices": len(crest.data.vertices),
    }


def main() -> None:
    OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    OUTPUT_GLB.parent.mkdir(parents=True, exist_ok=True)
    v14.clear_scene()
    bpy.ops.import_scene.gltf(filepath=os.fspath(v14.SOURCE))
    imported = [obj for obj in bpy.context.scene.objects if obj.type == "MESH"]
    if len(imported) != 1:
        raise RuntimeError(f"Se esperaba una malla escultorica, recibidas: {len(imported)}")
    faceguard = imported[0]
    faceguard.name = "AqueoV16_SculptedFaceguard"
    fit = v14.apply_anatomical_fit([faceguard])
    reduction = v14.reduce_for_web([faceguard])
    faceguard, crest, split_stats = split_faceguard_and_crest(faceguard)

    steel, _bronze = v14.production_materials()
    hair = preview.make_material(
        "AqueoV16_NaturalBlackHorsehair",
        (0.0035, 0.0055, 0.0080, 1.0),
        0.02,
        0.86,
        noise_scale=145.0,
        bump_strength=0.07,
        bump_distance=0.00022,
    )
    faceguard.data.materials.clear()
    faceguard.data.materials.append(steel)
    crest.data.materials.clear()
    crest.data.materials.append(hair)
    for obj in (faceguard, crest):
        for polygon in obj.data.polygons:
            polygon.material_index = 0
            polygon.use_smooth = True

    fit_reference, shell = v14.import_approved_shell(steel)
    forehead = v14.make_anatomical_forehead(steel)
    export_objects = [faceguard, crest, shell, forehead]

    for obj in [*export_objects, fit_reference]:
        obj["leonidasHelmetCandidate"] = True
        obj["leonidasHelmetStage"] = "aqueo-dark-production-v16"
        obj["leonidasProductionReady"] = True
        obj["leonidasHeadBone"] = "mixamorig:Head"
        obj["leonidasAxisCalibrated"] = True

    bpy.ops.object.select_all(action="DESELECT")
    for obj in [*export_objects, fit_reference]:
        obj.select_set(True)
    bpy.context.view_layer.objects.active = faceguard
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
        "stage": "aqueo-dark-production-v16",
        "status": "production-candidate",
        "source": str(v14.SOURCE.relative_to(ROOT)).replace("\\", "/"),
        "fit": fit,
        "reduction": reduction,
        "split": split_stats,
        "crestXCompression": CREST_X_COMPRESSION,
        "architecture": [
            "approved-closed-anatomical-shell",
            "sculpted-open-faceguard",
            "separated-organic-sagittal-crest",
            "independent-crest-width-calibration",
            "preserved-sculpted-fibers-and-rear-tail",
            "no-floating-decorative-trim",
            "head-bone-anchor-contract",
        ],
        "output": str(OUTPUT_GLB.relative_to(ROOT)).replace("\\", "/"),
        "defaultVisible": False,
    }
    REPORT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")

    preview.OUTPUT_ROOT = OUTPUT_ROOT / "preview"
    preview.REPORT = preview.OUTPUT_ROOT / "preview-report.json"
    preview.OUTPUT_ROOT.mkdir(parents=True, exist_ok=True)
    preview.render_views(OUTPUT_GLB)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
