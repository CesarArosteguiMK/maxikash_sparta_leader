"""
REPORTE GASTOS DE COBRANZA + SALDO A FAVOR
==========================================
Columnas finales:
  ID CREDITO | NOMBRE CLIENTE | STATUS CREDITO | CUOTA SEMANAL |
  DEUDA GC PENDIENTE | SALDO A FAVOR DEL CLIENTE | SALDO APLICABLE A GC |
  fecha_ultimo_abono_efectivo (Fecha_ultimo_pago_efectivo de MySQL, fecha + nombre del día) |
  COMENTARIOS | ERROR

Flujo:
  1. MySQL trae créditos elegibles (gastos_cobranza + tbl_segundometro_semana, Dias_mora=0,
     sin despacho, valor_real > 1) y Fecha_ultimo_pago_efectivo = **fecha de negocio** (ayer CDMX).
  2. Se consulta la lista negra (cobranza_gc_verificacion_semana) en una query aparte.
  3. En Python se descartan los id_credito que ya están en lista negra para esa semana
     (inicio_semana = martes del periodo, s2_exitoso = 1).
  4. Los que quedan van a S2 → cálculo → Excel.
  5. El Excel se genera siempre (aunque queden 0 registros después de los filtros).

Salida: reporte/reporte_cobranza_DD-MM-YYYY.xlsx (fecha calendario CDMX al generar; guiones en lugar de /).

Reintentos: tras la primera pasada S2 en paralelo, se pueden ejecutar pasadas extra solo sobre filas con
columna ERROR (timeout / sin respuesta S2). Un solo Excel final. Variables de entorno opcionales:
  REPORTE_COBRANZA_PASADAS_REINTENTO (default 1, 0 = desactivar)
  REPORTE_COBRANZA_REINTENTO_MAX_WORKERS (default 16)
  REPORTE_COBRANZA_REINTENTO_PAUSA_S (default 1.5 segundos entre anuncio y reintento)

SALDO A FAVOR: lunes de la semana calendario (lun–dom) de la fecha de negocio.

Fecha de negocio: siempre **ayer** respecto al calendario **America/Mexico_City** (hora CDMX real),
para alinear con ejecución programada el día previo al día operativo a las 08:00 CDMX.
"""

from __future__ import annotations

import json
import logging
import os
import re
import threading
import time
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import date, datetime, timedelta
from zoneinfo import ZoneInfo

import pymysql
import requests
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter

_SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
# Carpeta compartida con el agente Node: excel semanales y log de ejecuciones Python.
REPORTE_DIR = os.path.normpath(os.path.join(_SCRIPT_DIR, "..", "reporte"))
os.makedirs(REPORTE_DIR, exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler(
            os.path.join(REPORTE_DIR, "reporte_cobranza_py.log"),
            encoding="utf-8",
        ),
    ],
)
log = logging.getLogger(__name__)

# ─────────────────────────────────────────────
# CONFIGURACION
# ─────────────────────────────────────────────
DB_CONFIG = {
    "host":            "__SPARTA_HOST_REDACTED__",
    "port":            3306,
    "user":            "__SPARTA_SECRET_REDACTED__",
    "password":        "__SPARTA_PASSWORD_REDACTED__",
    "database":        "__SPARTA_SECRET_REDACTED__",
    "charset":         "utf8mb4",
    "connect_timeout": 30,
    "read_timeout":    180,
    "write_timeout":   180,
    "cursorclass":     pymysql.cursors.DictCursor,
}

S2_URL     = "https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta"
S2_TOKEN   = "__SPARTA_TOKEN_REDACTED__"
S2_HEADERS = {"Content-Type": "application/json", "Token": S2_TOKEN}

TZ_CDMX = ZoneInfo("America/Mexico_City")

# Google Chat (espacio Gastos Cobranza). Sobreescribir con GASTOS_COBRANZA_GCHAT_URL en el entorno.
GCHAT_URL = os.environ.get(
    "GASTOS_COBRANZA_GCHAT_URL",
    "__SPARTA_WEBHOOK_REDACTED__",
)

FECHA_CORTE_GASTOS = date(2026, 1, 28)
CONCEPTO_GC        = "NOTA DE DE CARGO GASTOS DE COBRANZA"

MAX_WORKERS    = 40
MAX_REINTENTOS = 3
TIMEOUT_S2     = 45
LOG_CADA       = 500


def _env_int(name: str, default: int) -> int:
    try:
        return max(0, int(str(os.environ.get(name, str(default))).strip()))
    except ValueError:
        return default


def _env_float(name: str, default: float) -> float:
    try:
        return float(str(os.environ.get(name, str(default))).strip())
    except ValueError:
        return default


# Rondas extra solo para filas con ERROR de S2 (timeout, sin respuesta, etc.). 0 = desactivado.
# Una sola Excel final con todo mezclado (aciertos + errores que sigan fallando).
PASADAS_REINTENTO_ERRORES = _env_int("REPORTE_COBRANZA_PASADAS_REINTENTO", 1)
REINTENTO_MAX_WORKERS = max(1, _env_int("REPORTE_COBRANZA_REINTENTO_MAX_WORKERS", 16))
REINTENTO_PAUSA_S = _env_float("REPORTE_COBRANZA_REINTENTO_PAUSA_S", 1.5)
COMENTARIO_CUOTA_CUBIERTA = "CUOTA SIGUIENTE CUBIERTA - NO APLICAR"
COMENTARIO_APLICAR        = "APLICAR"
COMENTARIO_SIN_REGLA      = "Sin Regla"

# Días en que se usa APLICAR: martes(1) a viernes(4). Sábado(5)-domingo(6)-lunes(0) → CUOTA CUBIERTA.
_DIAS_APLICAR = {1, 2, 3, 4}

# Textos en columna COMENTARIOS (fácil de editar)
ETIQUETA_REGLA_GRIS = "Regla aplicable GC < 200 (gris)"
ETIQUETA_REGLA_HOMERO = "Regla de Homero"
ETIQUETA_REGLA_PORCENTAJE = "Regla porcentaje"
ETIQUETA_HOMERO_SALDO_FAVOR = "Homero saldo a favor (= 250)"

# ─────────────────────────────────────────────
# DESCARGO ESTATUS 3 — MERGE CON EL REPORTE
# ─────────────────────────────────────────────
# Directorio donde viven guia_descargo.json y descargo_estatus3.xlsx.
DESCARGO_ESTATUS3_DIR = os.path.normpath(os.path.join(REPORTE_DIR, "descargo_estatus3"))
_GUIA_DESCARGO_FILE   = "guia_descargo.json"
TABLE_DESCARGO        = "cobranza_gc_verificacion_semana"

