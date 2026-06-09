<script>window.ATLAS_GOOGLE_MAPS_KEY = <?= $google_maps_api_key_js ?? '""' ?>;</script>

<div class="container-fluid py-3 atlas-page">
    <style>
        .atlas-page { color: #22303e; }
        .atlas-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
        .atlas-title { display: flex; align-items: center; gap: .7rem; margin: 0; color: #1e3a5f; font-size: 1.28rem; font-weight: 800; }
        .atlas-title i { color: #2563eb; }
        .btn-action-size { height: 36px; padding: .375rem .75rem; font-size: .875rem; line-height: 1; display: inline-flex; align-items: center; gap: .375rem; }
        .atlas-shell { border: 1px solid #e2e8f0; border-radius: .85rem; background: #fff; box-shadow: 0 .125rem .375rem rgba(34, 48, 62, .08); }
        .atlas-shell .card-body { padding: 1rem; }
        .atlas-shell .select2-container { max-width: 100% !important; }
        .atlas-tabs { border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; gap: .35rem; flex-wrap: wrap; }
        .atlas-tabs .nav-link { border: 0; border-bottom: 3px solid transparent; color: #64748b; font-weight: 800; padding: .65rem .9rem; }
        .atlas-tabs .nav-link.active { color: #173756; border-bottom-color: #2563eb; background: transparent; }
        .atlas-panel-head { display: flex; align-items: center; justify-content: flex-end; gap: .8rem; margin-bottom: .85rem; }
        .atlas-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
        .atlas-kpi { border: 1px solid #e2e8f0; border-radius: .65rem; background: #fff; padding: .78rem .9rem; }
        .atlas-kpi span { display: flex; align-items: center; gap: .45rem; color: #64748b; font-size: .72rem; font-weight: 900; text-transform: uppercase; letter-spacing: .03em; }
        .atlas-kpi strong { display: block; margin-top: .2rem; color: #173756; font-size: 1.2rem; font-weight: 900; }
        .atlas-kpi-action { display: flex; align-items: center; justify-content: space-between; gap: .6rem; }
        .atlas-kpi-danger { border-color: #fecaca; background: #fff7f7; }
        .atlas-kpi-danger strong, .atlas-kpi-danger span { color: #b91c1c; }
        .atlas-kpi-warn { border-color: #fde68a; background: #fffbeb; }
        .atlas-kpi-warn strong, .atlas-kpi-warn span { color: #92400e; }
        .atlas-map-badge-btn, .atlas-location-link { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; border-radius: 999px; font-size: .72rem; font-weight: 800; padding: .22rem .55rem; display: inline-flex; align-items: center; gap: .32rem; }
        .atlas-quality-btn { border-color: #fecaca; color: #b91c1c; background: #fff1f2; }
        .atlas-quality-btn-warning { border-color: #fde68a; color: #92400e; background: #fffbeb; }
        .atlas-location-link { max-width: 100%; border-color: #dbe4ef; color: #334155; background: #f8fafc; white-space: normal; text-align: left; border-radius: .45rem; }
        .atlas-field-row { display: flex; flex-direction: column; gap: .08rem; min-width: 0; }
        .atlas-field-label { color: #64748b; font-size: .68rem; font-weight: 900; text-transform: uppercase; letter-spacing: .03em; line-height: 1.1; }
        .atlas-field-value { color: #22303e; font-size: .88rem; font-weight: 800; line-height: 1.2; }
        .atlas-muted { color: #64748b; font-size: .78rem; font-weight: 700; }
        .atlas-empty { text-align: center; color: #94a3b8; font-weight: 700; padding: 2rem !important; }
        .atlas-badge { display: inline-flex; align-items: center; gap: .28rem; border-radius: 999px; padding: .18rem .55rem; font-size: .72rem; font-weight: 900; white-space: nowrap; }
        .atlas-badge-ok { background: #dcfce7; color: #15803d; }
        .atlas-badge-off { background: #f1f5f9; color: #64748b; }
        .atlas-action-buttons { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; }
        .atlas-action-buttons .btn { width: 2.15rem; height: 2.15rem; border-radius: 999px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: #26344e; border-color: #26344e; box-shadow: 0 5px 12px rgba(15, 23, 42, .18); }
        .atlas-table-wrap { border: 1px solid #e5e7eb; border-radius: .75rem; background: #fff; padding: 1.1rem; position: relative; min-height: 12rem; }
        .atlas-table-wrap table { width: 100% !important; }
        .atlas-table-wrap thead th { color: #566a7f; font-size: .76rem; font-weight: 900; letter-spacing: .03em; text-transform: uppercase; white-space: nowrap; border-bottom: 1px solid #dbe4ef; }
        .atlas-table-wrap tbody td { vertical-align: middle; color: #566a7f; border-color: #e8eef5; }
        .atlas-table-wrap.atlas-loading table, .atlas-table-wrap.atlas-loading .dt-container, .atlas-table-wrap.atlas-loading .dataTables_wrapper { opacity: .25; pointer-events: none; }
        .atlas-table-wrap .dt-container .row { align-items: center; row-gap: .75rem; }
        .atlas-table-wrap .dt-search { text-align: right; }
        .atlas-table-wrap .dt-search input, .atlas-table-wrap .dataTables_filter input { max-width: 14rem; }
        .atlas-table-wrap .dt-paging, .atlas-table-wrap .dataTables_paginate { display: flex; justify-content: flex-end; }
        .atlas-table-wrap .pagination { margin-bottom: 0; }
        .atlas-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem 1rem; }
        .atlas-form-grid > div { min-width: 0; }
        .atlas-field-wide { grid-column: 1 / -1; }
        .atlas-required::after { content: " *"; color: #dc2626; font-weight: 900; }
        .atlas-combo-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .45rem; align-items: end; }
        .atlas-combo-add i { margin: 0; }
        .atlas-cascade-help { display: block; margin-top: .28rem; color: #64748b; font-size: .72rem; font-weight: 700; }
        .atlas-fk-input { max-width: 10rem; text-align: center; font-weight: 900; color: #94a3b8; }
        .atlas-branch-row { display: grid; grid-template-columns: 11rem minmax(0, 1fr); gap: 1rem; align-items: start; }
        .atlas-location-fields { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
        .atlas-coord-row { display: grid; grid-template-columns: repeat(2, minmax(0, 12rem)); gap: .75rem; }
        .atlas-address-field { cursor: pointer; resize: vertical; min-height: 4.4rem; }
        .atlas-address-search { position: relative; }
        .atlas-address-search i { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: #64748b; z-index: 2; }
        .atlas-address-search .form-control { padding-left: 2.35rem; }
        .pac-container { z-index: 200000 !important; }
        .atlas-map-box { min-height: 24rem; border: 1px solid #dbe4ef; border-radius: .75rem; background: #f8fafc; overflow: hidden; }
        #atlas-direccion-mapa, #atlas-mapa-sucursales, #atlas-ubicacion-mapa { min-height: 24rem; width: 100%; }
        .atlas-map-marker {
            position: absolute;
            transform: translate(-50%, -100%);
            cursor: pointer;
            user-select: none;
        }
        .atlas-map-pin {
            width: 2.25rem;
            height: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #fff;
            border-radius: 999px 999px 999px .25rem;
            background: var(--atlas-marker-color, #2563eb);
            color: #fff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .28);
            transform: rotate(-45deg);
        }
        .atlas-map-pin i {
            transform: rotate(45deg);
            font-size: .95rem;
            line-height: 1;
        }
        .atlas-map-legend {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            align-items: center;
        }
        .atlas-map-legend-item {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .24rem .55rem;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: #fff;
            color: #334155;
            font-size: .72rem;
            font-weight: 800;
        }
        .atlas-map-legend-item i { color: var(--atlas-class-color, #2563eb); }
        .atlas-address-meta { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-top: .65rem; color: #64748b; font-size: .78rem; font-weight: 800; }
        .atlas-quality-list { display: grid; gap: .75rem; }
        .atlas-quality-item { border: 1px solid #e2e8f0; border-radius: .7rem; background: #fff; padding: .85rem; }
        .atlas-quality-head { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; margin-bottom: .55rem; }
        .atlas-quality-title { color: #173756; font-size: .9rem; font-weight: 900; line-height: 1.15; }
        .atlas-quality-sub { color: #64748b; font-size: .72rem; font-weight: 800; line-height: 1.2; margin-top: .12rem; }
        .atlas-quality-reasons { display: flex; flex-wrap: wrap; gap: .38rem; }
        .atlas-quality-reason { display: inline-flex; align-items: center; gap: .3rem; border-radius: 999px; padding: .22rem .55rem; font-size: .7rem; font-weight: 900; line-height: 1.1; }
        .atlas-quality-reason.is-error { background: #fee2e2; color: #b91c1c; }
        .atlas-quality-reason.is-warning { background: #fef3c7; color: #92400e; }
        .atlas-quality-reason.is-info { background: #e0f2fe; color: #0369a1; }
        .atlas-quality-actions { display: inline-flex; align-items: center; gap: .4rem; flex-shrink: 0; }
        .atlas-classif, .atlas-select-classif { display: inline-flex; align-items: center; gap: .42rem; color: #24364b; font-weight: 900; }
        .atlas-classif i { color: var(--atlas-class-color, #2563eb); }
        .atlas-classif-dot, .atlas-select-classif-dot { width: .72rem; height: .72rem; border-radius: 999px; background: var(--atlas-class-color, #94a3b8); box-shadow: inset 0 0 0 2px rgba(255,255,255,.8), 0 0 0 1px rgba(15,23,42,.12); flex: 0 0 auto; }
        .atlas-icon-gallery { display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: .45rem; margin-top: .45rem; }
        .atlas-icon-option { position: relative; height: 2.2rem; border: 1px solid #dbe4ef; border-radius: .55rem; background: #fff; color: #566a7f; display: inline-flex; align-items: center; justify-content: center; transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease; }
        .atlas-icon-option:hover, .atlas-icon-option:focus { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; transform: translateY(-1px); }
        .atlas-icon-option.is-disabled { background: #f1f5f9; border-color: #e2e8f0; color: #cbd5e1; cursor: not-allowed; opacity: .72; transform: none; box-shadow: none; }
        .atlas-icon-option.is-disabled:hover { background: #f1f5f9; border-color: #e2e8f0; color: #cbd5e1; transform: none; }
        .atlas-icon-option.is-disabled[data-atlas-tooltip]:hover::after, .atlas-icon-option.is-disabled[data-atlas-tooltip]:focus::after { content: attr(data-atlas-tooltip); position: absolute; left: 50%; bottom: calc(100% + .45rem); transform: translateX(-50%); z-index: 1060; min-width: max-content; max-width: 14rem; padding: .38rem .55rem; border-radius: .45rem; background: #172033; color: #fff; font-size: .72rem; font-weight: 700; line-height: 1.2; white-space: normal; box-shadow: 0 10px 24px rgba(15,23,42,.22); pointer-events: none; }
        .atlas-icon-option.is-disabled[data-atlas-tooltip]:hover::before, .atlas-icon-option.is-disabled[data-atlas-tooltip]:focus::before { content: ""; position: absolute; left: 50%; bottom: calc(100% + .2rem); transform: translateX(-50%); z-index: 1061; border-width: .28rem .28rem 0 .28rem; border-style: solid; border-color: #172033 transparent transparent transparent; pointer-events: none; }
        .atlas-icon-option.is-active { background: #2563eb; border-color: #2563eb; color: #fff; box-shadow: 0 8px 18px rgba(37,99,235,.22); }
        .atlas-color-input-wrap { display: flex; align-items: center; gap: .55rem; }
        .atlas-color-input-wrap input[type="color"] { width: 3rem; height: 2.4rem; border: 1px solid #dbe4ef; border-radius: .55rem; padding: .15rem; background: #fff; }
        .atlas-drag-handle { width: 2.15rem; height: 2.15rem; border: 1px solid #dbe4ef; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; color: #64748b; background: #f8fafc; cursor: grab; }
        .atlas-order-pill { display: inline-flex; min-width: 2.2rem; height: 1.75rem; align-items: center; justify-content: center; border-radius: 999px; background: #eef2ff; color: #1d4ed8; font-weight: 900; }
        .atlas-catalog-inline { display: grid; grid-template-columns: minmax(0, 1fr) 13rem; gap: 1rem; align-items: end; grid-column: 1 / -1; }
        #modalAtlasSucursal .modal-footer,
        #modalAtlasDireccion .modal-footer,
        #modalAtlasCatalogo .modal-footer,
        #modalAtlasMapa .modal-footer,
        #modalAtlasCalidad .modal-footer {
            justify-content: flex-end;
            gap: .75rem;
        }
        #modalAtlasSucursal .modal-footer .btn,
        #modalAtlasDireccion .modal-footer .btn,
        #modalAtlasCatalogo .modal-footer .btn,
        #modalAtlasMapa .modal-footer .btn,
        #modalAtlasCalidad .modal-footer .btn {
            min-width: 8.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            font-weight: 700;
        }
        @media (max-width: 991.98px) {
            .atlas-kpis, .atlas-location-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .atlas-icon-gallery { grid-template-columns: repeat(6, minmax(0, 1fr)); }
            .atlas-table-wrap { padding: .9rem; }
        }
        @media (max-width: 767.98px) {
            .atlas-page { padding-left: .25rem; padding-right: .25rem; }
            .atlas-tabs { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; padding-bottom: .25rem; -webkit-overflow-scrolling: touch; }
            .atlas-tabs .nav-item { flex: 0 0 auto; }
            .atlas-tabs .nav-link { white-space: nowrap; padding: .6rem .75rem; }
            .atlas-panel-head { justify-content: stretch; }
            .atlas-panel-head .btn { width: 100%; justify-content: center; }
            .atlas-table-wrap { padding: .75rem; border-radius: .6rem; }
            .atlas-table-wrap .dt-container .row,
            .atlas-table-wrap .dataTables_wrapper .row { row-gap: .75rem; }
            .atlas-table-wrap .dt-length,
            .atlas-table-wrap .dt-search,
            .atlas-table-wrap .dataTables_length,
            .atlas-table-wrap .dataTables_filter { width: 100%; text-align: left; }
            .atlas-table-wrap .dt-search label,
            .atlas-table-wrap .dataTables_filter label { display: block; width: 100%; }
            .atlas-table-wrap .dt-search input,
            .atlas-table-wrap .dataTables_filter input { width: 100%; max-width: none; margin-left: 0; margin-top: .35rem; }
            .atlas-table-wrap .dt-info,
            .atlas-table-wrap .dataTables_info { text-align: center; }
            .atlas-table-wrap .dt-paging,
            .atlas-table-wrap .dataTables_paginate { justify-content: center; width: 100%; }
            .atlas-table-wrap .dt-paging .pagination,
            .atlas-table-wrap .dataTables_paginate .pagination { justify-content: center; flex-wrap: wrap; gap: .25rem; }
            .atlas-address-meta { align-items: flex-start; flex-direction: column; }
            .atlas-map-box, #atlas-direccion-mapa, #atlas-mapa-sucursales, #atlas-ubicacion-mapa { min-height: 19rem; }
            #modalAtlasSucursal .modal-footer,
            #modalAtlasDireccion .modal-footer,
            #modalAtlasCatalogo .modal-footer,
            #modalAtlasMapa .modal-footer,
            #modalAtlasCalidad .modal-footer { flex-direction: column; align-items: stretch; }
            #modalAtlasSucursal .modal-footer .btn,
            #modalAtlasDireccion .modal-footer .btn,
            #modalAtlasCatalogo .modal-footer .btn,
            #modalAtlasMapa .modal-footer .btn,
            #modalAtlasCalidad .modal-footer .btn { width: 100%; }
        }
        @media (max-width: 575.98px) {
            .atlas-head { align-items: stretch; flex-direction: column; }
            .atlas-head .btn { width: 100%; justify-content: center; }
            .atlas-title { font-size: 1.08rem; }
            .atlas-kpis, .atlas-form-grid, .atlas-branch-row, .atlas-location-fields, .atlas-coord-row, .atlas-catalog-inline { grid-template-columns: 1fr; }
            .atlas-kpi { padding: .7rem .75rem; }
            .atlas-kpi-action { align-items: flex-start; flex-direction: column; }
            .atlas-map-badge-btn { justify-content: center; width: 100%; }
            .atlas-fk-input { max-width: none; }
            .atlas-combo-row { grid-template-columns: minmax(0, 1fr) 2.75rem; gap: .35rem; }
            .atlas-icon-gallery { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .atlas-map-box, #atlas-direccion-mapa, #atlas-mapa-sucursales, #atlas-ubicacion-mapa { min-height: 17rem; }
            .atlas-location-link { width: 100%; }
            .atlas-location-link span { overflow-wrap: anywhere; }
        }
    </style>

    <div class="atlas-head">
        <div>
            <h4 class="atlas-title">
                <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                <span>Catálogos</span>
            </h4>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="atlas-btn-recargar">
            <i class="fa-solid fa-rotate me-1"></i>Recargar
        </button>
    </div>

    <div class="card atlas-shell">
        <div class="card-body">
            <ul class="nav atlas-tabs" id="atlas-tabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-tab-sucursales"><i class="fa-solid fa-store me-1"></i>Sucursales</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-tab-divisiones"><i class="fa-solid fa-diagram-project me-1"></i>Divisiones</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-tab-distribuidores"><i class="fa-solid fa-building me-1"></i>Distribuidores</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-tab-diversificaciones"><i class="fa-solid fa-layer-group me-1"></i>Diversificaciones</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-tab-clasificaciones"><i class="fa-solid fa-tags me-1"></i>Clasificaciones</button></li>
            </ul>

            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="atlas-tab-sucursales" role="tabpanel">
            <div class="atlas-panel-head">
                <button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="sucursal"><i class="fa fa-plus icon-sm me-sm-1"></i><span>Agregar sucursal</span></button>
            </div>
            <div class="atlas-kpis">
                <div class="atlas-kpi"><span><i class="fa-solid fa-store"></i>Total</span><strong id="atlas-kpi-total">0</strong></div>
                <div class="atlas-kpi"><span><i class="fa-solid fa-circle-check"></i>Activas</span><strong id="atlas-kpi-activas">0</strong></div>
                <div class="atlas-kpi"><span><i class="fa-solid fa-circle-pause"></i>Inactivas</span><strong id="atlas-kpi-inactivas">0</strong></div>
                <div class="atlas-kpi"><span><i class="fa-solid fa-location-crosshairs"></i>Con coordenadas</span><div class="atlas-kpi-action"><strong id="atlas-kpi-coordenadas">0</strong><button type="button" class="atlas-map-badge-btn" id="atlas-btn-ver-mapa"><i class="fa-solid fa-map-location-dot"></i>Ver mapa</button></div></div>
                <div class="atlas-kpi atlas-kpi-danger"><span><i class="fa-solid fa-triangle-exclamation"></i>Con error</span><div class="atlas-kpi-action"><strong id="atlas-kpi-errores">0</strong><button type="button" class="atlas-map-badge-btn atlas-quality-btn" id="atlas-btn-ver-errores"><i class="fa-solid fa-list-check"></i>Detalle</button></div></div>
                <div class="atlas-kpi atlas-kpi-warn"><span><i class="fa-solid fa-map-pin"></i>Sin coordenadas</span><div class="atlas-kpi-action"><strong id="atlas-kpi-sin-coordenadas">0</strong><button type="button" class="atlas-map-badge-btn atlas-quality-btn-warning" id="atlas-btn-ver-sin-coordenadas"><i class="fa-solid fa-screwdriver-wrench"></i>Corregir</button></div></div>
            </div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="sucursales" data-atlas-loading-label="Cargando sucursales...">
                <table id="atlasTablaSucursales" class="dt-responsive table border-top">
                    <thead><tr><th></th><th>Sucursal</th><th>Clasificación</th><th>Estatus</th><th>Acciones</th></tr></thead>
                    <tbody id="atlas-sucursales-body"><tr><td colspan="5" class="atlas-empty"><span class="spinner-border spinner-border-sm me-2"></span>Cargando sucursales...</td></tr></tbody>
                </table>
            </div>
                </div>
                <div class="tab-pane fade" id="atlas-tab-divisiones" role="tabpanel">
            <div class="atlas-panel-head"><button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="division"><i class="fa fa-plus icon-sm me-sm-1"></i><span>Agregar división</span></button></div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="divisiones" data-atlas-loading-label="Cargando divisiones...">
                <table id="atlasTablaDivisiones" class="dt-responsive table border-top"><thead><tr><th>División</th><th>Divisional</th><th>Estatus</th><th>Acciones</th></tr></thead><tbody></tbody></table>
            </div>
                </div>
                <div class="tab-pane fade" id="atlas-tab-distribuidores" role="tabpanel">
            <div class="atlas-panel-head"><button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="distribuidor"><i class="fa fa-plus icon-sm me-sm-1"></i><span>Agregar distribuidor</span></button></div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="distribuidores" data-atlas-loading-label="Cargando distribuidores...">
                <table id="atlasTablaDistribuidores" class="dt-responsive table border-top"><thead><tr><th>Distribuidor</th><th>Estatus</th><th>Acciones</th></tr></thead><tbody></tbody></table>
            </div>
                </div>
                <div class="tab-pane fade" id="atlas-tab-diversificaciones" role="tabpanel">
            <div class="atlas-panel-head"><button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="diversificacion"><i class="fa fa-plus icon-sm me-sm-1"></i><span>Agregar diversificación</span></button></div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="diversificaciones" data-atlas-loading-label="Cargando diversificaciones...">
                <table id="atlasTablaDiversificaciones" class="dt-responsive table border-top"><thead><tr><th>Diversificación</th><th>Estatus</th><th>Acciones</th></tr></thead><tbody></tbody></table>
            </div>
                </div>
                <div class="tab-pane fade" id="atlas-tab-clasificaciones" role="tabpanel">
            <div class="atlas-panel-head"><button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="clasificacion"><i class="fa fa-plus icon-sm me-sm-1"></i><span>Agregar clasificación</span></button></div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="clasificaciones" data-atlas-loading-label="Cargando clasificaciones...">
                <table id="atlasTablaClasificaciones" class="dt-responsive table border-top"><thead><tr><th></th><th>Orden</th><th>Clasificación</th><th>Estatus</th><th>Acciones</th></tr></thead><tbody></tbody></table>
            </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtlasSucursal" tabindex="-1" aria-labelledby="modalAtlasSucursalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" id="formAtlasSucursal" autocomplete="off">
            <input type="hidden" name="id">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalAtlasSucursalLabel"><i class="fa-solid fa-store me-2"></i>Agregar sucursal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="atlas-form-grid">
                    <div class="atlas-branch-row atlas-field-wide">
                        <div><label class="form-label">FK sucursal</label><input type="text" class="form-control atlas-fk-input" name="fk_sucursal" readonly placeholder="Auto"><span class="atlas-cascade-help">Auto al guardar.</span></div>
                        <div><label class="form-label atlas-required">Sucursal</label><input type="text" class="form-control" name="sucursal" required placeholder="Nombre de la sucursal"></div>
                    </div>
                    <div><label class="form-label atlas-required">Distribuidor</label><div class="atlas-combo-row"><select class="form-select js-atlas-select-buscador" name="distribuidor_id" id="atlas-sucursal-distribuidor" required></select><button type="button" class="btn btn-icon btn-outline-primary atlas-combo-add" data-atlas-quick-add="distribuidor" data-atlas-target="distribuidor_id" title="Agregar distribuidor" aria-label="Agregar distribuidor"><i class="fa-solid fa-plus"></i></button></div></div>
                    <div><label class="form-label atlas-required">Clasificación</label><div class="atlas-combo-row"><select class="form-select js-atlas-select-buscador" name="clasificacion_id" id="atlas-sucursal-clasificacion" required></select><button type="button" class="btn btn-icon btn-outline-primary atlas-combo-add" data-atlas-quick-add="clasificacion" data-atlas-target="clasificacion_id" title="Agregar clasificación" aria-label="Agregar clasificación"><i class="fa-solid fa-plus"></i></button></div></div>
                    <div><label class="form-label atlas-required">Divisional</label><select class="form-select js-atlas-select-buscador" name="divisional_id" id="atlas-sucursal-divisional" required></select></div>
                    <div><label class="form-label atlas-required">División</label><div class="atlas-combo-row"><select class="form-select js-atlas-select-buscador" name="division_id" id="atlas-sucursal-division" required></select><button type="button" class="btn btn-icon btn-outline-primary atlas-combo-add" data-atlas-quick-add="division" data-atlas-target="division_id" title="Agregar división" aria-label="Agregar división"><i class="fa-solid fa-plus"></i></button></div><span class="atlas-cascade-help">Se habilita después de seleccionar un divisional.</span></div>
                    <div><label class="form-label atlas-required">Regional</label><select class="form-select js-atlas-select-buscador" name="regional_id" id="atlas-sucursal-regional" required></select><span class="atlas-cascade-help">Se habilita después de seleccionar una división.</span></div>
                    <div><label class="form-label atlas-required">Supervisor</label><select class="form-select js-atlas-select-buscador" name="supervisor_id" id="atlas-sucursal-supervisor" required></select></div>
                    <div><label class="form-label atlas-required">Asesor</label><select class="form-select js-atlas-select-buscador" name="asesor_id" id="atlas-sucursal-asesor" required></select></div>
                    <div><label class="form-label atlas-required">Diversificación</label><div class="atlas-combo-row"><select class="form-select js-atlas-select-buscador" name="diversificacion_id" id="atlas-sucursal-diversificacion" required></select><button type="button" class="btn btn-icon btn-outline-primary atlas-combo-add" data-atlas-quick-add="diversificacion" data-atlas-target="diversificacion_id" title="Agregar diversificación" aria-label="Agregar diversificación"><i class="fa-solid fa-plus"></i></button></div></div>
                    <div><label class="form-label atlas-required">Estatus</label><select class="form-select js-atlas-select-buscador" name="activo" required><option value="1">Activa</option><option value="0">Inactiva</option></select></div>
                    <div class="atlas-field-wide"><label class="form-label atlas-required">Dirección</label><textarea class="form-control atlas-address-field" name="direccion_sucursal" id="atlas-sucursal-direccion" rows="2" readonly required placeholder="Da clic para buscar la dirección en el mapa"></textarea><span class="atlas-cascade-help">La dirección se captura desde Google Maps para calcular estado, municipio, localidad, CP y coordenadas.</span></div>
                    <div class="atlas-location-fields atlas-field-wide">
                        <div><label class="form-label atlas-required">Estado</label><input type="text" class="form-control" name="estado" readonly required placeholder="Estado"></div>
                        <div><label class="form-label atlas-required">Municipio</label><input type="text" class="form-control" name="municipio" readonly required placeholder="Municipio"></div>
                        <div><label class="form-label atlas-required">Localidad</label><input type="text" class="form-control" name="localidad" readonly required placeholder="Localidad"></div>
                        <div><label class="form-label atlas-required">Código postal</label><input type="text" class="form-control" name="codigo_postal" readonly required placeholder="CP"></div>
                    </div>
                    <div class="atlas-coord-row atlas-field-wide">
                        <div><label class="form-label atlas-required">Latitud</label><input type="text" class="form-control" name="latitud" readonly required placeholder="Latitud"></div>
                        <div><label class="form-label atlas-required">Longitud</label><input type="text" class="form-control" name="longitud" readonly required placeholder="Longitud"></div>
                    </div>
                    <input type="hidden" name="coordenadas">
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar</button><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAtlasDireccion" tabindex="-1" aria-labelledby="modalAtlasDireccionLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasDireccionLabel"><i class="fa-solid fa-map-location-dot me-2"></i>Ubicar sucursal</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div class="atlas-address-search mb-3"><i class="fa-solid fa-magnifying-glass"></i><input type="text" class="form-control" id="atlas-direccion-busqueda" placeholder="Escribe la dirección de la sucursal"></div>
                <div class="atlas-map-box"><div id="atlas-direccion-mapa" aria-label="Mapa para seleccionar ubicación"></div></div>
                <div class="atlas-address-meta"><span id="atlas-direccion-coordenadas">Selecciona una dirección para colocar el pin.</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" id="atlas-direccion-confirmar" disabled><i class="fa-solid fa-location-dot"></i>Usar ubicación</button><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtlasCatalogo" tabindex="-1" aria-labelledby="modalAtlasCatalogoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" id="formAtlasCatalogo" autocomplete="off">
            <input type="hidden" name="id" id="atlas-catalogo-id"><input type="hidden" name="tipo" id="atlas-catalogo-tipo">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasCatalogoLabel"><i class="fa-solid fa-tags me-2"></i>Agregar catálogo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body"><div class="atlas-form-grid" id="atlas-catalogo-fields"></div></div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i>Guardar</button><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAtlasMapa" tabindex="-1" aria-labelledby="modalAtlasMapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasMapaLabel"><i class="fa-solid fa-map-location-dot me-2"></i>Sucursales con coordenadas</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div class="atlas-map-box"><div id="atlas-mapa-sucursales"></div></div>
                <div class="atlas-address-meta"><span id="atlas-mapa-meta">Cargando sucursales con coordenadas...</span></div>
                <div class="mt-2" id="atlas-mapa-legend"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtlasUbicacion" tabindex="-1" aria-labelledby="modalAtlasUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasUbicacionLabel"><i class="fa-solid fa-location-dot me-2"></i><span id="atlas-ubicacion-titulo">Ubicación</span></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body"><div class="mb-2 atlas-field-value" id="atlas-ubicacion-direccion">-</div><div class="mb-3 atlas-muted" id="atlas-ubicacion-coordenadas">-</div><div class="atlas-map-box"><div id="atlas-ubicacion-mapa"></div></div><div class="atlas-empty d-none" id="atlas-ubicacion-empty">Cargando ubicación...</div></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtlasCalidad" tabindex="-1" aria-labelledby="modalAtlasCalidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasCalidadLabel"><i class="fa-solid fa-list-check me-2"></i>Calidad de datos</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div class="atlas-muted mb-3" id="atlas-calidad-resumen">Revisando sucursales...</div>
                <div class="atlas-quality-list" id="atlas-calidad-lista"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    const body = document.getElementById('atlas-sucursales-body');
    const btnRecargar = document.getElementById('atlas-btn-recargar');
    const btnMapa = document.getElementById('atlas-btn-ver-mapa');
    const btnErrores = document.getElementById('atlas-btn-ver-errores');
    const btnSinCoordenadas = document.getElementById('atlas-btn-ver-sin-coordenadas');
    const formSucursal = document.getElementById('formAtlasSucursal');
    const formCatalogo = document.getElementById('formAtlasCatalogo');
    const modalSucursalEl = document.getElementById('modalAtlasSucursal');
    const modalCatalogoEl = document.getElementById('modalAtlasCatalogo');
    const modalDireccionEl = document.getElementById('modalAtlasDireccion');
    const modalMapaEl = document.getElementById('modalAtlasMapa');
    const modalUbicacionEl = document.getElementById('modalAtlasUbicacion');
    const modalCalidadEl = document.getElementById('modalAtlasCalidad');
    const modalSucursalTitulo = document.getElementById('modalAtlasSucursalLabel');
    const modalCatalogoTitulo = document.getElementById('modalAtlasCatalogoLabel');
    const modalCalidadTitulo = document.getElementById('modalAtlasCalidadLabel');
    const calidadResumen = document.getElementById('atlas-calidad-resumen');
    const calidadLista = document.getElementById('atlas-calidad-lista');
    const catalogoFields = document.getElementById('atlas-catalogo-fields');
    const direccionBusqueda = document.getElementById('atlas-direccion-busqueda');
    const direccionMapaCont = document.getElementById('atlas-direccion-mapa');
    const direccionCoordenadas = document.getElementById('atlas-direccion-coordenadas');
    const btnConfirmarDireccion = document.getElementById('atlas-direccion-confirmar');
    const tablaSelector = '#atlasTablaSucursales';
    const atlasIconosClasificacion = [
        'fa-solid fa-gem','fa-solid fa-medal','fa-solid fa-award','fa-solid fa-certificate','fa-solid fa-lightbulb','fa-solid fa-star','fa-solid fa-crown','fa-solid fa-trophy',
        'fa-solid fa-bolt','fa-solid fa-fire','fa-solid fa-shield-halved','fa-solid fa-location-dot','fa-solid fa-store','fa-solid fa-building','fa-solid fa-tags','fa-solid fa-chart-line',
        'fa-solid fa-chart-simple','fa-solid fa-arrow-trend-up','fa-solid fa-arrow-trend-down','fa-solid fa-triangle-exclamation','fa-solid fa-circle-exclamation','fa-solid fa-circle-xmark',
        'fa-solid fa-ban','fa-solid fa-skull-crossbones','fa-solid fa-thumbs-down','fa-solid fa-face-frown','fa-solid fa-face-sad-tear','fa-solid fa-bug','fa-solid fa-bomb',
        'fa-solid fa-fire-flame-curved','fa-solid fa-circle-minus','fa-solid fa-down-long','fa-solid fa-arrow-down','fa-solid fa-arrow-down-short-wide','fa-solid fa-link-slash',
        'fa-solid fa-unlink','fa-solid fa-lock','fa-solid fa-lock-open','fa fa-shield-alt','fa-solid fa-user-slash','fa-solid fa-user-xmark','fa-solid fa-store-slash',
        'fa-solid fa-house-circle-xmark','fa-solid fa-location-crosshairs','fa-solid fa-location-pin-lock','fa-solid fa-map-location-dot','fa-solid fa-magnifying-glass-chart',
        'fa-solid fa-seedling','fa-solid fa-hand-holding-dollar','fa-solid fa-sack-dollar','fa-solid fa-scale-unbalanced','fa-solid fa-hourglass-half','fa-solid fa-clock-rotate-left'
    ];
    let sucursales = [];
    let atlasCalidadSucursales = { errores: [], sinCoordenadas: [] };
    let catalogos = { divisiones: [], divisionales: [], regionales: [], supervisores: [], asesores: [], distribuidores: [], diversificaciones: [], clasificaciones: [] };
    let atlasTabla = null;
    let tablasCatalogo = {};
    let atlasClasificacionesSortable = null;
    let atlasMapaCargando = null;
    let atlasTablasCargando = new Set();
    let atlasSwalLoaderAbierto = false;
    let atlasQuickAddContext = null;
    let atlasDireccionMapa = null;
    let atlasDireccionMarker = null;
    let atlasDireccionAutocomplete = null;
    let atlasDireccionGeocoder = null;
    let atlasDireccionActual = null;
    let atlasDireccionContext = null;
    let atlasDireccionMapClickReady = false;
    let atlasUbicacionMapa = null;
    let atlasUbicacionMarker = null;

    function atlasNormalizarTexto(v) {
        const s = String(v == null ? '' : v);
        if (!/[ÃƒÃ‚Ã¢]/.test(s)) return s;
        try {
            const win1252 = {'â‚¬':0x80,'â€š':0x82,'Æ’':0x83,'â€ž':0x84,'â€¦':0x85,'â€ ':0x86,'â€¡':0x87,'Ë†':0x88,'â€°':0x89,'Å ':0x8A,'â€¹':0x8B,'Å’':0x8C,'Å½':0x8E,'â€˜':0x91,'â€™':0x92,'â€œ':0x93,'â€':0x94,'â€¢':0x95,'â€“':0x96,'â€”':0x97,'Ëœ':0x98,'â„¢':0x99,'Å¡':0x9A,'â€º':0x9B,'Å“':0x9C,'Å¾':0x9E,'Å¸':0x9F};
            const bytes = Array.from(s).map(ch => Object.prototype.hasOwnProperty.call(win1252, ch) ? win1252[ch] : (ch.charCodeAt(0) & 0xff));
            return new TextDecoder('utf-8').decode(new Uint8Array(bytes));
        } catch (e) {
            try { return decodeURIComponent(escape(s)); } catch (e2) { return s; }
        }
    }
    function esc(v) { return atlasNormalizarTexto(v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
    function textoPlano(row, keys) { return atlasNormalizarTexto(keys.map(key => row[key] || '').join(' ')).trim(); }
    function mostrarModal(el) { if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show(); }
    function cerrarModal(el) { if (el && window.bootstrap) { const inst = bootstrap.Modal.getInstance(el); if (inst) inst.hide(); } }
    function setKpi(id, value) { const el = document.getElementById(id); if (el) el.textContent = Number(value || 0).toLocaleString('es-MX'); }
    function setTexto(id, value) { const el = document.getElementById(id); if (el) el.textContent = value == null ? '' : String(value); }
    function numeroValido(v) { if (v == null || v === '') return null; const n = Number(String(v).trim()); return Number.isFinite(n) ? n : null; }
    function sucursalConCoordenadas(row) { const lat = numeroValido(row.latitud); const lng = numeroValido(row.longitud); if (lat == null || lng == null || Math.abs(lat) < 1e-9 && Math.abs(lng) < 1e-9) return null; return Object.assign({}, row, { _lat: lat, _lng: lng }); }
    function atlasClaveCalidad(v) {
        return atlasNormalizarTexto(v || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    }
    function atlasCoordenadaMexico(lat, lng) {
        return lat >= 14 && lat <= 33.5 && lng >= -119 && lng <= -86;
    }
    function atlasCalidadAddIssue(mapa, row, issue) {
        const id = String(row.id || '');
        if (!id) return;
        if (!mapa[id]) mapa[id] = { row: row, issues: [] };
        if (!mapa[id].issues.some(item => item.codigo === issue.codigo && item.detalle === issue.detalle)) {
            mapa[id].issues.push(issue);
        }
    }
    function atlasGrupoDuplicados(rows, keyFn) {
        const grupos = {};
        rows.forEach(row => {
            const key = keyFn(row);
            if (!key) return;
            if (!grupos[key]) grupos[key] = [];
            grupos[key].push(row);
        });
        return Object.keys(grupos).map(key => grupos[key]).filter(grupo => grupo.length > 1);
    }
    function atlasEvaluarCalidadSucursales() {
        const issuesById = {};
        const activas = sucursales.filter(row => Number(row.activo || 0) === 1);
        activas.forEach(row => {
            const lat = numeroValido(row.latitud);
            const lng = numeroValido(row.longitud);
            const direccion = atlasClaveCalidad(row.direccion || row.direccion_sucursal || '');
            if (!direccion) {
                atlasCalidadAddIssue(issuesById, row, { codigo: 'sin_direccion', severidad: 'error', icono: 'fa-solid fa-map', titulo: 'Sin dirección', detalle: 'No tiene dirección capturada para guiar rutas.' });
            }
            if (lat == null || lng == null) {
                atlasCalidadAddIssue(issuesById, row, { codigo: 'sin_coordenadas', severidad: 'error', icono: 'fa-solid fa-map-pin', titulo: 'Sin coordenadas', detalle: 'No tiene latitud y longitud capturadas.' });
            } else if (Math.abs(lat) < 1e-9 && Math.abs(lng) < 1e-9) {
                atlasCalidadAddIssue(issuesById, row, { codigo: 'coordenadas_cero', severidad: 'error', icono: 'fa-solid fa-location-crosshairs', titulo: 'Coordenadas en cero', detalle: 'La ubicación está en 0,0 y no sirve para ruta.' });
            } else if (!atlasCoordenadaMexico(lat, lng)) {
                atlasCalidadAddIssue(issuesById, row, { codigo: 'coordenadas_fuera_mx', severidad: 'warning', icono: 'fa-solid fa-globe', titulo: 'Coordenadas fuera de México', detalle: 'La ubicación parece estar fuera del rango operativo de México.' });
            }
            [
                ['distribuidor_id', 'Sin distribuidor'],
                ['clasificacion_id', 'Sin clasificación'],
                ['divisional_id', 'Sin divisional'],
                ['division_id', 'Sin división'],
                ['regional_id', 'Sin regional'],
                ['supervisor_id', 'Sin supervisor'],
                ['asesor_id', 'Sin asesor'],
                ['diversificacion_id', 'Sin diversificación']
            ].forEach(pair => {
                if (!String(row[pair[0]] || '').trim()) {
                    atlasCalidadAddIssue(issuesById, row, { codigo: pair[0], severidad: 'warning', icono: 'fa-solid fa-diagram-project', titulo: pair[1], detalle: 'Falta una asignación del catálogo operativo.' });
                }
            });
            if (!String(row.numero_telefono || '').trim()) {
                atlasCalidadAddIssue(issuesById, row, { codigo: 'sin_telefono', severidad: 'info', icono: 'fa-solid fa-phone', titulo: 'Sin teléfono', detalle: 'No tiene teléfono de contacto para validar ruta o entrega.' });
            }
        });
        atlasGrupoDuplicados(activas, row => atlasClaveCalidad(row.sucursal)).forEach(grupo => {
            grupo.forEach(row => {
                const nombres = grupo.filter(item => String(item.id || '') !== String(row.id || '')).map(item => '#' + (item.fk_sucursal || item.id) + ' ' + (item.sucursal || '')).join(', ');
                atlasCalidadAddIssue(issuesById, row, { codigo: 'sucursal_duplicada', severidad: 'error', icono: 'fa-solid fa-copy', titulo: 'Sucursal duplicada', detalle: 'Comparte nombre con ' + nombres + '.' });
            });
        });
        atlasGrupoDuplicados(activas, row => String(row.fk_sucursal || '').trim()).forEach(grupo => {
            grupo.forEach(row => {
                const nombres = grupo.filter(item => String(item.id || '') !== String(row.id || '')).map(item => item.sucursal || ('ID ' + item.id)).join(', ');
                atlasCalidadAddIssue(issuesById, row, { codigo: 'fk_duplicado', severidad: 'error', icono: 'fa-solid fa-hashtag', titulo: 'FK sucursal duplicado', detalle: 'El mismo FK aparece también en: ' + nombres + '.' });
            });
        });
        atlasGrupoDuplicados(activas, row => {
            const punto = sucursalConCoordenadas(row);
            return punto ? punto._lat.toFixed(6) + ',' + punto._lng.toFixed(6) : '';
        }).forEach(grupo => {
            grupo.forEach(row => {
                const nombres = grupo.filter(item => String(item.id || '') !== String(row.id || '')).map(item => item.sucursal || ('ID ' + item.id)).join(', ');
                atlasCalidadAddIssue(issuesById, row, { codigo: 'misma_ubicacion', severidad: 'error', icono: 'fa-solid fa-location-dot', titulo: 'Diferente sucursal con misma ubicación', detalle: 'Comparte coordenadas con: ' + nombres + '.' });
            });
        });
        atlasGrupoDuplicados(activas, row => atlasClaveCalidad(row.direccion || row.direccion_sucursal || '')).forEach(grupo => {
            grupo.forEach(row => {
                const nombres = grupo.filter(item => String(item.id || '') !== String(row.id || '')).map(item => item.sucursal || ('ID ' + item.id)).join(', ');
                atlasCalidadAddIssue(issuesById, row, { codigo: 'misma_direccion', severidad: 'warning', icono: 'fa-solid fa-road', titulo: 'Dirección repetida', detalle: 'Comparte dirección con: ' + nombres + '.' });
            });
        });
        const errores = Object.keys(issuesById).map(id => issuesById[id]).sort((a, b) => {
            const ae = a.issues.some(item => item.severidad === 'error') ? 0 : 1;
            const be = b.issues.some(item => item.severidad === 'error') ? 0 : 1;
            return ae - be || atlasClaveCalidad(a.row.sucursal).localeCompare(atlasClaveCalidad(b.row.sucursal));
        });
        const sinCoordenadas = errores
            .filter(item => item.issues.some(issue => issue.codigo === 'sin_coordenadas' || issue.codigo === 'coordenadas_cero'))
            .sort((a, b) => atlasClaveCalidad(a.row.sucursal).localeCompare(atlasClaveCalidad(b.row.sucursal)));
        atlasCalidadSucursales = { errores: errores, sinCoordenadas: sinCoordenadas };
        setKpi('atlas-kpi-errores', errores.length);
        setKpi('atlas-kpi-sin-coordenadas', sinCoordenadas.length);
        return atlasCalidadSucursales;
    }
    function formToJson(form) { const data = {}; Array.from(new FormData(form).entries()).forEach(pair => { data[pair[0]] = pair[1]; }); return data; }
    function formSnapshot(form) {
        const data = {};
        if (!form) return data;
        Array.from(form.elements || []).forEach(el => {
            if (!el || !el.name) return;
            data[el.name] = el.value == null ? '' : String(el.value);
        });
        return data;
    }
    function setFormValue(form, name, value) {
        const el = form ? form.elements[name] : null;
        if (!el) return;
        el.value = value == null ? '' : String(value);
        if (window.jQuery && jQuery.fn.select2 && jQuery(el).hasClass('select2-hidden-accessible')) {
            jQuery(el).trigger('change.select2');
        }
    }
    function getFormValue(form, name) { const el = form ? form.elements[name] : null; return el ? String(el.value || '').trim() : ''; }
    function atlasWrapTabla(key) { return document.querySelector('[data-atlas-table-loader="' + key + '"]'); }
    function atlasMostrarLoaderGlobal() {
        if (atlasSwalLoaderAbierto || typeof Swal === 'undefined') return;
        atlasSwalLoaderAbierto = true;
        Swal.fire({ title: 'Procesando su petición', text: 'Espere un momento...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: function () { Swal.showLoading(); } });
    }
    function atlasOcultarLoaderGlobal() { if (atlasTablasCargando.size > 0 || !atlasSwalLoaderAbierto) return; atlasSwalLoaderAbierto = false; if (typeof Swal !== 'undefined') Swal.close(); }
    function atlasSetTablaLoading(key, loading, texto) {
        const wrap = atlasWrapTabla(key);
        if (loading) { atlasTablasCargando.add(key); atlasMostrarLoaderGlobal(); } else { atlasTablasCargando.delete(key); }
        if (wrap) { if (texto) wrap.setAttribute('data-atlas-loading-label', texto); wrap.classList.toggle('atlas-loading', !!loading); }
        if (!loading) atlasOcultarLoaderGlobal();
    }
    function atlasSetCatalogosLoading(loading) { ['divisiones','distribuidores','diversificaciones','clasificaciones'].forEach(key => atlasSetTablaLoading(key, loading)); }
    function destruirSelectBuscador(el) { if (window.jQuery && jQuery.fn.select2 && jQuery(el).hasClass('select2-hidden-accessible')) jQuery(el).select2('destroy'); }
    function inicializarSelectBuscador(el) {
        if (!window.jQuery || !jQuery.fn.select2 || !el) return;
        const modal = jQuery(el).closest('.modal');
        const shell = jQuery(el).closest('.atlas-shell');
        jQuery(el).select2({ width: '100%', dropdownParent: modal.length ? modal : (shell.length ? shell : jQuery(document.body)) });
    }
    function refrescarSelectBuscadores(ctx) { (ctx || document).querySelectorAll('.js-atlas-select-buscador').forEach(inicializarSelectBuscador); }
    function colorHexSeguro(value) { const raw = String(value || '').trim(); if (/^#[0-9a-f]{6}$/i.test(raw)) return raw.toUpperCase(); if (/^[0-9a-f]{6}$/i.test(raw)) return ('#' + raw).toUpperCase(); return '#94A3B8'; }
    function colorClasificacion(row) { return colorHexSeguro(row.clasificacion_color_hex || row.color_hex || '#94A3B8'); }
    function clasificacionSucursalActual() {
        const id = getFormValue(formSucursal, 'clasificacion_id');
        return (catalogos.clasificaciones || []).find(row => String(row.id || '') === String(id || '')) || null;
    }
    function colorClasificacionSucursalActual() { return colorHexSeguro((clasificacionSucursalActual() || {}).color_hex || '#2563EB'); }
    function iconoPinDireccionActual() {
        const color = colorClasificacionSucursalActual();
        const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="52" viewBox="0 0 44 52"><path fill="' + color + '" stroke="#fff" stroke-width="3" d="M22 2C11.5 2 3 10.3 3 20.5c0 13.4 19 28.5 19 28.5s19-15.1 19-28.5C41 10.3 32.5 2 22 2Z"/><circle cx="22" cy="20" r="8" fill="#fff"/></svg>';
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(36, 42),
            anchor: new google.maps.Point(18, 42)
        };
    }
    function limpiarDireccionMarker() {
        if (atlasDireccionMarker) {
            atlasDireccionMarker.setMap(null);
            atlasDireccionMarker = null;
        }
    }
    function actualizarMarkerDireccionIcono() {
        if (atlasDireccionMarker && typeof google !== 'undefined' && google.maps) {
            atlasDireccionMarker.setIcon(iconoPinDireccionActual());
        }
    }
    function renderEstatus(row) { const activo = Number(row && row.activo || 0) === 1; return '<span class="atlas-badge ' + (activo ? 'atlas-badge-ok' : 'atlas-badge-off') + '"><i class="fa-solid ' + (activo ? 'fa-circle-check' : 'fa-circle-pause') + '"></i>' + (activo ? 'Activa' : 'Inactiva') + '</span>'; }
    function renderEditar(tipo, id) { return '<div class="atlas-action-buttons"><button type="button" class="btn btn-sm btn-primary" data-atlas-editar="' + esc(tipo) + '" data-id="' + esc(id || '') + '" title="Editar" aria-label="Editar"><i class="fa fa-edit"></i></button></div>'; }
    function findCatalogo(key, id) { const actual = String(id || ''); return actual ? (catalogos[key] || []).find(row => String(row.id || '') === actual) || null : null; }
    function optionsCatalogo(rows, selected, placeholder) {
        const actual = String(selected || '');
        return '<option value="">' + esc(placeholder || 'Selecciona') + '</option>' + (Array.isArray(rows) ? rows : []).map(row => {
            const id = String(row.id || '');
            return '<option value="' + esc(id) + '"' + (id === actual ? ' selected' : '') + '>' + esc(row.nombre || ('# ' + id)) + '</option>';
        }).join('');
    }
    function renderClasificacionSelect2(item) {
        if (!item.id) return item.text;
        const option = item.element;
        const color = option ? (option.getAttribute('data-color') || '#94a3b8') : '#94a3b8';
        const icon = option ? (option.getAttribute('data-icon') || 'fa-solid fa-tags') : 'fa-solid fa-tags';
        return jQuery('<span class="atlas-select-classif" style="--atlas-class-color:' + esc(color) + ';"><span class="atlas-select-classif-dot"></span><i class="' + esc(icon) + '"></i><span>' + esc(item.text) + '</span></span>');
    }
    function iconoClasificacion(row) { return String((row && row.clasificacion_icon_font) || 'fa-solid fa-location-dot').trim() || 'fa-solid fa-location-dot'; }
    function crearMarkerAtlas(map, row, pos) {
        if (!map || typeof google === 'undefined' || !google.maps || !google.maps.OverlayView) return null;
        const marker = new google.maps.OverlayView();
        const color = colorClasificacion(row);
        const icono = iconoClasificacion(row);
        const div = document.createElement('div');
        div.className = 'atlas-map-marker';
        div.title = atlasNormalizarTexto(row.sucursal || '');
        div.innerHTML = '<span class="atlas-map-pin" style="--atlas-marker-color:' + esc(color) + ';"><i class="' + esc(icono) + '"></i></span>';
        marker.onAdd = function () {
            const panes = marker.getPanes();
            if (panes && panes.overlayMouseTarget) panes.overlayMouseTarget.appendChild(div);
            div.addEventListener('click', function () {
                const info = new google.maps.InfoWindow({
                    content: '<div style="min-width:190px;"><div style="font-weight:800;color:#22303e;margin-bottom:.25rem;">' + esc(row.sucursal || 'Sucursal') + '</div><div style="display:flex;align-items:center;gap:.35rem;color:#64748b;font-size:.78rem;font-weight:700;"><span class="atlas-classif-dot" style="--atlas-class-color:' + esc(color) + ';"></span><i class="' + esc(icono) + '" style="color:' + esc(color) + ';"></i><span>' + esc(row.clasificacion_nombre || 'Sin clasificación') + '</span></div></div>'
                });
                info.setPosition(pos);
                info.open(map);
            });
        };
        marker.draw = function () {
            const projection = marker.getProjection();
            if (!projection) return;
            const point = projection.fromLatLngToDivPixel(new google.maps.LatLng(pos.lat, pos.lng));
            if (!point) return;
            div.style.left = point.x + 'px';
            div.style.top = point.y + 'px';
        };
        marker.onRemove = function () { if (div.parentNode) div.parentNode.removeChild(div); };
        marker.setMap(map);
        return marker;
    }
    function renderLeyendaMapa(puntos) {
        const legend = document.getElementById('atlas-mapa-legend');
        if (!legend) return;
        const usados = new Map();
        (puntos || []).forEach(row => {
            const key = String(row.clasificacion_id || row.clasificacion_nombre || 'sin');
            if (!usados.has(key)) {
                usados.set(key, {
                    nombre: row.clasificacion_nombre || 'Sin clasificación',
                    color: colorClasificacion(row),
                    icono: iconoClasificacion(row)
                });
            }
        });
        legend.innerHTML = usados.size ? '<div class="atlas-map-legend">' + Array.from(usados.values()).map(item => {
            return '<span class="atlas-map-legend-item" style="--atlas-class-color:' + esc(item.color) + ';"><i class="' + esc(item.icono) + '"></i>' + esc(item.nombre) + '</span>';
        }).join('') + '</div>' : '';
    }
    function llenarSelect(selectId, rows, placeholder) {
        const el = document.getElementById(selectId); if (!el) return;
        destruirSelectBuscador(el);
        el.innerHTML = '<option value="">' + esc(placeholder || 'Selecciona') + '</option>' + (Array.isArray(rows) ? rows : []).map(row => {
            let texto = row.nombre || ('#' + row.id); let attrs = '';
            if (selectId === 'atlas-sucursal-clasificacion') {
                const orden = parseInt(row.orden, 10); if (orden > 0) texto = orden + '.- ' + texto;
                attrs += ' data-color="' + esc(colorHexSeguro(row.color_hex)) + '" data-icon="' + esc(row.icon_font || 'fa-solid fa-tags') + '"';
            }
            return '<option value="' + esc(row.id || '') + '"' + attrs + '>' + esc(texto) + '</option>';
        }).join('');
        if (window.jQuery && jQuery.fn.select2 && selectId === 'atlas-sucursal-clasificacion') {
            jQuery(el).select2({ width: '100%', dropdownParent: jQuery(el).closest('.modal'), templateResult: renderClasificacionSelect2, templateSelection: renderClasificacionSelect2 });
        } else inicializarSelectBuscador(el);
    }
    function llenarSelectCascada(selectId, rows, placeholder, selected, disabled) {
        const el = document.getElementById(selectId); if (!el) return;
        destruirSelectBuscador(el);
        const actual = String(selected || '');
        el.innerHTML = '<option value="">' + esc(placeholder || 'Selecciona') + '</option>' + (Array.isArray(rows) ? rows : []).map(row => {
            const id = String(row.id || '');
            return '<option value="' + esc(id) + '"' + (id === actual ? ' selected' : '') + '>' + esc(row.nombre || ('# ' + id)) + '</option>';
        }).join('');
        el.disabled = !!disabled;
        inicializarSelectBuscador(el);
    }
    function valoresSucursalActuales() {
        return {
            distribuidor_id: getFormValue(formSucursal, 'distribuidor_id'), diversificacion_id: getFormValue(formSucursal, 'diversificacion_id'), clasificacion_id: getFormValue(formSucursal, 'clasificacion_id'),
            divisional_id: getFormValue(formSucursal, 'divisional_id'), division_id: getFormValue(formSucursal, 'division_id'), regional_id: getFormValue(formSucursal, 'regional_id'), supervisor_id: getFormValue(formSucursal, 'supervisor_id'), asesor_id: getFormValue(formSucursal, 'asesor_id')
        };
    }
    function resolverJerarquiaSucursal(values) {
        const out = Object.assign({}, values || {});
        const asesor = findCatalogo('asesores', out.asesor_id); if (asesor && !out.supervisor_id) out.supervisor_id = asesor.supervisor_id;
        const supervisor = findCatalogo('supervisores', out.supervisor_id); if (supervisor && !out.regional_id) out.regional_id = supervisor.regional_id;
        const regional = findCatalogo('regionales', out.regional_id); if (regional && !out.division_id) out.division_id = regional.division_id;
        const division = findCatalogo('divisiones', out.division_id); if (division && !out.divisional_id) out.divisional_id = division.divisional_id;
        return out;
    }
    function actualizarCascadaSucursal(values) {
        const v = resolverJerarquiaSucursal(values || valoresSucursalActuales());
        const divisionalId = String(v.divisional_id || ''), divisionId = String(v.division_id || ''), regionalId = String(v.regional_id || ''), supervisorId = String(v.supervisor_id || ''), asesorId = String(v.asesor_id || '');
        const divisiones = divisionalId ? (catalogos.divisiones || []).filter(row => String(row.divisional_id || '') === divisionalId) : [];
        const regionales = divisionId ? (catalogos.regionales || []).filter(row => String(row.division_id || '') === divisionId) : [];
        const supervisores = regionalId ? (catalogos.supervisores || []).filter(row => String(row.regional_id || '') === regionalId) : [];
        const asesores = supervisorId ? (catalogos.asesores || []).filter(row => String(row.supervisor_id || '') === supervisorId) : [];
        llenarSelectCascada('atlas-sucursal-divisional', catalogos.divisionales || [], 'Selecciona divisional', divisionalId, false);
        llenarSelectCascada('atlas-sucursal-division', divisiones, divisionalId ? 'Selecciona división' : 'Primero selecciona divisional', divisiones.some(row => String(row.id || '') === divisionId) ? divisionId : '', !divisionalId);
        llenarSelectCascada('atlas-sucursal-regional', regionales, divisionId ? 'Selecciona regional' : 'Primero selecciona división', regionales.some(row => String(row.id || '') === regionalId) ? regionalId : '', !divisionId);
        llenarSelectCascada('atlas-sucursal-supervisor', supervisores, regionalId ? 'Selecciona supervisor' : 'Primero selecciona regional', supervisores.some(row => String(row.id || '') === supervisorId) ? supervisorId : '', !regionalId);
        llenarSelectCascada('atlas-sucursal-asesor', asesores, supervisorId ? 'Selecciona asesor' : 'Primero selecciona supervisor', asesores.some(row => String(row.id || '') === asesorId) ? asesorId : '', !supervisorId);
    }
    async function guardarJson(url, payload) {
        const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, credentials: 'same-origin', body: JSON.stringify(payload) });
        const data = await res.json();
        if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo guardar.');
        return data;
    }
    function columnasAtlas() {
        return [
            { data: null, orderable: false, searchable: false, render: function () { return ''; } },
            { data: 'sucursal', title: 'Sucursal', render: function (data, renderType, row) {
                if (renderType !== 'display') return textoPlano(row, ['sucursal','fk_sucursal','distribuidor_nombre','direccion','numero_telefono','nombre_contacto']);
                const direccionBtn = row.direccion ? '<button type="button" class="atlas-location-link mt-1" data-atlas-ubicacion="' + esc(row.id || '') + '"><i class="fa-solid fa-location-dot"></i><span>' + esc(row.direccion) + '</span></button>' : '<span class="atlas-muted">Sin dirección</span>';
                return '<div class="atlas-field-row"><span class="atlas-field-value">' + esc(data || 'Sin nombre') + '</span><span class="atlas-muted">FK ' + esc(row.fk_sucursal || '-') + ' &middot; ' + esc(row.distribuidor_nombre || 'Sin distribuidor') + '</span></div>'
                    + '<div class="atlas-field-row mt-1"><span class="atlas-field-label">Teléfono</span><span class="atlas-muted"><i class="fa-solid fa-phone me-1"></i>' + esc(row.numero_telefono || 'Sin teléfono') + (row.nombre_contacto ? ' &middot; ' + esc(row.nombre_contacto) : '') + '</span></div>'
                    + '<div class="atlas-field-row mt-1"><span class="atlas-field-label">Dirección</span>' + direccionBtn + '</div>';
            }},
            { data: 'clasificacion_nombre', title: 'Clasificación', render: function (data, renderType, row) {
                if (renderType !== 'display') return textoPlano(row, ['clasificacion_nombre','clasificacion_id']);
                return '<div class="atlas-classif" style="--atlas-class-color:' + esc(colorClasificacion(row)) + ';"><span class="atlas-classif-dot"></span><i class="' + esc(row.clasificacion_icon_font || 'fa-solid fa-tags') + '"></i><span>' + esc(data || 'Sin clasificación') + '</span></div>';
            }},
            { data: 'activo', title: 'Estatus', render: function (data, renderType, row) { return renderType === 'display' ? renderEstatus(row) : (Number(data || 0) === 1 ? 'Activa' : 'Inactiva'); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('sucursal', row.id) : ''; }}
        ];
    }

    function renderFilaSucursalFallback(row) {
        const sucursal = columnasAtlas()[1].render(row.sucursal, 'display', row);
        const clasificacion = columnasAtlas()[2].render(row.clasificacion_nombre, 'display', row);
        const estatus = columnasAtlas()[3].render(row.activo, 'display', row);
        const acciones = columnasAtlas()[4].render(null, 'display', row);
        return '<tr>'
            + '<td></td>'
            + '<td>' + sucursal + '</td>'
            + '<td>' + clasificacion + '</td>'
            + '<td>' + estatus + '</td>'
            + '<td class="text-center">' + acciones + '</td>'
            + '</tr>';
    }

    function columnasCatalogo(tipo) {
        if (tipo === 'divisiones') return [
            { data: 'nombre', title: 'División', render: function (data, renderType) { return renderType === 'display' ? '<span class="atlas-field-value">' + esc(data || 'Sin nombre') + '</span>' : (data || ''); }},
            { data: 'divisional_id', title: 'Divisional', render: function (data, renderType, row) { return renderType === 'display' ? (data ? '<span class="atlas-field-value">' + esc(row.divisional_nombre || 'Sin divisional') + '</span>' : '<span class="atlas-muted">Sin divisional</span>') : [row.divisional_nombre || '', data || ''].join(' '); }},
            { data: 'activo', title: 'Estatus', render: function (data, renderType, row) { return renderType === 'display' ? renderEstatus(row) : (Number(data || 0) === 1 ? 'Activa' : 'Inactiva'); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('division', row.id) : ''; }}
        ];
        if (tipo === 'distribuidores') return [
            { data: 'nombre', title: 'Distribuidor', render: function (data, renderType) { return renderType === 'display' ? '<span class="atlas-field-value">' + esc(data || 'Sin nombre') + '</span>' : (data || ''); }},
            { data: 'activo', title: 'Estatus', render: function (data, renderType, row) { return renderType === 'display' ? renderEstatus(row) : (Number(data || 0) === 1 ? 'Activa' : 'Inactiva'); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('distribuidor', row.id) : ''; }}
        ];
        if (tipo === 'diversificaciones') return [
            { data: 'nombre', title: 'Diversificación', render: function (data, renderType) { return renderType === 'display' ? '<span class="atlas-field-value">' + esc(data || 'Sin nombre') + '</span>' : (data || ''); }},
            { data: 'activo', title: 'Estatus', render: function (data, renderType, row) { return renderType === 'display' ? renderEstatus(row) : (Number(data || 0) === 1 ? 'Activa' : 'Inactiva'); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('diversificacion', row.id) : ''; }}
        ];
        return [
            { data: null, title: '', orderable: false, searchable: false, render: function () { return '<span class="atlas-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>'; }},
            { data: 'orden', title: 'Orden', render: function (data, renderType) { return renderType === 'display' ? '<span class="atlas-order-pill">' + esc(data || '-') + '</span>' : (parseInt(data, 10) || 999999); }},
            { data: 'nombre', title: 'Clasificación', render: function (data, renderType, row) { return renderType === 'display' ? '<div class="atlas-classif" style="--atlas-class-color:' + esc(colorHexSeguro(row.color_hex)) + ';"><span class="atlas-classif-dot"></span><i class="' + esc(row.icon_font || 'fa-solid fa-tags') + '"></i><span>' + esc(data || 'Sin nombre') + '</span></div>' : (data || ''); }},
            { data: 'activo', title: 'Estatus', render: function (data, renderType, row) { return renderType === 'display' ? renderEstatus(row) : (Number(data || 0) === 1 ? 'Activa' : 'Inactiva'); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('clasificacion', row.id) : ''; }}
        ];
    }
    function initDataTable(selector, columns, order) {
        if (!window.jQuery || !jQuery.fn.DataTable) return null;
        if (jQuery.fn.DataTable.isDataTable(selector)) jQuery(selector).DataTable().destroy();
        return jQuery(selector).DataTable({ pageLength: 10, lengthMenu: [[10,40,-1],[10,40,'Todos']], order: order || [], autoWidth: false, responsive: { details: { type: 'inline', target: 'tr' } }, columns: columns, language: { emptyTable: 'No hay datos disponibles', info: 'Mostrando de _START_ a _END_ de _TOTAL_ registros', infoEmpty: 'Sin registros para mostrar', zeroRecords: 'No se encontraron registros', lengthMenu: 'Mostrar _MENU_ registros', search: 'Buscar:', paginate: { first: '&laquo;', last: '&raquo;', next: '&rsaquo;', previous: '&lsaquo;' } }, destroy: true });
    }
    function ajustarTablasAtlas() {
        const tablas = [atlasTabla].concat(Object.keys(tablasCatalogo || {}).map(key => tablasCatalogo[key]));
        tablas.forEach(tabla => {
            if (!tabla || !tabla.columns) return;
            tabla.columns.adjust();
            if (tabla.responsive && typeof tabla.responsive.recalc === 'function') tabla.responsive.recalc();
        });
    }
    function renderTabla() {
        if (!atlasTabla && body) body.innerHTML = '';
        if (!atlasTabla) atlasTabla = initDataTable(tablaSelector, columnasAtlas(), [[1,'asc']]);
        if (atlasTabla) {
            atlasTabla.clear();
            atlasTabla.rows.add(sucursales);
            atlasTabla.draw();
        } else if (body) {
            body.innerHTML = sucursales.length ? sucursales.map(renderFilaSucursalFallback).join('') : '<tr><td colspan="5" class="atlas-empty">No hay datos disponibles</td></tr>';
        }
        atlasSetTablaLoading('sucursales', false);
    }
    function renderCatalogos() {
        const mapa = { divisiones: '#atlasTablaDivisiones', distribuidores: '#atlasTablaDistribuidores', diversificaciones: '#atlasTablaDiversificaciones', clasificaciones: '#atlasTablaClasificaciones' };
        Object.keys(mapa).forEach(tipo => {
            if (!tablasCatalogo[tipo]) tablasCatalogo[tipo] = initDataTable(mapa[tipo], columnasCatalogo(tipo), tipo === 'clasificaciones' ? [[1,'asc']] : [[0,'asc']]);
            const tabla = tablasCatalogo[tipo];
            if (tabla) { tabla.clear(); tabla.rows.add(catalogos[tipo] || []); tabla.draw(); }
            atlasSetTablaLoading(tipo, false);
        });
        setTimeout(initOrdenClasificaciones, 50);
    }
    async function cargarSucursales() {
        atlasSetTablaLoading('sucursales', true, 'Cargando sucursales...');
        try {
            const res = await fetch('/Atlas/getSucursales', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar.');
            sucursales = Array.isArray(data.datos) ? data.datos : [];
            const t = data.totales || {};
            setKpi('atlas-kpi-total', t.total); setKpi('atlas-kpi-activas', t.activas); setKpi('atlas-kpi-inactivas', t.inactivas); setKpi('atlas-kpi-coordenadas', t.con_coordenadas);
            atlasEvaluarCalidadSucursales();
            renderTabla();
        } catch (err) {
            if (body) body.innerHTML = '<tr><td colspan="5" class="atlas-empty">No se pudieron cargar las sucursales.</td></tr>';
            atlasSetTablaLoading('sucursales', false);
        }
    }
    async function cargarCatalogos(opciones) {
        const opts = opciones || {};
        if (!opts.silencioso) atlasSetCatalogosLoading(true);
        const valores = opts.valoresSucursal ? Object.assign({}, opts.valoresSucursal) : {};
        if (opts.seleccionar && opts.seleccionar.name) valores[opts.seleccionar.name] = String(opts.seleccionar.id || '');
        try {
            const res = await fetch('/Atlas/getCatalogos', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudieron cargar catálogos.');
            catalogos = Object.assign({ divisiones: [], divisionales: [], regionales: [], supervisores: [], asesores: [], distribuidores: [], diversificaciones: [], clasificaciones: [] }, data.datos || {});
            llenarSelect('atlas-sucursal-distribuidor', catalogos.distribuidores, 'Selecciona distribuidor');
            llenarSelect('atlas-sucursal-diversificacion', catalogos.diversificaciones, 'Selecciona diversificación');
            llenarSelect('atlas-sucursal-clasificacion', catalogos.clasificaciones, 'Selecciona clasificación');
            actualizarCascadaSucursal(valores);
            Object.keys(valores).forEach(key => setFormValue(formSucursal, key, valores[key]));
            renderCatalogos();
        } catch (err) {
            if (!opts.silencioso) atlasSetCatalogosLoading(false);
            throw err;
        }
    }
    function abrirSucursal(row) {
        if (!formSucursal) return;
        formSucursal.reset();
        const data = row || {};
        modalSucursalTitulo.innerHTML = '<i class="fa-solid fa-store me-2"></i>' + (data.id ? 'Editar sucursal' : 'Agregar sucursal');
        ['id','fk_sucursal','sucursal','distribuidor_id','diversificacion_id','clasificacion_id','direccion_sucursal','estado','municipio','localidad','codigo_postal','latitud','longitud','activo','coordenadas'].forEach(key => setFormValue(formSucursal, key, key === 'direccion_sucursal' ? (data.direccion_sucursal || data.direccion || '') : (key === 'activo' && data[key] == null ? 1 : data[key])));
        actualizarCascadaSucursal(data);
        refrescarSelectBuscadores(modalSucursalEl);
        mostrarModal(modalSucursalEl);
    }
    function clasificacionConIcono(icon, idActual) {
        const actual = String(idActual || '');
        return (catalogos.clasificaciones || []).find(row => String(row.icon_font || '').trim() === icon && String(row.id || '') !== actual) || null;
    }
    function iconoDisponibleClasificacion(idActual) { return atlasIconosClasificacion.find(icon => !clasificacionConIcono(icon, idActual)) || 'fa-solid fa-tags'; }
    function renderGaleriaIconos(iconoActual, idActual) {
        const actual = String(iconoActual || '').trim() || iconoDisponibleClasificacion(idActual);
        return '<div class="atlas-icon-gallery" role="group" aria-label="Galería de iconos">' + atlasIconosClasificacion.map(icon => {
            const usada = clasificacionConIcono(icon, idActual);
            const disabled = !!usada;
            const titulo = disabled ? 'Usado por: ' + (usada.nombre || 'Clasificación sin nombre') : icon;
            return '<button type="button" class="atlas-icon-option' + (icon === actual ? ' is-active' : '') + (disabled ? ' is-disabled' : '') + '" data-atlas-icon-option="' + esc(icon) + '"' + (disabled ? ' data-atlas-tooltip="' + esc(titulo) + '"' : '') + ' title="' + esc(titulo) + '" aria-label="' + esc(titulo) + '"' + (disabled ? ' aria-disabled="true" tabindex="0"' : '') + '><i class="' + esc(icon) + '"></i></button>';
        }).join('') + '</div>';
    }
    function camposCatalogo(tipo, data) {
        const row = data || {};
        if (tipo === 'division') return '<div><label class="form-label atlas-required">Nombre</label><input type="text" class="form-control" name="nombre" required placeholder="Nombre de la división" value="' + esc(row.nombre || '') + '"></div><div><label class="form-label atlas-required">Divisional activo</label><select class="form-select js-atlas-select-buscador" name="divisional_id" required>' + optionsCatalogo(catalogos.divisionales, row.divisional_id, 'Selecciona divisional') + '</select></div><div><label class="form-label atlas-required">Estatus</label><select class="form-select js-atlas-select-buscador" name="activo" required><option value="1"' + (Number(row.activo ?? 1) === 1 ? ' selected' : '') + '>Activa</option><option value="0"' + (Number(row.activo ?? 1) === 0 ? ' selected' : '') + '>Inactiva</option></select></div>';
        if (tipo === 'distribuidor' || tipo === 'diversificacion') {
            const label = tipo === 'diversificacion' ? 'Diversificación' : 'Nombre';
            const placeholder = tipo === 'diversificacion' ? 'Nombre de la diversificación' : 'Nombre del distribuidor';
            return '<div class="atlas-catalog-inline"><div><label class="form-label atlas-required">' + label + '</label><input type="text" class="form-control" name="nombre" required placeholder="' + esc(placeholder) + '" value="' + esc(row.nombre || '') + '"></div><div><label class="form-label atlas-required">Estatus</label><select class="form-select js-atlas-select-buscador" name="activo" required><option value="1"' + (Number(row.activo ?? 1) === 1 ? ' selected' : '') + '>Activa</option><option value="0"' + (Number(row.activo ?? 1) === 0 ? ' selected' : '') + '>Inactiva</option></select></div></div>';
        }
        const colorActual = colorHexSeguro(row.color_hex || '#94A3B8');
        const idActual = row.id || '';
        const iconoActual = String(row.icon_font || iconoDisponibleClasificacion(idActual)).trim();
        return '<div><label class="form-label atlas-required">Nombre</label><input type="text" class="form-control" name="nombre" required placeholder="Nombre de la clasificación" value="' + esc(row.nombre || '') + '"></div><div class="atlas-field-wide"><label class="form-label atlas-required">Icono</label><input type="hidden" name="icon_font" id="atlas-catalogo-icon-font" required value="' + esc(iconoActual) + '">' + renderGaleriaIconos(iconoActual, idActual) + '</div><div><label class="form-label atlas-required">Color</label><div class="atlas-color-input-wrap"><input type="color" name="color_hex" id="atlas-catalogo-color" required value="' + esc(colorActual) + '"><span class="atlas-muted" id="atlas-catalogo-color-label">' + esc(colorActual) + '</span></div></div><div><label class="form-label atlas-required">Estatus</label><select class="form-select js-atlas-select-buscador" name="activo" required><option value="1"' + (Number(row.activo ?? 1) === 1 ? ' selected' : '') + '>Activa</option><option value="0"' + (Number(row.activo ?? 1) === 0 ? ' selected' : '') + '>Inactiva</option></select></div>';
    }
    function abrirCatalogo(tipo, row, opciones) {
        formCatalogo.reset();
        const opts = opciones || {};
        atlasQuickAddContext = opts.quickAdd ? { tipo: tipo, target: opts.target || '', snapshot: Object.assign({}, opts.snapshot || {}), guardado: false } : null;
        const data = Object.assign({}, row || {}, opts.prefill || {});
        const titulos = { division: ['fa-solid fa-diagram-project','división'], distribuidor: ['fa-solid fa-building','distribuidor'], diversificacion: ['fa-solid fa-layer-group','diversificación'], clasificacion: ['fa-solid fa-tags','clasificación'] };
        const cfg = titulos[tipo] || titulos.clasificacion;
        document.getElementById('atlas-catalogo-id').value = data.id || '';
        document.getElementById('atlas-catalogo-tipo').value = tipo;
        modalCatalogoTitulo.innerHTML = '<i class="' + cfg[0] + ' me-2"></i>' + (data.id ? 'Editar ' : 'Agregar ') + cfg[1];
        catalogoFields.innerHTML = camposCatalogo(tipo, data);
        refrescarSelectBuscadores(modalCatalogoEl);
        mostrarModal(modalCatalogoEl);
    }
    function buscarPorTipo(tipo, id) {
        const key = tipo === 'division' ? 'divisiones' : (tipo === 'distribuidor' ? 'distribuidores' : (tipo === 'diversificacion' ? 'diversificaciones' : 'clasificaciones'));
        return (catalogos[key] || []).find(row => String(row.id || '') === String(id || '')) || null;
    }
    function abrirCatalogoRapido(tipo, target, prefill) {
        const snapshot = formSucursal ? formToJson(formSucursal) : {};
        const abrir = () => abrirCatalogo(tipo, null, { quickAdd: true, target: target, prefill: prefill || {}, snapshot: snapshot });
        if (modalSucursalEl && modalSucursalEl.classList.contains('show')) {
            let abierto = false;
            const abrirUnaVez = function () {
                if (abierto) return;
                abierto = true;
                abrir();
            };
            modalSucursalEl.addEventListener('hidden.bs.modal', abrirUnaVez, { once: true });
            cerrarModal(modalSucursalEl);
            setTimeout(function () {
                if (!modalCatalogoEl || !modalCatalogoEl.classList.contains('show')) abrirUnaVez();
            }, 360);
            return;
        }
        abrir();
    }
    function refrescarCombosSucursal(valores) {
        const v = valores || valoresSucursalActuales();
        llenarSelect('atlas-sucursal-distribuidor', catalogos.distribuidores, 'Selecciona distribuidor');
        llenarSelect('atlas-sucursal-diversificacion', catalogos.diversificaciones, 'Selecciona diversificación');
        llenarSelect('atlas-sucursal-clasificacion', catalogos.clasificaciones, 'Selecciona clasificación');
        actualizarCascadaSucursal(v);
        Object.keys(v).forEach(key => setFormValue(formSucursal, key, v[key]));
    }
    function agregarCatalogoLocal(tipo, payload, id) {
        const nuevoId = String(id || '');
        if (!nuevoId) return;
        const activo = Number(payload.activo ?? 1) === 1 ? 1 : 0;
        const nombre = String(payload.nombre || '').trim();
        if (!nombre) return;
        if (tipo === 'distribuidor') {
            catalogos.distribuidores = (catalogos.distribuidores || []).filter(row => String(row.id || '') !== nuevoId).concat([{ id: nuevoId, nombre: nombre, activo: activo }]);
        } else if (tipo === 'diversificacion') {
            catalogos.diversificaciones = (catalogos.diversificaciones || []).filter(row => String(row.id || '') !== nuevoId).concat([{ id: nuevoId, nombre: nombre, activo: activo }]);
        } else if (tipo === 'division') {
            const divisionalId = String(payload.divisional_id || '');
            const divisional = findCatalogo('divisionales', divisionalId);
            catalogos.divisiones = (catalogos.divisiones || []).filter(row => String(row.id || '') !== nuevoId).concat([{ id: nuevoId, nombre: nombre, activo: activo, divisional_id: divisionalId, divisional_nombre: divisional ? divisional.nombre : '' }]);
        } else if (tipo === 'clasificacion') {
            const ordenes = (catalogos.clasificaciones || []).map(row => parseInt(row.orden, 10) || 0);
            const siguienteOrden = Math.max(0, ...ordenes) + 1;
            catalogos.clasificaciones = (catalogos.clasificaciones || []).filter(row => String(row.id || '') !== nuevoId).concat([{
                id: nuevoId,
                nombre: nombre,
                activo: activo,
                icon_font: payload.icon_font || 'fa-solid fa-tags',
                color_hex: colorHexSeguro(payload.color_hex || '#94A3B8'),
                orden: siguienteOrden
            }]);
        }
        renderCatalogos();
    }
    function restaurarSucursalRapido(snapshot, seleccionar) {
        const valores = Object.assign({}, snapshot || {});
        if (seleccionar && seleccionar.name) valores[seleccionar.name] = String(seleccionar.id || '');
        refrescarCombosSucursal(valores);
        mostrarModal(modalSucursalEl);
    }
    function restaurarSucursalDesdeQuickAdd(ctx) {
        if (!ctx || !ctx.target || !formSucursal) return;
        const snapshot = Object.assign({}, ctx.snapshot || {});
        restaurarSucursalRapido(snapshot);
    }
    function restaurarSucursalDesdeDireccion() {
        const ctx = atlasDireccionContext;
        if (!ctx || !formSucursal) return;
        atlasDireccionContext = null;
        restaurarSucursalRapido(Object.assign({}, ctx.snapshot || {}));
    }
    async function cargarGoogleMapsAtlas(requierePlaces) {
        if (typeof google !== 'undefined' && google.maps && (!requierePlaces || google.maps.places)) return;
        if (atlasMapaCargando) return atlasMapaCargando;
        const apiKey = typeof window.ATLAS_GOOGLE_MAPS_KEY === 'string' ? window.ATLAS_GOOGLE_MAPS_KEY.trim() : '';
        if (!apiKey) throw new Error('Falta GOOGLE_MAPS_API_KEY en configuración.');
        atlasMapaCargando = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&libraries=places&language=es&region=MX';
            script.async = true; script.defer = true;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Error al cargar Google Maps.'));
            document.head.appendChild(script);
        });
        return atlasMapaCargando;
    }
    function componenteDireccionGoogle(componentes, tipo) { const row = (componentes || []).find(item => Array.isArray(item.types) && item.types.indexOf(tipo) !== -1); return row ? String(row.long_name || row.short_name || '').trim() : ''; }
    function normalizarTextoDireccionGoogle(valor) { return String(valor || '').trim().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase(); }
    function normalizarEstadoGoogle(valor) {
        const estado = String(valor || '').trim();
        const mapa = { 'state of mexico': 'Estado de México', 'mexico state': 'Estado de México', 'estado de mexico': 'Estado de México', 'mexico city': 'Ciudad de México', 'ciudad de mexico': 'Ciudad de México' };
        return mapa[normalizarTextoDireccionGoogle(estado)] || estado;
    }
    function extraerDireccionGoogle(componentes) {
        return { estado: normalizarEstadoGoogle(componenteDireccionGoogle(componentes, 'administrative_area_level_1')), municipio: componenteDireccionGoogle(componentes, 'locality') || componenteDireccionGoogle(componentes, 'administrative_area_level_2'), localidad: componenteDireccionGoogle(componentes, 'neighborhood') || componenteDireccionGoogle(componentes, 'sublocality_level_1') || componenteDireccionGoogle(componentes, 'sublocality'), codigo_postal: componenteDireccionGoogle(componentes, 'postal_code') };
    }
    function setDireccionSeleccionada(lat, lng, direccion, componentes, centrar) {
        const extra = extraerDireccionGoogle(componentes || []);
        atlasDireccionActual = { lat: lat, lng: lng, direccion: String(direccion || '').trim(), estado: extra.estado, municipio: extra.municipio, localidad: extra.localidad, codigo_postal: extra.codigo_postal };
        const punto = { lat: lat, lng: lng };
        if (!atlasDireccionMarker) {
            atlasDireccionMarker = new google.maps.Marker({ map: atlasDireccionMapa, position: punto, draggable: true, title: 'Arrastra el pin para ajustar la ubicación' });
            atlasDireccionMarker.addListener('dragend', function () { const pos = atlasDireccionMarker.getPosition(); setDireccionSeleccionada(pos.lat(), pos.lng(), atlasDireccionActual ? atlasDireccionActual.direccion : '', [], false); });
        } else atlasDireccionMarker.setPosition(punto);
        if (direccionBusqueda && atlasDireccionActual.direccion) direccionBusqueda.value = atlasDireccionActual.direccion;
        if (atlasDireccionMapa && centrar !== false) { atlasDireccionMapa.setCenter(punto); atlasDireccionMapa.setZoom(17); }
        if (btnConfirmarDireccion) btnConfirmarDireccion.disabled = false;
        if (direccionCoordenadas) direccionCoordenadas.textContent = 'Coordenadas: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
    }
    async function inicializarModalDireccion() {
        await cargarGoogleMapsAtlas(true);
        atlasDireccionGeocoder = atlasDireccionGeocoder || new google.maps.Geocoder();
        if (!atlasDireccionMapa) atlasDireccionMapa = new google.maps.Map(direccionMapaCont, { center: { lat: 23.6345, lng: -102.5528 }, zoom: 5, mapTypeControl: false, streetViewControl: false, gestureHandling: 'greedy' });
        if (direccionBusqueda && !atlasDireccionAutocomplete) {
            atlasDireccionAutocomplete = new google.maps.places.Autocomplete(direccionBusqueda, { fields: ['formatted_address','geometry','address_components'], componentRestrictions: { country: 'mx' } });
            atlasDireccionAutocomplete.addListener('place_changed', function () {
                const place = atlasDireccionAutocomplete.getPlace();
                if (!place || !place.geometry || !place.geometry.location) return;
                setDireccionSeleccionada(place.geometry.location.lat(), place.geometry.location.lng(), place.formatted_address || direccionBusqueda.value, place.address_components || [], true);
            });
        }
        const lat = numeroValido(getFormValue(formSucursal, 'latitud')), lng = numeroValido(getFormValue(formSucursal, 'longitud'));
        if (lat != null && lng != null) setDireccionSeleccionada(lat, lng, getFormValue(formSucursal, 'direccion_sucursal'), [], true);
        else { if (btnConfirmarDireccion) btnConfirmarDireccion.disabled = true; if (direccionCoordenadas) direccionCoordenadas.textContent = 'Selecciona una dirección para colocar el pin.'; }
        setTimeout(() => { if (direccionBusqueda) direccionBusqueda.focus(); }, 250);
    }
    function abrirDireccionGoogle() { if (direccionBusqueda) direccionBusqueda.value = getFormValue(formSucursal, 'direccion_sucursal'); atlasDireccionActual = null; mostrarModal(modalDireccionEl); }
    function confirmarDireccionGoogle() {
        if (!formSucursal || !atlasDireccionActual) return;
        setFormValue(formSucursal, 'direccion_sucursal', atlasDireccionActual.direccion);
        setFormValue(formSucursal, 'latitud', atlasDireccionActual.lat.toFixed(7));
        setFormValue(formSucursal, 'longitud', atlasDireccionActual.lng.toFixed(7));
        setFormValue(formSucursal, 'coordenadas', atlasDireccionActual.lat.toFixed(7) + ',' + atlasDireccionActual.lng.toFixed(7));
        ['estado','municipio','localidad','codigo_postal'].forEach(key => { if (atlasDireccionActual[key]) setFormValue(formSucursal, key, atlasDireccionActual[key]); });
        cerrarModal(modalDireccionEl);
    }

    function setDireccionStatus(texto, habilitar) {
        if (direccionCoordenadas) direccionCoordenadas.textContent = texto;
        if (btnConfirmarDireccion) btnConfirmarDireccion.disabled = !habilitar;
    }
    function setDireccionSeleccionada(lat, lng, direccion, componentes, centrar) {
        const extra = extraerDireccionGoogle(componentes || []);
        atlasDireccionActual = { lat: lat, lng: lng, direccion: String(direccion || '').trim(), estado: extra.estado, municipio: extra.municipio, localidad: extra.localidad, codigo_postal: extra.codigo_postal };
        const punto = { lat: lat, lng: lng };
        if (!atlasDireccionMarker) {
            atlasDireccionMarker = new google.maps.Marker({ map: atlasDireccionMapa, position: punto, draggable: true, icon: iconoPinDireccionActual(), title: 'Arrastra el pin para ajustar la ubicación' });
            atlasDireccionMarker.addListener('dragend', function () {
                const pos = atlasDireccionMarker.getPosition();
                if (pos) resolverDireccionPorCoordenadas(pos.lat(), pos.lng(), atlasDireccionActual ? atlasDireccionActual.direccion : '', false);
            });
        } else {
            atlasDireccionMarker.setPosition(punto);
        }
        actualizarMarkerDireccionIcono();
        if (direccionBusqueda && atlasDireccionActual.direccion) direccionBusqueda.value = atlasDireccionActual.direccion;
        if (atlasDireccionMapa && centrar !== false) { atlasDireccionMapa.setCenter(punto); atlasDireccionMapa.setZoom(17); }
        setDireccionStatus('Coordenadas: ' + lat.toFixed(6) + ', ' + lng.toFixed(6), !!atlasDireccionActual.direccion);
    }
    function resolverDireccionPorCoordenadas(lat, lng, direccionFallback, centrar) {
        if (!atlasDireccionGeocoder) {
            setDireccionSeleccionada(lat, lng, direccionFallback || '', [], centrar);
            return;
        }
        setDireccionStatus('Resolviendo dirección...', false);
        atlasDireccionGeocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
            if (status === 'OK' && results && results[0]) {
                setDireccionSeleccionada(lat, lng, results[0].formatted_address || direccionFallback || '', results[0].address_components || [], centrar);
                return;
            }
            setDireccionSeleccionada(lat, lng, direccionFallback || '', [], centrar);
        });
    }
    function resolverDireccionPorTexto() {
        const texto = direccionBusqueda ? String(direccionBusqueda.value || '').trim() : '';
        if (!texto || !atlasDireccionGeocoder) return;
        setDireccionStatus('Buscando dirección...', false);
        atlasDireccionGeocoder.geocode({ address: texto, componentRestrictions: { country: 'MX' } }, function (results, status) {
            if (status === 'OK' && results && results[0] && results[0].geometry && results[0].geometry.location) {
                const loc = results[0].geometry.location;
                setDireccionSeleccionada(loc.lat(), loc.lng(), results[0].formatted_address || texto, results[0].address_components || [], true);
                return;
            }
            setDireccionStatus('No se encontró la dirección. Ajusta la búsqueda.', false);
        });
    }
    async function inicializarModalDireccion() {
        await cargarGoogleMapsAtlas(true);
        atlasDireccionGeocoder = atlasDireccionGeocoder || new google.maps.Geocoder();
        if (!atlasDireccionMapa) {
            atlasDireccionMapa = new google.maps.Map(direccionMapaCont, { center: { lat: 23.6345, lng: -102.5528 }, zoom: 5, mapTypeControl: false, streetViewControl: false, gestureHandling: 'greedy' });
        }
        if (atlasDireccionMapa && !atlasDireccionMapClickReady) {
            atlasDireccionMapClickReady = true;
            atlasDireccionMapa.addListener('click', function (ev) {
                if (!ev || !ev.latLng) return;
                resolverDireccionPorCoordenadas(ev.latLng.lat(), ev.latLng.lng(), direccionBusqueda ? direccionBusqueda.value : '', true);
            });
        }
        if (direccionBusqueda && !atlasDireccionAutocomplete) {
            direccionBusqueda.setAttribute('autocomplete', 'off');
            atlasDireccionAutocomplete = new google.maps.places.Autocomplete(direccionBusqueda, { fields: ['formatted_address','geometry','address_components'], componentRestrictions: { country: 'mx' } });
            atlasDireccionAutocomplete.addListener('place_changed', function () {
                const place = atlasDireccionAutocomplete.getPlace();
                if (!place || !place.geometry || !place.geometry.location) return;
                setDireccionSeleccionada(place.geometry.location.lat(), place.geometry.location.lng(), place.formatted_address || direccionBusqueda.value, place.address_components || [], true);
            });
        }
        const lat = numeroValido(getFormValue(formSucursal, 'latitud'));
        const lng = numeroValido(getFormValue(formSucursal, 'longitud'));
        if (lat != null && lng != null) {
            setDireccionSeleccionada(lat, lng, getFormValue(formSucursal, 'direccion_sucursal'), [], true);
        } else {
            limpiarDireccionMarker();
            atlasDireccionMapa.setCenter({ lat: 23.6345, lng: -102.5528 });
            atlasDireccionMapa.setZoom(5);
            setDireccionStatus('Selecciona una dirección para colocar el pin.', false);
        }
        setTimeout(function () { if (atlasDireccionMapa && google.maps.event) google.maps.event.trigger(atlasDireccionMapa, 'resize'); }, 120);
        setTimeout(function () { if (direccionBusqueda) direccionBusqueda.focus(); }, 250);
    }
    function abrirDireccionGoogle() {
        if (modalDireccionEl && modalDireccionEl.classList.contains('show')) return;
        const snapshot = formSnapshot(formSucursal);
        atlasDireccionContext = { snapshot: snapshot };
        if (direccionBusqueda) direccionBusqueda.value = snapshot.direccion_sucursal || '';
        atlasDireccionActual = null;
        const abrir = function () { mostrarModal(modalDireccionEl); };
        if (modalSucursalEl && modalSucursalEl.classList.contains('show')) {
            let abierto = false;
            const abrirUnaVez = function () {
                if (abierto) return;
                abierto = true;
                abrir();
            };
            modalSucursalEl.addEventListener('hidden.bs.modal', abrirUnaVez, { once: true });
            cerrarModal(modalSucursalEl);
            setTimeout(function () {
                if (!modalDireccionEl || !modalDireccionEl.classList.contains('show')) abrirUnaVez();
            }, 260);
            return;
        }
        abrir();
    }
    function confirmarDireccionGoogle() {
        if (!formSucursal || !atlasDireccionActual || !atlasDireccionActual.direccion) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Selecciona una direccion', text: 'Elige una sugerencia, presiona Enter o da clic en el mapa.' });
            return;
        }
        const valores = {
            direccion_sucursal: atlasDireccionActual.direccion,
            latitud: atlasDireccionActual.lat.toFixed(7),
            longitud: atlasDireccionActual.lng.toFixed(7),
            coordenadas: atlasDireccionActual.lat.toFixed(7) + ',' + atlasDireccionActual.lng.toFixed(7)
        };
        ['estado','municipio','localidad','codigo_postal'].forEach(key => { if (atlasDireccionActual[key]) valores[key] = atlasDireccionActual[key]; });
        Object.keys(valores).forEach(key => setFormValue(formSucursal, key, valores[key]));
        if (atlasDireccionContext && atlasDireccionContext.snapshot) Object.assign(atlasDireccionContext.snapshot, valores);
        cerrarModal(modalDireccionEl);
    }
    function initOrdenClasificaciones() {
        const tbody = document.querySelector('#atlasTablaClasificaciones tbody');
        if (!tbody || typeof Sortable === 'undefined') return;
        if (atlasClasificacionesSortable) atlasClasificacionesSortable.destroy();
        atlasClasificacionesSortable = Sortable.create(tbody, { handle: '.atlas-drag-handle', animation: 120, onEnd: guardarOrdenClasificaciones });
    }
    async function guardarOrdenClasificaciones() {
        const ids = Array.from(document.querySelectorAll('#atlasTablaClasificaciones tbody tr')).map(tr => {
            const data = tablasCatalogo.clasificaciones ? tablasCatalogo.clasificaciones.row(tr).data() : null;
            return data ? parseInt(data.id, 10) || 0 : 0;
        }).filter(Boolean);
        if (!ids.length) return;
        catalogos.clasificaciones = catalogos.clasificaciones.map(row => Object.assign({}, row, { orden: ids.indexOf(parseInt(row.id, 10)) + 1 || row.orden }));
        renderCatalogos();
        try { await guardarJson('/Atlas/guardarOrdenClasificaciones', { ids: ids }); } catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo guardar el orden', text: err.message || 'Error' }); }
    }
    function renderMapaSimple(row, containerId) {
        const punto = sucursalConCoordenadas(row);
        if (!punto) return;
        cargarGoogleMapsAtlas(false).then(() => {
            const el = document.getElementById(containerId); if (!el) return;
            const map = new google.maps.Map(el, { center: { lat: punto._lat, lng: punto._lng }, zoom: 16, mapTypeControl: false, streetViewControl: false, gestureHandling: 'greedy' });
            crearMarkerAtlas(map, row, { lat: punto._lat, lng: punto._lng });
        }).catch(() => {});
    }
    function abrirModalUbicacion(idSucursal) {
        const row = sucursales.find(r => String(r.id || '') === String(idSucursal || '')); if (!row) return;
        document.getElementById('atlas-ubicacion-titulo').textContent = row.sucursal || 'Ubicación';
        document.getElementById('atlas-ubicacion-direccion').textContent = atlasNormalizarTexto(row.direccion || 'Sin dirección capturada');
        document.getElementById('atlas-ubicacion-coordenadas').textContent = (row.latitud && row.longitud) ? row.latitud + ', ' + row.longitud : 'Sin coordenadas';
        mostrarModal(modalUbicacionEl);
        setTimeout(() => renderMapaSimple(row, 'atlas-ubicacion-mapa'), 250);
    }
    function renderCalidadItem(item) {
        const row = item.row || {};
        const razones = (item.issues || []).map(issue => {
            const clase = issue.severidad === 'error' ? 'is-error' : (issue.severidad === 'warning' ? 'is-warning' : 'is-info');
            return '<span class="atlas-quality-reason ' + clase + '"><i class="' + esc(issue.icono || 'fa-solid fa-circle-info') + '"></i>' + esc(issue.titulo || 'Revisar') + '</span>';
        }).join('');
        const detalles = (item.issues || []).map(issue => '<div class="atlas-quality-sub"><i class="' + esc(issue.icono || 'fa-solid fa-circle-info') + ' me-1"></i>' + esc(issue.detalle || issue.titulo || 'Revisar dato') + '</div>').join('');
        const coordenadas = row.latitud && row.longitud ? row.latitud + ', ' + row.longitud : 'Sin coordenadas';
        return '<div class="atlas-quality-item">'
            + '<div class="atlas-quality-head">'
            + '<div><div class="atlas-quality-title">' + esc(row.sucursal || 'Sucursal sin nombre') + '</div>'
            + '<div class="atlas-quality-sub">FK ' + esc(row.fk_sucursal || '-') + ' · ' + esc(row.distribuidor_nombre || 'Sin distribuidor') + ' · ' + esc(coordenadas) + '</div></div>'
            + '<div class="atlas-quality-actions"><button type="button" class="btn btn-sm btn-primary" data-atlas-editar-calidad="' + esc(row.id || '') + '"><i class="fa-solid fa-pen-to-square me-1"></i>Editar</button></div>'
            + '</div>'
            + '<div class="atlas-quality-reasons">' + razones + '</div>'
            + '<div class="mt-2">' + detalles + '</div>'
            + '</div>';
    }
    function abrirModalCalidad(tipo) {
        const calidad = atlasEvaluarCalidadSucursales();
        const esSinCoordenadas = tipo === 'sin-coordenadas';
        const datos = esSinCoordenadas ? calidad.sinCoordenadas : calidad.errores;
        if (modalCalidadTitulo) {
            modalCalidadTitulo.innerHTML = esSinCoordenadas
                ? '<i class="fa-solid fa-map-pin me-2"></i>Sucursales sin coordenadas'
                : '<i class="fa-solid fa-triangle-exclamation me-2"></i>Sucursales con error';
        }
        if (calidadResumen) {
            calidadResumen.textContent = esSinCoordenadas
                ? 'Sucursales activas sin ubicación útil para rutas. Corrige la dirección y confirma el pin en el mapa.'
                : 'Errores detectados en datos críticos para rutas: duplicados, ubicación compartida, datos faltantes o coordenadas inválidas.';
        }
        if (calidadLista) {
            calidadLista.innerHTML = datos.length
                ? datos.map(renderCalidadItem).join('')
                : '<div class="atlas-empty"><i class="fa-solid fa-circle-check d-block mb-2"></i>No hay registros para esta revisión.</div>';
        }
        mostrarModal(modalCalidadEl);
    }
    function renderMapa() {
        const puntos = sucursales.map(sucursalConCoordenadas).filter(Boolean);
        mostrarModal(modalMapaEl);
        cargarGoogleMapsAtlas(false).then(() => {
            const el = document.getElementById('atlas-mapa-sucursales'); if (!el) return;
            const map = new google.maps.Map(el, { center: { lat: 23.6345, lng: -102.5528 }, zoom: 5, mapTypeControl: false, streetViewControl: false, gestureHandling: 'greedy' });
            const bounds = new google.maps.LatLngBounds();
            puntos.forEach(row => { const pos = { lat: row._lat, lng: row._lng }; bounds.extend(pos); crearMarkerAtlas(map, row, pos); });
            if (puntos.length) map.fitBounds(bounds);
            const meta = document.getElementById('atlas-mapa-meta'); if (meta) meta.textContent = puntos.length + ' sucursales con coordenadas.';
            renderLeyendaMapa(puntos);
        }).catch(err => { const meta = document.getElementById('atlas-mapa-meta'); if (meta) meta.textContent = err.message || 'No se pudo cargar el mapa.'; });
    }
    function validarSucursalObligatoria() {
        const campos = ['sucursal','distribuidor_id','clasificacion_id','divisional_id','division_id','regional_id','supervisor_id','asesor_id','diversificacion_id','direccion_sucursal','estado','municipio','localidad','codigo_postal','latitud','longitud','activo'];
        const faltantes = campos.filter(name => !getFormValue(formSucursal, name));
        if (faltantes.length) throw new Error('Completa todos los campos obligatorios.');
    }
    document.addEventListener('click', function (ev) {
        const iconOption = ev.target.closest('[data-atlas-icon-option]');
        if (iconOption) {
            ev.preventDefault();
            if (iconOption.classList.contains('is-disabled')) return;
            const icon = iconOption.getAttribute('data-atlas-icon-option') || '';
            const input = document.getElementById('atlas-catalogo-icon-font'); if (input) input.value = icon;
            document.querySelectorAll('.atlas-icon-option').forEach(btn => btn.classList.toggle('is-active', btn === iconOption));
            return;
        }
        const quickAdd = ev.target.closest('[data-atlas-quick-add]');
        if (quickAdd) {
            ev.preventDefault();
            const tipo = quickAdd.getAttribute('data-atlas-quick-add') || '';
            const target = quickAdd.getAttribute('data-atlas-target') || '';
            const prefill = {};
            if (tipo === 'division') {
                const divisionalId = getFormValue(formSucursal, 'divisional_id');
                if (!divisionalId) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'Selecciona un divisional', text: 'La división se agrega dentro del divisional elegido.' }); return; }
                prefill.divisional_id = divisionalId;
            }
            abrirCatalogoRapido(tipo, target, prefill);
            return;
        }
        const agregar = ev.target.closest('[data-atlas-agregar]');
        if (agregar) { ev.preventDefault(); const tipo = agregar.getAttribute('data-atlas-agregar'); if (tipo === 'sucursal') abrirSucursal(null); else abrirCatalogo(tipo, null); return; }
        const editar = ev.target.closest('[data-atlas-editar]');
        if (editar) {
            ev.preventDefault();
            const tipo = editar.getAttribute('data-atlas-editar'), id = editar.getAttribute('data-id');
            if (tipo === 'sucursal') abrirSucursal(sucursales.find(row => String(row.id || '') === String(id || '')) || null);
            else abrirCatalogo(tipo, buscarPorTipo(tipo, id));
            return;
        }
        const ubicacion = ev.target.closest('[data-atlas-ubicacion]');
        if (ubicacion) { ev.preventDefault(); abrirModalUbicacion(ubicacion.getAttribute('data-atlas-ubicacion')); }
        const editarCalidad = ev.target.closest('[data-atlas-editar-calidad]');
        if (editarCalidad) {
            ev.preventDefault();
            const id = editarCalidad.getAttribute('data-atlas-editar-calidad');
            const row = sucursales.find(item => String(item.id || '') === String(id || '')) || null;
            cerrarModal(modalCalidadEl);
            if (row) setTimeout(() => abrirSucursal(row), 180);
            return;
        }
    });
    document.addEventListener('input', function (ev) {
        if (!ev.target) return;
        if (ev.target.id === 'atlas-catalogo-color') {
            const label = document.getElementById('atlas-catalogo-color-label');
            if (label) label.textContent = colorHexSeguro(ev.target.value);
        }
    });
    document.addEventListener('change', function (ev) {
        if (ev.target && ev.target.name === 'clasificacion_id') actualizarMarkerDireccionIcono();
    });
    if (formSucursal) {
        ['divisional_id','division_id','regional_id','supervisor_id'].forEach(name => {
            const el = formSucursal.elements[name];
            if (!el) return;
            el.addEventListener('change', function () {
                const values = valoresSucursalActuales();
                if (name === 'divisional_id') { values.division_id = ''; values.regional_id = ''; values.supervisor_id = ''; values.asesor_id = ''; }
                if (name === 'division_id') { values.regional_id = ''; values.supervisor_id = ''; values.asesor_id = ''; }
                if (name === 'regional_id') { values.supervisor_id = ''; values.asesor_id = ''; }
                if (name === 'supervisor_id') values.asesor_id = '';
                actualizarCascadaSucursal(values);
            });
        });
        const dir = formSucursal.elements.direccion_sucursal;
        if (dir) dir.addEventListener('click', abrirDireccionGoogle);
        formSucursal.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            try { validarSucursalObligatoria(); await guardarJson('/Atlas/guardarSucursal', formToJson(formSucursal)); cerrarModal(modalSucursalEl); await cargarSucursales(); }
            catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: err.message || 'Error' }); }
        });
    }
    if (modalDireccionEl) modalDireccionEl.addEventListener('shown.bs.modal', function () { inicializarModalDireccion().catch(err => { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'No se pudo abrir el mapa', text: err.message || 'Revisa la configuración de Google Maps.' }); }); });
    if (modalDireccionEl) modalDireccionEl.addEventListener('hidden.bs.modal', restaurarSucursalDesdeDireccion);
    if (btnConfirmarDireccion) btnConfirmarDireccion.addEventListener('click', confirmarDireccionGoogle);
    if (direccionBusqueda) direccionBusqueda.addEventListener('keydown', ev => { if (ev.key === 'Enter') ev.preventDefault(); });
    if (direccionBusqueda) direccionBusqueda.addEventListener('keydown', function (ev) { if (ev.key === 'Enter') { ev.preventDefault(); resolverDireccionPorTexto(); } });
    if (btnMapa) btnMapa.addEventListener('click', renderMapa);
    if (btnErrores) btnErrores.addEventListener('click', function () { abrirModalCalidad('errores'); });
    if (btnSinCoordenadas) btnSinCoordenadas.addEventListener('click', function () { abrirModalCalidad('sin-coordenadas'); });
    if (btnRecargar) btnRecargar.addEventListener('click', function () { Promise.all([cargarCatalogos(), cargarSucursales()]).catch(() => {}); });
    document.querySelectorAll('#atlas-tabs [data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function () { setTimeout(ajustarTablasAtlas, 80); });
    });
    window.addEventListener('resize', function () { setTimeout(ajustarTablasAtlas, 80); });
    if (modalCatalogoEl) {
        modalCatalogoEl.addEventListener('hidden.bs.modal', function () {
            const ctx = atlasQuickAddContext;
            if (!ctx || ctx.guardado) return;
            atlasQuickAddContext = null;
            restaurarSucursalDesdeQuickAdd(ctx);
        });
    }
    if (formCatalogo) {
        formCatalogo.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            const payload = formToJson(formCatalogo), tipo = payload.tipo;
            const urls = { division: '/Atlas/guardarDivision', distribuidor: '/Atlas/guardarDistribuidor', diversificacion: '/Atlas/guardarDiversificacion', clasificacion: '/Atlas/guardarClasificacion' };
            try {
                const ctx = atlasQuickAddContext;
                const data = await guardarJson(urls[tipo], payload);
                if (atlasQuickAddContext) atlasQuickAddContext.guardado = true;
                cerrarModal(modalCatalogoEl);
                if (ctx && ctx.target) {
                    atlasQuickAddContext = null;
                    agregarCatalogoLocal(tipo, payload, data.id);
                    atlasOcultarLoaderGlobal();
                    restaurarSucursalRapido(ctx.snapshot || {}, { name: ctx.target, id: data.id });
                    return;
                }
                atlasQuickAddContext = null;
                await cargarCatalogos();
                await cargarSucursales();
            } catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: err.message || 'Error' }); }
        });
    }
    Promise.all([cargarCatalogos(), cargarSucursales()]).catch(err => { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo cargar Atlas', text: err.message || 'Error' }); });
})();
</script>

