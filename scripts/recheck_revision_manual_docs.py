#!/usr/bin/env python
"""Re-run documents that previously ended in manual review."""
from __future__ import annotations

import argparse
import csv
import json
import sys
from collections import Counter, defaultdict
from dataclasses import asdict
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List

sys.path.insert(0, str(Path(__file__).resolve().parent))

from document_validation_qa import PdfItem, is_candidate_scope_item, run_quick, stats_ms


def load_results(path: Path) -> Dict[str, Any]:
    return json.loads(path.read_text(encoding="utf-8"))


def item_from_row(row: Dict[str, Any]) -> PdfItem:
    raw = row.get("item") or {}
    allowed = PdfItem.__dataclass_fields__.keys()
    data = {key: raw.get(key) for key in allowed}
    return PdfItem(**data)


def summarize(rows: List[Dict[str, Any]]) -> Dict[str, Any]:
    by_type: Dict[str, List[Dict[str, Any]]] = defaultdict(list)
    for row in rows:
        by_type[((row.get("item") or {}).get("doc_type") or "unknown")].append(row)
    return {
        "total": len(rows),
        "outcomes": dict(Counter(row.get("outcome") for row in rows)),
        "by_type": {
            doc_type: {
                "count": len(doc_rows),
                "outcomes": dict(Counter(row.get("outcome") for row in doc_rows)),
                "timing_ms": stats_ms([int(row.get("elapsed_ms") or 0) for row in doc_rows]),
                "messages": Counter((row.get("message") or "")[:180] for row in doc_rows).most_common(10),
            }
            for doc_type, doc_rows in sorted(by_type.items())
        },
    }


def write_csv(path: Path, rows: List[Dict[str, Any]]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", newline="", encoding="utf-8-sig") as fh:
        writer = csv.DictWriter(
            fh,
            fieldnames=[
                "candidate_name",
                "doc_type",
                "filename",
                "display_path",
                "old_outcome",
                "old_message",
                "new_outcome",
                "new_message",
                "elapsed_ms",
                "endpoint",
                "status_code",
            ],
        )
        writer.writeheader()
        for row in rows:
            item = row.get("item") or {}
            previous = row.get("previous") or {}
            writer.writerow(
                {
                    "candidate_name": item.get("candidate_name"),
                    "doc_type": item.get("doc_type"),
                    "filename": item.get("filename"),
                    "display_path": item.get("display_path"),
                    "old_outcome": previous.get("outcome"),
                    "old_message": previous.get("message"),
                    "new_outcome": row.get("outcome"),
                    "new_message": row.get("message"),
                    "elapsed_ms": row.get("elapsed_ms"),
                    "endpoint": row.get("endpoint"),
                    "status_code": row.get("status_code"),
                }
            )


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("results_json", type=Path)
    parser.add_argument("--api-base", default="http://127.0.0.1:8001/api/v1")
    parser.add_argument("--api-key", default="sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key")
    parser.add_argument("--workers", type=int, default=3)
    parser.add_argument("--output-dir", type=Path, default=None)
    parser.add_argument("--doc-type", action="append", default=None)
    parser.add_argument("--include-out-of-scope", action="store_true")
    args = parser.parse_args()

    data = load_results(args.results_json)
    previous_rows = [row for row in data.get("quick_results") or [] if row.get("outcome") == "revision"]
    original_revision_total = len(previous_rows)
    out_of_scope_rows = []
    if not args.include_out_of_scope:
        scoped = []
        for row in previous_rows:
            item = item_from_row(row)
            if is_candidate_scope_item(item):
                scoped.append(row)
            else:
                out_of_scope_rows.append(row)
        previous_rows = scoped
    if args.doc_type:
        wanted = set(args.doc_type)
        previous_rows = [row for row in previous_rows if ((row.get("item") or {}).get("doc_type") in wanted)]
    items = [item_from_row(row) for row in previous_rows]

    stamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    out_dir = args.output_dir or Path("output/pdf") / f"revision_manual_recheck_{stamp}"
    checkpoint = out_dir / "recheck_results.jsonl"
    rows = run_quick(
        args.api_base,
        args.api_key,
        items,
        checkpoint_path=checkpoint,
        workers=max(1, args.workers),
    )
    by_id = {((row.get("item") or {}).get("id")): row for row in previous_rows}
    for row in rows:
        row["previous"] = by_id.get((row.get("item") or {}).get("id"), {})

    summary = {
        "generated_at": datetime.now().isoformat(timespec="seconds"),
        "source_results": str(args.results_json),
        "previous_revision_total": original_revision_total,
        "previous_revision_in_scope_total": len(previous_rows),
        "out_of_scope_revision_ignored": len(out_of_scope_rows),
        "recheck": summarize(rows),
    }
    out_dir.mkdir(parents=True, exist_ok=True)
    (out_dir / "revision_manual_recheck_summary.json").write_text(
        json.dumps(summary, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    (out_dir / "revision_manual_recheck_results.json").write_text(
        json.dumps(rows, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    write_csv(out_dir / "revision_manual_recheck.csv", rows)
    print(json.dumps(summary, ensure_ascii=False, indent=2))
    print(str(out_dir))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
