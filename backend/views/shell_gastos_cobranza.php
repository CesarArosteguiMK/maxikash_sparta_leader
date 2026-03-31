<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <i class="fa fa-file-invoice-dollar text-primary me-2"></i>
                                <?= htmlspecialchars(isset($tituloShell) ? $tituloShell : 'Shell Gastos Cobranza', ENT_QUOTES, 'UTF-8') ?>
                            </h4>
                            <p class="text-muted mb-0">
                                Agente para <code>reporte_cobranza.py</code>, correos y cadena worker → lista negra.
                                <span class="badge bg-label-primary ms-1">Fase 2 · agente HTTP</span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted d-block">
                                <i class="fa fa-shield-alt me-1"></i>Módulo 31
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-plug text-primary me-2"></i>Estado del agente</h5>
                    <p class="small text-muted mb-3">
                        URL configurada: <code id="gastosCobranzaUrlDisplay"><?= htmlspecialchars(isset($gastosCobranzaAgenteUrl) ? $gastosCobranzaAgenteUrl : '', ENT_QUOTES, 'UTF-8') ?></code>
                        · <span id="gastosCobranzaHabilitadoIni"><?= !empty($gastosCobranzaAgenteHabilitado) ? 'enabled=1 en INI' : 'enabled=0 (solo lectura de estado)' ?></span>
                    </p>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span id="gastosCobranzaEstadoBadge" class="badge bg-label-secondary">Comprobando…</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnGastosCobranzaRefrescar">
                            <i class="fa fa-sync-alt me-1"></i>Actualizar
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="btnGastosCobranzaEjecutar" disabled>
                            <i class="fa fa-play me-1"></i>Ejecutar reporte (vía agente)
                        </button>
                    </div>
                    <p class="small mb-2 text-muted" id="gastosCobranzaDetalle">—</p>
                    <div id="gastosCobranzaSalidaWrap" class="d-none">
                        <label class="form-label small fw-semibold">Salida reciente</label>
                        <pre id="gastosCobranzaSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:240px;overflow:auto;white-space:pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-server text-warning me-2"></i>Puesta en marcha</h5>
                    <ol class="small text-muted mb-0 ps-3">
                        <li class="mb-2">En <code>backend/config/config.ini</code> → <code>[gastoscobranza_agent]</code>: <code>enabled=1</code>, <code>url</code> y opcional <code>key</code>.</li>
                        <li class="mb-2">En la carpeta <code>backend/services/gastos-cobranza-agent</code>: <code>npm install</code> y <code>npm start</code> (puerto 3120 por defecto).</li>
                        <li class="mb-2">En <code>.env</code> del agente: <code>REPORTE_COBRANZA_SCRIPT</code> = ruta absoluta al <code>.py</code>; opcional <code>API_KEY</code> acorde al INI.</li>
                        <li>Siguiente iteración: streaming de logs y descarga de Excel desde PHP.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-secondary mb-0" role="alert">
                <strong>Rutas API:</strong>
                <code>/gastoscobranza/estadoAgente</code>,
                <code>/gastoscobranza/ejecutarReporte</code>
                — Cabecera <code>Front-Request: true</code> en llamadas AJAX.
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    var badge = document.getElementById('gastosCobranzaEstadoBadge');
    var detalle = document.getElementById('gastosCobranzaDetalle');
    var btnRef = document.getElementById('btnGastosCobranzaRefrescar');
    var btnRun = document.getElementById('btnGastosCobranzaEjecutar');
    var outWrap = document.getElementById('gastosCobranzaSalidaWrap');
    var outPre = document.getElementById('gastosCobranzaSalida');

    function alertar(titulo, texto, icono) {
        if (typeof Swal !== 'undefined') {
            Swal.fire(titulo, texto, icono || 'info');
        } else {
            window.alert(titulo + ': ' + texto);
        }
    }

    async function refrescarEstado() {
        badge.className = 'badge bg-label-warning';
        badge.textContent = 'Comprobando…';
        detalle.textContent = '—';
        btnRun.disabled = true;
        try {
            var r = await fetch('/gastoscobranza/estadoAgente', {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (!data.success) {
                badge.className = 'badge bg-label-danger';
                badge.textContent = 'Error';
                detalle.textContent = data.mensaje || 'Error';
                return;
            }
            if (!data.agente_configurado) {
                badge.className = 'badge bg-label-secondary';
                badge.textContent = 'Agente desactivado (INI)';
                detalle.textContent = data.detalle || '';
                return;
            }
            if (data.agente_online) {
                badge.className = 'badge bg-label-success';
                badge.textContent = 'Agente en línea';
                detalle.textContent = data.detalle || '';
                btnRun.disabled = false;
            } else {
                badge.className = 'badge bg-label-danger';
                badge.textContent = 'Sin conexión';
                detalle.textContent = data.detalle || '';
            }
        } catch (e) {
            badge.className = 'badge bg-label-danger';
            badge.textContent = 'Error red';
            detalle.textContent = String(e.message || e);
        }
    }

    async function ejecutar() {
        btnRun.disabled = true;
        outWrap.classList.add('d-none');
        try {
            var r = await fetch('/gastoscobranza/ejecutarReporte', {
                method: 'POST',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            var ok = !!data.success;
            var msg = data.mensaje || (ok ? 'Proceso terminado.' : 'Falló la ejecución.');
            if (data.stdout || data.stderr) {
                outPre.textContent = (data.stdout || '') + (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                outWrap.classList.remove('d-none');
            }
            alertar(ok ? 'Listo' : 'Atención', msg, ok ? 'success' : 'warning');
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
        refrescarEstado();
    }

    btnRef.addEventListener('click', refrescarEstado);
    btnRun.addEventListener('click', ejecutar);
    refrescarEstado();
})();
</script>
