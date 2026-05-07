<style>
.acr-header-gradient {
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.acr-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.acr-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.9; color: #fff; }
.acr-header-gradient i  { color: #fff; }


.ac-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.ac-card:hover { box-shadow: 0 4px 18px rgba(124,58,237,.14); }
.ac-card.acr-card-dict {
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

.acr-btn-pipeline {
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
.acr-btn-pipeline:hover  { opacity: .9; transform: translateY(-1px); color: #fff; }
.acr-btn-pipeline:active { transform: translateY(0); }

/* Forzar tono de tabs al color propio de Recepción */
#acrTabNav .nav-link {
    color: #0f172a;
}
#acrTabNav .nav-link.active {
    background-color: #7c3aed !important;
    border-color: #7c3aed !important;
    color: #fff !important;
}
#acrTabNav .nav-link:hover:not(.active),
#acrTabNav .nav-link:focus:not(.active) {
    color: #7c3aed;
}

body.dark-mode .ac-card              { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-card.acr-card-dict { background: #111827; }
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
}

.acr-lista-updating {
    opacity: 0.5;
    transition: opacity 0.12s ease;
    pointer-events: none;
}

/* Vista expediente almacén (referencia UX jefe) */
#modalAcrRecepcionAlmacen .acr-rcpt-page {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    font-size: 15px;
    color: #1a1a1a;
    background: #fff;
    border-radius: 0;
    padding: 1rem;
    margin-top: 0;
}
body.dark-mode #modalAcrRecepcionAlmacen .acr-rcpt-page {
    background: #1e293b;
    color: #e2e8f0;
}
.acr-rcpt-page .acr-rcpt-header {
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
    color: #fff;
    padding: 1rem 1.25rem;
    border-radius: .75rem .75rem 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.acr-rcpt-page .acr-rcpt-header h1 {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    letter-spacing: 0.01em;
}
.acr-rcpt-page .acr-rcpt-header p {
    font-size: 12px;
    color: #aaa;
    margin: 4px 0 0 0;
    font-family: ui-monospace, monospace;
}
.acr-rcpt-badge-rec {
    background: rgba(255,255,255,.16);
    color: #fff;
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: 600;
    letter-spacing: 0.06em;
    white-space: nowrap;
}
.acr-rcpt-steps {
    display: flex;
    background: #faf5ff;
    border-left: 1px solid #e9d5ff;
    border-right: 1px solid #e9d5ff;
}
body.dark-mode .acr-rcpt-steps { background: #334155; }
.acr-rcpt-step {
    flex: 1;
    padding: 10px 6px;
    text-align: center;
    font-size: 11px;
    color: #64748b;
    border-right: 1px solid #e9d5ff;
}
.acr-rcpt-step:last-child { border-right: none; }
.acr-rcpt-step.done { color: #6d28d9; background: #f3e8ff; font-weight: 600; }
.acr-rcpt-step.active { color: #6d28d9; background: #ede9fe; font-weight: 700; }
body.dark-mode .acr-rcpt-step.done { background: #14532d; color: #bbf7d0; }
body.dark-mode .acr-rcpt-step.active { background: #78350f; color: #fde68a; }
.acr-rcpt-step-num { display: block; font-size: 15px; margin-bottom: 2px; }
.acr-rcpt-section {
    border: 1px solid #e2e8f0;
    border-top: none;
    background: #fff;
}
body.dark-mode .acr-rcpt-section { background: #0f172a; border-color: #334155; }
.acr-rcpt-section:last-child { border-radius: 0 0 10px 10px; }
.acr-rcpt-sec-head {
    padding: 12px 1rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #faf5ff;
}
body.dark-mode .acr-rcpt-sec-head { background: #1e293b; }
.acr-rcpt-sec-title { font-size: 14px; font-weight: 600; margin: 0; }
.acr-rcpt-sec-sub { font-size: 11px; color: #888; margin: 2px 0 0 0; }
.acr-rcpt-sec-body { padding: 1rem 1.25rem; }
.acr-rcpt-arrival-btn {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.acr-rcpt-arrival-btn:hover { background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 100%); color: #fff; }
.acr-rcpt-arrival-btn.arrived { background: #16a34a; cursor: default; }
.acr-rcpt-arrival-btn:disabled { opacity: 0.88; cursor: not-allowed; }
.acr-rcpt-ts {
    margin-top: 10px;
    background: #eaf3de;
    border: 1px solid #639922;
    border-radius: 6px;
    padding: 10px 14px;
    display: none;
    font-family: ui-monospace, monospace;
    font-size: 13px;
    color: #2d6a1f;
}
.acr-rcpt-ts.show { display: flex; align-items: center; gap: 8px; }
.acr-rcpt-ev-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
@media (max-width: 520px) {
    .acr-rcpt-ev-grid { grid-template-columns: repeat(2, 1fr); }
}
.acr-rcpt-ev-cell {
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .3rem;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    background: #fff;
    padding: 6px;
    text-align: center;
    transition: border-color .15s, background .15s;
}
body.dark-mode .acr-rcpt-ev-cell { background: #1e293b; border-color: #334155; }
.acr-rcpt-ev-cell:hover { border-color: #f59e0b; background: #fffbeb; }
body.dark-mode .acr-rcpt-ev-cell:hover { background: #334155; border-color: #f59e0b; }
.acr-rcpt-ev-cell.uploaded {
    border-style: solid;
    border-color: #e2e8f0;
    background: #fff;
}
.acr-rcpt-ev-cell.uploading { opacity: .65; pointer-events: none; }
.acr-rcpt-ev-label {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    margin: 0;
    background: rgba(15, 23, 42, .72);
    color: #fff;
    font-size: .58rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    line-height: 1.2;
    padding: .18rem .25rem;
    pointer-events: none;
}
.acr-rcpt-slot-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .7rem;
    border: none;
    color: #fff;
    background: #f59e0b;
    pointer-events: none;
    z-index: 2;
    box-shadow: 0 2px 5px rgba(0, 0, 0, .25);
}
.acr-rcpt-ev-cell.uploaded .acr-rcpt-slot-btn { background: #f97316; }
.acr-rcpt-slot-icon-ph {
    font-size: 1.35rem;
    color: #94a3b8;
    pointer-events: none;
}
.acr-rcpt-ev-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.acr-rcpt-ev-status {
    position: absolute;
    bottom: 22px;
    left: 50%;
    transform: translateX(-50%);
    font-size: .58rem;
    font-weight: 800;
    border-radius: 999px;
    padding: 1px 7px;
    white-space: nowrap;
    pointer-events: none;
    z-index: 2;
    display: none;
}
.acr-rcpt-ev-status-pendiente {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
.acr-rcpt-ev-status-cierre {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}
.acr-rcpt-ev-check {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 18px;
    height: 18px;
    background: #2d6a1f;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 10px;
}
.acr-rcpt-ev-cell.uploaded .acr-rcpt-ev-check { display: flex; }
.acr-rcpt-ev-cell input[type=file] { display: none; }
.acr-rcpt-ev-cell:focus-within {
    outline: 2px solid #f59e0b;
    outline-offset: 2px;
    background: #fffbeb;
}
body.dark-mode .acr-rcpt-ev-cell:focus-within { background: #334155; outline-color: #fbbf24; }
.acr-rcpt-alert-info {
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    background: #e6f1fb;
    color: #0c3a6b;
    border: 1px solid #378add;
    margin-bottom: 12px;
}
.acr-rcpt-alert-warn {
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    background: #fffbeb;
    color: #7c2d12;
    border: 1px solid #fed7aa;
    margin-bottom: 12px;
}
.acr-rcpt-edc-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 12px;
}
.acr-rcpt-edc-card {
    background: #faf5ff;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid rgba(0,0,0,.08);
}
body.dark-mode .acr-rcpt-edc-card { background: #1e293b; }
.acr-rcpt-edc-lbl { font-size: 11px; color: #888; margin-bottom: 4px; }
.acr-rcpt-edc-val { font-size: 20px; font-weight: 600; color: #1a1a1a; }
body.dark-mode .acr-rcpt-edc-val { color: #e2e8f0; }
.acr-rcpt-edc-val.hl { color: #c47a00; }
.acr-rcpt-edc-val.good { color: #2d6a1f; }
.acr-rcpt-edc-val.bad { color: #a32d2d; }
.acr-rcpt-cuotas { width: 100%; border-collapse: collapse; font-size: 13px; }
.acr-rcpt-cuotas th {
    background: #faf5ff;
    padding: 8px 10px;
    text-align: left;
    font-size: 11px;
    color: #64748b;
    border-bottom: 1px solid rgba(0,0,0,.08);
}
.acr-rcpt-cuotas td { padding: 8px 10px; border-bottom: 1px solid rgba(0,0,0,.06); }
.acr-rcpt-doc-card {
    border: 1px solid rgba(0,0,0,.1);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 10px;
}
.acr-rcpt-doc-h {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    background: #faf5ff;
    border-bottom: 1px solid rgba(0,0,0,.08);
    gap: 8px;
    flex-wrap: wrap;
}
body.dark-mode .acr-rcpt-doc-h { background: #1e293b; }
.acr-rcpt-doc-name { font-size: 14px; font-weight: 600; margin: 0; }
.acr-rcpt-confirm-btn {
    width: 100%;
    margin-top: 12px;
    padding: 12px;
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}
.acr-rcpt-confirm-btn:hover { background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 100%); color: #fff; }
.acr-rcpt-doc-thumb { max-height: 140px; max-width: 100%; border-radius: 8px; border: 1px solid rgba(0,0,0,.1); }
.acr-rcpt-success-msg {
    display: none;
    margin-top: 10px;
    padding: 12px;
    background: #eaf3de;
    border: 1px solid #639922;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
    color: #2d6a1f;
}
.acr-btn-almacen {
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
    border: 1px solid #6d28d9;
    color: #fff;
    font-weight: 700;
    font-size: .8rem;
    padding: .35rem 1rem;
    border-radius: 2rem;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(109, 40, 217, 0.25);
}
.acr-btn-almacen:hover { background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 100%); color: #fff; }
</style>

<div class="container-fluid py-4">

    <div class="acr-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-flag-checkered fa-2x"></i>
        <div>
            <h4>4.- Recepción</h4>
            <p>Gestión de recepción para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div id="acr-wrap-principal">
    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-pills flex-column flex-md-row mb-3 gap-md-0 gap-2 border-0" id="acrTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="acr-tab-bandeja-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#acrTabBandeja">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="acr-badge-bandeja" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acr-tab-dictaminado-btn" type="button" role="tab"
                            data-bs-toggle="tab" data-bs-target="#acrTabDictaminado">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Dictaminado
                        <span class="badge bg-label-secondary ms-1" id="acr-badge-dictaminado" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="acrTabContent">
            <div class="tab-pane fade show active" id="acrTabBandeja" role="tabpanel">
                <div id="acr-loader-bandeja" class="text-center py-5 text-muted" style="display:block;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="acr-lista-bandeja"></div>
            </div>

            <div class="tab-pane fade" id="acrTabDictaminado" role="tabpanel">
                <div id="acr-loader-dictaminado" class="text-center py-5 text-muted" style="display:none;">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="acr-lista-dictaminado"></div>
            </div>
        </div>
    </div>
    </div><!-- #acr-wrap-principal -->

    <div class="modal fade" id="modalAcrRecepcionAlmacen" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-warehouse me-2"></i>Recepción en almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="acr-rcpt-page">
        <div class="acr-rcpt-header">
            <div>
                <h1 class="text-white">Recepción de Moto — Almacén</h1>
                <p id="acr-rcpt-meta-line">Caso &nbsp;|&nbsp; Agente: — &nbsp;|&nbsp; <span id="acr-rcpt-hdate"></span></p>
            </div>
            <span class="acr-rcpt-badge-rec">RECEPCIÓN</span>
        </div>

        <div class="acr-rcpt-steps">
            <div class="acr-rcpt-step done"><span class="acr-rcpt-step-num"><i class="fa-solid fa-check"></i></span>Evidencias</div>
            <div class="acr-rcpt-step done"><span class="acr-rcpt-step-num"><i class="fa-solid fa-check"></i></span>Recuperación</div>
            <div class="acr-rcpt-step done"><span class="acr-rcpt-step-num"><i class="fa-solid fa-check"></i></span>Cierre documentación</div>
            <div class="acr-rcpt-step active"><span class="acr-rcpt-step-num"><i class="fa-solid fa-warehouse"></i></span>Recepción</div>
        </div>

        <div class="acr-rcpt-section">
            <div class="acr-rcpt-sec-head">
                <span class="text-warning"><i class="fa-solid fa-circle-info"></i></span>
                <div>
                    <div class="acr-rcpt-sec-title">Información del Caso</div>
                    <div class="acr-rcpt-sec-sub">Motocicleta recuperada</div>
                </div>
            </div>
            <div class="acr-rcpt-sec-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Cliente</div>
                        <div class="fw-semibold" id="acr-rcpt-cliente">—</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Contrato / Folio</div>
                        <div class="fw-semibold" id="acr-rcpt-contrato">—</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Motocicleta</div>
                        <div class="fw-semibold" id="acr-rcpt-moto">—</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Placa / VIN</div>
                        <div class="fw-semibold" id="acr-rcpt-placa">—</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="acr-rcpt-section">
            <div class="acr-rcpt-sec-head">
                <span class="text-warning"><i class="fa-regular fa-clock"></i></span>
                <div>
                    <div class="acr-rcpt-sec-title">Llegada a Almacén</div>
                    <div class="acr-rcpt-sec-sub">Registrar ingreso físico de la unidad</div>
                </div>
            </div>
            <div class="acr-rcpt-sec-body">
                <div class="acr-rcpt-alert-info">
                    <span class="me-1"><i class="fa-solid fa-circle-info"></i></span>
                    Presione el botón cuando la motocicleta haya ingresado físicamente al almacén.
                </div>
                <button type="button" class="acr-rcpt-arrival-btn" id="acr-rcpt-arrivalBtn">
                    <i class="fa-regular fa-clock"></i>
                    Registrar Llegada a Almacén
                </button>
                <div class="acr-rcpt-ts" id="acr-rcpt-tsDisplay">
                    <i class="fa-solid fa-check"></i>
                    <span id="acr-rcpt-tsText"></span>
                </div>
            </div>
        </div>

        <div class="acr-rcpt-section">
            <div class="acr-rcpt-sec-head">
                <span class="text-primary"><i class="fa-solid fa-camera"></i></span>
                <div>
                    <div class="acr-rcpt-sec-title">Evidencia Fotográfica y de Video</div>
                    <div class="acr-rcpt-sec-sub">Mínimo 6 de 8 ángulos requeridos</div>
                </div>
            </div>
            <div class="acr-rcpt-sec-body">
                <div class="acr-rcpt-alert-warn mb-3">
                    <span class="me-1"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    Las fotos <strong>REQ</strong> son obligatorias en operación real; aquí puede simular la carga.
                </div>
                <div class="acr-rcpt-ev-grid" id="acr-rcpt-evGrid"></div>
                <div class="mt-3 small text-muted">
                    Subidos: <span id="acr-rcpt-evCount">0</span> / 8 · Requeridos: <span id="acr-rcpt-evReq">0</span> / 6
                </div>
            </div>
        </div>

        <div class="acr-rcpt-section">
            <div class="acr-rcpt-sec-head">
                <span class="text-warning"><i class="fa-solid fa-sack-dollar"></i></span>
                <div>
                    <div class="acr-rcpt-sec-title">Estado de Cuenta</div>
                    <div class="acr-rcpt-sec-sub">Resumen financiero (API S2 — estado de cuenta)</div>
                </div>
            </div>
            <div class="acr-rcpt-sec-body">
                <p class="small text-muted mb-2" id="acr-rcpt-ec-leyenda" style="display:none;"></p>
                <div class="acr-rcpt-edc-grid">
                    <div class="acr-rcpt-edc-card">
                        <div class="acr-rcpt-edc-lbl">Saldo capital (ref.)</div>
                        <div class="acr-rcpt-edc-val hl" id="acr-rcpt-saldo-cap">—</div>
                    </div>
                    <div class="acr-rcpt-edc-card">
                        <div class="acr-rcpt-edc-lbl">Adeudo total (ref.)</div>
                        <div class="acr-rcpt-edc-val bad" id="acr-rcpt-adeudo">—</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="acr-rcpt-section">
            <div class="acr-rcpt-sec-head">
                <span class="text-success"><i class="fa-solid fa-file-lines"></i></span>
                <div>
                    <div class="acr-rcpt-sec-title">Recepción de Documentos</div>
                    <div class="acr-rcpt-sec-sub">Documentos esperados en este proceso</div>
                </div>
            </div>
            <div class="acr-rcpt-sec-body">
                <div class="acr-rcpt-doc-card">
                    <div class="acr-rcpt-doc-h">
                        <div>
                            <p class="acr-rcpt-doc-name mb-0">Contrato de Dación en Pago</p>
                            <div class="small text-muted">Documento principal — Firma del cliente</div>
                        </div>
                        <select class="form-select form-select-sm" id="acr-rcpt-dacionStatus" style="max-width:200px;">
                            <option value="">— Estado —</option>
                            <option value="received">Recibido firmado</option>
                            <option value="pending">Pendiente de firma</option>
                            <option value="missing">No se recibe</option>
                        </select>
                    </div>
                    <div class="p-3 border-top" id="acr-rcpt-dacionBody">
                        <p class="text-muted small mb-0">Seleccione el estado del documento.</p>
                    </div>
                </div>
                <div class="acr-rcpt-doc-card">
                    <div class="acr-rcpt-doc-h">
                        <div>
                            <p class="acr-rcpt-doc-name mb-0">Tarjeta de Circulación</p>
                            <div class="small text-muted">Documento vehicular</div>
                        </div>
                        <select class="form-select form-select-sm" id="acr-rcpt-tarjetaStatus" style="max-width:200px;">
                            <option value="">— Estado —</option>
                            <option value="received">Recibida</option>
                            <option value="missing">No se recibe</option>
                        </select>
                    </div>
                    <div class="p-3 border-top" id="acr-rcpt-tarjetaBody">
                        <p class="text-muted small mb-0">Seleccione el estado del documento.</p>
                    </div>
                </div>
                <div id="acr-rcpt-noDocSection" style="display:none;">
                    <hr class="my-3">
                    <div class="small fw-semibold text-danger mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>Justificación — Documento no recibido</div>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="small text-muted">Motivo</label>
                            <select class="form-select form-select-sm">
                                <option value="">Seleccionar…</option>
                                <option>Cliente no firmó / se negó a firmar</option>
                                <option>Documento extraviado por el cliente</option>
                                <option>Otro motivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted">Descripción</label>
                            <textarea class="form-control form-control-sm" rows="3" placeholder="Detalle para expediente…"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="acr-rcpt-section">
            <div class="acr-rcpt-sec-head">
                <span class="text-success"><i class="fa-solid fa-pen-to-square"></i></span>
                <div>
                    <div class="acr-rcpt-sec-title">Firma de Recepción</div>
                    <div class="acr-rcpt-sec-sub">Confirmación del agente de almacén</div>
                </div>
            </div>
            <div class="acr-rcpt-sec-body">
                <div class="border rounded p-3 mb-3" style="background:rgba(99,102,241,.06);border-color:rgba(99,102,241,.2)!important;">
                    <label class="small text-muted d-block mb-2">Firma de recepción (imagen — firma del agente de almacén)</label>
                    <input type="file" id="acr-rcpt-firma-file" class="form-control form-control-sm" accept="image/jpeg,image/png" />
                    <div id="acr-rcpt-firma-preview" class="mt-2"></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="small text-muted">Ubicación en almacén</label>
                        <input type="text" class="form-control form-control-sm" id="acr-rcpt-ubicacion" placeholder="Ej. Bodega 2 / Rack A-14">
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Observaciones</label>
                        <textarea class="form-control form-control-sm" id="acr-rcpt-obs" rows="2" placeholder="Condición general, accesorios…"></textarea>
                    </div>
                </div>
                <button type="button" class="acr-rcpt-confirm-btn" id="acr-rcpt-finBtn">
                    <i class="fa-solid fa-check me-2"></i>Confirmar recepción
                </button>
                <div class="acr-rcpt-success-msg" id="acr-rcpt-successMsg">
                    <i class="fa-solid fa-check me-1"></i>Recepción confirmada en el sistema.
                </div>
            </div>
        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const ACR_EV_TOTAL = 12;

    const ACR_CONFIG = {
        bandeja: {
            url:   '/AtencionClientes/obtenerRecuperacionRecepcion',
            vacio: 'No hay operaciones en bandeja (Recepción).',
        },
        dictaminado: {
            url:   '/AtencionClientes/obtenerDictaminadosRecepcion',
            vacio: 'No hay operaciones dictaminadas con expediente en esta etapa.',
        },
    };

    const ACR_BADGE = { bandeja: 'acr-badge-bandeja', dictaminado: 'acr-badge-dictaminado' };

    let _acrCargada = { bandeja: false, dictaminado: false };

    function acrEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function acrSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${acrEsc(msg)}</span>
        </div>`;
    }

    function acrRenderCardBandeja(item) {
        const ev = parseInt(item.evidencias_count, 10) || 0;
        const g  = item.gestor_nombre
            ? acrEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? acrEsc(item.fecha_asignacion)
            : '<span class="ae-list-muted">—</span>';
        const est = item.estatus ? acrEsc(item.estatus) : '<span class="ae-list-muted">—</span>';
        const dias = item.dias_en_pipeline != null && item.dias_en_pipeline !== ''
            ? acrEsc(String(item.dias_en_pipeline))
            : '<span class="ae-list-muted">—</span>';
        const nombreCliente = item.nombre_cliente
            ? acrEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? acrEsc(item.folio) : '—';

        return `
        <div class="ac-card">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${acrEsc(String(item.id_credito))}</span>
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
                        <span class="ac-lbl">Evidencias en expediente</span>
                        <span class="ac-val">${ev} / ${ACR_EV_TOTAL}</span>
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
                    <button type="button" class="acr-btn-almacen acr-abrir-almacen"
                        data-acr-id-op="${Number(item.id)}"
                        data-acr-id-credito="${Number(item.id_credito)}"
                        data-acr-folio="${encodeURIComponent(String(item.folio || ''))}"
                        data-acr-nombre="${encodeURIComponent(String(item.nombre_cliente || ''))}"
                        data-acr-gestor="${encodeURIComponent(String(item.gestor_nombre || ''))}">
                        <i class="fa-solid fa-warehouse me-1"></i>Recepción en almacén
                    </button>
                </div>
            </div>
        </div>`;
    }

    function acrRenderCardDictaminado(item) {
        const estPipeline = item.estatus ? acrEsc(item.estatus) : '<span class="ae-list-muted">—</span>';
        const dictTxt = item.dictamen
            ? acrEsc(item.dictamen)
            : '<span class="ae-list-muted">—</span>';
        const fechaD = item.fecha_dictamen
            ? acrEsc(item.fecha_dictamen)
            : '<span class="ae-list-muted">—</span>';
        const g = item.gestor_nombre
            ? acrEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">Sin asignar</span>';
        const nombreCliente = item.nombre_cliente
            ? acrEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? acrEsc(item.folio) : '—';

        return `
        <div class="ac-card acr-card-dict">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${acrEsc(String(item.id_credito))}</span>
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
                        <span class="ac-val" style="white-space:pre-line;">${acrEsc(item.comentarios)}</span>
                    </div>` : ''}
                </div>
            </div>
        </div>`;
    }

    function acrSetBadge(key, n) {
        const el = document.getElementById(ACR_BADGE[key]);
        if (!el) return;
        if (n > 0) {
            el.textContent   = n;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    function acrCargarConteosPestanas() {
        return fetch('/AtencionClientes/obtenerConteosRecepcion', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.conteos) return;
                const c = data.conteos;
                acrSetBadge('bandeja', c.bandeja);
                acrSetBadge('dictaminado', c.dictaminado);
            })
            .catch(function () {});
    }

    function acrCargarSeccion(key, forzar) {
        const cfg = ACR_CONFIG[key];
        if (!cfg) return Promise.resolve();

        const suf      = key === 'bandeja' ? 'bandeja' : 'dictaminado';
        const loaderId = 'acr-loader-' + suf;
        const listaId  = 'acr-lista-'  + suf;

        if (!forzar && _acrCargada[key]) {
            return Promise.resolve();
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return Promise.resolve();

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('acr-lista-updating');
        }

        return fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Error al cargar');
                }
                const n = data.datos.length;
                acrSetBadge(key, n);
                _acrCargada[key] = true;

                const render = key === 'bandeja' ? acrRenderCardBandeja : acrRenderCardDictaminado;
                if (n === 0) {
                    lista.innerHTML = acrSinDatos(cfg.vacio);
                } else {
                    lista.innerHTML = data.datos.map(function (d) { return render(d); }).join('');
                }
            })
            .catch(function (err) {
                lista.innerHTML = `<div class="alert alert-danger">${acrEsc(err.message || 'Error')}</div>`;
                acrSetBadge(key, 0);
            })
            .finally(function () {
                loader.style.display = 'none';
                lista.classList.remove('acr-lista-updating');
            });
    }

    function acrCargarVistaInicialConSpinner() {
        var hasSwal = typeof Swal !== 'undefined';
        if (hasSwal) {
            Swal.fire({
                title: 'Cargando Recepción…',
                html: '<span style="font-size:.875rem;color:#64748b;">Obteniendo todas las pestañas</span>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); },
            });
        }
        Promise.all([
            acrCargarConteosPestanas(),
            acrCargarSeccion('bandeja', true),
        ]).finally(function () {
            if (hasSwal) Swal.close();
        });
    }

    function acrFmtMoney(v) {
        const n = parseFloat(String(v).replace(/,/g, ''));
        if (!isFinite(n)) {
            return '—';
        }
        return n.toLocaleString('es-GT', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    const ACR_RCPT_MEDIA_SLOTS = [
        { key: 'vista_front', label: 'Vista frontal', required: true, isVideo: false, icon: 'fa-image' },
        { key: 'vista_trs', label: 'Vista trasera', required: true, isVideo: false, icon: 'fa-image' },
        { key: 'lado_izq', label: 'Lado izquierdo', required: true, isVideo: false, icon: 'fa-image' },
        { key: 'lado_der', label: 'Lado derecho', required: true, isVideo: false, icon: 'fa-image' },
        { key: 'tablero', label: 'Tablero / Odómetro', required: true, isVideo: false, icon: 'fa-gauge-high' },
        { key: 'vin', label: 'Número de serie (VIN)', required: true, isVideo: false, icon: 'fa-barcode' },
        { key: 'danos_vis', label: 'Daños visibles', required: false, isVideo: false, icon: 'fa-triangle-exclamation' },
        { key: 'vid_gen', label: 'Video general (360°)', required: false, isVideo: true, icon: 'fa-video' },
    ];

    var _acrRcptEvUploaded = {};
    var _acrRcptArrived = false;
    var _acrRcptIdOp = 0;
    var _acrRcptServerLocked = false;
    var _acrRcptLastDetalle = null;
    var ACR_RCPT_SLOT_DACION = 'doc_dacion_rcpt';
    var ACR_RCPT_SLOT_TARJ = 'doc_tarjeta_rcpt';
    var ACR_RCPT_SLOT_FIRMA = 'doc_firma_rcpt';

    function acrRcptSyncFinRecepcionUi(d) {
        var fin = document.getElementById('acr-rcpt-finBtn');
        var sm = document.getElementById('acr-rcpt-successMsg');
        var u = document.getElementById('acr-rcpt-ubicacion');
        var ob = document.getElementById('acr-rcpt-obs');
        if (!fin || !sm) return;
        var fi = document.getElementById('acr-rcpt-firma-file');
        if (!d) {
            sm.style.display = 'none';
            fin.style.display = '';
            fin.disabled = false;
            if (u) u.disabled = false;
            if (ob) ob.disabled = false;
            if (fi) { fi.disabled = false; }
            return;
        }
        var at = d.recepcion_confirmada_at;
        if (at != null && String(at) !== '') {
            var tf = d.recepcion_confirmada_at_fmt ? String(d.recepcion_confirmada_at_fmt) : String(at);
            sm.innerHTML = '<i class="fa-solid fa-check me-1"></i>Recepción confirmada el ' + acrEsc(tf) + '.';
            sm.style.display = 'block';
            fin.style.display = 'none';
            if (u) {
                u.disabled = true;
                if (d.recepcion_ubicacion != null && String(d.recepcion_ubicacion) !== '') u.value = String(d.recepcion_ubicacion);
            }
            if (ob) {
                ob.disabled = true;
                if (d.recepcion_observaciones != null) ob.value = String(d.recepcion_observaciones);
            }
            if (fi) fi.disabled = true;
        } else {
            sm.style.display = 'none';
            fin.style.display = '';
            fin.disabled = false;
            if (u) u.disabled = false;
            if (ob) ob.disabled = false;
        }
    }

    function acrRcptEvidUrl(detalle, slot) {
        var evs = (detalle && detalle.evidencias) ? detalle.evidencias : [];
        for (var i = 0; i < evs.length; i++) {
            if ((evs[i].slot || '') === slot && evs[i].url) {
                return String(evs[i].url);
            }
        }
        return '';
    }

    function acrRcptRefetchDetalle() {
        if (!_acrRcptIdOp) {
            return Promise.resolve(null);
        }
        return fetch('/MotosAdjudicadas/obtenerDetalle/' + _acrRcptIdOp + '?incluir_todas=1', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success && data.detalle) {
                    return data.detalle;
                }
                return null;
            });
    }

    function acrRcptDocRenderInitial() {
        _acrRcptLastDetalle = null;
        var ds = document.getElementById('acr-rcpt-dacionStatus');
        var ts = document.getElementById('acr-rcpt-tarjetaStatus');
        if (ds) { ds.value = ''; ds.disabled = false; }
        if (ts) { ts.value = ''; ts.disabled = false; }
        var bd = document.getElementById('acr-rcpt-dacionBody');
        var bt = document.getElementById('acr-rcpt-tarjetaBody');
        if (bd) bd.innerHTML = '<p class="text-muted small mb-0">Seleccione el estado del documento.</p>';
        if (bt) bt.innerHTML = '<p class="text-muted small mb-0">Seleccione el estado del documento.</p>';
        var fp = document.getElementById('acr-rcpt-firma-preview');
        var fi = document.getElementById('acr-rcpt-firma-file');
        if (fp) fp.innerHTML = '';
        if (fi) { fi.value = ''; fi.disabled = false; }
        var nd = document.getElementById('acr-rcpt-noDocSection');
        if (nd) nd.style.display = 'none';
    }

    function acrRcptSyncSelectsFromDetalle(d) {
        if (!d) return;
        var urlD = acrRcptEvidUrl(d, ACR_RCPT_SLOT_DACION);
        var urlT = acrRcptEvidUrl(d, ACR_RCPT_SLOT_TARJ);
        var sd = document.getElementById('acr-rcpt-dacionStatus');
        var st = document.getElementById('acr-rcpt-tarjetaStatus');
        if (sd) {
            sd.disabled = !!urlD;
            if (urlD) {
                sd.value = 'received';
            } else {
                var ed = String(d.recepcion_dacion_estado || '').toLowerCase();
                if (ed === 'pending') sd.value = 'pending';
                else if (ed === 'missing') sd.value = 'missing';
                else if (ed === 'received') sd.value = 'received';
                else sd.value = '';
            }
        }
        if (st) {
            st.disabled = !!urlT;
            if (urlT) {
                st.value = 'received';
            } else {
                var et = String(d.recepcion_tarjeta_estado || '').toLowerCase();
                if (et === 'missing') st.value = 'missing';
                else if (et === 'received') st.value = 'received';
                else st.value = '';
            }
        }
    }

    function acrRcptRenderDocumentacionBodies(d) {
        var det = d || _acrRcptLastDetalle || {};
        var urlD = acrRcptEvidUrl(det, ACR_RCPT_SLOT_DACION);
        var urlT = acrRcptEvidUrl(det, ACR_RCPT_SLOT_TARJ);
        var urlF = acrRcptEvidUrl(det, ACR_RCPT_SLOT_FIRMA);
        var bd = document.getElementById('acr-rcpt-dacionBody');
        var bt = document.getElementById('acr-rcpt-tarjetaBody');
        var sd = document.getElementById('acr-rcpt-dacionStatus');
        var st = document.getElementById('acr-rcpt-tarjetaStatus');
        var noDoc = document.getElementById('acr-rcpt-noDocSection');
        var dv = sd ? sd.value : '';
        var tv = st ? st.value : '';

        if (bd) {
            if (urlD) {
                bd.innerHTML = '<p class="text-success small fw-semibold mb-2"><i class="fa-solid fa-check me-1"></i>Contrato de Dación recibido con firma del cliente.</p>' +
                    '<div class="alert alert-success py-2 small mb-0">Documento recibido. Adjuntar escaneo al expediente digital antes de cerrar.</div>';
            } else if (dv === 'pending') {
                bd.innerHTML = '<p class="text-warning small fw-semibold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>El cliente aún no ha firmado el Contrato de Dación.</p>' +
                    '<div class="alert alert-warning py-2 small mb-0">Coordinar con legal para la firma antes del cierre definitivo.</div>';
            } else if (dv === 'missing') {
                bd.innerHTML = '<p class="text-danger small fw-semibold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>El Contrato de Dación NO será recibido en este proceso.</p>' +
                    '<div class="alert alert-danger py-2 small mb-0">Documentar la razón y notificar a legal.</div>';
            } else if (dv === 'received') {
                bd.innerHTML = '<p class="text-muted small mb-2">Seleccione el archivo del contrato firmado (PDF, JPG o PNG). Al guardar correctamente verá el mensaje de confirmación.</p>' +
                    '<input type="file" class="form-control form-control-sm acr-rcpt-slot-fi" data-slot="' + ACR_RCPT_SLOT_DACION + '" accept="application/pdf,image/jpeg,image/png" />';
            } else {
                bd.innerHTML = '<p class="text-muted small mb-0">Seleccione el estado del documento.</p>';
            }
        }
        if (bt) {
            if (urlT) {
                bt.innerHTML = '<p class="text-success small fw-semibold mb-0"><i class="fa-solid fa-check me-1"></i>Tarjeta de circulación recibida.</p>';
            } else if (tv === 'missing') {
                bt.innerHTML = '<p class="text-warning small fw-semibold mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i>No se recibe la tarjeta. Documentar en observaciones.</p>';
            } else if (tv === 'received') {
                bt.innerHTML = '<p class="text-muted small mb-2">Seleccione la imagen de la tarjeta de circulación (JPG o PNG).</p>' +
                    '<input type="file" class="form-control form-control-sm acr-rcpt-slot-fi" data-slot="' + ACR_RCPT_SLOT_TARJ + '" accept="image/jpeg,image/png" />';
            } else {
                bt.innerHTML = '<p class="text-muted small mb-0">Seleccione el estado del documento.</p>';
            }
        }
        if (noDoc) {
            noDoc.style.display = (dv === 'missing' || tv === 'missing') ? 'block' : 'none';
        }
        var fp = document.getElementById('acr-rcpt-firma-preview');
        var fi = document.getElementById('acr-rcpt-firma-file');
        if (fp) {
            fp.innerHTML = '';
            if (urlF) {
                var okf = document.createElement('p');
                okf.className = 'text-success small fw-semibold mb-2';
                okf.innerHTML = '<i class="fa-solid fa-check me-1"></i>Firma de recepción registrada.';
                fp.appendChild(okf);
                var imf = document.createElement('img');
                imf.className = 'acr-rcpt-doc-thumb';
                imf.alt = 'Firma';
                imf.src = urlF;
                fp.appendChild(imf);
            }
        }
        if (fi) {
            fi.disabled = !!urlF;
        }
    }

    function acrRcptApplyDocumentacionDesdeDetalle(d) {
        if (!d) {
            acrRcptDocRenderInitial();
            acrRcptSyncFinRecepcionUi(null);
            return;
        }
        _acrRcptLastDetalle = d;
        acrRcptSyncSelectsFromDetalle(d);
        acrRcptRenderDocumentacionBodies(d);
        acrRcptSyncEvDesdeDetalle(d);
        acrRcptSyncFinRecepcionUi(d);
    }

    function acrRcptUploadRecepcionEvidencia(slot, file) {
        if (!_acrRcptIdOp || !file) {
            return Promise.resolve(null);
        }
        var fd = new FormData();
        fd.append('id_operacion', String(_acrRcptIdOp));
        fd.append('slot', slot);
        fd.append('archivo', file, file.name);
        return fetch('/MotosAdjudicadas/subirEvidencia', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); });
    }

    function acrRcptApplyArrivalUi(fechaLinea, serverLocked) {
        _acrRcptArrived = true;
        if (serverLocked) {
            _acrRcptServerLocked = true;
        }
        var btn = document.getElementById('acr-rcpt-arrivalBtn');
        var dsp = document.getElementById('acr-rcpt-tsDisplay');
        var tx = document.getElementById('acr-rcpt-tsText');
        if (btn) {
            btn.classList.add('arrived');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Llegada registrada';
            btn.disabled = !!serverLocked;
        }
        if (tx) {
            tx.textContent = serverLocked
                ? ('Registrado en sistema: ' + (fechaLinea || '—'))
                : ('Ingresó: ' + (fechaLinea || '—') + ' | Almacén (pendiente de guardar)');
        }
        if (dsp) dsp.classList.add('show');
    }

    function acrRcptUpdateEvCount() {
        var total = 0;
        var req = 0;
        ACR_RCPT_MEDIA_SLOTS.forEach(function (row) {
            if (_acrRcptEvUploaded[row.key]) {
                total++;
                if (row.required) req++;
            }
        });
        var elT = document.getElementById('acr-rcpt-evCount');
        var elR = document.getElementById('acr-rcpt-evReq');
        if (elT) elT.textContent = String(total);
        if (elR) elR.textContent = String(req);
    }

    function acrRcptSetCellState(cell, cfg, row) {
        if (!cell || !cfg) return;
        var statusEl = cell.querySelector('.acr-rcpt-ev-status');
        var prevImg = cell.querySelector('img.acr-rcpt-ev-thumb');
        var prevVid = cell.querySelector('video.acr-rcpt-ev-thumb');
        if (prevImg) prevImg.remove();
        if (prevVid) prevVid.remove();

        var hasUrl = !!(row && row.url);
        _acrRcptEvUploaded[cfg.key] = hasUrl;
        cell.classList.remove('uploading');

        if (hasUrl) {
            cell.classList.add('uploaded');
            var mediaEl;
            if (cfg.isVideo) {
                mediaEl = document.createElement('video');
                mediaEl.className = 'acr-rcpt-ev-thumb';
                mediaEl.muted = true;
                mediaEl.playsInline = true;
                mediaEl.preload = 'metadata';
                mediaEl.src = String(row.url);
            } else {
                mediaEl = document.createElement('img');
                mediaEl.className = 'acr-rcpt-ev-thumb';
                mediaEl.alt = cfg.label;
                mediaEl.src = String(row.url);
            }
            cell.insertBefore(mediaEl, cell.firstChild);
        } else {
            cell.classList.remove('uploaded');
        }

        if (statusEl) {
            statusEl.classList.remove('acr-rcpt-ev-status-pendiente', 'acr-rcpt-ev-status-cierre');
            if (hasUrl) {
                var est = String(row.estatus || '').toLowerCase();
                if (est === 'cierre_almacen') {
                    statusEl.classList.add('acr-rcpt-ev-status-cierre');
                    statusEl.textContent = 'Cierre almacén';
                } else {
                    statusEl.classList.add('acr-rcpt-ev-status-pendiente');
                    statusEl.textContent = 'Pendiente almacén';
                }
                statusEl.style.display = 'inline-flex';
            } else {
                statusEl.style.display = 'none';
                statusEl.textContent = '';
            }
        }
    }

    function acrRcptSyncEvDesdeDetalle(detalle) {
        var bySlot = {};
        var evs = (detalle && detalle.evidencias) ? detalle.evidencias : [];
        evs.forEach(function (ev) {
            var k = String(ev.slot || '');
            if (k && ev.url) {
                bySlot[k] = ev;
            }
        });

        ACR_RCPT_MEDIA_SLOTS.forEach(function (cfg, i) {
            var cell = document.getElementById('acr-rcpt-ev-' + i);
            acrRcptSetCellState(cell, cfg, bySlot[cfg.key] || null);
        });
        acrRcptUpdateEvCount();
    }

    function acrRcptBuildEvGrid() {
        var host = document.getElementById('acr-rcpt-evGrid');
        if (!host) return;
        host.innerHTML = ACR_RCPT_MEDIA_SLOTS.map(function (row, i) {
            var badge = row.required
                ? '<span class="badge bg-danger bg-opacity-75" style="font-size:9px;">REQ</span>'
                : '<span class="badge bg-secondary" style="font-size:9px;">OPC</span>';
            var accept = row.isVideo ? 'video/mp4' : 'image/*';
            return '<div class="acr-rcpt-ev-cell" id="acr-rcpt-ev-' + i + '" data-idx="' + i + '">' +
                '<input type="file" id="acr-rcpt-fi-' + i + '" accept="' + accept + '" />' +
                '<button class="acr-rcpt-slot-btn" tabindex="-1"><i class="fa-solid fa-plus"></i></button>' +
                '<i class="acr-rcpt-slot-icon-ph fa-solid ' + row.icon + '"></i>' +
                '<span class="acr-rcpt-ev-label">' + acrEsc(row.label) + '</span>' +
                '<span class="acr-rcpt-ev-status"></span>' +
                badge +
                '<span class="acr-rcpt-ev-check"><i class="fa-solid fa-check"></i></span></div>';
        }).join('');
        _acrRcptEvUploaded = {};
        ACR_RCPT_MEDIA_SLOTS.forEach(function (row) {
            _acrRcptEvUploaded[row.key] = false;
        });
        host.querySelectorAll('.acr-rcpt-ev-cell').forEach(function (cell) {
            cell.addEventListener('click', function (ev) {
                if (ev.target && ev.target.tagName === 'INPUT') return;
                var idx = parseInt(cell.getAttribute('data-idx'), 10);
                var inp = document.getElementById('acr-rcpt-fi-' + idx);
                if (inp) inp.click();
            });
        });
        host.querySelectorAll('input[type=file]').forEach(function (inp) {
            inp.addEventListener('change', function () {
                var id = inp.id.replace('acr-rcpt-fi-', '');
                var ii = parseInt(id, 10);
                var cfg = ACR_RCPT_MEDIA_SLOTS[ii];
                if (!cfg) return;
                if (!inp.files || !inp.files[0]) return;

                var c = document.getElementById('acr-rcpt-ev-' + ii);
                if (c) c.classList.add('uploading');

                acrRcptUploadRecepcionEvidencia(cfg.key, inp.files[0])
                    .then(function (res) {
                        inp.value = '';
                        if (res && res.success) {
                            return acrRcptRefetchDetalle();
                        }
                        if (c) c.classList.remove('uploading');
                        window.alert((res && res.message) ? String(res.message) : 'No se pudo subir la evidencia.');
                        return null;
                    })
                    .then(function (det) {
                        if (det) {
                            _acrRcptLastDetalle = det;
                            acrRcptSyncEvDesdeDetalle(det);
                        }
                    })
                    .catch(function () {
                        if (c) c.classList.remove('uploading');
                        window.alert('Error de red al subir la evidencia.');
                    });
            });
        });
        acrRcptUpdateEvCount();
    }

    function acrRcptResetVistaDemo() {
        _acrRcptArrived = false;
        _acrRcptServerLocked = false;
        _acrRcptIdOp = 0;
        var btn = document.getElementById('acr-rcpt-arrivalBtn');
        var ts = document.getElementById('acr-rcpt-tsDisplay');
        if (btn) {
            btn.classList.remove('arrived');
            btn.style.display = '';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-regular fa-clock"></i> Registrar Llegada a Almacén';
        }
        if (ts) ts.classList.remove('show');
        acrRcptDocRenderInitial();
        var fin = document.getElementById('acr-rcpt-finBtn');
        var sm = document.getElementById('acr-rcpt-successMsg');
        if (fin) fin.style.display = '';
        if (sm) sm.style.display = 'none';
        var ub = document.getElementById('acr-rcpt-ubicacion');
        var ob = document.getElementById('acr-rcpt-obs');
        if (ub) { ub.value = ''; ub.disabled = false; }
        if (ob) { ob.value = ''; ob.disabled = false; }
        acrRcptSyncFinRecepcionUi(null);
        acrRcptBuildEvGrid();
    }

    function acrRcptMostrarVista(btn) {
        var modalEl = document.getElementById('modalAcrRecepcionAlmacen');
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
        var nombre = '';
        var folio = '';
        var gestor = '';
        try {
            nombre = decodeURIComponent(btn.getAttribute('data-acr-nombre') || '');
            folio = decodeURIComponent(btn.getAttribute('data-acr-folio') || '');
            gestor = decodeURIComponent(btn.getAttribute('data-acr-gestor') || '');
        } catch (e1) { /* ignore */ }
        var idCred = parseInt(btn.getAttribute('data-acr-id-credito') || '0', 10) || 0;
        var idOp = parseInt(btn.getAttribute('data-acr-id-op') || '0', 10) || 0;
        var meta = document.getElementById('acr-rcpt-meta-line');
        var fecha = new Date().toLocaleDateString('es-GT', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        if (meta) {
            meta.textContent = 'Caso #' + (folio || ('CRED-' + idCred)) + ' | Agente: ' + (gestor || '—') + ' | ' + fecha;
        }
        var elC = document.getElementById('acr-rcpt-cliente');
        var elCo = document.getElementById('acr-rcpt-contrato');
        var elM = document.getElementById('acr-rcpt-moto');
        var elP = document.getElementById('acr-rcpt-placa');
        if (elC) elC.textContent = nombre || '—';
        if (elCo) elCo.textContent = folio ? folio : ('CTR-' + idCred);
        if (elM) elM.textContent = '—';
        if (elP) elP.textContent = '—';
        var sc = document.getElementById('acr-rcpt-saldo-cap');
        var ad = document.getElementById('acr-rcpt-adeudo');
        var ley = document.getElementById('acr-rcpt-ec-leyenda');
        if (sc) sc.textContent = '…';
        if (ad) ad.textContent = '…';
        if (ley) {
            ley.textContent = '';
            ley.style.display = 'none';
        }
        acrRcptResetVistaDemo();
        _acrRcptIdOp = idOp;
        var arrB0 = document.getElementById('acr-rcpt-arrivalBtn');
        if (arrB0) arrB0.disabled = true;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();

        if (!idOp || !idCred) {
            if (ley) {
                ley.textContent = 'Falta id de operación o crédito para consultar S2.';
                ley.style.display = 'block';
            }
            if (sc) sc.textContent = '—';
            if (ad) ad.textContent = '—';
            if (arrB0) arrB0.disabled = false;
            return;
        }

        var urlDet = '/MotosAdjudicadas/obtenerDetalle/' + idOp + '?incluir_todas=1';
        var urlEc = '/MotosAdjudicadas/recepcionResumenFinanciero?id_credito=' + encodeURIComponent(String(idCred));
        Promise.all([
            fetch(urlDet, { headers: { Accept: 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.json(); }),
            fetch(urlEc, { headers: { Accept: 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.json(); }),
        ]).then(function (pair) {
            var jd = pair[0];
            var je = pair[1];
            if (jd && jd.success && jd.detalle) {
                var d = jd.detalle;
                var marca = (d.marca != null && String(d.marca).trim() !== '') ? String(d.marca).trim() : '';
                var modelo = (d.modelo != null && String(d.modelo).trim() !== '') ? String(d.modelo).trim() : '';
                var motoTxt = [marca, modelo].filter(Boolean).join(' ');
                if (elM) elM.textContent = motoTxt || '—';
                var pl = d.placas != null ? String(d.placas).trim() : '';
                if (elP) elP.textContent = pl || '—';
                var fl = d.fecha_llegada_almacen_fmt || d.fecha_llegada_almacen || '';
                if (fl) {
                    acrRcptApplyArrivalUi(String(fl), true);
                }
                acrRcptApplyDocumentacionDesdeDetalle(jd.detalle);
            }
            if (je && je.success) {
                if (sc) sc.textContent = acrFmtMoney(je.saldo_capital);
                if (ad) ad.textContent = acrFmtMoney(je.adeudo_total);
                if (ley) {
                    ley.textContent = '';
                    ley.style.display = 'none';
                }
            } else {
                if (sc) sc.textContent = '—';
                if (ad) ad.textContent = '—';
                if (ley) {
                    ley.textContent = (je && je.message) ? String(je.message) : 'No se pudo obtener el estado de cuenta en S2.';
                    ley.style.display = 'block';
                }
            }
        }).catch(function () {
            if (sc) sc.textContent = '—';
            if (ad) ad.textContent = '—';
            if (ley) {
                ley.textContent = 'Error de red al consultar detalle o estado de cuenta.';
                ley.style.display = 'block';
            }
        }).finally(function () {
            var b = document.getElementById('acr-rcpt-arrivalBtn');
            if (!b) return;
            if (_acrRcptArrived || _acrRcptServerLocked) {
                b.disabled = true;
                return;
            }
            b.disabled = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        acrCargarVistaInicialConSpinner();

        var bb = document.getElementById('acr-tab-bandeja-btn');
        var bd = document.getElementById('acr-tab-dictaminado-btn');
        if (bb) {
            bb.addEventListener('shown.bs.tab', function () { acrCargarSeccion('bandeja', false); });
        }
        if (bd) {
            bd.addEventListener('shown.bs.tab', function () { acrCargarSeccion('dictaminado', false); });
        }

        var listaB = document.getElementById('acr-lista-bandeja');
        if (listaB) {
            listaB.addEventListener('click', function (ev) {
                var b = ev.target.closest('.acr-abrir-almacen');
                if (!b) return;
                acrRcptMostrarVista(b);
            });
        }

        var modalAlmacen = document.getElementById('modalAcrRecepcionAlmacen');
        if (modalAlmacen) {
            modalAlmacen.addEventListener('hidden.bs.modal', function () {
                acrRcptResetVistaDemo();
            });
            modalAlmacen.addEventListener('change', function (ev) {
                var t = ev.target;
                if (!t || !t.classList || !t.classList.contains('acr-rcpt-slot-fi')) return;
                var slot = t.getAttribute('data-slot') || '';
                if (!t.files || !t.files[0]) return;
                acrRcptUploadRecepcionEvidencia(slot, t.files[0])
                    .then(function (res) {
                        t.value = '';
                        if (res && res.success) {
                            return acrRcptRefetchDetalle();
                        }
                        window.alert((res && res.message) ? String(res.message) : 'No se pudo subir el archivo.');
                        return null;
                    })
                    .then(function (det) {
                        if (det) {
                            acrRcptApplyDocumentacionDesdeDetalle(det);
                        }
                    });
            });
        }

        var firmaInp = document.getElementById('acr-rcpt-firma-file');
        if (firmaInp) {
            firmaInp.addEventListener('change', function () {
                if (!firmaInp.files || !firmaInp.files[0]) return;
                acrRcptUploadRecepcionEvidencia(ACR_RCPT_SLOT_FIRMA, firmaInp.files[0])
                    .then(function (res) {
                        firmaInp.value = '';
                        if (res && res.success) {
                            return acrRcptRefetchDetalle();
                        }
                        window.alert((res && res.message) ? String(res.message) : 'No se pudo subir la firma.');
                        return null;
                    })
                    .then(function (det) {
                        if (det) {
                            acrRcptApplyDocumentacionDesdeDetalle(det);
                        }
                    });
            });
        }

        var arrBtn = document.getElementById('acr-rcpt-arrivalBtn');
        if (arrBtn) {
            arrBtn.addEventListener('click', function () {
                if (_acrRcptArrived || _acrRcptServerLocked) return;
                if (!_acrRcptIdOp) {
                    window.alert('No se identificó la operación. Cierre el modal y vuelva a abrirlo.');
                    return;
                }
                arrBtn.disabled = true;
                fetch('/MotosAdjudicadas/registrarLlegadaAlmacenRecepcion', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id_operacion: _acrRcptIdOp }),
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.success && data.fecha_llegada_almacen) {
                            acrRcptApplyArrivalUi(String(data.fecha_llegada_almacen), true);
                            return;
                        }
                        if (data && data.ya_registrada && data.fecha_llegada_almacen) {
                            acrRcptApplyArrivalUi(String(data.fecha_llegada_almacen), true);
                            return;
                        }
                        arrBtn.disabled = false;
                        window.alert((data && data.message) ? String(data.message) : 'No se pudo registrar la llegada.');
                    })
                    .catch(function () {
                        arrBtn.disabled = false;
                        window.alert('Error de red al registrar la llegada a almacén.');
                    });
            });
        }

        var ds = document.getElementById('acr-rcpt-dacionStatus');
        if (ds) {
            ds.addEventListener('change', function () {
                var v = ds.value || '';
                if (!_acrRcptIdOp || ds.disabled) return;
                if (v === 'pending' || v === 'missing') {
                    fetch('/MotosAdjudicadas/guardarRecepcionEstadoDocumento', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ id_operacion: _acrRcptIdOp, documento: 'dacion', estado: v }),
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (!data || !data.success) {
                                window.alert((data && data.message) ? String(data.message) : 'No se pudo guardar.');
                                return null;
                            }
                            return acrRcptRefetchDetalle();
                        })
                        .then(function (det) {
                            if (det) {
                                acrRcptApplyDocumentacionDesdeDetalle(det);
                            }
                        });
                } else {
                    acrRcptRenderDocumentacionBodies(_acrRcptLastDetalle);
                }
            });
        }
        var tsSt = document.getElementById('acr-rcpt-tarjetaStatus');
        if (tsSt) {
            tsSt.addEventListener('change', function () {
                var v = tsSt.value || '';
                if (!_acrRcptIdOp || tsSt.disabled) return;
                if (v === 'missing') {
                    fetch('/MotosAdjudicadas/guardarRecepcionEstadoDocumento', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ id_operacion: _acrRcptIdOp, documento: 'tarjeta', estado: 'missing' }),
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (!data || !data.success) {
                                window.alert((data && data.message) ? String(data.message) : 'No se pudo guardar.');
                                return null;
                            }
                            return acrRcptRefetchDetalle();
                        })
                        .then(function (det) {
                            if (det) {
                                acrRcptApplyDocumentacionDesdeDetalle(det);
                            }
                        });
                } else {
                    acrRcptRenderDocumentacionBodies(_acrRcptLastDetalle);
                }
            });
        }

        var finB = document.getElementById('acr-rcpt-finBtn');
        var sucM = document.getElementById('acr-rcpt-successMsg');
        if (finB && sucM) {
            finB.addEventListener('click', function () {
                if (!_acrRcptIdOp) {
                    window.alert('No se identificó la operación.');
                    return;
                }
                var ub = document.getElementById('acr-rcpt-ubicacion');
                var ob = document.getElementById('acr-rcpt-obs');
                var uval = ub ? String(ub.value || '').trim() : '';
                var oval = ob ? String(ob.value || '').trim() : '';
                finB.disabled = true;
                fetch('/MotosAdjudicadas/confirmarRecepcionAlmacen', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        id_operacion: _acrRcptIdOp,
                        ubicacion: uval,
                        observaciones: oval,
                    }),
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && (data.success || data.ya_confirmada)) {
                            return acrRcptRefetchDetalle();
                        }
                        finB.disabled = false;
                        window.alert((data && data.message) ? String(data.message) : 'No se pudo confirmar la recepción.');
                        return null;
                    })
                    .then(function (det) {
                        if (det) {
                            acrRcptApplyDocumentacionDesdeDetalle(det);
                        } else {
                            finB.disabled = false;
                        }
                    })
                    .catch(function () {
                        finB.disabled = false;
                        window.alert('Error de red al confirmar la recepción.');
                    });
            });
        }
    });
})();
</script>
