# app/api/routes.py
"""
Endpoints de la API REST.
"""
import asyncio
import base64
import json
import re
import time
import unicodedata
from datetime import datetime
from fastapi import APIRouter, UploadFile, File, HTTPException, Depends, Query, Body, Form
from fastapi.security.api_key import APIKeyHeader
from typing import Optional, List, Dict, Any
from loguru import logger

from app.models.schemas import (
    VerificacionResponse, VerificacionCalidadResponse, TipoDocumento, ErrorResponse, ComprobanteResponse,
    CheckOCR, CheckForense
)
from app.services.verificacion import VerificacionService
from app.services.comprobante_analyzer import ComprobanteAnalyzer
from app.utils.nss_validator import validar_nss, extraer_nss_de_pdf
from app.utils.curp_validator import validar_curp, extraer_curp_de_pdf
from app.services.document_crosscheck import (
    extraer_datos_curp_pdf, extraer_datos_nss_pdf,
    extraer_datos_constancia_fiscal, extraer_datos_acta_nacimiento,
    extraer_informacion_ingresos_fad,
    extraer_datos_motocicleta_factura,
    validacion_cruzada,
    es_documento_nss, es_tarjeta_nss, es_documento_curp, es_documento_constancia_fiscal, es_documento_acta_nacimiento,
    pdf_paginas_a_png_bytes,
)
from app.services.alibaba_document_ai import (
    AlibabaDocumentAI,
    quick_result_to_summary,
    summary_is_usable,
    normalize_text,
    is_digital_bank,
)
from app.core.config import get_settings

try:
    import fitz
    PYMUPDF_AVAILABLE = True
except ImportError:
    PYMUPDF_AVAILABLE = False

router = APIRouter()
settings = get_settings()
API_BUILD = "doc-precheck-2026-06-18-comprobante-content-first"

api_key_header = APIKeyHeader(name=settings.api_key_header, auto_error=False)

EXTENSIONES_PERMITIDAS = {"jpg", "jpeg", "png", "webp", "tiff"}
EXTENSIONES_COMPROBANTE = {"jpg", "jpeg", "png", "webp", "tiff", "pdf"}
MAX_SIZE_BYTES = settings.max_image_size_mb * 1024 * 1024


def _doc_ai_alibaba_activo() -> bool:
    return str(getattr(settings, "doc_ai_engine", "legacy") or "legacy").strip().lower() == "alibaba"


def _crear_alibaba_ai() -> Optional[AlibabaDocumentAI]:
    api_key = str(getattr(settings, "alibaba_api_key", "") or "").strip()
    base_url = str(getattr(settings, "alibaba_base_url", "") or "").strip()
    model = str(getattr(settings, "alibaba_model", "") or "").strip()
    if not api_key or not base_url or not model:
        return None
    return AlibabaDocumentAI(
        api_key=api_key,
        base_url=base_url,
        model=model,
        fallback_models=str(getattr(settings, "alibaba_fallback_models", "") or ""),
        retry_delays=str(getattr(settings, "alibaba_retry_delays", "0") or "0"),
        timeout_seconds=int(getattr(settings, "doc_ai_quick_timeout_seconds", 35) or 35),
        max_pages=int(getattr(settings, "doc_ai_quick_max_pages", 3) or 3),
        dpi=int(getattr(settings, "doc_ai_quick_dpi", 150) or 150),
    )


def _crear_alibaba_ai_crosscheck() -> Optional[AlibabaDocumentAI]:
    api_key = str(getattr(settings, "alibaba_api_key", "") or "").strip()
    base_url = str(getattr(settings, "alibaba_base_url", "") or "").strip()
    model = str(getattr(settings, "alibaba_crosscheck_model", "") or "").strip()
    if not model:
        model = str(getattr(settings, "alibaba_model", "") or "").strip()
    if not api_key or not base_url or not model:
        return None
    fallback_models = str(getattr(settings, "alibaba_crosscheck_fallback_models", "") or "").strip()
    return AlibabaDocumentAI(
        api_key=api_key,
        base_url=base_url,
        model=model,
        fallback_models=fallback_models,
        retry_delays=str(getattr(settings, "alibaba_retry_delays", "0") or "0"),
        timeout_seconds=int(getattr(settings, "doc_ai_crosscheck_timeout_seconds", 90) or 90),
        max_pages=int(getattr(settings, "doc_ai_crosscheck_max_pages_per_document", 2) or 2),
        dpi=int(getattr(settings, "doc_ai_crosscheck_dpi", 135) or 135),
    )


async def _validar_rapido_alibaba_o_none(file_bytes: bytes, filename: str, expected_doc_type: str, nombre_candidato: Optional[str] = None) -> Optional[Dict[str, Any]]:
    if not _doc_ai_alibaba_activo():
        return None
    ai = _crear_alibaba_ai()
    if ai is None or not ai.enabled():
        mensaje = "DOC_AI_ENGINE=alibaba esta activo, pero faltan ALIBABA_API_KEY, ALIBABA_BASE_URL o ALIBABA_MODEL."
        if bool(getattr(settings, "doc_ai_legacy_fallback", True)):
            logger.warning(mensaje + " Se usara motor legacy por fallback.")
            return None
        raise HTTPException(status_code=503, detail=mensaje)
    timeout = int(getattr(settings, "doc_ai_quick_timeout_seconds", 35) or 35) + 5
    try:
        return await asyncio.wait_for(
            asyncio.to_thread(ai.quick_verify, file_bytes, filename, expected_doc_type, nombre_candidato),
            timeout=timeout,
        )
    except Exception as exc:
        if bool(getattr(settings, "doc_ai_legacy_fallback", True)):
            logger.exception(f"Alibaba document AI fallo para {expected_doc_type}; usando fallback legacy: {exc}")
            return None
        logger.exception(f"Alibaba document AI fallo para {expected_doc_type}: {exc}")
        raise HTTPException(status_code=502, detail="No se pudo validar el documento con Alibaba. Intenta de nuevo.")


def _ai_extraccion(res: Dict[str, Any]) -> Dict[str, Any]:
    return res.get("extraction") or {}


def _ai_campos(res: Dict[str, Any]) -> Dict[str, Any]:
    return _ai_extraccion(res).get("campos") or {}


def _ai_validacion(res: Dict[str, Any]) -> Dict[str, Any]:
    return res.get("validation") or {}


def _ai_errores(res: Dict[str, Any]) -> List[str]:
    return list(_ai_validacion(res).get("errores") or [])


def _ai_advertencias(res: Dict[str, Any]) -> List[str]:
    return list(_ai_validacion(res).get("advertencias") or [])


def _ai_mensaje(res: Dict[str, Any], fallback: str) -> str:
    return str(_ai_validacion(res).get("mensaje_usuario") or fallback)


def _ai_fecha_a_date(value: Optional[str]):
    if not value:
        return None
    for fmt in ("%Y-%m-%d", "%d/%m/%Y", "%d-%m-%Y"):
        try:
            return datetime.strptime(str(value).strip(), fmt).date()
        except ValueError:
            pass
    return None


def _ai_meses_desde(value: Optional[str]) -> Optional[int]:
    fecha = _ai_fecha_a_date(value)
    if not fecha:
        return None
    hoy = datetime.now().date()
    meses = (hoy.year - fecha.year) * 12 + (hoy.month - fecha.month)
    if hoy.day < fecha.day:
        meses -= 1
    return max(0, meses)


def _ai_metadata(res: Dict[str, Any]) -> Dict[str, Any]:
    return {
        "motor_ia": "alibaba",
        "modelo_ia": res.get("model"),
        "tiempo_ms": int(res.get("elapsed_ms") or 0),
    }


def _normalizar_dictamen_v2(value: Any) -> str:
    text = str(value or "").strip().lower()
    if text in {"aprobado", "requiere_revision", "rechazado"}:
        return text
    if "rechaz" in text or "no_coincide" in text:
        return "rechazado"
    if "revision" in text or "revisar" in text:
        return "requiere_revision"
    return "requiere_revision"


def _respuesta_alibaba_expediente(res: Dict[str, Any], nombre_candidato_registro: Optional[str] = None) -> Dict[str, Any]:
    analysis = res.get("analysis") or {}
    docs = analysis.get("documentos") if isinstance(analysis.get("documentos"), dict) else {}
    comps = analysis.get("comparaciones") if isinstance(analysis.get("comparaciones"), list) else []
    coincidencias = analysis.get("coincidencias") if isinstance(analysis.get("coincidencias"), dict) else {}
    alertas_raw = analysis.get("alertas") if isinstance(analysis.get("alertas"), list) else []
    recomendaciones = analysis.get("recomendaciones") if isinstance(analysis.get("recomendaciones"), list) else []
    dictamen = _normalizar_dictamen_v2(analysis.get("dictamen"))

    for meta in analysis.get("documentos_enviados") or []:
        if not isinstance(meta, dict):
            continue
        key = str(meta.get("key") or "").strip()
        if not key:
            continue
        doc = docs.get(key)
        if not isinstance(doc, dict):
            doc = {}
            docs[key] = doc
        doc.setdefault("archivo", meta.get("filename"))
        doc.setdefault("paginas_pdf", meta.get("pages_pdf"))
        doc.setdefault("paginas_analizadas", meta.get("pages_rendered"))

    docs_estado_revisar: set[str] = set()
    for comp in comps:
        if not isinstance(comp, dict):
            continue
        if comp.get("coincide") is False and _v2_comp_uses_comprobante_rfc(comp):
            comp["coincide"] = True
            comp["severidad"] = "aviso"
            comp["mensaje"] = (
                "RFC del comprobante de domicilio omitido: en recibos de servicios "
                "puede pertenecer al proveedor y no se usa para validar identidad."
            )
            docs_estado_revisar.update(_v2_comp_docs(comp))
            continue
        if comp.get("coincide") is False and _v2_comp_curp_compatible(comp):
            comp["coincide"] = True
            comp["severidad"] = "aviso"
            comp["mensaje"] = (
                "CURP base consistente entre documentos; la variacion detectada esta "
                "en caracteres finales susceptibles a lectura OCR/IA."
            )
            docs_estado_revisar.update(_v2_comp_docs(comp))
            continue
        if comp.get("coincide") is False and _v2_comp_rfc_compatible(comp):
            comp["coincide"] = True
            comp["severidad"] = "aviso"
            comp["mensaje"] = (
                "RFC base consistente entre documentos; la variacion detectada esta "
                "en la homoclave final susceptible a lectura OCR/IA."
            )
            docs_estado_revisar.update(_v2_comp_docs(comp))

    critical_docs = set()
    for comp in comps:
        if (
            isinstance(comp, dict)
            and comp.get("coincide") is False
            and str(comp.get("severidad") or "").lower() in {"critico", "critica", "alto"}
        ):
            critical_docs.update(_v2_comp_docs(comp))
    for doc_key in docs_estado_revisar:
        if doc_key in {"registro", "regla", "curp_principal", "documento"} or doc_key in critical_docs:
            continue
        doc = docs.get(doc_key)
        if isinstance(doc, dict) and str(doc.get("estado") or "").lower() == "no_coincide":
            doc["estado"] = "coincide"
            observaciones = doc.setdefault("observaciones", [])
            if isinstance(observaciones, list):
                observaciones.append("Estado ajustado por normalizacion del Motor V2; no hay falla critica vigente para este documento.")

    evaluables = [c for c in comps if isinstance(c, dict) and isinstance(c.get("coincide"), bool)]
    if evaluables:
        checks_totales = len(evaluables)
        checks_ok = sum(1 for c in evaluables if c.get("coincide") is True)
        checks_fallas = max(0, checks_totales - checks_ok)
    else:
        checks_totales = int(coincidencias.get("total") or 0)
        checks_ok = int(coincidencias.get("ok") or 0)
        checks_fallas = int(coincidencias.get("fallas") or max(0, checks_totales - checks_ok))

    alertas: List[str] = [str(a) for a in alertas_raw if str(a or "").strip()]
    hay_curp_critico = any(
        comp.get("coincide") is False
        and _v2_is_curp_comparison(comp)
        and str(comp.get("severidad") or "").lower() in {"critico", "critica", "alto"}
        for comp in evaluables
    )
    hay_rfc_critico = any(
        comp.get("coincide") is False
        and _v2_is_rfc_comparison(comp)
        and str(comp.get("severidad") or "").lower() in {"critico", "critica", "alto"}
        for comp in evaluables
    )
    if not hay_curp_critico:
        alertas = [
            alerta for alerta in alertas
            if "CURP NO COINCIDE ENTRE DOCUMENTOS" not in normalize_text(alerta)
        ]
    if not hay_rfc_critico:
        alertas = [
            alerta for alerta in alertas
            if "RFC NO COINCIDE" not in normalize_text(alerta)
        ]
    for comp in evaluables:
        if comp.get("coincide") is False and str(comp.get("severidad") or "").lower() in {"critico", "critica", "alto"}:
            msg = str(comp.get("mensaje") or comp.get("etiqueta") or "Comparacion critica no coincide").strip()
            if msg and msg not in alertas:
                alertas.append(msg)

    if not analysis.get("resumen_final"):
        if dictamen == "aprobado" and checks_fallas == 0:
            analysis["resumen_final"] = (
                "La informacion recibida es consistente entre los documentos revisados, "
                "cumple con las reglas documentales establecidas y corresponde al candidato registrado."
            )
        else:
            analysis["resumen_final"] = (
                "El expediente requiere revision documental antes del dictamen final. "
                "Revise las alertas y comparaciones marcadas por el motor V2."
            )

    confianza = analysis.get("confianza")
    try:
        confianza_num = int(round(float(confianza)))
    except Exception:
        confianza_num = int(round((checks_ok / checks_totales) * 100)) if checks_totales else None

    todo_coincide = bool(dictamen == "aprobado" and checks_totales > 0 and checks_ok == checks_totales and not alertas)
    datos_ref = analysis.get("datos_referencia") if isinstance(analysis.get("datos_referencia"), dict) else {}
    tiempos_fase = res.get("tiempos_fase_ms") if isinstance(res.get("tiempos_fase_ms"), dict) else None
    if tiempos_fase:
        tiempos_fase = {
            str(k): int(v)
            for k, v in tiempos_fase.items()
            if isinstance(v, (int, float)) or (isinstance(v, str) and v.strip().isdigit())
        }
    if not tiempos_fase:
        tiempos_fase = {"alibaba_crosscheck_ms": int(res.get("elapsed_ms") or 0)}

    return {
        "todo_coincide": todo_coincide,
        "foto_rechazada": False,
        "curp_definitivo": datos_ref.get("curp_principal"),
        "curp_fuente": "motor_v2_alibaba",
        "checks_ok": checks_ok,
        "checks_totales": checks_totales,
        "checks_fallas": checks_fallas,
        "alertas": alertas,
        "recomendaciones": recomendaciones,
        "identificacion_frente_score": confianza_num,
        "identificacion_reverso_score": confianza_num,
        "comparaciones": {},
        "comparaciones_v2": comps,
        "documentos_analizados_v2": docs,
        "datos_referencia_v2": datos_ref,
        "coincidencias_v2": {"total": checks_totales, "ok": checks_ok, "fallas": checks_fallas},
        "resumen_ia": str(analysis.get("resumen_final") or ""),
        "dictamen_ia": dictamen,
        "nombre_ocr": datos_ref.get("nombre_principal_documentos") or nombre_candidato_registro,
        "anio_nacimiento": None,
        "tipo_documento": "expediente_v2",
        "documentos_procesados": {k: True for k in docs.keys()},
        "datos_extraidos": {"motor_v2": analysis},
        "tiempo_proceso_ms": int(res.get("elapsed_ms") or 0),
        "tiempos_fase_ms": tiempos_fase,
        "modo_verificacion": "v2_alibaba_crosscheck",
        "motor_ia": "alibaba",
        "modelo_ia": res.get("model"),
        "nombre_candidato_registro": nombre_candidato_registro,
    }


def _v2_first_value(data: Dict[str, Any], keys: List[str]) -> Optional[str]:
    for key in keys:
        value = data.get(key)
        if isinstance(value, str) and value.strip():
            return value.strip()
        if value is not None and not isinstance(value, (dict, list)):
            text = str(value).strip()
            if text:
                return text
    return None


def _v2_bool(value: Any) -> Optional[bool]:
    if isinstance(value, bool):
        return value
    if value is None:
        return None
    text = str(value).strip().lower()
    if text in {"1", "true", "si", "yes", "ok"}:
        return True
    if text in {"0", "false", "no"}:
        return False
    return None


def _v2_clean_id(value: Optional[str]) -> Optional[str]:
    clean = re.sub(r"[^A-Z0-9]", "", normalize_text(value or ""))
    return clean or None


def _v2_clean_curp(value: Optional[str]) -> Optional[str]:
    raw = str(value or "")
    clean = _v2_clean_id(raw)
    if not clean:
        return None
    candidates: List[str] = []
    if len(clean) == 18:
        candidates.append(clean)
    candidates.extend(m.group(0) for m in re.finditer(r"[A-Z]{4}[A-Z0-9]{14}", clean))
    for candidate in candidates:
        if len(candidate) == 18 and validar_curp(candidate)[0]:
            return candidate
    return None


def _v2_clean_nss(value: Optional[str]) -> Optional[str]:
    raw = str(value or "")
    candidates = re.findall(r"\d{11}", raw)
    compact = re.sub(r"\D+", "", raw)
    if len(compact) == 11:
        candidates.insert(0, compact)
    for candidate in candidates:
        if validar_nss(candidate)[0]:
            return candidate
    return None


def _v2_edit_distance_limited(a: str, b: str, limit: int = 2) -> int:
    if abs(len(a) - len(b)) > limit:
        return limit + 1
    previous = list(range(len(b) + 1))
    for i, ca in enumerate(a, 1):
        current = [i]
        row_min = current[0]
        for j, cb in enumerate(b, 1):
            cost = 0 if ca == cb else 1
            value = min(previous[j] + 1, current[j - 1] + 1, previous[j - 1] + cost)
            current.append(value)
            row_min = min(row_min, value)
        if row_min > limit:
            return limit + 1
        previous = current
    return previous[-1]


def _v2_curp_similarity(a: Optional[str], b: Optional[str]) -> tuple[bool, str, str]:
    ca = _v2_clean_curp(a)
    cb = _v2_clean_curp(b)
    if not ca or not cb:
        return False, "critico", "CURP sin dato suficiente para comparar."
    if ca == cb:
        return True, "ok", "CURP consistente entre documentos."
    if ca[:13] == cb[:13] and _v2_edit_distance_limited(ca, cb, 1) <= 1:
        return True, "aviso", (
            "CURP corregida por consenso documental; la variacion detectada es "
            "de un caracter en la zona final susceptible a lectura OCR/IA."
        )
    return False, "critico", "CURP no coincide entre documentos."


def _v2_is_curp_comparison(comp: Dict[str, Any]) -> bool:
    categoria = normalize_text(str(comp.get("categoria") or ""))
    etiqueta = normalize_text(str(comp.get("etiqueta") or ""))
    return categoria == "CURP" or "CURP CONTRA" in etiqueta or "CURP ENTRE" in etiqueta


def _v2_comp_curp_compatible(comp: Dict[str, Any]) -> bool:
    if not _v2_is_curp_comparison(comp):
        return False
    ok, _, _ = _v2_curp_similarity(str(comp.get("valor_a") or ""), str(comp.get("valor_b") or ""))
    return bool(ok)


def _v2_rfc_similarity(a: Optional[str], b: Optional[str]) -> tuple[bool, str, str]:
    ca = _v2_clean_id(a)
    cb = _v2_clean_id(b)
    if not ca or not cb:
        return False, "critico", "RFC sin dato suficiente para comparar."
    if ca == cb:
        return True, "ok", "RFC consistente entre documentos."
    # El RFC de persona fisica se valida por la base de identidad:
    # 4 letras + fecha de nacimiento. La homoclave final es la parte que mas
    # se degrada en lecturas visuales y puede aparecer incompleta o con 1/I/O/0.
    if 12 <= len(ca) <= 13 and 12 <= len(cb) <= 13 and ca[:10] == cb[:10]:
        return True, "aviso", (
            "RFC base consistente entre documentos; la variacion detectada esta "
            "en la homoclave final susceptible a lectura OCR/IA."
        )
    if (
        12 <= len(ca) <= 13
        and 12 <= len(cb) <= 13
        and ca[:10] == cb[:10]
        and _v2_edit_distance_limited(ca, cb, 2) <= 2
    ):
        return True, "aviso", (
            "RFC base consistente entre documentos; la variacion detectada esta "
            "en caracteres susceptibles a lectura OCR/IA."
        )
    return False, "critico", "RFC no coincide entre documentos."


def _v2_is_rfc_comparison(comp: Dict[str, Any]) -> bool:
    categoria = normalize_text(str(comp.get("categoria") or ""))
    etiqueta = normalize_text(str(comp.get("etiqueta") or ""))
    return categoria == "RFC" or "RFC CONTRA" in etiqueta or "RFC ENTRE" in etiqueta


def _v2_comp_docs(comp: Dict[str, Any]) -> set[str]:
    docs: set[str] = set()
    for key in ("documento_a", "documento_b"):
        value = normalize_text(str(comp.get(key) or "")).lower()
        value = value.replace(" ", "_")
        if value:
            docs.add(value)
    return docs


