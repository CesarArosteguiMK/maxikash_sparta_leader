/**
 * Agente HTTP Gastos Cobranza — mismo patrón que segundometro-agent (express + API key opcional).
 * PHP en /gastoscobranza/* hace proxy aquí; el script Python vive en la máquina donde corre Node.
 *
 * Script por defecto: scripts/reporte_cobranza.py (copiado al repo). Opcional: REPORTE_COBRANZA_SCRIPT en .env.
 * Si no hay script y GASTOS_COBRANZA_DEMO=1, /run responde en modo prueba (solo desarrollo).
 * Reporte (Maxi app / __SPARTA_SECRET_REDACTED__): el hijo Python hereda process.env; credenciales opcionales
 * REPORTE_COBRANZA_AWS_HOST, _PORT, _USER, _PASSWORD, _DATABASE (ver docstring del .py).
 * Opcionales (solo pruebas locales): REPORTE_COBRANZA_NO_GUARDAR_GUIA_DESCARGO=1 no escribe guia_descargo.json;
 * REPORTE_COBRANZA_SIN_DESCARGO=1 omite descargo; REPORTE_COBRANZA_MODO_PRUEBA_EXCEL=1 → nombre ..._PRUEBA.xlsx.
 * POST /run JSON { regenerar_reporte: true }: renombra el .xlsx oficial del día (si existe) a *_antes_<timestamp>.xlsx
 *   y fuerza corrida completa (REPORTE_COBRANZA_REGENERAR=1 en el hijo Python).
 *
 * EC Launcher: POST /ec-launcher/run ejecuta tools/ec-webhook-worker/worker.php o
 * tools/ec-gc-excel-enrich/enrich_gc_excel.php (dentro de este agente; misma lógica que launcher/Lanzar.cmd).
 * Excel debe estar ya en reporte/ec-uploads (subida vía PHP en la vista).
 *
 * Carga verificación semana: POST /carga-verificacion-semana/run ejecuta
 * scripts/carga_cobranza_gc_verificacion_semana_desde_excel.py sobre un .xlsx en ec-uploads.
 *
 * Descargo estatus 3: POST /descargo-estatus3/run ejecuta scripts/descargo_cobranza_gc_estatus3.py;
 * escribe reporte/descargo_estatus3/descargo_estatus3.xlsx y guia_descargo.json.
 *
 * Log en logs/agente-gastos-cobranza.log: recorte automático si supera ~400 KB (últimas 1200 líneas).
 *   GASTOS_COBRANZA_LOG_MAX_BYTES (0 = sin recorte), GASTOS_COBRANZA_LOG_KEEP_LINES.
 *   GASTOS_COBRANZA_LOG_CLEAR_ON_START=1 vacía el log al arrancar el servicio.
 * POST /logs/clear vacía el archivo (deja una línea de marca).
 *
 * Reloj / archivado histórico: la semana «actual» usa el reloj del proceso (Date.now).
 * Si el servidor tiene hora mal, ponga GASTOS_GC_REMOTE_CDMX_TIME=1 para corregir contra una API HTTP
 * (ver syncCdmxClockFromRemote), o GASTOS_GC_CLOCK_OFFSET_MS (milisegundos a sumar al reloj local).
 *
 * Ejecución diaria del reporte (POST /run): con GASTOS_GC_AUTO_RUN_10AM_CDMX=1 (por defecto) un timer
 * en este proceso comprueba la hora civil en America/Mexico_City sobre el instante ya corregido
 * (offset remoto/manual); no usa el Programador de tareas de Windows. Recomendado activar
 * GASTOS_GC_REMOTE_CDMX_TIME=1 si el reloj del servidor no es fiable.
 */
const path = require('path');
const fs = require('fs');
const os = require('os');
const https = require('https');
const crypto = require('crypto');
const { spawn } = require('child_process');
try {
  require('dotenv').config({ path: path.join(__dirname, '.env') });
} catch (_) {}

const express = require('express');

const PORT = process.env.PORT || 3120;
const API_KEY = process.env.API_KEY || '';
const REPORTE_PYTHON = (process.env.REPORTE_PYTHON || 'python').trim();
const SCRIPT_BUNDLED = path.join(__dirname, 'scripts', 'reporte_cobranza.py');
const SCRIPT_CARGA_VERIFICACION = path.join(
  __dirname,
  'scripts',
  'carga_cobranza_gc_verificacion_semana_desde_excel.py'
);
const SCRIPT_DESCARGO_ESTATUS3 = path.join(__dirname, 'scripts', 'descargo_cobranza_gc_estatus3.py');

function getReporteScriptPath() {
  const fromEnv = (process.env.REPORTE_COBRANZA_SCRIPT || '').trim();
  if (fromEnv) return fromEnv;
  return fs.existsSync(SCRIPT_BUNDLED) ? SCRIPT_BUNDLED : '';
}

function getCargaVerificacionScriptPath() {
  const fromEnv = (process.env.CARGA_VERIFICACION_SCRIPT || '').trim();
  if (fromEnv) return fromEnv;
  return fs.existsSync(SCRIPT_CARGA_VERIFICACION) ? SCRIPT_CARGA_VERIFICACION : '';
}

function getDescargoEstatus3ScriptPath() {
  const fromEnv = (process.env.DESCARGO_ESTATUS3_SCRIPT || '').trim();
  if (fromEnv) return fromEnv;
  return fs.existsSync(SCRIPT_DESCARGO_ESTATUS3) ? SCRIPT_DESCARGO_ESTATUS3 : '';
}

function demoPermitidoSinScript() {
  return String(process.env.GASTOS_COBRANZA_DEMO || '0').trim() === '1';
}

const LOG_DIR = path.join(__dirname, 'logs');
const LOG_FILE = path.join(LOG_DIR, 'agente-gastos-cobranza.log');
/** Estado escribible por Node (auto-run, dedupe correo, etc.); fuera de `logs/` por permisos en Windows/XAMPP. */
const AGENT_DATA_DIR = path.join(__dirname, 'data');
/** 1/0 — toggle «automático» desde la UI. */
const AUTO_RUN_RUNTIME_FILE_DATA = path.join(AGENT_DATA_DIR, 'auto_run_reporte_runtime.txt');
/** Compatibilidad con versiones anteriores (mismo directorio que el .log). */
const AUTO_RUN_RUNTIME_FILE_LEGACY = path.join(LOG_DIR, '.auto_run_reporte_runtime.txt');
/** Último recurso si `data/` y `logs/` no son escribibles por el proceso Node. */
const AUTO_RUN_RUNTIME_FILE_TMP = path.join(os.tmpdir(), 'gastos-cobranza-agent-auto_run_reporte_runtime.txt');
/** Salida de scripts Python línea a línea hacia el log del agente (evita buffer de stdout sin TTY). */
const ENV_CON_PYTHON_UNBUFFERED = { ...process.env, PYTHONUNBUFFERED: '1' };
const REPORTE_DIR = path.join(__dirname, 'reporte');
const EC_UPLOAD_DIR = path.join(REPORTE_DIR, 'ec-uploads');
const EC_WORKER_DIR = path.join(__dirname, 'tools', 'ec-webhook-worker');
const EC_ENRICH_DIR = path.join(__dirname, 'tools', 'ec-gc-excel-enrich');
const DESCARGO_ESTATUS3_DIR = path.join(REPORTE_DIR, 'descargo_estatus3');

/** Mismo basename que reporte_cobranza.py / descargo_cobranza_gc_estatus3.py (guía distinta localhost vs servidor). */
function descargoGuiaBasename() {
  const raw = String(process.env.REPORTE_COBRANZA_DESCARGO_GUIA_BASENAME || 'guia_descargo.json').trim();
  if (!raw || raw.includes('/') || raw.includes('\\') || raw.includes('..') || raw.startsWith('.')) {
    return 'guia_descargo.json';
  }
  if (!raw.toLowerCase().endsWith('.json')) {
    return 'guia_descargo.json';
  }
  return raw;
}
const HISTORICO_DIR_NAME = 'historico';

/** Subcarpetas en la raíz de reporte/ que no se recorren al listar (historico/ sí se recorre). */
const REPORTE_SKIP_ROOT_DIRS = new Set(['ec-uploads', 'descargo_estatus3']);

function pad2Seg(n) {
  return String(n).padStart(2, '0');
}

function fmtCdmxYmdParts(date) {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'America/Mexico_City',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(date);
  const o = {};
  parts.forEach((p) => {
    if (p.type === 'year') o.y = parseInt(p.value, 10);
    if (p.type === 'month') o.m = parseInt(p.value, 10);
    if (p.type === 'day') o.d = parseInt(p.value, 10);
  });
  return o.y ? o : null;
}

/** Hora 24 h en calendario Ciudad de México para el instante `date` (ya corregido con nowForCdmxCalendar si aplica). */
function fmtCdmxHm24(date) {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'America/Mexico_City',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).formatToParts(date);
  const get = (t) => {
    const x = parts.find((p) => p.type === t);
    return x ? parseInt(x.value, 10) : 0;
  };
  return { h: get('hour'), m: get('minute') };
}

/** Milisegundos fijos sumados al reloj local (servidor desfasado; p. ej. +600000 si va 10 min atrasado). */
const MANUAL_CLOCK_OFFSET_MS =
  Number.parseInt(String(process.env.GASTOS_GC_CLOCK_OFFSET_MS || '0').trim(), 10) || 0;
/** Offset descubierto vía API (UTC real − Date.now() local). */
let remoteClockOffsetMs = 0;
let lastRemoteSyncOkAt = 0;
let lastRemoteSyncAttemptAt = 0;
/** Evita saturar el log si la API de hora falla en bucle (ECONNRESET, etc.). */
let lastRemoteSyncFailLogAt = 0;
let lastRemoteSyncFailMsg = '';

function remoteCdmxTimeEnabled() {
  return String(process.env.GASTOS_GC_REMOTE_CDMX_TIME || '').trim() === '1';
}

/** Instante usado para «hoy» calendario CDMX en archivado y clave de semana actual. */
function nowForCdmxCalendar() {
  return new Date(Date.now() + remoteClockOffsetMs + MANUAL_CLOCK_OFFSET_MS);
}

function addDaysYmd(y, m, d, delta) {
  const t = Date.UTC(y, m - 1, d) + delta * 86400000;
  const x = new Date(t);
  return { y: x.getUTCFullYear(), m: x.getUTCMonth() + 1, d: x.getUTCDate() };
}

/**
 * Día de la semana 0=lunes … 6=domingo para una fecha civil gregoriana (y, m, d).
 * No usa Intl: evita servidores sin locale completo y el bug anterior (fallback a lunes=0)
 * que trataba el 1-abr-2026 como «lunes» y generaba clave 2026-04-01 en vez de 2026-03-30.
 * «Hoy CDMX» sigue resolviéndose con fmtCdmxYmdParts(…) y luego el mismo calendario civil.
 */
function cdmxWeekdayMon0(y, m, d) {
  const t = [0, 3, 2, 5, 0, 3, 5, 1, 4, 6, 2, 4];
  const Y = m < 3 ? y - 1 : y;
  const wSun0 =
    (Y + Math.floor(Y / 4) - Math.floor(Y / 100) + Math.floor(Y / 400) + t[m - 1] + d) % 7;
  return (wSun0 + 6) % 7;
}

function lunesSemanaCdmx(y, m, d) {
  const k = cdmxWeekdayMon0(y, m, d);
  return addDaysYmd(y, m, d, -k);
}

function claveLunesYmd(L) {
  return `${L.y}-${pad2Seg(L.m)}-${pad2Seg(L.d)}`;
}

function parseNombreReporteCobranzaFecha(nom) {
  const m = /^reporte_cobranza_(\d{2})-(\d{2})-(\d{4})\.xlsx$/i.exec(String(nom || ''));
  if (!m) return null;
  const dd = parseInt(m[1], 10);
  const mm = parseInt(m[2], 10);
  const yy = parseInt(m[3], 10);
  if (mm < 1 || mm > 12 || dd < 1 || dd > 31) return null;
  return { y: yy, m: mm, d: dd };
}

function lunesSemanaClaveDesdeArchivoReporte(name, mtime) {
  const base = path.basename(name);
  let ymd = parseNombreReporteCobranzaFecha(base);
  if (!ymd) {
    const d = mtime instanceof Date ? mtime : new Date(mtime);
    ymd = fmtCdmxYmdParts(d);
  }
  if (!ymd) return null;
  return claveLunesYmd(lunesSemanaCdmx(ymd.y, ymd.m, ymd.d));
}

