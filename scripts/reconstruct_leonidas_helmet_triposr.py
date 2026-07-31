from __future__ import annotations

import argparse
import shutil
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "storage" / "tools" / "gradio-client"))

from gradio_client import Client, handle_file


def result_path(value) -> Path | None:
    if isinstance(value, str) and Path(value).exists():
        return Path(value)
    if isinstance(value, dict) and value.get("path") and Path(value["path"]).exists():
        return Path(value["path"])
    return None


parser = argparse.ArgumentParser()
parser.add_argument("input_image", type=Path)
parser.add_argument("output_glb", type=Path)
parser.add_argument("--resolution", type=int, default=320)
args = parser.parse_args()

source = args.input_image.resolve()
output = args.output_glb.resolve()
output.parent.mkdir(parents=True, exist_ok=True)
client = Client("stabilityai/TripoSR", download_files=str(output.parent / "downloads"))
processed = client.predict(handle_file(str(source)), True, 0.82, api_name="/preprocess")
generated = client.predict(processed, args.resolution, api_name="/generate")
values = generated if isinstance(generated, (tuple, list)) else (generated,)
glb = next((result_path(value) for value in reversed(values) if result_path(value)), None)
if glb is None:
    raise RuntimeError(f"TripoSR did not return a GLB: {generated!r}")
shutil.copy2(glb, output)
print(f"TRIPOSR_OUTPUT {output}")
