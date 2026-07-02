#!/usr/bin/env python
"""
QA runner for the document-verification API.

It inventories PDFs from a folder and from PDFs inside ZIP files, skips images and
other non-PDF files, runs the same quick-upload checks used by the candidate
document screen, then runs cross-check validation by expediente groups.
"""
from __future__ import annotations

import argparse
import base64
import concurrent.futures
import csv
import hashlib
import io
import json
import os
import re
import statistics
import sys
import threading
import time
import unicodedata
import zipfile
from dataclasses import dataclass, asdict
from datetime import datetime
from pathlib import Path, PurePosixPath
from typing import Any, Dict, Iterable, List, Optional, Tuple

import requests

try:
    import fitz  # PyMuPDF
    try:
        fitz.TOOLS.mupdf_display_errors(False)
    except Exception:
        pass
except Exception:  # pragma: no cover
    fitz = None


API_BASE_DEFAULT = "http://127.0.0.1:8001/api/v1"
API_KEY_DEFAULT = "sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key"

DOC_LABELS = {
    "solicitud_interna": "Solicitud interna",
    "cv": "CV o solicitud de trabajo",
    "acta_nacimiento": "Acta de nacimiento",
    "curp": "CURP",
    "identificacion_oficial": "Identificacion oficial",
    "comprobante_domicilio": "Comprobante de domicilio",
    "constancia_fiscal": "Constancia fiscal",
    "nss": "NSS",
    "hoja_retencion": "Hoja retencion FONACOT/INFONAVIT",
    "__SPARTA_SECRET_REDACTED__": "Estado de cuenta",
    "contrato_fad": "Contrato/FAD",
    "factura_moto": "Factura moto",
    "unknown": "PDF sin validador especifico",
}

FIELD_BY_DOC_TYPE = {
    "solicitud_interna": "solicitud_interna",
    "cv": "cv_solicitud",
    "acta_nacimiento": "acta_nacimiento",
    "curp": "documento_curp",
    "identificacion_oficial": "identificacion_pdf",
    "comprobante_domicilio": "comprobante_domicilio",
    "constancia_fiscal": "constancia_fiscal",
    "nss": "documento_nss",
    "hoja_retencion": "hoja_retencion",
    "__SPARTA_SECRET_REDACTED__": "__SPARTA_SECRET_REDACTED__",
}

SUMMARY_KEY_BY_DOC_TYPE = {
    "solicitud_interna": "solicitud_interna",
    "cv": "cv",
    "acta_nacimiento": "acta_nacimiento",
    "curp": "curp",
    "identificacion_oficial": "identificacion_oficial",
    "comprobante_domicilio": "comprobante_domicilio",
    "constancia_fiscal": "constancia_fiscal",
    "nss": "nss",
    "hoja_retencion": "hoja_retencion",
    "__SPARTA_SECRET_REDACTED__": "__SPARTA_SECRET_REDACTED__",
}


@dataclass
class PdfItem:
    id: str
    source_kind: str  # file | zip
    display_path: str
    filename: str
    size: int
    group_id: Optional[str]
    candidate_name: Optional[str]
    zip_path: Optional[str] = None
    zip_inner: Optional[str] = None
    file_path: Optional[str] = None
    doc_type: str = "unknown"
    classifier_reason: str = ""
    pages: Optional[int] = None
    sha256: Optional[str] = None


def normalize_text(value: str) -> str:
    value = unicodedata.normalize("NFD", value or "")
    value = "".join(ch for ch in value if unicodedata.category(ch) != "Mn")
    value = re.sub(r"[^A-Za-z0-9]+", " ", value).upper()
    return re.sub(r"\s+", " ", value).strip()


def strip_candidate_name(folder: str) -> str:
    name = re.sub(r"^\s*\d+\s*[\.-]\s*", "", folder or "").strip()
    name = re.sub(r"\([^)]*\)", "", name).strip()
    return re.sub(r"\s+", " ", name)


def first_pdf_text(pdf_bytes: bytes, max_pages: int = 2, max_chars: int = 7000) -> Tuple[str, Optional[int]]:
    if fitz is None:
        return "", None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        pages = int(doc.page_count or 0)
        chunks: List[str] = []
        for idx, page in enumerate(doc):
            if idx >= max_pages:
                break
            chunks.append(page.get_text() or "")
        doc.close()
        return ("\n".join(chunks))[:max_chars], pages
    except Exception:
        return "", None


