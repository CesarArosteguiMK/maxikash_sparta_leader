"""
Lógica alineada a Models\\PrimerosPagosHistoricoSegundometro / menú Lunes de cierre (`Empresa::getVencimientosLunes`):
- Fuente: `tbl_histo_primeros_pagos`.
- Nacimiento: `Bucket_Morosidad_Real` (como `tbl_segundometro_semana`).
- Mora corte: misma columna que `Empresa::getCorteActual()` si existe en histórico; si no, COALESCE `Dias_mora_Lunes_*` + `Dias_mora`.
"""
from __future__ import annotations

import os
import re
from concurrent.futures import ThreadPoolExecutor
from contextlib import closing
from dataclasses import dataclass
from datetime import date, datetime, timedelta
from typing import Any, Optional
from zoneinfo import ZoneInfo

import pymysql

_TZ = ZoneInfo("America/Mexico_City")

# Ventana para localizar etiquetas SEMANA (días hacia atrás desde hoy en el servidor MySQL)
LISTA_SEMANAS_LOOKBACK_DIAS = int(os.environ.get("PP_HISTO_LOOKBACK_DIAS", "30"))
# Máximo de grupos distintos (SEMANA) que trae la consulta agregada (no se leen 350k filas)
CANDIDATAS_GROUP_LIMIT = int(os.environ.get("PP_HISTO_CANDIDATAS_GROUP_LIMIT", "50"))
NUM_SEMANAS = int(os.environ.get("PP_HISTO_NUM_SEMANAS", "5"))

BUCKET_ORDER = [
    "a) Current",
    "b) 1 a 7 dias",
    "c) 8 a 30 dias",
    "d) 31 a 60 dias",
    "e) 61+ dias",
]
# Orden de severidad: 0 = menores días, 4 = más; «cobrado al corte» = corte estrictamente mejor
BUCKET_ORD: dict[str, int] = {b: i for i, b in enumerate(BUCKET_ORDER)}

# Desactiva el COUNT por etiqueta (69k) que escanea casi toda la semana. Activar: PP_HISTO_INCLUIR_CONTEO_ETIQUETA=1
INCLUIR_CONTEO_ETIQUETA = os.environ.get("PP_HISTO_INCLUIR_CONTEO_ETIQUETA", "0").lower() in (
    "1",
    "true",
    "sí",
    "si",
    "yes",
)
# Paralelo: en MySQL remoto 5 consultas pesadas a la vez empeoran el tiempo. Default 1 = secuencial, misma conexión.
PP_HISTO_WORKERS = max(1, int(os.environ.get("PP_HISTO_WORKERS", "1")))

TABLA_HISTORICO_PRIMEROS_PAGOS = os.environ.get(
    "PP_HISTO_TABLA", "tbl_histo_primeros_pagos"
)

DIA_MORA_LUNES_COLS = [
    "Dias_mora_Lunes_23_50",
    "Dias_mora_Lunes_20_30",
    "Dias_mora_Lunes_18_30",
    "Dias_mora_Lunes_16_30",
    "Dias_mora_Lunes_14_30",
    "Dias_mora_Lunes_13_30",
    "Dias_mora_Lunes_11_30",
    "Dias_mora_Lunes_09_30",
    "Dias_mora_Lunes_07_30",
]

# Peso día/hora = misma regla que `Empresa::getCorteActual()` (menú Lunes de cierre / `tbl_segundometro_semana`).
ORDEN_DIA_EMPRESA = {
    "Lunes": 1,
    "Martes": 2,
    "Miercoles": 3,
    "Jueves": 4,
    "Viernes": 5,
    "Sabado": 6,
    "Domingo": 7,
}

