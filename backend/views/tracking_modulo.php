<?php /** @var string $google_maps_api_key_js */ ?>
<style>
/* =======================================================
   Tracking RecolecciÃƒÂ³n Ã¢â‚¬â€ variables de color (teal/cyan)
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

/* -- Cabecera del mÃƒÂ³dulo -- */
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
    grid-template-columns: repeat(4, minmax(0, 1fr));
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

.track-filters {
    background: var(--track-bg-card);
    border: 1px solid var(--track-border);
    border-radius: .75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
body.dark-mode .track-filters { background: #1e2d2c; }

/* -- Tabla crÃƒÂ©ditos -- */
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
#tablaBorradores_wrapper .dataTables_paginate,
#tablaBorradores_wrapper .dt-paging {
    display: flex;
    justify-content: flex-end;
}
#tablaBorradores_wrapper .pagination {
    justify-content: flex-end;
    margin-left: auto;
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

/* -- Badges de estatus de confirmaciÃƒÂ³n gestor -- */
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

/* -- Lista de crÃƒÂ©ditos en modal (sortable) -- */
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

/* -- BotÃƒÂ³n pin ubicaciÃƒÂ³n en fila de crÃƒÂ©dito -- */
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

/* ========================================================
   Chat Operativo Ã¢â‚¬â€ Offcanvas lateral
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

/* Badge de mensajes no leÃƒÂ­dos */
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

/* Indicador WS en lÃƒÂ­nea */
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

/* Aviso de estatus (bloqueado / cerrado / sin conexiÃƒÂ³n) */
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

/* ÃƒÂrea de mensajes */
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

/* BotÃƒÂ³n "Nuevo mensaje Ã¢â€ â€œ" flotante */
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

/* ÃƒÂrea de input */
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
</style>
<?php endif; ?>

<div class="container-fluid py-3 px-3 px-md-4">

    <!-- -- Cabecera -- -->
    <div class="track-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="fa-solid fa-route me-2"></i><?= htmlspecialchars($trackingHeaderTitle, ENT_QUOTES, 'UTF-8'); ?></h4>
            <div class="track-subtitle"><?= htmlspecialchars($trackingHeaderSubtitle, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <button class="btn btn-primary fw-semibold" id="btnNuevaRuta">
            <i class="icon-base bx bx-plus icon-sm me-1"></i>Registrar ruta
        </button>
    </div>

    <!-- -- PestaÃƒÂ±as principales -- -->
    <div class="trk-section-grid" id="trkSectionGrid">
        <button type="button" class="trk-section-card active" data-section-target="#tabCreditos" data-section-load="creditos">
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
        <button type="button" class="trk-section-card" data-section-target="#tabBorradores" data-section-load="borradores">
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
        <button type="button" class="trk-section-card" data-section-target="#tabRutas" data-section-load="rutas">
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
        <button type="button" class="trk-section-card" data-section-target="#tabCatalogosTracking" data-section-load="catalogos">
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
    </div>

    <ul class="nav nav-tabs track-tabs mb-3 d-none" id="trackMainTabs">
        <li class="nav-item">
            <button class="nav-link active" id="tabCreditosBtn" data-bs-toggle="tab" data-bs-target="#tabCreditos">
                <i class="fa-solid fa-motorcycle me-1"></i>Pendientes de recoleccion
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
        <li class="nav-item">
            <button class="nav-link" id="tabCatalogosBtn" data-bs-toggle="tab" data-bs-target="#tabCatalogosTracking">
                <i class="fa-solid fa-building-user me-1"></i>CEDIS y transportistas
                <span id="badgeCatalogos" class="badge rounded-pill ms-1"
                      style="background:var(--track-color);font-size:.7rem;">0</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- == Tab: CrÃƒÂ©ditos disponibles == -->
        <div class="tab-pane fade show active" id="tabCreditos">

            <!-- Filtros -->
            <div class="track-filters">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Estado</label>
                        <select class="form-select form-select-sm trk-select-buscable" id="filtroEstado">
                            <option value="">Ã¢â‚¬â€ Todos los estados Ã¢â‚¬â€</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Municipio</label>
                        <select class="form-select form-select-sm trk-select-buscable" id="filtroMunicipio" disabled>
                            <option value="">Ã¢â‚¬â€ Todos los municipios Ã¢â‚¬â€</option>
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

            <!-- Tabla de crÃƒÂ©ditos -->
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
        <div class="tab-pane fade" id="tabBorradores">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5 col-lg-4 ms-auto">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                                <input type="search" class="form-control" id="trkBuscarBorradores"
                                       placeholder="Buscar borrador..." autocomplete="off">
                            </div>
                        </div>
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
        <div class="tab-pane fade" id="tabRutas">
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
                                    <!--
                                    <th>CrÃƒÂ©ditos</th>
                                    <th>AcciÃƒÂ³n</th>
                                    -->
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: CEDIS y transportistas -->
        <div class="tab-pane fade" id="tabCatalogosTracking">
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

            <div class="row g-3">
                <div class="col-12 col-xl-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-semibold py-2">
                            <i class="fa-solid fa-location-dot me-1" style="color:var(--track-color);"></i>CEDIS
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="tablaAgenciasTracking" class="table table-hover table-bordered mb-0 w-100" style="font-size:.8rem;">
                                    <thead>
                                        <tr>
                                            <th>CEDIS</th>
                                            <th>Ubicacion</th>
                                            <th>Contacto</th>
                                            <th>Horario</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-semibold py-2">
                            <i class="fa-solid fa-id-card-clip me-1" style="color:var(--track-color);"></i>Transportistas
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="tablaTransportistasTracking" class="table table-hover table-bordered mb-0 w-100" style="font-size:.8rem;">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <th>CEDIS / empresa</th>
                                            <th>Contacto</th>
                                            <th>Puesto</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /tab-content -->

</div><!-- /container-fluid -->

<!-- ==========================================================
     Modal Ã¢â‚¬â€ Registrar / editar ruta
========================================================== -->
<div class="modal fade" id="modalRegistrarRuta" tabindex="-1" aria-labelledby="modalRegistrarRutaLabel"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalRegistrarRutaLabel">
                    <i class="fa-solid fa-route me-2"></i>Registrar ruta de recolecciÃƒÂ³n
                </h5>
                <button type="button" class="btn-close" id="btnCerrarModal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger d-none py-2 px-3 mb-3" id="rutaCancelacionInfo"></div>

                <!-- -- SecciÃƒÂ³n 1: Datos de la ruta -- -->
                <div class="row g-3 mb-3" id="trkRouteHeaderSection">
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
                            MÃƒÂ­nimo <span id="rutaDiasMinimosTxt">2</span> dÃƒÂ­a(s) desde hoy - Deja una fecha tentativa si aÃƒÂºn no estÃƒÂ¡ definida para que puedas guardar correctamente el borrador de la ruta.
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
                </div>

                <div class="mb-2" id="secAgregarCredito">
                    <label class="form-label small fw-semibold">
                        Agregar crÃƒÂ©dito a la ruta
                    </label>
                    <!-- Filtros de ubicaciÃƒÂ³n para crÃƒÂ©ditos -->
                    <div class="row g-2 mb-2" id="crdFiltrosUbicacion">
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1 small">Estado</label>
                            <select class="form-select form-select-sm trk-select-buscable" id="crdFiltroEstado">
                                <option value="">Ã¢â‚¬â€ Todos los estados Ã¢â‚¬â€</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1 small">Municipio</label>
                            <select class="form-select form-select-sm trk-select-buscable" id="crdFiltroMunicipio" disabled>
                                <option value="">Ã¢â‚¬â€ Todos los municipios Ã¢â‚¬â€</option>
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

                <!-- -- Lista de crÃƒÂ©ditos en la ruta (sortable) -- -->
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
                            CrÃƒÂ©ditos en la ruta
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
                            AÃƒÂºn no hay crÃƒÂ©ditos en esta ruta
                        </div>
                    </div>
                </div>

                <!-- -- SecciÃƒÂ³n 3.5: Tracking en tiempo real -- -->
                <div id="trkTrackingSection" class="mb-3 d-none">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">
                            <i class="fa-solid fa-route me-1" style="color:var(--track-color);"></i>
                            Estado del recorrido
                        </span>
                        <span id="trkWsDot" title="Sin conexiÃƒÂ³n en tiempo real"
                              style="width:.55rem;height:.55rem;border-radius:50%;background:#cbd5e1;display:inline-block;"></span>
                    </div>
                    <!-- Barra de progreso -->
                    <div class="progress mb-1" style="height:5px;border-radius:999px;">
                        <div class="progress-bar" id="trkProgressBar"
                             style="width:0%;background:var(--track-color);transition:width .4s;"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span id="trkProgressText">Ã¢â‚¬â€ / Ã¢â‚¬â€ puntos</span>
                        <span id="trkPorcentaje">0%</span>
                    </div>
                    <!-- ÃƒÅ¡ltima ubicaciÃƒÂ³n del conductor -->
                    <div id="trkUltimaUbicacion" class="trk-location-pill d-none mb-2">
                        <i class="fa-solid fa-location-arrow" style="color:var(--track-color);"></i>
                        <span id="trkUbicacionText">Ã¢â‚¬â€</span>
                        <span class="text-muted" id="trkUbicacionTime"></span>
                    </div>
                    <!-- Timeline de paradas -->
                    <div class="trk-timeline" id="trkTimeline">
                        <div class="text-center text-muted py-2 small" id="trkTimelineEmpty">
                            <span class="spinner-border spinner-border-sm opacity-25" style="color:var(--track-color);"></span>
                        </div>
                    </div>
                </div>

                <!-- -- SecciÃƒÂ³n 4: Mapa de la ruta -- -->
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
                            <span>Agrega crÃƒÂ©ditos para visualizar la ruta</span>
                        </div>
                        <div id="trackMap" style="display:none;"></div>
                        <div id="trkLiveMapInfo" class="trk-live-map-card d-none">
                            <div class="live-title">
                                <i class="fa-solid fa-truck-fast"></i>
                                <span>Unidad en vivo</span>
                            </div>
                            <div class="live-meta">
                                <span id="trkLiveUpdated">Sin seÃƒÂ±al</span>
                                <span id="trkLiveSpeed">Vel. Ã¢â‚¬â€</span>
                                <span id="trkLiveAccuracy">Prec. Ã¢â‚¬â€</span>
                                <span id="trkLiveBattery">Bat. Ã¢â‚¬â€</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning py-1 px-2 mt-1 small d-none" id="mapAlertCoords">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Algunos crÃƒÂ©ditos no tienen coordenadas ni direcciÃƒÂ³n registrada.
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
                    <button type="button" class="btn btn-label-secondary btn-sm" id="btnGuardarBorrador">
                        <i class="icon-base bx bx-save icon-sm me-1"></i>Guardar borrador
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
     Modal Ã¢â‚¬â€ Detalle de ruta (solo lectura)
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
     Modal Ã¢â‚¬â€ Seleccionar ubicaciÃƒÂ³n en mapa (map picker)
========================================================== -->
<div class="modal fade" id="modalMapPicker" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:var(--track-color-dark);color:#fff;">
                <h6 class="modal-title mb-0">
                    <i class="fa-solid fa-map-pin me-2"></i>
                    Seleccionar ubicaciÃƒÂ³n en el mapa
                </h6>
                <button type="button" class="btn-close" id="btnCerrarMapPicker" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body p-2">
                <p class="small text-muted mb-2 px-1">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Haz clic en el mapa para colocar el pin de la ubicaciÃƒÂ³n del crÃƒÂ©dito
                    <strong id="mapPickerCreditoLabel"></strong>.
                </p>
                <div class="input-group input-group-sm mb-2" id="mapPickerSearchWrap">
                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--track-color);"></i>
                    </span>
                    <input type="text" class="form-control" id="mapPickerSearch"
                           placeholder="Buscar direcciÃƒÂ³n, colonia, municipio..." autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiarMapSearch" title="Limpiar bÃƒÂºsqueda">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="mapPickerContainer" style="width:100%;height:420px;border-radius:.5rem;overflow:hidden;border:1px solid var(--track-border);"></div>
                <div class="mt-2 px-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="small text-muted" id="mapPickerCoordsLabel">
                            <i class="fa-solid fa-crosshairs me-1"></i>Sin selecciÃƒÂ³n
                        </span>
                    </div>
                    <div id="mapPickerGeoInfo" class="small text-muted mt-1 d-none">
                        <i class="fa-solid fa-map-location-dot me-1" style="color:var(--track-color);"></i>
                        <span id="mapPickerEstadoMun">Ã¢â‚¬â€</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCancelarMapPicker">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-sm" id="btnConfirmarMapPicker"
                        style="background:var(--track-color);color:#fff;" disabled>
                    <i class="fa-solid fa-check me-1"></i>Confirmar ubicaciÃƒÂ³n
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Offcanvas Ã¢â‚¬â€ Chat Operativo (gestor / Sparta Ledger)
     Se abre desde el botÃƒÂ³n de chat en la tabla de rutas.
     Una pestaÃƒÂ±a por cada id_detalle (punto de recolecciÃƒÂ³n).
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
                            <span class="chat-connection-status" id="chatConnectionStatus">Sin conexiÃƒÂ³n registrada</span>
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
/* Chat Operativo Ã¢â‚¬â€ URL WebSocket (sin credenciales, solo el host) */
window._trackingChatWsBaseUrl   = <?= json_encode((string)($tracking_chat_ws_base_url ?? '')) ?>;
window._trackingApiBaseUrl      = <?= json_encode((string)($tracking_api_base_url ?? '')) ?>;
window._trackingChatGestorNombre = <?= json_encode(trim((string)($_SESSION['usuario_nombre'] ?? 'Gestor'))) ?>;
window._trackingDiasMinimosProgramacion = <?= json_encode((int)($tracking_dias_minimos_programacion ?? 2)) ?>;
window._trackingInitialSection = <?= json_encode((string)($tracking_initial_section ?? 'creditos')) ?>;
</script>
<!-- SortableJS (drag-and-drop sin jQuery UI) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
/* =======================================================
   tracking_recoleccion.js Ã¢â‚¬â€ lÃƒÂ³gica del mÃƒÂ³dulo
======================================================= */
'use strict';

// --- Estado local ---------------------------------------
const _trk = {
    creditosDisponibles:  [],   // todos los crÃƒÂ©ditos disponibles del servidor
    creditosEnRuta:       [],   // crÃƒÂ©ditos actualmente en el modal
    agenciasTracking:     [],
    transportistasTracking: [],
    rutasRegistradas:     [],
    rutasFiltro:          'todas',
    rutasVista:           'cards',
    rutasLayout:          'grid',
    rutasBusqueda:        '',
    rutasPagina:          1,
    rutasPorPagina:       18,
    borradoresBusqueda:   '',
    cargadoEstados:       false,
    cargadoCreditos:      false,
    cargadoCatalogos:     false,
    cargadoBorradores:    false,
    cargadoRutas:         false,
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
            title: 'Ã‚Â¿Salir sin guardar?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'SÃƒÂ­, salir',
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
    en_revision: '<span class="badge badge-conf-en_revision">En revisiÃƒÂ³n</span>',
};
const RUTA_LABEL = {
    borrador:               '<span class="badge badge-ruta-borrador">Borrador</span>',
    pendiente_confirmacion: '<span class="badge badge-ruta-pendiente_confirmacion">Pend. confirmaciÃƒÂ³n</span>',
    lista_envio:            '<span class="badge badge-ruta-lista_envio">Lista para enviar</span>',
    enviada:                '<span class="badge badge-ruta-enviada">Enviada</span>',
    en_proceso:             '<span class="badge badge-ruta-en_proceso">En proceso</span>',
    concluida:              '<span class="badge badge-ruta-concluida">Concluida</span>',
    cancelada:              '<span class="badge badge-ruta-cancelada">Cancelada</span>',
};

// --- InicializaciÃƒÂ³n -------------------------------------
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

document.addEventListener('DOMContentLoaded', function () {
    _trkInicializarFiltros();
    _trkInicializarSelectsBuscables();
    _trkInicializarTablaCreditosDT();
    _trkInicializarTablaRutasDT();
    _trkInicializarTablaBorradorDT();
    _trkInicializarTablasCatalogosDT();
    _trkInicializarRutasVista();
    _trkInicializarBusquedasRutas();
    _trkInicializarModal();

    // Observar cambios de clase en body para refrescar controles del mapa
    new MutationObserver(() => {
        if (!_trk.mapInstance) return;
        _trk.mapInstance.setOptions({ styles: [] });
        google.maps.event.trigger(_trk.mapInstance, 'resize');
        if (_trkPicker.mapInstance) {
            _trkPicker.mapInstance.setOptions({ styles: [] });
            google.maps.event.trigger(_trkPicker.mapInstance, 'resize');
        }
        if (_trk.chatMapInstance) {
            _trk.chatMapInstance.setOptions({ styles: document.body.classList.contains('dark-mode') ? _TRK_DARK_MAP_STYLES : [] });
            google.maps.event.trigger(_trk.chatMapInstance, 'resize');
        }
    }).observe(document.body, { attributeFilter: ['class'] });

    document.getElementById('tabRutasBtn').addEventListener('click', () => _trkCargarRutasSiHaceFalta());
    document.getElementById('tabBorradorBtn').addEventListener('click', () => _trkCargarBorradoresSiHaceFalta());
    document.getElementById('tabCatalogosBtn').addEventListener('click', () => _trkCargarCatalogosSiHaceFalta().then(() => _trkRenderCatalogosTracking()));
    document.getElementById('btnToggleChatMap')?.addEventListener('click', () => _trkToggleChatMapPanel());
    document.getElementById('trkSectionGrid')?.addEventListener('click', ev => {
        const btn = ev.target.closest('.trk-section-card');
        if (!btn) return;
        _trkActivarSeccion(btn.dataset.sectionTarget, btn.dataset.sectionLoad);
    });
    document.getElementById('btnNuevaRuta').addEventListener('click', () => _trkAbrirModalNuevo());
    const initialMap = {
        creditos: ['#tabCreditos', 'creditos'],
        borradores: ['#tabBorradores', 'borradores'],
        rutas: ['#tabRutas', 'rutas'],
        catalogos: ['#tabCatalogosTracking', 'catalogos'],
    };
    const initial = initialMap[window._trackingInitialSection] || initialMap.creditos;
    _trkActivarSeccion(initial[0], initial[1]);

    // ValidaciÃƒÂ³n estricta del input de minutos
    const $horaM = document.getElementById('rutaHoraM');
    $horaM.addEventListener('keydown', function (e) {
        // Permitir: backspace, delete, tab, escape, flechas, home, end
        const allowed = ['Backspace','Delete','Tab','Escape','ArrowLeft','ArrowRight','Home','End'];
        if (allowed.includes(e.key)) return;
        // Bloquear todo excepto dÃƒÂ­gitos 0-9
        if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
        }
    });
    $horaM.addEventListener('input', function () {
        // Eliminar cualquier carÃƒÂ¡cter que no sea dÃƒÂ­gito (copia/pega, etc.)
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
                    text: `"${n}" no es vÃƒÂ¡lido. Deben ser entre 00 y 59.`,
                    footer: 'Que gracioso...',
                    confirmButtonText: 'Aceptar',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Minutos incorrectos',
                    text: `"${n}" no es vÃƒÂ¡lido. Deben ser entre 00 y 59.`,
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

    // La seccion activa carga sus datos bajo demanda.
});

// --- Filtros ---------------------------------------------
function _trkInicializarFiltros() {
    $('#filtroEstado').on('change', function () {
        const est = $(this).val();
        const $mun = $('#filtroMunicipio');
        $mun.html('<option value="">Ã¢â‚¬â€ CargandoÃ¢â‚¬Â¦ Ã¢â‚¬â€</option>').prop('disabled', true);
        _trkRefrescarSelectBuscable('#filtroMunicipio');
        if (!est) {
            $mun.html('<option value="">Ã¢â‚¬â€ Todos los municipios Ã¢â‚¬â€</option>').prop('disabled', true);
            _trkRefrescarSelectBuscable('#filtroMunicipio');
            return;
        }
        trkFetch(`/TrackingRecoleccion/obtenerMunicipios?estado=${encodeURIComponent(est)}`)
            .then(r => {
                $mun.html('<option value="">Ã¢â‚¬â€ Todos los municipios Ã¢â‚¬â€</option>');
                (r.datos || []).forEach(m => {
                    $mun.append(`<option value="${m}">${m}</option>`);
                });
                $mun.prop('disabled', false);
                _trkRefrescarSelectBuscable('#filtroMunicipio');
            })
            .catch(() => {
                $mun.html('<option value="">Ã¢â‚¬â€ Error Ã¢â‚¬â€</option>');
                _trkRefrescarSelectBuscable('#filtroMunicipio');
            });
    });

    $('#btnFiltrarCreditos').on('click', function () {
        _trkRefrescarSelectBuscable('#filtroMunicipio');
        _trkCargarCreditosPaso2();
    });

    $('#btnLimpiarFiltros').on('click', function () {
        $('#filtroEstado').val('').trigger('change.select2');
        $('#filtroMunicipio').html('<option value="">Ã¢â‚¬â€ Todos los municipios Ã¢â‚¬â€</option>').prop('disabled', true);
        _trkRefrescarSelectBuscable('#filtroMunicipio');
        _trkCargarCreditosPaso2();
    });
}

function _trkCargarEstados() {
    if (_trk.cargadoEstados) return Promise.resolve();
    return trkFetch('/TrackingRecoleccion/obtenerEstados')
        .then(r => {
            const estados = r.datos || [];
            const $selFiltro = $('#filtroEstado');
            $selFiltro.find('option:not(:first)').remove();
            estados.forEach(e => $selFiltro.append(`<option value="${e}">${e}</option>`));
            _trkRefrescarSelectBuscable('#filtroEstado');
            _trk.cargadoEstados = true;
        });
}

// --- Tabla de crÃƒÂ©ditos ----------------------------------
function _trkRenderLocationBadges(estado, municipio) {
    const est = String(estado || '').trim();
    const mun = String(municipio || '').trim();
    if (!est && !mun) return 'Ã¢â‚¬â€';
    const parts = [];
    if (est) parts.push(`<span class="trk-loc-badge trk-loc-estado" title="Estado">${_trkChatEscapeHtml(est)}</span>`);
    if (mun) parts.push(`<span class="trk-loc-badge trk-loc-municipio" title="Municipio">${_trkChatEscapeHtml(mun)}</span>`);
    return `<span class="trk-location-badges">${parts.join('')}</span>`;
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
            <div class="trk-borrador-sub">VIN/BIN: ${_trkChatEscapeHtml(r?.bin || 'No disponible')}</div>
            <div class="trk-borrador-muted">${_trkChatEscapeHtml(estatus)}</div>
        </div>
    </div>`;
}

function _trkInicializarTablaCreditosDT() {
    _trk.tablaCreditosDT = $('#tablaCreditos').DataTable({
        language: {
            emptyTable:  'No hay crÃƒÂ©ditos disponibles',
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
            // TODO (pendiente autorizaciÃƒÂ³n): descomentar para filtrar solo crÃƒÂ©ditos listos para ruta
            // _trk.creditosDisponibles = _trkFiltrarListosParaRuta(_trk.creditosDisponibles);
            if (_trk.tablaCreditosDT) {
                _trk.tablaCreditosDT.clear().rows.add(_trk.creditosDisponibles).draw();
            }
            _trkPoblarFiltroEstadosCrd();
            _trkRefrescarSelectCreditos();
            _trk.cargadoCreditos = true;
            _trkSetBadge('badgeCreditos', _trk.creditosDisponibles.length);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar crÃƒÂ©ditos.', confirmButtonText: 'Aceptar' }));
}

// --- Filtro: solo crÃƒÂ©ditos con estatus "Cierre Documentados" -------------
// Pendiente de autorizaciÃƒÂ³n Ã¢â‚¬â€ para activar: descomentar la lÃƒÂ­nea en _trkCargarCreditosPaso2
// Una vez activo, el estatus se mostrarÃƒÂ¡ en tabla como "Listo para ruta" en lugar de "Cierre Documentados"
// function _trkFiltrarListosParaRuta(creditos) {
//     return creditos
//         .filter(c => c.estatus_proceso === 'Cierre Documentados')
//         .map(c => ({ ...c, estatus_proceso: 'Listo para ruta' }));
// }

// --- Tabla de rutas -------------------------------------
function _trkRenderUbicacionRuta(raw) {
    if (!raw) return 'Ã¢â‚¬â€';
    const map = new Map();
    raw.split('@@').forEach(p => {
        const sep  = p.indexOf('|||');
        const est  = sep >= 0 ? p.slice(0, sep).trim()  : '';
        const mun  = sep >= 0 ? p.slice(sep + 3).trim() : '';
        if (!est) return;
        if (!map.has(est)) map.set(est, new Set());
        if (mun && mun !== '|') map.get(est).add(mun);
    });
    if (!map.size) return 'Ã¢â‚¬â€';
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
    if (!r || !r.nombre_transportista) return 'Ã¢â‚¬â€';
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

function _trkCargarCreditosSiHaceFalta() {
    return _trk.cargadoCreditos ? Promise.resolve() : _trkCargarCreditosPaso2();
}

function _trkRutaCancelable(estatus) {
    return !['borrador', 'concluida', 'cancelada'].includes(String(estatus || ''));
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
    if (loadKey === 'creditos') _trkCargarCreditosInicialSiHaceFalta();
    if (loadKey === 'borradores') _trkCargarBorradoresSiHaceFalta();
    if (loadKey === 'rutas') _trkCargarRutasSiHaceFalta();
    if (loadKey === 'catalogos') _trkCargarCatalogosSiHaceFalta().then(() => _trkRenderCatalogosTracking());
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
            Rutas ${desde}-${hasta} de ${total} Ã‚Â· PÃƒÂ¡gina ${page} de ${totalPaginas}
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
            <div class="trk-ruta-title">#${id} ${_trkChatEscapeHtml(r.nombre_ruta || 'Ruta sin nombre')}</div>
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
        dom: 'lrtip',
        pageLength: 20,
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
            { data: 'fecha_programada_fmt', defaultContent: 'Ã¢â‚¬â€' },
            {
                data: null,
                title: 'Hora',
                render: r => {
                    const hi  = r.hora_inicial;
                    const ha1 = r.act_hora_1;
                    if (!hi && !ha1) return 'Ã¢â‚¬â€';
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
                    html += `<span class="badge bg-secondary trk-cred-badge"${ttAttr} style="cursor:default;white-space:nowrap;">${total} crÃƒÂ©dito${total !== 1 ? 's' : ''}</span>`;
                    if (conf > 0) html += `<small class="text-success fw-semibold" style="white-space:nowrap;">${conf} confirmado${conf !== 1 ? 's' : ''}</small>`;
                    if (pend > 0) html += `<small class="text-warning fw-semibold" style="white-space:nowrap;">${pend} pendiente${pend !== 1 ? 's' : ''}</small>`;
                    if (rech > 0) html += `<small class="text-danger  fw-semibold" style="white-space:nowrap;">${rech} rechazado${rech !== 1 ? 's' : ''}</small>`;
                    html += '</div>';
                    return html;
                },
            },
            {
                data: null,
                defaultContent: 'Ã¢â‚¬â€',
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
    const nombre = r?.nombre_ruta || 'Ruta sin nombre';
    const ubicacion = _trkRenderUbicacionRuta(r?.ubicaciones_lista);
    const estatusBadge = RUTA_LABEL[estatus] || `<span class="badge bg-secondary">${_trkChatEscapeHtml(estatus || 'Sin estatus')}</span>`;
    const pct = _trkRutaPorcentaje(r);
    const pctBadge = pct > 0 ? `<span class="badge bg-light text-dark border" style="font-size:.68rem;">${pct}%</span>` : '';
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-info">RUTA-${_trkChatEscapeHtml(id)}</span>
        <div class="trk-borrador-main">#${_trkChatEscapeHtml(id)} ${_trkChatEscapeHtml(nombre)}</div>
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
            info.nombre,
            info.tipo,
            info.agencia,
            info.direccion,
        ].filter(Boolean).join(' ');
    }
    return `<div class="trk-borrador-cell">
        <div class="trk-borrador-main"><i class="fa-solid fa-calendar-day me-1"></i>${_trkChatEscapeHtml(r?.fecha_programada_fmt || 'Sin fecha')}</div>
        <div class="d-flex flex-wrap align-items-center gap-1">${_trkRenderHoraBorrador(r)}</div>
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
        dom: 'lrtip',
        pageLength: 20,
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

function _trkCargarRutas() {
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
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar rutas.', confirmButtonText: 'Aceptar' }));
}

function _trkCargarRutasSiHaceFalta() {
    return _trk.cargadoRutas ? Promise.resolve() : _trkCargarRutas();
}

async function _trkCancelarRuta(idRuta, nombreRuta = '') {
    if (!idRuta) return;
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Cancelar ruta',
        html: `<div class="text-start small text-muted mb-2">Ruta: <b>${_trkChatEscapeHtml(nombreRuta || '#' + idRuta)}</b></div>`,
        input: 'textarea',
        inputLabel: 'Motivo de cancelaciÃƒÂ³n',
        inputPlaceholder: 'Describe el motivo...',
        inputAttributes: {
            maxlength: 200,
            rows: 4,
        },
        showCancelButton: true,
        confirmButtonText: 'SÃƒÂ­, cancelar ruta',
        cancelButtonText: 'Regresar',
        confirmButtonColor: '#ef4444',
        inputValidator: value => {
            const motivo = String(value || '').trim();
            if (!motivo) return 'El motivo de cancelaciÃƒÂ³n es obligatorio.';
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
            Swal.fire({ icon: 'success', title: 'Ruta cancelada', text: r.message || 'La ruta se cancelÃƒÂ³ correctamente.', timer: 1800, showConfirmButton: false });
            _trkCargarRutas();
            _trkCargarCreditosPaso2();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || r.message || 'No se pudo cancelar la ruta.', confirmButtonText: 'Aceptar' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexiÃƒÂ³n', text: 'No se pudo cancelar la ruta.', confirmButtonText: 'Aceptar' });
    }
}

function _trkStripHtml(html) {
    return String(html || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function _trkHoraBorradorTexto(r) {
    const hi = r?.hora_inicial || '';
    const ha1 = r?.act_hora_1 || '';
    if (!hi && !ha1) return 'Sin hora';
    if (ha1) return `${_trkFormatHora(ha1)} (original ${_trkFormatHora(hi)})`;
    return _trkFormatHora(hi);
}

function _trkRenderHoraBorrador(r) {
    const hi = r?.hora_inicial || '';
    const ha1 = r?.act_hora_1 || '';
    if (!hi && !ha1) return '<span class="trk-borrador-muted">Sin hora</span>';
    if (ha1) {
        return `<span class="trk-borrador-chip trk-borrador-chip-warning">${_trkFormatHora(ha1)}</span>
                <span class="trk-borrador-muted text-decoration-line-through">Original ${_trkFormatHora(hi)}</span>`;
    }
    return `<span class="trk-borrador-chip trk-borrador-chip-info">${_trkFormatHora(hi)}</span>`;
}

function _trkBorradorTextoBusqueda(r) {
    return [
        r?.id_ruta,
        r?.nombre_ruta,
        _trkStripHtml(_trkRenderUbicacionRuta(r?.ubicaciones_lista)),
        r?.fecha_programada_fmt,
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
    const nombre = r?.nombre_ruta || 'Ruta sin nombre';
    const ubicacion = _trkRenderUbicacionRuta(r?.ubicaciones_lista);
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-warning">BOR-${_trkChatEscapeHtml(id)}</span>
        <div class="trk-borrador-main">#${_trkChatEscapeHtml(id)} ${_trkChatEscapeHtml(nombre)}</div>
        <div class="trk-borrador-sub"><i class="fa-solid fa-location-dot me-1"></i>${ubicacion}</div>
    </div>`;
}

function _trkRenderBorradorPlaneacion(r, type) {
    if (type !== 'display') {
        return [
            r?.fecha_programada_fmt,
            _trkHoraBorradorTexto(r),
            r?.nombre_transportista,
            r?.nombre_agencia,
            r?.transportista_empresa,
        ].filter(Boolean).join(' ');
    }
    const transportista = _trkRenderTransportistaRuta(r);
    return `<div class="trk-borrador-cell">
        <div class="trk-borrador-main"><i class="fa-solid fa-calendar-day me-1"></i>${_trkChatEscapeHtml(r?.fecha_programada_fmt || 'Sin fecha')}</div>
        <div class="d-flex flex-wrap align-items-center gap-1">${_trkRenderHoraBorrador(r)}</div>
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
        dom: 'lrtip',
        pageLength: 20,
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
                render: r => `<button class="btn btn-icon btn-sm rounded-pill btn-label-warning trk-action-btn btn-editar-borrador"
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
                _trk.tablaRutasBorradorDT.clear().rows.add(borradores).search(_trk.borradoresBusqueda || '').draw();
            }
            // Actualizar contador en la pestaÃƒÂ±a
            _trk.cargadoBorradores = true;
            _trkSetBadge('badgeBorradores', borradores.length);
        })
        .catch(() => {});
}

// --- Carga inicial de todos los datos en paralelo ---------
function _trkCargarBorradoresSiHaceFalta() {
    return _trk.cargadoBorradores ? Promise.resolve() : _trkCargarBorradores();
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
                    <small class="text-muted">${_trkChatEscapeHtml([a.telefono, a.email].filter(Boolean).join(' Ã‚Â· '))}</small>`,
            },
            { data: 'horario', defaultContent: '-', render: v => _trkChatEscapeHtml(v || '-') },
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
            { data: 'puesto', defaultContent: '-', render: v => _trkChatEscapeHtml(v || '-') },
        ],
    });
}

function _trkCargarCatalogoAgenciasTransportistas() {
    return Promise.all([
        trkFetch('/TrackingRecoleccion/obtenerCatalogoAgenciasTransportistas').catch(() => ({ success: false, datos: {} })),
        trkFetch('/TrackingRecoleccion/obtenerCedisTracking').catch(() => ({ success: false, datos: {} })),
    ]).then(([catalogoResp, cedisResp]) => {
            const datos = catalogoResp.datos || {};
            const cedisApi = _trkExtraerCedisTracking(cedisResp);
            const cedisLocal = _trkFiltrarCedisActivos(datos.agencias || []);
            _trk.agenciasTracking = cedisApi.length ? cedisApi : cedisLocal;
            _trk.transportistasTracking = datos.transportistas || [];
            _trkPoblarAgenciasTrackingSelect();
            _trkRefrescarSelectTransportistas();
            _trk.cargadoCatalogos = true;
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

function _trkCargarCatalogosSiHaceFalta() {
    return _trk.cargadoCatalogos
        ? Promise.resolve()
        : _trkCargarCatalogoAgenciasTransportistas().catch(() => {});
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
        const label = a.nombre_agencia || '';
        $sel.append(`<option value="${a.id_agencia}">${_trkChatEscapeHtml(label)}</option>`);
    });
    cedisDestino.forEach(a => {
        const label = a.nombre_agencia || '';
        $dest.append(`<option value="${a.id_agencia}">${_trkChatEscapeHtml(label)}</option>`);
    });
    if (selected) $sel.val(selected);
    if (selectedDest && cedisDestino.some(a => String(a.id_agencia) === String(selectedDest))) {
        $dest.val(selectedDest);
    }
    _trkRenderCedisDestinoInfo();
}

function _trkRenderCatalogosTracking() {
    const agencias = _trk.agenciasTracking || [];
    const transportistas = _trk.transportistasTracking || [];
    const internos = transportistas.filter(t => t.tipo_transportista === 'interno').length;
    const externos = transportistas.filter(t => t.tipo_transportista === 'externo').length;

    $('#statAgenciasTracking').text(agencias.length);
    $('#statTransportistasInternos').text(internos);
    $('#statTransportistasExternos').text(externos);
    $('#statCatalogoTotal').text(agencias.length + transportistas.length);
    _trkSetBadge('badgeCatalogos', agencias.length + transportistas.length);

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

function _trkTransportistasFiltrados() {
    return (_trk.transportistasTracking || []).filter(t => Number(t.activo ?? 1) === 1);
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
    const empresa = t.nombre_agencia || t.empresa_origen || '';
    return `${t.nombre_transportista || ''}${empresa ? ' - ' + empresa : ''}`;
}

function _trkTransportistaPorId(id) {
    if (!id) return null;
    return (_trk.transportistasTracking || []).find(t => String(t.id_transportista) === String(id)) || null;
}

function _trkTemplateTransportistaSelect2(item) {
    if (!item.id) return _trkChatEscapeHtml(item.text || '');
    const t = _trkTransportistaPorId(item.id);
    if (!t) return _trkChatEscapeHtml(item.text || '');
    const empresa = t.nombre_agencia || t.empresa_origen || 'Sin CEDIS';
    const contacto = [t.telefono, t.email].filter(Boolean).join(' Ã‚Â· ');
    return `<div class="d-flex align-items-center gap-2 flex-wrap">
        ${_trkTipoTransportistaBadge(t.tipo_transportista)}
        <span class="fw-semibold">${_trkChatEscapeHtml(t.nombre_transportista || '')}</span>
        <span class="text-muted">${_trkChatEscapeHtml(empresa)}</span>
        ${contacto ? `<span class="text-muted">${_trkChatEscapeHtml(contacto)}</span>` : ''}
    </div>`;
}

function _trkTemplateTransportistaSeleccionado(item) {
    if (!item.id) return _trkChatEscapeHtml(item.text || '');
    const t = _trkTransportistaPorId(item.id);
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
    return (_trk.transportistasTracking || []).find(t => String(t.id_transportista) === String(id)) || null;
}

function _trkSincronizarTransportistaSeleccionado() {
    const t = _trkTransportistaSeleccionado();
    $('#rutaTipoTransportista').val(t?.tipo_transportista || '');
    $('#rutaAgenciaTracking').val(t?.id_agencia || '');
    _trkPoblarAgenciasTrackingSelect();
    _trkActualizarBadgeTransportista();
    _trkRenderCedisDestinoInfo();
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

function _trkEsZonaInterna(estado, municipio) {
    const est = _trkNormTxt(estado);
    const mun = _trkNormTxt(municipio);
    const estadosOk = ['CDMX', 'CIUDAD DE MEXICO', 'DISTRITO FEDERAL', 'ESTADO DE MEXICO', 'EDOMEX', 'MEXICO'];
    const municipiosOk = [
        'MIGUEL HIDALGO', 'CUAUHTEMOC', 'BENITO JUAREZ', 'ALVARO OBREGON', 'AZCAPOTZALCO',
        'COYOACAN', 'IZTAPALAPA', 'IZTACALCO', 'GUSTAVO A MADERO', 'VENUSTIANO CARRANZA',
        'TLALNEPANTLA', 'TLALNEPANTLA DE BAZ', 'CUAUTITLAN IZCALLI', 'CUAUTITLAN',
        'TULTITLAN', 'NAUCALPAN', 'NAUCALPAN DE JUAREZ', 'ATIZAPAN DE ZARAGOZA',
        'NEZAHUALCOYOTL', 'NEZA', 'ECATEPEC', 'ECATEPEC DE MORELOS', 'COACALCO',
        'COACALCO DE BERRIOZABAL', 'NICOLAS ROMERO', 'CHIMALHUACAN', 'LOS REYES',
        'LA PAZ', 'TECÃƒÂMAC', 'TECAMAC', 'HUIXQUILUCAN', 'CHALCO', 'VALLE DE CHALCO'
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
        return;
    }
    const agencia = t.nombre_agencia || t.empresa_origen || 'Sin CEDIS asignado';
    const contacto = [t.telefono, t.email].filter(Boolean).join(' Ã‚Â· ') || 'Sin contacto';
    $box.removeClass('d-none').html(`
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted">${_trkChatEscapeHtml(agencia)}</span>
            <span class="text-muted">${_trkChatEscapeHtml(contacto)}</span>
        </div>
    `);
}

function _trkRenderCedisDestinoInfo() {
    const cedis = _trkCedisDestinoSeleccionado();
    const $box = $('#rutaCedisDestinoInfo');
    if (!cedis) {
        $box.addClass('d-none').empty();
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
            </div>
            ${mapsBtn}
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
    ]);
}

function _trkPrepararModalRutaDetalle(soloLectura = false) {
    return Promise.all([
        _trkCargarCatalogosSiHaceFalta(),
        soloLectura ? Promise.resolve() : _trkCargarCreditosInicialSiHaceFalta(),
    ]);
}

function _trkCargarTodo() {
    const t0 = performance.now();
    Swal.fire({
        title: 'Obteniendo datos...',
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando informaciÃƒÂ³n del mÃƒÂ³dulo</span>',
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

// --- Modal Ã¢â‚¬â€ apertura ------------------------------------
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
    // Fecha mÃƒÂ­nima
    const minDate = _trkFechaMinimaProgramacion();
    document.getElementById('rutaFecha').min = minDate;
    $('#rutaDiasMinimosTxt').text(_trkDiasMinimosProgramacion());

    $('#rutaNombre').on('input', _trkMarcarCambio);

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
    $(document).on('mousedown', function (e) {
        if (!$(e.target).closest('#rutaTransportistaPicker').length) {
            $('#rutaTransportistaResults').addClass('d-none');
        }
    });
    $('#rutaCedisDestino').on('change', function () {
        _trkRenderCedisDestinoInfo();
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

    // Filtro de crÃƒÂ©ditos por estado
    $('#crdFiltroEstado').on('change', function () {
        const est = $(this).val();
        _trkPoblarFiltroMunicipiosCrd(est);
        _trkRefrescarSelectCreditos();
    });

    // Filtro de crÃƒÂ©ditos por municipio
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
    $('#btnGuardarBorrador').on('click', () => _trkGuardarRuta('borrador'));
    $('#btnEnviarRuta').on('click', () => _trkGuardarRuta('enviar'));
    $('#btnActualizarRuta').on('click', async () => {
        const ok = await Swal.fire({
            icon: 'question',
            title: 'Ã‚Â¿Guardar cambios?',
            text: 'Se actualizarÃƒÂ¡n los datos de esta ruta.',
            showCancelButton: true,
            confirmButtonText: 'SÃƒÂ­, actualizar',
            cancelButtonText: 'No, regresar',
            confirmButtonColor: '#0d6efd',
        });
        if (ok.isConfirmed) _trkGuardarRuta('actualizar');
    });

    // Cerrar con aviso
    const _closeFn = async () => {
        if (!_trk.soloLectura && _trk.haychangios && _trkPuedeAutosaveBorrador()) {
            _trkCancelarAutosaveProgramado();
            _trkSetAutosaveStatus('Guardando borrador...', 'muted');
            const okAuto = await _trkGuardarBorradorAutomatico();
            if (okAuto) {
                _trk.autosaveLastHash = _trkAutosaveHash();
                _trkSetAutosaveStatus('Borrador guardado', 'success');
            }
        }
        if (!_trk.soloLectura && _trk.haychangios) {
            const ok = await trkConfirm('Tienes cambios sin guardar. Ã‚Â¿Deseas salir sin guardar?');
            if (!ok) return;
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
    // Pre-seleccionar estado/municipio del crÃƒÂ©dito en los filtros
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
    $('#trkListaFiltroEstado, #trkListaFiltroMunicipio, #trkListaBuscar').val('');
    $('#trkListaFiltroMunicipio').prop('disabled', true);
    _trkRefrescarSelectTransportistas();
    // Reset filtros de crÃƒÂ©ditos
    $('#crdFiltroEstado').val('').trigger('change.select2');
    $('#crdFiltroMunicipio').html('<option value="">Ã¢â‚¬â€ Todos los municipios Ã¢â‚¬â€</option>').prop('disabled', true);
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
            AÃƒÂºn no hay crÃƒÂ©ditos en esta ruta
        </div>`;
    $('#rutaCreditosCount').text(0);
    _trkRefrescarSelectCreditos();
    _trkOcultarMapa();
    $('#mapAlertCoords').addClass('d-none');
    document.getElementById('modalRegistrarRutaLabel').innerHTML =
        '<i class="fa-solid fa-route me-2"></i>Registrar ruta de recolecciÃƒÂ³n';
}

// --- CrÃƒÂ©ditos en el modal --------------------------------
function _trkPoblarFiltroEstadosCrd() {
    const estadoActual = $('#crdFiltroEstado').val();
    const estados = [...new Set(
        _trk.creditosDisponibles.map(c => c.estado).filter(Boolean)
    )].sort();
    const $est = $('#crdFiltroEstado');
    $est.html('<option value="">Ã¢â‚¬â€ Todos los estados Ã¢â‚¬â€</option>');
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

function _trkCreditosPorEstado(estado) {
    if (!estado) return [];
    return _trk.creditosDisponibles.filter(c => _trkMismaUbicacion(c.estado, estado));
}

function _trkCreditosPorMunicipio(estado, municipio) {
    if (!estado || !municipio) return [];
    return _trk.creditosDisponibles.filter(c =>
        _trkMismaUbicacion(c.estado, estado) && _trkMismaUbicacion(c.municipio, municipio)
    );
}

function _trkEstadoCreditosAgotado(estado) {
    if (!estado) return false;
    const ids = _trkIdsCreditosEnRutaSet();
    const creditosEstado = _trkCreditosPorEstado(estado);
    return creditosEstado.length > 0 && creditosEstado.every(c => ids.has(String(c.id_credito)));
}

function _trkTotalCreditosPorEstado(estado) {
    if (!estado) return 0;
    return _trkCreditosPorEstado(estado).length;
}

function _trkTotalCreditosPorMunicipio(estado, municipio) {
    if (!estado || !municipio) return 0;
    return _trkCreditosPorMunicipio(estado, municipio).length;
}

function _trkMunicipioCreditosAgotado(estado, municipio) {
    if (!estado || !municipio) return false;
    const ids = _trkIdsCreditosEnRutaSet();
    const creditosMunicipio = _trkCreditosPorMunicipio(estado, municipio);
    return creditosMunicipio.length > 0 && creditosMunicipio.every(c => ids.has(String(c.id_credito)));
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
    const municipios = [...new Set(
        _trk.creditosDisponibles
            .filter(c => _trkMismaUbicacion(c.estado, estado) && c.municipio)
            .map(c => c.municipio)
    )].sort();
    municipios.forEach(m => {
        const total = _trkTotalCreditosPorMunicipio(estado, m);
        const agotado = _trkMunicipioCreditosAgotado(estado, m);
        const texto = agotado ? `${m} - (${total}) (TODOS SELECCIONADOS EN EL MAPA)` : `${m} - (${total})`;
        $mun.append($('<option>', { value: m, text: texto, disabled: agotado }));
    });
    if (municipioActual && municipios.some(m => _trkMismaUbicacion(m, municipioActual)) && !_trkMunicipioCreditosAgotado(estado, municipioActual)) {
        $mun.val(municipioActual);
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
        if (estFiltro && !_trkMismaUbicacion(c.estado, estFiltro)) return;
        if (munFiltro && !_trkMismaUbicacion(c.municipio, munFiltro)) return;
        const modelo = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const label  = `#${c.id_credito} Ã‚Â· ${modelo || '(sin modelo)'} Ã‚Â· ${c.bin || 'Ã¢â‚¬â€'}`;
        $sel.append(`<option value="${c.id_credito}">${label}</option>`);
    });
    _trkRefrescarSelectBuscable('#rutaCreditoSelect');
}

function _trkAgregarCreditoALista(cred) {
    // RN-03: no duplicados
    if (_trk.creditosEnRuta.find(c => String(c.id_credito) === String(cred.id_credito))) {
        Swal.fire({ icon: 'warning', title: 'Aviso', text: 'Este crÃƒÂ©dito ya estÃƒÂ¡ en la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
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

    if (isEmpty) {
        $list.html(`<div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
            AÃƒÂºn no hay crÃƒÂ©ditos en esta ruta
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
        const modelo    = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ') || 'Ã¢â‚¬â€';
        const badgeConf = CONF_LABEL[c.estatus_confirmacion_gestor] || CONF_LABEL['pendiente'];
        const tienePin  = c.latitud_manual && c.longitud_manual;
        const pinClass  = tienePin ? 'btn-pin-ubicacion tiene-pin' : 'btn-pin-ubicacion';
        const etaInfo   = _trkEstadoEta(c, c.estatus_recoleccion);
        const pinTitle  = tienePin ? 'UbicaciÃƒÂ³n manual asignada (clic para cambiar)' : 'Asignar ubicaciÃƒÂ³n en mapa';

        // Los crÃƒÂ©ditos en ruta nunca se bloquean
        // En rutas canceladas queda como consulta: solo se permite enfocar el crÃƒÂ©dito en el mapa.

        // Elementos que sÃƒÂ³lo aparecen en modo ediciÃƒÂ³n
        const dragHandle  = filaLectura ? '' : '<i class="fa-solid fa-grip-vertical drag-handle"></i>';
        const confControl = filaLectura
            ? badgeConf
            : `<select class="form-select form-select-sm py-0 ms-1 select-conf-gestor"
                    style="max-width:130px;font-size:.75rem;"
                    data-id="${c.id_credito}">
                <option value="pendiente"   ${c.estatus_confirmacion_gestor === 'pendiente'   ? 'selected' : ''}>Pendiente</option>
                <option value="confirmado"  ${c.estatus_confirmacion_gestor === 'confirmado'  ? 'selected' : ''}>Confirmado</option>
                <option value="rechazado"   ${c.estatus_confirmacion_gestor === 'rechazado'   ? 'selected' : ''}>Rechazado</option>
                <option value="en_revision" ${c.estatus_confirmacion_gestor === 'en_revision' ? 'selected' : ''}>En revisiÃƒÂ³n</option>
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
                    <span class="text-muted fw-semibold" style="font-size:.7rem;white-space:nowrap;">ETA:</span>
                    ${etaInfo.html}
                    <span class="badge bg-light text-dark border">${_trkChatEscapeHtml(c.fecha_eta || 'Sin fecha')}</span>
                    ${(c.hora_eta_ini && c.hora_eta_fin)
                        ? `<span class="badge bg-light text-dark border">${_trkFormatHora(c.hora_eta_ini)} - ${_trkFormatHora(c.hora_eta_fin)}</span>`
                        : '<span class="badge bg-light text-dark border">Sin horario</span>'}
                </div>`
            : null;

        const html = `
        <div class="track-credito-row" data-id="${c.id_credito}" title="Clic para ubicar este crÃƒÂ©dito en el mapa">
            ${dragHandle}
            <span class="orden-num">${idx + 1}</span>
            <div class="d-flex flex-column gap-0 flex-grow-1" style="min-width:0;">
                <span class="fw-semibold text-truncate">#${c.id_credito} Ã¢â‚¬â€ ${c.nombre_cliente || 'Ã¢â‚¬â€'}</span>
                <span class="text-muted" style="font-size:.75rem;">
                    <span class="trk-moto-model-pill"><i class="fa-solid fa-motorcycle" title="Modelo de motocicleta"></i>${_trkChatEscapeHtml(modelo || '-')}</span>
                    &middot; BIN: ${_trkChatEscapeHtml(c.bin || '-')}
                    &nbsp;|&nbsp;${_trkRenderLocationBadges(c.estado, c.municipio)}
                </span>
                ${filaLectura ? etaLectura : `<div class="eta-row d-flex align-items-center gap-1 mt-1 flex-wrap">
                    <span class="text-muted fw-semibold" style="font-size:.7rem;white-space:nowrap;">ETA:</span>
                    ${etaInfo.html}
                    <input type="date" class="form-control eta-fecha" data-id="${c.id_credito}" value="${c.fecha_eta || ''}" min="${_trkFechaRutaBase()}" style="max-width:130px;" title="Fecha estimada de llegada">
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="ini" style="width:62px;flex-shrink:0;" title="Hora inicio">${optsIni}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="ini" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaIni.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos inicio">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="ini" style="width:62px;flex-shrink:0;" title="AM/PM inicio">
                        <option value="AM"${etaIni.ampm === 'AM' ? ' selected' : ''}>AM</option>
                        <option value="PM"${etaIni.ampm === 'PM' ? ' selected' : ''}>PM</option>
                    </select>
                    <span class="text-muted" style="font-size:.7rem;line-height:1;">Ã¢â‚¬â€œ</span>
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="Hora fin (mÃƒÂ­nimo 4 horas despuÃƒÂ©s)">${optsFin}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="fin" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaFin.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos fin (mÃƒÂ­nimo 4 horas despuÃƒÂ©s)">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="AM/PM fin (mÃƒÂ­nimo 4 horas despuÃƒÂ©s)">
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

    // Eventos de crÃƒÂ©ditos (siempre activos, incluso en modo ver ruta)
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
                        title: 'Fecha ETA invÃƒÂ¡lida',
                        text: 'La ETA no puede ser anterior a la fecha de salida de la ruta.',
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
                        text: `"${n}" no es vÃƒÂ¡lido. Deben ser entre 00 y 59.`,
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
    // Actualizar numeraciÃƒÂ³n visual
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
    // Verificar si alguno tiene coordenadas o direcciÃƒÂ³n
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
        // Cargar Google Maps API dinÃƒÂ¡micamente
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
        // El script ya estÃƒÂ¡ cargando (desde el picker); esperar
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
    const soloPin      = creditos.filter(c =>
        c.estatus_confirmacion_gestor === 'pendiente' ||
        c.estatus_confirmacion_gestor === 'en_revision'
    );
    // rechazados -> completamente omitidos del mapa

    // -- Icono SVG numerado (confirmados) ---------------------
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

    // -- Icono por estatus (no confirmados) -------------------
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

    // -- Resolver coordenadas de un crÃƒÂ©dito -------------------
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
            const m = new google.maps.Marker({
                map,
                position: pos,
                icon:  svgIcon(idx + 1),
                title: `#${c.id_credito} \u2014 ${c.nombre_cliente || ''}`,
                zIndex: 10 + idx,
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

        // -- Trazar polilÃƒÂ­nea de ruta (solo confirmados) ------
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
            provideRouteAlternatives: false,
        }, (result, status) => {
            if (status === 'OK') {
                _trk.directionsRenderer.setDirections(result);
                const legs = result.routes?.[0]?.legs || [];
                _trk.routeLegDurations = legs.map(l => l.duration?.value || null);
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
    const addr = [cred.direccion, cred.municipio, cred.estado, 'MÃƒÂ©xico'].filter(Boolean).join(', ');
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
            Swal.fire({ icon: 'info', title: 'Sin ubicaciÃƒÂ³n', text: 'Este crÃƒÂ©dito todavÃƒÂ­a no tiene una ubicaciÃƒÂ³n vÃƒÂ¡lida para mostrar en el mapa.', confirmButtonText: 'Aceptar' });
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
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no estÃƒÂ¡ disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!_trk.mapInstance || typeof google === 'undefined' || !google.maps) {
        if ((opts.retry || 0) >= 4) {
            Swal.fire({ icon: 'info', title: 'Mapa cargando', text: 'No se pudo enfocar el crÃƒÂ©dito todavÃƒÂ­a. Intenta de nuevo en unos segundos.', confirmButtonText: 'Aceptar' });
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
    geocoder:          null,
    autocomplete:      null,
    searchDebounce:    null,
    listenersBound:    false,
};

function _trkAbrirMapPicker(cred) {
    if (!window._trackGoogleMapsKey) {
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no estÃƒÂ¡ disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }

    _trkPicker.creditoId         = cred.id_credito;
    _trkPicker.selectedLat        = null;
    _trkPicker.selectedLng        = null;
    _trkPicker.selectedEstado     = null;
    _trkPicker.selectedMunicipio  = null;

    // Etiqueta en el modal
    document.getElementById('mapPickerCreditoLabel').textContent =
        ` Ã¢â‚¬â€ #${cred.id_credito} ${cred.nombre_cliente ? '(' + cred.nombre_cliente + ')' : ''}`;
    document.getElementById('mapPickerCoordsLabel').innerHTML =
        '<i class="fa-solid fa-crosshairs me-1"></i>Sin selecciÃƒÂ³n';
    document.getElementById('mapPickerGeoInfo').classList.add('d-none');
    document.getElementById('btnConfirmarMapPicker').disabled = true;
    document.getElementById('mapPickerSearch').value = '';

    // Mostrar modal
    if (!_trkPicker.modal) {
        _trkPicker.modal = new bootstrap.Modal(document.getElementById('modalMapPicker'));
        document.getElementById('btnCerrarMapPicker').addEventListener('click',  () => _trkPicker.modal.hide());
        document.getElementById('btnCancelarMapPicker').addEventListener('click', () => _trkPicker.modal.hide());
        document.getElementById('btnConfirmarMapPicker').addEventListener('click', _trkConfirmarMapPicker);
    }
    // Oscurecer el backdrop cuando el map picker estÃƒÂ© sobre otro modal
    document.getElementById('modalMapPicker').addEventListener('shown.bs.modal', () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length >= 2) backdrops[backdrops.length - 1].style.opacity = '0.65';
    }, { once: true });
    _trkPicker.modal.show();

    // Inicializar mapa despuÃƒÂ©s de que el modal sea visible (necesario para que el div tenga dimensiones)
    document.getElementById('modalMapPicker').addEventListener('shown.bs.modal', _trkInicializarMapPicker, { once: true });
}

function _trkInicializarMapPicker() {
    const cred = _trk.creditosEnRuta.find(c => String(c.id_credito) === String(_trkPicker.creditoId));
    if (!cred) return;

    // Centro: coordenadas manuales existentes > coords del crÃƒÂ©dito > GDL
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
                        title: 'UbicaciÃƒÂ³n seleccionada',
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
                            title: 'UbicaciÃƒÂ³n seleccionada',
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

        // Si ya tenÃƒÂ­a coords manuales, mostrar marcador previo
        if (cred.latitud_manual && cred.longitud_manual) {
            const prevPos = { lat: parseFloat(cred.latitud_manual), lng: parseFloat(cred.longitud_manual) };
            _trkPicker.marker = new google.maps.Marker({
                map: _trkPicker.mapInstance,
                position: prevPos,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'UbicaciÃƒÂ³n guardada',
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
        }

        google.maps.event.trigger(_trkPicker.mapInstance, 'resize');
    };

    if (typeof google !== 'undefined' && google.maps) {
        initMap();
    } else if (_trk.mapLoaded) {
        // El script ya estÃƒÂ¡ cargando (desde el mapa de ruta); esperar a que estÃƒÂ© listo
        const waitForMaps = setInterval(() => {
            if (typeof google !== 'undefined' && google.maps) {
                clearInterval(waitForMaps);
                initMap();
            }
        }, 150);
        setTimeout(() => clearInterval(waitForMaps), 10000);
    } else {
        // Cargar Maps si aÃƒÂºn no estÃƒÂ¡
        const script = document.createElement('script');
        script.src   = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry,places&callback=_trkMapPickerReady`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        window._trkMapPickerReady = initMap;
        _trk.mapLoaded = true;
    }
}

function _trkPickerCrearMarker(latLng, title = 'UbicaciÃƒÂ³n seleccionada') {
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
    _trkPicker.mapInstance.panTo(latLng);
    _trkPicker.mapInstance.setZoom(16);
    _trkPickerCrearMarker(latLng, addressText || 'UbicaciÃƒÂ³n seleccionada');
    if (components) _trkPickerExtraerGeo(components);
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
        address: `${query}, MÃƒÂ©xico`,
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
            document.getElementById('mapPickerGeoInfo').classList.add('d-none');
            document.getElementById('mapPickerCoordsLabel').innerHTML =
                '<i class="fa-solid fa-crosshairs me-1"></i>Sin selecciÃƒÂ³n';
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

function _trkPickerExtraerGeo(components) {
    if (!components) return;
    let estado = '', municipio = '';
    components.forEach(c => {
        if (c.types.includes('administrative_area_level_1')) estado    = c.long_name;
        if (c.types.includes('locality'))                    municipio = c.long_name;
        if (!municipio && c.types.includes('sublocality_level_1'))        municipio = c.long_name;
        if (!municipio && c.types.includes('administrative_area_level_2')) municipio = c.long_name;
    });
    _trkPicker.selectedEstado    = estado    || null;
    _trkPicker.selectedMunicipio = municipio || null;
    const geoDiv  = document.getElementById('mapPickerGeoInfo');
    const geoSpan = document.getElementById('mapPickerEstadoMun');
    if (estado || municipio) {
        geoSpan.textContent = [municipio, estado].filter(Boolean).join(', ');
        geoDiv.classList.remove('d-none');
    } else {
        geoDiv.classList.add('d-none');
    }
}

function _trkPickerReverseGeocode(latLng) {
    if (!window.google || !google.maps) return;
    if (!_trkPicker.geocoder) _trkPicker.geocoder = new google.maps.Geocoder();
    _trkPicker.geocoder.geocode({ location: latLng }, (results, status) => {
        if (status === 'OK' && results && results[0]) {
            _trkPickerExtraerGeo(results[0].address_components);
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
        // Sobrescribir tambiÃƒÂ©n las props que usa el mapa de ruta
        cred.latitud  = lat;
        cred.longitud = lng;
        // Aplicar estado/municipio detectados por geocodificaciÃƒÂ³n
        if (_trkPicker.selectedEstado)    cred.estado    = _trkPicker.selectedEstado;
        if (_trkPicker.selectedMunicipio) cred.municipio = _trkPicker.selectedMunicipio;
        _trkMarcarCambio();
    }

    _trkPicker.modal.hide();
    _trkRenderListaCreditos();
    _trkRenderizarMapa();
}

// --- Guardar ruta ----------------------------------------
// --- Helpers de hora AM/PM ------------------------------
function _trkFormatHora(horaStr) {
    if (!horaStr) return 'Ã¢â‚¬â€';
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
        return { key: 'sin_eta', label: 'Sin ETA', html: '<span class="badge bg-secondary-subtle text-secondary border">Sin ETA</span>' };
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
    let etaIni = salida + 30; // colchÃƒÂ³n operativo para carga/salida hacia primer punto
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
        title: 'Ruta no ÃƒÂ³ptima',
        text: 'Tu ruta no es la mas optima para el recorrido, implicarian mayor inversion de recursos, estas seguro de que quieres enviarla asi?',
        showCancelButton: true,
        confirmButtonText: 'SÃƒÂ­, enviarla asÃƒÂ­',
        cancelButtonText: 'Revisar ruta',
        confirmButtonColor: '#0d9488',
    });
    return r.isConfirmed;
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

function _trkAutosaveHash() {
    return JSON.stringify({
        id_ruta: _trk.idRutaEditando || 0,
        nombre: $('#rutaNombre').val().trim(),
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
    if (!$('#rutaNombre').val().trim()) return false;
    if (!_trk.creditosEnRuta.length) return false;
    if (!$('#rutaFecha').val()) return false;
    if (!$('#rutaTransportistaTracking').val()) return false;
    if (!_trkValidarReglasTransportista().ok) return false;
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
        _trkSetAutosaveStatus('Autoguardado pendiente', 'warning');
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

function _trkGuardarRutaError(mensaje, opts = {}) {
    if (opts.silent) return false;
    Swal.fire({ icon: 'warning', title: 'Campo requerido', text: mensaje, confirmButtonText: 'Aceptar' });
    return false;
}

async function _trkGuardarBorradorAutomatico() {
    if (!_trkPuedeAutosaveBorrador()) return false;

    const nombre = $('#rutaNombre').val().trim();
    const fecha = $('#rutaFecha').val();
    const idTransportista = $('#rutaTransportistaTracking').val();
    const transportistaSel = _trkTransportistaSeleccionado();
    const tipoTransportista = transportistaSel?.tipo_transportista || '';
    const idAgenciaTracking = transportistaSel?.id_agencia || '';
    const idCedisDestino = $('#rutaCedisDestino').val();
    const geoRuta = _trkGeoResumenRuta();
    const estado = $('#crdFiltroEstado').val() || geoRuta.estado;
    const municipio = $('#crdFiltroMunicipio').val() || geoRuta.municipio;

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
            estado: c.estado || '',
            municipio: c.municipio || '',
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

    $('#btnGuardarBorrador').prop('disabled', true);
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
    } finally {
        $('#btnGuardarBorrador').prop('disabled', false);
    }
}

async function _trkGuardarRuta(modo, opts = {}) {
    const silent = !!opts.silent;
    if (!silent) _trkCancelarAutosaveProgramado();
    const nombre    = $('#rutaNombre').val().trim();
    const fecha     = $('#rutaFecha').val();
    const idTransportista   = $('#rutaTransportistaTracking').val();
    const transportistaSel  = _trkTransportistaSeleccionado();
    const tipoTransportista = transportistaSel?.tipo_transportista || '';
    const idAgenciaTracking = transportistaSel?.id_agencia || '';
    const idCedisDestino    = $('#rutaCedisDestino').val();

    const geoRuta = _trkGeoResumenRuta();
    const estado    = $('#crdFiltroEstado').val()    || geoRuta.estado;
    const municipio = $('#crdFiltroMunicipio').val() || geoRuta.municipio;

    if (!nombre) {
        if (!silent) document.getElementById('rutaNombre').focus();
        return _trkGuardarRutaError('El nombre de la ruta es obligatorio.', opts);
    }
    if (_trk.creditosEnRuta.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debe agregar al menos un crÃƒÂ©dito a la ruta.', confirmButtonText: 'Aceptar' });
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
            title: 'ETA invÃƒÂ¡lida',
            text: `La ETA del crÃƒÂ©dito #${etaInvalida.id_credito} no puede ser anterior a la fecha de salida de la ruta.`,
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

    if (modo !== 'borrador' && modo !== 'actualizar') {
        if (!tipoTransportista || !idTransportista) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Selecciona transportista antes de enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
        const noConfirmados = _trk.creditosEnRuta.filter(c => c.estatus_confirmacion_gestor !== 'confirmado');
        if (noConfirmados.length > 0) {
            Swal.fire({ icon: 'warning', title: 'Pendiente', text: 'Todos los crÃƒÂ©ditos deben tener confirmaciÃƒÂ³n del gestor para enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
    }

    _trk.creditosEnRuta.forEach(c => _trkAsegurarEtaMinima(c));
    const continuarRuta = await _trkConfirmarRutaNoOptimaSiAplica(modo);
    if (!continuarRuta) return;

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
            _trk.autosaveLastHash = _trkAutosaveHash();
            Swal.fire({ icon: 'success', title: 'Ã‚Â¡Listo!', text: modo === 'borrador' ? 'Borrador guardado correctamente.' : 'Ruta enviada correctamente.', timer: 2000, showConfirmButton: false });
            bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRuta'))?.hide();
            _trkCargarCreditosPaso2();
            _trkCargarBorradores();
            _trkCargarRutas();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || r.message || 'Error al guardar la ruta.', confirmButtonText: 'Aceptar' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexiÃƒÂ³n', text: 'Error de conexiÃƒÂ³n al guardar.', confirmButtonText: 'Aceptar' }))
    .finally(() => $btnGuardar.prop('disabled', false));
}

// --- Ver detalle de ruta ---------------------------------
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
            const infoTransportista = _trkTransportistaRutaData(d);
            const transportista = infoTransportista.nombre
                ? `${infoTransportista.nombre} (${infoTransportista.tipo || 'No disponible'})`
                : 'Sin transportista';
            const agenciaTracking = infoTransportista.agencia || infoTransportista.direccion || 'Sin CEDIS';
            const cedisDestino = d.cedis_destino_nombre || d.cedis_destino?.nombre_agencia || 'Sin destino asignado';
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
                rowsHtml += `<tr>
                    <td class="text-center">${det.orden_ruta || (i + 1)}</td>
                    <td>${det.id_credito || 'Ã¢â‚¬â€'}</td>
                    <td>${det.nombre_cliente || 'Ã¢â‚¬â€'}</td>
                    <td>${det.modelo || 'Ã¢â‚¬â€'}</td>
                    <td>${det.bin || 'Ã¢â‚¬â€'}</td>
                    <td>${_trkRenderLocationBadges(det.estado, det.municipio)}</td>
                    <td>${det.estatus_proceso || 'Ã¢â‚¬â€'}</td>
                    <td>${CONF_LABEL[det.estatus_confirmacion_gestor] || det.estatus_confirmacion_gestor || 'Ã¢â‚¬â€'}</td>
                </tr>`;
            });
            $body.html(`
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">Nombre</b>${d.nombre_ruta}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Estado</b>${_trkRenderLocationBadges(d.estado, null)}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Municipio</b>${_trkRenderLocationBadges(null, d.municipio)}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Fecha programada</b>${d.fecha_programada_fmt || d.fecha_programada || 'Ã¢â‚¬â€'}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Hora de salida</b>${
                        d.act_hora_1
                            ? `<span class="badge bg-warning text-dark me-1" title="Hora actualizada">${_trkFormatHora(d.act_hora_1)}</span><s class="text-muted small">${_trkFormatHora(d.hora_inicial)}</s>`
                            : (d.hora_inicial ? `<span class="badge bg-light text-dark border">${_trkFormatHora(d.hora_inicial)}</span>` : 'Ã¢â‚¬â€')
                    }</div>
                    <div class="col-6 col-md-1"><b class="d-block small text-muted">Estatus</b>${estatusBadge}</div>
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">Transportista</b><span class="small">${_trkChatEscapeHtml(transportista)}</span></div>
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">CEDIS / empresa</b><span class="small">${_trkChatEscapeHtml(agenciaTracking)}</span></div>
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">Destino transportista</b><span class="small">${_trkChatEscapeHtml(cedisDestino)}</span></div>
                    ${motivoCancelacion}
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size:.8rem;">
                        <thead style="background:var(--track-color);color:#fff;">
                            <tr>
                                <th>#</th><th>CrÃƒÂ©dito</th><th>Cliente</th><th>Modelo</th>
                                <th>BIN</th><th>Estado / Municipio</th>
                                <th>Proceso</th><th>ConfirmaciÃƒÂ³n</th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml || '<tr><td colspan="8" class="text-center text-muted">Sin crÃƒÂ©ditos</td></tr>'}</tbody>
                    </table>
                </div>
            `);
        })
        .catch(() => $body.html('<div class="alert alert-danger">Error de conexiÃƒÂ³n.</div>'));
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
    // Ocultar secciÃƒÂ³n de agregar nuevos crÃƒÂ©ditos (no aÃƒÂ±adir, solo editar existentes)
    $('#secAgregarCredito').hide();
    $('#reorderHint').hide();
    // Swap de botones: ocultar borrador/enviar, mostrar actualizar (gris hasta que haya cambios)
    $('#btnGuardarBorrador, #btnEnviarRuta').hide();
    $('#btnActualizarRuta').show().prop('disabled', true)
        .removeClass('btn-primary')
        .addClass('btn-label-secondary')
        .css({ cursor: 'not-allowed' });
    // Badge en tÃƒÂ­tulo
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

    // Actualizar tÃƒÂ­tulo mientras carga
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

            // TÃƒÂ­tulo final con nombre de la ruta
            document.getElementById('modalRegistrarRutaLabel').innerHTML =
                `<i class="fa-solid fa-${icon} me-2"></i>${soloLectura ? 'Ver ruta' : 'Editar ruta'}: <em>${d.nombre_ruta || ''}</em>`;

            // Campos bÃƒÂ¡sicos
            $('#rutaNombre').val(d.nombre_ruta || '');
            $('#rutaFecha').val(d.fecha_programada || '');
            $('#rutaTipoTransportista').val(d.tipo_transportista || '');
            _trkPoblarAgenciasTrackingSelect();
            $('#rutaAgenciaTracking').val(d.id_agencia_tracking ? String(d.id_agencia_tracking) : '');
            const idCedisDestinoRuta = d.id_cedis_destino || d.cedis_destino?.id_agencia || '';
            $('#rutaCedisDestino').val(idCedisDestinoRuta ? String(idCedisDestinoRuta) : '');
            _trkRefrescarSelectTransportistas(d.id_transportista || null);
            $('#rutaCedisDestino').val(idCedisDestinoRuta ? String(idCedisDestinoRuta) : '');
            _trkRenderCedisDestinoInfo();

            // CrÃƒÂ©ditos (cargar directamente en array)
            _trk.creditosEnRuta = (d.detalle || []).map((det, i) => ({
                id_detalle:                  det.id_detalle || null,
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
                eta_manual:                  !!(det.fecha_eta || det.hora_eta_ini || det.hora_eta_fin),
                eta_auto:                    false,
            }));
            _trk.estatusRuta  = d.estatus_ruta || null;
            _trk.rutaCancelada = String(d.estatus_ruta || '') === 'cancelada';
            _trkRenderListaCreditos();
            _trkRefrescarSelectCreditos();
            _trkRenderizarMapa();

            // Estado + Municipio via filtros de crÃƒÂ©ditos
            $('#crdFiltroEstado').val(d.estado || '').trigger('change');
            if (d.municipio) {
                $('#crdFiltroMunicipio').val(d.municipio).trigger('change.select2');
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

            // Iniciar tracking en tiempo real si la ruta estÃƒÂ¡ activa o completada
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
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexiÃƒÂ³n.', confirmButtonText: 'Aceptar' });
            modal.hide();
        });
}

function _trkCreditosRutaFiltrados() {
    const estado = _trkNormTxt($('#trkListaFiltroEstado').val());
    const municipio = _trkNormTxt($('#trkListaFiltroMunicipio').val());
    const q = _trkNormTxt($('#trkListaBuscar').val());
    return (_trk.creditosEnRuta || []).filter(c => {
        if (estado && _trkNormTxt(c.estado) !== estado) return false;
        if (municipio && _trkNormTxt(c.municipio) !== municipio) return false;
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
    const estados = [...new Set((_trk.creditosEnRuta || []).map(c => String(c.estado || '').trim()).filter(Boolean))]
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
        .filter(c => !estado || _trkNormTxt(c.estado) === estado)
        .map(c => String(c.municipio || '').trim())
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
    const estados = new Set(creditos.map(c => _trkNormTxt(c.estado || 'SIN ESTADO')));
    const municipios = new Set(creditos.map(c => `${_trkNormTxt(c.estado || 'SIN ESTADO')}|${_trkNormTxt(c.municipio || 'SIN MUNICIPIO')}`));
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
        const estado = String(c.estado || 'SIN ESTADO').trim() || 'SIN ESTADO';
        const municipio = String(c.municipio || 'SIN MUNICIPIO').trim() || 'SIN MUNICIPIO';
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
        const okEstado = _trkNormTxt(c.estado || 'SIN ESTADO') === estadoNorm;
        const okMunicipio = !municipioNorm || _trkNormTxt(c.municipio || 'SIN MUNICIPIO') === municipioNorm;
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
        .map(c => String(c.estado || '').trim())
        .filter(Boolean))];
    const municipios = [...new Set((_trk.creditosEnRuta || [])
        .map(c => String(c.municipio || '').trim())
        .filter(Boolean))];
    return {
        estado: estados.length > 1 ? 'MULTIPLES ESTADOS' : (estados[0] || ''),
        municipio: municipios.length > 1 ? 'MULTIPLES MUNICIPIOS' : (municipios[0] || ''),
    };
}

/* ============================================================
   TRACKING EN TIEMPO REAL Ã¢â‚¬â€ estilo Mercado Libre
   Muestra el estado del recorrido (paradas, progreso, ubicaciÃƒÂ³n)
   cuando la ruta estÃƒÂ¡ en_proceso o completado.
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
    estado:       null,   // ÃƒÂºltimo estado recibido del API
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

// --- Cargar estado vÃƒÂ­a REST -------------------------------
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

    // Timeline de crÃƒÂ©ditos
    const creditos = ruta.creditos || [];
    const puntoAct = ruta.punto_actual;
    if (!creditos.length) {
        document.getElementById('trkTimeline').innerHTML =
            `<div class="text-center text-muted py-2 small">Sin puntos de recolecciÃƒÂ³n registrados.</div>`;
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
            ? `${_trkFormatHora(cEta.hora_eta_ini)} - ${_trkFormatHora(cEta.hora_eta_fin)}`
            : '';

        // LÃƒÂ­nea 1: CrÃƒÂ©dito #XXXXX Ã‚Â· MARCA MODELO | Estado, Municipio
        const moto    = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const lugar   = [c.estado, c.municipio].filter(Boolean).join(', ');
        let linea1    = `CrÃƒÂ©dito #${c.id_credito}`;
        if (moto)  linea1 += ` Ã‚Â· ${moto}`;
        if (lugar) linea1 += ` | ${lugar}`;

        // LÃƒÂ­nea 2: nombre del cliente
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
                ${etaTxt ? `<span class="trk-step-dir">ETA ${_trkChatEscapeHtml(etaTxt)} Ã‚Â· ${_trkChatEscapeHtml(etaInfo.label)}</span>` : ''}
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

// --- Actualizar ÃƒÂºltima ubicaciÃƒÂ³n del conductor ------------
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
    if (!ts || isNaN(ts.getTime())) return 'Sin conexiÃƒÂ³n registrada';
    const diffMs = Math.max(0, Date.now() - ts.getTime());
    const min = Math.floor(diffMs / 60000);
    if (min < 1) return 'ÃƒÅ¡ltima conexiÃƒÂ³n hace menos de 1 min';
    if (min < 60) return `ÃƒÅ¡ltima conexiÃƒÂ³n hace ${min} min`;
    const horas = Math.floor(min / 60);
    if (horas < 24) return `ÃƒÅ¡ltima conexiÃƒÂ³n hace ${horas} hora${horas === 1 ? '' : 's'}`;
    return `ÃƒÅ¡ltima conexiÃƒÂ³n ${ts.toLocaleDateString('es-MX', {
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
    document.getElementById('trkLiveSpeed').textContent = ubi.speed !== null && !isNaN(ubi.speed) ? `Vel. ${Math.round(ubi.speed)} km/h` : 'Vel. Ã¢â‚¬â€';
    document.getElementById('trkLiveAccuracy').textContent = ubi.accuracy !== null && !isNaN(ubi.accuracy) ? `Prec. ${Math.round(ubi.accuracy)} m` : 'Prec. Ã¢â‚¬â€';
    document.getElementById('trkLiveBattery').textContent = ubi.battery !== null && !isNaN(ubi.battery) ? `Bat. ${ubi.battery}%` : 'Bat. Ã¢â‚¬â€';
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
        dot.title = conectado ? 'En tiempo real' : 'Sin conexiÃƒÂ³n en tiempo real';
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
                title: ev.code === 4001 ? 'SesiÃƒÂ³n expirada' : 'Sin acceso',
                text: ev.reason || (ev.code === 4001 ? 'Recarga la pÃƒÂ¡gina para renovar el tracking en vivo.' : 'No tienes permiso para visualizar esta ruta en vivo.'),
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

    ws.onerror = () => { /* onclose dispararÃƒÂ¡ */ };
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
        case 'tracking.event':
            // Recargar estado completo ante cualquier evento de tracking
            _trkRTCargarEstado();
            break;
        case 'error': {
            const code = String(data.code || data.codigo || '');
            if (code === '4001') {
                _trkRT.liveCfg = null;
                _trkRT.wsRetries = 5;
                _trkRTActualizarWsDot(false);
                Swal.fire({ icon: 'warning', title: 'SesiÃƒÂ³n expirada', text: 'Recarga la pÃƒÂ¡gina para renovar el tracking en vivo.', confirmButtonText: 'Aceptar' });
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
   CHAT OPERATIVO Ã¢â‚¬â€ gestor (Sparta Ledger)
   Flujo:
     1. Usuario hace clic en btn-abrir-chat de tablaRutas.
     2. Se obtiene el detalle de la ruta para listar id_detalle.
     3. Se abre el offcanvas con una pestaÃƒÂ±a por id_detalle.
     4. Al activar una pestaÃƒÂ±a, se carga info del chat por REST.
     5. Si el chat estÃƒÂ¡ activo, se conecta WebSocket (solo lectura).
     6. Mensajes se envÃƒÂ­an siempre por REST.
============================================================ */

// --- Estado global del Chat ------------------------------
const _trkChat = {
    rutaId:    null,   // id_ruta abierto actualmente
    activeTab: null,   // id_detalle de la pestaÃƒÂ±a activa
    jwtToken:  null,   // JWT en memoria JS (sÃƒÂ³lo para WS)
    jwtExpiry: 0,      // timestamp ms de expiraciÃƒÂ³n
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
            }));
            _trkChatAbrir(idRuta, r.datos.nombre_ruta || `Ruta #${idRuta}`, detalle);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexiÃƒÂ³n.', confirmButtonText: 'Aceptar' }));
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
            ws: null, wsRetries: 0, wsRetryTimeout: null, wsPingInterval: null,
            unread: 0, loadingMsgs: false, allLoaded: false, oldestMsgId: null,
            typingTimeout: null, typingStopTimeout: null, lastTypingSent: 0,
        };

        // Tab ---------------------------------------------
        const li = document.createElement('li');
        li.className = 'nav-item';
        const credLabel = det.id_credito ? ` Ã‚Â· ${det.id_credito}` : '';
        li.innerHTML = `
            <button class="chat-tab-link" id="chatTabBtn_${id}" data-detalle="${id}" type="button"
                    title="${_trkChatEscapeHtml(det.nombre_cliente)}">
                <span>#${id}${credLabel}</span>
                <span class="chat-status-badge chat-status-desconocido" id="chatStatusBadge_${id}">Ã¢â‚¬Â¦</span>
                <span class="chat-unread-badge d-none" id="chatUnreadBadge_${id}"></span>
            </button>`;
        list.appendChild(li);
        li.querySelector('button').addEventListener('click', () => _trkChatActivarTab(id));

        // Pane --------------------------------------------
        const pane = document.createElement('div');
        pane.className = 'chat-pane';
        pane.id        = `chatPane_${id}`;
        pane.innerHTML = `
            <div class="chat-status-notice d-none" id="chatNotice_${id}"></div>
            <div class="chat-messages-wrap" id="chatMsgsWrap_${id}"></div>
            <div class="chat-typing-indicator d-none" id="chatTyping_${id}">
                <span>Escribiendo</span>
                <span class="chat-typing-dots"><span></span><span></span><span></span></span>
            </div>
            <button class="chat-new-msg-btn d-none" id="chatNewMsgBtn_${id}"
                    type="button">Nuevo mensaje Ã¢â€ â€œ</button>
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
                              placeholder="Escribe un mensajeÃ¢â‚¬Â¦" rows="2"
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

    // Activar primera pestaÃƒÂ±a ------------------------------
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

// --- GestiÃƒÂ³n de pestaÃƒÂ±as ---------------------------------
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
        _trkChatMostrarError(idDetalle, 'Error de conexiÃƒÂ³n al cargar el chat.');
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
            Sin mensajes aÃƒÂºn</div>`;
        return;
    }

    let html = state.allLoaded
        ? `<div class="text-center text-muted py-2" style="font-size:.7rem;">Ã¢â‚¬â€ Inicio de la conversaciÃƒÂ³n Ã¢â‚¬â€</div>`
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
        <span class="chat-bubble-meta">${actor} Ã‚Â· ${hora}</span>
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
            <small>${_trkChatEscapeHtml([ext, size].filter(Boolean).join(' Ã‚Â· '))}</small>
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
            // Si WS no estÃƒÂ¡ activo, agregar localmente para feedback inmediato
            if ((!state.ws || state.ws.readyState !== WebSocket.OPEN) && r.mensaje) {
                _trkChatAgregarMensaje(idDetalle, r.mensaje);
            }
            // Si WS activo, el evento message.new lo agregarÃƒÂ¡ (evita duplicados)
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
        // Heartbeat cada 30s para mantener la conexiÃƒÂ³n activa en Cloud Run
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

        // CÃƒÂ³digos de cierre definitivo (no reintentar)
        if (evt.code === 4001) { // token invÃƒÂ¡lido/expirado
            _trkChat.jwtToken = null;
            _trkChatMostrarNotice(idDetalle, 'SesiÃƒÂ³n expirada. Recarga la pÃƒÂ¡gina.', 'cerrado');
            return;
        }
        if (evt.code === 4003) { // sin acceso
            _trkChatMostrarNotice(idDetalle, 'Sin acceso a este chat.', 'cerrado');
            return;
        }

        // Reintento con back-off exponencial (mÃƒÂ¡x. 5 intentos)
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
                'Sin conexiÃƒÂ³n en tiempo real Ã¢â‚¬â€ los mensajes se actualizan al enviar.',
                'cerrado'
            );
        }
    };
    ws.onerror = () => { /* ws.onclose dispararÃƒÂ¡ a continuaciÃƒÂ³n */ };
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
            _trkChatMostrarNotice(idDetalle, 'La ruta ha iniciado Ã¢â‚¬â€ ya puedes enviar mensajes.', 'activo', 5000);
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

// --- Actualizar UI segÃƒÂºn estatus -------------------------
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
        _trkChatMostrarNotice(idDetalle, ' El chat aÃƒÂºn no estÃƒÂ¡ disponible Ã¢â‚¬â€ la ruta no ha iniciado.', 'bloqueado');
        if (textarea) textarea.disabled = true;
        if (sendBtn)  sendBtn.disabled  = true;
        attachBtns.forEach(btn => { btn.disabled = true; });
    } else if (state.estatus === 'cerrado') {
        _trkChatMostrarNotice(idDetalle, 'Esta conversaciÃƒÂ³n ha sido cerrada.', 'cerrado');
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

// --- Badges no leÃƒÂ­dos ------------------------------------
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
    const input = document.getElementById(`chatFileInput_${idDetalle}`);
    if (!input) return;
    input.value = '';
    input.accept = accept;
    input.dataset.tipo = tipo;
    input.click();
}

async function _trkChatPrepararArchivo(idDetalle, file, input) {
    if (!file) return;
    if (file.size > 100 * 1024 * 1024) {
        Swal.fire({ icon: 'warning', title: 'Archivo muy grande', text: 'El archivo no puede superar 100 MB.', confirmButtonText: 'Aceptar' });
        if (input) input.value = '';
        return;
    }
    const tipo = _trkChatTipoArchivo(file);
    const preview = await _trkChatPreviewArchivo(file, tipo);
    const res = await Swal.fire({
        title: 'Enviar evidencia',
        html: preview,
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
                <div class="small text-muted mt-2">${safeName} ${size ? 'Ã‚Â· ' + size : ''}</div>
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
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexiÃƒÂ³n al subir el archivo.', confirmButtonText: 'Aceptar' });
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
        text: `El botÃƒÂ³n de ${labels[tipo] || 'archivo'} ya estÃƒÂ¡ preparado para id_detalle ${idDetalle}. Falta enlazar el endpoint de adjuntos del servicio de tracking.`,
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

function _trkChatFechaLocal(iso) {
    if (!iso) return '';
    try {
        const s = iso.endsWith('Z') || /[+\-]\d{2}:\d{2}$/.test(iso) ? iso : iso + 'Z';
        return new Date(s).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch { return iso; }
}
</script>
