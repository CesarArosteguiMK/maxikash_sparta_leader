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
    @media (max-width: 992px) {
        .avr-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 576px) {
        .avr-kpi-grid,
        .avr-check-grid {
            grid-template-columns: 1fr;
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
                <div class="text-muted small">Entrada fisica de unidades con evidencias y codigo validados.</div>
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
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="avr-form-recepcion">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar recepcion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_unidad" id="avr-id-unidad">
                <div class="avr-modal-summary mb-3" id="avr-modal-summary"></div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold" for="avr-modal-ubicacion">Ubicacion de recepcion</label>
                        <select class="form-select" name="id_ubicacion" id="avr-modal-ubicacion" required>
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold" for="avr-vin">VIN/NIV</label>
                        <input type="text" class="form-control" name="vin" id="avr-vin" maxlength="17" autocomplete="off" required>
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
                    <td>${statusHtml(row.estatus_inventario)}</td>
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
                    ? `${creadas} unidad(es) nueva(s) pendientes de evidencias.`
                    : 'Sin recolectadas nuevas por migrar.';
            }
            if (!silent && creadas > 0) {
                notify('success', 'Sincronizacion lista', `${creadas} unidad(es) migrada(s) desde Tracking.`);
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
        $('avr-vin').value = row.vin || '';
        $('avr-no-motor').value = row.no_motor || '';
        $('avr-placas').value = row.placas || '';
        $('avr-kilometraje').value = row.kilometraje || '';
        $('avr-modal-ubicacion').value = row.id_ubicacion_actual || '';

        const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
        const ruta = row.tracking_id_ruta ? ('Ruta #' + row.tracking_id_ruta) : '';
        const cedis = row.tracking_cedis_destino_nombre || '';
        $('avr-modal-summary').innerHTML = `
            <div class="avr-unit-main">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
            <div class="text-muted small">${esc(moto || 'Sin datos de moto')}</div>
            <div class="avr-unit-sub mt-1">${esc([ruta, cedis, row.tracking_fecha_finalizacion_fmt].filter(Boolean).join(' | '))}</div>
        `;

        const modalEl = $('avr-modal-recepcion');
        if (window.bootstrap && window.bootstrap.Modal && modalEl) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    async function confirmarRecepcion(ev) {
        ev.preventDefault();
        const form = $('avr-form-recepcion');
        const btn = $('avr-btn-confirmar');
        if (!form || !btn) return;
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