def _v2_comp_uses_comprobante_rfc(comp: Dict[str, Any]) -> bool:
    return _v2_is_rfc_comparison(comp) and "comprobante_domicilio" in _v2_comp_docs(comp)


def _v2_comp_rfc_compatible(comp: Dict[str, Any]) -> bool:
    if not _v2_is_rfc_comparison(comp):
        return False
    ok, _, _ = _v2_rfc_similarity(str(comp.get("valor_a") or ""), str(comp.get("valor_b") or ""))
    return bool(ok)


def _v2_choose_rfc_principal(values: List[str]) -> Optional[str]:
    cleaned = [_v2_clean_id(v) for v in values]
    cleaned = [v for v in cleaned if v and 12 <= len(v) <= 13]
    if not cleaned:
        return None
    counts: Dict[str, int] = {}
    for value in cleaned:
        counts[value] = counts.get(value, 0) + 1
    return sorted(cleaned, key=lambda v: (-counts.get(v, 0), -len(v), cleaned.index(v)))[0]


def _v2_choose_curp_principal(values: List[str]) -> Optional[str]:
    valid = [_v2_clean_curp(v) for v in values]
    valid = [v for v in valid if v]
    if not valid:
        return None
    counts: Dict[str, int] = {}
    for value in valid:
        counts[value] = counts.get(value, 0) + 1
    return sorted(valid, key=lambda v: (-counts.get(v, 0), valid.index(v)))[0]


def _v2_pdf_page_count(doc: Dict[str, Any], summary: Any = None) -> Optional[int]:
    if isinstance(summary, dict):
        for key in ("paginas_pdf", "paginas", "paginas_analizadas"):
            try:
                value = summary.get(key)
                if value:
                    return int(value)
            except Exception:
                pass
        previo = summary.get("validacion_previa")
        if isinstance(previo, dict):
            for key in ("paginas_pdf", "paginas", "paginas_analizadas"):
                try:
                    value = previo.get(key)
                    if value:
                        return int(value)
                except Exception:
                    pass
    file_bytes = doc.get("bytes") or b""
    filename = str(doc.get("filename") or "")
    if not file_bytes or not filename.lower().endswith(".pdf"):
        return None
    try:
        pdf_doc = fitz.open(stream=file_bytes, filetype="pdf")
        count = int(pdf_doc.page_count or 0)
        pdf_doc.close()
        return count or None
    except Exception:
        return None


def _v2_names_match(a: Optional[str], b: Optional[str]) -> bool:
    na = normalize_text(a or "")
    nb = normalize_text(b or "")
    if not na or not nb:
        return False
    if na == nb:
        return True
    stop = {"DE", "DEL", "LA", "LAS", "LOS", "Y"}
    ta = {t for t in na.split() if len(t) > 1 and t not in stop}
    tb = {t for t in nb.split() if len(t) > 1 and t not in stop}
    if not ta or not tb:
        return False
    overlap = ta.intersection(tb)
    if len(overlap) >= 2 and (len(overlap) / max(1, min(len(ta), len(tb)))) >= 0.80:
        return True

    tokens_a = [t for t in na.split() if len(t) > 1 and t not in stop]
    tokens_b = [t for t in nb.split() if len(t) > 1 and t not in stop]
    used_b = set()
    fuzzy_matches = 0
    for token_a in tokens_a:
        best_idx = None
        best_dist = 99
        for idx, token_b in enumerate(tokens_b):
            if idx in used_b:
                continue
            if token_a[0] != token_b[0] or min(len(token_a), len(token_b)) < 4:
                continue
            limit = 1 if min(len(token_a), len(token_b)) < 8 else 2
            dist = _v2_edit_distance_limited(token_a, token_b, limit)
            if dist <= limit and dist < best_dist:
                best_idx = idx
                best_dist = dist
        if best_idx is not None:
            used_b.add(best_idx)
            fuzzy_matches += 1

    comparable_tokens = max(1, min(len(tokens_a), len(tokens_b)))
    required_matches = comparable_tokens if comparable_tokens <= 2 else int((comparable_tokens * 0.80) + 0.999)
    return fuzzy_matches >= required_matches


def _v2_months_value(data: Dict[str, Any]) -> Optional[int]:
    for key in ("meses_antiguedad", "antiguedad_meses"):
        try:
            value = data.get(key)
            if value is not None and str(value).strip() != "":
                return int(value)
        except Exception:
            pass
    return None


def _resultado_v2_reglas_expediente(
    documents: List[Dict[str, Any]],
    nombre_candidato: Optional[str],
    motivo: str = "rules",
) -> Dict[str, Any]:
    start = time.time()
    nombre_registro = str(nombre_candidato or "").strip()
    docs_out: Dict[str, Any] = {}
    readable: Dict[str, Dict[str, Any]] = {}
    comparaciones: List[Dict[str, Any]] = []
    alertas: List[str] = []
    recomendaciones: List[str] = []
    curp_principal_v2: Optional[str] = None

    def add_comp(categoria: str, etiqueta: str, doc_a: str, val_a: Any, doc_b: str, val_b: Any, coincide: bool, severidad: str, mensaje: str) -> None:
        comparaciones.append({
            "categoria": categoria,
            "etiqueta": etiqueta,
            "documento_a": doc_a,
            "valor_a": val_a,
            "documento_b": doc_b,
            "valor_b": val_b,
            "coincide": bool(coincide),
            "severidad": severidad,
            "mensaje": mensaje,
        })

    for doc in documents:
        key = str(doc.get("key") or "").strip()
        label = str(doc.get("label") or key).strip()
        filename = str(doc.get("filename") or f"{key}.pdf").strip()
        summary = doc.get("summary")
        previo = summary.get("validacion_previa") if isinstance(summary, dict) and isinstance(summary.get("validacion_previa"), dict) else {}
        page_count = _v2_pdf_page_count(doc, summary)

        out = {
            "estado": "no_leido",
            "tipo_detectado": previo.get("tipo_documento_detectado") or previo.get("tipo_documento"),
            "archivo": filename,
            "paginas_pdf": page_count,
            "nombre": None,
            "curp": None,
            "rfc": None,
            "nss": None,
            "fecha_nacimiento": _v2_first_value(previo, ["fecha_nacimiento"]),
            "fecha_emision": _v2_first_value(previo, ["fecha_emision", "fecha_expedicion", "fecha_documento"]),
            "domicilio": _v2_first_value(previo, ["domicilio", "direccion"]),
            "banco": _v2_first_value(previo, ["banco_detectado", "banco"]),
            "clabe": _v2_first_value(previo, ["clabe"]),
            "numero_cuenta": _v2_first_value(previo, ["numero_cuenta", "cuenta"]),
            "regimen_fiscal": _v2_first_value(previo, ["regimen_fiscal"]),
            "firma_detectada": _v2_bool(previo.get("firma_detectada")),
            "nombre_y_firma_lleno": _v2_bool(previo.get("nombre_y_firma_lleno")),
            "evidencia_insuficiente": _v2_bool(previo.get("evidencia_insuficiente")),
            "mensaje": None,
            "observaciones": [],
        }

        if not summary_is_usable(summary):
            out["estado"] = "requiere_revision"
            out["mensaje"] = "Lectura V2 pendiente; el documento no se marcara como falla documental hasta completar la lectura automatica."
            docs_out[key] = out
            alertas.append(f"{label}: lectura V2 pendiente para completar el cruce final.")
            continue

        out["nombre"] = _v2_first_value(previo, ["nombre", "nombre_completo", "nombre_propietario", "nombre_titular", "titular_cuenta"])
        out["curp"] = _v2_first_value(previo, ["curp", "curp_extraido", "curp_lectura_ia"])
        out["rfc"] = _v2_first_value(previo, ["rfc"])
        if key == "comprobante_domicilio":
            out["rfc"] = None
        out["nss"] = _v2_first_value(previo, ["nss", "nss_extraido", "nss_lectura_ia"])
        out["mensaje"] = _v2_first_value(previo, ["mensaje", "motivo_rechazo"])
        out["observaciones"] = list(previo.get("alertas") or previo.get("notas") or previo.get("observaciones") or [])
        raw_curp = out["curp"]
        if raw_curp:
            curp_limpia = _v2_clean_curp(raw_curp)
            if curp_limpia:
                out["curp"] = curp_limpia
            else:
                out["curp"] = None
                out["observaciones"].append(f"CURP descartada por lectura no valida: {raw_curp}.")
        raw_nss = out["nss"]
        if raw_nss:
            nss_limpio = _v2_clean_nss(raw_nss)
            if nss_limpio:
                out["nss"] = nss_limpio
            else:
                out["nss"] = None
                out["observaciones"].append(f"NSS descartado por lectura no valida: {raw_nss}.")
        out["estado"] = "coincide"

        rechazado = _v2_bool(previo.get("rechazado"))
        valido = _v2_bool(previo.get("valido"))
        revision_manual = _v2_bool(previo.get("revision_manual"))
        if rechazado is True:
            out["estado"] = "no_coincide"
            add_comp("Regla documental", f"{label} rechazado por lectura rapida", key, out["mensaje"], "Regla", "Documento valido", False, "critico", out["mensaje"] or f"{label} no cumple la regla documental.")
        elif valido is False or revision_manual is True:
            out["estado"] = "requiere_revision"
            msg = out["mensaje"] or f"{label} requiere revision manual por lectura V2."
            add_comp("Regla documental", f"{label} requiere revision", key, msg, "Regla", "Lectura automatica suficiente", False, "aviso", msg)

        expected_types_by_key = {
            "solicitud_interna": {"solicitud_interna", "solicitud___SPARTA_SECRET_REDACTED__"},
            "cv": {"cv"},
            "acta_nacimiento": {"acta_nacimiento"},
            "curp": {"curp"},
            "identificacion_oficial": {
                "identificacion_oficial",
                "ine",
                "residencia_permanente",
                "residencia_temporal",
                "pasaporte_mexicano",
                "pasaporte_extranjero",
            },
            "comprobante_domicilio": {"comprobante_domicilio"},
            "constancia_fiscal": {"constancia_fiscal"},
            "nss": {"nss"},
            "hoja_retencion": {"infonavit_fonacot", "carta_no_adeudo"},
            "__SPARTA_SECRET_REDACTED__": {"__SPARTA_SECRET_REDACTED__"},
        }
        detected_type = str(out.get("tipo_detectado") or "").strip()
        allowed_types = expected_types_by_key.get(key)
        if detected_type and allowed_types and detected_type not in allowed_types and detected_type != "desconocido":
            detected_label = user_document_name(detected_type)
            expected_label = label
            if key == "solicitud_interna" and detected_type == "cv":
                msg_tipo = (
                    "El archivo cargado en Solicitud interna parece ser CV o solicitud de trabajo. "
                    "Debe subirse en la seccion CV o solicitud de trabajo, y en Solicitud interna "
                    "debe cargarse el formato interno de MaxiKash."
                )
            else:
                msg_tipo = (
                    f"El archivo cargado en {expected_label} parece ser {detected_label}. "
                    "Debe subirse en la seccion correcta antes de continuar."
                )
            out["estado"] = "no_coincide"
            out["mensaje"] = msg_tipo
            out.setdefault("observaciones", []).append(msg_tipo)
            alertas.append(msg_tipo)
            add_comp(
                "Tipo de documento",
                f"{label} en seccion correcta",
                key,
                detected_label,
                "Campo esperado",
                expected_label,
                False,
                "critico",
                msg_tipo,
            )

        if key == "hoja_retencion" and detected_type == "carta_no_adeudo":
            firma_detectada = out.get("firma_detectada")
            nombre_y_firma_lleno = out.get("nombre_y_firma_lleno")
            evidencia_insuficiente = out.get("evidencia_insuficiente")
            problemas_carta: List[str] = []
            if not out.get("nombre"):
                problemas_carta.append("no se leyo nombre del declarante")
            if evidencia_insuficiente is True:
                problemas_carta.append("no hay evidencia suficiente de nombre y firma")
            if nombre_y_firma_lleno is False:
                problemas_carta.append("la linea de nombre completo y firma esta vacia o incompleta")
            if firma_detectada is False:
                problemas_carta.append("no se detecto firma")
            if problemas_carta:
                msg_carta = "La carta de no adeudo no esta completa: " + "; ".join(problemas_carta) + "."
                out["estado"] = "no_coincide"
                out["mensaje"] = msg_carta
                out.setdefault("observaciones", []).append(msg_carta)
                add_comp(
                    "Regla documental",
                    "Carta no adeudo llenada y firmada",
                    key,
                    "; ".join(problemas_carta),
                    "Regla",
                    "Nombre del candidato y firma visibles",
                    False,
                    "critico",
                    msg_carta,
                )

        if nombre_registro and out["nombre"] and key != "comprobante_domicilio":
            ok_name = _v2_names_match(nombre_registro, out["nombre"])
            add_comp(
                "Nombre",
                f"Nombre registro vs {label}",
                "Registro",
                nombre_registro,
                key,
                out["nombre"],
                ok_name,
                "ok" if ok_name else "critico",
                f"El nombre de {label} coincide con el candidato registrado." if ok_name else f"El nombre leido en {label} no coincide con el candidato registrado.",
            )
            if not ok_name:
                out["estado"] = "no_coincide"

        docs_out[key] = out
        readable[key] = out

    for key, max_months, label in (
        ("curp", 2, "CURP no mayor a 2 meses"),
        ("comprobante_domicilio", 3, "Comprobante de domicilio no mayor a 3 meses"),
        ("constancia_fiscal", 2, "Constancia fiscal no mayor a 2 meses"),
    ):
        summary = next((d.get("summary") for d in documents if str(d.get("key") or "") == key), None)
        previo = summary.get("validacion_previa") if isinstance(summary, dict) and isinstance(summary.get("validacion_previa"), dict) else {}
        months = _v2_months_value(previo)
        if months is None:
            continue
        ok = months <= max_months
        add_comp("Regla documental", label, key, f"{months} meses", "Regla", f"maximo {max_months} meses", ok, "ok" if ok else "critico", label if ok else f"{label}: el documento tiene {months} meses de antiguedad.")
        if not ok and key in docs_out:
            docs_out[key]["estado"] = "no_coincide"

    solicitud_pages = docs_out.get("solicitud_interna", {}).get("paginas_pdf")
    if solicitud_pages is not None:
        ok = int(solicitud_pages) >= 2
        add_comp("Regla documental", "Solicitud interna minimo 2 hojas", "solicitud_interna", solicitud_pages, "Regla", "2 hojas", ok, "ok" if ok else "critico", "La solicitud interna cumple minimo 2 hojas." if ok else "La solicitud interna debe tener minimo 2 hojas.")

    fiscal_pages = docs_out.get("constancia_fiscal", {}).get("paginas_pdf")
    if fiscal_pages is not None:
        ok = int(fiscal_pages) >= 2
        add_comp("Regla documental", "Constancia fiscal con 2 hojas", "constancia_fiscal", fiscal_pages, "Regla", "2 hojas", ok, "ok" if ok else "critico", "La constancia fiscal incluye 2 hojas." if ok else "La constancia fiscal debe incluir sus 2 hojas completas.")

    fiscal_summary = next((d.get("summary") for d in documents if str(d.get("key") or "") == "constancia_fiscal"), None)
    fiscal_prev = fiscal_summary.get("validacion_previa") if isinstance(fiscal_summary, dict) and isinstance(fiscal_summary.get("validacion_previa"), dict) else {}
    regimen_ok = _v2_bool(fiscal_prev.get("regimen_sueldos_salarios"))
    if regimen_ok is not None:
        add_comp("Regla documental", "Regimen de sueldos y salarios", "constancia_fiscal", fiscal_prev.get("regimen_fiscal") or fiscal_prev.get("regimenes_fiscales"), "Regla", "Sueldos y salarios", regimen_ok, "ok" if regimen_ok else "critico", "La constancia fiscal confirma regimen de sueldos y salarios." if regimen_ok else "La constancia fiscal no confirma el regimen de sueldos y salarios.")

    banco = (docs_out.get("__SPARTA_SECRET_REDACTED__") or {}).get("banco")
    if banco:
        ok = not is_digital_bank(str(banco))
        add_comp("Banco", "Banco fisico aceptado", "__SPARTA_SECRET_REDACTED__", banco, "Regla", "Banco fisico", ok, "ok" if ok else "critico", "El estado de cuenta corresponde a banco fisico." if ok else f"El banco detectado no es aceptado para carga: {banco}.")

    for field, label in (("curp", "CURP"), ("rfc", "RFC"), ("nss", "NSS")):
        if field == "curp":
            values = [(k, _v2_clean_curp(v.get(field))) for k, v in readable.items() if v.get(field)]
        elif field == "nss":
            values = [(k, _v2_clean_nss(v.get(field))) for k, v in readable.items() if v.get(field)]
        else:
            values = [(k, _v2_clean_id(v.get(field))) for k, v in readable.items() if v.get(field)]
        if field == "rfc":
            values = [(k, v) for k, v in values if k != "comprobante_domicilio"]
        values = [(k, v) for k, v in values if v]
        if len(values) < 2:
            continue
        if field == "curp":
            curp_principal_v2 = _v2_choose_curp_principal([v for _, v in values])
            if not curp_principal_v2:
                continue
            for other_key, other_value in values:
                ok, severity, msg = _v2_curp_similarity(curp_principal_v2, other_value)
                add_comp(
                    label,
                    f"{label} contra referencia documental",
                    "CURP principal",
                    curp_principal_v2,
                    other_key,
                    other_value,
                    ok,
                    severity,
                    msg,
                )
                if ok:
                    if other_value != curp_principal_v2 and other_key in docs_out:
                        docs_out[other_key]["curp_lectura_ia"] = other_value
                        docs_out[other_key]["curp"] = curp_principal_v2
                        obs = docs_out[other_key].setdefault("observaciones", [])
                        obs.append(f"CURP normalizada contra referencia documental; lectura IA: {other_value}.")
                else:
                    docs_out[other_key]["estado"] = "no_coincide"
            continue
        if field == "rfc":
            rfc_principal_v2 = _v2_choose_rfc_principal([v for _, v in values])
            if not rfc_principal_v2:
                continue
            for other_key, other_value in values:
                ok, severity, msg = _v2_rfc_similarity(rfc_principal_v2, other_value)
                add_comp(
                    label,
                    f"{label} contra referencia documental",
                    "RFC principal",
                    rfc_principal_v2,
                    other_key,
                    other_value,
                    ok,
                    severity,
                    msg,
                )
                if ok:
                    if other_value != rfc_principal_v2 and other_key in docs_out:
                        docs_out[other_key]["rfc_lectura_ia"] = other_value
                        docs_out[other_key]["rfc"] = rfc_principal_v2
                        obs = docs_out[other_key].setdefault("observaciones", [])
                        obs.append(f"RFC normalizado contra referencia documental; lectura IA: {other_value}.")
                else:
                    docs_out[other_key]["estado"] = "no_coincide"
            continue
        base_key, base_value = values[0]
        for other_key, other_value in values[1:]:
            ok = base_value == other_value
            add_comp(label, f"{label} entre documentos", base_key, base_value, other_key, other_value, ok, "ok" if ok else "critico", f"{label} consistente entre documentos." if ok else f"{label} no coincide entre {base_key} y {other_key}.")
            if not ok:
                docs_out[base_key]["estado"] = "no_coincide"
                docs_out[other_key]["estado"] = "no_coincide"

    evaluables = [c for c in comparaciones if isinstance(c.get("coincide"), bool)]
    total = len(evaluables)
    ok_count = sum(1 for c in evaluables if c.get("coincide") is True)
    fail_count = max(0, total - ok_count)
    has_critical = any(c.get("coincide") is False and c.get("severidad") == "critico" for c in evaluables)
    has_unread = any(d.get("estado") == "no_leido" for d in docs_out.values())
    has_warning = bool(alertas) or any(c.get("coincide") is False and c.get("severidad") != "critico" for c in evaluables)

    if has_critical:
        dictamen = "rechazado"
        resumen = "El expediente no puede aprobarse automaticamente: se detectaron diferencias criticas entre la informacion registrada y los documentos recibidos."
    elif has_unread or has_warning:
        dictamen = "requiere_revision"
        resumen = "El expediente requiere revision documental: el Motor V2 no cuenta con lectura suficiente en todos los documentos o hay observaciones pendientes."
    else:
        dictamen = "aprobado"
        resumen = "La informacion recibida es consistente entre los documentos revisados, cumple con las reglas documentales establecidas y corresponde al candidato registrado."

    if motivo != "rules":
        recomendaciones.append("Se uso dictamen local del Motor V2 porque el cruce profundo no respondio a tiempo.")

    datos_ref = {
        "nombre_registro": nombre_registro or None,
        "nombre_principal_documentos": next((v.get("nombre") for v in readable.values() if v.get("nombre")), None),
        "curp_principal": curp_principal_v2 or next((_v2_clean_curp(v.get("curp")) for v in readable.values() if _v2_clean_curp(v.get("curp"))), None),
        "rfc_principal": _v2_choose_rfc_principal([
            v.get("rfc") for k, v in readable.items() if k != "comprobante_domicilio" and v.get("rfc")
        ]) or next((v.get("rfc") for k, v in readable.items() if k != "comprobante_domicilio" and v.get("rfc")), None),
        "nss_principal": next((_v2_clean_nss(v.get("nss")) for v in readable.values() if _v2_clean_nss(v.get("nss"))), None),
    }

    return {
        "provider": "alibaba",
        "model": "Motor V2",
        "requested_model": "rules",
        "fallback_used": motivo != "rules",
        "usage": {},
        "analysis": {
            "dictamen": dictamen,
            "confianza": int(round((ok_count / total) * 100)) if total else 0,
            "resumen_final": resumen,
            "datos_referencia": datos_ref,
            "documentos": docs_out,
            "comparaciones": comparaciones,
            "coincidencias": {"total": total, "ok": ok_count, "fallas": fail_count},
            "alertas": alertas,
            "recomendaciones": recomendaciones,
            "documentos_enviados": [],
        },
        "elapsed_ms": int((time.time() - start) * 1000),
    }


