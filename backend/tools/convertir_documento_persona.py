import argparse
import json
import os
import re
import sys
import tempfile
from pathlib import Path

import fitz
from docx import Document
from docx.shared import Inches


def safe_name(value: str) -> str:
    value = re.sub(r"[^A-Za-z0-9._-]+", "_", value.strip())
    return value.strip("._-") or "documento"


def render_pdf_to_jpgs(input_paths, out_dir: Path):
    out_dir.mkdir(parents=True, exist_ok=True)
    output_files = []
    for input_path in input_paths:
        src = Path(input_path)
        doc = fitz.open(str(src))
        base = safe_name(src.stem)
        for page_index in range(doc.page_count):
            page = doc.load_page(page_index)
            pix = page.get_pixmap(matrix=fitz.Matrix(2, 2), alpha=False)
            suffix = f"_p{page_index + 1:02d}" if doc.page_count > 1 or len(input_paths) > 1 else ""
            out_file = out_dir / f"{base}{suffix}.jpg"
            pix.save(str(out_file))
            output_files.append(str(out_file))
        doc.close()
    return output_files


def build_docx_from_pdfs(input_paths, out_file: Path):
    out_file.parent.mkdir(parents=True, exist_ok=True)
    document = Document()
    section = document.sections[0]
    section.top_margin = Inches(0.35)
    section.bottom_margin = Inches(0.35)
    section.left_margin = Inches(0.35)
    section.right_margin = Inches(0.35)
    max_width = section.page_width - section.left_margin - section.right_margin

    tmp_dir = Path(tempfile.mkdtemp(prefix="doc_persona_word_"))
    try:
        first_page = True
        for input_path in input_paths:
            src = Path(input_path)
            pdf = fitz.open(str(src))
            for page_index in range(pdf.page_count):
                if not first_page:
                    document.add_page_break()
                first_page = False
                page = pdf.load_page(page_index)
                pix = page.get_pixmap(matrix=fitz.Matrix(2, 2), alpha=False)
                img_file = tmp_dir / f"{safe_name(src.stem)}_{page_index + 1:03d}.jpg"
                pix.save(str(img_file))
                document.add_picture(str(img_file), width=max_width)
            pdf.close()
        document.save(str(out_file))
    finally:
        for child in tmp_dir.glob("*"):
            try:
                child.unlink()
            except OSError:
                pass
        try:
            tmp_dir.rmdir()
        except OSError:
            pass
    return [str(out_file)]


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--format", choices=["jpg", "word"], required=True)
    parser.add_argument("--out", required=True)
    parser.add_argument("--input", action="append", required=True)
    args = parser.parse_args()

    inputs = [str(Path(p)) for p in args.input]
    for path in inputs:
        if not os.path.isfile(path):
            raise FileNotFoundError(path)

    if args.format == "jpg":
        files = render_pdf_to_jpgs(inputs, Path(args.out))
    else:
        files = build_docx_from_pdfs(inputs, Path(args.out))

    print(json.dumps({"ok": True, "files": files}, ensure_ascii=False))


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(json.dumps({"ok": False, "error": str(exc)}, ensure_ascii=False), file=sys.stderr)
        sys.exit(1)