# (dia, hhmm, columna) — orden idéntico al array $cortes en Empresa.php
CORTES_EMPRESA: list[tuple[str, int, str]] = [
    ("Domingo", 2350, "Dias_mora_Domingo_23_50"),
    ("Domingo", 2030, "Dias_mora_Domingo_20_30"),
    ("Domingo", 1830, "Dias_mora_Domingo_18_30"),
    ("Domingo", 1630, "Dias_mora_Domingo_16_30"),
    ("Domingo", 1430, "Dias_mora_Domingo_14_30"),
    ("Domingo", 1330, "Dias_mora_Domingo_13_30"),
    ("Domingo", 1130, "Dias_mora_Domingo_11_30"),
    ("Domingo", 930, "Dias_mora_Domingo_09_30"),
    ("Domingo", 730, "Dias_mora_Domingo_07_30"),
    ("Sabado", 2350, "Dias_mora_Sabado_23_50"),
    ("Sabado", 2030, "Dias_mora_Sabado_20_30"),
    ("Sabado", 1830, "Dias_mora_Sabado_18_30"),
    ("Sabado", 1630, "Dias_mora_Sabado_16_30"),
    ("Sabado", 1430, "Dias_mora_Sabado_14_30"),
    ("Sabado", 1330, "Dias_mora_Sabado_13_30"),
    ("Sabado", 1130, "Dias_mora_Sabado_11_30"),
    ("Sabado", 930, "Dias_mora_Sabado_09_30"),
    ("Sabado", 730, "Dias_mora_Sabado_07_30"),
    ("Viernes", 2350, "Dias_mora_Viernes_23_50"),
    ("Viernes", 2030, "Dias_mora_Viernes_20_30"),
    ("Viernes", 1830, "Dias_mora_Viernes_18_30"),
    ("Viernes", 1630, "Dias_mora_Viernes_16_30"),
    ("Viernes", 1430, "Dias_mora_Viernes_14_30"),
    ("Viernes", 1330, "Dias_mora_Viernes_13_30"),
    ("Viernes", 1130, "Dias_mora_Viernes_11_30"),
    ("Viernes", 930, "Dias_mora_Viernes_09_30"),
    ("Viernes", 730, "Dias_mora_Viernes_07_30"),
    ("Jueves", 2350, "Dias_mora_Jueves_23_50"),
    ("Jueves", 2030, "Dias_mora_Jueves_20_30"),
    ("Jueves", 1830, "Dias_mora_Jueves_18_30"),
    ("Jueves", 1630, "Dias_mora_Jueves_16_30"),
    ("Jueves", 1430, "Dias_mora_Jueves_14_30"),
    ("Jueves", 1330, "Dias_mora_Jueves_13_30"),
    ("Jueves", 1130, "Dias_mora_Jueves_11_30"),
    ("Jueves", 930, "Dias_mora_Jueves_09_30"),
    ("Jueves", 730, "Dias_mora_Jueves_07_30"),
    ("Miercoles", 2350, "Dias_mora_Miercoles_23_50"),
    ("Miercoles", 2030, "Dias_mora_Miercoles_20_30"),
    ("Miercoles", 1830, "Dias_mora_Miercoles_18_30"),
    ("Miercoles", 1630, "Dias_mora_Miercoles_16_30"),
    ("Miercoles", 1430, "Dias_mora_Miercoles_14_30"),
    ("Miercoles", 1330, "Dias_mora_Miercoles_13_30"),
    ("Miercoles", 1130, "Dias_mora_Miercoles_11_30"),
    ("Miercoles", 930, "Dias_mora_Miercoles_09_30"),
    ("Miercoles", 730, "Dias_mora_Miercoles_07_30"),
    ("Martes", 2350, "Dias_mora_Martes_23_50"),
    ("Martes", 2030, "Dias_mora_Martes_20_30"),
    ("Martes", 1830, "Dias_mora_Martes_18_30"),
    ("Martes", 1630, "Dias_mora_Martes_16_30"),
    ("Martes", 1430, "Dias_mora_Martes_14_30"),
    ("Martes", 1330, "Dias_mora_Martes_13_30"),
    ("Martes", 1130, "Dias_mora_Martes_11_30"),
    ("Martes", 930, "Dias_mora_Martes_09_30"),
    ("Martes", 730, "Dias_mora_Martes_07_30"),
    ("Lunes", 2350, "Dias_mora_Lunes_23_50"),
    ("Lunes", 2030, "Dias_mora_Lunes_20_30"),
    ("Lunes", 1830, "Dias_mora_Lunes_18_30"),
    ("Lunes", 1630, "Dias_mora_Lunes_16_30"),
    ("Lunes", 1430, "Dias_mora_Lunes_14_30"),
    ("Lunes", 1330, "Dias_mora_Lunes_13_30"),
    ("Lunes", 1130, "Dias_mora_Lunes_11_30"),
    ("Lunes", 930, "Dias_mora_Lunes_09_30"),
    ("Lunes", 730, "Dias_mora_Lunes_07_30"),
]


