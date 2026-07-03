#!/usr/bin/env python
"""Regression checks for the candidate-document AI flow.

The script references local real-world PDFs/ZIPs when present, but does not copy
or store those documents in the repository. Missing files are reported as
skipped so the suite can still run on another workstation.
"""
from __future__ import annotations

import argparse
import base64
import json
import re
import sys
import time
import zipfile
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple

import requests


API_BASE_DEFAULT = "http://127.0.0.1:8001/api/v1"
API_KEY_DEFAULT = "sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key"


ROOT_DOWNLOADS = Path(r"C:\Users\amigo_j9s4pcx\Downloads")
ROOT_PRUEBAS = ROOT_DOWNLOADS / "Pruebas"


def read_case_bytes(case: Dict[str, Any]) -> Optional[Tuple[str, bytes]]:
    if case.get("path"):
        path = Path(case["path"])
        if not path.is_file():
            return None
        return path.name, path.read_bytes()
    zip_path = Path(case["zip_path"])
    if not zip_path.is_file():
        return None
    with zipfile.ZipFile(zip_path) as zf:
        inner = case["zip_inner"]
        return Path(inner).name, zf.read(inner)


def post_pdf(api_base: str, api_key: str, endpoint: str, filename: str, data: bytes, timeout: int = 90) -> Dict[str, Any]:
    start = time.perf_counter()
    resp = requests.post(
        f"{api_base.rstrip('/')}/{endpoint.lstrip('/')}",
        headers={"X-API-Key": api_key, "Accept": "application/json"},
        files={"documento": (filename, data, "application/pdf")},
        timeout=timeout,
    )
    elapsed_ms = int((time.perf_counter() - start) * 1000)
    try:
        body = resp.json()
    except Exception:
        body = {"raw": resp.text[:1000]}
    return {"status_code": resp.status_code, "elapsed_ms": elapsed_ms, "body": body}


def check(condition: bool, message: str) -> Optional[str]:
    return None if condition else message


def result(name: str, status: str, details: Dict[str, Any]) -> Dict[str, Any]:
    return {"name": name, "status": status, **details}


def case_quick(api_base: str, api_key: str, case: Dict[str, Any]) -> Dict[str, Any]:
    loaded = read_case_bytes(case)
    if loaded is None:
        return result(case["name"], "skipped", {"reason": "archivo no encontrado"})
    filename, data = loaded
    response = post_pdf(api_base, api_key, case["endpoint"], filename, data)
    body = response["body"]
    failures: List[str] = []
    failures.append(check(response["status_code"] == 200, f"HTTP {response['status_code']}"))
    for key, expected in (case.get("expect") or {}).items():
        failures.append(check(body.get(key) == expected, f"{key}: esperado {expected!r}, recibido {body.get(key)!r}"))
    if case.get("contains"):
        text = json.dumps(body, ensure_ascii=False).upper()
        for needle in case["contains"]:
            failures.append(check(str(needle).upper() in text, f"no contiene {needle!r}"))
    failures = [f for f in failures if f]
    return result(
        case["name"],
        "failed" if failures else "passed",
        {
            "failures": failures,
            "elapsed_ms": response["elapsed_ms"],
            "status_code": response["status_code"],
            "message": body.get("mensaje") or body.get("recomendacion"),
        },
    )


