/**
 * Agente HTTP Gastos Cobranza — mismo patrón que segundometro-agent (express + API key opcional).
 * PHP en /gastoscobranza/* hace proxy aquí; el script Python vive en la máquina donde corre Node.
 *
 * Script por defecto: scripts/reporte_cobranza.py (copiado al repo). Opcional: REPORTE_COBRANZA_SCRIPT en .env.
 * Si no hay script y GASTOS_COBRANZA_DEMO no es "0", /run responde en modo prueba.
 *
 * EC Launcher: POST /ec-launcher/run ejecuta tools/ec-webhook-worker/worker.php o
 * ec-gc-excel-enrich/enrich_gc_excel.php (misma lógica que launcher/Lanzar.cmd, sin menú interactivo).
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
 */
const path = require('path');
const fs = require('fs');
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
  return String(process.env.GASTOS_COBRANZA_DEMO || '1').trim() !== '0';
}

const LOG_DIR = path.join(__dirname, 'logs');
const LOG_FILE = path.join(LOG_DIR, 'agente-gastos-cobranza.log');
/** Salida de scripts Python línea a línea hacia el log del agente (evita buffer de stdout sin TTY). */
const ENV_CON_PYTHON_UNBUFFERED = { ...process.env, PYTHONUNBUFFERED: '1' };
const REPORTE_DIR = path.join(__dirname, 'reporte');
const EC_UPLOAD_DIR = path.join(REPORTE_DIR, 'ec-uploads');
const SPARTA_LEDGER_ROOT = path.resolve(path.join(__dirname, '..', '..', '..'));
const EC_WORKER_DIR = path.join(SPARTA_LEDGER_ROOT, 'tools', 'ec-webhook-worker');
const EC_ENRICH_DIR = path.join(SPARTA_LEDGER_ROOT, 'tools', 'ec-gc-excel-enrich');
const DESCARGO_ESTATUS3_DIR = path.join(REPORTE_DIR, 'descargo_estatus3');

