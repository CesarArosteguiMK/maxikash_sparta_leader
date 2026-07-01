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
    .av-sync-status {
        color: #64748b;
        font-size: .74rem;
        margin-top: .25rem;
        min-height: 1rem;
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
    .av-status-recolectada { background: #dcfce7; color: #15803d; }
    .av-status-recolectado { background: #dcfce7; color: #15803d; }
    .av-status-completado { background: #dcfce7; color: #15803d; }
    .av-status-completada { background: #dcfce7; color: #15803d; }
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
            <span class="av-head-icon"><i class="fa-solid fa-clipboard-list"></i></span>
            <div>
                <h4 class="mb-1">Inventario</h4>
                <div class="text-muted small">Inventario operativo de Motos Adjudicadas por celula, ubicacion y estatus.</div>
                <div class="av-sync-status" id="av-sync-status"></div>
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
                        <th>Recoleccion</th>
                        <th>Ubicacion</th>
                        <th>Estatus</th>
                        <th>Ingreso</th>
                    </tr>
                </thead>
                <tbody id="av-unidades-body">
                    <tr>
                        <td colspan="7" class="av-empty">
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
            recolectada: 'Recolectada',
            recolectado: 'Recolectada',
            completado: 'Recolectada',
            completada: 'Recolectada',
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
        const safeClass = ['pendiente_recepcion','en_recepcion','pendiente_revision','en_revision','recolectada','recolectado','completado','completada','reparada','fuera_presupuesto','irreparable','lista_venta','en_traspaso'].includes(key)
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
        const res = await fetch('/MotosAdjudicadas/inventarioResumen', { headers: { Accept: 'application/json' } });
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
            fetch('/MotosAdjudicadas/inventarioCelulas', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
            fetch('/MotosAdjudicadas/inventarioUbicaciones', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
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
            body.innerHTML = '<tr><td colspan="7" class="av-empty"><i class="fa-solid fa-clipboard-list"></i>Sin unidades para los filtros seleccionados.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((row) => {
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
                        <div>${row.tracking_estatus_recoleccion ? statusHtml(row.tracking_estatus_recoleccion) : '<span class="text-muted small">Sin tracking</span>'}</div>
                        <div class="av-unit-sub">${esc(trackingMeta || row.tracking_nombre_ruta || '')}</div>
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
            body.innerHTML = '<tr><td colspan="7" class="av-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando unidades...</td></tr>';
        }
        const res = await fetch('/MotosAdjudicadas/inventarioUnidades?' + params().toString(), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            if (body) {
                body.innerHTML = '<tr><td colspan="7" class="av-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(json.message || 'No se pudieron cargar las unidades.') + '</td></tr>';
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

    async function sincronizarRecolectadasInventario(silent = true) {
        const status = $('av-sync-status');
        if (status) status.textContent = 'Sincronizando recolectadas de Tracking...';
        try {
            const res = await fetch('/MotosAdjudicadas/inventarioSincronizarRecolectadas?limit=250', {
                method: 'POST',
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            if (!json.success && !Number(json.creadas || 0)) {
                if (status) status.textContent = json.message || 'No se pudo sincronizar recolectadas automaticamente.';
                if (!silent) notify('warning', 'Sin sincronizacion', json.message || 'No se pudo sincronizar recolectadas.');
                return json;
            }
            const creadas = Number(json.creadas || 0);
            const errores = Array.isArray(json.errores) ? json.errores.length : 0;
            if (status) {
                status.textContent = creadas > 0
                    ? `${creadas} unidad(es) recolectada(s) migrada(s) al inventario.`
                    : 'Sin recolectadas nuevas por migrar.';
            }
            if (!silent && creadas > 0) {
                notify('success', 'Sincronizacion lista', `${creadas} unidad(es) migrada(s) desde Tracking.`);
            } else if (!silent && errores > 0) {
                notify('warning', 'Sincronizacion parcial', json.message || 'Algunas unidades no pudieron sincronizarse.');
            }
            return json;
        } catch (err) {
            if (status) status.textContent = 'No se pudo sincronizar recolectadas automaticamente.';
            if (!silent) notify('error', 'Error de sincronizacion', err.message || 'No se pudo contactar al servidor.');
            return null;
        }
    }

    function reloadAll(resetPage) {
        if (resetPage) state.page = 1;
        cargarResumen().catch(() => {});
        cargarUnidades().catch((err) => {
            const body = $('av-unidades-body');
            if (body) {
                body.innerHTML = '<tr><td colspan="7" class="av-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(err.message || 'Error inesperado.') + '</td></tr>';
            }
        });
    }

    function init() {
        $('av-btn-refresh')?.addEventListener('click', () => {
            sincronizarRecolectadasInventario(false).finally(() => reloadAll(false));
        });
        $('av-btn-filtrar')?.addEventListener('click', () => reloadAll(true));
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
                sincronizarRecolectadasInventario(true).finally(() => {
                    reloadAll(true);
                });
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
