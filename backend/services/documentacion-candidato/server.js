/**
 * Microservicio: listado de documentación de candidato.
 * Misma lógica que CapHum::getDocumentosCandidatoList() pero sin arranque PHP.
 * Usar las mismas variables de entorno que el backend PHP (DB_HOST, DB_NAME, etc.).
 */
const path = require('path');
try { require('dotenv').config({ path: path.join(__dirname, '.env') }); } catch (_) {}

const http = require('http');
const mysql = require('mysql2/promise');

const PORT = process.env.DOC_PORT || 3001;
const DB_CONFIG = {
  host: process.env.DB_HOST || process.env.DB_SERVIDOR || 'localhost',
  port: parseInt(process.env.DB_PUERTO || '3306', 10),
  user: process.env.DB_USER || process.env.DB_USUARIO || '__SPARTA_SECRET_REDACTED__',
  password: process.env.DB_PASSWORD || process.env.DB_PASS || '',
  database: process.env.DB_NAME || process.env.DB_ESQUEMA || '__SPARTA_SECRET_REDACTED__',
  charset: 'utf8mb4',
};

const TIPOS_REQUERIDOS = {
  'SOLICITUD INTERNA': 1,
  'CV O SOLICITUD DE TRABAJO': 2,
  'ACTA DE NACIMIENTO': 3,
  'ACTA DE NACIMIENTO Certificada': 3,
  'CURP': 4,
  'IDENTIFICACIÓN OFICIAL': 5,
  'IDENTIFICACIÓN OFICIAL (REVERSO)': '5_reverso',
  'COMPROBANTE DE DOMICILIO': 6,
  'CONSTANCIA DE SITUACION FISCAL': 7,
  'NÚMERO DE SEGURIDAD SOCIAL': 8,
  'HOJA DE RETENCION FONACOT O INFONAVIT': 9,
  'ESTADO DE CUENTA': 10,
};

function normalize(s) {
  if (s == null) return '';
  s = String(s).trim().toUpperCase();
  s = s.replace(/Í/g, 'I').replace(/Ó/g, 'O').replace(/Ú/g, 'U').replace(/Á/g, 'A').replace(/É/g, 'E').replace(/Ñ/g, 'N');
  return s.replace(/\s+/g, ' ');
}

function buildMetricas(documentos) {
  const clavesUnicas = {};
  for (const d of documentos) {
    const tipo = normalize(d.tipo_documento || '');
    if (tipo === 'IDENTIFICACION OFICIAL (REVERSO)') {
      clavesUnicas['5_reverso'] = true;
    } else {
      for (const [nombre, num] of Object.entries(TIPOS_REQUERIDOS)) {
        if (num === '5_reverso') continue;
        const nombreNorm = normalize(nombre);
        if (tipo === nombreNorm || tipo.includes(nombreNorm) || nombreNorm.includes(tipo)) {
          clavesUnicas[typeof num === 'string' ? num : num] = true;
          break;
        }
      }
    }
  }
  const totalRequeridos = 11;
  const totalActual = Object.keys(clavesUnicas).length;
  const expedienteCompleto =
    totalActual >= totalRequeridos &&
    clavesUnicas['5_reverso'] &&
    [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].every((n) => clavesUnicas[n]);
  return {
    total_documentos: totalActual,
    documentos_requeridos: totalRequeridos,
    porcentaje: totalRequeridos > 0 ? Math.min(100, Math.round((totalActual / totalRequeridos) * 100)) : 0,
    expediente_completo: !!expedienteCompleto,
  };
}

async function getDocumentosCandidato(conn, id) {
  const [rows] = await conn.execute(
    'SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga, validado, fecha_validado FROM candidato_documento WHERE id_candidato = ? ORDER BY fecha_carga DESC',
    [id]
  );
  return Array.isArray(rows) ? rows : [];
}

async function getVerificacionExpediente(conn, id) {
  const [rows] = await conn.execute('SELECT ultima_verificacion_expediente FROM candidatos WHERE id = ?', [id]);
  const row = rows && rows[0];
  if (!row || !row.ultima_verificacion_expediente) return null;
  try {
    const decoded = JSON.parse(row.ultima_verificacion_expediente);
    return typeof decoded === 'object' && decoded !== null ? decoded : null;
  } catch {
    return null;
  }
}

const server = http.createServer(async (req, res) => {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Access-Control-Allow-Origin', '*');
  if (req.method === 'OPTIONS') {
    res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    res.statusCode = 204;
    res.end();
    return;
  }

  if (req.method !== 'GET' || !req.url.startsWith('/documentacion-candidato')) {
    res.statusCode = 404;
    res.end(JSON.stringify({ success: false, mensaje: 'Not Found' }));
    return;
  }

  const u = new URL(req.url, 'http://localhost');
  const id = parseInt(u.searchParams.get('id_candidato') || '0', 10);
  if (id <= 0) {
    res.statusCode = 400;
    res.end(JSON.stringify({ success: false, mensaje: 'id_candidato inválido.' }));
    return;
  }

  let conn;
  try {
    conn = await mysql.createConnection(DB_CONFIG);
    const [documentos, verificacion] = await Promise.all([
      getDocumentosCandidato(conn, id),
      getVerificacionExpediente(conn, id),
    ]);
    const payload = { documentos };
    if (verificacion != null) payload.verificacion_expediente = verificacion;
    payload.metricas = buildMetricas(documentos);
    res.statusCode = 200;
    res.end(JSON.stringify({ success: true, mensaje: 'OK', datos: payload }));
  } catch (err) {
    res.statusCode = 500;
    res.end(JSON.stringify({ success: false, mensaje: 'Error en servidor.', error: err.message }));
  } finally {
    if (conn) try { conn.end(); } catch (_) {}
  }
});

server.listen(PORT, () => {
  console.log('Documentación candidato API escuchando en puerto', PORT);
});