def _ai_es_doc(res: Dict[str, Any], expected: str) -> bool:
    doc_type = str(_ai_extraccion(res).get("tipo_documento") or "")
    aliases = {
        "solicitud_interna": {"solicitud_interna", "solicitud___SPARTA_SECRET_REDACTED__"},
        "solicitud___SPARTA_SECRET_REDACTED__": {"solicitud_interna", "solicitud___SPARTA_SECRET_REDACTED__"},
        "identificacion_oficial": {"identificacion_oficial", "ine", "residencia_permanente", "residencia_temporal", "pasaporte_mexicano", "pasaporte_extranjero"},
        "cv": {"cv", "solicitud___SPARTA_SECRET_REDACTED__"},
        "infonavit_fonacot": {"infonavit_fonacot", "carta_no_adeudo"},
    }
    return doc_type in aliases.get(expected, {expected})


def _respuesta_alibaba_curp(res: Dict[str, Any]) -> Dict[str, Any]:
    campos = _ai_campos(res)
    errores = _ai_errores(res)
    fecha = campos.get("fecha_emision") or campos.get("fecha_expedicion")
    meses = _ai_meses_desde(fecha)
    valido = _ai_es_doc(res, "curp") and not errores
    curp_raw = campos.get("curp")
    return {
        # qwen3.5-flash puede confundir un caracter en CURP y aun parecer valida
        # por formato. Para carga rapida no mostramos identificadores exactos
        # si vienen solo de IA; se dejan como diagnostico interno.
        "curp_extraido": None,
        "curp_lectura_ia": curp_raw,
        "valido": bool(valido),
        "mensaje": _ai_mensaje(res, "CURP verificada." if valido else "No se pudo confirmar la CURP."),
        "nombre": campos.get("nombre_completo"),
        "es_reciente": None if meses is None else meses <= 2,
        "meses_antiguedad": meses,
        "fecha_emision": fecha,
        "parece_curp": _ai_es_doc(res, "curp"),
        "revision_manual": bool(_ai_advertencias(res)) and not errores,
        "rechazado": bool(errores),
        "motivo_rechazo": "alibaba_validacion_rapida" if errores else None,
        **_ai_metadata(res),
    }


def _respuesta_alibaba_fiscal(res: Dict[str, Any]) -> Dict[str, Any]:
    campos = _ai_campos(res)
    errores = _ai_errores(res)
    fecha = campos.get("fecha_emision") or campos.get("fecha_expedicion")
    meses = _ai_meses_desde(fecha)
    texto_regimen = " ".join([
        str(campos.get("actividad_economica") or ""),
        str(campos.get("regimen_fiscal") or ""),
        " ".join(str(x) for x in (campos.get("regimenes_fiscales") or [])),
    ]).upper()
    regimen_ok = "SUELDOS" in texto_regimen and "SALARIOS" in texto_regimen
    valido = _ai_es_doc(res, "constancia_fiscal") and not errores
    return {
        "valido": bool(valido),
        "mensaje": _ai_mensaje(res, "Constancia fiscal verificada." if valido else "No se pudo confirmar la constancia fiscal."),
        "nombre": campos.get("nombre_completo"),
        "rfc": campos.get("rfc"),
        "curp": campos.get("curp"),
        "fecha_emision": fecha,
        "meses_antiguedad": meses,
        "vigencia_ok": None if meses is None else meses <= 2,
        "actividad_asalariado": "ASALARIADO" in texto_regimen,
        "regimen_sueldos_salarios": bool(regimen_ok),
        "revision_manual": bool(_ai_advertencias(res)) and not errores,
        "rechazado": bool(errores),
        "motivo_rechazo": "alibaba_validacion_rapida" if errores else None,
        **_ai_metadata(res),
    }


def _respuesta_alibaba_comprobante(res: Dict[str, Any]) -> ComprobanteResponse:
    campos = _ai_campos(res)
    errores = _ai_errores(res)
    advertencias = _ai_advertencias(res)
    fecha = (
        campos.get("fecha_emision")
        or campos.get("fecha_vencimiento")
        or campos.get("periodo___SPARTA_SECRET_REDACTED___fin")
        or campos.get("periodo___SPARTA_SECRET_REDACTED___inicio")
    )
    meses = _ai_meses_desde(fecha)
    rechazado = bool(errores)
    resultado = "RECHAZADO" if rechazado else ("REVISION_MANUAL" if advertencias else "APROBADO")
    return ComprobanteResponse(
        tipo_comprobante=str(_ai_extraccion(res).get("subtipo") or "COMPROBANTE_DOMICILIO"),
        score_validacion=35 if rechazado else (70 if advertencias else 95),
        resultado=resultado,
        nombre_titular=campos.get("nombre_completo") or campos.get("titular_cuenta"),
        direccion=campos.get("domicilio"),
        fecha_documento=fecha,
        es_reciente=None if meses is None else meses <= 3,
        meses_antiguedad=meses,
        empresa=campos.get("entidad_emisora"),
        alertas=errores + advertencias,
        recomendacion=_ai_mensaje(res, "Comprobante verificado." if not rechazado else "Comprobante rechazado."),
        tiempo_proceso_ms=int(res.get("elapsed_ms") or 0),
        motor_ia="alibaba",
        modelo_ia=str(res.get("model") or ""),
    )


def _respuesta_alibaba___SPARTA_SECRET_REDACTED__(res: Dict[str, Any]) -> Dict[str, Any]:
    campos = _ai_campos(res)
    errores = _ai_errores(res)
    advertencias = _ai_advertencias(res)
    valido = _ai_es_doc(res, "__SPARTA_SECRET_REDACTED__") and not errores
    return {
        "valido": bool(valido),
        "rechazado": bool(errores),
        "revision_manual": bool(advertencias) and not errores,
        "mensaje": _ai_mensaje(res, "Estado de cuenta verificado." if valido else "No se pudo confirmar el estado de cuenta."),
        "banco_detectado": campos.get("banco"),
        "nombre_propietario": campos.get("titular_cuenta") or campos.get("nombre_completo"),
        "clabe": campos.get("clabe"),
        "numero_cuenta": campos.get("numero_cuenta"),
        "es_banco_fisico": bool(valido),
        "tiene_datos_titular": bool(campos.get("titular_cuenta") or campos.get("nombre_completo")),
        "alertas": errores + advertencias,
        **_ai_metadata(res),
    }


def _respuesta_alibaba_nss(res: Dict[str, Any]) -> Dict[str, Any]:
    campos = _ai_campos(res)
    errores = _ai_errores(res)
    nss_raw = campos.get("nss")
    nss_ok = validar_nss(str(nss_raw))[0] if nss_raw else False
    return {
        "nss_extraido": nss_raw if nss_ok else None,
        "nss_lectura_ia": nss_raw,
        "valido": bool(_ai_es_doc(res, "nss") and not errores),
        "mensaje": _ai_mensaje(res, "NSS detectado."),
        "nombre": campos.get("nombre_completo"),
        "curp": campos.get("curp"),
        "revision_manual": bool(_ai_advertencias(res)) or bool(errores),
        "rechazado": bool(errores and not _ai_es_doc(res, "nss")),
        "motivo_rechazo": "alibaba_validacion_rapida" if errores else None,
        **_ai_metadata(res),
    }


def _respuesta_alibaba_identificacion(res: Dict[str, Any]) -> Dict[str, Any]:
    campos = _ai_campos(res)
    extraccion = _ai_extraccion(res)
    doc_type = str(extraccion.get("tipo_documento") or "")
    fr = extraccion.get("frente_reverso") or {}
    errores = _ai_errores(res)
    advertencias = _ai_advertencias(res)
    es_identificacion = _ai_es_doc(res, "identificacion_oficial")
    valido = es_identificacion and not errores
    indicadores = {
        "mrz": bool(fr.get("reverso_detectado")),
        "ine": doc_type in {"ine", "identificacion_oficial"},
        "elector": doc_type in {"ine", "identificacion_oficial"},
        "inm_residencia": doc_type in {"residencia_permanente", "residencia_temporal"},
        "pasaporte": doc_type in {"pasaporte_mexicano", "pasaporte_extranjero"},
        "curp": bool(campos.get("curp")),
    }
    return {
        "valido": bool(valido),
        "revision_manual": bool(advertencias) or (not bool(valido)),
        "rechazado": bool(errores) or (not es_identificacion),
        "mensaje": _ai_mensaje(res, "Identificacion oficial verificada." if valido else "No se pudo confirmar la identificacion oficial."),
        "indicadores": indicadores,
        "tipo_identificacion": doc_type if valido else None,
        "nombre": campos.get("nombre_completo"),
        "curp": campos.get("curp"),
        "fecha_vencimiento": campos.get("fecha_vencimiento"),
        **_ai_metadata(res),
    }


def _normalizar_texto_precheck(texto: str) -> str:
    """Normaliza texto OCR para detección rápida de identificación oficial."""
    if not texto:
        return ""
    reemplazos = {
        "Á": "A", "É": "E", "Í": "I", "Ó": "O", "Ú": "U", "Ü": "U", "Ñ": "N",
        "á": "A", "é": "E", "í": "I", "ó": "O", "ú": "U", "ü": "U", "ñ": "N",
    }
    for src, dst in reemplazos.items():
        texto = texto.replace(src, dst)
    return re.sub(r"\s+", " ", texto.upper()).strip()


def _indicadores_identificacion(texto: str) -> Dict[str, bool]:
    """Devuelve señales textuales suficientes para precheck rápido de ID."""
    t = _normalizar_texto_precheck(texto)
    lineas_mrz = re.findall(r"[A-Z0-9<]{20,}", t)
    tiene_mrz = bool(lineas_mrz and any("<<" in l or l.count("<") >= 2 for l in lineas_mrz))
    tiene_mrz_ine = bool(
        re.search(r"(?:^|\s)(?:ID\s*<?\s*MEX|IDMEX|I\s*<\s*MEX|I<MEX)|IDMEX", t)
        or (tiene_mrz and "MEX" in t and not re.search(r"\bP\s*<\s*[A-Z]{3}|P<[A-Z]{3}", t))
    )
    tiene_ine = bool(re.search(r"INSTITUTO\s+NACIONAL\s+ELECTORAL|CREDENCIAL\s+PARA\s+VOT", t) or tiene_mrz_ine)
    tiene_elector = bool(re.search(r"CLAVE\s*DE\s*ELECTOR|CLAVEDEELECTOR|CLAVE.{0,4}ELECTOR|SECCION\s*\d|ELECTORAL|VOTAR", t))
    tiene_curp = bool(re.search(r"[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d", t))
    tiene_inm = bool(re.search(r"INSTITUTO\s+NACIONAL\s+DE\s+MIGRACION|MIGRACION|INM|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|RESIDENTE\s+(TEMPORAL|PERMANENTE)|NUE\s*[.:]?\s*\d", t))
    tiene_senales_pasaporte = bool(re.search(r"PASAPORTE|PASSPORT|PASSAPORT|PASAPORTE\s*NO|NO\.?\s*DE\s*PASAPORTE", t))
    tiene_campos_pasaporte = bool(
        re.search(r"ISSUE\s+DATE|EXPIRY\s+DATE|DATE\s+DE\s+D[EI][LÍI]V|DATE\s+D['E]XP|HOLDER'?S\s+SIGNATURE|PLACE\s+OF\s+BIRTH", t)
    )
    tiene_mrz_pasaporte = bool(re.search(r"\bP\s*<\s*[A-Z]{3}|P<[A-Z]{3}", t))
    tiene_pasaporte = bool(
        tiene_senales_pasaporte
        or tiene_mrz_pasaporte
        or (tiene_mrz and tiene_campos_pasaporte)
        or (re.search(r"ESTADOS\s+UNIDOS\s+MEXICANOS", t) and re.search(r"CLAVE\s+DEL\s+PAIS|MEX\b|PASAPORTE", t))
    )
    if tiene_mrz_ine and not tiene_senales_pasaporte:
        tiene_pasaporte = False
    return {
        "mrz": tiene_mrz,
        "ine": tiene_ine,
        "elector": tiene_elector,
        "curp": tiene_curp,
        "inm_residencia": tiene_inm,
        "pasaporte": tiene_pasaporte,
    }


def _parece_identificacion_oficial(texto: str) -> bool:
    indicadores = _indicadores_identificacion(texto)
    if indicadores["ine"] or indicadores["inm_residencia"] or indicadores["pasaporte"]:
        return True
    if indicadores["mrz"] and not indicadores["curp"]:
        return True
    return indicadores["curp"] and indicadores["elector"]


def _tipo_identificacion_desde_indicadores(indicadores: Dict[str, bool]) -> str:
    if indicadores.get("pasaporte"):
        return "pasaporte"
    if indicadores.get("inm_residencia"):
        return "residencia"
    if indicadores.get("ine") or indicadores.get("elector"):
        return "ine"
    if indicadores.get("mrz"):
        return "identificacion"
    return "identificacion"


def _extraer_texto_pdf_rapido(pdf_bytes: bytes, max_paginas: int = 2) -> Dict[str, Any]:
    """
    Extrae texto mínimo para precheck. Primero usa capa de texto del PDF; si no hay,
    renderiza máximo 2 páginas a baja resolución y aplica OCR rápido.
    """
    if not PYMUPDF_AVAILABLE or not pdf_bytes or len(pdf_bytes) < 100:
        return {"texto": "", "paginas": 0, "modo": "sin_pymupdf"}

    texto = ""
    paginas = 0
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        paginas = doc.page_count
        for i, page in enumerate(doc):
            if i >= max_paginas:
                break
            texto += page.get_text() + "\n"
        if texto.strip():
            doc.close()
            return {"texto": texto, "paginas": paginas, "modo": "texto_pdf"}

        service = VerificacionService()
        # Pasaportes/IDs fotografiados suelen venir con margen amplio. Este recorte
        # es barato y evita probar rotaciones caras cuando el documento ya se lee derecho.
        if paginas > 0:
            try:
                page = doc[0]
                rect = page.rect
                clip = fitz.Rect(0, rect.height * 0.20, rect.width, rect.height * 0.92)
                pix = page.get_pixmap(dpi=145, clip=clip)
                img_bytes = pix.tobytes("png")
                texto_crop = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1800)
                if _parece_identificacion_oficial(texto_crop):
                    doc.close()
                    return {"texto": texto_crop, "paginas": paginas, "modo": "ocr_zona_contenido_145dpi"}
                texto = texto_crop or ""
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: crop inicial fallo: {e}")
        # Si es una sola hoja escaneada, puede traer frente/reverso de INE girados.
        # Probar una rotacion de hoja completa temprano es mucho mas barato que agotar
        # todos los OCR normales y luego intentar rescatar el documento.
        if paginas == 1:
            try:
                from io import BytesIO
                from PIL import Image
                import pytesseract

                page = doc[0]
                pix = page.get_pixmap(dpi=180)
                img = Image.open(BytesIO(pix.tobytes("png")))
                pytesseract.pytesseract.tesseract_cmd = settings.tesseract_cmd
                for angulo in (90, 180):
                    bio = BytesIO()
                    img.rotate(angulo, expand=True).save(bio, format="PNG")
                    texto_rotado = pytesseract.image_to_string(
                        Image.open(BytesIO(bio.getvalue())).convert("L"),
                        config="--oem 3 --psm 6 -l eng -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0__SPARTA_PASSWORD_REDACTED__<"
                    ).upper()
                    if _parece_identificacion_oficial(texto_rotado):
                        doc.close()
                        return {"texto": texto_rotado, "paginas": paginas, "modo": f"ocr_hoja_rotada_temprana_180dpi_rot{angulo}"}
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: rotacion temprana fallo: {e}")
        # Pasaportes/IDs fotografiados suelen venir girados o con mucho margen blanco.
        # Probar primero la zona de contenido evita gastar OCR en toda la hoja.
        if paginas > 0:
            try:
                page = doc[0]
                rect = page.rect
                clip = fitz.Rect(0, rect.height * 0.20, rect.width, rect.height * 0.92)
                pix = page.get_pixmap(dpi=145, clip=clip)
                img_bytes = pix.tobytes("png")
                texto_crop = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1800)
                if _parece_identificacion_oficial(texto_crop):
                    doc.close()
                    return {"texto": texto_crop, "paginas": paginas, "modo": "ocr_zona_contenido_145dpi"}
                texto = texto_crop or ""
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: crop inicial falló: {e}")
        for dpi, max_ancho in ((95, 1000), (150, 1400)):
            texto_full = ""
            for i, page in enumerate(doc):
                if i >= max_paginas:
                    break
                pix = page.get_pixmap(dpi=dpi)
                img_bytes = pix.tobytes("png")
                texto_full += service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=max_ancho) + "\n"
            texto_total = (texto or "") + "\n" + texto_full
            if _parece_identificacion_oficial(texto_total):
                doc.close()
                return {"texto": texto_total, "paginas": paginas, "modo": f"ocr_rapido_{dpi}dpi"}
            texto = texto_total

        # Fallback ligero para INE escaneada: enfoca zona de datos/clave sin correr validación profunda.
        if paginas > 0:
            try:
                page = doc[0]
                pix = page.get_pixmap(dpi=120)
                img_bytes = pix.tobytes("png")
                texto_dedicado = service.ocr_analyzer._extraer_mrz_dedicado(img_bytes)
                curp_zona = service.ocr_analyzer._extraer_curp_zona_dedicada(img_bytes)
                texto = (texto or "") + "\n" + (texto_dedicado or "")
                if curp_zona:
                    texto += "\nCURP " + curp_zona
                if _parece_identificacion_oficial(texto):
                    doc.close()
                    return {"texto": texto, "paginas": paginas, "modo": "ocr_zona_datos_120dpi"}
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: fallback zona datos falló: {e}")

        # Pasaporte/ID fotografiado dentro de una página: suele venir con mucho margen blanco.
        # Recortar la franja central-inferior evita que el OCR lea solo el fondo.
        if paginas > 0:
            try:
                page = doc[0]
                rect = page.rect
                clip = fitz.Rect(0, rect.height * 0.22, rect.width, rect.height * 0.90)
                for dpi in (160, 220):
                    pix = page.get_pixmap(dpi=dpi, clip=clip)
                    img_bytes = pix.tobytes("png")
                    texto_crop = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=2200)
                    texto_total = (texto or "") + "\n" + (texto_crop or "")
                    if _parece_identificacion_oficial(texto_total):
                        doc.close()
                        return {"texto": texto_total, "paginas": paginas, "modo": f"ocr_zona_contenido_{dpi}dpi"}
                    texto = texto_total
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: fallback zona contenido falló: {e}")
        # INE escaneada en una sola hoja: a veces el frente/reverso vienen girados
        # y con mucho margen blanco. Si lo normal fallo, probamos rotaciones para leer IDMEX/MRZ.
        if paginas > 0:
            try:
                from io import BytesIO
                from PIL import Image

                def _ocr_rotaciones_png(img_bytes: bytes, prefijo: str) -> Optional[Dict[str, str]]:
                    imagen = Image.open(BytesIO(img_bytes))
                    for angulo in (90, 180, 270):
                        bio = BytesIO()
                        imagen.rotate(angulo, expand=True).save(bio, format="PNG")
                        texto_rotado = service.ocr_analyzer.extraer_texto_rapido(bio.getvalue(), max_ancho=2400)
                        texto_total_rotado = (texto or "") + "\n" + (texto_rotado or "")
                        if _parece_identificacion_oficial(texto_total_rotado):
                            return {"texto": texto_total_rotado, "modo": f"{prefijo}_rot{angulo}"}
                    return None

                page = doc[0]
                pix = page.get_pixmap(dpi=180)
                rotado = _ocr_rotaciones_png(pix.tobytes("png"), "ocr_hoja_rotada_180dpi")
                if rotado:
                    doc.close()
                    return {"texto": rotado["texto"], "paginas": paginas, "modo": rotado["modo"]}

                rect = page.rect
                clip = fitz.Rect(rect.width * 0.12, rect.height * 0.32, rect.width * 0.82, rect.height * 0.72)
                pix = page.get_pixmap(dpi=230, clip=clip)
                rotado = _ocr_rotaciones_png(pix.tobytes("png"), "ocr_zona_mrz_rotada_230dpi")
                if rotado:
                    doc.close()
                    return {"texto": rotado["texto"], "paginas": paginas, "modo": rotado["modo"]}
            except Exception as e:
                logger.warning(f"precheck_identificacion_pdf: fallback rotado fallo: {e}")
        doc.close()
        return {"texto": texto, "paginas": paginas, "modo": "ocr_rapido"}
    except Exception as e:
        logger.warning(f"precheck_identificacion_pdf: error leyendo PDF: {e}")
        return {"texto": texto, "paginas": paginas, "modo": "error"}


