#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Carga masiva a asigna_creditos_despacho (BD __SPARTA_SECRET_REDACTED__) desde Excel (Id_credito + id_despacho).

En PHP, Database.php usa getenv('DB_HOST') etc. primero; los literales solo aplican si no hay env.
En Windows suele haber DB_* de otros proyectos: pisan esos literales. Para ignorar el sistema y usar
exactamente los mismos literales que al final de las líneas 14-18 de Database.php, usa --database-php-solo.

Dependencias:
  pip install pandas openpyxl mysql-connector-python

Variables de entorno (cualquiera de los dos esquemas):
  MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE, MYSQL_PORT
  o las mismas que backend/core/Database.php:
  DB_HOST o DB_SERVIDOR, DB_USER o DB_USUARIO, DB_PASSWORD o DB_PASS,
  DB_NAME o DB_ESQUEMA, DB_PUERTO (default 3306)

Uso ejemplo:
  set MYSQL_HOST=localhost
  set MYSQL_USER=...
  set MYSQL_PASSWORD=...
  set MYSQL_DATABASE=__SPARTA_SECRET_REDACTED__
  python scripts/carga_asigna_credito_despacho_desde_excel.py --excel "C:\\ruta\\archivo.xlsx"
  (tabla por defecto: asigna_creditos_despacho)

Opciones útiles:
  --sheet BD_Despachos
  --header-row 1          fila 0-based de pandas donde están los nombres de columna (default 1)
  --dry-run               solo muestra cuántas filas se insertarían
  --test-db               solo prueba usuario/clave/host (SELECT 1), no inserta ni lee Excel
  --ignore-duplicates     INSERT IGNORE (si hay UNIQUE y quieres omitir duplicados sin error)

Contraseñas con caracteres especiales en PowerShell: usa comillas simples, p. ej.
  $env:DB_PASSWORD = '__SPARTA_PASSWORD_REDACTED__'
