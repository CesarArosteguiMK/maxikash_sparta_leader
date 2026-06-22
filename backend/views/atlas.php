<script>window.ATLAS_GOOGLE_MAPS_KEY = <?= $google_maps_api_key_js ?? '""' ?>;</script>

<script>
window.ATLAS_PERMISOS_SUCURSAL = <?= json_encode($atlas_permisos_sucursal ?? ['paso1' => false, 'paso2' => false], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php
$atlasPermisosSucursalVista = is_array($atlas_permisos_sucursal ?? null) ? $atlas_permisos_sucursal : ['paso1' => false, 'paso2' => false];
$atlasPuedeAgregarSucursal = !empty($atlasPermisosSucursalVista['paso1']);
?>

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
        .atlas-subtabs-wrap { border: 1px solid #dbe4ef; border-radius: .75rem; background: #f8fafc; padding: .7rem .75rem; margin-bottom: .9rem; }
        .atlas-subtabs { gap: .45rem; flex-wrap: wrap; }
        .atlas-subtabs .nav-link { border: 1px solid #dbe4ef; border-radius: 999px; background: #fff; color: #64748b; font-size: .78rem; font-weight: 900; padding: .42rem .8rem; display: inline-flex; align-items: center; gap: .35rem; }
        .atlas-subtabs .nav-link.active { background: #173756; border-color: #173756; color: #fff; box-shadow: 0 8px 18px rgba(23, 55, 86, .14); }
        .atlas-subtabs .nav-link.active,
        .atlas-subtabs .nav-link.active i,
        .atlas-subtabs .nav-link.active span { color: #fff !important; }
        .atlas-panel-head { display: flex; align-items: center; justify-content: flex-end; gap: .8rem; margin-bottom: .85rem; }
        .atlas-quality-config { display: flex; align-items: center; justify-content: space-between; gap: .85rem; border: 1px solid #dbe4ef; border-radius: .65rem; background: #f8fafc; padding: .65rem .75rem; margin-bottom: .75rem; }
        .atlas-quality-config-title { display: flex; align-items: center; gap: .45rem; color: #173756; font-size: .78rem; font-weight: 900; text-transform: uppercase; letter-spacing: .03em; }
        .atlas-quality-config-title i { color: #b91c1c; }
        .atlas-quality-config .form-check { display: flex; align-items: center; gap: .45rem; min-height: auto; margin: 0; padding-left: 0; }
        .atlas-quality-config .form-check-input { margin: 0; width: 1.05rem; height: 1.05rem; flex: 0 0 auto; }
        .atlas-quality-config .form-check-label { color: #334155; font-size: .76rem; font-weight: 800; line-height: 1.2; }
        .atlas-quality-config-status { color: #64748b; font-size: .68rem; font-weight: 800; min-width: 4.5rem; text-align: right; }
        .atlas-quality-config--inline { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: .28rem .45rem; border: 0; border-top: 1px solid #fecaca; border-radius: 0; background: transparent; padding: .48rem 0 0; margin: .55rem 0 0; }
        .atlas-quality-config--inline .atlas-quality-config-title { grid-column: 1 / -1; color: #991b1b; font-size: .6rem; letter-spacing: .02em; }
        .atlas-quality-config--inline .form-check-label { color: #7f1d1d; font-size: .64rem; }
        .atlas-quality-config--inline .atlas-quality-config-status { color: #b91c1c; font-size: .6rem; min-width: 0; }
        .atlas-kpis { display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: .45rem; margin-bottom: .75rem; }
        .atlas-kpi { border: 1px solid #e2e8f0; border-radius: .55rem; background: #fff; padding: .68rem .55rem; min-height: 4.15rem; min-width: 0; }
        .atlas-kpi span { display: flex; align-items: center; gap: .3rem; color: #64748b; font-size: .62rem; font-weight: 900; text-transform: uppercase; letter-spacing: .02em; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .atlas-kpi strong { display: block; margin-top: .28rem; color: #173756; font-size: .98rem; font-weight: 900; line-height: 1.05; }
        .atlas-kpi-action { display: flex; align-items: center; justify-content: space-between; gap: .35rem; min-width: 0; }
        .atlas-kpi-danger { border-color: #fecaca; background: #fff7f7; }
        .atlas-kpi-danger strong, .atlas-kpi-danger span { color: #b91c1c; }
        .atlas-kpi-warn { border-color: #fde68a; background: #fffbeb; }
        .atlas-kpi-warn strong, .atlas-kpi-warn span { color: #92400e; }
        .atlas-step-stack { display: flex; flex-direction: column; align-items: center; gap: .18rem; margin-top: .32rem; }
        .atlas-step-pill { display: inline-flex; align-items: center; gap: .22rem; color: #15803d; font-size: .58rem; font-weight: 900; text-transform: uppercase; line-height: 1; white-space: nowrap; }
        .atlas-step-dot { width: 1.25rem; height: 1.25rem; border-radius: 999px; display: inline-grid; place-items: center; background: #dcfce7; border: 1px solid #86efac; color: #15803d; font-size: .58rem; font-weight: 900; }
        .atlas-step-pill.is-pending { color: #b45309; }
        .atlas-step-pill.is-pending .atlas-step-dot { background: #fff7ed; border-color: #fed7aa; color: #b45309; }
        .atlas-step-section-title { grid-column: 1 / -1; display: flex; align-items: center; gap: .45rem; margin-top: .25rem; color: #173756; font-size: .74rem; font-weight: 900; text-transform: uppercase; letter-spacing: .03em; }
        .atlas-step-section-title .atlas-step-dot { width: 1.4rem; height: 1.4rem; }
        .atlas-step-permission-note { grid-column: 1 / -1; color: #64748b; font-size: .72rem; font-weight: 700; }
        .atlas-modal-hidden { display: none !important; }
        .atlas-readonly-field { pointer-events: none; }
        .atlas-readonly-field.form-control,
        .atlas-readonly-field.form-select,
        .atlas-readonly-field + .select2 .select2-selection { background: #f8fafc; color: #64748b; cursor: not-allowed; }
        .atlas-error-card { text-align: left; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease; }
        .atlas-error-card:hover { transform: translateY(-1px); box-shadow: 0 .35rem .8rem rgba(153, 27, 27, .12); }
        .atlas-error-card-sub { display: block; color: #7f1d1d; font-size: .64rem; font-weight: 800; margin-top: .18rem; }
        .atlas-division-error-list { display: grid; gap: .55rem; }
        .atlas-division-error-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .75rem; align-items: center; border: 1px solid #fee2e2; border-radius: .65rem; background: #fff; padding: .7rem .8rem; }
        .atlas-division-error-title { color: #22303e; font-size: .88rem; font-weight: 900; line-height: 1.15; }
        .atlas-division-error-meta { color: #64748b; font-size: .74rem; font-weight: 700; margin-top: .18rem; }
        .atlas-division-error-meta strong { color: #991b1b; font-weight: 900; }
        .atlas-map-badge-btn, .atlas-location-link { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; border-radius: 999px; font-size: .64rem; font-weight: 800; padding: .16rem .42rem; display: inline-flex; align-items: center; gap: .24rem; white-space: nowrap; }
        .atlas-quality-btn { border-color: #fecaca; color: #b91c1c; background: #fff1f2; }
        .atlas-quality-btn-warning { border-color: #fde68a; color: #92400e; background: #fffbeb; }
        .atlas-location-link { max-width: 100%; border-color: #dbe4ef; color: #334155; background: #f8fafc; white-space: normal; text-align: left; border-radius: .45rem; }
        .atlas-field-row { display: flex; flex-direction: column; gap: .08rem; min-width: 0; }
        .atlas-field-label { color: #64748b; font-size: .68rem; font-weight: 900; text-transform: uppercase; letter-spacing: .03em; line-height: 1.1; }
        .atlas-field-value { color: #22303e; font-size: .88rem; font-weight: 800; line-height: 1.2; }
        .atlas-muted { color: #64748b; font-size: .78rem; font-weight: 700; }
        .atlas-assignment-box { display: grid; gap: .28rem; min-width: 14rem; font-family: Calibri, Arial, sans-serif; }
        .atlas-assignment-row { display: grid; grid-template-columns: 6.4rem minmax(0, 1fr); gap: .45rem; align-items: start; }
        .atlas-assignment-label { color: #64748b; font-size: .7rem; font-weight: 700; letter-spacing: 0; line-height: 1.15; display: inline-flex; align-items: center; gap: .3rem; white-space: nowrap; }
        .atlas-assignment-label i { width: .92rem; text-align: center; color: #173756; }
        .atlas-assignment-value { color: #22303e; font-size: .82rem; font-weight: 500; line-height: 1.18; min-width: 0; overflow-wrap: anywhere; }
        .atlas-assignment-status { margin-top: .08rem; }
        .atlas-sucursal-avatar { width: 2.65rem; height: 2.65rem; border-radius: 999px; background: linear-gradient(135deg, #173756, #2563eb); color: #fff; display: inline-grid; place-items: center; flex: 0 0 2.65rem; box-shadow: 0 8px 18px rgba(23, 55, 86, .18); }
        .atlas-sucursal-avatar i { display: block; font-size: 1.12rem; line-height: 1; margin: 0; transform: translateY(.02rem); }
        .atlas-sucursal-avatar-col { width: 4.2rem; min-width: 4.2rem; text-align: center !important; vertical-align: middle !important; }
        .atlas-sucursal-avatar-col .atlas-sucursal-avatar { margin: 0 auto; }
        .atlas-sucursal-status-under { display: flex; justify-content: center; margin-top: .38rem; }
        .atlas-sucursal-status-under .atlas-badge { font-size: .62rem; padding: .12rem .42rem; }
        .atlas-empty { text-align: center; color: #94a3b8; font-weight: 700; padding: 2rem !important; }
        .atlas-badge { display: inline-flex; align-items: center; gap: .28rem; border-radius: 999px; padding: .18rem .55rem; font-size: .72rem; font-weight: 900; white-space: nowrap; }
        .atlas-badge-ok { background: #dcfce7; color: #15803d; }
        .atlas-badge-off { background: #f1f5f9; color: #64748b; }
        .atlas-badge-warn { background: #fef3c7; color: #b45309; }
        .atlas-badge-info { background: #e0f2fe; color: #0369a1; }
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
        #modalAtlasCatalogo.atlas-modal-distribuidor .modal-dialog { max-width: min(82rem, 98vw); }
        #modalAtlasCatalogo.atlas-modal-distribuidor .modal-content { height: min(50rem, calc(100vh - 2rem)); }
        #modalAtlasCatalogo.atlas-modal-distribuidor .modal-body { min-height: 0; overflow-y: auto; }
        #modalAtlasCatalogo.atlas-modal-distribuidor .tab-content { min-height: 34rem; }
        .atlas-dist-step-layout { display: grid; grid-template-columns: minmax(13rem, .34fr) minmax(0, 1fr); gap: 1.35rem; align-items: start; }
        .atlas-dist-step-title { color: #173756; font-size: .92rem; font-weight: 900; margin: 0 0 .85rem; }
        .atlas-dist-step-nav.nav-pills .nav-link { display: flex; align-items: center; gap: .52rem; color: #566a7f; font-weight: 800; border-radius: .55rem; padding: .62rem .75rem; text-align: left; }
        .atlas-dist-step-nav.nav-pills .nav-link i { width: 1.15rem; text-align: center; }
        .atlas-dist-step-nav.nav-pills .nav-link.active { background: #26344e; color: #fff; box-shadow: 0 .18rem .45rem rgba(34,48,62,.16); }
        .atlas-dist-step-nav .atlas-tab-required-mark { margin-left: auto; background: #dc2626; box-shadow: 0 0 0 .14rem rgba(220,38,38,.12); }
        .atlas-tab-required-mark { display: inline-flex; width: .42rem; height: .42rem; border-radius: 999px; background: #dc2626; margin-left: .38rem; vertical-align: middle; box-shadow: 0 0 0 .16rem rgba(220, 38, 38, .12); }
        .atlas-field-wide { grid-column: 1 / -1; }
        .atlas-sucursal-catalog-row { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(13rem, .85fr); gap: 1rem; align-items: start; }
        .atlas-presencia-editor { border: 1px solid #dbe4ef; border-radius: .75rem; background: #f8fafc; padding: .8rem; }
        .atlas-presencia-inputs { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto; gap: .65rem; align-items: end; }
        .atlas-presencia-list { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .65rem; min-height: 2rem; }
        .atlas-presencia-chip { display: inline-flex; align-items: center; gap: .42rem; max-width: 100%; border: 1px solid #bfdbfe; border-radius: 999px; background: #eff6ff; color: #173756; padding: .28rem .42rem .28rem .62rem; font-size: .74rem; font-weight: 900; }
        .atlas-presencia-chip span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .atlas-presencia-chip button { border: 0; background: #dbeafe; color: #1d4ed8; border-radius: 999px; width: 1.35rem; height: 1.35rem; display: inline-grid; place-items: center; padding: 0; }
        .atlas-presencia-empty { color: #94a3b8; font-size: .78rem; font-weight: 800; padding: .35rem 0; }
        .atlas-presencia-summary { display: grid; gap: .16rem; min-width: 12rem; }
        .atlas-presencia-summary-lines { color: #64748b; font-size: .76rem; font-weight: 700; line-height: 1.18; }
        .atlas-presencia-hidden { display: none !important; }
        .atlas-check-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .45rem; }
        .atlas-check-head .form-label { margin: 0; }
        .atlas-check-add { width: 2.15rem; height: 2.15rem; border: 1px solid #26344e; border-radius: .7rem; background: #fff; color: #26344e; display: inline-grid; place-items: center; padding: 0; transition: background .15s ease, color .15s ease, transform .15s ease; }
        .atlas-check-add:hover { background: #26344e; color: #fff; transform: translateY(-1px); }
        .atlas-checkbox-grid { display: grid; grid-template-columns: 1fr; gap: .45rem; }
        .atlas-check-option { display: flex; align-items: center; gap: .45rem; border: 1px solid #dbe4ef; border-radius: .55rem; padding: .48rem .6rem; background: #fff; color: #22303e; font-size: .82rem; font-weight: 800; }
        .atlas-check-option input { flex: 0 0 auto; }
        .atlas-status-buttons { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .45rem; }
        .atlas-status-btn { border: 1px solid #dbe4ef; background: #fff; color: #566a7f; border-radius: .6rem; padding: .55rem .5rem; font-size: .78rem; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; gap: .35rem; }
        .atlas-status-btn.is-active { background: #26344e; border-color: #26344e; color: #fff; }
        .atlas-time-row { display: grid; grid-template-columns: minmax(0, 1fr) 9rem; gap: .55rem; align-items: end; }
        .atlas-required::after { content: " *"; color: #dc2626; font-weight: 900; }
        .atlas-combo-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .45rem; align-items: end; }
        .atlas-combo-add i { margin: 0; }
        .atlas-cascade-help { display: block; margin-top: .28rem; color: #64748b; font-size: .72rem; font-weight: 700; }
        .atlas-select-locked + .select2 { pointer-events: none; }
        .atlas-select-locked + .select2 .select2-selection { background: #f8fafc; cursor: not-allowed; }
        .atlas-fk-input { max-width: 10rem; text-align: center; font-weight: 900; color: #94a3b8; }
        .atlas-branch-row { display: grid; grid-template-columns: 11rem minmax(0, 1fr); gap: 1rem; align-items: start; }
        .atlas-location-fields { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
        .atlas-location-derived.is-hidden { display: none !important; }
        .atlas-location-field-wide { grid-column: span 2; }
        .atlas-location-admin-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)) repeat(2, minmax(0, .85fr)) minmax(0, 1fr); gap: .75rem; }
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
        .atlas-icon-picker { border: 1px solid #dbe4ef; border-radius: .65rem; background: #fff; overflow: hidden; }
        .atlas-icon-picker summary { list-style: none; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .6rem .75rem; color: #24364b; font-weight: 900; }
        .atlas-icon-picker summary::-webkit-details-marker { display: none; }
        .atlas-icon-picker-summary { display: inline-flex; align-items: center; gap: .5rem; min-width: 0; }
        .atlas-icon-picker-current { width: 2rem; height: 2rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; flex: 0 0 auto; }
        .atlas-icon-picker-action { color: #64748b; font-size: .72rem; font-weight: 900; text-transform: uppercase; white-space: nowrap; }
        .atlas-icon-picker[open] .atlas-icon-picker-action { color: #1d4ed8; }
        .atlas-icon-picker-panel { border-top: 1px solid #e2e8f0; padding: .65rem; background: #f8fafc; }
        .atlas-color-input-wrap { display: flex; align-items: center; gap: .55rem; }
        .atlas-color-input-wrap input[type="color"] { width: 3rem; height: 2.4rem; border: 1px solid #dbe4ef; border-radius: .55rem; padding: .15rem; background: #fff; }
        .atlas-drag-handle { width: 2.15rem; height: 2.15rem; border: 1px solid #dbe4ef; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; color: #64748b; background: #f8fafc; cursor: grab; }
        .atlas-order-pill { display: inline-flex; min-width: 2.2rem; height: 1.75rem; align-items: center; justify-content: center; border-radius: 999px; background: #eef2ff; color: #1d4ed8; font-weight: 900; }
        .atlas-catalog-inline { display: grid; grid-template-columns: minmax(0, 1fr) 13rem; gap: 1rem; align-items: end; grid-column: 1 / -1; }
        .atlas-business-map {
            min-height: 18rem;
            border: 1px solid #dbe4ef;
            border-radius: .75rem;
            background: linear-gradient(180deg, #f8fafc 0%, #eef6ff 100%);
            display: grid;
            place-items: center;
            padding: 1.25rem;
            text-align: center;
        }
        .atlas-business-map-inner {
            max-width: 34rem;
            display: grid;
            gap: .85rem;
            justify-items: center;
        }
        .atlas-business-map-icon {
            width: 4.4rem;
            height: 4.4rem;
            border-radius: 999px;
            background: #fff7ed;
            color: #d97706;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 10px 24px rgba(217, 119, 6, .18);
        }
        .atlas-business-map-title {
            color: #22303e;
            font-size: 1.05rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .atlas-business-map-sub {
            color: #64748b;
            font-size: .86rem;
            font-weight: 700;
        }
        #modalAtlasSucursal .modal-footer,
        #modalAtlasDireccion .modal-footer,
        #modalAtlasCatalogo .modal-footer,
        #modalAtlasReglasClasificacion .modal-footer,
        #modalAtlasMapa .modal-footer,
        #modalAtlasErroresDivisiones .modal-footer,
        #modalAtlasCalidad .modal-footer {
            justify-content: flex-end;
            gap: .75rem;
        }
        #modalAtlasSucursal .modal-footer .btn,
        #modalAtlasDireccion .modal-footer .btn,
        #modalAtlasCatalogo .modal-footer .btn,
        #modalAtlasReglasClasificacion .modal-footer .btn,
        #modalAtlasMapa .modal-footer .btn,
        #modalAtlasErroresDivisiones .modal-footer .btn,
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
            .atlas-dist-step-layout { grid-template-columns: 1fr; }
            #modalAtlasCatalogo.atlas-modal-distribuidor .tab-content { min-height: 24rem; }
            .atlas-error-card-list { grid-template-columns: 1fr; }
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
            #modalAtlasReglasClasificacion .modal-footer,
            #modalAtlasMapa .modal-footer,
            #modalAtlasErroresDivisiones .modal-footer,
            #modalAtlasCalidad .modal-footer { flex-direction: column; align-items: stretch; }
            #modalAtlasSucursal .modal-footer .btn,
            #modalAtlasDireccion .modal-footer .btn,
            #modalAtlasCatalogo .modal-footer .btn,
            #modalAtlasReglasClasificacion .modal-footer .btn,
            #modalAtlasMapa .modal-footer .btn,
            #modalAtlasErroresDivisiones .modal-footer .btn,
            #modalAtlasCalidad .modal-footer .btn { width: 100%; }
        }
        @media (max-width: 575.98px) {
            .atlas-head { align-items: stretch; flex-direction: column; }
            .atlas-head .btn { width: 100%; justify-content: center; }
            .atlas-title { font-size: 1.08rem; }
            .atlas-kpis, .atlas-form-grid, .atlas-branch-row, .atlas-sucursal-catalog-row, .atlas-location-fields, .atlas-location-admin-row, .atlas-coord-row, .atlas-catalog-inline { grid-template-columns: 1fr; }
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
                <span>Catálogos Operativos</span>
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
                <li class="nav-item" role="presentation"><button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-tab-clasificaciones"><i class="fa-solid fa-tags me-1"></i>Clasificaciones</button></li>
            </ul>

            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="atlas-tab-sucursales" role="tabpanel">
            <div class="atlas-kpis">
                <div class="atlas-kpi"><span><i class="fa-solid fa-store"></i>Total</span><strong id="atlas-kpi-total">0</strong></div>
                <div class="atlas-kpi"><span><i class="fa-solid fa-circle-check"></i>Activas</span><strong id="atlas-kpi-activas">0</strong></div>
                <div class="atlas-kpi"><span><i class="fa-solid fa-circle-pause"></i>Inactivas</span><strong id="atlas-kpi-inactivas">0</strong></div>
                <div class="atlas-kpi atlas-kpi-warn"><span><i class="fa-solid fa-user-check"></i>Pendientes paso 2</span><strong id="atlas-kpi-pendientes-paso2">0</strong></div>
                <div class="atlas-kpi atlas-kpi-warn"><span><i class="fa-solid fa-triangle-exclamation"></i>Sucursales pendientes</span><div class="atlas-kpi-action"><strong id="atlas-kpi-sucursales-pendientes">0</strong><button type="button" class="atlas-map-badge-btn atlas-quality-btn-warning" id="atlas-btn-ver-sucursales-pendientes"><i class="fa-solid fa-list-check"></i>Revisar</button></div></div>
                <div class="atlas-kpi"><span><i class="fa-solid fa-location-crosshairs"></i>Con coordenadas</span><div class="atlas-kpi-action"><strong id="atlas-kpi-coordenadas">0</strong><button type="button" class="atlas-map-badge-btn" id="atlas-btn-ver-mapa"><i class="fa-solid fa-map-location-dot"></i>Ver mapa</button></div></div>
                <div class="atlas-kpi atlas-kpi-danger"><span><i class="fa-solid fa-triangle-exclamation"></i>Con error</span><div class="atlas-kpi-action"><strong id="atlas-kpi-errores">0</strong><button type="button" class="atlas-map-badge-btn atlas-quality-btn" id="atlas-btn-ver-errores"><i class="fa-solid fa-list-check"></i>Detalle</button></div></div>
                <div class="atlas-kpi atlas-kpi-warn"><span><i class="fa-solid fa-map-pin"></i>Sin coordenadas</span><div class="atlas-kpi-action"><strong id="atlas-kpi-sin-coordenadas">0</strong><button type="button" class="atlas-map-badge-btn atlas-quality-btn-warning" id="atlas-btn-ver-sin-coordenadas"><i class="fa-solid fa-screwdriver-wrench"></i>Corregir</button></div></div>
            </div>
            <div class="atlas-panel-head">
                <?php if ($atlasPuedeAgregarSucursal): ?>
                <button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="sucursal"><i class="fa fa-plus icon-sm me-sm-1"></i><span>Agregar sucursal</span></button>
                <?php endif; ?>
            </div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="sucursales" data-atlas-loading-label="Cargando sucursales...">
                <table id="atlasTablaSucursales" class="dt-responsive table border-top">
                    <thead><tr><th></th><th>Sucursal</th><th>Clasificación</th><th>Asignación</th><th>Acciones</th></tr></thead>
                    <tbody id="atlas-sucursales-body"><tr><td colspan="5" class="atlas-empty"><span class="spinner-border spinner-border-sm me-2"></span>Cargando sucursales...</td></tr></tbody>
                </table>
            </div>
                </div>
                <div class="tab-pane fade" id="atlas-tab-divisiones" role="tabpanel">
            <div class="atlas-subtabs-wrap" aria-label="Opciones de divisiones">
                <ul class="nav atlas-subtabs" id="atlas-divisiones-subtabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-subtab-divisiones-asigna" aria-selected="true">
                            <i class="fa-solid fa-user-check"></i>Asigna divisiones
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#atlas-subtab-divisiones-catalogo" aria-selected="false">
                            <i class="fa-solid fa-layer-group"></i>Catálogo de divisiones
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="atlas-subtab-divisiones-asigna" role="tabpanel">
            <div class="atlas-kpis">
                <button type="button" class="atlas-kpi atlas-kpi-danger atlas-error-card" id="atlas-divisiones-error-card" style="display:none;">
                    <span><i class="fa-solid fa-triangle-exclamation"></i>Errores</span>
                    <strong id="atlas-divisiones-error-total">0 errores</strong>
                    <span class="atlas-error-card-sub">Ver divisionales repetidos</span>
                </button>
                <button type="button" class="atlas-kpi atlas-kpi-info atlas-error-card" id="atlas-divisiones-actualizaciones-card">
                    <span><i class="fa-solid fa-arrows-rotate"></i>Actualizaciones</span>
                    <strong id="atlas-divisiones-actualizaciones-total">0 pendientes</strong>
                    <span class="atlas-error-card-sub">Agregar o sacar divisionales</span>
                </button>
            </div>
            <div class="atlas-panel-head"><button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="asigna_division"><i class="fa fa-user-check icon-sm me-sm-1"></i><span>Asigna división</span></button></div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="asigna_divisiones" data-atlas-loading-label="Cargando asignaciones...">
                <table id="atlasTablaAsignaDivisiones" class="dt-responsive table border-top"><thead><tr><th>División</th><th>Divisional activo</th><th>Estatus</th><th>Acciones</th></tr></thead><tbody></tbody></table>
            </div>
                </div>
                <div class="tab-pane fade" id="atlas-subtab-divisiones-catalogo" role="tabpanel">
            <div class="atlas-panel-head d-flex justify-content-end gap-2 flex-wrap">
                <button type="button" class="btn btn-info text-white btn-action-size" data-atlas-fusionar-divisiones><i class="fa-solid fa-code-merge icon-sm me-sm-1"></i><span>Fusionar sucursales</span></button>
                <button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="division"><i class="fa fa-plus icon-sm me-sm-1"></i><span>Agregar división</span></button>
            </div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="divisiones" data-atlas-loading-label="Cargando divisiones...">
                <table id="atlasTablaDivisiones" class="dt-responsive table border-top"><thead><tr><th>División</th><th>Estatus</th><th>Acciones</th></tr></thead><tbody></tbody></table>
            </div>
                </div>
            </div>
                </div>
                <div class="tab-pane fade" id="atlas-tab-distribuidores" role="tabpanel">
            <div class="atlas-panel-head d-flex justify-content-end gap-2 flex-wrap">
                <button type="button" class="btn text-white btn-action-size" style="background-color:#0047bb;border-color:#0047bb;" id="atlas-btn-template-distribuidores"><i class="fa fa-download icon-sm me-sm-1"></i><span>Plantilla</span></button>
                <button type="button" class="btn btn-info text-white btn-action-size" id="atlas-btn-importar-distribuidores"><i class="fa fa-file-excel icon-sm me-sm-1"></i><span>Cargar layout</span></button>
                <button type="button" class="btn btn-primary add-new btn-action-size" data-atlas-agregar="distribuidor"><i class="fa fa-plus icon-sm me-sm-1"></i><span>Agregar distribuidor</span></button>
            </div>
            <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="distribuidores" data-atlas-loading-label="Cargando distribuidores...">
                <table id="atlasTablaDistribuidores" class="dt-responsive table border-top"><thead><tr><th>Distribuidor</th><th>Presencia</th><th>Estatus</th><th>Acciones</th></tr></thead><tbody></tbody></table>
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
            <input type="hidden" name="paso_alta" value="paso1">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalAtlasSucursalLabel"><i class="fa-solid fa-store me-2"></i>Agregar sucursal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="atlas-form-grid">
                    <div class="atlas-step-section-title"><span class="atlas-step-dot">1</span>Datos base de sucursal</div>
                    <div class="atlas-branch-row atlas-field-wide">
                        <div><label class="form-label">FK sucursal</label><input type="text" class="form-control atlas-fk-input" name="fk_sucursal" readonly placeholder="Auto"><span class="atlas-cascade-help">Auto al guardar.</span></div>
                        <div><label class="form-label atlas-required">Sucursal</label><input type="text" class="form-control" name="sucursal" required maxlength="120" placeholder="Nombre de la sucursal"><small class="text-danger fw-semibold d-none" id="atlas-sucursal-coincidencia"></small></div>
                    </div>
                    <div class="atlas-sucursal-catalog-row atlas-field-wide">
                        <div><label class="form-label atlas-required">Distribuidor</label><div class="atlas-combo-row"><select class="form-select js-atlas-select-buscador" name="distribuidor_id" id="atlas-sucursal-distribuidor" required></select><button type="button" class="btn btn-icon btn-outline-primary atlas-combo-add" data-atlas-quick-add="distribuidor" data-atlas-target="distribuidor_id" title="Agregar distribuidor" aria-label="Agregar distribuidor"><i class="fa-solid fa-plus"></i></button></div></div>
                        <div><label class="form-label atlas-required">Clasificación</label><select class="form-select js-atlas-select-buscador" name="clasificacion_id" id="atlas-sucursal-clasificacion" required></select><span class="atlas-cascade-help">Clasificación automática del sistema.</span></div>
                    </div>
                    <div><label class="form-label atlas-required">Estatus</label><select class="form-select js-atlas-select-buscador" name="activo" required><option value="1">Activa</option><option value="0">Inactiva</option></select></div>
                    <div class="atlas-field-wide"><label class="form-label atlas-required">Dirección</label><textarea class="form-control atlas-address-field" name="direccion_sucursal" id="atlas-sucursal-direccion" rows="2" readonly required maxlength="250" placeholder="Da clic para buscar la dirección en el mapa"></textarea><span class="atlas-cascade-help">La dirección se captura desde Google Maps para calcular estado, municipio, localidad, CP y coordenadas.</span></div>
                    <div class="atlas-location-fields atlas-location-derived atlas-field-wide">
                        <div class="atlas-location-field-wide"><label class="form-label atlas-required">Calle</label><input type="text" class="form-control" name="calle" readonly required maxlength="120" placeholder="Calle"></div>
                        <div><label class="form-label atlas-required">No. exterior</label><input type="text" class="form-control" name="numero_exterior" readonly required maxlength="40" placeholder="S/N"></div>
                        <div><label class="form-label">No. interior</label><input type="text" class="form-control" name="numero_interior" maxlength="40" placeholder="Interior, local o piso"></div>
                    </div>
                    <div class="atlas-location-admin-row atlas-location-derived atlas-field-wide">
                        <div><label class="form-label atlas-required">Colonia</label><input type="text" class="form-control" name="colonia" readonly required maxlength="120" placeholder="Colonia"></div>
                        <div><label class="form-label atlas-required">Localidad</label><input type="text" class="form-control" name="localidad" readonly required maxlength="120" placeholder="Localidad"></div>
                        <div><label class="form-label atlas-required">Municipio</label><input type="text" class="form-control" name="municipio" readonly required maxlength="120" placeholder="Municipio"></div>
                        <div><label class="form-label atlas-required">Código postal</label><input type="text" class="form-control" name="codigo_postal" readonly required maxlength="10" inputmode="numeric" pattern="[0-9]{5}" placeholder="CP"></div>
                        <div><label class="form-label atlas-required">Estado</label><input type="text" class="form-control" name="estado" readonly required maxlength="120" placeholder="Estado"></div>
                    </div>
                    <div class="atlas-coord-row atlas-location-derived atlas-field-wide">
                        <div><label class="form-label atlas-required">Latitud</label><input type="text" class="form-control" name="latitud" readonly required maxlength="16" placeholder="Latitud"></div>
                        <div><label class="form-label atlas-required">Longitud</label><input type="text" class="form-control" name="longitud" readonly required maxlength="16" placeholder="Longitud"></div>
                    </div>
                    <div class="atlas-step-section-title"><span class="atlas-step-dot">2</span>Asignación operativa</div>
                    <div class="atlas-step-permission-note" id="atlas-sucursal-paso2-note">Completa estos campos cuando la sucursal ya tenga asignación de gestores.</div>
                    <div><label class="form-label">División</label><div class="atlas-combo-row"><select class="form-select js-atlas-select-buscador" name="division_id" id="atlas-sucursal-division"></select><button type="button" class="btn btn-icon btn-outline-primary atlas-combo-add" data-atlas-quick-add="division" data-atlas-target="division_id" title="Agregar división" aria-label="Agregar división"><i class="fa-solid fa-plus"></i></button></div><span class="atlas-cascade-help">Primero selecciona la división operativa de la sucursal.</span></div>
                    <div><label class="form-label">Divisional</label><select class="form-select js-atlas-select-buscador" name="divisional_id" id="atlas-sucursal-divisional"></select><span class="atlas-cascade-help">Se asigna automáticamente según la división.</span></div>
                    <div><label class="form-label">Regional</label><select class="form-select js-atlas-select-buscador" name="regional_id" id="atlas-sucursal-regional"></select><span class="atlas-cascade-help">Se habilita después de seleccionar una división.</span></div>
                    <div><label class="form-label">Supervisor</label><select class="form-select js-atlas-select-buscador" name="supervisor_id" id="atlas-sucursal-supervisor"></select></div>
                    <div><label class="form-label">Asesor</label><select class="form-select js-atlas-select-buscador" name="asesor_id" id="atlas-sucursal-asesor"></select></div>
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

<div class="modal fade" id="modalAtlasImportarDistribuidores" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form class="modal-content" id="formAtlasImportarDistribuidores">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2"></i>Cargar distribuidores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <label class="form-label atlas-required">Layout Excel</label>
                <input type="file" class="form-control" name="archivo" accept=".xlsx,.xls" required>
                <span class="atlas-cascade-help">Descarga la plantilla para trabajar con los distribuidores activos actuales. Si el archivo trae un distribuidor bloqueado, el sistema te pedirá confirmar si deseas desbloquearlo.</span>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i>Cargar</button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
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

<div class="modal fade" id="modalAtlasReglasClasificacion" tabindex="-1" aria-labelledby="modalAtlasReglasClasificacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasReglasClasificacionLabel"><i class="fa-solid fa-trophy me-2"></i>Reglas de negocio</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div class="atlas-business-map">
                    <div class="atlas-business-map-inner">
                        <span class="atlas-business-map-icon"><i class="fa-solid fa-trophy"></i></span>
                        <div>
                            <div class="atlas-business-map-title" id="atlas-reglas-clasificacion-titulo">Reglas de negocio en construcción</div>
                            <div class="atlas-business-map-sub" id="atlas-reglas-clasificacion-sub">Aquí se armarán las condiciones operativas para esta clasificación.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
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
                <div class="atlas-quality-config" id="atlas-calidad-config">
                    <div class="atlas-quality-config-title"><i class="fa-solid fa-sliders"></i>Sucursales con error</div>
                    <label class="form-check" for="atlas-config-sin-telefono-error">
                        <input class="form-check-input" type="checkbox" id="atlas-config-sin-telefono-error">
                        <span class="form-check-label">Contar “Sin teléfono” como error operativo</span>
                    </label>
                    <span class="atlas-quality-config-status" id="atlas-config-sin-telefono-status">Guardado</span>
                </div>
                <div class="atlas-quality-list" id="atlas-calidad-lista"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtlasErroresDivisiones" tabindex="-1" aria-labelledby="modalAtlasErroresDivisionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasErroresDivisionesLabel"><i class="fa-solid fa-triangle-exclamation me-2"></i>Errores en divisionales</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div class="atlas-muted mb-3" id="atlas-divisiones-error-resumen">Revisando divisionales...</div>
                <div class="atlas-division-error-list" id="atlas-divisiones-error-modal-list"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtlasActualizacionesDivisionales" tabindex="-1" aria-labelledby="modalAtlasActualizacionesDivisionalesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasActualizacionesDivisionalesLabel"><i class="fa-solid fa-arrows-rotate me-2"></i>Actualizaciones de divisionales</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div class="atlas-muted mb-3" id="atlas-divisiones-actualizaciones-resumen">Revisando divisionales...</div>
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <h6 class="fw-bold mb-0"><i class="fa-solid fa-user-plus me-1"></i>Disponibles para agregar</h6>
                                <span class="atlas-badge atlas-badge-info" id="atlas-divisiones-disponibles-total">0</span>
                            </div>
                            <div id="atlas-divisiones-disponibles-list">
                                <label class="form-label atlas-required">Colaborador operativo</label>
                                <div class="d-flex gap-2 align-items-start">
                                    <select class="form-select js-atlas-select-buscador" id="atlas-divisiones-disponibles-select">
                                        <option value="">Selecciona colaborador</option>
                                    </select>
                                    <button type="button" class="btn btn-primary btn-action-size flex-shrink-0" id="atlas-divisiones-disponibles-agregar">
                                        <i class="fa-solid fa-plus me-1"></i>Agregar
                                    </button>
                                </div>
                                <span class="atlas-cascade-help">Busca y agrega colaboradores operativos al catálogo de divisionales activos.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <h6 class="fw-bold mb-0"><i class="fa-solid fa-user-minus me-1"></i>Sin uso en divisiones</h6>
                                <span class="atlas-badge atlas-badge-warn" id="atlas-divisiones-sin-uso-total">0</span>
                            </div>
                            <div class="atlas-division-error-list" id="atlas-divisiones-sin-uso-list"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtlasFusionDivisiones" tabindex="-1" aria-labelledby="modalAtlasFusionDivisionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="formAtlasFusionDivisiones">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAtlasFusionDivisionesLabel"><i class="fa-solid fa-code-merge me-2"></i>Fusionar sucursales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="atlas-muted mb-3">Elige la división que se queda, las divisiones que se integran y el responsable final. Las sucursales y regionales se moverán automáticamente a la división final.</div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label atlas-required">División que permanece</label>
                            <select class="form-select js-atlas-select-buscador" id="atlas-fusion-division-destino" required>
                                <option value="">Selecciona división</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label atlas-required">Nuevo nombre</label>
                            <input type="text" class="form-control" id="atlas-fusion-nuevo-nombre" maxlength="120" placeholder="Nombre final de la división" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label atlas-required">Divisiones que se fusionan</label>
                            <select class="form-select js-atlas-select-buscador" id="atlas-fusion-division-origenes" multiple required></select>
                            <span class="atlas-cascade-help">Puedes elegir una o varias divisiones. No aparecerá la división que permanece.</span>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Responsable divisional final</label>
                            <select class="form-select js-atlas-select-buscador" id="atlas-fusion-divisional">
                                <option value="">Conservar responsable actual</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label atlas-required">Motivo</label>
                            <textarea class="form-control" id="atlas-fusion-motivo" rows="2" maxlength="220" placeholder="Motivo para bitácora" required></textarea>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3 bg-light" id="atlas-fusion-resumen">Selecciona las divisiones para ver el resumen de la fusión.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Fusionar</button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAtlasSucursalesPendientes" tabindex="-1" aria-labelledby="modalAtlasSucursalesPendientesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold" id="modalAtlasSucursalesPendientesLabel"><i class="fa-solid fa-triangle-exclamation me-2"></i>Sucursales pendientes</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div class="atlas-muted mb-3">Sucursales que llegaron desde Maxi y no existen en el catálogo local. No se mandan a gestión hasta cuadrarlas.</div>
                <div class="card-datatable table-responsive atlas-table-wrap atlas-loading" data-atlas-table-loader="sucursales_pendientes" data-atlas-loading-label="Cargando sucursales pendientes...">
                    <table id="atlasTablaSucursalesPendientes" class="dt-responsive table border-top"><thead><tr><th>FK origen</th><th>Sucursal Maxi</th><th>Distribuidor</th><th>Afectación</th><th>Motivo</th><th>Estatus</th><th>Detectado</th><th>Acciones</th></tr></thead><tbody></tbody></table>
                </div>
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
    const btnSucursalesPendientes = document.getElementById('atlas-btn-ver-sucursales-pendientes');
    const formSucursal = document.getElementById('formAtlasSucursal');
    const formCatalogo = document.getElementById('formAtlasCatalogo');
    const modalSucursalEl = document.getElementById('modalAtlasSucursal');
    const modalCatalogoEl = document.getElementById('modalAtlasCatalogo');
    const modalImportarDistribuidoresEl = document.getElementById('modalAtlasImportarDistribuidores');
    const formImportarDistribuidores = document.getElementById('formAtlasImportarDistribuidores');
    const modalReglasClasificacionEl = document.getElementById('modalAtlasReglasClasificacion');
    const modalDireccionEl = document.getElementById('modalAtlasDireccion');
    const modalMapaEl = document.getElementById('modalAtlasMapa');
    const modalUbicacionEl = document.getElementById('modalAtlasUbicacion');
    const modalCalidadEl = document.getElementById('modalAtlasCalidad');
    const modalErroresDivisionesEl = document.getElementById('modalAtlasErroresDivisiones');
    const modalActualizacionesDivisionalesEl = document.getElementById('modalAtlasActualizacionesDivisionales');
    const modalFusionDivisionesEl = document.getElementById('modalAtlasFusionDivisiones');
    const formFusionDivisiones = document.getElementById('formAtlasFusionDivisiones');
    const modalSucursalesPendientesEl = document.getElementById('modalAtlasSucursalesPendientes');
    const modalSucursalTitulo = document.getElementById('modalAtlasSucursalLabel');
    const modalCatalogoTitulo = document.getElementById('modalAtlasCatalogoLabel');
    const reglasClasificacionTitulo = document.getElementById('atlas-reglas-clasificacion-titulo');
    const reglasClasificacionSub = document.getElementById('atlas-reglas-clasificacion-sub');
    const modalCalidadTitulo = document.getElementById('modalAtlasCalidadLabel');
    const calidadResumen = document.getElementById('atlas-calidad-resumen');
    const calidadLista = document.getElementById('atlas-calidad-lista');
    const calidadConfig = document.getElementById('atlas-calidad-config');
    const configSinTelefonoError = document.getElementById('atlas-config-sin-telefono-error');
    const configSinTelefonoStatus = document.getElementById('atlas-config-sin-telefono-status');
    const divisionesErrorResumen = document.getElementById('atlas-divisiones-error-resumen');
    const divisionesErrorModalList = document.getElementById('atlas-divisiones-error-modal-list');
    const divisionesActualizacionesResumen = document.getElementById('atlas-divisiones-actualizaciones-resumen');
    const divisionesDisponiblesList = document.getElementById('atlas-divisiones-disponibles-list');
    const divisionesSinUsoList = document.getElementById('atlas-divisiones-sin-uso-list');
    const divisionesDisponiblesTotal = document.getElementById('atlas-divisiones-disponibles-total');
    const divisionesSinUsoTotal = document.getElementById('atlas-divisiones-sin-uso-total');
    const divisionesDisponiblesSelect = document.getElementById('atlas-divisiones-disponibles-select');
    const divisionesDisponiblesAgregar = document.getElementById('atlas-divisiones-disponibles-agregar');
    const catalogoFields = document.getElementById('atlas-catalogo-fields');
    const direccionBusqueda = document.getElementById('atlas-direccion-busqueda');
    const direccionMapaCont = document.getElementById('atlas-direccion-mapa');
    const direccionCoordenadas = document.getElementById('atlas-direccion-coordenadas');
    const btnConfirmarDireccion = document.getElementById('atlas-direccion-confirmar');
    const tablaSelector = '#atlasTablaSucursales';
    const permisosSucursal = window.ATLAS_PERMISOS_SUCURSAL || { paso1: false, paso2: false };
    const atlasTabStorageKey = 'atlas_catalogos_tab_activa';
    let atlasConfiguracionCalidad = { sin_telefono_es_error: 0 };
    const atlasIconosClasificacion = [
        'fa-solid fa-gem','fa-solid fa-medal','fa-solid fa-award','fa-solid fa-certificate','fa-solid fa-lightbulb','fa-solid fa-star','fa-solid fa-crown','fa-solid fa-trophy',
        'fa-solid fa-water','fa-solid fa-sailboat','fa-solid fa-ship','fa-solid fa-fish','fa-solid fa-wheat-awn','fa-solid fa-seedling','fa-solid fa-tree','fa-solid fa-mountain','fa-solid fa-mountain-sun','fa-solid fa-city',
        'fa-solid fa-bolt','fa-solid fa-bolt-lightning','fa-solid fa-fire','fa-solid fa-route','fa-solid fa-compass','fa-solid fa-landmark','fa-solid fa-handshake','fa-solid fa-shield-halved','fa-solid fa-location-dot','fa-solid fa-store','fa-solid fa-building','fa-solid fa-tags','fa-solid fa-chart-line',
        'fa-solid fa-chart-simple','fa-solid fa-arrow-trend-up','fa-solid fa-arrow-trend-down','fa-solid fa-triangle-exclamation','fa-solid fa-circle-exclamation','fa-solid fa-circle-xmark',
        'fa-solid fa-ban','fa-solid fa-skull-crossbones','fa-solid fa-thumbs-down','fa-solid fa-face-frown','fa-solid fa-face-sad-tear','fa-solid fa-bug','fa-solid fa-bomb',
        'fa-solid fa-fire-flame-curved','fa-solid fa-circle-minus','fa-solid fa-down-long','fa-solid fa-arrow-down','fa-solid fa-arrow-down-short-wide','fa-solid fa-link-slash',
        'fa-solid fa-unlink','fa-solid fa-lock','fa-solid fa-lock-open','fa fa-shield-alt','fa-solid fa-user-slash','fa-solid fa-user-xmark','fa-solid fa-store-slash',
        'fa-solid fa-house-circle-xmark','fa-solid fa-location-crosshairs','fa-solid fa-location-pin-lock','fa-solid fa-map-location-dot','fa-solid fa-magnifying-glass-chart',
        'fa-solid fa-seedling','fa-solid fa-hand-holding-dollar','fa-solid fa-sack-dollar','fa-solid fa-scale-unbalanced','fa-solid fa-hourglass-half','fa-solid fa-clock-rotate-left'
    ];
    let sucursales = [];
    let atlasCalidadSucursales = { errores: [], sinCoordenadas: [] };
    let catalogos = { divisiones: [], divisionales: [], regionales: [], supervisores: [], asesores: [], personas_comercial: [], distribuidores: [], presencias_distribuidores: [], estados_presencia: [], clasificaciones: [], sucursales_pendientes: [] };
    let atlasTabla = null;
    let tablasCatalogo = {};
    let atlasClasificacionesSortable = null;
    let atlasMapaCargando = null;
    let atlasTablasCargando = new Set();
    let atlasSwalLoaderAbierto = false;
    let atlasQuickAddContext = null;
    let atlasAsignacionDivisionContext = null;
    let atlasActualizacionesDivisionalesCache = null;
    let atlasActualizacionesDivisionalesPromise = null;
    let atlasDireccionMapa = null;
    let atlasDireccionMarker = null;
    let atlasDireccionAutocomplete = null;
    let atlasDireccionGeocoder = null;
    let atlasDireccionActual = null;
    let atlasDireccionContext = null;
    let atlasDireccionMapClickReady = false;
    let atlasUbicacionMapa = null;
    let atlasUbicacionMarker = null;
    let atlasDistribuidorPresencias = [];
    let atlasImportarDistribuidoresModal = null;

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
    function actualizarCoincidenciaNombreSucursal() {
        if (!formSucursal) return;
        const input = formSucursal.elements.sucursal;
        const aviso = document.getElementById('atlas-sucursal-coincidencia');
        if (!input || !aviso) return;
        const actualId = String(getFormValue(formSucursal, 'id') || '');
        const clave = atlasClaveCalidad(input.value || '');
        aviso.classList.add('d-none');
        aviso.textContent = '';
        input.classList.remove('is-invalid');
        if (clave.length < 4) return;
        const coincidencia = (sucursales || []).find(row => {
            if (String(row.id || '') === actualId) return false;
            const nombre = atlasClaveCalidad(row.sucursal || '');
            if (!nombre) return false;
            return nombre === clave || (clave.length >= 8 && (nombre.includes(clave) || clave.includes(nombre)));
        });
        if (!coincidencia) return;
        const texto = (atlasClaveCalidad(coincidencia.sucursal || '') === clave ? 'Ya existe una sucursal con ese nombre: ' : 'Coincidencia posible con: ')
            + (coincidencia.sucursal || 'Sin nombre')
            + (coincidencia.fk_sucursal ? ' · FK ' + coincidencia.fk_sucursal : '');
        aviso.textContent = texto;
        aviso.classList.remove('d-none');
        input.classList.add('is-invalid');
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
                ['asesor_id', 'Sin asesor']
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
        const contarSinTelefono = Number(atlasConfiguracionCalidad.sin_telefono_es_error || 0) === 1;
        const errores = Object.keys(issuesById).map(id => issuesById[id]).filter(item => {
            if (contarSinTelefono) return true;
            return item.issues.some(issue => issue.codigo !== 'sin_telefono');
        }).sort((a, b) => {
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
    function aplicarConfiguracionCalidad(config) {
        atlasConfiguracionCalidad = Object.assign({ sin_telefono_es_error: 0 }, config || {});
        if (configSinTelefonoError) {
            configSinTelefonoError.checked = Number(atlasConfiguracionCalidad.sin_telefono_es_error || 0) === 1;
        }
        if (configSinTelefonoStatus) configSinTelefonoStatus.textContent = 'Guardado';
    }
    async function guardarConfiguracionCalidad() {
        if (!configSinTelefonoError) return;
        const valor = configSinTelefonoError.checked ? 1 : 0;
        atlasConfiguracionCalidad.sin_telefono_es_error = valor;
        if (configSinTelefonoStatus) configSinTelefonoStatus.textContent = 'Guardando...';
        atlasEvaluarCalidadSucursales();
        try {
            const res = await fetch('/Atlas/guardarConfiguracionCalidadSucursales', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ sin_telefono_es_error: valor })
            });
            const data = await res.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo guardar.');
            aplicarConfiguracionCalidad(data.datos || { sin_telefono_es_error: valor });
            atlasEvaluarCalidadSucursales();
        } catch (err) {
            configSinTelefonoError.checked = !configSinTelefonoError.checked;
            atlasConfiguracionCalidad.sin_telefono_es_error = configSinTelefonoError.checked ? 1 : 0;
            atlasEvaluarCalidadSucursales();
            if (configSinTelefonoStatus) configSinTelefonoStatus.textContent = 'Error';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'No se guardó la configuración', text: err.message || 'Intenta nuevamente.' });
            }
        }
    }
    function formToJson(form) {
        const data = {};
        Array.from(new FormData(form).entries()).forEach(pair => { data[pair[0]] = pair[1]; });
        if (form) {
            form.querySelectorAll('[disabled][name]').forEach(el => {
                if (data[el.name] == null) data[el.name] = el.value || '';
            });
        }
        return data;
    }
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
        if (atlasSwalLoaderAbierto) return;
        if (typeof Swal === 'undefined') {
            setTimeout(function () {
                if (atlasTablasCargando.size > 0) atlasMostrarLoaderGlobal();
            }, 60);
            return;
        }
        atlasSwalLoaderAbierto = true;
        Swal.fire({
            title: 'Procesando su petición',
            text: 'Espere un momento...',
            imageUrl: '/assets/img/wait.svg',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
    }
    function atlasOcultarLoaderGlobal() { if (atlasTablasCargando.size > 0 || !atlasSwalLoaderAbierto) return; atlasSwalLoaderAbierto = false; if (typeof Swal !== 'undefined') Swal.close(); }
    function atlasSetTablaLoading(key, loading, texto) {
        const wrap = atlasWrapTabla(key);
        if (loading) { atlasTablasCargando.add(key); atlasMostrarLoaderGlobal(); } else { atlasTablasCargando.delete(key); }
        if (wrap) { if (texto) wrap.setAttribute('data-atlas-loading-label', texto); wrap.classList.toggle('atlas-loading', !!loading); }
        if (!loading) atlasOcultarLoaderGlobal();
    }
    function atlasSetCatalogosLoading(loading) { ['divisiones','asigna_divisiones','distribuidores','clasificaciones','sucursales_pendientes'].forEach(key => atlasSetTablaLoading(key, loading)); }
    function destruirSelectBuscador(el) { if (window.jQuery && jQuery.fn.select2 && jQuery(el).hasClass('select2-hidden-accessible')) jQuery(el).select2('destroy'); }
    function inicializarSelectBuscador(el) {
        if (!window.jQuery || !jQuery.fn.select2 || !el) return;
        const modal = jQuery(el).closest('.modal');
        const shell = jQuery(el).closest('.atlas-shell');
        const config = { width: '100%', dropdownParent: modal.length ? modal : (shell.length ? shell : jQuery(document.body)) };
        if (el.id === 'atlas-sucursal-clasificacion') {
            config.templateResult = renderClasificacionSelect2;
            config.templateSelection = renderClasificacionSelect2;
        }
        if (jQuery(el).hasClass('select2-hidden-accessible')) jQuery(el).select2('destroy');
        jQuery(el).select2(config);
        if (formSucursal && el.form === formSucursal && ['division_id','divisional_id','regional_id','supervisor_id'].includes(el.name)) {
            jQuery(el)
                .off('change.atlasSucursalCascade select2:select.atlasSucursalCascade select2:close.atlasSucursalCascade')
                .on('change.atlasSucursalCascade select2:select.atlasSucursalCascade select2:close.atlasSucursalCascade', function (ev) {
                    const value = ev && ev.params && ev.params.data ? ev.params.data.id : this.value;
                    manejarCambioCascadaSucursal(this.name, value);
                });
        }
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
    function renderEstatusDistribuidor(row) {
        const v = String(row && row.estatus || (Number(row && row.activo || 0) === 1 ? 'activo' : 'inactivo')).toLowerCase();
        const labels = { activo: 'Activo', inactivo: 'Inactivo', suspendido: 'Suspendido', bloqueado: 'Bloqueado', pausado: 'Pausado', inhabilitado: 'Inhabilitado' };
        const icon = v === 'activo' ? 'fa-circle-check' : (v === 'bloqueado' || v === 'inhabilitado' ? 'fa-ban' : 'fa-circle-pause');
        const cls = v === 'activo' ? 'atlas-badge-ok' : (v === 'pausado' ? 'atlas-badge-warn' : 'atlas-badge-off');
        return '<span class="atlas-badge ' + cls + '"><i class="fa-solid ' + icon + '"></i>' + esc(labels[v] || 'Inactivo') + '</span>';
    }
    function renderConstanciaFiscal(row) {
        const url = String(row && row.constancia_fiscal_url || '').trim();
        if (!url) return '<span class="atlas-badge atlas-badge-warn"><i class="fa-solid fa-file-circle-exclamation"></i>Sin constancia</span>';
        return '<div class="atlas-field-row"><a class="atlas-badge atlas-badge-ok" href="' + esc(url) + '" target="_blank" rel="noopener"><i class="fa-solid fa-file-circle-check"></i>Constancia cargada</a><span class="atlas-muted">' + esc(row.constancia_fiscal_at_fmt || row.constancia_fiscal_nombre || '') + '</span></div>';
    }
    function renderEstadoCuentaDistribuidor(row) {
        const url = String(row && row.__SPARTA_SECRET_REDACTED___url || '').trim();
        if (!url) return '<span class="atlas-badge atlas-badge-warn"><i class="fa-solid fa-file-circle-exclamation"></i>Sin estado de cuenta</span>';
        return '<div class="atlas-field-row"><a class="atlas-badge atlas-badge-ok" href="' + esc(url) + '" target="_blank" rel="noopener"><i class="fa-solid fa-file-circle-check"></i>Estado de cuenta cargado</a><span class="atlas-muted">' + esc(row.__SPARTA_SECRET_REDACTED___at_fmt || row.__SPARTA_SECRET_REDACTED___nombre || '') + '</span></div>';
    }
    function renderErrorDivision(row) {
        return Number(row && row.divisional_duplicado || 0) === 1
            || (row && row.divisional_id && !row.divisional_persona_id && String(row.tipo_asignacion || '') !== 'vacante')
            || (row && String(row.divisional_persona_estatus || '').toLowerCase() === 'baja')
            ? '<span class="atlas-badge atlas-badge-off ms-2"><i class="fa-solid fa-triangle-exclamation"></i>Error</span>'
            : '';
    }
    function obtenerErroresDivisiones() {
        const activas = (catalogos.divisiones || []).filter(row => Number(row.activo || 0) === 1 && String(row.divisional_id || '').trim() !== '');
        const porDivisional = new Map();
        activas.forEach(row => {
            const key = String(row.divisional_id || '').trim();
            if (!porDivisional.has(key)) porDivisional.set(key, []);
            porDivisional.get(key).push(row);
        });
        const errores = [];
        porDivisional.forEach(rows => {
            if (rows.length < 2) return;
            rows.forEach(row => {
                errores.push({
                    row: row,
                    divisional: row.divisional_nombre || 'Divisional sin nombre',
                    similares: rows.filter(item => String(item.id || '') !== String(row.id || '')).map(item => item.nombre || 'División sin nombre')
                });
            });
        });
        activas.filter(row => !row.divisional_persona_id && String(row.tipo_asignacion || '') !== 'vacante').forEach(row => {
            errores.push({
                row: row,
                divisional: row.divisional_nombre || 'Divisional sin persona',
                similares: ['No existe vínculo con empleado de Accesos Atlas']
            });
        });
        activas.filter(row => String(row.divisional_persona_estatus || '').toLowerCase() === 'baja').forEach(row => {
            errores.push({
                row: row,
                divisional: row.divisional_nombre || 'Colaborador dado de baja',
                similares: ['Capital Humano dio de baja a esta persona' + (row.divisional_fecha_baja_fmt ? ' el ' + row.divisional_fecha_baja_fmt : '') + '. Actualiza la estructura para poder continuar operando sin errores.']
            });
        });
        return errores;
    }
    function renderErroresDivisionesCard() {
        const card = document.getElementById('atlas-divisiones-error-card');
        const totalEl = document.getElementById('atlas-divisiones-error-total');
        if (!card || !totalEl) return;
        const errores = obtenerErroresDivisiones();
        card.style.display = errores.length ? '' : 'none';
        totalEl.textContent = errores.length + ' error' + (errores.length === 1 ? '' : 'es');
    }
    function obtenerDivisionalesSinUsoLocal() {
        const usados = new Set((catalogos.divisiones || [])
            .filter(row => Number(row.activo || 0) === 1 && String(row.divisional_id || '').trim() !== '')
            .map(row => String(row.divisional_id)));
        return (catalogos.divisionales || [])
            .filter(row => Number(row.activo || 0) === 1 && !usados.has(String(row.id || '')));
    }
    function renderActualizacionesDivisionalesCard() {
        const card = document.getElementById('atlas-divisiones-actualizaciones-card');
        const totalEl = document.getElementById('atlas-divisiones-actualizaciones-total');
        if (!card || !totalEl) return;
        const sinUso = obtenerDivisionalesSinUsoLocal().length;
        totalEl.textContent = sinUso + ' sin uso';
    }
    function abrirErroresDivisiones() {
        const errores = obtenerErroresDivisiones();
        if (divisionesErrorResumen) {
            divisionesErrorResumen.textContent = errores.length
                ? 'Cada registro marcado tiene un problema operativo: divisional duplicado, sin vínculo válido o colaborador dado de baja por Capital Humano. Sin actualizar la estructura pueden presentarse errores de operación.'
                : 'No hay errores activos en divisionales.';
        }
        if (divisionesErrorModalList) {
            divisionesErrorModalList.innerHTML = errores.length ? errores.map(item => {
                const row = item.row || {};
                const esBaja = String(row.divisional_persona_estatus || '').toLowerCase() === 'baja';
                return '<div class="atlas-division-error-item">'
                    + '<div>'
                    + '<div class="atlas-division-error-title"><i class="fa-solid fa-diagram-project me-1 text-danger"></i>' + esc(row.nombre || 'División sin nombre') + '</div>'
                    + '<div class="atlas-division-error-meta">Divisional: <strong>' + esc(item.divisional) + '</strong></div>'
                    + '<div class="atlas-division-error-meta">' + (esBaja ? 'Motivo: ' : 'Comparte similitud con: ') + esc(item.similares.join(' / ') || 'Sin comparación') + '</div>'
                    + '</div>'
                    + '<button type="button" class="btn btn-sm btn-primary" data-atlas-editar-error-division="' + esc(row.id || '') + '"><i class="fa-solid fa-pen-to-square me-1"></i>Editar</button>'
                    + '</div>';
            }).join('') : '<div class="atlas-empty">Sin errores activos.</div>';
        }
        mostrarModal(modalErroresDivisionesEl);
    }
    function renderActualizacionesDivisionales(datos) {
        const disponibles = Array.isArray(datos && datos.disponibles) ? datos.disponibles : [];
        const sinUso = Array.isArray(datos && datos.sin_uso) ? datos.sin_uso : [];
        if (divisionesActualizacionesResumen) {
            divisionesActualizacionesResumen.textContent = 'Agrega colaboradores operativos como divisional activo o saca del catálogo los divisionales que no están ocupados en ninguna división activa.';
        }
        if (divisionesDisponiblesTotal) divisionesDisponiblesTotal.textContent = String(disponibles.length);
        if (divisionesSinUsoTotal) divisionesSinUsoTotal.textContent = String(sinUso.length);
        if (divisionesDisponiblesSelect) {
            destruirSelectBuscador(divisionesDisponiblesSelect);
            divisionesDisponiblesSelect.innerHTML = '<option value="">Selecciona colaborador</option>' + disponibles.map(row => {
                const meta = [row.numero_empleado ? '#' + row.numero_empleado : '', row.puesto || '', row.area || row.direccion || ''].filter(Boolean).join(' · ');
                return '<option value="' + esc(row.persona_id || '') + '">' + esc((row.nombre || 'Sin nombre') + (meta ? ' · ' + meta : '')) + '</option>';
            }).join('');
            divisionesDisponiblesSelect.disabled = disponibles.length === 0;
            inicializarSelectBuscador(divisionesDisponiblesSelect);
        }
        if (divisionesDisponiblesAgregar) {
            divisionesDisponiblesAgregar.disabled = disponibles.length === 0;
        }
        if (divisionesSinUsoList) {
            divisionesSinUsoList.innerHTML = sinUso.length ? sinUso.map(row => {
                const esVacante = String(row.tipo_asignacion || '') === 'vacante';
                const meta = esVacante ? 'Vacante sin división activa' : [row.numero_empleado ? '#' + row.numero_empleado : '', row.puesto || '', row.area || row.direccion || ''].filter(Boolean).join(' · ');
                const fechas = [
                    row.fecha_alta_fmt ? 'Alta: ' + row.fecha_alta_fmt : '',
                    row.fecha_baja_fmt ? 'Baja: ' + row.fecha_baja_fmt : '',
                    row.motivo_baja ? 'Motivo baja: ' + row.motivo_baja : ''
                ].filter(Boolean).join(' · ');
                return '<div class="atlas-division-error-item">'
                    + '<div>'
                    + '<div class="atlas-division-error-title"><i class="fa-solid ' + (esVacante ? 'fa-user-clock text-warning' : 'fa-user-minus text-danger') + ' me-1"></i>' + esc(row.nombre_vacante || row.nombre || 'Sin nombre') + '</div>'
                    + '<div class="atlas-division-error-meta">' + esc(meta || 'Sin uso') + '</div>'
                    + (fechas ? '<div class="atlas-division-error-meta">' + esc(fechas) + '</div>' : '')
                    + '</div>'
                    + '<button type="button" class="btn btn-sm btn-label-danger" data-atlas-desactivar-divisional="' + esc(row.id || '') + '"><i class="fa-solid fa-circle-minus me-1"></i>Sacar</button>'
                    + '</div>';
            }).join('') : '<div class="atlas-empty">No hay divisionales sin uso.</div>';
        }
    }
    function setDisponiblesDivisionalesLoading(texto) {
        if (divisionesDisponiblesSelect) {
            destruirSelectBuscador(divisionesDisponiblesSelect);
            divisionesDisponiblesSelect.innerHTML = '<option value="">' + esc(texto || 'Cargando colaboradores...') + '</option>';
            divisionesDisponiblesSelect.disabled = true;
            inicializarSelectBuscador(divisionesDisponiblesSelect);
        }
        if (divisionesDisponiblesAgregar) divisionesDisponiblesAgregar.disabled = true;
    }
    async function cargarActualizacionesDivisionalesData(force) {
        if (!force && atlasActualizacionesDivisionalesCache) {
            return atlasActualizacionesDivisionalesCache;
        }
        if (!force && atlasActualizacionesDivisionalesPromise) {
            return atlasActualizacionesDivisionalesPromise;
        }
        atlasActualizacionesDivisionalesPromise = fetch('/Atlas/getActualizacionesDivisionales', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudieron cargar actualizaciones.');
                atlasActualizacionesDivisionalesCache = data.datos || {};
                return atlasActualizacionesDivisionalesCache;
            })
            .finally(() => {
                atlasActualizacionesDivisionalesPromise = null;
            });
        return atlasActualizacionesDivisionalesPromise;
    }
    async function abrirActualizacionesDivisionales() {
        if (atlasActualizacionesDivisionalesCache) {
            renderActualizacionesDivisionales(atlasActualizacionesDivisionalesCache);
        } else {
            if (divisionesActualizacionesResumen) divisionesActualizacionesResumen.textContent = 'Revisando divisionales...';
            setDisponiblesDivisionalesLoading('Cargando colaboradores...');
            if (divisionesSinUsoList) divisionesSinUsoList.innerHTML = '<div class="atlas-empty">Cargando...</div>';
        }
        mostrarModal(modalActualizacionesDivisionalesEl);
        try {
            const datos = await cargarActualizacionesDivisionalesData(false);
            renderActualizacionesDivisionales(datos);
        } catch (err) {
            if (divisionesActualizacionesResumen) divisionesActualizacionesResumen.textContent = err.message || 'No se pudieron cargar actualizaciones.';
            setDisponiblesDivisionalesLoading('Sin datos disponibles');
            if (divisionesSinUsoList) divisionesSinUsoList.innerHTML = '<div class="atlas-empty">Sin datos disponibles.</div>';
        }
    }
    function abrirActualizacionesDesdeAsignacion() {
        const valores = formToJson(formCatalogo);
        atlasAsignacionDivisionContext = {
            division_id: valores.division_id || '',
            asignacion_id: valores.id || valores.division_id || '',
            tipo_asignacion: 'persona'
        };
        cerrarModal(modalCatalogoEl);
        setTimeout(abrirActualizacionesDivisionales, 180);
    }
    function reabrirAsignacionDivisionConDivisional(divisionalId) {
        const ctx = atlasAsignacionDivisionContext || {};
        atlasAsignacionDivisionContext = null;
        const divisionId = ctx.division_id || ctx.asignacion_id || '';
        const row = (catalogos.divisiones || []).find(item => String(item.id || '') === String(divisionId || '')) || null;
        cerrarModal(modalActualizacionesDivisionalesEl);
        if (!row) return;
        setTimeout(() => abrirCatalogo('asigna_division', row, {
            prefill: {
                division_id: divisionId,
                tipo_asignacion: 'persona',
                divisional_id: divisionalId || ''
            }
        }), 180);
    }
    async function refrescarActualizacionesDivisionales() {
        atlasActualizacionesDivisionalesCache = null;
        const [datos] = await Promise.all([
            cargarActualizacionesDivisionalesData(true),
            cargarCatalogos({ silencioso: true })
        ]);
        renderActualizacionesDivisionales(datos);
    }
    function divisionalesDisponiblesDivision(idActual) {
        const actual = String(idActual || '');
        const usados = new Set((catalogos.divisiones || [])
            .filter(row => Number(row.activo || 0) === 1 && String(row.id || '') !== actual && String(row.divisional_id || '').trim() !== '')
            .map(row => String(row.divisional_id)));
        return (catalogos.divisionales || []).filter(row => !usados.has(String(row.id || '')));
    }
    function renderEditar(tipo, id) {
        const extra = tipo === 'clasificacion'
            ? '<button type="button" class="btn btn-sm btn-warning text-white" data-atlas-reglas-clasificacion="' + esc(id || '') + '" title="Reglas de negocio" aria-label="Reglas de negocio"><i class="fa-solid fa-trophy"></i></button>'
            : '';
        return '<div class="atlas-action-buttons"><button type="button" class="btn btn-sm btn-primary" data-atlas-editar="' + esc(tipo) + '" data-id="' + esc(id || '') + '" title="Editar" aria-label="Editar"><i class="fa fa-edit"></i></button>' + extra + '</div>';
    }
    function renderEliminarDivision(row) {
        return '<div class="atlas-action-buttons mt-1"><button type="button" class="btn btn-sm btn-label-danger" data-atlas-eliminar-division="' + esc(row && row.id || '') + '" data-nombre="' + esc(row && row.nombre || 'División') + '" title="Eliminar división" aria-label="Eliminar división"><i class="fa-solid fa-trash-can"></i></button></div>';
    }
    function opcionesDivisionesActivas() {
        return (catalogos.divisiones || []).filter(row => String(row.activo ?? '1') === '1');
    }
    function llenarSelectFusion(select, rows, placeholder) {
        if (!select) return;
        select.innerHTML = placeholder ? '<option value="">' + esc(placeholder) + '</option>' : '';
        rows.forEach(row => {
            const opt = document.createElement('option');
            opt.value = row.id || '';
            opt.textContent = row.nombre || 'División';
            select.appendChild(opt);
        });
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery(select).trigger('change.select2');
        }
    }
    function abrirFusionDivisiones() {
        const divisiones = opcionesDivisionesActivas();
        if (divisiones.length < 2) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'No hay divisiones suficientes', text: 'Necesitas al menos dos divisiones activas para fusionar.' });
            return;
        }
        if (formFusionDivisiones) formFusionDivisiones.reset();
        const destino = document.getElementById('atlas-fusion-division-destino');
        const origenes = document.getElementById('atlas-fusion-division-origenes');
        const divisional = document.getElementById('atlas-fusion-divisional');
        llenarSelectFusion(destino, divisiones, 'Selecciona división');
        llenarSelectFusion(origenes, divisiones, '');
        llenarSelectFusion(divisional, (catalogos.divisionales || []).filter(row => String(row.activo ?? '1') === '1'), 'Conservar responsable actual');
        refrescarSelectBuscadores(modalFusionDivisionesEl);
        actualizarOpcionesFusionDivisiones();
        actualizarResumenFusionDivisiones();
        mostrarModal(modalFusionDivisionesEl);
    }
    function actualizarOpcionesFusionDivisiones() {
        const destino = document.getElementById('atlas-fusion-division-destino');
        const origenes = document.getElementById('atlas-fusion-division-origenes');
        const nombre = document.getElementById('atlas-fusion-nuevo-nombre');
        if (!destino || !origenes) return;
        const destinoId = String(destino.value || '');
        const actuales = Array.from(origenes.selectedOptions).map(opt => String(opt.value || ''));
        const divisiones = opcionesDivisionesActivas().filter(row => String(row.id || '') !== destinoId);
        llenarSelectFusion(origenes, divisiones, '');
        actuales.filter(id => id && id !== destinoId).forEach(id => {
            const opt = Array.from(origenes.options).find(item => String(item.value || '') === id);
            if (opt) opt.selected = true;
        });
        if (nombre && destinoId && !String(nombre.value || '').trim()) {
            const row = (catalogos.divisiones || []).find(item => String(item.id || '') === destinoId);
            nombre.value = row && row.nombre ? row.nombre : '';
        }
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery(origenes).trigger('change.select2');
        }
    }
    function actualizarResumenFusionDivisiones() {
        const destinoId = String(document.getElementById('atlas-fusion-division-destino')?.value || '');
        const origenIds = Array.from(document.getElementById('atlas-fusion-division-origenes')?.selectedOptions || []).map(opt => String(opt.value || '')).filter(Boolean);
        const destino = (catalogos.divisiones || []).find(row => String(row.id || '') === destinoId);
        const origenes = (catalogos.divisiones || []).filter(row => origenIds.includes(String(row.id || '')));
        const resumen = document.getElementById('atlas-fusion-resumen');
        if (!resumen) return;
        if (!destinoId || origenes.length === 0) {
            resumen.innerHTML = 'Selecciona las divisiones para ver el resumen de la fusión.';
            return;
        }
        resumen.innerHTML = '<div class="fw-bold mb-1"><i class="fa-solid fa-code-merge me-1"></i>' + esc(origenes.length) + ' división(es) se integran a ' + esc(destino && destino.nombre || 'la división final') + '</div>' +
            '<div class="small text-muted">Se reasignarán sucursales y regionales de: ' + esc(origenes.map(row => row.nombre || 'División').join(', ')) + '.</div>';
    }
    async function submitFusionDivisiones(ev) {
        ev.preventDefault();
        const destinoId = document.getElementById('atlas-fusion-division-destino')?.value || '';
        const origenIds = Array.from(document.getElementById('atlas-fusion-division-origenes')?.selectedOptions || []).map(opt => opt.value).filter(Boolean);
        const payload = {
            division_destino_id: destinoId,
            division_origen_ids: origenIds,
            nuevo_nombre: document.getElementById('atlas-fusion-nuevo-nombre')?.value || '',
            divisional_id: document.getElementById('atlas-fusion-divisional')?.value || '',
            motivo: document.getElementById('atlas-fusion-motivo')?.value || ''
        };
        try {
            const data = await guardarJson('/Atlas/fusionarDivisiones', payload);
            cerrarModal(modalFusionDivisionesEl);
            await cargarCatalogos({ silencioso: true });
            const stats = data.datos || {};
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Fusión lista',
                    html: 'Se fusionaron <strong>' + esc(stats.divisiones_fusionadas || origenIds.length) + '</strong> división(es).<br>Sucursales reasignadas: <strong>' + esc(stats.sucursales_reasignadas || 0) + '</strong><br>Regionales reasignadas: <strong>' + esc(stats.regionales_reasignadas || 0) + '</strong>'
                });
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo fusionar', text: err.message || 'Revisa las divisiones seleccionadas.' });
        }
    }
    function findCatalogo(key, id) { const actual = String(id || ''); return actual ? (catalogos[key] || []).find(row => String(row.id || '') === actual) || null : null; }
    function guardarAtlasTabActivo(target) {
        try {
            if (target) window.localStorage.setItem(atlasTabStorageKey, target);
            else window.localStorage.removeItem(atlasTabStorageKey);
        } catch (err) {}
    }
    function obtenerAtlasTabActivo() {
        try { return window.localStorage.getItem(atlasTabStorageKey) || ''; } catch (err) { return ''; }
    }
    function restaurarAtlasTabActivo() {
        const target = obtenerAtlasTabActivo();
        if (!target) return;
        const btn = document.querySelector('#atlas-tabs [data-bs-target="' + target + '"]');
        const pane = document.querySelector(target);
        if (!btn || !pane) {
            guardarAtlasTabActivo('');
            return;
        }
        if (window.bootstrap && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(btn).show();
        } else {
            document.querySelectorAll('#atlas-tabs .nav-link').forEach(el => el.classList.toggle('active', el === btn));
            document.querySelectorAll('#atlas-tabs + .tab-content > .tab-pane').forEach(el => el.classList.toggle('show', el === pane));
            document.querySelectorAll('#atlas-tabs + .tab-content > .tab-pane').forEach(el => el.classList.toggle('active', el === pane));
        }
        if (target === '#atlas-tab-divisiones') {
            setTimeout(activarAsignaDivisiones, 0);
            setTimeout(activarAsignaDivisiones, 120);
        }
    }
    function clasificacionOportunidadDefaultId() {
        const row = (catalogos.clasificaciones || []).find(item => {
            const nombre = String(item.nombre || '').trim().toLowerCase();
            return Number(item.activo ?? 1) === 1 && (nombre === 'oportunidad' || nombre.endsWith('oportunidad'));
        });
        return row ? String(row.id || '') : '';
    }
    function forzarClasificacionOportunidadNueva() {
        if (!formSucursal || getFormValue(formSucursal, 'id')) return;
        const oportunidadId = clasificacionOportunidadDefaultId();
        if (oportunidadId) setFormValue(formSucursal, 'clasificacion_id', oportunidadId);
    }
    function forzarClasificacionAutomatica() {
        if (!formSucursal) return;
        const el = document.getElementById('atlas-sucursal-clasificacion');
        const original = el ? String(el.dataset.atlasClasificacionOriginal || '') : '';
        if (getFormValue(formSucursal, 'id') && original) {
            setFormValue(formSucursal, 'clasificacion_id', original);
            return;
        }
        forzarClasificacionOportunidadNueva();
    }
    function bloquearClasificacionAutomatica() {
        const el = document.getElementById('atlas-sucursal-clasificacion');
        if (!el) return;
        el.dataset.atlasClasificacionOriginal = getFormValue(formSucursal, 'clasificacion_id');
        el.dataset.atlasLockedOportunidad = '1';
        el.classList.add('atlas-select-locked');
        if (window.jQuery && jQuery.fn && jQuery.fn.select2 && jQuery(el).hasClass('select2-hidden-accessible')) {
            jQuery(el).trigger('change.select2');
        }
    }
    function actualizarCamposDireccionSucursal() {
        if (!formSucursal) return;
        const tieneDireccion = String(getFormValue(formSucursal, 'direccion_sucursal') || '').trim() !== '';
        formSucursal.querySelectorAll('.atlas-location-derived').forEach(el => {
            el.classList.toggle('is-hidden', !tieneDireccion);
        });
    }
    function optionsCatalogo(rows, selected, placeholder) {
        const actual = String(selected || '');
        return '<option value="">' + esc(placeholder || 'Selecciona') + '</option>' + (Array.isArray(rows) ? rows : []).map(row => {
            const id = String(row.id || '');
            const numero = row.numero_empleado ? ('#' + row.numero_empleado + ' - ') : '';
            return '<option value="' + esc(id) + '"' + (id === actual ? ' selected' : '') + '>' + esc(numero + (row.nombre || ('# ' + id))) + '</option>';
        }).join('');
    }
    function opcionesValores(rows, selected, placeholder) {
        const actual = String(selected || '').toLowerCase();
        return '<option value="">' + esc(placeholder || 'Selecciona') + '</option>' + (Array.isArray(rows) ? rows : []).map(label => {
            const value = atlasNormalizarTexto(label).toLowerCase().replace(/\s+/g, '_');
            return '<option value="' + esc(value) + '"' + (value === actual ? ' selected' : '') + '>' + esc(label) + '</option>';
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
            const id = String(row.value || row.id || '');
            return '<option value="' + esc(id) + '"' + (id === actual ? ' selected' : '') + '>' + esc(row.label || row.nombre || ('# ' + id)) + '</option>';
        }).join('');
        el.disabled = !!disabled;
        inicializarSelectBuscador(el);
    }
    function nombrePersonaComercial(row) {
        return String(row && row.nombre || '').trim();
    }
    function personasComercialPorRol(rol) {
        const actual = String(rol || '').toLowerCase();
        return (catalogos.personas_comercial || [])
            .filter(row => String(row.rol_atlas || '').toLowerCase() === actual)
            .map(row => {
                const nombre = nombrePersonaComercial(row);
                const meta = [row.numero_empleado, row.departamento, row.puesto].filter(Boolean).join(' · ');
                return {
                    id: 'persona:' + String(row.persona_id || ''),
                    value: 'persona:' + String(row.persona_id || ''),
                    persona_id: row.persona_id,
                    nombre: nombre,
                    label: nombre + (meta ? ' (' + meta + ')' : ''),
                    tipo_asignacion: 'persona',
                    rol_atlas: actual,
                    jefe_persona_id: row.jefe_persona_id || '',
                    jefe_vacante_id: row.jefe_vacante_id || ''
                };
            });
    }
    function normalizarOpcionAsignacion(row, rol) {
        const tipo = String(row && row.tipo_asignacion || 'persona').toLowerCase();
        const esVacante = tipo === 'vacante';
        const nombre = esVacante
            ? ('Vacante: ' + String(row.nombre_vacante || row.nombre || 'Sin nombre').trim())
            : String(row.nombre || '').trim();
        return Object.assign({}, row, {
            rol_atlas: rol,
            label: nombre,
            nombre: nombre
        });
    }
    function opcionesAsignacionRol(rol, parentKey, parentValue, catalogoKey) {
        const parent = String(parentValue || '');
        const parentInfo = infoAsignacionSeleccionada(parent, parentKey, catalogoKey);
        const desdeCatalogo = (catalogos[catalogoKey] || [])
            .filter(row => !parentKey || String(row[parentKey] || '') === parent)
            .filter(row => String(row.tipo_asignacion || 'persona').toLowerCase() === 'vacante' || String(row.persona_id || '').trim() !== '')
            .map(row => normalizarOpcionAsignacion(row, rol));
        const personas = personasComercialPorRol(rol);
        const personasFiltradas = parentInfo
            ? personas.filter(row => {
                if (parentInfo.persona_id) return String(row.jefe_persona_id || '') === String(parentInfo.persona_id);
                if (parentInfo.vacante_personal_id) return String(row.jefe_vacante_id || '') === String(parentInfo.vacante_personal_id);
                return true;
            })
            : personas;
        if (!parent) return desdeCatalogo.concat(personasFiltradas);
        const idsPersonaConCatalogo = new Set(desdeCatalogo.map(row => String(row.persona_id || '')).filter(Boolean));
        return desdeCatalogo.concat(personasFiltradas.filter(row => !idsPersonaConCatalogo.has(String(row.persona_id || ''))));
    }
    function infoAsignacionSeleccionada(value, parentKey, catalogoKey) {
        const val = String(value || '');
        if (!val) return null;
        if (val.indexOf('persona:') === 0) return { persona_id: val.replace('persona:', ''), vacante_personal_id: '' };
        const mapa = { division_id: 'divisiones', regional_id: 'regionales', supervisor_id: 'supervisores', divisional_id: 'divisionales' };
        const key = mapa[parentKey] || catalogoKey || '';
        const row = key ? findCatalogo(key, val) : null;
        if (!row) return null;
        return {
            persona_id: row.persona_id || row.divisional_persona_id || '',
            vacante_personal_id: row.vacante_personal_id || ''
        };
    }
    function valoresSucursalActuales() {
        return {
            distribuidor_id: getFormValue(formSucursal, 'distribuidor_id'), clasificacion_id: getFormValue(formSucursal, 'clasificacion_id'),
            divisional_id: getFormValue(formSucursal, 'divisional_id'), division_id: getFormValue(formSucursal, 'division_id'), regional_id: getFormValue(formSucursal, 'regional_id'), supervisor_id: getFormValue(formSucursal, 'supervisor_id'), asesor_id: getFormValue(formSucursal, 'asesor_id')
        };
    }
    function resolverJerarquiaSucursal(values) {
        const out = Object.assign({}, values || {});
        const asesor = findCatalogo('asesores', out.asesor_id); if (asesor && !out.supervisor_id) out.supervisor_id = asesor.supervisor_id;
        const supervisor = findCatalogo('supervisores', out.supervisor_id); if (supervisor && !out.regional_id) out.regional_id = supervisor.regional_id;
        const regional = findCatalogo('regionales', out.regional_id); if (regional && !out.division_id) out.division_id = regional.division_id;
        const division = findCatalogo('divisiones', out.division_id); if (division) out.divisional_id = division.divisional_id || '';
        return out;
    }
    function opcionesDivisionalAsignadoDivision(divisionId) {
        const division = findCatalogo('divisiones', divisionId);
        if (!division) return [];
        const divisionalId = String(division.divisional_id || '');
        if (!divisionalId) return [];
        const base = findCatalogo('divisionales', divisionalId);
        if (base) return [base];
        const tipo = String(division.tipo_asignacion || '').toLowerCase();
        const nombreVacante = String(division.nombre_vacante || '').trim();
        const nombre = tipo === 'vacante'
            ? ('Vacante: ' + (nombreVacante || division.divisional_nombre || 'Sin nombre'))
            : (division.divisional_nombre || nombreVacante || 'Divisional asignado');
        return [{ id: divisionalId, nombre: nombre, label: nombre, persona_id: division.divisional_persona_id || '', vacante_personal_id: division.vacante_personal_id || '' }];
    }
    function valorSeleccionAsignacion(catalogoKey, id, personaId) {
        const actual = String(id || '');
        if (actual) return actual;
        const persona = String(personaId || '');
        return persona ? ('persona:' + persona) : '';
    }
    function actualizarCascadaSucursal(values) {
        const v = resolverJerarquiaSucursal(values || valoresSucursalActuales());
        const divisionalId = valorSeleccionAsignacion('divisionales', v.divisional_id, v.divisional_persona_id);
        const divisionId = String(v.division_id || '');
        const regionalId = valorSeleccionAsignacion('regionales', v.regional_id, v.regional_persona_id);
        const supervisorId = valorSeleccionAsignacion('supervisores', v.supervisor_id, v.supervisor_persona_id);
        const asesorId = valorSeleccionAsignacion('asesores', v.asesor_id, v.asesor_persona_id);
        const divisiones = (catalogos.divisiones || []).filter(row => Number(row.activo ?? 1) === 1);
        const divisionalAsignado = opcionesDivisionalAsignadoDivision(divisionId);
        const regionales = divisionId ? opcionesAsignacionRol('regional', 'division_id', divisionId, 'regionales') : [];
        const supervisores = regionalId ? opcionesAsignacionRol('supervisor', 'regional_id', regionalId, 'supervisores') : [];
        const asesores = supervisorId ? opcionesAsignacionRol('asesor', 'supervisor_id', supervisorId, 'asesores') : [];
        llenarSelectCascada('atlas-sucursal-division', divisiones, 'Selecciona división', divisiones.some(row => String(row.id || '') === divisionId) ? divisionId : '', false);
        llenarSelectCascada('atlas-sucursal-divisional', divisionalAsignado, divisionId ? 'Sin divisional asignado' : 'Selecciona división primero', divisionalId, true);
        llenarSelectCascada('atlas-sucursal-regional', regionales, divisionId ? 'Selecciona regional' : 'Primero selecciona división', regionales.some(row => String(row.value || row.id || '') === regionalId) ? regionalId : '', !divisionId);
        llenarSelectCascada('atlas-sucursal-supervisor', supervisores, regionalId ? 'Selecciona supervisor' : 'Primero selecciona regional', supervisores.some(row => String(row.value || row.id || '') === supervisorId) ? supervisorId : '', !regionalId);
        llenarSelectCascada('atlas-sucursal-asesor', asesores, supervisorId ? 'Selecciona asesor' : 'Primero selecciona supervisor', asesores.some(row => String(row.value || row.id || '') === asesorId) ? asesorId : '', !supervisorId);
    }
    function manejarCambioCascadaSucursal(name, value) {
        setTimeout(function () {
            const values = valoresSucursalActuales();
            if (value != null && name) values[name] = String(value || '');
            if (name === 'division_id') { values.divisional_id = ''; values.regional_id = ''; values.supervisor_id = ''; values.asesor_id = ''; }
            if (name === 'divisional_id') { values.divisional_id = ''; }
            if (name === 'regional_id') { values.supervisor_id = ''; values.asesor_id = ''; }
            if (name === 'supervisor_id') values.asesor_id = '';
            actualizarCascadaSucursal(values);
        }, 0);
    }
    async function guardarJson(url, payload) {
        const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, credentials: 'same-origin', body: JSON.stringify(payload) });
        const data = await res.json();
        if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo guardar.');
        return data;
    }
    async function subirConstanciaDistribuidor(id) {
        const input = formCatalogo ? formCatalogo.querySelector('input[name="constancia_fiscal"]') : null;
        const file = input && input.files && input.files[0] ? input.files[0] : null;
        if (!file) return null;
        const fd = new FormData();
        fd.append('id', String(id || ''));
        fd.append('archivo', file, file.name);
        const res = await fetch('/Atlas/subirConstanciaFiscalDistribuidor', { method: 'POST', credentials: 'same-origin', body: fd, headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar la constancia.');
        return data;
    }
    async function subirEstadoCuentaDistribuidor(id) {
        const input = formCatalogo ? formCatalogo.querySelector('input[name="__SPARTA_SECRET_REDACTED__"]') : null;
        const file = input && input.files && input.files[0] ? input.files[0] : null;
        if (!file) return null;
        const fd = new FormData();
        fd.append('id', String(id || ''));
        fd.append('archivo', file, file.name);
        const res = await fetch('/Atlas/subirEstadoCuentaDistribuidor', { method: 'POST', credentials: 'same-origin', body: fd, headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar el estado de cuenta.');
        return data;
    }
    async function descargarTemplateDistribuidores() {
        const url = '/Atlas/descargarTemplateDistribuidores';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Procesando tu solicitud',
                text: 'Estamos descargando la plantilla de distribuidores, espera un momento...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
        }
        try {
            const resp = await fetch(url, { credentials: 'same-origin' });
            if (!resp.ok) throw new Error((await resp.text()).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() || 'No se pudo descargar la plantilla.');
            const blob = await resp.blob();
            const cd = resp.headers.get('Content-Disposition') || '';
            const match = cd.match(/filename="?([^"]+)"?/i);
            const filename = match ? match[1] : 'template_distribuidores_atlas.xlsx';
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
            if (typeof Swal !== 'undefined') Swal.close();
        } catch (err) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo descargar', text: err.message || 'Intenta de nuevo.' });
        }
    }
    function abrirImportarDistribuidores() {
        if (formImportarDistribuidores) formImportarDistribuidores.reset();
        mostrarModal(modalImportarDistribuidoresEl);
    }
    function resumenImportacionDistribuidores(datos) {
        const errores = Array.isArray(datos && datos.errores) ? datos.errores : [];
        const detalleErrores = errores.length ? '<br><small>' + esc(errores.slice(0, 5).map(row => 'Fila ' + row.fila + ': ' + row.mensaje).join(' | ')) + '</small>' : '';
        return 'Creados: ' + Number(datos && datos.creados || 0)
            + ' · Actualizados: ' + Number(datos && datos.actualizados || 0)
            + ' · Desbloqueados: ' + Number(datos && datos.desbloqueados || 0)
            + ' · Errores: ' + errores.length
            + detalleErrores;
    }
    async function enviarImportDistribuidores(desbloquear) {
        const fd = new FormData(formImportarDistribuidores);
        fd.append('desbloquear_existentes', desbloquear ? '1' : '0');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Subiendo layout',
                text: 'Estamos validando distribuidores. No cierres esta ventana.',
                imageUrl: '/assets/img/wait.svg',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
        }
        const res = await fetch('/Atlas/importarDistribuidores', { method: 'POST', credentials: 'same-origin', body: fd, headers: { Accept: 'application/json' } });
        return await res.json();
    }
    async function importarDistribuidoresSubmit(ev) {
        ev.preventDefault();
        if (!formImportarDistribuidores) return;
        try {
            let resp = await enviarImportDistribuidores(false);
            if (resp && resp.requiere_confirmacion_desbloqueo) {
                const bloqueados = ((resp.datos && resp.datos.bloqueados) || []).slice(0, 6).map(row => row.distribuidor + ' (' + row.estatus + ')').join('<br>');
                const confirma = await Swal.fire({
                    icon: 'warning',
                    title: 'Distribuidores bloqueados',
                    html: '<div class="text-start">Ya existen distribuidores bloqueados, pausados o inhabilitados en el layout:<br><br><strong>' + bloqueados + '</strong><br><br>¿Deseas desbloquearlos y actualizar su información?</div>',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desbloquear',
                    cancelButtonText: 'Cancelar'
                });
                if (!confirma.isConfirmed) return;
                resp = await enviarImportDistribuidores(true);
            }
            if (!resp || resp.success === false) throw new Error(resp && (resp.mensaje || resp.error) || 'No se pudo importar.');
            cerrarModal(modalImportarDistribuidoresEl);
            await cargarCatalogos();
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Layout procesado', html: resumenImportacionDistribuidores(resp.datos || {}) });
        } catch (err) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo importar', text: err.message || 'Intenta de nuevo.' });
        }
    }
    function renderPasosSucursal(row) {
        const paso1 = Number(row && row.paso_datos_completos || 0) === 1;
        const paso2 = Number(row && row.paso_asignacion_completa || 0) === 1;
        const iconPaso1 = paso1 ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-xmark"></i>';
        const iconPaso2 = paso2 ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-xmark"></i>';
        return '<div class="atlas-step-stack">'
            + '<span class="atlas-step-pill' + (paso1 ? '' : ' is-pending') + '" title="' + esc(paso1 ? ('Paso 1 completo' + (row.paso_datos_completos_at_fmt ? ' · ' + row.paso_datos_completos_at_fmt : '')) : 'Paso 1 pendiente') + '"><span class="atlas-step-dot">' + iconPaso1 + '</span>Paso 1</span>'
            + '<span class="atlas-step-pill' + (paso2 ? '' : ' is-pending') + '" title="' + esc(paso2 ? ('Paso 2 completo' + (row.paso_asignacion_completa_at_fmt ? ' · ' + row.paso_asignacion_completa_at_fmt : '')) : 'Paso 2 pendiente') + '"><span class="atlas-step-dot">' + iconPaso2 + '</span>Paso 2</span>'
            + '</div>';
    }
    function asignacionSucursalCompleta() {
        return ['divisional_id','division_id','regional_id','supervisor_id','asesor_id'].every(name => !!getFormValue(formSucursal, name));
    }
    function asignacionSucursalIniciada() {
        return ['divisional_id','division_id','regional_id','supervisor_id','asesor_id'].some(name => !!getFormValue(formSucursal, name));
    }
    function campoWrapperSucursal(name) {
        const el = formSucursal && formSucursal.elements[name] ? formSucursal.elements[name] : null;
        if (!el) return null;
        const combo = el.closest('.atlas-combo-row');
        if (combo) return combo.parentElement || combo;
        return el.closest('.atlas-field-wide') || el.closest('.atlas-location-fields') || el.closest('.atlas-coord-row') || el.closest('.atlas-branch-row') || el.closest('div');
    }
    function setCampoSoloLectura(name, locked) {
        const el = formSucursal && formSucursal.elements[name] ? formSucursal.elements[name] : null;
        if (!el) return;
        const isInput = ['INPUT', 'TEXTAREA'].includes(String(el.tagName || '').toUpperCase());
        if (isInput && el.type !== 'hidden') el.readOnly = !!locked;
        el.classList.toggle('atlas-readonly-field', !!locked);
        if (window.jQuery && jQuery.fn && jQuery.fn.select2 && jQuery(el).hasClass('select2-hidden-accessible')) {
            jQuery(el).trigger('change.select2');
        }
    }
    function setCampoVisibleSucursal(name, visible) {
        const wrap = campoWrapperSucursal(name);
        if (wrap) wrap.classList.toggle('atlas-modal-hidden', !visible);
        const el = formSucursal && formSucursal.elements[name] ? formSucursal.elements[name] : null;
        if (el) el.disabled = !visible || name === 'divisional_id';
    }
    function aplicarPermisosSucursalModal() {
        if (!formSucursal) return;
        const puedePaso1 = !!permisosSucursal.paso1;
        const puedePaso2 = !!permisosSucursal.paso2;
        const soloPaso2 = !puedePaso1 && puedePaso2;
        const camposPaso1 = ['fk_sucursal','sucursal','distribuidor_id','clasificacion_id','activo','direccion_sucursal','calle','numero_exterior','numero_interior','estado','municipio','localidad','colonia','codigo_postal','latitud','longitud'];
        const camposPaso2 = ['divisional_id','division_id','regional_id','supervisor_id','asesor_id'];
        camposPaso1.forEach(name => {
            setCampoVisibleSucursal(name, true);
            setCampoSoloLectura(name, soloPaso2);
        });
        camposPaso2.forEach(name => {
            setCampoVisibleSucursal(name, puedePaso2);
            setCampoSoloLectura(name, false);
        });
        const paso2Title = Array.from(formSucursal.querySelectorAll('.atlas-step-section-title')).find(el => (el.textContent || '').includes('Asignación operativa'));
        if (paso2Title) paso2Title.classList.toggle('atlas-modal-hidden', !puedePaso2);
        const addDivision = formSucursal.querySelector('[data-atlas-quick-add="division"]');
        if (addDivision) {
            addDivision.disabled = !puedePaso2;
            addDivision.classList.toggle('atlas-modal-hidden', !puedePaso2);
        }
        const addDistribuidor = formSucursal.querySelector('[data-atlas-quick-add="distribuidor"]');
        if (addDistribuidor) {
            addDistribuidor.disabled = !puedePaso1;
            addDistribuidor.classList.toggle('atlas-modal-hidden', !puedePaso1);
        }
        const nota = document.getElementById('atlas-sucursal-paso2-note');
        if (nota) {
            nota.classList.toggle('atlas-modal-hidden', !puedePaso2);
            nota.textContent = soloPaso2
                ? 'Datos base solo como referencia. Captura únicamente la asignación operativa.'
                : 'Completa estos campos cuando la sucursal ya tenga asignación de gestores.';
        }
    }
    function columnasAtlas() {
        return [
            { data: null, title: '', orderable: false, searchable: false, className: 'atlas-sucursal-avatar-col', render: function (data, renderType, row) { return renderType === 'display' ? '<span class="atlas-sucursal-avatar"><i class="fa-solid fa-motorcycle"></i></span><span class="atlas-sucursal-status-under">' + renderEstatus(row) + '</span>' + renderPasosSucursal(row) : ''; } },
            { data: 'sucursal', title: 'Sucursal', render: function (data, renderType, row) {
                if (renderType !== 'display') return textoPlano(row, ['sucursal','fk_sucursal','distribuidor_nombre','direccion','numero_telefono','nombre_contacto']);
                const direccionBtn = row.direccion ? '<button type="button" class="atlas-location-link mt-1" data-atlas-ubicacion="' + esc(row.id || '') + '"><i class="fa-solid fa-location-dot"></i><span>' + esc(row.direccion) + '</span></button>' : '<span class="atlas-muted">Sin dirección</span>';
                const nuevoIngreso = Number(row.es_nuevo_ingreso || 0) === 1 ? '<span class="atlas-badge atlas-badge-info ms-1" title="Fecha de registro: ' + esc(row.fecha_alta_fmt || '') + '"><i class="fa-solid fa-star"></i>Sucursal de nuevo ingreso</span>' : '';
                return '<div class="atlas-field-row"><span class="atlas-field-value">' + esc(data || 'Sin nombre') + '</span>' + nuevoIngreso + '<span class="atlas-muted">FK ' + esc(row.fk_sucursal || '-') + ' &middot; ' + esc(row.distribuidor_nombre || 'Sin distribuidor') + '</span></div>'
                    + '<div class="atlas-field-row mt-1"><span class="atlas-field-label">Teléfono</span><span class="atlas-muted"><i class="fa-solid fa-phone me-1"></i>' + esc(row.numero_telefono || 'Sin teléfono') + (row.nombre_contacto ? ' &middot; ' + esc(row.nombre_contacto) : '') + '</span></div>'
                    + '<div class="atlas-field-row mt-1">' + direccionBtn + '</div>';
            }},
            { data: 'clasificacion_nombre', title: 'Clasificación', render: function (data, renderType, row) {
                if (renderType !== 'display') return textoPlano(row, ['clasificacion_nombre','clasificacion_id']);
                return '<div class="atlas-classif" style="--atlas-class-color:' + esc(colorClasificacion(row)) + ';"><span class="atlas-classif-dot"></span><i class="' + esc(row.clasificacion_icon_font || 'fa-solid fa-tags') + '"></i><span>' + esc(data || 'Sin clasificación') + '</span></div>';
            }},
            { data: null, title: 'Asignación', render: function (data, renderType, row) { return renderAsignacionSucursal(row, renderType); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('sucursal', row.id) : ''; }}
        ];
    }

    function renderAsignacionSucursal(row, renderType) {
        if (renderType !== 'display') {
            return textoPlano(row, ['distribuidor_nombre','divisional_nombre','regional_nombre','supervisor_nombre','asesor_nombre','activo']);
        }
        const item = function (icono, etiqueta, valor) {
            return '<div class="atlas-assignment-row">'
                + '<span class="atlas-assignment-label"><i class="' + esc(icono) + '"></i>' + esc(etiqueta) + '</span>'
                + '<span class="atlas-assignment-value">' + esc(valor || 'Sin asignar') + '</span>'
                + '</div>';
        };
        return '<div class="atlas-assignment-box">'
            + item('fa-solid fa-building', 'Distribuidor', row.distribuidor_nombre)
            + item('fa-solid fa-user-tie', 'Divisional', row.divisional_nombre)
            + item('fa-solid fa-map-location-dot', 'Regional', row.regional_nombre)
            + item('fa-solid fa-user-check', 'Supervisor', row.supervisor_nombre)
            + item('fa-solid fa-user', 'Asesor', row.asesor_nombre)
            + '</div>';
    }

    function renderFilaSucursalFallback(row) {
        const avatar = columnasAtlas()[0].render(null, 'display', row);
        const sucursal = columnasAtlas()[1].render(row.sucursal, 'display', row);
        const clasificacion = columnasAtlas()[2].render(row.clasificacion_nombre, 'display', row);
        const asignacion = columnasAtlas()[3].render(null, 'display', row);
        const acciones = columnasAtlas()[4].render(null, 'display', row);
        return '<tr>'
            + '<td class="atlas-sucursal-avatar-col">' + avatar + '</td>'
            + '<td>' + sucursal + '</td>'
            + '<td>' + clasificacion + '</td>'
            + '<td>' + asignacion + '</td>'
            + '<td class="text-center">' + acciones + '</td>'
            + '</tr>';
    }

    function renderEstatusTexto(valor) {
        const v = String(valor || 'pendiente').toLowerCase();
        const labels = { pendiente: 'Pendiente', en_revision: 'En revisión', mapeada: 'Mapeada', descartada: 'Descartada', resuelto: 'Resuelto', sin_etapa: 'Sin etapa', sin_sucursal: 'Sin sucursal', revisar_etapa: 'Revisar etapa', sucursal_no_mapeada: 'Sucursal no mapeada' };
        const cls = v === 'mapeada' || v === 'resuelto' ? 'atlas-badge-ok' : (v === 'descartada' ? 'atlas-badge-off' : 'atlas-badge-warn');
        const icon = v === 'mapeada' || v === 'resuelto' ? 'fa-circle-check' : (v === 'descartada' ? 'fa-circle-pause' : 'fa-triangle-exclamation');
        return '<span class="atlas-badge ' + cls + '"><i class="fa-solid ' + icon + '"></i>' + esc(labels[v] || valor || 'Pendiente') + '</span>';
    }

    function renderAccionesSucursalPendiente(row) {
        const id = esc(row && row.id || '');
        return '<div class="atlas-action-buttons">'
            + '<button type="button" class="btn btn-sm btn-label-secondary" data-atlas-pendiente-accion="revision" data-id="' + id + '" title="Marcar en revisión"><i class="fa-solid fa-eye"></i></button>'
            + '<button type="button" class="btn btn-sm btn-primary" data-atlas-pendiente-accion="mapear" data-id="' + id + '" title="Mapear a sucursal Sparta"><i class="fa-solid fa-link"></i></button>'
            + '<button type="button" class="btn btn-sm btn-label-danger" data-atlas-pendiente-accion="descartar" data-id="' + id + '" title="Descartar"><i class="fa-solid fa-ban"></i></button>'
            + '</div>';
    }

    function presenciasDistribuidor(row) {
        const id = String(row && row.id || '');
        const directas = (catalogos.presencias_distribuidores || [])
            .filter(item => String(item.distribuidor_id || '') === id)
            .map(item => ({
                estado_id: String(item.estado_id || ''),
                municipio_id: String(item.municipio_id || ''),
                estado: String(item.estado || '').trim(),
                municipio: String(item.municipio || '').trim()
            }))
            .filter(item => item.estado && item.municipio);
        if (directas.length) return directas;
        const estado = String(row && row.estado || '').trim();
        const municipio = String(row && row.municipio || '').trim();
        return estado && municipio ? [{ estado: estado, municipio: municipio }] : [];
    }

    function renderPresenciaDistribuidor(row, renderType) {
        const presencias = presenciasDistribuidor(row);
        if (renderType !== 'display') {
            return presencias.map(item => item.municipio + ', ' + item.estado).join(' ');
        }
        if (!presencias.length) {
            return '<div class="atlas-presencia-summary"><span class="atlas-badge atlas-badge-off"><i class="fa-solid fa-cloud"></i>Sin presencia física</span><span class="atlas-muted">Sin municipios registrados</span></div>';
        }
        const total = presencias.length;
        const lineas = presencias.slice(0, 3).map(item => '<div>' + esc(item.municipio) + ', ' + esc(item.estado) + '</div>').join('');
        const extra = total > 3 ? '<div>+' + (total - 3) + ' más</div>' : '';
        return '<div class="atlas-presencia-summary">'
            + '<span class="atlas-badge atlas-badge-ok"><i class="fa-solid fa-store"></i>' + total + ' municipio' + (total === 1 ? '' : 's') + '</span>'
            + '<div class="atlas-presencia-summary-lines">' + lineas + extra + '</div>'
            + '</div>';
    }

    function expedienteDistribuidorFaltantes(row) {
        const faltantes = [];
        const requerido = [
            ['nombre_comercial', 'Nombre comercial'],
            ['razon_social', 'Razón social'],
            ['rfc', 'RFC'],
            ['tipo_distribuidor', 'Tipo distribuidor'],
            ['tipo_persona', 'Tipo de persona'],
            ['estatus', 'Estatus'],
            ['nombre_contacto', 'Contacto principal'],
            ['telefono_contacto', 'Teléfono principal'],
            ['email_contacto', 'Correo principal'],
            ['regimen_fiscal', 'Régimen fiscal'],
            ['tipo_motos', 'Tipo de motos'],
            ['canal_venta', 'Canal venta'],
            ['icon_font', 'Icono']
        ];
        requerido.forEach(([key, label]) => {
            if (!String(row && row[key] || '').trim()) faltantes.push(label);
        });
        if (String((row && row.presencia_fisica) ?? '1') === '1' && !presenciasDistribuidor(row).length) {
            faltantes.push('Presencia física');
        }
        return faltantes;
    }

    function renderExpedienteDistribuidor(row) {
        const faltantes = expedienteDistribuidorFaltantes(row);
        if (!faltantes.length) {
            return '<span class="atlas-badge atlas-badge-ok"><i class="fa-solid fa-folder-check"></i>Expediente completo</span>';
        }
        return '<span class="atlas-badge atlas-badge-warn" title="Falta: ' + esc(faltantes.join(', ')) + '"><i class="fa-solid fa-folder-open"></i>Expediente incompleto</span>';
    }

    function columnasCatalogo(tipo) {
        if (tipo === 'sucursales_pendientes') return [
            { data: 'fk_sucursal_origen', title: 'FK origen', render: function (data, renderType) { return renderType === 'display' ? '<span class="atlas-order-pill">' + esc(data || '-') + '</span>' : (data || ''); }},
            { data: 'sucursal_origen', title: 'Sucursal Maxi', render: function (data, renderType, row) { return renderType === 'display' ? '<div class="atlas-field-row"><span class="atlas-field-value">' + esc(data || 'Sin nombre') + '</span><span class="atlas-muted">Fuente ' + esc(row.fuente || 'maxi') + '</span></div>' : (data || ''); }},
            { data: 'distribuidor_origen', title: 'Distribuidor', render: function (data, renderType) { return renderType === 'display' ? '<span class="atlas-field-value">' + esc(data || 'Sin distribuidor') + '</span>' : (data || ''); }},
            { data: 'total_ofertas', title: 'Afectación', render: function (data, renderType, row) { return renderType === 'display' ? '<div class="atlas-field-row"><span class="atlas-field-value">' + esc(data || 0) + ' oferta(s)</span><span class="atlas-muted">' + esc(row.total_creditos || 0) + ' crédito(s)</span></div>' : (parseInt(data, 10) || 0); }},
            { data: 'motivo', title: 'Motivo', render: function (data, renderType) { return renderType === 'display' ? '<span class="atlas-muted">' + esc(data || 'Sin motivo') + '</span>' : (data || ''); }},
            { data: 'estatus', title: 'Estatus', render: function (data, renderType) { return renderType === 'display' ? renderEstatusTexto(data) : (data || ''); }},
            { data: 'fecha_detectado_fmt', title: 'Detectado', render: function (data, renderType, row) { return renderType === 'display' ? '<div class="atlas-field-row"><span class="atlas-field-value">' + esc(data || '-') + '</span><span class="atlas-muted">Act. ' + esc(row.fecha_actualizacion_fmt || '-') + '</span></div>' : (data || ''); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderAccionesSucursalPendiente(row) : ''; }}
        ];
        if (tipo === 'divisiones') return [
            { data: 'nombre', title: 'División', render: function (data, renderType, row) { return renderType === 'display' ? '<div class="atlas-classif" style="--atlas-class-color:' + esc(colorHexSeguro(row.color_hex || '#2563EB')) + ';"><span class="atlas-classif-dot"></span><i class="' + esc(row.icon_font || 'fa-solid fa-diagram-project') + '"></i><span>' + esc(data || 'Sin nombre') + '</span>' + renderErrorDivision(row) + '</div>' : [data || '', row.icon_font || '', row.color_hex || '', Number(row.divisional_duplicado || 0) ? 'Error' : ''].join(' '); }},
            { data: 'activo', title: 'Estatus', render: function (data, renderType, row) { return renderType === 'display' ? renderEstatus(row) : (Number(data || 0) === 1 ? 'Activa' : 'Inactiva'); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('division', row.id) + renderEliminarDivision(row) : ''; }}
        ];
        if (tipo === 'asigna_divisiones') return [
            { data: 'nombre', title: 'División', render: function (data, renderType, row) { return renderType === 'display' ? '<div class="atlas-classif" style="--atlas-class-color:' + esc(colorHexSeguro(row.color_hex || '#2563EB')) + ';"><span class="atlas-classif-dot"></span><i class="' + esc(row.icon_font || 'fa-solid fa-diagram-project') + '"></i><span>' + esc(data || 'Sin nombre') + '</span>' + renderErrorDivision(row) + '</div>' : [data || '', row.icon_font || '', row.color_hex || '', Number(row.divisional_duplicado || 0) ? 'Error' : ''].join(' '); }},
            { data: 'divisional_id', title: 'Divisional activo', render: function (data, renderType, row) {
                if (renderType !== 'display') return [row.divisional_nombre || '', row.divisional_numero_empleado || '', row.tipo_asignacion || '', data || '', Number(row.divisional_duplicado || 0) ? 'Error' : ''].join(' ');
                if (!data) return '<span class="atlas-muted"><i class="fa-solid fa-user-tie me-1"></i>Sin asignar</span>';
                if (String(row.tipo_asignacion || '') === 'vacante') {
                    return '<div><div class="atlas-classif"><i class="fa-solid fa-user-clock text-warning"></i><span>' + esc(row.nombre_vacante || row.divisional_nombre || 'Vacante') + '</span>' + renderErrorDivision(row) + '</div><span class="atlas-badge atlas-badge-warn mt-1"><i class="fa-solid fa-circle-exclamation"></i>Vacante</span></div>';
                }
                if (String(row.divisional_persona_estatus || '').toLowerCase() === 'baja') {
                    const fechaBaja = row.divisional_fecha_baja_fmt ? ' · Baja ' + row.divisional_fecha_baja_fmt : '';
                    return '<div><div class="atlas-classif"><i class="fa-solid fa-user-xmark text-danger"></i><span>' + esc(row.divisional_nombre || 'Colaborador dado de baja') + '</span>' + renderErrorDivision(row) + '</div><span class="atlas-badge atlas-badge-off mt-1"><i class="fa-solid fa-circle-exclamation"></i>Baja Capital Humano</span><small class="text-danger fw-semibold d-block mt-1">Actualiza estructura' + esc(fechaBaja) + '</small></div>';
                }
                const empleado = row.divisional_numero_empleado ? '<small class="text-muted fw-semibold d-block mt-1"><i class="fa-solid fa-id-badge me-1"></i>#' + esc(row.divisional_numero_empleado) + '</small>' : '<small class="text-warning fw-semibold d-block mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Sin persona vinculada</small>';
                return '<div><div class="atlas-classif"><i class="fa-solid fa-user-tie text-secondary"></i><span>' + esc(row.divisional_nombre || 'Sin divisional') + '</span>' + renderErrorDivision(row) + '</div>' + empleado + '</div>';
            }},
            { data: 'activo', title: 'Estatus', render: function (data, renderType, row) { return renderType === 'display' ? renderEstatus(row) : (Number(data || 0) === 1 ? 'Activa' : 'Inactiva'); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('asigna_division', row.id) : ''; }}
        ];
        if (tipo === 'distribuidores') return [
            { data: 'nombre', title: 'Distribuidor', render: function (data, renderType, row) { return renderType === 'display' ? '<div class="atlas-field-row"><div class="atlas-classif"><i class="' + esc(row.icon_font || 'fa-solid fa-building') + ' text-secondary"></i><span>' + esc(data || 'Sin nombre') + '</span></div><div class="d-flex flex-column align-items-start gap-1 mt-1"><span class="atlas-badge atlas-badge-info"><i class="fa-solid fa-id-card"></i>' + (String(row.tipo_persona || 'moral') === 'fisica' ? 'Persona física' : 'Persona moral') + '</span>' + renderExpedienteDistribuidor(row) + '</div></div>' : [data || '', row.icon_font || '', row.tipo_persona || '', expedienteDistribuidorFaltantes(row).length ? 'Expediente incompleto' : 'Expediente completo'].join(' '); }},
            { data: null, title: 'Presencia', render: function (data, renderType, row) { return renderPresenciaDistribuidor(row, renderType); }},
            { data: 'estatus', title: 'Estatus', render: function (data, renderType, row) { return renderType === 'display' ? renderEstatusDistribuidor(row) : (data || ''); }},
            { data: null, title: 'Acciones', orderable: false, searchable: false, className: 'text-center', render: function (data, renderType, row) { return renderType === 'display' ? renderEditar('distribuidor', row.id) : ''; }}
        ];
        return [
            { data: null, title: '', orderable: false, searchable: false, render: function () { return '<span class="atlas-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>'; }},
            { data: 'orden', title: 'Orden', render: function (data, renderType) { return renderType === 'display' ? '<span class="atlas-order-pill">' + esc(data || '-') + '</span>' : (parseInt(data, 10) || 999999); }},
            { data: 'nombre', title: 'Clasificación', render: function (data, renderType, row) {
                if (renderType !== 'display') return [data || '', row.descripcion || ''].join(' ');
                const descripcion = String(row.descripcion || '').trim();
                return '<div class="atlas-classif-wrap"><div class="atlas-classif" style="--atlas-class-color:' + esc(colorHexSeguro(row.color_hex)) + ';"><span class="atlas-classif-dot"></span><i class="' + esc(row.icon_font || 'fa-solid fa-tags') + '"></i><span>' + esc(data || 'Sin nombre') + '</span></div>' + (descripcion ? '<small class="text-muted fw-semibold d-block mt-1">' + esc(descripcion) + '</small>' : '<small class="text-muted fw-semibold d-block mt-1">Sin descripción</small>') + '</div>';
            }},
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
        const mapa = { divisiones: '#atlasTablaDivisiones', asigna_divisiones: '#atlasTablaAsignaDivisiones', distribuidores: '#atlasTablaDistribuidores', clasificaciones: '#atlasTablaClasificaciones', sucursales_pendientes: '#atlasTablaSucursalesPendientes' };
        Object.keys(mapa).forEach(tipo => {
            if (!tablasCatalogo[tipo]) tablasCatalogo[tipo] = initDataTable(mapa[tipo], columnasCatalogo(tipo), tipo === 'clasificaciones' ? [[1,'asc']] : [[0,'asc']]);
            const tabla = tablasCatalogo[tipo];
            const datos = tipo === 'asigna_divisiones' ? (catalogos.divisiones || []) : (catalogos[tipo] || []);
            if (tabla) { tabla.clear(); tabla.rows.add(datos); tabla.draw(); }
            atlasSetTablaLoading(tipo, false);
        });
        renderErroresDivisionesCard();
        renderActualizacionesDivisionalesCard();
        setKpi('atlas-kpi-sucursales-pendientes', (catalogos.sucursales_pendientes || []).length);
        setTimeout(initOrdenClasificaciones, 50);
    }
    async function cargarSucursales() {
        atlasSetTablaLoading('sucursales', true, 'Cargando sucursales...');
        try {
            const res = await fetch('/Atlas/getSucursales', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (!data || !data.success) throw new Error((data && (data.mensaje || data.error)) || 'No se pudo cargar.');
            sucursales = Array.isArray(data.datos) ? data.datos : [];
            aplicarConfiguracionCalidad(data.configuracion_calidad || {});
            const t = data.totales || {};
            setKpi('atlas-kpi-total', t.total); setKpi('atlas-kpi-activas', t.activas); setKpi('atlas-kpi-inactivas', t.inactivas); setKpi('atlas-kpi-pendientes-paso2', t.pendientes_paso2 || 0); setKpi('atlas-kpi-coordenadas', t.con_coordenadas);
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
            catalogos = Object.assign({ divisiones: [], divisionales: [], regionales: [], supervisores: [], asesores: [], personas_comercial: [], distribuidores: [], presencias_distribuidores: [], estados_presencia: [], clasificaciones: [], sucursales_pendientes: [] }, data.datos || {});
            llenarSelect('atlas-sucursal-distribuidor', catalogos.distribuidores, 'Selecciona distribuidor');
            llenarSelect('atlas-sucursal-clasificacion', catalogos.clasificaciones, 'Selecciona clasificación');
            actualizarCascadaSucursal(valores);
            Object.keys(valores).forEach(key => setFormValue(formSucursal, key, valores[key]));
            renderCatalogos();
        } catch (err) {
            if (!opts.silencioso) atlasSetCatalogosLoading(false);
            throw err;
        }
    }
    function atlasDelay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    async function cargarVistaInicialAtlas() {
        atlasTablasCargando.add('vista_inicial');
        atlasMostrarLoaderGlobal();
        try {
            await atlasDelay(80);
            await Promise.all([
                cargarCatalogos(),
                cargarSucursales(),
                cargarActualizacionesDivisionalesData(true),
                atlasDelay(650)
            ]);
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'No se pudo cargar Atlas', text: err.message || 'Error' });
            }
        } finally {
            atlasTablasCargando.delete('vista_inicial');
            atlasOcultarLoaderGlobal();
        }
    }
    function abrirSucursal(row) {
        if (!formSucursal) return;
        if (!row && !permisosSucursal.paso1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin permiso',
                    text: 'No tienes permiso para agregar sucursales.'
                });
            }
            return;
        }
        if (row && !permisosSucursal.paso1 && !permisosSucursal.paso2) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin permiso',
                    text: 'No tienes permiso para modificar sucursales.'
                });
            }
            return;
        }
        formSucursal.reset();
        const data = row || {};
        if (!data.id && !data.clasificacion_id) {
            data.clasificacion_id = clasificacionOportunidadDefaultId();
        }
        modalSucursalTitulo.innerHTML = '<i class="fa-solid fa-store me-2"></i>' + (data.id ? 'Editar sucursal' : 'Agregar sucursal');
        ['id','fk_sucursal','sucursal','distribuidor_id','clasificacion_id','direccion_sucursal','calle','numero_exterior','numero_interior','estado','municipio','localidad','colonia','codigo_postal','latitud','longitud','activo','coordenadas'].forEach(key => setFormValue(formSucursal, key, key === 'direccion_sucursal' ? (data.direccion_sucursal || data.direccion || '') : (key === 'activo' && data[key] == null ? 1 : data[key])));
        setFormValue(formSucursal, 'paso_alta', 'paso1');
        actualizarCascadaSucursal(data);
        aplicarPermisosSucursalModal();
        refrescarSelectBuscadores(modalSucursalEl);
        bloquearClasificacionAutomatica();
        actualizarCamposDireccionSucursal();
        actualizarCoincidenciaNombreSucursal();
        mostrarModal(modalSucursalEl);
    }
    function clasificacionConIcono(icon, idActual) {
        const actual = String(idActual || '');
        return (catalogos.clasificaciones || []).find(row => String(row.icon_font || '').trim() === icon && String(row.id || '') !== actual) || null;
    }
    function iconoDisponibleClasificacion(idActual) { return atlasIconosClasificacion.find(icon => !clasificacionConIcono(icon, idActual)) || 'fa-solid fa-tags'; }
    function renderGaleriaIconos(iconoActual, idActual, bloquearUsados) {
        const bloquear = bloquearUsados !== false;
        const actual = String(iconoActual || '').trim() || iconoDisponibleClasificacion(idActual);
        const galeria = '<div class="atlas-icon-gallery" role="group" aria-label="Galería de iconos">' + atlasIconosClasificacion.map(icon => {
            const usada = bloquear ? clasificacionConIcono(icon, idActual) : null;
            const disabled = !!usada;
            const titulo = disabled ? 'Usado por: ' + (usada.nombre || 'Clasificación sin nombre') : icon;
            return '<button type="button" class="atlas-icon-option' + (icon === actual ? ' is-active' : '') + (disabled ? ' is-disabled' : '') + '" data-atlas-icon-option="' + esc(icon) + '"' + (disabled ? ' data-atlas-tooltip="' + esc(titulo) + '"' : '') + ' title="' + esc(titulo) + '" aria-label="' + esc(titulo) + '"' + (disabled ? ' aria-disabled="true" tabindex="0"' : '') + '><i class="' + esc(icon) + '"></i></button>';
        }).join('') + '</div>';
        return '<details class="atlas-icon-picker">'
            + '<summary><span class="atlas-icon-picker-summary"><span class="atlas-icon-picker-current" data-atlas-icon-current><i class="' + esc(actual) + '"></i></span><span>Icono seleccionado</span></span><span class="atlas-icon-picker-action">Cambiar icono</span></summary>'
            + '<div class="atlas-icon-picker-panel">' + galeria + '</div>'
            + '</details>';
    }

    function normalizarPresenciaTexto(v) {
        return String(v || '').trim().replace(/\s+/g, ' ').toUpperCase();
    }

    function valoresMultiplesDistribuidor(valor) {
        if (Array.isArray(valor)) return valor.map(v => String(v || '').trim()).filter(Boolean);
        return String(valor || '').split(/[|,]/).map(v => v.trim()).filter(Boolean);
    }

    function opcionesDistribuidorCatalogo(nombre) {
        const key = nombre === 'tipo_motos' ? 'distribuidor_tipo_motos' : 'distribuidor_canales_venta';
        const fallback = nombre === 'tipo_motos'
            ? ['Nuevas', 'Usadas', 'Adjudicadas', 'Seminuevas']
            : ['Piso', 'Digital', 'Financiamiento', 'Marketplace', 'Convenio', 'Referido'];
        const rows = Array.isArray(catalogos[key]) ? catalogos[key] : [];
        const opciones = rows.map(row => String(row.nombre || '').trim()).filter(Boolean);
        return opciones.length ? opciones : fallback;
    }

    function renderCheckboxOpcionesDistribuidor(nombre, opciones, seleccionados) {
        const set = new Set(valoresMultiplesDistribuidor(seleccionados).map(v => v.toLowerCase()));
        return opciones.map(op => {
            const checked = set.has(String(op).toLowerCase()) ? ' checked' : '';
            return '<label class="atlas-check-option"><input type="checkbox" value="' + esc(op) + '"' + checked + '> <span>' + esc(op) + '</span></label>';
        }).join('');
    }

    function renderCheckboxGrupoDistribuidor(nombre, seleccionados) {
        const opciones = opcionesDistribuidorCatalogo(nombre);
        const hiddenValue = valoresMultiplesDistribuidor(seleccionados).join('|');
        return '<input type="hidden" name="' + esc(nombre) + '" data-atlas-check-hidden="' + esc(nombre) + '" value="' + esc(hiddenValue) + '">'
            + '<div class="atlas-checkbox-grid" data-atlas-check-group="' + esc(nombre) + '">'
            + renderCheckboxOpcionesDistribuidor(nombre, opciones, seleccionados)
            + '</div>';
    }

    function refrescarCheckboxGrupoDistribuidor(nombre) {
        const wrap = document.querySelector('[data-atlas-check-group="' + nombre + '"]');
        const hidden = document.querySelector('[data-atlas-check-hidden="' + nombre + '"]');
        if (!wrap || !hidden) return;
        wrap.innerHTML = renderCheckboxOpcionesDistribuidor(nombre, opcionesDistribuidorCatalogo(nombre), hidden.value || '');
    }

    function syncCheckboxGrupoDistribuidor(nombre) {
        const wrap = document.querySelector('[data-atlas-check-group="' + nombre + '"]');
        const hidden = document.querySelector('[data-atlas-check-hidden="' + nombre + '"]');
        if (!wrap || !hidden) return;
        hidden.value = Array.from(wrap.querySelectorAll('input[type="checkbox"]:checked')).map(input => input.value).join('|');
    }

    function tiempoEstadiaPartes(valor) {
        const raw = String(valor || '').trim().toLowerCase();
        const m = raw.match(/^(\d+)\s*(minutos?|mins?|horas?|hrs?|d[ií]as?)?/i);
        if (!m) return { cantidad: '', unidad: 'minutos' };
        let unidad = String(m[2] || 'minutos').toLowerCase();
        if (unidad.startsWith('h')) unidad = 'horas';
        else if (unidad.startsWith('d')) unidad = 'dias';
        else unidad = 'minutos';
        return { cantidad: m[1] || '', unidad: unidad };
    }

    function syncTiempoEstadiaDistribuidor() {
        if (!formCatalogo) return;
        const cantidadEl = formCatalogo.querySelector('[data-atlas-tiempo-estadia-cantidad]');
        const unidadEl = formCatalogo.querySelector('[data-atlas-tiempo-estadia-unidad]');
        const hidden = formCatalogo.querySelector('input[name="tiempo_promedio_entrega"]');
        if (!cantidadEl || !unidadEl || !hidden) return;
        const cantidad = String(cantidadEl.value || '').replace(/\D+/g, '').slice(0, 4);
        cantidadEl.value = cantidad;
        hidden.value = cantidad ? (cantidad + ' ' + unidadEl.value) : '';
    }

    async function agregarOpcionCatalogoDistribuidor(tipo) {
        const label = tipo === 'tipo_motos' ? 'tipo de moto' : 'canal de venta';
        if (typeof Swal === 'undefined') return;
        const res = await Swal.fire({
            title: 'Agregar ' + label,
            input: 'text',
            inputPlaceholder: 'Nombre',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            inputValidator: value => {
                const v = String(value || '').trim();
                if (!v) return 'Captura el nombre.';
                if (v.length > 120) return 'Máximo 120 caracteres.';
                return undefined;
            }
        });
        if (!res.isConfirmed) return;
        try {
            const data = await guardarJson('/Atlas/guardarCatalogoDistribuidorOpcion', { tipo: tipo, nombre: res.value });
            const key = tipo === 'tipo_motos' ? 'distribuidor_tipo_motos' : 'distribuidor_canales_venta';
            catalogos[key] = Array.isArray(catalogos[key]) ? catalogos[key] : [];
            const nombre = String((data.dato && data.dato.nombre) || res.value || '').trim();
            if (nombre && !catalogos[key].some(row => String(row.nombre || '').toLowerCase() === nombre.toLowerCase())) {
                catalogos[key].push({ id: data.dato && data.dato.id ? data.dato.id : '', nombre: nombre, activo: 1 });
                catalogos[key].sort((a, b) => String(a.nombre || '').localeCompare(String(b.nombre || ''), 'es'));
            }
            refrescarCheckboxGrupoDistribuidor(tipo);
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: err.message || 'Error' });
        }
    }

    function setEstatusDistribuidorModal(estatus) {
        const input = formCatalogo ? formCatalogo.querySelector('input[name="estatus"]') : null;
        if (input) input.value = estatus || 'activo';
        document.querySelectorAll('[data-atlas-dist-status]').forEach(btn => btn.classList.toggle('is-active', btn.getAttribute('data-atlas-dist-status') === (estatus || 'activo')));
        actualizarVisibilidadBloqueoDistribuidor();
    }

    function actualizarVisibilidadBloqueoDistribuidor() {
        const estatusInput = formCatalogo ? formCatalogo.querySelector('input[name="estatus"]') : null;
        const estatus = estatusInput ? String(estatusInput.value || 'activo') : 'activo';
        const wrap = document.getElementById('atlas-dist-bloqueo-wrap');
        const fechaWrap = document.getElementById('atlas-dist-bloqueo-fecha-wrap');
        const vigencia = formCatalogo && formCatalogo.elements.bloqueo_vigencia ? String(formCatalogo.elements.bloqueo_vigencia.value || 'indefinida') : 'indefinida';
        if (wrap) wrap.classList.toggle('atlas-presencia-hidden', estatus === 'activo');
        if (fechaWrap) fechaWrap.classList.toggle('atlas-presencia-hidden', estatus === 'activo' || vigencia !== 'definida');
    }

    function syncPresenciasDistribuidorInput() {
        const input = document.getElementById('atlas-distribuidor-presencias-json');
        if (input) input.value = JSON.stringify(atlasDistribuidorPresencias);
    }

    function renderPresenciasDistribuidorEditor() {
        const list = document.getElementById('atlas-distribuidor-presencias-list');
        if (!list) return;
        if (!atlasDistribuidorPresencias.length) {
            list.innerHTML = '<div class="atlas-presencia-empty">Agrega al menos un estado y municipio cuando exista presencia física.</div>';
        } else {
            list.innerHTML = atlasDistribuidorPresencias.map((item, idx) => {
                return '<span class="atlas-presencia-chip"><i class="fa-solid fa-location-dot"></i><span>' + esc(item.municipio) + ', ' + esc(item.estado) + '</span><button type="button" data-atlas-remover-presencia="' + idx + '" title="Quitar presencia" aria-label="Quitar presencia"><i class="fa-solid fa-xmark"></i></button></span>';
            }).join('');
        }
        syncPresenciasDistribuidorInput();
    }

    function valorPresenciaFisicaActual() {
        const presencia = formCatalogo && formCatalogo.elements.presencia_fisica ? formCatalogo.elements.presencia_fisica : null;
        if (!presencia) return '1';
        return String(presencia.value || '1');
    }

    function actualizarVisibilidadPresenciaDistribuidor() {
        const presencia = formCatalogo && formCatalogo.elements.presencia_fisica ? formCatalogo.elements.presencia_fisica : null;
        const wrap = document.getElementById('atlas-presencia-editor-wrap');
        if (!wrap || !presencia) return;
        const visible = valorPresenciaFisicaActual() === '1';
        wrap.classList.toggle('atlas-presencia-hidden', !visible);
        wrap.style.display = visible ? '' : 'none';
        wrap.hidden = !visible;
        if (!visible) {
            atlasDistribuidorPresencias = [];
            renderPresenciasDistribuidorEditor();
            limpiarMunicipiosPresencia();
            syncPresenciasDistribuidorInput();
        }
    }

    function textoOptionSeleccionada(el) {
        if (!el || !el.options || el.selectedIndex < 0) return '';
        return String(el.options[el.selectedIndex].text || '').trim();
    }

    function limpiarMunicipiosPresencia() {
        const municipioEl = document.getElementById('atlas-presencia-municipio');
        if (!municipioEl) return;
        municipioEl.innerHTML = '<option value="">Primero selecciona estado</option>';
        municipioEl.disabled = true;
        inicializarSelectBuscador(municipioEl);
    }

    async function cargarMunicipiosPresencia(estadoId) {
        const municipioEl = document.getElementById('atlas-presencia-municipio');
        if (!municipioEl) return;
        limpiarMunicipiosPresencia();
        if (!estadoId) return;
        municipioEl.innerHTML = '<option value="">Cargando municipios...</option>';
        municipioEl.disabled = true;
        inicializarSelectBuscador(municipioEl);
        try {
            const res = await fetch('/Atlas/getMunicipiosPresencia?estado_id=' + encodeURIComponent(estadoId), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (!data || data.success === false) throw new Error((data && (data.mensaje || data.error)) || 'No se pudieron cargar municipios.');
            const rows = Array.isArray(data.datos) ? data.datos : [];
            const disponibles = rows.filter(row => {
                const municipioId = String(row.id || '');
                const municipio = normalizarPresenciaTexto(row.nombre || '');
                return !atlasDistribuidorPresencias.some(item => (
                    String(item.estado_id || '') === String(estadoId || '') &&
                    (String(item.municipio_id || '') === municipioId || normalizarPresenciaTexto(item.municipio || '') === municipio)
                ));
            });
            municipioEl.innerHTML = '<option value="">' + (disponibles.length ? 'Selecciona municipio' : 'Todos los municipios de este estado ya fueron agregados') + '</option>' + disponibles.map(row => '<option value="' + esc(row.id || '') + '">' + esc(row.nombre || '') + '</option>').join('');
            municipioEl.disabled = disponibles.length === 0;
            inicializarSelectBuscador(municipioEl);
        } catch (err) {
            municipioEl.innerHTML = '<option value="">No se pudieron cargar municipios</option>';
            municipioEl.disabled = true;
            inicializarSelectBuscador(municipioEl);
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudieron cargar municipios', text: err.message || 'Error' });
        }
    }
    function enlazarEstadoPresenciaDistribuidor() {
        const estadoEl = document.getElementById('atlas-presencia-estado');
        if (!estadoEl) return;
        const cargarDesdeSelect = function (valor) {
            const estadoId = String(valor || estadoEl.value || '').trim();
            cargarMunicipiosPresencia(estadoId);
        };
        estadoEl.onchange = function () { cargarDesdeSelect(estadoEl.value); };
        if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            jQuery(estadoEl)
                .off('change.atlasPresenciaEstadoLocal select2:select.atlasPresenciaEstadoLocal')
                .on('change.atlasPresenciaEstadoLocal select2:select.atlasPresenciaEstadoLocal', function (ev) {
                    const id = ev && ev.params && ev.params.data ? ev.params.data.id : this.value;
                    cargarDesdeSelect(id);
                });
        }
    }

    function cargarPresenciasDistribuidor(row) {
        atlasDistribuidorPresencias = presenciasDistribuidor(row || {}).map(item => ({
            estado_id: String(item.estado_id || ''),
            municipio_id: String(item.municipio_id || ''),
            estado: normalizarPresenciaTexto(item.estado),
            municipio: normalizarPresenciaTexto(item.municipio)
        })).filter(item => item.estado && item.municipio);
        renderPresenciasDistribuidorEditor();
        limpiarMunicipiosPresencia();
    }

    function agregarPresenciaDistribuidorDesdeModal() {
        const estadoEl = document.getElementById('atlas-presencia-estado');
        const municipioEl = document.getElementById('atlas-presencia-municipio');
        const estadoId = estadoEl ? String(estadoEl.value || '') : '';
        const municipioId = municipioEl ? String(municipioEl.value || '') : '';
        const estado = normalizarPresenciaTexto(textoOptionSeleccionada(estadoEl));
        const municipio = normalizarPresenciaTexto(textoOptionSeleccionada(municipioEl));
        if (!estadoId || !municipioId || !estado || !municipio) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'Completa la presencia', text: 'Selecciona estado y municipio.' });
            return;
        }
        const existe = atlasDistribuidorPresencias.some(item => item.estado === estado && item.municipio === municipio);
        if (!existe) atlasDistribuidorPresencias.push({ estado_id: estadoId, municipio_id: municipioId, estado: estado, municipio: municipio });
        const presencia = formCatalogo && formCatalogo.elements.presencia_fisica ? formCatalogo.elements.presencia_fisica : null;
        if (presencia) {
            presencia.value = '1';
            if (window.jQuery && jQuery.fn && jQuery.fn.select2 && jQuery(presencia).hasClass('select2-hidden-accessible')) {
                jQuery(presencia).trigger('change.select2');
            }
        }
        if (estadoEl) estadoEl.value = '';
        limpiarMunicipiosPresencia();
        if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            if (estadoEl) jQuery(estadoEl).trigger('change.select2');
            if (municipioEl) jQuery(municipioEl).trigger('change.select2');
        }
        renderPresenciasDistribuidorEditor();
        actualizarVisibilidadPresenciaDistribuidor();
        if (estadoEl) estadoEl.focus();
    }

    function camposCatalogo(tipo, data) {
        const row = data || {};
        if (tipo === 'division') {
            const colorDivision = colorHexSeguro(row.color_hex || '#2563EB');
            const iconoDivision = String(row.icon_font || 'fa-solid fa-diagram-project').trim();
            return '<div><label class="form-label atlas-required">Nombre</label><input type="text" class="form-control" name="nombre" required placeholder="Nombre de la división" value="' + esc(row.nombre || '') + '"></div><div class="atlas-field-wide"><label class="form-label atlas-required">Icono</label><input type="hidden" name="icon_font" id="atlas-catalogo-icon-font" required value="' + esc(iconoDivision) + '">' + renderGaleriaIconos(iconoDivision, '', false) + '</div><div><label class="form-label atlas-required">Color</label><div class="atlas-color-input-wrap"><input type="color" name="color_hex" id="atlas-catalogo-color" required value="' + esc(colorDivision) + '"><span class="atlas-muted" id="atlas-catalogo-color-label">' + esc(colorDivision) + '</span></div></div><div><label class="form-label atlas-required">Estatus</label><select class="form-select js-atlas-select-buscador" name="activo" required><option value="1"' + (Number(row.activo ?? 1) === 1 ? ' selected' : '') + '>Activa</option><option value="0"' + (Number(row.activo ?? 1) === 0 ? ' selected' : '') + '>Inactiva</option></select></div>';
        }
        if (tipo === 'asigna_division') {
            const divisionId = row.division_id || row.id || '';
            const divisionales = divisionalesDisponiblesDivision(divisionId);
            const tipoAsignacion = String(row.tipo_asignacion || (row.divisional_persona_id ? 'persona' : (row.divisional_id ? 'vacante' : 'persona'))).toLowerCase();
            const nombreVacante = row.nombre_vacante || (tipoAsignacion === 'vacante' ? row.divisional_nombre : '');
            return '<div><label class="form-label atlas-required">División</label><select class="form-select js-atlas-select-buscador" name="division_id" required>' + optionsCatalogo(catalogos.divisiones || [], divisionId, 'Selecciona división') + '</select></div>'
                + '<div><label class="form-label atlas-required">Tipo de asignación</label><select class="form-select js-atlas-select-buscador" name="tipo_asignacion" required><option value="persona"' + (tipoAsignacion !== 'vacante' ? ' selected' : '') + '>Colaborador</option><option value="vacante"' + (tipoAsignacion === 'vacante' ? ' selected' : '') + '>Vacante</option></select></div>'
                + '<div data-atlas-asigna-persona><label class="form-label atlas-required">Colaborador responsable</label><div class="d-flex gap-2 align-items-start"><select class="form-select js-atlas-select-buscador" name="divisional_id">' + optionsCatalogo(divisionales, row.divisional_id, 'Selecciona colaborador') + '</select><button type="button" class="btn btn-outline-primary btn-action-size flex-shrink-0" data-atlas-agregar-divisional-desde-asignacion title="Agregar divisional activo" aria-label="Agregar divisional activo"><i class="fa-solid fa-plus"></i></button></div><span class="atlas-cascade-help">Solo aparecen colaboradores operativos sin otra división activa asignada.</span></div>'
                + '<div data-atlas-asigna-vacante><label class="form-label atlas-required">Nombre de la vacante</label><input type="text" class="form-control" name="nombre_vacante" maxlength="160" placeholder="Ej. VACANTE NORTE" value="' + esc(nombreVacante || '') + '"><span class="atlas-cascade-help">Se guarda como posición vacante, no como persona.</span></div>';
        }
        if (tipo === 'distribuidor') {
            const iconoDistribuidor = String(row.icon_font || 'fa-solid fa-building').trim();
            const tipoPersona = String(row.tipo_persona || 'moral');
            const presencia = Number(row.presencia_fisica ?? 1) === 1;
            const estatus = String(row.estatus || (Number(row.activo ?? 1) === 1 ? 'activo' : 'inactivo')).toLowerCase();
            const reqCita = Number(row.requiere_cita ?? 0) === 1;
            const bloqueoVigencia = String(row.bloqueo_vigencia || 'indefinida').toLowerCase();
            const tiempoEstadia = tiempoEstadiaPartes(row.tiempo_promedio_entrega || '');
            return '<div class="atlas-field-wide atlas-dist-step-layout">'
                + '<div class="d-flex justify-content-between flex-column mb-4 mb-md-0">'
                + '<h5 class="atlas-dist-step-title">Datos del distribuidor</h5>'
                + '<ul class="nav nav-align-left nav-pills flex-column atlas-dist-step-nav" role="tablist">'
                + '<li class="nav-item mb-1"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#atlas-dist-tab-principal"><i class="fa-solid fa-id-card"></i><span class="align-middle">Principal</span><span class="atlas-tab-required-mark" title="Tiene campos obligatorios"></span></button></li>'
                + '<li class="nav-item mb-1"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#atlas-dist-tab-deposito"><i class="fa-solid fa-building-columns"></i><span class="align-middle">Depósito</span></button></li>'
                + '<li class="nav-item mb-1"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#atlas-dist-tab-presencia"><i class="fa-solid fa-location-dot"></i><span class="align-middle">Presencia</span><span class="atlas-tab-required-mark" title="Tiene campos obligatorios"></span></button></li>'
                + '<li class="nav-item mb-1"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#atlas-dist-tab-fiscal"><i class="fa-solid fa-file-invoice"></i><span class="align-middle">Fiscal</span><span class="atlas-tab-required-mark" title="Tiene campos obligatorios"></span></button></li>'
                + '<li class="nav-item mb-1"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#atlas-dist-tab-comercial"><i class="fa-solid fa-handshake"></i><span class="align-middle">Comercial</span><span class="atlas-tab-required-mark" title="Tiene campos obligatorios"></span></button></li>'
                + '<li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#atlas-dist-tab-operativo"><i class="fa-solid fa-clock"></i><span class="align-middle">Operativo</span></button></li>'
                + '</ul>'
                + '</div>'
                + '<div class="tab-content p-0">'
                + '<div class="tab-pane fade show active" id="atlas-dist-tab-principal"><div class="atlas-form-grid">'
                + '<div><label class="form-label atlas-required">Nombre comercial</label><input type="text" class="form-control" name="nombre_comercial" required maxlength="180" placeholder="Nombre comercial" value="' + esc(row.nombre_comercial || row.nombre || '') + '"></div>'
                + '<div><label class="form-label atlas-required">Razón social</label><input type="text" class="form-control" name="razon_social" required maxlength="220" placeholder="Razón social" value="' + esc(row.razon_social || row.nombre || '') + '"></div>'
                + '<div><label class="form-label atlas-required">RFC</label><input type="text" class="form-control text-uppercase" name="rfc" required maxlength="13" pattern="[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}" title="RFC válido, sin guiones ni espacios" placeholder="RFC" value="' + esc(row.rfc || '') + '"></div>'
                + '<div><label class="form-label atlas-required">Tipo distribuidor</label><select class="form-select js-atlas-select-buscador" name="tipo_distribuidor" required>' + opcionesValores(['Agencia','Subdistribuidor','Mayorista','Independiente'], row.tipo_distribuidor, 'Selecciona tipo') + '</select></div>'
                + '<div><label class="form-label atlas-required">Tipo de persona</label><select class="form-select js-atlas-select-buscador" name="tipo_persona" required><option value="moral"' + (tipoPersona !== 'fisica' ? ' selected' : '') + '>Persona moral</option><option value="fisica"' + (tipoPersona === 'fisica' ? ' selected' : '') + '>Persona física</option></select></div>'
                + '<div class="atlas-field-wide"><label class="form-label atlas-required">Estatus operativo</label><input type="hidden" name="estatus" value="' + esc(estatus || 'activo') + '"><div class="atlas-status-buttons"><button type="button" class="atlas-status-btn' + (estatus === 'activo' ? ' is-active' : '') + '" data-atlas-dist-status="activo"><i class="fa-solid fa-circle-check"></i>Activo</button><button type="button" class="atlas-status-btn' + (estatus === 'bloqueado' ? ' is-active' : '') + '" data-atlas-dist-status="bloqueado"><i class="fa-solid fa-ban"></i>Bloquear</button><button type="button" class="atlas-status-btn' + (estatus === 'pausado' ? ' is-active' : '') + '" data-atlas-dist-status="pausado"><i class="fa-solid fa-pause"></i>Pausar</button><button type="button" class="atlas-status-btn' + (estatus === 'inhabilitado' ? ' is-active' : '') + '" data-atlas-dist-status="inhabilitado"><i class="fa-solid fa-lock"></i>Inhabilitar</button></div></div>'
                + '<div class="atlas-field-wide' + (estatus === 'activo' ? ' atlas-presencia-hidden' : '') + '" id="atlas-dist-bloqueo-wrap"><div class="atlas-form-grid"><div><label class="form-label">Vigencia</label><select class="form-select js-atlas-select-buscador" name="bloqueo_vigencia"><option value="indefinida"' + (bloqueoVigencia !== 'definida' ? ' selected' : '') + '>Indefinida</option><option value="definida"' + (bloqueoVigencia === 'definida' ? ' selected' : '') + '>Definida</option></select></div><div id="atlas-dist-bloqueo-fecha-wrap"' + (bloqueoVigencia === 'definida' && estatus !== 'activo' ? '' : ' class="atlas-presencia-hidden"') + '><label class="form-label">Fecha y hora de fin</label><input type="datetime-local" class="form-control" name="bloqueo_fin_at" value="' + esc(row.bloqueo_fin_at || '') + '"></div><div class="atlas-field-wide"><label class="form-label">Motivo</label><input type="text" class="form-control" name="motivo_bloqueo" maxlength="250" placeholder="Motivo para bloquear, pausar o inhabilitar" value="' + esc(row.motivo_bloqueo || '') + '"></div></div></div>'
                + '<div><label class="form-label atlas-required">Contacto principal</label><input type="text" class="form-control" name="nombre_contacto" required maxlength="180" placeholder="Nombre contacto" value="' + esc(row.nombre_contacto || '') + '"></div>'
                + '<div><label class="form-label atlas-required">Teléfono principal</label><input type="tel" class="form-control" name="telefono_contacto" required inputmode="numeric" maxlength="10" pattern="[0-9]{10}" title="Teléfono a 10 dígitos" placeholder="10 dígitos" value="' + esc(row.telefono_contacto || '') + '"></div>'
                + '<div><label class="form-label">Teléfono alterno</label><input type="tel" class="form-control" name="telefono_secundario" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" title="Teléfono a 10 dígitos" placeholder="10 dígitos" value="' + esc(row.telefono_secundario || '') + '"></div>'
                + '<div><label class="form-label atlas-required">Correo principal</label><input type="email" class="form-control" name="email_contacto" required maxlength="180" title="Correo válido" placeholder="correo@dominio.com" value="' + esc(row.email_contacto || '') + '"></div>'
                + '<div class="atlas-field-wide"><label class="form-label atlas-required">Icono</label><input type="hidden" name="icon_font" id="atlas-catalogo-icon-font" required value="' + esc(iconoDistribuidor) + '">' + renderGaleriaIconos(iconoDistribuidor, '', false) + '</div>'
                + '</div></div>'
                + '<div class="tab-pane fade" id="atlas-dist-tab-deposito"><div class="atlas-form-grid">'
                + '<div><label class="form-label">Banco</label><input type="text" class="form-control" name="banco_deposito" maxlength="120" placeholder="Banco receptor" value="' + esc(row.banco_deposito || '') + '"></div>'
                + '<div><label class="form-label">Titular de la cuenta</label><input type="text" class="form-control" name="titular_deposito" maxlength="180" placeholder="Titular de la cuenta" value="' + esc(row.titular_deposito || '') + '"></div>'
                + '<div><label class="form-label">Cuenta</label><input type="text" class="form-control" name="cuenta_deposito" inputmode="numeric" maxlength="20" pattern="[0-9]{6,20}" title="Cuenta de 6 a 20 dígitos" placeholder="Cuenta bancaria" value="' + esc(row.cuenta_deposito || '') + '"></div>'
                + '<div><label class="form-label">CLABE</label><input type="text" class="form-control" name="clabe_deposito" inputmode="numeric" maxlength="18" pattern="[0-9]{18}" title="CLABE a 18 dígitos" placeholder="18 dígitos" value="' + esc(row.clabe_deposito || '') + '"></div>'
                + '<div class="atlas-field-wide"><label class="form-label">Estado de cuenta</label>' + (row.__SPARTA_SECRET_REDACTED___url ? '<div class="mb-2">' + renderEstadoCuentaDistribuidor(row) + '</div>' : '') + '<input type="file" class="form-control" name="__SPARTA_SECRET_REDACTED__" accept=".pdf,.jpg,.jpeg,.png"><span class="atlas-cascade-help">PDF, JPG o PNG. Máximo 10 MB.</span></div>'
                + '</div></div>'
                + '<div class="tab-pane fade" id="atlas-dist-tab-presencia"><div class="atlas-form-grid">'
                + '<div><label class="form-label atlas-required">Presencia física</label><select class="form-select js-atlas-select-buscador" name="presencia_fisica" required><option value="1"' + (presencia ? ' selected' : '') + '>Sí tiene presencia física</option><option value="0"' + (!presencia ? ' selected' : '') + '>Sin presencia física</option></select></div>'
                + '<div class="atlas-field-wide atlas-presencia-editor' + (!presencia ? ' atlas-presencia-hidden' : '') + '" id="atlas-presencia-editor-wrap"' + (!presencia ? ' hidden style="display:none;"' : '') + '><input type="hidden" name="presencias_json" id="atlas-distribuidor-presencias-json"><label class="form-label atlas-required">Estados y municipios con presencia</label><div class="atlas-presencia-inputs"><div><label class="form-label">Estado</label><select class="form-select js-atlas-select-buscador" id="atlas-presencia-estado">' + optionsCatalogo(catalogos.estados_presencia || [], '', 'Selecciona estado') + '</select></div><div><label class="form-label">Municipio</label><select class="form-select js-atlas-select-buscador" id="atlas-presencia-municipio" disabled><option value="">Primero selecciona estado</option></select></div><button type="button" class="btn btn-primary btn-action-size" id="atlas-btn-agregar-presencia"><i class="fa-solid fa-plus"></i>Agregar</button></div><div class="atlas-presencia-list" id="atlas-distribuidor-presencias-list"></div><span class="atlas-cascade-help">Puedes agregar todos los municipios donde el distribuidor tenga presencia física.</span></div>'
                + '</div></div>'
                + '<div class="tab-pane fade" id="atlas-dist-tab-fiscal"><div class="atlas-form-grid">'
                + '<div class="atlas-field-wide"><label class="form-label atlas-required">Régimen fiscal</label><input type="text" class="form-control" name="regimen_fiscal" required maxlength="180" placeholder="Régimen fiscal SAT" value="' + esc(row.regimen_fiscal || '') + '"></div>'
                + '<div class="atlas-field-wide"><label class="form-label">Constancia de situación fiscal</label>' + (row.constancia_fiscal_url ? '<div class="mb-2">' + renderConstanciaFiscal(row) + '</div>' : '') + '<input type="file" class="form-control" name="constancia_fiscal" accept=".pdf,.jpg,.jpeg,.png"><span class="atlas-cascade-help">PDF, JPG o PNG. Máximo 10 MB.</span></div>'
                + '</div></div>'
                + '<div class="tab-pane fade" id="atlas-dist-tab-comercial"><div class="atlas-form-grid">'
                + '<div class="atlas-field-wide"><div class="atlas-check-head"><label class="form-label atlas-required">Tipo de motos</label><button type="button" class="atlas-check-add" data-atlas-dist-add-option="tipo_motos" title="Agregar tipo de moto" aria-label="Agregar tipo de moto"><i class="fa-solid fa-plus"></i></button></div>' + renderCheckboxGrupoDistribuidor('tipo_motos', row.tipo_motos || '') + '</div>'
                + '<div class="atlas-field-wide"><div class="atlas-check-head"><label class="form-label atlas-required">Canal venta</label><button type="button" class="atlas-check-add" data-atlas-dist-add-option="canal_venta" title="Agregar canal de venta" aria-label="Agregar canal de venta"><i class="fa-solid fa-plus"></i></button></div>' + renderCheckboxGrupoDistribuidor('canal_venta', row.canal_venta || '') + '</div>'
                + '</div></div>'
                + '<div class="tab-pane fade" id="atlas-dist-tab-operativo"><div class="atlas-form-grid">'
                + '<div><label class="form-label">Horario atención</label><input type="text" class="form-control" name="horario_atencion" maxlength="180" placeholder="Lun-Vie 9:00 a 18:00" value="' + esc(row.horario_atencion || '') + '"></div>'
                + '<div><label class="form-label">Días operación</label><input type="text" class="form-control" name="dias_operacion" maxlength="120" placeholder="Lunes a sábado" value="' + esc(row.dias_operacion || '') + '"></div>'
                + '<div><label class="form-label">Requiere cita</label><select class="form-select js-atlas-select-buscador" name="requiere_cita"><option value="0"' + (!reqCita ? ' selected' : '') + '>No</option><option value="1"' + (reqCita ? ' selected' : '') + '>Sí</option></select></div>'
                + '<div><label class="form-label">Tiempo promedio de permiso para estadía en sucursal</label><input type="hidden" name="tiempo_promedio_entrega" value="' + esc(row.tiempo_promedio_entrega || '') + '"><div class="atlas-time-row"><input type="text" class="form-control" data-atlas-tiempo-estadia-cantidad inputmode="numeric" maxlength="4" pattern="[0-9]{1,4}" placeholder="45 minutos" value="' + esc(tiempoEstadia.cantidad) + '"><select class="form-select js-atlas-select-buscador" data-atlas-tiempo-estadia-unidad><option value="minutos"' + (tiempoEstadia.unidad === 'minutos' ? ' selected' : '') + '>Minutos</option><option value="horas"' + (tiempoEstadia.unidad === 'horas' ? ' selected' : '') + '>Horas</option><option value="dias"' + (tiempoEstadia.unidad === 'dias' ? ' selected' : '') + '>Días</option></select></div><span class="atlas-cascade-help">Ejemplo: 45 minutos.</span></div>'
                + '<div class="atlas-field-wide"><label class="form-label">Observaciones</label><textarea class="form-control" name="observaciones" maxlength="1000" rows="2" placeholder="Comentarios internos">' + esc(row.observaciones || '') + '</textarea></div>'
                + '</div></div>'
                + '</div></div>';
        }
        const colorActual = colorHexSeguro(row.color_hex || '#94A3B8');
        const idActual = row.id || '';
        const iconoActual = String(row.icon_font || iconoDisponibleClasificacion(idActual)).trim();
        return '<div><label class="form-label atlas-required">Nombre</label><input type="text" class="form-control" name="nombre" required maxlength="60" placeholder="Nombre de la clasificación" value="' + esc(row.nombre || '') + '"></div><div class="atlas-field-wide"><label class="form-label atlas-required">Descripción</label><textarea class="form-control" name="descripcion" required maxlength="500" rows="3" placeholder="Resumen operativo para la app">' + esc(row.descripcion || '') + '</textarea><span class="atlas-cascade-help">Resumen breve para orientar al gestor en la app.</span></div><div class="atlas-field-wide"><label class="form-label atlas-required">Icono</label><input type="hidden" name="icon_font" id="atlas-catalogo-icon-font" required value="' + esc(iconoActual) + '">' + renderGaleriaIconos(iconoActual, idActual) + '</div><div><label class="form-label atlas-required">Color</label><div class="atlas-color-input-wrap"><input type="color" name="color_hex" id="atlas-catalogo-color" required value="' + esc(colorActual) + '"><span class="atlas-muted" id="atlas-catalogo-color-label">' + esc(colorActual) + '</span></div></div><div><label class="form-label atlas-required">Estatus</label><select class="form-select js-atlas-select-buscador" name="activo" required><option value="1"' + (Number(row.activo ?? 1) === 1 ? ' selected' : '') + '>Activa</option><option value="0"' + (Number(row.activo ?? 1) === 0 ? ' selected' : '') + '>Inactiva</option></select></div>';
    }
    function abrirCatalogo(tipo, row, opciones) {
        formCatalogo.reset();
        const opts = opciones || {};
        atlasQuickAddContext = opts.quickAdd ? { tipo: tipo, target: opts.target || '', snapshot: Object.assign({}, opts.snapshot || {}), guardado: false } : null;
        const data = Object.assign({}, row || {}, opts.prefill || {});
        const titulos = { division: ['fa-solid fa-diagram-project','división'], asigna_division: ['fa-solid fa-user-check','asignación de división'], distribuidor: ['fa-solid fa-building','distribuidor'], clasificacion: ['fa-solid fa-tags','clasificación'] };
        const cfg = titulos[tipo] || titulos.clasificacion;
        modalCatalogoEl.classList.toggle('atlas-modal-distribuidor', tipo === 'distribuidor');
        document.getElementById('atlas-catalogo-id').value = data.id || '';
        document.getElementById('atlas-catalogo-tipo').value = tipo;
        modalCatalogoTitulo.innerHTML = '<i class="' + cfg[0] + ' me-2"></i>' + (data.id ? 'Editar ' : 'Agregar ') + cfg[1];
        catalogoFields.innerHTML = camposCatalogo(tipo, data);
        refrescarSelectBuscadores(modalCatalogoEl);
        if (tipo === 'distribuidor') {
            cargarPresenciasDistribuidor(data);
            enlazarEstadoPresenciaDistribuidor();
            actualizarVisibilidadPresenciaDistribuidor();
            setTimeout(actualizarVisibilidadPresenciaDistribuidor, 0);
            setTimeout(actualizarVisibilidadPresenciaDistribuidor, 120);
            syncCheckboxGrupoDistribuidor('tipo_motos');
            syncCheckboxGrupoDistribuidor('canal_venta');
            setEstatusDistribuidorModal(data.estatus || (Number(data.activo ?? 1) === 1 ? 'activo' : 'inactivo'));
        }
        if (tipo === 'asigna_division') {
            actualizarTipoAsignacionDivision();
        }
        mostrarModal(modalCatalogoEl);
    }
    function buscarPorTipo(tipo, id) {
        const key = (tipo === 'division' || tipo === 'asigna_division') ? 'divisiones' : (tipo === 'distribuidor' ? 'distribuidores' : 'clasificaciones');
        return (catalogos[key] || []).find(row => String(row.id || '') === String(id || '')) || null;
    }
    function validarNombreDivisionUnico(payload) {
        const nombre = atlasClaveCalidad(payload && payload.nombre ? payload.nombre : '');
        const idActual = String(payload && payload.id ? payload.id : '');
        if (!nombre) throw new Error('Captura el nombre de la división.');
        const duplicada = (catalogos.divisiones || []).find(row => (
            String(row.id || '') !== idActual &&
            atlasClaveCalidad(row.nombre || '') === nombre
        ));
        if (duplicada) {
            throw new Error('Ya existe una división con ese nombre: ' + (duplicada.nombre || 'sin nombre') + '.');
        }
    }
    function actualizarDivisionalesAsignacion() {
        if (!formCatalogo || getFormValue(formCatalogo, 'tipo') !== 'asigna_division') return;
        const divisionId = getFormValue(formCatalogo, 'division_id');
        const select = formCatalogo.querySelector('select[name="divisional_id"]');
        if (!select) return;
        const actual = select.value || '';
        destruirSelectBuscador(select);
        select.innerHTML = optionsCatalogo(divisionalesDisponiblesDivision(divisionId), actual, 'Selecciona divisional');
        inicializarSelectBuscador(select);
    }
    function actualizarTipoAsignacionDivision() {
        if (!formCatalogo || getFormValue(formCatalogo, 'tipo') !== 'asigna_division') return;
        const tipo = String(getFormValue(formCatalogo, 'tipo_asignacion') || 'persona');
        const personaBox = formCatalogo.querySelector('[data-atlas-asigna-persona]');
        const vacanteBox = formCatalogo.querySelector('[data-atlas-asigna-vacante]');
        const personaSelect = formCatalogo.querySelector('select[name="divisional_id"]');
        const vacanteInput = formCatalogo.querySelector('input[name="nombre_vacante"]');
        if (personaBox) personaBox.style.display = tipo === 'vacante' ? 'none' : '';
        if (vacanteBox) vacanteBox.style.display = tipo === 'vacante' ? '' : 'none';
        if (personaSelect) {
            personaSelect.required = tipo !== 'vacante';
            if (tipo === 'vacante') personaSelect.value = '';
            const select2Box = personaSelect.nextElementSibling && personaSelect.nextElementSibling.classList && personaSelect.nextElementSibling.classList.contains('select2')
                ? personaSelect.nextElementSibling
                : null;
            if (select2Box) select2Box.style.display = tipo === 'vacante' ? 'none' : '';
        }
        if (vacanteInput) vacanteInput.required = tipo === 'vacante';
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
    function abrirReglasClasificacion(id) {
        const row = buscarPorTipo('clasificacion', id);
        const nombre = row && row.nombre ? row.nombre : 'Clasificación';
        if (reglasClasificacionTitulo) reglasClasificacionTitulo.textContent = 'Reglas de negocio en construcción';
        if (reglasClasificacionSub) reglasClasificacionSub.textContent = 'Clasificación: ' + nombre + '. Aquí se armarán las reglas operativas cuando estén definidas.';
        mostrarModal(modalReglasClasificacionEl);
    }
    function refrescarCombosSucursal(valores) {
        const v = valores || valoresSucursalActuales();
        llenarSelect('atlas-sucursal-distribuidor', catalogos.distribuidores, 'Selecciona distribuidor');
        llenarSelect('atlas-sucursal-clasificacion', catalogos.clasificaciones, 'Selecciona clasificación');
        actualizarCascadaSucursal(v);
        Object.keys(v).forEach(key => setFormValue(formSucursal, key, v[key]));
        aplicarPermisosSucursalModal();
    }
    function agregarCatalogoLocal(tipo, payload, id) {
        const nuevoId = String(id || '');
        if (!nuevoId) return;
        const activo = Number(payload.activo ?? 1) === 1 ? 1 : 0;
        const nombre = String(payload.nombre || '').trim();
        if (!nombre) return;
        if (tipo === 'distribuidor') {
            catalogos.distribuidores = (catalogos.distribuidores || []).filter(row => String(row.id || '') !== nuevoId).concat([{ id: nuevoId, nombre: nombre, icon_font: payload.icon_font || 'fa-solid fa-building', activo: activo }]);
        } else if (tipo === 'division') {
            const divisionalId = String(payload.divisional_id || '');
            const divisional = findCatalogo('divisionales', divisionalId);
            catalogos.divisiones = (catalogos.divisiones || []).filter(row => String(row.id || '') !== nuevoId).concat([{ id: nuevoId, nombre: nombre, icon_font: payload.icon_font || 'fa-solid fa-diagram-project', color_hex: colorHexSeguro(payload.color_hex || '#2563EB'), activo: activo, divisional_id: divisionalId, divisional_nombre: divisional ? divisional.nombre : '' }]);
        } else if (tipo === 'clasificacion') {
            const ordenes = (catalogos.clasificaciones || []).map(row => parseInt(row.orden, 10) || 0);
            const siguienteOrden = Math.max(0, ...ordenes) + 1;
            catalogos.clasificaciones = (catalogos.clasificaciones || []).filter(row => String(row.id || '') !== nuevoId).concat([{
                id: nuevoId,
                nombre: nombre,
                activo: activo,
                icon_font: payload.icon_font || 'fa-solid fa-tags',
                color_hex: colorHexSeguro(payload.color_hex || '#94A3B8'),
                descripcion: payload.descripcion || '',
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
        const calle = componenteDireccionGoogle(componentes, 'route');
        const numeroExterior = componenteDireccionGoogle(componentes, 'street_number') || 'S/N';
        return {
            calle: calle,
            numero_exterior: numeroExterior,
            numero_interior: componenteDireccionGoogle(componentes, 'subpremise'),
            estado: normalizarEstadoGoogle(componenteDireccionGoogle(componentes, 'administrative_area_level_1')),
            municipio: componenteDireccionGoogle(componentes, 'locality') || componenteDireccionGoogle(componentes, 'administrative_area_level_2'),
            colonia: componenteDireccionGoogle(componentes, 'neighborhood') || componenteDireccionGoogle(componentes, 'sublocality_level_1') || componenteDireccionGoogle(componentes, 'sublocality'),
            localidad: componenteDireccionGoogle(componentes, 'locality') || componenteDireccionGoogle(componentes, 'administrative_area_level_2'),
            codigo_postal: componenteDireccionGoogle(componentes, 'postal_code')
        };
    }
    function setDireccionSeleccionada(lat, lng, direccion, componentes, centrar) {
        const extra = extraerDireccionGoogle(componentes || []);
        const direccionTxt = String(direccion || '').trim();
        const calle = extra.calle || direccionTxt.split(',')[0].trim();
        atlasDireccionActual = { lat: lat, lng: lng, direccion: direccionTxt, calle: calle, numero_exterior: extra.numero_exterior, numero_interior: extra.numero_interior, estado: extra.estado, municipio: extra.municipio, localidad: extra.localidad, colonia: extra.colonia, codigo_postal: extra.codigo_postal };
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
        setFormValue(formSucursal, 'calle', atlasDireccionActual.calle || '');
        setFormValue(formSucursal, 'numero_exterior', atlasDireccionActual.numero_exterior || 'S/N');
        setFormValue(formSucursal, 'numero_interior', atlasDireccionActual.numero_interior || getFormValue(formSucursal, 'numero_interior') || '');
        setFormValue(formSucursal, 'latitud', atlasDireccionActual.lat.toFixed(7));
        setFormValue(formSucursal, 'longitud', atlasDireccionActual.lng.toFixed(7));
        setFormValue(formSucursal, 'coordenadas', atlasDireccionActual.lat.toFixed(7) + ',' + atlasDireccionActual.lng.toFixed(7));
        ['estado','municipio','localidad','colonia','codigo_postal'].forEach(key => { if (atlasDireccionActual[key]) setFormValue(formSucursal, key, atlasDireccionActual[key]); });
        cerrarModal(modalDireccionEl);
    }

    function setDireccionStatus(texto, habilitar) {
        if (direccionCoordenadas) direccionCoordenadas.textContent = texto;
        if (btnConfirmarDireccion) btnConfirmarDireccion.disabled = !habilitar;
    }
    function setDireccionSeleccionada(lat, lng, direccion, componentes, centrar) {
        const extra = extraerDireccionGoogle(componentes || []);
        const direccionTxt = String(direccion || '').trim();
        const calle = extra.calle || direccionTxt.split(',')[0].trim();
        atlasDireccionActual = { lat: lat, lng: lng, direccion: direccionTxt, calle: calle, numero_exterior: extra.numero_exterior, numero_interior: extra.numero_interior, estado: extra.estado, municipio: extra.municipio, localidad: extra.localidad, colonia: extra.colonia, codigo_postal: extra.codigo_postal };
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
            calle: atlasDireccionActual.calle || '',
            numero_exterior: atlasDireccionActual.numero_exterior || 'S/N',
            numero_interior: atlasDireccionActual.numero_interior || getFormValue(formSucursal, 'numero_interior') || '',
            latitud: atlasDireccionActual.lat.toFixed(7),
            longitud: atlasDireccionActual.lng.toFixed(7),
            coordenadas: atlasDireccionActual.lat.toFixed(7) + ',' + atlasDireccionActual.lng.toFixed(7)
        };
        ['estado','municipio','localidad','colonia','codigo_postal'].forEach(key => { if (atlasDireccionActual[key]) valores[key] = atlasDireccionActual[key]; });
        Object.keys(valores).forEach(key => setFormValue(formSucursal, key, valores[key]));
        if (atlasDireccionContext && atlasDireccionContext.snapshot) Object.assign(atlasDireccionContext.snapshot, valores);
        actualizarCamposDireccionSucursal();
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
        if (calidadConfig) calidadConfig.style.display = esSinCoordenadas ? 'none' : '';
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
    function validarLongitudesSucursal() {
        const reglas = [
            ['sucursal', 'Sucursal', 120],
            ['direccion_sucursal', 'Dirección', 250],
            ['calle', 'Calle', 120],
            ['numero_exterior', 'No. exterior', 40],
            ['numero_interior', 'No. interior', 40],
            ['colonia', 'Colonia', 120],
            ['localidad', 'Localidad', 120],
            ['municipio', 'Municipio', 120],
            ['estado', 'Estado', 120],
            ['codigo_postal', 'Código postal', 10],
            ['latitud', 'Latitud', 16],
            ['longitud', 'Longitud', 16]
        ];
        const errores = [];
        reglas.forEach(([name, label, max]) => {
            const valor = getFormValue(formSucursal, name);
            if (valor.length > max) errores.push(label + ' máximo ' + max + ' caracteres');
        });
        const cp = getFormValue(formSucursal, 'codigo_postal');
        if (cp && !/^\d{5}$/.test(cp)) errores.push('Código postal debe tener 5 dígitos');
        const lat = parseFloat(getFormValue(formSucursal, 'latitud'));
        const lng = parseFloat(getFormValue(formSucursal, 'longitud'));
        if (Number.isNaN(lat) || lat < -90 || lat > 90) errores.push('Latitud no válida');
        if (Number.isNaN(lng) || lng < -180 || lng > 180) errores.push('Longitud no válida');
        if (errores.length) throw new Error(errores.join('. ') + '.');
    }
    function validarSucursalObligatoria() {
        const campos = ['sucursal','distribuidor_id','clasificacion_id','direccion_sucursal','calle','numero_exterior','estado','municipio','localidad','colonia','codigo_postal','latitud','longitud','activo'];
        const faltantes = campos.filter(name => !getFormValue(formSucursal, name));
        if (faltantes.length) throw new Error('Completa todos los campos obligatorios.');
        const claveSucursal = atlasClaveCalidad(getFormValue(formSucursal, 'sucursal'));
        const idActual = String(getFormValue(formSucursal, 'id') || '');
        const duplicada = (sucursales || []).find(row => String(row.id || '') !== idActual && atlasClaveCalidad(row.sucursal || '') === claveSucursal);
        if (duplicada) {
            throw new Error('Ya existe una sucursal con ese nombre: ' + (duplicada.sucursal || 'Sin nombre') + (duplicada.fk_sucursal ? ' · FK ' + duplicada.fk_sucursal : '') + '.');
        }
        validarLongitudesSucursal();
        if (asignacionSucursalIniciada() && !asignacionSucursalCompleta()) {
            throw new Error('Para completar el Paso 2 debes capturar divisional, división, regional, supervisor y asesor.');
        }
        if (asignacionSucursalCompleta()) {
            setFormValue(formSucursal, 'paso_alta', 'paso2');
            if (!permisosSucursal.paso2) throw new Error('No tienes permiso para completar el Paso 2 de asignación.');
            return;
        }
        setFormValue(formSucursal, 'paso_alta', 'paso1');
    }
    async function procesarSucursalPendiente(btn) {
        const id = btn.getAttribute('data-id') || '';
        const accion = btn.getAttribute('data-atlas-pendiente-accion') || '';
        if (!id || !accion) return;
        let payload = { id: id, accion: accion };
        if (accion === 'mapear') {
            const opciones = {};
            (sucursales || []).forEach(row => { if (row && row.fk_sucursal) opciones[String(row.fk_sucursal)] = 'FK ' + row.fk_sucursal + ' · ' + (row.sucursal || 'Sin nombre'); });
            if (!Object.keys(opciones).length) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'Sin sucursales', text: 'Primero carga o registra sucursales en el catálogo local.' }); return; }
            const res = typeof Swal !== 'undefined'
                ? await Swal.fire({ title: 'Mapear sucursal pendiente', input: 'select', inputOptions: opciones, inputPlaceholder: 'Selecciona sucursal Sparta', showCancelButton: true, confirmButtonText: 'Mapear', cancelButtonText: 'Cancelar', inputValidator: value => !value ? 'Selecciona una sucursal.' : undefined })
                : { isConfirmed: false };
            if (!res.isConfirmed) return;
            payload.fk_sucursal_sparta = res.value;
        }
        if (accion === 'descartar') {
            const res = typeof Swal !== 'undefined'
                ? await Swal.fire({ title: 'Descartar pendiente', input: 'textarea', inputPlaceholder: 'Motivo para auditoría', showCancelButton: true, confirmButtonText: 'Descartar', cancelButtonText: 'Cancelar', inputValidator: value => !String(value || '').trim() ? 'Captura el motivo.' : undefined })
                : { isConfirmed: false };
            if (!res.isConfirmed) return;
            payload.observaciones = res.value;
        }
        try {
            await guardarJson('/Atlas/actualizarSucursalPendiente', payload);
            await cargarCatalogos({ silencioso: true });
        } catch (err) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo actualizar', text: err.message || 'Error' });
        }
    }
    document.addEventListener('click', async function (ev) {
        const iconOption = ev.target.closest('[data-atlas-icon-option]');
        if (iconOption) {
            ev.preventDefault();
            if (iconOption.classList.contains('is-disabled')) return;
            const icon = iconOption.getAttribute('data-atlas-icon-option') || '';
            const input = document.getElementById('atlas-catalogo-icon-font'); if (input) input.value = icon;
            document.querySelectorAll('.atlas-icon-option').forEach(btn => btn.classList.toggle('is-active', btn === iconOption));
            const picker = iconOption.closest('.atlas-icon-picker');
            const current = picker ? picker.querySelector('[data-atlas-icon-current]') : null;
            if (current) current.innerHTML = '<i class="' + esc(icon) + '"></i>';
            if (picker) picker.removeAttribute('open');
            return;
        }
        const agregarPresencia = ev.target.closest('#atlas-btn-agregar-presencia');
        if (agregarPresencia) {
            ev.preventDefault();
            agregarPresenciaDistribuidorDesdeModal();
            return;
        }
        const addDistOption = ev.target.closest('[data-atlas-dist-add-option]');
        if (addDistOption) {
            ev.preventDefault();
            agregarOpcionCatalogoDistribuidor(addDistOption.getAttribute('data-atlas-dist-add-option') || '');
            return;
        }
        const statusBtn = ev.target.closest('[data-atlas-dist-status]');
        if (statusBtn) {
            ev.preventDefault();
            setEstatusDistribuidorModal(statusBtn.getAttribute('data-atlas-dist-status') || 'activo');
            return;
        }
        const removerPresencia = ev.target.closest('[data-atlas-remover-presencia]');
        if (removerPresencia) {
            ev.preventDefault();
            const idx = parseInt(removerPresencia.getAttribute('data-atlas-remover-presencia'), 10);
            if (!Number.isNaN(idx)) {
                atlasDistribuidorPresencias.splice(idx, 1);
                renderPresenciasDistribuidorEditor();
                const estadoEl = document.getElementById('atlas-presencia-estado');
                if (estadoEl && estadoEl.value) cargarMunicipiosPresencia(estadoEl.value);
            }
            return;
        }
        const pendienteAccion = ev.target.closest('[data-atlas-pendiente-accion]');
        if (pendienteAccion) { ev.preventDefault(); procesarSucursalPendiente(pendienteAccion); return; }
        const reglasClasificacion = ev.target.closest('[data-atlas-reglas-clasificacion]');
        if (reglasClasificacion) {
            ev.preventDefault();
            abrirReglasClasificacion(reglasClasificacion.getAttribute('data-atlas-reglas-clasificacion'));
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
        if (agregar) {
            ev.preventDefault();
            const tipo = agregar.getAttribute('data-atlas-agregar');
            if (tipo === 'sucursal') {
                if (!permisosSucursal.paso1) return;
                abrirSucursal(null);
            } else {
                abrirCatalogo(tipo, null);
            }
            return;
        }
        const fusionarDivisiones = ev.target.closest('[data-atlas-fusionar-divisiones]');
        if (fusionarDivisiones) {
            ev.preventDefault();
            abrirFusionDivisiones();
            return;
        }
        const editar = ev.target.closest('[data-atlas-editar]');
        if (editar) {
            ev.preventDefault();
            const tipo = editar.getAttribute('data-atlas-editar'), id = editar.getAttribute('data-id');
            if (tipo === 'sucursal') abrirSucursal(sucursales.find(row => String(row.id || '') === String(id || '')) || null);
            else abrirCatalogo(tipo, buscarPorTipo(tipo, id));
            return;
        }
        const eliminarDivision = ev.target.closest('[data-atlas-eliminar-division]');
        if (eliminarDivision) {
            ev.preventDefault();
            const id = eliminarDivision.getAttribute('data-atlas-eliminar-division') || '';
            const nombre = eliminarDivision.getAttribute('data-nombre') || 'la división';
            let confirmar = { isConfirmed: true, value: 'Eliminación solicitada desde catálogo operativo' };
            if (typeof Swal !== 'undefined') {
                confirmar = await Swal.fire({
                    icon: 'warning',
                    title: 'Eliminar división',
                    html: 'Se eliminará <strong>' + esc(nombre) + '</strong> solo si no tiene sucursales o regionales asignadas. La acción quedará en bitácora.',
                    input: 'textarea',
                    inputPlaceholder: 'Motivo para bitácora',
                    inputValue: 'Eliminación solicitada desde catálogo operativo',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar',
                    inputValidator: value => !String(value || '').trim() ? 'Captura el motivo para bitácora.' : undefined
                });
            }
            if (!confirmar.isConfirmed) return;
            try {
                await guardarJson('/Atlas/eliminarDivision', { id: id, motivo: confirmar.value || 'Eliminación solicitada desde catálogo operativo' });
                await cargarCatalogos({ silencioso: true });
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'División eliminada', text: 'La eliminación quedó registrada en bitácora.' });
            } catch (err) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se puede eliminar', text: err.message || 'La división aún tiene asignaciones.' });
            }
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
        const cardErroresDivisiones = ev.target.closest('#atlas-divisiones-error-card');
        if (cardErroresDivisiones) {
            ev.preventDefault();
            abrirErroresDivisiones();
            return;
        }
        const cardActualizacionesDivisiones = ev.target.closest('#atlas-divisiones-actualizaciones-card');
        if (cardActualizacionesDivisiones) {
            ev.preventDefault();
            atlasAsignacionDivisionContext = null;
            abrirActualizacionesDivisionales();
            return;
        }
        const agregarDivisionalDesdeAsignacion = ev.target.closest('[data-atlas-agregar-divisional-desde-asignacion]');
        if (agregarDivisionalDesdeAsignacion) {
            ev.preventDefault();
            abrirActualizacionesDesdeAsignacion();
            return;
        }
        const editarErrorDivision = ev.target.closest('[data-atlas-editar-error-division]');
        if (editarErrorDivision) {
            ev.preventDefault();
            const id = editarErrorDivision.getAttribute('data-atlas-editar-error-division');
            const row = buscarPorTipo('division', id);
            cerrarModal(modalErroresDivisionesEl);
            if (row) setTimeout(() => abrirCatalogo('asigna_division', row), 180);
            return;
        }
        const agregarDivisionalPersona = ev.target.closest('[data-atlas-agregar-divisional-persona], #atlas-divisiones-disponibles-agregar');
        if (agregarDivisionalPersona) {
            ev.preventDefault();
            const personaId = agregarDivisionalPersona.id === 'atlas-divisiones-disponibles-agregar'
                ? (divisionesDisponiblesSelect ? divisionesDisponiblesSelect.value : '')
                : agregarDivisionalPersona.getAttribute('data-atlas-agregar-divisional-persona');
            if (!personaId) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'Selecciona colaborador', text: 'Elige un colaborador operativo para agregarlo como divisional activo.' });
                return;
            }
            try {
                const data = await guardarJson('/Atlas/crearDivisionalDesdePersona', { persona_id: personaId });
                await refrescarActualizacionesDivisionales();
                if (atlasAsignacionDivisionContext) {
                    reabrirAsignacionDivisionConDivisional(data.id || '');
                }
            } catch (err) {
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo agregar', text: err.message || 'Error' });
            }
            return;
        }
        const desactivarDivisional = ev.target.closest('[data-atlas-desactivar-divisional]');
        if (desactivarDivisional) {
            ev.preventDefault();
            const id = desactivarDivisional.getAttribute('data-atlas-desactivar-divisional');
            let confirmar = { isConfirmed: true };
            if (typeof Swal !== 'undefined') {
                confirmar = await Swal.fire({
                    icon: 'warning',
                    title: 'Sacar divisional',
                    text: 'Se desactiva del catálogo, no se borra información histórica.',
                    input: 'textarea',
                    inputPlaceholder: 'Motivo de baja',
                    showCancelButton: true,
                    confirmButtonText: 'Sacar',
                    cancelButtonText: 'Cancelar',
                    inputValidator: value => !String(value || '').trim() ? 'Captura el motivo de baja.' : undefined
                });
            }
            if (!confirmar.isConfirmed) return;
            const filaDivisional = desactivarDivisional.closest('.atlas-division-error-item');
            const textoOriginal = desactivarDivisional.innerHTML;
            desactivarDivisional.disabled = true;
            desactivarDivisional.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Sacando';
            try {
                await guardarJson('/Atlas/desactivarDivisional', { id: id, motivo_baja: confirmar.value || '' });
                if (filaDivisional) filaDivisional.remove();
                await refrescarActualizacionesDivisionales();
            } catch (err) {
                desactivarDivisional.disabled = false;
                desactivarDivisional.innerHTML = textoOriginal;
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo sacar', text: err.message || 'Error' });
            }
            return;
        }
    });
    document.addEventListener('input', function (ev) {
        if (!ev.target) return;
        if (formSucursal && ev.target.form === formSucursal && ev.target.name === 'sucursal') {
            actualizarCoincidenciaNombreSucursal();
        }
        if (ev.target.id === 'atlas-catalogo-color') {
            const label = document.getElementById('atlas-catalogo-color-label');
            if (label) label.textContent = colorHexSeguro(ev.target.value);
        }
        if (formCatalogo && ev.target.form === formCatalogo && ev.target.name === 'rfc') {
            ev.target.value = String(ev.target.value || '').toUpperCase().replace(/[^A-Z0-9Ñ&]/g, '').slice(0, 13);
        }
        if (formCatalogo && ev.target.form === formCatalogo && (ev.target.name === 'telefono_contacto' || ev.target.name === 'telefono_secundario')) {
            ev.target.value = String(ev.target.value || '').replace(/\D+/g, '').slice(0, 10);
        }
        if (formCatalogo && ev.target.form === formCatalogo && ev.target.name === 'cuenta_deposito') {
            ev.target.value = String(ev.target.value || '').replace(/\D+/g, '').slice(0, 20);
        }
        if (formCatalogo && ev.target.form === formCatalogo && ev.target.name === 'clabe_deposito') {
            ev.target.value = String(ev.target.value || '').replace(/\D+/g, '').slice(0, 18);
        }
        if (formCatalogo && ev.target.form === formCatalogo && ev.target.matches('[data-atlas-tiempo-estadia-cantidad]')) {
            syncTiempoEstadiaDistribuidor();
        }
    });
    document.addEventListener('keydown', function (ev) {
        if (!ev.target || (ev.target.id !== 'atlas-presencia-estado' && ev.target.id !== 'atlas-presencia-municipio')) return;
        if (ev.key === 'Enter') {
            ev.preventDefault();
            agregarPresenciaDistribuidorDesdeModal();
        }
    });
    document.addEventListener('change', function (ev) {
        if (ev.target && ev.target.id === 'atlas-config-sin-telefono-error') {
            guardarConfiguracionCalidad();
            return;
        }
        if (ev.target && ev.target.id === 'atlas-presencia-estado') {
            cargarMunicipiosPresencia(ev.target.value || '');
            return;
        }
        if (ev.target && ev.target.id === 'atlas-sucursal-clasificacion' && ev.target.dataset.atlasLockedOportunidad === '1') {
            forzarClasificacionAutomatica();
            if (window.jQuery && jQuery.fn && jQuery.fn.select2) jQuery(ev.target).trigger('change.select2');
        }
        if (ev.target && ev.target.name === 'presencia_fisica') {
            actualizarVisibilidadPresenciaDistribuidor();
            return;
        }
        if (ev.target && ev.target.name === 'clasificacion_id') actualizarMarkerDireccionIcono();
        const checkGroup = ev.target && ev.target.closest ? ev.target.closest('[data-atlas-check-group]') : null;
        if (checkGroup) {
            syncCheckboxGrupoDistribuidor(checkGroup.getAttribute('data-atlas-check-group') || '');
            return;
        }
        if (ev.target && ev.target.name === 'bloqueo_vigencia') {
            actualizarVisibilidadBloqueoDistribuidor();
            return;
        }
        if (formCatalogo && ev.target.form === formCatalogo && ev.target.matches('[data-atlas-tiempo-estadia-unidad]')) {
            syncTiempoEstadiaDistribuidor();
            return;
        }
    });
    if (window.jQuery) {
        jQuery(document)
            .off('change.atlasPresenciaEstado select2:select.atlasPresenciaEstado', '#atlas-presencia-estado')
            .on('change.atlasPresenciaEstado select2:select.atlasPresenciaEstado', '#atlas-presencia-estado', function () {
                cargarMunicipiosPresencia(this.value || '');
            });
        jQuery(document)
            .off('change.atlasPresenciaFisica select2:select.atlasPresenciaFisica', 'select[name="presencia_fisica"]')
            .on('change.atlasPresenciaFisica select2:select.atlasPresenciaFisica', 'select[name="presencia_fisica"]', function () {
                actualizarVisibilidadPresenciaDistribuidor();
            });
    }
    if (formSucursal) {
        ['division_id','divisional_id','regional_id','supervisor_id'].forEach(name => {
            const el = formSucursal.elements[name];
            if (!el) return;
            el.addEventListener('change', function () { manejarCambioCascadaSucursal(name); });
        });
        const dir = formSucursal.elements.direccion_sucursal;
        if (dir) dir.addEventListener('click', function () {
            if (!permisosSucursal.paso1) return;
            abrirDireccionGoogle();
        });
        formSucursal.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            try { forzarClasificacionAutomatica(); validarSucursalObligatoria(); await guardarJson('/Atlas/guardarSucursal', formToJson(formSucursal)); cerrarModal(modalSucursalEl); await cargarSucursales(); }
            catch (err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: err.message || 'Error' }); }
        });
    }
    if (formFusionDivisiones) {
        formFusionDivisiones.addEventListener('submit', submitFusionDivisiones);
        const destinoFusion = document.getElementById('atlas-fusion-division-destino');
        const origenesFusion = document.getElementById('atlas-fusion-division-origenes');
        if (destinoFusion) destinoFusion.addEventListener('change', function () { actualizarOpcionesFusionDivisiones(); actualizarResumenFusionDivisiones(); });
        if (origenesFusion) origenesFusion.addEventListener('change', actualizarResumenFusionDivisiones);
    }
    if (modalDireccionEl) modalDireccionEl.addEventListener('shown.bs.modal', function () { inicializarModalDireccion().catch(err => { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'No se pudo abrir el mapa', text: err.message || 'Revisa la configuración de Google Maps.' }); }); });
    if (modalDireccionEl) modalDireccionEl.addEventListener('hidden.bs.modal', restaurarSucursalDesdeDireccion);
    if (btnConfirmarDireccion) btnConfirmarDireccion.addEventListener('click', confirmarDireccionGoogle);
    if (direccionBusqueda) direccionBusqueda.addEventListener('keydown', ev => { if (ev.key === 'Enter') ev.preventDefault(); });
    if (direccionBusqueda) direccionBusqueda.addEventListener('keydown', function (ev) { if (ev.key === 'Enter') { ev.preventDefault(); resolverDireccionPorTexto(); } });
    if (btnMapa) btnMapa.addEventListener('click', renderMapa);
    if (btnErrores) btnErrores.addEventListener('click', function () { abrirModalCalidad('errores'); });
    if (btnSinCoordenadas) btnSinCoordenadas.addEventListener('click', function () { abrirModalCalidad('sin-coordenadas'); });
    if (btnSucursalesPendientes) btnSucursalesPendientes.addEventListener('click', function () {
        mostrarModal(modalSucursalesPendientesEl);
        setTimeout(ajustarTablasAtlas, 120);
    });
    if (modalSucursalesPendientesEl) modalSucursalesPendientesEl.addEventListener('shown.bs.modal', function () { setTimeout(ajustarTablasAtlas, 80); });
    if (modalActualizacionesDivisionalesEl) {
        modalActualizacionesDivisionalesEl.addEventListener('hidden.bs.modal', function () {
            if (!atlasAsignacionDivisionContext) return;
            const ctx = atlasAsignacionDivisionContext;
            atlasAsignacionDivisionContext = null;
            const divisionId = ctx.division_id || ctx.asignacion_id || '';
            const row = (catalogos.divisiones || []).find(item => String(item.id || '') === String(divisionId || '')) || null;
            if (row) setTimeout(() => abrirCatalogo('asigna_division', row, { prefill: { division_id: divisionId, tipo_asignacion: 'persona' } }), 180);
        });
    }
    if (btnRecargar) btnRecargar.addEventListener('click', function () { Promise.all([cargarCatalogos(), cargarSucursales()]).catch(() => {}); });
    function activarAsignaDivisiones() {
        const btnAsigna = document.querySelector('#atlas-divisiones-subtabs [data-bs-target="#atlas-subtab-divisiones-asigna"]');
        const paneCatalogo = document.getElementById('atlas-subtab-divisiones-catalogo');
        const paneAsigna = document.getElementById('atlas-subtab-divisiones-asigna');
        if (!btnAsigna || !paneCatalogo || !paneAsigna) return;
        document.querySelectorAll('#atlas-divisiones-subtabs .nav-link').forEach(btn => {
            const activo = btn === btnAsigna;
            btn.classList.toggle('active', activo);
            btn.setAttribute('aria-selected', activo ? 'true' : 'false');
            if (activo) btn.removeAttribute('tabindex');
            else btn.setAttribute('tabindex', '-1');
        });
        paneAsigna.classList.add('show', 'active');
        paneCatalogo.classList.remove('show', 'active');
    }
    document.querySelectorAll('#atlas-tabs [data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function () {
            guardarAtlasTabActivo(btn.getAttribute('data-bs-target') || '');
            if ((btn.getAttribute('data-bs-target') || '') === '#atlas-tab-divisiones') {
                activarAsignaDivisiones();
            }
            setTimeout(ajustarTablasAtlas, 80);
        });
    });
    document.querySelectorAll('#atlas-divisiones-subtabs [data-bs-toggle="tab"]').forEach(btn => {
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
    const btnTemplateDistribuidores = document.getElementById('atlas-btn-template-distribuidores');
    if (btnTemplateDistribuidores) btnTemplateDistribuidores.addEventListener('click', descargarTemplateDistribuidores);
    const btnImportarDistribuidores = document.getElementById('atlas-btn-importar-distribuidores');
    if (btnImportarDistribuidores) btnImportarDistribuidores.addEventListener('click', abrirImportarDistribuidores);
    if (formImportarDistribuidores) formImportarDistribuidores.addEventListener('submit', importarDistribuidoresSubmit);
    if (formCatalogo) {
        formCatalogo.addEventListener('change', function (ev) {
            if (ev.target && ev.target.name === 'division_id') actualizarDivisionalesAsignacion();
            if (ev.target && ev.target.name === 'tipo_asignacion') actualizarTipoAsignacionDivision();
        });
        formCatalogo.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            syncPresenciasDistribuidorInput();
            syncCheckboxGrupoDistribuidor('tipo_motos');
            syncCheckboxGrupoDistribuidor('canal_venta');
            syncTiempoEstadiaDistribuidor();
            const payload = formToJson(formCatalogo), tipo = payload.tipo;
            const urls = { division: '/Atlas/guardarDivision', asigna_division: '/Atlas/guardarAsignacionDivision', distribuidor: '/Atlas/guardarDistribuidor', clasificacion: '/Atlas/guardarClasificacion' };
            try {
                if (tipo === 'distribuidor' && String(payload.presencia_fisica || '1') === '1' && atlasDistribuidorPresencias.length === 0) {
                    throw new Error('Agrega al menos un estado y municipio para la presencia física del distribuidor.');
                }
                if (tipo === 'distribuidor' && (!payload.tipo_motos || !payload.canal_venta)) {
                    throw new Error('Selecciona al menos un tipo de moto y un canal de venta.');
                }
                if (tipo === 'division') {
                    validarNombreDivisionUnico(payload);
                }
                const ctx = atlasQuickAddContext;
                const data = await guardarJson(urls[tipo], payload);
                if (tipo === 'distribuidor') {
                    await subirConstanciaDistribuidor(data.id);
                    await subirEstadoCuentaDistribuidor(data.id);
                }
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
    restaurarAtlasTabActivo();
    cargarVistaInicialAtlas();
})();
</script>
