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

.ac-nav-tabs .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.6rem 1.4rem;
}
.ac-nav-tabs .nav-link.active {
    color: #6d28d9;
    border-bottom-color: #fff !important;
}
.ac-nav-tabs .nav-link:hover:not(.active) {
    color: #6d28d9;
    background: #faf5ff;
}

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
    border-color: #e9d5ff;
    background: #faf5ff;
}

.ac-card-header {
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.ac-card-header .ac-credito-id {
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: .3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1;
}
.ac-card-header .ac-credito-id small {
    font-weight: 400;
    font-size: .75rem;
    opacity: .9;
    margin-left: .35rem;
}

.ac-card-body { padding: 1.1rem 1.25rem; }
.ac-detail-row {
    display: flex;
    align-items: baseline;
    gap: .5rem;
    margin-bottom: .45rem;
    font-size: .875rem;
}
.ac-detail-row:last-child { margin-bottom: 0; }
.ac-detail-row .ac-lbl {
    color: #64748b;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
    min-width: 140px;
}
.ac-detail-row .ac-val {
    color: #1e293b;
    font-weight: 500;
}

.ac-card-footer {
    border-top: 1px solid #e2e8f0;
    padding: .75rem 1.25rem;
    display: flex;
    justify-content: flex-end;
    background: #faf5ff;
    gap: .5rem;
}

.acr-btn-pipeline {
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
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
.acr-btn-pipeline:hover  { opacity: .92; transform: translateY(-1px); color: #fff; }
.acr-btn-pipeline:active { transform: translateY(0); }

body.dark-mode .ac-nav-tabs .nav-link        { color: #94a3b8; }
body.dark-mode .ac-nav-tabs .nav-link.active { color: #c4b5fd; border-bottom-color: #1e293b !important; }
body.dark-mode .ac-nav-tabs .nav-link:hover:not(.active) { background: #3b0764; color: #c4b5fd; }
body.dark-mode .ac-card              { background: #1e293b; border-color: #334155; }
body.dark-mode .ac-card.acr-card-dict { background: #1e1b4b; }
body.dark-mode .ac-detail-row .ac-lbl { color: #94a3b8; }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer       { background: #1e1b4b; border-color: #334155; }

.acr-lista-updating {
    opacity: 0.5;
    transition: opacity 0.12s ease;
    pointer-events: none;
}

/* Vista expediente almacén (referencia UX jefe) */
#acr-wrap-principal { transition: opacity .15s ease; }
#acr-vista-almacen.acr-rcpt-page {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    font-size: 15px;
    color: #1a1a1a;
    background: #f0eeea;
    border-radius: 0.75rem;
    padding: 1.25rem;
    margin-top: 0.5rem;
}
body.dark-mode #acr-vista-almacen.acr-rcpt-page {
    background: #1e293b;
    color: #e2e8f0;
}
.acr-rcpt-page .acr-rcpt-header {
    background: #1a1a1a;
    color: #fff;
    padding: 1rem 1.25rem;
    border-radius: 10px 10px 0 0;
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
    background: #c47a00;
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
    background: #e8e6e0;
    border-left: 1px solid rgba(0,0,0,.1);
    border-right: 1px solid rgba(0,0,0,.1);
}
body.dark-mode .acr-rcpt-steps { background: #334155; }
.acr-rcpt-step {
    flex: 1;
    padding: 10px 6px;
    text-align: center;
    font-size: 11px;
    color: #888;
    border-right: 1px solid rgba(0,0,0,.08);
}
.acr-rcpt-step:last-child { border-right: none; }
.acr-rcpt-step.done { color: #2d6a1f; background: #eaf3de; font-weight: 600; }
.acr-rcpt-step.active { color: #c47a00; background: #fdf3dc; font-weight: 600; }
body.dark-mode .acr-rcpt-step.done { background: #14532d; color: #bbf7d0; }
body.dark-mode .acr-rcpt-step.active { background: #78350f; color: #fde68a; }
.acr-rcpt-step-num { display: block; font-size: 15px; margin-bottom: 2px; }
.acr-rcpt-section {
    border: 1px solid rgba(0,0,0,.1);
    border-top: none;
    background: #fff;
}
body.dark-mode .acr-rcpt-section { background: #0f172a; border-color: #334155; }
.acr-rcpt-section:last-child { border-radius: 0 0 10px 10px; }
.acr-rcpt-sec-head {
    padding: 12px 1rem;
    border-bottom: 1px solid rgba(0,0,0,.08);
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f5f4f1;
}
body.dark-mode .acr-rcpt-sec-head { background: #1e293b; }
.acr-rcpt-sec-title { font-size: 14px; font-weight: 600; margin: 0; }
.acr-rcpt-sec-sub { font-size: 11px; color: #888; margin: 2px 0 0 0; }
.acr-rcpt-sec-body { padding: 1rem 1.25rem; }
.acr-rcpt-arrival-btn {
    width: 100%;
    padding: 0.85rem;
    background: #1a1a1a;
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
.acr-rcpt-arrival-btn:hover { background: #2a2a2a; color: #fff; }
.acr-rcpt-arrival-btn.arrived { background: #2d6a1f; cursor: default; }
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
    border: 1px dashed rgba(0,0,0,.2);
    border-radius: 8px;
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    background: #f5f4f1;
    padding: 6px;
    text-align: center;
}
body.dark-mode .acr-rcpt-ev-cell { background: #1e293b; }
.acr-rcpt-ev-cell:hover { border-color: #c47a00; background: #fdf3dc; }
body.dark-mode .acr-rcpt-ev-cell:hover { background: #431407; }
.acr-rcpt-ev-cell.uploaded {
    border: 2px solid #639922;
    background: #eaf3de;
}
.acr-rcpt-ev-label { font-size: 11px; color: #64748b; line-height: 1.25; }
.acr-rcpt-ev-cell.uploaded .acr-rcpt-ev-label { color: #2d6a1f; font-weight: 600; }
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
    background: #fdf3dc;
    color: #6b4200;
    border: 1px solid #e8a820;
    margin-bottom: 12px;
}
.acr-rcpt-edc-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 12px;
}
.acr-rcpt-edc-card {
    background: #f5f4f1;
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
    background: #f5f4f1;
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
    background: #f5f4f1;
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
    background: #c47a00;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}
.acr-rcpt-confirm-btn:hover { background: #b36e00; color: #fff; }
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
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.35);
    color: #fff;
    font-weight: 700;
    font-size: .8rem;
    padding: .35rem 1rem;
    border-radius: 2rem;
    cursor: pointer;
}
.acr-btn-almacen:hover { background: rgba(255,255,255,.3); color: #fff; }
</style>

<div class="container-fluid py-4">

    <div class="acr-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-flag-checkered fa-2x"></i>
        <div>
            <h4>5.- Recepción</h4>
            <p>Gestión de recepción para operaciones de motos adjudicadas</p>
        </div>
    </div>

    <div id="acr-wrap-principal">
    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-tabs ac-nav-tabs border-0 mb-0" id="acrTabNav" role="tablist">
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

    <div id="acr-vista-almacen" class="acr-rcpt-page" style="display:none;" aria-hidden="true">
        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="acr-btn-volver-almacen">
            <i class="fa-solid fa-arrow-left me-1"></i>Volver a la bandeja
        </button>

        <div class="acr-rcpt-header">
            <div>
                <h1 class="text-white">Recepción de Moto — Almacén</h1>
                <p id="acr-rcpt-meta-line">Caso &nbsp;|&nbsp; Agente: — &nbsp;|&nbsp; <span id="acr-rcpt-hdate"></span></p>
            </div>
            <span class="acr-rcpt-badge-rec">RECUPERACIÓN</span>
        </div>

        <div class="acr-rcpt-steps">
            <div class="acr-rcpt-step done"><span class="acr-rcpt-step-num"><i class="fa-solid fa-check"></i></span>Gestión</div>
            <div class="acr-rcpt-step done"><span class="acr-rcpt-step-num"><i class="fa-solid fa-check"></i></span>Recolección</div>
            <div class="acr-rcpt-step done"><span class="acr-rcpt-step-num"><i class="fa-solid fa-check"></i></span>Traslado</div>
            <div class="acr-rcpt-step active"><span class="acr-rcpt-step-num">4</span>Almacén</div>
            <div class="acr-rcpt-step"><span class="acr-rcpt-step-num">5</span>Cierre</div>
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
                    <div class="acr-rcpt-sec-sub">Mínimo 6 de 8 ángulos requeridos (demo visual)</div>
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
                    <div class="acr-rcpt-sec-sub">Resumen financiero (datos de expediente)</div>
                </div>
            </div>
            <div class="acr-rcpt-sec-body">
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
                <div class="small text-muted text-uppercase fw-semibold mb-2" style="letter-spacing:.04em;">Últimas cuotas (ejemplo)</div>
                <table class="acr-rcpt-cuotas">
                    <thead><tr><th>#</th><th>Vencimiento</th><th>Monto</th><th>Estado</th></tr></thead>
                    <tbody>
                        <tr><td>—</td><td>—</td><td>—</td><td><span class="badge bg-success">Pagada</span></td></tr>
                        <tr><td>—</td><td>—</td><td>—</td><td><span class="badge bg-warning text-dark">Pendiente</span></td></tr>
                    </tbody>
                </table>
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
                    <i class="fa-solid fa-check me-2"></i>Confirmar recepción (demo)
                </button>
                <div class="acr-rcpt-success-msg" id="acr-rcpt-successMsg">
                    <i class="fa-solid fa-check me-1"></i>Recepción registrada (vista demo). Conectar backend cuando defina reglas.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const ACR_EV_TOTAL = 10;

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
            : '<span class="text-muted fst-italic">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? acrEsc(item.fecha_asignacion)
            : '<span class="text-muted fst-italic">—</span>';
        const est = item.estatus ? acrEsc(item.estatus) : '—';
        const dias = item.dias_en_pipeline != null && item.dias_en_pipeline !== ''
            ? acrEsc(String(item.dias_en_pipeline))
            : '—';

        return `
        <div class="ac-card">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${acrEsc(String(item.id_credito))}
                    <small>${acrEsc(item.nombre_cliente || '')}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">${acrEsc(item.folio || '—')}</span>
            </div>
            <div class="ac-card-body">
                <div class="ac-detail-row">
                    <span class="ac-lbl">Estatus pipeline</span>
                    <span class="ac-val">${est}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Gestor a cargo</span>
                    <span class="ac-val">${g}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Asignación realizada</span>
                    <span class="ac-val">${fa}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Evidencias en expediente</span>
                    <span class="ac-val">${ev} / ${ACR_EV_TOTAL}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Días en pipeline</span>
                    <span class="ac-val">${dias}</span>
                </div>
            </div>
            <div class="ac-card-footer justify-content-end flex-wrap">
                <button type="button" class="acr-btn-almacen acr-abrir-almacen"
                    data-acr-id-op="${Number(item.id)}"
                    data-acr-id-credito="${Number(item.id_credito)}"
                    data-acr-folio="${encodeURIComponent(String(item.folio || ''))}"
                    data-acr-nombre="${encodeURIComponent(String(item.nombre_cliente || ''))}"
                    data-acr-gestor="${encodeURIComponent(String(item.gestor_nombre || ''))}"
                    data-acr-saldo-cap="${encodeURIComponent(String(item.saldo_capital ?? ''))}"
                    data-acr-adeudo="${encodeURIComponent(String(item.adeudo_total ?? ''))}">
                    <i class="fa-solid fa-warehouse me-1"></i>Recepción en almacén
                </button>
            </div>
        </div>`;
    }

    function acrRenderCardDictaminado(item) {
        const estPipeline = item.estatus ? acrEsc(item.estatus) : '—';
        const dictTxt = item.dictamen
            ? acrEsc(item.dictamen)
            : '<span class="text-muted fst-italic">—</span>';
        const fechaD = item.fecha_dictamen
            ? acrEsc(item.fecha_dictamen)
            : '<span class="text-muted fst-italic">—</span>';
        const g = item.gestor_nombre
            ? acrEsc(item.gestor_nombre)
            : '<span class="text-muted fst-italic">Sin asignar</span>';

        return `
        <div class="ac-card acr-card-dict">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${acrEsc(String(item.id_credito))}
                    <small>${acrEsc(item.nombre_cliente || '')}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">${acrEsc(item.folio || '—')}</span>
            </div>
            <div class="ac-card-body">
                <div class="ac-detail-row">
                    <span class="ac-lbl">Estatus pipeline</span>
                    <span class="ac-val"><span class="badge bg-secondary">${estPipeline}</span></span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Dictamen</span>
                    <span class="ac-val">${dictTxt}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Gestor a cargo</span>
                    <span class="ac-val">${g}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Fecha dictamen</span>
                    <span class="ac-val">${fechaD}</span>
                </div>
                ${item.comentarios ? `
                <div class="ac-detail-row" style="align-items:flex-start;">
                    <span class="ac-lbl">Comentarios</span>
                    <span class="ac-val" style="white-space:pre-line;">${acrEsc(item.comentarios)}</span>
                </div>` : ''}
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

    function acrCargarSeccion(key, forzar) {
        const cfg = ACR_CONFIG[key];
        if (!cfg) return;

        const suf      = key === 'bandeja' ? 'bandeja' : 'dictaminado';
        const loaderId = 'acr-loader-' + suf;
        const listaId  = 'acr-lista-'  + suf;

        if (!forzar && _acrCargada[key]) {
            return;
        }

        const loader = document.getElementById(loaderId);
        const lista  = document.getElementById(listaId);
        if (!loader || !lista) return;

        const primeraCarga = lista.children.length === 0;
        if (primeraCarga) {
            loader.style.display = 'block';
        } else {
            lista.classList.add('acr-lista-updating');
        }

        fetch(cfg.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
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

    function acrFmtMoney(v) {
        const n = parseFloat(String(v).replace(/,/g, ''));
        if (!isFinite(n)) {
            return '—';
        }
        return 'Q ' + n.toLocaleString('es-GT', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    var _acrRcptEvUploaded = [];
    var _acrRcptArrived = false;

    function acrRcptUpdateEvCount() {
        var total = _acrRcptEvUploaded.filter(Boolean).length;
        var req = _acrRcptEvUploaded.slice(0, 6).filter(Boolean).length;
        var elT = document.getElementById('acr-rcpt-evCount');
        var elR = document.getElementById('acr-rcpt-evReq');
        if (elT) elT.textContent = String(total);
        if (elR) elR.textContent = String(req);
    }

    function acrRcptBuildEvGrid() {
        var host = document.getElementById('acr-rcpt-evGrid');
        if (!host) return;
        var rows = [
            ['Vista frontal', true, false],
            ['Vista trasera', true, false],
            ['Lado izquierdo', true, false],
            ['Lado derecho', true, false],
            ['Tablero / Odómetro', true, false],
            ['Número de serie (VIN)', true, false],
            ['Daños visibles', false, false],
            ['Video general (360°)', false, true],
        ];
        host.innerHTML = rows.map(function (row, i) {
            var badge = row[1]
                ? '<span class="badge bg-danger bg-opacity-75" style="font-size:9px;">REQ</span>'
                : '<span class="badge bg-secondary" style="font-size:9px;">OPC</span>';
            var accept = row[2] ? 'video/*' : 'image/*';
            var icon = row[2] ? 'fa-video' : 'fa-image';
            return '<div class="acr-rcpt-ev-cell" id="acr-rcpt-ev-' + i + '" data-idx="' + i + '">' +
                '<input type="file" id="acr-rcpt-fi-' + i + '" accept="' + accept + '" />' +
                '<i class="fa-solid ' + icon + ' text-primary opacity-50"></i>' +
                '<span class="acr-rcpt-ev-label">' + acrEsc(row[0]) + '</span>' + badge +
                '<span class="acr-rcpt-ev-check"><i class="fa-solid fa-check"></i></span></div>';
        }).join('');
        _acrRcptEvUploaded = rows.map(function () { return false; });
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
                if (inp.files && inp.files[0]) {
                    _acrRcptEvUploaded[ii] = true;
                    var c = document.getElementById('acr-rcpt-ev-' + ii);
                    if (c) c.classList.add('uploaded');
                    acrRcptUpdateEvCount();
                }
            });
        });
        acrRcptUpdateEvCount();
    }

    function acrRcptDacionUpdate() {
        var v = (document.getElementById('acr-rcpt-dacionStatus') || {}).value || '';
        var body = document.getElementById('acr-rcpt-dacionBody');
        var noDoc = document.getElementById('acr-rcpt-noDocSection');
        if (!body) return;
        if (v === 'received') {
            body.innerHTML = '<p class="text-success small fw-semibold mb-2"><i class="fa-solid fa-check me-1"></i>Contrato de Dación recibido con firma del cliente.</p>' +
                '<div class="alert alert-success py-2 small mb-0">Documento recibido. Adjuntar escaneo al expediente digital antes de cerrar.</div>';
            if (noDoc) noDoc.style.display = 'none';
        } else if (v === 'pending') {
            body.innerHTML = '<p class="text-warning small fw-semibold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>El cliente aún no ha firmado el Contrato de Dación.</p>' +
                '<div class="alert alert-warning py-2 small mb-0">Coordinar con legal para la firma antes del cierre definitivo.</div>';
            if (noDoc) noDoc.style.display = 'none';
        } else if (v === 'missing') {
            body.innerHTML = '<p class="text-danger small fw-semibold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>El Contrato de Dación NO será recibido en este proceso.</p>' +
                '<div class="alert alert-danger py-2 small mb-0">Documentar la razón y notificar a legal.</div>';
            if (noDoc) noDoc.style.display = 'block';
        } else {
            body.innerHTML = '<p class="text-muted small mb-0">Seleccione el estado del documento.</p>';
            if (noDoc) noDoc.style.display = 'none';
        }
    }

    function acrRcptTarjetaUpdate() {
        var v = (document.getElementById('acr-rcpt-tarjetaStatus') || {}).value || '';
        var body = document.getElementById('acr-rcpt-tarjetaBody');
        if (!body) return;
        if (v === 'received') {
            body.innerHTML = '<p class="text-success small fw-semibold mb-0"><i class="fa-solid fa-check me-1"></i>Tarjeta de circulación recibida.</p>';
        } else if (v === 'missing') {
            body.innerHTML = '<p class="text-warning small fw-semibold mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i>No se recibe la tarjeta. Documentar en observaciones.</p>';
        } else {
            body.innerHTML = '<p class="text-muted small mb-0">Seleccione el estado del documento.</p>';
        }
    }

    function acrRcptResetVistaDemo() {
        _acrRcptArrived = false;
        var btn = document.getElementById('acr-rcpt-arrivalBtn');
        var ts = document.getElementById('acr-rcpt-tsDisplay');
        if (btn) {
            btn.classList.remove('arrived');
            btn.style.display = '';
            btn.innerHTML = '<i class="fa-regular fa-clock"></i> Registrar Llegada a Almacén';
        }
        if (ts) ts.classList.remove('show');
        var ds = document.getElementById('acr-rcpt-dacionStatus');
        var tsS = document.getElementById('acr-rcpt-tarjetaStatus');
        if (ds) ds.value = '';
        if (tsS) tsS.value = '';
        acrRcptDacionUpdate();
        acrRcptTarjetaUpdate();
        var nd = document.getElementById('acr-rcpt-noDocSection');
        if (nd) nd.style.display = 'none';
        var fin = document.getElementById('acr-rcpt-finBtn');
        var sm = document.getElementById('acr-rcpt-successMsg');
        if (fin) fin.style.display = '';
        if (sm) sm.style.display = 'none';
        acrRcptBuildEvGrid();
    }

    function acrRcptMostrarVista(btn) {
        var wrap = document.getElementById('acr-wrap-principal');
        var vista = document.getElementById('acr-vista-almacen');
        if (!wrap || !vista) return;
        var nombre = '';
        var folio = '';
        var gestor = '';
        var saldo = '';
        var adeudo = '';
        try {
            nombre = decodeURIComponent(btn.getAttribute('data-acr-nombre') || '');
            folio = decodeURIComponent(btn.getAttribute('data-acr-folio') || '');
            gestor = decodeURIComponent(btn.getAttribute('data-acr-gestor') || '');
            saldo = decodeURIComponent(btn.getAttribute('data-acr-saldo-cap') || '');
            adeudo = decodeURIComponent(btn.getAttribute('data-acr-adeudo') || '');
        } catch (e1) { /* ignore */ }
        var idCred = btn.getAttribute('data-acr-id-credito') || '';
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
        if (elM) elM.textContent = 'Datos de moto en expediente';
        if (elP) elP.textContent = '—';
        var sc = document.getElementById('acr-rcpt-saldo-cap');
        var ad = document.getElementById('acr-rcpt-adeudo');
        if (sc) sc.textContent = acrFmtMoney(saldo);
        if (ad) ad.textContent = acrFmtMoney(adeudo);
        acrRcptResetVistaDemo();
        wrap.style.display = 'none';
        vista.style.display = 'block';
        vista.setAttribute('aria-hidden', 'false');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function acrRcptOcultarVista() {
        var wrap = document.getElementById('acr-wrap-principal');
        var vista = document.getElementById('acr-vista-almacen');
        if (wrap) wrap.style.display = '';
        if (vista) {
            vista.style.display = 'none';
            vista.setAttribute('aria-hidden', 'true');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        acrCargarSeccion('bandeja', true);

        var bb = document.getElementById('acr-tab-bandeja-btn');
        var bd = document.getElementById('acr-tab-dictaminado-btn');
        if (bb) {
            bb.addEventListener('shown.bs.tab', function () { acrCargarSeccion('bandeja', false); });
        }
        if (bd) {
            bd.addEventListener('shown.bs.tab', function () { acrCargarSeccion('dictaminado', false); });
        }

        var btnVol = document.getElementById('acr-btn-volver-almacen');
        if (btnVol) btnVol.addEventListener('click', acrRcptOcultarVista);

        var listaB = document.getElementById('acr-lista-bandeja');
        if (listaB) {
            listaB.addEventListener('click', function (ev) {
                var b = ev.target.closest('.acr-abrir-almacen');
                if (!b) return;
                acrRcptMostrarVista(b);
            });
        }

        var arrBtn = document.getElementById('acr-rcpt-arrivalBtn');
        if (arrBtn) {
            arrBtn.addEventListener('click', function () {
                if (_acrRcptArrived) return;
                _acrRcptArrived = true;
                arrBtn.classList.add('arrived');
                arrBtn.innerHTML = '<i class="fa-solid fa-check"></i> Llegada registrada';
                var now = new Date();
                var ts = now.toLocaleDateString('es-GT', { day: '2-digit', month: 'long', year: 'numeric' }) +
                    ' — ' + now.toLocaleTimeString('es-GT', { hour: '2-digit', minute: '2-digit' });
                var tx = document.getElementById('acr-rcpt-tsText');
                var dsp = document.getElementById('acr-rcpt-tsDisplay');
                if (tx) tx.textContent = 'Ingresó: ' + ts + ' | Almacén (registro local)';
                if (dsp) dsp.classList.add('show');
            });
        }

        var ds = document.getElementById('acr-rcpt-dacionStatus');
        var tsSt = document.getElementById('acr-rcpt-tarjetaStatus');
        if (ds) ds.addEventListener('change', acrRcptDacionUpdate);
        if (tsSt) tsSt.addEventListener('change', acrRcptTarjetaUpdate);

        var finB = document.getElementById('acr-rcpt-finBtn');
        var sucM = document.getElementById('acr-rcpt-successMsg');
        if (finB && sucM) {
            finB.addEventListener('click', function () {
                finB.style.display = 'none';
                sucM.style.display = 'block';
            });
        }
    });
})();
</script>