def _extraer_texto_identificacion_rotada_pdf(pdf_bytes: bytes) -> Dict[str, Any]:
    """OCR corto para INE escaneada con reverso/frente girado dentro de una hoja PDF."""
    if not PYMUPDF_AVAILABLE or not pdf_bytes or len(pdf_bytes) < 100:
        return {"texto": "", "paginas": 0, "modo": "rotado_sin_pymupdf"}
    try:
        from io import BytesIO
        from PIL import Image
        import pytesseract

        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        paginas = doc.page_count
        if paginas <= 0:
            doc.close()
            return {"texto": "", "paginas": paginas, "modo": "rotado_sin_paginas"}

        pytesseract.pytesseract.tesseract_cmd = settings.tesseract_cmd
        page = doc[0]
        rect = page.rect
        pruebas = [
            ("ocr_hoja_rotada_180dpi", None, 180),
            ("ocr_zona_mrz_rotada_230dpi", fitz.Rect(rect.width * 0.12, rect.height * 0.32, rect.width * 0.82, rect.height * 0.72), 230),
        ]
        acumulado = ""
        for prefijo, clip, dpi in pruebas:
            pix = page.get_pixmap(dpi=dpi, clip=clip) if clip else page.get_pixmap(dpi=dpi)
            img = Image.open(BytesIO(pix.tobytes("png")))
            for angulo in (90, 180, 270):
                bio = BytesIO()
                img.rotate(angulo, expand=True).save(bio, format="PNG")
                texto = pytesseract.image_to_string(
                    Image.open(BytesIO(bio.getvalue())).convert("L"),
                    config="--oem 3 --psm 6 -l eng -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0__SPARTA_PASSWORD_REDACTED__<"
                ).upper()
                acumulado = (acumulado + "\n" + (texto or "")).strip()
                if _parece_identificacion_oficial(acumulado):
                    doc.close()
                    return {"texto": acumulado, "paginas": paginas, "modo": f"{prefijo}_rot{angulo}"}
        doc.close()
        return {"texto": acumulado, "paginas": paginas, "modo": "rotado_no_detectado"}
    except Exception as e:
        logger.warning(f"extraer_texto_identificacion_rotada_pdf: {e}")
        return {"texto": "", "paginas": 0, "modo": "rotado_error"}


def _texto_pdf_embebido_rapido(pdf_bytes: bytes, max_paginas: int = 2) -> Dict[str, Any]:
    """Lee solo texto embebido del PDF, sin OCR, para no bloquear con escaneos."""
    if not PYMUPDF_AVAILABLE:
        return {"texto": "", "paginas": 0, "modo": "pymupdf_no_disponible"}
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        paginas = len(doc)
        texto = []
        for i, page in enumerate(doc):
            if i >= max_paginas:
                break
            texto.append(page.get_text("text") or "")
        doc.close()
        return {"texto": "\n".join(texto), "paginas": paginas, "modo": "texto_pdf_embebido"}
    except Exception as e:
        logger.warning(f"texto_pdf_embebido_rapido: error leyendo PDF: {e}")
        return {"texto": "", "paginas": 0, "modo": "error"}


def _normalizar_ascii_precheck(texto: str) -> str:
    texto = unicodedata.normalize("NFKD", texto or "")
    texto = "".join(c for c in texto if not unicodedata.combining(c))
    return re.sub(r"\s+", " ", texto.upper()).strip()


def _parsear_fecha_curp_rapida(texto: str) -> Optional[datetime]:
    meses = {
        "ENERO": 1, "FEBRERO": 2, "MARZO": 3, "ABRIL": 4, "MAYO": 5, "JUNIO": 6,
        "JULIO": 7, "AGOSTO": 8, "SEPTIEMBRE": 9, "SETIEMBRE": 9, "OCTUBRE": 10,
        "NOVIEMBRE": 11, "DICIEMBRE": 12,
    }
    t = _normalizar_ascii_precheck(texto)
    m = re.search(r"\bA\s+(\d{1,2})\s+DE\s+([A-Z]+)\s+DE\s+(\d{4})\b", t)
    if not m:
        m = re.search(r"\b(\d{1,2})\s+DE\s+([A-Z]+)\s+DE\s+(\d{4})\b", t)
    if not m:
        return None
    mes = meses.get(m.group(2))
    if not mes:
        return None
    try:
        return datetime(int(m.group(3)), mes, int(m.group(1)))
    except ValueError:
        return None


def _extraer_datos_curp_pdf_rapido(pdf_bytes: bytes) -> Dict[str, Any]:
    """Ruta barata para constancia CURP: texto embebido o Tesseract ligero, sin Paddle."""
    resultado = {
        "nombre": None,
        "curp": None,
        "fecha_emision": None,
        "es_reciente": None,
        "meses_antiguedad": None,
        "parece_curp": False,
        "texto": "",
        "modo": "sin_texto",
    }
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    texto = info.get("texto") or ""
    modo = info.get("modo") or "texto_pdf_embebido"

    if not texto.strip() and PYMUPDF_AVAILABLE:
        try:
            from io import BytesIO
            from PIL import Image
            import pytesseract

            pytesseract.pytesseract.tesseract_cmd = settings.tesseract_cmd
            doc = fitz.open(stream=pdf_bytes, filetype="pdf")
            partes = []
            for i, page in enumerate(doc):
                if i >= 2:
                    break
                pix = page.get_pixmap(dpi=140)
                img = Image.open(BytesIO(pix.tobytes("png"))).convert("L")
                partes.append(pytesseract.image_to_string(img, lang="spa+eng", config="--oem 3 --psm 6"))
            doc.close()
            texto = "\n".join(partes)
            modo = "ocr_tesseract_curp_140dpi"
        except Exception as e:
            logger.debug(f"extraer_datos_curp_pdf_rapido OCR fallo: {e}")

    texto_norm = _normalizar_ascii_precheck(texto)
    resultado["texto"] = texto_norm
    resultado["modo"] = modo
    resultado["parece_curp"] = bool(
        ("CONSTANCIA" in texto_norm and ("CURP" in texto_norm or "CLAVE UNICA" in texto_norm or "REGISTRO DE POBLACION" in texto_norm))
        or ("ESTADOS UNIDOS MEXICANOS" in texto_norm and "CLAVE UNICA" in texto_norm)
        or ("RENAPO" in texto_norm and ("CURP" in texto_norm or "CONSTANCIA" in texto_norm))
    )

    curp_regex = r"\b[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d\b"
    m_curp = re.search(curp_regex, texto_norm)
    if m_curp and validar_curp(m_curp.group(0))[0]:
        resultado["curp"] = m_curp.group(0)
    else:
        compacto = re.sub(r"[^A-Z0-9]", "", texto_norm)
        for i in range(0, max(0, len(compacto) - 17)):
            cand = compacto[i:i + 18]
            if validar_curp(cand)[0]:
                resultado["curp"] = cand
                break

    if resultado["curp"]:
        curp_pos = texto_norm.find(resultado["curp"])
        antes = texto_norm[:curp_pos] if curp_pos > 0 else texto_norm
        m_nombre = re.search(r"NOMBRE\s+([A-Z\s]{6,80}?)(?:\s+ENTIDAD|\s+CURP|\s+CLAVE|\s+FECHA|$)", texto_norm)
        if m_nombre:
            nombre = re.sub(r"[^A-Z\s]", " ", m_nombre.group(1))
            nombre = re.sub(r"\s+", " ", nombre).strip()
            if len(nombre.split()) >= 3:
                resultado["nombre"] = nombre
        elif antes:
            candidatos = [ln.strip() for ln in antes.split("\n") if ln.strip()]
            for ln in reversed(candidatos[-8:]):
                limpio = re.sub(r"[^A-Z\s]", " ", _normalizar_ascii_precheck(ln))
                limpio = re.sub(r"\s+", " ", limpio).strip()
                if 3 <= len(limpio.split()) <= 6:
                    resultado["nombre"] = limpio
                    break

    fecha = _parsear_fecha_curp_rapida(texto_norm)
    if fecha:
        resultado["fecha_emision"] = fecha.strftime("%d/%m/%Y")
        meses = (datetime.now() - fecha).days / 30.44
        resultado["meses_antiguedad"] = round(meses, 1)
        resultado["es_reciente"] = meses <= 3.0
    return resultado


def _rechazo_identificacion_por_texto_equivocado(texto: str, paginas: int, modo: str, inicio: float) -> Optional[Dict[str, Any]]:
    """Rechaza rapido PDFs que claramente pertenecen a otro campo documental."""
    texto_upper = (texto or "").upper()
    if not texto_upper.strip():
        return None
    indicadores = _indicadores_identificacion(texto_upper)
    if _parece_identificacion_oficial(texto_upper):
        return None
    reglas = [
        (
            r"ASIGNACI[OÓ]N\s+O\s+LOCALIZACI[OÓ]N|N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b",
            "nss_en_identificacion",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
            "curp_en_identificacion",
            "El documento es una constancia CURP. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|C[ÉE]DULA\s+DE\s+IDENTIFICACI[OÓ]N\s+FISCAL",
            "constancia_fiscal_en_identificacion",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICADO\s+DE\s+NACIMIENTO",
            "acta_en_identificacion",
            "El documento es un acta de nacimiento. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"ESTADO\s+DE\s+CUENTA|CUENTA\s+CLABE|NO\.?\s+DE\s+CUENTA|N[UÚ]MERO\s+DE\s+CUENTA|TARJETA\s+N[OÓ]MINA|BBVA|BANCOMER|SANTANDER|BANORTE|HSBC|SCOTIABANK|BANCO\s+AZTECA|NU\s+M[EÉ]XICO",
            "__SPARTA_SECRET_REDACTED___en_identificacion",
            "El documento corresponde a estado de cuenta bancario. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"COMPROBANTE\s+DE\s+DOMICILIO|RECIBO\s+DE\s+(LUZ|AGUA|TEL[EÉ]FONO)|COMISI[OÓ]N\s+FEDERAL\s+DE\s+ELECTRICIDAD|\bCFE\b|TELMEX|IZZI|TOTALPLAY",
            "comprobante_domicilio_en_identificacion",
            "El documento corresponde a comprobante de domicilio. En este campo solo se acepta identificacion oficial.",
        ),
        (
            r"SOLICITUD\s+(INTERNA|DE\s+EMPLEO|EMPLEO|DE\s+TRABAJO|TRABAJO)|CURR.?CULUM|CURRICULUM\s+VITAE|EXPERIENCIA\s+LABORAL",
            "solicitud_en_identificacion",
            "El documento corresponde a solicitud/CV. En este campo solo se acepta identificacion oficial.",
        ),
    ]
    for patron, motivo, mensaje in reglas:
        if re.search(patron, texto_upper):
            return _respuesta_rechazo(
                motivo,
                mensaje,
                paginas=paginas,
                indicadores=indicadores,
                modo=modo,
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
    return None


def _detectar_documento_equivocado_identificacion_pdf(pdf_bytes: bytes, inicio: float) -> Optional[Dict[str, Any]]:
    """Precheck barato: texto embebido y OCR de primera pagina a baja resolucion."""
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    rechazo = _rechazo_identificacion_por_texto_equivocado(
        info.get("texto") or "",
        int(info.get("paginas") or 0),
        "documento_equivocado_texto_embebido",
        inicio,
    )
    if rechazo:
        return rechazo
    if not PYMUPDF_AVAILABLE:
        return None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        paginas = doc.page_count
        if paginas <= 0:
            doc.close()
            return None
        page = doc[0]
        pix = page.get_pixmap(dpi=105)
        img_bytes = pix.tobytes("png")
        doc.close()
        service = VerificacionService()
        texto = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1000)
        return _rechazo_identificacion_por_texto_equivocado(
            texto,
            paginas,
            "documento_equivocado_ocr_rapido",
            inicio,
        )
    except Exception as e:
        logger.debug(f"detectar documento equivocado identificacion fallo: {e}")
        return None