En comillas dobles el backtick (`) es carácter de escape y la clave queda mal.
  --env-file RUTA         Carga KEY=valor (UTF-8) sin pisar variables ya definidas en la sesión

Si en PowerShell ves el prompt >> (modo continuación), pulsa Ctrl+C; define $env:... con
prompt PS (ruta normal) y en otra línea ejecuta python.
"""

from __future__ import annotations

import argparse
import os
import sys
from pathlib import Path
from typing import Iterable, List, Tuple

import mysql.connector
import pandas as pd
from mysql.connector import errorcode

# Debe coincidir con backend/core/Database.php (getenv con ?:)
_PHP_DB_DEFAULTS: dict = {
    "host": "__SPARTA_HOST_REDACTED__",
    "port": 3306,
    "database": "__SPARTA_SECRET_REDACTED__",
    "user": "__SPARTA_SECRET_REDACTED__",
    "password": "__SPARTA_PASSWORD_REDACTED__",
}


def _load_env_file(path: str, *, required: bool, override: bool = False) -> None:
    """Carga KEY=valor al entorno. Si override=False, no pisa claves ya definidas."""
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
                key = key.strip()
                val = val.strip()
                if len(val) >= 2 and val[0] == val[-1] and val[0] in "\"'":
                    val = val[1:-1]
                if key and (override or key not in os.environ):
                    os.environ[key] = val
    except OSError as e:
        print(f"No se pudo leer {path}: {e}", file=sys.stderr)
        sys.exit(2)


def _env_first(*names: str) -> str | None:
    """Primer nombre definido en el entorno (incluye cadena vacía si la clave existe)."""
    for n in names:
        if n in os.environ:
            return os.environ[n]
    return None


def _print_config_summary(cfg: dict) -> None:
    pw = cfg.get("password") or ""
    print(
        "Config efectiva (contraseña oculta):\n"
        f"  host={cfg.get('host')!r}  port={cfg.get('port')!r}\n"
        f"  database={cfg.get('database')!r}  user={cfg.get('user')!r}\n"
        f"  longitud contraseña={len(pw)}"
        + ("  (VACÍA — suele provocar error 1045)" if len(pw) == 0 else "")
        + "\n"
        f"  DB_HOST definida={'DB_HOST' in os.environ}  "
        f"DB_PASSWORD definida={'DB_PASSWORD' in os.environ}  "
        f"DB_PASS definida={'DB_PASS' in os.environ}"
    )
    if str(cfg.get("database", "")).strip().lower() != "__SPARTA_SECRET_REDACTED__":
        print(
            "  AVISO: esta BD no es __SPARTA_SECRET_REDACTED__. Suele venir de variables DB_* de Windows "
            "(otro proyecto). Para esta carga redefine $env:... en PowerShell o usa "
            "--env-file RUTA --env-file-overrides."
        )


def _db_config_from_env(
    *, use_php_fallback: bool = False, database_php_solo: bool = False
) -> dict:
    if database_php_solo:
        return {
            "host": str(_PHP_DB_DEFAULTS["host"]).strip(),
            "user": str(_PHP_DB_DEFAULTS["user"]).strip(),
            "password": str(_PHP_DB_DEFAULTS["password"]),
            "database": str(_PHP_DB_DEFAULTS["database"]).strip(),
            "port": int(_PHP_DB_DEFAULTS["port"]),
        }

    host = _env_first("MYSQL_HOST", "DB_HOST", "DB_SERVIDOR")
    user = _env_first("MYSQL_USER", "DB_USER", "DB_USUARIO")
    password = _env_first("MYSQL_PASSWORD", "DB_PASSWORD", "DB_PASS")
    database = _env_first("MYSQL_DATABASE", "DB_NAME", "DB_ESQUEMA")
    port_s = _env_first("MYSQL_PORT", "DB_PUERTO") or "3306"

    if password is None:
        password = ""

    if use_php_fallback:
        if not (host or "").strip():
            host = _PHP_DB_DEFAULTS["host"]
        if not (user or "").strip():
            user = _PHP_DB_DEFAULTS["user"]
        if not (database or "").strip():
            database = _PHP_DB_DEFAULTS["database"]
        if not str(port_s).strip():
            port_s = str(_PHP_DB_DEFAULTS["port"])
        if password == "":
            password = _PHP_DB_DEFAULTS["password"]

    missing_labels: list[str] = []
    if not (host or "").strip():
        missing_labels.append("MYSQL_HOST o DB_HOST o DB_SERVIDOR")
    if not (user or "").strip():
        missing_labels.append("MYSQL_USER o DB_USER o DB_USUARIO")
    if not (database or "").strip():
        missing_labels.append("MYSQL_DATABASE o DB_NAME o DB_ESQUEMA")
    if missing_labels:
        root = Path(__file__).resolve().parent.parent
        print(
            "Faltan datos de conexión. Define en PowerShell (prompt que empieza por PS, no el modo >>):\n  "
            + "\n  ".join(missing_labels)
            + "\n\nO crea un archivo .env en la raíz del proyecto con DB_HOST, DB_USER, DB_PASSWORD, DB_NAME\n"
            + f"  (raíz esperada: {root})\n"
            + "  y ejecuta: python ... --env-file ruta/al/.env\n"
            + "Si ves modo >>, Ctrl+C y vuelve a pegar: primero $env:..., luego python en una línea nueva.",
            file=sys.stderr,
        )
        sys.exit(2)

    try:
        port_i = int(str(port_s).strip())
    except ValueError:
        print("MYSQL_PORT / DB_PUERTO debe ser un entero.", file=sys.stderr)
        sys.exit(2)

    return {
        "host": host.strip(),
        "user": user.strip(),
        "password": password,
        "database": database.strip(),
        "port": port_i,
    }


def _normalize_columns(df: pd.DataFrame) -> pd.DataFrame:
    df = df.copy()
    df.columns = [str(c).strip() for c in df.columns]
    lower_map = {c.lower().replace(" ", "_"): c for c in df.columns}
    # Aceptar variantes comunes
    id_cred_col = None
    for key in ("id_credito", "idcredito", "id_crédito"):
        if key in lower_map:
            id_cred_col = lower_map[key]
            break
    desp_col = None
    for key in ("id_despacho", "iddespacho"):
        if key in lower_map:
            desp_col = lower_map[key]
            break
    if not id_cred_col or not desp_col:
        print(
            "No se encontraron columnas Id_credito / id_despacho.\n"
            f"Columnas leídas: {list(df.columns)}",
            file=sys.stderr,
        )
        sys.exit(3)
    out = df[[desp_col, id_cred_col]].copy()
    out.columns = ["id_despacho", "id_credito"]
    return out


def _prepare_rows(df: pd.DataFrame) -> pd.DataFrame:
    df = df.dropna(subset=["id_credito", "id_despacho"])
    df["id_credito"] = pd.to_numeric(df["id_credito"], errors="coerce")
    df["id_despacho"] = pd.to_numeric(df["id_despacho"], errors="coerce")
    df = df.dropna(subset=["id_credito", "id_despacho"])
    df["id_credito"] = df["id_credito"].astype("int64")
    df["id_despacho"] = df["id_despacho"].astype("int64")
    df = df.drop_duplicates(subset=["id_despacho", "id_credito"])
    return df


def _chunks(rows: List[Tuple], size: int) -> Iterable[List[Tuple]]:
    for i in range(0, len(rows), size):
        yield rows[i : i + size]


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Inserta filas en asigna_creditos_despacho (MySQL, típicamente BD __SPARTA_SECRET_REDACTED__)."
    )
    parser.add_argument(
        "--excel",
        default=None,
        help="Ruta al archivo .xlsx (obligatorio salvo --test-db)",
    )
    parser.add_argument(
        "--sheet",
        default="BD_Despachos",
        help='Nombre de la hoja (default: "BD_Despachos")',
    )
    parser.add_argument(
        "--header-row",
        type=int,
        default=1,
        help="Fila (0-based) que pandas usa como encabezados (default: 1)",
    )
    parser.add_argument(
        "--table",
        default="asigna_creditos_despacho",
        help="Nombre de tabla (default: asigna_creditos_despacho)",
    )
    parser.add_argument(
        "--batch-size",
        type=int,
        default=500,
        help="Filas por executemany (default: 500)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="No conecta a BD; solo cuenta filas válidas",
    )
    parser.add_argument(
        "--test-db",
        action="store_true",
        help="Solo conecta y ejecuta SELECT 1 (útil para validar credenciales)",
    )
    parser.add_argument(
        "--show-config",
        action="store_true",
        help="Muestra host, usuario, BD y longitud de contraseña (no la imprime)",
    )
    parser.add_argument(
        "--use-php-fallback",
        action="store_true",
        help="Si falta host/usuario/clave/BD, usa los mismos valores por defecto que Database.php",
    )
    parser.add_argument(
        "--database-php-solo",
        action="store_true",
        help="Ignora DB_* y MYSQL_* del sistema; conecta solo con los literales de Database.php (líneas 14-18)",
    )
    parser.add_argument(
        "--ignore-duplicates",
        action="store_true",
        help="Usa INSERT IGNORE (requiere índice UNIQUE en los duplicados)",
    )
    parser.add_argument(
        "--env-file",
        default=None,
        metavar="RUTA",
        help="Archivo tipo .env (DB_HOST=...); por defecto no pisa variables ya definidas",
    )
    parser.add_argument(
        "--env-file-overrides",
        action="store_true",
        help="Al cargar --env-file o .env del proyecto, sobrescribe DB_* aunque existan en Windows",
    )
    args = parser.parse_args()

    repo_root = Path(__file__).resolve().parent.parent
    env_ov = args.env_file_overrides
    if args.env_file:
        _load_env_file(os.path.abspath(args.env_file), required=True, override=env_ov)
    else:
        auto_env = repo_root / ".env"
        if auto_env.is_file():
            _load_env_file(str(auto_env), required=False, override=env_ov)

    if args.test_db:
        cfg = _db_config_from_env(
            use_php_fallback=args.use_php_fallback,
            database_php_solo=args.database_php_solo,
        )
        if args.show_config:
            _print_config_summary(cfg)
        try:
            conn = mysql.connector.connect(**cfg)
            cur = conn.cursor()
            cur.execute("SELECT 1")
            cur.fetchone()
            cur.close()
            conn.close()
            print("Conexión OK (SELECT 1).")
        except mysql.connector.Error as err:
            if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
                print(
                    "Acceso denegado (1045): usuario/clave incorrectos, o tu IP no está "
                    "autorizada en el servidor MySQL.\n"
                    "  - Ejecuta con --show-config y revisa longitud contraseña (0 = vacía).\n"
                    "  - Sin variables en esta ventana: prueba --use-php-fallback junto a --test-db.\n"
                    "  - Contraseña con caracteres raros: en PowerShell usa $env:DB_PASSWORD = '...' "
                    "(comillas simples).",
                    file=sys.stderr,
                )
            else:
                print(f"Error MySQL: {err}", file=sys.stderr)
            sys.exit(4)
        return

    if not args.excel:
        parser.error("Falta --excel (o usa --test-db para solo probar la conexión).")

    excel_path = os.path.abspath(args.excel)
    if not os.path.isfile(excel_path):
        print(f"No existe el archivo: {excel_path}", file=sys.stderr)
        sys.exit(1)

    if not args.dry_run:
        cfg = _db_config_from_env(
            use_php_fallback=args.use_php_fallback,
            database_php_solo=args.database_php_solo,
        )
        if args.show_config:
            _print_config_summary(cfg)
    else:
        cfg = None

    try:
        raw = pd.read_excel(
            excel_path,
            sheet_name=args.sheet,
            header=args.header_row,
            engine="openpyxl",
        )
    except Exception as e:
        print(f"Error leyendo Excel: {e}", file=sys.stderr)
        sys.exit(1)

    df = _normalize_columns(raw)
    df = _prepare_rows(df)
    n = len(df)
    print(f"Filas listas para insertar (tras limpiar y deduplicar): {n}")
    if n == 0:
        sys.exit(0)
    if args.dry_run:
        return

    assert cfg is not None
    insert_kw = "INSERT IGNORE" if args.ignore_duplicates else "INSERT"
    sql = f"""
{insert_kw} INTO {args.table}
(id_despacho, id_credito, fecha_alta, fecha_baja, alta, estatus, celula)
VALUES (%s, %s, NOW(), NULL, %s, %s, %s)
"""
    # alta numérico 1; estatus como texto por si la columna es VARCHAR (captura DBeaver A-Z)
    alta_val = 1
    estatus_val = "1"
    celula_val = 1
    data: List[Tuple] = [
        (int(r.id_despacho), int(r.id_credito), alta_val, estatus_val, celula_val)
        for r in df.itertuples(index=False)
    ]

    conn = None
    cursor = None
    try:
        conn = mysql.connector.connect(**cfg)
        cursor = conn.cursor()
        for chunk in _chunks(data, max(1, args.batch_size)):
            cursor.executemany(sql, chunk)
        conn.commit()
        print(f"Commit OK. Filas enviadas: {len(data)} (tamaño de lote: {args.batch_size}).")
    except mysql.connector.Error as err:
        if conn:
            conn.rollback()
        if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
            print(
                "Acceso denegado a MySQL (usuario/clave/host o IP no permitida). "
                "Si la clave tiene backtick (`), en PowerShell usa: $env:DB_PASSWORD = '...' "
                "(comillas simples).",
                file=sys.stderr,
            )
        elif err.errno == errorcode.ER_BAD_DB_ERROR:
            print("La base de datos no existe.", file=sys.stderr)
        else:
            print(f"Error MySQL: {err}", file=sys.stderr)
        sys.exit(4)
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()


if __name__ == "__main__":
    main()
