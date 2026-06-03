    <!-- Encabezado (mismo patrón que Shell Gastos Cobranza) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 gc-shell-hero-card">
                <div class="card-body py-4">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-1">
                                <h4 class="mb-0 fw-semibold text-heading gc-shell-hero-title">
                                    <i class="fa fa-stopwatch text-primary me-2" aria-hidden="true"></i>
                                    Segundometro - Gestión de Reportes
                                </h4>
                                <span id="sgAgenteEstado" class="badge bg-label-secondary">Agente: comprobando…</span>
                            </div>
                            <div id="sgAgenteDetalle" class="small text-muted mt-1" style="min-height:1.25em">Consultando estado…</div>
                            <p class="text-muted mt-2 mb-0 small">
                                Modulo para gestionar archivos de reportes
                            </p>
                        </div>
                        <div class="col-lg-4 d-flex justify-content-lg-end">
                            <div class="gc-shell-module-card">
                                <div class="gc-shell-module-icon" aria-hidden="true">
                                    <i class="fa fa-shield-alt"></i>
                                </div>
                                <div class="gc-shell-module-text">
                                    <span class="gc-shell-module-label">Control de acceso</span>
                                    <span class="gc-shell-module-name">Módulo 16</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Centro de control -->
    <div class="row mb-3">
        <div class="col-12">
            <div id="shellSegundometroAgenteBar" class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="sg-agent-head mb-3">
                        <span class="sg-agent-title">
                            <span class="sg-agent-dot" aria-hidden="true"></span>
                            Integración agente
                        </span>
                        <div class="form-check form-switch mb-0 sg-agent-autocopy" title="Activa o desactiva el auto-copy del agente en segundo plano.">
                            <input class="form-check-input" type="checkbox" role="switch" id="sgAutoCopyEnabled">
                            <label class="form-check-label small" for="sgAutoCopyEnabled">Auto-copy</label>
                        </div>
                    </div>
                    <div class="sg-agent-actions">
                        <div class="sg-agent-row-top">
                            <div id="wrapBtnTruncarSegundometro" class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-danger" id="btnTruncarSegundometro" disabled>
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-cut"></i>
                                        <span class="sg-btn-label">Truncar</span>
                                        <span class="sg-tooltip-icon" data-tip="Disponible solo los martes de 7:00 a 9:30 AM (CDMX)."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                    </span>
                                </button>
                            </div>
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-warn" id="btnMonitorearSegundometro">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-terminal"></i>
                                        <span class="sg-btn-label">Monitorear</span>
                                        <span class="sg-tooltip-icon" data-tip="Abre el monitoreo en vivo en esta página (CPU, memoria y actividad del servidor)."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                    </span>
                                </button>
                            </div>
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-cyan" id="btnDiagnosticoSSH">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-stethoscope"></i>
                                        <span class="sg-btn-label">Diagnóstico SSH</span>
                                        <span class="sg-tooltip-icon" data-tip="Ejecuta diagnóstico SSH: conexión, llaves, permisos y pruebas de lectura/escritura en el remoto."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div class="sg-agent-row-bottom">
                            <button type="button" class="sg-tip-btn sg-btn-green sg-tip-btn-run" id="sgEjecutarAhora">
                                <span class="sg-tip-btn-face">
                                    <i class="fa fa-play"></i>
                                    <span class="sg-btn-label">Ejecutar ahora</span>
                                    <span class="sg-tooltip-icon" data-tip="Lanza ahora el flujo automático del agente: monitoreo previo, copia del último reporte y ajuste +1s."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                </span>
                            </button>
                            <button type="button" class="sg-tip-btn sg-btn-cyan sg-tip-btn-run" id="sgDescargarMegaReporteMes">
                                <span class="sg-tip-btn-face">
                                    <i class="fa fa-file-archive"></i>
                                    <span class="sg-btn-label">Mega reporte mensual</span>
                                    <span class="sg-tooltip-icon" data-tip="Descarga un CSV consolidado con todos los mega reportes del mes seleccionado."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="mt-2" id="wrapLinkTruncarPrueba" style="display: none;">
                        <a href="#" id="linkTruncarModoPrueba" class="small text-muted" title="Activa el modo prueba (?truncar_test=1) para mostrar Truncar fuera del horario normal.">Habilitar Truncar para pruebas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 📁 LISTA DE ARCHIVOS -->
    <div class="row">
        <div class="col-12">
            <div id="archivos-container">
                <!-- Los archivos se cargarán aquí dinámicamente -->
            </div>
        </div>
    </div>

