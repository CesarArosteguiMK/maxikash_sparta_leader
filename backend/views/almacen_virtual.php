<style>
    .av-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .av-head {
        border: 1px solid #dbe4ef;
        background: #f8fafc;
        border-radius: .5rem;
        padding: 1rem 1.15rem;
    }
    .av-head-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: .5rem;
        background: #e0f2fe;
        color: #0369a1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
    }
    .av-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .av-kpi {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        padding: .85rem;
        min-height: 5.3rem;
    }
    .av-kpi-label {
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .av-kpi-value {
        color: #1e293b;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
        margin-top: .35rem;
    }
    .av-toolbar {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        padding: .85rem;
    }
    .av-import {
        border: 1px solid #bae6fd;
        background: #f0f9ff;
        border-radius: .5rem;
        padding: .85rem;
    }
    .av-import-title {
        color: #0f172a;
        font-size: .86rem;
        font-weight: 800;
        margin-bottom: .15rem;
    }
    .av-import-sub {
        color: #64748b;
        font-size: .76rem;
    }
    .av-pending {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        overflow: hidden;
    }
    .av-pending-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
        padding: .85rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .av-pending-title {
        color: #0f172a;
        font-weight: 800;
        margin-bottom: .15rem;
    }
    .av-pending-sub {
        color: #64748b;
        font-size: .76rem;
    }
    .av-table-wrap {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        overflow: hidden;
    }
    .av-table {
        margin-bottom: 0;
    }
    .av-table th {
        white-space: nowrap;
    }
    .av-table td {
        vertical-align: middle;
    }
    .av-datatable-controls {
        padding: .85rem 1rem .35rem;
        background: #fff;
    }
    .av-datatable-controls .dataTables_length label {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        color: #697a8d;
        font-size: .875rem;
        margin: 0;
    }
    .av-datatable-controls .dataTables_length select {
        width: auto;
        min-width: 4.75rem;
    }
    .av-datatable-footer {
        border-top: 1px solid #d9dee3;
        padding: .75rem 1rem;
        background: #fff;
    }
    .av-datatable-footer .dataTables_info {
        color: #697a8d;
        font-size: .875rem;
        padding-top: .45rem;
    }
    .av-datatable-footer .pagination {
        justify-content: flex-end;
        margin: 0;
        gap: .25rem;
    }
    .av-datatable-footer .page-link {
        min-width: 2rem;
        text-align: center;
        border-radius: .375rem;
    }
    .av-unit-main {
        color: #1e293b;
        font-weight: 800;
    }
    .av-unit-sub {
        color: #64748b;
        font-size: .74rem;
    }
    .av-status {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border-radius: 999px;
        padding: .24rem .58rem;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .av-status-pendiente_recepcion { background: #fef3c7; color: #92400e; }
    .av-status-en_recepcion { background: #dbeafe; color: #1d4ed8; }
    .av-status-pendiente_revision { background: #f3e8ff; color: #7e22ce; }
    .av-status-en_revision { background: #e0f2fe; color: #0369a1; }
    .av-status-reparada { background: #dcfce7; color: #166534; }
    .av-status-fuera_presupuesto { background: #fee2e2; color: #991b1b; }
    .av-status-irreparable { background: #e5e7eb; color: #374151; }
    .av-status-lista_venta { background: #ccfbf1; color: #0f766e; }
    .av-status-en_traspaso { background: #ffedd5; color: #c2410c; }
    .av-status-default { background: #eef2ff; color: #3730a3; }
    .av-empty {
        padding: 2.25rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .av-empty i {
        display: block;
        font-size: 2rem;
        opacity: .35;
        margin-bottom: .65rem;
    }
    @media (max-width: 992px) {
        .av-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 576px) {
        .av-kpi-grid {
            grid-template-columns: 1fr;
        }
        .av-head {
            padding: .9rem;
        }
        .av-datatable-controls .dataTables_length label {
            width: 100%;
            justify-content: space-between;
        }
        .av-datatable-footer .dataTables_info,
        .av-datatable-footer .pagination {
            justify-content: center;
            text-align: center;
        }
    }
</style>

<div class="av-shell">
    <section class="av-head d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-start gap-3">
            <span class="av-head-icon"><i class="fa-solid fa-warehouse"></i></span>
            <div>
                <h4 class="mb-1">Almacen Virtual</h4>
                <div class="text-muted small">Inventario operativo de unidades por celula, ubicacion y estatus.</div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="av-btn-refresh">
            <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
        </button>
    </section>

    <section class="av-kpi-grid" id="av-kpis">
        <div class="av-kpi">
            <div class="av-kpi-label">Total unidades</div>
            <div class="av-kpi-value" id="av-kpi-total">0</div>
        </div>
        <div class="av-kpi">
            <div class="av-kpi-label">Motos adjudicadas</div>
            <div class="av-kpi-value" id="av-kpi-madj">0</div>
        </div>
        <div class="av-kpi">
            <div class="av-kpi-label">FuriaMotos</div>
            <div class="av-kpi-value" id="av-kpi-furia">0</div>
        </div>
        <div class="av-kpi">
            <div class="av-kpi-label">Estatus activos</div>
            <div class="av-kpi-value" id="av-kpi-estatus">0</div>
        </div>
    </section>

    <section class="av-import">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-5">
                <div class="av-import-title">
                    <i class="fa-solid fa-motorcycle me-1"></i>Importar desde Motos Adjudicadas
                </div>
                <div class="av-import-sub">Crea la unidad fisica en Almacen Virtual usando el expediente origen.</div>
            </div>
            <div class="col-12 col-sm-7 col-lg-4">
                <label class="form-label small fw-bold" for="av-import-id-operacion">ID operacion</label>
                <input type="number" min="1" step="1" class="form-control form-control-sm" id="av-import-id-operacion" placeholder="Ej. 123">
            </div>
            <div class="col-12 col-sm-5 col-lg-3 d-grid">
                <button type="button" class="btn btn-info btn-sm" id="av-btn-import-madj">
                    <i class="fa-solid fa-file-import me-1"></i>Crear unidad
                </button>
            </div>
        </div>
    </section>

    <section class="av-pending">
        <div class="av-pending-head">
            <div>
                <div class="av-pending-title">
                    <i class="fa-solid fa-list-check me-1 text-primary"></i>Pendientes de Motos Adjudicadas
                </div>
                <div class="av-pending-sub">Operaciones listas para crear unidad fisica en Almacen Virtual.</div>
            </div>
            <div class="d-flex align-items-end gap-2 flex-wrap">
                <div>
                    <label class="form-label small fw-bold" for="av-pending-q">Buscar pendiente</label>
                    <input type="text" class="form-control form-control-sm" id="av-pending-q" placeholder="Operacion, credito, cliente">
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="av-btn-pending-refresh">
                    <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
                </button>
            </div>
        </div>
        <div class="av-datatable-controls row mx-0 align-items-center">
            <div class="col-sm-12 col-md-6">
                <div class="dataTables_length">
                    <label>
                        Mostrar
                        <select id="av-pending-limit" class="form-select form-select-sm">
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
            <table class="dt-responsive table border-top av-table">
                <thead>
                    <tr>
                        <th>Operacion</th>
                        <th>Cliente</th>
                        <th>Unidad origen</th>
                        <th>Estatus origen</th>
                        <th>Sugerido</th>
                        <th class="text-end">Accion</th>
                    </tr>
                </thead>
                <tbody id="av-pendientes-body">
                    <tr>
                        <td colspan="6" class="av-empty">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            Cargando pendientes...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="av-datatable-footer row mx-0 align-items-center">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="av-pending-pager-info">Mostrando 0 a 0 de 0 pendientes</div>
            </div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    <ul class="pagination" id="av-pending-pagination"></ul>
                </div>
            </div>
        </div>
    </section>

    <section class="av-toolbar">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label small fw-bold" for="av-q">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control" id="av-q" placeholder="Folio, VIN, motor, placa, marca">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="av-celula">Celula</label>
                <select class="form-select form-select-sm" id="av-celula">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="av-estatus">Estatus</label>
                <select class="form-select form-select-sm" id="av-estatus">
                    <option value="">Todos</option>
                    <option value="pendiente_recepcion">Pendiente recepcion</option>
                    <option value="en_recepcion">En recepcion</option>
                    <option value="pendiente_revision">Pendiente revision</option>
                    <option value="en_revision">En revision</option>
                    <option value="reparada">Reparada</option>
                    <option value="fuera_presupuesto">Fuera de presupuesto</option>
                    <option value="irreparable">Irreparable</option>
                    <option value="lista_venta">Lista para venta</option>
                    <option value="en_traspaso">En traspaso</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="av-ubicacion">Ubicacion</label>
                <select class="form-select form-select-sm" id="av-ubicacion">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2 d-grid">
                <button type="button" class="btn btn-primary btn-sm" id="av-btn-filtrar">
                    <i class="fa-solid fa-filter me-1"></i>Filtrar
                </button>
            </div>
        </div>
    </section>

    <section class="av-table-wrap">
        <div class="av-datatable-controls row mx-0 align-items-center">
            <div class="col-sm-12 col-md-6">
                <div class="dataTables_length">
                    <label>
                        Mostrar
                        <select id="av-limit" class="form-select form-select-sm">
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
            <table class="dt-responsive table border-top av-table">
                <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Celula</th>
                        <th>Identificacion</th>
                        <th>Ubicacion</th>
                        <th>Estatus</th>
                        <th>Ingreso</th>
                    </tr>
                </thead>
                <tbody id="av-unidades-body">
                    <tr>
                        <td colspan="6" class="av-empty">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            Cargando unidades...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="av-datatable-footer row mx-0 align-items-center">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="av-pager-info">Mostrando 0 a 0 de 0 unidades</div>
            </div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    <ul class="pagination" id="av-pagination"></ul>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    const state = {
        page: 1,
        limit: 8,
        pages: 1,
        total: 0,
        timer: null,
        pendingTimer: null,
        pendingPage: 1,
        pendingLimit: 8,
        pendingPages: 1,
        pendingTotal: 0,
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
            pendiente_revision: 'Pendiente revision',
            en_revision: 'En revision',
            reparada: 'Reparada',
            fuera_presupuesto: 'Fuera de presupuesto',
            irreparable: 'Irreparable',
            lista_venta: 'Lista para venta',
            en_traspaso: 'En traspaso',
        };
        return map[value] || value || 'Sin estatus';
    }

    function statusHtml(value) {
        const key = String(value || 'default').replace(/[^a-z0-9_]/gi, '_');
        const safeClass = ['pendiente_recepcion','en_recepcion','pendiente_revision','en_revision','reparada','fuera_presupuesto','irreparable','lista_venta','en_traspaso'].includes(key)
            ? 'av-status-' + key
            : 'av-status-default';
        return '<span class="av-status ' + safeClass + '"><i class="fa-solid fa-circle"></i>' + esc(statusLabel(value)) + '</span>';
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

    function renderPagination(targetId, page, pages) {
        const target = $(targetId);
        if (!target) return;
        const current = Math.max(1, Number(page || 1));
        const totalPages = Math.max(1, Number(pages || 1));

        const item = (label, targetPage, disabled, active, extraClass) => {
            const classes = ['paginate_button', 'page-item'];
            if (extraClass) classes.push(extraClass);
            if (disabled) classes.push('disabled');
            if (active) classes.push('active');
            const attrs = disabled ? 'tabindex="-1" aria-disabled="true"' : 'href="#" data-page="' + esc(targetPage) + '"';
            return '<li class="' + classes.join(' ') + '"><a class="page-link" ' + attrs + '>' + label + '</a></li>';
        };

        const html = [
            item('Anterior', Math.max(1, current - 1), current <= 1, false, 'previous'),
            ...pageNumbers(current, totalPages).map((value) => {
                if (typeof value === 'string') {
                    return item('...', current, true, false, '');
                }
                return item(String(value), value, false, value === current, '');
            }),
            item('Siguiente', Math.min(totalPages, current + 1), current >= totalPages, false, 'next'),
        ];
        target.innerHTML = html.join('');
    }

    function params() {
        const p = new URLSearchParams();
        p.set('page', String(state.page));
        p.set('limit', String(state.limit));
        const q = $('av-q')?.value.trim() || '';
        const celula = $('av-celula')?.value || '';
        const estatus = $('av-estatus')?.value || '';
        const ubicacion = $('av-ubicacion')?.value || '';
        if (q) p.set('q', q);
        if (celula) p.set('id_celula', celula);
        if (estatus) p.set('estatus', estatus);
        if (ubicacion) p.set('id_ubicacion', ubicacion);
        return p;
    }

    async function cargarResumen() {
        const res = await fetch('/AlmacenVirtual/resumen', { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success || !json.datos) return;
        const d = json.datos;
        $('av-kpi-total').textContent = d.total || 0;
        $('av-kpi-estatus').textContent = Array.isArray(d.por_estatus) ? d.por_estatus.length : 0;
        let madj = 0;
        let furia = 0;
        (d.por_celula || []).forEach((row) => {
            if (Number(row.id_celula) === 1) madj = Number(row.total || 0);
            if (Number(row.id_celula) === 2) furia = Number(row.total || 0);
        });
        $('av-kpi-madj').textContent = madj;
        $('av-kpi-furia').textContent = furia;
    }

    async function cargarCatalogos() {
        const [celulasRes, ubicacionesRes] = await Promise.all([
            fetch('/AlmacenVirtual/celulas', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
            fetch('/AlmacenVirtual/ubicaciones', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
        ]);

        const celulaSelect = $('av-celula');
        if (celulaSelect && celulasRes && celulasRes.success) {
            (celulasRes.datos || []).forEach((row) => {
                const opt = document.createElement('option');
                opt.value = String(row.id_celula);
                opt.textContent = row.nombre;
                celulaSelect.appendChild(opt);
            });
        }

        const ubicacionSelect = $('av-ubicacion');
        if (ubicacionSelect && ubicacionesRes && ubicacionesRes.success) {
            (ubicacionesRes.datos || []).forEach((row) => {
                const opt = document.createElement('option');
                opt.value = String(row.id_ubicacion);
                opt.textContent = row.nombre_ubicacion;
                ubicacionSelect.appendChild(opt);
            });
        }
    }

    function renderRows(rows) {
        const body = $('av-unidades-body');
        if (!body) return;
        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="6" class="av-empty"><i class="fa-solid fa-warehouse"></i>Sin unidades para los filtros seleccionados.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((row) => {
            const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
            const ids = [
                row.vin ? 'VIN ' + row.vin : '',
                row.no_motor ? 'Motor ' + row.no_motor : '',
                row.placas ? 'Placa ' + row.placas : '',
            ].filter(Boolean).join(' | ');
            return `
                <tr>
                    <td>
                        <div class="av-unit-main">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
                        <div class="av-unit-sub">${esc(moto || 'Sin datos de moto')}</div>
                    </td>
                    <td>${esc(row.nombre_celula || '')}</td>
                    <td>
                        <div>${esc(ids || 'Sin identificadores')}</div>
                        ${row.id_credito ? `<div class="av-unit-sub">Credito historico: ${esc(row.id_credito)}</div>` : ''}
                    </td>
                    <td>
                        <div>${esc(row.nombre_ubicacion || 'Sin ubicacion')}</div>
                        <div class="av-unit-sub">${esc(row.tipo_ubicacion || '')}</div>
                    </td>
                    <td>${statusHtml(row.estatus_inventario)}</td>
                    <td>${esc(row.fecha_ingreso_virtual_fmt || row.fecha_alta_fmt || '')}</td>
                </tr>
            `;
        }).join('');
    }

    async function cargarUnidades() {
        const body = $('av-unidades-body');
        if (body) {
            body.innerHTML = '<tr><td colspan="6" class="av-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando unidades...</td></tr>';
        }
        const res = await fetch('/AlmacenVirtual/unidades?' + params().toString(), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            if (body) {
                body.innerHTML = '<tr><td colspan="6" class="av-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(json.message || 'No se pudieron cargar las unidades.') + '</td></tr>';
            }
            return;
        }
        state.total = Number(json.total || 0);
        state.pages = Number(json.pages || 1);
        state.page = Number(json.page || 1);
        state.limit = Number(json.limit || state.limit);
        renderRows(json.rows || []);
        actualizarPagerUnidades();
    }

    function actualizarPagerUnidades() {
        const info = $('av-pager-info');
        if (info) {
            info.textContent = rangeInfo(state.total, state.page, state.limit, 'unidades');
        }
        renderPagination('av-pagination', state.page, state.pages);
    }

    function renderPendientes(rows) {
        const body = $('av-pendientes-body');
        if (!body) return;
        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="6" class="av-empty"><i class="fa-solid fa-circle-check"></i>No hay operaciones pendientes por importar.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((row) => {
            const moto = [row.marca_unidad, row.modelo_unidad, row.moto_anio].filter(Boolean).join(' ');
            const ids = [
                row.vin ? 'VIN ' + row.vin : '',
                row.no_motor ? 'Motor ' + row.no_motor : '',
                row.placas_unidad ? 'Placa ' + row.placas_unidad : '',
            ].filter(Boolean).join(' | ');
            return `
                <tr>
                    <td>
                        <div class="av-unit-main">#${esc(row.id_operacion)}</div>
                        ${row.id_credito ? `<div class="av-unit-sub">Credito hist.: ${esc(row.id_credito)}</div>` : ''}
                    </td>
                    <td>
                        <div>${esc(row.nombre_cliente || 'Sin cliente')}</div>
                        <div class="av-unit-sub">${esc(row.fecha_actualizacion_fmt || '')}</div>
                    </td>
                    <td>
                        <div>${esc(moto || 'Sin datos de moto')}</div>
                        <div class="av-unit-sub">${esc(ids || 'Sin identificadores')}</div>
                    </td>
                    <td>${esc(row.estatus || '')}</td>
                    <td>${statusHtml(row.estatus_inventario_sugerido)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-primary btn-sm av-pending-create" data-id-operacion="${esc(row.id_operacion)}">
                            <i class="fa-solid fa-file-import me-1"></i>Crear
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function actualizarPagerPendientes() {
        const info = $('av-pending-pager-info');
        if (info) {
            info.textContent = rangeInfo(state.pendingTotal, state.pendingPage, state.pendingLimit, 'pendientes');
        }
        renderPagination('av-pending-pagination', state.pendingPage, state.pendingPages);
    }

    async function cargarPendientesMotosAdjudicadas(resetPage = false) {
        if (resetPage) state.pendingPage = 1;
        const body = $('av-pendientes-body');
        if (body) {
            body.innerHTML = '<tr><td colspan="6" class="av-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando pendientes...</td></tr>';
        }
        const p = new URLSearchParams({
            limit: String(state.pendingLimit),
            page: String(state.pendingPage),
        });
        const q = $('av-pending-q')?.value.trim() || '';
        if (q) p.set('q', q);
        try {
            const res = await fetch('/AlmacenVirtual/pendientesMotosAdjudicadas?' + p.toString(), { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!json.success) {
                if (body) {
                    body.innerHTML = '<tr><td colspan="6" class="av-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(json.message || 'No se pudieron cargar pendientes.') + '</td></tr>';
                }
                return;
            }
            state.pendingTotal = Number(json.total || 0);
            state.pendingPages = Number(json.pages || 1);
            state.pendingPage = Number(json.page || 1);
            state.pendingLimit = Number(json.limit || state.pendingLimit);
            renderPendientes(json.rows || []);
            actualizarPagerPendientes();
        } catch (err) {
            if (body) {
                body.innerHTML = '<tr><td colspan="6" class="av-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(err.message || 'Error al cargar pendientes.') + '</td></tr>';
            }
        }
    }

    async function importarDesdeMotosAdjudicadas(idOperacionArg) {
        const input = $('av-import-id-operacion');
        const btn = $('av-btn-import-madj');
        const idOperacion = Number(idOperacionArg || input?.value || 0);
        if (!idOperacion || idOperacion <= 0) {
            notify('warning', 'ID requerido', 'Indica un id_operacion valido.');
            input?.focus();
            return;
        }

        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Creando...';
        }

        try {
            const res = await fetch('/AlmacenVirtual/crearDesdeMotosAdjudicadas', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_operacion: idOperacion }),
            });
            const json = await res.json();
            if (!json.success) {
                notify('error', 'No se pudo crear', json.message || 'Intenta nuevamente.');
                return;
            }

            input.value = '';
            reloadAll(true);
            cargarPendientesMotosAdjudicadas();
            const folio = json.unidad && json.unidad.folio_unidad ? json.unidad.folio_unidad : '';
            notify(
                json.ya_existe ? 'info' : 'success',
                json.ya_existe ? 'Unidad ya existente' : 'Unidad creada',
                folio ? ('Folio: ' + folio) : (json.message || 'Operacion completada.')
            );
        } catch (err) {
            notify('error', 'Error de red', err.message || 'No se pudo contactar al servidor.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }

    function reloadAll(resetPage) {
        if (resetPage) state.page = 1;
        cargarResumen().catch(() => {});
        cargarUnidades().catch((err) => {
            const body = $('av-unidades-body');
            if (body) {
                body.innerHTML = '<tr><td colspan="6" class="av-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(err.message || 'Error inesperado.') + '</td></tr>';
            }
        });
    }

    function init() {
        $('av-btn-refresh')?.addEventListener('click', () => reloadAll(false));
        $('av-btn-filtrar')?.addEventListener('click', () => reloadAll(true));
        $('av-btn-pending-refresh')?.addEventListener('click', () => cargarPendientesMotosAdjudicadas());
        $('av-pending-q')?.addEventListener('input', () => {
            window.clearTimeout(state.pendingTimer);
            state.pendingTimer = window.setTimeout(() => cargarPendientesMotosAdjudicadas(true), 350);
        });
        $('av-pendientes-body')?.addEventListener('click', (ev) => {
            const btn = ev.target.closest('.av-pending-create');
            if (!btn) return;
            importarDesdeMotosAdjudicadas(btn.dataset.idOperacion);
        });
        $('av-pending-limit')?.addEventListener('change', () => {
            state.pendingLimit = Number($('av-pending-limit')?.value || 8) || 8;
            cargarPendientesMotosAdjudicadas(true);
        });
        $('av-pending-pagination')?.addEventListener('click', (ev) => {
            const link = ev.target.closest('[data-page]');
            if (!link) return;
            ev.preventDefault();
            const nextPage = Number(link.dataset.page || 1);
            if (nextPage && nextPage !== state.pendingPage) {
                state.pendingPage = nextPage;
                cargarPendientesMotosAdjudicadas();
            }
        });
        $('av-btn-import-madj')?.addEventListener('click', importarDesdeMotosAdjudicadas);
        $('av-import-id-operacion')?.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                importarDesdeMotosAdjudicadas();
            }
        });
        $('av-limit')?.addEventListener('change', () => {
            state.limit = Number($('av-limit')?.value || 8) || 8;
            reloadAll(true);
        });
        $('av-pagination')?.addEventListener('click', (ev) => {
            const link = ev.target.closest('[data-page]');
            if (!link) return;
            ev.preventDefault();
            const nextPage = Number(link.dataset.page || 1);
            if (nextPage && nextPage !== state.page) {
                state.page = nextPage;
                cargarUnidades();
            }
        });
        $('av-q')?.addEventListener('input', () => {
            window.clearTimeout(state.timer);
            state.timer = window.setTimeout(() => reloadAll(true), 350);
        });
        ['av-celula', 'av-estatus', 'av-ubicacion'].forEach((id) => {
            $(id)?.addEventListener('change', () => reloadAll(true));
        });

        cargarCatalogos()
            .catch(() => {})
            .finally(() => {
                reloadAll(true);
                cargarPendientesMotosAdjudicadas();
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
