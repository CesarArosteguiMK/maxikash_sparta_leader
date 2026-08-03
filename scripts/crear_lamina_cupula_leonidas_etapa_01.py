"""Crea la lámina de revisión de la cúpula de Leónidas (etapa 01)."""

from __future__ import annotations

import json
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
STAGE_ROOT = ROOT / "storage" / "leonidas-helmet-designs" / "stage-01-dome"
RENDER_ROOT = STAGE_ROOT / "renders"
REPORT_PATH = STAGE_ROOT / "leonidas-helmet-dome-stage-01-report.json"
OUTPUT_PATH = STAGE_ROOT / "leonidas-helmet-dome-stage-01-review.png"

VIEWS = [
    ("01-front.png", "FRENTE"),
    ("02-left.png", "PERFIL IZQUIERDO"),
    ("03-right.png", "PERFIL DERECHO"),
    ("04-back.png", "ESPALDA"),
    ("05-top.png", "PLANTA"),
    ("06-cutaway.png", "CORTE 3/4"),
]


def load_font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    filename = "segoeuib.ttf" if bold else "segoeui.ttf"
    path = Path("C:/Windows/Fonts") / filename
    if path.exists():
        return ImageFont.truetype(str(path), size=size)
    return ImageFont.load_default()


report = json.loads(REPORT_PATH.read_text(encoding="utf-8"))
shell_min = report["shell_bounds"]["min"]
shell_max = report["shell_bounds"]["max"]
width_cm = (shell_max[0] - shell_min[0]) * 100.0
depth_cm = (shell_max[1] - shell_min[1]) * 100.0
thickness_mm = report["shell_thickness"] * 1000.0

cell_size = 560
label_height = 54
header_height = 180
columns = 3
rows = 2
sheet = Image.new(
    "RGB",
    (cell_size * columns, header_height + (cell_size + label_height) * rows),
    (16, 22, 31),
)
draw = ImageDraw.Draw(sheet)
title_font = load_font(42, bold=True)
subtitle_font = load_font(23)
label_font = load_font(21, bold=True)
small_font = load_font(20)

draw.text((42, 28), "LEÓNIDAS · CASCO · ETAPA 01", font=title_font, fill=(237, 241, 246))
draw.text(
    (42, 88),
    "Cúpula neutral medida · sin máscara · sin cresta · sin integración productiva",
    font=subtitle_font,
    fill=(176, 193, 213),
)
draw.text(
    (42, 130),
    f"Exterior: {width_cm:.2f} cm ancho × {depth_cm:.2f} cm profundidad  |  pared: {thickness_mm:.1f} mm  |  simetría: eje Head",
    font=small_font,
    fill=(225, 181, 83),
)

for index, (filename, label) in enumerate(VIEWS):
    column = index % columns
    row = index // columns
    x = column * cell_size
    y = header_height + row * (cell_size + label_height)
    image = Image.open(RENDER_ROOT / filename).convert("RGB")
    image = image.resize((cell_size, cell_size), Image.Resampling.LANCZOS)
    sheet.paste(image, (x, y))
    draw.rectangle(
        (x, y + cell_size, x + cell_size, y + cell_size + label_height),
        fill=(25, 34, 48),
    )
    label_box = draw.textbbox((0, 0), label, font=label_font)
    label_width = label_box[2] - label_box[0]
    draw.text(
        (x + (cell_size - label_width) / 2, y + cell_size + 13),
        label,
        font=label_font,
        fill=(230, 235, 241),
    )

sheet.save(OUTPUT_PATH, quality=95)
print(OUTPUT_PATH)
