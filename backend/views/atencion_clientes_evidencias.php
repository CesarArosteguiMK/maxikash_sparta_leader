<style>
/* ══════════════════════════════════════════
   1.- EVIDENCIAS — estética alineada a Retenciones
══════════════════════════════════════════ */
.ac-header-gradient {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.ac-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.ac-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.85; color: #fff; }
.ac-header-gradient i  { color: #fff; }


/* ── Cards (misma base que Retenciones) ── */
.ac-card {
    border: 1px solid #e5e7eb;
    border-radius: 0.6rem;
    background: #fff;
    margin-bottom: .85rem;
    overflow: hidden;
    box-shadow: none;
    transition: background-color .15s ease, border-color .15s ease;
}
.ac-card:hover { background: #fcfcfd; border-color: #dbe2ea; }

.ac-card-body {
    padding: .62rem .9rem;
}
.ae-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .8rem;
}
.ae-list-grid {
    flex: 1;
    display: grid;
    grid-template-columns: minmax(210px, 1fr) minmax(320px, 1.35fr) minmax(210px, 1fr);
    gap: .25rem 1rem;
}
.ae-list-cell {
    display: flex;
    flex-direction: column;
    gap: .1rem;
    min-width: 0;
}
.ae-list-cell .ac-lbl {
    color: #6b7280;
    font-size: .68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    line-height: 1.1;
}
.ae-list-cell .ac-val {
    color: #1f2937;
    font-weight: 600;
    font-size: .82rem;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ae-list-credito .ac-val { font-weight: 700; }
.ae-list-nombre .ac-val { text-transform: uppercase; }
.ae-list-ev .ac-val { font-weight: 700; }
.ae-main-meta {
    display: flex;
    flex-direction: column;
    gap: .28rem;
}
.ae-main-folio {
    font-size: .78rem;
    font-weight: 700;
    color: #b45309;
}
.ae-main-credito {
    font-size: .84rem;
    font-weight: 700;
    color: #1f2937;
}
.ae-list-muted {
    color: #9ca3af;
    font-style: italic;
    font-weight: 500;
}
.ac-detail-row {
    display: flex;
    align-items: center;
    gap: .45rem;
    margin-bottom: .08rem;
    font-size: .82rem;
}
.ac-detail-row:last-child { margin-bottom: 0; }
.ac-detail-row .ac-lbl {
    color: #6b7280;
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
    min-width: 128px;
}
.ac-detail-row .ac-val {
    color: #1f2937;
    font-weight: 600;
}

.ac-card-footer {
    border-top: 1px solid #eef2f7;
    padding: .55rem .85rem;
    display: flex;
    justify-content: flex-end;
    background: #fff;
    gap: .5rem;
}
.ae-list-action {
    min-width: 142px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ae-form-trace {
    margin-top: .58rem;
    padding: .58rem .65rem;
    border: 1px solid #e2e8f0;
    border-radius: .55rem;
    background: #f8fafc;
}
.ae-form-trace-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .42rem;
}
.ae-form-trace-title {
    color: #123150;
    font-size: .76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .035em;
}
.ae-form-trace-date {
    color: #64748b;
    font-size: .72rem;
    font-weight: 700;
    white-space: nowrap;
}
.ae-form-trace-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: .42rem .55rem;
}
.ae-form-trace-columns {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
    gap: .85rem;
    align-items: start;
}
.ae-form-trace-panel {
    min-width: 0;
}
.ae-form-trace-panel-title {
    color: #123150;
    font-size: .68rem;
    font-weight: 900;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: .045em;
    margin: 0 0 .42rem;
}
.ae-form-field {
    min-width: 0;
    padding: .08rem .15rem .18rem;
    border-bottom: 1px solid #e2e8f0;
}
.ae-form-field-wide { grid-column: span 2; }
.ae-form-field-head {
    display: flex;
    align-items: center;
    gap: .28rem;
    min-width: 0;
    margin-bottom: .12rem;
}
.ae-form-field-icon {
    color: #64748b;
    font-size: .68rem;
    width: .85rem;
    flex: 0 0 .85rem;
    text-align: center;
}
.ae-form-field-label {
    display: block;
    color: #64748b;
    font-size: .62rem;
    font-weight: 800;
    line-height: 1.1;
    text-transform: uppercase;
    letter-spacing: .035em;
    white-space: nowrap;
    margin-bottom: 0;
}
.ae-form-field-value {
    display: flex;
    align-items: center;
    gap: .34rem;
    color: #1e293b;
    font-size: .75rem;
    font-weight: 700;
    line-height: 1.18;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ae-form-color-dot {
    display: inline-block;
    width: .68rem;
    height: .68rem;
    min-width: .68rem;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, .16);
    box-shadow: 0 0 0 2px rgba(255,255,255,.9);
    flex: 0 0 .68rem;
}
.ae-form-trace-subtitle {
    color: #123150;
    font-size: .68rem;
    font-weight: 900;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: .045em;
    margin: .58rem 0 .34rem;
    padding-top: .48rem;
    border-top: 1px solid #dbe4ef;
}
.ae-form-resguardo-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: .34rem;
}
.ae-form-resguardo-list .ae-form-field-wide {
    grid-column: span 1;
}
.ae-table-wrap {
    border: 1px solid #e5e7eb;
    border-radius: .75rem;
    overflow: visible;
    background: #fff;
}
.ae-table {
    margin: 0;
    font-size: .875rem;
    vertical-align: middle;
}
.ae-table thead th {
    background: #f8fafc;
    color: #566a7f;
    border-bottom: 1px solid #dbe4ef;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .02em;
    text-transform: uppercase;
    white-space: nowrap;
}
.ae-table tbody tr:hover {
    background: #f8fbff;
}
.ae-table td {
    color: #697a8d;
    border-color: #e8eef5;
}
.ae-table-main {
    min-width: 230px;
}
.ae-table-folio {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    padding: .12rem .42rem;
    border-radius: 999px;
    background: #fff7ed;
    color: #b45309;
    font-size: .68rem;
    font-weight: 800;
}
.ae-table-credit {
    display: block;
    margin-top: .22rem;
    color: #566a7f;
    font-weight: 700;
}
.ae-table-main-client {
    display: flex;
    align-items: flex-start;
    gap: .34rem;
    margin-top: .28rem;
    color: #697a8d;
    font-weight: 800;
    line-height: 1.18;
    text-transform: uppercase;
    white-space: normal;
}
.ae-table-main-client i,
.ae-table-gestor-name i,
.ae-table-legacy-label i,
.ae-evidence-approved-date i {
    color: #64748b;
    font-size: .72rem;
    line-height: 1;
    margin-top: .08rem;
    flex: 0 0 auto;
}
.ae-table-name {
    min-width: 210px;
    color: #697a8d;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.2;
}
.ae-table-muted {
    color: #94a3b8;
    font-style: italic;
}
.ae-table-evidence {
    width: 250px;
    max-width: 250px;
    white-space: nowrap;
    font-weight: 700;
    color: #566a7f;
}
.ae-evidence-pill {
    display: inline-flex;
    align-items: center;
    gap: .32rem;
    padding: .18rem .52rem;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 800;
    line-height: 1.1;
    white-space: nowrap;
}
.ae-evidence-pill i {
    font-size: .64rem;
}
.ae-evidence-pill--ok {
    background: #dcfce7;
    color: #15803d;
}
.ae-evidence-pill--warn {
    background: #fff7ed;
    color: #b45309;
}
.ae-evidence-pill--correction {
    background: #fef3c7;
    color: #b45309;
}
.ae-evidence-pill--bad {
    background: #fee2e2;
    color: #dc2626;
}
.ae-evidence-pill--neutral {
    background: #eef2f7;
    color: #475569;
}
.ae-evidence-detail {
    display: flex;
    flex-direction: column;
    gap: .08rem;
    margin-top: .22rem;
    color: #64748b;
    font-size: .68rem;
    font-weight: 700;
    line-height: 1.2;
    white-space: normal;
}
.ae-evidence-detail--single {
    display: block;
}
.ae-evidence-detail span {
    display: flex;
    align-items: center;
    gap: .28rem;
}
.ae-evidence-detail i {
    width: .78rem;
    flex: 0 0 .78rem;
    font-size: .66rem;
    text-align: center;
}
.ae-evidence-detail-ok i { color: #22c55e; }
.ae-evidence-detail-bad i { color: #ef4444; }
.ae-evidence-detail-pending i { color: #94a3b8; }
.ae-evidence-detail-ok { color: #15803d; }
.ae-evidence-detail-bad { color: #dc2626; }
.ae-evidence-detail-pending { color: #64748b; }
body.dark-mode .ae-evidence-detail-ok { color: #86efac; }
body.dark-mode .ae-evidence-detail-bad { color: #fca5a5; }
body.dark-mode .ae-evidence-detail-pending { color: #94a3b8; }
.ae-evidence-approved-date {
    display: block;
    margin-top: .42rem;
    padding-top: .36rem;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: .68rem;
    font-weight: 800;
    line-height: 1.25;
    white-space: normal;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.ae-evidence-approved-date-label {
    display: flex;
    align-items: flex-start;
    gap: .34rem;
}
.ae-evidence-approved-date strong {
    display: block;
    margin-top: .08rem;
    color: #566a7f;
    font-size: .76rem;
    font-weight: 800;
    text-transform: none;
    letter-spacing: 0;
}
.ae-evidence-approved-date small {
    display: block;
    margin-top: .1rem;
    color: #94a3b8;
    font-size: .62rem;
    font-weight: 700;
    line-height: 1.18;
    text-transform: none;
    letter-spacing: 0;
    white-space: normal;
}
.ae-table-action {
    width: 74px;
    min-width: 74px;
    text-align: center !important;
}
.ae-table-date {
    white-space: nowrap;
    line-height: 1.35;
}
.ae-table-gestor {
    min-width: 270px;
}
.ae-table-gestor-name {
    display: flex;
    align-items: flex-start;
    gap: .34rem;
    color: #697a8d;
    font-weight: 700;
    line-height: 1.18;
}
.ae-table-legacy-label {
    display: flex;
    align-items: flex-start;
    gap: .34rem;
    margin-top: .42rem;
    padding-top: .36rem;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: .66rem;
    font-weight: 800;
    line-height: 1.1;
    text-transform: uppercase;
    letter-spacing: .025em;
}
.ae-table-legacy-date {
    display: block;
    margin-top: .12rem;
    color: #566a7f;
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.15;
}
.ae-action-buttons {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center !important;
    gap: .5rem;
    flex-wrap: wrap;
}
#aeTabContent .card-datatable {
    padding: 1.5rem;
}
#aeTabContent .dataTables_length {
    margin-bottom: 1rem;
}
#aeTabContent .dataTables_filter {
    margin-bottom: 1rem;
    text-align: right;
}
#aeTabContent .dataTables_filter input {
    margin-left: .5rem;
    padding: .375rem .75rem;
    border: 1px solid #d9dee3;
    border-radius: .375rem;
}
#aeTabContent .dataTables_filter input:focus {
    border-color: #0d6efd;
    outline: none;
    box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
}
#aeTabContent .dataTables_length select {
    margin: 0 .5rem;
    padding: .375rem 1.75rem .375rem .75rem;
}
#aeTabContent .dataTables_info {
    margin: 0;
    color: #6c757d;
    font-size: .85rem;
}
#aeTabContent .ae-dt-footer {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 1rem;
    flex-wrap: wrap;
    width: 100%;
    margin-left: 0;
    margin-right: 0;
    padding-top: 1rem;
}
#aeTabContent .ae-dt-footer > [class*="col-"] {
    padding-left: 0;
    padding-right: 0;
}
#aeTabContent .ae-dt-info {
    flex: 1 1 auto !important;
    width: auto !important;
    max-width: none !important;
}
#aeTabContent .ae-dt-pages {
    flex: 0 0 auto !important;
    width: auto !important;
    max-width: none !important;
    margin-left: auto;
    display: flex !important;
    justify-content: flex-end !important;
}
#aeTabContent .ae-dt-footer .dataTables_paginate {
    display: flex;
    justify-content: flex-end;
    width: auto;
    margin: 0 !important;
}
#aeTabContent .ae-dt-footer .dataTables_paginate .pagination {
    justify-content: flex-end !important;
    margin: 0 !important;
    margin-left: auto !important;
}
#aeTabContent .ae-dt-footer .dataTables_paginate .page-item {
    margin: 0 .18rem;
}

/* Mismo botón en píldora que «Dictaminar» en Retenciones */
.ac-btn-dictaminar {
    background: #2563eb;
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: .76rem;
    padding: .38rem .9rem;
    border-radius: 999px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s, transform .15s;
}
.ac-btn-dictaminar:hover  { opacity: .9; transform: translateY(-1px); }
.ac-btn-dictaminar:active { transform: translateY(0); }

