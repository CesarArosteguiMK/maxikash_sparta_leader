/**
 * API Agente Segundómetro.
 * Expone los mismos comportamientos que el Shell Segundómetro de Sparta Ledger pero
 * ejecutando SSH/SFTP desde aquí (con su propia clave .unknown). El servidor PHP
 * no necesita Plink, SSH ni permisos en carpetas; solo llama a esta API por HTTP.
 *
 * Uso en pruebas: levantar este servicio y usar la página test.html o curl.
 * Cuando funcione al 100%, se integrará en Sparta Ledger (sin tocar nada hasta entonces).
 */

const path = require('path');
const fs = require('fs');
try { require('dotenv').config({ path: path.join(__dirname, '.env') }); } catch (_) {}

const express = require('express');
const { runCommand, downloadFile, downloadFileStream, runCommandStream } = require('./lib/sshClient');
const autoCopyConfig = require('./lib/autoCopyConfig');
const { getAccurateCdmxNow, getCdmxLocalSync } = require('./lib/cdmxTime');
const CDMX_WARN_LOG_COOLDOWN_MS = Math.max(30000, parseInt(process.env.CDMX_WARN_LOG_COOLDOWN_MS || '120000', 10) || 120000);
let lastCdmxWarnLogMs = 0;

/**
 * Hora CDMX para respuestas HTTP: nunca lanza (evita tumbar el proceso Node si
 * worldtimeapi/timeapi.io fallan y ALLOW_LOCAL_TIME_FALLBACK=0).
 * El Shell llama a GET /auto-copy justo después de /health; un rechazo no
 * manejado aquí dejaba el agente muerto y el puerto 3100 vacío al recargar.
 */
async function getCdmxNowForHttp() {
  try {
    return await getAccurateCdmxNow();
  } catch (e) {
    const now = Date.now();
    if ((now - lastCdmxWarnLogMs) >= CDMX_WARN_LOG_COOLDOWN_MS) {
      console.warn('[cdmx] Fuentes remotas no disponibles; usando reloj local:', e.message);
      lastCdmxWarnLogMs = now;
    }
    return getCdmxLocalSync();
  }
}

process.on('unhandledRejection', (reason) => {
  console.error('[agente] unhandledRejection:', reason);
});
process.on('uncaughtException', (err) => {
  console.error('[agente] uncaughtException:', err);
});

const app = express();
const PORT = process.env.PORT || 3100;
const REMOTE_DIR = process.env.REMOTE_DIR || '/home/usuariossftp/s2/mega_reporte';
const MONITOREAR_SCRIPT = process.env.MONITOREAR_SCRIPT || '/home/jesus/scripts/monitorear.sh';
const API_KEY = process.env.API_KEY || '';
/** Ruta del front PHP para estado BD (misma app que /segundometro/shell). */
const ESTADO_BD_PATH = 'index.php?url=segundometro/estadoReportesAgente';
/** Ruta del front PHP para truncar automático (ejecución interna del agente). */
const TRUNCAR_AUTO_PATH = 'index.php?url=segundometro/truncarAutomaticoAgente';
const SEGUNDOMETRO_AGENT_KEY = process.env.SEGUNDOMETRO_AGENT_KEY || '';
/** URL que funcionó la última vez (evita probar en cada request). */
let estadoBdUrlCached = null;
let truncarAutoUrlCached = null;

/**
 * URLs candidatas para POST estadoReportesAgente.
 * - Si .env tiene varias separadas por coma, solo esas (en orden).
 * - Si .env tiene una sola, esa primero y luego autodetección.
 * - Si .env vacío, solo autodetección (localhost:8086, 127.0.0.1, etc.).
 */
function getEstadoBdUrlCandidates() {
  const fromEnv = (process.env.SEGUNDOMETRO_ESTADO_BD_URL || '').trim();
  const seen = new Set();
  const list = [];
  const push = (u) => {
    const s = String(u || '').trim();
    if (!s || seen.has(s)) return;
    seen.add(s);
    list.push(s);
  };
  if (fromEnv.includes(',')) {
    fromEnv.split(',').forEach((s) => push(s.trim()));
    return list;
  }
  if (fromEnv) push(fromEnv);
  [
    'http://localhost:8086/' + ESTADO_BD_PATH,
    'http://127.0.0.1:8086/' + ESTADO_BD_PATH,
    'http://localhost/' + ESTADO_BD_PATH,
    'http://127.0.0.1/' + ESTADO_BD_PATH,
  ].forEach(push);
  return list;
}

/**
 * POST al endpoint estado BD; prueba candidatas hasta obtener JSON con forma esperada.
 * Respuesta PHP suele incluir success y/o estados.
 */
async function postEstadoBdJson(body, signal) {
  const headers = {
    'Content-Type': 'application/json',
    'Front-Request': 'true',
  };
  if (SEGUNDOMETRO_AGENT_KEY) headers['X-Agent-Key'] = SEGUNDOMETRO_AGENT_KEY;

  const candidates = getEstadoBdUrlCandidates();
  if (estadoBdUrlCached && !candidates.includes(estadoBdUrlCached)) {
    candidates.unshift(estadoBdUrlCached);
  } else if (estadoBdUrlCached) {
    candidates.splice(candidates.indexOf(estadoBdUrlCached), 1);
    candidates.unshift(estadoBdUrlCached);
  }

  let lastErr = null;
  for (const url of candidates) {
    try {
      const r = await fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(body),
        signal,
      });
      const text = await r.text();
      let data = null;
      try {
        data = text ? JSON.parse(text) : null;
      } catch (_) {
        lastErr =
          'No JSON en ' +
          url +
          ' (HTTP ' +
          r.status +
          '): ' +
          String(text).slice(0, 80);
        continue;
      }
      if (!data || typeof data !== 'object') {
        lastErr = 'JSON inesperado en ' + url;
        continue;
      }
      if (data.estados === undefined && data.success === undefined && data.mensaje === undefined) {
        lastErr = 'JSON no parece estadoReportesAgente en ' + url;
        continue;
      }
      if (estadoBdUrlCached !== url) {
        console.log('[estado BD] URL automática:', url);
      }
      estadoBdUrlCached = url;
      return { r, data, text, url };
    } catch (e) {
      lastErr = url + ': ' + e.message;
    }
  }
  estadoBdUrlCached = null;
  return {
    r: null,
    data: null,
    text: null,
    url: null,
    error: lastErr || 'Ninguna URL candidata respondió JSON válido. Configure SEGUNDOMETRO_ESTADO_BD_URL o revise Apache/puerto.',
  };
}

function getTruncarAutoUrlCandidates() {
  const fromEnv = (process.env.SEGUNDOMETRO_TRUNCAR_AUTO_URL || '').trim();
  const seen = new Set();
  const list = [];
  const push = (u) => {
    const s = String(u || '').trim();
    if (!s || seen.has(s)) return;
    seen.add(s);
    list.push(s);
  };
  if (fromEnv.includes(',')) {
    fromEnv.split(',').forEach((s) => push(s.trim()));
    return list;
  }
  if (fromEnv) {
    push(fromEnv);
  } else {
    getEstadoBdUrlCandidates().forEach((u) => {
      if (u.includes(ESTADO_BD_PATH)) {
        push(u.replace(ESTADO_BD_PATH, TRUNCAR_AUTO_PATH));
      }
    });
    [
      'http://localhost:8086/' + TRUNCAR_AUTO_PATH,
      'http://127.0.0.1:8086/' + TRUNCAR_AUTO_PATH,
      'http://localhost/' + TRUNCAR_AUTO_PATH,
      'http://127.0.0.1/' + TRUNCAR_AUTO_PATH,
    ].forEach(push);
  }
  return list;
}

async function postTruncarAutomaticoAgente(body, signal) {
  const headers = {
    'Content-Type': 'application/json',
    'Front-Request': 'true',
  };
  if (SEGUNDOMETRO_AGENT_KEY) headers['X-Agent-Key'] = SEGUNDOMETRO_AGENT_KEY;

  const candidates = getTruncarAutoUrlCandidates();
  if (truncarAutoUrlCached && !candidates.includes(truncarAutoUrlCached)) {
    candidates.unshift(truncarAutoUrlCached);
  } else if (truncarAutoUrlCached) {
    candidates.splice(candidates.indexOf(truncarAutoUrlCached), 1);
    candidates.unshift(truncarAutoUrlCached);
  }

  let lastErr = null;
  for (const url of candidates) {
    try {
      const r = await fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(body || {}),
        signal,
      });
      const text = await r.text();
      let data = null;
      try {
        data = text ? JSON.parse(text) : null;
      } catch (_) {
        lastErr = 'No JSON en ' + url + ' (HTTP ' + r.status + '): ' + String(text).slice(0, 120);
        continue;
      }
      if (!data || typeof data !== 'object') {
        lastErr = 'JSON inesperado en ' + url;
        continue;
      }
      if (truncarAutoUrlCached !== url) {
        console.log('[truncar-auto] URL automática:', url);
      }
      truncarAutoUrlCached = url;
      return { r, data, text, url };
    } catch (e) {
      lastErr = url + ': ' + e.message;
    }
  }
  truncarAutoUrlCached = null;
  return {
    r: null,
    data: null,
    text: null,
    url: null,
    error: lastErr || 'Ninguna URL candidata respondió JSON válido para truncar automático.',
  };
}

const AUTO_COPY_MAIN_ENABLED = (process.env.AUTO_COPY_MAIN_ENABLED || '0') === '1';

const MEGA_RPT_REGEX = /mega_rpt_(\d{8})_(\d{2})_(\d{2})_(\d{2})\.csv\.zip/;
const MEGA_RPT_FULL = /^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/;
const FALLBACK_DB_CHECK_MAP = {
  '07:40': { hh: '07', mm: '32', dayOffset: 0 },
  '09:40': { hh: '09', mm: '32', dayOffset: 0 },
  '11:40': { hh: '11', mm: '32', dayOffset: 0 },
  '13:40': { hh: '13', mm: '32', dayOffset: 0 },
  '14:40': { hh: '14', mm: '32', dayOffset: 0 },
  '16:40': { hh: '16', mm: '32', dayOffset: 0 },
  '18:40': { hh: '18', mm: '32', dayOffset: 0 },
  '20:40': { hh: '20', mm: '32', dayOffset: 0 },
  // Nocturno (distinto de xx:40): el ZIP en disco es mega_rpt_*_23_52_00; en la tabla semana los datos de ese corte viven en columnas tipo Dias_mora_*_23_50.
  // Comprobación BD a las 23:52 CDMX (mismo día del reporte). 00:05 del día siguiente = segunda ventana por si el poll no alcanzó 23:52–23:57.
  '23:52': { hh: '23', mm: '52', dayOffset: 0 },
  '00:05': { hh: '23', mm: '52', dayOffset: -1 },
};

function pad2(n) { return String(n).padStart(2, '0'); }
function ymdCompact(yyyyMmDd) { return String(yyyyMmDd || '').replace(/-/g, ''); }
function sumarDiasYmd(fechaYYYYMMDD, deltaDias) {
  const p = String(fechaYYYYMMDD || '').split('-');
  if (p.length !== 3) return fechaYYYYMMDD;
  const d = new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
  d.setDate(d.getDate() + deltaDias);
  return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
}

function dayOfWeekFromYmd(fechaYYYYMMDD) {
  const p = String(fechaYYYYMMDD || '').split('-');
  if (p.length !== 3) return null;
  const y = parseInt(p[0], 10);
  const m = parseInt(p[1], 10);
  const d = parseInt(p[2], 10);
  if (!Number.isFinite(y) || !Number.isFinite(m) || !Number.isFinite(d)) return null;
  return new Date(Date.UTC(y, m - 1, d, 12, 0, 0)).getUTCDay();
}

function esMartesFecha(fechaYYYYMMDD) {
  return dayOfWeekFromYmd(fechaYYYYMMDD) === 2;
}

/** Minutos desde medianoche (0–1439) desde objeto hora CDMX (`hora` HH:MM o `horaSeg` HH:MM:SS). */
function minutosDesdeMedianocheDesdeNowCdmx(nowCdmx) {
  const seg = String((nowCdmx && nowCdmx.horaSeg) || '');
  const hm = String((nowCdmx && nowCdmx.hora) || '');
  const raw = seg.split(':').length >= 3 ? seg : (hm ? hm + ':00' : '');
  const p = raw.split(':');
  if (p.length < 2) return null;
  const h = parseInt(p[0], 10);
  const m = parseInt(p[1], 10);
  if (!Number.isFinite(h) || !Number.isFinite(m)) return null;
  if (h < 0 || h > 23 || m < 0 || m > 59) return null;
  return h * 60 + m;
}

