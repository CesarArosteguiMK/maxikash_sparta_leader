"""
Falla si el intérprete es Python "free-threading" (sin GIL) o ejecutable tipo *t.exe.
PyMuPDF y otros paquetes fallan así:
  AssertionError: Py_GIL_DISABLED=1 ... not supported ...
Instale Python 3.12 64-bit ESTANDAR desde python.org (no el instalador free-thread).
"""
import os
import sys
import sysconfig


def main() -> int:
    exe = getattr(sys, "_base_executable", sys.executable)
    bn = os.path.basename(exe).lower()

    d = sysconfig.get_config_var("Py_GIL_DISABLED")
    gil_build = d == 1 or str(d) == "1"

    gil_runtime_ok = getattr(sys.flags, "gil_enabled", True)
    # En builds normales gil_enabled existe en 3.13+ como True.

    exe_free = "t.exe" in bn and bn.startswith("python")

    reasons = []
    if gil_build:
        reasons.append("build con Py_GIL_DISABLED=1 (Python free-threading)")
    try:
        if gil_runtime_ok is False:
            reasons.append("tiempo de ejecucion gil_enabled=False")
    except Exception:
        pass
    if exe_free:
        reasons.append(f"ejecutable free-thread probable: {bn}")

    if reasons:
        lines = [
            "[ERROR] Este Python NO es apto para instalar PyMuPDF y el resto de requirements.txt.",
            "Motivos: " + "; ".join(reasons),
            "",
            "SOLUCION:",
            "  1) Instale Python 3.12 64-bit ESTANDAR (no la variante free-thread / sin GIL) desde:",
            "     https://www.python.org/downloads/windows/",
            "  2) En el instalador de Python NO active 'free threading' / 'python3.12t' si aparece.",
            "  3) Borre la carpeta venv vieja:  backend\\API\\venv",
            "  4) Ejecute de nuevo:  launcher\\instalar-agente.bat /VENV",
            "",
            'Referencia del fallo en pip: Py_GIL_DISABLED=1 y py_limited_api en PyMuPDF no son compatibles.',
        ]
        for line in lines:
            print(line, flush=True)
        return 2
    return 0


if __name__ == "__main__":
    sys.exit(main())
