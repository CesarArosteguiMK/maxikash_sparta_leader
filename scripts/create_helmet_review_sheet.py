"""Create a labelled four-view review sheet from turntable renders."""

from __future__ import annotations

import argparse
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


parser = argparse.ArgumentParser()
parser.add_argument("renders_dir", type=Path)
parser.add_argument("output", type=Path)
parser.add_argument("title")
options = parser.parse_args()

views = (("front", "Frente"), ("right", "Perfil derecho"), ("back", "Espalda"), ("left", "Perfil izquierdo"))
images = [(name, label, Image.open(options.renders_dir / f"{name}.png").convert("RGB")) for name, label in views]
cell_width = max(image.width for _, _, image in images)
cell_height = max(image.height for _, _, image in images)
header = 86
label_height = 42
sheet = Image.new("RGB", (cell_width * 2, header + (cell_height + label_height) * 2), (20, 24, 31))
draw = ImageDraw.Draw(sheet)
font_path = Path("C:/Windows/Fonts/segoeui.ttf")
if font_path.exists():
    font = ImageFont.truetype(str(font_path), 24)
    label_font = ImageFont.truetype(str(font_path), 18)
else:
    font = ImageFont.load_default(size=24)
    label_font = ImageFont.load_default(size=18)
draw.text((32, 28), options.title, fill=(236, 239, 244), font=font)

for index, (_name, label, image) in enumerate(images):
    column = index % 2
    row = index // 2
    x = column * cell_width
    y = header + row * (cell_height + label_height)
    sheet.paste(image, (x, y))
    draw.rectangle((x, y + cell_height, x + cell_width, y + cell_height + label_height), fill=(12, 16, 23))
    draw.text((x + 20, y + cell_height + 10), label, fill=(212, 181, 105), font=label_font)

options.output.parent.mkdir(parents=True, exist_ok=True)
sheet.save(options.output, quality=95)
print(options.output.resolve())
