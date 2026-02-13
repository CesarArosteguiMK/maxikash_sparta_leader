<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitorear</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Consolas', 'Monaco', monospace; background: #1e1e1e; color: #d4d4d4; padding: 12px; min-height: 100vh; }
        #salida { white-space: pre-wrap; word-wrap: break-word; font-size: 0.85rem; line-height: 1.5; overflow: auto; max-height: 100vh; padding: 12px; background: #252526; border-radius: 6px; border: 1px solid #444; }
    </style>
</head>
<body>
    <pre id="salida"></pre>
    <script>
        var eventSource = null;
        var pre = document.getElementById('salida');
        var url = '/segundometro/streamMonitorear';
        var cerrandoConexion = false;

        function cerrarEventSource() {
            if (cerrandoConexion) return;
            cerrandoConexion = true;
            if (eventSource) {
                try {
                    eventSource.close();
                    eventSource = null;
                } catch (e) {}
            }
        }

        function iniciarMonitoreo() {
            cerrandoConexion = false;
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
            eventSource = new EventSource(url);
            eventSource.onmessage = function(ev) {
                if (cerrandoConexion) return;
                try {
                    var d = JSON.parse(ev.data);
                    if (d.error) pre.textContent += d.error + '\n';
                    if (d.out) { pre.textContent += d.out; pre.scrollTop = pre.scrollHeight; }
                    if (d.err) { pre.textContent += d.err; pre.scrollTop = pre.scrollHeight; }
                    if (d.done) cerrarEventSource();
                } catch (e) { pre.textContent += ev.data + '\n'; }
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
