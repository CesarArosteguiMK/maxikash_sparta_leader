import json
import sys
import tempfile
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

sys.path.insert(0, str(Path(__file__).resolve().parent))

from app.api.routes import _crear_alibaba_ai, _doc_ai_provider_name


FAKE_NAME = "MARIO ALBERTO PRUEBAS LOPEZ"
FAKE_CURP = "PULM900101HDFRPR09"
FAKE_NSS = "12345678901"


def make_document(path: Path, title: str, lines: list[str]) -> None:
    image = Image.new("RGB", (1500, 950), "white")
    draw = ImageDraw.Draw(image)
    title_font = ImageFont.truetype("arialbd.ttf", 48)
    body_font = ImageFont.truetype("arial.ttf", 34)
    draw.rectangle((45, 45, 1455, 905), outline="#1f2937", width=4)
    draw.text((95, 95), title, fill="#111827", font=title_font)
    y = 220
    for line in lines:
        draw.text((110, y), line, fill="#111827", font=body_font)
        y += 78
    image.save(path, format="PNG")


def main() -> None:
    reader = _crear_alibaba_ai()
    if reader is None:
        raise RuntimeError("El lector documental no esta configurado")

    with tempfile.TemporaryDirectory(prefix="sparta-gemini-synthetic-") as directory:
        root = Path(directory)
        cases = [
            (root / "identificacion.png", "identificacion_oficial", "IDENTIFICACION OFICIAL", [
                f"NOMBRE: {FAKE_NAME}", f"CURP: {FAKE_CURP}", "FECHA DE NACIMIENTO: 01/01/1990",
            ]),
            (root / "solicitud.png", "solicitud_interna", "SOLICITUD INTERNA DE EMPLEO", [
                f"NOMBRE COMPLETO: {FAKE_NAME}", f"CURP: {FAKE_CURP}", f"NUMERO DE SEGURIDAD SOCIAL: {FAKE_NSS}",
            ]),
            (root / "retencion.png", "hoja_retencion", "HOJA DE RETENCION FONACOT O INFONAVIT", [
                "A QUIEN CORRESPONDA:", f"NOMBRE DEL COLABORADOR: {FAKE_NAME}", "DECLARA NO CONTAR CON ADEUDO VIGENTE.",
            ]),
        ]

        results = []
        summaries = {}
        for path, document_type, title, lines in cases:
            make_document(path, title, lines)
            result = reader.quick_verify(path.read_bytes(), path.name, document_type, FAKE_NAME)
            extraction = result.get("extraction") or {}
            summaries[document_type] = extraction
            results.append({
                "documento": document_type,
                "provider": result.get("provider"),
                "model": result.get("model"),
                "fallback": result.get("fallback_used"),
                "elapsed_ms": result.get("elapsed_ms"),
                "nombre_leido": extraction.get("nombre_completo") or extraction.get("nombre") or extraction.get("nombre_leido"),
                "curp": extraction.get("curp"),
                "nss": extraction.get("nss"),
                "resultado": extraction.get("resultado"),
                "validacion": result.get("validation") or {},
            })

        crosscheck = reader.crosscheck_expediente([
            {"key": doc_type, "label": title, "filename": path.name, "bytes": path.read_bytes(), "summary": summaries[doc_type]}
            for path, doc_type, title, _ in cases
        ], FAKE_NAME)

    print(json.dumps({
        "datos": "100% sinteticos",
        "provider_activo": _doc_ai_provider_name(),
        "resultados": results,
        "cruce": {
            "provider": crosscheck.get("provider"),
            "model": crosscheck.get("model"),
            "fallback": crosscheck.get("fallback_used"),
            "elapsed_ms": crosscheck.get("elapsed_ms"),
            "resultado": crosscheck.get("result"),
        },
    }, ensure_ascii=False))


if __name__ == "__main__":
    main()