# IDs excluidos del descargo (misma lista que descargo_cobranza_gc_estatus3.py).
EXCLUIR_ID_CREDITO_ESTATUS3: tuple = (
    943898, 1403455, 1454729, 1820460, 456213, 1592177, 1363701,
    1382512, 837692, 1502282, 1386771, 1031813, 1363075, 1020293,
)


def _leer_guia_descargo() -> dict | None:
    p = os.path.join(DESCARGO_ESTATUS3_DIR, _GUIA_DESCARGO_FILE)
    if not os.path.isfile(p):
        return None
    try:
        with open(p, encoding="utf-8-sig") as f:
            return json.load(f)
    except (OSError, json.JSONDecodeError) as e:
        log.warning("No se pudo leer la guía descargo: %s", e)
        return None


def _guardar_guia_descargo(
    *,
    ultimo_id_credito: int,
    ultimo_id_tabla: int,
    ultimo_registrado_en_cdmx: str,
) -> None:
    os.makedirs(DESCARGO_ESTATUS3_DIR, exist_ok=True)
    payload = {
        "ultimo_id_tabla": ultimo_id_tabla,
        "ultimo_registrado_en_cdmx": ultimo_registrado_en_cdmx,
        "ultimo_id_credito": ultimo_id_credito,
        "tabla": TABLE_DESCARGO,
        "orden_descargo": "registrado_en_cdmx ASC, id ASC",
        "guia_escrita_en_cdmx": datetime.now(TZ_CDMX).strftime("%Y-%m-%d %H:%M:%S"),
        "nota": (
            "Checkpoint incremental: la siguiente corrida pide filas con "
            "(registrado_en_cdmx > ultimo_registrado_en_cdmx) O "
            "(misma fecha y id de tabla > ultimo_id_tabla). "
            "Escrito por reporte_cobranza.py al fusionar el reporte unificado."
        ),
    }
    p = os.path.join(DESCARGO_ESTATUS3_DIR, _GUIA_DESCARGO_FILE)
    try:
        with open(p, "w", encoding="utf-8", newline="\n") as f:
            json.dump(payload, f, ensure_ascii=False, indent=2)
            f.write("\n")
        log.info("Guía descargo actualizada: %s", os.path.basename(p))
    except OSError as e:
        log.warning("No se pudo guardar la guía descargo: %s", e)


def _dt_descargo_a_str(val) -> str:
    """Datetime / str del descargo → cadena legible para la columna fecha_ultimo_abono_efectivo."""
    if val is None:
        return ""
    if isinstance(val, datetime):
        return val.strftime("%Y-%m-%d %H:%M:%S")
    if isinstance(val, date):
        return val.strftime("%Y-%m-%d 00:00:00")
    s = str(val).strip()
    if len(s) >= 10 and s[4] == "-":
        return s[:19].replace("T", " ")
    return s


def obtener_descargo_incremental() -> list[dict]:
    """
    Consulta incrementalmente cobranza_gc_verificacion_semana (estatus=3)
    usando la guia_descargo.json como checkpoint.
    Devuelve lista de dicts (pymysql DictCursor).
    """
    guia = _leer_guia_descargo()
    ids  = EXCLUIR_ID_CREDITO_ESTATUS3
    ph   = ",".join(["%s"] * len(ids))
    conds: list[str] = [
        "`estatus` = 3",
        f"`id_credito` NOT IN ({ph})",
    ]
    params: list = list(ids)

    if guia is not None:
        reg_raw = guia.get("ultimo_registrado_en_cdmx", "")
        pk_raw  = guia.get("ultimo_id_tabla")
        if reg_raw and pk_raw is not None:
            try:
                lr   = str(reg_raw).strip()
                pk_i = int(pk_raw)
                log.info("Descargo checkpoint: id_tabla=%s, fecha=%s", pk_i, lr)
                conds.append(
                    "(`registrado_en_cdmx` > %s OR "
                    "(`registrado_en_cdmx` = %s AND `id` > %s))"
                )
                params.extend([lr, lr, pk_i])
            except (TypeError, ValueError) as e:
                log.warning(
                    "Guía descargo con checkpoint inválido (%s): se traen todas las filas.", e
                )
        else:
            log.info("Guía descargo sin checkpoint válido: traer todas las filas estatus=3.")
    else:
        log.info("Sin guía descargo previa: traer todas las filas estatus=3.")

    sql = (
        f"SELECT * FROM `{TABLE_DESCARGO}` "
        f"WHERE {' AND '.join(conds)} "
        "ORDER BY `registrado_en_cdmx` ASC, `id` ASC"
    )
    conn = pymysql.connect(**DB_CONFIG)
    try:
        with conn.cursor() as cur:
            cur.execute(sql, tuple(params))
            rows = cur.fetchall()
        log.info("Descargo filas nuevas en esta corrida: %d", len(rows))
        return list(rows)
    finally:
        conn.close()


def _relleno_descargo_obs(reg: dict) -> "PatternFill | None":
    """
    Color de fila para registros del descargo que llevan texto de Observaciones.
    Solo se evalúan reglas numéricas (SALDO_APLICABLE_GC), ya que las reglas basadas
    en texto (APLICAR / CUOTA SIGUIENTE CUBIERTA) requieren el contexto del día de ejecución
    que no está disponible para estas filas históricas del descargo.
    """
    aplicable = _float_reg(reg, "SALDO_APLICABLE_GC")
    if aplicable < 200:
        return FILL_ROW_GRIS
    return FILL_ROW_NARANJA


