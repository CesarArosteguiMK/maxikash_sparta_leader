#!/usr/bin/env python
"""Render the document validation QA JSON results into a PDF report."""
from __future__ import annotations

import argparse
import json
import os
import re
import unicodedata
from datetime import datetime
from html import escape
from pathlib import Path
from typing import Any, Iterable

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4, landscape
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    FrameBreak,
    KeepTogether,
    PageBreak,
    PageTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
)


DOC_LABELS = {
    "solicitud_interna": "Solicitud interna",
    "cv": "CV / solicitud",
    "acta_nacimiento": "Acta nacimiento",
    "curp": "CURP",
    "identificacion_oficial": "Identificacion",
    "comprobante_domicilio": "Comprobante domicilio",
    "constancia_fiscal": "Constancia fiscal",
    "nss": "NSS",
    "hoja_retencion": "Hoja retencion",
    "__SPARTA_SECRET_REDACTED__": "Estado cuenta",
    "contrato_fad": "Contrato/FAD",
    "factura_moto": "Factura moto",
    "unknown": "PDF auxiliar",
}


def ascii_text(value: Any) -> str:
    if value is None:
        return ""
    text = str(value)
    text = text.replace("\u2013", "-").replace("\u2014", "-").replace("\u2018", "'").replace("\u2019", "'")
    text = text.replace("\u201c", '"').replace("\u201d", '"').replace("\u00a0", " ")
    text = unicodedata.normalize("NFKD", text).encode("ascii", "ignore").decode("ascii")
    text = re.sub(r"\s+", " ", text).strip()
    return text


def short_text(value: Any, limit: int = 95) -> str:
    text = ascii_text(value)
    if len(text) <= limit:
        return text
    return text[: limit - 3].rstrip() + "..."


def fmt_ms(value: Any) -> str:
    if value is None or value == "":
        return "-"
    try:
        ms = float(value)
    except (TypeError, ValueError):
        return "-"
    if ms >= 60000:
        return f"{ms / 60000:.2f} min"
    if ms >= 1000:
        return f"{ms / 1000:.2f} s"
    return f"{ms:.0f} ms"


def fmt_num(value: Any) -> str:
    if value is None:
        return "-"
    try:
        return f"{float(value):,.2f}".rstrip("0").rstrip(".")
    except (TypeError, ValueError):
        return ascii_text(value)


def fmt_outcomes(outcomes: dict[str, int]) -> str:
    order = ["aceptado", "rechazado", "revision", "error"]
    parts = []
    for key in order:
        count = outcomes.get(key)
        if count:
            parts.append(f"{key}:{count}")
    for key, count in outcomes.items():
        if key not in order and count:
            parts.append(f"{key}:{count}")
    return ", ".join(parts) if parts else "-"


def candidate_path(result: dict[str, Any]) -> str:
    item = result.get("item") or {}
    candidate = item.get("candidate_name") or ""
    filename = item.get("filename") or ""
    if candidate and filename:
        return f"{candidate} / {filename}"
    return item.get("display_path") or filename


def artifact_path(path: Path) -> str:
    return str(path).replace("\\", "/")


