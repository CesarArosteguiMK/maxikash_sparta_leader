<?php /** @var string $google_maps_api_key_js */ ?>
<style>
/* ═══════════════════════════════════════════════════════
   Tracking Recolección — variables de color (teal/cyan)
═══════════════════════════════════════════════════════ */
:root {
    --track-color:        #0d9488;
    --track-color-light:  #ccfbf1;
    --track-color-dark:   #0f766e;
    --track-color-badge:  #14b8a6;
    --track-bg-card:      #f0fdfa;
    --track-border:       #99f6e4;
}
body.dark-mode {
    --track-color:        #2dd4bf;
    --track-color-light:  #134e4a;
    --track-color-dark:   #5eead4;
    --track-color-badge:  #2dd4bf;
    --track-bg-card:      #1a2e2c;
    --track-border:       #0d4040;
}

/* ── Cabecera del módulo ── */
.track-header {
    background: var(--track-bg-card);
    border: 1px solid var(--track-border);
    color: var(--track-color-dark);
    border-radius: .75rem;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}
body.dark-mode .track-header {
    background: var(--track-bg-card);
    border-color: var(--track-border);
    color: var(--track-color-dark);
}
.track-header h4 { margin: 0; font-weight: 700; letter-spacing: .5px; }
.track-header .track-subtitle { opacity: .85; font-size: .85rem; margin-top: .2rem; }

/* ── Filtros ── */
.track-filters {
    background: var(--track-bg-card);
    border: 1px solid var(--track-border);
    border-radius: .75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
body.dark-mode .track-filters { background: #1e2d2c; }

/* ── Tabla créditos ── */
#tablaCreditos thead th {
    font-size: .8rem;
    vertical-align: middle;
    white-space: nowrap;
}
#tablaCreditos tbody tr { vertical-align: middle; }

/* ── Tabla rutas ── */
#tablaRutas thead th {
    font-size: .8rem;
    vertical-align: middle;
    white-space: nowrap;
}