def case_diaz_cross(api_base: str, api_key: str) -> Dict[str, Any]:
    case = {
        "name": "acta_nombre_distinto_diaz",
        "zip_path": ROOT_PRUEBAS / "8. ABRIL QNA 2-20260629T193225Z-3-001.zip",
        "zip_inner": "8. ABRIL QNA 2/6. DIAZ CARREÑO MARIA DE LOURDES/ACTA NACIMIENTO.pdf",
    }
    loaded = read_case_bytes(case)
    if loaded is None:
        return result(case["name"], "skipped", {"reason": "archivo no encontrado"})
    filename, data = loaded
    quick = post_pdf(api_base, api_key, "verificar-acta-documento", filename, data)
    body = quick["body"]
    summary = {
        "key": "acta_nacimiento",
        "tipo_documento": "Acta de nacimiento certificada",
        "archivo": filename,
        "paginas_pdf": body.get("paginas_pdf") or body.get("paginas"),
        "motor_ia": body.get("motor_ia"),
        "modelo_ia": body.get("modelo_ia"),
        "fuente_lectura": body.get("fuente_lectura") or "regression",
        "validacion_previa": body,
    }
    payload = {
        "tipo_documento": "INE_NUEVA",
        "nombre_candidato_registro": "DIAZ CARREÑO MARIA DE LOURDES",
        "lecturas_json_b64": base64.b64encode(
            json.dumps({"acta_nacimiento": summary}, ensure_ascii=False).encode("utf-8")
        ).decode("ascii"),
    }
    start = time.perf_counter()
    resp = requests.post(
        f"{api_base.rstrip()}/validar-expediente-json",
        headers={"X-API-Key": api_key, "Content-Type": "application/json"},
        json=payload,
        timeout=60,
    )
    elapsed_ms = int((time.perf_counter() - start) * 1000)
    cross = resp.json()
    failures = [
        check(resp.status_code == 200, f"HTTP {resp.status_code}"),
        check(cross.get("dictamen_ia") == "rechazado", f"dictamen esperado rechazado, recibido {cross.get('dictamen_ia')!r}"),
        check("no coincide" in json.dumps(cross, ensure_ascii=False).lower(), "no se encontro motivo de nombre no coincide"),
    ]
    failures = [f for f in failures if f]
    return result(
        case["name"],
        "failed" if failures else "passed",
        {"failures": failures, "elapsed_ms": elapsed_ms, "quick_ms": quick["elapsed_ms"], "dictamen": cross.get("dictamen_ia")},
    )


