"""Mide la postura del rig de Leonidas directamente desde su GLB modular."""

from __future__ import annotations

import argparse
import json
import math
import struct
from pathlib import Path

import numpy as np


COMPONENT_DTYPES = {
    5120: np.int8,
    5121: np.uint8,
    5122: np.int16,
    5123: np.uint16,
    5125: np.uint32,
    5126: np.float32,
}
TYPE_WIDTHS = {
    "SCALAR": 1,
    "VEC2": 2,
    "VEC3": 3,
    "VEC4": 4,
    "MAT4": 16,
}


def load_glb(path: Path) -> tuple[dict, bytes]:
    raw = path.read_bytes()
    magic, version, length = struct.unpack_from("<4sII", raw, 0)
    if magic != b"glTF" or version != 2 or length != len(raw):
        raise ValueError(f"GLB invalido: {path}")
    offset = 12
    chunks: dict[bytes, bytes] = {}
    while offset < len(raw):
        chunk_length, chunk_type = struct.unpack_from("<I4s", raw, offset)
        offset += 8
        chunks[chunk_type] = raw[offset : offset + chunk_length]
        offset += chunk_length
    return json.loads(chunks[b"JSON"].decode("utf-8")), chunks[b"BIN\x00"]


class Glb:
    def __init__(self, path: Path) -> None:
        self.data, self.binary = load_glb(path)
        self.nodes = self.data.get("nodes", [])
        self.parents: dict[int, int] = {}
        for parent, node in enumerate(self.nodes):
            for child in node.get("children", []):
                self.parents[child] = parent

    def accessor(self, index: int) -> np.ndarray:
        accessor = self.data["accessors"][index]
        view = self.data["bufferViews"][accessor["bufferView"]]
        dtype = np.dtype(COMPONENT_DTYPES[accessor["componentType"]]).newbyteorder("<")
        width = TYPE_WIDTHS[accessor["type"]]
        offset = view.get("byteOffset", 0) + accessor.get("byteOffset", 0)
        stride = view.get("byteStride", dtype.itemsize * width)
        count = accessor["count"]
        if stride == dtype.itemsize * width:
            return np.frombuffer(
                self.binary,
                dtype=dtype,
                count=count * width,
                offset=offset,
            ).reshape(count, width)
        rows = [
            np.frombuffer(
                self.binary,
                dtype=dtype,
                count=width,
                offset=offset + row * stride,
            )
            for row in range(count)
        ]
        return np.stack(rows)

    def find(self, *needles: str) -> int:
        normalized = {
            "".join(character for character in node.get("name", "").lower() if character.isalnum()): index
            for index, node in enumerate(self.nodes)
        }
        for needle in needles:
            key = "".join(character for character in needle.lower() if character.isalnum())
            for name, index in normalized.items():
                if name == key or name.endswith(key):
                    return index
        raise KeyError(needles)


def quaternion_matrix(value: np.ndarray) -> np.ndarray:
    x, y, z, w = value / max(np.linalg.norm(value), 1e-12)
    return np.array(
        [
            [1 - 2 * (y * y + z * z), 2 * (x * y - z * w), 2 * (x * z + y * w), 0],
            [2 * (x * y + z * w), 1 - 2 * (x * x + z * z), 2 * (y * z - x * w), 0],
            [2 * (x * z - y * w), 2 * (y * z + x * w), 1 - 2 * (x * x + y * y), 0],
            [0, 0, 0, 1],
        ],
        dtype=float,
    )


def trs_matrix(translation: np.ndarray, rotation: np.ndarray, scale: np.ndarray) -> np.ndarray:
    matrix = quaternion_matrix(rotation)
    matrix[:3, :3] *= scale[np.newaxis, :]
    matrix[:3, 3] = translation
    return matrix


def slerp(left: np.ndarray, right: np.ndarray, alpha: float) -> np.ndarray:
    left = left / max(np.linalg.norm(left), 1e-12)
    right = right / max(np.linalg.norm(right), 1e-12)
    dot = float(np.dot(left, right))
    if dot < 0:
        right = -right
        dot = -dot
    if dot > 0.9995:
        result = left + alpha * (right - left)
        return result / max(np.linalg.norm(result), 1e-12)
    theta = math.acos(max(-1.0, min(1.0, dot)))
    sin_theta = math.sin(theta)
    return (
        math.sin((1 - alpha) * theta) / sin_theta * left
        + math.sin(alpha * theta) / sin_theta * right
    )


def interpolate(times: np.ndarray, values: np.ndarray, moment: float, rotation: bool) -> np.ndarray:
    flat_times = times[:, 0]
    if moment <= flat_times[0]:
        return values[0].astype(float)
    if moment >= flat_times[-1]:
        return values[-1].astype(float)
    right = int(np.searchsorted(flat_times, moment, side="right"))
    left = right - 1
    alpha = (moment - flat_times[left]) / (flat_times[right] - flat_times[left])
    if rotation:
        return slerp(values[left], values[right], float(alpha))
    return values[left] * (1 - alpha) + values[right] * alpha


