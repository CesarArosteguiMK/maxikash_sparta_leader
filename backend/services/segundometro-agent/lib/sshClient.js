/**
 * Cliente SSH usando la librería ssh2 (no depende de ssh/plink del sistema).
 * Lee la clave desde SSH_KEY_PATH y ejecuta comandos / SFTP en el servidor remoto.
 */

const { Client } = require('ssh2');
const path = require('path');
const fs = require('fs');

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function isTransientSshError(message) {
  const m = String(message || '').toLowerCase();
  return (
    m.includes('timed out while waiting for handshake') ||
    m.includes('handshake') ||
    m.includes('econnreset') ||
    m.includes('enetunreach') ||
    m.includes('etimedout') ||
    m.includes('ehostunreach')
  );
}

function loadConfig() {
  let env = process.env;
  try {
    require('dotenv').config({ path: path.join(__dirname, '..', '.env') });
    env = process.env;
  } catch (_) {}
  const defaultKey = path.join(__dirname, '..', 'keys', 'jesusssh4.unknown');
  const keyPath = env.SSH_KEY_PATH || defaultKey;
  const resolvedKey = path.isAbsolute(keyPath) ? keyPath : path.resolve(__dirname, '..', keyPath);
  if (!fs.existsSync(resolvedKey)) {
    throw new Error('Clave SSH no encontrada: ' + resolvedKey);
  }
  return {
    host: env.SSH_HOST || '34.173.106.81',
    port: parseInt(env.SSH_PORT || '22', 10),
    username: env.SSH_USER || 'jesus',
    privateKey: fs.readFileSync(resolvedKey, 'utf8'),
    readyTimeout: parseInt(env.SSH_READY_TIMEOUT_MS || '45000', 10),
    keepaliveInterval: parseInt(env.SSH_KEEPALIVE_INTERVAL_MS || '10000', 10),
    keepaliveCountMax: parseInt(env.SSH_KEEPALIVE_COUNT_MAX || '6', 10),
    algorithms: { serverHostKey: ['ssh-rsa', 'ssh-dss', 'ecdsa-sha2-nistp256', 'ecdsa-sha2-nistp384', 'ecdsa-sha2-nistp521'] },
  };
}

/**
 * Ejecuta un comando en el servidor remoto vía SSH.
 * @param {string} command
 * @param {{ timeoutMs?: number }} [options] — timeoutMs: 0 = sin límite; por defecto env SSH_COMMAND_TIMEOUT_MS o 120000 ms
 * @returns {Promise<{ success: boolean, output: string, error: string, code: number }>}
 */
function runCommand(command, options = {}) {
  const maxRetries = Math.max(0, parseInt(process.env.SSH_RUNCOMMAND_RETRIES || '2', 10) || 2);
  const retryDelayMs = Math.max(250, parseInt(process.env.SSH_RUNCOMMAND_RETRY_DELAY_MS || '1200', 10) || 1200);
  const envT = process.env.SSH_COMMAND_TIMEOUT_MS;
  let defaultTimeout = 120000;
  if (envT !== undefined && envT !== '') {
    const p = parseInt(envT, 10);
    defaultTimeout = Number.isFinite(p) ? Math.max(0, p) : 120000;
  }
  const timeoutMs = options.timeoutMs != null ? options.timeoutMs : defaultTimeout;

  function runOnce() {
    const config = loadConfig();
    return new Promise((resolve) => {
      const conn = new Client();
      let stdout = '';
      let stderr = '';
      let finished = false;
      let timer = null;

      function finish(out) {
        if (finished) return;
        finished = true;
        if (timer) {
          clearTimeout(timer);
          timer = null;
        }
        try {
          conn.end();
        } catch (_) {}
        resolve(out);
      }

      if (timeoutMs > 0) {
        timer = setTimeout(() => {
          finish({
            success: false,
            output: stdout.trim(),
            error:
              'Timeout SSH (' +
              Math.round(timeoutMs / 1000) +
              's): el comando no terminó. Revise sudo sin contraseña en el servidor, red, o aumente SSH_COMMAND_TIMEOUT_MS.',
            code: -2,
          });
        }, timeoutMs);
      }

      conn
        .on('ready', () => {
          conn.exec(command, (err, stream) => {
            if (err) {
              return finish({ success: false, output: '', error: err.message, code: -1 });
            }
            stream
              .on('close', (code) => {
                finish({
                  success: code === 0,
                  output: stdout.trim(),
                  error: code !== 0 ? (stderr.trim() || stdout.trim() || 'Exit code ' + code) : '',
                  code: code ?? -1,
                });
              })
              .on('data', (data) => {
                stdout += data.toString();
              })
              .stderr.on('data', (data) => {
                stderr += data.toString();
              });
          });
        })
        .on('error', (err) => {
          finish({ success: false, output: '', error: err.message, code: -1 });
        })
        .connect(config);
    });
  }

  return (async () => {
    let last = { success: false, output: '', error: 'Unknown SSH error', code: -1 };
    for (let attempt = 0; attempt <= maxRetries; attempt++) {
      last = await runOnce();
      if (last.success) return last;
      if (last.code === -2) return last;
      if (!isTransientSshError(last.error) || attempt === maxRetries) return last;
      await sleep(retryDelayMs * (attempt + 1));
    }
    return last;
  })();
}