function lunesSemanaActualClave() {
  const h = fmtCdmxYmdParts(nowForCdmxCalendar());
  if (!h) return null;
  return claveLunesYmd(lunesSemanaCdmx(h.y, h.m, h.d));
}

function ymdDesdeClaveLunes(clave) {
  const p = String(clave)
    .split('-')
    .map((x) => parseInt(x, 10));
  if (p.length !== 3 || p.some((n) => Number.isNaN(n))) return null;
  return { y: p[0], m: p[1], d: p[2] };
}

/** Carpeta legible: 30mar2026_a_5abr2026 (lun–dom de esa semana). */
function folderNameSemanaCdmx(L, D) {
  const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
  const slug = (ymd) => `${ymd.d}${months[ymd.m - 1]}${ymd.y}`;
  return `${slug(L)}_a_${slug(D)}`;
}

function migrateEstadoReporteKey(oldName, newRel) {
  const fp = path.join(REPORTE_DIR, '.estados_reporte.json');
  if (!fs.existsSync(fp)) return;
  let map;
  try {
    map = JSON.parse(fs.readFileSync(fp, 'utf8'));
  } catch (_) {
    return;
  }
  if (!map || typeof map !== 'object') return;
  const base = path.basename(oldName);
  const keys = [oldName, base, String(oldName).toLowerCase(), base.toLowerCase()];
  for (const k of keys) {
    if (Object.prototype.hasOwnProperty.call(map, k)) {
      map[String(newRel).replace(/\\/g, '/')] = map[k];
      delete map[k];
      try {
        fs.writeFileSync(fp, `${JSON.stringify(map, null, 2)}\n`, 'utf8');
      } catch (_) {}
      return;
    }
  }
}

/**
 * Archiva en reporte/historico/<semana>/ los reporte_cobranza_*.xlsx de la raíz
 * cuya semana (lun–dom CDMX) según la **fecha en el nombre** ya terminó respecto a la semana actual.
 *
 * No usa fecha de modificación para decidir: evita mover reportes de la semana en curso si el nombre
 * es reporte_cobranza_DD-MM-YYYY.xlsx. Si el nombre no trae esa fecha, no se archiva.
 */
function archivarReportesCobranzaRaizSiAplica() {
  const keyActual = lunesSemanaActualClave();
  if (!keyActual) return;

  let files;
  try {
    files = fs.readdirSync(REPORTE_DIR);
  } catch (_) {
    return;
  }

  const historicoRoot = path.join(REPORTE_DIR, HISTORICO_DIR_NAME);

  for (const name of files) {
    if (!/^reporte_cobranza_.*\.xlsx$/i.test(name)) continue;
    const src = path.join(REPORTE_DIR, name);
    let st;
    try {
      st = fs.statSync(src);
    } catch (_) {
      continue;
    }
    if (!st.isFile()) continue;

    const base = path.basename(name);
    const ymdNom = parseNombreReporteCobranzaFecha(base);
    if (!ymdNom) continue;
    const keyFile = claveLunesYmd(lunesSemanaCdmx(ymdNom.y, ymdNom.m, ymdNom.d));
    if (!keyFile || keyFile >= keyActual) continue;

    const L = ymdDesdeClaveLunes(keyFile);
    if (!L) continue;
    const D = addDaysYmd(L.y, L.m, L.d, 6);
    const folder = folderNameSemanaCdmx(L, D);
    const destDir = path.join(historicoRoot, folder);
    try {
      if (!fs.existsSync(destDir)) fs.mkdirSync(destDir, { recursive: true });
    } catch (_) {
      continue;
    }

    const dest = path.join(destDir, name);
    const relNew = `${HISTORICO_DIR_NAME}/${folder}/${name}`;
    if (fs.existsSync(dest)) {
      const stem = name.replace(/\.xlsx$/i, '');
      const alt = `${stem}_${Math.floor(st.mtimeMs)}.xlsx`;
      try {
        fs.renameSync(src, path.join(destDir, alt));
        migrateEstadoReporteKey(name, `${HISTORICO_DIR_NAME}/${folder}/${alt}`);
        appendLog(`[reporte] archivado (nombre único): ${relNew} → ${alt}`);
      } catch (_) {}
    } else {
      try {
        fs.renameSync(src, dest);
        migrateEstadoReporteKey(name, relNew);
        appendLog(`[reporte] archivado: ${relNew}`);
      } catch (_) {}
    }
  }
}

/**
 * Resuelve ruta bajo baseDir; rel solo con segmentos [a-zA-Z0-9._-]+ (sin ..).
 */
function safeResolveUnderDir(relRaw, baseDir) {
  const rel = String(relRaw || '')
    .replace(/\\/g, '/')
    .trim();
  if (!rel || rel.includes('..')) return { error: 'Ruta inválida.' };
  const parts = rel.split('/').filter(Boolean);
  if (parts.length === 0) return { error: 'Ruta vacía.' };
  for (const p of parts) {
    if (!/^[a-zA-Z0-9._-]+$/.test(p)) return { error: 'Nombre de archivo o carpeta no permitido.' };
  }
  const abs = path.resolve(path.join(baseDir, ...parts));
  const root = path.resolve(baseDir);
  if (!abs.startsWith(root + path.sep) && abs !== root) return { error: 'Ruta fuera de carpeta permitida.' };
  return { abs };
}

function collectReporteXlsxRecursive() {
  const out = [];
  function walk(dir, relPrefix) {
    let entries;
    try {
      entries = fs.readdirSync(dir, { withFileTypes: true });
    } catch (_) {
      return;
    }
    for (const e of entries) {
      const name = e.name;
      if (name.startsWith('.')) continue;
      const full = path.join(dir, name);
      const rel = relPrefix ? `${relPrefix}/${name}` : name;
      if (e.isDirectory()) {
        if (!relPrefix && REPORTE_SKIP_ROOT_DIRS.has(name.toLowerCase())) continue;
        walk(full, rel);
      } else if (e.isFile() && name.toLowerCase().endsWith('.xlsx')) {
        let st;
        try {
          st = fs.statSync(full);
        } catch (_) {
          continue;
        }
        out.push({ nombre: rel.replace(/\\/g, '/'), st });
      }
    }
  }
  walk(REPORTE_DIR, '');
  return out;
}

/** Texto corto en columna Estado (UI) → tooltip = frase completa operativa */
const ESTADO_REPORTE = {
  generado: { corto: 'Generado', detalle: 'Reporte generado' },
  procCartera: { corto: 'Proc. cartera', detalle: 'Procesando por cartera' },
  aplicCartera: { corto: 'Aplic. cartera', detalle: 'Aplicado y confirmado por cartera' },
  enWorker: { corto: 'En Worker', detalle: 'Ejecutando worker S2 con este Excel' },
  workerListo: {
    corto: 'Worker listo',
    detalle: 'Worker terminó (éxito o errores parciales); pendiente o en curso la carga a lista negra',
  },
  listaNegra: { corto: 'Lista negra', detalle: 'Carga a verificación semana aplicada con este Excel' },
};

const ESTADO_REPORTE_KEYS = new Set(Object.keys(ESTADO_REPORTE));

function readEstadosReporteJson() {
  const fp = path.join(REPORTE_DIR, '.estados_reporte.json');
  if (!fs.existsSync(fp)) return {};
  try {
    const raw = fs.readFileSync(fp, 'utf8');
    const j = JSON.parse(raw);
    return j && typeof j === 'object' && !Array.isArray(j) ? j : {};
  } catch (_) {
    return {};
  }
}

function setEstadoReporteArchivo(nombreArchivo, estadoKey) {
  const nom = String(nombreArchivo || '').replace(/\\/g, '/').trim();
  if (!nom || !ESTADO_REPORTE_KEYS.has(estadoKey)) return;
  ensureReporteDir();
  const fp = path.join(REPORTE_DIR, '.estados_reporte.json');
  const map = readEstadosReporteJson();
  map[nom] = estadoKey;
  try {
    fs.writeFileSync(fp, `${JSON.stringify(map, null, 2)}\n`, 'utf8');
    appendLog(`[estado reporte] ${nom} -> ${estadoKey}`);
  } catch (e) {
    appendLog(`[estado reporte] error al escribir .estados_reporte.json: ${e?.message || e}`);
  }
}

function payloadEstadoReporte(nombreArchivo, estadoKey) {
  if (!nombreArchivo || !ESTADO_REPORTE_KEYS.has(estadoKey)) return null;
  const e = ESTADO_REPORTE[estadoKey];
  return {
    archivo: path.basename(String(nombreArchivo)),
    clave: estadoKey,
    corto: e.corto,
    detalle: e.detalle,
  };
}

/**
 * @returns {{ estado: string, estadoDetalle: string }}
 */
function resolverEstadoReporte(nombreArchivo, mapaJson) {
  const nom = String(nombreArchivo || '').replace(/\\/g, '/');
  const base = path.posix.basename(nom);
  const tryKeys = [nom, nom.toLowerCase(), base, base.toLowerCase()];
  let k = '';
  for (const tk of tryKeys) {
    const kd = mapaJson[tk];
    if (typeof kd === 'string' && kd.trim()) {
      k = kd.trim();
      break;
    }
  }
  if (k && ESTADO_REPORTE_KEYS.has(k) && ESTADO_REPORTE[k]) {
    const { corto, detalle } = ESTADO_REPORTE[k];
    return { estado: corto, estadoDetalle: detalle };
  }
  if (/^reporte_cobranza_[A-Za-z0-9._-]+\.xlsx$/i.test(base)) {
    const { corto, detalle } = ESTADO_REPORTE.generado;
    return { estado: corto, estadoDetalle: detalle };
  }
  return { estado: '', estadoDetalle: '' };
}

function ensureReporteDir() {
  try {
    if (!fs.existsSync(REPORTE_DIR)) fs.mkdirSync(REPORTE_DIR, { recursive: true });
    if (!fs.existsSync(EC_UPLOAD_DIR)) fs.mkdirSync(EC_UPLOAD_DIR, { recursive: true });
    if (!fs.existsSync(DESCARGO_ESTATUS3_DIR)) fs.mkdirSync(DESCARGO_ESTATUS3_DIR, { recursive: true });
  } catch (_) {}
}

function resolvePhpExe() {
  const fromEnv = (process.env.PHP_EXE || '').trim();
  if (fromEnv !== '' && fs.existsSync(fromEnv)) return fromEnv;
  const win = 'C:\\xampp\\php\\php.exe';
  if (fs.existsSync(win)) return win;
  return 'php';
}

function ensureLogDir() {
  try {
    if (!fs.existsSync(LOG_DIR)) fs.mkdirSync(LOG_DIR, { recursive: true });
  } catch (_) {}
}

function ensureAgentDataDir() {
  try {
    if (!fs.existsSync(AGENT_DATA_DIR)) fs.mkdirSync(AGENT_DATA_DIR, { recursive: true });
  } catch (_) {}
}

/** Tamaño máximo del archivo de log antes de recortar (0 = sin recorte automático). */
function logMaxBytes() {
  const n = parseInt(String(process.env.GASTOS_COBRANZA_LOG_MAX_BYTES || '400000'), 10);
  return Number.isFinite(n) && n > 0 ? n : 0;
}

/** Líneas que se conservan al recortar (cola del archivo). */
function logKeepLines() {
  const n = parseInt(String(process.env.GASTOS_COBRANZA_LOG_KEEP_LINES || '1200'), 10);
  return Number.isFinite(n) && n >= 100 ? Math.min(20000, n) : 1200;
}

/**
 * Si el log supera el umbral, reescribe el archivo dejando solo las últimas N líneas.
 * Evita que la bitácora acumule días enteros en disco y en el panel.
 */
function trimLogFileIfOversized() {
  const maxB = logMaxBytes();
  if (maxB <= 0 || !fs.existsSync(LOG_FILE)) return;
  try {
    const st = fs.statSync(LOG_FILE);
    if (st.size <= maxB) return;
    const raw = fs.readFileSync(LOG_FILE, 'utf8');
    const lines = raw.split(/\r?\n/);
    const keep = logKeepLines();
    const tail = lines.slice(-keep);
    const hdr = `[${new Date().toISOString()}] --- log recortado automaticamente (conservadas ultimas ${keep} lineas, max ~${maxB} bytes) ---\n`;
    fs.writeFileSync(LOG_FILE, hdr + tail.join('\n'), { encoding: 'utf8' });
  } catch (_) {}
}

