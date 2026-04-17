/**
 * Agente: ejecuta periódicamente backend/cronjobs/enviar_primeros_pagos_lunes.php
 * (misma lógica que Programador de tareas; respeta PrimerosPagosAutoSwitch en PHP).
 *
 * Los horarios de envío (slots 07:45, 09:45, …, 23:50 CDMX) los calcula SOLO PHP con
 * date_default_timezone_set('America/Mexico_City') al arrancar el cron — no usa
 * la zona horaria del sistema operativo del servidor para eso.
 *
 * Calendario: los **lunes** (CDMX) no se ejecuta el cron PHP (sin correos);
 * de martes a domingo sí. Así se evita trabajo inútil en el agente y coincide
 * con enviar_primeros_pagos_lunes.php.
 *
 * Sin dependencias de Express: HTTP mínimo nativo para comprobar que sigue vivo.
 */

const fs = require('fs');
const path = require('path');
const http = require('http');
const { spawn } = require('child_process');

require('dotenv').config({ path: path.join(__dirname, '.env') });

/** Default 10 minutos (600000 ms). Mínimo 60 s. */
const INTERVAL_MS = Math.max(60000, parseInt(process.env.INTERVAL_MS || '600000', 10));
const HTTP_PORT = parseInt(process.env.HTTP_PORT || '3110', 10);

function resolveProjectRoot() {
  if (process.env.SPARTA_ROOT) {
    return path.resolve(process.env.SPARTA_ROOT);
  }
  // backend/services/correos-primeros-pagos-agent -> subir 3 niveles = raíz del repo
  return path.resolve(__dirname, '..', '..', '..');
}

function resolvePhpScript() {
  if (process.env.PHP_CRON_SCRIPT) {
    return path.resolve(process.env.PHP_CRON_SCRIPT);
  }
  const root = resolveProjectRoot();
  return path.join(root, 'backend', 'cronjobs', 'enviar_primeros_pagos_lunes.php');
}

function resolvePhpExe() {
  const fromEnv = process.env.PHP_PATH;
  if (fromEnv && fs.existsSync(fromEnv)) return fromEnv;
  const candidates = [
    'C:\\xampp\\php\\php.exe',
    'C:\\Program Files\\PHP\\php.exe',
    path.join(process.env.ProgramFiles || 'C:\\Program Files', 'PHP', 'php.exe'),
    path.join(process.env.LocalAppData || '', 'Programs', 'Php', 'php.exe'),
  ];
  for (const c of candidates) {
    if (c && fs.existsSync(c)) return c;
  }
  return 'php';
}

function pathAutoSwitch() {
  return path.join(resolveProjectRoot(), 'backend', 'cronjobs', 'logs', 'primeros_pagos_auto_switch.json');
}

function isAutoEnabled() {
  try {
    const p = pathAutoSwitch();
    if (!fs.existsSync(p)) return false;
    const j = JSON.parse(fs.readFileSync(p, 'utf8'));
    return Boolean(j.enabled);
  } catch {
    return false;
  }
}

/** true si hoy es lunes en America/Mexico_City (alineado al cron PHP). */
function isMondayCDMX() {
  const wd = new Date().toLocaleDateString('en-US', {
    timeZone: 'America/Mexico_City',
    weekday: 'long',
  });
  return wd === 'Monday';
}

const phpExe = resolvePhpExe();
const phpScript = resolvePhpScript();

if (!fs.existsSync(phpScript)) {
  console.error('[correos-agent] No existe el script PHP:', phpScript);
  console.error('[correos-agent] Ajuste SPARTA_ROOT o PHP_CRON_SCRIPT en .env');
  process.exit(1);
}

const pidFile = path.join(__dirname, 'correos_agent.pid');
fs.writeFileSync(pidFile, String(process.pid), 'utf8');

function cleanupPid() {
  try {
    if (fs.existsSync(pidFile)) {
      const saved = fs.readFileSync(pidFile, 'utf8').trim();
      if (saved === String(process.pid)) fs.unlinkSync(pidFile);
    }
  } catch (_) {}
}

process.on('SIGINT', () => {
  cleanupPid();
  process.exit(0);
});
process.on('SIGTERM', () => {
  cleanupPid();
  process.exit(0);
});

let running = false;
let lastSpawnAt = null;
let lastExitCode = null;
let lastAutoSkip = false;
let lastSkippedBecauseMonday = false;

function runPhpOnce() {
  return new Promise((resolve) => {
    const child = spawn(phpExe, [phpScript], {
      stdio: 'ignore',
      windowsHide: true,
    });
    child.on('exit', (code) => {
      lastExitCode = code;
      resolve(code ?? 0);
    });
    child.on('error', (err) => {
      console.error('[correos-agent] spawn error:', err.message);
      lastExitCode = -1;
      resolve(-1);
    });
  });
}

async function tick() {
  if (running) return;
  if (!isAutoEnabled()) {
    lastAutoSkip = true;
    lastSkippedBecauseMonday = false;
    return;
  }
  lastAutoSkip = false;
  if (isMondayCDMX()) {
    lastSkippedBecauseMonday = true;
    return;
  }
  lastSkippedBecauseMonday = false;
  running = true;
  lastSpawnAt = new Date().toISOString();
  try {
    await runPhpOnce();
  } finally {
    running = false;
  }
}

setInterval(() => {
  tick().catch((e) => console.error('[correos-agent]', e));
}, INTERVAL_MS);

tick().catch((e) => console.error('[correos-agent]', e));

console.log('[correos-agent] OK. PHP:', phpExe);
console.log('[correos-agent] Script:', phpScript);
console.log('[correos-agent] Intervalo:', INTERVAL_MS, 'ms (~' + Math.round(INTERVAL_MS / 60000) + ' min)');
console.log('[correos-agent] Horarios de envío: CDMX (America/Mexico_City) en el PHP del cron, no la hora del SO.');
if (HTTP_PORT > 0) {
  const server = http.createServer((req, res) => {
    const body = JSON.stringify(
      {
        ok: true,
        agent: 'correos-primeros-pagos',
        php: phpExe,
        script: phpScript,
        intervalMs: INTERVAL_MS,
        cronTimezone:
          'Los slots (07:45, 09:45, …, 23:50) usan America/Mexico_City (CDMX) dentro de enviar_primeros_pagos_lunes.php; no depende del huso del servidor.',
        envioCorreosDiasHabiles: 'martes a domingo (CDMX); los lunes no hay envío automático.',
        skippedBecauseMondayCDMX: lastSkippedBecauseMonday,
        autoSwitchEnabled: isAutoEnabled(),
        lastSkippedBecauseSwitchOff: lastAutoSkip,
        lastSpawnAt,
        lastExitCode,
        pid: process.pid,
      },
      null,
      2
    );
    res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
    res.end(body);
  });
  server.listen(HTTP_PORT, '127.0.0.1', () => {
    console.log('[correos-agent] Estado HTTP: http://127.0.0.1:' + HTTP_PORT + '/');
  });
} else {
  console.log('[correos-agent] HTTP desactivado (HTTP_PORT=0)');
}