body.dark-mode .ac-card              { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer       { background: #111827; border-color: #1f2937; }
body.dark-mode .ae-list-cell .ac-lbl { color: #94a3b8; }
body.dark-mode .ae-list-cell .ac-val { color: #e2e8f0; }
body.dark-mode .ae-list-muted { color: #64748b; }
body.dark-mode .ae-main-folio { color: #fcd34d; }
body.dark-mode .ae-main-credito { color: #e2e8f0; }
body.dark-mode .aev-ev-hint { color: #94a3b8 !important; }
body.dark-mode .ae-form-trace { background: #0f172a; border-color: #1f2937; }
body.dark-mode .ae-form-trace-title { color: #e2e8f0; }
body.dark-mode .ae-form-trace-date { color: #94a3b8; }
body.dark-mode .ae-form-field-label { color: #94a3b8; }
body.dark-mode .ae-form-field-value { color: #e2e8f0; }
body.dark-mode .ae-form-field-icon { color: #94a3b8; }
body.dark-mode .ae-form-trace-panel-title { color: #e2e8f0; }
body.dark-mode .ae-form-trace-subtitle { color: #e2e8f0; border-color: #1f2937; }
body.dark-mode .ae-table-wrap { background: #111827; border-color: #1f2937; }
body.dark-mode .ae-table thead th { background: #0f172a; color: #e2e8f0; border-color: #1f2937; }
body.dark-mode .ae-table tbody tr:hover { background: #172033; }
body.dark-mode .ae-table td { color: #cbd5e1; border-color: #1f2937; }
body.dark-mode .ae-table-credit,
body.dark-mode .ae-table-main-client,
body.dark-mode .ae-table-name,
body.dark-mode .ae-table-evidence { color: #e2e8f0; }
body.dark-mode .ae-table-legacy-label { color: #94a3b8; }
body.dark-mode .ae-table-legacy-label { border-color: #1f2937; }
body.dark-mode .ae-table-legacy-date { color: #e2e8f0; }
body.dark-mode .ae-table-main-client i,
body.dark-mode .ae-table-gestor-name i,
body.dark-mode .ae-table-legacy-label i,
body.dark-mode .ae-evidence-approved-date i { color: #94a3b8; }
body.dark-mode .ae-evidence-detail { color: #94a3b8; }
body.dark-mode .ae-evidence-approved-date { color: #94a3b8; border-color: #1f2937; }
body.dark-mode .ae-evidence-approved-date strong { color: #e2e8f0; }
body.dark-mode .ae-evidence-approved-date small { color: #64748b; }
body.dark-mode .ae-evidence-pill--neutral { background: #1f2937; color: #cbd5e1; }
body.dark-mode #aeTabContent .dataTables_filter input,
body.dark-mode #aeTabContent .dataTables_length select { background: #111827; border-color: #1f2937; color: #e2e8f0; }

@media (max-width: 991.98px) {
    .ae-list-grid {
        grid-template-columns: repeat(2, minmax(220px, 1fr));
    }
    .ae-form-trace-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .ae-form-trace-columns {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 767.98px) {
    #aeTabContent .ae-dt-footer {
        justify-content: center !important;
        text-align: center;
    }
    #aeTabContent .ae-dt-info,
    #aeTabContent .ae-dt-pages {
        flex: 0 0 100% !important;
        width: 100% !important;
        margin-left: 0;
        justify-content: center !important;
    }
    #aeTabContent .ae-dt-footer .dataTables_paginate {
        justify-content: center;
        width: 100%;
    }
    #aeTabContent .ae-dt-footer .dataTables_paginate .pagination {
        justify-content: center !important;
        margin-left: 0 !important;
    }
    .ae-card-top {
        flex-direction: column;
        align-items: stretch;
    }
    .ae-list-grid {
        grid-template-columns: 1fr;
    }
    .ae-list-action {
        justify-content: flex-end;
        margin-top: .2rem;
    }
    .ae-form-trace-grid {
        grid-template-columns: 1fr;
    }
    .ae-form-field-wide {
        grid-column: span 1;
    }
}

/* ── Modal validar evidencias (patrón Mis adjudicaciones + mock validación) ── */
#modalAevValidarEvidencias .modal-header {
    background: linear-gradient(135deg, #15803d, #22c55e);
    color: #fff; padding: .75rem 1.15rem; border: none;
}
#modalAevValidarEvidencias .btn-close { filter: brightness(0) invert(1); }
#modalAevValidarEvidencias .modal-dialog.modal-xl {
    max-width: min(72rem, 98vw);
}
.aev-modal-title-wrap {
    min-width: 0;
}
.aev-modal-title-line {
    display: flex;
    align-items: center;
    gap: .55rem;
    flex-wrap: wrap;
}
.aev-context-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .22rem .58rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, .2);
    color: #fff;
    font-size: .62rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: .03em;
    text-transform: uppercase;
    border: 1px solid rgba(255, 255, 255, .28);
}
.aev-context-badge--bandeja { background: rgba(37, 99, 235, .95); }
.aev-context-badge--aprobados { background: rgba(22, 163, 74, .95); }
.aev-context-badge--correcciones { background: rgba(220, 38, 38, .95); }
.aev-modal-subtitle {
    display: block;
    margin-top: .22rem;
    color: rgba(255, 255, 255, .86);
    font-size: .75rem;
    font-weight: 600;
    line-height: 1.25;
}
/* Una línea de ayuda; sin cajas ni párrafos largos */
.aev-ev-hint {
    font-size: .72rem;
    color: #64748b;
    margin: 0 0 .45rem;
    line-height: 1.35;
}
.aev-detalle-loading {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .65rem;
}
.aev-detalle-loading-card {
    min-height: 118px;
    border: 1px solid #e2e8f0;
    border-radius: .6rem;
    background: linear-gradient(90deg, #f8fafc 0%, #eef2f7 45%, #f8fafc 90%);
    background-size: 220% 100%;
    animation: aevLoadShimmer 1.05s linear infinite;
}
@keyframes aevLoadShimmer {
    0% { background-position: 220% 0; }
    100% { background-position: -220% 0; }
}
@media (max-width: 991.98px) { .aev-detalle-loading { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 575.98px) { .aev-detalle-loading { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
/* Recarga de tarjetas: mantiene contenido anterior visível (evita parpadeo en blanco) */
.ae-lista-updating {
    opacity: 1;
    transition: opacity 0.12s ease;
    pointer-events: none;
}
.aev-ev-progress-wrap { margin-bottom: 1rem; }
.aev-ev-progress-lbl  { font-size: .8rem; font-weight: 700; color: #14532d; }
.aev-ev-progress-bg   { height: 8px; background: #e2e8f0; border-radius: 6px; overflow: hidden; }
.aev-ev-progress-fill { height: 100%; background: linear-gradient(90deg, #16a34a, #4ade80); border-radius: 6px; transition: width .25s; }
.aev-ev-section { margin-bottom: 1rem; }
.aev-ev-section--doc { margin-bottom: .35rem; }
.aev-ev-hdr {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    padding: .4rem .75rem; border-radius: .5rem .5rem 0 0;
    font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .45px;
}
.aev-ev-hdr-title { display: inline-flex; align-items: center; gap: .5rem; min-width: 0; }
.aev-ev-hdr-status {
    border-radius: 999px;
    background: rgba(34, 197, 94, .14);
    color: #15803d;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .03em;
    line-height: 1;
    padding: .24rem .55rem;
    text-transform: uppercase;
    white-space: nowrap;
}
.aev-ev-hdr-orange { background: #fff7ed; border: 1px solid #fed7aa; border-bottom: 0; color: #9a3412; }
.aev-ev-hdr-blue   { background: #eff6ff; border: 1px solid #bfdbfe; border-bottom: 0; color: #1e40af; }
.aev-ev-hdr-green  { background: #f0fdf4; border: 1px solid #bbf7d0; border-bottom: 0; color: #14532d; }
.aev-ev-section--doc .aev-ev-hdr {
    padding: .18rem .55rem;
    font-size: .62rem;
    border-radius: .4rem .4rem 0 0;
}
.aev-ev-hdr-purple { background: #faf5ff; border: 1px solid #e9d5ff; border-bottom: 0; color: #6b21a8; }
.aev-ev-slots-wrap {
    padding: .65rem; background: #f8fafc; border: 1px solid #e2e8f0; border-top: 0; border-radius: 0 0 .5rem .5rem;
    display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .5rem;
}
@media (max-width: 1199.98px) { .aev-ev-slots-wrap { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
@media (max-width: 991.98px)  { .aev-ev-slots-wrap { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
@media (max-width: 767.98px)  { .aev-ev-slots-wrap { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 575.98px)  { .aev-ev-slots-wrap { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.aev-ev-slot {
    width: 100%; min-width: 0; height: 118px; background: #fff; border: 2px solid #e2e8f0; border-radius: .6rem; position: relative; overflow: hidden;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .2rem;
}
.aev-ev-slot--acept { border-color: #22c55a; background: #f0fdf4; }
.aev-ev-slot--pend  { border-color: #f59e0b; border-style: solid; }
.aev-ev-slot--rech  { border-color: #ef4444; background: #fef2f2; }
.aev-ev-slot--vacio { border: 2px dashed #cbd5e1; color: #94a3b8; font-size: .7rem; text-align: center; padding: .35rem; }
.aev-ev-slot--click { cursor: pointer; }
.aev-ev-slot--click:focus { outline: 2px solid #2563eb; outline-offset: 2px; }
.aev-ev-slot .aev-txt-lbl { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(15,23,42,.75); color: #fff; font-size: .55rem; font-weight: 700; text-transform: uppercase; padding: .2rem; text-align: center; }
.aev-btn-reemplazo-gestor {
    position: absolute;
    top: .32rem;
    right: .32rem;
    z-index: 4;
    width: 1.75rem;
    height: 1.75rem;
    border: 0;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #111827, #334155);
    color: #fff;
    box-shadow: 0 6px 16px rgba(15, 23, 42, .28);
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
}
.aev-btn-reemplazo-gestor:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15, 23, 42, .35); }
.aev-btn-reemplazo-gestor:disabled { opacity: .65; cursor: progress; transform: none; }
.aev-badge-ok { position: absolute; bottom: 1.35rem; left: 50%; transform: translateX(-50%); background: #22c55e; color: #fff; font-size: .58rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; }
.aev-badge-na { position: absolute; bottom: 1.35rem; left: 50%; transform: translateX(-50%); background: #dc2626; color: #fff; font-size: .58rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; }
/* Visor: dentro de #modalAevValidarEvidencias (trap de foco) — sigue a pantalla con fixed; sin overflow se recorta en .modal */
#modalAevValidarEvidencias.aev-ev-vista-abierta { overflow: visible !important; }
#modalAevValidarEvidencias .aev-vista-overlay {
    position: fixed; inset: 0; z-index: 10050;
    background: rgba(15, 23, 42, 0.78);
    display: flex; align-items: center; justify-content: center; padding: 1rem;
    pointer-events: auto;
}
#modalAevValidarEvidencias .aev-vista-panel {
    width: min(52rem, 96vw);
    height: min(42rem, 92vh);
    overflow: hidden;
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 20px 50px rgba(0,0,0,.35);
    padding: 1rem 1.1rem;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
}
.aev-vista-mediabox {
    flex: 1 1 auto;
    min-height: 0;
    height: 28rem;
    max-height: none;
    background: #0f172a;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    overflow: hidden;
}
.aev-vista-mediabox video, .aev-vista-mediabox img { max-width: 100%; max-height: 100%; object-fit: contain; }
.aev-vista-nav {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}
.aev-vista-nav-btn {
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    border: 1px solid #dbeafe;
    background: #f8fafc;
    color: #173756;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.aev-vista-nav-btn:hover:not(:disabled),
.aev-vista-nav-btn:focus:not(:disabled) {
    background: #e0f2fe;
    border-color: #7dd3fc;
}
.aev-vista-nav-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}
.aev-vista-counter {
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    min-width: 3.6rem;
    text-align: center;
}
.aev-vista-status-badge {
    display: inline-flex;
    align-items: center;
    gap: .28rem;
    margin-left: .45rem;
    padding: .28rem .62rem;
    border-radius: 999px;
    background: #22c55e;
    color: #fff;
    font-size: .64rem;
    font-weight: 900;
    line-height: 1;
    vertical-align: middle;
    letter-spacing: .02em;
    box-shadow: 0 6px 14px rgba(34, 197, 94, .24);
}
.aev-vista-status-badge i {
    font-size: .58rem;
}
.aev-vista-status-badge--rechazada {
    background: #ef4444;
    box-shadow: 0 6px 14px rgba(239, 68, 68, .24);
}
.aev-vista-mediabox.aev-vista-mediabox--zoomable {
    flex-direction: column; align-items: stretch; justify-content: flex-start; padding: 0; overflow: hidden;
}
.aev-vista-mediabox.aev-vista-mediabox--zoomable .aev-zoom-toolbar {
    flex-shrink: 0; background: rgba(15, 23, 42, 0.96); border-top: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 0 0 0.5rem 0.5rem;
}
.aev-vista-mediabox.aev-vista-mediabox--zoomable .aev-zoom-wrap {
    flex: 1; overflow: hidden; min-height: 0; max-height: none; border-radius: 0.5rem 0.5rem 0 0;
    display: flex; align-items: center; justify-content: center;
    touch-action: none;
}
.aev-vista-mediabox.aev-vista-mediabox--zoomable .aev-zoom-wrap.aev-zoom-wrap--scaled:not(.aev-zoom-wrap--dragging) {
    cursor: grab;
}
.aev-vista-mediabox.aev-vista-mediabox--zoomable .aev-zoom-wrap.aev-zoom-wrap--scaled:not(.aev-zoom-wrap--dragging) .aev-zoom-media {
    cursor: grab;
}
.aev-vista-mediabox.aev-vista-mediabox--zoomable .aev-zoom-wrap.aev-zoom-wrap--dragging {
    cursor: grabbing;
    user-select: none;
}
.aev-vista-mediabox.aev-vista-mediabox--zoomable .aev-zoom-wrap.aev-zoom-wrap--dragging .aev-zoom-media {
    user-select: none; pointer-events: none; transition: none;
}
.aev-vista-mediabox.aev-vista-mediabox--zoomable .aev-zoom-media {
    max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;
    transform-origin: center center; transition: transform 0.1s ease-out; display: block; will-change: transform;
}
.aev-vista-mediabox iframe { width: 100%; min-height: 50vh; border: 0; background: #fff; border-radius: 0.35rem; }
#modalAevValidarEvidencias .aev-vista-panel.aev-vista-panel--pdf-only { max-width: min(56rem, 96vw); }
#modalAevValidarEvidencias .aev-vista-mediabox.aev-vista-mediabox--repuve { min-height: 58vh; max-height: 72vh; }
#modalAevValidarEvidencias .aev-vista-panel.aev-vista-panel--repuve {
    border: 1px solid #bbf7d0;
    box-shadow: 0 22px 55px rgba(22, 163, 74, 0.12);
}
#modalAevValidarEvidencias .aev-vista-panel.aev-vista-panel--repuve #aev-vista-titulo { color: #14532d; }
.aev-btn-ver-repuve {
    border: 2px solid #22c55a; color: #14532d; background: #fff; border-radius: 2rem;
    font-weight: 800; font-size: .66rem; padding: .18rem .65rem; line-height: 1.1;
}
.aev-btn-ver-repuve:hover, .aev-btn-ver-repuve:focus {
    background: #dcfce7; border-color: #16a34a; color: #14532d;
}
.aev-doc-zone {
    min-height: 34px; border: 1px dashed #86efac; border-radius: .4rem; background: #f0fdf4; display: flex; align-items: center; justify-content: center; padding: .22rem .45rem; text-align: left; gap: .42rem; margin-top: -1px; flex-wrap: nowrap;
}
.aev-doc-zone .fa-2x { font-size: .95rem; }
.aev-doc-main { display: flex; align-items: center; gap: .35rem; min-width: 0; flex: 1 1 auto; }
.aev-doc-title { color: #14532d; font-size: .68rem; font-weight: 800; line-height: 1.1; white-space: nowrap; }
.aev-doc-sub { color: #64748b; font-size: .64rem; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.aev-doc-actions { display: inline-flex; align-items: center; gap: .3rem; flex-wrap: nowrap; margin-left: auto; }
.aev-doc-actions .aev-badge-ok { font-size: .52rem; padding: 1px 5px; }
.aev-doc-hidden { display: none !important; }
.aev-doc-zone--click { cursor: pointer; }
.aev-doc-zone--rech  { border-color: #f87171 !important; background: #fef2f2 !important; }
.aev-doc-zone--acept { border-color: #4ade80 !important; }
.aev-btn-enviar { border: 2px solid #16a34a; color: #166534; font-weight: 800; border-radius: 2rem; padding: .4rem 1.25rem; }
.aev-btn-enviar:disabled { opacity: .5; }
#aeTabAprobados .ac-btn-dictaminar { display: none !important; }
#modalAevValidarEvidencias.aev-modo-lectura #aev-btn-enviar,
#modalAevValidarEvidencias.aev-modo-lectura #aev-vista-panel-dictamen,
#modalAevValidarEvidencias.aev-modo-lectura .aev-btn-reemplazo-gestor {
    display: none !important;
}
body.dark-mode .aev-ev-slots-wrap { background: #0f172a; border-color: #334155; }
body.dark-mode .aev-ev-slot, body.dark-mode .aev-doc-zone { background: #1e293b; border-color: #334155; }
body.dark-mode .aev-detalle-loading-card {
    border-color: #334155;
    background: linear-gradient(90deg, #111827 0%, #1e293b 45%, #111827 90%);
    background-size: 220% 100%;
}
</style>

<div class="container-fluid py-4">

    <div class="ac-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-camera-retro fa-2x"></i>
        <div>
            <h4>1.- Evidencias</h4>
            <p>Gestión de evidencias para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-pills flex-column flex-md-row mb-3 gap-md-0 gap-2 border-0" id="aeTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ae-tab-bandeja-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#aeTabBandeja">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="ae-badge-bandeja" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ae-tab-aprobados-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#aeTabAprobados">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Aprobados
                        <span class="badge bg-label-success ms-1" id="ae-badge-aprobados" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ae-tab-correcciones-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#aeTabCorrecciones">
                        <i class="fa-solid fa-rotate me-1"></i>Correcciones
                        <span class="badge bg-label-warning ms-1" id="ae-badge-correcciones" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="aeTabContent">
            <div class="tab-pane fade show active" id="aeTabBandeja" role="tabpanel">
                <div id="ae-loader-bandeja" class="text-center py-5 text-muted" style="display:block;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ae-lista-bandeja"></div>
            </div>

            <div class="tab-pane fade" id="aeTabAprobados" role="tabpanel">
                <div id="ae-loader-aprobados" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ae-lista-aprobados"></div>
            </div>

            <div class="tab-pane fade" id="aeTabCorrecciones" role="tabpanel">
                <div id="ae-loader-correcciones" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ae-lista-correcciones"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Validar evidencias (mismo criterio de slots que Mis adjudicaciones / operaciones) -->
<div class="modal fade" id="modalAevValidarEvidencias" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0">
                    <span class="aev-modal-title-wrap">
                        <span class="aev-modal-title-line">
                            <span>
                                <i id="aev-modal-icon" class="fa-solid fa-images me-2"></i><span id="aev-modal-titulo-modo">Evidencias</span> &mdash;
                                <span id="aev-titulo-cliente" class="fw-normal" style="font-size:.9em;"></span>
                            </span>
                            <span id="aev-context-badge" class="aev-context-badge aev-context-badge--bandeja">
                                <i class="fa-solid fa-inbox"></i><span>Bandeja de entrada</span>
                            </span>
                        </span>
                        <span id="aev-context-subtitle" class="aev-modal-subtitle">Evidencias pendientes de revisión por Administración de Cobranza.</span>
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <input type="file" id="aev-inp-repuve" class="d-none" accept="application/pdf,.pdf" aria-hidden="true" tabindex="-1">
            <input type="file" id="aev-inp-reemplazo-gestor" class="d-none" aria-hidden="true" tabindex="-1">
            <div class="modal-body p-3" id="aev-body" style="position:relative;">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm" style="color:#22c55e;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="aev-btn-enviar me-auto d-none" id="aev-btn-enviar" disabled
                        title="">
                    <i class="fa-solid fa-paper-plane me-1"></i>Enviar evidencias validadas
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
    <!-- Dentro de .modal para que el foco del trap de Bootstrap incluya comentario + botones (si está en body, no deja escribir) -->
    <div id="aev-vista-overlay" class="aev-vista-overlay d-none" role="dialog" aria-modal="true" aria-labelledby="aev-vista-titulo">
        <div class="aev-vista-panel" tabindex="-1">
            <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                <h6 class="mb-0 me-auto" id="aev-vista-titulo" style="font-size:1rem;font-weight:700;">Evidencia</h6>
                <div class="aev-vista-nav" id="aev-vista-nav" aria-label="Navegacion de evidencias">
                    <button type="button" class="aev-vista-nav-btn" id="aev-vista-prev" aria-label="Evidencia anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <span class="aev-vista-counter" id="aev-vista-counter">1 / 1</span>
                    <button type="button" class="aev-vista-nav-btn" id="aev-vista-next" aria-label="Evidencia siguiente">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-light border" id="aev-vista-btn-cerrar" aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="aev-vista-mediabox" id="aev-vista-mediabox"></div>
            <div id="aev-vista-solo-aceptada" class="alert alert-success py-2 px-3 mb-3 d-none" role="status">
                <p class="small mb-0 text-dark" id="aev-vista-comentario-leido"></p>
            </div>
            <div id="aev-vista-solo-rechazada" class="alert alert-danger py-2 px-3 mb-3 d-none" role="status">
                <p class="small mb-0 text-dark" id="aev-vista-comentario-rechazo"></p>
            </div>
            <div id="aev-vista-panel-dictamen" class="mb-0">
                <div class="mb-3">
                    <label for="aev-vista-comentario" class="form-label small fw-bold mb-1">Comentario</label>
                    <textarea class="form-control" id="aev-vista-comentario" name="aev_vista_comentario" rows="3" placeholder="" autocomplete="off"></textarea>
                </div>
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button type="button" class="btn btn-success" id="aev-vista-aceptar">
                        <i class="fa-solid fa-check me-1"></i>Aceptar
                    </button>
                    <button type="button" class="btn btn-danger" id="aev-vista-rechazar">
                        <i class="fa-solid fa-xmark me-1"></i>Rechazar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (!function_exists('sparta_public_web_base')) {
    require_once dirname(__DIR__) . '/core/UploadsPaths.php';
}
$aevPublicPath = function_exists('sparta_public_web_base') ? sparta_public_web_base() : '';
$aevPuedeReemplazarEvidencia = in_array(79, array_map('intval', (array) ($_SESSION['modulos'] ?? [])), true);
?>
<script>
(function () {
    'use strict';

    /** Ruta al directorio público, ej. /sparta___SPARTA_SECRET_REDACTED__/public (definida por el servidor, no adivinada) */
    var AEV_SERVER_PUBLIC_BASE = <?php echo json_encode($aevPublicPath, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const AEV_PUEDE_REEMPLAZAR_EVIDENCIA = <?php echo $aevPuedeReemplazarEvidencia ? 'true' : 'false'; ?>;

    function aevInferBaseDesdePathname() {
        const p = (window.location && window.location.pathname) || '';
        const segs = p.split('/').filter(function (x) { return x.length; });
        const k = segs.indexOf('public');
        if (k >= 0) {
            return '/' + segs.slice(0, k + 1).join('/');
        }
        return '';
    }

    function aevBasePublic() {
        if (typeof AEV_SERVER_PUBLIC_BASE === 'string' && AEV_SERVER_PUBLIC_BASE.length > 0) {
            return AEV_SERVER_PUBLIC_BASE;
        }
        if (window._aevBaseCache !== undefined) {
            return window._aevBaseCache;
        }
        const path = (window.location && window.location.pathname) || '';
        let base = '';
        const i = path.indexOf('/public/');
        if (i !== -1) {
            base = path.substring(0, i + '/public'.length);
        } else {
            base = aevInferBaseDesdePathname();
        }
        window._aevBaseCache = base;
        return base;
    }
    function aevUrlForDisplay(u) {
        if (u == null || u === '') {
            return '';
        }
        let s = String(u).trim().replace(/\\/g, '/');
        s = s.replace(/^https?:\/\/uploads(?=\/|$)/i, '/uploads');
        s = s.replace(/^\/{2,}uploads(?=\/|$)/i, '/uploads');
        s = s.replace(/^\/uploads\/uploads\//i, '/uploads/');
        if (/^https?:\/\//i.test(s)) {
            return s;
        }
        const b = aevBasePublic();
        if (b !== '' && (s.indexOf(b + '/') === 0 || s === b)) {
            return s;
        }
        if (s.indexOf('/uploads/') === 0) {
            return b ? b + s : s;
        }
        if (/^uploads\//i.test(s)) {
            s = '/' + s;
            return b ? b + s : s;
        }
        return s;
    }

    (function aevInstalarProteccionUploadsFalso() {
        if (window.__aevUploadsGuardInstalado) return;
        window.__aevUploadsGuardInstalado = true;

        function fix(v) {
            return aevUrlForDisplay(v);
        }
        function fixHtml(html) {
            return String(html).replace(/\b(src|href)=([\"'])(https?:\/\/uploads\/[^\"']*|\/\/+uploads\/[^\"']*)\2/gi, function (_, attr, q, url) {
                return attr + '=' + q + fix(url) + q;
            });
        }

        const innerDesc = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML');
        if (innerDesc && innerDesc.set && innerDesc.get && innerDesc.configurable) {
            Object.defineProperty(Element.prototype, 'innerHTML', {
                configurable: true,
                enumerable: innerDesc.enumerable,
                get: function () { return innerDesc.get.call(this); },
                set: function (value) { return innerDesc.set.call(this, fixHtml(value)); }
            });
        }

        const origSetAttribute = Element.prototype.setAttribute;
        Element.prototype.setAttribute = function (name, value) {
            const n = String(name || '').toLowerCase();
            if ((n === 'src' || n === 'href') && value != null) {
                value = fix(value);
            }
            return origSetAttribute.call(this, name, value);
        };

        [HTMLImageElement.prototype, HTMLMediaElement.prototype, HTMLIFrameElement.prototype].forEach(function (proto) {
            const d = Object.getOwnPropertyDescriptor(proto, 'src');
            if (!d || !d.set || !d.get || !d.configurable) return;
            Object.defineProperty(proto, 'src', {
                configurable: true,
                enumerable: d.enumerable,
                get: function () { return d.get.call(this); },
                set: function (value) { return d.set.call(this, fix(value)); }
            });
        });
    })();

    function aevNormalizarUrlsDetalle(det) {
        if (!det || !Array.isArray(det.evidencias)) return;
        det.evidencias.forEach(function (e) {
            if (e && e.url) e.url = aevUrlForDisplay(e.url);
        });
    }
    function aevSanearDomUrls(root) {
        if (!root || !root.querySelectorAll) return;
        root.querySelectorAll('[data-aev-src]').forEach(function (el) {
            const raw = el.getAttribute('data-aev-src');
            const fixed = aevUrlForDisplay(raw || '');
            if (fixed) {
                el.setAttribute('src', fixed);
            }
        });
        root.querySelectorAll('[data-aev-href]').forEach(function (el) {
            const raw = el.getAttribute('data-aev-href');
            const fixed = aevUrlForDisplay(raw || '');
            if (fixed) {
                el.setAttribute('href', fixed);
            }
        });
        root.querySelectorAll('[src],[href]').forEach(function (el) {
            const src = el.getAttribute('src');
            if (src) {
                const nsrc = aevUrlForDisplay(src);
                if (nsrc && nsrc !== src) el.setAttribute('src', nsrc);
            }
            const href = el.getAttribute('href');
            if (href) {
                const nhref = aevUrlForDisplay(href);
                if (nhref && nhref !== href) el.setAttribute('href', nhref);
            }
        });
    }
    function aevAsegurarOverlayDentroModal() {
        const o = document.getElementById('aev-vista-overlay');
        const m = document.getElementById('modalAevValidarEvidencias');
        if (o && m && o.parentNode !== m) {
            m.appendChild(o);
        }
    }

    function aevSetModoLectura(activo) {
        _aevStore.soloLectura = !!activo;
        const modal = document.getElementById('modalAevValidarEvidencias');
        if (modal) modal.classList.toggle('aev-modo-lectura', !!activo);
        const btnEnviar = document.getElementById('aev-btn-enviar');
        if (btnEnviar && activo) btnEnviar.classList.add('d-none');
        if (btnEnviar && !activo) btnEnviar.classList.remove('d-none');
        const tituloModo = document.getElementById('aev-modal-titulo-modo');
        if (tituloModo) tituloModo.textContent = 'Evidencias';
        const iconoModo = document.getElementById('aev-modal-icon');
        if (iconoModo) {
            iconoModo.className = 'fa-solid fa-images me-2';
        }
    }

    function aevSetContextoModal(origen) {
        const key = String(origen || 'bandeja').toLowerCase();
        const cfg = {
            bandeja: {
                icon: 'fa-inbox',
                label: 'Bandeja de entrada',
                cls: 'aev-context-badge--bandeja',
                subtitle: 'Evidencias pendientes de revisión por Administración de Cobranza.'
            },
            aprobados: {
                icon: 'fa-clipboard-check',
                label: 'Aprobados',
                cls: 'aev-context-badge--aprobados',
                subtitle: 'Evidencias aceptadas y enviadas a la siguiente fase.'
            },
            correcciones: {
                icon: 'fa-rotate',
                label: 'Correcciones',
                cls: 'aev-context-badge--correcciones',
                subtitle: 'Evidencias con rechazo; el gestor debe corregirlas o reemplazarlas.'
            }
        }[key] || null;
        if (!cfg) return;

        const badge = document.getElementById('aev-context-badge');
        const sub = document.getElementById('aev-context-subtitle');
        if (badge) {
            badge.className = 'aev-context-badge ' + cfg.cls;
            badge.innerHTML = '<i class="fa-solid ' + cfg.icon + '"></i><span>' + aeEsc(cfg.label) + '</span>';
        }
        if (sub) sub.textContent = cfg.subtitle;
    }

    function aevRenderCargaRapidaDetalle(id) {
        let cards = '';
        for (let i = 0; i < 12; i++) {
            cards += '<div class="aev-detalle-loading-card" aria-hidden="true"></div>';
        }
        return '<p class="aev-ev-hint mb-3"><i class="fa-solid fa-bolt me-1"></i>Cargando evidencias del cr&eacute;dito #' + aeEsc(String(id)) + '...</p>'
            + '<div class="aev-detalle-loading">' + cards + '</div>';
    }

    function aevObtenerDetalleCredito(idCredito, forzar) {
        const id = parseInt(idCredito, 10);
        const key = String(id);
        if (!id) return Promise.reject(new Error('Cr&eacute;dito inv&aacute;lido'));
        if (!forzar && _aevDetalleCache.has(key)) {
            return Promise.resolve(_aevDetalleCache.get(key));
        }
        if (!forzar && _aevDetallePendientes.has(key)) {
            return _aevDetallePendientes.get(key);
        }
        if (forzar) _aevDetallePendientes.delete(key);
        const promesa = fetch('/MotosAdjudicadas/obtenerEvidenciasCredito', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ id_credito: id, nombre_cliente: '', rapido: true }),
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) _aevDetalleCache.set(key, data);
                return data;
            })
            .finally(function () { _aevDetallePendientes.delete(key); });
        _aevDetallePendientes.set(key, promesa);
        return promesa;
    }

    function aevPrecargarDetalleCredito(idCredito) {
        const id = parseInt(idCredito, 10);
        if (!id || _aevDetalleCache.has(String(id))) return;
        aevObtenerDetalleCredito(id, false).catch(function () {});
    }

    function aevPrecargarDetallesCreditos(idsCredito) {
        const ids = (idsCredito || [])
            .map(function (id) { return parseInt(id, 10); })
            .filter(function (id, idx, arr) {
                return id > 0
                    && arr.indexOf(id) === idx
                    && !_aevDetalleCache.has(String(id))
                    && !_aevDetallePendientes.has(String(id));
            });
        if (!ids.length) return;

        const batch = fetch('/MotosAdjudicadas/obtenerEvidenciasCreditosRapido', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ ids_credito: ids }),
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                const detalles = data && data.success && data.detalles ? data.detalles : {};
                ids.forEach(function (id) {
                    const det = detalles[String(id)] || detalles[id];
                    if (det && det.success) _aevDetalleCache.set(String(id), det);
                });
                return detalles;
            })
            .catch(function () { return {}; });

        ids.forEach(function (id) {
            const key = String(id);
            _aevDetallePendientes.set(key, batch.then(function () {
                return _aevDetalleCache.get(key) || aevObtenerDetalleCredito(id, true);
            }).finally(function () {
                _aevDetallePendientes.delete(key);
            }));
        });
    }

    function aevPrecargarBandejaVisible(rows) {
        if (!Array.isArray(rows) || !rows.length) return;
        aevPrecargarDetallesCreditos(rows.slice(0, 20).map(function (row) { return row && row.id_credito; }));
    }

    const AE_CONFIG = {
        bandeja: {
            url:   '/AtencionClientes/obtenerRecibidos',
            vacio: 'No hay operaciones en la bandeja en este momento.',
        },
        aprobados: {
            url:   '/AtencionClientes/obtenerAprobadosEvidencias',
            vacio: 'No hay operaciones en Aprobados (Procesando IA) en este momento.',
        },
        correcciones: {
            url:   '/AtencionClientes/obtenerCorreccionesEvidencias',
            vacio: 'No hay correcciones de evidencias en este momento.',
        },
    };

    const AE_BADGE_TAB = { bandeja: 'ae-badge-bandeja', aprobados: 'ae-badge-aprobados', correcciones: 'ae-badge-correcciones' };

    /** Tooltip en miniaturas (corto) */
    const AEV_TITLE_DICTAMEN_SLOT = 'Clic para abrir y dictaminar';

    let _aeCargada = { bandeja: false, aprobados: false, correcciones: false };
    let _aeDatos = { bandeja: [], aprobados: [], correcciones: [] };

    /** Evita varios POST finalizar + recargas seguidas al guardar veredictos rápido */
    let _aeFinalizarDebounceTimer = null;

    // Definición de slots (alineada a operaciones / Mis adjudicaciones) — sin tocar esos archivos: copia local.
    const AEV_EV_SECTIONS = [
        { key: 'fis', label: 'Evidencia física (momento 1)', headerClass: 'aev-ev-hdr-blue', icon: 'fa-camera', slots: [
            { key: 'fis_dacion_hoja_1', label: 'Foto dación (hoja 1)', icon: 'fa-file-signature' },
            { key: 'fis_dacion_hoja_2', label: 'Foto dación (hoja 2)', icon: 'fa-file-signature' },
            { key: 'fis_vin', label: 'Foto VIN (VIN)', icon: 'fa-barcode' },
            { key: 'fis_frontal', label: 'Foto frontal', icon: 'fa-camera' },
            { key: 'fis_lateral_der', label: 'Foto lateral derecha', icon: 'fa-camera-rotate' },
            { key: 'fis_trasera', label: 'Foto trasera', icon: 'fa-camera-retro' },
            { key: 'fis_lateral_izq', label: 'Foto lateral izquierda', icon: 'fa-camera-rotate' },
            { key: 'fis_tacometro', label: 'Foto tacómetro', icon: 'fa-gauge-high' },
            { key: 'fis_video_cliente_acuerdo', label: 'Video cliente de acuerdo', icon: 'fa-video' },
            { key: 'fis_360_encendida', label: 'Video moto 360 encendida', icon: 'fa-video' },
            { key: 'fis_video_vuelta_prueba', label: 'Video vuelta de prueba', icon: 'fa-road' },
            { key: 'fis_checklist', label: 'Foto checklist', icon: 'fa-list-check' },
        ]},
    ];
    const AEV_EV_DOCS = [
        { key: 'repuve', label: 'Momento 2: Repuve', headerClass: 'aev-ev-hdr-green', icon: 'fa-file-pdf', slotKey: 'doc_repuve', slotLabel: 'Subir Repuve' },
    ];
    const AEV_IMAGEN_KEYS = (function () {
        const a = [];
        AEV_EV_SECTIONS.forEach(s => s.slots.forEach(sl => a.push(sl.key)));
        return a;
    })();
    const AEV_PDF_KEYS     = ['doc_repuve'];
    const AEV_TOTAL_IMAGEN = AEV_IMAGEN_KEYS.length;
    const AEV_MODAL_TOTAL  = AEV_IMAGEN_KEYS.length + AEV_PDF_KEYS.length;
    const AE_EV_TOTAL      = AEV_TOTAL_IMAGEN;

    /** detalle de sesión: veredictos v[slot]= acep|rec, comentarios c[slot] (persiste solo mientras dura el modal) */
    let _aevStore  = { det: null, idCredito: 0, v: {}, c: {}, rechazosPendientes: {}, pendingVeredictos: {}, soloLectura: false };
    let _aevVistaCtx = { slot: '', label: '', evidId: 0, soloAceptada: false, soloRechazada: false, galeria: [], indice: -1 };
    let _aevReemplazoGestorCtx = { slot: '', label: '' };
    let _aevZoomTeardown = null;
    const _aevDetalleCache = new Map();
    const _aevDetallePendientes = new Map();

    function aevZoomTeardown() {
        if (typeof _aevZoomTeardown === 'function') {
            _aevZoomTeardown();
            _aevZoomTeardown = null;
        }
    }

    /** HTML del visor con barra de zoom abajo (solo imagen / video). */
    function aevZoomHtmlMedia(innerTag) {
        return (
            '<div class="aev-zoom-wrap" tabindex="-1">' + innerTag + '</div>' +
            '<div class="aev-zoom-toolbar d-flex align-items-center justify-content-center gap-2 flex-wrap py-1 px-2">' +
            '<button type="button" class="btn btn-sm btn-outline-light aev-zoom-btn-minus" title="Alejar" aria-label="Alejar"><i class="fa-solid fa-magnifying-glass-minus"></i></button>' +
            '<span class="small text-white aev-zoom-pct fw-semibold" style="min-width:3.25rem;text-align:center;">100%</span>' +
            '<button type="button" class="btn btn-sm btn-outline-light aev-zoom-btn-plus" title="Acercar" aria-label="Acercar"><i class="fa-solid fa-magnifying-glass-plus"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-light aev-rotate-btn-left" title="Rotar izquierda" aria-label="Rotar izquierda"><i class="fa-solid fa-rotate-left"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-light aev-rotate-btn-right" title="Rotar derecha" aria-label="Rotar derecha"><i class="fa-solid fa-rotate-right"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary aev-zoom-btn-reset d-none">Restablecer</button>' +
            '</div>'
        );
    }

    /** Enlaza rueda, botones, doble clic y arrastre para desplazar con zoom. */
    function aevZoomWire(box) {
        aevZoomTeardown();
        const wrap = box.querySelector('.aev-zoom-wrap');
        const media = box.querySelector('.aev-zoom-media');
        const pctEl = box.querySelector('.aev-zoom-pct');
        const btnMinus = box.querySelector('.aev-zoom-btn-minus');
        const btnPlus = box.querySelector('.aev-zoom-btn-plus');
        const btnReset = box.querySelector('.aev-zoom-btn-reset');
        const btnRotateLeft = box.querySelector('.aev-rotate-btn-left');
        const btnRotateRight = box.querySelector('.aev-rotate-btn-right');
        if (!wrap || !media) return;

        let scale = 1;
        let panX = 0;
        let panY = 0;
        let rotate = 0;
        const wheelOpts = { passive: false };
        let dragPan = false;
        let dragClientX0 = 0;
        let dragClientY0 = 0;
        let panDrag0X = 0;
        let panDrag0Y = 0;

        function clamp(v) { return Math.min(4, Math.max(1, v)); }

        /** scale() no agranda el layout: el pan va con translate, sin scrollbars. */
        function commitTransform() {
            if (scale <= 1.02) {
                scale = 1;
                panX = 0;
                panY = 0;
            }
            media.style.transformOrigin = 'center center';
            media.style.transform = 'translate(' + panX + 'px,' + panY + 'px) rotate(' + rotate + 'deg) scale(' + scale + ')';
            if (pctEl) pctEl.textContent = Math.round(scale * 100) + '%';
            wrap.classList.toggle('aev-zoom-wrap--scaled', scale > 1.02);
            if (btnReset) {
                const changed = scale > 1.02 || Math.abs(panX) > 1 || Math.abs(panY) > 1 || rotate !== 0;
                btnReset.classList.toggle('d-none', !changed);
            }
        }

        function onWheel(e) {
            const isVideo = media.tagName === 'VIDEO';
            if (isVideo && !e.ctrlKey && !e.metaKey) return;
            e.preventDefault();
            const factor = e.deltaY > 0 ? 0.9 : 1.1;
            scale = clamp(scale * factor);
            commitTransform();
        }

        function delta(step) {
            scale = clamp(scale + step);
            commitTransform();
        }

        function resetZoom() {
            scale = 1;
            panX = 0;
            panY = 0;
            rotate = 0;
            commitTransform();
        }

        function rotar(step) {
            rotate = ((rotate + step) % 360 + 360) % 360;
            commitTransform();
        }

        function endDragPan() {
            if (!dragPan) return;
            dragPan = false;
            wrap.classList.remove('aev-zoom-wrap--dragging');
            document.removeEventListener('mousemove', onDocMouseMove);
            document.removeEventListener('mouseup', onDocMouseUp);
        }

        function onDocMouseMove(e) {
            if (!dragPan) return;
            panX = panDrag0X + (e.clientX - dragClientX0);
            panY = panDrag0Y + (e.clientY - dragClientY0);
            commitTransform();
        }

        function onDocMouseUp() {
            endDragPan();
        }

        function onWrapMouseDown(e) {
            if (scale <= 1.02 || e.button !== 0) return;
            if (media.tagName === 'VIDEO') {
                const r = media.getBoundingClientRect();
                if (e.clientY > r.bottom - 52) return;
            }
            dragPan = true;
            dragClientX0 = e.clientX;
            dragClientY0 = e.clientY;
            panDrag0X = panX;
            panDrag0Y = panY;
            wrap.classList.add('aev-zoom-wrap--dragging');
            document.addEventListener('mousemove', onDocMouseMove);
            document.addEventListener('mouseup', onDocMouseUp);
            e.preventDefault();
        }

        media.style.transformOrigin = 'center center';
        wrap.addEventListener('wheel', onWheel, wheelOpts);
        wrap.addEventListener('mousedown', onWrapMouseDown);
        if (btnMinus) btnMinus.addEventListener('click', function () { delta(-0.22); });
        if (btnPlus) btnPlus.addEventListener('click', function () { delta(0.22); });
        if (btnRotateLeft) btnRotateLeft.addEventListener('click', function () { rotar(-90); });
        if (btnRotateRight) btnRotateRight.addEventListener('click', function () { rotar(90); });
        if (btnReset) btnReset.addEventListener('click', resetZoom);
        media.addEventListener('dblclick', resetZoom);

        _aevZoomTeardown = function () {
            endDragPan();
            wrap.removeEventListener('wheel', onWheel, wheelOpts);
            wrap.removeEventListener('mousedown', onWrapMouseDown);
        };
        commitTransform();
    }

    function aevReiniciarStore(det, idC) {
        _aevStore.det     = det;
        _aevStore.idCredito = idC;
        _aevStore.v     = {};
        _aevStore.c     = {};
        _aevStore.rechazosPendientes = {};
        _aevStore.pendingVeredictos = {};
        if (det && Array.isArray(det.evidencias)) {
            det.evidencias.forEach(function (e) {
                if (!e || !e.slot) return;
                if (e.slot === 'doc_repuve') return;
                const va = parseInt(e.val_atn, 10) || 0;
                if (va === 1) _aevStore.v[e.slot] = 'acep';
                if (va === 2) _aevStore.v[e.slot] = 'rec';
                if (e.comentario_atn) _aevStore.c[e.slot] = String(e.comentario_atn);
            });
        }
    }

    function aevVeredictoEfectivo(slot) {
        return _aevStore.v[slot] || null;
    }

    /** vac | pend | acep | rec — Repuve (doc_repuve) no usa dictamen en Atención */
    function aevEstadoEvidencia(row, slot) {
        if (!row || !row.url) return 'vac';
        if (slot === 'doc_repuve') return row.url ? 'subido' : 'vac';
        const vr = aevVeredictoEfectivo(slot);
        if (vr === 'acep') return 'acep';
        if (vr === 'rec')  return 'rec';
        const va = row && row.val_atn != null && row.val_atn !== '' ? parseInt(row.val_atn, 10) : 0;
        if (va === 2) return 'rec';
        if (va === 1) return 'acep';
        return 'pend';
    }

    function aevCuentaValidadosImagen(evList) {
        const m = aevMapaPorSlot(evList);
        let n   = 0;
        AEV_IMAGEN_KEYS.forEach(function (k) {
            if (aevEstadoEvidencia(m[k], k) === 'acep') n++;
        });
        return n;
    }

    function aevCuentaValidadosPdf(evList) {
        const m = aevMapaPorSlot(evList);
        const rep = m.doc_repuve;
        return rep && rep.url ? 1 : 0;
    }

    function aevCuentaValidadosTot(evList) {
        return aevCuentaValidadosImagen(evList) + aevCuentaValidadosPdf(evList);
    }

    function aevMapaPorSlot(evList) {
        const m = {};
        (evList || []).forEach(function (e) { m[e.slot] = e; });
        return m;
    }

    function aevImagenesTodasAceptadas(evList) {
        const m = aevMapaPorSlot(evList);
        return AEV_IMAGEN_KEYS.every(function (k) {
            return aevEstadoEvidencia(m[k], k) === 'acep';
        });
    }

    function aevLabelSlotFisico(slot) {
        for (let i = 0; i < AEV_EV_SECTIONS.length; i++) {
            const slots = AEV_EV_SECTIONS[i].slots || [];
            for (let j = 0; j < slots.length; j++) {
                if (slots[j].key === slot) {
                    return slots[j].label;
                }
            }
        }
        return slot || '';
    }

    function aevListaGaleriaFisica() {
        const evMap = aevMapaPorSlot((_aevStore.det && _aevStore.det.evidencias) || []);
        return AEV_IMAGEN_KEYS
            .filter(function (slot) { return evMap[slot] && evMap[slot].url; })
            .map(function (slot) {
                return { slot: slot, label: aevLabelSlotFisico(slot) };
            });
    }

    function aevGuardarComentarioVistaActual() {
        if (!_aevVistaCtx.slot || _aevVistaCtx.soloAceptada || _aevVistaCtx.soloRechazada || _aevStore.soloLectura) {
            return;
        }
        const cmt = document.getElementById('aev-vista-comentario');
        if (cmt) {
            _aevStore.c[_aevVistaCtx.slot] = (cmt.value || '').trim();
        }
    }

    function aevActualizarNavVista() {
        const nav = document.getElementById('aev-vista-nav');
        const prev = document.getElementById('aev-vista-prev');
        const next = document.getElementById('aev-vista-next');
        const counter = document.getElementById('aev-vista-counter');
        const total = Array.isArray(_aevVistaCtx.galeria) ? _aevVistaCtx.galeria.length : 0;
        const idx = parseInt(_aevVistaCtx.indice, 10);
        const visible = total > 1 && _aevVistaCtx.slot !== 'doc_repuve';
        if (nav) nav.classList.toggle('d-none', !visible);
        if (counter) counter.textContent = total ? ((idx + 1) + ' / ' + total) : '0 / 0';
        if (prev) prev.disabled = !visible;
        if (next) next.disabled = !visible;
    }

    function aevNavegarVista(delta) {
        const total = Array.isArray(_aevVistaCtx.galeria) ? _aevVistaCtx.galeria.length : 0;
        if (total <= 1) return;
        const idx = parseInt(_aevVistaCtx.indice, 10);
        const nextIdx = ((idx + delta) % total + total) % total;
        aevGuardarComentarioVistaActual();
        const item = _aevVistaCtx.galeria[nextIdx];
        if (item) {
            aevAbrirVistaEvidencia(item.slot, item.label, { desdeGaleria: true });
        }
    }

    function aevCerrarVistaOverlay() {
        const ovl = document.getElementById('aev-vista-overlay');
        const m   = document.getElementById('modalAevValidarEvidencias');
        if (m) m.classList.remove('aev-ev-vista-abierta');
        const box = document.getElementById('aev-vista-mediabox');
        const panRoot = ovl ? ovl.querySelector('.aev-vista-panel') : null;
        if (panRoot) {
            panRoot.classList.remove('aev-vista-panel--pdf-only');
            panRoot.classList.remove('aev-vista-panel--repuve');
        }
        if (box) {
            aevZoomTeardown();
            box.classList.remove('aev-vista-mediabox--repuve');
            box.classList.remove('aev-vista-mediabox--zoomable');
            box.innerHTML = '';
        }
        if (ovl) ovl.classList.add('d-none');
        const ta = document.getElementById('aev-vista-comentario');
        if (ta)  ta.value = '';
        const solo = document.getElementById('aev-vista-solo-aceptada');
        const rech = document.getElementById('aev-vista-solo-rechazada');
        const panel = document.getElementById('aev-vista-panel-dictamen');
        const cleido = document.getElementById('aev-vista-comentario-leido');
        const cleRech = document.getElementById('aev-vista-comentario-rechazo');
        if (solo) solo.classList.add('d-none');
        if (rech) rech.classList.add('d-none');
        if (panel) panel.classList.remove('d-none');
        if (cleido) { cleido.textContent = ''; cleido.style.display = ''; }
        if (cleRech) { cleRech.textContent = ''; cleRech.style.display = ''; }
        _aevVistaCtx.slot   = '';
        _aevVistaCtx.label  = '';
        _aevVistaCtx.evidId = 0;
        _aevVistaCtx.soloAceptada = false;
        _aevVistaCtx.soloRechazada = false;
        _aevVistaCtx.galeria = [];
        _aevVistaCtx.indice = -1;
        aevActualizarNavVista();
    }

    function aevAbrirVistaEvidencia(slot, label, opciones) {
        if (!_aevStore.det) return;
        if (slot === 'doc_repuve') return;
        if (!(opciones && opciones.desdeGaleria)) {
            aevGuardarComentarioVistaActual();
        }
        const ovlPre = document.getElementById('aev-vista-overlay');
        const panPre = ovlPre ? ovlPre.querySelector('.aev-vista-panel') : null;
        const boxPre = document.getElementById('aev-vista-mediabox');
        if (panPre) {
            panPre.classList.remove('aev-vista-panel--pdf-only');
            panPre.classList.remove('aev-vista-panel--repuve');
        }
        if (boxPre) boxPre.classList.remove('aev-vista-mediabox--repuve');
        const evMap = aevMapaPorSlot(_aevStore.det.evidencias);
        const row   = evMap[slot];
        if (!row || !row.url) return;
        const galeria = aevListaGaleriaFisica();
        const indice = galeria.findIndex(function (item) { return item.slot === slot; });
        const st = aevEstadoEvidencia(row, slot);
        const soloAceptada = (st === 'acep');
        const soloRechazada = (st === 'rec');
        const modoSoloLectura = soloAceptada || soloRechazada || !!_aevStore.soloLectura;
        _aevVistaCtx.soloAceptada = soloAceptada;
        _aevVistaCtx.soloRechazada = soloRechazada;
        _aevVistaCtx.galeria = galeria;
        _aevVistaCtx.indice = indice;
        aevAsegurarOverlayDentroModal();
        const modalAev = document.getElementById('modalAevValidarEvidencias');
        if (modalAev) modalAev.classList.add('aev-ev-vista-abierta');
        const urlRaw = aevUrlForDisplay(String(row.url));
        const ovl  = document.getElementById('aev-vista-overlay');
        const tEl  = document.getElementById('aev-vista-titulo');
        const box  = document.getElementById('aev-vista-mediabox');
        const cmt  = document.getElementById('aev-vista-comentario');
        const soloEl = document.getElementById('aev-vista-solo-aceptada');
        const rechEl = document.getElementById('aev-vista-solo-rechazada');
        const panelDict = document.getElementById('aev-vista-panel-dictamen');
        const cleido = document.getElementById('aev-vista-comentario-leido');
        const cleRech = document.getElementById('aev-vista-comentario-rechazo');
        if (!ovl || !box) return;
        if (tEl) {
            if (soloAceptada) {
                tEl.innerHTML = aeEsc(label || slot)
                    + '<span class="aev-vista-status-badge"><i class="fa-solid fa-circle-check"></i>ACEPTADA</span>';
            } else if (soloRechazada) {
                tEl.innerHTML = aeEsc(label || slot)
                    + '<span class="aev-vista-status-badge aev-vista-status-badge--rechazada"><i class="fa-solid fa-circle-xmark"></i>RECHAZADA</span>';
            } else {
                tEl.textContent = label || slot;
            }
        }
        if (soloEl) soloEl.classList.add('d-none');
        if (rechEl) rechEl.classList.toggle('d-none', !soloRechazada);
        if (panelDict) panelDict.classList.toggle('d-none', modoSoloLectura);
        if (soloAceptada) {
            const txtCom = (row.comentario_atn || _aevStore.c[slot] || '').trim();
            if (cleido) {
                cleido.textContent = txtCom ? ('Comentario de aceptacion: ' + txtCom) : '';
                cleido.style.display = txtCom ? '' : 'none';
            }
            if (soloEl && txtCom) soloEl.classList.remove('d-none');
            if (cmt) cmt.value = '';
        } else if (soloRechazada) {
            const txtRech = (row.comentario_atn || _aevStore.c[slot] || '').trim();
            if (cleRech) {
                cleRech.textContent = txtRech ? ('Motivo de rechazo: ' + txtRech) : 'Sin motivo registrado.';
                cleRech.classList.toggle('text-muted', !txtRech);
            }
            if (cmt) cmt.value = '';
        } else if (_aevStore.soloLectura) {
            if (cmt) cmt.value = '';
            if (cleido) { cleido.textContent = ''; cleido.style.display = ''; }
            if (cleRech) { cleRech.textContent = ''; cleRech.style.display = ''; }
        } else {
            if (cmt) cmt.value = _aevStore.c[slot] || '';
            if (cleido) { cleido.textContent = ''; cleido.style.display = ''; }
            if (cleRech) { cleRech.textContent = ''; cleRech.style.display = ''; }
        }
        const urlE = aeEsc(urlRaw);
        const esVideoSlot = slot === 'fis_360'
            || slot === 'fis_360_encendida'
            || slot === 'fis_video_cliente_acuerdo'
            || slot === 'fis_video_vuelta_prueba';
        const esVideo = (row.tipo && String(row.tipo).toLowerCase().indexOf('video') !== -1) || esVideoSlot;
        const esPdf = (AEV_PDF_KEYS.indexOf(slot) !== -1)
            || (row.tipo && String(row.tipo).toLowerCase().indexOf('pdf') !== -1)
            || /\.pdf(\?|#|$)/i.test(urlRaw);
        aevZoomTeardown();
        if (esPdf) {
            box.classList.remove('aev-vista-mediabox--zoomable');
            box.innerHTML = '<iframe data-aev-src="' + urlE + '" title="Documento" class="aev-iframe-pdf"></iframe>';
        } else if (esVideo) {
            box.classList.add('aev-vista-mediabox--zoomable');
            box.innerHTML = aevZoomHtmlMedia(
                '<video controls playsinline class="aev-zoom-media" data-aev-src="' + urlE + '"></video>'
            );
        } else {
            box.classList.add('aev-vista-mediabox--zoomable');
            box.innerHTML = aevZoomHtmlMedia(
                '<img class="aev-zoom-media" draggable="false" data-aev-src="' + urlE + '" alt="Evidencia">'
            );
        }
        aevSanearDomUrls(box);
        if (!esPdf) {
            aevZoomWire(box);
        }
        _aevVistaCtx.slot  = slot;
        _aevVistaCtx.label = label || '';
        ovl.classList.remove('d-none');
        _aevVistaCtx.evidId = row && row.id ? parseInt(row.id, 10) : 0;
        aevActualizarNavVista();
        if (!modoSoloLectura) {
            setTimeout(function () {
                const tx = document.getElementById('aev-vista-comentario');
                if (tx) tx.focus();
            }, 10);
        }
    }

    /** Repuve: solo lectura en el mismo overlay (sin dictaminar desde aquí). */
    function aevAbrirVistaRepuvePdf() {
        if (!_aevStore.det) return;
        const evMap = aevMapaPorSlot(_aevStore.det.evidencias);
        const row   = evMap.doc_repuve;
        if (!row || !row.url) return;
        const urlRaw = aevUrlForDisplay(String(row.url));
        aevAsegurarOverlayDentroModal();
        const modalAev = document.getElementById('modalAevValidarEvidencias');
        if (modalAev) modalAev.classList.add('aev-ev-vista-abierta');
        const ovl  = document.getElementById('aev-vista-overlay');
        const tEl  = document.getElementById('aev-vista-titulo');
        const box  = document.getElementById('aev-vista-mediabox');
        const panelDict = document.getElementById('aev-vista-panel-dictamen');
        const soloEl = document.getElementById('aev-vista-solo-aceptada');
        const rechEl = document.getElementById('aev-vista-solo-rechazada');
        const panRoot = ovl ? ovl.querySelector('.aev-vista-panel') : null;
        if (!ovl || !box) return;
        if (tEl) tEl.textContent = 'Momento 2: REPUVE — documento PDF';
        if (soloEl) soloEl.classList.add('d-none');
        if (rechEl) rechEl.classList.add('d-none');
        if (panelDict) panelDict.classList.add('d-none');
        if (panRoot) {
            panRoot.classList.add('aev-vista-panel--pdf-only');
            panRoot.classList.add('aev-vista-panel--repuve');
        }
        aevZoomTeardown();
        box.classList.remove('aev-vista-mediabox--zoomable');
        box.classList.add('aev-vista-mediabox--repuve');
        const urlE = aeEsc(urlRaw);
        box.innerHTML = '<iframe data-aev-src="' + urlE + '" title="Repuve (PDF)" class="aev-iframe-pdf"></iframe>';
        aevSanearDomUrls(box);
        _aevVistaCtx.slot  = 'doc_repuve';
        _aevVistaCtx.label = 'Repuve';
        _aevVistaCtx.evidId = row && row.id ? parseInt(row.id, 10) : 0;
        _aevVistaCtx.soloAceptada = false;
        _aevVistaCtx.soloRechazada = false;
        _aevVistaCtx.galeria = [];
        _aevVistaCtx.indice = -1;
        aevActualizarNavVista();
        ovl.classList.remove('d-none');
    }

    function aevRefrescarCuerpoModal() {
        if (!_aevStore.det) return;
        const body = document.getElementById('aev-body');
        if (body) {
            requestAnimationFrame(function () {
                body.innerHTML = aevRenderCuerpoModalValidar(_aevStore.det);
                aevSanearDomUrls(body);
                aevSincroBtnEnviar();
            });
            return;
        }
        aevSincroBtnEnviar();
    }

    function aevRenderSeccionValidar(sec, map, statusText) {
        let inner = '';
        sec.slots.forEach(function (sl) {
            inner += aevRenderCelda(sl, map[sl.key]);
        });
        const status = statusText
            ? '<span class="aev-ev-hdr-status">' + aeEsc(statusText) + '</span>'
            : '';
        return `
        <div class="aev-ev-section">
            <div class="aev-ev-hdr ${sec.headerClass}">
                <span class="aev-ev-hdr-title"><i class="fa-solid ${sec.icon}"></i> ${aeEsc(sec.label)}</span>
                ${status}
            </div>
            <div class="aev-ev-slots-wrap">${inner}</div>
        </div>`;
    }

    function aevEsVideoSlot(slot) {
        return slot === 'fis_360'
            || slot === 'fis_360_encendida'
            || slot === 'fis_video_cliente_acuerdo'
            || slot === 'fis_video_vuelta_prueba';
    }

    function aevAcceptReemplazoSlot(slot) {
        if (aevEsVideoSlot(slot)) return 'video/mp4,.mp4';
        if (slot === 'fis_dacion_hoja_1' || slot === 'fis_dacion_hoja_2') return 'image/jpeg,image/png,application/pdf,.jpg,.jpeg,.png,.pdf';
        return 'image/jpeg,image/png,.jpg,.jpeg,.png';
    }

    function aevBotonReemplazoGestor(sl, modo) {
        if (!AEV_PUEDE_REEMPLAZAR_EVIDENCIA) return '';
        const esCarga = modo === 'cargar';
        const titulo = esCarga ? 'Cargar evidencia por el gestor' : 'Reemplazar evidencia por el gestor';
        const aria = esCarga ? 'Cargar ' : 'Reemplazar ';
        return '<button type="button" class="aev-btn-reemplazo-gestor"'
            + ' data-aev-reemplazar-gestor="' + aeEsc(sl.key) + '"'
            + ' data-aev-reemplazar-lbl="' + aeEsc(sl.label) + '"'
            + ' title="' + aeEsc(titulo) + '"'
            + ' aria-label="' + aria + aeEsc(sl.label) + '">'
            + '<i class="fa-solid fa-lock"></i></button>';
    }

    function aevMostrarOverlaySubida(texto) {
        const body = document.getElementById('aev-body');
        if (!body) return;
        const prev = document.getElementById('aev-subida-overlay');
        if (prev) prev.remove();
        body.insertAdjacentHTML(
            'afterbegin',
            '<div class="aev-load-overlay" id="aev-subida-overlay" style="position:absolute;inset:0;background:rgba(255,255,255,.68);z-index:6;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;color:#334155;">'
            + aeEsc(texto || 'Subiendo...') +
            '</div>'
        );
    }

    function aevQuitarOverlaySubida() {
        const o = document.getElementById('aev-subida-overlay');
        if (o) o.remove();
    }

    function aevRefrescarDetalleActual() {
        if (!_aevStore.det || !_aevStore.det.id) return Promise.resolve(null);
        return fetch('/MotosAdjudicadas/obtenerDetalle/' + parseInt(_aevStore.det.id, 10) + '?incluir_todas=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.detalle) return j;
                aevNormalizarUrlsDetalle(j.detalle);
                aevReiniciarStore(j.detalle, _aevStore.idCredito);
                const body = document.getElementById('aev-body');
                if (body) {
                    body.innerHTML = aevRenderCuerpoModalValidar(_aevStore.det);
                    aevSanearDomUrls(body);
                }
                aevSincroBtnEnviar();
                return j;
            });
    }

    function aevArchivoValidoReemplazo(slot, f) {
        if (!f) return false;
        const name = String(f.name || '');
        const type = String(f.type || '').toLowerCase();
        if (aevEsVideoSlot(slot)) return type === 'video/mp4' || /\.mp4$/i.test(name);
        if (slot === 'fis_dacion_hoja_1' || slot === 'fis_dacion_hoja_2') {
            return type === 'image/jpeg'
                || type === 'image/png'
                || type === 'application/pdf'
                || /\.(jpe?g|png|pdf)$/i.test(name);
        }
        return type === 'image/jpeg' || type === 'image/png' || /\.(jpe?g|png)$/i.test(name);
    }

    function aevAbrirReemplazoGestor(slot, label) {
        if (!AEV_PUEDE_REEMPLAZAR_EVIDENCIA || !_aevStore.det || !_aevStore.det.id || !slot) return;
        const evMap = aevMapaPorSlot((_aevStore.det && _aevStore.det.evidencias) || []);
        const esCarga = !evMap[slot] || !evMap[slot].url;
        _aevReemplazoGestorCtx = { slot: slot, label: label || slot, modo: esCarga ? 'cargar' : 'reemplazar' };
        const abrirInput = function () {
            const inp = document.getElementById('aev-inp-reemplazo-gestor');
            if (!inp) return;
            inp.accept = aevAcceptReemplazoSlot(slot);
            inp.value = '';
            inp.click();
        };
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'question',
                title: esCarga ? 'Cargar evidencia' : 'Reemplazar evidencia',
                html: (esCarga ? 'Quieres cargar <strong>' : 'Quieres reemplazar <strong>') + aeEsc(label || slot) + '</strong> por el gestor?',
                showCancelButton: true,
                confirmButtonText: esCarga ? 'Si, cargar' : 'Si, reemplazar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function (res) {
                if (res && res.isConfirmed) abrirInput();
            });
            return;
        }
        if (window.confirm('¿Quieres reemplazar ' + (label || slot) + ' por el gestor?')) abrirInput();
    }

    function aevSubirReemplazoGestor(f) {
        const slot = _aevReemplazoGestorCtx.slot;
        const label = _aevReemplazoGestorCtx.label || slot;
        const esCarga = _aevReemplazoGestorCtx.modo === 'cargar';
        if (!_aevStore.det || !_aevStore.det.id || !slot || !f) return;
        if (!aevArchivoValidoReemplazo(slot, f)) {
            const msg = aevEsVideoSlot(slot)
                ? 'Este campo solo acepta video MP4.'
                : (slot === 'fis_dacion_hoja_1' || slot === 'fis_dacion_hoja_2'
                    ? 'Este campo acepta imagen JPG/PNG o PDF.'
                    : 'Este campo solo acepta imagen JPG/PNG.');
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Archivo no permitido', text: msg });
            else window.alert(msg);
            return;
        }
        const fd = new FormData();
        fd.append('id_operacion', String(_aevStore.det.id));
        fd.append('slot', slot);
        fd.append('archivo', f, f.name);
        aevMostrarOverlaySubida(esCarga ? 'Cargando evidencia...' : 'Reemplazando evidencia...');
        fetch('/MotosAdjudicadas/reemplazarEvidenciaGestor', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.success) {
                    throw new Error((data && data.message) || 'No se pudo reemplazar la evidencia.');
                }
                return aevRefrescarDetalleActual().then(function () { return data; });
            })
            .then(function () {
                aeCargarConteosPestanas();
                aevRecargarPestanaEvidenciasActiva();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: esCarga ? 'Evidencia cargada' : 'Evidencia reemplazada',
                        text: label + ' quedó pendiente de validación.',
                        timer: 1800,
                        showConfirmButton: false
                    });
                }
            })
            .catch(function (err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'No se pudo reemplazar', text: err.message || 'Intenta de nuevo.' });
                } else {
                    window.alert((err && err.message) || 'No se pudo reemplazar.');
                }
            })
            .finally(function () {
                aevQuitarOverlaySubida();
                _aevReemplazoGestorCtx = { slot: '', label: '', modo: '' };
            });
    }

    function aevRenderCelda(sl, row) {
        const st  = aevEstadoEvidencia(row, sl.key);
        const has = row && row.url;
        if (!has) {
            const botonCarga = aevBotonReemplazoGestor(sl, 'cargar');
            return `
            <div class="aev-ev-slot aev-ev-slot--vacio" style="position:relative;">
                ${botonCarga}
                <i class="fa-solid ${sl.icon} mb-1 opacity-50" style="font-size:1.1rem;"></i>
                <span style="line-height:1.15;">${aeEsc(sl.label)}</span>
            </div>`;
        }
        const uEsc = aeEsc(aevUrlForDisplay(row.url));
        const esVideoSlot = aevEsVideoSlot(sl.key);
        const esVideo = (row.tipo && String(row.tipo).toLowerCase().indexOf('video') !== -1) || esVideoSlot;
        const botonReemplazo = aevBotonReemplazoGestor(sl, 'reemplazar');
        const media = esVideo
            ? '<video class="aev-thumb" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" data-aev-src="' + uEsc + '" muted playsinline></video><div class="aev-aev-mute-play" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.2);pointer-events:none;"><i class="fa-solid fa-play" style="color:#fff;font-size:1.4rem;"></i></div>'
            : '<img class="aev-thumb" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" data-aev-src="' + uEsc + '" alt="">';
        const dataAttr = ' data-aev-ver="' + aeEsc(sl.key) + '" data-aev-lbl="' + aeEsc(sl.label) + '" ';
        const titleAttr = ' title="' + aeEsc(AEV_TITLE_DICTAMEN_SLOT) + '" ';
        if (st === 'acep') {
            return `
            <div class="aev-ev-slot aev-ev-slot--acept aev-ev-slot--click" style="position:relative;"` + dataAttr + titleAttr + `>
                ${media}
                ${botonReemplazo}
                <span class="aev-txt-lbl">${aeEsc(sl.label)}</span>
                <span class="aev-badge-ok">ACEPTADA</span>
            </div>`;
        }
        if (st === 'rec') {
            return `
            <div class="aev-ev-slot aev-ev-slot--rech aev-ev-slot--click" style="position:relative;"` + dataAttr + titleAttr + `>
                ${media}
                ${botonReemplazo}
                <span class="aev-txt-lbl">${aeEsc(sl.label)}</span>
                <span class="aev-badge-na">RECHAZADA</span>
            </div>`;
        }
        return `
        <div class="aev-ev-slot aev-ev-slot--pend aev-ev-slot--click" style="position:relative;"` + dataAttr + titleAttr + `>
            ${media}
            ${botonReemplazo}
            <span class="aev-txt-lbl">${aeEsc(sl.label)}</span>
        </div>`;
    }

    function aevRenderBloqueDoc(doc, map) {
        const ev  = map[doc.slotKey];
        if (!ev || !ev.url) {
            return `
            <div class="aev-doc-zone" data-aev-subir="doc_repuve" role="button" tabindex="0" style="cursor:pointer;" title="Solo PDF">
                <i class="fa-solid ${doc.icon} fa-2x text-success opacity-50"></i>
                <div class="fw-bold small text-success">${aeEsc(doc.slotLabel)}</div>
                <span class="small text-muted">Toca para subir (PDF)</span>
            </div>`;
        }
        const tit  = 'Repuve';
        return `
        <div class="aev-doc-zone aev-doc-zone--acept" data-aev-subir="doc_repuve" role="button" tabindex="0" style="border-style:solid;border-color:#22c55a;background:#f0fdf4;cursor:pointer;">
            <i class="fa-solid fa-file-pdf fa-2x text-success"></i>
            <div class="aev-doc-main">
                <span class="aev-doc-title">${aeEsc(tit)}</span>
                <span class="aev-doc-sub">PDF en expediente. Toca la zona para reemplazar.</span>
            </div>
            <div class="aev-doc-actions">
                <button type="button" class="aev-btn-ver-repuve" data-aev-ver-repuve="1">
                    <i class="fa-solid fa-eye me-1" aria-hidden="true"></i>Ver PDF
                </button>
                <span class="aev-badge-ok" style="position:static;transform:none;display:inline-block;background:#15803d;">En expediente</span>
            </div>
        </div>`;
    }

    function aevRenderCuerpoModalValidar(det) {
        const evl = det.evidencias || [];
        const m   = aevMapaPorSlot(evl);
        const vi   = aevCuentaValidadosImagen(evl);
        const vpdf = aevCuentaValidadosPdf(evl);
        const mostrarDoc = vpdf > 0 || aevImagenesTodasAceptadas(evl);

        let html = '';
        html += aeRenderFormularioOperacion(det);

        AEV_EV_SECTIONS.forEach(function (sec) {
            const completa = vi >= AEV_TOTAL_IMAGEN;
            html += aevRenderSeccionValidar(sec, m, (completa ? 'Evidencias completas ' : 'Evidencias pendientes ') + vi + '/' + AEV_TOTAL_IMAGEN);
        });
        if (!_aevStore.soloLectura) {
            html += '<p class="aev-ev-hint mt-1 mb-2" role="note"><i class="fa-solid fa-hand-pointer me-1" style="opacity:.65;" aria-hidden="true"></i>Clic en cada evidencia para aceptar o rechazar.</p>';
        }

        if (mostrarDoc) {
            AEV_EV_DOCS.forEach(function (doc) {
                html += '<div class="aev-ev-section aev-ev-section--doc">';
                html += '<div class="aev-ev-hdr ' + doc.headerClass + '"><i class="fa-solid ' + doc.icon + '"></i> ' + aeEsc(doc.label) + '</div>';
                html += aevRenderBloqueDoc(doc, m);
                html += '</div>';
            });
        }
        return html;
    }

    /**
     * Enviar: visible y habilitado con fotos/videos aceptados + Repuve (PDF) cargado.
     */
    function aevSincroBtnEnviar() {
        const btn = document.getElementById('aev-btn-enviar');
        if (!btn) return;
        btn.classList.add('d-none');
        btn.disabled = true;
        btn.removeAttribute('title');
        if (!_aevStore.det) return;
        if (!aevImagenesTodasAceptadas(_aevStore.det.evidencias)) return;
        const m = aevMapaPorSlot(_aevStore.det.evidencias);
        const rep = m.doc_repuve;
        if (!rep || !rep.url) return;
        btn.classList.remove('d-none');
        if (aevHayVeredictosPendientes()) {
            btn.disabled = true;
            btn.title = 'Guardando validaciones, espera un momento.';
            return;
        }
        btn.disabled = false;
        btn.removeAttribute('title');
    }

    function aevSetRowValAtnEnDetalle(slot, valAtn, comentario) {
        if (!_aevStore.det || !Array.isArray(_aevStore.det.evidencias)) return;
        _aevStore.det.evidencias.forEach(function (row) {
            if (row && row.slot === slot) {
                row.val_atn = valAtn;
                row.comentario_atn = comentario;
            }
        });
    }

    function aevBuscarEvidenciaDetalle(slot, evidId) {
        if (!_aevStore.det || !Array.isArray(_aevStore.det.evidencias)) return null;
        const id = parseInt(evidId, 10) || 0;
        for (let i = 0; i < _aevStore.det.evidencias.length; i++) {
            const row = _aevStore.det.evidencias[i];
            if (!row) continue;
            if (id > 0 && parseInt(row.id, 10) === id) return row;
            if (slot && row.slot === slot) return row;
        }
        return null;
    }

    function aevKeyRechazoPendiente(opId, evidId) {
        return String(parseInt(opId, 10) || 0) + ':' + String(parseInt(evidId, 10) || 0);
    }

    function aevKeyVeredictoPendiente(opId, evidId) {
        return String(parseInt(opId, 10) || 0) + ':' + String(parseInt(evidId, 10) || 0);
    }

    function aevSetVeredictoPendiente(opId, evidId, activo) {
        const key = aevKeyVeredictoPendiente(opId, evidId);
        if (!_aevStore.pendingVeredictos) _aevStore.pendingVeredictos = {};
        if (activo) {
            _aevStore.pendingVeredictos[key] = true;
        } else if (Object.prototype.hasOwnProperty.call(_aevStore.pendingVeredictos, key)) {
            delete _aevStore.pendingVeredictos[key];
        }
    }

    function aevHayVeredictosPendientes() {
        return !!(_aevStore.pendingVeredictos && Object.keys(_aevStore.pendingVeredictos).length > 0);
    }

    function aevRegistrarRechazoPendiente(opId, evidId, slot, comentario) {
        const idOp = parseInt(opId, 10) || 0;
        const idEv = parseInt(evidId, 10) || 0;
        if (idOp <= 0 || idEv <= 0 || !slot || slot === 'doc_repuve') return;
        const row = aevBuscarEvidenciaDetalle(slot, idEv);
        const motivo = (comentario || '').trim() || 'Evidencia incompleta o borrosa.';
        _aevStore.rechazosPendientes[aevKeyRechazoPendiente(idOp, idEv)] = {
            id_evidencia: idEv,
            slot: slot,
            motivo_rechazo: motivo,
            url_vieja_rechazada: row && row.url ? String(row.url) : ''
        };
    }

    function aevQuitarRechazoPendiente(opId, evidId) {
        const key = aevKeyRechazoPendiente(opId, evidId);
        if (_aevStore.rechazosPendientes && Object.prototype.hasOwnProperty.call(_aevStore.rechazosPendientes, key)) {
            delete _aevStore.rechazosPendientes[key];
        }
    }

    function aevRechazosPendientesOperacion(opId) {
        const idOp = parseInt(opId, 10) || 0;
        if (idOp <= 0 || !_aevStore.rechazosPendientes) return [];
        return Object.keys(_aevStore.rechazosPendientes)
            .filter(function (key) { return key.indexOf(String(idOp) + ':') === 0; })
            .map(function (key) { return _aevStore.rechazosPendientes[key]; })
            .filter(function (row) { return row && row.id_evidencia && row.slot; });
    }

    function aevEnviarRechazosPendientesSiAplica(opId) {
        const pendientes = aevRechazosPendientesOperacion(opId);
        if (!pendientes.length) {
            return Promise.resolve({ success: true, omitido: true });
        }
        const evidenciasApi = pendientes.map(function (row) {
            return {
                id_evidencia: row.id_evidencia,
                motivo_rechazo: row.motivo_rechazo || 'Evidencia incompleta o borrosa.',
                url_vieja_rechazada: row.url_vieja_rechazada || ''
            };
        });

        return fetch('/MotosAdjudicadas/enviarRechazosEvidenciasBulkLegacy', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({
                id_operacion: opId,
                motivo_general: 'Evidencias incompletas o borrosas.',
                evidencias: evidenciasApi
            }),
            credentials: 'same-origin'
        })
            .then(r => r.json())
            .then(function (data) {
                if (data && data.success) {
                    pendientes.forEach(function (row) {
                        aevQuitarRechazoPendiente(opId, row.id_evidencia);
                    });
                }
                return data;
            });
    }

    function aevAplicarVeredictoDesdeVista(ver) {
        if (_aevVistaCtx.soloAceptada || _aevVistaCtx.soloRechazada) return;
        const s = _aevVistaCtx.slot;
        if (!s || s === 'doc_repuve') return;
        const opId  = _aevStore.det && _aevStore.det.id ? parseInt(_aevStore.det.id, 10) : 0;
        const evidId = _aevVistaCtx.evidId;
        const rowPrev = aevBuscarEvidenciaDetalle(s, evidId);
        const prevV = _aevStore.v[s] || null;
        const prevC = _aevStore.c[s] || '';
        const prevValAtn = rowPrev ? rowPrev.val_atn : null;
        const prevComentario = rowPrev ? rowPrev.comentario_atn : '';
        function revertirVeredictoLocal() {
            if (prevV) _aevStore.v[s] = prevV; else delete _aevStore.v[s];
            _aevStore.c[s] = prevC;
            aevSetRowValAtnEnDetalle(s, prevValAtn, prevComentario);
        }
        if (ver === 'acep') { _aevStore.v[s] = 'acep'; }
        else if (ver === 'rec') { _aevStore.v[s] = 'rec'; }
        const cmt = document.getElementById('aev-vista-comentario');
        const coment = cmt ? (cmt.value || '').trim() : '';
        if (cmt) _aevStore.c[s] = coment;
        aevCerrarVistaOverlay();
        if (opId <= 0 || evidId <= 0) {
            revertirVeredictoLocal();
            aevRefrescarCuerpoModal();
            return;
        }
        aevSetVeredictoPendiente(opId, evidId, true);
        aevRefrescarCuerpoModal();
        if (opId > 0 && evidId > 0) {
            const val = ver === 'acep' ? 1 : 2;
            fetch('/MotosAdjudicadas/guardarVeredictoEvidenciaAtn', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify({
                    id_operacion:  opId,
                    id_evidencia:  evidId,
                    val_atn:       val,
                    comentario:    coment
                }),
                credentials: 'same-origin'
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        aevSetRowValAtnEnDetalle(s, val, coment);
                        if (val === 2) {
                            if (_aeFinalizarDebounceTimer) {
                                clearTimeout(_aeFinalizarDebounceTimer);
                                _aeFinalizarDebounceTimer = null;
                            }
                            aevRegistrarRechazoPendiente(opId, evidId, s, coment);
                        } else {
                            aevQuitarRechazoPendiente(opId, evidId);
                            // Recalcula estatus tras aceptar para evitar carreras al cerrar rápido el modal.
                            aevRecalcularDespuesDeVeredicto(opId);
                        }
                    } else {
                        revertirVeredictoLocal();
                        if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: data.message || 'Intenta de nuevo o revisa el servidor (¿migración adj_evidencia?)' });
                        } else {
                        window.alert((data && data.message) || 'No se pudo guardar el veredicto.');
                        }
                    }
                })
                .catch(function () {
                    revertirVeredictoLocal();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo guardar el veredicto.' });
                    }
                })
                .finally(function () {
                    aevSetVeredictoPendiente(opId, evidId, false);
                    aevRefrescarCuerpoModal();
                });
        }
    }

    function aevRecargarPestanaEvidenciasActiva() {
        const b0 = document.getElementById('ae-tab-bandeja-btn');
        const b1 = document.getElementById('ae-tab-aprobados-btn');
        const b2 = document.getElementById('ae-tab-correcciones-btn');
        if (b0 && b0.classList.contains('active')) { aeCargarSeccion('bandeja', true); return; }
        if (b1 && b1.classList.contains('active')) { aeCargarSeccion('aprobados', true); return; }
        if (b2 && b2.classList.contains('active')) { aeCargarSeccion('correcciones', true); }
    }

    /**
     * Tras «Enviar evidencias validadas»: refresca datos sin cambiar la pestaña activa.
     * Evita brincos automáticos de Bandeja a Aprobados.
     */
    function aePostEnviarEvidenciasValidadas() {
        _aeCargada = { bandeja: false, aprobados: false, correcciones: false };
        aeCargarConteosPestanas();
        aevRecargarPestanaEvidenciasActiva();
    }

    function aevAplicarRespuestaFinalizar(data) {
        if (!data || !data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No se actualizó la etapa',
                    text: (data && data.message) ? String(data.message) : 'Respuesta inválida del servidor.'
                });
            }
            return;
        }
        _aeCargada = { bandeja: false, aprobados: false, correcciones: false };
        aeCargarConteosPestanas();
        aevRecargarPestanaEvidenciasActiva();
    }

    function aevOnModalCerrarValidar() {
        if (_aeFinalizarDebounceTimer) {
            clearTimeout(_aeFinalizarDebounceTimer);
            _aeFinalizarDebounceTimer = null;
        }
        const idCreditoActual = parseInt(_aevStore.idCredito, 10) || 0;
        if (idCreditoActual > 0) _aevDetalleCache.delete(String(idCreditoActual));
        if (idCreditoActual > 0) _aevDetallePendientes.delete(String(idCreditoActual));
        if (_aevStore.soloLectura) {
            aevSetModoLectura(false);
            return;
        }
        const d = _aevStore.det;
        if (!d || !d.id) { return; }
        const opId = parseInt(d.id, 10);
        if (opId <= 0) { return; }
        aevEnviarRechazosPendientesSiAplica(opId)
            .then(function (data) {
                if (!data || !data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se notificaron los rechazos',
                            text: (data && data.message) ? String(data.message) : 'No se pudo enviar la notificacion agrupada.'
                        });
                    }
                    return null;
                }
                return fetch('/MotosAdjudicadas/finalizarCierreValidacionEvidenciaAtn', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body:    JSON.stringify({ id_operacion: opId }),
                    credentials: 'same-origin'
                });
            })
            .then(function (r) { return r ? r.json() : null; })
            .then(function (data) { if (data) aevAplicarRespuestaFinalizar(data); })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo cerrar la validacion de evidencias.' });
                }
            });
    }

    function aevRecalcularDespuesDeVeredicto(opId) {
        const id = parseInt(opId, 10);
        if (id <= 0) return;
        if (_aeFinalizarDebounceTimer) {
            clearTimeout(_aeFinalizarDebounceTimer);
        }
        _aeFinalizarDebounceTimer = setTimeout(function () {
            _aeFinalizarDebounceTimer = null;
            fetch('/MotosAdjudicadas/finalizarCierreValidacionEvidenciaAtn', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify({ id_operacion: id }),
                credentials: 'same-origin'
            })
                .then(r => r.json())
                .then(data => { aevAplicarRespuestaFinalizar(data); });
        }, 350);
    }

    function aeEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function aeSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${aeEsc(msg)}</span>
        </div>`;
    }

    function aeValorLimpio(v) {
        if (v == null) return '';
        const s = String(v).trim();
        if (!s || s === '-' || s.toLowerCase() === 'null') return '';
        return s;
    }

    function aeResguardoTexto(item) {
        const base = aeValorLimpio(item.log_lugar_resguardo);
        const otro = aeValorLimpio(item.log_lugar_otro);
        const mapa = {
            'cedis-__SPARTA_SECRET_REDACTED__': 'CEDIS Maxikash',
            'centro-de-acopio': 'Centro de acopio',
            'mi_domicilio': 'Mi domicilio',
            'sucursal': 'Sucursal',
            'agencia': 'Agencia',
            'otro': otro || 'Otro'
        };
        return mapa[base] || base || otro;
    }

    function aeIconoFormulario(label) {
        const k = String(label || '').toLowerCase();
        if (k.indexOf('marca') !== -1) return 'fa-tag';
        if (k.indexOf('modelo') !== -1 || k.indexOf('moto') !== -1) return 'fa-motorcycle';
        if (k.indexOf('ano') !== -1 || k.indexOf('año') !== -1) return 'fa-calendar';
        if (k.indexOf('color') !== -1) return 'fa-palette';
        if (k.indexOf('vin') !== -1 || k.indexOf('serie') !== -1) return 'fa-barcode';
        if (k.indexOf('motor') !== -1) return 'fa-gears';
        if (k.indexOf('placa') !== -1) return 'fa-id-card';
        if (k.indexOf('kilometraje') !== -1) return 'fa-gauge-high';
        if (k.indexOf('llave') !== -1) return 'fa-key';
        if (k.indexOf('tarjeta') !== -1) return 'fa-address-card';
        if (k.indexOf('resguardo') !== -1) return 'fa-warehouse';
        if (k.indexOf('ciudad') !== -1 || k.indexOf('estado') !== -1) return 'fa-location-dot';
        if (k.indexOf('responsable') !== -1) return 'fa-user-check';
        if (k.indexOf('telefono') !== -1 || k.indexOf('teléfono') !== -1) return 'fa-phone';
        if (k.indexOf('latitud') !== -1 || k.indexOf('longitud') !== -1) return 'fa-map-location-dot';
        if (k.indexOf('direccion') !== -1 || k.indexOf('dirección') !== -1) return 'fa-route';
        return 'fa-circle-info';
    }

    function aeColorCss(valor) {
        const s = String(valor || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        const colores = [
            ['morado', '#7c3aed'], ['morada', '#7c3aed'], ['purpura', '#7c3aed'], ['violeta', '#8b5cf6'], ['lila', '#a78bfa'],
            ['verde', '#22c55e'], ['rojo', '#ef4444'], ['azul', '#2563eb'], ['amarillo', '#facc15'],
            ['negro', '#111827'], ['blanco', '#f8fafc'], ['gris', '#64748b'], ['plata', '#94a3b8'],
            ['naranja', '#f97316'], ['rosa', '#ec4899'], ['cafe', '#92400e'], ['marron', '#92400e'],
            ['dorado', '#d97706'], ['beige', '#d6b98c'], ['crema', '#f5e6c8']
        ];
        for (let i = 0; i < colores.length; i++) {
            if (s === colores[i][0] || s.indexOf(colores[i][0]) !== -1) return colores[i][1];
        }
        return '#cbd5e1';
    }

    function aeFormatoTelefono(valor) {
        const limpio = String(valor || '').replace(/\D/g, '');
        if (limpio.length === 10) {
            return limpio.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        }
        if (limpio.length === 12 && limpio.indexOf('52') === 0) {
            return '+52 ' + limpio.slice(2).replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        }
        return aeValorLimpio(valor);
    }

    function aeFormatoSiNo(valor) {
        const raw = aeValorLimpio(valor);
        const s = raw.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
        if (s === 'si' || s === 'sí' || s === '1' || s === 'true') return 'Si';
        if (s === 'no' || s === '0' || s === 'false') return 'No';
        return raw;
    }

    function aeRenderFormularioOperacion(item) {
        const src = Object.assign({}, (item && item.datos_moto) ? item.datos_moto : {}, item || {});
        const moto = [aeValorLimpio(src.moto_marca), aeValorLimpio(src.moto_modelo), aeValorLimpio(src.moto_anio)]
            .filter(Boolean).join(' ');
        const ubicacion = [aeResguardoTexto(src), aeValorLimpio(src.log_ciudad), aeValorLimpio(src.log_estado)]
            .filter(Boolean).join(' / ');
        const camposMoto = [
            ['Marca', aeValorLimpio(src.moto_marca)],
            ['Modelo', aeValorLimpio(src.moto_modelo)],
            ['Año', aeValorLimpio(src.moto_anio)],
            ['Moto', moto, true],
            ['Color', aeValorLimpio(src.moto_color)],
            ['VIN / Serie', aeValorLimpio(src.moto_no_serie)],
            ['No. motor', aeValorLimpio(src.moto_no_motor)],
            ['Placas', aeValorLimpio(src.moto_placas)],
            ['Kilometraje', aeValorLimpio(src.kilometraje)],
            ['Llave fisica', aeFormatoSiNo(src.llave_fisica || src.tiene_llave_fisica)],
            ['Tarjeta circulacion', aeFormatoSiNo(src.tarjeta_circulacion || src.tiene_tarjeta_de_circulacion_en_fisico)],
            ['Placa fisica', aeFormatoSiNo(src.placa_fisica || src.la_moto_tiene_placa_fisica)]
        ].filter(function (row) { return !!row[1]; });

        const camposResguardo = [
            ['Lugar de resguardo', ubicacion.replace(/\s\/\s/g, ' · '), true],
            ['Responsable', aeValorLimpio(src.responsable_entrega)],
            ['Telefono', aeFormatoTelefono(src.log_telefono)],
            ['Latitud', aeValorLimpio(src.latitud)],
            ['Longitud', aeValorLimpio(src.longitud)],
            ['Direccion resguardo', aeValorLimpio(src.log_direccion), true],
            ['Direccion task', aeValorLimpio(src.direccion_task), true]
        ].filter(function (row) { return !!row[1]; });

        if (!camposMoto.length && !camposResguardo.length) return '';

        const fecha = aeValorLimpio(src.datos_moto_fecha);
        function renderCampo(row) {
            const label = row[0];
            const value = row[1];
            const isColor = String(label || '').toLowerCase() === 'color';
            const colorDot = isColor
                ? '<span class="ae-form-color-dot" style="background-color:' + aeEsc(aeColorCss(value)) + ';"></span>'
                : '';
            return `<div class="ae-form-field ${row[2] ? 'ae-form-field-wide' : ''}" title="${aeEsc(value)}">
                <span class="ae-form-field-head">
                    <i class="fa-solid ${aeEsc(aeIconoFormulario(label))} ae-form-field-icon"></i>
                    <span class="ae-form-field-label">${aeEsc(label)}</span>
                </span>
                <span class="ae-form-field-value">${colorDot}<span>${aeEsc(value)}</span></span>
            </div>`;
        }
        return `
            <div class="ae-form-trace">
                <div class="ae-form-trace-head">
                    <span class="ae-form-trace-title"><i class="fa-solid fa-list-check me-1"></i>Formulario capturado</span>
                    ${fecha ? `<span class="ae-form-trace-date">Capturado ${aeEsc(fecha)}</span>` : ''}
                </div>
                <div class="ae-form-trace-columns">
                    <div class="ae-form-trace-panel">
                        <div class="ae-form-trace-grid">
                            ${camposMoto.map(renderCampo).join('')}
                        </div>
                    </div>
                    ${camposResguardo.length ? `
                        <div class="ae-form-trace-panel">
                            <div class="ae-form-resguardo-list">
                                ${camposResguardo.map(renderCampo).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>`;
    }

    /**
     * Misma estructura visual que la tarjeta "Entrantes" en Retenciones
     * (encabezado azul, # crédito + nombre, filas etiqueta/valor, botón al pie).
     */
    function aeNum(item, keys) {
        for (let i = 0; i < keys.length; i++) {
            const v = item ? item[keys[i]] : null;
            if (v !== undefined && v !== null && v !== '') {
                const n = parseInt(v, 10);
                return Number.isFinite(n) ? n : 0;
            }
        }
        return 0;
    }

    function aeResumenEvidencias(item) {
        const cargadas = Math.min(aeNum(item, ['evidencias_count']), AE_EV_TOTAL);
        const aceptadas = Math.min(aeNum(item, ['evidencias_aceptadas_count', 'evidencias_aceptadas']), AE_EV_TOTAL);
        const rechazadas = Math.min(aeNum(item, ['evidencias_rechazadas_count', 'evidencias_rechazadas']), AE_EV_TOTAL);
        const pendientesServer = aeNum(item, ['evidencias_pendientes_count', 'evidencias_pendientes']);
        const pendientes = Math.max(0, Math.min(
            pendientesServer || (cargadas - aceptadas - rechazadas),
            AE_EV_TOTAL
        ));
        return { cargadas, aceptadas, rechazadas, pendientes };
    }

    function aeTextoMovimientoEvidencias(accion) {
        const txt = String(accion || '').trim();
        if (!txt) return '';
        return txt
            .replace(/\s*\(id evidencia\s+\d+\)\s*/i, '')
            .replace(/^VALIDACI[ÓO]N EVIDENCIA\s+/i, 'Evidencia ')
            .replace(/^ENVI[ÓO] EVIDENCIAS VALIDADAS\s*/i, 'Enviado a siguiente fase ')
            .replace(/^REGISTRO RECHAZOS EVIDENCIAS APP\s*/i, 'Registro de rechazos ')
            .replace(/^REEMPLAZO ESPECIAL DE EVIDENCIA:\s*/i, 'Reemplazo: ')
            .replace(/^SUBI[ÓO] EVIDENCIA EN\s*/i, 'Subida: ');
    }

    function aeFormatoTiempoBandeja(minutos) {
        const n = parseInt(minutos, 10);
        if (!Number.isFinite(n) || n < 0) return '';
        if (n < 60) return n + ' min';
        const horas = Math.floor(n / 60);
        if (horas < 24) return horas + (horas === 1 ? ' hora' : ' horas');
        const dias = Math.floor(horas / 24);
        const horasRestantes = horas % 24;
        if (horasRestantes <= 0) return dias + (dias === 1 ? ' dia' : ' dias');
        return dias + (dias === 1 ? ' dia ' : ' dias ') + horasRestantes + ' h';
    }

    function aeRenderEstadoEvidencias(item, key) {
        const r = aeResumenEvidencias(item);
        const pestana = String(key || '').toLowerCase();
        const cargadasTxt = 'Cargadas ' + r.cargadas + '/' + AE_EV_TOTAL;
        const tiempoBandeja = aeFormatoTiempoBandeja(item && item.minutos_en_bandeja_evidencias);
        const tiempoTotalValidacion = aeFormatoTiempoBandeja(item && item.minutos_total_validacion_evidencias);
        const fechaInicioValidacion = item && item.fecha_inicio_validacion_evidencias
            ? String(item.fecha_inicio_validacion_evidencias)
            : '';
        const fechaFinValidacion = item && item.fecha_fin_validacion_evidencias
            ? String(item.fecha_fin_validacion_evidencias)
            : '';
        const fechaEntradaBandeja = item && item.fecha_entrada_bandeja_evidencias
            ? String(item.fecha_entrada_bandeja_evidencias)
            : '';
        const bloqueTiempoBandeja = (tiempoBandeja && (pestana === 'bandeja' || pestana === 'correcciones'))
            ? '<span class="ae-evidence-approved-date"><span class="ae-evidence-approved-date-label"><i class="fa-solid fa-hourglass-half"></i>' + (pestana === 'correcciones' ? 'Tiempo en correcciones' : 'Tiempo en evidencias') + '</span><strong>' + aeEsc(tiempoBandeja) + '</strong>' + (fechaEntradaBandeja ? '<small>Desde ' + aeEsc(fechaEntradaBandeja) + '</small>' : '') + '</span>'
            : '';
        const bloqueTiempoTotalValidacion = (tiempoTotalValidacion && pestana === 'aprobados')
            ? '<span class="ae-evidence-approved-date"><span class="ae-evidence-approved-date-label"><i class="fa-solid fa-stopwatch"></i>Tiempo validacion</span><strong>' + aeEsc(tiempoTotalValidacion) + '</strong>' + (fechaInicioValidacion || fechaFinValidacion ? '<small>' + (fechaInicioValidacion ? 'Desde ' + aeEsc(fechaInicioValidacion) : '') + (fechaInicioValidacion && fechaFinValidacion ? '<br>' : '') + (fechaFinValidacion ? 'Lista ' + aeEsc(fechaFinValidacion) : '') + '</small>' : '') + '</span>'
            : '';
        const fechaUltimoMovimiento = item && item.fecha_ultimo_movimiento_evidencias
            ? String(item.fecha_ultimo_movimiento_evidencias)
            : '';
        const accionUltimoMovimiento = item && item.accion_ultimo_movimiento_evidencias
            ? aeTextoMovimientoEvidencias(item.accion_ultimo_movimiento_evidencias)
            : '';
        const ultimoMovimiento = (fechaUltimoMovimiento && pestana !== 'aprobados')
            ? '<span class="ae-evidence-approved-date"><span class="ae-evidence-approved-date-label"><i class="fa-solid fa-clock-rotate-left"></i>Último movimiento de evidencias</span><strong>' + aeEsc(fechaUltimoMovimiento) + '</strong>' + (accionUltimoMovimiento ? '<small>' + aeEsc(accionUltimoMovimiento) + '</small>' : '') + '</span>'
            : '';
        if (pestana === 'aprobados') {
            const aceptadas = r.aceptadas || r.cargadas;
            return '<span class="ae-evidence-pill ae-evidence-pill--ok"><i class="fa-solid fa-circle-check"></i>Aceptadas ' + aceptadas + '/' + AE_EV_TOTAL + '</span>'
                + bloqueTiempoTotalValidacion
                + ultimoMovimiento;
        }

        if (r.rechazadas > 0 || pestana === 'correcciones') {
            return '<span class="ae-evidence-pill ae-evidence-pill--correction"><i class="fa-solid fa-triangle-exclamation"></i>En correccion</span>'
                + '<span class="ae-evidence-detail">'
                + '<span class="ae-evidence-detail-ok"><i class="fa-solid fa-circle-check"></i>' + r.aceptadas + ' aceptadas</span>'
                + '<span class="ae-evidence-detail-bad"><i class="fa-solid fa-circle-xmark"></i>' + r.rechazadas + ' rechazadas</span>'
                + '<span class="ae-evidence-detail-pending"><i class="fa-solid fa-clock"></i>' + r.pendientes + ' pendientes</span>'
                + '</span>'
                + bloqueTiempoBandeja
                + ultimoMovimiento;
        }

        if (r.aceptadas > 0 || r.pendientes > 0) {
            return '<span class="ae-evidence-pill ae-evidence-pill--warn"><i class="fa-solid fa-clock"></i>En validacion</span>'
                + '<span class="ae-evidence-detail">'
                + '<span class="ae-evidence-detail-ok"><i class="fa-solid fa-circle-check"></i>' + r.aceptadas + ' aceptadas</span>'
                + '<span class="ae-evidence-detail-pending"><i class="fa-solid fa-clock"></i>' + r.pendientes + ' pendientes</span>'
                + '</span>'
                + bloqueTiempoBandeja
                + ultimoMovimiento;
        }

        return '<span class="ae-evidence-pill ae-evidence-pill--neutral"><i class="fa-solid fa-file-circle-check"></i>' + aeEsc(cargadasTxt) + '</span>'
            + bloqueTiempoBandeja
            + ultimoMovimiento;
    }

    function aeRenderCard(item, key) {
        const g  = item.gestor_nombre ? aeEsc(item.gestor_nombre) : '<span class="ae-list-muted">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? aeEsc(item.fecha_asignacion)
            : '<span class="ae-list-muted">—</span>';
        const nombreCliente = item.nombre_cliente
            ? aeEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? aeEsc(item.folio) : '—';
        const accion = String(key || '').toLowerCase() === 'aprobados' ? '' : `
                <div class="ae-list-action">
                <button type="button" class="ac-btn-dictaminar" data-aev-no-row="1"
                        onclick="event.stopPropagation(); aevValidarAbrir(${+item.id_credito})">
                    <i class="fa-solid fa-clipboard-check me-1"></i>Validar evidencias
                </button>
                </div>`;

        return `
        <div class="ac-card">
            <div class="ac-card-body">
                <div class="ae-card-top">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${aeEsc(String(item.id_credito))}</span>
                    </div>
                    <div class="ae-list-cell ae-list-gestor">
                        <span class="ac-lbl">Gestor a cargo</span>
                        <span class="ac-val">${g}</span>
                    </div>
                    <div class="ae-list-cell ae-list-asig">
                        <span class="ac-lbl">Asignación realizada</span>
                        <span class="ac-val">${fa}</span>
                        ${item.fecha_aprobacion_evidencias ? '<span class="ac-lbl mt-1">Aprobaci&oacute;n evidencias</span><span class="ac-val">' + aeEsc(item.fecha_aprobacion_evidencias) + '</span>' : ''}
                    </div>
                    <div class="ae-list-cell ae-list-nombre">
                        <span class="ac-lbl">Nombre</span>
                        <span class="ac-val">${nombreCliente}</span>
                    </div>
                    <div class="ae-list-cell ae-list-ev">
                        <span class="ac-lbl">Estado evidencias</span>
                        <span class="ac-val">${aeRenderEstadoEvidencias(item, key)}</span>
                    </div>
                </div>
                ${accion}
                </div>
            </div>
        </div>`;
    }

    function aeRenderFilaTabla(item, key) {
        const gestor = item.gestor_nombre ? aeEsc(item.gestor_nombre) : '<span class="ae-table-muted">Sin asignar</span>';
        const fechaDictamenLegacy = item.fecha_dictamen_legacy
            ? aeEsc(item.fecha_dictamen_legacy)
            : (item.fecha_gestion_legacy ? aeEsc(item.fecha_gestion_legacy) : '<span class="ae-table-muted">-</span>');
        const cliente = item.nombre_cliente ? aeEsc(item.nombre_cliente) : '<span class="ae-table-muted">Sin nombre</span>';
        const folio = item.folio ? aeEsc(item.folio) : '-';
        const esAprobados = String(key || '').toLowerCase() === 'aprobados';
        const accionVer = esAprobados ? `
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-aev-no-row="1"
                            title="Ver evidencias" aria-label="Ver evidencias"
                            onclick="event.stopPropagation(); aevValidarAbrir(${+item.id_credito}, { soloLectura: true, origen: '${aeEsc(String(key || 'bandeja'))}' })">
                        <i class="fa fa-eye"></i>
                    </button>` : '';
        const accionValidar = String(key || '').toLowerCase() === 'aprobados' ? '' : `
            <button type="button" class="btn btn-sm btn-primary" data-aev-no-row="1"
                    onclick="event.stopPropagation(); aevValidarAbrir(${+item.id_credito}, { origen: '${aeEsc(String(key || 'bandeja'))}' })"
                    title="Validar evidencias" aria-label="Validar evidencias">
                <i class="fa fa-clipboard-check"></i>
            </button>`;

        return `
        <tr>
            <td class="ae-table-main">
                <span class="ae-table-folio">${folio}</span>
                <span class="ae-table-credit"># ${aeEsc(String(item.id_credito))}</span>
                <span class="ae-table-main-client"><i class="fa-solid fa-user"></i>${cliente}</span>
            </td>
            <td class="ae-table-gestor">
                <span class="ae-table-gestor-name"><i class="fa-solid fa-user-tie"></i>${gestor}</span>
                <span class="ae-table-legacy-label"><i class="fa-solid fa-calendar-days"></i>DICTAMINADO EN LEGACY</span>
                <span class="ae-table-legacy-date">${fechaDictamenLegacy}</span>
            </td>
            <td class="ae-table-evidence">${aeRenderEstadoEvidencias(item, key)}</td>
            <td class="ae-table-action">
                <div class="ae-action-buttons">
                    ${accionVer}
                    ${accionValidar}
                </div>
            </td>
        </tr>`;
    }

    function aeRenderTabla(datos, key) {
        const tableId = 'ae-tabla-' + key;
        const filas = datos.map(function (item) { return aeRenderFilaTabla(item, key); }).join('');
        return `
        <div class="card-datatable ae-table-wrap">
            <table id="${aeEsc(tableId)}" class="dt-responsive table border-top ae-table">
                <thead>
                    <tr>
                        <th>Operacion</th>
                        <th>Gestor</th>
                        <th>Evidencias</th>
                        <th class="ae-table-action">Acciones</th>
                    </tr>
                </thead>
                <tbody>${filas}</tbody>
            </table>
        </div>`;
    }

    function aeInicializarDataTable(key) {
        const tableId = '#ae-tabla-' + key;
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable || !document.querySelector(tableId)) return;
        if (jQuery.fn.DataTable.isDataTable(tableId)) {
            jQuery(tableId).DataTable().destroy();
        }

        jQuery(tableId).DataTable({
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [],
            responsive: true,
            autoWidth: false,
            language: {
                decimal: "",
                emptyTable: "No hay operaciones registradas",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                thousands: ",",
                lengthMenu: "Mostrar _MENU_ registros",
                loadingRecords: "Cargando...",
                processing: "Procesando...",
                search: "Buscar:",
                zeroRecords: "No se encontraron resultados",
                paginate: {
                    first: "&laquo;",
                    last: "&raquo;",
                    next: "&rsaquo;",
                    previous: "&lsaquo;"
                }
            },
            dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row align-items-center mt-3 ae-dt-footer"<"col-sm-12 col-md-5 ae-dt-info"i><"col-sm-12 col-md-7 ae-dt-pages"p>>',
            drawCallback: function() {
                jQuery(tableId + '_paginate > .pagination').addClass('pagination-sm justify-content-end');
                jQuery(tableId + '_length select').addClass('form-select form-select-sm');
                jQuery(tableId + '_filter input').addClass('form-control form-control-sm');
            }
        });
    }

    function aeRefrescarTabla(key) {
        const listaId = 'ae-lista-' + (key === 'bandeja' ? 'bandeja' : key);
        const lista = document.getElementById(listaId);
        if (!lista) return;
        const datos = Array.isArray(_aeDatos[key]) ? _aeDatos[key] : [];
        lista.innerHTML = aeRenderTabla(datos, key);
        aeInicializarDataTable(key);
    }

    window.aevValidarAbrir = function (idCredito, opciones) {
        const id = parseInt(idCredito, 10);
        if (!id) return;
        const soloLectura = !!(opciones && opciones.soloLectura);
        const origen = opciones && opciones.origen ? String(opciones.origen) : 'bandeja';
        aevCerrarVistaOverlay();
        aevSetModoLectura(soloLectura);
        aevSetContextoModal(origen);
        aevReiniciarStore(null, id);
        aevSincroBtnEnviar();
        const tit = document.getElementById('aev-titulo-cliente');
        const body = document.getElementById('aev-body');
        if (tit) tit.textContent = 'Crédito #' + id;
        if (body) {
            body.innerHTML = aevRenderCargaRapidaDetalle(id);
        }
        const mEl = document.getElementById('modalAevValidarEvidencias');
        if (mEl && window.bootstrap) {
            (new bootstrap.Modal(mEl)).show();
        }

        aevObtenerDetalleCredito(id, false)
            .then(data => {
                if (!data.success) {
                    body.innerHTML = '<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>' + aeEsc(data.message || 'Error al cargar') + '</div>';
                    aevReiniciarStore(null, id);
                    aevSincroBtnEnviar();
                    return;
                }
                const det = data.detalle;
                aevNormalizarUrlsDetalle(det);
                aevReiniciarStore(det, id);
                const nom = (det && det.nombre_cliente) ? String(det.nombre_cliente).trim() : '';
                if (tit) {
                    const cred = det && det.id_credito ? String(det.id_credito).trim() : String(id);
                    tit.textContent = (nom || (det && det.folio ? 'Folio ' + det.folio : 'Crédito')) + ' (' + cred + ')';
                }
                if (body) {
                    body.innerHTML = aevRenderCuerpoModalValidar(det);
                    aevSanearDomUrls(body);
                }
                aevSincroBtnEnviar();
            })
            .catch(function () {
                aevReiniciarStore(null, id);
                aevSincroBtnEnviar();
                if (body) {
                    body.innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-wifi me-1"></i>Error de red al cargar el detalle de evidencias.</div>';
                }
            });
    };

    function aeSetBadgeTab(key, n) {
        const el = document.getElementById(AE_BADGE_TAB[key]);
        if (!el) return;
        const num = Math.max(0, parseInt(n, 10) || 0);
        if (num > 0) {
            el.textContent = String(num);
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    /** Misma regla que cada lista; muestra los tres totales sin abrir cada pestaña. */
    function aeCargarConteosPestanas() {
        return fetch('/AtencionClientes/obtenerConteosEvidencias', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.conteos) return;
                const c = data.conteos;
                aeSetBadgeTab('bandeja', c.bandeja);
                aeSetBadgeTab('aprobados', c.aprobados);
                aeSetBadgeTab('correcciones', c.correcciones);
            })
            .catch(function () { /* silencioso: los badges se actualizan al entrar a cada pestaña */ });
    }

    function aeCargarSeccion(key, forzar) {
        const cfg = AE_CONFIG[key];
        if (!cfg) return Promise.resolve();

        const idSuffix  = (key === 'bandeja' ? 'bandeja' : key);
        const loaderId  = 'ae-loader-' + idSuffix;
        const listaId   = 'ae-lista-'  + idSuffix;

        if (!forzar && _aeCargada[key]) {
            return Promise.resolve();
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return Promise.resolve();

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('ae-lista-updating');
        }

        return fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    if (key === 'correcciones') {
                        _aeDatos[key] = [];
                        aeRefrescarTabla(key);
                        aeSetBadgeTab(key, 0);
                        _aeCargada[key]   = true;
                        return;
                    }
                    throw new Error(data.message || 'Error al cargar');
                }

                const datos = Array.isArray(data.datos) ? data.datos.slice() : [];
                if (key === 'aprobados') {
                    datos.sort(function (a, b) {
                        const fa = Date.parse(String(a.fecha_aprobacion_evidencias_orden || '').replace(' ', 'T')) || 0;
                        const fb = Date.parse(String(b.fecha_aprobacion_evidencias_orden || '').replace(' ', 'T')) || 0;
                        return fb - fa;
                    });
                }

                const n = datos.length;
                _aeDatos[key] = datos;
                aeSetBadgeTab(key, n);
                _aeCargada[key] = true;

                aeRefrescarTabla(key);
                if (n > 0) {
                    aevPrecargarBandejaVisible(datos);
                }
            })
            .catch(err => {
                if (key === 'correcciones') {
                    _aeDatos[key] = [];
                    aeRefrescarTabla(key);
                    _aeCargada[key]  = true;
                } else {
                    lista.innerHTML = `<div class="alert alert-danger">${aeEsc(err.message)}</div>`;
                }
                aeSetBadgeTab(key, 0);
            })
            .finally(() => {
                loader.style.display = 'none';
                lista.classList.remove('ae-lista-updating');
            });
    }

    /** Carga inicial paralela (badges + bandeja) con el mismo Swal que Retenciones. */
    function aeCargarVistaInicialConSpinner() {
        const hasSwal = typeof Swal !== 'undefined';
        if (hasSwal) {
            Swal.fire({
                title: 'Cargando Evidencias…',
                html: '<span style="font-size:.875rem;color:#64748b;">Obteniendo bandeja de entrada</span>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); },
            });
        }
        Promise.allSettled([
            aeCargarSeccion('bandeja', true),
            aeCargarConteosPestanas()
        ]).finally(function () {
            if (hasSwal) Swal.close();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        aevAsegurarOverlayDentroModal();
        aeCargarVistaInicialConSpinner();

        document.getElementById('ae-tab-bandeja-btn').addEventListener('shown.bs.tab', function () {
            aeCargarSeccion('bandeja', false);
        });
        document.getElementById('ae-tab-aprobados-btn').addEventListener('shown.bs.tab', function () {
            aeCargarSeccion('aprobados', false);
        });
        document.getElementById('ae-tab-correcciones-btn').addEventListener('shown.bs.tab', function () {
            aeCargarSeccion('correcciones', false);
        });

        const aevBody = document.getElementById('aev-body');
        if (aevBody) {
            aevBody.addEventListener('click', function (ev) {
                const replGestor = ev.target.closest('[data-aev-reemplazar-gestor]');
                if (replGestor) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    if (_aevStore.soloLectura) return;
                    aevAbrirReemplazoGestor(
                        replGestor.getAttribute('data-aev-reemplazar-gestor'),
                        replGestor.getAttribute('data-aev-reemplazar-lbl') || ''
                    );
                    return;
                }
                const verRep = ev.target.closest('[data-aev-ver-repuve]');
                if (verRep) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    aevAbrirVistaRepuvePdf();
                    return;
                }
                const verEl = ev.target.closest('[data-aev-ver]');
                if (verEl) {
                    const slot = verEl.getAttribute('data-aev-ver');
                    const lbl  = verEl.getAttribute('data-aev-lbl') || slot;
                    aevAbrirVistaEvidencia(slot, lbl);
                    return;
                }
                const sub = ev.target.closest('[data-aev-subir]');
                if (sub) {
                    if (_aevStore.soloLectura) return;
                    const sk = sub.getAttribute('data-aev-subir');
                    if (sk === 'doc_repuve' && _aevStore.det && aevImagenesTodasAceptadas(_aevStore.det.evidencias)) {
                        const inp = document.getElementById('aev-inp-repuve');
                        if (inp) { inp.value = ''; inp.click(); }
                    }
                }
            });
        }
        const aevInpReemplazo = document.getElementById('aev-inp-reemplazo-gestor');
        if (aevInpReemplazo) {
            aevInpReemplazo.addEventListener('change', function (ev) {
                const f = ev.target && ev.target.files && ev.target.files[0];
                if (ev.target) { ev.target.value = ''; }
                if (!f) return;
                aevSubirReemplazoGestor(f);
            });
        }
        const aevInpRepuve = document.getElementById('aev-inp-repuve');
        if (aevInpRepuve) {
            aevInpRepuve.addEventListener('change', function (ev) {
                const f = ev.target && ev.target.files && ev.target.files[0];
                if (ev.target) { ev.target.value = ''; }
                if (!f || !_aevStore.det || !_aevStore.det.id) return;
                if (f.type && f.type !== 'application/pdf' && !/\.pdf$/i.test(f.name)) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', text: 'Repuve: solo se acepta PDF.' });
                    } else { window.alert('Solo PDF.'); }
                    return;
                }
                const fd = new FormData();
                fd.append('id_operacion', String(_aevStore.det.id));
                fd.append('slot', 'doc_repuve');
                fd.append('archivo', f, f.name);
                const body = document.getElementById('aev-body');
                if (body) { body.insertAdjacentHTML('afterbegin', '<div class="aev-load-overlay" id="aev-subida-overlay" style="position:absolute;inset:0;background:rgba(255,255,255,.6);z-index:5;display:flex;align-items:center;justify-content:center;font-size:.9rem;">Subiendo…</div>'); }
                fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        const o = document.getElementById('aev-subida-overlay');
                        if (o) o.remove();
                        if (!data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', text: data.message || 'Error al subir' });
                            } else { window.alert(data.message || 'Error al subir'); }
                            return;
                        }
                        return fetch('/MotosAdjudicadas/obtenerDetalle/' + parseInt(_aevStore.det.id, 10) + '?incluir_todas=1', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                            .then(r2 => r2.json());
                    })
                    .then(function (j) {
                        if (!j || !j.success || !j.detalle) return;
                        aevNormalizarUrlsDetalle(j.detalle);
                        aevReiniciarStore(j.detalle, _aevStore.idCredito);
                        const body2 = document.getElementById('aev-body');
                        if (body2) {
                            body2.innerHTML = aevRenderCuerpoModalValidar(_aevStore.det);
                            aevSanearDomUrls(body2);
                        }
                        aevSincroBtnEnviar();
                    })
                    .catch(function () {
                        const o2 = document.getElementById('aev-subida-overlay');
                        if (o2) o2.remove();
                    });
            });
        }
        const ovl = document.getElementById('aev-vista-overlay');
        if (ovl) {
            ovl.addEventListener('click', function (ev) {
                if (ev.target === ovl) aevCerrarVistaOverlay();
            });
        }
        const btnC = document.getElementById('aev-vista-btn-cerrar');
        if (btnC) btnC.addEventListener('click', function () { aevCerrarVistaOverlay(); });
        const btnPrev = document.getElementById('aev-vista-prev');
        if (btnPrev) btnPrev.addEventListener('click', function () { aevNavegarVista(-1); });
        const btnNext = document.getElementById('aev-vista-next');
        if (btnNext) btnNext.addEventListener('click', function () { aevNavegarVista(1); });
        const btnA = document.getElementById('aev-vista-aceptar');
        if (btnA) btnA.addEventListener('click', function () { aevAplicarVeredictoDesdeVista('acep'); });
        const btnR = document.getElementById('aev-vista-rechazar');
        if (btnR) btnR.addEventListener('click', function () { aevAplicarVeredictoDesdeVista('rec'); });
        document.addEventListener('keydown', function (ev) {
            const o = document.getElementById('aev-vista-overlay');
            if (!o || o.classList.contains('d-none')) return;
            const tag = ev.target && ev.target.tagName ? String(ev.target.tagName).toLowerCase() : '';
            const typing = tag === 'textarea' || tag === 'input' || tag === 'select';
            if (ev.key === 'Escape') {
                aevCerrarVistaOverlay();
                return;
            }
            if (!typing && ev.key === 'ArrowLeft') {
                ev.preventDefault();
                aevNavegarVista(-1);
                return;
            }
            if (!typing && ev.key === 'ArrowRight') {
                ev.preventDefault();
                aevNavegarVista(1);
            }
        });

        const mAev = document.getElementById('modalAevValidarEvidencias');
        if (mAev) {
            mAev.addEventListener('hidden.bs.modal', function () {
                aevCerrarVistaOverlay();
                aevOnModalCerrarValidar();
            });
        }

        const btnEnviar = document.getElementById('aev-btn-enviar');
        if (btnEnviar) {
            btnEnviar.addEventListener('click', function () {
                if (!_aevStore.det || !_aevStore.det.id) return;
                if (aevHayVeredictosPendientes()) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'info', title: 'Guardando validaciones', text: 'Espera un momento y vuelve a enviar.' });
                    }
                    aevSincroBtnEnviar();
                    return;
                }
                const opId = parseInt(_aevStore.det.id, 10);
                if (opId <= 0) return;
                btnEnviar.disabled = true;
                fetch('/MotosAdjudicadas/enviarEvidenciasValidadasAtencion', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body:    JSON.stringify({ id_operacion: opId }),
                    credentials: 'same-origin'
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        btnEnviar.disabled = false;
                        if (!data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: 'No se pudo enviar', text: data.message || 'Revisa que todo esté validado.' });
                            } else {
                                window.alert(data.message || 'No se pudo enviar.');
                            }
                            return;
                        }
                        aePostEnviarEvidenciasValidadas();
                        const mEl = document.getElementById('modalAevValidarEvidencias');
                        if (mEl && window.bootstrap) {
                            const inst = bootstrap.Modal.getInstance(mEl);
                            if (inst) inst.hide();
                        }
                        if (typeof spartaSwalEnviadoOk === 'function') {
                            spartaSwalEnviadoOk('Evidencias enviadas correctamente.');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Enviado',
                                text: 'Evidencias enviadas correctamente.',
                                confirmButtonColor: '#0f172a',
                            });
                        }
                    })
                    .catch(function () {
                        btnEnviar.disabled = false;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo enviar.' });
                        }
                    });
            });
        }
    });
})();
</script>