def merge_descargo_en_reporte(resultados: list[dict], rows_descargo: list[dict]) -> list[dict]:
    """
    Agrega al listado del reporte las filas del descargo que no estén ya presentes.

    Reglas de negocio:
    · id_credito del descargo YA en resultados  → saltar (no modificar la fila del reporte).
    · id_credito del descargo NO en resultados  → insertar nueva fila con campos mapeados:
        monto_aplicar       → SALDO_APLICABLE_GC
        tipo_reporte        → STATUS_CREDITO
        registrado_en_cdmx  → FECHA_ULTIMO_ABONO_EFECTIVO
        id_credito          → ID_CREDITO
        nombre              → NOMBRE_CLIENTE
        mensaje (Observaciones):
          · Con texto → COMENTARIOS = ese texto; color = reglas numéricas (_relleno_descargo_obs).
          · Vacío     → COMENTARIOS calculado por armar_comentarios_fila; color = relleno_fila_por_reglas.
      Columnas del descargo sin equivalente en el reporte se descartan.
      Columnas del reporte sin datos del descargo quedan en 0 / cadena vacía.
    """
    ids_en_reporte: set[str] = {str(r.get("ID_CREDITO", "")) for r in resultados}
    filas_nuevas  = 0
    filas_saltadas = 0

    for row in rows_descargo:
        id_c = str(row.get("id_credito", "") or "")
        if id_c in ids_en_reporte:
            filas_saltadas += 1
            continue

        obs = str(row.get("mensaje", "") or "").strip()

        reg: dict = {
            "ID_CREDITO":                   row.get("id_credito"),
            "NOMBRE_CLIENTE":               str(row.get("nombre", "") or ""),
            "STATUS_CREDITO":               str(row.get("tipo_reporte", "") or ""),
            "CUOTA_SEMANAL":                0.0,
            "DEUDA_GC":                     0.0,
            "SALDO_A_FAVOR":                0.0,
            "SALDO_APLICABLE_GC":           0.0,
            "FECHA_ULTIMO_ABONO_EFECTIVO":  _dt_descargo_a_str(row.get("registrado_en_cdmx")),
            "COMENTARIOS":                  "",
            "ERROR":                        "",
        }

        monto_raw = row.get("monto_aplicar")
        if monto_raw is not None and monto_raw != "":
            try:
                reg["SALDO_APLICABLE_GC"] = float(monto_raw)
            except (TypeError, ValueError):
                pass

        if obs:
            reg["COMENTARIOS"]    = obs
            reg["_fill_override"] = _relleno_descargo_obs(reg)
        else:
            reg["COMENTARIOS"] = armar_comentarios_fila("", reg)

        resultados.append(reg)
        ids_en_reporte.add(id_c)
        filas_nuevas += 1

    log.info(
        "Merge descargo: %d fila(s) nueva(s) insertada(s), %d saltada(s) (id ya en reporte).",
        filas_nuevas,
        filas_saltadas,
    )
    return resultados


# ─── QUERY PRINCIPAL: dinámica — filtro fecha = fecha de negocio (ayer CDMX el día de la corrida)

def sql_query_ids_principal(fecha_filtro: date) -> str:
    """fecha_filtro debe coincidir con DATE(Fecha_ultimo_pago_efectivo) en BD."""
    fs = fecha_filtro.isoformat()
    return f"""
SELECT
    g.`Id_credito`  AS id_credito,
    COUNT(*)         AS cuotas,
    MAX(s.`Fecha_ultimo_pago_efectivo`) AS fecha_ultimo_pago_efectivo,
    ROUND(
        SUM(
            CASE
                WHEN g.`estatus_pago` = 2              THEN 0
                WHEN IFNULL(g.`condonado`, 0) = 1      THEN 0
                WHEN g.`estatus_pago` = 1
                    THEN (g.`monto_valor` - IFNULL(g.`condonacion_parcial_monto`, 0))
                       - IFNULL(g.`monto_parcial_pagado`, 0)
                ELSE g.`monto_valor` - IFNULL(g.`condonacion_parcial_monto`, 0)
            END
        ), 2
    ) AS valor_real
FROM `gastos_cobranza` AS g
INNER JOIN `tbl_segundometro_semana` AS s
    ON s.`Id_credito` = g.`Id_credito`
   AND s.`Dias_mora` = 0
WHERE g.`condonado` = 0
  AND g.`estatus_pago` = 0
  AND s.`Fecha_ultimo_pago_efectivo` IS NOT NULL
  AND DATE(s.`Fecha_ultimo_pago_efectivo`) = '{fs}'
  AND NOT EXISTS (
      SELECT 1
      FROM `__SPARTA_SECRET_REDACTED__`.`asigna_creditos_despacho` AS d
      WHERE d.`id_credito` = g.`Id_credito`
        AND d.`estatus`    = '1'
  )
GROUP BY g.`Id_credito`
HAVING valor_real > 1
ORDER BY g.`Id_credito`;
"""


def fecha_negocio_y_reloj_cdmx() -> tuple[date, datetime]:
    """
    Reloj CDMX real y fecha de negocio = calendario CDMX **ayer**.
    La corrida programada (p. ej. día previo al operativo, 08:00 CDMX) usa ese día para S2 y filtros.
    """
    ahora_cdmx = datetime.now(TZ_CDMX)
    hoy_cal = ahora_cdmx.date()
    ayer = hoy_cal - timedelta(days=1)
    return ayer, ahora_cdmx


def notificar_google_chat(texto: str) -> None:
    """Aviso al webhook Google Chat; no interrumpe el reporte si falla."""
    u = (GCHAT_URL or "").strip()
    if not u.startswith("http"):
        return
    try:
        r = requests.post(
            u,
            json={"text": texto[:8000]},
            headers={"Content-Type": "application/json; charset=UTF-8"},
            timeout=20,
        )
        if r.status_code >= 400:
            log.warning("Google Chat HTTP %s: %s", r.status_code, (r.text or "")[:200])
    except Exception as e:
        log.warning("Google Chat error: %s", e)


# ─── QUERY LISTA NEGRA: IDs ya verificados esta semana ───────────────────────
QUERY_LISTA_NEGRA = """
SELECT DISTINCT `id_credito`
FROM `cobranza_gc_verificacion_semana`
WHERE `inicio_semana` = %s
  AND `s2_exitoso`    = 1;
"""


def inicio_semana_operativa_martes(d: date) -> date:
    """Martes que abre el periodo operativo mar–dom."""
    return d - timedelta(days=(d.weekday() - 1) % 7)


# Python weekday(): mismo orden que MySQL WEEKDAY (lunes=0 … domingo=6)
_NOMBRE_DIA_SEMANA_ES = (
    "lunes", "martes", "miércoles", "jueves", "viernes", "sábado", "domingo",
)


def _parse_fecha_desde_mysql(raw) -> date | None:
    if raw is None:
        return None
    if isinstance(raw, datetime):
        return raw.date()
    if isinstance(raw, date):
        return raw
    s = str(raw).strip()
    if not s:
        return None
    try:
        return date.fromisoformat(s[:10])
    except ValueError:
        return None


