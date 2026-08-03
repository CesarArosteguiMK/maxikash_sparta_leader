"""Registra el contrato dimensional del casco negro Áqueo oscuro.

El contrato enlaza el candidato histórico con la cabeza real y la cúpula
etapa 01 validada. No modifica ni vuelve a exportar el casco.
"""

from __future__ import annotations

import json
import hashlib
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
MEASUREMENTS = ROOT / "storage" / "leonidas-helmet-designs" / "measurements"
HEAD_CONTRACT = MEASUREMENTS / "leonidas-head-contract-v1.json"
STAGE_ROOT = ROOT / "storage" / "leonidas-helmet-designs" / "stage-01-dome"
DOME_REPORT = STAGE_ROOT / "leonidas-helmet-dome-stage-01-report.json"
DOME_VALIDATION = STAGE_ROOT / "leonidas-helmet-dome-stage-01-validation.json"
STORAGE_OUTPUT = MEASUREMENTS / "aqueo-dark-fit-contract-v1.json"
PUBLIC_OUTPUT = (
    ROOT
    / "public"
    / "assets"
    / "models"
    / "leonidas"
    / "qa"
    / "helmet-fit-contracts.json"
)
CANDIDATES_PATH = (
    ROOT
    / "storage"
    / "leonidas-helmet-designs"
    / "sculpted"
    / "CANDIDATOS.json"
)


def read_json(path: Path) -> dict:
    return json.loads(path.read_text(encoding="utf-8"))


