"""
reporte_campo.py
────────────────────────────────────────────────────────────────
Genera un Excel con el personal de los departamentos
"Campo 1-7*" y "Campo 8-21*", incluyendo jerarquía de 4 niveles.

Alineado con el organigrama (CapHum): puesto por MAX(puesto.nivel) en Campo,
activo=1, empate MIN(id_puesto); asigna_jefe con MIN(id_jefe) por persona.

La contraseña puede ir en config abajo o en variable de entorno DB_PASSWORD.
────────────────────────────────────────────────────────────────
"""

import os
import sys
from datetime import datetime
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
PYDEPS = REPO_ROOT / "backend" / "services" / "gastos-cobranza-agent" / "pydeps"
if PYDEPS.is_dir():
    sys.path.insert(0, str(PYDEPS))

import mysql.connector
import pandas as pd

# ── CONFIGURACIÓN ────────────────────────────────────────────────────────────
# Opcional: export DB_PASSWORD en PowerShell para no guardar clave en este archivo.
config = {
    "host": os.getenv("DB_HOST") or "__SPARTA_HOST_REDACTED__",
    "user": os.getenv("DB_USER") or "__SPARTA_SECRET_REDACTED__",
    "password": os.getenv("DB_PASSWORD") or "__SPARTA_PASSWORD_REDACTED__",
    "database": os.getenv("DB_NAME") or "__SPARTA_SECRET_REDACTED__",
    "port": int(os.getenv("DB_PORT") or "3306"),
}

RUTA_SALIDA = f"reporte_campo_{datetime.now().strftime('%Y%m%d_%H%M%S')}.xlsx"

SQL_DEPT_CAMPO = "(d.nombre LIKE 'Campo 1-7%' OR d.nombre LIKE 'Campo 8-21%')"
SQL_DEPT_CAMPO_D2 = "(d2.nombre LIKE 'Campo 1-7%' OR d2.nombre LIKE 'Campo 8-21%')"

ROLES_JERARQUIA = ["supervisor", "subgerente", "gerente", "subdirector"]


def query(cursor, sql: str) -> pd.DataFrame:
    cursor.execute(sql)
    cols = [d[0] for d in cursor.description]
    return pd.DataFrame(cursor.fetchall(), columns=cols)


def armar_nombre(row: dict) -> str:
    apellidop = str(row.get("apellidop", "") or "").strip().upper()
    apellidom = str(row.get("apellidom", "") or "").strip().upper()
    nombres = str(row.get("nombres", "") or "").strip().upper()
    segundo_nombre = str(row.get("segundo_nombre", "") or "").strip().upper()

    nombre_completo = " ".join(p for p in [nombres, segundo_nombre] if p)
    partes = [apellidop, apellidom, nombre_completo]
    return " ".join(p for p in partes if p)


def calc_estatus(persona_id, estatus_val: str, ausencias_dict: dict) -> str:
    if pd.isna(persona_id):
        return ""
    pid = int(persona_id)
    if estatus_val == "Baja":
        return "baja"
    razon = ausencias_dict.get(pid)
    if razon:
        return str(razon).lower().strip()
    if estatus_val == "Activo":
        return "activo"
    return str(estatus_val).lower().strip() if pd.notna(estatus_val) else ""


