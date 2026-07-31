from __future__ import annotations

import argparse
import shutil
import sys
import uuid
from pathlib import Path


PROJECT_ROOT = Path(__file__).resolve().parents[1]
GRADIO_CLIENT_DIR = PROJECT_ROOT / "storage" / "tools" / "gradio-client"
DEFAULT_OUTPUT_DIR = PROJECT_ROOT / "storage" / "leonidas-helmet-reconstruction" / "pixal3d"


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Reconstruct a high-fidelity Leonidas helmet with TencentARC Pixal3D."
    )
    parser.add_argument("input", type=Path, help="Approved three-quarter helmet image.")
    parser.add_argument("name", help="Output base name without extension.")
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR)
    parser.add_argument("--resolution", type=int, choices=[1024, 1536], default=1024)
    parser.add_argument("--decimation-target", type=int, default=500000)
    parser.add_argument("--texture-size", type=int, choices=[1024, 2048, 3072, 4096], default=2048)
    parser.add_argument("--seed", type=int, default=424242)
    return parser.parse_args()


def file_path(value) -> str:
    if isinstance(value, dict):
        value = value.get("path")
    if not value:
        raise RuntimeError(f"Expected a file path, received {value!r}")
    return str(value)


def main() -> int:
    args = parse_args()
    source = args.input.resolve()
    if not source.is_file():
        raise FileNotFoundError(source)
    if not GRADIO_CLIENT_DIR.is_dir():
        raise RuntimeError(f"gradio_client is missing at {GRADIO_CLIENT_DIR}")

    sys.path.insert(0, str(GRADIO_CLIENT_DIR))
    from gradio_client import Client, handle_file

    output_dir = args.output_dir.resolve()
    output_dir.mkdir(parents=True, exist_ok=True)
    session_id = f"leonidas-{uuid.uuid4().hex[:12]}"
    client = Client(
        "TencentARC/Pixal3D",
        httpx_kwargs={"timeout": 300.0},
        download_files=str(output_dir / "downloads"),
    )

    preprocessed = client.predict(
        image=handle_file(str(source)),
        api_name="/preprocess",
    )
    preprocessed_path = file_path(preprocessed)
    state = client.predict(
        image=handle_file(preprocessed_path),
        seed=args.seed,
        resolution=args.resolution,
        ss_guidance_strength=7.5,
        ss_guidance_rescale=0.7,
        ss_sampling_steps=12,
        ss_rescale_t=5.0,
        shape_slat_guidance_strength=7.5,
        shape_slat_guidance_rescale=0.5,
        shape_slat_sampling_steps=12,
        shape_slat_rescale_t=3.0,
        tex_slat_guidance_strength=1.0,
        tex_slat_guidance_rescale=0.0,
        tex_slat_sampling_steps=12,
        tex_slat_rescale_t=3.0,
        manual_fov=-1.0,
        fov_unit="deg",
        session_id=session_id,
        api_name="/generate_3d",
    )
    state_path = state.get("state_path") if isinstance(state, dict) else None
    if not state_path:
        raise RuntimeError(f"Pixal3D did not return a reconstruction state: {state!r}")

    glb_value = client.predict(
        state_path=state_path,
        decimation_target=args.decimation_target,
        texture_size=args.texture_size,
        session_id=session_id,
        api_name="/extract_glb_api",
    )
    glb_source = Path(file_path(glb_value))
    glb_target = output_dir / f"{args.name}.glb"
    shutil.copy2(glb_source, glb_target)

    preview_target = output_dir / f"{args.name}-preprocessed.png"
    shutil.copy2(preprocessed_path, preview_target)
    print(f"model={glb_target}")
    print(f"preprocessed={preview_target}")
    print(f"session={session_id}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