<!-- 💫 OVERLAY DE CARGA -->
<div id="loading-overlay" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
">
    <div class="text-center">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Procesando...</span>
        </div>
        <p class="text-white mt-3 fw-semibold">Procesando operación...</p>
    </div>
</div>

<!-- Panel Monitorear: iframe con stream en la misma página; minimizar deja usar otros botones sin cortar el stream -->
<div id="panelMonitorear" style="display:none; position:fixed; top:80px; right:20px; width:520px; max-width:95vw; height:420px; max-height:75vh; z-index:9000; background:#1e1e1e; border:1px solid #444; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.4); flex-direction:column; overflow:hidden;">
    <div id="panelMonitorearHeader" style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; background:#2d2d2d; cursor:move; user-select:none; border-bottom:1px solid #444;">
        <span style="color:#fff; font-weight:600;"><i class="fa fa-terminal me-2"></i>Monitorear</span>
        <span>
            <button type="button" id="panelMonitorearMinimizar" style="background:transparent; border:none; color:#aaa; cursor:pointer; padding:4px 8px;" title="Minimizar (el stream sigue; podrás usar Truncar y demás botones)"><i class="fa fa-window-minimize"></i></button>
            <button type="button" id="panelMonitorearCerrar" style="background:transparent; border:none; color:#aaa; cursor:pointer; padding:4px 8px;" title="Cerrar"><i class="fa fa-times"></i></button>
        </span>
    </div>
    <div id="panelMonitorearBody" style="flex:1; min-height:0; display:flex; flex-direction:column;">
        <iframe id="panelMonitorearIframe" style="flex:1; width:100%; height:100%; min-height:0; border:none; background:#1e1e1e;"></iframe>
    </div>
</div>

