<?php

namespace Controllers;

use Core\Controller;
use Models\SegundometroDAO;

class Segundometro extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Integración gradual con segundometro-agent.
     * Si está habilitado, el Shell usa el agente como backend principal y,
     * ante error, cae al flujo legado (DAO) para no romper operación.
     */
    private function usarAgente()
    {
        $enabled = $this->agenteConfigValue('enabled', (CONFIGURACION['segundometro_agent_enabled'] ?? '1'));
        return in_array((string)$enabled, ['1', 'true', 'TRUE', 'yes', 'on'], true);
    }

    private function agenteBaseUrl()
    {
        $url = trim((string)$this->agenteConfigValue('url', (CONFIGURACION['segundometro_agent_url'] ?? 'http://127.0.0.1:3100')));
        return rtrim($url, '/');
    }

    private function agenteApiKey()
    {
        return trim((string)$this->agenteConfigValue('key', (CONFIGURACION['segundometro_agent_key'] ?? '')));
    }

    private function agenteConfigValue($key, $default = '')
    {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = [];
            $configFile = __DIR__ . '/../config/config.ini';
            if (is_file($configFile)) {
                $parsed = @parse_ini_file($configFile, true);
                if (is_array($parsed) && isset($parsed['segundometro_agent']) && is_array($parsed['segundometro_agent'])) {
                    $cfg = $parsed['segundometro_agent'];
                }
            }
        }
        if (array_key_exists($key, $cfg)) return $cfg[$key];
        return $default;
    }

    private function agenteRequest($method, $path, $payload = null)
    {
        $url = $this->agenteBaseUrl() . $path;
        $headers = ['Accept: application/json'];
        $key = $this->agenteApiKey();
        if ($key !== '') $headers[] = 'X-Api-Key: ' . $key;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if ($payload !== null) {
                if (is_array($payload)) {
                    $json = json_encode($payload);
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                }
            }
            $raw = curl_exec($ch);
            $err = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($raw === false) return ['success' => false, 'status' => 0, 'error' => $err ?: 'Error CURL', 'json' => null, 'raw' => ''];
            $json = json_decode($raw, true);
            return ['success' => ($status >= 200 && $status < 300), 'status' => $status, 'error' => $err, 'json' => $json, 'raw' => $raw];
        }

        $opts = ['http' => ['method' => strtoupper($method), 'timeout' => 120, 'ignore_errors' => true, 'header' => implode("\r\n", $headers)]];
        if ($payload !== null) {
            $opts['http']['header'] .= "\r\nContent-Type: application/json";
            $opts['http']['content'] = is_array($payload) ? json_encode($payload) : (string)$payload;
        }
        $ctx = stream_context_create($opts);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) return ['success' => false, 'status' => 0, 'error' => 'No se pudo conectar al agente', 'json' => null, 'raw' => ''];
        $status = 200;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) $status = (int)$m[1];
        $json = json_decode($raw, true);
        return ['success' => ($status >= 200 && $status < 300), 'status' => $status, 'error' => '', 'json' => $json, 'raw' => $raw];
    }

    private function validarModoSoloAgente($operacion = 'operación')
    {
        if ($this->usarAgente()) return true;
        self::respuestaJSON([
            'success' => false,
            'mensaje' => 'El flujo legado está deshabilitado. Active [segundometro_agent].enabled=1 para usar ' . $operacion . ' vía API agente.'
        ]);
        return false;
    }

    /**
     * Vista principal del Shell de Segundómetro
     */
    public function shell()
    {
        $script = <<<'HTML'
        <script>
            // Estado de reportes por BD (solo consulta MySQL, sin SSH)
            let estadoReportesCache = {};
            let archivosActuales = [];
            var segundometroEstadoInterval = null;
            var segundometroEstadoFastInterval = null;
            var relojCdmxSyncInterval = null;
            var relojCdmxTickInterval = null;
            var relojCdmxOffsetMs = null;
            var guardandoAutoCopy = false;

            // 🎨 RENDERIZAR ARCHIVOS EN LA INTERFAZ (SOLO ARCHIVOS REALES DEL SERVIDOR)
            const renderArchivos = (archivos, estados) => {
                estados = estados || {};
                const container = document.getElementById('archivos-container');

                if (!archivos || archivos.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No se encontraron archivos de reportes en el servidor</p>
                            <small class="text-muted">Los archivos aparecerán aquí cuando existan en el servidor</small>
                        </div>
                    `;
                    return;
                }

                // Agrupar por fecha
                const archivosPorFecha = {};
                archivos.forEach(archivo => {
                    if (!archivosPorFecha[archivo.fecha]) {
                        archivosPorFecha[archivo.fecha] = {
                            archivos: [],
                            display: archivo.fecha_display || archivo.fecha
                        };
                    }
                    archivosPorFecha[archivo.fecha].archivos.push(archivo);
                });

                let html = '';

                // Renderizar por fecha (más reciente primero)
                Object.keys(archivosPorFecha).sort().reverse().forEach(fecha => {
                    const data = archivosPorFecha[fecha];
                    const archivosDelDia = data.archivos;

                    html += `
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fa fa-calendar-day me-2"></i>
                                    ${data.display}
                                    <span class="badge bg-light text-dark ms-2">${archivosDelDia.length} archivos</span>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50"><i class="fa fa-hashtag"></i></th>
                                                <th width="44" class="text-center" title="Estado en BD"><i class="fa fa-info-circle me-1"></i></th>
                                                <th><i class="fa fa-file-archive me-1"></i>Nombre del Archivo</th>
                                                <th width="120"><i class="fa fa-clock me-1"></i>Hora</th>
                                                <th width="120"><i class="fa fa-hdd me-1"></i>Tamaño</th>
                                                <th width="260" class="text-center"><i class="fa fa-cog me-1"></i>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${archivosDelDia.map((archivo, index) => {
                                                const owner = (archivo.owner || '').toString();
                                                const esProveedor = owner === 's2';
                                                const esNosotros = owner === 'root';
                                                let etiqueta = '';
                                                if (esProveedor) etiqueta = '<span class="text-danger ms-1">(proveedor)</span>';
                                                else if (esNosotros) etiqueta = '<span class="text-success ms-1">(nosotros)</span>';
                                                const nombreEscapado = (archivo.nombre || '').replace(/'/g, "\\\\'");
                                                const estado = estados[archivo.nombre] || 'procesando';
                                                const iconoEstado = estado === 'ok' ? '<i class="fa fa-check-circle text-success" title="OK en BD"></i>' : estado === 'error' ? '<i class="fa fa-times-circle text-danger" title="Error / sin datos en BD"></i>' : '<i class="fa fa-spinner fa-spin text-primary" title="Procesando"></i>';
                                                return `
                                                <tr>
                                                    <td class="text-muted">${index + 1}</td>
                                                    <td class="text-center">${iconoEstado}</td>
                                                    <td class="font-monospace text-primary fw-semibold">${archivo.nombre} ${etiqueta}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info">${archivo.hora}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="text-muted">${archivo.tamano}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
                                                            <a href="/segundometro/descargarArchivo?nombre_archivo=${encodeURIComponent(archivo.nombre)}" class="btn btn-sm btn-primary btn-descargar-reporte" title="Descargar reporte" data-descargar-reporte="1" data-nombre="${String(archivo.nombre || '').replace(/"/g, '&quot;')}">
                                                                <i class="fa fa-download me-1"></i>Descargar
                                                            </a>
                                                            <button
                                                                class="btn btn-sm btn-success"
                                                                onclick="copiarArchivo('${nombreEscapado}')"
                                                                title="Copiar archivo con +1 segundo">
                                                                <i class="fa fa-copy me-1"></i>Copiar +1s
                                                            </button>
                                                            ${esNosotros ? `
                                                            <button
                                                                class="btn btn-sm btn-danger"
                                                                onclick="eliminarArchivo('${nombreEscapado}')"
                                                                title="Eliminar archivo (solo nosotros)">
                                                                <i class="fa fa-trash me-1"></i>Eliminar
                                                            </button>
                                                            ` : ''}
                                                        </div>
                                                    </td>
                                                </tr>
                                            `;
                                            }).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            };

            // 📋 COPIAR ARCHIVO: diseño como antes; destino por defecto +1 s; lápiz para editar (ej. +2 s, +20 s, +1 min)
            const escHtml = function(s) { return (s + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); };
            const copiarArchivo = async (nombreArchivo) => {
                const match = nombreArchivo.match(/mega_rpt_(\d{8})_(\d{2})_(\d{2})_(\d{2})\.csv\.zip/);
                if (!match) {
                    Swal.fire('Error', 'Formato de archivo inválido', 'error');
                    return;
                }
                const [, fecha, hora, minuto, segundo] = match;
                let nuevoSegundo = parseInt(segundo) + 1;
                let nuevoMinuto = parseInt(minuto);
                let nuevaHora = parseInt(hora);
                let nuevaFecha = fecha;
                if (nuevoSegundo >= 60) {
                    nuevoSegundo = 0;
                    nuevoMinuto++;
                    if (nuevoMinuto >= 60) {
                        nuevoMinuto = 0;
                        nuevaHora++;
                        if (nuevaHora >= 24) {
                            nuevaHora = 0;
                            const dateObj = new Date(parseInt(fecha.substring(0, 4)), parseInt(fecha.substring(4, 6)) - 1, parseInt(fecha.substring(6, 8)));
                            dateObj.setDate(dateObj.getDate() + 1);
                            nuevaFecha = dateObj.toISOString().split('T')[0].replace(/-/g, '');
                        }
                    }
                }
                const nombreDestinoDefault = 'mega_rpt_' + nuevaFecha + '_' + String(nuevaHora).padStart(2, '0') + '_' + String(nuevoMinuto).padStart(2, '0') + '_' + String(nuevoSegundo).padStart(2, '0') + '.csv.zip';
                const result = await Swal.fire({
                    title: 'Copiar archivo',
                    html: '<div class="text-start">' +
                        '<div class="alert alert-light border rounded mb-3">' +
                        '<div class="mb-3"><label class="text-muted small text-uppercase mb-1">Origen</label><br><code class="text-primary bg-light px-2 py-1 rounded">' + escHtml(nombreArchivo) + '</code></div>' +
                        '<div id="swal-destino-block">' +
                        '<label class="text-muted small text-uppercase mb-1">Destino</label><br>' +
                        '<span id="swal-destino-wrap" class="d-inline-flex align-items-center gap-1">' +
                        '<span class="badge bg-success px-2 py-1 fs-6">+1s</span>' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary p-1" id="swal-destino-editar" title="Editar destino (poner el valor que desees)"><i class="fa fa-pencil-alt"></i></button>' +
                        '</span>' +
                        '<div id="swal-destino-input-wrap" class="mt-2" style="display:none">' +
                        '<input type="text" id="swal-destino-input" class="form-control form-control-sm font-monospace" value="' + escHtml(nombreDestinoDefault) + '" placeholder="mega_rpt_YYYYMMDD_HH_MM_SS.csv.zip">' +
                        '<small class="text-muted">Edita el nombre del archivo destino si quieres otro valor (ej. +2s, +1 min)</small>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="alert alert-secondary border rounded mb-0 py-2"><small class="text-muted"><strong>Comando:</strong></small> <code class="small">sudo cp ' + escHtml(nombreArchivo) + ' <span id="swal-cmd-dest">' + escHtml(nombreDestinoDefault) + '</span></code></div>' +
                        '</div>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, copiar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    didOpen: function() {
                        var btn = document.getElementById('swal-destino-editar');
                        var wrap = document.getElementById('swal-destino-wrap');
                        var inputWrap = document.getElementById('swal-destino-input-wrap');
                        var input = document.getElementById('swal-destino-input');
                        var cmdDest = document.getElementById('swal-cmd-dest');
                        if (btn && input) {
                            btn.addEventListener('click', function() {
                                wrap.style.display = 'none';
                                inputWrap.style.display = 'block';
                                input.focus();
                            });
                            input.addEventListener('input', function() { if (cmdDest) cmdDest.textContent = input.value; });
                        }
                    },
                    preConfirm: function() {
                        var input = document.getElementById('swal-destino-input');
                        var inputWrap = document.getElementById('swal-destino-input-wrap');
                        var dest = (inputWrap && inputWrap.style.display !== 'none' && input && input.value) ? input.value.trim() : nombreDestinoDefault;
                        if (!dest) dest = nombreDestinoDefault;
                        if (!/^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/.test(dest)) {
                            Swal.showValidationMessage('Destino debe tener formato mega_rpt_YYYYMMDD_HH_MM_SS.csv.zip');
                            return false;
                        }
                        if (dest === nombreArchivo) {
                            Swal.showValidationMessage('Destino no puede ser igual al origen');
                            return false;
                        }
                        return dest;
                    }
                });
                if (!result.isConfirmed) return;
                var nombreDestino = result.value === false ? nombreDestinoDefault : result.value;
                Swal.fire({ title: 'Procesando...', html: 'Ejecutando comando de copia en el servidor remoto', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                try {
                    var formData = new FormData();
                    formData.append('nombre_archivo', nombreArchivo);
                    formData.append('nombre_destino', nombreDestino);
                    var response = await fetch('/segundometro/copiarArchivo', { method: 'POST', body: formData, headers: { 'Front-Request': 'true' } });
                    var data = await response.json();
                    if (!data.success) throw new Error(data.mensaje || 'Error desconocido');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Archivo copiado exitosamente!',
                        html: '<div class="text-start"><div class="alert alert-success"><div class="mb-2"><strong>✅ Origen:</strong><br><code>' + escHtml(data.datos.origen) + '</code></div><div><strong>✅ Destino:</strong><br><code>' + escHtml(data.datos.destino) + '</code></div></div><p class="text-muted mb-0 small"><i class="fa fa-info-circle me-1"></i>El archivo se ha copiado correctamente en el servidor remoto</p></div>',
                        confirmButtonText: 'Aceptar'
                    }).then(function() { listarArchivos(); });
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Error al copiar archivo', text: error.message || 'No se pudo copiar en el servidor', confirmButtonText: 'Aceptar' });
                }
            };

            // 🗑️ ELIMINAR ARCHIVO (SOLO NOSOTROS / ROOT)
            const eliminarArchivo = async (nombreArchivo) => {
                const result = await Swal.fire({
                    title: '¿Eliminar archivo?',
                    html: '<p class="mb-2">Se eliminará permanentemente del servidor:</p><code class="d-block text-start">' + nombreArchivo + '</code><p class="text-danger mt-2 mb-0 small">Esta acción no se puede deshacer.</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                });
                if (!result.isConfirmed) return;
                Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                try {
                    const formData = new FormData();
                    formData.append('nombre_archivo', nombreArchivo);
                    const response = await fetch('/segundometro/eliminarArchivo', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Front-Request': 'true' }
                    });
                    const data = await response.json();
                    if (!data.success) throw new Error(data.mensaje || 'Error al eliminar');
                    Swal.fire({ icon: 'success', title: 'Archivo eliminado', text: 'El archivo se eliminó correctamente del servidor.', confirmButtonText: 'Aceptar' }).then(() => listarArchivos());
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Error al eliminar', text: error.message || 'No se pudo eliminar el archivo.', confirmButtonText: 'Aceptar' });
                }
            };

            // 🔄 LISTAR ARCHIVOS DESDE EL SERVIDOR (LLAMADA REAL)
            const listarArchivos = async (silent = false) => {
                const container = document.getElementById('archivos-container');
                try {
                    const response = await fetch('/segundometro/listarArchivos', {
                        method: 'GET',
                        headers: {
                            'Front-Request': 'true'
                        }
                    });

                    const contentType = response.headers.get('Content-Type') || '';
                    if (!contentType.includes('application/json')) {
                        throw new Error('El servidor respondió con un formato inesperado. Compruebe que la sesión esté activa.');
                    }

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.mensaje || 'Error al obtener archivos');
                    }

                    archivosActuales = data.datos || [];
                    renderArchivos(archivosActuales, estadoReportesCache);
                    // Consulta inmediata de estado BD tras listar para que "ok" aparezca rápido.
                    actualizarEstadoReportes();

                } catch (error) {
                    if (!silent) {
                        console.error('Error al listar archivos:', error);
                        const msg = error.message || 'Error al conectar con el servidor';
                        container.innerHTML = `
                            <div class="alert alert-danger text-center">
                                <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                                <p class="mb-0">Error al conectar con el servidor</p>
                                <small class="text-muted">${msg}</small>
                            </div>
                        `;
                    }
                }
            };

            // 🚀 ACTUALIZACIÓN SOLO EN VENTANAS (7:31, 9:31, 11:31, 13:31, 14:31, 16:31, 18:31, 20:31, 23:50 CDMX), 2 min cada 30 s. No SSH si la pestaña no está visible.
            var segundometroRefreshInterval = null;
            var segundometroRefreshTimeout = null;
            var VENTANAS_MINUTOS = [7*60+31, 9*60+31, 11*60+31, 13*60+31, 14*60+31, 16*60+31, 18*60+31, 20*60+31, 23*60+50];
            var DURACION_VENTANA_SEG = 120;
            var INTERVALO_SEG = 30;

            function clearSegundometroTimers() {
                if (segundometroRefreshInterval) { clearInterval(segundometroRefreshInterval); segundometroRefreshInterval = null; }
                if (segundometroRefreshTimeout) { clearTimeout(segundometroRefreshTimeout); segundometroRefreshTimeout = null; }
            }

            function minutosActualesCDMX() {
                var r = new Date().toLocaleTimeString('en-GB', { timeZone: 'America/Mexico_City', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                var p = r.split(':');
                return parseInt(p[0],10)*60 + parseInt(p[1],10);
            }

            function segundosActualesCDMX() {
                var r = new Date().toLocaleTimeString('en-GB', { timeZone: 'America/Mexico_City', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                var p = r.split(':');
                return parseInt(p[0],10)*3600 + parseInt(p[1],10)*60 + parseInt(p[2],10);
            }

            function estaEnVentana() {
                var min = minutosActualesCDMX();
                for (var i = 0; i < VENTANAS_MINUTOS.length; i++) {
                    if (min >= VENTANAS_MINUTOS[i] && min < VENTANAS_MINUTOS[i] + 2) return true;
                }
                return false;
            }

            function msHastaProximaVentana() {
                var segTotal = segundosActualesCDMX();
                var min = Math.floor(segTotal / 60);
                for (var i = 0; i < VENTANAS_MINUTOS.length; i++) {
                    var inicioSeg = VENTANAS_MINUTOS[i] * 60;
                    if (segTotal < inicioSeg) return (inicioSeg - segTotal) * 1000;
                }
                var primerInicioManana = VENTANAS_MINUTOS[0] * 60;
                return (24 * 3600 - segTotal + primerInicioManana) * 1000;
            }

            function clearEstadoReportesInterval() {
                if (segundometroEstadoInterval) { clearInterval(segundometroEstadoInterval); segundometroEstadoInterval = null; }
                if (segundometroEstadoFastInterval) { clearInterval(segundometroEstadoFastInterval); segundometroEstadoFastInterval = null; }
            }
            function formatearCdmxDesdeMs(ms) {
                var dt = new Date(ms);
                var p = new Intl.DateTimeFormat('en-CA', {
                    timeZone: 'America/Mexico_City',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                }).formatToParts(dt);
                var get = function(k){ var o = p.find(function(x){ return x.type === k; }); return o ? o.value : '00'; };
                return get('year') + '-' + get('month') + '-' + get('day') + ' ' + get('hour') + ':' + get('minute') + ':' + get('second') + ' CDMX';
            }
            function pintarRelojCdmx() {
                var el = document.getElementById('segundometroHoraServidor');
                if (!el || relojCdmxOffsetMs === null) return;
                el.textContent = formatearCdmxDesdeMs(Date.now() + relojCdmxOffsetMs);
            }
            function sincronizarRelojCdmx() {
                fetch('/segundometro/horaServidor', { method: 'GET', headers: { 'Front-Request': 'true' } })
                    .then(function(r){ return r.json(); })
                    .then(function(d){
                        if (d && d.success && d.timestamp_ms) {
                            relojCdmxOffsetMs = Number(d.timestamp_ms) - Date.now();
                            pintarRelojCdmx();
                            return;
                        }
                        if (d && d.hora_servidor_cdmx) {
                            var m = String(d.hora_servidor_cdmx).match(/(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})/);
                            if (m) {
                                var approx = new Date(Date.UTC(parseInt(m[1],10), parseInt(m[2],10)-1, parseInt(m[3],10), parseInt(m[4],10), parseInt(m[5],10), parseInt(m[6],10)));
                                relojCdmxOffsetMs = approx.getTime() - Date.now();
                                pintarRelojCdmx();
                            }
                        }
                    })
                    .catch(function(){});
            }
            function iniciarRelojCdmxTiempoReal() {
                if (relojCdmxTickInterval) clearInterval(relojCdmxTickInterval);
                if (relojCdmxSyncInterval) clearInterval(relojCdmxSyncInterval);
                sincronizarRelojCdmx();
                relojCdmxTickInterval = setInterval(pintarRelojCdmx, 1000);
                relojCdmxSyncInterval = setInterval(function(){ if (!document.hidden) sincronizarRelojCdmx(); }, 30000);
            }
            function actualizarEstadoAgente() {
                fetch('/segundometro/estadoAgente', { method: 'GET', headers: { 'Front-Request': 'true' } })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        var badgeModo = document.getElementById('sgAgenteModo');
                        var badgeEstado = document.getElementById('sgAgenteEstado');
                        var txt = document.getElementById('sgAgenteDetalle');
                        if (!badgeModo || !badgeEstado || !txt) return;
                        if (!data || !data.success) {
                            badgeModo.className = 'badge bg-secondary';
                            badgeModo.textContent = 'Agente: no disponible';
                            badgeEstado.className = 'badge bg-secondary';
                            badgeEstado.textContent = 'Estado: desconocido';
                            txt.textContent = (data && data.mensaje) ? data.mensaje : 'No se pudo consultar el estado de integración.';
                            return;
                        }
                        badgeModo.className = data.usando_agente ? 'badge bg-success' : 'badge bg-warning text-dark';
                        badgeModo.textContent = data.usando_agente ? 'Agente: activo' : 'Agente: inactivo';
                        badgeEstado.className = data.agente_online ? 'badge bg-success' : 'badge bg-danger';
                        badgeEstado.textContent = data.agente_online ? 'Estado: en línea' : 'Estado: fuera de línea';
                        txt.textContent = data.detalle || 'Sin detalle';
                    })
                    .catch(function(){
                        var badgeModo = document.getElementById('sgAgenteModo');
                        var badgeEstado = document.getElementById('sgAgenteEstado');
                        var txt = document.getElementById('sgAgenteDetalle');
                        if (badgeModo) { badgeModo.className = 'badge bg-secondary'; badgeModo.textContent = 'Agente: no disponible'; }
                        if (badgeEstado) { badgeEstado.className = 'badge bg-secondary'; badgeEstado.textContent = 'Estado: desconocido'; }
                        if (txt) txt.textContent = 'Error de red al consultar estado del agente.';
                    });
            }
            function cargarEstadoAutoCopy() {
                fetch('/segundometro/autoCopyEstado', { method: 'GET', headers: { 'Front-Request': 'true' } })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        var chk = document.getElementById('sgAutoCopyEnabled');
                        if (!chk) return;
                        if (!data || !data.success) return;
                        chk.checked = !!data.enabled;
                    })
                    .catch(function(){});
            }
            async function guardarEstadoAutoCopy() {
                var chk = document.getElementById('sgAutoCopyEnabled');
                if (!chk || guardandoAutoCopy) return;
                guardandoAutoCopy = true;
                var estadoPrevio = !chk.checked; // estado antes del cambio (opuesto al actual)
                try {
                    const response = await fetch('/segundometro/autoCopyConfig', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                        body: JSON.stringify({ enabled: !!chk.checked })
                    });
                    const data = await response.json();
                    if (!data.success) throw new Error(data.mensaje || 'No se pudo guardar auto-copy');
                    actualizarEstadoAgente();
                } catch (e) {
                    chk.checked = estadoPrevio; // revertir al estado anterior si falló
                    Swal.fire('Error', e.message || 'No se pudo guardar auto-copy', 'error');
                } finally {
                    guardandoAutoCopy = false;
                }
            }
            function actualizarEstadoReportes() {
                if (document.hidden) return;
                if (!archivosActuales || archivosActuales.length === 0) return;
                var nombresPendientes = archivosActuales.map(function(a){ return a.nombre; }).filter(function(n){ return estadoReportesCache[n] !== 'ok'; });
                if (nombresPendientes.length === 0) return;
                fetch('/segundometro/estadoReportes', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' }, body: JSON.stringify({ nombres: nombresPendientes }) })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (data.success && data.estados) {
                            for (var k in data.estados) estadoReportesCache[k] = data.estados[k];
                            renderArchivos(archivosActuales, estadoReportesCache);
                        }
                        if (data.timestamp_ms) {
                            relojCdmxOffsetMs = Number(data.timestamp_ms) - Date.now();
                            pintarRelojCdmx();
                        }
                    })
                    .catch(function(){});
            }
            function programarRefrescos() {
                if (document.hidden) return;
                clearSegundometroTimers();
                if (estaEnVentana()) {
                    var cuenta = 0;
                    var maxRefrescos = Math.floor(DURACION_VENTANA_SEG / INTERVALO_SEG);
                    segundometroRefreshInterval = setInterval(function() {
                        if (document.hidden) { clearSegundometroTimers(); return; }
                        listarArchivos(true);
                        cuenta++;
                        if (cuenta >= maxRefrescos) {
                            clearSegundometroTimers();
                            segundometroRefreshTimeout = setTimeout(programarRefrescos, msHastaProximaVentana());
                        }
                    }, INTERVALO_SEG * 1000);
                    listarArchivos(true);
                } else {
                    segundometroRefreshTimeout = setTimeout(programarRefrescos, msHastaProximaVentana());
                }
            }

            function esTruncarModoPrueba() {
                var params = new URLSearchParams(window.location.search);
                var v = params.get('truncar_test');
                return v === '1' || v === 'true' || v === 'yes';
            }

            function actualizarEstadoBotonTruncar() {
                var btn = document.getElementById('btnTruncarSegundometro');
                if (!btn) return;
                var habilitado = false;
                if (esTruncarModoPrueba()) {
                    habilitado = true;
                    btn.title = 'Modo prueba: Truncar habilitado (URL con ?truncar_test=1). Quita el parámetro para volver al horario normal.';
                } else {
                    var dia = new Date().toLocaleDateString('en-GB', { timeZone: 'America/Mexico_City', weekday: 'short' });
                    var r = new Date().toLocaleTimeString('en-GB', { timeZone: 'America/Mexico_City', hour: '2-digit', minute: '2-digit', hour12: false });
                    var p = r.split(':');
                    var minDia = parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
                    habilitado = (dia === 'Tue' && minDia >= 420 && minDia < 570); // Martes 7:00 (420) a 9:30 (570)
                    btn.title = habilitado ? 'Truncar tabla Semana (copia a Historial y limpia)' : 'Disponible solo los martes de 7:00 a 9:30 AM (CDMX)';
                }
                btn.disabled = !habilitado;
            }

            // Monitorear: panel en la misma página (streaming). Minimizar deja usar otros botones sin cortar el stream.
            function abrirPanelMonitorear() {
                var panel = document.getElementById('panelMonitorear');
                var iframe = document.getElementById('panelMonitorearIframe');
                if (!panel || !iframe) return;
                iframe.src = '/segundometro/ventanaMonitorear';
                panel.style.display = 'flex';
                panel.dataset.minimizado = '0';
                restaurarPanelMonitorear();
            }
            function minimizarPanelMonitorear() {
                var panel = document.getElementById('panelMonitorear');
                var body = document.getElementById('panelMonitorearBody');
                var btnMin = document.getElementById('panelMonitorearMinimizar');
                if (!panel || !body) return;
                panel.dataset.minimizado = '1';
                body.style.display = 'none';
                panel.style.height = 'auto';
                panel.style.maxHeight = 'none';
                if (btnMin) { btnMin.title = 'Restaurar panel para ver el stream'; btnMin.innerHTML = '<i class="fa fa-window-restore"></i>'; }
            }
            function restaurarPanelMonitorear() {
                var panel = document.getElementById('panelMonitorear');
                var body = document.getElementById('panelMonitorearBody');
                var btnMin = document.getElementById('panelMonitorearMinimizar');
                if (!panel || !body) return;
                panel.dataset.minimizado = '0';
                body.style.display = 'flex';
                panel.style.height = '420px';
                panel.style.maxHeight = '75vh';
                if (btnMin) { btnMin.title = 'Minimizar (el stream sigue; podrás usar Truncar y demás botones)'; btnMin.innerHTML = '<i class="fa fa-window-minimize"></i>'; }
            }
            function toggleMinimizarPanelMonitorear() {
                var panel = document.getElementById('panelMonitorear');
                if (!panel) return;
                if (panel.dataset.minimizado === '1') restaurarPanelMonitorear(); else minimizarPanelMonitorear();
            }
            function cerrarPanelMonitorear() {
                var panel = document.getElementById('panelMonitorear');
                var iframe = document.getElementById('panelMonitorearIframe');
                if (iframe && iframe.contentWindow) {
                    try {
                        iframe.contentWindow.postMessage({ action: 'cerrarEventSource' }, '*');
                    } catch (e) {}
                }
                if (iframe && iframe.parentNode) {
                    var contenedorIframe = iframe.parentNode;
                    iframe.src = 'about:blank';
                    contenedorIframe.removeChild(iframe);
                    var nuevoIframe = document.createElement('iframe');
                    nuevoIframe.id = 'panelMonitorearIframe';
                    nuevoIframe.style.cssText = 'flex:1; width:100%; height:100%; min-height:0; border:none; background:#1e1e1e;';
                    contenedorIframe.appendChild(nuevoIframe);
                }
                if (panel) { panel.style.display = 'none'; panel.dataset.minimizado = '0'; }
            }
            function initPanelMonitorearDrag() {
                var panel = document.getElementById('panelMonitorear');
                var header = document.getElementById('panelMonitorearHeader');
                if (!panel || !header) return;
                var dragging = false, offX = 0, offY = 0;
                header.addEventListener('mousedown', function(e) {
                    if (e.target.id === 'panelMonitorearCerrar' || e.target.closest('#panelMonitorearCerrar') || e.target.id === 'panelMonitorearMinimizar' || e.target.closest('#panelMonitorearMinimizar')) return;
                    dragging = true;
                    offX = e.clientX - panel.getBoundingClientRect().left;
                    offY = e.clientY - panel.getBoundingClientRect().top;
                });
                document.addEventListener('mousemove', function(e) {
                    if (!dragging) return;
                    var x = e.clientX - offX, y = e.clientY - offY;
                    if (x < 0) x = 0; if (y < 0) y = 0;
                    if (x + panel.offsetWidth > window.innerWidth) x = window.innerWidth - panel.offsetWidth;
                    if (y + panel.offsetHeight > window.innerHeight) y = window.innerHeight - panel.offsetHeight;
                    panel.style.left = x + 'px';
                    panel.style.top = y + 'px';
                    panel.style.right = 'auto';
                });
                document.addEventListener('mouseup', function() { dragging = false; });
            }

            async function truncarSegundometro() {
                var result = await Swal.fire({
                    title: '¿Truncar tabla Semana?',
                    html: '<div class="text-start">'
                        + '<p class="mb-2">Este proceso realizará las siguientes acciones:</p>'
                        + '<ol class="mb-2">'
                        + '<li><strong>Copiar</strong> todos los registros de <code>tbl_segundometro_semana</code> a <code>tbl_segundometro_histo</code></li>'
                        + '<li><strong>Notificar</strong> el resultado a Google Chat</li>'
                        + '<li><strong>Truncar</strong> (vaciar) la tabla <code>tbl_segundometro_semana</code></li>'
                        + '</ol>'
                        + '<p class="text-danger mb-0 small"><i class="fa fa-exclamation-triangle me-1"></i>Esta acción no se puede deshacer.</p>'
                        + '</div>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, truncar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#00c9d4',
                    cancelButtonColor: '#6c757d'
                });
                if (!result.isConfirmed) return;
                Swal.fire({ title: 'Procesando...', html: 'Copiando registros y truncando tabla.<br>Esto puede tardar unos segundos.', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                try {
                    var body = {};
                    if (esTruncarModoPrueba()) body.truncar_test = 1;
                    var response = await fetch('/segundometro/truncarSegundometro', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                        body: Object.keys(body).length ? JSON.stringify(body) : undefined
                    });
                    var data = await response.json();
                    if (!data.success) throw new Error(data.mensaje || 'Error desconocido');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Proceso completado!',
                        html: '<div class="text-start">'
                            + '<div class="alert alert-success">'
                            + '<p class="mb-1"><strong>✅ Registros copiados:</strong> ' + (data.registros_copiados || 0) + '</p>'
                            + '<p class="mb-0"><strong>🧹 Tabla truncada:</strong> exitosamente</p>'
                            + '</div>'
                            + '<p class="text-muted small mb-0"><i class="fa fa-bell me-1"></i>Se enviaron notificaciones a Google Chat.</p>'
                            + '</div>',
                        confirmButtonText: 'Aceptar'
                    });
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Error al truncar', text: error.message || 'No se pudo completar el proceso.', confirmButtonText: 'Aceptar' });
                }
            }

            async function ejecutarDiagnosticoSSH() {
                Swal.fire({
                    title: 'Ejecutando diagnóstico SSH...',
                    html: '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x text-primary mb-3"></i><p class="text-muted">Probando configuración, llaves, conectividad, permisos y funciones.</p><p class="text-muted small">Esto puede tardar unos segundos...</p></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                try {
                    const response = await fetch('/segundometro/diagnosticoSSH', {
                        method: 'GET',
                        headers: { 'Front-Request': 'true' }
                    });
                    const data = await response.json();
                    if (!data.success) throw new Error(data.mensaje || 'Error desconocido');
                    const pruebas = data.pruebas || [];
                    const totalOk = pruebas.filter(p => p.ok).length;
                    const totalFail = pruebas.filter(p => !p.ok).length;
                    const esc = s => (s||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                    const grupos = {local:'Pruebas locales (verifican tu PC)',remoto:'Pruebas remotas (verifican el servidor — 1 sola conexión SSH)',bd:'Pruebas de base de datos (verifican MySQL)'};
                    const grupoIcono = {local:'fa-laptop',remoto:'fa-server',bd:'fa-database'};

                    let html = '<div class="text-start" style="max-height:65vh; overflow-y:auto;">';
                    html += '<div class="alert ' + (totalFail === 0 ? 'alert-success' : 'alert-warning') + ' py-2 mb-3">';
                    html += '<strong>' + (totalFail === 0 ? 'Todas las pruebas pasaron' : totalFail + ' prueba(s) con problemas') + '</strong>';
                    html += ' — ' + totalOk + '/' + pruebas.length + ' OK';
                    html += '</div>';

                    var grupoActual = '';
                    var num = 0;
                    pruebas.forEach(function(p) {
                        num++;
                        var g = p.grupo || 'local';
                        if (g !== grupoActual) {
                            if (grupoActual !== '') html += '</tbody></table>';
                            grupoActual = g;
                            html += '<div class="fw-bold text-primary mt-3 mb-1" style="font-size:0.8rem;"><i class="fa ' + (grupoIcono[g]||'fa-cog') + ' me-1"></i>' + (grupos[g]||g) + '</div>';
                            html += '<table class="table table-sm table-bordered mb-0" style="font-size:0.82rem;">';
                            html += '<thead class="table-light"><tr><th width="26">#</th><th width="26"></th><th>Prueba</th><th>Detalle</th></tr></thead><tbody>';
                        }
                        var icono = p.ok ? '<i class="fa fa-check-circle text-success"></i>' : '<i class="fa fa-times-circle text-danger"></i>';
                        var tooltip = p.ayuda ? ' title="' + esc(p.ayuda) + (p.cubre ? ' | Cubre: ' + esc(p.cubre) : '') + '"' : '';
                        var cubreBadge = p.cubre ? ' <span class="badge bg-light text-dark border" style="font-size:0.7rem;font-weight:400;">' + esc(p.cubre) + '</span>' : '';
                        html += '<tr' + tooltip + ' style="cursor:help;"><td class="text-muted text-center" style="font-size:0.75rem;">' + num + '</td><td class="text-center">' + icono + '</td><td class="fw-semibold">' + esc(p.nombre) + cubreBadge + '</td><td class="text-muted small" style="word-break:break-all;">' + esc(p.detalle) + '</td></tr>';
                    });
                    if (grupoActual !== '') html += '</tbody></table>';

                    html += '<details class="mt-3"><summary class="fw-bold text-secondary" style="font-size:0.8rem;cursor:pointer;"><i class="fa fa-book me-1"></i>Referencia: cobertura por botón</summary>';
                    html += '<div class="mt-2 small text-muted" style="font-size:0.78rem;">';
                    html += '<table class="table table-sm table-bordered mb-0">';
                    html += '<thead class="table-light"><tr><th>Botón</th><th>Pruebas que lo cubren</th></tr></thead><tbody>';
                    html += '<tr><td><i class="fa fa-list me-1"></i>Listar archivos</td><td>9, 11, 12</td></tr>';
                    html += '<tr><td><i class="fa fa-copy me-1"></i>Copiar +1s</td><td>9, 10, 15, 17</td></tr>';
                    html += '<tr><td><i class="fa fa-trash me-1"></i>Eliminar</td><td>9, 10, 16, 17</td></tr>';
                    html += '<tr><td><i class="fa fa-download me-1"></i>Descargar</td><td>9, 18</td></tr>';
                    html += '<tr><td><i class="fa fa-terminal me-1"></i>Monitorear</td><td>9, 10, 13, 14</td></tr>';
                    html += '<tr><td><i class="fa fa-cut me-1"></i>Truncar</td><td>19, 20</td></tr>';
                    html += '</tbody></table>';
                    html += '</div></details>';
                    html += '</div>';

                    Swal.fire({
                        title: 'Diagnóstico SSH',
                        html: html,
                        width: '820px',
                        confirmButtonText: 'Cerrar',
                        icon: totalFail === 0 ? 'success' : 'warning'
                    });
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Error en diagnóstico', text: error.message || 'No se pudo ejecutar el diagnóstico', confirmButtonText: 'Cerrar' });
                }
            }

            async function probarVerificacionBdAgente() {
                try {
                    Swal.fire({
                        title: 'Probando verificación BD...',
                        html: 'Consultando último archivo y validando estado en BD.',
                        allowOutsideClick: false,
                        didOpen: function() { Swal.showLoading(); }
                    });
                    const response = await fetch('/segundometro/probarVerificacionBdAgente', {
                        method: 'GET',
                        headers: { 'Front-Request': 'true' }
                    });
                    const data = await response.json();
                    if (!data.success) throw new Error(data.mensaje || 'No fue posible validar');
                    const estado = data.estado || 'procesando';
                    const color = estado === 'ok' ? 'success' : (estado === 'error' ? 'error' : 'info');
                    const icono = estado === 'ok' ? '✅' : (estado === 'error' ? '❌' : '⏳');
                    Swal.fire({
                        icon: color,
                        title: 'Resultado verificación BD',
                        html: '<div class="text-start">'
                            + '<p class="mb-1"><strong>Archivo:</strong> <code>' + (data.nombre_archivo || 'N/D') + '</code></p>'
                            + '<p class="mb-1"><strong>Estado BD:</strong> ' + icono + ' <strong>' + estado + '</strong></p>'
                            + '<p class="mb-0 text-muted small"><strong>Fuente:</strong> ' + (data.fuente || 'N/D') + '</p>'
                            + '</div>'
                    });
                } catch (e) {
                    Swal.fire('Error', e.message || 'Error en prueba BD', 'error');
                }
            }

            // ── Ejecutar ahora: mismo flujo que el automático ──────────────────────
            async function ejecutarAhora() {
                var confirmResult = await Swal.fire({
                    title: '¿Ejecutar auto-copy ahora?',
                    html: '<div class="text-start">'
                        + '<p class="mb-2">Se ejecutará el mismo flujo que el automático:</p>'
                        + '<ol class="mb-2">'
                        + '<li>Inicia <strong>monitorear.sh</strong> en background (inotifywait)</li>'
                        + '<li>Espera warmup (~10 s) para que se abran los watches</li>'
                        + '<li>Copia el <strong>último reporte +1s</strong></li>'
                        + '<li>inotifywait detecta el archivo → Python procesa → datos en BD</li>'
                        + '</ol>'
                        + '<p class="text-muted small mb-0"><i class="fa fa-info-circle me-1"></i>El proceso corre en background en el agente. El resultado puede tardar hasta 15 min en reflejarse en BD.</p>'
                        + '</div>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, ejecutar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                });
                if (!confirmResult.isConfirmed) return;

                Swal.fire({ title: 'Lanzando...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                try {
                    var r = await fetch('/segundometro/ejecutarAhora', { method: 'POST', headers: { 'Front-Request': 'true' } });
                    var data = await r.json();
                    if (!data.success && data.mensaje && data.mensaje.includes('ya hay una ejecución')) {
                        Swal.fire({ icon: 'info', title: 'Ya hay una ejecución en curso', text: 'Espera a que termine antes de lanzar otra.', confirmButtonText: 'Entendido' });
                        return;
                    }
                    if (!data.success) throw new Error(data.mensaje || 'No se pudo lanzar');
                    // Lanzado → polling de estado
                    await _pollEjecucion();
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo ejecutar', confirmButtonText: 'Cerrar' });
                }
            }

            async function _pollEjecucion() {
                const esc = s => (s + '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var intentos = 0;
                var maxIntentos = 60; // hasta 2 min polling cada 2 s
                Swal.fire({
                    title: 'Ejecutando...',
                    html: '<div id="swal-exec-status" class="text-start"><p class="text-muted"><i class="fa fa-spinner fa-spin me-1"></i>Iniciando monitoreo previo y copiando archivo...</p></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: function() { Swal.showLoading(); }
                });
                while (intentos < maxIntentos) {
                    await new Promise(function(res){ setTimeout(res, 2000); });
                    intentos++;
                    try {
                        var r = await fetch('/segundometro/estadoEjecucion', { method: 'GET', headers: { 'Front-Request': 'true' } });
                        var data = await r.json();
                        var est = data.estado || {};
                        if (!est.running) {
                            // Terminó
                            var result = est.lastResult || {};
                            var ok = !!result.success;
                            var html = '<div class="text-start">'
                                + '<div class="alert ' + (ok ? 'alert-success' : 'alert-danger') + ' mb-2">'
                                + '<strong>' + (ok ? '✅ Éxito' : '❌ Error') + ':</strong> ' + esc(result.mensaje || (ok ? 'Completado' : 'Falló'))
                                + '</div>';
                            if (result.origen) html += '<p class="mb-1 small"><strong>Origen:</strong> <code>' + esc(result.origen) + '</code></p>';
                            if (result.destino) html += '<p class="mb-1 small"><strong>Destino:</strong> <code>' + esc(result.destino) + '</code></p>';
                            if (est.finishedAt) html += '<p class="mb-0 text-muted small">Terminó: ' + esc(est.finishedAt) + '</p>';
                            html += '<p class="mb-0 text-muted small mt-2"><i class="fa fa-info-circle me-1"></i>El reporte puede tardar hasta 10 min en aparecer en BD.</p>';
                            html += '</div>';
                            Swal.fire({ icon: ok ? 'success' : 'warning', title: 'Resultado', html: html, confirmButtonText: 'Cerrar' })
                                .then(function() { listarArchivos(true); actualizarEstadoAgente(); });
                            return;
                        }
                        // Sigue corriendo: actualizar texto
                        var elapsed = est.startedAt ? Math.round((Date.now() - new Date(est.startedAt).getTime()) / 1000) : null;
                        var el = document.getElementById('swal-exec-status');
                        if (el) el.innerHTML = '<p class="text-muted mb-0"><i class="fa fa-spinner fa-spin me-1"></i>Ejecutando' + (elapsed ? ' (' + elapsed + 's)' : '') + '...<br><small>Monitoreo + copia en curso en el servidor remoto.</small></p>';
                    } catch(_) {}
                }
                Swal.fire({ icon: 'info', title: 'Sigue en background', text: 'El proceso sigue corriendo en el agente. Revisa el estado en unos minutos con el botón de estado del agente.', confirmButtonText: 'Entendido' });
            }

            // ── Estado del catch-up ────────────────────────────────────────────────
            async function verEstadoCatchUp() {
                Swal.fire({ title: 'Consultando catch-up...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                try {
                    var r = await fetch('/segundometro/catchUpEstado', { method: 'GET', headers: { 'Front-Request': 'true' } });
                    var data = await r.json();
                    if (!data.success || !data.estado) throw new Error(data.mensaje || 'Sin datos');
                    var est = data.estado;
                    var esc = s => (s + '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    var html = '<div class="text-start" style="max-height:60vh;overflow-y:auto;">';
                    var estadoBadge = est.running
                        ? '<span class="badge bg-warning text-dark">En curso</span>'
                        : (est.completado ? '<span class="badge bg-success">Completado</span>' : '<span class="badge bg-secondary">No iniciado / sin datos</span>');
                    html += '<p class="mb-2"><strong>Estado:</strong> ' + estadoBadge + '</p>';
                    html += '<p class="mb-1"><strong>Procesados:</strong> ' + (est.procesados || 0) + ' &nbsp; <strong>Errores:</strong> ' + (est.errores || 0) + '</p>';
                    if (est.pendientes && est.pendientes.length) {
                        html += '<p class="mb-1"><strong>Pendientes (' + est.pendientes.length + '):</strong></p><ul class="mb-2 small">';
                        est.pendientes.forEach(function(n){ html += '<li><code>' + esc(n) + '</code></li>'; });
                        html += '</ul>';
                    } else {
                        html += '<p class="mb-1 text-success small">Sin pendientes detectados.</p>';
                    }
                    if (est.log && est.log.length) {
                        html += '<details class="mt-2"><summary class="fw-bold small text-secondary" style="cursor:pointer;">Log (' + est.log.length + ' líneas)</summary>';
                        html += '<pre class="mt-2 p-2 bg-dark text-light rounded small" style="max-height:200px;overflow-y:auto;font-size:0.75rem;">';
                        est.log.forEach(function(l){ html += esc(l) + '\n'; });
                        html += '</pre></details>';
                    }
                    html += '</div>';
                    Swal.fire({ title: 'Estado Catch-up al arrancar', html: html, icon: est.completado ? 'success' : (est.running ? 'info' : 'question'), confirmButtonText: 'Cerrar', width: '700px' });
                } catch(e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo consultar', confirmButtonText: 'Cerrar' });
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Delegado único: aviso de descarga + fetch (funciona en localhost y servidor aunque haya caché)
                document.addEventListener('click', function descargarReporteClick(e) {
                    var a = e.target && e.target.closest && e.target.closest('a[data-descargar-reporte]');
                    if (!a) return;
                    e.preventDefault();
                    var url = a.getAttribute('href');
                    if (!url) return;
                    var nombre = (a.getAttribute('data-nombre') || 'reporte.zip').replace(/</g, '');
                    var esc = function (s) { return (s + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;'); };
                    var hacerDescarga = function () {
                        fetch(url, { method: 'GET', credentials: 'same-origin', cache: 'no-store' })
                            .then(function (r) {
                                if (!r.ok) return r.text().then(function (t) { throw new Error(t || ('HTTP ' + r.status)); });
                                return r.blob();
                            })
                            .then(function (blob) {
                                if (typeof Swal !== 'undefined') Swal.close();
                                var u = URL.createObjectURL(blob);
                                var link = document.createElement('a');
                                link.href = u;
                                link.download = nombre;
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                                URL.revokeObjectURL(u);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'success', title: 'Descarga lista', text: nombre, timer: 2200, showConfirmButton: false });
                                }
                            })
                            .catch(function (err) {
                                if (typeof Swal !== 'undefined') Swal.close();
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'error', title: 'Error al descargar', html: esc((err && err.message) || String(err)).substring(0, 500), confirmButtonText: 'Cerrar' });
                                } else {
                                    alert('Error al descargar: ' + ((err && err.message) || err));
                                }
                            });
                    };
                    try {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Descargando reporte',
                                html: '<p class="mb-2">Se está descargando el reporte. Por favor espere; puede tardar según el tamaño del archivo.</p><code class="text-primary bg-light px-1 rounded small">' + esc(nombre) + '</code>',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: function () { if (typeof Swal !== 'undefined') Swal.showLoading(); hacerDescarga(); }
                            });
                        } else {
                            alert('Se está descargando el reporte. Por favor espere; puede tardar según el tamaño del archivo.');
                            hacerDescarga();
                        }
                    } catch (err) {
                        alert('Se está descargando el reporte. Por favor espere.');
                        hacerDescarga();
                    }
                });
                actualizarEstadoBotonTruncar();
                actualizarEstadoAgente();
                cargarEstadoAutoCopy();
                iniciarRelojCdmxTiempoReal();
                listarArchivos(false);
                programarRefrescos();
                setTimeout(function(){ actualizarEstadoReportes(); }, 800);
                setInterval(function(){ if (!document.hidden) actualizarEstadoAgente(); }, 30000);
                // Poll rápido para estados pendientes y poll normal de mantenimiento.
                segundometroEstadoFastInterval = setInterval(function() { if (!document.hidden) actualizarEstadoReportes(); }, 7000);
                segundometroEstadoInterval = setInterval(function() { if (!document.hidden) actualizarEstadoReportes(); }, 30000);
                var btnTruncar = document.getElementById('btnTruncarSegundometro');
                if (btnTruncar) btnTruncar.addEventListener('click', truncarSegundometro);
                var btnDiag = document.getElementById('btnDiagnosticoSSH');
                if (btnDiag) btnDiag.addEventListener('click', ejecutarDiagnosticoSSH);
                var btnProbarBd = document.getElementById('sgAgenteProbarBd');
                if (btnProbarBd) btnProbarBd.addEventListener('click', probarVerificacionBdAgente);
                var btnEjecutarAhora = document.getElementById('sgEjecutarAhora');
                if (btnEjecutarAhora) btnEjecutarAhora.addEventListener('click', ejecutarAhora);
                var btnCatchUp = document.getElementById('sgCatchUpEstado');
                if (btnCatchUp) btnCatchUp.addEventListener('click', verEstadoCatchUp);
                var chkAutoCopy = document.getElementById('sgAutoCopyEnabled');
                if (chkAutoCopy) chkAutoCopy.addEventListener('change', guardarEstadoAutoCopy);
                var linkPrueba = document.getElementById('linkTruncarModoPrueba');
                if (linkPrueba) {
                    if (esTruncarModoPrueba()) {
                        linkPrueba.textContent = 'Salir del modo prueba';
                        linkPrueba.title = 'Quitar ?truncar_test=1 y volver al horario normal (martes 7:00-9:30)';
                    }
                    linkPrueba.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (esTruncarModoPrueba()) {
                            var params = new URLSearchParams(window.location.search);
                            params.delete('truncar_test');
                            var qs = params.toString();
                            window.location.href = window.location.pathname + (qs ? '?' + qs : '');
                        } else {
                            var url = window.location.pathname + window.location.search;
                            url += (url.indexOf('?') === -1 ? '?' : '&') + 'truncar_test=1';
                            window.location.href = url;
                        }
                    });
                }
                var btnMon = document.getElementById('btnMonitorearSegundometro');
                if (btnMon) btnMon.addEventListener('click', abrirPanelMonitorear);
                var btnCerrarMon = document.getElementById('panelMonitorearCerrar');
                if (btnCerrarMon) btnCerrarMon.addEventListener('click', cerrarPanelMonitorear);
                var btnMinMon = document.getElementById('panelMonitorearMinimizar');
                if (btnMinMon) btnMinMon.addEventListener('click', toggleMinimizarPanelMonitorear);
                initPanelMonitorearDrag();
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) { clearSegundometroTimers(); clearEstadoReportesInterval(); }
                    else {
                        actualizarEstadoBotonTruncar();
                        listarArchivos(true);
                        programarRefrescos();
                        actualizarEstadoReportes();
                        sincronizarRelojCdmx();
                        clearEstadoReportesInterval();
                        segundometroEstadoFastInterval = setInterval(function() { if (!document.hidden) actualizarEstadoReportes(); }, 7000);
                        segundometroEstadoInterval = setInterval(function() { if (!document.hidden) actualizarEstadoReportes(); }, 30000);
                    }
                });
            });
        </script>
        HTML;

        self::set("titulo", "Shell Segundómetro");
        self::set("script", $script);
        self::render("shell_segundometro");
    }

    /**
     * Una sola ejecución de monitorear.sh (timeout 10 s). Respuesta JSON.
     * Sin streaming: la petición termina y no bloquea al cerrar el panel o cambiar de menú.
     */
    public function obtenerMonitorear()
    {
        try {
            if (!$this->validarModoSoloAgente('monitoreo')) return;
            $resultado = [
                'success' => false,
                'output' => '',
                'error' => 'Modo solo-agente activo: use el stream /segundometro/streamMonitorear.',
            ];
            self::respuestaJSON([
                'success' => $resultado['success'],
                'output'   => $resultado['output'] ?? '',
                'error'   => $resultado['error'] ?? ''
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'output'  => '',
                'error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Streaming SSE de monitorear.sh (para ventana nueva). La ventana mantiene la conexión; al cerrarla se libera el worker.
     */
    public function streamMonitorear()
    {
        if (!$this->usarAgente()) {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            echo "data: " . json_encode(['error' => 'Flujo legado deshabilitado. Active segundometro_agent.enabled=1.']) . "\n\n";
            if (function_exists('ob_flush')) { @ob_flush(); }
            if (function_exists('flush')) { flush(); }
            exit;
        }
        @ignore_user_abort(false);
        set_time_limit(3700);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        if (ob_get_level()) {
            while (ob_get_level()) { ob_end_flush(); }
        }
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', false);
        if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', '1'); }
        if (function_exists('session_write_close')) {
            session_write_close();
        }
        $url = $this->agenteBaseUrl() . '/stream/monitorear';
        $headers = ['Accept: text/event-stream'];
        $key = $this->agenteApiKey();
        if ($key !== '') $headers[] = 'X-Api-Key: ' . $key;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3700);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {
                echo $chunk;
                if (function_exists('ob_flush')) { @ob_flush(); }
                if (function_exists('flush')) { flush(); }
                return strlen($chunk);
            });
            $ok = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($ok === false) {
                echo "data: " . json_encode(['error' => 'Error al conectar stream del agente: ' . ($err ?: 'desconocido')]) . "\n\n";
                if (function_exists('ob_flush')) { @ob_flush(); }
                if (function_exists('flush')) { flush(); }
            }
            exit;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 3700,
                'header' => implode("\r\n", $headers)
            ]
        ]);
        $fp = @fopen($url, 'r', false, $context);
        if (!$fp) {
            echo "data: " . json_encode(['error' => 'No se pudo abrir el stream del agente']) . "\n\n";
            if (function_exists('ob_flush')) { @ob_flush(); }
            if (function_exists('flush')) { flush(); }
            exit;
        }
        while (!feof($fp) && !connection_aborted()) {
            $chunk = fread($fp, 8192);
            if ($chunk === false) break;
            if ($chunk !== '') {
                echo $chunk;
                if (function_exists('ob_flush')) { @ob_flush(); }
                if (function_exists('flush')) { flush(); }
            } else {
                usleep(120000);
            }
        }
        fclose($fp);
        exit;
    }

    /**
     * Ventana mínima que muestra el stream en vivo (EventSource). Al cerrar la ventana se cierra la conexión.
     * Sin layout para que sea solo consola.
     */
    public function ventanaMonitorear()
    {
        self::render("ventana_monitorear", true);
    }

    /**
     * Listar archivos de reportes de los últimos N días
     */
    public function listarArchivos()
    {
        try {
            if (!$this->validarModoSoloAgente('listar archivos')) return;
            $agent = $this->agenteRequest('GET', '/files');
            if (!$agent['success'] || !is_array($agent['json']) || empty($agent['json']['success'])) {
                $msg = 'No se pudieron listar archivos vía agente.';
                if (is_array($agent['json']) && !empty($agent['json']['mensaje'])) $msg .= ' ' . $agent['json']['mensaje'];
                elseif (!empty($agent['error'])) $msg .= ' ' . $agent['error'];
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => $msg
                ]);
                return;
            }
            self::respuestaJSON([
                'success' => true,
                'datos' => $agent['json']['datos'] ?? []
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al listar archivos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Copiar archivo: si se envía nombre_destino válido se usa ese; si no, se calcula +1 segundo.
     */
    public function copiarArchivo()
    {
        try {
            $nombreArchivo = $_POST['nombre_archivo'] ?? null;
            $nombreDestino = isset($_POST['nombre_destino']) ? trim((string) $_POST['nombre_destino']) : null;

            if (!$nombreArchivo) {
                throw new \Exception('Nombre de archivo no proporcionado');
            }
            if (!$this->validarModoSoloAgente('copiar archivos')) return;
            $payload = ['nombre_archivo' => $nombreArchivo];
            if ($nombreDestino !== null && $nombreDestino !== '') $payload['nombre_destino'] = $nombreDestino;
            $agent = $this->agenteRequest('POST', '/files/copy', $payload);
            if (!$agent['success'] || !is_array($agent['json']) || !array_key_exists('success', $agent['json'])) {
                $msg = 'Error al copiar vía agente.';
                if (is_array($agent['json']) && !empty($agent['json']['mensaje'])) $msg .= ' ' . $agent['json']['mensaje'];
                elseif (!empty($agent['error'])) $msg .= ' ' . $agent['error'];
                self::respuestaJSON(['success' => false, 'mensaje' => $msg]);
                return;
            }
            if (empty($agent['json']['success'])) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => $agent['json']['mensaje'] ?? 'Error al copiar archivo en agente'
                ]);
                return;
            }
            self::respuestaJSON([
                'success' => true,
                'mensaje' => $agent['json']['mensaje'] ?? 'Archivo copiado exitosamente',
                'datos' => $agent['json']['datos'] ?? ['origen' => $nombreArchivo, 'destino' => $nombreDestino]
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al copiar archivo: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Descargar reporte: copia del remoto a temporal y envía al navegador
     */
    public function descargarArchivo()
    {
        $nombreArchivo = $_GET['nombre_archivo'] ?? null;
        if (!$nombreArchivo || !preg_match('/^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/', $nombreArchivo)) {
            header('HTTP/1.0 400 Bad Request');
            echo 'Nombre de archivo inválido';
            exit;
        }
        if (!$this->usarAgente()) {
            header('HTTP/1.0 503 Service Unavailable');
            echo 'Flujo legado deshabilitado. Active segundometro_agent.enabled=1.';
            exit;
        }
        $url = $this->agenteBaseUrl() . '/files/' . rawurlencode($nombreArchivo) . '/download';
        $headers = [];
        $key = $this->agenteApiKey();
        if ($key !== '') $headers[] = 'X-Api-Key: ' . $key;

        if (!function_exists('curl_init')) {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'cURL no disponible para descargar desde agente.';
            exit;
        }
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
            curl_setopt($ch, CURLOPT_TIMEOUT, 240);
            if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $bin = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ctype = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $cerr = curl_error($ch);
            curl_close($ch);
            if ($bin === false || $status < 200 || $status >= 300) {
                throw new \Exception($cerr !== '' ? $cerr : ('HTTP ' . $status . ' al descargar desde agente'));
            }
            header('Content-Type: ' . ($ctype !== '' ? $ctype : 'application/zip'));
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Content-Length: ' . strlen($bin));
            echo $bin;
            exit;
        } catch (\Exception $e) {
            header('HTTP/1.0 500');
            echo 'Error al descargar desde agente: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * Eliminar archivo en el servidor remoto (solo si owner es root)
     */
    public function eliminarArchivo()
    {
        try {
            $nombreArchivo = $_POST['nombre_archivo'] ?? null;
            if (!$nombreArchivo) {
                throw new \Exception('Nombre de archivo no proporcionado');
            }
            if (!$this->validarModoSoloAgente('eliminar archivos')) return;
            $agent = $this->agenteRequest('DELETE', '/files/' . rawurlencode($nombreArchivo));
            if (!$agent['success'] || !is_array($agent['json']) || !array_key_exists('success', $agent['json'])) {
                $msg = 'Error al eliminar vía agente.';
                if (is_array($agent['json']) && !empty($agent['json']['mensaje'])) $msg .= ' ' . $agent['json']['mensaje'];
                elseif (!empty($agent['error'])) $msg .= ' ' . $agent['error'];
                self::respuestaJSON(['success' => false, 'mensaje' => $msg]);
                return;
            }
            if (!empty($agent['json']['success'])) {
                self::respuestaJSON(['success' => true, 'mensaje' => $agent['json']['mensaje'] ?? 'Archivo eliminado correctamente']);
                return;
            }
            self::respuestaJSON(['success' => false, 'mensaje' => $agent['json']['mensaje'] ?? 'Error al eliminar en agente']);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }

    /**
     * Truncar Segundómetro: copiar semana → historial, notificar webhook, truncar semana.
     * Solo permitido los martes de 7:00 a 9:30 AM (CDMX). Con truncar_test=1 se omite la validación (modo prueba).
     */
    public function truncarSegundometro()
    {
        try {
            $modoPrueba = false;
            $input = file_get_contents('php://input');
            if ($input !== '' && $input !== false) {
                $json = json_decode($input, true);
                $modoPrueba = !empty($json['truncar_test']);
            }
            if (!$modoPrueba && isset($_POST['truncar_test'])) {
                $modoPrueba = (bool)$_POST['truncar_test'];
            }

            if (!$modoPrueba) {
                // Validar: solo martes de 7:00 a 9:30 CDMX
                $tz = new \DateTimeZone('America/Mexico_City');
                $ahora = new \DateTime('now', $tz);
                $diaSemana = $ahora->format('N'); // 1=lunes, 7=domingo
                $hora = (int)$ahora->format('G');
                $minuto = (int)$ahora->format('i');
                $minutosDia = $hora * 60 + $minuto;
                $inicioPermitido = 7 * 60;       // 7:00
                $finPermitido    = 9 * 60 + 30;   // 9:30

                if ($diaSemana != 2) { // 2 = martes
                    self::respuestaJSON([
                        'success' => false,
                        'mensaje' => 'Operación solo disponible los martes de 7:00 a 9:30 AM (CDMX). Hoy no es martes.'
                    ]);
                    return;
                }
                if ($minutosDia < $inicioPermitido || $minutosDia >= $finPermitido) {
                    self::respuestaJSON([
                        'success' => false,
                        'mensaje' => 'Operación solo disponible los martes de 7:00 a 9:30 AM (CDMX). Hora actual: ' . $ahora->format('H:i')
                    ]);
                    return;
                }
            }

            $resultado = SegundometroDAO::truncarSemanaAHistorico();

            self::respuestaJSON([
                'success' => true,
                'mensaje' => $resultado['mensaje'],
                'registros_copiados' => $resultado['registros_copiados']
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Diagnóstico completo SSH: prueba config, llaves, conectividad, permisos, listado, descarga, monitoreo.
     * Devuelve JSON con lista de pruebas y resultado de cada una.
     */
    public function diagnosticoSSH()
    {
        try {
            if (!$this->validarModoSoloAgente('diagnóstico')) return;
            $agent = $this->agenteRequest('GET', '/diagnostico');
            if (!$agent['success'] || !is_array($agent['json']) || empty($agent['json']['success'])) {
                $msg = 'No se pudo ejecutar diagnóstico vía agente.';
                if (is_array($agent['json']) && !empty($agent['json']['mensaje'])) $msg .= ' ' . $agent['json']['mensaje'];
                elseif (!empty($agent['error'])) $msg .= ' ' . $agent['error'];
                self::respuestaJSON(['success' => false, 'mensaje' => $msg]);
                return;
            }
            self::respuestaJSON([
                'success' => true,
                'pruebas' => $agent['json']['pruebas'] ?? []
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al ejecutar diagnóstico: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Estado de integración del Shell con segundometro-agent (para UI).
     */
    public function estadoAgente()
    {
        try {
            $using = $this->usarAgente();
            if (!$using) {
                self::respuestaJSON([
                    'success' => true,
                    'usando_agente' => false,
                    'agente_online' => false,
                    'detalle' => 'Flujo legado activo (DAO SSH del proyecto).'
                ]);
                return;
            }
            $health = $this->agenteRequest('GET', '/health');
            if (!$health['success'] || !is_array($health['json']) || empty($health['json']['success'])) {
                self::respuestaJSON([
                    'success' => true,
                    'usando_agente' => true,
                    'agente_online' => false,
                    'detalle' => 'No responde /health del agente (' . ($health['error'] ?: 'sin respuesta') . ').'
                ]);
                return;
            }
            $auto = $this->agenteRequest('GET', '/auto-copy');
            $detalle = 'Agente en línea.';
            if ($auto['success'] && is_array($auto['json']) && !empty($auto['json']['success'])) {
                $enabled = !empty($auto['json']['enabled']) ? 'activo' : 'inactivo';
                $next = (isset($auto['json']['proximaEjecucion']['label']) ? $auto['json']['proximaEjecucion']['label'] : '—');
                $detalle = 'Auto-copy ' . $enabled . ' | Próxima: ' . $next;
            }
            self::respuestaJSON([
                'success' => true,
                'usando_agente' => true,
                'agente_online' => true,
                'detalle' => $detalle
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'usando_agente' => $this->usarAgente(),
                'agente_online' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }

    /**
     * Prueba rápida de verificación BD sobre el último archivo visible.
     * Sirve para validar desde UI la lógica base usada por el fallback.
     */
    public function probarVerificacionBdAgente()
    {
        try {
            if (!$this->validarModoSoloAgente('probar verificación BD')) return;
            $nombre = null;
            $fuente = 'agente';
            $agent = $this->agenteRequest('GET', '/files');
            if ($agent['success'] && is_array($agent['json']) && !empty($agent['json']['success'])) {
                $lista = $agent['json']['datos'] ?? [];
                if (is_array($lista) && !empty($lista) && !empty($lista[0]['nombre'])) {
                    $nombre = (string)$lista[0]['nombre'];
                }
            }
            if ($nombre === null) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No hay archivos para validar en BD.']);
                return;
            }
            $estado = 'procesando';
            $estadoReq = $this->agenteRequest('POST', '/reportes/estado', ['nombres' => [$nombre]]);
            if ($estadoReq['success'] && is_array($estadoReq['json']) && !empty($estadoReq['json']['success']) && isset($estadoReq['json']['estados'][$nombre])) {
                $estado = $estadoReq['json']['estados'][$nombre];
            }
            self::respuestaJSON([
                'success' => true,
                'nombre_archivo' => $nombre,
                'estado' => $estado,
                'fuente' => $fuente
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Hora del servidor en CDMX (para revisar desfase del reloj). GET → { hora_servidor_cdmx: "Y-m-d H:i:s T" }
     */
    public function horaServidor()
    {
        if (!$this->validarModoSoloAgente('consultar hora servidor')) return;
        $agent = $this->agenteRequest('GET', '/hora-cdmx');
        if ($agent['success'] && is_array($agent['json']) && !empty($agent['json']['success'])) {
            self::respuestaJSON([
                'success' => true,
                'hora_servidor_cdmx' => $agent['json']['hora_servidor_cdmx'] ?? '',
                'fuente_hora' => $agent['json']['fuente_hora'] ?? '',
                'hora_remota' => !empty($agent['json']['hora_remota']),
                'timestamp_ms' => $agent['json']['timestamp_ms'] ?? null,
            ]);
            return;
        }
        self::respuestaJSON([
            'success' => false,
            'hora_servidor_cdmx' => '',
            'timestamp_ms' => null,
            'mensaje' => 'No se pudo consultar hora vía agente.'
        ]);
    }

    /**
     * Estado de reportes por BD (sin SSH). POST con { nombres: [...] } → { estados: { nombre: 'ok'|'error'|'procesando' } }
     */
    public function estadoReportes()
    {
        try {
            if (!$this->validarModoSoloAgente('consultar estado de reportes')) return;
            $nombres = [];
            $input = file_get_contents('php://input');
            if ($input !== '' && $input !== false) {
                $json = json_decode($input, true);
                if (isset($json['nombres']) && is_array($json['nombres'])) {
                    $nombres = $json['nombres'];
                }
            }
            if (empty($nombres) && isset($_POST['nombres']) && is_array($_POST['nombres'])) {
                $nombres = $_POST['nombres'];
            }
            $agent = $this->agenteRequest('POST', '/reportes/estado', ['nombres' => $nombres]);
            if ($agent['success'] && is_array($agent['json']) && !empty($agent['json']['success'])) {
                self::respuestaJSON([
                    'success' => true,
                    'estados' => $agent['json']['estados'] ?? [],
                    'hora_servidor_cdmx' => $agent['json']['hora_servidor_cdmx'] ?? '',
                    'timestamp_ms' => $agent['json']['timestamp_ms'] ?? null,
                    'fuente' => 'agente',
                ]);
                return;
            }
            // Fallback: agente no alcanzó PHP estadoReportesAgente (URL/key/red). Consultar BD en el mismo servidor.
            $estados = SegundometroDAO::obtenerEstadoReportesPorBD($nombres);
            $tz = new \DateTimeZone('America/Mexico_City');
            $horaServidorCDMX = (new \DateTime('now', $tz))->format('Y-m-d H:i:s') . ' CDMX';
            $detalleAgente = '';
            if (!$agent['success']) {
                $detalleAgente = 'Agente HTTP/status fallo. ';
            } elseif (is_array($agent['json']) && !empty($agent['json']['mensaje'])) {
                $detalleAgente = $agent['json']['mensaje'] . ' ';
            }
            self::respuestaJSON([
                'success' => true,
                'estados' => $estados,
                'hora_servidor_cdmx' => $horaServidorCDMX,
                'timestamp_ms' => null,
                'fuente' => 'dao_fallback',
                'mensaje' => $detalleAgente ? trim($detalleAgente) . 'Estados servidos desde BD local.' : null,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'estados' => [],
                'mensaje' => $e->getMessage()
            ]);
        }
    }

    public function autoCopyEstado()
    {
        try {
            if (!$this->validarModoSoloAgente('consultar auto-copy')) return;
            $agent = $this->agenteRequest('GET', '/auto-copy');
            if (!$agent['success'] || !is_array($agent['json']) || empty($agent['json']['success'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No se pudo consultar auto-copy en agente.']);
                return;
            }
            self::respuestaJSON([
                'success' => true,
                'enabled' => !empty($agent['json']['enabled']),
                'preRunMonitoreo' => !empty($agent['json']['preRunMonitoreo']),
                'horarios' => $agent['json']['horarios'] ?? [],
                'proximaEjecucion' => $agent['json']['proximaEjecucion'] ?? null,
                'ultimaEjecucion' => $agent['json']['ultimaEjecucion'] ?? null,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    public function autoCopyConfig()
    {
        try {
            if (!$this->validarModoSoloAgente('guardar auto-copy')) return;
            $input = file_get_contents('php://input');
            $body = [];
            if ($input !== '' && $input !== false) {
                $json = json_decode($input, true);
                if (is_array($json)) $body = $json;
            }
            if (isset($_POST['enabled'])) {
                $body['enabled'] = ($_POST['enabled'] === '1' || $_POST['enabled'] === 1 || $_POST['enabled'] === true || $_POST['enabled'] === 'true');
            }
            $payload = [];
            if (array_key_exists('enabled', $body)) $payload['enabled'] = (bool)$body['enabled'];
            if (array_key_exists('preRunMonitoreo', $body)) $payload['preRunMonitoreo'] = (bool)$body['preRunMonitoreo'];
            if (isset($body['horarios']) && is_array($body['horarios'])) $payload['horarios'] = $body['horarios'];
            $agent = $this->agenteRequest('POST', '/auto-copy', $payload);
            if (!$agent['success'] || !is_array($agent['json']) || empty($agent['json']['success'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No se pudo guardar auto-copy en agente.']);
                return;
            }
            self::respuestaJSON([
                'success' => true,
                'enabled' => !empty($agent['json']['enabled']),
                'preRunMonitoreo' => !empty($agent['json']['preRunMonitoreo']),
                'horarios' => $agent['json']['horarios'] ?? [],
                'proximaEjecucion' => $agent['json']['proximaEjecucion'] ?? null,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Dispara el job auto-copy inmediatamente (para probar o recuperar un horario perdido).
     * Inicia en background en el agente; devuelve de inmediato con estado inicial.
     */
    public function ejecutarAhora()
    {
        try {
            if (!$this->validarModoSoloAgente('ejecutar ahora')) return;
            $agent = $this->agenteRequest('POST', '/auto-copy/ejecutar-ahora');
            if (!$agent['success'] || !is_array($agent['json'])) {
                $msg = 'No se pudo lanzar ejecución en agente.';
                if (!empty($agent['error'])) $msg .= ' ' . $agent['error'];
                self::respuestaJSON(['success' => false, 'mensaje' => $msg]);
                return;
            }
            self::respuestaJSON([
                'success'  => !empty($agent['json']['success']),
                'mensaje'  => $agent['json']['mensaje'] ?? '',
                'estado'   => $agent['json']['estado'] ?? null,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Estado de la última ejecución lanzada con ejecutarAhora() (running / done / error).
     */
    public function estadoEjecucion()
    {
        try {
            if (!$this->validarModoSoloAgente('estado ejecución')) return;
            $agent = $this->agenteRequest('GET', '/auto-copy/ejecutar-ahora/estado');
            if (!$agent['success'] || !is_array($agent['json'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No se pudo consultar estado de ejecución.']);
                return;
            }
            self::respuestaJSON([
                'success' => !empty($agent['json']['success']),
                'estado'  => $agent['json']['estado'] ?? null,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Estado del catch-up automático al arrancar el agente.
     */
    public function catchUpEstado()
    {
        try {
            if (!$this->validarModoSoloAgente('estado catch-up')) return;
            $agent = $this->agenteRequest('GET', '/catch-up/estado');
            if (!$agent['success'] || !is_array($agent['json'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'No se pudo consultar catch-up.']);
                return;
            }
            self::respuestaJSON([
                'success' => !empty($agent['json']['success']),
                'estado'  => $agent['json']['estado'] ?? null,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Estado de reportes para el agente (sin sesión web).
     * Seguridad:
     * - Si existe segundometro_agent_key en config.ini, requiere header X-Agent-Key válido.
     * - Si no existe key, solo permite solicitudes locales (127.0.0.1 / ::1).
     */
    public function estadoReportesAgente()
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        $esLocal = in_array($remoteAddr, ['127.0.0.1', '::1'], true);
        $expectedKey = trim((string) (CONFIGURACION['segundometro_agent_key'] ?? ''));
        $providedKey = trim((string) ($_SERVER['HTTP_X_AGENT_KEY'] ?? ''));

        if ($expectedKey !== '') {
            if ($providedKey === '' || !hash_equals($expectedKey, $providedKey)) {
                self::respuestaJSON(['success' => false, 'estados' => [], 'mensaje' => 'No autorizado']);
                return;
            }
        } else {
            if (!$esLocal) {
                self::respuestaJSON(['success' => false, 'estados' => [], 'mensaje' => 'No autorizado (solo localhost)']);
                return;
            }
        }

        try {
            $nombres = [];
            $input = file_get_contents('php://input');
            if ($input !== '' && $input !== false) {
                $json = json_decode($input, true);
                if (isset($json['nombres']) && is_array($json['nombres'])) {
                    $nombres = $json['nombres'];
                }
            }
            if (empty($nombres) && isset($_POST['nombres']) && is_array($_POST['nombres'])) {
                $nombres = $_POST['nombres'];
            }
            $estados = SegundometroDAO::obtenerEstadoReportesPorBD($nombres);
            $tz = new \DateTimeZone('America/Mexico_City');
            $horaServidorCDMX = (new \DateTime('now', $tz))->format('Y-m-d H:i:s T');
            self::respuestaJSON([
                'success' => true,
                'estados' => $estados,
                'hora_servidor_cdmx' => $horaServidorCDMX
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'estados' => [],
                'mensaje' => $e->getMessage()
            ]);
        }
    }
}