def get_corte_actual_col(now: Optional[datetime] = None) -> Optional[str]:
    """Réplica de `Empresa::getCorteActual()` (sin caché archivo)."""
    now = now or datetime.now()
    dias_nombres = ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"]
    php_w = (now.weekday() + 1) % 7
    hoy = dias_nombres[php_w]
    hora_actual = now.hour * 100 + now.minute
    peso_actual = ORDEN_DIA_EMPRESA[hoy] * 10000 + hora_actual
    for dia, hhmm, col in CORTES_EMPRESA:
        peso_cand = ORDEN_DIA_EMPRESA[dia] * 10000 + hhmm
        if peso_cand <= peso_actual:
            return col
    return None

def _canon_bucket(label: str) -> Optional[str]:
    """Alinea variantes (días/dias) a la clave BUCKET_ORD."""
    t = (label or "").strip()
    if t in ("", "—"):
        return None
    t0 = t.replace("días", "dias").lower()
    for b in BUCKET_ORDER:
        if t0 == b.replace("días", "dias").lower() or t == b:
            return b
    return None


def _etiqueta_cobrado_al_corte(nac: str, corte: str) -> str:
    """
    Igual que en jerarquía PHP: cobrado = mora lunes (corte) «mejor» que nacimiento (orden <).
    Mismo criterio en queryJerarquia: ord_c < ord_n.
    """
    on = _canon_bucket(nac)
    oc = _canon_bucket(corte)
    if on is None or oc is None:
        return "— (sin dato de etapa)"
    oi, oj = BUCKET_ORD[on], BUCKET_ORD[oc]
    if oj < oi:
        return "Sí, cobrado (mejora al corte)"
    if oj == oi:
        return "Sin mejora (misma etapa)"
    return "No (peor al corte)"


@dataclass
class RangoSemana:
    martes: str
    domingo: str
    lunes_iso: str


def get_conn():
    return pymysql.connect(
        host=os.environ.get("MYSQL_HOST", "127.0.0.1"),
        port=int(os.environ.get("MYSQL_PORT", "3306")),
        user=os.environ.get("MYSQL_USER", "root"),
        password=os.environ.get("MYSQL_PASSWORD", "") or "",
        database=os.environ.get("MYSQL_DB", "__SPARTA_SECRET_REDACTED__"),
        charset="utf8mb4",
        connect_timeout=15,
        read_timeout=int(os.environ.get("MYSQL_READ_TIMEOUT", "120")),
        write_timeout=30,
        cursorclass=pymysql.cursors.DictCursor,
    )


def _tabla_tiene_columna(cur, nombre: str) -> bool:
    cur.execute(
        "SELECT COUNT(*) AS n FROM information_schema.COLUMNS "
        "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = %s",
        (TABLA_HISTORICO_PRIMEROS_PAGOS, nombre),
    )
    row = cur.fetchone() or {}
    return int(row.get("n") or 0) > 0


def _columnas_tabla_histo_set(cur) -> set[str]:
    cur.execute(
        "SELECT COLUMN_NAME AS c FROM information_schema.COLUMNS "
        "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s",
        (TABLA_HISTORICO_PRIMEROS_PAGOS,),
    )
    return {str(r.get("c") or "") for r in (cur.fetchall() or []) if r.get("c")}


def _sql_mora_corte_histo(cur) -> str:
    """Misma columna que menú Lunes de cierre (`get_corte_actual_col`); si no existe en histórico, COALESCE Lunes_* + Dias_mora."""
    cols = _columnas_tabla_histo_set(cur)
    menu = get_corte_actual_col()
    if menu and menu in cols:
        return f"CAST(`{menu}` AS SIGNED)"
    parts: list[str] = []
    for c in DIA_MORA_LUNES_COLS:
        if c in cols:
            parts.append(f"CAST(`{c}` AS SIGNED)")
    if "Dias_mora" in cols:
        parts.append("CAST(`Dias_mora` AS SIGNED)")
    if not parts:
        return "NULL"
    return "COALESCE(" + ", ".join(parts) + ")"


