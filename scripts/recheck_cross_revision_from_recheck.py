#!/usr/bin/env python
"""Re-run crosscheck for groups that previously required review."""
from __future__ import annotations

import argparse
import base64
import json
import sys
from pathlib import Path
from typing import Any, Dict

import requests

sys.path.insert(0, str(Path(__file__).resolve().parent))
from document_validation_qa import validation_summary_from_quick  # noqa: E402


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("original_results", type=Path)
    parser.add_argument("recheck_results", type=Path)
    parser.add_argument("--api-base", default="http://127.0.0.1:8001/api/v1")
    parser.add_argument("--api-key", default="sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key")
    parser.add_argument("--output", type=Path, default=None)
    args = parser.parse_args()

    original = json.loads(args.original_results.read_text(encoding="utf-8"))
    rechecked = json.loads(args.recheck_results.read_text(encoding="utf-8"))

    quick_by_id: Dict[str, Dict[str, Any]] = {}
    for row in original.get("quick_results") or []:
        item_id = (row.get("item") or {}).get("id")
        if item_id:
            quick_by_id[item_id] = row
    for row in rechecked:
        item_id = (row.get("item") or {}).get("id")
        if item_id:
            quick_by_id[item_id] = row

    quick_by_group: Dict[str, list[Dict[str, Any]]] = {}
    for row in quick_by_id.values():
        group_id = (row.get("item") or {}).get("group_id")
        if group_id:
            quick_by_group.setdefault(group_id, []).append(row)

    url = args.api_base.rstrip("/") + "/validar-expediente-json"
    headers = {"X-API-Key": args.api_key, "Content-Type": "application/json"}
    out = []
    for cross in original.get("cross_results") or []:
        if cross.get("dictamen") != "requiere_revision":
            continue
        group_id = cross.get("group_id")
        lecturas = {}
        for quick in quick_by_group.get(group_id, []):
            pair = validation_summary_from_quick(quick)
            if pair:
                key, summary = pair
                lecturas[key] = summary
        payload = {
            "tipo_documento": "INE_NUEVA",
            "nombre_candidato_registro": cross.get("candidate_name"),
            "lecturas_json_b64": base64.b64encode(
                json.dumps(lecturas, ensure_ascii=False).encode("utf-8")
            ).decode("ascii"),
        }
        response = requests.post(url, headers=headers, json=payload, timeout=60)
        body = response.json()
        out.append(
            {
                "candidate": cross.get("candidate_name"),
                "old_dictamen": cross.get("dictamen"),
                "new_dictamen": body.get("dictamen_ia"),
                "checks": f"{body.get('checks_ok')}/{body.get('checks_totales')}",
                "alertas": body.get("alertas"),
                "acta": (body.get("documentos_analizados_v2") or {}).get("acta_nacimiento"),
            }
        )

    output = args.output or args.recheck_results.with_name("cross_revision_recheck_results.json")
    output.write_text(json.dumps(out, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps(out, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