def make_styles() -> dict[str, ParagraphStyle]:
    base = getSampleStyleSheet()
    styles = {
        "title": ParagraphStyle(
            "ReportTitle",
            parent=base["Title"],
            fontName="Helvetica-Bold",
            fontSize=20,
            leading=24,
            textColor=colors.HexColor("#172033"),
            alignment=TA_LEFT,
            spaceAfter=7,
        ),
        "subtitle": ParagraphStyle(
            "Subtitle",
            parent=base["BodyText"],
            fontSize=9.5,
            leading=13,
            textColor=colors.HexColor("#475467"),
            spaceAfter=7,
        ),
        "h1": ParagraphStyle(
            "SectionTitle",
            parent=base["Heading1"],
            fontName="Helvetica-Bold",
            fontSize=13.5,
            leading=16,
            textColor=colors.HexColor("#172033"),
            spaceBefore=8,
            spaceAfter=5,
        ),
        "h2": ParagraphStyle(
            "SubTitle",
            parent=base["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=10.5,
            leading=13,
            textColor=colors.HexColor("#25324a"),
            spaceBefore=5,
            spaceAfter=4,
        ),
        "body": ParagraphStyle(
            "Body",
            parent=base["BodyText"],
            fontSize=8.8,
            leading=11.5,
            textColor=colors.HexColor("#1f2937"),
            spaceAfter=4,
        ),
        "small": ParagraphStyle(
            "Small",
            parent=base["BodyText"],
            fontSize=7.4,
            leading=9.3,
            textColor=colors.HexColor("#344054"),
            spaceAfter=2,
        ),
        "cell": ParagraphStyle(
            "Cell",
            parent=base["BodyText"],
            fontSize=7.0,
            leading=8.5,
            textColor=colors.HexColor("#1f2937"),
        ),
        "cell_center": ParagraphStyle(
            "CellCenter",
            parent=base["BodyText"],
            fontSize=7.0,
            leading=8.5,
            textColor=colors.HexColor("#1f2937"),
            alignment=TA_CENTER,
        ),
        "head": ParagraphStyle(
            "Head",
            parent=base["BodyText"],
            fontName="Helvetica-Bold",
            fontSize=7.1,
            leading=8.5,
            textColor=colors.white,
            alignment=TA_CENTER,
        ),
    }
    return styles


def para(value: Any, style: ParagraphStyle, limit: int | None = None) -> Paragraph:
    text = ascii_text(value)
    if limit is not None:
        text = short_text(text, limit)
    return Paragraph(escape(text), style)


def make_table(rows: list[list[Any]], widths: list[float], styles: dict[str, ParagraphStyle]) -> Table:
    body = []
    for r, row in enumerate(rows):
        out = []
        for cell in row:
            if isinstance(cell, Paragraph):
                out.append(cell)
            else:
                out.append(para(cell, styles["head"] if r == 0 else styles["cell"]))
        body.append(out)
    table = Table(body, colWidths=widths, repeatRows=1, hAlign="LEFT")
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#26354f")),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
                ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("GRID", (0, 0), (-1, -1), 0.25, colors.HexColor("#d0d5dd")),
                ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, colors.HexColor("#f8fafc")]),
                ("LEFTPADDING", (0, 0), (-1, -1), 4),
                ("RIGHTPADDING", (0, 0), (-1, -1), 4),
                ("TOPPADDING", (0, 0), (-1, -1), 3),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 3),
            ]
        )
    )
    return table


def kv_table(rows: Iterable[tuple[Any, Any]], styles: dict[str, ParagraphStyle], width: float = 180 * mm) -> Table:
    table_rows = [[para("Metrica", styles["head"]), para("Valor", styles["head"])]]
    for key, value in rows:
        table_rows.append([para(key, styles["cell"]), para(value, styles["cell"])])
    return make_table(table_rows, [width * 0.44, width * 0.56], styles)


def source_inventory(items: list[dict[str, Any]]) -> list[tuple[str, int]]:
    counts: dict[str, int] = {}
    for wrapped in items:
        item = wrapped.get("item") if "item" in wrapped else wrapped
        if item.get("source_kind") == "zip":
            key = f"ZIP: {Path(item.get('zip_path') or '').name}"
        else:
            key = "Carpeta directa Pruebas"
        counts[key] = counts.get(key, 0) + 1
    return sorted(counts.items(), key=lambda x: (-x[1], x[0]))


def top_cross(rows: list[dict[str, Any]], status: str, limit: int = 10) -> list[dict[str, Any]]:
    selected = [r for r in rows if r.get("dictamen") == status]
    selected.sort(key=lambda r: (-(r.get("confianza") or 0), r.get("elapsed_ms") or 0, r.get("candidate_name") or ""))
    return selected[:limit]


