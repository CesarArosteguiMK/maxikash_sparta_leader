import json
import re
import time
from pathlib import Path

import httpx


API = "http://127.0.0.1:8000/api/v1"
HEADERS = {"X-API-Key": "sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key"}

FOLDERS = [
    Path(r"C:\Users\amigo_j9s4pcx\Downloads\prueba candidato"),
    Path(r"C:\Users\amigo_j9s4pcx\OneDrive\Desktop\prueba OCR"),
    Path(r"C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\pruebas_OCR"),
    Path(r"C:\xampp\htdocs\pruebas_OCR"),
]


def classify(path: Path) -> str | None:
    name = path.name.lower()
    if not path.exists() or path.suffix.lower() != ".pdf":
        return None
    if re.search(r"curriculo|curriculum|cv |cv_|cv\(|solicitud|__SPARTA_SECRET_REDACTED__|empleo", name):
        return None
    if re.search(r"domicilio|comprobante", name):
        return "domicilio"
    if re.search(r"constancia|fiscal|cif|csf|situacion", name):
        return "fiscal"
    if re.search(r"\bcurp\b|curp", name):
        return "curp"
    if re.search(r"nss|seguridad social|imss|comprobantenss|tarjetanss|tarjeta", name):
        return "nss"
    if re.search(r"estado|bbva|cuenta", name):
        return "__SPARTA_SECRET_REDACTED__"
    if re.search(r"acta|nacimiento", name):
        return "acta"
    if re.search(r"ine|identificacion|indentificacion|pasaporte|passport", name):
        return "identificacion"
    return "desconocido"


ENDPOINTS = {
    "domicilio": ("/verificar-comprobante", "documento"),
    "fiscal": ("/verificar-constancia-fiscal-documento", "documento"),
    "curp": ("/verificar-curp-documento", "documento"),
    "nss": ("/verificar-nss-documento", "documento"),
    "__SPARTA_SECRET_REDACTED__": ("/verificar-estado-cuenta", "documento"),
    "acta": ("/verificar-acta-documento", "documento"),
    "identificacion": ("/precheck-identificacion-pdf", "documento"),
}


def short_result(data):
    if not isinstance(data, dict):
        return {"raw": str(data)[:300]}
    keys = [
        "valido", "resultado", "revision_manual", "rechazado", "timeout",
        "mensaje", "recomendacion", "motivo_rechazo", "curp_extraido",
        "nss_extraido", "nombre", "nombre_ocr", "tipo_comprobante",
        "banco_detectado", "tiene_datos_titular", "es_banco_fisico",
        "parece_curp", "parece_acta", "tiempo_ms", "modo",
    ]
    out = {k: data.get(k) for k in keys if k in data}
    if "score_validacion" in data:
        out["score_validacion"] = data.get("score_validacion")
    if "tiempos_fase_ms" in data:
        out["tiempos_fase_ms"] = data.get("tiempos_fase_ms")
    if "resumen" in data:
        out["resumen"] = data.get("resumen")
    if "comparaciones" in data:
        out["comparaciones"] = data.get("comparaciones")
    if "alertas" in data and data.get("alertas"):
        out["alertas"] = data.get("alertas")[:3]
    return out


def post_pdf(client, endpoint, field, path, timeout=90):
    t0 = time.perf_counter()
    try:
        with path.open("rb") as fh:
            files = {field: (path.name, fh, "application/pdf")}
            resp = client.post(API + endpoint, headers=HEADERS, files=files, timeout=timeout)
        try:
            data = resp.json()
        except Exception:
            data = {"body": resp.text[:1000]}
        status = resp.status_code
    except Exception as exc:
        data = {"error": type(exc).__name__, "mensaje": str(exc)}
        status = "CLIENT_ERROR"
    elapsed = int((time.perf_counter() - t0) * 1000)
    return {
        "status": status,
        "elapsed_ms": elapsed,
        "data": data,
        "short": short_result(data),
    }


def build_folder_sets(items):
    grouped = {}
    for item in items:
        grouped.setdefault(str(item["folder"]), {})[item["kind"]] = item["path"]
    return grouped


def post_expediente(client, folder, docs, timeout=180):
    required = {
        "identificacion": "identificacion_pdf",
        "curp": "documento_curp",
        "nss": "documento_nss",
        "fiscal": "constancia_fiscal",
        "acta": "acta_nacimiento",
    }
    if "identificacion" not in docs:
        return None
    opened = []
    files = {}
    try:
        for kind, field in required.items():
            path = docs.get(kind)
            if path:
                fh = path.open("rb")
                opened.append(fh)
                files[field] = (path.name, fh, "application/pdf")
        data = {"nombre_candidato_registro": ""}
        t0 = time.perf_counter()
        try:
            resp = client.post(
                API + "/validar-expediente",
                headers=HEADERS,
                data=data,
                files=files,
                timeout=timeout,
            )
            try:
                body = resp.json()
            except Exception:
                body = {"body": resp.text[:1000]}
            status = resp.status_code
        except Exception as exc:
            body = {"error": type(exc).__name__, "mensaje": str(exc)}
            status = "CLIENT_ERROR"
        elapsed = int((time.perf_counter() - t0) * 1000)
        return {
            "folder": folder,
            "status": status,
            "elapsed_ms": elapsed,
            "docs": {k: str(v.name) for k, v in docs.items()},
            "short": short_result(body),
            "data": body,
        }
    finally:
        for fh in opened:
            fh.close()


def main():
    pdfs = []
    for folder in FOLDERS:
        if not folder.exists():
            continue
        for path in sorted(folder.iterdir()):
            kind = classify(path)
            if kind:
                pdfs.append({"folder": folder, "path": path, "kind": kind})

    report = {
        "api": API,
        "total_pdfs_relevantes": len(pdfs),
        "document_tests": [],
        "expediente_tests": [],
    }
    with httpx.Client() as client:
        health = client.get(API + "/health", timeout=10)
        report["health"] = {"status": health.status_code, "body": health.json()}
        for item in pdfs:
            kind = item["kind"]
            endpoint = ENDPOINTS.get(kind)
            if not endpoint:
                report["document_tests"].append({
                    "folder": str(item["folder"]),
                    "file": item["path"].name,
                    "kind": kind,
                    "skipped": "sin_endpoint_directo",
                })
                continue
            ep, field = endpoint
            result = post_pdf(client, ep, field, item["path"])
            report["document_tests"].append({
                "folder": str(item["folder"]),
                "file": item["path"].name,
                "kind": kind,
                "endpoint": ep,
                **result,
            })

        for folder, docs in build_folder_sets(pdfs).items():
            result = post_expediente(client, folder, docs)
            if result:
                report["expediente_tests"].append(result)

    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