def classify_pdf(filename: str, parent_hint: str, text_hint: str = "") -> Tuple[str, str]:
    base = normalize_text(filename.rsplit(".", 1)[0])
    parent = normalize_text(parent_hint)
    text = normalize_text(text_hint)
    hay = " ".join([base, parent, text[:5000]])

    if "VALIDACION" in base and "SAT" not in base:
        return "unknown", "archivo auxiliar de validacion, no campo documental principal"
    if "CURRICUL" in hay or re.search(r"\bCV\b", hay) or "SOLICITUD DE TRABAJO" in hay or "SOLICITUD EMPLEO" in hay:
        return "cv", "nombre/texto indica CV o solicitud de trabajo"
    if "SOLICITUD MAXIKASH" in hay or "SOLICITUD INTERNA" in hay:
        return "solicitud_interna", "nombre/texto indica solicitud interna"
    if re.search(r"\bSOLICITUD\b", base) and "EMPLEO" not in base and "TRABAJO" not in base:
        return "solicitud_interna", "nombre indica solicitud interna"
    if "ACTA" in hay and "NACIMIENTO" in hay:
        return "acta_nacimiento", "nombre/texto indica acta nacimiento"
    if "CLAVE UNICA DE REGISTRO" in hay or "RENAPO" in hay or re.search(r"\bCURP\b", base):
        return "curp", "nombre/texto indica CURP"
    if (
        "IDENTIFIC" in hay
        or "INDENTIFIC" in hay
        or re.search(r"\bINE\b", hay)
        or re.search(r"\bIFE\b", hay)
        or "CREDENCIAL PARA VOT" in hay
        or "INSTITUTO NACIONAL ELECTORAL" in hay
        or "RESIDENCIA TEMPORAL" in hay
        or "RESIDENCIA PERMANENTE" in hay
        or "PASAPORTE" in hay
        or "PASSPORT" in hay
    ):
        return "identificacion_oficial", "nombre/texto indica identificacion oficial"
    if (
        "DOMICILIO" in hay
        or "COMPROBANTE DE DOMICILIO" in hay
        or "COMISION FEDERAL DE ELECTRICIDAD" in hay
        or re.search(r"\bCFE\b", hay)
        or "TELMEX" in hay
        or "PREDIAL" in hay
        or "TOTAL A PAGAR" in hay
    ):
        return "comprobante_domicilio", "nombre/texto indica comprobante domicilio"
    if "CONSTANCIA DE SITUACION FISCAL" in hay or "CEDULA DE IDENTIFICACION FISCAL" in hay or "PORTAL DEL SAT" in hay:
        return "constancia_fiscal", "texto indica constancia fiscal"
    if "FISCAL" in base or re.search(r"\bSAT\b", base) or re.search(r"\bCSF\b", base):
        return "constancia_fiscal", "nombre indica constancia fiscal"
    if (
        re.search(r"\bNSS\b", hay)
        or "NUMERO DE SEGURIDAD SOCIAL" in hay
        or "INSTITUTO MEXICANO DEL SEGURO SOCIAL" in hay
        or re.search(r"\bIMSS\b", hay)
        or "SEMANAS COTIZADAS" in hay
    ):
        return "nss", "nombre/texto indica NSS/IMSS"
    if "RETENCION" in hay or "FONACOT" in hay or "INFONAVIT" in hay or "NO CREDITOS" in hay or "NO ADEUDO" in hay:
        return "hoja_retencion", "nombre/texto indica retencion o carta no adeudo"
    if (
        "ESTADO DE CUENTA" in hay
        or "CUENTA BANCARIA" in hay
        or re.search(r"\bCLABE\b", hay)
        or re.search(r"\bBBVA\b", hay)
        or re.search(r"\bBANORTE\b", hay)
        or re.search(r"\bSANTANDER\b", hay)
        or re.search(r"\bHSBC\b", hay)
        or re.search(r"\bSCOTIABANK\b", hay)
        or re.search(r"\bBANCO\b", hay)
    ):
        return "__SPARTA_SECRET_REDACTED__", "nombre/texto indica estado de cuenta"
    if "CONTRATO FIRMADO" in hay or re.search(r"\bFAD\b", hay):
        return "contrato_fad", "nombre indica contrato/FAD"
    if "FACTURA" in hay:
        return "factura_moto", "nombre/texto indica factura"
    return "unknown", "sin coincidencia clara"


def read_item_bytes(item: PdfItem) -> bytes:
    if item.source_kind == "file" and item.file_path:
        return Path(item.file_path).read_bytes()
    if item.source_kind == "zip" and item.zip_path and item.zip_inner:
        with zipfile.ZipFile(item.zip_path) as zf:
            return zf.read(item.zip_inner)
    raise RuntimeError(f"Cannot read item {item.id}")


def discover(root: Path) -> Tuple[List[PdfItem], Dict[str, int]]:
    items: List[PdfItem] = []
    skipped: Dict[str, int] = {}

    def count_skip(ext: str) -> None:
        skipped[ext.lower() or "<sin_ext>"] = skipped.get(ext.lower() or "<sin_ext>", 0) + 1

    # Filesystem PDFs, excluding entries inside ZIPs.
    for path in sorted(root.rglob("*")):
        if not path.is_file():
            continue
        ext = path.suffix.lower()
        if ext == ".zip":
            continue
        if ext != ".pdf":
            count_skip(ext)
            continue
        rel = str(path.relative_to(root))
        parts = path.relative_to(root).parts
        group_id = None
        candidate = None
        if len(parts) >= 2 and not parts[0].lower().endswith(".zip"):
            first = parts[0]
            if re.match(r"^\d+\s*[\.-]\s+", first) or first.lower() == "prueba candidato":
                group_id = f"fs::{first}"
                candidate = strip_candidate_name(first) if first.lower() != "prueba candidato" else "prueba candidato"
        item = PdfItem(
            id=f"fs::{rel}",
            source_kind="file",
            display_path=str(path),
            filename=path.name,
            size=path.stat().st_size,
            group_id=group_id,
            candidate_name=candidate,
            file_path=str(path),
        )
        items.append(item)

    # PDFs inside ZIP files.
    for zip_path in sorted(root.glob("*.zip")):
        try:
            with zipfile.ZipFile(zip_path) as zf:
                for info in sorted(zf.infolist(), key=lambda x: x.filename):
                    if info.is_dir():
                        continue
                    inner = info.filename.replace("\\", "/")
                    ext = PurePosixPath(inner).suffix.lower()
                    if ext != ".pdf":
                        count_skip(ext)
                        continue
                    parts = PurePosixPath(inner).parts
                    group_id = None
                    candidate = None
                    if len(parts) >= 3:
                        group_folder = parts[1]
                        group_id = f"zip::{zip_path.name}::{parts[0]}/{group_folder}"
                        candidate = strip_candidate_name(group_folder)
                    item = PdfItem(
                        id=f"zip::{zip_path.name}::{inner}",
                        source_kind="zip",
                        display_path=f"{zip_path}::{inner}",
                        filename=PurePosixPath(inner).name,
                        size=int(info.file_size or 0),
                        group_id=group_id,
                        candidate_name=candidate,
                        zip_path=str(zip_path),
                        zip_inner=inner,
                    )
                    items.append(item)
        except zipfile.BadZipFile:
            count_skip(".zip_bad")
    return items, skipped


