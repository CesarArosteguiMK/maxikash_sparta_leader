const TRUNCAR_AUTO_START_MINUTE = 7 * 60;
const TRUNCAR_AUTO_END_MINUTE = 9 * 60 + 30;

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

function esVentanaTruncarMartesCdmx(nowCdmx) {
  if (!nowCdmx || !esMartesFecha(nowCdmx.fecha)) return false;
  const mm = minutosDesdeMedianocheDesdeNowCdmx(nowCdmx);
  if (mm === null) return false;
  return mm >= TRUNCAR_AUTO_START_MINUTE && mm < TRUNCAR_AUTO_END_MINUTE;
}

function parseHoraServidorCdmx(raw) {
  const match = /(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})/.exec(String(raw || ''));
  if (!match) throw new Error('PHP no devolvio hora_servidor_cdmx valida.');

  const y = Number(match[1]);
  const mo = Number(match[2]);
  const d = Number(match[3]);
  const h = Number(match[4]);
  const mi = Number(match[5]);
  const s = Number(match[6]);
  const check = new Date(Date.UTC(y, mo - 1, d, h, mi, s));
  if (
    check.getUTCFullYear() !== y ||
    check.getUTCMonth() !== mo - 1 ||
    check.getUTCDate() !== d ||
    check.getUTCHours() !== h ||
    check.getUTCMinutes() !== mi ||
    check.getUTCSeconds() !== s
  ) {
    throw new Error('PHP devolvio una fecha CDMX imposible.');
  }

  const fecha = match[1] + '-' + match[2] + '-' + match[3];
  const horaSeg = match[4] + ':' + match[5] + ':' + match[6];
  return {
    fecha,
    hora: match[4] + ':' + match[5],
    horaSeg,
    source: 'php-servidor',
    fromRemote: false,
    timestampMs: Date.now(),
  };
}

module.exports = {
  TRUNCAR_AUTO_START_MINUTE,
  TRUNCAR_AUTO_END_MINUTE,
  dayOfWeekFromYmd,
  esMartesFecha,
  minutosDesdeMedianocheDesdeNowCdmx,
  esVentanaTruncarMartesCdmx,
  parseHoraServidorCdmx,
};
