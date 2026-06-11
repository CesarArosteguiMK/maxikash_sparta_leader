<style>
.acd-header-gradient {
    background: linear-gradient(135deg, #9a3412 0%, #ea580c 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.acd-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.acd-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.9; color: #fff; }
.acd-header-gradient i  { color: #fff; }


.ac-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.ac-card:hover { box-shadow: 0 4px 18px rgba(234,88,12,.14); }
.ac-card.acd-card-dict {
    border-color: #e5e7eb;
    background: #fff;
}

.ac-card-header {
    background: #fff;
    border-bottom: 1px solid #eef2f7;
    padding: .62rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.ac-card-header .ac-credito-id {
    color: #1f2937;
    font-weight: 700;
    font-size: .92rem;
    letter-spacing: .1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1;
}
.ac-card-header .ac-credito-id small {
    font-weight: 600;
    font-size: .7rem;
    color: #6b7280;
    opacity: 1;
    margin-left: .35rem;
}

.ac-card-body {
    padding: .62rem .9rem;
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
.ae-list-nombre .ac-val { text-transform: uppercase; }
.ae-list-status .ac-val { font-weight: 700; }
.ae-list-muted {
    color: #9ca3af;
    font-style: italic;
    font-weight: 500;
}
.ae-list-action {
    min-width: 142px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ac-detail-row {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .2rem;
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

.acd-btn-pipeline {
    background: linear-gradient(135deg, #9a3412 0%, #ea580c 100%);
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: .85rem;
    padding: .45rem 1.4rem;
    border-radius: 2rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    text-decoration: none;
    transition: opacity .2s, transform .15s;
}
.acd-btn-pipeline:hover  { opacity: .9; transform: translateY(-1px); color: #fff; }
.acd-btn-pipeline:active { transform: translateY(0); }

.acd-table-wrap {
    border: 1px solid #e5e7eb;
    border-radius: .75rem;
    overflow: visible;
    background: #fff;
    padding: 0;
}
.acd-table {
    margin: 0;
    font-size: .875rem;
    vertical-align: middle;
}
.acd-table thead th {
    background: #f8fafc;
    color: #566a7f;
    border-bottom: 1px solid #dbe4ef;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .02em;
    text-transform: uppercase;
    white-space: nowrap;
}
.acd-table tbody tr:hover { background: #f8fbff; }
.acd-table td {
    color: #697a8d;
    border-color: #e8eef5;
}
.acd-table-main { min-width: 260px; }
.acd-table-folio {
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
.acd-table-credit {
    display: block;
    margin-top: .22rem;
    color: #566a7f;
    font-weight: 700;
}
.acd-table-main-client {
    display: flex;
    align-items: flex-start;
    gap: .34rem;
    margin-top: .28rem;
    color: #697a8d;
    font-weight: 800;
    text-transform: uppercase;
    line-height: 1.18;
    white-space: normal;
}
.acd-table-main-client i {
    width: .85rem;
    flex: 0 0 .85rem;
    margin-top: .08rem;
    color: #697a8d;
    font-size: .72rem;
}
.acd-table-name {
    min-width: 210px;
    color: #697a8d;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.2;
}
.acd-table-muted {
    color: #94a3b8;
    font-style: italic;
}
.acd-table-action {
    width: 92px;
    min-width: 92px;
    text-align: center !important;
}
.acd-table-date {
    white-space: nowrap;
    line-height: 1.35;
}
.acd-table-gestor {
    min-width: 270px;
}
.acd-table-gestor-name {
    display: flex;
    align-items: flex-start;
    gap: .34rem;
    color: #697a8d;
    font-weight: 700;
    line-height: 1.18;
}
.acd-table-legacy-label {
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
.acd-table-legacy-date {
    display: block;
    margin-top: .12rem;
    color: #566a7f;
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.15;
}
.acd-table-operacion {
    min-width: 245px;
}
.acd-op-time {
    display: block;
    color: #64748b;
    line-height: 1.18;
}
.acd-op-time-label {
    display: inline-flex;
    align-items: center;
    gap: .34rem;
    color: #64748b;
    font-size: .68rem;
    font-weight: 800;
    line-height: 1.1;
    text-transform: uppercase;
    letter-spacing: .025em;
}
.acd-op-time strong {
    display: inline-block;
    margin-top: 0;
    margin-left: .42rem;
    color: #566a7f;
    font-size: .86rem;
    font-weight: 800;
    white-space: nowrap;
}
.acd-op-time small {
    display: block;
    margin-top: .12rem;
    color: #94a3b8;
    font-size: .72rem;
    font-weight: 700;
    line-height: 1.2;
}
.acd-action-buttons {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center !important;
    gap: .5rem;
    flex-wrap: wrap;
}
#acdTabContent .card-datatable {
    padding: 1.5rem;
}
#acdTabContent .dataTables_length,
#acdTabContent .dt-length { margin-bottom: 1rem; }
#acdTabContent .dataTables_filter {
    margin-bottom: 1rem;
    text-align: right;
}
#acdTabContent .dt-search {
    margin-bottom: 1rem;
    text-align: right;
}
#acdTabContent .dataTables_filter input,
#acdTabContent .dt-search input {
    margin-left: .5rem;
    padding: .375rem .75rem;
    border: 1px solid #d9dee3;
    border-radius: .375rem;
}
#acdTabContent .dataTables_filter input:focus,
#acdTabContent .dt-search input:focus {
    border-color: #ea580c;
    outline: none;
    box-shadow: 0 0 0 .2rem rgba(234, 88, 12, .15);
}
#acdTabContent .dataTables_length select,
#acdTabContent .dt-length select {
    margin: 0 .5rem;
    padding: .375rem 1.75rem .375rem .75rem;
}
#acdTabContent .dataTables_info,
#acdTabContent .dt-info {
    margin: 0;
    color: #6c757d;
    font-size: .85rem;
}
#acdTabContent .acd-dt-footer {
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
#acdTabContent .acd-dt-footer > [class*="col-"] {
    padding-left: 0;
    padding-right: 0;
}
#acdTabContent .acd-dt-info {
    flex: 1 1 auto !important;
    width: auto !important;
    max-width: none !important;
}
#acdTabContent .acd-dt-pages {
    flex: 0 0 auto !important;
    width: auto !important;
    max-width: none !important;
    margin-left: auto;
    display: flex !important;
    justify-content: flex-end !important;
}
#acdTabContent .acd-dt-footer .dataTables_paginate,
#acdTabContent .acd-dt-footer .dt-paging {
    display: flex;
    justify-content: flex-end;
    width: auto;
    margin: 0 !important;
}
#acdTabContent .acd-dt-footer .dataTables_paginate .pagination,
#acdTabContent .acd-dt-footer .dt-paging .pagination {
    justify-content: flex-end !important;
    margin: 0 !important;
    margin-left: auto !important;
    gap: 0;
}
#acdTabContent .acd-dt-footer .dataTables_paginate .page-item,
#acdTabContent .acd-dt-footer .dt-paging .page-item {
    margin: 0 .18rem;
}
#acdTabContent .page-link {
    border-radius: .375rem;
    border-color: #e5e7eb;
    color: #6b7280;
    min-width: 2.35rem;
    text-align: center;
}
#acdTabContent .page-item.active .page-link {
    background: #ea580c;
    border-color: #ea580c;
    color: #fff;
}
.acd-dict-detail-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
}
.acd-dict-chip {
    border: 1px solid #e5e7eb;
    border-radius: .65rem;
    padding: .7rem .85rem;
    background: #f8fafc;
    min-width: 0;
}
.acd-dict-chip span {
    display: block;
    color: #64748b;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.acd-dict-chip strong {
    display: block;
    color: #0f172a;
    font-size: .86rem;
    margin-top: .18rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.acd-dict-observacion {
    border-left: 4px solid #ea580c;
    background: #fff7ed;
    border-radius: .65rem;
    padding: .75rem .9rem;
    color: #475569;
    white-space: pre-line;
}

/* Forzar tono de tabs al color propio de Cartera (paso 3) */
#acdTabNav .nav-link {
    color: #0f172a;
}
#acdTabNav .nav-link.active {
    background-color: #ea580c !important;
    border-color: #ea580c !important;
    color: #fff !important;
}
#acdTabNav .nav-link:hover:not(.active),
#acdTabNav .nav-link:focus:not(.active) {
    color: #ea580c;
}

