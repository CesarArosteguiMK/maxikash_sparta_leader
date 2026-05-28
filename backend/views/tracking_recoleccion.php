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
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: .85rem;
}
.trk-ruta-card {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    background: #fff;
    box-shadow: 0 .1rem .55rem rgba(15,23,42,.05);
    overflow: hidden;
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
@media (max-width: 767.98px) {
    .trk-rutas-toolbar { align-items: stretch; flex-direction: column; }
    .trk-rutas-summary { gap: .4rem; }
    .trk-rutas-filter { flex: 1 1 auto; justify-content: center; }
    .trk-rutas-grid { grid-template-columns: 1fr; }
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
/* ── Badges estatus tracking API (servicio externo) ── */
.badge-trk-pendiente  { background: #94a3b8; color: #fff; }
.badge-trk-en_proceso { background: #0d9488; color: #fff; }
.badge-trk-completado { background: #16a34a; color: #fff; }
.badge-trk-confirmado { background: #0284c7; color: #fff; }
.badge-trk-cancelado  { background: #ef4444; color: #fff; }

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

/* ── Botón pin ubicación en fila de crédito ── */
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

/* ════════════════════════════════════════════════════════
   Chat Operativo — Offcanvas lateral
════════════════════════════════════════════════════════ */
#modalChatOperativo .modal-dialog {
    max-width: 920px;
}
#modalChatOperativo .modal-content {
    height: min(760px, calc(100vh - 2rem));
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
    max-width: 390px;
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
.chat-tabs-wrap ul { flex-wrap: nowrap; padding: .55rem .65rem; gap: .35rem; border-bottom: none; }
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
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
}

/* Google Places suggestions must float above Bootstrap modals */
.pac-container {
    z-index: 20000 !important;
}
</style>

<div class="container-fluid py-3 px-3 px-md-4">

    <!-- ── Cabecera ── -->
    <div class="track-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4><i class="fa-solid fa-route me-2"></i>Tracking Recolección — Motos Adjudicadas</h4>
            <div class="track-subtitle">Créditos disponibles para planeación de ruta física</div>
        </div>
        <button class="btn btn-primary fw-semibold" id="btnNuevaRuta">
            <i class="icon-base bx bx-plus icon-sm me-1"></i>Registrar ruta
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
        <li class="nav-item">
            <button class="nav-link" id="tabCatalogosBtn" data-bs-toggle="tab" data-bs-target="#tabCatalogosTracking">
                <i class="fa-solid fa-building-user me-1"></i>CEDIS y transportistas
                <span id="badgeCatalogos" class="badge rounded-pill ms-1"
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
                        <select class="form-select form-select-sm trk-select-buscable" id="filtroEstado">
                            <option value="">— Todos los estados —</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label mb-1 small fw-semibold">Municipio</label>
                        <select class="form-select form-select-sm trk-select-buscable" id="filtroMunicipio" disabled>
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
                                    <?php /* <th>Acción</th> */ ?>
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
                                    <th>Transportista</th>
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
                <div class="trk-rutas-toolbar">
                    <div>
                        <div class="fw-semibold" style="font-size:.92rem;">Rutas registradas</div>
                        <div class="text-muted small">Seguimiento operativo por estatus, transportista y avance.</div>
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
                        <button type="button" class="btn btn-sm btn-label-primary active" id="trkVistaCards" title="Vista tarjetas">
                            <i class="fa-solid fa-grip"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-label-secondary" id="trkVistaTabla" title="Vista tabla">
                            <i class="fa-solid fa-table-list"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="trkRutasCardsWrap" class="trk-rutas-board">
                        <div id="trkRutasCards" class="trk-rutas-grid"></div>
                    </div>
                    <div class="table-responsive" id="trkRutasTablaWrap" style="display:none;">
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
                                    <th>Transportista</th>
                                    <th>Acción</th>
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
                <div class="alert alert-danger d-none py-2 px-3 mb-3" id="rutaCancelacionInfo"></div>

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
                            Mínimo <span id="rutaDiasMinimosTxt">2</span> día(s) desde hoy - Deja una fecha tentativa si aún no está definida para que puedas guardar correctamente el borrador de la ruta.
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
                    </label>
                    <div class="row g-2 align-items-end">
                        <input type="hidden" id="rutaTipoTransportista" value="">
                        <input type="hidden" id="rutaAgenciaTracking" value="">
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                <label class="form-label mb-0 small">Transportista</label>
                                <span id="rutaTransportistaTipoBadge" class="d-none"></span>
                            </div>
                            <input type="hidden" id="rutaTransportistaTracking" value="">
                            <div class="trk-transport-picker" id="rutaTransportistaPicker">
                                <input type="text" class="form-control form-control-sm" id="rutaTransportistaSearch"
                                       placeholder="Buscar transportista..." autocomplete="off" disabled>
                                <div class="trk-transport-results d-none" id="rutaTransportistaResults"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1 small">Destino del transportista</label>
                            <select class="form-select form-select-sm" id="rutaCedisDestino">
                                <option value="">Selecciona CEDIS destino</option>
                            </select>
                        </div>
                    </div>
                    <div id="rutaTransportistaInfo" class="trk-transport-info mt-2 d-none"></div>
                </div>

                <div class="mb-2" id="secAgregarCredito">
                    <label class="form-label small fw-semibold">
                        Agregar crédito a la ruta
                    </label>
                    <!-- Filtros de ubicación para créditos -->
                    <div class="row g-2 mb-2" id="crdFiltrosUbicacion">
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1 small">Estado</label>
                            <select class="form-select form-select-sm trk-select-buscable" id="crdFiltroEstado">
                                <option value="">— Todos los estados —</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1 small">Municipio</label>
                            <select class="form-select form-select-sm trk-select-buscable" id="crdFiltroMunicipio" disabled>
                                <option value="">— Todos los municipios —</option>
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
                        <div id="trkLiveMapInfo" class="trk-live-map-card d-none">
                            <div class="live-title">
                                <i class="fa-solid fa-truck-fast"></i>
                                <span>Unidad en vivo</span>
                            </div>
                            <div class="live-meta">
                                <span id="trkLiveUpdated">Sin seÃ±al</span>
                                <span id="trkLiveSpeed">Vel. —</span>
                                <span id="trkLiveAccuracy">Prec. —</span>
                                <span id="trkLiveBattery">Bat. —</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning py-1 px-2 mt-1 small d-none" id="mapAlertCoords">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Algunos créditos no tienen coordenadas ni dirección registrada.
                        La ruta en el mapa puede estar incompleta.
                    </div>
                </div>

            </div><!-- /modal-body -->

            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-label-secondary btn-sm" id="btnCerrarModalFooter">
                    <i class="icon-base bx bx-x icon-sm me-1"></i>Cancelar
                </button>
                <div class="d-flex gap-2">
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
    </div>
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
                <div class="input-group input-group-sm mb-2" id="mapPickerSearchWrap">
                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--track-color);"></i>
                    </span>
                    <input type="text" class="form-control" id="mapPickerSearch"
                           placeholder="Buscar dirección, colonia, municipio..." autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiarMapSearch" title="Limpiar búsqueda">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="mapPickerContainer" style="width:100%;height:420px;border-radius:.5rem;overflow:hidden;border:1px solid var(--track-border);"></div>
                <div class="mt-2 px-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="small text-muted" id="mapPickerCoordsLabel">
                            <i class="fa-solid fa-crosshairs me-1"></i>Sin selección
                        </span>
                    </div>
                    <div id="mapPickerGeoInfo" class="small text-muted mt-1 d-none">
                        <i class="fa-solid fa-map-location-dot me-1" style="color:var(--track-color);"></i>
                        <span id="mapPickerEstadoMun">—</span>
                    </div>
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
        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"
                aria-label="Cerrar" style="flex-shrink:0;"></button>
    </div>

    <div class="modal-body">
        <!-- Tabs: una por id_detalle -->
        <div class="chat-tabs-wrap" id="chatTabsWrap" style="display:none;">
            <ul class="nav d-flex" id="chatTabList" role="tablist"></ul>
        </div>

        <!-- Panes: uno por id_detalle -->
        <div id="chatPanesContainer" class="flex-grow-1 d-flex flex-column" style="overflow:hidden;"></div>

        <!-- Placeholder cuando no hay items -->
        <div id="chatEmptyPlaceholder" class="flex-grow-1 d-none align-items-center justify-content-center text-center p-4"
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
/* Chat Operativo — URL WebSocket (sin credenciales, solo el host) */
window._trackingChatWsBaseUrl   = <?= json_encode((string)($tracking_chat_ws_base_url ?? '')) ?>;
window._trackingApiBaseUrl      = <?= json_encode((string)($tracking_api_base_url ?? '')) ?>;
window._trackingChatGestorNombre = <?= json_encode(trim((string)($_SESSION['usuario_nombre'] ?? 'Gestor'))) ?>;
window._trackingDiasMinimosProgramacion = <?= json_encode((int)($tracking_dias_minimos_programacion ?? 2)) ?>;
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
    agenciasTracking:     [],
    transportistasTracking: [],
    rutasRegistradas:     [],
    rutasFiltro:          'todas',
    rutasVista:           'cards',
    chatUnreadPorRuta:    {},
    trackingApiDisponible:true,
    trackingApiRetryAt:   0,
    rutaCancelada:        false,
    idRutaEditando:       null, // null = nueva ruta
    estatusRuta:          null, // estatus_ruta de la ruta cargada (null = nueva)
    soloLectura:          false,// modal en modo vista bloqueada
    cargando:             false, // cargando ruta existente (evita haychangios)
    haycambios:           false,
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
    routeLegDurations:    [],   // duraciones Google Maps entre puntos confirmados
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
}

function _trkRefrescarSelectBuscable(selector) {
    if (!_trkSelect2Disponible()) return;
    const $el = $(selector);
    if (!$el.length || !$el.hasClass('select2-hidden-accessible')) return;
    $el.trigger('change.select2');
}

document.addEventListener('DOMContentLoaded', function () {
    _trkInicializarFiltros();
    _trkInicializarSelectsBuscables();
    _trkInicializarTablaCreditosDT();
    _trkInicializarTablaRutasDT();
    _trkInicializarTablaBorradorDT();
    _trkInicializarTablasCatalogosDT();
    _trkInicializarRutasVista();
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
    }).observe(document.body, { attributeFilter: ['class'] });

    document.getElementById('tabRutasBtn').addEventListener('click', () => _trkCargarRutas());
    document.getElementById('tabBorradorBtn').addEventListener('click', () => _trkCargarBorradores());
    document.getElementById('tabCatalogosBtn').addEventListener('click', () => _trkRenderCatalogosTracking());
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

    // Carga inicial de todas las pestañas en paralelo
    _trkCargarTodo();
});

// ─── Filtros ─────────────────────────────────────────────
function _trkInicializarFiltros() {
    $('#filtroEstado').on('change', function () {
        const est = $(this).val();
        const $mun = $('#filtroMunicipio');
        $mun.html('<option value="">— Cargando… —</option>').prop('disabled', true);
        _trkRefrescarSelectBuscable('#filtroMunicipio');
        if (!est) {
            $mun.html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
            _trkRefrescarSelectBuscable('#filtroMunicipio');
            return;
        }
        trkFetch(`/TrackingRecoleccion/obtenerMunicipios?estado=${encodeURIComponent(est)}`)
            .then(r => {
                $mun.html('<option value="">— Todos los municipios —</option>');
                (r.datos || []).forEach(m => {
                    $mun.append(`<option value="${m}">${m}</option>`);
                });
                $mun.prop('disabled', false);
                _trkRefrescarSelectBuscable('#filtroMunicipio');
            })
            .catch(() => {
                $mun.html('<option value="">— Error —</option>');
                _trkRefrescarSelectBuscable('#filtroMunicipio');
            });
    });

    $('#btnFiltrarCreditos').on('click', function () {
        _trkRefrescarSelectBuscable('#filtroMunicipio');
        _trkCargarCreditosPaso2();
    });

    $('#btnLimpiarFiltros').on('click', function () {
        $('#filtroEstado').val('').trigger('change.select2');
        $('#filtroMunicipio').html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
        _trkRefrescarSelectBuscable('#filtroMunicipio');
        _trkCargarCreditosPaso2();
    });
}

function _trkCargarEstados() {
    return trkFetch('/TrackingRecoleccion/obtenerEstados')
        .then(r => {
            const estados = r.datos || [];
            const $selFiltro = $('#filtroEstado');
            estados.forEach(e => $selFiltro.append(`<option value="${e}">${e}</option>`));
            _trkRefrescarSelectBuscable('#filtroEstado');
        });
}

// ─── Tabla de créditos ──────────────────────────────────
function _trkRenderLocationBadges(estado, municipio) {
    const est = String(estado || '').trim();
    const mun = String(municipio || '').trim();
    if (!est && !mun) return '—';
    const parts = [];
    if (est) parts.push(`<span class="trk-loc-badge trk-loc-estado" title="Estado">${_trkChatEscapeHtml(est)}</span>`);
    if (mun) parts.push(`<span class="trk-loc-badge trk-loc-municipio" title="Municipio">${_trkChatEscapeHtml(mun)}</span>`);
    return `<span class="trk-location-badges">${parts.join('')}</span>`;
}

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
            { data: 'estado',    defaultContent: '—', render: v => _trkRenderLocationBadges(v, null) },
            { data: 'municipio', defaultContent: '—', render: v => _trkRenderLocationBadges(null, v) },
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
            /* columna Acción comentada
            {
                data: null,
                orderable: false,
                render: r => `<button class="btn btn-sm btn-outline-success py-0 px-1 btn-agregar-a-ruta"
                                  data-id="${r.id_credito}"
                                  title="Agregar a ruta">
                                  <i class="fa-solid fa-plus"></i>
                              </button>`,
            },
            */
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
    if (!r || !r.nombre_transportista) return '—';
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
        document.querySelectorAll('#trkRutasFiltros .trk-rutas-filter').forEach(b => b.classList.toggle('active', b === btn));
        _trkRenderRutasCards();
    });

    document.getElementById('trkVistaCards')?.addEventListener('click', () => _trkSetRutasVista('cards'));
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
}