def fecha_abono_efectivo_para_excel(row: dict, hoy: date) -> str:
    """
    Valor de columna Excel: misma fecha que MAX(Fecha_ultimo_pago_efectivo) del SELECT MySQL
    (tbl_segundometro_semana), con nombre del día en español. Antes solo se rellenaba si el pago
    era lunes, lo que dejaba la celda vacía para el resto de días.
    """
    _ = hoy  # firma conservada (procesar_registro / filas de error)
    raw = row.get("fecha_ultimo_pago_efectivo")
    d_pago = _parse_fecha_desde_mysql(raw)
    if d_pago is None:
        return ""
    nombre = _NOMBRE_DIA_SEMANA_ES[d_pago.weekday()]
    return f"{d_pago.isoformat()} — {nombre}"


def obtener_ids_mysql(fecha_filtro_pago_efectivo: date) -> list[dict]:
    """Trae TODOS los créditos elegibles desde MySQL (sin filtro lista negra)."""
    log.info("Conectando a MySQL para créditos (filtro fecha pago efectivo=%s)...", fecha_filtro_pago_efectivo)
    conn = pymysql.connect(**DB_CONFIG)
    try:
        with conn.cursor() as cur:
            cur.execute(sql_query_ids_principal(fecha_filtro_pago_efectivo))
            rows = cur.fetchall()
        log.info(f"  -> {len(rows):,} registros en MySQL")
        return list(rows)
    finally:
        conn.close()


def obtener_lista_negra(inicio_semana: date) -> set[str]:
    """Devuelve el conjunto de id_credito (como string) que ya están en lista negra esta semana."""
    log.info(f"Consultando lista negra (inicio_semana={inicio_semana})...")
    conn = pymysql.connect(**DB_CONFIG)
    try:
        with conn.cursor() as cur:
            cur.execute(QUERY_LISTA_NEGRA, (inicio_semana,))
            rows = cur.fetchall()
        ids = {str(r["id_credito"]) for r in rows}
        log.info(f"  -> {len(ids):,} IDs en lista negra")
        return ids
    finally:
        conn.close()


# ─────────────────────────────────────────────
# SESSION POOL
# ─────────────────────────────────────────────
_thread_local = threading.local()


def get_session():
    if not hasattr(_thread_local, "session"):
        s = requests.Session()
        s.headers.update(S2_HEADERS)
        s.mount("https://", requests.adapters.HTTPAdapter(
            pool_connections=1, pool_maxsize=1, max_retries=0))
        _thread_local.session = s
    return _thread_local.session


def consultar_s2(id_credito, fecha_corte):
    payload = {"idCredito": id_credito, "fechaCorte": fecha_corte}
    for intento in range(1, MAX_REINTENTOS + 1):
        try:
            resp = get_session().post(S2_URL, json=payload, timeout=TIMEOUT_S2)
            resp.raise_for_status()
            data = resp.json()
            if data.get("http") == 200:
                return data
            return None
        except requests.exceptions.Timeout:
            log.warning(f"[{id_credito}] Timeout intento {intento}")
        except requests.exceptions.ConnectionError:
            log.warning(f"[{id_credito}] ConexionError intento {intento}")
        except Exception as e:
            log.error(f"[{id_credito}] Error: {e}")
            return None
        if intento < MAX_REINTENTOS:
            time.sleep(2.0 * intento)
    return None


def calcular_gc(ec, fecha_limite):
    total_gc = 0.0
    for nota in (ec.get("datosNotasCargos") or []):
        if nota.get("concepto") != CONCEPTO_GC:
            continue
        try:
            fm = date.fromisoformat(str(nota["fechaMovimiento"])[:10])
        except Exception:
            continue
        if fm > fecha_limite:
            total_gc += float(nota.get("extemporaneos") or 0)

    if total_gc <= 0:
        return 0.0

    pagado = sum(float(p.get("extemporaneos") or 0)
                 for p in (ec.get("datosPagos") or []))

    return max(round(total_gc - pagado, 2), 0.0)


def calcular_saldo_a_favor(ec, lunes: date):
    saldos = ec.get("datosSaldos") or {}
    vencido = float(saldos.get("saldoTotalVencido") or 0)
    if vencido > 1:
        return 0.0

    lunes_str = str(lunes)

    cargos = ec.get("datosCargos") or []
    if not isinstance(cargos, list) or not cargos:
        return 0.0

    cargos_sorted = sorted(
        cargos,
        key=lambda c: int(c.get("idCargo") or 0)
    )

    monto_por_idcargo = {}
    es_anticipo_por_idcargo = {}

    target_idcargo = None
    target_monto = 0.0

    for cargo in cargos_sorted:
        idc = int(cargo.get("idCargo") or 0)
        if idc <= 0:
            continue

        concepto_upper = str(cargo.get("concepto") or "").upper()
        monto_cargo = float(cargo.get("monto") or 0)
        monto_por_idcargo[idc] = monto_cargo
        es_anticipo_por_idcargo[idc] = ("ANTICIPO A CAPITAL" in concepto_upper)

        fv = str(cargo.get("fechaVencimiento") or "")[:10]
        if fv == lunes_str and ("CUOTA SEMANAL" in concepto_upper) and target_idcargo is None:
            target_idcargo = idc
            target_monto = monto_cargo

    if target_idcargo is None or target_monto <= 0:
        return 0.0

    pagos_ordenados = sorted(
        ec.get("datosPagos") or [],
        key=lambda p: (str(p.get("fechaRegistro") or ""), p.get("idPago", 0))
    )

    abonado_por_idcargo = {}
    tol = 0.02

    for pago in pagos_ordenados:
        nums = [int(x) for x in re.findall(r"\d+", str(pago.get("numeroCuotaSemanal") or ""))]
        if not nums:
            continue

        monto_real = float(pago.get("montoPago") or 0) - float(pago.get("extemporaneos") or 0)
        monto_real = round(monto_real, 2)
        if monto_real <= 0:
            continue

        pago_real_total = monto_real
        restante = monto_real

        for idc in nums:
            if restante <= 0:
                break
            if idc not in monto_por_idcargo:
                continue

            monto_cargo = monto_por_idcargo[idc]
            ya = float(abonado_por_idcargo.get(idc, 0.0) or 0.0)
            falta = round(monto_cargo - ya, 2)
            if falta <= 0:
                continue

            es_sobrante_remaining = (restante + tol) < pago_real_total
            if es_anticipo_por_idcargo.get(idc, False) and es_sobrante_remaining:
                continue

            aplicar = min(restante, falta)
            aplicar = round(aplicar, 2)
            if aplicar <= 0:
                continue

            abonado_por_idcargo[idc] = round(ya + aplicar, 2)
            restante = round(restante - aplicar, 2)

        if restante > 0:
            sig = max(nums) + 1
            while es_anticipo_por_idcargo.get(sig, False):
                sig += 1
            abonado_por_idcargo[sig] = round(float(abonado_por_idcargo.get(sig, 0.0) or 0.0) + restante, 2)

    abonado_actual = float(abonado_por_idcargo.get(target_idcargo, 0.0) or 0.0)
    if abonado_actual + 0.009 < target_monto:
        return 0.0

    total_sf = 0.0
    excedente_actual = round(abonado_actual - target_monto, 2)
    if excedente_actual > 0:
        total_sf += excedente_actual

    for idc, ab in abonado_por_idcargo.items():
        if int(idc) > int(target_idcargo):
            total_sf += float(ab or 0.0)

    return round(total_sf, 2)