/** Martes CDMX entre 07:00 y 07:29 (ventana para scheduler cada 5 min). */
function esVentanaTruncarMartesCdmx(nowCdmx) {
  if (!esMartesFecha(nowCdmx.fecha)) return false;
  const mm = minutosDesdeMedianocheDesdeNowCdmx(nowCdmx);
  if (mm === null) return false;
  return mm >= 7 * 60 && mm < 7 * 60 + 30;
}

/** Martes CDMX antes de las 07:30: la UI no lista reportes (la semana operativa arranca con el slot 07:30). */
function esMartesAntesDe0730Cdmx(nowCdmx) {
  if (!esMartesFecha(nowCdmx.fecha)) return false;
  const mm = minutosDesdeMedianocheDesdeNowCdmx(nowCdmx);
  if (mm === null) return false;
  return mm < 7 * 60 + 30;
}

/** Ventana tras cada slot HH:MM para que un poll cada 5 min no pierda el disparo. */
const SLOT_VENTANA_MINUTOS = 6;

function fallbackSlotParaNowCdmx(nowCdmx) {
  const cur = minutosDesdeMedianocheDesdeNowCdmx(nowCdmx);
  if (cur === null) return null;
  for (const slotKey of Object.keys(FALLBACK_DB_CHECK_MAP)) {
    const parts = slotKey.split(':');
    if (parts.length !== 2) continue;
    const sh = parseInt(parts[0], 10);
    const sm = parseInt(parts[1], 10);
    if (!Number.isFinite(sh) || !Number.isFinite(sm)) continue;
    const slotStart = sh * 60 + sm;
    if (cur >= slotStart && cur < slotStart + SLOT_VENTANA_MINUTOS) {
      return { slotKey, meta: FALLBACK_DB_CHECK_MAP[slotKey] };
    }
  }
  return null;
}

function horarioAutoCopyParaNowCdmx(config, nowCdmx) {
  if (!config || !Array.isArray(config.horarios)) return null;
  const cur = minutosDesdeMedianocheDesdeNowCdmx(nowCdmx);
  if (cur === null) return null;
  for (const slot of config.horarios) {
    const parts = String(slot).split(':');
    if (parts.length !== 2) continue;
    const sh = parseInt(parts[0], 10);
    const sm = parseInt(parts[1], 10);
    if (!Number.isFinite(sh) || !Number.isFinite(sm)) continue;
    const slotStart = sh * 60 + sm;
    if (cur >= slotStart && cur < slotStart + SLOT_VENTANA_MINUTOS) return String(slot);
  }
  return null;
}

function proximaEjecucionByNow(config, nowCdmx) {
  if (!config || !config.enabled || !Array.isArray(config.horarios) || !config.horarios.length) return null;
  const ordenados = [...config.horarios].sort();
  const hora = nowCdmx.hora;
  const fecha = nowCdmx.fecha;
  for (const slot of ordenados) {
    if (slot > hora) return { fecha, hora: slot, label: fecha + ' ' + slot };
  }
  const manana = sumarDiasYmd(fecha, 1);
  return { fecha: manana, hora: ordenados[0], label: manana + ' ' + ordenados[0] };
}

function middlewareApiKey(req, res, next) {
  if (!API_KEY) return next();
  const key = req.get('X-Api-Key') || req.query.api_key || '';
  if (key !== API_KEY) {
    return res.status(401).json({ success: false, mensaje: 'API key inválida o faltante.' });
  }
  next();
}

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(middlewareApiKey);

app.use((req, res, next) => {
  res.set('Access-Control-Allow-Origin', '*');
  res.set('Access-Control-Allow-Methods', 'GET, POST, DELETE, OPTIONS');
  res.set('Access-Control-Allow-Headers', 'Content-Type, X-Api-Key');
  if (req.method === 'OPTIONS') return res.sendStatus(204);
  next();
});

app.use(express.static(path.join(__dirname, 'public')));

function formatSize(bytes) {
  const units = ['B', 'KB', 'MB', 'GB'];
  let i = 0;
  let n = bytes;
  while (n >= 1024 && i < units.length - 1) { n /= 1024; i++; }
  return n.toFixed(2) + ' ' + units[i];
}