def _sql_bucket_corte_desde_mora(mora_expr: str) -> str:
    return f"""(CASE
        WHEN ({mora_expr}) IS NULL THEN NULL
        WHEN ({mora_expr}) < 1 THEN 'a) Current'
        WHEN ({mora_expr}) BETWEEN 1 AND 7 THEN 'b) 1 a 7 dias'
        WHEN ({mora_expr}) BETWEEN 8 AND 30 THEN 'c) 8 a 30 dias'
        WHEN ({mora_expr}) BETWEEN 31 AND 60 THEN 'd) 31 a 60 dias'
        ELSE 'e) 61+ dias' END)"""


def _sql_bucket_nacimiento_expr(col: str) -> str:
    c = f"`{col}`"
    return f"""(CASE TRIM(CAST({c} AS CHAR))
        WHEN 'a) Current' THEN 'a) Current'
        WHEN 'Current' THEN 'a) Current'
        WHEN 'current' THEN 'a) Current'
        WHEN 'b) 1 a 7 dias' THEN 'b) 1 a 7 dias'
        WHEN 'b) 1 a 7 días' THEN 'b) 1 a 7 dias'
        WHEN 'c) 8 a 30 dias' THEN 'c) 8 a 30 dias'
        WHEN 'c) 8 a 30 días' THEN 'c) 8 a 30 dias'
        WHEN 'c) 8 a 14 dias' THEN 'c) 8 a 30 dias'
        WHEN 'c) 8 a 14 días' THEN 'c) 8 a 30 dias'
        WHEN 'd) 15 a 21 dias' THEN 'c) 8 a 30 dias'
        WHEN 'd) 15 a 21 días' THEN 'c) 8 a 30 dias'
        WHEN 'e) 22 a 29 dias' THEN 'c) 8 a 30 dias'
        WHEN 'e) 22 a 30 dias' THEN 'c) 8 a 30 dias'
        WHEN 'e) 22 a 30 días' THEN 'c) 8 a 30 dias'
        WHEN 'd) 31 a 60 dias' THEN 'd) 31 a 60 dias'
        WHEN 'd) 31 a 60 días' THEN 'd) 31 a 60 dias'
        WHEN 'f) 31 a 60 dias' THEN 'd) 31 a 60 dias'
        WHEN 'f) 31 a 60 días' THEN 'd) 31 a 60 dias'
        WHEN 'e) 61+ dias' THEN 'e) 61+ dias'
        WHEN 'e) 61+ días' THEN 'e) 61+ dias'
        WHEN 'g) 61 a 90 dias' THEN 'e) 61+ dias'
        WHEN 'h) 91 a 120 dias' THEN 'e) 61+ dias'
        WHEN 'i) 121+ dias' THEN 'e) 61+ dias'
        WHEN 'j) First Payment Default' THEN 'b) 1 a 7 dias'
        WHEN 'j) Second Payment Default' THEN 'c) 8 a 30 dias'
        WHEN 'k) Never Paid' THEN 'e) 61+ dias'
        ELSE NULL END)"""


def columna_nacimiento_existe(cur) -> str:
    """Igual que `getVencimientosLunes`: `Bucket_Morosidad_Real` primero."""
    t = TABLA_HISTORICO_PRIMEROS_PAGOS
    cur.execute(f"SHOW COLUMNS FROM `{t}` LIKE 'Bucket_Morosidad_Real'")
    if cur.fetchone():
        return "Bucket_Morosidad_Real"
    cur.execute(f"SHOW COLUMNS FROM `{t}` LIKE 'Bucket_Morosidad'")
    if cur.fetchone():
        return "Bucket_Morosidad"
    return "Bucket_Morosidad"


def resolver_rango_martes_domingo(etiqueta: str) -> Optional[RangoSemana]:
    m = re.search(r"(?i)(?:semana\s*)?(\d{1,2})\s*[-_/\s]\s*(\d{4})", etiqueta)
    if not m:
        return None
    sem = int(m.group(1))
    anio = int(m.group(2))
    if sem < 1 or sem > 53 or anio < 2000 or anio > 2100:
        return None
    lunes = date.fromisocalendar(anio, sem, 1)
    mar = lunes + timedelta(days=1)
    dom = lunes + timedelta(days=6)
    return RangoSemana(
        martes=mar.isoformat(),
        domingo=dom.isoformat(),
        lunes_iso=lunes.isoformat(),
    )