def _rechazo_curp_por_texto_equivocado(texto: str, inicio: float, modo: str) -> Optional[Dict[str, Any]]:
    """Rechaza documentos claramente equivocados antes del extractor CURP pesado."""
    texto_upper = (texto or "").upper()
    if not texto_upper.strip():
        return None
    if re.search(r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N", texto_upper):
        return None
    reglas = [
        (
            r"PASAPORTE|CREDENCIAL\s+PARA\s+VOTAR|INSTITUTO\s+NACIONAL\s+ELECTORAL|\bINE\b|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|INSTITUTO\s+NACIONAL\s+DE\s+MIGRACI[OÓ]N|\bINM\b",
            "identificacion_en_curp",
            "El documento corresponde a identificacion oficial. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
        (
            r"SOLICITUD\s+(INTERNA|DE\s+EMPLEO|EMPLEO|DE\s+TRABAJO|TRABAJO)|CURR.?CULUM|CURRICULUM\s+VITAE|EXPERIENCIA\s+LABORAL",
            "solicitud_en_curp",
            "El documento corresponde a solicitud/CV. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
        (
            r"ASIGNACI[OÓ]N\s+O\s+LOCALIZACI[OÓ]N|N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b",
            "nss_en_curp",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|\bSAT\b",
            "constancia_fiscal_en_curp",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICADO\s+DE\s+NACIMIENTO",
            "acta_en_curp",
            "El documento es un acta de nacimiento. En este campo solo se acepta constancia CURP del RENAPO.",
        ),
    ]
    for patron, motivo, mensaje in reglas:
        if re.search(patron, texto_upper):
            return _respuesta_rechazo(
                motivo,
                mensaje,
                curp_extraido=None,
                es_reciente=None,
                meses_antiguedad=None,
                fecha_emision=None,
                modo=modo,
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
    return None


def _detectar_documento_equivocado_curp_pdf(pdf_bytes: bytes, inicio: float) -> Optional[Dict[str, Any]]:
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    rechazo = _rechazo_curp_por_texto_equivocado(
        info.get("texto") or "",
        inicio,
        "documento_equivocado_texto_embebido",
    )
    if rechazo:
        return rechazo
    if not PYMUPDF_AVAILABLE:
        return None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        if doc.page_count <= 0:
            doc.close()
            return None
        page = doc[0]
        pix = page.get_pixmap(dpi=105)
        img_bytes = pix.tobytes("png")
        doc.close()
        service = VerificacionService()
        texto = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1000)
        return _rechazo_curp_por_texto_equivocado(
            texto,
            inicio,
            "documento_equivocado_ocr_rapido",
        )
    except Exception as e:
        logger.debug(f"detectar documento equivocado curp fallo: {e}")
        return None


def _rechazo___SPARTA_SECRET_REDACTED___por_texto_equivocado(texto: str, inicio: float, modo: str) -> Optional[Dict[str, Any]]:
    """Rechaza documentos claramente equivocados antes del OCR pesado de estado de cuenta."""
    texto_upper = _normalizar_texto_precheck(texto)
    compacto = re.sub(r"[^A-Z0-9]+", "", texto_upper)
    tiene_senales_bancarias = bool(
        re.search(r"ESTADO\s+DE\s+CUENTA|RESUMEN\s+DE\s+CUENTA|CUENTA\s+CLABE|CLABE\s+INTERBANCARIA", texto_upper)
        or "ESTADODECUENTA" in compacto
        or "RESUMENDECUENTA" in compacto
        or "CLABEINTERBANCARIA" in compacto
        or "CUENTACLABE" in compacto
        or ("CLABE" in compacto and re.search(r"\d{18}", compacto))
        or re.search(r"\b(BBVA|BANCOMER|BANORTE|SANTANDER|BANAMEX|CITIBANAMEX|HSBC|SCOTIABANK|BANCOPPEL|BANCO\s+AZTECA)\b", texto_upper)
    )
    if tiene_senales_bancarias:
        return None
    reglas = [
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|C[ÉE]DULA\s+DE\s+IDENTIFICACI[OÓ]N\s+FISCAL",
            "constancia_fiscal_en___SPARTA_SECRET_REDACTED__",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
            "curp_en___SPARTA_SECRET_REDACTED__",
            "El documento es una constancia CURP. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b|CONSTANCIA\s+.*NSS|TARJETA\s+NSS",
            "nss_en___SPARTA_SECRET_REDACTED__",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICAD[AO]\s+DE\s+NACIMIENTO",
            "acta_en___SPARTA_SECRET_REDACTED__",
            "El documento es un acta de nacimiento. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"SOLICITUD\s+DE\s+EMPLEO|CURRICULUM|CURR[IÍ]CULO|EXPERIENCIA\s+LABORAL",
            "solicitud_en___SPARTA_SECRET_REDACTED__",
            "El documento corresponde a solicitud/CV. En este campo solo se acepta estado de cuenta bancario.",
        ),
        (
            r"CREDENCIAL\s+PARA\s+VOTAR|INSTITUTO\s+NACIONAL\s+ELECTORAL|PASAPORTE|PASSPORT|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|INSTITUTO\s+NACIONAL\s+DE\s+MIGRACI[OÓ]N",
            "identificacion_en___SPARTA_SECRET_REDACTED__",
            "El documento corresponde a identificación oficial. En este campo solo se acepta estado de cuenta bancario.",
        ),
    ]
    for patron, motivo, mensaje in reglas:
        if re.search(patron, texto_upper):
            return _respuesta_rechazo(
                motivo,
                mensaje,
                modo=modo,
                tiempo_ms=int((time.time() - inicio) * 1000),
                banco_detectado=None,
                nombre_propietario=None,
                clabe=None,
                es_banco_fisico=False,
                tiene_datos_titular=False,
            )
    return None


def _detectar_documento_equivocado___SPARTA_SECRET_REDACTED___pdf(pdf_bytes: bytes, inicio: float) -> Optional[Dict[str, Any]]:
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    rechazo = _rechazo___SPARTA_SECRET_REDACTED___por_texto_equivocado(
        info.get("texto") or "",
        inicio,
        "documento_equivocado_texto_embebido",
    )
    if rechazo:
        return rechazo
    if not PYMUPDF_AVAILABLE:
        return None
    try:
        doc = fitz.open(stream=pdf_bytes, filetype="pdf")
        if doc.page_count <= 0:
            doc.close()
            return None
        page = doc[0]
        pix = page.get_pixmap(dpi=105)
        img_bytes = pix.tobytes("png")
        doc.close()
        service = VerificacionService()
        texto = service.ocr_analyzer.extraer_texto_rapido(img_bytes, max_ancho=1000)
        return _rechazo___SPARTA_SECRET_REDACTED___por_texto_equivocado(
            texto,
            inicio,
            "documento_equivocado_ocr_rapido",
        )
    except Exception as e:
        logger.debug(f"detectar documento equivocado __SPARTA_SECRET_REDACTED__ fallo: {e}")
    return None


def _rechazo_por_texto_para_campo(texto: str, campo: str, mensaje_campo: str, **extra: Any) -> Optional[Dict[str, Any]]:
    """Rechazo barato cuando el PDF claramente pertenece a otro campo documental."""
    texto_upper = _normalizar_texto_precheck(texto)
    parece_nss_texto = bool(
        re.search(
            r"INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b|N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|\bNSS\b|"
            r"VIGENCIA\s+DE\s+DERECHOS|CONSTANCIA\s+DE\s+VIGENCIA|SEMANAS\s+COTIZADAS|HISTORIAL\s+DE\s+REGISTROS\s+AFILIATORIOS",
            texto_upper,
        )
    )
    parece_fiscal_texto = bool(
        re.search(
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|C[ÉE]DULA\s+DE\s+IDENTIFICACI[OÓ]N\s+FISCAL|"
            r"REGISTRO\s+FEDERAL\s+DE\s+CONTRIBUYENTES|\bRFC\b|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|\bSAT\b|IDCIF",
            texto_upper,
        )
    )
    reglas = [
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|\bSAT\b",
            "constancia_fiscal",
            "El documento es una constancia fiscal SAT.",
        ),
        (
            r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
            "curp",
            "El documento es una constancia CURP.",
        ),
        (
            r"N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b|CONSTANCIA\s+.*NSS|TARJETA\s+NSS",
            "nss",
            "El documento corresponde a NSS del IMSS.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICAD[AO]\s+DE\s+NACIMIENTO",
            "acta",
            "El documento es un acta de nacimiento.",
        ),
        (
            r"ESTADO\s+DE\s+CUENTA|CUENTA\s+CLABE|NO\.?\s+DE\s+CUENTA|CLABE\s+INTERBANCARIA|BBVA|BANCOMER|SANTANDER|BANORTE|HSBC|SCOTIABANK",
            "__SPARTA_SECRET_REDACTED__",
            "El documento corresponde a estado de cuenta bancario.",
        ),
        (
            r"CREDENCIAL\s+PARA\s+VOTAR|INSTITUTO\s+NACIONAL\s+ELECTORAL|PASAPORTE|PASSPORT|RESIDENCIA\s+(TEMPORAL|PERMANENTE)|INSTITUTO\s+NACIONAL\s+DE\s+MIGRACI[OÓ]N",
            "identificacion",
            "El documento corresponde a identificación oficial.",
        ),
        (
            r"SOLICITUD\s+DE\s+EMPLEO|CURRICULUM|CURR[IÍ]CULO|EXPERIENCIA\s+LABORAL",
            "solicitud",
            "El documento corresponde a solicitud/CV.",
        ),
    ]
    for patron, tipo, base_msg in reglas:
        if tipo == campo:
            continue
        if campo == "nss" and tipo == "curp" and parece_nss_texto:
            continue
        if campo == "constancia_fiscal" and tipo == "curp" and parece_fiscal_texto:
            continue
        if re.search(patron, texto_upper):
            return _respuesta_rechazo(
                f"{tipo}_en_{campo}",
                f"{base_msg} {mensaje_campo}",
                **extra,
            )
    return None


def _respuesta_comprobante_rechazo(mensaje: str, inicio: float) -> ComprobanteResponse:
    return ComprobanteResponse(
        tipo_comprobante=None,
        score_validacion=0,
        resultado="RECHAZADO",
        nombre_titular=None,
        direccion=None,
        fecha_documento=None,
        es_reciente=None,
        meses_antiguedad=None,
        empresa=None,
        alertas=[mensaje],
        recomendacion=mensaje,
        tiempo_proceso_ms=int((time.time() - inicio) * 1000),
    )


def _nombre_archivo_indica_otro_documento(nombre_archivo: str, campo: str, mensaje_campo: str, **extra: Any) -> Optional[Dict[str, Any]]:
    nombre = _normalizar_texto_precheck(nombre_archivo or "")
    reglas = [
        (r"CONSTANCIA.*(SF|FISCAL|SAT)|SITUACION\s+FISCAL|\bCSF\b", "constancia_fiscal", "El archivo parece ser una constancia fiscal SAT."),
        (r"\bCURP\b|CONSTANCIA.*CURP", "curp", "El archivo parece ser una constancia CURP."),
        (r"\bNSS\b|SEGURO\s+SOCIAL", "nss", "El archivo parece corresponder a NSS del IMSS."),
        (r"ACTA.*NACIMIENTO|NACIMIENTO", "acta", "El archivo parece ser un acta de nacimiento."),
        (r"ESTADO.*CUENTA|CUENTA.*BANC", "__SPARTA_SECRET_REDACTED__", "El archivo parece ser un estado de cuenta bancario."),
        (r"IDENTIFICACI[OÓ]N|IDENTIFICACION|INDENTIFICACION|INE|PASAPORTE|PASSPORT|RESIDENCIA", "identificacion", "El archivo parece ser una identificación oficial."),
        (r"SOLICITUD|CURRICULUM|CURR[IÍ]CULO|CV", "solicitud", "El archivo parece corresponder a solicitud/CV."),
    ]
    for patron, tipo, base_msg in reglas:
        if tipo == campo:
            continue
        if re.search(patron, nombre):
            return _respuesta_rechazo(
                f"{tipo}_en_{campo}_nombre_archivo",
                f"{base_msg} {mensaje_campo}",
                **extra,
            )
    return None


def _nombre_archivo_rechazo_comprobante(nombre_archivo: str, inicio: float) -> Optional[ComprobanteResponse]:
    rechazo = _nombre_archivo_indica_otro_documento(
        nombre_archivo,
        "comprobante_domicilio",
        "En este campo debe subir un comprobante de domicilio.",
    )
    if rechazo:
        return _respuesta_comprobante_rechazo(rechazo.get("mensaje") or "El archivo no corresponde a comprobante de domicilio.", inicio)
    return None


def _rechazo_comprobante_domicilio_rapido(pdf_bytes: bytes, inicio: float) -> Optional[ComprobanteResponse]:
    """Evita mandar documentos claramente equivocados al analizador de comprobante."""
    info = _texto_pdf_embebido_rapido(pdf_bytes, max_paginas=2)
    texto = info.get("texto") or ""
    texto_upper = _normalizar_texto_precheck(texto)
    reglas = [
        (
            r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|C[ÉE]DULA\s+DE\s+IDENTIFICACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT",
            "Este documento es una constancia fiscal SAT, no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
        (
            r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N",
            "Este documento es una constancia CURP, no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
        (
            r"N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b|CONSTANCIA\s+.*NSS|TARJETA\s+NSS",
            "Este documento corresponde a NSS del IMSS, no a comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
        (
            r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICAD[AO]\s+DE\s+NACIMIENTO",
            "Este documento es un acta de nacimiento, no un comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
        (
            r"SOLICITUD\s+DE\s+EMPLEO|CURRICULUM|CURR[IÍ]CULO|EXPERIENCIA\s+LABORAL",
            "Este documento corresponde a solicitud/CV, no a comprobante de domicilio. Sube un recibo de luz, agua, gas, teléfono, banco o predial.",
        ),
    ]
    for patron, mensaje in reglas:
        if re.search(patron, texto_upper):
            return _respuesta_comprobante_rechazo(mensaje, inicio)
    # En comprobante de domicilio no rechazamos por precheck de identificacion.
    # Algunos comprobantes reales mencionan INE/identificacion o el OCR los mezcla;
    # el analizador completo decidira por contenido y, si hay duda, lo manda a revision.
    return None


async def verificar_api_key(api_key: str = Depends(api_key_header)):
    """Verifica que el request tenga una API key válida."""
    if not api_key or api_key != settings.master_api_key:
        raise HTTPException(status_code=403, detail="API Key inválida o faltante")
    return api_key


def validar_imagen(file: UploadFile) -> None:
    """Valida extensión y tamaño de la imagen."""
    extension = file.filename.split(".")[-1].lower() if file.filename else ""
    if extension not in EXTENSIONES_PERMITIDAS:
        raise HTTPException(
            status_code=400,
            detail=f"Formato no permitido. Use: {', '.join(EXTENSIONES_PERMITIDAS)}"
        )


class _BytesUploadFile:
    """Adaptador minimo para reutilizar validar_expediente con payload JSON/base64."""

    def __init__(self, filename: str, data: bytes):
        self.filename = filename
        self._data = data

    async def read(self) -> bytes:
        return self._data


def _payload_upload_from_b64(payload: Dict[str, Any], key: str, default_filename: str) -> Optional[_BytesUploadFile]:
    item = payload.get(key)
    if not item:
        return None

    filename = default_filename
    raw_b64: Any = item
    if isinstance(item, dict):
        raw_b64 = item.get("b64") or item.get("base64") or item.get("data")
        filename = str(item.get("filename") or default_filename)
    if raw_b64 is None:
        return None

    b64_text = str(raw_b64).strip()
    if b64_text.lower().startswith("data:") and "," in b64_text:
        b64_text = b64_text.split(",", 1)[1]
    try:
        data = base64.b64decode(b64_text, validate=False)
    except Exception as exc:
        raise HTTPException(status_code=400, detail=f"{key}: base64 invalido ({exc})")
    if not data:
        return None
    return _BytesUploadFile(filename, data)


async def _ejecutar_pdf_timeout(nombre: str, func, *args, timeout: int = 8, fallback: Optional[Dict[str, Any]] = None):
    """Ejecuta extracción de PDF sin dejar que una OCR difícil bloquee el expediente."""
    try:
        return await asyncio.wait_for(asyncio.to_thread(func, *args), timeout=timeout)
    except asyncio.TimeoutError:
        logger.warning(f"{nombre}: timeout tras {timeout}s; se enviará a revisión manual")
        data = {"revision_manual": True, "timeout": True, "mensaje": f"{nombre} no se pudo leer a tiempo."}
        if fallback:
            data.update(fallback)
        return data
    except Exception as e:
        logger.warning(f"{nombre}: error en extracción PDF: {e}")
        data = {"revision_manual": True, "error": str(e), "mensaje": f"{nombre} no se pudo leer automáticamente."}
        if fallback:
            data.update(fallback)
        return data


def _respuesta_rechazo(motivo: str, mensaje: str, **extra) -> Dict[str, Any]:
    data: Dict[str, Any] = {
        "valido": False,
        "revision_manual": False,
        "rechazado": True,
        "motivo_rechazo": motivo,
        "mensaje": mensaje,
    }
    data.update(extra)
    return data


@router.post(
    "/verificar",
    response_model=VerificacionResponse,
    summary="Verificar autenticidad de documento",
    description="""
    Analiza una imagen de documento oficial mexicano y determina si es original,
    requiere revisión manual, o debe ser rechazado.

    **Documentos soportados:**
    - INE / Credencial para Votar (nueva y anterior)
    - Residencia Temporal INM
    - Residencia Temporal Acumulativa INM
    - Residencia Permanente INM

    **Capas de análisis:**
    1. Metadatos del archivo (15%)
    2. Análisis forense ELA + moiré (20%)
    3. Geometría y proporciones (15%)
    4. OCR + validación de campos (30%)
    5. Códigos QR / Barcode (10%)
    6. Clasificador ML (10%)
    """,
    tags=["Verificación"]
)
async def verificar_documento(
    imagen: UploadFile = File(..., description="Imagen del documento (JPG, PNG, WEBP, TIFF)"),
    tipo_documento: Optional[TipoDocumento] = Query(
        None,
        description="Tipo de documento. Si no se especifica, se auto-detecta."
    ),
    api_key: str = Depends(verificar_api_key)
):
    """
    Endpoint principal de verificación de documentos.
    """
    validar_imagen(imagen)
    image_bytes = await imagen.read()

    if len(image_bytes) == 0:
        raise HTTPException(status_code=400, detail="Imagen vacía")

    if len(image_bytes) > MAX_SIZE_BYTES:
        raise HTTPException(
            status_code=413,
            detail=f"Imagen muy grande. Máximo {settings.max_image_size_mb}MB"
        )

    logger.info(f"Verificando documento - tipo: {tipo_documento} - tamaño: {len(image_bytes)/1024:.1f}KB")

    try:
        service = VerificacionService()
        resultado = await service.verificar(image_bytes, tipo_documento)
        return resultado
    except Exception as e:
        logger.exception(f"Error en verificación: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Error interno procesando el documento: {str(e)}"
        )


@router.post(
    "/verificar-calidad",
    response_model=VerificacionCalidadResponse,
    summary="Verificación ligera de calidad de imagen (identificación)",
    description="""
    Primera revisión rápida: comprueba que la imagen no esté borrosa,
    sin exceso de brillo/reflejos y que parezca un documento.
    Si se envía **lado_esperado** (frente o reverso), además valida que la imagen
    corresponda a ese lado (MRZ = reverso, CURP/CREDENCIAL = frente).
    """,
    tags=["Verificación"]
)
async def verificar_calidad_documento(
    imagen: UploadFile = File(..., description="Imagen del documento (frente o reverso)"),
    lado_esperado: Optional[str] = Query(None, description="Si es 'frente' o 'reverso', se valida que la imagen sea de ese lado"),
    api_key: str = Depends(verificar_api_key)
):
    validar_imagen(imagen)
    image_bytes = await imagen.read()
    if len(image_bytes) == 0:
        raise HTTPException(status_code=400, detail="Imagen vacía")
    if len(image_bytes) > MAX_SIZE_BYTES:
        raise HTTPException(
            status_code=413,
            detail=f"Imagen muy grande. Máximo {settings.max_image_size_mb}MB"
        )
    if lado_esperado is not None and lado_esperado not in ("frente", "reverso"):
        lado_esperado = None
    try:
        service = VerificacionService()
        return await service.verificar_calidad(image_bytes, lado_esperado=lado_esperado)
    except Exception as e:
        logger.exception(f"Error en verificación calidad: {e}")
        return VerificacionCalidadResponse(
            ok=False,
            mensaje="No se pudo verificar la imagen. Intenta de nuevo.",
            alertas=[str(e)],
            lado_detectado=None,
        )


@router.post(
    "/verificar-comprobante",
    response_model=ComprobanteResponse,
    summary="Verificar comprobante de domicilio",
    description="""
    Analiza un comprobante de domicilio (PDF o imagen) y extrae:
    nombre del titular, dirección, fecha, tipo de servicio.
    Verifica que no tenga más de 3 meses de antigüedad.

    **Formatos soportados:** PDF, JPG, PNG
    **Tipos reconocidos:** CFE/Luz, Agua, Gas, Teléfono/Internet, Banco, Predial
    """,
    tags=["Comprobante"]
)
async def verificar_comprobante(
    documento: UploadFile = File(..., description="Comprobante de domicilio (PDF, JPG, PNG)"),
    api_key: str = Depends(verificar_api_key)
):
    extension = documento.filename.split(".")[-1].lower() if documento.filename else ""
    if extension not in EXTENSIONES_COMPROBANTE:
        raise HTTPException(
            status_code=400,
            detail=f"Formato no permitido. Use: {', '.join(EXTENSIONES_COMPROBANTE)}"
        )

    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    if len(file_bytes) > MAX_SIZE_BYTES:
        raise HTTPException(status_code=413, detail=f"Documento muy grande. Máximo {settings.max_image_size_mb}MB")

    logger.info(f"Verificando comprobante - archivo: {documento.filename} - tamaño: {len(file_bytes)/1024:.1f}KB")

    inicio = time.time()
    resultado_alibaba = await _validar_rapido_alibaba_o_none(file_bytes, documento.filename or "comprobante.pdf", "comprobante_domicilio")
    if resultado_alibaba is not None:
        return _respuesta_alibaba_comprobante(resultado_alibaba)

    # No rechazar comprobantes por nombre de archivo: candidatos suelen reutilizar
    # nombres como "INE.pdf" o "documento.pdf". La decision debe salir del contenido.
    if extension == "pdf":
        rechazo_rapido = _rechazo_comprobante_domicilio_rapido(file_bytes, inicio)
        if rechazo_rapido:
            return rechazo_rapido

    try:
        analyzer = ComprobanteAnalyzer()
        check = analyzer.analyze(file_bytes, documento.filename or "")
        tiempo_ms = int((time.time() - inicio) * 1000)

        score = round(check.score * 100)
        resultado = "RECHAZADO"
        recomendacion = "Comprobante no válido o no se pudo verificar."
        if check.alertas:
            recomendacion = check.alertas[0]

        tipo_label = ""
        if check.tipo_comprobante == "CFE_LUZ":
            tipo_label = "luz (CFE)"
        elif check.tipo_comprobante == "AGUA":
            tipo_label = "agua"
        elif check.tipo_comprobante == "GAS":
            tipo_label = "gas"
        elif check.tipo_comprobante == "TELEFONO_INTERNET":
            tipo_label = "teléfono o internet"
        elif check.tipo_comprobante == "BANCO":
            tipo_label = "banco"
        elif check.tipo_comprobante == "PREDIAL":
            tipo_label = "predial"
        elif check.tipo_comprobante:
            tipo_label = (check.tipo_comprobante or "").replace("_", " ").title()
        prefijo = f"Comprobante de {tipo_label} rechazado: " if tipo_label else "Comprobante rechazado: "
        alerta_principal = (check.alertas[0] if check.alertas else "") or ""
        es_documento_equivocado = alerta_principal.startswith("Este documento es ") or alerta_principal.startswith("Este documento no es ")

        if es_documento_equivocado:
            resultado = "RECHAZADO"
            recomendacion = alerta_principal
        elif check.es_reciente is False:
            resultado = "RECHAZADO"
            recomendacion = prefijo + "tiene más de 3 meses de antigüedad. Se requiere máximo 3 meses."
        elif check.es_reciente is None and not check.fecha_documento:
            resultado = "REVISION_MANUAL"
            if any("no se pudo extraer texto" in (a or "").lower() for a in (check.alertas or [])):
                recomendacion = "Comprobante recibido; no se pudo extraer texto legible para validar la fecha. Revisar manualmente."
            else:
                recomendacion = "Comprobante recibido; no se pudo verificar la fecha automáticamente. Revisar manualmente."
        elif score >= 75 and check.es_reciente is True:
            resultado = "APROBADO"
            recomendacion = f"Comprobante de {tipo_label} válido y reciente. Puede procesarse." if tipo_label else "Comprobante válido y reciente. Puede procesarse."
        elif score >= 50:
            resultado = "REVISION_MANUAL"
            recomendacion = "Comprobante requiere revisión manual."

        else:
            resultado = "REVISION_MANUAL"
            recomendacion = "Comprobante recibido; no se pudo verificar con suficiente confianza. Revisar manualmente."

        return ComprobanteResponse(
            tipo_comprobante=check.tipo_comprobante,
            score_validacion=score,
            resultado=resultado,
            nombre_titular=check.nombre_titular,
            direccion=check.direccion_detectada,
            fecha_documento=check.fecha_documento,
            es_reciente=check.es_reciente,
            meses_antiguedad=check.meses_antiguedad,
            empresa=check.empresa_detectada,
            alertas=check.alertas,
            recomendacion=recomendacion,
            tiempo_proceso_ms=tiempo_ms,
        )
    except Exception as e:
        logger.exception(f"Error en verificación de comprobante: {e}")
        raise HTTPException(status_code=500, detail=f"Error procesando comprobante: {str(e)}")


@router.post(
    "/verificar-nss",
    summary="Validar NSS (formato y dígito verificador)",
    description="Valida que un NSS tenga 11 dígitos y que el dígito verificador sea correcto. No consulta IMSS. Número de prueba desde NSS.pdf: 03239629730.",
    tags=["Utilidades"]
)
async def verificar_nss(
    body: dict = Body(..., example={"nss": "03239629730"}),
    api_key: str = Depends(verificar_api_key)
):
    """
    Recibe JSON con clave 'nss'. Retorna si el formato y dígito verificador son válidos.
    """
    nss = body.get("nss") if isinstance(body, dict) else None
    if nss is None:
        raise HTTPException(status_code=400, detail="Falta el campo 'nss' en el cuerpo")
    valido, mensaje = validar_nss(str(nss))
    return {"valido": valido, "mensaje": mensaje}


@router.post(
    "/verificar-nss-documento",
    summary="Extraer y validar NSS desde PDF (vigencia de derechos / constancia IMSS)",
    description="Sube el PDF de vigencia de derechos del IMSS (imss.gob.mx), constancia de vigencia FF-IMSS-012, o constancia de semanas cotizadas. La tarjeta NSS (imprimir y recortar) se detecta y se rechaza sin mandarla a revisión manual. Retorna nss_extraido, válido y mensaje.",
    tags=["Utilidades"]
)
async def verificar_nss_documento(
    documento: UploadFile = File(..., description="PDF de vigencia de derechos IMSS o constancia de NSS"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de vigencia de derechos del IMSS")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    resultado_alibaba = await _validar_rapido_alibaba_o_none(file_bytes, documento.filename or "nss.pdf", "nss")
    if resultado_alibaba is not None:
        return _respuesta_alibaba_nss(resultado_alibaba)
    rechazo_nombre = _nombre_archivo_indica_otro_documento(
        documento.filename or "",
        "nss",
        "En NSS debe subir constancia o vigencia de derechos del IMSS.",
        nss_extraido=None,
    )
    if rechazo_nombre:
        return rechazo_nombre
    info_rapida = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
    rechazo_rapido = _rechazo_por_texto_para_campo(
        info_rapida.get("texto") or "",
        "nss",
        "En NSS debe subir constancia o vigencia de derechos del IMSS.",
        nss_extraido=None,
    )
    if rechazo_rapido:
        return rechazo_rapido
    try:
        tarjeta_nss = await asyncio.wait_for(asyncio.to_thread(es_tarjeta_nss, file_bytes), timeout=4)
    except asyncio.TimeoutError:
        return {"nss_extraido": None, "valido": False, "revision_manual": True, "timeout": True, "mensaje": "No se pudo confirmar automáticamente el formato del NSS. Revisar manualmente."}

    if tarjeta_nss:
        nss_extraido = extraer_nss_de_pdf(file_bytes)
        datos_nss = extraer_datos_nss_pdf(file_bytes)
        return _respuesta_rechazo(
            "tarjeta_nss_no_aceptada",
            "No se acepta tarjeta NSS (imprimir y recortar). Debe subir constancia o vigencia de derechos del IMSS.",
            nss_extraido=nss_extraido,
            nombre=datos_nss.get("nombre") if isinstance(datos_nss, dict) else None,
            curp=datos_nss.get("curp") if isinstance(datos_nss, dict) else None,
            fecha_nacimiento=datos_nss.get("fecha_nacimiento") if isinstance(datos_nss, dict) else None,
        )

    try:
        parece_nss = await asyncio.wait_for(asyncio.to_thread(es_documento_nss, file_bytes), timeout=5)
    except asyncio.TimeoutError:
        return {"nss_extraido": None, "valido": False, "revision_manual": True, "timeout": True, "mensaje": "No se pudo confirmar automáticamente el formato del NSS. Revisar manualmente."}

    if parece_nss:
        nss_extraido = extraer_nss_de_pdf(file_bytes)
        if nss_extraido is None:
            return {"nss_extraido": None, "valido": False, "revision_manual": True, "mensaje": "No se encontró automáticamente un NSS de 11 dígitos. Revisar manualmente."}
        valido, mensaje = validar_nss(nss_extraido)
        datos_nss = extraer_datos_nss_pdf(file_bytes)
        nombre = datos_nss.get("nombre") if isinstance(datos_nss, dict) else None
        return {
            "nss_extraido": nss_extraido,
            "valido": valido,
            "mensaje": mensaje,
            "nombre": nombre,
            "curp": datos_nss.get("curp") if isinstance(datos_nss, dict) else None,
            "fecha_nacimiento": datos_nss.get("fecha_nacimiento") if isinstance(datos_nss, dict) else None,
        }

    if es_documento_curp(file_bytes):
        return _respuesta_rechazo(
            "documento_curp_en_nss",
            "El documento es una constancia CURP. En NSS debe subir constancia o vigencia de derechos del IMSS.",
            nss_extraido=None,
        )
    if es_documento_constancia_fiscal(file_bytes):
        return _respuesta_rechazo(
            "constancia_fiscal_en_nss",
            "El documento es una constancia fiscal SAT. En NSS debe subir constancia o vigencia de derechos del IMSS.",
            nss_extraido=None,
        )
    if es_documento_acta_nacimiento(file_bytes):
        return _respuesta_rechazo(
            "acta_en_nss",
            "El documento es un acta de nacimiento. En NSS debe subir constancia o vigencia de derechos del IMSS.",
            nss_extraido=None,
        )
    return {"nss_extraido": None, "valido": False, "revision_manual": True, "mensaje": "No se pudo confirmar automáticamente el formato del NSS. Revisar manualmente."}


@router.post(
    "/verificar-acta-documento",
    summary="Verificar que el PDF sea un acta de nacimiento",
    description="Sube un PDF de acta de nacimiento certificada. Verifica que contenga nombre y/o fecha de nacimiento propios del acta. Rechaza constancias de NSS u otros documentos.",
    tags=["Utilidades"]
)
async def verificar_acta_documento(
    documento: UploadFile = File(..., description="PDF de acta de nacimiento certificada"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de acta de nacimiento")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    inicio = time.time()
    texto_info = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
    texto_norm = _normalizar_texto_precheck(texto_info.get("texto") or "")

    if (
        ("INSTITUTO MEXICANO DEL SEGURO SOCIAL" in texto_norm or "IMSS" in texto_norm)
        and (
            "NUMERO DE SEGURIDAD SOCIAL" in texto_norm
            or "VIGENCIA DE DERECHOS" in texto_norm
            or "CONSTANCIA DE VIGENCIA" in texto_norm
            or "SEMANAS COTIZADAS" in texto_norm
            or "HISTORIAL DE REGISTROS AFILIATORIOS" in texto_norm
        )
    ):
        return _respuesta_rechazo(
            "nss_en_acta",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta acta de nacimiento certificada.",
            parece_acta=False,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )

    parece_acta = bool(
        "ACTA DE NACIMIENTO" in texto_norm
        or "CERTIFICADO DE NACIMIENTO" in texto_norm
        or "CERTIFICACION DE NACIMIENTO" in texto_norm
        or (("ACTA" in texto_norm or "REGISTRO CIVIL" in texto_norm) and "NACIMIENTO" in texto_norm)
    )
    if parece_acta:
        tiempo_ms = int((time.time() - inicio) * 1000)
        return {
            "valido": True,
            "mensaje": "Acta de nacimiento verificada.",
            "nombre": None,
            "fecha_nacimiento": None,
            "parece_acta": True,
            "modo_validacion": "texto_pdf_rapido",
            "tiempo_ms": tiempo_ms,
        }
    return {
        "valido": False,
        "revision_manual": True,
        "mensaje": "No se pudo confirmar automáticamente el acta de nacimiento. Revisar manualmente.",
        "parece_acta": False,
        "modo_validacion": "revision_manual_rapida",
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }
@router.post(
    "/verificar-constancia-fiscal-documento",
    summary="Verificar que el PDF sea constancia de situación fiscal (SAT)",
    description="Sube un PDF de constancia de situación fiscal del SAT. Verifica: vigencia (máx 2 meses desde fecha de emisión), actividad económica Asalariado y régimen Sueldos y Salarios.",
    tags=["Utilidades"]
)
async def verificar_constancia_fiscal_documento(
    documento: UploadFile = File(..., description="PDF de constancia de situación fiscal (SAT)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de constancia de situación fiscal")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    inicio = time.time()
    resultado_alibaba = await _validar_rapido_alibaba_o_none(file_bytes, documento.filename or "constancia_fiscal.pdf", "constancia_fiscal")
    if resultado_alibaba is not None:
        return _respuesta_alibaba_fiscal(resultado_alibaba)
    rechazo_nombre = _nombre_archivo_indica_otro_documento(
        documento.filename or "",
        "constancia_fiscal",
        "En este campo solo se acepta constancia de situación fiscal SAT.",
    )
    if rechazo_nombre:
        return rechazo_nombre
    info_rapida = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
    rechazo_rapido = _rechazo_por_texto_para_campo(
        info_rapida.get("texto") or "",
        "constancia_fiscal",
        "En este campo solo se acepta constancia de situación fiscal SAT.",
    )
    if rechazo_rapido:
        return rechazo_rapido
    paginas_pdf = int(info_rapida.get("paginas") or 0)
    if paginas_pdf == 1:
        return _respuesta_rechazo(
            "constancia_fiscal_incompleta",
            "La constancia de situación fiscal debe incluir sus 2 hojas. Descarga la constancia completa del portal del SAT y vuelve a subirla.",
            paginas=paginas_pdf,
        )
    try:
        datos = await asyncio.wait_for(
            asyncio.to_thread(extraer_datos_constancia_fiscal, file_bytes),
            timeout=8,
        )
    except asyncio.TimeoutError:
        return {
            "valido": False,
            "mensaje": "No se pudo leer la constancia a tiempo.",
            "timeout": True,
            "revision_manual": True,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }

    if not datos.get("parece_constancia_fiscal"):
        if es_tarjeta_nss(file_bytes) or await asyncio.to_thread(es_documento_nss, file_bytes):
            return _respuesta_rechazo(
                "nss_en_constancia_fiscal",
                "El documento corresponde a NSS del IMSS. En este campo solo se acepta constancia de situación fiscal SAT.",
            )
        if await asyncio.to_thread(es_documento_curp, file_bytes):
            return _respuesta_rechazo(
                "curp_en_constancia_fiscal",
                "El documento es una constancia CURP. En este campo solo se acepta constancia de situación fiscal SAT.",
            )
        if await asyncio.to_thread(es_documento_acta_nacimiento, file_bytes):
            return _respuesta_rechazo(
                "acta_en_constancia_fiscal",
                "El documento es un acta de nacimiento. En este campo solo se acepta constancia de situación fiscal SAT.",
            )
        return {"valido": False, "revision_manual": True, "mensaje": "El documento no se pudo confirmar automáticamente como constancia fiscal SAT."}

    # Vigencia: máximo 2 meses desde "Lugar y Fecha de Emisión"
    meses = datos.get("meses_antiguedad")
    if meses is not None and meses > 2.0:
        return _respuesta_rechazo(
            "constancia_fiscal_vencida",
            "La constancia no puede tener más de 2 meses de antigüedad. Descarga una nueva constancia en el portal del SAT.",
            rfc=datos.get("rfc"),
            curp=datos.get("curp"),
            fecha_emision=datos.get("fecha_emision"),
            meses_antiguedad=meses,
            vigencia_ok=False,
            actividad_asalariado=datos.get("actividad_economica_asalariado"),
            regimen_sueldos_salarios=datos.get("regimen_sueldos_salarios"),
        )
    if meses is not None and meses > 2.0:
        return {
            "valido": False,
            "mensaje": "La constancia no puede tener más de 2 meses de antigüedad. Descarga una nueva constancia en el portal del SAT.",
            "rfc": datos.get("rfc"),
            "curp": datos.get("curp"),
            "rfc": datos.get("rfc"),
            "curp": datos.get("curp"),
            "fecha_emision": datos.get("fecha_emision"),
            "meses_antiguedad": meses,
            "vigencia_ok": False,
            "actividad_asalariado": datos.get("actividad_economica_asalariado"),
            "regimen_sueldos_salarios": datos.get("regimen_sueldos_salarios"),
        }

    # Actividad económica debe ser "Asalariado"
    if not datos.get("actividad_economica_asalariado") and not datos.get("regimen_sueldos_salarios"):
        return {
            "valido": False,
            "revision_manual": True,
            "mensaje": "No se pudo confirmar automáticamente la actividad Asalariado. Revisar manualmente.",
            "rfc": datos.get("rfc"),
            "curp": datos.get("curp"),
            "fecha_emision": datos.get("fecha_emision"),
            "meses_antiguedad": meses,
            "vigencia_ok": meses is None or meses <= 2.0,
            "actividad_asalariado": False,
            "regimen_sueldos_salarios": datos.get("regimen_sueldos_salarios"),
        }

    # Régimen debe incluir "Régimen de Sueldos y Salarios e Ingresos Asimilados a Salarios"
    if not datos.get("regimen_sueldos_salarios"):
        return {
            "valido": False,
            "revision_manual": True,
            "mensaje": "No se pudo confirmar automáticamente el régimen de Sueldos y Salarios. Revisar manualmente.",
            "fecha_emision": datos.get("fecha_emision"),
            "meses_antiguedad": meses,
            "vigencia_ok": meses is None or meses <= 2.0,
            "actividad_asalariado": bool(datos.get("actividad_economica_asalariado")),
            "regimen_sueldos_salarios": False,
        }

    if not (datos.get("rfc") or (datos.get("curp") and validar_curp(datos["curp"])[0])):
        return {"valido": False, "revision_manual": True, "mensaje": "No se pudo confirmar automáticamente la constancia fiscal SAT. Revisar manualmente."}

    return {
        "valido": True,
        "mensaje": "Constancia fiscal verificada.",
        "rfc": datos.get("rfc"),
        "curp": datos.get("curp"),
        "fecha_emision": datos.get("fecha_emision"),
        "meses_antiguedad": meses,
        "vigencia_ok": True,
        "actividad_asalariado": bool(datos.get("actividad_economica_asalariado")),
        "regimen_sueldos_salarios": True,
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }


@router.post(
    "/validar-paginas-pdf",
    summary="Validar cantidad mínima de páginas de un PDF",
    description="Devuelve el número de páginas de un PDF y permite validar mínimos simples por documento.",
    tags=["Utilidades"]
)
async def validar_paginas_pdf(
    documento: UploadFile = File(..., description="PDF a revisar"),
    minimo_paginas: int = Form(1, description="Cantidad mínima de páginas requerida"),
    nombre_documento: Optional[str] = Form(None, description="Nombre del documento para el mensaje"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    paginas = 0
    if PYMUPDF_AVAILABLE:
        try:
            doc = fitz.open(stream=file_bytes, filetype="pdf")
            paginas = int(doc.page_count or 0)
            doc.close()
        except Exception as e:
            logger.warning(f"validar_paginas_pdf: error leyendo PDF: {e}")
    minimo = max(1, int(minimo_paginas or 1))
    nombre = (nombre_documento or "El documento").strip() or "El documento"
    valido = paginas >= minimo if paginas > 0 else False
    return {
        "valido": valido,
        "paginas": paginas,
        "minimo_paginas": minimo,
        "mensaje": (
            f"{nombre} tiene {paginas} hoja(s)."
            if valido
            else f"{nombre} debe tener al menos {minimo} hojas. Vuelve a subir el PDF completo."
        ),
        "rechazado": paginas > 0 and not valido,
    }


@router.post(
    "/verificar-estado-cuenta",
    summary="Verificar PDF de estado de cuenta (banco físico + hoja datos titular)",
    description="Sube un PDF de estado de cuenta. Solo se aceptan bancos físicos de México (BBVA, Banorte, Santander, etc.). Se rechazan neobancos (Nu, Ualá, Klar, etc.). El PDF debe incluir la hoja con datos del titular (nombre, dirección, CLABE, cuenta).",
    tags=["Utilidades"]
)
async def verificar___SPARTA_SECRET_REDACTED__(
    documento: UploadFile = File(..., description="PDF de estado de cuenta"),
    api_key: str = Depends(verificar_api_key)
):
    inicio = time.time()
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de estado de cuenta")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    resultado_alibaba = await _validar_rapido_alibaba_o_none(file_bytes, documento.filename or "__SPARTA_SECRET_REDACTED__.pdf", "__SPARTA_SECRET_REDACTED__")
    if resultado_alibaba is not None:
        return _respuesta_alibaba___SPARTA_SECRET_REDACTED__(resultado_alibaba)
    try:
        rechazo = await asyncio.wait_for(
            asyncio.to_thread(_detectar_documento_equivocado___SPARTA_SECRET_REDACTED___pdf, file_bytes, inicio),
            timeout=3,
        )
        if rechazo:
            return rechazo
    except asyncio.TimeoutError:
        logger.debug("verificar___SPARTA_SECRET_REDACTED__: deteccion de documento equivocado agoto tiempo")
    except Exception as e:
        logger.debug(f"verificar___SPARTA_SECRET_REDACTED__: deteccion de documento equivocado fallo: {e}")
    from app.services.__SPARTA_SECRET_REDACTED___analyzer import validar___SPARTA_SECRET_REDACTED___pdf
    try:
        resultado = await asyncio.wait_for(
            asyncio.to_thread(validar___SPARTA_SECRET_REDACTED___pdf, file_bytes),
            timeout=8,
        )
        return resultado
    except asyncio.TimeoutError:
        return {
            "valido": False,
            "revision_manual": True,
            "timeout": True,
            "mensaje": "No se pudo leer el estado de cuenta a tiempo. Revisar manualmente.",
            "banco_detectado": None,
            "nombre_propietario": None,
            "clabe": None,
            "es_banco_fisico": False,
            "tiene_datos_titular": False,
        }


@router.post(
    "/fad/informacion-ingresos",
    summary="Extraer sección Información de Ingresos de FAD_DOC",
    description="Sube el PDF del contrato firmado (FAD_DOC). Extrae la sección 'Información de Ingresos' al final del documento para usar en Sabueso (donde trabaja).",
    tags=["Sabueso"]
)
async def fad_informacion_ingresos(
    documento: UploadFile = File(..., description="PDF FAD_DOC (contrato firmado)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF (FAD_DOC)")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    datos = extraer_informacion_ingresos_fad(file_bytes)
    return {
        "success": True,
        "encontrado": datos.get("encontrado", False),
        "texto_seccion": datos.get("texto_seccion") or "",
        "empresa": datos.get("empresa"),
        "empleado": datos.get("empleado"),
        "ingreso_mensual_neto": datos.get("ingreso_mensual_neto"),
        "telefono": datos.get("telefono"),
    }


@router.post(
    "/factura/datos-moto",
    summary="Extraer VIN, No. motor y color desde FACTURA",
    description="Sube FACTURA (PDF/JPG/PNG). Extrae No. de Serie (VIN), No. de Motor y Color para autollenado en Mis adjudicaciones.",
    tags=["Motos adjudicadas"]
)
async def factura_datos_moto(
    documento: UploadFile = File(..., description="Documento FACTURA (PDF/JPG/PNG)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename:
        raise HTTPException(status_code=400, detail="Se requiere un documento de FACTURA")

    ext = documento.filename.lower().rsplit(".", 1)[-1] if "." in documento.filename else ""
    if ext not in {"pdf", "jpg", "jpeg", "png"}:
        raise HTTPException(status_code=400, detail="Formato no soportado. Use PDF, JPG o PNG.")

    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")

    datos = extraer_datos_motocicleta_factura(file_bytes, documento.filename)
    return {
        "success": True,
        "encontrado": datos.get("encontrado", False),
        "vin": datos.get("vin"),
        "no_motor": datos.get("no_motor"),
        "color": datos.get("color"),
        "texto_fuente": datos.get("texto_fuente", ""),
    }


@router.post(
    "/verificar-curp-documento",
    summary="Extraer y validar CURP desde PDF (constancia RENAPO)",
    description="Sube un PDF de constancia de CURP (ej. CURP.pdf). Se extrae el CURP del texto y se valida. No acepta constancia fiscal ni otros documentos.",
    tags=["Utilidades"]
)
async def verificar_curp_documento(
    documento: UploadFile = File(..., description="PDF constancia de CURP (RENAPO, no constancia fiscal)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de constancia de CURP")
    nombre_archivo = (documento.filename or "").upper()
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    inicio = time.time()
    if re.search(r"SOLICITUD|CURR.?CUL|CURRICULUM|CV[\s_\-()]|IDENTIFICACI[OÓ]N|IDENTIFICACION|INDENTIFICACION|IDENTIF|INE|PASAPORTE|RESIDENCIA", nombre_archivo):
        return _respuesta_rechazo(
            "documento_no_curp",
            "El documento no corresponde a constancia CURP del RENAPO. En este campo solo se acepta CURP.",
            curp_extraido=None,
            es_reciente=None,
            meses_antiguedad=None,
            fecha_emision=None,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    resultado_alibaba = await _validar_rapido_alibaba_o_none(file_bytes, documento.filename or "curp.pdf", "curp")
    if resultado_alibaba is not None:
        return _respuesta_alibaba_curp(resultado_alibaba)
    datos_rapidos = _extraer_datos_curp_pdf_rapido(file_bytes)
    texto_inicial = datos_rapidos.get("texto") or ""
    if texto_inicial:
        rechazo_inicial = _rechazo_curp_por_texto_equivocado(
            texto_inicial,
            inicio,
            f"documento_equivocado_{datos_rapidos.get('modo') or 'texto_rapido'}",
        )
        if rechazo_inicial:
            return rechazo_inicial
        curp_rapida = datos_rapidos.get("curp")
        if datos_rapidos.get("parece_curp") and curp_rapida:
            valido_rapido, mensaje_rapido = validar_curp(curp_rapida)
            if valido_rapido:
                return {
                    "curp_extraido": curp_rapida,
                    "valido": True,
                    "mensaje": mensaje_rapido,
                    "nombre": datos_rapidos.get("nombre"),
                    "es_reciente": datos_rapidos.get("es_reciente"),
                    "meses_antiguedad": datos_rapidos.get("meses_antiguedad"),
                    "fecha_emision": datos_rapidos.get("fecha_emision"),
                    "parece_curp": True,
                    "revision_manual": False,
                    "modo": datos_rapidos.get("modo"),
                    "tiempo_ms": int((time.time() - inicio) * 1000),
                }
        if datos_rapidos.get("parece_curp"):
            texto_inicial = "CONSTANCIA CURP RENAPO"
        if not re.search(r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N", texto_inicial):
            return _respuesta_rechazo(
                "documento_no_curp",
                "El documento no corresponde a constancia CURP del RENAPO. En este campo solo se acepta CURP.",
                curp_extraido=None,
                es_reciente=None,
                meses_antiguedad=None,
                fecha_emision=None,
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
    elif not re.search(r"\bCURP\b", nombre_archivo):
        return {
            "curp_extraido": None,
            "valido": False,
            "revision_manual": True,
            "mensaje": "No se pudo confirmar automáticamente que el PDF sea una constancia CURP del RENAPO. Revisar manualmente.",
            "es_reciente": None,
            "meses_antiguedad": None,
            "fecha_emision": None,
            "parece_curp": False,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    try:
        rechazo_equivocado = await asyncio.wait_for(
            asyncio.to_thread(_detectar_documento_equivocado_curp_pdf, file_bytes, inicio),
            timeout=5,
        )
        if rechazo_equivocado:
            return rechazo_equivocado
    except asyncio.TimeoutError:
        logger.warning("verificar_curp_documento: deteccion de documento equivocado agoto tiempo")
    if es_tarjeta_nss(file_bytes) or es_documento_nss(file_bytes):
        return _respuesta_rechazo(
            "nss_en_curp",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta constancia CURP del RENAPO.",
            curp_extraido=None,
            es_reciente=None,
            meses_antiguedad=None,
            fecha_emision=None,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if es_documento_constancia_fiscal(file_bytes):
        return _respuesta_rechazo(
            "constancia_fiscal_en_curp",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta constancia CURP del RENAPO.",
            curp_extraido=None,
            es_reciente=None,
            meses_antiguedad=None,
            fecha_emision=None,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if es_documento_acta_nacimiento(file_bytes):
        return _respuesta_rechazo(
            "acta_en_curp",
            "El documento es un acta de nacimiento. En este campo solo se acepta constancia CURP del RENAPO.",
            curp_extraido=None,
            es_reciente=None,
            meses_antiguedad=None,
            fecha_emision=None,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    parece_curp = bool(datos_rapidos.get("parece_curp")) or es_documento_curp(file_bytes)
    if not parece_curp:
        info_texto = _texto_pdf_embebido_rapido(file_bytes, max_paginas=2)
        texto_curp = _normalizar_texto_precheck(info_texto.get("texto") or "")
        if not re.search(r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N", texto_curp):
            return _respuesta_rechazo(
                "documento_no_curp",
                "El documento no corresponde a constancia CURP del RENAPO. En este campo solo se acepta CURP.",
                curp_extraido=None,
                es_reciente=None,
                meses_antiguedad=None,
                fecha_emision=None,
                parece_curp=parece_curp,
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
    try:
        datos = await asyncio.wait_for(
            asyncio.to_thread(extraer_datos_curp_pdf, file_bytes),
            timeout=25,
        )
    except asyncio.TimeoutError:
        return {
            "curp_extraido": None,
            "valido": False,
            "mensaje": "No se pudo leer el CURP a tiempo.",
            "timeout": True,
            "revision_manual": True,
            "es_reciente": None,
            "meses_antiguedad": None,
            "fecha_emision": None,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    curp_extraido = datos.get("curp") if isinstance(datos, dict) else None
    if not curp_extraido:
        curp_extraido = extraer_curp_de_pdf(file_bytes)
    if curp_extraido is None:
        return {
            "curp_extraido": None,
            "valido": False,
            "mensaje": "No se pudo leer automáticamente un CURP válido en el documento.",
            "revision_manual": True,
            "es_reciente": None,
            "meses_antiguedad": None,
            "fecha_emision": None,
            "parece_curp": parece_curp,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    valido, mensaje = validar_curp(curp_extraido)
    nombre = datos.get("nombre") if isinstance(datos, dict) else None
    if not valido:
        return {
            "curp_extraido": curp_extraido,
            "valido": False,
            "mensaje": mensaje,
            "nombre": nombre,
            "revision_manual": True,
            "es_reciente": datos.get("es_reciente"),
            "meses_antiguedad": datos.get("meses_antiguedad"),
            "fecha_emision": datos.get("fecha_emision"),
            "parece_curp": parece_curp,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    if not parece_curp:
        return _respuesta_rechazo(
            "documento_no_curp",
            "Se encontro una CURP, pero el PDF no corresponde a una constancia CURP del RENAPO.",
            curp_extraido=curp_extraido,
            nombre=nombre,
            es_reciente=datos.get("es_reciente"),
            meses_antiguedad=datos.get("meses_antiguedad"),
            fecha_emision=datos.get("fecha_emision"),
            parece_curp=parece_curp,
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    return {
        "curp_extraido": curp_extraido,
        "valido": True,
        "mensaje": mensaje,
        "nombre": nombre,
        "es_reciente": datos.get("es_reciente"),
        "meses_antiguedad": datos.get("meses_antiguedad"),
        "fecha_emision": datos.get("fecha_emision"),
        "parece_curp": parece_curp,
        "revision_manual": False,
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }


@router.post(
    "/precheck-identificacion-pdf",
    summary="Precheck rápido de identificación oficial (PDF)",
    description="""
    Revisión rápida para la pantalla de subida de documentos.
    Solo valida que el PDF parezca una identificación oficial (INE, residencia o pasaporte),
    sin extraer datos finales ni ejecutar comparaciones profundas.
    """,
    tags=["Utilidades"]
)
async def precheck_identificacion_pdf(
    documento: UploadFile = File(..., description="PDF de identificación oficial"),
    api_key: str = Depends(verificar_api_key)
):
    inicio = time.time()
    nombre_archivo = (documento.filename or "").upper()
    if re.search(r"SOLICITUD|CURR.?CUL|CURRICULUM|CV[\s_\-()]", nombre_archivo):
        return _respuesta_rechazo(
            "solicitud_en_identificacion",
            "El documento corresponde a solicitud/CV. En este campo solo se acepta identificacion oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado_nombre_archivo",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de identificación oficial")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")
    if not file_bytes.startswith(b"%PDF-"):
        return {
            "valido": False,
            "mensaje": "El archivo no parece ser un PDF válido.",
            "paginas": 0,
            "indicadores": {},
            "modo": "firma_pdf",
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }

    try:
        resultado_alibaba = await _validar_rapido_alibaba_o_none(file_bytes, documento.filename or "identificacion.pdf", "identificacion_oficial")
        if resultado_alibaba is not None:
            return _respuesta_alibaba_identificacion(resultado_alibaba)
        rechazo_equivocado = await asyncio.wait_for(
            asyncio.to_thread(_detectar_documento_equivocado_identificacion_pdf, file_bytes, inicio),
            timeout=5,
        )
        if rechazo_equivocado:
            return rechazo_equivocado
    except asyncio.TimeoutError:
        logger.warning("precheck_identificacion_pdf: deteccion de documento equivocado agoto tiempo")

    if False and (es_tarjeta_nss(file_bytes) or es_documento_nss(file_bytes)):
        return _respuesta_rechazo(
            "nss_en_identificacion",
            "El documento corresponde a NSS del IMSS. En este campo solo se acepta identificación oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if False and es_documento_curp(file_bytes):
        return _respuesta_rechazo(
            "curp_en_identificacion",
            "El documento es una constancia CURP. En este campo solo se acepta identificación oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if False and es_documento_constancia_fiscal(file_bytes):
        return _respuesta_rechazo(
            "constancia_fiscal_en_identificacion",
            "El documento es una constancia fiscal SAT. En este campo solo se acepta identificación oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )
    if False and es_documento_acta_nacimiento(file_bytes):
        return _respuesta_rechazo(
            "acta_en_identificacion",
            "El documento es un acta de nacimiento. En este campo solo se acepta identificación oficial.",
            paginas=0,
            indicadores={},
            modo="documento_equivocado",
            tiempo_ms=int((time.time() - inicio) * 1000),
        )

    try:
        extraido = await asyncio.wait_for(
            asyncio.to_thread(_extraer_texto_pdf_rapido, file_bytes, 2),
            timeout=10,
        )
    except asyncio.TimeoutError:
        return {
            "valido": False,
            "revision_manual": True,
            "mensaje": "No se pudo revisar la identificación a tiempo. Revisar manualmente.",
            "paginas": 0,
            "indicadores": {},
            "modo": "timeout",
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }
    texto = extraido.get("texto") or ""
    indicadores = _indicadores_identificacion(texto)
    valido = _parece_identificacion_oficial(texto)
    paginas = int(extraido.get("paginas") or 0)
    if not valido:
        try:
            extraido_rotado = await asyncio.wait_for(
                asyncio.to_thread(_extraer_texto_identificacion_rotada_pdf, file_bytes),
                timeout=6,
            )
            texto_rotado = extraido_rotado.get("texto") or ""
            if _parece_identificacion_oficial(texto_rotado):
                extraido = extraido_rotado
                texto = texto_rotado
                indicadores = _indicadores_identificacion(texto)
                valido = True
                paginas = int(extraido.get("paginas") or paginas)
        except asyncio.TimeoutError:
            logger.warning("precheck_identificacion_pdf: fallback rotado agoto tiempo")
    texto_upper = texto.upper()
    if re.search(r"(?:^|\s)(?:ID\s*<?\s*MEX|IDMEX|I\s*<\s*MEX|I<MEX)|IDMEX", texto_upper):
        indicadores["ine"] = True
        indicadores["pasaporte"] = False
        valido = True

    if texto.strip() and not valido:
        if re.search(r"ASIGNACI[OÓ]N\s+O\s+LOCALIZACI[OÓ]N|N[UÚ]MERO\s+DE\s+SEGURIDAD\s+SOCIAL|INSTITUTO\s+MEXICANO\s+DEL\s+SEGURO\s+SOCIAL|\bIMSS\b", texto_upper):
            return _respuesta_rechazo(
                "nss_en_identificacion",
                "El documento corresponde a NSS del IMSS. En este campo solo se acepta identificación oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
        if re.search(r"CONSTANCIA\s+.*CURP|CLAVE\s+[UÚ]NICA\s+DE\s+REGISTRO|RENAPO|REGISTRO\s+NACIONAL\s+DE\s+POBLACI[OÓ]N", texto_upper):
            return _respuesta_rechazo(
                "curp_en_identificacion",
                "El documento es una constancia CURP. En este campo solo se acepta identificación oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
        if re.search(r"CONSTANCIA\s+DE\s+SITUACI[OÓ]N\s+FISCAL|SERVICIO\s+DE\s+ADMINISTRACI[OÓ]N\s+TRIBUTARIA|PORTAL\s+DEL\s+SAT|\bSAT\b", texto_upper):
            return _respuesta_rechazo(
                "constancia_fiscal_en_identificacion",
                "El documento es una constancia fiscal SAT. En este campo solo se acepta identificación oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )
        if re.search(r"ACTA\s+DE\s+NACIMIENTO|REGISTRO\s+CIVIL|CERTIFICADO\s+DE\s+NACIMIENTO", texto_upper):
            return _respuesta_rechazo(
                "acta_en_identificacion",
                "El documento es un acta de nacimiento. En este campo solo se acepta identificación oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )

        if re.search(r"SOLICITUD\s+(INTERNA|DE\s+EMPLEO|EMPLEO|DE\s+TRABAJO|TRABAJO)|CURR.?CULUM|CURRICULUM\s+VITAE|EXPERIENCIA\s+LABORAL", texto_upper):
            return _respuesta_rechazo(
                "solicitud_en_identificacion",
                "El documento corresponde a solicitud/CV. En este campo solo se acepta identificacion oficial.",
                paginas=paginas,
                indicadores=indicadores,
                modo="documento_equivocado_texto",
                tiempo_ms=int((time.time() - inicio) * 1000),
            )

    if paginas <= 0:
        mensaje = "No se pudo abrir el PDF para revisión rápida."
        valido = False
    elif valido:
        mensaje = "El PDF parece corresponder a una identificación oficial."
    elif texto.strip():
        mensaje = "El PDF no parece corresponder a una identificación oficial. Sube INE, residencia o pasaporte vigente."
    else:
        mensaje = "No se pudo leer suficiente texto del PDF. Sube una identificación oficial clara."

    return {
        "valido": valido,
        "revision_manual": not valido,
        "mensaje": mensaje,
        "paginas": paginas,
        "indicadores": indicadores,
        "tipo_identificacion": _tipo_identificacion_desde_indicadores(indicadores) if valido else None,
        "modo": extraido.get("modo"),
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }


@router.post(
    "/verificar-calidad-identificacion-pdf",
    summary="Revisión de calidad de identificación oficial (PDF)",
    description="""
    Sube un PDF de identificación oficial (frente y reverso en 1 o 2 páginas).
    El documento **siempre se acepta**; el sistema devuelve **notas de revisión** (ej. exceso de brillo, borroso)
    para que Capital Humano revise manualmente en el modal de documentación.
    """,
    tags=["Utilidades"]
)
async def verificar_calidad_identificacion_pdf(
    documento: UploadFile = File(..., description="PDF de identificación oficial (frente y opcionalmente reverso)"),
    api_key: str = Depends(verificar_api_key)
):
    if not documento.filename or not documento.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Se requiere un archivo PDF de identificación oficial")
    file_bytes = await documento.read()
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="Documento vacío")

    inicio = time.time()
    imagenes = pdf_paginas_a_png_bytes(file_bytes, dpi=115, max_paginas=2)
    if not imagenes:
        return {
            "aceptado": True,
            "notas": ["No se pudo procesar el PDF para revisión de calidad. Revisar identificación manualmente."],
            "detalle_frente": None,
            "detalle_reverso": None,
            "tiempo_ms": int((time.time() - inicio) * 1000),
        }

    service = VerificacionService()
    notas: List[str] = []
    detalle_frente: Optional[Dict[str, Any]] = None
    detalle_reverso: Optional[Dict[str, Any]] = None

    def _texto_calidad(calidad: str) -> str:
        """Convierte calidad_foto a texto legible para las notas."""
        if not calidad or calidad == "ok":
            return ""
        if calidad == "revisar_brillo":
            return "posible exceso de brillo o reflejo en la imagen."
        if calidad == "revisar_borroso":
            return "posible imagen borrosa o desenfocada."
        if calidad == "revisar_brillo_y_borroso":
            return "posible exceso de brillo y/o imagen borrosa."
        return calidad.replace("_", " ") + "."

    try:
        # Página 1 = frente
        forense_f = await asyncio.to_thread(service.forense_analyzer.analyze, imagenes[0])
        detalle_frente = {
            "calidad_foto": forense_f.calidad_foto,
            "brillo_excesivo": forense_f.brillo_excesivo,
            "porcentaje_sobreexpuesto": forense_f.porcentaje_sobreexpuesto,
            "borroso": forense_f.borroso,
            "alertas": forense_f.alertas or [],
            "nombre_ocr": None,
            "curp_ocr": None,
        }
        # Nota por calidad (texto amigable, una sola vez)
        texto_calidad = _texto_calidad(forense_f.calidad_foto)
        if texto_calidad:
            notas.append("Revisar identificación oficial: sistema detectó " + texto_calidad)
        # Alertas forense (evitar duplicar si ya se dijo algo similar)
        for a in (forense_f.alertas or []):
            if a and a.strip() and a not in notas:
                notas.append("Revisar identificación oficial: " + a.strip())

        # Página 2 = reverso (si existe)
        if len(imagenes) > 1:
            forense_r = await asyncio.to_thread(service.forense_analyzer.analyze, imagenes[1])
            detalle_reverso = {
                "calidad_foto": forense_r.calidad_foto,
                "brillo_excesivo": forense_r.brillo_excesivo,
                "borroso": forense_r.borroso,
                "alertas": forense_r.alertas or [],
                "mrz_nombre": None,
                "mrz_fecha_nac": None,
            }
            texto_rev = _texto_calidad(forense_r.calidad_foto)
            if texto_rev and not any("reverso" in n.lower() for n in notas):
                notas.append("Reverso: sistema detectó " + texto_rev)
            for a in (forense_r.alertas or []):
                if a and a.strip() and not any(a.strip() in n for n in notas):
                    notas.append("Reverso: " + a.strip())

        if not notas:
            notas.append("Revisión automática sin observaciones. Revisar identificación manualmente si lo considera necesario.")
    except Exception as e:
        logger.exception(f"verificar_calidad_identificacion_pdf: {e}")
        notas = ["Error al procesar la revisión de calidad. Revisar identificación manualmente."]
        detalle_frente = None
        detalle_reverso = None

    return {
        "aceptado": True,
        "notas": notas,
        "detalle_frente": detalle_frente,
        "detalle_reverso": detalle_reverso,
        "tiempo_ms": int((time.time() - inicio) * 1000),
    }


@router.post(
    "/validar-expediente",
    summary="Validación cruzada de expediente completo",
    description="""
    Identificación oficial: se puede enviar de dos formas:
    - **Opción A:** Un solo PDF (identificacion_pdf) con frente y reverso en 1 o 2 páginas. Página 1 = frente, página 2 = reverso (si existe).
    - **Opción B:** Dos imágenes por separado (frente y reverso).

    El resto del expediente: CURP.pdf, NSS.pdf, constancia_fiscal.pdf, acta_nacimiento.pdf (opcionales).

    **Reglas:**
    - Si la foto de la identificación tiene brillo excesivo o está borrosa → RECHAZADA.
    - CURP y nombre se comparan entre: ID, CURP.pdf, constancia fiscal, NSS.
    - Fecha de nacimiento se compara entre: CURP (decodificado), MRZ reverso, acta.
    - La constancia CURP debe tener máximo 3 meses de descargada.
    """,
    tags=["Verificación"]
)
async def validar_expediente(
    frente: Optional[UploadFile] = File(None, description="Imagen del frente (opcional si se envía identificacion_pdf)"),
    reverso: Optional[UploadFile] = File(None, description="Imagen del reverso (opcional si se envía identificacion_pdf)"),
    identificacion_pdf: Optional[UploadFile] = File(None, description="PDF de identificación oficial (frente y reverso en 1 o 2 páginas)"),
    solicitud_interna: Optional[UploadFile] = File(None, description="PDF solicitud interna"),
    cv_solicitud: Optional[UploadFile] = File(None, description="PDF CV o solicitud de trabajo"),
    documento_curp: Optional[UploadFile] = File(None, description="PDF constancia CURP"),
    documento_nss: Optional[UploadFile] = File(None, description="PDF constancia NSS"),
    constancia_fiscal: Optional[UploadFile] = File(None, description="PDF constancia de situación fiscal"),
    acta_nacimiento: Optional[UploadFile] = File(None, description="PDF acta de nacimiento"),
    comprobante_domicilio: Optional[UploadFile] = File(None, description="PDF comprobante de domicilio"),
    hoja_retencion: Optional[UploadFile] = File(None, description="PDF hoja de retencion FONACOT o INFONAVIT"),
    __SPARTA_SECRET_REDACTED__: Optional[UploadFile] = File(None, description="PDF estado de cuenta"),
    nombre_candidato_registro: Optional[str] = Form(None, description="Nombre registrado del candidato en Sparta Ledger"),
    lecturas_json: Optional[str] = Form(None, description="Lecturas IA rapidas ya guardadas por documento"),
    lecturas_json_b64: Optional[str] = Form(None, description="Lecturas IA rapidas en base64 para evitar problemas de encoding multipart"),
    tipo_documento_query: Optional[TipoDocumento] = Query(
        None,
        alias="tipo_documento",
        description="Tipo de identificación (query). Si no se envía, se usa Form o el valor por defecto.",
    ),
    tipo_documento_form: Optional[TipoDocumento] = Form(
        None,
        alias="tipo_documento",
        description="Tipo de identificación (campo form multipart). PHP lo envía con este nombre; coincide con ?tipo_documento= en query.",
    ),
    api_key: str = Depends(verificar_api_key)
):
    # Query tiene prioridad sobre Form; si ambos faltan, mantener default histórico (residencia).
    tipo_documento = (
        tipo_documento_query
        if tipo_documento_query is not None
        else (tipo_documento_form if tipo_documento_form is not None else TipoDocumento.RESIDENCIA_TEMPORAL)
    )

    # Resolver frente y reverso solo si se usa el motor legacy. Motor V2 puede
    # cruzar usando lecturas rapidas guardadas y evita renderizar de nuevo.
    frente_bytes: Optional[bytes] = None
    reverso_bytes: Optional[bytes] = None
    tiempos_fase: Dict[str, int] = {}

    identificacion_pdf_bytes: Optional[bytes] = None
    if identificacion_pdf and identificacion_pdf.filename and identificacion_pdf.filename.lower().endswith(".pdf"):
        pdf_bytes = await identificacion_pdf.read()
        identificacion_pdf_bytes = pdf_bytes

    async def _leer_pdf(upload: Optional[UploadFile]) -> Optional[bytes]:
        if upload and upload.filename and upload.filename.lower().endswith(".pdf"):
            data = await upload.read()
            return data if data else None
        return None

    solicitud_pdf_bytes = await _leer_pdf(solicitud_interna)
    cv_pdf_bytes = await _leer_pdf(cv_solicitud)
    curp_pdf_bytes = await _leer_pdf(documento_curp)
    nss_pdf_bytes = await _leer_pdf(documento_nss)
    fiscal_pdf_bytes = await _leer_pdf(constancia_fiscal)
    acta_pdf_bytes = await _leer_pdf(acta_nacimiento)
    domicilio_pdf_bytes = await _leer_pdf(comprobante_domicilio)
    retencion_pdf_bytes = await _leer_pdf(hoja_retencion)
    __SPARTA_SECRET_REDACTED___pdf_bytes = await _leer_pdf(__SPARTA_SECRET_REDACTED__)

    lecturas_previas: Dict[str, Any] = {}
    lecturas_payload = lecturas_json
    if lecturas_json_b64:
        try:
            import base64
            lecturas_payload = base64.b64decode(str(lecturas_json_b64), validate=False).decode("utf-8", errors="replace")
        except Exception:
            lecturas_payload = lecturas_json
    if lecturas_payload:
        parse_errors: List[str] = []
        candidatos_payload = [str(lecturas_payload)]
        try:
            from urllib.parse import unquote_plus
            decoded_url = unquote_plus(str(lecturas_payload))
            if decoded_url and decoded_url not in candidatos_payload:
                candidatos_payload.append(decoded_url)
        except Exception:
            pass
        inicio_json = str(lecturas_payload).find("{")
        fin_json = str(lecturas_payload).rfind("}")
        if inicio_json >= 0 and fin_json > inicio_json:
            recortado = str(lecturas_payload)[inicio_json:fin_json + 1]
            if recortado not in candidatos_payload:
                candidatos_payload.append(recortado)
        for candidato_payload in candidatos_payload:
            try:
                parsed = json.loads(candidato_payload, strict=False)
                if isinstance(parsed, str):
                    parsed = json.loads(parsed, strict=False)
                if isinstance(parsed, dict):
                    lecturas_previas = parsed
                    break
            except Exception as exc:
                parse_errors.append(str(exc))
        if not lecturas_previas and parse_errors:
            logger.warning(
                "validar-expediente V2 no pudo leer lecturas_json: "
                f"{parse_errors[-1]}"
            )
    logger.info(f"validar-expediente V2 lecturas_json_chars={len(lecturas_payload or '')} lecturas_keys={list(lecturas_previas.keys())}")

    if _doc_ai_alibaba_activo():
        ai = _crear_alibaba_ai_crosscheck()
        if ai is None or not ai.enabled():
            mensaje = "DOC_AI_ENGINE=alibaba esta activo, pero faltan credenciales/modelo para validacion cruzada."
            raise HTTPException(status_code=503, detail=mensaje)
        else:
            docs_v2 = [
                {"key": "solicitud_interna", "label": "Solicitud interna", "bytes": solicitud_pdf_bytes, "filename": getattr(solicitud_interna, "filename", None) or "solicitud_interna.pdf"},
                {"key": "cv", "label": "CV o solicitud de trabajo", "bytes": cv_pdf_bytes, "filename": getattr(cv_solicitud, "filename", None) or "cv.pdf"},
                {"key": "acta_nacimiento", "label": "Acta de nacimiento certificada", "bytes": acta_pdf_bytes, "filename": getattr(acta_nacimiento, "filename", None) or "acta_nacimiento.pdf"},
                {"key": "curp", "label": "CURP", "bytes": curp_pdf_bytes, "filename": getattr(documento_curp, "filename", None) or "curp.pdf"},
                {"key": "identificacion_oficial", "label": "Identificacion oficial", "bytes": identificacion_pdf_bytes, "filename": getattr(identificacion_pdf, "filename", None) or "identificacion_oficial.pdf"},
                {"key": "comprobante_domicilio", "label": "Comprobante de domicilio", "bytes": domicilio_pdf_bytes, "filename": getattr(comprobante_domicilio, "filename", None) or "comprobante_domicilio.pdf"},
                {"key": "constancia_fiscal", "label": "Constancia de situacion fiscal", "bytes": fiscal_pdf_bytes, "filename": getattr(constancia_fiscal, "filename", None) or "constancia_fiscal.pdf"},
                {"key": "nss", "label": "Numero de seguridad social", "bytes": nss_pdf_bytes, "filename": getattr(documento_nss, "filename", None) or "nss.pdf"},
                {"key": "hoja_retencion", "label": "Hoja de retencion FONACOT o INFONAVIT", "bytes": retencion_pdf_bytes, "filename": getattr(hoja_retencion, "filename", None) or "hoja_retencion.pdf"},
                {"key": "__SPARTA_SECRET_REDACTED__", "label": "Estado de cuenta", "bytes": __SPARTA_SECRET_REDACTED___pdf_bytes, "filename": getattr(__SPARTA_SECRET_REDACTED__, "filename", None) or "__SPARTA_SECRET_REDACTED__.pdf"},
            ]
            for doc in docs_v2:
                key = str(doc.get("key") or "")
                summary = lecturas_previas.get(key)
                if isinstance(summary, dict):
                    doc["summary"] = summary
                    archivo = summary.get("archivo") or summary.get("filename")
                    if archivo:
                        doc["filename"] = str(archivo)
            docs_v2 = [
                doc for doc in docs_v2
                if doc.get("bytes") or summary_is_usable(doc.get("summary"))
            ]
            logger.info(f"validar-expediente V2 docs={len(docs_v2)} summaries={sum(1 for doc in docs_v2 if summary_is_usable(doc.get('summary')))}")

            quick_expected = {
                "solicitud_interna": "solicitud___SPARTA_SECRET_REDACTED__",
                "cv": "cv",
                "acta_nacimiento": "acta_nacimiento",
                "curp": "curp",
                "identificacion_oficial": "identificacion_oficial",
                "comprobante_domicilio": "comprobante_domicilio",
                "constancia_fiscal": "constancia_fiscal",
                "nss": "nss",
                "hoja_retencion": "infonavit_fonacot",
                "__SPARTA_SECRET_REDACTED__": "__SPARTA_SECRET_REDACTED__",
            }
            # En el flujo real la carga rapida ya deja una lectura V2 guardada
            # por documento. La validacion cruzada debe comparar esas lecturas,
            # no volver a mandar los 10 PDF a IA: eso puede pasar el timeout de
            # PHP en expedientes pesados. Solo rescatamos documentos sin lectura
            # util, normalmente expedientes viejos o migrados.
            force_quick_refresh = set()
            docs_needing_quick = [
                doc for doc in docs_v2
                if (
                    str(doc.get("key") or "") in force_quick_refresh
                    or not summary_is_usable(doc.get("summary"))
                )
                and doc.get("bytes")
                and quick_expected.get(str(doc.get("key") or ""))
            ]
            prefill_ms = 0
            prefill_count = len(docs_needing_quick)
            if docs_needing_quick:
                t_prefill = time.time()
                quick_ai = _crear_alibaba_ai()
                if quick_ai is not None and quick_ai.enabled():
                    quick_ai.timeout_seconds = max(10, min(15, int(getattr(quick_ai, "timeout_seconds", 12) or 12)))
                    quick_ai.max_pages = min(2, int(getattr(quick_ai, "max_pages", 2) or 2))
                    quick_ai.dpi = max(130, min(150, int(getattr(quick_ai, "dpi", 140) or 140)))
                    sem = asyncio.Semaphore(5)

                    async def _prefill_summary(doc: Dict[str, Any]) -> None:
                        key = str(doc.get("key") or "")
                        expected = quick_expected.get(key)
                        if not expected:
                            return
                        async with sem:
                            try:
                                result = await asyncio.wait_for(
                                    asyncio.to_thread(
                                        quick_ai.quick_verify,
                                        doc.get("bytes") or b"",
                                        str(doc.get("filename") or f"{key}.pdf"),
                                        expected,
                                        nombre_candidato_registro,
                                    ),
                                    timeout=int(getattr(quick_ai, "timeout_seconds", 12) or 12) + 4,
                                )
                                if isinstance(result, dict):
                                    doc["summary"] = quick_result_to_summary(
                                        key,
                                        str(doc.get("label") or key),
                                        str(doc.get("filename") or f"{key}.pdf"),
                                        result,
                                    )
                            except Exception as exc:
                                logger.warning(f"No se pudo preleer {key} para crosscheck V2: {exc}")

                    await asyncio.gather(*(_prefill_summary(doc) for doc in docs_needing_quick))
                    prefill_ms = int((time.time() - t_prefill) * 1000)
                    logger.info(
                        "validar-expediente V2 prefill summaries="
                        f"{sum(1 for doc in docs_v2 if summary_is_usable(doc.get('summary')))} "
                        f"needed={prefill_count} ms={prefill_ms}"
                    )

            crosscheck_mode = str(getattr(settings, "doc_ai_crosscheck_mode", "rules") or "rules").strip().lower()
            if crosscheck_mode in {"rules", "local", "rapido", "fast"}:
                resultado_reglas = _resultado_v2_reglas_expediente(docs_v2, nombre_candidato_registro)
                resultado_reglas["tiempos_fase_ms"] = {
                    "prefill_lecturas_rapidas_ms": prefill_ms,
                    "cruce_reglas_ms": int(resultado_reglas.get("elapsed_ms") or 0),
                    "total_endpoint_ms": prefill_ms + int(resultado_reglas.get("elapsed_ms") or 0),
                }
                return _respuesta_alibaba_expediente(resultado_reglas, nombre_candidato_registro)

            timeout_v2 = int(getattr(settings, "doc_ai_crosscheck_timeout_seconds", 90) or 90) + 5
            try:
                resultado_alibaba = await asyncio.wait_for(
                    asyncio.to_thread(ai.crosscheck_expediente, docs_v2, nombre_candidato_registro),
                    timeout=timeout_v2,
                )
                return _respuesta_alibaba_expediente(resultado_alibaba, nombre_candidato_registro)
            except Exception as exc:
                logger.exception(f"Alibaba crosscheck fallo: {exc}")
                resultado_reglas = _resultado_v2_reglas_expediente(docs_v2, nombre_candidato_registro, motivo="fallback_timeout")
                return _respuesta_alibaba_expediente(resultado_reglas, nombre_candidato_registro)

    if identificacion_pdf_bytes and len(identificacion_pdf_bytes) >= 100:
        t_pdf = time.time()
        imagenes = await asyncio.to_thread(pdf_paginas_a_png_bytes, identificacion_pdf_bytes, 150, 2)
        tiempos_fase["pdf_identificacion_a_imagenes_ms"] = int((time.time() - t_pdf) * 1000)
        if imagenes:
            frente_bytes = imagenes[0]
            reverso_bytes = imagenes[1] if len(imagenes) > 1 else imagenes[0]

    if not frente_bytes or not reverso_bytes:
        # Intentar desde frente/reverso por separado
        if frente and reverso:
            frente_bytes = await frente.read()
            reverso_bytes = await reverso.read()
        if not frente_bytes or not reverso_bytes:
            raise HTTPException(
                status_code=400,
                detail="Se requiere identificacion_pdf (PDF con frente y reverso) o bien frente y reverso como imágenes."
            )

    try:
        inicio = time.time()
        service = VerificacionService()
        ocr_fallback_pdf_cache: Optional[CheckOCR] = None

        def _ocr_fallback_desde_pdf() -> Optional[CheckOCR]:
            """Rescate sin threadpool para PDFs de ID que el precheck rapido si puede leer."""
            nonlocal ocr_fallback_pdf_cache
            if ocr_fallback_pdf_cache is not None:
                return ocr_fallback_pdf_cache
            if not identificacion_pdf_bytes:
                return None
            extraido = _extraer_texto_pdf_rapido(identificacion_pdf_bytes, max_paginas=2)
            texto = extraido.get("texto") or ""
            if not _parece_identificacion_oficial(texto):
                rotado = _extraer_texto_identificacion_rotada_pdf(identificacion_pdf_bytes)
                texto_rotado = rotado.get("texto") or ""
                if _parece_identificacion_oficial(texto_rotado):
                    texto = texto_rotado
                    extraido = rotado
            if not _parece_identificacion_oficial(texto):
                return None

            texto_norm = service.ocr_analyzer._normalizar(texto)
            if tipo_documento in [TipoDocumento.INE_NUEVA, TipoDocumento.INE_ANTERIOR]:
                ocr = service.ocr_analyzer._validar_ine(texto_norm)
            elif tipo_documento in [
                TipoDocumento.RESIDENCIA_TEMPORAL,
                TipoDocumento.RESIDENCIA_TEMPORAL_ACUMULATIVA,
                TipoDocumento.RESIDENCIA_PERMANENTE,
            ]:
                ocr = service.ocr_analyzer._validar_residencia(texto_norm, tipo_documento)
            else:
                ocr = service.ocr_analyzer._validacion_generica(texto_norm)

            mrz = service.ocr_analyzer._parsear_mrz(texto_norm)
            alertas = list(ocr.alertas or [])
            alertas.append(
                "OCR profundo agoto el tiempo; se uso lectura rapida del PDF "
                f"({extraido.get('modo') or 'sin_modo'})."
            )
            update = {
                "alertas": alertas,
                "score": max(float(ocr.score or 0.0), 0.55),
                "ok": True,
            }
            if mrz.get("nombre_completo") and not ocr.mrz_nombre_completo:
                update["mrz_nombre_completo"] = mrz["nombre_completo"]
            if mrz.get("fecha_nacimiento") and not ocr.mrz_fecha_nacimiento:
                update["mrz_fecha_nacimiento"] = mrz["fecha_nacimiento"]
            ocr_fallback_pdf_cache = ocr.model_copy(update=update)
            return ocr_fallback_pdf_cache

        async def _analizar_identificacion_para_cruce(img_bytes: bytes) -> Dict[str, Any]:
            """Analisis acotado para validacion cruzada: calidad + OCR de campos."""
            try:
                forense, ocr = await asyncio.wait_for(
                    asyncio.gather(
                        asyncio.to_thread(service.forense_analyzer.analyze, img_bytes),
                        asyncio.to_thread(service.ocr_analyzer.analyze, img_bytes, tipo_documento),
                    ),
                    timeout=45,
                )
                score = round(((forense.score or 0.0) * 0.4 + (ocr.score or 0.0) * 0.6) * 100)
                return {"score": score, "forense": forense, "ocr": ocr, "timeout": False}
            except asyncio.TimeoutError:
                ocr_rescate = _ocr_fallback_desde_pdf()
                if ocr_rescate is not None:
                    forense_rescate = CheckForense(
                        ok=False,
                        ela_score=0.0,
                        calidad_foto="revision_manual",
                        alertas=["Forense profundo no termino antes del timeout; revisar calidad manualmente."],
                        score=0.5,
                    )
                    score = round(((forense_rescate.score or 0.0) * 0.4 + (ocr_rescate.score or 0.0) * 0.6) * 100)
                    return {"score": score, "forense": forense_rescate, "ocr": ocr_rescate, "timeout": False}
                return {
                    "score": 0,
                    "forense": CheckForense(
                        ok=False,
                        ela_score=0.0,
                        calidad_foto="revision_manual",
                        alertas=["La identificacion no se pudo analizar en el tiempo esperado."],
                        score=0.3,
                    ),
                    "ocr": CheckOCR(
                        ok=False,
                        alertas=["OCR de identificacion agoto el tiempo de espera."],
                        score=0.0,
                    ),
                    "timeout": True,
                }

        t_id = time.time()
        if frente_bytes == reverso_bytes:
            res_frente = await _analizar_identificacion_para_cruce(frente_bytes)
            res_reverso = res_frente
        else:
            res_frente, res_reverso = await asyncio.gather(
                _analizar_identificacion_para_cruce(frente_bytes),
                _analizar_identificacion_para_cruce(reverso_bytes),
            )
        tiempos_fase["identificacion_ocr_forense_ms"] = int((time.time() - t_id) * 1000)

        ocr_f = res_frente["ocr"]
        ocr_r = res_reverso["ocr"]
        calidad = res_frente["forense"].calidad_foto

        # Estos PDFs pueden disparar OCR. Ejecutarlos en paralelo evita sumar sus tiempos uno tras otro.
        t_pdfs = time.time()
        datos_curp, datos_nss, datos_fiscal, datos_acta = await asyncio.gather(
            _ejecutar_pdf_timeout("CURP", extraer_datos_curp_pdf, curp_pdf_bytes, timeout=5) if curp_pdf_bytes else asyncio.sleep(0, result=None),
            _ejecutar_pdf_timeout("NSS", extraer_datos_nss_pdf, nss_pdf_bytes, timeout=5) if nss_pdf_bytes else asyncio.sleep(0, result=None),
            _ejecutar_pdf_timeout(
                "Constancia fiscal",
                extraer_datos_constancia_fiscal,
                fiscal_pdf_bytes,
                timeout=10,
                fallback={"parece_constancia_fiscal": None},
            ) if fiscal_pdf_bytes else asyncio.sleep(0, result=None),
            _ejecutar_pdf_timeout(
                "Acta de nacimiento",
                lambda data: extraer_datos_acta_nacimiento(data, modo_rapido=True),
                acta_pdf_bytes,
                timeout=8,
                fallback={"parece_acta": None},
            ) if acta_pdf_bytes else asyncio.sleep(0, result=None),
        )
        tiempos_fase["pdfs_datos_ms"] = int((time.time() - t_pdfs) * 1000)

        t_cruce = time.time()
        resultado = validacion_cruzada(
            id_frente_curp=ocr_f.curp.get("valor") if ocr_f.curp else None,
            id_frente_nombre=ocr_f.nombre_ocr,
            id_frente_fecha_nac_curp=ocr_f.fecha_nacimiento_curp,
            id_reverso_mrz_nombre=ocr_r.mrz_nombre_completo,
            id_reverso_mrz_fecha_nac=ocr_r.mrz_fecha_nacimiento,
            calidad_foto=calidad,
            datos_curp_pdf=datos_curp,
            datos_nss=datos_nss,
            datos_fiscal=datos_fiscal,
            datos_acta=datos_acta,
            nombre_candidato_registro=nombre_candidato_registro,
        )
        tiempos_fase["cruce_reglas_ms"] = int((time.time() - t_cruce) * 1000)

        tiempo_ms = int((time.time() - inicio) * 1000)

        # Nombre: preferir MRZ (reverso) por ser estándar; si no, frente OCR
        nombre_ocr = (ocr_r.mrz_nombre_completo if ocr_r and ocr_r.mrz_nombre_completo else None) or (ocr_f.nombre_ocr if ocr_f else None)
        # Año nacimiento: de CURP (frente) o MRZ (reverso)
        fecha_nac = (ocr_f.fecha_nacimiento_curp if ocr_f else None) or (ocr_r.mrz_fecha_nacimiento if ocr_r else None)
        anio_nacimiento = None
        if fecha_nac:
            if isinstance(fecha_nac, str):
                m = re.search(r"(19|20)\d{2}", fecha_nac)
                if m:
                    anio_nacimiento = m.group(0)
            else:
                anio_nacimiento = str(fecha_nac)[:4] if len(str(fecha_nac)) >= 4 else None
        tipo_doc_val = tipo_documento.value if hasattr(tipo_documento, "value") else str(tipo_documento)

        return {
            **resultado,
            "identificacion_frente_score": res_frente["score"],
            "identificacion_reverso_score": res_reverso["score"],
            "identificacion_timeout": bool(res_frente.get("timeout") or res_reverso.get("timeout")),
            "nombre_ocr": nombre_ocr,
            "anio_nacimiento": anio_nacimiento,
            "tipo_documento": tipo_doc_val,
            "documentos_procesados": {
                "identificacion_frente": True,
                "identificacion_reverso": True,
                "curp_pdf": datos_curp is not None,
                "nss_pdf": datos_nss is not None,
                "constancia_fiscal": datos_fiscal is not None,
                "acta_nacimiento": datos_acta is not None,
            },
            "datos_extraidos": {
                "curp_pdf": datos_curp,
                "nss_pdf": datos_nss,
                "constancia_fiscal": datos_fiscal,
                "acta_nacimiento": datos_acta,
            },
            "tiempo_proceso_ms": tiempo_ms,
            "tiempos_fase_ms": tiempos_fase,
        }
    except Exception as e:
        logger.exception(f"Error en validación de expediente: {e}")
        raise HTTPException(status_code=500, detail=f"Error en validación: {str(e)}")


@router.post(
    "/validar-expediente-json",
    summary="Validacion cruzada de expediente completo por JSON/base64",
    description="Ruta alternativa para evitar fallos del parser multipart con expedientes grandes.",
    tags=["Verificacion"]
)
async def validar_expediente_json(
    payload: Dict[str, Any] = Body(...),
    api_key: str = Depends(verificar_api_key)
):
    tipo_raw = str(payload.get("tipo_documento") or "INE_NUEVA").strip()
    try:
        tipo_doc = TipoDocumento(tipo_raw)
    except Exception:
        tipo_doc = TipoDocumento.INE_NUEVA

    return await validar_expediente(
        frente=_payload_upload_from_b64(payload, "frente", "frente.jpg"),
        reverso=_payload_upload_from_b64(payload, "reverso", "reverso.jpg"),
        identificacion_pdf=_payload_upload_from_b64(payload, "identificacion_pdf", "identificacion_oficial.pdf"),
        solicitud_interna=_payload_upload_from_b64(payload, "solicitud_interna", "solicitud_interna.pdf"),
        cv_solicitud=_payload_upload_from_b64(payload, "cv_solicitud", "cv.pdf"),
        documento_curp=_payload_upload_from_b64(payload, "documento_curp", "curp.pdf"),
        documento_nss=_payload_upload_from_b64(payload, "documento_nss", "nss.pdf"),
        constancia_fiscal=_payload_upload_from_b64(payload, "constancia_fiscal", "constancia_fiscal.pdf"),
        acta_nacimiento=_payload_upload_from_b64(payload, "acta_nacimiento", "acta_nacimiento.pdf"),
        comprobante_domicilio=_payload_upload_from_b64(payload, "comprobante_domicilio", "comprobante_domicilio.pdf"),
        hoja_retencion=_payload_upload_from_b64(payload, "hoja_retencion", "hoja_retencion.pdf"),
        __SPARTA_SECRET_REDACTED__=_payload_upload_from_b64(payload, "__SPARTA_SECRET_REDACTED__", "__SPARTA_SECRET_REDACTED__.pdf"),
        nombre_candidato_registro=payload.get("nombre_candidato_registro"),
        lecturas_json=None,
        lecturas_json_b64=payload.get("lecturas_json_b64"),
        tipo_documento_query=tipo_doc,
        tipo_documento_form=None,
        api_key=api_key,
    )


@router.get(
    "/tipos-documento",
    summary="Tipos de documento soportados",
    tags=["Utilidades"]
)
async def listar_tipos():
    """Lista los tipos de documentos que el sistema puede verificar."""
    return {
        "tipos_soportados": [
            {"codigo": "INE_NUEVA", "nombre": "INE / Credencial para Votar (2014 en adelante)", "emisor": "Instituto Nacional Electoral"},
            {"codigo": "INE_ANTERIOR", "nombre": "IFE / Credencial para Votar (anterior a 2014)", "emisor": "Instituto Federal Electoral"},
            {"codigo": "RESIDENCIA_TEMPORAL", "nombre": "Tarjeta de Residencia Temporal", "emisor": "Instituto Nacional de Migración"},
            {"codigo": "RESIDENCIA_TEMPORAL_ACUMULATIVA", "nombre": "Tarjeta de Residencia Temporal Acumulativa", "emisor": "Instituto Nacional de Migración"},
            {"codigo": "RESIDENCIA_PERMANENTE", "nombre": "Tarjeta de Residencia Permanente", "emisor": "Instituto Nacional de Migración"},
        ]
    }


@router.get("/health", tags=["Sistema"])
async def health_check():
    """Verifica que el servicio esté funcionando."""
    return {"status": "ok", "version": settings.app_version, "build": API_BUILD}
