<?php /** @var string $google_maps_api_key_js */ ?>
<style>
/* =======================================================
   Tracking Recoleccion  -  variables de color (teal/cyan)
======================================================= */
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

/* -- Cabecera del modulo -- */
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

/* -- Filtros -- */
.trk-section-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}
.trk-section-card {
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: .55rem;
    padding: 1rem;
    min-height: 142px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: left;
    box-shadow: 0 .12rem .55rem rgba(15,23,42,.05);
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}
.trk-section-card:hover {
    transform: translateY(-2px);
    border-color: var(--track-color);
    box-shadow: 0 .35rem 1rem rgba(13,148,136,.12);
}
.trk-section-card.active {
    border-color: var(--track-color);
    box-shadow: 0 .35rem 1rem rgba(13,148,136,.16);
}
.trk-section-icon {
    width: 2.35rem;
    height: 2.35rem;
    border-radius: .5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--track-color-light);
    color: var(--track-color-dark);
    font-size: 1.05rem;
}
.trk-section-title {
    font-weight: 700;
    color: #26364f;
    margin-top: .75rem;
}
.trk-section-desc {
    color: #64748b;
    font-size: .78rem;
    margin-top: .18rem;
}
.trk-section-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: .85rem;
    color: var(--track-color);
    font-size: .78rem;
    font-weight: 700;
}
.trk-section-count {
    background: rgba(13,148,136,.1);
    color: var(--track-color-dark);
    border-radius: 999px;
    padding: .15rem .48rem;
    font-size: .7rem;
}
body.dark-mode .trk-section-card {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-section-card.active {
    border-color: var(--track-color);
}
body.dark-mode .trk-section-title {
    color: #f8fafc;
}
body.dark-mode .trk-section-desc {
    color: #94a3b8;
}
@media (max-width: 767.98px) {
    .trk-section-grid { grid-template-columns: 1fr; }
}
@media (min-width: 768px) and (max-width: 1199.98px) {
    .trk-section-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

/* -- Administracion de transportistas -- */
.trk-admin-shell { display: grid; gap: 1rem; }
.trk-admin-kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
    gap: .75rem;
}
.trk-admin-kpi,
.trk-admin-toolbar,
.trk-admin-card,
.trk-admin-empty {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .75rem;
    box-shadow: 0 .12rem .55rem rgba(15,23,42,.04);
}
.trk-admin-kpi { padding: .85rem; }
.trk-admin-kpi span,
.trk-admin-metric span {
    display: block;
    color: #64748b;
    font-size: .66rem;
    font-weight: 800;
    text-transform: uppercase;
}
.trk-admin-kpi strong {
    display: block;
    color: #25364f;
    font-size: 1.45rem;
    line-height: 1.1;
    margin-top: .25rem;
}
.trk-admin-toolbar {
    display: grid;
    grid-template-columns: minmax(220px, 1fr) 180px 170px auto auto auto;
    gap: .65rem;
    align-items: end;
    padding: .9rem;
}
.trk-admin-view-toggle .btn {
    min-width: 2.35rem;
    height: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.trk-admin-alerts { display: grid; gap: .5rem; }
.trk-admin-alert {
    border-radius: .55rem;
    border: 1px solid #fde68a;
    background: #fffbeb;
    color: #92400e;
    padding: .6rem .75rem;
    font-size: .78rem;
    font-weight: 700;
}
.trk-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: .85rem;
}
.trk-admin-grid.is-list {
    grid-template-columns: 1fr;
}
.trk-admin-grid.is-list .trk-admin-card {
    grid-template-columns: minmax(260px, .9fr) minmax(340px, 1.4fr);
    align-items: start;
}
.trk-admin-grid.is-list .trk-admin-card-head,
.trk-admin-grid.is-list .trk-admin-metrics,
.trk-admin-grid.is-list .trk-admin-route-list {
    grid-column: auto;
}
.trk-admin-grid.is-list .trk-admin-route-list {
    grid-row: span 5;
}
.trk-admin-card {
    padding: .95rem;
    display: grid;
    gap: .72rem;
}
.trk-admin-card-head {
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    align-items: flex-start;
}
.trk-admin-name {
    color: #25364f;
    font-size: .95rem;
    font-weight: 900;
    line-height: 1.18;
}
.trk-admin-sub,
.trk-admin-live {
    color: #64748b;
    font-size: .73rem;
}
.trk-admin-status {
    border-radius: 999px;
    padding: .18rem .5rem;
    font-size: .65rem;
    font-weight: 900;
    white-space: nowrap;
}
.trk-admin-status.disponible { background: #dcfce7; color: #166534; }
.trk-admin-status.en_ruta { background: #dbeafe; color: #1d4ed8; }
.trk-admin-status.programado { background: #fef3c7; color: #92400e; }
.trk-admin-status.advertencia { background: #ffedd5; color: #c2410c; }
.trk-admin-status.saturado { background: #fee2e2; color: #b91c1c; }
.trk-admin-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .45rem;
}
.trk-admin-metric {
    border: 1px solid #e2e8f0;
    border-radius: .55rem;
    padding: .5rem;
    min-width: 0;
}
.trk-admin-metric strong { color: #25364f; font-size: .9rem; }
.trk-admin-progress {
    height: .45rem;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
}
.trk-admin-progress-bar {
    height: 100%;
    width: 0%;
    background: #0d9488;
    border-radius: inherit;
}
.trk-admin-progress-bar.warn { background: #f59e0b; }
.trk-admin-progress-bar.danger { background: #ef4444; }
.trk-admin-route-list { display: grid; gap: .45rem; }
.trk-admin-route {
    border: 1px dashed #cbd5e1;
    border-radius: .55rem;
    padding: .55rem .65rem;
    background: #f8fafc;
    font-size: .74rem;
}
.trk-admin-route-title {
    color: #25364f;
    font-weight: 900;
}
.trk-admin-route-meta {
    color: #64748b;
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    margin-top: .2rem;
}
.trk-admin-empty {
    padding: 2rem;
    text-align: center;
    color: #64748b;
}
.trk-admin-table-wrap {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .75rem;
    box-shadow: 0 .12rem .55rem rgba(15,23,42,.04);
    overflow: hidden;
}
.trk-admin-table {
    margin: 0;
    font-size: .78rem;
}
.trk-admin-table thead th {
    color: #64748b;
    font-size: .68rem;
    font-weight: 900;
    text-transform: uppercase;
    border-bottom-color: #dbe3ef;
}
.trk-admin-table td {
    vertical-align: middle;
}
.trk-no-spinner::-webkit-outer-spin-button,
.trk-no-spinner::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.trk-no-spinner {
    -moz-appearance: textfield;
}
body.dark-mode .trk-admin-kpi,
body.dark-mode .trk-admin-toolbar,
body.dark-mode .trk-admin-card,
body.dark-mode .trk-admin-empty,
body.dark-mode .trk-admin-table-wrap {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-admin-kpi strong,
body.dark-mode .trk-admin-name,
body.dark-mode .trk-admin-metric strong,
body.dark-mode .trk-admin-route-title { color: #e2e8f0; }
body.dark-mode .trk-admin-sub,
body.dark-mode .trk-admin-kpi span,
body.dark-mode .trk-admin-metric span,
body.dark-mode .trk-admin-live,
body.dark-mode .trk-admin-route-meta { color: #94a3b8; }
body.dark-mode .trk-admin-metric,
body.dark-mode .trk-admin-route {
    background: #101818;
    border-color: #2d4444;
}
body.dark-mode .trk-admin-table thead th,
body.dark-mode .trk-admin-table td {
    border-color: #2d4444;
}

.trk-driver-assist {
    border: 1px solid #c7d2fe;
    background: #eef6ff;
    border-radius: .65rem;
    padding: .75rem;
}
.trk-driver-assist-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .55rem;
}
.trk-driver-assist-title {
    color: #25364f;
    font-size: .76rem;
    font-weight: 900;
    text-transform: uppercase;
}
.trk-driver-suggestions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: .55rem;
}
.trk-driver-suggestion {
    border: 1px solid #dbe3ef;
    background: #fff;
    border-radius: .6rem;
    padding: .6rem;
    min-width: 0;
}
.trk-driver-suggestion .name {
    color: #25364f;
    font-size: .78rem;
    font-weight: 900;
    line-height: 1.15;
}
.trk-driver-score {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    border-radius: 999px;
    padding: .18rem .5rem;
    font-size: .64rem;
    font-weight: 900;
    white-space: nowrap;
}
.trk-driver-score.success { background: #dcfce7; color: #166534; }
.trk-driver-score.info { background: #dbeafe; color: #1d4ed8; }
.trk-driver-score.warning { background: #fef3c7; color: #92400e; }
.trk-driver-score.danger { background: #fee2e2; color: #b91c1c; }
.trk-driver-mini {
    color: #64748b;
    font-size: .7rem;
}
.trk-driver-reasons {
    color: #64748b;
    font-size: .68rem;
    line-height: 1.25;
    margin-top: .35rem;
}
.trk-driver-select2 {
    display: grid;
    gap: .2rem;
    line-height: 1.15;
}
.trk-driver-select2-main {
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-wrap: wrap;
}
.trk-quick-driver-hint {
    border-top: 1px dashed #dbe3ef;
    margin-top: .45rem;
    padding-top: .45rem;
}
body.dark-mode .trk-driver-assist {
    background: #101818;
    border-color: #2d4444;
}
body.dark-mode .trk-driver-suggestion {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-driver-assist-title,
body.dark-mode .trk-driver-suggestion .name { color: #e2e8f0; }
body.dark-mode .trk-driver-mini,
body.dark-mode .trk-driver-reasons { color: #94a3b8; }
@media (max-width: 991.98px) {
    .trk-admin-toolbar { grid-template-columns: 1fr 1fr; }
    .trk-admin-grid.is-list .trk-admin-card { grid-template-columns: 1fr; }
}
@media (max-width: 575.98px) {
    .trk-admin-toolbar,
    .trk-admin-metrics { grid-template-columns: 1fr; }
    .trk-admin-grid { grid-template-columns: 1fr; }
}

.track-filters {
    background: var(--track-bg-card);
    border: 1px solid var(--track-border);
    border-radius: .75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
body.dark-mode .track-filters { background: #1e2d2c; }

/* -- Tabla creditos -- */
#tablaCreditos thead th {
    font-size: .8rem;
    vertical-align: middle;
    white-space: nowrap;
}
#tablaCreditos tbody tr { vertical-align: middle; }

#tablaCreditos.trk-operacion-table,
#tablaBorradores.trk-borradores-table,
#tablaRutas.trk-operacion-table {
    border-color: transparent;
    table-layout: fixed;
}
#tablaCreditos.trk-operacion-table thead th,
#tablaBorradores.trk-borradores-table thead th,
#tablaRutas.trk-operacion-table thead th {
    border: 0;
    border-bottom: 1px solid #eef2f7;
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: .9rem 1rem;
    white-space: nowrap;
}
#tablaCreditos.trk-operacion-table tbody td,
#tablaBorradores.trk-borradores-table tbody td,
#tablaRutas.trk-operacion-table tbody td {
    border-left: 0;
    border-right: 0;
    border-top: 0;
    border-bottom: 1px solid #eef2f7;
    padding: .85rem 1rem;
    vertical-align: middle;
}
#tablaCreditos.trk-operacion-table tbody tr:hover,
#tablaBorradores.trk-borradores-table tbody tr:hover,
#tablaRutas.trk-operacion-table tbody tr:hover {
    background: #f8fafc;
}

/* -- Tabla rutas -- */
#tablaRutas thead th {
    font-size: .8rem;
    vertical-align: middle;
    white-space: nowrap;
}
.trk-rutas-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .85rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
}
.trk-rutas-summary {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
}
.trk-rutas-search {
    flex: 1 1 260px;
    max-width: 360px;
    min-width: 220px;
}
.trk-rutas-filter {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border: 1px solid #d9dee3;
    background: #fff;
    color: #697a8d;
    border-radius: 999px;
    padding: .32rem .72rem;
    font-size: .78rem;
    font-weight: 600;
}
.trk-rutas-filter.active {
    color: #fff;
    border-color: var(--track-color);
    background: var(--track-color);
    box-shadow: 0 .15rem .45rem rgba(13,148,136,.18);
}
.trk-rutas-count {
    min-width: 1.35rem;
    height: 1.1rem;
    padding: 0 .34rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(105,122,141,.12);
    font-size: .66rem;
    font-weight: 700;
}
.trk-rutas-filter.active .trk-rutas-count {
    background: rgba(255,255,255,.24);
}
.trk-rutas-view-toggle .btn {
    width: 2.05rem;
    height: 2.05rem;
}
.trk-rutas-board {
    padding: 1rem;
    background: #f8fafc;
}
.trk-rutas-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: .85rem;
    max-width: 820px;
    margin: 0 auto;
}
.trk-rutas-board-grid .trk-rutas-grid {
    grid-template-columns: repeat(3, minmax(260px, 1fr));
    max-width: none;
}
.trk-ruta-card {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    background: #fff;
    box-shadow: 0 .1rem .55rem rgba(15,23,42,.05);
    overflow: hidden;
}
.trk-rutas-pagination {
    max-width: 820px;
    margin: .85rem auto 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
}
.trk-rutas-board-grid .trk-rutas-pagination {
    max-width: none;
}
.trk-rutas-page-info {
    color: #697a8d;
    font-size: .78rem;
    font-weight: 600;
}
.trk-rutas-page-actions {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
}
.trk-ruta-card-header {
    padding: .85rem .9rem .65rem;
    border-bottom: 1px solid #edf2f7;
}
.trk-ruta-title {
    min-width: 0;
    font-size: .88rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.28;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: normal;
}
.trk-route-folio,
.trk-borrador-chip {
    -webkit-user-select: none;
    user-select: none;
}
.trk-route-folio {
    display: inline-flex;
    align-items: center;
    align-self: flex-start;
    border-radius: 999px;
    padding: .18rem .55rem;
    background: #eef2ff;
    color: #24304f;
    font-size: .68rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: .42rem;
}
.trk-ruta-subtitle {
    margin-top: .28rem;
    color: #697a8d;
    font-size: .72rem;
    line-height: 1.25;
}
.trk-ruta-status {
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-wrap: wrap;
    margin-top: .5rem;
}
.trk-ruta-body {
    padding: .75rem .9rem .85rem;
}
.trk-ruta-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .55rem .75rem;
    margin-bottom: .65rem;
}
.trk-ruta-meta-label {
    display: block;
    color: #8592a3;
    font-size: .66rem;
    font-weight: 700;
    text-transform: uppercase;
}
.trk-ruta-meta-value {
    display: block;
    color: #334155;
    font-size: .78rem;
    font-weight: 600;
    line-height: 1.25;
}
.trk-ruta-progress {
    height: .42rem;
    border-radius: 999px;
    background: #e9ecef;
    overflow: hidden;
}
.trk-ruta-progress > span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #14b8a6, #3b82f6);
}
.trk-ruta-creditos {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    margin-top: .45rem;
    color: #697a8d;
    font-size: .72rem;
}
.trk-ruta-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .35rem;
    padding: .65rem .9rem;
    border-top: 1px solid #edf2f7;
    background: #fbfcfe;
}
.trk-rutas-empty {
    border: 1px dashed #cbd5e1;
    border-radius: .5rem;
    padding: 2.2rem 1rem;
    text-align: center;
    color: #697a8d;
    background: #fff;
}
body.dark-mode .trk-rutas-toolbar,
body.dark-mode .trk-ruta-card,
body.dark-mode .trk-rutas-empty {
    background: #1f2933;
    border-color: #334155;
}
body.dark-mode .trk-rutas-board,
body.dark-mode .trk-ruta-actions {
    background: #17212b;
}
body.dark-mode .trk-ruta-card-header,
body.dark-mode .trk-ruta-actions {
    border-color: #334155;
}
body.dark-mode .trk-ruta-title,
body.dark-mode .trk-ruta-meta-value {
    color: #f8fafc;
}
body.dark-mode .trk-route-folio {
    background: #24304f;
    color: #dbeafe;
}
body.dark-mode .trk-ruta-subtitle,
body.dark-mode .trk-ruta-meta-label,
body.dark-mode .trk-ruta-creditos,
body.dark-mode .trk-rutas-empty {
    color: #cbd5e1;
}
body.dark-mode .trk-rutas-filter {
    background: #17212b;
    border-color: #334155;
    color: #cbd5e1;
}
body.dark-mode #tabBorradores .card-header {
    background: #1f2933 !important;
    border-color: #334155;
}
#tablaBorradores_wrapper .dataTables_length,
#tablaBorradores_wrapper .dt-length {
    display: none !important;
}
#tablaBorradores_wrapper .dataTables_paginate,
#tablaBorradores_wrapper .dt-paging {
    display: flex;
    justify-content: flex-end;
}
#tablaBorradores_wrapper .pagination {
    justify-content: flex-end;
    margin-left: auto;
}
.trk-table-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem .9rem;
}
.trk-table-toolbar .trk-table-length,
.trk-table-toolbar .trk-table-search {
    display: flex;
    align-items: center;
    gap: .55rem;
}
.trk-table-toolbar .trk-table-search {
    margin-left: auto;
}
.trk-table-toolbar .form-select {
    width: 72px;
}
.trk-table-toolbar .form-control {
    width: min(280px, 32vw);
}
#tablaBorradores.trk-borradores-table,
#tablaRutas.trk-operacion-table {
    border-color: transparent;
    table-layout: fixed;
}
#tablaBorradores.trk-borradores-table thead th,
#tablaRutas.trk-operacion-table thead th {
    border: 0;
    border-bottom: 1px solid #eef2f7;
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: .9rem 1rem;
    white-space: nowrap;
}
#tablaBorradores.trk-borradores-table tbody td,
#tablaRutas.trk-operacion-table tbody td {
    border-left: 0;
    border-right: 0;
    border-top: 0;
    border-bottom: 1px solid #eef2f7;
    padding: .85rem 1rem;
    vertical-align: middle;
}
#tablaBorradores.trk-borradores-table tbody tr:hover,
#tablaRutas.trk-operacion-table tbody tr:hover {
    background: #f8fafc;
}
.trk-borrador-cell {
    display: flex;
    flex-direction: column;
    gap: .28rem;
    min-width: 0;
}
.trk-borrador-chip {
    align-self: flex-start;
    border-radius: 999px;
    padding: .18rem .55rem;
    font-size: .7rem;
    font-weight: 800;
    line-height: 1.1;
}
.trk-borrador-chip-warning {
    background: #fff7ed;
    color: #c2410c;
}
.trk-borrador-chip-success {
    background: #dcfce7;
    color: #15803d;
}
.trk-borrador-chip-info {
    background: #dbeafe;
    color: #1d4ed8;
}
.trk-borrador-main {
    color: #64748b;
    font-size: .9rem;
    font-weight: 800;
    line-height: 1.15;
    word-break: break-word;
}
.trk-borrador-sub {
    color: #64748b;
    font-size: .74rem;
    font-weight: 700;
    line-height: 1.15;
    word-break: break-word;
}
.trk-borrador-muted {
    color: #94a3b8;
    font-size: .7rem;
    font-weight: 700;
    line-height: 1.1;
}
.trk-borrador-divider {
    border-top: 1px solid #dbe4f0;
    margin-top: .12rem;
    padding-top: .32rem;
}
body.dark-mode #tablaCreditos.trk-operacion-table thead th,
body.dark-mode #tablaCreditos.trk-operacion-table tbody td,
body.dark-mode #tablaBorradores.trk-borradores-table thead th,
body.dark-mode #tablaBorradores.trk-borradores-table tbody td,
body.dark-mode #tablaRutas.trk-operacion-table thead th,
body.dark-mode #tablaRutas.trk-operacion-table tbody td {
    border-bottom-color: #334155;
}
body.dark-mode #tablaCreditos.trk-operacion-table tbody tr:hover,
body.dark-mode #tablaBorradores.trk-borradores-table tbody tr:hover,
body.dark-mode #tablaRutas.trk-operacion-table tbody tr:hover {
    background: #17212b;
}
body.dark-mode .trk-borrador-main,
body.dark-mode .trk-borrador-sub {
    color: #cbd5e1;
}
body.dark-mode .trk-borrador-muted {
    color: #94a3b8;
}
@media (max-width: 767.98px) {
    .trk-table-toolbar { align-items: stretch; flex-direction: column; }
    .trk-table-toolbar .trk-table-search { margin-left: 0; }
    .trk-table-toolbar .form-control { width: 100%; }
    .trk-rutas-toolbar { align-items: stretch; flex-direction: column; }
    .trk-rutas-search { max-width: none; min-width: 0; }
    .trk-rutas-summary { gap: .4rem; }
    .trk-rutas-filter { flex: 1 1 auto; justify-content: center; }
    .trk-rutas-grid { grid-template-columns: 1fr; }
}
@media (min-width: 768px) and (max-width: 1199.98px) {
    .trk-rutas-board-grid .trk-rutas-grid { grid-template-columns: repeat(2, minmax(260px, 1fr)); }
}
@media (max-width: 767.98px) {
    .trk-rutas-board-grid .trk-rutas-grid { grid-template-columns: 1fr; }
}
#tablaAgenciasTracking thead th,
#tablaTransportistasTracking thead th {
    font-size: .78rem;
    vertical-align: middle;
    white-space: nowrap;
}
.trk-catalog-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.trk-catalog-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #d7f4ef;
    border-radius: .65rem;
    background: #f0fdfa;
}
.trk-catalog-eyebrow {
    color: var(--track-color);
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .03em;
    text-transform: uppercase;
}
.trk-catalog-actions,
.trk-catalog-toolbar {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-wrap: wrap;
}
.trk-catalog-toolbar {
    justify-content: space-between;
    padding: .75rem;
    border: 1px solid #e2e8f0;
    border-radius: .65rem;
    background: #fff;
}
.trk-catalog-search {
    max-width: 480px;
}
.trk-catalog-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: .8rem;
}
.trk-catalog-card,
.trk-catalog-group {
    border: 1px solid #e2e8f0;
    border-radius: .65rem;
    background: #fff;
    box-shadow: 0 .25rem .75rem rgba(15, 23, 42, .04);
}
.trk-catalog-card {
    padding: .85rem;
    display: flex;
    flex-direction: column;
    gap: .55rem;
}
.trk-catalog-card-title {
    color: #24304f;
    font-size: .92rem;
    font-weight: 800;
    line-height: 1.15;
    text-transform: uppercase;
    word-break: break-word;
}
.trk-catalog-card-sub {
    color: #64748b;
    font-size: .76rem;
    font-weight: 700;
    line-height: 1.2;
}
.trk-catalog-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
}
.trk-catalog-card-actions {
    display: flex;
    justify-content: flex-end;
    gap: .35rem;
    padding-top: .45rem;
    border-top: 1px solid #e2e8f0;
}
.trk-catalog-group {
    margin-bottom: .85rem;
    overflow: hidden;
}
.trk-catalog-group-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .85rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.trk-catalog-group-title {
    color: #24304f;
    font-size: 1.05rem;
    font-weight: 900;
    line-height: 1.1;
    text-transform: uppercase;
}
.trk-catalog-group-body {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: .7rem;
    padding: .8rem;
}
.trk-catalog-empty {
    border: 1px dashed #cbd5e1;
    border-radius: .65rem;
    padding: 2rem 1rem;
    text-align: center;
    color: #64748b;
    background: #fff;
}
body.dark-mode .trk-catalog-hero {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-catalog-toolbar,
body.dark-mode .trk-catalog-card,
body.dark-mode .trk-catalog-group,
body.dark-mode .trk-catalog-empty {
    background: #1f2933;
    border-color: #334155;
}
body.dark-mode .trk-catalog-group-head {
    background: #17212b;
    border-color: #334155;
}
body.dark-mode .trk-catalog-card-title,
body.dark-mode .trk-catalog-group-title {
    color: #f8fafc;
}
body.dark-mode .trk-catalog-card-sub {
    color: #cbd5e1;
}
@media (max-width: 767.98px) {
    .trk-catalog-hero,
    .trk-catalog-toolbar {
        align-items: stretch;
        flex-direction: column;
    }
    .trk-catalog-actions .btn,
    .trk-catalog-search {
        width: 100%;
        max-width: none;
    }
}
.trk-action-btn {
    width: 2rem;
    height: 2rem;
    position: relative;
}
.trk-action-btn i {
    font-size: .86rem;
    line-height: 1;
}
.trk-route-chat-badge {
    position: absolute;
    top: -.38rem;
    right: -.38rem;
    min-width: 1rem;
    height: 1rem;
    padding: 0 .24rem;
    border-radius: 999px;
    background: #ff3e1d;
    color: #fff;
    border: 2px solid #fff;
    font-size: .58rem;
    font-weight: 700;
    line-height: .82rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 .12rem .32rem rgba(255,62,29,.35);
}
body.dark-mode .trk-route-chat-badge { border-color: #172121; }
.trk-location-badges {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    flex-wrap: wrap;
}
.trk-loc-badge {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    border-radius: 999px;
    padding: .16rem .48rem;
    font-size: .68rem;
    font-weight: 700;
    line-height: 1.15;
    white-space: nowrap;
}
.trk-loc-estado {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
}
.trk-loc-municipio {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
body.dark-mode .trk-loc-estado {
    background: #3a2418;
    color: #fdba74;
    border-color: #9a3412;
}
body.dark-mode .trk-loc-municipio {
    background: #172554;
    color: #93c5fd;
    border-color: #1d4ed8;
}
.trk-credit-address {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    max-width: 100%;
    color: #64748b;
    font-size: .72rem;
    line-height: 1.2;
}
.trk-credit-address span {
    display: inline-block;
    max-width: min(620px, 100%);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: bottom;
}
body.dark-mode .trk-credit-address { color: #94a3b8; }
.trk-catalog-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
    margin-bottom: 1rem;
}
.trk-catalog-stat {
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .5rem;
    padding: .85rem 1rem;
}
.trk-catalog-stat .stat-label {
    color: #64748b;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
}
.trk-catalog-stat .stat-value {
    color: #0f172a;
    font-size: 1.45rem;
    font-weight: 800;
    line-height: 1;
    margin-top: .35rem;
}
.trk-transport-panel {
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .5rem;
    padding: .85rem;
}
.trk-transport-info {
    border: 1px dashed #bae6fd;
    background: #f0f9ff;
    border-radius: .45rem;
    padding: .55rem .7rem;
    font-size: .78rem;
}
.trk-cedis-info-card {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
}
.trk-cedis-info-body {
    min-width: 0;
    flex: 1 1 auto;
}
.trk-cedis-map-btn {
    flex: 0 0 auto;
    min-width: 86px;
    height: 34px;
    border-radius: .45rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    font-weight: 700;
    font-size: .75rem;
    color: #1d4ed8;
    background: #fff;
    border: 1px solid #bfdbfe;
    box-shadow: 0 .2rem .45rem rgba(37, 99, 235, .12);
    text-decoration: none;
}
.trk-cedis-map-btn:hover {
    color: #fff;
    background: #2563eb;
    border-color: #2563eb;
    text-decoration: none;
}
.trk-cedis-map-btn .maps-pin {
    color: #ef4444;
    font-size: .95rem;
}
.trk-cedis-map-btn:hover .maps-pin { color: #fff; }
.trk-transport-picker { position: relative; }
.trk-transport-results {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + .25rem);
    z-index: 20;
    max-height: 240px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: .45rem;
    box-shadow: 0 .5rem 1.25rem rgba(15,23,42,.16);
    padding: .25rem;
}
.trk-transport-option {
    width: 100%;
    border: 0;
    background: transparent;
    display: flex;
    align-items: center;
    gap: .45rem;
    text-align: left;
    padding: .45rem .55rem;
    border-radius: .35rem;
    color: #334155;
    font-size: .78rem;
}
.trk-transport-option:hover,
.trk-transport-option.active {
    background: #f0fdfa;
}
.trk-transport-option .name {
    font-weight: 700;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.trk-transport-option .empresa {
    color: #64748b;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-left: auto;
}
.trk-transport-empty {
    padding: .65rem .75rem;
    color: #64748b;
    font-size: .78rem;
}
.trk-quick-credit-summary {
    border: 1px dashed #b6e7e2;
    background: #f0fdfa;
    border-radius: .5rem;
    padding: .75rem;
}
.trk-quick-routes-list {
    display: flex;
    flex-direction: column;
    gap: .55rem;
}
.trk-quick-route {
    border: 1px solid #e2e8f0;
    border-radius: .55rem;
    background: #fff;
    padding: .75rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: .75rem;
    align-items: center;
}
.trk-quick-route-title {
    color: #23304d;
    font-weight: 800;
    line-height: 1.15;
    overflow-wrap: anywhere;
    text-transform: uppercase;
}
.trk-quick-route-sub {
    color: #64748b;
    font-size: .76rem;
    margin-top: .2rem;
}
body.dark-mode .trk-catalog-stat,
body.dark-mode .trk-transport-panel {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-catalog-stat .stat-label { color: #94a3b8; }
body.dark-mode .trk-catalog-stat .stat-value { color: #e2e8f0; }
body.dark-mode .trk-transport-info {
    background: #1e2d2c;
    border-color: #2d4444;
    color: #e2e8f0;
}
body.dark-mode .trk-cedis-map-btn {
    background: #142222;
    border-color: #315f8a;
    color: #93c5fd;
}
body.dark-mode .trk-cedis-map-btn:hover {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
body.dark-mode .trk-transport-results {
    background: #172121;
    border-color: #2d4444;
    box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.35);
}
body.dark-mode .trk-transport-option { color: #e2e8f0; }
body.dark-mode .trk-transport-option:hover,
body.dark-mode .trk-transport-option.active { background: #1e2d2c; }
body.dark-mode .trk-transport-option .empresa,
body.dark-mode .trk-transport-empty { color: #94a3b8; }
body.dark-mode .trk-quick-credit-summary {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-quick-route {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-quick-route-title { color: #e2e8f0; }
body.dark-mode .trk-quick-route-sub { color: #94a3b8; }
body.dark-mode #tabCatalogosTracking .card-header {
    background: #172121 !important;
    color: #e2e8f0;
    border-color: #2d4444;
}
@media (max-width: 991.98px) {
    .trk-catalog-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .trk-catalog-grid { grid-template-columns: 1fr; }
}

/* -- Badges de estatus de confirmacion gestor -- */
.badge-conf-pendiente   { background: #fbbf24; color: #000; }
.badge-conf-confirmado  { background: #22c55e; color: #fff; }
.badge-conf-rechazado   { background: #ef4444; color: #fff; }
.badge-conf-en_revision { background: #60a5fa; color: #fff; }

/* -- Badges estatus ruta -- */
.badge-ruta-borrador              { background: #94a3b8; color: #fff; }
.badge-ruta-pendiente_confirmacion{ background: #f59e0b; color: #000; }
.badge-ruta-lista_envio           { background: #3b82f6; color: #fff; }
.badge-ruta-enviada               { background: #8b5cf6; color: #fff; }
.badge-ruta-en_proceso            { background: #0d9488; color: #fff; }
.badge-ruta-concluida             { background: #22c55e; color: #fff; }
.badge-ruta-cancelada             { background: #ef4444; color: #fff; }
/* -- Badges estatus tracking API (servicio externo) -- */
.badge-trk-pendiente  { background: #94a3b8; color: #fff; }
.badge-trk-en_proceso { background: #0d9488; color: #fff; }
.badge-trk-completado { background: #16a34a; color: #fff; }
.badge-trk-confirmado { background: #0284c7; color: #fff; }
.badge-trk-cancelado  { background: #ef4444; color: #fff; }

/* -- Modal -- */
#modalRegistrarRuta .modal-header {
    background: var(--track-color);
    color: #fff;
    border-radius: .375rem .375rem 0 0;
}
#modalRegistrarRuta .modal-header .btn-close { filter: invert(1); }

/* -- Tabs del modal -- */
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

/* -- Vista Planeacion: homologacion estilo Evidencias -- */
.tracking-planeacion-view {
    --track-color:       #2f4f9e;
    --track-color-dark:  #24437f;
    --track-bg-card:     #ffffff;
    --track-border:      #dbe4f0;
}
.tracking-planeacion-view .track-header {
    background: #244f8f;
    border: 0;
    color: #fff;
    border-radius: .75rem;
    padding: 1.35rem 1.55rem;
    margin-bottom: 1.15rem;
    box-shadow: none;
}
.tracking-planeacion-view .track-header h4 {
    color: #fff;
    font-size: 1.2rem;
    letter-spacing: 0;
}
.tracking-planeacion-view .track-header .track-subtitle {
    color: #e8eefc;
    opacity: 1;
    font-weight: 600;
}
.tracking-planeacion-view .track-header #btnNuevaRuta {
    background: #1f2d4d;
    border-color: #1f2d4d;
    color: #fff;
    border-radius: .65rem;
    min-width: 132px;
    box-shadow: 0 .25rem .55rem rgba(15, 23, 42, .24);
}
.tracking-planeacion-view .track-header #btnNuevaRuta:hover {
    background: #17233d;
    border-color: #17233d;
}
.trk-planeacion-shell {
    background: #fff;
    border: 1px solid #e5eaf2;
    border-radius: .75rem;
    padding: 1.2rem;
    box-shadow: 0 .12rem .65rem rgba(15, 23, 42, .04);
}
.tracking-planeacion-view #trackMainTabs {
    border-bottom: 0;
    gap: .75rem;
    margin-bottom: 1.15rem !important;
}
.tracking-planeacion-view #trackMainTabs .nav-item {
    margin-bottom: 0;
}
.tracking-planeacion-view .track-tabs .nav-link,
.tracking-planeacion-view .track-tabs .nav-link:link,
.tracking-planeacion-view .track-tabs .nav-link:visited,
.tracking-planeacion-view .track-tabs .nav-link:hover,
.tracking-planeacion-view .track-tabs .nav-link:focus {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border: 0 !important;
    border-radius: .55rem !important;
    background: transparent;
    color: #4b5563 !important;
    font-weight: 700;
    padding: .55rem 1rem;
    min-height: 2.45rem;
}
.tracking-planeacion-view .track-tabs .nav-link.active {
    background: #2f4f9e !important;
    color: #fff !important;
    box-shadow: 0 .22rem .55rem rgba(47, 79, 158, .22);
}
.tracking-planeacion-view .track-tabs .nav-link .badge {
    background: #d8f8c9 !important;
    color: #228a15 !important;
    min-width: 1.45rem;
}
.tracking-planeacion-view .track-tabs .nav-link.active .badge {
    background: #eef2ff !important;
    color: #24437f !important;
}
.tracking-planeacion-view .tab-content {
    border: 1px solid #dbe4f0;
    border-radius: .6rem;
    background: #fff;
    padding: 1.15rem 1.15rem 1rem;
}
.tracking-planeacion-view .track-filters {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: .55rem;
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
}
.tracking-planeacion-view .track-filters .form-label {
    color: #526477;
}
.tracking-planeacion-view .track-filters .btn:not(.btn-outline-secondary) {
    background: #2f4f9e !important;
    color: #fff !important;
    border-color: #2f4f9e !important;
}
.tracking-planeacion-view .track-filters .btn-outline-secondary {
    color: #d08718;
    border-color: #d99a27;
    background: #fff;
}
.tracking-planeacion-view #tabCreditos > .card,
.tracking-planeacion-view #tabBorradores > .card {
    border: 0 !important;
    box-shadow: none !important;
    background: transparent;
}
.tracking-planeacion-view #tabCreditos .card-body,
.tracking-planeacion-view #tabBorradores .card-body {
    background: transparent;
}
.tracking-planeacion-view .dataTables_wrapper .row:first-child,
.tracking-planeacion-view .dataTables_wrapper .dt-layout-row:first-child {
    align-items: center;
    padding: .9rem 0 1.35rem;
}
.tracking-planeacion-view .dataTables_wrapper .dataTables_length,
.tracking-planeacion-view .dataTables_wrapper .dataTables_filter,
.tracking-planeacion-view .dataTables_wrapper .dt-length,
.tracking-planeacion-view .dataTables_wrapper .dt-search {
    color: #374151;
    font-size: .85rem;
}
.tracking-planeacion-view .dataTables_wrapper .dataTables_filter input,
.tracking-planeacion-view .dataTables_wrapper .dt-search input,
.tracking-planeacion-view .trk-table-toolbar .form-control {
    border-radius: .55rem;
    border-color: #cfd8e3;
}
.tracking-planeacion-view .trk-table-toolbar {
    padding: .75rem 0 1.25rem;
}
.tracking-planeacion-view #tablaCreditos.trk-operacion-table thead th,
.tracking-planeacion-view #tablaBorradores.trk-borradores-table thead th {
    border-bottom-color: #dbe4f0;
    color: #62738d;
    padding-top: 1.05rem;
    padding-bottom: 1.05rem;
}
.tracking-planeacion-view #tablaCreditos.trk-operacion-table tbody td,
.tracking-planeacion-view #tablaBorradores.trk-borradores-table tbody td {
    border-bottom-color: #edf2f7;
}
body.dark-mode .tracking-planeacion-view {
    --track-bg-card: #17212b;
    --track-border:  #334155;
}
body.dark-mode .tracking-planeacion-view .track-header {
    background: #1f3b73;
}
body.dark-mode .trk-planeacion-shell,
body.dark-mode .tracking-planeacion-view .tab-content {
    background: #17212b;
    border-color: #334155;
}
body.dark-mode .tracking-planeacion-view .track-filters {
    background: #111827;
    border-color: #334155;
}
body.dark-mode .tracking-planeacion-view .track-tabs .nav-link,
body.dark-mode .tracking-planeacion-view .track-tabs .nav-link:link,
body.dark-mode .tracking-planeacion-view .track-tabs .nav-link:visited,
body.dark-mode .tracking-planeacion-view .track-tabs .nav-link:hover,
body.dark-mode .tracking-planeacion-view .track-tabs .nav-link:focus {
    color: #cbd5e1 !important;
}
body.dark-mode .tracking-planeacion-view .track-tabs .nav-link.active {
    color: #fff !important;
}

/* -- Lista de creditos en modal (sortable) -- */
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
.track-credito-row.trk-row-focused {
    border-color: var(--track-color);
    box-shadow: 0 0 0 .16rem rgba(13,148,136,.18);
}
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
.trk-moto-model-pill {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    color: #475569;
}
.trk-moto-model-pill i {
    color: var(--track-color);
    font-size: .78rem;
}
body.dark-mode .trk-moto-model-pill {
    color: #cbd5e1;
}
.trk-planner-panel {
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .55rem;
    padding: .75rem;
}
.trk-planner-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .45rem;
}
.trk-planner-kpi {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .45rem;
    padding: .45rem .55rem;
}
.trk-planner-kpi span {
    display: block;
    color: #64748b;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
}
.trk-planner-kpi b {
    color: #1e293b;
    font-size: 1rem;
}
.trk-planner-group {
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: .5rem;
    overflow: hidden;
}
.trk-planner-group + .trk-planner-group { margin-top: .5rem; }
.trk-planner-group-head,
.trk-planner-mun {
    width: 100%;
    border: 0;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    text-align: left;
}
.trk-planner-group-head {
    padding: .55rem .65rem;
    color: #172554;
    font-weight: 800;
}
.trk-planner-mun {
    padding: .42rem .65rem .42rem 1.25rem;
    color: #334155;
    border-top: 1px solid #eef2f7;
    font-size: .78rem;
}
.trk-planner-group-head:hover,
.trk-planner-mun:hover { background: #eff6ff; }
.trk-planner-counts {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}
.trk-planner-counts .badge { font-size: .62rem; }
.trk-route-list-tools {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(180px, 1.25fr);
    gap: .45rem;
    margin-bottom: .5rem;
}
.trk-route-list-tools .form-select,
.trk-route-list-tools .form-control {
    font-size: .75rem;
}
#rutaCreditosList {
    max-height: 420px !important;
}
#modalRegistrarRuta.trk-planner-active .modal-dialog {
    max-width: calc(100vw - 1.5rem);
    margin: .75rem auto;
}
#modalRegistrarRuta.trk-planner-active .modal-content {
    min-height: calc(100vh - 1.5rem);
}
#modalRegistrarRuta.trk-planner-active .modal-body {
    display: grid;
    grid-template-columns: minmax(430px, 520px) minmax(520px, 1fr);
    align-items: start;
    gap: .85rem;
}
#modalRegistrarRuta.trk-planner-active #rutaCancelacionInfo,
#modalRegistrarRuta.trk-planner-active #trkRouteHeaderSection,
#modalRegistrarRuta.trk-planner-active #secTransportistaRuta,
#modalRegistrarRuta.trk-planner-active #trkPlannerPanel,
#modalRegistrarRuta.trk-planner-active #secAgregarCredito,
#modalRegistrarRuta.trk-planner-active #trkRouteListSection,
#modalRegistrarRuta.trk-planner-active #trkTrackingSection {
    grid-column: 1;
}
#modalRegistrarRuta.trk-planner-active #trkMapSection {
    grid-column: 2;
    grid-row: 2 / span 6;
    position: relative;
    margin-top: .35rem;
}
#modalRegistrarRuta.trk-planner-active #trackMapContainer,
#modalRegistrarRuta.trk-planner-active #trackMap {
    height: clamp(420px, 58vh, 560px);
    min-height: 420px;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList {
    max-height: 520px !important;
}
body.dark-mode .trk-planner-panel,
body.dark-mode .trk-planner-kpi,
body.dark-mode .trk-planner-group {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-planner-kpi b,
body.dark-mode .trk-planner-group-head { color: #e2e8f0; }
body.dark-mode .trk-planner-mun { color: #cbd5e1; border-top-color: #2d4444; }
body.dark-mode .trk-planner-group-head:hover,
body.dark-mode .trk-planner-mun:hover { background: #1e2d2c; }
@media (max-width: 991.98px) {
    #modalRegistrarRuta.trk-planner-active .modal-body {
        display: block;
    }
    #modalRegistrarRuta.trk-planner-active #trkMapSection {
        position: static;
    }
    #modalRegistrarRuta.trk-planner-active #trackMapContainer,
    #modalRegistrarRuta.trk-planner-active #trackMap {
        height: 520px;
        min-height: 520px;
    }
}
@media (max-width: 575.98px) {
    .trk-route-list-tools {
        grid-template-columns: 1fr;
    }
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

/* -- Mapa -- */
#trackMapContainer {
    width: 100%;
    height: 475px;
    border-radius: .5rem;
    border: 2px solid var(--track-border);
    overflow: hidden;
    background: #e5e7eb;
    position: relative;
}
#trackMap { width: 100%; height: 100%; min-height: 475px; background: #e5e7eb; }
body.dark-mode #trackMapContainer {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode #trackMap {
    background: #172121;
}
body.dark-mode #trackMap .gm-control-active,
body.dark-mode #mapPickerContainer .gm-control-active,
body.dark-mode #trackMap .gmnoprint > div,
body.dark-mode #mapPickerContainer .gmnoprint > div {
    background-color: #263636 !important;
    color: #f8fafc !important;
    border-color: #3b5555 !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.35) !important;
}
body.dark-mode #trackMap .gm-control-active img,
body.dark-mode #mapPickerContainer .gm-control-active img {
    filter: invert(1) grayscale(1) brightness(1.8);
}
body.dark-mode #trackMap .gm-style-cc,
body.dark-mode #mapPickerContainer .gm-style-cc {
    filter: invert(.9) hue-rotate(180deg);
}
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
.trk-live-map-card {
    position: absolute;
    left: .75rem;
    bottom: .75rem;
    z-index: 4;
    max-width: min(360px, calc(100% - 1.5rem));
    background: rgba(255,255,255,.94);
    border: 1px solid #dbeafe;
    border-radius: .5rem;
    box-shadow: 0 .35rem 1rem rgba(15,23,42,.14);
    padding: .55rem .7rem;
    font-size: .76rem;
    color: #334155;
}
.trk-live-map-card .live-title {
    display: flex;
    align-items: center;
    gap: .35rem;
    font-weight: 700;
    color: #0f766e;
}
.trk-live-map-card .live-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem .7rem;
    margin-top: .28rem;
    color: #64748b;
}
body.dark-mode .trk-live-map-card {
    background: rgba(30,45,44,.94);
    border-color: #2d4444;
    color: #e2e8f0;
}
body.dark-mode .trk-live-map-card .live-meta { color: #b0cece; }

/* -- Tracking timeline (Mercado Libre style) -- */
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
.trk-badge-recolectada { background: #dcfce7; color: #15803d; }
.trk-badge-completado  { background: #dcfce7; color: #15803d; } /* alias */
.trk-badge-incidencia  { background: #fee2e2; color: #b91c1c; }
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

/* -- Resumen del modal -- */
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

/* -- Dark mode: modales + formularios -- */
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

/* -- Select2 / chosen override -- */
.select2-container {
    width: 100% !important;
}
.input-group .select2-container {
    flex: 1 1 auto;
    width: 1% !important;
}
.select2-container .select2-selection--single {
    min-height: 31px;
    border-color: #ced4da !important;
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 29px;
    padding-left: .75rem;
    padding-right: 1.75rem;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 29px;
}
.select2-container .select2-selection--multiple {
    min-height: 38px;
    border-color: #ced4da !important;
}
body.dark-mode .select2-container--default .select2-selection--multiple {
    background-color: #1e2d2c !important;
    border-color: #2d4444 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .select2-container--default .select2-selection--single {
    background-color: #1e2d2c !important;
    border-color: #2d4444 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #e2e8f0 !important;
}
body.dark-mode .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #6b8080 !important;
}
body.dark-mode .select2-container--default.select2-container--disabled .select2-selection--single {
    background-color: #111a1a !important;
    border-color: #1e2d2c !important;
}
body.dark-mode .select2-container--default.select2-container--disabled .select2-selection--single .select2-selection__rendered {
    color: #52686a !important;
}
body.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #2d4444 !important;
    border-color: #456262 !important;
    color: #f8fafc !important;
}
body.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
    color: #f8fafc !important;
}
body.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #cbd5e1 !important;
    border-right-color: #456262 !important;
}
body.dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    background-color: #3b5555 !important;
    color: #fff !important;
}
body.dark-mode .select2-container--default .select2-search--inline .select2-search__field {
    color: #e2e8f0 !important;
}
body.dark-mode .select2-dropdown {
    background-color: #1e2d2c !important;
    border-color: #2d4444 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .select2-search--dropdown .select2-search__field {
    background-color: #162323 !important;
    border-color: #2d4444 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .select2-results__option--selected {
    background-color: #2d4444 !important;
}
body.dark-mode .select2-results__option--highlighted {
    background-color: var(--track-color) !important;
    color: #06201d !important;
}

/* -- Boton pin ubicacion en fila de credito -- */
.select2-results__option[aria-disabled="true"] {
    color: #94a3b8 !important;
}
body.dark-mode .select2-results__option[aria-disabled="true"] {
    color: #64748b !important;
    background-color: #172121 !important;
}

.btn-pin-ubicacion {
    flex-shrink: 0;
}
.btn-pin-ubicacion.tiene-pin {
    color: var(--track-color-dark);
    border-color: var(--track-color);
    background: var(--track-color-light);
}
.btn-pin-ubicacion.pin-default-blink {
    color: #0d9488;
    border-color: #0d9488;
    animation: trkPinBlink 1.05s ease-in-out infinite;
}
@keyframes trkPinBlink {
    0%, 100% { box-shadow: 0 0 0 0 rgba(13,148,136,.42); }
    50% { box-shadow: 0 0 0 .22rem rgba(13,148,136,.08); }
}

/* ========================================================
   Chat Operativo  -  Offcanvas lateral
======================================================== */
#modalChatOperativo .modal-dialog {
    max-width: min(1540px, calc(100vw - 1.5rem));
    transition: max-width .28s ease, width .28s ease;
}
#modalChatOperativo.chat-map-collapsed-modal .modal-dialog {
    max-width: min(790px, calc(100vw - 1.5rem));
}
#modalChatOperativo .modal-content {
    height: min(860px, calc(100vh - 1.5rem));
    overflow: hidden;
    border: 0;
    border-radius: .65rem;
}
body.dark-mode #modalChatOperativo .modal-content {
    background: #161f1f;
    color: #e2e8f0;
    border-color: var(--track-border);
}
.chat-modal-header {
    background: #fff;
    color: #1f2937;
    flex-shrink: 0;
    border-bottom: 1px solid #e5e7eb;
}
.chat-header-icon {
    width: 2.25rem;
    height: 2.25rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: .5rem;
    background: var(--track-color-light);
    color: var(--track-color-dark);
}
.chat-route-name {
    font-size: .76rem;
    display: block;
    white-space: normal;
    overflow-wrap: anywhere;
    max-width: min(620px, 70vw);
    opacity: .82;
    line-height: 1.25;
    color: #697a8d;
}
body.dark-mode .chat-modal-header {
    background: #1e2d2c;
    color: #f8fafc;
    border-bottom-color: #2d4444;
}
body.dark-mode .chat-route-name {
    color: #b0cece;
}
.chat-operativo-layout {
    display: grid;
    grid-template-columns: minmax(420px, 1fr) minmax(420px, 1fr);
    min-height: 0;
    height: 100%;
    position: relative;
    transition: grid-template-columns .28s ease;
}
.chat-operativo-layout.chat-map-collapsed {
    grid-template-columns: minmax(0, 1fr) 0;
}
.chat-operativo-layout.chat-map-collapsed .chat-conversation-panel {
    border-right: 0;
}
.chat-operativo-layout.chat-map-collapsed .chat-live-panel {
    overflow: hidden;
    min-width: 0;
    width: 0;
    opacity: 0;
    pointer-events: none;
}
.chat-map-toggle {
    width: 2rem;
    height: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    flex-shrink: 0;
    box-shadow: 0 .2rem .6rem rgba(15,23,42,.18);
}
.chat-map-toggle-edge {
    position: absolute;
    top: .85rem;
    left: calc(50% - 1rem);
    z-index: 10;
    transition: left .28s ease, right .28s ease, transform .28s ease;
}
.chat-operativo-layout.chat-map-collapsed .chat-map-toggle-edge {
    left: auto;
    right: .85rem;
}
.chat-conversation-panel {
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--track-border);
}
.chat-live-panel {
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    background: #f8fafc;
}
body.dark-mode .chat-conversation-panel { border-right-color: #2d4444; }
body.dark-mode .chat-live-panel { background: #101818; }
.chat-live-header {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .75rem .9rem;
    border-bottom: 1px solid var(--track-border);
    background: #fff;
}
body.dark-mode .chat-live-header {
    background: #1e2d2c;
    border-bottom-color: #2d4444;
}
.chat-live-title {
    font-size: .85rem;
    font-weight: 700;
    color: #1f2937;
}
.chat-live-subtitle {
    display: block;
    color: #697a8d;
    font-size: .72rem;
    line-height: 1.2;
}
body.dark-mode .chat-live-title { color: #e2e8f0; }
body.dark-mode .chat-live-subtitle { color: #b0cece; }
.chat-live-map-wrap {
    position: relative;
    min-height: 0;
    flex: 1 1 auto;
    overflow: hidden;
}
#chatLiveMap {
    width: 100%;
    height: 100%;
    min-height: 520px;
    background: #e5e7eb;
}
body.dark-mode #chatLiveMap { background: #101818; }
.chat-live-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    color: #94a3b8;
    text-align: center;
    padding: 1rem;
    background: #f8fafc;
    z-index: 2;
}
body.dark-mode .chat-live-placeholder { background: #101818; color: #b0cece; }
.chat-live-info-card {
    position: absolute;
    left: .85rem;
    right: .85rem;
    bottom: .85rem;
    z-index: 3;
    background: rgba(255,255,255,.94);
    border: 1px solid rgba(148,163,184,.35);
    border-radius: .65rem;
    padding: .65rem .75rem;
    box-shadow: 0 .4rem 1rem rgba(15,23,42,.14);
    backdrop-filter: blur(8px);
}
body.dark-mode .chat-live-info-card {
    background: rgba(30,45,44,.94);
    border-color: #2d4444;
}
.chat-live-info-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .45rem;
    font-size: .72rem;
    color: #526070;
}
body.dark-mode .chat-live-info-grid { color: #b0cece; }
.chat-live-info-grid span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Tabs de id_detalle */
.chat-tabs-wrap {
    border-bottom: 1px solid var(--track-border);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: .75rem;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: thin;
}
.chat-tabs-wrap::-webkit-scrollbar { height: 4px; }
.chat-tabs-wrap::-webkit-scrollbar-thumb { background: var(--track-border); border-radius: 2px; }
.chat-tabs-wrap ul { flex: 0 0 auto; flex-wrap: nowrap; padding: .55rem 0 .55rem .65rem; gap: .35rem; border-bottom: none; }
.chat-connection-status {
    flex: 1 1 auto;
    min-width: 160px;
    color: #697a8d;
    font-size: .76rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.chat-connection-status.is-active {
    color: #16a34a;
}
body.dark-mode .chat-connection-status {
    color: #b0cece;
}
body.dark-mode .chat-connection-status.is-active {
    color: #4ade80;
}
.chat-tab-link {
    font-size: .77rem;
    padding: .38rem .68rem;
    border-radius: 999px;
    color: var(--track-color-dark) !important;
    border: 1px solid #d5f3ed;
    white-space: nowrap;
    position: relative;
    background: #f0fdfa;
    cursor: pointer;
}
.chat-tab-link:hover { background: #ccfbf1; }
.chat-tab-link.active {
    background: var(--track-color) !important;
    color: #fff !important;
    border-color: var(--track-color) !important;
}
body.dark-mode .chat-tab-link       { color: var(--track-color) !important; }
body.dark-mode .chat-tab-link:hover { background: var(--track-color-light); }
body.dark-mode .chat-tab-link {
    background: #172726;
    border-color: #244644;
}

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

/* Badge de mensajes no leidos */
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

/* Indicador WS en linea */
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
.chat-detail-context {
    flex-shrink: 0;
    display: grid;
    gap: .42rem;
    padding: .62rem .78rem;
    border-bottom: 1px solid #c7f5ef;
    background: #f0fdfa;
    color: #334155;
    font-size: .76rem;
}
.chat-detail-context-main {
    display: flex;
    align-items: center;
    gap: .45rem;
    min-width: 0;
    flex-wrap: wrap;
}
.chat-detail-context-title {
    color: #0f172a;
    font-weight: 800;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.chat-detail-context-chip {
    display: inline-flex;
    align-items: center;
    gap: .24rem;
    border-radius: 999px;
    padding: .12rem .42rem;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: .66rem;
    font-weight: 800;
    white-space: nowrap;
}
.chat-detail-context-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .32rem .8rem;
}
.chat-detail-context-item {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.chat-detail-context-item b {
    color: #0f766e;
}
.chat-detail-context-address {
    white-space: normal;
    grid-column: 1 / -1;
}
body.dark-mode .chat-detail-context {
    background: #112222;
    border-bottom-color: #244644;
    color: #b0cece;
}
body.dark-mode .chat-detail-context-title {
    color: #e2e8f0;
}
body.dark-mode .chat-detail-context-chip {
    background: #1e3a5f;
    color: #bfdbfe;
}
body.dark-mode .chat-detail-context-item b {
    color: #5eead4;
}

/* Aviso de estatus (bloqueado / cerrado / sin conexion) */
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

/* Area de mensajes */
.chat-messages-wrap {
    flex-grow: 1;
    overflow-y: auto;
    padding: .95rem .95rem;
    display: flex;
    flex-direction: column;
    gap: .55rem;
    scroll-behavior: smooth;
    background: #f8fafc;
}
.chat-messages-wrap::-webkit-scrollbar { width: 5px; }
.chat-messages-wrap::-webkit-scrollbar-thumb { background: var(--track-border); border-radius: 3px; }
body.dark-mode .chat-messages-wrap { background: #101818; }
body.dark-mode .chat-messages-wrap::-webkit-scrollbar-thumb { background: #2d4444; }

/* Burbujas de mensajes */
.chat-bubble-wrap { display: flex; flex-direction: column; max-width: 82%; }
.chat-bubble-wrap.dir-out { align-items: flex-end;   margin-left: auto; }
.chat-bubble-wrap.dir-in  { align-items: flex-start; margin-right: auto; }
.chat-bubble {
    border-radius: 1rem;
    padding: .52rem .78rem;
    font-size: .82rem;
    line-height: 1.45;
    word-break: break-word;
    max-width: 100%;
    box-shadow: 0 .12rem .45rem rgba(15,23,42,.08);
}
.dir-out.role-gestor   .chat-bubble { background: #0d9488; color: #fff; border-bottom-right-radius: .25rem; }
.dir-out.role-conductor .chat-bubble { background: var(--track-color); color: #fff; border-bottom-right-radius: .25rem; }
.dir-in  .chat-bubble { background: #fff; color: #1e293b; border: 1px solid #e2e8f0; border-bottom-left-radius: .25rem; }
body.dark-mode .dir-in .chat-bubble { background: #1e2d2c; color: #e2e8f0; border-color: #2d4444; }
.chat-bubble-meta { font-size: .67rem; color: #94a3b8; margin-top: .18rem; padding: 0 .2rem; }
.chat-attachment-media {
    display: block;
    max-width: min(320px, 68vw);
    border-radius: .5rem;
    margin-bottom: .4rem;
    background: rgba(15,23,42,.08);
}
.chat-attachment-video {
    width: min(360px, 68vw);
    max-height: 260px;
}
.chat-attachment-file {
    display: flex;
    align-items: center;
    gap: .55rem;
    color: inherit;
    text-decoration: none;
    min-width: min(280px, 64vw);
}
.chat-attachment-file i {
    font-size: 1.35rem;
    opacity: .86;
}
.chat-attachment-file small {
    display: block;
    opacity: .72;
    margin-top: .1rem;
}
.chat-attachment-caption {
    margin: .35rem 0 0;
    white-space: pre-wrap;
}

/* Indicador escribiendo */
.chat-typing-indicator {
    display: inline-flex;
    align-items: center;
    gap: .32rem;
    align-self: flex-start;
    margin: .1rem .8rem .45rem;
    padding: .34rem .68rem;
    border-radius: 999px;
    background: #f1f5f9;
    color: #64748b;
    font-size: .76rem;
    flex-shrink: 0;
}
body.dark-mode .chat-typing-indicator {
    background: #1e2d2c;
    color: #b0cece;
}
.chat-typing-dots {
    display: inline-flex;
    align-items: center;
    gap: .18rem;
}
.chat-typing-dots span {
    width: .28rem;
    height: .28rem;
    border-radius: 50%;
    background: currentColor;
    opacity: .45;
    animation: trkTypingDots 1s infinite ease-in-out;
}
.chat-typing-dots span:nth-child(2) { animation-delay: .15s; }
.chat-typing-dots span:nth-child(3) { animation-delay: .3s; }
@keyframes trkTypingDots {
    0%, 80%, 100% { transform: translateY(0); opacity: .35; }
    40% { transform: translateY(-.18rem); opacity: .95; }
}

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

/* Boton "Nuevo mensaje  abajo" flotante */
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

/* Area de input */
.chat-input-area {
    border-top: 1px solid var(--track-border);
    padding: .75rem .85rem .85rem;
    flex-shrink: 0;
    background: #fff;
}
body.dark-mode .chat-input-area { background: #161f1f; border-top-color: var(--track-border); }
.chat-attachment-bar {
    display: flex;
    align-items: center;
    gap: .38rem;
    margin-bottom: .55rem;
}
.chat-attach-btn {
    width: 2.05rem;
    height: 2.05rem;
    border: 1px solid #dbe4ea;
    border-radius: .5rem;
    background: #f8fafc;
    color: #526070;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all .15s ease;
}
.chat-attach-btn:hover:not(:disabled) {
    background: var(--track-color-light);
    color: var(--track-color-dark);
    border-color: var(--track-border);
}
.chat-attach-btn:disabled {
    opacity: .48;
    cursor: not-allowed;
}
.chat-compose-row {
    display: flex;
    gap: .55rem;
    align-items: flex-end;
}
.chat-textarea {
    resize: none;
    font-size: .82rem;
    border-color: var(--track-border);
    border-radius: .65rem;
    flex-grow: 1;
    line-height: 1.4;
    min-height: 46px;
    max-height: 116px;
}
body.dark-mode .chat-textarea {
    background: #1e2d2c;
    color: #e2e8f0;
    border-color: #2d4444;
}
body.dark-mode .chat-attach-btn {
    background: #1e2d2c;
    border-color: #2d4444;
    color: #b0cece;
}
.chat-textarea:focus { border-color: var(--track-color); box-shadow: 0 0 0 .15rem rgba(13,148,136,.2); }
.chat-send-btn {
    background: var(--track-color);
    color: #fff;
    border: none;
    border-radius: .65rem;
    flex-shrink: 0;
    width: 44px; height: 46px;
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

/* Modal body: flex column, sin overflow interno */
#modalChatOperativo .modal-body {
    padding: 0;
    overflow: hidden;
    height: 100%;
}
@media (max-width: 991.98px) {
    #modalChatOperativo .modal-dialog {
        max-width: calc(100vw - .75rem);
        margin: .375rem auto;
    }
    #modalChatOperativo .modal-content {
        height: calc(100vh - .75rem);
    }
    .chat-operativo-layout {
        grid-template-columns: 1fr;
        grid-template-rows: minmax(420px, 1fr) minmax(340px, 46vh);
    }
    .chat-conversation-panel {
        border-right: 0;
        border-bottom: 1px solid var(--track-border);
    }
    #chatLiveMap {
        min-height: 340px;
    }
    .chat-live-info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Google Places suggestions must float above Bootstrap modals */
.pac-container {
    z-index: 20000 !important;
}
</style>
<?php
$trackingShowMainTabs = !empty($tracking_show_main_tabs);
$trackingVisibleSections = isset($tracking_visible_sections) && is_array($tracking_visible_sections)
    ? array_map('strval', $tracking_visible_sections)
    : [];
$trackingHeaderTitle = isset($tracking_header_title) && $tracking_header_title !== ''
    ? (string) $tracking_header_title
    : 'Tracking Recoleccion - Motos Adjudicadas';
$trackingHeaderSubtitle = isset($tracking_header_subtitle) && $tracking_header_subtitle !== ''
    ? (string) $tracking_header_subtitle
    : 'Creditos disponibles para planeacion de ruta fisica';
$trackingInitialSection = isset($tracking_initial_section) && $tracking_initial_section !== ''
    ? (string) $tracking_initial_section
    : 'creditos';
if (!empty($trackingVisibleSections) && !in_array($trackingInitialSection, $trackingVisibleSections, true)) {
    $trackingInitialSection = (string) reset($trackingVisibleSections);
}
$trackingCatalogoDefaultView = isset($tracking_catalogo_default_view) && in_array((string) $tracking_catalogo_default_view, ['directorio', 'agrupado', 'tabla'], true)
    ? (string) $tracking_catalogo_default_view
    : 'directorio';
$trackingShowNewRouteButton = !isset($tracking_show_new_route_button)
    ? !($trackingInitialSection === 'catalogos' && $trackingVisibleSections === ['catalogos'])
    : !empty($tracking_show_new_route_button);
$trackingIsPlaneacion = $trackingShowMainTabs
    && count($trackingVisibleSections) === 2
    && in_array('creditos', $trackingVisibleSections, true)
    && in_array('borradores', $trackingVisibleSections, true);
?>
<?php if (!empty($tracking_hide_section_cards)): ?>
<style>
#trkSectionGrid {
    display: none !important;
}
<?php if ($trackingShowMainTabs): ?>
#trackMainTabs {
    display: flex !important;
}
<?php else: ?>
#trackMainTabs {
    display: none !important;
}
<?php endif; ?>
</style>
<?php endif; ?>
<?php if (!empty($trackingVisibleSections)): ?>
<style>
<?php if (!in_array('creditos', $trackingVisibleSections, true)): ?>
#tabCreditosBtn, #tabCreditos { display: none !important; }
<?php endif; ?>
<?php if (!in_array('borradores', $trackingVisibleSections, true)): ?>
#tabBorradorBtn, #tabBorradores { display: none !important; }
<?php endif; ?>
<?php if (!in_array('rutas', $trackingVisibleSections, true)): ?>
#tabRutasBtn, #tabRutas { display: none !important; }
<?php endif; ?>
<?php if (!in_array('catalogos', $trackingVisibleSections, true)): ?>
#tabCatalogosBtn, #tabCatalogosTracking { display: none !important; }
<?php endif; ?>
<?php if (!in_array('operacion', $trackingVisibleSections, true)): ?>
#tabOperacionBtn, #tabOperacionTransportistas { display: none !important; }
<?php endif; ?>
</style>
<?php endif; ?>

<div class="container-fluid py-3 px-3 px-md-4<?= $trackingIsPlaneacion ? ' tracking-planeacion-view' : ''; ?>">

    <!-- -- Cabecera -- -->
    <div class="track-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="fa-solid fa-route me-2"></i><?= htmlspecialchars($trackingHeaderTitle, ENT_QUOTES, 'UTF-8'); ?></h4>
            <div class="track-subtitle"><?= htmlspecialchars($trackingHeaderSubtitle, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <?php if ($trackingShowNewRouteButton): ?>
            <button class="btn btn-primary fw-semibold" id="btnNuevaRuta">
                <i class="icon-base bx bx-plus icon-sm me-1"></i>Registrar ruta
            </button>
        <?php endif; ?>
    </div>

    <!-- -- Pestanas principales -- -->
    <div class="trk-section-grid" id="trkSectionGrid">
        <button type="button" class="trk-section-card<?= $trackingInitialSection === 'creditos' ? ' active' : ''; ?>" data-section-target="#tabCreditos" data-section-load="creditos">
            <div>
                <span class="trk-section-icon"><i class="fa-solid fa-motorcycle"></i></span>
                <div class="trk-section-title">Creditos disponibles</div>
                <div class="trk-section-desc">Consulta y filtra motos listas para planear rutas.</div>
            </div>
            <div class="trk-section-footer">
                <span>Ver creditos</span>
                <span class="trk-section-count" data-section-count="badgeCreditos">0</span>
            </div>
        </button>
        <button type="button" class="trk-section-card<?= $trackingInitialSection === 'borradores' ? ' active' : ''; ?>" data-section-target="#tabBorradores" data-section-load="borradores">
            <div>
                <span class="trk-section-icon"><i class="fa-solid fa-file-pen"></i></span>
                <div class="trk-section-title">Borradores</div>
                <div class="trk-section-desc">Retoma rutas guardadas antes de enviarlas.</div>
            </div>
            <div class="trk-section-footer">
                <span>Ver borradores</span>
                <span class="trk-section-count" data-section-count="badgeBorradores">0</span>
            </div>
        </button>
        <button type="button" class="trk-section-card<?= $trackingInitialSection === 'rutas' ? ' active' : ''; ?>" data-section-target="#tabRutas" data-section-load="rutas">
            <div>
                <span class="trk-section-icon"><i class="fa-solid fa-map-marked-alt"></i></span>
                <div class="trk-section-title">Rutas registradas</div>
                <div class="trk-section-desc">Seguimiento operativo, chat, avance y cancelaciones.</div>
            </div>
            <div class="trk-section-footer">
                <span>Ver rutas</span>
                <span class="trk-section-count" data-section-count="badgeRutas">0</span>
            </div>
        </button>
        <button type="button" class="trk-section-card<?= $trackingInitialSection === 'catalogos' ? ' active' : ''; ?>" data-section-target="#tabCatalogosTracking" data-section-load="catalogos">
            <div>
                <span class="trk-section-icon"><i class="fa-solid fa-building-user"></i></span>
                <div class="trk-section-title">CEDIS y Transportistas</div>
                <div class="trk-section-desc">Directorio operativo para asignacion de rutas.</div>
            </div>
            <div class="trk-section-footer">
                <span>Ver catalogos</span>
                <span class="trk-section-count" data-section-count="badgeCatalogos">0</span>
            </div>
        </button>
        <button type="button" class="trk-section-card<?= $trackingInitialSection === 'operacion' ? ' active' : ''; ?>" data-section-target="#tabOperacionTransportistas" data-section-load="operacion">
            <div>
                <span class="trk-section-icon"><i class="fa-solid fa-truck-fast"></i></span>
                <div class="trk-section-title">Administracion de transportistas</div>
                <div class="trk-section-desc">Disponibilidad, capacidad, rutas activas y alertas operativas.</div>
            </div>
            <div class="trk-section-footer">
                <span>Ver operacion</span>
                <span class="trk-section-count" data-section-count="badgeOperacionTransportistas">0</span>
            </div>
        </button>
    </div>

    <div class="<?= $trackingIsPlaneacion ? 'trk-planeacion-shell' : 'trk-module-content'; ?>">

    <ul class="nav nav-tabs track-tabs mb-3 d-none" id="trackMainTabs">
        <li class="nav-item">
            <button class="nav-link<?= $trackingInitialSection === 'creditos' ? ' active' : ''; ?>" id="tabCreditosBtn" data-bs-toggle="tab" data-bs-target="#tabCreditos">
                <i class="fa-solid fa-motorcycle me-1"></i>Pendientes de recoleccion
                <span id="badgeCreditos" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link<?= $trackingInitialSection === 'borradores' ? ' active' : ''; ?>" id="tabBorradorBtn" data-bs-toggle="tab" data-bs-target="#tabBorradores">
                <i class="fa-solid fa-file-pen me-1"></i>Borradores
                <span id="badgeBorradores" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link<?= $trackingInitialSection === 'rutas' ? ' active' : ''; ?>" id="tabRutasBtn" data-bs-toggle="tab" data-bs-target="#tabRutas">
                <i class="fa-solid fa-map-marked-alt me-1"></i>Rutas registradas
                <span id="badgeRutas" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link<?= $trackingInitialSection === 'catalogos' ? ' active' : ''; ?>" id="tabCatalogosBtn" data-bs-toggle="tab" data-bs-target="#tabCatalogosTracking">
                <i class="fa-solid fa-building-user me-1"></i>CEDIS y transportistas
                <span id="badgeCatalogos" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link<?= $trackingInitialSection === 'operacion' ? ' active' : ''; ?>" id="tabOperacionBtn" data-bs-toggle="tab" data-bs-target="#tabOperacionTransportistas">
                <i class="fa-solid fa-truck-fast me-1"></i>Administracion de transportistas
                <span id="badgeOperacionTransportistas" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- == Tab: Creditos disponibles == -->
        <div class="tab-pane fade<?= $trackingInitialSection === 'creditos' ? ' show active' : ''; ?>" id="tabCreditos">

            <!-- Filtros -->
            <div class="track-filters">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm trk-select-buscable" id="filtroEstado">
                            <option value=""> -  Todos los estados  - </option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Municipio</label>
                        <select class="form-select form-select-sm trk-select-buscable" id="filtroMunicipio" disabled>
                            <option value=""> -  Todos los municipios  - </option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" id="btnLimpiarFiltros">
                            <i class="fa-solid fa-eraser me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de creditos -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaCreditos" class="table table-hover mb-0 w-100 trk-operacion-table" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>Operacion</th>
                                    <th>Ubicacion</th>
                                    <th>Unidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- == Tab: Borradores == -->
        <div class="tab-pane fade<?= $trackingInitialSection === 'borradores' ? ' show active' : ''; ?>" id="tabBorradores">
            <div class="track-filters">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm trk-select-buscable" id="trkFiltroEstadoBorradores">
                            <option value="">Todos los estados</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Municipio</label>
                        <select class="form-select form-select-sm trk-select-buscable" id="trkFiltroMunicipioBorradores" disabled>
                            <option value="">Todos los municipios</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-2">
                        <button class="btn btn-sm btn-outline-secondary w-100" id="btnLimpiarFiltrosBorradores">
                            <i class="fa-solid fa-eraser me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="trk-table-toolbar">
                    <div class="trk-table-length">
                        <span>Mostrar</span>
                        <select class="form-select form-select-sm" id="trkBorradoresLength" aria-label="Registros por pagina">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>registros</span>
                    </div>
                    <div class="trk-table-search">
                        <label class="mb-0" for="trkBuscarBorradores">Buscar:</label>
                        <input type="search" class="form-control form-control-sm" id="trkBuscarBorradores"
                               placeholder="Buscar borrador..." autocomplete="off">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaBorradores" class="table table-hover mb-0 w-100 trk-borradores-table" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>Ruta</th>
                                    <th>Planeacion</th>
                                    <th>Creditos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- == Tab: Rutas registradas == -->
        <div class="tab-pane fade<?= $trackingInitialSection === 'rutas' ? ' show active' : ''; ?>" id="tabRutas">
            <div class="card border-0 shadow-sm">
                <div class="trk-rutas-toolbar">
                    <div>
                        <div class="fw-semibold" style="font-size:.92rem;">Rutas registradas</div>
                        <div class="text-muted small">Seguimiento operativo por estatus, transportista y avance.</div>
                    </div>
                    <div class="trk-rutas-search">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                            <input type="search" class="form-control" id="trkBuscarRutas"
                                   placeholder="Buscar ruta..." autocomplete="off">
                        </div>
                    </div>
                    <div class="trk-rutas-summary" id="trkRutasFiltros">
                        <button type="button" class="trk-rutas-filter active" data-estatus="todas">
                            Todas <span class="trk-rutas-count" id="trkRutaCountTodas">0</span>
                        </button>
                        <button type="button" class="trk-rutas-filter" data-estatus="en_proceso">
                            En proceso <span class="trk-rutas-count" id="trkRutaCountProceso">0</span>
                        </button>
                        <button type="button" class="trk-rutas-filter" data-estatus="enviada">
                            Enviadas <span class="trk-rutas-count" id="trkRutaCountEnviada">0</span>
                        </button>
                        <button type="button" class="trk-rutas-filter" data-estatus="cancelada">
                            Canceladas <span class="trk-rutas-count" id="trkRutaCountCancelada">0</span>
                        </button>
                    </div>
                    <div class="btn-group trk-rutas-view-toggle" role="group" aria-label="Vista de rutas">
                        <button type="button" class="btn btn-sm btn-label-secondary" id="trkVistaCards" title="Vista lista">
                            <i class="fa-solid fa-list"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-label-primary active" id="trkVistaGrid" title="Vista celdas 3 x 6">
                            <i class="fa-solid fa-border-all"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-label-secondary" id="trkVistaTabla" title="Vista tabla">
                            <i class="fa-solid fa-table-list"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="trkRutasCardsWrap" class="trk-rutas-board">
                        <div id="trkRutasCards" class="trk-rutas-grid"></div>
                        <div id="trkRutasPagination" class="trk-rutas-pagination"></div>
                    </div>
                    <div class="table-responsive" id="trkRutasTablaWrap" style="display:none;">
                        <table id="tablaRutas" class="table table-hover mb-0 w-100 trk-operacion-table" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>Ruta</th>
                                    <th>Seguimiento</th>
                                    <th>Creditos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: CEDIS y transportistas -->
        <div class="tab-pane fade<?= $trackingInitialSection === 'catalogos' ? ' show active' : ''; ?>" id="tabCatalogosTracking">
            <div class="trk-catalog-shell">
                <div class="trk-catalog-hero">
                    <div>
                        <div class="trk-catalog-eyebrow">Directorio operativo</div>
                        <h5 class="mb-1">CEDIS y transportistas</h5>
                        <div class="text-muted small">Administra centros de destino y operadores para asignacion de rutas.</div>
                    </div>
                    <div class="trk-catalog-actions">
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btnNuevoCedisTracking">
                            <i class="fa-solid fa-plus me-1"></i>Registrar CEDIS <i class="fa-solid fa-warehouse ms-1"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btnNuevoTransportistaTracking">
                            <i class="fa-solid fa-plus me-1"></i>Registrar Transportista <i class="fa-solid fa-id-card-clip ms-1"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btnNuevaUnidadTracking">
                            <i class="fa-solid fa-plus me-1"></i>Registrar Unidad <i class="fa-solid fa-truck ms-1"></i>
                        </button>
                    </div>
                </div>

                <div class="trk-catalog-grid">
                    <div class="trk-catalog-stat">
                        <div class="stat-label">CEDIS activos</div>
                        <div class="stat-value" id="statAgenciasTracking">0</div>
                    </div>
                    <div class="trk-catalog-stat">
                        <div class="stat-label">Transportistas internos</div>
                        <div class="stat-value" id="statTransportistasInternos">0</div>
                    </div>
                    <div class="trk-catalog-stat">
                        <div class="stat-label">Transportistas externos</div>
                        <div class="stat-value" id="statTransportistasExternos">0</div>
                    </div>
                    <div class="trk-catalog-stat">
                        <div class="stat-label">Directorio total</div>
                        <div class="stat-value" id="statCatalogoTotal">0</div>
                    </div>
                </div>

                <div class="trk-catalog-toolbar">
                    <div class="input-group input-group-sm trk-catalog-search">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input type="search" class="form-control" id="trkCatalogoBuscar"
                               placeholder="Buscar CEDIS, transportista, empresa, estado..." autocomplete="off">
                    </div>
                    <div class="btn-group" role="group" aria-label="Vista catalogos">
                        <button type="button" class="btn btn-sm <?= $trackingCatalogoDefaultView === 'directorio' ? 'btn-label-primary active' : 'btn-label-secondary'; ?> trk-catalog-view" data-view="directorio">
                            <i class="fa-solid fa-table-cells-large me-1"></i>Directorio
                        </button>
                        <button type="button" class="btn btn-sm <?= $trackingCatalogoDefaultView === 'agrupado' ? 'btn-label-primary active' : 'btn-label-secondary'; ?> trk-catalog-view" data-view="agrupado">
                            <i class="fa-solid fa-layer-group me-1"></i>Agrupado por CEDIS
                        </button>
                        <button type="button" class="btn btn-sm <?= $trackingCatalogoDefaultView === 'tabla' ? 'btn-label-primary active' : 'btn-label-secondary'; ?> trk-catalog-view" data-view="tabla">
                            <i class="fa-solid fa-table-list me-1"></i>Tabla
                        </button>
                    </div>
                </div>

                <div id="trkCatalogoDirectorio" class="trk-catalog-list<?= $trackingCatalogoDefaultView !== 'directorio' ? ' d-none' : ''; ?>"></div>
                <div id="trkCatalogoAgrupado" class="trk-catalog-grouped<?= $trackingCatalogoDefaultView !== 'agrupado' ? ' d-none' : ''; ?>"></div>
                <div id="trkCatalogoTabla" class="trk-catalog-table<?= $trackingCatalogoDefaultView !== 'tabla' ? ' d-none' : ''; ?>">
                    <div class="table-responsive">
                        <table id="tablaAgenciasTracking" class="table table-hover mb-0 w-100 trk-operacion-table" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>CEDIS</th>
                                    <th>Ubicacion</th>
                                    <th>Contacto</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-3">
                        <table id="tablaTransportistasTracking" class="table table-hover mb-0 w-100 trk-operacion-table" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>Transportista</th>
                                    <th>Tipo</th>
                                    <th>CEDIS / empresa</th>
                                    <th>Contacto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Administracion de transportistas -->
        <div class="tab-pane fade<?= $trackingInitialSection === 'operacion' ? ' show active' : ''; ?>" id="tabOperacionTransportistas">
            <div class="trk-admin-shell">
                <div class="trk-admin-kpis">
                    <div class="trk-admin-kpi"><span>Activos</span><strong id="trkOpKpiActivos">0</strong></div>
                    <div class="trk-admin-kpi"><span>Disponibles</span><strong id="trkOpKpiDisponibles">0</strong></div>
                    <div class="trk-admin-kpi"><span>En ruta</span><strong id="trkOpKpiRuta">0</strong></div>
                    <div class="trk-admin-kpi"><span>Programados</span><strong id="trkOpKpiProgramados">0</strong></div>
                    <div class="trk-admin-kpi"><span>Advertencia</span><strong id="trkOpKpiAdvertencia">0</strong></div>
                    <div class="trk-admin-kpi"><span>Saturados</span><strong id="trkOpKpiSaturados">0</strong></div>
                </div>

                <div class="trk-admin-toolbar">
                    <div>
                        <label class="form-label mb-1 small fw-semibold">Buscar transportista</label>
                        <input type="search" class="form-control form-control-sm" id="trkOpBuscar"
                               placeholder="Nombre, empresa, CEDIS, ruta..." autocomplete="off">
                    </div>
                    <div>
                        <label class="form-label mb-1 small fw-semibold">Estatus operativo</label>
                        <select class="form-select form-select-sm" id="trkOpFiltroEstatus">
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="programado">Programado</option>
                            <option value="en_ruta">En ruta</option>
                            <option value="advertencia">Advertencia</option>
                            <option value="saturado">Saturado</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label mb-1 small fw-semibold">Tipo</label>
                        <select class="form-select form-select-sm" id="trkOpFiltroTipo">
                            <option value="">Todos</option>
                            <option value="interno">Interno</option>
                            <option value="externo">Externo</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="trkOpActualizar">
                        <i class="fa-solid fa-rotate me-1"></i>Actualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-label-info" id="btnNuevaUnidadOperacion">
                        <i class="fa-solid fa-plus me-1"></i>Registrar Unidad <i class="fa-solid fa-truck ms-1"></i>
                    </button>
                    <div class="btn-group trk-admin-view-toggle" role="group" aria-label="Vista administracion transportistas">
                        <button type="button" class="btn btn-sm btn-label-secondary" id="trkOpVistaLista" title="Vista lista">
                            <i class="fa-solid fa-list"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-label-primary active" id="trkOpVistaGrid" title="Vista celdas">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-label-secondary" id="trkOpVistaTabla" title="Vista tabla">
                            <i class="fa-solid fa-table-list"></i>
                        </button>
                    </div>
                </div>

                <div id="trkOpAlertas" class="trk-admin-alerts"></div>
                <div id="trkOpGridWrap">
                    <div id="trkOpGrid" class="trk-admin-grid"></div>
                </div>
                <div id="trkOpTablaWrap" class="trk-admin-table-wrap d-none">
                    <div class="table-responsive">
                        <table class="table table-hover trk-admin-table w-100">
                            <thead>
                                <tr>
                                    <th>Transportista</th>
                                    <th>Operacion</th>
                                    <th>Capacidad</th>
                                    <th>Rutas</th>
                                    <th>CEDIS / ubicacion</th>
                                    <th>Alertas</th>
                                </tr>
                            </thead>
                            <tbody id="trkOpTablaBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /tab-content -->

    </div><!-- /trk-planeacion-shell -->

</div><!-- /container-fluid -->

<!-- ==========================================================
     Modal  -  CEDIS tracking
========================================================== -->
<div class="modal fade" id="modalCedisTracking" tabindex="-1" aria-labelledby="modalCedisTrackingLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalCedisTrackingLabel">
                    <i class="fa-solid fa-warehouse me-2" style="color:var(--track-color);"></i>Registrar CEDIS
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cedisIdTracking" value="">
                <div class="row g-2">
                    <div class="col-12 col-md-8">
                        <label class="form-label small fw-semibold">Nombre CEDIS *</label>
                        <input type="text" class="form-control form-control-sm" id="cedisNombreTracking" maxlength="150">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Clave</label>
                        <input type="text" class="form-control form-control-sm" id="cedisClaveTracking" maxlength="80" placeholder="Auto">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Direccion</label>
                        <input type="text" class="form-control form-control-sm" id="cedisDireccionTracking" maxlength="255">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Estado</label>
                        <input type="text" class="form-control form-control-sm" id="cedisEstadoTracking" maxlength="100">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Municipio</label>
                        <input type="text" class="form-control form-control-sm" id="cedisMunicipioTracking" maxlength="120">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Codigo postal</label>
                        <input type="text" class="form-control form-control-sm" id="cedisCpTracking" maxlength="10">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Latitud</label>
                        <input type="number" step="any" class="form-control form-control-sm" id="cedisLatTracking">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Longitud</label>
                        <input type="number" step="any" class="form-control form-control-sm" id="cedisLngTracking">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Link Google Maps</label>
                        <input type="url" class="form-control form-control-sm" id="cedisLinkTracking" maxlength="1000">
                        <div class="form-text small" id="cedisLinkCoordStatus"></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Telefono</label>
                        <input type="text" class="form-control form-control-sm" id="cedisTelefonoTracking" maxlength="30">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Encargado</label>
                        <input type="text" class="form-control form-control-sm" id="cedisEncargadoTracking" maxlength="150">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" class="form-control form-control-sm" id="cedisEmailTracking" maxlength="150">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Horario</label>
                        <input type="text" class="form-control form-control-sm" id="cedisHorarioTracking" maxlength="1000">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" id="cedisActivoTracking" checked>
                            <label class="form-check-label small" for="cedisActivoTracking">Activo</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnGuardarCedisTracking">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar CEDIS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Modal  -  Transportista tracking
========================================================== -->
<div class="modal fade" id="modalTransportistaTracking" tabindex="-1" aria-labelledby="modalTransportistaTrackingLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalTransportistaTrackingLabel">
                    <i class="fa-solid fa-id-card-clip me-2" style="color:var(--track-color);"></i>Registrar Transportista
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="transportistaIdTracking" value="">
                <div class="row g-2">
                    <div class="col-12 col-md-8">
                        <label class="form-label small fw-semibold">Nombre transportista *</label>
                        <input type="text" class="form-control form-control-sm" id="transportistaNombreTracking" maxlength="180">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Tipo</label>
                        <select class="form-select form-select-sm" id="transportistaTipoTracking" disabled>
                            <option value="interno">Interno</option>
                            <option value="externo">Externo</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">CEDIS asignado</label>
                        <select class="form-select form-select-sm" id="transportistaCedisTracking"></select>
                        <div class="form-text small">LOMAS y TLALNEPANTLA asignan tipo Interno; el resto asigna Externo.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Empresa origen</label>
                        <input type="text" class="form-control form-control-sm" id="transportistaEmpresaTracking" maxlength="180">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">CURP / RFC</label>
                        <input type="text" class="form-control form-control-sm" id="transportistaCurpTracking" maxlength="25">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Telefono</label>
                        <input type="text" class="form-control form-control-sm" id="transportistaTelefonoTracking" maxlength="30">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Puesto</label>
                        <input type="text" class="form-control form-control-sm" id="transportistaPuestoTracking" maxlength="120">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" class="form-control form-control-sm" id="transportistaEmailTracking" maxlength="150">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Usuario</label>
                        <input type="text" class="form-control form-control-sm" id="transportistaUsernameTracking" maxlength="60" placeholder="Auto">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Password</label>
                        <input type="text" class="form-control form-control-sm" id="transportistaPasswordTracking" maxlength="255" placeholder="Solo si cambia">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" id="transportistaActivoTracking" checked>
                            <label class="form-check-label small" for="transportistaActivoTracking">Activo</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnGuardarTransportistaTracking">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar Transportista
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Modal  -  Unidad / transporte tracking
========================================================== -->
<div class="modal fade" id="modalUnidadTransportistaTracking" tabindex="-1" aria-labelledby="modalUnidadTransportistaTrackingLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalUnidadTransportistaTrackingLabel">
                    <i class="fa-solid fa-truck me-2" style="color:var(--track-color);"></i>Registrar Unidad
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="unidadIdCapacidadTracking" value="">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Transportista *</label>
                        <select class="form-select form-select-sm" id="unidadTransportistaTracking"></select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold">Tipo de unidad *</label>
                        <select class="form-select form-select-sm" id="unidadTipoTracking">
                            <option value="">Selecciona tipo</option>
                            <option value="CAMIONETA">CAMIONETA</option>
                            <option value="TORTON">TORTON</option>
                            <option value="RABON">RABON</option>
                            <option value="GRUA">GRUA</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold">Capacidad motos *</label>
                        <input type="text" class="form-control form-control-sm trk-no-spinner text-center fw-semibold"
                               id="unidadCapacidadTracking" inputmode="numeric" maxlength="2" autocomplete="off">
                    </div>
                    <div class="col-6 col-md-5">
                        <label class="form-label small fw-semibold">Identificador de unidad <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadEconomicoTracking" maxlength="60" placeholder="Ej. CAM-07, TORTON-02">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Marca</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadMarcaTracking" maxlength="80">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Modelo</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadModeloTracking" maxlength="100">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Año</label>
                        <input type="number" min="1980" max="2100" step="1" class="form-control form-control-sm" id="unidadAnioTracking">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Placa</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadPlacaTracking" maxlength="30">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Color</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadColorTracking" maxlength="60">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Seguro vence</label>
                        <input type="date" class="form-control form-control-sm" id="unidadVigenciaSeguroTracking">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">VIN / Numero de serie</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadSerieTracking" maxlength="80">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Numero de motor</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadMotorTracking" maxlength="80">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Aseguradora</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadAseguradoraTracking" maxlength="120">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Poliza</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="unidadPolizaTracking" maxlength="80">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Observaciones</label>
                        <textarea class="form-control form-control-sm" id="unidadObservacionesTracking" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" id="unidadActivoTracking" checked>
                            <label class="form-check-label small" for="unidadActivoTracking">Unidad activa para asignacion</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnGuardarUnidadTransportistaTracking">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar Unidad
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Modal  -  Registrar / editar ruta
========================================================== -->
<div class="modal fade" id="modalRegistrarRuta" tabindex="-1" aria-labelledby="modalRegistrarRutaLabel"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalRegistrarRutaLabel">
                    <i class="fa-solid fa-route me-2"></i>Registrar ruta de recoleccion
                </h5>
                <button type="button" class="btn-close" id="btnCerrarModal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger d-none py-2 px-3 mb-3" id="rutaCancelacionInfo"></div>

                <!-- -- Seccion 1: Datos de la ruta -- -->
                <div class="row g-3 mb-3" id="trkRouteHeaderSection">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">
                            Nombre de ruta <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm"
                               id="rutaNombre" maxlength="100" placeholder="Ej. Ruta GDL Norte Junio" style="text-transform:uppercase;">
                        <div class="form-text small mt-1" id="rutaNombreStatus"></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">
                            Fecha programada <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm"
                               id="rutaFecha" min="">
                        <div class="form-text text-muted" style="font-size:.72rem;">
                            Minimo <span id="rutaDiasMinimosTxt">2</span> dia(s) desde hoy - Deja una fecha tentativa si aun no esta definida para que puedas guardar correctamente el borrador de la ruta.
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

                <div class="trk-transport-panel mb-3" id="secTransportistaRuta">
                    <label class="form-label small fw-semibold mb-2">
                        Transportista de la ruta
                        <span class="text-muted">(interno o externo)</span>
                        <span id="rutaTransportistaTipoBadge" class="d-none float-end"></span>
                    </label>
                    <div class="row g-2 align-items-end">
                        <input type="hidden" id="rutaTipoTransportista" value="">
                        <input type="hidden" id="rutaAgenciaTracking" value="">
                        <div class="col-12 col-md-6">
                            <select class="form-select form-select-sm trk-select-buscable" id="rutaTransportistaTracking" disabled>
                                <option value="">Selecciona transportista</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1 small">Destino del transportista</label>
                            <select class="form-select form-select-sm" id="rutaCedisDestino">
                                <option value="">Selecciona CEDIS destino</option>
                            </select>
                        </div>
                    </div>
                    <div id="rutaTransportistaInfo" class="trk-transport-info mt-2 d-none"></div>
                    <div id="rutaCedisDestinoInfo" class="trk-transport-info mt-2 d-none"></div>
                    <div id="rutaTransportistaAssist" class="trk-driver-assist mt-2 d-none"></div>
                </div>

                <div class="mb-2" id="secAgregarCredito">
                    <label class="form-label small fw-semibold">
                        Agregar credito a la ruta
                    </label>
                    <!-- Filtros de ubicacion para creditos -->
                    <div class="row g-2 mb-2" id="crdFiltrosUbicacion">
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1 small">Estado</label>
                            <select class="form-select form-select-sm trk-select-buscable" id="crdFiltroEstado">
                                <option value=""> -  Todos los estados  - </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1 small">Municipio</label>
                            <select class="form-select form-select-sm trk-select-buscable" id="crdFiltroMunicipio" disabled>
                                <option value=""> -  Todos los municipios  - </option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group input-group-sm">
                        <select class="form-select trk-select-buscable" id="rutaCreditoSelect">
                            <option value="">Buscar por credito, modelo, VIN...</option>
                        </select>
                        <button class="btn" id="btnAgregarCredito"
                                style="background:var(--track-color);color:#fff;">
                            <i class="fa-solid fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                </div>

                <!-- -- Lista de creditos en la ruta (sortable) -- -->
                <div class="trk-planner-panel mb-3 d-none" id="trkPlannerPanel">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold">
                            <i class="fa-solid fa-layer-group me-1" style="color:var(--track-color);"></i>
                            Planeacion por estados y municipios
                        </span>
                        <span class="badge bg-primary" id="trkPlannerModeBadge">Modo volumen</span>
                    </div>
                    <div class="trk-planner-summary mb-2" id="trkPlannerSummary"></div>
                    <div id="trkPlannerGroups"></div>
                </div>

                <div class="mb-3" id="trkRouteListSection">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold text-muted">
                            Creditos en la ruta
                            (<span id="rutaCreditosCount">0</span>)
                        </span>
                        <span class="small text-muted" id="reorderHint">
                            <i class="fa-solid fa-arrows-up-down me-1"></i>
                            Arrastra para reordenar
                        </span>
                    </div>
                    <div class="trk-route-list-tools" id="trkRouteListTools">
                        <select class="form-select form-select-sm" id="trkListaFiltroEstado">
                            <option value="">Todos los estados</option>
                        </select>
                        <select class="form-select form-select-sm" id="trkListaFiltroMunicipio" disabled>
                            <option value="">Todos los municipios</option>
                        </select>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="search" class="form-control" id="trkListaBuscar"
                                   placeholder="Buscar en creditos de la ruta...">
                        </div>
                    </div>
                    <div id="rutaCreditosList" style="max-height:280px;overflow-y:auto;border:1px dashed var(--track-border);border-radius:.5rem;padding:.5rem;">
                        <div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
                            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
                            Aun no hay creditos en esta ruta
                        </div>
                    </div>
                </div>

                <!-- -- Seccion 3.5: Tracking en tiempo real -- -->
                <div id="trkTrackingSection" class="mb-3 d-none">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">
                            <i class="fa-solid fa-route me-1" style="color:var(--track-color);"></i>
                            Estado del recorrido
                        </span>
                        <span id="trkWsDot" title="Sin conexion en tiempo real"
                              style="width:.55rem;height:.55rem;border-radius:50%;background:#cbd5e1;display:inline-block;"></span>
                    </div>
                    <!-- Barra de progreso -->
                    <div class="progress mb-1" style="height:5px;border-radius:999px;">
                        <div class="progress-bar" id="trkProgressBar"
                             style="width:0%;background:var(--track-color);transition:width .4s;"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span id="trkProgressText"> -  /  -  puntos</span>
                        <span id="trkPorcentaje">0%</span>
                    </div>
                    <!-- Ultima ubicacion del conductor -->
                    <div id="trkUltimaUbicacion" class="trk-location-pill d-none mb-2">
                        <i class="fa-solid fa-location-arrow" style="color:var(--track-color);"></i>
                        <span id="trkUbicacionText"> - </span>
                        <span class="text-muted" id="trkUbicacionTime"></span>
                    </div>
                    <!-- Timeline de paradas -->
                    <div class="trk-timeline" id="trkTimeline">
                        <div class="text-center text-muted py-2 small" id="trkTimelineEmpty">
                            <span class="spinner-border spinner-border-sm opacity-25" style="color:var(--track-color);"></span>
                        </div>
                    </div>
                </div>

                <!-- -- Seccion 4: Mapa de la ruta -- -->
                <div class="mb-2" id="trkMapSection">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">
                            <i class="fa-solid fa-map-location-dot me-1" style="color:var(--track-color);"></i>
                            Mapa de la ruta
                        </span>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" id="btnTogglePlanner" style="font-size:.75rem;">
                                <i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i>Planeador
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnRefreshMap" style="font-size:.75rem;">
                                <i class="fa-solid fa-refresh me-1"></i>Actualizar mapa
                            </button>
                        </div>
                    </div>
                    <div id="trackMapContainer">
                        <div class="map-placeholder" id="mapPlaceholder">
                            <i class="fa-solid fa-map fa-2x opacity-30"></i>
                            <span>Agrega creditos para visualizar la ruta</span>
                        </div>
                        <div id="trackMap" style="display:none;"></div>
                        <div id="trkLiveMapInfo" class="trk-live-map-card d-none">
                            <div class="live-title">
                                <i class="fa-solid fa-truck-fast"></i>
                                <span>Unidad en vivo</span>
                            </div>
                            <div class="live-meta">
                                <span id="trkLiveUpdated">Sin senal</span>
                                <span id="trkLiveSpeed">Vel.  - </span>
                                <span id="trkLiveAccuracy">Prec.  - </span>
                                <span id="trkLiveBattery">Bat.  - </span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning py-1 px-2 mt-1 small d-none" id="mapAlertCoords">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Algunos creditos no tienen coordenadas ni direccion registrada.
                        La ruta en el mapa puede estar incompleta.
                    </div>
                </div>

            </div><!-- /modal-body -->

            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-label-secondary btn-sm" id="btnCerrarModalFooter">
                    <i class="icon-base bx bx-x icon-sm me-1"></i>Cancelar
                </button>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted me-1" id="trkAutosaveStatus">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>Autoguardado listo
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" id="btnActualizarRuta"
                            style="display:none;">
                        <i class="icon-base bx bx-refresh icon-sm me-1"></i>Actualizar ruta
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btnEnviarRuta">
                        <i class="icon-base bx bx-send icon-sm me-1"></i>Enviar ruta
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================
     Modal  -  Detalle de ruta (solo lectura)
========================================================== -->
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

<!-- ==========================================================
     Modal  -  Cambiar CEDIS destino
========================================================== -->
<div class="modal fade" id="modalCambiarCedisDestino" tabindex="-1" aria-labelledby="modalCambiarCedisDestinoLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalCambiarCedisDestinoLabel">
                    <i class="fa-solid fa-warehouse me-2" style="color:var(--track-color);"></i>
                    Cambiar CEDIS destino
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="trkCambiarCedisRutaId">
                <div class="alert alert-info py-2 px-3 small mb-3" id="trkCambiarCedisActual">
                    CEDIS actual: Sin destino asignado
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nuevo CEDIS destino</label>
                    <select class="form-select" id="trkCambiarCedisSelect"></select>
                </div>
                <div class="mb-1 d-flex justify-content-between align-items-center">
                    <label class="form-label small fw-semibold mb-0">Motivo del cambio</label>
                    <span class="small text-muted" id="trkCambiarCedisMotivoCount">0/200</span>
                </div>
                <textarea class="form-control" id="trkCambiarCedisMotivo" rows="4" maxlength="200"
                          placeholder="Ej. Cambio por disponibilidad operativa"></textarea>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="btnConfirmarCambioCedisDestino">
                    <i class="fa-solid fa-check me-1"></i>Confirmar cambio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Modal  -  Seleccionar ubicacion en mapa (map picker)
========================================================== -->
<div class="modal fade" id="modalMapPicker" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:var(--track-color-dark);color:#fff;">
                <h6 class="modal-title mb-0">
                    <i class="fa-solid fa-map-pin me-2"></i>
                    Asignar ubicacion manual del credito
                </h6>
                <button type="button" class="btn-close" id="btnCerrarMapPicker" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body p-2">
                <p class="small text-muted mb-2 px-1">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Busca una direccion o haz clic en el mapa para colocar el pin del credito
                    <strong id="mapPickerCreditoLabel"></strong>. Al confirmar o cancelar volveras a la ruta.
                </p>
                <div class="input-group input-group-sm mb-2" id="mapPickerSearchWrap">
                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--track-color);"></i>
                    </span>
                    <input type="text" class="form-control" id="mapPickerSearch"
                           placeholder="Buscar direccion, colonia, municipio..." autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiarMapSearch" title="Limpiar busqueda">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="mapPickerContainer" style="width:100%;height:420px;border-radius:.5rem;overflow:hidden;border:1px solid var(--track-border);"></div>
                <div class="mt-2 px-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="small text-muted" id="mapPickerCoordsLabel">
                            <i class="fa-solid fa-crosshairs me-1"></i>Sin seleccion
                        </span>
                    </div>
                    <div id="mapPickerGeoInfo" class="small text-muted mt-1 d-none">
                        <i class="fa-solid fa-map-location-dot me-1" style="color:var(--track-color);"></i>
                        <span id="mapPickerEstadoMun"> - </span>
                    </div>
                    <div id="mapPickerDireccionWrap" class="small text-muted mt-1 d-none">
                        <i class="fa-solid fa-location-dot me-1" style="color:var(--track-color);"></i>
                        <span id="mapPickerDireccionCompleta"> - </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCancelarMapPicker">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm" id="btnConfirmarMapPicker"
                        style="background:var(--track-color);color:#fff;" disabled>
                    <i class="fa-solid fa-check me-1"></i>Confirmar ubicacion
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Modal  -  Agregar credito a ruta existente
========================================================== -->
<div class="modal fade" id="modalAgregarCreditoRuta" tabindex="-1" aria-labelledby="modalAgregarCreditoRutaLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalAgregarCreditoRutaLabel">
                    <i class="fa-solid fa-plus me-2" style="color:var(--track-color);"></i>
                    Agregar credito a ruta existente
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="trk-quick-credit-summary mb-3" id="trkQuickCreditSummary"></div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">Rutas disponibles para agregar</span>
                    <span class="badge bg-label-primary" id="trkQuickRoutesCount">0</span>
                </div>
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold mb-1">Filtrar rutas por estado</label>
                        <select class="form-select form-select-sm" id="trkQuickFiltroEstado">
                            <option value="">Todos los estados</option>
                        </select>
                    </div>
                </div>
                <div id="trkQuickRoutesList" class="trk-quick-routes-list"></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Offcanvas  -  Chat Operativo (gestor / Sparta Ledger)
     Se abre desde el boton de chat en la tabla de rutas.
     Una pestana por cada id_detalle (punto de recoleccion).
========================================================== -->
<div class="modal fade" id="modalChatOperativo" tabindex="-1" aria-labelledby="modalChatOperativoLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header chat-modal-header py-3 px-3">
                <div class="d-flex align-items-center gap-2" style="min-width:0;">
                    <span class="chat-header-icon"><i class="fa-solid fa-comments"></i></span>
                    <div style="min-width:0;">
                        <h6 class="modal-title mb-0" id="modalChatOperativoLabel">Chat Operativo</h6>
                        <small id="chatRutaNombre" class="chat-route-name"></small>
                    </div>
                </div>
                <div class="d-inline-flex align-items-center gap-2 ms-2">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cerrar" style="flex-shrink:0;"></button>
                </div>
            </div>

            <div class="modal-body">
                <div class="chat-operativo-layout" id="chatOperativoLayout">
                    <button type="button" class="btn btn-sm btn-label-primary chat-map-toggle chat-map-toggle-edge"
                            id="btnToggleChatMap" title="Ocultar mapa">
                        <i class="fa-solid fa-angles-right"></i>
                    </button>
                    <section class="chat-conversation-panel">
                        <div class="chat-tabs-wrap" id="chatTabsWrap" style="display:none;">
                            <ul class="nav d-flex" id="chatTabList" role="tablist"></ul>
                            <span class="chat-connection-status" id="chatConnectionStatus">Sin conexion registrada</span>
                        </div>

                        <div id="chatPanesContainer" class="flex-grow-1 d-flex flex-column" style="overflow:hidden;"></div>

                        <div id="chatEmptyPlaceholder" class="flex-grow-1 d-none align-items-center justify-content-center text-center p-4"
                             style="color:#94a3b8;">
                            <div>
                                <i class="fa-solid fa-comments fa-2x mb-2 opacity-25 d-block"></i>
                                <span class="small">No hay puntos de recoleccion disponibles</span>
                            </div>
                        </div>
                    </section>

                    <section class="chat-live-panel">
                        <div class="chat-live-header">
                            <div style="min-width:0;">
                                <div class="chat-live-title">
                                    <i class="fa-solid fa-truck-fast me-1" style="color:var(--track-color);"></i>
                                    Ubicacion en tiempo real
                                </div>
                                <span class="chat-live-subtitle" id="chatLiveRutaNombre">Esperando ubicacion del transportista</span>
                            </div>
                            <span class="d-inline-flex align-items-center gap-1 small text-muted">
                                <span id="chatLiveWsDot" class="chat-ws-dot chat-ws-off"></span>
                                Live
                            </span>
                        </div>
                        <div class="chat-live-map-wrap">
                            <div id="chatLiveMap"></div>
                            <div id="chatLivePlaceholder" class="chat-live-placeholder">
                                <i class="fa-solid fa-location-dot fa-2x opacity-25"></i>
                                <span class="small">Esperando primera ubicacion GPS</span>
                            </div>
                            <div id="chatLiveMapInfo" class="chat-live-info-card d-none">
                                <div class="fw-semibold mb-1" id="chatLiveUpdated">Sin ubicacion</div>
                                <div class="chat-live-info-grid">
                                    <span id="chatLiveSpeed">Vel. -</span>
                                    <span id="chatLiveAccuracy">Prec. -</span>
                                    <span id="chatLiveBattery">Bat. -</span>
                                    <span id="chatLiveCoords">Coord. -</span>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($google_maps_api_key_js)) : ?>
<script>
    window._trackGoogleMapsKey = <?= json_encode((string) $google_maps_api_key_js) ?>;
</script>
<?php else : ?>
<script>window._trackGoogleMapsKey = null;</script>
<?php endif; ?>

<script>
/* Chat Operativo  -  URL WebSocket (sin credenciales, solo el host) */
window._trackingChatWsBaseUrl   = <?= json_encode((string)($tracking_chat_ws_base_url ?? '')) ?>;
window._trackingApiBaseUrl      = <?= json_encode((string)($tracking_api_base_url ?? '')) ?>;
window._trackingChatGestorNombre = <?= json_encode(trim((string)($_SESSION['usuario_nombre'] ?? 'Gestor'))) ?>;
window._trackingDiasMinimosProgramacion = <?= json_encode((int)($tracking_dias_minimos_programacion ?? 2)) ?>;
window._trackingInitialSection = <?= json_encode($trackingInitialSection) ?>;
window._trackingVisibleSections = <?= json_encode(array_values($trackingVisibleSections ?? [])) ?>;
window._trackingCatalogoDefaultView = <?= json_encode($trackingCatalogoDefaultView) ?>;
window._trackingPuedeCancelarRutas = <?= !empty($tracking_puede_cancelar_rutas) ? 'true' : 'false' ?>;
</script>
<!-- SortableJS (drag-and-drop sin jQuery UI) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
/* =======================================================
   tracking_recoleccion.js  -  logica del modulo
======================================================= */
'use strict';

// --- Estado local ---------------------------------------
const _trk = {
    creditosDisponibles:  [],   // todos los creditos disponibles del servidor
    creditosFiltroBase:   [],   // base real para armar filtros estado/municipio
    creditosEnRuta:       [],   // creditos actualmente en el modal
    agenciasTracking:     [],
    transportistasTracking: [],
    operacionTransportistas: [],
    operacionResumen:      {},
    operacionFiltroEstatus:'',
    operacionFiltroTipo:   '',
    operacionBusqueda:     '',
    operacionVista:        'grid',
    operacionLiveLoaded:   {},
    operacionLiveInfo:     {},
    rutasRegistradas:     [],
    rutasFiltro:          'todas',
    rutasVista:           'cards',
    rutasLayout:          'grid',
    rutasBusqueda:        '',
    rutasPagina:          1,
    rutasPorPagina:       18,
    borradoresBusqueda:   '',
    borradoresData:       [],
    borradoresFiltroEstado: '',
    borradoresFiltroMunicipio: '',
    quickAddCredito:      null,
    quickAddEstado:       '',
    detalleRutaActualId:   null,
    cedisDestinoPorRuta:   {},
    cedisDestinoHistorial: {},
    catalogoVista:        ['directorio', 'agrupado', 'tabla'].includes(window._trackingCatalogoDefaultView) ? window._trackingCatalogoDefaultView : 'directorio',
    catalogoBusqueda:     '',
    cargadoEstados:       false,
    cargadoCreditos:      false,
    cargadoCatalogos:     false,
    cargadoOperacion:     false,
    cargadoBorradores:    false,
    cargadoRutas:         false,
    syncStarted:          false,
    syncTimers:           [],
    syncInFlight:         {},
    syncLastAt:           0,
    syncLoaderActivo:     false,
    chatUnreadPorRuta:    {},
    trackingApiDisponible:true,
    trackingApiRetryAt:   0,
    rutaCancelada:        false,
    idRutaEditando:       null, // null = nueva ruta
    estatusRuta:          null, // estatus_ruta de la ruta cargada (null = nueva)
    soloLectura:          false,// modal en modo vista bloqueada
    cargando:             false, // cargando ruta existente (evita haychangios)
    haycambios:           false,
    haychangios:          false,
    autosaveTimer:        null,
    autosaveInFlight:     false,
    autosavePending:      false,
    autosaveLastHash:     '',
    autosaveStatusTimer:  null,
    autosaveDirtyLists:   false,
    nombreRutaCheckTimer: null,
    nombreRutaCheckSeq:   0,
    nombreRutaValidando:  false,
    nombreRutaDisponible: null,
    nombreRutaUltimoValor:'',
    tablaCreditosDT:      null,
    tablaRutasDT:         null,
    tablaRutasBorradorDT: null,
    tablaAgenciasDT:      null,
    tablaTransportistasDT:null,
    sortableInstance:     null,
    mapInstance:          null,
    mapLoaded:            false,
    geocoder:             null,
    mapMarkers:           [],   // marcadores activos en el mapa
    mapMarkersByCredito:  {},
    creditoPosiciones:    {},
    directionsRenderer:   null, // renderer de ruta activo
    trafficLayer:         null,
    chatTrafficLayer:     null,
    liveVehicleMarker:    null,
    liveVehiclePolyline:  null,
    liveVehiclePath:      [],
    chatMapInstance:      null,
    chatLiveVehicleMarker:null,
    chatLiveVehiclePolyline:null,
    chatLiveConectado:    false,
    chatUltimaConexionAt: null,
    chatConnectionTimer:  null,
    routeLegDurations:    [],   // duraciones Google Maps entre puntos confirmados
};

// --- Utilidades -----------------------------------------
const trkFetch = (url, opts = {}) =>
    fetch(url, { credentials: 'same-origin', ...opts })
        .then(r => r.json());



const trkConfirm = (msg) => new Promise(resolve => {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Salir sin guardar?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, salir',
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
    en_revision: '<span class="badge badge-conf-en_revision">En revision</span>',
};
const RUTA_LABEL = {
    borrador:               '<span class="badge badge-ruta-borrador">Borrador</span>',
    pendiente_confirmacion: '<span class="badge badge-ruta-pendiente_confirmacion">Pend. confirmacion</span>',
    lista_envio:            '<span class="badge badge-ruta-lista_envio">Lista para enviar</span>',
    enviada:                '<span class="badge badge-ruta-enviada">Enviada</span>',
    en_proceso:             '<span class="badge badge-ruta-en_proceso">En proceso</span>',
    concluida:              '<span class="badge badge-ruta-concluida">Concluida</span>',
    cancelada:              '<span class="badge badge-ruta-cancelada">Cancelada</span>',
};

// --- Inicializacion -------------------------------------
function _trkSelect2Disponible() {
    return typeof window.jQuery !== 'undefined' && !!window.jQuery.fn.select2;
}

function _trkInicializarSelectBuscable(selector, opts = {}) {
    if (!_trkSelect2Disponible()) return;
    const $el = $(selector);
    if (!$el.length || $el.hasClass('select2-hidden-accessible')) return;
    const parent = opts.dropdownParent ? $(opts.dropdownParent) : null;
    $el.select2({
        width: '100%',
        allowClear: !!opts.allowClear,
        placeholder: opts.placeholder || 'Buscar...',
        dropdownParent: parent && parent.length ? parent : undefined,
        templateResult: opts.templateResult,
        templateSelection: opts.templateSelection,
        escapeMarkup: opts.escapeMarkup,
        language: {
            noResults: () => 'Sin resultados',
            searching: () => 'Buscando...',
        },
    });
}

function _trkInicializarSelectsBuscables() {
    _trkInicializarSelectBuscable('#filtroEstado', { placeholder: 'Todos los estados', allowClear: true });
    _trkInicializarSelectBuscable('#filtroMunicipio', { placeholder: 'Todos los municipios', allowClear: true });
    _trkInicializarSelectBuscable('#trkFiltroEstadoBorradores', { placeholder: 'Todos los estados', allowClear: true });
    _trkInicializarSelectBuscable('#trkFiltroMunicipioBorradores', { placeholder: 'Todos los municipios', allowClear: true });
    _trkInicializarSelectBuscable('#crdFiltroEstado', {
        placeholder: 'Todos los estados',
        allowClear: true,
        dropdownParent: '#modalRegistrarRuta',
    });
    _trkInicializarSelectBuscable('#crdFiltroMunicipio', {
        placeholder: 'Todos los municipios',
        allowClear: true,
        dropdownParent: '#modalRegistrarRuta',
    });
    _trkInicializarSelectBuscable('#rutaCreditoSelect', {
        placeholder: 'Buscar por credito, modelo, VIN...',
        allowClear: true,
        dropdownParent: '#modalRegistrarRuta',
    });
    _trkInicializarSelectBuscable('#rutaTransportistaTracking', {
        placeholder: 'Selecciona transportista',
        allowClear: true,
        dropdownParent: '#modalRegistrarRuta',
        templateResult: _trkTemplateTransportistaSelect2,
        templateSelection: _trkTemplateTransportistaSeleccionado,
        escapeMarkup: markup => markup,
    });
    _trkInicializarSelectBuscable('#unidadTransportistaTracking', {
        placeholder: 'Buscar transportista...',
        allowClear: true,
        dropdownParent: '#modalUnidadTransportistaTracking',
        templateResult: _trkTemplateUnidadTransportistaSelect2,
        templateSelection: _trkTemplateUnidadTransportistaSeleccionado,
        escapeMarkup: markup => markup,
    });
}

function _trkRefrescarSelectBuscable(selector) {
    if (!_trkSelect2Disponible()) return;
    const $el = $(selector);
    if (!$el.length || !$el.hasClass('select2-hidden-accessible')) return;
    $el.trigger('change.select2');
}

function _trkSetBadge(id, value) {
    const text = String(value ?? 0);
    const badge = document.getElementById(id);
    if (badge) badge.textContent = text;
    document.querySelectorAll(`[data-section-count="${id}"]`).forEach(el => { el.textContent = text; });
}

function _trkSeccionesSincronizables() {
    const visibles = Array.isArray(window._trackingVisibleSections)
        ? window._trackingVisibleSections.filter(Boolean)
        : [];
    if (visibles.length) return [...new Set(visibles)];
    return ['creditos', 'borradores', 'rutas', 'catalogos', 'operacion'];
}

function _trkPuedeSincronizarAhora() {
    if (document.querySelector('.modal.show')) return false;
    return document.visibilityState !== 'hidden';
}

function _trkCargarSeccion(key, opts = {}) {
    const force = !!opts.force;
    const silent = !!opts.silent;
    if (_trk.syncInFlight[key]) return _trk.syncInFlight[key];
    let task;
    if (key === 'creditos') {
        task = force
            ? _trkCargarCreditosPaso2(silent)
            : _trkCargarCreditosSiHaceFalta(silent);
    } else if (key === 'borradores') {
        task = force
            ? _trkCargarBorradores(silent)
            : _trkCargarBorradoresSiHaceFalta(silent);
    } else if (key === 'rutas') {
        task = force
            ? _trkCargarRutas(silent)
            : _trkCargarRutasSiHaceFalta(silent);
    } else if (key === 'catalogos') {
        task = (force ? _trkCargarCatalogoAgenciasTransportistas(silent) : _trkCargarCatalogosSiHaceFalta(silent))
            .then(() => _trkRenderCatalogosTracking());
    } else if (key === 'operacion') {
        task = force
            ? _trkCargarOperacionTransportistas(silent)
            : _trkCargarOperacionTransportistasSiHaceFalta(silent);
    } else {
        task = Promise.resolve();
    }
    _trk.syncInFlight[key] = Promise.resolve(task)
        .catch(err => {
            if (!silent) console.warn('[Tracking Recoleccion] No se pudo cargar seccion', key, err);
        })
        .finally(() => { delete _trk.syncInFlight[key]; });
    return _trk.syncInFlight[key];
}

function _trkAbrirLoaderSincronizacion() {
    if (typeof Swal === 'undefined') return false;
    _trk.syncLoaderActivo = true;
    Swal.fire({
        title: 'Sincronizando tracking...',
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando pestanas, contadores y datos recientes del modulo.</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    return true;
}

function _trkCerrarLoaderSincronizacion() {
    if (!_trk.syncLoaderActivo) return;
    _trk.syncLoaderActivo = false;
    if (typeof Swal !== 'undefined' && Swal.isVisible()) {
        Swal.close();
    }
}

function _trkSincronizarSecciones(reason = 'auto', force = true, showLoader = false) {
    if (!_trkPuedeSincronizarAhora()) return Promise.resolve();
    const secciones = _trkSeccionesSincronizables();
    const usarLoader = showLoader && secciones.length > 0 && _trkAbrirLoaderSincronizacion();
    _trk.syncLastAt = Date.now();
    const tasks = secciones.map((key, idx) => new Promise(resolve => {
        setTimeout(() => {
            _trkCargarSeccion(key, { force, silent: true }).finally(resolve);
        }, idx * 500);
    }));
    return Promise.allSettled(tasks).then(() => {
        console.info(`[Tracking Recoleccion] Sincronizacion ${reason}: ${secciones.join(', ')}`);
    }).finally(() => {
        if (usarLoader) _trkCerrarLoaderSincronizacion();
    });
}

function _trkIniciarSincronizacionAutomatica() {
    if (_trk.syncStarted) return;
    _trk.syncStarted = true;
    setTimeout(() => _trkSincronizarSecciones('inicial', false, true), 250);
    _trk.syncTimers.push(setInterval(() => _trkSincronizarSecciones('periodica'), 60000));
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState !== 'visible') return;
        if (Date.now() - (_trk.syncLastAt || 0) > 30000) {
            _trkSincronizarSecciones('visible');
        }
    });
    window.addEventListener('focus', () => {
        if (Date.now() - (_trk.syncLastAt || 0) > 30000) {
            _trkSincronizarSecciones('focus');
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    _trkInicializarFiltros();
    _trkInicializarSelectsBuscables();
    _trkInicializarTablaCreditosDT();
    _trkInicializarTablaRutasDT();
    _trkInicializarTablaBorradorDT();
    _trkInicializarTablasCatalogosDT();
    _trkInicializarRutasVista();
    _trkInicializarBusquedasRutas();
    _trkInicializarCatalogosTrackingUI();
    _trkInicializarModal();

    // Observar cambios de clase en body para refrescar controles del mapa
    new MutationObserver(() => {
        if (!_trk.mapInstance) return;
        _trk.mapInstance.setOptions({ styles: [] });
        _trkActivarTraficoMapa(_trk.mapInstance, 'trafficLayer');
        google.maps.event.trigger(_trk.mapInstance, 'resize');
        if (_trkPicker.mapInstance) {
            _trkPicker.mapInstance.setOptions({ styles: [] });
            _trkActivarTraficoMapa(_trkPicker.mapInstance, 'trafficLayer', _trkPicker);
            google.maps.event.trigger(_trkPicker.mapInstance, 'resize');
        }
        if (_trk.chatMapInstance) {
            _trk.chatMapInstance.setOptions({ styles: document.body.classList.contains('dark-mode') ? _TRK_DARK_MAP_STYLES : [] });
            _trkActivarTraficoMapa(_trk.chatMapInstance, 'chatTrafficLayer');
            google.maps.event.trigger(_trk.chatMapInstance, 'resize');
        }
    }).observe(document.body, { attributeFilter: ['class'] });

    document.getElementById('tabRutasBtn').addEventListener('click', () => _trkCargarSeccion('rutas'));
    document.getElementById('tabBorradorBtn').addEventListener('click', () => _trkCargarSeccion('borradores'));
    document.getElementById('tabCatalogosBtn').addEventListener('click', () => _trkCargarSeccion('catalogos'));
    document.getElementById('tabOperacionBtn')?.addEventListener('click', () => _trkCargarSeccion('operacion'));
    document.getElementById('trkOpBuscar')?.addEventListener('input', e => {
        _trk.operacionBusqueda = e.target.value || '';
        _trkRenderOperacionTransportistas();
    });
    document.getElementById('trkOpFiltroEstatus')?.addEventListener('change', e => {
        _trk.operacionFiltroEstatus = e.target.value || '';
        _trkRenderOperacionTransportistas();
    });
    document.getElementById('trkOpFiltroTipo')?.addEventListener('change', e => {
        _trk.operacionFiltroTipo = e.target.value || '';
        _trkRenderOperacionTransportistas();
    });
    document.getElementById('trkOpVistaLista')?.addEventListener('click', () => _trkSetOperacionVista('lista'));
    document.getElementById('trkOpVistaGrid')?.addEventListener('click', () => _trkSetOperacionVista('grid'));
    document.getElementById('trkOpVistaTabla')?.addEventListener('click', () => _trkSetOperacionVista('tabla'));
    document.getElementById('trkOpActualizar')?.addEventListener('click', () => _trkCargarSeccion('operacion', { force: true }));
    document.getElementById('btnToggleChatMap')?.addEventListener('click', () => _trkToggleChatMapPanel());
    document.getElementById('trkSectionGrid')?.addEventListener('click', ev => {
        const btn = ev.target.closest('.trk-section-card');
        if (!btn) return;
        _trkActivarSeccion(btn.dataset.sectionTarget, btn.dataset.sectionLoad);
    });
    document.getElementById('btnNuevaRuta')?.addEventListener('click', () => _trkAbrirModalNuevo());
    const initialMap = {
        creditos: ['#tabCreditos', 'creditos'],
        borradores: ['#tabBorradores', 'borradores'],
        rutas: ['#tabRutas', 'rutas'],
        catalogos: ['#tabCatalogosTracking', 'catalogos'],
        operacion: ['#tabOperacionTransportistas', 'operacion'],
    };
    const initial = initialMap[window._trackingInitialSection] || initialMap.creditos;
    _trkActivarSeccion(initial[0], initial[1]);
    _trkIniciarSincronizacionAutomatica();

    // Validacion estricta del input de minutos
    const $horaM = document.getElementById('rutaHoraM');
    $horaM.addEventListener('keydown', function (e) {
        // Permitir: backspace, delete, tab, escape, flechas, home, end
        const allowed = ['Backspace','Delete','Tab','Escape','ArrowLeft','ArrowRight','Home','End'];
        if (allowed.includes(e.key)) return;
        // Bloquear todo excepto digitos 0-9
        if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
        }
    });
    $horaM.addEventListener('input', function () {
        // Eliminar cualquier caracter que no sea digito (copia/pega, etc.)
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
                    text: `"${n}" no es valido. Deben ser entre 00 y 59.`,
                    footer: 'Que gracioso...',
                    confirmButtonText: 'Aceptar',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Minutos incorrectos',
                    text: `"${n}" no es valido. Deben ser entre 00 y 59.`,
                    confirmButtonText: 'Aceptar',
                });
            }
        } else {
            this.value = String(n).padStart(2, '0');
            this.classList.remove('is-invalid');
        }
    });
    ['rutaFecha', 'rutaHoraH', 'rutaHoraM', 'rutaHoraAmPm'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', () => {
            if (id === 'rutaFecha') _trkAsegurarEtasFechaMinima();
            _trkAplicarEtasAutomaticas();
            _trkRenderListaCreditos();
            _trkMarcarCambio();
        });
    });

    // La seccion activa carga sus datos bajo demanda y el resto se sincroniza en segundo plano.
});

// --- Filtros ---------------------------------------------
function _trkInicializarFiltros() {
    $('#filtroEstado').on('change', function () {
        const est = $(this).val();
        _trkPoblarFiltroMunicipiosPrincipales(est);
        _trkAplicarFiltrosCreditosLocales();
    });

    $('#filtroMunicipio').on('change', function () {
        _trkAplicarFiltrosCreditosLocales();
    });

    $('#btnLimpiarFiltros').on('click', function () {
        $('#filtroEstado').val('').trigger('change.select2');
        _trkPoblarFiltroMunicipiosPrincipales('');
        if ((_trk.creditosFiltroBase || []).length) {
            _trkAplicarFiltrosCreditosLocales();
        } else {
            _trkCargarCreditosPaso2();
        }
    });
}

function _trkCreditoTieneUbicacionFiltro(c, requiereMunicipio = false) {
    const estado = _trkEstadoMayus(c?.estado, c?.municipio);
    const municipio = _trkMunicipioMayus(c?.municipio, estado);
    if (!estado) return false;
    if (String(estado).toUpperCase().startsWith('SIN ESTADO')) return false;
    if (['NA', 'N/A', 'SIN ESTADO', 'SIN UBICACION', 'NO DISPONIBLE'].includes(_trkNormTxt(estado))) return false;
    if (requiereMunicipio && !municipio) return false;
    if (requiereMunicipio && ['NA', 'N/A', 'SIN MUNICIPIO', 'NO DISPONIBLE'].includes(_trkNormTxt(municipio))) return false;
    return true;
}

function _trkPoblarFiltroEstadosPrincipales(creditos) {
    const actual = $('#filtroEstado').val();
    const conteo = new Map();
    (creditos || []).forEach(c => {
        if (!_trkCreditoTieneUbicacionFiltro(c, true)) return;
        const estado = _trkEstadoMayus(c.estado, c.municipio);
        conteo.set(estado, (conteo.get(estado) || 0) + 1);
    });
    const estados = [...conteo.keys()].sort((a, b) => a.localeCompare(b));
    const $selFiltro = $('#filtroEstado');
    $selFiltro.html('<option value="">Todos los estados</option>');
    estados.forEach(e => {
        $selFiltro.append(`<option value="${_trkChatEscapeHtml(e)}">${_trkChatEscapeHtml(e)} - (${conteo.get(e)})</option>`);
    });
    if (actual && estados.some(e => _trkMismaUbicacion(e, actual))) {
        $selFiltro.val(actual);
    } else {
        $selFiltro.val('');
    }
    _trkRefrescarSelectBuscable('#filtroEstado');
    _trkPoblarFiltroMunicipiosPrincipales($selFiltro.val());
}

function _trkPoblarFiltroMunicipiosPrincipales(estado) {
    const actual = $('#filtroMunicipio').val();
    const $mun = $('#filtroMunicipio');
    $mun.html('<option value="">Todos los municipios</option>');
    if (!estado) {
        $mun.val('').prop('disabled', true);
        _trkRefrescarSelectBuscable('#filtroMunicipio');
        return;
    }
    const conteo = new Map();
    (_trk.creditosFiltroBase || []).forEach(c => {
        if (!_trkCreditoTieneUbicacionFiltro(c, true)) return;
        if (!_trkMismaUbicacionEstado(c.estado, estado, c.municipio)) return;
        const municipio = _trkMunicipioMayus(c.municipio, c.estado);
        if (!municipio) return;
        conteo.set(municipio, (conteo.get(municipio) || 0) + 1);
    });
    const municipios = [...conteo.keys()].sort((a, b) => a.localeCompare(b));
    municipios.forEach(m => {
        $mun.append(`<option value="${_trkChatEscapeHtml(m)}">${_trkChatEscapeHtml(m)} - (${conteo.get(m)})</option>`);
    });
    if (actual && municipios.some(m => _trkMismaUbicacion(m, actual))) {
        $mun.val(actual);
    } else {
        $mun.val('');
    }
    $mun.prop('disabled', municipios.length === 0);
    _trkRefrescarSelectBuscable('#filtroMunicipio');
}

function _trkAplicarFiltrosCreditosLocales() {
    const estado = $('#filtroEstado').val();
    const municipio = $('#filtroMunicipio').val();
    const base = (_trk.creditosFiltroBase || []).length ? _trk.creditosFiltroBase : (_trk.creditosDisponibles || []);
    _trk.creditosDisponibles = base.filter(c => {
        if (estado && !_trkMismaUbicacionEstado(c.estado, estado, c.municipio)) return false;
        if (municipio && !_trkMismaUbicacionMunicipio(_trkMunicipioMayus(c.municipio, c.estado), municipio)) return false;
        return true;
    });
    if (_trk.tablaCreditosDT) {
        _trk.tablaCreditosDT.clear().rows.add(_trk.creditosDisponibles).draw();
    }
    _trkPoblarFiltroEstadosCrd();
    _trkRefrescarSelectCreditos();
    _trkSetBadge('badgeCreditos', _trk.creditosDisponibles.length);
}

function _trkCargarEstados(force = false) {
    if (_trk.cargadoEstados && !force) return Promise.resolve();
    return trkFetch('/TrackingRecoleccion/obtenerCreditosPaso2')
        .then(r => {
            _trk.creditosFiltroBase = (r.datos || []).map(c => ({
                ...c,
                estado_raw: c.estado || '',
                estado: _trkEstadoMayus(c.estado, c.municipio),
                municipio: _trkMunicipioMayus(c.municipio, c.estado),
            }));
            _trkPoblarFiltroEstadosPrincipales(_trk.creditosFiltroBase);
            _trk.cargadoEstados = true;
        });
}

// --- Tabla de creditos ----------------------------------
function _trkRenderLocationBadges(estado, municipio) {
    const est = _trkEstadoMayus(estado, municipio);
    const mun = _trkMunicipioMayus(municipio, est || estado);
    if (!est && !mun) return ' - ';
    const parts = [];
    if (est) parts.push(`<span class="trk-loc-badge trk-loc-estado" title="Estado">${_trkChatEscapeHtml(est)}</span>`);
    if (mun) parts.push(`<span class="trk-loc-badge trk-loc-municipio" title="Municipio">${_trkChatEscapeHtml(mun)}</span>`);
    return `<span class="trk-location-badges">${parts.join('')}</span>`;
}

function _trkDireccionCredito(c) {
    return String(c?.direccion_google || c?.direccion || '').trim();
}

function _trkRenderDireccionCredito(c) {
    const direccion = _trkDireccionCredito(c);
    const tieneCoords = !!(c?.latitud_manual && c?.longitud_manual) || !!(c?.latitud && c?.longitud);
    if (!direccion || !tieneCoords) return '';
    return `<span class="trk-credit-address mt-1" title="${_trkChatEscapeHtml(direccion)}">
        <i class="fa-solid fa-location-dot" style="color:var(--track-color);"></i>
        <span>Direccion: ${_trkChatEscapeHtml(direccion)}</span>
    </span>`;
}

function _trkCreditoModeloTexto(r) {
    return [r?.moto_marca, r?.moto_modelo].filter(Boolean).join(' ') || 'Sin modelo';
}

function _trkCreditoTextoBusqueda(r) {
    return [
        r?.id_credito,
        r?.nombre_cliente,
        r?.estado,
        r?.municipio,
        _trkCreditoModeloTexto(r),
        r?.bin,
        r?.estatus_proceso,
    ].filter(Boolean).join(' ');
}

function _trkRenderCreditoOperacion(r, type) {
    if (type === 'sort' || type === 'type') return parseInt(r?.id_credito, 10) || 0;
    if (type !== 'display') return _trkCreditoTextoBusqueda(r);
    const id = r?.id_credito || '';
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-warning">PENDIENTE</span>
        <div class="trk-borrador-main">#${_trkChatEscapeHtml(id)}</div>
        <div class="trk-borrador-sub"><i class="fa-solid fa-user me-1"></i>${_trkChatEscapeHtml(r?.nombre_cliente || 'Sin cliente')}</div>
    </div>`;
}

function _trkRenderCreditoUbicacion(r, type) {
    if (type !== 'display') return [r?.estado, r?.municipio].filter(Boolean).join(' ');
    return `<div class="trk-borrador-cell">
        <div class="trk-borrador-main"><i class="fa-solid fa-location-dot me-1"></i>${_trkChatEscapeHtml(r?.estado || 'Sin estado')}</div>
        <div class="trk-borrador-divider">
            <div class="trk-borrador-sub">${_trkChatEscapeHtml(r?.municipio || 'Sin municipio')}</div>
        </div>
    </div>`;
}

function _trkRenderCreditoUnidad(r, type) {
    const modelo = _trkCreditoModeloTexto(r);
    if (type !== 'display') return [modelo, r?.bin, r?.estatus_proceso].filter(Boolean).join(' ');
    const estatus = r?.estatus_proceso ? String(r.estatus_proceso).replace(/_/g, ' ') : 'Listo para ruta';
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-info"><i class="fa-solid fa-motorcycle me-1"></i>${_trkChatEscapeHtml(modelo)}</span>
        <div class="trk-borrador-divider">
            <div class="trk-borrador-sub">VIN: ${_trkChatEscapeHtml(r?.bin || 'No disponible')}</div>
            <div class="trk-borrador-muted">${_trkChatEscapeHtml(estatus)}</div>
        </div>
    </div>`;
}

function _trkInicializarTablaCreditosDT() {
    _trk.tablaCreditosDT = $('#tablaCreditos').DataTable({
        language: {
            emptyTable:  'No hay creditos disponibles',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 25,
        deferRender: true,
        responsive: false,
        autoWidth: false,
        order: [[0, 'desc']],
        columns: [
            {
                data: null,
                width: '32%',
                render: (data, type, r) => _trkRenderCreditoOperacion(r, type),
            },
            {
                data: null,
                width: '24%',
                render: (data, type, r) => _trkRenderCreditoUbicacion(r, type),
            },
            {
                data: null,
                width: '32%',
                render: (data, type, r) => _trkRenderCreditoUnidad(r, type),
            },
            {
                data: null,
                width: '12%',
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                render: r => `<button class="btn btn-icon btn-sm rounded-pill btn-label-success trk-action-btn btn-agregar-a-ruta"
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
        _trkAbrirModalAgregarCreditoRuta(cred);
    });

    $('#trkQuickRoutesList').on('click', '.btn-quick-add-ruta', function () {
        _trkConfirmarAgregarCreditoRuta(Number($(this).data('id')));
    });

    $('#trkQuickFiltroEstado').on('change', function () {
        _trk.quickAddEstado = this.value || '';
        _trkRenderQuickRutas();
    });
    $('#detalleRutaBody').on('click', '.btn-cambiar-cedis-destino', function () {
        _trkAbrirModalCambiarCedisDestino(Number($(this).data('id')));
    });
    $('#detalleRutaBody').on('click', '.btn-generar-otp', function () {
        _trkGenerarOtpDetalle(Number($(this).data('id')));
    });
    $('#rutaCedisDestinoInfo').on('click', '.btn-cambiar-cedis-destino', function () {
        _trkAbrirModalCambiarCedisDestino(Number($(this).data('id')));
    });
    $('#trkCambiarCedisMotivo').on('input', function () {
        $('#trkCambiarCedisMotivoCount').text(`${String(this.value || '').length}/200`);
    });
    $('#btnConfirmarCambioCedisDestino').on('click', _trkConfirmarCambioCedisDestino);
}

function _trkCargarCreditosPaso2(silent = false) {
    let url = '/TrackingRecoleccion/obtenerCreditosPaso2';

    return trkFetch(url)
        .then(r => {
            _trk.creditosFiltroBase = (r.datos || []).map(c => ({
                ...c,
                estado_raw: c.estado || '',
                estado: _trkEstadoMayus(c.estado, c.municipio),
                municipio: _trkMunicipioMayus(c.municipio, c.estado),
            }));
            _trkPoblarFiltroEstadosPrincipales(_trk.creditosFiltroBase);
            _trk.cargadoEstados = true;
            // TODO (pendiente autorizacion): descomentar para filtrar solo creditos listos para ruta
            // _trk.creditosDisponibles = _trkFiltrarListosParaRuta(_trk.creditosDisponibles);
            _trkAplicarFiltrosCreditosLocales();
            _trk.cargadoCreditos = true;
        })
        .catch(() => {
            if (!silent) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar creditos.', confirmButtonText: 'Aceptar' });
            }
        });
}

// --- Filtro: solo creditos con estatus "Cierre Documentados" -------------
// Pendiente de autorizacion  -  para activar: descomentar la linea en _trkCargarCreditosPaso2
// Una vez activo, el estatus se mostrara en tabla como "Listo para ruta" en lugar de "Cierre Documentados"
// function _trkFiltrarListosParaRuta(creditos) {
//     return creditos
//         .filter(c => c.estatus_proceso === 'Cierre Documentados')
//         .map(c => ({ ...c, estatus_proceso: 'Listo para ruta' }));
// }

// --- Tabla de rutas -------------------------------------
function _trkRenderUbicacionRuta(raw) {
    if (!raw) return ' - ';
    const map = new Map();
    raw.split('@@').forEach(p => {
        const sep  = p.indexOf('|||');
        const est  = sep >= 0 ? p.slice(0, sep).trim()  : '';
        const mun  = sep >= 0 ? p.slice(sep + 3).trim() : '';
        if (!est) return;
        if (!map.has(est)) map.set(est, new Set());
        if (mun && mun !== '|') map.get(est).add(mun);
    });
    if (!map.size) return ' - ';
    return [...map.entries()]
        .map(([est, munis]) => {
            const munStr = [...munis].filter(Boolean).join(', ');
            return munStr ? `${est} / ${munStr}` : est;
        })
        .join('<br>');
}

function _trkTransportistaRutaData(r) {
    const transportista = (r && r.transportista && typeof r.transportista === 'object') ? r.transportista : {};
    const agenciaObj = (r && r.agencia && typeof r.agencia === 'object') ? r.agencia : {};
    return {
        nombre: r?.nombre_transportista || transportista.nombre_transportista || '',
        tipo: r?.tipo_transportista || transportista.tipo_transportista || '',
        agencia: r?.nombre_agencia || r?.transportista_empresa || agenciaObj.nombre_agencia || agenciaObj.direccion || '',
        direccion: r?.agencia_direccion || agenciaObj.direccion || '',
    };
}

function _trkRenderTransportistaRutaLegacy(r) {
    if (!r || !r.nombre_transportista) return ' - ';
    const info = _trkTransportistaRutaData(r);
    const tipo = info.tipo ? _trkTipoTransportistaBadge(info.tipo) : '';
    const agencia = info.agencia || info.direccion || 'Sin CEDIS';
    return `<div class="d-flex flex-column gap-1 align-items-start">
        <span class="fw-semibold" style="white-space:nowrap;">${_trkChatEscapeHtml(r.nombre_transportista)}</span>
        <span>${tipo}</span>
        ${agencia ? `<small class="text-muted" style="white-space:nowrap;">${_trkChatEscapeHtml(agencia)}</small>` : ''}
    </div>`;
}

function _trkRenderTransportistaRuta(r) {
    const info = _trkTransportistaRutaData(r);
    if (!info.nombre) return '<span class="text-muted">Sin transportista</span>';
    const tipoLabel = String(info.tipo || '').toLowerCase() === 'interno' ? 'Interno' : (String(info.tipo || '').toLowerCase() === 'externo' ? 'Externo' : '');
    const tipoClass = tipoLabel === 'Interno' ? 'bg-success' : 'bg-primary';
    const agencia = info.agencia || info.direccion || 'Sin CEDIS';
    return `<div class="d-flex flex-column align-items-start" style="line-height:1.15;">
        <div class="d-flex align-items-center gap-1 flex-nowrap" style="max-width:100%;">
            <span class="fw-semibold text-truncate" style="font-size:.76rem;max-width:170px;">${_trkChatEscapeHtml(info.nombre)}</span>
            ${tipoLabel ? `<span class="badge ${tipoClass}" style="font-size:.58rem;padding:.18rem .34rem;">${tipoLabel}</span>` : ''}
        </div>
        <small class="text-muted text-truncate" style="font-size:.68rem;max-width:190px;">${_trkChatEscapeHtml(agencia)}</small>
    </div>`;
}

function _trkCargarCreditosSiHaceFalta(silent = false) {
    return _trk.cargadoCreditos ? Promise.resolve() : _trkCargarCreditosPaso2(silent);
}

function _trkRutaCancelable(estatus) {
    if (!window._trackingPuedeCancelarRutas) return false;
    return !['borrador', 'cancelada'].includes(String(estatus || ''));
}

function _trkRutaDebeConsultarEstadoLive(estatus) {
    return ['pendiente_confirmacion', 'lista_envio', 'enviada', 'en_proceso'].includes(String(estatus || ''));
}

function _trkRutaEstaCancelada() {
    return _trk.rutaCancelada || String(_trk.estatusRuta || '') === 'cancelada';
}

function _trkRenderRutaChatBadge(idRuta) {
    const n = parseInt(_trk.chatUnreadPorRuta[String(idRuta)] || 0, 10);
    if (!n) return '';
    return `<span class="trk-route-chat-badge">${n > 9 ? '+9' : n}</span>`;
}

function _trkActualizarRutaChatBadge(idRuta) {
    const $btn = $(`#tablaRutas .btn-abrir-chat[data-id="${idRuta}"], #trkRutasCards .btn-abrir-chat[data-id="${idRuta}"]`);
    if (!$btn.length) return;
    $btn.find('.trk-route-chat-badge').remove();
    const html = _trkRenderRutaChatBadge(idRuta);
    if (html) $btn.append(html);
}

function _trkInicializarRutasVista() {
    document.getElementById('trkRutasFiltros')?.addEventListener('click', ev => {
        const btn = ev.target.closest('.trk-rutas-filter');
        if (!btn) return;
        _trk.rutasFiltro = btn.dataset.estatus || 'todas';
        _trk.rutasPagina = 1;
        document.querySelectorAll('#trkRutasFiltros .trk-rutas-filter').forEach(b => b.classList.toggle('active', b === btn));
        _trkRenderRutasVistaActual();
    });

    document.getElementById('trkVistaCards')?.addEventListener('click', () => _trkSetRutasVista('lista'));
    document.getElementById('trkVistaGrid')?.addEventListener('click', () => _trkSetRutasVista('grid'));
    document.getElementById('trkVistaTabla')?.addEventListener('click', () => _trkSetRutasVista('tabla'));

    document.getElementById('trkRutasCards')?.addEventListener('click', ev => {
        const btn = ev.target.closest('button[data-id]');
        if (!btn) return;
        const idRuta = Number(btn.dataset.id);
        if (btn.classList.contains('btn-editar-ruta')) _trkCargarRutaEnModal(idRuta, false);
        if (btn.classList.contains('btn-ver-ruta')) _trkCargarRutaEnModal(idRuta, true);
        if (btn.classList.contains('btn-abrir-chat')) _trkChatCargarYAbrir(idRuta);
        if (btn.classList.contains('btn-cancelar-ruta')) _trkCancelarRuta(idRuta, String(btn.dataset.nombre || ''));
    });

    document.getElementById('trkRutasPagination')?.addEventListener('click', ev => {
        const btn = ev.target.closest('button[data-page]');
        if (!btn || btn.disabled) return;
        _trk.rutasPagina = Math.max(1, parseInt(btn.dataset.page, 10) || 1);
        _trkRenderRutasCards();
    });
}

function _trkInicializarBusquedasRutas() {
    $('#trkBuscarBorradores').on('input', function () {
        _trk.borradoresBusqueda = this.value || '';
        if (_trk.tablaRutasBorradorDT) {
            _trk.tablaRutasBorradorDT.search(_trk.borradoresBusqueda).draw();
        }
    });
    $('#trkBorradoresLength').on('change', function () {
        if (_trk.tablaRutasBorradorDT) {
            _trk.tablaRutasBorradorDT.page.len(parseInt(this.value, 10) || 25).draw();
        }
    });
    $('#trkFiltroEstadoBorradores').on('change', function () {
        _trk.borradoresFiltroEstado = this.value || '';
        _trk.borradoresFiltroMunicipio = '';
        _trkPoblarFiltroMunicipiosBorradores(_trk.borradoresFiltroEstado);
        _trkAplicarFiltrosBorradores();
    });
    $('#trkFiltroMunicipioBorradores').on('change', function () {
        _trk.borradoresFiltroMunicipio = this.value || '';
        _trkAplicarFiltrosBorradores();
    });
    $('#btnLimpiarFiltrosBorradores').on('click', function () {
        _trk.borradoresFiltroEstado = '';
        _trk.borradoresFiltroMunicipio = '';
        $('#trkFiltroEstadoBorradores').val('').trigger('change.select2');
        _trkPoblarFiltroMunicipiosBorradores('');
        _trkAplicarFiltrosBorradores();
    });

    $('#trkBuscarRutas').on('input', function () {
        _trk.rutasBusqueda = this.value || '';
        _trk.rutasPagina = 1;
        _trkRenderRutasVistaActual();
    });
}

function _trkActivarSeccion(target, loadKey = '') {
    if (!target) return;
    document.querySelectorAll('#trkSectionGrid .trk-section-card').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.sectionTarget === target);
    });
    const trigger = document.querySelector(`#trackMainTabs [data-bs-target="${target}"]`);
    if (trigger && window.bootstrap?.Tab) {
        bootstrap.Tab.getOrCreateInstance(trigger).show();
    } else {
        document.querySelectorAll('.tab-content > .tab-pane').forEach(pane => {
            pane.classList.toggle('show', `#${pane.id}` === target);
            pane.classList.toggle('active', `#${pane.id}` === target);
        });
    }
    _trkCargarSeccion(loadKey);
}

function _trkSetRutasVista(vista) {
    const vistaAnterior = _trk.rutasVista;
    const layoutAnterior = _trk.rutasLayout;
    _trk.rutasVista = vista === 'tabla' ? 'tabla' : 'cards';
    if (vista === 'grid') {
        _trk.rutasLayout = 'grid';
        _trk.rutasPorPagina = 18;
    } else if (vista === 'lista' || vista === 'cards') {
        _trk.rutasLayout = 'lista';
        _trk.rutasPorPagina = 6;
    }
    if (vistaAnterior !== _trk.rutasVista || layoutAnterior !== _trk.rutasLayout) {
        _trk.rutasPagina = 1;
    }
    const cardsWrap = document.getElementById('trkRutasCardsWrap');
    const tablaWrap = document.getElementById('trkRutasTablaWrap');
    const btnCards  = document.getElementById('trkVistaCards');
    const btnGrid   = document.getElementById('trkVistaGrid');
    const btnTabla  = document.getElementById('trkVistaTabla');
    if (cardsWrap) cardsWrap.style.display = _trk.rutasVista === 'cards' ? '' : 'none';
    cardsWrap?.classList.toggle('trk-rutas-board-grid', _trk.rutasVista === 'cards' && _trk.rutasLayout === 'grid');
    if (tablaWrap) tablaWrap.style.display = _trk.rutasVista === 'tabla' ? '' : 'none';
    btnCards?.classList.toggle('active', _trk.rutasVista === 'cards' && _trk.rutasLayout === 'lista');
    btnCards?.classList.toggle('btn-label-primary', _trk.rutasVista === 'cards' && _trk.rutasLayout === 'lista');
    btnCards?.classList.toggle('btn-label-secondary', !(_trk.rutasVista === 'cards' && _trk.rutasLayout === 'lista'));
    btnGrid?.classList.toggle('active', _trk.rutasVista === 'cards' && _trk.rutasLayout === 'grid');
    btnGrid?.classList.toggle('btn-label-primary', _trk.rutasVista === 'cards' && _trk.rutasLayout === 'grid');
    btnGrid?.classList.toggle('btn-label-secondary', !(_trk.rutasVista === 'cards' && _trk.rutasLayout === 'grid'));
    btnTabla?.classList.toggle('active', _trk.rutasVista === 'tabla');
    btnTabla?.classList.toggle('btn-label-primary', _trk.rutasVista === 'tabla');
    btnTabla?.classList.toggle('btn-label-secondary', _trk.rutasVista !== 'tabla');
    _trkRenderRutasVistaActual();
    if (_trk.rutasVista === 'tabla' && _trk.tablaRutasDT) {
        setTimeout(() => {
            _trk.tablaRutasDT.columns.adjust();
            if (_trk.tablaRutasDT.responsive) _trk.tablaRutasDT.responsive.recalc();
        }, 50);
    }
}

function _trkRenderRutasVistaActual() {
    if (_trk.rutasVista === 'tabla') {
        _trkRenderRutasTabla();
        return;
    }
    _trkRenderRutasCards();
}

function _trkRenderRutasTabla() {
    if (!_trk.tablaRutasDT) return;
    _trk.tablaRutasDT.clear().rows.add(_trkRutasFiltradas()).search('').draw();
}

function _trkActualizarResumenRutas(rutas) {
    const counts = {
        todas: rutas.length,
        en_proceso: 0,
        enviada: 0,
        cancelada: 0,
    };
    rutas.forEach(r => {
        const est = String(r.estatus_ruta || '');
        if (Object.prototype.hasOwnProperty.call(counts, est)) counts[est]++;
    });
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = String(value);
    };
    setText('trkRutaCountTodas', counts.todas);
    setText('trkRutaCountProceso', counts.en_proceso);
    setText('trkRutaCountEnviada', counts.enviada);
    setText('trkRutaCountCancelada', counts.cancelada);
}

function _trkRutaTextoBusqueda(r) {
    const transportista = _trkTransportistaRutaData(r);
    return [
        r.id_ruta,
        r.nombre_ruta,
        r.estatus_ruta,
        r.fecha_programada_fmt,
        r.fecha_programada,
        r.fecha_creacion_fmt,
        r.fecha_creacion,
        r.fecha_actualizacion_fmt,
        r.fecha_actualizacion,
        r.hora_inicial,
        r.act_hora_1,
        r.ubicaciones_lista,
        r.creditos_lista,
        r.total_creditos,
        transportista.nombre,
        transportista.tipo,
        transportista.agencia,
        transportista.direccion,
    ].filter(v => v !== null && v !== undefined).join(' ');
}

function _trkFechaCreacionRutaTexto(r) {
    return r?.fecha_creacion_fmt || _trkFormatFechaHora(r?.fecha_creacion) || 'No disponible';
}

function _trkFechaActualizacionRutaTexto(r) {
    return r?.fecha_actualizacion_fmt || _trkFormatFechaHora(r?.fecha_actualizacion) || _trkFechaCreacionRutaTexto(r);
}

function _trkRenderFechasRegistroRuta(r, etiquetaInicio = 'Creado') {
    return `<div class="trk-borrador-muted"><i class="fa-solid fa-circle-play me-1"></i>${_trkChatEscapeHtml(etiquetaInicio)}: ${_trkChatEscapeHtml(_trkFechaCreacionRutaTexto(r))}</div>
        <div class="trk-borrador-muted"><i class="fa-solid fa-clock-rotate-left me-1"></i>Actualizado: ${_trkChatEscapeHtml(_trkFechaActualizacionRutaTexto(r))}</div>`;
}

function _trkRutasFiltradas() {
    const filtro = _trk.rutasFiltro || 'todas';
    const q = _trkNormTxt(_trk.rutasBusqueda || '');
    let rutas = _trk.rutasRegistradas || [];
    if (filtro !== 'todas') {
        rutas = rutas.filter(r => String(r.estatus_ruta || '') === filtro);
    }
    if (q) {
        rutas = rutas.filter(r => _trkNormTxt(_trkRutaTextoBusqueda(r)).includes(q));
    }
    return rutas.slice().sort((a, b) => {
        const aId = parseInt(a.id_ruta, 10) || 0;
        const bId = parseInt(b.id_ruta, 10) || 0;
        return bId - aId;
    });
}

function _trkRutaPorcentaje(r) {
    const total = parseInt(r.total_creditos, 10) || 0;
    if (!total) return 0;
    const conf = parseInt(r.confirmados, 10) || 0;
    const rech = parseInt(r.rechazados, 10) || 0;
    return Math.max(0, Math.min(100, Math.round(((conf + rech) / total) * 100)));
}

function _trkRutaHoraTexto(r) {
    const hi = r.hora_inicial;
    const ha = r.act_hora_1;
    if (!hi && !ha) return 'No disponible';
    return ha ? `${_trkFormatHora(ha)} (actualizada)` : _trkFormatHora(hi);
}

function _trkRenderRutasCards() {
    const wrap = document.getElementById('trkRutasCards');
    if (!wrap) return;
    const rutas = _trkRutasFiltradas();
    const pager = document.getElementById('trkRutasPagination');
    if (!rutas.length) {
        const buscando = String(_trk.rutasBusqueda || '').trim() !== '';
        wrap.innerHTML = `<div class="trk-rutas-empty" style="grid-column:1/-1;">
            <i class="fa-solid fa-route mb-2" style="font-size:1.35rem;color:var(--track-color);"></i>
            <div class="fw-semibold">${buscando ? 'No hay coincidencias' : 'No hay rutas para este filtro'}</div>
            <div class="small mt-1">${buscando ? 'Prueba con otra palabra o limpia la busqueda.' : 'Cambia el estatus o registra una nueva ruta.'}</div>
        </div>`;
        if (pager) pager.innerHTML = '';
        return;
    }
    const porPagina = Math.max(1, parseInt(_trk.rutasPorPagina, 10) || 1);
    const totalPaginas = Math.max(1, Math.ceil(rutas.length / porPagina));
    _trk.rutasPagina = Math.min(Math.max(1, parseInt(_trk.rutasPagina, 10) || 1), totalPaginas);
    const inicio = (_trk.rutasPagina - 1) * porPagina;
    const visibles = rutas.slice(inicio, inicio + porPagina);
    wrap.innerHTML = visibles.map(r => _trkRenderRutaCard(r)).join('');
    _trkRenderRutasPagination(rutas.length, inicio + 1, Math.min(inicio + visibles.length, rutas.length), totalPaginas);
    wrap.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover', html: true });
    });
}

function _trkRenderRutasPagination(total, desde, hasta, totalPaginas) {
    const pager = document.getElementById('trkRutasPagination');
    if (!pager) return;
    const page = _trk.rutasPagina;
    const start = Math.max(1, page - 2);
    const end = Math.min(totalPaginas, start + 4);
    const first = Math.max(1, Math.min(start, Math.max(1, totalPaginas - 4)));
    let pagesHtml = '';
    for (let p = first; p <= end; p++) {
        pagesHtml += `<button type="button" class="btn btn-sm ${p === page ? 'btn-primary' : 'btn-label-secondary'}" data-page="${p}" ${p === page ? 'disabled' : ''}>${p}</button>`;
    }
    pager.innerHTML = `
        <div class="trk-rutas-page-info">
            Rutas ${desde}-${hasta} de ${total}  -  Pagina ${page} de ${totalPaginas}
        </div>
        <div class="trk-rutas-page-actions">
            <button type="button" class="btn btn-sm btn-label-secondary" data-page="1" ${page <= 1 ? 'disabled' : ''} title="Primera">
                <i class="fa-solid fa-angles-left"></i>
            </button>
            <button type="button" class="btn btn-sm btn-label-secondary" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>
                <i class="fa-solid fa-chevron-left me-1"></i>Anterior
            </button>
            ${pagesHtml}
            <button type="button" class="btn btn-sm btn-label-secondary" data-page="${page + 1}" ${page >= totalPaginas ? 'disabled' : ''}>
                Siguiente<i class="fa-solid fa-chevron-right ms-1"></i>
            </button>
        </div>`;
}

function _trkRenderRutaCard(r) {
    const id = r.id_ruta;
    const nombreLimpio = _trkSanitizarNombreRuta(r.nombre_ruta || 'Ruta sin nombre') || 'Ruta sin nombre';
    const estatus = String(r.estatus_ruta || '');
    const total = parseInt(r.total_creditos, 10) || 0;
    const conf = parseInt(r.confirmados, 10) || 0;
    const pend = parseInt(r.pendientes, 10) || 0;
    const rech = parseInt(r.rechazados, 10) || 0;
    const pct = _trkRutaPorcentaje(r);
    const ubicacion = _trkRenderUbicacionRuta(r.ubicaciones_lista);
    const estatusBadge = RUTA_LABEL[estatus] || `<span class="badge bg-secondary">${_trkChatEscapeHtml(estatus || 'Sin estatus')}</span>`;
    const statusLive = pct > 0
        ? `<span class="badge bg-light text-dark border" style="font-size:.68rem;">${pct}%</span>`
        : '';
    const btnCancelar = _trkRutaCancelable(estatus)
        ? `<button class="btn btn-icon btn-sm rounded-pill btn-label-danger trk-action-btn btn-cancelar-ruta"
               data-id="${id}" data-nombre="${_trkChatEscapeHtml(r.nombre_ruta || '')}" title="Cancelar ruta">
               <i class="fa-solid fa-ban"></i>
           </button>`
        : '';
    const btnPrincipal = estatus === 'borrador'
        ? `<button class="btn btn-icon btn-sm rounded-pill btn-label-warning trk-action-btn btn-editar-ruta"
               data-id="${id}" title="Editar ruta">
               <i class="fa-solid fa-pen-to-square"></i>
           </button>`
        : `<button class="btn btn-icon btn-sm rounded-pill btn-label-primary trk-action-btn btn-ver-ruta"
               data-id="${id}" title="Ver detalle">
               <i class="fa-solid fa-eye"></i>
           </button>`;
    return `<article class="trk-ruta-card" data-estatus="${_trkChatEscapeHtml(estatus)}">
        <div class="trk-ruta-card-header">
            <span class="trk-route-folio">#${_trkChatEscapeHtml(id)}</span>
            <div class="trk-ruta-title">${_trkChatEscapeHtml(nombreLimpio)}</div>
            <div class="trk-ruta-subtitle">${ubicacion || 'Sin ubicacion'}</div>
            <div class="trk-ruta-status">
                ${estatusBadge}
                ${statusLive}
            </div>
        </div>
        <div class="trk-ruta-body">
            <div class="trk-ruta-meta">
                <div>
                    <span class="trk-ruta-meta-label">Fecha</span>
                    <span class="trk-ruta-meta-value">${_trkChatEscapeHtml(r.fecha_programada_fmt || 'No disponible')}</span>
                </div>
                <div>
                    <span class="trk-ruta-meta-label">Hora</span>
                    <span class="trk-ruta-meta-value">${_trkChatEscapeHtml(_trkRutaHoraTexto(r))}</span>
                </div>
                <div>
                    <span class="trk-ruta-meta-label">Creado</span>
                    <span class="trk-ruta-meta-value">${_trkChatEscapeHtml(_trkFechaCreacionRutaTexto(r))}</span>
                </div>
                <div>
                    <span class="trk-ruta-meta-label">Actualizado</span>
                    <span class="trk-ruta-meta-value">${_trkChatEscapeHtml(_trkFechaActualizacionRutaTexto(r))}</span>
                </div>
                <div style="grid-column:1/-1;">
                    <span class="trk-ruta-meta-label">Transportista</span>
                    <div class="trk-ruta-meta-value">${_trkRenderTransportistaRuta(r)}</div>
                </div>
            </div>
            <div class="trk-ruta-progress" title="${pct}% avance"><span style="width:${pct}%;"></span></div>
            <div class="trk-ruta-creditos">
                <span>${total} credito${total !== 1 ? 's' : ''}</span>
                <span>${conf} conf. / ${pend} pend. / ${rech} rech.</span>
            </div>
        </div>
        <div class="trk-ruta-actions">
            ${btnPrincipal}
            <button class="btn btn-icon btn-sm rounded-pill btn-label-success trk-action-btn btn-abrir-chat"
                data-id="${id}" title="Chat operativo">
                <i class="fa-solid fa-comments"></i>
                ${_trkRenderRutaChatBadge(id)}
            </button>
            ${btnCancelar}
        </div>
    </article>`;
}

function _trkInicializarTablaRutasDTLegacy() {
    _trk.tablaRutasDT = $('#tablaRutas').DataTable({
        language: {
            emptyTable:  'No hay rutas registradas',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 25,
        deferRender: true,
        responsive: true,
        order: [[0, 'desc']],
        columns: [
            { data: 'id_ruta' },
            { data: 'nombre_ruta' },
            {
                data: null,
                render: r => _trkRenderUbicacionRuta(r.ubicaciones_lista),
            },
            { data: 'fecha_programada_fmt', defaultContent: ' - ' },
            {
                data: null,
                title: 'Hora',
                render: r => {
                    const hi  = r.hora_inicial;
                    const ha1 = r.act_hora_1;
                    if (!hi && !ha1) return ' - ';
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
                render: (v, type, r) => {
                    const base = RUTA_LABEL[v] || `<span class="badge bg-secondary">${v}</span>`;
                    const pct = _trkRutaPorcentaje(r);
                    return pct > 0
                        ? `${base} <span class="badge bg-light text-dark border" style="font-size:.68rem;">${pct}%</span>`
                        : base;
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
                    html += `<span class="badge bg-secondary trk-cred-badge"${ttAttr} style="cursor:default;white-space:nowrap;">${total} credito${total !== 1 ? 's' : ''}</span>`;
                    if (conf > 0) html += `<small class="text-success fw-semibold" style="white-space:nowrap;">${conf} confirmado${conf !== 1 ? 's' : ''}</small>`;
                    if (pend > 0) html += `<small class="text-warning fw-semibold" style="white-space:nowrap;">${pend} pendiente${pend !== 1 ? 's' : ''}</small>`;
                    if (rech > 0) html += `<small class="text-danger  fw-semibold" style="white-space:nowrap;">${rech} rechazado${rech !== 1 ? 's' : ''}</small>`;
                    html += '</div>';
                    return html;
                },
            },
            {
                data: null,
                defaultContent: ' - ',
                render: r => _trkRenderTransportistaRuta(r),
            },
            {
                data: null,
                orderable: false,
                render: r => {
                    if (r.estatus_ruta === 'borrador') {
                        return `<button class="btn btn-icon btn-sm rounded-pill btn-label-warning trk-action-btn btn-editar-ruta"
                           data-id="${r.id_ruta}" title="Editar ruta (borrador)">
                           <i class="fa-solid fa-pen-to-square"></i>
                       </button>`;
                    }
                    const btnCancelar = _trkRutaCancelable(r.estatus_ruta)
                        ? `<button class="btn btn-icon btn-sm rounded-pill btn-label-danger trk-action-btn btn-cancelar-ruta"
                               data-id="${r.id_ruta}" data-nombre="${_trkChatEscapeHtml(r.nombre_ruta || '')}" title="Cancelar ruta">
                               <i class="fa-solid fa-ban"></i>
                           </button>`
                        : '';
                    return `<div class="d-flex gap-1 align-items-center">
                           <button class="btn btn-icon btn-sm rounded-pill btn-label-primary trk-action-btn btn-ver-ruta"
                               data-id="${r.id_ruta}" title="Ver detalle">
                               <i class="fa-solid fa-eye"></i>
                           </button>
                           <button class="btn btn-icon btn-sm rounded-pill btn-label-success trk-action-btn btn-abrir-chat"
                               data-id="${r.id_ruta}" title="Chat operativo">
                               <i class="fa-solid fa-comments"></i>
                               ${_trkRenderRutaChatBadge(r.id_ruta)}
                           </button>
                           ${btnCancelar}
                       </div>`;
                },
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
    $('#tablaRutas').on('click', '.btn-cancelar-ruta', function () {
        _trkCancelarRuta(Number($(this).data('id')), String($(this).data('nombre') || ''));
    });
}

function _trkRenderRutaTablaRuta(r, type) {
    if (type === 'sort' || type === 'type') return parseInt(r?.id_ruta, 10) || 0;
    if (type !== 'display') return _trkRutaTextoBusqueda(r);
    const id = r?.id_ruta || '';
    const estatus = String(r?.estatus_ruta || '');
    const nombre = _trkSanitizarNombreRuta(r?.nombre_ruta || 'Ruta sin nombre') || 'Ruta sin nombre';
    const ubicacion = _trkRenderUbicacionRuta(r?.ubicaciones_lista);
    const estatusBadge = RUTA_LABEL[estatus] || `<span class="badge bg-secondary">${_trkChatEscapeHtml(estatus || 'Sin estatus')}</span>`;
    const pct = _trkRutaPorcentaje(r);
    const pctBadge = pct > 0 ? `<span class="badge bg-light text-dark border" style="font-size:.68rem;">${pct}%</span>` : '';
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-info">RUTA-${_trkChatEscapeHtml(id)}</span>
        <div class="trk-borrador-main">${_trkChatEscapeHtml(nombre)}</div>
        <div class="trk-borrador-sub"><i class="fa-solid fa-location-dot me-1"></i>${ubicacion || 'Sin ubicacion'}</div>
        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">${estatusBadge}${pctBadge}</div>
    </div>`;
}

function _trkRenderRutaTablaSeguimiento(r, type) {
    if (type !== 'display') {
        const info = _trkTransportistaRutaData(r);
        return [
            r?.fecha_programada_fmt,
            _trkHoraBorradorTexto(r),
            r?.fecha_creacion_fmt,
            r?.fecha_creacion,
            r?.fecha_actualizacion_fmt,
            r?.fecha_actualizacion,
            info.nombre,
            info.tipo,
            info.agencia,
            info.direccion,
        ].filter(Boolean).join(' ');
    }
    return `<div class="trk-borrador-cell">
        <div class="trk-borrador-main"><i class="fa-solid fa-calendar-day me-1"></i>${_trkChatEscapeHtml(r?.fecha_programada_fmt || 'Sin fecha')}</div>
        <div class="d-flex flex-wrap align-items-center gap-1">${_trkRenderHoraBorrador(r)}</div>
        <div class="trk-borrador-divider">${_trkRenderFechasRegistroRuta(r)}</div>
        <div class="trk-borrador-divider">${_trkRenderTransportistaRuta(r)}</div>
    </div>`;
}

function _trkRenderRutaTablaCreditos(r, type) {
    const total = parseInt(r?.total_creditos, 10) || 0;
    const conf = parseInt(r?.confirmados, 10) || 0;
    const pend = parseInt(r?.pendientes, 10) || 0;
    const rech = parseInt(r?.rechazados, 10) || 0;
    if (type === 'sort' || type === 'type') return total;
    if (type !== 'display') return `${total} creditos ${conf} confirmados ${pend} pendientes ${rech} rechazados ${r?.creditos_lista || ''}`;
    const lista = (r?.creditos_lista || '').split('||').filter(Boolean).join('<br>');
    const listaAttr = _trkChatEscapeHtml(lista).replace(/&lt;br&gt;/g, '<br>');
    const ttAttr = lista ? ` data-bs-toggle="tooltip" data-bs-placement="right" data-bs-html="true" data-bs-title="${listaAttr}"` : '';
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-success"${ttAttr}>
            <i class="fa-solid fa-motorcycle me-1"></i>${total} credito${total !== 1 ? 's' : ''}
        </span>
        <div class="trk-borrador-divider">
            <div class="trk-borrador-sub"><i class="fa-solid fa-circle-check me-1 text-success"></i>${conf} confirmado${conf !== 1 ? 's' : ''}</div>
            <div class="trk-borrador-sub"><i class="fa-solid fa-clock me-1 text-warning"></i>${pend} pendiente${pend !== 1 ? 's' : ''}</div>
            ${rech ? `<div class="trk-borrador-sub"><i class="fa-solid fa-circle-xmark me-1 text-danger"></i>${rech} rechazado${rech !== 1 ? 's' : ''}</div>` : ''}
        </div>
    </div>`;
}

function _trkRenderRutaTablaAcciones(r) {
    if (r?.estatus_ruta === 'borrador') {
        return `<button class="btn btn-icon btn-sm rounded-pill btn-label-warning trk-action-btn btn-editar-ruta"
                   data-id="${r.id_ruta}" title="Editar ruta (borrador)">
                   <i class="fa-solid fa-pen-to-square"></i>
               </button>`;
    }
    const btnCancelar = _trkRutaCancelable(r?.estatus_ruta)
        ? `<button class="btn btn-icon btn-sm rounded-pill btn-label-danger trk-action-btn btn-cancelar-ruta"
               data-id="${r.id_ruta}" data-nombre="${_trkChatEscapeHtml(r?.nombre_ruta || '')}" title="Cancelar ruta">
               <i class="fa-solid fa-ban"></i>
           </button>`
        : '';
    return `<div class="d-flex gap-1 align-items-center justify-content-center">
        <button class="btn btn-icon btn-sm rounded-pill btn-label-primary trk-action-btn btn-ver-ruta"
            data-id="${r.id_ruta}" title="Ver detalle">
            <i class="fa-solid fa-eye"></i>
        </button>
        <button class="btn btn-icon btn-sm rounded-pill btn-label-success trk-action-btn btn-abrir-chat"
            data-id="${r.id_ruta}" title="Chat operativo">
            <i class="fa-solid fa-comments"></i>
            ${_trkRenderRutaChatBadge(r.id_ruta)}
        </button>
        ${btnCancelar}
    </div>`;
}

// Vista tabla compacta para rutas registradas.
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
        dom: 'rtip',
        pageLength: 25,
        deferRender: true,
        responsive: false,
        autoWidth: false,
        order: [[0, 'desc']],
        columns: [
            {
                data: null,
                width: '32%',
                render: (data, type, r) => _trkRenderRutaTablaRuta(r, type),
            },
            {
                data: null,
                width: '30%',
                render: (data, type, r) => _trkRenderRutaTablaSeguimiento(r, type),
            },
            {
                data: null,
                width: '26%',
                render: (data, type, r) => _trkRenderRutaTablaCreditos(r, type),
            },
            {
                data: null,
                width: '12%',
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                render: r => _trkRenderRutaTablaAcciones(r),
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
    $('#tablaRutas').on('click', '.btn-cancelar-ruta', function () {
        _trkCancelarRuta(Number($(this).data('id')), String($(this).data('nombre') || ''));
    });
}

// --- Estatus live (tracking API) para rutas en_proceso ---------------------
const _TRK_LABEL_API = {
    pendiente:   '<span class="badge badge-trk-pendiente">Pendiente</span>',
    en_proceso:  '<span class="badge badge-trk-en_proceso">En proceso</span>',
    completado:  '<span class="badge badge-trk-completado">Completado</span>',
    confirmado:  '<span class="badge badge-trk-confirmado">Confirmado</span>',
    cancelado:   '<span class="badge badge-trk-cancelado">Cancelado</span>',
};
async function _trkActualizarEstadoCeldaRuta(idRuta) {
    const els = [
        document.getElementById(`trkRutaTrkStatus_${idRuta}`),
        document.getElementById(`trkRutaTrkStatusCard_${idRuta}`),
    ].filter(Boolean);
    if (!els.length) return;
    const setHtml = html => els.forEach(el => { el.innerHTML = html; });
    if (!_trk.trackingApiDisponible && Date.now() < _trk.trackingApiRetryAt) {
        setHtml('<span class="badge bg-label-secondary">Tracking no disponible</span>');
        return;
    }
    try {
        const r = await trkFetch(`/TrackingRecoleccion/trackingEstadoRuta?id_ruta=${idRuta}`);
        if (!r.success || !r.ruta) {
            if (r.servicio_no_disponible || [0, 500, 502, 503, 504].includes(parseInt(r.codigo_http, 10))) {
                _trk.trackingApiDisponible = false;
                _trk.trackingApiRetryAt = Date.now() + 60000;
                setHtml('<span class="badge bg-label-secondary">Tracking no disponible</span>');
                return;
            }
            setHtml('');
            return;
        }
        _trk.trackingApiDisponible = true;
        _trk.trackingApiRetryAt = 0;
        const ruta    = r.ruta;
        const estatus = ruta.estatus_ruta || ruta.estatus || null;
        const pct     = ruta.progreso?.porcentaje ?? null;
        let html = '';
        if (estatus && _TRK_LABEL_API[estatus]) {
            html = _TRK_LABEL_API[estatus];
        } else if (estatus) {
            html = `<span class="badge bg-secondary">${estatus}</span>`;
        }
        if (pct !== null) {
            html += ` <span class="badge bg-light text-dark border" style="font-size:.68rem;">${pct}%</span>`;
        }
        setHtml(html);
    } catch {
        _trk.trackingApiDisponible = false;
        _trk.trackingApiRetryAt = Date.now() + 60000;
        setHtml('<span class="badge bg-label-secondary">Tracking no disponible</span>');
    }
}

function _trkCargarRutas(silent = false) {
    const t0 = performance.now();
    return trkFetch('/TrackingRecoleccion/obtenerRutas', { method: 'POST' })
        .then(r => {
            const rutas = r.datos || [];
            _trk.rutasRegistradas = rutas;
            _trk.rutasPagina = 1;
            _trkActualizarResumenRutas(rutas);
            _trkSetRutasVista(_trk.rutasVista || 'cards');
            _trk.cargadoRutas = true;
            _trkSetBadge('badgeRutas', rutas.length);
            console.info(`[Tracking Recoleccion] Rutas cargadas: ${rutas.length} en ${Math.round(performance.now() - t0)} ms`);
        })
        .catch(() => {
            if (!silent) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar rutas.', confirmButtonText: 'Aceptar' });
            }
        });
}

function _trkCargarRutasSiHaceFalta(silent = false) {
    return _trk.cargadoRutas ? Promise.resolve() : _trkCargarRutas(silent);
}

function _trkRutaPermiteAgregarCredito(ruta, cred) {
    if (!ruta || !cred) return false;
    const estatus = String(ruta.estatus_ruta || '').toLowerCase();
    if (['cancelada', 'completado', 'concluida', 'finalizada'].includes(estatus)) return false;
    return true;
}

function _trkRutasQuickDisponibles(cred) {
    const porId = new Map();
    [...(_trk.rutasRegistradas || []), ...(_trk.borradoresData || [])].forEach(r => {
        const idRuta = Number(r?.id_ruta || r?.id || 0);
        if (!idRuta || porId.has(idRuta)) return;
        porId.set(idRuta, r);
    });
    return [...porId.values()].filter(r => _trkRutaPermiteAgregarCredito(r, cred));
}

function _trkEstadosRutaQuick(ruta) {
    const estados = new Set();
    const agregar = (estado, municipio = '') => {
        const canon = _trkEstadoMayus(estado, municipio);
        if (!canon) return;
        if (['MULTIPLE_ESTADOS', 'VARIOS', 'NACIONAL'].includes(String(canon).toUpperCase())) return;
        estados.add(canon);
    };
    agregar(ruta?.estado, ruta?.municipio);
    agregar(ruta?.estado_origen, ruta?.municipio_origen);
    (ruta?.detalle || []).forEach(det => agregar(det.estado, det.municipio));
    return [...estados].sort((a, b) => a.localeCompare(b));
}

function _trkPoblarQuickFiltroEstados(rutas) {
    const actual = _trk.quickAddEstado || '';
    const estados = new Set();
    rutas.forEach(r => _trkEstadosRutaQuick(r).forEach(e => estados.add(e)));
    const lista = [...estados].sort((a, b) => a.localeCompare(b));
    const $sel = $('#trkQuickFiltroEstado');
    $sel.html('<option value="">Todos los estados</option>');
    lista.forEach(e => $sel.append(`<option value="${_trkChatEscapeHtml(e)}">${_trkChatEscapeHtml(e)}</option>`));
    if (actual && lista.includes(actual)) {
        $sel.val(actual);
    } else {
        _trk.quickAddEstado = '';
        $sel.val('');
    }
}

function _trkQuickDriverHintHtml(ruta, cred) {
    const idTransportista = ruta?.id_transportista || ruta?.transportista?.id_transportista || '';
    const t = idTransportista ? _trkTransportistaConOperacion(idTransportista) : null;
    if (!t || !t.id_transportista) {
        return `<div class="trk-quick-driver-hint small text-muted">
            <i class="fa-solid fa-user-slash me-1"></i>Sin transportista asignado para evaluar capacidad.
        </div>`;
    }
    const evalInfo = _trkEvaluarTransportistaAsignacion(t, { credito: cred });
    const razones = evalInfo.razones.slice(0, 2).map(r => `<span>${_trkChatEscapeHtml(r)}</span>`).join('<span class="mx-1">|</span>');
    return `<div class="trk-quick-driver-hint">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            ${_trkDriverScoreHtml(evalInfo)}
            <span class="small fw-semibold">${_trkChatEscapeHtml(t.nombre_transportista || 'Sin transportista')}</span>
            <span class="small text-muted">${evalInfo.capacidad > 0 ? `${_trkChatEscapeHtml(evalInfo.disponible)} espacios libres` : 'capacidad sin configurar'}</span>
        </div>
        <div class="small text-muted mt-1">${razones}</div>
    </div>`;
}

function _trkQuickRutaHtml(ruta, cred) {
    const idRuta = Number(ruta.id_ruta || ruta.id || 0);
    const nombre = _trkSanitizarNombreRuta(ruta.nombre_ruta || `Ruta #${idRuta}`) || `Ruta #${idRuta}`;
    const estados = _trkEstadosRutaQuick(ruta);
    const estadoTexto = estados.length ? estados.join(' / ') : (_trkEstadoMayus(ruta.estado, ruta.municipio) || 'SIN ESTADO DEFINIDO');
    const tipo = String(ruta.tipo_transportista || ruta.transportista?.tipo_transportista || '').toLowerCase();
    const tipoBadge = tipo === 'interno'
        ? '<span class="badge bg-success ms-1">Interno</span>'
        : (tipo === 'externo' ? '<span class="badge bg-dark ms-1">Externo</span>' : '');
    const estatus = String(ruta.estatus_ruta || '').toLowerCase();
    const estatusBadge = RUTA_LABEL[estatus] || `<span class="badge bg-secondary">${_trkChatEscapeHtml(estatus || 'Sin estatus')}</span>`;
    const total = Number(ruta.total_creditos || ruta.creditos || ruta.total || 0);
    const transportista = ruta.nombre_transportista || ruta.transportista?.nombre_transportista || 'Sin transportista';
    const destino = ruta.cedis_destino_nombre || ruta.cedis_destino?.nombre_agencia || ruta.destino_transportista || '';
    return `
        <div class="trk-quick-route">
            <div style="min-width:0;">
                <div class="d-flex align-items-center gap-1 flex-wrap mb-1">
                    ${estatusBadge}
                    ${tipoBadge}
                    <span class="badge bg-label-primary">${total} credito${total === 1 ? '' : 's'}</span>
                </div>
                <span class="trk-route-folio">#${_trkChatEscapeHtml(idRuta)}</span>
                <div class="trk-quick-route-title">${_trkChatEscapeHtml(nombre)}</div>
                <div class="trk-quick-route-sub">
                    <i class="fa-solid fa-location-dot me-1"></i>${_trkChatEscapeHtml(estadoTexto)}
                    <span class="mx-1">|</span>
                    <i class="fa-solid fa-truck me-1"></i>${_trkChatEscapeHtml(transportista)}
                    ${destino ? `<span class="mx-1">|</span><i class="fa-solid fa-warehouse me-1"></i>${_trkChatEscapeHtml(destino)}` : ''}
                </div>
                ${_trkQuickDriverHintHtml(ruta, cred)}
            </div>
            <button type="button" class="btn btn-sm btn-primary btn-quick-add-ruta" data-id="${idRuta}">
                <i class="fa-solid fa-plus me-1"></i>Agregar
            </button>
        </div>`;
}

function _trkRenderQuickRutas() {
    const cred = _trk.quickAddCredito;
    const filtroEstado = _trk.quickAddEstado || '';
    if (!cred) return;
    const rutasBase = _trkRutasQuickDisponibles(cred);
    const rutas = (filtroEstado
        ? rutasBase.filter(r => _trkEstadosRutaQuick(r).some(e => _trkMismaUbicacion(e, filtroEstado)))
        : rutasBase)
        .map(r => {
            const idTransportista = r?.id_transportista || r?.transportista?.id_transportista || '';
            const evalInfo = idTransportista ? _trkEvaluarTransportistaAsignacion(_trkTransportistaConOperacion(idTransportista), { credito: cred }) : null;
            return { ruta: r, score: evalInfo?.score ?? 0 };
        })
        .sort((a, b) => b.score - a.score)
        .map(x => x.ruta);
    $('#trkQuickRoutesCount').text(rutas.length);
    if (!rutas.length) {
        $('#trkQuickRoutesList').html(`<div class="alert alert-warning small mb-0">
            No hay rutas disponibles${filtroEstado ? ' con el estado seleccionado' : ''}.
        </div>`);
        return;
    }
    $('#trkQuickRoutesList').html(rutas.map(r => _trkQuickRutaHtml(r, cred)).join(''));
}

async function _trkAbrirModalAgregarCreditoRuta(cred) {
    _trk.quickAddCredito = cred;
    _trk.quickAddEstado = '';
    const estado = _trkEstadoMayus(cred.estado, cred.municipio) || 'SIN ESTADO';
    const modelo = [cred.moto_marca, cred.moto_modelo].filter(Boolean).join(' ') || 'Sin modelo';
    $('#trkQuickCreditSummary').html(`
        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
            <div>
                <div class="fw-bold">#${_trkChatEscapeHtml(cred.id_credito)} - ${_trkChatEscapeHtml(cred.nombre_cliente || 'Sin cliente')}</div>
                <div class="small text-muted">
                    <i class="fa-solid fa-motorcycle me-1" style="color:var(--track-color);"></i>${_trkChatEscapeHtml(modelo)}
                    <span class="mx-1">|</span>${_trkRenderLocationBadges(estado, cred.municipio)}
                </div>
            </div>
            <span class="badge bg-label-success">Agregado rapido</span>
        </div>
    `);
    $('#trkQuickRoutesList').html('<div class="text-center py-3 text-muted small"><span class="spinner-border spinner-border-sm me-2"></span>Cargando rutas...</div>');
    $('#trkQuickRoutesCount').text('0');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAgregarCreditoRuta')).show();
    await Promise.allSettled([
        _trkCargarRutasSiHaceFalta(true),
        _trkCargarBorradoresSiHaceFalta(true),
        _trkCargarOperacionTransportistasSiHaceFalta(true),
    ]);
    const rutas = _trkRutasQuickDisponibles(cred);
    _trkPoblarQuickFiltroEstados(rutas);
    _trkRenderQuickRutas();
}

async function _trkConfirmarAgregarCreditoRuta(idRuta) {
    const cred = _trk.quickAddCredito;
    if (!cred || !idRuta) return;
    const ruta = _trkRutasQuickDisponibles(cred).find(r => Number(r.id_ruta || r.id || 0) === Number(idRuta));
    const nombreRuta = ruta?.nombre_ruta || `Ruta #${idRuta}`;
    const idTransportista = ruta?.id_transportista || ruta?.transportista?.id_transportista || '';
    const evalInfo = idTransportista ? _trkEvaluarTransportistaAsignacion(_trkTransportistaConOperacion(idTransportista), { credito: cred }) : null;
    const dictamen = evalInfo ? `
        <div class="mt-2 p-2 rounded border">
            <div class="fw-semibold mb-1">Dictamen preventivo</div>
            <div>${_trkDriverScoreHtml(evalInfo)}</div>
            <div class="text-muted mt-1">${evalInfo.razones.map(r => _trkChatEscapeHtml(r)).join(' | ')}</div>
        </div>` : '';
    const res = await Swal.fire({
        icon: 'question',
        title: 'Agregar credito a ruta?',
        html: `<div class="text-start small">
            <div><b>Credito:</b> #${_trkChatEscapeHtml(cred.id_credito)} - ${_trkChatEscapeHtml(cred.nombre_cliente || '')}</div>
            <div><b>Ruta:</b> ${_trkChatEscapeHtml(nombreRuta)}</div>
            ${dictamen}
            <div class="mt-2 text-muted">Se agregara al final de la ruta y quedara marcado como <b>NUEVO</b>.</div>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Si, agregar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488',
    });
    if (!res.isConfirmed) return;
    Swal.fire({
        title: 'Agregando credito...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    try {
        const r = await trkFetch('/TrackingRecoleccion/agregarCreditoRuta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_ruta: idRuta, id_credito: cred.id_credito }),
        });
        if (!r.success) {
            Swal.fire({ icon: 'error', title: 'No se pudo agregar', text: r.mensaje || r.message || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAgregarCreditoRuta')).hide();
        _trk.cargadoRutas = false;
        _trk.cargadoBorradores = false;
        _trk.cargadoOperacion = false;
        await Promise.allSettled([_trkCargarCreditosPaso2(), _trkCargarRutas(), _trkCargarBorradores(), _trkCargarOperacionTransportistas(true)]);
        const fechaRegistro = _trkFormatFechaHora(r.fecha_agregado) || 'ahora';
        Swal.fire({
            icon: 'success',
            title: 'Credito agregado',
            html: `<div class="small">
                ${_trkChatEscapeHtml(r.message || r.mensaje || 'Se agrego al final de la ruta.')}<br>
                <span class="badge bg-warning text-dark mt-2">NUEVO</span>
                <span class="text-muted">Registro: ${_trkChatEscapeHtml(fechaRegistro)}</span>
            </div>`,
            timer: 2300,
            showConfirmButton: false,
        });
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo agregar el credito a la ruta.', confirmButtonText: 'Aceptar' });
    }
}

async function _trkCancelarRuta(idRuta, nombreRuta = '') {
    if (!idRuta) return;
    if (!window._trackingPuedeCancelarRutas) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin permiso',
            text: 'No tienes permiso para cancelar rutas registradas.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Cancelar ruta',
        html: `<div class="text-start small text-muted mb-2">Ruta: <b>${_trkChatEscapeHtml(nombreRuta || '#' + idRuta)}</b></div>`,
        input: 'textarea',
        inputLabel: 'Motivo de cancelacion',
        inputPlaceholder: 'Describe el motivo...',
        inputAttributes: {
            maxlength: 200,
            rows: 4,
        },
        showCancelButton: true,
        confirmButtonText: 'Si, cancelar ruta',
        cancelButtonText: 'Regresar',
        confirmButtonColor: '#ef4444',
        inputValidator: value => {
            const motivo = String(value || '').trim();
            if (!motivo) return 'El motivo de cancelacion es obligatorio.';
            if (motivo.length > 200) return 'El motivo no puede exceder 200 caracteres.';
            return null;
        },
        didOpen: () => {
            const textarea = Swal.getInput();
            if (!textarea) return;
            const counter = document.createElement('div');
            counter.className = 'text-end text-muted small mt-1';
            counter.textContent = '0 / 200';
            textarea.insertAdjacentElement('afterend', counter);
            textarea.addEventListener('input', () => {
                if (textarea.value.length > 200) textarea.value = textarea.value.slice(0, 200);
                counter.textContent = `${textarea.value.length} / 200`;
            });
        },
    });
    if (!result.isConfirmed) return;

    Swal.fire({
        title: 'Cancelando ruta...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const r = await trkFetch('/TrackingRecoleccion/cancelarRuta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_ruta: idRuta,
                motivo_cancelacion: String(result.value || '').trim(),
            }),
        });
        if (r.success) {
            Swal.fire({ icon: 'success', title: 'Ruta cancelada', text: r.message || 'La ruta se cancelo correctamente.', timer: 1800, showConfirmButton: false });
            _trkCargarRutas();
            _trkCargarCreditosPaso2();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || r.message || 'No se pudo cancelar la ruta.', confirmButtonText: 'Aceptar' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo cancelar la ruta.', confirmButtonText: 'Aceptar' });
    }
}

function _trkStripHtml(html) {
    return String(html || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function _trkHoraBorradorTexto(r) {
    const hi = r?.hora_inicial || '';
    const ha1 = r?.act_hora_1 || '';
    if (!hi && !ha1) return 'Sin hora';
    if (ha1) return `Hora de salida ${_trkFormatHora(ha1)} (original ${_trkFormatHora(hi)})`;
    return `Hora de salida ${_trkFormatHora(hi)}`;
}

function _trkRenderHoraBorrador(r) {
    const hi = r?.hora_inicial || '';
    const ha1 = r?.act_hora_1 || '';
    if (!hi && !ha1) return '<span class="trk-borrador-muted">Sin hora</span>';
    if (ha1) {
        return `<span class="trk-borrador-chip trk-borrador-chip-warning">Hora de salida ${_trkFormatHora(ha1)}</span>
                <span class="trk-borrador-muted text-decoration-line-through">Original ${_trkFormatHora(hi)}</span>`;
    }
    return `<span class="trk-borrador-chip trk-borrador-chip-info">Hora de salida ${_trkFormatHora(hi)}</span>`;
}

function _trkBorradorUbicaciones(r) {
    const map = new Map();
    const add = (estado, municipio) => {
        const est = _trkEstadoMayus(estado, municipio);
        const mun = _trkMunicipioMayus(municipio, est);
        if (!_trkCreditoTieneUbicacionFiltro({ estado: est, municipio: mun }, false)) return;
        const key = `${_trkNormTxt(est)}|${_trkNormTxt(mun)}`;
        if (!map.has(key)) map.set(key, { estado: est, municipio: mun });
    };

    String(r?.ubicaciones_lista || '').split('@@').forEach(p => {
        const sep = p.indexOf('|||');
        if (sep < 0) return;
        add(p.slice(0, sep).trim(), p.slice(sep + 3).trim());
    });
    add(r?.estado, r?.municipio);
    return [...map.values()];
}

function _trkPoblarFiltroEstadosBorradores() {
    const actual = $('#trkFiltroEstadoBorradores').val();
    const conteo = new Map();
    (_trk.borradoresData || []).forEach(r => {
        const estadosRuta = new Set(_trkBorradorUbicaciones(r).map(u => u.estado).filter(Boolean));
        estadosRuta.forEach(e => conteo.set(e, (conteo.get(e) || 0) + 1));
    });
    const estados = [...conteo.keys()].sort((a, b) => a.localeCompare(b));
    const $sel = $('#trkFiltroEstadoBorradores');
    $sel.html('<option value="">Todos los estados</option>');
    estados.forEach(e => {
        $sel.append($('<option>', { value: e, text: `${e} - (${conteo.get(e)})` }));
    });
    if (actual && estados.some(e => _trkMismaUbicacion(e, actual))) {
        $sel.val(actual);
        _trk.borradoresFiltroEstado = actual;
    } else {
        $sel.val('');
        _trk.borradoresFiltroEstado = '';
    }
    _trkRefrescarSelectBuscable('#trkFiltroEstadoBorradores');
    _trkPoblarFiltroMunicipiosBorradores($sel.val());
}

function _trkPoblarFiltroMunicipiosBorradores(estado) {
    const actual = $('#trkFiltroMunicipioBorradores').val();
    const conteo = new Map();
    const $mun = $('#trkFiltroMunicipioBorradores');
    $mun.html('<option value="">Todos los municipios</option>');
    if (!estado) {
        $mun.val('').prop('disabled', true);
        _trk.borradoresFiltroMunicipio = '';
        _trkRefrescarSelectBuscable('#trkFiltroMunicipioBorradores');
        return;
    }

    (_trk.borradoresData || []).forEach(r => {
        const municipiosRuta = new Set();
        _trkBorradorUbicaciones(r).forEach(u => {
            if (!_trkMismaUbicacionEstado(u.estado, estado, u.municipio)) return;
            if (!u.municipio) return;
            municipiosRuta.add(u.municipio);
        });
        municipiosRuta.forEach(m => conteo.set(m, (conteo.get(m) || 0) + 1));
    });
    const municipios = [...conteo.keys()].sort((a, b) => a.localeCompare(b));
    municipios.forEach(m => {
        $mun.append($('<option>', { value: m, text: `${m} - (${conteo.get(m)})` }));
    });
    if (actual && municipios.some(m => _trkMismaUbicacionMunicipio(m, actual))) {
        $mun.val(actual);
        _trk.borradoresFiltroMunicipio = actual;
    } else {
        $mun.val('');
        _trk.borradoresFiltroMunicipio = '';
    }
    $mun.prop('disabled', municipios.length === 0);
    _trkRefrescarSelectBuscable('#trkFiltroMunicipioBorradores');
}

function _trkBorradorCoincideFiltros(r) {
    const estado = _trk.borradoresFiltroEstado || '';
    const municipio = _trk.borradoresFiltroMunicipio || '';
    if (!estado && !municipio) return true;
    return _trkBorradorUbicaciones(r).some(u => {
        if (estado && !_trkMismaUbicacionEstado(u.estado, estado, u.municipio)) return false;
        if (municipio && !_trkMismaUbicacionMunicipio(u.municipio, municipio)) return false;
        return true;
    });
}

function _trkBorradoresFiltrados() {
    return (_trk.borradoresData || []).filter(r => _trkBorradorCoincideFiltros(r));
}

function _trkAplicarFiltrosBorradores() {
    if (!_trk.tablaRutasBorradorDT) return;
    const busquedaActiva = _trk.borradoresBusqueda || _trk.tablaRutasBorradorDT.search() || '';
    _trk.tablaRutasBorradorDT
        .clear()
        .rows.add(_trkBorradoresFiltrados())
        .search(busquedaActiva)
        .draw();
}

function _trkBorradorTextoBusqueda(r) {
    return [
        r?.id_ruta,
        r?.nombre_ruta,
        _trkStripHtml(_trkRenderUbicacionRuta(r?.ubicaciones_lista)),
        r?.fecha_programada_fmt,
        r?.fecha_creacion_fmt,
        r?.fecha_creacion,
        r?.fecha_actualizacion_fmt,
        r?.fecha_actualizacion,
        _trkHoraBorradorTexto(r),
        r?.creditos_lista,
        r?.nombre_transportista,
        r?.nombre_agencia,
        r?.transportista_empresa,
    ].filter(Boolean).join(' ');
}

function _trkRenderBorradorRuta(r, type) {
    if (type === 'sort' || type === 'type') return parseInt(r?.id_ruta, 10) || 0;
    if (type !== 'display') return _trkBorradorTextoBusqueda(r);
    const id = r?.id_ruta || '';
    const nombre = _trkSanitizarNombreRuta(r?.nombre_ruta || 'Ruta sin nombre') || 'Ruta sin nombre';
    const ubicacion = _trkRenderUbicacionRuta(r?.ubicaciones_lista);
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-warning">#${_trkChatEscapeHtml(id)}</span>
        <div class="trk-borrador-main">${_trkChatEscapeHtml(nombre)}</div>
        <div class="trk-borrador-sub"><i class="fa-solid fa-location-dot me-1"></i>${ubicacion}</div>
    </div>`;
}

function _trkRenderBorradorPlaneacion(r, type) {
    if (type !== 'display') {
        return [
            r?.fecha_programada_fmt,
            _trkHoraBorradorTexto(r),
            r?.fecha_creacion_fmt,
            r?.fecha_creacion,
            r?.fecha_actualizacion_fmt,
            r?.fecha_actualizacion,
            r?.nombre_transportista,
            r?.nombre_agencia,
            r?.transportista_empresa,
        ].filter(Boolean).join(' ');
    }
    const transportista = _trkRenderTransportistaRuta(r);
    return `<div class="trk-borrador-cell">
        <div class="trk-borrador-main"><i class="fa-solid fa-calendar-day me-1"></i>${_trkChatEscapeHtml(r?.fecha_programada_fmt || 'Sin fecha')}</div>
        <div class="d-flex flex-wrap align-items-center gap-1">${_trkRenderHoraBorrador(r)}</div>
        <div class="trk-borrador-divider">${_trkRenderFechasRegistroRuta(r, 'Inicio')}</div>
        <div class="trk-borrador-divider">${transportista}</div>
    </div>`;
}

function _trkRenderBorradorCreditos(r, type) {
    const total = parseInt(r?.total_creditos, 10) || 0;
    const conf = parseInt(r?.confirmados, 10) || 0;
    const pend = parseInt(r?.pendientes, 10) || 0;
    const rech = parseInt(r?.rechazados, 10) || 0;
    if (type === 'sort' || type === 'type') return total;
    if (type !== 'display') return `${total} creditos ${conf} confirmados ${pend} pendientes ${rech} rechazados ${r?.creditos_lista || ''}`;
    const lista = (r?.creditos_lista || '').split('||').filter(Boolean).join('<br>');
    const listaAttr = _trkChatEscapeHtml(lista).replace(/&lt;br&gt;/g, '<br>');
    const ttAttr = lista ? ` data-bs-toggle="tooltip" data-bs-placement="right" data-bs-html="true" data-bs-title="${listaAttr}"` : '';
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-success"${ttAttr}>
            <i class="fa-solid fa-motorcycle me-1"></i>${total} credito${total !== 1 ? 's' : ''}
        </span>
        <div class="trk-borrador-divider">
            <div class="trk-borrador-sub"><i class="fa-solid fa-circle-check me-1 text-success"></i>${conf} confirmado${conf !== 1 ? 's' : ''}</div>
            <div class="trk-borrador-sub"><i class="fa-solid fa-clock me-1 text-warning"></i>${pend} pendiente${pend !== 1 ? 's' : ''}</div>
            ${rech ? `<div class="trk-borrador-sub"><i class="fa-solid fa-circle-xmark me-1 text-danger"></i>${rech} rechazado${rech !== 1 ? 's' : ''}</div>` : ''}
        </div>
    </div>`;
}

// --- Tabla de borradores -------------------------------------
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
        dom: 'rtip',
        pageLength: 25,
        deferRender: true,
        responsive: false,
        autoWidth: false,
        order: [[0, 'desc']],
        columns: [
            {
                data: null,
                width: '32%',
                render: (data, type, r) => _trkRenderBorradorRuta(r, type),
            },
            {
                data: null,
                width: '30%',
                render: (data, type, r) => _trkRenderBorradorPlaneacion(r, type),
            },
            {
                data: null,
                width: '26%',
                render: (data, type, r) => _trkRenderBorradorCreditos(r, type),
            },
            {
                data: null,
                width: '12%',
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                render: r => {
                    const nombre = _trkChatEscapeHtml(_trkSanitizarNombreRuta(r?.nombre_ruta || 'Borrador') || 'Borrador');
                    return `<div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-icon btn-sm rounded-pill btn-label-warning trk-action-btn btn-editar-borrador"
                            data-id="${r.id_ruta}" title="Editar borrador">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn btn-icon btn-sm rounded-pill btn-label-danger trk-action-btn btn-eliminar-borrador"
                            data-id="${r.id_ruta}" data-nombre="${nombre}" title="Borrar borrador">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>`;
                },
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
    $('#tablaBorradores').on('click', '.btn-eliminar-borrador', function () {
        _trkEliminarBorrador($(this).data('id'), $(this).data('nombre') || 'Borrador');
    });
}

function _trkCargarBorradores(silent = false) {
    return trkFetch('/TrackingRecoleccion/obtenerBorradores')
        .then(r => {
            const borradores = r.datos || [];
            _trk.borradoresData = borradores;
            _trkPoblarFiltroEstadosBorradores();
            _trkAplicarFiltrosBorradores();
            // Actualizar contador en la pestana
            _trk.cargadoBorradores = true;
            _trkSetBadge('badgeBorradores', borradores.length);
        })
        .catch(err => {
            if (!silent) console.warn('[Tracking Recoleccion] Error al cargar borradores', err);
        });
}

async function _trkEliminarBorrador(idRuta, nombre = 'Borrador') {
    idRuta = parseInt(idRuta, 10);
    if (!idRuta) return;
    const ok = await Swal.fire({
        icon: 'warning',
        title: 'Borrar borrador?',
        html: `<div class="text-start small">
            <div class="mb-2">Esta accion eliminara el borrador seleccionado.</div>
            <div class="fw-semibold">${_trkChatEscapeHtml(nombre)}</div>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Si, borrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
    });
    if (!ok.isConfirmed) return;

    Swal.fire({
        title: 'Eliminando borrador...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    trkFetch('/TrackingRecoleccion/eliminarBorrador', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_ruta: idRuta }),
    }).then(r => {
        if (!r.success) {
            Swal.fire({ icon: 'error', title: 'No se pudo borrar', text: r.mensaje || r.message || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        if (String(_trk.idRutaEditando || '') === String(idRuta)) {
            bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRuta'))?.hide();
            _trkResetModal();
        }
        Swal.fire({ icon: 'success', title: 'Borrador eliminado', timer: 1400, showConfirmButton: false });
        _trkCargarCreditosPaso2();
        _trkCargarBorradores();
        _trkCargarRutas();
    }).catch(() => {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo borrar el borrador.', confirmButtonText: 'Aceptar' });
    });
}

// --- Carga inicial de todos los datos en paralelo ---------
function _trkCargarBorradoresSiHaceFalta(silent = false) {
    return _trk.cargadoBorradores ? Promise.resolve() : _trkCargarBorradores(silent);
}

function _trkTipoTransportistaBadge(tipo) {
    return tipo === 'interno'
        ? '<span class="badge bg-success">Interno</span>'
        : '<span class="badge bg-primary">Externo</span>';
}

function _trkActualizarBadgeTransportista() {
    const t = _trkTransportistaSeleccionado();
    const $badge = $('#rutaTransportistaTipoBadge');
    if (!t) {
        $badge.addClass('d-none').empty();
        return;
    }
    $badge.removeClass('d-none').html(_trkTipoTransportistaBadge(t.tipo_transportista));
}

function _trkInicializarTablasCatalogosDT() {
    _trk.tablaAgenciasDT = $('#tablaAgenciasTracking').DataTable({
        language: {
            emptyTable:  'Sin CEDIS registrados',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 10,
        deferRender: true,
        responsive: true,
        columns: [
            {
                data: null,
                render: a => `<div class="fw-semibold">${_trkChatEscapeHtml(a.nombre_agencia || '')}</div>`,
            },
            {
                data: null,
                render: a => `<div>${_trkChatEscapeHtml([a.estado, a.municipio].filter(Boolean).join(' / ') || '-')}</div>
                    <small class="text-muted">${_trkChatEscapeHtml(a.direccion || '')}</small>`,
            },
            {
                data: null,
                render: a => `<div>${_trkChatEscapeHtml(a.encargado || '-')}</div>
                    <small class="text-muted">${_trkChatEscapeHtml([a.telefono, a.email].filter(Boolean).join('  -  '))}</small>`,
            },
            {
                data: 'activo',
                render: v => Number(v ?? 1) === 1
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>',
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: a => _trkCatalogoAccionesCedis(a),
            },
        ],
    });

    _trk.tablaTransportistasDT = $('#tablaTransportistasTracking').DataTable({
        language: {
            emptyTable:  'Sin transportistas registrados',
            info:        'Mostrando de _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:   'Sin registros para mostrar',
            zeroRecords: 'No se encontraron registros',
            lengthMenu:  'Mostrar _MENU_ registros',
            search:      'Buscar:',
        },
        pageLength: 10,
        deferRender: true,
        responsive: true,
        columns: [
            {
                data: null,
                render: t => `<div class="fw-semibold">${_trkChatEscapeHtml(t.nombre_transportista || '')}</div>
                    <small class="text-muted">${_trkChatEscapeHtml(t.curp_rfc || '')}</small>`,
            },
            { data: 'tipo_transportista', render: v => _trkTipoTransportistaBadge(v) },
            {
                data: null,
                render: t => `<div>${_trkChatEscapeHtml(t.nombre_agencia || t.empresa_origen || '-')}</div>
                    <small class="text-muted">${_trkChatEscapeHtml(t.empresa_origen && t.nombre_agencia ? t.empresa_origen : '')}</small>`,
            },
            {
                data: null,
                render: t => `<div>${_trkChatEscapeHtml(t.telefono || '-')}</div>
                    <small class="text-muted">${_trkChatEscapeHtml(t.email || '')}</small>`,
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: t => _trkCatalogoAccionesTransportista(t),
            },
        ],
    });
}

function _trkInicializarCatalogosTrackingUI() {
    $('#btnNuevoCedisTracking').on('click', () => _trkAbrirModalCedisTracking());
    $('#btnNuevoTransportistaTracking').on('click', () => _trkAbrirModalTransportistaTracking());
    $('#btnNuevaUnidadTracking').on('click', () => _trkAbrirModalUnidadTransportistaTracking());
    $('#btnNuevaUnidadOperacion').on('click', () => _trkAbrirModalUnidadTransportistaTracking());
    $('#btnGuardarCedisTracking').on('click', () => _trkGuardarCedisTracking());
    $('#btnGuardarTransportistaTracking').on('click', () => _trkGuardarTransportistaTracking());
    $('#btnGuardarUnidadTransportistaTracking').on('click', () => _trkGuardarUnidadTransportistaTracking());
    $('#unidadCapacidadTracking').on('input', function () {
        this.value = String(this.value || '').replace(/\D+/g, '').slice(0, 2);
    });
    $('#cedisLinkTracking').on('input blur change', () => _trkAplicarCoordsCedisDesdeLink(false));
    $('#cedisLinkTracking').on('paste', () => setTimeout(() => _trkAplicarCoordsCedisDesdeLink(true), 0));
    $('#trkCatalogoBuscar').on('input', function () {
        _trk.catalogoBusqueda = this.value || '';
        _trkRenderCatalogosTracking();
    });
    $('#tabCatalogosTracking').on('click', '.trk-catalog-view', function () {
        _trk.catalogoVista = this.dataset.view || 'directorio';
        _trkRenderCatalogosTracking();
    });
    $('#tabCatalogosTracking').on('click', '.btn-editar-cedis', function () {
        _trkAbrirModalCedisTracking(Number($(this).data('id')));
    });
    $('#tabCatalogosTracking').on('click', '.btn-editar-transportista', function () {
        _trkAbrirModalTransportistaTracking(Number($(this).data('id')));
    });
    $('#tabCatalogosTracking, #tabOperacionTransportistas').on('click', '.btn-editar-unidad', function () {
        _trkAbrirModalUnidadTransportistaTracking(Number($(this).data('id')));
    });
    $('#tabCatalogosTracking').on('click', '.btn-toggle-cedis', function () {
        _trkCambiarEstadoCedisTracking(Number($(this).data('id')), Number($(this).data('activo')));
    });
    $('#tabCatalogosTracking').on('click', '.btn-toggle-transportista', function () {
        _trkCambiarEstadoTransportistaTracking(Number($(this).data('id')), Number($(this).data('activo')));
    });
    $('#transportistaCedisTracking').on('change', () => _trkCatalogoSincronizarTipoTransportista());
    $('#unidadTransportistaTracking').on('change', function () {
        _trkPrecargarUnidadTransportista(Number(this.value || 0));
    });
}

function _trkCatalogoFiltradoTexto(item, tipo) {
    if (tipo === 'cedis') {
        return [
            item.nombre_agencia,
            item.clave_agencia,
            _trkUbicacionMayus(item.estado),
            _trkUbicacionMayus(item.municipio),
            item.direccion,
            item.encargado,
            item.email,
            item.telefono,
        ].filter(Boolean).join(' ');
    }
    return [
        item.nombre_transportista,
        item.tipo_transportista,
        item.nombre_agencia,
        item.empresa_origen,
        item.curp_rfc,
        item.email,
        item.telefono,
        item.puesto,
        item.username,
    ].filter(Boolean).join(' ');
}

function _trkCatalogoDatosFiltrados() {
    const q = _trkNormTxt(_trk.catalogoBusqueda || '');
    const agencias = (_trk.agenciasTracking || []).filter(a => {
        if (!q) return true;
        return _trkNormTxt(_trkCatalogoFiltradoTexto(a, 'cedis')).includes(q);
    });
    const transportistas = (_trk.transportistasTracking || []).filter(t => {
        if (!q) return true;
        return _trkNormTxt(_trkCatalogoFiltradoTexto(t, 'transportista')).includes(q);
    });
    return { agencias, transportistas };
}

function _trkCatalogoEstadoBadge(activo) {
    return Number(activo ?? 1) === 1
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-secondary">Inactivo</span>';
}

function _trkCatalogoAccionesCedis(a) {
    const activo = Number(a?.activo ?? 1) === 1 ? 0 : 1;
    const title = activo ? 'Activar CEDIS' : 'Desactivar CEDIS';
    const icon = activo ? 'fa-toggle-off' : 'fa-toggle-on';
    const klass = activo ? 'btn-label-secondary' : 'btn-label-danger';
    return `<div class="d-flex justify-content-end gap-1">
        <button type="button" class="btn btn-icon btn-sm rounded-pill btn-label-primary trk-action-btn btn-editar-cedis"
            data-id="${_trkChatEscapeHtml(a?.id_agencia || '')}" title="Editar CEDIS">
            <i class="fa-solid fa-pen-to-square"></i>
        </button>
        <button type="button" class="btn btn-icon btn-sm rounded-pill ${klass} trk-action-btn btn-toggle-cedis"
            data-id="${_trkChatEscapeHtml(a?.id_agencia || '')}" data-activo="${activo}" title="${title}">
            <i class="fa-solid ${icon}"></i>
        </button>
    </div>`;
}

function _trkCatalogoAccionesTransportista(t) {
    const activo = Number(t?.activo ?? 1) === 1 ? 0 : 1;
    const title = activo ? 'Activar transportista' : 'Desactivar transportista';
    const icon = activo ? 'fa-toggle-off' : 'fa-toggle-on';
    const klass = activo ? 'btn-label-secondary' : 'btn-label-danger';
    return `<div class="d-flex justify-content-end gap-1">
        <button type="button" class="btn btn-icon btn-sm rounded-pill btn-label-primary trk-action-btn btn-editar-transportista"
            data-id="${_trkChatEscapeHtml(t?.id_transportista || '')}" title="Editar transportista">
            <i class="fa-solid fa-pen-to-square"></i>
        </button>
        <button type="button" class="btn btn-icon btn-sm rounded-pill btn-label-info trk-action-btn btn-editar-unidad"
            data-id="${_trkChatEscapeHtml(t?.id_transportista || '')}" title="Unidad y capacidad">
            <i class="fa-solid fa-truck"></i>
        </button>
        <button type="button" class="btn btn-icon btn-sm rounded-pill ${klass} trk-action-btn btn-toggle-transportista"
            data-id="${_trkChatEscapeHtml(t?.id_transportista || '')}" data-activo="${activo}" title="${title}">
            <i class="fa-solid ${icon}"></i>
        </button>
    </div>`;
}

function _trkCatalogoCedisCard(a, compacto = false) {
    const ubicacion = [a.estado, a.municipio, a.codigo_postal ? `CP ${a.codigo_postal}` : ''].filter(Boolean).join(' / ') || 'Sin ubicacion';
    const contacto = [a.telefono, a.email].filter(Boolean).join(' - ') || 'Sin contacto';
    return `<article class="trk-catalog-card">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
                <span class="badge bg-primary mb-1"><i class="fa-solid fa-warehouse me-1"></i>CEDIS</span>
                <div class="trk-catalog-card-title">${_trkChatEscapeHtml(a.nombre_agencia || 'Sin nombre')}</div>
                <div class="trk-catalog-card-sub">ID ${_trkChatEscapeHtml(a.id_agencia || '-')} / ${_trkChatEscapeHtml(a.clave_agencia || 'Sin clave')}</div>
            </div>
            ${_trkCatalogoEstadoBadge(a.activo)}
        </div>
        <div class="trk-catalog-card-sub"><strong>Ubicacion:</strong> ${_trkChatEscapeHtml(ubicacion)}</div>
        ${compacto ? '' : `<div class="trk-catalog-card-sub"><strong>Direccion:</strong> ${_trkChatEscapeHtml(a.direccion || 'No disponible')}</div>`}
        <div class="trk-catalog-card-sub"><strong>Contacto:</strong> ${_trkChatEscapeHtml(contacto)}</div>
        ${compacto ? '' : `<div class="trk-catalog-card-sub"><strong>Encargado:</strong> ${_trkChatEscapeHtml(a.encargado || 'Sin encargado')}</div>`}
        <div class="trk-catalog-card-actions">${_trkCatalogoAccionesCedis(a)}</div>
    </article>`;
}

function _trkCatalogoTransportistaCard(t) {
    const cedis = t.nombre_agencia || t.empresa_origen || 'Sin CEDIS';
    const contacto = [t.telefono, t.email].filter(Boolean).join(' - ') || 'Sin contacto';
    return `<article class="trk-catalog-card">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
                ${_trkTipoTransportistaBadge(t.tipo_transportista)}
                <div class="trk-catalog-card-title mt-1">${_trkChatEscapeHtml(t.nombre_transportista || 'Sin nombre')}</div>
                <div class="trk-catalog-card-sub">${_trkChatEscapeHtml(cedis)}</div>
            </div>
            ${_trkCatalogoEstadoBadge(t.activo)}
        </div>
        <div class="trk-catalog-card-sub"><strong>Contacto:</strong> ${_trkChatEscapeHtml(contacto)}</div>
        <div class="trk-catalog-card-sub"><strong>CURP/RFC:</strong> ${_trkChatEscapeHtml(t.curp_rfc || 'No disponible')}</div>
        <div class="trk-catalog-card-sub"><strong>Puesto:</strong> ${_trkChatEscapeHtml(t.puesto || 'No disponible')}</div>
        <div class="trk-catalog-card-actions">${_trkCatalogoAccionesTransportista(t)}</div>
    </article>`;
}

function _trkRenderCatalogoDirectorio(agencias, transportistas) {
    const cards = [
        ...agencias.map(a => _trkCatalogoCedisCard(a, true)),
        ...transportistas.map(t => _trkCatalogoTransportistaCard(t)),
    ];
    $('#trkCatalogoDirectorio').html(cards.length
        ? cards.join('')
        : '<div class="trk-catalog-empty">Sin registros para mostrar</div>');
}

function _trkRenderCatalogoAgrupado(agencias, transportistas) {
    const grupos = agencias.map(a => {
        const relacionados = transportistas.filter(t => String(t.id_agencia || '') === String(a.id_agencia || ''));
        return `<section class="trk-catalog-group">
            <div class="trk-catalog-group-head">
                <div>
                    <span class="badge bg-primary mb-1"><i class="fa-solid fa-warehouse me-1"></i>CEDIS</span>
                    <div class="trk-catalog-group-title">${_trkChatEscapeHtml(a.nombre_agencia || 'Sin nombre')}</div>
                    <div class="trk-catalog-card-sub">ID ${_trkChatEscapeHtml(a.id_agencia || '-')} / ${_trkChatEscapeHtml(a.clave_agencia || 'Sin clave')}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-primary">${relacionados.length} transportista${relacionados.length !== 1 ? 's' : ''}</span>
                    ${_trkCatalogoAccionesCedis(a)}
                </div>
            </div>
            <div class="trk-catalog-group-body">
                ${relacionados.length ? relacionados.map(_trkCatalogoTransportistaCard).join('') : '<div class="trk-catalog-empty">Sin transportistas asignados</div>'}
            </div>
        </section>`;
    });
    const sinCedis = transportistas.filter(t => !t.id_agencia);
    if (sinCedis.length) {
        grupos.push(`<section class="trk-catalog-group">
            <div class="trk-catalog-group-head">
                <div>
                    <span class="badge bg-secondary mb-1">Sin CEDIS</span>
                    <div class="trk-catalog-group-title">Transportistas sin CEDIS asignado</div>
                </div>
                <span class="badge bg-label-secondary">${sinCedis.length}</span>
            </div>
            <div class="trk-catalog-group-body">${sinCedis.map(_trkCatalogoTransportistaCard).join('')}</div>
        </section>`);
    }
    $('#trkCatalogoAgrupado').html(grupos.length
        ? grupos.join('')
        : '<div class="trk-catalog-empty">Sin grupos para mostrar</div>');
}

function _trkSetCatalogoVista(vista) {
    _trk.catalogoVista = ['directorio', 'agrupado', 'tabla'].includes(vista) ? vista : 'directorio';
    $('.trk-catalog-view').each(function () {
        const active = this.dataset.view === _trk.catalogoVista;
        this.classList.toggle('active', active);
        this.classList.toggle('btn-label-primary', active);
        this.classList.toggle('btn-label-secondary', !active);
    });
    $('#trkCatalogoDirectorio').toggleClass('d-none', _trk.catalogoVista !== 'directorio');
    $('#trkCatalogoAgrupado').toggleClass('d-none', _trk.catalogoVista !== 'agrupado');
    $('#trkCatalogoTabla').toggleClass('d-none', _trk.catalogoVista !== 'tabla');
}

function _trkCedisCatalogoPorId(id) {
    return (_trk.agenciasTracking || []).find(a => String(a.id_agencia) === String(id)) || null;
}

function _trkTransportistaCatalogoPorId(id) {
    return (_trk.transportistasTracking || []).find(t => String(t.id_transportista) === String(id)) || null;
}

function _trkCatalogoLlenarSelectCedis(selected = '') {
    const $sel = $('#transportistaCedisTracking');
    $sel.html('<option value="">Sin CEDIS asignado</option>');
    (_trk.agenciasTracking || []).forEach(a => {
        $sel.append($('<option>', {
            value: a.id_agencia,
            text: a.nombre_agencia || `CEDIS ${a.id_agencia}`,
        }));
    });
    $sel.val(selected ? String(selected) : '');
    _trkCatalogoSincronizarTipoTransportista();
}

function _trkCatalogoSincronizarTipoTransportista() {
    const cedis = _trkCedisCatalogoPorId($('#transportistaCedisTracking').val());
    const tipo = cedis && _trkEsCedisDestinoInternoPermitido(cedis) ? 'interno' : 'externo';
    $('#transportistaTipoTracking').val(tipo);
}

function _trkExtraerCoordsGoogleMaps(link) {
    const raw = String(link || '').trim();
    if (!raw) return null;
    const variantes = [raw];
    try {
        variantes.push(decodeURIComponent(raw));
    } catch (_) {}
    for (const txt of variantes) {
        const exacta = txt.match(/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/i);
        if (exacta) {
            return { lat: exacta[1], lng: exacta[2], fuente: 'place' };
        }
    }
    for (const txt of variantes) {
        const centro = txt.match(/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)(?:,|z|\/)/i);
        if (centro) {
            return { lat: centro[1], lng: centro[2], fuente: 'map' };
        }
    }
    return null;
}

function _trkAplicarCoordsCedisDesdeLink(mostrarAviso = false) {
    const link = $('#cedisLinkTracking').val();
    const coords = _trkExtraerCoordsGoogleMaps(link);
    const $status = $('#cedisLinkCoordStatus');
    if (!String(link || '').trim()) {
        $status.removeClass('text-success text-warning').text('');
        $('#cedisLinkTracking').removeClass('is-valid is-invalid');
        return false;
    }
    if (!coords) {
        $('#cedisLinkTracking').removeClass('is-valid').toggleClass('is-invalid', mostrarAviso);
        $status
            .removeClass('text-success')
            .addClass('text-warning')
            .text(mostrarAviso ? 'No se detectaron coordenadas en este link.' : '');
        return false;
    }
    $('#cedisLatTracking').val(coords.lat);
    $('#cedisLngTracking').val(coords.lng);
    $('#cedisLinkTracking').removeClass('is-invalid').addClass('is-valid');
    $status
        .removeClass('text-warning')
        .addClass('text-success')
        .text(`Coordenadas detectadas automaticamente: ${coords.lat}, ${coords.lng}`);
    return true;
}

function _trkAbrirModalCedisTracking(id = 0) {
    const a = id ? _trkCedisCatalogoPorId(id) : null;
    $('#modalCedisTrackingLabel').html(`<i class="fa-solid fa-warehouse me-2" style="color:var(--track-color);"></i>${a ? 'Editar CEDIS' : 'Registrar CEDIS'}`);
    $('#cedisIdTracking').val(a?.id_agencia || '');
    $('#cedisNombreTracking').val(a?.nombre_agencia || '');
    $('#cedisClaveTracking').val(a?.clave_agencia || '');
    $('#cedisDireccionTracking').val(a?.direccion || '');
    $('#cedisEstadoTracking').val(_trkUbicacionMayus(a?.estado || ''));
    $('#cedisMunicipioTracking').val(_trkUbicacionMayus(a?.municipio || ''));
    $('#cedisCpTracking').val(a?.codigo_postal || '');
    $('#cedisLatTracking').val(a?.latitud ?? '');
    $('#cedisLngTracking').val(a?.longitud ?? '');
    $('#cedisLinkTracking').val(a?.link_ubicacion || '');
    $('#cedisLinkCoordStatus').removeClass('text-success text-warning').text('');
    $('#cedisLinkTracking').removeClass('is-valid is-invalid');
    $('#cedisTelefonoTracking').val(a?.telefono || '');
    $('#cedisEncargadoTracking').val(a?.encargado || '');
    $('#cedisEmailTracking').val(a?.email || '');
    $('#cedisHorarioTracking').val(a?.horario || '');
    $('#cedisActivoTracking').prop('checked', Number(a?.activo ?? 1) === 1);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCedisTracking')).show();
}

function _trkAbrirModalTransportistaTracking(id = 0) {
    const t = id ? _trkTransportistaCatalogoPorId(id) : null;
    $('#modalTransportistaTrackingLabel').html(`<i class="fa-solid fa-id-card-clip me-2" style="color:var(--track-color);"></i>${t ? 'Editar Transportista' : 'Registrar Transportista'}`);
    $('#transportistaIdTracking').val(t?.id_transportista || '');
    $('#transportistaNombreTracking').val(t?.nombre_transportista || '');
    $('#transportistaCurpTracking').val(t?.curp_rfc || '');
    $('#transportistaTelefonoTracking').val(t?.telefono || '');
    $('#transportistaEmailTracking').val(t?.email || '');
    $('#transportistaEmpresaTracking').val(t?.empresa_origen || '');
    $('#transportistaPuestoTracking').val(t?.puesto || '');
    $('#transportistaUsernameTracking').val(t?.username || '');
    $('#transportistaPasswordTracking').val('');
    $('#transportistaActivoTracking').prop('checked', Number(t?.activo ?? 1) === 1);
    _trkCatalogoLlenarSelectCedis(t?.id_agencia || '');
    if (t?.tipo_transportista) $('#transportistaTipoTracking').val(t.tipo_transportista);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTransportistaTracking')).show();
}

function _trkOperacionTransportistaPorId(id) {
    return (_trk.operacionTransportistas || []).find(t => String(t.id_transportista) === String(id)) || null;
}

function _trkTransportistaConOperacion(tOrId) {
    const id = typeof tOrId === 'object'
        ? (tOrId?.id_transportista || tOrId?.id)
        : tOrId;
    const base = typeof tOrId === 'object' ? (tOrId || {}) : (_trkTransportistaPorId(id) || {});
    const op = _trkOperacionTransportistaPorId(id) || {};
    return {
        ...base,
        ...op,
        cedis_base: { ...(base.cedis_base || {}), ...(op.cedis_base || {}) },
        unidad: { ...(base.unidad || {}), ...(op.unidad || {}) },
    };
}

function _trkEstadosDesdeCreditos(creditos = []) {
    return [...new Set((creditos || [])
        .map(c => _trkEstadoMayus(c.estado, c.municipio))
        .filter(Boolean))];
}

function _trkEstadosDesdeRutaOperacion(ruta) {
    if (!ruta) return [];
    const estados = new Set();
    const agregar = (estado, municipio = '') => {
        const canon = _trkEstadoMayus(estado, municipio);
        if (canon) estados.add(canon);
    };
    agregar(ruta.estado, ruta.municipio);
    const raw = String(ruta.ubicaciones_lista || '');
    if (raw.includes('@@')) {
        raw.split('@@').forEach(par => {
            const [estado, municipio] = par.split('|||');
            agregar(estado, municipio);
        });
    }
    if (ruta.cedis_destino_estado) agregar(ruta.cedis_destino_estado, ruta.cedis_destino_municipio);
    return [...estados];
}

function _trkEstadosOperacionTransportista(t) {
    const estados = new Set();
    (t?.rutas || []).forEach(r => _trkEstadosDesdeRutaOperacion(r).forEach(e => estados.add(e)));
    const baseEstado = _trkEstadoMayus(t?.cedis_base?.estado, t?.cedis_base?.municipio);
    if (baseEstado) estados.add(baseEstado);
    return [...estados];
}

function _trkCruceEstados(estadosA, estadosB) {
    const b = new Set((estadosB || []).map(e => _trkNormTxt(e)));
    return (estadosA || []).filter(e => b.has(_trkNormTxt(e)));
}

function _trkEvaluarTransportistaAsignacion(tRaw, opts = {}) {
    const t = _trkTransportistaConOperacion(tRaw);
    const creditos = Array.isArray(opts.creditos) ? opts.creditos : (_trk.creditosEnRuta || []);
    const creditoExtra = opts.credito ? [opts.credito] : [];
    const creditosPlan = [...creditos, ...creditoExtra].filter(Boolean);
    const cargaNueva = Math.max(0, creditosPlan.length + Number(opts.creditosExtra || 0));
    const capacidad = Number(t.capacidad_total || 0);
    const proyectada = Number(t.capacidad_proyectada || 0);
    const disponible = capacidad > 0
        ? Math.max(0, Number(t.capacidad_disponible ?? (capacidad - proyectada)))
        : null;
    const estatus = String(t.estatus_operativo || (t.rutas_activas > 0 ? 'en_ruta' : 'disponible')).toLowerCase();
    const razones = [];
    let score = 55;
    let bloqueo = false;

    if (Number(t.activo ?? 1) !== 1) {
        score = 0;
        bloqueo = true;
        razones.push('Transportista inactivo.');
    }

    if (capacidad <= 0) {
        score -= 18;
        razones.push('Capacidad de unidad no configurada.');
    } else if (cargaNueva > 0 && disponible < cargaNueva) {
        score -= 45;
        bloqueo = true;
        razones.push(`Sin cupo suficiente: disponible ${disponible} / requerido ${cargaNueva}.`);
    } else if (cargaNueva > 0 && disponible - cargaNueva <= 2) {
        score -= 12;
        razones.push(`Cupo ajustado: quedarian ${Math.max(0, disponible - cargaNueva)} espacios.`);
    } else if (capacidad > 0) {
        score += 18;
        razones.push(`Cupo disponible ${disponible}${cargaNueva ? ` / requerido ${cargaNueva}` : ''}.`);
    }

    if (estatus === 'disponible') {
        score += 18;
        razones.push('Sin ruta activa/programada.');
    } else if (estatus === 'en_ruta') {
        score += 10;
        razones.push('En ruta: evaluar si la recoleccion queda al paso.');
    } else if (estatus === 'programado') {
        score -= 8;
        razones.push('Tiene ruta programada.');
    } else if (estatus === 'advertencia') {
        score -= 14;
        razones.push('Operacion en advertencia.');
    } else if (estatus === 'saturado') {
        score -= 35;
        bloqueo = true;
        razones.push('Capacidad proyectada al limite.');
    }

    const tipo = String(t.tipo_transportista || '').toLowerCase();
    const creditosFueraZona = creditosPlan.filter(c => !_trkEsZonaInterna(c.estado, c.municipio));
    if (tipo === 'interno' && creditosFueraZona.length) {
        score -= 45;
        bloqueo = true;
        razones.push('Interno fuera de CDMX/zona metropolitana.');
    }

    const idDestino = opts.idCedisDestino || $('#rutaCedisDestino').val();
    const cedisDestino = idDestino ? _trkCedisPorId(idDestino) : null;
    if (tipo === 'interno' && cedisDestino && !_trkEsCedisDestinoInternoPermitido(cedisDestino)) {
        score -= 35;
        bloqueo = true;
        razones.push('Destino no permitido para transportista interno.');
    }

    const estadosPlan = opts.estadosPlan || _trkEstadosDesdeCreditos(creditosPlan);
    const estadosOperacion = _trkEstadosOperacionTransportista(t);
    const cruce = _trkCruceEstados(estadosPlan, estadosOperacion);
    if (cruce.length) {
        score += 10;
        razones.push(`Coincide con zona operativa: ${cruce.slice(0, 2).join(', ')}.`);
    }

    const rutaActiva = t.ruta_activa || (t.rutas || []).find(r => String(r.estatus_ruta || '').toLowerCase() === 'en_proceso') || null;
    if (rutaActiva?.cedis_destino_nombre) {
        razones.push(`Destino actual: ${rutaActiva.cedis_destino_nombre}.`);
    } else if (t.cedis_base?.nombre) {
        razones.push(`CEDIS base: ${t.cedis_base.nombre}.`);
    }

    score = Math.max(0, Math.min(100, Math.round(score)));
    let nivel = 'warning';
    let etiqueta = 'Revisar';
    if (bloqueo || score < 45) {
        nivel = 'danger';
        etiqueta = 'No ideal';
    } else if (score >= 80) {
        nivel = 'success';
        etiqueta = 'Recomendado';
    } else if (score >= 65) {
        nivel = 'info';
        etiqueta = 'Viable';
    }

    return {
        t,
        score,
        nivel,
        etiqueta,
        razones: [...new Set(razones)].slice(0, 4),
        capacidad,
        disponible,
        cargaNueva,
        estatus,
    };
}

function _trkDriverScoreHtml(evalInfo) {
    const icon = evalInfo.nivel === 'success' ? 'circle-check'
        : evalInfo.nivel === 'danger' ? 'triangle-exclamation'
        : evalInfo.nivel === 'info' ? 'circle-info'
        : 'clock';
    return `<span class="trk-driver-score ${_trkChatEscapeHtml(evalInfo.nivel)}">
        <i class="fa-solid fa-${icon}"></i>${_trkChatEscapeHtml(evalInfo.etiqueta)} ${_trkChatEscapeHtml(evalInfo.score)}
    </span>`;
}

function _trkDriverMiniHtml(evalInfo) {
    const t = evalInfo.t || {};
    const status = _trkOperacionStatusLabel(evalInfo.estatus);
    const capacidad = evalInfo.capacidad > 0
        ? `${evalInfo.disponible} libres de ${evalInfo.capacidad}`
        : 'capacidad sin configurar';
    const rutas = `${Number(t.rutas_activas || 0)} activas / ${Number(t.rutas_programadas || 0)} programadas`;
    return `<div class="trk-driver-mini">
        <i class="fa-solid fa-truck me-1"></i>${_trkChatEscapeHtml(status)}
        <span class="mx-1">|</span>${_trkChatEscapeHtml(capacidad)}
        <span class="mx-1">|</span>${_trkChatEscapeHtml(rutas)}
    </div>`;
}

function _trkTransportistasSugeridosRuta() {
    const candidatos = _trkTransportistasFiltrados()
        .map(t => _trkEvaluarTransportistaAsignacion(t, { creditos: _trk.creditosEnRuta }))
        .sort((a, b) => {
            if (b.score !== a.score) return b.score - a.score;
            return String(a.t.nombre_transportista || '').localeCompare(String(b.t.nombre_transportista || ''));
        });
    return candidatos;
}

function _trkRenderSugerenciasTransportistasRuta() {
    const $box = $('#rutaTransportistaAssist');
    if (!$box.length || _trk.soloLectura || _trkRutaEstaCancelada()) {
        $box.addClass('d-none').empty();
        return;
    }
    if (!_trk.operacionTransportistas.length) {
        $box.removeClass('d-none').html(`
            <div class="small text-muted">
                <span class="spinner-border spinner-border-sm me-1"></span>
                Consultando disponibilidad de transportistas...
            </div>`);
        return;
    }
    const sugeridos = _trkTransportistasSugeridosRuta().slice(0, 3);
    const carga = _trk.creditosEnRuta.length;
    if (!sugeridos.length) {
        $box.removeClass('d-none').html('<div class="small text-muted">Sin transportistas activos para sugerir.</div>');
        return;
    }
    const cards = sugeridos.map(evalInfo => {
        const t = evalInfo.t || {};
        const empresa = t.cedis_base?.nombre || t.nombre_agencia || t.empresa_origen || 'Sin CEDIS';
        const razones = evalInfo.razones.map(r => `<div>${_trkChatEscapeHtml(r)}</div>`).join('');
        return `<div class="trk-driver-suggestion">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div style="min-width:0;">
                    <div class="name">${_trkChatEscapeHtml(t.nombre_transportista || 'Sin nombre')}</div>
                    <div class="trk-driver-mini">${_trkTipoTransportistaBadge(t.tipo_transportista)} ${_trkChatEscapeHtml(empresa)}</div>
                </div>
                ${_trkDriverScoreHtml(evalInfo)}
            </div>
            ${_trkDriverMiniHtml(evalInfo)}
            <div class="trk-driver-reasons">${razones}</div>
            <button type="button" class="btn btn-xs btn-label-primary mt-2 btn-usar-transportista-sugerido"
                    data-id="${_trkChatEscapeHtml(t.id_transportista || '')}">
                <i class="fa-solid fa-user-check me-1"></i>Usar
            </button>
        </div>`;
    }).join('');
    $box.removeClass('d-none').html(`
        <div class="trk-driver-assist-head">
            <div>
                <div class="trk-driver-assist-title">
                    <i class="fa-solid fa-shield-halved me-1"></i>Asistente preventivo
                </div>
                <div class="small text-muted">Sugerencias con capacidad, rutas activas, destino y zona operativa.</div>
            </div>
            <span class="badge bg-label-primary">${_trkChatEscapeHtml(carga)} credito${carga === 1 ? '' : 's'}</span>
        </div>
        <div class="trk-driver-suggestions">${cards}</div>
    `);
}

function _trkTransportistasUnidadBase() {
    const map = new Map();
    (_trk.transportistasTracking || []).forEach(t => {
        if (t?.id_transportista) map.set(String(t.id_transportista), t);
    });
    (_trk.operacionTransportistas || []).forEach(t => {
        if (!t?.id_transportista) return;
        const key = String(t.id_transportista);
        map.set(key, { ...(map.get(key) || {}), ...t });
    });
    return Array.from(map.values()).sort((a, b) => String(a.nombre_transportista || '').localeCompare(String(b.nombre_transportista || '')));
}

function _trkUnidadTransportistaPorId(id) {
    if (!id) return null;
    return _trkTransportistasUnidadBase().find(t => String(t.id_transportista) === String(id)) || null;
}

function _trkTemplateUnidadTransportistaSelect2(item) {
    if (!item.id) return _trkChatEscapeHtml(item.text || '');
    const t = _trkUnidadTransportistaPorId(item.id);
    if (!t) return _trkChatEscapeHtml(item.text || '');
    const empresa = t.nombre_agencia || t.empresa_origen || t.cedis_base?.nombre || 'Sin CEDIS';
    const contacto = [t.telefono, t.email].filter(Boolean).join(' - ');
    const unidad = _trkOperacionUnidadTexto(t);
    return `<div class="d-flex flex-column" style="line-height:1.2;">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            ${_trkTipoTransportistaBadge(t.tipo_transportista)}
            <span class="fw-semibold">${_trkChatEscapeHtml(t.nombre_transportista || 'Sin nombre')}</span>
        </div>
        <small class="text-muted">${_trkChatEscapeHtml(empresa)}${contacto ? ' - ' + _trkChatEscapeHtml(contacto) : ''}</small>
        <small class="text-muted">${_trkChatEscapeHtml(unidad)}</small>
    </div>`;
}

function _trkTemplateUnidadTransportistaSeleccionado(item) {
    if (!item.id) return _trkChatEscapeHtml(item.text || '');
    const t = _trkUnidadTransportistaPorId(item.id);
    if (!t) return _trkChatEscapeHtml(item.text || '');
    const empresa = t.nombre_agencia || t.empresa_origen || t.cedis_base?.nombre || '';
    return _trkChatEscapeHtml(`${t.nombre_transportista || 'Sin nombre'}${empresa ? ' - ' + empresa : ''}`);
}

function _trkCatalogoLlenarSelectUnidadTransportistas(selected = '') {
    const $sel = $('#unidadTransportistaTracking');
    $sel.html('<option value="">Selecciona transportista</option>');
    _trkTransportistasUnidadBase().forEach(t => {
        const empresa = t.nombre_agencia || t.empresa_origen || t.cedis_base?.nombre || 'Sin CEDIS';
        const busqueda = [
            t.nombre_transportista,
            empresa,
            t.tipo_transportista,
            t.telefono,
            t.email,
            t.unidad?.tipo_unidad,
            t.unidad?.marca,
            t.unidad?.modelo,
            t.unidad?.placa,
            t.unidad?.numero_economico,
        ].filter(Boolean).join(' - ');
        $sel.append($('<option>', {
            value: t.id_transportista,
            text: busqueda || `${t.nombre_transportista || 'Sin nombre'} - ${empresa}`,
        }));
    });
    $sel.val(selected ? String(selected) : '');
    _trkRefrescarSelectBuscable('#unidadTransportistaTracking');
}

function _trkLimpiarUnidadTransportista() {
    $('#unidadIdCapacidadTracking').val('');
    $('#unidadTipoTracking').val('');
    $('#unidadCapacidadTracking').val('');
    $('#unidadEconomicoTracking').val('');
    $('#unidadMarcaTracking').val('');
    $('#unidadModeloTracking').val('');
    $('#unidadAnioTracking').val('');
    $('#unidadPlacaTracking').val('');
    $('#unidadColorTracking').val('');
    $('#unidadVigenciaSeguroTracking').val('');
    $('#unidadSerieTracking').val('');
    $('#unidadMotorTracking').val('');
    $('#unidadAseguradoraTracking').val('');
    $('#unidadPolizaTracking').val('');
    $('#unidadObservacionesTracking').val('');
    $('#unidadActivoTracking').prop('checked', true);
}

function _trkPrecargarUnidadTransportista(idTransportista) {
    _trkLimpiarUnidadTransportista();
    const t = _trkOperacionTransportistaPorId(idTransportista) || _trkTransportistaCatalogoPorId(idTransportista);
    const u = t?.unidad || {};
    $('#unidadIdCapacidadTracking').val(u.id_capacidad || '');
    $('#unidadTipoTracking').val(u.tipo_unidad || t?.tipo_unidad || '');
    $('#unidadCapacidadTracking').val(t?.capacidad_total || '');
    $('#unidadEconomicoTracking').val(u.numero_economico || '');
    $('#unidadMarcaTracking').val(u.marca || '');
    $('#unidadModeloTracking').val(u.modelo || '');
    $('#unidadAnioTracking').val(u.anio || '');
    $('#unidadPlacaTracking').val(u.placa || '');
    $('#unidadColorTracking').val(u.color || '');
    $('#unidadVigenciaSeguroTracking').val(u.vigencia_seguro || '');
    $('#unidadSerieTracking').val(u.numero_serie || '');
    $('#unidadMotorTracking').val(u.numero_motor || '');
    $('#unidadAseguradoraTracking').val(u.aseguradora || '');
    $('#unidadPolizaTracking').val(u.poliza_seguro || '');
    $('#unidadObservacionesTracking').val(u.observaciones || '');
    $('#unidadActivoTracking').prop('checked', Number(u.activo ?? 1) === 1);
}

function _trkAbrirModalUnidadTransportistaTracking(idTransportista = 0) {
    const abrir = () => {
        _trkCatalogoLlenarSelectUnidadTransportistas(idTransportista || '');
        _trkPrecargarUnidadTransportista(Number(idTransportista || 0));
        const t = idTransportista ? (_trkOperacionTransportistaPorId(idTransportista) || _trkTransportistaCatalogoPorId(idTransportista)) : null;
        $('#modalUnidadTransportistaTrackingLabel').html(`<i class="fa-solid fa-truck me-2" style="color:var(--track-color);"></i>${t ? 'Editar Unidad' : 'Registrar Unidad'}`);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUnidadTransportistaTracking')).show();
    };

    const requiereOperacion = idTransportista && !_trkOperacionTransportistaPorId(idTransportista);
    const promesa = requiereOperacion || !_trk.cargadoOperacion
        ? _trkCargarOperacionTransportistas(true).catch(() => {})
        : Promise.resolve();
    promesa.then(abrir);
}

function _trkPayloadCedisTracking() {
    return {
        id_agencia: $('#cedisIdTracking').val() || 0,
        nombre_agencia: $('#cedisNombreTracking').val(),
        clave_agencia: $('#cedisClaveTracking').val(),
        tipo_ubicacion: 'agencia',
        direccion: $('#cedisDireccionTracking').val(),
        estado: _trkUbicacionMayus($('#cedisEstadoTracking').val()),
        municipio: _trkUbicacionMayus($('#cedisMunicipioTracking').val()),
        codigo_postal: $('#cedisCpTracking').val(),
        latitud: $('#cedisLatTracking').val(),
        longitud: $('#cedisLngTracking').val(),
        link_ubicacion: $('#cedisLinkTracking').val(),
        telefono: $('#cedisTelefonoTracking').val(),
        encargado: $('#cedisEncargadoTracking').val(),
        email: $('#cedisEmailTracking').val(),
        horario: $('#cedisHorarioTracking').val(),
        activo: $('#cedisActivoTracking').is(':checked') ? 1 : 0,
    };
}

function _trkPayloadTransportistaTracking() {
    return {
        id_transportista: $('#transportistaIdTracking').val() || 0,
        id_agencia: $('#transportistaCedisTracking').val() || null,
        tipo_transportista: $('#transportistaTipoTracking').val() || 'externo',
        nombre_transportista: $('#transportistaNombreTracking').val(),
        curp_rfc: $('#transportistaCurpTracking').val(),
        email: $('#transportistaEmailTracking').val(),
        telefono: $('#transportistaTelefonoTracking').val(),
        empresa_origen: $('#transportistaEmpresaTracking').val(),
        puesto: $('#transportistaPuestoTracking').val(),
        username: $('#transportistaUsernameTracking').val(),
        password: $('#transportistaPasswordTracking').val(),
        activo: $('#transportistaActivoTracking').is(':checked') ? 1 : 0,
    };
}

function _trkPayloadUnidadTransportistaTracking() {
    return {
        id_capacidad: $('#unidadIdCapacidadTracking').val() || 0,
        id_transportista: $('#unidadTransportistaTracking').val() || 0,
        tipo_unidad: $('#unidadTipoTracking').val(),
        capacidad_motos: $('#unidadCapacidadTracking').val(),
        numero_economico: _trkUbicacionMayus($('#unidadEconomicoTracking').val()),
        marca: _trkUbicacionMayus($('#unidadMarcaTracking').val()),
        modelo: _trkUbicacionMayus($('#unidadModeloTracking').val()),
        anio: $('#unidadAnioTracking').val(),
        placa: _trkUbicacionMayus($('#unidadPlacaTracking').val()),
        color: _trkUbicacionMayus($('#unidadColorTracking').val()),
        vigencia_seguro: $('#unidadVigenciaSeguroTracking').val(),
        numero_serie: _trkUbicacionMayus($('#unidadSerieTracking').val()),
        numero_motor: _trkUbicacionMayus($('#unidadMotorTracking').val()),
        aseguradora: _trkUbicacionMayus($('#unidadAseguradoraTracking').val()),
        poliza_seguro: _trkUbicacionMayus($('#unidadPolizaTracking').val()),
        observaciones: $('#unidadObservacionesTracking').val(),
        activo: $('#unidadActivoTracking').is(':checked') ? 1 : 0,
    };
}

function _trkGuardarCatalogo(url, payload, btn, modalId) {
    const $btn = $(btn);
    $btn.prop('disabled', true);
    return trkFetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    }).then(r => {
        if (!r.success) {
            Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: r.message || r.mensaje || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById(modalId))?.hide();
        Swal.fire({ icon: 'success', title: 'Listo', text: r.message || r.mensaje || 'Registro guardado.', timer: 1500, showConfirmButton: false });
        _trk.cargadoCatalogos = false;
        _trkCargarSeccion('catalogos', { force: true, silent: true });
    }).catch(() => {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo guardar el registro.', confirmButtonText: 'Aceptar' });
    }).finally(() => $btn.prop('disabled', false));
}

function _trkGuardarCedisTracking() {
    _trkAplicarCoordsCedisDesdeLink(false);
    const payload = _trkPayloadCedisTracking();
    if (!String(payload.nombre_agencia || '').trim()) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre del CEDIS es obligatorio.', confirmButtonText: 'Aceptar' });
        return;
    }
    _trkGuardarCatalogo('/TrackingRecoleccion/guardarAgenciaTracking', payload, '#btnGuardarCedisTracking', 'modalCedisTracking');
}

function _trkGuardarTransportistaTracking() {
    const payload = _trkPayloadTransportistaTracking();
    if (!String(payload.nombre_transportista || '').trim()) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre del transportista es obligatorio.', confirmButtonText: 'Aceptar' });
        return;
    }
    _trkGuardarCatalogo('/TrackingRecoleccion/guardarTransportistaTracking', payload, '#btnGuardarTransportistaTracking', 'modalTransportistaTracking');
}

function _trkGuardarUnidadTransportistaTracking() {
    const payload = _trkPayloadUnidadTransportistaTracking();
    if (!Number(payload.id_transportista || 0)) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Selecciona un transportista.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!String(payload.tipo_unidad || '').trim()) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El tipo de unidad es obligatorio.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!Number(payload.capacidad_motos || 0)) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La capacidad debe ser mayor a cero.', confirmButtonText: 'Aceptar' });
        return;
    }

    const $btn = $('#btnGuardarUnidadTransportistaTracking');
    $btn.prop('disabled', true);
    trkFetch('/TrackingRecoleccion/guardarUnidadTransportistaTracking', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    }).then(r => {
        if (!r.success) {
            Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: r.message || r.mensaje || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('modalUnidadTransportistaTracking'))?.hide();
        Swal.fire({ icon: 'success', title: 'Listo', text: r.message || r.mensaje || 'Unidad guardada.', timer: 1500, showConfirmButton: false });
        _trk.cargadoOperacion = false;
        _trkCargarSeccion('operacion', { force: true, silent: true });
    }).catch(() => {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo guardar la unidad.', confirmButtonText: 'Aceptar' });
    }).finally(() => $btn.prop('disabled', false));
}

function _trkCambiarEstadoCedisTracking(id, activo) {
    _trkCambiarEstadoCatalogo('/TrackingRecoleccion/cambiarEstadoAgenciaTracking', { id_agencia: id, activo });
}

function _trkCambiarEstadoTransportistaTracking(id, activo) {
    _trkCambiarEstadoCatalogo('/TrackingRecoleccion/cambiarEstadoTransportistaTracking', { id_transportista: id, activo });
}

function _trkCambiarEstadoCatalogo(url, payload) {
    trkFetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    }).then(r => {
        if (!r.success) {
            Swal.fire({ icon: 'error', title: 'No se pudo actualizar', text: r.message || r.mensaje || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        _trk.cargadoCatalogos = false;
        _trkCargarSeccion('catalogos', { force: true, silent: true });
    }).catch(() => {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo actualizar el registro.', confirmButtonText: 'Aceptar' });
    });
}

function _trkCargarCatalogoAgenciasTransportistas(silent = false) {
    return Promise.all([
        trkFetch('/TrackingRecoleccion/obtenerCatalogoAgenciasTransportistas').catch(() => ({ success: false, datos: {} })),
        trkFetch('/TrackingRecoleccion/obtenerCedisTracking').catch(() => ({ success: false, datos: {} })),
    ]).then(([catalogoResp, cedisResp]) => {
            const datos = catalogoResp.datos || {};
            const cedisApi = _trkExtraerCedisTracking(cedisResp);
            const cedisLocal = _trkFiltrarCedisActivos(datos.agencias || []);
            _trk.agenciasTracking = (cedisApi.length ? cedisApi : cedisLocal).map(a => ({
                ...a,
                estado: _trkUbicacionMayus(a?.estado || ''),
                municipio: _trkUbicacionMayus(a?.municipio || ''),
            }));
            _trk.transportistasTracking = datos.transportistas || [];
            _trkPoblarAgenciasTrackingSelect();
            _trkRefrescarSelectTransportistas();
            _trk.cargadoCatalogos = true;
        })
        .catch(err => {
            if (!silent) console.warn('[Tracking Recoleccion] Error al cargar catalogos', err);
        });
}

function _trkFiltrarCedisActivos(cedis) {
    return (cedis || []).filter(a => String(a?.activo ?? '1') !== '0' && a?.activo !== false);
}

function _trkExtraerCedisTracking(resp) {
    if (!resp || resp.success === false) return [];
    const datos = resp.datos || resp.data || resp;
    const cedis = datos.cedis || datos.agencias || (Array.isArray(datos) ? datos : []);
    return _trkFiltrarCedisActivos(Array.isArray(cedis) ? cedis : []);
}

function _trkCargarCatalogosSiHaceFalta(silent = false) {
    return _trk.cargadoCatalogos
        ? Promise.resolve()
        : _trkCargarCatalogoAgenciasTransportistas(silent).catch(() => {});
}

function _trkPoblarAgenciasTrackingSelect() {
    const $sel = $('#rutaAgenciaTracking');
    const $dest = $('#rutaCedisDestino');
    const selected = $sel.val();
    const selectedDest = $dest.val();
    $sel.html('<option value="">Selecciona CEDIS</option>');
    $dest.html('<option value="">Sin destino asignado</option>');
    const cedis = _trkCedisActivos();
    const cedisDestino = _trkCedisDestinoFiltrados($('#rutaTipoTransportista').val());
    cedis.forEach(a => {
        const label = `${a.nombre_agencia || ''}${_trkCedisTieneUbicacionOperativa(a) ? '' : ' - SIN UBICACION'}`;
        $sel.append(`<option value="${a.id_agencia}">${_trkChatEscapeHtml(label)}</option>`);
    });
    cedisDestino.forEach(a => {
        const label = `${a.nombre_agencia || ''}${_trkCedisTieneUbicacionOperativa(a) ? '' : ' - SIN UBICACION'}`;
        $dest.append(`<option value="${a.id_agencia}">${_trkChatEscapeHtml(label)}</option>`);
    });
    if (selected) $sel.val(selected);
    if (selectedDest && cedisDestino.some(a => String(a.id_agencia) === String(selectedDest))) {
        $dest.val(selectedDest);
    }
    _trkRenderCedisDestinoInfo();
}

function _trkRenderCatalogosTracking() {
    const agenciasTotal = _trk.agenciasTracking || [];
    const transportistasTotal = _trk.transportistasTracking || [];
    const { agencias, transportistas } = _trkCatalogoDatosFiltrados();
    const internos = transportistasTotal.filter(t => t.tipo_transportista === 'interno').length;
    const externos = transportistasTotal.filter(t => t.tipo_transportista === 'externo').length;

    $('#statAgenciasTracking').text(agenciasTotal.filter(a => Number(a.activo ?? 1) === 1).length);
    $('#statTransportistasInternos').text(internos);
    $('#statTransportistasExternos').text(externos);
    $('#statCatalogoTotal').text(agenciasTotal.length + transportistasTotal.length);
    _trkSetBadge('badgeCatalogos', agenciasTotal.length + transportistasTotal.length);
    _trkSetCatalogoVista(_trk.catalogoVista);
    _trkRenderCatalogoDirectorio(agencias, transportistas);
    _trkRenderCatalogoAgrupado(agencias, transportistas);

    if (_trk.tablaAgenciasDT) {
        _trk.tablaAgenciasDT.clear().rows.add(agencias).draw();
    }
    if (_trk.tablaTransportistasDT) {
        _trk.tablaTransportistasDT.clear().rows.add(transportistas).draw();
    }
    setTimeout(() => {
        _trk.tablaAgenciasDT?.columns.adjust();
        _trk.tablaAgenciasDT?.responsive?.recalc();
        _trk.tablaTransportistasDT?.columns.adjust();
        _trk.tablaTransportistasDT?.responsive?.recalc();
    }, 120);
}

function _trkCargarOperacionTransportistas(silent = false) {
    const $grid = $('#trkOpGrid');
    if (!silent) {
        $grid.html('<div class="trk-admin-empty"><span class="spinner-border spinner-border-sm me-2"></span>Cargando operacion de transportistas...</div>');
        $('#trkOpTablaBody').html('<tr><td colspan="6" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Cargando operacion de transportistas...</td></tr>');
    }
    return trkFetch('/TrackingRecoleccion/obtenerOperacionTransportistas')
        .then(r => {
            if (!r.success) {
                throw new Error(r.message || r.mensaje || 'No se pudo cargar la operacion.');
            }
            const datos = r.datos || {};
            _trk.operacionResumen = datos.resumen || {};
            _trk.operacionTransportistas = Array.isArray(datos.transportistas) ? datos.transportistas : [];
            _trk.operacionLiveLoaded = {};
            _trk.operacionLiveInfo = {};
            _trkSetBadge('badgeOperacionTransportistas', _trk.operacionTransportistas.length);
            _trkRenderOperacionTransportistas();
            _trkRefrescarSelectTransportistas($('#rutaTransportistaTracking').val());
            _trkRenderTransportistaInfo();
            _trkRenderSugerenciasTransportistasRuta();
            _trk.cargadoOperacion = true;
            _trkCargarUbicacionesOperacion();
        })
        .catch(err => {
            if (!silent) {
                const msg = _trkChatEscapeHtml(err.message || 'No se pudo cargar la operacion.');
                $grid.html(`<div class="trk-admin-empty text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>${msg}</div>`);
                $('#trkOpTablaBody').html(`<tr><td colspan="6" class="text-center text-danger py-4"><i class="fa-solid fa-triangle-exclamation me-1"></i>${msg}</td></tr>`);
            }
        });
}

function _trkCargarOperacionTransportistasSiHaceFalta(silent = false) {
    return _trk.cargadoOperacion
        ? Promise.resolve().then(() => _trkRenderOperacionTransportistas())
        : _trkCargarOperacionTransportistas(silent);
}

function _trkSetOperacionVista(vista) {
    _trk.operacionVista = ['lista', 'grid', 'tabla'].includes(vista) ? vista : 'grid';
    _trkRenderOperacionTransportistas();
}

function _trkAplicarOperacionVista() {
    const vista = ['lista', 'grid', 'tabla'].includes(_trk.operacionVista) ? _trk.operacionVista : 'grid';
    _trk.operacionVista = vista;
    const esTabla = vista === 'tabla';
    $('#trkOpGridWrap').toggleClass('d-none', esTabla);
    $('#trkOpTablaWrap').toggleClass('d-none', !esTabla);
    $('#trkOpGrid').toggleClass('is-list', vista === 'lista');

    const botones = {
        lista: document.getElementById('trkOpVistaLista'),
        grid: document.getElementById('trkOpVistaGrid'),
        tabla: document.getElementById('trkOpVistaTabla'),
    };
    Object.entries(botones).forEach(([key, btn]) => {
        if (!btn) return;
        const active = key === vista;
        btn.classList.toggle('active', active);
        btn.classList.toggle('btn-label-primary', active);
        btn.classList.toggle('btn-label-secondary', !active);
    });
}

function _trkOperacionStatusLabel(status) {
    const map = {
        disponible: 'Disponible',
        en_ruta: 'En ruta',
        programado: 'Programado',
        advertencia: 'Advertencia',
        saturado: 'Saturado',
    };
    return map[String(status || '')] || 'Sin estado';
}

function _trkOperacionFiltrados() {
    const q = _trkNormTxt(_trk.operacionBusqueda || '');
    const estatus = String(_trk.operacionFiltroEstatus || '');
    const tipo = String(_trk.operacionFiltroTipo || '');
    return (_trk.operacionTransportistas || []).filter(t => {
        if (estatus && String(t.estatus_operativo || '') !== estatus) return false;
        if (tipo && String(t.tipo_transportista || '') !== tipo) return false;
        if (!q) return true;
        const rutasTxt = (t.rutas || []).map(r => `${r.nombre_ruta || ''} ${r.cedis_destino_nombre || ''} ${r.estado || ''} ${r.municipio || ''}`).join(' ');
        const base = [
            t.nombre_transportista,
            t.tipo_transportista,
            t.empresa_origen,
            t.cedis_base?.nombre,
            t.cedis_base?.estado,
            t.cedis_base?.municipio,
            t.unidad?.tipo_unidad,
            t.unidad?.marca,
            t.unidad?.modelo,
            t.unidad?.placa,
            t.unidad?.numero_economico,
            t.unidad?.numero_serie,
            rutasTxt,
        ].join(' ');
        return _trkNormTxt(base).includes(q);
    });
}

function _trkRenderOperacionTransportistas() {
    _trkAplicarOperacionVista();
    const resumen = _trk.operacionResumen || {};
    $('#trkOpKpiActivos').text(resumen.transportistas_activos || 0);
    $('#trkOpKpiDisponibles').text(resumen.disponibles || 0);
    $('#trkOpKpiRuta').text(resumen.en_ruta || 0);
    $('#trkOpKpiProgramados').text(resumen.programados || 0);
    $('#trkOpKpiAdvertencia').text(resumen.advertencia || 0);
    $('#trkOpKpiSaturados').text(resumen.saturados || 0);

    const filtrados = _trkOperacionFiltrados();
    const alertas = [];
    (_trk.operacionTransportistas || []).forEach(t => {
        (t.alertas || []).forEach(a => {
            if (a.nivel === 'danger' || a.tipo === 'sin_capacidad') {
                alertas.push(`${t.nombre_transportista}: ${a.texto}`);
            }
        });
    });
    $('#trkOpAlertas').html(alertas.slice(0, 5).map(a => (
        `<div class="trk-admin-alert"><i class="fa-solid fa-circle-exclamation me-1"></i>${_trkChatEscapeHtml(a)}</div>`
    )).join(''));

    if (!filtrados.length) {
        $('#trkOpGrid').html('<div class="trk-admin-empty" style="grid-column:1/-1;"><i class="fa-solid fa-truck-fast fa-2x opacity-25 d-block mb-2"></i>No hay transportistas con los filtros seleccionados.</div>');
        $('#trkOpTablaBody').html('<tr><td colspan="6" class="text-center text-muted py-4">No hay transportistas con los filtros seleccionados.</td></tr>');
        return;
    }
    if (_trk.operacionVista === 'tabla') {
        $('#trkOpGrid').empty();
        $('#trkOpTablaBody').html(filtrados.map(_trkOperacionTransportistaFila).join(''));
        return;
    }
    $('#trkOpTablaBody').empty();
    $('#trkOpGrid').html(filtrados.map(_trkOperacionTransportistaCard).join(''));
}

function _trkOperacionTransportistaCard(t) {
    const status = String(t.estatus_operativo || 'disponible');
    const cap = Number(t.capacidad_total || 0);
    const proyectada = Number(t.capacidad_proyectada || 0);
    const usada = Number(t.capacidad_usada || 0);
    const pct = cap > 0 ? Math.min(100, Math.round((proyectada / cap) * 100)) : 0;
    const barClass = cap > 0 && proyectada >= cap ? 'danger' : (cap > 0 && pct >= 80 ? 'warn' : '');
    const capacidadTxt = cap > 0 ? `${proyectada} / ${cap}` : 'Sin configurar';
    const disponibleTxt = cap > 0 ? `${t.capacidad_disponible ?? 0}` : '-';
    const cedisBase = [t.cedis_base?.nombre, t.cedis_base?.municipio, t.cedis_base?.estado].filter(Boolean).join(' / ') || t.empresa_origen || 'Sin CEDIS base';
    const unidad = _trkOperacionUnidadTexto(t);
    const unidadBtnTxt = Number(t.unidad?.id_capacidad || 0) > 0 ? 'Editar unidad' : '+ Registrar Unidad';
    const rutas = (t.rutas || []).slice(0, 3).map(_trkOperacionRutaMini).join('');
    const alertas = (t.alertas || []).map(a => `<span class="badge bg-label-${a.nivel === 'danger' ? 'danger' : (a.nivel === 'warning' ? 'warning' : 'info')} me-1 mb-1">${_trkChatEscapeHtml(a.texto)}</span>`).join('');
    const tipoBadge = _trkTipoTransportistaBadge(t.tipo_transportista);
    return `<article class="trk-admin-card" data-id-transportista="${_trkChatEscapeHtml(t.id_transportista || '')}">
        <div class="trk-admin-card-head">
            <div style="min-width:0;">
                <div class="trk-admin-name">${_trkChatEscapeHtml(t.nombre_transportista || 'Sin nombre')}</div>
                <div class="trk-admin-sub">${tipoBadge} ${_trkChatEscapeHtml(cedisBase)}</div>
            </div>
            <span class="trk-admin-status ${_trkChatEscapeHtml(status)}">${_trkChatEscapeHtml(_trkOperacionStatusLabel(status))}</span>
        </div>
        <div class="trk-admin-metrics">
            <div class="trk-admin-metric"><span>Activas</span><strong>${_trkChatEscapeHtml(t.rutas_activas || 0)}</strong></div>
            <div class="trk-admin-metric"><span>Programadas</span><strong>${_trkChatEscapeHtml(t.rutas_programadas || 0)}</strong></div>
            <div class="trk-admin-metric"><span>Recolectadas</span><strong>${_trkChatEscapeHtml(usada)}</strong></div>
            <div class="trk-admin-metric"><span>Disponible</span><strong>${_trkChatEscapeHtml(disponibleTxt)}</strong></div>
        </div>
        <div>
            <div class="d-flex justify-content-between small fw-semibold mb-1">
                <span>Capacidad proyectada</span><span>${_trkChatEscapeHtml(capacidadTxt)}</span>
            </div>
            <div class="trk-admin-progress"><div class="trk-admin-progress-bar ${barClass}" style="width:${pct}%;"></div></div>
        </div>
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="small text-muted"><i class="fa-solid fa-truck me-1"></i>${_trkChatEscapeHtml(unidad)}</div>
            <button type="button" class="btn btn-xs btn-label-info btn-editar-unidad" data-id="${_trkChatEscapeHtml(t.id_transportista || '')}">
                <i class="fa-solid fa-truck me-1"></i>${_trkChatEscapeHtml(unidadBtnTxt)}
            </button>
        </div>
        <div class="small fw-semibold text-muted">Recomendacion: ${_trkChatEscapeHtml(t.recomendacion || 'Evaluar')}</div>
        <div class="trk-admin-live" data-id-transportista-live="${_trkChatEscapeHtml(t.id_transportista || '')}">${_trkOperacionLiveHtml(t)}</div>
        ${alertas ? `<div>${alertas}</div>` : ''}
        <div class="trk-admin-route-list">${rutas || '<div class="trk-admin-route text-muted">Sin rutas activas o programadas.</div>'}</div>
    </article>`;
}

function _trkOperacionUnidadTexto(t) {
    const u = t?.unidad || {};
    const base = [
        u.tipo_unidad || t?.tipo_unidad,
        u.marca,
        u.modelo,
        u.anio,
    ].filter(Boolean).join(' ');
    const placa = u.placa ? `Placa ${u.placa}` : '';
    const eco = u.numero_economico ? `Identificador ${u.numero_economico}` : '';
    return [base || 'Unidad sin datos generales', placa, eco].filter(Boolean).join(' - ');
}

function _trkOperacionAlertasHtml(t) {
    const alertas = Array.isArray(t.alertas) ? t.alertas : [];
    if (!alertas.length) {
        return '<span class="text-muted">Sin alertas</span>';
    }
    return alertas.map(a => `<span class="badge bg-label-${a.nivel === 'danger' ? 'danger' : (a.nivel === 'warning' ? 'warning' : 'info')} me-1 mb-1">${_trkChatEscapeHtml(a.texto || 'Alerta')}</span>`).join('');
}

function _trkOperacionLiveHtml(t) {
    const id = String(t.id_transportista || '');
    if (_trk.operacionLiveInfo[id]) {
        return _trk.operacionLiveInfo[id];
    }
    return `<i class="fa-solid fa-location-dot me-1"></i>${Number(t.rutas_activas || 0) > 0 ? 'Consultando ubicacion live...' : 'Sin ruta live activa'}`;
}

function _trkOperacionTransportistaFila(t) {
    const status = String(t.estatus_operativo || 'disponible');
    const cap = Number(t.capacidad_total || 0);
    const proyectada = Number(t.capacidad_proyectada || 0);
    const usada = Number(t.capacidad_usada || 0);
    const pct = cap > 0 ? Math.min(100, Math.round((proyectada / cap) * 100)) : 0;
    const barClass = cap > 0 && proyectada >= cap ? 'danger' : (cap > 0 && pct >= 80 ? 'warn' : '');
    const cedisBase = [t.cedis_base?.nombre, t.cedis_base?.municipio, t.cedis_base?.estado].filter(Boolean).join(' / ') || t.empresa_origen || 'Sin CEDIS base';
    const rutas = Array.isArray(t.rutas) ? t.rutas : [];
    const rutaActiva = t.ruta_activa || rutas.find(r => String(r.estatus_ruta || '').toLowerCase() === 'en_proceso') || rutas[0] || null;
    const destino = rutaActiva?.cedis_destino_nombre || 'Sin CEDIS destino';
    const ubicaciones = rutaActiva ? _trkOperacionUbicacionesTexto(rutaActiva.ubicaciones_lista || rutaActiva.estado || '') : 'Sin ruta asignada';
    const capacidadTxt = cap > 0 ? `${proyectada} / ${cap}` : 'Sin configurar';
    const rutasTxt = `${t.rutas_activas || 0} activas / ${t.rutas_programadas || 0} programadas`;
    const unidadTxt = _trkOperacionUnidadTexto(t);
    const unidadBtnTxt = Number(t.unidad?.id_capacidad || 0) > 0 ? 'Editar unidad' : '+ Registrar Unidad';
    return `<tr>
        <td>
            <div class="fw-bold text-heading">${_trkChatEscapeHtml(t.nombre_transportista || 'Sin nombre')}</div>
            <div class="small text-muted">${_trkTipoTransportistaBadge(t.tipo_transportista)} ${_trkChatEscapeHtml(t.empresa_origen || cedisBase)}</div>
            <div class="small text-muted">${_trkChatEscapeHtml(t.telefono || 'Sin telefono')}</div>
        </td>
        <td>
            <span class="trk-admin-status ${_trkChatEscapeHtml(status)}">${_trkChatEscapeHtml(_trkOperacionStatusLabel(status))}</span>
            <div class="small text-muted mt-1">${_trkChatEscapeHtml(t.recomendacion || 'Evaluar')}</div>
            <div class="trk-admin-live mt-1" data-id-transportista-live="${_trkChatEscapeHtml(t.id_transportista || '')}">${_trkOperacionLiveHtml(t)}</div>
        </td>
        <td style="min-width:150px;">
            <div class="d-flex justify-content-between small fw-semibold mb-1">
                <span>${_trkChatEscapeHtml(capacidadTxt)}</span><span>${_trkChatEscapeHtml(usada)} rec.</span>
            </div>
            <div class="trk-admin-progress"><div class="trk-admin-progress-bar ${barClass}" style="width:${pct}%;"></div></div>
            <div class="small text-muted mt-1">${cap > 0 ? `${_trkChatEscapeHtml(t.capacidad_disponible ?? 0)} disponibles` : 'Configurar capacidad'}</div>
            <div class="small text-muted mt-1">${_trkChatEscapeHtml(unidadTxt)}</div>
            <button type="button" class="btn btn-xs btn-label-info btn-editar-unidad mt-1" data-id="${_trkChatEscapeHtml(t.id_transportista || '')}">
                <i class="fa-solid fa-truck me-1"></i>${_trkChatEscapeHtml(unidadBtnTxt)}
            </button>
        </td>
        <td>
            <div class="fw-semibold">${_trkChatEscapeHtml(rutasTxt)}</div>
            <div class="small text-muted">${rutaActiva ? `#${_trkChatEscapeHtml(rutaActiva.id_ruta || '')} ${_trkChatEscapeHtml(_trkSanitizarNombreRuta(rutaActiva.nombre_ruta || '') || 'Ruta sin nombre')}` : 'Sin rutas activas o programadas'}</div>
        </td>
        <td>
            <div class="fw-semibold"><i class="fa-solid fa-warehouse me-1"></i>${_trkChatEscapeHtml(destino)}</div>
            <div class="small text-muted">${_trkChatEscapeHtml(ubicaciones)}</div>
            <div class="small text-muted">${_trkChatEscapeHtml(cedisBase)}</div>
        </td>
        <td>${_trkOperacionAlertasHtml(t)}</td>
    </tr>`;
}

function _trkOperacionRutaMini(r) {
    const nombre = _trkSanitizarNombreRuta(r.nombre_ruta || `Ruta #${r.id_ruta || ''}`) || `Ruta #${r.id_ruta || ''}`;
    const destino = r.cedis_destino_nombre || 'Sin CEDIS destino';
    const fecha = [r.fecha_programada_fmt, r.hora_inicial ? `Hora de salida ${r.hora_inicial}` : ''].filter(Boolean).join(' - ');
    const ubicaciones = _trkOperacionUbicacionesTexto(r.ubicaciones_lista || r.estado || '');
    return `<div class="trk-admin-route" data-id-ruta="${_trkChatEscapeHtml(r.id_ruta || '')}">
        <div class="trk-admin-route-title">#${_trkChatEscapeHtml(r.id_ruta || '')} ${_trkChatEscapeHtml(nombre)}</div>
        <div class="trk-admin-route-meta">
            <span><i class="fa-solid fa-circle-play me-1"></i>${_trkChatEscapeHtml(r.estatus_ruta || 'sin estatus')}</span>
            <span><i class="fa-solid fa-calendar-day me-1"></i>${_trkChatEscapeHtml(fecha || 'Sin fecha')}</span>
            <span><i class="fa-solid fa-warehouse me-1"></i>${_trkChatEscapeHtml(destino)}</span>
        </div>
        <div class="trk-admin-route-meta">
            <span>${_trkChatEscapeHtml(r.recolectadas || 0)} rec.</span>
            <span>${_trkChatEscapeHtml(r.confirmados || 0)} confirm.</span>
            <span>${_trkChatEscapeHtml(ubicaciones || 'Sin ubicacion')}</span>
        </div>
    </div>`;
}

function _trkOperacionUbicacionesTexto(raw) {
    const txt = String(raw || '');
    if (!txt) return '';
    if (!txt.includes('@@')) return txt;
    const partes = txt.split('@@')
        .map(x => x.split('|||').filter(Boolean).join(' / '))
        .filter(Boolean);
    return partes.slice(0, 2).join(' | ') + (partes.length > 2 ? ` +${partes.length - 2}` : '');
}

function _trkCargarUbicacionesOperacion() {
    const activas = (_trk.operacionTransportistas || [])
        .filter(t => Number(t.rutas_activas || 0) > 0 && t.ruta_activa?.id_ruta)
        .slice(0, 12);
    activas.forEach(t => {
        const idRuta = Number(t.ruta_activa.id_ruta);
        const idTransportista = String(t.id_transportista || '');
        if (!idRuta || _trk.operacionLiveLoaded[idRuta]) return;
        _trk.operacionLiveLoaded[idRuta] = true;
        trkFetch(`/TrackingRecoleccion/trackingUbicacionActual?id_ruta=${encodeURIComponent(idRuta)}`)
            .then(r => {
                const ubi = r.ubicacion || r.datos?.ubicacion || null;
                if (!ubi) {
                    _trkActualizarOperacionLive(idTransportista, '<i class="fa-solid fa-location-dot me-1"></i>Sin ubicacion live reportada');
                    return;
                }
                const fecha = ubi.created_at || ubi.updated_at || '';
                const hora = fecha ? _trkChatFechaLocal(fecha) : 'Sin fecha';
                const lat = Number(ubi.lat);
                const lng = Number(ubi.lng);
                const coords = Number.isFinite(lat) && Number.isFinite(lng)
                    ? `${lat.toFixed(5)}, ${lng.toFixed(5)}`
                    : 'Coordenadas no disponibles';
                _trkActualizarOperacionLive(idTransportista, `<i class="fa-solid fa-location-arrow me-1"></i>Ultima ubicacion ${_trkChatEscapeHtml(hora)} - ${_trkChatEscapeHtml(coords)}`);
            })
            .catch(() => {
                _trkActualizarOperacionLive(idTransportista, '<i class="fa-solid fa-location-dot me-1"></i>Ubicacion live no disponible');
            });
    });
}

function _trkActualizarOperacionLive(idTransportista, html) {
    const id = String(idTransportista || '');
    if (!id) return;
    _trk.operacionLiveInfo[id] = html;
    document.querySelectorAll('.trk-admin-live[data-id-transportista-live]').forEach(el => {
        if (String(el.dataset.idTransportistaLive || '') === id) {
            el.innerHTML = html;
        }
    });
}

function _trkTransportistasFiltrados() {
    return _trkTransportistasUnidadBase().filter(t => Number(t.activo ?? 1) === 1);
}

function _trkTransportistaOptionHtml(t) {
    const empresa = t.nombre_agencia || t.empresa_origen || 'Sin CEDIS';
    return `<button type="button" class="trk-transport-option" data-id="${_trkChatEscapeHtml(String(t.id_transportista || ''))}">
        <span class="name">${_trkChatEscapeHtml(t.nombre_transportista || '')}</span>
        ${_trkTipoTransportistaBadge(t.tipo_transportista)}
        <span class="empresa">${_trkChatEscapeHtml(empresa)}</span>
    </button>`;
}

function _trkTransportistaTexto(t) {
    if (!t) return '';
    const empresa = t.nombre_agencia || t.empresa_origen || t.cedis_base?.nombre || '';
    return `${t.nombre_transportista || ''}${empresa ? ' - ' + empresa : ''}`;
}

function _trkTransportistaPorId(id) {
    if (!id) return null;
    return (_trk.transportistasTracking || []).find(t => String(t.id_transportista) === String(id)) || null;
}

function _trkTemplateTransportistaSelect2(item) {
    if (!item.id) return _trkChatEscapeHtml(item.text || '');
    const t = _trkTransportistaConOperacion(item.id);
    if (!t) return _trkChatEscapeHtml(item.text || '');
    const empresa = t.nombre_agencia || t.empresa_origen || 'Sin CEDIS';
    const contacto = [t.telefono, t.email].filter(Boolean).join('  -  ');
    const evalInfo = _trkEvaluarTransportistaAsignacion(t, { creditos: _trk.creditosEnRuta });
    return `<div class="trk-driver-select2">
        <div class="trk-driver-select2-main">
            ${_trkTipoTransportistaBadge(t.tipo_transportista)}
            <span class="fw-semibold">${_trkChatEscapeHtml(t.nombre_transportista || '')}</span>
            ${_trkDriverScoreHtml(evalInfo)}
        </div>
        <small class="text-muted">${_trkChatEscapeHtml(empresa)}${contacto ? ' - ' + _trkChatEscapeHtml(contacto) : ''}</small>
        ${_trkDriverMiniHtml(evalInfo)}
    </div>`;
}

function _trkTemplateTransportistaSeleccionado(item) {
    if (!item.id) return _trkChatEscapeHtml(item.text || '');
    const t = _trkTransportistaConOperacion(item.id);
    return _trkChatEscapeHtml(_trkTransportistaTexto(t) || item.text || '');
}

function _trkRenderTransportistaResultados(query = '') {
    const $res = $('#rutaTransportistaResults');
    const q = _trkNormTxt(query);
    const transportistas = _trkTransportistasFiltrados().filter(t => {
        if (!q) return true;
        return _trkNormTxt([
            t.nombre_transportista,
            t.tipo_transportista,
            t.nombre_agencia,
            t.empresa_origen,
            t.curp_rfc,
            t.email,
            t.telefono,
        ].filter(Boolean).join(' ')).includes(q);
    });
    if (!transportistas.length) {
        $res.html('<div class="trk-transport-empty">Sin coincidencias</div>');
        return;
    }
    $res.html(transportistas.map(_trkTransportistaOptionHtml).join(''));
}

function _trkSeleccionarTransportista(id) {
    $('#rutaTransportistaTracking').val(id ? String(id) : '').trigger('change.select2');
    const t = _trkTransportistaSeleccionado();
    $('#rutaTransportistaResults').addClass('d-none');
    $('#rutaCedisDestino').val('');
    _trkSincronizarTransportistaSeleccionado();
    _trkRenderTransportistaInfo();
    _trkRenderCedisDestinoInfo();
}

function _trkRefrescarSelectTransportistas(preselectedId = null) {
    const transportistas = _trkTransportistasFiltrados();
    const actual = preselectedId || $('#rutaTransportistaTracking').val();
    const $sel = $('#rutaTransportistaTracking');
    $sel.html('<option value="">Selecciona transportista</option>');
    transportistas.forEach(t => {
        const textoBusqueda = [
            t.nombre_transportista,
            t.tipo_transportista,
            t.nombre_agencia,
            t.empresa_origen,
            t.cedis_base?.nombre,
            t.estatus_operativo,
            t.recomendacion,
            t.unidad?.tipo_unidad,
            t.unidad?.marca,
            t.unidad?.modelo,
            t.unidad?.placa,
            t.telefono,
            t.email,
            t.curp_rfc,
        ].filter(Boolean).join(' ');
        $sel.append($('<option>', {
            value: t.id_transportista,
            text: textoBusqueda,
        }));
    });
    $sel.prop('disabled', transportistas.length === 0);
    if (actual && transportistas.some(t => String(t.id_transportista) === String(actual))) {
        $sel.val(String(actual));
    } else {
        $sel.val('');
    }
    _trkRefrescarSelectBuscable('#rutaTransportistaTracking');
    _trkRenderTransportistaResultados('');
    _trkSincronizarTransportistaSeleccionado();
    _trkRenderTransportistaInfo();
    _trkRenderCedisDestinoInfo();
}

function _trkTransportistaSeleccionado() {
    const id = $('#rutaTransportistaTracking').val();
    if (!id) return null;
    return _trkTransportistaConOperacion(id);
}

function _trkSincronizarTransportistaSeleccionado() {
    const t = _trkTransportistaSeleccionado();
    $('#rutaTipoTransportista').val(t?.tipo_transportista || '');
    $('#rutaAgenciaTracking').val(t?.id_agencia || '');
    _trkPoblarAgenciasTrackingSelect();
    _trkActualizarBadgeTransportista();
    _trkRenderCedisDestinoInfo();
    _trkRenderSugerenciasTransportistasRuta();
}

function _trkCedisPorId(id) {
    if (!id) return null;
    return (_trk.agenciasTracking || []).find(a => String(a.id_agencia) === String(id)) || null;
}

function _trkCedisDestinoSeleccionado() {
    return _trkCedisPorId($('#rutaCedisDestino').val());
}

function _trkCedisDestinoPosicion(cedis) {
    if (!cedis) return null;
    const lat = parseFloat(cedis.latitud);
    const lng = parseFloat(cedis.longitud);
    if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) return null;
    return { lat, lng };
}

function _trkCedisValorUtil(v) {
    const txt = _trkNormTxt(v);
    if (!txt) return false;
    return !['NA', 'N/A', 'NO APLICA', 'SIN DATOS', 'NO DISPONIBLE', 'EN ESPERA DE DATOS', 'SIN UBICACION'].includes(txt);
}

function _trkCedisTieneUbicacionOperativa(cedis) {
    if (!cedis) return false;
    if (_trkCedisDestinoPosicion(cedis)) return true;
    return _trkCedisValorUtil(cedis.estado) && _trkCedisValorUtil(cedis.municipio);
}

function _trkCedisUbicacionBloqueoMsg(cedis) {
    const nombre = cedis?.nombre_agencia || cedis?.clave_agencia || 'El CEDIS seleccionado';
    return `${nombre} no tiene ubicacion operativa suficiente. Completa al menos coordenadas o Estado/Municipio en CEDIS y Transportistas antes de asignarlo como destino.`;
}

function _trkEsCedisDestinoInternoPermitido(cedis) {
    if (!cedis) return false;
    const id = Number(cedis.id_agencia || 0);
    const clave = _trkNormTxt(cedis.clave_agencia);
    return [1, 2].includes(id) || ['LOMAS_PLAZA_MAXIKASH', 'TLALNEPANTLA_MAXIKASH'].includes(clave);
}

function _trkCedisDestinoFiltrados(tipo) {
    const cedis = _trkCedisActivos();
    if (tipo === 'interno') return cedis.filter(_trkEsCedisDestinoInternoPermitido);
    return cedis;
}

function _trkCedisActivos() {
    return _trkFiltrarCedisActivos(_trk.agenciasTracking || []).filter(a => {
        const tipo = _trkNormTxt(a?.tipo_ubicacion || 'cedis');
        return !tipo || ['AGENCIA', 'CEDIS', 'ALMACEN'].includes(tipo);
    });
}

function _trkNormTxt(v) {
    return String(v || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toUpperCase()
        .trim();
}

const _TRK_ESTADOS_OFICIALES = [
    'AGUASCALIENTES', 'BAJA CALIFORNIA', 'BAJA CALIFORNIA SUR', 'CAMPECHE',
    'CHIAPAS', 'CHIHUAHUA', 'CIUDAD DE MEXICO', 'COAHUILA', 'COLIMA',
    'DURANGO', 'ESTADO DE MEXICO', 'GUANAJUATO', 'GUERRERO', 'HIDALGO',
    'JALISCO', 'MICHOACAN', 'MORELOS', 'NAYARIT', 'NUEVO LEON', 'OAXACA',
    'PUEBLA', 'QUERETARO', 'QUINTANA ROO', 'SAN LUIS POTOSI', 'SINALOA',
    'SONORA', 'TABASCO', 'TAMAULIPAS', 'TLAXCALA', 'VERACRUZ', 'YUCATAN',
    'ZACATECAS',
];

const _TRK_ESTADO_ALIAS = {
    CDMX: 'CIUDAD DE MEXICO',
    CMDX: 'CIUDAD DE MEXICO',
    'DISTRITO FEDERAL': 'CIUDAD DE MEXICO',
    DF: 'CIUDAD DE MEXICO',
    MEXICO: 'ESTADO DE MEXICO',
    EDOMEX: 'ESTADO DE MEXICO',
    'EDO MEX': 'ESTADO DE MEXICO',
    'EDO DE MEX': 'ESTADO DE MEXICO',
    'EDO DE MEXICO': 'ESTADO DE MEXICO',
    'ESTADO MEXICO': 'ESTADO DE MEXICO',
    'EDO MEXICO': 'ESTADO DE MEXICO',
    MICHOACAN: 'MICHOACAN',
    'MICHOACAN DE OCAMPO': 'MICHOACAN',
    'SAN LUIS': 'SAN LUIS POTOSI',
    SLP: 'SAN LUIS POTOSI',
    QRO: 'QUERETARO',
    VER: 'VERACRUZ',
};

const _TRK_MUNICIPIO_ESTADO_HINTS = {
    'ALVARO OBREGON': 'CIUDAD DE MEXICO',
    AZCAPOTZALCO: 'CIUDAD DE MEXICO',
    COYOACAN: 'CIUDAD DE MEXICO',
    CUAJIMALPA: 'CIUDAD DE MEXICO',
    'GUSTAVO A MADERO': 'CIUDAD DE MEXICO',
    GAM: 'CIUDAD DE MEXICO',
    IZTAPALAPA: 'CIUDAD DE MEXICO',
    IZTACALCO: 'CIUDAD DE MEXICO',
    TLAHUAC: 'CIUDAD DE MEXICO',
    CUAUHTEMOC: 'CIUDAD DE MEXICO',
    'MIGUEL HIDALGO': 'CIUDAD DE MEXICO',
    'LOS REYES LA PAZ': 'ESTADO DE MEXICO',
    'LOS REYES': 'ESTADO DE MEXICO',
    'LA PAZ': 'ESTADO DE MEXICO',
    OCUILAN: 'ESTADO DE MEXICO',
    'SAN MATEO ATENCO': 'ESTADO DE MEXICO',
    TLALNEPANTLA: 'ESTADO DE MEXICO',
    'TLALNEPANTLA DE BAZ': 'ESTADO DE MEXICO',
    TECAMAC: 'ESTADO DE MEXICO',
    ECATEPEC: 'ESTADO DE MEXICO',
    NEZAHUALCOYOTL: 'ESTADO DE MEXICO',
    NAUCALPAN: 'ESTADO DE MEXICO',
    'CHIAPA DE CORZO': 'CHIAPAS',
    'GOMEZ PALACIOS': 'DURANGO',
    LEON: 'GUANAJUATO',
    ACAPULCO: 'GUERRERO',
    CHILPANCINGO: 'GUERRERO',
    'CIUDAD ALTAMIRANO': 'GUERRERO',
    PUNGARABATO: 'GUERRERO',
    GUADALAJARA: 'JALISCO',
    JIUTEPEC: 'MORELOS',
    PUEBLA: 'PUEBLA',
    QUERETARO: 'QUERETARO',
    MATEHUALA: 'SAN LUIS POTOSI',
    RIOVERDE: 'SAN LUIS POTOSI',
    'SAN LUIS POTOSI': 'SAN LUIS POTOSI',
    CENTRO: 'TABASCO',
    COMALCALCO: 'TABASCO',
    COMNALCALCO: 'TABASCO',
    FRONTERA: 'TABASCO',
    'CD VICTORIA': 'TAMAULIPAS',
    'CIUDAD VICTORIA': 'TAMAULIPAS',
    'AMATLAN DE LOS REYES': 'VERACRUZ',
    COATEPEC: 'VERACRUZ',
    COSCOMATEPEC: 'VERACRUZ',
    COSOLEACAQUE: 'VERACRUZ',
    COSOEACAQUE: 'VERACRUZ',
    'BOCA DEL RIO': 'VERACRUZ',
    MERIDA: 'YUCATAN',
};

function _trkEstadoTextoBase(v) {
    return _trkNormTxt(v)
        .replace(/[^A-Z0-9\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function _trkPareceDireccionEnEstado(txt) {
    return txt.length > 35
        || /\d/.test(txt)
        || /\b(CALLE|COLONIA|COL|FRACC|FRACCIONAMIENTO|MZ|MANZANA|LT|LOTE|AV|AVENIDA|RETORNO|CDA|CERRADA|CP|HDA|ANDADOR|CIRCUITO|PRIVADA|BOULEVARD)\b/.test(txt);
}

function _trkEstadoCanonico(estado, municipio = '') {
    const est = _trkEstadoTextoBase(estado);
    const mun = _trkEstadoTextoBase(municipio);
    if (!est && !mun) return '';
    const estadoAmbiguo = ['MEXICO', 'EDO MEX', 'EDO DE MEX', 'EDO DE MEXICO', 'ESTADO MEXICO', 'EDO MEXICO'].includes(est);
    if (estadoAmbiguo && mun && _TRK_MUNICIPIO_ESTADO_HINTS[mun]) return _TRK_MUNICIPIO_ESTADO_HINTS[mun];
    if (_TRK_ESTADO_ALIAS[est]) return _TRK_ESTADO_ALIAS[est];
    if (_TRK_ESTADOS_OFICIALES.includes(est)) return est;

    const combo = `${est} ${mun}`.trim();
    if (combo.includes('CIUDAD DE MEXICO') || /\bCDMX\b/.test(combo) || /\bCMDX\b/.test(combo)) {
        return 'CIUDAD DE MEXICO';
    }
    if (combo.includes('ESTADO DE MEXICO') || combo.includes('EDO DE MEX') || combo.includes('EDOMEX')) {
        return 'ESTADO DE MEXICO';
    }

    if (mun && _TRK_MUNICIPIO_ESTADO_HINTS[mun]) return _TRK_MUNICIPIO_ESTADO_HINTS[mun];
    const hint = Object.entries(_TRK_MUNICIPIO_ESTADO_HINTS)
        .find(([m]) => combo.includes(m));
    if (hint) return hint[1];

    const porNombre = _TRK_ESTADOS_OFICIALES
        .filter(e => e !== 'ESTADO DE MEXICO')
        .sort((a, b) => b.length - a.length)
        .find(e => combo.includes(e));
    if (porNombre) return porNombre;

    if (_trkPareceDireccionEnEstado(est)) return 'SIN ESTADO / REVISAR DATOS';
    return est;
}

function _trkMismaUbicacionEstado(estadoA, estadoB, municipioA = '', municipioB = '') {
    return _trkEstadoCanonico(estadoA, municipioA) === _trkEstadoCanonico(estadoB, municipioB);
}

function _trkEsZonaInterna(estado, municipio) {
    const est = _trkNormTxt(_trkEstadoCanonico(estado, municipio));
    const mun = _trkNormTxt(municipio);
    const estadosOk = ['CDMX', 'CIUDAD DE MEXICO', 'DISTRITO FEDERAL', 'ESTADO DE MEXICO', 'EDOMEX', 'MEXICO'];
    const municipiosOk = [
        'MIGUEL HIDALGO', 'CUAUHTEMOC', 'BENITO JUAREZ', 'ALVARO OBREGON', 'AZCAPOTZALCO',
        'COYOACAN', 'IZTAPALAPA', 'IZTACALCO', 'GUSTAVO A MADERO', 'VENUSTIANO CARRANZA',
        'TLALNEPANTLA', 'TLALNEPANTLA DE BAZ', 'CUAUTITLAN IZCALLI', 'CUAUTITLAN',
        'TULTITLAN', 'NAUCALPAN', 'NAUCALPAN DE JUAREZ', 'ATIZAPAN DE ZARAGOZA',
        'NEZAHUALCOYOTL', 'NEZA', 'ECATEPEC', 'ECATEPEC DE MORELOS', 'COACALCO',
        'COACALCO DE BERRIOZABAL', 'NICOLAS ROMERO', 'CHIMALHUACAN', 'LOS REYES',
        'LA PAZ', 'TECAMAC', 'TECAMAC', 'HUIXQUILUCAN', 'CHALCO', 'VALLE DE CHALCO'
    ];
    if (est === 'CDMX' || est === 'CIUDAD DE MEXICO' || est === 'DISTRITO FEDERAL') return true;
    return estadosOk.includes(est) && municipiosOk.includes(mun);
}

function _trkValidarReglasTransportista() {
    const tipo = _trkTransportistaSeleccionado()?.tipo_transportista || $('#rutaTipoTransportista').val();
    const idDestino = $('#rutaCedisDestino').val();
    const cedisDestino = _trkCedisPorId(idDestino);
    if (!idDestino) {
        if (_trk.idRutaEditando) {
            return { ok: true };
        }
        return { ok: false, mensaje: 'Selecciona el CEDIS destino donde se entregara el vehiculo.' };
    }
    if (!cedisDestino) {
        return { ok: false, mensaje: 'El CEDIS destino seleccionado no esta disponible en el catalogo activo.' };
    }
    if (!_trkCedisTieneUbicacionOperativa(cedisDestino)) {
        return { ok: false, mensaje: _trkCedisUbicacionBloqueoMsg(cedisDestino) };
    }
    if (tipo === 'interno') {
        if (!_trkEsCedisDestinoInternoPermitido(cedisDestino)) {
            return { ok: false, mensaje: 'Para transportistas internos solo puedes seleccionar LOMAS PLAZA MAXIKASH o TLALNEPANTLA MAXIKASH como destino.' };
        }
        if (cedisDestino && !_trkEsZonaInterna(cedisDestino.estado, cedisDestino.municipio)) {
            return { ok: false, mensaje: 'Los transportistas internos solo pueden tener destino en CDMX o zona metropolitana.' };
        }
        const fueraZona = _trk.creditosEnRuta.find(c => !_trkEsZonaInterna(c.estado, c.municipio));
        if (fueraZona) {
            return {
                ok: false,
                mensaje: `El credito #${fueraZona.id_credito} esta fuera de CDMX/zona metropolitana. Asigna un transportista externo para esta ruta.`,
            };
        }
    }
    return { ok: true };
}

function _trkRenderTransportistaInfo() {
    const t = _trkTransportistaSeleccionado();
    const $box = $('#rutaTransportistaInfo');
    if (!t) {
        $box.addClass('d-none').empty();
        _trkRenderSugerenciasTransportistasRuta();
        return;
    }
    const agencia = t.nombre_agencia || t.empresa_origen || t.cedis_base?.nombre || 'Sin CEDIS asignado';
    const contacto = [t.telefono, t.email].filter(Boolean).join('  -  ') || 'Sin contacto';
    const evalInfo = _trkEvaluarTransportistaAsignacion(t, { creditos: _trk.creditosEnRuta });
    const razones = evalInfo.razones.map(r => `<div>${_trkChatEscapeHtml(r)}</div>`).join('');
    const unidad = _trkOperacionUnidadTexto(t);
    $box.removeClass('d-none').html(`
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
            <div style="min-width:0;">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="text-muted">${_trkChatEscapeHtml(agencia)}</span>
                    <span class="text-muted">${_trkChatEscapeHtml(contacto)}</span>
                </div>
                <div class="trk-driver-mini"><i class="fa-solid fa-truck me-1"></i>${_trkChatEscapeHtml(unidad)}</div>
                <div class="trk-driver-reasons">${razones}</div>
            </div>
            ${_trkDriverScoreHtml(evalInfo)}
        </div>
    `);
    _trkRenderSugerenciasTransportistasRuta();
}

function _trkRenderCedisDestinoInfo() {
    const cedis = _trkCedisDestinoSeleccionado();
    const $box = $('#rutaCedisDestinoInfo');
    if (!cedis) {
        if (_trk.idRutaEditando && _trkRutaPermiteCambioCedis(_trk.estatusRuta)) {
            $box.removeClass('d-none').html(_trkCedisDestinoHtml(null, {
                idRuta: _trk.idRutaEditando,
                estatus: _trk.estatusRuta,
            }));
        } else {
            $box.addClass('d-none').empty();
        }
        return;
    }
    const nombre = cedis.nombre_agencia || 'Sin destino asignado';
    const encargado = cedis.encargado || 'No disponible';
    const ubicacion = [cedis.municipio, cedis.estado, cedis.codigo_postal ? `CP ${cedis.codigo_postal}` : '']
        .filter(Boolean).join(' / ') || 'Ubicacion no disponible';
    const direccion = cedis.direccion || 'No disponible';
    const contacto = [cedis.telefono, cedis.email].filter(Boolean).join(' / ') || 'Sin contacto';
    const horario = cedis.horario || 'No disponible';
    const mapsUrl = _trkCedisMapsUrl(cedis);
    const puedeCambiar = _trk.idRutaEditando && _trkRutaPermiteCambioCedis(_trk.estatusRuta);
    const ubicacionOperativa = _trkCedisTieneUbicacionOperativa(cedis);
    const avisoUbicacion = ubicacionOperativa ? '' : `
        <div class="alert alert-warning py-1 px-2 mb-0 mt-1 small">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Sin ubicacion operativa. No se puede asignar como destino hasta completar coordenadas o Estado/Municipio.
        </div>`;
    const mapsBtn = mapsUrl
        ? `<a class="trk-cedis-map-btn" href="${_trkChatEscapeHtml(mapsUrl)}" target="_blank" rel="noopener noreferrer" title="Abrir CEDIS en Google Maps">
                <i class="fa-solid fa-location-dot maps-pin"></i>
                <span>Maps</span>
           </a>`
        : '';
    $box.removeClass('d-none').html(`
        <div class="trk-cedis-info-card">
            <div class="trk-cedis-info-body d-flex flex-column gap-1">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-primary">
                        <i class="fa-solid fa-warehouse me-1"></i>CEDIS destino
                    </span>
                    <span class="fw-semibold">${_trkChatEscapeHtml(nombre)}</span>
                </div>
                <div class="d-flex flex-wrap gap-2 text-muted">
                    <span><strong>Recibe:</strong> ${_trkChatEscapeHtml(encargado)}</span>
                    <span><strong>Ubicacion:</strong> ${_trkChatEscapeHtml(ubicacion)}</span>
                </div>
                <div class="text-muted"><strong>Direccion:</strong> ${_trkChatEscapeHtml(direccion)}</div>
                <div class="d-flex flex-wrap gap-2 text-muted">
                    <span><strong>Contacto:</strong> ${_trkChatEscapeHtml(contacto)}</span>
                    <span><strong>Horario:</strong> ${_trkChatEscapeHtml(horario)}</span>
                </div>
                ${avisoUbicacion}
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                ${mapsBtn}
                ${puedeCambiar ? `<button type="button" class="btn btn-sm btn-primary btn-cambiar-cedis-destino" data-id="${_trkChatEscapeHtml(_trk.idRutaEditando)}">
                    <i class="fa-solid fa-rotate me-1"></i>Cambiar
                </button>` : ''}
            </div>
        </div>
    `);
}

function _trkCedisMapsUrl(cedis) {
    if (!cedis) return '';
    const lat = parseFloat(cedis.latitud);
    const lng = parseFloat(cedis.longitud);
    if (Number.isFinite(lat) && Number.isFinite(lng)) {
        return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${lat},${lng}`)}`;
    }
    const query = [cedis.direccion, cedis.municipio, cedis.estado, cedis.codigo_postal]
        .filter(Boolean)
        .join(', ');
    if (query) return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
    return cedis.link_ubicacion ? String(cedis.link_ubicacion) : '';
}

function _trkRutaPermiteCambioCedis(estatus) {
    const st = String(estatus || '').toLowerCase();
    return !['cancelada', 'concluida', 'completado', 'finalizada'].includes(st);
}

function _trkNormalizarCedisDestino(c) {
    if (!c || typeof c !== 'object') return null;
    return {
        id_agencia: c.id_agencia || c.id_cedis_destino || c.id || null,
        clave_agencia: c.clave_agencia || '',
        nombre_agencia: c.nombre_agencia || c.nombre_cedis || c.nombre || 'Sin destino asignado',
        direccion: c.direccion || '',
        estado: _trkEstadoMayus(c.estado || '', c.municipio || ''),
        municipio: _trkMunicipioMayus(c.municipio || '', c.estado || ''),
        codigo_postal: c.codigo_postal || '',
        latitud: c.latitud ?? c.lat ?? null,
        longitud: c.longitud ?? c.lng ?? null,
        link_ubicacion: c.link_ubicacion || '',
        telefono: c.telefono || '',
        encargado: c.encargado || '',
        email: c.email || '',
        horario: c.horario || '',
        activo: c.activo !== false && c.activo !== 0 && c.activo !== '0',
    };
}

function _trkCedisDestinoHtml(cedis, opts = {}) {
    const idRuta = opts.idRuta || '';
    const estatus = opts.estatus || '';
    const puedeCambiar = opts.puedeCambiar !== false && _trkRutaPermiteCambioCedis(estatus);
    if (!cedis) {
        return `
            <div class="trk-cedis-info-card" id="trkDetalleCedisDestinoCard">
                <div class="trk-cedis-info-body">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="badge bg-primary"><i class="fa-solid fa-warehouse me-1"></i>CEDIS destino</span>
                        <span class="fw-semibold">Sin destino asignado</span>
                    </div>
                    <div class="text-muted small">Esta ruta no tiene CEDIS destino registrado.</div>
                </div>
                ${puedeCambiar ? `<button type="button" class="btn btn-sm btn-primary btn-cambiar-cedis-destino" data-id="${_trkChatEscapeHtml(idRuta)}">
                    <i class="fa-solid fa-rotate me-1"></i>Cambiar
                </button>` : ''}
            </div>`;
    }
    const ubicacion = [cedis.municipio, cedis.estado, cedis.codigo_postal ? `CP ${cedis.codigo_postal}` : '']
        .filter(Boolean).join(' / ') || 'Ubicacion no disponible';
    const coords = (cedis.latitud != null && cedis.longitud != null)
        ? `${cedis.latitud}, ${cedis.longitud}`
        : 'Sin coordenadas';
    const contacto = [cedis.telefono, cedis.email].filter(Boolean).join(' / ') || 'Sin contacto';
    const mapsUrl = _trkCedisMapsUrl(cedis);
    const ubicacionOperativa = _trkCedisTieneUbicacionOperativa(cedis);
    const avisoUbicacion = ubicacionOperativa ? '' : `
        <div class="alert alert-warning py-1 px-2 mb-0 mt-1 small">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Sin ubicacion operativa. No se puede asignar como destino hasta completar coordenadas o Estado/Municipio.
        </div>`;
    const mapsBtn = mapsUrl
        ? `<a class="trk-cedis-map-btn" href="${_trkChatEscapeHtml(mapsUrl)}" target="_blank" rel="noopener noreferrer" title="Abrir en Google Maps">
                <i class="fa-solid fa-location-dot maps-pin"></i><span>Maps</span>
           </a>`
        : '';
    return `
        <div class="trk-cedis-info-card" id="trkDetalleCedisDestinoCard">
            <div class="trk-cedis-info-body d-flex flex-column gap-1">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-primary"><i class="fa-solid fa-warehouse me-1"></i>CEDIS destino</span>
                    <span class="fw-semibold">${_trkChatEscapeHtml(cedis.nombre_agencia || 'Sin destino asignado')}</span>
                </div>
                <div class="d-flex flex-wrap gap-2 text-muted">
                    <span><strong>Recibe:</strong> ${_trkChatEscapeHtml(cedis.encargado || 'No disponible')}</span>
                    <span><strong>Ubicacion:</strong> ${_trkChatEscapeHtml(ubicacion)}</span>
                </div>
                <div class="text-muted"><strong>Direccion:</strong> ${_trkChatEscapeHtml(cedis.direccion || 'No disponible')}</div>
                <div class="d-flex flex-wrap gap-2 text-muted">
                    <span><strong>Coordenadas:</strong> ${_trkChatEscapeHtml(coords)}</span>
                    <span><strong>Contacto:</strong> ${_trkChatEscapeHtml(contacto)}</span>
                </div>
                ${cedis.horario ? `<div class="text-muted"><strong>Horario:</strong> ${_trkChatEscapeHtml(cedis.horario)}</div>` : ''}
                ${avisoUbicacion}
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                ${mapsBtn}
                ${puedeCambiar ? `<button type="button" class="btn btn-sm btn-primary btn-cambiar-cedis-destino" data-id="${_trkChatEscapeHtml(idRuta)}">
                    <i class="fa-solid fa-rotate me-1"></i>Cambiar
                </button>` : ''}
            </div>
        </div>`;
}

function _trkRenderHistorialCedisDestino(historial) {
    if (!Array.isArray(historial) || !historial.length) {
        return '<div class="text-muted small">Sin cambios registrados.</div>';
    }
    return historial.map(h => {
        const anterior = h.cedis_anterior_nombre || h.nombre_cedis_anterior || h.id_cedis_anterior || 'Sin destino';
        const nuevo = h.cedis_nuevo_nombre || h.nombre_cedis_nuevo || h.id_cedis_nuevo || 'No disponible';
        const fecha = _trkFormatFechaHora(h.fecha_cambio || h.created_at || h.updated_at) || (h.fecha_cambio || 'No disponible');
        return `
            <div class="border-top py-2 small">
                <div class="fw-semibold">${_trkChatEscapeHtml(anterior)} <i class="fa-solid fa-arrow-right mx-1"></i> ${_trkChatEscapeHtml(nuevo)}</div>
                <div class="text-muted">${_trkChatEscapeHtml(fecha)} | ${_trkChatEscapeHtml(h.tipo_actor || 'gestor')}</div>
                <div class="text-muted">${_trkChatEscapeHtml(h.motivo || 'Sin motivo')}</div>
            </div>`;
    }).join('');
}

async function _trkConsultarCedisDestinoRuta(idRuta) {
    const r = await trkFetch(`/TrackingRecoleccion/trackingCedisDestino?id_ruta=${encodeURIComponent(idRuta)}`);
    if (!r || !r.success) {
        const endpointNotFound = Number(r?.codigo_http || 0) === 404 && String(r?.detail || '').toLowerCase() === 'not found';
        throw new Error(endpointNotFound
            ? 'La API no encontro el endpoint cedis-destino. Verifica la ruta publicada del servicio.'
            : (r?.mensaje || r?.message || r?.detail || 'No se pudo consultar CEDIS destino.'));
    }
    const cedis = _trkNormalizarCedisDestino(r.cedis_destino || r.datos?.cedis_destino || null);
    const historial = r.historial || r.datos?.historial || [];
    _trk.cedisDestinoPorRuta[idRuta] = cedis;
    _trk.cedisDestinoHistorial[idRuta] = Array.isArray(historial) ? historial : [];
    return { cedis, historial: _trk.cedisDestinoHistorial[idRuta] };
}

function _trkActualizarCedisDestinoEnMemoria(idRuta, cedis) {
    const normal = _trkNormalizarCedisDestino(cedis);
    _trk.cedisDestinoPorRuta[idRuta] = normal;
    const aplicar = r => {
        if (String(r?.id_ruta || r?.id || '') !== String(idRuta)) return;
        r.id_cedis_destino = normal?.id_agencia || null;
        r.cedis_destino = normal;
        r.cedis_destino_nombre = normal?.nombre_agencia || '';
        r.cedis_destino_direccion = normal?.direccion || '';
    };
    (_trk.rutasRegistradas || []).forEach(aplicar);
    (_trk.borradoresData || []).forEach(aplicar);
    if (String(_trk.idRutaEditando || '') === String(idRuta)) {
        $('#rutaCedisDestino').val(normal?.id_agencia ? String(normal.id_agencia) : '');
        _trkRenderCedisDestinoInfo();
        _trkRenderizarMapa();
    }
}

function _trkRenderDetalleCedisDestino(idRuta, estatus) {
    const cedis = _trk.cedisDestinoPorRuta[idRuta] || null;
    const historial = _trk.cedisDestinoHistorial[idRuta] || [];
    $('#trkDetalleCedisDestinoWrap').html(_trkCedisDestinoHtml(cedis, { idRuta, estatus }));
    $('#trkDetalleCedisHistorial').html(_trkRenderHistorialCedisDestino(historial));
}

async function _trkCargarDetalleCedisDestino(idRuta, estatus) {
    $('#trkDetalleCedisDestinoWrap').html('<div class="text-muted small"><span class="spinner-border spinner-border-sm me-2"></span>Consultando CEDIS destino...</div>');
    try {
        await _trkConsultarCedisDestinoRuta(idRuta);
    } catch (err) {
        const cedisFallback = _trk.cedisDestinoPorRuta[idRuta] || null;
        $('#trkDetalleCedisDestinoWrap').html(
            _trkCedisDestinoHtml(cedisFallback, { idRuta, estatus })
            + `<div class="alert alert-warning py-1 px-2 small mt-2 mb-0">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                ${_trkChatEscapeHtml(err.message || 'No se pudo consultar CEDIS destino.')}
            </div>`
        );
        return;
    }
    _trkRenderDetalleCedisDestino(idRuta, estatus);
}

function _trkLlenarSelectCambioCedis(selected = '') {
    const cedis = _trkCedisActivos();
    const $sel = $('#trkCambiarCedisSelect');
    $sel.html('<option value="">Selecciona CEDIS destino</option>');
    cedis.forEach(a => {
        const label = `${a.nombre_agencia || a.clave_agencia || 'CEDIS'}${_trkCedisTieneUbicacionOperativa(a) ? '' : ' - SIN UBICACION'}`;
        $sel.append(`<option value="${_trkChatEscapeHtml(a.id_agencia)}">${_trkChatEscapeHtml(label)}</option>`);
    });
    $sel.val(selected ? String(selected) : '');
}

async function _trkAbrirModalCambiarCedisDestino(idRuta) {
    if (!idRuta) return;
    await _trkCargarCatalogosSiHaceFalta();
    const cedisActual = _trk.cedisDestinoPorRuta[idRuta] || null;
    $('#trkCambiarCedisRutaId').val(idRuta);
    $('#trkCambiarCedisActual').html(`CEDIS actual: <b>${_trkChatEscapeHtml(cedisActual?.nombre_agencia || 'Sin destino asignado')}</b>`);
    _trkLlenarSelectCambioCedis(cedisActual?.id_agencia || '');
    $('#trkCambiarCedisMotivo').val('');
    $('#trkCambiarCedisMotivoCount').text('0/200');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCambiarCedisDestino')).show();
}

function _trkMensajeErrorCedisDestino(r) {
    const code = Number(r?.codigo_http || r?.status || r?.http_code || 0);
    const msg = r?.mensaje || r?.message || r?.detail || r?.error || '';
    if (code === 404 && String(r?.detail || '').toLowerCase() === 'not found') {
        return 'La API no encontro el endpoint cedis-destino. Verifica la ruta publicada del servicio.';
    }
    if (code === 409) return 'No se puede cambiar el CEDIS destino en el estatus actual de la ruta';
    if (code === 404) return 'Ruta o CEDIS no encontrado';
    if (code === 422) return msg || 'CEDIS inactivo o motivo invalido';
    return msg || 'No se pudo cambiar el CEDIS destino.';
}

async function _trkConfirmarCambioCedisDestino() {
    const idRuta = Number($('#trkCambiarCedisRutaId').val() || 0);
    const idCedisDestino = Number($('#trkCambiarCedisSelect').val() || 0);
    const motivo = String($('#trkCambiarCedisMotivo').val() || '').trim();
    if (!idRuta) return;
    if (!idCedisDestino) {
        Swal.fire({ icon: 'warning', title: 'Selecciona CEDIS', text: 'El CEDIS destino es obligatorio.', confirmButtonText: 'Aceptar' });
        return;
    }
    const cedisSeleccionado = _trkCedisPorId(idCedisDestino);
    if (!cedisSeleccionado || !_trkCedisTieneUbicacionOperativa(cedisSeleccionado)) {
        Swal.fire({
            icon: 'warning',
            title: 'CEDIS sin ubicacion',
            text: _trkCedisUbicacionBloqueoMsg(cedisSeleccionado),
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    if (!motivo) {
        Swal.fire({ icon: 'warning', title: 'Motivo obligatorio', text: 'Escribe el motivo del cambio.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (motivo.length > 200) {
        Swal.fire({ icon: 'warning', title: 'Motivo muy largo', text: 'El motivo no puede exceder 200 caracteres.', confirmButtonText: 'Aceptar' });
        return;
    }
    const ok = await Swal.fire({
        icon: 'warning',
        title: 'Confirmar cambio de CEDIS?',
        text: 'La ruta puede estar en operacion. El transportista recibira el nuevo destino.',
        showCancelButton: true,
        confirmButtonText: 'Si, cambiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488',
    });
    if (!ok.isConfirmed) return;

    Swal.fire({
        title: 'Actualizando CEDIS destino...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const r = await trkFetch('/TrackingRecoleccion/trackingCambiarCedisDestino', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_ruta: idRuta, id_cedis_destino: idCedisDestino, motivo }),
        });
        const informativo = String(r?.message || r?.mensaje || '').toLowerCase().includes('ya esta asignado')
            || String(r?.message || r?.mensaje || '').toLowerCase().includes('ya est');
        if (!r.success && !informativo) {
            Swal.fire({ icon: 'error', title: 'No se pudo cambiar', text: _trkMensajeErrorCedisDestino(r), confirmButtonText: 'Aceptar' });
            return;
        }
        const cedis = _trkNormalizarCedisDestino(r.cedis_destino || r.datos?.cedis_destino || _trkCedisPorId(idCedisDestino));
        if (cedis) _trkActualizarCedisDestinoEnMemoria(idRuta, cedis);
        await _trkConsultarCedisDestinoRuta(idRuta).catch(() => {});
        if (_trk.detalleRutaActualId && String(_trk.detalleRutaActualId) === String(idRuta)) {
            const ruta = [...(_trk.rutasRegistradas || []), ...(_trk.borradoresData || [])].find(x => String(x.id_ruta || x.id || '') === String(idRuta));
            _trkRenderDetalleCedisDestino(idRuta, ruta?.estatus_ruta || '');
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCambiarCedisDestino')).hide();
        Swal.fire({
            icon: informativo ? 'info' : 'success',
            title: informativo ? 'Sin cambios' : 'CEDIS destino actualizado',
            text: r.message || r.mensaje || 'Cambio aplicado correctamente.',
            timer: 2200,
            showConfirmButton: false,
        });
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo cambiar el CEDIS destino.', confirmButtonText: 'Aceptar' });
    }
}

function _trkAplicarEventoCambioCedisDestino(data) {
    const idRuta = Number(data?.id_ruta || data?.ruta_id || 0);
    if (!idRuta) return;
    const cedis = _trkNormalizarCedisDestino({
        id_agencia: data.id_cedis_destino,
        nombre_agencia: data.nombre_cedis,
        direccion: data.direccion,
        estado: data.estado,
        municipio: data.municipio,
        latitud: data.lat,
        longitud: data.lng,
        link_ubicacion: data.link_ubicacion,
        activo: true,
    });
    _trkActualizarCedisDestinoEnMemoria(idRuta, cedis);
    if (_trk.detalleRutaActualId && String(_trk.detalleRutaActualId) === String(idRuta)) {
        _trkConsultarCedisDestinoRuta(idRuta)
            .then(() => _trkRenderDetalleCedisDestino(idRuta, ''))
            .catch(() => _trkRenderDetalleCedisDestino(idRuta, ''));
    }
}

function _trkEstatusRecoleccionTexto(estatus) {
    const st = String(estatus || '').toLowerCase();
    const labels = {
        pendiente: 'Pendiente',
        en_camino: 'En camino',
        en_sitio: 'En sitio',
        recolectada: 'Recolectada',
        recolectado: 'Recolectada',
        no_recolectada: 'No recolectada',
        cancelada: 'Cancelada',
    };
    return labels[st] || (estatus || 'Pendiente');
}

function _trkRenderEstatusRecoleccionDetalle(estatus) {
    const st = String(estatus || '').toLowerCase();
    const cls = st === 'recolectada' || st === 'recolectado'
        ? 'bg-success'
        : (st === 'en_sitio' ? 'bg-warning text-dark' : (st === 'en_camino' ? 'bg-info' : 'bg-label-secondary'));
    return `<span class="badge ${cls}">${_trkChatEscapeHtml(_trkEstatusRecoleccionTexto(estatus))}</span>`;
}

function _trkPuntoPermiteOtp(det) {
    return String(det?.estatus_recoleccion || '').toLowerCase() === 'en_sitio' && Number(det?.id_detalle || 0) > 0;
}

function _trkOtpExpiraInfo(expiraAt) {
    if (!expiraAt) return { texto: 'Sin expiracion', alerta: false };
    const fecha = new Date(String(expiraAt).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) return { texto: expiraAt, alerta: false };
    const diffMs = fecha.getTime() - Date.now();
    const min = Math.ceil(diffMs / 60000);
    if (diffMs <= 0) return { texto: `Expirado ${_trkFormatFechaHora(expiraAt) || expiraAt}`, alerta: true };
    return { texto: `Expira en ${min} min`, alerta: min <= 5 };
}

function _trkRenderOtpEstado(otp) {
    if (!otp) return '<span class="text-muted small">Sin OTP activo</span>';
    const exp = _trkOtpExpiraInfo(otp.expira_at);
    const intentosActuales = Number.isFinite(Number(otp.intentos)) ? Number(otp.intentos) : 0;
    const intentosMax = Number.isFinite(Number(otp.max_intentos)) ? Number(otp.max_intentos) : 3;
    const intentos = `${intentosActuales} / ${intentosMax}`;
    return `
        <div class="small ${exp.alerta ? 'text-danger fw-semibold' : 'text-muted'}">
            <i class="fa-solid fa-key me-1"></i>${_trkChatEscapeHtml(otp.estatus || 'activo')}
            <span class="mx-1">|</span>${_trkChatEscapeHtml(exp.texto)}
            <span class="mx-1">|</span>Intentos ${_trkChatEscapeHtml(intentos)}
        </div>`;
}

function _trkOtpCellHtml(det) {
    const idDetalle = Number(det?.id_detalle || 0);
    if (!_trkPuntoPermiteOtp(det)) {
        return '<span class="text-muted small">Disponible en sitio</span>';
    }
    return `
        <div class="d-flex flex-column gap-1">
            <div id="trkOtpEstado-${idDetalle}" class="trk-otp-status">
                <span class="spinner-border spinner-border-sm me-1"></span>Consultando OTP...
            </div>
            <button type="button" class="btn btn-sm btn-primary btn-generar-otp" data-id="${idDetalle}">
                <i class="fa-solid fa-key me-1"></i>Generar OTP
            </button>
        </div>`;
}

async function _trkConsultarOtpActivo(idDetalle) {
    const r = await trkFetch(`/TrackingRecoleccion/trackingOtpActivo?id_detalle=${encodeURIComponent(idDetalle)}`);
    const otp = r?.otp || r?.datos?.otp || null;
    $(`#trkOtpEstado-${idDetalle}`).html(_trkRenderOtpEstado((r && r.success) ? otp : null));
    return otp;
}

function _trkConsultarOtpsActivosDetalle(detalles) {
    (detalles || []).filter(_trkPuntoPermiteOtp).forEach(det => {
        _trkConsultarOtpActivo(det.id_detalle).catch(() => {
            $(`#trkOtpEstado-${det.id_detalle}`).html('<span class="text-muted small">Sin OTP activo</span>');
        });
    });
}

async function _trkGenerarOtpDetalle(idDetalle) {
    if (!idDetalle) return;
    Swal.fire({
        title: 'Generando OTP...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    try {
        const r = await trkFetch('/TrackingRecoleccion/trackingGenerarOtp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_detalle: idDetalle, canal: 'manual', telefono_destino: null }),
        });
        if (!r.success) {
            Swal.fire({ icon: 'error', title: 'No se pudo generar OTP', text: r.mensaje || r.message || r.detail || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        const otp = r.otp || r.datos?.otp || {};
        $(`#trkOtpEstado-${idDetalle}`).html(_trkRenderOtpEstado(otp));
        Swal.fire({
            icon: 'success',
            title: 'OTP generado',
            html: `<div class="text-center">
                <div class="small text-muted mb-2">Comparte este codigo con el transportista.</div>
                <div class="display-6 fw-bold" style="letter-spacing:.18em;">${_trkChatEscapeHtml(otp.codigo || '')}</div>
                <div class="small text-muted mt-2">${_trkChatEscapeHtml(_trkOtpExpiraInfo(otp.expira_at).texto)}</div>
            </div>`,
            confirmButtonText: 'Entendido',
        });
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo generar el OTP.', confirmButtonText: 'Aceptar' });
    }
}

function _trkDetalleActualizarRecoleccion(idDetalle, estatus) {
    if (!idDetalle) return;
    const $cell = $(`.trk-det-recoleccion[data-id="${idDetalle}"]`);
    if ($cell.length) $cell.html(_trkRenderEstatusRecoleccionDetalle(estatus));
    const $otp = $(`.trk-det-otp[data-id="${idDetalle}"]`);
    if ($otp.length && String(estatus || '').toLowerCase() !== 'en_sitio') {
        $otp.html('<span class="text-muted small">Disponible en sitio</span>');
    }
}

function _trkCargarCreditosInicialSiHaceFalta() {
    return Promise.all([
        _trkCargarEstados().catch(() => {}),
        _trkCargarCreditosSiHaceFalta().catch(() => {}),
    ]);
}

function _trkPrepararModalRuta() {
    return Promise.all([
        _trkCargarCatalogosSiHaceFalta(),
        _trkCargarCreditosInicialSiHaceFalta(),
        _trkCargarOperacionTransportistasSiHaceFalta(true),
    ]);
}

function _trkPrepararModalRutaDetalle(soloLectura = false) {
    return Promise.all([
        _trkCargarCatalogosSiHaceFalta(),
        soloLectura ? Promise.resolve() : _trkCargarCreditosInicialSiHaceFalta(),
        _trkCargarOperacionTransportistasSiHaceFalta(true),
    ]);
}

function _trkCargarTodo() {
    const t0 = performance.now();
    Swal.fire({
        title: 'Obteniendo datos...',
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando informacion del modulo</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    _trkCargarCreditosInicialSiHaceFalta().then(() => {
        Swal.close();
        console.info(`[Tracking Recoleccion] Carga inicial esencial: ${Math.round(performance.now() - t0)} ms`);
    });
}

// --- Modal  -  apertura ------------------------------------
function _trkDiasMinimosProgramacion() {
    const dias = parseInt(window._trackingDiasMinimosProgramacion, 10);
    if (Number.isNaN(dias)) return 2;
    return Math.max(0, Math.min(365, dias));
}

function _trkFechaMinimaProgramacion() {
    const d = new Date();
    d.setDate(d.getDate() + _trkDiasMinimosProgramacion());
    return d.toISOString().split('T')[0];
}

function _trkInicializarModal() {
    // Fecha minima
    const minDate = _trkFechaMinimaProgramacion();
    document.getElementById('rutaFecha').min = minDate;
    $('#rutaDiasMinimosTxt').text(_trkDiasMinimosProgramacion());

    $('#rutaNombre').on('input paste blur', function (e) {
        if (e.type === 'input') {
            const cursor = this.selectionStart;
            const upper = String(this.value || '').toUpperCase();
            if (upper !== this.value) {
                this.value = upper;
                if (Number.isInteger(cursor)) {
                    try { this.setSelectionRange(cursor, cursor); } catch (_) {}
                }
            }
        }
        if (e.type === 'blur') {
            const limpio = _trkSanitizarNombreRuta(this.value);
            if (limpio && limpio !== this.value.trim()) this.value = limpio;
        }
        _trkMarcarCambio();
        _trkProgramarValidacionNombreRuta(e.type === 'blur' ? 80 : 650);
    });

    $('#rutaTransportistaTracking').on('change', function () {
        $('#rutaCedisDestino').val('');
        _trkSincronizarTransportistaSeleccionado();
        _trkRenderTransportistaInfo();
        _trkRenderCedisDestinoInfo();
        _trkMarcarCambio();
    });

    $('#rutaTransportistaSearch')
        .on('focus input', function (e) {
            if (e.type === 'input') {
                $('#rutaTransportistaTracking').val('');
                _trkSincronizarTransportistaSeleccionado();
                _trkRenderTransportistaInfo();
                _trkRenderCedisDestinoInfo();
            }
            _trkRenderTransportistaResultados(this.value);
            $('#rutaTransportistaResults').removeClass('d-none');
        })
        .on('keydown', function (e) {
            if (e.key === 'Escape') $('#rutaTransportistaResults').addClass('d-none');
        });
    $('#rutaTransportistaResults').on('mousedown', '.trk-transport-option', function (e) {
        e.preventDefault();
        _trkSeleccionarTransportista($(this).data('id'));
        _trkMarcarCambio();
    });
    $('#rutaTransportistaAssist').on('click', '.btn-usar-transportista-sugerido', function () {
        _trkSeleccionarTransportista($(this).data('id'));
        _trkMarcarCambio();
    });
    $(document).on('mousedown', function (e) {
        if (!$(e.target).closest('#rutaTransportistaPicker').length) {
            $('#rutaTransportistaResults').addClass('d-none');
        }
    });
    $('#rutaCedisDestino').on('change', function () {
        _trkRenderCedisDestinoInfo();
        _trkRenderTransportistaInfo();
        _trkRenderizarMapa();
        _trkMarcarCambio();
    });

    $('#btnAgregarCredito').on('click', function () {
        const $sel = $('#rutaCreditoSelect');
        const idCred = $sel.val();
        if (!idCred) return;
        const cred = _trk.creditosDisponibles.find(c => String(c.id_credito) === String(idCred));
        if (!cred) return;
        _trkAgregarCreditoALista(cred);
        $sel.val('').trigger('change.select2');
        _trkMarcarCambio();
    });

    // Filtro de creditos por estado
    $('#crdFiltroEstado').on('change', function () {
        const est = $(this).val();
        _trkPoblarFiltroMunicipiosCrd(est);
        _trkRefrescarSelectCreditos();
    });

    // Filtro de creditos por municipio
    $('#crdFiltroMunicipio').on('change', _trkRefrescarSelectCreditos);

    // Mapa refresh
    $('#btnRefreshMap').on('click', _trkRenderizarMapa);
    $('#btnTogglePlanner').on('click', _trkTogglePlaneador);
    $('#trkListaFiltroEstado').on('change', function () {
        _trkPoblarFiltroMunicipiosListaRuta();
        _trkRenderListaCreditos();
    });
    $('#trkListaFiltroMunicipio, #trkListaBuscar').on('change input', _trkRenderListaCreditos);
    $('#trkPlannerGroups').on('click', '.trk-planner-group-head, .trk-planner-mun', function () {
        _trkPlannerEnfocarGrupo(this.dataset.estado || '', this.dataset.municipio || '');
    });

    // Guardar
    $('#btnEnviarRuta').on('click', () => _trkGuardarRuta('enviar'));
    $('#btnActualizarRuta').on('click', async () => {
        const ok = await Swal.fire({
            icon: 'question',
            title: 'Guardar cambios?',
            text: 'Se actualizaran los datos de esta ruta.',
            showCancelButton: true,
            confirmButtonText: 'Si, actualizar',
            cancelButtonText: 'No, regresar',
            confirmButtonColor: '#0d6efd',
        });
        if (ok.isConfirmed) _trkGuardarRuta('actualizar');
    });

    // Cerrar con aviso
    const _closeFn = async () => {
        if (!_trk.soloLectura && _trk.haychangios) {
            const okAuto = await _trkForzarAutosaveBorrador();
            if (!okAuto) {
                const ok = await trkConfirm('Tienes cambios sin guardar. Deseas salir sin guardar?');
                if (!ok) return;
            }
        }
        bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRuta'))?.hide();
        if (_trk.autosaveDirtyLists) {
            _trk.autosaveDirtyLists = false;
            _trkCargarCreditosPaso2();
            _trkCargarBorradores();
            _trkCargarRutas();
        }
    };
    document.getElementById('btnCerrarModal').addEventListener('click', _closeFn);
    document.getElementById('btnCerrarModalFooter').addEventListener('click', _closeFn);

    // Drag-and-drop
    _trk.sortableInstance = Sortable.create(document.getElementById('rutaCreditosList'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: () => {
            _trkRecalcularOrden();
            _trkAplicarEtasAutomaticas();
            _trkRenderListaCreditos();
            _trkRenderizarMapa();
            _trkMarcarCambio();
        },
    });
}

async function _trkAbrirModalNuevo() {
    Swal.fire({
        title: 'Preparando ruta...',
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando catalogos necesarios</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    await _trkPrepararModalRuta();
    Swal.close();
    _trkResetModal();
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarRuta'));
    modal.show();
}

async function _trkAbrirModalConCredito(cred) {
    Swal.fire({
        title: 'Preparando ruta...',
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando catalogos necesarios</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    await _trkPrepararModalRuta();
    Swal.close();
    _trkResetModal();
    _trkAgregarCreditoALista(cred);
    // Pre-seleccionar estado/municipio del credito en los filtros
    if (cred.estado) {
        $('#crdFiltroEstado').val(_trkEstadoMayus(cred.estado, cred.municipio)).trigger('change');
        const municipioFiltro = _trkMunicipioMayus(cred.municipio, cred.estado);
        if (municipioFiltro) {
            $('#crdFiltroMunicipio').val(municipioFiltro).trigger('change');
        }
    }
    const modal = new bootstrap.Modal(document.getElementById('modalRegistrarRuta'));
    modal.show();
}

function _trkResetModal() {
    _trkCancelarAutosaveProgramado();
    _trkRTLimpiar();   // limpia tracking RT antes de resetear el modal
    _trk.idRutaEditando        = null;
    _trk.estatusRuta           = null;
    _trk.soloLectura           = false;
    _trk.rutaCancelada         = false;
    _trk.cargando              = false;
    _trk.creditosEnRuta        = [];
    _trk.routeLegDurations     = [];
    _trk.haychangios           = false;
    _trk.autosaveLastHash      = '';
    _trk.autosaveDirtyLists    = false;
    _trk.nombreRutaDisponible  = null;
    _trk.nombreRutaValidando   = false;
    _trk.nombreRutaUltimoValor = '';
    if (_trk.nombreRutaCheckTimer) {
        clearTimeout(_trk.nombreRutaCheckTimer);
        _trk.nombreRutaCheckTimer = null;
    }
    _trkSetNombreRutaStatus('', '');
    _trkSetAutosaveStatus('Autoguardado listo');
    _trkDesbloquearModal();
    $('#rutaNombre').val('');
    const minDate = _trkFechaMinimaProgramacion();
    $('#rutaFecha').val('').attr('min', minDate);
    // Reset hora a 8:00 AM
    $('#rutaHoraH').val('8');
    $('#rutaHoraM').val('00');
    $('#rutaHoraAmPm').val('AM');
    $('#rutaHoraActInfo').addClass('d-none').text('');
    $('#rutaCancelacionInfo').addClass('d-none').empty();
    $('#btnRefreshMap').show().prop('disabled', false);
    $('#rutaTipoTransportista').val('');
    $('#rutaAgenciaTracking').val('');
    $('#rutaTransportistaTracking').val('');
    $('#rutaTransportistaSearch').val('');
    $('#rutaTransportistaResults').addClass('d-none').empty();
    $('#rutaTransportistaTipoBadge').addClass('d-none').empty();
    $('#rutaCedisDestino').val('');
    $('#rutaCedisDestinoInfo').addClass('d-none').empty();
    $('#rutaTransportistaAssist').addClass('d-none').empty();
    $('#trkListaFiltroEstado, #trkListaFiltroMunicipio, #trkListaBuscar').val('');
    $('#trkListaFiltroMunicipio').prop('disabled', true);
    _trkRefrescarSelectTransportistas();
    // Reset filtros de creditos
    $('#crdFiltroEstado').val('').trigger('change.select2');
    $('#crdFiltroMunicipio').html('<option value=""> -  Todos los municipios  - </option>').prop('disabled', true);
    _trkRefrescarSelectBuscable('#crdFiltroMunicipio');
    _trkPoblarFiltroEstadosCrd();
    $('#modalRegistrarRuta').removeClass('trk-planner-active');
    $('#trkPlannerPanel').addClass('d-none');
    $('#btnTogglePlanner').removeClass('btn-primary').addClass('btn-outline-primary')
        .html('<i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i>Planeador');
    _trkRenderPlaneadorPanel();
    document.getElementById('rutaCreditosList').innerHTML =
        `<div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
            Aun no hay creditos en esta ruta
        </div>`;
    $('#rutaCreditosCount').text(0);
    _trkRefrescarSelectCreditos();
    _trkOcultarMapa();
    $('#mapAlertCoords').addClass('d-none');
    document.getElementById('modalRegistrarRutaLabel').innerHTML =
        '<i class="fa-solid fa-route me-2"></i>Registrar ruta de recoleccion';
}

// --- Creditos en el modal --------------------------------
function _trkPoblarFiltroEstadosCrd() {
    const estadoActual = $('#crdFiltroEstado').val();
    const estados = [...new Set(
        _trk.creditosDisponibles
            .map(c => _trkEstadoMayus(c.estado, c.municipio))
            .filter(Boolean)
    )].sort();
    const $est = $('#crdFiltroEstado');
    $est.html('<option value=""> -  Todos los estados  - </option>');
    estados.forEach(e => {
        const total = _trkTotalCreditosPorEstado(e);
        const agotado = _trkEstadoCreditosAgotado(e);
        const texto = agotado ? `${e} - (${total}) (TODOS SELECCIONADOS EN EL MAPA)` : `${e} - (${total})`;
        $est.append($('<option>', { value: e, text: texto, disabled: agotado }));
    });
    if (estadoActual && !_trkEstadoCreditosAgotado(estadoActual)) {
        $est.val(estadoActual);
    } else {
        $est.val('');
    }
    _trkRefrescarSelectBuscable('#crdFiltroEstado');
    _trkPoblarFiltroMunicipiosCrd($est.val());
}

function _trkIdsCreditosEnRutaSet() {
    return new Set(_trk.creditosEnRuta.map(c => String(c.id_credito)));
}

function _trkMismaUbicacion(a, b) {
    return _trkNormTxt(a) === _trkNormTxt(b);
}

function _trkMunicipioCanonico(municipio) {
    return _trkEstadoTextoBase(municipio);
}

function _trkMunicipioEsEstadoAlias(municipio) {
    const mun = _trkMunicipioCanonico(municipio);
    if (!mun) return false;
    return [
        'CDMX', 'CMDX', 'DF', 'DISTRITO FEDERAL', 'CIUDAD DE MEXICO',
        'ESTADO DE MEXICO', 'ESTADO MEXICO', 'EDO MEX', 'EDO DE MEX',
        'EDO DE MEXICO', 'EDO MEXICO', 'EDOMEX', 'MEXICO',
    ].includes(mun);
}

function _trkMunicipioFiltroCanonico(municipio, estado = '') {
    const mun = _trkMunicipioCanonico(municipio);
    if (!mun) return '';
    if (['NA', 'N/A', 'SIN MUNICIPIO', 'SIN UBICACION', 'NO DISPONIBLE', 'NULL'].includes(mun)) return '';
    if (_trkMunicipioEsEstadoAlias(mun)) return '';
    if (_trkPareceDireccionEnEstado(mun)) return '';
    const estadoCanonico = _trkEstadoCanonico(estado, municipio);
    if (estadoCanonico === 'CIUDAD DE MEXICO' && ['CIUDAD DE MEXICO', 'CDMX', 'CMDX', 'DF'].includes(mun)) return '';
    if (estadoCanonico === 'ESTADO DE MEXICO' && ['MEXICO', 'ESTADO DE MEXICO', 'EDOMEX'].includes(mun)) return '';
    return mun;
}

function _trkUbicacionMayus(valor) {
    return _trkEstadoTextoBase(valor);
}

function _trkEstadoMayus(estado, municipio = '') {
    return _trkEstadoCanonico(estado, municipio) || _trkUbicacionMayus(estado);
}

function _trkMunicipioMayus(municipio, estado = '') {
    return _trkMunicipioFiltroCanonico(municipio, estado) || _trkUbicacionMayus(municipio);
}

function _trkMismaUbicacionMunicipio(municipioA, municipioB) {
    return _trkMunicipioCanonico(municipioA) === _trkMunicipioCanonico(municipioB);
}

function _trkCreditosPorEstado(estado) {
    if (!estado) return [];
    return _trk.creditosDisponibles.filter(c => _trkMismaUbicacionEstado(c.estado, estado, c.municipio));
}

function _trkCreditosPorMunicipio(estado, municipio) {
    if (!estado || !municipio) return [];
    return _trk.creditosDisponibles.filter(c =>
        _trkMismaUbicacionEstado(c.estado, estado, c.municipio) && _trkMismaUbicacionMunicipio(_trkMunicipioMayus(c.municipio, c.estado), municipio)
    );
}

function _trkCreditosRestantesPorEstado(estado) {
    if (!estado) return [];
    const ids = _trkIdsCreditosEnRutaSet();
    return _trkCreditosPorEstado(estado).filter(c => !ids.has(String(c.id_credito)));
}

function _trkCreditosRestantesPorMunicipio(estado, municipio) {
    if (!estado || !municipio) return [];
    const ids = _trkIdsCreditosEnRutaSet();
    return _trkCreditosPorMunicipio(estado, municipio).filter(c => !ids.has(String(c.id_credito)));
}

function _trkEstadoCreditosAgotado(estado) {
    if (!estado) return false;
    const creditosEstado = _trkCreditosPorEstado(estado);
    return creditosEstado.length > 0 && _trkCreditosRestantesPorEstado(estado).length === 0;
}

function _trkTotalCreditosPorEstado(estado) {
    if (!estado) return 0;
    return _trkCreditosRestantesPorEstado(estado).length;
}

function _trkTotalCreditosPorMunicipio(estado, municipio) {
    if (!estado || !municipio) return 0;
    return _trkCreditosRestantesPorMunicipio(estado, municipio).length;
}

function _trkMunicipioCreditosAgotado(estado, municipio) {
    if (!estado || !municipio) return false;
    const creditosMunicipio = _trkCreditosPorMunicipio(estado, municipio);
    return creditosMunicipio.length > 0 && _trkCreditosRestantesPorMunicipio(estado, municipio).length === 0;
}

function _trkPoblarFiltroMunicipiosCrd(estado) {
    const municipioActual = $('#crdFiltroMunicipio').val();
    const $mun = $('#crdFiltroMunicipio');
    $mun.html('<option value="">Todos los municipios</option>');
    $mun.find('option:first').text('Todos los municipios');
    if (!estado) {
        $mun.val('').prop('disabled', true);
        _trkRefrescarSelectBuscable('#crdFiltroMunicipio');
        return;
    }
    const municipiosMap = new Map();
    _trk.creditosDisponibles
        .filter(c => _trkMismaUbicacionEstado(c.estado, estado, c.municipio) && c.municipio)
        .forEach(c => {
            const mun = _trkMunicipioMayus(c.municipio, c.estado);
            if (mun && !municipiosMap.has(mun)) municipiosMap.set(mun, mun);
        });
    const municipios = [...municipiosMap.values()].sort();
    municipios.forEach(m => {
        const total = _trkTotalCreditosPorMunicipio(estado, m);
        const agotado = _trkMunicipioCreditosAgotado(estado, m);
        const texto = agotado ? `${m} - (${total}) (TODOS SELECCIONADOS EN EL MAPA)` : `${m} - (${total})`;
        $mun.append($('<option>', { value: m, text: texto, disabled: agotado }));
    });
    const municipioActualCanonico = _trkMunicipioFiltroCanonico(municipioActual, estado);
    if (municipioActualCanonico && municipios.some(m => _trkMismaUbicacionMunicipio(m, municipioActualCanonico)) && !_trkMunicipioCreditosAgotado(estado, municipioActualCanonico)) {
        $mun.val(municipioActualCanonico);
    } else {
        $mun.val('');
    }
    $mun.prop('disabled', false);
    if (municipios.length === 0) $mun.prop('disabled', true);
    _trkRefrescarSelectBuscable('#crdFiltroMunicipio');
}

function _trkRefrescarFiltrosCreditoUbicacion() {
    _trkPoblarFiltroEstadosCrd();
    _trkRefrescarSelectCreditos();
}

function _trkRefrescarSelectCreditos() {
    const estFiltro = $('#crdFiltroEstado').val();
    const munFiltro = $('#crdFiltroMunicipio').val();
    const $sel = $('#rutaCreditoSelect');
    const idsEnRuta = new Set(_trk.creditosEnRuta.map(c => String(c.id_credito)));
    $sel.html('<option value="">Buscar por credito, modelo, VIN...</option>');
    _trk.creditosDisponibles.forEach(c => {
        if (idsEnRuta.has(String(c.id_credito))) return;
        if (estFiltro && !_trkMismaUbicacionEstado(c.estado, estFiltro, c.municipio)) return;
        if (munFiltro && !_trkMismaUbicacionMunicipio(_trkMunicipioMayus(c.municipio, c.estado), munFiltro)) return;
        const modelo = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const label  = `#${c.id_credito}  -  ${modelo || '(sin modelo)'}  -  ${c.bin || ' - '}`;
        $sel.append(`<option value="${c.id_credito}">${label}</option>`);
    });
    _trkRefrescarSelectBuscable('#rutaCreditoSelect');
}

function _trkAgregarCreditoALista(cred) {
    // RN-03: no duplicados
    if (_trk.creditosEnRuta.find(c => String(c.id_credito) === String(cred.id_credito))) {
        Swal.fire({ icon: 'warning', title: 'Aviso', text: 'Este credito ya esta en la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    cred = {
        ...cred,
        estado_raw: cred.estado || '',
        estado: _trkEstadoMayus(cred.estado, cred.municipio),
        municipio: _trkMunicipioMayus(cred.municipio, cred.estado),
    };
    const tipoSeleccionado = _trkTransportistaSeleccionado()?.tipo_transportista || $('#rutaTipoTransportista').val();
    if (tipoSeleccionado === 'interno' && !_trkEsZonaInterna(cred.estado, cred.municipio)) {
        Swal.fire({
            icon: 'warning',
            title: 'Zona no permitida',
            text: 'Este credito esta fuera de CDMX/zona metropolitana. Para esta ubicacion usa transportista externo.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    cred.orden_ruta = _trk.creditosEnRuta.length + 1;
    cred.estatus_confirmacion_gestor = cred.estatus_confirmacion_gestor || 'pendiente';
    _trk.creditosEnRuta.push(cred);
    _trkAplicarEtasAutomaticas();
    _trkRenderListaCreditos();
    _trkRefrescarFiltrosCreditoUbicacion();
    _trkRenderizarMapa();
}

function _trkQuitarCredito(idCred) {
    _trk.creditosEnRuta = _trk.creditosEnRuta.filter(c => String(c.id_credito) !== String(idCred));
    _trkRecalcularOrden();
    _trkAplicarEtasAutomaticas();
    _trkRenderListaCreditos();
    _trkRefrescarFiltrosCreditoUbicacion();
    _trkRenderizarMapa();
    _trkMarcarCambio();
}

function _trkRenderListaCreditos() {
    const $list = $('#rutaCreditosList');
    _trkPoblarFiltrosListaRuta();
    const creditosVisibles = _trkCreditosRutaFiltrados();
    const isEmpty = _trk.creditosEnRuta.length === 0;
    const filaLectura = _trkRutaEstaCancelada();
    $('#rutaCreditosCount').text(_trk.creditosEnRuta.length);
    if (_trk.sortableInstance) _trk.sortableInstance.option('disabled', filaLectura);
    _trkRenderPlaneadorPanel();
    _trkRenderSugerenciasTransportistasRuta();

    if (isEmpty) {
        $list.html(`<div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
            Aun no hay creditos en esta ruta
        </div>`);
        return;
    }
    if (!creditosVisibles.length) {
        $list.html(`<div class="text-center text-muted py-3 small">
            <i class="fa-solid fa-filter opacity-25 fa-2x mb-1 d-block"></i>
            Sin coincidencias con los filtros aplicados
        </div>`);
        return;
    }

    $list.html('');
    creditosVisibles.forEach((c) => {
        const idx = _trk.creditosEnRuta.findIndex(x => String(x.id_credito) === String(c.id_credito));
        const modelo    = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ') || ' - ';
        const badgeConf = CONF_LABEL[c.estatus_confirmacion_gestor] || CONF_LABEL['pendiente'];
        const tienePin  = c.latitud_manual && c.longitud_manual;
        const tieneUbicacionDefault = !tienePin && (!!(c.latitud && c.longitud) || !!String(c.direccion || '').trim());
        const pinClass  = tienePin
            ? 'btn-pin-ubicacion tiene-pin'
            : (tieneUbicacionDefault ? 'btn-pin-ubicacion pin-default-blink' : 'btn-pin-ubicacion');
        const etaInfo   = _trkEstadoEta(c, c.estatus_recoleccion);
        const pinTitle  = tienePin
            ? 'Ubicacion manual asignada (clic para cambiar)'
            : (tieneUbicacionDefault ? 'Ubicacion default detectada (clic para confirmar o ajustar)' : 'Asignar ubicacion en mapa');

        // Los creditos en ruta nunca se bloquean
        // En rutas canceladas queda como consulta: solo se permite enfocar el credito en el mapa.

        // Elementos que solo aparecen en modo edicion
        const dragHandle  = filaLectura ? '' : '<i class="fa-solid fa-grip-vertical drag-handle"></i>';
        const confControl = filaLectura
            ? badgeConf
            : `<select class="form-select form-select-sm py-0 ms-1 select-conf-gestor"
                    style="max-width:130px;font-size:.75rem;"
                    data-id="${c.id_credito}">
                <option value="pendiente"   ${c.estatus_confirmacion_gestor === 'pendiente'   ? 'selected' : ''}>Pendiente</option>
                <option value="confirmado"  ${c.estatus_confirmacion_gestor === 'confirmado'  ? 'selected' : ''}>Confirmado</option>
                <option value="rechazado"   ${c.estatus_confirmacion_gestor === 'rechazado'   ? 'selected' : ''}>Rechazado</option>
                <option value="en_revision" ${c.estatus_confirmacion_gestor === 'en_revision' ? 'selected' : ''}>En revision</option>
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
        const etaLectura = filaLectura
            ? `<div class="eta-row d-flex align-items-center gap-1 mt-1 flex-wrap">
                    <span class="text-muted fw-semibold" style="font-size:.7rem;white-space:nowrap;">Horas estimadas:</span>
                    ${etaInfo.html}
                    <span class="badge bg-light text-dark border">${_trkChatEscapeHtml(c.fecha_eta || 'Sin fecha')}</span>
                    ${(c.hora_eta_ini && c.hora_eta_fin)
                        ? `<span class="badge bg-light text-dark border">Inicio: ${_trkFormatHora(c.hora_eta_ini)}</span>
                           <span class="badge bg-light text-dark border">Llegada: ${_trkFormatHora(c.hora_eta_fin)}</span>`
                        : '<span class="badge bg-light text-dark border">Sin horario</span>'}
                </div>`
            : null;

        const html = `
        <div class="track-credito-row" data-id="${c.id_credito}" title="Clic para ubicar este credito en el mapa">
            ${dragHandle}
            <span class="orden-num">${idx + 1}</span>
            <div class="d-flex flex-column gap-0 flex-grow-1" style="min-width:0;">
                <span class="fw-semibold text-truncate">#${c.id_credito}  -  ${c.nombre_cliente || ' - '} ${_trkNuevoCreditoRutaHtml(c)}</span>
                <span class="text-muted" style="font-size:.75rem;">
                    <span class="trk-moto-model-pill"><i class="fa-solid fa-motorcycle" title="Modelo de motocicleta"></i>${_trkChatEscapeHtml(modelo || '-')}</span>
                    &middot; VIN: ${_trkChatEscapeHtml(c.bin || '-')}
                    &nbsp;|&nbsp;${_trkRenderLocationBadges(c.estado, c.municipio)}
                </span>
                ${_trkRenderDireccionCredito(c)}
                ${filaLectura ? etaLectura : `<div class="eta-row d-flex align-items-center gap-1 mt-1 flex-wrap">
                    <span class="text-muted fw-semibold" style="font-size:.7rem;white-space:nowrap;">Horas estimadas:</span>
                    ${etaInfo.html}
                    <input type="date" class="form-control eta-fecha" data-id="${c.id_credito}" value="${c.fecha_eta || ''}" min="${_trkFechaRutaBase()}" style="max-width:130px;" title="Fecha estimada de llegada">
                    <span class="badge bg-label-secondary">Inicio</span>
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="ini" style="width:62px;flex-shrink:0;" title="Hora de inicio">${optsIni}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="ini" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaIni.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos inicio">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="ini" style="width:62px;flex-shrink:0;" title="AM/PM inicio">
                        <option value="AM"${etaIni.ampm === 'AM' ? ' selected' : ''}>AM</option>
                        <option value="PM"${etaIni.ampm === 'PM' ? ' selected' : ''}>PM</option>
                    </select>
                    <span class="text-muted" style="font-size:.7rem;line-height:1;">-</span>
                    <span class="badge bg-label-secondary">Llegada</span>
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="Hora de llegada (minimo 4 horas despues)">${optsFin}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="fin" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaFin.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos llegada (minimo 4 horas despues)">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="AM/PM llegada (minimo 4 horas despues)">
                        <option value="AM"${etaFin.ampm === 'AM' ? ' selected' : ''}>AM</option>
                        <option value="PM"${etaFin.ampm === 'PM' ? ' selected' : ''}>PM</option>
                    </select>
                </div>`}
            </div>
            ${confControl}
            ${actionBtns}
        </div>`;
        $list.append(html);
    });

    // Eventos de creditos (siempre activos, incluso en modo ver ruta)
    $list.find('.track-credito-row').off('click').on('click', function (e) {
            if ($(e.target).closest('button, select, input, .select2, .drag-handle').length) return;
            _trkEnfocarCreditoEnMapa($(this).data('id'));
        });
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
                _trkAplicarEtasAutomaticas();
                _trkRenderListaCreditos();
                _trkRenderizarMapa();
            }
            _trkMarcarCambio();
        });
        $list.find('.eta-fecha').off('change').on('change', function () {
            const id = $(this).data('id');
            const c  = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
            if (c) {
                const base = _trkFechaRutaBase();
                let val = $(this).val() || null;
                if (val && base && _trkCompararFecha(val, base) < 0) {
                    val = base;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha de horas estimadas invalida',
                        text: 'Las horas estimadas no pueden ser anteriores a la fecha de salida de la ruta.',
                        confirmButtonText: 'Aceptar',
                    });
                }
                c.fecha_eta = val;
                c.eta_manual = !!c.fecha_eta;
            }
            _trkRenderListaCreditos();
            _trkMarcarCambio();
        });
        $list.find('.eta-h, .eta-ap').off('change').on('change', function () {
            const id   = $(this).data('id');
            const tipo = $(this).data('tipo');
            const c    = _trk.creditosEnRuta.find(x => String(x.id_credito) === String(id));
            if (c) {
                if (tipo === 'ini') c.hora_eta_ini = _trkLeerEtaHora(id, 'ini');
                else                c.hora_eta_fin = _trkLeerEtaHora(id, 'fin');
                _trkAsegurarEtaMinima(c);
                c.eta_manual = true;
            }
            _trkRenderListaCreditos();
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
                        text: `"${n}" no es valido. Deben ser entre 00 y 59.`,
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
                    _trkAsegurarEtaMinima(c);
                    c.eta_manual = true;
                }
                _trkRenderListaCreditos();
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
    // Actualizar numeracion visual
    items.forEach((el, i) => {
        const numEl = el.querySelector('.orden-num');
        if (numEl) numEl.textContent = i + 1;
    });
}



// --- Mapa ------------------------------------------------
function _trkOcultarMapa() {
    document.getElementById('trackMap').style.display      = 'none';
    document.getElementById('mapPlaceholder').style.display = 'flex';
}

function _trkRenderizarMapa() {
    const creditos = _trk.creditosEnRuta;
    const cedisDestino = _trkCedisDestinoSeleccionado();
    const cedisPos = _trkCedisDestinoPosicion(cedisDestino);
    if (!creditos.length && !cedisPos) {
        _trkOcultarMapa();
        return;
    }
    // Verificar si alguno tiene coordenadas o direccion
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
    if (_trk.mapInstance && window.google?.maps) {
        google.maps.event.trigger(_trk.mapInstance, 'resize');
    }

    if (!_trk.mapLoaded) {
        // Cargar Google Maps API dinamicamente
        const script     = document.createElement('script');
        script.src       = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry,places&callback=_trkMapCallback`;
        script.async     = true;
        script.defer     = true;
        document.head.appendChild(script);
        _trk.mapLoaded = true;
        window._trkMapCallback = () => _trkDibujarMapa(creditos);
    } else if (typeof google !== 'undefined' && google.maps) {
        _trkDibujarMapa(creditos);
    } else {
        // El script ya esta cargando (desde el picker); esperar
        const waitForMaps = setInterval(() => {
            if (typeof google !== 'undefined' && google.maps) {
                clearInterval(waitForMaps);
                _trkDibujarMapa(creditos);
            }
        }, 150);
        setTimeout(() => clearInterval(waitForMaps), 10000);
    }
}

// --- Estilos oscuros para Google Maps -------------------
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

function _trkActivarTraficoMapa(map, layerKey, state = _trk) {
    if (!map || typeof google === 'undefined' || !google.maps || !google.maps.TrafficLayer) return;
    if (!state[layerKey]) {
        state[layerKey] = new google.maps.TrafficLayer();
    }
    state[layerKey].setMap(map);
}

function _trkDibujarMapa(creditos) {
    const mapDiv = document.getElementById('trackMap');
    if (!mapDiv || typeof google === 'undefined') return;
    const cedisDestino = _trkCedisDestinoSeleccionado();
    const cedisPos = _trkCedisDestinoPosicion(cedisDestino);

    if (!_trk.mapInstance) {
        _trk.mapInstance = new google.maps.Map(mapDiv, {
            zoom: 10,
            center: { lat: 20.6597, lng: -103.3496 },
            styles: [],
            mapTypeControl: false,
        });
        _trk.geocoder = new google.maps.Geocoder();
    }
    _trkActivarTraficoMapa(_trk.mapInstance, 'trafficLayer');

    // -- Limpiar mapa anterior ---------------------------------
    _trk.mapMarkers.forEach(m => m.setMap(null));
    _trk.mapMarkers = [];
    _trk.mapMarkersByCredito = {};
    _trk.creditoPosiciones = {};
    _trkRTRepaintLiveMap();
    if (_trk.directionsRenderer) {
        _trk.directionsRenderer.setMap(null);
        _trk.directionsRenderer = null;
    }

    const map    = _trk.mapInstance;
    const bounds = new google.maps.LatLngBounds();

    // -- Separar por estatus -----------------------------------
    const paraRuta     = creditos
        .filter(c => c.estatus_confirmacion_gestor === 'confirmado')
        .sort((a, b) => (a.orden_ruta || 99) - (b.orden_ruta || 99));
    const soloPin      = creditos.filter(c => c.estatus_confirmacion_gestor !== 'confirmado');

    // -- Icono SVG numerado por estatus -----------------------
    const markerColorByStatus = {
        confirmado:  { fill: '#e53935', text: '#fff' },
        pendiente:   { fill: '#fdd835', text: '#111827' },
        en_revision: { fill: '#fb8c00', text: '#fff' },
        rechazado:   { fill: '#ef4444', text: '#fff' },
    };
    const numeroMarkerRuta = (c, fallback = 1) => {
        const n = parseInt(c?.orden_ruta, 10);
        return Number.isFinite(n) && n > 0 ? n : fallback;
    };
    const svgIcon = (num, estatus = 'confirmado') => {
        const cfg = markerColorByStatus[estatus] || { fill: '#64748b', text: '#fff' };
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34">
            <circle cx="17" cy="17" r="15" fill="${cfg.fill}" stroke="#fff" stroke-width="2.5"/>
            <text x="17" y="22" text-anchor="middle" fill="${cfg.text}"
                  font-size="${num > 9 ? 11 : 13}" font-weight="bold" font-family="Arial,sans-serif">${num}</text>
        </svg>`;
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(34, 34),
            anchor:     new google.maps.Point(17, 17),
        };
    };

    const cedisIcon = () => {
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 42 42">
            <circle cx="21" cy="21" r="18" fill="#0d6efd" stroke="#fff" stroke-width="3"/>
            <path d="M12 20 21 14l9 6v10H12V20Z" fill="#fff"/>
            <path d="M15 30v-7h12v7M15 23h12M21 23v7" fill="none" stroke="#0d6efd" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`;
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(42, 42),
            anchor:     new google.maps.Point(21, 21),
        };
    };

    // -- Resolver coordenadas de un credito -------------------
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

    // -- Resolver todo en paralelo y dibujar ------------------
    Promise.all([
        ...paraRuta.map(c  => resolverPos(c).then(pos => ({ c, pos, tipo: 'ruta' }))),
        ...soloPin.map(c   => resolverPos(c).then(pos => ({ c, pos, tipo: 'pin'  }))),
    ]).then(results => {
        // Marcadores sin ruta (pendiente / en_revision / rechazado)
        results
            .filter(r => r.tipo === 'pin' && r.pos)
            .sort((a, b) => numeroMarkerRuta(a.c, 99) - numeroMarkerRuta(b.c, 99))
            .forEach(({ c, pos }, idx) => {
                const num = numeroMarkerRuta(c, idx + 1);
                const m = new google.maps.Marker({
                    map,
                    position: pos,
                    icon:  svgIcon(num, c.estatus_confirmacion_gestor || 'pendiente'),
                    title: `Posicion ${num} - #${c.id_credito} \u2014 ${c.nombre_cliente || ''} (${c.estatus_confirmacion_gestor || 'pendiente'})`,
                    zIndex: 10 + num,
                });
                _trk.mapMarkers.push(m);
                _trk.mapMarkersByCredito[String(c.id_credito)] = m;
                _trk.creditoPosiciones[String(c.id_credito)] = pos;
                m.addListener('click', () => _trkEnfocarCreditoEnMapa(c.id_credito, { scroll: false }));
                bounds.extend(pos);
            });

        // Marcadores numerados (confirmados)
        const rutaConPos = results
            .filter(r => r.tipo === 'ruta' && r.pos)
            .sort((a, b) => (a.c.orden_ruta || 99) - (b.c.orden_ruta || 99));

        rutaConPos.forEach(({ c, pos }, idx) => {
            const num = numeroMarkerRuta(c, idx + 1);
            const m = new google.maps.Marker({
                map,
                position: pos,
                icon:  svgIcon(num, c.estatus_confirmacion_gestor || 'confirmado'),
                title: `Posicion ${num} - #${c.id_credito} \u2014 ${c.nombre_cliente || ''}`,
                zIndex: 20 + num,
            });
            _trk.mapMarkers.push(m);
            _trk.mapMarkersByCredito[String(c.id_credito)] = m;
            _trk.creditoPosiciones[String(c.id_credito)] = pos;
            m.addListener('click', () => _trkEnfocarCreditoEnMapa(c.id_credito, { scroll: false }));
            bounds.extend(pos);
        });

        if (cedisPos) {
            const nombreCedis = cedisDestino?.nombre_agencia || 'CEDIS destino';
            const mCedis = new google.maps.Marker({
                map,
                position: cedisPos,
                icon: cedisIcon(),
                title: `${nombreCedis} - destino del transportista`,
                zIndex: 80,
            });
            _trk.mapMarkers.push(mCedis);
            bounds.extend(cedisPos);
        }

        if (!bounds.isEmpty()) map.fitBounds(bounds);

        const todosConfirmados = creditos.length > 0 && creditos.every(c => c.estatus_confirmacion_gestor === 'confirmado');
        const rutaConDestino = (todosConfirmados && cedisPos)
            ? [...rutaConPos, { c: { id_credito: '__cedis_destino__' }, pos: cedisPos, tipo: 'cedis' }]
            : rutaConPos;

        if (rutaConDestino.length < 2) {
            if (rutaConPos.length === 1) {
                map.setCenter(rutaConPos[0].pos);
                map.setZoom(14);
            } else if (cedisPos) {
                map.setCenter(cedisPos);
                map.setZoom(13);
            }
            return;
        }

        // -- Trazar polilinea de ruta (solo confirmados) ------
        const origin      = new google.maps.LatLng(rutaConDestino[0].pos.lat, rutaConDestino[0].pos.lng);
        const destination = new google.maps.LatLng(rutaConDestino[rutaConDestino.length - 1].pos.lat, rutaConDestino[rutaConDestino.length - 1].pos.lng);
        const waypoints   = rutaConDestino.slice(1, -1).map(r => ({
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
            drivingOptions: {
                departureTime: new Date(),
                trafficModel: google.maps.TrafficModel?.BEST_GUESS || 'bestguess',
            },
            provideRouteAlternatives: false,
        }, (result, status) => {
            if (status === 'OK') {
                _trk.directionsRenderer.setDirections(result);
                const legs = result.routes?.[0]?.legs || [];
                _trk.routeLegDurations = legs.map(l => l.duration_in_traffic?.value || l.duration?.value || null);
                _trkAplicarEtasAutomaticas();
                _trkRenderListaCreditos();
            }
            map.fitBounds(bounds);
        });
    });
}

function _trkCreditoPosicionBasica(cred) {
    if (!cred) return null;
    const lat = parseFloat(cred.latitud_manual ?? cred.latitud);
    const lng = parseFloat(cred.longitud_manual ?? cred.longitud);
    if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
        return { lat, lng };
    }
    return null;
}

function _trkResolverPosCredito(cred) {
    const directa = _trkCreditoPosicionBasica(cred);
    if (directa) return Promise.resolve(directa);
    const cache = _trk.creditoPosiciones[String(cred?.id_credito || '')];
    if (cache) return Promise.resolve(cache);
    if (!_trk.geocoder || !cred || !(cred.direccion || (cred.municipio && cred.estado))) {
        return Promise.resolve(null);
    }
    const addr = [cred.direccion, cred.municipio, cred.estado, 'Mexico'].filter(Boolean).join(', ');
    return new Promise(resolve => {
        _trk.geocoder.geocode({ address: addr }, (res, st) => {
            if (st === 'OK' && res[0]) {
                resolve({ lat: res[0].geometry.location.lat(), lng: res[0].geometry.location.lng() });
            } else {
                resolve(null);
            }
        });
    });
}

function _trkEnfocarCreditoEnMapa(idCredito, opts = {}) {
    const scroll = opts.scroll !== false;
    const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(idCredito));
    if (!cred) return;

    if (scroll) {
        document.getElementById('trackMapContainer')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    $('#rutaCreditosList .track-credito-row').removeClass('trk-row-focused');
    $(`#rutaCreditosList .track-credito-row[data-id="${idCredito}"]`).addClass('trk-row-focused');

    const enfocar = (pos) => {
        if (!_trk.mapInstance || !pos) {
            Swal.fire({ icon: 'info', title: 'Sin ubicacion', text: 'Este credito todavia no tiene una ubicacion valida para mostrar en el mapa.', confirmButtonText: 'Aceptar' });
            return;
        }
        const latLng = new google.maps.LatLng(pos.lat, pos.lng);
        _trk.mapInstance.panTo(latLng);
        _trk.mapInstance.setZoom(Math.max(_trk.mapInstance.getZoom() || 0, 13));
        const marker = _trk.mapMarkersByCredito[String(idCredito)];
        if (marker) {
            marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => marker.setAnimation(null), 1200);
        }
    };

    if (!window._trackGoogleMapsKey) {
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no esta disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!_trk.mapInstance || typeof google === 'undefined' || !google.maps) {
        if ((opts.retry || 0) >= 4) {
            Swal.fire({ icon: 'info', title: 'Mapa cargando', text: 'No se pudo enfocar el credito todavia. Intenta de nuevo en unos segundos.', confirmButtonText: 'Aceptar' });
            return;
        }
        _trkRenderizarMapa();
        setTimeout(() => _trkEnfocarCreditoEnMapa(idCredito, { scroll: false, retry: (opts.retry || 0) + 1 }), 700);
        return;
    }
    const cached = _trk.creditoPosiciones[String(idCredito)];
    if (cached) {
        enfocar(cached);
        return;
    }
    _trkResolverPosCredito(cred).then(pos => {
        if (pos) _trk.creditoPosiciones[String(idCredito)] = pos;
        enfocar(pos);
    });
}

// --- Map Picker (Plan B: clic en mapa para asignar coords) ------------------
const _trkPicker = {
    modal:             null,
    mapInstance:       null,
    marker:            null,
    creditoId:         null,
    selectedLat:       null,
    selectedLng:       null,
    selectedEstado:    null,
    selectedMunicipio: null,
    selectedDireccion: null,
    geocoder:          null,
    autocomplete:      null,
    searchDebounce:    null,
    listenersBound:    false,
    restoreRouteModal: false,
    closingByConfirm:  false,
    trafficLayer:      null,
};

function _trkAbrirMapPicker(cred) {
    if (!window._trackGoogleMapsKey) {
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no esta disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }

    const routeModalEl = document.getElementById('modalRegistrarRuta');
    const routeModalAbierto = routeModalEl?.classList.contains('show');
    _trkPicker.restoreRouteModal = !!routeModalAbierto;
    _trkPicker.closingByConfirm = false;

    _trkPicker.creditoId         = cred.id_credito;
    _trkPicker.selectedLat        = null;
    _trkPicker.selectedLng        = null;
    _trkPicker.selectedEstado     = null;
    _trkPicker.selectedMunicipio  = null;
    _trkPicker.selectedDireccion  = null;

    // Etiqueta en el modal
    document.getElementById('mapPickerCreditoLabel').textContent =
        `  -  #${cred.id_credito} ${cred.nombre_cliente ? '(' + cred.nombre_cliente + ')' : ''}`;
    document.getElementById('mapPickerCoordsLabel').innerHTML =
        '<i class="fa-solid fa-crosshairs me-1"></i>Sin seleccion';
    document.getElementById('mapPickerGeoInfo').classList.add('d-none');
    document.getElementById('mapPickerDireccionWrap').classList.add('d-none');
    document.getElementById('mapPickerDireccionCompleta').textContent = ' - ';
    document.getElementById('btnConfirmarMapPicker').disabled = true;
    document.getElementById('mapPickerSearch').value = '';

    // Mostrar modal
    if (!_trkPicker.modal) {
        _trkPicker.modal = new bootstrap.Modal(document.getElementById('modalMapPicker'));
        document.getElementById('btnCerrarMapPicker').addEventListener('click',  () => _trkPicker.modal.hide());
        document.getElementById('btnCancelarMapPicker').addEventListener('click', () => _trkPicker.modal.hide());
        document.getElementById('btnConfirmarMapPicker').addEventListener('click', _trkConfirmarMapPicker);
        document.getElementById('modalMapPicker').addEventListener('hidden.bs.modal', _trkRestaurarModalRutaDespuesPicker);
    }

    // Inicializar mapa despues de que el modal sea visible (necesario para que el div tenga dimensiones)
    document.getElementById('modalMapPicker').addEventListener('shown.bs.modal', _trkInicializarMapPicker, { once: true });

    const abrirPicker = () => _trkPicker.modal.show();
    if (routeModalAbierto) {
        routeModalEl.addEventListener('hidden.bs.modal', abrirPicker, { once: true });
        bootstrap.Modal.getOrCreateInstance(routeModalEl).hide();
    } else {
        abrirPicker();
    }
}

function _trkRestaurarModalRutaDespuesPicker() {
    if (!_trkPicker.restoreRouteModal) return;
    _trkPicker.restoreRouteModal = false;
    const routeModalEl = document.getElementById('modalRegistrarRuta');
    if (!routeModalEl) return;
    bootstrap.Modal.getOrCreateInstance(routeModalEl).show();
    setTimeout(() => {
        _trkRenderListaCreditos();
        _trkRenderizarMapa();
    }, 250);
}

function _trkInicializarMapPicker() {
    const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(_trkPicker.creditoId));
    if (!cred) return;

    // Centro: coordenadas manuales existentes > coords del credito > GDL
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
            _trkActivarTraficoMapa(_trkPicker.mapInstance, 'trafficLayer', _trkPicker);

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
                        title: 'Ubicacion seleccionada',
                    });
                    _trkPicker.marker.addListener('dragend', (ev) => {
                        _trkPicker.selectedLat = ev.latLng.lat();
                        _trkPicker.selectedLng = ev.latLng.lng();
                        _trkPickerReverseGeocode(ev.latLng);
                    });
                } else {
                    _trkPicker.marker.setPosition(e.latLng);
                }

                _trkPickerReverseGeocode(e.latLng);
                document.getElementById('btnConfirmarMapPicker').disabled = false;
            });

            // -- Google Places Autocomplete --------------------------
            if (false && !_trkPicker.autocomplete && google.maps.places) {
                const searchInput = document.getElementById('mapPickerSearch');
                _trkPicker.autocomplete = new google.maps.places.Autocomplete(searchInput, {
                    fields: ['geometry', 'address_components', 'formatted_address'],
                    componentRestrictions: { country: 'mx' },
                });
                // Evitar que Enter en el input cierre el modal
                searchInput.addEventListener('keydown', ev => { if (ev.key === 'Enter') ev.preventDefault(); });
                _trkPicker.autocomplete.addListener('place_changed', () => {
                    const place = _trkPicker.autocomplete.getPlace();
                    if (!place.geometry || !place.geometry.location) return;
                    const loc = place.geometry.location;
                    _trkPicker.selectedLat = loc.lat();
                    _trkPicker.selectedLng = loc.lng();
                    _trkPicker.mapInstance.panTo(loc);
                    _trkPicker.mapInstance.setZoom(16);
                    if (!_trkPicker.marker) {
                        _trkPicker.marker = new google.maps.Marker({
                            map: _trkPicker.mapInstance,
                            position: loc,
                            draggable: true,
                            animation: google.maps.Animation.DROP,
                            title: 'Ubicacion seleccionada',
                        });
                        _trkPicker.marker.addListener('dragend', (ev) => {
                            _trkPicker.selectedLat = ev.latLng.lat();
                            _trkPicker.selectedLng = ev.latLng.lng();
                            _trkPickerReverseGeocode(ev.latLng);
                        });
                    } else {
                        _trkPicker.marker.setPosition(loc);
                    }
                    _trkPickerExtraerGeo(place.address_components);
                    _trkActualizarLabelCoordsicker();
                    document.getElementById('btnConfirmarMapPicker').disabled = false;
                });
                document.getElementById('btnLimpiarMapSearch').addEventListener('click', () => {
                    searchInput.value = '';
                    searchInput.focus();
                });
            }
            _trkPickerInicializarBusqueda();
        } else {
            // Reusar mapa: re-centrar y limpiar marcador anterior
            _trkPicker.mapInstance.setCenter({ lat: centerLat, lng: centerLng });
            _trkPicker.mapInstance.setZoom(15);
            if (_trkPicker.marker) {
                _trkPicker.marker.setMap(null);
                _trkPicker.marker = null;
            }
            document.getElementById('mapPickerSearch').value = '';
            _trkPickerInicializarBusqueda();
        }

        // Si ya tenia coords manuales, mostrar marcador previo
        if (cred.latitud_manual && cred.longitud_manual) {
            const prevPos = { lat: parseFloat(cred.latitud_manual), lng: parseFloat(cred.longitud_manual) };
            _trkPicker.marker = new google.maps.Marker({
                map: _trkPicker.mapInstance,
                position: prevPos,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'Ubicacion guardada',
            });
            _trkPicker.selectedLat = prevPos.lat;
            _trkPicker.selectedLng = prevPos.lng;
            _trkPicker.marker.addListener('dragend', (ev) => {
                _trkPicker.selectedLat = ev.latLng.lat();
                _trkPicker.selectedLng = ev.latLng.lng();
                _trkPickerReverseGeocode(ev.latLng);
            });
            _trkPickerReverseGeocode(new google.maps.LatLng(prevPos.lat, prevPos.lng));
            document.getElementById('btnConfirmarMapPicker').disabled = false;
        } else if (cred.latitud && cred.longitud) {
            const defaultPos = { lat: parseFloat(cred.latitud), lng: parseFloat(cred.longitud) };
            _trkPickerCrearMarker(defaultPos, 'Ubicacion default detectada');
            _trkPicker.selectedLat = defaultPos.lat;
            _trkPicker.selectedLng = defaultPos.lng;
            _trkPickerReverseGeocode(new google.maps.LatLng(defaultPos.lat, defaultPos.lng));
            document.getElementById('btnConfirmarMapPicker').disabled = false;
        }

        google.maps.event.trigger(_trkPicker.mapInstance, 'resize');
    };

    if (typeof google !== 'undefined' && google.maps) {
        initMap();
    } else if (_trk.mapLoaded) {
        // El script ya esta cargando (desde el mapa de ruta); esperar a que este listo
        const waitForMaps = setInterval(() => {
            if (typeof google !== 'undefined' && google.maps) {
                clearInterval(waitForMaps);
                initMap();
            }
        }, 150);
        setTimeout(() => clearInterval(waitForMaps), 10000);
    } else {
        // Cargar Maps si aun no esta
        const script = document.createElement('script');
        script.src   = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry,places&callback=_trkMapPickerReady`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        window._trkMapPickerReady = initMap;
        _trk.mapLoaded = true;
    }
}

function _trkPickerCrearMarker(latLng, title = 'Ubicacion seleccionada') {
    if (!_trkPicker.marker) {
        _trkPicker.marker = new google.maps.Marker({
            map: _trkPicker.mapInstance,
            position: latLng,
            draggable: true,
            animation: google.maps.Animation.DROP,
            title,
        });
        _trkPicker.marker.addListener('dragend', (ev) => {
            _trkPicker.selectedLat = ev.latLng.lat();
            _trkPicker.selectedLng = ev.latLng.lng();
            _trkPickerReverseGeocode(ev.latLng);
        });
    } else {
        _trkPicker.marker.setMap(_trkPicker.mapInstance);
        _trkPicker.marker.setPosition(latLng);
    }
}

function _trkPickerAplicarLugar(latLng, components = null, addressText = '') {
    if (!_trkPicker.mapInstance || !latLng) return;
    _trkPicker.selectedLat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
    _trkPicker.selectedLng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
    _trkPicker.selectedDireccion = addressText || null;
    _trkPicker.mapInstance.panTo(latLng);
    _trkPicker.mapInstance.setZoom(16);
    _trkPickerCrearMarker(latLng, addressText || 'Ubicacion seleccionada');
    if (components) _trkPickerExtraerGeo(components, addressText);
    else _trkPickerReverseGeocode(latLng);
    _trkActualizarLabelCoordsicker();
    document.getElementById('btnConfirmarMapPicker').disabled = false;
}

function _trkPickerBuscarTextoLibre() {
    const input = document.getElementById('mapPickerSearch');
    const query = (input?.value || '').trim();
    if (query.length < 4 || !_trkPicker.mapInstance) return;
    if (!_trkPicker.geocoder) _trkPicker.geocoder = new google.maps.Geocoder();
    const sesgo = _trkPicker.mapInstance.getCenter();
    _trkPicker.geocoder.geocode({
        address: `${query}, Mexico`,
        componentRestrictions: { country: 'MX' },
        location: sesgo,
    }, (results, status) => {
        if (status !== 'OK' || !results || !results[0]) return;
        const loc = results[0].geometry.location;
        _trkPickerAplicarLugar(loc, results[0].address_components, results[0].formatted_address || query);
    });
}

function _trkPickerInicializarBusqueda() {
    const searchInput = document.getElementById('mapPickerSearch');
    if (!searchInput || !window.google || !google.maps || !_trkPicker.mapInstance) return;

    if (!_trkPicker.autocomplete && google.maps.places) {
        _trkPicker.autocomplete = new google.maps.places.Autocomplete(searchInput, {
            fields: ['geometry', 'address_components', 'formatted_address', 'name'],
            componentRestrictions: { country: 'mx' },
        });
        _trkPicker.autocomplete.addListener('place_changed', () => {
            const place = _trkPicker.autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                _trkPickerBuscarTextoLibre();
                return;
            }
            _trkPickerAplicarLugar(
                place.geometry.location,
                place.address_components,
                place.formatted_address || place.name || searchInput.value
            );
        });
    }

    if (_trkPicker.autocomplete?.bindTo) {
        _trkPicker.autocomplete.bindTo('bounds', _trkPicker.mapInstance);
    }

    if (!_trkPicker.listenersBound) {
        searchInput.addEventListener('keydown', ev => {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                _trkPickerBuscarTextoLibre();
            }
        });
        searchInput.addEventListener('input', () => {
            clearTimeout(_trkPicker.searchDebounce);
            _trkPicker.searchDebounce = setTimeout(_trkPickerBuscarTextoLibre, 700);
        });
        document.getElementById('btnLimpiarMapSearch').addEventListener('click', () => {
            clearTimeout(_trkPicker.searchDebounce);
            searchInput.value = '';
            searchInput.focus();
            if (_trkPicker.marker) {
                _trkPicker.marker.setMap(null);
                _trkPicker.marker = null;
            }
            _trkPicker.selectedLat = null;
            _trkPicker.selectedLng = null;
            _trkPicker.selectedEstado = null;
            _trkPicker.selectedMunicipio = null;
            _trkPicker.selectedDireccion = null;
            document.getElementById('mapPickerGeoInfo').classList.add('d-none');
            document.getElementById('mapPickerDireccionWrap').classList.add('d-none');
            document.getElementById('mapPickerDireccionCompleta').textContent = ' - ';
            document.getElementById('mapPickerCoordsLabel').innerHTML =
                '<i class="fa-solid fa-crosshairs me-1"></i>Sin seleccion';
            document.getElementById('btnConfirmarMapPicker').disabled = true;
        });
        _trkPicker.listenersBound = true;
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

function _trkPickerExtraerGeo(components, formattedAddress = '') {
    if (!components) return;
    let estado = '', localidad = '', alcaldia = '', sublocalidad = '', barrio = '';
    components.forEach(c => {
        if (c.types.includes('administrative_area_level_1')) estado = c.long_name;
        if (c.types.includes('locality')) localidad = c.long_name;
        if (c.types.includes('administrative_area_level_2')) alcaldia = c.long_name;
        if (c.types.includes('sublocality_level_1')) sublocalidad = c.long_name;
        if (c.types.includes('neighborhood')) barrio = c.long_name;
    });
    const estadoCanonico = _trkEstadoCanonico(estado, alcaldia || sublocalidad || localidad || barrio);
    const municipio = estadoCanonico === 'CIUDAD DE MEXICO'
        ? (alcaldia || sublocalidad || localidad || barrio)
        : (localidad || alcaldia || sublocalidad || barrio);
    _trkPicker.selectedEstado    = _trkEstadoMayus(estadoCanonico || estado, municipio) || null;
    _trkPicker.selectedMunicipio = _trkMunicipioMayus(municipio, _trkPicker.selectedEstado) || null;
    _trkPicker.selectedDireccion = formattedAddress || _trkPicker.selectedDireccion || null;
    const geoDiv  = document.getElementById('mapPickerGeoInfo');
    const geoSpan = document.getElementById('mapPickerEstadoMun');
    const dirWrap = document.getElementById('mapPickerDireccionWrap');
    const dirSpan = document.getElementById('mapPickerDireccionCompleta');
    if (_trkPicker.selectedEstado || _trkPicker.selectedMunicipio) {
        geoSpan.textContent = [_trkPicker.selectedMunicipio, _trkPicker.selectedEstado].filter(Boolean).join(', ');
        geoDiv.classList.remove('d-none');
    } else {
        geoDiv.classList.add('d-none');
    }
    if (_trkPicker.selectedDireccion) {
        dirSpan.textContent = _trkPicker.selectedDireccion;
        dirWrap.classList.remove('d-none');
    } else {
        dirSpan.textContent = ' - ';
        dirWrap.classList.add('d-none');
    }
}

function _trkPickerReverseGeocode(latLng) {
    if (!window.google || !google.maps) return;
    if (!_trkPicker.geocoder) _trkPicker.geocoder = new google.maps.Geocoder();
    _trkPicker.selectedDireccion = null;
    document.getElementById('mapPickerDireccionCompleta').textContent = ' - ';
    document.getElementById('mapPickerDireccionWrap').classList.add('d-none');
    _trkPicker.geocoder.geocode({ location: latLng }, (results, status) => {
        if (status === 'OK' && results && results[0]) {
            _trkPickerExtraerGeo(results[0].address_components, results[0].formatted_address || '');
        }
        _trkActualizarLabelCoordsicker();
    });
}

function _trkConfirmarMapPicker() {
    const lat = _trkPicker.selectedLat;
    const lng = _trkPicker.selectedLng;
    if (lat === null || lng === null) return;

    const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(_trkPicker.creditoId));
    if (cred) {
        cred.latitud_manual  = lat;
        cred.longitud_manual = lng;
        // Sobrescribir tambien las props que usa el mapa de ruta
        cred.latitud  = lat;
        cred.longitud = lng;
        // Aplicar estado/municipio detectados por geocodificacion
        if (_trkPicker.selectedEstado)    cred.estado    = _trkEstadoMayus(_trkPicker.selectedEstado, _trkPicker.selectedMunicipio);
        if (_trkPicker.selectedMunicipio) cred.municipio = _trkMunicipioMayus(_trkPicker.selectedMunicipio, cred.estado);
        if (_trkPicker.selectedDireccion) {
            cred.direccion_google = _trkPicker.selectedDireccion;
            cred.direccion = _trkPicker.selectedDireccion;
        }
        _trkMarcarCambio();
    }

    _trkPicker.closingByConfirm = true;
    _trkPicker.modal.hide();
    _trkRenderListaCreditos();
}

// --- Guardar ruta ----------------------------------------
// --- Helpers de hora AM/PM ------------------------------
function _trkFormatHora(horaStr) {
    if (!horaStr) return ' - ';
    const parts = horaStr.split(':');
    const hh    = parseInt(parts[0], 10);
    const mm    = parts[1] || '00';
    const ampm  = hh >= 12 ? 'PM' : 'AM';
    const h12   = hh % 12 || 12;
    return `${h12}:${mm} ${ampm}`;
}

function _trkFormatFechaHora(valor) {
    if (!valor) return '';
    const raw = String(valor).trim();
    const d = new Date(raw.includes('T') ? raw : raw.replace(' ', 'T'));
    if (isNaN(d.getTime())) return raw;
    return d.toLocaleString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
}

function _trkNuevoCreditoRutaHtml(item) {
    const fecha = item?.fecha_agregado_fmt || _trkFormatFechaHora(item?.fecha_agregado) || '';
    if (!fecha) return '';
    const safeFecha = _trkChatEscapeHtml(fecha);
    return `<span class="badge bg-warning text-dark ms-1" title="Agregado el ${safeFecha}">NUEVO</span>
            <span class="text-muted ms-1" style="font-size:.72rem;">Agregado ${safeFecha}</span>`;
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

function _trkHoraToMinutes(horaStr) {
    if (!horaStr) return null;
    const [h, m] = String(horaStr).split(':').map(v => parseInt(v, 10));
    if (isNaN(h) || isNaN(m)) return null;
    return h * 60 + m;
}

function _trkMinutesToHora(totalMin) {
    totalMin = ((Math.round(totalMin) % 1440) + 1440) % 1440;
    const h = Math.floor(totalMin / 60);
    const m = totalMin % 60;
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

const TRK_ETA_MIN_MINUTES = 240;

function _trkFechaRutaBase() {
    return $('#rutaFecha').val() || new Date().toISOString().split('T')[0];
}

function _trkFechaToDate(fecha) {
    if (!fecha) return null;
    const d = new Date(`${fecha}T00:00:00`);
    return isNaN(d.getTime()) ? null : d;
}

function _trkCompararFecha(a, b) {
    const da = _trkFechaToDate(a);
    const db = _trkFechaToDate(b);
    if (!da || !db) return 0;
    return da.getTime() - db.getTime();
}

function _trkAsegurarEtaFechaMinima(c) {
    const base = _trkFechaRutaBase();
    if (!c || !base) return false;
    if (!c.fecha_eta || _trkCompararFecha(c.fecha_eta, base) < 0) {
        c.fecha_eta = base;
        return true;
    }
    return false;
}

function _trkAsegurarEtasFechaMinima() {
    let cambio = false;
    _trk.creditosEnRuta.forEach(c => {
        if (_trkAsegurarEtaFechaMinima(c)) cambio = true;
    });
    return cambio;
}

function _trkAsegurarEtaMinima(c) {
    if (!c || !c.hora_eta_ini) return false;
    _trkAsegurarEtaFechaMinima(c);
    const ini = _trkHoraToMinutes(c.hora_eta_ini);
    const fin = _trkHoraToMinutes(c.hora_eta_fin);
    if (ini === null) return false;
    if (fin === null || (fin - ini) < TRK_ETA_MIN_MINUTES) {
        c.hora_eta_fin = _trkMinutesToHora(ini + TRK_ETA_MIN_MINUTES);
        return true;
    }
    return false;
}

function _trkEtaDate(c, tipo = 'ini') {
    const fecha = c.fecha_eta;
    const hora  = tipo === 'fin' ? c.hora_eta_fin : c.hora_eta_ini;
    if (!fecha || !hora) return null;
    const d = new Date(`${fecha}T${hora.length === 5 ? hora + ':00' : hora}`);
    return isNaN(d.getTime()) ? null : d;
}

function _trkEstadoEta(c, estatusReal = null) {
    const ini = _trkEtaDate(c, 'ini');
    const fin = _trkEtaDate(c, 'fin');
    const real = String(estatusReal || '').toLowerCase();
    const done = ['recolectada', 'completado', 'completada'].includes(real);
    if (!ini || !fin) {
        return { key: 'sin_eta', label: 'Sin horas estimadas', html: '<span class="badge bg-secondary-subtle text-secondary border">Sin horas estimadas</span>' };
    }
    if (done) {
        return { key: 'completado', label: 'Completado', html: '<span class="badge bg-success-subtle text-success border">Completado</span>' };
    }
    const now = new Date();
    const minToIni = Math.round((ini - now) / 60000);
    const minPastFin = Math.round((now - fin) / 60000);
    if (now >= ini && now <= fin) {
        return { key: 'en_ventana', label: 'En ventana', html: '<span class="badge bg-info-subtle text-info border">En ventana</span>' };
    }
    if (now > fin) {
        return { key: 'vencido', label: `Vencido ${minPastFin} min`, html: `<span class="badge bg-danger-subtle text-danger border">Vencido ${minPastFin} min</span>` };
    }
    if (minToIni <= 60) {
        return { key: 'proximo', label: `Proximo ${minToIni} min`, html: `<span class="badge bg-warning-subtle text-warning border">Proximo ${minToIni} min</span>` };
    }
    return { key: 'programado', label: 'Programado', html: '<span class="badge bg-light text-dark border">Programado</span>' };
}

function _trkAplicarEtasAutomaticas(force = false) {
    if (!_trk.creditosEnRuta.length) return;
    const fecha = _trkFechaRutaBase();
    const salida = _trkHoraToMinutes(_trkHoraToPayload());
    if (!fecha || salida === null) return;

    const ordenados = [..._trk.creditosEnRuta]
        .sort((a, b) => (a.orden_ruta || 99) - (b.orden_ruta || 99));
    let etaIni = salida + 30; // colchon operativo para carga/salida hacia primer punto
    ordenados.forEach((c, idx) => {
        if (idx > 0) {
            const travelMin = _trk.routeLegDurations[idx - 1]
                ? Math.ceil(_trk.routeLegDurations[idx - 1] / 60)
                : 30;
            etaIni += travelMin + 15; // traslado + margen de servicio/maniobra
        }
        const debeActualizar = force || c.eta_auto || !c.eta_manual || !c.fecha_eta || !c.hora_eta_ini || !c.hora_eta_fin;
        if (!debeActualizar) return;
        c.fecha_eta    = fecha;
        c.hora_eta_ini = _trkMinutesToHora(etaIni);
        c.hora_eta_fin = _trkMinutesToHora(etaIni + TRK_ETA_MIN_MINUTES);
        c.eta_auto     = true;
        c.eta_manual   = false;
    });
}

function _trkDistanciaKm(a, b) {
    if (!a || !b) return 0;
    const rad = d => d * Math.PI / 180;
    const r = 6371;
    const dLat = rad(b.lat - a.lat);
    const dLng = rad(b.lng - a.lng);
    const lat1 = rad(a.lat);
    const lat2 = rad(b.lat);
    const h = Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) ** 2;
    return 2 * r * Math.asin(Math.sqrt(h));
}

function _trkDistanciaOrdenKm(puntos) {
    let total = 0;
    for (let i = 1; i < puntos.length; i++) {
        total += _trkDistanciaKm(puntos[i - 1].pos, puntos[i].pos);
    }
    return total;
}

function _trkRutaVecinoMasCercano(puntos) {
    if (puntos.length <= 2) return puntos;
    const pendientes = puntos.slice(1);
    const orden = [puntos[0]];
    while (pendientes.length) {
        const ultimo = orden[orden.length - 1];
        let bestIdx = 0;
        let bestDist = Infinity;
        pendientes.forEach((p, idx) => {
            const d = _trkDistanciaKm(ultimo.pos, p.pos);
            if (d < bestDist) {
                bestDist = d;
                bestIdx = idx;
            }
        });
        orden.push(pendientes.splice(bestIdx, 1)[0]);
    }
    return orden;
}

function _trkEvaluarRutaNoOptima() {
    const puntos = [..._trk.creditosEnRuta]
        .filter(c => c.estatus_confirmacion_gestor === 'confirmado')
        .sort((a, b) => (a.orden_ruta || 99) - (b.orden_ruta || 99))
        .map(c => ({
            id: c.id_credito,
            orden: c.orden_ruta,
            pos: _trk.creditoPosiciones[String(c.id_credito)] || _trkCreditoPosicionBasica(c),
        }))
        .filter(p => p.pos);
    if (puntos.length < 3) return { noOptima: false };

    const actualKm = _trkDistanciaOrdenKm(puntos);
    const sugerida = _trkRutaVecinoMasCercano(puntos);
    const sugeridaKm = _trkDistanciaOrdenKm(sugerida);
    let peorTriple = false;

    for (let i = 0; i <= puntos.length - 3; i++) {
        const trio = puntos.slice(i, i + 3);
        const actualTrio = _trkDistanciaOrdenKm(trio);
        const alternoTrio = _trkDistanciaOrdenKm([trio[0], trio[2], trio[1]]);
        if (actualTrio > alternoTrio * 1.12 && (actualTrio - alternoTrio) >= 20) {
            peorTriple = true;
            break;
        }
    }

    return {
        noOptima: peorTriple || (actualKm > sugeridaKm * 1.12 && (actualKm - sugeridaKm) >= 20),
        actualKm,
        sugeridaKm,
    };
}

async function _trkConfirmarRutaNoOptimaSiAplica(modo) {
    if (modo !== 'enviar') return true;
    const evalRuta = _trkEvaluarRutaNoOptima();
    if (!evalRuta.noOptima) return true;
    const r = await Swal.fire({
        icon: 'warning',
        title: 'Ruta no optima',
        text: 'Tu ruta no es la mas optima para el recorrido, implicarian mayor inversion de recursos, estas seguro de que quieres enviarla asi?',
        showCancelButton: true,
        confirmButtonText: 'Si, enviarla asi',
        cancelButtonText: 'Revisar ruta',
        confirmButtonColor: '#0d9488',
    });
    return r.isConfirmed;
}

function _trkUbicacionesResumenEnvio() {
    const grupos = new Map();
    (_trk.creditosEnRuta || []).forEach(c => {
        const estado = _trkEstadoMayus(c?.estado, c?.municipio) || 'SIN ESTADO';
        const municipio = _trkMunicipioMayus(c?.municipio, estado) || 'SIN MUNICIPIO';
        const key = `${estado}|||${municipio}`;
        if (!grupos.has(key)) grupos.set(key, { estado, municipio, creditos: [] });
        grupos.get(key).creditos.push({
            id: c?.id_credito || '',
            orden: parseInt(c?.orden_ruta, 10) || 9999,
        });
    });
    return [...grupos.values()]
        .map(g => ({
            ...g,
            creditos: g.creditos.sort((a, b) => a.orden - b.orden),
            total: g.creditos.length,
        }))
        .sort((a, b) => `${a.estado} ${a.municipio}`.localeCompare(`${b.estado} ${b.municipio}`));
}

function _trkResumenCreditoChips(creditos, limite = 6) {
    const lista = Array.isArray(creditos) ? creditos : [];
    const visibles = lista.slice(0, limite);
    const chips = visibles.map(c =>
        `<span class="badge bg-light text-dark border me-1 mb-1">#${_trkChatEscapeHtml(c.id)}</span>`
    ).join('');
    const restantes = lista.length - visibles.length;
    return chips + (restantes > 0
        ? `<span class="badge bg-label-secondary mb-1">+${restantes}</span>`
        : '');
}

function _trkResumenUbicacionesPorEstado(ubicaciones) {
    const estados = new Map();
    ubicaciones.forEach(u => {
        if (!estados.has(u.estado)) estados.set(u.estado, { estado: u.estado, total: 0, municipios: [] });
        const bucket = estados.get(u.estado);
        bucket.total += u.total;
        bucket.municipios.push(u);
    });
    return [...estados.values()].sort((a, b) => b.total - a.total || a.estado.localeCompare(b.estado));
}

function _trkRenderResumenUbicacionesNormal(ubicaciones) {
    if (!ubicaciones.length) return '<div class="text-muted small">Sin ubicaciones detectadas</div>';
    const rows = ubicaciones.map(u => `
        <div class="d-grid gap-2 align-items-start border-top px-2 py-2" style="grid-template-columns:1fr 1fr 1.25fr;">
            <div><span class="badge bg-label-primary">${_trkChatEscapeHtml(u.estado)}</span></div>
            <div><span class="badge bg-label-info">${_trkChatEscapeHtml(u.municipio)}</span></div>
            <div>${_trkResumenCreditoChips(u.creditos, 8)}</div>
        </div>`).join('');
    return `
        <div class="border rounded overflow-hidden">
            <div class="d-grid gap-2 small text-muted fw-semibold text-uppercase bg-light px-2 py-1" style="grid-template-columns:1fr 1fr 1.25fr;">
                <span>Estado</span><span>Municipio</span><span>Creditos</span>
            </div>
            ${rows}
        </div>`;
}

function _trkRenderResumenUbicacionesVolumen(ubicaciones) {
    if (!ubicaciones.length) return '<div class="text-muted small">Sin ubicaciones detectadas</div>';
    const estados = _trkResumenUbicacionesPorEstado(ubicaciones);
    return `
        <div class="small text-muted mb-2">Vista volumen: agrupada por estado para revisar rapido el recorrido.</div>
        <div style="max-height:260px;overflow:auto;">
            ${estados.map((estado, idx) => `
                <details class="border rounded mb-2" ${idx < 2 ? 'open' : ''}>
                    <summary class="d-flex align-items-center justify-content-between gap-2 px-2 py-2" style="cursor:pointer;">
                        <span class="fw-semibold">${_trkChatEscapeHtml(estado.estado)}</span>
                        <span class="badge bg-label-primary">${estado.total} credito${estado.total !== 1 ? 's' : ''}</span>
                    </summary>
                    <div class="px-2 pb-2">
                        ${estado.municipios.map(u => `
                            <div class="border-top py-2">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                    <span class="badge bg-label-info">${_trkChatEscapeHtml(u.municipio)}</span>
                                    <span class="small text-muted">${u.total}</span>
                                </div>
                                <div>${_trkResumenCreditoChips(u.creditos, 10)}</div>
                            </div>`).join('')}
                    </div>
                </details>`).join('')}
        </div>`;
}

async function _trkConfirmarResumenEnvioRuta({ nombre, fecha }) {
    const total = _trk.creditosEnRuta.length;
    const confirmados = _trk.creditosEnRuta.filter(c => c.estatus_confirmacion_gestor === 'confirmado').length;
    const ubicaciones = _trkUbicacionesResumenEnvio();
    const horaSalida = _trkHoraToPayload();
    const modoVolumen = total > 12 || ubicaciones.length > 6 || ubicaciones.some(u => u.total > 5);
    const ubicacionesHtml = modoVolumen
        ? _trkRenderResumenUbicacionesVolumen(ubicaciones)
        : _trkRenderResumenUbicacionesNormal(ubicaciones);

    const res = await Swal.fire({
        icon: 'question',
        title: 'Resumen de ruta',
        html: `
            <div class="text-start">
                <div class="mb-2">
                    <div class="small text-muted fw-semibold text-uppercase">Nombre de la ruta</div>
                    <div class="fw-bold">${_trkChatEscapeHtml(nombre || 'Ruta sin nombre')}</div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="small text-muted fw-semibold text-uppercase">FECHA DE SALIDA</div>
                        <div>${_trkChatEscapeHtml(fecha || 'Sin fecha')}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted fw-semibold text-uppercase">Hora de salida</div>
                        <div>${_trkChatEscapeHtml(_trkFormatHora(horaSalida))}</div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="small text-muted fw-semibold text-uppercase">Creditos confirmados</div>
                    <span class="badge bg-success">${confirmados}</span>
                </div>
                <div>
                    <div class="small text-muted fw-semibold text-uppercase mb-1">Ubicaciones</div>
                    ${ubicacionesHtml}
                </div>
                <div class="alert alert-info py-2 px-3 mt-3 mb-0">
                    Deseas confirmar la ruta?
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Si, confirmar ruta',
        cancelButtonText: 'Revisar',
        confirmButtonColor: '#0d9488',
        width: 640,
    });
    return res.isConfirmed;
}

function _trkSetAutosaveStatus(text, tone = 'muted') {
    const $status = $('#trkAutosaveStatus');
    if (!$status.length) return;
    const toneClass = tone === 'success' ? 'text-success'
        : tone === 'danger' ? 'text-danger'
        : tone === 'warning' ? 'text-warning'
        : 'text-muted';
    $status
        .removeClass('text-muted text-success text-danger text-warning')
        .addClass(toneClass)
        .html(`<i class="fa-solid fa-cloud-arrow-up me-1"></i>${_trkChatEscapeHtml(text)}`);
}

function _trkCancelarAutosaveProgramado() {
    if (_trk.autosaveTimer) {
        clearTimeout(_trk.autosaveTimer);
        _trk.autosaveTimer = null;
    }
    if (_trk.autosaveStatusTimer) {
        clearTimeout(_trk.autosaveStatusTimer);
        _trk.autosaveStatusTimer = null;
    }
}

function _trkSleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function _trkAutosaveHash() {
    return JSON.stringify({
        id_ruta: _trk.idRutaEditando || 0,
        nombre: _trkSanitizarNombreRuta($('#rutaNombre').val()),
        fecha: $('#rutaFecha').val() || '',
        hora: _trkHoraToPayload(),
        id_transportista: $('#rutaTransportistaTracking').val() || '',
        id_cedis_destino: $('#rutaCedisDestino').val() || '',
        creditos: _trk.creditosEnRuta.map(c => ({
            id_credito: c.id_credito,
            orden_ruta: c.orden_ruta,
            estatus_confirmacion_gestor: c.estatus_confirmacion_gestor || 'pendiente',
            fecha_eta: c.fecha_eta || null,
            hora_eta_ini: c.hora_eta_ini || null,
            hora_eta_fin: c.hora_eta_fin || null,
            latitud: c.latitud || null,
            longitud: c.longitud || null,
        })),
    });
}

function _trkPuedeAutosaveBorrador() {
    if (_trk.cargando || _trk.soloLectura || _trkRutaEstaCancelada()) return false;
    if (_trk.idRutaEditando && String(_trk.estatusRuta || '').toLowerCase() !== 'borrador') return false;
    if (!_trkSanitizarNombreRuta($('#rutaNombre').val())) return false;
    if (_trk.nombreRutaValidando || _trk.nombreRutaDisponible === false) return false;
    if ($('#rutaCedisDestino').val() && !_trkValidarReglasTransportista().ok) return false;
    return true;
}

function _trkProgramarAutosaveBorrador(delay = 1200) {
    if (_trk.cargando || _trk.soloLectura || _trkRutaEstaCancelada()) return;
    if (_trk.autosaveTimer) clearTimeout(_trk.autosaveTimer);
    _trkSetAutosaveStatus('Cambios pendientes...', 'warning');
    _trk.autosaveTimer = setTimeout(_trkEjecutarAutosaveBorrador, delay);
}

async function _trkEjecutarAutosaveBorrador() {
    _trk.autosaveTimer = null;
    if (!_trkPuedeAutosaveBorrador()) {
        if (_trk.nombreRutaValidando) {
            _trkSetAutosaveStatus('Esperando validacion del nombre...', 'warning');
            _trkProgramarAutosaveBorrador(700);
            return;
        }
        const nombre = _trkSanitizarNombreRuta($('#rutaNombre').val());
        _trkSetAutosaveStatus(nombre ? 'Autoguardado pendiente' : 'Escribe el nombre para autoguardar', 'warning');
        return;
    }
    if (_trkAutosaveHash() === _trk.autosaveLastHash) {
        _trkSetAutosaveStatus('Borrador al dia', 'success');
        return;
    }
    if (_trk.autosaveInFlight) {
        _trk.autosavePending = true;
        return;
    }

    _trk.autosaveInFlight = true;
    _trkSetAutosaveStatus('Guardando borrador...', 'muted');
    const ok = await _trkGuardarBorradorAutomatico();
    _trk.autosaveInFlight = false;

    if (ok) {
        _trk.autosaveLastHash = _trkAutosaveHash();
        _trkSetAutosaveStatus('Borrador guardado', 'success');
        if (_trk.autosaveStatusTimer) clearTimeout(_trk.autosaveStatusTimer);
        _trk.autosaveStatusTimer = setTimeout(() => _trkSetAutosaveStatus('Autoguardado listo'), 2500);
    } else {
        _trkSetAutosaveStatus('No se pudo autoguardar', 'danger');
    }

    if (_trk.autosavePending) {
        _trk.autosavePending = false;
        _trkProgramarAutosaveBorrador(500);
    }
}

function _trkExtraerIdRutaRespuesta(r) {
    return r?.id_ruta || r?.idRuta || r?.datos?.id_ruta || r?.data?.id_ruta || null;
}

function _trkDescargarPdfEvidenciaRuta(idRuta) {
    if (!idRuta) return;
    const url = `/TrackingRecoleccion/pdfEvidenciaRuta?id_ruta=${encodeURIComponent(idRuta)}`;
    const now = new Date();
    const dd = String(now.getDate()).padStart(2, '0');
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const yyyy = now.getFullYear();
    let hh = now.getHours();
    const ampm = hh >= 12 ? 'PM' : 'AM';
    hh = hh % 12 || 12;
    const min = String(now.getMinutes()).padStart(2, '0');
    const filename = `EvidenciaRuta_${dd}.${mm}.${yyyy}_${String(hh).padStart(2, '0')}.${min}_${ampm}_No.${idRuta}.pdf`;
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    setTimeout(() => a.remove(), 1000);
}

function _trkGuardarRutaError(mensaje, opts = {}) {
    if (opts.silent) return false;
    Swal.fire({ icon: 'warning', title: 'Campo requerido', text: mensaje, confirmButtonText: 'Aceptar' });
    return false;
}

function _trkSetNombreRutaStatus(tipo, texto = '') {
    const el = document.getElementById('rutaNombreStatus');
    const input = document.getElementById('rutaNombre');
    if (!el || !input) return;
    input.classList.remove('is-valid', 'is-invalid');
    el.className = 'form-text small mt-1';
    if (!texto) {
        el.innerHTML = '';
        return;
    }
    if (tipo === 'loading') {
        el.classList.add('text-muted');
        el.innerHTML = `<span class="spinner-border spinner-border-sm me-1" style="width:.8rem;height:.8rem;"></span>${_trkChatEscapeHtml(texto)}`;
        return;
    }
    if (tipo === 'ok') {
        input.classList.add('is-valid');
        el.classList.add('text-success');
        el.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i>${_trkChatEscapeHtml(texto)}`;
        return;
    }
    if (tipo === 'error') {
        input.classList.add('is-invalid');
        el.classList.add('text-danger');
        el.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i>${_trkChatEscapeHtml(texto)}`;
    }
}

async function _trkForzarAutosaveBorrador() {
    if (_trk.soloLectura || _trkRutaEstaCancelada()) return true;
    _trkCancelarAutosaveProgramado();

    const nombre = _trkSanitizarNombreRuta($('#rutaNombre').val());
    if (!nombre) return false;

    if (_trk.nombreRutaCheckTimer) {
        clearTimeout(_trk.nombreRutaCheckTimer);
        _trk.nombreRutaCheckTimer = null;
    }

    const nombreOk = await _trkValidarNombreRutaDisponible(false);
    if (!nombreOk) return false;
    _trkCancelarAutosaveProgramado();

    while (_trk.autosaveInFlight) {
        await _trkSleep(150);
    }

    if (_trkAutosaveHash() === _trk.autosaveLastHash) {
        _trk.haychangios = false;
        _trkSetAutosaveStatus('Borrador al dia', 'success');
        return true;
    }

    if (!_trkPuedeAutosaveBorrador()) return false;

    _trkSetAutosaveStatus('Guardando borrador...', 'muted');
    const ok = await _trkGuardarBorradorAutomatico();
    if (ok) {
        _trk.autosaveLastHash = _trkAutosaveHash();
        _trkSetAutosaveStatus('Borrador guardado', 'success');
    }
    return ok;
}

function _trkProgramarValidacionNombreRuta(delay = 650) {
    if (_trk.nombreRutaCheckTimer) clearTimeout(_trk.nombreRutaCheckTimer);
    _trk.nombreRutaDisponible = null;
    const nombre = _trkSanitizarNombreRuta($('#rutaNombre').val());
    if (!nombre) {
        _trk.nombreRutaValidando = false;
        _trk.nombreRutaUltimoValor = '';
        _trkSetNombreRutaStatus('', '');
        return;
    }
    _trkSetNombreRutaStatus('loading', 'Consultando disponibilidad del nombre...');
    _trk.nombreRutaCheckTimer = setTimeout(() => {
        _trkValidarNombreRutaDisponible(false);
    }, delay);
}

async function _trkValidarNombreRutaDisponible(mostrarDisponible = true) {
    const nombre = _trkSanitizarNombreRuta($('#rutaNombre').val());
    if (!nombre) {
        _trk.nombreRutaValidando = false;
        _trk.nombreRutaDisponible = null;
        _trk.nombreRutaUltimoValor = '';
        _trkSetNombreRutaStatus('', '');
        return false;
    }

    if (_trk.nombreRutaUltimoValor === nombre && _trk.nombreRutaDisponible !== null && !_trk.nombreRutaValidando) {
        return _trk.nombreRutaDisponible;
    }

    const seq = ++_trk.nombreRutaCheckSeq;
    _trk.nombreRutaValidando = true;
    _trkSetNombreRutaStatus('loading', 'Consultando disponibilidad del nombre...');
    try {
        const r = await trkFetch('/TrackingRecoleccion/validarNombreRuta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nombre_ruta: nombre,
                id_ruta: _trk.idRutaEditando || 0,
            }),
        });
        if (seq !== _trk.nombreRutaCheckSeq) return false;
        _trk.nombreRutaUltimoValor = nombre;
        _trk.nombreRutaDisponible = !!r.disponible;
        _trk.nombreRutaValidando = false;
        if (r.nombre_limpio && r.nombre_limpio !== $('#rutaNombre').val().trim()) {
            $('#rutaNombre').val(r.nombre_limpio);
        }
        if (_trk.nombreRutaDisponible) {
            _trkSetNombreRutaStatus(mostrarDisponible ? 'ok' : '', mostrarDisponible ? 'Nombre disponible.' : '');
            if (_trk.haychangios && _trkPuedeAutosaveBorrador()) {
                _trkProgramarAutosaveBorrador(250);
            }
            return true;
        }
        _trkSetNombreRutaStatus('error', r.message || r.mensaje || 'Nombre no permitido, ya existe una ruta con este nombre.');
        return false;
    } catch {
        if (seq !== _trk.nombreRutaCheckSeq) return false;
        _trk.nombreRutaValidando = false;
        _trk.nombreRutaDisponible = null;
        _trkSetNombreRutaStatus('error', 'No se pudo validar el nombre. Intenta de nuevo.');
        return false;
    }
}

function _trkMostrarConflictosRuta(resp) {
    const errores = Array.isArray(resp?.errores) ? resp.errores : [];
    if (!errores.length) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: resp?.mensaje || resp?.message || 'Error al guardar la ruta.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }

    const bloques = errores.map(err => {
        const titulo = (err.tipo === 'nombre_fecha' || err.tipo === 'nombre_ruta')
            ? 'Nombre de ruta duplicado'
            : 'Transportista con horario ocupado';
        const rutas = Array.isArray(err.rutas) ? err.rutas : [];
        const rows = rutas.map(r => `
            <div class="border rounded p-2 mb-1">
                <div class="fw-semibold">
                    <span class="trk-route-folio me-1">#${_trkChatEscapeHtml(r.id_ruta || '')}</span>
                    ${_trkChatEscapeHtml(_trkSanitizarNombreRuta(r.nombre_ruta || 'Ruta') || 'Ruta')}
                </div>
                <div class="text-muted">
                    ${_trkChatEscapeHtml(r.fecha_programada_fmt || '')}
                    ${r.hora_salida_fmt ? ` - ${_trkChatEscapeHtml(r.hora_salida_fmt)}` : ''}
                    ${r.estatus_ruta ? ` - ${_trkChatEscapeHtml(r.estatus_ruta)}` : ''}
                </div>
                ${r.nombre_transportista ? `<div class="text-muted">${_trkChatEscapeHtml(r.nombre_transportista)}</div>` : ''}
            </div>`).join('');
        return `
            <div class="text-start mb-3">
                <div class="fw-bold mb-1">${_trkChatEscapeHtml(titulo)}</div>
                <div class="small mb-2">${_trkChatEscapeHtml(err.message || err.mensaje || 'Conflicto detectado.')}</div>
                ${rows}
            </div>`;
    }).join('');

    Swal.fire({
        icon: 'warning',
        title: 'No se puede guardar la ruta',
        html: `<div class="small">${bloques}</div>`,
        confirmButtonText: 'Corregir datos',
        confirmButtonColor: '#0d9488',
        width: 620,
    });
}

async function _trkGuardarBorradorAutomatico() {
    if (!_trkPuedeAutosaveBorrador()) return false;

    const nombre = _trkSanitizarNombreRuta($('#rutaNombre').val());
    if (nombre && nombre !== $('#rutaNombre').val().trim()) $('#rutaNombre').val(nombre);
    const fecha = $('#rutaFecha').val() || _trkFechaMinimaProgramacion();
    if (!$('#rutaFecha').val()) $('#rutaFecha').val(fecha);
    const idTransportista = $('#rutaTransportistaTracking').val();
    const transportistaSel = _trkTransportistaSeleccionado();
    const tipoTransportista = transportistaSel?.tipo_transportista || '';
    const idAgenciaTracking = transportistaSel?.id_agencia || '';
    const idCedisDestino = $('#rutaCedisDestino').val();
    const geoRuta = _trkGeoResumenRuta();
    const municipio = _trkMunicipioMayus($('#crdFiltroMunicipio').val() || geoRuta.municipio, $('#crdFiltroEstado').val() || geoRuta.estado);
    const estado = _trkEstadoMayus($('#crdFiltroEstado').val() || geoRuta.estado, municipio);

    if (_trk.creditosEnRuta.some(c => c.fecha_eta && _trkCompararFecha(c.fecha_eta, fecha) < 0)) {
        return false;
    }

    _trk.creditosEnRuta.forEach(c => _trkAsegurarEtaMinima(c));

    const payload = {
        id_ruta: _trk.idRutaEditando || 0,
        nombre_ruta: nombre,
        estado,
        municipio,
        fecha_programada: fecha,
        hora_salida: _trkHoraToPayload(),
        tipo_transportista: tipoTransportista || null,
        id_transportista: idTransportista || null,
        id_agencia_tracking: idAgenciaTracking || null,
        id_cedis_destino: idCedisDestino || null,
        modo: 'borrador',
        creditos: _trk.creditosEnRuta.map(c => ({
            id_credito: c.id_credito,
            modelo: [c.moto_marca, c.moto_modelo].filter(Boolean).join(' '),
            bin: c.bin || '',
            estado: _trkEstadoMayus(c.estado, c.municipio),
            municipio: _trkMunicipioMayus(c.municipio, c.estado),
            direccion: c.direccion || '',
            latitud: c.latitud || null,
            longitud: c.longitud || null,
            orden_ruta: c.orden_ruta,
            estatus_confirmacion_gestor: c.estatus_confirmacion_gestor || 'pendiente',
            fecha_eta: c.fecha_eta || null,
            hora_eta_ini: c.hora_eta_ini || null,
            hora_eta_fin: c.hora_eta_fin || null,
        })),
    };

    try {
        const r = await trkFetch('/TrackingRecoleccion/guardarRuta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (!r.success) {
            _trk.haychangios = true;
            return false;
        }
        const idRutaGuardada = _trkExtraerIdRutaRespuesta(r);
        if (idRutaGuardada) _trk.idRutaEditando = parseInt(idRutaGuardada, 10) || idRutaGuardada;
        _trk.estatusRuta = 'borrador';
        _trk.haychangios = false;
        _trk.autosaveDirtyLists = true;
        return true;
    } catch (err) {
        _trk.haychangios = true;
        return false;
    }
}

async function _trkGuardarRuta(modo, opts = {}) {
    const silent = !!opts.silent;
    if (!silent) _trkCancelarAutosaveProgramado();
    if (modo === 'enviar' && _trk.autosaveInFlight) {
        _trkSetAutosaveStatus('Terminando autoguardado...', 'muted');
        while (_trk.autosaveInFlight) {
            await _trkSleep(150);
        }
    }
    const nombre    = _trkSanitizarNombreRuta($('#rutaNombre').val());
    if (nombre && nombre !== $('#rutaNombre').val().trim()) $('#rutaNombre').val(nombre);
    const fecha     = $('#rutaFecha').val();
    const idTransportista   = $('#rutaTransportistaTracking').val();
    const transportistaSel  = _trkTransportistaSeleccionado();
    const tipoTransportista = transportistaSel?.tipo_transportista || '';
    const idAgenciaTracking = transportistaSel?.id_agencia || '';
    const idCedisDestino    = $('#rutaCedisDestino').val();

    const geoRuta = _trkGeoResumenRuta();
    const municipio = _trkMunicipioMayus($('#crdFiltroMunicipio').val() || geoRuta.municipio, $('#crdFiltroEstado').val() || geoRuta.estado);
    const estado    = _trkEstadoMayus($('#crdFiltroEstado').val() || geoRuta.estado, municipio);

    if (!nombre) {
        if (!silent) document.getElementById('rutaNombre').focus();
        return _trkGuardarRutaError('El nombre de la ruta es obligatorio.', opts);
    }
    const nombreDisponible = await _trkValidarNombreRutaDisponible(true);
    if (!nombreDisponible) {
        if (!silent) document.getElementById('rutaNombre').focus();
        return _trkGuardarRutaError('Nombre no permitido, ya existe una ruta con este nombre.', opts);
    }
    if (_trk.creditosEnRuta.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debe agregar al menos un credito a la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!fecha) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La fecha programada es obligatoria.', confirmButtonText: 'Aceptar' });
        return;
    }
    const etaInvalida = _trk.creditosEnRuta.find(c => c.fecha_eta && _trkCompararFecha(c.fecha_eta, fecha) < 0);
    if (etaInvalida) {
        Swal.fire({
            icon: 'warning',
            title: 'Horas estimadas invalidas',
            text: `Las horas estimadas del credito #${etaInvalida.id_credito} no pueden ser anteriores a la fecha de salida de la ruta.`,
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    if (!idTransportista) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Selecciona el transportista de la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    const reglasTransportista = _trkValidarReglasTransportista();
    if (!reglasTransportista.ok) {
        Swal.fire({ icon: 'warning', title: 'Asignacion no permitida', text: reglasTransportista.mensaje, confirmButtonText: 'Aceptar' });
        return;
    }

    if (modo !== 'borrador') {
        const evalInfo = _trkEvaluarTransportistaAsignacion(transportistaSel, { creditos: _trk.creditosEnRuta });
        if (['warning', 'danger'].includes(evalInfo.nivel)) {
            const continuarAsignacion = await Swal.fire({
                icon: evalInfo.nivel === 'danger' ? 'warning' : 'info',
                title: 'Revisar transportista asignado',
                html: `<div class="text-start small">
                    <div class="mb-2">${_trkDriverScoreHtml(evalInfo)}</div>
                    <div><b>Transportista:</b> ${_trkChatEscapeHtml(transportistaSel?.nombre_transportista || 'Sin transportista')}</div>
                    <div><b>Capacidad:</b> ${evalInfo.capacidad > 0 ? `${_trkChatEscapeHtml(evalInfo.disponible)} disponibles / ${_trkChatEscapeHtml(evalInfo.capacidad)} total` : 'Sin configurar'}</div>
                    <div class="mt-2 text-muted">${evalInfo.razones.map(r => `<div>${_trkChatEscapeHtml(r)}</div>`).join('')}</div>
                    <div class="alert alert-warning py-2 mt-2 mb-0">Puedes continuar si operativamente ya fue autorizado.</div>
                </div>`,
                showCancelButton: true,
                confirmButtonText: 'Continuar de todos modos',
                cancelButtonText: 'Cambiar transportista',
                confirmButtonColor: '#0d9488',
            });
            if (!continuarAsignacion.isConfirmed) return;
        }
    }

    if (modo !== 'borrador' && modo !== 'actualizar') {
        if (!tipoTransportista || !idTransportista) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Selecciona transportista antes de enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
        const noConfirmados = _trk.creditosEnRuta.filter(c => c.estatus_confirmacion_gestor !== 'confirmado');
        if (noConfirmados.length > 0) {
            Swal.fire({ icon: 'warning', title: 'Pendiente', text: 'Todos los creditos deben tener confirmacion del gestor para enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
    }

    _trk.creditosEnRuta.forEach(c => _trkAsegurarEtaMinima(c));
    const continuarRuta = await _trkConfirmarRutaNoOptimaSiAplica(modo);
    if (!continuarRuta) return;
    if (modo === 'enviar') {
        const confirmarEnvio = await _trkConfirmarResumenEnvioRuta({ nombre, fecha });
        if (!confirmarEnvio) return;
    }

    const payload = {
        id_ruta:          _trk.idRutaEditando || 0,
        nombre_ruta:      nombre,
        estado,
        municipio,
        fecha_programada: fecha,
        hora_salida:      _trkHoraToPayload(),
        tipo_transportista:  tipoTransportista || null,
        id_transportista:    idTransportista || null,
        id_agencia_tracking: idAgenciaTracking || null,
        id_cedis_destino:    idCedisDestino || null,
        modo,
        creditos: _trk.creditosEnRuta.map(c => ({
            id_credito:                  c.id_credito,
            modelo:                      [c.moto_marca, c.moto_modelo].filter(Boolean).join(' '),
            bin:                         c.bin || '',
            estado:                      _trkEstadoMayus(c.estado, c.municipio),
            municipio:                   _trkMunicipioMayus(c.municipio, c.estado),
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
            const idRutaGuardada = _trkExtraerIdRutaRespuesta(r) || payload.id_ruta || _trk.idRutaEditando;
            if (idRutaGuardada) {
                _trk.idRutaEditando = parseInt(idRutaGuardada, 10) || idRutaGuardada;
            }
            _trk.haychangios = false;
            _trk.autosaveLastHash = _trkAutosaveHash();
            if (modo === 'enviar' && idRutaGuardada) _trkDescargarPdfEvidenciaRuta(idRutaGuardada);
            Swal.fire({ icon: 'success', title: 'Listo!', text: modo === 'borrador' ? 'Borrador guardado correctamente.' : 'Ruta enviada correctamente.', timer: 2000, showConfirmButton: false });
            bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRuta'))?.hide();
            _trk.cargadoOperacion = false;
            _trkCargarCreditosPaso2();
            _trkCargarBorradores();
            _trkCargarRutas();
            _trkCargarOperacionTransportistas(true);
        } else {
            if (r.tipo === 'conflictos_ruta' || Array.isArray(r.errores)) {
                _trkMostrarConflictosRuta(r);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || r.message || 'Error al guardar la ruta.', confirmButtonText: 'Aceptar' });
            }
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'Error de conexion al guardar.', confirmButtonText: 'Aceptar' });
    })
    .finally(() => $btnGuardar.prop('disabled', false));
}

// --- Ver detalle de ruta ---------------------------------
function _trkVerDetalleRuta(idRuta) {
    const $body = $('#detalleRutaBody');
    _trk.detalleRutaActualId = idRuta;
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
            const infoTransportista = _trkTransportistaRutaData(d);
            const transportista = infoTransportista.nombre
                ? `${infoTransportista.nombre} (${infoTransportista.tipo || 'No disponible'})`
                : 'Sin transportista';
            const agenciaTracking = infoTransportista.agencia || infoTransportista.direccion || 'Sin CEDIS';
            const cedisDestinoLocal = _trkNormalizarCedisDestino(d.cedis_destino || (d.id_cedis_destino || d.cedis_destino_nombre ? {
                id_agencia: d.id_cedis_destino,
                nombre_agencia: d.cedis_destino_nombre,
                direccion: d.cedis_destino_direccion,
                estado: d.cedis_destino_estado,
                municipio: d.cedis_destino_municipio,
                codigo_postal: d.cedis_destino_codigo_postal,
                telefono: d.cedis_destino_telefono,
                encargado: d.cedis_destino_encargado,
                email: d.cedis_destino_email,
                horario: d.cedis_destino_horario,
                link_ubicacion: d.cedis_destino_link_ubicacion,
            } : null));
            if (cedisDestinoLocal && !_trk.cedisDestinoPorRuta[idRuta]) {
                _trk.cedisDestinoPorRuta[idRuta] = cedisDestinoLocal;
            }
            const fechaCancelacion = d.fecha_cancelacion_fmt || _trkFormatFechaHora(d.fecha_cancelacion) || 'No disponible';
            const motivoCancelacion = d.estatus_ruta === 'cancelada'
                ? `<div class="col-12"><div class="alert alert-danger py-2 mb-0">
                    <div class="fw-semibold mb-1"><i class="fa-solid fa-ban me-1"></i>Ruta cancelada</div>
                    <div><b>Fecha y hora:</b> ${_trkChatEscapeHtml(fechaCancelacion)}</div>
                    <div><b>Motivo:</b> ${_trkChatEscapeHtml(d.motivo_cancelacion || 'No disponible')}</div>
                </div></div>`
                : '';
            let rowsHtml = '';
            (d.detalle || []).forEach((det, i) => {
                rowsHtml += `<tr data-id-detalle="${_trkChatEscapeHtml(det.id_detalle || '')}">
                    <td class="text-center">${det.orden_ruta || (i + 1)}</td>
                    <td>${det.id_credito || ' - '} ${_trkNuevoCreditoRutaHtml(det)}</td>
                    <td>${det.nombre_cliente || ' - '}</td>
                    <td>${det.modelo || ' - '}</td>
                    <td>${det.bin || ' - '}</td>
                    <td>${_trkRenderLocationBadges(det.estado, det.municipio)}</td>
                    <td>${det.estatus_proceso || ' - '}</td>
                    <td class="trk-det-recoleccion" data-id="${_trkChatEscapeHtml(det.id_detalle || '')}">
                        ${_trkRenderEstatusRecoleccionDetalle(det.estatus_recoleccion)}
                    </td>
                    <td>${CONF_LABEL[det.estatus_confirmacion_gestor] || det.estatus_confirmacion_gestor || ' - '}</td>
                    <td class="trk-det-otp" data-id="${_trkChatEscapeHtml(det.id_detalle || '')}">
                        ${_trkOtpCellHtml(det)}
                    </td>
                </tr>`;
            });
            $body.html(`
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">Nombre</b>${d.nombre_ruta}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Estado</b>${_trkRenderLocationBadges(d.estado, null)}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Municipio</b>${_trkRenderLocationBadges(null, d.municipio)}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Fecha programada</b>${d.fecha_programada_fmt || d.fecha_programada || ' - '}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Hora de salida</b>${
                        d.act_hora_1
                            ? `<span class="badge bg-warning text-dark me-1" title="Hora actualizada">${_trkFormatHora(d.act_hora_1)}</span><s class="text-muted small">${_trkFormatHora(d.hora_inicial)}</s>`
                            : (d.hora_inicial ? `<span class="badge bg-light text-dark border">${_trkFormatHora(d.hora_inicial)}</span>` : ' - ')
                    }</div>
                    <div class="col-6 col-md-1"><b class="d-block small text-muted">Estatus</b>${estatusBadge}</div>
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">Transportista</b><span class="small">${_trkChatEscapeHtml(transportista)}</span></div>
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">CEDIS / empresa</b><span class="small">${_trkChatEscapeHtml(agenciaTracking)}</span></div>
                    <div class="col-12">
                        <b class="d-block small text-muted mb-1">CEDIS destino</b>
                        <div id="trkDetalleCedisDestinoWrap">${_trkCedisDestinoHtml(cedisDestinoLocal, { idRuta, estatus: d.estatus_ruta })}</div>
                    </div>
                    <div class="col-12">
                        <div class="small fw-semibold text-muted mb-1"><i class="fa-solid fa-clock-rotate-left me-1"></i>Historial de cambios de CEDIS</div>
                        <div id="trkDetalleCedisHistorial">${_trkRenderHistorialCedisDestino([])}</div>
                    </div>
                    ${motivoCancelacion}
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size:.8rem;">
                        <thead style="background:var(--track-color);color:#fff;">
                            <tr>
                                <th>#</th><th>Credito</th><th>Cliente</th><th>Modelo</th>
                                <th>VIN</th><th>Estado / Municipio</th>
                                <th>Proceso</th><th>Recoleccion</th><th>Confirmacion</th><th>OTP</th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml || '<tr><td colspan="10" class="text-center text-muted">Sin creditos</td></tr>'}</tbody>
                    </table>
                </div>
            `);
            _trkCargarDetalleCedisDestino(idRuta, d.estatus_ruta);
            _trkConsultarOtpsActivosDetalle(d.detalle || []);
        })
        .catch(() => $body.html('<div class="alert alert-danger">Error de conexion.</div>'));
}

// --- Marcar cambios pendientes ------------------------
function _trkMarcarCambio() {
    if (_trk.cargando) return;
    if (_trkRutaEstaCancelada()) return;
    _trk.haychangios = true;
    _trkProgramarAutosaveBorrador();
    if (_trk.soloLectura) {
        $('#btnActualizarRuta').prop('disabled', false)
            .removeClass('btn-label-secondary')
            .addClass('btn-primary')
            .css({ cursor: 'pointer' });
    }
}

// --- Bloquear / Desbloquear modal -----------------------
function _trkMostrarCancelacionRuta(d) {
    const fecha = d.fecha_cancelacion_fmt || _trkFormatFechaHora(d.fecha_cancelacion) || 'No disponible';
    const motivo = d.motivo_cancelacion || 'No disponible';
    $('#rutaCancelacionInfo')
        .removeClass('d-none')
        .html(`<div class="fw-semibold mb-1"><i class="fa-solid fa-ban me-1"></i>Ruta cancelada</div>
               <div class="small"><b>Fecha y hora:</b> ${_trkChatEscapeHtml(fecha)}</div>
               <div class="small"><b>Motivo:</b> ${_trkChatEscapeHtml(motivo)}</div>`);
}

function _trkBloquearModalCancelada(d) {
    _trk.rutaCancelada = true;
    _trk.soloLectura = true;
    _trkMostrarCancelacionRuta(d || {});
    $('#rutaNombre, #rutaFecha, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm, #rutaTipoTransportista, #rutaAgenciaTracking, #rutaTransportistaTracking, #rutaTransportistaSearch, #rutaCedisDestino, #crdFiltroEstado, #crdFiltroMunicipio, #rutaCreditoSelect, #btnAgregarCredito')
        .prop('disabled', true);
    $('#secAgregarCredito, #reorderHint, #btnActualizarRuta, #btnGuardarBorrador, #btnEnviarRuta, #btnRefreshMap').hide();
    if (_trk.sortableInstance) _trk.sortableInstance.option('disabled', true);
    const $label = $('#modalRegistrarRutaLabel');
    if (!$label.find('.badge-ruta-cancelada-modal').length) {
        $label.append('<span class="badge bg-danger badge-ruta-cancelada-modal ms-2" style="font-size:.63rem;vertical-align:middle;">Cancelada</span>');
    }
}

function _trkBloquearModal() {
    _trk.soloLectura = true;
    // Solo bloquear campos de cabecera de la ruta
    $('#rutaNombre, #rutaFecha, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm, #rutaTipoTransportista, #rutaAgenciaTracking, #rutaTransportistaTracking, #rutaTransportistaSearch, #rutaCedisDestino').prop('disabled', true);
    // Ocultar seccion de agregar nuevos creditos (no anadir, solo editar existentes)
    $('#secAgregarCredito').hide();
    $('#reorderHint').hide();
    // Swap de botones: ocultar borrador/enviar, mostrar actualizar (gris hasta que haya cambios)
    $('#btnGuardarBorrador, #btnEnviarRuta').hide();
    $('#btnActualizarRuta').show().prop('disabled', true)
        .removeClass('btn-primary')
        .addClass('btn-label-secondary')
        .css({ cursor: 'not-allowed' });
    // Badge en titulo
    const $label = $('#modalRegistrarRutaLabel');
    if (!$label.find('.badge-solo-lectura').length) {
        $label.append('<span class="badge bg-secondary badge-solo-lectura ms-2" style="font-size:.63rem;vertical-align:middle;">Ver ruta</span>');
    }
}

function _trkDesbloquearModal() {
    _trk.soloLectura = false;
    _trk.rutaCancelada = false;
    $('#rutaNombre, #rutaFecha, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm, #rutaTipoTransportista, #rutaAgenciaTracking, #rutaTransportistaTracking, #rutaTransportistaSearch, #rutaCedisDestino, #crdFiltroEstado').prop('disabled', false);
    _trkRefrescarSelectTransportistas($('#rutaTransportistaTracking').val());
    $('#btnGuardarBorrador, #btnEnviarRuta').show();
    $('#btnActualizarRuta').hide();
    $('#secAgregarCredito').show();
    $('#reorderHint').show();
    $('#btnRefreshMap').show().prop('disabled', false);
    $('#rutaCancelacionInfo').addClass('d-none').empty();
    if (_trk.sortableInstance) _trk.sortableInstance.option('disabled', false);
    $('#modalRegistrarRutaLabel .badge-solo-lectura, #modalRegistrarRutaLabel .badge-ruta-cancelada-modal').remove();
}

// --- Abrir ruta existente en el modal -------------------
function _trkCargarRutaEnModal(idRuta, soloLectura) {
    _trkResetModal();
    _trk.idRutaEditando = idRuta;
    _trk.cargando       = true;

    // Actualizar titulo mientras carga
    const icon = soloLectura ? 'eye' : 'pen-to-square';
    document.getElementById('modalRegistrarRutaLabel').innerHTML =
        `<i class="fa-solid fa-${icon} me-2"></i>${soloLectura ? 'Ver ruta' : 'Editar ruta'}`;

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRegistrarRuta'));
    modal.show();

    Swal.fire({
        title: 'Cargando ruta...',
        html: '<span style="font-size:.875rem;color:#64748b;">Consultando datos, creditos y mapa de la ruta</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    Promise.all([
        _trkPrepararModalRutaDetalle(soloLectura),
        trkFetch(`/TrackingRecoleccion/obtenerDetalleRuta?id_ruta=${idRuta}`),
    ])
        .then(([, r]) => {
            if (!r.success || !r.datos) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la ruta.', confirmButtonText: 'Aceptar' });
                modal.hide();
                return;
            }
            const d = r.datos;

            // Titulo final con nombre de la ruta
            const nombreRutaLimpio = _trkSanitizarNombreRuta(d.nombre_ruta || '');
            document.getElementById('modalRegistrarRutaLabel').innerHTML =
                `<i class="fa-solid fa-${icon} me-2"></i>${soloLectura ? 'Ver ruta' : 'Editar ruta'}: <em>${nombreRutaLimpio}</em>`;

            // Campos basicos
            $('#rutaNombre').val(nombreRutaLimpio);
            $('#rutaFecha').val(d.fecha_programada || '');
            $('#rutaTipoTransportista').val(d.tipo_transportista || '');
            _trkPoblarAgenciasTrackingSelect();
            $('#rutaAgenciaTracking').val(d.id_agencia_tracking ? String(d.id_agencia_tracking) : '');
            const idCedisDestinoRuta = d.id_cedis_destino || d.cedis_destino?.id_agencia || '';
            $('#rutaCedisDestino').val(idCedisDestinoRuta ? String(idCedisDestinoRuta) : '');
            _trkRefrescarSelectTransportistas(d.id_transportista || null);
            $('#rutaCedisDestino').val(idCedisDestinoRuta ? String(idCedisDestinoRuta) : '');
            _trkRenderCedisDestinoInfo();
            _trkConsultarCedisDestinoRuta(idRuta).then(({ cedis }) => {
                if (cedis) {
                    _trkActualizarCedisDestinoEnMemoria(idRuta, cedis);
                } else {
                    _trk.cedisDestinoPorRuta[idRuta] = null;
                    $('#rutaCedisDestino').val('');
                    _trkRenderCedisDestinoInfo();
                }
            }).catch(() => {});

            // Creditos (cargar directamente en array)
            _trk.creditosEnRuta = (d.detalle || []).map((det, i) => ({
                id_detalle:                  det.id_detalle || null,
                id_credito:                  det.id_credito,
                nombre_cliente:              det.nombre_cliente || '',
                moto_marca:                  '',
                moto_modelo:                 det.modelo || '',
                bin:                         det.bin || '',
                estado:                      _trkEstadoMayus(det.estado, det.municipio),
                estado_raw:                  det.estado || '',
                municipio:                   _trkMunicipioMayus(det.municipio, det.estado),
                direccion:                   det.direccion || '',
                latitud:                     det.latitud  || null,
                longitud:                    det.longitud || null,
                orden_ruta:                  det.orden_ruta || (i + 1),
                estatus_confirmacion_gestor: det.estatus_confirmacion_gestor || 'pendiente',
                fecha_eta:                   det.fecha_eta    || null,
                hora_eta_ini:                det.hora_eta_ini || null,
                hora_eta_fin:                det.hora_eta_fin || null,
                fecha_agregado:              det.fecha_agregado || null,
                fecha_agregado_fmt:          det.fecha_agregado_fmt || '',
                eta_manual:                  !!(det.fecha_eta || det.hora_eta_ini || det.hora_eta_fin),
                eta_auto:                    false,
            }));
            _trk.estatusRuta  = d.estatus_ruta || null;
            _trk.rutaCancelada = String(d.estatus_ruta || '') === 'cancelada';
            _trkRenderListaCreditos();
            _trkRefrescarSelectCreditos();
            _trkRenderizarMapa();

            // Estado + Municipio via filtros de creditos
            $('#crdFiltroEstado').val(_trkEstadoMayus(d.estado, d.municipio)).trigger('change');
            const municipioRutaFiltro = _trkMunicipioMayus(d.municipio, d.estado);
            if (municipioRutaFiltro) {
                $('#crdFiltroMunicipio').val(municipioRutaFiltro).trigger('change.select2');
            }
            _trk.cargando     = false;
            _trk.haychangios  = false;

            // Aplicar bloqueo si es solo lectura o si la ruta ya fue cancelada.
            if (_trkRutaEstaCancelada()) _trkBloquearModalCancelada(d);
            else if (soloLectura) _trkBloquearModal();

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

            // Iniciar tracking en tiempo real si la ruta esta activa o completada
            _trk.autosaveLastHash = _trkAutosaveHash();
            const esActiva = _trkRutaDebeConsultarEstadoLive(d.estatus_ruta) || ['completado', 'concluida'].includes(String(d.estatus_ruta || ''));
            if (esActiva) {
                _trkRTIniciar(idRuta);
            } else {
                _trkRTLimpiar();
            }
            Swal.close();
        })
        .catch(() => {
            _trk.cargando = false;
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexion.', confirmButtonText: 'Aceptar' });
            modal.hide();
        });
}

function _trkCreditosRutaFiltrados() {
    const estado = _trkNormTxt($('#trkListaFiltroEstado').val());
    const municipio = _trkNormTxt($('#trkListaFiltroMunicipio').val());
    const q = _trkNormTxt($('#trkListaBuscar').val());
    return (_trk.creditosEnRuta || []).filter(c => {
        if (estado && _trkNormTxt(_trkEstadoMayus(c.estado, c.municipio)) !== estado) return false;
        if (municipio && _trkNormTxt(_trkMunicipioMayus(c.municipio, c.estado)) !== municipio) return false;
        if (!q) return true;
        return _trkNormTxt([
            c.id_credito,
            c.nombre_cliente,
            c.moto_marca,
            c.moto_modelo,
            c.bin,
            c.estado,
            c.municipio,
            c.direccion,
            c.estatus_confirmacion_gestor,
        ].filter(Boolean).join(' ')).includes(q);
    });
}

function _trkPoblarFiltrosListaRuta() {
    const $estado = $('#trkListaFiltroEstado');
    const actual = $estado.val();
    const estados = [...new Set((_trk.creditosEnRuta || []).map(c => _trkEstadoMayus(c.estado, c.municipio)).filter(Boolean))]
        .sort((a, b) => a.localeCompare(b));
    const html = '<option value="">Todos los estados</option>' + estados
        .map(e => `<option value="${_trkChatEscapeHtml(e)}">${_trkChatEscapeHtml(e)}</option>`)
        .join('');
    if ($estado.data('lastHtml') !== html) {
        $estado.html(html).data('lastHtml', html);
        if (actual && estados.some(e => String(e) === String(actual))) $estado.val(actual);
    }
    _trkPoblarFiltroMunicipiosListaRuta(false);
}

function _trkPoblarFiltroMunicipiosListaRuta(render = true) {
    const estado = _trkNormTxt($('#trkListaFiltroEstado').val());
    const $municipio = $('#trkListaFiltroMunicipio');
    const actual = $municipio.val();
    const municipios = [...new Set((_trk.creditosEnRuta || [])
        .filter(c => !estado || _trkNormTxt(_trkEstadoMayus(c.estado, c.municipio)) === estado)
        .map(c => _trkMunicipioMayus(c.municipio, c.estado))
        .filter(Boolean))]
        .sort((a, b) => a.localeCompare(b));
    const html = '<option value="">Todos los municipios</option>' + municipios
        .map(m => `<option value="${_trkChatEscapeHtml(m)}">${_trkChatEscapeHtml(m)}</option>`)
        .join('');
    if ($municipio.data('lastHtml') !== html) {
        $municipio.html(html).data('lastHtml', html);
    }
    $municipio.prop('disabled', municipios.length === 0);
    if (actual && municipios.some(m => String(m) === String(actual))) {
        $municipio.val(actual);
    } else if (actual) {
        $municipio.val('');
    }
    if (render) _trkRenderListaCreditos();
}

function _trkTogglePlaneador() {
    const modal = document.getElementById('modalRegistrarRuta');
    const active = !modal.classList.contains('trk-planner-active');
    modal.classList.toggle('trk-planner-active', active);
    $('#trkPlannerPanel').toggleClass('d-none', !active);
    $('#btnTogglePlanner').toggleClass('btn-primary', active).toggleClass('btn-outline-primary', !active)
        .html(active
            ? '<i class="fa-solid fa-down-left-and-up-right-to-center me-1"></i>Vista normal'
            : '<i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i>Planeador');
    _trkRenderPlaneadorPanel();
    setTimeout(() => {
        if (_trk.mapInstance && window.google?.maps) {
            google.maps.event.trigger(_trk.mapInstance, 'resize');
            _trkRenderizarMapa();
        }
    }, 180);
}

function _trkRenderPlaneadorPanel() {
    const $summary = $('#trkPlannerSummary');
    const $groups = $('#trkPlannerGroups');
    if (!$summary.length || !$groups.length) return;
    const creditos = _trk.creditosEnRuta || [];
    const estados = new Set(creditos.map(c => _trkNormTxt(_trkEstadoMayus(c.estado, c.municipio) || 'SIN ESTADO')));
    const municipios = new Set(creditos.map(c => {
        const estado = _trkEstadoMayus(c.estado, c.municipio) || 'SIN ESTADO';
        const municipio = _trkMunicipioMayus(c.municipio, c.estado) || 'SIN MUNICIPIO';
        return `${_trkNormTxt(estado)}|${_trkNormTxt(municipio)}`;
    }));
    const confirmados = creditos.filter(c => c.estatus_confirmacion_gestor === 'confirmado').length;
    const pendientes = creditos.filter(c => ['pendiente', 'en_revision'].includes(c.estatus_confirmacion_gestor || 'pendiente')).length;
    const rechazados = creditos.filter(c => c.estatus_confirmacion_gestor === 'rechazado').length;
    $summary.html(`
        <div class="trk-planner-kpi"><span>Creditos</span><b>${creditos.length}</b></div>
        <div class="trk-planner-kpi"><span>Estados</span><b>${estados.size}</b></div>
        <div class="trk-planner-kpi"><span>Municipios</span><b>${municipios.size}</b></div>
    `);
    if (!creditos.length) {
        $groups.html('<div class="text-center text-muted small py-3">Agrega creditos para ver la planeacion por volumen.</div>');
        return;
    }
    const tree = {};
    creditos.forEach(c => {
        const estado = _trkEstadoMayus(c.estado, c.municipio) || 'SIN ESTADO';
        const municipio = _trkMunicipioMayus(c.municipio, c.estado) || 'SIN MUNICIPIO';
        tree[estado] ??= { total: 0, confirmados: 0, pendientes: 0, rechazados: 0, municipios: {} };
        tree[estado].municipios[municipio] ??= { total: 0, confirmados: 0, pendientes: 0, rechazados: 0 };
        [tree[estado], tree[estado].municipios[municipio]].forEach(bucket => {
            bucket.total++;
            if (c.estatus_confirmacion_gestor === 'confirmado') bucket.confirmados++;
            else if (c.estatus_confirmacion_gestor === 'rechazado') bucket.rechazados++;
            else bucket.pendientes++;
        });
    });
    const estadoHtml = Object.entries(tree)
        .sort((a, b) => b[1].total - a[1].total || a[0].localeCompare(b[0]))
        .map(([estado, data]) => {
            const municipiosHtml = Object.entries(data.municipios)
                .sort((a, b) => b[1].total - a[1].total || a[0].localeCompare(b[0]))
                .map(([municipio, m]) => `
                    <button type="button" class="trk-planner-mun" data-estado="${_trkChatEscapeHtml(estado)}" data-municipio="${_trkChatEscapeHtml(municipio)}">
                        <span>${_trkChatEscapeHtml(municipio)}</span>
                        ${_trkPlannerCounts(m)}
                    </button>
                `).join('');
            return `<div class="trk-planner-group">
                <button type="button" class="trk-planner-group-head" data-estado="${_trkChatEscapeHtml(estado)}">
                    <span>${_trkChatEscapeHtml(estado)}</span>
                    ${_trkPlannerCounts(data)}
                </button>
                ${municipiosHtml}
            </div>`;
        }).join('');
    const alert = (creditos.length >= 25 || estados.size >= 3)
        ? `<div class="alert alert-warning py-2 px-2 small mb-2">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Ruta pesada: ${creditos.length} creditos en ${estados.size} estado(s). Revisa agrupacion antes de enviar.
        </div>`
        : '';
    $groups.html(alert + estadoHtml);
}

function _trkPlannerCounts(data) {
    return `<span class="trk-planner-counts">
        <span class="badge bg-secondary">${data.total}</span>
        ${data.confirmados ? `<span class="badge bg-success">${data.confirmados}</span>` : ''}
        ${data.pendientes ? `<span class="badge bg-warning text-dark">${data.pendientes}</span>` : ''}
        ${data.rechazados ? `<span class="badge bg-danger">${data.rechazados}</span>` : ''}
    </span>`;
}

function _trkPlannerEnfocarGrupo(estado, municipio = '') {
    const estadoNorm = _trkNormTxt(estado);
    const municipioNorm = _trkNormTxt(municipio);
    const matches = (_trk.creditosEnRuta || []).filter(c => {
        const okEstado = _trkNormTxt(_trkEstadoMayus(c.estado, c.municipio) || 'SIN ESTADO') === estadoNorm;
        const okMunicipio = !municipioNorm || _trkNormTxt(_trkMunicipioMayus(c.municipio, c.estado) || 'SIN MUNICIPIO') === municipioNorm;
        return okEstado && okMunicipio;
    });
    $('#rutaCreditosList .track-credito-row').removeClass('trk-row-focused');
    matches.forEach(c => {
        $(`#rutaCreditosList .track-credito-row[data-id="${c.id_credito}"]`).addClass('trk-row-focused');
    });
    if (!_trk.mapInstance || !window.google?.maps) {
        _trkRenderizarMapa();
        return;
    }
    const bounds = new google.maps.LatLngBounds();
    matches.forEach(c => {
        const pos = _trk.creditoPosiciones[String(c.id_credito)] || _trkCreditoPosicionBasica(c);
        if (pos) bounds.extend(pos);
    });
    if (!bounds.isEmpty()) {
        _trk.mapInstance.fitBounds(bounds);
    } else if (matches[0]) {
        _trkEnfocarCreditoEnMapa(matches[0].id_credito, { scroll: false });
    }
}

function _trkAsegurarGoogleMaps(callback) {
    if (typeof callback !== 'function') return;
    if (typeof google !== 'undefined' && google.maps) {
        callback();
        return;
    }
    if (!window._trackGoogleMapsKey) return;
    window._trkMapsReadyCallbacks = window._trkMapsReadyCallbacks || [];
    window._trkMapsReadyCallbacks.push(callback);
    window._trkMapCallback = () => {
        const callbacks = window._trkMapsReadyCallbacks || [];
        window._trkMapsReadyCallbacks = [];
        callbacks.forEach(fn => {
            try { fn(); } catch {}
        });
    };
    if (!_trk.mapLoaded) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry,places&callback=_trkMapCallback`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        _trk.mapLoaded = true;
        return;
    }
    const waitForMaps = setInterval(() => {
        if (typeof google !== 'undefined' && google.maps) {
            clearInterval(waitForMaps);
            window._trkMapCallback();
        }
    }, 150);
    setTimeout(() => clearInterval(waitForMaps), 10000);
}

function _trkGeoResumenRuta() {
    const estados = [...new Set((_trk.creditosEnRuta || [])
        .map(c => _trkEstadoMayus(c.estado, c.municipio))
        .filter(Boolean))];
    const municipios = [...new Set((_trk.creditosEnRuta || [])
        .map(c => _trkMunicipioMayus(c.municipio, c.estado))
        .filter(Boolean))];
    return {
        estado: estados.length > 1 ? 'MULTIPLES ESTADOS' : (estados[0] || ''),
        municipio: municipios.length > 1 ? 'MULTIPLES MUNICIPIOS' : (municipios[0] || ''),
    };
}

/* ============================================================
   TRACKING EN TIEMPO REAL  -  estilo Mercado Libre
   Muestra el estado del recorrido (paradas, progreso, ubicacion)
   cuando la ruta esta en_proceso o completado.
============================================================ */

const _trkRT = {
    idRuta:       null,
    ws:           null,
    wsRetries:    0,
    wsRetryTO:    null,
    wsPingIv:     null,
    liveCfg:      null,
    liveCfgExpiry:0,
    ubicacion:    null,
    historial:    [],
    estado:       null,   // ultimo estado recibido del API
};

// --- Limpiar todo el tracking RT -------------------------
function _trkRTLimpiar() {
    if (_trkRT.wsPingIv)  { clearInterval(_trkRT.wsPingIv);  _trkRT.wsPingIv  = null; }
    if (_trkRT.wsRetryTO) { clearTimeout(_trkRT.wsRetryTO);  _trkRT.wsRetryTO = null; }
    if (_trkRT.ws)        { _trkRT.ws.onclose = null; _trkRT.ws.close(); _trkRT.ws = null; }
    _trkRT.idRuta    = null;
    _trkRT.wsRetries = 0;
    _trkRT.estado    = null;
    _trkRT.ubicacion = null;
    _trkRT.historial = [];
    _trkRTLimpiarMapaLive();
    document.getElementById('trkTrackingSection').classList.add('d-none');
    document.getElementById('trkTimeline').innerHTML =
        `<div class="text-center text-muted py-2 small" id="trkTimelineEmpty">
            <span class="spinner-border spinner-border-sm opacity-25" style="color:var(--track-color);"></span>
         </div>`;
    _trkRTActualizarWsDot(false);
}

// --- Inicializar para una ruta ----------------------------
async function _trkRTIniciar(idRuta) {
    _trkRTLimpiar();
    _trkRT.idRuta = idRuta;
    document.getElementById('trkTrackingSection').classList.remove('d-none');
    await _trkRTCargarEstado();
    await _trkRTCargarMapaLive();
    const cfg = await _trkRTObtenerLiveConfig();
    if (cfg) _trkRTConectarWS(cfg);
}

// --- Cargar estado via REST -------------------------------
async function _trkRTCargarEstado() {
    const id = _trkRT.idRuta;
    if (!id) return;
    if (!_trk.trackingApiDisponible && Date.now() < _trk.trackingApiRetryAt) {
        document.getElementById('trkTimeline').innerHTML =
            `<div class="text-center text-muted py-2 small">Tracking no disponible temporalmente.</div>`;
        return;
    }
    try {
        const r = await trkFetch(`/TrackingRecoleccion/trackingEstadoRuta?id_ruta=${id}`);
        if (r.success && r.ruta) {
            _trk.trackingApiDisponible = true;
            _trk.trackingApiRetryAt = 0;
            _trkRT.estado = r.ruta;
            _trkRTRenderizar(r.ruta);
        } else {
            if (r.servicio_no_disponible || [0, 500, 502, 503, 504].includes(parseInt(r.codigo_http, 10))) {
                _trk.trackingApiDisponible = false;
                _trk.trackingApiRetryAt = Date.now() + 60000;
            }
            document.getElementById('trkTimeline').innerHTML =
                `<div class="text-center text-muted py-2 small">${r.mensaje || 'Sin datos de tracking disponibles.'}</div>`;
        }
    } catch {
        _trk.trackingApiDisponible = false;
        _trk.trackingApiRetryAt = Date.now() + 60000;
        document.getElementById('trkTimeline').innerHTML =
            `<div class="text-center text-muted py-2 small">Tracking no disponible temporalmente.</div>`;
    }
}

// --- Renderizar timeline ----------------------------------
function _trkRTRenderizar(ruta) {
    // Barra de progreso
    const prog = ruta.progreso || {};
    const pct  = prog.porcentaje ?? 0;
    document.getElementById('trkProgressBar').style.width = pct + '%';
    document.getElementById('trkProgressText').textContent =
        `${prog.completados ?? 0} / ${prog.total ?? 0} puntos completados`;
    document.getElementById('trkPorcentaje').textContent = pct + '%';

    // Timeline de creditos
    const creditos = ruta.creditos || [];
    const puntoAct = ruta.punto_actual;
    if (!creditos.length) {
        document.getElementById('trkTimeline').innerHTML =
            `<div class="text-center text-muted py-2 small">Sin puntos de recoleccion registrados.</div>`;
        return;
    }

    const LABELS = {
        pendiente:   'Pendiente',
        en_camino:   'En camino',
        en_sitio:    'En sitio',
        recolectada: 'Recolectada',
        completado:  'Recolectada', // alias por compatibilidad
        incidencia:  'Incidencia',
    };
    const ICONS = {
        pendiente:   'fa-circle-dot',
        en_camino:   'fa-motorcycle',
        en_sitio:    'fa-location-dot',
        recolectada: 'fa-circle-check',
        completado:  'fa-circle-check', // alias
        incidencia:  'fa-triangle-exclamation',
    };

    let html = '';
    creditos.forEach(c => {
        const localEta = _trk.creditosEnRuta.find(x =>
            (x.id_detalle && c.id_detalle && Number(x.id_detalle) === Number(c.id_detalle)) ||
            (x.id_credito && c.id_credito && Number(x.id_credito) === Number(c.id_credito))
        ) || {};
        const cEta = { ...c, ...localEta };
        const est     = c.estatus_recoleccion || 'pendiente';
        const esAct   = puntoAct && puntoAct.id_detalle === c.id_detalle;
        const esDone  = est === 'recolectada' || est === 'completado';
        const stepCls = esDone ? 'done' : (esAct ? 'activo' : (est === 'en_sitio' ? 'en_sitio' : (est === 'incidencia' ? 'incidencia' : '')));
        const icon    = ICONS[est] || ICONS.pendiente;
        const label   = LABELS[est] || est;
        const etaInfo = _trkEstadoEta(cEta, est);
        const etaTxt  = (cEta.fecha_eta && cEta.hora_eta_ini && cEta.hora_eta_fin)
            ? `Inicio: ${_trkFormatHora(cEta.hora_eta_ini)} / Llegada: ${_trkFormatHora(cEta.hora_eta_fin)}`
            : '';

        // Linea 1: Credito #XXXXX  -  MARCA MODELO | Estado, Municipio
        const moto    = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const lugar   = [c.estado, c.municipio].filter(Boolean).join(', ');
        let linea1    = `Credito #${c.id_credito}`;
        if (moto)  linea1 += `  -  ${moto}`;
        if (lugar) linea1 += ` | ${lugar}`;

        // Linea 2: nombre del cliente
        const cliente = _trkChatEscapeHtml(c.nombre_cliente || '');

        html += `<div class="trk-step ${stepCls}" data-id="${c.id_detalle}">
            <div class="trk-step-dot"><i class="fa-solid ${icon}" style="font-size:.45rem;"></i></div>
            <div class="trk-step-body">
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="trk-step-nombre" style="font-weight:600;">${_trkChatEscapeHtml(linea1)}</span>
                    <span class="trk-step-badge trk-badge-${est}">${label}</span>
                    ${etaInfo.html}
                </div>
                ${cliente ? `<span class="trk-step-dir">${cliente}</span>` : ''}
                ${etaTxt ? `<span class="trk-step-dir">Horas estimadas ${_trkChatEscapeHtml(etaTxt)}  -  ${_trkChatEscapeHtml(etaInfo.label)}</span>` : ''}
            </div>
        </div>`;
    });
    document.getElementById('trkTimeline').innerHTML = html;
}

// --- Aplicar cambios parciales (WS update) ---------------
function _trkRTAplicarChanges(changes) {
    if (!changes || !changes.length || !_trkRT.estado) return;
    const creditos = _trkRT.estado.creditos || [];
    changes.forEach(ch => {
        const c = creditos.find(x => x.id_detalle === ch.id_detalle);
        if (c && ch.estatus_recoleccion) c.estatus_recoleccion = ch.estatus_recoleccion;
        const local = _trk.creditosEnRuta.find(x => x.id_detalle && Number(x.id_detalle) === Number(ch.id_detalle));
        if (local && ch.estatus_recoleccion) local.estatus_recoleccion = ch.estatus_recoleccion;
        if (ch.id_detalle && ch.estatus_recoleccion) {
            _trkDetalleActualizarRecoleccion(ch.id_detalle, ch.estatus_recoleccion);
            _trkChatActualizarContextoDetalle(ch.id_detalle, { estatus_recoleccion: ch.estatus_recoleccion });
        }
    });
    // Recalcular progreso
    const total       = creditos.length;
    const esTerminado = e => e === 'recolectada' || e === 'completado';
    const completados = creditos.filter(c => esTerminado(c.estatus_recoleccion)).length;
    if (_trkRT.estado.progreso) {
        _trkRT.estado.progreso.completados  = completados;
        _trkRT.estado.progreso.pendientes   = total - completados;
        _trkRT.estado.progreso.porcentaje   = total ? Math.round((completados / total) * 100) : 0;
    }
    // Punto actual: primer no recolectado
    const noComp = creditos.find(c => !esTerminado(c.estatus_recoleccion));
    _trkRT.estado.punto_actual = noComp ? { id_detalle: noComp.id_detalle } : null;
    _trkRTRenderizar(_trkRT.estado);
    _trkRenderListaCreditos();
}

// --- Actualizar ultima ubicacion del conductor ------------
function _trkRTActualizarUbicacion(evt) {
    const pill = document.getElementById('trkUltimaUbicacion');
    const txt  = document.getElementById('trkUbicacionText');
    const time = document.getElementById('trkUbicacionTime');
    if (!pill) return;
    const lat = parseFloat(evt.lat ?? evt.latitud ?? 0).toFixed(5);
    const lng = parseFloat(evt.lng ?? evt.longitud ?? 0).toFixed(5);
    txt.textContent  = `${lat}, ${lng}`;
    const rawTs = evt.updated_at || evt.created_at || evt.timestamp || null;
    const ts = rawTs ? new Date(String(rawTs).endsWith('Z') ? rawTs : rawTs + 'Z') : new Date();
    time.textContent = ts.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    pill.classList.remove('d-none');
}

// --- WS dot ----------------------------------------------
function _trkRTNormalizarUbicacion(raw) {
    if (!raw) return null;
    const lat = parseFloat(raw.lat ?? raw.latitud);
    const lng = parseFloat(raw.lng ?? raw.longitud);
    if (isNaN(lat) || isNaN(lng)) return null;
    return {
        id_ruta: raw.id_ruta ?? _trkRT.idRuta,
        id_transportista: raw.id_transportista ?? null,
        lat,
        lng,
        heading: raw.heading !== undefined && raw.heading !== null ? parseFloat(raw.heading) : null,
        speed: raw.speed !== undefined && raw.speed !== null ? parseFloat(raw.speed) : null,
        accuracy: raw.accuracy !== undefined && raw.accuracy !== null ? parseFloat(raw.accuracy) : null,
        battery: raw.battery !== undefined && raw.battery !== null ? parseInt(raw.battery, 10) : null,
        updated_at: raw.updated_at || raw.created_at || raw.timestamp || null,
    };
}

async function _trkRTObtenerLiveConfig(force = false) {
    if (!force && _trkRT.liveCfg && _trkRT.liveCfgExpiry > Date.now() + 300000) return _trkRT.liveCfg;
    try {
        const r = await trkFetch('/TrackingRecoleccion/trackingLiveConfig');
        if (!r.success || !r.ws_base || !r.token || !r.api_key) return null;
        _trkRT.liveCfg = r;
        _trkRT.liveCfgExpiry = r.expiry_ms || (Date.now() + 3300000);
        return r;
    } catch { return null; }
}

async function _trkRTCargarMapaLive() {
    const id = _trkRT.idRuta;
    if (!id) return;
    try {
        const [hist, actual] = await Promise.all([
            trkFetch(`/TrackingRecoleccion/trackingUbicacionHistorial?id_ruta=${id}&limit=300`),
            trkFetch(`/TrackingRecoleccion/trackingUbicacionActual?id_ruta=${id}`),
        ]);
        if (hist.success && Array.isArray(hist.ubicaciones)) {
            _trkRT.historial = hist.ubicaciones.map(_trkRTNormalizarUbicacion).filter(Boolean);
        }
        if (actual.success && actual.ubicacion) {
            _trkRTActualizarVehiculo(actual.ubicacion, { center: true, append: true });
        } else {
            _trkRTRepaintLiveMap();
        }
    } catch { _trkRTRepaintLiveMap(); }
}

function _trkRTLimpiarMapaLive() {
    if (_trk.liveVehicleMarker) { _trk.liveVehicleMarker.setMap(null); _trk.liveVehicleMarker = null; }
    if (_trk.liveVehiclePolyline) { _trk.liveVehiclePolyline.setMap(null); _trk.liveVehiclePolyline = null; }
    if (_trk.chatLiveVehicleMarker) { _trk.chatLiveVehicleMarker.setMap(null); _trk.chatLiveVehicleMarker = null; }
    if (_trk.chatLiveVehiclePolyline) { _trk.chatLiveVehiclePolyline.setMap(null); _trk.chatLiveVehiclePolyline = null; }
    _trk.liveVehiclePath = [];
    document.getElementById('trkLiveMapInfo')?.classList.add('d-none');
    document.getElementById('chatLiveMapInfo')?.classList.add('d-none');
    document.getElementById('chatLivePlaceholder')?.classList.remove('d-none');
}

function _trkRTIconoVehiculo(heading = 0) {
    const rot = Number.isFinite(heading) ? heading : 0;
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 42 42">
        <g transform="rotate(${rot} 21 21)">
            <circle cx="21" cy="21" r="19" fill="#0d9488" stroke="#fff" stroke-width="3"/>
            <g transform="translate(7 10)">
                <path d="M3 8h15v10H3z" fill="#fff" rx="2"/>
                <path d="M18 10h5.5l4.5 4.8V18H18z" fill="#fff"/>
                <path d="M21.3 12.2h2.1l2.2 2.4h-4.3z" fill="#0d9488"/>
                <circle cx="8" cy="20" r="2.6" fill="#0d9488" stroke="#fff" stroke-width="1.4"/>
                <circle cx="23" cy="20" r="2.6" fill="#0d9488" stroke="#fff" stroke-width="1.4"/>
                <path d="M3 18h25" stroke="#0d9488" stroke-width="1.2" opacity=".55"/>
            </g>
        </g>
    </svg>`;
    return {
        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
        scaledSize: new google.maps.Size(42, 42),
        anchor: new google.maps.Point(21, 21),
    };
}

function _trkChatFormatearConexion(ts) {
    if (!ts || isNaN(ts.getTime())) return 'Sin conexion registrada';
    const diffMs = Math.max(0, Date.now() - ts.getTime());
    const min = Math.floor(diffMs / 60000);
    if (min < 1) return 'Ultima conexion hace menos de 1 min';
    if (min < 60) return `Ultima conexion hace ${min} min`;
    const horas = Math.floor(min / 60);
    if (horas < 24) return `Ultima conexion hace ${horas} hora${horas === 1 ? '' : 's'}`;
    return `Ultima conexion ${ts.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })} ${ts.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    })}`;
}

function _trkChatActualizarConexion() {
    const el = document.getElementById('chatConnectionStatus');
    if (!el) return;
    if (_trk.chatLiveConectado) {
        el.textContent = 'Activo ahora';
        el.classList.add('is-active');
        return;
    }
    el.classList.remove('is-active');
    el.textContent = _trkChatFormatearConexion(_trk.chatUltimaConexionAt);
}

function _trkToggleChatMapPanel(forceCollapsed = null) {
    if (forceCollapsed && typeof forceCollapsed === 'object') forceCollapsed = null;
    const layout = document.getElementById('chatOperativoLayout');
    const modal = document.getElementById('modalChatOperativo');
    const btn = document.getElementById('btnToggleChatMap');
    if (!layout) return;
    const collapsed = forceCollapsed === null
        ? !layout.classList.contains('chat-map-collapsed')
        : !!forceCollapsed;
    layout.classList.toggle('chat-map-collapsed', collapsed);
    modal?.classList.toggle('chat-map-collapsed-modal', collapsed);
    if (btn) {
        btn.title = collapsed ? 'Mostrar mapa' : 'Ocultar mapa';
        btn.innerHTML = collapsed
            ? '<i class="fa-solid fa-angles-left"></i>'
            : '<i class="fa-solid fa-angles-right"></i>';
    }
    if (!collapsed && _trk.chatMapInstance && window.google?.maps) {
        setTimeout(() => {
            google.maps.event.trigger(_trk.chatMapInstance, 'resize');
            _trkChatRepaintLiveMap({ center: true });
        }, 180);
    }
}

function _trkChatPrepararMapaLive(idRuta, rutaNombre = '') {
    const subtitle = document.getElementById('chatLiveRutaNombre');
    if (subtitle) subtitle.textContent = rutaNombre ? `Ruta #${idRuta} - ${rutaNombre}` : `Ruta #${idRuta}`;
    const placeholder = document.getElementById('chatLivePlaceholder');
    const mapDiv = document.getElementById('chatLiveMap');
    if (!mapDiv) return;
    if (!window._trackGoogleMapsKey) {
        if (placeholder) {
            placeholder.innerHTML = '<i class="fa-solid fa-triangle-exclamation fa-2x opacity-25"></i><span class="small">Google Maps no disponible</span>';
            placeholder.classList.remove('d-none');
        }
        return;
    }
    _trkAsegurarGoogleMaps(() => {
        if (!_trk.chatMapInstance) {
            _trk.chatMapInstance = new google.maps.Map(mapDiv, {
                center: { lat: 19.4326, lng: -99.1332 },
                zoom: 6,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                styles: document.body.classList.contains('dark-mode') ? _TRK_DARK_MAP_STYLES : [],
            });
        }
        _trkActivarTraficoMapa(_trk.chatMapInstance, 'chatTrafficLayer');
        google.maps.event.trigger(_trk.chatMapInstance, 'resize');
        _trkChatRepaintLiveMap({ center: true });
    });
}

function _trkChatRepaintLiveMap(opts = {}) {
    if (!_trk.chatMapInstance || typeof google === 'undefined' || !google.maps) return;
    const map = _trk.chatMapInstance;
    const path = (_trkRT.historial || []).map(p => ({ lat: p.lat, lng: p.lng }));
    if (path.length) {
        if (!_trk.chatLiveVehiclePolyline) {
            _trk.chatLiveVehiclePolyline = new google.maps.Polyline({
                map,
                path,
                strokeColor: '#0d9488',
                strokeOpacity: 0.9,
                strokeWeight: 4,
                zIndex: 20,
            });
        } else {
            _trk.chatLiveVehiclePolyline.setMap(map);
            _trk.chatLiveVehiclePolyline.setPath(path);
        }
    }
    const ubi = _trkRT.ubicacion || _trkRT.historial[_trkRT.historial.length - 1] || null;
    if (!ubi) return;
    const pos = { lat: ubi.lat, lng: ubi.lng };
    const heading = Number.isFinite(ubi.heading) ? ubi.heading : 0;
    if (!_trk.chatLiveVehicleMarker) {
        _trk.chatLiveVehicleMarker = new google.maps.Marker({
            map,
            position: pos,
            icon: _trkRTIconoVehiculo(heading),
            title: 'Unidad en vivo',
            zIndex: 1000,
        });
    } else {
        _trk.chatLiveVehicleMarker.setMap(map);
        _trk.chatLiveVehicleMarker.setPosition(pos);
        _trk.chatLiveVehicleMarker.setIcon(_trkRTIconoVehiculo(heading));
    }
    if (opts.center) {
        map.panTo(pos);
        map.setZoom(Math.max(map.getZoom() || 0, 13));
    }
    document.getElementById('chatLivePlaceholder')?.classList.add('d-none');
    _trkRTActualizarInfoLive(ubi);
}

function _trkRTActualizarVehiculo(raw, opts = {}) {
    const ubi = _trkRTNormalizarUbicacion(raw);
    if (!ubi) return;
    _trkRT.ubicacion = ubi;
    if (opts.append !== false) {
        const last = _trkRT.historial[_trkRT.historial.length - 1];
        if (!last || Math.abs(last.lat - ubi.lat) > 0.000001 || Math.abs(last.lng - ubi.lng) > 0.000001) {
            _trkRT.historial.push(ubi);
            if (_trkRT.historial.length > 500) _trkRT.historial = _trkRT.historial.slice(-500);
        }
    }
    _trkRTActualizarUbicacion(ubi);
    _trkRTRepaintLiveMap(opts);
}

function _trkRTRepaintLiveMap(opts = {}) {
    _trkChatRepaintLiveMap(opts);
    if (!_trk.mapInstance || typeof google === 'undefined' || !google.maps) return;
    const map = _trk.mapInstance;
    const path = (_trkRT.historial || []).map(p => ({ lat: p.lat, lng: p.lng }));
    _trk.liveVehiclePath = path;
    if (path.length) {
        if (!_trk.liveVehiclePolyline) {
            _trk.liveVehiclePolyline = new google.maps.Polyline({
                map,
                path,
                strokeColor: '#0d9488',
                strokeOpacity: 0.9,
                strokeWeight: 4,
                zIndex: 20,
            });
        } else {
            _trk.liveVehiclePolyline.setMap(map);
            _trk.liveVehiclePolyline.setPath(path);
        }
    }
    const ubi = _trkRT.ubicacion || _trkRT.historial[_trkRT.historial.length - 1] || null;
    if (!ubi) return;
    const pos = { lat: ubi.lat, lng: ubi.lng };
    const heading = Number.isFinite(ubi.heading) ? ubi.heading : 0;
    if (!_trk.liveVehicleMarker) {
        _trk.liveVehicleMarker = new google.maps.Marker({
            map,
            position: pos,
            icon: _trkRTIconoVehiculo(heading),
            title: 'Unidad en vivo',
            zIndex: 1000,
        });
    } else {
        _trk.liveVehicleMarker.setMap(map);
        _trk.liveVehicleMarker.setPosition(pos);
        _trk.liveVehicleMarker.setIcon(_trkRTIconoVehiculo(heading));
    }
    if (opts.center) {
        map.panTo(pos);
        map.setZoom(Math.max(map.getZoom() || 0, 13));
    }
    _trkRTActualizarInfoLive(ubi);
}

function _trkRTActualizarInfoLive(ubi) {
    const card = document.getElementById('trkLiveMapInfo');
    if (!card || !ubi) return;
    const ts = ubi.updated_at ? new Date(String(ubi.updated_at).endsWith('Z') ? ubi.updated_at : ubi.updated_at + 'Z') : new Date();
    if (!isNaN(ts.getTime())) {
        _trk.chatUltimaConexionAt = ts;
        _trkChatActualizarConexion();
    }
    const timeTxt = isNaN(ts.getTime()) ? 'Sin fecha' : ts.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.getElementById('trkLiveUpdated').textContent = `Actualizado ${timeTxt}`;
    document.getElementById('trkLiveSpeed').textContent = ubi.speed !== null && !isNaN(ubi.speed) ? `Vel. ${Math.round(ubi.speed)} km/h` : 'Vel.  - ';
    document.getElementById('trkLiveAccuracy').textContent = ubi.accuracy !== null && !isNaN(ubi.accuracy) ? `Prec. ${Math.round(ubi.accuracy)} m` : 'Prec.  - ';
    document.getElementById('trkLiveBattery').textContent = ubi.battery !== null && !isNaN(ubi.battery) ? `Bat. ${ubi.battery}%` : 'Bat.  - ';
    card.classList.remove('d-none');
    const chatCard = document.getElementById('chatLiveMapInfo');
    if (chatCard) {
        document.getElementById('chatLiveUpdated').textContent = `Actualizado ${timeTxt}`;
        document.getElementById('chatLiveSpeed').textContent = ubi.speed !== null && !isNaN(ubi.speed) ? `Vel. ${Math.round(ubi.speed)} km/h` : 'Vel. -';
        document.getElementById('chatLiveAccuracy').textContent = ubi.accuracy !== null && !isNaN(ubi.accuracy) ? `Prec. ${Math.round(ubi.accuracy)} m` : 'Prec. -';
        document.getElementById('chatLiveBattery').textContent = ubi.battery !== null && !isNaN(ubi.battery) ? `Bat. ${ubi.battery}%` : 'Bat. -';
        document.getElementById('chatLiveCoords').textContent = `Coord. ${ubi.lat.toFixed(5)}, ${ubi.lng.toFixed(5)}`;
        chatCard.classList.remove('d-none');
    }
}

function _trkRTActualizarWsDot(conectado) {
    _trk.chatLiveConectado = !!conectado;
    if (conectado) _trk.chatUltimaConexionAt = new Date();
    _trkChatActualizarConexion();
    const dot = document.getElementById('trkWsDot');
    if (dot) {
        dot.style.background = conectado ? '#16a34a' : '#cbd5e1';
        dot.title = conectado ? 'En tiempo real' : 'Sin conexion en tiempo real';
    }
    const chatDot = document.getElementById('chatLiveWsDot');
    if (chatDot) {
        chatDot.classList.toggle('chat-ws-on', conectado);
        chatDot.classList.toggle('chat-ws-off', !conectado);
        chatDot.title = conectado ? 'En tiempo real' : 'Sin conexion en tiempo real';
    }
}

// --- Conectar WebSocket de ruta ---------------------------
function _trkRTConectarWS(cfg) {
    const wsBase = cfg?.ws_base || window._trackingChatWsBaseUrl;
    const token = cfg?.token || '';
    const apiKey = cfg?.api_key || '';
    if (!wsBase || !token || !apiKey || !_trkRT.idRuta) return;
    if (_trkRT.ws && _trkRT.ws.readyState === WebSocket.OPEN) return;
    if (_trkRT.ws) { _trkRT.ws.onclose = null; _trkRT.ws.close(); _trkRT.ws = null; }

    let ws;
    try {
        ws = new WebSocket(`${wsBase}/api/tracking/rutas/${_trkRT.idRuta}/live?token=${encodeURIComponent(token)}&api_key=${encodeURIComponent(apiKey)}`);
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

    ws.onclose = async (ev) => {
        clearInterval(_trkRT.wsPingIv); _trkRT.wsPingIv = null;
        _trkRT.ws = null;
        _trkRTActualizarWsDot(false);
        if (ev && (ev.code === 4001 || ev.code === 4003)) {
            _trkRT.liveCfg = ev.code === 4001 ? null : _trkRT.liveCfg;
            _trkRT.wsRetries = 5;
            Swal.fire({
                icon: 'warning',
                title: ev.code === 4001 ? 'Sesion expirada' : 'Sin acceso',
                text: ev.reason || (ev.code === 4001 ? 'Recarga la pagina para renovar el tracking en vivo.' : 'No tienes permiso para visualizar esta ruta en vivo.'),
                confirmButtonText: 'Aceptar',
            });
            return;
        }
        if (_trkRT.wsRetries < 5 && _trkRT.idRuta) {
            const delay = Math.min(1000 * Math.pow(2, _trkRT.wsRetries), 30000);
            _trkRT.wsRetries++;
            _trkRT.wsRetryTO = setTimeout(async () => {
                const liveCfg = await _trkRTObtenerLiveConfig();
                if (liveCfg && _trkRT.idRuta) _trkRTConectarWS(liveCfg);
            }, delay);
        }
    };

    ws.onerror = () => { /* onclose disparara */ };
}

// --- Procesar eventos WS de ruta -------------------------
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
            _trkRTActualizarVehiculo(data, { append: true });
            break;
        case 'vehicle.snapshot':
        case 'vehicle.location':
            _trkRTActualizarVehiculo(data.data || data, { center: data.event === 'vehicle.snapshot', append: true });
            break;
        case 'route.destination.changed':
            _trkAplicarEventoCambioCedisDestino(data.data || data);
            break;
        case 'otp.generated': {
            const payload = data.data || data;
            const idDetalle = payload.id_detalle || payload.detalle_id || payload.otp?.id_detalle;
            if (idDetalle) {
                const otp = payload.otp || payload;
                $(`#trkOtpEstado-${idDetalle}`).html(_trkRenderOtpEstado(otp));
            }
            break;
        }
        case 'tracking.event': {
            const payload = data.data || data;
            const tipo = String(payload.type || payload.tipo || '').toLowerCase();
            if (tipo === 'recoleccion_confirmada_otp') {
                if (Array.isArray(payload.changes) && payload.changes.length) {
                    _trkRTAplicarChanges(payload.changes);
                } else {
                    const idDetalle = payload.id_detalle || payload.detalle_id;
                    _trkDetalleActualizarRecoleccion(idDetalle, 'recolectada');
                    const nextId = payload.next_id_detalle || payload.siguiente_id_detalle;
                    if (nextId) _trkDetalleActualizarRecoleccion(nextId, 'en_camino');
                    if (_trkRT.estado && idDetalle) {
                        _trkRTAplicarChanges([{ id_detalle: idDetalle, estatus_recoleccion: 'recolectada' }]);
                    }
                }
            } else {
                _trkRTCargarEstado();
            }
            break;
        }
        case 'error': {
            const code = String(data.code || data.codigo || '');
            if (code === '4001') {
                _trkRT.liveCfg = null;
                _trkRT.wsRetries = 5;
                _trkRTActualizarWsDot(false);
                Swal.fire({ icon: 'warning', title: 'Sesion expirada', text: 'Recarga la pagina para renovar el tracking en vivo.', confirmButtonText: 'Aceptar' });
            } else if (code === '4003') {
                _trkRT.wsRetries = 5;
                _trkRTActualizarWsDot(false);
                Swal.fire({ icon: 'warning', title: 'Sin acceso', text: data.message || data.detail || 'No tienes permiso para visualizar esta ruta en vivo.', confirmButtonText: 'Aceptar' });
            }
            break;
        }
    }
}

/* ============================================================
   CHAT OPERATIVO  -  gestor (Sparta Ledger)
   Flujo:
     1. Usuario hace clic en btn-abrir-chat de tablaRutas.
     2. Se obtiene el detalle de la ruta para listar id_detalle.
     3. Se abre el offcanvas con una pestana por id_detalle.
     4. Al activar una pestana, se carga info del chat por REST.
     5. Si el chat esta activo, se conecta WebSocket (solo lectura).
     6. Mensajes se envian siempre por REST.
============================================================ */

// --- Estado global del Chat ------------------------------
const _trkChat = {
    rutaId:    null,   // id_ruta abierto actualmente
    activeTab: null,   // id_detalle de la pestana activa
    jwtToken:  null,   // JWT en memoria JS (solo para WS)
    jwtExpiry: 0,      // timestamp ms de expiracion
    chats:     {},     // Map<id_detalle, chatState>
};
/* chatState = {
    id_chat, estatus, mensajes[], ws, wsRetries, wsRetryTimeout,
    unread, loadingMsgs, allLoaded, oldestMsgId
} */

// --- Abrir chat de una ruta (entry point) ----------------
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
                modelo:         d.modelo || '',
                bin:            d.bin || d.vin || '',
                estado:         d.estado || '',
                municipio:      d.municipio || '',
                direccion:      d.direccion || '',
                estatus_confirmacion_gestor: d.estatus_confirmacion_gestor || '',
                estatus_recoleccion: d.estatus_recoleccion || '',
            }));
            _trkChatAbrir(idRuta, r.datos.nombre_ruta || `Ruta #${idRuta}`, detalle);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexion.', confirmButtonText: 'Aceptar' }));
}

function _trkChatRenderContextoDetalle(det) {
    const idDetalle = det?.id_detalle || '';
    const idCredito = det?.id_credito || 'Sin credito';
    const orden = parseInt(det?.orden_ruta, 10) || 0;
    const cliente = det?.nombre_cliente || 'Sin cliente';
    const modelo = det?.modelo || 'Unidad no disponible';
    const vin = det?.bin || 'VIN no disponible';
    const estado = det?.estado || 'Sin estado';
    const municipio = det?.municipio || 'Sin municipio';
    const direccion = det?.direccion || 'Sin direccion registrada';
    const confirmacion = det?.estatus_confirmacion_gestor || 'Sin confirmacion';
    const recoleccion = det?.estatus_recoleccion || 'Sin avance';
    return `<div class="chat-detail-context-main">
            <span class="chat-detail-context-chip">
                <i class="fa-solid fa-location-dot"></i>Punto ${orden || '-'}
            </span>
            <span class="chat-detail-context-title">${_trkChatEscapeHtml(cliente)}</span>
            <span class="chat-detail-context-chip">Credito #${_trkChatEscapeHtml(idCredito)}</span>
            <span class="chat-detail-context-chip">Detalle #${_trkChatEscapeHtml(idDetalle)}</span>
        </div>
        <div class="chat-detail-context-grid">
            <span class="chat-detail-context-item"><b>Unidad:</b> ${_trkChatEscapeHtml(modelo)}</span>
            <span class="chat-detail-context-item"><b>VIN:</b> ${_trkChatEscapeHtml(vin)}</span>
            <span class="chat-detail-context-item"><b>Estado:</b> ${_trkChatEscapeHtml(estado)}</span>
            <span class="chat-detail-context-item"><b>Municipio:</b> ${_trkChatEscapeHtml(municipio)}</span>
            <span class="chat-detail-context-item"><b>Confirmacion:</b> ${_trkChatEscapeHtml(confirmacion)}</span>
            <span class="chat-detail-context-item"><b>Recoleccion:</b> ${_trkChatEscapeHtml(recoleccion)}</span>
            <span class="chat-detail-context-item chat-detail-context-address"><b>Direccion:</b> ${_trkChatEscapeHtml(direccion)}</span>
        </div>`;
}

function _trkChatDetalleActivoValido(idDetalle, accion = 'continuar') {
    const id = Number(idDetalle);
    const state = _trkChat.chats[id];
    const pane = document.getElementById(`chatPane_${id}`);
    if (!state || !pane) {
        Swal.fire({
            icon: 'warning',
            title: 'Punto no disponible',
            text: 'No se encontro el punto de recoleccion para esta accion.',
            confirmButtonText: 'Aceptar',
        });
        return false;
    }
    if (Number(_trkChat.activeTab) !== id || !pane.classList.contains('active')) {
        Swal.fire({
            icon: 'warning',
            title: 'Revisa el punto activo',
            text: `Para ${accion}, primero abre la pestana correcta del punto de recoleccion.`,
            confirmButtonText: 'Aceptar',
        });
        return false;
    }
    return true;
}

function _trkChatEventoPerteneceDetalle(idDetalle, data) {
    const recibido = data?.id_detalle || data?.detalle_id || data?.mensaje?.id_detalle;
    return !recibido || Number(recibido) === Number(idDetalle);
}

function _trkChatResumenDetalle(idDetalle) {
    const det = _trkChat.chats[Number(idDetalle)]?.detalle || {};
    const partes = [
        det.orden_ruta ? `Punto ${det.orden_ruta}` : '',
        det.id_detalle ? `Detalle #${det.id_detalle}` : '',
        det.id_credito ? `Credito #${det.id_credito}` : '',
        det.nombre_cliente || '',
    ].filter(Boolean);
    return partes.join(' | ') || `Detalle #${idDetalle}`;
}

function _trkChatActualizarContextoDetalle(idDetalle, patch = {}) {
    const state = _trkChat.chats[Number(idDetalle)];
    if (!state) return;
    state.detalle = { ...(state.detalle || {}), ...(patch || {}) };
    const el = document.getElementById(`chatContext_${idDetalle}`);
    if (el) el.innerHTML = _trkChatRenderContextoDetalle(state.detalle);
}

function _trkChatAbrir(idRuta, rutaNombre, detalleItems) {
    _trkChatLimpiarTodo();
    _trkToggleChatMapPanel(false);
    _trk.chatConnectionTimer = setInterval(_trkChatActualizarConexion, 60000);
    _trkChatActualizarConexion();
    _trkChat.rutaId = idRuta;
    _trk.chatUnreadPorRuta[String(idRuta)] = 0;
    _trkActualizarRutaChatBadge(idRuta);

    document.getElementById('chatRutaNombre').textContent = rutaNombre;

    const list        = document.getElementById('chatTabList');
    const tabsWrap    = document.getElementById('chatTabsWrap');
    const container   = document.getElementById('chatPanesContainer');
    const placeholder = document.getElementById('chatEmptyPlaceholder');

    list.innerHTML      = '';
    container.innerHTML = '';

    const modalChat = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalChatOperativo')
    );
    document.getElementById('modalChatOperativo').addEventListener('shown.bs.modal', () => {
        _trkChatPrepararMapaLive(idRuta, rutaNombre);
        _trkRTIniciar(idRuta);
    }, { once: true });
    modalChat.show();

    if (!detalleItems || detalleItems.length === 0) {
        tabsWrap.style.display    = 'none';
        placeholder.classList.remove('d-none');
        placeholder.classList.add('d-flex');
        return;
    }
    placeholder.classList.add('d-none');
    placeholder.classList.remove('d-flex');
    tabsWrap.style.display    = '';

    detalleItems.forEach(det => {
        const id = det.id_detalle;
        _trkChat.chats[id] = {
            id_chat: null, estatus: null, mensajes: [],
            detalle: det,
            ws: null, wsRetries: 0, wsRetryTimeout: null, wsPingInterval: null,
            unread: 0, loadingMsgs: false, allLoaded: false, oldestMsgId: null,
            typingTimeout: null, typingStopTimeout: null, lastTypingSent: 0,
        };

        // Tab ---------------------------------------------
        const li = document.createElement('li');
        li.className = 'nav-item';
        const credLabel = det.id_credito ? `  -  ${det.id_credito}` : '';
        li.innerHTML = `
            <button class="chat-tab-link" id="chatTabBtn_${id}" data-detalle="${id}" type="button"
                    title="${_trkChatEscapeHtml(det.nombre_cliente)}">
                <span>#${id}${credLabel}</span>
                <span class="chat-status-badge chat-status-desconocido" id="chatStatusBadge_${id}">...</span>
                <span class="chat-unread-badge d-none" id="chatUnreadBadge_${id}"></span>
            </button>`;
        list.appendChild(li);
        li.querySelector('button').addEventListener('click', () => _trkChatActivarTab(id));

        // Pane --------------------------------------------
        const pane = document.createElement('div');
        pane.className = 'chat-pane';
        pane.id        = `chatPane_${id}`;
        pane.innerHTML = `
            <div class="chat-detail-context" id="chatContext_${id}">
                ${_trkChatRenderContextoDetalle(det)}
            </div>
            <div class="chat-status-notice d-none" id="chatNotice_${id}"></div>
            <div class="chat-messages-wrap" id="chatMsgsWrap_${id}"></div>
            <div class="chat-typing-indicator d-none" id="chatTyping_${id}">
                <span>Escribiendo</span>
                <span class="chat-typing-dots"><span></span><span></span><span></span></span>
            </div>
            <button class="chat-new-msg-btn d-none" id="chatNewMsgBtn_${id}"
                    type="button">Nuevo mensaje  abajo</button>
            <div class="chat-input-area" id="chatInputArea_${id}">
                <div class="chat-attachment-bar">
                    <button class="chat-attach-btn" type="button" data-detalle="${id}" data-tipo="foto"
                            data-accept="image/jpeg,image/png,image/webp,image/gif" title="Subir foto" disabled>
                        <i class="fa-solid fa-camera"></i>
                    </button>
                    <button class="chat-attach-btn" type="button" data-detalle="${id}" data-tipo="video"
                            data-accept="video/mp4,video/quicktime,video/x-m4v,video/webm" title="Subir video" disabled>
                        <i class="fa-solid fa-video"></i>
                    </button>
                    <button class="chat-attach-btn" type="button" data-detalle="${id}" data-tipo="archivo"
                            data-accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt" title="Subir archivo" disabled>
                        <i class="fa-solid fa-paperclip"></i>
                    </button>
                    <input type="file" class="d-none chat-file-input" id="chatFileInput_${id}">
                </div>
                <div class="chat-compose-row">
                    <textarea class="form-control chat-textarea" id="chatTextarea_${id}"
                              placeholder="Escribe un mensaje..." rows="2"
                              maxlength="2000" disabled></textarea>
                    <button class="chat-send-btn" id="chatSendBtn_${id}"
                            type="button" disabled>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>`;
        container.appendChild(pane);

        // Listeners ---------------------------------------
        document.getElementById(`chatNewMsgBtn_${id}`)
            .addEventListener('click', () => _trkChatScrollFinal(id));

        document.getElementById(`chatSendBtn_${id}`)
            .addEventListener('click', () => _trkChatEnviarMensaje(id));

        document.querySelectorAll(`#chatInputArea_${id} .chat-attach-btn`).forEach(btn => {
            btn.addEventListener('click', () => _trkChatSeleccionarArchivo(id, btn.dataset.tipo || 'archivo', btn.dataset.accept || ''));
        });
        document.getElementById(`chatFileInput_${id}`)
            .addEventListener('change', e => _trkChatPrepararArchivo(id, e.target.files?.[0] || null, e.target));

        document.getElementById(`chatTextarea_${id}`)
            .addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    _trkChatEnviarMensaje(id);
                }
            });
        document.getElementById(`chatTextarea_${id}`)
            .addEventListener('input', e => _trkChatEmitirTyping(id, e.target.value.trim() !== ''));
        document.getElementById(`chatTextarea_${id}`)
            .addEventListener('blur', () => _trkChatEmitirTyping(id, false));

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

    // Activar primera pestana ------------------------------
    if (detalleItems.length > 0) {
        _trkChatActivarTab(detalleItems[0].id_detalle);
    }

    // Limpiar WS al cerrar el modal
    document.getElementById('modalChatOperativo')
        .addEventListener('hidden.bs.modal', () => {
            _trkChatLimpiarTodo();
            _trkRTLimpiar();
        }, { once: true });
}

// --- Gestion de pestanas ---------------------------------
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

// --- Carga de info del chat ------------------------------
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
        if (chat.unread_count !== undefined && chat.unread_count !== null) {
            state.unread = Math.max(0, parseInt(chat.unread_count, 10) || 0);
            _trkChatActualizarUnreadBadge(idDetalle);
        }
        _trkChatActualizarEstatusBadge(idDetalle, chat.estatus);
        _trkChatActualizarUI(idDetalle);
        await _trkChatCargarMensajes(idDetalle);
        if (chat.estatus === 'activo' || chat.estatus === 'bloqueado') {
            const token = await _trkChatObtenerToken();
            if (token) _trkChatConectarWS(idDetalle, token);
        }
    } catch {
        _trkChatMostrarError(idDetalle, 'Error de conexion al cargar el chat.');
    }
}

// --- Carga paginada de mensajes --------------------------
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

// --- Render de mensajes ----------------------------------
function _trkChatRenderMensajes(idDetalle, scrollToBottom = true) {
    const state = _trkChat.chats[idDetalle];
    const wrap  = document.getElementById(`chatMsgsWrap_${idDetalle}`);
    if (!state || !wrap) return;

    if (state.mensajes.length === 0) {
        wrap.innerHTML = `<div class="text-center text-muted small py-5">
            <i class="fa-solid fa-comment-slash opacity-25 fa-2x mb-2 d-block"></i>
            Sin mensajes aun</div>`;
        return;
    }

    let html = state.allLoaded
        ? `<div class="text-center text-muted py-2" style="font-size:.7rem;"> -  Inicio de la conversacion  - </div>`
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
        <div class="chat-bubble">${_trkChatRenderContenidoMensaje(msg)}</div>
        <span class="chat-bubble-meta">${actor}  -  ${hora}</span>
    </div>`;
}

function _trkChatArchivoUrl(msg) {
    const url = msg?.metadata?.archivo?.url;
    if (!url) return null;
    if (/^https?:\/\//i.test(url)) return url;
    const base = String(window._trackingApiBaseUrl || '').replace(/\/$/, '');
    return base ? `${base}${url.startsWith('/') ? '' : '/'}${url}` : url;
}

function _trkChatFormatBytes(bytes) {
    const n = parseInt(bytes, 10);
    if (!n || n < 0) return '';
    const units = ['B', 'KB', 'MB', 'GB'];
    let val = n;
    let idx = 0;
    while (val >= 1024 && idx < units.length - 1) {
        val /= 1024;
        idx++;
    }
    return `${val >= 10 || idx === 0 ? Math.round(val) : val.toFixed(1)} ${units[idx]}`;
}

function _trkChatRenderContenidoMensaje(msg) {
    const archivo = msg?.metadata?.archivo || null;
    const archivoUrl = _trkChatArchivoUrl(msg);
    if (!archivo || !archivoUrl) return _trkChatEscapeHtml(msg.mensaje || '');

    const tipoMsg = String(msg.tipo_mensaje || archivo.tipo || '').toLowerCase();
    const nombre = archivo.nombre_original || 'Archivo';
    const caption = msg.mensaje ? `<div class="chat-attachment-caption">${_trkChatEscapeHtml(msg.mensaje)}</div>` : '';

    if (tipoMsg === 'imagen' || archivo.tipo === 'imagen') {
        return `<a href="${_trkChatEscapeHtml(archivoUrl)}" target="_blank" rel="noreferrer">
            <img class="chat-attachment-media" src="${_trkChatEscapeHtml(archivoUrl)}" alt="${_trkChatEscapeHtml(nombre)}">
        </a>${caption}`;
    }
    if (tipoMsg === 'video' || archivo.tipo === 'video') {
        return `<video class="chat-attachment-media chat-attachment-video" src="${_trkChatEscapeHtml(archivoUrl)}" controls></video>${caption}`;
    }
    const size = _trkChatFormatBytes(archivo.size_bytes);
    const ext = archivo.extension || '';
    return `<a class="chat-attachment-file" href="${_trkChatEscapeHtml(archivoUrl)}" target="_blank" rel="noreferrer">
        <i class="fa-solid fa-file-arrow-down"></i>
        <span>
            <span>${_trkChatEscapeHtml(nombre)}</span>
            <small>${_trkChatEscapeHtml([ext, size].filter(Boolean).join('  -  '))}</small>
        </span>
    </a>${caption}`;
}

function _trkChatAgregarMensaje(idDetalle, msg) {
    const state = _trkChat.chats[idDetalle];
    if (!state) return;
    if (state.mensajes.find(m => m.id_mensaje === msg.id_mensaje)) return; // deduplicar
    _trkChatMostrarTyping(idDetalle, false);
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

    const esEntrada = String(msg.tipo_actor || '').toLowerCase() !== 'gestor';
    if (esEntrada && (_trkChat.activeTab !== idDetalle || !atBottom)) {
        state.unread++;
        _trkChatActualizarUnreadBadge(idDetalle);
    }
}

function _trkChatMostrarTyping(idDetalle, mostrar = true, actor = 'Conductor') {
    const state = _trkChat.chats[idDetalle];
    const el = document.getElementById(`chatTyping_${idDetalle}`);
    if (!state || !el) return;
    if (state.typingTimeout) {
        clearTimeout(state.typingTimeout);
        state.typingTimeout = null;
    }
    if (!mostrar) {
        el.classList.add('d-none');
        return;
    }
    const label = actor && actor !== 'Conductor' ? `${actor} escribiendo` : 'Escribiendo';
    const labelEl = el.querySelector('span:first-child');
    if (labelEl) labelEl.textContent = label;
    el.classList.remove('d-none');
    state.typingTimeout = setTimeout(() => _trkChatMostrarTyping(idDetalle, false), 4500);
    if (_trkChat.activeTab === idDetalle) _trkChatScrollFinal(idDetalle);
}

function _trkChatEmitirTyping(idDetalle, activo) {
    const state = _trkChat.chats[idDetalle];
    if (!state || state.estatus !== 'activo' || !state.ws || state.ws.readyState !== WebSocket.OPEN) return;
    clearTimeout(state.typingStopTimeout);
    const now = Date.now();
    if (activo) {
        if (now - (state.lastTypingSent || 0) > 1800) {
            try { state.ws.send(JSON.stringify({ event: 'typing.start', tipo_actor: 'gestor' })); } catch {}
            state.lastTypingSent = now;
        }
        state.typingStopTimeout = setTimeout(() => _trkChatEmitirTyping(idDetalle, false), 1800);
    } else {
        try { state.ws.send(JSON.stringify({ event: 'typing.stop', tipo_actor: 'gestor' })); } catch {}
        state.lastTypingSent = 0;
    }
}

function _trkChatScrollFinal(idDetalle) {
    const wrap = document.getElementById(`chatMsgsWrap_${idDetalle}`);
    if (wrap) wrap.scrollTo({ top: wrap.scrollHeight, behavior: 'smooth' });
    const btn = document.getElementById(`chatNewMsgBtn_${idDetalle}`);
    if (btn) btn.classList.add('d-none');
}

// --- Enviar mensaje --------------------------------------
async function _trkChatEnviarMensaje(idDetalle) {
    if (!_trkChatDetalleActivoValido(idDetalle, 'enviar mensajes')) return;
    const state    = _trkChat.chats[idDetalle];
    const textarea = document.getElementById(`chatTextarea_${idDetalle}`);
    const sendBtn  = document.getElementById(`chatSendBtn_${idDetalle}`);
    if (!state || state.estatus !== 'activo' || !textarea || !sendBtn) return;

    const texto = textarea.value.trim();
    if (!texto) return;

    _trkChatEmitirTyping(idDetalle, false);
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
            // Si WS no esta activo, agregar localmente para feedback inmediato
            if ((!state.ws || state.ws.readyState !== WebSocket.OPEN) && r.mensaje) {
                _trkChatAgregarMensaje(idDetalle, r.mensaje);
            }
            // Si WS activo, el evento message.new lo agregara (evita duplicados)
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

// --- Token JWT (para WebSocket) --------------------------
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

// --- WebSocket -------------------------------------------
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
        // Heartbeat cada 30s para mantener la conexion activa en Cloud Run
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

        // Codigos de cierre definitivo (no reintentar)
        if (evt.code === 4001) { // token invalido/expirado
            _trkChat.jwtToken = null;
            _trkChatMostrarNotice(idDetalle, 'Sesion expirada. Recarga la pagina.', 'cerrado');
            return;
        }
        if (evt.code === 4003) { // sin acceso
            _trkChatMostrarNotice(idDetalle, 'Sin acceso a este chat.', 'cerrado');
            return;
        }

        // Reintento con back-off exponencial (max. 5 intentos)
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
                'Sin conexion en tiempo real  -  los mensajes se actualizan al enviar.',
                'cerrado'
            );
        }
    };
    ws.onerror = () => { /* ws.onclose disparara a continuacion */ };
}

function _trkChatProcesarEventoWS(idDetalle, data) {
    const state = _trkChat.chats[idDetalle];
    if (!state) return;
    if (!_trkChatEventoPerteneceDetalle(idDetalle, data)) return;

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

        case 'typing':
        case 'typing.start':
        case 'typing.stop':
        case 'chat.typing': {
            const tipoActor = String(data.tipo_actor || data.actor || '').toLowerCase();
            if (tipoActor === 'gestor') break;
            const nombre = data.nombre_remitente || data.nombre || 'Conductor';
            const activo = data.event === 'typing.stop'
                ? false
                : (data.typing ?? data.is_typing ?? data.active ?? true);
            _trkChatMostrarTyping(idDetalle, !!activo, nombre);
            break;
        }

        case 'chat.unlocked':
            state.estatus = 'activo';
            _trkChatActualizarEstatusBadge(idDetalle, 'activo');
            _trkChatActualizarUI(idDetalle);
            _trkChatMostrarNotice(idDetalle, 'La ruta ha iniciado  -  ya puedes enviar mensajes.', 'activo', 5000);
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
    if (state.typingTimeout) { clearTimeout(state.typingTimeout); state.typingTimeout = null; }
    if (state.typingStopTimeout) { clearTimeout(state.typingStopTimeout); state.typingStopTimeout = null; }
    _trkChatMostrarTyping(idDetalle, false);
    if (state.ws) { state.ws.onclose = null; state.ws.close(); state.ws = null; }
}

function _trkChatLimpiarTodo() {
    Object.keys(_trkChat.chats).forEach(id => _trkChatDesconectarWS(Number(id)));
    _trkChat.chats     = {};
    _trkChat.activeTab = null;
    _trkChat.rutaId    = null;
    if (_trk.chatConnectionTimer) {
        clearInterval(_trk.chatConnectionTimer);
        _trk.chatConnectionTimer = null;
    }
    _trk.chatLiveConectado = false;
    _trk.chatUltimaConexionAt = null;
    _trkChatActualizarConexion();
}

// --- Actualizar UI segun estatus -------------------------
function _trkChatActualizarUI(idDetalle) {
    const state    = _trkChat.chats[idDetalle];
    const textarea = document.getElementById(`chatTextarea_${idDetalle}`);
    const sendBtn  = document.getElementById(`chatSendBtn_${idDetalle}`);
    const notice   = document.getElementById(`chatNotice_${idDetalle}`);
    const attachBtns = document.querySelectorAll(`#chatInputArea_${idDetalle} .chat-attach-btn`);
    if (!state) return;

    if (state.estatus === 'activo') {
        if (notice)   notice.classList.add('d-none');
        if (textarea) textarea.disabled = false;
        if (sendBtn)  sendBtn.disabled  = false;
        attachBtns.forEach(btn => { btn.disabled = false; });
    } else if (state.estatus === 'bloqueado') {
        _trkChatMostrarNotice(idDetalle, ' El chat aun no esta disponible  -  la ruta no ha iniciado.', 'bloqueado');
        if (textarea) textarea.disabled = true;
        if (sendBtn)  sendBtn.disabled  = true;
        attachBtns.forEach(btn => { btn.disabled = true; });
    } else if (state.estatus === 'cerrado') {
        _trkChatMostrarNotice(idDetalle, 'Esta conversacion ha sido cerrada.', 'cerrado');
        if (textarea) textarea.disabled = true;
        if (sendBtn)  sendBtn.disabled  = true;
        attachBtns.forEach(btn => { btn.disabled = true; });
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

// --- Badges no leidos ------------------------------------
function _trkChatClearUnread(idDetalle) {
    const state = _trkChat.chats[idDetalle];
    if (state) state.unread = 0;
    const badge = document.getElementById(`chatUnreadBadge_${idDetalle}`);
    if (badge) badge.classList.add('d-none');
    _trkChatActualizarRutaUnreadBadge();
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
    _trkChatActualizarRutaUnreadBadge();
}

function _trkChatActualizarRutaUnreadBadge() {
    const idRuta = _trkChat.rutaId;
    if (!idRuta) return;
    const total = Object.values(_trkChat.chats).reduce((acc, st) => acc + (parseInt(st.unread, 10) || 0), 0);
    _trk.chatUnreadPorRuta[String(idRuta)] = total;
    _trkActualizarRutaChatBadge(idRuta);
}

// --- Helpers UI ------------------------------------------
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
    const attachBtns = document.querySelectorAll(`#chatInputArea_${idDetalle} .chat-attach-btn`);
    if (textarea) textarea.disabled = true;
    if (sendBtn)  sendBtn.disabled  = true;
    attachBtns.forEach(btn => { btn.disabled = true; });
    _trkChatMostrarNotice(idDetalle, motivo, 'cerrado');
}

function _trkChatSeleccionarArchivo(idDetalle, tipo, accept = '') {
    if (!_trkChatDetalleActivoValido(idDetalle, 'adjuntar evidencia')) return;
    const input = document.getElementById(`chatFileInput_${idDetalle}`);
    if (!input) return;
    input.value = '';
    input.accept = accept;
    input.dataset.tipo = tipo;
    input.click();
}

async function _trkChatPrepararArchivo(idDetalle, file, input) {
    if (!file) return;
    if (!_trkChatDetalleActivoValido(idDetalle, 'adjuntar evidencia')) {
        if (input) input.value = '';
        return;
    }
    if (file.size > 100 * 1024 * 1024) {
        Swal.fire({ icon: 'warning', title: 'Archivo muy grande', text: 'El archivo no puede superar 100 MB.', confirmButtonText: 'Aceptar' });
        if (input) input.value = '';
        return;
    }
    const tipo = _trkChatTipoArchivo(file);
    const preview = await _trkChatPreviewArchivo(file, tipo);
    const contexto = _trkChatEscapeHtml(_trkChatResumenDetalle(idDetalle));
    const res = await Swal.fire({
        title: 'Enviar evidencia',
        html: `<div class="alert alert-info py-2 px-3 text-start small mb-3">
                <i class="fa-solid fa-circle-info me-1"></i>
                Esta evidencia se adjuntara a: <b>${contexto}</b>
            </div>${preview}`,
        input: 'textarea',
        inputPlaceholder: 'Mensaje opcional...',
        inputAttributes: { maxlength: 1000, rows: 3 },
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488',
    });
    if (input) input.value = '';
    if (!res.isConfirmed) return;
    await _trkChatSubirArchivo(idDetalle, file, String(res.value || '').trim());
}

function _trkChatTipoArchivo(file) {
    const t = String(file.type || '').toLowerCase();
    if (t.startsWith('image/')) return 'imagen';
    if (t.startsWith('video/')) return 'video';
    return 'archivo';
}

function _trkChatPreviewArchivo(file, tipo) {
    const safeName = _trkChatEscapeHtml(file.name || 'Archivo');
    const size = _trkChatFormatBytes(file.size);
    if (tipo === 'imagen') {
        return new Promise(resolve => {
            const reader = new FileReader();
            reader.onload = () => resolve(`<div class="text-center">
                <img src="${reader.result}" style="max-width:260px;max-height:180px;border-radius:8px;object-fit:contain;">
                <div class="small text-muted mt-2">${safeName} ${size ? ' -  ' + size : ''}</div>
            </div>`);
            reader.onerror = () => resolve(`<div class="text-center small">${safeName}</div>`);
            reader.readAsDataURL(file);
        });
    }
    const icon = tipo === 'video' ? 'fa-file-video' : 'fa-file-lines';
    return Promise.resolve(`<div class="d-flex align-items-center gap-2 justify-content-center">
        <i class="fa-solid ${icon}" style="font-size:2rem;color:#0d9488;"></i>
        <div class="text-start">
            <div class="fw-semibold">${safeName}</div>
            <div class="small text-muted">${size || 'Archivo'}</div>
        </div>
    </div>`);
}

async function _trkChatSubirArchivo(idDetalle, file, mensaje = '') {
    if (!_trkChatDetalleActivoValido(idDetalle, 'subir evidencia')) return;
    const state = _trkChat.chats[idDetalle];
    if (!state || state.estatus !== 'activo') return;
    const sendBtn = document.getElementById(`chatSendBtn_${idDetalle}`);
    const attachBtns = document.querySelectorAll(`#chatInputArea_${idDetalle} .chat-attach-btn`);
    const oldHtml = sendBtn ? sendBtn.innerHTML : '';
    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    attachBtns.forEach(btn => { btn.disabled = true; });
    try {
        const formData = new FormData();
        formData.append('id_detalle', String(idDetalle));
        formData.append('archivo', file);
        if (mensaje) formData.append('mensaje', mensaje);
        const r = await fetch('/TrackingRecoleccion/chatSubirArchivo', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
        }).then(resp => resp.json());
        if (!r.success || !r.mensaje) {
            Swal.fire({ icon: 'error', title: 'No se pudo subir', text: r.mensaje || r.detail || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            if ([401, 403, 404, 409, 413, 415].includes(parseInt(r.codigo_http, 10))) {
                _trkChatMostrarNotice(idDetalle, r.mensaje || 'No se pudo subir el archivo.', 'cerrado', 5000);
            }
            return;
        }
        _trkChatAgregarMensaje(idDetalle, r.mensaje);
    } catch {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexion al subir el archivo.', confirmButtonText: 'Aceptar' });
    } finally {
        if (sendBtn) sendBtn.innerHTML = oldHtml || '<i class="fa-solid fa-paper-plane"></i>';
        _trkChatActualizarUI(idDetalle);
    }
}

function _trkChatAdjuntoPendiente(idDetalle, tipo) {
    const labels = { foto: 'foto', video: 'video', archivo: 'archivo' };
    Swal.fire({
        icon: 'info',
        title: 'Adjuntos listos para conectar',
        text: `El boton de ${labels[tipo] || 'archivo'} ya esta preparado para id_detalle ${idDetalle}. Falta enlazar el endpoint de adjuntos del servicio de tracking.`,
        confirmButtonText: 'Entendido',
    });
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

function _trkSanitizarNombreRuta(nombre) {
    return String(nombre || '')
        .replace(/^\s*(?:(?:#|BOR-|RUTA-)\s*\d+\s*)+/i, '')
        .replace(/\s+/g, ' ')
        .trim()
        .toUpperCase();
}

function _trkChatFechaLocal(iso) {
    if (!iso) return '';
    try {
        const s = iso.endsWith('Z') || /[+\-]\d{2}:\d{2}$/.test(iso) ? iso : iso + 'Z';
        return new Date(s).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch { return iso; }
}
</script>