function appendLog(text) {
  ensureLogDir();
  const line = typeof text === 'string' ? text : String(text);
  try {
    fs.appendFileSync(LOG_FILE, line + (line.endsWith('\n') ? '' : '\n'), { encoding: 'utf8' });
    trimLogFileIfOversized();
  } catch (_) {}
}

/** Mínimo entre líneas de log por el mismo error de sync (ms). Default 5 min. */
function remoteSyncFailLogIntervalMs() {
  const n = parseInt(String(process.env.GASTOS_GC_TIME_SYNC_FAIL_LOG_MIN_MS || '300000').trim(), 10);
  return Number.isFinite(n) && n >= 10000 ? n : 300000;
}

function appendRemoteSyncFailureLog(tagPrefix, err) {
  const msg = String(err && err.message != null ? err.message : err);
  const now = Date.now();
  const minMs = remoteSyncFailLogIntervalMs();
  if (msg === lastRemoteSyncFailMsg && now - lastRemoteSyncFailLogAt < minMs) {
    return;
  }
  lastRemoteSyncFailMsg = msg;
  lastRemoteSyncFailLogAt = now;
  appendLog(`${tagPrefix} Fallo sincronización: ${msg} (offset_remoto_ms=${remoteClockOffsetMs})`);
}

function timeSyncMaxAgeMs() {
  const n = parseInt(String(process.env.GASTOS_GC_TIME_SYNC_MAX_AGE_MS || '900000').trim(), 10);
  return Number.isFinite(n) && n >= 60000 ? n : 900000;
}

function httpsGetJson(urlStr, timeoutMs) {
  return new Promise((resolve, reject) => {
    let settled = false;
    const url = new URL(urlStr);
    const req = https.request(
      {
        hostname: url.hostname,
        port: url.port || 443,
        path: `${url.pathname}${url.search}`,
        method: 'GET',
        headers: { 'User-Agent': 'gastos-cobranza-agent/1.0' },
      },
      (res) => {
        let d = '';
        res.on('data', (c) => {
          d += c;
        });
        res.on('end', () => {
          if (settled) return;
          settled = true;
          clearTimeout(to);
          if (res.statusCode && res.statusCode >= 400) {
            reject(new Error(`HTTP ${res.statusCode}`));
            return;
          }
          try {
            resolve(JSON.parse(d));
          } catch (e) {
            reject(e);
          }
        });
      },
    );
    const to = setTimeout(() => {
      if (settled) return;
      settled = true;
      req.destroy();
      reject(new Error('timeout'));
    }, timeoutMs);
    req.on('error', (e) => {
      if (settled) return;
      settled = true;
      clearTimeout(to);
      reject(e);
    });
    req.end();
  });
}

function offsetMsFromPublicTimeJson(body) {
  if (!body || typeof body !== 'object') throw new Error('cuerpo vacío');
  if (body.unixtime != null) return Number(body.unixtime) * 1000 - Date.now();
  if (body.epochMilliseconds != null) return Number(body.epochMilliseconds) - Date.now();
  if (body.unixTime != null) return Number(body.unixTime) * 1000 - Date.now();
  throw new Error('JSON sin unixtime ni epochMilliseconds');
}

async function syncCdmxClockFromRemote() {
  const url =
    String(process.env.GASTOS_GC_TIME_API_URL || '').trim() ||
    'https://worldtimeapi.org/api/timezone/America/Mexico_City';
  const timeout = Math.min(
    30000,
    Math.max(3000, parseInt(String(process.env.GASTOS_GC_TIME_API_TIMEOUT_MS || '8000').trim(), 10) || 8000),
  );
  const body = await httpsGetJson(url, timeout);
  const off = offsetMsFromPublicTimeJson(body);
  if (!Number.isFinite(off)) throw new Error('offset NaN');
  remoteClockOffsetMs = off;
  lastRemoteSyncOkAt = Date.now();
  lastRemoteSyncFailMsg = '';
  const parts = fmtCdmxYmdParts(nowForCdmxCalendar());
  appendLog(
    `[reloj] API hora (${url}) offset_remoto_ms=${remoteClockOffsetMs} ` +
      `→ CDMX hoy ${parts ? `${parts.y}-${pad2Seg(parts.m)}-${pad2Seg(parts.d)}` : '?'}`,
  );
}

async function ensureCdmxTimeFresh() {
  if (!remoteCdmxTimeEnabled()) return;
  const n = Date.now();
  if (lastRemoteSyncOkAt && n - lastRemoteSyncOkAt < timeSyncMaxAgeMs()) return;
  if (n - lastRemoteSyncAttemptAt < 15000) return;
  lastRemoteSyncAttemptAt = n;
  try {
    await syncCdmxClockFromRemote();
  } catch (e) {
    appendRemoteSyncFailureLog('[reloj]', e);
  }
}

/** Igual que ensureCdmxTimeFresh pero también actúa si GASTOS_GC_AUTO_RUN_SYNC_REMOTE_CLOCK=1 (sin GASTOS_GC_REMOTE_CDMX_TIME). */
async function ensureCdmxTimeFreshForAutoRunTick() {
  if (!remoteCdmxTimeEnabled() && !autoRunSyncRemoteClockForTick()) return;
  const n = Date.now();
  if (lastRemoteSyncOkAt && n - lastRemoteSyncOkAt < timeSyncMaxAgeMs()) return;
  if (n - lastRemoteSyncAttemptAt < 15000) return;
  lastRemoteSyncAttemptAt = n;
  try {
    await syncCdmxClockFromRemote();
  } catch (e) {
    appendRemoteSyncFailureLog('[reloj][auto-run]', e);
  }
}

/** Programador interno: equivale a «Ejecutar agente» sin Programador de tareas Windows. */
function autoRun10amCdmxEnabled() {
  return String(process.env.GASTOS_GC_AUTO_RUN_10AM_CDMX ?? '1').trim() !== '0';
}

/** `true`|`false` si no hubo disco escribible (EPERM/EACCES); `undefined` = leer de archivos. */
let autoRunRuntimeMemory = undefined;

function listAutoRunRuntimeWritePaths() {
  const envPath = String(process.env.GASTOS_GC_AUTO_RUN_RUNTIME_FILE || '').trim();
  if (envPath) {
    return [path.resolve(envPath)];
  }
  return [AUTO_RUN_RUNTIME_FILE_DATA, AUTO_RUN_RUNTIME_FILE_TMP, AUTO_RUN_RUNTIME_FILE_LEGACY];
}

function listAutoRunRuntimeReadPaths() {
  const envPath = String(process.env.GASTOS_GC_AUTO_RUN_RUNTIME_FILE || '').trim();
  if (envPath) {
    return [path.resolve(envPath)];
  }
  // Mismo orden que escritura: preferir `data/` y tmp antes que `logs/` (suele fallar en Windows).
  return [AUTO_RUN_RUNTIME_FILE_DATA, AUTO_RUN_RUNTIME_FILE_TMP, AUTO_RUN_RUNTIME_FILE_LEGACY];
}

function readAutoRunRuntimeOverride() {
  if (autoRunRuntimeMemory !== undefined) return autoRunRuntimeMemory;
  for (const p of listAutoRunRuntimeReadPaths()) {
    try {
      if (!fs.existsSync(p)) continue;
      const t = fs.readFileSync(p, 'utf8').trim().toLowerCase();
      if (t === '0' || t === 'false' || t === 'off') return false;
      if (t === '1' || t === 'true' || t === 'on') return true;
    } catch (_) {}
  }
  return null;
}

function writeAutoRunRuntimeOverride(enabled) {
  autoRunRuntimeMemory = undefined;
  const val = enabled ? '1' : '0';
  let lastErr = null;
  for (const p of listAutoRunRuntimeWritePaths()) {
    try {
      const dir = path.dirname(p);
      if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
      fs.writeFileSync(p, val, { encoding: 'utf8' });
      appendLog(`[auto-run] Preferencia runtime (${enabled ? 'activada' : 'desactivada'}) → ${p}`);
      for (const other of listAutoRunRuntimeReadPaths()) {
        if (other === p) continue;
        try {
          if (fs.existsSync(other)) fs.unlinkSync(other);
        } catch (_) {}
      }
      return;
    } catch (e) {
      lastErr = e;
    }
  }
  autoRunRuntimeMemory = !!enabled;
  appendLog(
    `[auto-run] AVISO: no se pudo escribir estado en disco (${lastErr && lastErr.message ? lastErr.message : lastErr}); ` +
      `queda ${enabled ? 'activado' : 'desactivado'} solo en memoria hasta reiniciar el agente Node.`,
  );
}

/** Si hay archivo runtime: manda sobre .env; si no, se usa GASTOS_GC_AUTO_RUN_10AM_CDMX. */
function autoRun10amCdmxEffective() {
  const o = readAutoRunRuntimeOverride();
  if (o === true) return true;
  if (o === false) return false;
  return autoRun10amCdmxEnabled();
}

function autoRunTargetHour() {
  const n = parseInt(String(process.env.GASTOS_GC_AUTO_RUN_HOUR || '10').trim(), 10);
  return Number.isFinite(n) && n >= 0 && n <= 23 ? n : 10;
}

function autoRunTargetMinute() {
  const n = parseInt(String(process.env.GASTOS_GC_AUTO_RUN_MINUTE || '0').trim(), 10);
  return Number.isFinite(n) && n >= 0 && n <= 59 ? n : 0;
}

function autoRunWindowMinutes() {
  const n = parseInt(String(process.env.GASTOS_GC_AUTO_RUN_WINDOW_MINUTES || '25').trim(), 10);
  return Number.isFinite(n) && n >= 1 && n <= 120 ? n : 25;
}

function autoRunTickMs() {
  const n = parseInt(String(process.env.GASTOS_GC_AUTO_RUN_TICK_MS || '30000').trim(), 10);
  return Math.max(10000, Math.min(600000, Number.isFinite(n) ? n : 30000));
}

function autoRunWeekdaysOnly() {
  return String(process.env.GASTOS_GC_AUTO_RUN_WEEKDAYS_ONLY || '0').trim() === '1';
}

/**
 * Con auto-run, opcionalmente sincroniza hora vía API aunque GASTOS_GC_REMOTE_CDMX_TIME=0 (solo el tick programado).
 * Por defecto desactivado: evita llamadas a internet y log masivo si no hay salida estable (ECONNRESET).
 * Activar: GASTOS_GC_AUTO_RUN_SYNC_REMOTE_CLOCK=1
 */
function autoRunSyncRemoteClockForTick() {
  return String(process.env.GASTOS_GC_AUTO_RUN_SYNC_REMOTE_CLOCK || '0').trim() === '1';
}

let lastAutoRunDebugLogMs = 0;

function autoRunStateFilePath() {
  return path.join(AGENT_DATA_DIR, '.auto_run_reporte_cdmx_ymd.txt');
}

function readAutoRunLastYmd() {
  ensureAgentDataDir();
  try {
    const p = autoRunStateFilePath();
    if (fs.existsSync(p)) return fs.readFileSync(p, 'utf8').trim();
  } catch (_) {}
  return '';
}

function writeAutoRunLastYmd(ymd) {
  ensureAgentDataDir();
  try {
    fs.writeFileSync(autoRunStateFilePath(), String(ymd).trim(), { encoding: 'utf8' });
  } catch (_) {}
}

/** Tras /run exitoso: correo (PHP + PHPMailer) + aviso en Google Chat webhook (texto; el .xlsx va en el correo). */
function reportePostRunNotifyEnabled() {
  return String(process.env.GASTOS_GC_REPORTE_POST_RUN_NOTIFY ?? '1').trim() !== '0';
}

function reporteMailAfterRunEnabled() {
  return String(process.env.GASTOS_GC_REPORTE_MAIL_ENABLED ?? '1').trim() !== '0';
}

function reporteChatWebhookUrl() {
  return String(process.env.GASTOS_GC_REPORTE_CHAT_WEBHOOK_URL || '').trim();
}

function sanitizeTraceId(raw) {
  const t = String(raw || '').trim();
  return /^[A-Za-z0-9._:-]{6,80}$/.test(t) ? t : '';
}

/** Solo URL dedicada a lista negra / worker EC; no reutiliza el webhook post-reporte (evita mezclar espacios de Chat). */
function ecWorkflowWebhookUrl() {
  return String(process.env.GASTOS_GC_EC_CHAT_WEBHOOK_URL || '').trim();
}

function ecWorkflowWebhookEnabled() {
  return String(process.env.GASTOS_GC_EC_CHAT_ENABLED ?? '1').trim() !== '0';
}