body.dark-mode .ac-card              { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-card.acd-card-dict { background: #111827; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-header       { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-card-header .ac-credito-id { color: #e2e8f0; }
body.dark-mode .ac-card-header .ac-credito-id small { color: #94a3b8; }
body.dark-mode .ac-card-footer       { background: #111827; border-color: #1f2937; }
body.dark-mode .ae-list-cell .ac-lbl { color: #94a3b8; }
body.dark-mode .ae-list-cell .ac-val { color: #e2e8f0; }
body.dark-mode .ae-list-muted { color: #64748b; }
body.dark-mode .ae-main-folio { color: #fcd34d; }
body.dark-mode .ae-main-credito { color: #e2e8f0; }
body.dark-mode .acd-table-wrap { background: #111827; border-color: #1f2937; }
body.dark-mode .acd-table thead th { background: #0f172a; color: #e2e8f0; border-color: #1f2937; }
body.dark-mode .acd-table tbody tr:hover { background: #172033; }
body.dark-mode .acd-table td { color: #cbd5e1; border-color: #1f2937; }
body.dark-mode .acd-table-credit,
body.dark-mode .acd-table-main-client,
body.dark-mode .acd-table-name { color: #e2e8f0; }
body.dark-mode .acd-table-main-client i { color: #94a3b8; }
body.dark-mode .acd-table-gestor-name,
body.dark-mode .acd-table-legacy-date { color: #e2e8f0; }
body.dark-mode .acd-table-legacy-label { color: #94a3b8; border-color: #1f2937; }
body.dark-mode .acd-table-gestor-name i,
body.dark-mode .acd-table-legacy-label i { color: #94a3b8; }
body.dark-mode .acd-op-time-label,
body.dark-mode .acd-op-time small { color: #94a3b8; }
body.dark-mode .acd-op-time strong { color: #e2e8f0; }
body.dark-mode .acd-dict-chip { background: #111827; border-color: #1f2937; }
body.dark-mode .acd-dict-chip strong { color: #e2e8f0; }
body.dark-mode .acd-dict-observacion { background: #292524; color: #fed7aa; }

@media (max-width: 991.98px) {
    .ae-list-grid {
        grid-template-columns: repeat(2, minmax(220px, 1fr));
    }
}
@media (max-width: 767.98px) {
    .ac-card-body {
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
    .acd-dict-detail-grid {
        grid-template-columns: 1fr;
    }
    .acd-form-cols {
        grid-template-columns: 1fr;
    }
    .acd-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    #acdTabContent .acd-dt-footer {
        justify-content: center !important;
        text-align: center;
    }
    #acdTabContent .acd-dt-info,
    #acdTabContent .acd-dt-pages {
        flex: 0 0 100% !important;
        width: 100% !important;
        margin-left: 0;
        justify-content: center !important;
    }
    #acdTabContent .acd-dt-footer .dataTables_paginate,
    #acdTabContent .acd-dt-footer .dt-paging {
        justify-content: center;
        width: 100%;
    }
    #acdTabContent .acd-dt-footer .dataTables_paginate .pagination,
    #acdTabContent .acd-dt-footer .dt-paging .pagination {
        justify-content: center !important;
        margin-left: 0 !important;
    }
}

.acd-lista-updating {
    opacity: 0.5;
    transition: opacity 0.12s ease;
    pointer-events: none;
}

/* Modal Cierre documentación (vista 4) */
#modalAcdCierreDocumentacion .modal-header,
#modalAcdDictaminadoDetalle .modal-header {
    background: #fff !important;
    color: #0f172a !important;
    padding: .85rem 1.15rem;
    border: none !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
#modalAcdCierreDocumentacion .modal-dialog.modal-xl,
#modalAcdDictaminadoDetalle .modal-dialog.modal-xl {
    max-width: min(72rem, 98vw);
}
#modalAcdCierreDocumentacion .acd-cierre-banner {
    background: linear-gradient(135deg, #c7d2fe 0%, #e9d5ff 100%);
    border-radius: 0.75rem;
    padding: 0.85rem 1.25rem;
    margin: -1rem -1rem 1rem -1rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    color: #312e81;
    font-weight: 700;
    font-size: 0.95rem;
}
#modalAcdCierreDocumentacion .acd-cierre-banner i { font-size: 1.25rem; opacity: 0.9; }
.acd-modal-context-title {
    color: #1f2937;
    font-weight: 900;
    line-height: 1.2;
}
.acd-modal-context-title .acd-modal-context-client {
    font-weight: 500;
}
.acd-modal-context-subtitle {
    color: #1f2937;
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.25;
}
.acd-modal-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 270px;
    gap: .85rem;
    align-items: start;
}
.acd-modal-main { min-width: 0; }
.acd-form-wrap {
    border: 1px solid #dbe4ef;
    border-radius: .65rem;
    background: #f8fafc;
    padding: .65rem .8rem;
    margin-bottom: .85rem;
}
.acd-form-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .5rem;
}
.acd-form-title {
    color: #123150;
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .035em;
}
.acd-form-date {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .18rem .65rem;
    border: 1px solid #fed7aa;
    border-radius: 999px;
    background: #fff7ed;
    color: #c2410c;
    font-size: .72rem;
    font-weight: 800;
    white-space: nowrap;
}
.acd-form-cols {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
    gap: .7rem 1rem;
}
.acd-form-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .38rem .55rem;
}
.acd-form-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: .38rem;
}
.acd-form-field {
    min-width: 0;
    border-bottom: 1px solid #dbe4ef;
    padding-bottom: .25rem;
}
.acd-form-field-wide,
.acd-form-field-series {
    grid-column: span 2;
}
.acd-form-field-head {
    display: flex;
    align-items: center;
    gap: .28rem;
    color: #64748b;
    font-size: .67rem;
    font-weight: 800;
    letter-spacing: .04em;
    line-height: 1.1;
    text-transform: uppercase;
}
.acd-form-value {
    display: flex;
    align-items: center;
    gap: .28rem;
    color: #1f2937;
    font-size: .82rem;
    font-weight: 800;
    line-height: 1.18;
    margin-top: .08rem;
    overflow: hidden;
}
.acd-form-value span:last-child {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.acd-form-color-dot {
    width: .72rem;
    height: .72rem;
    border-radius: 999px;
    border: 1px solid rgba(15,23,42,.18);
    flex: 0 0 auto;
}
@media (max-width: 767.98px) {
    .acd-form-cols {
        grid-template-columns: 1fr;
    }
    .acd-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
#acdCierreContenido {
    align-items: stretch;
}
#acdCierreContenido > [class*="col-"] {
    display: flex;
}
#acdCierreContenido > [class*="col-"] > * {
    width: 100%;
}

.acd-cierre-etapas-box {
    border: 2px solid #22c55e;
    border-radius: 0.75rem;
    padding: 1rem 1rem 0.75rem;
    background: #fff;
    min-height: 280px;
}
.acd-cierre-etapas-lbl {
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: #16a34a;
    display: block;
    margin-bottom: 0.65rem;
}
.acd-cierre-etapa-fila {
    border: 2px solid #22c55e;
    border-radius: 0.5rem;
    padding: 0.55rem 0.75rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #14532d;
}
.acd-cierre-etapa-fila .badge { font-size: 0.68rem; }
.acd-cierre-btn-coment {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    white-space: nowrap;
}

.acd-cierre-s2-box {
    border: 3px solid #22c55e;
    border-radius: 0.75rem;
    padding: 1.25rem;
    background: #f0fdf4;
    min-height: 150px;
    max-width: none;
    margin: 0;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.acd-cierre-s2-text {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #14532d;
    text-align: center;
    margin-bottom: 1rem;
    line-height: 1.35;
}
body.dark-mode .acd-cierre-etapas-box,
body.dark-mode #modalAcdCierreDocumentacion .modal-content { background: #1e293b; color: #e2e8f0; }
body.dark-mode .acd-modal-context-title,
body.dark-mode .acd-modal-context-subtitle { color: #e2e8f0; }
body.dark-mode .acd-form-wrap { background: #0f172a; border-color: #1f2937; }
body.dark-mode .acd-form-title,
body.dark-mode .acd-form-value { color: #e2e8f0; }
body.dark-mode .acd-form-field,
body.dark-mode .acd-form-date { border-color: #334155; }
body.dark-mode .acd-form-field-head { color: #94a3b8; }
body.dark-mode .acd-cierre-etapa-fila { background: #0f172a; color: #bbf7d0; border-color: #22c55e; }
body.dark-mode .acd-cierre-s2-box { background: #052e16; border-color: #4ade80; }
body.dark-mode .acd-cierre-s2-text { color: #bbf7d0; }
body.dark-mode #modalAcdCierreDocumentacion .acd-cierre-banner {
    background: linear-gradient(135deg, #4338ca 0%, #7c3aed 100%);
    color: #eef2ff;
}
body.dark-mode #modalAcdCierreVerBitacoraEtapa .modal-content { background: #1e293b; color: #e2e8f0; }

/* Bitácora por etapa — timeline (modal secundario vista 4) */
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl {
    list-style: none;
    margin: 0;
    padding: 0.35rem 0.25rem 0.5rem 0;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-item {
    position: relative;
    padding: 0 0 1.35rem 1.65rem;
    margin: 0;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-item:last-child {
    padding-bottom: 0;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-item::before {
    content: '';
    position: absolute;
    left: 0.4rem;
    top: 1rem;
    bottom: 0;
    width: 0;
    border-left: 2px dashed #cbd5e1;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-item:last-child::before {
    display: none;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-dot {
    position: absolute;
    left: 0;
    top: 0.12rem;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    box-sizing: border-box;
    background: #fff;
    border: 3px solid currentColor;
    box-shadow: 0 0 0 3px #fff;
    z-index: 1;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-content {
    min-width: 0;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-headrow {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.25rem;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-title {
    font-size: 0.82rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    flex: 1;
    min-width: 0;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-time {
    font-size: 0.68rem;
    font-weight: 600;
    color: #64748b;
    white-space: nowrap;
    flex-shrink: 0;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-tl-user {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 600;
}
#modalAcdCierreVerBitacoraEtapa .acd-bit-dot--0 { color: #7c3aed; }
#modalAcdCierreVerBitacoraEtapa .acd-bit-dot--1 { color: #16a34a; }
#modalAcdCierreVerBitacoraEtapa .acd-bit-dot--2 { color: #0891b2; }
#modalAcdCierreVerBitacoraEtapa .acd-bit-dot--3 { color: #ea580c; }
.acd-bitacora-full {
    position: sticky;
    top: .35rem;
    border: 1px solid #dbe4ef;
    border-radius: .75rem;
    background: #fff;
    max-height: 66vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 0;
    margin-top: 0;
}
.acd-bitacora-full-title {
    display: flex;
    align-items: center;
    gap: .4rem;
    color: #123150;
    font-size: .78rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin: 0;
    padding: .68rem .75rem .16rem;
    background: #f8fafc;
}
.acd-bitacora-full-sub {
    color: #64748b;
    font-size: .68rem;
    font-weight: 700;
    padding: 0 .75rem .68rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.acd-bitacora-full .acd-bit-tl {
    list-style: none;
    margin: 0;
    padding: .7rem .75rem;
    overflow: auto;
}
.acd-bitacora-full .acd-bit-tl-item {
    position: relative;
    padding: 0 0 .72rem .9rem;
    margin: 0;
    border-left: 2px solid #dbe4ef;
}
.acd-bitacora-full .acd-bit-tl-item:last-child { padding-bottom: 0; }
.acd-bitacora-full .acd-bit-tl-item::before {
    content: "";
    position: absolute;
    left: -.32rem;
    top: .08rem;
    width: .55rem;
    height: .55rem;
    border-radius: 999px;
    background: #ea580c;
    box-shadow: 0 0 0 3px #fff7ed;
}
.acd-bitacora-full .acd-bit-tl-dot { display: none; }
.acd-bitacora-full .acd-bit-tl-headrow {
    display: block;
    margin-bottom: .18rem;
}
.acd-bitacora-full .acd-bit-tl-title {
    display: block;
    color: #1e293b;
    font-size: .72rem;
    font-weight: 850;
    line-height: 1.2;
    text-transform: none;
    letter-spacing: 0;
}
.acd-bitacora-full .acd-bit-tl-time,
.acd-bitacora-full .acd-bit-tl-user {
    display: block;
    color: #64748b;
    font-size: .66rem;
    font-weight: 750;
    line-height: 1.18;
}
.acd-bitacora-full .acd-bit-tl-user { margin-top: .18rem; }
.acd-bitacora-empty {
    color: #94a3b8;
    font-size: .78rem;
    text-align: center;
    margin: 0;
    padding: .85rem;
}
body.dark-mode #modalAcdCierreVerBitacoraEtapa .acd-bit-tl-item::before {
    border-left-color: #475569;
}
body.dark-mode #modalAcdCierreVerBitacoraEtapa .acd-bit-tl-dot {
    background: #1e293b;
    box-shadow: 0 0 0 3px #1e293b;
}
body.dark-mode #modalAcdCierreVerBitacoraEtapa .acd-bit-tl-title { color: #f1f5f9; }
body.dark-mode #modalAcdCierreVerBitacoraEtapa .acd-bit-tl-time,
body.dark-mode #modalAcdCierreVerBitacoraEtapa .acd-bit-tl-user { color: #94a3b8; }
body.dark-mode .acd-bitacora-full { background: #0f172a; border-color: #334155; }
body.dark-mode .acd-bitacora-full-title { color: #e2e8f0; }
body.dark-mode .acd-bitacora-full-title,
body.dark-mode .acd-bitacora-full-sub { background: #0f172a; border-color: #1f2937; }
body.dark-mode .acd-bitacora-full .acd-bit-tl-item { border-color: #334155; }
body.dark-mode .acd-bitacora-full .acd-bit-tl-title { color: #e2e8f0; }
body.dark-mode .acd-bitacora-full .acd-bit-tl-time,
body.dark-mode .acd-bitacora-full .acd-bit-tl-user,
body.dark-mode .acd-bitacora-full-sub { color: #94a3b8; }

/* Bloque documento S2 (modal vista 4) */
#acdEvidenciaCard {
    border: 2px solid #fed7aa;
    border-radius: 0.75rem;
    background: #fffbeb;
    padding: 1rem 1.15rem 1.1rem;
    margin-bottom: 1rem;
    min-height: 280px;
    height: 100%;
    display: flex;
    flex-direction: column;
}
#acdEvidenciaTitulo {
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    color: #9a3412;
    margin: 0 0 0.75rem 0;
}
#acdEvidenciaTitulo.acd-ev-ok {
    color: #15803d;
}
body.dark-mode #acdEvidenciaCard {
    background: #292524;
    border-color: #78350f;
}
body.dark-mode #acdEvidenciaTitulo { color: #fdba74; }
body.dark-mode #acdEvidenciaTitulo.acd-ev-ok { color: #86efac; }
.acd-dict-evidencia-card {
    border: 1px solid #bbf7d0;
    border-radius: .65rem;
    background: #f0fdf4;
    padding: .75rem .85rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.acd-dict-evidencia-main {
    display: flex;
    align-items: center;
    gap: .55rem;
    min-width: 0;
}
.acd-dict-evidencia-main i {
    color: #16a34a;
    font-size: 1.2rem;
}
.acd-dict-evidencia-main strong {
    display: block;
    color: #14532d;
    font-size: .8rem;
    font-weight: 800;
    line-height: 1.15;
}
.acd-dict-evidencia-main small {
    display: block;
    color: #64748b;
    font-size: .7rem;
    font-weight: 700;
    line-height: 1.15;
}
body.dark-mode .acd-dict-evidencia-card { background: #052e16; border-color: #22c55e; }
body.dark-mode .acd-dict-evidencia-main strong { color: #bbf7d0; }
body.dark-mode .acd-dict-evidencia-main small { color: #94a3b8; }
.acd-doc-viewer-overlay {
    position: fixed;
    inset: 0;
    z-index: 10050;
    background: rgba(15, 23, 42, 0.78);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.acd-doc-viewer-overlay.d-none { display: none !important; }
.acd-doc-viewer-panel {
    width: min(52rem, 96vw);
    height: min(42rem, 92vh);
    overflow: hidden;
    background: #fff;
    border-radius: .75rem;
    box-shadow: 0 20px 50px rgba(0,0,0,.35);
    padding: 1rem 1.1rem;
    display: flex;
    flex-direction: column;
}
.acd-doc-viewer-title {
    color: #14532d;
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.2;
}
.acd-doc-viewer-box {
    flex: 1 1 auto;
    min-height: 0;
    height: 28rem;
    background: #0f172a;
    border-radius: .5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: .75rem;
    overflow: hidden;
}
.acd-doc-viewer-box iframe {
    width: 100%;
    height: 100%;
    border: 0;
    background: #fff;
    border-radius: .35rem;
}
.acd-doc-viewer-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transform-origin: center center;
    transition: transform .1s ease-out;
    will-change: transform;
}
.acd-doc-viewer-box--image {
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
}
.acd-doc-viewer-image-wrap {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.acd-doc-viewer-toolbar {
    flex-shrink: 0;
    background: rgba(15, 23, 42, .96);
    border-top: 1px solid rgba(148, 163, 184, .22);
    border-radius: 0 0 .5rem .5rem;
}
@media (max-width: 991.98px) {
    .acd-modal-grid {
        grid-template-columns: 1fr;
    }
    .acd-bitacora-full {
        position: static;
        max-height: none;
    }
}
</style>

<div class="container-fluid py-4">

    <div class="acd-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-file-circle-check fa-2x"></i>
        <div>
            <h4>3.-Cartera</h4>
            <p>Gestión de cartera / cierre de documentación para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-pills flex-column flex-md-row mb-3 gap-md-0 gap-2 border-0" id="acdTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="acd-tab-bandeja-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#acdTabBandeja">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="acd-badge-bandeja" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acd-tab-dictaminado-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#acdTabDictaminado">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Dictaminado
                        <span class="badge bg-label-secondary ms-1" id="acd-badge-dictaminado" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="acdTabContent">
            <div class="tab-pane fade show active" id="acdTabBandeja" role="tabpanel">
                <div id="acd-loader-bandeja" class="text-center py-5 text-muted" style="display:block;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="acd-lista-bandeja"></div>
            </div>

            <div class="tab-pane fade" id="acdTabDictaminado" role="tabpanel">
                <div id="acd-loader-dictaminado" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="acd-lista-dictaminado"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cierre documentado — etapas previas + confirmación S2 -->
<div class="modal fade" id="modalAcdCierreDocumentacion" tabindex="-1" aria-labelledby="modalAcdCierreDocumentacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 border-bottom">
                <h5 class="modal-title mb-0 acd-modal-context-title" id="modalAcdCierreDocumentacionLabel">
                    <i class="fa-solid fa-file-circle-check me-2 text-warning"></i>CIERRE DOCUMENTADO
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-4 pb-3">
                <p class="acd-modal-context-subtitle mb-3" id="acdCierreSubtitulo"></p>
                <div id="acdCierreLoader" class="text-center py-4 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando expediente…
                </div>
                <div class="acd-modal-grid">
                    <div class="acd-modal-main">
                <div id="acdCierreFormularioCapturado"></div>
                <div id="acdCierreContenido" class="row g-3 mt-1">
                    <div class="col-lg-5">
                        <div class="acd-cierre-etapas-box">
                            <span class="acd-cierre-etapas-lbl">ETAPAS</span>
                            <div id="acdCierreEtapasFilas"></div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div id="acdEvidenciaCard">
                            <h2 class="h6" id="acdEvidenciaTitulo">Documento de cierre en S2</h2>
                            <div id="acdEvidenciaFormulario">
                                <p class="small text-muted mb-2">Adjunta una imagen (o PDF) y comentarios opcionales antes de confirmar el registro en S2.</p>
                                <div class="mb-2">
                                    <input type="file" class="form-control form-control-sm" id="acdEvidenciaArchivo"
                                           accept="image/jpeg,image/png,application/pdf">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small mb-1" for="acdEvidenciaComentarios">Comentarios</label>
                                    <textarea class="form-control form-control-sm" id="acdEvidenciaComentarios" rows="3"
                                              placeholder="Detalle relevante para expediente…"></textarea>
                                </div>
                                <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold" id="acdBtnSubirEvidenciaCierre">
                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i>Subir documento
                                </button>
                            </div>
                            <div id="acdEvidenciaExito" style="display:none;" class="text-center py-2">
                                <p class="small text-muted mb-2 mb-md-3">Puedes verificar el archivo antes de marcar la confirmación en S2.</p>
                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill fw-bold" id="acdBtnVerEvidenciaCierre">
                                    <i class="fa-regular fa-eye me-1"></i>Ver
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="acd-cierre-s2-box">
                            <p class="acd-cierre-s2-text mb-0">
                                CONFIRMA AQUÍ QUE EL CIERRE FUE REGISTRADO EN S2 DE FORMA EXITOSA
                            </p>
                            <div class="form-check mx-auto mt-3" style="max-width:22rem;">
                                <input class="form-check-input" type="checkbox" id="acdChkConfirmoS2" autocomplete="off" disabled>
                                <label class="form-check-label small fw-semibold" for="acdChkConfirmoS2">
                                    Confirmo que el cierre quedó registrado correctamente en S2.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                    <div id="acdCierreBitacoraCompleta"></div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success rounded-pill btn-sm fw-bold" id="acdBtnRegistrarConfirmacionS2" disabled>
                    <i class="fa-solid fa-check me-1"></i>Registrar confirmación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal secundario: bitácora filtrada por etapa -->
<div class="modal fade" id="modalAcdCierreVerBitacoraEtapa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 border-bottom">
                <h6 class="modal-title mb-0" id="acdCierreBitModalTitulo">Comentarios / bitácora</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-2" id="acdCierreBitModalBody"></div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: vista de expediente dictaminado -->
<div class="modal fade" id="modalAcdDictaminadoDetalle" tabindex="-1" aria-labelledby="modalAcdDictaminadoDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 border-bottom">
                <h5 class="modal-title mb-0 acd-modal-context-title" id="modalAcdDictaminadoDetalleLabel">
                    <i class="fa-regular fa-eye me-2 text-warning"></i>Expediente dictaminado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="px-3 pt-3">
                <p class="acd-modal-context-subtitle mb-0" id="acdDictaminadoDetalleSubtitulo"></p>
            </div>
            <div class="modal-body" id="acdDictaminadoDetalleBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando expediente...
                </div>
            </div>
            <div class="modal-footer py-2 border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Visor interno: evidencia de cierre documentado -->
<div id="acdDocViewerOverlay" class="acd-doc-viewer-overlay d-none" role="dialog" aria-modal="true" aria-labelledby="acdDocViewerTitle">
    <div class="acd-doc-viewer-panel" tabindex="-1">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <h6 class="acd-doc-viewer-title mb-0" id="acdDocViewerTitle">Evidencia de cierre documentado</h6>
            <button type="button" class="btn btn-sm btn-light border" id="acdDocViewerClose" aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="acd-doc-viewer-box" id="acdDocViewerBox"></div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const ACD_CONFIG = {
        bandeja: {
            url:   '/AtencionClientes/obtenerRecuperacionCierreDocumentado',
            vacio: 'No hay operaciones en bandeja (Cierre documentado).',
        },
        dictaminado: {
            url:   '/AtencionClientes/obtenerDictaminadosCierreDocumentacion',
            vacio: 'No hay operaciones dictaminadas con expediente en esta etapa.',
        },
    };

    const ACD_BADGE = { bandeja: 'acd-badge-bandeja', dictaminado: 'acd-badge-dictaminado' };

    let _acdCargada = { bandeja: false, dictaminado: false };
    let _acdDatos = { bandeja: [], dictaminado: [] };

    let _acdCierreIdOp = 0;
    let _acdCierreBuckets = { atencion: [], validacion: [], recuperacion: [] };
    let _acdEvidenciaUrl = '';
    let _acdDictaminadoEvidenciaUrl = '';
    let _acdDocViewerState = { scale: 1, rotate: 0 };
    /** Si true: el modal principal se ocultó solo para ver bitácora; al cerrarla se vuelve a mostrar (no limpiar formulario). */
    let _acdCierreDebeReaparecerTrasBitacora = false;

    const ACD_CIERRE_ETAPA_META = [
        { key: 'atencion', tituloModal: 'Atención a clientes — bitácora', textoFila: 'ATENCION A CLIENTES' },
        { key: 'validacion', tituloModal: 'Validaciones — bitácora', textoFila: 'VALIDACIONES' },
        { key: 'recuperacion', tituloModal: 'Recuperación — bitácora', textoFila: 'RECUPERACION' },
    ];

    function acdPartirBitacoraPorEtapa(bitacora) {
        const buckets = { atencion: [], validacion: [], recuperacion: [] };
        (bitacora || []).forEach(function (b) {
            const a = String(b.accion || '');
            if (/RECUPER|TRÁNSITO|TRANSITO|RECOLECCI|REVISIÓN\s*RECUPER|REVISIÓN\s*RECUP|REVISION\s*RECUPER/i.test(a)) {
                buckets.recuperacion.push(b);
            } else if (/PROCESANDO\s*IA|VALID|CORRECCI|FACTURA|\bIA\b/i.test(a)) {
                buckets.validacion.push(b);
            } else {
                buckets.atencion.push(b);
            }
        });
        return buckets;
    }

    function acdRenderFilasEtapasCierre(buckets) {
        return ACD_CIERRE_ETAPA_META.map(function (meta) {
            const lines = buckets[meta.key] || [];
            const tiene = lines.length > 0;
            const badge = '<span class="badge bg-success">OK</span>';
            const btnDis = tiene ? '' : ' disabled';
            const btnClass = tiene ? 'btn-outline-success' : 'btn-outline-secondary';
            return `
            <div class="acd-cierre-etapa-fila">
                <span style="flex:1;min-width:0;">${meta.textoFila} ${badge}</span>
                <button type="button" class="btn ${btnClass} btn-sm acd-cierre-btn-coment"${btnDis}
                    data-acd-etapa="${meta.key}">
                    <i class="fa-regular fa-comments me-1"></i>Ver comentarios
                </button>
            </div>`;
        }).join('');
    }

    function acdRenderBitacoraCompleta(bitacora) {
        const rows = Array.isArray(bitacora) ? bitacora : [];
        if (!rows.length) {
            return '<div class="acd-bitacora-full">' +
                '<div class="acd-bitacora-full-title"><i class="fa-solid fa-clock-rotate-left"></i>Bitacora completa</div>' +
                '<div class="acd-bitacora-full-sub">Movimientos completos de la operacion.</div>' +
                '<p class="acd-bitacora-empty">Sin movimientos registrados.</p>' +
            '</div>';
        }
        const nDots = 4;
        return '<div class="acd-bitacora-full">' +
            '<div class="acd-bitacora-full-title"><i class="fa-solid fa-clock-rotate-left"></i>Bitacora completa</div>' +
            '<div class="acd-bitacora-full-sub">' + rows.length + ' movimientos registrados.</div>' +
            '<ul class="acd-bit-tl" role="list">' + rows.map(function (b, idx) {
                const dotCls = 'acd-bit-dot--' + (idx % nDots);
                return '<li class="acd-bit-tl-item ' + dotCls + '">' +
                    '<span class="acd-bit-tl-dot" aria-hidden="true"></span>' +
                    '<div class="acd-bit-tl-content">' +
                        '<div class="acd-bit-tl-headrow">' +
                            '<span class="acd-bit-tl-title">' + acdEsc(b.accion || 'Movimiento') + '</span>' +
                            '<span class="acd-bit-tl-time">' + acdEsc(b.fecha_alta || '') + '</span>' +
                        '</div>' +
                        '<div class="acd-bit-tl-user">' + acdEsc(b.nombre_usuario || 'Sistema') + '</div>' +
                    '</div>' +
                '</li>';
            }).join('') + '</ul>' +
        '</div>';
    }

    function acdModalBitacoraEtapaMostrar(key) {
        const meta = ACD_CIERRE_ETAPA_META.find(function (m) { return m.key === key; });
        const lines = _acdCierreBuckets[key] || [];
        const tituloEl = document.getElementById('acdCierreBitModalTitulo');
        const body = document.getElementById('acdCierreBitModalBody');
        if (tituloEl) tituloEl.textContent = meta ? meta.tituloModal : 'Bitácora';
        if (!body) return;
        if (!lines.length) {
            body.innerHTML = '<p class="text-muted small mb-0">No hay movimientos clasificados para esta etapa.</p>';
        } else {
            const nDots = 4;
            body.innerHTML = '<ul class="acd-bit-tl" role="list">' + lines.map(function (b, idx) {
                const dotCls = 'acd-bit-dot--' + (idx % nDots);
                return `<li class="acd-bit-tl-item ${dotCls}">
                    <span class="acd-bit-tl-dot" aria-hidden="true"></span>
                    <div class="acd-bit-tl-content">
                        <div class="acd-bit-tl-headrow">
                            <span class="acd-bit-tl-title">${acdEsc(b.accion || '—')}</span>
                            <span class="acd-bit-tl-time">${acdEsc(b.fecha_alta || '')}</span>
                        </div>
                        <div class="acd-bit-tl-user">${acdEsc(b.nombre_usuario || '')}</div>
                    </div>
                </li>`;
            }).join('') + '</ul>';
        }
        const modalBit = document.getElementById('modalAcdCierreVerBitacoraEtapa');
        const modalCierre = document.getElementById('modalAcdCierreDocumentacion');
        if (!modalBit || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

        function abrirModalBitacora() {
            bootstrap.Modal.getOrCreateInstance(modalBit).show();
        }

        if (modalCierre && modalCierre.classList.contains('show')) {
            _acdCierreDebeReaparecerTrasBitacora = true;
            const instCierre = bootstrap.Modal.getInstance(modalCierre) || bootstrap.Modal.getOrCreateInstance(modalCierre);
            const onCierreHidden = function () {
                modalCierre.removeEventListener('hidden.bs.modal', onCierreHidden);
                abrirModalBitacora();
            };
            modalCierre.addEventListener('hidden.bs.modal', onCierreHidden, { once: true });
            instCierre.hide();
        } else {
            abrirModalBitacora();
        }
    }

    function acdModalCierreDocLimpiarFormulario() {
        _acdCierreBuckets = { atencion: [], validacion: [], recuperacion: [] };
        _acdEvidenciaUrl = '';
        const chk = document.getElementById('acdChkConfirmoS2');
        const btn = document.getElementById('acdBtnRegistrarConfirmacionS2');
        const filas = document.getElementById('acdCierreEtapasFilas');
        const sub = document.getElementById('acdCierreSubtitulo');
        const title = document.getElementById('modalAcdCierreDocumentacionLabel');
        const titEv = document.getElementById('acdEvidenciaTitulo');
        const formEv = document.getElementById('acdEvidenciaFormulario');
        const okEv = document.getElementById('acdEvidenciaExito');
        const formCap = document.getElementById('acdCierreFormularioCapturado');
        const bitFull = document.getElementById('acdCierreBitacoraCompleta');
        const inpF = document.getElementById('acdEvidenciaArchivo');
        const txC = document.getElementById('acdEvidenciaComentarios');
        if (chk) {
            chk.checked = false;
            chk.disabled = true;
        }
        if (btn) btn.disabled = true;
        if (filas) filas.innerHTML = '';
        if (sub) sub.textContent = '';
        if (title) {
            title.innerHTML = '<i class="fa-solid fa-file-circle-check me-2 text-warning"></i>CIERRE DOCUMENTADO';
        }
        if (titEv) {
            titEv.textContent = 'Documento de cierre en S2';
            titEv.classList.remove('acd-ev-ok');
        }
        if (formEv) formEv.style.display = '';
        if (okEv) okEv.style.display = 'none';
        if (formCap) formCap.innerHTML = '';
        if (bitFull) bitFull.innerHTML = '';
        if (inpF) inpF.value = '';
        if (txC) txC.value = '';
    }

    window.acdModalCierreDocAbrir = function (idOperacion, idCredito, nombreCliente) {
        idOperacion = parseInt(idOperacion, 10) || 0;
        if (idOperacion <= 0) return;
        _acdCierreDebeReaparecerTrasBitacora = false;
        acdModalCierreDocLimpiarFormulario();
        _acdCierreIdOp = idOperacion;
        const sub = document.getElementById('acdCierreSubtitulo');
        if (sub) {
            sub.textContent = 'Crédito ' + String(idCredito || '') + (nombreCliente ? ' / ' + String(nombreCliente) : '');
        }
        const title = document.getElementById('modalAcdCierreDocumentacionLabel');
        if (title) {
            title.innerHTML = acdTituloContextualModal(nombreCliente, idCredito);
        }
        if (sub) {
            sub.innerHTML = 'Bandeja de entrada: pendiente de cerrar el cr&eacute;dito en S2.';
        }
        const loader = document.getElementById('acdCierreLoader');
        const contenido = document.getElementById('acdCierreContenido');
        if (loader) loader.style.display = 'block';
        if (contenido) contenido.style.opacity = '0.45';

        const modalEl = document.getElementById('modalAcdCierreDocumentacion');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        fetch('/MotosAdjudicadas/obtenerDetalle/' + idOperacion + '?incluir_todas=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.detalle) {
                    throw new Error(data.message || 'No se pudo cargar el detalle.');
                }
                const bit = data.detalle.bitacora || [];
                _acdCierreBuckets = acdPartirBitacoraPorEtapa(bit);
                const filas = document.getElementById('acdCierreEtapasFilas');
                const formCap = document.getElementById('acdCierreFormularioCapturado');
                const bitFull = document.getElementById('acdCierreBitacoraCompleta');
                if (filas) filas.innerHTML = acdRenderFilasEtapasCierre(_acdCierreBuckets);
                if (formCap) formCap.innerHTML = acdRenderFormularioOperacion(data.detalle);
                if (bitFull) bitFull.innerHTML = acdRenderBitacoraCompleta(bit);
            })
            .catch(function (err) {
                const filas = document.getElementById('acdCierreEtapasFilas');
                if (filas) {
                    filas.innerHTML = '<div class="alert alert-warning mb-0 small">' + acdEsc(err.message || 'Error') + '</div>';
                }
            })
            .finally(function () {
                if (loader) loader.style.display = 'none';
                if (contenido) contenido.style.opacity = '1';
            });
    };

    function acdEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function acdDatoVisible(v) {
        if (v === null || v === undefined) return '';
        const s = String(v).trim();
        return s === '' || s === 'null' || s === 'undefined' ? '' : s;
    }

    function acdFormatoTelefono(valor) {
        const limpio = String(valor || '').replace(/\D/g, '');
        if (limpio.length === 10) {
            return limpio.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        }
        if (limpio.length === 12 && limpio.indexOf('52') === 0) {
            return '+52 ' + limpio.slice(2).replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        }
        return acdDatoVisible(valor);
    }

    function acdFormatoSiNo(valor) {
        const raw = acdDatoVisible(valor);
        const s = raw.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        if (s === 'si' || s === '1' || s === 'true') return 'Si';
        if (s === 'no' || s === '0' || s === 'false') return 'No';
        return raw;
    }

    function acdResguardoTexto(src) {
        const base = acdDatoVisible(src && src.log_lugar_resguardo);
        const otro = acdDatoVisible(src && src.log_lugar_otro);
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

    function acdIconoFormulario(label) {
        const k = String(label || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        if (k.indexOf('marca') !== -1) return 'fa-tag';
        if (k.indexOf('serie') !== -1 || k.indexOf('vin') !== -1) return 'fa-barcode';
        if (k.indexOf('modelo') !== -1) return 'fa-motorcycle';
        if (k.indexOf('ano') !== -1) return 'fa-calendar-days';
        if (k.indexOf('color') !== -1) return 'fa-palette';
        if (k.indexOf('motor') !== -1) return 'fa-gears';
        if (k.indexOf('placa') !== -1) return 'fa-id-card';
        if (k.indexOf('kilometraje') !== -1) return 'fa-gauge-high';
        if (k.indexOf('llave') !== -1) return 'fa-key';
        if (k.indexOf('tarjeta') !== -1) return 'fa-address-card';
        if (k.indexOf('resguardo') !== -1 || k.indexOf('lugar') !== -1) return 'fa-warehouse';
        if (k.indexOf('latitud') !== -1 || k.indexOf('longitud') !== -1) return 'fa-location-dot';
        if (k.indexOf('responsable') !== -1) return 'fa-user-check';
        if (k.indexOf('telefono') !== -1) return 'fa-phone';
        if (k.indexOf('direccion') !== -1 || k.indexOf('task') !== -1) return 'fa-route';
        return 'fa-circle-info';
    }

    function acdColorCss(valor) {
        const s = String(valor || '').toLowerCase()
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

    function acdRenderFormularioOperacion(det) {
        const src = Object.assign({}, det && det.datos_moto && typeof det.datos_moto === 'object' ? det.datos_moto : {}, det || {});
        const pick = function () {
            for (let i = 0; i < arguments.length; i++) {
                if (acdDatoVisible(arguments[i])) return arguments[i];
            }
            return '';
        };
        const ubicacion = [
            acdResguardoTexto(src),
            acdDatoVisible(src.log_ciudad),
            acdDatoVisible(src.log_estado)
        ].filter(Boolean).join(' / ');
        const camposMoto = [
            ['Marca', src.moto_marca || src.marca],
            ['Serie', src.moto_no_serie || src.serie],
            ['Modelo', src.moto_modelo || src.modelo],
            ['A\u00f1o', src.moto_anio || src.anio || src.ano],
            ['Color', src.moto_color || src.color],
            ['No. motor', src.moto_no_motor || src.num_motor || src.no_motor],
            ['Placas', src.moto_placas || src.placas],
            ['Kilometraje', src.kilometraje],
            ['Llave fisica', acdFormatoSiNo(pick(src.llave_fisica, src.tiene_llave_fisica))],
            ['Placa fisica', acdFormatoSiNo(pick(src.placa_fisica, src.la_moto_tiene_placa_fisica))],
            ['Tarjeta circulacion', acdFormatoSiNo(pick(src.tarjeta_circulacion, src.tiene_tarjeta_de_circulacion_en_fisico)), true]
        ];
        const camposResguardo = [
            ['Lugar de resguardo', ubicacion, true],
            ['Responsable', src.responsable_entrega || src.log_responsable || src.nombre_responsable],
            ['Telefono', acdFormatoTelefono(src.log_telefono || src.telefono_contacto || src.telefono)],
            ['Direccion resguardo', src.log_direccion || src.direccion, true]
        ];
        const fecha = acdDatoVisible(src.datos_moto_fecha);

        function renderCampo(row) {
            const label = row[0];
            const rawValue = acdDatoVisible(row[1]);
            const value = rawValue || 'No capturado';
            const lower = String(label || '').toLowerCase();
            const cls = [
                'acd-form-field',
                row[2] ? 'acd-form-field-wide' : '',
                lower === 'serie' ? 'acd-form-field-series' : ''
            ].filter(Boolean).join(' ');
            const dot = lower === 'color' && rawValue
                ? '<span class="acd-form-color-dot" style="background-color:' + acdEsc(acdColorCss(value)) + ';"></span>'
                : '';
            return '<div class="' + cls + '" title="' + acdEsc(value) + '">'
                + '<span class="acd-form-field-head"><i class="fa-solid ' + acdEsc(acdIconoFormulario(label)) + '"></i><span>' + acdEsc(label) + '</span></span>'
                + '<span class="acd-form-value">' + dot + '<span>' + acdEsc(value) + '</span></span>'
                + '</div>';
        }

        return '<div class="acd-form-wrap">'
            + '<div class="acd-form-head">'
            + '<span class="acd-form-title"><i class="fa-solid fa-list-check me-1"></i>Formulario capturado</span>'
            + '<span class="acd-form-date"><i class="fa-solid fa-calendar-check"></i>Capturado ' + acdEsc(fecha || 'No capturado') + '</span>'
            + '</div>'
            + '<div class="acd-form-cols">'
            + '<div class="acd-form-grid">' + camposMoto.map(renderCampo).join('') + '</div>'
            + '<div class="acd-form-list">' + camposResguardo.map(renderCampo).join('') + '</div>'
            + '</div>'
            + '</div>';
    }

    function acdEsPdfUrl(url) {
        const clean = String(url || '').split('?')[0].split('#')[0].toLowerCase();
        return clean.endsWith('.pdf');
    }

    function acdCerrarVisorDocumento() {
        const overlay = document.getElementById('acdDocViewerOverlay');
        const box = document.getElementById('acdDocViewerBox');
        if (box) {
            box.classList.remove('acd-doc-viewer-box--image');
            box.innerHTML = '';
        }
        if (overlay) overlay.classList.add('d-none');
        _acdDocViewerState = { scale: 1, rotate: 0 };
    }

    function acdCommitVisorImagen() {
        const box = document.getElementById('acdDocViewerBox');
        const img = box ? box.querySelector('.acd-doc-viewer-img') : null;
        const pct = box ? box.querySelector('.acd-doc-viewer-pct') : null;
        const reset = box ? box.querySelector('.acd-doc-viewer-reset') : null;
        if (!img) return;
        img.style.transform = 'rotate(' + _acdDocViewerState.rotate + 'deg) scale(' + _acdDocViewerState.scale + ')';
        if (pct) pct.textContent = Math.round(_acdDocViewerState.scale * 100) + '%';
        if (reset) {
            const changed = _acdDocViewerState.scale !== 1 || _acdDocViewerState.rotate !== 0;
            reset.classList.toggle('d-none', !changed);
        }
    }

    function acdAbrirVisorDocumento(url, titulo) {
        const rawUrl = String(url || '').trim();
        if (!rawUrl) return;
        const overlay = document.getElementById('acdDocViewerOverlay');
        const box = document.getElementById('acdDocViewerBox');
        const title = document.getElementById('acdDocViewerTitle');
        if (!overlay || !box) return;
        if (title) title.textContent = titulo || 'Evidencia de cierre documentado';
        _acdDocViewerState = { scale: 1, rotate: 0 };
        box.classList.remove('acd-doc-viewer-box--image');
        const urlSafe = acdEsc(rawUrl);
        if (acdEsPdfUrl(rawUrl)) {
            box.innerHTML = '<iframe src="' + urlSafe + '" title="Evidencia de cierre documentado"></iframe>';
        } else {
            box.classList.add('acd-doc-viewer-box--image');
            box.innerHTML = ''
                + '<div class="acd-doc-viewer-image-wrap">'
                + '<img class="acd-doc-viewer-img" src="' + urlSafe + '" alt="Evidencia de cierre documentado" draggable="false">'
                + '</div>'
                + '<div class="acd-doc-viewer-toolbar d-flex align-items-center justify-content-center gap-2 flex-wrap py-1 px-2">'
                + '<button type="button" class="btn btn-sm btn-outline-light acd-doc-viewer-minus" title="Alejar" aria-label="Alejar"><i class="fa-solid fa-magnifying-glass-minus"></i></button>'
                + '<span class="small text-white acd-doc-viewer-pct fw-semibold" style="min-width:3.25rem;text-align:center;">100%</span>'
                + '<button type="button" class="btn btn-sm btn-outline-light acd-doc-viewer-plus" title="Acercar" aria-label="Acercar"><i class="fa-solid fa-magnifying-glass-plus"></i></button>'
                + '<button type="button" class="btn btn-sm btn-outline-light acd-doc-viewer-rotate-left" title="Rotar izquierda" aria-label="Rotar izquierda"><i class="fa-solid fa-rotate-left"></i></button>'
                + '<button type="button" class="btn btn-sm btn-outline-light acd-doc-viewer-rotate-right" title="Rotar derecha" aria-label="Rotar derecha"><i class="fa-solid fa-rotate-right"></i></button>'
                + '<button type="button" class="btn btn-sm btn-outline-secondary acd-doc-viewer-reset d-none">Restablecer</button>'
                + '</div>';
            const minus = box.querySelector('.acd-doc-viewer-minus');
            const plus = box.querySelector('.acd-doc-viewer-plus');
            const left = box.querySelector('.acd-doc-viewer-rotate-left');
            const right = box.querySelector('.acd-doc-viewer-rotate-right');
            const reset = box.querySelector('.acd-doc-viewer-reset');
            if (minus) minus.addEventListener('click', function () {
                _acdDocViewerState.scale = Math.max(1, _acdDocViewerState.scale - 0.22);
                acdCommitVisorImagen();
            });
            if (plus) plus.addEventListener('click', function () {
                _acdDocViewerState.scale = Math.min(4, _acdDocViewerState.scale + 0.22);
                acdCommitVisorImagen();
            });
            if (left) left.addEventListener('click', function () {
                _acdDocViewerState.rotate = ((_acdDocViewerState.rotate - 90) % 360 + 360) % 360;
                acdCommitVisorImagen();
            });
            if (right) right.addEventListener('click', function () {
                _acdDocViewerState.rotate = ((_acdDocViewerState.rotate + 90) % 360 + 360) % 360;
                acdCommitVisorImagen();
            });
            if (reset) reset.addEventListener('click', function () {
                _acdDocViewerState = { scale: 1, rotate: 0 };
                acdCommitVisorImagen();
            });
            acdCommitVisorImagen();
        }
        overlay.classList.remove('d-none');
    }

    function acdSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${acdEsc(msg)}</span>
        </div>`;
    }

    function acdRenderCardBandeja(item) {
        const g  = item.gestor_nombre
            ? acdEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">—</span>';
        const fa = item.fecha_asignacion
            ? acdEsc(item.fecha_asignacion)
            : '<span class="ae-list-muted">—</span>';
        const est = item.estatus ? acdEsc(item.estatus) : '<span class="ae-list-muted">—</span>';
        const dias = item.dias_en_pipeline != null && item.dias_en_pipeline !== ''
            ? acdEsc(String(item.dias_en_pipeline))
            : '<span class="ae-list-muted">—</span>';
        const nombreCliente = item.nombre_cliente
            ? acdEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? acdEsc(item.folio) : '—';

        return `
        <div class="ac-card">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${acdEsc(String(item.id_credito))}</span>
                    </div>
                    <div class="ae-list-cell ae-list-gestor">
                        <span class="ac-lbl">Gestor a cargo</span>
                        <span class="ac-val">${g}</span>
                    </div>
                    <div class="ae-list-cell ae-list-asig">
                        <span class="ac-lbl">Asignación realizada</span>
                        <span class="ac-val">${fa}</span>
                    </div>
                    <div class="ae-list-cell ae-list-nombre">
                        <span class="ac-lbl">Nombre</span>
                        <span class="ac-val">${nombreCliente}</span>
                    </div>
                    <div class="ae-list-cell">
                        <span class="ac-lbl">Estatus flujo</span>
                        <span class="ac-val">${est}</span>
                    </div>
                    <div class="ae-list-cell">
                        <span class="ac-lbl">Días en flujo</span>
                        <span class="ac-val">${dias}</span>
                    </div>
                </div>
                <div class="ae-list-action">
                    <button type="button" class="acd-btn-pipeline acd-abrir-modal-cierre-doc" title="Cierre documentado en S2"
                        data-acd-id-operacion="${Number(item.id)}"
                        data-acd-id-credito="${Number(item.id_credito)}"
                        data-acd-nombre="${encodeURIComponent(String(item.nombre_cliente || ''))}">
                        <i class="fa-solid fa-file-circle-check me-1"></i>Cierre documentado
                    </button>
                </div>
            </div>
        </div>`;
    }

    function acdRenderCardDictaminado(item) {
        const estPipeline = item.estatus ? acdEsc(item.estatus) : '<span class="ae-list-muted">—</span>';
        const dictTxt = item.dictamen
            ? acdEsc(item.dictamen)
            : '<span class="ae-list-muted">—</span>';
        const fechaD = item.fecha_dictamen
            ? acdEsc(item.fecha_dictamen)
            : '<span class="ae-list-muted">—</span>';
        const g = item.gestor_nombre
            ? acdEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">—</span>';
        const nombreCliente = item.nombre_cliente
            ? acdEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? acdEsc(item.folio) : '—';

        return `
        <div class="ac-card acd-card-dict">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${acdEsc(String(item.id_credito))}</span>
                    </div>
                    <div class="ae-list-cell ae-list-gestor">
                        <span class="ac-lbl">Gestor a cargo</span>
                        <span class="ac-val">${g}</span>
                    </div>
                    <div class="ae-list-cell ae-list-asig">
                        <span class="ac-lbl">Fecha dictamen</span>
                        <span class="ac-val">${fechaD}</span>
                    </div>
                    <div class="ae-list-cell ae-list-nombre">
                        <span class="ac-lbl">Nombre</span>
                        <span class="ac-val">${nombreCliente}</span>
                    </div>
                    <div class="ae-list-cell ae-list-status">
                        <span class="ac-lbl">Estatus flujo</span>
                        <span class="ac-val">${estPipeline}</span>
                    </div>
                    <div class="ae-list-cell">
                        <span class="ac-lbl">Dictamen</span>
                        <span class="ac-val">${dictTxt}</span>
                    </div>
                    ${item.comentarios ? `
                    <div class="ae-list-cell" style="grid-column: 1 / -1;">
                        <span class="ac-lbl">Comentarios</span>
                        <span class="ac-val" style="white-space:pre-line;">${acdEsc(item.comentarios)}</span>
                    </div>` : ''}
                </div>
            </div>
        </div>`;
    }

    function acdGestorTablaHtml(item) {
        const nombre = item && item.gestor_nombre ? String(item.gestor_nombre).trim() : '';
        return nombre ? acdEsc(nombre) : '<span class="acd-table-muted">—</span>';
    }

    function acdMinutosTexto(minutos) {
        const n = parseInt(minutos, 10);
        if (!Number.isFinite(n) || n < 0) return '';
        const dias = Math.floor(n / 1440);
        if (dias > 0) return dias + ' día' + (dias === 1 ? '' : 's');
        const horas = Math.floor((n % 1440) / 60);
        const mins = n % 60;
        if (horas > 0) return horas + ' hora' + (horas === 1 ? '' : 's');
        if (mins > 0) return mins + ' min';
        return '0 min';
    }

    function acdParseFechaEtapa(fecha) {
        const txt = String(fecha || '').trim();
        if (!txt || txt === '-') return null;
        const m = txt.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?$/);
        if (m) {
            return new Date(
                parseInt(m[3], 10),
                parseInt(m[2], 10) - 1,
                parseInt(m[1], 10),
                parseInt(m[4] || '0', 10),
                parseInt(m[5] || '0', 10),
                0
            );
        }
        const iso = Date.parse(txt.replace(' ', 'T'));
        return Number.isFinite(iso) ? new Date(iso) : null;
    }

    function acdMinutosEntreFechas(desde, hasta) {
        const ini = acdParseFechaEtapa(desde);
        const fin = hasta ? acdParseFechaEtapa(hasta) : new Date();
        if (!ini || !fin) return '';
        return Math.max(0, Math.floor((fin.getTime() - ini.getTime()) / 60000));
    }

    function acdRenderTiempoEtapa(minutos, desde, lista) {
        const desdeTxt = desde ? String(desde) : '';
        const listaTxt = lista ? String(lista) : '';
        const minutosNum = parseInt(minutos, 10);
        const minutosCalculados = acdMinutosEntreFechas(desdeTxt, listaTxt);
        const minutosFinal = (!Number.isFinite(minutosNum) || minutosNum < 0 || (listaTxt && minutosNum === 0 && minutosCalculados > 0))
            ? minutosCalculados
            : minutosNum;
        const tiempo = acdMinutosTexto(minutosFinal);
        if (!tiempo && !desdeTxt && !listaTxt) {
            return '<span class="acd-table-muted">-</span>';
        }
        return '<span class="acd-op-time">'
            + '<span class="acd-op-time-label"><i class="fa-solid fa-stopwatch"></i>Tiempo en esta etapa</span>'
            + (tiempo ? '<strong>' + acdEsc(tiempo) + '</strong>' : '')
            + (desdeTxt || listaTxt
                ? '<small>' + (desdeTxt ? 'Desde ' + acdEsc(desdeTxt) : '') + (desdeTxt && listaTxt ? '<br>' : '') + (listaTxt ? 'Lista ' + acdEsc(listaTxt) : '') + '</small>'
                : '')
            + '</span>';
    }

    function acdRenderFilaTablaBandeja(item) {
        const gestor = acdGestorTablaHtml(item);
        const cliente = item.nombre_cliente ? acdEsc(item.nombre_cliente) : '<span class="acd-table-muted">Sin nombre</span>';
        const folio = item.folio ? acdEsc(item.folio) : '-';
        const fechaLegacy = item.fecha_gestion_legacy ? acdEsc(item.fecha_gestion_legacy) : '<span class="acd-table-muted">-</span>';
        const fechaValidadoRecuperacion = item.fecha_entrada_cierre_documentacion
            ? acdEsc(item.fecha_entrada_cierre_documentacion)
            : '<span class="acd-table-muted">-</span>';
        const tiempoEtapa = acdRenderTiempoEtapa(
            item.minutos_en_cierre_documentacion,
            item.fecha_entrada_cierre_documentacion || item.fecha_asignacion || '',
            ''
        );

        return `
        <tr>
            <td class="acd-table-main">
                <span class="acd-table-folio">${folio}</span>
                <span class="acd-table-credit"># ${acdEsc(String(item.id_credito || ''))}</span>
                <span class="acd-table-main-client"><i class="fa-solid fa-user"></i>${cliente}</span>
            </td>
            <td class="acd-table-gestor">
                <span class="acd-table-gestor-name"><i class="fa-solid fa-user-tie"></i>${gestor}</span>
                <span class="acd-table-legacy-label"><i class="fa-solid fa-calendar-days"></i>FECHA DE ADJUDICACION</span>
                <span class="acd-table-legacy-date">${fechaLegacy}</span>
                <span class="acd-table-legacy-label"><i class="fa-solid fa-file-circle-check"></i>VALIDADO EN RECUPERACION</span>
                <span class="acd-table-legacy-date">${fechaValidadoRecuperacion}</span>
            </td>
            <td class="acd-table-operacion">
                ${tiempoEtapa}
            </td>
            <td class="acd-table-action">
                <div class="acd-action-buttons">
                    <button type="button" class="btn btn-sm btn-warning acd-abrir-modal-cierre-doc"
                            data-acd-id-operacion="${Number(item.id)}"
                            data-acd-id-credito="${Number(item.id_credito)}"
                            data-acd-nombre="${encodeURIComponent(String(item.nombre_cliente || ''))}"
                            title="Cierre documentado" aria-label="Cierre documentado">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }

    function acdRenderFilaTablaDictaminado(item) {
        const gestor = acdGestorTablaHtml(item);
        const cliente = item.nombre_cliente ? acdEsc(item.nombre_cliente) : '<span class="acd-table-muted">Sin nombre</span>';
        const folio = item.folio ? acdEsc(item.folio) : '-';
        const fechaLegacy = item.fecha_gestion_legacy ? acdEsc(item.fecha_gestion_legacy) : '<span class="acd-table-muted">-</span>';
        const fechaValidadoRecuperacion = item.fecha_inicio_cierre_documentacion
            ? acdEsc(item.fecha_inicio_cierre_documentacion)
            : '<span class="acd-table-muted">-</span>';
        const tiempoEtapa = acdRenderTiempoEtapa(
            item.minutos_total_cierre_documentacion,
            item.fecha_inicio_cierre_documentacion || '',
            item.fecha_fin_cierre_documentacion || item.fecha_dictamen || ''
        );

        return `
        <tr>
            <td class="acd-table-main">
                <span class="acd-table-folio">${folio}</span>
                <span class="acd-table-credit"># ${acdEsc(String(item.id_credito || ''))}</span>
                <span class="acd-table-main-client"><i class="fa-solid fa-user"></i>${cliente}</span>
            </td>
            <td class="acd-table-gestor">
                <span class="acd-table-gestor-name"><i class="fa-solid fa-user-tie"></i>${gestor}</span>
                <span class="acd-table-legacy-label"><i class="fa-solid fa-calendar-days"></i>FECHA DE ADJUDICACION</span>
                <span class="acd-table-legacy-date">${fechaLegacy}</span>
                <span class="acd-table-legacy-label"><i class="fa-solid fa-file-circle-check"></i>VALIDADO EN RECUPERACION</span>
                <span class="acd-table-legacy-date">${fechaValidadoRecuperacion}</span>
            </td>
            <td class="acd-table-operacion">
                ${tiempoEtapa}
            </td>
            <td class="acd-table-action">
                <div class="acd-action-buttons">
                    <button type="button" class="btn btn-sm btn-outline-secondary acd-ver-dictaminado"
                            data-acd-id-operacion="${Number(item.id)}"
                            data-acd-id-credito="${Number(item.id_credito)}"
                            title="Ver expediente" aria-label="Ver expediente">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }

    function acdRenderTabla(key, datos) {
        const esBandeja = key === 'bandeja';
        const tableId = 'acd-tabla-' + key;
        const filas = (datos || []).map(function (item) {
            return esBandeja ? acdRenderFilaTablaBandeja(item) : acdRenderFilaTablaDictaminado(item);
        }).join('');
        return `
        <div class="card-datatable acd-table-wrap">
            <table id="${acdEsc(tableId)}" class="dt-responsive table border-top acd-table">
                <thead>
                    <tr>
                        <th>Operacion</th>
                        <th>Gestor</th>
                        <th>Tiempo</th>
                        <th class="acd-table-action">Acciones</th>
                    </tr>
                </thead>
                <tbody>${filas}</tbody>
            </table>
        </div>`;
    }

    function acdInicializarTabla(key) {
        const tableId = '#acd-tabla-' + key;
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
                 '<"row align-items-center mt-3 acd-dt-footer"<"col-sm-12 col-md-5 acd-dt-info"i><"col-sm-12 col-md-7 acd-dt-pages"p>>',
            drawCallback: function () {
                jQuery(tableId + '_paginate > .pagination').addClass('pagination-sm justify-content-end');
                jQuery(tableId).closest('.dt-container').find('.dt-paging .pagination').addClass('pagination-sm justify-content-end');
                jQuery(tableId + '_length select').addClass('form-select form-select-sm');
                jQuery(tableId + '_filter input').addClass('form-control form-control-sm');
            }
        });
    }

    function acdBuscarDictaminadoPorOperacion(idOperacion) {
        const id = parseInt(idOperacion, 10) || 0;
        return (_acdDatos.dictaminado || []).find(function (row) {
            return parseInt(row.id, 10) === id;
        }) || null;
    }

    function acdRenderDetalleDictaminado(det, row) {
        const base = Object.assign({}, row || {}, det || {});
        const comentarios = base.comentarios || '';
        const dictamen = base.dictamen || '';
        const evidencias = Array.isArray(base.evidencias) ? base.evidencias : [];
        const evidenciaCierre = evidencias.find(function (ev) {
            return ev && ev.slot === 'doc_cierre_s2' && ev.url;
        }) || null;
        _acdDictaminadoEvidenciaUrl = evidenciaCierre && evidenciaCierre.url ? String(evidenciaCierre.url) : '';
        const fechaFinEtapa = base.fecha_fin_cierre_documentacion || base.fecha_dictamen || base.fecha_actualizacion_fmt || '';
        const fechaInicioEtapa = base.fecha_inicio_cierre_documentacion
            || base.fecha_entrada_cierre_documentacion
            || base.fecha_gestion_legacy
            || base.fecha_alta_fmt
            || '';
        const minutosRaw = base.minutos_total_cierre_documentacion != null
            ? base.minutos_total_cierre_documentacion
            : base.minutos_en_cierre_documentacion;
        const minutosNum = parseInt(minutosRaw, 10);
        const minutosCalc = acdMinutosEntreFechas(fechaInicioEtapa, fechaFinEtapa);
        const tiempoEtapa = acdMinutosTexto(Number.isFinite(minutosNum) && minutosNum >= 0 ? minutosNum : minutosCalc);
        const tiempoEtapaDetalle = tiempoEtapa
            ? tiempoEtapa + (fechaFinEtapa ? ' / Lista ' + fechaFinEtapa : '')
            : (fechaFinEtapa || '-');
        const chips = [
            ['Gestor', base.gestor_nombre || '-'],
            ['FECHA DE ADJUDICACION', base.fecha_gestion_legacy || base.fecha_alta_fmt || '-'],
            ['TIEMPO EN ESTA ETAPA', tiempoEtapaDetalle]
        ];

        return `
        <div class="acd-modal-grid">
            <div class="acd-modal-main d-flex flex-column gap-3">
                ${acdRenderFormularioOperacion(base)}
                <div class="acd-dict-detail-grid">
                    ${chips.map(function (chip) {
                        return `<div class="acd-dict-chip"><span>${acdEsc(chip[0])}</span><strong>${acdEsc(String(chip[1] || '-'))}</strong></div>`;
                    }).join('')}
                </div>
                <div class="acd-dict-evidencia-card">
                    <div class="acd-dict-evidencia-main">
                        <i class="fa-solid fa-file-circle-check"></i>
                        <div>
                            <strong>Evidencia de cierre documentado</strong>
                            <small>${_acdDictaminadoEvidenciaUrl ? 'Documento cargado en expediente.' : 'Sin documento cargado en expediente.'}</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill fw-bold" data-acd-ver-evidencia-dict="1"${_acdDictaminadoEvidenciaUrl ? '' : ' disabled'}>
                        <i class="fa-regular fa-eye me-1"></i>Ver evidencia
                    </button>
                </div>
                <div>
                    <h6 class="fw-bold mb-2">Dictamen</h6>
                    <div class="acd-dict-observacion">${acdEsc(dictamen || 'Sin dictamen registrado.')}</div>
                </div>
                ${comentarios ? `<div>
                    <h6 class="fw-bold mb-2">Comentarios</h6>
                    <div class="acd-dict-observacion">${acdEsc(comentarios)}</div>
                </div>` : ''}
            </div>
            ${acdRenderBitacoraCompleta(base.bitacora || [])}
        </div>`;
    }

    function acdAbrirVistaDictaminado(idOperacion) {
        const id = parseInt(idOperacion, 10) || 0;
        if (id <= 0) return;
        const row = acdBuscarDictaminadoPorOperacion(id);
        const body = document.getElementById('acdDictaminadoDetalleBody');
        const title = document.getElementById('modalAcdDictaminadoDetalleLabel');
        const subtitle = document.getElementById('acdDictaminadoDetalleSubtitulo');
        if (title) {
            title.innerHTML = acdTituloContextualModal(
                row && row.nombre_cliente ? row.nombre_cliente : 'Cliente',
                row && row.id_credito ? row.id_credito : ''
            );
        }
        if (subtitle) {
            subtitle.innerHTML = 'Dictaminado: el cr&eacute;dito se cerr&oacute; en S2 y se document&oacute; correctamente.';
        }
        if (body) {
            body.innerHTML = '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Cargando expediente...</div>';
        }
        const modal = document.getElementById('modalAcdDictaminadoDetalle');
        if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        fetch('/MotosAdjudicadas/obtenerDetalle/' + id + '?incluir_todas=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.detalle) {
                    throw new Error(data.message || 'No se pudo cargar el expediente.');
                }
                if (body) body.innerHTML = acdRenderDetalleDictaminado(data.detalle, row);
            })
            .catch(function (err) {
                if (body) {
                    body.innerHTML = '<div class="alert alert-warning mb-0">' + acdEsc(err.message || 'No se pudo cargar el expediente.') + '</div>';
                }
            });
    }

    function acdSetBadge(key, n) {
        const el = document.getElementById(ACD_BADGE[key]);
        if (!el) return;
        if (n > 0) {
            el.textContent   = n;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    function acdTituloContextualModal(nombreCliente, idCredito) {
        const nombre = String(nombreCliente || '').trim() || 'Cliente';
        const credito = String(idCredito || '').trim();
        return '<i class="fa-solid fa-file-circle-check me-2 text-warning"></i>'
            + 'CIERRE DOCUMENTADO &mdash; '
            + '<span class="acd-modal-context-client">' + acdEsc(nombre) + (credito ? ' (' + acdEsc(credito) + ')' : '') + '</span>';
    }

    function acdCargarConteosPestanas() {
        return fetch('/AtencionClientes/obtenerConteosCierreDocumentacion', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.conteos) return;
                const c = data.conteos;
                acdSetBadge('bandeja', c.bandeja);
                acdSetBadge('dictaminado', c.dictaminado);
            })
            .catch(function () {});
    }

    function acdCargarSeccion(key, forzar) {
        const cfg = ACD_CONFIG[key];
        if (!cfg) return Promise.resolve();

        const suf      = key === 'bandeja' ? 'bandeja' : 'dictaminado';
        const loaderId = 'acd-loader-' + suf;
        const listaId  = 'acd-lista-'  + suf;

        if (!forzar && _acdCargada[key]) {
            return Promise.resolve();
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return Promise.resolve();

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('acd-lista-updating');
        }

        return fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar');
                }
                const datos = Array.isArray(data.datos) ? data.datos.slice() : [];
                const n = datos.length;
                _acdDatos[key] = datos;
                acdSetBadge(key, n);
                _acdCargada[key] = true;

                lista.innerHTML = acdRenderTabla(key, datos);
                acdInicializarTabla(key);
            })
            .catch(function (err) {
                lista.innerHTML = `<div class="alert alert-danger">${acdEsc(err.message || 'Error')}</div>`;
                acdSetBadge(key, 0);
            })
            .finally(function () {
                loader.style.display = 'none';
                lista.classList.remove('acd-lista-updating');
            });
    }

    function acdCargarVistaInicialConSpinner() {
        const hasSwal = typeof Swal !== 'undefined';
        if (hasSwal) {
            Swal.fire({
                title: 'Cargando Cartera…',
                html: '<span style="font-size:.875rem;color:#64748b;">Obteniendo bandeja de entrada</span>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); },
            });
        }
        Promise.all([
            acdCargarSeccion('bandeja', true),
            acdCargarConteosPestanas()
        ]).finally(function () {
            if (hasSwal) Swal.close();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        acdCargarVistaInicialConSpinner();

        const bb = document.getElementById('acd-tab-bandeja-btn');
        const bd = document.getElementById('acd-tab-dictaminado-btn');
        if (bb) {
            bb.addEventListener('shown.bs.tab', function () { acdCargarSeccion('bandeja', false); });
        }
        if (bd) {
            bd.addEventListener('shown.bs.tab', function () { acdCargarSeccion('dictaminado', false); });
        }

        const chkS2 = document.getElementById('acdChkConfirmoS2');
        const btnReg = document.getElementById('acdBtnRegistrarConfirmacionS2');
        if (chkS2 && btnReg) {
            chkS2.addEventListener('change', function () {
                btnReg.disabled = !chkS2.checked;
            });
        }

        const btnSubEv = document.getElementById('acdBtnSubirEvidenciaCierre');
        const btnVerEv = document.getElementById('acdBtnVerEvidenciaCierre');
        const dictBody = document.getElementById('acdDictaminadoDetalleBody');
        if (btnSubEv) {
            btnSubEv.addEventListener('click', function () {
                const idOp = _acdCierreIdOp;
                const input = document.getElementById('acdEvidenciaArchivo');
                if (!idOp || !input || !input.files || !input.files[0]) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', text: 'Selecciona una imagen o PDF.' });
                    }
                    return;
                }
                btnSubEv.disabled = true;
                const fd = new FormData();
                fd.append('id_operacion', String(idOp));
                fd.append('slot', 'doc_cierre_s2');
                fd.append('archivo', input.files[0]);
                fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success || !data.url) {
                            throw new Error(data.message || 'No se pudo subir el documento.');
                        }
                        _acdEvidenciaUrl = String(data.url);
                        const com = (document.getElementById('acdEvidenciaComentarios') || {}).value || '';
                        const titEv = document.getElementById('acdEvidenciaTitulo');
                        const formEv = document.getElementById('acdEvidenciaFormulario');
                        const okEv = document.getElementById('acdEvidenciaExito');
                        if (titEv) {
                            titEv.textContent = 'DOCUMENTO CARGADO CON EXITO';
                            titEv.classList.add('acd-ev-ok');
                        }
                        if (formEv) formEv.style.display = 'none';
                        if (okEv) okEv.style.display = '';
                        if (chkS2) chkS2.disabled = false;
                        if (String(com).trim() !== '') {
                            return fetch('/MotosAdjudicadas/agregarObservacion', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    id_operacion: idOp,
                                    etapa: 'Cierre Documentado',
                                    area: 'Cierre documentación',
                                    texto: 'Comentarios documento cierre S2: ' + String(com).trim(),
                                }),
                            }).then(function (r) { return r.json(); });
                        }
                    })
                    .catch(function (err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', text: err.message || 'Error' });
                        }
                    })
                    .finally(function () {
                        btnSubEv.disabled = false;
                    });
            });
        }
        if (btnVerEv) {
            btnVerEv.addEventListener('click', function () {
                if (_acdEvidenciaUrl) {
                    acdAbrirVisorDocumento(_acdEvidenciaUrl, 'Evidencia de cierre documentado');
                }
            });
        }
        if (dictBody) {
            dictBody.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-acd-ver-evidencia-dict]');
                if (!btn || !_acdDictaminadoEvidenciaUrl) return;
                acdAbrirVisorDocumento(_acdDictaminadoEvidenciaUrl, 'Evidencia de cierre documentado');
            });
        }

        const docViewer = document.getElementById('acdDocViewerOverlay');
        const docViewerClose = document.getElementById('acdDocViewerClose');
        if (docViewer) {
            docViewer.addEventListener('click', function (e) {
                if (e.target === docViewer) acdCerrarVisorDocumento();
            });
        }
        if (docViewerClose) {
            docViewerClose.addEventListener('click', acdCerrarVisorDocumento);
        }
        document.addEventListener('keydown', function (e) {
            const overlay = document.getElementById('acdDocViewerOverlay');
            if (!overlay || overlay.classList.contains('d-none')) return;
            if (e.key === 'Escape') acdCerrarVisorDocumento();
        });

        const filasEtapas = document.getElementById('acdCierreEtapasFilas');
        if (filasEtapas) {
            filasEtapas.addEventListener('click', function (e) {
                const t = e.target.closest('[data-acd-etapa]');
                if (!t || t.disabled) return;
                acdModalBitacoraEtapaMostrar(t.getAttribute('data-acd-etapa'));
            });
        }

        if (btnReg) {
            btnReg.addEventListener('click', function () {
                if (!_acdCierreIdOp || !chkS2 || !chkS2.checked) return;
                btnReg.disabled = true;
                fetch('/MotosAdjudicadas/confirmarCierreDocumentacionEnS2', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id_operacion: _acdCierreIdOp }),
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success) throw new Error(data.message || 'No se pudo registrar.');
                        if (typeof spartaSwalEnviadoOk === 'function') {
                            spartaSwalEnviadoOk(
                                'Confirmación de cierre documentación enviada correctamente. La operación pasó a la bandeja de Recepción.'
                            );
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Enviado',
                                text: 'Confirmación de cierre documentación enviada correctamente. La operación pasó a la bandeja de Recepción.',
                                confirmButtonColor: '#0f172a',
                            });
                        }
                        const mEl = document.getElementById('modalAcdCierreDocumentacion');
                        if (mEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            _acdCierreDebeReaparecerTrasBitacora = false;
                            const inst = bootstrap.Modal.getInstance(mEl);
                            if (inst) inst.hide();
                        }
                        Promise.all([
                            acdCargarSeccion('bandeja', true),
                            acdCargarConteosPestanas()
                        ]);
                    })
                    .catch(function (err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', text: err.message || 'Error' });
                        }
                        btnReg.disabled = false;
                    });
            });
        }

        const modalCierre = document.getElementById('modalAcdCierreDocumentacion');
        if (modalCierre) {
            modalCierre.addEventListener('hidden.bs.modal', function () {
                if (_acdCierreDebeReaparecerTrasBitacora) {
                    return;
                }
                _acdCierreIdOp = 0;
                acdModalCierreDocLimpiarFormulario();
            });
        }
        const modalBitacoraEtapa = document.getElementById('modalAcdCierreVerBitacoraEtapa');
        if (modalBitacoraEtapa) {
            modalBitacoraEtapa.addEventListener('hidden.bs.modal', function () {
                if (!_acdCierreDebeReaparecerTrasBitacora) return;
                _acdCierreDebeReaparecerTrasBitacora = false;
                const mc = document.getElementById('modalAcdCierreDocumentacion');
                if (mc && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    requestAnimationFrame(function () {
                        bootstrap.Modal.getOrCreateInstance(mc).show();
                    });
                }
            });
        }

        /* Delegación: onclick inline + JSON.stringify(nombre) rompía el HTML por comillas dobles en el atributo */
        const listaBandeja = document.getElementById('acd-lista-bandeja');
        if (listaBandeja) {
            listaBandeja.addEventListener('click', function (e) {
                const btn = e.target.closest('.acd-abrir-modal-cierre-doc');
                if (!btn) {
                    return;
                }
                const idOp = parseInt(btn.getAttribute('data-acd-id-operacion'), 10) || 0;
                const idCred = parseInt(btn.getAttribute('data-acd-id-credito'), 10) || 0;
                let nombre = '';
                try {
                    nombre = decodeURIComponent(btn.getAttribute('data-acd-nombre') || '');
                } catch (err2) {
                    nombre = '';
                }
                if (typeof window.acdModalCierreDocAbrir === 'function') {
                    window.acdModalCierreDocAbrir(idOp, idCred, nombre);
                }
            });
        }

        const listaDictaminado = document.getElementById('acd-lista-dictaminado');
        if (listaDictaminado) {
            listaDictaminado.addEventListener('click', function (e) {
                const btn = e.target.closest('.acd-ver-dictaminado');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();
                acdAbrirVistaDictaminado(btn.getAttribute('data-acd-id-operacion'));
            });
        }
    });
})();
</script>
