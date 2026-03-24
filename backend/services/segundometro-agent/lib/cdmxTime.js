const TZ = 'America/Mexico_City';
const CACHE_TTL_MS = 20000;
const ALLOW_LOCAL_FALLBACK = (process.env.ALLOW_LOCAL_TIME_FALLBACK || '0') === '1';

let cache = null;

function pad2(n) {
  return String(n).padStart(2, '0');
}

function formatByTz(date) {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: TZ,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
  }).formatToParts(date);
  const get = (k) => (parts.find(p => p.type === k) || {}).value || '00';
  const fecha = get('year') + '-' + get('month') + '-' + get('day');
  const hora = get('hour') + ':' + get('minute');
  const horaSeg = hora + ':' + get('second');
  return { fecha, hora, horaSeg };
}

function localFallbackNow() {
  const now = new Date();
  const out = formatByTz(now);
  return {
    ...out,
    source: 'local-fallback',
    fromRemote: false,
    timestampMs: now.getTime(),
  };
}

async function fetchWorldTimeApi() {
  const r = await fetch('https://worldtimeapi.org/api/timezone/America/Mexico_City', { method: 'GET' });
  if (!r.ok) throw new Error('worldtimeapi HTTP ' + r.status);
  const j = await r.json();
  const dt = j.datetime ? new Date(j.datetime) : (Number.isFinite(j.unixtime) ? new Date(j.unixtime * 1000) : null);
  if (!dt || Number.isNaN(dt.getTime())) throw new Error('worldtimeapi sin fecha válida');
  const out = formatByTz(dt);
  return {
    ...out,
    source: 'worldtimeapi',
    fromRemote: true,
    timestampMs: dt.getTime(),
  };
}

async function fetchTimeApiIo() {
  const url = 'https://timeapi.io/api/Time/current/zone?timeZone=America/Mexico_City';
  const r = await fetch(url, { method: 'GET' });
  if (!r.ok) throw new Error('timeapi.io HTTP ' + r.status);
  const j = await r.json();
  const d = String(j.date || '').trim();       // MM/DD/YYYY
  const t = String(j.time || '').trim();       // HH:mm
  if (!d || !t) throw new Error('timeapi.io sin date/time');
  const dp = d.split('/');
  const tp = t.split(':');
  if (dp.length !== 3 || tp.length < 2) throw new Error('timeapi.io formato inválido');
  const mm = pad2(dp[0]);
  const dd = pad2(dp[1]);
  const yyyy = dp[2];
  const hh = pad2(tp[0]);
  const mi = pad2(tp[1]);
  const ss = pad2(j.seconds != null ? j.seconds : 0);
  return {
    fecha: yyyy + '-' + mm + '-' + dd,
    hora: hh + ':' + mi,
    horaSeg: hh + ':' + mi + ':' + ss,
    source: 'timeapi.io',
    fromRemote: true,
    timestampMs: Date.now(),
  };
}

async function getAccurateCdmxNow() {
  const nowMs = Date.now();
  if (cache && (nowMs - cache.cachedAtMs) < CACHE_TTL_MS) return cache.value;

  let value = null;
  try {
    value = await fetchWorldTimeApi();
  } catch (_) {
    try {
      value = await fetchTimeApiIo();
    } catch (_) {
      if (!ALLOW_LOCAL_FALLBACK) {
        throw new Error('No se pudo obtener hora CDMX remota (worldtimeapi/timeapi.io).');
      }
      value = localFallbackNow();
    }
  }
  cache = { cachedAtMs: nowMs, value };
  return value;
}

/**
 * Hora CDMX sin red: no lanza. Úsese cuando getAccurateCdmxNow falle pero haga falta
 * responder (p. ej. /reportes/estado) sin tumbar todo el endpoint.
 */
function getCdmxLocalSync() {
  return localFallbackNow();
}

module.exports = {
  getAccurateCdmxNow,
  getCdmxLocalSync,
};