/** Avisos worker_inicio/worker_fin a Chat (0 = solo lista negra resumida; 1 = incluir worker). */
function ecWorkerChatEventsEnabled() {
  return String(process.env.GASTOS_GC_EC_CHAT_WORKER_EVENTS ?? '0').trim() === '1';
}

function notifyEcWorkflowWebhook(evento, traceId, detalleLineas) {
  if (!ecWorkflowWebhookEnabled()) return Promise.resolve(false);
  const hook = ecWorkflowWebhookUrl();
  if (!hook) {
    appendLog('[ec-webhook] GASTOS_GC_EC_CHAT_WEBHOOK_URL vacía; aviso EC no enviado a Chat.');
    return Promise.resolve(false);
  }
  const tr = sanitizeTraceId(traceId);
  const lines = Array.isArray(detalleLineas) ? detalleLineas.filter((x) => String(x || '').trim() !== '') : [];
  const text =
    `GC Worker/Lista negra\n` +
    `Evento: ${String(evento || 'sin_evento')}` +
    (tr ? `\nTrace: ${tr}` : '') +
    (lines.length ? `\n${lines.join('\n')}` : '');
  appendLog(`[ec-webhook] enviando evento=${evento}${tr ? ` trace=${tr}` : ''}`);
  return postGoogleChatWebhook(hook, text);
}

/** Última línea `[ec-webhook-lista-negra] {...}` emitida por el script Python. */
function parseListaNegraResumenFromStdout(stdout) {
  const pref = '[ec-webhook-lista-negra] ';
  const lines = String(stdout || '').split(/\r?\n/);
  for (let i = lines.length - 1; i >= 0; i -= 1) {
    const L = String(lines[i] || '').trim();
    if (!L.startsWith(pref)) continue;
    try {
      return JSON.parse(L.slice(pref.length));
    } catch (_) {
      return null;
    }
  }
  return null;
}

/** Un solo mensaje legible para Chat (menos ruido que evento + claves técnicas). */
function notifyListaNegraFinResumenWebhook(traceId, archivo, code, dryRunReq, resumen) {
  if (!ecWorkflowWebhookEnabled()) return Promise.resolve(false);
  const hook = ecWorkflowWebhookUrl();
  if (!hook) {
    appendLog('[ec-webhook] GASTOS_GC_EC_CHAT_WEBHOOK_URL vacía; resumen lista negra no enviado a Chat.');
    return Promise.resolve(false);
  }
  const tr = sanitizeTraceId(traceId);
  const ok = code === 0;
  const sim = !!(dryRunReq || (resumen && resumen.dry_run));
  const lines = [];
  lines.push('Lista negra · Gastos cobranza');
  lines.push(`Archivo: ${archivo}`);
  if (tr) lines.push(`Traza: ${tr}`);
  if (resumen && typeof resumen === 'object') {
    if (resumen.ids_unicos_validos_en_excel != null) {
      lines.push(`IDs únicos válidos en el Excel: ${resumen.ids_unicos_validos_en_excel}`);
    }
    if (sim) {
      if (resumen.prevision_actualizaciones_3_a_2 != null) {
        lines.push(`Previsión 3→2 (créditos distintos): ${resumen.prevision_actualizaciones_3_a_2}`);
      }
      if (resumen.prevision_inserts_nuevos != null) {
        lines.push(`Previsión insertados nuevos (créditos distintos): ${resumen.prevision_inserts_nuevos}`);
      }
    } else {
      if (resumen.filas_actualizadas_3_a_2 != null) {
        lines.push(`Filas actualizadas en BD (estatus 3→2): ${resumen.filas_actualizadas_3_a_2}`);
      }
      if (resumen.filas_insertadas_nuevas != null) {
        lines.push(`Filas insertadas nuevas en BD: ${resumen.filas_insertadas_nuevas}`);
      }
    }
    if (resumen.sin_cambio_en_bd != null) {
      lines.push(`Sin cambio en BD (misma semana, sin fila en estatus 3): ${resumen.sin_cambio_en_bd}`);
    }
    const notas = [];
    if (Number(resumen.filas_sin_id_credito) > 0) {
      notas.push(`${resumen.filas_sin_id_credito} fila(s) en Excel sin id de crédito válido`);
    }
    if (Number(resumen.duplicados_excel_omitidos) > 0) {
      notas.push(`${resumen.duplicados_excel_omitidos} id(s) duplicado(s) en Excel omitidos`);
    }
    if (notas.length) lines.push(`Nota: ${notas.join(' · ')}`);
  } else {
    lines.push('(No hubo línea de resumen parseable; revisar log del agente / salida del script.)');
  }
  lines.push(
    `${ok ? 'Resultado: OK' : 'Resultado: ERROR'} · código salida ${code}${sim ? ' · simulación (dry-run)' : ''}`,
  );
  const text = lines.join('\n');
  appendLog(`[ec-webhook] lista negra fin resumen${tr ? ` trace=${tr}` : ''}`);
  return postGoogleChatWebhook(hook, text);
}

/** Ruta absoluta del Excel oficial del día (CDMX), en la raíz de reporte/ (sin _PRUEBA). */
function pathReporteOficialHoyCdmx() {
  ensureReporteDir();
  const ymd = fmtCdmxYmdParts(nowForCdmxCalendar());
  if (!ymd) return null;
  const dd = pad2Seg(ymd.d);
  const mm = pad2Seg(ymd.m);
  const yyyy = ymd.y;
  const base = `reporte_cobranza_${dd}-${mm}-${yyyy}.xlsx`;
  return path.join(REPORTE_DIR, base);
}

/** Si existe el oficial del día, lo renombra para conservarlo y permitir regeneración completa. */
function archivarExcelOficialParaRegeneracion() {
  const abs = pathReporteOficialHoyCdmx();
  if (!abs) return;
  try {
    if (!fs.existsSync(abs) || !fs.statSync(abs).isFile()) return;
    const stamp = new Date().toISOString().replace(/[-:TZ.]/g, '').slice(0, 15);
    const dest = abs.replace(/\.xlsx$/i, `_antes_${stamp}.xlsx`);
    if (fs.existsSync(dest)) {
      appendLog('[run] Regenerar: ya existe copia de respaldo con el mismo sello; no se renombró.');
      return;
    }
    fs.renameSync(abs, dest);
    appendLog(`[run] Regenerar: Excel previo guardado como ${path.basename(dest)}`);
    try {
      migrateEstadoReporteKey(path.basename(abs), path.basename(dest));
    } catch (_) {
      /* ignorar migración de estado */
    }
  } catch (e) {
    appendLog(`[run] Regenerar: no se pudo renombrar Excel previo (${e?.message || e}); el script usará REPORTE_COBRANZA_REGENERAR.`);
  }
}

function resolveReporteXlsxPathAfterRun() {
  ensureReporteDir();
  const ymd = fmtCdmxYmdParts(nowForCdmxCalendar());
  if (!ymd) return null;
  const dd = pad2Seg(ymd.d);
  const mm = pad2Seg(ymd.m);
  const yyyy = ymd.y;
  const prueba = String(process.env.REPORTE_COBRANZA_MODO_PRUEBA_EXCEL || '0').trim() === '1';
  const suf = prueba ? '_PRUEBA' : '';
  const base = `reporte_cobranza_${dd}-${mm}-${yyyy}${suf}.xlsx`;
  const abs = path.join(REPORTE_DIR, base);
  try {
    if (fs.existsSync(abs) && fs.statSync(abs).isFile()) return abs;
  } catch (_) {}
  try {
    const names = fs.readdirSync(REPORTE_DIR);
    let best = null;
    let bestMs = 0;
    for (const f of names) {
      if (!/^reporte_cobranza_.+\.xlsx$/i.test(f)) continue;
      const p = path.join(REPORTE_DIR, f);
      let st;
      try {
        st = fs.statSync(p);
      } catch (_) {
        continue;
      }
      if (!st.isFile()) continue;
      if (st.mtimeMs > bestMs) {
        bestMs = st.mtimeMs;
        best = p;
      }
    }
    return best;
  } catch (_) {
    return null;
  }
}

function postGoogleChatWebhook(urlStr, text) {
  return new Promise((resolve) => {
    let u;
    try {
      u = new URL(urlStr);
    } catch (_) {
      appendLog('[post-reporte] URL webhook inválida.');
      resolve(false);
      return;
    }
    if (u.protocol !== 'https:') {
      appendLog('[post-reporte] Solo se admite webhook HTTPS.');
      resolve(false);
      return;
    }
    const body = JSON.stringify({ text });
    const opts = {
      hostname: u.hostname,
      port: u.port || 443,
      path: u.pathname + u.search,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json; charset=UTF-8',
        'Content-Length': Buffer.byteLength(body, 'utf8'),
      },
      timeout: 25000,
    };
    const req = https.request(opts, (res) => {
      let d = '';
      res.on('data', (c) => {
        d += c;
      });
      res.on('end', () => {
        if (res.statusCode && res.statusCode >= 200 && res.statusCode < 300) {
          appendLog(`[post-reporte] Google Chat OK HTTP ${res.statusCode}`);
          resolve(true);
        } else {
          appendLog(`[post-reporte] Google Chat HTTP ${res.statusCode} ${d ? d.slice(0, 500) : ''}`);
          resolve(false);
        }
      });
    });
    req.on('error', (e) => {
      appendLog('[post-reporte] Google Chat error: ' + (e.message || e));
      resolve(false);
    });
    req.on('timeout', () => {
      req.destroy();
      appendLog('[post-reporte] Google Chat timeout');
      resolve(false);
    });
    req.write(body);
    req.end();
  });
}

function spawnPhpEnviarReporteGcMail(xlsxAbs) {
  const backendRoot = path.resolve(__dirname, '..', '..');
  const script = path.join(backendRoot, 'cronjobs', 'enviar_reporte_gc_excel.php');
  const php = resolvePhpExe();
  if (!fs.existsSync(script)) {
    appendLog('[post-reporte] No existe cronjobs/enviar_reporte_gc_excel.php');
    return;
  }
  appendLog('[post-reporte] Enviando correo vía PHP (Reporteria / PHPMailer)…');
  const ch = spawn(php, [script, xlsxAbs], {
    env: { ...process.env },
    windowsHide: true,
    stdio: ['ignore', 'pipe', 'pipe'],
  });
  ch.stdout.on('data', (d) => {
    const s = d.toString().replace(/\r/g, '').trim();
    if (s) appendLog('[correo-gc] ' + s);
  });
  ch.stderr.on('data', (d) => {
    const s = d.toString().replace(/\r/g, '').trim();
    if (s) appendLog('[correo-gc][err] ' + s);
  });
  ch.on('exit', (c) => {
    if (c !== 0) appendLog('[post-reporte] PHP correo exit code ' + c);
  });
}

/** Huella estable por contenido del Excel ya cerrado (nombre + tamaño + mtime en seg.). */
function fingerprintPostReporteXlsx(st, base) {
  return `${base}:${st.size}:${Math.floor(st.mtimeMs / 1000)}`;
}

function postReporteDedupeStatePath() {
  return path.join(AGENT_DATA_DIR, '.post_reporte_entrega_dedupe.json');
}

function postReporteDedupeWindowMs() {
  const h = parseFloat(String(process.env.GASTOS_GC_REPORTE_POST_DEDUPE_HOURS || '48').trim());
  const hrs = Number.isFinite(h) && h > 0 ? Math.min(h, 168) : 48;
  return Math.round(hrs * 3600000);
}

function readPostReporteDedupeRecord() {
  const tryParse = (p) => {
    try {
      const raw = fs.readFileSync(p, 'utf8');
      const j = JSON.parse(raw);
      if (j && typeof j.fp === 'string' && typeof j.t === 'number') return j;
    } catch (_) {}
    return null;
  };
  const cur = tryParse(postReporteDedupeStatePath());
  if (cur) return cur;
  const legacy = path.join(LOG_DIR, '.post_reporte_entrega_dedupe.json');
  return tryParse(legacy);
}

function writePostReporteDedupeRecord(fp) {
  try {
    ensureAgentDataDir();
    fs.writeFileSync(postReporteDedupeStatePath(), JSON.stringify({ fp, t: Date.now() }), { encoding: 'utf8' });
  } catch (_) {}
}

function postReporteEntregaLockPath(fp) {
  const h = crypto.createHash('sha256').update(fp).digest('hex').slice(0, 40);
  return path.join(AGENT_DATA_DIR, `.post_reporte_entrega_${h}.lock`);
}