def procesar_registro(row, fecha_corte_str, lunes: date, hoy: date):
    """Igual que reporte_cobranza (Downloads): dict con datos o error, None si se excluye."""
    id_credito = row["id_credito"]
    base = {
        "ID_CREDITO": id_credito,
        "NOMBRE_CLIENTE": "",
        "STATUS_CREDITO": "",
        "CUOTA_SEMANAL": 0.0,
        "DEUDA_GC": float(row["valor_real"] or 0),
        "SALDO_A_FAVOR": 0.0,
        "SALDO_APLICABLE_GC": 0.0,
        "FECHA_ULTIMO_ABONO_EFECTIVO": fecha_abono_efectivo_para_excel(row, hoy),
        "COMENTARIOS": "",
        "ERROR": "",
    }

    data = consultar_s2(id_credito, fecha_corte_str)
    if not data:
        base["ERROR"] = "Sin respuesta S2 tras reintentos"
        return base

    ec      = data.get("estadoCuenta", {}) or {}
    cliente = ec.get("datosCliente",   {}) or {}
    status  = ec.get("statusCredito",  "") or ""

    base["NOMBRE_CLIENTE"] = cliente.get("nombreCliente", "")
    base["STATUS_CREDITO"] = status
    base["CUOTA_SEMANAL"]  = float(ec.get("cuota") or 0)

    if str(status).strip().lower() != "vigente":
        return None

    gc_desde_s2 = calcular_gc(ec, FECHA_CORTE_GASTOS)

    if gc_desde_s2 > 1:
        deuda_gc = gc_desde_s2
    else:
        deuda_gc = float(row.get("valor_real") or 0)

    if deuda_gc <= 1:
        return None

    sf = calcular_saldo_a_favor(ec, lunes)
    if sf <= 0:
        return None

    saldo_aplicable = round(min(sf, deuda_gc), 2)
    # sf > 0 garantizado (filtramos arriba). Etiqueta según día de ejecución:
    # Martes–Viernes → APLICAR (verde); Sábado–Lunes → CUOTA SIGUIENTE CUBIERTA (rojo).
    comentario = (
        COMENTARIO_APLICAR
        if hoy.weekday() in _DIAS_APLICAR
        else COMENTARIO_CUOTA_CUBIERTA
    )

    base.update({
        "DEUDA_GC": deuda_gc,
        "SALDO_A_FAVOR": sf,
        "SALDO_APLICABLE_GC": saldo_aplicable,
    })
    base["COMENTARIOS"] = armar_comentarios_fila(comentario, base)
    return base


COLUMNAS = [
    ("ID_CREDITO", "ID CREDITO"), ("NOMBRE_CLIENTE", "NOMBRE CLIENTE"),
    ("STATUS_CREDITO", "STATUS CREDITO"), ("CUOTA_SEMANAL", "CUOTA SEMANAL"),
    ("DEUDA_GC", "DEUDA GC PENDIENTE"), ("SALDO_A_FAVOR", "SALDO A FAVOR DEL CLIENTE"),
    ("SALDO_APLICABLE_GC", "SALDO APLICABLE A GC"),
    ("FECHA_ULTIMO_ABONO_EFECTIVO", "fecha_ultimo_abono_efectivo"),
    ("COMENTARIOS", "COMENTARIOS"), ("ERROR", "ERROR"),
]
ANCHOS = {
    "ID_CREDITO": 14, "NOMBRE_CLIENTE": 34, "STATUS_CREDITO": 14,
    "CUOTA_SEMANAL": 14, "DEUDA_GC": 20, "SALDO_A_FAVOR": 24,
    "SALDO_APLICABLE_GC": 22, "FECHA_ULTIMO_ABONO_EFECTIVO": 32,
    "COMENTARIOS": 52, "ERROR": 30,
}
MONEY_COLS = {"CUOTA_SEMANAL", "DEUDA_GC", "SALDO_A_FAVOR", "SALDO_APLICABLE_GC"}
SF_COLS    = {"SALDO_A_FAVOR", "SALDO_APLICABLE_GC"}
DEUDA_COLS = {"DEUDA_GC"}

FILL_RED   = PatternFill("solid", start_color="FFCDD2")
FILL_SF    = PatternFill("solid", start_color="DDEBF7")
FILL_DEUDA = PatternFill("solid", start_color="FFEBEE")
FILL_ALT   = PatternFill("solid", start_color="F2F2F2")

# Reglas de fila (orden estricto; la primera que aplique pinta toda la fila)
FILL_ROW_VERDE  = PatternFill("solid", start_color="C8E6C9")     # verde: APLICAR (mar–vie)
FILL_ROW_NARANJA = PatternFill("solid", start_color="FFE0B2")    # naranja: Sin Regla
FILL_ROW_GRIS   = PatternFill("solid", start_color="BDBDBD")     # gris: aplicable < 200
FILL_ROW_HOMERO = PatternFill("solid", start_color="B3E5FC")     # azul cielo: deuda y SF en [200,300]
FILL_ROW_PCT    = PatternFill("solid", start_color="E1BEE7")     # morado claro: SF/cuota en 0%–25%
FILL_ROW_SF250  = PatternFill("solid", start_color="FFF9C4")     # amarillo claro: SF == 250


def _float_reg(reg: dict, key: str) -> float:
    try:
        return float(reg.get(key) or 0)
    except (TypeError, ValueError):
        return 0.0


