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
        .atlas-pres-resp-compact { max-width:19rem; cursor:help; }
        .atlas-pres-resp-main { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .atlas-pres-resp-summary { display:flex; align-items:center; gap:.3rem .45rem; flex-wrap:wrap; margin-top:.2rem; color:#7a838b; font-size:.73rem; font-weight:800; line-height:1.2; }
        .atlas-pres-resp-more { display:inline-flex; align-items:center; border-radius:999px; padding:.12rem .45rem; background:#eef2ff; color:#334b8f; font-size:.68rem; font-weight:900; }
        .tooltip.atlas-pres-resp-tooltip { --bs-tooltip-max-width:min(25rem, 92vw); }
        .tooltip.atlas-pres-resp-tooltip .tooltip-inner { text-align:left; padding:.65rem .75rem; }
        .atlas-pres-resp-tip-title { font-weight:900; margin-bottom:.42rem; }
        .atlas-pres-resp-tip-row { display:grid; gap:.08rem; padding:.35rem 0; border-top:1px solid rgba(255,255,255,.18); }
        .atlas-pres-resp-tip-row:first-of-type { border-top:0; padding-top:0; }
        .atlas-pres-resp-tip-row strong { line-height:1.18; }
        .atlas-pres-resp-tip-row span { opacity:.82; font-size:.75rem; line-height:1.2; }
        .atlas-pres-detail-table { min-width:76rem; }
        .atlas-pres-detail-table thead th { white-space:nowrap; }
        .atlas-pres-th { display:inline-flex; align-items:center; gap:.4rem; }
        .atlas-pres-th i { color:#64748b; font-size:.8rem; }
        .atlas-pres-owner { display:flex; align-items:center; gap:.65rem; min-width:13rem; }
        .atlas-pres-owner-icon {
            width:2rem;
            height:2rem;
            flex:0 0 2rem;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border-radius:.45rem;
            background:#eaf2ff;
            color:#2563eb;
        }
        .atlas-pres-owner-copy { min-width:0; }
        .atlas-pres-owner-copy .atlas-pres-main { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .atlas-pres-value { display:flex; align-items:center; gap:.45rem; white-space:nowrap; font-weight:900; color:#22303e; }
        .atlas-pres-value i { width:1.1rem; text-align:center; color:#2563eb; }
        .atlas-pres-value.cash i { color:#0f9f8f; }
        .atlas-pres-value.base i { color:#6d5dfc; }
        .atlas-pres-selection-table { min-width:42rem; }
        .atlas-pres-selection-table td,
        .atlas-pres-selection-table th { vertical-align:middle; }
        .atlas-pres-selection-wrap { border:1px solid #dbe3ec; border-radius:.55rem; overflow:auto; max-height:18rem; }
        .atlas-pres-selection-empty { color:#64748b; font-size:.82rem; font-weight:700; padding:1rem; text-align:center; }
        .atlas-pres-selection-total { display:flex; align-items:center; justify-content:flex-end; gap:.9rem; flex-wrap:wrap; padding:.65rem .8rem; border-top:1px solid #e5e7eb; background:#f8fafc; color:#475569; font-size:.78rem; font-weight:800; }
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
        #modalAtlasPresupuestoComparativo .modal-content,
        #modalAtlasPresupuestoEditar .modal-content,
        #modalAtlasPresupuestoReasignar .modal-content,
        #modalAtlasPresupuestoEliminarSucursal .modal-content,
        #modalAtlasPresupuestoRanking .modal-content,
        #modalAtlasPresupuestoBitacora .modal-content { border:0; border-radius:.875rem; box-shadow:var(--bs-box-shadow-lg); overflow:hidden; }
        #modalAtlasPresupuestoImportar .modal-header,
        #modalAtlasPresupuestoComparativo .modal-header,
        #modalAtlasPresupuestoEditar .modal-header,
        #modalAtlasPresupuestoReasignar .modal-header,
        #modalAtlasPresupuestoEliminarSucursal .modal-header,
        #modalAtlasPresupuestoRanking .modal-header,
        #modalAtlasPresupuestoBitacora .modal-header { border-bottom:1px solid #e5e7eb; padding:1rem 1.25rem; }
        #modalAtlasPresupuestoImportar .modal-footer,
        #modalAtlasPresupuestoComparativo .modal-footer,
        #modalAtlasPresupuestoEditar .modal-footer,
        #modalAtlasPresupuestoReasignar .modal-footer,
        #modalAtlasPresupuestoEliminarSucursal .modal-footer,
        #modalAtlasPresupuestoRanking .modal-footer,
        #modalAtlasPresupuestoBitacora .modal-footer { border-top:1px solid #e5e7eb; padding:1rem 1.25rem; gap:.75rem; }
        #modalAtlasPresupuestoComparativo .modal-body,
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
        #modalAtlasPresupuestoComparativo .modal-body::-webkit-scrollbar,
        #modalAtlasPresupuestoReasignar .modal-body::-webkit-scrollbar,
        #modalAtlasPresupuestoEliminarSucursal .modal-body::-webkit-scrollbar { width:.6rem; }
        #modalAtlasPresupuestoComparativo .modal-body::-webkit-scrollbar-track,
        #modalAtlasPresupuestoReasignar .modal-body::-webkit-scrollbar-track,
        #modalAtlasPresupuestoEliminarSucursal .modal-body::-webkit-scrollbar-track { background:#eef2f7; }
        #modalAtlasPresupuestoComparativo .modal-body::-webkit-scrollbar-thumb,
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
        .atlas-pres-adjust-summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .atlas-pres-adjust-metric { border:1px solid #dbe3ec; border-left:4px solid #2563eb; border-radius:.5rem; background:#fff; padding:.75rem .85rem; min-width:0; }
        .atlas-pres-adjust-metric.is-green { border-left-color:#0f9f8f; }
        .atlas-pres-adjust-metric.is-violet { border-left-color:#6d5dfc; }
        .atlas-pres-adjust-metric.is-slate { border-left-color:#64748b; }
        .atlas-pres-adjust-metric span { display:block; color:#64748b; font-size:.7rem; font-weight:800; }
        .atlas-pres-adjust-metric strong { display:block; color:#22303e; font-size:1.2rem; font-weight:900; margin-top:.14rem; }
        .atlas-pres-adjust-totals { min-width:42rem; margin-bottom:0; }
        .atlas-pres-adjust-totals th,
        .atlas-pres-adjust-totals td { vertical-align:middle; white-space:nowrap; }
        .atlas-pres-adjust-table-shell { overflow:hidden; background:#fff; }
        .atlas-pres-adjust-table { min-width:89rem; margin-bottom:0; table-layout:fixed; }
        .atlas-pres-adjust-table th { white-space:nowrap; background:#f8fafc; }
        .atlas-pres-adjust-table td { vertical-align:middle; }
        .atlas-pres-adjust-table th:nth-child(1) { width:5rem; }
        .atlas-pres-adjust-table th:nth-child(2) { width:14rem; }
        .atlas-pres-adjust-table th:nth-child(3) { width:15rem; }
        .atlas-pres-adjust-table th:nth-child(4) { width:10rem; }
        .atlas-pres-adjust-table th:nth-child(5) { width:12rem; }
        .atlas-pres-adjust-table th:nth-child(6) { width:11rem; }
        .atlas-pres-adjust-table th:nth-child(7) { width:11rem; }
        .atlas-pres-adjust-table th:nth-child(8) { width:11rem; }
        .atlas-pres-adjust-pagination { border-top:1px solid #e5e7eb; background:#f8fafc; padding:.65rem .8rem; display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }
        .atlas-pres-adjust-pagination-copy { color:#64748b; font-size:.76rem; font-weight:800; }
        .atlas-pres-adjust-pagination-controls { display:flex; align-items:center; gap:.45rem; }
        .atlas-pres-adjust-pagination-controls .form-select { width:auto; min-width:4.6rem; }
        .atlas-pres-adjust-pagination-controls .btn { width:2rem; height:2rem; padding:0; display:inline-flex; align-items:center; justify-content:center; }
        .atlas-pres-adjust-change { display:grid; gap:.18rem; min-width:9rem; }
        .atlas-pres-adjust-before { color:#64748b; font-size:.72rem; font-weight:700; line-height:1.2; }
        .atlas-pres-adjust-after { color:#22303e; font-size:.78rem; font-weight:900; line-height:1.2; }
        .atlas-pres-adjust-after i { color:#2563eb; font-size:.65rem; margin-right:.22rem; }
        .atlas-pres-adjust-field-list { display:flex; align-items:center; gap:.3rem; flex-wrap:wrap; }
        .atlas-pres-adjust-blockers { display:grid; gap:.45rem; }
        .atlas-pres-adjust-blocker { display:flex; align-items:flex-start; gap:.55rem; color:#475569; font-size:.8rem; font-weight:700; }
        .atlas-pres-adjust-blocker i { color:#2563eb; margin-top:.12rem; }
        .swal2-popup.atlas-pres-analysis-popup { width:min(30rem, calc(100vw - 2rem)); padding:1.35rem; }
        .atlas-pres-analysis { text-align:left; }
        .atlas-pres-analysis-head { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; }
        .atlas-pres-analysis-head > div { min-width:0; }
        .atlas-pres-analysis-icon { width:2.5rem; height:2.5rem; flex:0 0 2.5rem; border-radius:.5rem; display:inline-flex; align-items:center; justify-content:center; background:#eaf2ff; color:#2563eb; font-size:1.05rem; }
        .atlas-pres-analysis-title { color:#22303e; font-size:1.05rem; font-weight:900; line-height:1.2; }
        .atlas-pres-analysis-file { color:#64748b; font-size:.75rem; font-weight:700; line-height:1.25; margin-top:.16rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:22rem; }
        .atlas-pres-analysis-status { min-height:2.7rem; color:#475569; font-size:.86rem; font-weight:700; line-height:1.35; }
        .atlas-pres-analysis-progress { height:.55rem; background:#e2e8f0; margin:.85rem 0 .65rem; }
        .atlas-pres-analysis-progress .progress-bar { background:#2563eb; transition:width .45s ease; }
        .atlas-pres-analysis-meta { display:flex; align-items:center; justify-content:space-between; gap:.35rem .75rem; flex-wrap:wrap; color:#64748b; font-size:.72rem; font-weight:800; }
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
            .atlas-pres-summary,
            .atlas-pres-adjust-summary { grid-template-columns:1fr; }
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
                        <i class="fa-solid fa-arrow-right-arrow-left icon-sm me-sm-1"></i><span>Reasignar sucursales</span>
                    </button>
                    <button type="button" class="btn btn-label-secondary btn-action-size" data-atlas-pres-regresar>
                        <i class="fa-solid fa-arrow-left icon-sm me-sm-1"></i><span>Regresar a Presupuestos</span>
                    </button>
                </div>
            </div>
            <div class="atlas-pres-summary" id="atlasPresDetalleResumen"></div>
            <div class="card-datatable table-responsive">
                <table class="dt-responsive table border-top atlas-pres-detail-table" id="atlasPresDetalleTabla">
                    <thead>
                        <tr>
                            <th><span class="atlas-pres-th"><i class="fa-solid fa-hashtag"></i>PK sucursal</span></th>
                            <th><span class="atlas-pres-th"><i class="fa-solid fa-store"></i>Sucursal</span></th>
                            <th id="atlasPresAsignacionHeader"><span class="atlas-pres-th"><i class="fa-solid fa-user-check"></i>Asignación</span></th>
                            <th><span class="atlas-pres-th"><i class="fa-solid fa-list-ol"></i>Presupuesto de créditos</span></th>
                            <th><span class="atlas-pres-th"><i class="fa-solid fa-money-bill-wave"></i>Presupuesto de cash</span></th>
                            <th><span class="atlas-pres-th"><i class="fa-solid fa-gauge-high"></i>Presupuesto base</span></th>
                            <th><span class="atlas-pres-th"><i class="fa-solid fa-medal"></i>Clasificación</span></th>
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
                        <div>
                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2"></i>Cargar o reajustar presupuesto</h5>
                            <div class="text-muted small fw-semibold">El archivo se revisará antes de aplicar cualquier cambio.</div>
                        </div>
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
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass-chart me-1"></i>Analizar archivo</button>
                        <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasPresupuestoComparativo" tabindex="-1" aria-hidden="true" aria-labelledby="atlasPresComparativoTitulo">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content" id="atlasPresComparativoForm">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="atlasPresComparativoTitulo">
                            <i class="fa-solid fa-code-compare me-2"></i>Revisión del reajuste
                        </h5>
                        <div class="text-muted small fw-semibold" id="atlasPresComparativoSub"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-start gap-2 mb-3" id="atlasPresComparativoEstado" role="status">
                        <i class="fa-solid fa-circle-info mt-1"></i>
                        <div id="atlasPresComparativoMensaje">Preparando comparativo...</div>
                    </div>

                    <div class="atlas-pres-adjust-summary" id="atlasPresComparativoResumen"></div>

                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                        <strong><i class="fa-solid fa-chart-column me-2 text-primary"></i>Totales del mes</strong>
                        <span class="text-muted small fw-semibold" id="atlasPresComparativoVigencia"></span>
                    </div>
                    <div class="table-responsive border rounded mb-4">
                        <table class="table table-sm atlas-pres-adjust-totals">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th class="text-end">Antes</th>
                                    <th class="text-end">Después</th>
                                    <th class="text-end">Diferencia</th>
                                </tr>
                            </thead>
                            <tbody id="atlasPresComparativoTotales"></tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                        <strong><i class="fa-solid fa-table-list me-2 text-primary"></i>Cambios por sucursal</strong>
                        <span class="atlas-pres-badge atlas-pres-badge-info" id="atlasPresComparativoConteo">0 cambios</span>
                    </div>
                    <div class="atlas-pres-adjust-table-shell border rounded mb-3">
                        <div class="table-responsive">
                            <table class="table table-sm atlas-pres-adjust-table">
                                <thead>
                                    <tr>
                                        <th>PK</th>
                                        <th>Sucursal</th>
                                        <th>Responsable</th>
                                        <th>Créditos</th>
                                        <th>Cash</th>
                                        <th>Presupuesto base</th>
                                        <th>Clasificación</th>
                                        <th>Cambios</th>
                                    </tr>
                                </thead>
                                <tbody id="atlasPresComparativoBody"></tbody>
                            </table>
                        </div>
                        <div class="atlas-pres-adjust-pagination" id="atlasPresComparativoPaginacion">
                            <span class="atlas-pres-adjust-pagination-copy" id="atlasPresComparativoRango">Sin registros</span>
                            <div class="atlas-pres-adjust-pagination-controls">
                                <button type="button" class="btn btn-sm btn-label-secondary" id="atlasPresComparativoAnterior" aria-label="Página anterior" title="Página anterior">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <label class="visually-hidden" for="atlasPresComparativoPagina">Página</label>
                                <select class="form-select form-select-sm" id="atlasPresComparativoPagina" aria-label="Página del comparativo"></select>
                                <span class="atlas-pres-adjust-pagination-copy" id="atlasPresComparativoPaginas">de 1</span>
                                <button type="button" class="btn btn-sm btn-label-secondary" id="atlasPresComparativoSiguiente" aria-label="Página siguiente" title="Página siguiente">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="atlasPresComparativoBloqueos" class="mb-3"></div>

                    <div>
                        <label class="form-label fw-bold" for="atlasPresComparativoMotivo">Motivo del reajuste *</label>
                        <textarea
                            class="form-control"
                            id="atlasPresComparativoMotivo"
                            rows="3"
                            minlength="5"
                            maxlength="500"
                            placeholder="Ej. Reestructura mensual de responsables y presupuesto"
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="submit" class="btn btn-primary" id="atlasPresComparativoConfirmar" disabled>
                        <i class="fa-solid fa-circle-check me-1"></i>Confirmar reajuste
                    </button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalAtlasPresupuestoEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <form id="atlasPresEditForm">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold" id="atlasPresEditTitle"><i class="fa-solid fa-pen-to-square me-2"></i>Editar presupuesto de sucursal</h5>
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
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Presupuesto de créditos *</label>
                                <input class="form-control" name="meta_creditos" id="atlasPresEditCreditos" type="number" min="0" step="1" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Presupuesto de cash *</label>
                                <input class="form-control" name="meta_cash" id="atlasPresEditCash" type="number" min="0" step="0.01" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Presupuesto base</label>
                                <input class="form-control" name="comisiona_a_partir_de" id="atlasPresEditBase" type="number" min="0" step="1">
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
                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-arrow-right-arrow-left me-2"></i>Reasignar sucursales</h5>
                            <div class="text-muted small fw-semibold" id="atlasPresReasignarSub">Cambia el responsable mensual de una o varias sucursales.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
                            <i class="fa-solid fa-circle-info mt-1"></i>
                            <div>Cada sucursal conserva completos sus créditos, cash, presupuesto base y clasificación. Las rutas y visitas existentes mantienen su historial.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresReasignarOrigen">Sucursales que cambiarán de responsable *</label>
                                <select class="form-select" id="atlasPresReasignarOrigen" multiple required></select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresReasignarDestino">Nuevo responsable *</label>
                                <select class="form-select" id="atlasPresReasignarDestino" required></select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <strong>Presupuesto que cambia de responsable</strong>
                                    <span class="atlas-pres-badge atlas-pres-badge-info" id="atlasPresReasignarConteo">0 sucursales</span>
                                </div>
                                <div class="atlas-pres-selection-wrap" id="atlasPresReasignarSucursales">
                                    <div class="atlas-pres-selection-empty">Selecciona una o varias sucursales.</div>
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
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>Guardar reasignación</button>
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
                            <div>Selecciona una sucursal y un responsable destino. Sus créditos y cash se sumarán completos antes de eliminar el registro original.</div>
                        </div>
                        <div class="border rounded p-3 bg-light mb-3" id="atlasPresEliminarOrigenResumen"></div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresEliminarDestino">Sucursal destino *</label>
                                <select class="form-select" id="atlasPresEliminarDestino" required></select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold" for="atlasPresEliminarResponsable">Responsable de la sucursal destino *</label>
                                <select class="form-select" id="atlasPresEliminarResponsable" required></select>
                            </div>
                            <div class="col-12">
                                <strong class="d-block mb-2">Presupuesto resultante en la sucursal destino</strong>
                                <div class="atlas-pres-selection-wrap" id="atlasPresEliminarAsignaciones">
                                    <div class="atlas-pres-selection-empty">Selecciona una sucursal destino.</div>
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
            reasignacionSucursales: [],
            eliminacionOrigen: null,
            eliminacionDestino: null,
            reajusteAnalisis: null,
            comparativoCambios: [],
            comparativoPagina: 1,
            comparativoPorPagina: 50,
            analisisProgreso: null,
            analisisDuracionStorageKey: 'atlas_presupuesto_analisis_duracion_ms',
            confirmacionProgreso: null,
            confirmacionDuracionStorageKey: 'atlas_presupuesto_confirmacion_duracion_ms',
            modalImport: null,
            modalComparativo: null,
            modalEdit: null,
            modalReasignar: null,
            modalEliminarSucursal: null,
            modalRanking: null,
            modalBitacora: null,

            mountModalsAtBody() {
                [
                    'modalAtlasPresupuestoImportar',
                    'modalAtlasPresupuestoComparativo',
                    'modalAtlasPresupuestoEditar',
                    'modalAtlasPresupuestoReasignar',
                    'modalAtlasPresupuestoEliminarSucursal',
                    'modalAtlasPresupuestoRanking',
                    'modalAtlasPresupuestoBitacora'
                ].forEach((id) => {
                    const modal = document.getElementById(id);
                    if (modal && modal.parentElement !== document.body) {
                        document.body.appendChild(modal);
                    }
                });
            },

            init() {
                this.mountModalsAtBody();
                this.modalImport = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoImportar'));
                this.modalComparativo = new bootstrap.Modal(document.getElementById('modalAtlasPresupuestoComparativo'));
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
                document.getElementById('atlasPresComparativoForm').addEventListener('submit', (ev) => this.confirmarReajusteMasivo(ev));
                document.getElementById('atlasPresComparativoMotivo').addEventListener('input', () => this.actualizarEstadoConfirmacionReajuste());
                document.getElementById('atlasPresComparativoAnterior').addEventListener('click', () => this.cambiarPaginaComparativoReajuste(-1));
                document.getElementById('atlasPresComparativoSiguiente').addEventListener('click', () => this.cambiarPaginaComparativoReajuste(1));
                document.getElementById('atlasPresComparativoPagina').addEventListener('change', (ev) => {
                    this.comparativoPagina = parseInt(ev.target.value, 10) || 1;
                    this.renderFilasComparativoReajuste();
                });
                document.getElementById('atlasPresEditForm').addEventListener('submit', (ev) => this.guardarDetalle(ev));
                document.getElementById('atlasPresReasignarForm').addEventListener('submit', (ev) => this.reasignarPresupuesto(ev));
                document.getElementById('atlasPresReasignarOrigen').addEventListener('change', () => this.actualizarReasignacion());
                document.getElementById('atlasPresReasignarDestino').addEventListener('change', () => this.actualizarEstadoReasignacion());
                document.getElementById('atlasPresReasignarMotivo').addEventListener('input', () => this.actualizarEstadoReasignacion());
                document.getElementById('atlasPresEliminarSucursalForm').addEventListener('submit', (ev) => this.eliminarSucursal(ev));
                document.getElementById('atlasPresEliminarDestino').addEventListener('change', () => this.cambiarDestinoEliminacion());
                document.getElementById('atlasPresEliminarResponsable').addEventListener('change', () => this.actualizarEstadoEliminarSucursal());
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
                        const etiqueta = item.presupuesto ? `${nombre} - Analizar reajuste` : `${nombre} - Carga nueva con revisión`;
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
                document.getElementById('atlasPresAsignacionHeader').innerHTML =
                    `<span class="atlas-pres-th"><i class="fa-solid fa-user-check"></i>Asignación ${this.escape(p.nombre_mes)}</span>`;
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
                        <td><span class="atlas-pres-badge atlas-pres-badge-muted">${this.escape(d.fk_sucursal)}</span></td>
                        <td>
                            <div class="atlas-pres-main">${this.escape(d.sucursal || 'Sin sucursal')}</div>
                            <div class="atlas-pres-sub">${this.escape(d.distribuidor || 'Sin distribuidor')}</div>
                        </td>
                        <td>${this.renderResponsableDetalle(d)}</td>
                        <td><div class="atlas-pres-value"><i class="fa-solid fa-list-ol"></i>${this.number(d.meta_creditos)}</div></td>
                        <td><div class="atlas-pres-value cash"><i class="fa-solid fa-money-bill-wave"></i>${this.money(d.meta_cash)}</div></td>
                        <td><div class="atlas-pres-value base"><i class="fa-solid fa-gauge-high"></i>${this.presupuestoBase(d.comisiona_a_partir_de)}</div></td>
                        <td>${this.renderClasificacionBadge(d)}</td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-label-primary" data-pres-distribuir="${d.id}" title="Reasignar sucursal"><i class="fa-solid fa-user-pen"></i></button>
                                <button type="button" class="btn btn-sm btn-label-secondary" data-pres-edit="${d.id}" title="Editar presupuesto"><i class="fa-solid fa-pen"></i></button>
                                <button type="button" class="btn btn-sm btn-label-danger" data-pres-eliminar="${d.id}" title="Reasignar y eliminar sucursal"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </td>
                    </tr>
                `).join('');
                this.inicializarTablaDom('#atlasPresDetalleTabla', 10);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            renderResponsableDetalle(detalle) {
                const asignaciones = (detalle.asignaciones || []).filter(item => parseInt(item.persona_id, 10) > 0);
                const asignacion = asignaciones.length === 1 ? asignaciones[0] : null;
                const nombre = asignacion
                    ? (asignacion.gestor || asignacion.gestor_nombre || detalle.asesor)
                    : detalle.asesor;
                const tieneCompartida = asignaciones.length > 1;
                return `
                    <div class="atlas-pres-owner">
                        <span class="atlas-pres-owner-icon"><i class="fa-solid fa-user"></i></span>
                        <div class="atlas-pres-owner-copy">
                            <div class="atlas-pres-main">${this.escape(nombre || 'Sin responsable')}</div>
                            <div class="atlas-pres-sub">${tieneCompartida ? 'Asignación anterior por consolidar' : 'Responsable del mes'}</div>
                        </div>
                    </div>`;
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
                document.getElementById('atlasPresEditBase').value = '';

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
                this.reasignacionSucursales = [];
                document.getElementById('atlasPresReasignarOrigen').innerHTML = '<option value="">Cargando sucursales...</option>';
                document.getElementById('atlasPresReasignarDestino').innerHTML = '';
                document.getElementById('atlasPresReasignarSucursales').innerHTML =
                    '<div class="atlas-pres-selection-empty">Consultando sucursales...</div>';
                document.getElementById('atlasPresReasignarConteo').textContent = '0 sucursales';
                form.querySelector('button[type="submit"]').disabled = true;

                this.cargarCatalogosReasignacion(() => {
                    this.poblarSelectSucursales('atlasPresReasignarOrigen');
                    this.poblarSelectResponsables('atlasPresReasignarDestino');
                    this.inicializarBuscadoresReasignacion();
                    document.getElementById('atlasPresReasignarSub').textContent =
                        `${this.detalle.presupuesto.nombre_mes} ${this.detalle.presupuesto.anio}`;
                    this.modalReasignar.show();
                    if (parseInt(detalleIdInicial, 10) > 0) {
                        this.seleccionarValores('#atlasPresReasignarOrigen', [detalleIdInicial]);
                    }
                    this.actualizarReasignacion();
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
                            showError(resp?.mensaje || 'No se pudo cargar la reasignación del presupuesto.');
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
                    onError: (mensaje) => showError(mensaje || 'No se pudo cargar la reasignación del presupuesto.')
                });
            },

            poblarSelectSucursales(id, excluirDetalleId = 0) {
                const sucursales = (this.reasignacionCatalogos?.sucursales || [])
                    .filter(item => parseInt(item.detalle_id, 10) !== parseInt(excluirDetalleId, 10));
                const multiple = document.getElementById(id).multiple;
                document.getElementById(id).innerHTML = (multiple ? '' : '<option value=""></option>') + sucursales.map(item => {
                    const completa = this.sucursalCompleta(item.detalle_id);
                    const responsable = this.responsableSucursal(completa);
                    return `<option value="${parseInt(item.detalle_id, 10)}">FK ${this.escape(item.fk_sucursal)} - ${this.escape(item.sucursal || 'Sin sucursal')} - ${this.escape(responsable.nombre)}</option>`;
                }).join('');
            },

            poblarSelectResponsables(id) {
                document.getElementById(id).innerHTML = '<option value=""></option>' + (this.reasignacionCatalogos?.usuarios_destino || []).map(usuario => {
                    const empleado = usuario.numero_empleado ? ` - ${this.escape(usuario.numero_empleado)}` : '';
                    const acceso = usuario.acceso_movil ? '' : ' - acceso móvil pendiente';
                    return `<option value="${parseInt(usuario.persona_id, 10)}">${this.escape(usuario.nombre || '')}${empleado}${acceso}</option>`;
                }).join('');
            },

            inicializarSelect2Presupuesto(selector, placeholder, modalSelector, multiple = false, callback = null) {
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
                this.inicializarSelect2Presupuesto(
                    '#atlasPresReasignarOrigen',
                    'Buscar y seleccionar sucursales',
                    '#modalAtlasPresupuestoReasignar',
                    true,
                    () => this.actualizarReasignacion()
                );
                this.inicializarSelect2Presupuesto(
                    '#atlasPresReasignarDestino',
                    'Buscar responsable',
                    '#modalAtlasPresupuestoReasignar',
                    false,
                    () => this.actualizarEstadoReasignacion()
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
                    jQuery(selector)
                        .val(element.multiple ? normalized : (normalized[0] || null))
                        .trigger('change.select2');
                }
            },

            valoresSeleccionados(id) {
                return Array.from(document.getElementById(id)?.selectedOptions || [])
                    .map(option => parseInt(option.value, 10))
                    .filter(value => value > 0);
            },

            sucursalCompleta(detalleId) {
                const id = parseInt(detalleId, 10);
                const api = (this.reasignacionCatalogos?.sucursales || [])
                    .find(item => parseInt(item.detalle_id, 10) === id) || {};
                const local = (this.detalle?.detalles || [])
                    .find(item => parseInt(item.id, 10) === id) || {};
                return { ...api, ...local, detalle_id: id };
            },

            responsableSucursal(sucursal) {
                const asignaciones = (sucursal?.asignaciones || [])
                    .filter(item => parseInt(item.persona_id, 10) > 0);
                const personaId = parseInt(sucursal?.asesor_persona_id, 10)
                    || (asignaciones.length === 1 ? parseInt(asignaciones[0].persona_id, 10) : 0)
                    || 0;
                const nombre = String(
                    sucursal?.asesor
                    || (asignaciones.length === 1 ? asignaciones[0].gestor : '')
                    || (asignaciones.length === 1 ? asignaciones[0].gestor_nombre : '')
                    || (asignaciones.length > 1 ? 'Asignación anterior por consolidar' : '')
                    || 'Sin responsable'
                ).trim();
                return { persona_id: personaId, nombre: nombre || 'Sin responsable' };
            },

            responsablePorId(personaId) {
                const id = parseInt(personaId, 10);
                return (this.reasignacionCatalogos?.usuarios_destino || [])
                    .find(item => parseInt(item.persona_id, 10) === id) || null;
            },

            actualizarReasignacion() {
                this.reasignacionSucursales = this.valoresSeleccionados('atlasPresReasignarOrigen')
                    .map(id => this.sucursalCompleta(id))
                    .filter(item => item.detalle_id > 0);
                const total = this.reasignacionSucursales.length;
                document.getElementById('atlasPresReasignarConteo').textContent =
                    `${this.number(total)} sucursal${total === 1 ? '' : 'es'}`;
                this.renderSucursalesReasignacion();
                this.actualizarEstadoReasignacion();
            },

            renderSucursalesReasignacion() {
                const contenedor = document.getElementById('atlasPresReasignarSucursales');
                if (!this.reasignacionSucursales.length) {
                    contenedor.innerHTML = '<div class="atlas-pres-selection-empty">Selecciona una o varias sucursales.</div>';
                    return;
                }
                const totales = this.reasignacionSucursales.reduce((acc, item) => ({
                    creditos: acc.creditos + (Number(item.meta_creditos) || 0),
                    cash: acc.cash + (Number(item.meta_cash) || 0)
                }), { creditos: 0, cash: 0 });
                contenedor.innerHTML = `
                    <table class="table table-sm align-middle mb-0 atlas-pres-selection-table">
                        <thead><tr><th>PK</th><th>Sucursal</th><th>Responsable actual</th><th>Créditos</th><th>Cash</th><th>Base</th></tr></thead>
                        <tbody>${this.reasignacionSucursales.map(item => {
                            const responsable = this.responsableSucursal(item);
                            return `<tr>
                                <td><span class="atlas-pres-badge atlas-pres-badge-muted">${this.escape(item.fk_sucursal)}</span></td>
                                <td><strong>${this.escape(item.sucursal || 'Sin sucursal')}</strong></td>
                                <td>${this.escape(responsable.nombre)}</td>
                                <td>${this.number(item.meta_creditos || 0)}</td>
                                <td>${this.money(item.meta_cash || 0)}</td>
                                <td>${this.presupuestoBase(item.comisiona_a_partir_de)}</td>
                            </tr>`;
                        }).join('')}</tbody>
                    </table>
                    <div class="atlas-pres-selection-total">
                        <span>${this.number(totales.creditos)} créditos</span>
                        <span>${this.money(totales.cash)}</span>
                    </div>`;
            },

            actualizarEstadoReasignacion() {
                const destinoId = parseInt(document.getElementById('atlasPresReasignarDestino').value, 10);
                const motivo = document.getElementById('atlasPresReasignarMotivo')?.value.trim() || '';
                const tieneCambio = this.reasignacionSucursales.some(item =>
                    this.responsableSucursal(item).persona_id !== destinoId
                );
                const submit = document.querySelector('#atlasPresReasignarForm button[type="submit"]');
                if (submit) {
                    submit.disabled = !(
                        this.reasignacionSucursales.length
                        && destinoId > 0
                        && tieneCambio
                        && motivo.length >= 5
                    );
                }
            },

            async reasignarPresupuesto(ev) {
                ev.preventDefault();
                const form = ev.currentTarget;
                const submitBtn = form?.querySelector('button[type="submit"]');
                const motivo = document.getElementById('atlasPresReasignarMotivo').value.trim();
                const presupuestoId = parseInt(this.detalle?.presupuesto?.id, 10);
                const destinoId = parseInt(document.getElementById('atlasPresReasignarDestino').value, 10);
                const destino = this.responsablePorId(destinoId);
                const detalleIds = this.reasignacionSucursales.map(item => parseInt(item.detalle_id, 10));
                const tieneCambio = this.reasignacionSucursales.some(item =>
                    this.responsableSucursal(item).persona_id !== destinoId
                );
                if (!presupuestoId || !destino || !detalleIds.length || !tieneCambio || motivo.length < 5) {
                    showError('Selecciona las sucursales, un responsable distinto y captura el motivo.');
                    return;
                }
                const totalCash = this.reasignacionSucursales.reduce(
                    (total, item) => total + (Number(item.meta_cash) || 0),
                    0
                );
                const confirmado = typeof Swal === 'undefined'
                    ? window.confirm('¿Guardar esta reasignación de sucursales?')
                    : await Swal.fire({
                        icon: 'question',
                        title: 'Confirmar reasignación',
                        html: `<strong>${this.number(detalleIds.length)} sucursal${detalleIds.length === 1 ? '' : 'es'}</strong> pasarán a <strong>${this.escape(destino.nombre || '')}</strong> conservando <strong>${this.money(totalCash)}</strong>.`,
                        showCancelButton: true,
                        confirmButtonText: 'Reasignar sucursales',
                        cancelButtonText: 'Revisar'
                    }).then(result => !!result.isConfirmed);
                if (!confirmado) return;
                if (submitBtn) submitBtn.disabled = true;
                http.request({
                    endpoint: '/Atlas/reasignarPresupuesto',
                    metodo: 'POST',
                    data: JSON.stringify({
                        presupuesto_id: presupuestoId,
                        detalle_ids: detalleIds,
                        asesor_destino_persona_id: destinoId,
                        motivo
                    }),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: true,
                    onSuccess: (resp) => {
                        if (!resp || resp.success === false) {
                            showError(resp?.mensaje || 'No se pudo guardar la reasignación.');
                            return;
                        }
                        this.modalReasignar.hide();
                        showSuccess(resp.mensaje || 'Sucursales reasignadas.');
                        this.cargarDetalle(presupuestoId);
                        this.cargar();
                    },
                    onError: (mensaje) => showError(mensaje || 'No se pudo guardar la reasignación.'),
                    onAlways: () => this.actualizarEstadoReasignacion()
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
                document.getElementById('atlasPresEliminarResponsable').innerHTML = '';
                document.getElementById('atlasPresEliminarAsignaciones').innerHTML =
                    '<div class="atlas-pres-selection-empty">Consultando presupuesto...</div>';
                this.eliminacionOrigen = null;
                this.eliminacionDestino = null;
                document.getElementById('atlasPresEliminarConfirmar').disabled = true;
                this.cargarCatalogosReasignacion(() => {
                    this.eliminacionOrigen = this.sucursalCompleta(row.id);
                    if (!this.eliminacionOrigen) {
                        showError('La sucursal ya no se encuentra activa en el presupuesto.');
                        return;
                    }
                    this.poblarSelectSucursales('atlasPresEliminarDestino', row.id);
                    this.poblarSelectResponsables('atlasPresEliminarResponsable');
                    this.inicializarSelect2Presupuesto(
                        '#atlasPresEliminarDestino',
                        'Buscar sucursal destino',
                        '#modalAtlasPresupuestoEliminarSucursal',
                        false,
                        () => this.cambiarDestinoEliminacion()
                    );
                    this.inicializarSelect2Presupuesto(
                        '#atlasPresEliminarResponsable',
                        'Buscar responsable',
                        '#modalAtlasPresupuestoEliminarSucursal',
                        false,
                        () => this.actualizarEstadoEliminarSucursal()
                    );
                    document.getElementById('atlasPresEliminarSucursalSub').textContent =
                        `${this.detalle.presupuesto.nombre_mes} ${this.detalle.presupuesto.anio}`;
                    document.getElementById('atlasPresEliminarOrigenResumen').innerHTML = `
                        <div class="fw-bold">${this.escape(this.eliminacionOrigen.sucursal || '')}</div>
                        <div class="small">Se moverán ${this.number(this.eliminacionOrigen.meta_creditos || 0)} créditos y ${this.money(this.eliminacionOrigen.meta_cash || 0)} antes de eliminarla.</div>`;
                    this.renderResumenEliminacion();
                    this.modalEliminarSucursal.show();
                });
            },

            cambiarDestinoEliminacion() {
                const detalleId = parseInt(document.getElementById('atlasPresEliminarDestino').value, 10);
                this.eliminacionDestino = detalleId > 0 ? this.sucursalCompleta(detalleId) : null;
                if (!this.eliminacionDestino) {
                    this.seleccionarValores('#atlasPresEliminarResponsable', []);
                    this.renderResumenEliminacion();
                    this.actualizarEstadoEliminarSucursal();
                    return;
                }
                const responsable = this.responsableSucursal(this.eliminacionDestino);
                this.seleccionarValores(
                    '#atlasPresEliminarResponsable',
                    responsable.persona_id > 0 ? [responsable.persona_id] : []
                );
                this.renderResumenEliminacion();
                this.actualizarEstadoEliminarSucursal();
            },

            renderResumenEliminacion() {
                const contenedor = document.getElementById('atlasPresEliminarAsignaciones');
                if (!this.eliminacionOrigen || !this.eliminacionDestino) {
                    contenedor.innerHTML = '<div class="atlas-pres-selection-empty">Selecciona una sucursal destino.</div>';
                    return;
                }
                const totalCreditos = (Number(this.eliminacionOrigen.meta_creditos) || 0)
                    + (Number(this.eliminacionDestino.meta_creditos) || 0);
                const totalCash = (Number(this.eliminacionOrigen.meta_cash) || 0)
                    + (Number(this.eliminacionDestino.meta_cash) || 0);
                const responsableId = parseInt(document.getElementById('atlasPresEliminarResponsable').value, 10);
                const responsable = this.responsablePorId(responsableId);
                contenedor.innerHTML = `
                    <table class="table table-sm align-middle mb-0 atlas-pres-selection-table">
                        <thead><tr><th>Resultado</th><th>Sucursal</th><th>Responsable</th><th>Créditos</th><th>Cash</th><th>Base</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><span class="atlas-pres-badge atlas-pres-badge-info">Destino</span></td>
                                <td><strong>${this.escape(this.eliminacionDestino.sucursal || '')}</strong></td>
                                <td>${this.escape(responsable?.nombre || 'Selecciona responsable')}</td>
                                <td>${this.number(totalCreditos)}</td>
                                <td>${this.money(totalCash)}</td>
                                <td>${this.presupuestoBase(this.eliminacionDestino.comisiona_a_partir_de)}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="atlas-pres-selection-total">
                        <span>Se elimina FK ${this.escape(this.eliminacionOrigen.fk_sucursal)}</span>
                        <span>Se conserva el presupuesto base de la sucursal destino</span>
                    </div>`;
            },

            actualizarEstadoEliminarSucursal() {
                const motivo = document.getElementById('atlasPresEliminarMotivo')?.value.trim() || '';
                const responsableId = parseInt(document.getElementById('atlasPresEliminarResponsable').value, 10);
                const boton = document.getElementById('atlasPresEliminarConfirmar');
                if (this.eliminacionDestino) this.renderResumenEliminacion();
                if (boton) {
                    boton.disabled = !(
                        this.eliminacionOrigen
                        && this.eliminacionDestino
                        && responsableId > 0
                        && motivo.length >= 5
                    );
                }
            },

            async eliminarSucursal(ev) {
                ev.preventDefault();
                const presupuestoId = parseInt(this.detalle?.presupuesto?.id, 10);
                const motivo = document.getElementById('atlasPresEliminarMotivo').value.trim();
                const responsableId = parseInt(document.getElementById('atlasPresEliminarResponsable').value, 10);
                const responsable = this.responsablePorId(responsableId);
                const totalCreditos = (Number(this.eliminacionOrigen?.meta_creditos) || 0)
                    + (Number(this.eliminacionDestino?.meta_creditos) || 0);
                const totalCash = (Number(this.eliminacionOrigen?.meta_cash) || 0)
                    + (Number(this.eliminacionDestino?.meta_cash) || 0);
                if (!presupuestoId || !this.eliminacionOrigen || !this.eliminacionDestino || !responsable || motivo.length < 5) {
                    showError('Selecciona la sucursal destino, su responsable y captura el motivo.');
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
                                <div><strong>Responsable:</strong> ${this.escape(responsable.nombre || '')}</div>
                                <div><strong>Total conservado:</strong> ${this.money(totalCash)}</div>
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
                        asignaciones_destino: [{
                            persona_id: responsableId,
                            meta_creditos: totalCreditos.toFixed(2),
                            meta_cash: totalCash.toFixed(2)
                        }],
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
                    this.disposeTooltips(selector);
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
                    drawCallback: () => {
                        this.repararPaginacion(selector);
                        this.activarTooltips(selector);
                    }
                });
                this.repararPaginacion(selector, tabla);
                this.activarTooltips(selector);
            },

            disposeTooltips(scopeSelector) {
                if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
                const scope = scopeSelector ? document.querySelector(scopeSelector) : document;
                if (!scope) return;
                scope.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
                    const instance = bootstrap.Tooltip.getInstance(el);
                    if (instance) instance.dispose();
                });
            },

            activarTooltips(scopeSelector) {
                if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
                const scope = scopeSelector ? document.querySelector(scopeSelector) : document;
                if (!scope) return;
                scope.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
                    bootstrap.Tooltip.getOrCreateInstance(el, {
                        container: 'body',
                        trigger: 'hover focus',
                        html: el.getAttribute('data-bs-html') === 'true'
                    });
                });
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

            presupuestoBase(valor) {
                if (valor === null || valor === undefined || valor === '') return 'Sin dato';
                const numero = Number(valor);
                if (!Number.isFinite(numero)) return this.escape(String(valor));
                return this.number(numero);
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
                    reajuste_masivo: 'Reajuste masivo confirmado',
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
                if (evento === 'reajuste_masivo') return 'fa-solid fa-code-compare';
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
                document.getElementById('atlasPresEditTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Editar presupuesto de sucursal';
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
                document.getElementById('atlasPresEditBase').value = row.comisiona_a_partir_de ?? '';
                document.getElementById('atlasPresEditSub').textContent = `${this.detalle.presupuesto.nombre_mes} ${this.detalle.presupuesto.anio}`;
                this.modalEdit.show();
            },

            relojAnalisis() {
                return window.performance && typeof window.performance.now === 'function'
                    ? window.performance.now()
                    : Date.now();
            },

            estimarDuracionAnalisis(archivo) {
                const megabytes = Math.max(0, Number(archivo?.size || 0) / (1024 * 1024));
                const estimadoPorTamano = 15000 + Math.min(45000, megabytes * 6000);
                let duracionAnterior = 0;
                try {
                    duracionAnterior = Number(window.localStorage.getItem(this.analisisDuracionStorageKey) || 0);
                } catch (err) {
                    duracionAnterior = 0;
                }
                const estimado = duracionAnterior >= 3000 && duracionAnterior <= 120000
                    ? (duracionAnterior * .65) + (estimadoPorTamano * .35)
                    : estimadoPorTamano;
                return Math.round(Math.min(90000, Math.max(12000, estimado)) / 1000) * 1000;
            },

            guardarDuracionAnalisis(duracionMs) {
                if (!Number.isFinite(duracionMs) || duracionMs < 1000 || duracionMs > 120000) return;
                try {
                    window.localStorage.setItem(this.analisisDuracionStorageKey, String(Math.round(duracionMs)));
                } catch (err) {
                    // El estimado puede funcionar sin almacenamiento local.
                }
            },

            mostrarProgresoAnalisis(archivo) {
                const estimadoMs = this.estimarDuracionAnalisis(archivo);
                this.analisisProgreso = {
                    inicio: this.relojAnalisis(),
                    estimadoMs,
                    timer: null
                };
                if (typeof Swal === 'undefined') return;

                Swal.fire({
                    title: '',
                    html: `
                        <div class="atlas-pres-analysis">
                            <div class="atlas-pres-analysis-head">
                                <span class="atlas-pres-analysis-icon"><i class="fa-solid fa-file-excel"></i></span>
                                <div class="min-w-0">
                                    <div class="atlas-pres-analysis-title">Revisando presupuesto</div>
                                    <div class="atlas-pres-analysis-file">${this.escape(archivo?.name || 'Archivo Excel')}</div>
                                </div>
                            </div>
                            <div class="atlas-pres-analysis-status" id="atlasPresAnalisisEstado" role="status" aria-live="polite">
                                Estamos cargando y preparando el archivo...
                            </div>
                            <div class="progress atlas-pres-analysis-progress" role="progressbar" aria-label="Avance estimado del análisis" aria-valuemin="0" aria-valuemax="100" aria-valuenow="6" id="atlasPresAnalisisProgreso">
                                <div class="progress-bar" id="atlasPresAnalisisBarra" style="width:6%"></div>
                            </div>
                            <div class="atlas-pres-analysis-meta">
                                <span id="atlasPresAnalisisEstimado">Tiempo estimado: ${Math.ceil(estimadoMs / 1000)} s</span>
                                <span id="atlasPresAnalisisTranscurrido">Transcurrido: 0 s</span>
                            </div>
                        </div>`,
                    customClass: { popup: 'atlas-pres-analysis-popup' },
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        this.actualizarProgresoAnalisis();
                        if (this.analisisProgreso) {
                            this.analisisProgreso.timer = window.setInterval(() => this.actualizarProgresoAnalisis(), 500);
                        }
                    }
                });
            },

            actualizarProgresoAnalisis() {
                const progreso = this.analisisProgreso;
                if (!progreso) return;
                const transcurridoMs = Math.max(0, this.relojAnalisis() - progreso.inicio);
                const proporcion = transcurridoMs / progreso.estimadoMs;
                const porcentaje = proporcion <= 1
                    ? Math.min(86, 6 + (80 * proporcion))
                    : Math.min(94, 86 + ((proporcion - 1) * 8));
                let mensaje = 'Estamos cargando y preparando el archivo...';
                if (proporcion >= .15) mensaje = 'Estamos revisando las sucursales y sus datos...';
                if (proporcion >= .4) mensaje = 'Estamos validando responsables y presupuestos...';
                if (proporcion >= .7) mensaje = 'Estamos comparando los cambios contra el presupuesto actual...';
                if (proporcion >= .95) mensaje = 'Ya falta poco, estamos preparando el comparativo...';
                if (proporcion >= 1.25) mensaje = 'El archivo es grande; seguimos revisándolo de forma segura...';

                const barra = document.getElementById('atlasPresAnalisisBarra');
                const contenedor = document.getElementById('atlasPresAnalisisProgreso');
                const estado = document.getElementById('atlasPresAnalisisEstado');
                const transcurrido = document.getElementById('atlasPresAnalisisTranscurrido');
                if (barra) barra.style.width = `${porcentaje.toFixed(1)}%`;
                if (contenedor) contenedor.setAttribute('aria-valuenow', String(Math.round(porcentaje)));
                if (estado) estado.textContent = mensaje;
                if (transcurrido) transcurrido.textContent = `Transcurrido: ${Math.floor(transcurridoMs / 1000)} s`;
            },

            detenerProgresoAnalisis() {
                const progreso = this.analisisProgreso;
                if (!progreso) return 0;
                if (progreso.timer) window.clearInterval(progreso.timer);
                progreso.timer = null;
                return Math.max(0, this.relojAnalisis() - progreso.inicio);
            },

            completarProgresoAnalisis(duracionMs) {
                this.detenerProgresoAnalisis();
                this.guardarDuracionAnalisis(duracionMs);
                const barra = document.getElementById('atlasPresAnalisisBarra');
                const contenedor = document.getElementById('atlasPresAnalisisProgreso');
                const estado = document.getElementById('atlasPresAnalisisEstado');
                const transcurrido = document.getElementById('atlasPresAnalisisTranscurrido');
                if (barra) barra.style.width = '100%';
                if (contenedor) contenedor.setAttribute('aria-valuenow', '100');
                if (estado) estado.textContent = 'Comparativo listo. Estamos abriendo la revisión...';
                if (transcurrido) transcurrido.textContent = `Completado en ${Math.max(1, Math.round(duracionMs / 1000))} s`;
                return new Promise(resolve => window.setTimeout(resolve, 250));
            },

            cancelarProgresoAnalisis() {
                this.detenerProgresoAnalisis();
                this.analisisProgreso = null;
                if (typeof Swal !== 'undefined') Swal.close();
            },

            formatearDuracionProceso(duracionMs) {
                const segundos = Math.max(0, Math.round(Number(duracionMs || 0) / 1000));
                if (segundos < 60) return `${segundos} s`;
                const minutos = Math.floor(segundos / 60);
                const segundosRestantes = segundos % 60;
                return segundosRestantes > 0
                    ? `${minutos} min ${segundosRestantes} s`
                    : `${minutos} min`;
            },

            estimarDuracionConfirmacion(analisis) {
                const resumen = analisis?.resumen || {};
                const filas = Math.max(1, Number(resumen.sucursales_archivo || resumen.filas_leidas || 0));
                const cambios = Math.max(1, Number(resumen.cambios || 0));
                const estimadoPorVolumen = 30000 + Math.min(70000, (filas * 20) + (cambios * 120));
                let duracionAnterior = 0;
                try {
                    duracionAnterior = Number(window.localStorage.getItem(this.confirmacionDuracionStorageKey) || 0);
                } catch (err) {
                    duracionAnterior = 0;
                }
                const estimado = duracionAnterior >= 5000 && duracionAnterior <= 180000
                    ? (duracionAnterior * .7) + (estimadoPorVolumen * .3)
                    : estimadoPorVolumen;
                return Math.round(Math.min(150000, Math.max(20000, estimado)) / 1000) * 1000;
            },

            guardarDuracionConfirmacion(duracionMs) {
                if (!Number.isFinite(duracionMs) || duracionMs < 1000 || duracionMs > 180000) return;
                try {
                    window.localStorage.setItem(this.confirmacionDuracionStorageKey, String(Math.round(duracionMs)));
                } catch (err) {
                    // La confirmacion conserva su estimado por volumen si no hay almacenamiento local.
                }
            },

            mostrarProgresoConfirmacion(analisis) {
                const estimadoMs = this.estimarDuracionConfirmacion(analisis);
                const totalCambios = Number(analisis?.resumen?.cambios || 0);
                this.confirmacionProgreso = {
                    inicio: this.relojAnalisis(),
                    estimadoMs,
                    timer: null
                };
                if (typeof Swal === 'undefined') return;

                Swal.fire({
                    title: '',
                    html: `
                        <div class="atlas-pres-analysis">
                            <div class="atlas-pres-analysis-head">
                                <span class="atlas-pres-analysis-icon"><i class="fa-solid fa-arrows-rotate"></i></span>
                                <div class="min-w-0">
                                    <div class="atlas-pres-analysis-title">Aplicando reajuste</div>
                                    <div class="atlas-pres-analysis-file">${this.escape(analisis?.nombre_mes || '')} ${this.escape(analisis?.anio || '')} · ${this.number(totalCambios)} cambio${totalCambios === 1 ? '' : 's'}</div>
                                </div>
                            </div>
                            <div class="atlas-pres-analysis-status" id="atlasPresConfirmacionEstado" role="status" aria-live="polite">
                                Estamos preparando la actualización del presupuesto...
                            </div>
                            <div class="progress atlas-pres-analysis-progress" role="progressbar" aria-label="Avance estimado del reajuste" aria-valuemin="0" aria-valuemax="100" aria-valuenow="5" id="atlasPresConfirmacionProgreso">
                                <div class="progress-bar" id="atlasPresConfirmacionBarra" style="width:5%"></div>
                            </div>
                            <div class="atlas-pres-analysis-meta">
                                <span id="atlasPresConfirmacionEstimado">Tiempo estimado: cerca de ${this.formatearDuracionProceso(estimadoMs)}</span>
                                <span id="atlasPresConfirmacionTranscurrido">Transcurrido: 0 s</span>
                            </div>
                        </div>`,
                    customClass: { popup: 'atlas-pres-analysis-popup' },
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        this.actualizarProgresoConfirmacion();
                        if (this.confirmacionProgreso) {
                            this.confirmacionProgreso.timer = window.setInterval(() => this.actualizarProgresoConfirmacion(), 500);
                        }
                    }
                });
            },

            actualizarProgresoConfirmacion() {
                const progreso = this.confirmacionProgreso;
                if (!progreso) return;
                const transcurridoMs = Math.max(0, this.relojAnalisis() - progreso.inicio);
                const proporcion = transcurridoMs / progreso.estimadoMs;
                const porcentaje = proporcion <= 1
                    ? Math.min(88, 5 + (83 * proporcion))
                    : Math.min(96, 88 + ((proporcion - 1) * 5));
                let mensaje = 'Estamos preparando la actualización del presupuesto...';
                if (proporcion >= .12) mensaje = 'Estamos verificando que el presupuesto siga listo para actualizarse...';
                if (proporcion >= .32) mensaje = 'Estamos aplicando responsables, metas y sucursales...';
                if (proporcion >= .58) mensaje = 'Estamos recalculando los totales del presupuesto...';
                if (proporcion >= .82) mensaje = 'Ya falta poco, estamos registrando el reajuste...';
                if (proporcion >= 1.1) mensaje = 'El reajuste es amplio y sigue procesándose de forma segura...';
                if (proporcion >= 1.6) mensaje = 'Está tomando más de lo habitual; no cierres esta ventana...';

                const barra = document.getElementById('atlasPresConfirmacionBarra');
                const contenedor = document.getElementById('atlasPresConfirmacionProgreso');
                const estado = document.getElementById('atlasPresConfirmacionEstado');
                const estimado = document.getElementById('atlasPresConfirmacionEstimado');
                const transcurrido = document.getElementById('atlasPresConfirmacionTranscurrido');
                if (barra) barra.style.width = `${porcentaje.toFixed(1)}%`;
                if (contenedor) contenedor.setAttribute('aria-valuenow', String(Math.round(porcentaje)));
                if (estado) estado.textContent = mensaje;
                if (estimado && proporcion > 1) estimado.textContent = 'La operación continúa en curso';
                if (transcurrido) transcurrido.textContent = `Transcurrido: ${this.formatearDuracionProceso(transcurridoMs)}`;
            },

            detenerProgresoConfirmacion() {
                const progreso = this.confirmacionProgreso;
                if (!progreso) return 0;
                if (progreso.timer) window.clearInterval(progreso.timer);
                progreso.timer = null;
                return Math.max(0, this.relojAnalisis() - progreso.inicio);
            },

            completarProgresoConfirmacion(duracionMs) {
                this.detenerProgresoConfirmacion();
                this.guardarDuracionConfirmacion(duracionMs);
                const barra = document.getElementById('atlasPresConfirmacionBarra');
                const contenedor = document.getElementById('atlasPresConfirmacionProgreso');
                const estado = document.getElementById('atlasPresConfirmacionEstado');
                const estimado = document.getElementById('atlasPresConfirmacionEstimado');
                const transcurrido = document.getElementById('atlasPresConfirmacionTranscurrido');
                if (barra) barra.style.width = '100%';
                if (contenedor) contenedor.setAttribute('aria-valuenow', '100');
                if (estado) estado.textContent = 'Reajuste aplicado. Estamos preparando el resultado...';
                if (estimado) estimado.textContent = 'Actualización completada';
                if (transcurrido) transcurrido.textContent = `Completado en ${this.formatearDuracionProceso(duracionMs)}`;
                return new Promise(resolve => window.setTimeout(resolve, 300));
            },

            cancelarProgresoConfirmacion() {
                this.detenerProgresoConfirmacion();
                this.confirmacionProgreso = null;
                if (typeof Swal !== 'undefined') Swal.close();
            },

            importar(ev) {
                ev.preventDefault();
                const form = ev.currentTarget;
                const submitBtn = form.querySelector('button[type="submit"]');
                const submitBtnHtml = submitBtn?.innerHTML || '';
                const archivo = form.querySelector('input[name="archivo"]')?.files?.[0] || null;
                const anioImportacion = parseInt(document.getElementById('atlasPresImportAnio').value, 10);
                const mesImportacion = parseInt(document.getElementById('atlasPresImportMes').value, 10);
                if (!anioImportacion || !mesImportacion) {
                    showError('Selecciona un mes disponible para cargar.');
                    return;
                }
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Analizando...';
                }
                const fd = new FormData(form);
                let loaderImportacionActivo = true;
                this.mostrarProgresoAnalisis(archivo);
                http.request({
                    endpoint: '/Atlas/importarPresupuesto',
                    metodo: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    showLoader: false,
                    timeout: 120000,
                    retry: 0,
                    onSuccess: (resp) => {
                        const duracionMs = this.detenerProgresoAnalisis();
                        if (!resp || resp.success === false) {
                            loaderImportacionActivo = false;
                            this.cancelarProgresoAnalisis();
                            showError(resp?.mensaje || 'No se pudo importar el presupuesto.');
                            return;
                        }
                        loaderImportacionActivo = false;
                        const datosImportacion = resp.datos || {};
                        form.reset();
                        document.getElementById('atlasPresImportAnio').value = document.getElementById('atlasPresAnio').value;
                        const cierreImportacion = this.cerrarModalImportacion();
                        Promise.all([
                            cierreImportacion,
                            this.completarProgresoAnalisis(duracionMs)
                        ]).then(() => {
                            this.analisisProgreso = null;
                            if (typeof Swal !== 'undefined') Swal.close();
                            this.abrirComparativoReajuste(datosImportacion, resp.mensaje || '');
                        });
                    },
                    onError: (mensaje) => {
                        loaderImportacionActivo = false;
                        this.cancelarProgresoAnalisis();
                        showError(mensaje || 'No se pudo importar el presupuesto.');
                    },
                    onAlways: () => {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = submitBtnHtml;
                        }
                        if (loaderImportacionActivo && typeof Swal !== 'undefined') {
                            this.cancelarProgresoAnalisis();
                        }
                    }
                });
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

            abrirComparativoReajuste(datos, mensaje) {
                datos = datos && typeof datos === 'object' ? datos : {};
                this.reajusteAnalisis = datos;
                const resumen = datos.resumen || {};
                const puedeConfirmar = Number(datos.puede_confirmar || 0) === 1;
                const cambios = Array.isArray(datos.comparativo) ? datos.comparativo : [];
                const bloqueos = Array.isArray(datos.bloqueos) ? datos.bloqueos : [];
                const advertencias = Array.isArray(datos.advertencias) ? datos.advertencias : [];
                const motivo = document.getElementById('atlasPresComparativoMotivo');
                motivo.value = '';
                motivo.disabled = false;

                document.getElementById('atlasPresComparativoSub').textContent =
                    `${datos.nombre_mes || this.nombreMes(datos.mes)} ${datos.anio || ''}`
                    + (datos.archivo_original ? ` · ${datos.archivo_original}` : '');
                document.getElementById('atlasPresComparativoVigencia').textContent =
                    `Comparativo disponible por ${Math.round(Number(datos.expira_en_segundos || 1800) / 60)} minutos`;
                document.getElementById('atlasPresComparativoConteo').textContent =
                    `${this.number(cambios.length)} cambio${cambios.length === 1 ? '' : 's'}`;
                this.comparativoCambios = cambios;
                this.comparativoPagina = 1;

                const estado = document.getElementById('atlasPresComparativoEstado');
                estado.className = `alert d-flex align-items-start gap-2 mb-3 ${bloqueos.length ? 'alert-danger' : 'alert-info'}`;
                document.getElementById('atlasPresComparativoMensaje').textContent =
                    mensaje || (puedeConfirmar
                        ? 'El comparativo está listo para revisión.'
                        : (cambios.length
                            ? 'Corrige las observaciones indicadas y vuelve a analizar el archivo.'
                            : 'El archivo no contiene cambios frente al presupuesto actual.'));

                document.getElementById('atlasPresComparativoResumen').innerHTML = `
                    <div class="atlas-pres-adjust-metric">
                        <span>Sucursales con cambio</span>
                        <strong>${this.number(resumen.cambios || 0)}</strong>
                    </div>
                    <div class="atlas-pres-adjust-metric is-green">
                        <span>Altas nuevas</span>
                        <strong>${this.number(resumen.altas || 0)}</strong>
                    </div>
                    <div class="atlas-pres-adjust-metric is-violet">
                        <span>Ajustes de presupuesto</span>
                        <strong>${this.number(resumen.ajustes_presupuesto || 0)}</strong>
                    </div>
                    <div class="atlas-pres-adjust-metric is-slate">
                        <span>Reasignaciones</span>
                        <strong>${this.number(resumen.reasignaciones || 0)}</strong>
                    </div>
                `;
                this.renderTotalesComparativoReajuste(datos);
                document.getElementById('atlasPresComparativoBody').innerHTML =
                    '<tr><td colspan="8" class="atlas-pres-empty">Preparando la primera página...</td></tr>';
                this.renderPaginacionComparativoReajuste(cambios.length);
                this.renderBloqueosComparativoReajuste(datos);
                this.actualizarEstadoConfirmacionReajuste();
                this.modalComparativo.show();
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => this.renderFilasComparativoReajuste());
                });
            },

            renderTotalesComparativoReajuste(datos) {
                const antes = datos.totales_antes || {};
                const despues = datos.totales_despues || {};
                const delta = datos.totales_delta || {};
                const filas = [
                    ['Sucursales', antes.sucursales, despues.sucursales, delta.sucursales, value => this.number(value || 0)],
                    ['Presupuesto de créditos', antes.creditos, despues.creditos, delta.creditos, value => this.number(value || 0)],
                    ['Presupuesto de cash', antes.cash, despues.cash, delta.cash, value => this.money(value || 0)]
                ];
                document.getElementById('atlasPresComparativoTotales').innerHTML = filas.map(([label, valorAntes, valorDespues, diferencia, formato]) => `
                    <tr>
                        <td><strong>${this.escape(label)}</strong></td>
                        <td class="text-end">${formato(valorAntes)}</td>
                        <td class="text-end fw-bold">${formato(valorDespues)}</td>
                        <td class="text-end">${this.renderDeltaReajuste(diferencia, formato)}</td>
                    </tr>
                `).join('');
            },

            renderDeltaReajuste(valor, formato) {
                const numero = Number(valor) || 0;
                const clase = numero > 0 ? 'text-success' : (numero < 0 ? 'text-danger' : 'text-muted');
                const prefijo = numero > 0 ? '+' : '';
                const absolutoFormateado = formato(Math.abs(numero));
                return `<strong class="${clase}">${prefijo}${numero < 0 ? '-' : ''}${absolutoFormateado}</strong>`;
            },

            renderFilasComparativoReajuste(cambios = null) {
                if (Array.isArray(cambios)) {
                    this.comparativoCambios = cambios;
                    this.comparativoPagina = 1;
                }
                const registros = Array.isArray(this.comparativoCambios) ? this.comparativoCambios : [];
                const body = document.getElementById('atlasPresComparativoBody');
                const totalPaginas = Math.max(1, Math.ceil(registros.length / this.comparativoPorPagina));
                this.comparativoPagina = Math.min(totalPaginas, Math.max(1, this.comparativoPagina));
                const inicio = (this.comparativoPagina - 1) * this.comparativoPorPagina;
                const visibles = registros.slice(inicio, inicio + this.comparativoPorPagina);
                if (!registros.length) {
                    body.innerHTML = '<tr><td colspan="8" class="atlas-pres-empty">No hay diferencias por aplicar.</td></tr>';
                    this.renderPaginacionComparativoReajuste(0);
                    return;
                }
                body.innerHTML = visibles.map(item => {
                    const antes = item.antes || null;
                    const despues = item.despues || {};
                    const campos = Array.isArray(item.campos) ? item.campos : [];
                    return `
                        <tr>
                            <td><span class="atlas-pres-badge atlas-pres-badge-muted">${this.escape(item.fk_sucursal || '')}</span></td>
                            <td>${this.renderSucursalComparativoReajuste(item, campos)}</td>
                            <td>${this.renderValorComparativoReajuste(
                                antes?.responsable,
                                despues.responsable,
                                campos.includes('responsable') || campos.includes('alta'),
                                value => this.escape(value || 'Sin responsable'),
                                !!antes
                            )}</td>
                            <td>${this.renderValorComparativoReajuste(
                                antes?.creditos,
                                despues.creditos,
                                campos.includes('creditos') || campos.includes('alta'),
                                value => this.number(value || 0),
                                !!antes
                            )}</td>
                            <td>${this.renderValorComparativoReajuste(
                                antes?.cash,
                                despues.cash,
                                campos.includes('cash') || campos.includes('alta'),
                                value => this.money(value || 0),
                                !!antes
                            )}</td>
                            <td>${this.renderValorComparativoReajuste(
                                antes?.presupuesto_base,
                                despues.presupuesto_base,
                                campos.includes('presupuesto_base') || campos.includes('alta'),
                                value => this.presupuestoBase(value),
                                !!antes
                            )}</td>
                            <td>${this.renderValorComparativoReajuste(
                                antes?.clasificacion,
                                despues.clasificacion,
                                campos.includes('clasificacion') || campos.includes('alta'),
                                value => this.escape(value || 'Sin clasificación'),
                                !!antes
                            )}</td>
                            <td><div class="atlas-pres-adjust-field-list">${campos.map(campo => this.badgeCampoReajuste(campo)).join('')}</div></td>
                        </tr>
                    `;
                }).join('');
                this.renderPaginacionComparativoReajuste(registros.length);
            },

            renderPaginacionComparativoReajuste(totalRegistros) {
                const total = Math.max(0, Number(totalRegistros) || 0);
                const totalPaginas = Math.max(1, Math.ceil(total / this.comparativoPorPagina));
                this.comparativoPagina = Math.min(totalPaginas, Math.max(1, this.comparativoPagina));
                const inicio = total ? ((this.comparativoPagina - 1) * this.comparativoPorPagina) + 1 : 0;
                const fin = total ? Math.min(total, this.comparativoPagina * this.comparativoPorPagina) : 0;
                const select = document.getElementById('atlasPresComparativoPagina');
                const anterior = document.getElementById('atlasPresComparativoAnterior');
                const siguiente = document.getElementById('atlasPresComparativoSiguiente');
                document.getElementById('atlasPresComparativoRango').textContent = total
                    ? `Mostrando ${this.number(inicio)} a ${this.number(fin)} de ${this.number(total)} cambios`
                    : 'Sin cambios para mostrar';
                document.getElementById('atlasPresComparativoPaginas').textContent = `de ${this.number(totalPaginas)}`;
                select.innerHTML = Array.from({ length: totalPaginas }, (_, index) => {
                    const pagina = index + 1;
                    return `<option value="${pagina}"${pagina === this.comparativoPagina ? ' selected' : ''}>${pagina}</option>`;
                }).join('');
                select.disabled = totalPaginas <= 1;
                anterior.disabled = this.comparativoPagina <= 1;
                siguiente.disabled = this.comparativoPagina >= totalPaginas;
            },

            cambiarPaginaComparativoReajuste(direccion) {
                const totalPaginas = Math.max(1, Math.ceil(this.comparativoCambios.length / this.comparativoPorPagina));
                const pagina = Math.min(totalPaginas, Math.max(1, this.comparativoPagina + direccion));
                if (pagina === this.comparativoPagina) return;
                this.comparativoPagina = pagina;
                this.renderFilasComparativoReajuste();
            },

            renderSucursalComparativoReajuste(item, campos) {
                const antes = item.antes || null;
                const despues = item.despues || {};
                if (antes && campos.includes('datos_sucursal')) {
                    return `
                        <div class="atlas-pres-adjust-before">Antes: ${this.escape(antes.sucursal || 'Sin sucursal')} · ${this.escape(antes.distribuidor || 'Sin distribuidor')}</div>
                        <div class="atlas-pres-main mt-1"><i class="fa-solid fa-arrow-right text-primary me-1"></i>${this.escape(despues.sucursal || item.sucursal || 'Sin sucursal')}</div>
                        <div class="atlas-pres-sub">${this.escape(despues.distribuidor || item.distribuidor || 'Sin distribuidor')}</div>
                    `;
                }
                return `
                    <div class="atlas-pres-main">${this.escape(despues.sucursal || item.sucursal || 'Sin sucursal')}</div>
                    <div class="atlas-pres-sub">${this.escape(despues.distribuidor || item.distribuidor || 'Sin distribuidor')}</div>
                `;
            },

            renderValorComparativoReajuste(antes, despues, cambio, formato, tieneRegistroAnterior) {
                const htmlAntes = tieneRegistroAnterior ? formato(antes) : 'Sin registro';
                const htmlDespues = formato(despues);
                return `
                    <div class="atlas-pres-adjust-change">
                        <div class="atlas-pres-adjust-before">Antes: ${htmlAntes}</div>
                        <div class="atlas-pres-adjust-after">${cambio ? '<i class="fa-solid fa-arrow-right"></i>' : ''}${htmlDespues}</div>
                    </div>
                `;
            },

            badgeCampoReajuste(campo) {
                const etiquetas = {
                    alta: 'Alta nueva',
                    responsable: 'Responsable',
                    creditos: 'Créditos',
                    cash: 'Cash',
                    presupuesto_base: 'Base',
                    clasificacion: 'Clasificación',
                    datos_sucursal: 'Datos de sucursal'
                };
                const clase = campo === 'alta' ? 'atlas-pres-badge-ok' : 'atlas-pres-badge-info';
                return `<span class="atlas-pres-badge ${clase}">${this.escape(etiquetas[campo] || campo)}</span>`;
            },

            renderBloqueosComparativoReajuste(datos) {
                const contenedor = document.getElementById('atlasPresComparativoBloqueos');
                const bloqueos = Array.isArray(datos.bloqueos) ? datos.bloqueos : [];
                const advertencias = Array.isArray(datos.advertencias) ? datos.advertencias : [];
                if (!bloqueos.length && !advertencias.length) {
                    contenedor.innerHTML = `
                        <div class="alert alert-info d-flex align-items-start gap-2 mb-0">
                            <i class="fa-solid fa-circle-check mt-1"></i>
                            <div>El archivo incluye todas las sucursales del presupuesto mensual y está listo para confirmar.</div>
                        </div>`;
                    return;
                }
                const detalles = this.detallesBloqueoReajuste(datos);
                const soloAdvertencias = !bloqueos.length;
                const observaciones = soloAdvertencias ? advertencias : bloqueos;
                contenedor.innerHTML = `
                    <div class="alert ${soloAdvertencias ? 'alert-info' : 'alert-danger'} mb-0">
                        <div class="fw-bold mb-2"><i class="fa-solid ${soloAdvertencias ? 'fa-circle-info' : 'fa-triangle-exclamation'} me-2"></i>${soloAdvertencias ? 'Observaciones del archivo' : 'Observaciones por corregir'}</div>
                        <div class="atlas-pres-adjust-blockers">
                            ${observaciones.map(item => `
                                <div class="atlas-pres-adjust-blocker">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <div><strong>${this.number(item.cantidad || 0)}</strong> · ${this.escape(item.mensaje || '')}</div>
                                </div>
                            `).join('')}
                        </div>
                        ${detalles ? `<div class="small fw-semibold mt-2">${detalles}</div>` : ''}
                        ${soloAdvertencias ? '<div class="small fw-bold mt-2">Puedes confirmar el reajuste: se aplicaran solamente los cambios validos indicados en el comparativo.</div>' : ''}
                    </div>`;
            },

            detallesBloqueoReajuste(datos) {
                const partes = [];
                const resumir = (items, formatear) => (Array.isArray(items) ? items : [])
                    .slice(0, 8)
                    .map(formatear)
                    .filter(Boolean)
                    .join(', ');
                const faltantes = resumir(datos.detalle_faltantes, item => `PK ${item?.fk_sucursal || ''}`);
                const extras = resumir(datos.detalle_extras, item => `fila ${item?.fila || '-'} · PK ${item?.fk_sucursal || ''}`);
                const duplicadas = resumir(datos.detalle_duplicadas, item => `fila ${item?.fila || '-'} · PK ${item?.fk_sucursal || ''}`);
                const responsables = resumir(datos.detalle_errores_asignacion, item => `fila ${item?.fila || '-'} · ${item?.asesor_excel || 'sin responsable'}`);
                const clasificaciones = resumir(datos.detalle_errores_clasificacion, item => `fila ${item?.fila || '-'} · ${item?.clasificacion_excel || 'sin clasificación'}`);
                const presupuestos = resumir(datos.detalle_errores_presupuesto, item => `fila ${item?.fila || '-'} · PK ${item?.fk_sucursal || ''}`);
                const operacion = resumir(datos.detalle_errores_operacion, item => `fila ${item?.fila || '-'} · PK ${item?.fk_sucursal || ''}`);
                const invalidas = resumir(datos.detalle_filas_invalidas, item => `fila ${item?.fila || '-'} · ${item?.pk_sucursal || 'sin PK'}`);
                if (faltantes) partes.push(`Faltantes del presupuesto mensual: ${faltantes}.`);
                if (extras) partes.push(`PK nuevas para el presupuesto: ${extras}.`);
                if (duplicadas) partes.push(`Duplicadas: ${duplicadas}.`);
                if (responsables) partes.push(`Responsables: ${responsables}.`);
                if (clasificaciones) partes.push(`Clasificaciones: ${clasificaciones}.`);
                if (presupuestos) partes.push(`Presupuestos inválidos: ${presupuestos}.`);
                if (operacion) partes.push(`Estado operativo: ${operacion}.`);
                if (invalidas) partes.push(`Filas inválidas: ${invalidas}.`);
                return partes.map(parte => this.escape(parte)).join('<br>');
            },

            actualizarEstadoConfirmacionReajuste() {
                const boton = document.getElementById('atlasPresComparativoConfirmar');
                const motivo = document.getElementById('atlasPresComparativoMotivo')?.value.trim() || '';
                const puedeConfirmar = Number(this.reajusteAnalisis?.puede_confirmar || 0) === 1;
                const tieneToken = !!this.reajusteAnalisis?.reajuste_token;
                if (boton) boton.disabled = !(puedeConfirmar && tieneToken && motivo.length >= 5);
            },

            async confirmarReajusteMasivo(ev) {
                ev.preventDefault();
                const analisis = this.reajusteAnalisis || {};
                const motivo = document.getElementById('atlasPresComparativoMotivo').value.trim();
                if (Number(analisis.puede_confirmar || 0) !== 1 || !analisis.reajuste_token || motivo.length < 5) {
                    showError('Revisa el comparativo y captura el motivo del reajuste.');
                    return;
                }
                const totalCambios = Number(analisis.resumen?.cambios || 0);
                const confirmado = typeof Swal === 'undefined'
                    ? window.confirm(`¿Aplicar ${totalCambios} cambio(s) al presupuesto?`)
                    : await Swal.fire({
                        icon: 'question',
                        title: 'Confirmar reajuste',
                        html: `Se aplicarán <strong>${this.number(totalCambios)} cambio${totalCambios === 1 ? '' : 's'}</strong> al presupuesto de <strong>${this.escape(analisis.nombre_mes || '')} ${this.escape(analisis.anio || '')}</strong>.`,
                        showCancelButton: true,
                        confirmButtonText: 'Confirmar reajuste',
                        cancelButtonText: 'Seguir revisando'
                    }).then(result => !!result.isConfirmed);
                if (!confirmado) return;

                const boton = document.getElementById('atlasPresComparativoConfirmar');
                if (boton) boton.disabled = true;
                this.mostrarProgresoConfirmacion(analisis);
                http.request({
                    endpoint: '/Atlas/confirmarReajustePresupuesto',
                    metodo: 'POST',
                    data: JSON.stringify({
                        token: analisis.reajuste_token,
                        motivo
                    }),
                    contentType: 'application/json; charset=UTF-8',
                    processData: false,
                    showLoader: false,
                    timeout: 120000,
                    retry: 0,
                    onSuccess: (resp) => {
                        const duracionMs = this.detenerProgresoConfirmacion();
                        if (!resp || resp.success === false) {
                            this.cancelarProgresoConfirmacion();
                            showError(resp?.mensaje || 'No se pudo aplicar el reajuste.');
                            if ([409, 410].includes(Number(resp?.status || 0))) {
                                this.reajusteAnalisis = null;
                                document.getElementById('atlasPresComparativoMotivo').disabled = true;
                            }
                            return;
                        }
                        const resultado = resp.datos || {};
                        const presupuestoId = parseInt(resultado.presupuesto_id, 10);
                        this.calendariosPorAnio = {};
                        this.completarProgresoConfirmacion(duracionMs).then(() => {
                            this.confirmacionProgreso = null;
                            if (typeof Swal !== 'undefined') Swal.close();
                            return this.cerrarModalComparativo();
                        }).then(() => {
                            this.reajusteAnalisis = null;
                            const aviso = this.mostrarResultadoImportacion(resultado);
                            Promise.resolve(aviso).finally(() => {
                                this.cargar();
                                if (presupuestoId > 0 && parseInt(this.detalle?.presupuesto?.id, 10) === presupuestoId) {
                                    this.cargarDetalle(presupuestoId);
                                }
                            });
                        });
                    },
                    onError: (mensajeError, xhr) => {
                        this.cancelarProgresoConfirmacion();
                        const esTimeout = String(xhr?.statusText || '').toLowerCase() === 'timeout';
                        console.error('[Atlas Presupuestos] Fallo al confirmar reajuste', {
                            status: Number(xhr?.status || 0),
                            statusText: String(xhr?.statusText || ''),
                            mensaje: mensajeError || ''
                        });
                        this.reajusteAnalisis = null;
                        const motivoControl = document.getElementById('atlasPresComparativoMotivo');
                        if (motivoControl) motivoControl.disabled = true;
                        this.cerrarModalComparativo().then(() => {
                            const mensaje = esTimeout
                                ? 'La actualización superó el tiempo de espera. No se enviará de nuevo automáticamente. Pulsa Actualizar para comprobar si se aplicó antes de cargar nuevamente el Excel.'
                                : 'No pudimos confirmar el resultado del reajuste. No se enviará de nuevo automáticamente. Pulsa Actualizar para comprobar el estado antes de cargar nuevamente el Excel.';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No pudimos confirmar el resultado',
                                    text: mensaje,
                                    confirmButtonText: 'Entendido'
                                });
                            } else {
                                showError(mensaje);
                            }
                        });
                    },
                    onAlways: () => this.actualizarEstadoConfirmacionReajuste()
                });
            },

            cerrarModalComparativo() {
                const el = document.getElementById('modalAtlasPresupuestoComparativo');
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
                    const inst = (window.bootstrap && bootstrap.Modal.getInstance(el)) || this.modalComparativo;
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
                const esReajuste = String(resumen.motivo || '').trim() !== '';
                const title = esReajuste
                    ? (observaciones > 0 ? 'Reajuste aplicado con observaciones' : 'Reajuste aplicado')
                    : (esRecarga
                        ? (observaciones > 0 ? 'Presupuesto recargado con observaciones' : 'Presupuesto recargado')
                        : (observaciones > 0 ? 'Presupuesto cargado con observaciones' : 'Presupuesto cargado completo'));
                const detalle = [];
                if (Number(resumen.duplicados || 0) > 0) {
                    detalle.push(`${this.number(resumen.duplicados)} duplicado(s). Se tomó el último registro de cada sucursal.`);
                }
                if (Number(resumen.extras || 0) > 0) {
                    const muestra = (resumen.detalle_extras || []).map(x => `fila ${x.fila}: ${x.fk_sucursal}`).join(', ');
                    detalle.push(`${this.number(resumen.extras)} sucursal(es) nueva(s) se agregaron al presupuesto${muestra ? ` (${muestra})` : ''}.`);
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
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Revisadas</span><span class="atlas-pres-import-result-value">${this.number(resumen.registros_importados || datos.registros_importados || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Aplicadas</span><span class="atlas-pres-import-result-value">${this.number(resumen.registros_persistidos ?? resumen.registros_importados ?? datos.registros_importados ?? 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Sin cambios</span><span class="atlas-pres-import-result-value">${this.number(resumen.registros_sin_cambios || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Duplicadas</span><span class="atlas-pres-import-result-value">${this.number(resumen.duplicados || 0)}</span></div>
                            <div class="atlas-pres-import-result-item"><span class="atlas-pres-import-result-label">Nuevas</span><span class="atlas-pres-import-result-value">${this.number(resumen.altas_fuera_catalogo || resumen.extras || 0)}</span></div>
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
                            <div><strong>${this.number(resumen.altas_fuera_catalogo || resumen.extras)} sucursal(es) nueva(s) se agregaron al presupuesto mensual.</strong></div>
                            <div class="small">Su alta completa en el catalogo operativo se administra por separado.</div>
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
                    extras: 'Sucursales nuevas del Excel que se agregaron al presupuesto mensual. El catalogo operativo se completa por separado con sus datos administrativos y de ubicacion.',
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
