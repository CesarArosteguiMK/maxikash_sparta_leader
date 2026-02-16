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
                                                            <a href="/segundometro/descargarArchivo?nombre_archivo=${encodeURIComponent(archivo.nombre)}" class="btn btn-sm btn-primary" title="Descargar reporte">
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
            }
            function actualizarEstadoReportes() {
                if (document.hidden) return;
                if (!archivosActuales || archivosActuales.length === 0) return;
                var nombresPendientes = archivosActuales.map(function(a){ return a.nombre; }).filter(function(n){ return estadoReportesCache[n] !== 'ok'; });
                if (nombresPendientes.length === 0) return;
                fetch('/segundometro/estadoReportes', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' }, body: JSON.stringify({ nombres: nombresPendientes }) })
                    .then(function(r){ return r.json(); })
                    .then(function(data){ if (data.success && data.estados) { for (var k in data.estados) estadoReportesCache[k] = data.estados[k]; renderArchivos(archivosActuales, estadoReportesCache); } })
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

            // Monitorear: panel con iframe (stream solo en el iframe; al cerrar panel se corta la conexión)
            function abrirPanelMonitorear() {
                var panel = document.getElementById('panelMonitorear');
                var iframe = document.getElementById('panelMonitorearIframe');
                if (!panel || !iframe) return;
                iframe.src = '/segundometro/ventanaMonitorear';
                panel.style.display = 'flex';
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
                    nuevoIframe.style.cssText = 'flex:1; width:100%; height:360px; border:none; background:#1e1e1e;';
                    contenedorIframe.appendChild(nuevoIframe);
                }
                if (panel) panel.style.display = 'none';
            }
            function initPanelMonitorearDrag() {
                var panel = document.getElementById('panelMonitorear');
                var header = document.getElementById('panelMonitorearHeader');
                if (!panel || !header) return;
                var dragging = false, offX = 0, offY = 0;
                header.addEventListener('mousedown', function(e) {
                    if (e.target.id === 'panelMonitorearCerrar' || e.target.closest('#panelMonitorearCerrar')) return;
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
                        + '<li><strong>Copiar</strong> todos los registros de <code>tbl_segundometro_semana_prueba</code> a <code>tbl_segundometro_histo_prueba</code></li>'
                        + '<li><strong>Notificar</strong> el resultado a Google Chat</li>'
                        + '<li><strong>Truncar</strong> (vaciar) la tabla <code>tbl_segundometro_semana_prueba</code></li>'
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

            document.addEventListener('DOMContentLoaded', function() {
                actualizarEstadoBotonTruncar();
                listarArchivos(false);
                programarRefrescos();
                setTimeout(function(){ actualizarEstadoReportes(); }, 2500);
                segundometroEstadoInterval = setInterval(function() { if (!document.hidden) actualizarEstadoReportes(); }, 60000);
                var btnTruncar = document.getElementById('btnTruncarSegundometro');
                if (btnTruncar) btnTruncar.addEventListener('click', truncarSegundometro);
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
                initPanelMonitorearDrag();
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) { clearSegundometroTimers(); clearEstadoReportesInterval(); }
                    else {
                        actualizarEstadoBotonTruncar();
                        listarArchivos(true);
                        programarRefrescos();
                        actualizarEstadoReportes();
                        clearEstadoReportesInterval();
                        segundometroEstadoInterval = setInterval(function() { if (!document.hidden) actualizarEstadoReportes(); }, 60000);
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
            $resultado = SegundometroDAO::obtenerSalidaMonitorearCorto(10);
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
        @ignore_user_abort(false);
        set_time_limit(60);
        $cmd = SegundometroDAO::getComandoMonitorearParaStream();
        if ($cmd === null) {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            echo "data: " . json_encode(['error' => 'SSH no disponible']) . "\n\n";
            if (function_exists('ob_flush')) { @ob_flush(); }
            if (function_exists('flush')) { flush(); }
            exit;
        }
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
        // Liberar sesión para que otras peticiones (Eliminar, Copiar +1s) no queden bloqueadas esperando el lock
        if (function_exists('session_write_close')) {
            session_write_close();
        }
        $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = @proc_open($cmd, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            echo "data: " . json_encode(['error' => 'No se pudo iniciar el comando']) . "\n\n";
            if (function_exists('ob_flush')) { @ob_flush(); }
            if (function_exists('flush')) { flush(); }
            exit;
        }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $inicio = time();
        $timeoutTotal = 50;
        $ultimaActividad = time();
        $iteracion = 0;
        while (true) {
            $iteracion++;
            if ($iteracion % 5 === 0 && connection_aborted()) {
                @proc_terminate($process, 15);
                usleep(100000);
                @proc_terminate($process, 9);
                @fclose($pipes[1]);
                @fclose($pipes[2]);
                @proc_close($process);
                exit;
            }
            if (time() - $ultimaActividad > 5) {
                echo ": keepalive\n\n";
                if (function_exists('ob_flush')) { @ob_flush(); }
                if (function_exists('flush')) { flush(); }
                $ultimaActividad = time();
                if (connection_aborted()) {
                    @proc_terminate($process, 15);
                    usleep(100000);
                    @proc_terminate($process, 9);
                    @fclose($pipes[1]);
                    @fclose($pipes[2]);
                    @proc_close($process);
                    exit;
                }
            }
            $linea = fgets($pipes[1]);
            if ($linea !== false && $linea !== '') {
                echo "data: " . json_encode(['out' => $linea]) . "\n\n";
                if (function_exists('ob_flush')) { @ob_flush(); }
                if (function_exists('flush')) { flush(); }
                $ultimaActividad = time();
            }
            $errLine = fgets($pipes[2]);
            if ($errLine !== false && $errLine !== '') {
                echo "data: " . json_encode(['err' => $errLine]) . "\n\n";
                if (function_exists('ob_flush')) { @ob_flush(); }
                if (function_exists('flush')) { flush(); }
                $ultimaActividad = time();
            }
            $estado = proc_get_status($process);
            if (!$estado['running']) {
                break;
            }
            if (time() - $inicio >= $timeoutTotal) {
                break;
            }
            usleep(100000);
        }
        @proc_terminate($process, 15);
        usleep(50000);
        @proc_terminate($process, 9);
        @fclose($pipes[1]);
        @fclose($pipes[2]);
        @proc_close($process);
        echo "data: " . json_encode(['done' => true]) . "\n\n";
        if (function_exists('ob_flush')) { @ob_flush(); }
        if (function_exists('flush')) { flush(); }
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
            $archivos = SegundometroDAO::obtenerArchivos();
            
            self::respuestaJSON([
                'success' => true,
                'datos' => $archivos
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
            
            if ($nombreDestino !== '' && preg_match('/^mega_rpt_\d{8}_\d{2}_\d{2}_\d{2}\.csv\.zip$/', $nombreDestino) && $nombreDestino !== $nombreArchivo) {
                $resultado = SegundometroDAO::copiarAArchivo($nombreArchivo, $nombreDestino);
            } else {
                $resultado = SegundometroDAO::copiarConSegundoAdelantado($nombreArchivo);
            }
            
            self::respuestaJSON([
                'success' => true,
                'mensaje' => 'Archivo copiado exitosamente',
                'datos' => $resultado
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
        $rutaLocal = null;
        try {
            $rutaLocal = SegundometroDAO::copiarRemotoATemporal($nombreArchivo);
            if (!is_file($rutaLocal)) {
                throw new \Exception('No se pudo obtener el archivo');
            }
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Content-Length: ' . filesize($rutaLocal));
            readfile($rutaLocal);
            @unlink($rutaLocal);
            exit;
        } catch (\Exception $e) {
            if ($rutaLocal && is_file($rutaLocal)) {
                @unlink($rutaLocal);
            }
            header('HTTP/1.0 500');
            echo 'Error al descargar: ' . $e->getMessage();
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
            SegundometroDAO::eliminarArchivo($nombreArchivo);
            self::respuestaJSON(['success' => true, 'mensaje' => 'Archivo eliminado correctamente']);
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
     * Estado de reportes por BD (sin SSH). POST con { nombres: [...] } → { estados: { nombre: 'ok'|'error'|'procesando' } }
     */
    public function estadoReportes()
    {
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
            self::respuestaJSON([
                'success' => true,
                'estados' => $estados
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