function stalePostReporteLockMs() {
  const n = parseInt(String(process.env.GASTOS_GC_REPORTE_POST_LOCK_STALE_MS || '600000').trim(), 10);
  return Number.isFinite(n) && n >= 60000 ? Math.min(n, 3600000) : 600000;
}

/**
 * Evita ráfagas duplicadas (varias instancias Node, reintentos, carreras).
 * @returns {string|null} ruta del .lock si se obtuvo; null si hay que omitir
 */
function acquirePostReporteNotifyLock(fp) {
  ensureAgentDataDir();
  const lp = postReporteEntregaLockPath(fp);
  const staleMs = stalePostReporteLockMs();
  for (let attempt = 0; attempt < 3; attempt += 1) {
    try {
      fs.writeFileSync(lp, String(process.pid), { encoding: 'utf8', flag: 'wx' });
      return lp;
    } catch (e) {
      if (!e || e.code !== 'EEXIST') {
        appendLog('[post-reporte] Lock: ' + (e && e.message ? e.message : String(e)));
        return null;
      }
      let mtime = 0;
      try {
        mtime = fs.statSync(lp).mtimeMs;
      } catch (_) {
        continue;
      }
      if (Date.now() - mtime > staleMs) {
        try {
          fs.unlinkSync(lp);
        } catch (_) {}
        continue;
      }
      appendLog('[post-reporte] Omitido: notificación del mismo archivo ya en curso o reciente (lock).');
      return null;
    }
  }
  return null;
}

function releasePostReporteNotifyLock(lp) {
  if (!lp) return;
  try {
    fs.unlinkSync(lp);
  } catch (_) {}
}

/** Cola: no solapa envíos post-reporte en el mismo proceso. */
let postReporteEntregaChain = Promise.resolve();

async function runPostReporteEntregasImpl(xlsxAbs) {
  if (!reportePostRunNotifyEnabled()) return;
  if (!xlsxAbs || !fs.existsSync(xlsxAbs)) {
    appendLog('[post-reporte] Sin .xlsx para notificar.');
    return;
  }
  let st;
  try {
    st = fs.statSync(xlsxAbs);
  } catch (_) {
    appendLog('[post-reporte] No se pudo leer el .xlsx.');
    return;
  }
  if (!st.isFile()) return;
  const base = path.basename(xlsxAbs);
  const fp = fingerprintPostReporteXlsx(st, base);
  const prev = readPostReporteDedupeRecord();
  const win = postReporteDedupeWindowMs();
  if (prev && prev.fp === fp && Date.now() - prev.t < win) {
    appendLog('[post-reporte] Omitido: ya se envió correo/Chat para este mismo archivo (dedupe).');
    return;
  }

  const lockPath = acquirePostReporteNotifyLock(fp);
  if (!lockPath) return;

  try {
    const kb = Math.max(1, Math.round(st.size / 1024));

    if (reporteMailAfterRunEnabled()) {
      spawnPhpEnviarReporteGcMail(xlsxAbs);
    }

    const hook = reporteChatWebhookUrl();
    if (hook) {
      const text =
        `📊 *Reporte Gastos Cobranza*\n` +
        `Archivo: \`${base}\` (~${kb} KB)\n` +
        `El Excel se envió por correo a los destinatarios configurados (PHPMailer).`;
      await postGoogleChatWebhook(hook, text);
    }

    writePostReporteDedupeRecord(fp);
  } finally {
    releasePostReporteNotifyLock(lockPath);
  }
}

function runPostReporteEntregas(xlsxAbs) {
  const p = postReporteEntregaChain.then(() => runPostReporteEntregasImpl(xlsxAbs));
  postReporteEntregaChain = p.catch(() => {});
  return p;
}

/** Una sola corrida del reporte a la vez (/run HTTP o auto-run). */
let reporteRunBusy = false;
/** Proceso hijo de reporte_cobranza.py mientras corre (para POST /run/cancel). */
let reporteChild = null;

/**
 * Ejecuta reporte_cobranza.py (o demo). `res` null = disparo interno (solo log).
 * @param {import('express').Response|null} res
 * @param {{ autoRunMarkYmd?: string, regenerar?: boolean }} [opts] Si `autoRunMarkYmd` (YYYY-MM-DD CDMX), solo se escribe estado al terminar con código 0 (no al iniciar).
 * @returns {boolean} true si se inició una corrida (o demo).
 */
function runReporteCobranza(res, opts = {}) {
  const autoRunMarkYmd = typeof opts.autoRunMarkYmd === 'string' ? opts.autoRunMarkYmd.trim() : '';
  const regenerar = !!(opts && opts.regenerar);
  if (reporteRunBusy) {
    const msg = 'Ya hay una ejecución del reporte en curso.';
    appendLog('[run] ' + msg);
    if (res) {
      res.status(409).json({ success: false, mensaje: msg });
    }
    return false;
  }

  const REPORTE_COBRANZA_SCRIPT = getReporteScriptPath();
  if (!REPORTE_COBRANZA_SCRIPT) {
    if (!demoPermitidoSinScript()) {
      const err = {
        success: false,
        mensaje:
          'No hay scripts/reporte_cobranza.py ni REPORTE_COBRANZA_SCRIPT en .env. Para modo demo sin script: GASTOS_COBRANZA_DEMO=1.',
      };
      if (res) res.json(err);
      else appendLog('[auto-run] ' + err.mensaje);
      return false;
    }
    const ts = new Date().toISOString();
    const stdout =
      `[${ts}] MODO PRUEBA (sin script Python)\n` +
      'Coloque reporte_cobranza.py en scripts/ o defina REPORTE_COBRANZA_SCRIPT en .env.\n' +
      'OK — demo completada.\n';
    appendLog(`--- /run demo ${ts} ---\n${stdout}`);
    const demoPayload = {
      success: true,
      codigo_salida: 0,
      stdout,
      stderr: '',
      demo: true,
    };
    if (res) res.json(demoPayload);
    else {
      appendLog('[auto-run] Demo sin script completada (codigo_salida=0).');
      if (autoRunMarkYmd) writeAutoRunLastYmd(autoRunMarkYmd);
    }
    return true;
  }

  const esPy = REPORTE_COBRANZA_SCRIPT.toLowerCase().endsWith('.py');
  const cmd = esPy ? REPORTE_PYTHON : REPORTE_COBRANZA_SCRIPT;
  const args = esPy ? [REPORTE_COBRANZA_SCRIPT] : [];
  const cwd = esPy ? path.dirname(REPORTE_COBRANZA_SCRIPT) : path.dirname(REPORTE_COBRANZA_SCRIPT);

  if (regenerar) {
    archivarExcelOficialParaRegeneracion();
  }

  reporteRunBusy = true;
  const tag = res ? 'HTTP /run' : 'auto-run CDMX';
  appendLog(`--- /run inicio (${tag}) ${new Date().toISOString()} ${cmd} ${args.join(' ')}${regenerar ? ' [regenerar]' : ''} ---`);

  const envPy =
    esPy && regenerar
      ? { ...ENV_CON_PYTHON_UNBUFFERED, REPORTE_COBRANZA_REGENERAR: '1' }
      : ENV_CON_PYTHON_UNBUFFERED;

  const child = spawn(cmd, args, {
    cwd,
    env: esPy ? envPy : { ...process.env },
    windowsHide: true,
  });
  reporteChild = child;

  let stdout = '';
  let stderr = '';
  const maxChunk = 512 * 1024;
  let respondio = false;
  const enviar = (payload) => {
    if (respondio) return;
    respondio = true;
    if (payload.codigo_salida !== undefined) {
      appendLog(`--- cierre código ${payload.codigo_salida} ---`);
    } else if (payload.mensaje) {
      appendLog('--- error: ' + payload.mensaje);
    }
    if (res) res.json(payload);
    else if (payload.codigo_salida !== undefined) {
      appendLog(`[auto-run] Fin script código=${payload.codigo_salida} success=${!!payload.success}`);
    } else {
      appendLog(`[auto-run] ${payload.mensaje || 'fin'}`);
    }
  };

  child.stdout.on('data', (d) => {
    const s = d.toString();
    if (stdout.length < maxChunk) stdout += s;
    appendLog(s.replace(/\r/g, ''));
  });
  child.stderr.on('data', (d) => {
    const s = d.toString();
    if (stderr.length < maxChunk) stderr += s;
    appendLog('[stderr] ' + s.replace(/\r/g, ''));
  });

  child.on('error', (err) => {
    reporteRunBusy = false;
    reporteChild = null;
    appendLog('[error spawn] ' + (err.message || String(err)));
    enviar({
      success: false,
      mensaje: err.message || String(err),
    });
  });

  child.on('close', (code) => {
    reporteRunBusy = false;
    reporteChild = null;
    enviar({
      success: code === 0,
      codigo_salida: code,
      stdout: stdout.slice(-8000),
      stderr: stderr.slice(-8000),
    });
    if (code === 0) {
      if (autoRunMarkYmd) writeAutoRunLastYmd(autoRunMarkYmd);
      const xlsx = resolveReporteXlsxPathAfterRun();
      runPostReporteEntregas(xlsx).catch((e) => appendLog('[post-reporte] ' + (e?.message || e)));
    } else if (autoRunMarkYmd) {
      appendLog(
        `[auto-run] El script terminó con código ${code}; no se marca el día como enviado — dentro de la ventana se reintentará.`,
      );
    }
  });
  return true;
}

let autoRunTickBusy = false;

async function tickAutoRunReporteCdmx() {
  if (!autoRun10amCdmxEffective()) return;
  if (autoRunTickBusy) return;
  if (reporteRunBusy) return;

  autoRunTickBusy = true;
  try {
    await ensureCdmxTimeFreshForAutoRunTick();

    const wall = nowForCdmxCalendar();
    const ymd = fmtCdmxYmdParts(wall);
    if (!ymd) return;
    const { h, m } = fmtCdmxHm24(wall);
    const todayStr = `${ymd.y}-${pad2Seg(ymd.m)}-${pad2Seg(ymd.d)}`;

    if (String(process.env.GASTOS_GC_AUTO_RUN_DEBUG || '').trim() === '1') {
      const nowMs = Date.now();
      if (nowMs - lastAutoRunDebugLogMs > 9 * 60 * 1000) {
        lastAutoRunDebugLogMs = nowMs;
        const th = autoRunTargetHour();
        const tm = autoRunTargetMinute();
        const win = autoRunWindowMinutes();
        appendLog(
          `[auto-run][debug] CDMX ahora ${pad2Seg(h)}:${pad2Seg(m)} · calendario ${todayStr} · objetivo ${pad2Seg(th)}:${pad2Seg(tm)} +${win} min · offset_remoto_ms=${remoteClockOffsetMs} · OKhoy=${readAutoRunLastYmd()}`,
        );
      }
    }

    if (autoRunWeekdaysOnly()) {
      const wd = cdmxWeekdayMon0(ymd.y, ymd.m, ymd.d);
      if (wd >= 5) return;
    }

    const th = autoRunTargetHour();
    const tm = autoRunTargetMinute();
    const startMin = th * 60 + tm;
    const curMin = h * 60 + m;
    const win = autoRunWindowMinutes();
    if (curMin < startMin || curMin >= startMin + win) return;

    const last = readAutoRunLastYmd();
    if (last === todayStr) return;

    appendLog(
      `[auto-run] Disparo CDMX ${todayStr} ${pad2Seg(h)}:${pad2Seg(m)} (objetivo ${pad2Seg(th)}:${pad2Seg(tm)}, ventana ${win} min; offset_remoto_ms=${remoteClockOffsetMs})`,
    );
    runReporteCobranza(null, { autoRunMarkYmd: todayStr });
  } finally {
    autoRunTickBusy = false;
  }
}

function readLogTail(maxLines) {
  ensureLogDir();
  trimLogFileIfOversized();
  if (!fs.existsSync(LOG_FILE)) return '';
  try {
    const raw = fs.readFileSync(LOG_FILE, 'utf8');
    const lines = raw.split(/\r?\n/);
    const n = Math.max(1, Math.min(500, maxLines || 150));
    return lines.slice(-n).join('\n');
  } catch (_) {
    return '';
  }
}

function clearLogFile(manual) {
  ensureLogDir();
  const tag = manual ? 'vaciado manualmente' : 'vaciado';
  const hdr = `[${new Date().toISOString()}] --- log ${tag} ---\n`;
  try {
    fs.writeFileSync(LOG_FILE, hdr, { encoding: 'utf8' });
  } catch (_) {}
}

