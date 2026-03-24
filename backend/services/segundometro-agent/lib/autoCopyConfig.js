/**
 * Configuración persistente para Copiar +1s automático.
 * Se guarda en data/auto-copy-config.json dentro del agente.
 */

const fs = require('fs');
const path = require('path');

const DATA_DIR = path.join(__dirname, '..', 'data');
const CONFIG_PATH = path.join(DATA_DIR, 'auto-copy-config.json');

const HORARIOS_DEFAULT = [
  '07:40', '09:40', '11:40', '13:40', '14:40', '16:40', '18:40', '20:40', '00:05'
];

const DEFAULTS = {
  enabled: false,
  preRunMonitoreo: true,
  horarios: [...HORARIOS_DEFAULT],
  lastRunBySlot: {},
  lastRunResult: null,
  /** Unix ms: cuando llegue la hora, el scheduler ejecuta una sola vez el job de prueba (último informe +1s). */
  pruebaEjecutarEn: null,
  /** Unix ms: no ejecutar por archivo antes de esta hora (evita disparo inmediato con timestamp viejo en disco). */
  pruebaNoAntesDe: null,
};

function ensureDataDir() {
  if (!fs.existsSync(DATA_DIR)) {
    fs.mkdirSync(DATA_DIR, { recursive: true });
  }
}

function readConfig() {
  try {
    if (fs.existsSync(CONFIG_PATH)) {
      const raw = fs.readFileSync(CONFIG_PATH, 'utf8');
      const data = JSON.parse(raw);
      return { ...DEFAULTS, ...data, horarios: data.horarios && data.horarios.length ? data.horarios : DEFAULTS.horarios };
    }
  } catch (e) {
    console.warn('[auto-copy] Error leyendo config:', e.message);
  }
  return { ...DEFAULTS };
}

function writeConfig(config) {
  ensureDataDir();
  const toWrite = {
    enabled: !!config.enabled,
    preRunMonitoreo: config.preRunMonitoreo !== false,
    horarios: Array.isArray(config.horarios) ? config.horarios : DEFAULTS.horarios,
    lastRunBySlot: config.lastRunBySlot || {},
    lastRunResult: config.lastRunResult ?? null,
    pruebaEjecutarEn: typeof config.pruebaEjecutarEn === 'number' && config.pruebaEjecutarEn > 0 ? config.pruebaEjecutarEn : null,
    pruebaNoAntesDe: typeof config.pruebaNoAntesDe === 'number' && config.pruebaNoAntesDe > 0 ? config.pruebaNoAntesDe : null,
  };
  fs.writeFileSync(CONFIG_PATH, JSON.stringify(toWrite, null, 2), 'utf8');
  return toWrite;
}

function updateLastRun(slot, fechaCdmx, result) {
  const config = readConfig();
  config.lastRunBySlot = config.lastRunBySlot || {};
  // Solo marcar el slot del día si la copia fue exitosa. Si falló, el scheduler puede reintentar
  // en el siguiente tick (30s) en lugar de dar por cerrado el horario hasta mañana.
  if (result && result.success === true) {
    config.lastRunBySlot[slot] = fechaCdmx;
  }
  config.lastRunResult = result;
  writeConfig(config);
}

/**
 * Obtiene la hora actual en zona CDMX (America/Mexico_City) como "HH:MM".
 */
function getHoraCDMX() {
  const s = new Date().toLocaleString('en-CA', { timeZone: 'America/Mexico_City', hour: '2-digit', minute: '2-digit', hour12: false });
  return s.length === 5 ? s : '0' + s;
}

/**
 * Obtiene la fecha actual en CDMX como "YYYY-MM-DD".
 */
function getFechaCDMX() {
  const parts = new Date().toLocaleString('en-CA', { timeZone: 'America/Mexico_City', year: 'numeric', month: '2-digit', day: '2-digit' }).split('-');
  if (parts.length === 3) return parts[0] + '-' + parts[1] + '-' + parts[2];
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return y + '-' + m + '-' + day;
}

/**
 * Calcula la próxima ejecución (siguiente horario en la lista).
 */
function getProximaEjecucion(config) {
  if (!config || !config.enabled || !config.horarios || !config.horarios.length) {
    return null;
  }
  const ahora = getHoraCDMX();
  const hoy = getFechaCDMX();
  const ordenados = [...config.horarios].sort();
  for (const slot of ordenados) {
    if (slot > ahora) {
      return { fecha: hoy, hora: slot, label: hoy + ' ' + slot };
    }
  }
  const manana = new Date(new Date().toLocaleString('en-US', { timeZone: 'America/Mexico_City' }));
  manana.setDate(manana.getDate() + 1);
  const mananaStr = manana.getFullYear() + '-' + String(manana.getMonth() + 1).padStart(2, '0') + '-' + String(manana.getDate()).padStart(2, '0');
  const primerSlot = ordenados[0];
  return { fecha: mananaStr, hora: primerSlot, label: mananaStr + ' ' + primerSlot };
}

module.exports = {
  CONFIG_PATH,
  readConfig,
  writeConfig,
  updateLastRun,
  getHoraCDMX,
  getFechaCDMX,
  getProximaEjecucion,
  HORARIOS_DEFAULT,
  DEFAULTS,
};
