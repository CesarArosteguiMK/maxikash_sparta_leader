"""
PROCESADOR DE DICTÁMENES - CAMPO
=================================
  1. Conexión BD (2 servidores)
  2. Leer reporte 60k  → __SPARTA_SECRET_REDACTED__.legacy_historico
  3. Traer dictámenes Base 1 → __SPARTA_SECRET_REDACTED__.legacy_historico (dictamen_for)
  4. Traer dictámenes Base 2 → __SPARTA_SECRET_REDACTED__.base_clientes (dictamen_campo)
  5. Agrupar
  6. Procesar crédito por crédito (en Pandas, sin queries individuales)
  7. Generar dataframe final con columnas UNO..QUINCE
  8. Exportar CSV
"""

import pandas as pd
from sqlalchemy import create_engine
import time
import os
from datetime import datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))

# ─────────────────────────────────────────────
# 1. CONFIGURACIÓN DE CONEXIONES
# ─────────────────────────────────────────────

DB1 = {
    "host":     "__SPARTA_HOST_REDACTED__",
    "port":     3306,
    "user":     "__SPARTA_SECRET_REDACTED__",
    "password": '__SPARTA_PASSWORD_REDACTED__',
    "database": "__SPARTA_SECRET_REDACTED__",
}

DB2 = {
    "host":     "__SPARTA_HOST_REDACTED__",
    "port":     3306,
    "user":     "__SPARTA_SECRET_REDACTED__",
    "password": "__SPARTA_PASSWORD_REDACTED__",
    "database": "__SPARTA_SECRET_REDACTED__",
}

def get_engine(cfg):
    url = (
        f"mysql+pymysql://{cfg['user']}:{cfg['password']}"
        f"@{cfg['host']}:{cfg['port']}/{cfg['database']}"
    )
    return create_engine(
        url,
        pool_pre_ping=True,
        connect_args={
            "connect_timeout": 10,   # falla rápido si no puede conectar
            "read_timeout":    300,   # 5 min para leer resultados grandes
            "write_timeout":   300,
        },
    )


# ─────────────────────────────────────────────
# 2 & 3. TRAER DICTÁMENES BASE 1 (query única, sin IN 60k)
# ─────────────────────────────────────────────

def traer_base1(engine):
    """
    Trae los últimos 15 dictámenes campo por crédito directamente.
    Ya no se necesita un SELECT DISTINCT previo ni un IN con 60k IDs.
    La lista de créditos se deriva del propio resultado.
    """
    print("📥 [1/4] Trayendo dictámenes Base 1 (__SPARTA_SECRET_REDACTED__.legacy_historico)...")

    query = """
        SELECT id_credit, comentarios_generales, fecha_dictamen, rn
        FROM (
            SELECT
                id_credit,
                comentarios_generales,
                fecha_dictamen,
                ROW_NUMBER() OVER (
                    PARTITION BY id_credit
                    ORDER BY fecha_dictamen DESC
                ) AS rn
            FROM __SPARTA_SECRET_REDACTED__.legacy_historico
            WHERE contacto != 'Campo' 
        ) ranked
        WHERE rn <= 15
    """
    chunks = pd.read_sql(query, engine, chunksize=5000)
    df = pd.concat(chunks, ignore_index=True)
    print(f"   ✅ {len(df):,} registros traídos de Base 1.")
    return df


# ─────────────────────────────────────────────
# 4. TRAER DICTÁMENES BASE 2 — 1 sola query, solo los que faltan
# ─────────────────────────────────────────────

def traer_base2(engine):
    print("📥 [2/4] Trayendo dictámenes Base 2 (__SPARTA_SECRET_REDACTED__.base_clientes)...")

    query = """
        SELECT id_credito, dictamen_ccc, fecha_carga_base, rn
        FROM (
            SELECT
                id_credito,
                dictamen_ccc,
                fecha_carga_base,
                ROW_NUMBER() OVER (
                    PARTITION BY id_credito
                    ORDER BY fecha_carga_base DESC
                ) AS rn
            FROM `__SPARTA_SECRET_REDACTED__`.base_clientes
            WHERE contacto = 'Telefono'
        ) ranked
        WHERE rn <= 15
    """
    chunks = pd.read_sql(query, engine, chunksize=5000)
    df = pd.concat(chunks, ignore_index=True)
    print(f"   ✅ {len(df):,} registros traídos de Base 2.")
    return df


