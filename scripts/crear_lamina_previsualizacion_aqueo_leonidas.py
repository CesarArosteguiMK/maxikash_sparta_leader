"""Crea una lámina de la previsualización QA del Áqueo sobre Leónidas."""

from __future__ import annotations

import json
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
PREVIEW = ROOT / "storage" / "leonidas-helmet-designs" / "aqueo-fit-preview-v1"
REPORT = PREVIEW / "aqueo-fit-preview-v1-report.json"
OUTPUT = PREVIEW / "aqueo-on-leonidas-preview-v1-review.png"
VIEWS = (
    ("01-full-front.png", "CUERPO COMPLETO"),
    ("02-head-front.png", "FRENTE"),
    ("03-head-three-quarter.png", "TRES CUARTOS"),
    ("04-head-profile.png", "PERFIL"),
)


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    name = "segoeuib.ttf" if bold else "segoeui.ttf"
    path = Path("C:/Windows/Fonts") / name
    return ImageFont.truetype(str(path), size) if path.exists() else ImageFont.load_default()


data = json.loads(REPORT.read_text(encoding="utf-8"))
cell_w = 720
cell_h = 820
label_h = 56
header_h = 176
sheet = Image.new("RGB", (cell_w * 2, header_h + (cell_h + label_h) * 2), (14, 20, 29))
draw = ImageDraw.Draw(sheet)
draw.text((40, 25), "ÁQUEO OSCURO SOBRE LEÓNIDAS", font=font(38, True), fill=(237, 241, 246))
draw.text(
    (40, 82),
    "PREVIEW QA · candidato v7 legado · no integrado en producción",
    font=font(23),
    fill=(181, 197, 217),
)
draw.text(
    (40, 125),
    f"Escala uniforme por ancho aprobado: {data['uniformScaleFromApprovedDomeWidth']:.6f} · contrato aqueo_oscuro:v1",
    font=font(20),
    fill=(226, 180, 78),
)

for index, (filename, label) in enumerate(VIEWS):
    image = Image.open(PREVIEW / filename).convert("RGB")
    image.thumbnail((cell_w, cell_h), Image.Resampling.LANCZOS)
    canvas = Image.new("RGB", (cell_w, cell_h), (37, 37, 38))
    canvas.paste(image, ((cell_w - image.width) // 2, (cell_h - image.height) // 2))
    col = index % 2
    row = index // 2
    x = col * cell_w
    y = header_h + row * (cell_h + label_h)
    sheet.paste(canvas, (x, y))
    draw.rectangle((x, y + cell_h, x + cell_w, y + cell_h + label_h), fill=(24, 33, 47))
    text_box = draw.textbbox((0, 0), label, font=font(21, True))
    text_width = text_box[2] - text_box[0]
    draw.text(
        (x + (cell_w - text_width) / 2, y + cell_h + 13),
        label,
        font=font(21, True),
        fill=(232, 236, 242),
    )

sheet.save(OUTPUT, quality=95)
print(OUTPUT)
