<div class="container-xxl flex-grow-1 container-p-y atlas-rutas-page">
    <style>
        .atlas-rutas-page { color: #22303e; }
        .atlas-rutas-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:800; margin:0; }
        .atlas-rutas-title i { color:#26344e; }
        .atlas-rutas-subtitle { color:#6b7280; font-size:.88rem; font-weight:600; margin:.2rem 0 0; }
        .atlas-rutas-field-help { color:#64748b; font-size:.72rem; font-weight:700; line-height:1.25; }
        .atlas-rutas-actions { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
        .atlas-rutas-filterbar { display:flex; align-items:end; justify-content:flex-end; gap:.75rem; flex-wrap:wrap; margin-bottom:.85rem; }
        .atlas-rutas-filterbar .atlas-rutas-filter { min-width:10rem; }
        .atlas-rutas-required::after { content:" *"; color:#ff3e1d; font-weight:900; }
        .btn-action-size { height:36px; padding:.375rem .75rem; display:inline-flex; align-items:center; justify-content:center; gap:.375rem; font-size:.875rem; font-weight:600; line-height:1; }
        .atlas-rutas-layout { display:grid; grid-template-columns:1fr; gap:1rem; align-items:start; }
        .atlas-rutas-map-panel { border:1px solid #dbe4ef; border-radius:.75rem; background:#f8fafc; min-height:24rem; padding:1rem; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden; }
        .atlas-rutas-map-title { display:flex; align-items:center; justify-content:space-between; gap:.75rem; color:#22303e; font-weight:900; }
        .atlas-rutas-map-canvas { flex:1; min-height:19rem; margin-top:.8rem; border:1px dashed #cbd5e1; border-radius:.7rem; background:linear-gradient(135deg,#eef6ff,#ffffff); display:flex; align-items:center; justify-content:center; gap:.45rem; flex-wrap:wrap; color:#64748b; font-size:.82rem; font-weight:800; text-align:center; padding:1rem; overflow:hidden; }
        .atlas-rutas-map-canvas.is-google-map { display:block; padding:0; border-style:solid; background:#eef2f7; }
        .atlas-rutas-map-marker { position:absolute; transform:translate(-50%, -100%); cursor:pointer; }
        .atlas-rutas-map-pin { width:2rem; height:2rem; border-radius:999px; background:#26344e; color:#fff; display:flex; align-items:center; justify-content:center; font-size:.82rem; font-weight:900; border:3px solid #fff; box-shadow:0 .25rem .75rem rgba(15,23,42,.25); }
        .atlas-rutas-budget-summary { border:1px solid #dbeafe; border-radius:.7rem; background:#f8fbff; padding:.75rem .85rem; display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.75rem; }
        .atlas-rutas-budget-summary .lbl { color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.025em; }
        .atlas-rutas-budget-summary .val { color:#22303e; font-size:.9rem; font-weight:900; line-height:1.15; margin-top:.12rem; }
        .atlas-rutas-budget-summary .sub { color:#7a838b; font-size:.72rem; font-weight:700; margin-top:.1rem; }
        .atlas-rutas-capacity-alert { grid-column:1 / -1; border:1px solid #ffb4a8; border-left:4px solid #ff3e1d; border-radius:.55rem; background:#fff1ef; color:#b42318; padding:.62rem .75rem; font-size:.78rem; font-weight:800; line-height:1.3; display:grid; grid-template-columns:auto 1fr auto; align-items:start; gap:.5rem; }
        .atlas-rutas-capacity-alert i { margin-top:.1rem; color:#ff3e1d; }
        .atlas-rutas-capacity-alert .btn { align-self:center; white-space:nowrap; }
        .atlas-rutas-planning-card { border:1px solid #dbe4ef; border-radius:.75rem; background:#f8fafc; padding:.9rem; display:grid; gap:.85rem; }
        .atlas-rutas-planning-card .atlas-rutas-section-title { margin:0; }
        .atlas-rutas-route-row { display:grid; grid-template-columns:10rem 10rem 13rem minmax(16rem, 1fr) auto auto; gap:.75rem; align-items:end; }
        .atlas-rutas-default-stay { border:1px solid #dbe4ef; border-radius:.6rem; background:#fff; padding:.45rem .55rem; }
        .atlas-rutas-default-stay .form-check-label { color:#22303e; font-size:.76rem; font-weight:800; }
        .atlas-rutas-default-stay-controls { display:grid; grid-template-columns:4.4rem 1fr; gap:.35rem; margin-top:.35rem; }
        .atlas-rutas-visit-controls { display:grid; grid-template-columns:7.2rem 6.6rem 5.8rem minmax(8rem, 1fr); gap:.42rem; align-items:end; margin-top:.55rem; }
        .atlas-rutas-visit-controls .form-control,
        .atlas-rutas-visit-controls .form-select { min-width:0; }
        .atlas-rutas-visit-control-sm { min-height:1.78rem; height:1.78rem; padding:.18rem .42rem; font-size:.74rem; line-height:1.1; }
        .atlas-rutas-visit-actions { display:flex; align-items:end; gap:.5rem; flex-wrap:wrap; justify-content:flex-end; }
        .atlas-rutas-visit-action-field { flex:0 0 8rem; max-width:8rem; }
        .atlas-rutas-visit-actions .form-select { width:100%; }
        .atlas-rutas-visit-label { color:#64748b; font-size:.62rem; font-weight:900; text-transform:uppercase; letter-spacing:.025em; margin-bottom:.15rem; }
        .atlas-rutas-admin-grid { display:grid; grid-template-columns:1fr; gap:1rem; margin-bottom:1rem; }
        .atlas-rutas-admin-form { display:grid; grid-template-columns:minmax(13rem, 1fr) 8rem 6rem auto; gap:.55rem; align-items:end; }
        .atlas-rutas-coverage-form { display:grid; grid-template-columns:minmax(12rem, .95fr) minmax(13rem, 1.05fr) 7rem auto; gap:.55rem; align-items:end; }
        .atlas-rutas-detail-head { display:grid; grid-template-columns:1fr auto; gap:1rem; align-items:start; margin-bottom:1rem; }
        .atlas-rutas-detail-title { color:#22303e; font-size:1rem; font-weight:900; margin:0; line-height:1.18; }
        .atlas-rutas-detail-meta { color:#7a838b; font-size:.78rem; font-weight:800; margin-top:.18rem; }
        .atlas-rutas-detail-actions { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
        .atlas-rutas-detail-grid { display:grid; grid-template-columns:1fr; gap:.85rem; }
        .atlas-rutas-credit-form { display:grid; grid-template-columns:minmax(12rem, .9fr) minmax(13rem, 1.1fr) auto; gap:.55rem; align-items:end; }
        .atlas-rutas-section-title { color:#566a7f; font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.02em; margin:1rem 0 .55rem; }
        .atlas-rutas-muted-box { border:1px dashed #dbe4ef; border-radius:.65rem; color:#6b7280; background:#f8fafc; padding:.9rem; font-size:.82rem; font-weight:700; }
        .atlas-rutas-mini { border:1px solid #e5e7eb; border-radius:.6rem; background:#fff; padding:.75rem; margin-bottom:.55rem; }
        .atlas-rutas-mini[draggable="true"] { cursor:grab; }
        .atlas-rutas-mini.is-dragging { opacity:.55; }
        .atlas-rutas-mini-row { display:flex; align-items:flex-start; justify-content:space-between; gap:.7rem; }
        .atlas-rutas-mini-actions { display:inline-flex; align-items:center; gap:.35rem; flex:0 0 auto; }
        .atlas-rutas-mini-title { color:#22303e; font-size:.88rem; font-weight:800; line-height:1.2; }
        .atlas-rutas-mini-meta { color:#697a8d; font-size:.76rem; font-weight:600; line-height:1.28; margin-top:.2rem; }
        .atlas-rutas-inline-cards { display:flex; align-items:center; gap:.3rem; flex-wrap:nowrap; margin-top:.25rem; white-space:nowrap; overflow-x:auto; scrollbar-width:thin; }
        .atlas-rutas-info-chip { display:inline-flex; align-items:center; gap:.24rem; border:1px solid #dbeafe; background:#f8fbff; color:#22303e; border-radius:999px; padding:.16rem .42rem; font-size:.68rem; font-weight:800; line-height:1.1; white-space:nowrap; flex:0 0 auto; }
        a.atlas-rutas-info-chip { color:#2563eb; text-decoration:none; }
        .atlas-rutas-builder-toolbar { display:flex; align-items:center; justify-content:flex-end; gap:.45rem; margin-bottom:.55rem; }
        .atlas-rutas-mini.is-collapsed .atlas-rutas-visit-controls,
        .atlas-rutas-mini.is-collapsed .atlas-rutas-mini-title + .atlas-rutas-mini-meta { display:none; }
        .atlas-rutas-mini.is-collapsed [data-atlas-ruta-builder-remove] { display:none; }
        .atlas-rutas-meta-chip { display:inline-flex; align-items:center; gap:.3rem; border-radius:999px; padding:.2rem .55rem; background:rgba(250, 204, 21, .18); color:#92400e; border:1px solid rgba(245, 158, 11, .35); font-size:.72rem; font-weight:900; line-height:1.1; }
        .atlas-rutas-visit-alert { margin-top:.55rem; border:1px solid #ffb4a8; border-left:4px solid #ff3e1d; border-radius:.55rem; background:#fff1ef; color:#b42318; padding:.5rem .65rem; font-size:.74rem; font-weight:800; line-height:1.3; display:flex; align-items:flex-start; gap:.45rem; }
        .atlas-rutas-visit-alert i { color:#ff3e1d; margin-top:.08rem; }
        .atlas-rutas-route-num { width:1.7rem; height:1.7rem; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; color:#fff; background:#26344e; font-size:.75rem; font-weight:800; flex:0 0 auto; }
        .atlas-rutas-sucursal-option { display:flex; align-items:center; gap:.55rem; min-width:0; }
        .atlas-rutas-sucursal-option-icon { width:1.65rem; height:1.65rem; border-radius:999px; color:#fff; display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; font-size:.78rem; }
        .atlas-rutas-sucursal-option-title { display:block; color:#22303e; font-size:.82rem; font-weight:800; line-height:1.15; white-space:normal; }
        .atlas-rutas-sucursal-option-sub { display:block; color:#7a838b; font-size:.68rem; font-weight:700; line-height:1.15; margin-top:.1rem; }
        .atlas-rutas-main { color:#22303e; font-weight:800; line-height:1.16; }
        .atlas-rutas-sub { color:#7a838b; font-size:.75rem; font-weight:700; line-height:1.18; margin-top:.14rem; }
        .atlas-rutas-badge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.22rem .62rem; font-size:.72rem; font-weight:800; white-space:nowrap; }
        .atlas-rutas-inline-badges { display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
        .atlas-rutas-meta-badge { display:inline-flex; align-items:center; gap:.3rem; border-radius:999px; padding:.18rem .55rem; font-size:.7rem; font-weight:900; background:#fff3d6; color:#a14b00; border:1px solid #ffca64; margin-top:.2rem; }
        .atlas-rutas-status-meta-badge { padding:.2rem .65rem; }
        .atlas-rutas-status-meta-badge.atlas-rutas-status-borrador { background:#eef2ff; color:#4338ca; border-color:#c7d2fe; }
        .atlas-rutas-status-meta-badge.atlas-rutas-status-asignada { background:#dbeafe; color:#1d4ed8; border-color:#93c5fd; }
        .atlas-rutas-status-meta-badge.atlas-rutas-status-en_progreso { background:#fef3c7; color:#b45309; border-color:#fcd34d; }
        .atlas-rutas-status-meta-badge.atlas-rutas-status-completada { background:#dcfce7; color:#15803d; border-color:#86efac; }
        .atlas-rutas-status-meta-badge.atlas-rutas-status-cancelada { background:#fee2e2; color:#b91c1c; border-color:#fca5a5; }
        .atlas-rutas-date-stack { display:grid; gap:.18rem; }
        .atlas-rutas-date-line { display:flex; align-items:center; gap:.35rem; color:#22303e; font-size:.78rem; font-weight:800; white-space:nowrap; }
        .atlas-rutas-date-label { color:#64748b; font-size:.67rem; font-weight:900; text-transform:uppercase; }
        .atlas-rutas-budget-badge { display:inline-flex; align-items:center; gap:.3rem; border-radius:999px; padding:.18rem .55rem; font-size:.7rem; font-weight:900; border:1px solid transparent; white-space:nowrap; }
        .atlas-rutas-budget-badge-ok { background:#dcfce7; color:#15803d; border-color:#86efac; }
        .atlas-rutas-budget-badge-empty { background:#fee2e2; color:#b91c1c; border-color:#fca5a5; }
        .atlas-rutas-money-progress { display:inline-grid; gap:.12rem; border-radius:.55rem; padding:.36rem .55rem; border:1px solid #e5e7eb; background:#f8fafc; min-width:9.5rem; }
        .atlas-rutas-money-progress .atlas-rutas-main,
        .atlas-rutas-money-progress .atlas-rutas-sub { color:inherit; }
        .atlas-rutas-money-green { background:#dcfce7; border-color:#86efac; color:#15803d; }
        .atlas-rutas-money-yellow { background:#fef9c3; border-color:#fde047; color:#854d0e; }
        .atlas-rutas-money-orange { background:#ffedd5; border-color:#fdba74; color:#c2410c; }
        .atlas-rutas-money-red { background:#fee2e2; border-color:#fca5a5; color:#b91c1c; }
        .atlas-rutas-money-neutral { background:#f8fafc; border-color:#e5e7eb; color:#64748b; }
        .atlas-rutas-badge-borrador { background:#eef2ff; color:#4338ca; }
        .atlas-rutas-badge-asignada { background:#dbeafe; color:#1d4ed8; }
        .atlas-rutas-badge-en_progreso { background:#fef3c7; color:#b45309; }
        .atlas-rutas-badge-completada { background:#dcfce7; color:#15803d; }
        .atlas-rutas-badge-cancelada { background:#fee2e2; color:#b91c1c; }
        .atlas-rutas-row-actions { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; }
        .atlas-rutas-row-actions .btn { width:2rem; height:2rem; padding:0; display:inline-flex; align-items:center; justify-content:center; }
        #modalAtlasRuta .modal-content { border:0; box-shadow:var(--bs-box-shadow-lg); }
        #modalAtlasRuta .modal-dialog { max-width:min(1280px, 96vw); }
        #modalAtlasRuta .modal-body { min-height:68vh; }
        #modalAtlasRuta .modal-footer { gap:1rem; }
        #modalAtlasRutaDetalle .modal-content { border:0; box-shadow:var(--bs-box-shadow-lg); }
        #modalAtlasRutaDetalle .modal-dialog { max-width:min(1280px, 96vw); }
        #modalAtlasRutaDetalle .modal-body { min-height:68vh; }
        #modalAtlasRutaDetalle .modal-footer { gap:1rem; }
        @media (max-width: 1199.98px) {
            .atlas-rutas-layout,
            .atlas-rutas-admin-grid { grid-template-columns:1fr; }
        }
        @media (max-width: 767.98px) {
            .atlas-rutas-admin-form,
            .atlas-rutas-coverage-form,
            .atlas-rutas-credit-form { align-items:stretch; flex-direction:column; grid-template-columns:1fr; }
            .atlas-rutas-detail-head { grid-template-columns:1fr; }
            .atlas-rutas-detail-actions { justify-content:stretch; }
            .atlas-rutas-detail-actions .btn,
            .atlas-rutas-detail-actions .form-select { width:100%; max-width:none !important; }
            .atlas-rutas-budget-summary { grid-template-columns:1fr; }
            .atlas-rutas-route-row { grid-template-columns:1fr; }
            .atlas-rutas-visit-controls { grid-template-columns:1fr; }
            .atlas-rutas-visit-actions { grid-column:auto; justify-content:stretch; }
            .atlas-rutas-actions .btn { width:100%; min-width:0; }
            .atlas-rutas-filterbar { justify-content:stretch; }
            .atlas-rutas-filterbar .atlas-rutas-filter,
            .atlas-rutas-filterbar .btn { width:100%; }
        }
    </style>

    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h1 class="atlas-rutas-title"><i class="fa-solid fa-route"></i><span>Rutas y seguimiento</span></h1>
            <p class="atlas-rutas-subtitle">Planea visitas por gestor usando las sucursales que ya tiene asignadas en ATLAS.</p>
        </div>
    </div>

    <div class="d-flex align-items-end justify-content-end gap-2 flex-wrap mb-3">
        <button type="button" class="btn btn-label-secondary btn-action-size" data-atlas-rutas-refresh>
            <i class="fa-solid fa-rotate icon-sm me-sm-2"></i><span class="d-inline-block">Actualizar</span>
        </button>
        <button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-rutas-new>
            <i class="fa-solid fa-plus icon-sm me-sm-2"></i><span class="d-inline-block">Nueva ruta</span>
        </button>
    </div>

    <div class="atlas-rutas-layout">
        <div class="card">
            <div class="card-body">
                <div class="atlas-rutas-filterbar">
                    <div class="atlas-rutas-filter">
                        <label class="form-label mb-1">Año</label>
                        <select id="atlasRutasFiltroAnio" class="form-select form-select-sm">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="atlas-rutas-filter">
                        <label class="form-label mb-1">Mes</label>
                        <select id="atlasRutasFiltroMes" class="form-select form-select-sm">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-sm btn-label-secondary" data-atlas-rutas-clear-budget-filter>
                        <i class="fa-solid fa-filter-circle-xmark me-1"></i>Limpiar
                    </button>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="table border-top" id="atlasRutasTabla">
                        <thead>
                            <tr>
                                <th>Ruta / Gestor</th>
                                <th>Fecha</th>
                                <th>Sucursales</th>
                                <th>Créditos</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="atlasRutasBody">
                            <tr><td class="text-center text-muted fw-semibold py-4" colspan="5">Cargando rutas...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalAtlasRutaDetalle" tabindex="-1" aria-labelledby="atlasDetalleTitulo" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="atlasDetalleTitulo"><i class="fa-solid fa-route me-2"></i>Detalle de ruta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                <div id="atlasRutaDetalle" class="d-none">
                    <div class="atlas-rutas-detail-head">
                        <div>
                            <h6 class="atlas-rutas-detail-title" id="atlasDetalleNombre">Ruta</h6>
                            <div class="atlas-rutas-detail-meta" id="atlasDetalleMeta"></div>
                        </div>
                        <div class="atlas-rutas-detail-actions">
                            <button type="button" class="btn btn-label-secondary btn-sm" data-atlas-detalle-ver-ruta>
                                <i class="fa-solid fa-map-location-dot me-1"></i>Ver ruta
                            </button>
                            <select id="atlasDetalleEstatus" class="form-select form-select-sm" style="max-width: 11rem;">
                                <option value="borrador">Borrador</option>
                                <option value="asignada">Asignada</option>
                                <option value="en_progreso">En progreso</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>

                    <div class="atlas-rutas-map-panel d-none mb-3" id="atlasDetalleMapaPanel">
                        <div class="atlas-rutas-map-title">
                            <span><i class="fa-solid fa-map-location-dot me-2"></i>Mapa de la ruta</span>
                            <span class="atlas-rutas-badge atlas-rutas-badge-asignada" id="atlasRutaMapaResumen">Sin ruta seleccionada</span>
                        </div>
                        <div class="atlas-rutas-map-canvas" id="atlasRutaMapaCanvas">
                            Esta ruta todavía no tiene visitas para dibujar.
                        </div>
                    </div>

                    <div id="atlasDetalleMetaTrabajo" class="atlas-rutas-budget-summary mb-3"></div>

                    <div class="atlas-rutas-section-title">Visitas de la ruta</div>
                    <div id="atlasDetalleSucursales"></div>

                    <div class="atlas-rutas-section-title">Créditos pendientes de la sucursal</div>
                    <div id="atlasDetalleCreditos" class="mt-2"></div>
                </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasRuta" tabindex="-1" aria-labelledby="atlasRutaModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="atlasRutaModalTitle"><i class="fa-solid fa-route me-2"></i>Nueva ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" data-atlas-modal-close></button>
                </div>
                <div class="modal-body">
                    <input id="atlasRutaId" type="hidden">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label atlas-rutas-required">Nombre de la ruta</label>
                            <input id="atlasRutaNombre" class="form-control" type="text" placeholder="Ej. Ruta norte viernes" maxlength="140" required>
                        </div>
                        <input id="atlasRutaPresupuesto" type="hidden">
                        <div class="col-md-2">
                            <label class="form-label atlas-rutas-required">Periodo de ruta</label>
                            <select id="atlasRutaPeriodoPresupuesto" class="form-select"></select>
                        </div>
                        <div class="col-md-4 d-none" data-atlas-ruta-regional-wrap>
                            <label class="form-label atlas-rutas-required">Regional</label>
                            <select id="atlasRutaRegional" class="form-select"></select>
                        </div>
                        <div class="col-md-4 d-none" data-atlas-ruta-supervisor-wrap>
                            <label class="form-label atlas-rutas-required">Supervisor</label>
                            <select id="atlasRutaSupervisor" class="form-select"></select>
                        </div>
                        <div class="col-md-2" data-atlas-ruta-carga-wrap>
                            <label class="form-label atlas-rutas-required">Carga de sucursales</label>
                            <div class="form-check form-switch mt-2">
                                <input id="atlasRutaTodasSucursales" class="form-check-input" type="checkbox" checked>
                                <label class="form-check-label fw-semibold" for="atlasRutaTodasSucursales">Todas</label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label atlas-rutas-required">Gestor asignado</label>
                            <select id="atlasRutaGestor" class="form-select" required></select>
                        </div>
                        <div class="col-12">
                            <div class="atlas-rutas-planning-card">
                                <div class="atlas-rutas-route-row">
                                    <div>
                                        <label class="form-label">Fecha inicio</label>
                                        <input id="atlasRutaFechaInicio" class="form-control" type="date" lang="es-MX">
                                    </div>
                                    <div>
                                        <label class="form-label">Fecha fin</label>
                                        <input id="atlasRutaFechaFin" class="form-control" type="date" lang="es-MX">
                                    </div>
                                    <div class="atlas-rutas-default-stay">
                                        <div class="form-check">
                                            <input id="atlasRutaUsarEstanciaDefault" class="form-check-input" type="checkbox">
                                            <label class="form-check-label" for="atlasRutaUsarEstanciaDefault">Estadía default</label>
                                        </div>
                                        <div class="atlas-rutas-default-stay-controls">
                                            <input id="atlasRutaEstanciaDefaultValor" class="form-control form-control-sm" type="number" min="1" value="45">
                                            <select id="atlasRutaEstanciaDefaultUnidad" class="form-select form-select-sm">
                                                <option value="minutos">Minutos</option>
                                                <option value="horas">Horas</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-none" data-atlas-ruta-sucursal-manual-wrap>
                                        <label class="form-label">Sucursal asignada al gestor</label>
                                        <select id="atlasRutaSucursal" class="form-select"></select>
                                    </div>
                                    <button class="btn btn-primary d-none" type="button" data-atlas-ruta-add-visita>
                                        <i class="fa-solid fa-plus me-1"></i>Agregar visita
                                    </button>
                                    <button class="btn btn-label-secondary d-none" type="button" data-atlas-ruta-add-todas-faltantes>
                                        <i class="fa-solid fa-layer-group me-1"></i>Agregar todas faltantes
                                    </button>
                                </div>
                                <div class="atlas-rutas-section-title">Visitas de la ruta</div>
                                <div id="atlasRutaMetaTrabajo" class="atlas-rutas-budget-summary"></div>
                                <div id="atlasRutaVisitasBuilder" class="atlas-rutas-muted-box">Selecciona un gestor para cargar sus sucursales asignadas.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label atlas-rutas-required">Tipo de ruta</label>
                            <select id="atlasRutaTipo" class="form-select" required>
                                <option value="campo">Campo</option>
                                <option value="telefonica">Telefónica</option>
                                <option value="mixta">Mixta</option>
                            </select>
                            <div id="atlasRutaTipoAyuda" class="atlas-rutas-field-help mt-1"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estatus</label>
                            <select id="atlasRutaEstatus" class="form-select">
                                <option value="borrador">Borrador</option>
                                <option value="asignada">Asignada</option>
                                <option value="en_progreso">En progreso</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                            <div id="atlasRutaEstatusAyuda" class="atlas-rutas-field-help mt-1"></div>
                        </div>
                        <input id="atlasRutaGestorManual" type="hidden">
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea id="atlasRutaObservaciones" class="form-control" rows="3" placeholder="Notas internas de asignaci?n"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" data-atlas-save-ruta><i class="fa-solid fa-floppy-disk me-1"></i>Guardar ruta</button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal" data-atlas-modal-close>Cancelar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';

    window.ATLAS_GOOGLE_MAPS_KEY = <?php echo $google_maps_api_key_js ?? '""'; ?>;
    window.ATLAS_RUTAS_CDMX_NOW = <?php echo json_encode((new DateTimeImmutable('now', new DateTimeZone('America/Mexico_City')))->format('Y-m-d\TH:i:s')); ?>;

    const AtlasRutas = {
        rutas: [],
        filtrosPresupuesto: { anio: '', mes: '' },
        catalogos: { gestores: [], sucursales: [], creditos: [] },
        horarioOperativo: { inicio: '08:00', fin: '20:00', inicio_minutos: 480, fin_minutos: 1200, duracion_minutos: 720 },
        presupuestos: [],
        presupuestoDetalle: { presupuesto: null, detalles: [], porSucursal: {} },
        rutaBuilderSucursales: [],
        visitasColapsadas: {},
        detalle: null,
        modalInst: null,
        detalleModalInst: null,
        googleMapsLoader: null,
        cdmxBaseNow: null,
        cdmxBaseClientMs: 0,
        mapaRuta: null,
        mapaRutaMarkers: [],
        mapaRutaPolyline: null,
        rutaEditando: false,

        init() {
            document.documentElement.lang = 'es-MX';
            this.inicializarRelojCdmx();
            this.aplicarRestriccionesFechas();
            this.bindEvents();
            this.cargarTodo();
        },

        bindEvents() {
            document.querySelector('[data-atlas-rutas-refresh]')?.addEventListener('click', () => this.cargarTodo());
            document.querySelector('[data-atlas-rutas-new]')?.addEventListener('click', () => this.nuevaRuta());
            document.getElementById('atlasRutaPeriodoPresupuesto')?.addEventListener('change', ev => this.cambiarPeriodoRuta(ev.target.value));
            document.getElementById('atlasRutasFiltroAnio')?.addEventListener('change', ev => {
                this.filtrosPresupuesto.anio = ev.target.value || '';
                this.renderSelectFiltroMes();
                this.renderRutas();
            });
            document.getElementById('atlasRutasFiltroMes')?.addEventListener('change', ev => {
                this.filtrosPresupuesto.mes = ev.target.value || '';
                this.renderRutas();
            });
            document.querySelector('[data-atlas-rutas-clear-budget-filter]')?.addEventListener('click', () => {
                this.filtrosPresupuesto = { anio: '', mes: '' };
                this.setValue('atlasRutasFiltroAnio', '');
                this.renderSelectFiltroMes();
                this.renderRutas();
            });
            document.querySelector('[data-atlas-save-ruta]')?.addEventListener('click', () => this.guardarRuta());
            document.querySelector('[data-atlas-ruta-add-visita]')?.addEventListener('click', () => this.agregarVisitaBuilder());
            document.querySelector('[data-atlas-ruta-add-todas-faltantes]')?.addEventListener('click', () => this.agregarSucursalesFaltantesBuilder());
            document.getElementById('atlasRutaTodasSucursales')?.addEventListener('change', ev => this.confirmarCambioModoSucursales(ev));
            document.getElementById('atlasRutaRegional')?.addEventListener('change', () => {
                this.rutaBuilderSucursales = [];
                this.visitasColapsadas = {};
                this.setValue('atlasRutaSupervisor', '');
                this.renderSelectSupervisores();
                this.renderSelectGestores();
                this.actualizarModoSucursalesRuta();
                this.renderRutaBuilder();
            });
            document.getElementById('atlasRutaSupervisor')?.addEventListener('change', () => {
                this.rutaBuilderSucursales = [];
                this.visitasColapsadas = {};
                this.renderSelectGestores();
                this.actualizarModoSucursalesRuta();
                this.renderRutaBuilder();
            });
            document.getElementById('atlasRutaGestor')?.addEventListener('change', () => {
                this.actualizarVisitasPorGestor();
            });
            document.getElementById('atlasRutaTipo')?.addEventListener('change', () => this.actualizarAyudaTipoRuta());
            document.getElementById('atlasRutaEstatus')?.addEventListener('change', () => this.actualizarAyudaEstatusRuta());
            document.getElementById('atlasRutaUsarEstanciaDefault')?.addEventListener('change', () => this.aplicarEstanciaDefaultATodas());
            document.getElementById('atlasRutaEstanciaDefaultValor')?.addEventListener('change', () => this.aplicarEstanciaDefaultATodas());
            document.getElementById('atlasRutaEstanciaDefaultUnidad')?.addEventListener('change', () => this.aplicarEstanciaDefaultATodas());
            ['atlasRutaFechaInicio', 'atlasRutaFechaFin'].forEach(id => {
                document.getElementById(id)?.addEventListener('change', async () => {
                    this.normalizarFechaInput(id);
                    this.asegurarOrdenFechas('atlasRutaFechaInicio', 'atlasRutaFechaFin');
                    await this.actualizarPresupuestoRutaPorFecha();
                    this.renderSelectPeriodoRuta();
                    this.rutaBuilderSucursales.forEach(v => this.validarRangoVisita(v));
                    this.renderRutaBuilder();
                });
            });
            document.querySelectorAll('[data-atlas-modal-close]').forEach(btn => btn.addEventListener('click', () => this.cerrarModalRuta()));
            document.getElementById('atlasDetalleEstatus')?.addEventListener('change', () => this.cambiarEstatus());
            document.querySelector('[data-atlas-detalle-ver-ruta]')?.addEventListener('click', () => this.toggleMapaDetalle());
        },

        async cargarTodo() {
            this.showWait();
            try {
                await this.cargarCatalogos();
                await this.cargarPresupuestos();
                await this.cargarRutas();
            } finally {
                this.closeWait();
            }
        },

        async cargarCatalogos() {
            const res = await this.getJson('/Atlas/getRutasGestoresCatalogos');
            if (!res.success) {
                this.toast(res.mensaje || 'No se pudieron cargar catálogos.', 'error');
                return;
            }
            this.catalogos = res.datos || { gestores: [], sucursales: [], creditos: [] };
            this.horarioOperativo = this.normalizarHorarioOperativo(this.catalogos.horario_operativo || this.horarioOperativo);
            this.aplicarRestriccionesFechas();
            this.renderSelectRegionales();
            this.renderSelectSupervisores();
            this.renderSelectGestores();
        },

        async cargarPresupuestos() {
            const anio = this.ahoraCdmx().getFullYear();
            const anios = [anio];
            if (this.ahoraCdmx().getMonth() === 11) anios.push(anio + 1);
            const historiales = [];
            for (const anioConsulta of anios) {
                const res = await this.getJson('/Atlas/getPresupuestos?anio=' + encodeURIComponent(anioConsulta));
                if (res && res.success && res.datos && Array.isArray(res.datos.historial)) {
                    historiales.push(...res.datos.historial);
                }
            }
            this.presupuestos = historiales;
            this.renderSelectPresupuestos();
            await this.cargarPresupuestoBaseDetalle();
        },

        renderSelectPresupuestos() {
            const input = document.getElementById('atlasRutaPresupuesto');
            if (input && !input.value) input.value = this.presupuestoBase()?.id || '';
            this.renderSelectPeriodoRuta();
        },

        presupuestosPermitidosRuta() {
            return (this.presupuestos || []).filter(p => {
                const fecha = `${String(p.anio || '').padStart(4, '0')}-${String(p.mes || '').padStart(2, '0')}-01`;
                return this.periodoPermitidoParaFecha(fecha).permitido;
            }).sort((a, b) => (Number(a.anio || 0) - Number(b.anio || 0)) || (Number(a.mes || 0) - Number(b.mes || 0)));
        },

        renderSelectPeriodoRuta() {
            const select = document.getElementById('atlasRutaPeriodoPresupuesto');
            if (!select) return;
            const actual = select.value || this.periodoKeyDesdeFecha(this.value('atlasRutaFechaInicio') || this.fechaMinimaOperativa());
            const opciones = this.presupuestosPermitidosRuta();
            select.innerHTML = opciones.length
                ? opciones.map(p => `<option value="${this.escape(this.periodoKey(p.anio, p.mes))}">${this.escape(p.nombre_mes || this.nombreMes(p.mes))} ${this.escape(p.anio || '')}</option>`).join('')
                : '<option value="">Sin presupuesto permitido</option>';
            if (actual && opciones.some(p => this.periodoKey(p.anio, p.mes) === actual)) {
                select.value = actual;
            } else if (opciones[0]) {
                select.value = this.periodoKey(opciones[0].anio, opciones[0].mes);
            }
        },

        periodoKey(anio, mes) {
            return `${String(anio || '').padStart(4, '0')}-${String(mes || '').padStart(2, '0')}`;
        },

        periodoKeyDesdeFecha(fecha) {
            const date = this.fechaDesdeTexto(fecha || this.fechaMinimaOperativa());
            return this.periodoKey(date.getFullYear(), date.getMonth() + 1);
        },

        async cambiarPeriodoRuta(key) {
            if (!key) return;
            const [anio, mes] = String(key).split('-').map(Number);
            if (!anio || !mes) return;
            const min = this.fechaMinimaOperativa();
            const minPeriodo = this.periodoKeyDesdeFecha(min);
            const fechaPeriodo = minPeriodo === key ? min : `${String(anio).padStart(4, '0')}-${String(mes).padStart(2, '0')}-01`;
            this.setValue('atlasRutaFechaInicio', fechaPeriodo);
            this.setValue('atlasRutaFechaFin', fechaPeriodo);
            this.aplicarRestriccionesFechas();
            this.asegurarOrdenFechas('atlasRutaFechaInicio', 'atlasRutaFechaFin');
            await this.actualizarPresupuestoRutaPorFecha();
            this.rutaBuilderSucursales.forEach(v => {
                v.fecha_inicio_visita = fechaPeriodo;
                v.fecha_fin_visita = fechaPeriodo;
                this.validarRangoVisita(v);
            });
            this.renderRutaBuilder();
        },

        presupuestoBase() {
            if (!Array.isArray(this.presupuestos) || !this.presupuestos.length) return null;
            return this.presupuestoParaFecha(this.fechaMinimaOperativa())
                || this.presupuestos[0];
        },

        presupuestoParaFecha(fecha) {
            const info = this.periodoPermitidoParaFecha(fecha);
            if (!info.permitido) return null;
            return this.presupuestos.find(p => Number(p.anio || 0) === info.anio && Number(p.mes || 0) === info.mes) || null;
        },

        periodoPermitidoParaFecha(fecha) {
            const fechaRuta = this.fechaDesdeTexto(fecha || this.fechaMinimaOperativa());
            const ahora = this.ahoraCdmx();
            const actual = { anio: ahora.getFullYear(), mes: ahora.getMonth() + 1 };
            const siguienteDate = new Date(actual.anio, actual.mes, 1);
            const siguiente = { anio: siguienteDate.getFullYear(), mes: siguienteDate.getMonth() + 1 };
            const habilitaSiguiente = new Date(siguienteDate);
            habilitaSiguiente.setDate(habilitaSiguiente.getDate() - 5);
            const esActual = fechaRuta.getFullYear() === actual.anio && (fechaRuta.getMonth() + 1) === actual.mes;
            const esSiguiente = fechaRuta.getFullYear() === siguiente.anio && (fechaRuta.getMonth() + 1) === siguiente.mes;
            if (esActual) return { permitido: true, anio: actual.anio, mes: actual.mes, tipo: 'actual' };
            if (esSiguiente && ahora >= habilitaSiguiente) return { permitido: true, anio: siguiente.anio, mes: siguiente.mes, tipo: 'siguiente' };
            return { permitido: false, anio: fechaRuta.getFullYear(), mes: fechaRuta.getMonth() + 1, tipo: esSiguiente ? 'siguiente_no_habilitado' : 'fuera_de_periodo' };
        },

        async cargarPresupuestoBaseDetalle() {
            const presupuesto = this.presupuestoBase();
            this.presupuestoDetalle = { presupuesto: presupuesto || null, detalles: [], porSucursal: {} };
            this.renderPresupuestoRutaInfo();
            if (!presupuesto || !presupuesto.id) return;
            const res = await this.getJson('/Atlas/getPresupuestoDetalle?id=' + encodeURIComponent(presupuesto.id));
            if (!res || !res.success || !res.datos) return;
            const detalles = Array.isArray(res.datos.detalles) ? res.datos.detalles : [];
            const porSucursal = {};
            detalles.forEach(d => { porSucursal[String(d.fk_sucursal || '')] = d; });
            this.presupuestoDetalle = {
                presupuesto: res.datos.presupuesto || presupuesto,
                detalles,
                porSucursal
            };
            this.renderPresupuestoRutaInfo();
        },

        async actualizarPresupuestoRutaPorFecha() {
            const fecha = this.value('atlasRutaFechaInicio') || this.fechaMinimaOperativa();
            const presupuesto = this.presupuestoParaFecha(fecha);
            const info = this.periodoPermitidoParaFecha(fecha);
            if (!info.permitido || !presupuesto) {
                const fechaValida = this.fechaMinimaOperativa();
                if (fecha === fechaValida && !presupuesto) {
                    this.presupuestoDetalle = { presupuesto: null, detalles: [], porSucursal: {} };
                    this.setValue('atlasRutaPresupuesto', '');
                    this.renderPresupuestoRutaInfo();
                    this.toast('No hay presupuesto cargado para el mes permitido de la ruta.', 'warning');
                    return;
                }
                this.setValue('atlasRutaFechaInicio', fechaValida);
                this.setValue('atlasRutaFechaFin', fechaValida);
                this.toast('Solo puedes crear rutas sobre el mes actual. El mes siguiente se habilita 5 dias antes si su presupuesto ya esta cargado.', 'warning');
                return this.actualizarPresupuestoRutaPorFecha();
            }
            const actualId = String(this.presupuestoDetalle?.presupuesto?.id || '');
            if (String(presupuesto.id || '') === actualId) {
                this.setValue('atlasRutaPresupuesto', presupuesto.id || '');
                this.renderPresupuestoRutaInfo();
                this.renderSelectPeriodoRuta();
                return;
            }
            this.setValue('atlasRutaPresupuesto', presupuesto.id || '');
            this.presupuestoDetalle = { presupuesto, detalles: [], porSucursal: {} };
            this.renderPresupuestoRutaInfo();
            this.renderSelectPeriodoRuta();
            const res = await this.getJson('/Atlas/getPresupuestoDetalle?id=' + encodeURIComponent(presupuesto.id));
            if (!res || !res.success || !res.datos) return;
            const detalles = Array.isArray(res.datos.detalles) ? res.datos.detalles : [];
            const porSucursal = {};
            detalles.forEach(d => { porSucursal[String(d.fk_sucursal || '')] = d; });
            this.presupuestoDetalle = { presupuesto: res.datos.presupuesto || presupuesto, detalles, porSucursal };
            this.renderPresupuestoRutaInfo();
            this.renderSelectPeriodoRuta();
        },

        renderPresupuestoRutaInfo() {
            const wrap = document.getElementById('atlasRutaPresupuestoInfo');
            if (!wrap) return;
            const presupuesto = this.presupuestoDetalle?.presupuesto || this.presupuestoBase();
            if (!presupuesto) {
                wrap.innerHTML = 'Sin presupuesto cargado para el periodo permitido.';
                return;
            }
            const total = Number(presupuesto.total_sucursales || this.presupuestoDetalle?.detalles?.length || 0);
            wrap.innerHTML = `<strong>${this.escape(presupuesto.nombre_mes || 'Presupuesto')} ${this.escape(presupuesto.anio || '')}</strong><br><span>${total.toLocaleString('es-MX')} sucursales con presupuesto</span>`;
        },

        async cargarRutas() {
            const res = await this.getJson('/Atlas/getRutasGestores');
            if (!res.success) {
                this.toast(res.mensaje || 'No se pudieron cargar rutas.', 'error');
                return;
            }
            this.rutas = res.datos || [];
            this.renderFiltrosPresupuestoRutas();
            this.renderRutas();
        },

        renderFiltrosPresupuestoRutas() {
            const anioSelect = document.getElementById('atlasRutasFiltroAnio');
            if (!anioSelect) return;
            const actual = this.filtrosPresupuesto.anio || anioSelect.value || '';
            const anios = new Set();
            (this.presupuestos || []).forEach(p => {
                if (p.anio) anios.add(String(p.anio));
            });
            (this.rutas || []).forEach(r => {
                if (r.presupuesto_anio) anios.add(String(r.presupuesto_anio));
            });
            anioSelect.innerHTML = '<option value="">Todos</option>' + Array.from(anios).sort((a, b) => Number(b) - Number(a)).map(anio => `<option value="${this.escape(anio)}">${this.escape(anio)}</option>`).join('');
            if (actual && anios.has(String(actual))) {
                anioSelect.value = String(actual);
                this.filtrosPresupuesto.anio = String(actual);
            } else {
                anioSelect.value = '';
                this.filtrosPresupuesto.anio = '';
            }
            this.renderSelectFiltroMes();
        },

        renderSelectFiltroMes() {
            const mesSelect = document.getElementById('atlasRutasFiltroMes');
            if (!mesSelect) return;
            const actual = this.filtrosPresupuesto.mes || mesSelect.value || '';
            const anio = this.filtrosPresupuesto.anio || '';
            const meses = new Map();
            (this.presupuestos || []).forEach(p => {
                if (anio && String(p.anio || '') !== anio) return;
                if (p.mes) meses.set(String(p.mes), p.nombre_mes || this.nombreMes(p.mes));
            });
            (this.rutas || []).forEach(r => {
                if (anio && String(r.presupuesto_anio || '') !== anio) return;
                const mesNum = r.presupuesto_mes_num || this.numeroMesPorNombre(r.presupuesto_mes);
                if (mesNum) meses.set(String(mesNum), r.presupuesto_mes || this.nombreMes(mesNum));
            });
            mesSelect.innerHTML = '<option value="">Todos</option>' + Array.from(meses.entries())
                .sort((a, b) => Number(a[0]) - Number(b[0]))
                .map(([mes, nombre]) => `<option value="${this.escape(mes)}">${this.escape(nombre)}</option>`)
                .join('');
            if (actual && meses.has(String(actual))) {
                mesSelect.value = String(actual);
                this.filtrosPresupuesto.mes = String(actual);
            } else {
                mesSelect.value = '';
                this.filtrosPresupuesto.mes = '';
            }
        },

        nombreMes(mes) {
            const nombres = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            return nombres[Number(mes || 0)] || 'Mes ' + mes;
        },

        numeroMesPorNombre(nombre) {
            const limpio = String(nombre || '').trim().toLowerCase();
            const meses = {
                enero: 1, febrero: 2, marzo: 3, abril: 4, mayo: 5, junio: 6,
                julio: 7, agosto: 8, septiembre: 9, setiembre: 9, octubre: 10, noviembre: 11, diciembre: 12
            };
            return meses[limpio] || '';
        },

        rutasFiltradasPorPresupuesto() {
            const anio = this.filtrosPresupuesto.anio || '';
            const mes = this.filtrosPresupuesto.mes || '';
            return (this.rutas || []).filter(r => {
                if (anio && String(r.presupuesto_anio || '') !== anio) return false;
                if (mes) {
                    const mesRuta = String(r.presupuesto_mes_num || this.numeroMesPorNombre(r.presupuesto_mes) || '');
                    if (mesRuta !== mes) return false;
                }
                return true;
            });
        },

        refrescarSelectBuscador(id) {
            const el = document.getElementById(id);
            if (!el || !window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
            const $el = jQuery(el);
            const $modal = $el.closest('.modal');
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                width: '100%',
                dropdownParent: $modal.length ? $modal : jQuery(document.body),
                templateResult: id === 'atlasRutaSucursal' ? (data => this.formatoSucursalSelect2(data)) : undefined,
                templateSelection: id === 'atlasRutaSucursal' ? (data => this.formatoSucursalSelect2(data)) : undefined,
                escapeMarkup: markup => markup,
                language: {
                    noResults: () => 'Sin resultados',
                    searching: () => 'Buscando...'
                }
            });
            if (id === 'atlasRutaGestor') {
                $el.off('select2:select.atlasRutaGestorAuto change.atlasRutaGestorAuto');
                $el.on('select2:select.atlasRutaGestorAuto change.atlasRutaGestorAuto', () => {
                    this.actualizarVisitasPorGestor();
                });
            }
        },

        formatoSucursalSelect2(data) {
            if (!data || !data.element || !data.id) return this.escape(data && data.text ? data.text : '');
            const el = data.element;
            const icono = el.getAttribute('data-icon') || 'fa-solid fa-location-dot';
            const color = el.getAttribute('data-color') || '#2563EB';
            const clasificacion = el.getAttribute('data-clasificacion') || 'Sin clasificación';
            return `
                <span class="atlas-rutas-sucursal-option">
                    <span class="atlas-rutas-sucursal-option-icon" style="background:${this.escape(color)}"><i class="${this.escape(icono)}"></i></span>
                    <span>
                        <span class="atlas-rutas-sucursal-option-title">${this.escape(data.text || '')}</span>
                        <span class="atlas-rutas-sucursal-option-sub">${this.escape(clasificacion)}</span>
                    </span>
                </span>
            `;
        },

        sincronizarSelectBuscador(id) {
            const el = document.getElementById(id);
            if (!el || !window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
            jQuery(el).trigger('change.select2');
        },

        contextoRutas() {
            return (this.catalogos && this.catalogos.contexto) ? this.catalogos.contexto : {};
        },

        puestoContexto() {
            return String(this.contextoRutas().puesto || '').toUpperCase();
        },

        rolRutasContexto() {
            return String(this.contextoRutas().rol_rutas || '').toLowerCase();
        },

        usaJerarquiaRuta() {
            return !this.contextoRutas().combo_gestor_completo;
        },

        esDivisionalRuta() {
            const puesto = this.puestoContexto();
            return this.usaJerarquiaRuta() && (this.rolRutasContexto() === 'divisional' || puesto.includes('DIVISIONAL') || puesto.includes('SUBDIRECTOR') || puesto.includes('DIRECTOR'));
        },

        esRegionalRuta() {
            const puesto = this.puestoContexto();
            return this.usaJerarquiaRuta() && (this.rolRutasContexto() === 'regional' || puesto.includes('REGIONAL'));
        },

        opcionesUnicasSucursales(sucursales, idCampo, nombreCampo) {
            const mapa = new Map();
            (sucursales || []).forEach(s => {
                const id = String(s[idCampo] || '').trim();
                const nombre = String(s[nombreCampo] || '').trim();
                if (!id || !nombre || /^sin\s+/i.test(nombre) || /^vacante/i.test(nombre)) return;
                if (!mapa.has(id)) mapa.set(id, { id, nombre, total_sucursales: 0 });
                mapa.get(id).total_sucursales += 1;
            });
            return Array.from(mapa.values()).sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));
        },

        sucursalesParaSupervisores() {
            let sucursales = this.catalogos.sucursales || [];
            const regionalId = this.value('atlasRutaRegional');
            if (regionalId) {
                sucursales = sucursales.filter(s => String(s.regional_id || '') === String(regionalId));
            }
            return sucursales;
        },

        sucursalesParaGestores() {
            let sucursales = this.catalogos.sucursales || [];
            const regionalId = this.value('atlasRutaRegional');
            const supervisorId = this.value('atlasRutaSupervisor');
            if (regionalId) {
                sucursales = sucursales.filter(s => String(s.regional_id || '') === String(regionalId));
            }
            if (supervisorId) {
                sucursales = sucursales.filter(s => String(s.supervisor_id || '') === String(supervisorId));
            }
            return sucursales;
        },

        renderSelectRegionales() {
            const wrap = document.querySelector('[data-atlas-ruta-regional-wrap]');
            const select = document.getElementById('atlasRutaRegional');
            if (!wrap || !select) return;
            const mostrar = this.esDivisionalRuta();
            wrap.classList.toggle('d-none', !mostrar);
            if (!mostrar) {
                select.innerHTML = '<option value="">Todos los regionales</option>';
                return;
            }
            const opciones = this.opcionesUnicasSucursales(this.catalogos.sucursales || [], 'regional_id', 'regional_nombre');
            select.innerHTML = '<option value="">Selecciona regional</option>' + opciones.map(o => {
                return `<option value="${this.escape(o.id)}">${this.escape(o.nombre + ' - ' + o.total_sucursales + ' suc.')}</option>`;
            }).join('');
            if (!opciones.length) select.innerHTML += '<option value="" disabled>No hay regionales disponibles</option>';
            this.refrescarSelectBuscador('atlasRutaRegional');
        },

        renderSelectSupervisores() {
            const wrap = document.querySelector('[data-atlas-ruta-supervisor-wrap]');
            const select = document.getElementById('atlasRutaSupervisor');
            if (!wrap || !select) return;
            const puedeMostrar = this.esRegionalRuta() || (this.esDivisionalRuta() && !!this.value('atlasRutaRegional'));
            const opciones = this.opcionesUnicasSucursales(this.sucursalesParaSupervisores(), 'supervisor_id', 'supervisor_nombre');
            const mostrar = puedeMostrar && opciones.length > 0;
            wrap.classList.toggle('d-none', !mostrar);
            if (!mostrar) {
                select.innerHTML = '<option value="">Sin supervisor disponible</option>';
                this.setValue('atlasRutaSupervisor', '');
                return;
            }
            const valorPrevio = select.value;
            select.innerHTML = '<option value="">Selecciona supervisor</option>' + opciones.map(o => {
                return `<option value="${this.escape(o.id)}">${this.escape(o.nombre + ' - ' + o.total_sucursales + ' suc.')}</option>`;
            }).join('');
            if (valorPrevio && opciones.some(o => String(o.id) === String(valorPrevio))) {
                select.value = valorPrevio;
            }
            this.refrescarSelectBuscador('atlasRutaSupervisor');
            this.sincronizarSelectBuscador('atlasRutaSupervisor');
        },

        renderSelectGestores() {
            const selectRuta = document.getElementById('atlasRutaGestor');

            if (selectRuta) {
                const gestoresCatalogo = this.gestoresDesdeSucursales(this.sucursalesParaGestores());
                selectRuta.innerHTML = '<option value="">Selecciona gestor asignado</option>' + gestoresCatalogo.map(g => {
                    const extra = g.total_sucursales ? ` · ${g.total_sucursales} suc.` : '';
                    return `<option value="${this.escape(g.key)}" data-gestor-nombre="${this.escape(g.nombre)}">${this.escape((g.etiqueta || 'Responsable') + ' · ' + g.nombre + extra)}</option>`;
                }).join('');
                if (!gestoresCatalogo.length) selectRuta.innerHTML += '<option value="" disabled>No hay gestores asignados en sucursales</option>';
                this.refrescarSelectBuscador('atlasRutaGestor');
                this.renderSelectSucursales(selectRuta.value);
            }
        },

        gestoresDesdeSucursales(sucursales = null) {
            const mapa = new Map();
            const base = sucursales || this.catalogos.sucursales || [];
            const prioridad = [
                [{ tipo: 'asesor', id: 'asesor_id', nombre: 'asesor_nombre', etiqueta: 'Gestor' }],
                [{ tipo: 'supervisor', id: 'supervisor_id', nombre: 'supervisor_nombre', etiqueta: 'Supervisor' }],
                [{ tipo: 'regional', id: 'regional_id', nombre: 'regional_nombre', etiqueta: 'Regional' }],
                [{ tipo: 'divisional', id: 'divisional_id', nombre: 'divisional_nombre', etiqueta: 'Divisional' }]
            ];
            const roles = prioridad.find(grupo => grupo.some(role => {
                return base.some(s => {
                    const id = String(s[role.id] || '').trim();
                    const nombre = String(s[role.nombre] || '').trim();
                    return id && nombre && !/^sin\s+/i.test(nombre) && !/^vacante/i.test(nombre);
                });
            })) || prioridad[0];
            base.forEach(s => {
                roles.forEach(role => {
                    const id = String(s[role.id] || '').trim();
                    const nombre = String(s[role.nombre] || '').trim();
                    if (!id || !nombre || /^sin\s+/i.test(nombre) || /^vacante/i.test(nombre)) return;
                    const key = role.tipo + ':' + id;
                    const actual = mapa.get(key) || { key, tipo: role.tipo, id, nombre, etiqueta: role.etiqueta, total_sucursales: 0 };
                    actual.total_sucursales += 1;
                    mapa.set(key, actual);
                });
            });
            const order = { asesor: 1, supervisor: 2, regional: 3, divisional: 4 };
            return Array.from(mapa.values()).sort((a, b) => {
                return (order[a.tipo] || 9) - (order[b.tipo] || 9) || a.nombre.localeCompare(b.nombre, 'es');
            });
        },

        parseGestorKey(value) {
            const raw = String(value || '').trim();
            if (!raw) return { tipo: '', id: '', nombre: '' };
            if (raw.indexOf(':') !== -1) {
                const parts = raw.split(':');
                return { tipo: parts[0] || '', id: parts.slice(1).join(':') || '', nombre: '' };
            }
            return { tipo: 'persona', id: raw, nombre: '' };
        },

        gestorSeleccionado() {
            const select = document.getElementById('atlasRutaGestor');
            const parsed = this.parseGestorKey(select ? select.value : '');
            const option = select && select.selectedOptions && select.selectedOptions[0] ? select.selectedOptions[0] : null;
            parsed.nombre = option ? (option.getAttribute('data-gestor-nombre') || option.textContent || '') : '';
            parsed.nombre = parsed.nombre.replace(/\s+·\s+\d+\s+suc\.$/i, '').trim();
            return parsed;
        },

        gestorKeyPorNombre(nombre) {
            const limpio = String(nombre || '').trim().toLowerCase();
            if (!limpio) return '';
            const gestor = this.gestoresDesdeSucursales().find(g => String(g.nombre || '').trim().toLowerCase() === limpio);
            return gestor ? gestor.key : '';
        },

        seleccionarJerarquiaPorGestorKey(gestorKey) {
            const gestor = this.parseGestorKey(gestorKey);
            if (!gestor.id) return;
            const campo = gestor.tipo + '_id';
            const sucursal = (this.catalogos.sucursales || []).find(s => String(s[campo] || '') === String(gestor.id));
            if (!sucursal) return;
            if (this.esDivisionalRuta() && sucursal.regional_id) {
                this.setValue('atlasRutaRegional', sucursal.regional_id);
                this.sincronizarSelectBuscador('atlasRutaRegional');
            }
            this.renderSelectSupervisores();
            if ((this.esRegionalRuta() || this.esDivisionalRuta()) && sucursal.supervisor_id) {
                this.setValue('atlasRutaSupervisor', sucursal.supervisor_id);
                this.sincronizarSelectBuscador('atlasRutaSupervisor');
            }
            this.renderSelectGestores();
        },

        gestorKeyDeRuta(ruta) {
            if (!ruta) return '';
            return this.gestorKeyPorNombre(ruta.gestor_nombre)
                || (ruta.gestor_persona_id ? String(ruta.gestor_persona_id) : '');
        },

        renderSelectSucursales(gestorKey, usarDetalleActual = false) {
            const gestor = this.parseGestorKey(gestorKey);
            const seleccionadas = new Set([
                ...(this.rutaBuilderSucursales || []).map(s => String(s.fk_sucursal || '')),
                ...(usarDetalleActual && this.detalle && Array.isArray(this.detalle.sucursales) ? this.detalle.sucursales.map(s => String(s.fk_sucursal || '')) : [])
            ]);
            const basePorGestor = gestor.id && gestor.tipo !== 'persona' ? this.sucursalesDeGestor(gestor) : [];
            const base = basePorGestor.length ? basePorGestor : (usarDetalleActual ? (this.catalogos.sucursales || []) : basePorGestor);
            const baseSucursales = base
                .filter(s => !seleccionadas.has(String(s.fk_sucursal || '')));
            const textoInicial = (gestor.id || usarDetalleActual) ? 'Selecciona sucursal asignada' : 'Primero selecciona gestor';
            const options = '<option value="">' + textoInicial + '</option>' + baseSucursales.map(s => {
                const label = `${s.sucursal || 'Sucursal'} · ${s.fk_sucursal}`;
                return `<option value="${this.escape(s.fk_sucursal)}" data-icon="${this.escape(s.clasificacion_icon_font || 'fa-solid fa-location-dot')}" data-color="${this.escape(s.clasificacion_color_hex || '#2563EB')}" data-clasificacion="${this.escape(s.clasificacion_nombre || 'Sin clasificacion')}">${this.escape(label)}</option>`;
            }).join('');
            const rutaPrincipal = document.getElementById('atlasRutaSucursal');
            if (rutaPrincipal) {
                rutaPrincipal.innerHTML = options;
                this.refrescarSelectBuscador('atlasRutaSucursal');
            }
        },

        sucursalesDeGestor(gestor) {
            if (!gestor || !gestor.id) return [];
            if (gestor.tipo === 'asesor') {
                return (this.catalogos.sucursales || []).filter(s => String(s.asesor_id || '') === String(gestor.id));
            }
            if (gestor.tipo === 'supervisor') {
                return (this.catalogos.sucursales || []).filter(s => String(s.supervisor_id || '') === String(gestor.id));
            }
            if (gestor.tipo === 'regional') {
                return (this.catalogos.sucursales || []).filter(s => String(s.regional_id || '') === String(gestor.id));
            }
            if (gestor.tipo === 'divisional') {
                return (this.catalogos.sucursales || []).filter(s => String(s.divisional_id || '') === String(gestor.id));
            }
            return [];
        },

        visitaDesdeSucursal(sucursal) {
            const estancia = this.estanciaDefaultRuta();
            const sugerida = this.estadiaSugerida(sucursal, estancia.activo ? estancia : null);
            return {
                fk_sucursal: sucursal.fk_sucursal,
                sucursal: sucursal.sucursal || 'Sucursal',
                latitud: sucursal.latitud,
                longitud: sucursal.longitud,
                direccion: sucursal.direccion || sucursal.direccion_sucursal || 'Sin direccion',
                prioridad: 'media',
                criterio_prioridad: 'enganches',
                fecha_inicio_visita: this.value('atlasRutaFechaInicio'),
                fecha_fin_visita: this.value('atlasRutaFechaInicio'),
                hora_llegada: null,
                estancia_valor: sugerida.valor,
                estancia_unidad: sugerida.unidad,
                estadia_sugerida: sucursal.estadia_sugerida || null
            };
        },

        cargarSucursalesGestorEnRuta(gestorKey) {
            const gestor = this.parseGestorKey(gestorKey);
            const sucursales = this.sucursalesDeGestor(gestor);
            this.rutaBuilderSucursales = sucursales.map(s => this.visitaDesdeSucursal(s));
            this.visitasColapsadas = {};
        },

        cargarTodasSucursalesRuta() {
            if (this.rutaEditando) return false;
            return document.getElementById('atlasRutaTodasSucursales')?.checked !== false;
        },

        async confirmarCambioModoSucursales(ev) {
            const input = ev?.target || document.getElementById('atlasRutaTodasSucursales');
            if (!input) return;
            const tieneCambios = (this.rutaBuilderSucursales || []).length > 0;
            if (tieneCambios) {
                const ok = await this.confirmarAccion('Se perderan los cambios que has realizado en las visitas de esta ruta. ¿Deseas continuar?');
                if (!ok) {
                    input.checked = !input.checked;
                    return;
                }
            }
            this.actualizarModoSucursalesRuta();
        },

        async confirmarAccion(mensaje) {
            if (typeof Swal !== 'undefined') {
                const res = await Swal.fire({
                    icon: 'warning',
                    title: 'Confirmar cambio',
                    text: mensaje,
                    showCancelButton: true,
                    confirmButtonText: 'Si, continuar',
                    cancelButtonText: 'Cancelar'
                });
                return !!res.isConfirmed;
            }
            return window.confirm(mensaje);
        },

        actualizarModoSucursalesRuta() {
            document.querySelector('[data-atlas-ruta-carga-wrap]')?.classList.toggle('d-none', this.rutaEditando);
            const wrapManual = document.querySelector('[data-atlas-ruta-sucursal-manual-wrap]');
            const btnManual = document.querySelector('[data-atlas-ruta-add-visita]');
            const btnTodasFaltantes = document.querySelector('[data-atlas-ruta-add-todas-faltantes]');
            const cargarTodas = this.cargarTodasSucursalesRuta();
            wrapManual?.classList.toggle('d-none', cargarTodas);
            btnManual?.classList.toggle('d-none', cargarTodas);
            btnTodasFaltantes?.classList.toggle('d-none', !this.rutaEditando);
            if (cargarTodas) {
                this.actualizarVisitasPorGestor();
                return;
            }
            if (!this.rutaEditando) {
                this.rutaBuilderSucursales = [];
                this.visitasColapsadas = {};
            }
            this.renderSelectSucursales(this.value('atlasRutaGestor'));
            this.renderRutaBuilder();
        },

        actualizarVisitasPorGestor() {
            let gestorKey = this.value('atlasRutaGestor');
            if (!gestorKey) {
                const gestor = this.gestorSeleccionado();
                if (gestor && gestor.nombre) gestorKey = this.gestorKeyPorNombre(gestor.nombre);
            }
            if (this.cargarTodasSucursalesRuta()) {
                this.cargarSucursalesGestorEnRuta(gestorKey);
            } else {
                this.rutaBuilderSucursales = [];
                this.visitasColapsadas = {};
                this.renderSelectSucursales(gestorKey);
            }
            this.renderRutaBuilder();
        },

        agregarVisitaBuilder() {
            const fk = this.value('atlasRutaSucursal');
            if (!fk) {
                this.toast('Selecciona una sucursal para agregarla a la ruta.', 'warning');
                return;
            }
            if (this.rutaBuilderSucursales.some(s => String(s.fk_sucursal) === String(fk))) {
                this.toast('Esa sucursal ya está en la ruta.', 'warning');
                return;
            }
            const sucursal = (this.catalogos.sucursales || []).find(s => String(s.fk_sucursal || '') === String(fk));
            if (!sucursal) return;
            const estancia = this.estanciaDefaultRuta();
            const sugerida = this.estadiaSugerida(sucursal, estancia.activo ? estancia : null);
            this.rutaBuilderSucursales.push({
                fk_sucursal: sucursal.fk_sucursal,
                sucursal: sucursal.sucursal || 'Sucursal',
                latitud: sucursal.latitud,
                longitud: sucursal.longitud,
                direccion: sucursal.direccion || sucursal.direccion_sucursal || 'Sin dirección',
                prioridad: 'media',
                criterio_prioridad: 'enganches',
                fecha_inicio_visita: this.value('atlasRutaFechaInicio'),
                fecha_fin_visita: this.value('atlasRutaFechaInicio'),
                hora_llegada: null,
                estancia_valor: sugerida.valor,
                estancia_unidad: sugerida.unidad,
                estadia_sugerida: sucursal.estadia_sugerida || null
            });
            this.renderSelectSucursales(this.value('atlasRutaGestor'));
            this.renderRutaBuilder();
        },

        agregarSucursalesFaltantesBuilder() {
            let gestorKey = this.value('atlasRutaGestor');
            if (!gestorKey) {
                const gestor = this.gestorSeleccionado();
                if (gestor && gestor.nombre) gestorKey = this.gestorKeyPorNombre(gestor.nombre);
            }
            const gestor = this.parseGestorKey(gestorKey);
            if (!gestor.id) {
                this.toast('Selecciona un gestor para agregar sus sucursales.', 'warning');
                return;
            }
            const existentes = new Set((this.rutaBuilderSucursales || []).map(s => String(s.fk_sucursal || '')));
            const faltantes = this.sucursalesDeGestor(gestor).filter(s => !existentes.has(String(s.fk_sucursal || '')));
            if (!faltantes.length) {
                this.toast('La ruta ya tiene todas las sucursales asignadas al gestor.', 'success');
                return;
            }
            faltantes.forEach(s => this.rutaBuilderSucursales.push(this.visitaDesdeSucursal(s)));
            this.renderSelectSucursales(gestorKey);
            this.renderRutaBuilder();
            this.toast(`${faltantes.length} sucursal(es) agregada(s) a la ruta.`, 'success');
        },

        renderRutaBuilder() {
            const wrap = document.getElementById('atlasRutaVisitasBuilder');
            if (!wrap) return;
            const visitas = this.rutaBuilderSucursales || [];
            visitas.forEach(v => this.normalizarEstanciaVisita(v, true));
            this.renderRutaMetaTrabajo();
            if (!visitas.length) {
                wrap.classList.add('atlas-rutas-muted-box');
                wrap.innerHTML = 'Selecciona un gestor para cargar sus sucursales asignadas.';
                return;
            }
            wrap.classList.remove('atlas-rutas-muted-box');
            wrap.innerHTML = `
                <div class="atlas-rutas-builder-toolbar">
                    <button class="btn btn-sm btn-label-secondary" type="button" data-atlas-ruta-collapse-all><i class="fa-solid fa-compress me-1"></i>Colapsar todo</button>
                    <button class="btn btn-sm btn-label-secondary" type="button" data-atlas-ruta-expand-all><i class="fa-solid fa-expand me-1"></i>Abrir todo</button>
                </div>
                ${visitas.map((s, idx) => {
                    const colapsada = !!this.visitasColapsadas[String(s.fk_sucursal)];
                    return `
                <div class="atlas-rutas-mini ${colapsada ? 'is-collapsed' : ''}" draggable="true" data-atlas-ruta-builder-card="${this.escape(s.fk_sucursal)}">
                    <div class="atlas-rutas-mini-row">
                        <div class="d-flex gap-2 align-items-start">
                            <span class="atlas-rutas-route-num">${idx + 1}</span>
                            <div>
                                <div class="atlas-rutas-mini-title">${this.escape(s.sucursal || 'Sucursal')}</div>
                                <div class="atlas-rutas-mini-meta">FK ${this.escape(s.fk_sucursal)} · ${this.escape(s.direccion || 'Sin dirección')}</div>
                                <div class="atlas-rutas-mini-meta"><span class="atlas-rutas-meta-chip">${this.metaSucursalTexto(s.fk_sucursal)}</span></div>
                            </div>
                        </div>
                        <span class="atlas-rutas-mini-actions">
                            <button class="btn btn-sm btn-label-secondary" type="button" data-atlas-ruta-builder-toggle="${this.escape(s.fk_sucursal)}" title="${colapsada ? 'Abrir card' : 'Colapsar card'}"><i class="fa-solid ${colapsada ? 'fa-chevron-down' : 'fa-chevron-up'}"></i></button>
                            <button class="btn btn-sm btn-label-danger" type="button" data-atlas-ruta-builder-remove="${this.escape(s.fk_sucursal)}" title="Quitar visita"><i class="fa-solid fa-trash"></i></button>
                        </span>
                    </div>
                    <div class="atlas-rutas-visit-controls">
                        <div>
                            <div class="atlas-rutas-visit-label">Fecha de visita</div>
                            <input class="form-control form-control-sm atlas-rutas-visit-control-sm" type="date" min="${this.escape(this.value('atlasRutaFechaInicio') || '')}" max="${this.escape(this.value('atlasRutaFechaFin') || this.value('atlasRutaFechaInicio') || '')}" value="${this.escape(s.fecha_inicio_visita || '')}" data-atlas-ruta-builder-field="${this.escape(s.fk_sucursal)}" data-field="fecha_inicio_visita">
                        </div>
                        <div>
                            <div class="atlas-rutas-visit-label">Estadía</div>
                            <input class="form-control form-control-sm atlas-rutas-visit-control-sm" type="number" min="1" value="${Number(s.estancia_valor || 45)}" data-atlas-ruta-builder-field="${this.escape(s.fk_sucursal)}" data-field="estancia_valor">
                        </div>
                        <div>
                            <div class="atlas-rutas-visit-label">Unidad</div>
                            <select class="form-select form-select-sm atlas-rutas-visit-control-sm" data-atlas-ruta-builder-field="${this.escape(s.fk_sucursal)}" data-field="estancia_unidad">
                                <option value="minutos"${(s.estancia_unidad || 'minutos') === 'minutos' ? ' selected' : ''}>Minutos</option>
                                ${Number(s.estancia_valor || 0) <= 5 ? '<option value="horas"' + ((s.estancia_unidad || 'minutos') === 'horas' ? ' selected' : '') + '>Horas</option>' : ''}
                            </select>
                        </div>
                        <div class="atlas-rutas-visit-actions">
                            <div class="atlas-rutas-visit-action-field"><div class="atlas-rutas-visit-label">Criterio</div><select class="form-select form-select-sm atlas-rutas-visit-control-sm" data-atlas-ruta-builder-criterio="${this.escape(s.fk_sucursal)}">
                                <option value="enganches"${(s.criterio_prioridad || 'enganches') === 'enganches' ? ' selected' : ''}>Enganches</option>
                                <option value="cash_detenido"${(s.criterio_prioridad || 'enganches') === 'cash_detenido' ? ' selected' : ''}>Cash detenido</option>
                                <option value="creditos_pendientes"${(s.criterio_prioridad || 'enganches') === 'creditos_pendientes' ? ' selected' : ''}>Créditos pendientes</option>
                                <option value="manual"${(s.criterio_prioridad || 'enganches') === 'manual' ? ' selected' : ''}>Manual</option>
                            </select></div>
                            <div class="atlas-rutas-visit-action-field"><div class="atlas-rutas-visit-label">Prioridad</div><select class="form-select form-select-sm atlas-rutas-visit-control-sm" data-atlas-ruta-builder-prioridad="${this.escape(s.fk_sucursal)}">
                                <option value="alta"${(s.prioridad || 'media') === 'alta' ? ' selected' : ''}>Alta</option>
                                <option value="media"${(s.prioridad || 'media') === 'media' ? ' selected' : ''}>Media</option>
                                <option value="baja"${(s.prioridad || 'media') === 'baja' ? ' selected' : ''}>Baja</option>
                            </select></div>
                        </div>
                    </div>
                    ${this.alertaEstadiaSucursalHtml(s)}
                </div>
            `;
                }).join('')}
            `;
            wrap.querySelectorAll('[data-atlas-ruta-builder-field]').forEach(input => input.addEventListener('change', () => {
                const fk = input.getAttribute('data-atlas-ruta-builder-field');
                const field = input.getAttribute('data-field');
                const visita = this.rutaBuilderSucursales.find(s => String(s.fk_sucursal) === String(fk));
                if (!visita || !field) return;
                visita[field] = input.value;
                if (field === 'fecha_inicio_visita') {
                    visita.fecha_fin_visita = input.value;
                }
                this.normalizarEstanciaVisita(visita);
                if (field === 'fecha_inicio_visita') {
                    this.validarRangoVisita(visita);
                }
                this.renderRutaBuilder();
            }));
            wrap.querySelector('[data-atlas-ruta-collapse-all]')?.addEventListener('click', () => {
                (this.rutaBuilderSucursales || []).forEach(s => { this.visitasColapsadas[String(s.fk_sucursal)] = true; });
                this.renderRutaBuilder();
            });
            wrap.querySelector('[data-atlas-ruta-expand-all]')?.addEventListener('click', () => {
                this.visitasColapsadas = {};
                this.renderRutaBuilder();
            });
            wrap.querySelectorAll('[data-atlas-ruta-builder-toggle]').forEach(btn => btn.addEventListener('click', () => {
                const fk = String(btn.getAttribute('data-atlas-ruta-builder-toggle') || '');
                if (!fk) return;
                if (this.visitasColapsadas[fk]) delete this.visitasColapsadas[fk];
                else this.visitasColapsadas[fk] = true;
                this.renderRutaBuilder();
            }));
            wrap.querySelectorAll('[data-atlas-ruta-builder-prioridad]').forEach(select => select.addEventListener('change', () => {
                const fk = select.getAttribute('data-atlas-ruta-builder-prioridad');
                const visita = this.rutaBuilderSucursales.find(s => String(s.fk_sucursal) === String(fk));
                if (visita) visita.prioridad = select.value || 'media';
            }));
            wrap.querySelectorAll('[data-atlas-ruta-builder-criterio]').forEach(select => select.addEventListener('change', () => {
                const fk = select.getAttribute('data-atlas-ruta-builder-criterio');
                const visita = this.rutaBuilderSucursales.find(s => String(s.fk_sucursal) === String(fk));
                if (visita) visita.criterio_prioridad = select.value || 'enganches';
            }));
            wrap.querySelectorAll('[data-atlas-ruta-builder-remove]').forEach(btn => btn.addEventListener('click', () => {
                const fk = btn.getAttribute('data-atlas-ruta-builder-remove');
                this.rutaBuilderSucursales = this.rutaBuilderSucursales.filter(s => String(s.fk_sucursal) !== String(fk));
                delete this.visitasColapsadas[String(fk)];
                this.renderSelectSucursales(this.value('atlasRutaGestor'));
                this.renderRutaBuilder();
            }));
            this.activarDragBuilder(wrap);
        },

        renderRutaMetaTrabajo() {
            const wrap = document.getElementById('atlasRutaMetaTrabajo');
            if (!wrap) return;
            const presupuesto = this.presupuestoDetalle.presupuesto || this.presupuestoBase() || {};
            const gestor = this.gestorSeleccionado();
            const sucursalesResponsable = this.sucursalesDeGestor(gestor);
            const resumenResponsable = this.resumenMetaSucursales(sucursalesResponsable);
            const resumen = this.resumenMetaVisitas();
            const nombreMes = presupuesto.nombre_mes || 'Presupuesto';
            const anio = presupuesto.anio || '';
            const factibilidad = this.validarFactibilidadOperativaRuta();
            const alertaCapacidad = !factibilidad.ok ? `
                <div class="atlas-rutas-capacity-alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>${this.escape(factibilidad.mensaje || 'La ruta no cabe en el rango seleccionado. Cambia forzosamente la Fecha inicio y la Fecha fin, y reparte las sucursales por día.')}</span>
                    <button class="btn btn-sm btn-danger" type="button" data-atlas-ruta-redistribuir-fechas><i class="fa-solid fa-calendar-days me-1"></i>Redistribuir fechas</button>
                </div>
            ` : '';
            wrap.innerHTML = `
                <div>
                    <div class="lbl">Presupuesto del responsable</div>
                    <div class="val">${this.escape(nombreMes)} ${this.escape(anio)}</div>
                    <div class="sub">${Number(resumenResponsable.sucursales).toLocaleString('es-MX')} sucursales asignadas · ${Number(resumenResponsable.creditos).toLocaleString('es-MX')} créditos · ${this.formatMoney(resumenResponsable.cash)}</div>
                </div>
                <div>
                    <div class="lbl">Meta seleccionada</div>
                    <div class="val">${Number(resumen.creditos).toLocaleString('es-MX')} créditos</div>
                    <div class="sub">${this.formatMoney(resumen.cash)} cash</div>
                </div>
                <div>
                    <div class="lbl">Visitas seleccionadas</div>
                    <div class="val">${Number(resumen.sucursales).toLocaleString('es-MX')} / ${Number(resumenResponsable.sucursales).toLocaleString('es-MX')}</div>
                    <div class="sub">${resumen.sinMeta ? Number(resumen.sinMeta).toLocaleString('es-MX') + ' sin meta cargada' : 'Todas con meta cargada'}</div>
                </div>
                ${alertaCapacidad}
            `;
            wrap.querySelector('[data-atlas-ruta-redistribuir-fechas]')?.addEventListener('click', () => this.redistribuirFechasRuta());
        },

        resumenMetaSucursales(sucursales) {
            let cash = 0;
            let creditos = 0;
            let sinMeta = 0;
            const lista = Array.isArray(sucursales) ? sucursales : [];
            lista.forEach(s => {
                const meta = this.metaSucursal(s.fk_sucursal);
                if (!meta) {
                    sinMeta += 1;
                    return;
                }
                cash += Number(meta.meta_cash || 0);
                creditos += Number(meta.meta_creditos || 0);
            });
            return { sucursales: lista.length, cash, creditos, sinMeta };
        },

        resumenMetaVisitas() {
            let cash = 0;
            let creditos = 0;
            let sinMeta = 0;
            (this.rutaBuilderSucursales || []).forEach(s => {
                const meta = this.metaSucursal(s.fk_sucursal);
                if (!meta) {
                    sinMeta += 1;
                    return;
                }
                cash += Number(meta.meta_cash || 0);
                creditos += Number(meta.meta_creditos || 0);
            });
            return { sucursales: (this.rutaBuilderSucursales || []).length, cash, creditos, sinMeta };
        },

        metaSucursal(fkSucursal) {
            return (this.presupuestoDetalle.porSucursal || {})[String(fkSucursal || '')] || null;
        },

        metaSucursalTexto(fkSucursal) {
            const meta = this.metaSucursal(fkSucursal);
            if (!meta) return 'Meta: sin presupuesto cargado para esta sucursal';
            return `Meta: ${Number(meta.meta_creditos || 0).toLocaleString('es-MX')} créditos · ${this.formatMoney(meta.meta_cash || 0)}`;
        },

        alertaEstadiaSucursal(visita) {
            const meta = this.metaSucursal(visita?.fk_sucursal);
            const creditos = Math.max(0, Number(meta?.meta_creditos || visita?.total_creditos || 0));
            if (!creditos) return '';
            const minutos = this.estanciaEnMinutos(visita.estancia_valor, visita.estancia_unidad);
            const minPorCredito = minutos / creditos;
            const creditoTxt = `${Number(creditos).toLocaleString('es-MX')} crédito${creditos === 1 ? '' : 's'}`;
            const estanciaTxt = this.duracionTexto(minutos);
            if (minPorCredito < 5) {
                return `No se puede: ${estanciaTxt} para ${creditoTxt} es insuficiente. Ajusta la estadía porque equivale a ${minPorCredito.toFixed(1)} min por crédito.`;
            }
            if (minPorCredito > 20) {
                return `Sugerencia: ${estanciaTxt} para ${creditoTxt} parece excesivo. Equivale a ${minPorCredito.toFixed(1)} min por crédito; revisa si la estadía debe reducirse.`;
            }
            return '';
        },

        alertaEstadiaSucursalHtml(visita) {
            const alerta = this.alertaEstadiaSucursal(visita);
            if (!alerta) return '';
            return `<div class="atlas-rutas-visit-alert"><i class="fa-solid fa-triangle-exclamation"></i><span>${this.escape(alerta)}</span></div>`;
        },

        mapsChipSucursalHtml(sucursal) {
            const lat = this.numeroValido(sucursal?.latitud);
            const lng = this.numeroValido(sucursal?.longitud);
            if (lat == null || lng == null) return '<span class="atlas-rutas-info-chip">Sin coordenadas</span>';
            const coords = `${lat}, ${lng}`;
            return `<a class="atlas-rutas-info-chip" href="https://www.google.com/maps?q=${encodeURIComponent(coords)}" target="_blank" rel="noopener"><i class="fa-solid fa-map-location-dot"></i>Maps</a>`;
        },

        estanciaDefaultRuta() {
            const activo = !!document.getElementById('atlasRutaUsarEstanciaDefault')?.checked;
            let valor = Math.max(1, Number(this.value('atlasRutaEstanciaDefaultValor') || 45));
            let unidad = this.value('atlasRutaEstanciaDefaultUnidad') || 'minutos';
            if (unidad === 'horas' && valor > 5) {
                unidad = 'minutos';
                this.setValue('atlasRutaEstanciaDefaultUnidad', 'minutos');
                this.toast('Si la estancia default es mayor a 5, la unidad debe ser minutos.', 'warning');
            }
            return { activo, valor, unidad };
        },

        estadiaSugerida(sucursal, fallback = null) {
            const normalizada = this.normalizarEstadiaSugerida(sucursal?.estadia_sugerida);
            if (normalizada) return normalizada;
            if (fallback && Number(fallback.valor || 0) > 0) {
                return {
                    valor: Math.max(1, Number(fallback.valor || 45)),
                    unidad: ['minutos', 'horas'].includes(fallback.unidad) ? fallback.unidad : 'minutos'
                };
            }
            return { valor: 45, unidad: 'minutos' };
        },

        normalizarEstadiaSugerida(estadia) {
            if (estadia == null || estadia === '') return null;
            if (typeof estadia === 'number') {
                return { valor: Math.max(1, Math.trunc(estadia)), unidad: 'minutos' };
            }
            if (typeof estadia === 'object') {
                const valor = Number(estadia.valor ?? estadia.estancia_valor ?? estadia.minutos ?? estadia.duracion ?? 0);
                let unidad = String(estadia.unidad ?? estadia.estancia_unidad ?? (estadia.horas ? 'horas' : 'minutos')).toLowerCase();
                unidad = unidad.startsWith('hora') ? 'horas' : 'minutos';
                if (Number.isFinite(valor) && valor > 0) return { valor: Math.trunc(valor), unidad };
                return null;
            }
            const texto = String(estadia).trim().toLowerCase();
            const match = texto.match(/(\d+(?:\.\d+)?)\s*(minuto|minutos|min|hora|horas|hr|hrs)?/);
            if (!match) return null;
            const valor = Math.max(1, Math.trunc(Number(match[1])));
            const unidadRaw = match[2] || 'minutos';
            const unidad = unidadRaw.startsWith('h') ? 'horas' : 'minutos';
            return { valor, unidad };
        },

        aplicarEstanciaDefaultATodas() {
            const estancia = this.estanciaDefaultRuta();
            if (!estancia.activo) return;
            (this.rutaBuilderSucursales || []).forEach(visita => {
                visita.estancia_valor = estancia.valor;
                visita.estancia_unidad = estancia.unidad;
                this.normalizarEstanciaVisita(visita, true);
            });
            this.renderRutaBuilder();
        },

        validarRangoVisita(visita) {
            const globalInicio = this.value('atlasRutaFechaInicio');
            const globalFin = this.value('atlasRutaFechaFin') || globalInicio;
            if (!visita) return false;
            const teniaRango = visita.fecha_inicio_visita && visita.fecha_fin_visita && visita.fecha_inicio_visita !== visita.fecha_fin_visita;
            let dia = visita.fecha_inicio_visita || visita.fecha_fin_visita || globalInicio;
            if (globalInicio && dia && dia < globalInicio) {
                dia = globalInicio;
                this.toast('La fecha de la sucursal no puede ser menor al inicio global.', 'warning');
            }
            if (globalFin && dia && dia > globalFin) {
                dia = globalFin;
                this.toast('La fecha de la sucursal no puede pasar la fecha fin global.', 'warning');
            }
            if (teniaRango) {
                this.toast('La visita de una sucursal solo puede ser de un dia. Crea otra ruta si necesita asistir otro dia.', 'warning');
            }
            visita.fecha_inicio_visita = dia;
            visita.fecha_fin_visita = dia;
            return true;
        },

        normalizarEstanciaVisita(visita, silencioso = false) {
            if (!visita) return;
            visita.estancia_valor = Math.max(1, Number(visita.estancia_valor || 45));
            if (visita.estancia_unidad === 'horas' && visita.estancia_valor > 5) {
                visita.estancia_unidad = 'minutos';
                if (!silencioso) {
                    this.toast('Si la estancia es mayor a 5, la unidad debe ser minutos.', 'warning');
                }
            }
        },

        minutosDesdeHora(hora) {
            const parts = String(hora || '00:00').split(':').map(Number);
            if (parts.length < 2 || parts.some(n => !Number.isFinite(n))) return 0;
            return (parts[0] * 60) + parts[1];
        },

        horaDesdeMinutosConfig(minutos) {
            const total = Math.max(0, Math.min((23 * 60) + 59, Math.trunc(Number(minutos || 0))));
            return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
        },

        normalizarHorarioOperativo(horario) {
            const fallback = { inicio: '08:00', fin: '20:00', inicio_minutos: 480, fin_minutos: 1200, duracion_minutos: 720 };
            const inicio = Number(horario?.inicio_minutos ?? this.minutosDesdeHora(horario?.inicio || fallback.inicio));
            const fin = Number(horario?.fin_minutos ?? this.minutosDesdeHora(horario?.fin || fallback.fin));
            if (!Number.isFinite(inicio) || !Number.isFinite(fin) || fin <= inicio) return fallback;
            return {
                inicio: this.horaDesdeMinutosConfig(inicio),
                fin: this.horaDesdeMinutosConfig(fin),
                inicio_minutos: inicio,
                fin_minutos: fin,
                duracion_minutos: fin - inicio
            };
        },

        ventanaOperativaTexto() {
            const h = this.normalizarHorarioOperativo(this.horarioOperativo);
            return `${h.inicio} a ${h.fin}`;
        },

        horaDesdeMinutos(minutos) {
            const total = Math.max(0, Math.trunc(Number(minutos || 0)));
            return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
        },

        duracionTexto(minutos) {
            const total = Math.max(0, Math.trunc(Number(minutos || 0)));
            const horas = Math.floor(total / 60);
            const mins = total % 60;
            if (horas && mins) return `${horas} h ${mins} min`;
            if (horas) return `${horas} h`;
            return `${mins} min`;
        },

        fechaMensajeTexto(fecha) {
            const parts = String(fecha || '').split('-');
            if (parts.length !== 3) return fecha || '';
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        },

        coordsVisita(visita) {
            const lat = Number(visita?.latitud);
            const lng = Number(visita?.longitud);
            if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat === 0 || lng === 0) return null;
            return { lat, lng };
        },

        distanciaKm(a, b) {
            const ca = this.coordsVisita(a);
            const cb = this.coordsVisita(b);
            if (!ca || !cb) return null;
            const rad = deg => deg * Math.PI / 180;
            const r = 6371;
            const dLat = rad(cb.lat - ca.lat);
            const dLng = rad(cb.lng - ca.lng);
            const h = Math.sin(dLat / 2) ** 2 + Math.cos(rad(ca.lat)) * Math.cos(rad(cb.lat)) * Math.sin(dLng / 2) ** 2;
            return 2 * r * Math.asin(Math.sqrt(h));
        },

        minutosTraslado(a, b) {
            const km = this.distanciaKm(a, b);
            if (km == null) return 25;
            return Math.max(15, Math.ceil((km / 28) * 60) + 5);
        },

        validarFactibilidadOperativaRuta() {
            const visitas = (this.rutaBuilderSucursales || []).map((v, idx) => ({ ...v, _orden: idx + 1 }));
            const porDia = new Map();
            visitas.forEach(v => {
                const dia = v.fecha_inicio_visita || this.value('atlasRutaFechaInicio');
                if (!porDia.has(dia)) porDia.set(dia, []);
                porDia.get(dia).push(v);
            });

            const horario = this.normalizarHorarioOperativo(this.horarioOperativo);
            const inicioDia = horario.inicio_minutos;
            const finDia = horario.fin_minutos;
            const separacion = 10;
            const comida = 60;

            for (const [dia, lista] of porDia.entries()) {
                const ordenadas = lista.slice().sort((a, b) => (a._orden || 0) - (b._orden || 0));
                let servicio = 0;
                let traslados = 0;
                let visitaAnterior = null;

                for (const visita of ordenadas) {
                    const estancia = this.estanciaEnMinutos(visita.estancia_valor, visita.estancia_unidad);
                    servicio += estancia;

                    if (estancia > (finDia - inicioDia)) {
                        return { ok: false, mensaje: `La visita ${visita._orden} requiere ${this.duracionTexto(estancia)} y no cabe en un día operativo de ${this.duracionTexto(finDia - inicioDia)}.` };
                    }
                    if (visitaAnterior) {
                        const traslado = this.minutosTraslado(visitaAnterior, visita);
                        traslados += traslado;
                    }
                    visitaAnterior = visita;
                }

                const separaciones = Math.max(0, ordenadas.length - 1) * separacion;
                const totalBase = servicio + traslados + separaciones;
                const requiereComida = totalBase >= (5 * 60);
                const totalRequerido = totalBase + (requiereComida ? comida : 0);
                if (totalRequerido > (finDia - inicioDia)) {
                    const diasNecesarios = Math.max(2, Math.ceil(totalRequerido / (finDia - inicioDia)));
                    return { ok: false, mensaje: `La ruta del ${this.fechaMensajeTexto(dia)} requiere aprox. ${this.duracionTexto(totalRequerido)} (${ordenadas.length} sucursal(es), estadía, traslados, separaciones${requiereComida ? ' y comida' : ''}) y el día operativo solo tiene ${this.duracionTexto(finDia - inicioDia)}. Necesitas cambiar forzosamente la Fecha inicio y la Fecha fin para cubrir al menos ${diasNecesarios} días y repartir las sucursales, o reducir sucursales/estadía.` };
                }
            }

            return { ok: true };
        },

        sumarDiasFecha(fecha, dias) {
            const base = this.fechaDesdeTexto(fecha || this.fechaMinimaOperativa());
            base.setDate(base.getDate() + Number(dias || 0));
            return this.fechaTexto(base);
        },

        costoVisitaEnDia(visita, listaDia) {
            const separacion = listaDia.length ? 10 : 0;
            const traslado = listaDia.length ? this.minutosTraslado(listaDia[listaDia.length - 1], visita) : 0;
            return this.estanciaEnMinutos(visita.estancia_valor, visita.estancia_unidad) + separacion + traslado;
        },

        cargaDiaConComida(minutosBase) {
            return minutosBase + (minutosBase >= (5 * 60) ? 60 : 0);
        },

        redistribuirFechasRuta() {
            const visitas = this.rutaBuilderSucursales || [];
            if (!visitas.length) return;
            const horario = this.normalizarHorarioOperativo(this.horarioOperativo);
            const capacidadDia = horario.fin_minutos - horario.inicio_minutos;
            const fechaInicio = this.value('atlasRutaFechaInicio') || this.fechaMinimaOperativa();
            const dias = [{ fecha: fechaInicio, visitas: [], cargaBase: 0 }];

            visitas.forEach(visita => {
                let dia = dias[dias.length - 1];
                let costo = this.costoVisitaEnDia(visita, dia.visitas);
                if (this.cargaDiaConComida(dia.cargaBase + costo) > capacidadDia && dia.visitas.length) {
                    dia = { fecha: this.sumarDiasFecha(fechaInicio, dias.length), visitas: [], cargaBase: 0 };
                    dias.push(dia);
                    costo = this.costoVisitaEnDia(visita, dia.visitas);
                }
                dia.visitas.push(visita);
                dia.cargaBase += costo;
                visita.fecha_inicio_visita = dia.fecha;
                visita.fecha_fin_visita = dia.fecha;
            });

            const nuevaFechaFin = dias[dias.length - 1]?.fecha || fechaInicio;
            this.setValue('atlasRutaFechaFin', nuevaFechaFin);
            this.asegurarOrdenFechas('atlasRutaFechaInicio', 'atlasRutaFechaFin');
            this.rutaBuilderSucursales.forEach(v => this.validarRangoVisita(v));
            this.toast(`Fechas redistribuidas en ${dias.length} día(s). Revisa la ruta antes de guardar.`, 'success');
            this.renderRutaBuilder();
        },

        estatusRequiereRangoVigente(estatus) {
            return !['', 'borrador', 'cancelada'].includes(String(estatus || '').toLowerCase());
        },

        inicializarRelojCdmx() {
            const raw = String(window.ATLAS_RUTAS_CDMX_NOW || '').trim();
            this.cdmxBaseNow = raw ? new Date(raw) : new Date();
            this.cdmxBaseClientMs = Date.now();
        },

        ahoraCdmx() {
            const base = this.cdmxBaseNow instanceof Date && !Number.isNaN(this.cdmxBaseNow.getTime()) ? this.cdmxBaseNow : new Date();
            return new Date(base.getTime() + (Date.now() - (this.cdmxBaseClientMs || Date.now())));
        },

        fechaTexto(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        },

        fechaDesdeTexto(fecha) {
            const parts = String(fecha || '').split('-').map(Number);
            if (parts.length === 3 && parts.every(n => Number.isFinite(n))) {
                return new Date(parts[0], parts[1] - 1, parts[2]);
            }
            return this.ahoraCdmx();
        },

        fechaLocalHoy() {
            return this.fechaTexto(this.ahoraCdmx());
        },

        fechaMinimaOperativa() {
            const ahora = this.ahoraCdmx();
            const minutosActuales = (ahora.getHours() * 60) + ahora.getMinutes();
            const horario = this.normalizarHorarioOperativo(this.horarioOperativo);
            if (minutosActuales >= horario.fin_minutos) {
                ahora.setDate(ahora.getDate() + 1);
            }
            return this.fechaTexto(ahora);
        },

        aplicarRestriccionesFechas() {
            const min = this.fechaMinimaOperativa();
            const max = this.fechaMaximaOperativa();
            ['atlasRutaFechaInicio', 'atlasRutaFechaFin'].forEach(id => {
                const input = document.getElementById(id);
                if (!input) return;
                input.lang = 'es-MX';
                input.min = min;
                input.max = max;
                input.setAttribute('data-formato-mx', 'dd/mm/aaaa');
            });
        },

        fechaMaximaOperativa() {
            const ahora = this.ahoraCdmx();
            const actualFin = new Date(ahora.getFullYear(), ahora.getMonth() + 1, 0);
            const siguienteInicio = new Date(ahora.getFullYear(), ahora.getMonth() + 1, 1);
            const habilitaSiguiente = new Date(siguienteInicio);
            habilitaSiguiente.setDate(habilitaSiguiente.getDate() - 5);
            const siguienteAnio = siguienteInicio.getFullYear();
            const siguienteMes = siguienteInicio.getMonth() + 1;
            const tienePresupuestoSiguiente = (this.presupuestos || []).some(p => Number(p.anio || 0) === siguienteAnio && Number(p.mes || 0) === siguienteMes);
            if (ahora >= habilitaSiguiente && tienePresupuestoSiguiente) {
                return this.fechaTexto(new Date(siguienteAnio, siguienteMes, 0));
            }
            return this.fechaTexto(actualFin);
        },

        normalizarFechaInput(id) {
            const input = document.getElementById(id);
            if (!input) return;
            this.aplicarRestriccionesFechas();
            const min = input.min || this.fechaMinimaOperativa();
            const max = input.max || this.fechaMaximaOperativa();
            if (input.value && input.value < min) {
                input.value = min;
                this.toast(`La fecha debe estar dentro de la ventana operativa CDMX. Despues de las ${this.normalizarHorarioOperativo(this.horarioOperativo).fin}, la fecha minima es manana.`, 'warning');
            }
            if (input.value && input.value > max) {
                input.value = max;
                this.toast('Solo puedes crear rutas sobre el mes actual. El mes siguiente se habilita 5 dias antes si su presupuesto ya esta cargado.', 'warning');
            }
        },

        asegurarOrdenFechas(inicioId, finId) {
            const inicio = document.getElementById(inicioId);
            const fin = document.getElementById(finId);
            if (!inicio || !fin) return;
            if (inicio.value && fin.value && fin.value < inicio.value) {
                fin.value = inicio.value;
            }
            if (inicio.value) fin.min = inicio.value > (fin.min || '') ? inicio.value : (fin.min || this.fechaMinimaOperativa());
        },

        fechaLocalManana() {
            const ahora = this.ahoraCdmx();
            ahora.setDate(ahora.getDate() + 1);
            const yyyy = ahora.getFullYear();
            const mm = String(ahora.getMonth() + 1).padStart(2, '0');
            const dd = String(ahora.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        },

        validarPublicacionRuta(estatus, ruta) {
            if (!this.estatusRequiereRangoVigente(estatus)) return { ok: true };
            const fechaInicio = ruta?.fecha_inicio || ruta?.fecha_ruta || '';
            const fechaFin = ruta?.fecha_fin || ruta?.fecha_ruta || fechaInicio;
            if (!fechaInicio || !fechaFin) {
                return { ok: false, mensaje: 'Actualiza las fechas de la ruta antes de publicarla.' };
            }
            if (fechaInicio > fechaFin) {
                return { ok: false, mensaje: 'La fecha fin global no puede ser menor a la fecha inicio global.' };
            }
            const hoy = this.fechaLocalHoy();
            const ahora = this.ahoraCdmx();
            const minutosActuales = (ahora.getHours() * 60) + ahora.getMinutes();
            const horario = this.normalizarHorarioOperativo(this.horarioOperativo);
            if (fechaFin < hoy) {
                return { ok: false, mensaje: 'No se puede publicar una ruta vencida. Actualiza el rango de fechas.' };
            }
            if (fechaFin === hoy && minutosActuales >= horario.fin_minutos) {
                return { ok: false, mensaje: `Ya paso la ventana operativa de visitas de hoy (${this.ventanaOperativaTexto()}). Actualiza las fechas antes de publicar.` };
            }
            return { ok: true };
        },

        normalizarHora24(value) {
            const raw = String(value || '').trim().toLowerCase();
            if (!raw) return '09:00';
            const ampm = raw.match(/\b(am|pm)\b/);
            const nums = raw.replace(/\b(am|pm)\b/g, '').trim().split(':');
            let hora = Number(nums[0]);
            let minuto = nums.length > 1 ? Number(nums[1]) : 0;
            if (!Number.isFinite(hora) || !Number.isFinite(minuto)) return '09:00';
            if (ampm) {
                if (ampm[1] === 'pm' && hora < 12) hora += 12;
                if (ampm[1] === 'am' && hora === 12) hora = 0;
            }
            const horario = this.normalizarHorarioOperativo(this.horarioOperativo);
            hora = Math.trunc(hora);
            minuto = Math.max(0, Math.min(59, Math.trunc(minuto)));
            let total = (hora * 60) + minuto;
            total = Math.max(horario.inicio_minutos, Math.min(horario.fin_minutos, total));
            return this.horaDesdeMinutosConfig(total);
        },

        estanciaEnMinutos(valor, unidad) {
            const n = Math.max(1, Number(valor || 0));
            if (unidad === 'horas') return n * 60;
            return n;
        },

        activarDragBuilder(wrap) {
            let dragged = null;
            wrap.querySelectorAll('[data-atlas-ruta-builder-card]').forEach(card => {
                card.addEventListener('dragstart', () => {
                    dragged = card;
                    card.classList.add('is-dragging');
                });
                card.addEventListener('dragend', () => {
                    card.classList.remove('is-dragging');
                    dragged = null;
                    const orden = Array.from(wrap.querySelectorAll('[data-atlas-ruta-builder-card]')).map(el => el.getAttribute('data-atlas-ruta-builder-card'));
                    this.rutaBuilderSucursales = orden.map(fk => this.rutaBuilderSucursales.find(s => String(s.fk_sucursal) === String(fk))).filter(Boolean);
                    this.renderRutaBuilder();
                });
                card.addEventListener('dragover', ev => {
                    ev.preventDefault();
                    if (!dragged || dragged === card) return;
                    const rect = card.getBoundingClientRect();
                    const before = ev.clientY < rect.top + rect.height / 2;
                    wrap.insertBefore(dragged, before ? card : card.nextSibling);
                });
            });
        },
        renderRutas() {
            const rows = this.rutasFiltradasPorPresupuesto();

            const body = document.getElementById('atlasRutasBody');
            if (!body) return;
            this.destruirTablaRutas();
            if (!rows.length) {
                body.innerHTML = '';
                this.inicializarTablaRutas();
                return;
            }
            body.innerHTML = rows.map(r => `
                <tr>
                    <td><div class="atlas-rutas-main">${this.escape(r.nombre_ruta || ('Ruta #' + r.id))}</div><div class="atlas-rutas-sub">#${this.escape(r.id)} · ${this.escape(r.tipo_ruta || 'campo')}</div></td>
                    <td><div class="atlas-rutas-main">${this.escape(r.gestor_nombre || '')}</div><div class="atlas-rutas-sub">${this.escape(r.gestor_numero_empleado || '')}</div></td>
                    <td data-atlas-presupuesto-ruta="${this.escape([r.presupuesto_mes, r.presupuesto_anio].filter(Boolean).join(' '))}"><div class="atlas-rutas-main">${this.escape(r.fecha_inicio_fmt || r.fecha_ruta_fmt || '')}</div><div class="atlas-rutas-sub">Fin ${this.escape(r.fecha_fin_fmt || r.fecha_ruta_fmt || '')}</div></td>
                    <td><div class="atlas-rutas-main">${Number(r.total_sucursales || 0).toLocaleString('es-MX')} sucursal(es)</div></td>
                    <td><div class="atlas-rutas-main" data-atlas-creditos-avance="${Number(r.total_creditos || 0).toLocaleString('es-MX')} de ${Number(r.meta_creditos || 0).toLocaleString('es-MX')} crédito(s)">$${Number(r.cash_detenido_operativo || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div><div class="atlas-rutas-sub">de $${Number(r.meta_cash || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div></td>
                    <td>${this.estatusBadgeHtml(r.estatus)}</td>
                    <td class="text-center">
                        <span class="atlas-rutas-row-actions">
                            <button class="btn btn-sm btn-primary" type="button" data-atlas-view-ruta="${Number(r.id)}" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn btn-sm btn-label-info" type="button" data-atlas-progress-ruta="${Number(r.id)}" title="Ver avance / progreso"><i class="fa-solid fa-chart-line"></i></button>
                            <button class="btn btn-sm btn-label-secondary" type="button" data-atlas-edit-ruta="${Number(r.id)}" title="Editar ruta"><i class="fa-solid fa-pen"></i></button>
                            <a class="btn btn-sm btn-label-danger" href="/Atlas/pdfRutaGestor?id=${Number(r.id)}" target="_blank" rel="noopener" title="PDF resumen tecnico"><i class="fa-solid fa-file-pdf"></i></a>
                        </span>
                    </td>
                </tr>`).join('');
            body.querySelectorAll('tr').forEach(row => {
                const celdas = row.querySelectorAll('td');
                if (celdas.length < 2) return;
                const rutaCell = celdas[0];
                const gestorCell = celdas[1];
                const gestorNombre = gestorCell.querySelector('.atlas-rutas-main')?.textContent?.trim() || '';
                const gestorNumero = gestorCell.querySelector('.atlas-rutas-sub')?.textContent?.trim() || '';
                const gestorInfo = [gestorNombre, gestorNumero].filter(Boolean).join(' - ');
                if (gestorInfo) {
                    rutaCell.insertAdjacentHTML('beforeend', `<div class="atlas-rutas-sub mt-1"><strong>Gestor:</strong> ${this.escape(gestorInfo)}</div>`);
                }
                rutaCell.querySelector('.atlas-rutas-main')?.insertAdjacentHTML('afterbegin', '<i class="fa-solid fa-route me-1"></i>');
                const rutaMeta = rutaCell.querySelector('.atlas-rutas-sub');
                if (rutaMeta) {
                    rutaMeta.classList.add('atlas-rutas-meta-badge');
                    rutaMeta.insertAdjacentHTML('beforebegin', '<div class="atlas-rutas-inline-badges mt-1"></div>');
                    rutaCell.querySelector('.atlas-rutas-inline-badges')?.appendChild(rutaMeta);
                }
                gestorCell.remove();
                const visibles = row.querySelectorAll('td');
                visibles[1]?.querySelector('.atlas-rutas-main')?.insertAdjacentHTML('afterbegin', '<i class="fa-solid fa-calendar-days me-1"></i>');
                if (visibles[1]) {
                    const fechaInicio = visibles[1].querySelector('.atlas-rutas-main')?.textContent?.trim() || '';
                    const fechaFinRaw = visibles[1].querySelector('.atlas-rutas-sub')?.textContent?.trim() || '';
                    const fechaFin = fechaFinRaw.replace(/^Fin\s+/i, '').trim();
                    const presupuestoRuta = visibles[1].getAttribute('data-atlas-presupuesto-ruta') || 'Sin presupuesto';
                    const clasePresupuesto = presupuestoRuta === 'Sin presupuesto' ? 'atlas-rutas-budget-badge-empty' : 'atlas-rutas-budget-badge-ok';
                    visibles[1].innerHTML = `
                        <div class="atlas-rutas-date-stack">
                            <div class="atlas-rutas-date-line"><i class="fa-solid fa-calendar-day"></i><span class="atlas-rutas-date-label">Inicio</span><span>${this.escape(fechaInicio)}</span></div>
                            <div class="atlas-rutas-date-line"><i class="fa-solid fa-calendar-check"></i><span class="atlas-rutas-date-label">Fin</span><span>${this.escape(fechaFin || fechaInicio)}</span></div>
                            <div class="atlas-rutas-date-line"><span class="atlas-rutas-budget-badge ${clasePresupuesto}"><i class="fa-solid fa-file-invoice-dollar"></i>${this.escape(presupuestoRuta)}</span></div>
                        </div>
                    `;
                }
                visibles[2]?.querySelector('.atlas-rutas-main')?.insertAdjacentHTML('afterbegin', '<i class="fa-solid fa-store me-1"></i>');
                visibles[3]?.querySelector('.atlas-rutas-main')?.insertAdjacentHTML('afterbegin', '<i class="fa-solid fa-coins me-1"></i>');
                this.clasificarCeldaCash(visibles[3]);
                const avanceCreditos = visibles[3]?.querySelector('[data-atlas-creditos-avance]')?.getAttribute('data-atlas-creditos-avance') || '';
                if (avanceCreditos) {
                    visibles[2]?.insertAdjacentHTML('beforeend', `<div class="atlas-rutas-sub mt-1"><i class="fa-solid fa-coins me-1"></i>${this.escape(avanceCreditos)}</div>`);
                }
                const estatusBadge = visibles[4]?.querySelector('.atlas-rutas-badge');
                if (estatusBadge) {
                    const estatusKey = estatusBadge.getAttribute('data-atlas-ruta-estatus') || 'borrador';
                    const rutaMetaBadge = rutaCell.querySelector('.atlas-rutas-meta-badge');
                    if (rutaMetaBadge) {
                        rutaMetaBadge.innerHTML = `${estatusBadge.innerHTML}, ${rutaMetaBadge.innerHTML}`;
                        rutaMetaBadge.classList.add('atlas-rutas-status-meta-badge', `atlas-rutas-status-${estatusKey}`);
                    } else {
                        rutaCell.insertAdjacentHTML('beforeend', `<div class="mt-1">${estatusBadge.outerHTML}</div>`);
                    }
                    visibles[4].remove();
                }
            });
            body.querySelectorAll('[data-atlas-view-ruta]').forEach(btn => btn.addEventListener('click', () => this.verDetalle(btn.getAttribute('data-atlas-view-ruta'))));
            body.querySelectorAll('[data-atlas-progress-ruta]').forEach(btn => btn.addEventListener('click', () => this.verAvanceRuta(btn.getAttribute('data-atlas-progress-ruta'))));
            body.querySelectorAll('[data-atlas-edit-ruta]').forEach(btn => btn.addEventListener('click', () => this.editarRuta(btn.getAttribute('data-atlas-edit-ruta'))));
            this.inicializarTablaRutas();
        },

        destruirTablaRutas() {
            if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#atlasRutasTabla')) {
                jQuery('#atlasRutasTabla').DataTable().destroy();
            }
        },

        inicializarTablaRutas() {
            if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable || !document.getElementById('atlasRutasTabla')) return;
            if (jQuery.fn.DataTable.isDataTable('#atlasRutasTabla')) return;
            if (typeof window.configuraTabla === 'function') {
                window.configuraTabla('#atlasRutasTabla', {
                    registrosPorPagina: 10,
                    order: []
                });
                return;
            }
            jQuery('#atlasRutasTabla').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [],
                autoWidth: false,
                language: {
                    emptyTable: 'No hay rutas registradas',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    search: 'Buscar:',
                    zeroRecords: 'No se encontraron resultados',
                    paginate: { first: '«', last: '»', next: '›', previous: '‹' }
                }
            });
        },

        async nuevaRuta() {
            const fechaMinima = this.fechaMinimaOperativa();
            this.rutaEditando = false;
            this.setModalRutaTitle('Nueva ruta', 'fa-solid fa-route');
            this.setValue('atlasRutaId', '');
            this.setValue('atlasRutaNombre', '');
            this.setValue('atlasRutaPresupuesto', this.presupuestoBase()?.id || '');
            this.setValue('atlasRutaRegional', '');
            this.setValue('atlasRutaSupervisor', '');
            this.setValue('atlasRutaGestor', '');
            const todasSucursales = document.getElementById('atlasRutaTodasSucursales');
            if (todasSucursales) todasSucursales.checked = true;
            const estanciaDefault = document.getElementById('atlasRutaUsarEstanciaDefault');
            if (estanciaDefault) estanciaDefault.checked = false;
            this.setValue('atlasRutaEstanciaDefaultValor', 45);
            this.setValue('atlasRutaEstanciaDefaultUnidad', 'minutos');
            this.setValue('atlasRutaFechaInicio', fechaMinima);
            this.setValue('atlasRutaFechaFin', fechaMinima);
            this.renderSelectPeriodoRuta();
            await this.actualizarPresupuestoRutaPorFecha();
            this.rutaBuilderSucursales = [];
            this.visitasColapsadas = {};
            this.renderSelectRegionales();
            this.renderSelectSupervisores();
            this.renderSelectGestores();
            this.sincronizarSelectBuscador('atlasRutaGestor');
            this.renderSelectSucursales('');
            this.actualizarModoSucursalesRuta();
            this.renderRutaBuilder();
            this.aplicarRestriccionesFechas();
            this.setValue('atlasRutaTipo', 'campo');
            this.setValue('atlasRutaEstatus', 'borrador');
            this.actualizarAyudaTipoRuta();
            this.actualizarAyudaEstatusRuta();
            this.setValue('atlasRutaGestorManual', '');
            this.setValue('atlasRutaObservaciones', '');
            this.abrirModalRuta();
        },

        editarRuta(id) {
            const ruta = this.rutas.find(r => Number(r.id) === Number(id));
            if (!ruta) return;
            this.rutaEditando = true;
            this.visitasColapsadas = {};
            this.verDetalle(id, false).then(() => {
                this.rutaBuilderSucursales = (this.detalle && Array.isArray(this.detalle.sucursales) ? this.detalle.sucursales : []).map(s => {
                    const sugerida = this.estadiaSugerida(s);
                    return {
                        fk_sucursal: s.fk_sucursal,
                        sucursal: s.sucursal || 'Sucursal',
                        latitud: s.latitud,
                        longitud: s.longitud,
                        direccion: s.direccion || 'Sin dirección',
                        prioridad: s.prioridad_visita || s.prioridad || 'media',
                        criterio_prioridad: s.criterio_prioridad_visita || s.criterio_prioridad || 'enganches',
                        fecha_inicio_visita: s.fecha_inicio_visita || ruta.fecha_inicio || ruta.fecha_ruta || '',
                        fecha_fin_visita: s.fecha_inicio_visita || ruta.fecha_inicio || ruta.fecha_ruta || '',
                        hora_llegada: null,
                        estancia_valor: s.estancia_valor || sugerida.valor,
                        estancia_unidad: s.estancia_unidad || sugerida.unidad,
                        estadia_sugerida: s.estadia_sugerida || null
                    };
                });
                const todasSucursales = document.getElementById('atlasRutaTodasSucursales');
                if (todasSucursales) todasSucursales.checked = false;
                this.actualizarModoSucursalesRuta();
                this.renderRutaBuilder();
            });
            this.setModalRutaTitle('Editar ruta', 'fa-solid fa-pen-to-square');
            this.setValue('atlasRutaId', ruta.id);
            this.setValue('atlasRutaNombre', ruta.nombre_ruta || ('Ruta #' + ruta.id));
            this.setValue('atlasRutaPresupuesto', ruta.presupuesto_id || this.presupuestoBase()?.id || '');
            const gestorKey = this.gestorKeyPorNombre(ruta.gestor_nombre) || (ruta.gestor_persona_id ? String(ruta.gestor_persona_id) : '');
            this.setValue('atlasRutaRegional', '');
            this.setValue('atlasRutaSupervisor', '');
            this.renderSelectRegionales();
            this.renderSelectSupervisores();
            this.seleccionarJerarquiaPorGestorKey(gestorKey);
            this.setValue('atlasRutaGestor', gestorKey);
            this.sincronizarSelectBuscador('atlasRutaGestor');
            this.renderSelectSucursales(gestorKey);
            this.setValue('atlasRutaFechaInicio', ruta.fecha_inicio || ruta.fecha_ruta || '');
            this.setValue('atlasRutaFechaFin', ruta.fecha_fin || ruta.fecha_ruta || '');
            this.renderSelectPeriodoRuta();
            this.aplicarRestriccionesFechas();
            this.asegurarOrdenFechas('atlasRutaFechaInicio', 'atlasRutaFechaFin');
            this.actualizarPresupuestoRutaPorFecha();
            this.setValue('atlasRutaTipo', ruta.tipo_ruta || 'campo');
            this.setValue('atlasRutaEstatus', ruta.estatus || 'borrador');
            this.actualizarAyudaTipoRuta();
            this.actualizarAyudaEstatusRuta();
            this.setValue('atlasRutaGestorManual', ruta.gestor_persona_id ? '' : (ruta.gestor_nombre || ''));
            this.setValue('atlasRutaObservaciones', ruta.observaciones || '');
            this.abrirModalRuta();
        },

        abrirModalRuta() {
            const el = document.getElementById('modalAtlasRuta');
            if (!el) return;
            if (window.bootstrap && bootstrap.Modal) {
                this.modalInst = bootstrap.Modal.getOrCreateInstance(el);
                this.modalInst.show();
                return;
            }
            if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
                jQuery(el).modal('show');
                return;
            }
            el.classList.add('show');
            el.style.display = 'block';
            el.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        },

        cerrarModalRuta() {
            const el = document.getElementById('modalAtlasRuta');
            if (!el) return;
            if (window.bootstrap && bootstrap.Modal) {
                (bootstrap.Modal.getInstance(el) || this.modalInst || bootstrap.Modal.getOrCreateInstance(el)).hide();
                return;
            }
            if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
                jQuery(el).modal('hide');
                return;
            }
            el.classList.remove('show');
            el.style.display = 'none';
            el.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        },

        async guardarRuta() {
            const gestor = this.gestorSeleccionado();
            const sucursalesDisponibles = this.sucursalesDeGestor(gestor);
            const nombreRuta = this.value('atlasRutaNombre').trim();
            if (!nombreRuta) {
                this.toast('Captura el nombre de la ruta.', 'warning');
                document.getElementById('atlasRutaNombre')?.focus();
                return;
            }
            if (!gestor.id) {
                this.toast('Selecciona un gestor para crear la ruta.', 'warning');
                return;
            }
            await this.actualizarPresupuestoRutaPorFecha();
            if (!this.value('atlasRutaPresupuesto')) {
                this.toast('No hay presupuesto disponible para la fecha de inicio de la ruta.', 'warning');
                return;
            }
            if (!this.value('atlasRutaTipo')) {
                this.toast('Selecciona el tipo de ruta.', 'warning');
                document.getElementById('atlasRutaTipo')?.focus();
                return;
            }
            if (!this.rutaBuilderSucursales.length) {
                this.toast('Agrega al menos una visita a la ruta.', 'warning');
                return;
            }
            if (sucursalesDisponibles.length > 1 && this.rutaBuilderSucursales.length < 2) {
                this.toast('La ruta debe tener más de una visita cuando el gestor tiene más sucursales disponibles.', 'warning');
                return;
            }
            const fechaGlobalInicio = this.value('atlasRutaFechaInicio');
            const fechaGlobalFin = this.value('atlasRutaFechaFin') || fechaGlobalInicio;
            if (fechaGlobalInicio && fechaGlobalFin && fechaGlobalInicio > fechaGlobalFin) {
                this.toast('La fecha fin global no puede ser menor a la fecha inicio global.', 'warning');
                return;
            }
            const publicacion = this.validarPublicacionRuta(this.value('atlasRutaEstatus'), {
                fecha_inicio: fechaGlobalInicio,
                fecha_fin: fechaGlobalFin
            });
            if (!publicacion.ok) {
                this.toast(publicacion.mensaje, 'warning');
                return;
            }
            for (const visita of this.rutaBuilderSucursales) {
                this.validarRangoVisita(visita);
                if ((fechaGlobalInicio && visita.fecha_inicio_visita && visita.fecha_inicio_visita < fechaGlobalInicio) || (fechaGlobalFin && visita.fecha_fin_visita && visita.fecha_fin_visita > fechaGlobalFin)) {
                    this.toast('Todas las visitas deben respetar el rango global de la ruta.', 'warning');
                    this.renderRutaBuilder();
                    return;
                }
                const meta = this.metaSucursal(visita.fk_sucursal);
                const creditos = Math.max(0, Number(meta?.meta_creditos || visita.total_creditos || 0));
                const minutos = this.estanciaEnMinutos(visita.estancia_valor, visita.estancia_unidad);
                if (creditos > 0 && (minutos / creditos) < 5) {
                    this.toast(`La estadía de ${visita.sucursal || 'una sucursal'} es insuficiente para ${creditos} crédito(s). Ajusta la estadía antes de guardar.`, 'warning');
                    this.renderRutaBuilder();
                    return;
                }
            }
            const factibilidad = this.validarFactibilidadOperativaRuta();
            if (!factibilidad.ok) {
                this.toast(factibilidad.mensaje || 'La ruta no es factible con los tiempos capturados.', 'warning');
                return;
            }
            const payload = {
                id: this.value('atlasRutaId'),
                nombre_ruta: nombreRuta,
                presupuesto_id: this.value('atlasRutaPresupuesto') || this.presupuestoBase()?.id || '',
                gestor_persona_id: gestor.id || '',
                gestor_nombre: gestor.nombre || this.value('atlasRutaGestorManual'),
                fecha_inicio: this.value('atlasRutaFechaInicio'),
                fecha_fin: this.value('atlasRutaFechaFin'),
                fk_sucursal: this.rutaBuilderSucursales[0]?.fk_sucursal || '',
                sucursales: this.rutaBuilderSucursales.map((s, idx) => ({
                    fk_sucursal: s.fk_sucursal,
                    orden_visita: idx + 1,
                    prioridad: s.prioridad || 'media',
                    criterio_prioridad: s.criterio_prioridad || 'enganches',
                    fecha_inicio_visita: s.fecha_inicio_visita || this.value('atlasRutaFechaInicio'),
                    fecha_fin_visita: s.fecha_inicio_visita || this.value('atlasRutaFechaInicio'),
                    hora_llegada: null,
                    estancia_valor: s.estancia_valor || 45,
                    estancia_unidad: s.estancia_unidad || 'minutos'
                })),
                tipo_ruta: this.value('atlasRutaTipo'),
                prioridad: this.rutaBuilderSucursales[0]?.prioridad || 'media',
                criterio_prioridad: this.rutaBuilderSucursales[0]?.criterio_prioridad || 'enganches',
                estatus: this.value('atlasRutaEstatus'),
                observaciones: this.value('atlasRutaObservaciones')
            };
            const res = await this.postJson('/Atlas/guardarRutaGestor', payload);
            this.toast(res.mensaje || 'Ruta guardada.', res.success ? 'success' : 'warning');
            if (res.success) {
                this.cerrarModalRuta();
                await this.cargarRutas();
                if (res.id) await this.verDetalle(res.id);
            }
        },

        async verDetalle(id, abrirModal = true) {
            const res = await this.getJson('/Atlas/getRutaGestorDetalle?id=' + encodeURIComponent(id));
            if (!res.success) {
                this.toast(res.mensaje || 'No se pudo cargar el detalle.', 'error');
                return;
            }
            this.detalle = res.datos;
            this.renderDetalle(abrirModal);
        },

        async verAvanceRuta(id) {
            await this.verDetalle(id);
            this.toast('Vista de avance/progreso de la ruta.', 'success');
        },

        renderDetalle(abrirModal = true) {
            if (!this.detalle || !this.detalle.ruta) return;
            const ruta = this.detalle.ruta;
            document.getElementById('atlasRutaDetalle')?.classList.remove('d-none');
            document.getElementById('atlasDetalleMapaPanel')?.classList.add('d-none');
            this.setText('atlasDetalleTitulo', 'Detalle de ruta');
            this.setText('atlasDetalleNombre', `${ruta.nombre_ruta || ('Ruta #' + ruta.id)} · ${ruta.gestor_nombre || ''}`);
            this.setText('atlasDetalleMeta', `${ruta.fecha_inicio || ruta.fecha_ruta || ''} a ${ruta.fecha_fin || ruta.fecha_ruta || ''} · ${this.labelEstatus(ruta.estatus)}`);
            this.setValue('atlasDetalleEstatus', ruta.estatus || 'borrador');

            const sucursales = this.detalle.sucursales || [];
            const metaWrap = document.getElementById('atlasDetalleMetaTrabajo');
            if (metaWrap) {
                metaWrap.innerHTML = this.resumenDetalleRutaHtml(ruta, sucursales);
            }
            const sucWrap = document.getElementById('atlasDetalleSucursales');
            if (sucWrap) {
                sucWrap.innerHTML = sucursales.length ? sucursales.map(s => `
                    <div class="atlas-rutas-mini" draggable="true" data-atlas-ruta-sucursal-card="${Number(s.id)}">
                        <div class="atlas-rutas-mini-row">
                            <div class="d-flex gap-2 align-items-start">
                                <span class="atlas-rutas-route-num">${Number(s.orden_visita || 1)}</span>
                                <div>
                                    <div class="atlas-rutas-mini-title">${this.escape(s.sucursal || '')}</div>
                                    <div class="atlas-rutas-mini-meta">FK ${this.escape(s.fk_sucursal || '')} · ${this.escape(s.direccion || 'Sin dirección')}</div>
                                    <div class="atlas-rutas-mini-meta">${this.escape(s.numero_telefono || 'Sin teléfono')} · ${this.escape(s.division_nombre || 'Sin división')}${Number(s.total_gestiones || 0) > 0 ? ' · ' + Number(s.total_gestiones || 0) + ' gestión(es)' : ''}</div>
                                    <div class="atlas-rutas-mini-meta"><span class="atlas-rutas-meta-chip">${this.escape(this.metaRutaSucursalTexto(s))}</span></div>
                                </div>
                            </div>
                            ${Number(s.tiene_gestion || 0) > 0
                                ? '<button class="btn btn-sm btn-label-secondary" type="button" disabled title="No se puede remover: ya tiene gestión"><i class="fa-solid fa-lock"></i></button>'
                                : `<button class="btn btn-sm btn-label-danger" type="button" data-atlas-remove-ruta-sucursal="${Number(s.id)}" title="Remover"><i class="fa-solid fa-trash"></i></button>`}
                        </div>
                        <div class="atlas-rutas-mini-meta">Prioridad: ${this.escape(this.labelPrioridad(s.prioridad_visita || 'media'))} · Orden sugerido: ${this.escape(this.labelCriterioPrioridad(s.criterio_prioridad_visita || 'enganches'))} · Créditos pendientes: ${Number(s.total_creditos || 0).toLocaleString('es-MX')}</div>
                        <div class="atlas-rutas-inline-cards"><span class="atlas-rutas-info-chip">${this.escape(s.clasificacion_nombre || 'Sin clasificación')}</span>${this.mapsChipSucursalHtml(s)}<span class="atlas-rutas-info-chip">Fecha ${this.escape(s.fecha_inicio_visita || 'Sin fecha')}</span><span class="atlas-rutas-info-chip">Estancia ${Number(s.estancia_valor || 0) || 45} ${this.escape(s.estancia_unidad || 'minutos')}</span></div>
                    </div>`).join('') : '<div class="atlas-rutas-muted-box">Aún no hay sucursales asignadas.</div>';
                sucWrap.querySelectorAll('[data-atlas-remove-ruta-sucursal]').forEach(btn => btn.addEventListener('click', () => this.removerSucursal(btn.getAttribute('data-atlas-remove-ruta-sucursal'))));
                this.activarDragRuta(sucWrap);
            }

            const credWrap = document.getElementById('atlasDetalleCreditos');
            const creditos = this.detalle.creditos || [];
            if (credWrap) {
                credWrap.innerHTML = creditos.length ? creditos.map(c => `
                    <div class="atlas-rutas-mini">
                        <div class="atlas-rutas-mini-row">
                            <div>
                                <div class="atlas-rutas-mini-title">${this.escape(c.id_solicitud || c.credito_id || '')}</div>
                                <div class="atlas-rutas-mini-meta">
                                    ${this.escape(c.sucursal || '')} · ${this.escape(c.bucket_actual || c.estatus_actual || '')}<br>
                                    $${Number(c.monto_financiar || c.cash_detenido || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} · Sync ${this.escape(c.fecha_ultima_sync_fmt || 'pendiente')}
                                </div>
                            </div>
                            <span class="atlas-rutas-badge atlas-rutas-badge-asignada">Pendiente</span>
                        </div>
                    </div>`).join('') : '<div class="atlas-rutas-muted-box">Esta sucursal no tiene créditos pendientes operativos confirmados.</div>';
            }
            this.renderMapaRuta(sucursales);
            if (abrirModal) {
                this.abrirModalDetalle();
            }
        },

        resumenDetalleRutaHtml(ruta, sucursales) {
            const lista = Array.isArray(sucursales) ? sucursales : [];
            let metaCash = 0;
            let metaCreditos = 0;
            let pendientes = 0;
            let sinMeta = 0;
            lista.forEach(s => {
                const meta = this.metaRutaSucursal(s);
                if (!meta) {
                    sinMeta += 1;
                } else {
                    metaCash += Number(meta.meta_cash || 0);
                    metaCreditos += Number(meta.meta_creditos || 0);
                }
                pendientes += Number(s.total_creditos || 0);
            });
            const periodo = [ruta.presupuesto_mes || ruta.nombre_mes, ruta.presupuesto_anio || ruta.anio].filter(Boolean).join(' ') || 'Presupuesto de la ruta';
            return `
                <div>
                    <div class="lbl">Presupuesto base</div>
                    <div class="val">${this.escape(periodo)}</div>
                    <div class="sub">${Number(lista.length).toLocaleString('es-MX')} visita(s) programada(s)</div>
                </div>
                <div>
                    <div class="lbl">Meta de la ruta</div>
                    <div class="val">${Number(metaCreditos).toLocaleString('es-MX')} créditos</div>
                    <div class="sub">${this.formatMoney(metaCash)} cash</div>
                </div>
                <div>
                    <div class="lbl">Pendiente operativo</div>
                    <div class="val">${Number(pendientes).toLocaleString('es-MX')} crédito(s)</div>
                    <div class="sub">${sinMeta ? Number(sinMeta).toLocaleString('es-MX') + ' sucursal(es) sin meta' : 'Todas con meta cargada'}</div>
                </div>
            `;
        },

        metaRutaSucursal(sucursal) {
            const metaLocal = this.metaSucursal(sucursal?.fk_sucursal);
            const metaCreditos = Number(sucursal?.meta_creditos ?? metaLocal?.meta_creditos ?? 0);
            const metaCash = Number(sucursal?.meta_cash ?? metaLocal?.meta_cash ?? 0);
            if (!metaCreditos && !metaCash) return null;
            return { meta_creditos: metaCreditos, meta_cash: metaCash };
        },

        metaRutaSucursalTexto(sucursal) {
            const meta = this.metaRutaSucursal(sucursal);
            if (!meta) return 'Meta: sin presupuesto cargado para esta sucursal';
            return `Meta: ${Number(meta.meta_creditos || 0).toLocaleString('es-MX')} créditos · ${this.formatMoney(meta.meta_cash || 0)}`;
        },

        abrirModalDetalle() {
            const el = document.getElementById('modalAtlasRutaDetalle');
            if (!el) return;
            if (window.bootstrap && bootstrap.Modal) {
                this.detalleModalInst = bootstrap.Modal.getOrCreateInstance(el);
                this.detalleModalInst.show();
                return;
            }
            el.classList.add('show');
            el.style.display = 'block';
            el.removeAttribute('aria-hidden');
        },

        toggleMapaDetalle() {
            const panel = document.getElementById('atlasDetalleMapaPanel');
            if (!panel) return;
            panel.classList.toggle('d-none');
            if (!panel.classList.contains('d-none')) {
                this.renderMapaRuta((this.detalle && this.detalle.sucursales) ? this.detalle.sucursales : []);
            }
        },

        activarDragRuta(container) {
            if (!container) return;
            let dragging = null;
            container.querySelectorAll('[data-atlas-ruta-sucursal-card]').forEach(card => {
                card.addEventListener('dragstart', () => {
                    dragging = card;
                    card.classList.add('is-dragging');
                });
                card.addEventListener('dragend', () => {
                    card.classList.remove('is-dragging');
                    dragging = null;
                    this.renumerarRutaVisual(container);
                });
                card.addEventListener('dragover', ev => {
                    ev.preventDefault();
                    if (!dragging || dragging === card) return;
                    const rect = card.getBoundingClientRect();
                    const after = ev.clientY > rect.top + rect.height / 2;
                    container.insertBefore(dragging, after ? card.nextSibling : card);
                });
            });
        },

        renumerarRutaVisual(container) {
            const ids = [];
            container.querySelectorAll('[data-atlas-ruta-sucursal-card]').forEach((card, idx) => {
                ids.push(Number(card.getAttribute('data-atlas-ruta-sucursal-card') || 0));
                const num = card.querySelector('.atlas-rutas-route-num');
                if (num) num.textContent = String(idx + 1);
            });
            if (this.detalle && this.detalle.ruta && ids.length) {
                this.guardarOrdenVisual(ids);
            }
        },

        async guardarOrdenVisual(ids) {
            try {
                const res = await this.postJson('/Atlas/guardarOrdenRutaSucursales', {
                    ruta_id: this.detalle.ruta.id,
                    ids: ids
                });
                if (res.success && this.detalle && Array.isArray(this.detalle.sucursales)) {
                    const orden = new Map(ids.map((id, idx) => [Number(id), idx + 1]));
                    this.detalle.sucursales.sort((a, b) => (orden.get(Number(a.id)) || 9999) - (orden.get(Number(b.id)) || 9999));
                    this.detalle.sucursales.forEach(s => { s.orden_visita = orden.get(Number(s.id)) || s.orden_visita; });
                    this.renderMapaRuta(this.detalle.sucursales);
                }
            } catch (e) {
                this.toast('No se pudo guardar el orden de visita.', 'warning');
            }
        },

        renderMapaRuta(sucursales) {
            const resumen = document.getElementById('atlasRutaMapaResumen');
            const canvas = document.getElementById('atlasRutaMapaCanvas');
            const total = (sucursales || []).length;
            if (resumen) resumen.textContent = total ? `${total} parada(s)` : 'Sin ruta seleccionada';
            if (!canvas) return;
            if (!total) {
                canvas.classList.remove('is-google-map');
                canvas.textContent = 'Esta ruta todavía no tiene visitas para dibujar.';
                return;
            }
            const puntos = (sucursales || []).map((s, idx) => {
                const lat = this.numeroValido(s.latitud);
                const lng = this.numeroValido(s.longitud);
                return lat == null || lng == null ? null : Object.assign({}, s, { _idx: idx + 1, _lat: lat, _lng: lng });
            }).filter(Boolean);
            if (!puntos.length) {
                canvas.classList.remove('is-google-map');
                canvas.textContent = 'Las visitas de esta ruta no tienen coordenadas para dibujar el mapa.';
                return;
            }
            canvas.classList.add('is-google-map');
            canvas.innerHTML = '';
            this.cargarGoogleMaps().then(() => this.dibujarMapaRuta(canvas, puntos)).catch(err => {
                canvas.classList.remove('is-google-map');
                canvas.textContent = err.message || 'No se pudo cargar Google Maps.';
            });
        },

        cargarGoogleMaps() {
            if (typeof google !== 'undefined' && google.maps) return Promise.resolve();
            if (this.googleMapsLoader) return this.googleMapsLoader;
            const apiKey = typeof window.ATLAS_GOOGLE_MAPS_KEY === 'string' ? window.ATLAS_GOOGLE_MAPS_KEY.trim() : '';
            if (!apiKey) return Promise.reject(new Error('Falta configurar GOOGLE_MAPS_API_KEY para dibujar el mapa.'));
            this.googleMapsLoader = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&language=es&region=MX';
                script.async = true;
                script.defer = true;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error('No se pudo cargar Google Maps.'));
                document.head.appendChild(script);
            });
            return this.googleMapsLoader;
        },

        dibujarMapaRuta(canvas, puntos) {
            if (typeof google === 'undefined' || !google.maps || !puntos.length) return;
            this.limpiarMapaRuta();
            const bounds = new google.maps.LatLngBounds();
            const path = puntos.map(p => {
                const pos = { lat: p._lat, lng: p._lng };
                bounds.extend(pos);
                return pos;
            });
            this.mapaRuta = new google.maps.Map(canvas, {
                center: path[0],
                zoom: 12,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                gestureHandling: 'greedy'
            });
            this.mapaRutaPolyline = new google.maps.Polyline({
                path,
                geodesic: true,
                strokeColor: '#26344e',
                strokeOpacity: .95,
                strokeWeight: 4,
                map: this.mapaRuta
            });
            puntos.forEach(p => this.mapaRutaMarkers.push(this.crearMarkerRuta(p)));
            if (puntos.length > 1) this.mapaRuta.fitBounds(bounds);
            else this.mapaRuta.setCenter(path[0]);
        },

        crearMarkerRuta(punto) {
            const marker = new google.maps.OverlayView();
            const pos = { lat: punto._lat, lng: punto._lng };
            const div = document.createElement('div');
            div.className = 'atlas-rutas-map-marker';
            div.title = `${punto._idx}. ${punto.sucursal || 'Sucursal'}`;
            div.innerHTML = `<span class="atlas-rutas-map-pin">${Number(punto._idx || 0)}</span>`;
            marker.onAdd = () => {
                const panes = marker.getPanes();
                if (panes && panes.overlayMouseTarget) panes.overlayMouseTarget.appendChild(div);
                div.addEventListener('click', () => {
                    const info = new google.maps.InfoWindow({
                        content: `<div style="min-width:210px"><div style="font-weight:800;color:#22303e;margin-bottom:.25rem;">${Number(punto._idx || 0)}. ${this.escape(punto.sucursal || 'Sucursal')}</div><div style="color:#64748b;font-size:.78rem;font-weight:700;">${this.escape(punto.direccion || 'Sin dirección')}<br>Estancia ${Number(punto.estancia_valor || 0) || 45} ${this.escape(punto.estancia_unidad || 'minutos')}</div></div>`
                    });
                    info.setPosition(pos);
                    info.open(this.mapaRuta);
                });
            };
            marker.draw = () => {
                const projection = marker.getProjection();
                if (!projection) return;
                const point = projection.fromLatLngToDivPixel(new google.maps.LatLng(pos.lat, pos.lng));
                if (!point) return;
                div.style.left = point.x + 'px';
                div.style.top = point.y + 'px';
            };
            marker.onRemove = () => div.remove();
            marker.setMap(this.mapaRuta);
            return marker;
        },

        limpiarMapaRuta() {
            if (this.mapaRutaPolyline) {
                this.mapaRutaPolyline.setMap(null);
                this.mapaRutaPolyline = null;
            }
            (this.mapaRutaMarkers || []).forEach(marker => {
                if (marker && typeof marker.setMap === 'function') marker.setMap(null);
            });
            this.mapaRutaMarkers = [];
        },

        numeroValido(valor) {
            const n = parseFloat(String(valor ?? '').replace(',', '.'));
            if (!Number.isFinite(n) || Math.abs(n) < 1e-9) return null;
            return n;
        },

        async cambiarEstatus() {
            if (!this.detalle || !this.detalle.ruta) return;
            const estatusAnterior = this.detalle.ruta.estatus || 'borrador';
            const estatusNuevo = this.value('atlasDetalleEstatus');
            const publicacion = this.validarPublicacionRuta(estatusNuevo, this.detalle.ruta);
            if (!publicacion.ok) {
                this.setValue('atlasDetalleEstatus', estatusAnterior);
                this.toast(publicacion.mensaje, 'warning');
                return;
            }
            const res = await this.postJson('/Atlas/actualizarEstatusRutaGestor', {
                id: this.detalle.ruta.id,
                estatus: estatusNuevo
            });
            this.toast(res.mensaje || 'Estatus actualizado.', res.success ? 'success' : 'warning');
            if (!res.success) this.setValue('atlasDetalleEstatus', estatusAnterior);
            if (res.success) {
                await this.cargarRutas();
                await this.verDetalle(this.detalle.ruta.id);
            }
        },

        async removerSucursal(id) {
            if (!window.confirm('¿Remover esta sucursal de la ruta?')) return;
            const rutaId = this.detalle.ruta.id;
            const res = await this.postJson('/Atlas/eliminarRutaSucursal', { id });
            this.toast(res.mensaje || 'Sucursal removida.', res.success ? 'success' : 'warning');
            if (res.success) {
                await this.cargarRutas();
                await this.verDetalle(rutaId);
            }
        },
        async getJson(url) {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            return await response.json();
        },

        async postJson(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload || {})
            });
            return await response.json();
        },

        labelEstatus(estatus) {
            const labels = {
                borrador: 'Borrador',
                asignada: 'Asignada',
                en_progreso: 'En progreso',
                completada: 'Completada',
                cancelada: 'Cancelada'
            };
            return labels[estatus] || estatus || 'Borrador';
        },

        iconoEstatus(estatus) {
            const iconos = {
                borrador: 'fa-regular fa-pen-to-square',
                asignada: 'fa-solid fa-share-nodes',
                en_progreso: 'fa-solid fa-person-walking-arrow-right',
                completada: 'fa-solid fa-circle-check',
                cancelada: 'fa-solid fa-ban'
            };
            return iconos[estatus] || iconos.borrador;
        },

        estatusBadgeHtml(estatus) {
            const key = ['borrador', 'asignada', 'en_progreso', 'completada', 'cancelada'].includes(estatus) ? estatus : 'borrador';
            return `<span class="atlas-rutas-badge atlas-rutas-badge-${this.escape(key)}" data-atlas-ruta-estatus="${this.escape(key)}"><i class="${this.escape(this.iconoEstatus(key))}"></i>${this.escape(this.labelEstatus(key))}</span>`;
        },

        ayudaEstatusRuta(estatus) {
            const textos = {
                borrador: 'Guarda la ruta en preparación; todavía no debe ejecutarse ni publicarse.',
                asignada: 'La ruta queda publicada para que el gestor la atienda en las fechas capturadas.',
                en_progreso: 'Indica que el gestor ya inició visitas o seguimiento de la ruta.',
                completada: 'Cierra la ruta cuando las visitas quedaron atendidas y ya no habrá cambios operativos.',
                cancelada: 'Anula la ruta; úsalo cuando no se realizará o fue reemplazada por otra.'
            };
            return textos[estatus] || textos.borrador;
        },

        ayudaTipoRuta(tipo) {
            const textos = {
                campo: 'Para visitas presenciales en sucursal; valida tiempos y traslados de la ruta.',
                telefonica: 'Para seguimiento remoto por llamada; no debe usarse si requiere visita fisica.',
                mixta: 'Combina visitas presenciales y seguimiento telefonico dentro de la misma ruta.'
            };
            return textos[tipo] || textos.campo;
        },

        actualizarAyudaTipoRuta() {
            this.setText('atlasRutaTipoAyuda', this.ayudaTipoRuta(this.value('atlasRutaTipo') || 'campo'));
        },

        actualizarAyudaEstatusRuta() {
            this.setText('atlasRutaEstatusAyuda', this.ayudaEstatusRuta(this.value('atlasRutaEstatus') || 'borrador'));
        },

        labelPrioridad(prioridad) {
            const labels = { alta: 'Alta', media: 'Media', baja: 'Baja' };
            return labels[prioridad] || 'Media';
        },

        labelCriterioPrioridad(criterio) {
            const labels = {
                enganches: 'Enganches',
                cash_detenido: 'Cash detenido',
                creditos_pendientes: 'Créditos pendientes',
                manual: 'Manual'
            };
            return labels[criterio] || 'Enganches';
        },

        formatMoney(value) {
            return '$' + Number(value || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        numeroDesdeMoneda(texto) {
            const limpio = String(texto || '').replace(/[^\d.-]/g, '');
            return Number(limpio || 0);
        },

        claseAvanceCash(actual, meta) {
            const metaNum = Number(meta || 0);
            if (metaNum <= 0) return 'atlas-rutas-money-neutral';
            const pct = (Number(actual || 0) / metaNum) * 100;
            if (pct >= 80) return 'atlas-rutas-money-green';
            if (pct >= 50) return 'atlas-rutas-money-yellow';
            if (pct >= 25) return 'atlas-rutas-money-orange';
            return 'atlas-rutas-money-red';
        },

        clasificarCeldaCash(cell) {
            if (!cell || cell.querySelector('.atlas-rutas-money-progress')) return;
            const main = cell.querySelector('.atlas-rutas-main');
            const sub = cell.querySelector('.atlas-rutas-sub');
            const actual = this.numeroDesdeMoneda(main?.textContent || '');
            const meta = this.numeroDesdeMoneda(sub?.textContent || '');
            const wrap = document.createElement('div');
            wrap.className = `atlas-rutas-money-progress ${this.claseAvanceCash(actual, meta)}`;
            while (cell.firstChild) {
                wrap.appendChild(cell.firstChild);
            }
            cell.appendChild(wrap);
        },

        value(id) { return document.getElementById(id)?.value || ''; },
        setValue(id, value) { const el = document.getElementById(id); if (el) el.value = value == null ? '' : value; },
        setText(id, value) { const el = document.getElementById(id); if (el) el.textContent = value == null ? '' : value; },
        setModalRutaTitle(texto, icono) {
            const el = document.getElementById('atlasRutaModalTitle');
            if (!el) return;
            el.innerHTML = `<i class="${this.escape(icono || 'fa-solid fa-route')} me-2"></i>${this.escape(texto || 'Ruta')}`;
        },
        escape(value) { return String(value ?? '').replace(/[&<>"']/g, s => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[s])); },

        showWait() {
            if (typeof window.showWait === 'function') {
                window.showWait('Espere un momento...');
                return;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Procesando su petición', text: 'Espere un momento...', imageUrl: '/assets/img/wait.svg', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false });
            }
        },
        closeWait() {
            if (typeof Swal !== 'undefined' && Swal.isVisible()) Swal.close();
        },
        toast(message, type) {
            if (window.toastr && typeof window.toastr[type || 'info'] === 'function') {
                window.toastr[type || 'info'](message);
                return;
            }
            if (typeof Swal !== 'undefined' && type && type !== 'success') {
                Swal.fire({ icon: type === 'error' ? 'error' : 'warning', title: message });
                return;
            }
            console.log(message);
        }
    };

    window.AtlasRutas = AtlasRutas;
    document.addEventListener('DOMContentLoaded', () => AtlasRutas.init());
})();
</script>
