<style>
.ar-header-gradient {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.ar-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.ar-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.88; color: #fff; }
.ar-header-gradient i  { color: #fff; }


.ac-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.ac-card:hover { box-shadow: 0 4px 18px rgba(15,118,110,.12); }
.ac-card.ar-card-dict {
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
.ae-list-ev .ac-val { font-weight: 700; }
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

.ar-btn-pipeline {
    background: #2563eb;
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
.ar-btn-pipeline:hover  { opacity: .9; transform: translateY(-1px); color: #fff; }
.ar-btn-pipeline:active { transform: translateY(0); }

.ar-btn-evidencias {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
    border: none;
    color: #fff !important;
    font-weight: 700;
    font-size: .76rem;
    padding: .38rem .9rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s, transform .15s;
}
.ar-btn-evidencias:hover  { opacity: .92; color: #fff !important; transform: translateY(-1px); }
.ar-btn-evidencias:active { transform: translateY(0); }

/* Forzar tono de tabs al color propio de Recuperación */
.ar-table-wrap {
    border: 1px solid #e5e7eb;
    border-radius: .75rem;
    overflow: visible;
    background: #fff;
    padding: 1.5rem;
}
.ar-table { margin: 0; font-size: .875rem; vertical-align: middle; }
.ar-table thead th {
    background: #f8fafc;
    color: #566a7f;
    border-bottom: 1px solid #dbe4ef;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .02em;
    text-transform: uppercase;
    white-space: nowrap;
}
.ar-table tbody tr:hover { background: #f8fbff; }
.ar-table td { color: #697a8d; border-color: #e8eef5; }
.ar-table-main { min-width: 260px; }
.ar-table-folio {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    padding: .12rem .42rem;
    border-radius: 999px;
    background: #f0fdfa;
    color: #0f766e;
    font-size: .68rem;
    font-weight: 800;
}
.ar-table-credit {
    display: block;
    margin-top: .22rem;
    color: #566a7f;
    font-weight: 700;
}
.ar-table-main-client {
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
.ar-table-main-client i,
.ar-table-seguimiento-label i,
.ar-rec-time i {
    color: #64748b;
    font-size: .72rem;
    line-height: 1;
    margin-top: .08rem;
    flex: 0 0 auto;
}
.ar-table-name {
    min-width: 210px;
    color: #697a8d;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.2;
}
.ar-table-muted { color: #94a3b8; font-style: italic; }
.ar-table-evidence {
    width: 260px;
    max-width: 260px;
    white-space: nowrap;
    font-weight: 700;
    color: #566a7f;
}
.ar-table-status { white-space: nowrap; font-weight: 700; color: #566a7f; }
.ar-rec-pill {
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
.ar-rec-pill--ok { background: #dcfce7; color: #15803d; }
.ar-rec-pill--work { background: #ccfbf1; color: #0f766e; }
.ar-rec-pill--sent { background: #dbeafe; color: #1d4ed8; }
.ar-rec-detail {
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
.ar-rec-detail span {
    display: flex;
    align-items: center;
    gap: .28rem;
}
.ar-rec-detail i {
    width: .78rem;
    flex: 0 0 .78rem;
    font-size: .66rem;
    text-align: center;
}
.ar-rec-detail-ok i { color: #22c55e; }
.ar-rec-detail-doc i { color: #0d9488; }
.ar-rec-time {
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
.ar-rec-time-label {
    display: inline-flex;
    align-items: flex-start;
    gap: .34rem;
}
.ar-rec-time strong {
    display: inline;
    margin-top: 0;
    margin-left: .35rem;
    color: #566a7f;
    font-size: .76rem;
    font-weight: 800;
    text-transform: none;
    letter-spacing: 0;
}
.ar-rec-time small {
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
.ar-table-status-stack {
    display: flex;
    flex-direction: column;
    gap: .18rem;
    min-width: 190px;
}
.ar-table-status-line {
    line-height: 1.25;
}
.ar-table-status-pill {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    border: 1px solid #bbf7d0;
    border-radius: 999px;
    background: #f0fdf4;
    color: #15803d;
    font-size: .7rem;
    font-weight: 800;
    line-height: 1;
    padding: .28rem .55rem;
    white-space: nowrap;
}
.ar-table-status-label {
    color: #94a3b8;
    font-size: .68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .02em;
    margin-right: .25rem;
}
.ar-table-seguimiento {
    min-width: 270px;
}
.ar-table-seguimiento-row {
    display: block;
}
.ar-table-seguimiento-label {
    display: flex;
    align-items: flex-start;
    gap: .34rem;
    color: #64748b;
    font-size: .66rem;
    font-weight: 800;
    line-height: 1.1;
    text-transform: uppercase;
    letter-spacing: .025em;
}
.ar-table-seguimiento-date {
    display: block;
    margin-top: .12rem;
    color: #566a7f;
    font-size: .78rem;
    font-weight: 700;
    line-height: 1.15;
}
.ar-table-seguimiento-row + .ar-table-seguimiento-row {
    margin-top: .42rem;
    padding-top: .36rem;
    border-top: 1px solid #e2e8f0;
}
.ar-table-action {
    width: 74px;
    min-width: 74px;
    text-align: center !important;
}
.ar-table-actions {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    flex-wrap: wrap;
}
#arTabContent .dataTables_length,
#arTabContent .dataTables_filter { margin-bottom: 1rem; }
#arTabContent .dataTables_filter { text-align: right; }
#arTabContent .dataTables_filter input {
    margin-left: .5rem;
    padding: .375rem .75rem;
    border: 1px solid #d9dee3;
    border-radius: .375rem;
}
#arTabContent .dataTables_filter input:focus {
    border-color: #0d9488;
    outline: none;
    box-shadow: 0 0 0 .2rem rgba(13, 148, 136, .15);
}
#arTabContent .dataTables_length select {
    margin: 0 .5rem;
    padding: .375rem 1.75rem .375rem .75rem;
}
#arTabContent .dataTables_info { margin: 0; color: #6c757d; font-size: .85rem; }
#arTabContent .ar-dt-footer {
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
#arTabContent .ar-dt-footer > [class*="col-"] { padding-left: 0; padding-right: 0; }
#arTabContent .ar-dt-info { flex: 1 1 auto !important; width: auto !important; max-width: none !important; }
#arTabContent .ar-dt-pages {
    flex: 0 0 auto !important;
    width: auto !important;
    max-width: none !important;
    margin-left: auto;
    display: flex !important;
    justify-content: flex-end !important;
}
#arTabContent .ar-dt-footer .dataTables_paginate {
    display: flex;
    justify-content: flex-end;
    width: auto;
    margin: 0 !important;
}
#arTabContent .ar-dt-footer .dataTables_paginate .pagination {
    justify-content: flex-end !important;
    margin: 0 !important;
    margin-left: auto !important;
}
#arTabContent .ar-dt-footer .dataTables_paginate .page-item { margin: 0 .18rem; }

#arTabNav .nav-link {
    color: #0f172a;
}
#arTabNav .nav-link.active {
    background-color: #0d9488 !important;
    border-color: #0d9488 !important;
    color: #fff !important;
}
#arTabNav .nav-link:hover:not(.active),
#arTabNav .nav-link:focus:not(.active) {
    color: #0d9488;
}

/* ── Modal evidencias (Recuperación) ── */
/* dark-mode.css fuerza .modal-header a fondo blanco (!important); sin esto el título text-white no se ve */
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header {
    background: #fff !important;
    border: none !important;
    border-bottom: 1px solid #e2e8f0 !important;
    color: #0f172a !important;
    padding: .85rem 1.15rem;
}
#modalArRecuperacionEvidencias .ar-ev-modal-title-wrap {
    min-width: 0;
}
#modalArRecuperacionEvidencias .ar-ev-modal-title-line {
    display: flex;
    align-items: center;
    gap: .55rem;
    flex-wrap: wrap;
    font-weight: 900;
}
#modalArRecuperacionEvidencias .ar-ev-modal-title-main {
    font-weight: 900;
    color: #111827 !important;
}
#modalArRecuperacionEvidencias #ar-ev-titulo-cliente {
    font-weight: 400;
}
#modalArRecuperacionEvidencias .ar-ev-context-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .22rem .58rem;
    border-radius: 999px;
    color: #fff !important;
    font-size: .62rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: .03em;
    text-transform: uppercase;
    border: 1px solid rgba(255, 255, 255, .28);
}
#modalArRecuperacionEvidencias .ar-ev-context-badge--bandeja { background: rgba(13, 148, 136, .95); }
#modalArRecuperacionEvidencias .ar-ev-context-badge--dictaminado { background: rgba(22, 163, 74, .95); }
#modalArRecuperacionEvidencias .ar-ev-modal-subtitle {
    display: block;
    margin-top: .22rem;
    color: #111827 !important;
    font-size: .75rem;
    font-weight: 700;
    line-height: 1.25;
}
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .modal-title,
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .modal-title span,
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-subtitle,
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-subtitle strong {
    color: #0f172a !important;
}
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-context-badge,
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-context-badge span,
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-context-badge i {
    color: #fff !important;
}
/* En cabecera el progreso inline no debe usar el verde oscuro del cuerpo del modal */
#modalArRecuperacionEvidencias .modal-header.ar-ev-modal-header .ar-ev-prog-lbl {
    color: #0f766e !important;
}
#modalArRecuperacionEvidencias .modal-header .btn-close { filter: none; }
#modalArRecuperacionEvidencias .modal-dialog.modal-xl {
    max-width: min(72rem, 98vw);
}
#modalArRecuperacionEvidencias .ar-ev-prog-bg {
    height: 8px;
    background: #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: .75rem;
}
#modalArRecuperacionEvidencias .ar-ev-prog-fill {
    height: 100%;
    background: linear-gradient(90deg, #0d9488, #5eead4);
    border-radius: 6px;
    transition: width .25s ease;
}
#modalArRecuperacionEvidencias .ar-ev-prog-lbl { font-size: .8rem; font-weight: 800; color: #0f766e; }

.ar-ev-sec { margin-bottom: 1rem; }
.ar-ev-hdr {
    display: flex; align-items: center; justify-content: space-between; gap: .45rem;
    padding: .38rem .65rem;
    border-radius: .45rem .45rem 0 0;
    font-size: .62rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
}
.ar-ev-hdr-title { display: inline-flex; align-items: center; gap: .45rem; min-width: 0; }
.ar-ev-hdr-status {
    margin-left: auto;
    font-size: .66rem;
    font-weight: 900;
    letter-spacing: .02em;
    color: #15803d;
    text-transform: uppercase;
    white-space: nowrap;
}
.ar-ev-hdr-left { justify-content: flex-start; }
.ar-ev-hdr-download {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border: 1px solid #38bdf8;
    border-radius: 999px;
    padding: .32rem .7rem;
    background: linear-gradient(135deg, #06b6d4, #2563eb);
    color: #fff !important;
    box-shadow: 0 6px 14px rgba(37, 99, 235, .22);
    font-size: .68rem;
    font-weight: 900;
    line-height: 1;
    text-transform: none;
    letter-spacing: 0;
    transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
}
.ar-ev-hdr-download:hover {
    color: #fff !important;
    filter: brightness(1.03);
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(37, 99, 235, .28);
}
.ar-ev-hdr-orange { background: #fff7ed; border: 1px solid #fed7aa; border-bottom: 0; color: #9a3412; }
.ar-ev-hdr-blue   { background: #eff6ff; border: 1px solid #bfdbfe; border-bottom: 0; color: #1e40af; }
.ar-ev-hdr-green  { background: #f0fdf4; border: 1px solid #bbf7d0; border-bottom: 0; color: #14532d; }
.ar-ev-hdr-purple { background: #faf5ff; border: 1px solid #e9d5ff; border-bottom: 0; color: #6b21a8; }

.ar-ev-slots-wrap {
    padding: .55rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-top: 0;
    border-radius: 0 0 .45rem .45rem;
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: .45rem;
}
@media (max-width: 1199.98px) { #modalArRecuperacionEvidencias .ar-ev-slots-wrap { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
@media (max-width: 991.98px)  { #modalArRecuperacionEvidencias .ar-ev-slots-wrap { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
@media (max-width: 767.98px)  { #modalArRecuperacionEvidencias .ar-ev-slots-wrap { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 575.98px)  { #modalArRecuperacionEvidencias .ar-ev-slots-wrap { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.ar-ev-slot {
    width: 100%;
    min-width: 0;
    height: 108px;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: .5rem;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .15rem;
}
.ar-ev-slot--vac { border-style: dashed; color: #94a3b8; font-size: .62rem; text-align: center; padding: .25rem; }
.ar-ev-slot--pend { border-color: #f59e0b; }
.ar-ev-slot--acep { border-color: #22c55a; background: #f0fdf4; }
.ar-ev-slot--rec  { border-color: #ef4444; background: #fef2f2; }
.ar-ev-slot--click { cursor: pointer; }
.ar-ev-slot .ar-ev-thumb {
    position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
}
.ar-ev-slot .ar-ev-lbl {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(15,23,42,.78); color: #fff;
    font-size: .5rem; font-weight: 700; text-transform: uppercase;
    padding: .15rem; text-align: center;
}
.ar-ev-badge-mini {
    position: absolute; bottom: 1.15rem; left: 50%; transform: translateX(-50%);
    font-size: .5rem; font-weight: 800; padding: 1px 5px; border-radius: 3px; color: #fff;
}
.ar-ev-badge-mini.ok { background: #16a34a; }
.ar-ev-badge-mini.no { background: #dc2626; }

.ar-ev-doc-zone {
    min-height: 34px;
    border: 1px dashed #cbd5e1;
    border-radius: .4rem;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: .22rem .45rem;
    text-align: left;
    gap: .42rem;
    background: #fff;
    cursor: pointer;
    flex-wrap: nowrap;
}
.ar-ev-doc-zone--green { border-color: #86efac; background: #f0fdf4; }
.ar-ev-doc-zone--purple { border-color: #d8b4fe; background: #faf5ff; }
.ar-ev-doc-zone a { font-size: .8rem; font-weight: 700; }
.ar-ev-doc-zone .fa-2x { font-size: .95rem; }
.ar-ev-doc-main {
    display: flex;
    align-items: center;
    gap: .35rem;
    min-width: 0;
    flex: 1 1 auto;
}
.ar-ev-doc-title {
    color: #14532d;
    font-size: .68rem;
    font-weight: 800;
    line-height: 1.1;
    white-space: nowrap;
}
.ar-ev-doc-sub {
    color: #64748b;
    font-size: .64rem;
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ar-ev-doc-zone--purple .ar-ev-doc-sub { color: #6b7280; }
.ar-ev-doc-title--purple { color: #5b21b6; }
.ar-ev-doc-actions {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    flex-wrap: nowrap;
    margin-left: auto;
}
.ar-ev-btn-ver-doc {
    border: 2px solid #22c55a;
    color: #14532d;
    background: #fff;
    border-radius: 2rem;
    font-weight: 800;
    font-size: .66rem;
    padding: .18rem .65rem;
    line-height: 1.1;
    white-space: nowrap;
}
.ar-ev-btn-ver-doc:hover,
.ar-ev-btn-ver-doc:focus {
    background: #dcfce7;
    border-color: #16a34a;
    color: #14532d;
}
.ar-ev-btn-ver-doc--purple {
    border-color: #a78bfa;
    color: #5b21b6;
}
.ar-ev-btn-ver-doc--purple:hover,
.ar-ev-btn-ver-doc--purple:focus {
    background: #f3e8ff;
    border-color: #7c3aed;
    color: #5b21b6;
}
.ar-ev-doc-badge {
    border-radius: 999px;
    color: #fff;
    display: inline-block;
    font-size: .52rem;
    font-weight: 800;
    padding: 1px 5px;
    white-space: nowrap;
}
.ar-ev-doc-badge--green { background: #15803d; }
.ar-ev-doc-badge--purple { background: #6d28d9; }

.ar-ev-cartera-panel {
    border-color: #e2e8f0 !important;
    min-height: 120px;
}
.ar-ev-cartera-panel textarea.form-control {
    min-height: 140px;
}
body.dark-mode .ar-ev-cartera-panel { border-color: #334155 !important; }
body.dark-mode .ar-ev-cartera-panel .form-label { color: #e2e8f0; }
body.dark-mode .ar-ev-cartera-panel textarea.form-control {
    background: #1e293b;
    border-color: #475569;
    color: #e2e8f0;
}
body.dark-mode #modalArRecuperacionEvidencias #ar-ev-btn-enviar-cartera {
    color: #fff;
}
.ar-ev-info-panel {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
    gap: .85rem;
    margin-bottom: .9rem;
}
.ar-ev-info-panel--form {
    margin-bottom: 0;
}
.ar-ev-title-pill {
    margin-left: .5rem;
    vertical-align: middle;
}
.ar-ev-title-pill.ar-table-status-pill {
    font-size: .68rem;
    padding: .24rem .55rem;
}
.ar-ev-info-card {
    min-width: 0;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: .6rem;
    padding: .65rem .75rem;
}
.ar-ev-info-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .5rem;
}
.ar-ev-info-title {
    color: #123150;
    font-size: .72rem;
    font-weight: 900;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: .045em;
    margin: 0;
}
.ar-ev-info-pill {
    display: inline-flex;
    align-items: center;
    gap: .32rem;
    border-radius: 999px;
    background: #ccfbf1;
    color: #0f766e;
    font-size: .66rem;
    font-weight: 800;
    padding: .18rem .5rem;
    white-space: nowrap;
}
.ar-ev-info-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .42rem .6rem;
}
.ar-ev-info-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: .34rem;
}
.ar-ev-info-block {
    min-width: 0;
    padding: .08rem .15rem .18rem;
    border-bottom: 1px solid #e2e8f0;
}
.ar-ev-info-block--wide {
    grid-column: span 2;
}
.ar-ev-info-block--series {
    grid-column: span 2;
}
.ar-ev-info-field-head {
    display: flex;
    align-items: center;
    gap: .28rem;
    min-width: 0;
    margin-bottom: .12rem;
}
.ar-ev-info-icon {
    color: #64748b;
    font-size: .68rem;
    width: .85rem;
    flex: 0 0 .85rem;
    text-align: center;
}
.ar-ev-info-label {
    display: block;
    color: #64748b;
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .035em;
    line-height: 1.1;
    margin-bottom: 0;
    text-transform: uppercase;
    white-space: nowrap;
}
.ar-ev-info-value {
    display: flex;
    align-items: center;
    gap: .34rem;
    color: #0f172a;
    font-size: .76rem;
    font-weight: 800;
    line-height: 1.18;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ar-ev-info-color-dot {
    display: inline-block;
    width: .68rem;
    height: .68rem;
    min-width: .68rem;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, .16);
    box-shadow: 0 0 0 2px rgba(255,255,255,.9);
    flex: 0 0 .68rem;
}
body.dark-mode .ar-ev-info-card { background: #0f172a; border-color: #334155; }
body.dark-mode .ar-ev-info-title,
body.dark-mode .ar-ev-info-value { color: #e2e8f0; }
body.dark-mode .ar-ev-info-block { border-color: #334155; }
body.dark-mode .ar-ev-info-label { color: #94a3b8; }
body.dark-mode .ar-ev-info-icon { color: #94a3b8; }
.ar-ev-info-card--formulario {
    margin-bottom: .9rem;
}
.ar-ev-info-card--formulario .ar-ev-info-pill {
    border: 1px solid #fed7aa;
    background: #fff7ed;
    color: #b45309;
    font-weight: 900;
    box-shadow: 0 .08rem .22rem rgba(180, 83, 9, .08);
}
.ar-form-wrap {
    margin-top: .58rem;
    margin-bottom: .9rem;
    padding: .58rem .65rem;
    border: 1px solid #e2e8f0;
    border-radius: .55rem;
    background: #f8fafc;
}
.ar-form-wrap .ae-form-trace {
    margin: 0;
    padding: 0;
    border: 0;
    border-radius: 0;
    background: transparent;
}
.ar-form-wrap .ae-form-trace-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .42rem;
}
.ar-form-wrap .ae-form-trace-title {
    color: #123150;
    font-size: .76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .035em;
}
.ar-form-wrap .ae-form-trace-date {
    display: inline-flex;
    align-items: center;
    gap: .32rem;
    padding: .2rem .58rem;
    border: 1px solid #fed7aa;
    border-radius: 999px;
    background: #fff7ed;
    color: #b45309;
    font-size: .68rem;
    font-weight: 900;
    line-height: 1.05;
    white-space: nowrap;
    box-shadow: 0 .08rem .22rem rgba(180, 83, 9, .08);
}
.ar-form-wrap .ae-form-trace-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: .42rem .55rem;
}
.ar-form-wrap .ae-form-trace-columns {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
    gap: .85rem;
    align-items: start;
}
.ar-form-wrap .ae-form-trace-panel { min-width: 0; }
.ar-form-wrap .ae-form-field {
    min-width: 0;
    padding: .08rem .15rem .18rem;
    border-bottom: 1px solid #e2e8f0;
}
.ar-form-wrap .ae-form-field-wide { grid-column: span 2; }
.ar-form-wrap .ae-form-field-series { grid-column: span 2; }
.ar-form-wrap .ae-form-field-head {
    display: flex;
    align-items: center;
    gap: .28rem;
    min-width: 0;
    margin-bottom: .12rem;
}
.ar-form-wrap .ae-form-field-icon {
    color: #64748b;
    font-size: .68rem;
    width: .85rem;
    flex: 0 0 .85rem;
    text-align: center;
}
.ar-form-wrap .ae-form-field-label {
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
.ar-form-wrap .ae-form-field-value {
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
.ar-form-wrap .ae-form-color-dot {
    display: inline-block;
    width: .68rem;
    height: .68rem;
    min-width: .68rem;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, .16);
    box-shadow: 0 0 0 2px rgba(255,255,255,.9);
    flex: 0 0 .68rem;
}
.ar-form-wrap .ae-form-resguardo-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: .34rem;
}
.ar-form-wrap .ae-form-resguardo-list .ae-form-field-wide { grid-column: span 1; }
body.dark-mode .ar-form-wrap { background: #0f172a; border-color: #1f2937; }
body.dark-mode .ar-form-wrap .ae-form-trace-title { color: #e2e8f0; }
body.dark-mode .ar-form-wrap .ae-form-trace-date { background: #422006; border-color: #78350f; color: #fcd34d; box-shadow: none; }
body.dark-mode .ar-form-wrap .ae-form-field { border-color: #334155; }
body.dark-mode .ar-form-wrap .ae-form-field-label,
body.dark-mode .ar-form-wrap .ae-form-field-icon { color: #94a3b8; }
body.dark-mode .ar-form-wrap .ae-form-field-value { color: #e2e8f0; }

/* Visor in-page (fotos, video, PDF) — dentro del modal para z-index / foco */
#modalArRecuperacionEvidencias.ar-ev-ar-vista-abierta { overflow: visible !important; }
#modalArRecuperacionEvidencias .ar-ev-vista-overlay {
    position: fixed; inset: 0; z-index: 10050;
    background: rgba(15, 23, 42, 0.82);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem; pointer-events: auto;
}
#modalArRecuperacionEvidencias .ar-ev-vista-panel {
    width: min(52rem, 96vw);
    height: min(42rem, 92vh);
    overflow: hidden;
    background: #fff; border-radius: 0.75rem;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    padding: 1rem 1.15rem; position: relative; z-index: 1;
    display: flex;
    flex-direction: column;
}
.ar-ev-vista-nav {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}
.ar-ev-vista-nav-btn {
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
.ar-ev-vista-nav-btn:hover:not(:disabled),
.ar-ev-vista-nav-btn:focus:not(:disabled) {
    background: #e0f2fe;
    border-color: #7dd3fc;
}
.ar-ev-vista-nav-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}
.ar-ev-vista-counter {
    color: #64748b;
    font-size: .72rem;
    font-weight: 800;
    min-width: 3.6rem;
    text-align: center;
}
#modalArRecuperacionEvidencias .ar-ev-vista-panel.ar-ev-vista-panel--wide {
    width: min(56rem, 96vw);
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox {
    flex: 1 1 auto;
    min-height: 0;
    height: 28rem;
    max-height: none;
    background: #0f172a; border-radius: 0.5rem;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 0;
    overflow: hidden;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--pdf {
    flex: 1 1 auto;
    min-height: 0;
    height: auto;
    max-height: none;
    background: #fff;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox .ar-ev-vista-img,
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox .ar-ev-vista-video {
    max-width: 100%; max-height: 100%; object-fit: contain;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox iframe {
    width: 100%; height: 100%; min-height: 0; border: 0; border-radius: 0.35rem; background: #fff;
}
/* Zoom fotos/video (igual criterio que menú Evidencias) */
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--zoomable {
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
    padding: 0;
    overflow: hidden;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--zoomable .ar-zoom-toolbar {
    flex-shrink: 0;
    background: rgba(15, 23, 42, 0.96);
    border-top: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 0 0 0.5rem 0.5rem;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--zoomable .ar-zoom-wrap {
    flex: 1;
    overflow: hidden;
    min-height: 0;
    max-height: none;
    border-radius: 0.5rem 0.5rem 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    touch-action: none;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--zoomable .ar-zoom-wrap.ar-zoom-wrap--scaled:not(.ar-zoom-wrap--dragging),
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--zoomable .ar-zoom-wrap.ar-zoom-wrap--scaled:not(.ar-zoom-wrap--dragging) .ar-zoom-media {
    cursor: grab;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--zoomable .ar-zoom-wrap.ar-zoom-wrap--dragging {
    cursor: grabbing;
    user-select: none;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--zoomable .ar-zoom-wrap.ar-zoom-wrap--dragging .ar-zoom-media {
    user-select: none;
    pointer-events: none;
    transition: none;
}
#modalArRecuperacionEvidencias .ar-ev-vista-mediabox.ar-ev-vista-mediabox--zoomable .ar-zoom-media {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    transform-origin: center center;
    transition: transform 0.1s ease-out;
    display: block;
    will-change: transform;
}
#modalArRecuperacionEvidencias .ar-ev-vista-panel--slot #ar-ev-vista-titulo { color: #0f766e; }
.ar-ev-vista-status {
    display: inline-flex;
    align-items: center;
    margin-left: .45rem;
    padding: .16rem .52rem;
    border-radius: 999px;
    font-size: .64rem;
    font-weight: 900;
    line-height: 1;
    vertical-align: middle;
}
.ar-ev-vista-status--ok { background: #dcfce7; color: #15803d; }
.ar-ev-vista-status--bad { background: #fee2e2; color: #dc2626; }
#modalArRecuperacionEvidencias .ar-ev-vista-panel--repuve {
    border: 1px solid #bbf7d0;
    box-shadow: 0 22px 55px rgba(22, 163, 74, 0.14);
}
#modalArRecuperacionEvidencias .ar-ev-vista-panel--repuve #ar-ev-vista-titulo { color: #14532d; }
#modalArRecuperacionEvidencias .ar-ev-vista-panel--factura {
    border: 1px solid #e9d5ff;
    box-shadow: 0 22px 55px rgba(124, 58, 237, 0.12);
}
#modalArRecuperacionEvidencias .ar-ev-vista-panel--factura #ar-ev-vista-titulo { color: #5b21b6; }
body.dark-mode #modalArRecuperacionEvidencias .ar-ev-vista-panel { background: #1e293b; }

.ar-descarga-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
}
@media (max-width: 1199.98px) { .ar-descarga-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 767.98px) { .ar-descarga-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 480px) { .ar-descarga-grid { grid-template-columns: 1fr; } }
.ar-descarga-card {
    position: relative;
    border: 1px solid #dbeafe;
    border-radius: .75rem;
    background: #fff;
    padding: .55rem;
    min-height: 10.6rem;
    display: flex;
    flex-direction: column;
    gap: .45rem;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
}
.ar-descarga-card.is-checked {
    border-color: #14b8a6;
    box-shadow: 0 10px 22px rgba(20, 184, 166, .13);
}
.ar-descarga-check {
    position: absolute;
    top: .45rem;
    right: .45rem;
    width: 1.2rem;
    height: 1.2rem;
    z-index: 2;
}
.ar-descarga-prev {
    height: 6.4rem;
    border: 1px solid #e2e8f0;
    border-radius: .55rem;
    background: #f8fafc;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ar-descarga-prev img,
.ar-descarga-prev video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.ar-descarga-prev i {
    font-size: 1.75rem;
    color: #1d4ed8;
}
.ar-descarga-title {
    color: #173756;
    font-weight: 800;
    font-size: .78rem;
    line-height: 1.15;
    min-height: 1.8rem;
    padding-right: 1.35rem;
}
.ar-ev-notas-panel {
    border: 1px solid #e2e8f0;
    border-radius: .65rem;
    background: #f8fafc;
    padding: .85rem;
    min-height: 180px;
    max-height: 420px;
    overflow-y: auto;
}
.ar-ev-notas-panel .ar-ev-notas-title {
    font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
    color: #64748b; margin-bottom: .5rem;
}
.ar-ev-bit-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.ar-ev-bit-item {
    position: relative;
    padding: 0 0 .72rem .9rem;
    border-left: 2px solid #cbd5e1;
}
.ar-ev-bit-item:last-child { padding-bottom: 0; }
.ar-ev-bit-item::before {
    content: "";
    position: absolute;
    left: -.32rem;
    top: .08rem;
    width: .55rem;
    height: .55rem;
    border-radius: 999px;
    background: #14b8a6;
    box-shadow: 0 0 0 3px #ccfbf1;
}
.ar-ev-bit-action {
    color: #1e293b;
    font-size: .72rem;
    font-weight: 850;
    line-height: 1.2;
}
.ar-ev-bit-meta {
    margin-top: .18rem;
    color: #64748b;
    font-size: .66rem;
    font-weight: 750;
    line-height: 1.18;
}
.ar-ev-bit-empty {
    color: #94a3b8;
    font-size: .76rem;
    text-align: center;
    padding: .75rem 0;
}

body.dark-mode .ac-card              { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-card.ar-card-dict { background: #111827; }
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
body.dark-mode .ar-ev-slots-wrap { background: #0f172a; border-color: #334155; }
body.dark-mode .ar-ev-slot { background: #1e293b; border-color: #475569; }
body.dark-mode .ar-ev-notas-panel { background: #0f172a; border-color: #334155; }
body.dark-mode .ar-ev-bit-action { color: #e2e8f0; }
body.dark-mode .ar-ev-bit-meta { color: #94a3b8; }
body.dark-mode .ar-ev-bit-item { border-color: #334155; }
body.dark-mode .ar-table-wrap { background: #111827; border-color: #1f2937; }
body.dark-mode .ar-table thead th { background: #0f172a; color: #e2e8f0; border-color: #1f2937; }
body.dark-mode .ar-table tbody tr:hover { background: #172033; }
body.dark-mode .ar-table td { color: #cbd5e1; border-color: #1f2937; }
body.dark-mode .ar-table-credit,
body.dark-mode .ar-table-name,
body.dark-mode .ar-table-evidence,
body.dark-mode .ar-table-status { color: #e2e8f0; }
body.dark-mode #arTabContent .dataTables_filter input,
body.dark-mode #arTabContent .dataTables_length select { background: #111827; border-color: #1f2937; color: #e2e8f0; }

@media (max-width: 991.98px) {
    .ae-list-grid {
        grid-template-columns: repeat(2, minmax(220px, 1fr));
    }
    .ar-ev-info-panel {
        grid-template-columns: 1fr;
    }
    .ar-ev-info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (max-width: 767.98px) {
    #arTabContent .ar-dt-footer {
        justify-content: center !important;
        text-align: center;
    }
    #arTabContent .ar-dt-info,
    #arTabContent .ar-dt-pages {
        flex: 0 0 100% !important;
        width: 100% !important;
        margin-left: 0;
        justify-content: center !important;
    }
    #arTabContent .ar-dt-footer .dataTables_paginate {
        justify-content: center;
        width: 100%;
    }
    #arTabContent .ar-dt-footer .dataTables_paginate .pagination {
        justify-content: center !important;
        margin-left: 0 !important;
    }
    .ac-card-body {
        flex-direction: column;
        align-items: stretch;
    }
    .ae-list-grid {
        grid-template-columns: 1fr;
    }
    .ar-ev-info-grid {
        grid-template-columns: 1fr;
    }
    .ar-ev-info-block--wide {
        grid-column: span 1;
    }
    .ae-list-action {
        justify-content: flex-end;
        margin-top: .2rem;
    }
}

.ar-lista-updating {
    opacity: 0.5;
    transition: opacity 0.12s ease;
    pointer-events: none;
}
</style>

<div class="container-fluid py-4">

    <div class="ar-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-truck-moving fa-2x"></i>
        <div>
            <h4>2.- Recuperación</h4>
            <p>Gestión de recuperación para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-pills flex-column flex-md-row mb-3 gap-md-0 gap-2 border-0"
                id="arTabNav" role="tablist"
                style="--bs-nav-pills-link-active-bg: #0d9488; --bs-nav-pills-link-active-color: #fff;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ar-tab-bandeja-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#arTabBandeja">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="ar-badge-bandeja" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ar-tab-dictaminado-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#arTabDictaminado">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Dictaminado
                        <span class="badge bg-label-secondary ms-1" id="ar-badge-dictaminado" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="arTabContent">
            <div class="tab-pane fade show active" id="arTabBandeja" role="tabpanel">
                <div id="ar-loader-bandeja" class="text-center py-5 text-muted" style="display:block;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ar-lista-bandeja"></div>
            </div>

            <div class="tab-pane fade" id="arTabDictaminado" role="tabpanel">
                <div id="ar-loader-dictaminado" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ar-lista-dictaminado"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal evidencias (vista Recuperación — bandeja) -->
<div class="modal fade" id="modalArRecuperacionEvidencias" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header ar-ev-modal-header">
                <div class="flex-grow-1 min-w-0">
                    <h5 class="modal-title mb-0">
                        <span class="ar-ev-modal-title-wrap">
                            <span class="ar-ev-modal-title-line">
                                <span class="ar-ev-modal-title-main">
                                    <i class="fa-solid fa-motorcycle me-2"></i>Recuperacion &mdash;
                                    <span id="ar-ev-titulo-cliente">&mdash;</span>
                                    <span id="ar-ev-estatus-cliente" class="ar-table-status-pill ar-ev-title-pill d-none"></span>
                                </span>
                                <span id="ar-ev-context-badge" class="ar-ev-context-badge ar-ev-context-badge--bandeja d-none">
                                    <i class="fa-solid fa-inbox"></i><span>Bandeja de entrada</span>
                                </span>
                            </span>
                            <span class="ar-ev-modal-subtitle" id="ar-ev-context-subtitle"></span>
                        </span>
                    </h5>
                    <span class="d-none" id="ar-ev-prog-inline">0 / 14</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <input type="file" id="ar-ev-inp-repuve" class="d-none" accept="application/pdf,.pdf" tabindex="-1">
            <input type="file" id="ar-ev-inp-factura" class="d-none" accept="application/pdf,.pdf,image/jpeg,image/png" tabindex="-1">
            <div class="modal-body p-3" id="ar-ev-modal-inner">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm" style="color:#14b8a6;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 d-flex justify-content-end align-items-center gap-2 flex-wrap">
                <button type="button" id="ar-ev-btn-enviar-cartera" class="btn btn-primary btn-sm rounded-pill fw-semibold"
                        style="display:none;">
                    <i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera
                </button>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
    <div id="ar-ev-vista-overlay" class="ar-ev-vista-overlay d-none" role="dialog" aria-modal="true" aria-labelledby="ar-ev-vista-titulo">
        <div class="ar-ev-vista-panel" tabindex="-1">
            <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                <h6 class="mb-0 me-auto" id="ar-ev-vista-titulo" style="font-size:1rem;font-weight:700;">Vista previa de recuperacion</h6>
                <div class="ar-ev-vista-nav" id="ar-ev-vista-nav" aria-label="Navegacion de evidencias">
                    <button type="button" class="ar-ev-vista-nav-btn" id="ar-ev-vista-prev" aria-label="Evidencia anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <span class="ar-ev-vista-counter" id="ar-ev-vista-counter">1 / 1</span>
                    <button type="button" class="ar-ev-vista-nav-btn" id="ar-ev-vista-next" aria-label="Evidencia siguiente">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-light border" id="ar-ev-vista-btn-cerrar" aria-label="Cerrar vista">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="ar-ev-vista-mediabox" id="ar-ev-vista-mediabox"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalArDescargarEvidencias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <div>
                    <h5 class="modal-title mb-0 fw-bold" style="color:#173756;">
                        <i class="fa-solid fa-download me-2"></i>Descargar evidencias
                    </h5>
                    <div class="text-muted small">Selecciona las evidencias que quieres descargar.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
                    <label class="d-inline-flex align-items-center gap-2 fw-semibold mb-0" style="color:#173756;">
                        <input type="checkbox" id="ar-descarga-check-all" class="form-check-input mt-0" checked>
                        Seleccionar todas
                    </label>
                    <span class="badge rounded-pill bg-light text-primary border" id="ar-descarga-contador">0 seleccionadas</span>
                </div>
                <div id="ar-descarga-grid" class="ar-descarga-grid"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="ar-descarga-btn-confirmar" class="btn btn-primary btn-sm rounded-pill fw-semibold">
                    <i class="fa-solid fa-file-zipper me-1"></i>Descargar seleccionadas
                </button>
            </div>
        </div>
    </div>
</div>

<?php
if (!function_exists('sparta_public_web_base')) {
    require_once dirname(__DIR__) . '/core/UploadsPaths.php';
}
$arPublicPath = function_exists('sparta_public_web_base') ? sparta_public_web_base() : '';
?>
<script>
(function () {
    'use strict';

    var AR_SERVER_PUBLIC_BASE = <?php echo json_encode($arPublicPath, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    /** Lista: física M1 + Repuve + Factura (sin recolección). */
    const AR_EV_TOTAL_LISTA = 14;

    const AR_SEC_FIS = {
        key: 'fis',
        label: 'Soporte fisico de recuperacion (momento 1)',
        hdr: 'ar-ev-hdr-blue',
        icon: 'fa-camera',
        slots: [
            { key: 'fis_dacion_hoja_1', label: 'Foto dación (hoja 1)', icon: 'fa-file-signature' },
            { key: 'fis_dacion_hoja_2', label: 'Foto dación (hoja 2)', icon: 'fa-file-signature' },
            { key: 'fis_vin', label: 'Foto NIV (VIN)', icon: 'fa-barcode' },
            { key: 'fis_frontal', label: 'Foto frontal', icon: 'fa-camera' },
            { key: 'fis_lateral_der', label: 'Foto lateral derecha', icon: 'fa-camera-rotate' },
            { key: 'fis_trasera', label: 'Foto trasera', icon: 'fa-camera-retro' },
            { key: 'fis_lateral_izq', label: 'Foto lateral izquierda', icon: 'fa-camera-rotate' },
            { key: 'fis_tacometro', label: 'Foto tacómetro', icon: 'fa-gauge-high' },
            { key: 'fis_video_cliente_acuerdo', label: 'Video cliente de acuerdo', icon: 'fa-user-check' },
            { key: 'fis_360_encendida', label: 'Video moto 360 encendida', icon: 'fa-video' },
            { key: 'fis_video_vuelta_prueba', label: 'Video vuelta de prueba', icon: 'fa-road' },
            { key: 'fis_checklist', label: 'Foto checklist', icon: 'fa-list-check' },
        ],
    };

    const AR_IMG_KEYS = [];
    AR_SEC_FIS.slots.forEach(function (sl) { AR_IMG_KEYS.push(sl.key); });
    const AR_TOTAL_VALIDABLE_IMG = AR_IMG_KEYS.length;
    const AR_DOC_KEYS = ['doc_repuve', 'doc_factura'];
    const AR_TOTAL_VALIDABLE_EXPEDIENTE = AR_TOTAL_VALIDABLE_IMG + AR_DOC_KEYS.length;

    const AR_SLOT_LABEL = {};
    AR_SEC_FIS.slots.forEach(function (sl) { AR_SLOT_LABEL[sl.key] = sl.label; });
    AR_SLOT_LABEL.doc_repuve = 'Repuve';
    AR_SLOT_LABEL.doc_factura = 'Factura';

    const AR_CONFIG = {
        bandeja: {
            url:   '/AtencionClientes/obtenerRecuperacionEnTransito',
            vacio: 'No hay operaciones en bandeja. Aparecen aqui las recuperaciones con archivos aprobados y envio validado.',
        },
        dictaminado: {
            url:   '/AtencionClientes/obtenerDictaminadosRecuperacion',
            vacio: 'No hay operaciones dictaminadas con expediente en esta etapa.',
        },
    };

    const AR_BADGE = { bandeja: 'ar-badge-bandeja', dictaminado: 'ar-badge-dictaminado' };

    let _arCargada = { bandeja: false, dictaminado: false };
    let _arEvDetalle = null;
    let _arEvSoloLectura = false;
    let _arZoomTeardown = null;
    let _arReabrirRecuperacionTrasDescarga = false;
    let _arDescargaConfirmada = false;
    let _arVistaCtx = { slot: '', label: '', galeria: [], indice: -1 };

    function arZoomTeardown() {
        if (typeof _arZoomTeardown === 'function') {
            _arZoomTeardown();
            _arZoomTeardown = null;
        }
    }

    /** Misma UX que Evidencias: barra de zoom bajo imagen/video. */
    function arZoomHtmlMedia(innerTag) {
        return (
            '<div class="ar-zoom-wrap" tabindex="-1">' + innerTag + '</div>' +
            '<div class="ar-zoom-toolbar d-flex align-items-center justify-content-center gap-2 flex-wrap py-1 px-2">' +
            '<button type="button" class="btn btn-sm btn-outline-light ar-zoom-btn-minus" title="Alejar" aria-label="Alejar"><i class="fa-solid fa-magnifying-glass-minus"></i></button>' +
            '<span class="small text-white ar-zoom-pct fw-semibold" style="min-width:3.25rem;text-align:center;">100%</span>' +
            '<button type="button" class="btn btn-sm btn-outline-light ar-zoom-btn-plus" title="Acercar" aria-label="Acercar"><i class="fa-solid fa-magnifying-glass-plus"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-light ar-rotate-btn-left" title="Rotar izquierda" aria-label="Rotar izquierda"><i class="fa-solid fa-rotate-left"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-light ar-rotate-btn-right" title="Rotar derecha" aria-label="Rotar derecha"><i class="fa-solid fa-rotate-right"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary ar-zoom-btn-reset d-none">Restablecer</button>' +
            '</div>'
        );
    }

    function arZoomWire(box) {
        arZoomTeardown();
        const wrap = box.querySelector('.ar-zoom-wrap');
        const media = box.querySelector('.ar-zoom-media');
        const pctEl = box.querySelector('.ar-zoom-pct');
        const btnMinus = box.querySelector('.ar-zoom-btn-minus');
        const btnPlus = box.querySelector('.ar-zoom-btn-plus');
        const btnReset = box.querySelector('.ar-zoom-btn-reset');
        const btnRotateLeft = box.querySelector('.ar-rotate-btn-left');
        const btnRotateRight = box.querySelector('.ar-rotate-btn-right');
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

        function commitTransform() {
            if (scale <= 1.02) {
                scale = 1;
                panX = 0;
                panY = 0;
            }
            media.style.transformOrigin = 'center center';
            media.style.transform = 'translate(' + panX + 'px,' + panY + 'px) rotate(' + rotate + 'deg) scale(' + scale + ')';
            if (pctEl) pctEl.textContent = Math.round(scale * 100) + '%';
            wrap.classList.toggle('ar-zoom-wrap--scaled', scale > 1.02);
            if (btnReset) {
                const zoomActivo = Math.abs(scale - 1) > 0.02 || Math.abs(panX) > 1 || Math.abs(panY) > 1 || rotate !== 0;
                btnReset.classList.toggle('d-none', !zoomActivo);
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
            wrap.classList.remove('ar-zoom-wrap--dragging');
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
            wrap.classList.add('ar-zoom-wrap--dragging');
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

        _arZoomTeardown = function () {
            endDragPan();
            wrap.removeEventListener('wheel', onWheel, wheelOpts);
            wrap.removeEventListener('mousedown', onWrapMouseDown);
        };
        commitTransform();
    }

    function arEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function arMinutosTexto(minutos) {
        const n = parseInt(minutos, 10);
        if (!Number.isFinite(n) || n < 0) return '';
        const dias = Math.floor(n / 1440);
        if (dias > 0) return dias + ' día' + (dias === 1 ? '' : 's');
        const horas = Math.floor((n % 1440) / 60);
        const mins = n % 60;
        const out = [];
        if (horas > 0) out.push(horas + ' hora' + (horas === 1 ? '' : 's'));
        if (!out.length && mins > 0) out.push(mins + ' min');
        return out.length ? out.join(' ') : '0 min';
    }

    function arConteosFila(item) {
        const aceptadas = parseInt(item.evidencias_aceptadas_count, 10) || 0;
        const rechazadas = parseInt(item.evidencias_rechazadas_count, 10) || 0;
        const pendientes = parseInt(item.evidencias_pendientes_count, 10) || 0;
        const fisicas = parseInt(item.evidencias_count, 10) || 0;
        const expediente = parseInt(item.recuperacion_expediente_count, 10) || fisicas;
        return {
            aceptadas: aceptadas,
            rechazadas: rechazadas,
            pendientes: pendientes,
            fisicas: Math.min(fisicas, AR_TOTAL_VALIDABLE_IMG),
            expediente: Math.min(expediente, AR_TOTAL_VALIDABLE_EXPEDIENTE)
        };
    }

    function arRenderResumenExpediente(item, key) {
        const r = arConteosFila(item || {});
        const esDictaminado = key === 'dictaminado';
        const minRec = esDictaminado ? item.minutos_total_recuperacion : item.minutos_en_recuperacion;
        const tiempo = arMinutosTexto(minRec);
        const desde = esDictaminado ? (item.fecha_inicio_recuperacion || '') : (item.fecha_entrada_recuperacion || item.fecha_aprobacion_evidencias || '');
        const lista = esDictaminado ? (item.fecha_fin_recuperacion || item.fecha_dictamen || '') : '';
        const pill = esDictaminado
            ? '<span class="ar-rec-pill ar-rec-pill--sent"><i class="fa-solid fa-paper-plane"></i>Enviado a cartera</span>'
            : '';
        const detalle = esDictaminado
            ? ''
            : '<span class="ar-rec-detail">'
                + '<span class="ar-rec-detail-doc"><i class="fa-solid fa-file-circle-check"></i>Expediente ' + r.expediente + '/' + AR_TOTAL_VALIDABLE_EXPEDIENTE + '</span>'
                + '</span>';
        const labelTiempo = 'Tiempo en esta etapa';
        const desdeTexto = desde
            ? (esDictaminado ? 'Desde ' + arEsc(desde) : 'Desde validacion en cobranza<br>' + arEsc(desde))
            : '';
        const listaTexto = lista ? 'Lista ' + arEsc(lista) : '';
        const bloqueTiempo = tiempo
            ? '<span class="ar-rec-time"><span class="ar-rec-time-label"><i class="fa-solid fa-stopwatch"></i>' + labelTiempo + '</span><strong>' + arEsc(tiempo) + '</strong>' + (desdeTexto || listaTexto ? '<small>' + desdeTexto + (desdeTexto && listaTexto ? '<br>' : '') + listaTexto + '</small>' : '') + '</span>'
            : '';
        return pill + detalle + bloqueTiempo;
    }

    function arSetContextoModal(key) {
        const sub = document.getElementById('ar-ev-context-subtitle');
        const badge = document.getElementById('ar-ev-context-badge');
        const cfg = key === 'dictaminado'
            ? {
                icon: 'fa-clipboard-check',
                label: 'Dictaminado',
                cls: 'ar-ev-context-badge--dictaminado',
                subtitle: 'Recuperacion documentada y enviada a cartera.'
            }
            : {
                icon: 'fa-inbox',
                label: 'Bandeja de entrada',
                cls: 'ar-ev-context-badge--bandeja',
                subtitle: 'Expediente en recuperacion pendiente de factura o envio a cartera.'
            };
        if (sub) sub.textContent = cfg.subtitle;
        if (badge) {
            badge.className = 'ar-ev-context-badge ' + cfg.cls + ' d-none';
            badge.innerHTML = '';
        }
    }

    function arInferBaseDesdePathname() {
        const p = (window.location && window.location.pathname) || '';
        const segs = p.split('/').filter(function (x) { return x.length; });
        const k = segs.indexOf('public');
        if (k >= 0) {
            return '/' + segs.slice(0, k + 1).join('/');
        }
        return '';
    }

    function arBasePublic() {
        if (typeof AR_SERVER_PUBLIC_BASE === 'string' && AR_SERVER_PUBLIC_BASE.length > 0) {
            return AR_SERVER_PUBLIC_BASE;
        }
        if (window._arBaseCache !== undefined) {
            return window._arBaseCache;
        }
        const path = (window.location && window.location.pathname) || '';
        let base = '';
        const i = path.indexOf('/public/');
        if (i !== -1) {
            base = path.substring(0, i + '/public'.length);
        } else {
            base = arInferBaseDesdePathname();
        }
        window._arBaseCache = base;
        return base;
    }

    function arUrlForDisplay(u) {
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
        const b = arBasePublic();
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

    function arMapaPorSlot(evidencias) {
        const m = {};
        if (!evidencias || !Array.isArray(evidencias)) {
            return m;
        }
        evidencias.forEach(function (e) {
            if (e && e.slot) {
                m[e.slot] = e;
            }
        });
        return m;
    }

    function arEsVideoSlot(slot, row) {
        return slot === 'fis_360'
            || slot === 'fis_360_encendida'
            || slot === 'fis_video_cliente_acuerdo'
            || slot === 'fis_video_vuelta_prueba'
            || (row && row.tipo && String(row.tipo).toLowerCase().indexOf('video') !== -1);
    }

    function arListaGaleriaFisica() {
        const map = arMapaPorSlot((_arEvDetalle && _arEvDetalle.evidencias) || []);
        return AR_IMG_KEYS
            .filter(function (slot) {
                return map[slot] && map[slot].url;
            })
            .map(function (slot) {
                const row = map[slot];
                return {
                    slot: slot,
                    label: AR_SLOT_LABEL[slot] || slot,
                    url: arUrlForDisplay(row.url),
                    isVideo: arEsVideoSlot(slot, row),
                    estado: arEstadoSlot(row)
                };
            });
    }

    function arActualizarNavVista() {
        const nav = document.getElementById('ar-ev-vista-nav');
        const prev = document.getElementById('ar-ev-vista-prev');
        const next = document.getElementById('ar-ev-vista-next');
        const counter = document.getElementById('ar-ev-vista-counter');
        const total = Array.isArray(_arVistaCtx.galeria) ? _arVistaCtx.galeria.length : 0;
        const idxRaw = parseInt(_arVistaCtx.indice, 10);
        const idx = total ? Math.max(0, Math.min(total - 1, isNaN(idxRaw) ? 0 : idxRaw)) : -1;
        const visible = total > 0 && _arVistaCtx.slot !== 'doc_repuve' && _arVistaCtx.slot !== 'doc_factura';
        if (nav) nav.classList.toggle('d-none', !visible);
        if (counter) counter.textContent = visible ? ((idx + 1) + ' / ' + total) : '0 / 0';
        if (prev) prev.disabled = total <= 1;
        if (next) next.disabled = total <= 1;
    }

    function arNavegarVista(delta) {
        const total = Array.isArray(_arVistaCtx.galeria) ? _arVistaCtx.galeria.length : 0;
        if (total <= 1) return;
        const idxRaw = parseInt(_arVistaCtx.indice, 10);
        const idx = isNaN(idxRaw) ? 0 : idxRaw;
        const nextIdx = ((idx + delta) % total + total) % total;
        const item = _arVistaCtx.galeria[nextIdx];
        if (item) {
            arAbrirVistaSlotMedia(item.url, item.label, item.isVideo, item.slot, item.estado);
        }
    }

    function arEstadoSlot(row) {
        if (!row || !row.url) {
            return 'vac';
        }
        const va = row.val_atn != null && row.val_atn !== '' ? parseInt(row.val_atn, 10) : 0;
        if (va === 1) {
            return 'acep';
        }
        if (va === 2) {
            return 'rec';
        }
        return 'pend';
    }

    function arCuentaValidadasImg(map) {
        let n = 0;
        AR_IMG_KEYS.forEach(function (k) {
            const row = map[k];
            if (row && row.url && arEstadoSlot(row) === 'acep') {
                n++;
            }
        });
        return n;
    }

    function arCuentaDocumentosCargados(map) {
        let n = 0;
        AR_DOC_KEYS.forEach(function (k) {
            const row = map[k];
            if (row && row.url && String(row.url).trim() !== '') {
                n++;
            }
        });
        return n;
    }

    function arRenderSlotHtml(sl, map) {
        const row = map[sl.key];
        const st = arEstadoSlot(row);
        const has = row && row.url;
        const uRaw = has ? arUrlForDisplay(row.url) : '';
        const uEsc = arEsc(uRaw);

        let cls = 'ar-ev-slot';
        if (!has) {
            cls += ' ar-ev-slot--vac';
        } else if (st === 'acep') {
            cls += ' ar-ev-slot--acep ar-ev-slot--click';
        } else if (st === 'rec') {
            cls += ' ar-ev-slot--rec ar-ev-slot--click';
        } else {
            cls += ' ar-ev-slot--pend ar-ev-slot--click';
        }

        if (!has) {
            return `
            <div class="${cls}">
                <i class="fa-solid ${sl.icon} opacity-50" style="font-size:1.1rem;"></i>
                <span style="line-height:1.15;">${arEsc(sl.label)}</span>
            </div>`;
        }

        const esVideo = arEsVideoSlot(sl.key, row);
        const media = esVideo
            ? '<video class="ar-ev-thumb" muted playsinline preload="metadata" src="' + uEsc + '"></video>'
            : '<img class="ar-ev-thumb" src="' + uEsc + '" alt="">';
        let badge = '';
        if (st === 'acep') {
            badge = '<span class="ar-ev-badge-mini ok">ACEPTADA</span>';
        } else if (st === 'rec') {
            badge = '<span class="ar-ev-badge-mini no">RECHAZADA</span>';
        }

        return `
        <div class="${cls}" data-ar-ev-slot="${arEsc(sl.key)}" data-ar-ev-url="${uEsc}" data-ar-ev-lbl="${arEsc(sl.label)}" ${esVideo ? 'data-ar-ev-video="1"' : ''} title="Clic para ver aqui">
            ${media}
            <span class="ar-ev-lbl">${arEsc(sl.label)}</span>
            ${badge}
        </div>`;
    }

    function arRenderSeccion(sec, map) {
        let inner = '';
        sec.slots.forEach(function (sl) {
            inner += arRenderSlotHtml(sl, map);
        });
        const validadas = sec.key === 'fis' ? arCuentaValidadasImg(map) : 0;
        const estatusTxt = validadas >= AR_TOTAL_VALIDABLE_IMG ? 'Evidencias completas ' : 'Evidencias validadas ';
        const estatus = sec.key === 'fis'
            ? '<span class="ar-ev-hdr-status">' + estatusTxt + validadas + '/' + AR_TOTAL_VALIDABLE_IMG + '</span>'
            : '';
        const acciones = sec.key === 'fis'
            ? '<button type="button" id="ar-ev-btn-descargar-evidencias" class="btn btn-sm ar-ev-hdr-download" style="display:none;"><i class="fa-solid fa-cloud-arrow-down"></i>Descargar evidencias</button>'
            : '';
        return `
        <div class="ar-ev-sec">
            <div class="ar-ev-hdr ${sec.hdr}">
                <span class="ar-ev-hdr-title"><i class="fa-solid ${sec.icon}"></i> ${arEsc(sec.label)}</span>
                ${estatus}
                ${acciones}
            </div>
            <div class="ar-ev-slots-wrap">${inner}</div>
        </div>`;
    }

    function arRenderDocRepuve(map) {
        const row = map.doc_repuve;
        const has = row && row.url;
        const url = has ? arUrlForDisplay(row.url) : '';
        if (has) {
            return `
            <div class="ar-ev-doc-zone ar-ev-doc-zone--green">
                <i class="fa-solid fa-file-pdf fa-2x text-success"></i>
                <div class="ar-ev-doc-main">
                    <span class="ar-ev-doc-title">Repuve</span>
                    <span class="ar-ev-doc-sub">${_arEvSoloLectura ? 'PDF en expediente.' : 'PDF en expediente. Toca la zona para reemplazar.'}</span>
                </div>
                <div class="ar-ev-doc-actions">
                    <button type="button" class="ar-ev-btn-ver-doc"
                            data-ar-ev-doc-open="${arEsc(url)}" data-ar-ev-doc-kind="repuve">
                        <i class="fa-solid fa-eye me-1" aria-hidden="true"></i>Ver PDF
                    </button>
                    <span class="ar-ev-doc-badge ar-ev-doc-badge--green">En expediente</span>
                </div>
            </div>`;
        }
        return `
        <div class="ar-ev-doc-zone ar-ev-doc-zone--green" id="ar-ev-zone-repuve" role="button" tabindex="0">
            <i class="fa-solid fa-file-pdf fa-2x text-success opacity-50"></i>
            <div class="ar-ev-doc-main">
                <span class="ar-ev-doc-title">Subir Repuve</span>
                <span class="ar-ev-doc-sub">Solo PDF</span>
            </div>
        </div>`;
    }

    function arRenderDocFactura(map) {
        const row = map.doc_factura;
        const has = row && row.url;
        const url = has ? arUrlForDisplay(row.url) : '';
        if (has) {
            return `
            <div class="ar-ev-doc-zone ar-ev-doc-zone--purple">
                <i class="fa-solid fa-file-invoice fa-2x" style="color:#7c3aed;"></i>
                <div class="ar-ev-doc-main">
                    <span class="ar-ev-doc-title ar-ev-doc-title--purple">Factura</span>
                    <span class="ar-ev-doc-sub">${_arEvSoloLectura ? 'Documento cargado.' : 'Documento cargado. Toca la zona para reemplazar.'}</span>
                </div>
                <div class="ar-ev-doc-actions">
                    <button type="button" class="ar-ev-btn-ver-doc ar-ev-btn-ver-doc--purple"
                            data-ar-ev-doc-open="${arEsc(url)}" data-ar-ev-doc-kind="factura">
                        <i class="fa-solid fa-eye me-1" aria-hidden="true"></i>Ver factura
                    </button>
                    <span class="ar-ev-doc-badge ar-ev-doc-badge--purple">Cargada</span>
                </div>
            </div>`;
        }
        return `
        <div class="ar-ev-doc-zone ar-ev-doc-zone--purple" id="ar-ev-zone-factura" role="button" tabindex="0">
            <i class="fa-solid fa-file-circle-plus fa-2x" style="color:#7c3aed;opacity:.5;"></i>
            <div class="ar-ev-doc-main">
                <span class="ar-ev-doc-title ar-ev-doc-title--purple">Subir Factura</span>
                <span class="ar-ev-doc-sub">PDF o imagen</span>
            </div>
        </div>`;
    }

    /** Tras subir factura (momento 3): comentarios a ancho completo (debajo de Repuve y Factura) */
    function arRenderFacturaCarteraComentarios(map) {
        if (_arEvSoloLectura) {
            return '';
        }
        const row = map.doc_factura;
        const has = row && row.url && String(row.url).trim() !== '';
        if (!has) {
            return '';
        }
        return (
            '<div class="ar-ev-cartera-panel mt-2 pt-3 border-top">' +
            '<label class="form-label fw-bold mb-1 small" for="ar-ev-comentarios-cartera">Comentarios:</label>' +
            '<textarea id="ar-ev-comentarios-cartera" class="form-control form-control-sm" rows="5" ' +
            'placeholder="Observaciones para cartera (opcional)" autocomplete="off"></textarea>' +
            '</div>'
        );
    }

    function arEvToggleFooterEnviarCartera(map) {
        const btn = document.getElementById('ar-ev-btn-enviar-cartera');
        if (!btn) {
            return;
        }
        if (_arEvSoloLectura) {
            btn.style.display = 'none';
            btn.disabled = true;
            return;
        }
        const has = map && map.doc_factura && map.doc_factura.url && String(map.doc_factura.url).trim() !== '';
        btn.style.display = has ? '' : 'none';
        btn.disabled = false;
    }

    function arEvidenciasDescargables(map) {
        const items = [];
        const keys = AR_IMG_KEYS.concat(AR_DOC_KEYS);
        keys.forEach(function (slot) {
            const row = map && map[slot] ? map[slot] : null;
            const url = row && row.url ? arUrlForDisplay(row.url) : '';
            if (!url) {
                return;
            }
            items.push({
                slot: slot,
                label: AR_SLOT_LABEL[slot] || slot,
                url: url,
                isVideo: /video|360|vuelta/i.test(slot) || /\.(mp4|mov|webm|m4v)(\?|#|$)/i.test(url),
                isPdf: arEsPdfPorUrl(url),
            });
        });
        return items;
    }

    function arEvToggleFooterDescarga(map) {
        const btn = document.getElementById('ar-ev-btn-descargar-evidencias');
        if (!btn) {
            return;
        }
        btn.style.display = arEvidenciasDescargables(map).length ? '' : 'none';
        btn.disabled = false;
    }

    function arActualizarContadorDescarga() {
        const grid = document.getElementById('ar-descarga-grid');
        const contador = document.getElementById('ar-descarga-contador');
        const all = document.getElementById('ar-descarga-check-all');
        if (!grid || !contador) {
            return;
        }
        const checks = Array.prototype.slice.call(grid.querySelectorAll('[data-ar-descarga-slot]'));
        const sel = checks.filter(function (x) { return x.checked; }).length;
        contador.textContent = sel + ' seleccionada' + (sel === 1 ? '' : 's');
        checks.forEach(function (chk) {
            const card = chk.closest('.ar-descarga-card');
            if (card) {
                card.classList.toggle('is-checked', chk.checked);
            }
        });
        if (all) {
            all.checked = checks.length > 0 && sel === checks.length;
            all.indeterminate = sel > 0 && sel < checks.length;
        }
    }

    function arRenderPreviewDescarga(item) {
        const url = arEsc(item.url);
        if (item.isVideo) {
            return '<video src="' + url + '" muted playsinline preload="metadata"></video>';
        }
        if (item.isPdf) {
            return '<i class="fa-solid fa-file-pdf"></i>';
        }
        return '<img src="' + url + '" alt="' + arEsc(item.label) + '" loading="lazy">';
    }

    function arAbrirDescargaEvidencias() {
        if (!_arEvDetalle) {
            return;
        }
        const map = arMapaPorSlot(_arEvDetalle.evidencias || []);
        const items = arEvidenciasDescargables(map);
        const grid = document.getElementById('ar-descarga-grid');
        if (!items.length) {
            if (window.Swal) {
                Swal.fire('Sin archivos', 'No hay archivos de recuperacion disponibles para descargar.', 'info');
            }
            return;
        }
        if (!grid) {
            return;
        }
        grid.innerHTML = items.map(function (item) {
            return '' +
                '<label class="ar-descarga-card is-checked">' +
                    '<input type="checkbox" class="form-check-input ar-descarga-check" data-ar-descarga-slot="' + arEsc(item.slot) + '" checked>' +
                    '<div class="ar-descarga-prev">' + arRenderPreviewDescarga(item) + '</div>' +
                    '<div class="ar-descarga-title">' + arEsc(item.label) + '</div>' +
                '</label>';
        }).join('');
        arActualizarContadorDescarga();
        if (window.bootstrap) {
            _arReabrirRecuperacionTrasDescarga = true;
            _arDescargaConfirmada = false;
            const modalRecuperacion = document.getElementById('modalArRecuperacionEvidencias');
            const modalDescarga = document.getElementById('modalArDescargarEvidencias');
            const abrirDescarga = function () {
                if (modalDescarga) {
                    (new bootstrap.Modal(modalDescarga)).show();
                }
            };
            if (modalRecuperacion) {
                const instanciaRecuperacion = bootstrap.Modal.getInstance(modalRecuperacion);
                if (instanciaRecuperacion) {
                    modalRecuperacion.addEventListener('hidden.bs.modal', abrirDescarga, { once: true });
                    instanciaRecuperacion.hide();
                    return;
                }
            }
            abrirDescarga();
        }
    }

    function arDescargarEvidenciasSeleccionadas() {
        if (!_arEvDetalle || !_arEvDetalle.id) {
            return;
        }
        const grid = document.getElementById('ar-descarga-grid');
        const btn = document.getElementById('ar-descarga-btn-confirmar');
        if (!grid) {
            return;
        }
        const slots = Array.prototype.slice.call(grid.querySelectorAll('[data-ar-descarga-slot]:checked'))
            .map(function (chk) { return chk.getAttribute('data-ar-descarga-slot'); })
            .filter(Boolean);
        if (!slots.length) {
            if (window.Swal) {
                Swal.fire('Selecciona archivos', 'Marca al menos un archivo de recuperacion para descargar.', 'warning');
            }
            return;
        }
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Preparando ZIP';
        }
        fetch('/MotosAdjudicadas/descargarEvidenciasSeleccionadas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/zip, application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ id_operacion: _arEvDetalle.id, slots: slots }),
        })
            .then(function (res) {
                const ct = res.headers.get('content-type') || '';
                if (!res.ok || ct.indexOf('application/json') !== -1) {
                    return res.json().catch(function () { return {}; }).then(function (data) {
                        throw new Error(data.message || 'No se pudo preparar la descarga.');
                    });
                }
                return res.blob();
            })
            .then(function (blob) {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'recuperacion_' + (_arEvDetalle.id_credito || _arEvDetalle.id || 'operacion') + '.zip';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                _arDescargaConfirmada = true;
                const modalEl = document.getElementById('modalArDescargarEvidencias');
                if (modalEl && window.bootstrap && bootstrap.Modal.getInstance(modalEl)) {
                    bootstrap.Modal.getInstance(modalEl).hide();
                }
            })
            .catch(function (err) {
                if (window.Swal) {
                    Swal.fire('No se pudo descargar', err.message || 'Intenta de nuevo.', 'error');
                } else {
                    alert(err.message || 'No se pudo descargar.');
                }
            })
            .finally(function () {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-file-zipper me-1"></i>Descargar seleccionadas';
                }
            });
    }

    function arEsPdfPorUrl(u) {
        return /\.pdf(\?|#|$)/i.test(String(u || ''));
    }

    function arLimpiarVistaOverlay(ocultar) {
        arZoomTeardown();
        const modal = document.getElementById('modalArRecuperacionEvidencias');
        if (modal && ocultar) modal.classList.remove('ar-ev-ar-vista-abierta');
        const ovl = document.getElementById('ar-ev-vista-overlay');
        const box = document.getElementById('ar-ev-vista-mediabox');
        const pan = ovl ? ovl.querySelector('.ar-ev-vista-panel') : null;
        if (pan) {
            pan.classList.remove(
                'ar-ev-vista-panel--slot',
                'ar-ev-vista-panel--repuve',
                'ar-ev-vista-panel--factura',
                'ar-ev-vista-panel--wide'
            );
        }
        if (box) {
            box.classList.remove('ar-ev-vista-mediabox--pdf', 'ar-ev-vista-mediabox--zoomable');
            box.innerHTML = '';
        }
        if (ocultar) {
            if (ovl) ovl.classList.add('d-none');
            _arVistaCtx = { slot: '', label: '', galeria: [], indice: -1 };
            arActualizarNavVista();
        }
    }

    function arCerrarVistaOverlay() {
        arLimpiarVistaOverlay(true);
    }

    function arAbrirVistaSlotMedia(url, label, isVideo, slot, estado) {
        arLimpiarVistaOverlay(false);
        const modal = document.getElementById('modalArRecuperacionEvidencias');
        const ovl = document.getElementById('ar-ev-vista-overlay');
        const tEl = document.getElementById('ar-ev-vista-titulo');
        const box = document.getElementById('ar-ev-vista-mediabox');
        const pan = ovl ? ovl.querySelector('.ar-ev-vista-panel') : null;
        if (!ovl || !box || !pan) return;
        const galeria = arListaGaleriaFisica();
        const indice = slot ? galeria.findIndex(function (item) { return item.slot === slot; }) : -1;
        _arVistaCtx = {
            slot: slot || '',
            label: label || '',
            galeria: galeria,
            indice: indice >= 0 ? indice : 0
        };
        const title = (label || 'Archivo de recuperacion') + (isVideo ? ' — video' : '');
        if (tEl) tEl.textContent = title;
        if (tEl) {
            const st = estado || (slot && _arEvDetalle ? arEstadoSlot(arMapaPorSlot(_arEvDetalle.evidencias || [])[slot]) : '');
            const badge = st === 'acep'
                ? ' <span class="ar-ev-vista-status ar-ev-vista-status--ok">ACEPTADA</span>'
                : (st === 'rec' ? ' <span class="ar-ev-vista-status ar-ev-vista-status--bad">RECHAZADA</span>' : '');
            tEl.innerHTML = arEsc(tEl.textContent || label || 'Archivo de recuperacion') + badge;
        }
        pan.classList.add('ar-ev-vista-panel--slot');
        box.classList.add('ar-ev-vista-mediabox--zoomable');
        const urlE = arEsc(url);
        const lblE = arEsc(label || 'Archivo de recuperacion');
        if (isVideo) {
            box.innerHTML = arZoomHtmlMedia(
                '<video controls playsinline preload="metadata" class="ar-zoom-media" src="' + urlE + '"></video>'
            );
        } else {
            box.innerHTML = arZoomHtmlMedia(
                '<img class="ar-zoom-media" draggable="false" src="' + urlE + '" alt="' + lblE + '">'
            );
        }
        arZoomWire(box);
        ovl.classList.remove('d-none');
        arActualizarNavVista();
        if (modal) modal.classList.add('ar-ev-ar-vista-abierta');
    }

    function arAbrirVistaDocumento(url, docKind) {
        arCerrarVistaOverlay();
        const modal = document.getElementById('modalArRecuperacionEvidencias');
        const ovl = document.getElementById('ar-ev-vista-overlay');
        const tEl = document.getElementById('ar-ev-vista-titulo');
        const box = document.getElementById('ar-ev-vista-mediabox');
        const pan = ovl ? ovl.querySelector('.ar-ev-vista-panel') : null;
        if (!ovl || !box || !pan || !url) return;
        _arVistaCtx = {
            slot: docKind === 'repuve' ? 'doc_repuve' : 'doc_factura',
            label: docKind === 'repuve' ? 'Repuve' : 'Factura',
            galeria: [],
            indice: -1
        };
        const isPdf = arEsPdfPorUrl(url);
        if (docKind === 'repuve') {
            if (tEl) tEl.textContent = 'REPUVE — PDF';
            pan.classList.add('ar-ev-vista-panel--repuve', 'ar-ev-vista-panel--wide');
            box.classList.add('ar-ev-vista-mediabox--pdf');
            box.innerHTML =
                '<iframe src="' + arEsc(url) + '" title="REPUVE (PDF)"></iframe>';
        } else {
            if (tEl) tEl.textContent = isPdf ? 'Factura — PDF' : 'Factura';
            pan.classList.add('ar-ev-vista-panel--factura');
            if (isPdf) {
                pan.classList.add('ar-ev-vista-panel--wide');
                box.classList.add('ar-ev-vista-mediabox--pdf');
                box.innerHTML =
                    '<iframe src="' + arEsc(url) + '" title="Factura (PDF)"></iframe>';
            } else {
                box.classList.add('ar-ev-vista-mediabox--zoomable');
                box.innerHTML = arZoomHtmlMedia(
                    '<img class="ar-zoom-media" draggable="false" src="' + arEsc(url) + '" alt="Factura">'
                );
                arZoomWire(box);
            }
        }
        ovl.classList.remove('d-none');
        arActualizarNavVista();
        if (modal) modal.classList.add('ar-ev-ar-vista-abierta');
    }

    function arBindSlotClicks(root) {
        if (!root || !root.querySelectorAll) {
            return;
        }
        root.querySelectorAll('.ar-ev-slot--click[data-ar-ev-url]').forEach(function (el) {
            el.addEventListener('click', function () {
                const u = el.getAttribute('data-ar-ev-url');
                const lbl = el.getAttribute('data-ar-ev-lbl') || '';
                const isVid = el.getAttribute('data-ar-ev-video') === '1';
                const slot = el.getAttribute('data-ar-ev-slot') || '';
                if (u) {
                    const row = slot && _arEvDetalle ? arMapaPorSlot(_arEvDetalle.evidencias || [])[slot] : null;
                    arAbrirVistaSlotMedia(u, lbl, isVid, slot, arEstadoSlot(row));
                }
            });
        });
    }

    function arDatoVisible(v) {
        if (v === null || v === undefined) return '';
        const s = String(v).trim();
        return s === '' || s === 'null' || s === 'undefined' ? '' : s;
    }

    function arInfoIcono(label) {
        const k = String(label || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
        if (k.indexOf('folio') !== -1) return 'fa-hashtag';
        if (k.indexOf('credito') !== -1 || k.indexOf('credito') !== -1) return 'fa-credit-card';
        if (k.indexOf('cliente') !== -1 || k.indexOf('responsable') !== -1) return 'fa-user';
        if (k.indexOf('fecha') !== -1 || k.indexOf('capturado') !== -1 || k.indexOf('tiempo') !== -1 || k.indexOf('ano') !== -1) return 'fa-calendar-days';
        if (k.indexOf('marca') !== -1) return 'fa-tag';
        if (k.indexOf('modelo') !== -1 || k.indexOf('moto') !== -1) return 'fa-motorcycle';
        if (k.indexOf('color') !== -1) return 'fa-palette';
        if (k.indexOf('vin') !== -1 || k.indexOf('serie') !== -1) return 'fa-barcode';
        if (k.indexOf('motor') !== -1) return 'fa-gears';
        if (k.indexOf('placa') !== -1) return 'fa-id-card';
        if (k.indexOf('kilometraje') !== -1) return 'fa-gauge-high';
        if (k.indexOf('llave') !== -1) return 'fa-key';
        if (k.indexOf('tarjeta') !== -1) return 'fa-address-card';
        if (k.indexOf('lugar') !== -1 || k.indexOf('resguardo') !== -1) return 'fa-warehouse';
        if (k.indexOf('latitud') !== -1 || k.indexOf('longitud') !== -1) return 'fa-location-dot';
        if (k.indexOf('ciudad') !== -1 || k.indexOf('estado') !== -1) return 'fa-location-dot';
        if (k.indexOf('telefono') !== -1 || k.indexOf('tel') !== -1) return 'fa-phone';
        if (k.indexOf('direccion') !== -1 || k.indexOf('task') !== -1) return 'fa-route';
        return 'fa-circle-info';
    }

    function arInfoColorCss(valor) {
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

    function arInfoTelefono(valor) {
        const limpio = String(valor || '').replace(/\D/g, '');
        if (limpio.length === 10) {
            return limpio.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        }
        if (limpio.length === 12 && limpio.indexOf('52') === 0) {
            return '+52 ' + limpio.slice(2).replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        }
        return arDatoVisible(valor);
    }

    function arInfoSiNo(valor) {
        const raw = arDatoVisible(valor);
        const s = raw.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
        if (s === 'si' || s === '1' || s === 'true') return 'Si';
        if (s === 'no' || s === '0' || s === 'false') return 'No';
        return raw;
    }

    function arInfoResguardoTexto(moto) {
        const base = arDatoVisible(moto.log_lugar_resguardo);
        const otro = arDatoVisible(moto.log_lugar_otro);
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

    function arInfoItem(label, value, extraClass, mostrarVacio) {
        const valorReal = arDatoVisible(value);
        const v = valorReal || (mostrarVacio ? 'No capturado' : '');
        if (!v) return '';
        const isSerie = String(label || '').toLowerCase() === 'serie';
        const cls = (extraClass ? ' ' + extraClass : '') + (isSerie ? ' ae-form-field-series' : '');
        const esColor = String(label || '').toLowerCase() === 'color';
        const colorDot = esColor && valorReal
            ? '<span class="ae-form-color-dot" style="background-color:' + arEsc(arInfoColorCss(v)) + ';"></span>'
            : '';
        return '<div class="ae-form-field' + cls + '">' +
            '<span class="ae-form-field-head">' +
            '<i class="fa-solid ' + arEsc(arInfoIcono(label)) + ' ae-form-field-icon"></i>' +
            '<span class="ae-form-field-label">' + arEsc(label) + '</span>' +
            '</span>' +
            '<span class="ae-form-field-value">' + colorDot + '<span>' + arEsc(v) + '</span></span>' +
            '</div>';
    }

    function arInfoCard(titulo, items, modoLista, pill, extraClass) {
        const html = items.filter(Boolean).join('');
        if (!html) return '';
        return '<div class="ar-ev-info-card' + (extraClass ? ' ' + extraClass : '') + '">' +
            '<div class="ar-ev-info-card-head">' +
            '<h6 class="ar-ev-info-title"><i class="fa-solid fa-list-check me-1"></i>' + arEsc(titulo) + '</h6>' +
            (pill ? '<span class="ar-ev-info-pill">' + arEsc(pill) + '</span>' : '') +
            '</div>' +
            '<div class="' + (modoLista ? 'ar-ev-info-list' : 'ar-ev-info-grid') + '">' + html + '</div>' +
            '</div>';
    }

    function arRenderInfoOperacion(det) {
        const moto = Object.assign({}, det && det.datos_moto && typeof det.datos_moto === 'object' ? det.datos_moto : {}, det || {});
        const pick = function () {
            for (let i = 0; i < arguments.length; i++) {
                if (arDatoVisible(arguments[i])) return arguments[i];
            }
            return '';
        };
        const ubicacion = [
            arInfoResguardoTexto(moto),
            arDatoVisible(moto.log_ciudad),
            arDatoVisible(moto.log_estado)
        ].filter(Boolean).join(' / ');

        const datosMoto = [
            arInfoItem('Marca', moto.moto_marca || moto.marca, '', true),
            arInfoItem('Serie', moto.moto_no_serie || moto.serie, '', true),
            arInfoItem('Modelo', moto.moto_modelo || moto.modelo, '', true),
            arInfoItem('A\u00f1o', moto.moto_anio || moto.anio || moto.ano, '', true),
            arInfoItem('Color', moto.moto_color || moto.color, '', true),
            arInfoItem('No. motor', moto.moto_no_motor || moto.num_motor || moto.no_motor, '', true),
            arInfoItem('Placas', moto.moto_placas || moto.placas, '', true),
            arInfoItem('Kilometraje', moto.kilometraje, '', true),
            arInfoItem('Llave fisica', arInfoSiNo(pick(moto.llave_fisica, moto.tiene_llave_fisica)), '', true),
            arInfoItem('Placa fisica', arInfoSiNo(pick(moto.placa_fisica, moto.la_moto_tiene_placa_fisica)), '', true),
            arInfoItem('Tarjeta circulacion', arInfoSiNo(pick(moto.tarjeta_circulacion, moto.tiene_tarjeta_de_circulacion_en_fisico)), 'ae-form-field-wide', true)
        ].filter(Boolean).join('');

        const datosResguardo = [
            arInfoItem('Lugar de resguardo', ubicacion, 'ar-ev-info-block--wide', true),
            arInfoItem('Responsable', moto.responsable_entrega || moto.log_responsable || moto.nombre_responsable, '', true),
            arInfoItem('Telefono', arInfoTelefono(moto.log_telefono || moto.telefono_contacto || moto.telefono), '', true),
            arInfoItem('Direccion resguardo', moto.log_direccion || moto.direccion, 'ar-ev-info-block--wide', true)
        ].filter(Boolean).join('');

        if (!datosMoto && !datosResguardo) return '';

        return '<div class="ar-form-wrap">' +
            '<div class="ae-form-trace">' +
            '<div class="ae-form-trace-head">' +
            '<span class="ae-form-trace-title"><i class="fa-solid fa-list-check me-1"></i>Formulario capturado</span>' +
            (det.datos_moto_fecha ? '<span class="ae-form-trace-date"><i class="fa-solid fa-calendar-check"></i>Capturado ' + arEsc(arDatoVisible(det.datos_moto_fecha)) + '</span>' : '') +
            '</div>' +
            '<div class="ae-form-trace-columns">' +
            '<div class="ae-form-trace-panel"><div class="ae-form-trace-grid">' + datosMoto + '</div></div>' +
            (datosResguardo ? '<div class="ae-form-trace-panel"><div class="ae-form-resguardo-list">' + datosResguardo + '</div></div>' : '') +
            '</div>' +
            '</div>' +
            '</div>';
    }

    function arRenderBitacoraOperacion(det) {
        const rows = Array.isArray(det && det.bitacora) ? det.bitacora : [];
        if (!rows.length) {
            return '<div class="ar-ev-bit-empty">Sin movimientos registrados.</div>';
        }
        return '<ul class="ar-ev-bit-list">' + rows.map(function (b) {
            const accion = arEsc(b.accion || 'Movimiento');
            const usuario = arEsc(b.nombre_usuario || 'Sistema');
            const fecha = arEsc(b.fecha_alta || '');
            return '<li class="ar-ev-bit-item">' +
                '<div class="ar-ev-bit-action">' + accion + '</div>' +
                '<div class="ar-ev-bit-meta"><i class="fa-solid fa-user me-1"></i>' + usuario +
                (fecha ? '<br><i class="fa-regular fa-clock me-1"></i>' + fecha : '') +
                '</div>' +
            '</li>';
        }).join('') + '</ul>';
    }

    function arEvRenderDetalle(det) {
        const inner = document.getElementById('ar-ev-modal-inner');
        if (!inner || !det) {
            return;
        }

        const map = arMapaPorSlot(det.evidencias || []);
        const validadas = arCuentaValidadasImg(map);
        const docsCargados = arCuentaDocumentosCargados(map);
        const totalExpedienteValidadas = validadas + docsCargados;
        const pctExpediente = AR_TOTAL_VALIDABLE_EXPEDIENTE ? Math.round((totalExpedienteValidadas / AR_TOTAL_VALIDABLE_EXPEDIENTE) * 100) : 0;

        const titulo = document.getElementById('ar-ev-titulo-cliente');
        const estatusTitulo = document.getElementById('ar-ev-estatus-cliente');
        const progIn = document.getElementById('ar-ev-prog-inline');
        if (titulo) {
            titulo.textContent = (det.nombre_cliente && String(det.nombre_cliente).trim())
                ? String(det.nombre_cliente).trim()
                : ('Operación #' + (det.id || ''));
        }
        if (titulo && det.id_credito && titulo.textContent.indexOf('(') === -1) {
            titulo.textContent += ' (' + det.id_credito + ')';
        }
        if (estatusTitulo) {
            estatusTitulo.innerHTML = '';
            estatusTitulo.classList.add('d-none');
        }
        if (progIn) {
            progIn.textContent = totalExpedienteValidadas + ' / ' + AR_TOTAL_VALIDABLE_EXPEDIENTE;
        }

        inner.innerHTML =
            arRenderInfoOperacion(det) +
            '<div class="row g-3">' +
            '<div class="col-lg-8">' +
            arRenderSeccion(AR_SEC_FIS, map) +
            '<div class="row g-3 ar-ev-moment-row mt-1">' +
            '<div class="col-lg-6">' +
            '<div class="ar-ev-moment-doc">' +
            '<div class="ar-ev-hdr ar-ev-hdr-green ar-ev-hdr-left mb-0"><i class="fa-solid fa-file-pdf"></i> Momento 2: Repuve</div>' +
            '<div class="ar-ev-moment-doc-body">' + arRenderDocRepuve(map) + '</div>' +
            '</div></div>' +
            '<div class="col-lg-6">' +
            '<div class="ar-ev-moment-doc">' +
            '<div class="ar-ev-hdr ar-ev-hdr-purple ar-ev-hdr-left mb-0"><i class="fa-solid fa-file-invoice"></i> Momento 3: Factura</div>' +
            '<div class="ar-ev-moment-doc-body">' + arRenderDocFactura(map) + '</div>' +
            '</div></div>' +
            '</div>' +
            '<div class="row mt-2 g-2"><div class="col-12">' + arRenderFacturaCarteraComentarios(map) + '</div></div>' +
            '</div>' +
            '<div class="col-lg-4">' +
            '<div class="ar-ev-notas-panel">' +
            '<div class="ar-ev-notas-title"><i class="fa-solid fa-clock-rotate-left me-1"></i>Bitácora</div>' +
            '<div id="ar-ev-notas-body">' + arRenderBitacoraOperacion(det) + '</div>' +
            '</div></div></div>';

        arBindSlotClicks(inner);

        const zr = inner.querySelector('#ar-ev-zone-repuve');
        if (zr && !_arEvSoloLectura) {
            zr.addEventListener('click', function () {
                const inp = document.getElementById('ar-ev-inp-repuve');
                if (inp) {
                    inp.value = '';
                    inp.click();
                }
            });
        }
        const zf = inner.querySelector('#ar-ev-zone-factura');
        if (zf && !_arEvSoloLectura) {
            zf.addEventListener('click', function () {
                const inp = document.getElementById('ar-ev-inp-factura');
                if (inp) {
                    inp.value = '';
                    inp.click();
                }
            });
        }
        inner.querySelectorAll('[data-ar-ev-doc-open]').forEach(function (z) {
            z.addEventListener('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                const u = z.getAttribute('data-ar-ev-doc-open');
                const kind = z.getAttribute('data-ar-ev-doc-kind') || 'factura';
                if (u) {
                    arAbrirVistaDocumento(u, kind);
                }
            });
        });

        arEvToggleFooterEnviarCartera(map);
        arEvToggleFooterDescarga(map);
    }

    function arEvEnviarACartera() {
        if (_arEvSoloLectura) {
            return;
        }
        if (!_arEvDetalle || !_arEvDetalle.id) {
            return;
        }
        const ta = document.getElementById('ar-ev-comentarios-cartera');
        const com = ta ? String(ta.value || '').trim() : '';
        const btn = document.getElementById('ar-ev-btn-enviar-cartera');
        if (btn) {
            btn.disabled = true;
        }
        fetch('/MotosAdjudicadas/enviarRecuperacionACartera', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ id_operacion: _arEvDetalle.id, comentarios: com }),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo enviar.');
                }
                const mEl = document.getElementById('modalArRecuperacionEvidencias');
                if (mEl && window.bootstrap && bootstrap.Modal.getInstance(mEl)) {
                    bootstrap.Modal.getInstance(mEl).hide();
                }
                arCargarConteosPestanas();
                arCargarSeccion('bandeja', true);
                arCargarSeccion('dictaminado', true);
                if (typeof spartaSwalEnviadoOk === 'function') {
                    spartaSwalEnviadoOk('Recuperación enviada correctamente.');
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enviado',
                        text: 'Recuperación enviada correctamente.',
                        confirmButtonColor: '#0f172a',
                    });
                }
            })
            .catch(function (err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: err.message || 'Error' });
                }
                if (btn) {
                    btn.disabled = false;
                }
            });
    }

    function arEvRecargarDetalle(idOp, cb) {
        fetch('/MotosAdjudicadas/obtenerDetalle/' + parseInt(idOp, 10) + '?incluir_todas=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.detalle) {
                    throw new Error((data && data.message) || 'No se pudo recargar');
                }
                _arEvDetalle = data.detalle;
                arEvRenderDetalle(_arEvDetalle);
                if (typeof cb === 'function') {
                    cb();
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'No se pudo actualizar el detalle.' });
                }
            });
    }

    window.arEvModalAbrir = function (idOperacion, soloLectura, contexto) {
        const id = parseInt(idOperacion, 10);
        if (!id) {
            return;
        }
        _arEvDetalle = null;
        _arEvSoloLectura = !!soloLectura;
        arSetContextoModal(contexto || (_arEvSoloLectura ? 'dictaminado' : 'bandeja'));
        arCerrarVistaOverlay();

        const inner = document.getElementById('ar-ev-modal-inner');
        const titulo = document.getElementById('ar-ev-titulo-cliente');
        const estatusTitulo = document.getElementById('ar-ev-estatus-cliente');
        const progIn = document.getElementById('ar-ev-prog-inline');
        if (titulo) {
            titulo.textContent = '…';
        }
        if (estatusTitulo) {
            estatusTitulo.innerHTML = '';
            estatusTitulo.classList.add('d-none');
        }
        if (progIn) {
            progIn.textContent = '… / ' + AR_TOTAL_VALIDABLE_IMG;
        }
        if (inner) {
            inner.innerHTML = '<div class="text-center py-5 text-muted">' +
                '<div class="spinner-border spinner-border-sm" style="color:#14b8a6;"></div>' +
                '<div class="small mt-2">Cargando recuperacion…</div></div>';
        }
        const btnFoot = document.getElementById('ar-ev-btn-enviar-cartera');
        if (btnFoot) {
            btnFoot.style.display = 'none';
            btnFoot.disabled = !!soloLectura;
        }
        const btnDesc = document.getElementById('ar-ev-btn-descargar-evidencias');
        if (btnDesc) {
            btnDesc.style.display = 'none';
            btnDesc.disabled = false;
        }

        const mEl = document.getElementById('modalArRecuperacionEvidencias');
        if (mEl && window.bootstrap) {
            (new bootstrap.Modal(mEl)).show();
        }

        fetch('/MotosAdjudicadas/obtenerDetalle/' + id + '?incluir_todas=1', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.detalle) {
                    throw new Error(data.message || 'No se pudo cargar el detalle');
                }
                _arEvDetalle = data.detalle;
                arEvRenderDetalle(_arEvDetalle);
            })
            .catch(function (err) {
                if (inner) {
                    inner.innerHTML = '<div class="alert alert-danger mb-0">' + arEsc(err.message || 'Error') + '</div>';
                }
            });
    };

    function arEvSubirArchivo(slot, input) {
        if (_arEvSoloLectura) {
            return;
        }
        const f = input.files && input.files[0];
        if (input) {
            input.value = '';
        }
        if (!f || !_arEvDetalle || !_arEvDetalle.id) {
            return;
        }
        const fd = new FormData();
        fd.append('id_operacion', String(_arEvDetalle.id));
        fd.append('slot', slot);
        fd.append('archivo', f, f.name);
        fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', text: data.message || 'No se pudo subir el archivo.' });
                    }
                    return;
                }
                arEvRecargarDetalle(_arEvDetalle.id);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Listo', text: 'Archivo guardado.', timer: 1600, showConfirmButton: false });
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'Error de red al subir.' });
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const inpR = document.getElementById('ar-ev-inp-repuve');
        const inpF = document.getElementById('ar-ev-inp-factura');
        if (inpR) {
            inpR.addEventListener('change', function () {
                arEvSubirArchivo('doc_repuve', inpR);
            });
        }
        if (inpF) {
            inpF.addEventListener('change', function () {
                arEvSubirArchivo('doc_factura', inpF);
            });
        }
    });

    function arSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${arEsc(msg)}</span>
        </div>`;
    }

    function arRenderCardBandeja(item) {
        const ev = parseInt(item.evidencias_count, 10) || 0;
        const g  = item.gestor_nombre
            ? arEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? arEsc(item.fecha_asignacion)
            : '<span class="ae-list-muted">—</span>';
        const nombreCliente = item.nombre_cliente
            ? arEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const est = item.estatus ? arEsc(item.estatus) : '<span class="ae-list-muted">—</span>';
        const dias = item.dias_en_pipeline != null && item.dias_en_pipeline !== ''
            ? arEsc(String(item.dias_en_pipeline))
            : '<span class="ae-list-muted">—</span>';
        const folio = item.folio ? arEsc(item.folio) : '—';
        const idOp = parseInt(item.id, 10) || 0;

        return `
        <div class="ac-card">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${arEsc(String(item.id_credito))}</span>
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
                    <div class="ae-list-cell ae-list-ev">
                        <span class="ac-lbl">Archivos cargados</span>
                        <span class="ac-val">${ev} / ${AR_EV_TOTAL_LISTA}</span>
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
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="arEvModalAbrir(${idOp})" ${idOp ? '' : 'disabled'}
                            title="Ver evidencias" aria-label="Ver evidencias">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }

    function arRenderCardDictaminado(item) {
        const estPipeline = item.estatus ? arEsc(item.estatus) : '<span class="ae-list-muted">—</span>';
        const dictTxt = item.dictamen
            ? arEsc(item.dictamen)
            : '<span class="ae-list-muted">—</span>';
        const fechaD = item.fecha_dictamen
            ? arEsc(item.fecha_dictamen)
            : '<span class="ae-list-muted">—</span>';
        const g = item.gestor_nombre
            ? arEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">Sin asignar</span>';
        const nombreCliente = item.nombre_cliente
            ? arEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? arEsc(item.folio) : '—';

        return `
        <div class="ac-card ar-card-dict">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${arEsc(String(item.id_credito))}</span>
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
                    <div class="ae-list-cell ae-list-ev">
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
                        <span class="ac-val" style="white-space:pre-line;">${arEsc(item.comentarios)}</span>
                    </div>` : ''}
                </div>
            </div>
        </div>`;
    }

    function arRenderFilaTabla(item, key) {
        const esBandeja = key === 'bandeja';
        const nombreCliente = item.nombre_cliente
            ? arEsc(item.nombre_cliente)
            : '<span class="ar-table-muted">Sin nombre</span>';
        const folio = item.folio ? arEsc(item.folio) : '-';
        const credito = item.id_credito ? arEsc(String(item.id_credito)) : '-';
        const idOp = parseInt(item.id, 10) || 0;
        const fechaMaxikash = item.fecha_dictamen_legacy
            ? arEsc(item.fecha_dictamen_legacy)
            : (item.fecha_gestion_legacy ? arEsc(item.fecha_gestion_legacy) : '<span class="ar-table-muted">-</span>');
        const fechaEntrada = item.fecha_entrada_recuperacion
            ? arEsc(item.fecha_entrada_recuperacion)
            : (item.fecha_inicio_recuperacion
                ? arEsc(item.fecha_inicio_recuperacion)
                : (item.fecha_aprobacion_evidencias ? arEsc(item.fecha_aprobacion_evidencias) : '<span class="ar-table-muted">-</span>'));
        const accion = `
            <button type="button" class="btn btn-sm btn-outline-secondary" data-ar-no-row="1"
                    onclick="event.stopPropagation(); arEvModalAbrir(${idOp}, ${esBandeja ? 'false' : 'true'}, '${key}')" ${idOp ? '' : 'disabled'}
                    title="Ver recuperacion" aria-label="Ver recuperacion">
                <i class="fa fa-eye"></i>
            </button>`;

        return `
        <tr>
            <td class="ar-table-main">
                <span class="ar-table-folio">${folio}</span>
                <span class="ar-table-credit"># ${credito}</span>
                <span class="ar-table-main-client"><i class="fa-solid fa-user"></i>${nombreCliente}</span>
            </td>
            <td class="ar-table-seguimiento">
                <span class="ar-table-seguimiento-row">
                    <span class="ar-table-seguimiento-label"><i class="fa-solid fa-house-circle-check"></i>FECHA DE ADJUDICACION</span>
                    <span class="ar-table-seguimiento-date">${fechaMaxikash}</span>
                </span>
                <span class="ar-table-seguimiento-row">
                    <span class="ar-table-seguimiento-label"><i class="fa-solid fa-inbox"></i>VALIDADO EN COBRANZA</span>
                    <span class="ar-table-seguimiento-date">${fechaEntrada}</span>
                </span>
            </td>
            <td class="ar-table-evidence">${arRenderResumenExpediente(item, key)}</td>
            <td class="ar-table-action"><div class="ar-table-actions">${accion}</div></td>
        </tr>`;
    }

    function arRenderTabla(datos, key) {
        const tableId = 'ar-tabla-' + key;
        const filas = datos.map(function (item) { return arRenderFilaTabla(item, key); }).join('');
        const headers = '<th>Operacion</th><th>Seguimiento</th><th>Expediente</th><th class="ar-table-action">Acciones</th>';

        return `
        <div class="card-datatable ar-table-wrap">
            <table id="${arEsc(tableId)}" class="dt-responsive table border-top ar-table">
                <thead><tr>${headers}</tr></thead>
                <tbody>${filas}</tbody>
            </table>
        </div>`;
    }

    function arInicializarDataTable(key) {
        const tableId = '#ar-tabla-' + key;
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
                emptyTable: (AR_CONFIG[key] && AR_CONFIG[key].vacio) ? AR_CONFIG[key].vacio : "No hay operaciones registradas",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                thousands: ",",
                lengthMenu: "Mostrar _MENU_ registros",
                loadingRecords: "Cargando...",
                processing: "Procesando...",
                search: "Buscar:",
                zeroRecords: "No se encontraron resultados",
                paginate: { first: "&laquo;", last: "&raquo;", next: "&rsaquo;", previous: "&lsaquo;" }
            },
            dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row align-items-center mt-3 ar-dt-footer"<"col-sm-12 col-md-5 ar-dt-info"i><"col-sm-12 col-md-7 ar-dt-pages"p>>',
            drawCallback: function() {
                jQuery(tableId + '_paginate > .pagination').addClass('pagination-sm justify-content-end');
                jQuery(tableId + '_length select').addClass('form-select form-select-sm');
                jQuery(tableId + '_filter input').addClass('form-control form-control-sm');
            }
        });
    }

    function arSetBadge(key, n) {
        const el = document.getElementById(AR_BADGE[key]);
        if (!el) return;
        if (n > 0) {
            el.textContent   = n;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    function arCargarConteosPestanas() {
        return fetch('/AtencionClientes/obtenerConteosRecuperacion', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.conteos) return;
                const c = data.conteos;
                arSetBadge('bandeja', c.bandeja);
                arSetBadge('dictaminado', c.dictaminado);
            })
            .catch(function () {});
    }

    function arCargarSeccion(key, forzar) {
        const cfg = AR_CONFIG[key];
        if (!cfg) return Promise.resolve();

        const suf      = key === 'bandeja' ? 'bandeja' : 'dictaminado';
        const loaderId = 'ar-loader-' + suf;
        const listaId  = 'ar-lista-'  + suf;

        if (!forzar && _arCargada[key]) {
            return Promise.resolve();
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return Promise.resolve();

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('ar-lista-updating');
        }

        return fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar');
                }
                const n = data.datos.length;
                arSetBadge(key, n);
                _arCargada[key] = true;

                const tableId = '#ar-tabla-' + key;
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && document.querySelector(tableId) && jQuery.fn.DataTable.isDataTable(tableId)) {
                    jQuery(tableId).DataTable().destroy();
                }
                if (n === 0) {
                    lista.innerHTML = arRenderTabla([], key);
                    arInicializarDataTable(key);
                } else {
                    lista.innerHTML = arRenderTabla(data.datos, key);
                    arInicializarDataTable(key);
                }
            })
            .catch(function (err) {
                lista.innerHTML = `<div class="alert alert-danger">${arEsc(err.message || 'Error')}</div>`;
                arSetBadge(key, 0);
            })
            .finally(function () {
                loader.style.display = 'none';
                lista.classList.remove('ar-lista-updating');
            });
    }

    function arCargarVistaInicialConSpinner() {
        const hasSwal = typeof Swal !== 'undefined';
        if (hasSwal) {
            Swal.fire({
                title: 'Cargando Recuperación…',
                html: '<span style="font-size:.875rem;color:#64748b;">Obteniendo bandeja de entrada</span>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); },
            });
        }
        Promise.allSettled([
            arCargarSeccion('bandeja', true),
            arCargarConteosPestanas()
        ]).finally(function () {
            if (hasSwal) Swal.close();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        arCargarVistaInicialConSpinner();

        const bb = document.getElementById('ar-tab-bandeja-btn');
        const bd = document.getElementById('ar-tab-dictaminado-btn');
        if (bb) {
            bb.addEventListener('shown.bs.tab', function () {
                arCargarSeccion('bandeja', false);
            });
        }
        if (bd) {
            bd.addEventListener('shown.bs.tab', function () {
                arCargarSeccion('dictaminado', false);
            });
        }

        const btnEnviarCartera = document.getElementById('ar-ev-btn-enviar-cartera');
        if (btnEnviarCartera) {
            btnEnviarCartera.addEventListener('click', function () {
                arEvEnviarACartera();
            });
        }
        document.addEventListener('click', function (ev) {
            const btnDescargar = ev.target.closest ? ev.target.closest('#ar-ev-btn-descargar-evidencias') : null;
            if (btnDescargar) {
                ev.preventDefault();
                arAbrirDescargaEvidencias();
            }
        });
        const allDesc = document.getElementById('ar-descarga-check-all');
        if (allDesc) {
            allDesc.addEventListener('change', function () {
                const grid = document.getElementById('ar-descarga-grid');
                if (!grid) return;
                grid.querySelectorAll('[data-ar-descarga-slot]').forEach(function (chk) {
                    chk.checked = allDesc.checked;
                });
                arActualizarContadorDescarga();
            });
        }
        const gridDesc = document.getElementById('ar-descarga-grid');
        if (gridDesc) {
            gridDesc.addEventListener('change', function (ev) {
                if (ev.target && ev.target.matches('[data-ar-descarga-slot]')) {
                    arActualizarContadorDescarga();
                }
            });
        }
        const btnConfirmarDesc = document.getElementById('ar-descarga-btn-confirmar');
        if (btnConfirmarDesc) {
            btnConfirmarDesc.addEventListener('click', function () {
                arDescargarEvidenciasSeleccionadas();
            });
        }

        const mArEv = document.getElementById('modalArRecuperacionEvidencias');
        if (mArEv) {
            mArEv.addEventListener('hidden.bs.modal', function () {
                arCerrarVistaOverlay();
            });
        }
        const mArDescarga = document.getElementById('modalArDescargarEvidencias');
        if (mArDescarga) {
            mArDescarga.addEventListener('hidden.bs.modal', function () {
                if (!_arReabrirRecuperacionTrasDescarga || _arDescargaConfirmada || !_arEvDetalle || !window.bootstrap) {
                    _arReabrirRecuperacionTrasDescarga = false;
                    _arDescargaConfirmada = false;
                    return;
                }
                _arReabrirRecuperacionTrasDescarga = false;
                setTimeout(function () {
                    const modalRecuperacion = document.getElementById('modalArRecuperacionEvidencias');
                    if (modalRecuperacion) {
                        (new bootstrap.Modal(modalRecuperacion)).show();
                    }
                }, 120);
            });
        }
        const arOvl = document.getElementById('ar-ev-vista-overlay');
        if (arOvl) {
            arOvl.addEventListener('click', function (ev) {
                if (ev.target === arOvl) {
                    arCerrarVistaOverlay();
                }
            });
        }
        const arBtnVistaCerrar = document.getElementById('ar-ev-vista-btn-cerrar');
        if (arBtnVistaCerrar) {
            arBtnVistaCerrar.addEventListener('click', function () {
                arCerrarVistaOverlay();
            });
        }
        const arBtnVistaPrev = document.getElementById('ar-ev-vista-prev');
        if (arBtnVistaPrev) {
            arBtnVistaPrev.addEventListener('click', function () {
                arNavegarVista(-1);
            });
        }
        const arBtnVistaNext = document.getElementById('ar-ev-vista-next');
        if (arBtnVistaNext) {
            arBtnVistaNext.addEventListener('click', function () {
                arNavegarVista(1);
            });
        }
        document.addEventListener('keydown', function (ev) {
            const o = document.getElementById('ar-ev-vista-overlay');
            if (!o || o.classList.contains('d-none')) return;
            const tag = ev.target && ev.target.tagName ? String(ev.target.tagName).toLowerCase() : '';
            const typing = tag === 'textarea' || tag === 'input' || tag === 'select';
            if (ev.key === 'Escape') {
                arCerrarVistaOverlay();
                return;
            }
            if (!typing && ev.key === 'ArrowLeft') {
                ev.preventDefault();
                arNavegarVista(-1);
                return;
            }
            if (!typing && ev.key === 'ArrowRight') {
                ev.preventDefault();
                arNavegarVista(1);
            }
        });
    });
})();
</script>