def where_fecha_params(
    modo: str, martes_iso: str, domingo_iso: str, lunes_iso: Optional[str]
) -> tuple[str, list]:
    if modo == "rango":
        d0 = date.fromisoformat(martes_iso)
        d1 = date.fromisoformat(domingo_iso)
        fechas_iso: list[str] = []
        fechas_dmy: list[str] = []
        d = d0
        while d <= d1:
            fechas_iso.append(d.isoformat())
            fechas_dmy.append(d.strftime("%d/%m/%Y"))
            d += timedelta(days=1)
        n = len(fechas_iso)
        ph = ", ".join(["%s"] * n)
        where = f" AND (Fecha_primer_vencimiento IN ({ph}) OR Fecha_primer_vencimiento IN ({', '.join(['%s']*n)}))"
        return where, fechas_iso + fechas_dmy
    if modo == "lunes" and lunes_iso:
        dt = date.fromisoformat(lunes_iso)
        dmy = dt.strftime("%d/%m/%Y")
        return " AND (Fecha_primer_vencimiento = %s OR Fecha_primer_vencimiento = %s)", [
            lunes_iso,
            dmy,
        ]
    return "", []


def _where_semana() -> str:
    return "TRIM(CAST(SEMANA AS CHAR)) = %s"


def query_meta(
    cur, sem: str, martes: str, dom: str, modo: str, lunes_iso: Optional[str]
) -> int:
    wf, pextra = where_fecha_params(modo, martes, dom, lunes_iso)
    sql = f"SELECT COUNT(*) AS total FROM `{TABLA_HISTORICO_PRIMEROS_PAGOS}` WHERE {_where_semana()}{wf}"
    cur.execute(sql, (sem, *pextra))
    r = cur.fetchone() or {}
    return int(r.get("total") or 0)


def query_distrib(
    cur,
    col_nac: str,
    sem: str,
    martes: str,
    dom: str,
    modo: str,
    lunes_iso: Optional[str],
) -> list[dict[str, Any]]:
    mora = _sql_mora_corte_histo(cur)
    b_nac = _sql_bucket_nacimiento_expr(col_nac)
    b_corte = _sql_bucket_corte_desde_mora(mora)
    wf, pextra = where_fecha_params(modo, martes, dom, lunes_iso)
    t = TABLA_HISTORICO_PRIMEROS_PAGOS
    sql = f"""
        SELECT bn AS bucket_nacio, bc AS bucket_corte, COUNT(*) AS cnt
        FROM (
            SELECT {b_nac} AS bn, {b_corte} AS bc
            FROM `{t}`
            WHERE {_where_semana()}{wf}
        ) t
        GROUP BY bn, bc
    """
    cur.execute(sql, (sem, *pextra))
    return list(cur.fetchall() or [])


def agregar_candidatas() -> list[dict[str, Any]]:
    """
    Etiquetas recientes **sin** traer cientos de miles de filas al cliente.
    Una sola agregación en el servidor: MAX(fecha_hora_insert) por SEMANA
    en la ventana de días, ordenada por recencia. Luego filtramos en Python
    (patrón Semana NN-AAAA; ya no se excluye la semana ISO actual).
    """
    sql = """
        SELECT
            TRIM(CAST(SEMANA AS CHAR)) AS semana,
            MAX(fecha_hora_insert) AS ultimo_insert
        FROM `{TABLA_HISTORICO_PRIMEROS_PAGOS}`
        WHERE fecha_hora_insert >= DATE_SUB(CURDATE(), INTERVAL %s DAY)
          AND SEMANA IS NOT NULL
          AND TRIM(CAST(SEMANA AS CHAR)) <> ''
        GROUP BY TRIM(CAST(SEMANA AS CHAR))
        ORDER BY MAX(fecha_hora_insert) DESC
        LIMIT %s
    """
    with closing(get_conn()) as c:
        with c.cursor() as cur:
            cur.execute(
                sql,
                (LISTA_SEMANAS_LOOKBACK_DIAS, CANDIDATAS_GROUP_LIMIT),
            )
            rows = cur.fetchall() or []
    out: list[dict[str, Any]] = []
    for r in rows:
        s = str(r.get("semana") or "").strip()
        if not s:
            continue
        rango = resolver_rango_martes_domingo(s)
        if rango is None:
            continue
        u = r.get("ultimo_insert")
        ustr = u.isoformat(sep=" ", timespec="seconds") if hasattr(u, "isoformat") else (str(u) if u else None)
        out.append(
            {
                "semana": s,
                "ultimo_insert": ustr,
                "periodo_martes": rango.martes,
                "periodo_domingo": rango.domingo,
                "lunes_primer_vencimiento": rango.lunes_iso,
            }
        )
        if len(out) >= NUM_SEMANAS:
            break
    return out