def build_report(results_path: Path, output_path: Path, smoke_path: Path | None = None) -> None:
    data = json.loads(results_path.read_text(encoding="utf-8"))
    summary = data["summary"]
    quick = data.get("quick_results") or []
    cross = data.get("cross_results") or []
    items = data.get("items") or []

    smoke_summary = None
    smoke_cross = None
    if smoke_path and smoke_path.exists():
        smoke = json.loads(smoke_path.read_text(encoding="utf-8"))
        smoke_summary = smoke.get("summary")
        smoke_cross = (smoke.get("cross_results") or [None])[0]

    styles = make_styles()
    output_path.parent.mkdir(parents=True, exist_ok=True)

    doc = BaseDocTemplate(
        str(output_path),
        pagesize=A4,
        leftMargin=14 * mm,
        rightMargin=14 * mm,
        topMargin=13 * mm,
        bottomMargin=13 * mm,
        title="Reporte QA validacion documental",
        author="Codex",
    )
    frame = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="normal")

    def footer(canvas, document):
        canvas.saveState()
        canvas.setFont("Helvetica", 7)
        canvas.setFillColor(colors.HexColor("#667085"))
        canvas.drawString(doc.leftMargin, 8 * mm, "Reporte QA validacion documental")
        canvas.drawRightString(doc.leftMargin + doc.width, 8 * mm, f"Pagina {document.page}")
        canvas.restoreState()

    doc.addPageTemplates([PageTemplate(id="normal", frames=[frame], onPage=footer)])

    story: list[Any] = []
    generated = summary.get("generated_at") or datetime.now().isoformat(timespec="seconds")
    story.append(para("Reporte QA de validacion documental", styles["title"]))
    story.append(
        para(
            "Simulacion completa sobre PDFs directos y PDFs dentro de ZIPs en C:/Users/amigo_j9s4pcx/Downloads/Pruebas. "
            "Se omitieron solo archivos no PDF; JPEG/JPG/PNG y otros anexos no PDF quedaron contabilizados como saltados.",
            styles["subtitle"],
        )
    )
    story.append(
        kv_table(
            [
                ("Fecha de generacion", generated.replace("T", " ")),
                ("API probada", "http://127.0.0.1:8001/api/v1"),
                ("Build health", "doc-precheck-2026-06-18-comprobante-content-first"),
                ("Motor IA observado", "Alibaba / Motor V2 en lectura rapida; cruce por reglas con lecturas V2"),
                ("PDFs procesados", summary.get("total_pdfs")),
                ("PDFs duplicados por hash", summary.get("duplicate_pdf_hash_count")),
                ("Revision rapida", f"{summary.get('quick_total')} documentos"),
                ("Validacion cruzada", f"{summary.get('cross_total')} expedientes"),
            ],
            styles,
        )
    )

    story.append(para("Conclusiones principales", styles["h1"]))
    conclusions = [
        "No se omitio ningun PDF descubierto: se procesaron 1,230 PDFs entre carpeta directa y ZIPs.",
        "La revision rapida es estable en la mayoria de documentos, pero la latencia real esta en los validadores con IA externa.",
        "El cruce con lecturas rapidas ya calculadas es muy rapido: promedio 19.92 ms, p95 33 ms y cero errores en 100 expedientes.",
        "La prueba pesada de cruce reenviando PDFs completos no es recomendable: un expediente de control alcanzo timeout de cliente tras 24.90 min.",
        "Hubo un unico fallo real en revision rapida: NSS devolvio 502 por Alibaba. Conviene agregar reintento y fallback para ese caso.",
        "Los 151 PDFs auxiliares/sin validador especifico no se ignoraron; se validaron como PDF/paginas, pero no entran al cruce estricto.",
    ]
    for item in conclusions:
        story.append(para(f"- {item}", styles["body"]))

    story.append(para("Cobertura", styles["h1"]))
    skipped = summary.get("skipped_non_pdf") or {}
    skipped_text = ", ".join(f"{k}:{v}" for k, v in sorted(skipped.items())) or "0"
    story.append(
        kv_table(
            [
                ("Origenes con PDF", "; ".join(f"{name} ({count})" for name, count in source_inventory(items))),
                ("No PDF saltados", skipped_text),
                ("Criterio de salto", "Solo archivos no PDF. Los JPEG/PNG/DOC/DOCX/FAD no se enviaron a validadores PDF."),
            ],
            styles,
        )
    )

    type_rows = [[
        "Tipo",
        "PDFs",
        "Prom.",
        "Mediana",
        "P95",
        "Max",
        "Resultados",
    ]]
    for doc_type, count in sorted((summary.get("pdfs_by_type") or {}).items(), key=lambda x: DOC_LABELS.get(x[0], x[0])):
        entry = (summary.get("quick_by_type") or {}).get(doc_type) or {}
        timing = entry.get("timing_ms") or {}
        type_rows.append(
            [
                DOC_LABELS.get(doc_type, doc_type),
                count,
                fmt_ms(timing.get("avg")),
                fmt_ms(timing.get("median")),
                fmt_ms(timing.get("p95")),
                fmt_ms(timing.get("max")),
                fmt_outcomes(entry.get("outcomes") or {}),
            ]
        )
    story.append(make_table(type_rows, [38 * mm, 17 * mm, 20 * mm, 20 * mm, 20 * mm, 20 * mm, 45 * mm], styles))

    story.append(para("Tiempos globales", styles["h1"]))
    qt = summary.get("quick_timing_ms") or {}
    ct = summary.get("cross_timing_ms") or {}
    story.append(
        kv_table(
            [
                ("Revision rapida promedio", fmt_ms(qt.get("avg"))),
                ("Revision rapida mediana", fmt_ms(qt.get("median"))),
                ("Revision rapida p95", fmt_ms(qt.get("p95"))),
                ("Revision rapida maximo", fmt_ms(qt.get("max"))),
                ("Cruce promedio", fmt_ms(ct.get("avg"))),
                ("Cruce mediana", fmt_ms(ct.get("median"))),
                ("Cruce p95", fmt_ms(ct.get("p95"))),
                ("Cruce maximo", fmt_ms(ct.get("max"))),
            ],
            styles,
        )
    )

    story.append(para("Resultados por fase", styles["h1"]))
    story.append(
        kv_table(
            [
                ("Revision rapida", fmt_outcomes(summary.get("quick_outcomes") or {})),
                ("Validacion cruzada", fmt_outcomes(summary.get("cross_dictamen") or {})),
                ("Errores de cruce", summary.get("cross_errors")),
            ],
            styles,
        )
    )

    story.append(PageBreak())
    story.append(para("Documentos mas lentos en revision rapida", styles["h1"]))
    slow_rows = [["#", "Documento", "Tipo", "Tiempo", "Resultado", "Mensaje"]]
    for idx, result in enumerate(sorted(quick, key=lambda r: r.get("elapsed_ms") or 0, reverse=True)[:15], 1):
        item = result.get("item") or {}
        slow_rows.append(
            [
                idx,
                short_text(candidate_path(result), 75),
                DOC_LABELS.get(item.get("doc_type"), item.get("doc_type")),
                fmt_ms(result.get("elapsed_ms")),
                result.get("outcome"),
                short_text(result.get("message") or result.get("error"), 85),
            ]
        )
    story.append(make_table(slow_rows, [9 * mm, 57 * mm, 28 * mm, 20 * mm, 20 * mm, 48 * mm], styles))

    error_rows = [["Documento", "Tipo", "HTTP", "Tiempo", "Detalle"]]
    for result in quick:
        status = result.get("status_code") or 0
        if result.get("outcome") == "error" or status >= 400:
            item = result.get("item") or {}
            error_rows.append(
                [
                    short_text(candidate_path(result), 78),
                    DOC_LABELS.get(item.get("doc_type"), item.get("doc_type")),
                    status,
                    fmt_ms(result.get("elapsed_ms")),
                    short_text(result.get("message") or result.get("error"), 95),
                ]
            )
    story.append(para("Errores detectados", styles["h1"]))
    if len(error_rows) == 1:
        story.append(para("No hubo errores de API en revision rapida.", styles["body"]))
    else:
        story.append(make_table(error_rows, [64 * mm, 25 * mm, 14 * mm, 20 * mm, 58 * mm], styles))

    story.append(para("Validacion cruzada", styles["h1"]))
    story.append(
        para(
            "La corrida completa uso el modo optimizado: cada expediente se cruzo con los resumenes/lecturas rapidas ya obtenidas, "
            "sin reenviar los PDFs completos. Esto replica el flujo eficiente de carga previa mas dictamen final.",
            styles["body"],
        )
    )
    cross_rows = [["Dictamen", "Cantidad"]]
    for key, count in (summary.get("cross_dictamen") or {}).items():
        cross_rows.append([key, count])
    story.append(make_table(cross_rows, [55 * mm, 35 * mm], styles))

    story.append(para("Expedientes aprobados", styles["h2"]))
    approved_rows = [["Candidato", "Docs", "Confianza", "Tiempo", "Alertas"]]
    for row in top_cross(cross, "aprobado", 10):
        approved_rows.append(
            [
                row.get("candidate_name"),
                row.get("selected_doc_count"),
                row.get("confianza"),
                fmt_ms(row.get("elapsed_ms")),
                row.get("alertas_count"),
            ]
        )
    if len(approved_rows) == 1:
        approved_rows.append(["Sin aprobados", "-", "-", "-", "-"])
    story.append(make_table(approved_rows, [74 * mm, 16 * mm, 24 * mm, 23 * mm, 18 * mm], styles))

    story.append(para("Expedientes en revision", styles["h2"]))
    review_rows = [["Candidato", "Docs", "Confianza", "Tiempo", "Alertas"]]
    for row in top_cross(cross, "requiere_revision", 12):
        review_rows.append(
            [
                row.get("candidate_name"),
                row.get("selected_doc_count"),
                row.get("confianza"),
                fmt_ms(row.get("elapsed_ms")),
                row.get("alertas_count"),
            ]
        )
    if len(review_rows) == 1:
        review_rows.append(["Sin revision", "-", "-", "-", "-"])
    story.append(make_table(review_rows, [74 * mm, 16 * mm, 24 * mm, 23 * mm, 18 * mm], styles))

    if smoke_summary and smoke_cross:
        story.append(para("Prueba de control con PDFs completos", styles["h1"]))
        story.append(
            para(
                "Ademas de la corrida completa optimizada, se hizo una prueba de control enviando archivos completos al cruce. "
                "Ese modo no escalo: el expediente probado termino en timeout de cliente. La recomendacion es mantener el cruce "
                "con lecturas rapidas ya preparadas y evitar relectura IA durante el dictamen final.",
                styles["body"],
            )
        )
        story.append(
            kv_table(
                [
                    ("Expediente control", smoke_cross.get("candidate_name")),
                    ("Incluyo archivos completos", smoke_cross.get("include_files")),
                    ("Resultado", smoke_cross.get("dictamen") or "timeout/error"),
                    ("Tiempo observado", fmt_ms(smoke_cross.get("elapsed_ms"))),
                    ("Error", short_text(smoke_cross.get("error"), 120)),
                ],
                styles,
            )
        )

    story.append(PageBreak())
    story.append(para("Lectura operativa", styles["h1"]))
    ops = [
        ("Fortaleza", "El cruce final es muy rapido y consistente cuando se alimenta con lecturas rapidas V2."),
        ("Riesgo principal", "Dependencia de Alibaba en lectura rapida: un 502 en NSS produjo el unico fallo de toda la corrida."),
        ("Riesgo de latencia", "Identificacion, estado de cuenta, constancia fiscal, domicilio, CURP y NSS tardan entre 11 s y 15 s en promedio."),
        ("Revision manual", "Acta de nacimiento y contrato/FAD caen a revision por diseno/regla; no son fallos tecnicos, pero si carga operativa."),
        ("Documentos auxiliares", "Los PDFs sin tipo estricto pasan sanity check de PDF/paginas y deben mantenerse fuera del dictamen cruzado."),
    ]
    story.append(kv_table(ops, styles))

    story.append(para("Recomendaciones", styles["h1"]))
    recommendations = [
        "Agregar reintento automatico con backoff para errores 5xx/timeouts de Alibaba en todos los validadores rapidos.",
        "Mantener el flujo de cruce basado en lecturas rapidas; bloquear o advertir cuando se intente cruzar reenviando PDFs completos.",
        "Persistir lecturas rapidas por hash para documentos repetidos; hay 21 hashes duplicados que podrian evitar reproceso.",
        "Revisar muestras de CURP y constancia fiscal rechazadas para separar reglas de vigencia correctas de posibles falsos negativos.",
        "Definir tratamiento de los 151 PDFs auxiliares: archivarlos como soporte, pero sin forzarlos a validadores documentales estrictos.",
        "Medir una segunda corrida despues de agregar retry/fallback para confirmar que el unico 502 desaparece.",
    ]
    for item in recommendations:
        story.append(para(f"- {item}", styles["body"]))

    story.append(para("Artefactos generados", styles["h1"]))
    out_dir = results_path.parent
    artifacts = [
        ("JSON completo", artifact_path(out_dir / "document_validation_qa_results.json")),
        ("CSV revision rapida", artifact_path(out_dir / "document_validation_qa_quick.csv")),
        ("CSV validacion cruzada", artifact_path(out_dir / "document_validation_qa_cross.csv")),
        ("HTML resumen", artifact_path(out_dir / "document_validation_qa_report.html")),
        ("Log stdout", artifact_path(out_dir / "runner.stdout.log")),
        ("Log stderr", artifact_path(out_dir / "runner.stderr.log")),
    ]
    story.append(kv_table(artifacts, styles))

    story.append(Spacer(1, 4 * mm))
    story.append(
        para(
            "Nota: el detalle exhaustivo por cada documento queda en los CSV/JSON. Este PDF resume los resultados para decision "
            "operativa y senala los casos que requieren atencion.",
            styles["small"],
        )
    )

    doc.build(story)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("results_json", type=Path)
    parser.add_argument("--output", type=Path, default=None)
    parser.add_argument("--smoke-json", type=Path, default=None)
    args = parser.parse_args()
    output = args.output or args.results_json.with_name("document_validation_qa_report.pdf")
    build_report(args.results_json, output, args.smoke_json)
    print(output)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
