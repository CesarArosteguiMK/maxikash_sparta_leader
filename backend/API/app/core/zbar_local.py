# app/core/zbar_local.py
"""DLLs nativas de zbar solo dentro del proyecto (sin System32 ni PATH global).

pyzbar en Windows carga libzbar/libiconv desde el cwd o desde site-packages/pyzbar.
Copiamos desde backend/API/tools/zbar/bin hacia esa carpeta antes de cualquier uso.
"""
from __future__ import annotations

import os
import shutil
import sys
from pathlib import Path


def _api_root() -> Path:
    # Este archivo: app/core/zbar_local.py -> parents[2] = .../backend/API
    return Path(__file__).resolve().parents[2]


def _pyzbar_package_dir() -> Path | None:
    try:
        import pyzbar  # noqa: PLC0415 — después de definir rutas; evita import circular pesado
    except Exception:
        return None
    paths = getattr(pyzbar, "__path__", None)
    if paths:
        return Path(next(iter(paths))).resolve()
    return None


_REGISTERED_DLL_DIRS: set[str] = set()


def _register_dll_search_paths(api_root: Path, pyzbar_dir: Path | None) -> None:
    """Python 3.8+: permite que las DLL dependientes se resuelvan junto a pyzbar."""
    if sys.platform != "win32" or not hasattr(os, "add_dll_directory"):
        return
    for base in (pyzbar_dir, api_root / "tools" / "zbar" / "bin"):
        if base is None:
            continue
        if isinstance(base, Path) and base.is_dir():
            key = str(base.resolve())
            if key in _REGISTERED_DLL_DIRS:
                continue
            try:
                os.add_dll_directory(key)
                _REGISTERED_DLL_DIRS.add(key)
            except OSError:
                pass


def ensure_local_zbar_dlls() -> bool:
    """Copia DLLs desde tools/zbar/bin al paquete pyzbar (incl. dependencias MinGW).

    libzbar de MSYS2 enlaza contra libgcc, libpng, libjpeg, zlib, etc. Se copian
    todos los .dll del bin local para que el loader los resuelva junto a pyzbar.
    """
    if sys.platform != "win32":
        return True

    api_root = _api_root()
    src_bin = api_root / "tools" / "zbar" / "bin"
    src_dirs = [src_bin, api_root / "tools" / "zbar", api_root / "tools"]

    pyzbar_dir = _pyzbar_package_dir()
    if not pyzbar_dir or not pyzbar_dir.is_dir():
        return False

    # 1) Volcar bin completo (runtime MSYS2 + zbar + iconv).
    if src_bin.is_dir():
        for src in src_bin.glob("*.dll"):
            try:
                shutil.copy2(str(src), str(pyzbar_dir / src.name))
            except OSError:
                pass

    # 2) Nombres que espera pyzbar (pueden faltar si el bin no se genero aun).
    required = ("libzbar-64.dll", "libiconv.dll")
    optional = ("libcharset-1.dll",)
    ok = True
    for name in required + optional:
        dest = pyzbar_dir / name
        if dest.is_file():
            continue
        copied = False
        for sd in src_dirs:
            cand = sd / name
            if cand.is_file():
                try:
                    shutil.copy2(str(cand), str(dest))
                    copied = True
                except OSError:
                    pass
                break
        if not copied and name in required:
            ok = False

    _register_dll_search_paths(api_root, pyzbar_dir)

    return ok


def zbar_decode_import_ok() -> bool:
    """True si el modulo pyzbar carga (mismo import que usa la API)."""
    ensure_local_zbar_dlls()
    try:
        from pyzbar import pyzbar  # noqa: PLC0415
        return callable(getattr(pyzbar, "decode", None))
    except Exception:
        return False
