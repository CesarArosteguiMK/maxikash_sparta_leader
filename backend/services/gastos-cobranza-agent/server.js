/**
 * Agente HTTP Gastos Cobranza — mismo patrón que segundometro-agent (express + API key opcional).
 * PHP en /gastoscobranza/* hace proxy aquí; el script Python vive en la máquina donde corre Node.
 */
const path = require('path');
const { spawn } = require('child_process');
try {
  require('dotenv').config({ path: path.join(__dirname, '.env') });
} catch (_) {}

const express = require('express');

const PORT = process.env.PORT || 3120;
const API_KEY = process.env.API_KEY || '';
/** Ruta absoluta al .py o al intérprete + script (documentar en .env del servidor). */
const REPORTE_COBRANZA_SCRIPT = (process.env.REPORTE_COBRANZA_SCRIPT || '').trim();
const REPORTE_PYTHON = (process.env.REPORTE_PYTHON || 'python').trim();

const app = express();
app.use(express.json());

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

app.get('/health', (req, res) => {
  res.json({
    success: true,
    servicio: 'gastos-cobranza-agent',
    timestamp: new Date().toISOString(),
    script_configurado: REPORTE_COBRANZA_SCRIPT.length > 0,
  });
});

/**
 * Ejecuta reporte_cobranza.py (o el binario configurado). Sin script en .env devuelve mensaje claro.
 */
app.post('/run', (req, res) => {
  if (!REPORTE_COBRANZA_SCRIPT) {
    return res.json({
      success: false,
      mensaje:
        'Defina REPORTE_COBRANZA_SCRIPT en .env del agente (ruta absoluta a reporte_cobranza.py).',
    });
  }

  const esPy = REPORTE_COBRANZA_SCRIPT.toLowerCase().endsWith('.py');
  const cmd = esPy ? REPORTE_PYTHON : REPORTE_COBRANZA_SCRIPT;
  const args = esPy ? [REPORTE_COBRANZA_SCRIPT] : [];
  const cwd = esPy ? path.dirname(REPORTE_COBRANZA_SCRIPT) : path.dirname(REPORTE_COBRANZA_SCRIPT);

  const child = spawn(cmd, args, {
    cwd,
    env: { ...process.env },
    windowsHide: true,
  });

  let stdout = '';
  let stderr = '';
  const maxChunk = 512 * 1024;
  let respondio = false;
  const enviar = (payload) => {
    if (respondio) return;
    respondio = true;
    res.json(payload);
  };

  child.stdout.on('data', (d) => {
    if (stdout.length < maxChunk) stdout += d.toString();
  });
  child.stderr.on('data', (d) => {
    if (stderr.length < maxChunk) stderr += d.toString();
  });

  child.on('error', (err) => {
    enviar({
      success: false,
      mensaje: err.message || String(err),
    });
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

app.listen(PORT, () => {
  console.log('[gastos-cobranza-agent] escuchando en', PORT);
});