def etiquetas_reglas_desde_reg(reg: dict) -> list[str]:
    """Etiquetas de negocio para COMENTARIOS (pueden combinarse varias)."""
    out: list[str] = []
    aplicable = _float_reg(reg, "SALDO_APLICABLE_GC")
    if aplicable < 200:
        out.append(ETIQUETA_REGLA_GRIS)

    deuda = _float_reg(reg, "DEUDA_GC")
    sf = _float_reg(reg, "SALDO_A_FAVOR")
    if 200 <= deuda <= 300 and 200 <= sf <= 300:
        out.append(ETIQUETA_REGLA_HOMERO)

    cuota = _float_reg(reg, "CUOTA_SEMANAL")
    if cuota > 1e-9:
        pct = round(100.0 * sf / cuota, 2)
        if 0 <= pct <= 25:
            out.append(ETIQUETA_REGLA_PORCENTAJE)

    if round(sf, 2) == 250.0:
        out.append(ETIQUETA_HOMERO_SALDO_FAVOR)

    return out


def armar_comentarios_fila(comentario_cuota: str, reg: dict) -> str:
    partes: list[str] = []
    if comentario_cuota:
        partes.append(comentario_cuota)
    partes.extend(etiquetas_reglas_desde_reg(reg))
    if not partes:
        partes.append(COMENTARIO_SIN_REGLA)
    return " | ".join(partes)


def relleno_fila_por_reglas(reg: dict) -> PatternFill | None:
    """
    Primera regla que cumpla gana (no se combinan).
    0) APLICAR (mar–vie)                    → verde
    1) CUOTA SIGUIENTE CUBIERTA (sáb–lun)  → rojo
    2) SALDO_APLICABLE_GC < 200            → gris
    3) Regla de Homero: deuda y SF en [200,300] → azul
    4) Regla porcentaje: SF/cuota en [0,25]%    → morado
    5) Homero SF == 250                         → amarillo
    6) Sin Regla (fallback)                     → naranja
    """
    com = str(reg.get("COMENTARIOS") or "")

    if COMENTARIO_APLICAR in com:
        return FILL_ROW_VERDE

    if COMENTARIO_CUOTA_CUBIERTA in com:
        return FILL_RED

    aplicable = _float_reg(reg, "SALDO_APLICABLE_GC")
    if aplicable < 200:
        return FILL_ROW_GRIS

    deuda = _float_reg(reg, "DEUDA_GC")
    sf = _float_reg(reg, "SALDO_A_FAVOR")
    if 200 <= deuda <= 300 and 200 <= sf <= 300:
        return FILL_ROW_HOMERO

    cuota = _float_reg(reg, "CUOTA_SEMANAL")
    if cuota > 1e-9:
        pct = round(100.0 * sf / cuota, 2)
        if 0 <= pct <= 25:
            return FILL_ROW_PCT

    if round(sf, 2) == 250.0:
        return FILL_ROW_SF250

    if COMENTARIO_SIN_REGLA in com:
        return FILL_ROW_NARANJA

    return None


def generar_excel(registros, lunes: date, hoy: date, ruta_salida: str):
    log.info(f"Construyendo Excel ({len(registros):,} filas)...")
    wb = Workbook()
    ws = wb.active
    ws.title = "Reporte Cobranza"

    thin   = Side(style="thin", color="CCCCCC")
    border = Border(left=thin, right=thin, top=thin, bottom=thin)
    ncols  = len(COLUMNAS)

    ws.merge_cells(f"A1:{get_column_letter(ncols)}1")
    ws["A1"] = (
        f"Reporte Gastos de Cobranza  |  Generado: {hoy}  |  "
        f"Lunes semana: {lunes}  |  GC desde: {FECHA_CORTE_GASTOS}  |  "
        f"Total registros: {len(registros):,}"
    )
    ws["A1"].font      = Font(name="Arial", size=9, italic=True, color="595959")
    ws["A1"].alignment = Alignment(horizontal="center")
    ws.row_dimensions[1].height = 18

    for ci, (key, label) in enumerate(COLUMNAS, 1):
        c = ws.cell(row=2, column=ci, value=label)
        c.font      = Font(name="Arial", bold=True, color="FFFFFF", size=10)
        c.alignment = Alignment(horizontal="center", vertical="center", wrap_text=True)
        c.border    = border
        if key in SF_COLS:
            c.fill = PatternFill("solid", start_color="1565C0")
        elif key in DEUDA_COLS:
            c.fill = PatternFill("solid", start_color="B71C1C")
        elif key == "COMENTARIOS":
            c.fill = PatternFill("solid", start_color="E65100")
        else:
            c.fill = PatternFill("solid", start_color="1F3864")
    ws.row_dimensions[2].height = 30

    f_data = Font(name="Arial", size=9)
    for ri, reg in enumerate(registros, 3):
        alt = (ri % 2 == 0)
        row_fill = reg.get("_fill_override")
        if row_fill is None:
            row_fill = relleno_fila_por_reglas(reg)
        for ci, (key, _) in enumerate(COLUMNAS, 1):
            val = reg.get(key, "")
            c   = ws.cell(row=ri, column=ci, value=val)
            c.font      = f_data
            c.border    = border
            c.alignment = Alignment(horizontal="center", vertical="center")
            if row_fill is not None:
                c.fill = row_fill
            elif key in SF_COLS:
                c.fill = FILL_SF
            elif key in DEUDA_COLS:
                c.fill = FILL_DEUDA
            elif alt:
                c.fill = FILL_ALT
            if key == "COMENTARIOS":
                c.alignment = Alignment(horizontal="center", vertical="center", wrap_text=True)
            if key in MONEY_COLS and isinstance(val, (int, float)):
                c.number_format = "$#,##0.00"
            elif key == "ID_CREDITO" and isinstance(val, (int, float)):
                c.number_format = "#,##0"

    for ci, (key, _) in enumerate(COLUMNAS, 1):
        ws.column_dimensions[get_column_letter(ci)].width = ANCHOS.get(key, 14)
    ws.freeze_panes = "A3"
    ws.auto_filter.ref = f"A2:{get_column_letter(ncols)}2"

    wb.save(ruta_salida)
    log.info(f"Excel guardado: {ruta_salida}")


