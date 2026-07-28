<div class="container-xxl flex-grow-1 container-p-y atlas-pres-page">
    <style>
        .atlas-pres-page { color:#22303e; }
        .atlas-pres-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:800; margin:0; }
        .atlas-pres-title i { color:#26344e; }
        .atlas-pres-subtitle { color:#6b7280; font-size:.88rem; font-weight:600; margin:.2rem 0 0; }
        .btn-action-size { min-height:2.375rem; padding:.5rem .95rem; display:inline-flex; align-items:center; justify-content:center; gap:.35rem; font-size:.875rem; font-weight:600; }
        .atlas-pres-calendar { display:grid; grid-template-columns:repeat(6, minmax(0, 1fr)); gap:.75rem; }
        .atlas-pres-month { border:1px solid #e5e7eb; border-radius:.7rem; background:#fff; padding:.8rem; cursor:pointer; transition:border-color .15s ease, transform .15s ease, box-shadow .15s ease; min-height:6.8rem; text-align:left; }
        .atlas-pres-month:hover { border-color:#d09f48; transform:translateY(-1px); box-shadow:0 .25rem .75rem rgba(34,48,62,.08); }
        .atlas-pres-month.active { border-color:#26344e; box-shadow:0 0 0 .18rem rgba(38,52,78,.08); }
        .atlas-pres-month.is-empty,
        .atlas-pres-month:disabled { cursor:not-allowed; background:#f3f4f6; border-color:#e5e7eb; opacity:1; }
        .atlas-pres-month.is-empty:hover,
        .atlas-pres-month:disabled:hover { border-color:#e5e7eb; transform:none; box-shadow:none; }
        .atlas-pres-month.is-empty .atlas-pres-month-name,
        .atlas-pres-month:disabled .atlas-pres-month-name { color:#94a3b8; }
        .atlas-pres-month-name { color:#22303e; font-size:.92rem; font-weight:900; line-height:1.15; }
        .atlas-pres-month-meta { color:#7a838b; font-size:.72rem; font-weight:700; margin-top:.28rem; line-height:1.24; }
        .atlas-pres-month-empty { color:#94a3b8; }
        .atlas-pres-badge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.22rem .62rem; font-size:.72rem; font-weight:800; white-space:nowrap; }
        .atlas-pres-badge-ok { background:#dcfce7; color:#15803d; }
        .atlas-pres-badge-warn { background:#fef3c7; color:#b45309; }
        .atlas-pres-badge-info { background:#dbeafe; color:#1d4ed8; }
        .atlas-pres-badge-muted { background:#f1f5f9; color:#64748b; }
        .atlas-pres-main { color:#22303e; font-weight:800; line-height:1.16; }
        .atlas-pres-sub { color:#7a838b; font-size:.75rem; font-weight:700; line-height:1.2; margin-top:.16rem; }
        .atlas-pres-empty { text-align:center; color:#9ca3af; font-weight:700; padding:2rem !important; }
        .atlas-pres-actions { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; }
        .atlas-pres-actions .btn { width:2rem; height:2rem; padding:0; display:inline-flex; align-items:center; justify-content:center; }
        .atlas-pres-summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .atlas-pres-chip { border:1px solid #e5e7eb; border-radius:.65rem; background:#f8fafc; padding:.8rem .9rem; min-width:0; }
        .atlas-pres-chip--button { cursor:pointer; text-align:left; transition:border-color .15s ease, transform .15s ease, box-shadow .15s ease; }
        .atlas-pres-chip--button:hover { border-color:#d09f48; transform:translateY(-1px); box-shadow:0 .25rem .75rem rgba(34,48,62,.08); }
        .atlas-pres-chip-label { display:block; color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.02em; margin-bottom:.18rem; }
        .atlas-pres-chip-value { display:block; color:#22303e; font-size:1rem; font-weight:900; overflow-wrap:anywhere; }
        .atlas-pres-rank-list { display:flex; flex-direction:column; gap:.55rem; }
        .atlas-pres-rank-row { border:1px solid #e5e7eb; border-radius:.65rem; background:#fff; padding:.72rem .85rem; display:grid; grid-template-columns:2.2rem 1fr auto; gap:.75rem; align-items:center; }
        .atlas-pres-rank-row.is-top { background:#f0fdf4; border-color:#bbf7d0; }
        .atlas-pres-rank-row.is-low { background:#fef2f2; border-color:#fecaca; }
        .atlas-pres-rank-num { width:2rem; height:2rem; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; background:#26344e; color:#fff; font-weight:900; font-size:.8rem; }
        .atlas-pres-rank-row.is-top .atlas-pres-rank-num { background:#16a34a; }
        .atlas-pres-rank-row.is-low .atlas-pres-rank-num { background:#dc2626; }
        .atlas-pres-rank-name { color:#22303e; font-weight:900; line-height:1.18; }
        .atlas-pres-rank-meta { color:#7a838b; font-size:.73rem; font-weight:700; margin-top:.15rem; line-height:1.22; }
        .atlas-pres-rank-value { text-align:right; color:#22303e; font-weight:900; white-space:nowrap; min-width:8.5rem; }
        .atlas-pres-rank-metric { display:flex; justify-content:space-between; gap:.75rem; font-size:.76rem; line-height:1.2; }
        .atlas-pres-rank-metric span:first-child { color:#64748b; font-weight:800; }
        .atlas-pres-rank-metric strong { color:#22303e; font-weight:900; }
        #modalAtlasPresupuestoImportar .modal-content,
        #modalAtlasPresupuestoEditar .modal-content,
        #modalAtlasPresupuestoReasignar .modal-content,
        #modalAtlasPresupuestoEliminarSucursal .modal-content,
        #modalAtlasPresupuestoRanking .modal-content,
        #modalAtlasPresupuestoBitacora .modal-content { border:0; border-radius:.875rem; box-shadow:var(--bs-box-shadow-lg); overflow:hidden; }
        #modalAtlasPresupuestoImportar .modal-header,
        #modalAtlasPresupuestoEditar .modal-header,
        #modalAtlasPresupuestoReasignar .modal-header,
        #modalAtlasPresupuestoEliminarSucursal .modal-header,
        #modalAtlasPresupuestoRanking .modal-header,
        #modalAtlasPresupuestoBitacora .modal-header { border-bottom:1px solid #e5e7eb; padding:1rem 1.25rem; }
        #modalAtlasPresupuestoImportar .modal-footer,
        #modalAtlasPresupuestoEditar .modal-footer,
        #modalAtlasPresupuestoReasignar .modal-footer,
        #modalAtlasPresupuestoEliminarSucursal .modal-footer,
        #modalAtlasPresupuestoRanking .modal-footer,
        #modalAtlasPresupuestoBitacora .modal-footer { border-top:1px solid #e5e7eb; padding:1rem 1.25rem; gap:.75rem; }
        #modalAtlasPresupuestoReasignar .modal-body,
        #modalAtlasPresupuestoEliminarSucursal .modal-body {
            min-height:0;
            overflow-x:hidden;
            overflow-y:scroll;
            overscroll-behavior:contain;
            scrollbar-color:#94a3b8 #eef2f7;
            scrollbar-gutter:stable;
            scrollbar-width:thin;
        }
        #modalAtlasPresupuestoReasignar .modal-body::-webkit-scrollbar,
        #modalAtlasPresupuestoEliminarSucursal .modal-body::-webkit-scrollbar { width:.6rem; }
        #modalAtlasPresupuestoReasignar .modal-body::-webkit-scrollbar-track,
        #modalAtlasPresupuestoEliminarSucursal .modal-body::-webkit-scrollbar-track { background:#eef2f7; }
        #modalAtlasPresupuestoReasignar .modal-body::-webkit-scrollbar-thumb,
        #modalAtlasPresupuestoEliminarSucursal .modal-body::-webkit-scrollbar-thumb {
            background:#94a3b8;
            border:2px solid #eef2f7;
            border-radius:.5rem;
        }
        .atlas-pres-import-result { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.65rem; text-align:left; margin:.4rem 0 .85rem; }
        .atlas-pres-import-result-item { border:1px solid #e5e7eb; border-radius:.55rem; padding:.62rem .7rem; background:#fff; }
        .atlas-pres-import-result-button { cursor:pointer; transition:border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
        .atlas-pres-import-result-button:hover { border-color:#d09f48; box-shadow:0 .2rem .65rem rgba(34,48,62,.08); transform:translateY(-1px); }
        .atlas-pres-import-result-label { display:block; color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; }
        .atlas-pres-import-result-value { display:block; color:#22303e; font-size:1.05rem; font-weight:900; margin-top:.12rem; }
        .atlas-pres-import-title { text-align:center; font-size:1.05rem; font-weight:900; color:#22303e; border:0; background:transparent; padding:0; margin:.15rem auto .85rem; display:block; cursor:pointer; }
        .atlas-pres-import-title:hover { color:#d09f48; }
        .atlas-pres-import-download { display:flex; justify-content:flex-end; margin:.65rem 0 .25rem; }
        .atlas-pres-import-warnings { text-align:left; border:1px solid #fde68a; border-radius:.6rem; background:#fffbeb; color:#92400e; padding:.75rem .85rem; font-size:.82rem; font-weight:700; }
        .atlas-pres-import-warning-list { margin:.4rem 0 0; padding-left:1rem; max-height:7.5rem; overflow:auto; }
        .atlas-pres-import-warning-list li { margin-bottom:.22rem; }
        .atlas-pres-timeline { display:flex; flex-direction:column; gap:.7rem; }
        .atlas-pres-timeline-row { border:1px solid #e5e7eb; border-radius:.65rem; background:#fff; padding:.75rem .85rem; display:grid; grid-template-columns:2.1rem 1fr auto; gap:.75rem; align-items:start; }
        .atlas-pres-timeline-icon { width:2.1rem; height:2.1rem; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; color:#fff; background:#26344e; }
        .atlas-pres-timeline-main { color:#22303e; font-weight:900; line-height:1.18; }
        .atlas-pres-timeline-sub { color:#64748b; font-size:.75rem; font-weight:700; line-height:1.22; margin-top:.15rem; }
        .atlas-pres-timeline-date { color:#64748b; font-size:.75rem; font-weight:800; white-space:nowrap; text-align:right; }
        .atlas-pres-reassign-picker { border:1px solid #dbe3ec; border-radius:.65rem; background:#fff; overflow:hidden; }
        .atlas-pres-reassign-picker-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.75rem .85rem; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
        .atlas-pres-reassign-list { max-height:15rem; overflow:auto; }
        .atlas-pres-reassign-item { display:grid; grid-template-columns:auto minmax(0, 1fr); gap:.7rem; align-items:start; padding:.7rem .85rem; margin:0; cursor:pointer; border-bottom:1px solid #eef2f7; }
        .atlas-pres-reassign-item:last-child { border-bottom:0; }
        .atlas-pres-reassign-item:hover { background:#f8fafc; }
        .atlas-pres-reassign-item .form-check-input { margin-top:.18rem; }
        .atlas-pres-reassign-empty { color:#64748b; font-size:.82rem; font-weight:700; padding:1rem; text-align:center; }
        .atlas-pres-tabs { border-bottom:1px solid #e2e8f0; margin-bottom:1rem; gap:.35rem; flex-wrap:wrap; }
        .atlas-pres-tabs .nav-link { border:0; border-bottom:3px solid transparent; border-radius:0; background:transparent; color:#64748b; font-weight:800; padding:.65rem .9rem; display:inline-flex; align-items:center; gap:.42rem; }
        .atlas-pres-tabs .nav-link.active { color:#173756; border-bottom-color:#2563eb; background:transparent; box-shadow:none; }
        .atlas-pres-tab-empty { border:1px dashed #cbd5e1; border-radius:.75rem; background:#f8fafc; color:#64748b; font-weight:800; padding:2rem; text-align:center; }
        .atlas-pres-page .dataTables_paginate .pagination,
        .atlas-pres-page .dt-paging .pagination { flex-wrap:wrap; gap:.25rem; }
        .atlas-pres-page .dataTables_paginate .page-link,
        .atlas-pres-page .dt-paging .page-link { min-width:2rem; text-align:center; }
        @media (max-width: 1199.98px) { .atlas-pres-calendar { grid-template-columns:repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 767.98px) {
            .atlas-pres-calendar,
            .atlas-pres-summary { grid-template-columns:1fr; }
            .atlas-pres-toolbar { align-items:stretch !important; flex-direction:column; }
            .atlas-pres-toolbar .btn,
            .atlas-pres-toolbar .form-select { width:100%; }
            .atlas-pres-tabs { flex-wrap:nowrap; overflow-x:auto; overflow-y:hidden; padding-bottom:.25rem; -webkit-overflow-scrolling:touch; }
            .atlas-pres-tabs .nav-item { flex:0 0 auto; }
            .atlas-pres-tabs .nav-link { white-space:nowrap; padding:.6rem .75rem; }
        }
    </style>

    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h1 class="atlas-pres-title"><i class="fa-solid fa-calendar-check"></i><span>Presupuestos</span></h1>
            <p class="atlas-pres-subtitle">Metas mensuales por sucursal. El historial se controla por mes y año.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
    <ul class="nav atlas-pres-tabs" id="atlasPresTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#atlasPresTabPresupuestos" role="tab" aria-controls="atlasPresTabPresupuestos" aria-selected="true">
                <i class="fa-solid fa-file-invoice-dollar"></i><span>Presupuestos</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#atlasPresTabAvance" role="tab" aria-controls="atlasPresTabAvance" aria-selected="false">
                <i class="fa-solid fa-chart-line"></i><span>Avance de meta</span>
            </button>
        </li>
    </ul>

    <div class="tab-content p-0">
        <div class="tab-pane fade show active" id="atlasPresTabPresupuestos" role="tabpanel" aria-labelledby="atlasPresTabPresupuestos">
    <div id="atlasPresListadoView">
            <div class="d-flex align-items-end justify-content-between gap-3 flex-wrap atlas-pres-toolbar mb-3">
                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <div>
                        <label class="form-label fw-bold mb-1">Año</label>
                        <select id="atlasPresAnio" class="form-select form-select-sm"></select>
                    </div>
                    <button type="button" class="btn btn-label-secondary btn-action-size" data-atlas-pres-refresh>
                        <i class="fa-solid fa-rotate icon-sm me-sm-1"></i><span>Actualizar</span>
                    </button>
                    <button type="button" class="btn btn-label-primary btn-action-size" data-atlas-pres-bitacora-anio>
                        <i class="fa-solid fa-clock-rotate-left icon-sm me-sm-1"></i><span>Bit&aacute;cora</span>
                    </button>
                </div>
                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-pres-import-open>
                        <i class="fa-solid fa-file-arrow-up icon-sm me-sm-1"></i><span>Cargar Excel</span>
                    </button>
                    <button type="button" class="btn btn-info text-white btn-action-size" data-atlas-pres-template>
                        <i class="fa-solid fa-download icon-sm me-sm-1"></i><span>Template</span>
                    </button>
                </div>
            </div>

            <div class="atlas-pres-calendar mb-4" id="atlasPresCalendar"></div>

            <div class="card-datatable table-responsive">
                <table class="dt-responsive table border-top" id="atlasPresHistorialTabla">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Sucursales</th>
                            <th>Meta créditos</th>
                            <th>Meta cash</th>
                            <th>Archivo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="atlasPresHistorialBody">
                        <tr><td class="atlas-pres-empty" colspan="6">Cargando presupuestos...</td></tr>
                    </tbody>
                </table>
            </div>
    </div>

    <div id="atlasPresDetalleCard" style="display:none;">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                <div>
                    <h5 class="fw-bold mb-1" id="atlasPresDetalleTitulo"><i class="fa-solid fa-store me-2"></i>Detalle mensual</h5>
                    <div class="text-muted small fw-semibold" id="atlasPresDetalleSub">Selecciona un mes.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-success btn-action-size" data-atlas-pres-asignar-sucursal>
                        <i class="fa-solid fa-circle-plus icon-sm me-sm-1"></i><span>Asignar sucursal</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-action-size" data-atlas-pres-reasignar>
                        <i class="fa-solid fa-users icon-sm me-sm-1"></i><span>Distribuir presupuesto</span>
                    </button>
                    <button type="button" class="btn btn-label-secondary btn-action-size" data-atlas-pres-regresar>
                        <i class="fa-solid fa-arrow-left icon-sm me-sm-1"></i><span>Regresar a Presupuestos</span>
                    </button>
                </div>
            </div>
            <div class="atlas-pres-summary" id="atlasPresDetalleResumen"></div>
            <div class="card-datatable table-responsive">
                <table class="dt-responsive table border-top" id="atlasPresDetalleTabla">
                    <thead>
                        <tr>
                            <th>Sucursal</th>
                            <th>Responsable</th>
                            <th>Clasificación</th>
                            <th>Meta créditos</th>
                            <th>Comisiona desde</th>
                            <th>Meta cash</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="atlasPresDetalleBody"></tbody>
                </table>
            </div>
    </div>
        </div>

        <div class="tab-pane fade" id="atlasPresTabAvance" role="tabpanel" aria-labelledby="atlasPresTabAvance">
                    <?php
                    $atlas_suc_asig_embedded = true;
                    $atlas_suc_asig_titulo = 'Avance de meta';
                    require __DIR__ . '/atlas_sucursales_asignadas.php';
                    ?>
        </div>
    </div>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasPresupuestoImportar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="atlasPresImportForm">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2"></i>Cargar presupuesto mensual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">Año *</label>
                                <select class="form-select" name="anio" id="atlasPresImportAnio" required></select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Mes *</label>
                                <select class="form-select" name="mes" id="atlasPresImportMes" required></select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Excel *</label>
                                <input class="form-control" type="file" name="archivo" accept=".xlsx,.xls" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                        <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasPresupuestoEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <form id="atlasPresEditForm">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold" id="atlasPresEditTitle"><i class="fa-solid fa-pen-to-square me-2"></i>Editar meta de sucursal</h5>
                            <div class="text-muted small fw-semibold" id="atlasPresEditSub">Actualiza este registro del mes.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="atlasPresEditId">
                        <input type="hidden" name="presupuesto_id" id="atlasPresEditPresupuestoId">
                        <div class="mb-3" id="atlasPresEditSucursalActualWrap">
                            <label class="form-label fw-bold">Sucursal</label>
                            <input class="form-control" id="atlasPresEditSucursal" readonly>
                        </div>
                        <div class="mb-3 d-none" id="atlasPresEditSucursalSelectWrap">
                            <label class="form-label fw-bold" for="atlasPresEditSucursalSelect">Nueva sucursal *</label>
                            <select class="form-select" name="fk_sucursal" id="atlasPresEditSucursalSelect"></select>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">Meta créditos *</label>
                                <input class="form-control" name="meta_creditos" id="atlasPresEditCreditos" type="number" min="0" step="1" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Meta cash *</label>
                                <input class="form-control" name="meta_cash" id="atlasPresEditCash" type="number" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                        <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasPresupuestoReasignar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content" id="atlasPresReasignarForm">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-users me-2"></i>Distribuir presupuesto</h5>
                            <div class="text-muted small fw-semibold" id="atlasPresReasignarSub">Asigna el presupuesto de una sucursal entre uno o varios gestores.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
                            <i class="fa-solid fa-circle-info mt-1"></i>
                            <div>La distribución conserva el total de la sucursal. Las rutas, visitas y check-ins existentes mantienen su historial.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresReasignarOrigen">Sucursal *</label>
                                <select class="form-select" id="atlasPresReasignarOrigen" required></select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresReasignarDestino">Gestores asignados *</label>
                                <select class="form-select" id="atlasPresReasignarDestino" multiple required></select>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-3 bg-light" id="atlasPresReasignarResumen">
                                    Selecciona una sucursal para revisar su presupuesto.
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <strong>Distribución propuesta</strong>
                                    <button type="button" class="btn btn-sm btn-label-primary" id="atlasPresReasignarTodas" disabled>
                                        <i class="fa-solid fa-scale-balanced me-1"></i>Recalcular equitativamente
                                    </button>
                                </div>
                                <div class="border rounded overflow-hidden" id="atlasPresReasignarSucursales">
                                    <div class="atlas-pres-reassign-empty">Selecciona sucursal y gestores.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresReasignarMotivo">Motivo *</label>
                                <textarea
                                    class="form-control"
                                    id="atlasPresReasignarMotivo"
                                    rows="3"
                                    minlength="5"
                                    maxlength="500"
                                    placeholder="Ej. Alta de nuevo colaborador o cambio de estructura"
                                    required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>Guardar distribución</button>
                        <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasPresupuestoEliminarSucursal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content" id="atlasPresEliminarSucursalForm">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-arrow-right-arrow-left me-2"></i>Reasignar antes de eliminar</h5>
                            <div class="text-muted small fw-semibold" id="atlasPresEliminarSucursalSub">El presupuesto debe conservarse completo.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                            <div>La eliminación permanecerá bloqueada hasta seleccionar una sucursal destino y cuadrar el reparto completo.</div>
                        </div>
                        <div class="border rounded p-3 bg-light mb-3" id="atlasPresEliminarOrigenResumen"></div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresEliminarDestino">Sucursal destino *</label>
                                <select class="form-select" id="atlasPresEliminarDestino" required></select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresEliminarGestores">Gestores del nuevo reparto *</label>
                                <select class="form-select" id="atlasPresEliminarGestores" multiple required></select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <strong>Presupuesto resultante en la sucursal destino</strong>
                                    <button type="button" class="btn btn-sm btn-label-primary" id="atlasPresEliminarRecalcular" disabled>
                                        <i class="fa-solid fa-scale-balanced me-1"></i>Recalcular
                                    </button>
                                </div>
                                <div class="border rounded overflow-hidden" id="atlasPresEliminarAsignaciones">
                                    <div class="atlas-pres-reassign-empty">Selecciona una sucursal destino.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresEliminarMotivo">Motivo *</label>
                                <textarea class="form-control" id="atlasPresEliminarMotivo" rows="3" minlength="5" maxlength="500" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-end">
                        <button type="submit" class="btn btn-danger" id="atlasPresEliminarConfirmar" disabled>
                            <i class="fa-solid fa-trash-can me-1"></i>Reasignar y eliminar sucursal
                        </button>
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasPresupuestoRanking" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-ranking-star me-2"></i>Ranking</h5>
                        <div class="text-muted small fw-semibold" id="atlasPresRankingSub">Top de sucursales del mes.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">Periodo</label>
                            <select class="form-select" id="atlasPresRankingPeriodo">
                                <option value="mes">Mes</option>
                                <option value="semana">Semana</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4" id="atlasPresRankingSemanaWrap" style="display:none;">
                            <label class="form-label fw-bold">Semana</label>
                            <select class="form-select" id="atlasPresRankingSemana">
                                <option value="1">Semana 1</option>
                                <option value="2">Semana 2</option>
                                <option value="3">Semana 3</option>
                                <option value="4">Semana 4</option>
                                <option value="5">Semana 5</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">Ordenar por</label>
                            <select class="form-select" id="atlasPresRankingOrden">
                                <option value="cash">Meta cash</option>
                                <option value="avanzado">Cash avanzado</option>
                                <option value="creditos">Créditos</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 small fw-semibold mb-3" id="atlasPresRankingNota">
                        Ranking calculado con el presupuesto mensual cargado. Al conectar ventas reales, esta misma vista mostrará vendido real.
                    </div>
                    <div id="atlasPresRankingLista" class="atlas-pres-rank-list"></div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasPresupuestoBitacora" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-clock-rotate-left me-2"></i>Bit&aacute;cora de presupuesto</h5>
                        <div class="text-muted small fw-semibold" id="atlasPresBitacoraSub">Movimientos registrados.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="atlas-pres-summary" id="atlasPresBitacoraResumen"></div>
                    <div class="atlas-pres-timeline" id="atlasPresBitacoraLista"></div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (() => {
        const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        const Pres = {
            tabStorageKey: 'atlas_presupuestos_tab_activa',
            anio: new Date().getFullYear(),
            historial: [],
            calendario: [],
            calendariosPorAnio: {},
            detalle: null,
            reasignacionCatalogos: null,
            reasignacionSucursal: null,
            reasignacionAsignaciones: [],
            eliminacionOrigen: null,
            eliminacionDestino: null,
            eliminacionAsignaciones: [],
            modalImport: null,
            modalEdit: null,
            modalReasignar: null,
            modalEliminarSucursal: null,
            modalRanking: null,
            modalBitacora: null,

            init() {
                this.modalImport = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoImportar'));
                this.modalEdit = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoEditar'));
                this.modalReasignar = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoReasignar'));
                this.modalEliminarSucursal = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoEliminarSucursal'));
                this.modalRanking = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoRanking'));
                this.modalBitacora = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoBitacora'));
                this.initYears();
                this.initTabs();
                this.bind();
                this.cargar();
            },

            nombreMes(mes) {
                return meses[(parseInt(mes, 10) || 1) - 1] || 'Mes';
            },

            initYears() {
                const actual = new Date().getFullYear();
                const opts = [];
                for (let y = actual - 2; y <= actual + 2; y++) opts.push(`<option value="${y}">${y}</option>`);
                document.getElementById('atlasPresAnio').innerHTML = opts.join('');
                document.getElementById('atlasPresImportAnio').innerHTML = opts.join('');
                document.getElementById('atlasPresAnio').value = String(actual);
                document.getElementById('atlasPresImportAnio').value = String(actual);
                document.getElementById('atlasPresImportMes').innerHTML = meses.map((m, i) => `<option value="${i + 1}">${m}</option>`).join('');
                document.getElementById('atlasPresImportMes').value = String(new Date().getMonth() + 1);
            },

            initTabs() {
                this.activarTabGuardada();
                document.querySelectorAll('#atlasPresTabs [data-bs-toggle="tab"]').forEach(btn => {
                    btn.addEventListener('shown.bs.tab', () => {
                        this.guardarTabActiva(btn.getAttribute('data-bs-target') || '');
                        setTimeout(() => {
                            if (window.jQuery && jQuery.fn && jQuery.fn.dataTable) {
                                jQuery('.dataTable').each(function () {
                                    if (jQuery.fn.dataTable.isDataTable(this)) {
                                        jQuery(this).DataTable().columns.adjust();
                                    }
                                });
                            }
                        }, 80);
                    });
                });
            },

            guardarTabActiva(target) {
                try {
                    if (target) window.localStorage.setItem(this.tabStorageKey, target);
                    else window.localStorage.removeItem(this.tabStorageKey);
                } catch (err) {}
            },

            leerTabActiva() {
                try { return window.localStorage.getItem(this.tabStorageKey) || ''; } catch (err) { return ''; }
            },

            activarTabGuardada() {
                const target = this.leerTabActiva();
                if (!target) return;
                const btn = document.querySelector('#atlasPresTabs [data-bs-target="' + target + '"]');
                if (!btn || !window.bootstrap) return;
                bootstrap.Tab.getOrCreateInstance(btn).show();
            },

            bind() {
                document.getElementById('atlasPresAnio').addEventListener('change', (ev) => {
                    this.anio = parseInt(ev.target.value, 10) || new Date().getFullYear();
                    this.cargar();
                });
                document.querySelector('[data-atlas-pres-refresh]').addEventListener('click', () => this.cargar());
                document.querySelector('[data-atlas-pres-bitacora-anio]').addEventListener('click', () => this.abrirBitacora(0));
                document.querySelector('[data-atlas-pres-import-open]').addEventListener('click', () => {
                    document.getElementById('atlasPresImportAnio').value = document.getElementById('atlasPresAnio').value;
                    this.actualizarMesesImportacion().then(() => this.modalImport.show());
                });
                document.querySelector('[data-atlas-pres-template]').addEventListener('click', () => this.descargarTemplate());
                document.querySelector('[data-atlas-pres-regresar]').addEventListener('click', () => this.regresarListado());
                document.querySelector('[data-atlas-pres-asignar-sucursal]').addEventListener('click', () => this.abrirAsignarSucursal());
                document.querySelector('[data-atlas-pres-reasignar]').addEventListener('click', () => this.abrirReasignacion());
                document.getElementById('atlasPresImportAnio').addEventListener('change', () => this.actualizarMesesImportacion());
                document.getElementById('atlasPresImportForm').addEventListener('submit', (ev) => this.importar(ev));
                document.getElementById('atlasPresEditForm').addEventListener('submit', (ev) => this.guardarDetalle(ev));
                document.getElementById('atlasPresReasignarForm').addEventListener('submit', (ev) => this.reasignarPresupuesto(ev));
                document.getElementById('atlasPresReasignarOrigen').addEventListener('change', () => this.cambiarSucursalDistribucion());
                document.getElementById('atlasPresReasignarDestino').addEventListener('change', () => this.cambiarGestoresDistribucion());
                document.getElementById('atlasPresReasignarSucursales').addEventListener('input', (ev) => this.cambiarMontoDistribucion(ev, 'reasignacion'));
                document.getElementById('atlasPresReasignarTodas').addEventListener('click', () => this.recalcularDistribucion('reasignacion'));
                document.getElementById('atlasPresReasignarMotivo').addEventListener('input', () => this.renderResumenDistribucion('reasignacion'));
                document.getElementById('atlasPresEliminarSucursalForm').addEventListener('submit', (ev) => this.eliminarSucursal(ev));
                document.getElementById('atlasPresEliminarDestino').addEventListener('change', () => this.cambiarDestinoEliminacion());
                document.getElementById('atlasPresEliminarGestores').addEventListener('change', () => this.cambiarGestoresEliminacion());
                document.getElementById('atlasPresEliminarAsignaciones').addEventListener('input', (ev) => this.cambiarMontoDistribucion(ev, 'eliminacion'));
                document.getElementById('atlasPresEliminarRecalcular').addEventListener('click', () => this.recalcularDistribucion('eliminacion'));
                document.getElementById('atlasPresEliminarMotivo').addEventListener('input', () => this.actualizarEstadoEliminarSucursal());
                document.getElementById('atlasPresRankingPeriodo').addEventListener('change', () => this.renderRanking());
                document.getElementById('atlasPresRankingSemana').addEventListener('change', () => this.renderRanking());
                document.getElementById('atlasPresRankingOrden').addEventListener('change', () => this.renderRanking());
                document.getElementById('atlasPresCalendar').addEventListener('click', (ev) => {
                    const btn = ev.target.closest('[data-pres-id]');
                    if (btn) this.cargarDetalle(parseInt(btn.getAttribute('data-pres-id'), 10));
                });
                document.getElementById('atlasPresHistorialBody').addEventListener('click', (ev) => this.handleHistorial(ev));
                document.getElementById('atlasPresDetalleBody').addEventListener('click', (ev) => this.handleDetalle(ev));
                document.getElementById('atlasPresDetalleResumen').addEventListener('click', (ev) => {
                    if (ev.target.closest('[data-atlas-pres-ranking]')) this.abrirRanking();
                });
            },

            cargar() {
                http.request({
                    endpoint: '/Atlas/getPresupuestos',
                    metodo: 'GET',
                    data: { anio: this.anio },
                    showLoader: true,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            showError(resp?.mensaje || 'No se pudieron cargar presupuestos.');
                            return;
                        }
                        this.historial = resp.datos?.historial || [];
                        this.calendario = resp.datos?.calendario || [];
                        this.calendariosPorAnio[this.anio] = this.calendario;
                        this.renderCalendar();
                        this.renderHistorial();
                    }
                });
            },

            obtenerCalendarioImportacion(anio) {
                anio = parseInt(anio, 10) || new Date().getFullYear();
                if (this.calendariosPorAnio[anio]) {
                    return Promise.resolve(this.calendariosPorAnio[anio]);
                }
                return new Promise((resolve) => {
                    http.request({
                        endpoint: '/Atlas/getPresupuestos',
                        metodo: 'GET',
                        data: { anio },
                        showLoader: false,
                        onSuccess: (resp) => {
                            const calendario = (!resp || resp.success === false) ? null : (resp.datos?.calendario || []);
                            this.calendariosPorAnio[anio] = calendario;
                            resolve(calendario);
                        },
                        onError: () => {
                            this.calendariosPorAnio[anio] = null;
                            resolve(null);
                        }
                    });
                });
            },

            actualizarMesesImportacion() {
                const anio = parseInt(document.getElementById('atlasPresImportAnio').value, 10) || this.anio;
                const selectMes = document.getElementById('atlasPresImportMes');
                const inputArchivo = document.querySelector('#atlasPresImportForm input[name="archivo"]');
                const submitBtn = document.querySelector('#atlasPresImportForm button[type="submit"]');
                const mesPrevio = selectMes.value || String(new Date().getMonth() + 1);
                selectMes.disabled = true;
                selectMes.innerHTML = '<option value="">Validando meses...</option>';
                if (inputArchivo) inputArchivo.disabled = true;
                if (submitBtn) submitBtn.disabled = true;

                return this.obtenerCalendarioImportacion(anio).then((calendario) => {
                    if (calendario === null) {
                        selectMes.innerHTML = '<option value="">No se pudieron validar meses</option>';
                        selectMes.disabled = true;
                        if (inputArchivo) inputArchivo.disabled = true;
                        if (submitBtn) submitBtn.disabled = true;
                        return;
                    }
                    const base = Array.isArray(calendario) && calendario.length
                        ? calendario
                        : meses.map((nombre, idx) => ({ mes: idx + 1, nombre_mes: nombre, presupuesto: null }));
                    const disponibles = base;
                    if (!disponibles.length) {
                        selectMes.innerHTML = '<option value="">Sin meses disponibles</option>';
                        selectMes.disabled = true;
                        if (inputArchivo) inputArchivo.disabled = true;
                        if (submitBtn) submitBtn.disabled = true;
                        return;
                    }
                    selectMes.innerHTML = disponibles.map(item => {
                        const mes = parseInt(item.mes, 10);
                        const nombre = item.nombre_mes || meses[mes - 1] || `Mes ${mes}`;
                        const etiqueta = item.presupuesto ? `${nombre} - Recargar presupuesto existente` : `${nombre} - Carga nueva`;
                        return `<option value="${mes}">${this.escape(etiqueta)}</option>`;
                    }).join('');
                    if ([...selectMes.options].some(opt => opt.value === mesPrevio)) {
                        selectMes.value = mesPrevio;
                    }
                    selectMes.disabled = false;
                    if (inputArchivo) inputArchivo.disabled = false;
                    if (submitBtn) submitBtn.disabled = false;
                });
            },

            mesImportacionYaCargado(anio, mes) {
                const calendario = this.calendariosPorAnio[parseInt(anio, 10)] || [];
                const item = calendario.find(row => parseInt(row.mes, 10) === parseInt(mes, 10));
                return !!(item && item.presupuesto);
            },

            avisoPresupuestoExistente(anio, mes) {
                const nombreMes = meses[(parseInt(mes, 10) || 1) - 1] || 'ese mes';
                const mensaje = `Ya existe un presupuesto cargado para ${nombreMes} ${anio}. Elimina el presupuesto actual antes de volver a cargarlo.`;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Presupuesto existente', text: mensaje, confirmButtonText: 'Entendido' });
                    return;
                }
                showError(mensaje);
            },

            renderCalendar() {
                const html = this.calendario.map(item => {
                    const p = item.presupuesto;
                    const active = this.detalle && p && parseInt(this.detalle.presupuesto.id, 10) === parseInt(p.id, 10) ? ' active' : '';
                    const body = p
                        ? `<span class="atlas-pres-badge atlas-pres-badge-ok"><i class="fa-solid fa-check"></i>Cargado</span>
                           <div class="atlas-pres-month-meta">${p.total_sucursales} sucursales<br>${this.money(p.total_cash)}</div>`
                        : `<span class="atlas-pres-badge atlas-pres-badge-muted"><i class="fa-solid fa-minus"></i>Sin presupuesto</span>
                           <div class="atlas-pres-month-meta atlas-pres-month-empty">Disponible para carga</div>`;
                    const disabled = p ? '' : ' disabled aria-disabled="true"';
                    const empty = p ? '' : ' is-empty';
                    return `<button type="button" class="atlas-pres-month${active}${empty}" ${p ? `data-pres-id="${p.id}"` : ''}${disabled}>
                        <div class="atlas-pres-month-name">${item.nombre_mes}</div>${body}
                    </button>`;
                }).join('');
                document.getElementById('atlasPresCalendar').innerHTML = html;
            },

            renderHistorial() {
                const body = document.getElementById('atlasPresHistorialBody');
                if ($.fn.DataTable.isDataTable('#atlasPresHistorialTabla')) $('#atlasPresHistorialTabla').DataTable().destroy();
                if (!this.historial.length) {
                    body.innerHTML = '<tr><td class="atlas-pres-empty" colspan="6">No hay presupuestos cargados para este año.</td></tr>';
                    return;
                }
                body.innerHTML = this.historial.map(p => {
                    p = p || {};
                    return `
                    <tr>
                        <td>
                            <div class="atlas-pres-main">${this.escape(p.nombre_mes)} ${p.anio}</div>
                            <div class="atlas-pres-sub">Actualizado ${this.escape(p.fecha_actualizacion_fmt || '')}</div>
                        </td>
                        <td><span class="atlas-pres-badge atlas-pres-badge-info">${this.number(p.total_sucursales || 0)}</span></td>
                        <td><strong>${this.number(p.total_creditos || 0)}</strong></td>
                        <td><strong>${this.money(p.total_cash || 0)}</strong></td>
                        <td><div class="atlas-pres-sub">${this.escape(p.archivo_original || 'Sin archivo')}</div></td>
                        <td class="text-center">
                            <span class="atlas-pres-actions">
                                <button type="button" class="btn btn-sm btn-label-primary" data-pres-ver="${p.id}" title="Ver mes"><i class="fa-solid fa-eye"></i></button>
                                <button type="button" class="btn btn-sm btn-label-secondary" data-pres-bitacora="${p.id}" title="Bit&aacute;cora del mes"><i class="fa-solid fa-clock-rotate-left"></i></button>
                                ${parseInt(p.puede_eliminar, 10) === 1
                                    ? `<button type="button" class="btn btn-sm btn-label-danger" data-pres-del="${p.id}" title="Borrar mes futuro"><i class="fa-solid fa-trash-can"></i></button>`
                                    : `<button type="button" class="btn btn-sm btn-label-secondary opacity-50" data-pres-del-blocked="${p.id}" data-pres-mes="${this.escape(p.nombre_mes || '')}" data-pres-anio="${this.escape(p.anio || '')}" title="No se puede borrar un presupuesto pasado o en curso"><i class="fa-solid fa-trash-can"></i></button>`}
                            </span>
                        </td>
                    </tr>
                `}).join('');
                this.inicializarTablaDom('#atlasPresHistorialTabla', 5);
            },

            cargarDetalle(id) {
                if (!id) return;
                http.request({
                    endpoint: '/Atlas/getPresupuestoDetalle',
                    metodo: 'GET',
                    data: { id },
                    showLoader: true,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            showError(resp?.mensaje || 'No se pudo cargar el mes.');
                            return;
                        }
                        this.detalle = resp.datos;
                        this.renderCalendar();
                        this.renderDetalle();
                    }
                });
            },

            renderDetalle() {
                const card = document.getElementById('atlasPresDetalleCard');
                const p = this.detalle.presupuesto;
                const detalles = this.detalle.detalles || [];
                document.getElementById('atlasPresListadoView').style.display = 'none';
                card.style.display = '';
                document.getElementById('atlasPresDetalleTitulo').innerHTML = `<i class="fa-solid fa-store me-2"></i>${this.escape(p.nombre_mes)} ${p.anio}`;
                document.getElementById('atlasPresDetalleSub').textContent = `${detalles.length} sucursales con meta mensual`;
                document.getElementById('atlasPresDetalleResumen').innerHTML = `
                    <div class="atlas-pres-chip"><span class="atlas-pres-chip-label">Sucursales</span><span class="atlas-pres-chip-value">${p.total_sucursales}</span></div>
                    <div class="atlas-pres-chip"><span class="atlas-pres-chip-label">Meta créditos</span><span class="atlas-pres-chip-value">${this.number(p.total_creditos)}</span></div>
                    <div class="atlas-pres-chip"><span class="atlas-pres-chip-label">Meta cash</span><span class="atlas-pres-chip-value">${this.money(p.total_cash)}</span></div>
                    <button type="button" class="atlas-pres-chip atlas-pres-chip--button" data-atlas-pres-ranking>
                        <span class="atlas-pres-chip-label">Ranking</span>
                        <span class="atlas-pres-chip-value"><i class="fa-solid fa-ranking-star me-1"></i>Top sucursales</span>
                    </button>
                `;

                const body = document.getElementById('atlasPresDetalleBody');
                if ($.fn.DataTable.isDataTable('#atlasPresDetalleTabla')) $('#atlasPresDetalleTabla').DataTable().destroy();
                body.innerHTML = detalles.map(d => `
                    <tr>
                        <td>
                            <div class="atlas-pres-main"><span class="atlas-pres-badge atlas-pres-badge-muted">FK ${this.escape(d.fk_sucursal)}</span></div>
                            <div class="atlas-pres-main mt-1">${this.escape(d.sucursal || 'Sin sucursal')}</div>
                            <div class="atlas-pres-sub">${this.escape(d.distribuidor || 'Sin distribuidor')}</div>
                        </td>
                        <td>${this.renderAsignacionesDetalle(d)}</td>
                        <td>${this.renderClasificacionBadge(d)}</td>
                        <td><strong>${this.number(d.meta_creditos)}</strong></td>
                        <td><span class="atlas-pres-badge atlas-pres-badge-warn">${this.comisionaDesde(d.comisiona_a_partir_de)}</span></td>
                        <td><strong>${this.money(d.meta_cash)}</strong></td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-label-primary" data-pres-distribuir="${d.id}" title="Distribuir presupuesto"><i class="fa-solid fa-users"></i></button>
                                <button type="button" class="btn btn-sm btn-label-secondary" data-pres-edit="${d.id}" title="Editar meta"><i class="fa-solid fa-pen"></i></button>
                                <button type="button" class="btn btn-sm btn-label-danger" data-pres-eliminar="${d.id}" title="Reasignar y eliminar sucursal"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </td>
                    </tr>
                `).join('');
                this.inicializarTablaDom('#atlasPresDetalleTabla', 10);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            renderAsignacionesDetalle(detalle) {
                const asignaciones = (detalle.asignaciones || []).filter(item => parseInt(item.persona_id, 10) > 0);
                if (!asignaciones.length) {
                    return `
                        <div class="atlas-pres-main">${this.escape(detalle.asesor || 'Sin responsable')}</div>
                        <div class="atlas-pres-sub">${detalle.asesor_persona_id ? `Persona ${this.escape(detalle.asesor_persona_id)}` : 'Sin distribución'}</div>`;
                }
                return asignaciones.map(item => `
                    <div class="mb-2">
                        <div class="atlas-pres-main">${this.escape(item.gestor || item.gestor_nombre || `Persona ${item.persona_id}`)}</div>
                        <div class="atlas-pres-sub">${this.number(item.meta_creditos || 0)} créditos · ${this.money(item.meta_cash || 0)}</div>
                    </div>
                `).join('');
            },

            abrirAsignarSucursal() {
                const presupuestoId = parseInt(this.detalle?.presupuesto?.id, 10);
                if (!presupuestoId) {
                    showError('Selecciona un presupuesto mensual.');
                    return;
                }
                const disponibles = this.detalle?.sucursales_disponibles || [];
                if (!disponibles.length) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin sucursales disponibles',
                            text: 'Todas las sucursales activas ya estan asignadas a este presupuesto.'
                        });
                    } else {
                        showInfo('Todas las sucursales activas ya estan asignadas a este presupuesto.');
                    }
                    return;
                }

                const form = document.getElementById('atlasPresEditForm');
                form.reset();
                document.getElementById('atlasPresEditId').value = '';
                document.getElementById('atlasPresEditPresupuestoId').value = presupuestoId;
                document.getElementById('atlasPresEditTitle').innerHTML = '<i class="fa-solid fa-circle-plus me-2"></i>Asignar sucursal';
                document.getElementById('atlasPresEditSub').textContent = `${this.detalle.presupuesto.nombre_mes} ${this.detalle.presupuesto.anio}`;
                document.getElementById('atlasPresEditSucursalActualWrap').classList.add('d-none');
                document.getElementById('atlasPresEditSucursalSelectWrap').classList.remove('d-none');
                document.getElementById('atlasPresEditCreditos').value = 0;
                document.getElementById('atlasPresEditCash').value = 0;

                const select = document.getElementById('atlasPresEditSucursalSelect');
                select.disabled = false;
                select.required = true;
                select.innerHTML = '<option value=""></option>' + disponibles.map(item => {
                    const distribuidor = item.distribuidor ? ` - ${this.escape(item.distribuidor)}` : '';
                    const asesor = item.asesor ? ` - ${this.escape(item.asesor)}` : '';
                    return `<option value="${this.escape(item.fk_sucursal)}">FK ${this.escape(item.fk_sucursal)} - ${this.escape(item.sucursal || 'Sin sucursal')}${distribuidor}${asesor}</option>`;
                }).join('');
                this.inicializarBuscadorSucursalPresupuesto();
                this.modalEdit.show();
            },

            inicializarBuscadorSucursalPresupuesto() {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
                const modal = jQuery('#modalAtlasPresupuestoEditar');
                const select = jQuery('#atlasPresEditSucursalSelect');
                if (select.hasClass('select2-hidden-accessible')) select.select2('destroy');
                select.select2({
                    width: '100%',
                    dropdownParent: modal,
                    placeholder: 'Buscar sucursal por FK, nombre o distribuidor',
                    allowClear: true,
                    minimumResultsForSearch: 0
                });
            },

            abrirReasignacion(detalleIdInicial = 0) {
                const presupuestoId = parseInt(this.detalle?.presupuesto?.id, 10);
                if (!presupuestoId) {
                    showError('Selecciona un presupuesto mensual.');
                    return;
                }
                const form = document.getElementById('atlasPresReasignarForm');
                form.reset();
                this.reasignacionSucursal = null;
                this.reasignacionAsignaciones = [];
                document.getElementById('atlasPresReasignarOrigen').innerHTML = '<option value="">Cargando sucursales...</option>';
                document.getElementById('atlasPresReasignarDestino').innerHTML = '';
                document.getElementById('atlasPresReasignarResumen').textContent = 'Consultando sucursales del presupuesto...';
                document.getElementById('atlasPresReasignarSucursales').innerHTML =
                    '<div class="atlas-pres-reassign-empty">Consultando distribución...</div>';
                document.getElementById('atlasPresReasignarTodas').disabled = true;
                form.querySelector('button[type="submit"]').disabled = true;

                this.cargarCatalogosReasignacion(() => {
                    this.poblarSelectSucursales('atlasPresReasignarOrigen');
                    this.poblarSelectGestores('atlasPresReasignarDestino');
                    this.inicializarBuscadoresReasignacion();
                    document.getElementById('atlasPresReasignarSub').textContent =
                        `${this.detalle.presupuesto.nombre_mes} ${this.detalle.presupuesto.anio}`;
                    this.modalReasignar.show();
                    if (parseInt(detalleIdInicial, 10) > 0) {
                        this.seleccionarValores('#atlasPresReasignarOrigen', [detalleIdInicial]);
                        this.cambiarSucursalDistribucion();
                    }
                });
            },

            cargarCatalogosReasignacion(onSuccess) {
                const presupuestoId = parseInt(this.detalle?.presupuesto?.id, 10);
                http.request({
                    endpoint: '/Atlas/getPresupuestoReasignacionCatalogos',
                    metodo: 'GET',
                    data: { id: presupuestoId },
                    showLoader: true,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            showError(resp?.mensaje || 'No se pudo cargar la distribución del presupuesto.');
                            return;
                        }
                        this.reasignacionCatalogos = resp.datos || {};
                        if (!(this.reasignacionCatalogos.sucursales || []).length) {
                            showError('Este presupuesto no tiene sucursales activas.');
                            return;
                        }
                        if (!(this.reasignacionCatalogos.usuarios_destino || []).length) {
                            showError('No hay colaboradores activos en Accesos Atlas.');
                            return;
                        }
                        onSuccess();
                    },
                    onError: (mensaje) => showError(mensaje || 'No se pudo cargar la distribución del presupuesto.')
                });
            },

            poblarSelectSucursales(id, excluirDetalleId = 0) {
                const sucursales = (this.reasignacionCatalogos?.sucursales || [])
                    .filter(item => parseInt(item.detalle_id, 10) !== parseInt(excluirDetalleId, 10));
                document.getElementById(id).innerHTML = '<option value=""></option>' + sucursales.map(item =>
                    `<option value="${parseInt(item.detalle_id, 10)}">FK ${this.escape(item.fk_sucursal)} - ${this.escape(item.sucursal || 'Sin sucursal')} - ${this.money(item.meta_cash || 0)}</option>`
                ).join('');
            },

            poblarSelectGestores(id) {
                document.getElementById(id).innerHTML = (this.reasignacionCatalogos?.usuarios_destino || []).map(usuario => {
                    const empleado = usuario.numero_empleado ? ` - ${this.escape(usuario.numero_empleado)}` : '';
                    const acceso = usuario.acceso_movil ? '' : ' - acceso móvil pendiente';
                    return `<option value="${parseInt(usuario.persona_id, 10)}">${this.escape(usuario.nombre || '')}${empleado}${acceso}</option>`;
                }).join('');
            },

            inicializarSelect2Distribucion(selector, placeholder, modalSelector, multiple = false, callback = null) {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
                const select = jQuery(selector);
                if (select.hasClass('select2-hidden-accessible')) select.select2('destroy');
                select.select2({
                    width: '100%',
                    dropdownParent: jQuery(modalSelector),
                    placeholder,
                    allowClear: true,
                    closeOnSelect: !multiple,
                    minimumResultsForSearch: 0
                });
                if (callback) {
                    select
                        .off('change.atlasPresDistribucion')
                        .on('change.atlasPresDistribucion', (ev) => {
                            if (!ev.originalEvent) callback();
                        });
                }
            },

            inicializarBuscadoresReasignacion() {
                this.inicializarSelect2Distribucion(
                    '#atlasPresReasignarOrigen',
                    'Buscar sucursal',
                    '#modalAtlasPresupuestoReasignar',
                    false,
                    () => this.cambiarSucursalDistribucion()
                );
                this.inicializarSelect2Distribucion(
                    '#atlasPresReasignarDestino',
                    'Buscar y seleccionar gestores',
                    '#modalAtlasPresupuestoReasignar',
                    true,
                    () => this.cambiarGestoresDistribucion()
                );
            },

            seleccionarValores(selector, values) {
                const normalized = values.map(value => String(value));
                const element = document.querySelector(selector);
                if (!element) return;
                Array.from(element.options).forEach(option => {
                    option.selected = normalized.includes(String(option.value));
                });
                if (window.jQuery && jQuery.fn && jQuery.fn.select2 && jQuery(selector).hasClass('select2-hidden-accessible')) {
                    jQuery(selector).val(normalized).trigger('change.select2');
                }
            },

            valoresSeleccionados(id) {
                return Array.from(document.getElementById(id)?.selectedOptions || [])
                    .map(option => parseInt(option.value, 10))
                    .filter(value => value > 0);
            },

            usuarioDistribucion(personaId) {
                return (this.reasignacionCatalogos?.usuarios_destino || [])
                    .find(usuario => parseInt(usuario.persona_id, 10) === parseInt(personaId, 10)) || null;
            },

            repartirValor(total, cantidad) {
                if (!cantidad) return [];
                const centavos = Math.round((Number(total) || 0) * 100);
                const base = Math.floor(centavos / cantidad);
                const residuo = centavos - (base * cantidad);
                return Array.from({ length: cantidad }, (_, index) => (base + (index < residuo ? 1 : 0)) / 100);
            },

            construirReparto(sucursal, personaIds, metaCreditos = null, metaCash = null) {
                const ids = [...new Set(personaIds.map(value => parseInt(value, 10)).filter(value => value > 0))];
                const creditos = this.repartirValor(metaCreditos ?? sucursal?.meta_creditos ?? 0, ids.length);
                const cash = this.repartirValor(metaCash ?? sucursal?.meta_cash ?? 0, ids.length);
                return ids.map((personaId, index) => {
                    const usuario = this.usuarioDistribucion(personaId) || {};
                    return {
                        persona_id: personaId,
                        gestor: usuario.nombre || usuario.user_name || `Persona ${personaId}`,
                        meta_creditos: creditos[index] || 0,
                        meta_cash: cash[index] || 0
                    };
                });
            },

            cambiarSucursalDistribucion() {
                const detalleId = parseInt(document.getElementById('atlasPresReasignarOrigen').value, 10);
                this.reasignacionSucursal = (this.reasignacionCatalogos?.sucursales || [])
                    .find(item => parseInt(item.detalle_id, 10) === detalleId) || null;
                if (!this.reasignacionSucursal) {
                    this.reasignacionAsignaciones = [];
                    this.seleccionarValores('#atlasPresReasignarDestino', []);
                    this.renderDistribucion('reasignacion');
                    return;
                }
                const existentes = (this.reasignacionSucursal.asignaciones || [])
                    .filter(item => parseInt(item.persona_id, 10) > 0)
                    .map(item => ({
                        persona_id: parseInt(item.persona_id, 10),
                        gestor: item.gestor || item.gestor_nombre || this.usuarioDistribucion(item.persona_id)?.nombre || `Persona ${item.persona_id}`,
                        meta_creditos: Number(item.meta_creditos) || 0,
                        meta_cash: Number(item.meta_cash) || 0
                    }));
                this.reasignacionAsignaciones = existentes;
                this.seleccionarValores('#atlasPresReasignarDestino', existentes.map(item => item.persona_id));
                this.renderDistribucion('reasignacion');
            },

            cambiarGestoresDistribucion() {
                if (!this.reasignacionSucursal) return;
                this.reasignacionAsignaciones = this.construirReparto(
                    this.reasignacionSucursal,
                    this.valoresSeleccionados('atlasPresReasignarDestino')
                );
                this.renderDistribucion('reasignacion');
            },

            cambiarMontoDistribucion(ev, modo) {
                const input = ev.target.closest('[data-atlas-pres-distribucion]');
                if (!input) return;
                const index = parseInt(input.dataset.index, 10);
                const campo = input.dataset.campo;
                const asignaciones = modo === 'eliminacion' ? this.eliminacionAsignaciones : this.reasignacionAsignaciones;
                if (!asignaciones[index] || !['meta_creditos', 'meta_cash'].includes(campo)) return;
                asignaciones[index][campo] = Math.max(0, Number(input.value) || 0);
                this.renderResumenDistribucion(modo);
            },

            totalesEsperados(modo) {
                if (modo === 'eliminacion') {
                    return {
                        meta_creditos: (Number(this.eliminacionOrigen?.meta_creditos) || 0) + (Number(this.eliminacionDestino?.meta_creditos) || 0),
                        meta_cash: (Number(this.eliminacionOrigen?.meta_cash) || 0) + (Number(this.eliminacionDestino?.meta_cash) || 0)
                    };
                }
                return {
                    meta_creditos: Number(this.reasignacionSucursal?.meta_creditos) || 0,
                    meta_cash: Number(this.reasignacionSucursal?.meta_cash) || 0
                };
            },

            estadoDistribucion(modo) {
                const asignaciones = modo === 'eliminacion' ? this.eliminacionAsignaciones : this.reasignacionAsignaciones;
                const esperado = this.totalesEsperados(modo);
                const actual = asignaciones.reduce((acc, item) => ({
                    meta_creditos: acc.meta_creditos + (Number(item.meta_creditos) || 0),
                    meta_cash: acc.meta_cash + (Number(item.meta_cash) || 0)
                }), { meta_creditos: 0, meta_cash: 0 });
                const valida = asignaciones.length > 0
                    && Math.round(actual.meta_creditos * 100) === Math.round(esperado.meta_creditos * 100)
                    && Math.round(actual.meta_cash * 100) === Math.round(esperado.meta_cash * 100);
                return { asignaciones, esperado, actual, valida };
            },

            renderDistribucion(modo) {
                const esEliminacion = modo === 'eliminacion';
                const contenedor = document.getElementById(esEliminacion ? 'atlasPresEliminarAsignaciones' : 'atlasPresReasignarSucursales');
                const asignaciones = esEliminacion ? this.eliminacionAsignaciones : this.reasignacionAsignaciones;
                const boton = document.getElementById(esEliminacion ? 'atlasPresEliminarRecalcular' : 'atlasPresReasignarTodas');
                boton.disabled = !asignaciones.length;
                if (!asignaciones.length) {
                    contenedor.innerHTML = `<div class="atlas-pres-reassign-empty">${esEliminacion ? 'Selecciona destino y gestores.' : 'Selecciona sucursal y gestores.'}</div>`;
                    this.renderResumenDistribucion(modo);
                    return;
                }
                contenedor.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Gestor</th><th style="width:150px">Meta créditos</th><th style="width:180px">Meta cash</th></tr></thead>
                            <tbody>
                                ${asignaciones.map((item, index) => `
                                    <tr>
                                        <td><strong>${this.escape(item.gestor || '')}</strong><div class="small text-muted">Persona ${this.escape(item.persona_id)}</div></td>
                                        <td><input class="form-control form-control-sm" type="number" min="0" step="0.01" value="${Number(item.meta_creditos || 0).toFixed(2)}" data-atlas-pres-distribucion data-index="${index}" data-campo="meta_creditos"></td>
                                        <td><input class="form-control form-control-sm" type="number" min="0" step="0.01" value="${Number(item.meta_cash || 0).toFixed(2)}" data-atlas-pres-distribucion data-index="${index}" data-campo="meta_cash"></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>`;
                this.renderResumenDistribucion(modo);
            },

            renderResumenDistribucion(modo) {
                const estado = this.estadoDistribucion(modo);
                if (modo === 'reasignacion') {
                    const resumen = document.getElementById('atlasPresReasignarResumen');
                    if (!this.reasignacionSucursal) {
                        resumen.textContent = 'Selecciona una sucursal para revisar su presupuesto.';
                    } else {
                        resumen.innerHTML = `
                            <div class="fw-bold">${this.escape(this.reasignacionSucursal.sucursal || '')}</div>
                            <div class="small">Total protegido: ${this.number(estado.esperado.meta_creditos)} créditos · ${this.money(estado.esperado.meta_cash)}</div>
                            <div class="small fw-bold ${estado.valida ? 'text-success' : 'text-danger'}">
                                Repartido: ${this.number(estado.actual.meta_creditos)} créditos · ${this.money(estado.actual.meta_cash)} ${estado.valida ? '· Cuadrado' : '· Ajusta las diferencias'}
                            </div>`;
                    }
                    const submit = document.querySelector('#atlasPresReasignarForm button[type="submit"]');
                    const motivo = document.getElementById('atlasPresReasignarMotivo')?.value.trim() || '';
                    if (submit) submit.disabled = !(estado.valida && motivo.length >= 5);
                } else {
                    this.actualizarEstadoEliminarSucursal();
                }
            },

            recalcularDistribucion(modo) {
                if (modo === 'eliminacion') {
                    this.eliminacionAsignaciones = this.construirReparto(
                        this.eliminacionDestino,
                        this.eliminacionAsignaciones.map(item => item.persona_id),
                        this.totalesEsperados('eliminacion').meta_creditos,
                        this.totalesEsperados('eliminacion').meta_cash
                    );
                } else {
                    this.reasignacionAsignaciones = this.construirReparto(
                        this.reasignacionSucursal,
                        this.reasignacionAsignaciones.map(item => item.persona_id)
                    );
                }
                this.renderDistribucion(modo);
            },

            async reasignarPresupuesto(ev) {
                ev.preventDefault();
                const form = ev.currentTarget;
                const submitBtn = form?.querySelector('button[type="submit"]');
                const motivo = document.getElementById('atlasPresReasignarMotivo').value.trim();
                const presupuestoId = parseInt(this.detalle?.presupuesto?.id, 10);
                const estado = this.estadoDistribucion('reasignacion');
                if (!this.reasignacionSucursal || !presupuestoId || motivo.length < 5 || !estado.valida) {
                    showError('Selecciona sucursal y gestores, cuadra el reparto y captura el motivo.');
                    return;
                }
                const confirmado = typeof Swal === 'undefined'
                    ? window.confirm('¿Guardar esta distribución de presupuesto?')
                    : await Swal.fire({
                        icon: 'question',
                        title: 'Aprobar distribución',
                        html: `Se conservarán <strong>${this.money(estado.esperado.meta_cash)}</strong> y se repartirán entre <strong>${estado.asignaciones.length} gestores</strong>.`,
                        showCancelButton: true,
                        confirmButtonText: 'Aprobar y guardar',
                        cancelButtonText: 'Revisar'
                    }).then(result => !!result.isConfirmed);
                if (!confirmado) return;
                if (submitBtn) submitBtn.disabled = true;
                http.request({
                    endpoint: '/Atlas/reasignarPresupuesto',
                    metodo: 'POST',
                    data: JSON.stringify({
                        presupuesto_id: presupuestoId,
                        detalle_id: parseInt(this.reasignacionSucursal.detalle_id, 10),
                        asignaciones: estado.asignaciones.map(item => ({
                            persona_id: parseInt(item.persona_id, 10),
                            meta_creditos: Number(item.meta_creditos || 0).toFixed(2),
                            meta_cash: Number(item.meta_cash || 0).toFixed(2)
                        })),
                        motivo
                    }),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: true,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            showError(resp?.mensaje || 'No se pudo guardar la distribución.');
                            return;
                        }
                        this.modalReasignar.hide();
                        showSuccess(resp.mensaje || 'Distribución guardada.');
                        this.cargarDetalle(presupuestoId);
                        this.cargar();
                    },
                    onError: (mensaje) => showError(mensaje || 'No se pudo guardar la distribución.'),
                    onAlways: () => {
                        const motivoActual = document.getElementById('atlasPresReasignarMotivo')?.value.trim() || '';
                        if (submitBtn) {
                            submitBtn.disabled = !(this.estadoDistribucion('reasignacion').valida && motivoActual.length >= 5);
                        }
                    }
                });
            },

            abrirEliminarSucursal(row) {
                const detalles = this.detalle?.detalles || [];
                if (detalles.length < 2) {
                    showError('No existe otra sucursal en este presupuesto para recibir la reasignación.');
                    return;
                }
                document.getElementById('atlasPresEliminarSucursalForm').reset();
                document.getElementById('atlasPresEliminarDestino').innerHTML = '<option value="">Cargando sucursales...</option>';
                document.getElementById('atlasPresEliminarGestores').innerHTML = '';
                document.getElementById('atlasPresEliminarAsignaciones').innerHTML =
                    '<div class="atlas-pres-reassign-empty">Consultando distribución...</div>';
                this.eliminacionOrigen = null;
                this.eliminacionDestino = null;
                this.eliminacionAsignaciones = [];
                document.getElementById('atlasPresEliminarConfirmar').disabled = true;
                this.cargarCatalogosReasignacion(() => {
                    this.eliminacionOrigen = (this.reasignacionCatalogos?.sucursales || [])
                        .find(item => parseInt(item.detalle_id, 10) === parseInt(row.id, 10)) || null;
                    if (!this.eliminacionOrigen) {
                        showError('La sucursal ya no se encuentra activa en el presupuesto.');
                        return;
                    }
                    this.poblarSelectSucursales('atlasPresEliminarDestino', row.id);
                    this.poblarSelectGestores('atlasPresEliminarGestores');
                    this.inicializarSelect2Distribucion(
                        '#atlasPresEliminarDestino',
                        'Buscar sucursal destino',
                        '#modalAtlasPresupuestoEliminarSucursal',
                        false,
                        () => this.cambiarDestinoEliminacion()
                    );
                    this.inicializarSelect2Distribucion(
                        '#atlasPresEliminarGestores',
                        'Buscar y seleccionar gestores',
                        '#modalAtlasPresupuestoEliminarSucursal',
                        true,
                        () => this.cambiarGestoresEliminacion()
                    );
                    document.getElementById('atlasPresEliminarSucursalSub').textContent =
                        `${this.detalle.presupuesto.nombre_mes} ${this.detalle.presupuesto.anio}`;
                    document.getElementById('atlasPresEliminarOrigenResumen').innerHTML = `
                        <div class="fw-bold">${this.escape(this.eliminacionOrigen.sucursal || '')}</div>
                        <div class="small">Se moverán ${this.number(this.eliminacionOrigen.meta_creditos || 0)} créditos y ${this.money(this.eliminacionOrigen.meta_cash || 0)} antes de eliminarla.</div>`;
                    this.renderDistribucion('eliminacion');
                    this.modalEliminarSucursal.show();
                });
            },

            cambiarDestinoEliminacion() {
                const detalleId = parseInt(document.getElementById('atlasPresEliminarDestino').value, 10);
                this.eliminacionDestino = (this.reasignacionCatalogos?.sucursales || [])
                    .find(item => parseInt(item.detalle_id, 10) === detalleId) || null;
                if (!this.eliminacionDestino) {
                    this.eliminacionAsignaciones = [];
                    this.seleccionarValores('#atlasPresEliminarGestores', []);
                    this.renderDistribucion('eliminacion');
                    return;
                }
                const ids = [
                    ...(this.eliminacionOrigen?.asignaciones || []).map(item => parseInt(item.persona_id, 10)),
                    ...(this.eliminacionDestino?.asignaciones || []).map(item => parseInt(item.persona_id, 10))
                ].filter(value => value > 0);
                this.seleccionarValores('#atlasPresEliminarGestores', [...new Set(ids)]);
                this.eliminacionAsignaciones = this.construirReparto(
                    this.eliminacionDestino,
                    ids,
                    this.totalesEsperados('eliminacion').meta_creditos,
                    this.totalesEsperados('eliminacion').meta_cash
                );
                this.renderDistribucion('eliminacion');
            },

            cambiarGestoresEliminacion() {
                if (!this.eliminacionDestino) return;
                this.eliminacionAsignaciones = this.construirReparto(
                    this.eliminacionDestino,
                    this.valoresSeleccionados('atlasPresEliminarGestores'),
                    this.totalesEsperados('eliminacion').meta_creditos,
                    this.totalesEsperados('eliminacion').meta_cash
                );
                this.renderDistribucion('eliminacion');
            },

            actualizarEstadoEliminarSucursal() {
                const estado = this.estadoDistribucion('eliminacion');
                const motivo = document.getElementById('atlasPresEliminarMotivo')?.value.trim() || '';
                const boton = document.getElementById('atlasPresEliminarConfirmar');
                if (boton) boton.disabled = !(this.eliminacionOrigen && this.eliminacionDestino && estado.valida && motivo.length >= 5);
            },

            async eliminarSucursal(ev) {
                ev.preventDefault();
                const presupuestoId = parseInt(this.detalle?.presupuesto?.id, 10);
                const motivo = document.getElementById('atlasPresEliminarMotivo').value.trim();
                const estado = this.estadoDistribucion('eliminacion');
                if (!presupuestoId || !this.eliminacionOrigen || !this.eliminacionDestino || !estado.valida || motivo.length < 5) {
                    showError('Completa y cuadra la reasignación antes de eliminar.');
                    return;
                }
                const confirmado = typeof Swal === 'undefined'
                    ? window.confirm('¿Reasignar el presupuesto y eliminar la sucursal anterior?')
                    : await Swal.fire({
                        icon: 'warning',
                        title: 'Confirmar reasignación y eliminación',
                        html: `
                            <div class="text-start">
                                <div><strong>Origen:</strong> ${this.escape(this.eliminacionOrigen.sucursal || '')}</div>
                                <div><strong>Destino:</strong> ${this.escape(this.eliminacionDestino.sucursal || '')}</div>
                                <div><strong>Total conservado:</strong> ${this.money(estado.esperado.meta_cash)}</div>
                            </div>`,
                        showCancelButton: true,
                        confirmButtonText: 'Sí, reasignar y eliminar',
                        cancelButtonText: 'Revisar',
                        confirmButtonColor: '#d33'
                    }).then(result => !!result.isConfirmed);
                if (!confirmado) return;
                const submitBtn = document.getElementById('atlasPresEliminarConfirmar');
                submitBtn.disabled = true;
                http.request({
                    endpoint: '/Atlas/eliminarPresupuestoSucursal',
                    metodo: 'POST',
                    data: JSON.stringify({
                        presupuesto_id: presupuestoId,
                        detalle_id: parseInt(this.eliminacionOrigen.detalle_id, 10),
                        destino_detalle_id: parseInt(this.eliminacionDestino.detalle_id, 10),
                        asignaciones_destino: estado.asignaciones.map(item => ({
                            persona_id: parseInt(item.persona_id, 10),
                            meta_creditos: Number(item.meta_creditos || 0).toFixed(2),
                            meta_cash: Number(item.meta_cash || 0).toFixed(2)
                        })),
                        motivo
                    }),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: true,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            showError(resp?.mensaje || 'No se pudo eliminar la sucursal.');
                            return;
                        }
                        this.modalEliminarSucursal.hide();
                        showSuccess(resp.mensaje || 'Sucursal eliminada y presupuesto reasignado.');
                        this.cargarDetalle(presupuestoId);
                        this.cargar();
                    },
                    onError: (mensaje) => showError(mensaje || 'No se pudo eliminar la sucursal.'),
                    onAlways: () => this.actualizarEstadoEliminarSucursal()
                });
            },

            inicializarTablaDom(selector, registrosPorPagina) {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
                if (jQuery.fn.DataTable.isDataTable(selector)) {
                    jQuery(selector).DataTable().destroy();
                }
                const tabla = jQuery(selector).DataTable({
                    pageLength: registrosPorPagina,
                    lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Todos']],
                    pagingType: 'full_numbers',
                    order: [],
                    autoWidth: false,
                    responsive: true,
                    language: {
                        emptyTable: 'No hay datos disponibles',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Sin registros para mostrar',
                        zeroRecords: 'No se encontraron registros',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        search: 'Buscar:',
                        paginate: { first: '«', last: '»', next: '›', previous: '‹' }
                    },
                    drawCallback: () => this.repararPaginacion(selector)
                });
                this.repararPaginacion(selector, tabla);
            },

            repararPaginacion(selector, tablaInstancia) {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable || !jQuery.fn.DataTable.isDataTable(selector)) return;
                const tabla = tablaInstancia || jQuery(selector).DataTable();
                const info = tabla.page.info();
                if (!info || info.pages <= 1) return;
                const wrapper = jQuery(selector).closest('.dataTables_wrapper, .dt-container');
                const paginador = wrapper.find('.dataTables_paginate ul.pagination, .dt-paging ul.pagination').first();
                if (!paginador.length) return;

                const paginaActual = info.page;
                const totalPaginas = info.pages;
                const paginas = new Set([0, totalPaginas - 1]);
                const inicio = Math.max(0, paginaActual - 2);
                const fin = Math.min(totalPaginas - 1, Math.max(4, paginaActual + 2));
                for (let i = inicio; i <= fin; i += 1) paginas.add(i);

                const items = [
                    this.itemPaginacion('«', 0, paginaActual === 0, false, tabla),
                    this.itemPaginacion('‹', Math.max(0, paginaActual - 1), paginaActual === 0, false, tabla)
                ];
                let anterior = -1;
                Array.from(paginas).sort((a, b) => a - b).forEach(pagina => {
                    if (anterior >= 0 && pagina - anterior > 1) items.push(this.itemPaginacion('…', null, true, false, tabla));
                    items.push(this.itemPaginacion(String(pagina + 1), pagina, false, pagina === paginaActual, tabla));
                    anterior = pagina;
                });
                items.push(this.itemPaginacion('›', Math.min(totalPaginas - 1, paginaActual + 1), paginaActual === totalPaginas - 1, false, tabla));
                items.push(this.itemPaginacion('»', totalPaginas - 1, paginaActual === totalPaginas - 1, false, tabla));
                paginador.empty().append(items);
            },

            itemPaginacion(texto, pagina, deshabilitado, activo, tabla) {
                const li = jQuery('<li/>', { class: `paginate_button page-item${deshabilitado ? ' disabled' : ''}${activo ? ' active' : ''}` });
                const link = jQuery('<a/>', { href: '#', class: 'page-link', text: texto });
                if (!deshabilitado && pagina !== null) {
                    link.on('click', ev => {
                        ev.preventDefault();
                        tabla.page(pagina).draw('page');
                    });
                }
                return li.append(link);
            },

            regresarListado() {
                document.getElementById('atlasPresDetalleCard').style.display = 'none';
                document.getElementById('atlasPresListadoView').style.display = '';
                this.detalle = null;
                this.renderCalendar();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            renderClasificacionBadge(row) {
                const nombre = row?.clasificacion || 'Sin clasificacion';
                const color = /^#[0-9a-fA-F]{6}$/.test(String(row?.clasificacion_color_hex || ''))
                    ? row.clasificacion_color_hex
                    : '';
                const icon = /^[a-z0-9\-\s]+$/i.test(String(row?.clasificacion_icon_font || ''))
                    ? String(row.clasificacion_icon_font || '').trim()
                    : '';
                const style = color
                    ? ` style="border-color:${this.escape(color)}33;color:${this.escape(color)};background:${this.escape(color)}12;"`
                    : '';
                const title = row?.clasificacion_id ? ` title="Clasificacion ID ${this.escape(row.clasificacion_id)}"` : '';
                return `<span class="atlas-pres-badge atlas-pres-badge-info"${style}${title}>${icon ? `<i class="${this.escape(icon)} me-1"></i>` : ''}${this.escape(nombre)}</span>`;
            },

            comisionaDesde(valor) {
                if (valor === null || valor === undefined || valor === '') return 'No capturado';
                const numero = Number(valor);
                if (!Number.isFinite(numero)) return this.escape(String(valor));
                return `${this.number(numero)} crédito${Math.abs(numero) === 1 ? '' : 's'}`;
            },

            abrirRanking() {
                if (!this.detalle) return;
                const p = this.detalle.presupuesto;
                document.getElementById('atlasPresRankingSub').textContent = `${p.nombre_mes} ${p.anio} · Top de sucursales`;
                document.getElementById('atlasPresRankingPeriodo').value = 'mes';
                document.getElementById('atlasPresRankingSemana').value = '1';
                document.getElementById('atlasPresRankingOrden').value = 'cash';
                this.modalRanking.show();
                this.renderRanking();
            },

            async renderRanking() {
                if (!this.detalle) return;
                const periodo = document.getElementById('atlasPresRankingPeriodo').value || 'mes';
                const orden = document.getElementById('atlasPresRankingOrden').value || 'cash';
                const semana = document.getElementById('atlasPresRankingSemana').value || '1';
                document.getElementById('atlasPresRankingSemanaWrap').style.display = periodo === 'semana' ? '' : 'none';
                document.getElementById('atlasPresRankingLista').innerHTML = '<div class="atlas-pres-empty">Cargando ranking...</div>';

                try {
                    const id = parseInt(this.detalle.presupuesto.id, 10);
                    const params = new URLSearchParams({ id, periodo, semana, orden });
                    const resp = await fetch('/Atlas/getPresupuestoRanking?' + params.toString(), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).then(r => r.json());
                    if (resp && resp.success && resp.datos && Array.isArray(resp.datos.ranking)) {
                        const usarReal = !!resp.datos.vendido_real_disponible;
                        const divisorReal = !usarReal && periodo === 'semana' ? 4 : 1;
                        const rowsReal = resp.datos.ranking;
                        document.getElementById('atlasPresRankingNota').textContent = usarReal
                            ? 'Ranking por vendido/avance registrado en Sparta para el periodo seleccionado.'
                            : 'Aun no hay vendido/avance registrado para este periodo; se muestra ranking por meta como referencia.';
                        document.getElementById('atlasPresRankingLista').innerHTML = rowsReal.length ? rowsReal.map((row, idx) => {
                            const metaCash = (Number(row.meta_cash) || 0) / divisorReal;
                            const avanzado = usarReal ? (Number(row.cash_avanzado ?? row.cash_vendido) || 0) : 0;
                            const detenido = usarReal ? (Number(row.cash_detenido) || 0) : metaCash;
                            const creditos = usarReal ? (Number(row.creditos_vendidos) || 0) : ((Number(row.meta_creditos) || 0) / divisorReal);
                            const avance = metaCash > 0 ? Math.min(100, Math.max(0, (avanzado / metaCash) * 100)) : 0;
                            const clase = idx < 5 ? ' is-top' : (idx >= Math.max(rowsReal.length - 30, 5) ? ' is-low' : '');
                            return `
                                <div class="atlas-pres-rank-row${clase}">
                                    <span class="atlas-pres-rank-num">${idx + 1}</span>
                                    <div>
                                        <div class="atlas-pres-rank-name">${this.escape(row.sucursal || 'Sin sucursal')}</div>
                                        <div class="atlas-pres-rank-meta">FK ${this.escape(row.fk_sucursal)} - ${this.escape(row.distribuidor || 'Sin distribuidor')} - ${this.escape(row.clasificacion || 'Sin clasificacion')}</div>
                                    </div>
                                    <div class="atlas-pres-rank-value">
                                        <div class="atlas-pres-rank-metric"><span>Detenido</span><strong>${this.money(detenido)}</strong></div>
                                        <div class="atlas-pres-rank-metric"><span>Avanzado</span><strong>${this.money(avanzado)}</strong></div>
                                        <div class="atlas-pres-rank-metric"><span></span><strong>${this.percent(avance)} avance</strong></div>
                                        <div class="atlas-pres-rank-meta">${this.number(creditos)} creditos</div>
                                    </div>
                                </div>
                            `;
                        }).join('') : '<div class="atlas-pres-empty">Sin sucursales para ranking.</div>';
                        return;
                    }
                } catch (e) {}

                const divisor = periodo === 'semana' ? 4 : 1;
                const rows = [...(this.detalle.detalles || [])].sort((a, b) => {
                    const av = Number(orden === 'cash' ? a.meta_cash : a.meta_creditos) || 0;
                    const bv = Number(orden === 'cash' ? b.meta_cash : b.meta_creditos) || 0;
                    return bv - av;
                });

                document.getElementById('atlasPresRankingNota').textContent = periodo === 'semana'
                    ? 'Ranking semanal estimado con base en el presupuesto mensual dividido en 4 semanas. Pendiente conectar ventas reales.'
                    : 'Ranking calculado con el presupuesto mensual cargado. Al conectar ventas reales, esta misma vista mostrará vendido real.';

                document.getElementById('atlasPresRankingLista').innerHTML = rows.length ? rows.map((row, idx) => {
                    const metaCash = (Number(row.meta_cash) || 0) / divisor;
                    const creditos = (Number(row.meta_creditos) || 0) / divisor;
                    const clase = idx < 5 ? ' is-top' : (idx >= Math.max(rows.length - 30, 5) ? ' is-low' : '');
                    return `
                        <div class="atlas-pres-rank-row${clase}">
                            <span class="atlas-pres-rank-num">${idx + 1}</span>
                            <div>
                                <div class="atlas-pres-rank-name">${this.escape(row.sucursal || 'Sin sucursal')}</div>
                                <div class="atlas-pres-rank-meta">FK ${this.escape(row.fk_sucursal)} - ${this.escape(row.distribuidor || 'Sin distribuidor')} - ${this.escape(row.clasificacion || 'Sin clasificacion')}</div>
                            </div>
                            <div class="atlas-pres-rank-value">
                                <div class="atlas-pres-rank-metric"><span>Detenido</span><strong>${this.money(metaCash)}</strong></div>
                                <div class="atlas-pres-rank-metric"><span>Avanzado</span><strong>${this.money(0)}</strong></div>
                                <div class="atlas-pres-rank-metric"><span></span><strong>0% avance</strong></div>
                                <div class="atlas-pres-rank-meta">${this.number(creditos)} creditos</div>
                            </div>
                        </div>
                    `;
                }).join('') : '<div class="atlas-pres-empty">Sin sucursales para ranking.</div>';
            },

            handleHistorial(ev) {
                const ver = ev.target.closest('[data-pres-ver]');
                if (ver) {
                    this.cargarDetalle(parseInt(ver.getAttribute('data-pres-ver'), 10));
                    return;
                }
                const bitacora = ev.target.closest('[data-pres-bitacora]');
                if (bitacora) {
                    this.abrirBitacora(parseInt(bitacora.getAttribute('data-pres-bitacora'), 10));
                    return;
                }
                const delBlocked = ev.target.closest('[data-pres-del-blocked]');
                if (delBlocked) {
                    this.avisoEliminarBloqueado(delBlocked);
                    return;
                }
                const del = ev.target.closest('[data-pres-del]');
                if (del) this.eliminar(parseInt(del.getAttribute('data-pres-del'), 10));
            },

            avisoEliminarBloqueado(btn) {
                const mes = btn.getAttribute('data-pres-mes') || 'este mes';
                const anio = btn.getAttribute('data-pres-anio') || '';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'No se puede eliminar',
                        text: `El presupuesto de ${mes} ${anio} no puede eliminarse porque es un mes pasado o en curso. Solo se permite borrar presupuestos futuros.`,
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                showInfo('No se puede eliminar un presupuesto pasado o en curso.');
            },

            abrirBitacora(id) {
                const params = new URLSearchParams();
                if (id) params.set('id', id);
                else params.set('anio', this.anio);
                document.getElementById('atlasPresBitacoraSub').textContent = id ? 'Movimientos del mes seleccionado.' : `Movimientos del año ${this.anio}.`;
                document.getElementById('atlasPresBitacoraResumen').innerHTML = '';
                document.getElementById('atlasPresBitacoraLista').innerHTML = '<div class="atlas-pres-empty">Cargando bitácora...</div>';
                this.modalBitacora.show();
                http.request({
                    endpoint: '/Atlas/getPresupuestoBitacora',
                    metodo: 'GET',
                    data: Object.fromEntries(params.entries()),
                    showLoader: false,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            document.getElementById('atlasPresBitacoraLista').innerHTML = '<div class="atlas-pres-empty">No se pudo cargar la bitácora.</div>';
                            return;
                        }
                        this.renderBitacora(resp.datos || {}, !!id);
                    }
                });
            },

            renderBitacora(data, porMes) {
                const resumen = data.resumen || {};
                const presupuesto = data.presupuesto || null;
                document.getElementById('atlasPresBitacoraSub').textContent = presupuesto
                    ? `${this.escape(presupuesto.nombre_mes || '')} ${this.escape(presupuesto.anio || '')}`
                    : `Movimientos del año ${this.escape(data.anio || this.anio)}`;
                document.getElementById('atlasPresBitacoraResumen').innerHTML = `
                    <div class="atlas-pres-chip"><span class="atlas-pres-chip-label">Carga</span><span class="atlas-pres-chip-value">${this.escape(resumen.ultima_carga || presupuesto?.fecha_alta_fmt || 'Sin registro')}</span></div>
                    <div class="atlas-pres-chip"><span class="atlas-pres-chip-label">Modificaciones</span><span class="atlas-pres-chip-value">${this.number(resumen.total_modificaciones || 0)}</span></div>
                    <div class="atlas-pres-chip"><span class="atlas-pres-chip-label">Eliminacion</span><span class="atlas-pres-chip-value">${this.escape(resumen.ultima_eliminacion || 'Sin eliminacion')}</span></div>
                    <div class="atlas-pres-chip"><span class="atlas-pres-chip-label">Inicio operacion</span><span class="atlas-pres-chip-value">${this.escape(resumen.inicio_operacion || (porMes ? 'Sin fecha' : 'Por mes'))}</span></div>
                `;
                const eventos = data.eventos || [];
                document.getElementById('atlasPresBitacoraLista').innerHTML = eventos.length ? eventos.map(ev => `
                    <div class="atlas-pres-timeline-row">
                        <span class="atlas-pres-timeline-icon"><i class="${this.escape(this.iconoEvento(ev.evento))}"></i></span>
                        <div>
                            <div class="atlas-pres-timeline-main">${this.escape(this.tituloEvento(ev))}</div>
                            <div class="atlas-pres-timeline-sub">${this.escape(ev.descripcion || 'Movimiento de presupuesto.')}</div>
                            ${this.detalleEvento(ev)}
                            <div class="atlas-pres-timeline-sub"><i class="fa-solid fa-user me-1"></i>${this.escape(ev.usuario_nombre || 'Sistema')}</div>
                        </div>
                        <div class="atlas-pres-timeline-date">${this.escape(ev.fecha_evento_fmt || '')}</div>
                    </div>
                `).join('') : '<div class="atlas-pres-empty">Sin movimientos registrados.</div>';
            },

            tituloEvento(ev) {
                const mapa = {
                    carga: 'Carga de presupuesto',
                    recarga: 'Recarga de presupuesto',
                    modificacion_sucursal: 'Modificacion de sucursal',
                    alta_sucursal: 'Alta de sucursal en presupuesto',
                    desactivacion_sucursal: 'Sucursal desactivada del presupuesto',
                    reasignacion_asesor: 'Reasignacion de responsable',
                    redistribucion_gestores: 'Distribucion entre gestores',
                    reasignacion_sucursal: 'Presupuesto trasladado a otra sucursal',
                    eliminacion_sucursal: 'Sucursal eliminada con reasignacion',
                    eliminacion: 'Eliminacion de presupuesto',
                    carga_inicial: 'Carga inicial'
                };
                const base = mapa[ev.evento] || 'Movimiento';
                return ev.mes && ev.anio ? `${base} - ${meses[(Number(ev.mes) || 1) - 1] || ev.mes} ${ev.anio}` : base;
            },

            iconoEvento(evento) {
                if (evento === 'modificacion_sucursal') return 'fa-solid fa-pen';
                if (evento === 'alta_sucursal') return 'fa-solid fa-circle-plus';
                if (evento === 'desactivacion_sucursal') return 'fa-solid fa-circle-minus';
                if (evento === 'reasignacion_asesor') return 'fa-solid fa-user-pen';
                if (evento === 'redistribucion_gestores') return 'fa-solid fa-users';
                if (evento === 'reasignacion_sucursal') return 'fa-solid fa-arrow-right-arrow-left';
                if (evento === 'eliminacion_sucursal') return 'fa-solid fa-store-slash';
                if (evento === 'eliminacion') return 'fa-solid fa-trash-can';
                if (evento === 'recarga') return 'fa-solid fa-file-import';
                return 'fa-solid fa-file-excel';
            },

            detalleEvento(ev) {
                const partes = [];
                if (ev.total_sucursales) partes.push(`${this.number(ev.total_sucursales)} sucursales`);
                if (ev.archivo_original) partes.push(`Archivo: ${this.escape(ev.archivo_original)}`);
                if (ev.fk_sucursal) partes.push(`FK ${this.escape(ev.fk_sucursal)}`);
                if (['modificacion_sucursal', 'alta_sucursal', 'desactivacion_sucursal'].includes(ev.evento)) {
                    partes.push(`Creditos: ${this.number(ev.meta_creditos_anterior || 0)} -> ${this.number(ev.meta_creditos_nueva || 0)}`);
                    partes.push(`Cash: ${this.money(ev.meta_cash_anterior || 0)} -> ${this.money(ev.meta_cash_nueva || 0)}`);
                }
                if (ev.evento === 'reasignacion_asesor' && ev.payload_json) {
                    const payload = this.payloadEvento(ev);
                    const anterior = payload?.antes?.asesor || 'Sin responsable';
                    const nuevo = payload?.despues?.asesor || 'Sin responsable';
                    partes.push(`${this.escape(anterior)} -> ${this.escape(nuevo)}`);
                    if (payload?.motivo) partes.push(`Motivo: ${this.escape(payload.motivo)}`);
                }
                if (ev.evento === 'redistribucion_gestores') {
                    const payload = this.payloadEvento(ev);
                    partes.push(this.comparativoAsignaciones(payload?.antes, payload?.despues));
                    if (payload?.motivo) partes.push(`Motivo: ${this.escape(payload.motivo)}`);
                }
                if (['reasignacion_sucursal', 'eliminacion_sucursal'].includes(ev.evento)) {
                    const payload = this.payloadEvento(ev);
                    const origen = payload?.origen || {};
                    const destino = payload?.destino || {};
                    partes.push(
                        `Origen ${this.escape(origen?.antes?.sucursal || '')}: ${this.money(origen?.antes?.meta_cash || 0)} -> ${this.money(origen?.despues?.meta_cash || 0)}`
                    );
                    partes.push(
                        `Destino ${this.escape(destino?.antes?.sucursal || '')}: ${this.money(destino?.antes?.meta_cash || 0)} -> ${this.money(destino?.despues?.meta_cash || 0)}`
                    );
                    partes.push(this.comparativoAsignaciones(destino?.antes, destino?.despues));
                    if (payload?.motivo) partes.push(`Motivo: ${this.escape(payload.motivo)}`);
                }
                return partes.length ? `<div class="atlas-pres-timeline-sub">${partes.join(' | ')}</div>` : '';
            },

            payloadEvento(ev) {
                if (!ev?.payload_json) return {};
                if (typeof ev.payload_json !== 'string') return ev.payload_json;
                try {
                    return JSON.parse(ev.payload_json);
                } catch (err) {
                    return {};
                }
            },

            comparativoAsignaciones(antes, despues) {
                const texto = (snapshot) => (snapshot?.asignaciones || []).map(item =>
                    `${this.escape(item.gestor || `Persona ${item.persona_id}`)} ${this.money(item.meta_cash || 0)}`
                ).join(', ') || 'Sin asignaciones';
                return `Reparto: ${texto(antes)} -> ${texto(despues)}`;
            },

            handleDetalle(ev) {
                const btn = ev.target.closest('[data-pres-edit], [data-pres-distribuir], [data-pres-eliminar]');
                if (!btn || !this.detalle) return;
                const id = parseInt(
                    btn.getAttribute('data-pres-edit')
                    || btn.getAttribute('data-pres-distribuir')
                    || btn.getAttribute('data-pres-eliminar'),
                    10
                );
                const row = (this.detalle.detalles || []).find(x => parseInt(x.id, 10) === id);
                if (!row) return;
                if (btn.hasAttribute('data-pres-distribuir')) {
                    this.abrirReasignacion(row.id);
                    return;
                }
                if (btn.hasAttribute('data-pres-eliminar')) {
                    this.abrirEliminarSucursal(row);
                    return;
                }
                document.getElementById('atlasPresEditForm').reset();
                document.getElementById('atlasPresEditId').value = row.id;
                document.getElementById('atlasPresEditPresupuestoId').value = this.detalle?.presupuesto?.id || '';
                document.getElementById('atlasPresEditTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Editar meta de sucursal';
                document.getElementById('atlasPresEditSucursal').value = `FK ${row.fk_sucursal} · ${row.sucursal || ''}`;
                document.getElementById('atlasPresEditSucursalActualWrap').classList.remove('d-none');
                document.getElementById('atlasPresEditSucursalSelectWrap').classList.add('d-none');
                const selectSucursal = document.getElementById('atlasPresEditSucursalSelect');
                selectSucursal.required = false;
                selectSucursal.disabled = true;
                if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
                    const select = jQuery('#atlasPresEditSucursalSelect');
                    if (select.hasClass('select2-hidden-accessible')) select.select2('destroy');
                }
                document.getElementById('atlasPresEditCreditos').value = row.meta_creditos || 0;
                document.getElementById('atlasPresEditCash').value = row.meta_cash || 0;
                document.getElementById('atlasPresEditSub').textContent = `${this.detalle.presupuesto.nombre_mes} ${this.detalle.presupuesto.anio}`;
                this.modalEdit.show();
            },

            async importar(ev) {
                ev.preventDefault();
                const form = ev.currentTarget;
                const submitBtn = form.querySelector('button[type="submit"]');
                const anioImportacion = parseInt(document.getElementById('atlasPresImportAnio').value, 10);
                const mesImportacion = parseInt(document.getElementById('atlasPresImportMes').value, 10);
                if (!anioImportacion || !mesImportacion) {
                    showError('Selecciona un mes disponible para cargar.');
                    return;
                }
                if (this.mesImportacionYaCargado(anioImportacion, mesImportacion)) {
                    const confirmado = await this.confirmarRecargaPresupuesto(anioImportacion, mesImportacion);
                    if (!confirmado) return;
                }
                if (submitBtn) submitBtn.disabled = true;
                const fd = new FormData(form);
                let loaderImportacionActivo = false;
                if (typeof Swal !== 'undefined') {
                    loaderImportacionActivo = true;
                    Swal.fire({
                        title: 'Subiendo Excel',
                        text: 'No cierres ni actualices la pagina. Estamos validando y cargando el presupuesto mensual.',
                        imageUrl: '/assets/img/wait.svg',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false
                    });
                }
                http.request({
                    endpoint: '/Atlas/importarPresupuesto',
                    metodo: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    showLoader: false,
                    timeout: 120000,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            loaderImportacionActivo = false;
                            if (typeof Swal !== 'undefined') Swal.close();
                            showError(resp?.mensaje || 'No se pudo importar el presupuesto.');
                            return;
                        }
                        loaderImportacionActivo = false;
                        if (typeof Swal !== 'undefined') Swal.close();
                        const datosImportacion = resp.datos || {};
                        form.reset();
                        document.getElementById('atlasPresImportAnio').value = document.getElementById('atlasPresAnio').value;
                        this.calendariosPorAnio = {};
                        this.cerrarModalImportacion().then(() => {
                            if (typeof Swal !== 'undefined') {
                                const resultado = this.mostrarResultadoImportacion(datosImportacion);
                                if (resultado && typeof resultado.then === 'function') {
                                    resultado.then(() => this.cargar());
                                } else {
                                    this.cargar();
                                }
                            } else {
                                showSuccess(resp.mensaje || 'Presupuesto cargado correctamente.');
                                this.cargar();
                            }
                        });
                    },
                    onError: (mensaje) => {
                        loaderImportacionActivo = false;
                        if (typeof Swal !== 'undefined') Swal.close();
                        showError(mensaje || 'No se pudo importar el presupuesto.');
                    },
                    onAlways: () => {
                        if (submitBtn) submitBtn.disabled = false;
                        if (loaderImportacionActivo && typeof Swal !== 'undefined') {
                            Swal.close();
                        }
                    }
                });
            },

            confirmarRecargaPresupuesto(anio, mes) {
                const nombreMes = meses[(parseInt(mes, 10) || 1) - 1] || 'este mes';
                if (typeof Swal === 'undefined') {
                    return Promise.resolve(window.confirm(`Ya existe presupuesto para ${nombreMes} ${anio}. Se recargará y se guardará bitácora de cambios por sucursal. ¿Continuar?`));
                }
                return Swal.fire({
                    icon: 'warning',
                    title: 'Recargar presupuesto existente',
                    html: `Ya existe presupuesto para <strong>${this.escape(nombreMes)} ${this.escape(anio)}</strong>.<br>Se comparará contra el archivo anterior y se guardará bitácora con antes/después por sucursal.`,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, recargar',
                    cancelButtonText: 'Cancelar'
                }).then(r => !!r.isConfirmed);
            },

            cerrarModalImportacion() {
                const el = document.getElementById('modalAtlasPresupuestoImportar');
                if (!el || !el.classList.contains('show')) return Promise.resolve();
                return new Promise((resolve) => {
                    let done = false;
                    const finish = () => {
                        if (done) return;
                        done = true;
                        el.removeEventListener('hidden.bs.modal', finish);
                        resolve();
                    };
                    el.addEventListener('hidden.bs.modal', finish, { once: true });
                    const inst = (window.bootstrap && bootstrap.Modal.getInstance(el)) || this.modalImport;
                    if (inst && typeof inst.hide === 'function') inst.hide();
                    else finish();
                    setTimeout(finish, 700);
                });
            },

            mostrarResultadoImportacion(datos) {
                if (typeof Swal === 'undefined') {
                    showSuccess('Presupuesto cargado correctamente.');
                    return Promise.resolve();
                }
                const resumen = datos.resumen_importacion || {};
                const omitidos = Number(resumen.total_omitidos || 0);
                const erroresAsignacion = Number(resumen.errores_asignacion || 0);
                const observaciones = omitidos + erroresAsignacion;
                const icon = observaciones > 0 ? 'warning' : 'success';
                const esRecarga = Number(resumen.es_recarga || 0) === 1;
                const title = esRecarga
                    ? (observaciones > 0 ? 'Presupuesto recargado con observaciones' : 'Presupuesto recargado')
                    : (observaciones > 0 ? 'Presupuesto cargado con observaciones' : 'Presupuesto cargado completo');
                const detalle = [];
                if (Number(resumen.duplicados || 0) > 0) {
                    detalle.push(`${this.number(resumen.duplicados)} duplicado(s). Se tomó el último registro de cada sucursal.`);
                }
                if (Number(resumen.extras || 0) > 0) {
                    const muestra = (resumen.detalle_extras || []).map(x => `fila ${x.fila}: ${x.fk_sucursal}`).join(', ');
                    detalle.push(`${this.number(resumen.extras)} sucursal(es) extra no venían en el template y no se cargaron${muestra ? ` (${muestra})` : ''}.`);
                }
                if (Number(resumen.faltantes || 0) > 0) {
                    const muestra = (resumen.detalle_faltantes || []).join(', ');
                    detalle.push(`${this.number(resumen.faltantes)} sucursal(es) faltante(s) del template${muestra ? ` (${muestra})` : ''}.`);
                }
                if (Number(resumen.omitidos_invalidos || 0) > 0) {
                    detalle.push(`${this.number(resumen.omitidos_invalidos)} fila(s) omitida(s) por Pk_Sucursal inválido.`);
                }
                if (erroresAsignacion > 0) {
                    detalle.push(`${this.number(erroresAsignacion)} asesor(es) del Excel no se pudieron ligar contra persona. Revisa el detalle de errores.`);
                }
                const detalleHtml = this.htmlObservacionesImportacion(resumen);
                const tieneFaltantes = Number(resumen.faltantes || 0) > 0 && Array.isArray(resumen.detalle_faltantes) && resumen.detalle_faltantes.length > 0;
                if (detalleHtml) detalle.length = 0;
                return Swal.fire({
                    icon,
                    title: '',
                    html: `
                        <button type="button" class="atlas-pres-import-title" data-import-help="general">${this.escape(title)}</button>
                        <div class="atlas-pres-import-result">
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Esperadas</span><span class="atlas-pres-import-result-value">${this.number(resumen.sucursales_esperadas || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Leídas</span><span class="atlas-pres-import-result-value">${this.number(resumen.filas_leidas || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Cargadas</span><span class="atlas-pres-import-result-value">${this.number(resumen.registros_importados || datos.registros_importados || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Duplicadas</span><span class="atlas-pres-import-result-value">${this.number(resumen.duplicados || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Extras</span><span class="atlas-pres-import-result-value">${this.number(resumen.extras || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Faltantes</span><span class="atlas-pres-import-result-value">${this.number(resumen.faltantes || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Catalogo actualizado</span><span class="atlas-pres-import-result-value">${this.number(resumen.catalogo_clasificacion_actualizado || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Asesores actualizados</span><span class="atlas-pres-import-result-value">${this.number(resumen.catalogo_asesor_actualizado || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Asesores con error</span><span class="atlas-pres-import-result-value">${this.number(resumen.errores_asignacion || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Cambios bitácora</span><span class="atlas-pres-import-result-value">${this.number(resumen.cambios_registrados || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Altas</span><span class="atlas-pres-import-result-value">${this.number(resumen.altas_registradas || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Desactivadas</span><span class="atlas-pres-import-result-value">${this.number(resumen.desactivadas_registradas || 0)}</span></div>
                        </div>
                        ${detalleHtml}
                        ${tieneFaltantes ? `
                            <div class="atlas-pres-import-download">
                                <button type="button" class="btn btn-sm btn-label-success" data-atlas-pres-import-download-missing>
                                    <i class="fa-solid fa-file-excel me-1"></i>Descargar Excel de sucursales sin presupuesto
                                </button>
                            </div>
                        ` : ''}
                        ${detalle.length ? `<div class="atlas-pres-import-warnings">${detalle.map(x => `<div>${this.escape(x)}</div>`).join('')}</div>` : '<div class="text-success fw-bold">El archivo cuadró contra el template.</div>'}
                    `,
                    width: 760,
                    confirmButtonText: 'Entendido',
                    didOpen: () => {
                        if (detalleHtml) {
                            const successText = document.querySelector('.swal2-html-container .text-success');
                            if (successText) successText.remove();
                        }
                        const ayuda = ['esperadas', 'leidas', 'cargadas', 'duplicadas', 'extras', 'faltantes', 'catalogo', 'asesores_actualizados', 'asesores_error', 'cambios', 'altas', 'desactivadas'];
                        document.querySelectorAll('.atlas-pres-import-result-item').forEach((btn, idx) => {
                            btn.classList.add('atlas-pres-import-result-button');
                            btn.setAttribute('role', 'button');
                            btn.setAttribute('tabindex', '0');
                            btn.setAttribute('data-import-help', ayuda[idx] || 'general');
                        });
                        document.querySelectorAll('[data-import-help]').forEach(btn => {
                            btn.addEventListener('click', () => this.mostrarAyudaImportacion(btn.getAttribute('data-import-help')));
                        });
                        const downloadMissingBtn = document.querySelector('[data-atlas-pres-import-download-missing]');
                        if (downloadMissingBtn) {
                            downloadMissingBtn.addEventListener('click', () => this.descargarSucursalesSinPresupuestoExcel(datos));
                        }
                        this.descargarResumenImportacionPdf(datos);
                    }
                });
            },

            htmlObservacionesImportacion(resumen) {
                const bloques = [];
                if (Number(resumen.extras || 0) > 0) {
                    bloques.push(`
                        <div class="mb-2">
                            <div><strong>${this.number(resumen.extras)} sucursal(es) extra no venian en el template y no se cargaron.</strong></div>
                            <ul class="atlas-pres-import-warning-list">${(resumen.detalle_extras || []).map(x => `<li>${this.detalleSucursalImportacion(x, true)}</li>`).join('')}</ul>
                        </div>
                    `);
                }
                if (Number(resumen.faltantes || 0) > 0) {
                    bloques.push(`
                        <div class="mb-2">
                            <div><strong>${this.number(resumen.faltantes)} sucursal(es) faltante(s) del template.</strong></div>
                            <ul class="atlas-pres-import-warning-list">${(resumen.detalle_faltantes || []).map(x => `<li>${this.detalleSucursalImportacion(x, false)}</li>`).join('')}</ul>
                        </div>
                    `);
                }
                if (Number(resumen.duplicados || 0) > 0) {
                    bloques.push(`
                        <div class="mb-2">
                            <div><strong>${this.number(resumen.duplicados)} sucursal(es) duplicada(s). Se tomo el ultimo registro de cada FK.</strong></div>
                            <ul class="atlas-pres-import-warning-list">${(resumen.detalle_duplicados || []).map(x => `<li>${this.detalleSucursalImportacion(x, true)}</li>`).join('')}</ul>
                        </div>
                    `);
                }
                if (Number(resumen.omitidos_invalidos || 0) > 0) {
                    bloques.push(`<div><strong>${this.number(resumen.omitidos_invalidos)} fila(s) omitida(s) por Pk_Sucursal invalido.</strong></div>`);
                }
                if (Number(resumen.errores_asignacion || 0) > 0) {
                    bloques.push(`
                        <div class="mb-2">
                            <div><strong>${this.number(resumen.errores_asignacion)} asesor(es) no se pudieron ligar contra persona.</strong></div>
                            <ul class="atlas-pres-import-warning-list">${(resumen.detalle_errores_asignacion || []).map(x => `<li>${this.detalleErrorAsignacionImportacion(x)}</li>`).join('')}</ul>
                        </div>
                    `);
                }
                return bloques.length ? `<div class="atlas-pres-import-warnings">${bloques.join('')}</div>` : '';
            },

            detalleSucursalImportacion(item, mostrarFila) {
                if (!item || typeof item !== 'object') item = { fk_sucursal: item };
                const fila = mostrarFila && item.fila ? `<strong>Fila ${this.escape(item.fila)}</strong> - ` : '';
                const fk = item.fk_sucursal || 'Sin FK';
                const sucursal = item.sucursal || 'Sin nombre de sucursal';
                const distribuidor = item.distribuidor ? ` - ${this.escape(item.distribuidor)}` : '';
                return `${fila}FK <strong>${this.escape(fk)}</strong> - ${this.escape(sucursal)}${distribuidor}`;
            },

            detalleErrorAsignacionImportacion(item) {
                if (!item || typeof item !== 'object') item = {};
                const fila = item.fila ? `<strong>Fila ${this.escape(item.fila)}</strong> - ` : '';
                const fk = item.fk_sucursal || 'Sin FK';
                const sucursal = item.sucursal || 'Sin nombre de sucursal';
                const asesor = item.asesor_excel || 'Sin asesor';
                const falla = item.falla || 'No se pudo ligar contra persona.';
                return `${fila}FK <strong>${this.escape(fk)}</strong> - ${this.escape(sucursal)} · Asesor Excel: <strong>${this.escape(asesor)}</strong> · ${this.escape(falla)}`;
            },

            mostrarAyudaImportacion(tipo) {
                const textos = {
                    general: 'El presupuesto se cargo, pero el archivo no coincide al 100% con el template esperado. Revisa las observaciones y el PDF descargado.',
                    esperadas: 'Sucursales que el sistema esperaba recibir porque existen en el template oficial de ATLAS.',
                    leidas: 'Filas validas leidas del Excel con Pk_Sucursal numerico.',
                    cargadas: 'Sucursales que si se guardaron en el presupuesto mensual.',
                    duplicadas: 'FK repetidos dentro del Excel. El sistema conserva el ultimo registro encontrado para esa sucursal.',
                    extras: 'Sucursales que venian en el Excel, pero no existen en el template esperado. No se cargan.',
                    faltantes: 'Sucursales del template oficial que no llegaron en el Excel. Deben revisarse porque quedaron sin cargar.',
                    catalogo: 'Clasificaciones del catalogo de sucursales que fueron actualizadas para empatar con el Excel.',
                    asesores_actualizados: 'Sucursales cuyo asesor se actualizo automaticamente en el catalogo a partir del nombre recibido en el Excel.',
                    asesores_error: 'Asesores que venian en el Excel pero no pudieron ligarse contra la tabla persona. El presupuesto se carga, pero esa sucursal conserva su asignacion operativa anterior.',
                    cambios: 'Sucursales que ya existian en el presupuesto y cambiaron meta, cash o datos operativos durante la recarga.',
                    altas: 'Sucursales que no estaban activas en el presupuesto anterior y entraron con la recarga.',
                    desactivadas: 'Sucursales que estaban activas antes, pero ya no vinieron en el Excel de recarga.'
                };
                Swal.fire({
                    icon: 'info',
                    title: 'Que significa',
                    text: textos[tipo] || textos.general,
                    confirmButtonText: 'Entendido'
                });
            },

            descargarResumenImportacionPdf(datos) {
                if (!datos || !datos.pdf_url) return;
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = datos.pdf_url;
                document.body.appendChild(iframe);
                setTimeout(() => iframe.remove(), 15000);
            },

            descargarSucursalesSinPresupuestoExcel(datos) {
                const resumen = datos?.resumen_importacion || {};
                const faltantes = Array.isArray(resumen.detalle_faltantes) ? resumen.detalle_faltantes : [];
                if (!faltantes.length) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'info', title: 'Sin sucursales', text: 'No hay sucursales sin presupuesto para descargar.' });
                    }
                    return;
                }

                const anio = parseInt(datos?.anio || this.anio || new Date().getFullYear(), 10) || new Date().getFullYear();
                const mes = parseInt(datos?.mes || document.getElementById('atlasPresImportMes')?.value || 1, 10) || 1;
                const mesNombre = this.nombreMes(mes);
                const rows = faltantes.map((item, index) => {
                    const record = item && typeof item === 'object' ? item : { fk_sucursal: item };
                    return `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${this.escape(record.fk_sucursal || '')}</td>
                            <td>${this.escape(record.sucursal || 'Sin nombre de sucursal')}</td>
                            <td>${this.escape(record.distribuidor || '')}</td>
                        </tr>
                    `;
                }).join('');
                const html = `
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <style>
                            table { border-collapse:collapse; font-family:Arial, sans-serif; font-size:12px; }
                            th { background:#26344e; color:#ffffff; font-weight:bold; }
                            th, td { border:1px solid #d9dee3; padding:6px 8px; mso-number-format:'\\@'; }
                            .title { font-size:18px; font-weight:bold; color:#22303e; }
                            .subtitle { color:#64748b; font-weight:bold; }
                        </style>
                    </head>
                    <body>
                        <table>
                            <tr><td colspan="4" class="title">Sucursales sin presupuesto</td></tr>
                            <tr><td colspan="4" class="subtitle">Presupuesto base: ${this.escape(mesNombre)} ${anio}</td></tr>
                            <tr><td colspan="4"></td></tr>
                            <tr>
                                <th>#</th>
                                <th>FK sucursal</th>
                                <th>Sucursal</th>
                                <th>Distribuidor</th>
                            </tr>
                            ${rows}
                        </table>
                    </body>
                    </html>
                `;
                const blob = new Blob(['\ufeff', html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `sucursales_sin_presupuesto_${anio}_${String(mes).padStart(2, '0')}.xls`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
            },

            guardarDetalle(ev) {
                ev.preventDefault();
                const data = Object.fromEntries(new FormData(ev.currentTarget).entries());
                const presupuestoId = parseInt(data.presupuesto_id || this.detalle?.presupuesto?.id, 10);
                http.request({
                    endpoint: '/Atlas/guardarPresupuestoSucursal',
                    metodo: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json; charset=UTF-8',
                    processData: false,
                    showLoader: true,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            showError(resp?.mensaje || 'No se pudo actualizar la meta.');
                            return;
                        }
                        this.modalEdit.hide();
                        if (resp.mensaje) showSuccess(resp.mensaje);
                        this.cargarDetalle(presupuestoId);
                        this.cargar();
                    }
                });
            },

            eliminar(id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Borrar presupuesto futuro',
                    text: 'Solo se permite borrar meses que aun no llegan.',
                    showCancelButton: true,
                    confirmButtonText: 'Borrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33'
                }).then(res => {
                    if (!res.isConfirmed) return;
                    http.request({
                        endpoint: '/Atlas/eliminarPresupuestoMes',
                        metodo: 'POST',
                        data: JSON.stringify({ id }),
                        contentType: 'application/json; charset=UTF-8',
                        processData: false,
                        showLoader: true,
                        onSuccess: (resp) => {
                            if (!resp || resp.success === false) {
                                showError(resp?.mensaje || 'No se pudo borrar.');
                                return;
                            }
                            this.detalle = null;
                            document.getElementById('atlasPresDetalleCard').style.display = 'none';
                            document.getElementById('atlasPresListadoView').style.display = '';
                            this.cargar();
                        }
                    });
                });
            },

            async descargarTemplate() {
                const anio = document.getElementById('atlasPresAnio').value || this.anio;
                const mes = document.getElementById('atlasPresImportMes').value || (new Date().getMonth() + 1);
                const url = `/Atlas/descargarTemplatePresupuesto?anio=${encodeURIComponent(anio)}&mes=${encodeURIComponent(mes)}`;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Procesando tu solicitud',
                        text: 'Estamos descargando el template, espera un momento...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });
                }

                try {
                    const resp = await fetch(url, { credentials: 'same-origin' });
                    if (!resp.ok) {
                        const errorText = await resp.text();
                        throw new Error(this.limpiarMensajeDescarga(errorText) || 'No se pudo descargar el template.');
                    }
                    const contentType = resp.headers.get('Content-Type') || '';
                    if (!contentType.includes('spreadsheetml')) {
                        throw new Error('No se recibió un archivo Excel. Revisa que tu sesión siga activa.');
                    }
                    const blob = await resp.blob();
                    const cd = resp.headers.get('Content-Disposition') || '';
                    const match = cd.match(/filename="?([^"]+)"?/i);
                    const filename = match ? match[1] : `template_presupuesto_${anio}_${String(mes).padStart(2, '0')}.xlsx`;
                    const objectUrl = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = objectUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(objectUrl);
                    if (typeof Swal !== 'undefined') Swal.close();
                } catch (err) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'No se pudo descargar', text: err.message || 'Intenta de nuevo.' });
                    }
                }
            },

            limpiarMensajeDescarga(texto) {
                return String(texto || '')
                    .replace(/<[^>]*>/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim()
                    .slice(0, 240);
            },

            money(value) {
                return new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(Number(value || 0));
            },
            number(value) {
                return new Intl.NumberFormat('es-MX', { maximumFractionDigits: 0 }).format(Number(value || 0));
            },
            percent(value) {
                return `${new Intl.NumberFormat('es-MX', { maximumFractionDigits: 1 }).format(Number(value || 0))}%`;
            },
            escape(value) {
                return String(value ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
            }
        };

        document.addEventListener('DOMContentLoaded', () => Pres.init());
    })();
    </script>
</div>
