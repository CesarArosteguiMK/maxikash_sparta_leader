#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Carga un CSV a __SPARTA_SECRET_REDACTED__.tbl_segundometro_histo.

Flujo:
  1) Lee el esquema real de la tabla.
  2) Mapea columnas del CSV por nombre, sin depender del orden.
  3) Ignora columnas del CSV que no existan en la tabla.
  4) En modo --execute, dentro de una transaccion:
       DELETE FROM tbl_segundometro_histo WHERE SEMANA = <semana>
       INSERT masivo de las filas del CSV para esa misma semana

Ejemplos:
  # Validar sin borrar ni insertar
  C:\\xampp\\htdocs\\sparta___SPARTA_SECRET_REDACTED__\\backend\\API\\tools\\PythonPortable\\python.exe scripts\\carga_segundometro_histo_csv.py ^
    --csv C:\\Users\\amigo_j9s4pcx\\Downloads\\w20.csv --semana "Semana 20-2026" --mega-php-defaults

  # Ejecutar la carga real
  C:\\xampp\\htdocs\\sparta___SPARTA_SECRET_REDACTED__\\backend\\API\\tools\\PythonPortable\\python.exe scripts\\carga_segundometro_histo_csv.py ^
    --csv C:\\Users\\amigo_j9s4pcx\\Downloads\\w20.csv --semana "Semana 20-2026" --mega-php-defaults --execute
