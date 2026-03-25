# -*- coding: utf-8 -*-
path = r"c:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\backend\controllers\Sabueso.php"
with open(path, encoding="utf-8") as f:
    lines = f.readlines()
new_line = "        require __DIR__ . '/SabuesoPaneladminScriptChunk.php';\n"
# Remove former lines 138-2189 (1-based) -> keep 0..136, insert require, then from 2189
out = lines[:137] + [new_line] + lines[2189:]
with open(path, "w", encoding="utf-8") as f:
    f.writelines(out)
print("ok", len(lines), "->", len(out))