/* ── Badges de estatus de confirmación gestor ── */
.badge-conf-pendiente   { background: #fbbf24; color: #000; }
.badge-conf-confirmado  { background: #22c55e; color: #fff; }
.badge-conf-rechazado   { background: #ef4444; color: #fff; }
.badge-conf-en_revision { background: #60a5fa; color: #fff; }

/* ── Badges estatus ruta ── */
.badge-ruta-borrador              { background: #94a3b8; color: #fff; }
.badge-ruta-pendiente_confirmacion{ background: #f59e0b; color: #000; }
.badge-ruta-lista_envio           { background: #3b82f6; color: #fff; }
.badge-ruta-enviada               { background: #8b5cf6; color: #fff; }
.badge-ruta-en_proceso            { background: #0d9488; color: #fff; }
.badge-ruta-concluida             { background: #22c55e; color: #fff; }
.badge-ruta-cancelada             { background: #ef4444; color: #fff; }

/* ── Modal ── */
#modalRegistrarRuta .modal-header {
    background: var(--track-color);
    color: #fff;
    border-radius: .375rem .375rem 0 0;
}
#modalRegistrarRuta .modal-header .btn-close { filter: invert(1); }

/* ── Tabs del modal ── */
.track-tabs .nav-link,
.track-tabs .nav-link:link,
.track-tabs .nav-link:visited,
.track-tabs .nav-link:hover,
.track-tabs .nav-link:focus {
    color: var(--track-color-dark) !important;
    border-radius: .5rem .5rem 0 0;
}
.track-tabs .nav-link.active {
    background: var(--track-color) !important;
    color: #fff !important;
    border-color: var(--track-color) !important;
}
body.dark-mode .track-tabs .nav-link,
body.dark-mode .track-tabs .nav-link:link,
body.dark-mode .track-tabs .nav-link:visited,
body.dark-mode .track-tabs .nav-link:hover,
body.dark-mode .track-tabs .nav-link:focus {
    color: var(--track-color) !important;
}

/* ── Lista de créditos en modal (sortable) ── */
.track-credito-row {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    padding: .5rem .75rem;
    margin-bottom: .4rem;
    display: flex;
    align-items: center;
    gap: .5rem;
    cursor: grab;
    user-select: none;
    font-size: .82rem;
}
body.dark-mode .track-credito-row {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}
.track-credito-row:active { cursor: grabbing; }
.track-credito-row .drag-handle { color: #94a3b8; font-size: 1rem; }
.track-credito-row .orden-num {
    min-width: 1.4rem;
    font-weight: 700;
    color: var(--track-color);
}
.track-credito-row .btn-remove-cred {
    margin-left: auto;
    padding: .1rem .35rem;
    font-size: .75rem;
}
.eta-row .form-control,
.eta-row .form-select {
    font-size: .72rem;
    padding: .1rem .3rem;
    height: auto;
    line-height: 1.4;
}
body.dark-mode .eta-row .form-control,
body.dark-mode .eta-row .form-select {
    background-color: #1e2d2c;
    color: #e2e8f0;
    border-color: #2d4444;
}

/* ── Mapa ── */
#trackMapContainer {
    width: 100%;
    height: 475px;
    border-radius: .5rem;
    border: 2px solid var(--track-border);
    overflow: hidden;
    background: #e5e7eb;
}
#trackMap { width: 100%; height: 100%; }
.map-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6b7280;
    font-size: .9rem;
    flex-direction: column;
    gap: .4rem;
}

/* ── Tracking timeline (Mercado Libre style) ── */
#trkTrackingSection { font-size: .82rem; }
.trk-timeline { position: relative; padding-left: 1.6rem; }
.trk-timeline::before {
    content: '';
    position: absolute;
    left: .65rem;
    top: .4rem;
    bottom: .4rem;
    width: 2px;
    background: #e2e8f0;
}
body.dark-mode .trk-timeline::before { background: #334155; }
.trk-step {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: .6rem;
    padding: .45rem 0;
}
.trk-step-dot {
    position: absolute;
    left: -1.6rem;
    top: .55rem;
    width: 1.1rem;
    height: 1.1rem;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .5rem;
    color: #fff;
    z-index: 1;
    flex-shrink: 0;
}
body.dark-mode .trk-step-dot { background: #1e293b; }
.trk-step.done .trk-step-dot      { background: #16a34a; border-color: #16a34a; }
.trk-step.activo .trk-step-dot    { background: var(--track-color); border-color: var(--track-color); animation: trkPulse 1.4s infinite; }
.trk-step.en_sitio .trk-step-dot  { background: #f59e0b; border-color: #f59e0b; }
.trk-step.incidencia .trk-step-dot { background: #ef4444; border-color: #ef4444; }
@keyframes trkPulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,.4); }
    50%      { box-shadow: 0 0 0 5px rgba(22,163,74,0); }
}
.trk-step-body { flex: 1; min-width: 0; }
.trk-step-orden { font-weight: 700; color: #64748b; margin-right: .3rem; }
.trk-step-nombre {
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    display: block;
}
body.dark-mode .trk-step-nombre { color: #e2e8f0; }
.trk-step-dir { color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
.trk-step-badge {
    font-size: .65rem;
    padding: .15rem .45rem;
    border-radius: 999px;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: .15rem;
    align-self: flex-start;
}
.trk-badge-pendiente  { background: #f1f5f9; color: #64748b; }
.trk-badge-en_camino  { background: #dbeafe; color: #1d4ed8; }
.trk-badge-en_sitio   { background: #fef3c7; color: #92400e; }
.trk-badge-completado { background: #dcfce7; color: #15803d; }
.trk-badge-incidencia { background: #fee2e2; color: #b91c1c; }
.trk-location-pill {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    background: #f1f5f9;
    border-radius: 999px;
    padding: .2rem .6rem;
    font-size: .72rem;
    color: #475569;
    margin-top: .3rem;
}
body.dark-mode .trk-location-pill { background: #1e293b; color: #94a3b8; }

/* ── Resumen del modal ── */
.track-summary-chip {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    background: var(--track-color-light);
    color: var(--track-color-dark);
    border-radius: 1rem;
    padding: .2rem .6rem;
    font-size: .78rem;
    font-weight: 600;
    margin: .15rem;
}
body.dark-mode .track-summary-chip { background: #134e4a; color: #5eead4; }

/* ── Dark mode: modales + formularios ── */
body.dark-mode .modal-content {
    background-color: #161f1f;
    color: #e2e8f0;
    border-color: #334444;
}
body.dark-mode .modal-header { border-bottom-color: #334444; }
body.dark-mode .modal-footer {
    background-color: #161f1f;
    border-top-color: #334444;
}
body.dark-mode .modal-body { background-color: #161f1f; }
body.dark-mode .form-control,
body.dark-mode .form-select {
    background-color: #1e2d2c;
    color: #e2e8f0;
    border-color: #2d4444;
}
body.dark-mode .form-control::placeholder { color: #6b8080; }
body.dark-mode .form-control:disabled,
body.dark-mode .form-select:disabled {
    background-color: #111a1a;
    color: #52686a;
    border-color: #1e2d2c;
}
body.dark-mode .form-label { color: #b0cece; }
body.dark-mode .form-text  { color: #52686a; }
body.dark-mode .input-group-text {
    background-color: #1e2d2c;
    color: #b0cece;
    border-color: #2d4444;
}

/* ── Select2 / chosen override ── */
.select2-container .select2-selection--multiple {
    min-height: 38px;
    border-color: #ced4da !important;
}

/* ── Botón pin ubicación en fila de crédito ── */
.btn-pin-ubicacion {
    flex-shrink: 0;
}
.btn-pin-ubicacion.tiene-pin {
    color: var(--track-color-dark);
    border-color: var(--track-color);
    background: var(--track-color-light);
}

/* ════════════════════════════════════════════════════════
   Chat Operativo — Offcanvas lateral
════════════════════════════════════════════════════════ */
#offcanvasChat { width: 480px; max-width: 100vw; }
body.dark-mode #offcanvasChat {
    background: #161f1f;
    color: #e2e8f0;
    border-left-color: var(--track-border);
}

/* Tabs de id_detalle */
.chat-tabs-wrap {
    border-bottom: 1px solid var(--track-border);
    flex-shrink: 0;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: thin;
}
.chat-tabs-wrap::-webkit-scrollbar { height: 4px; }
.chat-tabs-wrap::-webkit-scrollbar-thumb { background: var(--track-border); border-radius: 2px; }
.chat-tabs-wrap ul { flex-wrap: nowrap; padding: .35rem .5rem 0; gap: .2rem; border-bottom: none; }
.chat-tab-link {
    font-size: .77rem;
    padding: .28rem .65rem;
    border-radius: .4rem .4rem 0 0;
    color: var(--track-color-dark) !important;
    border: 1px solid transparent;
    border-bottom: none;
    white-space: nowrap;
    position: relative;
    background: transparent;
    cursor: pointer;
}
.chat-tab-link:hover { background: var(--track-color-light); }
.chat-tab-link.active {
    background: var(--track-color) !important;
    color: #fff !important;
    border-color: var(--track-color) !important;
}
body.dark-mode .chat-tab-link       { color: var(--track-color) !important; }
body.dark-mode .chat-tab-link:hover { background: var(--track-color-light); }

/* Badges de estatus del chat */
.chat-status-badge {
    font-size: .62rem;
    padding: .08rem .32rem;
    border-radius: .75rem;
    vertical-align: middle;
    margin-left: .22rem;
}
.chat-status-activo   { background: #22c55e; color: #fff; }
.chat-status-bloqueado { background: #f59e0b; color: #000; }
.chat-status-cerrado  { background: #64748b; color: #fff; }
.chat-status-desconocido { background: #cbd5e1; color: #475569; }

/* Badge de mensajes no leídos */
.chat-unread-badge {
    position: absolute;
    top: .1rem; right: .08rem;
    background: #ef4444;
    color: #fff;
    font-size: .6rem;
    padding: .04rem .28rem;
    border-radius: 9999px;
    line-height: 1.4;
    min-width: 1.1rem;
    text-align: center;
}

/* Indicador WS en línea */
.chat-ws-dot {
    display: inline-block;
    width: .52rem; height: .52rem;
    border-radius: 50%;
    margin-left: .35rem;
    vertical-align: middle;
}
.chat-ws-on  { background: #22c55e; }
.chat-ws-off { background: #94a3b8; }

/* Pane / panel de un detalle */
.chat-pane {
    display: none;
    flex-direction: column;
    flex-grow: 1;
    overflow: hidden;
    position: relative;
}
.chat-pane.active { display: flex; }

/* Aviso de estatus (bloqueado / cerrado / sin conexión) */
.chat-status-notice {
    text-align: center;
    font-size: .79rem;
    padding: .45rem .85rem;
    flex-shrink: 0;
}
.chat-notice-bloqueado { background: #fefce8; color: #854d0e; border-bottom: 1px solid #fde68a; }
.chat-notice-cerrado   { background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0; }
.chat-notice-activo    { background: #f0fdf4; color: #15803d; border-bottom: 1px solid #bbf7d0; }
body.dark-mode .chat-notice-bloqueado { background: #2c1600; color: #fbbf24; border-color: #451f00; }
body.dark-mode .chat-notice-cerrado   { background: #161f1f; color: #64748b; border-color: #1e2d2c; }
body.dark-mode .chat-notice-activo    { background: #092716; color: #4ade80; border-color: #14532d; }

/* Área de mensajes */
.chat-messages-wrap {
    flex-grow: 1;
    overflow-y: auto;
    padding: .7rem .8rem;
    display: flex;
    flex-direction: column;
    gap: .38rem;
    scroll-behavior: smooth;
}
.chat-messages-wrap::-webkit-scrollbar { width: 5px; }
.chat-messages-wrap::-webkit-scrollbar-thumb { background: var(--track-border); border-radius: 3px; }
body.dark-mode .chat-messages-wrap::-webkit-scrollbar-thumb { background: #2d4444; }

/* Burbujas de mensajes */
.chat-bubble-wrap { display: flex; flex-direction: column; max-width: 82%; }
.chat-bubble-wrap.dir-out { align-items: flex-end;   margin-left: auto; }
.chat-bubble-wrap.dir-in  { align-items: flex-start; margin-right: auto; }
.chat-bubble {
    border-radius: .875rem;
    padding: .38rem .72rem;
    font-size: .82rem;
    line-height: 1.45;
    word-break: break-word;
    max-width: 100%;
}
.dir-out.role-gestor   .chat-bubble { background: #16a34a; color: #fff; border-bottom-right-radius: .2rem; }
.dir-out.role-conductor .chat-bubble { background: var(--track-color); color: #fff; border-bottom-right-radius: .2rem; }
.dir-in  .chat-bubble { background: #f1f5f9; color: #1e293b; border-bottom-left-radius: .2rem; }
body.dark-mode .dir-in .chat-bubble { background: #1e2d2c; color: #e2e8f0; }
.chat-bubble-meta { font-size: .67rem; color: #94a3b8; margin-top: .1rem; }

/* Mensaje de sistema */
.chat-sys-msg {
    text-align: center;
    font-size: .77rem;
    color: #64748b;
    background: #f1f5f9;
    border-radius: 1rem;
    padding: .22rem .7rem;
    margin: .15rem auto;
    max-width: 90%;
}
body.dark-mode .chat-sys-msg { background: #1e2d2c; color: #9db0b0; }

/* Botón "Nuevo mensaje ↓" flotante */
.chat-new-msg-btn {
    position: absolute;
    bottom: 68px; left: 50%;
    transform: translateX(-50%);
    background: var(--track-color);
    color: #fff;
    border: none;
    border-radius: 9999px;
    padding: .28rem .85rem;
    font-size: .77rem;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,.2);
    z-index: 5;
    white-space: nowrap;
}
.chat-new-msg-btn:hover { background: var(--track-color-dark); }

/* Área de input */
.chat-input-area {
    border-top: 1px solid var(--track-border);
    padding: .6rem .8rem;
    flex-shrink: 0;
    background: #fff;
}
body.dark-mode .chat-input-area { background: #161f1f; border-top-color: var(--track-border); }
.chat-textarea {
    resize: none;
    font-size: .82rem;
    border-color: var(--track-border);
    border-radius: .5rem;
    flex-grow: 1;
    line-height: 1.4;
}
body.dark-mode .chat-textarea {
    background: #1e2d2c;
    color: #e2e8f0;
    border-color: #2d4444;
}
.chat-textarea:focus { border-color: var(--track-color); box-shadow: 0 0 0 .15rem rgba(13,148,136,.2); }
.chat-send-btn {
    background: var(--track-color);
    color: #fff;
    border: none;
    border-radius: .5rem;
    flex-shrink: 0;
    width: 42px; height: 54px;
    padding: 0;
    font-size: .88rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
}
.chat-send-btn:hover:not(:disabled) { background: var(--track-color-dark); color: #fff; }
.chat-send-btn:disabled { background: #cbd5e1; cursor: not-allowed; }
body.dark-mode .chat-send-btn:disabled { background: #2d4444; }

/* Offcanvas body: flex column, sin overflow interno */
#offcanvasChat .offcanvas-body {
    padding: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
}
</style>

<div class="container-fluid py-3 px-3 px-md-4">

    <!-- ── Cabecera ── -->
    <div class="track-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="fa-solid fa-route me-2"></i>Tracking Recolección — Motos Adjudicadas</h4>
            <div class="track-subtitle">Créditos disponibles para planeación de ruta física</div>
        </div>
        <button class="btn btn-light fw-semibold" id="btnNuevaRuta">
            <i class="fa-solid fa-plus me-1"></i>Registrar ruta
        </button>
    </div>

    <!-- ── Pestañas principales ── -->
    <ul class="nav nav-tabs track-tabs mb-3" id="trackMainTabs">
        <li class="nav-item">
            <button class="nav-link active" id="tabCreditosBtn" data-bs-toggle="tab" data-bs-target="#tabCreditos">
                <i class="fa-solid fa-motorcycle me-1"></i>Créditos disponibles
                <span id="badgeCreditos" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tabBorradorBtn" data-bs-toggle="tab" data-bs-target="#tabBorradores">
                <i class="fa-solid fa-file-pen me-1"></i>Borradores
                <span id="badgeBorradores" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tabRutasBtn" data-bs-toggle="tab" data-bs-target="#tabRutas">
                <i class="fa-solid fa-map-marked-alt me-1"></i>Rutas registradas
                <span id="badgeRutas" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- ══ Tab: Créditos disponibles ══ -->
        <div class="tab-pane fade show active" id="tabCreditos">

            <!-- Filtros -->
            <div class="track-filters">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm" id="filtroEstado">
                            <option value="">— Todos los estados —</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Municipio</label>
                        <select class="form-select form-select-sm" id="filtroMunicipio" disabled>
                            <option value="">— Todos los municipios —</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-2">
                        <button class="btn btn-sm w-100" id="btnFiltrarCreditos"
                                style="background:var(--track-color);color:#fff;">
                            <i class="fa-solid fa-search me-1"></i>Filtrar
                        </button>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" id="btnLimpiarFiltros">
                            <i class="fa-solid fa-eraser me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de créditos -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaCreditos" class="table table-hover table-bordered mb-0 w-100" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>ID Crédito</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Municipio</th>
                                    <th>Modelo</th>
                                    <th>BIN / NIV</th>
                                    <th>Estatus Proceso</th>
                                    <th>Confirmación Gestor</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ Tab: Borradores ══ -->
        <div class="tab-pane fade" id="tabBorradores">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaBorradores" class="table table-hover table-bordered mb-0 w-100" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre de ruta</th>
                                    <th>Estado / Municipio</th>
                                    <th>Fecha programada</th>
                                    <th>Hora</th>
                                    <th>Créditos</th>
                                    <th>Responsables</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ Tab: Rutas registradas ══ -->
        <div class="tab-pane fade" id="tabRutas">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaRutas" class="table table-hover table-bordered mb-0 w-100" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre de ruta</th>
                                    <th>Estado / Municipio</th>
                                    <th>Fecha programada</th>
                                    <th>Hora</th>
                                    <th>Estatus</th>
                                    <th>Créditos</th>
                                    <th>Responsables</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /tab-content -->

</div><!-- /container-fluid -->

<!-- ══════════════════════════════════════════════════════════
     Modal — Registrar / editar ruta
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalRegistrarRuta" tabindex="-1" aria-labelledby="modalRegistrarRutaLabel"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalRegistrarRutaLabel">
                    <i class="fa-solid fa-route me-2"></i>Registrar ruta de recolección
                </h5>
                <button type="button" class="btn-close" id="btnCerrarModal"></button>
            </div>

            <div class="modal-body">

                <!-- ── Sección 1: Datos de la ruta ── -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">
                            Nombre de ruta <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm"
                               id="rutaNombre" maxlength="100" placeholder="Ej. Ruta GDL Norte Junio">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">
                            Fecha programada <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm"
                               id="rutaFecha" min="">
                        <div class="form-text text-muted" style="font-size:.72rem;">
                            Mínimo 2 días desde hoy  - Deja una fecha tentativa si aún no está definida para que puedas guardar correctamente el borrador de la ruta.
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">
                            Hora de salida
                        </label>
                        <div class="d-flex gap-1 align-items-center">
                            <select class="form-select form-select-sm" id="rutaHoraH" style="width:80px;flex-shrink:0;">
                                <?php for ($h = 1; $h <= 12; $h++): ?>
                                <option value="<?= $h ?>"><?= $h ?></option>
                                <?php endfor; ?>
                            </select>
                            <input type="text" class="form-control form-control-sm text-center fw-semibold"
                                   id="rutaHoraM" inputmode="numeric" maxlength="2"
                                   placeholder="00" autocomplete="off"
                                   style="width:80px;flex-shrink:0;letter-spacing:.05em;">
                            <select class="form-select form-select-sm" id="rutaHoraAmPm" style="width:80px;flex-shrink:0;">
                                <option value="AM">AM</option>
                                <option value="PM">PM</option>
                            </select>
                        </div>
                        <div id="rutaHoraActInfo" class="mt-1 d-none" style="font-size:.72rem;"></div>
                    </div>
                </div>

                <!-- ── Sección 2: Usuarios responsables ── -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Usuarios responsables de recolección
                        <span class="text-muted">(requerido para enviar)</span>
                    </label>
                    <select class="form-select form-select-sm" id="rutaUsuarios" multiple="multiple">
                    </select>
                </div>

                <!-- ── Sección 3: Agregar créditos ── -->
                <div class="mb-2" id="secAgregarCredito">
                    <label class="form-label small fw-semibold">
                        Agregar crédito a la ruta
                    </label>
                    <!-- Filtros de ubicación para créditos -->
                    <div class="row g-2 mb-2" id="crdFiltrosUbicacion">
                        <div class="col-12 col-md-4">
                            <select class="form-select form-select-sm" id="crdFiltroEstado">
                                <option value="">— Todos los estados —</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <select class="form-select form-select-sm" id="crdFiltroMunicipio" disabled>
                                <option value="">— Todos los municipios —</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group input-group-sm">
                        <select class="form-select" id="rutaCreditoSelect">
                            <option value="">— Buscar crédito (ID · Modelo · BIN) —</option>
                        </select>
                        <button class="btn" id="btnAgregarCredito"
                                style="background:var(--track-color);color:#fff;">
                            <i class="fa-solid fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                </div>

                <!-- ── Lista de créditos en la ruta (sortable) ── -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold text-muted">
                            Créditos en la ruta
                            (<span id="rutaCreditosCount">0</span>)
                        </span>
                        <span class="small text-muted" id="reorderHint">
                            <i class="fa-solid fa-arrows-up-down me-1"></i>
                            Arrastra para reordenar
                        </span>
                    </div>
                    <div id="rutaCreditosList" style="max-height:280px;overflow-y:auto;border:1px dashed var(--track-border);border-radius:.5rem;padding:.5rem;">
                        <div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
                            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
                            Aún no hay créditos en esta ruta
                        </div>
                    </div>
                </div>

                <!-- ── Sección 3.5: Tracking en tiempo real ── -->
                <div id="trkTrackingSection" class="mb-3 d-none">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">
                            <i class="fa-solid fa-route me-1" style="color:var(--track-color);"></i>
                            Estado del recorrido
                        </span>
                        <span id="trkWsDot" title="Sin conexión en tiempo real"
                              style="width:.55rem;height:.55rem;border-radius:50%;background:#cbd5e1;display:inline-block;"></span>
                    </div>
                    <!-- Barra de progreso -->
                    <div class="progress mb-1" style="height:5px;border-radius:999px;">
                        <div class="progress-bar" id="trkProgressBar"
                             style="width:0%;background:var(--track-color);transition:width .4s;"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span id="trkProgressText">— / — puntos</span>
                        <span id="trkPorcentaje">0%</span>
                    </div>
                    <!-- Última ubicación del conductor -->
                    <div id="trkUltimaUbicacion" class="trk-location-pill d-none mb-2">
                        <i class="fa-solid fa-location-arrow" style="color:var(--track-color);"></i>
                        <span id="trkUbicacionText">—</span>
                        <span class="text-muted" id="trkUbicacionTime"></span>
                    </div>
                    <!-- Timeline de paradas -->
                    <div class="trk-timeline" id="trkTimeline">
                        <div class="text-center text-muted py-2 small" id="trkTimelineEmpty">
                            <span class="spinner-border spinner-border-sm opacity-25" style="color:var(--track-color);"></span>
                        </div>
                    </div>
                </div>

                <!-- ── Sección 4: Mapa de la ruta ── -->
                <div class="mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">
                            <i class="fa-solid fa-map-location-dot me-1" style="color:var(--track-color);"></i>
                            Mapa de la ruta
                        </span>
                        <button class="btn btn-sm btn-outline-secondary" id="btnRefreshMap" style="font-size:.75rem;">
                            <i class="fa-solid fa-refresh me-1"></i>Actualizar mapa
                        </button>
                    </div>
                    <div id="trackMapContainer">
                        <div class="map-placeholder" id="mapPlaceholder">
                            <i class="fa-solid fa-map fa-2x opacity-30"></i>
                            <span>Agrega créditos para visualizar la ruta</span>
                        </div>
                        <div id="trackMap" style="display:none;"></div>
                    </div>
                    <div class="alert alert-warning py-1 px-2 mt-1 small d-none" id="mapAlertCoords">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Algunos créditos no tienen coordenadas ni dirección registrada.
                        La ruta en el mapa puede estar incompleta.
                    </div>
                </div>

            </div><!-- /modal-body -->

            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCerrarModalFooter">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm" id="btnActualizarRuta"
                            style="background:#0d6efd;color:#fff;display:none;">
                        <i class="fa-solid fa-arrows-rotate me-1"></i>Actualizar ruta
                    </button>
                    <button type="button" class="btn btn-sm" id="btnGuardarBorrador"
                            style="background:#94a3b8;color:#fff;">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar borrador
                    </button>
                    <button type="button" class="btn btn-sm" id="btnEnviarRuta"
                            style="background:var(--track-color);color:#fff;">
                        <i class="fa-solid fa-paper-plane me-1"></i>Enviar ruta
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     Modal — Detalle de ruta (solo lectura)
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetalleRuta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--track-color-dark);color:#fff;">
                <h6 class="modal-title">
                    <i class="fa-solid fa-map-marked-alt me-2"></i>
                    Detalle de ruta
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body" id="detalleRutaBody">
                <div class="text-center py-4">
                    <div class="spinner-border" style="color:var(--track-color);"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     Modal — Seleccionar ubicación en mapa (map picker)
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalMapPicker" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:var(--track-color-dark);color:#fff;">
                <h6 class="modal-title mb-0">
                    <i class="fa-solid fa-map-pin me-2"></i>
                    Seleccionar ubicación en el mapa
                </h6>
                <button type="button" class="btn-close" id="btnCerrarMapPicker" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body p-2">
                <p class="small text-muted mb-2 px-1">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Haz clic en el mapa para colocar el pin de la ubicación del crédito
                    <strong id="mapPickerCreditoLabel"></strong>.
                </p>
                <div id="mapPickerContainer" style="width:100%;height:420px;border-radius:.5rem;overflow:hidden;border:1px solid var(--track-border);"></div>
                <div class="mt-2 px-1 d-flex align-items-center gap-2 flex-wrap">
                    <span class="small text-muted" id="mapPickerCoordsLabel">
                        <i class="fa-solid fa-crosshairs me-1"></i>Sin selección
                    </span>
                </div>
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCancelarMapPicker">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm" id="btnConfirmarMapPicker"
                        style="background:var(--track-color);color:#fff;" disabled>
                    <i class="fa-solid fa-check me-1"></i>Confirmar ubicación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     Offcanvas — Chat Operativo (gestor / Sparta Ledger)
     Se abre desde el botón de chat en la tabla de rutas.
     Una pestaña por cada id_detalle (punto de recolección).
══════════════════════════════════════════════════════════ -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasChat"
     aria-labelledby="offcanvasChatLabel">
    <div class="offcanvas-header py-2 px-3" style="background:var(--track-color-dark);color:#fff;flex-shrink:0;">
        <div style="min-width:0;">
            <h6 class="offcanvas-title mb-0" id="offcanvasChatLabel">
                <i class="fa-solid fa-comments me-2"></i>Chat Operativo
            </h6>
            <small id="chatRutaNombre" class="opacity-75" style="font-size:.75rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:320px;"></small>
        </div>
        <button type="button" class="btn-close ms-2" data-bs-dismiss="offcanvas"
                aria-label="Cerrar" style="filter:invert(1);flex-shrink:0;"></button>
    </div>

    <div class="offcanvas-body">
        <!-- Tabs: una por id_detalle -->
        <div class="chat-tabs-wrap" id="chatTabsWrap" style="display:none;">
            <ul class="nav d-flex" id="chatTabList" role="tablist"></ul>
        </div>

        <!-- Panes: uno por id_detalle -->
        <div id="chatPanesContainer" class="flex-grow-1 d-flex flex-column" style="overflow:hidden;"></div>

        <!-- Placeholder cuando no hay items -->
        <div id="chatEmptyPlaceholder" class="flex-grow-1 d-flex align-items-center justify-content-center text-center p-4"
             style="color:#94a3b8;">
            <div>
                <i class="fa-solid fa-comments fa-2x mb-2 opacity-25 d-block"></i>
                <span class="small">No hay puntos de recolección disponibles</span>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     Google Maps API
══════════════════════════════════════════════════════════ -->
<?php if (!empty($google_maps_api_key_js)) : ?>
<script>
    window._trackGoogleMapsKey = <?= json_encode((string) $google_maps_api_key_js) ?>;
</script>
<?php else : ?>
<script>window._trackGoogleMapsKey = null;</script>
<?php endif; ?>

<script>
/* Chat Operativo — URL WebSocket (sin credenciales, solo el host) */
window._trackingChatWsBaseUrl   = <?= json_encode((string)($tracking_chat_ws_base_url ?? '')) ?>;
window._trackingChatGestorNombre = <?= json_encode(trim((string)($_SESSION['usuario_nombre'] ?? 'Gestor'))) ?>;
</script>

<!-- SortableJS (drag-and-drop sin jQuery UI) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
/* ═══════════════════════════════════════════════════════
   tracking_recoleccion.js — lógica del módulo
═══════════════════════════════════════════════════════ */
'use strict';

// ─── Estado local ───────────────────────────────────────
const _trk = {
    creditosDisponibles:  [],   // todos los créditos disponibles del servidor
    creditosEnRuta:       [],   // créditos actualmente en el modal
    usuariosDisponibles:  [],   // personas activas
    usuariosSeleccionados:[],   // ids seleccionados
    idRutaEditando:       null, // null = nueva ruta
    estatusRuta:          null, // estatus_ruta de la ruta cargada (null = nueva)
    soloLectura:          false,// modal en modo vista bloqueada
    cargando:             false, // cargando ruta existente (evita haychangios)
    haycambios:           false,
    tablaCreditosDT:      null,
    tablaRutasDT:         null,
    tablaRutasBorradorDT: null,
    sortableInstance:     null,
    mapInstance:          null,
    mapLoaded:            false,
    geocoder:             null,
    mapMarkers:           [],   // marcadores activos en el mapa
    directionsRenderer:   null, // renderer de ruta activo
};

// ─── Utilidades ─────────────────────────────────────────
const trkFetch = (url, opts = {}) =>
    fetch(url, { credentials: 'same-origin', ...opts })
        .then(r => r.json());



const trkConfirm = (msg) => new Promise(resolve => {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Salir sin guardar?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Quedarme',
            confirmButtonColor: '#ef4444',
        }).then(r => resolve(r.isConfirmed));
    } else {
        resolve(confirm(msg));
    }
});

// Mapeo estatus
const CONF_LABEL = {
    pendiente:   '<span class="badge badge-conf-pendiente">Pendiente</span>',
    confirmado:  '<span class="badge badge-conf-confirmado">Confirmado</span>',
    rechazado:   '<span class="badge badge-conf-rechazado">Rechazado</span>',
    en_revision: '<span class="badge badge-conf-en_revision">En revisión</span>',
};
const RUTA_LABEL = {
    borrador:               '<span class="badge badge-ruta-borrador">Borrador</span>',
    pendiente_confirmacion: '<span class="badge badge-ruta-pendiente_confirmacion">Pend. confirmación</span>',
    lista_envio:            '<span class="badge badge-ruta-lista_envio">Lista para enviar</span>',
    enviada:                '<span class="badge badge-ruta-enviada">Enviada</span>',
    en_proceso:             '<span class="badge badge-ruta-en_proceso">En proceso</span>',
    concluida:              '<span class="badge badge-ruta-concluida">Concluida</span>',
    cancelada:              '<span class="badge badge-ruta-cancelada">Cancelada</span>',
};

// ─── Inicialización ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    _trkInicializarFiltros();
    _trkInicializarTablaCreditosDT();
    _trkInicializarTablaRutasDT();
    _trkInicializarTablaBorradorDT();
    _trkInicializarModal();

    // Observar cambios de clase en body para actualizar estilos del mapa
    new MutationObserver(() => {
        if (!_trk.mapInstance) return;
        const isDark = document.body.classList.contains('dark-mode');
        _trk.mapInstance.setOptions({ styles: isDark ? _TRK_DARK_MAP_STYLES : [] });
        // El picker tiene su propio mapa
        if (_trkPicker.mapInstance) {
            _trkPicker.mapInstance.setOptions({ styles: isDark ? _TRK_DARK_MAP_STYLES : [] });
        }
    }).observe(document.body, { attributeFilter: ['class'] });

    document.getElementById('tabRutasBtn').addEventListener('click', () => _trkCargarRutas());
    document.getElementById('tabBorradorBtn').addEventListener('click', () => _trkCargarBorradores());
    document.getElementById('btnNuevaRuta').addEventListener('click', () => _trkAbrirModalNuevo());

    // Validación estricta del input de minutos
    const $horaM = document.getElementById('rutaHoraM');
    $horaM.addEventListener('keydown', function (e) {
        // Permitir: backspace, delete, tab, escape, flechas, home, end
        const allowed = ['Backspace','Delete','Tab','Escape','ArrowLeft','ArrowRight','Home','End'];
        if (allowed.includes(e.key)) return;
        // Bloquear todo excepto dígitos 0-9
        if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
        }
    });
    $horaM.addEventListener('input', function () {
        // Eliminar cualquier carácter que no sea dígito (copia/pega, etc.)
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
    });
    $horaM.addEventListener('blur', function () {
        const raw = this.value.replace(/[^0-9]/g, '');
        if (raw === '') {
            this.value = '00';
            return;
        }
        const n = parseInt(raw, 10);
        if (isNaN(n) || n > 59) {
            this.value = '00';
            this.classList.add('is-invalid');
            setTimeout(() => this.classList.remove('is-invalid'), 1500);
            if (n === 69 || n === 67 || n === 91) {
                Swal.fire({
                    icon: 'error',
                    title: 'Minutos incorrectos',
                    text: `"${n}" no es válido. Deben ser entre 00 y 59.`,
                    footer: 'Que gracioso...',
                    confirmButtonText: 'Aceptar',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Minutos incorrectos',
                    text: `"${n}" no es válido. Deben ser entre 00 y 59.`,
                    confirmButtonText: 'Aceptar',
                });
            }
        } else {
            this.value = String(n).padStart(2, '0');
            this.classList.remove('is-invalid');
        }
    });

    // Carga inicial de todas las pestañas en paralelo
    _trkCargarTodo();
});

// ─── Filtros ─────────────────────────────────────────────
function _trkInicializarFiltros() {
    $('#filtroEstado').on('change', function () {
        const est = $(this).val();
        const $mun = $('#filtroMunicipio');
        $mun.html('<option value="">— Cargando… —</option>').prop('disabled', true);
        if (!est) {
            $mun.html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
            return;
        }
        trkFetch(`/TrackingRecoleccion/obtenerMunicipios?estado=${encodeURIComponent(est)}`)
            .then(r => {
                $mun.html('<option value="">— Todos los municipios —</option>');
                (r.datos || []).forEach(m => {
                    $mun.append(`<option value="${m}">${m}</option>`);
                });
                $mun.prop('disabled', false);
            })
            .catch(() => $mun.html('<option value="">— Error —</option>'));
    });

    $('#btnFiltrarCreditos').on('click', function () {
        _trkCargarCreditosPaso2();
    });

    $('#btnLimpiarFiltros').on('click', function () {
        $('#filtroEstado').val('');
        $('#filtroMunicipio').html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
        _trkCargarCreditosPaso2();
    });
}

function _trkCargarEstados() {
    return trkFetch('/TrackingRecoleccion/obtenerEstados')
        .then(r => {
            const estados = r.datos || [];
            const $selFiltro = $('#filtroEstado');
            estados.forEach(e => $selFiltro.append(`<option value="${e}">${e}</option>`));
        });
}

// ─── Tabla de créditos ──────────────────────────────────
function _trkInicializarTablaCreditosDT() {
    _trk.tablaCreditosDT = $('#tablaCreditos').DataTable({
        language: {
            emptyTable:  'No hay créditos disponibles',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 25,
        responsive: true,
        columns: [
            { data: 'id_credito' },
            { data: 'nombre_cliente' },
            { data: 'estado',    defaultContent: '—' },
            { data: 'municipio', defaultContent: '—' },
            {
                data: null,
                render: r => [r.moto_marca, r.moto_modelo].filter(Boolean).join(' ') || '—',
            },
            { data: 'bin', defaultContent: '—' },
            {
                data: 'estatus_proceso',
                defaultContent: '—',
                render: v => v ? v.replace(/_/g, ' ') : '—',
            },
            {
                data: null,
                render: r => CONF_LABEL['pendiente'],   // siempre pendiente en esta tabla
            },
            {
                data: null,
                orderable: false,
                render: r => `<button class="btn btn-sm btn-outline-success py-0 px-1 btn-agregar-a-ruta"
                                  data-id="${r.id_credito}"
                                  title="Agregar a ruta">
                                  <i class="fa-solid fa-plus"></i>
                              </button>`,
            },
        ],
    });

    $('#tablaCreditos').on('click', '.btn-agregar-a-ruta', function () {
        const idCred = $(this).data('id');
        const cred   = _trk.creditosDisponibles.find(c => String(c.id_credito) === String(idCred));
        if (!cred) return;
        _trkAbrirModalConCredito(cred);
    });
}

function _trkCargarCreditosPaso2() {
    const estado    = $('#filtroEstado').val();
    const municipio = $('#filtroMunicipio').val();
    let url = '/TrackingRecoleccion/obtenerCreditosPaso2';
    const params = [];
    if (estado)    params.push(`estado=${encodeURIComponent(estado)}`);
    if (municipio) params.push(`municipio=${encodeURIComponent(municipio)}`);
    if (params.length) url += '?' + params.join('&');

    return trkFetch(url)
        .then(r => {
            _trk.creditosDisponibles = r.datos || [];
            // TODO (pendiente autorización): descomentar para filtrar solo créditos listos para ruta
            // _trk.creditosDisponibles = _trkFiltrarListosParaRuta(_trk.creditosDisponibles);
            if (_trk.tablaCreditosDT) {
                _trk.tablaCreditosDT.clear().rows.add(_trk.creditosDisponibles).draw();
            }
            _trkPoblarFiltroEstadosCrd();
            _trkRefrescarSelectCreditos();
            const badge = document.getElementById('badgeCreditos');
            if (badge) badge.textContent = String(_trk.creditosDisponibles.length);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar créditos.', confirmButtonText: 'Aceptar' }));
}

// ─── Filtro: solo créditos con estatus "Cierre Documentados" ─────────────
// Pendiente de autorización — para activar: descomentar la línea en _trkCargarCreditosPaso2
// Una vez activo, el estatus se mostrará en tabla como "Listo para ruta" en lugar de "Cierre Documentados"
// function _trkFiltrarListosParaRuta(creditos) {
//     return creditos
//         .filter(c => c.estatus_proceso === 'Cierre Documentados')
//         .map(c => ({ ...c, estatus_proceso: 'Listo para ruta' }));
// }

// ─── Tabla de rutas ─────────────────────────────────────
function _trkRenderUbicacionRuta(raw) {
    if (!raw) return '—';
    const map = new Map();
    raw.split('@@').forEach(p => {
        const sep  = p.indexOf('|||');
        const est  = sep >= 0 ? p.slice(0, sep).trim()  : '';
        const mun  = sep >= 0 ? p.slice(sep + 3).trim() : '';
        if (!est) return;
        if (!map.has(est)) map.set(est, new Set());
        if (mun && mun !== '|') map.get(est).add(mun);
    });
    if (!map.size) return '—';
    return [...map.entries()]
        .map(([est, munis]) => {
            const munStr = [...munis].filter(Boolean).join(', ');
            return munStr ? `${est} / ${munStr}` : est;
        })
        .join('<br>');
}

function _trkInicializarTablaRutasDT() {
    _trk.tablaRutasDT = $('#tablaRutas').DataTable({
        language: {
            emptyTable:  'No hay rutas registradas',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 20,
        responsive: true,
        columns: [
            { data: 'id_ruta' },
            { data: 'nombre_ruta' },
            {
                data: null,
                render: r => _trkRenderUbicacionRuta(r.ubicaciones_lista),
            },
            { data: 'fecha_programada_fmt', defaultContent: '—' },
            {
                data: null,
                title: 'Hora',
                render: r => {
                    const hi  = r.hora_inicial;
                    const ha1 = r.act_hora_1;
                    if (!hi && !ha1) return '—';
                    if (ha1) {
                        return `<div class="d-flex flex-column gap-1">
                            <span class="badge bg-warning text-dark" title="Hora actualizada">${_trkFormatHora(ha1)}</span>
                            <small class="text-muted text-decoration-line-through" title="Hora original">${_trkFormatHora(hi)}</small>
                        </div>`;
                    }
                    return `<span class="badge bg-light text-dark border">${_trkFormatHora(hi)}</span>`;
                },
            },
            {
                data: 'estatus_ruta',
                render: v => RUTA_LABEL[v] || `<span class="badge bg-secondary">${v}</span>`,
            },
            {
                data: null,
                render: r => {
                    const total = parseInt(r.total_creditos) || 0;
                    const conf  = parseInt(r.confirmados)    || 0;
                    const pend  = parseInt(r.pendientes)     || 0;
                    const rech  = parseInt(r.rechazados)     || 0;
                    const lista = (r.creditos_lista || '').split('||').filter(Boolean).join('<br>');
                    const ttAttr = lista ? ` data-bs-toggle="tooltip" data-bs-placement="right" data-bs-html="true" data-bs-title="${lista}"` : '';
                    let html = `<div class="d-flex flex-column gap-1 align-items-start">`;
                    html += `<span class="badge bg-secondary trk-cred-badge"${ttAttr} style="cursor:default;white-space:nowrap;">${total} crédito${total !== 1 ? 's' : ''}</span>`;
                    if (conf > 0) html += `<small class="text-success fw-semibold" style="white-space:nowrap;">${conf} confirmado${conf !== 1 ? 's' : ''}</small>`;
                    if (pend > 0) html += `<small class="text-warning fw-semibold" style="white-space:nowrap;">${pend} pendiente${pend !== 1 ? 's' : ''}</small>`;
                    if (rech > 0) html += `<small class="text-danger  fw-semibold" style="white-space:nowrap;">${rech} rechazado${rech !== 1 ? 's' : ''}</small>`;
                    html += '</div>';
                    return html;
                },
            },
            {
                data: 'usuarios_responsables',
                defaultContent: '—',
                render: v => {
                    if (!v) return '—';
                    return v.split(',').map(n => `<span style="display:block;white-space:nowrap;">${n.trim()}</span>`).join('');
                },
            },
            {
                data: null,
                orderable: false,
                render: r => r.estatus_ruta === 'borrador'
                    ? `<button class="btn btn-sm btn-outline-warning py-0 px-2 btn-editar-ruta"
                           data-id="${r.id_ruta}" title="Editar ruta (borrador)">
                           <i class="fa-solid fa-pen-to-square"></i>
                       </button>`
                    : `<div class="d-flex gap-1">
                           <button class="btn btn-sm btn-outline-primary py-0 px-2 btn-ver-ruta"
                               data-id="${r.id_ruta}" title="Ver detalle">
                               <i class="fa-solid fa-eye"></i>
                           </button>
                           <button class="btn btn-sm btn-outline-success py-0 px-2 btn-abrir-chat"
                               data-id="${r.id_ruta}" title="Chat operativo">
                               <i class="fa-solid fa-comments"></i>
                           </button>
                       </div>`,
            },
        ],
        drawCallback: function () {
            document.querySelectorAll('#tablaRutas [data-bs-toggle="tooltip"]').forEach(el => {
                bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover', html: true });
            });
        },
    });

    $('#tablaRutas').on('click', '.btn-editar-ruta', function () {
        _trkCargarRutaEnModal($(this).data('id'), false);
    });
    $('#tablaRutas').on('click', '.btn-ver-ruta', function () {
        _trkCargarRutaEnModal($(this).data('id'), true);
    });
    $('#tablaRutas').on('click', '.btn-abrir-chat', function () {
        _trkChatCargarYAbrir(Number($(this).data('id')));
    });
}

function _trkCargarRutas() {
    return trkFetch('/TrackingRecoleccion/obtenerRutas', { method: 'POST' })
        .then(r => {
            const rutas = r.datos || [];
            if (_trk.tablaRutasDT) {
                _trk.tablaRutasDT.clear().rows.add(rutas).draw();
            }
            const badge = document.getElementById('badgeRutas');
            if (badge) badge.textContent = String(rutas.length);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar rutas.', confirmButtonText: 'Aceptar' }));
}

// ─── Tabla de borradores ─────────────────────────────────────
function _trkInicializarTablaBorradorDT() {
    _trk.tablaRutasBorradorDT = $('#tablaBorradores').DataTable({
        language: {
            emptyTable:  'No hay rutas en borrador',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 20,
        responsive: true,
        columns: [
            { data: 'id_ruta' },
            { data: 'nombre_ruta', defaultContent: '—' },
            {
                data: null,
                render: r => _trkRenderUbicacionRuta(r.ubicaciones_lista),
            },
            { data: 'fecha_programada_fmt', defaultContent: '—' },
            {
                data: null,
                title: 'Hora',
                render: r => {
                    const hi  = r.hora_inicial;
                    const ha1 = r.act_hora_1;
                    if (!hi && !ha1) return '—';
                    if (ha1) {
                        return `<div class="d-flex flex-column gap-1">
                            <span class="badge bg-warning text-dark" title="Hora actualizada">${_trkFormatHora(ha1)}</span>
                            <small class="text-muted text-decoration-line-through" title="Hora original">${_trkFormatHora(hi)}</small>
                        </div>`;
                    }
                    return `<span class="badge bg-light text-dark border">${_trkFormatHora(hi)}</span>`;
                },
            },
            {
                data: null,
                render: r => {
                    const total = parseInt(r.total_creditos) || 0;
                    const conf  = parseInt(r.confirmados)    || 0;
                    const pend  = parseInt(r.pendientes)     || 0;
                    const rech  = parseInt(r.rechazados)     || 0;
                    const lista = (r.creditos_lista || '').split('||').filter(Boolean).join('<br>');
                    const ttAttr = lista ? ` data-bs-toggle="tooltip" data-bs-placement="right" data-bs-html="true" data-bs-title="${lista}"` : '';
                    let html = `<div class="d-flex flex-column gap-1 align-items-start">`;
                    html += `<span class="badge bg-secondary trk-cred-badge"${ttAttr} style="cursor:default;white-space:nowrap;">${total} crédito${total !== 1 ? 's' : ''}</span>`;
                    if (conf > 0) html += `<small class="text-success fw-semibold" style="white-space:nowrap;">${conf} confirmado${conf !== 1 ? 's' : ''}</small>`;
                    if (pend > 0) html += `<small class="text-warning fw-semibold" style="white-space:nowrap;">${pend} pendiente${pend !== 1 ? 's' : ''}</small>`;
                    if (rech > 0) html += `<small class="text-danger  fw-semibold" style="white-space:nowrap;">${rech} rechazado${rech !== 1 ? 's' : ''}</small>`;
                    html += '</div>';
                    return html;
                },
            },
            {
                data: 'usuarios_responsables',
                defaultContent: '—',
                render: v => {
                    if (!v) return '—';
                    return v.split(',').map(n => `<span style="display:block;white-space:nowrap;">${n.trim()}</span>`).join('');
                },
            },
            {
                data: null,
                orderable: false,
                render: r => `<button class="btn btn-sm btn-outline-warning py-0 px-2 btn-editar-borrador"
                                   data-id="${r.id_ruta}" title="Editar borrador">
                                   <i class="fa-solid fa-pen-to-square"></i>
                               </button>`,
            },
        ],
        drawCallback: function () {
            document.querySelectorAll('#tablaBorradores [data-bs-toggle="tooltip"]').forEach(el => {
                bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover', html: true });
            });
        },
    });

    $('#tablaBorradores').on('click', '.btn-editar-borrador', function () {
        _trkCargarRutaEnModal($(this).data('id'), false);
    });
}

function _trkCargarBorradores() {
    return trkFetch('/TrackingRecoleccion/obtenerBorradores')
        .then(r => {
            const borradores = r.datos || [];
            if (_trk.tablaRutasBorradorDT) {
                _trk.tablaRutasBorradorDT.clear().rows.add(borradores).draw();
            }
            // Actualizar contador en la pestaña
            const $badge = document.getElementById('badgeBorradores');
            if ($badge) $badge.textContent = borradores.length > 0 ? borradores.length : '0';
        })
        .catch(() => {});
}

// ─── Usuarios ────────────────────────────────────────────
function _trkCargarUsuarios() {
    return trkFetch('/TrackingRecoleccion/obtenerUsuariosRecoleccion')
        .then(r => {
            _trk.usuariosDisponibles = r.datos || [];
            const $sel = $('#rutaUsuarios');
            _trk.usuariosDisponibles.forEach(u => {
                $sel.append(`<option value="${u.id}">${u.nombre}</option>`);
            });
        });
}

// ─── Carga inicial de todos los datos en paralelo ─────────
function _trkCargarTodo() {
    Swal.fire({
        title: 'Obteniendo datos...',
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando información del módulo</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    Promise.all([
        _trkCargarEstados().catch(() => {}),
        _trkCargarUsuarios().catch(() => {}),
        _trkCargarCreditosPaso2().catch(() => {}),
        _trkCargarBorradores().catch(() => {}),
        _trkCargarRutas().catch(() => {}),
    ]).then(() => Swal.close());
}

// ─── Modal — apertura ────────────────────────────────────
function _trkInicializarModal() {
    // Fecha mínima
    const minDate = (() => {
        const d = new Date();
        d.setDate(d.getDate() + 2);
        return d.toISOString().split('T')[0];
    })();
    document.getElementById('rutaFecha').min = minDate;

    // Usuarios multi-select → Select2
    $('#rutaUsuarios').select2({
        width: '100%',
        placeholder: '— Buscar y seleccionar usuarios —',
        allowClear: true,
        closeOnSelect: false,
        maximumSelectionLength: 4,
        dropdownParent: $('#modalRegistrarRuta'),
        language: {
            noResults: () => 'Sin resultados',
            searching: () => 'Buscando…',
            maximumSelected: () => 'Máximo 4 usuarios responsables',
        },
    });
    $('#rutaUsuarios').on('change', function () {
        _trk.usuariosSeleccionados = Array.from(this.selectedOptions).map(o => ({
            id: Number(o.value),
            nombre: o.text,
        }));
        _trkMarcarCambio();
    });

    // Agregar crédito
    $('#btnAgregarCredito').on('click', function () {
        const $sel = $('#rutaCreditoSelect');
        const idCred = $sel.val();
        if (!idCred) return;
        const cred = _trk.creditosDisponibles.find(c => String(c.id_credito) === String(idCred));
        if (!cred) return;
        _trkAgregarCreditoALista(cred);
        $sel.val('');
        _trkMarcarCambio();
    });

    // Filtro de créditos por estado
    $('#crdFiltroEstado').on('change', function () {
        const est = $(this).val();
        const $mun = $('#crdFiltroMunicipio');
        $mun.html('<option value="">— Todos los municipios —</option>');
        if (est) {
            const munis = [...new Set(
                _trk.creditosDisponibles
                    .filter(c => c.estado === est && c.municipio)
                    .map(c => c.municipio)
            )].sort();
            munis.forEach(m => $mun.append(`<option value="${m}">${m}</option>`));
            $mun.prop('disabled', false);
        } else {
            $mun.prop('disabled', true);
        }
        _trkRefrescarSelectCreditos();
    });

    // Filtro de créditos por municipio
    $('#crdFiltroMunicipio').on('change', _trkRefrescarSelectCreditos);

    // Mapa refresh
    $('#btnRefreshMap').on('click', _trkRenderizarMapa);

    // Guardar
    $('#btnGuardarBorrador').on('click', () => _trkGuardarRuta('borrador'));
    $('#btnEnviarRuta').on('click', () => _trkGuardarRuta('enviar'));
    $('#btnActualizarRuta').on('click', async () => {
        const ok = await Swal.fire({
            icon: 'question',
            title: '¿Guardar cambios?',
            text: 'Se actualizarán los datos de esta ruta.',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'No, regresar',
            confirmButtonColor: '#0d6efd',
        });
        if (ok.isConfirmed) _trkGuardarRuta('actualizar');
    });

    // Cerrar con aviso
    const _closeFn = async () => {
        if (!_trk.soloLectura && _trk.haychangios) {
            const ok = await trkConfirm('Tienes cambios sin guardar. ¿Deseas salir sin guardar?');
            if (!ok) return;
        }
        bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRuta'))?.hide();
    };
    document.getElementById('btnCerrarModal').addEventListener('click', _closeFn);
    document.getElementById('btnCerrarModalFooter').addEventListener('click', _closeFn);

    // Drag-and-drop
    _trk.sortableInstance = Sortable.create(document.getElementById('rutaCreditosList'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: () => {
            _trkRecalcularOrden();
            _trkMarcarCambio();
        },
    });
}

function _trkAbrirModalNuevo() {
    _trkResetModal();
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarRuta'));
    modal.show();
}

function _trkAbrirModalConCredito(cred) {
    _trkResetModal();
    _trkAgregarCreditoALista(cred);
    // Pre-seleccionar estado/municipio del crédito en los filtros
    if (cred.estado) {
        $('#crdFiltroEstado').val(cred.estado).trigger('change');
        if (cred.municipio) {
            $('#crdFiltroMunicipio').val(cred.municipio).trigger('change');
        }
    }
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarRuta'));
    modal.show();
}

function _trkResetModal() {
    _trkRTLimpiar();   // limpia tracking RT antes de resetear el modal
    _trk.idRutaEditando        = null;
    _trk.estatusRuta           = null;
    _trk.soloLectura           = false;
    _trk.cargando              = false;
    _trk.creditosEnRuta        = [];
    _trk.usuariosSeleccionados = [];
    _trk.haychangios           = false;
    _trkDesbloquearModal();
    $('#rutaNombre').val('');
    const minDate = (() => {
        const d = new Date();
        d.setDate(d.getDate() + 2);
        return d.toISOString().split('T')[0];
    })();
    $('#rutaFecha').val('').attr('min', minDate);
    // Reset hora a 8:00 AM
    $('#rutaHoraH').val('8');
    $('#rutaHoraM').val('00');
    $('#rutaHoraAmPm').val('AM');
    $('#rutaHoraActInfo').addClass('d-none').text('');
    // Reset filtros de créditos
    $('#crdFiltroEstado').val('');
    $('#crdFiltroMunicipio').html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
    _trkPoblarFiltroEstadosCrd();
    if ($('#rutaUsuarios').hasClass('select2-hidden-accessible')) {
        $('#rutaUsuarios').val(null).trigger('change');
    } else {
        $('#rutaUsuarios').val([]);
    }
    document.getElementById('rutaCreditosList').innerHTML =
        `<div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
            Aún no hay créditos en esta ruta
        </div>`;
    $('#rutaCreditosCount').text(0);
    _trkRefrescarSelectCreditos();
    _trkOcultarMapa();
    $('#mapAlertCoords').addClass('d-none');
    document.getElementById('modalRegistrarRutaLabel').innerHTML =
        '<i class="fa-solid fa-route me-2"></i>Registrar ruta de recolección';
}

// ─── Créditos en el modal ────────────────────────────────
function _trkPoblarFiltroEstadosCrd() {
    const estados = [...new Set(
        _trk.creditosDisponibles.map(c => c.estado).filter(Boolean)
    )].sort();
    const $est = $('#crdFiltroEstado');
    $est.html('<option value="">— Todos los estados —</option>');
    estados.forEach(e => $est.append(`<option value="${e}">${e}</option>`));
}

function _trkRefrescarSelectCreditos() {
    const estFiltro = $('#crdFiltroEstado').val();
    const munFiltro = $('#crdFiltroMunicipio').val();
    const $sel = $('#rutaCreditoSelect');
    const idsEnRuta = new Set(_trk.creditosEnRuta.map(c => String(c.id_credito)));
    $sel.html('<option value="">— Buscar crédito (ID · Modelo · BIN) —</option>');
    _trk.creditosDisponibles.forEach(c => {
        if (idsEnRuta.has(String(c.id_credito))) return;
        if (estFiltro && c.estado !== estFiltro) return;
        if (munFiltro && c.municipio !== munFiltro) return;
        const modelo = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const label  = `#${c.id_credito} · ${modelo || '(sin modelo)'} · ${c.bin || '—'}`;
        $sel.append(`<option value="${c.id_credito}">${label}</option>`);
    });
}

function _trkAgregarCreditoALista(cred) {
    // RN-03: no duplicados
    if (_trk.creditosEnRuta.find(c => String(c.id_credito) === String(cred.id_credito))) {
        Swal.fire({ icon: 'warning', title: 'Aviso', text: 'Este crédito ya está en la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    cred.orden_ruta = _trk.creditosEnRuta.length + 1;
    cred.estatus_confirmacion_gestor = cred.estatus_confirmacion_gestor || 'pendiente';
    _trk.creditosEnRuta.push(cred);
    _trkRenderListaCreditos();
    _trkRefrescarSelectCreditos();
    _trkRenderizarMapa();
}

function _trkQuitarCredito(idCred) {
    _trk.creditosEnRuta = _trk.creditosEnRuta.filter(c => String(c.id_credito) !== String(idCred));
    _trkRecalcularOrden();
    _trkRenderListaCreditos();
    _trkRefrescarSelectCreditos();
    _trkRenderizarMapa();
    _trkMarcarCambio();
}

function _trkRenderListaCreditos() {
    const $list = $('#rutaCreditosList');
    const isEmpty = _trk.creditosEnRuta.length === 0;
    $('#rutaCreditosCount').text(_trk.creditosEnRuta.length);

    if (isEmpty) {
        $list.html(`<div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
            Aún no hay créditos en esta ruta
        </div>`);
        return;
    }

    $list.html('');
    _trk.creditosEnRuta.forEach((c, idx) => {
        const modelo    = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ') || '—';
        const badgeConf = CONF_LABEL[c.estatus_confirmacion_gestor] || CONF_LABEL['pendiente'];
        const tienePin  = c.latitud_manual && c.longitud_manual;
        const pinClass  = tienePin ? 'btn-pin-ubicacion tiene-pin' : 'btn-pin-ubicacion';
        const pinTitle  = tienePin ? 'Ubicación manual asignada (clic para cambiar)' : 'Asignar ubicación en mapa';

        // Los créditos en ruta nunca se bloquean
        const filaLectura = false;

        // Elementos que sólo aparecen en modo edición
        const dragHandle  = filaLectura ? '' : '<i class="fa-solid fa-grip-vertical drag-handle"></i>';
        const confControl = filaLectura
            ? badgeConf
            : `<select class="form-select form-select-sm py-0 ms-1 select-conf-gestor"
                    style="max-width:130px;font-size:.75rem;"
                    data-id="${c.id_credito}">
                <option value="pendiente"   ${c.estatus_confirmacion_gestor === 'pendiente'   ? 'selected' : ''}>Pendiente</option>
                <option value="confirmado"  ${c.estatus_confirmacion_gestor === 'confirmado'  ? 'selected' : ''}>Confirmado</option>
                <option value="rechazado"   ${c.estatus_confirmacion_gestor === 'rechazado'   ? 'selected' : ''}>Rechazado</option>
                <option value="en_revision" ${c.estatus_confirmacion_gestor === 'en_revision' ? 'selected' : ''}>En revisión</option>
            </select>`;
        const actionBtns = filaLectura ? '' : `
            <button class="btn btn-sm btn-outline-secondary ${pinClass}" data-id="${c.id_credito}" title="${pinTitle}" style="font-size:.72rem;padding:.15rem .4rem;">
                <i class="fa-solid fa-map-pin"></i>
            </button>
            <button class="btn btn-outline-danger btn-remove-cred" data-id="${c.id_credito}" title="Quitar">
                <i class="fa-solid fa-trash-alt"></i>
            </button>`;

        const etaIni  = _trkParseHora12(c.hora_eta_ini);
        const etaFin  = _trkParseHora12(c.hora_eta_fin);
        const optsIni = _trkEtaHoraOpts(etaIni.h);
        const optsFin = _trkEtaHoraOpts(etaFin.h);

        const html = `
        <div class="track-credito-row" data-id="${c.id_credito}">
            ${dragHandle}
            <span class="orden-num">${idx + 1}</span>
            <div class="d-flex flex-column gap-0 flex-grow-1" style="min-width:0;">
                <span class="fw-semibold text-truncate">#${c.id_credito} — ${c.nombre_cliente || '—'}</span>
                <span class="text-muted" style="font-size:.75rem;">
                    ${modelo} · BIN: ${c.bin || '—'}
                    &nbsp;|&nbsp;${c.estado || '—'}, ${c.municipio || '—'}
                </span>
                <div class="eta-row d-flex align-items-center gap-1 mt-1 flex-wrap">
                    <span class="text-muted fw-semibold" style="font-size:.7rem;white-space:nowrap;">ETA:</span>
                    <input type="date" class="form-control eta-fecha" data-id="${c.id_credito}" value="${c.fecha_eta || ''}" style="max-width:130px;" title="Fecha estimada de llegada">
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="ini" style="width:62px;flex-shrink:0;" title="Hora inicio">${optsIni}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="ini" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaIni.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos inicio">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="ini" style="width:62px;flex-shrink:0;" title="AM/PM inicio">
                        <option value="AM"${etaIni.ampm === 'AM' ? ' selected' : ''}>AM</option>
                        <option value="PM"${etaIni.ampm === 'PM' ? ' selected' : ''}>PM</option>
                    </select>
                    <span class="text-muted" style="font-size:.7rem;line-height:1;">–</span>
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="Hora fin">${optsFin}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="fin" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaFin.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos fin">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="AM/PM fin">
                        <option value="AM"${etaFin.ampm === 'AM' ? ' selected' : ''}>AM</option>
                        <option value="PM"${etaFin.ampm === 'PM' ? ' selected' : ''}>PM</option>
                    </select>
                </div>
            </div>
            ${confControl}
            ${actionBtns}
        </div>`;
        $list.append(html);
    });

    // Eventos de créditos (siempre activos, incluso en modo ver ruta)
    $list.find('.btn-remove-cred').off('click').on('click', function () {
            _trkQuitarCredito($(this).data('id'));
        });
        $list.find('.btn-pin-ubicacion').off('click').on('click', function () {
            const id = $(this).data('id');
            const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(id));
            if (cred) _trkAbrirMapPicker(cred);
        });
        $list.find('.select-conf-gestor').off('change').on('change', function () {
            const id  = $(this).data('id');
            const val = $(this).val();
            const c   = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
            if (c) {
                c.estatus_confirmacion_gestor = val;
                _trkRenderListaCreditos();
            }
            _trkMarcarCambio();
        });
        $list.find('.eta-fecha').off('change').on('change', function () {
            const id = $(this).data('id');
            const c  = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
            if (c) c.fecha_eta = $(this).val() || null;
            _trkMarcarCambio();
        });
        $list.find('.eta-h, .eta-ap').off('change').on('change', function () {
            const id   = $(this).data('id');
            const tipo = $(this).data('tipo');
            const c    = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
            if (c) {
                if (tipo === 'ini') c.hora_eta_ini = _trkLeerEtaHora(id, 'ini');
                else                c.hora_eta_fin = _trkLeerEtaHora(id, 'fin');
            }
            _trkMarcarCambio();
        });
        $list.find('.eta-m')
            .off('keydown input blur')
            .on('keydown', function (e) {
                const allowed = ['Backspace','Delete','Tab','Escape','ArrowLeft','ArrowRight','Home','End'];
                if (allowed.includes(e.key)) return;
                if (!/^[0-9]$/.test(e.key)) e.preventDefault();
            })
            .on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
            })
            .on('blur', function () {
                const raw = this.value.replace(/[^0-9]/g, '');
                if (raw === '') { this.value = '00'; }
                const n = parseInt(raw || '0', 10);
                if (isNaN(n) || n > 59) {
                    this.value = '00';
                    $(this).addClass('is-invalid');
                    setTimeout(() => $(this).removeClass('is-invalid'), 1500);
                    Swal.fire({
                        icon: 'error', title: 'Minutos incorrectos',
                        text: `"${n}" no es válido. Deben ser entre 00 y 59.`,
                        confirmButtonText: 'Aceptar',
                    });
                } else {
                    this.value = String(n).padStart(2, '0');
                    $(this).removeClass('is-invalid');
                }
                const id   = $(this).data('id');
                const tipo = $(this).data('tipo');
                const c    = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
                if (c) {
                    if (tipo === 'ini') c.hora_eta_ini = _trkLeerEtaHora(id, 'ini');
                    else                c.hora_eta_fin = _trkLeerEtaHora(id, 'fin');
                }
                _trkMarcarCambio();
            });
}

function _trkRecalcularOrden() {
    const items = document.querySelectorAll('#rutaCreditosList .track-credito-row');
    const newOrder = Array.from(items).map(el => el.dataset.id);
    _trk.creditosEnRuta.sort((a, b) => {
        const ia = newOrder.indexOf(String(a.id_credito));
        const ib = newOrder.indexOf(String(b.id_credito));
        return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
    });
    _trk.creditosEnRuta.forEach((c, i) => { c.orden_ruta = i + 1; });
    // Actualizar numeración visual
    items.forEach((el, i) => {
        const numEl = el.querySelector('.orden-num');
        if (numEl) numEl.textContent = i + 1;
    });
}



// ─── Mapa ────────────────────────────────────────────────
function _trkOcultarMapa() {
    document.getElementById('trackMap').style.display      = 'none';
    document.getElementById('mapPlaceholder').style.display = 'flex';
}

function _trkRenderizarMapa() {
    const creditos = _trk.creditosEnRuta;
    if (!creditos.length) {
        _trkOcultarMapa();
        return;
    }
    // Verificar si alguno tiene coordenadas o dirección
    const sinUbicacion = creditos.filter(c =>
        !(c.latitud && c.longitud) && !String(c.direccion || '').trim()
    );
    if (sinUbicacion.length > 0) {
        document.getElementById('mapAlertCoords').classList.remove('d-none');
    } else {
        document.getElementById('mapAlertCoords').classList.add('d-none');
    }
    if (!window._trackGoogleMapsKey) {
        document.getElementById('mapPlaceholder').innerHTML =
            '<i class="fa-solid fa-triangle-exclamation fa-2x opacity-30"></i>' +
            '<span>Google Maps no disponible (falta API key)</span>';
        return;
    }

    document.getElementById('trackMap').style.display      = 'block';
    document.getElementById('mapPlaceholder').style.display = 'none';

    if (!_trk.mapLoaded) {
        // Cargar Google Maps API dinámicamente
        const script     = document.createElement('script');
        script.src       = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry&callback=_trkMapCallback`;
        script.async     = true;
        script.defer     = true;
        document.head.appendChild(script);
        _trk.mapLoaded = true;
        window._trkMapCallback = () => _trkDibujarMapa(creditos);
    } else if (typeof google !== 'undefined' && google.maps) {
        _trkDibujarMapa(creditos);
    } else {
        // El script ya está cargando (desde el picker); esperar
        const waitForMaps = setInterval(() => {
            if (typeof google !== 'undefined' && google.maps) {
                clearInterval(waitForMaps);
                _trkDibujarMapa(creditos);
            }
        }, 150);
        setTimeout(() => clearInterval(waitForMaps), 10000);
    }
}

// ─── Estilos oscuros para Google Maps ───────────────────
const _TRK_DARK_MAP_STYLES = [
    { elementType: 'geometry',             stylers: [{ color: '#1d2c2c' }] },
    { elementType: 'labels.text.fill',     stylers: [{ color: '#8ec3b0' }] },
    { elementType: 'labels.text.stroke',   stylers: [{ color: '#1a2e2c' }] },
    { featureType: 'road',                 elementType: 'geometry',        stylers: [{ color: '#2c3e3e' }] },
    { featureType: 'road',                 elementType: 'geometry.stroke', stylers: [{ color: '#1a2e2c' }] },
    { featureType: 'road',                 elementType: 'labels.text.fill',stylers: [{ color: '#9ca5b3' }] },
    { featureType: 'road.highway',         elementType: 'geometry',        stylers: [{ color: '#0f4a4a' }] },
    { featureType: 'road.highway',         elementType: 'geometry.stroke', stylers: [{ color: '#0d3838' }] },
    { featureType: 'water',                elementType: 'geometry',        stylers: [{ color: '#0e1d1d' }] },
    { featureType: 'water',                elementType: 'labels.text.fill',stylers: [{ color: '#515c6d' }] },
    { featureType: 'poi',                  elementType: 'geometry',        stylers: [{ color: '#263636' }] },
    { featureType: 'poi.park',             elementType: 'geometry',        stylers: [{ color: '#1c3030' }] },
    { featureType: 'transit',              elementType: 'geometry',        stylers: [{ color: '#2f3948' }] },
    { featureType: 'administrative',       elementType: 'geometry.stroke', stylers: [{ color: '#334444' }] },
];

function _trkDibujarMapa(creditos) {
    const mapDiv = document.getElementById('trackMap');
    if (!mapDiv || typeof google === 'undefined') return;

    if (!_trk.mapInstance) {
        _trk.mapInstance = new google.maps.Map(mapDiv, {
            zoom: 10,
            center: { lat: 20.6597, lng: -103.3496 },
            styles: document.body.classList.contains('dark-mode') ? _TRK_DARK_MAP_STYLES : [],
        });
        _trk.geocoder = new google.maps.Geocoder();
    }

    // ── Limpiar mapa anterior ─────────────────────────────────
    _trk.mapMarkers.forEach(m => m.setMap(null));
    _trk.mapMarkers = [];
    if (_trk.directionsRenderer) {
        _trk.directionsRenderer.setMap(null);
        _trk.directionsRenderer = null;
    }

    const map    = _trk.mapInstance;
    const bounds = new google.maps.LatLngBounds();

    // ── Separar por estatus ───────────────────────────────────
    const paraRuta     = creditos
        .filter(c => c.estatus_confirmacion_gestor === 'confirmado')
        .sort((a, b) => (a.orden_ruta || 99) - (b.orden_ruta || 99));
    const soloPin      = creditos.filter(c =>
        c.estatus_confirmacion_gestor === 'pendiente' ||
        c.estatus_confirmacion_gestor === 'en_revision'
    );
    // rechazados → completamente omitidos del mapa

    // ── Icono SVG numerado (confirmados) ─────────────────────
    const svgIcon = (num) => {
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34">
            <circle cx="17" cy="17" r="15" fill="#e53935" stroke="#fff" stroke-width="2.5"/>
            <text x="17" y="22" text-anchor="middle" fill="#fff"
                  font-size="${num > 9 ? 11 : 13}" font-weight="bold" font-family="Arial,sans-serif">${num}</text>
        </svg>`;
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(34, 34),
            anchor:     new google.maps.Point(17, 17),
        };
    };

    // ── Icono por estatus (no confirmados) ───────────────────
    const statusIcon = (estatus) => {
        const color = estatus === 'en_revision' ? '#fb8c00' : '#fdd835';
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26">
            <circle cx="13" cy="13" r="11" fill="${color}" stroke="#fff" stroke-width="2"/>
        </svg>`;
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(26, 26),
            anchor:     new google.maps.Point(13, 13),
        };
    };

    // ── Resolver coordenadas de un crédito ───────────────────
    const resolverPos = (c) => new Promise(resolve => {
        const lat = parseFloat(c.latitud_manual ?? c.latitud);
        const lng = parseFloat(c.longitud_manual ?? c.longitud);
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            resolve({ lat, lng });
            return;
        }
        if (_trk.geocoder && (c.direccion || (c.municipio && c.estado))) {
            const addr = [c.direccion, c.municipio, c.estado, 'M\u00e9xico'].filter(Boolean).join(', ');
            _trk.geocoder.geocode({ address: addr }, (res, st) => {
                if (st === 'OK' && res[0]) {
                    resolve({ lat: res[0].geometry.location.lat(), lng: res[0].geometry.location.lng() });
                } else {
                    resolve(null);
                }
            });
        } else {
            resolve(null);
        }
    });

    // ── Resolver todo en paralelo y dibujar ──────────────────
    Promise.all([
        ...paraRuta.map(c  => resolverPos(c).then(pos => ({ c, pos, tipo: 'ruta' }))),
        ...soloPin.map(c   => resolverPos(c).then(pos => ({ c, pos, tipo: 'pin'  }))),
    ]).then(results => {
        // Marcadores sin ruta (pendiente / en_revision)
        results.filter(r => r.tipo === 'pin' && r.pos).forEach(({ c, pos }) => {
            const m = new google.maps.Marker({
                map,
                position: pos,
                icon:  statusIcon(c.estatus_confirmacion_gestor),
                title: `#${c.id_credito} \u2014 ${c.nombre_cliente || ''} (${c.estatus_confirmacion_gestor})`,
                zIndex: 1,
            });
            _trk.mapMarkers.push(m);
            bounds.extend(pos);
        });

        // Marcadores numerados (confirmados)
        const rutaConPos = results
            .filter(r => r.tipo === 'ruta' && r.pos)
            .sort((a, b) => (a.c.orden_ruta || 99) - (b.c.orden_ruta || 99));

        rutaConPos.forEach(({ c, pos }, idx) => {
            const m = new google.maps.Marker({
                map,
                position: pos,
                icon:  svgIcon(idx + 1),
                title: `#${c.id_credito} \u2014 ${c.nombre_cliente || ''}`,
                zIndex: 10 + idx,
            });
            _trk.mapMarkers.push(m);
            bounds.extend(pos);
        });

        if (!bounds.isEmpty()) map.fitBounds(bounds);

        if (rutaConPos.length < 2) {
            if (rutaConPos.length === 1) {
                map.setCenter(rutaConPos[0].pos);
                map.setZoom(14);
            }
            return;
        }

        // ── Trazar polilínea de ruta (solo confirmados) ──────
        const origin      = new google.maps.LatLng(rutaConPos[0].pos.lat, rutaConPos[0].pos.lng);
        const destination = new google.maps.LatLng(rutaConPos[rutaConPos.length - 1].pos.lat, rutaConPos[rutaConPos.length - 1].pos.lng);
        const waypoints   = rutaConPos.slice(1, -1).map(r => ({
            location: new google.maps.LatLng(r.pos.lat, r.pos.lng),
            stopover: true,
        }));

        _trk.directionsRenderer = new google.maps.DirectionsRenderer({
            map,
            suppressMarkers:          true,  // usamos nuestros propios marcadores
            suppressInfoWindows:       true,
            preserveViewport:          true,
            polylineOptions: { strokeColor: '#1565C0', strokeWeight: 4, strokeOpacity: 0.85 },
        });

        new google.maps.DirectionsService().route({
            origin,
            destination,
            waypoints,
            travelMode:               google.maps.TravelMode.DRIVING,
            provideRouteAlternatives: false,
        }, (result, status) => {
            if (status === 'OK') {
                _trk.directionsRenderer.setDirections(result);
            }
            map.fitBounds(bounds);
        });
    });
}

// ─── Map Picker (Plan B: clic en mapa para asignar coords) ──────────────────
const _trkPicker = {
    modal:        null,
    mapInstance:  null,
    marker:       null,
    creditoId:    null,
    selectedLat:  null,
    selectedLng:  null,
};

function _trkAbrirMapPicker(cred) {
    if (!window._trackGoogleMapsKey) {
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no está disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }

    _trkPicker.creditoId   = cred.id_credito;
    _trkPicker.selectedLat = null;
    _trkPicker.selectedLng = null;

    // Etiqueta en el modal
    document.getElementById('mapPickerCreditoLabel').textContent =
        ` — #${cred.id_credito} ${cred.nombre_cliente ? '(' + cred.nombre_cliente + ')' : ''}`;
    document.getElementById('mapPickerCoordsLabel').innerHTML =
        '<i class="fa-solid fa-crosshairs me-1"></i>Sin selección';
    document.getElementById('btnConfirmarMapPicker').disabled = true;

    // Mostrar modal
    if (!_trkPicker.modal) {
        _trkPicker.modal = new bootstrap.Modal(document.getElementById('modalMapPicker'));
        document.getElementById('btnCerrarMapPicker').addEventListener('click',  () => _trkPicker.modal.hide());
        document.getElementById('btnCancelarMapPicker').addEventListener('click', () => _trkPicker.modal.hide());
        document.getElementById('btnConfirmarMapPicker').addEventListener('click', _trkConfirmarMapPicker);
    }
    _trkPicker.modal.show();

    // Inicializar mapa después de que el modal sea visible (necesario para que el div tenga dimensiones)
    document.getElementById('modalMapPicker').addEventListener('shown.bs.modal', _trkInicializarMapPicker, { once: true });
}

function _trkInicializarMapPicker() {
    const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(_trkPicker.creditoId));
    if (!cred) return;

    // Centro: coordenadas manuales existentes > coords del crédito > GDL
    let centerLat = 20.6597, centerLng = -103.3496;
    if (cred.latitud_manual && cred.longitud_manual) {
        centerLat = parseFloat(cred.latitud_manual);
        centerLng = parseFloat(cred.longitud_manual);
    } else if (cred.latitud && cred.longitud) {
        centerLat = parseFloat(cred.latitud);
        centerLng = parseFloat(cred.longitud);
    }

    const pickerDiv = document.getElementById('mapPickerContainer');

    const initMap = () => {
        if (!_trkPicker.mapInstance) {
            _trkPicker.mapInstance = new google.maps.Map(pickerDiv, {
                zoom: 15,
                center: { lat: centerLat, lng: centerLng },
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            _trkPicker.mapInstance.addListener('click', (e) => {
                const lat = e.latLng.lat();
                const lng = e.latLng.lng();
                _trkPicker.selectedLat = lat;
                _trkPicker.selectedLng = lng;

                if (!_trkPicker.marker) {
                    _trkPicker.marker = new google.maps.Marker({
                        map: _trkPicker.mapInstance,
                        position: e.latLng,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                        title: 'Ubicación seleccionada',
                    });
                    _trkPicker.marker.addListener('dragend', (ev) => {
                        _trkPicker.selectedLat = ev.latLng.lat();
                        _trkPicker.selectedLng = ev.latLng.lng();
                        _trkActualizarLabelCoordsicker();
                    });
                } else {
                    _trkPicker.marker.setPosition(e.latLng);
                }

                _trkActualizarLabelCoordsicker();
                document.getElementById('btnConfirmarMapPicker').disabled = false;
            });
        } else {
            // Reusar mapa: re-centrar y limpiar marcador anterior
            _trkPicker.mapInstance.setCenter({ lat: centerLat, lng: centerLng });
            _trkPicker.mapInstance.setZoom(15);
            if (_trkPicker.marker) {
                _trkPicker.marker.setMap(null);
                _trkPicker.marker = null;
            }
        }

        // Si ya tenía coords manuales, mostrar marcador previo
        if (cred.latitud_manual && cred.longitud_manual) {
            const prevPos = { lat: parseFloat(cred.latitud_manual), lng: parseFloat(cred.longitud_manual) };
            _trkPicker.marker = new google.maps.Marker({
                map: _trkPicker.mapInstance,
                position: prevPos,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'Ubicación guardada',
            });
            _trkPicker.selectedLat = prevPos.lat;
            _trkPicker.selectedLng = prevPos.lng;
            _trkPicker.marker.addListener('dragend', (ev) => {
                _trkPicker.selectedLat = ev.latLng.lat();
                _trkPicker.selectedLng = ev.latLng.lng();
                _trkActualizarLabelCoordsicker();
            });
            _trkActualizarLabelCoordsicker();
            document.getElementById('btnConfirmarMapPicker').disabled = false;
        }

        google.maps.event.trigger(_trkPicker.mapInstance, 'resize');
    };

    if (typeof google !== 'undefined' && google.maps) {
        initMap();
    } else if (_trk.mapLoaded) {
        // El script ya está cargando (desde el mapa de ruta); esperar a que esté listo
        const waitForMaps = setInterval(() => {
            if (typeof google !== 'undefined' && google.maps) {
                clearInterval(waitForMaps);
                initMap();
            }
        }, 150);
        setTimeout(() => clearInterval(waitForMaps), 10000);
    } else {
        // Cargar Maps si aún no está
        const script = document.createElement('script');
        script.src   = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry&callback=_trkMapPickerReady`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        window._trkMapPickerReady = initMap;
        _trk.mapLoaded = true;
    }
}

function _trkActualizarLabelCoordsicker() {
    const lat = _trkPicker.selectedLat;
    const lng = _trkPicker.selectedLng;
    if (lat === null || lng === null) return;
    document.getElementById('mapPickerCoordsLabel').innerHTML =
        `<i class="fa-solid fa-location-dot me-1" style="color:var(--track-color);"></i>` +
        `Lat: <strong>${lat.toFixed(6)}</strong> &nbsp; Lng: <strong>${lng.toFixed(6)}</strong>`;
}

function _trkConfirmarMapPicker() {
    const lat = _trkPicker.selectedLat;
    const lng = _trkPicker.selectedLng;
    if (lat === null || lng === null) return;

    const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(_trkPicker.creditoId));
    if (cred) {
        cred.latitud_manual  = lat;
        cred.longitud_manual = lng;
        // Sobrescribir también las props que usa el mapa de ruta
        cred.latitud  = lat;
        cred.longitud = lng;
        _trkMarcarCambio();
    }

    _trkPicker.modal.hide();
    _trkRenderListaCreditos();
    _trkRenderizarMapa();
}

// ─── Guardar ruta ────────────────────────────────────────
// ─── Helpers de hora AM/PM ──────────────────────────────
function _trkFormatHora(horaStr) {
    if (!horaStr) return '—';
    const parts = horaStr.split(':');
    const hh    = parseInt(parts[0], 10);
    const mm    = parts[1] || '00';
    const ampm  = hh >= 12 ? 'PM' : 'AM';
    const h12   = hh % 12 || 12;
    return `${h12}:${mm} ${ampm}`;
}

function _trkHoraToPayload() {
    const h    = parseInt($('#rutaHoraH').val(), 10) || 12;
    const m    = $('#rutaHoraM').val() || '00';
    const ampm = $('#rutaHoraAmPm').val() || 'AM';
    let hh;
    if (ampm === 'PM') {
        hh = (h === 12) ? 12 : h + 12;
    } else {
        hh = (h === 12) ? 0 : h;
    }
    return String(hh).padStart(2, '0') + ':' + m;
}

// Convierte un string HH:MM (24h) al objeto {h, m, ampm} en formato 12h
function _trkParseHora12(horaStr) {
    if (!horaStr) return { h: 8, m: '00', ampm: 'AM' };
    const parts = horaStr.split(':');
    const hh    = parseInt(parts[0], 10) || 0;
    const mm    = (parts[1] || '00').slice(0, 2);
    return { h: hh % 12 || 12, m: mm, ampm: hh >= 12 ? 'PM' : 'AM' };
}

// Genera <option> 1-12 con el seleccionado marcado
function _trkEtaHoraOpts(sel) {
    return Array.from({length: 12}, (_, i) => i + 1)
        .map(h => `<option value="${h}"${h === sel ? ' selected' : ''}>${h}</option>`)
        .join('');
}

// Lee los selects H/M/AP del DOM y devuelve HH:MM en 24h
function _trkLeerEtaHora(idCredito, tipo) {
    const $row = $(`#rutaCreditosList .track-credito-row[data-id="${idCredito}"]`);
    const h    = parseInt($row.find(`.eta-h[data-tipo="${tipo}"]`).val(), 10) || 12;
    const m    = $row.find(`.eta-m[data-tipo="${tipo}"]`).val() || '00';
    const ampm = $row.find(`.eta-ap[data-tipo="${tipo}"]`).val() || 'AM';
    let hh;
    if (ampm === 'PM') { hh = (h === 12) ? 12 : h + 12; }
    else               { hh = (h === 12) ? 0  : h; }
    return String(hh).padStart(2, '0') + ':' + m;
}

function _trkGuardarRuta(modo) {
    const nombre    = $('#rutaNombre').val().trim();
    const fecha     = $('#rutaFecha').val();

    // Estado/municipio: preferir el filtro seleccionado; si vacío y hay créditos, derivar del primero
    const primerCred = _trk.creditosEnRuta[0] || {};
    const estado    = $('#crdFiltroEstado').val()    || primerCred.estado    || '';
    const municipio = $('#crdFiltroMunicipio').val() || primerCred.municipio || '';

    if (!nombre) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre de la ruta es obligatorio.', confirmButtonText: 'Aceptar' });
        document.getElementById('rutaNombre').focus();
        return;
    }
    if (_trk.creditosEnRuta.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debe agregar al menos un crédito a la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!fecha) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La fecha programada es obligatoria.', confirmButtonText: 'Aceptar' });
        return;
    }

    if (modo !== 'borrador' && modo !== 'actualizar') {
        if (_trk.usuariosSeleccionados.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Asigna al menos un usuario responsable para enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
        const noConfirmados = _trk.creditosEnRuta.filter(c => c.estatus_confirmacion_gestor !== 'confirmado');
        if (noConfirmados.length > 0) {
            Swal.fire({ icon: 'warning', title: 'Pendiente', text: 'Todos los créditos deben tener confirmación del gestor para enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
    }

    const payload = {
        id_ruta:          _trk.idRutaEditando || 0,
        nombre_ruta:      nombre,
        estado,
        municipio,
        fecha_programada: fecha,
        hora_salida:      _trkHoraToPayload(),
        modo,
        usuarios: _trk.usuariosSeleccionados.map(u => u.id),
        creditos: _trk.creditosEnRuta.map(c => ({
            id_credito:                  c.id_credito,
            modelo:                      [c.moto_marca, c.moto_modelo].filter(Boolean).join(' '),
            bin:                         c.bin || '',
            estado:                      c.estado || '',
            municipio:                   c.municipio || '',
            direccion:                   c.direccion || '',
            latitud:                     c.latitud  || null,
            longitud:                    c.longitud || null,
            orden_ruta:                  c.orden_ruta,
            estatus_confirmacion_gestor: c.estatus_confirmacion_gestor || 'pendiente',
            fecha_eta:                   c.fecha_eta    || null,
            hora_eta_ini:                c.hora_eta_ini || null,
            hora_eta_fin:                c.hora_eta_fin || null,
        })),
    };

    const $btnGuardar = $('#btnGuardarBorrador, #btnEnviarRuta');
    $btnGuardar.prop('disabled', true);

    trkFetch('/TrackingRecoleccion/guardarRuta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => {
        if (r.success) {
            _trk.haychangios = false;
            Swal.fire({ icon: 'success', title: '¡Listo!', text: modo === 'borrador' ? 'Borrador guardado correctamente.' : 'Ruta enviada correctamente.', timer: 2000, showConfirmButton: false });
            bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRuta'))?.hide();
            _trkCargarCreditosPaso2();
            _trkCargarBorradores();
            _trkCargarRutas();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || r.message || 'Error al guardar la ruta.', confirmButtonText: 'Aceptar' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Error de conexión al guardar.', confirmButtonText: 'Aceptar' }))
    .finally(() => $btnGuardar.prop('disabled', false));
}

// ─── Ver detalle de ruta ─────────────────────────────────
function _trkVerDetalleRuta(idRuta) {
    const $body = $('#detalleRutaBody');
    $body.html('<div class="text-center py-4"><div class="spinner-border" style="color:var(--track-color);"></div></div>');
    const modal = new bootstrap.Modal(document.getElementById('modalDetalleRuta'));
    modal.show();

    trkFetch(`/TrackingRecoleccion/obtenerDetalleRuta?id_ruta=${idRuta}`)
        .then(r => {
            if (!r.success || !r.datos) {
                $body.html('<div class="alert alert-danger">No se pudo cargar el detalle.</div>');
                return;
            }
            const d = r.datos;
            const estatusBadge = RUTA_LABEL[d.estatus_ruta] || d.estatus_ruta;
            const usuarios = (d.usuarios || []).map(u => u.nombre_usuario).join(', ') || '—';
            let rowsHtml = '';
            (d.detalle || []).forEach((det, i) => {
                rowsHtml += `<tr>
                    <td class="text-center">${det.orden_ruta || (i + 1)}</td>
                    <td>${det.id_credito || '—'}</td>
                    <td>${det.nombre_cliente || '—'}</td>
                    <td>${det.modelo || '—'}</td>
                    <td>${det.bin || '—'}</td>
                    <td>${det.estado || '—'} / ${det.municipio || '—'}</td>
                    <td>${det.estatus_proceso || '—'}</td>
                    <td>${CONF_LABEL[det.estatus_confirmacion_gestor] || det.estatus_confirmacion_gestor || '—'}</td>
                </tr>`;
            });
            $body.html(`
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">Nombre</b>${d.nombre_ruta}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Estado</b>${d.estado || '—'}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Municipio</b>${d.municipio || '—'}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Fecha programada</b>${d.fecha_programada_fmt || d.fecha_programada || '—'}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Hora de salida</b>${
                        d.act_hora_1
                            ? `<span class="badge bg-warning text-dark me-1" title="Hora actualizada">${_trkFormatHora(d.act_hora_1)}</span><s class="text-muted small">${_trkFormatHora(d.hora_inicial)}</s>`
                            : (d.hora_inicial ? `<span class="badge bg-light text-dark border">${_trkFormatHora(d.hora_inicial)}</span>` : '—')
                    }</div>
                    <div class="col-6 col-md-1"><b class="d-block small text-muted">Estatus</b>${estatusBadge}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Responsables</b><span class="small">${usuarios}</span></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size:.8rem;">
                        <thead style="background:var(--track-color);color:#fff;">
                            <tr>
                                <th>#</th><th>Crédito</th><th>Cliente</th><th>Modelo</th>
                                <th>BIN</th><th>Estado / Municipio</th>
                                <th>Proceso</th><th>Confirmación</th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml || '<tr><td colspan="8" class="text-center text-muted">Sin créditos</td></tr>'}</tbody>
                    </table>
                </div>
            `);
        })
        .catch(() => $body.html('<div class="alert alert-danger">Error de conexión.</div>'));
}

// ─── Marcar cambios pendientes ────────────────────────
function _trkMarcarCambio() {
    if (_trk.cargando) return;
    _trk.haychangios = true;
    if (_trk.soloLectura) {
        $('#btnActualizarRuta').prop('disabled', false)
            .css({ background: '#0d6efd', cursor: 'pointer' });
    }
}

// ─── Bloquear / Desbloquear modal ───────────────────────
function _trkBloquearModal() {
    _trk.soloLectura = true;
    // Solo bloquear campos de cabecera de la ruta
    $('#rutaNombre, #rutaFecha, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm').prop('disabled', true);
    // Usuarios: bloquear también
    $('#rutaUsuarios').prop('disabled', true);
    if ($('#rutaUsuarios').hasClass('select2-hidden-accessible')) {
        $('#rutaUsuarios').prop('disabled', true);
    }
    // Ocultar sección de agregar nuevos créditos (no añadir, solo editar existentes)
    $('#secAgregarCredito').hide();
    $('#reorderHint').hide();
    // Swap de botones: ocultar borrador/enviar, mostrar actualizar (gris hasta que haya cambios)
    $('#btnGuardarBorrador, #btnEnviarRuta').hide();
    $('#btnActualizarRuta').show().prop('disabled', true)
        .css({ background: '#94a3b8', cursor: 'not-allowed' });
    // Badge en título
    const $label = $('#modalRegistrarRutaLabel');
    if (!$label.find('.badge-solo-lectura').length) {
        $label.append('<span class="badge bg-secondary badge-solo-lectura ms-2" style="font-size:.63rem;vertical-align:middle;">Ver ruta</span>');
    }
}

function _trkDesbloquearModal() {
    _trk.soloLectura = false;
    $('#rutaNombre, #rutaFecha, #rutaUsuarios, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm, #crdFiltroEstado').prop('disabled', false);
    if ($('#rutaUsuarios').hasClass('select2-hidden-accessible')) {
        $('#rutaUsuarios').prop('disabled', false);
    }
    $('#btnGuardarBorrador, #btnEnviarRuta').show();
    $('#btnActualizarRuta').hide();
    $('#secAgregarCredito').show();
    $('#reorderHint').show();
    $('#modalRegistrarRutaLabel .badge-solo-lectura').remove();
}

// ─── Abrir ruta existente en el modal ───────────────────
function _trkCargarRutaEnModal(idRuta, soloLectura) {
    _trkResetModal();
    _trk.idRutaEditando = idRuta;
    _trk.cargando       = true;

    // Actualizar título mientras carga
    const icon = soloLectura ? 'eye' : 'pen-to-square';
    document.getElementById('modalRegistrarRutaLabel').innerHTML =
        `<i class="fa-solid fa-${icon} me-2"></i>${soloLectura ? 'Ver ruta' : 'Editar ruta'}`;

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRegistrarRuta'));
    modal.show();

    trkFetch(`/TrackingRecoleccion/obtenerDetalleRuta?id_ruta=${idRuta}`)
        .then(r => {
            if (!r.success || !r.datos) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la ruta.', confirmButtonText: 'Aceptar' });
                modal.hide();
                return;
            }
            const d = r.datos;

            // Título final con nombre de la ruta
            document.getElementById('modalRegistrarRutaLabel').innerHTML =
                `<i class="fa-solid fa-${icon} me-2"></i>${soloLectura ? 'Ver ruta' : 'Editar ruta'}: <em>${d.nombre_ruta || ''}</em>`;

            // Campos básicos
            $('#rutaNombre').val(d.nombre_ruta || '');
            $('#rutaFecha').val(d.fecha_programada || '');

            // Créditos (cargar directamente en array)
            _trk.creditosEnRuta = (d.detalle || []).map((det, i) => ({
                id_credito:                  det.id_credito,
                nombre_cliente:              det.nombre_cliente || '',
                moto_marca:                  '',
                moto_modelo:                 det.modelo || '',
                bin:                         det.bin || '',
                estado:                      det.estado || '',
                municipio:                   det.municipio || '',
                direccion:                   det.direccion || '',
                latitud:                     det.latitud  || null,
                longitud:                    det.longitud || null,
                orden_ruta:                  det.orden_ruta || (i + 1),
                estatus_confirmacion_gestor: det.estatus_confirmacion_gestor || 'pendiente',
                fecha_eta:                   det.fecha_eta    || null,
                hora_eta_ini:                det.hora_eta_ini || null,
                hora_eta_fin:                det.hora_eta_fin || null,
            }));
            _trkRenderListaCreditos();
            _trkRefrescarSelectCreditos();
            _trkRenderizarMapa();

            // Usuarios
            if (d.usuarios && d.usuarios.length) {
                _trk.usuariosSeleccionados = d.usuarios.map(u => ({
                    id: u.id_usuario, nombre: u.nombre_usuario,
                }));
                const ids = d.usuarios.map(u => String(u.id_usuario));
                $('#rutaUsuarios').val(ids).trigger('change');
            }

            // Estado + Municipio via filtros de créditos
            $('#crdFiltroEstado').val(d.estado || '').trigger('change');
            if (d.municipio) {
                $('#crdFiltroMunicipio').val(d.municipio);
            }
            _trk.estatusRuta  = d.estatus_ruta || null;
            _trk.cargando     = false;
            _trk.haychangios  = false;

            // Aplicar bloqueo si es solo lectura
            if (soloLectura) _trkBloquearModal();

            // Poblar hora desde act_hora_1 (si hay cambio) o hora_inicial
            const horaVigente = d.act_hora_1 || d.hora_inicial || null;
            if (horaVigente) {
                const hParts = horaVigente.split(':');
                const hh = parseInt(hParts[0], 10);
                const mm = hParts[1] || '00';
                const ampm = hh >= 12 ? 'PM' : 'AM';
                const h12  = hh % 12 || 12;
                $('#rutaHoraH').val(String(h12));
                $('#rutaHoraM').val(mm);
                $('#rutaHoraAmPm').val(ampm);
                // Si hay hora actualizada, mostrar la original tachada como referencia
                if (d.act_hora_1 && d.hora_inicial) {
                    $('#rutaHoraActInfo')
                        .removeClass('d-none')
                        .html(`<span class="text-warning"><i class="fa-solid fa-clock-rotate-left me-1"></i>Hora original: <s>${_trkFormatHora(d.hora_inicial)}</s></span>`);
                } else {
                    $('#rutaHoraActInfo').addClass('d-none').text('');
                }
            }

            _trk.haychangios = false;

            // Iniciar tracking en tiempo real si la ruta está activa o completada
            const esActiva = ['en_proceso', 'completado'].includes(d.estatus_ruta);
            if (esActiva) {
                _trkRTIniciar(idRuta);
            } else {
                _trkRTLimpiar();
            }
        })
        .catch(() => {
            _trk.cargando = false;
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonText: 'Aceptar' });
            modal.hide();
        });
}

/* ════════════════════════════════════════════════════════════
   TRACKING EN TIEMPO REAL — estilo Mercado Libre
   Muestra el estado del recorrido (paradas, progreso, ubicación)
   cuando la ruta está en_proceso o completado.
════════════════════════════════════════════════════════════ */

const _trkRT = {
    idRuta:       null,
    ws:           null,
    wsRetries:    0,
    wsRetryTO:    null,
    wsPingIv:     null,
    estado:       null,   // último estado recibido del API
};

// ─── Limpiar todo el tracking RT ─────────────────────────
function _trkRTLimpiar() {
    if (_trkRT.wsPingIv)  { clearInterval(_trkRT.wsPingIv);  _trkRT.wsPingIv  = null; }
    if (_trkRT.wsRetryTO) { clearTimeout(_trkRT.wsRetryTO);  _trkRT.wsRetryTO = null; }
    if (_trkRT.ws)        { _trkRT.ws.onclose = null; _trkRT.ws.close(); _trkRT.ws = null; }
    _trkRT.idRuta    = null;
    _trkRT.wsRetries = 0;
    _trkRT.estado    = null;
    document.getElementById('trkTrackingSection').classList.add('d-none');
    document.getElementById('trkTimeline').innerHTML =
        `<div class="text-center text-muted py-2 small" id="trkTimelineEmpty">
            <span class="spinner-border spinner-border-sm opacity-25" style="color:var(--track-color);"></span>
         </div>`;
    _trkRTActualizarWsDot(false);
}

// ─── Inicializar para una ruta ────────────────────────────
async function _trkRTIniciar(idRuta) {
    _trkRTLimpiar();
    _trkRT.idRuta = idRuta;
    document.getElementById('trkTrackingSection').classList.remove('d-none');
    await _trkRTCargarEstado();
    const tok = await _trkChatObtenerToken();
    if (tok) _trkRTConectarWS(tok);
}

// ─── Cargar estado vía REST ───────────────────────────────
async function _trkRTCargarEstado() {
    const id = _trkRT.idRuta;
    if (!id) return;
    try {
        const r = await trkFetch(`/TrackingRecoleccion/trackingEstadoRuta?id_ruta=${id}`);
        if (r.success && r.ruta) {
            _trkRT.estado = r.ruta;
            _trkRTRenderizar(r.ruta);
        } else {
            document.getElementById('trkTimeline').innerHTML =
                `<div class="text-center text-muted py-2 small">Sin datos de tracking disponibles.</div>`;
        }
    } catch { /* silencioso */ }
}

// ─── Renderizar timeline ──────────────────────────────────
function _trkRTRenderizar(ruta) {
    // Barra de progreso
    const prog = ruta.progreso || {};
    const pct  = prog.porcentaje ?? 0;
    document.getElementById('trkProgressBar').style.width = pct + '%';
    document.getElementById('trkProgressText').textContent =
        `${prog.completados ?? 0} / ${prog.total ?? 0} puntos completados`;
    document.getElementById('trkPorcentaje').textContent = pct + '%';

    // Timeline de créditos
    const creditos = ruta.creditos || [];
    const puntoAct = ruta.punto_actual;
    if (!creditos.length) {
        document.getElementById('trkTimeline').innerHTML =
            `<div class="text-center text-muted py-2 small">Sin puntos de recolección registrados.</div>`;
        return;
    }

    const LABELS = {
        pendiente:  'Pendiente',
        en_camino:  'En camino',
        en_sitio:   'En sitio',
        completado: 'Completado',
        incidencia: 'Incidencia',
    };
    const ICONS = {
        pendiente:  'fa-circle-dot',
        en_camino:  'fa-motorcycle',
        en_sitio:   'fa-location-dot',
        completado: 'fa-circle-check',
        incidencia: 'fa-triangle-exclamation',
    };

    let html = '';
    creditos.forEach(c => {
        const est     = c.estatus_recoleccion || 'pendiente';
        const esAct   = puntoAct && puntoAct.id_detalle === c.id_detalle;
        const esDone  = est === 'completado';
        const stepCls = esDone ? 'done' : (esAct ? 'activo' : (est === 'en_sitio' ? 'en_sitio' : (est === 'incidencia' ? 'incidencia' : '')));
        const icon    = ICONS[est] || ICONS.pendiente;
        const label   = LABELS[est] || est;
        const nombre  = _trkChatEscapeHtml(c.nombre_cliente || `Crédito #${c.id_credito}`);
        const dir     = _trkChatEscapeHtml(c.direccion || c.municipio || '');
        html += `<div class="trk-step ${stepCls}" data-id="${c.id_detalle}">
            <div class="trk-step-dot"><i class="fa-solid ${icon}" style="font-size:.45rem;"></i></div>
            <div class="trk-step-body">
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="trk-step-orden">${c.orden_ruta ?? '?'}.</span>
                    <span class="trk-step-nombre">${nombre}</span>
                    <span class="trk-step-badge trk-badge-${est}">${label}</span>
                </div>
                ${dir ? `<span class="trk-step-dir">${dir}</span>` : ''}
            </div>
        </div>`;
    });
    document.getElementById('trkTimeline').innerHTML = html;
}

// ─── Aplicar cambios parciales (WS update) ───────────────
function _trkRTAplicarChanges(changes) {
    if (!changes || !changes.length || !_trkRT.estado) return;
    const creditos = _trkRT.estado.creditos || [];
    changes.forEach(ch => {
        const c = creditos.find(x => x.id_detalle === ch.id_detalle);
        if (c && ch.estatus_recoleccion) c.estatus_recoleccion = ch.estatus_recoleccion;
    });
    // Recalcular progreso
    const total       = creditos.length;
    const completados = creditos.filter(c => c.estatus_recoleccion === 'completado').length;
    if (_trkRT.estado.progreso) {
        _trkRT.estado.progreso.completados  = completados;
        _trkRT.estado.progreso.pendientes   = total - completados;
        _trkRT.estado.progreso.porcentaje   = total ? Math.round((completados / total) * 100) : 0;
    }
    // Punto actual: primer no completado
    const noComp = creditos.find(c => c.estatus_recoleccion !== 'completado');
    _trkRT.estado.punto_actual = noComp ? { id_detalle: noComp.id_detalle } : null;
    _trkRTRenderizar(_trkRT.estado);
}

// ─── Actualizar última ubicación del conductor ────────────
function _trkRTActualizarUbicacion(evt) {
    const pill = document.getElementById('trkUltimaUbicacion');
    const txt  = document.getElementById('trkUbicacionText');
    const time = document.getElementById('trkUbicacionTime');
    if (!pill) return;
    const lat = (evt.latitud  ?? 0).toFixed(5);
    const lng = (evt.longitud ?? 0).toFixed(5);
    txt.textContent  = `${lat}, ${lng}`;
    const ts = evt.timestamp ? new Date(evt.timestamp.endsWith('Z') ? evt.timestamp : evt.timestamp + 'Z') : new Date();
    time.textContent = ts.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    pill.classList.remove('d-none');
}

// ─── WS dot ──────────────────────────────────────────────
function _trkRTActualizarWsDot(conectado) {
    const dot = document.getElementById('trkWsDot');
    if (!dot) return;
    dot.style.background = conectado ? '#16a34a' : '#cbd5e1';
    dot.title = conectado ? 'En tiempo real' : 'Sin conexión en tiempo real';
}

// ─── Conectar WebSocket de ruta ───────────────────────────
function _trkRTConectarWS(token) {
    const wsBase = window._trackingChatWsBaseUrl;
    if (!wsBase || !_trkRT.idRuta) return;
    if (_trkRT.ws && _trkRT.ws.readyState === WebSocket.OPEN) return;
    if (_trkRT.ws) { _trkRT.ws.onclose = null; _trkRT.ws.close(); _trkRT.ws = null; }

    let ws;
    try {
        ws = new WebSocket(`${wsBase}/api/tracking/rutas/${_trkRT.idRuta}/live?token=${encodeURIComponent(token)}`);
    } catch { _trkRTActualizarWsDot(false); return; }
    _trkRT.ws = ws;

    ws.onopen = () => {
        _trkRT.wsRetries = 0;
        _trkRTActualizarWsDot(true);
        _trkRT.wsPingIv = setInterval(() => {
            if (_trkRT.ws && _trkRT.ws.readyState === WebSocket.OPEN) {
                _trkRT.ws.send(JSON.stringify({ event: 'ping' }));
            } else {
                clearInterval(_trkRT.wsPingIv); _trkRT.wsPingIv = null;
            }
        }, 30000);
    };

    ws.onmessage = evt => {
        let data;
        try { data = JSON.parse(evt.data); } catch { return; }
        if (data.event === 'pong') return;
        _trkRTProcesarEvento(data);
    };

    ws.onclose = () => {
        clearInterval(_trkRT.wsPingIv); _trkRT.wsPingIv = null;
        _trkRT.ws = null;
        _trkRTActualizarWsDot(false);
        if (_trkRT.wsRetries < 5 && _trkRT.idRuta) {
            const delay = Math.min(1000 * Math.pow(2, _trkRT.wsRetries), 30000);
            _trkRT.wsRetries++;
            _trkRT.wsRetryTO = setTimeout(async () => {
                const tok = await _trkChatObtenerToken();
                if (tok && _trkRT.idRuta) _trkRTConectarWS(tok);
            }, delay);
        }
    };

    ws.onerror = () => { /* onclose disparará */ };
}

// ─── Procesar eventos WS de ruta ─────────────────────────
function _trkRTProcesarEvento(data) {
    switch (data.event) {
        case 'init':
            // El endpoint WS puede enviar estado inicial
            if (data.creditos && _trkRT.estado) {
                _trkRT.estado.creditos = data.creditos;
                _trkRTRenderizar(_trkRT.estado);
            }
            break;
        case 'update':
            _trkRTAplicarChanges(data.changes || []);
            break;
        case 'location.update':
            _trkRTActualizarUbicacion(data);
            break;
        case 'tracking.event':
            // Recargar estado completo ante cualquier evento de tracking
            _trkRTCargarEstado();
            break;
    }
}

/* ════════════════════════════════════════════════════════════
   CHAT OPERATIVO — gestor (Sparta Ledger)
   Flujo:
     1. Usuario hace clic en btn-abrir-chat de tablaRutas.
     2. Se obtiene el detalle de la ruta para listar id_detalle.
     3. Se abre el offcanvas con una pestaña por id_detalle.
     4. Al activar una pestaña, se carga info del chat por REST.
     5. Si el chat está activo, se conecta WebSocket (solo lectura).
     6. Mensajes se envían siempre por REST.
════════════════════════════════════════════════════════════ */

// ─── Estado global del Chat ──────────────────────────────
const _trkChat = {
    rutaId:    null,   // id_ruta abierto actualmente
    activeTab: null,   // id_detalle de la pestaña activa
    jwtToken:  null,   // JWT en memoria JS (sólo para WS)
    jwtExpiry: 0,      // timestamp ms de expiración
    chats:     {},     // Map<id_detalle, chatState>
};
/* chatState = {
    id_chat, estatus, mensajes[], ws, wsRetries, wsRetryTimeout,
    unread, loadingMsgs, allLoaded, oldestMsgId
} */

// ─── Abrir chat de una ruta (entry point) ────────────────
function _trkChatCargarYAbrir(idRuta) {
    trkFetch(`/TrackingRecoleccion/obtenerDetalleRuta?id_ruta=${idRuta}`)
        .then(r => {
            if (!r.success || !r.datos) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la ruta.', confirmButtonText: 'Aceptar' });
                return;
            }
            const detalle = (r.datos.detalle || []).map(d => ({
                id_detalle:     d.id_detalle,
                id_credito:     d.id_credito,
                nombre_cliente: d.nombre_cliente || '',
                orden_ruta:     d.orden_ruta,
            }));
            _trkChatAbrir(idRuta, r.datos.nombre_ruta || `Ruta #${idRuta}`, detalle);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonText: 'Aceptar' }));
}

function _trkChatAbrir(idRuta, rutaNombre, detalleItems) {
    _trkChatLimpiarTodo();
    _trkChat.rutaId = idRuta;

    document.getElementById('chatRutaNombre').textContent = rutaNombre;

    const list        = document.getElementById('chatTabList');
    const tabsWrap    = document.getElementById('chatTabsWrap');
    const container   = document.getElementById('chatPanesContainer');
    const placeholder = document.getElementById('chatEmptyPlaceholder');

    list.innerHTML      = '';
    container.innerHTML = '';

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(
        document.getElementById('offcanvasChat')
    );
    offcanvas.show();

    if (!detalleItems || detalleItems.length === 0) {
        tabsWrap.style.display    = 'none';
        placeholder.style.display = '';
        return;
    }
    placeholder.style.display = 'none';
    tabsWrap.style.display    = '';

    detalleItems.forEach(det => {
        const id = det.id_detalle;
        _trkChat.chats[id] = {
            id_chat: null, estatus: null, mensajes: [],
            ws: null, wsRetries: 0, wsRetryTimeout: null, wsPingInterval: null,
            unread: 0, loadingMsgs: false, allLoaded: false, oldestMsgId: null,
        };

        // Tab ─────────────────────────────────────────────
        const li = document.createElement('li');
        li.className = 'nav-item';
        const credLabel = det.id_credito ? ` · ${det.id_credito}` : '';
        li.innerHTML = `
            <button class="chat-tab-link" id="chatTabBtn_${id}" data-detalle="${id}" type="button"
                    title="${_trkChatEscapeHtml(det.nombre_cliente)}">
                <span>#${id}${credLabel}</span>
                <span class="chat-status-badge chat-status-desconocido" id="chatStatusBadge_${id}">…</span>
                <span class="chat-unread-badge d-none" id="chatUnreadBadge_${id}"></span>
            </button>`;
        list.appendChild(li);
        li.querySelector('button').addEventListener('click', () => _trkChatActivarTab(id));

        // Pane ────────────────────────────────────────────
        const pane = document.createElement('div');
        pane.className = 'chat-pane';
        pane.id        = `chatPane_${id}`;
        pane.innerHTML = `
            <div class="chat-status-notice d-none" id="chatNotice_${id}"></div>
            <div class="chat-messages-wrap" id="chatMsgsWrap_${id}"></div>
            <button class="chat-new-msg-btn d-none" id="chatNewMsgBtn_${id}"
                    type="button">Nuevo mensaje ↓</button>
            <div class="chat-input-area" id="chatInputArea_${id}">
                <div class="d-flex gap-2 align-items-end">
                    <textarea class="form-control chat-textarea" id="chatTextarea_${id}"
                              placeholder="Escribe un mensaje…" rows="2"
                              maxlength="2000" disabled></textarea>
                    <button class="chat-send-btn" id="chatSendBtn_${id}"
                            type="button" disabled>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>`;
        container.appendChild(pane);

        // Listeners ───────────────────────────────────────
        document.getElementById(`chatNewMsgBtn_${id}`)
            .addEventListener('click', () => _trkChatScrollFinal(id));

        document.getElementById(`chatSendBtn_${id}`)
            .addEventListener('click', () => _trkChatEnviarMensaje(id));

        document.getElementById(`chatTextarea_${id}`)
            .addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    _trkChatEnviarMensaje(id);
                }
            });

        const wrap = document.getElementById(`chatMsgsWrap_${id}`);
        let scrollTimer = null;
        wrap.addEventListener('scroll', () => {
            if (wrap.scrollTop < 80) {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(() => _trkChatCargarMasMensajes(id), 200);
            }
            const atBottom = (wrap.scrollHeight - wrap.scrollTop - wrap.clientHeight) < 80;
            if (atBottom) {
                const btn = document.getElementById(`chatNewMsgBtn_${id}`);
                if (btn) btn.classList.add('d-none');
            }
        });
    });

    // Activar primera pestaña ──────────────────────────────
    if (detalleItems.length > 0) {
        _trkChatActivarTab(detalleItems[0].id_detalle);
    }

    // Limpiar WS al cerrar el offcanvas
    document.getElementById('offcanvasChat')
        .addEventListener('hide.bs.offcanvas', _trkChatLimpiarTodo, { once: true });
}

// ─── Gestión de pestañas ─────────────────────────────────
function _trkChatActivarTab(idDetalle) {
    document.querySelectorAll('.chat-tab-link').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.chat-pane').forEach(p => p.classList.remove('active'));

    const btn  = document.getElementById(`chatTabBtn_${idDetalle}`);
    const pane = document.getElementById(`chatPane_${idDetalle}`);
    if (btn)  btn.classList.add('active');
    if (pane) pane.classList.add('active');

    _trkChat.activeTab = idDetalle;
    _trkChatClearUnread(idDetalle);

    const state = _trkChat.chats[idDetalle];
    if (state && state.estatus === null) {
        _trkChatCargarInfo(idDetalle);
    }
}

// ─── Carga de info del chat ──────────────────────────────
async function _trkChatCargarInfo(idDetalle) {
    const state = _trkChat.chats[idDetalle];
    if (!state) return;

    const wrap = document.getElementById(`chatMsgsWrap_${idDetalle}`);
    if (wrap) {
        wrap.innerHTML = `<div class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--track-color);"></div>
        </div>`;
    }

    try {
        const r = await trkFetch(`/TrackingRecoleccion/chatInfo?id_detalle=${idDetalle}`);
        if (!r.success) {
            _trkChatMostrarError(idDetalle, r.mensaje || 'Error al cargar el chat.');
            return;
        }
        const chat = r.chat;
        state.id_chat = chat.id_chat;
        state.estatus = chat.estatus;
        _trkChatActualizarEstatusBadge(idDetalle, chat.estatus);
        _trkChatActualizarUI(idDetalle);
        await _trkChatCargarMensajes(idDetalle);
        if (chat.estatus === 'activo' || chat.estatus === 'bloqueado') {
            const token = await _trkChatObtenerToken();
            if (token) _trkChatConectarWS(idDetalle, token);
        }
    } catch {
        _trkChatMostrarError(idDetalle, 'Error de conexión al cargar el chat.');
    }
}

// ─── Carga paginada de mensajes ──────────────────────────
async function _trkChatCargarMensajes(idDetalle, beforeId = null) {
    const state = _trkChat.chats[idDetalle];
    if (!state || state.loadingMsgs || state.allLoaded) return;
    state.loadingMsgs = true;

    let url = `/TrackingRecoleccion/chatMensajes?id_detalle=${idDetalle}&limit=50`;
    if (beforeId) url += `&before_id=${beforeId}`;

    try {
        const r = await trkFetch(url);
        if (!r.success) { state.loadingMsgs = false; return; }

        const nuevos = r.mensajes || [];
        if (beforeId) {
            state.mensajes = [...nuevos, ...state.mensajes];
        } else {
            state.mensajes = nuevos;
        }
        if (nuevos.length < 50) state.allLoaded = true;
        if (state.mensajes.length > 0) {
            state.oldestMsgId = state.mensajes[0].id_mensaje;
        }
        _trkChatRenderMensajes(idDetalle, !beforeId);
    } catch { /* silent */ }
    finally { state.loadingMsgs = false; }
}

async function _trkChatCargarMasMensajes(idDetalle) {
    const state = _trkChat.chats[idDetalle];
    if (!state || state.allLoaded || state.loadingMsgs || !state.oldestMsgId) return;
    const wrap = document.getElementById(`chatMsgsWrap_${idDetalle}`);
    if (!wrap) return;
    const prevH = wrap.scrollHeight;
    await _trkChatCargarMensajes(idDetalle, state.oldestMsgId);
    requestAnimationFrame(() => { wrap.scrollTop = wrap.scrollHeight - prevH; });
}

// ─── Render de mensajes ──────────────────────────────────
function _trkChatRenderMensajes(idDetalle, scrollToBottom = true) {
    const state = _trkChat.chats[idDetalle];
    const wrap  = document.getElementById(`chatMsgsWrap_${idDetalle}`);
    if (!state || !wrap) return;

    if (state.mensajes.length === 0) {
        wrap.innerHTML = `<div class="text-center text-muted small py-5">
            <i class="fa-solid fa-comment-slash opacity-25 fa-2x mb-2 d-block"></i>
            Sin mensajes aún</div>`;
        return;
    }

    let html = state.allLoaded
        ? `<div class="text-center text-muted py-2" style="font-size:.7rem;">— Inicio de la conversación —</div>`
        : `<div class="text-center py-2" id="chatLoadMore_${idDetalle}">
               <span class="spinner-border spinner-border-sm opacity-25" style="color:var(--track-color);"></span>
           </div>`;

    state.mensajes.forEach(msg => { html += _trkChatRenderBurbuja(msg); });
    wrap.innerHTML = html;
    if (scrollToBottom) _trkChatScrollFinal(idDetalle);
}

function _trkChatRenderBurbuja(msg) {
    const tipo = (msg.tipo_actor || '').toLowerCase();
    if (tipo === 'sistema') {
        return `<div class="chat-sys-msg">${_trkChatEscapeHtml(msg.mensaje)}</div>`;
    }
    const esOut     = (tipo === 'gestor');
    const dirClass  = esOut ? 'dir-out' : 'dir-in';
    const roleClass = tipo === 'gestor' ? 'role-gestor' : 'role-conductor';
    const hora      = _trkChatFechaLocal(msg.fecha_alta);
    const actor     = tipo === 'gestor'
        ? (window._trackingChatGestorNombre || 'Gestor')
        : (msg.nombre_remitente || 'Conductor');
    return `<div class="chat-bubble-wrap ${dirClass} ${roleClass}">
        <div class="chat-bubble">${_trkChatEscapeHtml(msg.mensaje)}</div>
        <span class="chat-bubble-meta">${actor} · ${hora}</span>
    </div>`;
}

function _trkChatAgregarMensaje(idDetalle, msg) {
    const state = _trkChat.chats[idDetalle];
    if (!state) return;
    if (state.mensajes.find(m => m.id_mensaje === msg.id_mensaje)) return; // deduplicar
    state.mensajes.push(msg);

    const wrap = document.getElementById(`chatMsgsWrap_${idDetalle}`);
    if (!wrap) return;
    const atBottom = (wrap.scrollHeight - wrap.scrollTop - wrap.clientHeight) < 80;
    wrap.insertAdjacentHTML('beforeend', _trkChatRenderBurbuja(msg));

    if (atBottom) {
        _trkChatScrollFinal(idDetalle);
    } else {
        const btn = document.getElementById(`chatNewMsgBtn_${idDetalle}`);
        if (btn) btn.classList.remove('d-none');
    }

    if (_trkChat.activeTab !== idDetalle) {
        state.unread++;
        _trkChatActualizarUnreadBadge(idDetalle);
    }
}

function _trkChatScrollFinal(idDetalle) {
    const wrap = document.getElementById(`chatMsgsWrap_${idDetalle}`);
    if (wrap) wrap.scrollTo({ top: wrap.scrollHeight, behavior: 'smooth' });
    const btn = document.getElementById(`chatNewMsgBtn_${idDetalle}`);
    if (btn) btn.classList.add('d-none');
}

// ─── Enviar mensaje ──────────────────────────────────────
async function _trkChatEnviarMensaje(idDetalle) {
    const state    = _trkChat.chats[idDetalle];
    const textarea = document.getElementById(`chatTextarea_${idDetalle}`);
    const sendBtn  = document.getElementById(`chatSendBtn_${idDetalle}`);
    if (!state || state.estatus !== 'activo' || !textarea || !sendBtn) return;

    const texto = textarea.value.trim();
    if (!texto) return;

    textarea.disabled = true;
    sendBtn.disabled  = true;
    try {
        const r = await trkFetch('/TrackingRecoleccion/chatEnviarMensaje', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                id_detalle:   idDetalle,
                mensaje:      texto,
                tipo_mensaje: 'texto',
                latitud:      null,
                longitud:     null,
                metadata:     null,
            }),
        });
        if (r.success) {
            textarea.value = '';
            // Si WS no está activo, agregar localmente para feedback inmediato
            if ((!state.ws || state.ws.readyState !== WebSocket.OPEN) && r.mensaje) {
                _trkChatAgregarMensaje(idDetalle, r.mensaje);
            }
            // Si WS activo, el evento message.new lo agregará (evita duplicados)
        } else if (r.codigo_http === 409) {
            _trkChatDeshabilitarInput(idDetalle, r.mensaje || 'Chat bloqueado o cerrado.');
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || 'Error al enviar.', confirmButtonText: 'Aceptar' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo enviar el mensaje.', confirmButtonText: 'Aceptar' });
    } finally {
        textarea.disabled = false;
        sendBtn.disabled  = (state.estatus !== 'activo');
        textarea.focus();
    }
}

// ─── Token JWT (para WebSocket) ──────────────────────────
async function _trkChatObtenerToken() {
    if (_trkChat.jwtToken && _trkChat.jwtExpiry > Date.now() + 5 * 60 * 1000) {
        return _trkChat.jwtToken;
    }
    try {
        const r = await trkFetch('/TrackingRecoleccion/chatObtenerToken');
        if (r.success && r.token) {
            _trkChat.jwtToken  = r.token;
            _trkChat.jwtExpiry = r.expiry_ms || (Date.now() + 55 * 60 * 1000);
            return r.token;
        }
    } catch { /* ignore */ }
    return null;
}

// ─── WebSocket ───────────────────────────────────────────
function _trkChatConectarWS(idDetalle, token) {
    const state = _trkChat.chats[idDetalle];
    if (!state) return;
    if (state.ws && state.ws.readyState === WebSocket.OPEN) return;
    if (state.ws) { state.ws.onclose = null; state.ws.close(); state.ws = null; }

    const wsBase = window._trackingChatWsBaseUrl;
    if (!wsBase) { _trkChatActualizarWsDot(idDetalle, false); return; }

    let ws;
    try {
        ws = new WebSocket(
            `${wsBase}/api/tracking/chats/${idDetalle}/live?token=${encodeURIComponent(token)}`
        );
    } catch { _trkChatActualizarWsDot(idDetalle, false); return; }
    state.ws = ws;

    ws.onopen = () => {
        state.wsRetries = 0;
        _trkChatActualizarWsDot(idDetalle, true);
        // Heartbeat cada 30s para mantener la conexión activa en Cloud Run
        state.wsPingInterval = setInterval(() => {
            if (state.ws && state.ws.readyState === WebSocket.OPEN) {
                state.ws.send(JSON.stringify({ event: 'ping' }));
            } else {
                clearInterval(state.wsPingInterval);
                state.wsPingInterval = null;
            }
        }, 30000);
    };

    ws.onmessage = evt => {
        let data;
        try { data = JSON.parse(evt.data); } catch { return; }
        if (data.event === 'pong') return; // ignorar respuesta heartbeat
        _trkChatProcesarEventoWS(idDetalle, data);
    };

    ws.onclose = evt => {
        clearInterval(state.wsPingInterval);
        state.wsPingInterval = null;
        state.ws = null;
        _trkChatActualizarWsDot(idDetalle, false);

        // Códigos de cierre definitivo (no reintentar)
        if (evt.code === 4001) { // token inválido/expirado
            _trkChat.jwtToken = null;
            _trkChatMostrarNotice(idDetalle, 'Sesión expirada. Recarga la página.', 'cerrado');
            return;
        }
        if (evt.code === 4003) { // sin acceso
            _trkChatMostrarNotice(idDetalle, 'Sin acceso a este chat.', 'cerrado');
            return;
        }

        // Reintento con back-off exponencial (máx. 5 intentos)
        if (state.wsRetries < 5) {
            const delay = Math.min(1000 * Math.pow(2, state.wsRetries), 30000);
            state.wsRetries++;
            state.wsRetryTimeout = setTimeout(async () => {
                const tok = await _trkChatObtenerToken();
                const est = _trkChat.chats[idDetalle]?.estatus;
                if (tok && (est === 'activo' || est === 'bloqueado')) {
                    _trkChatConectarWS(idDetalle, tok);
                }
            }, delay);
        } else {
            _trkChatMostrarNotice(
                idDetalle,
                'Sin conexión en tiempo real — los mensajes se actualizan al enviar.',
                'cerrado'
            );
        }
    };
    ws.onerror = () => { /* ws.onclose disparará a continuación */ };
}

function _trkChatProcesarEventoWS(idDetalle, data) {
    const state = _trkChat.chats[idDetalle];
    if (!state) return;

    switch (data.event) {
        case 'init':
            state.mensajes  = data.mensajes || [];
            state.allLoaded = state.mensajes.length < 50;
            if (state.mensajes.length) {
                state.oldestMsgId = state.mensajes[0].id_mensaje;
            }
            _trkChatRenderMensajes(idDetalle, true);
            break;

        case 'message.new':
            if (data.mensaje) _trkChatAgregarMensaje(idDetalle, data.mensaje);
            break;

        case 'chat.unlocked':
            state.estatus = 'activo';
            _trkChatActualizarEstatusBadge(idDetalle, 'activo');
            _trkChatActualizarUI(idDetalle);
            _trkChatMostrarNotice(idDetalle, 'La ruta ha iniciado — ya puedes enviar mensajes.', 'activo', 5000);
            break;

        case 'error':
            _trkChatMostrarError(idDetalle, data.detail || 'Error en el chat.');
            break;
    }
}

function _trkChatDesconectarWS(idDetalle) {
    const state = _trkChat.chats[idDetalle];
    if (!state) return;
    if (state.wsPingInterval) { clearInterval(state.wsPingInterval); state.wsPingInterval = null; }
    if (state.wsRetryTimeout) { clearTimeout(state.wsRetryTimeout); state.wsRetryTimeout = null; }
    if (state.ws) { state.ws.onclose = null; state.ws.close(); state.ws = null; }
}

function _trkChatLimpiarTodo() {
    Object.keys(_trkChat.chats).forEach(id => _trkChatDesconectarWS(Number(id)));
    _trkChat.chats     = {};
    _trkChat.activeTab = null;
    _trkChat.rutaId    = null;
}

// ─── Actualizar UI según estatus ─────────────────────────
function _trkChatActualizarUI(idDetalle) {
    const state    = _trkChat.chats[idDetalle];
    const textarea = document.getElementById(`chatTextarea_${idDetalle}`);
    const sendBtn  = document.getElementById(`chatSendBtn_${idDetalle}`);
    const notice   = document.getElementById(`chatNotice_${idDetalle}`);
    if (!state) return;

    if (state.estatus === 'activo') {
        if (notice)   notice.classList.add('d-none');
        if (textarea) textarea.disabled = false;
        if (sendBtn)  sendBtn.disabled  = false;
    } else if (state.estatus === 'bloqueado') {
        _trkChatMostrarNotice(idDetalle, '🔒 El chat aún no está disponible — la ruta no ha iniciado.', 'bloqueado');
        if (textarea) textarea.disabled = true;
        if (sendBtn)  sendBtn.disabled  = true;
    } else if (state.estatus === 'cerrado') {
        _trkChatMostrarNotice(idDetalle, 'Esta conversación ha sido cerrada.', 'cerrado');
        if (textarea) textarea.disabled = true;
        if (sendBtn)  sendBtn.disabled  = true;
    }
}

function _trkChatActualizarEstatusBadge(idDetalle, estatus) {
    const badge = document.getElementById(`chatStatusBadge_${idDetalle}`);
    if (!badge) return;
    const MAP = {
        activo:    ['activo',    'chat-status-activo'],
        bloqueado: ['bloqueado', 'chat-status-bloqueado'],
        cerrado:   ['cerrado',   'chat-status-cerrado'],
    };
    const [label, cls] = MAP[estatus] || ['?', 'chat-status-desconocido'];
    badge.textContent = label;
    badge.className   = `chat-status-badge ${cls}`;
}

function _trkChatActualizarWsDot(idDetalle, online) {
    const btn = document.getElementById(`chatTabBtn_${idDetalle}`);
    if (!btn) return;
    let dot = btn.querySelector('.chat-ws-dot');
    if (!dot) { dot = document.createElement('span'); btn.appendChild(dot); }
    dot.className = `chat-ws-dot ${online ? 'chat-ws-on' : 'chat-ws-off'}`;
    dot.title     = online ? 'Tiempo real activo' : 'Sin tiempo real';
}

// ─── Badges no leídos ────────────────────────────────────
function _trkChatClearUnread(idDetalle) {
    const state = _trkChat.chats[idDetalle];
    if (state) state.unread = 0;
    const badge = document.getElementById(`chatUnreadBadge_${idDetalle}`);
    if (badge) badge.classList.add('d-none');
}

function _trkChatActualizarUnreadBadge(idDetalle) {
    const state = _trkChat.chats[idDetalle];
    if (!state) return;
    const badge = document.getElementById(`chatUnreadBadge_${idDetalle}`);
    if (!badge) return;
    if (state.unread > 0) {
        badge.textContent = state.unread > 99 ? '99+' : String(state.unread);
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

// ─── Helpers UI ──────────────────────────────────────────
function _trkChatMostrarNotice(idDetalle, msg, tipo, autoHideMs = 0) {
    const notice = document.getElementById(`chatNotice_${idDetalle}`);
    if (!notice) return;
    notice.textContent = msg;
    notice.className   = `chat-status-notice chat-notice-${tipo}`;
    notice.classList.remove('d-none');
    if (autoHideMs > 0) setTimeout(() => notice.classList.add('d-none'), autoHideMs);
}

function _trkChatDeshabilitarInput(idDetalle, motivo) {
    const textarea = document.getElementById(`chatTextarea_${idDetalle}`);
    const sendBtn  = document.getElementById(`chatSendBtn_${idDetalle}`);
    if (textarea) textarea.disabled = true;
    if (sendBtn)  sendBtn.disabled  = true;
    _trkChatMostrarNotice(idDetalle, motivo, 'cerrado');
}

function _trkChatMostrarError(idDetalle, msg) {
    const wrap = document.getElementById(`chatMsgsWrap_${idDetalle}`);
    if (wrap) {
        wrap.innerHTML = `<div class="alert alert-warning small m-2 py-2">${_trkChatEscapeHtml(msg)}</div>`;
    }
}

function _trkChatEscapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function _trkChatFechaLocal(iso) {
    if (!iso) return '';
    try {
        const s = iso.endsWith('Z') || /[+\-]\d{2}:\d{2}$/.test(iso) ? iso : iso + 'Z';
        return new Date(s).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch { return iso; }
}
</script>