def gomez_consensus_summaries() -> Dict[str, Dict[str, Any]]:
    def summary(key: str, label: str, filename: str, pages: int, previo: Dict[str, Any]) -> Dict[str, Any]:
        payload = {
            "key": key,
            "tipo_documento": label,
            "archivo": filename,
            "paginas_pdf": pages,
            "motor_ia": "motor_v1",
            "modelo_ia": "regression",
            "fuente_lectura": "pdf_text",
            "validacion_previa": {
                "valido": True,
                "rechazado": False,
                "revision_manual": False,
                **previo,
            },
        }
        return payload

    return {
        "solicitud_interna": summary(
            "solicitud_interna",
            "Solicitud interna",
            "SOLICITUD.pdf",
            2,
            {
                "tipo_documento_detectado": "solicitud___SPARTA_SECRET_REDACTED__",
                "nombre": "Silvia Gomez Velez",
                "rfc": "GOVS711012YN3",
                "nss": "54877166709",
                "mensaje": "solicitud interna listo.",
            },
        ),
        "cv": summary(
            "cv",
            "CV o solicitud de trabajo",
            "CV.pdf",
            1,
            {"tipo_documento_detectado": "cv", "nombre": "SILVIA GOMEZ VEJAR", "mensaje": "CV listo."},
        ),
        "acta_nacimiento": summary(
            "acta_nacimiento",
            "Acta de nacimiento certificada",
            "ACTA NACIMIENTO.pdf",
            1,
            {"tipo_documento_detectado": "acta_nacimiento", "nombre": "SILVIA GOMEZ VEJAR", "mensaje": "acta de nacimiento listo."},
        ),
        "curp": summary(
            "curp",
            "CURP",
            "CURP.pdf",
            1,
            {"tipo_documento_detectado": "curp", "nombre": "SILVIA GOMEZ VEJAR", "curp": "GOVS710120MBCMJL04", "meses_antiguedad": 1, "mensaje": "CURP listo."},
        ),
        "identificacion_oficial": summary(
            "identificacion_oficial",
            "Identificacion oficial",
            "INE.pdf",
            2,
            {"tipo_documento_detectado": "ine", "nombre": "GOMEZ VEJAR SILVIA", "curp": "GOVS710120MBCMJL04", "mensaje": "INE lista."},
        ),
        "comprobante_domicilio": summary(
            "comprobante_domicilio",
            "Comprobante de domicilio",
            "DOMICILIO.pdf",
            2,
            {"tipo_documento_detectado": "comprobante_domicilio", "nombre": "GOMEZ VEJAR IRMA YOLANDA", "meses_antiguedad": 1, "mensaje": "domicilio listo."},
        ),
        "constancia_fiscal": summary(
            "constancia_fiscal",
            "Constancia de situacion fiscal",
            "CSF.pdf",
            3,
            {
                "tipo_documento_detectado": "constancia_fiscal",
                "nombre": "SILVIA GOMEZ VEJAR",
                "curp": "GOVS710120MBCMJL04",
                "rfc": "GOVS7101204N3",
                "meses_antiguedad": 1,
                "regimen_sueldos_salarios": True,
                "mensaje": "constancia fiscal listo.",
            },
        ),
        "nss": summary(
            "nss",
            "Numero de seguridad social",
            "NSS.pdf",
            5,
            {"tipo_documento_detectado": "nss", "nombre": "GOMEZ VEJAR SILVIA", "curp": "GOVS710120MBCMJL04", "nss": "54877166709", "mensaje": "NSS listo."},
        ),
        "hoja_retencion": summary(
            "hoja_retencion",
            "Hoja de retencion FONACOT o INFONAVIT",
            "CARTA NO CREDITOS.pdf",
            1,
            {
                "tipo_documento_detectado": "carta_no_adeudo",
                "nombre": "Silvia Gomez Vejar",
                "firma_detectada": True,
                "nombre_y_firma_lleno": True,
                "evidencia_insuficiente": False,
                "mensaje": "carta de no adeudo listo.",
            },
        ),
        "__SPARTA_SECRET_REDACTED__": summary(
            "__SPARTA_SECRET_REDACTED__",
            "Estado de cuenta",
            "BBVA.pdf",
            1,
            {"tipo_documento_detectado": "__SPARTA_SECRET_REDACTED__", "nombre": "SILVIA GOMEZ VEJAR", "banco": "BBVA", "mensaje": "estado de cuenta listo."},
        ),
    }


def case_gomez_consensus_rule(api_base: str, api_key: str, nombre_registro: str, expected_dictamen: str, name: str) -> Dict[str, Any]:
    payload = {
        "tipo_documento": "INE_NUEVA",
        "nombre_candidato_registro": nombre_registro,
        "lecturas_json_b64": base64.b64encode(
            json.dumps(gomez_consensus_summaries(), ensure_ascii=False).encode("utf-8")
        ).decode("ascii"),
    }
    start = time.perf_counter()
    resp = requests.post(
        f"{api_base.rstrip('/')}/validar-expediente-json",
        headers={"X-API-Key": api_key, "Content-Type": "application/json"},
        json=payload,
        timeout=60,
    )
    elapsed_ms = int((time.perf_counter() - start) * 1000)
    cross = resp.json()
    text = json.dumps(cross, ensure_ascii=False).lower()
    failures = [
        check(resp.status_code == 200, f"HTTP {resp.status_code}"),
        check(cross.get("dictamen_ia") == expected_dictamen, f"dictamen esperado {expected_dictamen}, recibido {cross.get('dictamen_ia')!r}"),
    ]
    if expected_dictamen == "requiere_revision":
        failures.append(check("solicitud interna requiere revision" in text, "no se explico revision de solicitud interna"))
        failures.append(check("rfc_principal" in text and "govs7101204n3" in text, "no eligio RFC principal por constancia/CURP"))
    if expected_dictamen == "rechazado":
        failures.append(check("no coincide con el candidato registrado" in text, "no se encontro rechazo por nombre distinto"))
    failures = [f for f in failures if f]
    return result(
        name,
        "failed" if failures else "passed",
        {"failures": failures, "elapsed_ms": elapsed_ms, "dictamen": cross.get("dictamen_ia")},
    )


