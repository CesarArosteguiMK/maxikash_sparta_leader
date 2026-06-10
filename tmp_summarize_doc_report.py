import json
from collections import Counter, defaultdict


with open("tmp_doc_api_flow_report.json", encoding="utf-16") as fh:
    r = json.load(fh)

print("docs", len(r["document_tests"]), "exp", len(r["expediente_tests"]))
print("status docs", Counter(str(x.get("status")) for x in r["document_tests"]))
print("status exp", Counter(str(x.get("status")) for x in r["expediente_tests"]))
print("kinds", Counter(x.get("kind") for x in r["document_tests"]))

times = defaultdict(list)
for x in r["document_tests"]:
    if "elapsed_ms" in x:
        times[x["kind"]].append(x["elapsed_ms"])
print("---TIMES DOC---")
for k, vals in sorted(times.items()):
    print(k, "n=", len(vals), "avg=", int(sum(vals) / len(vals)), "min=", min(vals), "max=", max(vals))

print("---DOC ISSUES---")
for x in r["document_tests"]:
    if "elapsed_ms" not in x:
        print("SKIP|", x.get("kind"), "|", x.get("file"), "|", x.get("skipped"))
        continue
    s = x.get("short", {})
    bad = (
        str(x.get("status")) != "200"
        or bool(s.get("timeout"))
        or bool(s.get("rechazado"))
        or bool(s.get("revision_manual"))
        or s.get("valido") is False
        or s.get("resultado") in ("RECHAZADO", "REVISION_MANUAL")
    )
    if bad:
        print("DOCBAD|", x["kind"], "|", x["file"], "|", x["elapsed_ms"], "|", x["status"], "|", s)

print("---EXP---")
for x in r["expediente_tests"]:
    print("EXP|", x["folder"], "|", x["elapsed_ms"], "|", x["status"], "|", x.get("short"))