def enrich_items(items: List[PdfItem], classify_with_text: bool = True, sample_text_unknown_only: bool = False) -> None:
    for idx, item in enumerate(items, 1):
        data = read_item_bytes(item)
        item.sha256 = hashlib.sha256(data).hexdigest()
        text = ""
        pages = None
        parent_hint = str(PurePosixPath(item.zip_inner).parent if item.zip_inner else Path(item.file_path or "").parent)
        if classify_with_text:
            first_type, _ = classify_pdf(item.filename, parent_hint, "")
            if (not sample_text_unknown_only) or first_type == "unknown":
                text, pages = first_pdf_text(data)
            else:
                _, pages = first_pdf_text(data, max_pages=1, max_chars=1)
        else:
            _, pages = first_pdf_text(data, max_pages=1, max_chars=1)
        item.pages = pages
        item.doc_type, item.classifier_reason = classify_pdf(item.filename, parent_hint, text)
        if idx % 100 == 0:
            print(f"[inventario] clasificados {idx}/{len(items)} PDFs", flush=True)


def post_pdf(api_base: str, api_key: str, endpoint: str, field: str, item: PdfItem, data: bytes, timeout: int, form: Optional[Dict[str, str]] = None) -> Tuple[int, Dict[str, Any], int, Optional[str]]:
    url = f"{api_base.rstrip('/')}/{endpoint.lstrip('/')}"
    headers = {"X-API-Key": api_key, "Accept": "application/json"}
    files = {field: (item.filename, io.BytesIO(data), "application/pdf")}
    start = time.perf_counter()
    try:
        resp = requests.post(url, headers=headers, files=files, data=form or {}, timeout=timeout)
        elapsed_ms = int((time.perf_counter() - start) * 1000)
        try:
            body = resp.json()
        except Exception:
            body = {"raw": resp.text[:1000]}
        return resp.status_code, body if isinstance(body, dict) else {"json": body}, elapsed_ms, None
    except requests.Timeout as exc:
        elapsed_ms = int((time.perf_counter() - start) * 1000)
        return 0, {}, elapsed_ms, f"timeout: {exc}"
    except Exception as exc:
        elapsed_ms = int((time.perf_counter() - start) * 1000)
        return 0, {}, elapsed_ms, str(exc)


def quick_config(doc_type: str) -> Tuple[str, str, int, Optional[Dict[str, str]]]:
    if doc_type == "identificacion_oficial":
        return "precheck-identificacion-pdf", "documento", 60, None
    if doc_type == "curp":
        return "verificar-curp-documento", "documento", 70, None
    if doc_type == "nss":
        return "verificar-nss-documento", "documento", 60, None
    if doc_type == "acta_nacimiento":
        return "verificar-acta-documento", "documento", 45, None
    if doc_type == "constancia_fiscal":
        return "verificar-constancia-fiscal-documento", "documento", 70, None
    if doc_type == "comprobante_domicilio":
        return "verificar-comprobante", "documento", 70, None
    if doc_type == "__SPARTA_SECRET_REDACTED__":
        return "verificar-estado-cuenta", "documento", 70, None
    if doc_type == "contrato_fad":
        return "fad/informacion-ingresos", "documento", 45, None
    if doc_type == "factura_moto":
        return "factura/datos-moto", "documento", 45, None
    if doc_type == "solicitud_interna":
        return "validar-paginas-pdf", "documento", 25, {"minimo_paginas": "2", "nombre_documento": "La solicitud interna"}
    if doc_type == "cv":
        return "validar-paginas-pdf", "documento", 25, {"minimo_paginas": "1", "nombre_documento": "El CV"}
    if doc_type == "hoja_retencion":
        return "validar-paginas-pdf", "documento", 25, {"minimo_paginas": "1", "nombre_documento": "La hoja de retencion"}
    return "validar-paginas-pdf", "documento", 25, {"minimo_paginas": "1", "nombre_documento": "El documento"}


def quick_outcome(doc_type: str, status_code: int, body: Dict[str, Any], error: Optional[str]) -> Tuple[str, str]:
    if error:
        return "error", error
    if status_code != 200:
        return "error", f"HTTP {status_code}: {body.get('detail') or body.get('mensaje') or body.get('raw') or ''}"
    if body.get("rechazado") is True:
        return "rechazado", str(body.get("mensaje") or body.get("recomendacion") or body.get("motivo_rechazo") or "rechazado")
    if doc_type == "comprobante_domicilio":
        result = str(body.get("resultado") or "").upper()
        if result == "RECHAZADO":
            return "rechazado", str(body.get("recomendacion") or "rechazado")
        if result == "REVISION_MANUAL":
            return "revision", str(body.get("recomendacion") or "revision manual")
        return "aceptado", str(body.get("recomendacion") or "aceptado")
    if doc_type == "contrato_fad":
        return ("aceptado" if body.get("encontrado") else "revision", "seccion ingresos encontrada" if body.get("encontrado") else "sin seccion ingresos")
    if doc_type == "factura_moto":
        return ("aceptado" if body.get("encontrado") else "revision", "datos moto encontrados" if body.get("encontrado") else "sin datos moto")
    if body.get("valido") is True or body.get("aceptado") is True:
        return "aceptado", str(body.get("mensaje") or "aceptado")
    if body.get("revision_manual") is True or body.get("timeout") is True:
        return "revision", str(body.get("mensaje") or "revision manual")
    if body.get("valido") is False:
        return "rechazado", str(body.get("mensaje") or "no valido")
    return "revision", str(body.get("mensaje") or "resultado no concluyente")