<!-- 🎨 ESTILOS PERSONALIZADOS -->
<style>
    /* Shell hero + tarjeta módulo (alineado con shell_gastos_cobranza.php) */
    .gc-shell-hero-card {
        background: linear-gradient(135deg, #fcfdff 0%, #f6f8fc 100%);
        border: 1px solid rgba(67, 89, 113, 0.08) !important;
    }
    .gc-shell-hero-title {
        letter-spacing: -0.02em;
    }
    .gc-shell-module-card {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        max-width: 16rem;
        padding: 0.85rem 1rem;
        border-radius: 0.65rem;
        background: #fff;
        border: 1px solid rgba(67, 89, 113, 0.1);
        box-shadow: 0 2px 8px rgba(67, 89, 113, 0.06);
    }
    .gc-shell-module-icon {
        flex-shrink: 0;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, rgba(105, 108, 255, 0.18), rgba(105, 108, 255, 0.06));
        color: #696cff;
        font-size: 1.1rem;
    }
    .gc-shell-module-text {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        min-width: 0;
    }
    .gc-shell-module-label {
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #a1acb8;
    }
    .gc-shell-module-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #566a7f;
        line-height: 1.2;
    }
    body.dark-mode .gc-shell-hero-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.65) 100%);
        border-color: rgba(148, 163, 184, 0.12) !important;
    }
    body.dark-mode .gc-shell-module-card {
        background: rgba(30, 41, 59, 0.85);
        border-color: rgba(148, 163, 184, 0.15);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    body.dark-mode .gc-shell-module-label {
        color: #94a3b8;
    }
    body.dark-mode .gc-shell-module-name {
        color: #e2e8f0;
    }

    /* Estilo del bloque de acciones del agente (4 botones) */
    #shellSegundometroAgenteBar {
        border: 0.5px solid #e2e8f0 !important;
        border-radius: 14px;
        background: #fff;
        display: inline-block;
        width: fit-content;
        max-width: 100%;
    }
    #shellSegundometroAgenteBar .sg-agent-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    #shellSegundometroAgenteBar .sg-agent-title {
        font-size: 0.76rem;
        font-weight: 600;
        color: #64748b;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    #shellSegundometroAgenteBar .sg-agent-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
    }
    #shellSegundometroAgenteBar .sg-agent-autocopy {
        color: #94a3b8;
    }
    #shellSegundometroAgenteBar .sg-agent-autocopy .form-check-label {
        font-size: 0.78rem;
    }
    #shellSegundometroAgenteBar .sg-agent-actions {
        width: min(860px, 100%);
    }
    #shellSegundometroAgenteBar .sg-agent-row-top {
        display: grid;
        grid-template-columns: repeat(3, minmax(150px, 1fr));
        gap: 8px;
        align-items: stretch;
    }
    #shellSegundometroAgenteBar .sg-agent-row-bottom {
        margin-top: 8px;
        display: flex;
        width: 100%;
        gap: 10px;
    }
    #shellSegundometroAgenteBar .sg-btn-wrap {
        min-width: 0;
    }
    #shellSegundometroAgenteBar .sg-tip-btn {
        position: relative;
        width: 100%;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid;
        background: #fff;
        padding: 0 0.9rem;
        font-size: 0.79rem;
        font-weight: 500;
        line-height: 1;
        transition: all 0.16s ease;
        white-space: nowrap;
    }
    #shellSegundometroAgenteBar .sg-tip-btn-face {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        width: 100%;
        min-width: 0;
        padding-right: 1.35rem;
    }
    #shellSegundometroAgenteBar .sg-tip-btn-face > i {
        font-size: 0.8rem;
        line-height: 1;
    }
    #shellSegundometroAgenteBar .sg-tooltip-icon {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: help;
        opacity: 0.55;
        flex-shrink: 0;
        line-height: 1;
    }
    #shellSegundometroAgenteBar .sg-tooltip-icon i {
        font-size: 0.72rem;
        line-height: 1;
    }
    #shellSegundometroAgenteBar .sg-btn-label {
        flex: 1;
        text-align: left;
    }
    #shellSegundometroAgenteBar .sg-tip-btn:hover {
        transform: translateY(-1px);
    }
    #shellSegundometroAgenteBar .sg-tip-btn:active {
        transform: scale(0.98);
    }
    #shellSegundometroAgenteBar .sg-tip-btn:disabled {
        opacity: 0.9;
        cursor: not-allowed;
        transform: none;
        pointer-events: none;
    }
    #shellSegundometroAgenteBar .sg-tip-btn:disabled .sg-tooltip-icon {
        pointer-events: auto;
    }
    #shellSegundometroAgenteBar .sg-tip-btn:disabled .sg-tip-btn-face {
        opacity: 0.55;
    }
    #shellSegundometroAgenteBar .sg-btn-danger {
        background: #fff5f5;
        border-color: #fca5a5;
        color: #dc2626;
    }
    #shellSegundometroAgenteBar .sg-btn-danger:hover {
        background: #fee2e2;
        border-color: #f87171;
    }
    #shellSegundometroAgenteBar .sg-btn-warn {
        background: #fffbeb;
        border-color: #fcd34d;
        color: #b45309;
    }
    #shellSegundometroAgenteBar .sg-btn-warn:hover {
        background: #fef3c7;
        border-color: #fbbf24;
    }
    #shellSegundometroAgenteBar .sg-btn-cyan {
        background: #f0fdfa;
        border-color: #5eead4;
        color: #0d9488;
    }
    #shellSegundometroAgenteBar .sg-btn-cyan:hover {
        background: #ccfbf1;
        border-color: #2dd4bf;
    }
    #shellSegundometroAgenteBar .sg-btn-green {
        background: #f0fdf4;
        border-color: #86efac;
        color: #16a34a;
    }
    #shellSegundometroAgenteBar .sg-btn-green:hover {
        background: #dcfce7;
        border-color: #4ade80;
    }
    #shellSegundometroAgenteBar .sg-tip-btn-run {
        min-height: 38px;
        height: 38px;
        width: 100%;
        flex: 1 1 auto;
        font-size: 0.82rem;
        display: flex;
        justify-content: center;
    }
    #shellSegundometroAgenteBar .sg-tip-btn-run .sg-btn-label {
        flex: 0 1 auto;
        text-align: center;
    }
    #shellSegundometroAgenteBar .sg-tooltip-icon::after {
        content: attr(data-tip);
        position: absolute;
        left: 50%;
        bottom: calc(100% + 8px);
        transform: translateX(-50%) translateY(3px);
        font-size: 0.72rem;
        line-height: 1.35;
        background: #020617;
        color: #f8fafc;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
        padding: 0.45rem 0.65rem;
        border-radius: 8px;
        white-space: normal;
        max-width: min(460px, 95vw);
        min-width: min(280px, 92vw);
        text-align: left;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.16s ease, transform 0.16s ease;
        z-index: 10050;
    }
    #shellSegundometroAgenteBar .sg-tooltip-icon::before {
        content: '';
        position: absolute;
        left: 50%;
        bottom: calc(100% + 2px);
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: #020617;
        opacity: 0;
        transition: opacity 0.16s ease;
        z-index: 10050;
    }
    #shellSegundometroAgenteBar .sg-tooltip-icon:hover::after,
    #shellSegundometroAgenteBar .sg-tooltip-icon:hover::before {
        opacity: 1;
    }
    #shellSegundometroAgenteBar .sg-tooltip-icon:hover::after {
        transform: translateX(-50%) translateY(0);
    }
    @media (max-width: 991.98px) {
        #shellSegundometroAgenteBar .sg-agent-actions {
            width: 100%;
        }
    }
    @media (max-width: 767.98px) {
        #shellSegundometroAgenteBar .sg-agent-row-top {
            grid-template-columns: 1fr;
        }
        #shellSegundometroAgenteBar .sg-tip-btn {
            height: 36px;
        }
        #shellSegundometroAgenteBar .sg-tip-btn-run {
            width: 100%;
        }
    }
    body.dark-mode #shellSegundometroAgenteBar {
        background: rgba(30, 41, 59, 0.92);
        border-color: rgba(148, 163, 184, 0.18) !important;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-agent-title {
        color: #cbd5e1;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-agent-autocopy,
    body.dark-mode #shellSegundometroAgenteBar .sg-agent-autocopy .form-check-label {
        color: #94a3b8;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-tip-btn {
        background: rgba(30, 41, 59, 0.9);
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.06) inset;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-btn-danger {
        background: rgba(127, 29, 29, 0.5);
        border-color: rgba(248, 113, 113, 0.55);
        color: #fecaca;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-btn-danger:hover {
        background: rgba(153, 27, 27, 0.62);
        border-color: #f87171;
        color: #fef2f2;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-btn-warn {
        background: rgba(120, 53, 15, 0.45);
        border-color: rgba(251, 191, 36, 0.5);
        color: #fde68a;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-btn-warn:hover {
        background: rgba(146, 64, 14, 0.58);
        border-color: #fbbf24;
        color: #fffbeb;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-btn-cyan {
        background: rgba(17, 94, 89, 0.42);
        border-color: rgba(45, 212, 191, 0.45);
        color: #99f6e4;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-btn-cyan:hover {
        background: rgba(19, 78, 74, 0.55);
        border-color: #2dd4bf;
        color: #ccfbf1;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-btn-green {
        background: rgba(20, 83, 45, 0.48);
        border-color: rgba(74, 222, 128, 0.5);
        color: #bbf7d0;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-btn-green:hover {
        background: rgba(22, 101, 52, 0.6);
        border-color: #4ade80;
        color: #f0fdf4;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-tip-btn:disabled {
        opacity: 1;
    }
    body.dark-mode #shellSegundometroAgenteBar .sg-tip-btn:disabled .sg-tip-btn-face {
        opacity: 0.6;
    }

    .font-monospace {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(67, 89, 113, 0.05);
        cursor: pointer;
    }

    .avatar-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
    }

    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .btn-success {
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(40, 199, 111, 0.4);
    }

    code {
        background-color: #f5f5f5;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.9em;
    }

    .alert-primary {
        border-left: 4px solid #696cff;
    }

    /* Forzar texto blanco en headers de archivos */
    .card-header.bg-primary h5,
    .card-header.bg-primary h5 i,
    .card-header.bg-primary * {
        color: #ffffff !important;
    }

    /* Badge contador de archivos - texto negro/oscuro */
    .card-header.bg-primary .badge {
        color: #2c3e50 !important;
        background-color: #ffffff !important;
        font-weight: 600;
    }
    /* Easter egg Shell: Ctrl+Shift+S → toast + lluvia Matrix */
    .shell-easter-wrap{position:fixed;inset:0;z-index:1046;pointer-events:none;overflow:hidden}
    .shell-easter-matrix-canvas{position:absolute;inset:0;width:100%;height:100%;display:block}
    .shell-easter-scan{position:absolute;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,rgba(0,255,136,0.6),transparent);box-shadow:0 0 20px 4px rgba(0,255,136,0.5);animation:shellEasterScan 1.2s linear infinite}
    @keyframes shellEasterScan{0%{top:0;opacity:0.6}50%{opacity:1}100%{top:100%;opacity:0.6}}
    .shell-easter-toast{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:1050;background:#0d1117;color:#00ff88;padding:18px 32px;border-radius:10px;font-size:1.05rem;font-weight:600;font-family:'Consolas','Monaco','Courier New',monospace;box-shadow:0 0 40px rgba(0,255,136,0.25), 0 0 80px rgba(0,255,136,0.1), inset 0 0 0 1px rgba(0,255,136,0.2);border:1px solid #00ff8840;opacity:0;pointer-events:none;text-align:left;min-width:220px;overflow:hidden;animation:shellEasterIn .3s ease forwards, shellEasterGlow 2s ease-in-out infinite .3s}
    @keyframes shellEasterGlow{0%,100%{box-shadow:0 0 30px rgba(0,255,136,0.2), 0 0 60px rgba(0,255,136,0.08), inset 0 0 0 1px rgba(0,255,136,0.2)}50%{box-shadow:0 0 50px rgba(0,255,136,0.4), 0 0 100px rgba(0,255,136,0.15), inset 0 0 0 1px rgba(0,255,136,0.35)}}
    .shell-easter-toast .shell-easter-cursor{display:inline-block;width:3px;height:1.1em;background:#00ff88;margin-left:2px;vertical-align:text-bottom;animation:shellEasterBlink 0.8s step-end infinite;box-shadow:0 0 8px #00ff88}
    @keyframes shellEasterBlink{0%,50%{opacity:1}51%,100%{opacity:0}}
    @keyframes shellEasterIn{0%{opacity:0;transform:translate(-50%,-50%) scale(0.9)}100%{opacity:1;transform:translate(-50%,-50%) scale(1)}}
    @keyframes shellEasterOut{0%{opacity:1;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(-50%,-50%) scale(0.95)}}
</style>

<script>
(function(){
    function beep(freq,dur){
        try{
            var ctx=new (window.AudioContext||window.webkitAudioContext)();
            var osc=ctx.createOscillator();
            var gain=ctx.createGain();
            osc.connect(gain);gain.connect(ctx.destination);
            osc.frequency.value=freq||880;osc.type='sine';
            gain.gain.setValueAtTime(0.12,ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01,ctx.currentTime+(dur||0.06));
            osc.start(ctx.currentTime);osc.stop(ctx.currentTime+(dur||0.06));
        }catch(e){}
    }
    function show(){
        var msg='$ Shell listo';
        var wrap=document.createElement('div');
        wrap.className='shell-easter-wrap';
        var canvas=document.createElement('canvas');
        canvas.className='shell-easter-matrix-canvas';
        var ctx=canvas.getContext('2d');
        var w=canvas.width=window.innerWidth;
        var h=canvas.height=window.innerHeight;
        window.addEventListener('resize',function(){w=canvas.width=window.innerWidth;h=canvas.height=window.innerHeight;});
        wrap.appendChild(canvas);
        var scan=document.createElement('div');
        scan.className='shell-easter-scan';
        wrap.appendChild(scan);
        document.body.appendChild(wrap);
        var chars='0__SPARTA_PASSWORD_REDACTED__ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var fontSize=14;
        var cols=Math.floor(w/fontSize);
        var drops=[];
        for(var c=0;c<cols;c++)drops[c]=Math.random()*h/fontSize;
        var rafId;
        function draw(){
            if(!wrap.parentNode)return;
            ctx.fillStyle='rgba(0,8,12,0.08)';
            ctx.fillRect(0,0,w,h);
            ctx.font=fontSize+'px "Consolas","Monaco",monospace';
            for(var c=0;c<cols;c++){
                var headChar=chars[Math.floor(Math.random()*chars.length)];
                ctx.fillStyle='#00ff88';
                ctx.fillText(headChar,c*fontSize,drops[c]*fontSize);
                ctx.fillStyle='rgba(0,255,136,0.25)';
                for(var t=1;t<=8;t++){
                    var idx=Math.max(0,drops[c]-t);
                    if(idx>=0)ctx.fillText(chars[Math.floor(Math.random()*chars.length)],c*fontSize,idx*fontSize);
                }
                drops[c]+=0.5+Math.random()*0.8;
                if(drops[c]*fontSize>h+40)drops[c]=0;
            }
            rafId=requestAnimationFrame(draw);
        }
        draw();
        var container=document.createElement('div');
        container.className='shell-easter-toast';
        var span=document.createElement('span');
        span.className='shell-easter-text';
        var cursor=document.createElement('span');
        cursor.className='shell-easter-cursor';
        container.appendChild(span);
        container.appendChild(cursor);
        document.body.appendChild(container);
        beep(880,0.05);
        var i=0;
        function type(){
            if(i<=msg.length){
                span.textContent=msg.slice(0,i);
                i++;
                if(i<=msg.length)beep(660,0.03);
                setTimeout(type,70);
            }else{
                beep(1100,0.08);
                setTimeout(function(){
                    cancelAnimationFrame(rafId);
                    container.style.animation='shellEasterOut .3s ease forwards';
                    setTimeout(function(){
                        if(container.parentNode)container.parentNode.removeChild(container);
                        if(wrap.parentNode)wrap.parentNode.removeChild(wrap);
                    },300);
                },2000);
            }
        }
        type();
    }
    document.addEventListener('keydown',function(e){
        if(e.ctrlKey&&e.shiftKey&&(e.key==='S'||e.keyCode===83)){e.preventDefault();show();}
    });
})();
</script>
