from __future__ import annotations

import argparse
import shutil
import sys
from pathlib import Path


PROJECT_ROOT = Path(__file__).resolve().parents[1]
GRADIO_CLIENT_DIR = PROJECT_ROOT / "storage" / "tools" / "gradio-client"
DEFAULT_OUTPUT_DIR = (
    PROJECT_ROOT / "storage" / "leonidas-helmet-reconstruction" / "hunyuan3d-2mv"
)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Reconstruct a Leonidas helmet from four approved views with Hunyuan3D-2mv."
    )
    parser.add_argument("inputs", type=Path, help="Directory containing front/back/left/right PNGs.")
    parser.add_argument("name", help="Output base name without extension.")
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR)
    parser.add_argument("--seed", type=int, default=424242)
    parser.add_argument("--steps", type=int, default=10)
    parser.add_argument("--octree-resolution", type=int, default=384)
    parser.add_argument("--space", default="tencent/Hunyuan3D-2mv")
    parser.add_argument("--textured", action="store_true")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    input_dir = args.inputs.resolve()
    view_paths = {name: input_dir / f"{name}.png" for name in ("front", "back", "left", "right")}
    missing = [path for path in view_paths.values() if not path.is_file()]
    if missing:
        raise FileNotFoundError(f"Missing multiview inputs: {missing}")
    if not GRADIO_CLIENT_DIR.is_dir():
        raise RuntimeError(f"gradio_client is missing at {GRADIO_CLIENT_DIR}")

    sys.path.insert(0, str(GRADIO_CLIENT_DIR))
    from gradio_client import Client, handle_file

    output_dir = args.output_dir.resolve()
    output_dir.mkdir(parents=True, exist_ok=True)
    download_dir = output_dir / "downloads"
    existing_downloads = {
        path.resolve() for path in download_dir.rglob("*.glb")
    } if download_dir.exists() else set()

    client = Client(
        args.space,
        httpx_kwargs={"timeout": 240.0},
        download_files=str(download_dir),
    )
    request = {
        "image": None,
        "mv_image_front": handle_file(str(view_paths["front"])),
        "mv_image_back": handle_file(str(view_paths["back"])),
        "mv_image_left": handle_file(str(view_paths["left"])),
        "mv_image_right": handle_file(str(view_paths["right"])),
        "steps": args.steps,
        "guidance_scale": 5.0,
        "seed": args.seed,
        "octree_resolution": args.octree_resolution,
        "check_box_rembg": True,
        "num_chunks": 8000,
        "randomize_seed": False,
        "api_name": "/generation_all" if args.textured else "/shape_generation",
    }
    if args.space.lower().endswith("hunyuan3d-2mv"):
        request["caption"] = None
    prediction = client.predict(**request)

    if args.textured:
        shape_path, textured_path, _viewer_html, mesh_stats, used_seed = prediction
        model_path = textured_path or shape_path
    else:
        model_path, _viewer_html, mesh_stats, used_seed = prediction

    if isinstance(model_path, dict):
        model_path = model_path.get("path")
    if not model_path:
        downloaded = [
            path.resolve()
            for path in download_dir.rglob("*.glb")
            if path.resolve() not in existing_downloads
        ]
        if downloaded:
            model_path = max(downloaded, key=lambda path: path.stat().st_mtime)
        else:
            raise RuntimeError(
                "Hunyuan3D-2mv did not return a model path. "
                f"viewer={_viewer_html!r}; stats={mesh_stats!r}; seed={used_seed!r}"
            )
    model_source = Path(model_path)
    suffix = model_source.suffix.lower() or ".glb"
    model_target = output_dir / f"{args.name}{suffix}"
    shutil.copy2(model_source, model_target)

    print(f"model={model_target}")
    print(f"seed={used_seed}")
    print(f"stats={mesh_stats}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
