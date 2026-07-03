#!/usr/bin/env python
"""Render a PDF report for the manual-review recheck pass."""
from __future__ import annotations

import json
from collections import Counter, defaultdict
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, Iterable, List

from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    KeepTogether,
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path("output/pdf/revision_manual_recheck_v2_v1_20260702_run3")
RECHECK = ROOT / "revision_manual_recheck_results_after_acta_rescue.json"
CROSS = ROOT / "cross_revision_recheck_after_acta_rescue.json"
OUT = ROOT / "revision_manual_recheck_v1_v2_report.pdf"


def ptxt(value: Any) -> str:
    if value is None:
        return ""
    text = str(value).replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
    return text


def stats(values: Iterable[int]) -> Dict[str, Any]:
    vals = sorted(int(v) for v in values if isinstance(v, (int, float)))
    if not vals:
        return {"count": 0, "avg": "", "median": "", "p95": "", "max": ""}
    idx = max(0, min(len(vals) - 1, int(round((len(vals) - 1) * 0.95))))
    return {
        "count": len(vals),
        "avg": round(sum(vals) / len(vals), 2),
        "median": vals[len(vals) // 2],
        "p95": vals[idx],
        "max": vals[-1],
    }


def add_table(story: List[Any], rows: List[List[Any]], widths: List[float]) -> None:
    table = Table(rows, colWidths=widths, repeatRows=1)
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#1f2d4d")),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
                ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                ("FONTSIZE", (0, 0), (-1, -1), 8),
                ("GRID", (0, 0), (-1, -1), 0.25, colors.HexColor("#c9ced8")),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, colors.HexColor("#f6f8fb")]),
                ("LEFTPADDING", (0, 0), (-1, -1), 5),
                ("RIGHTPADDING", (0, 0), (-1, -1), 5),
                ("TOPPADDING", (0, 0), (-1, -1), 4),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
            ]
        )
    )
    story.append(table)
    story.append(Spacer(1, 0.14 * inch))


