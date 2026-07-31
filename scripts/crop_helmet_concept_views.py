from __future__ import annotations

import argparse
from pathlib import Path

from PIL import Image


parser = argparse.ArgumentParser(description="Split a 2x2 helmet concept sheet into named orthographic views")
parser.add_argument("concept_sheet", type=Path)
parser.add_argument("output_dir", type=Path)
args = parser.parse_args()

source = Image.open(args.concept_sheet).convert("RGB")
width, height = source.size
half_w, half_h = width // 2, height // 2
args.output_dir.mkdir(parents=True, exist_ok=True)

# Contract used by both approved concept sheets:
# top-left front, top-right 3/4/right, bottom-left left, bottom-right back.
boxes = {
    "front": (0, 0, half_w, half_h),
    "right": (half_w, 0, width, half_h),
    "left": (0, half_h, half_w, height),
    "back": (half_w, half_h, width, height),
}
for name, box in boxes.items():
    target = args.output_dir / f"{name}.png"
    source.crop(box).save(target)
    print(target)