"""

from __future__ import annotations

import argparse
import csv
import os
import re
import sys
from collections import Counter
from datetime import datetime
from decimal import Decimal, InvalidOperation
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[1]
PYDEPS = REPO_ROOT / "backend" / "services" / "gastos-cobranza-agent" / "pydeps"
if PYDEPS.is_dir():
    sys.path.insert(0, str(PYDEPS))

try:
    import mysql.connector
    from mysql.connector import errorcode
except Exception as exc:  # pragma: no cover - mensaje de ayuda para CLI
    print(
        "No se pudo importar mysql.connector. Usa el PythonPortable del proyecto "
        "o instala mysql-connector-python.",
        file=sys.stderr,
    )
    raise


TABLE_NAME = "tbl_segundometro_histo"
DEFAULT_DB = {
    "host": "",
    "port": 3306,
    "database": "",
    "user": "",
    "password": "",
}
EMPTY_VALUES = {"", "-", "NULL", "null", "None", "none"}
MONEY_RE = re.compile(r"[\s$,]")
INT_TYPES = ("int", "bigint", "smallint", "mediumint", "tinyint")
DECIMAL_TYPES = ("decimal", "double", "float")


def env_first(*names: str) -> str | None:
    for name in names:
        value = os.getenv(name)
        if value is not None and value != "":
            return value
    return None


def load_env_file(path: str = r"C:\xampp\secure\sparta___SPARTA_SECRET_REDACTED__.env") -> None:
    env_path = Path(os.getenv("SPARTA_ENV_FILE") or path)
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


def db_config(use_defaults: bool) -> dict[str, Any]:
    if use_defaults:
        load_env_file()
        return {
            "host": env_first("SEGUNDOMETRO_DB_HOST", "MEGA_HOST", "MYSQL_HOST", "DB_HOST", "DB_SERVIDOR") or "",
            "port": int(env_first("SEGUNDOMETRO_DB_PORT", "MEGA_PORT", "MYSQL_PORT", "DB_PUERTO") or "3306"),
            "database": env_first("SEGUNDOMETRO_DB_NAME", "MEGA_DATABASE", "MYSQL_DATABASE", "DB_NAME", "DB_ESQUEMA") or "",
            "user": env_first("SEGUNDOMETRO_DB_USER", "MEGA_USER", "MYSQL_USER", "DB_USER", "DB_USUARIO") or "",
            "password": env_first("SEGUNDOMETRO_DB_PASSWORD", "MEGA_PASSWORD", "MYSQL_PASSWORD", "DB_PASSWORD", "DB_PASS") or "",
        }

    host = env_first("MEGA_HOST", "MYSQL_HOST", "DB_HOST", "DB_SERVIDOR")
    user = env_first("MEGA_USER", "MYSQL_USER", "DB_USER", "DB_USUARIO")
    password = env_first("MEGA_PASSWORD", "MYSQL_PASSWORD", "DB_PASSWORD", "DB_PASS") or ""
    database = env_first("MEGA_DATABASE", "MYSQL_DATABASE", "DB_NAME", "DB_ESQUEMA")
    port_s = env_first("MEGA_PORT", "MYSQL_PORT", "DB_PUERTO") or "3306"

    missing = []
    if not host:
        missing.append("MEGA_HOST/MYSQL_HOST/DB_HOST")
    if not user:
        missing.append("MEGA_USER/MYSQL_USER/DB_USER")
    if not database:
        missing.append("MEGA_DATABASE/MYSQL_DATABASE/DB_NAME")
    if missing:
        raise SystemExit(
            "Faltan variables de conexion: "
            + ", ".join(missing)
            + ". Tambien puedes usar --mega-php-defaults."
        )

    return {
        "host": host,
        "port": int(port_s),
        "database": database,
        "user": user,
        "password": password,
    }


def quote_identifier(name: str) -> str:
    return "`" + name.replace("`", "``") + "`"


def table_schema(cnx: mysql.connector.MySQLConnection) -> list[dict[str, Any]]:
    cur = cnx.cursor(dictionary=True)
    cur.execute(f"SHOW COLUMNS FROM {quote_identifier(TABLE_NAME)}")
    rows = list(cur.fetchall())
    cur.close()
    if not rows:
        raise RuntimeError(f"No se pudo leer esquema de {TABLE_NAME}.")
    return rows


def normalize_header(name: str) -> str:
    return name.strip().lstrip("\ufeff").lower()


def build_header_map(csv_headers: list[str], schema: list[dict[str, Any]], include_pk: bool) -> tuple[list[str], dict[str, str], list[str]]:
    db_by_norm = {normalize_header(str(row["Field"])): str(row["Field"]) for row in schema}
    pk_name = "id_segundometro_histo"
    mapped: dict[str, str] = {}
    ignored: list[str] = []

    for header in csv_headers:
        clean = header.strip().lstrip("\ufeff")
        db_col = db_by_norm.get(normalize_header(clean))
        if not db_col:
            ignored.append(clean)
            continue
        if normalize_header(db_col) == pk_name and not include_pk:
            ignored.append(clean)
            continue
        mapped[clean] = db_col

    insert_cols = [str(row["Field"]) for row in schema if str(row["Field"]) in set(mapped.values())]
    return insert_cols, mapped, ignored


def parse_decimal_like(value: str) -> str | None:
    cleaned = MONEY_RE.sub("", value).replace(",", "")
    if cleaned in EMPTY_VALUES:
        return None
    try:
        return format(Decimal(cleaned), "f")
    except InvalidOperation:
        return value


def normalize_datetime(value: str) -> str | None:
    value = value.strip()
    if value in EMPTY_VALUES:
        return None
    formats = (
        "%Y-%m-%d %H:%M:%S",
        "%Y-%m-%d %H:%M",
        "%d/%m/%Y %H:%M:%S",
        "%d/%m/%Y %H:%M",
        "%d/%m/%Y",
        "%Y-%m-%d",
    )
    for fmt in formats:
        try:
            return datetime.strptime(value, fmt).strftime("%Y-%m-%d %H:%M:%S")
        except ValueError:
            pass
    return value


def clean_value(raw: Any, mysql_type: str) -> Any:
    if raw is None:
        return None
    value = str(raw).strip()
    if value in EMPTY_VALUES:
        return None

    type_l = mysql_type.lower()
    if "datetime" in type_l or "timestamp" in type_l:
        return normalize_datetime(value)

    if type_l.startswith(INT_TYPES):
        normalized = parse_decimal_like(value)
        if normalized is None:
            return None
        try:
            return int(Decimal(str(normalized)))
        except (InvalidOperation, ValueError):
            return normalized

    if type_l.startswith(DECIMAL_TYPES):
        return parse_decimal_like(value)

    if "e+" in value.lower() or "e-" in value.lower():
        try:
            return format(Decimal(value), "f").split(".")[0]
        except InvalidOperation:
            return value

    return value


def csv_summary(path: Path, semana: str) -> tuple[int, Counter[str]]:
    total = 0
    weeks: Counter[str] = Counter()
    with path.open("r", encoding="utf-8-sig", newline="") as fh:
        reader = csv.DictReader(fh)
        if not reader.fieldnames:
            raise RuntimeError("El CSV no tiene encabezados.")
        for row in reader:
            total += 1
            weeks[(row.get("SEMANA") or "").strip()] += 1
    if weeks.get(semana, 0) == 0:
        raise RuntimeError(f"El CSV no contiene filas con SEMANA = {semana!r}.")
    return total, weeks


def rows_for_insert(
    path: Path,
    insert_cols: list[str],
    csv_to_db: dict[str, str],
    type_by_col: dict[str, str],
    semana: str,
):
    db_to_csv = {db: csv_name for csv_name, db in csv_to_db.items()}
    with path.open("r", encoding="utf-8-sig", newline="") as fh:
        reader = csv.DictReader(fh)
        for row in reader:
            if (row.get("SEMANA") or "").strip() != semana:
                continue
            yield tuple(clean_value(row.get(db_to_csv[col]), type_by_col[col]) for col in insert_cols)


def count_existing(cnx: mysql.connector.MySQLConnection, semana: str) -> int:
    cur = cnx.cursor()
    cur.execute(f"SELECT COUNT(*) FROM {quote_identifier(TABLE_NAME)} WHERE `SEMANA` = %s", (semana,))
    count = int(cur.fetchone()[0])
    cur.close()
    return count


def main() -> int:
    parser = argparse.ArgumentParser(description="Carga CSV a tbl_segundometro_histo por semana.")
    parser.add_argument("--csv", required=True, type=Path, help="Ruta del CSV a cargar.")
    parser.add_argument("--semana", default="Semana 20-2026", help="Valor exacto de SEMANA a reemplazar.")
    parser.add_argument("--execute", action="store_true", help="Ejecuta DELETE + INSERT. Sin esto solo valida.")
    parser.add_argument("--with-pk-id", action="store_true", help="Inserta id_segundometro_histo desde el CSV.")
    parser.add_argument("--mega-php-defaults", action="store_true", help="Usa la conexion de DatabaseSegundometro.php.")
    parser.add_argument("--batch-size", type=int, default=1000, help="Filas por lote de INSERT.")
    args = parser.parse_args()

    csv_path = args.csv.expanduser().resolve()
    if not csv_path.is_file():
        raise SystemExit(f"No existe el CSV: {csv_path}")
    if args.batch_size < 1:
        raise SystemExit("--batch-size debe ser mayor a cero.")

    total_csv, weeks = csv_summary(csv_path, args.semana)

    cfg = db_config(args.mega_php_defaults)
    cnx = mysql.connector.connect(
        host=cfg["host"],
        port=cfg["port"],
        user=cfg["user"],
        password=cfg["password"],
        database=cfg["database"],
        charset="utf8mb4",
        use_unicode=True,
        autocommit=False,
        connection_timeout=10,
    )

    try:
        schema = table_schema(cnx)
        type_by_col = {str(row["Field"]): str(row["Type"]) for row in schema}
        with csv_path.open("r", encoding="utf-8-sig", newline="") as fh:
            reader = csv.DictReader(fh)
            csv_headers = list(reader.fieldnames or [])
        insert_cols, csv_to_db, ignored = build_header_map(csv_headers, schema, args.with_pk_id)
        missing_required = {"KT", "SEMANA"} - set(insert_cols)
        if missing_required:
            raise RuntimeError("Faltan columnas indispensables en el CSV/esquema: " + ", ".join(sorted(missing_required)))

        existing = count_existing(cnx, args.semana)
        rows_target = weeks[args.semana]
        other_weeks = sum(v for k, v in weeks.items() if k != args.semana)

        print(f"CSV: {csv_path}")
        print(f"Filas CSV totales: {total_csv}")
        print(f"Filas CSV para {args.semana!r}: {rows_target}")
        print(f"Filas CSV de otras semanas ignoradas: {other_weeks}")
        print(f"Filas existentes en BD para {args.semana!r}: {existing}")
        print(f"Columnas a insertar: {len(insert_cols)}")
        if ignored:
            print("Columnas ignoradas del CSV: " + ", ".join(ignored[:20]) + ("..." if len(ignored) > 20 else ""))

        if not args.execute:
            print("Validacion OK. No se borro ni inserto nada porque falta --execute.")
            cnx.rollback()
            return 0

        placeholders = ",".join(["%s"] * len(insert_cols))
        cols_sql = ",".join(quote_identifier(col) for col in insert_cols)
        insert_sql = f"INSERT INTO {quote_identifier(TABLE_NAME)} ({cols_sql}) VALUES ({placeholders})"

        cur = cnx.cursor()
        cur.execute(f"DELETE FROM {quote_identifier(TABLE_NAME)} WHERE `SEMANA` = %s", (args.semana,))
        deleted = cur.rowcount

        inserted = 0
        batch: list[tuple[Any, ...]] = []
        for values in rows_for_insert(csv_path, insert_cols, csv_to_db, type_by_col, args.semana):
            batch.append(values)
            if len(batch) >= args.batch_size:
                cur.executemany(insert_sql, batch)
                inserted += cur.rowcount
                batch.clear()
                print(f"Insertadas hasta ahora: {inserted}", flush=True)
        if batch:
            cur.executemany(insert_sql, batch)
            inserted += cur.rowcount

        cnx.commit()
        final_count = count_existing(cnx, args.semana)
        cur.close()

        print(f"DELETE filas afectadas: {deleted}")
        print(f"INSERT filas afectadas: {inserted}")
        print(f"Conteo final BD para {args.semana!r}: {final_count}")
        if final_count != rows_target:
            print("ADVERTENCIA: el conteo final no coincide con las filas objetivo del CSV.", file=sys.stderr)
            return 3
        return 0
    except mysql.connector.Error as err:
        cnx.rollback()
        if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
            print("Acceso denegado a MySQL. Revisa usuario/password.", file=sys.stderr)
        else:
            print(f"Error MySQL: {err}", file=sys.stderr)
        return 1
    except Exception as exc:
        cnx.rollback()
        print(f"Error: {exc}", file=sys.stderr)
        return 1
    finally:
        cnx.close()


if __name__ == "__main__":
    raise SystemExit(main())