function _trkSetRutasVista(vista) {
    _trk.rutasVista = vista === 'tabla' ? 'tabla' : 'cards';
    const cardsWrap = document.getElementById('trkRutasCardsWrap');
    const tablaWrap = document.getElementById('trkRutasTablaWrap');
    const btnCards  = document.getElementById('trkVistaCards');
    const btnTabla  = document.getElementById('trkVistaTabla');
    if (cardsWrap) cardsWrap.style.display = _trk.rutasVista === 'cards' ? '' : 'none';
    if (tablaWrap) tablaWrap.style.display = _trk.rutasVista === 'tabla' ? '' : 'none';
    btnCards?.classList.toggle('active', _trk.rutasVista === 'cards');
    btnCards?.classList.toggle('btn-label-primary', _trk.rutasVista === 'cards');
    btnCards?.classList.toggle('btn-label-secondary', _trk.rutasVista !== 'cards');
    btnTabla?.classList.toggle('active', _trk.rutasVista === 'tabla');
    btnTabla?.classList.toggle('btn-label-primary', _trk.rutasVista === 'tabla');
    btnTabla?.classList.toggle('btn-label-secondary', _trk.rutasVista !== 'tabla');
    if (_trk.rutasVista === 'tabla' && _trk.tablaRutasDT) {
        setTimeout(() => {
            _trk.tablaRutasDT.columns.adjust();
            if (_trk.tablaRutasDT.responsive) _trk.tablaRutasDT.responsive.recalc();
        }, 50);
    }
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

function _trkRutasFiltradas() {
    const filtro = _trk.rutasFiltro || 'todas';
    if (filtro === 'todas') return _trk.rutasRegistradas || [];
    return (_trk.rutasRegistradas || []).filter(r => String(r.estatus_ruta || '') === filtro);
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
    if (!rutas.length) {
        wrap.innerHTML = `<div class="trk-rutas-empty" style="grid-column:1/-1;">
            <i class="fa-solid fa-route mb-2" style="font-size:1.35rem;color:var(--track-color);"></i>
            <div class="fw-semibold">No hay rutas para este filtro</div>
            <div class="small mt-1">Cambia el estatus o registra una nueva ruta.</div>
        </div>`;
        return;
    }
    wrap.innerHTML = rutas.map(r => _trkRenderRutaCard(r)).join('');
    wrap.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover', html: true });
    });
    rutas.forEach(r => {
        if (_trkRutaDebeConsultarEstadoLive(r.estatus_ruta)) _trkActualizarEstadoCeldaRuta(r.id_ruta);
    });
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
    const consultarLive = _trkRutaDebeConsultarEstadoLive(estatus);
    const estatusBadge = RUTA_LABEL[estatus] || `<span class="badge bg-secondary">${_trkChatEscapeHtml(estatus || 'Sin estatus')}</span>`;
    const statusLive = consultarLive
        ? `<div id="trkRutaTrkStatusCard_${id}" class="d-flex align-items-center gap-1 mt-1">
            <span class="spinner-border spinner-border-sm text-secondary" style="width:.55rem;height:.55rem;border-width:1.5px;"></span>
        </div>`
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
                ${consultarLive ? '' : estatusBadge}
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
                render: (v, type, r) => {
                    const base = RUTA_LABEL[v] || `<span class="badge bg-secondary">${v}</span>`;
                    if (_trkRutaDebeConsultarEstadoLive(v)) {
                        return `<div class="d-flex flex-column gap-1">
                            <div id="trkRutaTrkStatus_${r.id_ruta}" class="d-flex align-items-center gap-1">
                                <span class="spinner-border spinner-border-sm text-secondary" style="width:.55rem;height:.55rem;border-width:1.5px;"></span>
                            </div>
                        </div>`;
                    }
                    return base;
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
                data: null,
                defaultContent: '—',
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
            this.api().rows().every(function () {
                const d = this.data();
                if (_trkRutaDebeConsultarEstadoLive(d.estatus_ruta)) {
                    _trkActualizarEstadoCeldaRuta(d.id_ruta);
                }
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

// ─── Estatus live (tracking API) para rutas en_proceso ─────────────────────
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
    return trkFetch('/TrackingRecoleccion/obtenerRutas', { method: 'POST' })
        .then(r => {
            const rutas = r.datos || [];
            _trk.rutasRegistradas = rutas;
            if (_trk.tablaRutasDT) {
                _trk.tablaRutasDT.clear().rows.add(rutas).draw();
            }
            _trkActualizarResumenRutas(rutas);
            _trkRenderRutasCards();
            _trkSetRutasVista(_trk.rutasVista || 'cards');
            const badge = document.getElementById('badgeRutas');
            if (badge) badge.textContent = String(rutas.length);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar rutas.', confirmButtonText: 'Aceptar' }));
}

async function _trkCancelarRuta(idRuta, nombreRuta = '') {
    if (!idRuta) return;
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Cancelar ruta',
        html: `<div class="text-start small text-muted mb-2">Ruta: <b>${_trkChatEscapeHtml(nombreRuta || '#' + idRuta)}</b></div>`,
        input: 'textarea',
        inputLabel: 'Motivo de cancelación',
        inputPlaceholder: 'Describe el motivo...',
        inputAttributes: {
            maxlength: 200,
            rows: 4,
        },
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar ruta',
        cancelButtonText: 'Regresar',
        confirmButtonColor: '#ef4444',
        inputValidator: value => {
            const motivo = String(value || '').trim();
            if (!motivo) return 'El motivo de cancelación es obligatorio.';
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
            Swal.fire({ icon: 'success', title: 'Ruta cancelada', text: r.message || 'La ruta se canceló correctamente.', timer: 1800, showConfirmButton: false });
            _trkCargarRutas();
            _trkCargarCreditosPaso2();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || r.message || 'No se pudo cancelar la ruta.', confirmButtonText: 'Aceptar' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cancelar la ruta.', confirmButtonText: 'Aceptar' });
    }
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
                data: null,
                defaultContent: '—',
                render: r => _trkRenderTransportistaRuta(r),
            },
            {
                data: null,
                orderable: false,
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
                _trk.tablaRutasBorradorDT.clear().rows.add(borradores).draw();
            }
            // Actualizar contador en la pestaña
            const $badge = document.getElementById('badgeBorradores');
            if ($badge) $badge.textContent = borradores.length > 0 ? borradores.length : '0';
        })
        .catch(() => {});
}

// ─── Carga inicial de todos los datos en paralelo ─────────
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
                    <small class="text-muted">${_trkChatEscapeHtml([a.telefono, a.email].filter(Boolean).join(' · '))}</small>`,
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
    return trkFetch('/TrackingRecoleccion/obtenerCatalogoAgenciasTransportistas')
        .then(r => {
            const datos = r.datos || {};
            _trk.agenciasTracking = datos.agencias || [];
            _trk.transportistasTracking = datos.transportistas || [];
            _trkPoblarAgenciasTrackingSelect();
            _trkRenderCatalogosTracking();
            _trkRefrescarSelectTransportistas();
        });
}

function _trkPoblarAgenciasTrackingSelect() {
    const $sel = $('#rutaAgenciaTracking');
    const $dest = $('#rutaCedisDestino');
    const selected = $sel.val();
    const selectedDest = $dest.val();
    $sel.html('<option value="">Selecciona CEDIS</option>');
    $dest.html('<option value="">Selecciona CEDIS destino</option>');
    const cedis = _trk.agenciasTracking.filter(a => (a.tipo_ubicacion || 'agencia') === 'agencia');
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
    $('#badgeCatalogos').text(agencias.length + transportistas.length);

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
    $('#rutaTransportistaTracking').val(id ? String(id) : '');
    const t = _trkTransportistaSeleccionado();
    $('#rutaTransportistaSearch').val(_trkTransportistaTexto(t));
    $('#rutaTransportistaResults').addClass('d-none');
    $('#rutaCedisDestino').val('');
    _trkSincronizarTransportistaSeleccionado();
    _trkRenderTransportistaInfo();
}

function _trkRefrescarSelectTransportistas(preselectedId = null) {
    const transportistas = _trkTransportistasFiltrados();
    $('#rutaTransportistaSearch').prop('disabled', transportistas.length === 0);
    _trkRenderTransportistaResultados('');
    if (preselectedId) {
        _trkSeleccionarTransportista(preselectedId);
        return;
    }
    _trkSincronizarTransportistaSeleccionado();
    _trkRenderTransportistaInfo();
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
}

function _trkCedisPorId(id) {
    if (!id) return null;
    return (_trk.agenciasTracking || []).find(a => String(a.id_agencia) === String(id)) || null;
}

function _trkEsCedisDestinoInternoPermitido(cedis) {
    if (!cedis) return false;
    const id = Number(cedis.id_agencia || 0);
    const clave = _trkNormTxt(cedis.clave_agencia);
    return [1, 2].includes(id) || ['LOMAS_PLAZA_MAXIKASH', 'TLALNEPANTLA_MAXIKASH'].includes(clave);
}

function _trkCedisDestinoFiltrados(tipo) {
    const cedis = (_trk.agenciasTracking || []).filter(a => (a.tipo_ubicacion || 'agencia') === 'agencia');
    if (tipo === 'interno') return cedis.filter(_trkEsCedisDestinoInternoPermitido);
    return cedis;
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
        'LA PAZ', 'TECÁMAC', 'TECAMAC', 'HUIXQUILUCAN', 'CHALCO', 'VALLE DE CHALCO'
    ];
    if (est === 'CDMX' || est === 'CIUDAD DE MEXICO' || est === 'DISTRITO FEDERAL') return true;
    return estadosOk.includes(est) && municipiosOk.includes(mun);
}

function _trkValidarReglasTransportista() {
    const tipo = _trkTransportistaSeleccionado()?.tipo_transportista || $('#rutaTipoTransportista').val();
    const idDestino = $('#rutaCedisDestino').val();
    const cedisDestino = _trkCedisPorId(idDestino);
    if (!idDestino) {
        return { ok: false, mensaje: 'Selecciona el CEDIS destino donde se entregara el vehiculo.' };
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
    const contacto = [t.telefono, t.email].filter(Boolean).join(' · ') || 'Sin contacto';
    $box.removeClass('d-none').html(`
        <div class="d-flex flex-wrap align-items-center gap-2">
            ${_trkTipoTransportistaBadge(t.tipo_transportista)}
            <span class="fw-semibold">${_trkChatEscapeHtml(t.nombre_transportista || '')}</span>
            <span class="text-muted">${_trkChatEscapeHtml(agencia)}</span>
            <span class="text-muted">${_trkChatEscapeHtml(contacto)}</span>
        </div>
    `);
}

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
        _trkCargarCatalogoAgenciasTransportistas().catch(() => {}),
        _trkCargarCreditosPaso2().catch(() => {}),
        _trkCargarBorradores().catch(() => {}),
        _trkCargarRutas().catch(() => {}),
    ]).then(() => Swal.close());
}

// ─── Modal — apertura ────────────────────────────────────
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
    // Fecha mínima
    const minDate = _trkFechaMinimaProgramacion();
    document.getElementById('rutaFecha').min = minDate;
    $('#rutaDiasMinimosTxt').text(_trkDiasMinimosProgramacion());

    $('#rutaTransportistaSearch')
        .on('focus input', function (e) {
            if (e.type === 'input') {
                $('#rutaTransportistaTracking').val('');
                _trkSincronizarTransportistaSeleccionado();
                _trkRenderTransportistaInfo();
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

    // Filtro de créditos por estado
    $('#crdFiltroEstado').on('change', function () {
        const est = $(this).val();
        _trkPoblarFiltroMunicipiosCrd(est);
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
            _trkAplicarEtasAutomaticas();
            _trkRenderListaCreditos();
            _trkRenderizarMapa();
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
    _trk.rutaCancelada         = false;
    _trk.cargando              = false;
    _trk.creditosEnRuta        = [];
    _trk.routeLegDurations     = [];
    _trk.haychangios           = false;
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
    _trkRefrescarSelectTransportistas();
    // Reset filtros de créditos
    $('#crdFiltroEstado').val('').trigger('change.select2');
    $('#crdFiltroMunicipio').html('<option value="">— Todos los municipios —</option>').prop('disabled', true);
    _trkRefrescarSelectBuscable('#crdFiltroMunicipio');
    _trkPoblarFiltroEstadosCrd();
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
    const estadoActual = $('#crdFiltroEstado').val();
    const estados = [...new Set(
        _trk.creditosDisponibles.map(c => c.estado).filter(Boolean)
    )].sort();
    const $est = $('#crdFiltroEstado');
    $est.html('<option value="">— Todos los estados —</option>');
    estados.forEach(e => {
        const agotado = _trkEstadoCreditosAgotado(e);
        const texto = agotado ? `${e} (TODOS SELECCIONADOS EN EL MAPA)` : e;
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

function _trkEstadoCreditosAgotado(estado) {
    if (!estado) return false;
    const ids = _trkIdsCreditosEnRutaSet();
    const creditosEstado = _trk.creditosDisponibles.filter(c => c.estado === estado);
    return creditosEstado.length > 0 && creditosEstado.every(c => ids.has(String(c.id_credito)));
}

function _trkMunicipioCreditosAgotado(estado, municipio) {
    if (!estado || !municipio) return false;
    const ids = _trkIdsCreditosEnRutaSet();
    const creditosMunicipio = _trk.creditosDisponibles.filter(c =>
        c.estado === estado && c.municipio === municipio
    );
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
            .filter(c => c.estado === estado && c.municipio)
            .map(c => c.municipio)
    )].sort();
    municipios.forEach(m => {
        const agotado = _trkMunicipioCreditosAgotado(estado, m);
        const texto = agotado ? `${m} (TODOS SELECCIONADOS EN EL MAPA)` : m;
        $mun.append($('<option>', { value: m, text: texto, disabled: agotado }));
    });
    if (municipioActual && !_trkMunicipioCreditosAgotado(estado, municipioActual)) {
        $mun.val(municipioActual);
    } else {
        $mun.val('');
    }
    $mun.prop('disabled', municipios.length === 0);
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
        if (estFiltro && c.estado !== estFiltro) return;
        if (munFiltro && c.municipio !== munFiltro) return;
        const modelo = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const label  = `#${c.id_credito} · ${modelo || '(sin modelo)'} · ${c.bin || '—'}`;
        $sel.append(`<option value="${c.id_credito}">${label}</option>`);
    });
    _trkRefrescarSelectBuscable('#rutaCreditoSelect');
}

