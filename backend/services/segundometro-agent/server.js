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
const { runCommand, downloadFileStream, runCommandStream } = require('./lib/sshClient');
const autoCopyConfig = require('./lib/autoCopyConfig');
const { getAccurateCdmxNow, getCdmxLocalSync } = require('./lib/cdmxTime');

const app = express();
const PORT = process.env.PORT || 3100;
const REMOTE_DIR = process.env.REMOTE_DIR || '/home/usuariossftp/s2/mega_reporte';
const MONITOREAR_SCRIPT = process.env.MONITOREAR_SCRIPT || '/home/jesus/scripts/monitorear.sh';
const API_KEY = process.env.API_KEY || '';
/** Ruta del front PHP para estado BD (misma app que /segundometro/shell). */
const ESTADO_BD_PATH = 'index.php?url=segundometro/estadoReportesAgente';
const SEGUNDOMETRO_AGENT_KEY = process.env.SEGUNDOMETRO_AGENT_KEY || '';
/** URL que funcionó la última vez (evita probar en cada request). */
let estadoBdUrlCached = null;

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
  // Slot de noche: automático principal en 23:52, respaldo al día siguiente 00:05.
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

function parseListOutput(output, refCdmx) {
  const archivos = [];
  const hoyStr = refCdmx && refCdmx.fecha ? refCdmx.fecha : '1970-01-01';
  const ayerStr = sumarDiasYmd(hoyStr, -1);
  const fechalimite = ymdCompact(ayerStr);
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
    if (fechaArchivo < fechalimite) continue;

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
        const nowCdmx = await getAccurateCdmxNow();
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

async function postEstadoBdPingVacio() {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 12000);
  try {
    const result = await postEstadoBdJson({ nombres: [] }, controller.signal);
    if (result.error || !result.data) {
      return { ok: false, error: result.error || 'Sin respuesta', url: result.url || null };
    }
    const { r, data } = result;
    if (!r || !r.ok || !data || !data.success) {
      return {
        ok: false,
        error: (data && data.mensaje) ? data.mensaje : 'HTTP ' + (r ? r.status : '?'),
        url: result.url,
      };
    }
    return { ok: true, url: result.url, horaPhp: String(data.hora_servidor_cdmx || '') };
  } catch (e) {
    return { ok: false, error: e.message };
  } finally {
    clearTimeout(timeoutId);
  }
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
  const sshOpts = { timeoutMs: 45000 };
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

  const ping = await postEstadoBdPingVacio();
  pruebas.push({
    nombre: 'PHP estadoReportesAgente (alcance BD)',
    ok: ping.ok,
    detalle: ping.ok
      ? 'JSON OK. URL: ' + (ping.url || '') + (ping.horaPhp ? '. Hora servidor CDMX (PHP): ' + ping.horaPhp : '')
      : (ping.error || 'Fallo') + (ping.url ? ' — URL: ' + ping.url : ''),
    grupo: 'bd',
    ayuda: 'Misma ruta POST que usa el agente para consultar MySQL.',
    cubre: 'BD',
  });

  if (ping.ok && ping.horaPhp && nowCdmx && nowCdmx.horaSeg) {
    const parsePhp = /(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})/.exec(ping.horaPhp);
    if (parsePhp) {
      const tAgent = new Date(nowCdmx.fecha + 'T' + nowCdmx.horaSeg).getTime();
      const tPhp = new Date(parsePhp[1] + 'T' + parsePhp[2]).getTime();
      if (Number.isFinite(tAgent) && Number.isFinite(tPhp)) {
        const dsec = Math.round((tAgent - tPhp) / 1000);
        pruebas[pruebas.length - 1].detalle += ' Diferencia agente−PHP ~' + dsec + ' s.';
      }
    }
  }

  let rSsh = { success: false, output: '', error: '' };
  let rttMs = 0;
  try {
    const t0 = Date.now();
    rSsh = await runCommand('echo DIAG_OK', sshOpts);
    rttMs = Date.now() - t0;
  } catch (e) {
    rSsh = { success: false, output: '', error: e.message };
  }
  pruebas.push({
    nombre: 'Conexión SSH al servidor',
    ok: rSsh.success && String(rSsh.output || '').includes('DIAG_OK'),
    detalle: rSsh.success ? host + ' — OK (eco ~' + rttMs + ' ms)' : (rSsh.error || 'Fallo'),
    grupo: 'remoto',
    cubre: 'Listar / Copiar / Monitorear',
  });

  if (!rSsh.success || !String(rSsh.output || '').includes('DIAG_OK')) {
    pruebas.push({
      nombre: 'Pruebas remotas adicionales',
      ok: false,
      detalle: 'Omitidas: sin sesión SSH válida.',
      grupo: 'remoto',
    });
    return { pruebas, resumen: armarResumenDiagnostico(pruebas) };
  }

  const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
  const scriptEsc = monScript.replace(/'/g, "'\\''");

  const rSudo = await runCommand("sudo -n true 2>/dev/null && echo SUDO_NOPASS || echo SUDO_FAIL", sshOpts);
  const sudoOk = rSudo.success && String(rSudo.output || '').includes('SUDO_NOPASS');
  pruebas.push({
    nombre: 'sudo sin contraseña (NOPASSWD)',
    ok: sudoOk,
    detalle: sudoOk
      ? 'sudo -n aceptado (necesario para cp y monitoreo previo).'
      : 'sudo -n falló — puede colgar Copiar +1s. Salida: ' + String(rSudo.output || rSudo.error || '').trim().slice(0, 160),
    grupo: 'remoto',
    ayuda: 'Si falla, el servidor puede pedir contraseña sin TTY.',
    cubre: 'Copiar +1s / monitoreo previo',
  });

  const rScript = await runCommand(
    "if test -f '" + scriptEsc + "' && test -x '" + scriptEsc + "'; then echo SCRIPT_OK; else echo SCRIPT_BAD; fi",
    sshOpts
  );
  const scriptOk = rScript.success && String(rScript.output || '').includes('SCRIPT_OK');
  pruebas.push({
    nombre: 'Script monitoreo (remoto)',
    ok: scriptOk,
    detalle: scriptOk ? monScript + ' — existe y es ejecutable.' : monScript + ' — ' + String(rScript.output || rScript.error || '').trim().slice(0, 200),
    grupo: 'remoto',
    cubre: 'Monitorear / preRunMonitoreo',
  });

  const rDf = await runCommand("df -hP '" + dirEsc + "' 2>/dev/null | tail -1", sshOpts);
  pruebas.push({
    nombre: 'Espacio en disco (df)',
    ok: rDf.success && String(rDf.output || '').trim().length > 0,
    detalle: rDf.success && rDf.output.trim() ? rDf.output.trim() : (rDf.error || 'No se pudo leer df'),
    grupo: 'remoto',
  });

  const rLd = await runCommand("ls -ld '" + dirEsc + "' 2>&1", sshOpts);
  pruebas.push({
    nombre: 'Permisos directorio mega (ls -ld)',
    ok: rLd.success && !String(rLd.output || '').includes('No such file'),
    detalle: rLd.output ? rLd.output.trim().slice(0, 240) : (rLd.error || 'Fallo'),
    grupo: 'remoto',
  });

  const lsFull = await runCommand(`cd '${dirEsc}' && ls -l mega_rpt_*.csv.zip 2>/dev/null`, sshOpts);
  const archivos = lsFull.success ? parseListOutput(lsFull.output, { fecha: nowCdmx.fecha }) : [];
  const ultimo = archivos[0];
  const ultimoNombre = ultimo ? ultimo.nombre : null;

  let detList = 'REMOTE_DIR accesible.';
  if (ultimo) {
    const edadMin = Math.max(0, Math.round((Date.now() - ultimo.timestamp) / 60000));
    detList =
      'Último: ' +
      ultimo.nombre +
      ' · ' +
      ultimo.tamano +
      ' · ~' +
      edadMin +
      ' min desde timestamp del nombre. «Ejecutar ahora» usaría este.';
  } else if (lsFull.success && !lsFull.output.trim()) {
    detList = 'No hay mega_rpt_*.csv.zip en ventana (ayer/hoy). Ejecutar ahora: sin archivo.';
  } else {
    detList = 'Listado: ' + (lsFull.error || lsFull.output || 'error').slice(0, 160);
  }
  pruebas.push({
    nombre: 'Último informe mega en carpeta',
    ok: lsFull.success && !!ultimo,
    detalle: detList,
    grupo: 'remoto',
    ayuda: 'No sube archivos: solo lista lo ya en el servidor.',
    cubre: 'Listar / Ejecutar ahora',
  });

  if (ultimoNombre) {
    const dest = nombreDestinoPlusOneSeg(ultimoNombre);
    const fileEsc = (s) => s.replace(/'/g, "'\\''");
    const rDest = await runCommand(
      `cd '${dirEsc}' && if [ -f '${fileEsc(dest)}' ]; then echo EXISTS; else echo NEW_OK; fi`,
      sshOpts
    );
    const exists = rDest.success && String(rDest.output || '').includes('EXISTS');
    const simOk = rDest.success && (String(rDest.output || '').includes('NEW_OK') || exists);
    pruebas.push({
      nombre: 'Simulación Copiar +1s (sin escribir)',
      ok: simOk,
      detalle:
        'Origen ' +
        ultimoNombre +
        ' → destino ' +
        dest +
        ' — ' +
        (exists ? 'el destino ya existe en disco.' : 'destino aún no existe (cp sería nuevo).'),
      grupo: 'remoto',
      cubre: 'Copiar +1s',
    });
  } else {
    pruebas.push({
      nombre: 'Simulación Copiar +1s (sin escribir)',
      ok: false,
      detalle: 'Sin último ZIP válido; omitido.',
      grupo: 'remoto',
    });
  }

  const rPg = await runCommand("pgrep -af '[m]onitorear' 2>/dev/null || true", sshOpts);
  const pgOut = (rPg.output || '').trim();
  pruebas.push({
    nombre: 'Procesos relacionados con monitorear',
    ok: rPg.success,
    detalle: pgOut ? pgOut.slice(0, 300) : 'Ninguno coincidente (normal si no corre el script).',
    grupo: 'remoto',
    ayuda: 'pgrep en el servidor remoto.',
  });

  if (ultimoNombre) {
    const bdFile = await consultarEstadoBD(ultimoNombre);
    const st = bdFile.estado != null ? bdFile.estado : 'sin registro';
    pruebas.push({
      nombre: 'Estado en BD del último ZIP',
      ok: bdFile.success,
      detalle: bdFile.success
        ? ultimoNombre + ' → estado: ' + st + (bdFile.urlUsada ? ' · ' + bdFile.urlUsada : '')
        : (bdFile.error || 'Error consultando BD'),
      grupo: 'bd',
      cubre: 'pipeline MySQL',
    });
  } else {
    pruebas.push({
      nombre: 'Estado en BD del último ZIP',
      ok: false,
      detalle: 'Sin archivo remoto en ventana; omitido.',
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
    const nowCdmx = await getAccurateCdmxNow();
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
      const nowCdmx = await getAccurateCdmxNow();
      const hora = nowCdmx.hora;
      const fecha = nowCdmx.fecha;

    // Respaldo: a xx:40 (y 00:05 para el slot de 23:52) consulta BD;
    // si no está en BD (ok), dispara ejecución automática en background.
    const fallbackMeta = FALLBACK_DB_CHECK_MAP[hora] || null;
    if (config.enabled && fallbackMeta) {
      const fallbackKey = 'fallback_' + hora;
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
              mensaje: 'Respaldo ' + hora + ': BD OK para ' + nombreEsperado + ' (no se dispara copia).',
              esPrueba: false,
            };
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            console.log('[fallback]', hora, 'BD OK, sin acción:', nombreEsperado);
            return;
          }
          // Importante: si la verificación BD falla (endpoint inaccesible, login, timeout, etc.)
          // NO disparar copia para evitar falsos positivos. No consumir slot: reintento en ~30s.
          if (!bd.success || !bd.estado) {
            ejecutarAhoraState.lastResult = {
              success: false,
              mensaje: 'Respaldo ' + hora + ': no se pudo verificar BD para ' + nombreEsperado + ' (' + (bd.error || 'sin estado') + '). No se dispara copia.',
              esPrueba: false,
            };
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            console.warn('[fallback]', hora, 'sin verificación BD válida; no se dispara copia.', nombreEsperado, 'error=', bd.error || 'n/a');
            return;
          }
          // Si BD respondió "procesando", no disparamos respaldo para evitar falsos positivos.
          if (bd.estado === 'procesando') {
            ejecutarAhoraState.lastResult = {
              success: true,
              mensaje: 'Respaldo ' + hora + ': BD en estado procesando para ' + nombreEsperado + ' (sin acción).',
              esPrueba: false,
            };
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            console.log('[fallback]', hora, 'BD procesando, sin acción:', nombreEsperado);
            return;
          }
          // Solo si BD respondió explícitamente "error", se dispara respaldo.
          if (bd.estado !== 'error') {
            consumeFallbackSlot();
            ejecutarAhoraState.lastResult = {
              success: false,
              mensaje: 'Respaldo ' + hora + ': estado BD no esperado (' + bd.estado + ') para ' + nombreEsperado + '. No se dispara copia.',
              esPrueba: false,
            };
            ejecutarAhoraState.finishedAt = new Date().toISOString();
            console.warn('[fallback]', hora, 'estado BD no esperado; sin acción.', nombreEsperado, 'estadoBD=', bd.estado);
            return;
          }
          if (ejecutarAhoraState.running) {
            console.log('[fallback]', hora, 'BD no OK, pero ya hay ejecución en curso.');
            return;
          }
          const started = startEjecutarAhoraBackground('fallback-' + hora);
          if (started) consumeFallbackSlot();
          console.log('[fallback]', hora, started ? 'disparado (BD=error)' : 'no disparado (ya en curso)', nombreEsperado);
          } finally {
            fallbackAsyncRunning.delete(fallbackKey);
          }
        })();
        }
      }
    }

      if (!AUTO_COPY_MAIN_ENABLED) return;
      if (!config.enabled || !config.horarios || !config.horarios.length) return;
      if (!config.horarios.includes(hora)) return;
      const lastBySlotAuto = config.lastRunBySlot || {};
      if (lastBySlotAuto[hora] === fecha) return;
      if (autoCopyRunningSlots.has(hora)) return;
      autoCopyRunningSlots.add(hora);
      (async () => {
        try {
          await runAutoCopyJob(hora, fecha);
        } finally {
          autoCopyRunningSlots.delete(hora);
        }
      })();
    } catch (e) {
      console.error('[auto-copy] Error obteniendo hora CDMX remota:', e.message);
    }
  }, 30000);
  console.log('Auto Copiar +1s: programador activo (cada 30s). Horarios CDMX:', (autoCopyConfig.readConfig().horarios || []).join(', '));
}