/**
 * Descarga un archivo del servidor remoto por SFTP y devuelve un Buffer.
 * @param {string} remotePath - Ruta completa en el servidor
 * @returns {Promise<Buffer>}
 */
function downloadFile(remotePath) {
  const config = loadConfig();
  return new Promise((resolve, reject) => {
    const conn = new Client();
    conn
      .on('ready', () => {
        conn.sftp((err, sftp) => {
          if (err) {
            conn.end();
            return reject(err);
          }
          const chunks = [];
          const stream = sftp.createReadStream(remotePath);
          stream.on('data', (chunk) => chunks.push(chunk));
          stream.on('end', () => {
            conn.end();
            resolve(Buffer.concat(chunks));
          });
          stream.on('error', (e) => {
            conn.end();
            reject(e);
          });
        });
      })
      .on('error', reject)
      .connect(config);
  });
}

/**
 * Devuelve un stream de lectura del archivo remoto (para hacer pipe a la respuesta HTTP y no cargar todo en memoria).
 * Al terminar el stream hay que cerrar conn (conn.end()).
 * @param {string} remotePath - Ruta completa en el servidor
 * @returns {Promise<{ stream: Readable, conn: Client }>}
 */
function downloadFileStream(remotePath) {
  const config = loadConfig();
  return new Promise((resolve, reject) => {
    const conn = new Client();
    conn
      .on('ready', () => {
        conn.sftp((err, sftp) => {
          if (err) {
            conn.end();
            return reject(err);
          }
          const stream = sftp.createReadStream(remotePath);
          stream.on('error', (e) => {
            conn.end();
          });
          resolve({ stream, conn });
        });
      })
      .on('error', reject)
      .connect(config);
  });
}

/**
 * Ejecuta un comando y devuelve stream de salida (para monitorear en vivo).
 * Usa PTY para que Python y otros procesos detecten un terminal y no buffereen
 * su salida, permitiendo ver cada [INFO] en tiempo real.
 * @param {string} command
 * @returns {Promise<{ stream: Readable, conn: Client }>}
 */
function runCommandStream(command) {
  const config = loadConfig();
  return new Promise((resolve, reject) => {
    const conn = new Client();
    conn
      .on('ready', () => {
        // PTY: terminal virtual que elimina el buffering de Python/bash.
        // Sin timeout en el comando: el SSH keepalive gestiona la conexión.
        conn.exec(command, { pty: { term: 'xterm', cols: 220, rows: 50 } }, (err, stream) => {
          if (err) {
            conn.end();
            return reject(err);
          }
          resolve({ stream, conn });
        });
      })
      .on('error', reject)
      .connect(config);
  });
}

module.exports = { loadConfig, runCommand, downloadFile, downloadFileStream, runCommandStream };