def load_jsonl(path: Path) -> List[Dict[str, Any]]:
    if not path.is_file():
        return []
    rows: List[Dict[str, Any]] = []
    with path.open("r", encoding="utf-8") as fh:
        for line in fh:
            line = line.strip()
            if not line:
                continue
            try:
                row = json.loads(line)
                if isinstance(row, dict):
                    rows.append(row)
            except Exception:
                continue
    return rows


def append_jsonl(path: Path, row: Dict[str, Any], lock: threading.Lock) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    raw = json.dumps(row, ensure_ascii=False, separators=(",", ":"))
    with lock:
        with path.open("a", encoding="utf-8") as fh:
            fh.write(raw + "\n")


def run_one_quick(api_base: str, api_key: str, item: PdfItem) -> Dict[str, Any]:
    data = read_item_bytes(item)
    endpoint, field, timeout, form = quick_config(item.doc_type)
    status, body, elapsed_ms, error = post_pdf(api_base, api_key, endpoint, field, item, data, timeout, form)
    outcome, message = quick_outcome(item.doc_type, status, body, error)
    api_ms = body.get("tiempo_ms") or body.get("tiempo_proceso_ms")
    if api_ms is None and isinstance(body.get("tiempos_fase_ms"), dict):
        api_ms = body.get("tiempos_fase_ms", {}).get("total_endpoint_ms")
    return {
        "item": asdict(item),
        "endpoint": endpoint,
        "status_code": status,
        "elapsed_ms": elapsed_ms,
        "api_reported_ms": api_ms,
        "outcome": outcome,
        "message": message[:500] if isinstance(message, str) else str(message)[:500],
        "response": compact_response(body),
    }


def run_quick(
    api_base: str,
    api_key: str,
    items: List[PdfItem],
    limit: Optional[int] = None,
    checkpoint_path: Optional[Path] = None,
    workers: int = 1,
) -> List[Dict[str, Any]]:
    results: List[Dict[str, Any]] = []
    selected = items[:limit] if limit else items
    total = len(selected)

    existing_by_id: Dict[str, Dict[str, Any]] = {}
    if checkpoint_path:
        for row in load_jsonl(checkpoint_path):
            item_id = ((row.get("item") or {}).get("id"))
            if item_id:
                existing_by_id[item_id] = row
    pending = [item for item in selected if item.id not in existing_by_id]
    results = [existing_by_id[item.id] for item in selected if item.id in existing_by_id]
    if existing_by_id:
        print(f"[rapida] checkpoint={len(results)} pendientes={len(pending)} total={total}", flush=True)

    lock = threading.Lock()
    done = len(results)

    def record(result: Dict[str, Any]) -> None:
        nonlocal done
        results.append(result)
        done += 1
        if checkpoint_path:
            append_jsonl(checkpoint_path, result, lock)
        if done == 1 or done % 25 == 0 or done == total:
            item = result["item"]
            print(
                f"[rapida] {done}/{total} {result.get('outcome')} {result.get('elapsed_ms')}ms :: {item.get('display_path')}",
                flush=True,
            )

    if not pending:
        return results
    if workers <= 1:
        for item in pending:
            record(run_one_quick(api_base, api_key, item))
        return results

    with concurrent.futures.ThreadPoolExecutor(max_workers=workers) as executor:
        future_map = {executor.submit(run_one_quick, api_base, api_key, item): item for item in pending}
        for future in concurrent.futures.as_completed(future_map):
            item = future_map[future]
            try:
                record(future.result())
            except Exception as exc:
                result = {
                    "item": asdict(item),
                    "endpoint": quick_config(item.doc_type)[0],
                    "status_code": 0,
                    "elapsed_ms": 0,
                    "api_reported_ms": None,
                    "outcome": "error",
                    "message": str(exc)[:500],
                    "response": {},
                }
                record(result)
    return results


def compact_response(body: Dict[str, Any]) -> Dict[str, Any]:
    keep = [
        "valido", "rechazado", "revision_manual", "timeout", "mensaje", "motivo_rechazo",
        "resultado", "recomendacion", "score_validacion", "tipo_comprobante", "empresa",
        "nombre", "nombre_titular", "nombre_propietario", "curp", "curp_extraido",
        "curp_lectura_ia", "rfc", "nss_extraido", "nss_lectura_ia", "fecha_emision",
        "fecha_documento", "meses_antiguedad", "vigencia_ok", "es_reciente",
        "regimen_sueldos_salarios", "actividad_asalariado", "banco_detectado", "clabe",
        "numero_cuenta", "paginas", "minimo_paginas", "modo", "modo_validacion",
        "motor_ia", "modelo_ia", "tiempo_ms", "tiempo_proceso_ms", "indicadores",
        "tipo_identificacion", "alertas", "notas", "encontrado", "empresa",
        "empleado", "ingreso_mensual_neto", "vin", "no_motor", "color",
    ]
    out: Dict[str, Any] = {}
    for key in keep:
        if key in body and body[key] not in (None, "", [], {}):
            out[key] = body[key]
    detail = body.get("detail")
    if detail:
        out["detail"] = detail
    return out


