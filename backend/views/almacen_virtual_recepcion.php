<link rel="stylesheet" href="/assets/css/almacen-virtual-dark.css?v=20260716">

<style>
    .avr-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .avr-head {
        border: 1px solid #dbe4ef;
        background: #f8fafc;
        border-radius: .5rem;
        padding: 1rem 1.15rem;
    }
    .avr-head-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: .5rem;
        background: #dcfce7;
        color: #15803d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
    }
    .avr-sync-status {
        color: #64748b;
        font-size: .74rem;
        min-height: 1rem;
        margin-top: .25rem;
    }
    .avr-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .avr-kpi {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        padding: .85rem;
        min-height: 5.3rem;
    }
    .avr-kpi-label {
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .avr-kpi-value {
        color: #1e293b;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
        margin-top: .35rem;
    }
    .avr-toolbar {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        padding: .85rem;
    }
    .avr-table-wrap {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        overflow: hidden;
    }
    .avr-table {
        margin-bottom: 0;
    }
    .avr-table th {
        white-space: nowrap;
    }
    .avr-table td {
        vertical-align: middle;
    }
    .avr-datatable-controls {
        padding: .85rem 1rem .35rem;
        background: #fff;
    }
    .avr-datatable-controls .dataTables_length label {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        color: #697a8d;
        font-size: .875rem;
        margin: 0;
    }
    .avr-datatable-controls .dataTables_length select {
        width: auto;
        min-width: 4.75rem;
    }
    .avr-datatable-footer {
        border-top: 1px solid #d9dee3;
        padding: .75rem 1rem;
        background: #fff;
    }
    .avr-datatable-footer .dataTables_info {
        color: #697a8d;
        font-size: .875rem;
        padding-top: .45rem;
    }
    .avr-datatable-footer .pagination {
        justify-content: flex-end;
        margin: 0;
        gap: .25rem;
    }
    .avr-datatable-footer .page-link {
        min-width: 2rem;
        text-align: center;
        border-radius: .375rem;
    }
    .avr-unit-main {
        color: #1e293b;
        font-weight: 800;
    }
    .avr-unit-sub {
        color: #64748b;
        font-size: .74rem;
    }
    .avr-status {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border-radius: 999px;
        padding: .24rem .58rem;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .avr-status-pendiente_recepcion { background: #fef3c7; color: #92400e; }
    .avr-status-pendiente_entrega_cedis { background: #dbeafe; color: #1d4ed8; }
    .avr-status-en_recepcion { background: #dbeafe; color: #1d4ed8; }
    .avr-status-incidencia_recepcion { background: #fee2e2; color: #991b1b; }
    .avr-status-pendiente_revision { background: #f3e8ff; color: #7e22ce; }
    .avr-status-recolectada,
    .avr-status-recolectado,
    .avr-status-completado,
    .avr-status-completada { background: #dcfce7; color: #15803d; }
    .avr-status-default { background: #eef2ff; color: #3730a3; }
    .avr-empty {
        padding: 2.25rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .avr-empty i {
        display: block;
        font-size: 2rem;
        opacity: .35;
        margin-bottom: .65rem;
    }
    .avr-modal-summary {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: .5rem;
        padding: .85rem;
    }
    .avr-check-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .5rem .85rem;
    }
    .avr-review-panel {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: .5rem;
        padding: .85rem;
        height: 100%;
    }
    .avr-review-title {
        color: #1e293b;
        font-size: .82rem;
        font-weight: 800;
        margin-bottom: .55rem;
    }
    .avr-cedis-lock {
        border: 1px dashed #cbd5e1;
        background: #fff;
        border-radius: .45rem;
        padding: .7rem;
        margin-bottom: .85rem;
    }
    .avr-formulario-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .45rem;
        margin-bottom: .85rem;
    }
    .avr-formulario-item {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .45rem;
        padding: .55rem;
        min-height: 3.75rem;
    }
    .avr-formulario-item.is-wide {
        grid-column: 1 / -1;
    }
    .avr-formulario-item .label {
        color: #64748b;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .avr-formulario-item .value {
        color: #1e293b;
        font-size: .78rem;
        font-weight: 700;
        overflow-wrap: anywhere;
        margin-top: .15rem;
    }
    .avr-cedis-lock .label {
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .avr-cedis-lock .value {
        color: #0f172a;
        font-weight: 800;
    }
    .avr-otp-panel {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        border-radius: .5rem;
        padding: .85rem;
    }
    .avr-otp-panel.is-empty {
        border-color: #e2e8f0;
        background: #f8fafc;
    }
    .avr-otp-label {
        color: #1e40af;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .avr-otp-code {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .avr-otp-meta {
        color: #475569;
        font-size: .75rem;
        font-weight: 700;
    }
    .avr-otp-chip {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: .68rem;
        font-weight: 800;
        padding: .18rem .46rem;
        margin-top: .35rem;
    }
    .avr-evidence-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
    }
    .avr-evidence-cell {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .45rem;
        overflow: hidden;
        min-height: 8rem;
    }
    .avr-evidence-cell img {
        width: 100%;
        height: 5.75rem;
        object-fit: cover;
        display: block;
        background: #e2e8f0;
    }
    .avr-evidence-cell .meta {
        padding: .45rem .5rem;
        color: #475569;
        font-size: .72rem;
        font-weight: 700;
    }
    .avr-evidence-empty {
        border: 1px dashed #cbd5e1;
        background: #fff;
        border-radius: .45rem;
        min-height: 8rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: .75rem;
        font-size: .78rem;
    }
    .avr-doc-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: .6rem;
    }
    @media (max-width: 992px) {
        .avr-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 576px) {
        .avr-kpi-grid,
        .avr-check-grid,
        .avr-evidence-grid,
        .avr-formulario-grid {
            grid-template-columns: 1fr;
        }
        .avr-formulario-item.is-wide {
            grid-column: auto;
        }
        .avr-head {
            padding: .9rem;
        }
        .avr-datatable-controls .dataTables_length label {
            width: 100%;
            justify-content: space-between;
        }
        .avr-datatable-footer .dataTables_info,
        .avr-datatable-footer .pagination {
            justify-content: center;
            text-align: center;
        }
    }
</style>

<div class="avr-shell">
    <section class="avr-head d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-start gap-3">
            <span class="avr-head-icon"><i class="fa-solid fa-clipboard-check"></i></span>
            <div>
                <h4 class="mb-1">Recepcion de Almacen</h4>
                <div class="text-muted small">Revision de evidencias recibidas desde MotoTrack, codigo de ingreso y pase a revision mecanica.</div>
                <div class="avr-sync-status" id="avr-sync-status"></div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/MotosAdjudicadas/almacenVirtual" class="btn btn-label-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Almacen Virtual
            </a>
            <a href="/MotosAdjudicadas/inventario" class="btn btn-label-primary btn-sm">
                <i class="fa-solid fa-boxes-stacked me-1"></i>Inventario
            </a>
            <a href="/MotosAdjudicadas/revisionMecanica" class="btn btn-label-danger btn-sm">
                <i class="fa-solid fa-screwdriver-wrench me-1"></i>Revision
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="avr-btn-refresh">
                <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
            </button>
        </div>
    </section>

    <section class="avr-kpi-grid">
        <div class="avr-kpi">
            <div class="avr-kpi-label">Pendientes</div>
            <div class="avr-kpi-value" id="avr-kpi-pendientes">0</div>
        </div>
        <div class="avr-kpi">
            <div class="avr-kpi-label">En recepcion</div>
            <div class="avr-kpi-value" id="avr-kpi-en-recepcion">0</div>
        </div>
        <div class="avr-kpi">
            <div class="avr-kpi-label">Incidencias</div>
            <div class="avr-kpi-value" id="avr-kpi-incidencias">0</div>
        </div>
        <div class="avr-kpi">
            <div class="avr-kpi-label">Recibidas</div>
            <div class="avr-kpi-value" id="avr-kpi-recibidas">0</div>
        </div>
    </section>

    <section class="avr-toolbar">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-3">
                <label class="form-label small fw-bold" for="avr-q">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control" id="avr-q" placeholder="Folio, VIN, motor, placa">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="avr-celula">Celula</label>
                <select class="form-select form-select-sm" id="avr-celula">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="avr-estatus">Estatus</label>
                <select class="form-select form-select-sm" id="avr-estatus">
                    <option value="abiertas" selected>Abiertas</option>
                    <option value="pendiente_recepcion">Pendiente recepcion</option>
                    <option value="en_recepcion">En recepcion</option>
                    <option value="incidencia_recepcion">Incidencia recepcion</option>
                    <option value="pendiente_revision">Recibidas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-bold" for="avr-ubicacion">Ubicacion</label>
                <select class="form-select form-select-sm" id="avr-ubicacion">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2 d-grid">
                <button type="button" class="btn btn-primary btn-sm" id="avr-btn-filtrar">
                    <i class="fa-solid fa-filter me-1"></i>Filtrar
                </button>
            </div>
        </div>
    </section>

    <section class="avr-table-wrap">
        <div class="avr-datatable-controls row mx-0 align-items-center">
            <div class="col-sm-12 col-md-6">
                <div class="dataTables_length">
                    <label>
                        Mostrar
                        <select id="avr-limit" class="form-select form-select-sm">
                            <option value="8" selected>8</option>
                            <option value="16">16</option>
                            <option value="32">32</option>
                            <option value="64">64</option>
                        </select>
                        registros
                    </label>
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-responsive table border-top avr-table">
                <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Celula</th>
                        <th>Identificacion</th>
                        <th>Recoleccion</th>
                        <th>Ubicacion</th>
                        <th>Estatus</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody id="avr-unidades-body">
                    <tr>
                        <td colspan="7" class="avr-empty">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            Cargando unidades...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="avr-datatable-footer row mx-0 align-items-center">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="avr-pager-info">Mostrando 0 a 0 de 0 unidades</div>
            </div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    <ul class="pagination" id="avr-pagination"></ul>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="avr-modal-recepcion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form class="modal-content" id="avr-form-recepcion" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Validar recepcion y codigo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_unidad" id="avr-id-unidad">
                <div class="avr-modal-summary mb-3" id="avr-modal-summary"></div>
                <div class="avr-otp-panel mb-3 is-empty" id="avr-otp-panel"></div>
                <div class="row g-3">
                    <div class="col-12 col-lg-7">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold" for="avr-modal-ubicacion">Ubicacion de recepcion</label>
                                <select class="form-select" name="id_ubicacion" id="avr-modal-ubicacion">
                                    <option value="">Seleccionar</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold" for="avr-codigo-verificacion">Codigo de ingreso</label>
                                <input type="text" class="form-control text-uppercase" name="codigo_verificacion" id="avr-codigo-verificacion" maxlength="24" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold" for="avr-vin">VIN/NIV</label>
                                <input type="text" class="form-control" name="vin" id="avr-vin" maxlength="17" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold" for="avr-no-motor">No. motor</label>
                                <input type="text" class="form-control" name="no_motor" id="avr-no-motor" maxlength="24" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold" for="avr-placas">Placas</label>
                                <input type="text" class="form-control" name="placas" id="avr-placas" maxlength="20" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold" for="avr-kilometraje">Kilometraje</label>
                                <input type="number" class="form-control" name="kilometraje" id="avr-kilometraje" min="0" step="1">
                            </div>
                            <div class="col-12">
                                <div class="avr-check-grid">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="avr-vin-coincide" name="vin_coincide">
                                        <label class="form-check-label" for="avr-vin-coincide">VIN coincide con origen</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="avr-evidencia-4" name="evidencia_4_angulos">
                                        <label class="form-check-label" for="avr-evidencia-4">4 angulos revisados</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="avr-evidencia-vin" name="evidencia_vin">
                                        <label class="form-check-label" for="avr-evidencia-vin">Evidencia VIN revisada</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="avr-documentos" name="documentos_completos">
                                        <label class="form-check-label" for="avr-documentos">Documentos completos</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="avr-arranque" name="arranque_motor">
                                        <label class="form-check-label" for="avr-arranque">Motor arranca</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="avr-danos" name="sin_danos_mayores">
                                        <label class="form-check-label" for="avr-danos">Sin danos mayores visibles</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold" for="avr-observaciones">Observaciones</label>
                                <textarea class="form-control" name="observaciones" id="avr-observaciones" rows="3" maxlength="1000"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="avr-review-title mb-2">Formulario MotoTrack</div>
                                <div class="avr-formulario-grid" id="avr-formulario-mototrack">
                                    <div class="avr-evidence-empty">Cargando formulario...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <aside class="avr-review-panel">
                            <div class="avr-cedis-lock">
                                <div class="label">CEDIS asignado</div>
                                <div class="value" id="avr-cedis-asignado">Sin CEDIS</div>
                            </div>
                            <div class="avr-review-title">Evidencias fotograficas</div>
                            <div class="avr-evidence-grid mb-3" id="avr-evidencias-grid">
                                <div class="avr-evidence-empty">Cargando evidencias...</div>
                            </div>
                            <div class="avr-review-title">Documentos requeridos</div>
                            <div class="avr-doc-grid">
                                <div>
                                    <label class="form-label small fw-bold" for="rec_doc_factura_moto">Factura de la moto</label>
                                    <input type="file" class="form-control" name="rec_doc_factura_moto" id="rec_doc_factura_moto" accept=".pdf,image/*">
                                </div>
                                <div>
                                    <label class="form-label small fw-bold" for="rec_doc_tarjeta_circulacion">Tarjeta de circulacion</label>
                                    <input type="file" class="form-control" name="rec_doc_tarjeta_circulacion" id="rec_doc_tarjeta_circulacion" accept=".pdf,image/*">
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="avr-btn-confirmar">
                    <i class="fa-solid fa-check me-1"></i>Confirmar recepcion
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const state = {
        page: 1,
        limit: 8,
        pages: 1,
        total: 0,
        timer: null,
        rows: new Map(),
        ubicaciones: [],
    };

    const $ = (id) => document.getElementById(id);

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function notify(icon, title, text) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ icon, title, text });
            return;
        }
        window.alert((title ? title + '\n' : '') + (text || ''));
    }

    function statusLabel(value) {
        const map = {
            pendiente_recepcion: 'Pendiente recepcion',
            en_recepcion: 'En recepcion',
            incidencia_recepcion: 'Incidencia recepcion',
            pendiente_revision: 'Recibida',
            recolectada: 'Recolectada',
            recolectado: 'Recolectada',
            completado: 'Recolectada',
            completada: 'Recolectada',
        };
        return map[value] || value || 'Sin estatus';
    }

    function statusHtml(value) {
        const key = String(value || 'default').replace(/[^a-z0-9_]/gi, '_');
        const safe = ['pendiente_recepcion','en_recepcion','incidencia_recepcion','pendiente_revision','recolectada','recolectado','completado','completada'].includes(key)
            ? 'avr-status-' + key
            : 'avr-status-default';
        return '<span class="avr-status ' + safe + '"><i class="fa-solid fa-circle"></i>' + esc(statusLabel(value)) + '</span>';
    }

    function otpLabel(row) {
        const codigo = String(row?.otp_codigo || '').trim();
        if (!codigo) return '';
        return '<div class="avr-otp-chip"><i class="fa-solid fa-key"></i>OTP ' + esc(codigo) + '</div>';
    }

    function renderOtpPanel(row) {
        const target = $('avr-otp-panel');
        if (!target) return;
        const codigo = String(row?.otp_codigo || '').trim();
        if (!codigo) {
            target.classList.add('is-empty');
            target.innerHTML = `
                <div class="d-flex align-items-center gap-2 text-muted">
                    <i class="fa-solid fa-key"></i>
                    <div>
                        <div class="fw-bold small">Sin OTP activo</div>
                        <div class="small">El transportista debe generarlo desde MotoTrack.</div>
                    </div>
                </div>
            `;
            return;
        }

        target.classList.remove('is-empty');
        const intentos = Number.isFinite(Number(row.otp_intentos)) ? Number(row.otp_intentos) : 0;
        const maxIntentos = Number.isFinite(Number(row.otp_max_intentos)) ? Number(row.otp_max_intentos) : 3;
        const expira = row.otp_expiracion_fmt ? 'Expira ' + row.otp_expiracion_fmt : 'Sin expiracion registrada';
        target.innerHTML = `
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div>
                    <div class="avr-otp-label">OTP entrega almacen</div>
                    <div class="avr-otp-code">${esc(codigo)}</div>
                    <div class="avr-otp-meta">${esc(expira)} | Intentos ${esc(intentos)} / ${esc(maxIntentos)}</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-label-primary btn-sm" data-action="usar-otp" data-code="${esc(codigo)}">
                        <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>Usar
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-action="copiar-otp" data-code="${esc(codigo)}">
                        <i class="fa-regular fa-copy me-1"></i>Copiar
                    </button>
                </div>
            </div>
        `;
    }

    const evidenciaLabels = {
        foto_tacometro: 'Tacometro',
        foto_frontal: 'Frontal',
        foto_lateral_derecha: 'Lateral derecha',
        foto_trasera: 'Trasera',
        foto_lateral_izquierda: 'Lateral izquierda',
        foto_vin: 'VIN/NIV',
        video_360_encendida: 'Video 360 encendida',
        foto_checklist: 'Checklist',
    };

    const formularioMotoTrackCampos = [
        ['tiene_llave_fisica', 'Llave fisica'],
        ['tiene_tarjeta_circulacion', 'Tarjeta circulacion'],
        ['tiene_placa_fisica', 'Placa fisica'],
        ['tipo_unidad', 'Tipo de moto'],
        ['categoria', 'Categoria'],
        ['tipo_motor', 'Tipo motor'],
        ['tipo_motor_combustion', 'Combustion'],
        ['cilindraje', 'Cilindraje'],
        ['potencia', 'Potencia'],
        ['otro_descripcion', 'Otro'],
        ['comentarios_generales', 'Comentarios generales', true],
    ];

    const formularioMotoTrackLabels = {
        si: 'Si',
        no: 'No',
        '2_ruedas': '2 ruedas',
        '3_ruedas': '3 ruedas',
        cuatrimoto: 'Cuatrimoto',
        combustion: 'Combustion',
        electrica: 'Electrica',
        carburador: 'Carburador',
        full_inyeccion: 'Full inyeccion',
        doble_proposito: 'Doble proposito',
        cross_enduro: 'Cross/Enduro',
        naked: 'Naked',
        deportivas: 'Deportivas',
        custom: 'Custom',
        scrambler: 'Scrambler',
        scooter: 'Scooter',
        touring: 'Touring',
        otro: 'Otro',
    };

    function prettyFormularioValue(value) {
        if (value === null || value === undefined || value === '') return 'Sin capturar';
        const raw = String(value);
        if (formularioMotoTrackLabels[raw]) return formularioMotoTrackLabels[raw];
        return raw.replace(/^otro:\s*/i, 'Otro: ').replace(/_/g, ' ').replace(/\bcc\b/gi, 'CC').replace(/\bkw\b/gi, 'KW');
    }

    function parsePayloadJson(value) {
        if (!value || typeof value !== 'string') return null;
        try {
            return JSON.parse(value);
        } catch (e) {
            return null;
        }
    }

    function formularioMotoTrackData(unidad, bitacora) {
        const data = {};
        (bitacora || []).forEach((row) => {
            const payload = parsePayloadJson(row.payload_json);
            if (!payload || typeof payload !== 'object') return;
            const form = payload.formulario || payload.formulario_mototrack || payload.datos_formulario || payload.evidencias_formulario;
            if (!form || typeof form !== 'object') return;
            formularioMotoTrackCampos.forEach(([key]) => {
                if ((data[key] === undefined || data[key] === null || data[key] === '') && form[key] !== undefined) {
                    data[key] = form[key];
                }
            });
        });
        formularioMotoTrackCampos.forEach(([key]) => {
            if (unidad && unidad[key] !== undefined && unidad[key] !== null && unidad[key] !== '') {
                data[key] = unidad[key];
            }
        });
        return data;
    }

    function formularioMotoTrackCamposVisibles(data) {
        const hasValue = (key) => data[key] !== undefined && data[key] !== null && data[key] !== '';
        const tipoMotor = String(data.tipo_motor || '').toLowerCase();
        const keys = [
            'tiene_llave_fisica',
            'tiene_tarjeta_circulacion',
            'tiene_placa_fisica',
            'tipo_unidad',
            'categoria',
            'tipo_motor',
        ];

        if (tipoMotor === 'electrica') {
            keys.push('potencia');
        } else if (tipoMotor === 'combustion') {
            keys.push('tipo_motor_combustion', 'cilindraje');
        } else {
            ['tipo_motor_combustion', 'cilindraje', 'potencia'].forEach((key) => {
                if (hasValue(key)) keys.push(key);
            });
        }
        if (hasValue('otro_descripcion')) keys.push('otro_descripcion');
        keys.push('comentarios_generales');

        return formularioMotoTrackCampos.filter(([key]) => keys.includes(key));
    }

    function renderFormularioMotoTrack(unidad, bitacora) {
        const target = $('avr-formulario-mototrack');
        if (!target) return;
        const data = formularioMotoTrackData(unidad || {}, bitacora || []);
        const hasData = formularioMotoTrackCampos.some(([key]) => data[key] !== undefined && data[key] !== null && data[key] !== '');
        if (!hasData) {
            target.innerHTML = '<div class="avr-evidence-empty">Sin formulario MotoTrack capturado.</div>';
            return;
        }

        target.innerHTML = formularioMotoTrackCamposVisibles(data).map(([key, label, wide]) => `
            <div class="avr-formulario-item${wide ? ' is-wide' : ''}">
                <div class="label">${esc(label)}</div>
                <div class="value">${esc(prettyFormularioValue(data[key]))}</div>
            </div>
        `).join('');
    }

    function renderEvidenciasPanel(evidencias) {
        const grid = $('avr-evidencias-grid');
        if (!grid) return;
        const bySlot = new Map();
        (evidencias || []).forEach((ev) => bySlot.set(String(ev.slot || ''), ev));
        grid.innerHTML = Object.keys(evidenciaLabels).map((slot) => {
            const ev = bySlot.get(slot);
            if (!ev || !ev.url_publica) {
                return '<div class="avr-evidence-empty">' + esc(evidenciaLabels[slot]) + '<br>Sin evidencia</div>';
            }
            const tipo = String(ev.tipo_evidencia || 'foto');
            const isImage = tipo === 'foto' || /\.(jpg|jpeg|png|webp|gif|heic|heif)(\?|$)/i.test(String(ev.url_publica || ''));
            if (!isImage) {
                return '<a class="avr-evidence-cell text-decoration-none" href="' + esc(ev.url_publica) + '" target="_blank" rel="noopener">' +
                    '<div class="avr-evidence-empty"><i class="fa-solid fa-file me-1"></i>Ver archivo</div>' +
                    '<div class="meta">' + esc(evidenciaLabels[slot]) + '</div>' +
                '</a>';
            }
            return '<a class="avr-evidence-cell text-decoration-none" href="' + esc(ev.url_publica) + '" target="_blank" rel="noopener">' +
                '<img src="' + esc(ev.url_publica) + '" alt="' + esc(evidenciaLabels[slot]) + '">' +
                '<div class="meta">' + esc(evidenciaLabels[slot]) + '</div>' +
            '</a>';
        }).join('');
    }

    function syncDocumentosCheck() {
        const factura = $('rec_doc_factura_moto');
        const tarjeta = $('rec_doc_tarjeta_circulacion');
        const check = $('avr-documentos');
        if (check) {
            check.checked = Boolean(factura?.files?.length && tarjeta?.files?.length);
        }
    }

    function rangeInfo(total, page, limit, label) {
        const start = total > 0 ? ((page - 1) * limit) + 1 : 0;
        const end = total > 0 ? Math.min(page * limit, total) : 0;
        return 'Mostrando ' + start + ' a ' + end + ' de ' + total + ' ' + label;
    }

    function pageNumbers(current, pages) {
        const totalPages = Math.max(1, Number(pages || 1));
        const activePage = Math.max(1, Math.min(Number(current || 1), totalPages));
        if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, index) => index + 1);
        }

        const numbers = [1];
        const start = Math.max(2, activePage - 1);
        const end = Math.min(totalPages - 1, activePage + 1);
        if (start > 2) numbers.push('ellipsis-start');
        for (let page = start; page <= end; page++) numbers.push(page);
        if (end < totalPages - 1) numbers.push('ellipsis-end');
        numbers.push(totalPages);
        return numbers;
    }

    function renderPagination() {
        const target = $('avr-pagination');
        if (!target) return;
        const current = Math.max(1, Number(state.page || 1));
        const totalPages = Math.max(1, Number(state.pages || 1));

        const item = (label, targetPage, disabled, active, extraClass) => {
            const classes = ['paginate_button', 'page-item'];
            if (extraClass) classes.push(extraClass);
            if (disabled) classes.push('disabled');
            if (active) classes.push('active');
            const attrs = disabled ? 'tabindex="-1" aria-disabled="true"' : 'href="#" data-page="' + esc(targetPage) + '"';
            return '<li class="' + classes.join(' ') + '"><a class="page-link" ' + attrs + '>' + label + '</a></li>';
        };

        target.innerHTML = [
            item('Anterior', Math.max(1, current - 1), current <= 1, false, 'previous'),
            ...pageNumbers(current, totalPages).map((value) => {
                if (typeof value === 'string') return item('...', current, true, false, '');
                return item(String(value), value, false, value === current, '');
            }),
            item('Siguiente', Math.min(totalPages, current + 1), current >= totalPages, false, 'next'),
        ].join('');
    }

    function params() {
        const p = new URLSearchParams();
        p.set('page', String(state.page));
        p.set('limit', String(state.limit));
        const q = $('avr-q')?.value.trim() || '';
        const celula = $('avr-celula')?.value || '';
        const estatus = $('avr-estatus')?.value || 'abiertas';
        const ubicacion = $('avr-ubicacion')?.value || '';
        if (q) p.set('q', q);
        if (celula) p.set('id_celula', celula);
        if (estatus) p.set('estatus', estatus);
        if (ubicacion) p.set('id_ubicacion', ubicacion);
        return p;
    }

    async function cargarResumen() {
        const res = await fetch('/MotosAdjudicadas/recepcionAlmacenResumen', { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success || !json.datos) return;
        const d = json.datos;
        $('avr-kpi-pendientes').textContent = d.pendientes || 0;
        $('avr-kpi-en-recepcion').textContent = d.en_recepcion || 0;
        $('avr-kpi-incidencias').textContent = d.incidencias || 0;
        $('avr-kpi-recibidas').textContent = d.recibidas || 0;
    }

    function llenarUbicaciones(select, includeAll) {
        if (!select) return;
        const current = select.value;
        select.innerHTML = includeAll ? '<option value="">Todas</option>' : '<option value="">Seleccionar</option>';
        state.ubicaciones.forEach((row) => {
            if (!includeAll && String(row.clave_ubicacion || '').toUpperCase() === 'SIN_ASIGNAR') {
                return;
            }
            const opt = document.createElement('option');
            opt.value = String(row.id_ubicacion);
            opt.textContent = row.nombre_ubicacion;
            select.appendChild(opt);
        });
        if (current) select.value = current;
    }

    async function cargarCatalogos() {
        const [celulasRes, ubicacionesRes] = await Promise.all([
            fetch('/MotosAdjudicadas/inventarioCelulas', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
            fetch('/MotosAdjudicadas/inventarioUbicaciones', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
        ]);

        const celulaSelect = $('avr-celula');
        if (celulaSelect && celulasRes && celulasRes.success) {
            (celulasRes.datos || []).forEach((row) => {
                const opt = document.createElement('option');
                opt.value = String(row.id_celula);
                opt.textContent = row.nombre;
                celulaSelect.appendChild(opt);
            });
        }

        state.ubicaciones = ubicacionesRes && ubicacionesRes.success ? (ubicacionesRes.datos || []) : [];
        llenarUbicaciones($('avr-ubicacion'), true);
        llenarUbicaciones($('avr-modal-ubicacion'), false);
    }

    function renderRows(rows) {
        const body = $('avr-unidades-body');
        if (!body) return;
        state.rows.clear();
        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="7" class="avr-empty"><i class="fa-solid fa-clipboard-check"></i>Sin unidades para recepcion con los filtros seleccionados.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((row) => {
            state.rows.set(String(row.id_unidad), row);
            const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
            const ids = [
                row.vin ? 'VIN ' + row.vin : '',
                row.no_motor ? 'Motor ' + row.no_motor : '',
                row.placas ? 'Placa ' + row.placas : '',
            ].filter(Boolean).join(' | ');
            const ruta = row.tracking_id_ruta ? ('Ruta #' + row.tracking_id_ruta) : '';
            const cedis = row.tracking_cedis_destino_nombre || '';
            const fechaRecoleccion = row.tracking_fecha_finalizacion_fmt || '';
            const trackingMeta = [ruta, cedis, fechaRecoleccion].filter(Boolean).join(' | ');
            const puedeRecibir = ['pendiente_recepcion', 'en_recepcion', 'incidencia_recepcion'].includes(String(row.estatus_inventario || ''));
            const accion = puedeRecibir
                ? '<button type="button" class="btn btn-success btn-sm" data-action="recibir" data-id="' + esc(row.id_unidad) + '"><i class="fa-solid fa-clipboard-check me-1"></i>Recibir</button>'
                : '<button type="button" class="btn btn-label-secondary btn-sm" disabled><i class="fa-solid fa-check me-1"></i>Recibida</button>';

            return `
                <tr>
                    <td>
                        <div class="avr-unit-main">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
                        <div class="avr-unit-sub">${esc(moto || 'Sin datos de moto')}</div>
                    </td>
                    <td>${esc(row.nombre_celula || '')}</td>
                    <td>
                        <div>${esc(ids || 'Sin identificadores')}</div>
                        ${row.id_credito ? `<div class="avr-unit-sub">Credito historico: ${esc(row.id_credito)}</div>` : ''}
                    </td>
                    <td>
                        <div>${row.tracking_estatus_recoleccion ? statusHtml(row.tracking_estatus_recoleccion) : '<span class="text-muted small">Sin tracking</span>'}</div>
                        <div class="avr-unit-sub">${esc(trackingMeta || row.tracking_nombre_ruta || '')}</div>
                    </td>
                    <td>
                        <div>${esc(row.nombre_ubicacion || 'Sin ubicacion')}</div>
                        <div class="avr-unit-sub">${esc(row.tipo_ubicacion || '')}</div>
                    </td>
                    <td>${statusHtml(row.estatus_inventario)}${otpLabel(row)}</td>
                    <td>${accion}</td>
                </tr>
            `;
        }).join('');
    }

    async function cargarUnidades() {
        const body = $('avr-unidades-body');
        if (body) {
            body.innerHTML = '<tr><td colspan="7" class="avr-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando unidades...</td></tr>';
        }
        const res = await fetch('/MotosAdjudicadas/recepcionAlmacenUnidades?' + params().toString(), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            if (body) {
                body.innerHTML = '<tr><td colspan="7" class="avr-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(json.message || 'No se pudieron cargar las unidades.') + '</td></tr>';
            }
            return;
        }
        state.total = Number(json.total || 0);
        state.pages = Number(json.pages || 1);
        state.page = Number(json.page || 1);
        state.limit = Number(json.limit || state.limit);
        renderRows(json.rows || []);
        const info = $('avr-pager-info');
        if (info) info.textContent = rangeInfo(state.total, state.page, state.limit, 'unidades');
        renderPagination();
    }

    async function sincronizarRecolectadas(silent = true) {
        const status = $('avr-sync-status');
        if (status) status.textContent = 'Sincronizando recolectadas de Tracking...';
        try {
            const res = await fetch('/MotosAdjudicadas/inventarioSincronizarRecolectadas?limit=250', {
                method: 'POST',
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            const creadas = Number(json.creadas || 0);
            if (status) {
                status.textContent = creadas > 0
                    ? `${creadas} prealta(s) esperando entrega fisica en CEDIS.`
                    : 'Sin recolectadas nuevas por migrar.';
            }
            if (!silent && creadas > 0) {
                notify('success', 'Sincronizacion lista', `${creadas} prealta(s) preparadas desde Tracking.`);
            } else if (!silent && !json.success) {
                notify('warning', 'Sin sincronizacion', json.message || 'No se pudo sincronizar Tracking.');
            }
            return json;
        } catch (err) {
            if (status) status.textContent = 'No se pudo sincronizar recolectadas automaticamente.';
            if (!silent) notify('error', 'Error de sincronizacion', err.message || 'No se pudo contactar al servidor.');
            return null;
        }
    }

    function abrirModalRecepcion(idUnidad) {
        const row = state.rows.get(String(idUnidad));
        if (!row) return;
        $('avr-form-recepcion')?.reset();
        $('avr-id-unidad').value = row.id_unidad || '';
        $('avr-codigo-verificacion').value = '';
        $('avr-vin').value = row.vin || '';
        $('avr-no-motor').value = row.no_motor || '';
        $('avr-placas').value = row.placas || '';
        $('avr-kilometraje').value = row.kilometraje || '';
        $('avr-modal-ubicacion').value = row.id_ubicacion_actual || '';
        const almacenGeneral = state.ubicaciones.find((u) => String(u.nombre_ubicacion || '').toLowerCase() === 'almacen general');
        if (almacenGeneral && (!row.id_ubicacion_actual || String(row.nombre_ubicacion || '').toLowerCase() === 'sin asignar')) {
            $('avr-modal-ubicacion').value = String(almacenGeneral.id_ubicacion);
        }
        const cedisAsignado = row.tracking_cedis_destino_nombre || row.nombre_ubicacion || 'Almacen General';
        $('avr-cedis-asignado').textContent = cedisAsignado;
        renderEvidenciasPanel([]);
        const formMotoTrack = $('avr-formulario-mototrack');
        if (formMotoTrack) {
            formMotoTrack.innerHTML = '<div class="avr-evidence-empty">Cargando formulario...</div>';
        }

        const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
        const ruta = row.tracking_id_ruta ? ('Ruta #' + row.tracking_id_ruta) : '';
        const cedis = row.tracking_cedis_destino_nombre || '';
        $('avr-modal-summary').innerHTML = `
            <div class="avr-unit-main">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
            <div class="text-muted small">${esc(moto || 'Sin datos de moto')}</div>
            <div class="avr-unit-sub mt-1">${esc([ruta, cedis, row.tracking_fecha_finalizacion_fmt].filter(Boolean).join(' | '))}</div>
        `;
        renderOtpPanel(row);

        const modalEl = $('avr-modal-recepcion');
        if (window.bootstrap && window.bootstrap.Modal && modalEl) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        fetch('/MotosAdjudicadas/inventarioFichaUnidad?id_unidad=' + encodeURIComponent(String(row.id_unidad)), {
            headers: { Accept: 'application/json' },
        })
            .then((res) => res.json())
            .then((json) => {
                if (json && json.success) {
                    renderFormularioMotoTrack(json.unidad || {}, json.bitacora || []);
                    renderEvidenciasPanel(json.evidencias || []);
                } else {
                    renderFormularioMotoTrack(null, null);
                    const grid = $('avr-evidencias-grid');
                    if (grid) grid.innerHTML = '<div class="avr-evidence-empty">No se pudieron cargar evidencias.</div>';
                }
            })
            .catch(() => {
                renderFormularioMotoTrack(null, null);
                const grid = $('avr-evidencias-grid');
                if (grid) grid.innerHTML = '<div class="avr-evidence-empty">No se pudieron cargar evidencias.</div>';
            });
    }

    async function confirmarRecepcion(ev) {
        ev.preventDefault();
        const form = $('avr-form-recepcion');
        const btn = $('avr-btn-confirmar');
        if (!form || !btn) return;
        const codigoInput = $('avr-codigo-verificacion');
        if (codigoInput) {
            codigoInput.value = String(codigoInput.value || '').trim().toUpperCase();
        }
        btn.disabled = true;
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Guardando';
        try {
            const res = await fetch('/MotosAdjudicadas/recepcionAlmacenConfirmar', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            const json = await res.json();
            if (!json.success) {
                notify('error', 'Recepcion no guardada', json.message || 'No se pudo confirmar la recepcion.');
                return;
            }
            const modalEl = $('avr-modal-recepcion');
            if (window.bootstrap && window.bootstrap.Modal && modalEl) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            notify(json.resultado === 'recibida' ? 'success' : 'warning', 'Recepcion guardada', json.message || 'Recepcion actualizada.');
            reloadAll(false);
        } catch (err) {
            notify('error', 'Error inesperado', err.message || 'No se pudo contactar al servidor.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }

    function reloadAll(resetPage) {
        if (resetPage) state.page = 1;
        cargarResumen().catch(() => {});
        cargarUnidades().catch((err) => {
            const body = $('avr-unidades-body');
            if (body) {
                body.innerHTML = '<tr><td colspan="7" class="avr-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(err.message || 'Error inesperado.') + '</td></tr>';
            }
        });
    }

    function init() {
        $('avr-btn-refresh')?.addEventListener('click', () => {
            sincronizarRecolectadas(false).finally(() => reloadAll(false));
        });
        $('avr-btn-filtrar')?.addEventListener('click', () => reloadAll(true));
        $('avr-form-recepcion')?.addEventListener('submit', confirmarRecepcion);
        $('avr-otp-panel')?.addEventListener('click', (ev) => {
            const btn = ev.target.closest('[data-action]');
            if (!btn) return;
            const code = String(btn.dataset.code || '').trim();
            if (!code) return;
            if (btn.dataset.action === 'usar-otp') {
                const input = $('avr-codigo-verificacion');
                if (input) {
                    input.value = code;
                    input.focus();
                }
                return;
            }
            if (btn.dataset.action === 'copiar-otp') {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(code).then(() => {
                        notify('success', 'OTP copiado', 'Codigo copiado al portapapeles.');
                    }).catch(() => {
                        notify('info', 'OTP activo', code);
                    });
                } else {
                    notify('info', 'OTP activo', code);
                }
            }
        });
        $('avr-limit')?.addEventListener('change', () => {
            state.limit = Number($('avr-limit')?.value || 8) || 8;
            reloadAll(true);
        });
        $('avr-pagination')?.addEventListener('click', (ev) => {
            const link = ev.target.closest('[data-page]');
            if (!link) return;
            ev.preventDefault();
            const nextPage = Number(link.dataset.page || 1);
            if (nextPage && nextPage !== state.page) {
                state.page = nextPage;
                cargarUnidades();
            }
        });
        $('avr-unidades-body')?.addEventListener('click', (ev) => {
            const btn = ev.target.closest('[data-action="recibir"]');
            if (!btn) return;
            abrirModalRecepcion(btn.dataset.id);
        });
        $('rec_doc_factura_moto')?.addEventListener('change', syncDocumentosCheck);
        $('rec_doc_tarjeta_circulacion')?.addEventListener('change', syncDocumentosCheck);
        $('avr-q')?.addEventListener('input', () => {
            window.clearTimeout(state.timer);
            state.timer = window.setTimeout(() => reloadAll(true), 350);
        });
        ['avr-celula', 'avr-estatus', 'avr-ubicacion'].forEach((id) => {
            $(id)?.addEventListener('change', () => reloadAll(true));
        });

        cargarCatalogos()
            .catch(() => {})
            .finally(() => {
                sincronizarRecolectadas(true).finally(() => reloadAll(true));
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