function _trkAgregarCreditoALista(cred) {
    // RN-03: no duplicados
    if (_trk.creditosEnRuta.find(c => String(c.id_credito) === String(cred.id_credito))) {
        Swal.fire({ icon: 'warning', title: 'Aviso', text: 'Este crédito ya está en la ruta.', confirmButtonText: 'Aceptar' });
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
    const isEmpty = _trk.creditosEnRuta.length === 0;
    const filaLectura = _trkRutaEstaCancelada();
    $('#rutaCreditosCount').text(_trk.creditosEnRuta.length);
    if (_trk.sortableInstance) _trk.sortableInstance.option('disabled', filaLectura);

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
        const etaInfo   = _trkEstadoEta(c, c.estatus_recoleccion);
        const pinTitle  = tienePin ? 'Ubicación manual asignada (clic para cambiar)' : 'Asignar ubicación en mapa';

        // Los créditos en ruta nunca se bloquean
        // En rutas canceladas queda como consulta: solo se permite enfocar el crédito en el mapa.

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
        <div class="track-credito-row" data-id="${c.id_credito}" title="Clic para ubicar este crédito en el mapa">
            ${dragHandle}
            <span class="orden-num">${idx + 1}</span>
            <div class="d-flex flex-column gap-0 flex-grow-1" style="min-width:0;">
                <span class="fw-semibold text-truncate">#${c.id_credito} — ${c.nombre_cliente || '—'}</span>
                <span class="text-muted" style="font-size:.75rem;">
                    ${modelo} · BIN: ${c.bin || '—'}
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
                    <span class="text-muted" style="font-size:.7rem;line-height:1;">–</span>
                    <select class="form-select form-select-sm eta-h" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="Hora fin (mínimo 4 horas después)">${optsFin}</select>
                    <input type="text" class="form-control text-center fw-semibold eta-m" data-id="${c.id_credito}" data-tipo="fin" inputmode="numeric" maxlength="2" placeholder="00" autocomplete="off" value="${etaFin.m}" style="width:48px;flex-shrink:0;letter-spacing:.05em;" title="Minutos fin (mínimo 4 horas después)">
                    <select class="form-select form-select-sm eta-ap" data-id="${c.id_credito}" data-tipo="fin" style="width:62px;flex-shrink:0;" title="AM/PM fin (mínimo 4 horas después)">
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

    // Eventos de créditos (siempre activos, incluso en modo ver ruta)
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
                        title: 'Fecha ETA invÃ¡lida',
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
    if (_trk.mapInstance && window.google?.maps) {
        google.maps.event.trigger(_trk.mapInstance, 'resize');
    }

    if (!_trk.mapLoaded) {
        // Cargar Google Maps API dinámicamente
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
            styles: [],
            mapTypeControl: false,
        });
        _trk.geocoder = new google.maps.Geocoder();
    }

    // ── Limpiar mapa anterior ─────────────────────────────────
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
    const addr = [cred.direccion, cred.municipio, cred.estado, 'México'].filter(Boolean).join(', ');
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
            Swal.fire({ icon: 'info', title: 'Sin ubicación', text: 'Este crédito todavía no tiene una ubicación válida para mostrar en el mapa.', confirmButtonText: 'Aceptar' });
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
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no está disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!_trk.mapInstance || typeof google === 'undefined' || !google.maps) {
        if ((opts.retry || 0) >= 4) {
            Swal.fire({ icon: 'info', title: 'Mapa cargando', text: 'No se pudo enfocar el crédito todavía. Intenta de nuevo en unos segundos.', confirmButtonText: 'Aceptar' });
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

// ─── Map Picker (Plan B: clic en mapa para asignar coords) ──────────────────
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
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no está disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }

    _trkPicker.creditoId         = cred.id_credito;
    _trkPicker.selectedLat        = null;
    _trkPicker.selectedLng        = null;
    _trkPicker.selectedEstado     = null;
    _trkPicker.selectedMunicipio  = null;

    // Etiqueta en el modal
    document.getElementById('mapPickerCreditoLabel').textContent =
        ` — #${cred.id_credito} ${cred.nombre_cliente ? '(' + cred.nombre_cliente + ')' : ''}`;
    document.getElementById('mapPickerCoordsLabel').innerHTML =
        '<i class="fa-solid fa-crosshairs me-1"></i>Sin selección';
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
    // Oscurecer el backdrop cuando el map picker esté sobre otro modal
    document.getElementById('modalMapPicker').addEventListener('shown.bs.modal', () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length >= 2) backdrops[backdrops.length - 1].style.opacity = '0.65';
    }, { once: true });
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
                        _trkPickerReverseGeocode(ev.latLng);
                    });
                } else {
                    _trkPicker.marker.setPosition(e.latLng);
                }

                _trkPickerReverseGeocode(e.latLng);
                document.getElementById('btnConfirmarMapPicker').disabled = false;
            });

            // ── Google Places Autocomplete ──────────────────────────
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
                            title: 'Ubicación seleccionada',
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
        script.src   = `https://maps.googleapis.com/maps/api/js?key=${window._trackGoogleMapsKey}&libraries=geometry,places&callback=_trkMapPickerReady`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        window._trkMapPickerReady = initMap;
        _trk.mapLoaded = true;
    }
}