app.get('/files', async (req, res) => {
  try {
    // No bloquear el listado si worldtimeapi/timeapi.io cuelgan o no hay red;
    // el Shell depende de este endpoint y los usuarios ven "no carga".
    let nowCdmx;
    try {
      nowCdmx = await Promise.race([
        getAccurateCdmxNow(),
        new Promise((_, rej) => setTimeout(() => rej(new Error('timeout hora CDMX')), 6000)),
      ]);
    } catch (_) {
      nowCdmx = getCdmxLocalSync();
    }
    const dirEsc = REMOTE_DIR.replace(/'/g, "'\\''");
    const cmd = `cd '${dirEsc}' && ls -l mega_rpt_*.csv.zip 2>/dev/null`;
    const result = await runCommand(cmd);
    if (!result.success) {
      return res.status(500).json({
        success: false,
        mensaje: 'No se pudieron listar los archivos: ' + (result.error || 'Error SSH'),
      });
    }
    const archivos = parseListOutput(result.output, nowCdmx);
    return res.json({ success: true, datos: archivos });
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

app.get('/files/:nombre/download', async (req, res) => {
  try {
    const nombre = (req.params.nombre || '').trim();
    if (!MEGA_RPT_FULL.test(nombre)) {
      return res.status(400).send('Formato de archivo inválido.');
    }
    const remotePath = REMOTE_DIR + '/' + nombre;
    const { stream, conn } = await downloadFileStream(remotePath);
    res.setHeader('Content-Type', 'application/zip');
    res.setHeader('Content-Disposition', 'attachment; filename="' + nombre + '"');
    stream.on('error', (e) => {
      try { conn.end(); } catch (_) {}
      if (!res.headersSent) res.status(500).send('Error al descargar: ' + e.message);
    });
    stream.on('end', () => { conn.end(); });
    stream.pipe(res);
  } catch (e) {
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
    const nowCdmx = await getAccurateCdmxNow();
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
  let nowCdmx;
  try {
    nowCdmx = await getAccurateCdmxNow();
  } catch (_) {
    nowCdmx = getCdmxLocalSync();
  }
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
  const nowCdmx = await getAccurateCdmxNow();
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
  const nowCdmx = await getAccurateCdmxNow();
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
  // Catch-up: verifica reportes perdidos cuando el agente estuvo apagado
  runStartupCatchUp().catch((e) => console.error('[catch-up] Error al iniciar:', e.message));
});
