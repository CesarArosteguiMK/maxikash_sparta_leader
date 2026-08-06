const TZ = 'America/Mexico_City';
const CACHE_TTL_MS = 20000;
const ALLOW_LOCAL_FALLBACK = (process.env.ALLOW_LOCAL_TIME_FALLBACK || '0') === '1';
const REMOTE_TIMEOUT_MS = Math.max(800, parseInt(process.env.CDMX_TIME_REMOTE_TIMEOUT_MS || '2500', 10) || 2500);
const REMOTE_FAIL_COOLDOWN_MS = Math.max(30000, parseInt(process.env.CDMX_TIME_REMOTE_FAIL_COOLDOWN_MS || '120000', 10) || 120000);
const MAX_REMOTE_CLOCK_SKEW_MS = Math.max(5000, parseInt(process.env.CDMX_TIME_MAX_SKEW_MS || '90000', 10) || 90000);

let cache = null;
let remoteBlockedUntilMs = 0;

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

async function fetchWithTimeout(url) {
  const ctrl = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), REMOTE_TIMEOUT_MS);
  try {
    const cacheBuster = '_sparta_now=' + Date.now();
    const freshUrl = url + (url.includes('?') ? '&' : '?') + cacheBuster;
    return await fetch(freshUrl, {
      method: 'GET',
      signal: ctrl.signal,
      cache: 'no-store',
      headers: { 'Cache-Control': 'no-cache', Pragma: 'no-cache' },
    });
  } finally {
    clearTimeout(timer);
  }
}

function wallClockMs(value) {
  const ymd = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(value && value.fecha || ''));
  const hms = /^(\d{2}):(\d{2}):(\d{2})$/.exec(String(value && value.horaSeg || ''));
  if (!ymd || !hms) return NaN;
  return Date.UTC(
    Number(ymd[1]), Number(ymd[2]) - 1, Number(ymd[3]),
    Number(hms[1]), Number(hms[2]), Number(hms[3])
  );
}

function validateRemoteClock(value, localReference = localFallbackNow()) {
  const remoteWall = wallClockMs(value);
  const localWall = wallClockMs(localReference);
  if (!Number.isFinite(remoteWall) || !Number.isFinite(localWall)) {
    throw new Error((value && value.source || 'fuente remota') + ' devolvio una hora invalida.');
  }
  const clockSkewMs = remoteWall - localWall;
  if (Math.abs(clockSkewMs) > MAX_REMOTE_CLOCK_SKEW_MS) {
    throw new Error(
      (value.source || 'fuente remota') +
      ' rechazada por desfase de ' + Math.round(clockSkewMs / 1000) + ' s (maximo ' +
      Math.round(MAX_REMOTE_CLOCK_SKEW_MS / 1000) + ' s).'
    );
  }
  return { ...value, clockSkewMs };
}

async function fetchWorldTimeApi() {
  const r = await fetchWithTimeout('https://worldtimeapi.org/api/timezone/America/Mexico_City');
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
  const r = await fetchWithTimeout(url);
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
  if (remoteBlockedUntilMs > nowMs) {
    if (ALLOW_LOCAL_FALLBACK) {
      const value = localFallbackNow();
      cache = { cachedAtMs: nowMs, value };
      return value;
    }
    throw new Error('Fuentes de hora remota en enfriamiento temporal.');
  }

  let value = null;
  let firstError = null;
  try {
    value = validateRemoteClock(await fetchWorldTimeApi());
  } catch (e) {
    firstError = e;
    try {
      value = validateRemoteClock(await fetchTimeApiIo());
    } catch (secondError) {
      if (!ALLOW_LOCAL_FALLBACK) {
        remoteBlockedUntilMs = Date.now() + REMOTE_FAIL_COOLDOWN_MS;
        throw new Error(
          'No se pudo obtener una hora CDMX remota confiable. ' +
          'worldtimeapi: ' + (firstError && firstError.message || 'fallo') + '; ' +
          'timeapi.io: ' + (secondError && secondError.message || 'fallo')
        );
      }
      value = localFallbackNow();
      remoteBlockedUntilMs = Date.now() + REMOTE_FAIL_COOLDOWN_MS;
    }
  }
  if (value && value.fromRemote) {
    remoteBlockedUntilMs = 0;
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
  _test: {
    MAX_REMOTE_CLOCK_SKEW_MS,
    wallClockMs,
    validateRemoteClock,
  },
};