def angle_degrees(first: np.ndarray, middle: np.ndarray, last: np.ndarray) -> float:
    left = first - middle
    right = last - middle
    cosine = float(np.dot(left, right) / max(np.linalg.norm(left) * np.linalg.norm(right), 1e-12))
    return math.degrees(math.acos(max(-1.0, min(1.0, cosine))))


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "model",
        nargs="?",
        type=Path,
        default=Path("public/assets/models/leonidas/leonidas-spartan-modular-v2.glb"),
    )
    args = parser.parse_args()
    glb = Glb(args.model)
    animation = glb.data["animations"][0]
    channels: dict[tuple[int, str], tuple[np.ndarray, np.ndarray]] = {}
    duration = 0.0
    for channel in animation["channels"]:
        sampler = animation["samplers"][channel["sampler"]]
        times = glb.accessor(sampler["input"])
        values = glb.accessor(sampler["output"])
        target = channel["target"]
        channels[(target["node"], target["path"])] = (times, values)
        duration = max(duration, float(times[-1, 0]))

    joints = {
        "hips": glb.find("mixamorigHips"),
        "spine": glb.find("mixamorigSpine"),
        "spine1": glb.find("mixamorigSpine1"),
        "spine2": glb.find("mixamorigSpine2"),
        "neck": glb.find("mixamorigNeck"),
        "head": glb.find("mixamorigHead"),
        "left_hip": glb.find("mixamorigLeftUpLeg"),
        "left_knee": glb.find("mixamorigLeftLeg"),
        "left_ankle": glb.find("mixamorigLeftFoot"),
        "right_hip": glb.find("mixamorigRightUpLeg"),
        "right_knee": glb.find("mixamorigRightLeg"),
        "right_ankle": glb.find("mixamorigRightFoot"),
    }

    def pose(moment: float | None) -> dict:
        local: list[np.ndarray] = []
        for index, node in enumerate(glb.nodes):
            if "matrix" in node:
                matrix = np.array(node["matrix"], dtype=float).reshape(4, 4).T
                local.append(matrix)
                continue
            translation = np.array(node.get("translation", [0, 0, 0]), dtype=float)
            rotation = np.array(node.get("rotation", [0, 0, 0, 1]), dtype=float)
            scale = np.array(node.get("scale", [1, 1, 1]), dtype=float)
            if moment is not None and (index, "translation") in channels:
                translation = interpolate(*channels[(index, "translation")], moment, False)
            if moment is not None and (index, "rotation") in channels:
                rotation = interpolate(*channels[(index, "rotation")], moment, True)
            if moment is not None and (index, "scale") in channels:
                scale = interpolate(*channels[(index, "scale")], moment, False)
            local.append(trs_matrix(translation, rotation, scale))

        world: dict[int, np.ndarray] = {}

        def world_matrix(index: int) -> np.ndarray:
            if index not in world:
                parent = glb.parents.get(index)
                world[index] = local[index] if parent is None else world_matrix(parent) @ local[index]
            return world[index]

        positions = {name: world_matrix(index)[:3, 3] for name, index in joints.items()}
        hips = positions["hips"]
        head = positions["head"]
        height = max(head[1] - hips[1], 1e-6)
        torso_depth = float((head[2] - hips[2]) / height)
        torso_side = float((head[0] - hips[0]) / height)
        left_knee = angle_degrees(positions["left_hip"], positions["left_knee"], positions["left_ankle"])
        right_knee = angle_degrees(positions["right_hip"], positions["right_knee"], positions["right_ankle"])
        knee_bend = (180 - left_knee + 180 - right_knee) / 2
        score = abs(torso_depth) * 100 + abs(torso_side) * 60 + knee_bend * 0.35
        return {
            "time": "rest" if moment is None else moment,
            "torsoDepth": torso_depth,
            "torsoSide": torso_side,
            "leftKnee": left_knee,
            "rightKnee": right_knee,
            "score": score,
            "positions": {name: value.tolist() for name, value in positions.items()},
        }

    candidates = [pose(float(moment)) for moment in np.linspace(0, duration, 401)]
    best = min(candidates, key=lambda value: value["score"])
    requested = sorted(set([
        0, 0.35, 0.45, 1, 2, 3, 4, 4.55,
        5, 5.4, 5.6, 5.8, 6, 6.1, 6.2, 6.3, 6.4, 6.6, 6.8, 7,
        duration, best["time"],
    ]))
    report = {
        "model": str(args.model),
        "animation": animation.get("name", "animation-0"),
        "duration": duration,
        "rest": pose(None),
        "best": best,
        "samples": [pose(moment) for moment in requested if moment <= duration],
    }
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