def contar_filas_histo_por_semanas(
    cur, semanas: list[str]
) -> dict[str, int]:
    if not semanas:
        return {}
    ph = ", ".join(["%s"] * len(semanas))
    sql = f"""
        SELECT TRIM(CAST(SEMANA AS CHAR)) AS s, COUNT(*) AS c
        FROM `{TABLA_HISTORICO_PRIMEROS_PAGOS}`
        WHERE TRIM(CAST(SEMANA AS CHAR)) IN ({ph})
        GROUP BY TRIM(CAST(SEMANA AS CHAR))
    """
    cur.execute(sql, tuple(semanas))
    d: dict[str, int] = {}
    for r in cur.fetchall() or []:
        d[str(r.get("s", "")).strip()] = int(r.get("c") or 0)
    return d


def resumen_una_semana(
    con, col_nac: str, c: dict[str, Any]
) -> dict[str, Any]:
    """Replica resumenPorSemana: meta + distrib, sin devolver filas de créditos."""
    sem = c["semana"]
    m1, m2, lunes_iso = c["periodo_martes"], c["periodo_domingo"], c["lunes_primer_vencimiento"]
    with con.cursor() as cur:
        meta = query_meta(cur, sem, m1, m2, "lunes", lunes_iso)
        criterio = "lunes_cierre"
        if meta < 1:
            meta = query_meta(cur, sem, m1, m2, "rango", None)
            criterio = "martes_domingo"
        pares = query_distrib(
            cur,
            col_nac,
            sem,
            m1,
            m2,
            "lunes" if criterio == "lunes_cierre" else "rango",
            lunes_iso if criterio == "lunes_cierre" else None,
        )
    nac_dist: dict[str, int] = {b: 0 for b in BUCKET_ORDER}
    matriz: dict[str, dict[str, int]] = {bn: {bc: 0 for bc in BUCKET_ORDER} for bn in BUCKET_ORDER}
    for r in pares:
        bn = (r.get("bucket_nacio") or "").strip() or None
        bc = (r.get("bucket_corte") or "").strip() or None
        cnt = int(r.get("cnt") or 0)
        if bn and bn in nac_dist:
            nac_dist[bn] += cnt
        if (
            bn
            and bc
            and bn in matriz
            and bc in matriz[bn]
        ):
            matriz[bn][bc] += cnt
    t_cur = nac_dist.get("a) Current", 0)
    m17 = matriz.get("b) 1 a 7 dias", {}) or {}
    recuperados_1_7 = int(m17.get("a) Current", 0) or 0)
    t_1_7 = nac_dist.get("b) 1 a 7 dias", 0)
    current_mas_rec = t_cur + recuperados_1_7
    pend_pp = max(0, t_1_7 - recuperados_1_7)
    pares_orden = sorted(
        [
            {
                "morosidad_al_nacimiento": p.get("bucket_nacio") or "—",
                "mora_al_lunes_corte": p.get("bucket_corte") or "—",
                "cobrado_mejora_al_corte": _etiqueta_cobrado_al_corte(
                    str(p.get("bucket_nacio") or ""),
                    str(p.get("bucket_corte") or ""),
                ),
                "conteo_creditos": int(p.get("cnt") or 0),
            }
            for p in pares
        ],
        key=lambda x: -x["conteo_creditos"],
    )
    g = t_cur + t_1_7
    mostrar = g > 0
    pC = int(round(100.0 * t_cur / g)) if mostrar and g else 0
    p7 = 100 - pC if mostrar and g else 0
    return {
        "semana": sem,
        "criterio_fecha": criterio,
        "Fecha_primer_vencimiento": {
            "periodo_martes": m1,
            "periodo_domingo": m2,
            "lunes_cierre": lunes_iso,
        },
        "nota_cartera": "Equivalente a menú Lunes de cierre: Bucket_Morosidad_Real + corte por columna getCorteActual (fallback COALESCE Lunes_* + Dias_mora).",
        "total_creditos_cartera_filtro_pv": meta,
        "agregado_nacimiento_x_mora_lunes": pares_orden,
        "resumen": {
            "current_al_corte_mas_1_7": current_mas_rec,
            "pendientes_primeros_pagos": pend_pp,
            "nac_dist": nac_dist,
            "bar_current_vs_1_7_pct": {"p_current": pC, "p_1_7": p7, "mostrar": mostrar},
        },
    }