def validation_summary_from_quick(result: Dict[str, Any]) -> Optional[Tuple[str, Dict[str, Any]]]:
    item = result["item"]
    doc_type = item["doc_type"]
    key = SUMMARY_KEY_BY_DOC_TYPE.get(doc_type)
    if not key:
        return None
    response = result.get("response") or {}
    pages = item.get("pages") or response.get("paginas")
    validation: Dict[str, Any] = {
        "valido": result.get("outcome") == "aceptado",
        "rechazado": result.get("outcome") == "rechazado",
        "revision_manual": result.get("outcome") == "revision",
        "mensaje": result.get("message"),
        "paginas_pdf": pages,
        "tipo_documento_detectado": {
            "solicitud_interna": "solicitud___SPARTA_SECRET_REDACTED__",
            "cv": "cv",
            "acta_nacimiento": "acta_nacimiento",
            "curp": "curp",
            "identificacion_oficial": response.get("tipo_identificacion") or "identificacion_oficial",
            "comprobante_domicilio": "comprobante_domicilio",
            "constancia_fiscal": "constancia_fiscal",
            "nss": "nss",
            "hoja_retencion": "infonavit_fonacot",
            "__SPARTA_SECRET_REDACTED__": "__SPARTA_SECRET_REDACTED__",
        }.get(doc_type),
    }
    mapping = {
        "nombre": ["nombre", "nombre_titular", "nombre_propietario"],
        "curp": ["curp", "curp_extraido", "curp_lectura_ia"],
        "curp_extraido": ["curp_extraido"],
        "rfc": ["rfc"],
        "nss_extraido": ["nss_extraido", "nss_lectura_ia"],
        "fecha_nacimiento": ["fecha_nacimiento"],
        "fecha_emision": ["fecha_emision", "fecha_documento"],
        "meses_antiguedad": ["meses_antiguedad"],
        "domicilio": ["direccion", "domicilio"],
        "direccion": ["direccion", "domicilio"],
        "banco_detectado": ["banco_detectado"],
        "nombre_propietario": ["nombre_propietario"],
        "clabe": ["clabe"],
        "numero_cuenta": ["numero_cuenta"],
        "regimen_fiscal": ["regimen_fiscal"],
        "regimen_sueldos_salarios": ["regimen_sueldos_salarios"],
    }
    for out_key, candidates in mapping.items():
        for cand in candidates:
            value = response.get(cand)
            if value not in (None, "", [], {}):
                validation[out_key] = value
                break
    if doc_type == "comprobante_domicilio":
        validation["valido"] = str(response.get("resultado") or "").upper() != "RECHAZADO"
        validation["rechazado"] = str(response.get("resultado") or "").upper() == "RECHAZADO"
    validation = {k: v for k, v in validation.items() if v not in (None, "", [], {})}
    summary = {
        "key": key,
        "tipo_documento": DOC_LABELS.get(doc_type, doc_type),
        "archivo": item["filename"],
        "paginas_pdf": pages,
        "motor_ia": response.get("motor_ia") or "motor_v1",
        "modelo_ia": response.get("modelo_ia") or "quick_endpoint",
        "fuente_lectura": "qa_quick_endpoint",
        "validacion_previa": validation,
    }
    return key, summary


def choose_group_docs(group_items: List[PdfItem]) -> Dict[str, PdfItem]:
    chosen: Dict[str, PdfItem] = {}
    priority_words = {
        "solicitud_interna": ["SOLICITUD INTERNA", "SOLICITUD MAXIKASH", "SOLICITUD"],
        "cv": ["CV", "CURRICUL"],
        "acta_nacimiento": ["ACTA"],
        "curp": ["CURP"],
        "identificacion_oficial": ["INE", "IDENTIFIC", "RESIDENCIA", "PASAPORTE"],
        "comprobante_domicilio": ["DOMICILIO", "COMPROBANTE"],
        "constancia_fiscal": ["CSF", "FISCAL", "SAT"],
        "nss": ["NSS", "IMSS"],
        "hoja_retencion": ["RETENCION", "FONACOT", "INFONAVIT", "NO CREDITOS", "NO ADEUDO"],
        "__SPARTA_SECRET_REDACTED__": ["ESTADO DE CUENTA", "BBVA", "BANORTE", "SANTANDER", "BANCO"],
    }
    for item in group_items:
        if item.doc_type not in FIELD_BY_DOC_TYPE:
            continue
        key = item.doc_type
        if key not in chosen:
            chosen[key] = item
            continue
        current = chosen[key]
        base_new = normalize_text(item.filename)
        base_old = normalize_text(current.filename)
        words = priority_words.get(key, [])
        score_new = sum(1 for w in words if w in base_new)
        score_old = sum(1 for w in words if w in base_old)
        if (score_new, -len(base_new), -item.size) > (score_old, -len(base_old), -current.size):
            chosen[key] = item
    return chosen


def build_cross_payload(group_id: str, candidate: str, chosen: Dict[str, PdfItem], quick_by_item_id: Dict[str, Dict[str, Any]], include_files: bool) -> Dict[str, Any]:
    payload: Dict[str, Any] = {
        "tipo_documento": "INE_NUEVA",
        "nombre_candidato_registro": candidate,
    }
    lecturas: Dict[str, Any] = {}
    for doc_type, item in chosen.items():
        field = FIELD_BY_DOC_TYPE.get(doc_type)
        if field and include_files:
            data = read_item_bytes(item)
            payload[field] = {
                "filename": item.filename,
                "mime": "application/pdf",
                "bytes": len(data),
                "b64": base64.b64encode(data).decode("ascii"),
            }
        quick = quick_by_item_id.get(item.id)
        if quick:
            summary_pair = validation_summary_from_quick(quick)
            if summary_pair:
                key, summary = summary_pair
                lecturas[key] = summary
    if lecturas:
        raw = json.dumps(lecturas, ensure_ascii=False, separators=(",", ":")).encode("utf-8")
        payload["lecturas_json_b64"] = base64.b64encode(raw).decode("ascii")
    return payload