def cargar_datos(cursor) -> dict:
    print("[INFO] Cargando persona (activos, sin REPORTERIA)...")
    personas_activas = query(
        cursor,
        """
        SELECT id, numero_empleado,
               nombres, segundo_nombre, apellidop, apellidom, estatus
        FROM persona
        WHERE estatus = 'Activo'
          AND UPPER(TRIM(COALESCE(user_name, ''))) <> 'REPORTERIA'
    """,
    )

    print("[INFO] Cargando persona (no baja, para jerarquias)...")
    personas_todas = query(
        cursor,
        """
        SELECT id, nombres, segundo_nombre, apellidop, apellidom, estatus
        FROM persona
        WHERE estatus <> 'Baja'
    """,
    )

    print("[INFO] Cargando ausencias activas...")
    ausencias = query(
        cursor,
        """
        SELECT a.id_persona, ra.nombre AS razon_ausencia
        FROM ausencia a
        INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
        INNER JOIN (
            SELECT id_persona, MIN(id) AS min_id
            FROM ausencia
            WHERE activo = 1
              AND NOW() BETWEEN fecha_inicio AND fecha_fin
            GROUP BY id_persona
        ) pick ON pick.min_id = a.id
    """,
    )

    print("[INFO] Cargando asigna_puesto Campo (activo=1, MAX nivel)...")
    asigna_puesto_activo = query(
        cursor,
        f"""
        SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
        FROM asigna_puesto ap
        INNER JOIN puesto pp ON pp.id = ap.id_puesto
        INNER JOIN departamento d ON d.id = pp.departamento_id
        INNER JOIN (
            SELECT ap2.id_persona, MAX(pp2.nivel) AS max_nivel
            FROM asigna_puesto ap2
            INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto
            INNER JOIN departamento d2 ON d2.id = pp2.departamento_id
            WHERE ap2.activo = 1
              AND {SQL_DEPT_CAMPO_D2}
            GROUP BY ap2.id_persona
        ) sel ON sel.id_persona = ap.id_persona AND pp.nivel = sel.max_nivel
        WHERE ap.activo = 1
          AND {SQL_DEPT_CAMPO}
        GROUP BY ap.id_persona
    """,
    )

    print("[INFO] Cargando asigna_puesto Campo (fallback sin activo)...")
    asigna_puesto_todos = query(
        cursor,
        f"""
        SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
        FROM asigna_puesto ap
        INNER JOIN puesto pp ON pp.id = ap.id_puesto
        INNER JOIN departamento d ON d.id = pp.departamento_id
        INNER JOIN (
            SELECT ap2.id_persona, MAX(pp2.nivel) AS max_nivel
            FROM asigna_puesto ap2
            INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto
            INNER JOIN departamento d2 ON d2.id = pp2.departamento_id
            WHERE {SQL_DEPT_CAMPO_D2}
            GROUP BY ap2.id_persona
        ) sel ON sel.id_persona = ap.id_persona AND pp.nivel = sel.max_nivel
        WHERE {SQL_DEPT_CAMPO}
        GROUP BY ap.id_persona
    """,
    )

    print("[INFO] Cargando puestos, departamentos, equivalencias...")
    puestos = query(
        cursor,
        "SELECT id, nombre AS puesto_nombre, nivel, departamento_id FROM puesto",
    )
    departamentos = query(cursor, "SELECT id AS dept_id, nombre AS dept_nombre FROM departamento")
    equiv = query(cursor, "SELECT id_puesto, id_puesto_legacy FROM equivalencias_legacy_puestos")
    puestos_leg = query(cursor, "SELECT id AS id_leg, clave FROM puestos_legacy")

    print("[INFO] Cargando puesto_legacy (MAX nivel + equivalencia)...")
    puesto_legacy_activo = query(
        cursor,
        """
        SELECT x.id_persona, pl.clave AS puesto_legacy_clave
        FROM (
            SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
            FROM asigna_puesto ap
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            INNER JOIN equivalencias_legacy_puestos el ON el.id_puesto = ap.id_puesto
            INNER JOIN (
                SELECT ap2.id_persona, MAX(pp2.nivel) AS max_nivel
                FROM asigna_puesto ap2
                INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                INNER JOIN equivalencias_legacy_puestos el2 ON el2.id_puesto = ap2.id_puesto
                WHERE ap2.activo = 1
                GROUP BY ap2.id_persona
            ) sel ON sel.id_persona = ap.id_persona AND pp.nivel = sel.max_nivel
            WHERE ap.activo = 1
            GROUP BY ap.id_persona
        ) x
        INNER JOIN asigna_puesto ap
            ON ap.id_persona = x.id_persona AND ap.id_puesto = x.id_puesto AND ap.activo = 1
        INNER JOIN equivalencias_legacy_puestos el ON el.id_puesto = ap.id_puesto
        INNER JOIN puestos_legacy pl ON pl.id = el.id_puesto_legacy
    """,
    )

    puesto_legacy_fallback = query(
        cursor,
        """
        SELECT x.id_persona, pl.clave AS puesto_legacy_clave
        FROM (
            SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
            FROM asigna_puesto ap
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            INNER JOIN equivalencias_legacy_puestos el ON el.id_puesto = ap.id_puesto
            INNER JOIN (
                SELECT ap2.id_persona, MAX(pp2.nivel) AS max_nivel
                FROM asigna_puesto ap2
                INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                INNER JOIN equivalencias_legacy_puestos el2 ON el2.id_puesto = ap2.id_puesto
                GROUP BY ap2.id_persona
            ) sel ON sel.id_persona = ap.id_persona AND pp.nivel = sel.max_nivel
            GROUP BY ap.id_persona
        ) x
        INNER JOIN asigna_puesto ap
            ON ap.id_persona = x.id_persona AND ap.id_puesto = x.id_puesto
        INNER JOIN equivalencias_legacy_puestos el ON el.id_puesto = ap.id_puesto
        INNER JOIN puestos_legacy pl ON pl.id = el.id_puesto_legacy
    """,
    )

    print("[INFO] Cargando jerarquias (asigna_jefe mas reciente + vacantes)...")
    jefes = query(
        cursor,
        """
        SELECT aj.id_persona,
               aj.id_jefe,
               aj.id_vacante_jefe,
               v.id_jefe AS id_jefe_vacante
        FROM asigna_jefe aj
        INNER JOIN (
            SELECT id_persona, MAX(id) AS max_id
            FROM asigna_jefe
            GROUP BY id_persona
        ) ult ON ult.id_persona = aj.id_persona AND ult.max_id = aj.id
        LEFT JOIN vacantes_personal v ON v.id = aj.id_vacante_jefe
    """,
    )

    return dict(
        personas_activas=personas_activas,
        personas_todas=personas_todas,
        ausencias=ausencias,
        asigna_puesto_activo=asigna_puesto_activo,
        asigna_puesto_todos=asigna_puesto_todos,
        puestos=puestos,
        departamentos=departamentos,
        equiv=equiv,
        puestos_leg=puestos_leg,
        puesto_legacy_activo=puesto_legacy_activo,
        puesto_legacy_fallback=puesto_legacy_fallback,
        jefes=jefes,
    )


