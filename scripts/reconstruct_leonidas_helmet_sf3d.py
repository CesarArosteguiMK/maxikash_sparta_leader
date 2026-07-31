from __future__ import annotations

import argparse
import shutil
import sys
from pathlib import Path


PROJECT_ROOT = Path(__file__).resolve().parents[1]
GRADIO_CLIENT_DIR = PROJECT_ROOT / "storage" / "tools" / "gradio-client"
DEFAULT_OUTPUT_DIR = (
    PROJECT_ROOT / "storage" / "leonidas-helmet-reconstruction" / "sf3d"
)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Reconstruct a Leonidas helmet reference as a textured GLB with SF3D."
    )
    parser.add_argument("input", type=Path, help="Clean single-view PNG reference.")
    parser.add_argument("name", help="Output base name without extension.")
    parser.add_argument(
        "--foreground-ratio",
        type=float,
        default=0.85,
        choices=[0.5, 0.55, 0.6, 0.65, 0.7, 0.75, 0.8, 0.85, 0.9, 0.95, 1.0],
    )
    parser.add_argument(
        "--texture-size",
        type=int,
        default=1024,
        choices=[512, 768, 1024, 1280, 1536, 1792, 2048],
    )
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR)
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    source = args.input.resolve()
    if not source.is_file():
        raise FileNotFoundError(f"Input reference not found: {source}")
    if not GRADIO_CLIENT_DIR.is_dir():
        raise RuntimeError(
            "gradio_client is missing. Install it into "
            f"{GRADIO_CLIENT_DIR} before running this script."
        )

    sys.path.insert(0, str(GRADIO_CLIENT_DIR))
    from gradio_client import Client, handle_file

    output_dir = args.output_dir.resolve()
    output_dir.mkdir(parents=True, exist_ok=True)

    client = Client(
        "stabilityai/stable-fast-3d",
        httpx_kwargs={"timeout": 180.0},
        download_files=str(output_dir / "downloads"),
        _skip_components=False,
    )
    input_file = handle_file(str(source))
    endpoint = client.endpoints[5]

    def run_raw(button_value: str):
        helper = client.new_helper(5, headers={"x-gradio-user": "api"})
        invoke = endpoint.make_end_to_end_fn(helper)
        return invoke(
            button_value,
            input_file,
            None,
            args.foreground_ratio,
            "None",
            -1,
            args.texture_size,
        )

    # The public Space implements background removal and reconstruction as two
    # states of the same button. Its simplified API hides that button value, so
    # call the endpoint with the exact UI sequence while preserving session state.
    remove_result = run_raw("Remove Background")
    run_result = run_raw("Run")
    preview_value = remove_result[3] if len(remove_result) > 3 else None
    model_value = run_result[4]

    def file_path(value):
        if isinstance(value, dict):
            return value.get("path")
        return value

    preview_path = file_path(preview_value)
    model_path = file_path(model_value)
    if not model_path:
        raise RuntimeError(f"SF3D did not return a GLB path: {model_value!r}")

    model_source = Path(model_path)
    model_target = output_dir / f"{args.name}.glb"
    shutil.copy2(model_source, model_target)

    if preview_path:
        preview_source = Path(preview_path)
        if preview_source.is_file():
            preview_target = output_dir / f"{args.name}-foreground.png"
            shutil.copy2(preview_source, preview_target)

    print(model_target)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