def run_crosschecks(
    api_base: str,
    api_key: str,
    items: List[PdfItem],
    quick_results: List[Dict[str, Any]],
    include_files: bool = True,
    limit: Optional[int] = None,
    checkpoint_path: Optional[Path] = None,
) -> List[Dict[str, Any]]:
    quick_by_id = {r["item"]["id"]: r for r in quick_results}
    groups: Dict[str, List[PdfItem]] = {}
    candidates: Dict[str, str] = {}
    for item in items:
        if item.group_id:
            groups.setdefault(item.group_id, []).append(item)
            if item.candidate_name:
                candidates[item.group_id] = item.candidate_name
    todo: List[Tuple[str, str, Dict[str, PdfItem]]] = []
    for group_id, group_items in sorted(groups.items()):
        chosen = choose_group_docs(group_items)
        if len(chosen) < 2:
            continue
        todo.append((group_id, candidates.get(group_id) or group_id, chosen))
    if limit:
        todo = todo[:limit]

    results: List[Dict[str, Any]] = []
    existing_by_group: Dict[str, Dict[str, Any]] = {}
    if checkpoint_path:
        for row in load_jsonl(checkpoint_path):
            group_id = row.get("group_id")
            if group_id:
                existing_by_group[str(group_id)] = row
    if existing_by_group:
        results = [existing_by_group[group_id] for group_id, _, _ in todo if group_id in existing_by_group]
        todo = [(group_id, candidate, chosen) for group_id, candidate, chosen in todo if group_id not in existing_by_group]
        print(f"[cruce] checkpoint={len(results)} pendientes={len(todo)}", flush=True)

    lock = threading.Lock()
    headers = {"X-API-Key": api_key, "Accept": "application/json", "Content-Type": "application/json"}
    url = f"{api_base.rstrip('/')}/validar-expediente-json"
    total = len(todo) + len(results)
    for idx, (group_id, candidate, chosen) in enumerate(todo, 1):
        payload = build_cross_payload(group_id, candidate, chosen, quick_by_id, include_files)
        payload_bytes = len(json.dumps(payload, ensure_ascii=False).encode("utf-8"))
        start = time.perf_counter()
        error = None
        status = 0
        body: Dict[str, Any] = {}
        try:
            resp = requests.post(url, headers=headers, data=json.dumps(payload, ensure_ascii=False).encode("utf-8"), timeout=320)
            status = resp.status_code
            try:
                parsed = resp.json()
                body = parsed if isinstance(parsed, dict) else {"json": parsed}
            except Exception:
                body = {"raw": resp.text[:2000]}
        except requests.Timeout as exc:
            error = f"timeout: {exc}"
        except Exception as exc:
            error = str(exc)
        elapsed_ms = int((time.perf_counter() - start) * 1000)
        analysis = body.get("analysis") if isinstance(body.get("analysis"), dict) else {}
        normalized_dictamen = body.get("dictamen_ia") or analysis.get("dictamen")
        normalized_confianza = body.get("identificacion_frente_score") or analysis.get("confianza")
        normalized_coincidencias = body.get("coincidencias_v2") or analysis.get("coincidencias")
        normalized_alertas = body.get("alertas") if isinstance(body.get("alertas"), list) else (analysis.get("alertas") or [])
        normalized_comps = body.get("comparaciones_v2") if isinstance(body.get("comparaciones_v2"), list) else (analysis.get("comparaciones") or [])
        result = {
            "group_id": group_id,
            "candidate_name": candidate,
            "selected_docs": {k: v.display_path for k, v in chosen.items()},
            "selected_doc_count": len(chosen),
            "payload_bytes": payload_bytes,
            "include_files": include_files,
            "status_code": status,
            "elapsed_ms": elapsed_ms,
            "error": error,
            "dictamen": normalized_dictamen,
            "confianza": normalized_confianza,
            "coincidencias": normalized_coincidencias,
            "alertas_count": len(normalized_alertas or []),
            "comparaciones_count": len(normalized_comps or []),
            "tiempos_fase_ms": body.get("tiempos_fase_ms"),
            "response_compact": compact_cross_response(body),
        }
        results.append(result)
        if checkpoint_path:
            append_jsonl(checkpoint_path, result, lock)
        done = len(results)
        if done == 1 or done % 5 == 0 or done == total:
            print(f"[cruce] {idx}/{total} {result.get('dictamen')} {elapsed_ms}ms :: {candidate}", flush=True)
    return results


def compact_cross_response(body: Dict[str, Any]) -> Dict[str, Any]:
    analysis = body.get("analysis") if isinstance(body.get("analysis"), dict) else {}
    docs = analysis.get("documentos") if isinstance(analysis.get("documentos"), dict) else {}
    if not docs and isinstance(body.get("documentos_analizados_v2"), dict):
        docs = body.get("documentos_analizados_v2") or {}
    comps = analysis.get("comparaciones") if isinstance(analysis.get("comparaciones"), list) else []
    if not comps and isinstance(body.get("comparaciones_v2"), list):
        comps = body.get("comparaciones_v2") or []
    out = {
        "dictamen": body.get("dictamen_ia") or analysis.get("dictamen"),
        "confianza": body.get("identificacion_frente_score") or analysis.get("confianza"),
        "resumen_final": body.get("resumen_ia") or analysis.get("resumen_final"),
        "coincidencias": body.get("coincidencias_v2") or analysis.get("coincidencias"),
        "datos_referencia": body.get("datos_referencia_v2") or analysis.get("datos_referencia"),
        "alertas": (body.get("alertas") or analysis.get("alertas") or [])[:20],
        "recomendaciones": (body.get("recomendaciones") or analysis.get("recomendaciones") or [])[:20],
        "tiempos_fase_ms": body.get("tiempos_fase_ms"),
        "tiempo_proceso_ms": body.get("tiempo_proceso_ms"),
        "motor_ia": body.get("motor_ia") or body.get("provider"),
        "modelo_ia": body.get("modelo_ia") or body.get("model"),
    }
    out["documentos"] = {
        k: {
            kk: vv
            for kk, vv in (v or {}).items()
            if kk in {"estado", "tipo_detectado", "archivo", "paginas_pdf", "nombre", "curp", "rfc", "nss", "mensaje"}
        }
        for k, v in docs.items()
    }
    out["comparaciones_fallidas"] = [c for c in comps if isinstance(c, dict) and c.get("coincide") is False][:20]
    return {k: v for k, v in out.items() if v not in (None, "", [], {})}


