from __future__ import annotations

import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "storage" / "tools" / "gradio-client"))

from gradio_client import Client


if len(sys.argv) != 2:
    raise SystemExit("Usage: python inspect_gradio_space.py owner/space")

client = Client(sys.argv[1])
print(client.view_api())
