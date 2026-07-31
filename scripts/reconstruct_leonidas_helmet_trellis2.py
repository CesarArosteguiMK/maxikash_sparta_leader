from __future__ import annotations

import argparse
import shutil
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "storage" / "tools" / "gradio-client"))

from gradio_client import Client, handle_file


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Reconstruct a standalone helmet with the official TRELLIS.2 Space")
    parser.add_argument("input_image", type=Path)
    parser.add_argument("output_glb", type=Path)
    parser.add_argument("--resolution", choices=("512", "1024", "1536"), default="1024")
    parser.add_argument("--seed", type=int, default=1206)
    parser.add_argument("--steps", type=int, default=12)
    parser.add_argument("--decimation-target", type=int, default=300_000)
    parser.add_argument("--texture-size", type=int, default=2048)
    return parser.parse_args()


def path_from_result(value) -> Path | None:
    if isinstance(value, str):
        candidate = Path(value)
        return candidate if candidate.exists() else None
    if isinstance(value, dict):
        raw = value.get("path")
        if raw:
            candidate = Path(raw)
            return candidate if candidate.exists() else None
    return None


def main() -> None:
    args = parse_args()
    source = args.input_image.resolve()
    output = args.output_glb.resolve()
    if not source.exists():
        raise SystemExit(f"Input image not found: {source}")
    output.parent.mkdir(parents=True, exist_ok=True)

    client = Client("microsoft/TRELLIS.2", download_files=str(output.parent / "downloads"))
    try:
        client.predict(api_name="/start_session")
    except Exception:
        pass
    processed = client.predict(handle_file(str(source)), api_name="/preprocess_image")
    client.predict(
        processed,
        args.seed,
        args.resolution,
        7.5,
        0.7,
        args.steps,
        5.0,
        7.5,
        0.5,
        args.steps,
        3.0,
        1.0,
        0.0,
        args.steps,
        3.0,
        api_name="/image_to_3d",
    )
    extracted = client.predict(
        args.decimation_target,
        args.texture_size,
        api_name="/extract_glb",
    )
    values = extracted if isinstance(extracted, (tuple, list)) else (extracted,)
    source_glb = next((path_from_result(value) for value in values if path_from_result(value)), None)
    if source_glb is None:
        raise RuntimeError(f"TRELLIS.2 did not return a downloadable GLB: {extracted!r}")
    shutil.copy2(source_glb, output)
    print(f"TRELLIS2_OUTPUT {output}")


if __name__ == "__main__":
    main()