def main() -> None:
    recheck = json.loads(RECHECK.read_text(encoding="utf-8"))
    cross = json.loads(CROSS.read_text(encoding="utf-8"))

    styles = getSampleStyleSheet()
    title = ParagraphStyle(
        "TitleCustom",
        parent=styles["Title"],
        fontName="Helvetica-Bold",
        fontSize=18,
        leading=22,
        textColor=colors.HexColor("#1f2d4d"),
        alignment=TA_LEFT,
        spaceAfter=10,
    )
    h2 = ParagraphStyle(
        "H2",
        parent=styles["Heading2"],
        fontName="Helvetica-Bold",
        fontSize=12,
        leading=15,
        textColor=colors.HexColor("#253858"),
        spaceBefore=8,
        spaceAfter=6,
    )
    body = ParagraphStyle(
        "BodyCustom",
        parent=styles["BodyText"],
        fontSize=9,
        leading=12,
        spaceAfter=6,
    )
    small = ParagraphStyle("Small", parent=body, fontSize=8, leading=10)

    doc = SimpleDocTemplate(
        str(OUT),
        pagesize=letter,
        rightMargin=0.45 * inch,
        leftMargin=0.45 * inch,
        topMargin=0.45 * inch,
        bottomMargin=0.45 * inch,
    )
    story: List[Any] = []

    story.append(Paragraph("Revision manual - recheck V1/V2", title))
    story.append(
        Paragraph(
            "Generado: "
            + datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            + ". Segunda pasada sobre documentos que antes quedaron en revision manual.",
            body,
        )
    )

    outcome_counts = Counter(row.get("outcome") for row in recheck)
    cross_counts = Counter(row.get("new_dictamen") for row in cross)
    story.append(Paragraph("Resumen ejecutivo", h2))
    add_table(
        story,
        [
            ["Metrica", "Resultado"],
            ["Documentos re-evaluados", str(len(recheck))],
            ["Carga rapida despues del ajuste", ptxt(dict(outcome_counts))],
            ["Cruce final anterior", ptxt(dict(cross_counts))],
            ["Hallazgo clave", "Las actas ya no quedan pendientes por falta de lectura; V1 rescata cuando V2 se confunde o falla."],
            ["Fuera de alcance", "77 FAD/contratos no pertenecen al motor de los 10 documentos del candidato."],
            ["Revision real pendiente", "1 NSS ilegible/invalido dentro del alcance documental."],
        ],
        [2.1 * inch, 5.0 * inch],
    )

    by_type: Dict[str, List[Dict[str, Any]]] = defaultdict(list)
    for row in recheck:
        by_type[(row.get("item") or {}).get("doc_type") or ""] .append(row)

    story.append(Paragraph("Carga rapida por tipo", h2))
    rows = [["Tipo", "Total", "Aceptado", "Revision", "Rechazado", "Avg ms", "Mediana", "P95", "Max"]]
    for doc_type, rows_for_type in sorted(by_type.items()):
        c = Counter(row.get("outcome") for row in rows_for_type)
        timing = stats(row.get("elapsed_ms") for row in rows_for_type)
        rows.append(
            [
                ptxt(doc_type),
                str(len(rows_for_type)),
                str(c.get("aceptado", 0)),
                str(c.get("revision", 0)),
                str(c.get("rechazado", 0)),
                str(timing["avg"]),
                str(timing["median"]),
                str(timing["p95"]),
                str(timing["max"]),
            ]
        )
    add_table(story, rows, [1.2 * inch, 0.55 * inch, 0.65 * inch, 0.65 * inch, 0.65 * inch, 0.65 * inch, 0.65 * inch, 0.55 * inch, 0.55 * inch])

    story.append(Paragraph("Cruce final recalculado", h2))
    cross_rows = [["Candidato", "Antes", "Ahora", "Checks", "Motivo"]]
    for row in cross:
        cross_rows.append(
            [
                Paragraph(ptxt(row.get("candidate")), small),
                ptxt(row.get("old_dictamen")),
                ptxt(row.get("new_dictamen")),
                ptxt(row.get("checks")),
                Paragraph(ptxt("; ".join(row.get("alertas") or []) or "Sin alertas"), small),
            ]
        )
    add_table(story, cross_rows, [2.0 * inch, 1.0 * inch, 1.0 * inch, 0.7 * inch, 2.4 * inch])

    story.append(PageBreak())
    story.append(Paragraph("Casos explicados", h2))
    story.append(
        Paragraph(
            "SERRANO MIRANDA JOSE LUIS: el PDF de acta trae pagina 1 con acta correcta y pagina 2 con solicitud interna. "
            "Antes V2 clasificaba el PDF completo como solicitud y rechazaba. Ahora se rescata con V1, se usa el acta, "
            "queda aprobado y se conserva la marca documento_mixto.",
            body,
        )
    )
    story.append(
        Paragraph(
            "DIAZ CARRENO MARIA DE LOURDES: el acta leida dice MARIA DE LOURDES CARRENO RODRIGUEZ. "
            "El rechazo del cruce final es correcto porque el nombre no coincide con el candidato registrado.",
            body,
        )
    )
    story.append(
        Paragraph(
            "DOMINGUEZ MARTINEZ ALEJANDRO / NSS: el documento visible muestra NSS 4800-85-0557, que deja solo 10 digitos. "
            "No debe auto-aceptarse como NSS mexicano de 11 digitos.",
            body,
        )
    )
    story.append(
        Paragraph(
            "Contratos/FAD: los 77 no pertenecen al motor de los 10 documentos del candidato. El endpoint probado busca la seccion "
            "Informacion de Ingresos para otro flujo, por eso no debe contaminar el dictamen documental del expediente.",
            body,
        )
    )

    story.append(Paragraph("Archivos de evidencia", h2))
    story.append(
        Paragraph(
            "Carpetas base: output/pdf/revision_manual_recheck_v2_v1_20260702_run3 y tmp/pdfs/recheck_cases.",
            body,
        )
    )
    add_table(
        story,
        [
            ["Archivo", "Uso"],
            [ptxt(RECHECK.name), "Resultados de la segunda pasada con rescate de acta."],
            [ptxt(CROSS.name), "Cruce final recalculado despues del rescate."],
            ["SERRANO_MIRANDA_JOSE_LUIS_ACTA_page1.png", "Pagina 1: acta correcta."],
            ["SERRANO_MIRANDA_JOSE_LUIS_ACTA_page2.png", "Pagina 2: solicitud interna, confirma PDF mixto."],
            ["DIAZ_CARRENO_MARIA_DE_LOURDES_ACTA_page1.png", "Acta con nombre distinto al candidato."],
        ],
        [3.5 * inch, 3.6 * inch],
    )

    story.append(KeepTogether([Paragraph("Conclusion", h2), Paragraph(
        "El comportamiento correcto queda asi: V2 lee primero; si V2 no puede, falla o se confunde por PDF mixto, V1 intenta rescatar evidencia. "
        "El cruce final usa la mejor lectura disponible, pero mantiene rechazos reales cuando el nombre o el documento no corresponde.",
        body,
    )]))

    doc.build(story)
    print(OUT)


if __name__ == "__main__":
    main()
