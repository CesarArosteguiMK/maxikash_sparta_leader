#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Nota: el agente Gastos Cobranza (Node) ejecuta la copia en
backend/services/gastos-cobranza-agent/scripts/carga_cobranza_gc_verificacion_semana_desde_excel.py
(versión distinta). Este archivo es para uso manual desde la raíz del repo.

Carga id_credito desde Excel hacia __SPARTA_SECRET_REDACTED__.cobranza_gc_verificacion_semana.

El Excel debe tener una columna de encabezado reconocible como ID de crédito, por ejemplo:
  "ID CREDITO", "ID_CREDITO", "id_credito", "Id Credito"

El resto de columnas del Excel (nombre cliente, montos, etc.) se ignoran.
Semana operativa = martes a domingo (igual que reporte_cobranza.py). Por defecto inicio_semana
se calcula solo: fecha de hoy en CDMX → martes que abre ese periodo (no hace falta pasar el martes).

Si pasas --inicio-semana (opcional), es solo fecha de referencia y se normaliza al martes de ese
periodo, salvo --sin-normalizar-martes (solo con fecha manual).

Duplicados / "seguir donde quedó": misma semana = mismo inicio_semana (ese martes). Si vuelves a
correr con más IDs en el Excel, los que ya estaban en BD para esa semana se omiten y solo se
insertan los nuevos (filas nuevas en la tabla, mismo lote semanal).

Cómo se indica el Excel:
  - Ruta en línea de comandos: archivo.xlsx al final o --excel / -e.
  - Si no pasas ruta, se abre una ventana (Explorador) para elegir el .xlsx (tkinter).
  - En servidores sin escritorio usa --excel "ruta" o --no-gui (falla si falta ruta).

anio_iso / semana_iso: por defecto ISO (lunes-dom); si vuestra operación usa otra convención,
usar --anio-iso y --semana-iso.

Dependencias:
  pip install pandas openpyxl mysql-connector-python

Conexión (prioridad):
  1) Variables MEGA_HOST, MEGA_USER, MEGA_PASSWORD, MEGA_DATABASE, MEGA_PORT
  2) Con --mega-php-defaults: mismos valores que backend/core/DatabaseSegundometro.php
  3) MYSQL_* / DB_* pero forzando base con --database __SPARTA_SECRET_REDACTED__ si hace falta

Ejemplo (PowerShell; contraseña con caracteres raros entre comillas simples):
  $env:MEGA_PASSWORD = 'tu_clave'
  python scripts/carga_cobranza_gc_verificacion_semana_desde_excel.py ^
    --mega-php-defaults --excel "C:\\ruta\\archivo.xlsx"

  # Sin ruta: se abre ventana para elegir el Excel
  python ... --mega-php-defaults

  # Con mensaje explícito (el .xlsx puede ir al final sin --excel):
  python ... --mega-php-defaults archivo.xlsx -m "GASTOS_COBRANZA_APLICAR_25MAR2026"

  # Semana distinta a "hoy CDMX" (histórico): referencia de fecha → se usa su martes operativo
  python ... archivo.xlsx --inicio-semana 2026-03-15

  python ... --dry-run archivo.xlsx
  python ... archivo.xlsx --anio-iso 2026 --semana-iso 13

  # Solo con --inicio-semana manual: exigir que sea martes
  python ... archivo.xlsx --inicio-semana 2026-03-24 --sin-normalizar-martes
