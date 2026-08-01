"""Reconstruct a standalone helmet with Stability AI's official SF3D Space.

The generated asset is kept in storage as a review candidate. This script does
not update Leonidas manifests or attach the helmet to the avatar.
"""

from __future__ import annotations

import argparse
import shutil
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "storage" / "tools" / "gradio-client"))

from gradio_client import Client, handle_file


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("input_image", type=Path)
    parser.add_argument("output_glb", type=Path)
    parser.add_argument("--foreground-ratio", type=float, default=0.90)
    parser.add_argument("--texture-size", type=int, choices=(512, 1024, 2048), default=2048)
    return parser.parse_args()


def result_path(value: object) -> Path | None:
    if isinstance(value, str):
        path = Path(value)
        return path if path.exists() else None
    if isinstance(value, dict) and value.get("path"):
        path = Path(str(value["path"]))
        return path if path.exists() else None
    return None


def main() -> None:
    args = parse_args()
    source = args.input_image.resolve()
    output = args.output_glb.resolve()
    if not source.exists():
        raise SystemExit(f"Input image not found: {source}")
    output.parent.mkdir(parents=True, exist_ok=True)

    client = Client(
        "stabilityai/stable-fast-3d",
        download_files=str(output.parent / "sf3d-downloads"),
    )
    result = client.predict(
        handle_file(str(source)),
        args.foreground_ratio,
        "None",
        -1,
        args.texture_size,
        api_name="/run_button",
    )
    values = result if isinstance(result, (tuple, list)) else (result,)
    candidates = [path for value in values if (path := result_path(value))]
    source_glb = next((path for path in candidates if path.suffix.lower() == ".glb"), None)
    if source_glb is None:
        raise RuntimeError(f"SF3D did not return a GLB: {result!r}")
    shutil.copy2(source_glb, output)
    print(f"SF3D_OUTPUT {output}")


if __name__ == "__main__":
    main()
