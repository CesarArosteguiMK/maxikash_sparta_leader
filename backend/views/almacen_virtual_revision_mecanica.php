<?php
$checklistRevision = is_array($av_revision_checklist ?? null) ? $av_revision_checklist : [];
?>

<style>
    .avm-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .avm-head {
        border: 1px solid #dbe4ef;
        background: #f8fafc;
        border-radius: .5rem;
        padding: 1rem 1.15rem;
    }
    .avm-head-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: .5rem;
        background: #fee2e2;
        color: #b91c1c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
    }
    .avm-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .75rem;
    }
    .avm-kpi {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        padding: .85rem;
        min-height: 5.3rem;
    }
    .avm-kpi-label {
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .avm-kpi-value {
        color: #1e293b;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
        margin-top: .35rem;
    }
    .avm-toolbar,
    .avm-table-wrap,
    .avm-modal-summary,
    .avm-section {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
    }
    .avm-toolbar {
        padding: .85rem;
    }
    .avm-table-wrap {
        overflow: hidden;
    }
    .avm-table {
        margin-bottom: 0;
    }
    .avm-table th {
        white-space: nowrap;
    }
    .avm-table td {
        vertical-align: middle;
    }
    .avm-datatable-controls {
        padding: .85rem 1rem .35rem;
        background: #fff;
    }
    .avm-datatable-controls .dataTables_length label {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        color: #697a8d;
        font-size: .875rem;
        margin: 0;
    }
    .avm-datatable-controls .dataTables_length select {
        width: auto;
        min-width: 4.75rem;
    }
    .avm-datatable-footer {
        border-top: 1px solid #d9dee3;
        padding: .75rem 1rem;
        background: #fff;
    }
    .avm-datatable-footer .dataTables_info {
        color: #697a8d;
        font-size: .875rem;
        padding-top: .45rem;
    }
    .avm-datatable-footer .pagination {
        justify-content: flex-end;
        margin: 0;
        gap: .25rem;
    }
    .avm-datatable-footer .page-link {
        min-width: 2rem;
        text-align: center;
        border-radius: .375rem;
    }
    .avm-unit-main {
        color: #1e293b;
        font-weight: 800;
    }
    .avm-unit-sub {
        color: #64748b;
        font-size: .74rem;
    }
    .avm-status {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border-radius: 999px;
        padding: .24rem .58rem;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .avm-status-pendiente_revision { background: #f3e8ff; color: #7e22ce; }
    .avm-status-en_revision { background: #e0f2fe; color: #0369a1; }
    .avm-status-reparada { background: #dcfce7; color: #166534; }
    .avm-status-fuera_presupuesto { background: #fee2e2; color: #991b1b; }
    .avm-status-irreparable { background: #e5e7eb; color: #374151; }
    .avm-status-recolectada,
    .avm-status-recolectado,
    .avm-status-completado,
    .avm-status-completada { background: #dcfce7; color: #15803d; }
    .avm-status-default { background: #eef2ff; color: #3730a3; }
    .avm-empty {
        padding: 2.25rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .avm-empty i {
        display: block;
        font-size: 2rem;
        opacity: .35;
        margin-bottom: .65rem;
    }
    .avm-modal-summary {
        background: #f8fafc;
        padding: .85rem;
    }
    .avm-section {
        padding: .85rem;
    }
    .avm-section-title {
        color: #1e293b;
        font-size: .86rem;
        font-weight: 800;
        margin-bottom: .65rem;
        text-transform: uppercase;
    }
    .avm-check-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem .85rem;
    }
    .avm-check-row {
        border: 1px solid #e2e8f0;
        border-radius: .45rem;
        padding: .55rem;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: .5rem;
        align-items: center;
    }
    .avm-check-row .form-check-label {
        font-size: .82rem;
        font-weight: 700;
        color: #334155;
    }
    .avm-check-row select {
        min-width: 5.5rem;
    }
    .avm-reception-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .5rem;
    }
    .avm-reception-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .45rem;
        padding: .5rem;
        font-size: .78rem;
    }
    .avm-reception-item strong {
        display: block;
        color: #475569;
        font-size: .7rem;
        text-transform: uppercase;
    }
    @media (max-width: 1100px) {
        .avm-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .avm-check-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 576px) {
        .avm-kpi-grid,
        .avm-reception-grid {
            grid-template-columns: 1fr;
        }
        .avm-head {
            padding: .9rem;
        }
        .avm-datatable-controls .dataTables_length label {
            width: 100%;
            justify-content: space-between;
        }
        .avm-datatable-footer .dataTables_info,
        .avm-datatable-footer .pagination {
            justify-content: center;
            text-align: center;
        }
    }
</style>

<div class="avm-shell">
    <section class="avm-head d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-start gap-3">
            <span class="avm-head-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            <div>
                <h4 class="mb-1">Revision Mecanica</h4>
                <div class="text-muted small">Diagnostico mecanico, electrico y estetico posterior a recepcion de almacen.</div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/MotosAdjudicadas/almacenVirtual" class="btn btn-label-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Almacen Virtual
            </a>
            <a href="/MotosAdjudicadas/recepcionAlmacen" class="btn btn-label-success btn-sm">
                <i class="fa-solid fa-clipboard-check me-1"></i>Recepcion
            </a>
            <a href="/MotosAdjudicadas/kanbanOperativo" class="btn btn-label-secondary btn-sm">
                <i class="fa-solid fa-table-columns me-1"></i>Kanban
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="avm-btn-refresh">
                <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
            </button>
        </div>
    </section>

    <section class="avm-kpi-grid">
        <div class="avm-kpi">
            <div class="avm-kpi-label">Pendientes</div>
            <div class="avm-kpi-value" id="avm-kpi-pendientes">0</div>
        </div>
        <div class="avm-kpi">
            <div class="avm-kpi-label">En revision</div>
            <div class="avm-kpi-value" id="avm-kpi-en-revision">0</div>
        </div>
        <div class="avm-kpi">
            <div class="avm-kpi-label">Reparadas</div>
            <div class="avm-kpi-value" id="avm-kpi-reparadas">0</div>
        </div>
        <div class="avm-kpi">
            <div class="avm-kpi-label">Fuera presupuesto</div>
            <div class="avm-kpi-value" id="avm-kpi-fuera">0</div>
        </div>
        <div class="avm-kpi">
            <div class="avm-kpi-label">Irreparables</div>
            <div class="avm-kpi-value" id="avm-kpi-irreparables">0</div>
        </div>
    </section>

    <section class="avm-toolbar">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-3">
                <label class="form-label small fw-bold" for="avm-q">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control" id="avm-q" placeholder="Folio, VIN, motor, placa">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="avm-celula">Celula</label>
                <select class="form-select form-select-sm" id="avm-celula">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="avm-estatus">Estatus</label>
                <select class="form-select form-select-sm" id="avm-estatus">
                    <option value="abiertas" selected>Abiertas</option>
                    <option value="pendiente_revision">Pendiente revision</option>
                    <option value="en_revision">En revision</option>
                    <option value="reparada">Reparada</option>
                    <option value="fuera_presupuesto">Fuera presupuesto</option>
                    <option value="irreparable">Irreparable</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-bold" for="avm-ubicacion">Ubicacion</label>
                <select class="form-select form-select-sm" id="avm-ubicacion">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2 d-grid">
                <button type="button" class="btn btn-primary btn-sm" id="avm-btn-filtrar">
                    <i class="fa-solid fa-filter me-1"></i>Filtrar
                </button>
            </div>
        </div>
    </section>

    <section class="avm-table-wrap">
        <div class="avm-datatable-controls row mx-0 align-items-center">
            <div class="col-sm-12 col-md-6">
                <div class="dataTables_length">
                    <label>
                        Mostrar
                        <select id="avm-limit" class="form-select form-select-sm">
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
            <table class="dt-responsive table border-top avm-table">
                <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Celula</th>
                        <th>Identificacion</th>
                        <th>Recepcion</th>
                        <th>Ubicacion</th>
                        <th>Estatus</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody id="avm-unidades-body">
                    <tr>
                        <td colspan="7" class="avm-empty">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            Cargando unidades...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="avm-datatable-footer row mx-0 align-items-center">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="avm-pager-info">Mostrando 0 a 0 de 0 unidades</div>
            </div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    <ul class="pagination" id="avm-pagination"></ul>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="avm-modal-revision" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form class="modal-content" id="avm-form-revision" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Revision mecanica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_unidad" id="avm-id-unidad">
                <div class="avm-modal-summary mb-3" id="avm-modal-summary"></div>
                <div class="avm-modal-summary mb-3" id="avm-recepcion-summary"></div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold" for="avm-diagnostico">Diagnostico general</label>
                        <textarea class="form-control" name="diagnostico_general" id="avm-diagnostico" rows="3" maxlength="2000" required></textarea>
                    </div>
                    <div class="col-12" id="avm-checklist"></div>
                    <div class="col-12">
                        <div class="avm-section">
                            <div class="avm-section-title">Evidencias opcionales</div>
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-bold" for="avm-ev-mecanica">Mecanica</label>
                                    <input type="file" class="form-control" id="avm-ev-mecanica" name="rev_ev_mecanica" accept="image/*,video/mp4,video/quicktime,video/webm">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-bold" for="avm-ev-electrica">Electrica</label>
                                    <input type="file" class="form-control" id="avm-ev-electrica" name="rev_ev_electrica" accept="image/*,video/mp4,video/quicktime,video/webm">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-bold" for="avm-ev-estetica">Estetica</label>
                                    <input type="file" class="form-control" id="avm-ev-estetica" name="rev_ev_estetica" accept="image/*,video/mp4,video/quicktime,video/webm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="avm-section">
                            <div class="avm-section-title">Dictamen final</div>
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <div class="form-check border rounded p-3 h-100">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="dictamen" id="avm-dictamen-reparada" value="reparada" required>
                                        <label class="form-check-label fw-bold" for="avm-dictamen-reparada">Reparada</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-check border rounded p-3 h-100">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="dictamen" id="avm-dictamen-fuera" value="fuera_presupuesto" required>
                                        <label class="form-check-label fw-bold" for="avm-dictamen-fuera">Fuera de presupuesto</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-check border rounded p-3 h-100">
                                        <input class="form-check-input ms-0 me-2" type="radio" name="dictamen" id="avm-dictamen-irreparable" value="irreparable" required>
                                        <label class="form-check-label fw-bold" for="avm-dictamen-irreparable">Irreparable</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="avm-btn-finalizar">
                    <i class="fa-solid fa-check me-1"></i>Finalizar revision
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const checklist = <?= json_encode($checklistRevision, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
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
            pendiente_revision: 'Pendiente revision',
            en_revision: 'En revision',
            reparada: 'Reparada',
            fuera_presupuesto: 'Fuera presupuesto',
            irreparable: 'Irreparable',
            recolectada: 'Recolectada',
            recolectado: 'Recolectada',
            completado: 'Recolectada',
            completada: 'Recolectada',
        };
        return map[value] || value || 'Sin estatus';
    }

    function statusHtml(value) {
        const key = String(value || 'default').replace(/[^a-z0-9_]/gi, '_');
        const safe = ['pendiente_revision','en_revision','reparada','fuera_presupuesto','irreparable','recolectada','recolectado','completado','completada'].includes(key)
            ? 'avm-status-' + key
            : 'avm-status-default';
        return '<span class="avm-status ' + safe + '"><i class="fa-solid fa-circle"></i>' + esc(statusLabel(value)) + '</span>';
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
        const target = $('avm-pagination');
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
        const q = $('avm-q')?.value.trim() || '';
        const celula = $('avm-celula')?.value || '';
        const estatus = $('avm-estatus')?.value || 'abiertas';
        const ubicacion = $('avm-ubicacion')?.value || '';
        if (q) p.set('q', q);
        if (celula) p.set('id_celula', celula);
        if (estatus) p.set('estatus', estatus);
        if (ubicacion) p.set('id_ubicacion', ubicacion);
        return p;
    }

    async function cargarResumen() {
        const res = await fetch('/MotosAdjudicadas/revisionMecanicaResumen', { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success || !json.datos) return;
        const d = json.datos;
        $('avm-kpi-pendientes').textContent = d.pendientes || 0;
        $('avm-kpi-en-revision').textContent = d.en_revision || 0;
        $('avm-kpi-reparadas').textContent = d.reparadas || 0;
        $('avm-kpi-fuera').textContent = d.fuera_presupuesto || 0;
        $('avm-kpi-irreparables').textContent = d.irreparables || 0;
    }

    async function cargarCatalogos() {
        const [celulasRes, ubicacionesRes] = await Promise.all([
            fetch('/MotosAdjudicadas/inventarioCelulas', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
            fetch('/MotosAdjudicadas/inventarioUbicaciones', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
        ]);

        const celulaSelect = $('avm-celula');
        if (celulaSelect && celulasRes && celulasRes.success) {
            (celulasRes.datos || []).forEach((row) => {
                const opt = document.createElement('option');
                opt.value = String(row.id_celula);
                opt.textContent = row.nombre;
                celulaSelect.appendChild(opt);
            });
        }

        const ubicacionSelect = $('avm-ubicacion');
        state.ubicaciones = ubicacionesRes && ubicacionesRes.success ? (ubicacionesRes.datos || []) : [];
        state.ubicaciones.forEach((row) => {
            const opt = document.createElement('option');
            opt.value = String(row.id_ubicacion);
            opt.textContent = row.nombre_ubicacion;
            ubicacionSelect?.appendChild(opt);
        });
    }

    function renderRows(rows) {
        const body = $('avm-unidades-body');
        if (!body) return;
        state.rows.clear();
        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="7" class="avm-empty"><i class="fa-solid fa-screwdriver-wrench"></i>Sin unidades para revision mecanica con los filtros seleccionados.</td></tr>';
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
            const recepcion = row.recepcion_fecha_fmt ? ('Recibida ' + row.recepcion_fecha_fmt) : 'Sin recepcion registrada';
            const checklistOk = [
                Number(row.recepcion_arranque_motor || 0) === 1 ? 'Arranca' : '',
                Number(row.recepcion_sin_danos_mayores || 0) === 1 ? 'Sin danos mayores' : '',
            ].filter(Boolean).join(' | ');
            const puedeRevisar = ['pendiente_revision', 'en_revision'].includes(String(row.estatus_inventario || ''));
            const accion = puedeRevisar
                ? '<button type="button" class="btn btn-danger btn-sm" data-action="revisar" data-id="' + esc(row.id_unidad) + '"><i class="fa-solid fa-screwdriver-wrench me-1"></i>Revisar</button>'
                : '<button type="button" class="btn btn-label-secondary btn-sm" disabled><i class="fa-solid fa-check me-1"></i>Dictaminada</button>';

            return `
                <tr>
                    <td>
                        <div class="avm-unit-main">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
                        <div class="avm-unit-sub">${esc(moto || 'Sin datos de moto')}</div>
                    </td>
                    <td>${esc(row.nombre_celula || '')}</td>
                    <td>
                        <div>${esc(ids || 'Sin identificadores')}</div>
                        ${row.id_credito ? `<div class="avm-unit-sub">Credito historico: ${esc(row.id_credito)}</div>` : ''}
                    </td>
                    <td>
                        <div>${esc(recepcion)}</div>
                        <div class="avm-unit-sub">${esc(checklistOk || row.recepcion_resultado || '')}</div>
                    </td>
                    <td>
                        <div>${esc(row.nombre_ubicacion || 'Sin ubicacion')}</div>
                        <div class="avm-unit-sub">${esc(row.tipo_ubicacion || '')}</div>
                    </td>
                    <td>${statusHtml(row.estatus_inventario)}</td>
                    <td>${accion}</td>
                </tr>
            `;
        }).join('');
    }

    async function cargarUnidades() {
        const body = $('avm-unidades-body');
        if (body) {
            body.innerHTML = '<tr><td colspan="7" class="avm-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando unidades...</td></tr>';
        }
        const res = await fetch('/MotosAdjudicadas/revisionMecanicaUnidades?' + params().toString(), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            if (body) {
                body.innerHTML = '<tr><td colspan="7" class="avm-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(json.message || 'No se pudieron cargar las unidades.') + '</td></tr>';
            }
            return;
        }
        state.total = Number(json.total || 0);
        state.pages = Number(json.pages || 1);
        state.page = Number(json.page || 1);
        state.limit = Number(json.limit || state.limit);
        renderRows(json.rows || []);
        const info = $('avm-pager-info');
        if (info) info.textContent = rangeInfo(state.total, state.page, state.limit, 'unidades');
        renderPagination();
    }

    function renderChecklist() {
        const target = $('avm-checklist');
        if (!target) return;
        target.innerHTML = Object.entries(checklist || {}).map(([categoria, grupo]) => {
            const items = Array.isArray(grupo.items) ? grupo.items : [];
            const campos = items.map((item) => {
                const clave = String(item.clave || '');
                const id = 'avm-item-' + categoria + '-' + clave;
                const selectName = 'tipo_servicio[' + categoria + '_' + clave + ']';
                const requiereTipo = item.tipo_servicio === 'mp_mc';
                const selector = requiereTipo
                    ? `<select class="form-select form-select-sm" name="${esc(selectName)}" data-service-for="${esc(categoria + '_' + clave)}" disabled>
                            <option value="">MP/MC</option>
                            <option value="mp">MP</option>
                            <option value="mc">MC</option>
                       </select>`
                    : '';
                return `
                    <div class="avm-check-row">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="items[]" value="${esc(categoria + '|' + clave)}" data-service-key="${esc(categoria + '_' + clave)}" id="${esc(id)}">
                            <label class="form-check-label" for="${esc(id)}">${esc(item.descripcion || clave)}</label>
                        </div>
                        ${selector}
                    </div>
                `;
            }).join('');
            return `
                <div class="avm-section mb-3">
                    <div class="avm-section-title">${esc(grupo.titulo || categoria)}</div>
                    <div class="avm-check-grid mb-3">${campos}</div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold" for="avm-otros-${esc(categoria)}">Otros</label>
                            <textarea class="form-control" id="avm-otros-${esc(categoria)}" name="otros_${esc(categoria)}" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold" for="avm-comentario-${esc(categoria)}">Comentario</label>
                            <textarea class="form-control" id="avm-comentario-${esc(categoria)}" name="comentario_${esc(categoria)}" rows="2" maxlength="1500"></textarea>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function bit(value) {
        return Number(value || 0) === 1 ? 'Si' : 'No';
    }

    function renderReception(row) {
        return `
            <div class="avm-unit-main mb-2">Respuestas de recepcion</div>
            <div class="avm-reception-grid">
                <div class="avm-reception-item"><strong>VIN coincide</strong>${esc(bit(row.recepcion_vin_coincide))}</div>
                <div class="avm-reception-item"><strong>4 angulos</strong>${esc(bit(row.recepcion_evidencia_4_angulos))}</div>
                <div class="avm-reception-item"><strong>Evidencia VIN</strong>${esc(bit(row.recepcion_evidencia_vin))}</div>
                <div class="avm-reception-item"><strong>Documentos</strong>${esc(bit(row.recepcion_documentos_completos))}</div>
                <div class="avm-reception-item"><strong>Arranque</strong>${esc(bit(row.recepcion_arranque_motor))}</div>
                <div class="avm-reception-item"><strong>Sin danos mayores</strong>${esc(bit(row.recepcion_sin_danos_mayores))}</div>
            </div>
            ${row.recepcion_observaciones ? `<div class="text-muted small mt-2">${esc(row.recepcion_observaciones)}</div>` : ''}
        `;
    }

    async function abrirModalRevision(idUnidad) {
        const row = state.rows.get(String(idUnidad));
        if (!row) return;
        const form = $('avm-form-revision');
        form?.reset();
        $('avm-id-unidad').value = row.id_unidad || '';

        const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
        const ids = [
            row.vin ? 'VIN ' + row.vin : '',
            row.no_motor ? 'Motor ' + row.no_motor : '',
            row.placas ? 'Placa ' + row.placas : '',
            row.kilometraje ? row.kilometraje + ' km' : '',
        ].filter(Boolean).join(' | ');
        $('avm-modal-summary').innerHTML = `
            <div class="d-flex justify-content-between gap-2 flex-wrap">
                <div>
                    <div class="avm-unit-main">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
                    <div class="text-muted small">${esc(moto || 'Sin datos de moto')}</div>
                    <div class="avm-unit-sub mt-1">${esc(ids || 'Sin identificadores')}</div>
                </div>
                <div>${statusHtml(row.estatus_inventario)}</div>
            </div>
        `;
        $('avm-recepcion-summary').innerHTML = renderReception(row);

        try {
            const fd = new FormData();
            fd.set('id_unidad', String(row.id_unidad));
            const res = await fetch('/MotosAdjudicadas/revisionMecanicaIniciar', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: fd,
            });
            const json = await res.json();
            if (!json.success) {
                notify('error', 'Revision no iniciada', json.message || 'No se pudo iniciar la revision.');
                return;
            }
            row.estatus_inventario = 'en_revision';
            cargarResumen().catch(() => {});
        } catch (err) {
            notify('error', 'Error inesperado', err.message || 'No se pudo contactar al servidor.');
            return;
        }

        const modalEl = $('avm-modal-revision');
        if (window.bootstrap && window.bootstrap.Modal && modalEl) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    async function finalizarRevision(ev) {
        ev.preventDefault();
        const form = $('avm-form-revision');
        const btn = $('avm-btn-finalizar');
        if (!form || !btn) return;
        btn.disabled = true;
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Guardando';
        try {
            const res = await fetch('/MotosAdjudicadas/revisionMecanicaFinalizar', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            const json = await res.json();
            if (!json.success) {
                notify('error', 'Revision no guardada', json.message || 'No se pudo finalizar la revision.');
                return;
            }
            const modalEl = $('avm-modal-revision');
            if (window.bootstrap && window.bootstrap.Modal && modalEl) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            notify('success', 'Revision finalizada', json.message || 'Revision actualizada.');
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
            const body = $('avm-unidades-body');
            if (body) {
                body.innerHTML = '<tr><td colspan="7" class="avm-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(err.message || 'Error inesperado.') + '</td></tr>';
            }
        });
    }

    function init() {
        renderChecklist();
        $('avm-btn-refresh')?.addEventListener('click', () => reloadAll(false));
        $('avm-btn-filtrar')?.addEventListener('click', () => reloadAll(true));
        $('avm-form-revision')?.addEventListener('submit', finalizarRevision);
        $('avm-limit')?.addEventListener('change', () => {
            state.limit = Number($('avm-limit')?.value || 8) || 8;
            reloadAll(true);
        });
        $('avm-pagination')?.addEventListener('click', (ev) => {
            const link = ev.target.closest('[data-page]');
            if (!link) return;
            ev.preventDefault();
            const nextPage = Number(link.dataset.page || 1);
            if (nextPage && nextPage !== state.page) {
                state.page = nextPage;
                cargarUnidades();
            }
        });
        $('avm-unidades-body')?.addEventListener('click', (ev) => {
            const btn = ev.target.closest('[data-action="revisar"]');
            if (!btn) return;
            abrirModalRevision(btn.dataset.id);
        });
        $('avm-checklist')?.addEventListener('change', (ev) => {
            const input = ev.target.closest('input[type="checkbox"][name="items[]"]');
            if (!input) return;
            const select = document.querySelector('[data-service-for="' + input.dataset.serviceKey + '"]');
            if (select) {
                select.disabled = !input.checked;
                if (!input.checked) select.value = '';
            }
        });
        $('avm-q')?.addEventListener('input', () => {
            window.clearTimeout(state.timer);
            state.timer = window.setTimeout(() => reloadAll(true), 350);
        });
        ['avm-celula', 'avm-estatus', 'avm-ubicacion'].forEach((id) => {
            $(id)?.addEventListener('change', () => reloadAll(true));
        });

        cargarCatalogos()
            .catch(() => {})
            .finally(() => reloadAll(true));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