def procesar(datos: dict) -> pd.DataFrame:
    personas_activas = datos["personas_activas"]
    ausencias_dict = datos["ausencias"].set_index("id_persona")["razon_ausencia"].to_dict()
    asigna_puesto_activo = datos["asigna_puesto_activo"]
    asigna_puesto_todos = datos["asigna_puesto_todos"]
    puestos = datos["puestos"]
    departamentos = datos["departamentos"]
    equiv = datos["equiv"]
    puestos_leg = datos["puestos_leg"]
    jefes = datos["jefes"]

    def norm(df_leg, col="puesto_legacy_clave"):
        return {
            int(pid): str(clave).lower().strip()
            for pid, clave in df_leg.set_index("id_persona")[col].items()
            if pd.notna(clave)
        }

    pleg_map = norm(datos["puesto_legacy_activo"])
    for pid, clave in norm(datos["puesto_legacy_fallback"]).items():
        if pid not in pleg_map:
            pleg_map[pid] = clave

    df = personas_activas.merge(
        asigna_puesto_activo, left_on="id", right_on="id_persona", how="left"
    )
    sin_puesto_mask = df["id_puesto"].isna()
    if sin_puesto_mask.any():
        fallback = asigna_puesto_todos.set_index("id_persona")["id_puesto"]
        df.loc[sin_puesto_mask, "id_puesto"] = df.loc[sin_puesto_mask, "id"].map(fallback)
        print(f"[FIX] {sin_puesto_mask.sum()} persona(s) sin puesto Campo activo → fallback aplicado")

    df = df.merge(puestos, left_on="id_puesto", right_on="id", how="left", suffixes=("", "_p"))
    df = df.merge(departamentos, left_on="departamento_id", right_on="dept_id", how="left")
    df = df.merge(equiv, on="id_puesto", how="left")
    df = df.merge(puestos_leg, left_on="id_puesto_legacy", right_on="id_leg", how="left")

    mask_campo = df["dept_nombre"].str.startswith("Campo 1-7", na=False) | df[
        "dept_nombre"
    ].str.startswith("Campo 8-21", na=False)
    df = df[mask_campo].copy()
    print(f"[INFO] {len(df)} personas en departamentos Campo 1-7* / Campo 8-21*")

    df["external_id"] = df["numero_empleado"]
    df["nombre_completo"] = df.apply(armar_nombre, axis=1)
    df["estatus"] = df.apply(
        lambda r: calc_estatus(r["id"], r["estatus"], ausencias_dict), axis=1
    )
    df["es_gestor"] = df["clave"].apply(lambda x: "Si" if x == "gestor" else "No")
    df["puesto_legacy"] = df["clave"].fillna("")
    df["puesto_actual"] = df["puesto_nombre"].fillna("")
    df["departamento"] = df["dept_nombre"].fillna("")

    # Mapa de segmento/departamento para emular el organigrama filtrado por departamento:
    # si la cadena de jefe salta fuera del mismo depto Campo del colaborador, se corta.
    dept_map = {}
    if "id" in df.columns and "dept_id" in df.columns:
        dept_rows = (
            df[["id", "dept_id"]]
            .dropna(subset=["id", "dept_id"])
            .drop_duplicates(subset=["id"], keep="first")
        )
        for _, r in dept_rows.iterrows():
            dept_map[int(r["id"])] = int(r["dept_id"])

    pmap = (
        datos["personas_todas"]
        .set_index("id")[
            ["nombres", "segundo_nombre", "apellidop", "apellidom", "estatus"]
        ]
        .to_dict("index")
    )
    jefes_map = jefes.set_index("id_persona")[
        ["id_jefe", "id_vacante_jefe", "id_jefe_vacante"]
    ].to_dict("index")

    def resolver_jerarquia(persona_id) -> dict:
        resultado = {}
        for rol in ROLES_JERARQUIA:
            resultado[rol] = ""
            resultado[f"{rol}_estatus"] = ""

        current = persona_id
        visitados = set()
        dept_objetivo = None
        if not pd.isna(current):
            dept_objetivo = dept_map.get(int(current))

        for _ in range(8):
            if pd.isna(current) or int(current) in visitados:
                break
            visitados.add(int(current))

            jefe_info = jefes_map.get(int(current))
            if jefe_info is None:
                break

            jefe_id_raw = jefe_info.get("id_jefe")
            if jefe_id_raw is None or pd.isna(jefe_id_raw):
                jefe_id_raw = jefe_info.get("id_jefe_vacante")
            if jefe_id_raw is None or pd.isna(jefe_id_raw):
                break

            jefe_id = int(jefe_id_raw)
            p = pmap.get(jefe_id)
            if p is None:
                break

            # Igual que organigrama con filtro de departamento:
            # no cruzar a un jefe fuera del depto Campo del colaborador.
            if dept_objetivo is not None and dept_map.get(jefe_id) != dept_objetivo:
                break

            rol_jefe = pleg_map.get(jefe_id, "")
            if rol_jefe in ROLES_JERARQUIA and resultado[rol_jefe] == "":
                resultado[rol_jefe] = armar_nombre(p)
                resultado[f"{rol_jefe}_estatus"] = calc_estatus(
                    jefe_id, p["estatus"], ausencias_dict
                )

            current = jefe_id

        return resultado

    print("[INFO] Resolviendo jerarquias (esto puede tardar unos segundos)...")
    jerarquias = df["id"].apply(resolver_jerarquia).apply(pd.Series)
    df = pd.concat([df.reset_index(drop=True), jerarquias.reset_index(drop=True)], axis=1)

    cols = [
        "external_id",
        "nombre_completo",
        "estatus",
        "es_gestor",
        "puesto_legacy",
        "puesto_actual",
        "departamento",
        "supervisor",
        "supervisor_estatus",
        "subgerente",
        "subgerente_estatus",
        "gerente",
        "gerente_estatus",
        "subdirector",
        "subdirector_estatus",
    ]
    return df[cols].sort_values("external_id").reset_index(drop=True)


def main():
    if not (config.get("password") or "").strip():
        print("[ERROR] Falta contraseña: edita config['password'] en este archivo o define DB_PASSWORD.")
        sys.exit(1)

    print("[INFO] Conectando a base de datos...")
    try:
        conn = mysql.connector.connect(**config)
        cursor = conn.cursor()
    except mysql.connector.Error as e:
        print(f"[ERROR] No se pudo conectar: {e}")
        sys.exit(1)

    try:
        datos = cargar_datos(cursor)
        resultado = procesar(datos)
    finally:
        cursor.close()
        conn.close()
        print("[INFO] Conexion cerrada.")

    gestores = resultado[resultado["es_gestor"] == "Si"]
    print(f"\n[INFO] Gestores incluidos: {len(gestores)}")
    if len(gestores):
        print(
            gestores[
                [
                    "external_id",
                    "nombre_completo",
                    "puesto_legacy",
                    "subgerente",
                    "gerente",
                    "subdirector",
                ]
            ].to_string(index=False)
        )

    resultado.to_excel(RUTA_SALIDA, index=False, engine="openpyxl")
    print(f"\n✅ Archivo generado: {RUTA_SALIDA}  ({len(resultado)} filas)")


if __name__ == "__main__":
    main()