/** Día de la semana (es-MX) según calendario Ciudad de México, para la fecha de modificación del .xlsx. */
function diaSemanaLargoCdmx(date) {
  const s = new Intl.DateTimeFormat('es-MX', {
    timeZone: 'America/Mexico_City',
    weekday: 'long',
  }).format(date);
  return s.charAt(0).toUpperCase() + s.slice(1);
}

const app = express();
app.use(express.json());

/** Un solo proceso EC (worker o enrich) a la vez; evita solapar worker.php en el mismo agente. */
let ecLauncherBusy = false;

function middlewareApiKey(req, res, next) {
  if (!API_KEY) return next();
  const key = req.get('X-Api-Key') || req.query.api_key || '';
  if (key !== API_KEY) {
    return res.status(401).json({ success: false, mensaje: 'API key inválida o faltante.' });
  }
  next();
}

app.use(middlewareApiKey);

app.use((req, res, next) => {
  res.set('Access-Control-Allow-Origin', '*');
  res.set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.set('Access-Control-Allow-Headers', 'Content-Type, X-Api-Key');
  if (req.method === 'OPTIONS') return res.sendStatus(204);
  next();
});

app.post('/auto-run-reporte', (req, res) => {
  try {
    const body = req.body && typeof req.body === 'object' ? req.body : {};
    if (!Object.prototype.hasOwnProperty.call(body, 'enabled')) {
      return res.status(400).json({ success: false, mensaje: 'Falta el campo enabled (boolean).' });
    }
    const en =
      body.enabled === true ||
      body.enabled === 1 ||
      String(body.enabled).toLowerCase() === '1' ||
      String(body.enabled).toLowerCase() === 'true';
    writeAutoRunRuntimeOverride(en);
    const ro = readAutoRunRuntimeOverride();
    const soloMemoria = autoRunRuntimeMemory !== undefined;
    return res.json({
      success: true,
      auto_run_runtime_solo_memoria: soloMemoria,
      auto_run_cdmx: {
        enabled: autoRun10amCdmxEffective(),
        enabled_env: autoRun10amCdmxEnabled(),
        runtime_override: ro === null ? null : ro ? 1 : 0,
        hour: autoRunTargetHour(),
        minute: autoRunTargetMinute(),
        window_minutes: autoRunWindowMinutes(),
        tick_ms: autoRunTickMs(),
        weekdays_only: autoRunWeekdaysOnly(),
        last_fired_cdmx_ymd: readAutoRunLastYmd(),
        reporte_busy: reporteRunBusy,
      },
    });
  } catch (e) {
    return res.status(500).json({ success: false, mensaje: String(e?.message || e) });
  }
});

app.get('/health', (req, res) => {
  ensureReporteDir();
  const scriptPath = getReporteScriptPath();
  const hCdmx = fmtCdmxYmdParts(nowForCdmxCalendar());
  const runtimeOr = readAutoRunRuntimeOverride();
  res.json({
    success: true,
    servicio: 'gastos-cobranza-agent',
    timestamp: new Date().toISOString(),
    cdmx_calendario_hoy: hCdmx ? `${hCdmx.y}-${pad2Seg(hCdmx.m)}-${pad2Seg(hCdmx.d)}` : null,
    reloj_remoto_cdmx: remoteCdmxTimeEnabled(),
    reloj_offset_remoto_ms: remoteClockOffsetMs,
    reloj_offset_manual_ms: MANUAL_CLOCK_OFFSET_MS,
    script_configurado: scriptPath.length > 0,
    script_bundled: scriptPath !== '' && scriptPath === SCRIPT_BUNDLED,
    demo_sin_script: !scriptPath && demoPermitidoSinScript(),
    carpeta_reporte: REPORTE_DIR,
    ec_worker_presente: fs.existsSync(path.join(EC_WORKER_DIR, 'worker.php')),
    ec_enrich_presente: fs.existsSync(path.join(EC_ENRICH_DIR, 'enrich_gc_excel.php')),
    ec_launcher_cmd: path.join(__dirname, 'launcher', 'Lanzar.cmd'),
    script_carga_verificacion_semana: getCargaVerificacionScriptPath() !== '',
    script_carga_verificacion_semana_bundled:
      getCargaVerificacionScriptPath() !== '' &&
      getCargaVerificacionScriptPath() === SCRIPT_CARGA_VERIFICACION,
    script_descargo_estatus3: getDescargoEstatus3ScriptPath() !== '',
    script_descargo_estatus3_bundled:
      getDescargoEstatus3ScriptPath() !== '' && getDescargoEstatus3ScriptPath() === SCRIPT_DESCARGO_ESTATUS3,
    carpeta_descargo_estatus3: DESCARGO_ESTATUS3_DIR,
    ec_launcher_ocupado: ecLauncherBusy,
    auto_run_cdmx: {
      enabled: autoRun10amCdmxEffective(),
      enabled_env: autoRun10amCdmxEnabled(),
      runtime_override: runtimeOr === null ? null : runtimeOr ? 1 : 0,
      runtime_solo_memoria: autoRunRuntimeMemory !== undefined,
      hour: autoRunTargetHour(),
      minute: autoRunTargetMinute(),
      window_minutes: autoRunWindowMinutes(),
      tick_ms: autoRunTickMs(),
      weekdays_only: autoRunWeekdaysOnly(),
      last_fired_cdmx_ymd: readAutoRunLastYmd(),
      reporte_busy: reporteRunBusy,
    },
    reporte: {
      en_curso: reporteRunBusy,
      cancelar: 'POST /run/cancel (misma API key) termina el proceso Python del reporte si está en ejecución.',
    },
  });
});

/** Listado recursivo de .xlsx bajo reporte/ (excluye ec-uploads y descargo_estatus3 en raíz). */
app.get('/reportes', async (req, res) => {
  ensureReporteDir();
  try {
    await ensureCdmxTimeFresh();
    archivarReportesCobranzaRaizSiAplica();
  } catch (e) {
    appendLog(`[reporte] archivado automático: ${e?.message || e}`);
  }
  let items = [];
  try {
    items = collectReporteXlsxRecursive();
  } catch (e) {
    return res.json({ success: false, mensaje: String(e.message || e), archivos: [] });
  }
  const mapaEstados = readEstadosReporteJson();
  const list = items
    .map(({ nombre, st }) => {
      try {
        const { estado, estadoDetalle } = resolverEstadoReporte(nombre, mapaEstados);
        return {
          nombre,
          bytes: st.size,
          modificado: st.mtime.toISOString(),
          diaSemanaModificado: diaSemanaLargoCdmx(st.mtime),
          estado,
          estadoDetalle,
        };
      } catch (_) {
        return null;
      }
    })
    .filter(Boolean)
    .sort((a, b) => (a.modificado < b.modificado ? 1 : -1));
  res.json({ success: true, archivos: list });
});

/** Descarga CSV de errores persistentes tras 2.ª pasada del worker (solo nombres generados por worker.php). */
app.get('/ec-uploads/descargar-errores-reintento', (req, res) => {
  ensureReporteDir();
  const raw = String(req.query.nombre || '');
  const nombre = path.basename(raw);
  if (
    !nombre ||
    nombre !== raw ||
    !/^ec_worker_errores_reintento_\d{8}_\d{6}\.csv$/.test(nombre)
  ) {
    return res.status(400).json({ success: false, mensaje: 'Nombre de archivo no permitido.' });
  }
  const p = path.join(EC_UPLOAD_DIR, nombre);
  if (!fs.existsSync(p) || !fs.statSync(p).isFile()) {
    return res.status(404).json({ success: false, mensaje: 'Archivo no encontrado.' });
  }
  res.setHeader('Content-Type', 'text/csv; charset=utf-8');
  return res.download(p, nombre);
});

/** Descarga segura de un Excel bajo reporte/ (raíz o historico/…). */
app.get('/reportes/descargar', (req, res) => {
  ensureReporteDir();
  const raw = String(req.query.nombre || '').replace(/\\/g, '/');
  const resolved = safeResolveUnderDir(raw, REPORTE_DIR);
  if (resolved.error) {
    return res.status(400).json({ success: false, mensaje: resolved.error });
  }
  const p = resolved.abs;
  const base = path.basename(p);
  if (!base.toLowerCase().endsWith('.xlsx') || !/^reporte_cobranza_/i.test(base)) {
    return res.status(400).json({ success: false, mensaje: 'Nombre de archivo no permitido.' });
  }
  if (!fs.existsSync(p) || !fs.statSync(p).isFile()) {
    return res.status(404).json({ success: false, mensaje: 'Archivo no encontrado.' });
  }
  res.download(p, base);
});

/** Últimas líneas del log en disco (para panel en la vista). */
app.get('/logs', (req, res) => {
  const lines = parseInt(String(req.query.lines || '120'), 10) || 120;
  const text = readLogTail(lines);
  res.json({
    success: true,
    contenido: text,
    archivo: path.basename(LOG_FILE),
  });
});

/** Vacía el archivo de log (deja una sola línea de marca). Misma API key que el resto. */
app.post('/logs/clear', (req, res) => {
  clearLogFile(true);
  res.json({ success: true, mensaje: 'Log vaciado.' });
});

app.post('/run', (req, res) => {
  const b = req.body && typeof req.body === 'object' && !Array.isArray(req.body) ? req.body : {};
  const regenerar = !!(
    b.regenerar_reporte === true ||
    b.regenerar_reporte === 1 ||
    String(b.regenerar_reporte || '').toLowerCase() === 'true'
  );
  runReporteCobranza(res, { regenerar });
});

/**
 * Detiene la corrida actual de reporte_cobranza.py (hijo del proceso Node).
 * No afecta otros endpoints (EC launcher, etc.).
 */
app.post('/run/cancel', (req, res) => {
  if (!reporteChild || reporteChild.killed) {
    return res.status(404).json({
      success: false,
      mensaje: 'No hay corrida de reporte en ejecución.',
    });
  }
  try {
    appendLog('[run/cancel] Cancelación solicitada: terminando proceso del reporte…');
    reporteChild.kill();
    res.json({
      success: true,
      mensaje: 'Se envió la señal de terminación al reporte. Revise el log del agente.',
    });
  } catch (e) {
    appendLog('[run/cancel] Error: ' + (e?.message || e));
    res.status(500).json({ success: false, mensaje: String(e?.message || e) });
  }
});