function parseListOutput(output, refCdmx, options = {}) {
  const archivos = [];
  const hoyStr = refCdmx && refCdmx.fecha ? refCdmx.fecha : '1970-01-01';
  const ayerStr = sumarDiasYmd(hoyStr, -1);
  const fechalimite = ymdCompact(ayerStr);
  const mesTexto = options.mesTexto || '';
  const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

  const lineas = output.split('\n').map(l => l.trim()).filter(Boolean);
  for (const linea of lineas) {
    const partes = linea.split(/\s+/);
    if (partes.length < 9) continue;
    const owner = partes[2];
    const sizeBytes = parseInt(partes[4], 10) || 0;
    const nombreArchivo = partes.slice(8).join(' ').replace(/.*\//, '') || partes[8];
    const match = nombreArchivo.match(MEGA_RPT_REGEX);
    if (!match) continue;
    const [, fechaArchivo, hora, minuto, segundo] = match;
    if (mesTexto) {
      if (!fechaArchivo.startsWith(mesTexto)) continue;
    } else if (fechaArchivo < fechalimite) {
      continue;
    }

    const y = fechaArchivo.slice(0, 4), m = fechaArchivo.slice(4, 6), d = fechaArchivo.slice(6, 8);
    const fechaObj = y + '-' + m + '-' + d;
    const dNum = new Date(parseInt(y, 10), parseInt(m, 10) - 1, parseInt(d, 10));
    const mes = meses[dNum.getMonth()];
    let fechaDisplay;
    if (fechaObj === hoyStr) fechaDisplay = 'Hoy - ' + d + ' de ' + mes + ' de ' + y;
    else if (fechaObj === ayerStr) fechaDisplay = 'Ayer - ' + d + ' de ' + mes + ' de ' + y;
    else fechaDisplay = d + ' de ' + mes + ' de ' + y;

    archivos.push({
      nombre: nombreArchivo,
      owner,
      ruta_completa: REMOTE_DIR + '/' + nombreArchivo,
      fecha: fechaObj,
      fecha_formato: pad2(dNum.getDate()) + '/' + pad2(dNum.getMonth() + 1) + '/' + dNum.getFullYear(),
      fecha_display: fechaDisplay,
      hora: hora + ':' + minuto + ':' + segundo,
      tamano: formatSize(sizeBytes),
      tamano_bytes: sizeBytes,
      timestamp: new Date(fechaObj + 'T' + hora + ':' + minuto + ':' + segundo).getTime(),
    });
  }
  archivos.sort((a, b) => b.timestamp - a.timestamp);
  return archivos;
}

/** Nombre destino mega_rpt +1s en el segundo (misma regla que Copiar +1s). */
function nombreDestinoPlusOneSeg(nombreArchivo) {
  const match = String(nombreArchivo || '').match(MEGA_RPT_REGEX);
  if (!match) return null;
  let [, fecha, h, min, seg] = match;
  seg = parseInt(seg, 10) + 1;
  let hi = parseInt(h, 10);
  let mini = parseInt(min, 10);
  if (seg >= 60) {
    seg = 0;
    mini++;
  }
  if (mini >= 60) {
    mini = 0;
    hi++;
  }
  if (hi >= 24) {
    hi = 0;
    const d = new Date(parseInt(fecha.slice(0, 4), 10), parseInt(fecha.slice(4, 6), 10) - 1, parseInt(fecha.slice(6, 8), 10));
    d.setDate(d.getDate() + 1);
    fecha = d.getFullYear() + pad2(d.getMonth() + 1) + pad2(d.getDate());
  }
  return 'mega_rpt_' + fecha + '_' + pad2(hi) + '_' + pad2(mini) + '_' + pad2(seg) + '.csv.zip';
}

/**
 * Verifica si un ZIP existe en el directorio remoto.
 * Evita sobreescribir el mismo +1s y re-disparar procesos aguas abajo.
 */
async function remoteZipExists(nombreArchivo) {
  const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
  const fileEsc = String(nombreArchivo || '').replace(/'/g, "'\\''");
  const r = await runCommand(`cd '${dirEsc}' && test -e '${fileEsc}' && echo EXISTS || true`, {
    timeoutMs: 15000,
    retries: 0,
    readyTimeoutMs: 8000,
  });
  if (!r.success) return { ok: false, exists: false, error: r.error || 'Error SSH' };
  return { ok: true, exists: String(r.output || '').includes('EXISTS'), error: '' };
}

/** Ejecuta Copiar +1s automático: opcionalmente corre monitoreo 8s, lista archivos, copia el más reciente +1s. */
async function runAutoCopyJob(slot, fechaCdmx) {
  const esPrueba = slot === 'PRUEBA';
  const config = autoCopyConfig.readConfig();
  const MONITOREAR_SCRIPT_LOCAL = process.env.MONITOREAR_SCRIPT || '/home/jesus/scripts/monitorear.sh';
  const postCloseSec = Math.max(10, parseInt(process.env.MONITOREAR_POST_CLOSE_SEC || '15', 10) || 15);
  let monitorPid = null;

  async function closeMonitorAfter(delaySec) {
    if (!monitorPid) return;
    const pid = String(monitorPid).replace(/[^\d]/g, '');
    if (!pid) return;
    try {
      await runCommand('sleep ' + delaySec + ' && sudo kill -TERM ' + pid + ' 2>/dev/null || true');
      console.log('[auto-copy] Monitoreo previo cerrado (PID ' + pid + ')');
    } catch (e) {
      console.warn('[auto-copy] No se pudo cerrar monitoreo (PID ' + pid + '):', e.message);
    } finally {
      monitorPid = null;
    }
  }

  // Monitoreo previo obligatorio:
  // Inicia el script en background y espera un "warmup" corto antes de listar/copiar.
  // Asi evitamos que la accion quede bloqueada varios minutos.
  if (config.preRunMonitoreo && MONITOREAR_SCRIPT_LOCAL) {
    try {
      const scriptEsc = MONITOREAR_SCRIPT_LOCAL.replace(/'/g, "'\\''");
      const warmup = Math.max(3, parseInt(process.env.MONITOREAR_PRE_WARMUP_SEC || '5', 10) || 5);
      console.log('[auto-copy] Monitoreo previo: iniciando background + warmup', warmup, 's');
      const cmdMon = "nohup sudo bash '" + scriptEsc + "' >/dev/null 2>&1 & echo MON_PID:$!; sleep " + warmup;
      const monResult = await runCommand(cmdMon);
      if (!monResult.success) {
        const result = { success: false, mensaje: 'No se pudo iniciar monitoreo previo: ' + (monResult.error || 'error desconocido'), esPrueba };
        autoCopyConfig.updateLastRun(slot, fechaCdmx, { ...result, fecha: fechaCdmx, hora: slot });
        console.log('[auto-copy]', slot, result.mensaje);
        return result;
      }
      const pidMatch = (monResult.output || '').match(/MON_PID:(\d+)/);
      if (pidMatch && pidMatch[1]) {
        monitorPid = pidMatch[1];
        console.log('[auto-copy] Monitoreo previo activo (PID ' + monitorPid + ')');
      } else {
        console.warn('[auto-copy] Monitoreo previo: no se pudo capturar PID, no se cerrará automáticamente');
      }
    } catch (e) {
      const result = { success: false, mensaje: 'No se pudo iniciar monitoreo previo: ' + e.message, esPrueba };
      autoCopyConfig.updateLastRun(slot, fechaCdmx, { ...result, fecha: fechaCdmx, hora: slot });
      console.log('[auto-copy]', slot, result.mensaje);
      return result;
    }
  }

  const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
  const lsResult = await runCommand(`cd '${dirEsc}' && ls -l mega_rpt_*.csv.zip 2>/dev/null`);
  if (!lsResult.success) {
    await closeMonitorAfter(1);
    const result = { success: false, mensaje: 'No se pudo listar: ' + (lsResult.error || 'Error SSH'), esPrueba };
    autoCopyConfig.updateLastRun(slot, fechaCdmx, { ...result, fecha: fechaCdmx, hora: slot });
    console.log('[auto-copy]', slot, result.mensaje);
    return result;
  }

  const archivos = parseListOutput(lsResult.output, { fecha: fechaCdmx });
  if (!archivos.length) {
    await closeMonitorAfter(1);
    const result = { success: false, mensaje: 'No hay archivos recientes para copiar', esPrueba };
    autoCopyConfig.updateLastRun(slot, fechaCdmx, { ...result, fecha: fechaCdmx, hora: slot });
    console.log('[auto-copy]', slot, result.mensaje);
    return result;
  }

  const nombreArchivo = archivos[0].nombre;
  const nombreDestino = nombreDestinoPlusOneSeg(nombreArchivo);
  if (!nombreDestino) {
    await closeMonitorAfter(1);
    const result = { success: false, mensaje: 'Formato de archivo inválido', esPrueba };
    autoCopyConfig.updateLastRun(slot, fechaCdmx, { ...result, fecha: fechaCdmx, hora: slot });
    return result;
  }

  const fileEsc = (s) => s.replace(/'/g, "'\\''");
  const existsCheck = await remoteZipExists(nombreDestino);
  if (!existsCheck.ok) {
    await closeMonitorAfter(1);
    const result = { success: false, mensaje: 'No se pudo verificar destino: ' + (existsCheck.error || 'Error SSH'), origen: nombreArchivo, destino: nombreDestino, esPrueba };
    autoCopyConfig.updateLastRun(slot, fechaCdmx, { ...result, fecha: fechaCdmx, hora: slot });
    return result;
  }
  if (existsCheck.exists) {
    await closeMonitorAfter(1);
    const result = {
      success: true,
      mensaje: 'Destino ya existía; se evita sobreescribir para no re-disparar proceso.',
      origen: nombreArchivo,
      destino: nombreDestino,
      fecha: fechaCdmx,
      hora: slot,
      esPrueba,
      yaExistiaDestino: true,
    };
    autoCopyConfig.updateLastRun(slot, fechaCdmx, result);
    console.log('[auto-copy]', slot, result.mensaje, result.origen, '->', result.destino);
    return result;
  }
  const cmd = `cd '${dirEsc}' && sudo cp '${fileEsc(nombreArchivo)}' '${fileEsc(nombreDestino)}'`;
  const copyResult = await runCommand(cmd);
  if (!copyResult.success) {
    await closeMonitorAfter(1);
    const result = { success: false, mensaje: copyResult.error || 'Error al copiar', origen: nombreArchivo, destino: nombreDestino, esPrueba };
    autoCopyConfig.updateLastRun(slot, fechaCdmx, { ...result, fecha: fechaCdmx, hora: slot });
    console.log('[auto-copy]', slot, result.mensaje);
    return result;
  }

  // Verificación robusta: en algunos casos la red/SSH falla justo en el segundo comando
  // aunque el archivo sí se creó. Reintentar unas veces antes de marcar error.
  let verify = { success: false, output: '' };
  for (let i = 0; i < 3; i++) {
    verify = await runCommand(`cd '${dirEsc}' && ls -1 '${fileEsc(nombreDestino)}' 2>/dev/null`);
    if (verify.success && verify.output.trim()) break;
    await new Promise((resolve) => setTimeout(resolve, 1200 * (i + 1)));
  }
  if (!verify.success || !verify.output.trim()) {
    await closeMonitorAfter(1);
    const result = { success: false, mensaje: 'El archivo no se creó correctamente en el servidor', origen: nombreArchivo, destino: nombreDestino, esPrueba };
    autoCopyConfig.updateLastRun(slot, fechaCdmx, { ...result, fecha: fechaCdmx, hora: slot });
    return result;
  }

  await closeMonitorAfter(postCloseSec);
  const result = { success: true, mensaje: esPrueba ? 'Prueba: copiado el último informe (+1s)' : 'Archivo copiado automáticamente', origen: nombreArchivo, destino: nombreDestino, fecha: fechaCdmx, hora: slot, esPrueba };
  autoCopyConfig.updateLastRun(slot, fechaCdmx, result);
  console.log('[auto-copy]', slot, result.mensaje, result.origen, '->', result.destino);
  return result;
}

/** Tiempo de espera para la prueba programada (1 minuto). */
const PRUEBA_DELAY_MS = 60000;

/** Evita dos runAutoCopyJob para el mismo HH:MM en paralelo (ticks cada 30s). */
const autoCopyRunningSlots = new Set();
/** Evita varias consultas fallback en paralelo para el mismo slot. */
const fallbackAsyncRunning = new Set();
let truncarAutoRunning = false;

let autoCopySchedulerInterval = null;
let pruebaTimeoutId = null;
let pruebaEjecutando = false;
/** Deadline en memoria: no depende solo del archivo; se setea en POST y se revisa cada 2 s. */
let pruebaDeadlineMs = null;
/** Estado de ejecucion de "Ejecutar ahora" en segundo plano. */
let ejecutarAhoraState = {
  running: false,
  source: null,
  startedAt: null,
  finishedAt: null,
  lastResult: null,
  lastError: null,
};

function startEjecutarAhoraBackground(source) {
  if (ejecutarAhoraState.running) return false;
  ejecutarAhoraState.running = true;
  ejecutarAhoraState.source = source || 'manual';
  ejecutarAhoraState.startedAt = new Date().toISOString();
  ejecutarAhoraState.finishedAt = null;
  ejecutarAhoraState.lastError = null;
  const jobMaxMs = Math.max(180000, parseInt(process.env.AUTOCOPY_JOB_MAX_MS || '720000', 10) || 720000);
  (async () => {
    try {
      const workPromise = (async () => {
        const nowCdmx = await getCdmxNowForHttp();
        const slot = nowCdmx.hora;
        const fecha = nowCdmx.fecha;
        return await runAutoCopyJob(slot, fecha);
      })();
      const timeoutPromise = new Promise((resolve) => {
        setTimeout(() => {
          resolve({
            success: false,
            mensaje:
              'Tiempo máximo (' +
              Math.round(jobMaxMs / 60000) +
              ' min) agotado. Suele deberse a SSH colgado, sudo pidiendo contraseña, o monitoreo previo bloqueado. Use Diagnóstico SSH o desactive preRunMonitoreo en auto-copy.',
          });
        }, jobMaxMs);
      });
      const result = await Promise.race([workPromise, timeoutPromise]);
      ejecutarAhoraState.lastResult = result;
    } catch (e) {
      ejecutarAhoraState.lastError = e.message;
      ejecutarAhoraState.lastResult = { success: false, mensaje: e.message };
    } finally {
      ejecutarAhoraState.running = false;
      ejecutarAhoraState.finishedAt = new Date().toISOString();
    }
  })();
  return true;
}

async function consultarEstadoBD(nombreArchivo) {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 15000);
  try {
    const result = await postEstadoBdJson({ nombres: [nombreArchivo] }, controller.signal);
    if (result.error || !result.data) {
      return { success: false, estado: null, error: result.error || 'Sin respuesta BD' };
    }
    const { r, data } = result;
    const estado = data && data.estados ? data.estados[nombreArchivo] : null;
    return { success: !!(r && r.ok && data && data.success), estado, raw: data, urlUsada: result.url };
  } catch (e) {
    return { success: false, estado: null, error: e.message };
  } finally {
    clearTimeout(timeoutId);
  }
}

function esSlotEspecialMartesSemanal(hora, fallbackMeta, fechaCdmx) {
  return (
    esMartesFecha(fechaCdmx)
    && hora === '07:40'
    && fallbackMeta
    && fallbackMeta.hh === '07'
    && fallbackMeta.mm === '32'
    && (fallbackMeta.dayOffset || 0) === 0
  );
}

async function runMartesSemanalJob(slot, fechaCdmx, nombreEsperado) {
  const commandTemplate = String(process.env.MARTES_SEMANAL_REMOTE_COMMAND || '').trim();
  if (!commandTemplate) {
    return {
      success: false,
      mensaje: 'MARTES_SEMANAL_REMOTE_COMMAND no está configurado en .env (rama especial martes 07:31).',
      slot,
      fecha: fechaCdmx,
      archivo: nombreEsperado,
      tipo: 'martes_semanal',
    };
  }
  const timeoutMs = Math.max(60000, parseInt(process.env.MARTES_SEMANAL_TIMEOUT_MS || '900000', 10) || 900000);
  const archivoBase = String(nombreEsperado || '').replace(/\.zip$/i, '');
  const cmd = commandTemplate
    .replace(/\{archivo\}/g, String(nombreEsperado || ''))
    .replace(/\{archivo_base\}/g, archivoBase)
    .replace(/\{fecha\}/g, String(fechaCdmx || ''))
    .replace(/\{slot\}/g, String(slot || ''));
  try {
    console.log('[martes-semanal] Ejecutando rama especial:', slot, 'archivo=', nombreEsperado);
    const r = await runCommand(cmd, { timeoutMs, retries: 0, readyTimeoutMs: 12000 });
    if (!r.success) {
      return {
        success: false,
        mensaje: r.error || 'Error al ejecutar rama especial martes semanal.',
        slot,
        fecha: fechaCdmx,
        archivo: nombreEsperado,
        tipo: 'martes_semanal',
      };
    }
    return {
      success: true,
      mensaje: 'Rama especial martes semanal completada.',
      slot,
      fecha: fechaCdmx,
      archivo: nombreEsperado,
      tipo: 'martes_semanal',
      output: String(r.output || '').slice(0, 800),
    };
  } catch (e) {
    return {
      success: false,
      mensaje: e.message || 'Error inesperado en rama especial martes semanal.',
      slot,
      fecha: fechaCdmx,
      archivo: nombreEsperado,
      tipo: 'martes_semanal',
    };
  }
}

async function ejecutarTruncarAutomaticoSiToca(config, nowCdmx) {
  // Mismo interruptor que «Copiar +1s automático» (`data/auto-copy-config.json` → enabled).
  if (!config || !config.enabled) return;
  if (!esVentanaTruncarMartesCdmx(nowCdmx)) return;
  const slotKey = 'truncar_auto_07_00';
  const lastBySlot = config.lastRunBySlot || {};
  if (lastBySlot[slotKey] === nowCdmx.fecha) return;
  if (truncarAutoRunning) return;

  truncarAutoRunning = true;
  const ctrl = new AbortController();
  const to = setTimeout(() => ctrl.abort(), 45000);
  try {
    const cFresh = autoCopyConfig.readConfig();
    const lbsCheck = cFresh.lastRunBySlot || {};
    if (lbsCheck[slotKey] === nowCdmx.fecha) {
      return;
    }

    const result = await postTruncarAutomaticoAgente(
      {
        source: 'auto-copy-scheduler',
        fecha_cdmx: nowCdmx.fecha,
        hora_cdmx: nowCdmx.hora,
      },
      ctrl.signal
    );
    const ok = !!(result && result.r && result.r.ok && result.data && result.data.success);
    if (!ok) {
      const msg = (result && result.data && result.data.mensaje) || (result && result.error) || 'No se pudo ejecutar truncar automático.';
      ejecutarAhoraState.lastResult = { success: false, mensaje: 'Truncar automático (martes 07:00–07:29 CDMX): ' + msg, tipo: 'truncar_auto' };
      ejecutarAhoraState.finishedAt = new Date().toISOString();
      console.warn('[truncar-auto] Falló:', msg);
      // Importante: NO grabar lastRunBySlot si PHP/red falló — debe poder reintentarse en la misma ventana.
      return;
    }
    const c = autoCopyConfig.readConfig();
    c.lastRunBySlot = c.lastRunBySlot || {};
    c.lastRunBySlot[slotKey] = nowCdmx.fecha;
    c.lastRunResult = {
      success: true,
      mensaje: (result.data && result.data.mensaje) || 'Truncar automático (martes 07:00–07:29 CDMX) ejecutado.',
      tipo: 'truncar_auto',
      fecha: nowCdmx.fecha,
      hora: nowCdmx.hora,
      registros_copiados: result.data && result.data.registros_copiados,
    };
    autoCopyConfig.writeConfig(c);
    ejecutarAhoraState.lastResult = c.lastRunResult;
    ejecutarAhoraState.finishedAt = new Date().toISOString();
    console.log('[truncar-auto] Ejecutado con éxito (ventana martes 07:00–07:29 CDMX).');
  } catch (e) {
    const msg = e && e.message ? e.message : 'Error en truncar automático.';
    ejecutarAhoraState.lastResult = { success: false, mensaje: 'Truncar automático (martes 07:00–07:29 CDMX): ' + msg, tipo: 'truncar_auto' };
    ejecutarAhoraState.finishedAt = new Date().toISOString();
    console.warn('[truncar-auto] Excepción:', msg);
  } finally {
    clearTimeout(to);
    truncarAutoRunning = false;
  }
}

async function consultarEstadoBDLote(nombres) {
  const lista = Array.isArray(nombres) ? nombres.map(n => String(n || '').trim()).filter(Boolean) : [];
  if (!lista.length) return { success: true, estados: {} };
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 15000);
  try {
    const result = await postEstadoBdJson({ nombres: lista }, controller.signal);
    if (result.error || !result.data) {
      return { success: false, estados: {}, error: result.error || 'Sin respuesta BD' };
    }
    const { r, data } = result;
    const estados = (data && data.estados && typeof data.estados === 'object') ? data.estados : {};
    if (!r || !r.ok || !data || !data.success) {
      const msg = (data && data.mensaje) ? data.mensaje : (r ? 'HTTP ' + r.status : 'Sin HTTP');
      return { success: false, estados, error: msg, raw: data };
    }
    return { success: true, estados, raw: data, urlUsada: result.url };
  } catch (e) {
    return { success: false, estados: {}, error: e.message };
  } finally {
    clearTimeout(timeoutId);
  }
}

/**
 * Una sola POST a estadoReportesAgente: ping (nombres vacíos o con último ZIP).
 * @param {string|null} ultimoNombre mega_rpt_....csv.zip o null
 */
async function postEstadoBdDiagnostico(ultimoNombre) {
  const nombres = ultimoNombre ? [ultimoNombre] : [];
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 15000);
  try {
    const result = await postEstadoBdJson({ nombres }, controller.signal);
    if (result.error || !result.data) {
      return {
        ok: false,
        error: result.error || 'Sin respuesta',
        url: result.url || null,
        horaPhp: '',
        estadoUltimo: null,
      };
    }
    const { r, data } = result;
    if (!r || !r.ok || !data || !data.success) {
      return {
        ok: false,
        error: (data && data.mensaje) ? data.mensaje : 'HTTP ' + (r ? r.status : '?'),
        url: result.url,
        horaPhp: String((data && data.hora_servidor_cdmx) || ''),
        estadoUltimo: null,
      };
    }
    const est = ultimoNombre && data.estados && typeof data.estados === 'object' ? data.estados[ultimoNombre] : null;
    return {
      ok: true,
      url: result.url,
      horaPhp: String(data.hora_servidor_cdmx || ''),
      estadoUltimo: est !== undefined ? est : null,
    };
  } catch (e) {
    return { ok: false, error: e.message, url: null, horaPhp: '', estadoUltimo: null };
  } finally {
    clearTimeout(timeoutId);
  }
}

function shQuoteForEnv(s) {
  return "'" + String(s).replace(/'/g, "'\\''") + "'";
}

/** Script remoto: una sola sesión SSH; salida con marcadores. */
function buildDiagnosticoRemoteScript() {
  return [
    'set +e',
    'echo "__SSH_MARK_ECHO__"',
    'echo "DIAG_OK"',
    'echo "__SSH_MARK_SUDO__"',
    'sudo -n true 2>/dev/null && echo "SUDO_NOPASS" || echo "SUDO_FAIL"',
    'echo "__SSH_MARK_SCRIPT__"',
    'if test -f "$MON_SCRIPT" && test -r "$MON_SCRIPT"; then',
    '  if test -x "$MON_SCRIPT"; then echo "SCRIPT_OK_EXEC"; else echo "SCRIPT_OK_SH"; fi',
    'else',
    '  echo "SCRIPT_BAD"',
    'fi',
    'echo "__SSH_MARK_DF__"',
    'df -hP "$REMOTE_DIR" 2>/dev/null | tail -1',
    'echo "__SSH_MARK_LSLD__"',
    'ls -ld "$REMOTE_DIR" 2>&1 | head -1',
    'echo "__SSH_MARK_LSZIP__"',
    'ls -l "$REMOTE_DIR"/mega_rpt_*.csv.zip 2>/dev/null || true',
    'echo "__SSH_MARK_PGREP__"',
    'ps -eo pid=,args= --no-headers 2>/dev/null | grep -F "$MON_SCRIPT" | grep -vF "printf %s" | head -8 || true',
    'echo "__SSH_MARK_DEST__"',
    'ULT=""',
    'for f in $(ls -1 "$REMOTE_DIR"/mega_rpt_*.csv.zip 2>/dev/null | sort -r); do',
    '  BN=$(basename "$f")',
    '  [[ "$BN" =~ ^mega_rpt_([0-9]{8})_ ]] || continue',
    '  FD="${BASH_REMATCH[1]}"',
    '  [[ "$FD" < "$FECHA_MIN" ]] && continue',
    '  ULT="$f"',
    '  break',
    'done',
    'if [ -z "$ULT" ]; then',
    '  echo "LATEST_NONE"',
    'else',
    '  echo "LATEST_PATH:$ULT"',
    '  BN=$(basename "$ULT")',
    '  if [[ "$BN" =~ ^mega_rpt_([0-9]{8})_([0-9]{2})_([0-9]{2})_([0-9]{2})\\.csv\\.zip$ ]]; then',
    '    D="${BASH_REMATCH[1]}"; H="${BASH_REMATCH[2]}"; M="${BASH_REMATCH[3]}"; S="${BASH_REMATCH[4]}"',
    '    DFMT="${D:0:4}-${D:4:2}-${D:6:2} ${H}:${M}:${S}"',
    '    NEXT=$(date -d "$DFMT + 1 second" +%Y%m%d_%H_%M_%S 2>/dev/null)',
    '    if [ -n "$NEXT" ]; then',
    '      DEST="mega_rpt_${NEXT}.csv.zip"',
    '      if [ -f "$REMOTE_DIR/$DEST" ]; then echo "DEST_EXISTS"; else echo "DEST_NEW"; fi',
    '      echo "DEST_NAME:$DEST"',
    '    else',
    '      echo "DEST_DATE_ERR"',
    '    fi',
    '  else',
    '    echo "DEST_PARSE_ERR"',
    '  fi',
    'fi',
    'echo "__SSH_MARK_END__"',
  ].join('\n');
}

function parseDiagnosticoSshBundle(output) {
  const text = String(output || '');
  const order = [
    '__SSH_MARK_ECHO__',
    '__SSH_MARK_SUDO__',
    '__SSH_MARK_SCRIPT__',
    '__SSH_MARK_DF__',
    '__SSH_MARK_LSLD__',
    '__SSH_MARK_LSZIP__',
    '__SSH_MARK_PGREP__',
    '__SSH_MARK_DEST__',
    '__SSH_MARK_END__',
  ];
  const sections = {};
  for (let i = 0; i < order.length - 1; i++) {
    const a = order[i];
    const b = order[i + 1];
    const p = text.indexOf(a);
    const q = text.indexOf(b);
    if (p === -1) {
      sections[a] = '';
      continue;
    }
    const start = p + a.length;
    const chunk = q === -1 ? text.slice(start) : text.slice(start, q);
    sections[a] = chunk.replace(/^\n/, '').replace(/\n$/, '');
  }
  return sections;
}

function armarResumenDiagnostico(pruebas) {
  const ok = (n) => !!(pruebas.find((p) => p.nombre === n) || {}).ok;
  const parts = [];
  parts.push(ok('Conexión SSH al servidor') ? 'SSH OK' : 'SSH falló');
  parts.push(ok('PHP estadoReportesAgente (alcance BD)') ? 'PHP/BD OK' : 'PHP/BD falló');
  parts.push(ok('Último informe mega en carpeta') ? 'hay ZIP reciente' : 'sin ZIP');
  parts.push(ok('sudo sin contraseña (NOPASSWD)') ? 'sudo OK' : 'sudo revisar');
  return parts.join(' · ');
}

/**
 * Diagnóstico amplio: local, PHP/BD, remoto (SSH). Sin copiar archivos.
 */
async function ejecutarDiagnosticoCompleto() {
  const pruebas = [];
  const host = process.env.SSH_HOST || '34.173.106.81';
  const monScript = process.env.MONITOREAR_SCRIPT || '/home/jesus/scripts/monitorear.sh';
  const keyDefault = path.join(__dirname, 'keys', 'jesusssh4.unknown');
  const keyPath = process.env.SSH_KEY_PATH || keyDefault;
  const resolvedKey = path.isAbsolute(keyPath) ? keyPath : path.resolve(__dirname, keyPath);

  pruebas.push({
    nombre: 'Clave SSH (dentro del agente)',
    ok: fs.existsSync(resolvedKey),
    detalle: resolvedKey + ' — ' + (fs.existsSync(resolvedKey) ? 'Encontrada' : 'NO encontrada.'),
    grupo: 'local',
    ayuda: 'Sin clave válida no hay sesión SSH.',
  });

  let nowCdmx;
  try {
    nowCdmx = await getAccurateCdmxNow();
  } catch (e) {
    nowCdmx = getCdmxLocalSync();
  }

  pruebas.push({
    nombre: 'Hora CDMX (agente)',
    ok: true,
    detalle: (nowCdmx.fecha || '?') + ' ' + (nowCdmx.horaSeg || nowCdmx.hora || '?') + ' — fuente: ' + (nowCdmx.source || 'n/d'),
    grupo: 'local',
    ayuda: 'Referencia para horarios automáticos.',
  });

  const ac = autoCopyConfig.readConfig();
  const prox = proximaEjecucionByNow(ac, nowCdmx);
  const proxStr = prox ? prox.label : (ac.enabled ? '—' : 'Automático desactivado');
  pruebas.push({
    nombre: 'Auto-copy (config en disco)',
    ok: true,
    detalle:
      'activo=' +
      (ac.enabled ? 'sí' : 'no') +
      ', monitoreo previo=' +
      (ac.preRunMonitoreo ? 'sí' : 'no') +
      ', REMOTE_DIR=' +
      REMOTE_DIR +
      '. Próxima ventana: ' +
      proxStr,
    grupo: 'local',
    cubre: 'scheduler',
  });

  const fechaMinCompact = ymdCompact(sumarDiasYmd(nowCdmx.fecha, -1));
  const remoteScriptBody = buildDiagnosticoRemoteScript();
  const b64 = Buffer.from(remoteScriptBody, 'utf8').toString('base64');
  const sshBundleCmd =
    'printf %s ' +
    shQuoteForEnv(b64) +
    ' | base64 -d | env REMOTE_DIR=' +
    shQuoteForEnv(REMOTE_DIR) +
    ' MON_SCRIPT=' +
    shQuoteForEnv(monScript) +
    ' FECHA_MIN=' +
    shQuoteForEnv(fechaMinCompact) +
    ' bash';
  const sshOptsBundle = { timeoutMs: 120000 };

  let rBundle = { success: false, output: '', error: '' };
  let rttMs = 0;
  try {
    const t0 = Date.now();
    rBundle = await runCommand(sshBundleCmd, sshOptsBundle);
    rttMs = Date.now() - t0;
  } catch (e) {
    rBundle = { success: false, output: '', error: e.message };
  }

  const bundleOut = String(rBundle.output || '');
  const sshEchoOk = bundleOut.includes('DIAG_OK');
  pruebas.push({
    nombre: 'Conexión SSH al servidor',
    ok: rBundle.success && sshEchoOk,
    detalle:
      rBundle.success && sshEchoOk
        ? host +
          ' — OK · ~' +
          rttMs +
          ' ms · 1 sesión SSH (sudo, script, df, listado, pgrep, simulación +1s)'
        : (rBundle.error || bundleOut.slice(0, 220) || 'Fallo'),
    grupo: 'remoto',
    cubre: 'Listar / Copiar / Monitorear',
    ayuda: 'Todas las pruebas remotas van en un único script para no abrir muchas conexiones.',
  });

  function appendDiffRelojPhp(detalleBase, bdOk, horaPhp) {
    if (!bdOk || !horaPhp || !nowCdmx || !nowCdmx.horaSeg) return detalleBase;
    const parsePhp = /(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})/.exec(horaPhp);
    if (!parsePhp) return detalleBase;
    const tAgent = new Date(nowCdmx.fecha + 'T' + nowCdmx.horaSeg).getTime();
    const tPhp = new Date(parsePhp[1] + 'T' + parsePhp[2]).getTime();
    if (!Number.isFinite(tAgent) || !Number.isFinite(tPhp)) return detalleBase;
    const dsec = Math.round((tAgent - tPhp) / 1000);
    return detalleBase + ' Diferencia agente−PHP ~' + dsec + ' s.';
  }

  if (!rBundle.success || !sshEchoOk) {
    pruebas.push({
      nombre: 'Pruebas remotas (detalle)',
      ok: false,
      detalle: 'Omitidas: el bundle remoto no completó (revisar SSH y salida arriba).',
      grupo: 'remoto',
    });
    const bdOnly = await postEstadoBdDiagnostico(null);
    let detPhp =
      bdOnly.ok
        ? 'JSON OK. URL: ' + (bdOnly.url || '') + (bdOnly.horaPhp ? '. Hora servidor CDMX (PHP): ' + bdOnly.horaPhp : '')
        : (bdOnly.error || 'Fallo') + (bdOnly.url ? ' — URL: ' + bdOnly.url : '');
    detPhp = appendDiffRelojPhp(detPhp, bdOnly.ok, bdOnly.horaPhp);
    pruebas.push({
      nombre: 'PHP estadoReportesAgente (alcance BD)',
      ok: bdOnly.ok,
      detalle: detPhp,
      grupo: 'bd',
      ayuda: 'Una sola POST (sin último ZIP si no hubo listado remoto).',
      cubre: 'BD',
    });
    pruebas.push({
      nombre: 'Estado en BD del último ZIP',
      ok: false,
      detalle: 'Sin listado remoto; omitido.',
      grupo: 'bd',
    });
    return { pruebas, resumen: armarResumenDiagnostico(pruebas) };
  }

  const sec = parseDiagnosticoSshBundle(bundleOut);

  const sudoOk = (sec['__SSH_MARK_SUDO__'] || '').includes('SUDO_NOPASS');
  pruebas.push({
    nombre: 'sudo sin contraseña (NOPASSWD)',
    ok: sudoOk,
    detalle: sudoOk
      ? 'sudo -n aceptado (necesario para cp y monitoreo previo).'
      : 'sudo -n falló — puede colgar Copiar +1s. Fragmento: ' + String(sec['__SSH_MARK_SUDO__'] || '').trim().slice(0, 160),
    grupo: 'remoto',
    ayuda: 'Incluido en el bundle SSH.',
    cubre: 'Copiar +1s / monitoreo previo',
  });

  const scriptChunk = String(sec['__SSH_MARK_SCRIPT__'] || '').trim();
  const scriptOk = scriptChunk.includes('SCRIPT_OK_EXEC') || scriptChunk.includes('SCRIPT_OK_SH');
  let scriptDetalle = monScript + ' — ';
  if (scriptChunk.includes('SCRIPT_OK_EXEC')) {
    scriptDetalle += 'existe, legible y con bit ejecutable (+x).';
  } else if (scriptChunk.includes('SCRIPT_OK_SH')) {
    scriptDetalle +=
      'existe y es legible (sin +x). Es válido: el agente lo lanza con `bash ruta/script.sh` (como monitoreo previo).';
  } else {
    scriptDetalle += (scriptChunk || 'SCRIPT_BAD').slice(0, 200);
  }
  pruebas.push({
    nombre: 'Script monitoreo (remoto)',
    ok: scriptOk,
    detalle: scriptDetalle,
    grupo: 'remoto',
    ayuda: 'Antes se exigía +x; muchos servidores lo ejecutan solo con bash.',
    cubre: 'Monitorear / preRunMonitoreo',
  });

  const dfChunk = (sec['__SSH_MARK_DF__'] || '').trim();
  const dfLine = dfChunk.split('\n').filter(Boolean).pop() || '';
  pruebas.push({
    nombre: 'Espacio en disco (df)',
    ok: dfLine.length > 0,
    detalle: dfLine || 'No se pudo leer df',
    grupo: 'remoto',
  });

  const lsldLine = (sec['__SSH_MARK_LSLD__'] || '').trim().split('\n')[0] || '';
  pruebas.push({
    nombre: 'Permisos directorio mega (ls -ld)',
    ok: lsldLine.length > 0 && !lsldLine.includes('No such file'),
    detalle: lsldLine.slice(0, 240) || 'Fallo',
    grupo: 'remoto',
  });

  const lszip = sec['__SSH_MARK_LSZIP__'] || '';
  const archivos = parseListOutput(lszip, { fecha: nowCdmx.fecha });
  const ultimo = archivos[0];
  const ultimoNombre = ultimo ? ultimo.nombre : null;

  let detList = 'Listado dentro del bundle SSH.';
  if (ultimo) {
    const edadMin = Math.max(0, Math.round((Date.now() - ultimo.timestamp) / 60000));
    detList =
      'Último (desde ayer CDMX, misma regla que listar): ' +
      ultimo.nombre +
      ' · ' +
      ultimo.tamano +
      ' · ~' +
      edadMin +
      ' min. «Ejecutar ahora» usaría este.';
  } else if (!lszip.trim()) {
    detList = 'No hay mega_rpt_*.csv.zip en ventana (ayer/hoy). Ejecutar ahora: sin archivo.';
  } else {
    detList = 'Sin ZIP en ventana tras filtrar fechas; raw: ' + lszip.slice(0, 120).replace(/\s+/g, ' ');
  }
  pruebas.push({
    nombre: 'Último informe mega en carpeta',
    ok: !!ultimo,
    detalle: detList,
    grupo: 'remoto',
    ayuda: 'Datos del mismo listado -l que el bundle.',
    cubre: 'Listar / Ejecutar ahora',
  });

  const destBlock = sec['__SSH_MARK_DEST__'] || '';
  const destExists = destBlock.includes('DEST_EXISTS');
  const destNew = destBlock.includes('DEST_NEW');
  const destErr = destBlock.includes('DEST_PARSE_ERR') || destBlock.includes('DEST_DATE_ERR');
  const dest = ultimoNombre ? nombreDestinoPlusOneSeg(ultimoNombre) : null;
  const simOk = !!dest && !destErr && (destExists || destNew);
  if (ultimoNombre && dest) {
    pruebas.push({
      nombre: 'Simulación Copiar +1s (sin escribir)',
      ok: simOk,
      detalle:
        'Origen ' +
        ultimoNombre +
        ' → destino ' +
        dest +
        ' — ' +
        (destErr
          ? 'error al calcular/verificar en remoto (date o nombre).'
          : destExists
            ? 'el destino ya existe en disco.'
            : 'destino aún no existe (cp sería nuevo).'),
      grupo: 'remoto',
      cubre: 'Copiar +1s',
    });
  } else {
    pruebas.push({
      nombre: 'Simulación Copiar +1s (sin escribir)',
      ok: false,
      detalle: 'Sin último ZIP válido en ventana; omitido.',
      grupo: 'remoto',
    });
  }

  const pgOut = (sec['__SSH_MARK_PGREP__'] || '').trim();
  pruebas.push({
    nombre: 'Procesos relacionados con monitorear',
    ok: true,
    detalle: pgOut ? pgOut.slice(0, 300) : 'Ninguno coincidente (normal si no corre el script).',
    grupo: 'remoto',
    ayuda: 'Incluido en el bundle SSH.',
  });

  const bd = await postEstadoBdDiagnostico(ultimoNombre);
  let detPhp2 =
    bd.ok
      ? 'JSON OK. URL: ' + (bd.url || '') + (bd.horaPhp ? '. Hora servidor CDMX (PHP): ' + bd.horaPhp : '')
      : (bd.error || 'Fallo') + (bd.url ? ' — URL: ' + bd.url : '');
  detPhp2 = appendDiffRelojPhp(detPhp2, bd.ok, bd.horaPhp);
  if (ultimoNombre) {
    detPhp2 += ' Consulta incluye estado del último ZIP.';
  }
  pruebas.push({
    nombre: 'PHP estadoReportesAgente (alcance BD)',
    ok: bd.ok,
    detalle: detPhp2,
    grupo: 'bd',
    ayuda: 'Una sola POST: ping + estado del último archivo (si hay nombre).',
    cubre: 'BD',
  });

  if (ultimoNombre) {
    const st = bd.estadoUltimo != null ? bd.estadoUltimo : 'sin registro';
    pruebas.push({
      nombre: 'Estado en BD del último ZIP',
      ok: bd.ok,
      detalle: bd.ok
        ? ultimoNombre + ' → estado: ' + st + (bd.url ? ' · ' + bd.url : '')
        : (bd.error || 'Error consultando BD'),
      grupo: 'bd',
      cubre: 'pipeline MySQL',
    });
  } else {
    pruebas.push({
      nombre: 'Estado en BD del último ZIP',
      ok: false,
      detalle: 'Sin archivo en ventana; no se consultó fila de estado.',
      grupo: 'bd',
    });
  }

  return { pruebas, resumen: armarResumenDiagnostico(pruebas) };
}

function appendPruebaLog(line) {
  try {
    const dir = path.join(__dirname, 'data');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    fs.appendFileSync(path.join(dir, 'prueba-log.txt'), new Date().toISOString() + ' ' + line + '\n', 'utf8');
  } catch (_) {}
}

/**
 * Ejecuta la prueba programada una sola vez.
 * Solo dispara cuando realmente tocaba (evita ejecutar al instante por timestamp viejo en disco).
 */
async function ejecutarPruebaProgramadaSiToca() {
  if (pruebaEjecutando) return;
  const ahora = Date.now();
  const config = autoCopyConfig.readConfig();
  const enMs = Number(config.pruebaEjecutarEn);

  // Por memoria: deadline puesto en este proceso al pulsar el botón
  const porMemoria = pruebaDeadlineMs != null && ahora >= pruebaDeadlineMs;

  // Por archivo: solo si ya pasó la hora programada Y no es basura antigua
  // (si enMs quedó de hace horas, no ejecutar; limpiar)
  let porArchivo = false;
  if (Number.isFinite(enMs) && enMs > 0 && ahora >= enMs) {
    const ventanaMs = 150000;
    if (enMs < ahora - ventanaMs) {
      appendPruebaLog('LIMPIEZA pruebaEjecutarEn viejo en disco (sin ejecutar)');
      const updated = autoCopyConfig.readConfig();
      updated.pruebaEjecutarEn = null;
      updated.pruebaNoAntesDe = null;
      autoCopyConfig.writeConfig(updated);
      return;
    }
    porArchivo = true;
  }

  if (!porMemoria && !porArchivo) return;

  pruebaEjecutando = true;
  pruebaDeadlineMs = null;
  try {
    if (pruebaTimeoutId) {
      clearTimeout(pruebaTimeoutId);
      pruebaTimeoutId = null;
    }
    const updated = autoCopyConfig.readConfig();
    updated.pruebaEjecutarEn = null;
    updated.pruebaNoAntesDe = null;
    autoCopyConfig.writeConfig(updated);
    const nowCdmx = await getCdmxNowForHttp();
    const fecha = nowCdmx.fecha;
    appendPruebaLog('EJECUTANDO job PRUEBA');
    console.log('[auto-copy] Ejecutando prueba programada (último informe +1s)', new Date().toISOString());
    runAutoCopyJob('PRUEBA', fecha)
      .then((r) => {
        appendPruebaLog('TERMINADA success=' + r.success + ' ' + (r.mensaje || ''));
        console.log('[auto-copy] Prueba terminada:', r.success, r.mensaje || '');
      })
      .catch((e) => {
        appendPruebaLog('ERROR ' + e.message);
        console.error('[auto-copy] Prueba error:', e.message);
      })
      .finally(() => { pruebaEjecutando = false; });
  } catch (e) {
    pruebaEjecutando = false;
    appendPruebaLog('EXCEPCION ' + e.message);
    console.error('[auto-copy] ejecutarPruebaProgramadaSiToca:', e.message);
  }
}

function programarTimeoutPrueba() {
  if (pruebaTimeoutId) {
    clearTimeout(pruebaTimeoutId);
    pruebaTimeoutId = null;
  }
  const cuando = pruebaDeadlineMs != null ? pruebaDeadlineMs : Number(autoCopyConfig.readConfig().pruebaEjecutarEn);
  if (!Number.isFinite(cuando) || cuando <= 0) return;
  let delay = Math.max(0, cuando - Date.now());
  if (delay === 0 && pruebaDeadlineMs != null && cuando === pruebaDeadlineMs) {
    delay = PRUEBA_DELAY_MS;
    pruebaDeadlineMs = Date.now() + PRUEBA_DELAY_MS;
    const c = autoCopyConfig.readConfig();
    c.pruebaEjecutarEn = pruebaDeadlineMs;
    c.pruebaNoAntesDe = Date.now();
    autoCopyConfig.writeConfig(c);
    appendPruebaLog('setTimeout delay 0 inesperado; reprogramado +60s');
  }
  if (delay === 0) {
    ejecutarPruebaProgramadaSiToca().catch(() => {});
    return;
  }
  pruebaTimeoutId = setTimeout(() => {
    pruebaTimeoutId = null;
    ejecutarPruebaProgramadaSiToca().catch(() => {});
  }, delay);
  console.log('[auto-copy] Prueba: setTimeout en', Math.round(delay / 1000), 's ->', new Date(cuando).toISOString());
}

function startAutoCopyScheduler() {
  if (autoCopySchedulerInterval) return;
  // Si al arrancar hay prueba en disco y aún no venció, cargar deadline en memoria
  const cfg = autoCopyConfig.readConfig();
  const enMs = Number(cfg.pruebaEjecutarEn);
  const ahora = Date.now();
  if (Number.isFinite(enMs) && enMs > 0) {
    if (ahora < enMs) {
      pruebaDeadlineMs = enMs;
      appendPruebaLog('ARRANQUE: prueba pendiente hasta ' + new Date(enMs).toISOString());
    } else if (enMs < ahora - 150000) {
      const updated = autoCopyConfig.readConfig();
      updated.pruebaEjecutarEn = null;
      updated.pruebaNoAntesDe = null;
      autoCopyConfig.writeConfig(updated);
      appendPruebaLog('ARRANQUE: limpiada prueba vieja en disco');
    }
  }
  programarTimeoutPrueba();
  // Poll cada 2 s: dispara aunque setTimeout falle
  setInterval(() => { ejecutarPruebaProgramadaSiToca().catch(() => {}); }, 2000);
  autoCopySchedulerInterval = setInterval(async () => {
    try {
      const config = autoCopyConfig.readConfig();
      const nowCdmx = await getCdmxNowForHttp();
      const fecha = nowCdmx.fecha;

    // Truncar automático: martes 07:00–07:29 CDMX, solo si Auto-copy está activo (mismo `enabled`).
    await ejecutarTruncarAutomaticoSiToca(config, nowCdmx);

    // Respaldo: a xx:40; nocturno a 23:52 y respaldo 00:05 (mismo archivo …_23_52_00) consulta BD;
    // si no está en BD (ok), dispara ejecución automática en background.
    const fallbackHit = fallbackSlotParaNowCdmx(nowCdmx);
    const fallbackMeta = fallbackHit && fallbackHit.meta;
    const fallbackSlotKey = fallbackHit && fallbackHit.slotKey;
    if (config.enabled && fallbackMeta && fallbackSlotKey) {
      const fallbackKey = 'fallback_' + fallbackSlotKey;
      const lastBySlot = config.lastRunBySlot || {};
      if (lastBySlot[fallbackKey] !== fecha) {
        if (fallbackAsyncRunning.has(fallbackKey)) {
          /* ya hay un async fallback en curso para este slot */
        } else {
        fallbackAsyncRunning.add(fallbackKey);
        (async () => {
          const consumeFallbackSlot = () => {
            const c = autoCopyConfig.readConfig();
            c.lastRunBySlot = c.lastRunBySlot || {};
            c.lastRunBySlot[fallbackKey] = fecha;
            autoCopyConfig.writeConfig(c);
          };
          try {
          const fechaObjetivo = fallbackMeta.dayOffset ? sumarDiasYmd(fecha, fallbackMeta.dayOffset) : fecha;
          const nombreEsperado = 'mega_rpt_' + ymdCompact(fechaObjetivo) + '_' + fallbackMeta.hh + '_' + fallbackMeta.mm + '_00.csv.zip';
          const bd = await consultarEstadoBD(nombreEsperado);
          if (bd.success && bd.estado === 'ok') {
            consumeFallbackSlot();
            ejecutarAhoraState.lastResult = {
              success: true,
              mensaje: 'Respaldo ' + fallbackSlotKey + ': BD OK para ' + nombreEsperado + ' (no se dispara copia).',
              esPrueba: false,
            };
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            console.log('[fallback]', fallbackSlotKey, 'BD OK, sin acción:', nombreEsperado);
            return;
          }
          // Importante: si la verificación BD falla (endpoint inaccesible, login, timeout, etc.)
          // NO disparar copia para evitar falsos positivos. No consumir slot: reintento en el siguiente poll (~5 min).
          if (!bd.success || !bd.estado) {
            ejecutarAhoraState.lastResult = {
              success: false,
              mensaje: 'Respaldo ' + fallbackSlotKey + ': no se pudo verificar BD para ' + nombreEsperado + ' (' + (bd.error || 'sin estado') + '). No se dispara copia.',
              esPrueba: false,
            };
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            console.warn('[fallback]', fallbackSlotKey, 'sin verificación BD válida; no se dispara copia.', nombreEsperado, 'error=', bd.error || 'n/a');
            return;
          }
          // Si BD respondió "procesando", no disparamos respaldo para evitar falsos positivos.
          if (bd.estado === 'procesando') {
            ejecutarAhoraState.lastResult = {
              success: true,
              mensaje: 'Respaldo ' + fallbackSlotKey + ': BD en estado procesando para ' + nombreEsperado + ' (sin acción).',
              esPrueba: false,
            };
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            console.log('[fallback]', fallbackSlotKey, 'BD procesando, sin acción:', nombreEsperado);
            return;
          }
          // Solo si BD respondió explícitamente "error", se dispara respaldo.
          if (bd.estado !== 'error') {
            consumeFallbackSlot();
            ejecutarAhoraState.lastResult = {
              success: false,
              mensaje: 'Respaldo ' + fallbackSlotKey + ': estado BD no esperado (' + bd.estado + ') para ' + nombreEsperado + '. No se dispara copia.',
              esPrueba: false,
            };
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            console.warn('[fallback]', fallbackSlotKey, 'estado BD no esperado; sin acción.', nombreEsperado, 'estadoBD=', bd.estado);
            return;
          }
          // Martes 07:31 (validación a las 07:40): rama especial en lugar del flujo normal.
          if (esSlotEspecialMartesSemanal(fallbackSlotKey, fallbackMeta, fecha)) {
            const especial = await runMartesSemanalJob('MARTES_07_31', fecha, nombreEsperado);
            ejecutarAhoraState.lastResult = especial;
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            if (especial.success) {
              consumeFallbackSlot();
              console.log('[fallback]', fallbackSlotKey, 'rama especial martes semanal completada.', nombreEsperado);
            } else {
              console.warn('[fallback]', fallbackSlotKey, 'rama especial martes semanal falló:', especial.mensaje);
            }
            return;
          }
          if (ejecutarAhoraState.running) {
            console.log('[fallback]', fallbackSlotKey, 'BD no OK, pero ya hay ejecución en curso.');
            return;
          }
          const started = startEjecutarAhoraBackground('fallback-' + fallbackSlotKey);
          if (started) consumeFallbackSlot();
          console.log('[fallback]', fallbackSlotKey, started ? 'disparado (BD=error)' : 'no disparado (ya en curso)', nombreEsperado);
          } finally {
            fallbackAsyncRunning.delete(fallbackKey);
          }
        })();
        }
      }
    }

      if (!AUTO_COPY_MAIN_ENABLED) return;
      if (!config.enabled || !config.horarios || !config.horarios.length) return;
      const horaSlot = horarioAutoCopyParaNowCdmx(config, nowCdmx);
      if (!horaSlot) return;
      const lastBySlotAuto = config.lastRunBySlot || {};
      if (lastBySlotAuto[horaSlot] === fecha) return;
      if (autoCopyRunningSlots.has(horaSlot)) return;
      autoCopyRunningSlots.add(horaSlot);
      (async () => {
        try {
          await runAutoCopyJob(horaSlot, fecha);
        } finally {
          autoCopyRunningSlots.delete(horaSlot);
        }
      })();
    } catch (e) {
      console.error('[auto-copy] Error obteniendo hora CDMX remota:', e.message);
    }
  }, 300000);
  console.log('Auto Copiar +1s: programador activo (cada 5 min). Horarios CDMX:', (autoCopyConfig.readConfig().horarios || []).join(', '));
}

/** Poll cada 1 min solo en martes ~06:55–07:35 CDMX (reloj local TZ): el truncar automático no debe depender del ciclo de 5 min. */
let truncarVentanaSchedulerInterval = null;
function startTruncarVentanaScheduler() {
  if (truncarVentanaSchedulerInterval) return;
  truncarVentanaSchedulerInterval = setInterval(async () => {
    try {
      const quick = getCdmxLocalSync();
      const mm = minutosDesdeMedianocheDesdeNowCdmx(quick);
      if (!esMartesFecha(quick.fecha) || mm === null || mm < 6 * 60 + 55 || mm >= 7 * 60 + 35) return;
      const config = autoCopyConfig.readConfig();
      const nowCdmx = await getCdmxNowForHttp();
      await ejecutarTruncarAutomaticoSiToca(config, nowCdmx);
    } catch (e) {
      console.error('[truncar-auto] Ventana rápida:', e.message);
    }
  }, 60000);
}

app.get('/files', async (req, res) => {
  try {
    // No bloquear el listado si worldtimeapi/timeapi.io cuelgan o no hay red;
    // el Shell depende de este endpoint y los usuarios ven "no carga".
    let nowCdmx;
    try {
      nowCdmx = await Promise.race([
        getAccurateCdmxNow(),
        new Promise((_, rej) => setTimeout(() => rej(new Error('timeout hora CDMX')), 2500)),
      ]);
    } catch (_) {
      nowCdmx = getCdmxLocalSync();
    }
    const anio = String(req.query.anio || '').trim();
    const mes = String(req.query.mes || '').trim().padStart(2, '0');
    const mesTexto = /^\d{4}$/.test(anio) && /^(0[1-9]|1[0-2])$/.test(mes) ? (anio + mes) : '';
    const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
    const cmd = `cd '${dirEsc}' && ls -l mega_rpt_*.csv.zip 2>/dev/null`;
    const result = await runCommand(cmd, {
      timeoutMs: mesTexto ? 90000 : 45000,
      retries: mesTexto ? 3 : 2,
      retryDelayMs: 1800,
      readyTimeoutMs: mesTexto ? 45000 : 25000,
    });
    if (!result.success) {
      return res.status(500).json({
        success: false,
        mensaje: 'No se pudieron listar los archivos: ' + (result.error || 'Error SSH'),
      });
    }
    let archivos = parseListOutput(result.output, nowCdmx, { mesTexto });
    const ocultoInicioSemanaMartes = esMartesAntesDe0730Cdmx(nowCdmx);
    if (!mesTexto && ocultoInicioSemanaMartes) {
      archivos = [];
    }
    return res.json({
      success: true,
      datos: archivos,
      oculto_inicio_semana_martes: ocultoInicioSemanaMartes,
    });
  } catch (e) {
    return res.status(500).json({ success: false, mensaje: e.message });
  }
});

app.post('/files/copy', async (req, res) => {
  try {
    const nombreArchivo = (req.body.nombre_archivo || req.body.nombreArchivo || '').trim();
    let nombreDestino = (req.body.nombre_destino || req.body.nombreDestino || '').trim();

    if (!nombreArchivo || !MEGA_RPT_FULL.test(nombreArchivo)) {
      return res.status(400).json({ success: false, mensaje: 'nombre_archivo inválido o faltante.' });
    }

    if (!nombreDestino || !MEGA_RPT_FULL.test(nombreDestino) || nombreDestino === nombreArchivo) {
      const match = nombreArchivo.match(MEGA_RPT_REGEX);
      if (!match) return res.status(400).json({ success: false, mensaje: 'Formato de archivo inválido.' });
      let fecha = match[1], h = parseInt(match[2], 10), min = parseInt(match[3], 10), seg = parseInt(match[4], 10) + 1;
      if (seg >= 60) { seg = 0; min++; }
      if (min >= 60) { min = 0; h++; }
      if (h >= 24) {
        h = 0;
        const d = new Date(parseInt(fecha.slice(0, 4), 10), parseInt(fecha.slice(4, 6), 10) - 1, parseInt(fecha.slice(6, 8), 10));
        d.setDate(d.getDate() + 1);
        fecha = d.getFullYear() + pad2(d.getMonth() + 1) + pad2(d.getDate());
      }
      nombreDestino = 'mega_rpt_' + fecha + '_' + pad2(h) + '_' + pad2(min) + '_' + pad2(seg) + '.csv.zip';
    }

    const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
    const fileEsc = (s) => s.replace(/'/g, "'\\''");
    const existsCheck = await remoteZipExists(nombreDestino);
    if (!existsCheck.ok) {
      return res.status(500).json({ success: false, mensaje: 'No se pudo verificar destino: ' + (existsCheck.error || 'Error SSH') });
    }
    if (existsCheck.exists) {
      return res.status(409).json({
        success: false,
        mensaje: 'El archivo destino ya existe. Se bloquea sobreescritura para evitar reprocesos/duplicados.',
        datos: { origen: nombreArchivo, destino: nombreDestino, ya_existia_destino: true },
      });
    }
    const cmd = `cd '${dirEsc}' && sudo cp '${fileEsc(nombreArchivo)}' '${fileEsc(nombreDestino)}'`;
    const result = await runCommand(cmd);
    if (!result.success) {
      return res.status(500).json({ success: false, mensaje: result.error || 'Error al copiar.' });
    }
    // Si sudo cp salió con código 0, el archivo existe: cp en Linux es atómico.
    // No hace falta una segunda conexión SSH para verificar.
    return res.json({
      success: true,
      mensaje: 'Archivo copiado exitosamente',
      datos: { origen: nombreArchivo, destino: nombreDestino },
    });
  } catch (e) {
    return res.status(500).json({ success: false, mensaje: e.message });
  }
});

app.delete('/files/:nombre', async (req, res) => {
  try {
    const nombre = (req.params.nombre || '').trim();
    if (!MEGA_RPT_FULL.test(nombre)) {
      return res.status(400).json({ success: false, mensaje: 'Formato de archivo inválido.' });
    }
    const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
    const fileEsc = nombre.replace(/'/g, "'\\''");
    const lsResult = await runCommand(`cd '${dirEsc}' && ls -l '${fileEsc}' 2>/dev/null`);
    if (!lsResult.success || !lsResult.output.trim()) {
      return res.status(404).json({ success: false, mensaje: 'El archivo no existe en el servidor.' });
    }
    const line = lsResult.output.split('\n').find(l => l.includes(nombre));
    if (line) {
      const parts = line.trim().split(/\s+/);
      if (parts.length >= 3 && parts[2].toLowerCase() !== 'root') {
        return res.status(403).json({ success: false, mensaje: 'Solo se pueden eliminar archivos propios (owner root). Este archivo es del proveedor.' });
      }
    }
    const rmResult = await runCommand(`cd '${dirEsc}' && sudo rm '${fileEsc}' 2>&1`);
    if (!rmResult.success) {
      return res.status(500).json({ success: false, mensaje: rmResult.error || 'Error al eliminar.' });
    }
    return res.json({ success: true, mensaje: 'Archivo eliminado correctamente' });
  } catch (e) {
    return res.status(500).json({ success: false, mensaje: e.message });
  }
});

app.get('/files/mega-report/download', async (req, res) => {
  const anio = String(req.query.anio || '').trim();
  const mes = String(req.query.mes || '').trim().padStart(2, '0');
  if (!/^\d{4}$/.test(anio) || !/^(0[1-9]|1[0-2])$/.test(mes)) {
    return res.status(400).send('Año o mes inválido.');
  }

  const mesTexto = anio + mes;
  const token = Date.now() + '_' + Math.random().toString(16).slice(2);
  const remoteCsv = '/tmp/sparta_mega_reporte_' + mesTexto + '_' + token + '.csv';
  const remoteCsvEsc = remoteCsv.replace(/'/g, "'\\''");
  const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
  const pattern = 'mega_rpt_' + mesTexto + '*.csv.zip';

  const cmd = [
    "cd '" + dirEsc + "'",
    "rm -f '" + remoteCsvEsc + "'",
    "count=$(ls " + pattern + " 2>/dev/null | wc -l)",
    "test \"$count\" -gt 0",
    "first=1; for f in " + pattern + "; do if [ \"$first\" -eq 1 ]; then unzip -p \"$f\" '*.csv' 2>/dev/null; first=0; else unzip -p \"$f\" '*.csv' 2>/dev/null | tail -n +2; fi; done | awk 'NF && !seen[$0]++' > '" + remoteCsvEsc + "'",
    "test -s '" + remoteCsvEsc + "'"
  ].join(' && ');

  try {
    const result = await runCommand(cmd, { timeoutMs: 900000, retries: 0, readyTimeoutMs: 45000 });
    if (!result.success) {
      return res.status(500).send('No se pudo generar el mega reporte mensual: ' + (result.error || result.output || 'error remoto'));
    }

    const buffer = await downloadFile(remoteCsv);
    await runCommand("rm -f '" + remoteCsvEsc + "'", { timeoutMs: 15000, retries: 0 }).catch(() => {});

    res.setHeader('Content-Type', 'text/csv; charset=utf-8');
    res.setHeader('Content-Disposition', 'attachment; filename="mega_reporte_' + mesTexto + '.csv"');
    res.setHeader('Content-Length', buffer.length);
    res.send(buffer);
  } catch (e) {
    await runCommand("rm -f '" + remoteCsvEsc + "'", { timeoutMs: 15000, retries: 0 }).catch(() => {});
    console.error('[mega-report] Error al generar mega reporte mensual:', mesTexto, e && e.message ? e.message : e);
    res.status(500).send('Error al generar mega reporte mensual: ' + (e && e.message ? e.message : e));
  }
});

app.get('/files/:nombre/download', async (req, res) => {
  try {
    const nombre = (req.params.nombre || '').trim();
    if (!MEGA_RPT_FULL.test(nombre)) {
      return res.status(400).send('Formato de archivo inválido.');
    }
    const remotePath = REMOTE_DIR + '/' + nombre;
    const buffer = await downloadFile(remotePath);
    res.setHeader('Content-Type', 'application/zip');
    res.setHeader('Content-Disposition', 'attachment; filename="' + nombre + '"');
    res.setHeader('Content-Length', buffer.length);
    res.send(buffer);
  } catch (e) {
    console.error('[download] Error al descargar archivo remoto:', (req.params.nombre || '').trim(), e && e.message ? e.message : e);
    res.status(500).send('Error al descargar: ' + e.message);
  }
});

app.get('/stream/monitorear', (req, res) => {
  res.setHeader('Content-Type', 'text/event-stream');
  res.setHeader('Cache-Control', 'no-cache');
  res.setHeader('Connection', 'keep-alive');
  res.setHeader('X-Accel-Buffering', 'no');
  res.flushHeaders();

  // Sin timeout: el SSH keepalive gestiona la conexión (timeout lo bloqueaba con PTY).
  // PYTHONUNBUFFERED=1: Ubuntu acepta VAR=value antes del comando en sudo.
  const cmd = 'sudo PYTHONUNBUFFERED=1 bash ' + MONITOREAR_SCRIPT + ' 2>&1';
  runCommandStream(cmd)
    .then(({ stream, conn }) => {
      stream.on('data', (data) => {
        try {
          res.write('data: ' + JSON.stringify({ out: data.toString() }) + '\n\n');
          if (typeof res.flush === 'function') res.flush();
        } catch (_) {}
      });
      stream.on('close', () => {
        try { conn.end(); } catch (_) {}
        try {
          res.write('data: ' + JSON.stringify({ done: true }) + '\n\n');
          res.end();
        } catch (_) {}
      });
      // Con PTY, stderr se funde en stdout; este handler es por si acaso.
      if (stream.stderr) {
        stream.stderr.on('data', (data) => {
          try {
            res.write('data: ' + JSON.stringify({ err: data.toString() }) + '\n\n');
            if (typeof res.flush === 'function') res.flush();
          } catch (_) {}
        });
      }
    })
    .catch((err) => {
      try {
        res.write('data: ' + JSON.stringify({ error: err.message }) + '\n\n');
        res.end();
      } catch (_) {}
    });
});

// Página mínima para el iframe de Monitorear: consume el stream SSE y lo muestra en pantalla.
app.get('/ventana-monitorear', (req, res) => {
  res.setHeader('Content-Type', 'text/html; charset=utf-8');
  res.send(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>Monitorear</title><style>body{margin:0;background:#1e1e1e;color:#d4d4d4;font-family:Consolas,monospace;padding:8px;} pre{white-space:pre-wrap;word-break:break-all;margin:0;font-size:13px;}</style></head><body><pre id="out"></pre><script>
(function(){
  var pre = document.getElementById('out');
  var es = new EventSource('/stream/monitorear');
  es.onmessage = function(e) {
    try {
      var d = JSON.parse(e.data);
      if (d.out) pre.textContent += d.out;
      if (d.err) pre.textContent += d.err;
      if (d.error) pre.textContent += '[ERROR] ' + d.error + '\\n';
      if (d.done) { es.close(); }
    } catch (err) {}
  };
  es.onerror = function() { es.close(); };
})();
</script></body></html>`);
});

app.get('/health', (req, res) => {
  res.json({ success: true, servicio: 'segundometro-agent', timestamp: new Date().toISOString() });
});

app.get('/hora-cdmx', async (req, res) => {
  try {
    const nowCdmx = await getCdmxNowForHttp();
    const hora = nowCdmx.fecha + ' ' + nowCdmx.horaSeg + ' CDMX';
    res.json({
      success: true,
      hora_servidor_cdmx: hora,
      fuente_hora: nowCdmx.source,
      hora_remota: !!nowCdmx.fromRemote,
      timestamp_ms: nowCdmx.timestampMs || null,
    });
  } catch (e) {
    res.status(500).json({ success: false, mensaje: e.message });
  }
});

app.post('/reportes/estado', async (req, res) => {
  // Hora CDMX: no tumbar el endpoint si falla la red; la consulta BD no depende de hora remota.
  const nowCdmx = await getCdmxNowForHttp();
  try {
    const nombres = Array.isArray(req.body && req.body.nombres) ? req.body.nombres : [];
    const lote = await consultarEstadoBDLote(nombres);
    const estados = {};
    for (const n of nombres) {
      const nombre = String(n || '').trim();
      if (!nombre) continue;
      const st = lote && lote.estados ? lote.estados[nombre] : null;
      estados[nombre] = st || 'procesando';
    }
    const h = nowCdmx.fecha + ' ' + nowCdmx.horaSeg + ' CDMX';
    res.json({
      success: !!lote.success,
      estados,
      hora_servidor_cdmx: h,
      fuente_hora: nowCdmx.source,
      hora_remota: !!nowCdmx.fromRemote,
      timestamp_ms: nowCdmx.timestampMs || null,
      mensaje: lote.success ? undefined : (lote.error || 'No se pudo consultar BD en lote'),
    });
  } catch (e) {
    res.status(500).json({ success: false, estados: {}, mensaje: e.message });
  }
});

// --- Auto Copiar +1s (activar/desactivar y horarios CDMX) ---
app.get('/auto-copy', async (req, res) => {
  const config = autoCopyConfig.readConfig();
  const nowCdmx = await getCdmxNowForHttp();
  const proxima = proximaEjecucionByNow(config, nowCdmx);
  const pruebaMs = config.pruebaEjecutarEn;
  res.json({
    success: true,
    enabled: config.enabled,
    preRunMonitoreo: config.preRunMonitoreo,
    horarios: config.horarios,
    proximaEjecucion: proxima,
    ultimaEjecucion: config.lastRunResult || null,
    horaActualCDMX: nowCdmx.hora,
    fechaActualCDMX: nowCdmx.fecha,
    fuente_hora: nowCdmx.source,
    hora_remota: !!nowCdmx.fromRemote,
    pruebaProgramadaPara: pruebaMs ? new Date(pruebaMs).toISOString() : null,
    pruebaProgramadaEnMs: pruebaMs || null,
    ejecucionEnCurso: ejecutarAhoraState.running,
  });
});

app.post('/auto-copy', async (req, res) => {
  const body = req.body || {};
  const config = autoCopyConfig.readConfig();
  if (typeof body.enabled === 'boolean') config.enabled = body.enabled;
  if (typeof body.preRunMonitoreo === 'boolean') config.preRunMonitoreo = body.preRunMonitoreo;
  if (Array.isArray(body.horarios) && body.horarios.length > 0) {
    config.horarios = body.horarios.map(h => String(h).trim()).filter(Boolean);
  }
  autoCopyConfig.writeConfig(config);
  const updated = autoCopyConfig.readConfig();
  const nowCdmx = await getCdmxNowForHttp();
  const proxima = proximaEjecucionByNow(updated, nowCdmx);
  res.json({
    success: true,
    mensaje: 'Configuración guardada',
    enabled: updated.enabled,
    preRunMonitoreo: updated.preRunMonitoreo,
    horarios: updated.horarios,
    proximaEjecucion: proxima,
    fuente_hora: nowCdmx.source,
    hora_remota: !!nowCdmx.fromRemote,
    ultimaEjecucion: updated.lastRunResult || null,
  });
});

/** Programa una sola ejecución en 1 minuto: mismo flujo que automático (monitoreo opcional + último informe +1s). */
app.post('/auto-copy/programar-prueba', (req, res) => {
  const clicMs = Date.now();
  const enMs = clicMs + PRUEBA_DELAY_MS;
  pruebaDeadlineMs = enMs;
  if (pruebaTimeoutId) {
    clearTimeout(pruebaTimeoutId);
    pruebaTimeoutId = null;
  }
  const config = autoCopyConfig.readConfig();
  config.pruebaEjecutarEn = enMs;
  config.pruebaNoAntesDe = clicMs;
  autoCopyConfig.writeConfig(config);
  appendPruebaLog('PROGRAMADA para ' + new Date(enMs).toISOString());
  programarTimeoutPrueba();
  res.json({
    success: true,
    mensaje: 'Prueba programada: en 1 minuto se ejecutará Copiar +1s con el último informe. Revise la consola del agente o el archivo data/prueba-log.txt.',
    ejecutarEn: new Date(enMs).toISOString(),
    ejecutarEnMs: enMs,
    delaySegundos: PRUEBA_DELAY_MS / 1000,
  });
});

/** Cancela la prueba programada si aún no se ha ejecutado. */
app.post('/auto-copy/cancelar-prueba', (req, res) => {
  pruebaDeadlineMs = null;
  if (pruebaTimeoutId) {
    clearTimeout(pruebaTimeoutId);
    pruebaTimeoutId = null;
  }
  appendPruebaLog('CANCELADA');
  const config = autoCopyConfig.readConfig();
  config.pruebaEjecutarEn = null;
  config.pruebaNoAntesDe = null;
  autoCopyConfig.writeConfig(config);
  res.json({ success: true, mensaje: 'Prueba programada cancelada.' });
});

app.get('/auto-copy/ejecutar-ahora/estado', (req, res) => {
  res.json({ success: true, estado: ejecutarAhoraState });
});

app.post('/auto-copy/ejecutar-ahora', (req, res) => {
  const started = startEjecutarAhoraBackground('manual');
  if (!started) {
    return res.status(409).json({
      success: false,
      mensaje: 'Ya hay una ejecución en curso. Espere a que termine.',
      estado: ejecutarAhoraState,
    });
  }
  return res.json({
    success: true,
    mensaje: 'Ejecución iniciada en segundo plano.',
    estado: ejecutarAhoraState,
  });
});

// Diagnóstico amplio (local, PHP/BD, SSH remoto). Botón en vista Segundómetro.
app.get('/diagnostico', async (req, res) => {
  try {
    const { pruebas, resumen } = await ejecutarDiagnosticoCompleto();
    return res.json({ success: true, pruebas, resumen });
  } catch (e) {
    return res.status(500).json({ success: false, mensaje: e.message, pruebas: [], resumen: '' });
  }
});

// ─── Catch-up al arrancar ───────────────────────────────────────────────────
// Cuando el agente estuvo apagado y se perdieron horarios automáticos,
// al arrancar se detectan los reportes sin procesar (estado BD != 'ok')
// y se copian uno por uno, esperando CATCHUP_INTERVAL_MINUTES (default 10)
// minutos entre cada copia para que el script tenga tiempo de procesar.

let catchUpRunning = false;
let catchUpStatus = { running: false, pendientes: [], procesados: 0, errores: 0, log: [], completado: false };

function catchUpLog(msg) {
  const ts = new Date().toISOString();
  const line = ts + ' ' + msg;
  console.log('[catch-up]', msg);
  catchUpStatus.log.push(line);
  if (catchUpStatus.log.length > 200) catchUpStatus.log.shift();
}

/**
 * Copia un archivo específico +1s en el servidor remoto para disparar el monitoreo.
 */
async function runCatchUpCopy(nombreArchivo) {
  const nombreDestino = nombreDestinoPlusOneSeg(nombreArchivo);
  if (!nombreDestino) return { success: false, mensaje: 'Formato inválido: ' + nombreArchivo };

  const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
  const fileEsc = (s) => s.replace(/'/g, "'\\''");
  const existsCheck = await remoteZipExists(nombreDestino);
  if (!existsCheck.ok) {
    return { success: false, mensaje: 'No se pudo verificar destino: ' + (existsCheck.error || 'Error SSH'), origen: nombreArchivo, destino: nombreDestino };
  }
  if (existsCheck.exists) {
    return {
      success: true,
      mensaje: 'Destino ya existía; se omite sobreescritura para evitar reprocesos.',
      origen: nombreArchivo,
      destino: nombreDestino,
      yaExistiaDestino: true,
    };
  }
  const cmd = `cd '${dirEsc}' && sudo cp '${fileEsc(nombreArchivo)}' '${fileEsc(nombreDestino)}'`;
  const result = await runCommand(cmd);
  if (!result.success) {
    return { success: false, mensaje: result.error || 'Error al copiar', origen: nombreArchivo, destino: nombreDestino };
  }

  // Verificar que el archivo efectivamente se creó
  let verify = { success: false, output: '' };
  for (let i = 0; i < 3; i++) {
    verify = await runCommand(`cd '${dirEsc}' && ls -1 '${fileEsc(nombreDestino)}' 2>/dev/null`);
    if (verify.success && verify.output.trim()) break;
    await new Promise((r) => setTimeout(r, 1200 * (i + 1)));
  }
  if (!verify.success || !verify.output.trim()) {
    return { success: false, mensaje: 'El archivo no se creó en el servidor', origen: nombreArchivo, destino: nombreDestino };
  }
  return { success: true, mensaje: 'Copiado OK', origen: nombreArchivo, destino: nombreDestino };
}

/**
 * Al arrancar: detecta reportes no procesados (estado BD != 'ok') después del
 * último reporte OK y los copia +1s uno por uno con intervalo configurable.
 */
async function runStartupCatchUp() {
  if (catchUpRunning) return;
  catchUpRunning = true;
  catchUpStatus = { running: true, pendientes: [], procesados: 0, errores: 0, log: [], completado: false };

  const CATCHUP_WAIT_INICIO_MS = Math.max(5000, parseInt(process.env.CATCHUP_WAIT_INICIO_MS || '30000', 10));
  const CATCHUP_INTERVAL_MS = Math.max(60000, parseInt(process.env.CATCHUP_INTERVAL_MINUTES || '10', 10) * 60 * 1000);

  catchUpLog('Iniciando en ' + Math.round(CATCHUP_WAIT_INICIO_MS / 1000) + 's...');
  await new Promise((r) => setTimeout(r, CATCHUP_WAIT_INICIO_MS));
  catchUpLog('Verificando reportes pendientes al arrancar...');

  try {
    let nowCdmx;
    try { nowCdmx = await getAccurateCdmxNow(); } catch (_) { nowCdmx = getCdmxLocalSync(); }

    const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
    const lsResult = await runCommand(`cd '${dirEsc}' && ls -l mega_rpt_*.csv.zip 2>/dev/null`);
    if (!lsResult.success) {
      catchUpLog('No se pudo listar archivos: ' + (lsResult.error || 'Error SSH'));
      catchUpStatus.running = false;
      catchUpRunning = false;
      return;
    }

    const archivos = parseListOutput(lsResult.output, nowCdmx);
    if (!archivos.length) {
      catchUpLog('Sin archivos en el servidor. Sin pendientes.');
      catchUpStatus.completado = true;
      catchUpStatus.running = false;
      catchUpRunning = false;
      return;
    }

    // Ordenar de más antiguo a más reciente
    archivos.sort((a, b) => a.timestamp - b.timestamp);
    const nombres = archivos.map(a => a.nombre);

    catchUpLog('Archivos en servidor: ' + nombres.length + '. Consultando estado BD...');
    const bdResult = await consultarEstadoBDLote(nombres);
    if (!bdResult.success) {
      catchUpLog('No se pudo consultar BD: ' + (bdResult.error || 'Sin respuesta'));
      catchUpStatus.running = false;
      catchUpRunning = false;
      return;
    }

    // Encontrar el último archivo con estado 'ok'
    let lastOkIdx = -1;
    for (let i = archivos.length - 1; i >= 0; i--) {
      if (bdResult.estados[archivos[i].nombre] === 'ok') {
        lastOkIdx = i;
        break;
      }
    }

    if (lastOkIdx >= 0) {
      catchUpLog('Último OK en BD: ' + archivos[lastOkIdx].nombre);
    } else {
      catchUpLog('Ningún archivo con estado OK encontrado en BD.');
    }

    // Pendientes = archivos después del último OK que no están 'ok'
    const pendientes = archivos.slice(lastOkIdx + 1).filter(
      (a) => bdResult.estados[a.nombre] !== 'ok'
    );

    catchUpStatus.pendientes = pendientes.map(a => a.nombre);

    if (!pendientes.length) {
      catchUpLog('Todos los reportes están OK. Sin pendientes.');
      catchUpStatus.completado = true;
      catchUpStatus.running = false;
      catchUpRunning = false;
      return;
    }

    catchUpLog(pendientes.length + ' reporte(s) pendiente(s): ' + pendientes.map(a => a.nombre).join(', '));

    // Iniciar monitorear en background para que inotifywait detecte las copias
    let monitorPid = null;
    const config = autoCopyConfig.readConfig();
    if (config.preRunMonitoreo && MONITOREAR_SCRIPT) {
      try {
        const scriptEsc = MONITOREAR_SCRIPT.replace(/'/g, "'\\''");
        const warmup = Math.max(3, parseInt(process.env.MONITOREAR_PRE_WARMUP_SEC || '5', 10));
        catchUpLog('Iniciando monitoreo previo (warmup ' + warmup + 's)...');
        const cmdMon = "nohup sudo bash '" + scriptEsc + "' >/dev/null 2>&1 & echo MON_PID:$!; sleep " + warmup;
        const monResult = await runCommand(cmdMon);
        const pidMatch = (monResult.output || '').match(/MON_PID:(\d+)/);
        if (pidMatch && pidMatch[1]) {
          monitorPid = pidMatch[1];
          catchUpLog('Monitor activo (PID ' + monitorPid + ')');
        } else {
          catchUpLog('Monitor iniciado (PID no capturado)');
        }
      } catch (e) {
        catchUpLog('No se pudo iniciar monitoreo: ' + e.message);
      }
    }

    // Procesar cada archivo pendiente con intervalo entre ellos
    for (let i = 0; i < pendientes.length; i++) {
      if (i > 0) {
        catchUpLog('Esperando ' + Math.round(CATCHUP_INTERVAL_MS / 60000) + ' min antes del siguiente (' + (i + 1) + '/' + pendientes.length + ')...');
        await new Promise((r) => setTimeout(r, CATCHUP_INTERVAL_MS));
      }

      const archivo = pendientes[i];
      catchUpLog('Copiando (' + (i + 1) + '/' + pendientes.length + '): ' + archivo.nombre);

      const result = await runCatchUpCopy(archivo.nombre);
      if (result.success) {
        catchUpStatus.procesados++;
        catchUpLog('✓ ' + archivo.nombre + ' → ' + result.destino);
      } else {
        catchUpStatus.errores++;
        catchUpLog('✗ ' + archivo.nombre + ': ' + result.mensaje);
      }
    }

    // Cerrar monitorear al terminar
    if (monitorPid) {
      try {
        await runCommand('sudo kill -TERM ' + monitorPid + ' 2>/dev/null || true');
        catchUpLog('Monitor cerrado (PID ' + monitorPid + ')');
      } catch (_) {}
    }

    catchUpLog('Catch-up completado. Procesados: ' + catchUpStatus.procesados + ', errores: ' + catchUpStatus.errores);
    catchUpStatus.completado = true;
  } catch (e) {
    catchUpLog('Error inesperado: ' + e.message);
  } finally {
    catchUpStatus.running = false;
    catchUpRunning = false;
  }
}

/** Estado del catch-up para monitorear desde la UI si se necesita. */
app.get('/catch-up/estado', (req, res) => {
  res.json({ success: true, estado: catchUpStatus });
});

app.listen(PORT, () => {
  console.log('Segundómetro Agent escuchando en http://localhost:' + PORT);
  console.log('  Vista shell (clon): http://localhost:' + PORT + '/shell.html');
  console.log('  Pruebas JSON:       http://localhost:' + PORT + '/test.html');
  if (API_KEY) console.log('  API Key requerida (X-Api-Key)');
  startAutoCopyScheduler();
  startTruncarVentanaScheduler();
  // Catch-up: verifica reportes perdidos cuando el agente estuvo apagado
  runStartupCatchUp().catch((e) => console.error('[catch-up] Error al iniciar:', e.message));
});