def _enriquecer_respuesta_por_candidata(r: dict[str, Any], cand: dict[str, Any]) -> None:
    r["ultima_fecha_hora_insert_muestra"] = cand.get("ultimo_insert")
    r["ayuda_lectura"] = (
        "Cada fila: créditos por cruce nacimiento × corte lunes; columna 3 = mejora al corte (misma regla que PHP)."
    )
    if not INCLUIR_CONTEO_ETIQUETA:
        r["registros_todas_filas_por_semana_histo"] = (
            "omitido (lento; PP_HISTO_INCLUIR_CONTEO_ETIQUETA=1 para ver total ~69k por etiqueta)"
        )


def build_primeros_pagos_payload() -> dict[str, Any]:
    candidatas = agregar_candidatas()
    if not candidatas:
        return {
            "fuente": TABLA_HISTORICO_PRIMEROS_PAGOS,
            "enfoque": "Primeros pagos — Histórico (equivalente PHP) — sin filas a granel",
            "semanas": [],
            "mensaje": f"No se encontraron hasta {NUM_SEMANAS} semanas parseables (Semana NN-AAAA) en el lookback de {LISTA_SEMANAS_LOOKBACK_DIAS} días.",
        }
    labels = [c["semana"] for c in candidatas]
    n_workers = min(PP_HISTO_WORKERS, len(candidatas), 8)

    if n_workers == 1:
        out_semanas = []
        with closing(get_conn()) as con:
            with con.cursor() as cur0:
                col = columna_nacimiento_existe(cur0)
            for cand in candidatas:
                r = resumen_una_semana(con, col, cand)
                _enriquecer_respuesta_por_candidata(r, cand)
                out_semanas.append(r)
    else:
        with closing(get_conn()) as con0:
            with con0.cursor() as cur0:
                col = columna_nacimiento_existe(cur0)

        def _resumen_por_candidata(cand: dict[str, Any]) -> dict[str, Any]:
            with closing(get_conn()) as con:
                r = resumen_una_semana(con, col, cand)
            _enriquecer_respuesta_por_candidata(r, cand)
            return r

        with ThreadPoolExecutor(max_workers=n_workers) as ex:
            out_semanas = list(ex.map(_resumen_por_candidata, candidatas))

    if INCLUIR_CONTEO_ETIQUETA and labels:
        with closing(get_conn()) as csum:
            with csum.cursor() as curf:
                filas_por = contar_filas_histo_por_semanas(curf, labels)
        for r, cand in zip(out_semanas, candidatas):
            r["registros_todas_filas_por_semana_histo"] = int(
                filas_por.get(cand["semana"], 0)
            )

    return {
        "fuente": TABLA_HISTORICO_PRIMEROS_PAGOS,
        "enfoque": "Primeros pagos — Histórico (equivalente a PrimerosPagosHistoricoSegundometro): solo conteos y GROUP BY, filtro Fecha_primer_vencimiento",
        "parametros": {
            "lookback_dias_fecha_hora_insert": LISTA_SEMANAS_LOOKBACK_DIAS,
            "candidatas_max_grupos_distinct_semana": CANDIDATAS_GROUP_LIMIT,
            "num_semanas": NUM_SEMANAS,
            "incluir_conteo_todas_filas_etiqueta": INCLUIR_CONTEO_ETIQUETA,
            "workers_semanas_paralelo": min(
                max(1, PP_HISTO_WORKERS), len(candidatas) if candidatas else 1
            ),
        },
        "semanas": out_semanas,
    }
