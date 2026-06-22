<?php
/** @var string|null $titulo */
$atlasSucAsigEmbedded = !empty($atlas_suc_asig_embedded);
$atlasSucAsigTitulo = $atlas_suc_asig_titulo ?? ($titulo ?? 'Seguimiento');
$atlasSucAsigRootClass = $atlasSucAsigEmbedded ? 'atlas-suc-asig-page' : 'container-xxl flex-grow-1 container-p-y atlas-suc-asig-page';
?>
<div class="<?= $atlasSucAsigRootClass ?>">
    <style>
        .atlas-suc-asig-page { color:#22303e; }
        .atlas-suc-asig-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:800; margin:0; }
        .atlas-suc-asig-title i { color:#26344e; }
        .atlas-suc-asig-subtitle { color:#6b7280; font-size:.88rem; font-weight:600; margin:.2rem 0 0; }
        .atlas-suc-asig-actions { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
        .btn-action-size { min-height:2.375rem; padding:.5rem .95rem; display:inline-flex; align-items:center; justify-content:center; gap:.35rem; font-size:.875rem; font-weight:600; }
        .atlas-suc-asig-main { color:#22303e; font-weight:800; line-height:1.16; }
        .atlas-suc-asig-sub { color:#7a838b; font-size:.75rem; font-weight:700; line-height:1.18; margin-top:.14rem; }
        .atlas-suc-asig-muted { color:#94a3b8; font-weight:700; font-size:.78rem; }
        .atlas-suc-asig-branch-head { display:flex; flex-direction:column; align-items:flex-start; gap:.28rem; min-width:0; }
        .atlas-suc-asig-branch-meta { display:flex; align-items:center; gap:.45rem; }
        .atlas-suc-asig-moto { width:2.25rem; height:2.25rem; border:0; border-radius:999px; color:#fff; display:inline-grid; place-items:center; flex:0 0 2.25rem; box-shadow:0 6px 14px rgba(23,55,86,.16); cursor:pointer; transition:transform .15s ease, box-shadow .15s ease; }
        .atlas-suc-asig-moto:hover { transform:translateY(-1px); box-shadow:0 8px 18px rgba(23,55,86,.2); }
        .atlas-suc-asig-moto--ok,
        .atlas-suc-asig-moto--missing { background:linear-gradient(135deg,#173756,#2563eb); }
        .atlas-suc-asig-moto i { font-size:1rem; line-height:1; }
        .atlas-suc-asig-fk { display:inline-flex; align-items:center; gap:.25rem; border-radius:999px; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; padding:.14rem .5rem; font-size:.7rem; font-weight:900; white-space:nowrap; }
        .atlas-suc-asig-empty { text-align:center; color:#9ca3af; font-weight:700; padding:2.2rem !important; }
        .atlas-suc-asig-badge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.22rem .62rem; font-size:.72rem; font-weight:800; white-space:nowrap; }
        .atlas-suc-asig-badge-ok { background:#dcfce7; color:#15803d; }
        .atlas-suc-asig-badge-warn { background:#fef3c7; color:#b45309; }
        .atlas-suc-asig-badge-info { background:#dbeafe; color:#1d4ed8; }
        .atlas-suc-asig-badge-danger { background:#fee2e2; color:#b91c1c; }
        .atlas-suc-asig-pipeline { display:flex; flex-direction:column; align-items:stretch; gap:.3rem; min-width:16rem; }
        .atlas-suc-asig-pipeline-row { display:flex; align-items:center; justify-content:space-between; gap:.6rem; }
        .atlas-suc-asig-pipeline-label { color:#22303e; font-size:.76rem; font-weight:700; line-height:1.18; }
        .atlas-suc-asig-pipeline-row .atlas-suc-asig-badge { min-width:2.1rem; justify-content:center; padding:.18rem .5rem; }
        .atlas-suc-pipeline-money { min-width:17rem; display:grid; gap:.42rem; margin-bottom:.55rem; padding-bottom:.55rem; border-bottom:1px solid #e5e7eb; }
        .atlas-suc-pipeline-money-title { display:flex; align-items:center; justify-content:space-between; gap:.7rem; color:#173756; font-size:.76rem; font-weight:900; }
        .atlas-suc-pipeline-money-title strong { font-size:.9rem; }
        .atlas-suc-pipeline-money-row { display:grid; gap:.18rem; }
        .atlas-suc-pipeline-money-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.02em; }
        .atlas-suc-pipeline-money-value { color:#22303e; font-size:.78rem; font-weight:900; white-space:nowrap; }
        .atlas-suc-pipeline-bar { height:6px; border-radius:999px; background:#e5e7eb; overflow:hidden; }
        .atlas-suc-pipeline-bar-fill { height:100%; border-radius:999px; min-width:0; }
        .atlas-suc-pipeline-bar-fill--meta { background:linear-gradient(90deg,#173756,#2563eb); }
        .atlas-suc-pipeline-bar-fill--detenido { background:linear-gradient(90deg,#d97706,#f59e0b); }
        .atlas-suc-pipeline-bar-fill--avanzado { background:linear-gradient(90deg,#15803d,#22c55e); }
        .atlas-suc-asig-cash { min-width:11rem; }
        .atlas-suc-asig-cash-row { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; padding:.18rem 0; }
        .atlas-suc-asig-cash-row + .atlas-suc-asig-cash-row { border-top:1px solid #e5e7eb; margin-top:.24rem; padding-top:.42rem; }
        .atlas-suc-asig-cash-label { color:#64748b; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.02em; }
        .atlas-suc-asig-cash-value { color:#22303e; font-size:.85rem; font-weight:900; text-align:right; white-space:nowrap; }
        .atlas-suc-asig-progress { height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden; margin-top:.45rem; }
        .atlas-suc-asig-progress-fill { height:100%; background:linear-gradient(90deg,#16a34a,#22c55e); border-radius:999px; }
        .atlas-suc-asig-progress-text { color:#15803d; font-size:.72rem; font-weight:900; margin-top:.22rem; text-align:right; }
        .atlas-suc-asig-priority { display:grid; grid-template-columns:repeat(3, max-content); gap:.28rem; align-items:center; }
        .atlas-suc-asig-priority span { font-size:.72rem; font-weight:800; white-space:nowrap; }
        .atlas-suc-asig-priority .alta { color:#b91c1c; }
        .atlas-suc-asig-priority .media { color:#b45309; }
        .atlas-suc-asig-priority .baja { color:#15803d; }
        .atlas-suc-asig-row-actions { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; }
        .atlas-suc-asig-row-actions .btn { width:2rem; height:2rem; padding:0; display:inline-flex; align-items:center; justify-content:center; }
        .atlas-suc-asig-mini { border:1px solid #e5e7eb; border-radius:.6rem; background:#fff; padding:.75rem; margin-bottom:.55rem; }
        .atlas-suc-asig-mini-title { color:#22303e; font-size:.88rem; font-weight:800; line-height:1.2; }
        .atlas-suc-asig-mini-meta { color:#697a8d; font-size:.76rem; font-weight:600; line-height:1.28; margin-top:.2rem; }
        .atlas-suc-asig-section-title { color:#566a7f; font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.02em; margin:1rem 0 .55rem; }
        .atlas-suc-asig-muted-box { border:1px dashed #dbe4ef; border-radius:.65rem; color:#6b7280; background:#f8fafc; padding:.9rem; font-size:.82rem; font-weight:700; }
        .atlas-suc-asig-meta { border:1px solid #bfdbfe; background:#eff6ff; color:#1e3a8a; border-radius:.65rem; padding:.75rem .9rem; font-size:.82rem; font-weight:700; margin-bottom:1rem; }
        .atlas-suc-asig-meta strong { color:#172554; }
        .atlas-suc-asig-tabs { border-bottom:1px solid #e2e8f0; gap:.35rem; flex-wrap:wrap; }
        .atlas-suc-asig-tabs .nav-link { border:0; border-bottom:3px solid transparent; color:#64748b; font-weight:800; padding:.75rem 1rem; border-radius:0; background:transparent; }
        .atlas-suc-asig-tabs .nav-link.active { color:#173756; border-bottom-color:#d09f48; background:transparent; }
        #modalAtlasSucursalAsignada .modal-content { border:0; border-radius:.875rem; overflow:hidden; box-shadow:var(--bs-box-shadow-lg); }
        #modalAtlasSucursalAsignada .modal-header { border-bottom:1px solid #e5e7eb; padding:1rem 1.25rem; }
        #modalAtlasSucursalAsignada .modal-body { padding:1.25rem; }
        #modalAtlasSucursalAsignada .modal-footer { border-top:1px solid #e5e7eb; padding:1rem 1.25rem; gap:.75rem; }
        #modalAtlasSucursalMapa .modal-content { border:0; border-radius:.875rem; overflow:hidden; box-shadow:var(--bs-box-shadow-lg); }
        #modalAtlasSucursalMapa .modal-header { border-bottom:1px solid #e5e7eb; padding:1rem 1.25rem; }
        #modalAtlasSucursalMapa .modal-body { padding:1.25rem; }
        #modalAtlasSucursalMapa .modal-footer { border-top:1px solid #e5e7eb; padding:1rem 1.25rem; }
        .atlas-suc-map-frame { width:100%; min-height:24rem; border:0; border-radius:.65rem; background:#f8fafc; }
        .atlas-suc-map-meta { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .atlas-suc-map-chip { border:1px solid #e5e7eb; border-radius:.65rem; background:#f8fafc; padding:.7rem .8rem; min-width:0; }
        .atlas-suc-map-chip-label { display:block; color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.02em; margin-bottom:.18rem; }
        .atlas-suc-map-chip-value { display:block; color:#22303e; font-size:.84rem; font-weight:800; line-height:1.2; overflow-wrap:anywhere; }
        .atlas-suc-kpis { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:.55rem; margin:.85rem 0 .9rem; }
        .atlas-suc-kpi { border:1px solid #e2e8f0; border-radius:.55rem; background:#fff; padding:.68rem .65rem; min-height:4.2rem; min-width:0; text-align:left; }
        .atlas-suc-kpi span { display:flex; align-items:center; gap:.32rem; color:#64748b; font-size:.62rem; font-weight:900; text-transform:uppercase; letter-spacing:.02em; line-height:1.1; }
        .atlas-suc-kpi strong { display:block; margin-top:.28rem; color:#173756; font-size:1rem; font-weight:900; line-height:1.05; }
        .atlas-suc-kpi small { display:block; margin-top:.18rem; color:#64748b; font-size:.68rem; font-weight:800; line-height:1.15; }
        .atlas-suc-kpi-warn { border-color:#fde68a; background:#fffbeb; cursor:pointer; transition:transform .15s ease, box-shadow .15s ease; }
        .atlas-suc-kpi-warn span,
        .atlas-suc-kpi-warn strong { color:#92400e; }
        .atlas-suc-kpi-warn:hover { transform:translateY(-1px); box-shadow:0 .35rem .8rem rgba(146,64,14,.1); }
        .atlas-suc-filter-panel { border:1px solid #e2e8f0; border-radius:.65rem; background:#fff; padding:.85rem; margin:.75rem 0 1rem; }
        .atlas-suc-filter-title { color:#566a7f; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.02em; margin-bottom:.55rem; display:flex; align-items:center; gap:.4rem; }
        .atlas-suc-filter-grid { display:grid; grid-template-columns:2fr repeat(4, minmax(9.5rem, 1fr)); gap:.65rem; align-items:end; }
        .atlas-suc-filter-grid .form-label { color:#566a7f; font-size:.72rem; font-weight:800; margin-bottom:.22rem; }
        .atlas-suc-filter-grid .form-control,
        .atlas-suc-filter-grid .form-select { min-height:2.25rem; font-size:.82rem; }
        .atlas-suc-filter-range { display:grid; grid-template-columns:1fr 1fr; gap:.35rem; }
        .atlas-suc-filter-actions { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-top:.75rem; }
        .atlas-suc-filter-count { color:#64748b; font-size:.78rem; font-weight:800; }
        .atlas-suc-budget-list { display:grid; gap:.55rem; }
        .atlas-suc-budget-item { border:1px solid #e5e7eb; border-radius:.65rem; background:#fff; padding:.72rem .82rem; display:grid; grid-template-columns:minmax(0,1fr) auto; gap:.75rem; align-items:center; }
        .atlas-suc-budget-title { color:#22303e; font-size:.88rem; font-weight:900; line-height:1.15; }
        .atlas-suc-budget-meta { color:#64748b; font-size:.74rem; font-weight:700; line-height:1.2; margin-top:.18rem; }
        .atlas-suc-asig-page .dataTables_paginate .pagination,
        .atlas-suc-asig-page .dt-paging .pagination { flex-wrap:wrap; gap:.25rem; }
        .atlas-suc-asig-page .dataTables_paginate .page-link,
        .atlas-suc-asig-page .dt-paging .page-link { min-width:2rem; text-align:center; }
        @media (max-width: 767.98px) {
            .atlas-suc-asig-actions,
            .atlas-suc-asig-actions { align-items:stretch; flex-direction:column; }
            .atlas-suc-asig-actions .btn { width:100%; min-width:0; }
            .atlas-suc-asig-priority { grid-template-columns:1fr; }
            .atlas-suc-asig-tabs { flex-wrap:nowrap; overflow-x:auto; }
            .atlas-suc-asig-tabs .nav-item { flex:0 0 auto; }
            .atlas-suc-map-meta { grid-template-columns:1fr; }
            .atlas-suc-map-frame { min-height:18rem; }
            .atlas-suc-kpis { grid-template-columns:1fr; }
            .atlas-suc-filter-grid { grid-template-columns:1fr; }
            .atlas-suc-filter-range { grid-template-columns:1fr; }
            .atlas-suc-filter-actions { align-items:stretch; flex-direction:column; }
            .atlas-suc-filter-actions .btn { width:100%; }
        }
    </style>

    <?php if (!$atlasSucAsigEmbedded): ?>
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h1 class="atlas-suc-asig-title"><i class="fa-solid fa-chart-simple"></i><span><?= htmlspecialchars($atlasSucAsigTitulo, ENT_QUOTES, 'UTF-8') ?></span></h1>
            <p class="atlas-suc-asig-subtitle">Análisis por sucursal: pendientes operativos, cash detenido y cash que ya avanzó.</p>
        </div>
        <div class="atlas-suc-asig-actions">
            <button type="button" class="btn btn-label-secondary btn-action-size" data-atlas-suc-refresh>
                <i class="fa-solid fa-rotate icon-sm me-sm-2"></i><span>Actualizar</span>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$atlasSucAsigEmbedded): ?>
    <div class="card">
        <div class="card-body">
    <?php endif; ?>
            <?php if (!$atlasSucAsigEmbedded): ?>
            <ul class="nav atlas-suc-asig-tabs mb-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" type="button" data-atlas-suc-tab="asignadas">
                        <i class="fa-solid fa-chart-line me-1"></i><?= htmlspecialchars($atlasSucAsigTitulo, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </li>
            </ul>
            <?php endif; ?>
            <div class="atlas-suc-kpis" id="atlasSucKpis" style="display:none;"></div>
            <div class="atlas-suc-filter-panel" id="atlasSucFilterPanel">
                <div class="atlas-suc-filter-title"><i class="fa-solid fa-filter"></i>Filtros de avance</div>
                <div class="atlas-suc-filter-grid">
                    <div>
                        <label class="form-label">Buscar</label>
                        <input type="search" class="form-control" data-atlas-suc-filter="q" placeholder="Sucursal, FK, dirección, gestor, división...">
                    </div>
                    <div>
                        <label class="form-label">División</label>
                        <select class="form-select" data-atlas-suc-filter="division"><option value="">Todas</option></select>
                    </div>
                    <div>
                        <label class="form-label">Regional</label>
                        <select class="form-select" data-atlas-suc-filter="regional"><option value="">Todas</option></select>
                    </div>
                    <div>
                        <label class="form-label">Clasificación</label>
                        <select class="form-select" data-atlas-suc-filter="clasificacion"><option value="">Todas</option></select>
                    </div>
                    <div>
                        <label class="form-label">Presupuesto</label>
                        <select class="form-select" data-atlas-suc-filter="presupuesto">
                            <option value="">Todos</option>
                            <option value="con">Con presupuesto</option>
                            <option value="sin">Sin presupuesto</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Gestor</label>
                        <select class="form-select" data-atlas-suc-filter="gestor">
                            <option value="">Todos</option>
                            <option value="con">Con gestor</option>
                            <option value="sin">Sin gestor</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Pipeline</label>
                        <select class="form-select" data-atlas-suc-filter="bucket"><option value="">Todos</option></select>
                    </div>
                    <div>
                        <label class="form-label">Cash detenido</label>
                        <div class="atlas-suc-filter-range">
                            <input type="number" class="form-control" data-atlas-suc-filter="detenido_min" placeholder="Min">
                            <input type="number" class="form-control" data-atlas-suc-filter="detenido_max" placeholder="Max">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Cash avanzado</label>
                        <div class="atlas-suc-filter-range">
                            <input type="number" class="form-control" data-atlas-suc-filter="avanzado_min" placeholder="Min">
                            <input type="number" class="form-control" data-atlas-suc-filter="avanzado_max" placeholder="Max">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">% avance</label>
                        <div class="atlas-suc-filter-range">
                            <input type="number" class="form-control" data-atlas-suc-filter="avance_min" placeholder="Min">
                            <input type="number" class="form-control" data-atlas-suc-filter="avance_max" placeholder="Max">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Pendientes</label>
                        <div class="atlas-suc-filter-range">
                            <input type="number" class="form-control" data-atlas-suc-filter="pendientes_min" placeholder="Min">
                            <input type="number" class="form-control" data-atlas-suc-filter="pendientes_max" placeholder="Max">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Ordenar por</label>
                        <select class="form-select" data-atlas-suc-filter="orden">
                            <option value="detenido_desc">Mayor cash detenido</option>
                            <option value="detenido_asc">Menor cash detenido</option>
                            <option value="avanzado_desc">Mayor cash avanzado</option>
                            <option value="avanzado_asc">Menor cash avanzado</option>
                            <option value="avance_desc">Mayor % avance</option>
                            <option value="avance_asc">Menor % avance</option>
                            <option value="pendientes_desc">Mayor pendientes</option>
                            <option value="pendientes_asc">Menor pendientes</option>
                            <option value="meta_cash_desc">Mayor meta cash</option>
                            <option value="meta_cash_asc">Menor meta cash</option>
                            <option value="sucursal_asc">Sucursal A-Z</option>
                            <option value="sucursal_desc">Sucursal Z-A</option>
                            <option value="fk_asc">FK menor a mayor</option>
                            <option value="fk_desc">FK mayor a menor</option>
                        </select>
                    </div>
                </div>
                <div class="atlas-suc-filter-actions">
                    <div class="atlas-suc-filter-count" id="atlasSucFilterCount">Mostrando 0 sucursales.</div>
                    <button type="button" class="btn btn-label-secondary btn-sm" data-atlas-suc-clear-filters>
                        <i class="fa-solid fa-eraser me-1"></i>Limpiar filtros
                    </button>
                </div>
            </div>
            <div class="card-datatable table-responsive">
                <table class="dt-responsive table border-top" id="atlasSucursalesAsignadasTabla" data-atlas-suc-panel="asignadas">
                    <thead>
                        <tr>
                            <th>Sucursal</th>
                            <th>Gestores</th>
                            <th>Pipeline</th>
                            <th>Cash</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="atlasSucursalesAsignadasBody">
                        <tr><td class="atlas-suc-asig-empty" colspan="5">Cargando seguimiento...</td></tr>
                    </tbody>
                </table>
            </div>
    <?php if (!$atlasSucAsigEmbedded): ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="modalAtlasSucursalAsignada" tabindex="-1" aria-labelledby="atlasSucModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="atlasSucModalTitle"><i class="fa-solid fa-store me-2"></i>Sucursal</h5>
                        <div class="text-muted small fw-semibold" id="atlasSucModalSub">Detalle operativo</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="atlasSucDetalleContenido" class="atlas-suc-asig-muted-box">Selecciona una sucursal.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasSucursalMapa" tabindex="-1" aria-labelledby="atlasSucMapaTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="atlasSucMapaTitle"><i class="fa-solid fa-map-location-dot me-2"></i>Sucursal</h5>
                        <div class="text-muted small fw-semibold" id="atlasSucMapaSub">Ubicación de sucursal</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="atlasSucMapaContenido">
                    <div class="atlas-suc-asig-muted-box">Selecciona una sucursal.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasSucSinPresupuesto" tabindex="-1" aria-labelledby="atlasSucSinPresTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="atlasSucSinPresTitle"><i class="fa-solid fa-triangle-exclamation me-2"></i>Sucursales sin presupuesto</h5>
                        <div class="text-muted small fw-semibold" id="atlasSucSinPresSub">Sucursales activas sin meta mensual cargada.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="atlas-suc-budget-list" id="atlasSucSinPresLista"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (() => {
        const AtlasSucursalesAsignadas = {
            datos: [],
            filtrados: [],
            meta: {},
            modal: null,
            tab: 'asignadas',
            tablas: {},
            filtros: {
                q: '',
                division: '',
                regional: '',
                clasificacion: '',
                presupuesto: '',
                gestor: '',
                bucket: '',
                detenido_min: '',
                detenido_max: '',
                avanzado_min: '',
                avanzado_max: '',
                avance_min: '',
                avance_max: '',
                pendientes_min: '',
                pendientes_max: '',
                orden: 'detenido_desc'
            },

            init() {
                this.bind();
                this.cargar();
            },

            bind() {
                document.querySelector('[data-atlas-suc-refresh]')?.addEventListener('click', () => this.cargar());
                document.querySelectorAll('[data-atlas-suc-tab]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        this.tab = btn.getAttribute('data-atlas-suc-tab') || 'asignadas';
                        this.renderTabs();
                    });
                });
                document.getElementById('atlasSucursalesAsignadasBody')?.addEventListener('click', ev => {
                    const mapBtn = ev.target.closest('[data-atlas-suc-map]');
                    if (mapBtn) {
                        this.verMapa(parseInt(mapBtn.getAttribute('data-atlas-suc-map'), 10));
                        return;
                    }
                    const btn = ev.target.closest('[data-atlas-suc-detalle]');
                    if (btn) this.verDetalle(btn.getAttribute('data-atlas-suc-detalle'));
                });
                document.getElementById('atlasSucKpis')?.addEventListener('click', ev => {
                    if (ev.target.closest('[data-atlas-suc-sin-presupuesto]')) this.verSinPresupuesto();
                });
                document.querySelectorAll('[data-atlas-suc-filter]').forEach(input => {
                    input.addEventListener('input', () => this.cambiarFiltro(input));
                    input.addEventListener('change', () => this.cambiarFiltro(input));
                });
                document.querySelector('[data-atlas-suc-clear-filters]')?.addEventListener('click', () => this.limpiarFiltros());
            },

            async cargar() {
                this.wait(true);
                try {
                    const res = await this.getJson('/Atlas/getSucursalesAsignadas');
                    if (!res.success) {
                        this.toast(res.mensaje || 'No se pudo cargar seguimiento.', 'error');
                        this.datos = [];
                    } else {
                        this.datos = Array.isArray(res.datos) ? res.datos : [];
                        this.meta = res.meta || {};
                        this.cargarOpcionesFiltros();
                    }
                    this.render();
                } finally {
                    this.wait(false);
                }
            },

            renderTabs() {
                document.querySelectorAll('[data-atlas-suc-tab]').forEach(btn => {
                    btn.classList.toggle('active', btn.getAttribute('data-atlas-suc-tab') === this.tab);
                });
                document.querySelectorAll('[data-atlas-suc-panel]').forEach(panel => {
                    const visible = panel.getAttribute('data-atlas-suc-panel') === this.tab;
                    panel.classList.toggle('d-none', !visible);
                    const wrapper = panel.id ? document.getElementById(panel.id + '_wrapper') : null;
                    if (wrapper) wrapper.classList.toggle('d-none', !visible);
                });
            },

            render() {
                this.filtrados = this.aplicarFiltros(this.datos);
                this.renderKpis();
                this.renderFiltroConteo();
                const body = document.getElementById('atlasSucursalesAsignadasBody');
                if (!body) return;
                this.destroyTable('asignadas');
                if (!this.filtrados.length) {
                    body.innerHTML = '<tr><td class="atlas-suc-asig-empty" colspan="5">No hay sucursales que coincidan con esos filtros.</td></tr>';
                    this.renderTabs();
                    return;
                }
                body.innerHTML = this.filtrados.map((r, idx) => {
                    const tieneMapa = this.tieneCoordenadas(r);
                    return `
                    <tr>
                        <td>
                            <div class="atlas-suc-asig-branch-head">
                                <span class="atlas-suc-asig-branch-meta">
                                    <button type="button"
                                            class="atlas-suc-asig-moto ${tieneMapa ? 'atlas-suc-asig-moto--ok' : 'atlas-suc-asig-moto--missing'}"
                                            data-atlas-suc-map="${idx}"
                                            title="${tieneMapa ? 'Ver mapa' : 'Sucursal sin coordenadas'}"
                                            aria-label="${tieneMapa ? 'Ver mapa de sucursal' : 'Sucursal sin coordenadas'}">
                                        <i class="fa-solid fa-motorcycle"></i>
                                    </button>
                                    <span class="atlas-suc-asig-fk">FK ${this.escape(r.fk_sucursal)}</span>
                                </span>
                                <span class="atlas-suc-asig-main">${this.escape(r.sucursal || 'Sucursal sin nombre')}</span>
                                ${Number(r.es_nuevo_ingreso || 0) === 1 ? '<span class="atlas-suc-asig-badge atlas-suc-asig-badge-info" title="Fecha de registro: ' + this.escape(r.fecha_alta_fmt || '') + '"><i class="fa-solid fa-star"></i>Sucursal de nuevo ingreso</span>' : ''}
                            </div>
                            <div class="atlas-suc-asig-sub">${this.escape(r.direccion || 'Sin dirección')}</div>
                            <div class="atlas-suc-asig-sub">${this.escape(r.numero_telefono || 'Sin teléfono')} · ${this.escape(r.division_nombre || 'Sin división')} / ${this.escape(r.regional_nombre || 'Sin regional')}</div>
                        </td>
                        <td>
                            <div class="atlas-suc-asig-main">${this.escape(r.gestores_asignados || 'Sin gestor')}</div>
                            <div class="atlas-suc-asig-sub">${Number(r.total_gestores || 0)} gestor(es)</div>
                        </td>
                        <td>
                            ${this.pipelineResumen(r)}
                        </td>
                        <td>
                            ${this.cashResumen(r)}
                        </td>
                        <td class="text-center">
                            <span class="atlas-suc-asig-row-actions">
                                <button class="btn btn-sm btn-primary" type="button" data-atlas-suc-detalle="${this.escape(r.fk_sucursal)}" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>
                            </span>
                        </td>
                    </tr>`;
                }).join('');
                this.initTable('asignadas', '#atlasSucursalesAsignadasTabla');
                this.renderTabs();
            },

            cambiarFiltro(input) {
                const key = input.getAttribute('data-atlas-suc-filter');
                if (!key) return;
                this.filtros[key] = input.value || '';
                this.render();
            },

            limpiarFiltros() {
                Object.keys(this.filtros).forEach(key => {
                    this.filtros[key] = key === 'orden' ? 'detenido_desc' : '';
                });
                document.querySelectorAll('[data-atlas-suc-filter]').forEach(input => {
                    const key = input.getAttribute('data-atlas-suc-filter');
                    input.value = this.filtros[key] || '';
                });
                this.render();
            },

            aplicarFiltros(rows) {
                const f = this.filtros;
                const q = this.norm(f.q);
                const filtrados = rows.filter(row => {
                    if (q) {
                        const texto = [
                            row.fk_sucursal,
                            row.sucursal,
                            row.direccion,
                            row.numero_telefono,
                            row.division_nombre,
                            row.regional_nombre,
                            row.clasificacion_nombre,
                            row.gestores_asignados,
                            row.bucket_resumen
                        ].map(v => this.norm(v)).join(' ');
                        if (!texto.includes(q)) return false;
                    }
                    if (f.division && this.norm(row.division_nombre) !== this.norm(f.division)) return false;
                    if (f.regional && this.norm(row.regional_nombre) !== this.norm(f.regional)) return false;
                    if (f.clasificacion && this.norm(row.clasificacion_nombre) !== this.norm(f.clasificacion)) return false;
                    if (f.presupuesto === 'con' && Number(row.tiene_presupuesto || 0) !== 1) return false;
                    if (f.presupuesto === 'sin' && Number(row.tiene_presupuesto || 0) === 1) return false;
                    if (f.gestor === 'con' && Number(row.total_gestores || 0) <= 0) return false;
                    if (f.gestor === 'sin' && Number(row.total_gestores || 0) > 0) return false;
                    if (f.bucket && !this.tieneBucket(row, f.bucket)) return false;
                    if (!this.enRango(this.cashDetenido(row), f.detenido_min, f.detenido_max)) return false;
                    if (!this.enRango(this.cashAvanzado(row), f.avanzado_min, f.avanzado_max)) return false;
                    if (!this.enRango(this.porcentajeAvance(row), f.avance_min, f.avance_max)) return false;
                    if (!this.enRango(this.totalPendientes(row), f.pendientes_min, f.pendientes_max)) return false;
                    return true;
                });
                return this.ordenarFilas(filtrados, f.orden || 'detenido_desc');
            },

            ordenarFilas(rows, orden) {
                const get = row => {
                    switch (orden) {
                        case 'detenido_asc':
                        case 'detenido_desc':
                            return this.cashDetenido(row);
                        case 'avanzado_asc':
                        case 'avanzado_desc':
                            return this.cashAvanzado(row);
                        case 'avance_asc':
                        case 'avance_desc':
                            return this.porcentajeAvance(row);
                        case 'pendientes_asc':
                        case 'pendientes_desc':
                            return this.totalPendientes(row);
                        case 'meta_cash_asc':
                        case 'meta_cash_desc':
                            return Number(row.presupuesto_meta_cash || 0);
                        case 'fk_asc':
                        case 'fk_desc':
                            return Number(row.fk_sucursal || 0);
                        case 'sucursal_asc':
                        case 'sucursal_desc':
                            return this.norm(row.sucursal);
                        default:
                            return this.cashDetenido(row);
                    }
                };
                const desc = String(orden || '').endsWith('_desc');
                return rows.slice().sort((a, b) => {
                    const av = get(a);
                    const bv = get(b);
                    let cmp = 0;
                    if (typeof av === 'string' || typeof bv === 'string') cmp = String(av).localeCompare(String(bv), 'es');
                    else cmp = Number(av || 0) - Number(bv || 0);
                    if (desc) cmp *= -1;
                    if (cmp !== 0) return cmp;
                    return this.norm(a.sucursal).localeCompare(this.norm(b.sucursal), 'es');
                });
            },

            cargarOpcionesFiltros() {
                this.llenarSelectFiltro('division', this.valoresUnicos('division_nombre'));
                this.llenarSelectFiltro('regional', this.valoresUnicos('regional_nombre'));
                this.llenarSelectFiltro('clasificacion', this.valoresUnicos('clasificacion_nombre'));
                this.llenarSelectFiltro('bucket', this.bucketsUnicos());
            },

            valoresUnicos(campo) {
                return Array.from(new Set(this.datos.map(row => String(row[campo] || '').trim()).filter(Boolean)))
                    .sort((a, b) => a.localeCompare(b, 'es'));
            },

            bucketsUnicos() {
                const buckets = new Set();
                this.datos.forEach(row => {
                    String(row.bucket_resumen || '').split('|').map(x => x.trim()).filter(Boolean).forEach(raw => {
                        const label = this.bucketLabel(raw);
                        if (label) buckets.add(label);
                    });
                });
                return Array.from(buckets).sort((a, b) => a.localeCompare(b, 'es'));
            },

            llenarSelectFiltro(key, values) {
                const select = document.querySelector(`[data-atlas-suc-filter="${key}"]`);
                if (!select) return;
                const current = select.value;
                const firstText = select.querySelector('option')?.textContent || 'Todos';
                select.innerHTML = '<option value="">' + this.escape(firstText) + '</option>'
                    + values.map(value => '<option value="' + this.escape(value) + '">' + this.escape(value) + '</option>').join('');
                select.value = values.includes(current) ? current : '';
                this.filtros[key] = select.value;
            },

            renderFiltroConteo() {
                const el = document.getElementById('atlasSucFilterCount');
                if (!el) return;
                el.textContent = `Mostrando ${this.number(this.filtrados.length)} de ${this.number(this.datos.length)} sucursales.`;
            },

            cashDetenido(row) {
                return Number(row.cash_detenido_total || 0);
            },

            cashAvanzado(row) {
                return Number(row.cash_avanzado_total || row.cash_terminal_total || 0);
            },

            porcentajeAvance(row) {
                const detenido = this.cashDetenido(row);
                const avanzado = this.cashAvanzado(row);
                const total = detenido + avanzado;
                return total > 0 ? Math.round((avanzado / total) * 100) : 0;
            },

            totalPendientes(row) {
                return Number(row.total_pendientes || 0) + Number(row.total_revisar_etapa || 0);
            },

            enRango(value, min, max) {
                const n = Number(value || 0);
                if (String(min || '').trim() !== '' && n < Number(min)) return false;
                if (String(max || '').trim() !== '' && n > Number(max)) return false;
                return true;
            },

            tieneBucket(row, bucket) {
                const needle = this.norm(bucket);
                return String(row.bucket_resumen || '').split('|').some(raw => this.norm(this.bucketLabel(raw)) === needle);
            },

            renderKpis() {
                const el = document.getElementById('atlasSucKpis');
                if (!el) return;
                const presupuesto = this.meta.presupuesto_base || null;
                const sinPresupuesto = this.datos.filter(r => Number(r.tiene_presupuesto || 0) === 0);
                const etiquetaMes = presupuesto ? `${presupuesto.nombre_mes || 'Mes'} ${presupuesto.anio || ''}`.trim() : 'Sin presupuesto base';
                el.style.display = '';
                el.innerHTML = `
                    <button type="button" class="atlas-suc-kpi atlas-suc-kpi-warn" data-atlas-suc-sin-presupuesto>
                        <span><i class="fa-solid fa-triangle-exclamation"></i>Sin presupuesto</span>
                        <strong>${this.number(sinPresupuesto.length)}</strong>
                        <small>${this.escape(etiquetaMes)}</small>
                    </button>
                `;
            },

            verSinPresupuesto() {
                const presupuesto = this.meta.presupuesto_base || null;
                const rows = this.datos.filter(r => Number(r.tiene_presupuesto || 0) === 0);
                this.text('atlasSucSinPresSub', presupuesto
                    ? `Presupuesto base: ${presupuesto.nombre_mes || 'Mes'} ${presupuesto.anio || ''}`
                    : 'No hay presupuesto base cargado; todas las sucursales quedan pendientes.');
                const el = document.getElementById('atlasSucSinPresLista');
                if (!el) return;
                el.innerHTML = rows.length ? rows.map(row => `
                    <div class="atlas-suc-budget-item">
                        <div>
                            <div class="atlas-suc-budget-title">FK ${this.escape(row.fk_sucursal)} · ${this.escape(row.sucursal || 'Sucursal sin nombre')}</div>
                            <div class="atlas-suc-budget-meta">${this.escape(row.direccion || 'Sin dirección')}</div>
                            <div class="atlas-suc-budget-meta">${this.escape(row.division_nombre || 'Sin división')} / ${this.escape(row.regional_nombre || 'Sin regional')}</div>
                        </div>
                        <span class="atlas-suc-asig-badge atlas-suc-asig-badge-warn"><i class="fa-solid fa-file-circle-exclamation"></i>Sin meta</span>
                    </div>
                `).join('') : '<div class="atlas-suc-asig-muted-box">Todas las sucursales activas tienen presupuesto cargado.</div>';
                this.abrirModalPorId('modalAtlasSucSinPresupuesto');
            },

            async verDetalle(fkSucursal) {
                this.wait(true);
                try {
                    const res = await this.getJson('/Atlas/getSucursalAsignadaDetalle?fk_sucursal=' + encodeURIComponent(fkSucursal));
                    if (!res.success) {
                        this.toast(res.mensaje || 'No se pudo cargar detalle.', 'error');
                        return;
                    }
                    this.renderDetalle(res.datos || {});
                    this.abrirModal();
                } finally {
                    this.wait(false);
                }
            },

            renderDetalle(data) {
                const s = data.sucursal || {};
                const gestores = Array.isArray(data.gestores) ? data.gestores : [];
                const creditos = Array.isArray(data.creditos) ? data.creditos : [];
                this.text('atlasSucModalTitle', `${s.sucursal || 'Sucursal'} (${s.fk_sucursal || ''})`);
                this.text('atlasSucModalSub', `${s.direccion || 'Sin dirección'} · ${s.numero_telefono || 'Sin teléfono'}`);
                const el = document.getElementById('atlasSucDetalleContenido');
                if (!el) return;
                el.innerHTML = `
                    <div class="atlas-suc-asig-section-title">Gestores asignados</div>
                    ${gestores.length ? gestores.map(g => `
                        <div class="atlas-suc-asig-mini">
                            <div class="atlas-suc-asig-mini-title">${this.escape(g.gestor_nombre || g.user_name || 'Gestor')}</div>
                            <div class="atlas-suc-asig-mini-meta">${this.escape(g.numero_empleado || '')} · ${this.escape(g.tipo_cobertura || 'principal')} ${Number(g.es_principal || 0) ? '· Principal' : ''}</div>
                        </div>`).join('') : '<div class="atlas-suc-asig-muted-box">Esta sucursal no tiene gestores asignados.</div>'}
                    <div class="atlas-suc-asig-section-title">Créditos / ofertas con cruce México</div>
                    ${creditos.length ? `
                        <div class="table-responsive">
                            <table class="table border-top">
                                <thead>
                                    <tr>
                                        <th>Crédito</th>
                                        <th>Cliente / usuario</th>
                                        <th>Etapa México</th>
                                        <th>Bucket calculado</th>
                                        <th>Monto financiar</th>
                                        <th>Seguimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${creditos.map(c => `
                                        <tr>
                                            <td>
                                                <div class="atlas-suc-asig-main">${this.escape(c.id_solicitud || c.credito_id || '')}</div>
                                                <div class="atlas-suc-asig-sub">Vínculo ${this.escape(c.vinculo_credito_id || c.credito_id)} → ${this.escape(c.vinculo_fk_sucursal || s.fk_sucursal)}</div>
                                            </td>
                                            <td>${this.escape(c.usuario || 'Sin usuario')}</td>
                                            <td><div class="atlas-suc-asig-main">${this.escape(c.etapa_original || 'Sin etapa')}</div><div class="atlas-suc-asig-sub">Oferta ${this.escape(c.oferta_id || '')}</div></td>
                                            <td><div class="atlas-suc-asig-main">${this.escape(c.bucket_operativo || '')}</div><div class="atlas-suc-asig-sub">${this.escape(c.tipo_bucket || '')}</div></td>
                                            <td>${this.money(c.monto_financiar)}<div class="atlas-suc-asig-sub">${this.escape(c.fuente_monto || 'oferta.monto_financiar')}</div></td>
                                            <td>${this.escape(c.fecha_siguiente_seguimiento_fmt || c.fecha_siguiente_seguimiento || 'Sin seguimiento')}</td>
                                        </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>` : '<div class="atlas-suc-asig-muted-box">Esta sucursal no tiene créditos con cruce válido a oferta México. Los créditos sin id_solicitud numérico no se muestran como pendientes operativos.</div>'}
                `;
            },

            abrirModal() {
                this.abrirModalPorId('modalAtlasSucursalAsignada');
            },

            async getJson(url) {
                const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                return res.json();
            },

            wait(show) {
                if (typeof Swal === 'undefined') return;
                if (show) {
                    Swal.fire({ title: 'Procesando su petición', html: 'Espere un momento...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
                } else {
                    Swal.close();
                }
            },

            toast(msg, icon) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: icon || 'info', text: msg });
                else window.alert(msg);
            },

            destroyTable(key) {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
                const tabla = this.tablas[key];
                if (tabla) {
                    tabla.destroy();
                    this.tablas[key] = null;
                }
            },

            initTable(key, selector) {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable || !document.querySelector(selector)) return;
                this.tablas[key] = jQuery(selector).DataTable({
                    pageLength: 5,
                    lengthMenu: [5, 10, 25, 50, 100],
                    pagingType: 'full_numbers',
                    order: [],
                    responsive: true,
                    autoWidth: false,
                    language: {
                        emptyTable: 'No hay datos disponibles',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                        infoFiltered: '(filtrado de _MAX_ registros totales)',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        loadingRecords: 'Cargando...',
                        processing: 'Procesando...',
                        search: 'Buscar:',
                        zeroRecords: 'No se encontraron resultados',
                        paginate: { first: '&laquo;', last: '&raquo;', next: '&rsaquo;', previous: '&lsaquo;' }
                    },
                    dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>'
                       + '<"row"<"col-sm-12"tr>>'
                       + '<"row align-items-center mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                    drawCallback: () => {
                        jQuery(selector + '_paginate .pagination').addClass('pagination-sm justify-content-end');
                        jQuery(selector + '_length select').addClass('form-select form-select-sm');
                        jQuery(selector + '_filter input').addClass('form-control form-control-sm');
                        this.repararPaginacion(selector);
                    }
                });
                this.repararPaginacion(selector, this.tablas[key]);
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
                paginador.empty().append(items).addClass('pagination-sm justify-content-end');
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

            bucketLabel(raw) {
                const text = String(raw || '').trim();
                return text.replace(/:\s*\d+\s*$/, '').trim();
            },

            bucketCount(raw) {
                const match = String(raw || '').trim().match(/:\s*(\d+)\s*$/);
                return match ? Number(match[1] || 0) : 0;
            },

            bucketClass(label) {
                const n = this.norm(label);
                if (n.includes('revisar')) return 'atlas-suc-asig-badge-danger';
                if (n.includes('no detenido') || n.includes('terminal')) return 'atlas-suc-asig-badge-info';
                if (n.includes('sin etapa') || n.includes('depurar')) return 'atlas-suc-asig-badge-warn';
                return 'atlas-suc-asig-badge-ok';
            },

            pipelineResumen(row) {
                const presupuesto = Number(row.presupuesto_meta_cash || 0);
                const detenido = this.cashDetenido(row);
                const avanzado = this.cashAvanzado(row);
                const base = presupuesto > 0 ? presupuesto : Math.max(detenido + avanzado, 1);
                const pctAvance = presupuesto > 0 ? Math.min(100, Math.round((avanzado / presupuesto) * 100)) : this.porcentajeAvance(row);
                const pctDetenido = Math.min(100, Math.round((detenido / base) * 100));
                const pctAvanzado = Math.min(100, Math.round((avanzado / base) * 100));
                const pctMeta = presupuesto > 0 ? 100 : 0;
                const metaTexto = presupuesto > 0 ? this.money(presupuesto) : 'Sin presupuesto';
                return '<div class="atlas-suc-pipeline-money">'
                    + '<div class="atlas-suc-pipeline-money-title"><span>Avance contra presupuesto</span><strong>' + pctAvance + '%</strong></div>'
                    + this.pipelineMoneyRow('Presupuesto', metaTexto, pctMeta, 'meta')
                    + this.pipelineMoneyRow('Detenido', this.money(detenido), pctDetenido, 'detenido')
                    + this.pipelineMoneyRow('Avanzado', this.money(avanzado), pctAvanzado, 'avanzado')
                    + '</div>'
                    + this.pipelineBadges(row.bucket_resumen);
            },

            pipelineMoneyRow(label, value, pct, type) {
                const safePct = Math.max(0, Math.min(100, Number(pct || 0)));
                return '<div class="atlas-suc-pipeline-money-row">'
                    + '<div class="atlas-suc-pipeline-money-head"><span>' + this.escape(label) + '</span><span class="atlas-suc-pipeline-money-value">' + this.escape(value) + '</span></div>'
                    + '<div class="atlas-suc-pipeline-bar" aria-hidden="true"><div class="atlas-suc-pipeline-bar-fill atlas-suc-pipeline-bar-fill--' + this.escape(type) + '" style="width:' + safePct + '%"></div></div>'
                    + '</div>';
            },

            pipelineBadges(resumen) {
                const rows = String(resumen || '').split('|').map(x => x.trim()).filter(Boolean);
                if (!rows.length) return '<div class="atlas-suc-asig-muted">Sin pipeline operativo</div>';
                return '<div class="atlas-suc-asig-pipeline">' + rows.map(row => {
                    const label = this.bucketLabel(row);
                    const count = this.bucketCount(row);
                    return '<div class="atlas-suc-asig-pipeline-row">'
                        + '<span class="atlas-suc-asig-pipeline-label">' + this.escape(label) + '</span>'
                        + '<span class="atlas-suc-asig-badge ' + this.bucketClass(label) + '">' + count + '</span>'
                        + '</div>';
                }).join('') + '</div>';
            },

            verMapa(index) {
                const row = this.filtrados[index];
                if (!row) return;
                const titulo = row.sucursal || 'Sucursal';
                this.text('atlasSucMapaTitle', titulo);
                this.text('atlasSucMapaSub', 'Ubicación y contacto principal');
                const el = document.getElementById('atlasSucMapaContenido');
                if (!el) return;
                const lat = String(row.latitud || '').trim();
                const lng = String(row.longitud || '').trim();
                const tieneMapa = this.tieneCoordenadas(row);
                const tel = row.numero_telefono || 'Sin teléfono';
                const direccion = row.direccion || 'Sin dirección';
                el.innerHTML = `
                    <div class="atlas-suc-map-meta">
                        <div class="atlas-suc-map-chip">
                            <span class="atlas-suc-map-chip-label"><i class="fa-solid fa-phone me-1"></i>Teléfono</span>
                            <span class="atlas-suc-map-chip-value">${this.escape(tel)}</span>
                        </div>
                        <div class="atlas-suc-map-chip">
                            <span class="atlas-suc-map-chip-label"><i class="fa-solid fa-location-dot me-1"></i>Dirección</span>
                            <span class="atlas-suc-map-chip-value">${this.escape(direccion)}</span>
                        </div>
                    </div>
                    ${tieneMapa
                        ? `<iframe class="atlas-suc-map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=${encodeURIComponent(lat + ',' + lng)}&z=16&output=embed"></iframe>`
                        : '<div class="atlas-suc-asig-muted-box"><i class="fa-solid fa-circle-exclamation me-2"></i>Esta sucursal no tiene coordenadas capturadas, por eso no se puede mostrar el mapa.</div>'}
                `;
                this.abrirMapaModal();
            },

            abrirMapaModal() {
                this.abrirModalPorId('modalAtlasSucursalMapa');
            },

            abrirModalPorId(id) {
                const el = document.getElementById(id);
                if (!el) return;
                if (el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                    return;
                }
                if (window.jQuery && jQuery.fn && jQuery.fn.modal) {
                    jQuery(el).modal('show');
                }
            },

            cashResumen(row) {
                const detenido = Number(row.cash_detenido_total || 0);
                const avanzado = Number(row.cash_avanzado_total || row.cash_terminal_total || 0);
                const total = detenido + avanzado;
                const pct = total > 0 ? Math.round((avanzado / total) * 100) : 0;
                return '<div class="atlas-suc-asig-cash">'
                    + '<div class="atlas-suc-asig-cash-row"><span class="atlas-suc-asig-cash-label">Detenido</span><span class="atlas-suc-asig-cash-value">' + this.money(detenido) + '</span></div>'
                    + '<div class="atlas-suc-asig-cash-row"><span class="atlas-suc-asig-cash-label">Avanzado</span><span class="atlas-suc-asig-cash-value">' + this.money(avanzado) + '</span></div>'
                    + '<div class="atlas-suc-asig-progress" aria-hidden="true"><div class="atlas-suc-asig-progress-fill" style="width:' + pct + '%"></div></div>'
                    + '<div class="atlas-suc-asig-progress-text">' + pct + '% avance</div>'
                    + '</div>';
            },

            tieneCoordenadas(row) {
                const lat = String(row.latitud || '').trim();
                const lng = String(row.longitud || '').trim();
                return lat !== '' && lng !== '';
            },

            value(id) { return document.getElementById(id)?.value || ''; },
            text(id, value) { const el = document.getElementById(id); if (el) el.textContent = value; },
            norm(v) { return String(v || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim(); },
            money(v) { return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(v || 0)); },
            number(v) { return new Intl.NumberFormat('es-MX', { maximumFractionDigits: 0 }).format(Number(v || 0)); },
            escape(v) {
                return String(v ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[ch]));
            },
        };

        window.AtlasSucursalesAsignadas = AtlasSucursalesAsignadas;
        document.addEventListener('DOMContentLoaded', () => AtlasSucursalesAsignadas.init());
    })();
    </script>
</div>