# ─────────────────────────────────────────────
# 5 & 6. AGRUPAR + PROCESAR EN PANDAS
# ─────────────────────────────────────────────

COLUMNAS = ["UNO","DOS","TRES","CUATRO","CINCO",
            "SEIS","SIETE","OCHO","NUEVE","DIEZ",
            "ONCE","DOCE","TRECE","CATORCE","QUINCE"]

def procesar(df_reporte, df_base1, df_base2):
    print("⚙️  [3/4] Procesando créditos en Pandas...")
    t0 = time.time()

    # dict { id_credit: [dictamen1, dictamen2, ...] }
    grp1 = (
        df_base1.sort_values(["id_credit", "rn"])
                .groupby("id_credit")["comentarios_generales"]
                .apply(list).to_dict()
    )

    # dict { id_credito: [dictamen1, ...] }
    grp2 = {}
    if not df_base2.empty:
        grp2 = (
            df_base2.sort_values(["id_credito", "rn"])
                    .groupby("id_credito")["dictamen_ccc"]
                    .apply(list).to_dict()
        )

    filas = []
    for credito in df_reporte["id_credit"]:
        lista1    = grp1.get(credito, [])
        faltantes = 15 - len(lista1)
        lista2    = grp2.get(credito, [])[:faltantes] if faltantes > 0 else []

        dictamenes  = (lista1 + lista2)[:15]
        dictamenes += [None] * (15 - len(dictamenes))  # rellenar vacíos con None

        fila = {"id_credit": credito}
        for col, val in zip(COLUMNAS, dictamenes):
            fila[col] = val
        filas.append(fila)

    df_final = pd.DataFrame(filas)
    print(f"   ✅ {len(df_final):,} filas procesadas en {time.time()-t0:.1f}s")
    return df_final


# ─────────────────────────────────────────────
# 7 & 8. EXPORTAR CSV
# ─────────────────────────────────────────────

def exportar_csv(df):
    ts   = datetime.now().strftime("%Y%m%d_%H%M%S")
    path = os.path.join(SCRIPT_DIR, f"resultado_dictamenes_{ts}.csv")
    print(f"💾 [4/4] Exportando CSV → {path}")
    df.to_csv(path, index=False, encoding="utf-8-sig")
    print(f"   ✅ Guardado: {path}  |  Shape: {df.shape[0]:,} × {df.shape[1]}")


# ─────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────

def main():
    print("=" * 55)
    print("  PROCESADOR DE DICTÁMENES CAMPO")
    print("=" * 55)

    engine1 = get_engine(DB1)
    engine2 = get_engine(DB2)

    # Paso 1 — Base 1 (sin IN, trae todos los registros 'Campo')
    df_base1 = traer_base1(engine1)
    df_base1["id_credit"] = df_base1["id_credit"].astype(str)
    ids_base1 = set(df_base1["id_credit"].unique())
    print(f"   ℹ️  {len(ids_base1):,} créditos únicos en Base 1.")

    # Paso 2 — Base 2 (sin IN, trae todos los registros 'Campo')
    df_base2 = traer_base2(engine2)
    df_base2["id_credito"] = df_base2["id_credito"].astype(str)
    ids_base2 = set(df_base2["id_credito"].unique()) if not df_base2.empty else set()
    print(f"   ℹ️  {len(ids_base2):,} créditos únicos en Base 2.")

    # Unión de IDs de ambas bases — ningún crédito queda fuera
    todos_ids  = sorted(ids_base1 | ids_base2)
    df_reporte = pd.DataFrame({"id_credit": todos_ids})
    print(f"   ℹ️  {len(todos_ids):,} créditos únicos en total (Base1 ∪ Base2).")

    # Paso 3 — Procesar y exportar
    df_final = procesar(df_reporte, df_base1, df_base2)
    exportar_csv(df_final)

    print("\n✅ PROCESO COMPLETADO")
    print("=" * 55)


if __name__ == "__main__":
    main()