/** Texto corto en columna Estado (UI) → tooltip = frase completa operativa */
const ESTADO_REPORTE = {
  generado: { corto: 'Generado', detalle: 'Reporte generado' },
  procCartera: { corto: 'Proc. cartera', detalle: 'Procesando por cartera' },
  aplicCartera: { corto: 'Aplic. cartera', detalle: 'Aplicado y confirmado por cartera' },
  enWorker: { corto: 'En Worker', detalle: 'Ejecutando en Worker' },
  listaNegra: { corto: 'Lista negra', detalle: 'Registrado lista negra' },
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

/**
 * @returns {{ estado: string, estadoDetalle: string }}
 */
function resolverEstadoReporte(nombreArchivo, mapaJson) {
  const nom = String(nombreArchivo || '');
  const keyDirecto = mapaJson[nom] ?? mapaJson[nom.toLowerCase()];
  const k = typeof keyDirecto === 'string' ? keyDirecto.trim() : '';
  if (k && ESTADO_REPORTE_KEYS.has(k) && ESTADO_REPORTE[k]) {
    const { corto, detalle } = ESTADO_REPORTE[k];
    return { estado: corto, estadoDetalle: detalle };
  }
  if (/^reporte_cobranza_[A-Za-z0-9._-]+\.xlsx$/i.test(nom)) {
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
  ensureReporteDir();
  const scriptPath = getReporteScriptPath();
  res.json({
    success: true,
    servicio: 'gastos-cobranza-agent',
    timestamp: new Date().toISOString(),
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
  });
});

/** Listado de .xlsx en carpeta reporte/ (orden: más reciente primero). */
app.get('/reportes', (req, res) => {
  ensureReporteDir();
  let files = [];
  try {
    files = fs.readdirSync(REPORTE_DIR).filter((f) => f.toLowerCase().endsWith('.xlsx'));
  } catch (e) {
    return res.json({ success: false, mensaje: String(e.message || e), archivos: [] });
  }
  const mapaEstados = readEstadosReporteJson();
  const list = files
    .map((name) => {
      try {
        const p = path.join(REPORTE_DIR, name);
        const st = fs.statSync(p);
        const { estado, estadoDetalle } = resolverEstadoReporte(name, mapaEstados);
        return {
          nombre: name,
          bytes: st.size,
          modificado: st.mtime.toISOString(),
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

/** Descarga segura de un Excel de reporte/. */
app.get('/reportes/descargar', (req, res) => {
  ensureReporteDir();
  const raw = String(req.query.nombre || '');
  const nombre = path.basename(raw);
  if (
    !nombre ||
    nombre !== raw ||
    !nombre.toLowerCase().endsWith('.xlsx') ||
    !/^reporte_cobranza_[A-Za-z0-9._-]+\.xlsx$/.test(nombre)
  ) {
    return res.status(400).json({ success: false, mensaje: 'Nombre de archivo no permitido.' });
  }
  const p = path.join(REPORTE_DIR, nombre);
  if (!fs.existsSync(p) || !fs.statSync(p).isFile()) {
    return res.status(404).json({ success: false, mensaje: 'Archivo no encontrado.' });
  }
  res.download(p, nombre);
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
  const REPORTE_COBRANZA_SCRIPT = getReporteScriptPath();
  if (!REPORTE_COBRANZA_SCRIPT) {
    if (!demoPermitidoSinScript()) {
      return res.json({
        success: false,
        mensaje:
          'No hay scripts/reporte_cobranza.py ni REPORTE_COBRANZA_SCRIPT en .env. Para demo: no use GASTOS_COBRANZA_DEMO=0.',
      });
    }
    const ts = new Date().toISOString();
    const stdout =
      `[${ts}] MODO PRUEBA (sin script Python)\n` +
      'Coloque reporte_cobranza.py en scripts/ o defina REPORTE_COBRANZA_SCRIPT en .env.\n' +
      'OK — demo completada.\n';
    appendLog(`--- /run demo ${ts} ---\n${stdout}`);
    return res.json({
      success: true,
      codigo_salida: 0,
      stdout,
      stderr: '',
      demo: true,
    });
  }

  const esPy = REPORTE_COBRANZA_SCRIPT.toLowerCase().endsWith('.py');
  const cmd = esPy ? REPORTE_PYTHON : REPORTE_COBRANZA_SCRIPT;
  const args = esPy ? [REPORTE_COBRANZA_SCRIPT] : [];
  const cwd = esPy ? path.dirname(REPORTE_COBRANZA_SCRIPT) : path.dirname(REPORTE_COBRANZA_SCRIPT);

  appendLog(`--- /run inicio ${new Date().toISOString()} ${cmd} ${args.join(' ')} ---`);

  const child = spawn(cmd, args, {
    cwd,
    env: esPy ? ENV_CON_PYTHON_UNBUFFERED : { ...process.env },
    windowsHide: true,
  });

  let stdout = '';
  let stderr = '';
  const maxChunk = 512 * 1024;
  let respondio = false;
  const enviar = (payload) => {
    if (respondio) return;
    respondio = true;
    if (payload.codigo_salida !== undefined) {
      // No repetir stdout/stderr: ya se volcaron en tiempo real con child.stdout/stderr.on('data').
      appendLog(`--- cierre código ${payload.codigo_salida} ---`);
    } else if (payload.mensaje) {
      appendLog('--- error: ' + payload.mensaje);
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
    appendLog('[stderr] ' + s.replace(/\r/g, ''));
  });

  child.on('error', (err) => {
    appendLog('[error spawn] ' + (err.message || String(err)));
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

/** EC launcher: worker.php o enrich_gc_excel.php (paridad con launcher/Lanzar.cmd). */
app.post('/ec-launcher/run', express.json({ limit: '1mb' }), (req, res) => {
  ensureReporteDir();
  const tipo = String(req.body?.tipo || '').toLowerCase();
  const archivo = path.basename(String(req.body?.archivo || ''));
  const fechaCorte = String(req.body?.fechaCorte || '').trim();
  const column = String(req.body?.column != null ? req.body.column : 'ID CREDITO').trim() || 'ID CREDITO';
  const omitir = Math.max(0, parseInt(String(req.body?.omitir ?? '0'), 10) || 0);
  const soloColumnas = !!req.body?.soloColumnas;

  if (!archivo || archivo.toLowerCase().endsWith('.xlsx') === false) {
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
  const abs = path.resolve(path.join(baseDir, archivo));
  if (!abs.startsWith(baseDir + path.sep) && abs !== baseDir) {
    return res.status(400).json({ success: false, mensaje: 'Ruta de archivo no permitida.' });
  }
  if (!fs.existsSync(abs)) {
    const donde = origenCarpeta === 'reporte' ? 'reporte/' : 'reporte/ec-uploads';
    return res.status(404).json({ success: false, mensaje: `Archivo no encontrado en ${donde}.` });
  }

  const php = resolvePhpExe();
  /** Evita que PHP acumule stdout en tubería: el log del agente ve líneas al vuelo. */
  const phpUnbuf = ['-d', 'output_buffering=0', '-d', 'implicit_flush=1', '-d', 'zlib.output_compression=0'];
  let cwd;
  let args;
  if (tipo === 'worker') {
    if (!fs.existsSync(path.join(EC_WORKER_DIR, 'worker.php'))) {
      return res.status(500).json({
        success: false,
        mensaje: 'No existe tools/ec-webhook-worker/worker.php (¿raíz del repo correcta?).',
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
        mensaje: 'No existe tools/ec-gc-excel-enrich/enrich_gc_excel.php.',
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

  const env = { ...process.env, FECHA_CORTE: fechaCorte };
  appendLog(`--- ec-launcher ${tipo} archivo=${archivo} fecha=${fechaCorte} php=${php} ---`);

  const child = spawn(php, args, { cwd, env, windowsHide: true, shell: false });

  let stdout = '';
  let stderr = '';
  const maxChunk = 512 * 1024;
  let respondio = false;
  const enviar = (payload) => {
    if (respondio) return;
    respondio = true;
    if (payload.codigo_salida !== undefined) {
      appendLog(`--- ec-launcher cierre ${payload.codigo_salida} ---`);
    } else if (payload.mensaje) {
      appendLog('--- ec-launcher error: ' + payload.mensaje);
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
    appendLog('[ec-launcher spawn] ' + (err.message || String(err)));
    enviar({ success: false, mensaje: err.message || String(err) });
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
    const payload = {
      success: code === 0,
      codigo_salida: code,
      stdout: outSlice,
      stderr: stderr.slice(-8000),
      tipo,
      archivo,
    };
    if (erroresReintentoCsv) {
      payload.errores_reintento_csv = erroresReintentoCsv;
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

  const archivo = path.basename(String(req.body?.archivo || ''));
  if (!archivo || archivo.toLowerCase().endsWith('.xlsx') === false) {
    return res.status(400).json({
      success: false,
      mensaje: 'Indique nombre de archivo .xlsx (ec-uploads o reporte/ con origenCarpeta=reporte).',
    });
  }

  const origenCarpeta = String(req.body?.origenCarpeta || 'ec-uploads').toLowerCase();
  const baseDir =
    origenCarpeta === 'reporte' ? path.resolve(REPORTE_DIR) : path.resolve(EC_UPLOAD_DIR);
  const abs = path.resolve(path.join(baseDir, archivo));
  if (!abs.startsWith(baseDir + path.sep) && abs !== baseDir) {
    return res.status(400).json({ success: false, mensaje: 'Ruta de archivo no permitida.' });
  }
  if (!fs.existsSync(abs)) {
    const donde = origenCarpeta === 'reporte' ? 'reporte/' : 'reporte/ec-uploads';
    return res.status(404).json({ success: false, mensaje: `Archivo no encontrado en ${donde}.` });
  }

  const inicioSemana = String(req.body?.inicioSemana || '').trim();
  const dryRun = !!req.body?.dryRun;
  const megaPhpDefaults = req.body?.megaPhpDefaults !== false;
  let estatus = 2;
  if (req.body?.estatus !== undefined && req.body?.estatus !== null && req.body?.estatus !== '') {
    const n = parseInt(String(req.body.estatus), 10);
    if (n === 0 || n === 1 || n === 2) estatus = n;
  }
  const mensaje = req.body?.mensaje != null ? String(req.body.mensaje).trim() : '';

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

  const cwd = path.dirname(scriptPath);
  appendLog(`--- carga-verificacion-semana archivo=${archivo} dryRun=${dryRun} inicioSemana=${inicioSemana || '(auto)'} ---`);

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
      appendLog(`--- carga-verificacion-semana cierre ${payload.codigo_salida} ---`);
    } else if (payload.mensaje) {
      appendLog('--- carga-verificacion-semana error: ' + payload.mensaje);
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
    appendLog('[carga-verificacion spawn] ' + (err.message || String(err)));
    enviar({ success: false, mensaje: err.message || String(err) });
  });
  child.on('close', (code) => {
    enviar({
      success: code === 0,
      codigo_salida: code,
      stdout: stdout.slice(-8000),
      stderr: stderr.slice(-8000),
      archivo,
    });
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
  const nombre = tipo === 'guia' ? 'guia_descargo.json' : 'descargo_estatus3.xlsx';
  if (nombre !== 'descargo_estatus3.xlsx' && nombre !== 'guia_descargo.json') {
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

// ─── Auto /run a las 08:00 hora CDMX (reloj ciud. México, no TZ del servidor) ───
function getCdmxYmd(d = new Date()) {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'America/Mexico_City',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(d);
  const y = parts.find((p) => p.type === 'year').value;
  const mo = parts.find((p) => p.type === 'month').value;
  const da = parts.find((p) => p.type === 'day').value;
  return `${y}-${mo}-${da}`;
}

function getCdmxHourMin(d = new Date()) {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'America/Mexico_City',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).formatToParts(d);
  const h = parseInt(parts.find((p) => p.type === 'hour').value, 10);
  const m = parseInt(parts.find((p) => p.type === 'minute').value, 10);
  return { h, m };
}

/** Lunes=0 … domingo=6 (igual que Python weekday del script). */
function cdmxHoyPyWeekday(d = new Date()) {
  const s = new Intl.DateTimeFormat('en-US', { timeZone: 'America/Mexico_City', weekday: 'short' }).format(d);
  const map = { Mon: 0, Tue: 1, Wed: 2, Thu: 3, Fri: 4, Sat: 5, Sun: 6 };
  return map[s];
}

function parseAuto8amHoyPyFilter() {
  const raw = (process.env.AUTO_8AM_CDMX_HOY_PY || '').trim();
  if (!raw) return null;
  const s = new Set();
  for (const x of raw.split(',')) {
    const n = parseInt(x.trim(), 10);
    if (!Number.isNaN(n) && n >= 0 && n <= 6) s.add(n);
  }
  return s.size ? s : null;
}

const AUTO_8AM_HOY_PY = parseAuto8amHoyPyFilter();
let lastAuto8amCdmxYmd = null;

function dispararRunInterno() {
  const url = `http://127.0.0.1:${PORT}/run`;
  /** @type {Record<string, string>} */
  const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
  if (API_KEY) headers['X-Api-Key'] = API_KEY;
  fetch(url, { method: 'POST', headers, body: '{}' }).catch((e) => {
    appendLog('[auto-8am] fetch /run: ' + String(e.message || e));
  });
}

function tickAuto8amCdmx() {
  if ((process.env.AUTO_EJECUTAR_8AM_CDMX || '0').trim() !== '1') return;
  const d = new Date();
  const { h, m } = getCdmxHourMin(d);
  if (h !== 8 || m > 1) return;
  const ymd = getCdmxYmd(d);
  if (lastAuto8amCdmxYmd === ymd) return;
  if (AUTO_8AM_HOY_PY && !AUTO_8AM_HOY_PY.has(cdmxHoyPyWeekday(d))) return;
  lastAuto8amCdmxYmd = ymd;
  appendLog(`[auto-8am] CDMX ${ymd} 08:00 → POST /run (Python: ayer CDMX como fecha de negocio)`);
  dispararRunInterno();
}

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
  setInterval(tickAuto8amCdmx, 30_000);
  tickAuto8amCdmx();
});