"""

from __future__ import annotations

import argparse
import os
import re
import sys
from datetime import date, datetime, timedelta
from pathlib import Path
from typing import Optional
from zoneinfo import ZoneInfo

import mysql.connector
import pandas as pd

_MEGA_PHP_DEFAULTS: dict = {
    "host": "",
    "port": 3306,
    "database": "",
    "user": "",
    "password": "",
}

CDMX = ZoneInfo("America/Mexico_City")
TABLE_NAME = "cobranza_gc_verificacion_semana"


def _pick_excel_path_gui() -> Optional[str]:
    """Abre diálogo nativo para elegir .xlsx; None si cancela o no hay GUI."""
    try:
        import tkinter as tk
        from tkinter import filedialog
    except Exception as e:
        print(f"No se pudo cargar tkinter ({e}). Usa --excel con la ruta.", file=sys.stderr)
        return None
    root = tk.Tk()
    root.withdraw()
    try:
        root.attributes("-topmost", True)
    except tk.TclError:
        pass
    try:
        path = filedialog.askopenfilename(
            title="Carga cobranza GC - selecciona el Excel",
            filetypes=[
                ("Libro Excel", "*.xlsx *.xlsm"),
                ("Todos los archivos", "*.*"),
            ],
        )
    except tk.TclError as e:
        print(f"No se pudo abrir el diálogo de archivos ({e}). Usa --excel.", file=sys.stderr)
        path = ""
    finally:
        try:
            root.destroy()
        except tk.TclError:
            pass
    return str(path).strip() if path else None


def _load_env_file(path: str, *, required: bool, override: bool = False) -> None:
    if not os.path.isfile(path):
        if required:
            print(f"No existe el archivo: {path}", file=sys.stderr)
            sys.exit(2)
        return
    try:
        with open(path, encoding="utf-8-sig") as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith("#"):
                    continue
                if "=" not in line:
                    continue
                key, _, val = line.partition("=")
                key, val = key.strip(), val.strip()
                if len(val) >= 2 and val[0] == val[-1] and val[0] in "\"'":
                    val = val[1:-1]
                if key and (override or key not in os.environ):
                    os.environ[key] = val
    except OSError as e:
        print(f"No se pudo leer {path}: {e}", file=sys.stderr)
        sys.exit(2)


def _env_first(*names: str) -> Optional[str]:
    for n in names:
        if n in os.environ:
            return os.environ[n]
    return None


def _load_default_env_file() -> None:
    env_path = Path(os.getenv("SPARTA_ENV_FILE") or r"C:\xampp\secure\sparta___SPARTA_SECRET_REDACTED__.env")
    if not env_path.is_file():
        return
    for raw_line in env_path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        key = key.strip()
        value = value.strip().strip('"').strip("'")
        if key and key not in os.environ:
            os.environ[key] = value


def _db_config(*, mega_php_defaults: bool) -> dict:
    if mega_php_defaults:
        _load_default_env_file()
        return {
            "host": _env_first("SEGUNDOMETRO_DB_HOST", "MEGA_HOST", "MYSQL_HOST", "DB_HOST", "DB_SERVIDOR") or "",
            "port": int(_env_first("SEGUNDOMETRO_DB_PORT", "MEGA_PORT", "MYSQL_PORT", "DB_PUERTO") or "3306"),
            "database": _env_first("SEGUNDOMETRO_DB_NAME", "MEGA_DATABASE", "MYSQL_DATABASE", "DB_NAME", "DB_ESQUEMA") or "",
            "user": _env_first("SEGUNDOMETRO_DB_USER", "MEGA_USER", "MYSQL_USER", "DB_USER", "DB_USUARIO") or "",
            "password": _env_first("SEGUNDOMETRO_DB_PASSWORD", "MEGA_PASSWORD", "MYSQL_PASSWORD", "DB_PASSWORD", "DB_PASS") or "",
        }

    host = _env_first("MEGA_HOST", "MYSQL_HOST", "DB_HOST", "DB_SERVIDOR")
    user = _env_first("MEGA_USER", "MYSQL_USER", "DB_USER", "DB_USUARIO")
    password = _env_first("MEGA_PASSWORD", "MYSQL_PASSWORD", "DB_PASSWORD", "DB_PASS")
    database = _env_first("MEGA_DATABASE", "MYSQL_DATABASE", "DB_NAME", "DB_ESQUEMA")
    port_s = _env_first("MEGA_PORT", "MYSQL_PORT", "DB_PUERTO") or "3306"

    if password is None:
        password = ""
    if not (host or "").strip() or not (user or "").strip() or not (database or "").strip():
        print(
            "Faltan MEGA_HOST/MEGA_USER/MEGA_DATABASE (o MYSQL_*/DB_*) "
            "o usa --mega-php-defaults.",
            file=sys.stderr,
        )
        sys.exit(2)
    try:
        port_i = int(str(port_s).strip())
    except ValueError:
        print("Puerto debe ser entero.", file=sys.stderr)
        sys.exit(2)

    return {
        "host": host.strip(),
        "user": user.strip(),
        "password": password,
        "database": database.strip(),
        "port": port_i,
    }


def _normalize_header(s: str) -> str:
    s = str(s).strip().lower()
    s = re.sub(r"\s+", "_", s)
    s = re.sub(r"[^a-z0-9_áéíóúñü]", "", s)
    return s


def _find_id_credito_column(df: pd.DataFrame) -> str:
    """Devuelve el nombre real de columna en el DataFrame."""
    candidates = {}
    for c in df.columns:
        n = _normalize_header(c)
        candidates[n] = c

    for key in ("id_credito", "idcredito", "id_crédito"):
        if key in candidates:
            return candidates[key]

    # Heurística: columna cuyo encabezado contiene id y credito
    for c in df.columns:
        n = _normalize_header(c)
        if "id" in n and "credito" in n.replace("crédito", "credito"):
            return c

    print(
        "No se encontró columna de id_credito. Encabezados: "
        + ", ".join(repr(c) for c in df.columns),
        file=sys.stderr,
    )
    sys.exit(2)


def _parse_id_credito(val) -> Optional[int]:
    if val is None or (isinstance(val, float) and pd.isna(val)):
        return None
    if isinstance(val, str):
        v = val.strip()
        if not v:
            return None
        v = re.sub(r"[^\d]", "", v)
        if not v:
            return None
        return int(v)
    try:
        return int(float(val))
    except (TypeError, ValueError):
        return None


def _iso_year_week(d: date) -> tuple[int, int]:
    y, w, _ = d.isocalendar()
    return int(y), int(w)


def inicio_semana_operativa_martes(d: date) -> date:
    """Martes que abre el periodo operativo mar–dom (misma fórmula que reporte_cobranza.py)."""
    return d - timedelta(days=(d.weekday() - 1) % 7)


_DIA_SEMANA_ES = (
    "lunes",
    "martes",
    "miércoles",
    "jueves",
    "viernes",
    "sábado",
    "domingo",
)


def _parse_date_arg(s: str) -> date:
    s = s.strip()
    for fmt in ("%Y-%m-%d", "%d/%m/%Y", "%d-%m-%Y"):
        try:
            return datetime.strptime(s, fmt).date()
        except ValueError:
            continue
    raise ValueError(f"Fecha no válida: {s!r} (use YYYY-MM-DD o DD/MM/YYYY)")


def _parse_registrado_arg(s: Optional[str]) -> datetime:
    if s:
        for fmt in ("%Y-%m-%d %H:%M:%S", "%Y-%m-%dT%H:%M:%S", "%d/%m/%Y %H:%M:%S"):
            try:
                dt = datetime.strptime(s.strip(), fmt)
                return dt.replace(tzinfo=CDMX)
            except ValueError:
                continue
        print(f"--registrado-cdmx no válido: {s!r}", file=sys.stderr)
        sys.exit(2)
    return datetime.now(CDMX)


def main() -> None:
    ap = argparse.ArgumentParser(
        description="Excel -> cobranza_gc_verificacion_semana",
        epilog="Excel: argumento final, --excel / -e, o sin ruta -> ventana para adjuntar .xlsx.",
    )
    ap.add_argument(
        "--excel",
        "-e",
        dest="excel_opt",
        default=None,
        metavar="RUTA",
        help="Ruta al .xlsx (alternativa: pasar el archivo como argumento posicional)",
    )
    ap.add_argument(
        "excel_positional",
        nargs="?",
        default=None,
        metavar="archivo.xlsx",
        help="Ruta al .xlsx (si no usas --excel)",
    )
    ap.add_argument("--sheet", default=0, help="Nombre o índice de hoja (default 0)")
    ap.add_argument(
        "--header-row",
        type=int,
        default=0,
        help="Fila 0-based donde están los títulos (default 0 = primera fila)",
    )
    ap.add_argument(
        "--inicio-semana",
        default=None,
        metavar="FECHA",
        help="Opcional. Fecha de referencia (YYYY-MM-DD o DD/MM/YYYY) -> martes del periodo. "
        "Si se omite, se usa hoy (America/Mexico_City) y el martes se calcula solo.",
    )
    ap.add_argument(
        "--sin-normalizar-martes",
        action="store_true",
        help="Exige que --inicio-semana sea ya un martes; si no, error y no inserta.",
    )
    ap.add_argument(
        "--mensaje",
        "-m",
        default=None,
        help="Texto del lote. Si se omite, se genera automático con fecha/martes y hora CDMX.",
    )
    ap.add_argument(
        "--anio-iso",
        type=int,
        default=None,
        help="Si no se indica, se calcula con ISO week de --inicio-semana",
    )
    ap.add_argument(
        "--semana-iso",
        type=int,
        default=None,
        help="Si no se indica, se calcula con ISO week de --inicio-semana",
    )
    ap.add_argument(
        "--registrado-cdmx",
        default=None,
        help="Marca de tiempo CDMX (default: ahora). Ej. 2026-03-27 15:10:11",
    )
    ap.add_argument("--s2-exitoso", type=int, default=1, choices=(0, 1))
    ap.add_argument("--incluido-reporte", type=int, default=1, choices=(0, 1))
    ap.add_argument("--dedupe", action="store_true", help="Quitar id_credito duplicados (default)")
    ap.add_argument("--no-dedupe", action="store_true", help="Mantener duplicados del Excel")
    ap.add_argument(
        "--ignore-duplicates",
        action="store_true",
        help="Además usar INSERT IGNORE (útil si existe UNIQUE en BD; por defecto ya se omiten duplicados vía SELECT)",
    )
    ap.add_argument("--dry-run", action="store_true")
    ap.add_argument(
        "--mega-php-defaults",
        action="store_true",
        help="Usar host/usuario/clave/bd de DatabaseSegundometro.php",
    )
    ap.add_argument("--env-file", default=None, help="Archivo KEY=valor UTF-8")
    ap.add_argument(
        "--env-file-overrides",
        action="store_true",
        help="Con --env-file, pisar variables ya definidas",
    )
    ap.add_argument("--test-db", action="store_true", help="Solo SELECT 1 y salir")
    ap.add_argument(
        "--no-gui",
        action="store_true",
        help="No abrir ventana para elegir Excel; exige ruta con --excel o argumento posicional",
    )
    args = ap.parse_args()

    if args.env_file:
        _load_env_file(
            args.env_file, required=True, override=bool(args.env_file_overrides)
        )

    cfg = _db_config(mega_php_defaults=bool(args.mega_php_defaults))

    if args.sin_normalizar_martes and not args.inicio_semana:
        print(
            "--sin-normalizar-martes solo tiene sentido con --inicio-semana explícita.",
            file=sys.stderr,
        )
        sys.exit(2)

    if args.inicio_semana:
        try:
            inicio_raw = _parse_date_arg(args.inicio_semana)
        except ValueError as e:
            print(str(e), file=sys.stderr)
            sys.exit(2)
        modo_fecha = "manual"
    else:
        inicio_raw = datetime.now(CDMX).date()
        modo_fecha = "automático (hoy CDMX)"

    inicio_martes = inicio_semana_operativa_martes(inicio_raw)
    if args.sin_normalizar_martes:
        if inicio_raw.weekday() != 1:
            print(
                f"--sin-normalizar-martes: la fecha debe ser martes. "
                f"Recibido {inicio_raw} ({_DIA_SEMANA_ES[inicio_raw.weekday()]})",
                file=sys.stderr,
            )
            sys.exit(2)
        inicio = inicio_raw
        print(
            f"inicio_semana: {inicio} ({_DIA_SEMANA_ES[inicio.weekday()]}) "
            f"[sin normalizar; ya es martes]  (fecha {modo_fecha})"
        )
    else:
        inicio = inicio_martes
        if modo_fecha == "automático (hoy CDMX)":
            print(
                f"inicio_semana automático: hoy CDMX {inicio_raw} "
                f"({_DIA_SEMANA_ES[inicio_raw.weekday()]}) -> martes del periodo {inicio} "
                f"({_DIA_SEMANA_ES[inicio.weekday()]})"
            )
        elif inicio_raw != inicio:
            print(
                f"Fecha indicada: {inicio_raw} ({_DIA_SEMANA_ES[inicio_raw.weekday()]}) "
                f"-> inicio_semana operativo (martes): {inicio} ({_DIA_SEMANA_ES[inicio.weekday()]})"
            )
        else:
            print(
                f"inicio_semana (martes del periodo): {inicio} ({_DIA_SEMANA_ES[inicio.weekday()]})"
            )

    if args.mensaje is None:
        tag = datetime.now(CDMX).strftime("%Y%m%d_%H%M%S")
        args.mensaje = f"COBRANZA_GC_EXCEL_AUTO_{inicio.isoformat()}_{tag}"
        print(f"mensaje (automático): {args.mensaje!r}")

    if args.anio_iso is not None and args.semana_iso is not None:
        anio_iso, semana_iso = args.anio_iso, args.semana_iso
    elif args.anio_iso is not None or args.semana_iso is not None:
        print("Use ambos --anio-iso y --semana-iso o ninguno.", file=sys.stderr)
        sys.exit(2)
    else:
        anio_iso, semana_iso = _iso_year_week(inicio)

    registrado = _parse_registrado_arg(args.registrado_cdmx)
    registrado_naive = registrado.replace(tzinfo=None)

    conn_kw = {
        "host": cfg["host"],
        "port": cfg["port"],
        "user": cfg["user"],
        "password": cfg["password"],
        "database": cfg["database"],
        "charset": "utf8mb4",
        "use_unicode": True,
    }

    try:
        conn = mysql.connector.connect(**conn_kw)
    except mysql.connector.Error as e:
        print(f"Error de conexión MySQL: {e}", file=sys.stderr)
        sys.exit(1)

    try:
        cur = conn.cursor()
        if args.test_db:
            cur.execute("SELECT 1 AS ok")
            print(cur.fetchone())
            return

        excel_arg = args.excel_opt or args.excel_positional
        if not excel_arg or not str(excel_arg).strip():
            if args.no_gui:
                print(
                    "Falta el Excel: pasa archivo.xlsx, --excel o quita --no-gui para elegir en ventana.",
                    file=sys.stderr,
                )
                sys.exit(2)
            print("No indicaste Excel. Abriendo ventana para elegir el archivo…", flush=True)
            picked = _pick_excel_path_gui()
            if not picked:
                print("No se seleccionó ningún archivo.", file=sys.stderr)
                sys.exit(2)
            excel_arg = picked
            print(f"Excel seleccionado: {excel_arg}")

        excel_path = Path(str(excel_arg).strip())
        if not excel_path.is_file():
            print(f"No existe el Excel: {excel_path}", file=sys.stderr)
            sys.exit(2)

        df = pd.read_excel(
            excel_path,
            sheet_name=args.sheet,
            header=args.header_row,
            engine="openpyxl",
        )
        col = _find_id_credito_column(df)
        ids: list[int] = []
        for raw in df[col].tolist():
            i = _parse_id_credito(raw)
            if i is not None and i > 0:
                ids.append(i)

        dedupe = not args.no_dedupe
        if dedupe:
            seen = set()
            unique: list[int] = []
            for i in ids:
                if i not in seen:
                    seen.add(i)
                    unique.append(i)
            ids = unique

        # Ya existen en BD para este mismo inicio_semana (misma semana operativa) → omitir
        cur.execute(
            f"SELECT DISTINCT `id_credito` FROM `{TABLE_NAME}` WHERE `inicio_semana` = %s",
            (inicio,),
        )
        ya_existentes = {int(r[0]) for r in cur.fetchall() if r[0] is not None}
        ids_a_insertar = [i for i in ids if i not in ya_existentes]
        omitidos_bd = len(ids) - len(ids_a_insertar)

        print(
            f"IDs válidos en Excel: {len(ids)}  |  ya en BD (misma inicio_semana): {omitidos_bd}  "
            f"|  a insertar: {len(ids_a_insertar)}"
        )
        print(
            f"BD={cfg['database']!r}  tabla={TABLE_NAME}  inicio_semana={inicio}  "
            f"anio_iso={anio_iso}  semana_iso={semana_iso}  registrado_en_cdmx={registrado_naive}"
        )

        if args.dry_run:
            print("(dry-run) Primeros 15 a insertar:", ids_a_insertar[:15])
            if omitidos_bd:
                muestra = sorted(ya_existentes & set(ids))[:15]
                print(f"(dry-run) Ejemplo omitidos (ya en BD): {muestra}")
            return

        if not ids_a_insertar:
            print("Nada que insertar (todos ya existían para esta inicio_semana).")
            return

        sql = (
            f"INSERT {'IGNORE ' if args.ignore_duplicates else ''}INTO `{TABLE_NAME}` "
            "(id_credito, inicio_semana, anio_iso, semana_iso, registrado_en_cdmx, "
            "s2_exitoso, incluido_reporte, mensaje) "
            "VALUES (%s, %s, %s, %s, %s, %s, %s, %s)"
        )
        batch: list[tuple] = [
            (
                i,
                inicio.isoformat(),
                anio_iso,
                semana_iso,
                registrado_naive,
                args.s2_exitoso,
                args.incluido_reporte,
                args.mensaje,
            )
            for i in ids_a_insertar
        ]

        chunk = 500
        total = 0
        for off in range(0, len(batch), chunk):
            part = batch[off : off + chunk]
            cur.executemany(sql, part)
            conn.commit()
            total += cur.rowcount if cur.rowcount is not None else len(part)
        print(f"Insertadas (filas afectadas reportadas): {total}")
        if omitidos_bd:
            print(f"Omitidos (mismo id_credito + misma inicio_semana): {omitidos_bd}")

    finally:
        conn.close()


if __name__ == "__main__":
    main()