function _trkPickerCrearMarker(latLng, title = 'UbicaciÃ³n seleccionada') {
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
    _trkPickerCrearMarker(latLng, addressText || 'UbicaciÃ³n seleccionada');
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
        address: `${query}, MÃ©xico`,
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
                '<i class="fa-solid fa-crosshairs me-1"></i>Sin selecciÃ³n';
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
        // Sobrescribir también las props que usa el mapa de ruta
        cred.latitud  = lat;
        cred.longitud = lng;
        // Aplicar estado/municipio detectados por geocodificación
        if (_trkPicker.selectedEstado)    cred.estado    = _trkPicker.selectedEstado;
        if (_trkPicker.selectedMunicipio) cred.municipio = _trkPicker.selectedMunicipio;
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
    let etaIni = salida + 30; // colchón operativo para carga/salida hacia primer punto
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
        title: 'Ruta no óptima',
        text: 'Tu ruta no es la mas optima para el recorrido, implicarian mayor inversion de recursos, estas seguro de que quieres enviarla asi?',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviarla así',
        cancelButtonText: 'Revisar ruta',
        confirmButtonColor: '#0d9488',
    });
    return r.isConfirmed;
}

async function _trkGuardarRuta(modo) {
    const nombre    = $('#rutaNombre').val().trim();
    const fecha     = $('#rutaFecha').val();
    const idTransportista   = $('#rutaTransportistaTracking').val();
    const transportistaSel  = _trkTransportistaSeleccionado();
    const tipoTransportista = transportistaSel?.tipo_transportista || '';
    const idAgenciaTracking = transportistaSel?.id_agencia || '';
    const idCedisDestino    = $('#rutaCedisDestino').val();

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
    const etaInvalida = _trk.creditosEnRuta.find(c => c.fecha_eta && _trkCompararFecha(c.fecha_eta, fecha) < 0);
    if (etaInvalida) {
        Swal.fire({
            icon: 'warning',
            title: 'ETA invÃ¡lida',
            text: `La ETA del crÃ©dito #${etaInvalida.id_credito} no puede ser anterior a la fecha de salida de la ruta.`,
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
            Swal.fire({ icon: 'warning', title: 'Pendiente', text: 'Todos los créditos deben tener confirmación del gestor para enviar la ruta.', confirmButtonText: 'Aceptar' });
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
            const infoTransportista = _trkTransportistaRutaData(d);
            const transportista = infoTransportista.nombre
                ? `${infoTransportista.nombre} (${infoTransportista.tipo || 'No disponible'})`
                : 'Sin transportista';
            const agenciaTracking = infoTransportista.agencia || infoTransportista.direccion || 'Sin CEDIS';
            const cedisDestino = d.cedis_destino_nombre || 'Sin CEDIS destino';
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
                    <td>${det.id_credito || '—'}</td>
                    <td>${det.nombre_cliente || '—'}</td>
                    <td>${det.modelo || '—'}</td>
                    <td>${det.bin || '—'}</td>
                    <td>${_trkRenderLocationBadges(det.estado, det.municipio)}</td>
                    <td>${det.estatus_proceso || '—'}</td>
                    <td>${CONF_LABEL[det.estatus_confirmacion_gestor] || det.estatus_confirmacion_gestor || '—'}</td>
                </tr>`;
            });
            $body.html(`
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3"><b class="d-block small text-muted">Nombre</b>${d.nombre_ruta}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Estado</b>${_trkRenderLocationBadges(d.estado, null)}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Municipio</b>${_trkRenderLocationBadges(null, d.municipio)}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Fecha programada</b>${d.fecha_programada_fmt || d.fecha_programada || '—'}</div>
                    <div class="col-6 col-md-2"><b class="d-block small text-muted">Hora de salida</b>${
                        d.act_hora_1
                            ? `<span class="badge bg-warning text-dark me-1" title="Hora actualizada">${_trkFormatHora(d.act_hora_1)}</span><s class="text-muted small">${_trkFormatHora(d.hora_inicial)}</s>`
                            : (d.hora_inicial ? `<span class="badge bg-light text-dark border">${_trkFormatHora(d.hora_inicial)}</span>` : '—')
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
    if (_trkRutaEstaCancelada()) return;
    _trk.haychangios = true;
    if (_trk.soloLectura) {
        $('#btnActualizarRuta').prop('disabled', false)
            .removeClass('btn-label-secondary')
            .addClass('btn-primary')
            .css({ cursor: 'pointer' });
    }
}

// ─── Bloquear / Desbloquear modal ───────────────────────
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
    // Ocultar sección de agregar nuevos créditos (no añadir, solo editar existentes)
    $('#secAgregarCredito').hide();
    $('#reorderHint').hide();
    // Swap de botones: ocultar borrador/enviar, mostrar actualizar (gris hasta que haya cambios)
    $('#btnGuardarBorrador, #btnEnviarRuta').hide();
    $('#btnActualizarRuta').show().prop('disabled', true)
        .removeClass('btn-primary')
        .addClass('btn-label-secondary')
        .css({ cursor: 'not-allowed' });
    // Badge en título
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
            $('#rutaTipoTransportista').val(d.tipo_transportista || '');
            _trkPoblarAgenciasTrackingSelect();
            $('#rutaAgenciaTracking').val(d.id_agencia_tracking ? String(d.id_agencia_tracking) : '');
            $('#rutaCedisDestino').val(d.id_cedis_destino ? String(d.id_cedis_destino) : '');
            _trkRefrescarSelectTransportistas(d.id_transportista || null);

            // Créditos (cargar directamente en array)
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

            // Estado + Municipio via filtros de créditos
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

            // Iniciar tracking en tiempo real si la ruta está activa o completada
            const esActiva = _trkRutaDebeConsultarEstadoLive(d.estatus_ruta) || ['completado', 'concluida'].includes(String(d.estatus_ruta || ''));
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
    liveCfg:      null,
    liveCfgExpiry:0,
    ubicacion:    null,
    historial:    [],
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

// ─── Inicializar para una ruta ────────────────────────────
async function _trkRTIniciar(idRuta) {
    _trkRTLimpiar();
    _trkRT.idRuta = idRuta;
    document.getElementById('trkTrackingSection').classList.remove('d-none');
    await _trkRTCargarEstado();
    await _trkRTCargarMapaLive();
    const cfg = await _trkRTObtenerLiveConfig();
    if (cfg) _trkRTConectarWS(cfg);
}

// ─── Cargar estado vía REST ───────────────────────────────
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

        // Línea 1: Crédito #XXXXX · MARCA MODELO | Estado, Municipio
        const moto    = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const lugar   = [c.estado, c.municipio].filter(Boolean).join(', ');
        let linea1    = `Crédito #${c.id_credito}`;
        if (moto)  linea1 += ` · ${moto}`;
        if (lugar) linea1 += ` | ${lugar}`;

        // Línea 2: nombre del cliente
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
                ${etaTxt ? `<span class="trk-step-dir">ETA ${_trkChatEscapeHtml(etaTxt)} · ${_trkChatEscapeHtml(etaInfo.label)}</span>` : ''}
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

// ─── Actualizar última ubicación del conductor ────────────
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

// ─── WS dot ──────────────────────────────────────────────
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
    _trk.liveVehiclePath = [];
    document.getElementById('trkLiveMapInfo')?.classList.add('d-none');
}

function _trkRTIconoVehiculo(heading = 0) {
    const rot = Number.isFinite(heading) ? heading : 0;
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 42 42">
        <g transform="rotate(${rot} 21 21)">
            <circle cx="21" cy="21" r="19" fill="#0d9488" stroke="#fff" stroke-width="3"/>
            <path d="M21 7l8 22-8-4-8 4 8-22z" fill="#fff"/>
        </g>
    </svg>`;
    return {
        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
        scaledSize: new google.maps.Size(42, 42),
        anchor: new google.maps.Point(21, 21),
    };
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
    const timeTxt = isNaN(ts.getTime()) ? 'Sin fecha' : ts.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.getElementById('trkLiveUpdated').textContent = `Actualizado ${timeTxt}`;
    document.getElementById('trkLiveSpeed').textContent = ubi.speed !== null && !isNaN(ubi.speed) ? `Vel. ${Math.round(ubi.speed)} km/h` : 'Vel. —';
    document.getElementById('trkLiveAccuracy').textContent = ubi.accuracy !== null && !isNaN(ubi.accuracy) ? `Prec. ${Math.round(ubi.accuracy)} m` : 'Prec. —';
    document.getElementById('trkLiveBattery').textContent = ubi.battery !== null && !isNaN(ubi.battery) ? `Bat. ${ubi.battery}%` : 'Bat. —';
    card.classList.remove('d-none');
}

function _trkRTActualizarWsDot(conectado) {
    const dot = document.getElementById('trkWsDot');
    if (!dot) return;
    dot.style.background = conectado ? '#16a34a' : '#cbd5e1';
    dot.title = conectado ? 'En tiempo real' : 'Sin conexión en tiempo real';
}

// ─── Conectar WebSocket de ruta ───────────────────────────
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
                title: ev.code === 4001 ? 'SesiÃ³n expirada' : 'Sin acceso',
                text: ev.reason || (ev.code === 4001 ? 'Recarga la pÃ¡gina para renovar el tracking en vivo.' : 'No tienes permiso para visualizar esta ruta en vivo.'),
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
                Swal.fire({ icon: 'warning', title: 'SesiÃ³n expirada', text: 'Recarga la pÃ¡gina para renovar el tracking en vivo.', confirmButtonText: 'Aceptar' });
            } else if (code === '4003') {
                _trkRT.wsRetries = 5;
                _trkRTActualizarWsDot(false);
                Swal.fire({ icon: 'warning', title: 'Sin acceso', text: data.message || data.detail || 'No tienes permiso para visualizar esta ruta en vivo.', confirmButtonText: 'Aceptar' });
            }
            break;
        }
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
            <div class="chat-typing-indicator d-none" id="chatTyping_${id}">
                <span>Escribiendo</span>
                <span class="chat-typing-dots"><span></span><span></span><span></span></span>
            </div>
            <button class="chat-new-msg-btn d-none" id="chatNewMsgBtn_${id}"
                    type="button">Nuevo mensaje ↓</button>
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

    // Activar primera pestaña ──────────────────────────────
    if (detalleItems.length > 0) {
        _trkChatActivarTab(detalleItems[0].id_detalle);
    }

    // Limpiar WS al cerrar el modal
    document.getElementById('modalChatOperativo')
        .addEventListener('hidden.bs.modal', _trkChatLimpiarTodo, { once: true });
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
        <div class="chat-bubble">${_trkChatRenderContenidoMensaje(msg)}</div>
        <span class="chat-bubble-meta">${actor} · ${hora}</span>
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
            <small>${_trkChatEscapeHtml([ext, size].filter(Boolean).join(' Â· '))}</small>
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

// ─── Enviar mensaje ──────────────────────────────────────
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
}

// ─── Actualizar UI según estatus ─────────────────────────
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
        _trkChatMostrarNotice(idDetalle, '🔒 El chat aún no está disponible — la ruta no ha iniciado.', 'bloqueado');
        if (textarea) textarea.disabled = true;
        if (sendBtn)  sendBtn.disabled  = true;
        attachBtns.forEach(btn => { btn.disabled = true; });
    } else if (state.estatus === 'cerrado') {
        _trkChatMostrarNotice(idDetalle, 'Esta conversación ha sido cerrada.', 'cerrado');
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

// ─── Badges no leídos ────────────────────────────────────
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
                <div class="small text-muted mt-2">${safeName} ${size ? 'Â· ' + size : ''}</div>
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
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexiÃ³n al subir el archivo.', confirmButtonText: 'Aceptar' });
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
        text: `El botÃ³n de ${labels[tipo] || 'archivo'} ya estÃ¡ preparado para id_detalle ${idDetalle}. Falta enlazar el endpoint de adjuntos del servicio de tracking.`,
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
