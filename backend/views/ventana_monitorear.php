<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitorear</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Consolas', 'Monaco', monospace; background: #1e1e1e; color: #d4d4d4; padding: 12px; min-height: 100vh; }
        #monitorearEstado { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 8px 12px; margin-bottom: 8px; background: #252526; border-radius: 6px; border: 1px solid #444; font-size: 0.8rem; }
        #monitorearEstado .estado { font-weight: 600; }
        #monitorearEstado .estado.conectado { color: #4ec9b0; }
        #monitorearEstado .estado.desconectado { color: #f48771; }
        #salida { white-space: pre-wrap; word-wrap: break-word; font-size: 0.85rem; line-height: 1.5; overflow: auto; max-height: calc(100vh - 120px); padding: 12px; background: #252526; border-radius: 6px; border: 1px solid #444; }
    </style>
</head>
<body>
    <div id="monitorearEstado">
        <span id="monitorearEstadoTexto" class="estado">Conectando...</span>
        <span id="monitorearUltimaActividad">Última actividad: --:--:--</span>
    </div>
    <pre id="salida"></pre>
    <script>
        var eventSource = null;
        var pre = document.getElementById('salida');
        var url = '/segundometro/streamMonitorear';
        var cerrandoConexion = false;
        var elEstado = document.getElementById('monitorearEstadoTexto');
        var elUltima = document.getElementById('monitorearUltimaActividad');

        function actualizarEstado(conectado) {
            if (!elEstado) return;
            elEstado.textContent = conectado ? 'Conectado (streaming)' : 'Desconectado';
            elEstado.className = 'estado ' + (conectado ? 'conectado' : 'desconectado');
        }
        function actualizarUltimaActividad(ts) {
            if (elUltima) elUltima.textContent = 'Última actividad: ' + (ts || '--:--:--');
        }

        function cerrarEventSource() {
            if (cerrandoConexion) return;
            cerrandoConexion = true;
            actualizarEstado(false);
            if (eventSource) {
                try {
                    eventSource.close();
                    eventSource = null;
                } catch (e) {}
            }
        }

        function iniciarMonitoreo() {
            cerrandoConexion = false;
            actualizarEstado(false);
            actualizarUltimaActividad('--:--:--');
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
            eventSource = new EventSource(url);
            eventSource.onopen = function() {
                if (!cerrandoConexion) actualizarEstado(true);
            };
            eventSource.onmessage = function(ev) {
                if (cerrandoConexion) return;
                try {
                    var d = JSON.parse(ev.data);
                    if (d.heartbeat) {
                        actualizarUltimaActividad(d.ts || new Date().toLocaleTimeString('es-MX', { hour12: false }));
                        return;
                    }
                    if (d.error) pre.textContent += d.error + '\n';
                    if (d.out) { pre.textContent += d.out; pre.scrollTop = pre.scrollHeight; }
                    if (d.err) { pre.textContent += d.err; pre.scrollTop = pre.scrollHeight; }
                    actualizarUltimaActividad(new Date().toLocaleTimeString('es-MX', { hour12: false }));
                    if (d.done) cerrarEventSource();
                } catch (e) { pre.textContent += ev.data + '\n'; actualizarUltimaActividad(new Date().toLocaleTimeString('es-MX', { hour12: false })); }
            };
            eventSource.onerror = function() {
                cerrarEventSource();
            };
        }

        window.addEventListener('message', function(event) {
            if (event.data && event.data.action === 'cerrarEventSource') {
                cerrarEventSource();
            }
        });
        window.addEventListener('beforeunload', cerrarEventSource);
        window.addEventListener('pagehide', cerrarEventSource);
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) cerrarEventSource();
        });

        iniciarMonitoreo();
    </script>
</body>
</html>