def sha256(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def transform_point(matrix: list[list[float]], point: list[float]) -> list[float]:
    vector = [point[0], point[1], point[2], 1.0]
    return [
        round(sum(matrix[row][column] * vector[column] for column in range(4)), 6)
        for row in range(3)
    ]


def transformed_bounds(
    matrix: list[list[float]], minimum: list[float], maximum: list[float]
) -> dict[str, list[float]]:
    corners = [
        transform_point(matrix, [x, y, z])
        for x in (minimum[0], maximum[0])
        for y in (minimum[1], maximum[1])
        for z in (minimum[2], maximum[2])
    ]
    local_minimum = [min(point[axis] for point in corners) for axis in range(3)]
    local_maximum = [max(point[axis] for point in corners) for axis in range(3)]
    return {
        "min": [round(value, 6) for value in local_minimum],
        "max": [round(value, 6) for value in local_maximum],
        "extent": [
            round(local_maximum[axis] - local_minimum[axis], 6)
            for axis in range(3)
        ],
    }


head = read_json(HEAD_CONTRACT)
dome = read_json(DOME_REPORT)
validation = read_json(DOME_VALIDATION)
if not validation.get("valid"):
    raise RuntimeError("La cúpula etapa 01 no está validada; no se puede fijar el contrato Áqueo.")
if dome["parameters_meters"]["collision_count"] != 0.0:
    raise RuntimeError("La cúpula de referencia registra colisiones.")

world_to_bone = head["head_bone_local"]["world_to_bone_matrix"]
dome_params = dome["parameters_meters"]
dome_center_world = [
    dome_params["axis_x"],
    dome_params["center_y"],
    dome_params["center_z"],
]
dome_center_bone = transform_point(world_to_bone, dome_center_world)
shell_bounds_bone = transformed_bounds(
    world_to_bone,
    dome["shell_bounds"]["min"],
    dome["shell_bounds"]["max"],
)

aqueo_contract = {
    "id": "aqueo_oscuro",
    "name": "Áqueo oscuro",
    "version": 1,
    "status": "measurements_locked_rebuild_required",
    "productionReady": False,
    "anchor": {
        "model": "leonidas-spartan-modular-v2.glb",
        "anatomyObject": "LeonidasHeadUnderlay",
        "armature": "LeonidasRig",
        "bone": "mixamorig:Head",
        "axes": head["head_bone_local"]["axes"],
        "centerBoneLocal": dome_center_bone,
    },
    "headReference": {
        "contract": "storage/leonidas-helmet-designs/measurements/leonidas-head-contract-v1.json",
        "robustWorld": head["world"]["robust_0_5_to_99_5_percent"],
        "exactWorld": head["world"]["exact"],
        "clearanceMeters": head["design_reference"]["clearance"],
    },
    "approvedDomeReference": {
        "asset": "/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb",
        "object": "LeonidasHelmetDomeStage01",
        "sourceBlend": "storage/leonidas-helmet-designs/stage-01-dome/leonidas-helmet-dome-stage-01.blend",
        "worldParametersMeters": dome_params,
        "shellBoundsWorldMeters": dome["shell_bounds"],
        "shellBoundsBoneLocalMeters": shell_bounds_bone,
        "shellThicknessMeters": dome["shell_thickness"],
        "validation": {
            "valid": True,
            "collisions": 0,
            "coveredHeadPoints": int(dome_params["covered_head_points"]),
            "maxOccupancy": dome_params["max_occupancy"],
            "manifold": validation["checks"]["closed_manifold"],
            "symmetryRatio": validation["shell"]["symmetry_ratio"],
        },
    },
    "legacyCandidate": {
        "asset": "/assets/models/leonidas/qa/helmet-aqueo-dimensioned-v7.glb",
        "fitNode": "Aqueo_FitReference",
        "status": "unverified_do_not_promote",
        "requiresRebuildAgainstApprovedDome": True,
    },
    "fitPolicy": {
        "strategy": "rebuild_shell_against_approved_dome",
        "runtimeGlobalScaleIsFinalFit": False,
        "runtimeOffsetsAreQaOnly": True,
        "crestMayDefineScale": False,
        "maskMayDefineScale": False,
        "requiredFitNode": "Aqueo_FitReference",
        "requiredSymmetryAxis": "Head local X",
    },
    "acceptance": {
        "maximumBoundsDeviationMeters": 0.003,
        "maximumAnchorDeviationMeters": 0.002,
        "maximumOccupancy": 0.965,
        "allowedCollisionCount": 0,
        "requiresClosedManifold": True,
        "requiresFrontLeftRightBackTopCutawayReview": True,
        "requiresHumanVisualApproval": True,
    },
    "pendingStages": [
        "rebuild black shell against dome",
        "design facial opening and mask",
        "design crest and plume",
        "bind rigidly to Head bone",
        "validate on animated Leonidas",
    ],
}

document = {
    "version": 1,
    "productionIsolation": True,
    "sourceOfTruth": "measured LeonidasHeadUnderlay and validated stage-01 dome",
    "sourceChecksumsSha256": {
        "headContract": sha256(HEAD_CONTRACT),
        "domeReport": sha256(DOME_REPORT),
        "domeValidation": sha256(DOME_VALIDATION),
    },
    "contracts": {"aqueo_oscuro": aqueo_contract},
}
MEASUREMENTS.mkdir(parents=True, exist_ok=True)
PUBLIC_OUTPUT.parent.mkdir(parents=True, exist_ok=True)
payload = json.dumps(document, ensure_ascii=False, indent=2) + "\n"
STORAGE_OUTPUT.write_text(payload, encoding="utf-8")
PUBLIC_OUTPUT.write_text(payload, encoding="utf-8")

if CANDIDATES_PATH.exists():
    candidates = read_json(CANDIDATES_PATH)
    for candidate in candidates.get("candidates", []):
        if candidate.get("id") == "aqueo_oscuro_v7":
            candidate.update(
                {
                    "fit_contract": "../measurements/aqueo-dark-fit-contract-v1.json",
                    "fit_contract_public": "/assets/models/leonidas/qa/helmet-fit-contracts.json#aqueo_oscuro",
                    "fit_status": "legacy_requires_rebuild",
                    "approved_dome": "/assets/models/leonidas/qa/leonidas-helmet-dome-stage-01.glb",
                    "runtime_scale_is_final_fit": False,
                }
            )
    CANDIDATES_PATH.write_text(
        json.dumps(candidates, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )

print("AQUEO_DARK_FIT_CONTRACT", STORAGE_OUTPUT)
print("AQUEO_DARK_PUBLIC_FIT_CONTRACT", PUBLIC_OUTPUT)
print("AQUEO_DARK_CENTER_BONE_LOCAL", dome_center_bone)
print("AQUEO_DARK_SHELL_BOUNDS_BONE_LOCAL", shell_bounds_bone)