/** EC launcher: worker.php o enrich_gc_excel.php (paridad con launcher/Lanzar.cmd). */
app.post('/ec-launcher/run', express.json({ limit: '1mb' }), (req, res) => {
  ensureReporteDir();
  const tipo = String(req.body?.tipo || '').toLowerCase();
  const archivoRelRaw = String(req.body?.archivo || '').replace(/\\/g, '/').trim();
  const fechaCorte = String(req.body?.fechaCorte || '').trim();
  const column = String(req.body?.column != null ? req.body.column : 'ID CREDITO').trim() || 'ID CREDITO';
  const omitir = Math.max(0, parseInt(String(req.body?.omitir ?? '0'), 10) || 0);
  const soloColumnas = !!req.body?.soloColumnas;
  const traceId = sanitizeTraceId(req.body?.traceId);

  if (!archivoRelRaw || !archivoRelRaw.toLowerCase().endsWith('.xlsx')) {
    return res.status(400).json({
      success: false,
      mensaje: 'Indique nombre de archivo .xlsx (en reporte/ec-uploads o en reporte/ si origenCarpeta=reporte).',
    });
  }
  if (!/^\d{4}-\d{2}-\d{2}$/.test(fechaCorte)) {
    return res.status(400).json({ success: false, mensaje: 'fechaCorte debe ser YYYY-MM-DD.' });
  }

  const origenCarpeta = String(req.body?.origenCarpeta || 'ec-uploads').toLowerCase();
  const baseDir =
    origenCarpeta === 'reporte' ? path.resolve(REPORTE_DIR) : path.resolve(EC_UPLOAD_DIR);

  let abs;
  if (origenCarpeta === 'reporte') {
    const r = safeResolveUnderDir(archivoRelRaw, baseDir);
    if (r.error) {
      return res.status(400).json({ success: false, mensaje: r.error });
    }
    abs = r.abs;
  } else {
    const archivo = path.basename(archivoRelRaw);
    if (!archivo || !archivo.toLowerCase().endsWith('.xlsx')) {
      return res.status(400).json({
        success: false,
        mensaje: 'Indique nombre de archivo .xlsx (en reporte/ec-uploads o en reporte/ si origenCarpeta=reporte).',
      });
    }
    abs = path.resolve(path.join(baseDir, archivo));
    if (!abs.startsWith(baseDir + path.sep) && abs !== baseDir) {
      return res.status(400).json({ success: false, mensaje: 'Ruta de archivo no permitida.' });
    }
  }

  if (!fs.existsSync(abs)) {
    const donde = origenCarpeta === 'reporte' ? 'reporte/' : 'reporte/ec-uploads';
    return res.status(404).json({ success: false, mensaje: `Archivo no encontrado en ${donde}.` });
  }

  const archivoBase = path.basename(abs);
  const archivoEstado =
    origenCarpeta === 'reporte' ? path.relative(REPORTE_DIR, abs).replace(/\\/g, '/') : archivoBase;

  const php = resolvePhpExe();
  /** Evita que PHP acumule stdout en tubería: el log del agente ve líneas al vuelo. */
  const phpUnbuf = ['-d', 'output_buffering=0', '-d', 'implicit_flush=1', '-d', 'zlib.output_compression=0'];
  let cwd;
  let args;
  if (tipo === 'worker') {
    if (!fs.existsSync(path.join(EC_WORKER_DIR, 'worker.php'))) {
      return res.status(500).json({
        success: false,
        mensaje: 'No existe gastos-cobranza-agent/tools/ec-webhook-worker/worker.php.',
      });
    }
    cwd = EC_WORKER_DIR;
    args = [
      ...phpUnbuf,
      path.join(EC_WORKER_DIR, 'worker.php'),
      `--ids-xlsx=${abs}`,
      `--ids-xlsx-column=${column}`,
      `--fecha-corte=${fechaCorte}`,
      `--omitir-primeros=${omitir}`,
    ];
  } else if (tipo === 'enrich') {
    if (!fs.existsSync(path.join(EC_ENRICH_DIR, 'enrich_gc_excel.php'))) {
      return res.status(500).json({
        success: false,
        mensaje: 'No existe gastos-cobranza-agent/tools/ec-gc-excel-enrich/enrich_gc_excel.php.',
      });
    }
    cwd = EC_ENRICH_DIR;
    args = [
      ...phpUnbuf,
      path.join(EC_ENRICH_DIR, 'enrich_gc_excel.php'),
      `--input=${abs}`,
      '--chat',
      `--fecha-corte=${fechaCorte}`,
      `--omitir-primeros=${omitir}`,
    ];
    if (soloColumnas) args.push('--solo-columnas');
  } else {
    return res.status(400).json({ success: false, mensaje: 'tipo debe ser "worker" o "enrich".' });
  }

  if (ecLauncherBusy) {
    return res.status(409).json({
      success: false,
      mensaje:
        'Ya hay un Worker o proceso EC (enrich) en ejecución en este agente. Espere a que termine antes de lanzar otro.',
    });
  }
  ecLauncherBusy = true;

  /** Solo reportes cobranza en reporte/: la tabla del shell muestra estado por .estados_reporte.json */
  const marcarEstadoWorkerReporte =
    tipo === 'worker' && origenCarpeta === 'reporte' && /^reporte_cobranza_/i.test(archivoBase);
  if (marcarEstadoWorkerReporte) {
    setEstadoReporteArchivo(archivoEstado, 'enWorker');
  }

  const env = { ...process.env, FECHA_CORTE: fechaCorte };
  appendLog(`--- ec-launcher ${tipo} archivo=${archivoEstado} fecha=${fechaCorte} php=${php}${traceId ? ` trace=${traceId}` : ''} ---`);
  if (tipo === 'worker' && ecWorkerChatEventsEnabled()) {
    void notifyEcWorkflowWebhook('worker_inicio', traceId, [
      `archivo=${archivoEstado}`,
      `fecha_corte=${fechaCorte}`,
      `origen=${origenCarpeta}`,
    ]);
  }

  const child = spawn(php, args, { cwd, env, windowsHide: true, shell: false });

  let stdout = '';
  let stderr = '';
  const maxChunk = 512 * 1024;
  let respondio = false;
  const enviar = (payload) => {
    if (respondio) return;
    respondio = true;
    ecLauncherBusy = false;
    if (payload.codigo_salida !== undefined) {
      appendLog(`--- ec-launcher cierre ${payload.codigo_salida}${traceId ? ` trace=${traceId}` : ''} ---`);
    } else if (payload.mensaje) {
      appendLog(`--- ec-launcher error${traceId ? ` trace=${traceId}` : ''}: ` + payload.mensaje);
    }
    res.json(payload);
  };

  child.stdout.on('data', (d) => {
    const s = d.toString();
    stdout += s;
    if (stdout.length > maxChunk) {
      stdout = stdout.slice(-maxChunk);
    }
    appendLog(s.replace(/\r/g, ''));
  });
  child.stderr.on('data', (d) => {
    const s = d.toString();
    stderr += s;
    if (stderr.length > maxChunk) {
      stderr = stderr.slice(-maxChunk);
    }
    appendLog('[ec-launcher stderr] ' + s.replace(/\r/g, ''));
  });
  child.on('error', (err) => {
    appendLog(`[ec-launcher spawn${traceId ? ` trace=${traceId}` : ''}] ` + (err.message || String(err)));
    if (tipo === 'worker') {
      void notifyEcWorkflowWebhook('worker_error', traceId, [
        `archivo=${archivoEstado}`,
        `error=${String(err?.message || err)}`,
      ]);
    }
    if (marcarEstadoWorkerReporte) {
      setEstadoReporteArchivo(archivoEstado, 'generado');
    }
    enviar({
      success: false,
      mensaje: err.message || String(err),
      estado_reporte: marcarEstadoWorkerReporte ? payloadEstadoReporte(archivoEstado, 'generado') : null,
      traceId: traceId || null,
    });
  });
  child.on('close', (code) => {
    const outSlice = stdout.slice(-8000);
    let erroresReintentoCsv = null;
    if (tipo === 'worker') {
      const reCsv = /\[ec-webhook-worker\] ERRORES_REINTENTO_CSV=([^\s\r\n]+)/g;
      let m;
      let last = null;
      while ((m = reCsv.exec(stdout)) !== null) {
        last = m[1];
      }
      if (last && /^ec_worker_errores_reintento_\d{8}_\d{6}\.csv$/.test(last)) {
        erroresReintentoCsv = last;
      }
    }
    let estadoRep = null;
    if (marcarEstadoWorkerReporte) {
      const key = code === 0 || code === 2 ? 'workerListo' : 'generado';
      setEstadoReporteArchivo(archivoEstado, key);
      estadoRep = payloadEstadoReporte(archivoEstado, key);
    }
    const payload = {
      success: code === 0,
      codigo_salida: code,
      stdout: outSlice,
      stderr: stderr.slice(-8000),
      tipo,
      archivo: archivoEstado,
      estado_reporte: estadoRep,
      traceId: traceId || null,
    };
    if (erroresReintentoCsv) {
      payload.errores_reintento_csv = erroresReintentoCsv;
    }
    if (tipo === 'worker') {
      const workerFallo = code !== 0;
      if (workerFallo || ecWorkerChatEventsEnabled()) {
        void notifyEcWorkflowWebhook('worker_fin', traceId, [
          `archivo=${archivoEstado}`,
          `codigo_salida=${code}`,
          `success=${code === 0 ? '1' : '0'}`,
          erroresReintentoCsv ? `errores_reintento_csv=${erroresReintentoCsv}` : '',
        ]);
      }
    }
    enviar(payload);
  });
});

/** Carga Excel → INSERT cobranza_gc_verificacion_semana (Python, mismo .xlsx en ec-uploads). */
app.post('/carga-verificacion-semana/run', express.json({ limit: '512kb' }), (req, res) => {
  ensureReporteDir();
  const scriptPath = getCargaVerificacionScriptPath();
  if (!scriptPath) {
    return res.status(500).json({
      success: false,
      mensaje:
        'No hay scripts/carga_cobranza_gc_verificacion_semana_desde_excel.py ni CARGA_VERIFICACION_SCRIPT en .env.',
    });
  }

  const archivoRelRaw = String(req.body?.archivo || '').replace(/\\/g, '/').trim();
  if (!archivoRelRaw || !archivoRelRaw.toLowerCase().endsWith('.xlsx')) {
    return res.status(400).json({
      success: false,
      mensaje: 'Indique nombre de archivo .xlsx (ec-uploads o reporte/ con origenCarpeta=reporte).',
    });
  }

  const origenCarpeta = String(req.body?.origenCarpeta || 'ec-uploads').toLowerCase();
  const baseDir =
    origenCarpeta === 'reporte' ? path.resolve(REPORTE_DIR) : path.resolve(EC_UPLOAD_DIR);

  let abs;
  if (origenCarpeta === 'reporte') {
    const r = safeResolveUnderDir(archivoRelRaw, baseDir);
    if (r.error) {
      return res.status(400).json({ success: false, mensaje: r.error });
    }
    abs = r.abs;
  } else {
    const archivo = path.basename(archivoRelRaw);
    abs = path.resolve(path.join(baseDir, archivo));
    if (!abs.startsWith(baseDir + path.sep) && abs !== baseDir) {
      return res.status(400).json({ success: false, mensaje: 'Ruta de archivo no permitida.' });
    }
  }

  if (!fs.existsSync(abs)) {
    const donde = origenCarpeta === 'reporte' ? 'reporte/' : 'reporte/ec-uploads';
    return res.status(404).json({ success: false, mensaje: `Archivo no encontrado en ${donde}.` });
  }

  const archivoLog =
    origenCarpeta === 'reporte' ? path.relative(REPORTE_DIR, abs).replace(/\\/g, '/') : path.basename(abs);

  const inicioSemana = String(req.body?.inicioSemana || '').trim();
  const dryRun = !!req.body?.dryRun;
  const megaPhpDefaults = req.body?.megaPhpDefaults !== false;
  const traceId = sanitizeTraceId(req.body?.traceId);
  let estatus = 2;
  if (req.body?.estatus !== undefined && req.body?.estatus !== null && req.body?.estatus !== '') {
    const n = parseInt(String(req.body.estatus), 10);
    if (n === 0 || n === 1 || n === 2) estatus = n;
  }
  const mensaje = req.body?.mensaje != null ? String(req.body.mensaje).trim() : '';

  let headerRowPandas = 0;
  let headerRowExplicit = false;
  if (req.body?.headerRow !== undefined && req.body?.headerRow !== null && String(req.body.headerRow).trim() !== '') {
    const hr = parseInt(String(req.body.headerRow).trim(), 10);
    if (!Number.isFinite(hr) || hr < 1 || hr > 200) {
      return res.status(400).json({
        success: false,
        mensaje: 'headerRow: número de fila en Excel donde están los títulos de columna (1–200). Vacío = detectar automático.',
      });
    }
    headerRowPandas = hr - 1;
    headerRowExplicit = true;
  }

  if (inicioSemana && !/^\d{4}-\d{2}-\d{2}$/.test(inicioSemana)) {
    return res.status(400).json({ success: false, mensaje: 'inicioSemana debe ser YYYY-MM-DD o vacío.' });
  }

  // Esta ruta solo sirve carga lista negra masiva: tipo_reporte = NULL en MySQL (no error/falta_aplicar).
  const args = [
    scriptPath,
    '--excel',
    abs,
    '--no-gui',
    '--estatus',
    String(estatus),
    '--tipo-reporte-nulo',
  ];
  if (inicioSemana) args.push('--inicio-semana', inicioSemana);
  if (dryRun) args.push('--dry-run');
  if (mensaje) args.push('--mensaje', mensaje);
  if (megaPhpDefaults) args.push('--mega-php-defaults');
  else args.push('--no-mega-php-defaults');
  if (headerRowExplicit) {
    args.push('--header-row', String(headerRowPandas));
  }

  const cwd = path.dirname(scriptPath);
  appendLog(
    `--- carga-verificacion-semana archivo=${archivoLog} dryRun=${dryRun} inicioSemana=${inicioSemana || '(auto)'} headerRow=${headerRowExplicit ? headerRowPandas : 'auto'}${traceId ? ` trace=${traceId}` : ''} ---`,
  );

  const child = spawn(REPORTE_PYTHON, args, {
    cwd,
    env: ENV_CON_PYTHON_UNBUFFERED,
    windowsHide: true,
    shell: false,
  });

  let stdout = '';
  let stderr = '';
  const maxChunk = 512 * 1024;
  let respondio = false;
  const enviar = (payload) => {
    if (respondio) return;
    respondio = true;
    if (payload.codigo_salida !== undefined) {
      appendLog(`--- carga-verificacion-semana cierre ${payload.codigo_salida}${traceId ? ` trace=${traceId}` : ''} ---`);
    } else if (payload.mensaje) {
      appendLog(`--- carga-verificacion-semana error${traceId ? ` trace=${traceId}` : ''}: ` + payload.mensaje);
    }
    res.json(payload);
  };

  child.stdout.on('data', (d) => {
    const s = d.toString();
    if (stdout.length < maxChunk) stdout += s;
    appendLog(s.replace(/\r/g, ''));
  });
  child.stderr.on('data', (d) => {
    const s = d.toString();
    if (stderr.length < maxChunk) stderr += s;
    appendLog('[carga-verificacion stderr] ' + s.replace(/\r/g, ''));
  });
  child.on('error', (err) => {
    appendLog(`[carga-verificacion spawn${traceId ? ` trace=${traceId}` : ''}] ` + (err.message || String(err)));
    void notifyEcWorkflowWebhook('lista_negra_error', traceId, [
      `archivo=${archivoLog}`,
      `error=${String(err?.message || err)}`,
    ]);
    if (origenCarpeta === 'reporte' && !dryRun) {
      setEstadoReporteArchivo(archivoLog, 'generado');
    }
    enviar({
      success: false,
      mensaje: err.message || String(err),
      estado_reporte:
        origenCarpeta === 'reporte' && !dryRun ? payloadEstadoReporte(archivoLog, 'generado') : null,
      traceId: traceId || null,
    });
  });
  child.on('close', (code) => {
    let estadoRepPayload = null;
    if (origenCarpeta === 'reporte' && !dryRun) {
      if (code === 0) {
        setEstadoReporteArchivo(archivoLog, 'listaNegra');
        estadoRepPayload = payloadEstadoReporte(archivoLog, 'listaNegra');
      } else {
        setEstadoReporteArchivo(archivoLog, 'generado');
        estadoRepPayload = payloadEstadoReporte(archivoLog, 'generado');
      }
    }
    enviar({
      success: code === 0,
      codigo_salida: code,
      stdout: stdout.slice(-8000),
      stderr: stderr.slice(-8000),
      archivo: archivoLog,
      estado_reporte: estadoRepPayload,
      traceId: traceId || null,
    });
    const resumenLn = parseListaNegraResumenFromStdout(stdout);
    void notifyListaNegraFinResumenWebhook(traceId, archivoLog, code, dryRun, resumenLn);
  });
});