def main() -> None:
    t0 = time.time()
    hoy, ahora_cdmx = fecha_negocio_y_reloj_cdmx()
    lunes = hoy - timedelta(days=hoy.weekday())
    inicio_semana = inicio_semana_operativa_martes(hoy)
    fecha_corte = str(hoy)

    os.makedirs(REPORTE_DIR, exist_ok=True)
    # Nombre: día de generación en CDMX, formato tipo 31-03-2026 (DD-MM-AAAA; sin / por rutas Windows).
    fecha_generacion_cdmx = datetime.now(TZ_CDMX).date()
    nombre_excel = f"reporte_cobranza_{fecha_generacion_cdmx.strftime('%d-%m-%Y')}.xlsx"
    ruta_excel = os.path.join(REPORTE_DIR, nombre_excel)

    # Si el reporte de hoy ya fue generado, avisar y salir sin regenerar.
    if os.path.isfile(ruta_excel):
        aviso = (
            f"\n{'=' * 65}\n"
            f"  AVISO: El reporte del día ya existe\n"
            f"  Archivo : {nombre_excel}\n"
            f"  Este reporte ya fue generado hoy. Por favor descárguelo\n"
            f"  directamente desde la tabla de reportes y úselo.\n"
            f"  Si necesita regenerarlo, elimine el archivo existente\n"
            f"  y vuelva a ejecutar el agente.\n"
            f"{'=' * 65}"
        )
        print(aviso, flush=True)
        log.warning("Reporte del día ya existe: %s — proceso terminado sin regenerar.", ruta_excel)
        return

    log.info("=" * 65)
    log.info(f"  Reloj CDMX (inicio) : {ahora_cdmx.isoformat()}")
    log.info(f"  Fecha negocio (ayer): {hoy}")
    log.info("  Calendario CDMX al arrancar el proceso : %s", ahora_cdmx.date())
    log.info(f"  Lunes semana negocio : {lunes}")
    log.info(f"  Inicio semana negra : {inicio_semana}  (martes del periodo)")
    log.info(f"  fechaCorte -> S2    : {fecha_corte}")
    log.info(f"  Filtro ultimo pago  : DATE(...) = {hoy}")
    log.info(f"  Excel guardado en   : {os.path.abspath(ruta_excel)}")
    log.info(f"  GC desde (limite)   : {FECHA_CORTE_GASTOS}")
    log.info(f"  Hilos paralelos     : {MAX_WORKERS}")
    log.info("=" * 65)

    notificar_google_chat(
        f"📊 **Reporte Cobranza** — inicio\n"
        f"Hora CDMX: `{ahora_cdmx.strftime('%Y-%m-%d %H:%M')}`\n"
        f"Fecha de negocio (ayer CDMX): `{hoy}`\n"
        f"→ MySQL filt. último pago efectivo = `{hoy}` · S2 `fechaCorte` = `{fecha_corte}`"
    )

    # PASO 1: traer TODOS los elegibles desde MySQL
    todos_rows = obtener_ids_mysql(hoy)
    if not todos_rows:
        log.warning("Sin registros MySQL. Se generará Excel vacío.")
    notificar_google_chat(
        f"📥 **MySQL** listo: **{len(todos_rows):,}** créditos elegibles (filtro fecha `{hoy}`)."
    )

    # PASO 2: traer lista negra y descartar en Python
    ids_negra = obtener_lista_negra(inicio_semana)
    ids_rows = [r for r in todos_rows if str(r["id_credito"]) not in ids_negra]
    descartados_negra = len(todos_rows) - len(ids_rows)
    log.info(f"  Descartados por lista negra : {descartados_negra:,}")
    log.info(f"  Quedan para consultar S2    : {len(ids_rows):,}")
    notificar_google_chat(
        f"🚫 **Lista negra** semana `{inicio_semana}`: se quitan **{descartados_negra:,}** · "
        f"**{len(ids_rows):,}** van a S2."
    )

    total = len(ids_rows)
    resultados_raw: list = [None] * total
    contador = {"n": 0}
    lock = threading.Lock()
    chat_progreso_lock = threading.Lock()
    ultimo_pct_chat = -1

    def quizas_chat_progreso(n: int) -> None:
        nonlocal ultimo_pct_chat
        if total <= 0:
            return
        pct = int(100 * n / total)
        hitos = (10, 25, 50, 75, 90, 100)
        with chat_progreso_lock:
            for h in hitos:
                if pct >= h and ultimo_pct_chat < h:
                    ultimo_pct_chat = h
                    el = time.time() - t0
                    vel = n / el if el > 0 else 1
                    notificar_google_chat(
                        f"⚙️ **S2** avance **{h}%** (`{n:,}` / `{total:,}`) · ~`{vel:.1f}` req/s"
                    )
                    break

    if total > 0:
        with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
            future_to_idx = {
                executor.submit(procesar_registro, row, fecha_corte, lunes, hoy): i
                for i, row in enumerate(ids_rows)
            }
            for future in as_completed(future_to_idx):
                idx = future_to_idx[future]
                try:
                    res = future.result()
                except Exception as e:
                    row = ids_rows[idx]
                    res = {
                        "ID_CREDITO": row["id_credito"],
                        "NOMBRE_CLIENTE": "",
                        "STATUS_CREDITO": "",
                        "CUOTA_SEMANAL": 0.0,
                        "DEUDA_GC": float(row["valor_real"] or 0),
                        "SALDO_A_FAVOR": 0.0,
                        "SALDO_APLICABLE_GC": 0.0,
                        "FECHA_ULTIMO_ABONO_EFECTIVO": fecha_abono_efectivo_para_excel(row, hoy),
                        "COMENTARIOS": "",
                        "ERROR": f"Excepcion: {e}",
                    }
                resultados_raw[idx] = res

                with lock:
                    contador["n"] += 1
                    n = contador["n"]
                quizas_chat_progreso(n)
                if n % LOG_CADA == 0 or n == total:
                    el = time.time() - t0
                    vel = n / el if el > 0 else 1
                    log.info(
                        f"  [{n:>6,}/{total:,}] {n/total*100:5.1f}%"
                        f" | {vel:5.1f} req/s"
                        f" | ETA: {(total-n)/vel/60:.1f} min"
                    )
    else:
        notificar_google_chat("⚙️ **S2** sin consultas (0 créditos tras filtros).")

    def _fila_error_s2_reintentable(r) -> bool:
        if r is None:
            return False
        return bool(str(r.get("ERROR") or "").strip())

    def _reintento_fusionar(idx: int, nuevo) -> None:
        """Si el reintento devuelve None (excluido negocio), conserva la fila con error anterior."""
        if nuevo is None:
            return
        resultados_raw[idx] = nuevo

    for num_pasada in range(PASADAS_REINTENTO_ERRORES):
        pendientes_idx = [
            i
            for i in range(total)
            if resultados_raw[i] is not None and _fila_error_s2_reintentable(resultados_raw[i])
        ]
        if not pendientes_idx:
            break
        log.info(
            "  Reintento errores S2 — pasada %s de %s — %s crédito(s)",
            num_pasada + 1,
            PASADAS_REINTENTO_ERRORES,
            len(pendientes_idx),
        )
        notificar_google_chat(
            f"🔁 **Reintento S2** (pasada {num_pasada + 1}/{PASADAS_REINTENTO_ERRORES}): "
            f"**{len(pendientes_idx):,}** crédito(s) con error en la pasada anterior."
        )
        if REINTENTO_PAUSA_S > 0:
            time.sleep(REINTENTO_PAUSA_S)
        w_retry = min(MAX_WORKERS, REINTENTO_MAX_WORKERS)
        with ThreadPoolExecutor(max_workers=w_retry) as executor:
            future_to_idx = {
                executor.submit(
                    procesar_registro, ids_rows[i], fecha_corte, lunes, hoy
                ): i
                for i in pendientes_idx
            }
            for future in as_completed(future_to_idx):
                idx = future_to_idx[future]
                try:
                    nuevo = future.result()
                except Exception as e:
                    row = ids_rows[idx]
                    nuevo = {
                        "ID_CREDITO": row["id_credito"],
                        "NOMBRE_CLIENTE": "",
                        "STATUS_CREDITO": "",
                        "CUOTA_SEMANAL": 0.0,
                        "DEUDA_GC": float(row["valor_real"] or 0),
                        "SALDO_A_FAVOR": 0.0,
                        "SALDO_APLICABLE_GC": 0.0,
                        "FECHA_ULTIMO_ABONO_EFECTIVO": fecha_abono_efectivo_para_excel(row, hoy),
                        "COMENTARIOS": "",
                        "ERROR": f"Excepcion (reintento): {e}",
                    }
                _reintento_fusionar(idx, nuevo)
        corregidos = sum(
            1
            for i in pendientes_idx
            if resultados_raw[i] is not None and not _fila_error_s2_reintentable(resultados_raw[i])
        )
        log.info("  Tras reintento: %s de %s corregidos en esta pasada", corregidos, len(pendientes_idx))
        for i in pendientes_idx:
            r = resultados_raw[i]
            if r is not None and _fila_error_s2_reintentable(r):
                err_txt = str(r.get("ERROR") or "").strip() or "(sin texto ERROR)"
                log.warning(
                    "  Sigue con error S2 tras reintento: id_credito=%s — %s",
                    r.get("ID_CREDITO"),
                    err_txt[:300],
                )
        notificar_google_chat(
            f"🔁 **Reintento** pasada {num_pasada + 1} terminada: "
            f"**{corregidos:,}** recuperados de **{len(pendientes_idx):,}**."
        )

    resultados = [r for r in resultados_raw if r is not None]
    excluidos_s2 = total - len(resultados)
    log.info(f"  Excluidos post-S2 (no vigente / sin GC / sin saldo) : {excluidos_s2:,}")
    notificar_google_chat(
        f"✅ **S2** terminado. En reporte final: **{len(resultados):,}** filas "
        f"(excl. post-S2: **{excluidos_s2:,}**). Fusionando descargo…"
    )

    # PASO 3: Descargo incremental (estatus=3) + merge con el reporte
    log.info("Consultando descargo incremental (estatus=3)...")
    print("--- descargo incremental ---", flush=True)
    rows_descargo = obtener_descargo_incremental()
    if rows_descargo:
        resultados = merge_descargo_en_reporte(resultados, rows_descargo)
        ult = rows_descargo[-1]
        _guardar_guia_descargo(
            ultimo_id_credito=int(ult.get("id_credito", 0) or 0),
            ultimo_id_tabla=int(ult.get("id", 0) or 0),
            ultimo_registrado_en_cdmx=_dt_descargo_a_str(ult.get("registrado_en_cdmx")),
        )
        print(
            f"Descargo: {len(rows_descargo)} fila(s) nueva(s) en esta corrida. "
            "Guía actualizada.",
            flush=True,
        )
        notificar_google_chat(
            f"🔀 **Descargo estatus-3**: **{len(rows_descargo):,}** fila(s) nueva(s) fusionadas al reporte."
        )
    else:
        log.info("Descargo: sin filas nuevas en esta corrida. La guía no se actualiza.")
        print(
            "Descargo incremental: sin filas nuevas en esta corrida. La guía no se actualiza.",
            flush=True,
        )
        notificar_google_chat("ℹ️ **Descargo estatus-3**: sin filas nuevas en esta corrida.")

    # PASO 4: generar Excel unificado (siempre, aunque queden 0 registros)
    generar_excel(resultados, lunes, hoy, ruta_excel)

    elapsed = time.time() - t0
    log.info("=" * 65)
    log.info(f"  TIEMPO              : {elapsed/60:.1f} min")
    log.info(f"  MySQL total         : {len(todos_rows):,}")
    log.info(f"  Lista negra quitó   : {descartados_negra:,}")
    log.info(f"  Procesados con S2   : {total:,}")
    log.info(f"  En reporte Excel    : {len(resultados):,}")
    log.info(f"  Con advertencia     : {sum(1 for r in resultados if r.get('COMENTARIOS')):,}")
    log.info(f"  Con error S2        : {sum(1 for r in resultados if r.get('ERROR')):,}")
    log.info(f"  DEUDA GC            : ${sum(r.get('DEUDA_GC', 0) for r in resultados):>15,.2f}")
    log.info(f"  SALDO A FAVOR       : ${sum(r.get('SALDO_A_FAVOR', 0) for r in resultados):>15,.2f}")
    log.info(f"  SALDO APLICABLE     : ${sum(r.get('SALDO_APLICABLE_GC', 0) for r in resultados):>15,.2f}")
    log.info("=" * 65)

    notificar_google_chat(
        f"🏁 **Reporte Cobranza** — **OK**\n"
        f"Tiempo: `{elapsed/60:.1f}` min · Archivo: `{os.path.basename(ruta_excel)}`\n"
        f"Filas Excel: **{len(resultados):,}**"
    )


if __name__ == "__main__":
    try:
        main()
    except Exception as _e:
        logging.exception("Fallo reporte cobranza")
        try:
            notificar_google_chat(f"❌ **Reporte Cobranza** — **ERROR**\n```{_e!s}```")
        except Exception:
            pass
        raise
