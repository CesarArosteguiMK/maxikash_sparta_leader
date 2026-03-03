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
                    .then(function(data){
                        if (data.success && data.estados) {
                            for (var k in data.estados) estadoReportesCache[k] = data.estados[k];
                            renderArchivos(archivosActuales, estadoReportesCache);
                        }
                        if (data.hora_servidor_cdmx) {
                            var el = document.getElementById('segundometroHoraServidor');
                            if (el) el.textContent = data.hora_servidor_cdmx;
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

            document.addEventListener('DOMContentLoaded', function() {
                actualizarEstadoBotonTruncar();
                listarArchivos(false);
                programarRefrescos();
                setTimeout(function(){ actualizarEstadoReportes(); }, 2500);
                segundometroEstadoInterval = setInterval(function() { if (!document.hidden) actualizarEstadoReportes(); }, 60000);
                // Hora del servidor (CDMX) para revisar desfase del reloj — se actualiza también con estadoReportes; aquí por si no hay archivos pendientes
                fetch('/segundometro/horaServidor', { headers: { 'Front-Request': 'true' } }).then(function(r){ return r.json(); }).then(function(d){ if (d.hora_servidor_cdmx) { var el = document.getElementById('segundometroHoraServidor'); if (el) el.textContent = d.hora_servidor_cdmx; } }).catch(function(){});
                var btnTruncar = document.getElementById('btnTruncarSegundometro');
                if (btnTruncar) btnTruncar.addEventListener('click', truncarSegundometro);
                var btnDiag = document.getElementById('btnDiagnosticoSSH');
                if (btnDiag) btnDiag.addEventListener('click', ejecutarDiagnosticoSSH);
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
        set_time_limit(3700);
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
        $timeoutTotal = 3600;
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
                echo "data: " . json_encode(['heartbeat' => true, 'ts' => date('H:i:s')]) . "\n\n";
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
            $errorLista = SegundometroDAO::getLastListError();

            // Si la lista está vacía por un fallo SSH, devolver error para que la UI lo muestre
            if (count($archivos) === 0 && $errorLista !== '') {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'No se pudieron listar los archivos: ' . $errorLista
                ]);
                return;
            }

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
     * Diagnóstico completo SSH: prueba config, llaves, conectividad, permisos, listado, descarga, monitoreo.
     * Devuelve JSON con lista de pruebas y resultado de cada una.
     */
    public function diagnosticoSSH()
    {
        try {
            $resultados = SegundometroDAO::diagnosticoSSH();
            self::respuestaJSON([
                'success' => true,
                'pruebas' => $resultados
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'mensaje' => 'Error al ejecutar diagnóstico: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Hora del servidor en CDMX (para revisar desfase del reloj). GET → { hora_servidor_cdmx: "Y-m-d H:i:s T" }
     */
    public function horaServidor()
    {
        header('Content-Type: application/json; charset=utf-8');
        $tz = new \DateTimeZone('America/Mexico_City');
        $hora = (new \DateTime('now', $tz))->format('Y-m-d H:i:s T');
        echo json_encode(['success' => true, 'hora_servidor_cdmx' => $hora]);
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