/** Descargo incremental estatus=3 (Python → Excel + guia_descargo.json en reporte/descargo_estatus3/). */
app.post('/descargo-estatus3/run', express.json({ limit: '32kb' }), (req, res) => {
  ensureReporteDir();
  const scriptPath = getDescargoEstatus3ScriptPath();
  if (!scriptPath) {
    return res.status(500).json({
      success: false,
      mensaje:
        'No hay scripts/descargo_cobranza_gc_estatus3.py ni DESCARGO_ESTATUS3_SCRIPT en .env.',
    });
  }

  const megaPhpDefaults = req.body?.megaPhpDefaults !== false;
  const desdeCero = !!req.body?.desdeCero;
  const sinActualizarGuia = !!req.body?.sinActualizarGuia;

  const args = [scriptPath, '--datos-dir', DESCARGO_ESTATUS3_DIR];
  if (megaPhpDefaults) args.push('--mega-php-defaults');
  else args.push('--no-mega-php-defaults');
  if (desdeCero) args.push('--desde-cero');
  if (sinActualizarGuia) args.push('--sin-actualizar-guia');

  const cwd = path.dirname(scriptPath);
  appendLog(
    `--- descargo-estatus3 megaPhpDefaults=${megaPhpDefaults} desdeCero=${desdeCero} sinGuia=${sinActualizarGuia} dir=${DESCARGO_ESTATUS3_DIR} ---`
  );

  const child = spawn(REPORTE_PYTHON, args, {
    cwd,
    env: ENV_CON_PYTHON_UNBUFFERED,
    windowsHide: true,
    shell: false,
  });

  let stdout = '';
  let stderr = '';
  const maxChunk = 512 * 1024;
  let respondio = false;
  const enviar = (payload) => {
    if (respondio) return;
    respondio = true;
    if (payload.codigo_salida !== undefined) {
      appendLog(`--- descargo-estatus3 cierre ${payload.codigo_salida} ---`);
    } else if (payload.mensaje) {
      appendLog('--- descargo-estatus3 error: ' + payload.mensaje);
    }
    res.json(payload);
  };

  child.stdout.on('data', (d) => {
    const s = d.toString();
    stdout += s;
    if (stdout.length > maxChunk) stdout = stdout.slice(-maxChunk);
    appendLog(s.replace(/\r/g, ''));
  });
  child.stderr.on('data', (d) => {
    const s = d.toString();
    stderr += s;
    if (stderr.length > maxChunk) stderr = stderr.slice(-maxChunk);
    appendLog('[descargo-estatus3 stderr] ' + s.replace(/\r/g, ''));
  });
  child.on('error', (err) => {
    appendLog('[descargo-estatus3 spawn] ' + (err.message || String(err)));
    enviar({ success: false, mensaje: err.message || String(err) });
  });
  child.on('close', (code) => {
    enviar({
      success: code === 0,
      codigo_salida: code,
      stdout: stdout.slice(-8000),
      stderr: stderr.slice(-8000),
    });
  });
});

/**
 * Mismo descargo que /run; si terminó bien y hubo Excel nuevo, responde el .xlsx (descarga directa).
 * Si no hubo filas nuevas o error, JSON (el front muestra mensaje / log del agente).
 */
app.post('/descargo-estatus3/run-and-download', express.json({ limit: '32kb' }), (req, res) => {
  ensureReporteDir();
  const scriptPath = getDescargoEstatus3ScriptPath();
  if (!scriptPath) {
    return res.status(500).json({
      success: false,
      mensaje:
        'No hay scripts/descargo_cobranza_gc_estatus3.py ni DESCARGO_ESTATUS3_SCRIPT en .env.',
    });
  }

  const megaPhpDefaults = req.body?.megaPhpDefaults !== false;
  const desdeCero = !!req.body?.desdeCero;
  const sinActualizarGuia = !!req.body?.sinActualizarGuia;

  const args = [scriptPath, '--datos-dir', DESCARGO_ESTATUS3_DIR];
  if (megaPhpDefaults) args.push('--mega-php-defaults');
  else args.push('--no-mega-php-defaults');
  if (desdeCero) args.push('--desde-cero');
  if (sinActualizarGuia) args.push('--sin-actualizar-guia');

  const cwd = path.dirname(scriptPath);
  appendLog(
    `--- descargo-estatus3/run-and-download megaPhpDefaults=${megaPhpDefaults} desdeCero=${desdeCero} sinGuia=${sinActualizarGuia} ---`
  );

  const child = spawn(REPORTE_PYTHON, args, {
    cwd,
    env: ENV_CON_PYTHON_UNBUFFERED,
    windowsHide: true,
    shell: false,
  });

  let stdout = '';
  let stderr = '';
  const maxChunk = 512 * 1024;
  let respondio = false;
  const enviarJson = (payload) => {
    if (respondio) return;
    respondio = true;
    appendLog(`--- descargo-estatus3/run-and-download cierre json ${payload.codigo_salida ?? ''} ---`);
    res.json(payload);
  };

  child.stdout.on('data', (d) => {
    const s = d.toString();
    stdout += s;
    if (stdout.length > maxChunk) stdout = stdout.slice(-maxChunk);
    appendLog(s.replace(/\r/g, ''));
  });
  child.stderr.on('data', (d) => {
    const s = d.toString();
    stderr += s;
    if (stderr.length > maxChunk) stderr = stderr.slice(-maxChunk);
    appendLog('[descargo-estatus3 stderr] ' + s.replace(/\r/g, ''));
  });
  child.on('error', (err) => {
    appendLog('[descargo-estatus3 spawn] ' + (err.message || String(err)));
    enviarJson({ success: false, mensaje: err.message || String(err) });
  });
  child.on('close', (code) => {
    if (respondio) return;
    const outS = stdout.slice(-8000);
    const errS = stderr.slice(-8000);
    if (code !== 0) {
      return enviarJson({
        success: false,
        codigo_salida: code,
        stdout: outS,
        stderr: errS,
        mensaje: 'El script terminó con error.',
      });
    }
    const sinExcelNuevo = /No hubo filas nuevas/i.test(stdout) || /no toco la gu[ií]a anterior ni sobreescribo el excel/i.test(stdout);
    const xlsxPath = path.join(DESCARGO_ESTATUS3_DIR, 'descargo_estatus3.xlsx');
    if (sinExcelNuevo || !fs.existsSync(xlsxPath) || !fs.statSync(xlsxPath).isFile()) {
      return enviarJson({
        success: true,
        sin_excel: true,
        mensaje: sinExcelNuevo
          ? 'No hubo filas nuevas en esta corrida; no se generó Excel nuevo (la guía no cambió).'
          : 'No se encontró el archivo Excel tras el descargo.',
        stdout: outS,
        stderr: errS,
        codigo_salida: 0,
      });
    }
    respondio = true;
    appendLog('--- descargo-estatus3/run-and-download enviando Excel al navegador ---');
    return res.download(xlsxPath, 'descargo_estatus3.xlsx');
  });
});

/** Descarga segura del Excel o la guía JSON generados por descargo estatus 3. */
app.get('/descargo-estatus3/descargar', (req, res) => {
  ensureReporteDir();
  const tipo = String(req.query.tipo || 'xlsx').toLowerCase();
  const guiaNombre = descargoGuiaBasename();
  const nombre = tipo === 'guia' ? guiaNombre : 'descargo_estatus3.xlsx';
  if (tipo !== 'guia' && tipo !== 'xlsx') {
    return res.status(400).json({ success: false, mensaje: 'tipo inválido (use xlsx o guia).' });
  }
  if (nombre !== 'descargo_estatus3.xlsx' && nombre !== guiaNombre) {
    return res.status(400).json({ success: false, mensaje: 'tipo inválido (use xlsx o guia).' });
  }
  const p = path.join(DESCARGO_ESTATUS3_DIR, nombre);
  if (!fs.existsSync(p) || !fs.statSync(p).isFile()) {
    return res.status(404).json({ success: false, mensaje: 'Archivo no encontrado. Ejecute el descargo primero.' });
  }
  const ct =
    nombre.endsWith('.json') ? 'application/json; charset=utf-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
  res.setHeader('Content-Type', ct);
  return res.download(p, nombre);
});

ensureLogDir();
ensureReporteDir();
if (String(process.env.GASTOS_COBRANZA_LOG_CLEAR_ON_START || '0').trim() === '1') {
  clearLogFile(false);
}
appendLog(
  `[servicio] gastos-cobranza-agent arranque puerto ${PORT} | script=${getReporteScriptPath() || 'ninguno'} | reporte=${REPORTE_DIR}`
);

app.listen(PORT, () => {
  console.log('[gastos-cobranza-agent] escuchando en', PORT);
  if (remoteCdmxTimeEnabled()) {
    void ensureCdmxTimeFresh();
    setInterval(() => {
      void ensureCdmxTimeFresh();
    }, 10 * 60 * 1000);
  }
  appendLog(
    `[auto-run] Tick cada ${autoRunTickMs()} ms · efectivo=${autoRun10amCdmxEffective() ? 'sí' : 'no'} · env=${autoRun10amCdmxEnabled() ? 'sí' : 'no'} · override=${readAutoRunRuntimeOverride() === null ? '—' : readAutoRunRuntimeOverride() ? '1' : '0'}`,
  );
  if (autoRun10amCdmxEffective()) {
    if (!remoteCdmxTimeEnabled()) {
      appendLog(
        '[auto-run] Aviso: GASTOS_GC_REMOTE_CDMX_TIME no está en 1; la hora CDMX depende del reloj del servidor. Si va desfasado, ponga GASTOS_GC_REMOTE_CDMX_TIME=1 o GASTOS_GC_AUTO_RUN_SYNC_REMOTE_CLOCK=1 (sync solo en ticks del auto-run).',
      );
    }
  } else if (!autoRun10amCdmxEnabled() && readAutoRunRuntimeOverride() === null) {
    console.log('[gastos-cobranza-agent] auto-run CDMX desactivado por .env (GASTOS_GC_AUTO_RUN_10AM_CDMX=0)');
  }
  setInterval(() => {
    tickAutoRunReporteCdmx().catch((e) => appendLog(`[auto-run] error tick: ${e?.message || e}`));
  }, autoRunTickMs());
  tickAutoRunReporteCdmx().catch((e) => appendLog(`[auto-run] error tick: ${e?.message || e}`));
});