def stats_ms(values: Iterable[Optional[int]]) -> Dict[str, Optional[float]]:
    nums = [int(v) for v in values if v is not None]
    if not nums:
        return {"count": 0, "avg": None, "median": None, "p95": None, "min": None, "max": None}
    nums_sorted = sorted(nums)
    p95_idx = min(len(nums_sorted) - 1, int(round((len(nums_sorted) - 1) * 0.95)))
    return {
        "count": len(nums),
        "avg": round(statistics.mean(nums), 2),
        "median": round(statistics.median(nums), 2),
        "p95": nums_sorted[p95_idx],
        "min": nums_sorted[0],
        "max": nums_sorted[-1],
    }


def summarize(items: List[PdfItem], skipped: Dict[str, int], quick: List[Dict[str, Any]], cross: List[Dict[str, Any]]) -> Dict[str, Any]:
    by_type: Dict[str, int] = {}
    for item in items:
        by_type[item.doc_type] = by_type.get(item.doc_type, 0) + 1
    quick_by_type: Dict[str, Dict[str, Any]] = {}
    for doc_type in sorted(by_type):
        values = [r["elapsed_ms"] for r in quick if r["item"]["doc_type"] == doc_type]
        outcomes: Dict[str, int] = {}
        for r in quick:
            if r["item"]["doc_type"] == doc_type:
                outcomes[r["outcome"]] = outcomes.get(r["outcome"], 0) + 1
        quick_by_type[doc_type] = {"timing_ms": stats_ms(values), "outcomes": outcomes}
    cross_dictamen: Dict[str, int] = {}
    for r in cross:
        key = r.get("dictamen") or ("error" if r.get("error") or r.get("status_code") != 200 else "sin_dictamen")
        cross_dictamen[key] = cross_dictamen.get(key, 0) + 1
    duplicate_hashes = len(items) - len({i.sha256 for i in items if i.sha256})
    return {
        "generated_at": datetime.now().isoformat(timespec="seconds"),
        "total_pdfs": len(items),
        "skipped_non_pdf": skipped,
        "pdfs_by_type": by_type,
        "duplicate_pdf_hash_count": duplicate_hashes,
        "quick_total": len(quick),
        "quick_timing_ms": stats_ms([r.get("elapsed_ms") for r in quick]),
        "quick_by_type": quick_by_type,
        "quick_outcomes": {
            outcome: sum(1 for r in quick if r.get("outcome") == outcome)
            for outcome in sorted({r.get("outcome") for r in quick})
        },
        "cross_total": len(cross),
        "cross_timing_ms": stats_ms([r.get("elapsed_ms") for r in cross]),
        "cross_dictamen": cross_dictamen,
        "cross_errors": sum(1 for r in cross if r.get("error") or r.get("status_code") != 200),
    }


def write_outputs(out_dir: Path, items: List[PdfItem], skipped: Dict[str, int], quick: List[Dict[str, Any]], cross: List[Dict[str, Any]], summary: Dict[str, Any]) -> Dict[str, str]:
    out_dir.mkdir(parents=True, exist_ok=True)
    paths = {
        "json": str(out_dir / "document_validation_qa_results.json"),
        "quick_csv": str(out_dir / "document_validation_qa_quick.csv"),
        "cross_csv": str(out_dir / "document_validation_qa_cross.csv"),
        "html": str(out_dir / "document_validation_qa_report.html"),
    }
    full = {
        "summary": summary,
        "items": [asdict(i) for i in items],
        "skipped_non_pdf": skipped,
        "quick_results": quick,
        "cross_results": cross,
    }
    Path(paths["json"]).write_text(json.dumps(full, ensure_ascii=False, indent=2), encoding="utf-8")

    with open(paths["quick_csv"], "w", newline="", encoding="utf-8-sig") as fh:
        writer = csv.DictWriter(
            fh,
            fieldnames=[
                "doc_type", "outcome", "elapsed_ms", "api_reported_ms", "endpoint",
                "status_code", "pages", "size", "filename", "display_path", "message",
            ],
        )
        writer.writeheader()
        for r in quick:
            it = r["item"]
            writer.writerow({
                "doc_type": it["doc_type"],
                "outcome": r.get("outcome"),
                "elapsed_ms": r.get("elapsed_ms"),
                "api_reported_ms": r.get("api_reported_ms"),
                "endpoint": r.get("endpoint"),
                "status_code": r.get("status_code"),
                "pages": it.get("pages"),
                "size": it.get("size"),
                "filename": it.get("filename"),
                "display_path": it.get("display_path"),
                "message": r.get("message"),
            })

    with open(paths["cross_csv"], "w", newline="", encoding="utf-8-sig") as fh:
        writer = csv.DictWriter(
            fh,
            fieldnames=[
                "candidate_name", "dictamen", "confianza", "elapsed_ms", "status_code",
                "selected_doc_count", "payload_bytes", "alertas_count", "comparaciones_count",
                "group_id", "error",
            ],
        )
        writer.writeheader()
        for r in cross:
            writer.writerow({k: r.get(k) for k in writer.fieldnames})

    Path(paths["html"]).write_text(render_html(summary, quick, cross), encoding="utf-8")
    return paths


