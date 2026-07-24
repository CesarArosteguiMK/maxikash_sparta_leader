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
.trk-admin-shell {
    display: grid;
    gap: 1rem;
}
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
.trk-admin-kpi {
    padding: .85rem;
    display: grid;
    grid-template-columns: 2.25rem minmax(0, 1fr);
    gap: .65rem;
    align-items: center;
}
.trk-admin-kpi i {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: .7rem;
    display: inline-grid;
    place-items: center;
    background: #eff6ff;
    color: #1d4ed8;
}
.trk-admin-kpi[data-tone="success"] i { background: #dcfce7; color: #15803d; }
.trk-admin-kpi[data-tone="info"] i { background: #e0f2fe; color: #0369a1; }
.trk-admin-kpi[data-tone="warning"] i { background: #fef3c7; color: #b45309; }
.trk-admin-kpi[data-tone="danger"] i { background: #fee2e2; color: #b91c1c; }
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
    font-size: 1.35rem;
    line-height: 1.1;
}
.trk-admin-toolbar {
    display: grid;
    grid-template-columns: minmax(260px, 1fr) 180px 170px auto auto auto;
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
.trk-admin-grid.is-panel {
    grid-template-columns: minmax(285px, 360px) minmax(0, 1fr);
    align-items: stretch;
}
.trk-admin-roster,
.trk-admin-detail {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .75rem;
    box-shadow: 0 .12rem .55rem rgba(15,23,42,.04);
    min-width: 0;
}
.trk-admin-roster {
    padding: .7rem;
    display: flex;
    flex-direction: column;
    gap: .45rem;
    max-height: 72vh;
    overflow-y: auto;
}
.trk-admin-roster-title {
    color: #64748b;
    font-size: .68rem;
    font-weight: 900;
    text-transform: uppercase;
    padding: .2rem .35rem .4rem;
}
.trk-admin-roster-item {
    width: 100%;
    border: 1px solid transparent;
    background: transparent;
    border-radius: .65rem;
    padding: .55rem .6rem;
    text-align: left;
    display: grid;
    grid-template-columns: 2rem minmax(0, 1fr) auto;
    gap: .55rem;
    align-items: center;
    color: inherit;
}
.trk-admin-roster-item:hover,
.trk-admin-roster-item.active {
    background: #f8fafc;
    border-color: #dbeafe;
}
.trk-admin-roster-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    display: inline-grid;
    place-items: center;
    background: #e0f2fe;
    color: #0369a1;
}
.trk-admin-roster-name {
    color: #25364f;
    font-size: .78rem;
    font-weight: 900;
    line-height: 1.15;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.trk-admin-roster-sub {
    color: #64748b;
    font-size: .68rem;
    line-height: 1.15;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.trk-admin-detail { overflow: hidden; }
.trk-admin-detail-hero {
    padding: 1.05rem 1.2rem;
    min-height: 245px;
    display: grid;
    grid-template-columns: minmax(220px, .75fr) minmax(310px, 1.15fr) minmax(220px, .72fr);
    gap: 1rem;
    align-items: stretch;
    background: linear-gradient(110deg, #fff 0%, #fff 44%, #f1f5f9 44%, #f8fafc 100%);
    border-bottom: 1px solid #e2e8f0;
}
.trk-admin-detail-modal-dialog {
    width: 96vw;
    max-width: 96vw;
    margin: 1rem auto;
}
.trk-admin-detail-modal-dialog .modal-content {
    min-height: calc(100vh - 2rem);
}
.trk-admin-detail-modal-dialog.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 6rem);
}
.trk-admin-detail-overview {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    min-height: 0;
    background: #fff;
    border-bottom: 0;
}
.trk-admin-driver-strip {
    display: grid;
    grid-template-columns: minmax(320px, 1fr) minmax(210px, .38fr) minmax(250px, .45fr);
    gap: .75rem;
    align-items: stretch;
    border: 1px solid #dbe3ef;
    border-radius: .75rem;
    background: #fff;
    padding: .85rem;
}
.trk-admin-driver-identity {
    display: grid;
    align-content: center;
    gap: .35rem;
    min-width: 0;
}
.trk-admin-driver-counters {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .15rem;
}
.trk-admin-driver-counters span {
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: .68rem;
    font-weight: 900;
    padding: .2rem .5rem;
}
.trk-admin-unit-band {
    display: grid;
    grid-template-columns: minmax(185px, .45fr) minmax(310px, .75fr) minmax(460px, 1.3fr);
    gap: 1rem;
    align-items: center;
    border: 1px solid #dbe3ef;
    border-radius: .75rem;
    background:
        linear-gradient(90deg, rgba(255,255,255,.98), rgba(248,250,252,.86)),
        radial-gradient(circle at 72% 42%, rgba(148,163,184,.16), transparent 34%);
    padding: 1.1rem 1.25rem;
}
.trk-admin-unit-band .trk-admin-capacity-card {
    min-height: 190px;
}
.trk-admin-unit-specs {
    margin-top: 0;
    align-self: center;
}
.trk-admin-unit-band .trk-admin-detail-vehicle {
    min-height: 230px;
}
.trk-admin-unit-band .trk-admin-vehicle-img {
    max-height: 310px;
}
.trk-admin-driver-block,
.trk-admin-vehicle-card,
.trk-admin-capacity-card {
    min-width: 0;
}
.trk-admin-detail-name {
    color: #25364f;
    font-size: 1.1rem;
    font-weight: 950;
    line-height: 1.15;
}
.trk-admin-detail-unit,
.trk-admin-vehicle-specs {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .6rem;
    margin-top: .85rem;
}
.trk-admin-contact-layout {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
.trk-admin-info-box {
    border: 1px solid #e2e8f0;
    border-radius: .65rem;
    background: rgba(248,250,252,.88);
    padding: .62rem .68rem;
    min-width: 0;
}
.trk-admin-info-box span {
    display: block;
    color: #64748b;
    font-size: .66rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .01em;
}
.trk-admin-info-box strong {
    display: block;
    color: #25364f;
    font-size: .9rem;
    font-weight: 950;
    line-height: 1.15;
    overflow-wrap: anywhere;
}
.trk-admin-info-box small {
    display: block;
    color: #64748b;
    font-size: .72rem;
    line-height: 1.2;
    overflow-wrap: anywhere;
    margin-top: .15rem;
}
.trk-admin-vehicle-card {
    position: relative;
    overflow: hidden;
    border-radius: .85rem;
    border: 1px solid #dbeafe;
    background:
        radial-gradient(circle at 68% 22%, rgba(37,99,235,.12), transparent 26%),
        linear-gradient(135deg, #f8fbff, #eef6ff);
    padding: 1rem;
    display: grid;
    grid-template-rows: auto minmax(92px, 1fr) auto;
    gap: .65rem;
}
.trk-admin-vehicle-label {
    color: #64748b;
    font-size: .68rem;
    font-weight: 900;
    text-transform: uppercase;
}
.trk-admin-vehicle-name {
    color: #172554;
    font-size: 1rem;
    font-weight: 950;
    line-height: 1.15;
}
.trk-admin-detail-vehicle {
    min-height: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}
.trk-admin-vehicle-img {
    display: block;
    width: 100%;
    max-height: 190px;
    object-fit: contain;
    filter: drop-shadow(0 14px 24px rgba(15,23,42,.14));
}
.trk-admin-detail-modal .modal-body {
    background: #f8fafc;
}
.trk-admin-detail.is-modal {
    border: 0;
    box-shadow: none;
    background: transparent;
}
.trk-admin-detail.is-modal .trk-admin-detail-hero {
    border: 1px solid #e2e8f0;
    border-radius: .8rem;
    min-height: 0;
    margin-bottom: 1rem;
}
.trk-admin-detail.is-modal .trk-admin-detail-body {
    border: 1px solid #e2e8f0;
    border-radius: .8rem;
    background: #fff;
}
.trk-admin-detail.is-modal .trk-admin-vehicle-card {
    background:
        linear-gradient(90deg, rgba(255,255,255,.95), rgba(248,250,252,.8)),
        radial-gradient(circle at 65% 38%, rgba(148,163,184,.18), transparent 38%);
}
.trk-admin-detail.is-modal .trk-admin-detail-vehicle {
    min-height: 230px;
}
.trk-admin-detail.is-modal .trk-admin-vehicle-img {
    max-height: 310px;
}
.trk-admin-capacity-card {
    border-radius: .85rem;
    border: 1px solid #e2e8f0;
    background: rgba(255,255,255,.72);
    padding: .95rem;
    display: grid;
    align-content: space-between;
    gap: .75rem;
}
.trk-admin-capacity-main {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: .75rem;
    align-items: center;
}
.trk-admin-capacity-main strong {
    color: #25364f;
    font-size: 1.45rem;
    line-height: 1;
}
.trk-admin-capacity-main span {
    color: #64748b;
    font-size: .68rem;
    font-weight: 900;
    text-transform: uppercase;
}
.trk-admin-detail-body {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr);
    gap: 1rem;
    padding: 1rem 1.25rem 1.25rem;
}
.trk-admin-panel-section {
    min-width: 0;
}
.trk-admin-panel-title {
    color: #25364f;
    font-size: .82rem;
    font-weight: 950;
    margin-bottom: .6rem;
}
.trk-admin-stat-table {
    display: grid;
    gap: .35rem;
}
.trk-admin-stat-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: .75rem;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    padding: .35rem 0;
    font-size: .74rem;
}
.trk-admin-stat-row span:first-child {
    color: #64748b;
    font-weight: 800;
}
.trk-admin-stat-row strong { color: #25364f; }
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
.trk-admin-route:hover {
    border-style: solid;
    border-color: #bfdbfe;
    background: #f1f5f9;
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
body.dark-mode .trk-admin-table-wrap,
body.dark-mode .trk-admin-roster,
body.dark-mode .trk-admin-detail,
body.dark-mode .trk-admin-capacity-card,
body.dark-mode .trk-admin-vehicle-card,
body.dark-mode .trk-admin-info-box,
body.dark-mode .trk-admin-detail.is-modal .trk-admin-detail-body {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-admin-detail-modal .modal-body {
    background: #101818;
}
body.dark-mode .trk-admin-kpi i {
    background: #101818;
}
body.dark-mode .trk-admin-kpi strong,
body.dark-mode .trk-admin-name,
body.dark-mode .trk-admin-metric strong,
body.dark-mode .trk-admin-info-box strong,
body.dark-mode .trk-admin-route-title,
body.dark-mode .trk-admin-roster-name,
body.dark-mode .trk-admin-detail-name,
body.dark-mode .trk-admin-vehicle-name,
body.dark-mode .trk-admin-capacity-main strong,
body.dark-mode .trk-admin-panel-title,
body.dark-mode .trk-admin-stat-row strong { color: #e2e8f0; }
body.dark-mode .trk-admin-sub,
body.dark-mode .trk-admin-kpi span,
body.dark-mode .trk-admin-metric span,
body.dark-mode .trk-admin-info-box span,
body.dark-mode .trk-admin-info-box small,
body.dark-mode .trk-admin-live,
body.dark-mode .trk-admin-route-meta,
body.dark-mode .trk-admin-roster-title,
body.dark-mode .trk-admin-roster-sub,
body.dark-mode .trk-admin-vehicle-label,
body.dark-mode .trk-admin-capacity-main span,
body.dark-mode .trk-admin-stat-row span:first-child { color: #94a3b8; }
body.dark-mode .trk-admin-metric,
body.dark-mode .trk-admin-route {
    background: #101818;
    border-color: #2d4444;
}
body.dark-mode .trk-admin-roster-item:hover,
body.dark-mode .trk-admin-roster-item.active {
    background: #101818;
    border-color: #2d4444;
}
body.dark-mode .trk-admin-detail-hero {
    background: linear-gradient(110deg, #172121 0%, #172121 50%, #101818 50%, #162121 100%);
    border-color: #2d4444;
}
body.dark-mode .trk-admin-detail.is-modal .trk-admin-detail-hero {
    border-color: #2d4444;
}
body.dark-mode .trk-admin-detail.is-modal .trk-admin-vehicle-card {
    background:
        linear-gradient(90deg, rgba(23,33,33,.96), rgba(16,24,24,.88)),
        radial-gradient(circle at 65% 38%, rgba(148,163,184,.10), transparent 38%);
}
body.dark-mode .trk-admin-detail-overview,
body.dark-mode .trk-admin-driver-strip,
body.dark-mode .trk-admin-unit-band {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-admin-unit-band {
    background:
        linear-gradient(90deg, rgba(23,33,33,.98), rgba(16,24,24,.88)),
        radial-gradient(circle at 72% 42%, rgba(148,163,184,.10), transparent 34%);
}
body.dark-mode .trk-admin-driver-counters span {
    background: #102231;
    color: #93c5fd;
}
body.dark-mode .trk-admin-route:hover {
    background: #132020;
    border-color: #355555;
}
body.dark-mode .trk-admin-stat-row { border-color: #2d4444; }
body.dark-mode .trk-admin-table thead th,
body.dark-mode .trk-admin-table td {
    border-color: #2d4444;
}

/* -- Evaluacion de transportista (score/mini) -- */
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
body.dark-mode .trk-driver-mini { color: #94a3b8; }


@media (max-width: 991.98px) {
    .trk-admin-toolbar { grid-template-columns: 1fr 1fr; }
    .trk-admin-grid.is-panel,
    .trk-admin-detail-body,
    .trk-admin-detail-hero,
    .trk-admin-driver-strip,
    .trk-admin-unit-band { grid-template-columns: 1fr; }
    .trk-admin-contact-layout { grid-template-columns: 1fr; }
    .trk-admin-grid.is-list .trk-admin-card { grid-template-columns: 1fr; }
}
@media (max-width: 575.98px) {
    .trk-admin-toolbar,
    .trk-admin-metrics,
    .trk-admin-detail-unit { grid-template-columns: 1fr; }
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
    width: 100%;
    max-width: none;
    margin: 0;
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
    width: 100%;
    max-width: none;
    margin: .85rem 0 0;
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
    display: grid;
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
    display: grid;
    grid-template-columns: minmax(260px, 1fr) auto auto auto auto auto;
    align-items: end;
}
.trk-catalog-toolbar .btn {
    white-space: nowrap;
}
.trk-catalog-toolbar .trk-catalog-view {
    min-width: 2.35rem;
    height: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0;
}
.trk-catalog-toolbar .trk-catalog-view i {
    font-size: .875rem;
    margin-right: 0 !important;
}
.trk-catalog-search {
    min-width: 0;
}
.trk-catalog-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: .8rem;
}
.trk-catalog-card,
.trk-catalog-group {
    border: 1px solid #e2e8f0;
    border-radius: .75rem;
    background: #fff;
    box-shadow: 0 .12rem .55rem rgba(15, 23, 42, .04);
}
.trk-catalog-card {
    padding: .85rem;
    display: flex;
    flex-direction: column;
    gap: .55rem;
}
.trk-catalog-card-title {
    color: #24304f;
    font-size: .95rem;
    font-weight: 900;
    line-height: 1.18;
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
    font-size: 1.16rem;
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
        grid-template-columns: 1fr;
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
.trk-map-driver-summary {
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .55rem;
    padding: .65rem .75rem;
    margin-bottom: .55rem;
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1.15fr) auto;
    gap: .75rem;
    align-items: stretch;
}
.trk-map-driver-block {
    min-width: 0;
    border-right: 1px solid #e2e8f0;
    padding-right: .75rem;
}
.trk-map-driver-block:last-child {
    border-right: 0;
    padding-right: 0;
}
.trk-map-driver-title {
    display: flex;
    align-items: center;
    gap: .4rem;
    color: #23304d;
    font-size: .82rem;
    font-weight: 800;
    line-height: 1.1;
    text-transform: uppercase;
    overflow: hidden;
}
.trk-map-driver-title span:not(.badge) {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.trk-map-driver-meta {
    color: #64748b;
    font-size: .72rem;
    line-height: 1.35;
    margin-top: .25rem;
}
.trk-map-driver-meta strong {
    color: #475569;
}
.trk-map-driver-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .45rem;
    flex-wrap: wrap;
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
body.dark-mode .trk-map-driver-summary {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-map-driver-block {
    border-right-color: #2d4444;
}
body.dark-mode .trk-map-driver-title,
body.dark-mode .trk-map-driver-meta strong {
    color: #e2e8f0;
}
body.dark-mode .trk-map-driver-meta {
    color: #94a3b8;
}
.trk-operaciones-drawer {
    width: min(98vw, 1880px) !important;
    border-left: 0;
    box-shadow: -1rem 0 2.5rem rgba(15, 23, 42, .18);
}
.trk-operaciones-drawer .offcanvas-header {
    background: #23304d;
    color: #fff;
    padding: .85rem 1rem;
}
.trk-operaciones-drawer .btn-close {
    filter: invert(1);
}
.trk-operaciones-body {
    background: #f5f7fb;
    height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
    padding: .85rem;
}
.trk-ops-shell {
    display: grid;
    grid-template-columns: minmax(310px, 380px) minmax(0, 1fr);
    grid-template-rows: auto minmax(260px, auto) minmax(560px, auto) minmax(270px, auto) minmax(260px, auto);
    grid-template-areas:
        "summary ops"
        "planner map"
        "cedis map"
        "timeline timeline"
        "opportunities opportunities";
    gap: .85rem;
    min-height: 100%;
}
.trk-ops-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: .8rem;
    box-shadow: 0 .5rem 1.25rem rgba(15, 23, 42, .06);
    min-width: 0;
}
.trk-ops-summary {
    grid-area: summary;
    padding: .9rem;
    align-self: start;
    height: fit-content;
}
.trk-ops-planner-panel {
    grid-area: planner;
    padding: .9rem;
    overflow: auto;
    min-height: 260px;
}
.trk-ops-cedis-panel {
    grid-area: cedis;
    padding: .9rem;
    overflow: auto;
    min-height: 260px;
}
.trk-ops-opportunities-panel {
    grid-area: opportunities;
    padding: .85rem;
    overflow: hidden;
    min-height: 260px;
}
.trk-ops-map-panel {
    grid-area: map;
    display: flex;
    flex-direction: column;
    min-height: 560px;
    overflow: hidden;
}
.trk-ops-operator-panel {
    grid-area: ops;
    padding: .9rem;
    overflow: auto;
    display: flex;
    flex-direction: column;
    gap: .75rem;
}
.trk-ops-timeline-panel {
    grid-area: timeline;
    padding: .85rem;
    overflow: hidden;
    min-height: 270px;
}
.trk-ops-title {
    color: #23304d;
    font-weight: 900;
    line-height: 1.15;
    text-transform: uppercase;
    overflow-wrap: anywhere;
}
.trk-ops-sub {
    color: #64748b;
    font-size: .78rem;
}
.trk-ops-kpis {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .45rem;
    margin-top: .85rem;
}
.trk-ops-kpi {
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .6rem;
    padding: .55rem;
}
.trk-ops-kpi span {
    display: block;
    color: #64748b;
    font-size: .62rem;
    font-weight: 800;
    text-transform: uppercase;
}
.trk-ops-kpi b {
    color: #172554;
    font-size: 1.08rem;
    line-height: 1;
}
.trk-ops-info-card {
    border: 1px dashed #bfdbfe;
    background: #f8fbff;
    border-radius: .65rem;
    padding: .65rem;
    margin-top: .7rem;
}
.trk-ops-info-card .label {
    color: #64748b;
    font-size: .62rem;
    font-weight: 900;
    text-transform: uppercase;
}
.trk-ops-info-card .value {
    color: #23304d;
    font-size: .82rem;
    font-weight: 800;
    overflow-wrap: anywhere;
}
.trk-ops-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .45rem;
    margin-top: .75rem;
}
.trk-ops-route-fields {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(110px, .85fr) minmax(100px, .75fr);
    gap: .45rem;
}
.trk-ops-route-field {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: .55rem;
    padding: .55rem;
    min-width: 0;
}
.trk-ops-route-field span {
    display: block;
    color: #64748b;
    font-size: .6rem;
    font-weight: 900;
    text-transform: uppercase;
}
.trk-ops-route-field b {
    display: block;
    color: #23304d;
    font-size: .78rem;
    line-height: 1.2;
    overflow-wrap: anywhere;
}
.trk-ops-map-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .8rem .95rem;
    border-bottom: 1px solid #e2e8f0;
}
.trk-ops-map-title {
    color: #23304d;
    font-size: .92rem;
    font-weight: 900;
}
.trk-ops-map-sub {
    color: #64748b;
    font-size: .72rem;
}
.trk-ops-map-wrap {
    position: relative;
    flex: 1 1 auto;
    min-height: 500px;
    background: #dbeafe;
}
#trkOperacionMap {
    width: 100%;
    height: 100%;
    min-height: 500px;
}
.trk-ops-map-empty {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    color: #64748b;
    background: linear-gradient(135deg, #e0f2fe, #f8fafc);
    text-align: center;
    z-index: 2;
}
.trk-ops-live-card {
    position: absolute;
    left: .85rem;
    right: .85rem;
    bottom: .85rem;
    border: 1px solid #e2e8f0;
    background: rgba(255,255,255,.94);
    backdrop-filter: blur(6px);
    border-radius: .75rem;
    padding: .65rem .85rem;
    box-shadow: 0 .75rem 1.5rem rgba(15,23,42,.12);
    z-index: 3;
}
.trk-ops-live-card .live-main {
    color: #23304d;
    font-weight: 900;
    font-size: .82rem;
}
.trk-ops-live-card .live-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .5rem;
    color: #64748b;
    font-size: .72rem;
    margin-top: .25rem;
}
.trk-ops-timeline {
    display: flex;
    gap: .75rem;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 1.1rem .2rem .2rem;
    min-height: 205px;
    scroll-snap-type: x proximity;
    position: relative;
}
.trk-ops-timeline::before {
    content: "";
    position: absolute;
    top: 1.95rem;
    left: .6rem;
    right: .6rem;
    height: 2px;
    background: #dbeafe;
}
.trk-ops-step {
    flex: 0 0 320px;
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: .7rem;
    padding: 1.05rem .75rem .75rem;
    position: relative;
    scroll-snap-align: start;
}
.trk-ops-step-dot {
    position: absolute;
    top: -.72rem;
    left: .75rem;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: var(--track-color);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    box-shadow: 0 .45rem .85rem rgba(13, 148, 136, .2);
    font-weight: 900;
}
.trk-ops-step-title {
    color: #23304d;
    font-weight: 900;
    line-height: 1.15;
    overflow-wrap: anywhere;
}
.trk-ops-step-meta {
    color: #64748b;
    font-size: .72rem;
    line-height: 1.35;
    margin-top: .25rem;
}
.trk-ops-operator-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
}
.trk-ops-operator-title {
    color: #23304d;
    font-weight: 900;
    text-transform: uppercase;
}
.trk-ops-mini-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .5rem;
}
.trk-ops-mini-card,
.trk-ops-block {
    border: 1px solid #e2e8f0;
    border-radius: .75rem;
    background: #f8fafc;
    padding: .72rem;
}
.trk-ops-mini-card span,
.trk-ops-block-label {
    display: block;
    color: #64748b;
    font-size: .64rem;
    font-weight: 900;
    text-transform: uppercase;
}
.trk-ops-mini-card b {
    color: #172554;
    display: block;
    font-size: .98rem;
    line-height: 1.15;
    margin-top: .12rem;
    overflow-wrap: anywhere;
}
.trk-ops-block-title {
    color: #23304d;
    font-size: .88rem;
    font-weight: 900;
    line-height: 1.2;
    overflow-wrap: anywhere;
}
.trk-ops-block-text {
    color: #64748b;
    font-size: .74rem;
    line-height: 1.35;
    overflow-wrap: anywhere;
}
.trk-ops-live-status {
    border: 1px solid #bbf7d0;
    background: #f0fdf4;
}
.trk-ops-live-status.is-waiting {
    border-color: #fde68a;
    background: #fffbeb;
}
.trk-ops-validation-list {
    display: grid;
    gap: .45rem;
    margin-top: .55rem;
}
.trk-ops-validation-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .6rem;
    border-bottom: 1px solid rgba(148, 163, 184, .2);
    padding-bottom: .42rem;
    color: #23304d;
    font-size: .74rem;
    font-weight: 800;
}
.trk-ops-validation-item:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}
.trk-ops-action-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .45rem;
}
.trk-ops-group-search {
    margin: .65rem 0 .75rem;
}
.trk-ops-groups {
    display: grid;
    gap: .55rem;
}
.trk-ops-group {
    border: 1px solid #e2e8f0;
    border-radius: .7rem;
    background: #f8fafc;
    overflow: hidden;
}
.trk-ops-group-head,
.trk-ops-group-mun {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: .65rem;
    border: 0;
    background: transparent;
    color: #23304d;
    font-size: .78rem;
    font-weight: 900;
    padding: .6rem .72rem;
    text-align: left;
}
.trk-ops-group-mun {
    color: #475569;
    font-size: .74rem;
    font-weight: 700;
    border-top: 1px solid #e2e8f0;
    padding-left: 1.25rem;
}
.trk-ops-opp-list {
    display: flex;
    gap: .6rem;
    overflow-x: auto;
    overflow-y: hidden;
    min-height: 190px;
    padding-bottom: .25rem;
}
.trk-ops-opp-card {
    flex: 0 0 330px;
    border: 1px solid #e2e8f0;
    border-radius: .75rem;
    background: #f8fafc;
    padding: .7rem;
    min-height: 105px;
}
.trk-ops-edit-workspace {
    grid-column: 2;
    grid-row: 2 / -1;
    display: grid;
    grid-template-rows: auto minmax(0, 1fr);
    min-height: 0;
    overflow: hidden;
}
.trk-ops-shell.is-edit-mode .trk-ops-map-panel,
.trk-ops-shell.is-edit-mode .trk-ops-operator-panel,
.trk-ops-shell.is-edit-mode .trk-ops-opportunities-panel,
.trk-ops-shell.is-edit-mode .trk-ops-timeline-panel {
    display: none;
}
.trk-ops-edit-placeholder {
    display: grid;
    place-items: center;
    min-height: 360px;
    color: #64748b;
    text-align: center;
}
body.dark-mode .trk-operaciones-body {
    background: #111827;
}
body.dark-mode .trk-ops-panel,
body.dark-mode .trk-ops-step,
body.dark-mode .trk-ops-mini-card,
body.dark-mode .trk-ops-block,
body.dark-mode .trk-ops-route-field,
body.dark-mode .trk-ops-group,
body.dark-mode .trk-ops-opp-card {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-ops-title,
body.dark-mode .trk-ops-map-title,
body.dark-mode .trk-ops-info-card .value,
body.dark-mode .trk-ops-kpi b,
body.dark-mode .trk-ops-step-title,
body.dark-mode .trk-ops-operator-title,
body.dark-mode .trk-ops-mini-card b,
body.dark-mode .trk-ops-block-title,
body.dark-mode .trk-ops-validation-item,
body.dark-mode .trk-ops-route-field b,
body.dark-mode .trk-ops-group-head,
body.dark-mode .trk-ops-live-card .live-main {
    color: #e2e8f0;
}
body.dark-mode .trk-ops-sub,
body.dark-mode .trk-ops-info-card .label,
body.dark-mode .trk-ops-step-meta,
body.dark-mode .trk-ops-map-sub,
body.dark-mode .trk-ops-mini-card span,
body.dark-mode .trk-ops-block-label,
body.dark-mode .trk-ops-block-text,
body.dark-mode .trk-ops-route-field span,
body.dark-mode .trk-ops-group-mun {
    color: #94a3b8;
}
body.dark-mode .trk-ops-group-mun {
    border-top-color: #2d4444;
}
body.dark-mode .trk-ops-kpi,
body.dark-mode .trk-ops-info-card,
body.dark-mode .trk-ops-live-status {
    background: #1e2d2c;
    border-color: #2d4444;
}
body.dark-mode .trk-ops-map-head {
    border-bottom-color: #2d4444;
}
body.dark-mode .trk-ops-live-card {
    background: rgba(23, 33, 33, .94);
    border-color: #2d4444;
}
@media (max-width: 1199.98px) {
    .trk-ops-shell {
        grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
        grid-template-rows: auto auto minmax(500px, auto) auto auto auto;
        grid-template-areas:
            "summary ops"
            "planner planner"
            "map map"
            "cedis cedis"
            "timeline timeline"
            "opportunities opportunities";
    }
}
@media (max-width: 767.98px) {
    .trk-operaciones-drawer {
        width: 100vw !important;
    }
    .trk-ops-shell {
        display: block;
    }
    .trk-ops-panel {
        margin-bottom: .85rem;
    }
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
.trk-route-day-planner {
    border: 1px solid #bfdbfe;
    background: #f8fbff;
    border-radius: .65rem;
    padding: .72rem;
    margin-top: .75rem;
    display: none;
}
#modalRegistrarRuta.trk-planner-active .trk-route-day-planner {
    display: block;
}
.trk-route-day-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .7rem;
    margin-bottom: .6rem;
}
.trk-route-day-title {
    color: #172554;
    font-weight: 900;
    font-size: .82rem;
}
.trk-route-day-sub {
    color: #64748b;
    font-size: .7rem;
    line-height: 1.25;
}
.trk-route-day-tools {
    display: grid;
    grid-template-columns: repeat(4, minmax(90px, 1fr)) minmax(90px, auto) minmax(120px, auto) minmax(90px, auto);
    gap: .38rem;
    align-items: end;
    margin-bottom: .58rem;
}
.trk-route-day-tools label {
    color: #64748b;
    font-size: .62rem;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: .12rem;
}
.trk-route-day-tools .form-control {
    font-size: .72rem;
    min-height: 2rem;
}
.trk-route-day-tools .btn {
    min-height: 2rem;
    white-space: nowrap;
}
.trk-route-real-summary {
    border: 1px dashed #93c5fd;
    background: #eff6ff;
    color: #1e3a8a;
    border-radius: .55rem;
    padding: .45rem .55rem;
    margin-bottom: .55rem;
    font-size: .68rem;
}
.trk-route-real-summary .trk-real-kpis {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
    margin-top: .28rem;
}
.trk-route-real-summary .trk-real-kpi {
    border: 1px solid #bfdbfe;
    background: #fff;
    border-radius: .45rem;
    padding: .22rem .45rem;
    font-weight: 800;
}
.trk-route-real-summary .trk-real-warning {
    color: #92400e;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: .4rem;
    padding: .2rem .4rem;
    margin-top: .25rem;
}
.trk-route-day-list {
    display: grid;
    gap: .45rem;
    max-height: 360px;
    overflow-y: auto;
    padding-right: .12rem;
}
.trk-route-day-card {
    border: 1px solid #dbeafe;
    background: #fff;
    border-radius: .55rem;
    padding: .58rem;
}
.trk-route-day-card.is-overloaded {
    border-color: #f59e0b;
    box-shadow: inset 4px 0 0 #f59e0b;
}
.trk-route-day-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .45rem;
    margin-bottom: .45rem;
}
.trk-route-day-date {
    color: #25364f;
    font-size: .78rem;
    font-weight: 900;
}
.trk-route-day-items {
    display: grid;
    gap: .35rem;
}
.trk-route-day-item {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: start;
    gap: .45rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: .48rem;
    padding: .45rem .5rem;
    min-width: 0;
}
.trk-route-day-start {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    align-items: start;
    gap: .5rem;
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    border-radius: .5rem;
    padding: .5rem .6rem;
    min-width: 0;
}
.trk-route-day-start-icon {
    width: 1.55rem;
    height: 1.55rem;
    display: inline-grid;
    place-items: center;
    border-radius: 999px;
    background: #1d4ed8;
    color: #fff;
    font-size: .72rem;
    box-shadow: 0 6px 14px rgba(29, 78, 216, .2);
}
.trk-route-day-start-label {
    color: #1d4ed8;
    font-size: .62rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.trk-route-day-start-title {
    color: #25364f;
    font-size: .76rem;
    font-weight: 900;
    line-height: 1.15;
}
.trk-route-day-item.is-overflow {
    border-color: #f59e0b;
    background: #fff7ed;
}
.trk-route-day-timebar {
    display: flex;
    flex-wrap: wrap;
    gap: .25rem .4rem;
    margin-top: .25rem;
}
.trk-route-day-timechip {
    display: inline-flex;
    align-items: center;
    gap: .18rem;
    border: 1px solid #dbeafe;
    background: #fff;
    color: #334155;
    border-radius: 999px;
    padding: .12rem .42rem;
    font-size: .62rem;
    font-weight: 800;
}
.trk-route-day-timechip.is-pinned {
    border-color: #c4b5fd;
    background: #f5f3ff;
    color: #5b21b6;
}
.trk-route-day-timechip.is-warning {
    border-color: #fcd34d;
    background: #fffbeb;
    color: #92400e;
}
.trk-route-day-actions {
    display: inline-flex;
    gap: .25rem;
    align-items: center;
}
.trk-route-day-num {
    width: 1.45rem;
    height: 1.45rem;
    display: inline-grid;
    place-items: center;
    border-radius: 999px;
    background: var(--track-color);
    color: #fff;
    font-size: .72rem;
    font-weight: 900;
    box-shadow: 0 6px 14px rgba(13, 148, 136, .22);
}
.trk-route-day-item-title {
    color: #25364f;
    font-size: .74rem;
    font-weight: 900;
    line-height: 1.15;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.trk-route-day-item-meta {
    color: #64748b;
    font-size: .64rem;
    line-height: 1.25;
}
.trk-route-event-list {
    display: grid;
    gap: .35rem;
    margin-top: .55rem;
}
.trk-route-event-item {
    border: 1px dashed #bfdbfe;
    background: #eef6ff;
    border-radius: .5rem;
    padding: .45rem .55rem;
    font-size: .68rem;
    color: #334155;
}
body.dark-mode .trk-route-day-planner {
    background: #101c24;
    border-color: #1e3a5f;
}
body.dark-mode .trk-route-day-title,
body.dark-mode .trk-route-day-date,
body.dark-mode .trk-route-day-item-title {
    color: #e2e8f0;
}
body.dark-mode .trk-route-day-sub,
body.dark-mode .trk-route-day-tools label,
body.dark-mode .trk-route-day-item-meta {
    color: #b0cece;
}
body.dark-mode .trk-route-day-card,
body.dark-mode .trk-route-day-item {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-route-day-start {
    background: #102231;
    border-color: #1e3a5f;
}
body.dark-mode .trk-route-day-start-title {
    color: #e2e8f0;
}
body.dark-mode .trk-route-day-start-label {
    color: #93c5fd;
}
body.dark-mode .trk-route-day-item.is-overflow {
    background: #2b2414;
    border-color: #b45309;
}
body.dark-mode .trk-route-day-timechip {
    background: #111827;
    border-color: #334155;
    color: #d8eeee;
}
body.dark-mode .trk-route-day-timechip.is-pinned {
    background: #221b38;
    border-color: #7c3aed;
    color: #ddd6fe;
}
body.dark-mode .trk-route-day-timechip.is-warning {
    background: #2b2414;
    border-color: #b45309;
    color: #fde68a;
}
body.dark-mode .trk-route-real-summary {
    background: #102231;
    border-color: #1e3a5f;
    color: #dbeafe;
}
body.dark-mode .trk-route-real-summary .trk-real-kpi {
    background: #111827;
    border-color: #334155;
    color: #d8eeee;
}
body.dark-mode .trk-route-real-summary .trk-real-warning {
    background: #2b2414;
    border-color: #b45309;
    color: #fde68a;
}
body.dark-mode .trk-route-event-item {
    background: #102231;
    border-color: #1e3a5f;
    color: #cbd5e1;
}
.trk-route-opportunities {
    border: 1px solid #bfdbfe;
    background: #f8fbff;
    border-radius: .65rem;
    padding: .72rem;
    margin-top: .75rem;
}
.trk-route-opportunities-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .7rem;
    margin-bottom: .6rem;
}
.trk-route-opportunities-title {
    color: #172554;
    font-weight: 900;
    font-size: .82rem;
}
.trk-route-opportunities-sub {
    color: #64748b;
    font-size: .7rem;
    line-height: 1.25;
}
.trk-route-opportunities-tools {
    display: grid;
    grid-template-columns: 88px 88px minmax(0, 1fr) auto;
    gap: .38rem;
    align-items: end;
    margin-bottom: .58rem;
}
.trk-route-opportunities-tools label {
    color: #64748b;
    font-size: .62rem;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: .12rem;
}
.trk-route-opportunities-tools .form-control,
.trk-route-opportunities-tools .form-select {
    font-size: .72rem;
    min-height: 2rem;
}
.trk-route-opportunities-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .4rem;
    margin-bottom: .58rem;
}
.trk-route-opportunities-summary .mini-kpi {
    border: 1px solid #dbeafe;
    background: #fff;
    border-radius: .5rem;
    padding: .42rem .5rem;
    min-width: 0;
}
.trk-route-opportunities-summary .mini-kpi span {
    display: block;
    color: #64748b;
    font-size: .6rem;
    font-weight: 800;
    text-transform: uppercase;
}
.trk-route-opportunities-summary .mini-kpi b {
    color: #1e293b;
    font-size: .86rem;
}
.trk-route-opportunities-list {
    display: grid;
    gap: .45rem;
    max-height: 330px;
    overflow-y: auto;
    padding-right: .12rem;
}
.trk-route-mini-chat {
    border: 1px solid #c7f5ef;
    background: #fff;
    border-radius: .65rem;
    margin-top: .75rem;
    overflow: hidden;
}
.trk-route-mini-chat-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .65rem;
    padding: .62rem .72rem;
    border-bottom: 1px solid #d5f3ed;
    background: #f0fdfa;
}
.trk-route-mini-chat-title {
    color: #172554;
    font-size: .82rem;
    font-weight: 900;
    text-transform: uppercase;
}
.trk-route-mini-chat-sub {
    color: #64748b;
    font-size: .69rem;
    line-height: 1.25;
    overflow-wrap: anywhere;
}
.trk-route-mini-chat-messages {
    min-height: 170px;
    max-height: 240px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: .5rem;
    padding: .7rem;
    background: #f8fafc;
}
.trk-route-mini-chat-messages .chat-bubble-wrap {
    max-width: 92%;
}
.trk-route-mini-chat-compose {
    display: flex;
    gap: .5rem;
    align-items: flex-end;
    padding: .62rem .72rem .72rem;
    border-top: 1px solid #e2e8f0;
    background: #fff;
}
.trk-route-mini-chat-compose .chat-textarea {
    min-height: 42px;
    max-height: 86px;
}
.trk-route-mini-chat-compose .chat-send-btn {
    width: 40px;
    height: 42px;
}
.trk-route-opportunity {
    border: 1px solid #dbeafe;
    background: #fff;
    border-radius: .55rem;
    padding: .6rem;
    display: grid;
    gap: .35rem;
}
.trk-route-opportunity.is-recomendado { border-left: 4px solid #16a34a; }
.trk-route-opportunity.is-advertencia { border-left: 4px solid #f59e0b; }
.trk-route-opportunity.is-no_recomendado { border-left: 4px solid #ef4444; }
.trk-route-opportunity-title {
    color: #25364f;
    font-size: .78rem;
    font-weight: 900;
    line-height: 1.2;
}
.trk-route-opportunity-meta,
.trk-route-opportunity-reasons {
    color: #64748b;
    font-size: .68rem;
    line-height: 1.35;
}
.trk-route-opportunity-badges {
    display: flex;
    align-items: center;
    gap: .25rem;
    flex-wrap: wrap;
}
.trk-route-opportunity-badges .badge { font-size: .6rem; }
.trk-route-opportunity-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    flex-wrap: wrap;
}
body.dark-mode .trk-route-opportunities {
    background: #101c24;
    border-color: #1e3a5f;
}
body.dark-mode .trk-route-mini-chat,
body.dark-mode .trk-route-mini-chat-compose {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-route-mini-chat-head {
    background: #112222;
    border-bottom-color: #244644;
}
body.dark-mode .trk-route-opportunities-title,
body.dark-mode .trk-route-mini-chat-title,
body.dark-mode .trk-route-opportunity-title {
    color: #e2e8f0;
}
body.dark-mode .trk-route-opportunities-sub,
body.dark-mode .trk-route-mini-chat-sub,
body.dark-mode .trk-route-opportunity-meta,
body.dark-mode .trk-route-opportunity-reasons,
body.dark-mode .trk-route-opportunities-tools label {
    color: #b0cece;
}
body.dark-mode .trk-route-mini-chat-messages {
    background: #101818;
}
body.dark-mode .trk-route-opportunities-summary .mini-kpi,
body.dark-mode .trk-route-opportunity {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-route-opportunities-summary .mini-kpi b {
    color: #f8fafc;
}
.trk-route-list-tools {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(135px, .75fr) minmax(180px, 1.25fr);
    gap: .45rem;
    margin-bottom: .5rem;
}
.trk-route-list-tools .form-select,
.trk-route-list-tools .form-control {
    font-size: .75rem;
}
.trk-route-list-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .4rem;
    margin: -.2rem 0 .5rem;
    flex-wrap: wrap;
}
.trk-route-list-actions .btn {
    font-size: .72rem;
    font-weight: 800;
    white-space: nowrap;
}
.trk-warning-groups {
    color: #1f2937;
    font-size: 14px;
    line-height: 1.5;
    text-align: left;
}
.trk-warning-group {
    border: 1px solid #e5e7eb;
    border-radius: .65rem;
    background: #fff;
    padding: .85rem;
    margin-bottom: .7rem;
}
.trk-warning-group-title {
    color: #111827;
    font-weight: 900;
    font-size: 1rem;
    margin-bottom: .35rem;
}
.trk-warning-group-message,
.trk-warning-group-action {
    color: #1f2937;
    margin-bottom: .5rem;
}
.trk-warning-group-action {
    background: #ecfeff;
    border: 1px solid #a5f3fc;
    border-radius: .5rem;
    padding: .5rem .65rem;
    font-weight: 700;
}
.trk-warning-credit-list {
    display: grid;
    gap: .35rem;
    margin: .55rem 0;
}
.trk-warning-credit-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    border: 1px solid #e5e7eb;
    border-radius: .5rem;
    padding: .38rem .55rem;
    background: #f9fafb;
}
.trk-warning-credit-item-ok {
    border-color: #86efac;
    background: #f0fdf4;
}
.trk-warning-credit-main {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.trk-warning-credit-actions {
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-shrink: 0;
}
.trk-warning-credit-ok {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.35rem;
    height: 1.35rem;
    border-radius: 999px;
    color: #16a34a;
    background: #dcfce7;
    border: 1px solid #86efac;
    font-size: .78rem;
}
.trk-warning-pin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.65rem;
    height: 1.65rem;
    padding: 0;
    border-radius: .5rem;
}
.trk-warning-delete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.65rem;
    height: 1.65rem;
    padding: 0;
    border-radius: .5rem;
}
.trk-warning-credit-item strong {
    color: #111827;
}
.trk-warning-credit-item small {
    color: #6b7280;
    font-size: .75rem;
}
.trk-warning-group details summary {
    cursor: pointer;
    color: #0f766e;
    font-weight: 900;
    margin-top: .4rem;
}
body.dark-mode .trk-warning-group {
    background: #172121;
    border-color: #2d4444;
}
body.dark-mode .trk-warning-groups,
body.dark-mode .trk-warning-group-message,
body.dark-mode .trk-warning-group-action {
    color: #e5e7eb;
}
body.dark-mode .trk-warning-group-title {
    color: #f8fafc;
}
body.dark-mode .trk-warning-credit-item {
    background: #101c24;
    border-color: #2d4444;
}
body.dark-mode .trk-warning-credit-item-ok {
    background: #0f2f25;
    border-color: #22c55e;
}
body.dark-mode .trk-warning-credit-item strong {
    color: #f8fafc;
}
body.dark-mode .trk-warning-credit-item small {
    color: #cbd5e1;
}
body.dark-mode .trk-warning-credit-ok {
    color: #bbf7d0;
    background: #14532d;
    border-color: #22c55e;
}
body.dark-mode .trk-warning-group-action {
    background: #102f34;
    border-color: #155e75;
}
#rutaCreditosList {
    max-height: 420px !important;
}
#modalRegistrarRuta.trk-planner-active .modal-dialog {
    max-width: calc(100vw - 1rem);
    height: calc(100vh - .5rem);
    margin: .25rem auto;
}
#modalRegistrarRuta.trk-planner-active .modal-content {
    height: 100%;
    min-height: 0;
}
#modalRegistrarRuta.trk-planner-active .modal-header,
#modalRegistrarRuta.trk-planner-active .modal-footer {
    flex-shrink: 0;
}
#modalRegistrarRuta.trk-planner-active .modal-body {
    display: grid;
    grid-template-columns: minmax(420px, 580px) minmax(680px, 1fr);
    grid-template-rows: auto auto auto auto auto minmax(320px, auto);
    grid-template-areas:
        "header    header"
        "transport map"
        "add       map"
        "planner   map"
        "planner   tracking"
        "planner   list";
    align-items: start;
    gap: .75rem;
    flex: 1 1 auto;
    height: auto;
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    padding: .75rem;
    scrollbar-gutter: stable;
}
#modalRegistrarRuta.trk-planner-active #rutaCancelacionInfo {
    grid-column: 1 / -1;
}
#modalRegistrarRuta.trk-planner-active #trkRouteHeaderSection {
    grid-area: header;
    display: grid !important;
    grid-template-columns: minmax(260px, 1.4fr) repeat(3, minmax(160px, .85fr));
    gap: .75rem;
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .75rem;
    padding: .85rem;
    margin-bottom: 0 !important;
}
#modalRegistrarRuta.trk-planner-active #trkRouteHeaderSection > [class*="col-"] {
    width: 100%;
    max-width: 100%;
    padding: 0;
}
body.dark-mode #modalRegistrarRuta.trk-planner-active #trkRouteHeaderSection {
    background: #172121;
    border-color: #2d4444;
}
#modalRegistrarRuta.trk-planner-active #secTransportistaRuta {
    grid-area: transport;
    margin-bottom: 0 !important;
}
#modalRegistrarRuta.trk-planner-active #secAgregarCredito {
    grid-area: add;
    margin-bottom: 0 !important;
}
#modalRegistrarRuta.trk-planner-active #secAgregarCredito,
#modalRegistrarRuta.trk-planner-active #trkPlannerPanel,
#modalRegistrarRuta.trk-planner-active #trkTrackingSection,
#modalRegistrarRuta.trk-planner-active #trkRouteListSection {
    border: 1px solid #dbeafe;
    background: #fff;
    border-radius: .75rem;
    padding: .85rem;
}
body.dark-mode #modalRegistrarRuta.trk-planner-active #secAgregarCredito,
body.dark-mode #modalRegistrarRuta.trk-planner-active #trkPlannerPanel,
body.dark-mode #modalRegistrarRuta.trk-planner-active #trkTrackingSection,
body.dark-mode #modalRegistrarRuta.trk-planner-active #trkRouteListSection {
    background: #172121;
    border-color: #2d4444;
}
#modalRegistrarRuta.trk-planner-active #trkPlannerPanel {
    grid-area: planner;
    align-self: stretch;
    min-height: 0;
    overflow: visible;
    margin-bottom: 0 !important;
}
#modalRegistrarRuta.trk-planner-active #trkRouteListSection {
    grid-area: list;
    align-self: stretch;
    display: flex;
    flex-direction: column;
    min-height: 0;
    margin-top: 11rem !important;
    margin-bottom: 0 !important;
    min-width: 0;
    padding-top: 1.25rem;
}
#modalRegistrarRuta.trk-planner-active #trkTrackingSection {
    grid-area: tracking;
    min-height: 0;
    max-height: none;
    overflow: hidden;
    margin-bottom: 0 !important;
    min-width: 0;
    padding-bottom: 1rem;
}
#modalRegistrarRuta.trk-planner-active #trkMapSection {
    grid-area: map;
    align-self: stretch;
    position: relative;
    top: auto;
    display: flex;
    flex-direction: column;
    height: clamp(620px, calc(100vh - 10rem), 760px);
    min-height: 620px;
    max-height: 760px;
    margin-top: 0;
    margin-bottom: 0 !important;
}
#modalRegistrarRuta.trk-planner-active #trkPlannerCedisBox {
    grid-area: cedis;
    align-self: stretch;
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .55rem;
    padding: .75rem;
    min-height: 0;
    overflow-y: auto;
    margin-bottom: 0 !important;
}
body.dark-mode #modalRegistrarRuta.trk-planner-active #trkPlannerCedisBox {
    background: #172121;
    border-color: #2d4444;
}
#modalRegistrarRuta:not(.trk-planner-active) #trkMapTransportSummary {
    display: none !important;
}
#modalRegistrarRuta.trk-planner-active #secTransportistaRuta .row > [class*="col-"] {
    width: 100%;
    flex: 0 0 100%;
    max-width: 100%;
}
#modalRegistrarRuta.trk-planner-active #trkRouteListTools {
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    flex-shrink: 0;
    margin-bottom: .75rem;
}
#modalRegistrarRuta.trk-planner-active #trkRouteListSection > .d-flex:first-child {
    flex-shrink: 0;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: .65rem;
    margin-bottom: .65rem !important;
}
body.dark-mode #modalRegistrarRuta.trk-planner-active #trkRouteListSection > .d-flex:first-child {
    border-bottom-color: #2d4444;
}
#modalRegistrarRuta.trk-planner-active #trkRouteListTools .input-group {
    grid-column: 1 / -1;
}
#modalRegistrarRuta.trk-planner-active #trackMapContainer,
#modalRegistrarRuta.trk-planner-active #trackMap {
    height: 100%;
    min-height: 560px;
}
#modalRegistrarRuta.trk-planner-active #trackMapContainer {
    flex: 1 1 auto;
    min-height: 0;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList {
    flex: 1 1 auto;
    min-height: 260px;
    max-height: 420px !important;
    display: block;
    overflow-x: hidden;
    overflow-y: auto;
    padding: .5rem;
    position: relative;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList::before {
    content: none;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row {
    max-width: none;
    min-height: 0;
    margin-bottom: .45rem;
    align-items: flex-start;
    gap: .5rem;
    flex-direction: row;
    flex-wrap: wrap;
    position: static;
    z-index: 1;
    padding: .7rem .75rem;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row > .d-flex.flex-column {
    flex: 1 1 420px;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row .fw-semibold {
    white-space: normal !important;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row .drag-handle {
    position: static;
    padding-top: .35rem;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row .orden-num {
    position: static;
    min-width: 1.8rem;
    width: 1.8rem;
    height: 1.8rem;
    border-radius: 999px;
    background: var(--track-color);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 .35rem .75rem rgba(13, 148, 136, .22);
    border: 2px solid #fff;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row .select-conf-gestor {
    width: auto;
    max-width: 130px !important;
    margin-left: .25rem !important;
    margin-top: .2rem;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row .btn-remove-cred {
    margin-left: .25rem;
}
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row > .btn-pin-ubicacion,
#modalRegistrarRuta.trk-planner-active #rutaCreditosList .track-credito-row > .btn-remove-cred {
    position: static;
    margin-top: .2rem;
}
#modalRegistrarRuta.trk-planner-active .trk-route-opportunities-list {
    max-height: 260px;
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
body.dark-mode #modalRegistrarRuta.trk-planner-active #rutaCreditosList::before {
    background: #2d4444;
}
@media (max-width: 991.98px) {
    #modalRegistrarRuta.trk-planner-active .modal-body {
        display: block;
        height: auto;
        overflow: auto;
        padding: 1rem;
    }
    #modalRegistrarRuta.trk-planner-active #trkMapSection {
        position: static;
    }
    #modalRegistrarRuta.trk-planner-active #trackMapContainer,
    #modalRegistrarRuta.trk-planner-active #trackMap {
        height: 520px;
        min-height: 520px;
    }
    .trk-map-driver-summary {
        grid-template-columns: 1fr;
    }
    .trk-map-driver-block {
        border-right: 0;
        border-bottom: 1px solid #e2e8f0;
        padding-right: 0;
        padding-bottom: .65rem;
    }
    body.dark-mode .trk-map-driver-block {
        border-bottom-color: #2d4444;
    }
}
@media (max-width: 575.98px) {
    .trk-route-list-tools {
        grid-template-columns: 1fr;
    }
    .trk-route-day-tools,
    .trk-route-opportunities-tools,
    .trk-route-day-list,
    .trk-route-opportunities-summary {
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
.eta-row {
    display: none !important;
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
.trk-badge-entregada_cedis { background: #dbeafe; color: #1d4ed8; }
.trk-badge-incidencia  { background: #fee2e2; color: #b91c1c; }
#modalRegistrarRuta.trk-planner-active .trk-timeline {
    display: flex;
    gap: .7rem;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 1.05rem .25rem .9rem;
    margin-bottom: .25rem;
    scroll-snap-type: x proximity;
}
#modalRegistrarRuta.trk-planner-active .trk-timeline::before {
    left: .4rem;
    right: .4rem;
    top: 1.48rem;
    bottom: auto;
    width: auto;
    height: 2px;
}
#modalRegistrarRuta.trk-planner-active .trk-step {
    flex: 0 0 260px;
    border: 1px solid #e2e8f0;
    background: #fff;
    border-radius: .5rem;
    padding: 1rem .7rem .7rem;
    scroll-snap-align: start;
}
#modalRegistrarRuta.trk-planner-active .trk-step-dot {
    left: .75rem;
    top: -.55rem;
    box-shadow: 0 .35rem .75rem rgba(15, 23, 42, .12);
}
body.dark-mode #modalRegistrarRuta.trk-planner-active .trk-step {
    background: #1e293b;
    border-color: #334155;
}
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

.swal2-container.trk-swal-over-modal {
    z-index: 30000 !important;
}

.swal2-container.trk-swal-over-modal input,
.swal2-container.trk-swal-over-modal textarea,
.swal2-container.trk-swal-over-modal select {
    pointer-events: auto !important;
    user-select: text !important;
}

.trk-plan-editor-overlay {
    position: fixed;
    inset: 0;
    z-index: 30020;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(15, 23, 42, .42);
}
.trk-plan-editor-card {
    width: min(520px, calc(100vw - 2rem));
    background: #fff;
    border-radius: .75rem;
    box-shadow: 0 1.5rem 4rem rgba(15, 23, 42, .28);
    border: 1px solid var(--track-border);
    overflow: hidden;
}
body.dark-mode .trk-plan-editor-card {
    background: #182424;
    color: #e8f0f0;
    border-color: #334444;
}
.trk-plan-editor-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.1rem .75rem;
    border-bottom: 1px solid var(--track-border);
}
.trk-plan-editor-body {
    padding: 1rem 1.1rem;
}
.trk-plan-editor-footer {
    display: flex;
    justify-content: flex-end;
    gap: .65rem;
    padding: .8rem 1.1rem 1rem;
    background: rgba(15, 23, 42, .03);
}
body.dark-mode .trk-plan-editor-footer {
    background: rgba(255,255,255,.03);
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
    : 'Créditos disponibles para planeación de ruta física';
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
                <div class="trk-section-title">Créditos disponibles</div>
                <div class="trk-section-desc">Consulta y filtra motos listas para planear rutas.</div>
            </div>
            <div class="trk-section-footer">
                <span>Ver créditos</span>
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
                <span>Ver catálogos</span>
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
                <span>Ver operación</span>
                <span class="trk-section-count" data-section-count="badgeOperacionTransportistas">0</span>
            </div>
        </button>
    </div>

    <div class="<?= $trackingIsPlaneacion ? 'trk-planeacion-shell' : 'trk-module-content'; ?>">

    <ul class="nav nav-tabs track-tabs mb-3 d-none" id="trackMainTabs">
        <li class="nav-item">
            <button class="nav-link<?= $trackingInitialSection === 'creditos' ? ' active' : ''; ?>" id="tabCreditosBtn" data-bs-toggle="tab" data-bs-target="#tabCreditos">
                <i class="fa-solid fa-motorcycle me-1"></i>Pendientes de recolección
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
                                    <th>Créditos</th>
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
                        <select class="form-select form-select-sm" id="trkListaFiltroDireccion">
                            <option value="">Todas las direcciones</option>
                            <option value="sin_direccion">Sin direccion</option>
                        </select>
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
                                    <th>Créditos</th>
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
                <div class="trk-admin-kpis">
                    <div class="trk-admin-kpi" data-tone="info"><i class="fa-solid fa-warehouse"></i><div><span>CEDIS activos</span><strong id="statAgenciasTracking">0</strong></div></div>
                    <div class="trk-admin-kpi" data-tone="success"><i class="fa-solid fa-user-check"></i><div><span>Transportistas</span><strong id="statTransportistasInternos">0</strong></div></div>
                    <div class="trk-admin-kpi" data-tone="warning"><i class="fa-solid fa-clipboard-check"></i><div><span>Almacenistas</span><strong id="statTransportistasExternos">0</strong></div></div>
                    <div class="trk-admin-kpi" data-tone="info"><i class="fa-solid fa-address-book"></i><div><span>Directorio total</span><strong id="statCatalogoTotal">0</strong></div></div>
                </div>

                <div class="trk-admin-toolbar trk-catalog-toolbar">
                    <div class="trk-catalog-search">
                        <label class="form-label mb-1 small fw-semibold">Buscar en directorio</label>
                        <input type="search" class="form-control form-control-sm" id="trkCatalogoBuscar"
                               placeholder="CEDIS, transportista, empresa, estado..." autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="trkCatalogoActualizar">
                        <i class="fa-solid fa-rotate me-1"></i>Actualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-label-info" id="btnNuevoCedisTracking">
                        <i class="fa-solid fa-plus me-1"></i>Registrar CEDIS <i class="fa-solid fa-warehouse ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-label-info" id="btnNuevoTransportistaTracking">
                        <i class="fa-solid fa-plus me-1"></i>Registrar Usuario <i class="fa-solid fa-id-card-clip ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-label-info" id="btnNuevaUnidadTracking">
                        <i class="fa-solid fa-plus me-1"></i>Registrar Unidad <i class="fa-solid fa-truck ms-1"></i>
                    </button>
                    <div class="btn-group" role="group" aria-label="Vista catálogos">
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
                <div id="trkCatalogoTabla" class="trk-catalog-table trk-admin-table-wrap<?= $trackingCatalogoDefaultView !== 'tabla' ? ' d-none' : ''; ?>">
                    <div class="table-responsive">
                        <table id="tablaAgenciasTracking" class="table table-hover mb-0 w-100 trk-operacion-table trk-admin-table" style="font-size:.82rem;">
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
                        <table id="tablaTransportistasTracking" class="table table-hover mb-0 w-100 trk-operacion-table trk-admin-table" style="font-size:.82rem;">
                            <thead>
                                <tr>
                                    <th>Usuario operativo</th>
                                    <th>Rol / tipo</th>
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
                    <div class="trk-admin-kpi" data-tone="info"><i class="fa-solid fa-id-card"></i><div><span>Activos</span><strong id="trkOpKpiActivos">0</strong></div></div>
                    <div class="trk-admin-kpi" data-tone="success"><i class="fa-solid fa-circle-check"></i><div><span>Disponibles</span><strong id="trkOpKpiDisponibles">0</strong></div></div>
                    <div class="trk-admin-kpi" data-tone="info"><i class="fa-solid fa-route"></i><div><span>En ruta</span><strong id="trkOpKpiRuta">0</strong></div></div>
                    <div class="trk-admin-kpi" data-tone="warning"><i class="fa-solid fa-calendar-day"></i><div><span>Programados</span><strong id="trkOpKpiProgramados">0</strong></div></div>
                    <div class="trk-admin-kpi" data-tone="warning"><i class="fa-solid fa-triangle-exclamation"></i><div><span>Advertencia</span><strong id="trkOpKpiAdvertencia">0</strong></div></div>
                    <div class="trk-admin-kpi" data-tone="danger"><i class="fa-solid fa-boxes-stacked"></i><div><span>Saturados</span><strong id="trkOpKpiSaturados">0</strong></div></div>
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
     Modal  -  Usuario operativo tracking
========================================================== -->
<div class="modal fade" id="modalTransportistaTracking" tabindex="-1" aria-labelledby="modalTransportistaTrackingLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalTransportistaTrackingLabel">
                    <i class="fa-solid fa-id-card-clip me-2" style="color:var(--track-color);"></i>Registrar Usuario Operativo
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="transportistaIdTracking" value="">
                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Nombre usuario operativo *</label>
                        <input type="text" class="form-control form-control-sm" id="transportistaNombreTracking" maxlength="180">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">Rol operativo</label>
                        <select class="form-select form-select-sm" id="transportistaActorTracking">
                            <option value="transportista">Transportista</option>
                            <option value="almacenista">Almacenista</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
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
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar Usuario
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
     Modal  -  Detalle completo de transportista
========================================================== -->
<div class="modal fade" id="modalOperacionTransportistaDetalle" tabindex="-1" aria-labelledby="modalOperacionTransportistaDetalleLabel">
    <div class="modal-dialog modal-dialog-scrollable trk-admin-detail-modal-dialog">
        <div class="modal-content trk-admin-detail-modal">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalOperacionTransportistaDetalleLabel">
                    <i class="fa-solid fa-truck-fast me-2" style="color:var(--track-color);"></i>Datos completos del transportista
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="trkOpDetalleModalBody"></div>
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
                    <i class="fa-solid fa-route me-2"></i>Registrar ruta de recolección
                </h5>
                <button type="button" class="btn-close" id="btnCerrarModal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger d-none py-2 px-3 mb-3" id="rutaCancelacionInfo"></div>

                <!-- -- Seccion 1: Datos de la ruta -- -->
                <div class="row g-3 mb-3" id="trkRouteHeaderSection">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">
                            Nombre de ruta <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm"
                               id="rutaNombre" maxlength="100" placeholder="Ej. Ruta GDL Norte Junio" style="text-transform:uppercase;">
                        <div class="form-text small mt-1" id="rutaNombreStatus"></div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">
                            Fecha de inicio <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm"
                               id="rutaFecha" min="">
                        <div class="form-text text-muted" style="font-size:.72rem;">
                            Mínimo <span id="rutaDiasMinimosTxt">2</span> día(s) desde hoy - Deja una fecha tentativa si aún no está definida para que puedas guardar correctamente el borrador de la ruta.
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold">
                            Fecha final <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm"
                               id="rutaFechaFin" min="">
                        <div class="form-text text-muted" style="font-size:.72rem;">
                            Sin límite máximo; no puede ser anterior a la fecha de inicio.
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
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
                        Agregar crédito a la ruta
                    </label>
                    <!-- Filtros de ubicación para créditos -->
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
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-label-primary w-100" id="btnAgregarTodosUbicacion" disabled
                                    title="Agrega todos los créditos restantes del estado o municipio seleccionado">
                                <i class="fa-solid fa-layer-group me-1"></i>Agregar todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2 flex-shrink-0" id="btnLimpiarFiltrosCrd"
                                    title="Limpiar filtros" aria-label="Limpiar filtros">
                                <i class="fa-solid fa-eraser"></i>
                            </button>
                        </div>
                    </div>
                    <div class="input-group input-group-sm">
                        <select class="form-select trk-select-buscable" id="rutaCreditoSelect">
                            <option value="">Buscar por crédito, modelo, VIN...</option>
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
                    <div class="trk-route-opportunities" id="trkRouteOpportunities">
                        <div class="trk-route-opportunities-head">
                            <div>
                                <div class="trk-route-opportunities-title">
                                    <i class="fa-solid fa-road-circle-check me-1" style="color:var(--track-color);"></i>
                                    Créditos sobre la ruta
                                </div>
                                <div class="trk-route-opportunities-sub">
                                    Candidatos cercanos al recorrido, capacidad y destino del transportista.
                                </div>
                            </div>
                            <span class="badge bg-label-primary" id="trkRouteOppCount">0</span>
                        </div>
                        <div class="trk-route-opportunities-tools">
                            <div>
                                <label for="trkOppRadioKm">Radio km</label>
                                <input type="number" class="form-control form-control-sm" id="trkOppRadioKm"
                                       min="1" max="80" step="1" value="10">
                            </div>
                            <div>
                                <label for="trkOppLimit">Limite</label>
                                <input type="number" class="form-control form-control-sm" id="trkOppLimit"
                                       min="5" max="100" step="5" value="30">
                            </div>
                            <div>
                                <label for="trkOppNivel">Nivel</label>
                                <select class="form-select form-select-sm" id="trkOppNivel">
                                    <option value="">Todos</option>
                                    <option value="recomendado">Recomendado</option>
                                    <option value="advertencia">Advertencia</option>
                                    <option value="no_recomendado">No recomendado</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-sm btn-label-primary" id="trkOppRefresh" title="Actualizar candidatos">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                        <div class="trk-route-opportunities-summary d-none" id="trkRouteOppSummary"></div>
                        <div class="trk-route-opportunities-list" id="trkRouteOppList">
                            <div class="text-center text-muted small py-2">
                                Guarda o abre una ruta para consultar sugerencias sobre el recorrido.
                            </div>
                        </div>
                    </div>
                    <div class="trk-route-mini-chat d-none" id="trkRouteMiniChat">
                        <div class="trk-route-mini-chat-head">
                            <div style="min-width:0;">
                                <div class="trk-route-mini-chat-title">
                                    <i class="fa-solid fa-comments me-1" style="color:var(--track-color);"></i>
                                    Chat operativo de ruta
                                </div>
                                <div class="trk-route-mini-chat-sub" id="trkRouteMiniChatSub">
                                    Conversacion general con transportista y torre de control.
                                </div>
                            </div>
                            <div class="d-inline-flex align-items-center gap-1">
                                <span class="chat-ws-dot chat-ws-off" id="trkRouteMiniChatWsDot"></span>
                                <button type="button" class="btn btn-sm btn-label-primary" id="trkRouteMiniChatExpand" title="Extender chat">
                                    <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                                </button>
                            </div>
                        </div>
                        <div class="chat-status-notice d-none" id="trkRouteMiniChatNotice"></div>
                        <div class="trk-route-mini-chat-messages" id="trkRouteMiniChatMessages">
                            <div class="text-center text-muted small py-3">Abre una ruta para cargar el chat.</div>
                        </div>
                        <div class="chat-typing-indicator d-none" id="trkRouteMiniChatTyping">
                            <span>Escribiendo</span>
                            <span class="chat-typing-dots"><span></span><span></span><span></span></span>
                        </div>
                        <div class="trk-route-mini-chat-compose">
                            <textarea class="form-control chat-textarea" id="trkRouteMiniChatTextarea"
                                      placeholder="Mensaje para la ruta..." rows="2" maxlength="2000" disabled></textarea>
                            <button class="chat-send-btn" id="trkRouteMiniChatSend" type="button" disabled>
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CEDIS destino en modo planeador (columna izquierda) -->
                <div class="d-none" id="trkPlannerCedisBox"></div>

                <div class="mb-3" id="trkRouteListSection">
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
                                   placeholder="Buscar en créditos de la ruta...">
                        </div>
                    </div>
                    <div class="trk-route-list-actions" id="trkRouteListActions">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnEliminarCreditosSinDireccion" disabled>
                            <i class="fa-solid fa-trash-can me-1"></i>Eliminar creditos
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="btnConfirmarCreditosFiltrados" disabled>
                            <i class="fa-solid fa-circle-check me-1"></i>Confirmar todos
                        </button>
                    </div>
                    <div id="rutaCreditosList" style="max-height:280px;overflow-y:auto;border:1px dashed var(--track-border);border-radius:.5rem;padding:.5rem;">
                        <div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
                            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
                            Aún no hay créditos en esta ruta
                        </div>
                    </div>

                <div class="trk-route-day-planner" id="trkRouteDayPlanner">
                    <div class="trk-route-day-head">
                        <div>
                            <div class="trk-route-day-title">
                                <i class="fa-solid fa-calendar-days me-1" style="color:var(--track-color);"></i>
                                Planeacion auditada por dia
                            </div>
                            <div class="trk-route-day-sub">
                                Distribuye, reprograma o adelanta paradas dejando motivo y auditoria.
                            </div>
                        </div>
                        <span class="badge bg-label-primary" id="trkRouteDayCount">0 dias</span>
                    </div>
                    <div class="trk-route-day-tools">
                        <div>
                            <label for="trkPlanInicioJornada">Inicio jornada</label>
                            <input type="time" class="form-control form-control-sm" id="trkPlanInicioJornada"
                                   value="10:00">
                        </div>
                        <div>
                            <label for="trkPlanFinJornada">Fin jornada</label>
                            <input type="time" class="form-control form-control-sm" id="trkPlanFinJornada"
                                   value="19:00">
                        </div>
                        <div>
                            <label for="trkPlanMinParada">Min por parada</label>
                            <input type="text" class="form-control form-control-sm" id="trkPlanMinParada"
                                   inputmode="numeric" pattern="[0-9]*" maxlength="2" value="45">
                        </div>
                        <div id="trkPlanTrasladoEstadosWrap">
                            <label for="trkPlanDiasTrasladoEstado">Traslado entre estados</label>
                            <input type="number" class="form-control form-control-sm" id="trkPlanDiasTrasladoEstado"
                                   min="0" max="2" step="1" value="1">
                        </div>
                        <button type="button" class="btn btn-sm btn-label-primary" id="trkPlanDistribuir">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Generar plan
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="trkPlanGuardar">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                        </button>
                    </div>
                    <div class="trk-route-real-summary d-none" id="trkRouteRealSummary"></div>
                    <div class="trk-route-day-list" id="trkRouteDayList">
                        <div class="text-center text-muted small py-2">
                            Agrega creditos para generar una propuesta por dia.
                        </div>
                    </div>
                    <div class="trk-route-event-list" id="trkRouteEventList"></div>
                </div>

                </div>

                <!-- -- Seccion 3.5: Tracking en tiempo real -- -->
                <div id="trkTrackingSection" class="mb-3 d-none">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">
                            <i class="fa-solid fa-route me-1" style="color:var(--track-color);"></i>
                            Timeline de recolección
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
                        <span id="trkProgressText"> -  /  -  puntos</span>
                        <span id="trkPorcentaje">0%</span>
                    </div>
                    <!-- Última ubicación del conductor -->
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
                    <div id="trkMapTransportSummary" class="trk-map-driver-summary d-none"></div>
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
                                <span id="trkLiveUpdated">Sin senal</span>
                                <span id="trkLiveSpeed">Vel.  - </span>
                                <span id="trkLiveAccuracy">Prec.  - </span>
                                <span id="trkLiveBattery">Bat.  - </span>
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

            <div class="trk-plan-editor-overlay d-none" id="trkPlanHorarioOverlay" aria-hidden="true">
                <div class="trk-plan-editor-card">
                    <div class="trk-plan-editor-head">
                        <div>
                            <h5 class="mb-1">
                                <i class="fa-solid fa-clock me-2" style="color:var(--track-color);"></i>
                                Fijar horario del punto
                            </h5>
                            <div class="small text-muted" id="trkPlanHorarioCreditoLabel">Credito seleccionado</div>
                        </div>
                        <button type="button" class="btn-close" id="trkPlanHorarioCerrar" aria-label="Cerrar"></button>
                    </div>
                    <div class="trk-plan-editor-body">
                        <div class="alert alert-danger py-2 px-3 small d-none" id="trkPlanHorarioError"></div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Tipo de ajuste</label>
                            <select id="trkPlanHorarioTipo" class="form-select form-select-sm">
                                <option value="reprogramacion_por_descanso">Descanso autorizado</option>
                                <option value="adelanto_operativo">Adelanto operativo</option>
                                <option value="reprogramacion_manual">Reprogramacion manual</option>
                                <option value="reprogramacion_por_cliente">Cambio por cliente</option>
                                <option value="reprogramacion_por_trafico">Cambio por trafico</option>
                                <option value="reprogramacion_por_seguridad">Cambio por seguridad</option>
                                <option value="reprogramacion_por_capacidad">Cambio por capacidad</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Fecha</label>
                            <input type="date" id="trkPlanHorarioFecha" class="form-control form-control-sm">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Llegada</label>
                                <input type="time" id="trkPlanHorarioLlegada" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Salida</label>
                                <input type="time" id="trkPlanHorarioSalida" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-3 mb-1">
                            <label class="form-label small fw-semibold mb-0">Motivo</label>
                            <span class="small text-muted" id="trkPlanHorarioMotivoCount">0/300</span>
                        </div>
                        <textarea id="trkPlanHorarioMotivo" class="form-control" rows="4" maxlength="300"
                                  placeholder="Ej. El transportista descansara y retoma a las 5 AM."></textarea>
                        <div class="form-text small">Evita patrones repetidos o texto sin descripcion.</div>
                    </div>
                    <div class="trk-plan-editor-footer">
                        <button type="button" class="btn btn-label-secondary btn-sm" id="trkPlanHorarioCancelar">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="trkPlanHorarioGuardar">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Guardar ajuste
                        </button>
                    </div>
                </div>
            </div>

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
                    Asignar ubicación manual del crédito
                </h6>
                <button type="button" class="btn-close" id="btnCerrarMapPicker" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body p-2">
                <p class="small text-muted mb-2 px-1">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Busca una dirección o haz clic en el mapa para colocar el pin del crédito
                    <strong id="mapPickerCreditoLabel"></strong>. Al confirmar o cancelar volverás a la ruta.
                </p>
                <div class="input-group input-group-sm mb-2" id="mapPickerSearchWrap">
                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--track-color);"></i>
                    </span>
                    <input type="text" class="form-control" id="mapPickerSearch"
                           placeholder="Buscar dirección, colonia, municipio..." autocomplete="off">
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
                    <i class="fa-solid fa-check me-1"></i>Confirmar ubicación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Modal  -  Agregar crédito a ruta existente
========================================================== -->
<div class="modal fade" id="modalAgregarCreditoRuta" tabindex="-1" aria-labelledby="modalAgregarCreditoRutaLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalAgregarCreditoRutaLabel">
                    <i class="fa-solid fa-plus me-2" style="color:var(--track-color);"></i>
                    Agregar crédito a ruta existente
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
     Modal - Consulta y rechazo de evidencias desde Tracking
========================================================== -->
<div class="modal fade" id="modalEvidenciasTracking" tabindex="-1" aria-labelledby="modalEvidenciasTrackingLabel">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalEvidenciasTrackingLabel">
                    <i class="fa-solid fa-motorcycle me-2" style="color:var(--track-color);"></i>
                    Datos y evidencias de la moto
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="trkEvidenciasTrackingBody">
                <div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span>Cargando...</div>
            </div>
            <div class="modal-footer py-2">
                <span class="small text-muted me-auto"><i class="fa-solid fa-circle-info me-1"></i>Al rechazar, la operaci&oacute;n se enviar&aacute; a Correcciones.</span>
                <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVistaEvidenciaTracking" tabindex="-1" aria-labelledby="modalVistaEvidenciaTrackingLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down" style="max-width:1180px;">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary py-2 text-white">
                <h6 class="modal-title mb-0" id="modalVistaEvidenciaTrackingLabel">Evidencia</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-2 text-center" id="trkVistaEvidenciaTrackingBody" style="height:72vh;min-height:520px;"></div>
            <div class="modal-footer border-secondary py-2" id="trkVistaEvidenciaTrackingFooter">
                <span class="small text-white-50 me-auto" id="trkVistaEvidenciaTrackingContador">1 / 1</span>
                <button type="button" class="btn btn-sm btn-danger d-none" id="btnVistaEvidenciaTrackingRechazar"><i class="fa-solid fa-xmark me-1"></i>Rechazar evidencia</button>
                <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
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
                            <span class="chat-connection-status" id="chatConnectionStatus">Sin conexión registrada</span>
                        </div>

                        <div id="chatPanesContainer" class="flex-grow-1 d-flex flex-column" style="overflow:hidden;"></div>

                        <div id="chatEmptyPlaceholder" class="flex-grow-1 d-none align-items-center justify-content-center text-center p-4"
                             style="color:#94a3b8;">
                            <div>
                                <i class="fa-solid fa-comments fa-2x mb-2 opacity-25 d-block"></i>
                                <span class="small">No hay puntos de recolección disponibles</span>
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
                                <span class="chat-live-subtitle" id="chatLiveRutaNombre">Esperando ubicación del transportista</span>
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
                                <span class="small">Esperando primera ubicación GPS</span>
                            </div>
                            <div id="chatLiveMapInfo" class="chat-live-info-card d-none">
                                <div class="fw-semibold mb-1" id="chatLiveUpdated">Sin ubicación</div>
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

const TRK_UNIDAD_ASSET_BASE = '/assets/img/tracking/';

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
    operacionSeleccionado: '',
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
    oportunidadesRuta:    null,
    oportunidadesRutaId:  null,
    oportunidadesLoading: false,
    oportunidadesSeq:     0,
    oportunidadesFiltroNivel: '',
    planeacionRuta:       [],
    planeacionEventos:    [],
    planeacionLoading:    false,
    planeacionMaxParadas: 6,
    planeacionTrasladoEstados: 1,
    planeacionRealResumen: null,
    planeacionRealWarnings: [],
    planeacionRealWarningGroups: [],
    planeacionRealLegs:    [],
    planeacionRealSource:  '',
    planeacionCreditosListosMensaje: false,
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
    routeMiniChat:        {
        idRuta: null,
        rutaNombre: '',
        estatus: null,
        mensajes: [],
        ws: null,
        wsRetries: 0,
        wsRetryTimeout: null,
        wsPingInterval: null,
        typingTimeout: null,
        typingStopTimeout: null,
        lastTypingSent: 0,
        loading: false,
        loaded: false,
        allLoaded: false,
        oldestMsgId: null,
    },
    opsRutaActualId:      null,
    opsRutaActualData:    null,
    opsMapInstance:       null,
    opsMapMarkers:        [],
    opsMapPolyline:       null,
    opsLiveMarker:        null,
    opsLivePolyline:      null,
    opsTrafficLayer:      null,
    routeLegDurations:    [],   // duraciones Google Maps entre puntos confirmados
    routeLegMetrics:      [],   // distancia/duracion de tramos calculados en el mapa
    planeacionCreditoEditando: null,
};

// --- Utilidades -----------------------------------------
function _trkSuspenderFocusModalRuta() {
    const modalEl = document.getElementById('modalRegistrarRuta');
    const modal = modalEl && window.bootstrap?.Modal
        ? bootstrap.Modal.getInstance(modalEl)
        : null;
    const focusTrap = modal?._focustrap;
    const $doc = window.jQuery ? window.jQuery(document) : null;
    let reactivate = false;
    let reenforce = false;
    if ($doc?.off) {
        try {
            $doc.off('focusin.bs.modal');
            reenforce = !!modal && typeof modal._enforceFocus === 'function';
        } catch (_) {}
    }
    if (focusTrap && typeof focusTrap.deactivate === 'function') {
        try {
            focusTrap.deactivate();
            reactivate = true;
        } catch (_) {}
    }
    return () => {
        if (!modalEl?.classList.contains('show')) return;
        if (reactivate) {
            try {
                focusTrap.activate();
            } catch (_) {}
        }
        if (reenforce) {
            try {
                modal._enforceFocus();
            } catch (_) {}
        }
    };
}

function _trkSwalConFocoModalRuta(options = {}) {
    const didOpen = options.didOpen;
    const willClose = options.willClose;
    const modalEl = document.getElementById('modalRegistrarRuta');
    const swalOptions = { ...options };
    if (!swalOptions.target && modalEl?.classList.contains('show')) {
        swalOptions.target = modalEl;
    }
    const customClass = swalOptions.customClass || {};
    swalOptions.customClass = typeof customClass === 'object'
        ? { ...customClass, container: `${customClass.container || ''} trk-swal-over-modal`.trim() }
        : customClass;
    let restoreFocus = null;
    return Swal.fire({
        returnFocus: false,
        stopKeydownPropagation: false,
        ...swalOptions,
        didOpen: (popup) => {
            restoreFocus = _trkSuspenderFocusModalRuta();
            popup?.querySelectorAll('input, textarea, select').forEach(el => {
                el.disabled = false;
                el.readOnly = false;
                el.style.pointerEvents = 'auto';
                ['mousedown', 'mouseup', 'click', 'focus', 'keydown', 'keyup', 'input'].forEach(evt => {
                    el.addEventListener(evt, ev => ev.stopPropagation());
                });
            });
            if (typeof didOpen === 'function') didOpen(popup);
        },
        willClose: (popup) => {
            if (typeof willClose === 'function') willClose(popup);
            if (restoreFocus) {
                const restore = restoreFocus;
                restoreFocus = null;
                setTimeout(restore, 0);
            }
        },
    });
}

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
    en_revision: '<span class="badge badge-conf-en_revision">En revision</span>',
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
        placeholder: 'Buscar por crédito, modelo, VIN...',
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
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando pestañas, contadores y datos recientes del módulo.</span>',
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
    document.getElementById('trkOpGrid')?.addEventListener('click', ev => {
        const btn = ev.target.closest('[data-op-driver]');
        if (!btn) return;
        _trk.operacionSeleccionado = String(btn.dataset.opDriver || '');
        _trkRenderOperacionTransportistas();
    });
    $('#tabOperacionTransportistas').on('click', '.btn-ver-operacion-transportista', function () {
        _trkAbrirModalOperacionTransportista($(this).data('id'));
    });
    document.getElementById('btnToggleChatMap')?.addEventListener('click', () => _trkToggleChatMapPanel());
    document.getElementById('trkRouteMiniChatExpand')?.addEventListener('click', () => {
        const idRuta = _trk.routeMiniChat.idRuta || _trk.idRutaEditando;
        if (!idRuta) return;
        _trkMiniChatDesconectarWS();
        _trkChatCargarYAbrir(Number(idRuta));
    });
    document.getElementById('trkRouteMiniChatSend')?.addEventListener('click', _trkMiniChatEnviarMensaje);
    document.getElementById('trkRouteMiniChatTextarea')?.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            _trkMiniChatEnviarMensaje();
        }
    });
    document.getElementById('trkRouteMiniChatTextarea')?.addEventListener('input', e => _trkMiniChatEmitirTyping(e.target.value.trim() !== ''));
    document.getElementById('trkRouteMiniChatTextarea')?.addEventListener('blur', () => _trkMiniChatEmitirTyping(false));
    document.getElementById('modalRegistrarRuta')?.addEventListener('hidden.bs.modal', _trkMiniChatLimpiar);
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
    ['rutaFecha', 'rutaFechaFin', 'rutaHoraH', 'rutaHoraM', 'rutaHoraAmPm'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', () => {
            if (id === 'rutaFecha') {
                _trkSincronizarFechaFinalizacion();
                _trkAsegurarEtasFechaMinima();
                _trkPlanCascadeCurrent();
            }
            if (id === 'rutaFechaFin') _trkSincronizarFechaFinalizacion();
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
        <span>Dirección: ${_trkChatEscapeHtml(direccion)}</span>
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
            emptyTable:  'No hay créditos disponibles',
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
                render: r => `<div class="d-inline-flex gap-1">
                    <button class="btn btn-icon btn-sm rounded-pill btn-label-primary trk-action-btn btn-ver-evidencias-tracking"
                            data-id="${r.id_credito}"
                            title="Ver datos y evidencias">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn btn-icon btn-sm rounded-pill btn-label-success trk-action-btn btn-agregar-a-ruta"
                            data-id="${r.id_credito}"
                            title="Agregar a ruta">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>`,
            },
        ],
    });

    $('#tablaCreditos').on('click', '.btn-agregar-a-ruta', function () {
        const idCred = $(this).data('id');
        const cred   = _trk.creditosDisponibles.find(c => String(c.id_credito) === String(idCred));
        if (!cred) return;
        _trkAbrirModalAgregarCreditoRuta(cred);
    });

    $('#tablaCreditos').on('click', '.btn-ver-evidencias-tracking', function () {
        _trkAbrirEvidenciasTracking(Number($(this).data('id')));
    });

    $('#modalEvidenciasTracking').on('click', '.btn-vista-evidencia-tracking', function () {
        _trkAbrirVistaEvidenciaTracking(Number($(this).data('indice')));
    });
    $('#modalVistaEvidenciaTracking').on('click', '.btn-vista-evidencia-tracking-nav', function () {
        _trkCambiarVistaEvidenciaTracking(Number($(this).data('direccion')));
    });
    $('#btnVistaEvidenciaTrackingRechazar').on('click', () => {
        const actual = _trkVistaEvidenciasTracking.items[_trkVistaEvidenciasTracking.indice];
        if (!actual) return;
        bootstrap.Modal.getInstance(document.getElementById('modalVistaEvidenciaTracking'))?.hide();
        _trkConfirmarRechazoEvidenciaTracking(actual.idOperacion, actual.idEvidencia, actual.etiqueta);
    });
    document.getElementById('modalVistaEvidenciaTracking')?.addEventListener('hidden.bs.modal', () => {
        if (!_trkVistaEvidenciasTracking.reabrirModalPrincipal) return;
        _trkVistaEvidenciasTracking.reabrirModalPrincipal = false;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEvidenciasTracking')).show();
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
    $('#detalleRutaBody').on('click', '.btn-validar-otp', function () {
        _trkValidarOtpDetalle(Number($(this).data('id')));
    });
    $('#trkTimeline').on('click', '.btn-validar-otp', function () {
        _trkValidarOtpDetalle(Number($(this).data('id')));
    });
    $('#rutaCedisDestinoInfo, #trkMapTransportSummary').on('click', '.btn-cambiar-cedis-destino', function () {
        _trkAbrirModalCambiarCedisDestino(Number($(this).data('id')));
    });
    $('#trkCambiarCedisMotivo').on('input', function () {
        $('#trkCambiarCedisMotivoCount').text(`${String(this.value || '').length}/200`);
    });
    $('#btnConfirmarCambioCedisDestino').on('click', _trkConfirmarCambioCedisDestino);
}

function _trkEtiquetaEvidenciaTracking(evidencia) {
    const slot = String(evidencia?.slot || evidencia?.tipo || 'Evidencia').trim();
    const etiquetas = {
        fis_dacion_hoja_1: 'Dación hoja 1', fis_dacion_hoja_2: 'Dación hoja 2',
        fis_ine_frente: 'INE frente', fis_ine_reverso: 'INE reverso', fis_vin: 'VIN',
        fis_frontal: 'Foto frontal', fis_lateral_izq: 'Lateral izquierdo', fis_lateral_der: 'Lateral derecho',
        fis_trasera: 'Foto trasera', fis_tacometro: 'Tacómetro',
        fis_video_cliente_acuerdo: 'Video cliente de acuerdo', fis_video_vuelta_prueba: 'Video vuelta de prueba',
        fis_checklist: 'Checklist', fis_360: 'Video 360°', fis_360_encendida: 'Video moto encendida',
        doc_repuve: 'Documento REPUVE'
    };
    return etiquetas[slot] || slot.replace(/^fis_/, '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

const _trkVistaEvidenciasTracking = { items: [], indice: 0, reabrirModalPrincipal: false, solicitud: 0 };

function _trkEvidenciaEsVideo(evidencia, url = '') {
    const slot = String(evidencia?.slot || '').toLowerCase();
    const tipo = String(evidencia?.tipo || '').toLowerCase();
    return tipo.includes('video') || slot.includes('video') || slot === 'fis_360' || slot === 'fis_360_encendida' || /\.(mp4|mov|webm)(\?|#|$)/i.test(url);
}

function _trkEvidenciaEsPdf(evidencia, url = '') {
    return String(evidencia?.slot || '') === 'doc_repuve'
        || String(evidencia?.tipo || '').toLowerCase().includes('pdf')
        || /\.pdf(\?|#|$)/i.test(url);
}

function _trkUrlsAlternasEvidenciaTracking(url) {
    const original = String(url || '').trim();
    if (!original) return [];
    const alternativas = [];
    try {
        const parsed = new URL(original, window.location.origin);
        const matchUploads = parsed.pathname.match(/(\/uploads\/.*)$/i);
        if (!matchUploads) return [];
        const archivo = matchUploads[1] + parsed.search + parsed.hash;
        alternativas.push(archivo);

        const marcadorRuta = '/trackingrecoleccion/';
        const rutaActual = window.location.pathname || '';
        const indiceModulo = rutaActual.toLowerCase().indexOf(marcadorRuta);
        if (indiceModulo > 0) {
            alternativas.push(rutaActual.slice(0, indiceModulo).replace(/\/$/, '') + archivo);
        }
    } catch (_) {
        return [];
    }
    return alternativas.filter((item, indice, lista) => item && item !== original && lista.indexOf(item) === indice);
}

async function _trkResolverUrlEvidenciaTracking(url) {
    const candidatos = [String(url || '').trim(), ..._trkUrlsAlternasEvidenciaTracking(url)].filter(Boolean);
    for (const candidato of candidatos) {
        try {
            const parsed = new URL(candidato, window.location.origin);
            if (parsed.origin !== window.location.origin) return candidato;
            const respuesta = await fetch(candidato, { method: 'HEAD', credentials: 'same-origin' });
            if (respuesta.ok) return candidato;
        } catch (_) {
            // Probar la siguiente ruta posible del mismo archivo.
        }
    }
    return candidatos[0] || '';
}

function _trkActivarFallbackEvidenciasTracking(contenedor) {
    (contenedor || document).querySelectorAll?.('[data-url-principal]').forEach(media => {
        media.addEventListener('error', function () {
            const alternativas = String(this.dataset.urlAlternas || '').split('||').filter(Boolean);
            const indice = Number(this.dataset.fallbackIndice || 0);
            if (!alternativas[indice]) {
                this.classList.add('opacity-50');
                return;
            }
            this.dataset.fallbackIndice = String(indice + 1);
            this.src = alternativas[indice];
        });
        if (!media.getAttribute('src')) {
            media.src = String(media.dataset.urlPrincipal || '');
        }
    });
}

function _trkVistaPreviaEvidenciaTracking(evidencia, etiqueta, url, indice) {
    const urlEsc = _trkChatEscapeHtml(url);
    const etiquetaEsc = _trkChatEscapeHtml(etiqueta);
    const attrs = `data-indice="${Number(indice)}"`;
    const urlsAlternas = _trkUrlsAlternasEvidenciaTracking(url);
    const fuentes = ` data-url-principal="${urlEsc}"${urlsAlternas.length ? ` data-url-alternas="${_trkChatEscapeHtml(urlsAlternas.join('||'))}" data-fallback-indice="0"` : ''}`;
    if (!url) {
        return '<div class="border rounded bg-light text-muted d-flex align-items-center justify-content-center" style="height:150px;"><i class="fa-solid fa-image fa-2x"></i></div>';
    }
    if (_trkEvidenciaEsPdf(evidencia, url)) {
        return `<button type="button" class="btn-vista-evidencia-tracking border-0 rounded w-100 bg-light text-danger" ${attrs} style="height:150px;"><i class="fa-solid fa-file-pdf fa-3x d-block mb-2"></i><span class="small">Vista previa del PDF</span></button>`;
    }
    if (_trkEvidenciaEsVideo(evidencia, url)) {
        return `<button type="button" class="btn-vista-evidencia-tracking border-0 rounded w-100 position-relative overflow-hidden bg-dark" ${attrs} style="height:150px;"><video${fuentes} muted preload="metadata" class="w-100 h-100" style="object-fit:cover;pointer-events:none;"></video><span class="position-absolute top-50 start-50 translate-middle rounded-circle bg-dark bg-opacity-75 text-white d-inline-flex align-items-center justify-content-center" style="width:42px;height:42px;"><i class="fa-solid fa-play"></i></span></button>`;
    }
    return `<button type="button" class="btn-vista-evidencia-tracking border-0 rounded w-100 overflow-hidden p-0 bg-light" ${attrs} title="Clic para ampliar" style="height:150px;"><img${fuentes} alt="${etiquetaEsc}" class="w-100 h-100" style="object-fit:cover;"></button>`;
}

function _trkEstadoEvidenciaTracking(evidencia) {
    const valor = Number(evidencia?.val_atn || 0);
    if (valor === 2) {
        const porTracking = /RECHAZADO POR TRACKING/i.test(String(evidencia?.comentario_atn || ''));
        return `<span class="badge ${porTracking ? 'bg-label-danger' : 'bg-label-warning'}">${porTracking ? 'Rechazado por Tracking' : 'Rechazada'}</span>`;
    }
    if (valor === 1) return '<span class="badge bg-label-success">Aceptada</span>';
    return '<span class="badge bg-label-secondary">Pendiente</span>';
}

function _trkRenderEvidenciasTracking(detalle) {
    const esc = _trkChatEscapeHtml;
    const datos = detalle?.datos_moto || detalle || {};
    const campos = [
        ['Crédito', detalle?.id_credito], ['Cliente', detalle?.nombre_cliente], ['Estatus', detalle?.estatus],
        ['Marca', datos?.moto_marca], ['Modelo', datos?.moto_modelo], ['Año', datos?.moto_anio],
        ['Color', datos?.moto_color], ['VIN / serie', datos?.moto_no_serie], ['Motor', datos?.moto_no_motor],
        ['Placas', datos?.moto_placas], ['Ubicación', [datos?.log_estado, datos?.log_ciudad].filter(Boolean).join(' - ')]
    ].filter(([, valor]) => valor !== null && valor !== undefined && String(valor).trim() !== '');
    const resumen = campos.length
        ? `<div class="row g-2 mb-4">${campos.map(([etiqueta, valor]) => `<div class="col-12 col-md-4"><div class="border rounded p-2 h-100"><div class="small text-muted">${esc(etiqueta)}</div><div class="fw-semibold small">${esc(valor)}</div></div></div>`).join('')}</div>`
        : '<div class="alert alert-light border mb-4">No hay datos adicionales de la moto.</div>';
    const evidencias = Array.isArray(detalle?.evidencias) ? detalle.evidencias : [];
    _trkVistaEvidenciasTracking.items = evidencias
        .filter(ev => String(ev?.url || '').trim() !== '')
        .map(ev => ({
            url: String(ev.url || '').trim(),
            etiqueta: _trkEtiquetaEvidenciaTracking(ev),
            tipo: String(ev?.tipo || ''),
            idOperacion: Number(detalle?.id || 0),
            idEvidencia: Number(ev?.id || 0),
            puedeRechazar: String(ev?.slot || '') !== 'doc_repuve' && Number(ev?.val_atn || 0) !== 2,
        }));
    const tarjetas = evidencias.length
        ? evidencias.map(ev => {
            const url = String(ev?.url || '').trim();
            const etiqueta = _trkEtiquetaEvidenciaTracking(ev);
            const indice = _trkVistaEvidenciasTracking.items.findIndex(item => item.idEvidencia === Number(ev?.id || 0));
            const previsualizacion = _trkVistaPreviaEvidenciaTracking(ev, etiqueta, url, indice);
            const comentario = ev?.comentario_atn ? `<div class="small text-danger mt-2">${esc(ev.comentario_atn)}</div>` : '';
            return `<div class="col-12 col-sm-6 col-lg-3"><div class="border rounded p-3 h-100 d-flex flex-column gap-2"><div class="d-flex align-items-start justify-content-between gap-2"><strong class="small">${esc(etiqueta)}</strong>${_trkEstadoEvidenciaTracking(ev)}</div>${previsualizacion}${comentario}</div></div>`;
        }).join('')
        : '<div class="col-12"><div class="alert alert-warning mb-0">Esta moto no tiene evidencias cargadas.</div></div>';
    return `${resumen}<div class="d-flex align-items-center justify-content-between mb-2"><h6 class="mb-0"><i class="fa-solid fa-images me-1"></i>Evidencias</h6><span class="badge bg-label-primary">${evidencias.length}</span></div><div class="row g-3">${tarjetas}</div>`;
}

async function _trkAbrirVistaEvidenciaTracking(indice) {
    if (!Number.isInteger(indice) || !_trkVistaEvidenciasTracking.items[indice]) return;
    _trkVistaEvidenciasTracking.indice = indice;
    const modalEl = document.getElementById('modalVistaEvidenciaTracking');
    const titulo = document.getElementById('modalVistaEvidenciaTrackingLabel');
    const body = document.getElementById('trkVistaEvidenciaTrackingBody');
    if (!modalEl || !body) return;
    const actual = _trkVistaEvidenciasTracking.items[indice];
    const { url, etiqueta, tipo } = actual;
    const esc = _trkChatEscapeHtml;
    const solicitud = ++_trkVistaEvidenciasTracking.solicitud;
    if (titulo) titulo.textContent = etiqueta || 'Evidencia';
    body.innerHTML = '<div class="h-100 d-flex align-items-center justify-content-center text-white"><span class="spinner-border spinner-border-sm me-2"></span>Cargando evidencia...</div>';
    const urlVisual = await _trkResolverUrlEvidenciaTracking(url);
    if (solicitud !== _trkVistaEvidenciasTracking.solicitud || _trkVistaEvidenciasTracking.indice !== indice) return;
    const urlsAlternas = _trkUrlsAlternasEvidenciaTracking(urlVisual);
    const fuentes = ` data-url-principal="${esc(urlVisual)}"${urlsAlternas.length ? ` data-url-alternas="${esc(urlsAlternas.join('||'))}" data-fallback-indice="0"` : ''}`;
    const evidencia = { tipo, slot: '' };
    const total = _trkVistaEvidenciasTracking.items.length;
    const navegacion = total > 1
        ? `<button type="button" class="btn btn-dark rounded-circle position-absolute top-50 start-0 translate-middle-y ms-2 btn-vista-evidencia-tracking-nav" data-direccion="-1" title="Evidencia anterior" style="z-index:2;width:42px;height:42px;"><i class="fa-solid fa-chevron-left"></i></button>
           <button type="button" class="btn btn-dark rounded-circle position-absolute top-50 end-0 translate-middle-y me-2 btn-vista-evidencia-tracking-nav" data-direccion="1" title="Evidencia siguiente" style="z-index:2;width:42px;height:42px;"><i class="fa-solid fa-chevron-right"></i></button>`
        : '';
    let medio = '';
    if (_trkEvidenciaEsPdf(evidencia, urlVisual)) {
        medio = `<iframe src="${esc(urlVisual)}" title="${esc(etiqueta)}" class="w-100 h-100 bg-white" style="border:0;"></iframe>`;
    } else if (_trkEvidenciaEsVideo(evidencia, urlVisual)) {
        medio = `<video${fuentes} controls autoplay class="mw-100 mh-100" style="max-height:100%;max-width:100%;"></video>`;
    } else {
        medio = `<img${fuentes} alt="${esc(etiqueta)}" class="mw-100 mh-100" style="max-height:100%;max-width:100%;object-fit:contain;">`;
    }
    body.innerHTML = `<div class="position-relative d-flex align-items-center justify-content-center h-100 w-100 overflow-hidden">${medio}${navegacion}</div>`;
    _trkActivarFallbackEvidenciasTracking(body);
    $('#trkVistaEvidenciaTrackingContador').text(`${indice + 1} / ${total}`);
    $('#btnVistaEvidenciaTrackingRechazar').toggleClass('d-none', !actual.puedeRechazar);
    const modalPrincipalEl = document.getElementById('modalEvidenciasTracking');
    const modalPrincipal = modalPrincipalEl ? bootstrap.Modal.getInstance(modalPrincipalEl) : null;
    const visor = bootstrap.Modal.getOrCreateInstance(modalEl);
    if (modalEl.classList.contains('show')) {
        visor.show();
    } else if (modalPrincipalEl?.classList.contains('show') && modalPrincipal) {
        _trkVistaEvidenciasTracking.reabrirModalPrincipal = true;
        modalPrincipalEl.addEventListener('hidden.bs.modal', () => visor.show(), { once: true });
        modalPrincipal.hide();
    } else {
        visor.show();
    }
}

function _trkCambiarVistaEvidenciaTracking(delta) {
    const total = _trkVistaEvidenciasTracking.items.length;
    if (total <= 1) return;
    const siguiente = (_trkVistaEvidenciasTracking.indice + Number(delta) + total) % total;
    _trkAbrirVistaEvidenciaTracking(siguiente);
}

async function _trkAbrirEvidenciasTracking(idCredito) {
    if (!idCredito) return;
    const modalEl = document.getElementById('modalEvidenciasTracking');
    const body = document.getElementById('trkEvidenciasTrackingBody');
    if (!modalEl || !body) return;
    body.innerHTML = '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span>Cargando datos y evidencias...</div>';
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    try {
        const respuesta = await trkFetch('/MotosAdjudicadas/obtenerEvidenciasCredito', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id_credito: idCredito, rapido: true })
        });
        if (!respuesta?.success || !respuesta?.detalle) throw new Error(respuesta?.message || 'No se encontraron datos de la moto.');
        body.innerHTML = _trkRenderEvidenciasTracking(respuesta.detalle);
        _trkActivarFallbackEvidenciasTracking(body);
    } catch (error) {
        body.innerHTML = `<div class="alert alert-danger mb-0">${_trkChatEscapeHtml(error?.message || 'No se pudieron cargar las evidencias.')}</div>`;
    }
}

async function _trkConfirmarRechazoEvidenciaTracking(idOperacion, idEvidencia, etiqueta) {
    if (!idOperacion || !idEvidencia) return;
    const dialogo = await Swal.fire({
        icon: 'warning', title: 'Rechazar evidencia',
        html: `Indica el motivo para <b>${_trkChatEscapeHtml(etiqueta)}</b>. La operación pasará a <b>Correcciones</b> y se identificará como <b>Rechazado por Tracking</b>.`,
        input: 'textarea', inputPlaceholder: 'Motivo del rechazo...', inputAttributes: { maxlength: 1800 },
        showCancelButton: true, confirmButtonText: 'Rechazar y enviar a Correcciones', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545', inputValidator: value => !String(value || '').trim() && 'El motivo es requerido.'
    });
    if (!dialogo.isConfirmed) return;
    try {
        Swal.fire({ title: 'Guardando rechazo...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const respuesta = await trkFetch('/TrackingRecoleccion/rechazarEvidenciaTracking', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id_operacion: idOperacion, id_evidencia: idEvidencia, motivo: String(dialogo.value || '').trim() })
        });
        if (!respuesta?.success) throw new Error(respuesta?.message || respuesta?.mensaje || 'No se pudo rechazar la evidencia.');
        await Swal.fire({ icon: 'success', title: 'Evidencia rechazada', text: 'La operación fue enviada a Correcciones con la etiqueta Rechazado por Tracking.' });
        await _trkCargarCreditosPaso2(true);
        const credito = _trk.creditosFiltroBase.find(c => Number(c.id_operacion) === idOperacion);
        if (credito) _trkAbrirEvidenciasTracking(Number(credito.id_credito));
        else bootstrap.Modal.getInstance(document.getElementById('modalEvidenciasTracking'))?.hide();
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'No se pudo rechazar', text: error?.message || 'Intenta nuevamente.' });
    }
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
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar créditos.', confirmButtonText: 'Aceptar' });
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
function _trkParseUbicacionesRuta(raw) {
    if (!raw) return [];
    const map = new Map();
    String(raw || '').split('@@').forEach(p => {
        const sep  = p.indexOf('|||');
        const estRaw = sep >= 0 ? p.slice(0, sep).trim()  : String(p || '').trim();
        const munRaw = sep >= 0 ? p.slice(sep + 3).trim() : '';
        const est = _trkEstadoMayus(estRaw, munRaw);
        const mun = _trkMunicipioMayus(munRaw, est);
        if (!est) return;
        if (!map.has(est)) map.set(est, new Set());
        if (mun && mun !== '|') map.get(est).add(mun);
    });
    return [...map.entries()].map(([estado, munis]) => ({
        estado,
        municipios: [...munis].filter(Boolean),
    }));
}

function _trkUbicacionRutaTextoCompleto(raw) {
    const grupos = _trkParseUbicacionesRuta(raw);
    if (!grupos.length) return '';
    return grupos.map(g => {
        const munStr = g.municipios.join(', ');
        return munStr ? `${g.estado} / ${munStr}` : g.estado;
    }).join(' | ');
}

function _trkRenderUbicacionRuta(raw) {
    const grupos = _trkParseUbicacionesRuta(raw);
    if (!grupos.length) return ' - ';
    const totalMunicipios = grupos.reduce((total, g) => total + g.municipios.length, 0);
    const compactar = grupos.length > 2 || totalMunicipios > 4 || grupos.some(g => g.municipios.length > 3);
    const detalle = _trkUbicacionRutaTextoCompleto(raw);
    const html = grupos.map(g => {
        if (compactar) {
            const total = g.municipios.length;
            const label = total > 0
                ? `${g.estado} - ${total} municipio${total === 1 ? '' : 's'}`
                : g.estado;
            return _trkChatEscapeHtml(label);
        }
        const munStr = g.municipios.join(', ');
        return _trkChatEscapeHtml(munStr ? `${g.estado} / ${munStr}` : g.estado);
    }).join('<br>');
    return `<span class="trk-ubicacion-resumen" title="${_trkChatEscapeHtml(detalle)}">${html}</span>`;
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
    const status = String(estatus || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
    return ![
        'borrador',
        'cancelada',
        'cancelado',
        'concluida',
        'concluido',
        'completada',
        'completado',
        'finalizada',
        'finalizado',
        'terminada',
        'terminado',
    ].includes(status);
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
        if (btn.classList.contains('btn-ver-ruta')) _trkCargarRutaEnModal(idRuta, false);
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
               data-id="${id}" title="Ver ruta" aria-label="Ver ruta">
               <i class="fa-solid fa-eye"></i>
           </button>`;
    return `<article class="trk-ruta-card" data-estatus="${_trkChatEscapeHtml(estatus)}">
        <div class="trk-ruta-card-header">
            <span class="trk-route-folio">#${_trkChatEscapeHtml(id)}</span>
            <div class="trk-ruta-title">${_trkChatEscapeHtml(nombreLimpio)}</div>
            <div class="trk-ruta-subtitle">${ubicacion || 'Sin ubicación'}</div>
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
                <span>${total} crédito${total !== 1 ? 's' : ''}</span>
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
                               data-id="${r.id_ruta}" title="Ver ruta" aria-label="Ver ruta">
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
        _trkCargarRutaEnModal($(this).data('id'), false);
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
        <div class="trk-borrador-sub"><i class="fa-solid fa-location-dot me-1"></i>${ubicacion || 'Sin ubicación'}</div>
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
    if (type !== 'display') return `${total} créditos ${conf} confirmados ${pend} pendientes ${rech} rechazados ${r?.creditos_lista || ''}`;
    const lista = (r?.creditos_lista || '').split('||').filter(Boolean).join('<br>');
    const listaAttr = _trkChatEscapeHtml(lista).replace(/&lt;br&gt;/g, '<br>');
    const ttAttr = lista ? ` data-bs-toggle="tooltip" data-bs-placement="right" data-bs-html="true" data-bs-title="${listaAttr}"` : '';
    return `<div class="trk-borrador-cell">
        <span class="trk-borrador-chip trk-borrador-chip-success"${ttAttr}>
            <i class="fa-solid fa-motorcycle me-1"></i>${total} crédito${total !== 1 ? 's' : ''}
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
            data-id="${r.id_ruta}" title="Ver ruta" aria-label="Ver ruta">
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
        _trkCargarRutaEnModal($(this).data('id'), false);
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
                    <span class="badge bg-label-primary">${total} crédito${total === 1 ? '' : 's'}</span>
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
    const res = await _trkSwalConFocoModalRuta({
        icon: 'question',
        title: 'Agregar crédito a ruta?',
        html: `<div class="text-start small">
            <div><b>Credito:</b> #${_trkChatEscapeHtml(cred.id_credito)} - ${_trkChatEscapeHtml(cred.nombre_cliente || '')}</div>
            <div><b>Ruta:</b> ${_trkChatEscapeHtml(nombreRuta)}</div>
            ${dictamen}
            <div class="mt-2 text-muted">Se agregara al final de la ruta y quedara marcado como <b>NUEVO</b>.</div>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488',
    });
    if (!res.isConfirmed) return;
    Swal.fire({
        title: 'Agregando crédito...',
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
            title: 'Crédito agregado',
            html: `<div class="small">
                ${_trkChatEscapeHtml(r.message || r.mensaje || 'Se agrego al final de la ruta.')}<br>
                <span class="badge bg-warning text-dark mt-2">NUEVO</span>
                <span class="text-muted">Registro: ${_trkChatEscapeHtml(fechaRegistro)}</span>
            </div>`,
            timer: 2300,
            showConfirmButton: false,
        });
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo agregar el crédito a la ruta.', confirmButtonText: 'Aceptar' });
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
        confirmButtonText: 'Sí, cancelar ruta',
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
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cancelar la ruta.', confirmButtonText: 'Aceptar' });
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
        _trkUbicacionRutaTextoCompleto(r?.ubicaciones_lista),
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
        confirmButtonText: 'Sí, borrar',
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
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo borrar el borrador.', confirmButtonText: 'Aceptar' });
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

function _trkTipoActorBadge(actor) {
    return actor === 'almacenista'
        ? '<span class="badge bg-warning text-dark"><i class="fa-solid fa-clipboard-check me-1"></i>Almacenista</span>'
        : '<span class="badge bg-info"><i class="fa-solid fa-truck-fast me-1"></i>Transportista</span>';
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
            {
                data: null,
                render: t => `${_trkTipoActorBadge(t.tipo_actor || 'transportista')} <span class="ms-1">${_trkTipoTransportistaBadge(t.tipo_transportista)}</span>`,
            },
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
    $('#trkCatalogoActualizar').on('click', () => _trkCargarSeccion('catalogos', { force: true }));
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
    $('#transportistaActorTracking').on('change', () => _trkSincronizarActorOperativo());
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
        item.tipo_actor,
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
    const esTransportista = String(t?.tipo_actor || 'transportista') !== 'almacenista';
    const title = activo ? 'Activar usuario operativo' : 'Desactivar usuario operativo';
    const icon = activo ? 'fa-toggle-off' : 'fa-toggle-on';
    const klass = activo ? 'btn-label-secondary' : 'btn-label-danger';
    return `<div class="d-flex justify-content-end gap-1">
        <button type="button" class="btn btn-icon btn-sm rounded-pill btn-label-primary trk-action-btn btn-editar-transportista"
            data-id="${_trkChatEscapeHtml(t?.id_transportista || '')}" title="Editar usuario operativo">
            <i class="fa-solid fa-pen-to-square"></i>
        </button>
        ${esTransportista ? `<button type="button" class="btn btn-icon btn-sm rounded-pill btn-label-info trk-action-btn btn-editar-unidad"
            data-id="${_trkChatEscapeHtml(t?.id_transportista || '')}" title="Unidad y capacidad">
            <i class="fa-solid fa-truck"></i>
        </button>` : ''}
        <button type="button" class="btn btn-icon btn-sm rounded-pill ${klass} trk-action-btn btn-toggle-transportista"
            data-id="${_trkChatEscapeHtml(t?.id_transportista || '')}" data-activo="${activo}" title="${title}">
            <i class="fa-solid ${icon}"></i>
        </button>
    </div>`;
}

function _trkCatalogoCedisCard(a, compacto = false) {
    const ubicacion = [a.estado, a.municipio, a.codigo_postal ? `CP ${a.codigo_postal}` : ''].filter(Boolean).join(' / ') || 'Sin ubicación';
    const contacto = [a.telefono, a.email].filter(Boolean).join(' - ') || 'Sin contacto';
    return `<article class="trk-catalog-card trk-admin-card">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
                <span class="badge bg-primary mb-1"><i class="fa-solid fa-warehouse me-1"></i>CEDIS</span>
                <div class="trk-catalog-card-title">${_trkChatEscapeHtml(a.nombre_agencia || 'Sin nombre')}</div>
                <div class="trk-catalog-card-sub">ID ${_trkChatEscapeHtml(a.id_agencia || '-')} / ${_trkChatEscapeHtml(a.clave_agencia || 'Sin clave')}</div>
            </div>
            ${_trkCatalogoEstadoBadge(a.activo)}
        </div>
        <div class="trk-catalog-card-sub"><strong>Ubicacion:</strong> ${_trkChatEscapeHtml(ubicacion)}</div>
        ${compacto ? '' : `<div class="trk-catalog-card-sub"><strong>Dirección:</strong> ${_trkChatEscapeHtml(a.direccion || 'No disponible')}</div>`}
        <div class="trk-catalog-card-sub"><strong>Contacto:</strong> ${_trkChatEscapeHtml(contacto)}</div>
        ${compacto ? '' : `<div class="trk-catalog-card-sub"><strong>Encargado:</strong> ${_trkChatEscapeHtml(a.encargado || 'Sin encargado')}</div>`}
        <div class="trk-catalog-card-actions">${_trkCatalogoAccionesCedis(a)}</div>
    </article>`;
}

function _trkCatalogoTransportistaCard(t) {
    const cedis = t.nombre_agencia || t.empresa_origen || 'Sin CEDIS';
    const contacto = [t.telefono, t.email].filter(Boolean).join(' - ') || 'Sin contacto';
    return `<article class="trk-catalog-card trk-admin-card">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
                ${_trkTipoActorBadge(t.tipo_actor || 'transportista')} <span class="ms-1">${_trkTipoTransportistaBadge(t.tipo_transportista)}</span>
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
                    <span class="badge bg-label-primary">${relacionados.length} usuario${relacionados.length !== 1 ? 's' : ''}</span>
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
                    <div class="trk-catalog-group-title">Usuarios operativos sin CEDIS asignado</div>
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

function _trkSincronizarActorOperativo() {
    const actor = $('#transportistaActorTracking').val() || 'transportista';
    const esAlmacenista = actor === 'almacenista';
    $('#transportistaPuestoTracking').attr('placeholder', esAlmacenista ? 'Almacenista / auxiliar de almacen' : 'Operador / transportista');
    $('#transportistaEmpresaTracking').attr('placeholder', esAlmacenista ? 'CEDIS / area de almacen' : 'Empresa transportista');
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
    $('#modalTransportistaTrackingLabel').html(`<i class="fa-solid fa-id-card-clip me-2" style="color:var(--track-color);"></i>${t ? 'Editar Usuario Operativo' : 'Registrar Usuario Operativo'}`);
    $('#transportistaIdTracking').val(t?.id_transportista || '');
    $('#transportistaActorTracking').val(t?.tipo_actor || 'transportista');
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
    _trkSincronizarActorOperativo();
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
        razones.push('En ruta: evaluar si la recolección queda al paso.');
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

function _trkTransportistasUnidadBase() {
    const map = new Map();
    (_trk.transportistasTracking || []).forEach(t => {
        if (String(t?.tipo_actor || 'transportista') === 'almacenista') return;
        if (t?.id_transportista) map.set(String(t.id_transportista), t);
    });
    (_trk.operacionTransportistas || []).forEach(t => {
        if (String(t?.tipo_actor || 'transportista') === 'almacenista') return;
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
        tipo_actor: $('#transportistaActorTracking').val() || 'transportista',
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
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo guardar el registro.', confirmButtonText: 'Aceptar' });
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
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre del usuario operativo es obligatorio.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (payload.tipo_actor === 'almacenista' && !Number(payload.id_agencia || 0)) {
        Swal.fire({ icon: 'warning', title: 'CEDIS requerido', text: 'El almacenista debe tener un CEDIS asignado.', confirmButtonText: 'Aceptar' });
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
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo guardar la unidad.', confirmButtonText: 'Aceptar' });
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
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo actualizar el registro.', confirmButtonText: 'Aceptar' });
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
            if (!silent) console.warn('[Tracking Recolección] Error al cargar catálogos', err);
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
    const transportistasActores = transportistasTotal.filter(t => String(t.tipo_actor || 'transportista') !== 'almacenista').length;
    const almacenistasActores = transportistasTotal.filter(t => String(t.tipo_actor || '') === 'almacenista').length;

    $('#statAgenciasTracking').text(agenciasTotal.filter(a => Number(a.activo ?? 1) === 1).length);
    $('#statTransportistasInternos').text(transportistasActores);
    $('#statTransportistasExternos').text(almacenistasActores);
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
    $('#trkOpGrid').removeClass('is-panel');

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

function _trkOperacionTransportistaPanel(filtrados) {
    if (!_trk.operacionSeleccionado || !filtrados.some(t => String(t.id_transportista || '') === String(_trk.operacionSeleccionado))) {
        _trk.operacionSeleccionado = String(filtrados[0]?.id_transportista || '');
    }
    const seleccionado = filtrados.find(t => String(t.id_transportista || '') === String(_trk.operacionSeleccionado)) || filtrados[0];
    const roster = filtrados.map(t => _trkOperacionRosterItem(t, seleccionado?.id_transportista)).join('');
    return `<aside class="trk-admin-roster">
            <div class="trk-admin-roster-title">Transportistas (${filtrados.length})</div>
            ${roster}
        </aside>
        ${_trkOperacionDetallePanel(seleccionado)}`;
}

function _trkOperacionRosterItem(t, selectedId) {
    const id = String(t.id_transportista || '');
    const active = id && String(selectedId || '') === id;
    const status = String(t.estatus_operativo || 'disponible');
    const unidad = _trkOperacionUnidadTexto(t);
    const cap = Number(t.capacidad_total || 0);
    const disponible = cap > 0 ? `${t.capacidad_disponible ?? 0} disp.` : 'Sin cap.';
    const rutasTxt = `${t.rutas_activas || 0} activas / ${t.rutas_programadas || 0} prog.`;
    return `<button type="button" class="trk-admin-roster-item${active ? ' active' : ''}" data-op-driver="${_trkChatEscapeHtml(id)}">
        <span class="trk-admin-roster-avatar"><i class="fa-solid fa-truck"></i></span>
        <span style="min-width:0;">
            <span class="trk-admin-roster-name">${_trkChatEscapeHtml(t.nombre_transportista || 'Sin nombre')}</span>
            <span class="trk-admin-roster-sub">${_trkChatEscapeHtml(unidad)}</span>
            <span class="trk-admin-roster-sub">${_trkChatEscapeHtml(rutasTxt)} - ${_trkChatEscapeHtml(disponible)}</span>
        </span>
        <span class="trk-admin-status ${_trkChatEscapeHtml(status)}">${_trkChatEscapeHtml(_trkOperacionStatusLabel(status))}</span>
    </button>`;
}

function _trkOperacionUnidadImagen(t) {
    const raw = _trkNormTxt([
        t?.unidad?.tipo_unidad,
        t?.tipo_unidad,
        t?.unidad?.marca,
        t?.unidad?.modelo,
    ].filter(Boolean).join(' '));
    if (raw.includes('torton')) return `${TRK_UNIDAD_ASSET_BASE}unidad_torton.png`;
    if (raw.includes('rabon')) return `${TRK_UNIDAD_ASSET_BASE}unidad_rabon.png`;
    if (raw.includes('grua')) return `${TRK_UNIDAD_ASSET_BASE}unidad_grua.png`;
    return `${TRK_UNIDAD_ASSET_BASE}unidad_camioneta.png`;
}

function _trkOperacionInfoBox(label, main, sub = '') {
    return `<div class="trk-admin-info-box">
        <span>${_trkChatEscapeHtml(label)}</span>
        <strong>${_trkChatEscapeHtml(main || '-')}</strong>
        ${sub ? `<small>${_trkChatEscapeHtml(sub)}</small>` : ''}
    </div>`;
}

function _trkAbrirModalOperacionTransportista(idTransportista) {
    const id = String(idTransportista || '');
    const t = (_trk.operacionTransportistas || []).find(x => String(x.id_transportista || '') === id);
    if (!t) {
        Swal.fire({
            icon: 'info',
            title: 'Transportista no encontrado',
            text: 'No se encontro el transportista dentro de la lectura operativa actual.',
            confirmButtonText: 'Aceptar',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false,
        });
        return;
    }
    $('#trkOpDetalleModalBody').html(_trkOperacionDetallePanel(t, { modal: true }));
    const modalEl = document.getElementById('modalOperacionTransportistaDetalle');
    if (!modalEl) return;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

function _trkOperacionDetallePanel(t, opts = {}) {
    if (!t) {
        return '<section class="trk-admin-detail"><div class="trk-admin-empty">Selecciona un transportista para revisar su operacion.</div></section>';
    }
    const status = String(t.estatus_operativo || 'disponible');
    const cap = Number(t.capacidad_total || 0);
    const proyectada = Number(t.capacidad_proyectada || 0);
    const usada = Number(t.capacidad_usada || 0);
    const pct = cap > 0 ? Math.min(100, Math.round((proyectada / cap) * 100)) : 0;
    const barClass = cap > 0 && proyectada >= cap ? 'danger' : (cap > 0 && pct >= 80 ? 'warn' : '');
    const cedisBase = [t.cedis_base?.nombre, t.cedis_base?.municipio, t.cedis_base?.estado].filter(Boolean).join(' / ') || t.empresa_origen || 'Sin CEDIS base';
    const unidad = t.unidad || {};
    const unidadNombre = _trkOperacionUnidadTexto(t);
    const disponibleTxt = cap > 0 ? `${t.capacidad_disponible ?? 0}` : '-';
    const rutas = (t.rutas || []).slice(0, 5).map(_trkOperacionRutaMini).join('');
    const alertas = _trkOperacionAlertasHtml(t);
    const tipoBadge = _trkTipoTransportistaBadge(t.tipo_transportista);
    const telefono = t.telefono || 'Sin telefono';
    const email = t.email || 'Sin email';
    const contacto = [t.telefono, t.email].filter(Boolean).join(' / ') || 'Sin contacto registrado';
    const empresa = t.empresa_origen || 'Sin empresa';
    const unidadTipo = unidad.tipo_unidad || t.tipo_unidad || 'Sin tipo';
    const unidadModelo = [unidad.marca, unidad.modelo, unidad.anio].filter(Boolean).join(' ') || unidadNombre;
    const placa = unidad.placa || 'Sin placa';
    const identificador = unidad.numero_economico || unidad.numero_serie || 'Sin identificador';
    const capacidadTxt = cap > 0 ? `${proyectada} / ${cap}` : 'Sin configurar';
    const recomendacion = t.recomendacion || 'Evaluar';
    const unidadImg = _trkOperacionUnidadImagen(t);
    const modalClass = opts.modal ? ' is-modal' : '';
    return `<section class="trk-admin-detail${modalClass}">
        <div class="trk-admin-detail-hero trk-admin-detail-overview">
            <div class="trk-admin-driver-strip">
                <div class="trk-admin-driver-identity">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        ${tipoBadge}
                        <span class="trk-admin-status ${_trkChatEscapeHtml(status)}">${_trkChatEscapeHtml(_trkOperacionStatusLabel(status))}</span>
                    </div>
                    <div class="trk-admin-detail-name">${_trkChatEscapeHtml(t.nombre_transportista || 'Sin nombre')}</div>
                    <div class="trk-admin-sub">${_trkChatEscapeHtml(cedisBase)}</div>
                    <div class="trk-admin-driver-counters">
                        <span>Rutas activas ${_trkChatEscapeHtml(t.rutas_activas || 0)}</span>
                        <span>Programadas ${_trkChatEscapeHtml(t.rutas_programadas || 0)}</span>
                    </div>
                </div>
                ${_trkOperacionInfoBox('Contacto', telefono, email)}
                ${_trkOperacionInfoBox('Empresa', empresa)}
            </div>
            <div class="trk-admin-unit-band">
                <div class="trk-admin-capacity-card">
                    <div class="trk-admin-capacity-main">
                        <div>
                            <span>Capacidad proyectada</span>
                            <strong>${_trkChatEscapeHtml(capacidadTxt)}</strong>
                        </div>
                        <span class="trk-admin-status ${_trkChatEscapeHtml(status)}">${_trkChatEscapeHtml(_trkOperacionStatusLabel(status))}</span>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>Uso estimado</span><span>${_trkChatEscapeHtml(cap > 0 ? `${pct}%` : 'Sin configurar')}</span>
                        </div>
                        <div class="trk-admin-progress"><div class="trk-admin-progress-bar ${barClass}" style="width:${pct}%;"></div></div>
                    </div>
                    <div class="trk-admin-stat-table">
                        <div class="trk-admin-stat-row"><span>Motos recolectadas</span><strong>${_trkChatEscapeHtml(usada)}</strong></div>
                        <div class="trk-admin-stat-row"><span>Disponibles</span><strong>${_trkChatEscapeHtml(disponibleTxt)}</strong></div>
                        <div class="trk-admin-stat-row"><span>Recomendacion</span><strong>${_trkChatEscapeHtml(recomendacion)}</strong></div>
                    </div>
                </div>
                <div class="trk-admin-vehicle-specs trk-admin-unit-specs">
                    <div class="trk-admin-metric"><span>Tipo</span><strong>${_trkChatEscapeHtml(unidadTipo)}</strong></div>
                    <div class="trk-admin-metric"><span>Placa</span><strong>${_trkChatEscapeHtml(placa)}</strong></div>
                    <div class="trk-admin-metric"><span>Identificador</span><strong>${_trkChatEscapeHtml(identificador)}</strong></div>
                    <div class="trk-admin-metric"><span>Disponible</span><strong>${_trkChatEscapeHtml(disponibleTxt)}</strong></div>
                </div>
                <div class="trk-admin-detail-vehicle">
                    <img src="${_trkChatEscapeHtml(unidadImg)}" alt="${_trkChatEscapeHtml(unidadTipo)}" class="trk-admin-vehicle-img" loading="lazy">
                </div>
            </div>
        </div>
        <div class="trk-admin-detail-body">
            <div class="trk-admin-panel-section">
                <div class="trk-admin-panel-title">Rutas operativas</div>
                <div class="trk-admin-route-list">${rutas || '<div class="trk-admin-route text-muted">Sin rutas activas o programadas.</div>'}</div>
            </div>
            <div class="trk-admin-panel-section">
                <div class="trk-admin-panel-title">Lectura operativa</div>
                <div class="trk-admin-stat-table">
                    <div class="trk-admin-stat-row"><span>Unidad asignada</span><strong>${_trkChatEscapeHtml(unidadNombre)}</strong></div>
                    <div class="trk-admin-stat-row"><span>CEDIS base</span><strong>${_trkChatEscapeHtml(cedisBase)}</strong></div>
                    <div class="trk-admin-stat-row"><span>Contacto</span><strong>${_trkChatEscapeHtml(contacto)}</strong></div>
                    <div class="trk-admin-stat-row"><span>Recomendacion</span><strong>${_trkChatEscapeHtml(recomendacion)}</strong></div>
                </div>
                <div class="mt-3">${alertas}</div>
                <div class="trk-admin-live mt-3" data-id-transportista-live="${_trkChatEscapeHtml(t.id_transportista || '')}">${_trkOperacionLiveHtml(t)}</div>
                <button type="button" class="btn btn-sm btn-label-info mt-3 btn-editar-unidad" data-id="${_trkChatEscapeHtml(t.id_transportista || '')}">
                    <i class="fa-solid fa-truck me-1"></i>${Number(unidad?.id_capacidad || 0) > 0 ? 'Editar unidad' : '+ Registrar Unidad'}
                </button>
            </div>
        </div>
    </section>`;
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
            <div class="d-flex flex-wrap justify-content-end gap-1">
                <button type="button" class="btn btn-xs btn-primary btn-ver-operacion-transportista" data-id="${_trkChatEscapeHtml(t.id_transportista || '')}">
                    <i class="fa-solid fa-eye me-1"></i>Ver datos completos
                </button>
                <button type="button" class="btn btn-xs btn-label-info btn-editar-unidad" data-id="${_trkChatEscapeHtml(t.id_transportista || '')}">
                    <i class="fa-solid fa-truck me-1"></i>${_trkChatEscapeHtml(unidadBtnTxt)}
                </button>
            </div>
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
            <div class="d-flex flex-wrap gap-1 mt-1">
                <button type="button" class="btn btn-xs btn-primary btn-ver-operacion-transportista" data-id="${_trkChatEscapeHtml(t.id_transportista || '')}">
                    <i class="fa-solid fa-eye me-1"></i>Ver datos completos
                </button>
                <button type="button" class="btn btn-xs btn-label-info btn-editar-unidad" data-id="${_trkChatEscapeHtml(t.id_transportista || '')}">
                    <i class="fa-solid fa-truck me-1"></i>${_trkChatEscapeHtml(unidadBtnTxt)}
                </button>
            </div>
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
            <span>${_trkChatEscapeHtml(ubicaciones || 'Sin ubicación')}</span>
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
                    _trkActualizarOperacionLive(idTransportista, '<i class="fa-solid fa-location-dot me-1"></i>Sin ubicación live reportada');
                    return;
                }
                const fecha = ubi.created_at || ubi.updated_at || '';
                const hora = fecha ? _trkChatFechaLocal(fecha) : 'Sin fecha';
                const lat = Number(ubi.lat);
                const lng = Number(ubi.lng);
                const coords = Number.isFinite(lat) && Number.isFinite(lng)
                    ? `${lat.toFixed(5)}, ${lng.toFixed(5)}`
                    : 'Coordenadas no disponibles';
                _trkActualizarOperacionLive(idTransportista, `<i class="fa-solid fa-location-arrow me-1"></i>Última ubicación ${_trkChatEscapeHtml(hora)} - ${_trkChatEscapeHtml(coords)}`);
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
    'BENITO JUAREZ': 'CIUDAD DE MEXICO',
    COYOACAN: 'CIUDAD DE MEXICO',
    CUAJIMALPA: 'CIUDAD DE MEXICO',
    'CUAJIMALPA DE MORELOS': 'CIUDAD DE MEXICO',
    'GUSTAVO A MADERO': 'CIUDAD DE MEXICO',
    GAM: 'CIUDAD DE MEXICO',
    IZTAPALAPA: 'CIUDAD DE MEXICO',
    IZTACALCO: 'CIUDAD DE MEXICO',
    TLAHUAC: 'CIUDAD DE MEXICO',
    TLALPAN: 'CIUDAD DE MEXICO',
    XOCHIMILCO: 'CIUDAD DE MEXICO',
    'VENUSTIANO CARRANZA': 'CIUDAD DE MEXICO',
    'LA MAGDALENA CONTRERAS': 'CIUDAD DE MEXICO',
    'MAGDALENA CONTRERAS': 'CIUDAD DE MEXICO',
    'MILPA ALTA': 'CIUDAD DE MEXICO',
    CUAUHTEMOC: 'CIUDAD DE MEXICO',
    'MIGUEL HIDALGO': 'CIUDAD DE MEXICO',
    'AREA RURAL': 'CIUDAD DE MEXICO',
    CLAVERIA: 'CIUDAD DE MEXICO',
    'COOPERATIVA PALO ALTO': 'CIUDAD DE MEXICO',
    'LOMAS DE CHAPULTEPEC': 'CIUDAD DE MEXICO',
    'LOMAS DE CHAPULTEPEC III SECCION': 'CIUDAD DE MEXICO',
    'LOMAS DE CHAPULTEPEC V SECCION': 'CIUDAD DE MEXICO',
    'LOMAS DE TARANGO': 'CIUDAD DE MEXICO',
    'POPULAR EMILIANO ZAPATA': 'CIUDAD DE MEXICO',
    'SAN JUAN DE ARAGON II SECCION': 'CIUDAD DE MEXICO',
    TLALPEXCO: 'CIUDAD DE MEXICO',
    CITLALLI: 'CIUDAD DE MEXICO',
    '3 DE MAYO': 'CIUDAD DE MEXICO',
    'LOS REYES LA PAZ': 'ESTADO DE MEXICO',
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
                mensaje: `El crédito #${fueraZona.id_credito} está fuera de CDMX/zona metropolitana. Asigna un transportista externo para esta ruta.`,
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
        _trkRenderMapTransportSummary();
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
    _trkRenderMapTransportSummary();
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
        _trkRenderMapTransportSummary();
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
            Sin ubicación operativa. No se puede asignar como destino hasta completar coordenadas o Estado/Municipio.
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
                <div class="text-muted"><strong>Dirección:</strong> ${_trkChatEscapeHtml(direccion)}</div>
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
    _trkRenderMapTransportSummary();
    $('#trkPlannerCedisBox').addClass('d-none').empty();
}

function _trkRenderMapTransportSummary() {
    const $wrap = $('#trkMapTransportSummary');
    if (!$wrap.length) return;

    const t = _trkTransportistaSeleccionado();
    const cedis = _trkCedisDestinoSeleccionado();
    if (!t && !cedis) {
        $wrap.addClass('d-none').empty();
        return;
    }

    const empresa = t ? (t.nombre_agencia || t.empresa_origen || t.cedis_base?.nombre || 'Sin CEDIS base') : 'Sin transportista';
    const contacto = t ? ([t.telefono, t.email].filter(Boolean).join(' / ') || 'Sin contacto') : 'Selecciona transportista';
    const unidad = t ? _trkOperacionUnidadTexto(t) : 'Unidad no asignada';
    const evalInfo = t ? _trkEvaluarTransportistaAsignacion(t, { creditos: _trk.creditosEnRuta }) : null;
    const nombreCedis = cedis?.nombre_agencia || 'Sin destino asignado';
    const ubicacionCedis = cedis
        ? [cedis.municipio, cedis.estado, cedis.codigo_postal ? `CP ${cedis.codigo_postal}` : ''].filter(Boolean).join(' / ') || 'Ubicacion no disponible'
        : 'Sin CEDIS destino';
    const recibe = cedis?.encargado || 'No disponible';
    const mapsUrl = cedis ? _trkCedisMapsUrl(cedis) : '';
    const puedeCambiar = _trk.idRutaEditando && _trkRutaPermiteCambioCedis(_trk.estatusRuta);

    $wrap.removeClass('d-none').html(`
        <div class="trk-map-driver-block">
            <div class="trk-map-driver-title">
                <i class="fa-solid fa-truck-fast" style="color:var(--track-color);"></i>
                <span>${_trkChatEscapeHtml(t?.nombre_transportista || 'Sin transportista asignado')}</span>
                ${t ? _trkTipoTransportistaBadge(t.tipo_transportista) : ''}
                ${evalInfo ? _trkDriverScoreHtml(evalInfo) : ''}
            </div>
            <div class="trk-map-driver-meta">
                <strong>Origen:</strong> ${_trkChatEscapeHtml(empresa)}<br>
                <strong>Contacto:</strong> ${_trkChatEscapeHtml(contacto)}<br>
                <strong>Unidad:</strong> ${_trkChatEscapeHtml(unidad)}
            </div>
        </div>
        <div class="trk-map-driver-block">
            <div class="trk-map-driver-title">
                <i class="fa-solid fa-warehouse" style="color:var(--track-color);"></i>
                <span>${_trkChatEscapeHtml(nombreCedis)}</span>
            </div>
            <div class="trk-map-driver-meta">
                <strong>Recibe:</strong> ${_trkChatEscapeHtml(recibe)}<br>
                <strong>Ubicacion:</strong> ${_trkChatEscapeHtml(ubicacionCedis)}<br>
                ${cedis?.direccion ? `<strong>Dirección:</strong> ${_trkChatEscapeHtml(cedis.direccion)}` : '<strong>Dirección:</strong> No disponible'}
            </div>
        </div>
        <div class="trk-map-driver-actions">
            ${mapsUrl ? `<a class="trk-cedis-map-btn" href="${_trkChatEscapeHtml(mapsUrl)}" target="_blank" rel="noopener noreferrer" title="Abrir CEDIS en Google Maps">
                <i class="fa-solid fa-location-dot maps-pin"></i><span>Maps</span>
            </a>` : ''}
            ${puedeCambiar ? `<button type="button" class="btn btn-sm btn-primary btn-cambiar-cedis-destino" data-id="${_trkChatEscapeHtml(_trk.idRutaEditando)}">
                <i class="fa-solid fa-rotate me-1"></i>Cambiar CEDIS
            </button>` : ''}
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
            Sin ubicación operativa. No se puede asignar como destino hasta completar coordenadas o Estado/Municipio.
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
                <div class="text-muted"><strong>Dirección:</strong> ${_trkChatEscapeHtml(cedis.direccion || 'No disponible')}</div>
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
        confirmButtonText: 'Sí, cambiar',
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
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo cambiar el CEDIS destino.', confirmButtonText: 'Aceptar' });
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
    const permite = String(det?.estatus_recoleccion || '').toLowerCase() === 'en_sitio' && Number(det?.id_detalle || 0) > 0;
    console.debug('[Tracking OTP] permite?', {
        id_detalle: det?.id_detalle || null,
        id_credito: det?.id_credito || null,
        estatus_recoleccion: det?.estatus_recoleccion || '',
        permite,
    });
    return permite;
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

function _trkOtpOrigenInfo(otp) {
    const raw = String(
        otp?.canal
        || otp?.tipo
        || otp?.origen
        || otp?.source
        || otp?.generado_por_actor
        || otp?.generated_by_actor
        || ''
    ).toLowerCase();
    if (raw.includes('mototrack') || raw.includes('android') || raw.includes('transportista') || raw.includes('manual') || raw.includes('normal')) {
        return { label: 'OTP MotoTrack', meta: raw || 'mototrack' };
    }
    if (raw.includes('emergencia') || raw.includes('sparta') || raw.includes('__SPARTA_SECRET_REDACTED__')) {
        return { label: 'OTP emergencia', meta: raw || 'emergencia' };
    }
    return { label: 'OTP vigente', meta: raw };
}

function _trkRenderOtpEstado(otp) {
    if (!otp) return '<span class="text-muted small">Sin OTP activo reportado</span>';
    const exp = _trkOtpExpiraInfo(otp.expira_at);
    const origen = _trkOtpOrigenInfo(otp);
    const intentosActuales = Number.isFinite(Number(otp.intentos)) ? Number(otp.intentos) : 0;
    const intentosMax = Number.isFinite(Number(otp.max_intentos)) ? Number(otp.max_intentos) : 3;
    const intentos = `${intentosActuales} / ${intentosMax}`;
    return `
        <div class="small ${exp.alerta ? 'text-danger fw-semibold' : 'text-muted'}">
            <i class="fa-solid fa-key me-1"></i>${_trkChatEscapeHtml(origen.label)} ${_trkChatEscapeHtml(otp.estatus || 'activo')}
            ${origen.meta ? `<span class="badge bg-label-secondary ms-1">${_trkChatEscapeHtml(origen.meta)}</span>` : ''}
            <span class="mx-1">|</span>${_trkChatEscapeHtml(exp.texto)}
            <span class="mx-1">|</span>Intentos ${_trkChatEscapeHtml(intentos)}
        </div>`;
}

function _trkOtpEstaActivo(otp) {
    if (!otp) return false;
    const estatus = String(otp.estatus || '').toLowerCase();
    if (estatus && estatus !== 'activo') return false;
    if (!otp.expira_at) return true;
    const fecha = new Date(String(otp.expira_at).replace(' ', 'T'));
    return Number.isNaN(fecha.getTime()) || fecha.getTime() > Date.now();
}

function _trkOtpEsEmergenciaParaSparta(otp) {
    if (!otp) return false;
    const tipo = String(otp.tipo || '').toLowerCase();
    const raw = String(`${otp.canal || ''} ${otp.origen || ''} ${otp.source || ''}`).toLowerCase();
    return tipo === 'emergencia' || raw.includes('emergencia') || raw.includes('sparta') || raw.includes('__SPARTA_SECRET_REDACTED__');
}

function _trkActualizarOtpEstadoDom(idDetalle, otp) {
    const html = _trkRenderOtpEstado(otp);
    $(`#trkOtpEstado-${idDetalle}, [data-otp-status-id="${idDetalle}"]`).html(html);
}

function _trkOtpCellHtml(det) {
    const idDetalle = Number(det?.id_detalle || 0);
    const estatus = String(det?.estatus_recoleccion || '').toLowerCase();
    if (!idDetalle) return '<span class="text-muted small">Sin detalle de recoleccion</span>';
    if (!_trkPuntoPermiteOtp(det)) {
        if (estatus === 'en_camino') {
            return '<span class="text-muted small">Disponible cuando el transportista confirme llegada</span>';
        }
        if (['recolectada', 'recolectado', 'completado'].includes(estatus)) {
            return '<span class="text-success small fw-semibold">Recoleccion confirmada</span>';
        }
        return '<span class="text-muted small">Disponible cuando el punto este en sitio</span>';
    }
    return `
        <div class="d-flex flex-column gap-1">
            <div id="trkOtpEstado-${idDetalle}" class="trk-otp-status" data-otp-status-id="${idDetalle}">
                <span class="spinner-border spinner-border-sm me-1"></span>Consultando OTP vigente...
            </div>
            <button type="button" class="btn btn-sm btn-warning btn-validar-otp" data-id="${idDetalle}">
                <i class="fa-solid fa-key me-1"></i>Validar OTP de MotoTrack
            </button>
        </div>`;
}

function _trkAlmacenOtpCellHtml(det) {
    const idUnidad = Number(det?.id_unidad || 0);
    const idCredito = Number(det?.id_credito || 0);
    const idDetalle = Number(det?.id_detalle || 0);
    const folio = String(det?.folio_unidad || '');
    const estatus = String(det?.estatus_inventario || '').trim().toLowerCase();
    const estatusRecoleccion = String(det?.estatus_recoleccion || '').trim().toLowerCase();
    if (estatusRecoleccion === 'entregada_cedis') {
        return '<span class="text-primary small fw-semibold"><i class="fa-solid fa-warehouse me-1"></i>Ingresada a CEDIS</span>';
    }
    const recolectada = ['recolectada', 'recolectado', 'completado', 'completada'].includes(estatusRecoleccion);
    if (!recolectada) {
        return '<span class="text-muted small">Disponible al recolectar</span>';
    }
    if (idUnidad && !['pendiente_entrega_cedis', 'pendiente_evidencias', 'incidencia_evidencias'].includes(estatus)) {
        const etiqueta = estatus ? estatus.replaceAll('_', ' ') : 'etapa no disponible';
        return `<div class="small text-muted">
            <i class="fa-solid fa-shield-halved me-1"></i>${_trkChatEscapeHtml(etiqueta)}
        </div>`;
    }
    return `<button type="button" class="btn btn-sm btn-dark btn-generar-otp-almacen"
        data-id-unidad="${idUnidad}"
        data-id-credito="${idCredito}"
        data-id-detalle="${idDetalle}"
        data-folio="${_trkChatEscapeHtml(folio)}">
        <i class="fa-solid fa-shield-halved me-1"></i>OTP emergencia
    </button>`;
}

function _trkAlmacenOtpMensajeError(r) {
    const detalle = r?.detail?.mensaje || r?.detail?.message || r?.mensaje?.mensaje
        || r?.mensaje?.message || r?.mensaje || r?.message || r?.error || '';
    return typeof detalle === 'string' && detalle
        ? detalle
        : 'No se pudo generar el OTP de entrega.';
}

async function _trkGenerarOtpEntregaAlmacen({ idUnidad, idCredito, idDetalle, folio }) {
    if (!idUnidad && !idCredito) return;
    const confirmar = await _trkSwalConFocoModalRuta({
        icon: 'warning',
        title: 'Generar OTP de emergencia',
        html: `<div class="text-start small">
            <p class="mb-2">El flujo normal exige que el almacenista genere el OTP desde MotoTrack. Usa este codigo solo si ese flujo no esta disponible.</p>
            <div class="border rounded p-2 bg-light">
                <b>${_trkChatEscapeHtml(folio || (idUnidad ? `Unidad #${idUnidad}` : 'Unidad recolectada'))}</b><br>
                <span class="text-muted">Credito #${_trkChatEscapeHtml(idCredito || 'N/A')}</span>
            </div>
            <p class="text-danger fw-semibold mt-2 mb-0">El uso queda registrado como contingencia de Torre de Control.</p>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Generar emergencia',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#172033',
        reverseButtons: true,
    });
    if (!confirmar.isConfirmed) return;

    Swal.fire({
        title: 'Generando OTP de entrega...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    try {
        const r = await trkFetch('/TrackingRecoleccion/trackingGenerarOtpAlmacen', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_unidad: idUnidad, id_credito: idCredito, id_detalle: idDetalle }),
        });
        if (!r?.success) {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo generar el OTP',
                text: _trkAlmacenOtpMensajeError(r),
                confirmButtonText: 'Aceptar',
            });
            return;
        }

        const otp = r.otp || r.datos?.otp || {};
        const codigo = String(otp.codigo || '').replace(/\D+/g, '').slice(0, 6);
        const unidadOtp = Number(otp.id_unidad || idUnidad || 0);
        if (!/^\d{6}$/.test(codigo)) {
            Swal.fire({
                icon: 'error',
                title: 'Codigo incompatible',
                text: 'La API no devolvio un OTP numerico de 6 digitos.',
                confirmButtonText: 'Aceptar',
            });
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'OTP de emergencia listo',
            html: `<div class="text-center">
                <div class="small text-muted mb-2">El transportista debe capturar este codigo en MotoTrack dentro del CEDIS.</div>
                <div class="display-5 fw-bold" style="letter-spacing:.2em;">${_trkChatEscapeHtml(codigo)}</div>
                <div class="small text-muted mt-2">${_trkChatEscapeHtml(_trkOtpExpiraInfo(otp.expira_at).texto)}</div>
                <div class="small text-muted mt-1">Unidad ${_trkChatEscapeHtml(folio || (unidadOtp ? `#${unidadOtp}` : `credito #${idCredito}`))}</div>
                <div class="alert alert-warning py-2 px-3 mt-3 mb-0 small text-start">
                    Codigo de un solo uso. No lo compartas fuera de esta entrega.
                </div>
            </div>`,
            confirmButtonText: 'Entendido',
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexion',
            text: error?.message || 'No se pudo generar el OTP de entrega.',
            confirmButtonText: 'Aceptar',
        });
    }
}

function _trkOtpInlineHtml(det) {
    const idDetalle = Number(det?.id_detalle || 0);
    const estatus = String(det?.estatus_recoleccion || '').toLowerCase();
    if (!idDetalle) return '';
    if (estatus === 'en_sitio') {
        return `<div class="trk-step-otp mt-2">
            <div class="small text-muted mb-1" data-otp-status-id="${idDetalle}">
                <span class="spinner-border spinner-border-sm me-1"></span>Consultando OTP vigente...
            </div>
            <button type="button" class="btn btn-sm btn-warning btn-validar-otp" data-id="${idDetalle}">
                <i class="fa-solid fa-key me-1"></i>Validar OTP de MotoTrack
            </button>
        </div>`;
    }
    if (estatus === 'en_camino') {
        return '<div class="small text-muted mt-2">Disponible cuando el transportista confirme llegada.</div>';
    }
    if (['recolectada', 'recolectado', 'completado'].includes(estatus)) {
        return '<div class="small text-success fw-semibold mt-2"><i class="fa-solid fa-circle-check me-1"></i>Recoleccion confirmada</div>';
    }
    return '';
}

async function _trkConsultarOtpActivo(idDetalle) {
    const r = await trkFetch(`/TrackingRecoleccion/trackingOtpActivo?id_detalle=${encodeURIComponent(idDetalle)}`);
    const otp = r?.otp || r?.datos?.otp || null;
    _trkActualizarOtpEstadoDom(idDetalle, (r && r.success) ? otp : null);
    return otp;
}

function _trkConsultarOtpsActivosDetalle(detalles) {
    (detalles || []).filter(_trkPuntoPermiteOtp).forEach(det => {
        _trkConsultarOtpActivo(det.id_detalle).catch(() => {
            $(`#trkOtpEstado-${det.id_detalle}`).html('<span class="text-muted small">Sin OTP activo reportado</span>');
        });
    });
}

function _trkOtpValidacionMensajeError(r) {
    const code = Number(r?.codigo_http || r?.status || r?.http_code || 0);
    const detalle = r?.mensaje?.mensaje || r?.mensaje?.message || r?.detail?.mensaje || r?.detail?.message || r?.mensaje || r?.message || r?.detail || r?.error || '';
    const raw = String(detalle).toLowerCase();
    if (code === 400 || code === 422 || /incorrect|inval|codigo/.test(raw)) return 'Codigo incorrecto';
    if (code === 401) return 'Sesion expirada. Recarga la pagina.';
    if (code === 403) {
        if (/validar este codigo|validar este codigo|tipo de otp/.test(raw)) {
            return 'Este OTP no puede validarse desde Sparta. Solicita al transportista generar un OTP nuevo desde MotoTrack y captura ese codigo.';
        }
        return 'No tienes permiso para validar este punto';
    }
    if (code === 404) return 'OTP o punto no encontrado';
    if (code === 409) {
        if (/recolect/.test(raw)) return 'Este punto ya fue recolectado';
        if (/expir/.test(raw) || /vencid/.test(raw)) return 'OTP expirado, solicita uno nuevo al transportista';
        return 'OTP expirado, usado o el punto no esta en sitio';
    }
    return typeof detalle === 'string' && detalle ? detalle : 'No se pudo validar el OTP.';
}

function _trkAplicarRespuestaOtpValidado(r, idDetalle) {
    const data = r?.data || r?.datos || r || {};
    const changes = data.changes || r?.changes || [];
    if (Array.isArray(changes) && changes.length) {
        _trkRTAplicarChanges(changes);
        return;
    }
    const id = data.id_detalle || data.detalle_id || r?.id_detalle || idDetalle;
    const nextId = data.next_id_detalle || data.siguiente_id_detalle || r?.next_id_detalle || r?.siguiente_id_detalle;
    const cambios = [];
    if (id) cambios.push({ id_detalle: id, estatus_recoleccion: 'recolectada' });
    if (nextId) cambios.push({ id_detalle: nextId, estatus_recoleccion: 'en_camino' });
    _trkRTAplicarChanges(cambios);
}

async function _trkValidarOtpDetalle(idDetalle) {
    if (!idDetalle) return;
    let otpActivo = null;
    try {
        otpActivo = await _trkConsultarOtpActivo(idDetalle);
    } catch (_) {
        otpActivo = null;
    }
    if (_trkOtpEstaActivo(otpActivo) && _trkOtpEsEmergenciaParaSparta(otpActivo)) {
        Swal.fire({
            icon: 'warning',
            title: 'OTP no valido para Sparta',
            text: 'Este OTP fue generado como emergencia. Solicita al transportista generar un OTP nuevo desde MotoTrack y captura ese codigo.',
            confirmButtonText: 'Entendido',
        });
        return;
    }
    const res = await _trkSwalConFocoModalRuta({
        icon: 'question',
        title: 'Validar OTP de recoleccion',
        html: `<div class="text-start">
            <p class="small text-muted mb-2">Ingresa el codigo mostrado por el transportista en MotoTrack.</p>
            <input id="trkOtpCodigoInput" class="form-control form-control-lg text-center fw-bold"
                type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                placeholder="000000" style="letter-spacing:.25em;">
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Validar y recolectar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488',
        focusConfirm: false,
        didOpen: () => {
            const input = document.getElementById('trkOtpCodigoInput');
            setTimeout(() => input?.focus(), 40);
            input?.addEventListener('input', () => {
                input.value = String(input.value || '').replace(/\D+/g, '').slice(0, 6);
            });
        },
        preConfirm: () => {
            const codigo = String(document.getElementById('trkOtpCodigoInput')?.value || '').replace(/\D+/g, '');
            if (!/^\d{6}$/.test(codigo)) {
                Swal.showValidationMessage('Captura los 6 digitos del OTP.');
                return false;
            }
            return codigo;
        },
    });
    if (!res.isConfirmed) return;

    Swal.fire({
        title: 'Validando OTP...',
        text: 'Confirmando recoleccion con el codigo de MotoTrack.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    try {
        const r = await trkFetch('/TrackingRecoleccion/trackingValidarOtp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_detalle: idDetalle, codigo: res.value, origen: 'sparta_emergencia' }),
        });
        if (!r.success) {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo validar el OTP',
                text: _trkOtpValidacionMensajeError(r),
                confirmButtonText: 'Aceptar',
            });
            return;
        }
        _trkAplicarRespuestaOtpValidado(r, idDetalle);
        Swal.fire({
            icon: 'success',
            title: 'Recoleccion autorizada con OTP de MotoTrack',
            timer: 2200,
            showConfirmButton: false,
        });
    } catch {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexion',
            text: 'No se pudo validar el OTP en este momento.',
            confirmButtonText: 'Aceptar',
        });
    }
}

async function _trkGenerarOtpDetalle(idDetalle) {
    if (!idDetalle) return;
    let otpActivo = null;
    Swal.fire({
        title: 'Consultando OTP vigente...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    try {
        otpActivo = await _trkConsultarOtpActivo(idDetalle);
    } catch (_) {
        otpActivo = null;
    }
    if (_trkOtpEstaActivo(otpActivo)) {
        const vigente = await Swal.fire({
            icon: 'info',
            title: 'Ya hay un OTP vigente',
            html: `<div class="text-start">
                ${_trkRenderOtpEstado(otpActivo)}
                <div class="alert alert-info py-2 px-3 mt-3 mb-0 small">
                    Si el OTP vigente viene de MotoTrack, conserva el flujo normal. Genera emergencia solo si el flujo normal no puede completarse.
                </div>
            </div>`,
            showCancelButton: true,
            confirmButtonText: 'Generar OTP emergencia',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
        });
        if (!vigente.isConfirmed) return;
    } else if (Swal.isLoading()) {
        Swal.close();
    }
    const confirmar = await Swal.fire({
        icon: 'warning',
        title: 'Generar OTP de emergencia?',
        html: `<div class="text-start small">
            <p class="mb-2">Este codigo es un respaldo operativo para que el transportista confirme la recoleccion cuando el gestor no puede completar el flujo normal.</p>
            <ul class="mb-0 ps-3">
                <li>Usalo solo para el punto seleccionado.</li>
                <li>No lo compartas fuera de esta operacion.</li>
                <li>Queda asociado al detalle de recoleccion.</li>
            </ul>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Si, generar OTP',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    });
    if (!confirmar.isConfirmed) return;
    Swal.fire({
        title: 'Generando OTP de emergencia...',
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
            Swal.fire({ icon: 'error', title: 'No se pudo generar OTP de emergencia', text: r.mensaje || r.message || r.detail || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        const otp = r.otp || r.datos?.otp || {};
        console.debug('[Tracking OTP] generado', {
            id_detalle: idDetalle,
            success: !!r.success,
            estatus: otp.estatus || '',
            canal: otp.canal || otp.tipo || otp.origen || '',
            expira_at: otp.expira_at || '',
            max_intentos: otp.max_intentos ?? null,
        });
        _trkActualizarOtpEstadoDom(idDetalle, otp);
        Swal.fire({
            icon: 'success',
            title: 'OTP de emergencia generado',
            html: `<div class="text-center">
                <div class="small text-muted mb-2">Comparte este codigo solo con el transportista asignado.</div>
                <div class="display-6 fw-bold" style="letter-spacing:.18em;">${_trkChatEscapeHtml(otp.codigo || '')}</div>
                <div class="small text-muted mt-2">${_trkChatEscapeHtml(_trkOtpExpiraInfo(otp.expira_at).texto)}</div>
                <div class="small text-muted mt-1">Intentos ${_trkChatEscapeHtml(Number(otp.intentos || 0))} / ${_trkChatEscapeHtml(Number(otp.max_intentos || 3))}</div>
                <div class="alert alert-warning py-2 px-3 mt-3 mb-0 small text-start">
                    Uso excepcional: el transportista lo capturara en Android para cerrar la recoleccion por emergencia.
                </div>
            </div>`,
            confirmButtonText: 'Entendido',
        });
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo generar el OTP de emergencia.', confirmButtonText: 'Aceptar' });
    }
}

function _trkDetalleActualizarRecoleccion(idDetalle, estatus) {
    if (!idDetalle) return;
    const $cell = $(`.trk-det-recoleccion[data-id="${idDetalle}"]`);
    if ($cell.length) $cell.html(_trkRenderEstatusRecoleccionDetalle(estatus));
    const $otp = $(`.trk-det-otp[data-id="${idDetalle}"]`);
    if ($otp.length) {
        $otp.html(_trkOtpCellHtml({ id_detalle: idDetalle, estatus_recoleccion: estatus }));
                if (String(estatus || '').toLowerCase() === 'en_sitio') {
            _trkConsultarOtpActivo(idDetalle).catch(() => {
                $(`[data-otp-status-id="${idDetalle}"]`).html('<span class="text-muted small">Sin OTP activo reportado</span>');
            });
        }
    }
    const $timelineStep = $(`#trkTimeline .trk-step[data-id="${idDetalle}"]`);
    if ($timelineStep.length && String(estatus || '').toLowerCase() !== 'en_sitio') {
        $timelineStep.find('.trk-step-otp').remove();
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

function _trkFechaSumarDias(fecha, dias) {
    if (!fecha) return '';
    const d = new Date(`${fecha}T00:00:00`);
    if (isNaN(d.getTime())) return '';
    d.setDate(d.getDate() + (parseInt(dias, 10) || 0));
    return d.toISOString().split('T')[0];
}

function _trkFechaDiffDias(fechaBase, fechaDestino) {
    const parse = (fecha) => {
        const parts = String(fecha || '').split('-').map(Number);
        if (parts.length !== 3 || parts.some(n => !Number.isFinite(n))) return null;
        return Date.UTC(parts[0], parts[1] - 1, parts[2]);
    };
    const base = parse(fechaBase);
    const destino = parse(fechaDestino);
    if (base === null || destino === null) return 0;
    return Math.round((destino - base) / 86400000);
}

function _trkFechaMinimaHorarioPlaneacion() {
    const fechaRuta = $('#rutaFecha').val() || _trkFechaMinimaProgramacion();
    return _trkFechaSumarDias(fechaRuta, -1) || fechaRuta;
}

function _trkPlanDayIndexPorFecha(fecha) {
    return _trkFechaDiffDias(_trkPlanFechaBaseRuta(), fecha);
}

function _trkSincronizarFechaFinalizacion(force = false) {
    const $fin = $('#rutaFechaFin');
    if (!$fin.length) return;
    const inicio = $('#rutaFecha').val() || _trkFechaMinimaProgramacion();
    $fin.attr('min', inicio).removeAttr('max');
    const actual = $fin.val();
    if (force || !actual || _trkCompararFecha(actual, inicio) < 0) {
        $fin.val(force ? inicio : '');
    }
}

function _trkInicializarModal() {
    // Fecha minima
    const minDate = _trkFechaMinimaProgramacion();
    document.getElementById('rutaFecha').min = minDate;
    document.getElementById('rutaFechaFin').min = minDate;
    document.getElementById('rutaFechaFin').removeAttribute('max');
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

    $('#rutaFecha').val(minDate);
    _trkSincronizarFechaFinalizacion(false);

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

    $('#btnAgregarTodosUbicacion').on('click', _trkAgregarTodosCreditosUbicacion);

    // Filtro de creditos por estado
    $('#crdFiltroEstado').on('change', function () {
        const est = $(this).val();
        _trkPoblarFiltroMunicipiosCrd(est);
        _trkRefrescarSelectCreditos();
    });

    // Filtro de creditos por municipio
    $('#crdFiltroMunicipio').on('change', _trkRefrescarSelectCreditos);

    $('#btnLimpiarFiltrosCrd').on('click', function () {
        $('#crdFiltroEstado').val('').trigger('change.select2');
        _trkPoblarFiltroMunicipiosCrd('');
        $('#crdFiltroMunicipio').val('').trigger('change.select2');
        _trkRefrescarSelectCreditos();
    });

    // Mapa refresh
    $('#btnRefreshMap').on('click', _trkRenderizarMapa);
    $('#btnTogglePlanner').on('click', _trkTogglePlaneador);
    $('#trkListaFiltroEstado').on('change', function () {
        _trkPoblarFiltroMunicipiosListaRuta();
        _trkRenderListaCreditos();
    });
    $('#trkListaFiltroMunicipio, #trkListaFiltroDireccion, #trkListaBuscar').on('change input', _trkRenderListaCreditos);
    $('#btnEliminarCreditosSinDireccion').on('click', _trkEliminarCreditosSinDireccionFiltrados);
    $('#btnConfirmarCreditosFiltrados').on('click', _trkConfirmarCreditosFiltrados);
    $('#trkPlannerGroups').on('click', '.trk-planner-group-head, .trk-planner-mun', function () {
        _trkPlannerEnfocarGrupo(this.dataset.estado || '', this.dataset.municipio || '');
    });
    $('#trkPlanDistribuir').on('click', _trkGenerarPlaneacionCompleta);
    $('#trkPlanGuardar').on('click', _trkGuardarPlaneacionRuta);
    $('#trkPlanMinParada')
        .on('input', function () { _trkSanitizarPlanMinParadaInput(this, false); })
        .on('blur', function () { _trkSanitizarPlanMinParadaInput(this, true); });
    $('#trkPlanDiasTrasladoEstado').on('input change', function () {
        const cfg = _trkPlanConfig();
        _trk.planeacionTrasladoEstados = cfg.trasladoEstados;
        _trkRenderPlaneacionDias();
    });
    $('#trkRouteDayList').on('click', '.btn-trk-plan-editar-hora', function () {
        const idCredito = Number($(this).data('credito') || 0);
        const credito = (_trk.creditosEnRuta || []).find(c =>
            idCredito && Number(c.id_credito || 0) === idCredito
        );
        if (credito) _trkAbrirEditarHorarioPlaneacion(credito);
    });
    $('#trkRouteDayList').on('change', '.trk-route-day-date-input', function () {
        _trkPlanCambiarFechaDia(Number($(this).data('day') || 0), this.value || '');
    });
    $('#trkPlanHorarioCerrar, #trkPlanHorarioCancelar').on('click', _trkCerrarEditorHorarioPlaneacion);
    $('#trkPlanHorarioGuardar').on('click', _trkGuardarEditorHorarioPlaneacion);
    $('#trkPlanHorarioMotivo').on('input', function () {
        $('#trkPlanHorarioMotivoCount').text(`${String(this.value || '').length}/300`);
    });
    $('#trkOppRefresh').on('click', () => _trkCargarOportunidadesRuta(true));
    $('#trkOppRadioKm, #trkOppLimit').on('change', () => _trkCargarOportunidadesRuta(true));
    $('#trkOppNivel').on('change', function () {
        _trk.oportunidadesFiltroNivel = this.value || '';
        _trkRenderOportunidadesRuta();
    });
    $('#trkRouteOppList').on('click', '.btn-trk-add-opportunity', function () {
        const idCredito = Number($(this).data('id') || 0);
        const candidato = (_trk.oportunidadesRuta?.candidatos || []).find(c => Number(c.id_credito || 0) === idCredito);
        if (candidato) _trkAgregarOportunidadARuta(candidato);
    });

    // Guardar
    $('#btnEnviarRuta').on('click', () => _trkGuardarRuta('enviar'));
    $('#btnActualizarRuta').on('click', async () => {
        const ok = await Swal.fire({
            icon: 'question',
            title: 'Guardar cambios?',
            text: 'Se actualizaran los datos de esta ruta.',
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
            const okAuto = await _trkForzarAutosaveBorrador();
            if (!okAuto) {
                const ok = await trkConfirm('Tienes cambios sin guardar. ¿Deseas salir sin guardar?');
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
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando catálogos necesarios</span>',
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
        html: '<span style="font-size:.875rem;color:#64748b;">Cargando catálogos necesarios</span>',
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
    _trkMiniChatLimpiar();
    _trk.idRutaEditando        = null;
    _trk.estatusRuta           = null;
    _trk.soloLectura           = false;
    _trk.rutaCancelada         = false;
    _trk.cargando              = false;
    _trk.creditosEnRuta        = [];
    _trk.routeLegDurations     = [];
    _trk.routeLegMetrics       = [];
    _trk.haychangios           = false;
    _trk.autosaveLastHash      = '';
    _trk.autosaveDirtyLists    = false;
    _trk.nombreRutaDisponible  = null;
    _trk.nombreRutaValidando   = false;
    _trk.nombreRutaUltimoValor = '';
    _trk.oportunidadesRuta     = null;
    _trk.oportunidadesRutaId   = null;
    _trk.oportunidadesLoading  = false;
    _trk.oportunidadesFiltroNivel = '';
    _trk.planeacionRuta        = [];
    _trk.planeacionEventos     = [];
    _trk.planeacionLoading     = false;
    _trk.planeacionRealResumen = null;
    _trk.planeacionRealWarnings = [];
    _trk.planeacionRealWarningGroups = [];
    _trk.planeacionRealLegs    = [];
    _trk.planeacionRealSource  = '';
    if (_trk.nombreRutaCheckTimer) {
        clearTimeout(_trk.nombreRutaCheckTimer);
        _trk.nombreRutaCheckTimer = null;
    }
    _trkSetNombreRutaStatus('', '');
    _trkSetAutosaveStatus('Autoguardado listo');
    _trkDesbloquearModal();
    $('#rutaNombre').val('');
    const minDate = _trkFechaMinimaProgramacion();
    $('#rutaFecha').val(minDate).attr('min', minDate);
    $('#rutaFechaFin').val('').attr('min', minDate).removeAttr('max');
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
    $('#trkMapTransportSummary').addClass('d-none').empty();
    $('#trkListaFiltroEstado, #trkListaFiltroMunicipio, #trkListaFiltroDireccion, #trkListaBuscar').val('');
    $('#trkListaFiltroMunicipio').prop('disabled', true);
    $('#trkOppRadioKm').val('10');
    $('#trkOppLimit').val('30');
    $('#trkOppNivel').val('');
    $('#trkPlanInicioJornada').val('10:00');
    $('#trkPlanFinJornada').val('19:00');
    $('#trkPlanMinParada').val('45');
    $('#trkPlanDiasTrasladoEstado').val(Math.min(TRK_PLAN_MAX_GAP_DAYS, Number(_trk.planeacionTrasladoEstados || 1)));
    _trkSyncTrasladoEstadosControl();
    _trkRefrescarSelectTransportistas();
    // Reset filtros de creditos
    $('#crdFiltroEstado').val('').trigger('change.select2');
    $('#crdFiltroMunicipio').html('<option value=""> -  Todos los municipios  - </option>').prop('disabled', true);
    _trkRefrescarSelectBuscable('#crdFiltroMunicipio');
    _trkPoblarFiltroEstadosCrd();
    $('#modalRegistrarRuta').removeClass('trk-planner-active');
    $('#trkPlannerPanel').addClass('d-none');
    $('#trkPlannerCedisBox').addClass('d-none').empty();
    $('#btnTogglePlanner').removeClass('btn-primary').addClass('btn-outline-primary')
        .html('<i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i>Planeador');
    _trkRenderPlaneadorPanel();
    _trkRenderPlaneacionDias();
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
    $sel.html('<option value="">Buscar por crédito, modelo, VIN...</option>');
    _trk.creditosDisponibles.forEach(c => {
        if (idsEnRuta.has(String(c.id_credito))) return;
        if (estFiltro && !_trkMismaUbicacionEstado(c.estado, estFiltro, c.municipio)) return;
        if (munFiltro && !_trkMismaUbicacionMunicipio(_trkMunicipioMayus(c.municipio, c.estado), munFiltro)) return;
        const modelo = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const label  = `#${c.id_credito}  -  ${modelo || '(sin modelo)'}  -  ${c.bin || ' - '}`;
        $sel.append(`<option value="${c.id_credito}">${label}</option>`);
    });
    _trkRefrescarSelectBuscable('#rutaCreditoSelect');
    _trkActualizarBotonAgregarTodosUbicacion();
}

function _trkCreditosMasivosUbicacion(estado, municipio = '') {
    if (!estado) return [];
    const idsEnRuta = _trkIdsCreditosEnRutaSet();
    return (_trk.creditosDisponibles || []).filter(c => {
        if (idsEnRuta.has(String(c.id_credito))) return false;
        if (!_trkMismaUbicacionEstado(c.estado, estado, c.municipio)) return false;
        if (municipio && !_trkMismaUbicacionMunicipio(_trkMunicipioMayus(c.municipio, c.estado), municipio)) return false;
        return true;
    });
}

function _trkActualizarBotonAgregarTodosUbicacion() {
    const $btn = $('#btnAgregarTodosUbicacion');
    if (!$btn.length) return;
    const estado = $('#crdFiltroEstado').val();
    const municipio = $('#crdFiltroMunicipio').val();
    const total = _trkCreditosMasivosUbicacion(estado, municipio).length;
    const label = municipio ? 'Agregar municipio' : 'Agregar estado';
    const disabled = !estado || total <= 0 || _trkRutaEstaCancelada() || _trk.soloLectura;
    $btn.prop('disabled', disabled);
    $btn.html(total > 0
        ? `<i class="fa-solid fa-layer-group me-1"></i>${label} (${total})`
        : '<i class="fa-solid fa-layer-group me-1"></i>Agregar todos');
}

async function _trkAgregarTodosCreditosUbicacion() {
    const estado = $('#crdFiltroEstado').val();
    const municipio = $('#crdFiltroMunicipio').val();
    if (!estado) {
        Swal.fire({
            icon: 'info',
            title: 'Selecciona un estado',
            text: 'Primero elige un estado para agregar créditos de forma masiva.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }

    const candidatos = _trkCreditosMasivosUbicacion(estado, municipio);
    if (!candidatos.length) {
        Swal.fire({
            icon: 'info',
            title: 'Sin créditos disponibles',
            text: 'No hay créditos restantes para agregar con este filtro.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }

    const tipoSeleccionado = _trkTransportistaSeleccionado()?.tipo_transportista || $('#rutaTipoTransportista').val();
    const fueraZonaInterna = tipoSeleccionado === 'interno'
        ? candidatos.filter(c => !_trkEsZonaInterna(_trkEstadoMayus(c.estado, c.municipio), _trkMunicipioMayus(c.municipio, c.estado)))
        : [];
    if (fueraZonaInterna.length) {
        Swal.fire({
            icon: 'warning',
            title: 'Zona no permitida',
            text: `Hay ${fueraZonaInterna.length} crédito(s) fuera de CDMX/zona metropolitana. Para esta ubicación usa transportista externo.`,
            confirmButtonText: 'Aceptar',
        });
        return;
    }

    const alcance = municipio ? `${estado} / ${municipio}` : `${estado} / todos los municipios`;
    const ok = await Swal.fire({
        icon: 'question',
        title: 'Agregar créditos a la ruta',
        html: `<div class="text-start">
            <div><b>Ubicación:</b> ${_trkChatEscapeHtml(alcance)}</div>
            <div><b>Créditos a agregar:</b> ${candidatos.length}</div>
            <div class="text-muted small mt-2">Se agregarán al final de la ruta y se recalculará el orden.</div>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Revisar',
        confirmButtonColor: '#0f9488',
    });
    if (!ok.isConfirmed) return;

    candidatos.forEach(cred => {
        const normalizado = {
            ...cred,
            estado_raw: cred.estado || '',
            estado: _trkEstadoMayus(cred.estado, cred.municipio),
            municipio: _trkMunicipioMayus(cred.municipio, cred.estado),
            orden_ruta: _trk.creditosEnRuta.length + 1,
            estatus_confirmacion_gestor: cred.estatus_confirmacion_gestor || 'pendiente',
        };
        _trk.creditosEnRuta.push(normalizado);
    });

    _trkRecalcularOrden();
    _trkAplicarEtasAutomaticas();
    _trkRenderListaCreditos();
    _trkRefrescarFiltrosCreditoUbicacion();
    _trkRenderizarMapa();
    _trkMarcarCambio();
    Swal.fire({
        icon: 'success',
        title: 'Créditos agregados',
        text: `${candidatos.length} crédito(s) fueron agregados a la ruta.`,
        timer: 1600,
        showConfirmButton: false,
    });
}

function _trkAgregarCreditoALista(cred) {
    // RN-03: no duplicados
    if (_trk.creditosEnRuta.find(c => String(c.id_credito) === String(cred.id_credito))) {
        Swal.fire({ icon: 'warning', title: 'Aviso', text: 'Este crédito ya está en la ruta.', confirmButtonText: 'Aceptar' });
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
            text: 'Este crédito está fuera de CDMX/zona metropolitana. Para esta ubicación usa transportista externo.',
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

async function _trkEliminarCreditosSinDireccionFiltrados() {
    if (_trkRutaEstaCancelada() || _trk.soloLectura) return;
    if (String($('#trkListaFiltroDireccion').val() || '') !== 'sin_direccion') {
        Swal.fire({
            icon: 'info',
            title: 'Activa el filtro Sin direccion',
            text: 'Primero filtra los creditos sin direccion para poder eliminarlos en bloque.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    const creditos = _trkCreditosRutaFiltrados().filter(_trkCreditoSinDireccionRuta);
    if (!creditos.length) {
        Swal.fire({
            icon: 'info',
            title: 'Sin creditos por eliminar',
            text: 'No hay creditos visibles sin direccion.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    const muestra = creditos.slice(0, 8).map(c =>
        `<div class="small text-start">#${_trkChatEscapeHtml(c.id_credito)} - ${_trkChatEscapeHtml(c.nombre_cliente || 'Sin cliente')}</div>`
    ).join('');
    const extra = creditos.length > 8
        ? `<div class="small text-muted text-start mt-1">Y ${creditos.length - 8} credito(s) mas...</div>`
        : '';
    const resp = await Swal.fire({
        icon: 'warning',
        title: 'Eliminar creditos sin direccion?',
        html: `<div class="text-start">
            <p class="mb-2">Se eliminaran <b>${creditos.length}</b> credito(s) visibles sin direccion de esta ruta.</p>
            <p class="mb-2 text-muted small">
                Antes de eliminarlos, torre de control puede asignar coordenadas desde el icono de puntero que aparece junto al estatus y al boton de descartar credito.
            </p>
            <div class="border rounded p-2 bg-light">${muestra}${extra}</div>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
    });
    if (!resp.isConfirmed) return;

    const ids = new Set(creditos.map(c => String(c.id_credito)));
    _trk.creditosEnRuta = (_trk.creditosEnRuta || []).filter(c => !ids.has(String(c.id_credito)));
    _trkRecalcularOrden();
    _trkAplicarEtasAutomaticas();
    _trkRenderListaCreditos();
    _trkRefrescarFiltrosCreditoUbicacion();
    _trkRenderizarMapa();
    _trkMarcarCambio();
    Swal.fire({
        icon: 'success',
        title: 'Creditos eliminados',
        text: `Se quitaron ${creditos.length} credito(s) sin direccion de la ruta.`,
        timer: 1800,
        showConfirmButton: false,
    });
}

async function _trkConfirmarCreditosFiltrados() {
    if (_trkRutaEstaCancelada() || _trk.soloLectura) return;
    const creditos = _trkCreditosRutaFiltrados();
    const pendientes = creditos.filter(c => c.estatus_confirmacion_gestor !== 'confirmado');
    if (!creditos.length) {
        Swal.fire({
            icon: 'info',
            title: 'Sin creditos visibles',
            text: 'No hay creditos con los filtros actuales.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    if (!pendientes.length) {
        Swal.fire({
            icon: 'success',
            title: 'Todo confirmado',
            text: 'Los creditos visibles ya estan confirmados.',
            timer: 1600,
            showConfirmButton: false,
        });
        return;
    }

    const estado = $('#trkListaFiltroEstado').val() || '';
    const municipio = $('#trkListaFiltroMunicipio').val() || '';
    const sinDireccion = String($('#trkListaFiltroDireccion').val() || '') === 'sin_direccion';
    const q = String($('#trkListaBuscar').val() || '').trim();
    const alcance = [
        estado ? `Estado: ${estado}` : '',
        municipio ? `Municipio: ${municipio}` : '',
        sinDireccion ? 'Filtro: Sin direccion' : '',
        q ? `Busqueda: ${q}` : '',
    ].filter(Boolean).join('<br>') || 'Todos los creditos de la ruta';

    const resp = await Swal.fire({
        icon: 'question',
        title: 'Confirmar creditos?',
        html: `<div class="text-start">
            <p class="mb-2">Se marcaran como <b>Confirmado</b> <b>${pendientes.length}</b> credito(s).</p>
            <div class="alert alert-info py-2 px-3 small mb-0">
                <b>Alcance:</b><br>${_trkChatEscapeHtml(alcance).replace(/&lt;br&gt;/g, '<br>')}
            </div>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Si, confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488',
    });
    if (!resp.isConfirmed) return;

    const ids = new Set(pendientes.map(c => String(c.id_credito)));
    (_trk.creditosEnRuta || []).forEach(c => {
        if (ids.has(String(c.id_credito))) {
            c.estatus_confirmacion_gestor = 'confirmado';
        }
    });
    _trkAplicarEtasAutomaticas();
    _trkRenderListaCreditos();
    _trkRenderizarMapa();
    _trkMarcarCambio();
    Swal.fire({
        icon: 'success',
        title: 'Creditos confirmados',
        text: `Se confirmaron ${pendientes.length} credito(s).`,
        timer: 1600,
        showConfirmButton: false,
    });
}

function _trkRenderListaCreditos() {
    const $list = $('#rutaCreditosList');
    _trkPoblarFiltrosListaRuta();
    const creditosVisibles = _trkCreditosRutaFiltrados();
    const isEmpty = _trk.creditosEnRuta.length === 0;
    const filaLectura = _trkRutaEstaCancelada();
    $('#rutaCreditosCount').text(_trk.creditosEnRuta.length);
    _trkActualizarAccionesListaRuta();
    if (_trk.sortableInstance) _trk.sortableInstance.option('disabled', filaLectura);
    _trkRenderPlaneadorPanel();
    _trkRenderPlaneacionDias();

    if (isEmpty) {
        $list.html(`<div class="text-center text-muted py-3 small" id="rutaCreditosEmpty">
            <i class="fa-solid fa-motorcycle opacity-25 fa-2x mb-1 d-block"></i>
            Aún no hay créditos en esta ruta
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
                _trk.routeLegMetrics = legs.map(l => ({
                    duration_seconds: l.duration_in_traffic?.value || l.duration?.value || null,
                    distance_meters: l.distance?.value || null,
                }));
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
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no esta disponible (falta API key).', confirmButtonText: 'Aceptar' });
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

function _trkCreditoPosicionSugerida(cred) {
    const raw = cred?._geo_sugerida || null;
    if (!raw) return null;
    const lat = parseFloat(raw.lat ?? raw.latitud);
    const lng = parseFloat(raw.lng ?? raw.longitud);
    if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) return null;
    return { lat, lng };
}

function _trkPickerCentroFallback() {
    const cedisPos = _trkCedisDestinoPosicion(_trkCedisDestinoSeleccionado());
    if (cedisPos) return cedisPos;
    return { lat: 23.6345, lng: -102.5528 };
}

async function _trkAbrirMapPicker(cred, opts = {}) {
    if (!cred) return;
    if (!window._trackGoogleMapsKey) {
        Swal.fire({ icon: 'warning', title: 'Sin API Key', text: 'Google Maps no esta disponible (falta API key).', confirmButtonText: 'Aceptar' });
        return;
    }

    if (opts.autoCenter !== false && !_trkCreditoPosicionBasica(cred) && !_trkCreditoPosicionSugerida(cred) && _trkDireccionBusquedaCredito(cred)) {
        let loaderAbierto = false;
        try {
            Swal.fire({
                title: 'Buscando ubicacion...',
                text: 'Consultando Google Maps para posicionar el pin sugerido.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading(),
            });
            loaderAbierto = true;
            const mapsOk = await _trkEsperarGoogleMapsDisponible();
            if (mapsOk) {
                const sugerida = await _trkGeocodificarCreditoSugerido(cred);
                if (sugerida) cred._geo_sugerida = sugerida;
            }
        } catch (_) {
            // Si Google no sugiere ubicacion, el picker abre en el fallback operativo.
        } finally {
            if (loaderAbierto && Swal.isVisible()) Swal.close();
        }
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

    // Centro: coordenadas manuales > coords del credito > ubicacion sugerida > CEDIS/Mexico.
    const centerPos = _trkCreditoPosicionBasica(cred)
        || _trkCreditoPosicionSugerida(cred)
        || _trkPickerCentroFallback();
    let centerLat = centerPos.lat;
    let centerLng = centerPos.lng;

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
        } else {
            const sugeridaPos = _trkCreditoPosicionSugerida(cred);
            if (sugeridaPos) {
                const latLng = new google.maps.LatLng(sugeridaPos.lat, sugeridaPos.lng);
                _trkPickerAplicarLugar(
                    latLng,
                    cred._geo_sugerida?.components || null,
                    cred._geo_sugerida?.direccion || 'Ubicacion sugerida por Google Maps'
                );
            } else if (_trkDireccionBusquedaCredito(cred) && window.google?.maps?.Geocoder) {
                _trkGeocodificarCreditoSugerido(cred).then(sugerida => {
                    if (!sugerida) return;
                    cred._geo_sugerida = sugerida;
                    _trkPickerAplicarLugar(
                        new google.maps.LatLng(sugerida.lat, sugerida.lng),
                        sugerida.components || null,
                        sugerida.direccion || 'Ubicacion sugerida por Google Maps'
                    );
                });
            }
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

async function _trkConfirmarMapPicker() {
    const lat = _trkPicker.selectedLat;
    const lng = _trkPicker.selectedLng;
    if (lat === null || lng === null) return;

    const btn = document.getElementById('btnConfirmarMapPicker');
    const btnHtml = btn?.innerHTML || '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
    }

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
        cred._geo_sugerida = null;
        cred._geo_auto_resuelto = true;
        cred._geo_auto_fallo = false;
        _trk.creditoPosiciones[String(cred.id_credito || '')] = { lat, lng };
        const payloadCoord = _trkCoordenadasPayloadCredito(cred);
        if (payloadCoord && Number(_trk.idRutaEditando || 0)) {
            try {
                await _trkPersistirCoordenadasRuta([payloadCoord]);
            } catch (err) {
                console.warn('[Tracking Recoleccion] No se pudo persistir coordenada manual antes del recalculo', err);
                _trkSetAutosaveStatus('Coordenada pendiente de guardar', 'warning');
            }
        }
        _trkMarcarCambio();
    }

    if (btn) {
        btn.disabled = false;
        btn.innerHTML = btnHtml;
    }

    _trkPicker.closingByConfirm = true;
    _trkPicker.modal.hide();
    _trkActualizarAdvertenciasUbicacionActuales();
    _trkRenderListaCreditos();
    _trkRenderPlaneacionRealInfo();
    _trkRenderizarMapa();
    _trkRecalcularTiemposSiPinsCompletos();
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
const TRK_PLAN_MAX_GAP_DAYS = 2;

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
    // ETA por credito queda deprecado: la ruta opera con fecha inicio, fecha final y hora de salida.
    return;
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
        text: 'Tu ruta no es la más óptima para el recorrido, implicaría mayor inversión de recursos. ¿Estás seguro de que quieres enviarla así?',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviarla así',
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
                <span>Estado</span><span>Municipio</span><span>Créditos</span>
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
                        <span class="badge bg-label-primary">${estado.total} crédito${estado.total !== 1 ? 's' : ''}</span>
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

async function _trkConfirmarResumenEnvioRuta({ nombre, fecha, fechaFinalizacion }) {
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
                        <div class="small text-muted fw-semibold text-uppercase">Fecha de inicio</div>
                        <div>${_trkChatEscapeHtml(fecha || 'Sin fecha')}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted fw-semibold text-uppercase">Fecha final</div>
                        <div>${_trkChatEscapeHtml(fechaFinalizacion || fecha || 'Sin fecha')}</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted fw-semibold text-uppercase">Hora de salida</div>
                        <div>${_trkChatEscapeHtml(_trkFormatHora(horaSalida))}</div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="small text-muted fw-semibold text-uppercase">Créditos confirmados</div>
                    <span class="badge bg-success">${confirmados}</span>
                </div>
                <div>
                    <div class="small text-muted fw-semibold text-uppercase mb-1">Ubicaciones</div>
                    ${ubicacionesHtml}
                </div>
                <div class="alert alert-info py-2 px-3 mt-3 mb-0">
                    ¿Deseas confirmar la ruta?
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar ruta',
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
        fecha_finalizacion: $('#rutaFechaFin').val() || '',
        hora: _trkHoraToPayload(),
        id_transportista: $('#rutaTransportistaTracking').val() || '',
        id_cedis_destino: $('#rutaCedisDestino').val() || '',
        creditos: _trk.creditosEnRuta.map(c => ({
            id_credito: c.id_credito,
            orden_ruta: c.orden_ruta,
            fecha_planeacion: c.fecha_planeacion || null,
            orden_dia: c.orden_dia || null,
            estatus_planeacion: c.estatus_planeacion || 'programado',
            ..._trkPlanCamposPayload(c),
            estatus_confirmacion_gestor: c.estatus_confirmacion_gestor || 'pendiente',
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

function _trkEsActualizacionOperativa(modo) {
    if (modo !== 'actualizar') return false;
    if (!_trk.idRutaEditando) return false;
    return String(_trk.estatusRuta || '').toLowerCase() !== 'borrador';
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
        _trkValidarNombreRutaDisponible(true);
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
        if (_trk.nombreRutaDisponible) {
            _trkSetNombreRutaStatus('ok', 'Nombre disponible.');
        } else {
            _trkSetNombreRutaStatus('error', 'Nombre no permitido, ya existe una ruta con este nombre.');
        }
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
            _trkSetNombreRutaStatus('ok', r.message || r.mensaje || 'Nombre disponible.');
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
    _trkSincronizarFechaFinalizacion();
    const fechaFinalizacion = $('#rutaFechaFin').val() || fecha;
    const idTransportista = $('#rutaTransportistaTracking').val();
    const transportistaSel = _trkTransportistaSeleccionado();
    const tipoTransportista = transportistaSel?.tipo_transportista || '';
    const idAgenciaTracking = transportistaSel?.id_agencia || '';
    const idCedisDestino = $('#rutaCedisDestino').val();
    const geoRuta = _trkGeoResumenRuta();
    const municipio = _trkMunicipioMayus($('#crdFiltroMunicipio').val() || geoRuta.municipio, $('#crdFiltroEstado').val() || geoRuta.estado);
    const estado = _trkEstadoMayus($('#crdFiltroEstado').val() || geoRuta.estado, municipio);

    const payload = {
        id_ruta: _trk.idRutaEditando || 0,
        nombre_ruta: nombre,
        estado,
        municipio,
        fecha_programada: fecha,
        fecha_finalizacion: fechaFinalizacion,
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
            fecha_eta: null,
            hora_eta_ini: null,
            hora_eta_fin: null,
            fecha_planeacion: c.fecha_planeacion || null,
            orden_dia: c.orden_dia || null,
            estatus_planeacion: c.estatus_planeacion || 'programado',
            ..._trkPlanCamposPayload(c),
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
    _trkSincronizarFechaFinalizacion();
    const fechaFinalizacion = $('#rutaFechaFin').val();
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
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debe agregar al menos un crédito a la ruta.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!fecha) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La fecha programada es obligatoria.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (!fechaFinalizacion) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'La fecha final es obligatoria.', confirmButtonText: 'Aceptar' });
        return;
    }
    if (_trkCompararFecha(fechaFinalizacion, fecha) < 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Fecha final invalida',
            text: 'La fecha final no puede ser anterior a la fecha de inicio.',
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
                cancelButtonText: 'Cancelar',
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
            Swal.fire({ icon: 'warning', title: 'Pendiente', text: 'Todos los créditos deben tener confirmación del gestor para enviar la ruta.', confirmButtonText: 'Aceptar' });
            return;
        }
    }

    const continuarRuta = await _trkConfirmarRutaNoOptimaSiAplica(modo);
    if (!continuarRuta) return;
    if (modo === 'enviar') {
        const confirmarEnvio = await _trkConfirmarResumenEnvioRuta({ nombre, fecha, fechaFinalizacion });
        if (!confirmarEnvio) return;
    }

    const payload = {
        id_ruta:          _trk.idRutaEditando || 0,
        nombre_ruta:      nombre,
        estado,
        municipio,
        fecha_programada: fecha,
        fecha_finalizacion: fechaFinalizacion,
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
            fecha_eta:                   null,
            hora_eta_ini:                null,
            hora_eta_fin:                null,
            fecha_planeacion:            c.fecha_planeacion || null,
            orden_dia:                   c.orden_dia || null,
            estatus_planeacion:          c.estatus_planeacion || 'programado',
            ..._trkPlanCamposPayload(c),
        })),
    };

    const $btnGuardar = $('#btnGuardarBorrador, #btnEnviarRuta, #btnActualizarRuta');
    $btnGuardar.prop('disabled', true);

    const esActualizacionOperativa = _trkEsActualizacionOperativa(modo);
    const endpointGuardarRuta = esActualizacionOperativa
        ? '/TrackingRecoleccion/actualizarRutaOperativa'
        : '/TrackingRecoleccion/guardarRuta';
    const payloadGuardarRuta = esActualizacionOperativa
        ? { ...payload, creditos: [] }
        : payload;

    trkFetch(endpointGuardarRuta, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payloadGuardarRuta),
    })
    .then(r => {
        if (r.success) {
            const idRutaGuardada = _trkExtraerIdRutaRespuesta(r) || payloadGuardarRuta.id_ruta || _trk.idRutaEditando;
            if (idRutaGuardada) {
                _trk.idRutaEditando = parseInt(idRutaGuardada, 10) || idRutaGuardada;
            }
            _trk.haychangios = false;
            _trk.autosaveLastHash = _trkAutosaveHash();
            if (modo === 'enviar' && idRutaGuardada) _trkDescargarPdfEvidenciaRuta(idRutaGuardada);
            const mensajeOk = esActualizacionOperativa
                ? 'Actualizacion operativa aplicada sin reiniciar el progreso.'
                : (modo === 'borrador'
                ? 'Borrador guardado correctamente.'
                : (modo === 'actualizar' ? 'Ruta actualizada correctamente.' : 'Ruta enviada correctamente.'));
            Swal.fire({ icon: 'success', title: 'Listo!', text: mensajeOk, timer: 2000, showConfirmButton: false });
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
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Error de conexión al guardar.', confirmButtonText: 'Aceptar' });
    })
    .finally(() => $btnGuardar.prop('disabled', false));
}

// --- Panel lateral de operaciones -------------------------
function _trkOpsCedisFromDetalle(d) {
    return _trkNormalizarCedisDestino(d?.cedis_destino || (d?.id_cedis_destino || d?.cedis_destino_nombre ? {
        id_agencia: d.id_cedis_destino,
        nombre_agencia: d.cedis_destino_nombre,
        direccion: d.cedis_destino_direccion,
        estado: d.cedis_destino_estado,
        municipio: d.cedis_destino_municipio,
        codigo_postal: d.cedis_destino_codigo_postal,
        latitud: d.cedis_destino_latitud,
        longitud: d.cedis_destino_longitud,
        telefono: d.cedis_destino_telefono,
        encargado: d.cedis_destino_encargado,
        email: d.cedis_destino_email,
        horario: d.cedis_destino_horario,
        link_ubicacion: d.cedis_destino_link_ubicacion,
    } : null));
}

function _trkOpsMetricas(d) {
    const detalle = d?.detalle || [];
    const total = detalle.length;
    const confirmados = detalle.filter(x => String(x.estatus_confirmacion_gestor || '') === 'confirmado').length;
    const recolectados = detalle.filter(x => ['recolectada', 'completado'].includes(String(x.estatus_recoleccion || ''))).length;
    const pendientes = Math.max(0, total - recolectados);
    const pct = total ? Math.round((recolectados / total) * 100) : 0;
    return { total, confirmados, recolectados, pendientes, pct };
}

function _trkOpsHoraRuta(d) {
    if (d?.act_hora_1) {
        return `${_trkFormatHora(d.act_hora_1)} (actualizada)`;
    }
    return d?.hora_inicial ? _trkFormatHora(d.hora_inicial) : 'Sin hora';
}

function _trkOpsCreditoPos(det) {
    const lat = parseFloat(det?.latitud_manual ?? det?.latitud);
    const lng = parseFloat(det?.longitud_manual ?? det?.longitud);
    if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat === 0 || lng === 0) return null;
    return { lat, lng };
}

function _trkOpsMarkerIcon(num, fill = '#0d9488', text = '#fff') {
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34">
        <circle cx="17" cy="17" r="15" fill="${fill}" stroke="#fff" stroke-width="2.5"/>
        <text x="17" y="22" text-anchor="middle" fill="${text}"
              font-size="${num > 9 ? 11 : 13}" font-weight="bold" font-family="Arial,sans-serif">${num}</text>
    </svg>`;
    return {
        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
        scaledSize: new google.maps.Size(34, 34),
        anchor: new google.maps.Point(17, 17),
    };
}

function _trkOpsCedisIcon() {
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 42 42">
        <circle cx="21" cy="21" r="18" fill="#2563eb" stroke="#fff" stroke-width="3"/>
        <path d="M12 20 21 14l9 6v10H12V20Z" fill="#fff"/>
        <path d="M15 30v-7h12v7M15 23h12M21 23v7" fill="none" stroke="#2563eb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`;
    return {
        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
        scaledSize: new google.maps.Size(42, 42),
        anchor: new google.maps.Point(21, 21),
    };
}

function _trkOpsLimpiarMapa() {
    (_trk.opsMapMarkers || []).forEach(m => m.setMap?.(null));
    _trk.opsMapMarkers = [];
    if (_trk.opsMapPolyline) { _trk.opsMapPolyline.setMap(null); _trk.opsMapPolyline = null; }
    if (_trk.opsLiveMarker) { _trk.opsLiveMarker.setMap(null); _trk.opsLiveMarker = null; }
    if (_trk.opsLivePolyline) { _trk.opsLivePolyline.setMap(null); _trk.opsLivePolyline = null; }
    if (_trk.opsTrafficLayer) { _trk.opsTrafficLayer.setMap(null); _trk.opsTrafficLayer = null; }
}

function _trkOpsRenderTimeline(detalle) {
    if (!detalle?.length) {
        return '<div class="text-center text-muted small py-3">Sin créditos registrados en esta ruta.</div>';
    }
    return `<div class="trk-ops-timeline">${detalle.map((det, i) => {
        const orden = det.orden_ruta || (i + 1);
        const modelo = [det.moto_marca, det.modelo || det.moto_modelo].filter(Boolean).join(' ') || 'Unidad no disponible';
        const lugar = [_trkEstadoMayus(det.estado, det.municipio), _trkMunicipioMayus(det.municipio, det.estado)].filter(Boolean).join(' / ') || 'Sin ubicación';
        const eta = (det.fecha_eta || det.hora_eta_ini || det.hora_eta_fin)
            ? `<div class="trk-ops-step-meta"><i class="fa-solid fa-clock me-1"></i>${_trkChatEscapeHtml(det.fecha_eta || 'Sin fecha')} ${det.hora_eta_ini ? 'Inicio ' + _trkFormatHora(det.hora_eta_ini) : ''} ${det.hora_eta_fin ? 'Llegada ' + _trkFormatHora(det.hora_eta_fin) : ''}</div>`
            : '';
        return `<article class="trk-ops-step">
            <span class="trk-ops-step-dot">${_trkChatEscapeHtml(orden)}</span>
            <div class="trk-ops-step-title">#${_trkChatEscapeHtml(det.id_credito || '-')} - ${_trkChatEscapeHtml(det.nombre_cliente || 'Sin cliente')}</div>
            <div class="trk-ops-step-meta">
                <i class="fa-solid fa-motorcycle me-1" style="color:var(--track-color);"></i>${_trkChatEscapeHtml(modelo)}
            </div>
            <div class="trk-ops-step-meta">${_trkRenderLocationBadges(det.estado, det.municipio)}</div>
            <div class="trk-ops-step-meta">${_trkRenderEstatusRecoleccionDetalle(det.estatus_recoleccion)}</div>
            ${eta}
            ${det.direccion ? `<div class="trk-ops-step-meta"><i class="fa-solid fa-location-dot me-1"></i>${_trkChatEscapeHtml(det.direccion)}</div>` : ''}
        </article>`;
    }).join('')}</div>`;
}

function _trkOpsCountsHtml(data) {
    return `<span class="d-inline-flex align-items-center gap-1">
        <span class="badge bg-warning text-dark">${_trkChatEscapeHtml(data.pendientes || 0)}</span>
        <span class="badge bg-success">${_trkChatEscapeHtml(data.confirmados || 0)}</span>
    </span>`;
}

function _trkOpsRenderPlannerGroups(detalle) {
    if (!detalle?.length) {
        return '<div class="text-center text-muted small py-3">Sin créditos para agrupar por estado.</div>';
    }
    const tree = {};
    detalle.forEach(det => {
        const estado = _trkEstadoMayus(det.estado, det.municipio) || 'SIN ESTADO';
        const municipio = _trkMunicipioMayus(det.municipio, det.estado) || 'SIN MUNICIPIO';
        tree[estado] ??= { total: 0, confirmados: 0, pendientes: 0, municipios: {} };
        tree[estado].municipios[municipio] ??= { total: 0, confirmados: 0, pendientes: 0 };
        [tree[estado], tree[estado].municipios[municipio]].forEach(bucket => {
            bucket.total++;
            if (String(det.estatus_confirmacion_gestor || '') === 'confirmado') bucket.confirmados++;
            else bucket.pendientes++;
        });
    });
    const groups = Object.entries(tree)
        .sort((a, b) => b[1].total - a[1].total || a[0].localeCompare(b[0]))
        .map(([estado, data]) => {
            const municipios = Object.entries(data.municipios)
                .sort((a, b) => b[1].total - a[1].total || a[0].localeCompare(b[0]))
                .map(([municipio, m]) => `
                    <button type="button" class="trk-ops-group-mun" data-ops-filter="${_trkChatEscapeHtml(`${estado} ${municipio}`)}">
                        <span>${_trkChatEscapeHtml(municipio)}</span>
                        ${_trkOpsCountsHtml(m)}
                    </button>
                `).join('');
            return `<div class="trk-ops-group" data-ops-filter="${_trkChatEscapeHtml(estado)}">
                <button type="button" class="trk-ops-group-head">
                    <span>${_trkChatEscapeHtml(estado)}</span>
                    ${_trkOpsCountsHtml(data)}
                </button>
                ${municipios}
            </div>`;
        }).join('');
    return `<input type="search" class="form-control form-control-sm trk-ops-group-search" placeholder="Buscar estado o municipio...">
        <div class="trk-ops-groups">${groups}</div>`;
}

function _trkOpsRenderCedisPanel(d) {
    const cedis = _trkOpsCedisFromDetalle(d);
    const mapsUrl = cedis ? _trkCedisMapsUrl(cedis) : '';
    const permiteCambiarCedis = _trkRutaPermiteCambioCedis(d.estatus_ruta);
    return `<section class="trk-ops-panel trk-ops-cedis-panel">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
                <div class="trk-ops-operator-title"><i class="fa-solid fa-warehouse me-1" style="color:var(--track-color);"></i>CEDIS destino</div>
                <div class="trk-ops-block-text">Datos de entrega y recepción del vehículo.</div>
            </div>
            ${cedis ? '<span class="badge bg-label-primary">Destino</span>' : '<span class="badge bg-label-warning">Sin destino</span>'}
        </div>
        <div class="trk-ops-block mt-3">
            <span class="trk-ops-block-label">CEDIS</span>
            <div class="trk-ops-block-title mt-1">${_trkChatEscapeHtml(cedis?.nombre_agencia || 'Sin destino asignado')}</div>
            <div class="trk-ops-block-text mt-1">${_trkChatEscapeHtml([cedis?.municipio, cedis?.estado, cedis?.codigo_postal ? 'CP ' + cedis.codigo_postal : ''].filter(Boolean).join(' / ') || 'Sin ubicación')}</div>
            ${cedis?.direccion ? `<div class="trk-ops-block-text mt-1">${_trkChatEscapeHtml(cedis.direccion)}</div>` : ''}
        </div>
        <div class="trk-ops-block mt-2">
            <span class="trk-ops-block-label">Recepcion</span>
            <div class="trk-ops-block-text mt-1"><b>Recibe:</b> ${_trkChatEscapeHtml(cedis?.encargado || 'No disponible')}</div>
            <div class="trk-ops-block-text"><b>Contacto:</b> ${_trkChatEscapeHtml([cedis?.telefono, cedis?.email].filter(Boolean).join(' / ') || 'No disponible')}</div>
            <div class="trk-ops-block-text"><b>Horario:</b> ${_trkChatEscapeHtml(cedis?.horario || 'No disponible')}</div>
        </div>
        <div class="trk-ops-action-grid mt-3">
            ${permiteCambiarCedis ? `<button type="button" class="btn btn-sm btn-label-info btn-cambiar-cedis-destino" data-id="${_trkChatEscapeHtml(d.id_ruta || '')}">
                <i class="fa-solid fa-warehouse me-1"></i>Cambiar CEDIS
            </button>` : ''}
            ${mapsUrl ? `<a class="btn btn-sm btn-label-secondary" href="${_trkChatEscapeHtml(mapsUrl)}" target="_blank" rel="noopener noreferrer">
                <i class="fa-solid fa-map-location-dot me-1"></i>Maps
            </a>` : ''}
        </div>
    </section>`;
}

function _trkOpsRenderOpportunitiesPanel(d) {
    return `<section class="trk-ops-panel trk-ops-opportunities-panel">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-bold text-uppercase" style="color:#23304d;">
                <i class="fa-solid fa-road-circle-check me-1" style="color:var(--track-color);"></i>
                Créditos sobre la ruta
            </div>
            <span class="badge bg-label-primary" id="trkOpsOppCount">...</span>
        </div>
        <div class="trk-ops-opp-list" id="trkOpsOppList">
            <div class="text-center text-muted small py-3 w-100">
                <span class="spinner-border spinner-border-sm me-2"></span>Consultando candidatos cercanos...
            </div>
        </div>
    </section>`;
}

function _trkOpsRenderOpportunityCard(c) {
    const nivel = String(c.nivel || '').toLowerCase();
    const cliente = c.cliente || c.nombre_cliente || 'Sin cliente';
    const moto = c.moto || c.modelo || [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ') || 'Sin modelo';
    const estado = _trkEstadoMayus(c.estado, c.municipio);
    const municipio = _trkMunicipioMayus(c.municipio, c.estado);
    const distancia = _trkNumFmt(c.distancia_corredor_km, 1);
    const posicion = Number(c.posicion_sugerida || 0);
    return `<article class="trk-ops-opp-card">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="fw-bold" style="color:#23304d;">#${_trkChatEscapeHtml(c.id_credito || '-')}</div>
            ${_trkOportunidadNivelBadge(nivel)}
        </div>
        <div class="small fw-semibold mt-1 text-truncate" title="${_trkChatEscapeHtml(cliente)}">${_trkChatEscapeHtml(cliente)}</div>
        <div class="trk-ops-block-text"><i class="fa-solid fa-motorcycle me-1" style="color:var(--track-color);"></i>${_trkChatEscapeHtml(moto)}</div>
        <div class="mt-1">${_trkRenderLocationBadges(estado, municipio)}</div>
        <div class="trk-ops-block-text mt-1">
            ${distancia !== null ? `${_trkChatEscapeHtml(distancia)} km corredor` : 'Sin distancia'}
            ${posicion ? ` | Pos. ${_trkChatEscapeHtml(posicion)}` : ''}
        </div>
    </article>`;
}

function _trkOpsRenderOperativo(d) {
    const detalle = d?.detalle || [];
    const m = _trkOpsMetricas(d);
    const creditosConCoords = detalle.filter(_trkOpsCreditoPos).length;
    const infoTransportista = _trkTransportistaRutaData(d);

    return `<section class="trk-ops-panel trk-ops-operator-panel">
        <div class="trk-ops-operator-head">
            <div>
                <div class="trk-ops-operator-title"><i class="fa-solid fa-truck-fast me-1" style="color:var(--track-color);"></i>Datos del transportista</div>
                <div class="trk-ops-block-text">Unidad asignada, base y estado operativo de la ruta.</div>
            </div>
            <span class="badge bg-label-primary">Operacion</span>
        </div>

        <div class="trk-ops-mini-grid">
            <div class="trk-ops-mini-card"><span>Avance</span><b>${_trkChatEscapeHtml(m.pct)}%</b></div>
            <div class="trk-ops-mini-card"><span>Por recolectar</span><b>${_trkChatEscapeHtml(m.pendientes)}</b></div>
            <div class="trk-ops-mini-card"><span>Confirmados</span><b>${_trkChatEscapeHtml(m.confirmados)}</b></div>
            <div class="trk-ops-mini-card"><span>Con coordenadas</span><b>${_trkChatEscapeHtml(creditosConCoords)}/${_trkChatEscapeHtml(m.total)}</b></div>
        </div>

        <div class="trk-ops-block">
            <span class="trk-ops-block-label">Transportista</span>
            <div class="trk-ops-block-title mt-1">${_trkChatEscapeHtml(infoTransportista.nombre || 'Sin transportista')}</div>
            <div class="trk-ops-block-text mt-1">
                ${infoTransportista.tipo ? _trkTipoTransportistaBadge(infoTransportista.tipo) : ''}
                ${_trkChatEscapeHtml(infoTransportista.agencia || infoTransportista.direccion || 'Sin CEDIS base')}
            </div>
        </div>

        <div class="trk-ops-block trk-ops-live-status is-waiting" id="trkOpsSideLiveStatus">
            <span class="trk-ops-block-label">GPS transportista</span>
            <div class="trk-ops-block-title" id="trkOpsSideLiveUpdated">Esperando ubicación live</div>
            <div class="trk-ops-block-text mt-1" id="trkOpsSideLiveData">Vel. - | Prec. - | Bat. -</div>
            <div class="trk-ops-block-text" id="trkOpsSideLiveCoords">Coord. -</div>
        </div>

        <div class="trk-ops-action-grid">
            <button type="button" class="btn btn-sm btn-primary btn-ops-chat-ruta" data-id="${_trkChatEscapeHtml(d.id_ruta || '')}">
                <i class="fa-solid fa-comments me-1"></i>Chat
            </button>
            <button type="button" class="btn btn-sm btn-label-primary btn-ops-editar-ruta" data-id="${_trkChatEscapeHtml(d.id_ruta || '')}">
                <i class="fa-solid fa-pen-to-square me-1"></i>Editar
            </button>
        </div>
    </section>`;
}

function _trkOpsRenderHtml(d) {
    const m = _trkOpsMetricas(d);
    const estatusBadge = RUTA_LABEL[d.estatus_ruta] || `<span class="badge bg-secondary">${_trkChatEscapeHtml(d.estatus_ruta || 'Sin estatus')}</span>`;
    const cedis = _trkOpsCedisFromDetalle(d);
    if (cedis && !_trk.cedisDestinoPorRuta[d.id_ruta]) _trk.cedisDestinoPorRuta[d.id_ruta] = cedis;
    const cancelada = String(d.estatus_ruta || '') === 'cancelada';
    const fechaSalida = d.fecha_programada_fmt || d.fecha_programada || 'Sin fecha';
    const horaSalida = _trkOpsHoraRuta(d);

    return `<div class="trk-ops-shell">
        <section class="trk-ops-panel trk-ops-summary">
            <div class="trk-ops-route-fields">
                <div class="trk-ops-route-field">
                    <span>Nombre de ruta</span>
                    <b>#${_trkChatEscapeHtml(d.id_ruta || '')} ${_trkChatEscapeHtml(_trkSanitizarNombreRuta(d.nombre_ruta || 'Ruta sin nombre'))}</b>
                </div>
                <div class="trk-ops-route-field">
                    <span>Fecha programada</span>
                    <b>${_trkChatEscapeHtml(fechaSalida)}</b>
                </div>
                <div class="trk-ops-route-field">
                    <span>Hora de salida</span>
                    <b>${_trkChatEscapeHtml(horaSalida)}</b>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-2 mt-2">
                <div>${estatusBadge}</div>
                <span class="badge bg-light text-dark border">${_trkChatEscapeHtml(m.pct)}% avance</span>
            </div>
            ${cancelada ? `<div class="alert alert-danger py-2 px-3 small mt-3 mb-0">
                <div class="fw-semibold"><i class="fa-solid fa-ban me-1"></i>Ruta cancelada</div>
                <div>${_trkChatEscapeHtml(d.motivo_cancelacion || 'Sin motivo registrado')}</div>
            </div>` : ''}
        </section>

        <section class="trk-ops-panel trk-ops-planner-panel">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="trk-ops-operator-title"><i class="fa-solid fa-layer-group me-1" style="color:var(--track-color);"></i>Listado de créditos por estado</div>
                    <div class="trk-ops-block-text">Ubicaciones agrupadas para rutas con volumen alto.</div>
                </div>
                <span class="badge bg-label-primary">${_trkChatEscapeHtml(m.total)} créditos</span>
            </div>
            ${_trkOpsRenderPlannerGroups(d.detalle || [])}
        </section>

        ${_trkOpsRenderCedisPanel(d)}
        ${_trkOpsRenderOperativo(d)}

        <section class="trk-ops-panel trk-ops-map-panel">
            <div class="trk-ops-map-head">
                <div>
                    <div class="trk-ops-map-title"><i class="fa-solid fa-map-location-dot me-1" style="color:var(--track-color);"></i>Mapa operativo</div>
                    <div class="trk-ops-map-sub">Ruta, tráfico y última ubicación GPS del transportista.</div>
                </div>
                <span class="badge bg-label-success"><i class="fa-solid fa-satellite-dish me-1"></i>Live</span>
            </div>
            <div class="trk-ops-map-wrap">
                <div id="trkOperacionMap"></div>
                <div class="trk-ops-map-empty" id="trkOperacionMapEmpty">
                    <i class="fa-solid fa-map fa-2x opacity-50"></i>
                    <span class="fw-semibold">Preparando mapa operativo...</span>
                    <span class="small">Si no hay coordenadas, se mostrará solo la operación.</span>
                </div>
                <div class="trk-ops-live-card d-none" id="trkOpsLiveInfo">
                    <div class="live-main" id="trkOpsLiveUpdated">Sin ubicación live</div>
                    <div class="live-grid">
                        <span id="trkOpsLiveSpeed">Vel. -</span>
                        <span id="trkOpsLiveAccuracy">Prec. -</span>
                        <span id="trkOpsLiveBattery">Bat. -</span>
                        <span id="trkOpsLiveCoords">Coord. -</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="trk-ops-panel trk-ops-timeline-panel">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold text-uppercase" style="color:#23304d;">Timeline de recolección</div>
                <span class="badge bg-label-primary">${m.confirmados} confirmados</span>
            </div>
            ${_trkOpsRenderTimeline(d.detalle || [])}
        </section>

        ${_trkOpsRenderOpportunitiesPanel(d)}
    </div>`;
}

async function _trkAbrirOperacionesRuta(idRuta) {
    _trkCargarRutaEnModal(idRuta, false);
}

async function _trkOpsCargarOportunidadesRuta(idRuta) {
    const $list = $('#trkOpsOppList');
    const $count = $('#trkOpsOppCount');
    if (!idRuta || !$list.length) return;
    $count.text('...');
    $list.html(`<div class="text-center text-muted small py-3 w-100">
        <span class="spinner-border spinner-border-sm me-2"></span>Consultando candidatos cercanos...
    </div>`);
    const qs = new URLSearchParams({
        id_ruta: String(idRuta),
        radio_km: '10',
        limit: '12',
        usar_ubicacion_actual: 'true',
        incluir_detour: 'true',
        solo_con_coordenadas: 'true',
    });
    try {
        const r = await trkFetch(`/TrackingRecoleccion/trackingCreditosSobreRuta?${qs.toString()}`);
        if (!r.success) {
            $count.text('0');
            $list.html(`<div class="alert alert-warning small mb-0 w-100">
                ${_trkChatEscapeHtml(r.mensaje || r.message || r.detail || 'No se pudieron cargar créditos sobre la ruta.')}
            </div>`);
            return;
        }
        const data = _trkOportunidadesNormalizarRespuesta(r);
        const idsEnRuta = new Set((_trk.opsRutaActualData?.detalle || []).map(x => String(x.id_credito)));
        const candidatos = (data.candidatos || []).filter(c => !idsEnRuta.has(String(c.id_credito))).slice(0, 12);
        $count.text(candidatos.length);
        if (!candidatos.length) {
            $list.html('<div class="text-center text-muted small py-3 w-100">Sin candidatos cercanos con los filtros actuales.</div>');
            return;
        }
        $list.html(candidatos.map(_trkOpsRenderOpportunityCard).join(''));
    } catch {
        $count.text('0');
        $list.html('<div class="alert alert-warning small mb-0 w-100">Error de conexión al consultar candidatos.</div>');
    }
}

function _trkOpsMostrarEdicionRuta(idRuta) {
    const shell = document.querySelector('#trkOperacionBody .trk-ops-shell');
    const d = _trk.opsRutaActualData || {};
    if (!shell) {
        _trkCargarRutaEnModal(idRuta, false);
        return;
    }
    _trkOpsLimpiarMapa();
    shell.classList.add('is-edit-mode');
    shell.querySelector('.trk-ops-edit-workspace')?.remove();
    const infoTransportista = _trkTransportistaRutaData(d);
    const creditos = d.detalle || [];
    const cedis = _trkOpsCedisFromDetalle(d);
    shell.insertAdjacentHTML('beforeend', `<section class="trk-ops-panel trk-ops-edit-workspace">
        <div class="trk-ops-map-head">
            <div>
                <div class="trk-ops-map-title">
                    <i class="fa-solid fa-pen-to-square me-1" style="color:var(--track-color);"></i>
                    Edicion operativa
                </div>
                <div class="trk-ops-map-sub">Cambios controlados sin reenviar la ruta ni modificar su estatus.</div>
            </div>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-label-secondary btn-ops-volver-planeador" data-id="${_trkChatEscapeHtml(idRuta)}">
                    <i class="fa-solid fa-arrow-left me-1"></i>Planeador
                </button>
            </div>
        </div>
        <div class="p-3" style="overflow:auto;min-height:0;">
            <div class="row g-3">
                <div class="col-12 col-xl-4">
                    <div class="trk-ops-block h-100">
                        <span class="trk-ops-block-label">Ruta</span>
                        <div class="trk-ops-block-title mt-1">${_trkChatEscapeHtml(_trkSanitizarNombreRuta(d.nombre_ruta || 'Ruta sin nombre'))}</div>
                        <div class="trk-ops-block-text mt-2"><b>Fecha:</b> ${_trkChatEscapeHtml(d.fecha_programada_fmt || d.fecha_programada || 'Sin fecha')}</div>
                        <div class="trk-ops-block-text"><b>Hora de salida:</b> ${_trkChatEscapeHtml(_trkOpsHoraRuta(d))}</div>
                        <div class="trk-ops-block-text"><b>Estatus:</b> ${_trkChatEscapeHtml(d.estatus_ruta || 'Sin estatus')}</div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="trk-ops-block h-100">
                        <span class="trk-ops-block-label">Transportista</span>
                        <div class="trk-ops-block-title mt-1">${_trkChatEscapeHtml(infoTransportista.nombre || 'Sin transportista')}</div>
                        <div class="trk-ops-block-text mt-2">${infoTransportista.tipo ? _trkTipoTransportistaBadge(infoTransportista.tipo) : ''}</div>
                        <div class="trk-ops-block-text mt-1">${_trkChatEscapeHtml(infoTransportista.agencia || infoTransportista.direccion || 'Sin CEDIS base')}</div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="trk-ops-block h-100">
                        <span class="trk-ops-block-label">CEDIS destino</span>
                        <div class="trk-ops-block-title mt-1">${_trkChatEscapeHtml(cedis?.nombre_agencia || 'Sin destino asignado')}</div>
                        <div class="trk-ops-block-text mt-2">${_trkChatEscapeHtml([cedis?.municipio, cedis?.estado].filter(Boolean).join(' / ') || 'Sin ubicación')}</div>
                        <button type="button" class="btn btn-sm btn-label-info mt-2 btn-cambiar-cedis-destino" data-id="${_trkChatEscapeHtml(idRuta)}">
                            <i class="fa-solid fa-warehouse me-1"></i>Cambiar CEDIS
                        </button>
                    </div>
                </div>
            </div>
            <div class="trk-ops-block mt-3">
                <span class="trk-ops-block-label">Créditos en ruta</span>
                <div class="trk-ops-opp-list mt-2" style="height:auto;max-height:300px;">
                    ${creditos.length ? creditos.map(det => `<article class="trk-ops-opp-card">
                        <div class="fw-bold" style="color:#23304d;">#${_trkChatEscapeHtml(det.id_credito || '-')}</div>
                        <div class="small fw-semibold mt-1">${_trkChatEscapeHtml(det.nombre_cliente || 'Sin cliente')}</div>
                        <div class="mt-1">${_trkRenderLocationBadges(det.estado, det.municipio)}</div>
                        <div class="trk-ops-block-text mt-1">${_trkRenderEstatusRecoleccionDetalle(det.estatus_recoleccion)}</div>
                    </article>`).join('') : '<div class="text-muted small">Sin créditos registrados.</div>'}
                </div>
            </div>
        </div>
    </section>`);
}

function _trkOpsRenderMapa(d) {
    const empty = document.getElementById('trkOperacionMapEmpty');
    const mapDiv = document.getElementById('trkOperacionMap');
    if (!mapDiv) return;
    if (!window._trackGoogleMapsKey) {
        if (empty) {
            empty.innerHTML = '<i class="fa-solid fa-triangle-exclamation fa-2x opacity-50"></i><span class="fw-semibold">Google Maps no disponible</span><span class="small">Falta API key.</span>';
            empty.classList.remove('d-none');
        }
        return;
    }
    _trkAsegurarGoogleMaps(() => {
        _trkOpsLimpiarMapa();
        const map = _trk.opsMapInstance || new google.maps.Map(mapDiv, {
            center: { lat: 19.4326, lng: -99.1332 },
            zoom: 6,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            styles: document.body.classList.contains('dark-mode') ? _TRK_DARK_MAP_STYLES : [],
        });
        _trk.opsMapInstance = map;
        _trkActivarTraficoMapa(map, 'opsTrafficLayer');
        google.maps.event.trigger(map, 'resize');

        const bounds = new google.maps.LatLngBounds();
        const path = [];
        (d.detalle || [])
            .slice()
            .sort((a, b) => (a.orden_ruta || 99) - (b.orden_ruta || 99))
            .forEach((det, idx) => {
                const pos = _trkOpsCreditoPos(det);
                if (!pos) return;
                const n = det.orden_ruta || (idx + 1);
                const est = String(det.estatus_confirmacion_gestor || 'pendiente');
                const color = est === 'confirmado' ? '#e53935' : (est === 'pendiente' ? '#fdd835' : '#fb8c00');
                const text = est === 'pendiente' ? '#111827' : '#fff';
                const marker = new google.maps.Marker({
                    map,
                    position: pos,
                    icon: _trkOpsMarkerIcon(n, color, text),
                    title: `#${det.id_credito || '-'} - ${det.nombre_cliente || ''}`,
                    zIndex: 20 + n,
                });
                _trk.opsMapMarkers.push(marker);
                path.push(pos);
                bounds.extend(pos);
            });

        const cedis = _trkOpsCedisFromDetalle(d);
        const cedisPos = _trkCedisDestinoPosicion(cedis);
        if (cedisPos) {
            const marker = new google.maps.Marker({
                map,
                position: cedisPos,
                icon: _trkOpsCedisIcon(),
                title: `${cedis?.nombre_agencia || 'CEDIS destino'} - destino`,
                zIndex: 90,
            });
            _trk.opsMapMarkers.push(marker);
            path.push(cedisPos);
            bounds.extend(cedisPos);
        }

        if (path.length > 1) {
            _trk.opsMapPolyline = new google.maps.Polyline({
                map,
                path,
                strokeColor: '#0d9488',
                strokeOpacity: 0.88,
                strokeWeight: 4,
                zIndex: 15,
            });
        }
        if (!bounds.isEmpty()) {
            map.fitBounds(bounds);
            empty?.classList.add('d-none');
        } else if (empty) {
            empty.innerHTML = '<i class="fa-solid fa-location-dot fa-2x opacity-50"></i><span class="fw-semibold">Sin coordenadas para pintar ruta</span><span class="small">Completa ubicaciones de créditos o CEDIS.</span>';
            empty.classList.remove('d-none');
        }
        _trkOpsCargarLiveMapa(d.id_ruta || _trk.opsRutaActualId);
    });
}

async function _trkOpsCargarLiveMapa(idRuta) {
    if (!idRuta || !_trk.opsMapInstance || typeof google === 'undefined' || !google.maps) return;
    const empty = document.getElementById('trkOperacionMapEmpty');
    try {
        const [hist, actual] = await Promise.all([
            trkFetch(`/TrackingRecoleccion/trackingUbicacionHistorial?id_ruta=${encodeURIComponent(idRuta)}&limit=300`),
            trkFetch(`/TrackingRecoleccion/trackingUbicacionActual?id_ruta=${encodeURIComponent(idRuta)}`),
        ]);
        const puntos = Array.isArray(hist.ubicaciones)
            ? hist.ubicaciones.map(_trkRTNormalizarUbicacion).filter(Boolean)
            : [];
        const actualUbi = actual.success && actual.ubicacion ? _trkRTNormalizarUbicacion(actual.ubicacion) : (puntos[puntos.length - 1] || null);
        if (puntos.length) {
            _trk.opsLivePolyline = new google.maps.Polyline({
                map: _trk.opsMapInstance,
                path: puntos.map(p => ({ lat: p.lat, lng: p.lng })),
                strokeColor: '#2563eb',
                strokeOpacity: .9,
                strokeWeight: 4,
                zIndex: 30,
            });
            empty?.classList.add('d-none');
        }
        if (actualUbi) {
            const pos = { lat: actualUbi.lat, lng: actualUbi.lng };
            _trk.opsLiveMarker = new google.maps.Marker({
                map: _trk.opsMapInstance,
                position: pos,
                icon: _trkRTIconoVehiculo(Number.isFinite(actualUbi.heading) ? actualUbi.heading : 0),
                title: 'Transportista en vivo',
                zIndex: 1000,
            });
            _trk.opsMapInstance.panTo(pos);
            _trk.opsMapInstance.setZoom(Math.max(_trk.opsMapInstance.getZoom() || 0, 12));
            google.maps.event.trigger(_trk.opsMapInstance, 'resize');
            empty?.classList.add('d-none');
            _trkOpsActualizarLiveInfo(actualUbi);
        }
    } catch {}
}

function _trkOpsActualizarLiveInfo(ubi) {
    if (!ubi) return;
    const card = document.getElementById('trkOpsLiveInfo');
    if (!card) return;
    const ts = ubi.updated_at ? new Date(String(ubi.updated_at).endsWith('Z') ? ubi.updated_at : ubi.updated_at + 'Z') : new Date();
    const timeTxt = isNaN(ts.getTime()) ? 'Sin fecha' : ts.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.getElementById('trkOpsLiveUpdated').textContent = `Última ubicación ${timeTxt}`;
    document.getElementById('trkOpsLiveSpeed').textContent = ubi.speed !== null && !isNaN(ubi.speed) ? `Vel. ${Math.round(ubi.speed)} km/h` : 'Vel. -';
    document.getElementById('trkOpsLiveAccuracy').textContent = ubi.accuracy !== null && !isNaN(ubi.accuracy) ? `Prec. ${Math.round(ubi.accuracy)} m` : 'Prec. -';
    document.getElementById('trkOpsLiveBattery').textContent = ubi.battery !== null && !isNaN(ubi.battery) ? `Bat. ${ubi.battery}%` : 'Bat. -';
    document.getElementById('trkOpsLiveCoords').textContent = `Coord. ${ubi.lat.toFixed(5)}, ${ubi.lng.toFixed(5)}`;
    card.classList.remove('d-none');

    const sideStatus = document.getElementById('trkOpsSideLiveStatus');
    if (sideStatus) {
        sideStatus.classList.remove('is-waiting');
        document.getElementById('trkOpsSideLiveUpdated').textContent = `Última ubicación ${timeTxt}`;
        document.getElementById('trkOpsSideLiveData').textContent = [
            ubi.speed !== null && !isNaN(ubi.speed) ? `Vel. ${Math.round(ubi.speed)} km/h` : 'Vel. -',
            ubi.accuracy !== null && !isNaN(ubi.accuracy) ? `Prec. ${Math.round(ubi.accuracy)} m` : 'Prec. -',
            ubi.battery !== null && !isNaN(ubi.battery) ? `Bat. ${ubi.battery}%` : 'Bat. -',
        ].join(' | ');
        document.getElementById('trkOpsSideLiveCoords').textContent = `Coord. ${ubi.lat.toFixed(5)}, ${ubi.lng.toFixed(5)}`;
    }
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
                                <th>Proceso</th><th>Recoleccion</th><th>Confirmacion</th><th>OTP MotoTrack</th>
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
    $('#rutaNombre, #rutaFecha, #rutaFechaFin, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm, #rutaTipoTransportista, #rutaAgenciaTracking, #rutaTransportistaTracking, #rutaTransportistaSearch, #rutaCedisDestino, #crdFiltroEstado, #crdFiltroMunicipio, #rutaCreditoSelect, #btnAgregarCredito, #btnAgregarTodosUbicacion')
        .prop('disabled', true);
    $('#secAgregarCredito, #reorderHint, #btnActualizarRuta, #btnGuardarBorrador, #btnEnviarRuta, #btnRefreshMap').hide();
    if (_trk.sortableInstance) _trk.sortableInstance.option('disabled', true);
    const $label = $('#modalRegistrarRutaLabel');
    if (!$label.find('.badge-ruta-cancelada-modal').length) {
        $label.append('<span class="badge bg-danger badge-ruta-cancelada-modal ms-2" style="font-size:.63rem;vertical-align:middle;">Cancelada</span>');
    }
}

function _trkBloquearModal(etiqueta = 'Ver ruta') {
    _trk.soloLectura = true;
    // Solo bloquear campos de cabecera de la ruta
    $('#rutaNombre, #rutaFecha, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm, #rutaTipoTransportista, #rutaAgenciaTracking, #rutaTransportistaTracking, #rutaTransportistaSearch, #rutaCedisDestino').prop('disabled', true);
    $('#rutaFechaFin').prop('disabled', false);
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
        $label.append(`<span class="badge bg-secondary badge-solo-lectura ms-2" style="font-size:.63rem;vertical-align:middle;">${_trkChatEscapeHtml(etiqueta)}</span>`);
    }
}

function _trkDesbloquearModal() {
    _trk.soloLectura = false;
    _trk.rutaCancelada = false;
    $('#rutaNombre, #rutaFecha, #rutaFechaFin, #rutaHoraH, #rutaHoraM, #rutaHoraAmPm, #rutaTipoTransportista, #rutaAgenciaTracking, #rutaTransportistaTracking, #rutaTransportistaSearch, #rutaCedisDestino, #crdFiltroEstado, #btnAgregarCredito, #btnAgregarTodosUbicacion').prop('disabled', false);
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

function _trkNormalizarDetalleRutaRespuesta(resp, idRuta) {
    const datos = resp?.datos ?? resp?.data ?? null;
    if (Array.isArray(datos)) {
        return datos.find(r => String(r?.id_ruta || r?.id || '') === String(idRuta)) || datos[0] || null;
    }
    return datos;
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
        html: '<span style="font-size:.875rem;color:#64748b;">Consultando datos, créditos y mapa de la ruta</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    Promise.all([
        _trkPrepararModalRutaDetalle(soloLectura).catch(err => {
            console.warn('[Tracking Recoleccion] Preparacion parcial del modal fallida', err);
            return null;
        }),
        trkFetch(`/TrackingRecoleccion/obtenerDetalleRuta?id_ruta=${idRuta}`),
    ])
        .then(([, r]) => {
            const d = _trkNormalizarDetalleRutaRespuesta(r, idRuta);
            if (!r.success || !d) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la ruta.', confirmButtonText: 'Aceptar' });
                modal.hide();
                return;
            }
            if (!Array.isArray(d.detalle)) d.detalle = [];

            // Titulo final con nombre de la ruta
            const nombreRutaLimpio = _trkSanitizarNombreRuta(d.nombre_ruta || '');
            document.getElementById('modalRegistrarRutaLabel').innerHTML =
                `<i class="fa-solid fa-${icon} me-2"></i>${soloLectura ? 'Ver ruta' : 'Editar ruta'}: <em>${nombreRutaLimpio}</em>`;

            // Campos basicos
            $('#rutaNombre').val(nombreRutaLimpio);
            $('#rutaFecha').val(d.fecha_programada || '');
            $('#rutaFechaFin').val(d.fecha_finalizacion || '');
            _trkSincronizarFechaFinalizacion(false);
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
                id_detalle:                  det.id_detalle || det.detalle_id || det.id_ruta_detalle || det.id_detalle_ruta || det.id_asigna_detalle || det.id_asigna_hora_detalle || det.id_asigna_horas_tracking_detalle || det.id_tracking_detalle || det.id_punto || ((det.id && String(det.id) !== String(det.id_credito)) ? det.id : null) || null,
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
                fecha_planeacion:            det.fecha_planeacion || det.fecha_recoleccion || null,
                fecha_planeacion_fmt:        det.fecha_planeacion_fmt || det.fecha_recoleccion_fmt || '',
                orden_dia:                   det.orden_dia || null,
                estatus_planeacion:          det.estatus_planeacion || 'programado',
                day_index:                   Number(det.day_index || 0),
                arrival_minutes:             det.arrival_minutes !== null && det.arrival_minutes !== undefined ? Number(det.arrival_minutes) : null,
                departure_minutes:           det.departure_minutes !== null && det.departure_minutes !== undefined ? Number(det.departure_minutes) : null,
                travel_from_prev_minutes:    det.travel_from_prev_minutes !== null && det.travel_from_prev_minutes !== undefined ? Number(det.travel_from_prev_minutes) : null,
                operation_minutes:           det.operation_minutes !== null && det.operation_minutes !== undefined ? Number(det.operation_minutes) : null,
                pinned:                      Number(det.pinned || 0),
                edited:                      Number(det.edited || 0),
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
            _trkCargarPlaneacionRuta(idRuta);

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
            const esBorradorCargado = String(d.estatus_ruta || '') === 'borrador';
            const modoActualizacionOperativa = !soloLectura && !esBorradorCargado;
            if (!_trkRutaEstaCancelada() && modoActualizacionOperativa) {
                _trkBloquearModal('Actualizacion operativa');
            }
            const esActiva = _trkRutaDebeConsultarEstadoLive(d.estatus_ruta) || ['completado', 'concluida'].includes(String(d.estatus_ruta || ''));
            if (esActiva) {
                _trkRTIniciar(idRuta);
            } else {
                _trkRTLimpiar();
            }
            _trkSetPlaneadorActivo(true);
            _trkMiniChatIniciar(idRuta, nombreRutaLimpio || `Ruta #${idRuta}`);
            Swal.close();
        })
        .catch((err) => {
            _trk.cargando = false;
            console.error('[Tracking Recoleccion] Error al abrir ruta en modal', err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonText: 'Aceptar' });
            modal.hide();
        });
}

function _trkCreditosRutaFiltrados() {
    const estado = _trkNormTxt($('#trkListaFiltroEstado').val());
    const municipio = _trkNormTxt($('#trkListaFiltroMunicipio').val());
    const direccion = String($('#trkListaFiltroDireccion').val() || '');
    const q = _trkNormTxt($('#trkListaBuscar').val());
    return (_trk.creditosEnRuta || []).filter(c => {
        if (estado && _trkNormTxt(_trkEstadoMayus(c.estado, c.municipio)) !== estado) return false;
        if (municipio && _trkNormTxt(_trkMunicipioMayus(c.municipio, c.estado)) !== municipio) return false;
        if (direccion === 'sin_direccion' && !_trkCreditoSinDireccionRuta(c)) return false;
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

function _trkCreditoSinDireccionRuta(c) {
    return !_trkDireccionCreditoUtil(c?.direccion);
}

function _trkListaRutaTieneFiltroActivo() {
    return Boolean(
        $('#trkListaFiltroEstado').val()
        || $('#trkListaFiltroMunicipio').val()
        || $('#trkListaFiltroDireccion').val()
        || String($('#trkListaBuscar').val() || '').trim()
    );
}

function _trkActualizarAccionesListaRuta() {
    const creditosVisibles = _trkCreditosRutaFiltrados();
    const sinDireccionActivo = String($('#trkListaFiltroDireccion').val() || '') === 'sin_direccion';
    const puedeEditar = !_trkRutaEstaCancelada() && !_trk.soloLectura;
    const sinDireccionVisibles = creditosVisibles.filter(_trkCreditoSinDireccionRuta);
    $('#btnEliminarCreditosSinDireccion')
        .prop('disabled', !puedeEditar || !sinDireccionActivo || sinDireccionVisibles.length === 0)
        .attr('title', sinDireccionActivo
            ? 'Elimina los creditos visibles sin direccion'
            : 'Activa el filtro Sin direccion para eliminar creditos');
    $('#btnConfirmarCreditosFiltrados')
        .prop('disabled', !puedeEditar || creditosVisibles.length === 0)
        .attr('title', _trkListaRutaTieneFiltroActivo()
            ? 'Confirma todos los creditos visibles por los filtros aplicados'
            : 'Confirma todos los creditos de la ruta');
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
    if (!modal) return;
    _trkSetPlaneadorActivo(!modal.classList.contains('trk-planner-active'));
}

function _trkSetPlaneadorActivo(active = true) {
    const modal = document.getElementById('modalRegistrarRuta');
    if (!modal) return;
    modal.classList.toggle('trk-planner-active', active);
    $('#trkPlannerPanel').toggleClass('d-none', !active);
    if (!active) {
        $('#trkPlannerCedisBox').addClass('d-none').empty();
    }
    $('#btnTogglePlanner').toggleClass('btn-primary', active).toggleClass('btn-outline-primary', !active)
        .html(active
            ? '<i class="fa-solid fa-down-left-and-up-right-to-center me-1"></i>Vista simplificada'
            : '<i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i>Planeador');
    _trkRenderMapTransportSummary();
    _trkRenderPlaneadorPanel();
    if (active) {
        _trkRenderCedisDestinoInfo();
        _trkCargarOportunidadesRuta(false);
    }
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
    _trkSyncTrasladoEstadosControl();
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
        <div class="trk-planner-kpi"><span>Créditos</span><b>${creditos.length}</b></div>
        <div class="trk-planner-kpi"><span>Estados</span><b>${estados.size}</b></div>
        <div class="trk-planner-kpi"><span>Municipios</span><b>${municipios.size}</b></div>
    `);
    if (!creditos.length) {
        $groups.html('<div class="text-center text-muted small py-3">Agrega créditos para ver la planeación por volumen.</div>');
        _trkRenderOportunidadesRuta();
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
            Ruta pesada: ${creditos.length} créditos en ${estados.size} estado(s). Revisa agrupación antes de enviar.
        </div>`
        : '';
    $groups.html(alert + estadoHtml);
    _trkRenderPlaneacionDias();
    _trkRenderOportunidadesRuta();
}

function _trkFechaCorta(fecha) {
    if (!fecha) return 'Sin fecha';
    const parts = String(fecha).split('-');
    if (parts.length !== 3) return fecha;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

function _trkPlaneacionFechaCredito(c) {
    return c?.fecha_planeacion || c?.fecha_recoleccion || c?.fecha_eta || $('#rutaFecha').val() || _trkFechaMinimaProgramacion();
}

function _trkPlanTimeToMinutes(value, fallback = 600) {
    const n = _trkHoraToMinutes(String(value || ''));
    return Number.isFinite(n) ? n : fallback;
}

function _trkPlanMinutesLabel(minutes) {
    return _trkFormatHora(_trkMinutesToHora(Number(minutes || 0)));
}

function _trkPlanDurationLabel(minutes) {
    const n = Number(minutes);
    if (!Number.isFinite(n) || n < 0) return 'Sin calculo real';
    const total = Math.max(0, Math.round(n));
    const h = Math.floor(total / 60);
    const m = total % 60;
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
}

function _trkPlanCedisInicioHtml(firstItem) {
    const cedis = _trkCedisDestinoSeleccionado();
    const nombreCedis = cedis?.nombre_agencia || cedis?.clave_agencia || 'CEDIS destino no asignado';
    const salidaCedis = _trkFormatHora(_trkHoraToPayload());
    const travelPending = !!firstItem?._plan_travel_pending;
    const trayecto = travelPending ? 'Sin calculo real' : _trkPlanDurationLabel(firstItem?.travel_from_prev_minutes);
    return `<div class="trk-route-day-start">
        <span class="trk-route-day-start-icon"><i class="fa-solid fa-warehouse"></i></span>
        <div style="min-width:0;">
            <div class="trk-route-day-start-label">Comienzo de ruta</div>
            <div class="trk-route-day-start-title">CEDIS ${_trkChatEscapeHtml(nombreCedis)}</div>
            <div class="trk-route-day-timebar">
                <span class="trk-route-day-timechip">
                    <i class="fa-solid fa-right-from-bracket"></i>Salida de CEDIS: ${_trkChatEscapeHtml(salidaCedis)}
                </span>
                <span class="trk-route-day-timechip${travelPending ? ' is-warning' : ''}">
                    <i class="fa-solid fa-route"></i>Tiempo de trayecto al primer punto: ${_trkChatEscapeHtml(trayecto)}
                </span>
            </div>
        </div>
    </div>`;
}

function _trkPlanClamp(value, min, max, fallback) {
    const n = Number(value);
    if (!Number.isFinite(n)) return fallback;
    return Math.max(min, Math.min(max, Math.round(n)));
}

function _trkSanitizarPlanMinParadaInput(input, force = false) {
    if (!input) return 45;
    let raw = String(input.value || '').replace(/[^\d]/g, '').slice(0, 2);
    if (raw !== input.value) input.value = raw;
    if (!raw && !force) return null;
    let n = Number(raw || 45);
    if (!Number.isFinite(n)) n = 45;
    n = Math.max(1, Math.min(59, Math.round(n)));
    if (force || String(n) !== raw) input.value = String(n);
    return n;
}

function _trkPlanEstadosUnicos(source = null) {
    const lista = Array.isArray(source) ? source : _trkPlanCreditosCalculables(_trk.creditosEnRuta || []);
    const estados = new Set();
    lista.forEach(c => {
        const estado = _trkEstadoMayus(c?.estado, c?.municipio) || '';
        const norm = _trkNormTxt(estado);
        if (!norm || norm.startsWith('SIN ESTADO')) return;
        if (!_TRK_ESTADOS_OFICIALES.includes(norm)) return;
        estados.add(norm);
    });
    return estados;
}

function _trkPlanRequiereTrasladoEstados(source = null) {
    return _trkPlanEstadosUnicos(source).size >= 2;
}

function _trkSyncTrasladoEstadosControl(source = null) {
    const base = Array.isArray(source) ? source : _trkPlanCreditosCalculables(_trk.creditosEnRuta || []);
    const requiere = _trkPlanRequiereTrasladoEstados(base);
    const $wrap = $('#trkPlanTrasladoEstadosWrap');
    const $input = $('#trkPlanDiasTrasladoEstado');
    if (!$wrap.length || !$input.length) return requiere;
    $wrap.toggleClass('d-none', !requiere);
    $wrap.css('display', requiere ? '' : 'none');
    $input.prop('disabled', !requiere);
    if (!requiere) {
        $input.val('0');
        _trk.planeacionTrasladoEstados = 0;
    } else if (!$input.val()) {
        $input.val(Math.min(TRK_PLAN_MAX_GAP_DAYS, Number(_trk.planeacionTrasladoEstados || 1)));
    }
    return requiere;
}

function _trkPlanHasNumber(value) {
    return value !== null && value !== undefined && value !== '' && Number.isFinite(Number(value));
}

function _trkPlanOptionalNumber(value) {
    return _trkPlanHasNumber(value) ? Number(value) : null;
}

function _trkPlanConfig() {
    let inicio = _trkPlanTimeToMinutes($('#trkPlanInicioJornada').val(), 600);
    let fin = _trkPlanTimeToMinutes($('#trkPlanFinJornada').val(), 1140);
    if (fin <= inicio) fin = inicio + 60;
    const requiereTrasladoEstados = _trkSyncTrasladoEstadosControl();
    const operacion = _trkSanitizarPlanMinParadaInput(document.getElementById('trkPlanMinParada'), true) || 45;
    return {
        inicio,
        fin,
        operacion,
        trasladoEstados: requiereTrasladoEstados
            ? _trkPlanClamp($('#trkPlanDiasTrasladoEstado').val(), 0, TRK_PLAN_MAX_GAP_DAYS, 1)
            : 0,
    };
}

function _trkPlanCreditoNombre(c) {
    return [
        c?.id_credito || '',
        c?.nombre_cliente || '',
        c?.direccion || '',
        _trkMunicipioMayus(c?.municipio, c?.estado) || '',
        _trkEstadoMayus(c?.estado, c?.municipio) || '',
    ].filter(Boolean).join(' | ') || 'PUNTO';
}

function _trkPlanTravel(from, to) {
    return 0;
}

function _trkPlanEstado(c) {
    return _trkEstadoMayus(c?.estado, c?.municipio) || 'SIN ESTADO';
}

function _trkPlanMunicipio(c) {
    return _trkMunicipioMayus(c?.municipio, c?.estado) || 'SIN MUNICIPIO';
}

function _trkPlanSortCreditos(creditos) {
    return [...creditos].sort((a, b) => {
        const pa = Number(a.pinned || 0);
        const pb = Number(b.pinned || 0);
        const fa = _trkPlaneacionFechaCredito(a);
        const fb = _trkPlaneacionFechaCredito(b);
        if (pa && pb && fa !== fb) return _trkCompararFecha(fa, fb);
        const ea = _trkPlanEstado(a);
        const eb = _trkPlanEstado(b);
        if (ea !== eb) return ea.localeCompare(eb);
        const ma = _trkPlanMunicipio(a);
        const mb = _trkPlanMunicipio(b);
        if (ma !== mb) return ma.localeCompare(mb);
        return Number(a.orden_ruta || 0) - Number(b.orden_ruta || 0);
    });
}

function _trkPlanCreditoKey(c) {
    const detalle = String(c?.id_detalle || '').trim();
    if (detalle) return `d:${detalle}`;
    return `c:${String(c?.id_credito || '').trim()}`;
}

function _trkPlanCreditoCalculable(c) {
    return Boolean(c && _trkCreditoTieneCoordenadasValidas(c));
}

function _trkPlanCreditosCalculables(source = null) {
    const lista = Array.isArray(source) ? source : (_trk.creditosEnRuta || []);
    return lista.filter(_trkPlanCreditoCalculable);
}

function _trkPlanCreditosRemanentes(source = null) {
    const lista = Array.isArray(source) ? source : (_trk.creditosEnRuta || []);
    return lista.filter(c => !_trkPlanCreditoCalculable(c));
}

function _trkPlanFechaFinalRuta() {
    const base = _trkPlanFechaBaseRuta();
    const fin = $('#rutaFechaFin').val() || base;
    return _trkCompararFecha(fin, base) >= 0 ? fin : base;
}

function _trkPlanFechaMaxManual() {
    return _trkFechaSumarDias(_trkPlanFechaFinalRuta(), TRK_PLAN_MAX_GAP_DAYS);
}

function _trkPlanFechasRangoRuta() {
    const inicio = _trkPlanFechaBaseRuta();
    const fin = _trkPlanFechaFinalRuta();
    const fechas = [];
    let fecha = inicio;
    let guard = 0;
    while (fecha && _trkCompararFecha(fecha, fin) <= 0 && guard < 120) {
        fechas.push(fecha);
        fecha = _trkFechaSumarDias(fecha, 1);
        guard++;
    }
    return fechas.length ? fechas : [inicio];
}

function _trkPlanSeedDays(creditos, config) {
    const fechasRango = _trkPlanFechasRangoRuta();
    const days = [];
    const sorted = _trkPlanSortCreditos(_trkPlanCreditosCalculables(creditos));
    const libres = sorted.filter(c => !(Number(c.pinned || 0) === 1 && c.fecha_planeacion));
    sorted.forEach(c => {
        let fecha = c.fecha_planeacion || fechasRango[0];
        if (!(Number(c.pinned || 0) === 1 && c.fecha_planeacion)) {
            const idxLibre = libres.indexOf(c);
            const idxFecha = libres.length > 1
                ? Math.min(fechasRango.length - 1, Math.floor(idxLibre * fechasRango.length / libres.length))
                : 0;
            fecha = fechasRango[idxFecha] || fechasRango[0];
        }
        let day = days.find(d => d.date === fecha);
        if (!day) {
            day = { date: fecha, stops: [] };
            days.push(day);
        }
        day.stops.push(c);
    });
    return days.sort((a, b) => _trkCompararFecha(a.date, b.date));
}

function _trkPlanApplyCascade(days, config) {
    let guard = 0;
    for (let d = 0; d < days.length && guard < 120; d++, guard++) {
        const day = days[d];
        day.index = d;
        if (!day.date) day.date = _trkFechaSumarDias($('#rutaFecha').val() || _trkFechaMinimaProgramacion(), d);
        let cursor = config.inicio;
        let prev = null;
        const keep = [];
        const overflow = [];
        day.stops.forEach(c => {
            const travelSource = String(c.travel_source || c.source || '').toLowerCase();
            const hasRealTravel = !!(c._plan_real || ['google_routes', 'google_maps', 'google_maps_route', 'maps_directions'].includes(travelSource))
                && _trkPlanHasNumber(c.travel_from_prev_minutes);
            const travel = hasRealTravel ? Number(c.travel_from_prev_minutes) : 0;
            c._plan_travel_pending = !hasRealTravel;
            const hasPinnedTime = Number(c.pinned || 0) === 1
                && _trkPlanHasNumber(c.arrival_minutes)
                && _trkPlanHasNumber(c.departure_minutes);
            const opBase = hasPinnedTime
                ? _trkPlanClamp(c.operation_minutes, 1, 59, config.operacion)
                : config.operacion;
            let llegada;
            let salida;
            if (hasPinnedTime) {
                llegada = Number(c.arrival_minutes);
                salida = Number(c.departure_minutes);
                if (salida <= llegada) salida = llegada + opBase;
            } else {
                llegada = Math.max(config.inicio, cursor + travel);
                salida = llegada + opBase;
            }
            const rebasa = salida > config.fin;
            if (rebasa && keep.length > 0 && !hasPinnedTime) {
                c._plan_overflow = true;
                overflow.push(c);
                return;
            }
            c.day_index = d;
            c.fecha_planeacion = day.date;
            c.orden_dia = keep.length + 1;
            c.arrival_minutes = llegada;
            c.departure_minutes = salida;
            c.travel_from_prev_minutes = travel;
            c.operation_minutes = Math.max(1, salida - llegada);
            c.estatus_planeacion = c.estatus_planeacion || 'programado';
            c._plan_warning = salida > config.fin;
            keep.push(c);
            cursor = salida;
            prev = c;
        });
        day.stops = keep;
        if (overflow.length) {
            const nextDate = _trkFechaSumarDias(day.date, 1);
            if (!days[d + 1]) {
                days.splice(d + 1, 0, { date: nextDate, stops: overflow });
            } else {
                days[d + 1].date = days[d + 1].date || nextDate;
                days[d + 1].stops = overflow.concat(days[d + 1].stops || []);
            }
        }
    }
    return days.filter(d => (d.stops || []).length);
}

function _trkPlanApplyDaysToRoute(days, opts = {}) {
    const ordered = [];
    const plannedKeys = new Set();
    days.forEach((day, dayIndex) => {
        (day.stops || []).forEach((c, stopIndex) => {
            c.day_index = dayIndex;
            c.fecha_planeacion = day.date;
            c.orden_dia = stopIndex + 1;
            plannedKeys.add(_trkPlanCreditoKey(c));
            ordered.push(c);
        });
    });
    const preserveRemanentes = opts.preserveRemanentes !== false;
    const remanentes = preserveRemanentes
        ? (_trk.creditosEnRuta || []).filter(c => !plannedKeys.has(_trkPlanCreditoKey(c)))
        : [];
    const finalList = ordered.concat(remanentes);
    finalList.forEach((c, i) => { c.orden_ruta = i + 1; });
    _trk.creditosEnRuta = finalList;
}

function _trkPlanBuildDaysFromCurrent(source = null) {
    const buckets = new Map();
    _trkPlanCreditosCalculables(source || _trk.creditosEnRuta || []).forEach((c, idx) => {
        const dayIndex = Number.isFinite(Number(c.day_index)) ? Number(c.day_index) : 0;
        const fecha = _trkPlaneacionFechaCredito(c);
        const key = `${String(dayIndex).padStart(4, '0')}|${fecha}`;
        if (!buckets.has(key)) buckets.set(key, { index: dayIndex, date: fecha, stops: [] });
        buckets.get(key).stops.push(c);
    });
    return [...buckets.values()]
        .sort((a, b) => a.index - b.index || _trkCompararFecha(a.date, b.date))
        .map((d, i) => ({
            ...d,
            index: i,
            stops: d.stops.sort((a, b) => Number(a.orden_dia || a.orden_ruta || 0) - Number(b.orden_dia || b.orden_ruta || 0)),
        }));
}

function _trkPlanFechaBaseRuta() {
    return $('#rutaFecha').val() || _trkFechaMinimaProgramacion();
}

function _trkPlanPoliticaFechaDia(days, dayIndex) {
    const base = _trkPlanFechaBaseRuta();
    const minGlobal = _trkFechaSumarDias(base, -1) || base;
    const maxManual = _trkPlanFechaMaxManual();
    if (dayIndex <= 0) {
        return { min: minGlobal, max: maxManual, locked: false };
    }
    const prev = days?.[dayIndex - 1]?.date || _trkFechaSumarDias(base, dayIndex - 1);
    const min = _trkFechaSumarDias(prev, 1);
    return {
        min,
        max: _trkCompararFecha(min, maxManual) > 0 ? min : maxManual,
        locked: false,
    };
}

function _trkPlanNormalizarFechasPolitica(days) {
    if (!Array.isArray(days) || !days.length) return days || [];
    days.forEach((day, dayIndex) => {
        const policy = _trkPlanPoliticaFechaDia(days, dayIndex);
        let fecha = day.date || policy.min;
        if (policy.locked) {
            fecha = policy.min;
        } else {
            if (_trkCompararFecha(fecha, policy.min) < 0) fecha = policy.min;
            if (_trkCompararFecha(fecha, policy.max) > 0) fecha = policy.max;
        }
        day.index = dayIndex;
        day.date = fecha;
        (day.stops || []).forEach(c => {
            c.day_index = dayIndex;
            c.fecha_planeacion = fecha;
        });
    });
    return days;
}

function _trkPlanCascadeCurrent() {
    const config = _trkPlanConfig();
    const days = _trkPlanNormalizarFechasPolitica(
        _trkPlanApplyCascade(_trkPlanNormalizarFechasPolitica(_trkPlanBuildDaysFromCurrent()), config)
    );
    _trkPlanApplyDaysToRoute(days);
}

function _trkPlanCambiarFechaDia(dayIndex, fecha) {
    if (!fecha) return;
    let days = _trkPlanNormalizarFechasPolitica(_trkPlanBuildDaysFromCurrent());
    if (!days.length || !days[dayIndex]) return;
    const policy = _trkPlanPoliticaFechaDia(days, dayIndex);
    if (policy.locked) {
        Swal.fire({
            icon: 'info',
            title: 'Dia 1 bloqueado',
            text: 'El Dia 1 siempre usa la fecha de salida de la ruta para evitar dias sin operacion.',
            confirmButtonText: 'Aceptar',
        });
        _trkPlanApplyDaysToRoute(days);
        _trkRenderPlaneacionDias();
        return;
    }
    if (_trkCompararFecha(fecha, policy.min) < 0 || _trkCompararFecha(fecha, policy.max) > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Fecha fuera de rango',
            html: `El Dia ${dayIndex + 1} solo puede programarse del <b>${_trkFechaCorta(policy.min)}</b> al <b>${_trkFechaCorta(policy.max)}</b>.`,
            confirmButtonText: 'Aceptar',
        });
        _trkRenderPlaneacionDias();
        return;
    }
    days[dayIndex].date = fecha;
    (days[dayIndex].stops || []).forEach(c => {
        c.fecha_planeacion = fecha;
        c.fecha_independiente = 1;
    });
    days = _trkPlanNormalizarFechasPolitica(days);
    _trkPlanApplyDaysToRoute(days);
    _trkPlanCascadeCurrent();
    _trkRenderListaCreditos();
    _trkRenderizarMapa();
    _trkMarcarCambio();
}

function _trkPlanCamposPayload(c) {
    return {
        day_index: Number(c?.day_index || 0),
        arrival_minutes: _trkPlanOptionalNumber(c?.arrival_minutes),
        departure_minutes: _trkPlanOptionalNumber(c?.departure_minutes),
        travel_from_prev_minutes: _trkPlanOptionalNumber(c?.travel_from_prev_minutes),
        operation_minutes: _trkPlanOptionalNumber(c?.operation_minutes),
        pinned: Number(c?.pinned || 0),
        edited: Number(c?.edited || 0),
    };
}

function _trkMetersLabel(value) {
    const meters = Number(value || 0);
    if (!Number.isFinite(meters) || meters <= 0) return '-';
    if (meters < 1000) return `${Math.round(meters)} m`;
    return `${(meters / 1000).toLocaleString('es-MX', { maximumFractionDigits: 1 })} km`;
}

function _trkSecondsLabel(value) {
    const seconds = Number(value || 0);
    if (!Number.isFinite(seconds) || seconds <= 0) return '-';
    const minutes = Math.round(seconds / 60);
    if (minutes < 60) return `${minutes} min`;
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;
    return rest ? `${hours} h ${rest} min` : `${hours} h`;
}

function _trkPlanSourceLabel(source) {
    const key = String(source || '').trim().toLowerCase();
    if (key === 'google_routes') return 'Google Routes';
    if (key === 'google_maps') return 'Google Maps';
    if (key === 'google_maps_route' || key === 'maps_directions') return 'Google Maps (ruta del mapa)';
    if (key === 'local_fallback') return 'Propuesta estimada';
    if (!key) return '';
    return key
        .split(/[_\s-]+/)
        .filter(Boolean)
        .map(p => p.charAt(0).toUpperCase() + p.slice(1))
        .join(' ');
}

function _trkPlanOperacionResumenLabel(resumen) {
    const total = Number(resumen?.operacion_total_min || 0);
    const porParada = Number(_trkPlanConfig().operacion || 45);
    if (!Number.isFinite(total) || total <= 0) return '-';
    if (total > porParada && porParada > 0) {
        return `${porParada} min por parada · ${total} min total`;
    }
    return `${total} min operacion`;
}

function _trkPlanBodyCalculoReal(persistir = false) {
    const config = _trkPlanConfig();
    return {
        origen: 'cedis',
        usar_gps_transportista: false,
        fecha_salida: $('#rutaFecha').val() || _trkFechaMinimaProgramacion(),
        hora_salida: _trkHoraToPayload(),
        inicio_jornada: $('#trkPlanInicioJornada').val() || '10:00',
        fin_jornada: $('#trkPlanFinJornada').val() || '19:00',
        min_por_parada: Number(config.operacion || 45),
        traslado_entre_estados: Number(config.trasladoEstados || 0),
        persistir: !!persistir,
    };
}

function _trkNormalizarCalculoTiemposRespuesta(r) {
    const data = r?.datos || r?.data || r || {};
    return {
        ...data,
        success: Boolean(r?.success ?? data?.success),
        source: data?.source || r?.source || '',
        resumen: data?.resumen || r?.resumen || null,
        legs: Array.isArray(data?.legs) ? data.legs : (Array.isArray(r?.legs) ? r.legs : []),
        planeacion: Array.isArray(data?.planeacion) ? data.planeacion : (Array.isArray(r?.planeacion) ? r.planeacion : []),
        warnings: Array.isArray(data?.warnings) ? data.warnings : (Array.isArray(r?.warnings) ? r.warnings : []),
        warning_groups: Array.isArray(data?.warning_groups) ? data.warning_groups : (Array.isArray(r?.warning_groups) ? r.warning_groups : []),
        mensaje: r?.message || r?.mensaje || r?.detail || data?.message || data?.mensaje || data?.detail || '',
        codigo_http: r?.codigo_http || data?.codigo_http || 200,
    };
}

function _trkWarningGroupEsUbicacion(group) {
    const txt = _trkNormTxt([group?.codigo, group?.tipo, group?.titulo, group?.mensaje, group?.accion].filter(Boolean).join(' '));
    return /UBICACION|UBICACIONES|COORDENADA|COORDENADAS|GPS|LATITUD|LONGITUD|CREDITOS_SIN_UBICACION/.test(txt);
}

function _trkWarningCreditoResuelto(item) {
    const cred = _trkBuscarCreditoWarning(item?.id_detalle, item?.id_credito || item?.credito, item?.orden_ruta || item?.orden);
    return !cred || _trkCreditoTieneCoordenadasValidas(cred);
}

function _trkWarningGroupNormalizado(group) {
    if (!_trkWarningGroupEsUbicacion(group) || !Array.isArray(group?.creditos) || !group.creditos.length) {
        return group;
    }
    const pendientes = group.creditos.filter(item => !_trkWarningCreditoResuelto(item));
    if (!pendientes.length) return null;
    return {
        ...group,
        total: pendientes.length,
        creditos: pendientes,
    };
}

function _trkWarningGroups(dataOrGroups, opts = {}) {
    const pareceGrupo = g => g && typeof g === 'object' && (
        g.codigo || g.tipo || g.titulo || g.mensaje || g.accion || Array.isArray(g.creditos)
    );
    let groups = [];
    if (Array.isArray(dataOrGroups)) groups = dataOrGroups.filter(pareceGrupo);
    else if (Array.isArray(dataOrGroups?.warning_groups)) groups = dataOrGroups.warning_groups.filter(pareceGrupo);
    if (opts.onlyActive === false) return groups;
    return groups
        .map(_trkWarningGroupNormalizado)
        .filter(Boolean);
}

function _trkPlanWarningGroupRemanentes() {
    const remanentes = _trkPlanCreditosRemanentes();
    if (!remanentes.length) return null;
    return {
        codigo: 'creditos_sin_ubicacion',
        tipo: 'ubicacion',
        titulo: 'Faltan ubicaciones para calcular la ruta',
        mensaje: `${remanentes.length} credito${remanentes.length === 1 ? '' : 's'} quedan como remanente${remanentes.length === 1 ? '' : 's'} porque no tienen coordenadas confirmadas.`,
        accion: 'Realiza el ajuste desde Planeacion auditada por dia usando el puntero del credito. Despues vuelve a generar el plan.',
        total: remanentes.length,
        creditos: remanentes.map(c => ({
            id_detalle: c.id_detalle,
            id_credito: c.id_credito,
            orden_ruta: c.orden_ruta,
        })),
    };
}

function _trkWarningGroupsActuales() {
    const list = _trkWarningGroups(_trk.planeacionRealWarningGroups);
    if (list.some(_trkWarningGroupEsUbicacion)) return list;
    const remanentes = _trkPlanWarningGroupRemanentes();
    return remanentes ? list.concat([remanentes]) : list;
}

function _trkWarningCreditoItemHtml(item, opts = {}) {
    const idCredito = item?.id_credito || item?.credito || '';
    const orden = item?.orden_ruta || item?.orden || '';
    const idDetalle = item?.id_detalle || '';
    const cred = idDetalle ? _trkCreditoPorDetalle(idDetalle) : (_trk.creditosEnRuta || []).find(c => String(c.id_credito || '') === String(idCredito || ''));
    const creditoLabel = idCredito || cred?.id_credito || 'Sin credito';
    const ordenLabel = orden || cred?.orden_ruta || '';
    const tooltip = idDetalle ? ` title="ID detalle tecnico: ${_trkChatEscapeHtml(idDetalle)}"` : '';
    const resuelto = Boolean(cred && _trkCreditoTieneCoordenadasValidas(cred));
    const itemClass = resuelto ? ' trk-warning-credit-item-ok' : '';
    const dataAttrs = `data-id-detalle="${_trkChatEscapeHtml(idDetalle)}" data-id-credito="${_trkChatEscapeHtml(creditoLabel)}" data-orden-ruta="${_trkChatEscapeHtml(ordenLabel)}"`;
    let actionHtml = '';
    if (resuelto) {
        actionHtml = '<div class="trk-warning-credit-actions"><span class="trk-warning-credit-ok" title="Ubicacion confirmada"><i class="fa-solid fa-check"></i></span></div>';
    } else if (opts.showActions) {
        actionHtml = `<div class="trk-warning-credit-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary trk-warning-pin-btn" ${dataAttrs} title="Asignar ubicacion">
                <i class="fa-solid fa-map-pin"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger trk-warning-delete-btn" ${dataAttrs} title="Descartar credito de la ruta">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>`;
    }
    return `<div class="trk-warning-credit-item${itemClass}"${tooltip}>
        <div class="trk-warning-credit-main">
            <strong>#${_trkChatEscapeHtml(creditoLabel)}</strong>
            <small>${ordenLabel ? `Orden ${_trkChatEscapeHtml(ordenLabel)}` : 'Orden no disponible'}</small>
        </div>
        ${actionHtml}
    </div>`;
}

function _trkWarningGroupCreditosHtml(group, opts = {}) {
    const creditos = Array.isArray(group?.creditos) ? group.creditos : [];
    if (!creditos.length) return '';
    const first = creditos.slice(0, 5).map(item => _trkWarningCreditoItemHtml(item, opts)).join('');
    const restItems = creditos.slice(5);
    const rest = restItems.map(item => _trkWarningCreditoItemHtml(item, opts)).join('');
    const codigo = String(group?.codigo || '').toLowerCase();
    const label = codigo === 'creditos_sin_ubicacion'
        ? 'Creditos con ubicacion pendiente:'
        : 'Creditos afectados:';
    return `<div class="mt-2">
        <div class="fw-semibold mb-1">${_trkChatEscapeHtml(label)}</div>
        <div class="trk-warning-credit-list">${first}</div>
        ${rest ? `<details><summary>Ver todos (${restItems.length})</summary><div class="trk-warning-credit-list mt-2">${rest}</div></details>` : ''}
    </div>`;
}

function _trkWarningGroupsHtml(groups, opts = {}) {
    const list = _trkWarningGroups(groups);
    if (!list.length) return '';
    return `<div class="trk-warning-groups">
        ${list.map(group => {
            const title = _trkWarningTextoOperativo(group?.titulo || 'Advertencia de planeacion') || 'Advertencia de planeacion';
            const msg = _trkWarningTextoOperativo(group?.mensaje || '');
            const action = _trkWarningGroupEsUbicacion(group)
                ? 'Ajusta estas ubicaciones desde Planeacion auditada por dia usando el puntero del credito. Cuando termines, vuelve a generar el plan.'
                : _trkWarningTextoOperativo(group?.accion || '');
            return `<div class="trk-warning-group">
                <div class="trk-warning-group-title">${_trkChatEscapeHtml(title)}</div>
                ${msg ? `<div class="trk-warning-group-message">${_trkChatEscapeHtml(msg)}</div>` : ''}
                ${_trkWarningGroupCreditosHtml(group, opts)}
                ${action ? `<div class="trk-warning-group-action"><i class="fa-solid fa-circle-info me-1"></i>${_trkChatEscapeHtml(action)}</div>` : ''}
            </div>`;
        }).join('')}
    </div>`;
}

function _trkBuscarCreditoWarning(idDetalle, idCredito, ordenRuta = '') {
    const detalle = String(idDetalle || '').trim();
    const credito = String(idCredito || '').replace(/^#/, '').trim();
    const orden = Number(String(ordenRuta || '').replace(/[^\d]/g, ''));
    const lista = _trk.creditosEnRuta || [];

    return (detalle ? _trkCreditoPorDetalle(detalle) : null)
        || (credito ? lista.find(c => String(c.id_credito || '') === credito) : null)
        || (detalle ? lista.find(c => _trkDetalleIdsCredito(c).includes(detalle)) : null)
        || (Number.isFinite(orden) && orden > 0 ? lista.find(c => Number(c.orden_ruta || 0) === orden) : null)
        || null;
}

function _trkAbrirPinDesdeWarning(idDetalle, idCredito, ordenRuta = '') {
    const cred = _trkBuscarCreditoWarning(idDetalle, idCredito, ordenRuta);
    if (!cred) {
        Swal.fire({
            icon: 'info',
            title: 'Credito no encontrado',
            text: 'No se encontro este credito dentro de la ruta actual.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }
    if (Swal.isVisible()) Swal.close();
    _trkAbrirMapPicker(cred, { autoCenter: true });
}

function _trkAdvertenciasSwalHtmlActual() {
    const warningGroups = _trkWarningGroupsActuales();
    const warnings = Array.isArray(_trk.planeacionRealWarnings) ? _trk.planeacionRealWarnings : [];
    if (warningGroups.length) {
        return `${_trkWarningGroupsHtml(warningGroups)}
            <div class="alert alert-warning text-start mt-2 mb-0" style="font-size:14px;line-height:1.5;">
                Mientras tanto, revisa los tiempos antes de guardar la propuesta.
            </div>`;
    }
    return _trkWarningsListaHtml(warnings);
}

function _trkEliminarCreditoDesdeWarning(idDetalle, idCredito, ordenRuta = '') {
    const cred = _trkBuscarCreditoWarning(idDetalle, idCredito, ordenRuta);
    if (!cred) {
        Swal.fire({
            icon: 'info',
            title: 'Credito no encontrado',
            text: 'No se encontro este credito dentro de la ruta actual.',
            confirmButtonText: 'Aceptar',
        });
        return;
    }

    _trkQuitarCredito(cred.id_credito);
    _trkActualizarAdvertenciasUbicacionActuales();
    _trkRenderPlaneacionRealInfo();

    if (Swal.isVisible()) {
        const html = _trkAdvertenciasSwalHtmlActual();
        if (html) {
            const grupos = _trkWarningGroupsActuales();
            Swal.update({
                title: grupos.length ? _trkWarningsModalTitle(grupos, 'Faltan ubicaciones para calcular la ruta') : 'Tiempos estimados con advertencias',
                html,
            });
        } else {
            Swal.close();
        }
    }
}

if (!window._trkWarningPinDelegado) {
    window._trkWarningPinDelegado = true;
    document.addEventListener('click', function (e) {
        const btn = e.target?.closest?.('.trk-warning-pin-btn, .trk-warning-delete-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        if (btn.classList.contains('trk-warning-delete-btn')) {
            _trkEliminarCreditoDesdeWarning(btn.dataset.idDetalle, btn.dataset.idCredito, btn.dataset.ordenRuta);
            return;
        }
        _trkAbrirPinDesdeWarning(btn.dataset.idDetalle, btn.dataset.idCredito, btn.dataset.ordenRuta);
    });
}

function _trkWarningsListaHtml(warnings) {
    const items = (warnings || []).map(_trkWarningTexto).filter(Boolean);
    if (!items.length) return '';
    return `<div class="trk-warning-groups">
        <div class="trk-warning-group">
            <div class="trk-warning-group-title">Tiempos estimados con advertencias</div>
            <ul class="mb-0 ps-3">
                ${items.map(txt => `<li>${_trkChatEscapeHtml(txt)}</li>`).join('')}
            </ul>
        </div>
    </div>`;
}

function _trkWarningsModalHtml(dataOrWarnings, groups = []) {
    const groupList = _trkWarningGroups(groups.length ? groups : dataOrWarnings);
    if (groupList.length) return _trkWarningGroupsHtml(groupList);
    const warnings = Array.isArray(dataOrWarnings) ? dataOrWarnings : (Array.isArray(dataOrWarnings?.warnings) ? dataOrWarnings.warnings : []);
    return _trkWarningsListaHtml(warnings);
}

function _trkWarningsModalTitle(dataOrWarnings, fallback = 'Tiempos estimados con advertencias') {
    const groups = _trkWarningGroups(dataOrWarnings);
    return groups[0]?.titulo || fallback;
}

function _trkWarningTexto(warning) {
    if (warning == null) return '';
    let txt = '';
    if (typeof warning === 'string') {
        txt = warning;
    } else {
        txt = warning.message || warning.mensaje || warning.detail || JSON.stringify(warning);
    }
    return _trkWarningTextoOperativo(txt);
}

function _trkWarningCedisNombre(id) {
    const cedis = _trkCedisPorId(id) || _trkCedisDestinoSeleccionado();
    return cedis?.nombre_agencia ? `CEDIS ${cedis.nombre_agencia}` : 'CEDIS destino';
}

function _trkDetalleIdsCredito(cred) {
    return [
        cred?.id_detalle,
        cred?.detalle_id,
        cred?.id_ruta_detalle,
        cred?.id_detalle_ruta,
        cred?.id_asigna_detalle,
        cred?.id_asigna_hora_detalle,
        cred?.id_asigna_horas_tracking_detalle,
        cred?.id_tracking_detalle,
        cred?.id_punto,
    ]
        .filter(v => v !== null && v !== undefined && String(v).trim() !== '')
        .map(v => String(v).trim());
}

function _trkCreditoPorDetalle(idDetalle) {
    const id = String(idDetalle || '').trim();
    if (!id) return null;
    return (_trk.creditosEnRuta || []).find(c => _trkDetalleIdsCredito(c).includes(id)) || null;
}

function _trkWarningCreditoSinCoords(idDetalle) {
    const cred = _trkCreditoPorDetalle(idDetalle);
    const label = cred
        ? `Credito #${cred.id_credito || idDetalle}${cred.nombre_cliente ? ` - ${cred.nombre_cliente}` : ''}`
        : 'Un credito de la ruta';
    const tieneDireccion = cred ? Boolean(_trkDireccionBusquedaCredito(cred)) : false;
    if (cred?._geo_auto_resuelto) {
        return `${label}: Google Maps encontro coordenadas, pero el servicio de tracking aun no las uso para el calculo. Intenta calcular nuevamente.`;
    }
    if (tieneDireccion) {
        return `${label}: sin coordenadas. Se intento buscar en Google Maps con la direccion registrada, pero no se obtuvo una ubicacion confiable. Confirma el pin manualmente.`;
    }
    return `${label}: sin coordenadas ni direccion suficiente para buscar en Google Maps. Captura o confirma el pin manualmente.`;
}

function _trkWarningCreditoLabel(id) {
    const raw = String(id || '').replace(/^#/, '').trim();
    if (!raw) return 'credito';

    const lista = _trk.creditosEnRuta || [];
    const cred = _trkCreditoPorDetalle(raw)
        || lista.find(c => String(c.id_credito || '') === raw)
        || null;
    const idCredito = cred?.id_credito || raw;

    return `credito #${idCredito}`;
}

function _trkWarningTextoOperativo(texto) {
    let txt = String(texto || '').trim();
    if (!txt) return '';

    if (/asigna_horas_tracking_detalle/i.test(txt) || /columnas?\s+de\s+planeacion/i.test(txt)) {
        return 'La planeacion se genero, pero el servicio no pudo guardar todos los tiempos estimados. Puedes continuar y volver a guardar cuando el servicio este actualizado.';
    }
    if (/(table|tabla|column|columna|sql|database|bd|schema|campo)\b/i.test(txt) && /planeacion|tracking|ruta/i.test(txt)) {
        return 'La planeacion se genero con una advertencia tecnica del servicio. Puedes continuar y revisar el guardado de tiempos mas tarde.';
    }

    const warningSinCoords = txt.match(/credito\s+id_detalle\s*=\s*(\d+)\s+sin\s+latitud\s+o\s+longitud;\s*no\s+se\s+calcularon\s+tiempos/i);
    if (warningSinCoords) {
        return _trkWarningCreditoSinCoords(warningSinCoords[1]);
    }

    txt = txt.replace(/\bhora_salida\b/gi, 'hora de salida');
    txt = txt.replace(/\binicio_jornada\b/gi, 'inicio de jornada');
    txt = txt.replace(/\bfin_jornada\b/gi, 'fin de jornada');
    txt = txt.replace(/\bday_index\s*=\s*(\d+)\b/gi, (_, n) => `Dia ${Number(n) + 1}`);
    txt = txt.replace(/\bcedis\s+(\d+)\b/gi, (_, id) => _trkWarningCedisNombre(id));
    txt = txt.replace(/\bcredito\s+#?(\d+)\b/gi, (_, id) => _trkWarningCreditoLabel(id));

    txt = txt.replace(
        /La planeacion rebasa la jornada inicial y llega hasta Dia\s+(\d+)/i,
        'La planeacion se extiende hasta el Dia $1.'
    );
    txt = txt.replace(
        /El tramo hacia (.+?) no cabe completo dentro de una jornada/i,
        'El traslado hacia $1 no cabe completo dentro de una jornada.'
    );
    txt = txt.replace(
        /hora de salida anterior a inicio de jornada;\s*se ajusto al inicio de jornada/i,
        'La hora de salida era anterior al inicio de jornada; se ajusto automaticamente al horario de inicio.'
    );

    return txt;
}

function _trkWarningsBloqueantes(warnings, groups = []) {
    const rawGroups = _trkWarningGroups(groups, { onlyActive: false });
    const groupList = _trkWarningGroups(groups);
    if (rawGroups.length) {
        return groupList.some(g => {
            const txt = _trkNormTxt([g?.codigo, g?.tipo, g?.titulo, g?.mensaje, g?.accion].filter(Boolean).join(' '));
            return /UBICACION|UBICACIONES|COORDENADA|COORDENADAS|GPS|LATITUD|LONGITUD|CREDITOS_SIN_UBICACION/.test(txt);
        });
    }
    return (warnings || []).some(w => {
        const txt = _trkNormTxt(_trkWarningTexto(w));
        return /CEDIS|COORDENADA|COORDENADAS|UBICACION|GPS|LATITUD|LONGITUD/.test(txt);
    });
}

function _trkCreditoTieneCoordenadasValidas(cred) {
    return Boolean(_trkCreditoPosicionBasica(cred));
}

function _trkWarningTextoEsUbicacion(warning) {
    const txt = _trkNormTxt(_trkWarningTexto(warning));
    return /CEDIS|COORDENADA|COORDENADAS|UBICACION|UBICACIONES|GPS|LATITUD|LONGITUD/.test(txt);
}

function _trkActualizarAdvertenciasUbicacionActuales() {
    const rawGroups = _trkWarningGroups(_trk.planeacionRealWarningGroups || [], { onlyActive: false });
    const activeGroups = _trkWarningGroups(rawGroups);
    const teniaGruposUbicacion = rawGroups.some(_trkWarningGroupEsUbicacion);
    const quedanGruposUbicacion = activeGroups.some(_trkWarningGroupEsUbicacion);

    _trk.planeacionRealWarningGroups = activeGroups;
    if (teniaGruposUbicacion && !quedanGruposUbicacion) {
        _trk.planeacionRealWarnings = (_trk.planeacionRealWarnings || [])
            .filter(w => !_trkWarningTextoEsUbicacion(w));
        if (!_trkPlanCreditosRemanentes().length) {
            _trk.planeacionCreditosListosMensaje = true;
        }
    }

    return {
        teniaGruposUbicacion,
        quedanGruposUbicacion,
        activeGroups,
    };
}

function _trkRecalcularTiemposSiPinsCompletos() {
    const estado = _trkActualizarAdvertenciasUbicacionActuales();
    if (!estado.teniaGruposUbicacion || estado.quedanGruposUbicacion) return;
    _trk.planeacionCreditosListosMensaje = true;
    _trkRenderPlaneacionRealInfo();
}

function _trkDireccionCreditoUtil(valor) {
    const txt = _trkNormTxt(valor);
    if (!txt) return false;
    return !['NA', 'N/A', 'NO APLICA', 'SIN DATOS', 'NO DISPONIBLE', 'EN ESPERA DE DATOS', 'SIN UBICACION', 'SIN DIRECCION', 'NULL'].includes(txt);
}

function _trkDireccionBusquedaCredito(cred) {
    if (!cred) return '';
    const direccion = _trkDireccionCreditoUtil(cred.direccion_google)
        ? cred.direccion_google
        : (_trkDireccionCreditoUtil(cred.direccion) ? cred.direccion : '');
    const estado = _trkEstadoMayus(cred.estado, cred.municipio);
    const municipio = _trkMunicipioMayus(cred.municipio, estado);
    if (!_trkDireccionCreditoUtil(direccion)) return '';
    return [direccion, municipio, estado, 'Mexico']
        .filter(v => _trkDireccionCreditoUtil(v))
        .join(', ');
}

function _trkGeoComponente(result, tipos) {
    const components = Array.isArray(result?.address_components) ? result.address_components : [];
    const found = components.find(c => tipos.some(t => c.types?.includes(t)));
    return found?.long_name || found?.short_name || '';
}

function _trkGeoZonaDesdeResultado(result) {
    const estado = _trkGeoComponente(result, ['administrative_area_level_1']);
    const localidad = _trkGeoComponente(result, ['locality']);
    const alcaldia = _trkGeoComponente(result, ['administrative_area_level_2']);
    const sublocalidad = _trkGeoComponente(result, ['sublocality_level_1']);
    const barrio = _trkGeoComponente(result, ['neighborhood']);
    const estadoCanonico = _trkEstadoCanonico(estado, alcaldia || sublocalidad || localidad || barrio);
    const municipioBase = estadoCanonico === 'CIUDAD DE MEXICO'
        ? (alcaldia || sublocalidad || localidad || barrio)
        : (localidad || alcaldia || sublocalidad || barrio);
    const estadoFinal = _trkEstadoMayus(estadoCanonico || estado, municipioBase);
    return {
        estado: estadoFinal,
        municipio: _trkMunicipioMayus(municipioBase, estadoFinal),
        direccion: result?.formatted_address || '',
    };
}

function _trkGeocodificarCreditoSugerido(cred) {
    const address = _trkDireccionBusquedaCredito(cred);
    if (!cred || !address || !window.google || !google.maps || !google.maps.Geocoder) {
        return Promise.resolve(null);
    }
    if (!_trk.geocoder) _trk.geocoder = new google.maps.Geocoder();

    return new Promise(resolve => {
        _trk.geocoder.geocode({
            address,
            componentRestrictions: { country: 'MX' },
        }, (results, status) => {
            if (status !== 'OK' || !results || !results[0]) {
                resolve(null);
                return;
            }
            const loc = results[0].geometry?.location;
            const lat = typeof loc?.lat === 'function' ? loc.lat() : null;
            const lng = typeof loc?.lng === 'function' ? loc.lng() : null;
            if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat === 0 || lng === 0) {
                resolve(null);
                return;
            }
            const zona = _trkGeoZonaDesdeResultado(results[0]);
            resolve({
                lat,
                lng,
                direccion: zona.direccion || results[0].formatted_address || address,
                estado: zona.estado || '',
                municipio: zona.municipio || '',
                components: results[0].address_components || null,
            });
        });
    });
}

function _trkGeocodificarCreditoAutomatico(cred) {
    if (!cred || _trkCreditoTieneCoordenadasValidas(cred)) {
        return Promise.resolve(null);
    }
    const address = _trkDireccionBusquedaCredito(cred);
    if (!address) return Promise.resolve(null);
    if (!window.google || !google.maps || !google.maps.Geocoder) {
        return Promise.resolve(null);
    }
    if (!_trk.geocoder) _trk.geocoder = new google.maps.Geocoder();

    return new Promise(resolve => {
        _trk.geocoder.geocode({ address }, (results, status) => {
            if (status !== 'OK' || !results || !results[0]) {
                cred._geo_auto_fallo = true;
                resolve(null);
                return;
            }
            const loc = results[0].geometry?.location;
            const lat = typeof loc?.lat === 'function' ? loc.lat() : null;
            const lng = typeof loc?.lng === 'function' ? loc.lng() : null;
            if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat === 0 || lng === 0) {
                cred._geo_auto_fallo = true;
                resolve(null);
                return;
            }
            const zona = _trkGeoZonaDesdeResultado(results[0]);
            cred.latitud_manual = lat;
            cred.longitud_manual = lng;
            cred.latitud = lat;
            cred.longitud = lng;
            cred._geo_auto_resuelto = true;
            cred._geo_auto_fallo = false;
            if (zona.direccion) {
                cred.direccion_google = zona.direccion;
                cred.direccion = zona.direccion;
            }
            if (zona.estado) cred.estado = zona.estado;
            if (zona.municipio) cred.municipio = zona.municipio;
            _trk.creditoPosiciones[String(cred.id_credito || '')] = { lat, lng };
            resolve({
                id_detalle: Number(cred.id_detalle || 0),
                id_credito: Number(cred.id_credito || 0),
                latitud: lat,
                longitud: lng,
                direccion: cred.direccion_google || cred.direccion || '',
                estado: cred.estado || '',
                municipio: cred.municipio || '',
                motivo: 'Correccion manual de ubicacion desde Sparta Ledger',
                origen: 'sparta',
            });
        });
    });
}

function _trkCoordenadasPayloadCredito(cred) {
    if (!cred || !_trkCreditoTieneCoordenadasValidas(cred)) return null;
    const pos = _trkCreditoPosicionBasica(cred);
    if (!pos) return null;
    return {
        id_detalle: Number(cred.id_detalle || 0),
        id_credito: Number(cred.id_credito || 0),
        latitud: pos.lat,
        longitud: pos.lng,
        direccion: cred.direccion_google || cred.direccion || '',
        estado: cred.estado || '',
        municipio: cred.municipio || '',
        motivo: 'Correccion manual de ubicacion desde Sparta Ledger',
        origen: 'sparta',
    };
}

async function _trkPersistirCoordenadasRuta(creditos) {
    const idRuta = Number(_trk.idRutaEditando || 0);
    const items = Array.isArray(creditos) ? creditos.filter(Boolean) : [];
    if (!idRuta || !items.length) return 0;
    const r = await trkFetch('/TrackingRecoleccion/actualizarCoordenadasRuta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_ruta: idRuta, creditos: items }),
    });
    if (!r?.success) {
        console.warn('[Tracking ubicacion puntual] Fallo al persistir coordenadas', {
            id_ruta: idRuta,
            items,
            response: r,
        });
        throw new Error(r?.message || r?.mensaje || 'No se pudieron guardar las coordenadas detectadas.');
    }
    console.info('[Tracking ubicacion puntual] Coordenadas persistidas', {
        id_ruta: idRuta,
        enviados: items.map(i => ({
            id_detalle: i.id_detalle || null,
            id_credito: i.id_credito || null,
            latitud: i.latitud,
            longitud: i.longitud,
        })),
        response: r,
    });
    return Number(r?.actualizados || 0);
}

function _trkEsperarGoogleMapsDisponible() {
    if (window.google && google.maps && google.maps.Geocoder) {
        return Promise.resolve(true);
    }
    if (!window._trackGoogleMapsKey) return Promise.resolve(false);
    return new Promise(resolve => {
        let done = false;
        const finish = (ok) => {
            if (done) return;
            done = true;
            resolve(!!ok);
        };
        try {
            _trkAsegurarGoogleMaps(() => finish(Boolean(window.google && google.maps && google.maps.Geocoder)));
        } catch (_) {
            finish(false);
        }
        setTimeout(() => finish(Boolean(window.google && google.maps && google.maps.Geocoder)), 10000);
    });
}

async function _trkResolverCoordenadasFaltantesRuta(opts = {}) {
    const persistir = opts.persistir !== false;
    const faltantes = (_trk.creditosEnRuta || [])
        .filter(c => !_trkCreditoTieneCoordenadasValidas(c))
        .filter(c => _trkDireccionBusquedaCredito(c));
    if (!faltantes.length) {
        const stats = { intentados: 0, resueltos: 0, persistidos: 0 };
        _trk.ultimaGeoAuto = stats;
        return stats;
    }
    const mapsOk = await _trkEsperarGoogleMapsDisponible();
    if (!mapsOk) {
        faltantes.forEach(c => {
            c._geo_auto_fallo = true;
            c._geo_auto_motivo = 'Google Maps no disponible';
        });
        const stats = { intentados: faltantes.length, resueltos: 0, persistidos: 0, sinGoogle: true };
        _trk.ultimaGeoAuto = stats;
        return stats;
    }

    const resueltos = [];
    for (const cred of faltantes) {
        const item = await _trkGeocodificarCreditoAutomatico(cred);
        if (item) resueltos.push(item);
        await _trkSleep(140);
    }

    let persistidos = 0;
    if (persistir && resueltos.length) {
        persistidos = await _trkPersistirCoordenadasRuta(resueltos);
    }
    if (resueltos.length) {
        _trkRenderListaCreditos();
        _trkRenderizarMapa();
    }
    const stats = {
        intentados: faltantes.length,
        resueltos: resueltos.length,
        persistidos,
    };
    _trk.ultimaGeoAuto = stats;
    return stats;
}

function _trkAplicarPlaneacionReal(data) {
    const planeacion = Array.isArray(data?.planeacion) ? data.planeacion : [];
    const legs = Array.isArray(data?.legs) ? data.legs : [];
    let aplicados = 0;
    const calculablesOrdenados = _trkPlanCreditosCalculables(_trkCreditosOrdenRutaActual());
    const addKey = (map, key, value) => {
        const k = String(key ?? '').trim();
        if (k) map.set(k, value);
    };
    const indexar = (items) => {
        const byDetalle = new Map();
        const byCredito = new Map();
        const byOrden = new Map();
        (items || []).forEach((item, idx) => {
            addKey(byDetalle, item.id_detalle, item);
            addKey(byDetalle, item.to_id_detalle, item);
            addKey(byDetalle, item.detalle_id, item);
            addKey(byCredito, item.id_credito, item);
            addKey(byCredito, item.to_id_credito, item);
            addKey(byCredito, item.credito, item);
            addKey(byCredito, item.credito_id, item);
            addKey(byOrden, item.orden_ruta, item);
            addKey(byOrden, item.orden, item);
            addKey(byOrden, item.position, item);
            addKey(byOrden, idx + 1, item);
        });
        return { byDetalle, byCredito, byOrden, byIndex: items || [] };
    };
    const planIdx = indexar(planeacion);
    const legIdx = indexar(legs);
    const pick = (idxs, c, idx) =>
        idxs.byDetalle.get(String(c.id_detalle || ''))
        || idxs.byCredito.get(String(c.id_credito || ''))
        || idxs.byOrden.get(String(c.orden_ruta || ''))
        || idxs.byIndex[idx]
        || null;
    const travelMinutes = item => {
        if (!item) return null;
        if (_trkPlanHasNumber(item.travel_from_prev_minutes)) return Number(item.travel_from_prev_minutes);
        if (_trkPlanHasNumber(item.travel_minutes)) return Number(item.travel_minutes);
        if (_trkPlanHasNumber(item.duration_in_traffic_seconds)) return Math.max(0, Math.round(Number(item.duration_in_traffic_seconds) / 60));
        if (_trkPlanHasNumber(item.duration_seconds)) return Math.max(0, Math.round(Number(item.duration_seconds) / 60));
        if (_trkPlanHasNumber(item.duration)) return Math.max(0, Math.round(Number(item.duration) / 60));
        return null;
    };
    (_trk.creditosEnRuta || []).forEach(c => {
        const idx = calculablesOrdenados.findIndex(x =>
            String(x.id_detalle || '') === String(c.id_detalle || '')
            || String(x.id_credito || '') === String(c.id_credito || '')
        );
        const p = pick(planIdx, c, idx);
        const leg = pick(legIdx, c, idx);
        if (p) {
            c.arrival_minutes = _trkPlanHasNumber(p.arrival_minutes) ? Number(p.arrival_minutes) : c.arrival_minutes;
            c.departure_minutes = _trkPlanHasNumber(p.departure_minutes) ? Number(p.departure_minutes) : c.departure_minutes;
            c.operation_minutes = _trkPlanHasNumber(p.operation_minutes) ? Number(p.operation_minutes) : c.operation_minutes;
            const travel = travelMinutes(p);
            c.travel_from_prev_minutes = travel !== null ? travel : c.travel_from_prev_minutes;
            if (_trkPlanHasNumber(p.day_index) && !c.fecha_planeacion) {
                c.day_index = Number(p.day_index);
            }
            c._plan_real = true;
            c._plan_travel_pending = false;
            c.travel_source = data?.source || 'google_routes';
            aplicados++;
        }
        if (leg) {
            c.distance_from_prev_meters = _trkPlanHasNumber(leg.distance_meters) ? Number(leg.distance_meters) : c.distance_from_prev_meters;
            c.duration_seconds = _trkPlanHasNumber(leg.duration_seconds) ? Number(leg.duration_seconds) : c.duration_seconds;
            c.duration_in_traffic_seconds = _trkPlanHasNumber(leg.duration_in_traffic_seconds) ? Number(leg.duration_in_traffic_seconds) : c.duration_in_traffic_seconds;
            const travel = travelMinutes(leg);
            c.travel_from_prev_minutes = travel !== null ? travel : c.travel_from_prev_minutes;
            c._plan_real = true;
            c._plan_travel_pending = false;
            c.travel_source = data?.source || 'google_routes';
            if (!p) aplicados++;
        }
    });
    _trk.planeacionRealResumen = data?.resumen || null;
    _trk.planeacionRealWarnings = Array.isArray(data?.warnings) ? data.warnings : [];
    _trk.planeacionRealWarningGroups = _trkWarningGroups(data);
    _trk.planeacionRealLegs = legs;
    _trk.planeacionRealSource = data?.source || '';
    return aplicados;
}

function _trkCreditosOrdenRutaActual() {
    return [...(_trk.creditosEnRuta || [])].sort((a, b) =>
        Number(a.orden_ruta || 0) - Number(b.orden_ruta || 0)
    );
}

async function _trkCalcularMetricasMapaPlaneacion() {
    const mapsOk = await _trkEsperarGoogleMapsDisponible();
    if (!mapsOk || !window.google?.maps?.DirectionsService) {
        throw new Error('Google Maps no esta disponible para calcular tramos del mapa.');
    }
    const creditos = _trkPlanCreditosCalculables(_trkCreditosOrdenRutaActual());
    if (!creditos.length) throw new Error('No hay creditos para calcular tramos.');
    if (creditos.length > 23) {
        throw new Error('La ruta supera el limite de tramos del mapa para fallback.');
    }

    const cedisPos = _trkCedisDestinoPosicion(_trkCedisDestinoSeleccionado());
    const puntos = [];
    for (const c of creditos) {
        const pos = await _trkResolverPosCredito(c);
        if (!pos) throw new Error('Hay creditos sin coordenadas para calcular tramos del mapa.');
        puntos.push({ c, pos });
    }
    if (!puntos.length || (!cedisPos && puntos.length < 2)) {
        throw new Error('No hay suficientes puntos para calcular tramos del mapa.');
    }

    const useCedis = Boolean(cedisPos);
    const origin = useCedis
        ? new google.maps.LatLng(cedisPos.lat, cedisPos.lng)
        : new google.maps.LatLng(puntos[0].pos.lat, puntos[0].pos.lng);
    const destination = useCedis
        ? new google.maps.LatLng(cedisPos.lat, cedisPos.lng)
        : new google.maps.LatLng(puntos[puntos.length - 1].pos.lat, puntos[puntos.length - 1].pos.lng);
    const waypoints = (useCedis ? puntos : puntos.slice(1, -1)).map(p => ({
        location: new google.maps.LatLng(p.pos.lat, p.pos.lng),
        stopover: true,
    }));

    const result = await new Promise((resolve, reject) => {
        new google.maps.DirectionsService().route({
            origin,
            destination,
            waypoints,
            travelMode: google.maps.TravelMode.DRIVING,
            drivingOptions: {
                departureTime: new Date(),
                trafficModel: google.maps.TrafficModel?.BEST_GUESS || 'bestguess',
            },
            provideRouteAlternatives: false,
        }, (res, status) => {
            if (status === 'OK' && res) resolve(res);
            else reject(new Error(`Google Maps no pudo calcular tramos (${status || 'sin estado'}).`));
        });
    });

    const legs = result.routes?.[0]?.legs || [];
    if (!legs.length) throw new Error('Google Maps no regreso tramos de recorrido.');

    let distanciaTotal = 0;
    let duracionTotal = 0;
    const porCredito = [];
    puntos.forEach((p, idx) => {
        const leg = useCedis ? legs[idx] : (idx === 0 ? null : legs[idx - 1]);
        const duration = leg?.duration_in_traffic?.value || leg?.duration?.value || 0;
        const distance = leg?.distance?.value || 0;
        distanciaTotal += distance;
        duracionTotal += duration;
        porCredito.push({
            id_credito: p.c.id_credito,
            id_detalle: p.c.id_detalle,
            travel_minutes: Math.max(0, Math.round(duration / 60)),
            duration_seconds: duration || null,
            duration_in_traffic_seconds: duration || null,
            distance_meters: distance || null,
        });
    });
    const finalLeg = useCedis ? legs[puntos.length] : null;
    if (finalLeg) {
        distanciaTotal += Number(finalLeg.distance?.value || 0);
        duracionTotal += Number(finalLeg.duration_in_traffic?.value || finalLeg.duration?.value || 0);
    }

    return {
        source: 'google_maps_route',
        resumen: {
            distancia_total_m: distanciaTotal,
            duracion_total_seg: duracionTotal,
            operacion_total_min: puntos.length * Number(_trkPlanConfig().operacion || 45),
        },
        porCredito,
    };
}

async function _trkAplicarFallbackPlaneacion(motivo = '', opts = {}) {
    _trkSyncTrasladoEstadosControl();
    const config = _trkPlanConfig();
    const creditos = _trkPlanCreditosCalculables();
    _trk.planeacionTrasladoEstados = config.trasladoEstados;

    let metricas = null;
    let source = 'local_fallback';
    const esPropuestaManual = !!opts.proponer;
    const warningGroups = _trkWarningGroups(opts.warningGroups || opts.warning_groups || []);
    const apiWarnings = Array.isArray(opts.warnings) ? opts.warnings.map(_trkWarningTexto).filter(Boolean) : [];
    let warnings = esPropuestaManual
        ? ['Se genero una propuesta operativa. Revisa los tiempos antes de guardar la auditoria.']
        : (apiWarnings.length ? apiWarnings : [
            motivo
                ? `Google Routes no estuvo disponible: ${motivo}`
                : 'Google Routes no estuvo disponible. Se genero una propuesta estimada.',
        ]);

    try {
        metricas = await _trkCalcularMetricasMapaPlaneacion();
        source = metricas.source;
        warnings.push('Se usaron los tramos calculados en el mapa para estimar traslados entre puntos.');
    } catch (mapErr) {
        warnings.push('No se pudo usar la ruta del mapa para tiempos/distancias. Se aplico la propuesta estimada anterior.');
    }

    const metricasPorCredito = new Map((metricas?.porCredito || []).map(m => [String(m.id_credito), m]));
    creditos.forEach(c => {
        const m = metricasPorCredito.get(String(c.id_credito || ''));
        if (m) {
            c._plan_real = true;
            c._plan_travel_pending = false;
            c.travel_source = source;
            c.travel_from_prev_minutes = m.travel_minutes;
            c.distance_from_prev_meters = m.distance_meters;
            c.duration_seconds = m.duration_seconds;
            c.duration_in_traffic_seconds = m.duration_in_traffic_seconds;
        } else {
            c._plan_real = false;
            c._plan_travel_pending = true;
            c.travel_source = 'local_fallback';
            c.travel_from_prev_minutes = 0;
            delete c.distance_from_prev_meters;
            delete c.duration_seconds;
            delete c.duration_in_traffic_seconds;
        }
    });

    _trk.planeacionRealResumen = metricas?.resumen || {
        distancia_total_m: 0,
        duracion_total_seg: 0,
        operacion_total_min: creditos.length * Number(config.operacion || 45),
    };
    _trk.planeacionRealWarnings = warnings;
    _trk.planeacionRealWarningGroups = warningGroups;
    _trk.planeacionRealLegs = [];
    _trk.planeacionRealSource = source;

    const days = _trkPlanNormalizarFechasPolitica(_trkPlanApplyCascade(_trkPlanSeedDays(creditos, config), config));
    _trkPlanApplyDaysToRoute(days);
    _trkRenderListaCreditos();
    _trkSyncTrasladoEstadosControl();
    _trkRenderizarMapa();
    _trkMarcarCambio();

    if (opts.swal !== false) {
        const html = warningGroups.length
            ? `${_trkWarningGroupsHtml(warningGroups)}
                <div class="alert alert-warning text-start mt-2 mb-0" style="font-size:14px;line-height:1.5;">
                    Mientras tanto, revisa los tiempos antes de guardar la propuesta.
                </div>`
            : _trkWarningsListaHtml(warnings);
        Swal.fire({
            icon: 'warning',
            title: warningGroups.length
                ? _trkWarningsModalTitle(warningGroups, 'Faltan ubicaciones para calcular la ruta')
                : (source === 'google_maps_route' ? 'Tiempos estimados con advertencias' : 'Propuesta estimada generada'),
            html,
            confirmButtonText: 'Entendido',
        });
    }

    return {
        success: true,
        source,
        resumen: _trk.planeacionRealResumen,
        planeacion: creditos.map(c => ({
            id_detalle: Number(c.id_detalle || 0),
            arrival_minutes: _trkPlanOptionalNumber(c.arrival_minutes),
            departure_minutes: _trkPlanOptionalNumber(c.departure_minutes),
            operation_minutes: _trkPlanOptionalNumber(c.operation_minutes),
            travel_from_prev_minutes: _trkPlanOptionalNumber(c.travel_from_prev_minutes),
            day_index: Number(c.day_index || 0),
        })),
        warnings,
        warning_groups: warningGroups,
        fallback: true,
    };
}

function _trkRenderPlaneacionRealInfo() {
    const $box = $('#trkRouteRealSummary');
    if (!$box.length) return;
    const resumen = _trk.planeacionRealResumen || null;
    const warnings = Array.isArray(_trk.planeacionRealWarnings) ? _trk.planeacionRealWarnings : [];
    const warningGroups = _trkWarningGroupsActuales();
    const readyMsg = !!_trk.planeacionCreditosListosMensaje;
    if (!resumen && !warnings.length && !warningGroups.length && !readyMsg) {
        $box.addClass('d-none').empty();
        return;
    }
    const warningHtml = warningGroups.length
        ? _trkWarningGroupsHtml(warningGroups, { showActions: true })
        : _trkWarningsListaHtml(warnings);
    const kpis = resumen ? `
        <div class="trk-real-kpis">
            <span class="trk-real-kpi"><i class="fa-solid fa-road me-1"></i>${_trkChatEscapeHtml(_trkMetersLabel(resumen.distancia_total_m))}</span>
            <span class="trk-real-kpi"><i class="fa-solid fa-clock me-1"></i>${_trkChatEscapeHtml(_trkSecondsLabel(resumen.duracion_total_seg))}</span>
            <span class="trk-real-kpi"><i class="fa-solid fa-truck-ramp-box me-1"></i>${_trkChatEscapeHtml(_trkPlanOperacionResumenLabel(resumen))}</span>
            <span class="trk-real-kpi"><i class="fa-solid fa-warehouse me-1"></i>Origen: CEDIS destino</span>
            ${_trk.planeacionRealSource ? `<span class="trk-real-kpi"><i class="fa-solid fa-satellite-dish me-1"></i>${_trkChatEscapeHtml(_trkPlanSourceLabel(_trk.planeacionRealSource))}</span>` : ''}
        </div>` : '';
    const readyHtml = readyMsg ? `
        <div class="alert alert-info text-start mt-2 mb-0" style="font-size:14px;line-height:1.5;">
            <span class="badge bg-info me-1">Creditos listos</span>
            Da clic en <b>Generar plan</b> para recalcular la ruta con los creditos ya ubicados.
        </div>` : '';
    $box.removeClass('d-none').html(`
        <div class="fw-semibold"><i class="fa-solid fa-route me-1"></i>Calculo real de tiempos y distancias</div>
        ${kpis}
        ${warningHtml}
        ${readyHtml}
    `);
}

async function _trkSolicitarCalculoTiemposPlaneacion(persistir = false) {
    const idRuta = Number(_trk.idRutaEditando || 0);
    if (!idRuta) {
        throw new Error('Guarda la ruta primero para calcular tiempos reales.');
    }
    await _trkResolverCoordenadasFaltantesRuta({ persistir: true });
    const r = await trkFetch(`/TrackingRecoleccion/calcularTiemposPlaneacionRuta?id_ruta=${encodeURIComponent(idRuta)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(_trkPlanBodyCalculoReal(persistir)),
    });
    const data = _trkNormalizarCalculoTiemposRespuesta(r);
    if (!data.success) {
        throw new Error(data.mensaje || 'No se pudo calcular con Google Routes');
    }
    return data;
}

async function _trkCalcularTiemposPlaneacion(persistir = false) {
    const idRuta = Number(_trk.idRutaEditando || 0);
    if (!idRuta) {
        Swal.fire({ icon: 'info', title: 'Guarda la ruta primero', text: 'El calculo real necesita una ruta existente.', confirmButtonText: 'Aceptar' });
        return null;
    }
    _trk.planeacionCreditosListosMensaje = false;
    Swal.fire({
        title: persistir ? 'Guardando tiempos reales...' : 'Calculando tiempos reales...',
        text: 'Buscando ubicaciones faltantes en Google Maps y consultando distancia con Google Routes.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    try {
        const data = await _trkSolicitarCalculoTiemposPlaneacion(persistir);
        const aplicados = _trkAplicarPlaneacionReal(data);
        const warnings = Array.isArray(data.warnings) ? data.warnings : [];
        const warningGroups = _trkWarningGroups(data);
        if (!aplicados && _trkPlanCreditosCalculables().length) {
            return await _trkAplicarFallbackPlaneacion(
                'Google Routes no regreso tramos aplicables para los creditos listos.',
                { swal: true, warningGroups, warnings }
            );
        }
        _trkRenderListaCreditos();
        _trkRenderizarMapa();
        const hasBlockers = _trkWarningsBloqueantes(warnings, data);
        if (hasBlockers) {
            return await _trkAplicarFallbackPlaneacion(
                _trkWarningsModalTitle(data, 'Faltan ubicaciones para calcular la ruta'),
                { swal: true, warningGroups, warnings }
            );
        } else if (warningGroups.length || warnings.length) {
            Swal.fire({
                icon: 'warning',
                title: _trkWarningsModalTitle(data, 'Tiempos estimados con advertencias'),
                html: _trkWarningsModalHtml(data),
                confirmButtonText: 'Entendido',
            });
        } else {
            Swal.fire({
                icon: 'success',
                title: persistir ? 'Tiempos reales guardados' : 'Tiempos reales calculados',
                text: persistir ? 'La planeacion fue persistida en tracking.' : 'Revisa la propuesta antes de guardar.',
                timer: 1800,
                showConfirmButton: false,
            });
        }
        return data;
    } catch (err) {
        return await _trkAplicarFallbackPlaneacion(err?.message || 'Servicio no disponible.', { swal: true });
    }
}

function _trkValidarMotivoPlaneacion(valor, max = 300) {
    const motivo = String(valor || '').replace(/\s+/g, ' ').trim();
    const compacto = motivo.replace(/\s+/g, '').toLowerCase();
    const palabras = motivo.toLowerCase().match(/[a-z0-9áéíóúüñ]+/gi) || [];
    const caracteresUnicos = new Set(compacto.split('')).size;

    if (!motivo) return { ok: false, message: 'El motivo es obligatorio.', value: '' };
    if (motivo.length < 8) return { ok: false, message: 'Describe un motivo mas claro.', value: motivo };
    if (motivo.length > max) return { ok: false, message: `Maximo ${max} caracteres.`, value: motivo };
    if (!/[a-záéíóúüñ]/i.test(motivo)) return { ok: false, message: 'El motivo debe incluir texto descriptivo.', value: motivo };
    if (compacto.length >= 8 && /^(.)(\1)+$/i.test(compacto)) {
        return { ok: false, message: 'Evita caracteres repetidos sin descripcion.', value: motivo };
    }
    if (compacto.length >= 10 && /^(.{2,5})\1{3,}$/i.test(compacto)) {
        return { ok: false, message: 'Evita patrones repetidos como 101010 o textos duplicados.', value: motivo };
    }
    if (compacto.length >= 14 && caracteresUnicos <= 2) {
        return { ok: false, message: 'El motivo parece un patron repetitivo. Escribe una descripcion real.', value: motivo };
    }
    if (palabras.length >= 4 && new Set(palabras).size === 1) {
        return { ok: false, message: 'Evita repetir la misma palabra como motivo.', value: motivo };
    }
    return { ok: true, message: '', value: motivo };
}

function _trkPlaneacionStatusBadge(status) {
    const s = String(status || 'programado').toLowerCase();
    if (s === 'adelantado') return '<span class="badge bg-success">Adelantado</span>';
    if (s === 'reprogramado') return '<span class="badge bg-warning text-dark">Reprogramado</span>';
    if (s === 'omitido') return '<span class="badge bg-secondary">Omitido</span>';
    return '<span class="badge bg-label-primary">Programado</span>';
}

function _trkPlaneacionEventoLabel(tipo) {
    const labels = {
        reprogramacion_por_descanso: 'Descanso autorizado',
        adelanto_operativo: 'Adelanto operativo',
        reprogramacion_manual: 'Reprogramacion manual',
        reprogramacion_por_cliente: 'Cambio por cliente',
        reprogramacion_por_trafico: 'Cambio por trafico',
        reprogramacion_por_seguridad: 'Cambio por seguridad',
        reprogramacion_por_capacidad: 'Cambio por capacidad',
        distribucion_automatica: 'Distribucion guardada',
    };
    return labels[String(tipo || '').toLowerCase()] || 'Evento operativo';
}

function _trkRenderPlaneacionEventos() {
    const $events = $('#trkRouteEventList');
    if (!$events.length) return;
    const eventos = Array.isArray(_trk.planeacionEventos) ? _trk.planeacionEventos.slice(0, 4) : [];
    if (!eventos.length) {
        $events.empty();
        return;
    }
    $events.html(eventos.map(ev => {
        const usuario = ev.usuario_nombre || ev.id_usuario || 'Sparta';
        const motivo = ev.motivo || 'Sin motivo registrado';
        return `<div class="trk-route-event-item">
            <b>${_trkChatEscapeHtml(_trkPlaneacionEventoLabel(ev.tipo_evento))}</b>
            <span class="text-muted"> ${_trkChatEscapeHtml(ev.fecha_evento_fmt || ev.fecha_evento || '')}</span>
            <div>${_trkChatEscapeHtml(motivo)}</div>
            <div class="text-muted">Por: ${_trkChatEscapeHtml(usuario)}</div>
        </div>`;
    }).join(''));
}

function _trkRenderPlaneacionDias() {
    const $list = $('#trkRouteDayList');
    if (!$list.length) return;
    _trkSyncTrasladoEstadosControl();
    const totalRuta = (_trk.creditosEnRuta || []).length;
    const creditos = _trkPlanCreditosCalculables();
    const remanentes = _trkPlanCreditosRemanentes();
    if (!totalRuta) {
        $('#trkRouteDayCount').text('0 dias');
        $list.html('<div class="text-center text-muted small py-2">Agrega creditos para generar una propuesta por dia.</div>');
        _trkRenderPlaneacionRealInfo();
        _trkRenderPlaneacionEventos();
        return;
    }
    if (!creditos.length) {
        $('#trkRouteDayCount').text('0 listos');
        $list.html(`<div class="alert alert-info small mb-0">
            <b>Sin creditos listos para planear.</b><br>
            ${remanentes.length} credito${remanentes.length === 1 ? '' : 's'} requieren pin o coordenadas antes de entrar al calculo.
        </div>`);
        _trkRenderPlaneacionRealInfo();
        _trkRenderPlaneacionEventos();
        return;
    }
    const config = _trkPlanConfig();
    const days = _trkPlanNormalizarFechasPolitica(_trkPlanBuildDaysFromCurrent(creditos));
    _trkPlanApplyDaysToRoute(days);
    const remTxt = remanentes.length ? ` / ${remanentes.length} rem.` : '';
    $('#trkRouteDayCount').text(`${creditos.length} listos${remTxt}`);
    $list.html(days.map((day, dayIndex) => {
        const items = day.stops || [];
        const policy = _trkPlanPoliticaFechaDia(days, dayIndex);
        const hasWarning = items.some(c => c._plan_warning || Number(c.departure_minutes || 0) > config.fin);
        const totalTraslado = items.reduce((acc, c) => acc + Number(c.travel_from_prev_minutes || 0), 0);
        const totalOperacion = items.reduce((acc, c) => acc + Number(c.operation_minutes || config.operacion), 0);
        const inicioRutaHtml = dayIndex === 0 && items.length ? _trkPlanCedisInicioHtml(items[0]) : '';
        const itemsHtml = items.map((c, i) => {
            const estado = _trkPlanEstado(c);
            const municipio = _trkPlanMunicipio(c);
            const puedeEditar = !_trkRutaEstaCancelada();
            const llegada = _trkPlanHasNumber(c.arrival_minutes) ? Number(c.arrival_minutes) : config.inicio;
            const salida = _trkPlanHasNumber(c.departure_minutes) ? Number(c.departure_minutes) : llegada + config.operacion;
            const warning = salida > config.fin;
            const pinned = Number(c.pinned || 0) === 1;
            const travelPending = !!c._plan_travel_pending;
            const travelChip = travelPending
                ? '<i class="fa-solid fa-triangle-exclamation"></i>Sin calculo real'
                : `<i class="fa-solid fa-road"></i>${_trkChatEscapeHtml(c.travel_from_prev_minutes || 0)} min`;
            const distanceChip = _trkPlanHasNumber(c.distance_from_prev_meters)
                ? `<span class="trk-route-day-timechip"><i class="fa-solid fa-ruler-horizontal"></i>${_trkChatEscapeHtml(_trkMetersLabel(c.distance_from_prev_meters))}</span>`
                : '';
            return `<div class="trk-route-day-item">
                <span class="trk-route-day-num">${_trkChatEscapeHtml(c.orden_dia || (i + 1))}</span>
                <div style="min-width:0;">
                    <div class="trk-route-day-item-title">#${_trkChatEscapeHtml(c.id_credito || '-')} - ${_trkChatEscapeHtml(c.nombre_cliente || 'Sin cliente')}</div>
                    <div class="trk-route-day-item-meta">
                        ${_trkChatEscapeHtml(estado)} / ${_trkChatEscapeHtml(municipio)}
                        &middot; ${_trkPlaneacionStatusBadge(c.estatus_planeacion)}
                    </div>
                    <div class="trk-route-day-timebar">
                        <span class="trk-route-day-timechip${pinned ? ' is-pinned' : ''}">
                            <i class="fa-solid fa-thumbtack"></i>${pinned ? 'Fijada' : 'Auto'}
                        </span>
                        <span class="trk-route-day-timechip">
                            <i class="fa-solid fa-right-to-bracket"></i>Llega ${_trkPlanMinutesLabel(llegada)}
                        </span>
                        <span class="trk-route-day-timechip${warning ? ' is-warning' : ''}">
                            <i class="fa-solid fa-right-from-bracket"></i>Sale ${_trkPlanMinutesLabel(salida)}
                        </span>
                        <span class="trk-route-day-timechip">
                            <i class="fa-solid fa-truck-ramp-box"></i>${_trkChatEscapeHtml(c.operation_minutes || config.operacion)} min
                        </span>
                        <span class="trk-route-day-timechip${travelPending ? ' is-warning' : ''}">${travelChip}</span>
                        ${distanceChip}
                    </div>
                </div>
                <div class="trk-route-day-actions">
                    ${warning ? '<span class="badge bg-warning text-dark">Rebasa 19:00</span>' : ''}
                    ${puedeEditar
                        ? `<button type="button" class="btn btn-xs btn-label-primary btn-trk-plan-editar-hora"
                                data-credito="${_trkChatEscapeHtml(c.id_credito)}"
                                title="Editar horario y fijar parada">
                                <i class="fa-solid fa-clock"></i>
                           </button>`
                        : '<span class="badge bg-label-secondary">Lectura</span>'}
                </div>
            </div>`;
        }).join('');
        return `<div class="trk-route-day-card${hasWarning ? ' is-overloaded' : ''}">
            <div class="trk-route-day-card-head">
                <div>
                    <div class="trk-route-day-date">Dia ${dayIndex + 1}</div>
                    <input type="date" class="form-control form-control-sm trk-route-day-date-input mt-1"
                           data-day="${dayIndex}"
                           min="${_trkChatEscapeHtml(policy.min)}"
                           max="${_trkChatEscapeHtml(policy.max)}"
                           value="${_trkChatEscapeHtml(day.date || '')}"
                           ${policy.locked ? 'disabled' : ''}>
                    <div class="trk-route-day-item-meta">
                        ${items.length} parada${items.length === 1 ? '' : 's'} &middot;
                        traslado ${totalTraslado} min &middot; operacion ${totalOperacion} min
                    </div>
                    <div class="trk-route-day-item-meta">
                        ${policy.locked
                            ? 'Amarrado a la fecha de salida'
                            : `Rango permitido ${_trkFechaCorta(policy.min)} - ${_trkFechaCorta(policy.max)}`}
                    </div>
                </div>
                <span class="badge ${hasWarning ? 'bg-warning text-dark' : 'bg-label-success'}">${_trkChatEscapeHtml(_trkFechaCorta(day.date))}</span>
            </div>
            <div class="trk-route-day-items">${inicioRutaHtml}${itemsHtml}</div>
        </div>`;
    }).join(''));
    _trkRenderPlaneacionRealInfo();
    _trkRenderPlaneacionEventos();
}

function _trkPrepararAcomodadoPlaneacion(opts = {}) {
    const resetTravel = opts.resetTravel !== false;
    const render = opts.render !== false;
    const creditos = _trkPlanCreditosCalculables();
    const config = _trkPlanConfig();
    _trk.planeacionTrasladoEstados = config.trasladoEstados;

    if (resetTravel) {
        (_trk.creditosEnRuta || []).forEach(c => {
            const pinned = Number(c.pinned || 0) === 1;
            c._plan_real = false;
            c._plan_travel_pending = true;
            c.travel_source = 'pendiente_routes';
            c.travel_from_prev_minutes = 0;
            delete c.distance_from_prev_meters;
            delete c.duration_seconds;
            delete c.duration_in_traffic_seconds;
            if (!pinned) {
                delete c.arrival_minutes;
                delete c.departure_minutes;
                c.operation_minutes = config.operacion;
            }
        });
    }

    const days = _trkPlanNormalizarFechasPolitica(
        _trkPlanApplyCascade(_trkPlanSeedDays(creditos, config), config)
    );
    _trkPlanApplyDaysToRoute(days);

    if (render) {
        _trkRenderListaCreditos();
        _trkRenderizarMapa();
        _trkRenderPlaneacionDias();
    }
    _trkMarcarCambio();
    return days;
}

async function _trkSincronizarBorradorParaCalculoReal() {
    const estatus = String(_trk.estatusRuta || '').toLowerCase();
    const requiereBorrador = !Number(_trk.idRutaEditando || 0) || estatus === 'borrador';
    if (!requiereBorrador) return true;
    return await _trkForzarAutosaveBorrador();
}

async function _trkGenerarPlaneacionCompleta() {
    const creditos = _trk.creditosEnRuta || [];
    if (!creditos.length) {
        Swal.fire({ icon: 'info', title: 'Sin creditos', text: 'Agrega creditos antes de generar el plan.', confirmButtonText: 'Aceptar' });
        return null;
    }
    _trkSyncTrasladoEstadosControl();
    _trk.planeacionCreditosListosMensaje = false;

    Swal.fire({
        title: 'Generando plan completo...',
        text: 'Acomodando puntos, buscando ubicaciones y calculando la ruta con Google Routes.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        _trkPrepararAcomodadoPlaneacion({ resetTravel: true, render: false });
        _trkSyncTrasladoEstadosControl();

        const sincronizado = await _trkSincronizarBorradorParaCalculoReal();
        if (!sincronizado || !Number(_trk.idRutaEditando || 0)) {
            return await _trkAplicarFallbackPlaneacion(
                'No se pudo sincronizar el borrador para consultar Google Routes.',
                { swal: true, proponer: true }
            );
        }

        const data = await _trkSolicitarCalculoTiemposPlaneacion(false);
        const aplicados = _trkAplicarPlaneacionReal(data);
        const warnings = Array.isArray(data.warnings) ? data.warnings : [];
        const warningGroups = _trkWarningGroups(data);
        if (!aplicados && _trkPlanCreditosCalculables().length) {
            return await _trkAplicarFallbackPlaneacion(
                'Google Routes no regreso tramos aplicables para los creditos listos.',
                { swal: true, warningGroups, warnings, proponer: true }
            );
        }
        _trkPlanCascadeCurrent();
        _trkRenderListaCreditos();
        _trkSyncTrasladoEstadosControl();
        _trkRenderizarMapa();
        _trkMarcarCambio();

        const hasBlockers = _trkWarningsBloqueantes(warnings, data);

        if (hasBlockers) {
            return await _trkAplicarFallbackPlaneacion(
                _trkWarningsModalTitle(data, 'Faltan ubicaciones para calcular la ruta'),
                { swal: true, warningGroups, warnings }
            );
        }

        if (warningGroups.length || warnings.length) {
            Swal.fire({
                icon: 'warning',
                title: _trkWarningsModalTitle(data, 'Plan generado con advertencias'),
                html: _trkWarningsModalHtml(data),
                confirmButtonText: 'Entendido',
            });
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Plan completo generado',
                text: 'La ruta quedo acomodada por dias con tiempos y distancias reales.',
                timer: 1800,
                showConfirmButton: false,
            });
        }

        return data;
    } catch (err) {
        return await _trkAplicarFallbackPlaneacion(err?.message || 'Servicio no disponible.', {
            swal: true,
            proponer: true,
        });
    }
}

async function _trkDistribuirPlaneacionLocal() {
    const creditos = _trk.creditosEnRuta || [];
    if (!creditos.length) {
        Swal.fire({ icon: 'info', title: 'Sin creditos', text: 'Agrega creditos antes de generar una propuesta.', confirmButtonText: 'Aceptar' });
        return;
    }

    Swal.fire({
        title: 'Generando propuesta...',
        text: 'Calculando tramos del mapa y distribucion por dia.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
    await _trkAplicarFallbackPlaneacion('Propuesta manual', { swal: true, proponer: true });
}

function _trkMergePlaneacionRuta(items) {
    const porDetalle = new Map((items || []).map(i => [String(i.id_detalle), i]));
    (_trk.creditosEnRuta || []).forEach(c => {
        const item = porDetalle.get(String(c.id_detalle || ''));
        if (!item) return;
        c.fecha_planeacion = item.fecha_recoleccion || c.fecha_planeacion || null;
        c.fecha_planeacion_fmt = item.fecha_recoleccion_fmt || c.fecha_planeacion_fmt || '';
        c.orden_dia = item.orden_dia || c.orden_dia || c.orden_ruta || 1;
        c.estatus_planeacion = item.estatus_planeacion || c.estatus_planeacion || 'programado';
        c.day_index = Number(item.day_index || 0);
        c.arrival_minutes = item.arrival_minutes !== null && item.arrival_minutes !== undefined ? Number(item.arrival_minutes) : c.arrival_minutes;
        c.departure_minutes = item.departure_minutes !== null && item.departure_minutes !== undefined ? Number(item.departure_minutes) : c.departure_minutes;
        c.travel_from_prev_minutes = item.travel_from_prev_minutes !== null && item.travel_from_prev_minutes !== undefined ? Number(item.travel_from_prev_minutes) : c.travel_from_prev_minutes;
        c.operation_minutes = item.operation_minutes !== null && item.operation_minutes !== undefined ? Number(item.operation_minutes) : c.operation_minutes;
        if (item.travel_from_prev_minutes !== null && item.travel_from_prev_minutes !== undefined) {
            c._plan_real = true;
            c._plan_travel_pending = false;
            c.travel_source = item.travel_source || item.source || c.travel_source || 'google_routes';
        }
        c.pinned = Number(item.pinned || 0);
        c.edited = Number(item.edited || 0);
    });
}

async function _trkCargarPlaneacionRuta(idRuta) {
    if (!idRuta) {
        _trkRenderPlaneacionDias();
        return;
    }
    _trk.planeacionLoading = true;
    try {
        const r = await trkFetch(`/TrackingRecoleccion/obtenerPlaneacionRuta?id_ruta=${encodeURIComponent(idRuta)}`);
        if (!r.success) return;
        _trk.planeacionRuta = Array.isArray(r.items) ? r.items : [];
        _trk.planeacionEventos = Array.isArray(r.eventos) ? r.eventos : [];
        _trkMergePlaneacionRuta(_trk.planeacionRuta);
        _trkPlanCascadeCurrent();
    } catch (_) {
        // No bloquear apertura de ruta por el historial de planeacion.
    } finally {
        _trk.planeacionLoading = false;
        _trkRenderPlaneacionDias();
    }
}

function _trkIdDetallePlaneacion(c) {
    return Number(c?.id_detalle || c?.detalle_id || c?.id_ruta_detalle || c?.id_asigna_detalle || 0);
}

function _trkBuildPlaneacionAuditItems() {
    return _trkPlanCreditosCalculables()
        .filter(c => _trkIdDetallePlaneacion(c) > 0 || Number(c?.id_credito || 0) > 0)
        .map(c => ({
            id_detalle: _trkIdDetallePlaneacion(c) || null,
            id_credito: Number(c.id_credito || 0) || null,
            fecha_recoleccion: _trkPlaneacionFechaCredito(c),
            orden_dia: Number(c.orden_dia || c.orden_ruta || 1),
            estatus_planeacion: c.estatus_planeacion || 'programado',
            day_index: Number(c.day_index || 0),
            arrival_minutes: _trkPlanOptionalNumber(c.arrival_minutes),
            departure_minutes: _trkPlanOptionalNumber(c.departure_minutes),
            travel_from_prev_minutes: _trkPlanOptionalNumber(c.travel_from_prev_minutes),
            operation_minutes: _trkPlanOptionalNumber(c.operation_minutes),
            pinned: Number(c.pinned || 0),
            edited: Number(c.edited || 0),
        }));
}

async function _trkAsegurarPuntosAuditablesPlaneacion() {
    let items = _trkBuildPlaneacionAuditItems();
    if (items.length) return items;

    const faltantesConDireccion = (_trk.creditosEnRuta || [])
        .filter(c => _trkIdDetallePlaneacion(c) > 0)
        .filter(c => !_trkCreditoTieneCoordenadasValidas(c))
        .filter(c => _trkDireccionBusquedaCredito(c));

    if (!faltantesConDireccion.length) return items;

    Swal.fire({
        title: 'Preparando ubicaciones...',
        text: 'Buscando coordenadas con Google Maps antes de guardar la auditoria.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });
    try {
        await _trkResolverCoordenadasFaltantesRuta({ persistir: true });
        _trkPlanCascadeCurrent();
        items = _trkBuildPlaneacionAuditItems();
    } catch (err) {
        console.warn('[Tracking Recoleccion] No se pudieron preparar coordenadas para auditoria', err);
    } finally {
        if (Swal.isLoading()) Swal.close();
    }
    return items;
}

function _trkMostrarSinPuntosAuditables() {
    const conDetalle = (_trk.creditosEnRuta || []).filter(c => _trkIdDetallePlaneacion(c) > 0);
    const sinCoords = conDetalle.filter(c => !_trkCreditoTieneCoordenadasValidas(c));
    if (conDetalle.length && sinCoords.length === conDetalle.length) {
        Swal.fire({
            icon: 'warning',
            title: 'Faltan coordenadas confirmadas',
            html: `<div class="text-start">
                <p class="mb-2">La ruta tiene creditos guardados, pero ninguno tiene coordenadas validas para auditar la planeacion.</p>
                <p class="mb-0">Usa el puntero para confirmar ubicaciones o genera el plan para intentar resolverlas con Google Maps.</p>
            </div>`,
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    Swal.fire({
        icon: 'warning',
        title: 'Sin puntos auditables',
        text: conDetalle.length
            ? 'No hay puntos con ubicacion valida para auditar.'
            : 'No hay puntos guardados para auditar. Guarda o actualiza la ruta antes de guardar la planeacion.',
        confirmButtonText: 'Aceptar'
    });
}

async function _trkGuardarPlaneacionRuta() {
    const idRuta = Number(_trk.idRutaEditando || 0);
    if (!idRuta) {
        Swal.fire({ icon: 'info', title: 'Guarda la ruta primero', text: 'La auditoria por dia se guarda cuando la ruta ya existe.', confirmButtonText: 'Aceptar' });
        return;
    }
    _trkPlanCascadeCurrent();
    let items = await _trkAsegurarPuntosAuditablesPlaneacion();
    if (!items.length) {
        console.warn('[Tracking Recoleccion] Planeacion sin puntos auditables persistidos; se intentara guardar de todos modos.');
    }
    const motivoRes = await _trkSwalConFocoModalRuta({
        icon: 'question',
        title: 'Guardar planeacion?',
        html: `<div class="text-start">
            <label for="swalPlanMotivoDistribucion" class="form-label fw-semibold">Motivo de la distribucion</label>
            <textarea id="swalPlanMotivoDistribucion" class="form-control" rows="4" maxlength="300"
                placeholder="Ej. Distribucion operativa por descanso, capacidad o cambio de zona.">Distribucion operativa de paradas por dia.</textarea>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="text-muted small">Evita texto repetitivo o sin descripcion.</span>
                <span class="text-muted small"><span id="swalPlanMotivoDistribucionCount">0</span>/300</span>
            </div>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Guardar planeacion',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488',
        focusConfirm: false,
        didOpen: () => {
            const textarea = document.getElementById('swalPlanMotivoDistribucion');
            const counter = document.getElementById('swalPlanMotivoDistribucionCount');
            if (textarea) {
                textarea.disabled = false;
                textarea.readOnly = false;
                textarea.style.pointerEvents = 'auto';
            }
            const syncCounter = () => {
                if (counter && textarea) counter.textContent = String(textarea.value.length);
            };
            textarea?.addEventListener('input', syncCounter);
            syncCounter();
            setTimeout(() => {
                textarea?.focus();
                textarea?.setSelectionRange(textarea.value.length, textarea.value.length);
            }, 50);
        },
        preConfirm: () => {
            const validacion = _trkValidarMotivoPlaneacion(document.getElementById('swalPlanMotivoDistribucion')?.value, 300);
            if (!validacion.ok) {
                Swal.showValidationMessage(validacion.message);
                return false;
            }
            return validacion.value;
        },
    });
    if (!motivoRes.isConfirmed) return;

    Swal.fire({
        title: 'Calculando y guardando planeacion...',
        text: 'Buscando ubicaciones faltantes en Google Maps antes de guardar tiempos reales.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });
    try {
        let calculo = null;
        try {
            calculo = await _trkSolicitarCalculoTiemposPlaneacion(true);
            const aplicados = _trkAplicarPlaneacionReal(calculo);
            const warningGroups = _trkWarningGroups(calculo);
            if ((!aplicados && _trkPlanCreditosCalculables().length) || _trkWarningsBloqueantes(calculo.warnings || [], calculo)) {
                calculo = await _trkAplicarFallbackPlaneacion(
                    _trkWarningsModalTitle(calculo, 'Calculo con advertencias.'),
                    { swal: false, warningGroups, warnings: calculo.warnings || [] }
                );
            }
        } catch (calcErr) {
            calculo = await _trkAplicarFallbackPlaneacion(calcErr?.message || 'Servicio no disponible.', { swal: false });
        }
        _trkPlanCascadeCurrent();
        _trkRenderListaCreditos();
        _trkRenderizarMapa();
        if (!calculo?.fallback && _trkWarningsBloqueantes(calculo.warnings || [], calculo)) {
            Swal.fire({
                icon: 'warning',
                title: _trkWarningsModalTitle(calculo, 'No se guardaron tiempos'),
                html: _trkWarningsModalHtml(calculo) || 'Revisa CEDIS y coordenadas antes de guardar.',
                confirmButtonText: 'Entendido',
            });
            return;
        }
        items = _trkBuildPlaneacionAuditItems();
        if (!items.length) {
            console.warn('[Tracking Recoleccion] Planeacion generada sin items auditables; se enviara sin bloquear al gestor.');
        }
        const r = await trkFetch('/TrackingRecoleccion/guardarPlaneacionRuta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_ruta: idRuta, items, motivo: motivoRes.value }),
        });
        if (!r.success) {
            Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: r.message || r.mensaje || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        await _trkCargarPlaneacionRuta(idRuta);
        Swal.fire({
            icon: calculo?.fallback ? 'warning' : 'success',
            title: calculo?.fallback ? 'Planeacion guardada con fallback' : 'Planeacion guardada',
            text: calculo?.fallback
                ? 'Google Routes no estuvo disponible; se guardo la auditoria con tiempos estimados.'
                : (r.message || 'Auditoria registrada correctamente.'),
            timer: 2600,
            showConfirmButton: false
        });
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'No se pudo guardar',
            text: err?.message || 'No se pudo guardar la planeacion.',
            confirmButtonText: 'Aceptar',
        });
    }
}

function _trkCerrarEditorHorarioPlaneacion() {
    _trk.planeacionCreditoEditando = null;
    $('#trkPlanHorarioOverlay').addClass('d-none').attr('aria-hidden', 'true');
    $('#trkPlanHorarioError').addClass('d-none').text('');
}

function _trkMostrarErrorHorarioPlaneacion(message) {
    $('#trkPlanHorarioError').removeClass('d-none').text(message || 'Revisa los datos capturados.');
}

function _trkAbrirEditarHorarioPlaneacion(c) {
    if (!c) return;
    const config = _trkPlanConfig();
    const llegadaActual = _trkPlanHasNumber(c.arrival_minutes) ? Number(c.arrival_minutes) : config.inicio;
    const salidaActual = _trkPlanHasNumber(c.departure_minutes) ? Number(c.departure_minutes) : llegadaActual + (Number(c.operation_minutes) || config.operacion);
    _trk.planeacionCreditoEditando = c;
    $('#trkPlanHorarioCreditoLabel').text(`#${c.id_credito || '-'} - ${c.nombre_cliente || 'Sin cliente'}`);
    $('#trkPlanHorarioTipo').val(c.estatus_planeacion === 'adelantado' ? 'adelanto_operativo' : 'reprogramacion_manual');
    $('#trkPlanHorarioFecha')
        .attr('min', _trkFechaMinimaHorarioPlaneacion())
        .val(_trkPlaneacionFechaCredito(c));
    $('#trkPlanHorarioLlegada').val(_trkMinutesToHora(llegadaActual));
    $('#trkPlanHorarioSalida').val(_trkMinutesToHora(salidaActual));
    $('#trkPlanHorarioMotivo').val(c._plan_motivo || '').trigger('input');
    $('#trkPlanHorarioError').addClass('d-none').text('');
    $('#trkPlanHorarioOverlay').removeClass('d-none').attr('aria-hidden', 'false');
    setTimeout(() => document.getElementById('trkPlanHorarioMotivo')?.focus(), 60);
}

function _trkGuardarEditorHorarioPlaneacion() {
    const c = _trk.planeacionCreditoEditando;
    if (!c) {
        _trkCerrarEditorHorarioPlaneacion();
        return;
    }
    const tipo = $('#trkPlanHorarioTipo').val() || 'reprogramacion_manual';
    const fecha = $('#trkPlanHorarioFecha').val() || '';
    const llegada = _trkPlanTimeToMinutes($('#trkPlanHorarioLlegada').val(), NaN);
    const salida = _trkPlanTimeToMinutes($('#trkPlanHorarioSalida').val(), NaN);
    const motivoValidado = _trkValidarMotivoPlaneacion($('#trkPlanHorarioMotivo').val(), 300);
    if (!fecha) return _trkMostrarErrorHorarioPlaneacion('La fecha es obligatoria.');
    if (_trkCompararFecha(fecha, _trkFechaMinimaHorarioPlaneacion()) < 0) {
        return _trkMostrarErrorHorarioPlaneacion('Solo puedes adelantar el punto un dia antes de la fecha de inicio de la ruta.');
    }
    if (!Number.isFinite(llegada) || !Number.isFinite(salida)) {
        return _trkMostrarErrorHorarioPlaneacion('Captura hora de llegada y salida.');
    }
    if (salida <= llegada) {
        return _trkMostrarErrorHorarioPlaneacion('La salida debe ser posterior a la llegada.');
    }
    if (!motivoValidado.ok) {
        return _trkMostrarErrorHorarioPlaneacion(motivoValidado.message);
    }
    c.fecha_planeacion = fecha;
    c.day_index = _trkPlanDayIndexPorFecha(fecha);
    c.arrival_minutes = llegada;
    c.departure_minutes = salida;
    c.operation_minutes = salida - llegada;
    c.pinned = 1;
    c.edited = 1;
    c.estatus_planeacion = tipo === 'adelanto_operativo' ? 'adelantado' : 'reprogramado';
    c._plan_motivo = motivoValidado.value;
    _trkCerrarEditorHorarioPlaneacion();
    _trkPlanCascadeCurrent();
    _trkRenderListaCreditos();
    _trkRenderizarMapa();
    _trkMarcarCambio();
    Swal.fire({
        icon: 'success',
        title: 'Horario fijado',
        text: 'La parada quedo fijada y las posteriores fueron recalculadas. Guarda la planeacion para auditar.',
        timer: 3500,
        showConfirmButton: true,
        confirmButtonText: 'Entendido',
    });
}

function _trkOportunidadesRutaIdActual() {
    return Number(_trk.idRutaEditando || 0);
}

function _trkOportunidadNivelBadge(nivel) {
    const n = String(nivel || '').toLowerCase();
    if (n === 'recomendado') return '<span class="badge bg-success">Recomendado</span>';
    if (n === 'advertencia') return '<span class="badge bg-warning text-dark">Advertencia</span>';
    if (n === 'no_recomendado') return '<span class="badge bg-danger">No recomendado</span>';
    return '<span class="badge bg-secondary">Sin nivel</span>';
}

function _trkNumFmt(v, dec = 1) {
    const n = Number(v);
    if (!Number.isFinite(n)) return null;
    return n.toLocaleString('es-MX', { maximumFractionDigits: dec, minimumFractionDigits: 0 });
}

function _trkOportunidadesNormalizarRespuesta(r) {
    const data = r?.datos || r?.data || r || {};
    const resumen = data.resumen || {};
    const candidatos = Array.isArray(data.candidatos)
        ? data.candidatos
        : (Array.isArray(data.creditos) ? data.creditos : []);
    return { ...data, resumen, candidatos };
}

function _trkOportunidadesFiltradas() {
    const data = _trk.oportunidadesRuta || {};
    const idsEnRuta = _trkIdsCreditosEnRutaSet();
    const nivel = String(_trk.oportunidadesFiltroNivel || '').toLowerCase();
    return (data.candidatos || []).filter(c => {
        if (idsEnRuta.has(String(c.id_credito))) return false;
        if (nivel && String(c.nivel || '').toLowerCase() !== nivel) return false;
        return true;
    });
}

async function _trkCargarOportunidadesRuta(force = false) {
    const idRuta = _trkOportunidadesRutaIdActual();
    if (!idRuta) {
        _trk.oportunidadesRuta = null;
        _trk.oportunidadesRutaId = null;
        _trkRenderOportunidadesRuta();
        return;
    }
    if (!force && _trk.oportunidadesRutaId === idRuta && _trk.oportunidadesRuta && !_trk.oportunidadesLoading) {
        _trkRenderOportunidadesRuta();
        return;
    }
    if (_trk.oportunidadesLoading) return;

    const radio = Math.max(1, Math.min(80, Number($('#trkOppRadioKm').val() || 10)));
    const limit = Math.max(5, Math.min(100, Number($('#trkOppLimit').val() || 30)));
    $('#trkOppRadioKm').val(radio);
    $('#trkOppLimit').val(limit);

    const seq = ++_trk.oportunidadesSeq;
    _trk.oportunidadesLoading = true;
    _trk.oportunidadesRutaId = idRuta;
    _trkRenderOportunidadesRuta();

    const qs = new URLSearchParams({
        id_ruta: String(idRuta),
        radio_km: String(radio),
        limit: String(limit),
        usar_ubicacion_actual: 'true',
        incluir_detour: 'true',
        solo_con_coordenadas: 'true',
    });

    try {
        const r = await trkFetch(`/TrackingRecoleccion/trackingCreditosSobreRuta?${qs.toString()}`);
        if (seq !== _trk.oportunidadesSeq) return;
        if (!r.success) {
            _trk.oportunidadesRuta = {
                success: false,
                error: r.mensaje || r.message || r.detail || 'No se pudieron obtener sugerencias sobre la ruta.',
                codigo_http: r.codigo_http || null,
                candidatos: [],
                resumen: {},
            };
            return;
        }
        _trk.oportunidadesRuta = _trkOportunidadesNormalizarRespuesta(r);
        _trk.oportunidadesRuta.success = true;
    } catch {
        if (seq !== _trk.oportunidadesSeq) return;
        _trk.oportunidadesRuta = {
            success: false,
            error: 'Error de conexión al consultar créditos sobre la ruta.',
            candidatos: [],
            resumen: {},
        };
    } finally {
        if (seq === _trk.oportunidadesSeq) {
            _trk.oportunidadesLoading = false;
            _trkRenderOportunidadesRuta();
        }
    }
}

function _trkRenderOportunidadesResumen(data) {
    const resumen = data?.resumen || {};
    const capTotal = resumen.capacidad_total ?? null;
    const planeada = resumen.carga_planeada ?? resumen.capacidad_planeada ?? null;
    const disponible = resumen.capacidad_disponible_planeada ?? resumen.capacidad_disponible ?? null;
    const transportistaRaw = resumen.transportista || null;
    const transportista = (transportistaRaw && typeof transportistaRaw === 'object')
        ? (transportistaRaw.nombre_transportista || transportistaRaw.nombre || 'Sin transportista')
        : (transportistaRaw || resumen.nombre_transportista || 'Sin transportista');
    const cedis = resumen.cedis_destino?.nombre_agencia || resumen.cedis_destino?.nombre || 'Sin CEDIS destino';
    const warnings = Array.isArray(resumen.warnings) ? resumen.warnings : [];
    $('#trkRouteOppSummary').removeClass('d-none').html(`
        <div class="mini-kpi"><span>Transportista</span><b title="${_trkChatEscapeHtml(transportista)}">${_trkChatEscapeHtml(transportista)}</b></div>
        <div class="mini-kpi"><span>Capacidad</span><b>${capTotal ? `${_trkChatEscapeHtml(planeada ?? 0)} / ${_trkChatEscapeHtml(capTotal)}` : 'Sin configurar'}</b></div>
        <div class="mini-kpi"><span>Disponible</span><b>${capTotal ? _trkChatEscapeHtml(disponible ?? 0) : '-'}</b></div>
        <div class="mini-kpi" style="grid-column:1/-1;"><span>Destino</span><b>${_trkChatEscapeHtml(cedis)}</b></div>
        ${warnings.length ? `<div class="alert alert-warning py-1 px-2 small mb-0" style="grid-column:1/-1;">${warnings.map(w => _trkChatEscapeHtml(w)).join('<br>')}</div>` : ''}
    `);
}

function _trkRenderOportunidadCard(c) {
    const nivel = String(c.nivel || '').toLowerCase() || 'advertencia';
    const cliente = c.cliente || c.nombre_cliente || 'Sin cliente';
    const moto = c.moto || c.modelo || [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ') || 'Sin modelo';
    const vin = c.vin || c.bin || c.moto_no_serie || '';
    const estado = _trkEstadoMayus(c.estado, c.municipio);
    const municipio = _trkMunicipioMayus(c.municipio, c.estado);
    const distancia = _trkNumFmt(c.distancia_corredor_km, 1);
    const detourKm = _trkNumFmt(c.detour_km, 1);
    const detourMin = _trkNumFmt(c.detour_min, 0);
    const rawScore = c.score ?? c.prioridad_score;
    const score = Number.isFinite(Number(rawScore)) ? Number(rawScore) : null;
    const posicion = Number(c.posicion_sugerida || 0);
    const razones = Array.isArray(c.motivos) ? c.motivos : (Array.isArray(c.razones) ? c.razones : []);
    const warnings = Array.isArray(c.warnings) ? c.warnings : [];
    const puedeAgregar = _trkRutaPermiteAgregarCredito({ estatus_ruta: _trk.estatusRuta || 'enviada' }, c);
    const btn = puedeAgregar
        ? `<button type="button" class="btn btn-xs btn-primary btn-trk-add-opportunity" data-id="${_trkChatEscapeHtml(c.id_credito || '')}">
                <i class="fa-solid fa-plus me-1"></i>Agregar
           </button>`
        : '<span class="badge bg-label-secondary">No editable</span>';

    return `<div class="trk-route-opportunity is-${_trkChatEscapeHtml(nivel)}">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div style="min-width:0;">
                <div class="trk-route-opportunity-title">#${_trkChatEscapeHtml(c.id_credito || '-')} - ${_trkChatEscapeHtml(cliente)}</div>
                <div class="trk-route-opportunity-meta">
                    <i class="fa-solid fa-motorcycle me-1" style="color:var(--track-color);"></i>${_trkChatEscapeHtml(moto)}
                    ${vin ? `<span class="mx-1">|</span>VIN: ${_trkChatEscapeHtml(vin)}` : ''}
                </div>
            </div>
            ${_trkOportunidadNivelBadge(nivel)}
        </div>
        <div class="trk-route-opportunity-badges">
            ${score !== null ? `<span class="badge bg-label-primary">Score ${_trkChatEscapeHtml(score)}</span>` : ''}
            ${distancia !== null ? `<span class="badge bg-label-info">${_trkChatEscapeHtml(distancia)} km corredor</span>` : ''}
            ${detourKm !== null ? `<span class="badge bg-label-secondary">Desvio ${_trkChatEscapeHtml(detourKm)} km</span>` : ''}
            ${detourMin !== null ? `<span class="badge bg-label-secondary">${_trkChatEscapeHtml(detourMin)} min</span>` : ''}
            ${posicion ? `<span class="badge bg-label-success">Pos. sugerida ${_trkChatEscapeHtml(posicion)}</span>` : ''}
        </div>
        <div>${_trkRenderLocationBadges(estado, municipio)}</div>
        ${c.direccion ? `<div class="trk-route-opportunity-meta"><i class="fa-solid fa-location-dot me-1"></i>${_trkChatEscapeHtml(c.direccion)}</div>` : ''}
        ${razones.length ? `<div class="trk-route-opportunity-reasons">${razones.slice(0, 3).map(r => `<div>${_trkChatEscapeHtml(r)}</div>`).join('')}</div>` : ''}
        ${warnings.length ? `<div class="alert alert-warning py-1 px-2 small mb-0">${warnings.slice(0, 2).map(w => _trkChatEscapeHtml(w)).join('<br>')}</div>` : ''}
        <div class="trk-route-opportunity-actions">
            <span class="small text-muted">${posicion ? `Idealmente insertar en posicion ${_trkChatEscapeHtml(posicion)}` : 'Sin posicion sugerida'}</span>
            ${btn}
        </div>
    </div>`;
}

function _trkRenderOportunidadesRuta() {
    const $list = $('#trkRouteOppList');
    if (!$list.length) return;
    const idRuta = _trkOportunidadesRutaIdActual();
    if (!idRuta) {
        $('#trkRouteOppCount').text('0');
        $('#trkRouteOppSummary').addClass('d-none').empty();
        $list.html('<div class="text-center text-muted small py-2">Guarda o abre una ruta para consultar sugerencias sobre el recorrido.</div>');
        return;
    }
    if (_trk.oportunidadesLoading) {
        $('#trkRouteOppCount').text('...');
        $('#trkRouteOppSummary').addClass('d-none').empty();
        $list.html('<div class="text-center text-muted small py-3"><span class="spinner-border spinner-border-sm me-2"></span>Consultando créditos sobre la ruta...</div>');
        return;
    }
    const data = _trk.oportunidadesRuta;
    if (!data || _trk.oportunidadesRutaId !== idRuta) {
        $('#trkRouteOppCount').text('0');
        $('#trkRouteOppSummary').addClass('d-none').empty();
        $list.html('<div class="text-center text-muted small py-2">Pulsa actualizar para consultar oportunidades sobre esta ruta.</div>');
        return;
    }
    if (data.success === false) {
        $('#trkRouteOppCount').text('0');
        $('#trkRouteOppSummary').addClass('d-none').empty();
        $list.html(`<div class="alert alert-warning small mb-0">
            ${_trkChatEscapeHtml(data.error || 'No se pudieron cargar las sugerencias.')}
        </div>`);
        return;
    }
    _trkRenderOportunidadesResumen(data);
    const candidatos = _trkOportunidadesFiltradas();
    $('#trkRouteOppCount').text(candidatos.length);
    if (!candidatos.length) {
        $list.html('<div class="text-center text-muted small py-2">Sin candidatos con los filtros actuales.</div>');
        return;
    }
    $list.html(candidatos.map(_trkRenderOportunidadCard).join(''));
}

async function _trkAgregarOportunidadARuta(candidato) {
    const idRuta = _trkOportunidadesRutaIdActual();
    const idCredito = Number(candidato?.id_credito || 0);
    if (!idRuta || !idCredito) return;
    const posicion = Number(candidato.posicion_sugerida || 0);
    const nivel = String(candidato.nivel || '').toLowerCase();
    const res = await Swal.fire({
        icon: nivel === 'no_recomendado' ? 'warning' : 'question',
        title: 'Agregar crédito sugerido?',
        html: `<div class="text-start small">
            <div><b>Crédito:</b> #${_trkChatEscapeHtml(idCredito)} - ${_trkChatEscapeHtml(candidato.cliente || candidato.nombre_cliente || '')}</div>
            <div><b>Nivel:</b> ${_trkOportunidadNivelBadge(nivel)}</div>
            ${posicion ? `<div><b>Posición sugerida:</b> ${_trkChatEscapeHtml(posicion)}</div>` : ''}
            <div class="alert alert-info py-2 mt-2 mb-0">
                La API actual de asignacion lo agregara al final. Cuando exista el endpoint inteligente se insertara en la posicion sugerida.
            </div>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488',
    });
    if (!res.isConfirmed) return;

    Swal.fire({
        title: 'Agregando crédito...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const r = await trkFetch('/TrackingRecoleccion/agregarCreditoRuta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_ruta: idRuta, id_credito: idCredito }),
        });
        if (!r.success) {
            Swal.fire({ icon: 'error', title: 'No se pudo agregar', text: r.mensaje || r.message || 'Intenta nuevamente.', confirmButtonText: 'Aceptar' });
            return;
        }
        _trk.oportunidadesRuta = null;
        _trk.oportunidadesRutaId = null;
        _trk.cargadoRutas = false;
        _trk.cargadoBorradores = false;
        _trk.cargadoOperacion = false;
        await Promise.allSettled([
            _trkCargarCreditosPaso2(true),
            _trkCargarRutas(true),
            _trkCargarBorradores(true),
            _trkCargarOperacionTransportistas(true),
        ]);
        await Swal.fire({ icon: 'success', title: 'Crédito agregado', text: r.message || 'Se agregó correctamente a la ruta.', timer: 1400, showConfirmButton: false });
        _trkCargarRutaEnModal(idRuta, _trk.soloLectura);
    } catch {
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo agregar el crédito a la ruta.', confirmButtonText: 'Aceptar' });
    }
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
        entregada_cedis: 'Entregada en CEDIS',
        completado:  'Recolectada', // alias por compatibilidad
        incidencia:  'Incidencia',
    };
    const ICONS = {
        pendiente:   'fa-circle-dot',
        en_camino:   'fa-motorcycle',
        en_sitio:    'fa-location-dot',
        recolectada: 'fa-circle-check',
        entregada_cedis: 'fa-warehouse',
        completado:  'fa-circle-check', // alias
        incidencia:  'fa-triangle-exclamation',
    };

    let html = '';
    creditos.forEach(c => {
        const est     = c.estatus_recoleccion || 'pendiente';
        const esAct   = puntoAct && puntoAct.id_detalle === c.id_detalle;
        const esDone  = est === 'recolectada' || est === 'completado' || est === 'entregada_cedis';
        const stepCls = esDone ? 'done' : (esAct ? 'activo' : (est === 'en_sitio' ? 'en_sitio' : (est === 'incidencia' ? 'incidencia' : '')));
        const icon    = ICONS[est] || ICONS.pendiente;
        const label   = LABELS[est] || est;

        // Linea 1: Credito #XXXXX  -  MARCA MODELO | Estado, Municipio
        const moto    = [c.moto_marca, c.moto_modelo].filter(Boolean).join(' ');
        const lugar   = [c.estado, c.municipio].filter(Boolean).join(', ');
        let linea1    = `Credito #${c.id_credito}`;
        if (moto)  linea1 += `  -  ${moto}`;
        if (lugar) linea1 += ` | ${lugar}`;

        // Linea 2: nombre del cliente
        const cliente = _trkChatEscapeHtml(c.nombre_cliente || '');
        const otpHtml = _trkOtpInlineHtml(c);
        console.debug('[Tracking OTP] render punto', {
            id_detalle: c.id_detalle || null,
            id_credito: c.id_credito || null,
            estatus_recoleccion: est,
            permite_otp: _trkPuntoPermiteOtp(c),
        });

        html += `<div class="trk-step ${stepCls}" data-id="${c.id_detalle}">
            <div class="trk-step-dot"><i class="fa-solid ${icon}" style="font-size:.45rem;"></i></div>
            <div class="trk-step-body">
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="trk-step-nombre" style="font-weight:600;">${_trkChatEscapeHtml(linea1)}</span>
                    <span class="trk-step-badge trk-badge-${est}">${label}</span>
                </div>
                ${cliente ? `<span class="trk-step-dir">${cliente}</span>` : ''}
                ${otpHtml}
            </div>
        </div>`;
    });
    document.getElementById('trkTimeline').innerHTML = html;
    _trkConsultarOtpsActivosDetalle(creditos);
}

// --- Aplicar cambios parciales (WS update) ---------------
function _trkRTAplicarChanges(changes) {
    if (!changes || !changes.length) return;
    const creditos = _trkRT.estado?.creditos || [];
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
    if (!_trkRT.estado) {
        _trkRenderListaCreditos();
        return;
    }
    // Recalcular progreso
    const total       = creditos.length;
    const esTerminado = e => e === 'recolectada' || e === 'completado' || e === 'entregada_cedis';
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
    if (!ts || isNaN(ts.getTime())) return 'Sin conexión registrada';
    const diffMs = Math.max(0, Date.now() - ts.getTime());
    const min = Math.floor(diffMs / 60000);
    if (min < 1) return 'Última conexión hace menos de 1 min';
    if (min < 60) return `Última conexión hace ${min} min`;
    const horas = Math.floor(min / 60);
    if (horas < 24) return `Última conexión hace ${horas} hora${horas === 1 ? '' : 's'}`;
    return `Última conexión ${ts.toLocaleDateString('es-MX', {
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
        dot.title = conectado ? 'En tiempo real' : 'Sin conexión en tiempo real';
    }
    const chatDot = document.getElementById('chatLiveWsDot');
    if (chatDot) {
        chatDot.classList.toggle('chat-ws-on', conectado);
        chatDot.classList.toggle('chat-ws-off', !conectado);
        chatDot.title = conectado ? 'En tiempo real' : 'Sin conexión en tiempo real';
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
                text: ev.reason || (ev.code === 4001 ? 'Recarga la página para renovar el tracking en vivo.' : 'No tienes permiso para visualizar esta ruta en vivo.'),
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
            _trkRTAplicarChanges(data.changes || data.data?.changes || []);
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
        case 'almacen.delivery.pending': {
            const payload = data.data || data;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Unidad #${payload.id_unidad || '-'} esperando OTP del almacenista`,
                showConfirmButton: false,
                timer: 3500,
            });
            break;
        }
        case 'almacen.otp.generated': {
            const payload = data.data || data;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `OTP del almacenista listo para la unidad #${payload.id_unidad || '-'}`,
                showConfirmButton: false,
                timer: 3500,
            });
            break;
        }
        case 'almacen.entry.confirmed': {
            const payload = data.data || data;
            if (payload.id_detalle) {
                _trkRTAplicarChanges([{ id_detalle: payload.id_detalle, estatus_recoleccion: 'entregada_cedis' }]);
            }
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Entrega confirmada en CEDIS: unidad #${payload.id_unidad || '-'}`,
                showConfirmButton: false,
                timer: 3500,
            });
            break;
        }
        case 'ruta.concluida':
            _trkRTCargarEstado();
            _trkCargarRutas();
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
                    const nextId = payload.next_id_detalle || payload.siguiente_id_detalle;
                    const cambiosOtp = [];
                    if (idDetalle) cambiosOtp.push({ id_detalle: idDetalle, estatus_recoleccion: 'recolectada' });
                    if (nextId) cambiosOtp.push({ id_detalle: nextId, estatus_recoleccion: 'en_camino' });
                    _trkRTAplicarChanges(cambiosOtp);
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
                Swal.fire({ icon: 'warning', title: 'Sesión expirada', text: 'Recarga la página para renovar el tracking en vivo.', confirmButtonText: 'Aceptar' });
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
     2. Se obtiene el detalle de la ruta para abrir la conversacion general.
     3. Se abre el offcanvas existente con una pestana de ruta.
     4. Al activar la pestana, se carga info del chat por REST.
     5. Si el chat esta activo, se conecta WebSocket (solo lectura).
     6. Mensajes se envian siempre por REST.
============================================================ */

// --- Estado global del Chat ------------------------------
const _trkChat = {
    rutaId:    null,   // id_ruta abierto actualmente
    activeTab: null,   // clave de conversacion activa
    jwtToken:  null,   // JWT en memoria JS (solo para WS)
    jwtExpiry: 0,      // timestamp ms de expiracion
    chats:     {},     // Map<chatKey, chatState>
};
/* chatState = {
    id_chat, estatus, mensajes[], ws, wsRetries, wsRetryTimeout,
    unread, loadingMsgs, allLoaded, oldestMsgId
} */

function _trkChatRutaKey(idRuta) {
    return `ruta_${idRuta}`;
}

function _trkChatKey(det) {
    if (det?.chat_key) return String(det.chat_key);
    if (det?.scope === 'ruta' || (det?.id_ruta && !det?.id_detalle)) return _trkChatRutaKey(det.id_ruta);
    return String(det?.id_detalle || '');
}

function _trkChatQueryString(chatKey) {
    const state = _trkChat.chats[String(chatKey)];
    if (!state) return '';
    if (state.scope === 'ruta') return `id_ruta=${encodeURIComponent(state.id_ruta)}`;
    return `id_detalle=${encodeURIComponent(state.id_detalle || chatKey)}`;
}

function _trkChatRequestBody(chatKey, extra = {}) {
    const state = _trkChat.chats[String(chatKey)];
    const base = { ...extra };
    if (state?.scope === 'ruta') base.id_ruta = state.id_ruta;
    else base.id_detalle = state?.id_detalle || chatKey;
    return base;
}

function _trkChatWsUrl(chatKey, token) {
    const state = _trkChat.chats[String(chatKey)];
    const wsBase = String(window._trackingChatWsBaseUrl || '').replace(/\/$/, '');
    if (!state || !wsBase) return '';
    if (state.scope === 'ruta') {
        return `${wsBase}/api/tracking/rutas/${encodeURIComponent(state.id_ruta)}/chat/live?token=${encodeURIComponent(token)}`;
    }
    return `${wsBase}/api/tracking/chats/${encodeURIComponent(state.id_detalle || chatKey)}/live?token=${encodeURIComponent(token)}`;
}

function _trkChatBuildRutaConversation(idRuta, datos = {}) {
    const detalle = Array.isArray(datos.detalle) ? datos.detalle : [];
    const nombre = _trkSanitizarNombreRuta(datos.nombre_ruta || '') || `Ruta #${idRuta}`;
    return {
        chat_key: _trkChatRutaKey(idRuta),
        scope: 'ruta',
        id_ruta: idRuta,
        ruta_nombre: nombre,
        fecha_programada: datos.fecha_programada_fmt || datos.fecha_programada || '',
        estatus_ruta: datos.estatus_ruta || '',
        total_creditos: detalle.length,
        transportista: _trkTransportistaRutaData(datos)?.nombre || datos.nombre_transportista || '',
    };
}

// --- Abrir chat de una ruta (entry point) ----------------
function _trkChatCargarYAbrir(idRuta) {
    trkFetch(`/TrackingRecoleccion/obtenerDetalleRuta?id_ruta=${idRuta}`)
        .then(r => {
            if (!r.success || !r.datos) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la ruta.', confirmButtonText: 'Aceptar' });
                return;
            }
            const rutaNombre = _trkSanitizarNombreRuta(r.datos.nombre_ruta || '') || `Ruta #${idRuta}`;
            _trkChatAbrir(idRuta, rutaNombre, [_trkChatBuildRutaConversation(idRuta, r.datos)]);
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', confirmButtonText: 'Aceptar' }));
}

function _trkChatRenderContextoDetalle(det) {
    if (det?.scope === 'ruta') {
        const idRuta = det?.id_ruta || '';
        const ruta = det?.ruta_nombre || `Ruta #${idRuta}`;
        const fecha = det?.fecha_programada || 'Sin fecha';
        const estatus = det?.estatus_ruta || 'Sin estatus';
        const total = det?.total_creditos ?? 0;
        const transportista = det?.transportista || 'Sin transportista';
        return `<div class="chat-detail-context-main">
                <span class="chat-detail-context-chip">
                    <i class="fa-solid fa-route"></i>Ruta #${_trkChatEscapeHtml(idRuta)}
                </span>
                <span class="chat-detail-context-title">${_trkChatEscapeHtml(ruta)}</span>
                <span class="chat-detail-context-chip">${_trkChatEscapeHtml(total)} creditos</span>
            </div>
            <div class="chat-detail-context-grid">
                <span class="chat-detail-context-item"><b>Fecha:</b> ${_trkChatEscapeHtml(fecha)}</span>
                <span class="chat-detail-context-item"><b>Estatus:</b> ${_trkChatEscapeHtml(estatus)}</span>
                <span class="chat-detail-context-item chat-detail-context-address"><b>Transportista:</b> ${_trkChatEscapeHtml(transportista)}</span>
            </div>`;
    }
    const idDetalle = det?.id_detalle || '';
    const idCredito = det?.id_credito || 'Sin crédito';
    const orden = parseInt(det?.orden_ruta, 10) || 0;
    const cliente = det?.nombre_cliente || 'Sin cliente';
    const modelo = det?.modelo || 'Unidad no disponible';
    const vin = det?.bin || 'VIN no disponible';
    const estado = det?.estado || 'Sin estado';
    const municipio = det?.municipio || 'Sin municipio';
    const direccion = det?.direccion || 'Sin dirección registrada';
    const confirmacion = det?.estatus_confirmacion_gestor || 'Sin confirmación';
    const recoleccion = det?.estatus_recoleccion || 'Sin avance';
    return `<div class="chat-detail-context-main">
            <span class="chat-detail-context-chip">
                <i class="fa-solid fa-location-dot"></i>Punto ${orden || '-'}
            </span>
            <span class="chat-detail-context-title">${_trkChatEscapeHtml(cliente)}</span>
            <span class="chat-detail-context-chip">Crédito #${_trkChatEscapeHtml(idCredito)}</span>
            <span class="chat-detail-context-chip">Detalle #${_trkChatEscapeHtml(idDetalle)}</span>
        </div>
        <div class="chat-detail-context-grid">
            <span class="chat-detail-context-item"><b>Unidad:</b> ${_trkChatEscapeHtml(modelo)}</span>
            <span class="chat-detail-context-item"><b>VIN:</b> ${_trkChatEscapeHtml(vin)}</span>
            <span class="chat-detail-context-item"><b>Estado:</b> ${_trkChatEscapeHtml(estado)}</span>
            <span class="chat-detail-context-item"><b>Municipio:</b> ${_trkChatEscapeHtml(municipio)}</span>
            <span class="chat-detail-context-item"><b>Confirmación:</b> ${_trkChatEscapeHtml(confirmacion)}</span>
            <span class="chat-detail-context-item"><b>Recolección:</b> ${_trkChatEscapeHtml(recoleccion)}</span>
            <span class="chat-detail-context-item chat-detail-context-address"><b>Dirección:</b> ${_trkChatEscapeHtml(direccion)}</span>
        </div>`;
}

function _trkChatDetalleActivoValido(idDetalle, accion = 'continuar') {
    const id = String(idDetalle);
    const state = _trkChat.chats[id];
    const pane = document.getElementById(`chatPane_${id}`);
    if (!state || !pane) {
        Swal.fire({
            icon: 'warning',
            title: 'Conversacion no disponible',
            text: 'No se encontro el chat para esta accion.',
            confirmButtonText: 'Aceptar',
        });
        return false;
    }
    if (String(_trkChat.activeTab) !== id || !pane.classList.contains('active')) {
        Swal.fire({
            icon: 'warning',
            title: 'Revisa el chat activo',
            text: `Para ${accion}, primero abre la pestana correcta de la conversacion.`,
            confirmButtonText: 'Aceptar',
        });
        return false;
    }
    return true;
}

function _trkChatEventoPerteneceDetalle(idDetalle, data) {
    const state = _trkChat.chats[String(idDetalle)];
    if (state?.scope === 'ruta') {
        const recibidoRuta = data?.id_ruta || data?.ruta_id || data?.mensaje?.id_ruta || data?.mensaje?.metadata?.id_ruta;
        return !recibidoRuta || Number(recibidoRuta) === Number(state.id_ruta);
    }
    const recibido = data?.id_detalle || data?.detalle_id || data?.mensaje?.id_detalle;
    return !recibido || Number(recibido) === Number(idDetalle);
}

function _trkChatResumenDetalle(idDetalle) {
    const state = _trkChat.chats[String(idDetalle)];
    const det = state?.detalle || {};
    if (state?.scope === 'ruta') {
        return det.ruta_nombre ? `Ruta #${det.id_ruta} | ${det.ruta_nombre}` : `Ruta #${det.id_ruta || ''}`;
    }
    const partes = [
        det.orden_ruta ? `Punto ${det.orden_ruta}` : '',
        det.id_detalle ? `Detalle #${det.id_detalle}` : '',
        det.id_credito ? `Crédito #${det.id_credito}` : '',
        det.nombre_cliente || '',
    ].filter(Boolean);
    return partes.join(' | ') || `Detalle #${idDetalle}`;
}

function _trkChatActualizarContextoDetalle(idDetalle, patch = {}) {
    const state = _trkChat.chats[String(idDetalle)];
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
        const id = _trkChatKey(det);
        if (!id) return;
        _trkChat.chats[id] = {
            id_chat: null, estatus: null, mensajes: [],
            scope: det.scope || 'detalle',
            id_ruta: det.id_ruta || idRuta,
            id_detalle: det.id_detalle || null,
            detalle: det,
            ws: null, wsRetries: 0, wsRetryTimeout: null, wsPingInterval: null,
            unread: 0, loadingMsgs: false, allLoaded: false, oldestMsgId: null,
            typingTimeout: null, typingStopTimeout: null, lastTypingSent: 0,
        };

        // Tab ---------------------------------------------
        const li = document.createElement('li');
        li.className = 'nav-item';
        const tabLabel = det.scope === 'ruta'
            ? 'Ruta general'
            : `#${id}${det.id_credito ? `  -  ${det.id_credito}` : ''}`;
        const tabTitle = det.scope === 'ruta'
            ? (det.ruta_nombre || rutaNombre)
            : (det.nombre_cliente || '');
        li.innerHTML = `
            <button class="chat-tab-link" id="chatTabBtn_${id}" data-detalle="${id}" type="button"
                    title="${_trkChatEscapeHtml(tabTitle)}">
                <span>${_trkChatEscapeHtml(tabLabel)}</span>
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
        _trkChatActivarTab(_trkChatKey(detalleItems[0]));
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
    const chatKey = String(idDetalle);
    document.querySelectorAll('.chat-tab-link').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.chat-pane').forEach(p => p.classList.remove('active'));

    const btn  = document.getElementById(`chatTabBtn_${chatKey}`);
    const pane = document.getElementById(`chatPane_${chatKey}`);
    if (btn)  btn.classList.add('active');
    if (pane) pane.classList.add('active');

    _trkChat.activeTab = chatKey;
    _trkChatClearUnread(chatKey);

    const state = _trkChat.chats[chatKey];
    if (state && state.estatus === null) {
        _trkChatCargarInfo(chatKey);
    }
}

// --- Carga de info del chat ------------------------------
async function _trkChatCargarInfo(idDetalle) {
    const chatKey = String(idDetalle);
    const state = _trkChat.chats[chatKey];
    if (!state) return;

    const wrap = document.getElementById(`chatMsgsWrap_${chatKey}`);
    if (wrap) {
        wrap.innerHTML = `<div class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--track-color);"></div>
        </div>`;
    }

    try {
        const r = await trkFetch(`/TrackingRecoleccion/chatInfo?${_trkChatQueryString(chatKey)}`);
        if (!r.success) {
            _trkChatMostrarError(chatKey, r.mensaje || 'Error al cargar el chat.');
            return;
        }
        const chat = r.chat || r.conversacion || {};
        state.id_chat = chat.id_chat || chat.id || null;
        state.estatus = chat.estatus || r.estatus || 'activo';
        if (chat.unread_count !== undefined && chat.unread_count !== null) {
            state.unread = Math.max(0, parseInt(chat.unread_count, 10) || 0);
            _trkChatActualizarUnreadBadge(chatKey);
        }
        _trkChatActualizarEstatusBadge(chatKey, state.estatus);
        _trkChatActualizarUI(chatKey);
        await _trkChatCargarMensajes(chatKey);
        if (state.estatus === 'activo' || state.estatus === 'bloqueado') {
            const token = await _trkChatObtenerToken();
            if (token) _trkChatConectarWS(chatKey, token);
        }
    } catch {
        _trkChatMostrarError(idDetalle, 'Error de conexión al cargar el chat.');
    }
}

// --- Carga paginada de mensajes --------------------------
async function _trkChatCargarMensajes(idDetalle, beforeId = null) {
    const chatKey = String(idDetalle);
    const state = _trkChat.chats[chatKey];
    if (!state || state.loadingMsgs || state.allLoaded) return;
    state.loadingMsgs = true;

    let url = `/TrackingRecoleccion/chatMensajes?${_trkChatQueryString(chatKey)}&limit=50`;
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
        _trkChatRenderMensajes(chatKey, !beforeId);
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
    const chatKey = String(idDetalle);
    const state    = _trkChat.chats[chatKey];
    const textarea = document.getElementById(`chatTextarea_${chatKey}`);
    const sendBtn  = document.getElementById(`chatSendBtn_${chatKey}`);
    if (!state || state.estatus !== 'activo' || !textarea || !sendBtn) return;

    const texto = textarea.value.trim();
    if (!texto) return;

    _trkChatEmitirTyping(chatKey, false);
    textarea.disabled = true;
    sendBtn.disabled  = true;
    try {
        const r = await trkFetch('/TrackingRecoleccion/chatEnviarMensaje', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(_trkChatRequestBody(chatKey, {
                mensaje:      texto,
                tipo_mensaje: 'texto',
                latitud:      null,
                longitud:     null,
                metadata:     null,
            })),
        });
        if (r.success) {
            textarea.value = '';
            // Si WS no esta activo, agregar localmente para feedback inmediato
            if ((!state.ws || state.ws.readyState !== WebSocket.OPEN) && r.mensaje) {
                _trkChatAgregarMensaje(chatKey, r.mensaje);
            }
            // Si WS activo, el evento message.new lo agregara (evita duplicados)
        } else if (r.codigo_http === 409) {
            _trkChatDeshabilitarInput(chatKey, r.mensaje || 'Chat bloqueado o cerrado.');
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
    const chatKey = String(idDetalle);
    const state = _trkChat.chats[chatKey];
    if (!state) return;
    if (state.ws && state.ws.readyState === WebSocket.OPEN) return;
    if (state.ws) { state.ws.onclose = null; state.ws.close(); state.ws = null; }

    const wsUrl = _trkChatWsUrl(chatKey, token);
    if (!wsUrl) { _trkChatActualizarWsDot(chatKey, false); return; }

    let ws;
    try {
        ws = new WebSocket(wsUrl);
    } catch { _trkChatActualizarWsDot(chatKey, false); return; }
    state.ws = ws;

    ws.onopen = () => {
        state.wsRetries = 0;
        _trkChatActualizarWsDot(chatKey, true);
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
        _trkChatProcesarEventoWS(chatKey, data);
    };

    ws.onclose = evt => {
        clearInterval(state.wsPingInterval);
        state.wsPingInterval = null;
        state.ws = null;
        _trkChatActualizarWsDot(chatKey, false);

        // Codigos de cierre definitivo (no reintentar)
        if (evt.code === 4001) { // token invalido/expirado
            _trkChat.jwtToken = null;
            _trkChatMostrarNotice(chatKey, 'Sesion expirada. Recarga la pagina.', 'cerrado');
            return;
        }
        if (evt.code === 4003) { // sin acceso
            _trkChatMostrarNotice(chatKey, 'Sin acceso a este chat.', 'cerrado');
            return;
        }

        // Reintento con back-off exponencial (max. 5 intentos)
        if (state.wsRetries < 5) {
            const delay = Math.min(1000 * Math.pow(2, state.wsRetries), 30000);
            state.wsRetries++;
            state.wsRetryTimeout = setTimeout(async () => {
                const tok = await _trkChatObtenerToken();
                const est = _trkChat.chats[chatKey]?.estatus;
                if (tok && (est === 'activo' || est === 'bloqueado')) {
                    _trkChatConectarWS(chatKey, tok);
                }
            }, delay);
        } else {
            _trkChatMostrarNotice(
                chatKey,
                'Sin conexion en tiempo real - los mensajes se actualizan al enviar.',
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
    Object.keys(_trkChat.chats).forEach(id => _trkChatDesconectarWS(id));
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
            _trkChatMostrarNotice(idDetalle, ' El chat aún no está disponible  -  la ruta no ha iniciado.', 'bloqueado');
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
    const chatKey = String(idDetalle);
    const state = _trkChat.chats[chatKey];
    if (!state || state.estatus !== 'activo') return;
    const sendBtn = document.getElementById(`chatSendBtn_${chatKey}`);
    const attachBtns = document.querySelectorAll(`#chatInputArea_${chatKey} .chat-attach-btn`);
    const oldHtml = sendBtn ? sendBtn.innerHTML : '';
    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    attachBtns.forEach(btn => { btn.disabled = true; });
    try {
        const formData = new FormData();
        if (state.scope === 'ruta') formData.append('id_ruta', String(state.id_ruta));
        else formData.append('id_detalle', String(state.id_detalle || chatKey));
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
                _trkChatMostrarNotice(chatKey, r.mensaje || 'No se pudo subir el archivo.', 'cerrado', 5000);
            }
            return;
        }
        _trkChatAgregarMensaje(chatKey, r.mensaje);
    } catch {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión al subir el archivo.', confirmButtonText: 'Aceptar' });
    } finally {
        if (sendBtn) sendBtn.innerHTML = oldHtml || '<i class="fa-solid fa-paper-plane"></i>';
        _trkChatActualizarUI(chatKey);
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

function _trkMiniChatSetVisible(visible) {
    document.getElementById('trkRouteMiniChat')?.classList.toggle('d-none', !visible);
}

function _trkMiniChatActualizarWsDot(online) {
    const dot = document.getElementById('trkRouteMiniChatWsDot');
    if (!dot) return;
    dot.className = `chat-ws-dot ${online ? 'chat-ws-on' : 'chat-ws-off'}`;
    dot.title = online ? 'Tiempo real activo' : 'Sin tiempo real';
}

function _trkMiniChatMostrarNotice(msg, tipo = 'cerrado', autoHideMs = 0) {
    const notice = document.getElementById('trkRouteMiniChatNotice');
    if (!notice) return;
    notice.textContent = msg;
    notice.className = `chat-status-notice chat-notice-${tipo}`;
    notice.classList.remove('d-none');
    if (autoHideMs > 0) setTimeout(() => notice.classList.add('d-none'), autoHideMs);
}

function _trkMiniChatActualizarUI() {
    const st = _trk.routeMiniChat;
    const textarea = document.getElementById('trkRouteMiniChatTextarea');
    const sendBtn = document.getElementById('trkRouteMiniChatSend');
    const activo = st.estatus === 'activo';
    if (textarea) textarea.disabled = !activo;
    if (sendBtn) sendBtn.disabled = !activo;
    if (activo) {
        document.getElementById('trkRouteMiniChatNotice')?.classList.add('d-none');
    } else if (st.estatus === 'bloqueado') {
        _trkMiniChatMostrarNotice('El chat esta disponible cuando la ruta inicia.', 'bloqueado');
    } else if (st.estatus === 'cerrado') {
        _trkMiniChatMostrarNotice('Esta conversacion ha sido cerrada.', 'cerrado');
    }
}

function _trkMiniChatRenderMensajes(scrollToBottom = true) {
    const st = _trk.routeMiniChat;
    const wrap = document.getElementById('trkRouteMiniChatMessages');
    if (!wrap) return;
    if (!st.mensajes.length) {
        wrap.innerHTML = `<div class="text-center text-muted small py-3">
            <i class="fa-solid fa-comment-slash opacity-25 fa-2x mb-2 d-block"></i>
            Sin mensajes aun
        </div>`;
        return;
    }
    wrap.innerHTML = st.mensajes.slice(-40).map(_trkChatRenderBurbuja).join('');
    if (scrollToBottom) _trkMiniChatScrollFinal();
}

function _trkMiniChatScrollFinal() {
    const wrap = document.getElementById('trkRouteMiniChatMessages');
    if (wrap) wrap.scrollTo({ top: wrap.scrollHeight, behavior: 'smooth' });
}

function _trkMiniChatAgregarMensaje(msg) {
    const st = _trk.routeMiniChat;
    if (!msg) return;
    if (msg.id_mensaje && st.mensajes.some(m => m.id_mensaje === msg.id_mensaje)) return;
    _trkMiniChatMostrarTyping(false);
    st.mensajes.push(msg);
    _trkMiniChatRenderMensajes(true);
}

function _trkMiniChatMostrarTyping(mostrar = true, actor = 'Conductor') {
    const el = document.getElementById('trkRouteMiniChatTyping');
    const st = _trk.routeMiniChat;
    if (!el) return;
    if (st.typingTimeout) {
        clearTimeout(st.typingTimeout);
        st.typingTimeout = null;
    }
    if (!mostrar) {
        el.classList.add('d-none');
        return;
    }
    const labelEl = el.querySelector('span:first-child');
    if (labelEl) labelEl.textContent = actor && actor !== 'Conductor' ? `${actor} escribiendo` : 'Escribiendo';
    el.classList.remove('d-none');
    st.typingTimeout = setTimeout(() => _trkMiniChatMostrarTyping(false), 4500);
    _trkMiniChatScrollFinal();
}

function _trkMiniChatEmitirTyping(activo) {
    const st = _trk.routeMiniChat;
    if (st.estatus !== 'activo' || !st.ws || st.ws.readyState !== WebSocket.OPEN) return;
    clearTimeout(st.typingStopTimeout);
    const now = Date.now();
    if (activo) {
        if (now - (st.lastTypingSent || 0) > 1800) {
            try { st.ws.send(JSON.stringify({ event: 'typing.start', tipo_actor: 'gestor' })); } catch {}
            st.lastTypingSent = now;
        }
        st.typingStopTimeout = setTimeout(() => _trkMiniChatEmitirTyping(false), 1800);
    } else {
        try { st.ws.send(JSON.stringify({ event: 'typing.stop', tipo_actor: 'gestor' })); } catch {}
        st.lastTypingSent = 0;
    }
}

function _trkMiniChatProcesarEventoWS(data) {
    const st = _trk.routeMiniChat;
    const recibidoRuta = data?.id_ruta || data?.ruta_id || data?.mensaje?.id_ruta || data?.mensaje?.metadata?.id_ruta;
    if (recibidoRuta && Number(recibidoRuta) !== Number(st.idRuta)) return;
    switch (data.event) {
        case 'init':
            st.mensajes = data.mensajes || [];
            st.allLoaded = st.mensajes.length < 50;
            if (st.mensajes.length) st.oldestMsgId = st.mensajes[0].id_mensaje;
            _trkMiniChatRenderMensajes(true);
            break;
        case 'message.new':
            if (data.mensaje) _trkMiniChatAgregarMensaje(data.mensaje);
            break;
        case 'typing':
        case 'typing.start':
        case 'typing.stop':
        case 'chat.typing': {
            const tipoActor = String(data.tipo_actor || data.actor || '').toLowerCase();
            if (tipoActor === 'gestor') break;
            const activo = data.event === 'typing.stop' ? false : (data.typing ?? data.is_typing ?? data.active ?? true);
            _trkMiniChatMostrarTyping(!!activo, data.nombre_remitente || data.nombre || 'Conductor');
            break;
        }
        case 'chat.unlocked':
            st.estatus = 'activo';
            _trkMiniChatActualizarUI();
            _trkMiniChatMostrarNotice('La ruta ha iniciado; ya puedes enviar mensajes.', 'activo', 5000);
            break;
        case 'error':
            _trkMiniChatMostrarNotice(data.detail || 'Error en el chat.', 'cerrado');
            break;
    }
}

function _trkMiniChatWsUrl(token) {
    const st = _trk.routeMiniChat;
    const wsBase = String(window._trackingChatWsBaseUrl || '').replace(/\/$/, '');
    if (!wsBase || !st.idRuta) return '';
    return `${wsBase}/api/tracking/rutas/${encodeURIComponent(st.idRuta)}/chat/live?token=${encodeURIComponent(token)}`;
}

function _trkMiniChatConectarWS(token) {
    const st = _trk.routeMiniChat;
    if (!st.idRuta) return;
    if (st.ws && st.ws.readyState === WebSocket.OPEN) return;
    if (st.ws) { st.ws.onclose = null; st.ws.close(); }
    st.ws = null;
    const wsUrl = _trkMiniChatWsUrl(token);
    if (!wsUrl) { _trkMiniChatActualizarWsDot(false); return; }
    let ws;
    try { ws = new WebSocket(wsUrl); } catch { _trkMiniChatActualizarWsDot(false); return; }
    st.ws = ws;
    ws.onopen = () => {
        st.wsRetries = 0;
        _trkMiniChatActualizarWsDot(true);
        st.wsPingInterval = setInterval(() => {
            if (st.ws && st.ws.readyState === WebSocket.OPEN) st.ws.send(JSON.stringify({ event: 'ping' }));
            else {
                clearInterval(st.wsPingInterval);
                st.wsPingInterval = null;
            }
        }, 30000);
    };
    ws.onmessage = evt => {
        let data;
        try { data = JSON.parse(evt.data); } catch { return; }
        if (data.event === 'pong') return;
        _trkMiniChatProcesarEventoWS(data);
    };
    ws.onclose = evt => {
        clearInterval(st.wsPingInterval);
        st.wsPingInterval = null;
        st.ws = null;
        _trkMiniChatActualizarWsDot(false);
        if (evt.code === 4001) _trkChat.jwtToken = null;
        if (evt.code === 4001 || evt.code === 4003) return;
        if (st.wsRetries < 5 && st.idRuta) {
            const delay = Math.min(1000 * Math.pow(2, st.wsRetries), 30000);
            st.wsRetries++;
            st.wsRetryTimeout = setTimeout(async () => {
                const tok = await _trkChatObtenerToken();
                if (tok && ['activo', 'bloqueado'].includes(st.estatus)) _trkMiniChatConectarWS(tok);
            }, delay);
        } else {
            _trkMiniChatMostrarNotice('Sin conexion en tiempo real; se actualizara al enviar.', 'cerrado');
        }
    };
    ws.onerror = () => {};
}

function _trkMiniChatDesconectarWS() {
    const st = _trk.routeMiniChat;
    if (st.wsPingInterval) { clearInterval(st.wsPingInterval); st.wsPingInterval = null; }
    if (st.wsRetryTimeout) { clearTimeout(st.wsRetryTimeout); st.wsRetryTimeout = null; }
    if (st.typingTimeout) { clearTimeout(st.typingTimeout); st.typingTimeout = null; }
    if (st.typingStopTimeout) { clearTimeout(st.typingStopTimeout); st.typingStopTimeout = null; }
    if (st.ws) { st.ws.onclose = null; st.ws.close(); }
    st.ws = null;
    _trkMiniChatActualizarWsDot(false);
}

function _trkMiniChatLimpiar() {
    _trkMiniChatDesconectarWS();
    _trk.routeMiniChat = {
        ..._trk.routeMiniChat,
        idRuta: null,
        rutaNombre: '',
        estatus: null,
        mensajes: [],
        wsRetries: 0,
        lastTypingSent: 0,
        loading: false,
        loaded: false,
        allLoaded: false,
        oldestMsgId: null,
    };
    const msgWrap = document.getElementById('trkRouteMiniChatMessages');
    if (msgWrap) msgWrap.innerHTML = '<div class="text-center text-muted small py-3">Abre una ruta para cargar el chat.</div>';
    document.getElementById('trkRouteMiniChatNotice')?.classList.add('d-none');
    _trkMiniChatSetVisible(false);
    _trkMiniChatActualizarUI();
}

async function _trkMiniChatCargarMensajes() {
    const st = _trk.routeMiniChat;
    if (!st.idRuta || st.loading) return;
    st.loading = true;
    try {
        const r = await trkFetch(`/TrackingRecoleccion/chatMensajes?id_ruta=${encodeURIComponent(st.idRuta)}&limit=50`);
        if (!r.success) return;
        st.mensajes = r.mensajes || [];
        st.allLoaded = st.mensajes.length < 50;
        if (st.mensajes.length) st.oldestMsgId = st.mensajes[0].id_mensaje;
        _trkMiniChatRenderMensajes(true);
    } finally {
        st.loading = false;
    }
}

async function _trkMiniChatIniciar(idRuta, rutaNombre = '') {
    if (!idRuta) {
        _trkMiniChatLimpiar();
        return;
    }
    _trkMiniChatDesconectarWS();
    const st = _trk.routeMiniChat;
    st.idRuta = Number(idRuta);
    st.rutaNombre = rutaNombre || `Ruta #${idRuta}`;
    st.estatus = null;
    st.mensajes = [];
    st.loaded = false;
    st.allLoaded = false;
    st.oldestMsgId = null;
    _trkMiniChatSetVisible(true);
    document.getElementById('trkRouteMiniChatSub').textContent = `${st.rutaNombre} | conversacion general`;
    const wrap = document.getElementById('trkRouteMiniChatMessages');
    if (wrap) wrap.innerHTML = '<div class="text-center py-3"><span class="spinner-border spinner-border-sm" style="color:var(--track-color);"></span></div>';
    try {
        const r = await trkFetch(`/TrackingRecoleccion/chatInfo?id_ruta=${encodeURIComponent(st.idRuta)}`);
        if (!r.success) {
            st.estatus = 'cerrado';
            _trkMiniChatActualizarUI();
            if (wrap) wrap.innerHTML = `<div class="alert alert-warning small m-2 py-2">${_trkChatEscapeHtml(r.mensaje || 'No se pudo cargar el chat de ruta.')}</div>`;
            return;
        }
        const chat = r.chat || r.conversacion || {};
        st.estatus = chat.estatus || r.estatus || 'activo';
        st.loaded = true;
        _trkMiniChatActualizarUI();
        await _trkMiniChatCargarMensajes();
        if (['activo', 'bloqueado'].includes(st.estatus)) {
            const token = await _trkChatObtenerToken();
            if (token) _trkMiniChatConectarWS(token);
        }
    } catch {
        st.estatus = 'cerrado';
        _trkMiniChatActualizarUI();
        if (wrap) wrap.innerHTML = '<div class="alert alert-warning small m-2 py-2">Error de conexion al cargar el chat.</div>';
    }
}

async function _trkMiniChatEnviarMensaje() {
    const st = _trk.routeMiniChat;
    const textarea = document.getElementById('trkRouteMiniChatTextarea');
    const sendBtn = document.getElementById('trkRouteMiniChatSend');
    if (!st.idRuta || st.estatus !== 'activo' || !textarea || !sendBtn) return;
    const texto = textarea.value.trim();
    if (!texto) return;
    _trkMiniChatEmitirTyping(false);
    textarea.disabled = true;
    sendBtn.disabled = true;
    try {
        const r = await trkFetch('/TrackingRecoleccion/chatEnviarMensaje', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_ruta: st.idRuta,
                mensaje: texto,
                tipo_mensaje: 'texto',
                latitud: null,
                longitud: null,
                metadata: null,
            }),
        });
        if (r.success) {
            textarea.value = '';
            if ((!st.ws || st.ws.readyState !== WebSocket.OPEN) && r.mensaje) {
                _trkMiniChatAgregarMensaje(r.mensaje);
            }
        } else if (r.codigo_http === 409) {
            st.estatus = 'cerrado';
            _trkMiniChatActualizarUI();
            _trkMiniChatMostrarNotice(r.mensaje || 'Chat bloqueado o cerrado.', 'cerrado');
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje || 'Error al enviar.', confirmButtonText: 'Aceptar' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo enviar el mensaje.', confirmButtonText: 'Aceptar' });
    } finally {
        _trkMiniChatActualizarUI();
        textarea.focus();
    }
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