def case_provider_fallback_guard() -> Dict[str, Any]:
    routes = Path("backend/API/app/api/routes.py")
    if not routes.is_file():
        return result("proveedor_v2_caido_guard", "skipped", {"reason": "routes.py no encontrado"})
    text = routes.read_text(encoding="utf-8", errors="ignore")
    pattern = r"except Exception as exc:\s*logger\.exception\(f\"Alibaba document AI fallo.*?return None"
    ok = re.search(pattern, text, flags=re.S) is not None
    return result(
        "proveedor_v2_caido_guard",
        "passed" if ok else "failed",
        {"failures": [] if ok else ["El helper de Alibaba no garantiza fallback V1/local ante excepcion."]},
    )


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--api-base", default=API_BASE_DEFAULT)
    parser.add_argument("--api-key", default=API_KEY_DEFAULT)
    parser.add_argument("--output", type=Path, default=Path("output/pdf/document_validation_regression_results.json"))
    args = parser.parse_args()

    try:
        health = requests.get(f"{args.api_base.rstrip('/')}/health", headers={"X-API-Key": args.api_key}, timeout=10)
        health.raise_for_status()
    except Exception as exc:
        print(f"API no disponible: {exc}", file=sys.stderr)
        return 2

    quick_cases = [
        {
            "name": "solicitud_manuscrita_buena",
            "endpoint": "verificar-solicitud-interna-documento",
            "path": ROOT_DOWNLOADS / "solicitud internetna.pdf",
            "expect": {"valido": True, "rechazado": False},
            "contains": ["JESUS", "MAYA"],
        },
        {
            "name": "acta_escaneada_buena",
            "endpoint": "verificar-acta-documento",
            "path": ROOT_DOWNLOADS / "acta de nacimiento.pdf",
            "expect": {"valido": True, "rechazado": False},
            "contains": ["JESUS", "MAYA"],
        },
        {
            "name": "pdf_mixto_serrano_rescate",
            "endpoint": "verificar-acta-documento",
            "zip_path": ROOT_PRUEBAS / "8. ABRIL QNA 2-20260629T193225Z-3-001.zip",
            "zip_inner": "8. ABRIL QNA 2/27. SERRANO MIRANDA JOSE LUIS/ACTA NACIMIENTO.pdf",
            "expect": {"valido": True, "rechazado": False, "documento_mixto": True},
            "contains": ["SERRANO", "MIRANDA"],
        },
        {
            "name": "nss_invalido_10_digitos",
            "endpoint": "verificar-nss-documento",
            "zip_path": ROOT_PRUEBAS / "2. ENERO QNA 2.zip",
            "zip_inner": "2. ENERO QNA 2/28. DOMINGUEZ MARTINEZ ALEJANDRO/NSS(1).pdf",
            "expect": {"valido": False},
            "contains": ["NSS"],
        },
    ]

    results = [case_quick(args.api_base, args.api_key, case) for case in quick_cases]
    results.append(case_diaz_cross(args.api_base, args.api_key))
    results.append(case_gomez_consensus_rule(args.api_base, args.api_key, "SILVIA GOMEZ VEJAR", "requiere_revision", "gomez_solicitud_dudosa_requiere_revision"))
    results.append(case_gomez_consensus_rule(args.api_base, args.api_key, "PRUEBA CODEX UPLOADFLOW", "rechazado", "gomez_nombre_registro_falso_rechaza"))
    results.append(case_provider_fallback_guard())

    counts = {status: sum(1 for row in results if row["status"] == status) for status in ("passed", "failed", "skipped")}
    output = {"generated_at": time.strftime("%Y-%m-%d %H:%M:%S"), "counts": counts, "results": results}
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(output, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps(output, ensure_ascii=False, indent=2))
    return 1 if counts["failed"] else 0


if __name__ == "__main__":
    raise SystemExit(main())