def render_html(summary: Dict[str, Any], quick: List[Dict[str, Any]], cross: List[Dict[str, Any]]) -> str:
    def esc(v: Any) -> str:
        return (str(v).replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;"))

    by_type_rows = []
    for doc_type, data in sorted(summary.get("quick_by_type", {}).items()):
        t = data["timing_ms"]
        by_type_rows.append(
            f"<tr><td>{esc(doc_type)}</td><td>{t['count']}</td><td>{t['avg']}</td><td>{t['median']}</td><td>{t['p95']}</td><td>{t['max']}</td><td>{esc(data['outcomes'])}</td></tr>"
        )
    slow_quick = sorted(quick, key=lambda r: r.get("elapsed_ms") or 0, reverse=True)[:25]
    slow_rows = "".join(
        f"<tr><td>{esc(r['item']['doc_type'])}</td><td>{r.get('elapsed_ms')}</td><td>{esc(r.get('outcome'))}</td><td>{esc(r['item']['display_path'])}</td><td>{esc(r.get('message'))}</td></tr>"
        for r in slow_quick
    )
    cross_rows = "".join(
        f"<tr><td>{esc(r.get('candidate_name'))}</td><td>{esc(r.get('dictamen'))}</td><td>{r.get('confianza')}</td><td>{r.get('elapsed_ms')}</td><td>{r.get('selected_doc_count')}</td><td>{esc(r.get('error') or '')}</td></tr>"
        for r in sorted(cross, key=lambda x: x.get("elapsed_ms") or 0, reverse=True)[:40]
    )
    return f"""<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reporte QA Validacion Documental</title>
<style>
body {{ font-family: Arial, sans-serif; margin: 28px; color: #1f2933; }}
h1, h2 {{ color: #102a43; }}
table {{ border-collapse: collapse; width: 100%; margin: 12px 0 24px; font-size: 12px; }}
th, td {{ border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: top; }}
th {{ background: #eef2f7; text-align: left; }}
.grid {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }}
.metric {{ border: 1px solid #cbd5e1; padding: 10px; }}
.metric b {{ display: block; font-size: 20px; margin-top: 4px; }}
code {{ font-size: 11px; }}
</style>
</head>
<body>
<h1>Reporte QA Validacion Documental</h1>
<p>Generado: {esc(summary.get('generated_at'))}</p>
<div class="grid">
  <div class="metric">PDFs revisados<b>{summary.get('total_pdfs')}</b></div>
  <div class="metric">Revision rapida<b>{summary.get('quick_total')}</b></div>
  <div class="metric">Cruces expediente<b>{summary.get('cross_total')}</b></div>
  <div class="metric">Errores cruce<b>{summary.get('cross_errors')}</b></div>
</div>
<h2>Resumen</h2>
<pre>{esc(json.dumps(summary, ensure_ascii=False, indent=2))}</pre>
<h2>Tiempos por tipo - revision rapida</h2>
<table><thead><tr><th>Tipo</th><th>N</th><th>Prom ms</th><th>Mediana</th><th>P95</th><th>Max</th><th>Outcomes</th></tr></thead><tbody>{''.join(by_type_rows)}</tbody></table>
<h2>Mas lentos - revision rapida</h2>
<table><thead><tr><th>Tipo</th><th>ms</th><th>Resultado</th><th>Archivo</th><th>Mensaje</th></tr></thead><tbody>{slow_rows}</tbody></table>
<h2>Mas lentos - validacion cruzada</h2>
<table><thead><tr><th>Candidato/grupo</th><th>Dictamen</th><th>Confianza</th><th>ms</th><th>Docs</th><th>Error</th></tr></thead><tbody>{cross_rows}</tbody></table>
</body></html>"""


def main(argv: Optional[List[str]] = None) -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", default=r"C:\Users\amigo_j9s4pcx\Downloads\Pruebas")
    parser.add_argument("--api-base", default=API_BASE_DEFAULT)
    parser.add_argument("--api-key", default=os.environ.get("DOC_API_KEY", API_KEY_DEFAULT))
    parser.add_argument("--out-dir", default="")
    parser.add_argument("--quick-limit", type=int, default=0)
    parser.add_argument("--cross-limit", type=int, default=0)
    parser.add_argument("--quick-workers", type=int, default=1)
    parser.add_argument("--skip-cross", action="store_true")
    parser.add_argument("--cross-without-files", action="store_true")
    parser.add_argument("--no-text-classify", action="store_true")
    args = parser.parse_args(argv)

    root = Path(args.root)
    if not root.exists():
        raise SystemExit(f"Root does not exist: {root}")

    ts = datetime.now().strftime("%Y%m%d_%H%M%S")
    out_dir = Path(args.out_dir) if args.out_dir else Path.cwd() / "output" / "pdf" / f"document_validation_qa_{ts}"

    print(f"[inicio] root={root}", flush=True)
    print(f"[inicio] api={args.api_base}", flush=True)
    health = requests.get(f"{args.api_base.rstrip('/')}/health", headers={"X-API-Key": args.api_key}, timeout=10)
    print(f"[inicio] health={health.status_code} {health.text[:120]}", flush=True)

    items, skipped = discover(root)
    print(f"[inventario] PDFs={len(items)} skipped={skipped}", flush=True)
    enrich_items(items, classify_with_text=not args.no_text_classify, sample_text_unknown_only=True)
    quick_checkpoint = out_dir / "quick_results.jsonl"
    cross_checkpoint = out_dir / "cross_results.jsonl"
    quick = run_quick(
        args.api_base,
        args.api_key,
        items,
        limit=args.quick_limit or None,
        checkpoint_path=quick_checkpoint,
        workers=max(1, int(args.quick_workers or 1)),
    )
    cross: List[Dict[str, Any]] = []
    if not args.skip_cross:
        cross = run_crosschecks(
            args.api_base,
            args.api_key,
            items,
            quick,
            include_files=not args.cross_without_files,
            limit=args.cross_limit or None,
            checkpoint_path=cross_checkpoint,
        )
    summary = summarize(items, skipped, quick, cross)
    paths = write_outputs(out_dir, items, skipped, quick, cross, summary)
    print("[salida] " + json.dumps(paths, ensure_ascii=False), flush=True)
    print("[resumen] " + json.dumps(summary, ensure_ascii=False), flush=True)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
