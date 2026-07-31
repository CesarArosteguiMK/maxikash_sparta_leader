<?php
$atlasVentasNow = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
$atlasVentasStart = $atlasVentasNow->format('Y-m-01');
$atlasVentasEnd = $atlasVentasNow->format('Y-m-d');
?>

<div class="container-fluid py-3 atlas-sales-page">
    <style>
        .atlas-sales-page { color:#22303e; }
        .atlas-sales-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .atlas-sales-title { display:flex; align-items:center; gap:.65rem; margin:0; color:#173756; font-size:1.35rem; font-weight:900; }
        .atlas-sales-title i { color:#15803d; }
        .atlas-sales-subtitle { margin:.2rem 0 0; color:#64748b; font-size:.86rem; font-weight:700; }
        .atlas-sales-filter-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#f8fafc; padding:.9rem; margin-bottom:.8rem; }
        .atlas-sales-filters { display:grid; grid-template-columns:repeat(2, minmax(9rem, .8fr)) repeat(2, minmax(12rem, 1fr)) minmax(14rem, 1.35fr) auto; gap:.7rem; align-items:end; }
        .atlas-sales-filter-actions { display:flex; align-items:center; gap:.45rem; }
        .atlas-sales-filter-actions .btn { min-height:2.35rem; white-space:nowrap; }
        .atlas-sales-rule { display:flex; align-items:flex-start; gap:.65rem; margin-bottom:1rem; padding:.7rem .85rem; border-left:4px solid #2563eb; background:#eff6ff; color:#36516d; font-size:.76rem; font-weight:700; }
        .atlas-sales-rule i { margin-top:.1rem; color:#2563eb; }
        .atlas-sales-metrics { display:grid; grid-template-columns:repeat(5, minmax(9rem, 1fr)); gap:.7rem; margin-bottom:1rem; }
        .atlas-sales-metric { min-width:0; min-height:5rem; border:1px solid #dbe4ef; border-left:4px solid #64748b; border-radius:.45rem; background:#fff; padding:.72rem .8rem; }
        .atlas-sales-metric.is-green { border-left-color:#16a34a; }
        .atlas-sales-metric.is-blue { border-left-color:#2563eb; }
        .atlas-sales-metric.is-amber { border-left-color:#d97706; }
        .atlas-sales-metric.is-red { border-left-color:#dc2626; }
        .atlas-sales-metric-label { display:flex; align-items:center; gap:.4rem; color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; }
        .atlas-sales-metric-value { margin-top:.28rem; color:#172033; font-size:1.23rem; font-weight:900; line-height:1.1; overflow-wrap:anywhere; }
        .atlas-sales-table-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#fff; overflow:hidden; }
        .atlas-sales-table-head { display:flex; align-items:center; justify-content:space-between; gap:.8rem; padding:.72rem .9rem; border-bottom:1px solid #e5e7eb; }
        .atlas-sales-table-title { margin:0; color:#173756; font-size:.9rem; font-weight:900; }
        .atlas-sales-table-meta { color:#64748b; font-size:.75rem; font-weight:800; }
        .atlas-sales-scroll { overflow:auto; max-height:65vh; }
        .atlas-sales-table { min-width:2520px; margin:0; table-layout:fixed; }
        .atlas-sales-table th { position:sticky; top:0; z-index:2; background:#f8fafc; color:#566a7f; font-size:.67rem; font-weight:900; text-transform:uppercase; white-space:nowrap; }
        .atlas-sales-table td { color:#566a7f; font-size:.75rem; font-weight:700; vertical-align:middle; overflow-wrap:anywhere; }
        .atlas-sales-table th:nth-child(1), .atlas-sales-table td:nth-child(1),
        .atlas-sales-table th:nth-child(2), .atlas-sales-table td:nth-child(2) { width:6rem; }
        .atlas-sales-table th:nth-child(3), .atlas-sales-table td:nth-child(3) { width:15rem; }
        .atlas-sales-table th:nth-child(4), .atlas-sales-table td:nth-child(4),
        .atlas-sales-table th:nth-child(7), .atlas-sales-table td:nth-child(7),
        .atlas-sales-table th:nth-child(8), .atlas-sales-table td:nth-child(8) { width:10.5rem; }
        .atlas-sales-table th:nth-child(5), .atlas-sales-table td:nth-child(5),
        .atlas-sales-table th:nth-child(6), .atlas-sales-table td:nth-child(6) { width:13rem; }
        .atlas-sales-main { color:#22303e; font-weight:900; line-height:1.2; }
        .atlas-sales-sub { margin-top:.15rem; color:#94a3b8; font-size:.66rem; font-weight:800; line-height:1.2; }
        .atlas-sales-stage { display:inline-flex; align-items:center; gap:.32rem; max-width:100%; padding:.22rem .5rem; border-radius:999px; background:#e8f5ee; color:#147a48; font-size:.66rem; font-weight:900; white-space:normal; }
        .atlas-sales-rule-tag { display:inline-flex; align-items:center; gap:.3rem; margin-top:.25rem; color:#2563eb; font-size:.64rem; font-weight:900; }
        .atlas-sales-loading { display:flex; align-items:center; justify-content:center; gap:.65rem; min-height:12rem; color:#64748b; font-size:.82rem; font-weight:800; }
        .atlas-sales-empty { display:grid; place-items:center; min-height:13rem; padding:2rem; color:#64748b; text-align:center; }
        .atlas-sales-empty i { margin-bottom:.6rem; color:#94a3b8; font-size:1.75rem; }
        .atlas-sales-empty strong { display:block; color:#334155; font-size:.9rem; }
        .atlas-sales-empty span { display:block; margin-top:.2rem; font-size:.76rem; font-weight:700; }
        .atlas-sales-pagination { display:flex; align-items:center; justify-content:space-between; gap:.8rem; flex-wrap:wrap; padding:.75rem .9rem; border-top:1px solid #e5e7eb; }
        .atlas-sales-pagination-info { color:#64748b; font-size:.75rem; font-weight:800; }
        .atlas-sales-pagination-actions { display:flex; align-items:center; gap:.4rem; }
        .atlas-sales-page .form-label { color:#566a7f; font-size:.72rem; font-weight:900; }
        .atlas-sales-page .form-control,
        .atlas-sales-page .form-select { min-height:2.35rem; border-color:#d9dee3; color:#334155; font-size:.79rem; }
        .atlas-sales-page .form-control:focus,
        .atlas-sales-page .form-select:focus { border-color:#2563eb; box-shadow:0 0 0 .15rem rgba(37,99,235,.12); }
        .atlas-sales-alert { margin-bottom:1rem; border-radius:.45rem; }
        @media (max-width: 1399.98px) {
            .atlas-sales-filters { grid-template-columns:repeat(3, minmax(10rem, 1fr)); }
            .atlas-sales-metrics { grid-template-columns:repeat(3, minmax(9rem, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .atlas-sales-filters,
            .atlas-sales-metrics { grid-template-columns:1fr; }
            .atlas-sales-filter-actions,
            .atlas-sales-filter-actions .btn { width:100%; }
            .atlas-sales-filter-actions .btn { flex:1; }
            .atlas-sales-head > .btn { width:100%; }
            .atlas-sales-scroll { max-height:58vh; }
        }
    </style>

    <header class="atlas-sales-head">
        <div>
            <h1 class="atlas-sales-title">
                <i class="fa-solid fa-chart-column"></i>
                <span>Ventas</span>
            </h1>
            <p class="atlas-sales-subtitle">Ventas realizadas en Maxi, conciliadas con las reglas vigentes de Atlas.</p>
        </div>
        <button type="button" class="btn btn-success btn-sm" id="atlasSalesExport">
            <i class="fa-solid fa-file-excel me-2"></i>Exportar reporte
        </button>
    </header>

    <form class="atlas-sales-filter-panel" id="atlasSalesFilters" autocomplete="off">
        <div class="atlas-sales-filters">
            <div>
                <label class="form-label mb-1" for="atlasSalesStart">Desde</label>
                <input class="form-control form-control-sm" type="date" id="atlasSalesStart"
                       value="<?= htmlspecialchars($atlasVentasStart, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasSalesEnd">Hasta</label>
                <input class="form-control form-control-sm" type="date" id="atlasSalesEnd"
                       value="<?= htmlspecialchars($atlasVentasEnd, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasSalesDistributor">Distribuidor</label>
                <select class="form-select form-select-sm" id="atlasSalesDistributor">
                    <option value="">Todos los distribuidores</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasSalesBranch">Sucursal</label>
                <select class="form-select form-select-sm" id="atlasSalesBranch">
                    <option value="">Todas las sucursales</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasSalesSearch">Buscar</label>
                <input class="form-control form-control-sm" type="search" id="atlasSalesSearch"
                       placeholder="Cliente, oferta, sucursal o vendedor">
            </div>
            <div class="atlas-sales-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm" id="atlasSalesConsult">
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Consultar
                </button>
            </div>
        </div>
    </form>

    <div class="atlas-sales-rule">
        <i class="fa-solid fa-circle-info"></i>
        <span>La regla vigente del distribuidor tiene prioridad. Cuando no existe una regla particular, la venta se contabiliza al pasar por Por dispersar; si no pas&oacute; por esa etapa, se usa Dispersado y finalmente S2Credit.</span>
    </div>

    <div class="alert alert-danger atlas-sales-alert d-none" role="alert" id="atlasSalesError"></div>

    <section class="atlas-sales-metrics" aria-label="Resumen de ventas">
        <article class="atlas-sales-metric is-green">
            <div class="atlas-sales-metric-label"><i class="fa-solid fa-motorcycle"></i>Unidades vendidas</div>
            <div class="atlas-sales-metric-value" id="atlasSalesUnits">0</div>
        </article>
        <article class="atlas-sales-metric is-blue">
            <div class="atlas-sales-metric-label"><i class="fa-solid fa-money-bill-trend-up"></i>Monto financiado</div>
            <div class="atlas-sales-metric-value" id="atlasSalesFinanced">$0.00</div>
        </article>
        <article class="atlas-sales-metric is-amber">
            <div class="atlas-sales-metric-label"><i class="fa-solid fa-receipt"></i>Enganche</div>
            <div class="atlas-sales-metric-value" id="atlasSalesDownPayment">$0.00</div>
        </article>
        <article class="atlas-sales-metric is-red">
            <div class="atlas-sales-metric-label"><i class="fa-solid fa-tag"></i>Precio de motos</div>
            <div class="atlas-sales-metric-value" id="atlasSalesBikePrice">$0.00</div>
        </article>
        <article class="atlas-sales-metric">
            <div class="atlas-sales-metric-label"><i class="fa-solid fa-store"></i>Sucursales con venta</div>
            <div class="atlas-sales-metric-value" id="atlasSalesBranches">0</div>
        </article>
    </section>

    <section class="atlas-sales-table-panel" aria-label="Detalle de ventas">
        <div class="atlas-sales-table-head">
            <h2 class="atlas-sales-table-title">Detalle de ventas</h2>
            <div class="atlas-sales-table-meta" id="atlasSalesTableMeta">Preparando consulta...</div>
        </div>
        <div class="atlas-sales-scroll">
            <table class="table table-sm table-hover atlas-sales-table">
                <thead>
                    <tr>
                        <th>Cliente ID</th>
                        <th>Oferta ID</th>
                        <th>Cliente</th>
                        <th>Fecha de venta</th>
                        <th>Sucursal</th>
                        <th>Distribuidor</th>
                        <th>Fecha de oferta</th>
                        <th>Fecha etapa actual</th>
                        <th>Etapa</th>
                        <th>Precio moto</th>
                        <th>Enganche</th>
                        <th>Monto financiado</th>
                        <th>Semanas</th>
                        <th>Oferta</th>
                        <th>Modelo</th>
                        <th>Marca</th>
                        <th>Usuario</th>
                        <th>Vendedor</th>
                        <th>Sucursal ID</th>
                        <th>Distribuidor ID</th>
                    </tr>
                </thead>
                <tbody id="atlasSalesRows">
                    <tr><td colspan="20"><div class="atlas-sales-loading"><span class="spinner-border spinner-border-sm"></span>Consultando ventas...</div></td></tr>
                </tbody>
            </table>
        </div>
        <footer class="atlas-sales-pagination">
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="atlasSalesPageSize">Mostrar</label>
                <select class="form-select form-select-sm" id="atlasSalesPageSize" style="width:5.25rem">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="atlas-sales-pagination-info" id="atlasSalesPaginationInfo">P&aacute;gina 1 de 1</div>
            <div class="atlas-sales-pagination-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="atlasSalesPrevious" title="P&aacute;gina anterior" aria-label="P&aacute;gina anterior">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="atlasSalesNext" title="P&aacute;gina siguiente" aria-label="P&aacute;gina siguiente">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </footer>
    </section>
</div>

<script>
(() => {
    const elements = {
        form: document.getElementById('atlasSalesFilters'),
        start: document.getElementById('atlasSalesStart'),
        end: document.getElementById('atlasSalesEnd'),
        distributor: document.getElementById('atlasSalesDistributor'),
        branch: document.getElementById('atlasSalesBranch'),
        search: document.getElementById('atlasSalesSearch'),
        consult: document.getElementById('atlasSalesConsult'),
        export: document.getElementById('atlasSalesExport'),
        pageSize: document.getElementById('atlasSalesPageSize'),
        previous: document.getElementById('atlasSalesPrevious'),
        next: document.getElementById('atlasSalesNext'),
        rows: document.getElementById('atlasSalesRows'),
        error: document.getElementById('atlasSalesError'),
        tableMeta: document.getElementById('atlasSalesTableMeta'),
        paginationInfo: document.getElementById('atlasSalesPaginationInfo'),
        units: document.getElementById('atlasSalesUnits'),
        financed: document.getElementById('atlasSalesFinanced'),
        downPayment: document.getElementById('atlasSalesDownPayment'),
        bikePrice: document.getElementById('atlasSalesBikePrice'),
        branches: document.getElementById('atlasSalesBranches'),
    };
    const state = {
        page: 1,
        totalPages: 1,
        total: 0,
        request: null,
        catalogsLoaded: false,
        branches: [],
    };
    const currency = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    const integer = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 0 });

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const formatDate = (value) => {
        const text = String(value || '').trim();
        if (!text) return 'Sin dato';
        const normalized = text.includes('T') ? text : text.replace(' ', 'T');
        const parsed = new Date(normalized);
        if (Number.isNaN(parsed.getTime())) return text;
        return new Intl.DateTimeFormat('es-MX', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
        }).format(parsed);
    };

    const ruleLabel = (value) => {
        const rules = {
            POR_DISPERSAR: 'Por dispersar',
            DISPERSADO: 'Dispersado',
            S2CREDIT: 'S2Credit',
            ACTIVACION_S2: 'Activaci\u00f3n S2Credit',
        };
        return rules[String(value || '').toUpperCase()] || 'Regla vigente';
    };

    const setLoading = (loading) => {
        elements.consult.disabled = loading;
        elements.export.disabled = loading;
        elements.pageSize.disabled = loading;
        if (loading) {
            elements.rows.innerHTML = '<tr><td colspan="20"><div class="atlas-sales-loading"><span class="spinner-border spinner-border-sm"></span>Consultando ventas...</div></td></tr>';
            elements.tableMeta.textContent = 'Consultando...';
        }
    };

    const showError = (message = '') => {
        elements.error.textContent = message;
        elements.error.classList.toggle('d-none', !message);
    };

    const paramsFromFilters = (withPaging = true) => {
        const params = new URLSearchParams();
        params.set('fecha_inicio', elements.start.value);
        params.set('fecha_fin', elements.end.value);
        const distributor = elements.distributor.value || elements.distributor.dataset.pending || '';
        const branch = elements.branch.value || elements.branch.dataset.pending || '';
        if (distributor) params.set('fk_distribuidor', distributor);
        if (branch) params.set('fk_sucursal', branch);
        if (elements.search.value.trim()) params.set('search', elements.search.value.trim());
        if (withPaging) {
            params.set('page', String(state.page));
            params.set('page_size', elements.pageSize.value);
        }
        return params;
    };

    const optionHtml = (value, label) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`;

    const renderBranches = () => {
        const selected = elements.branch.value;
        const distributor = Number(elements.distributor.value || 0);
        const options = state.branches.filter((item) => !distributor || Number(item.fk_distribuidor) === distributor);
        elements.branch.innerHTML = optionHtml('', 'Todas las sucursales')
            + options.map((item) => optionHtml(item.id, `${item.nombre} (#${item.id})`)).join('');
        if (options.some((item) => String(item.id) === selected)) elements.branch.value = selected;
    };

    const renderCatalogs = (catalogs) => {
        if (state.catalogsLoaded || !catalogs) return;
        const distributors = Array.isArray(catalogs.distribuidores) ? catalogs.distribuidores : [];
        state.branches = Array.isArray(catalogs.sucursales) ? catalogs.sucursales : [];
        const selectedDistributor = elements.distributor.value;
        elements.distributor.innerHTML = optionHtml('', 'Todos los distribuidores')
            + distributors.map((item) => optionHtml(item.id, `${item.nombre} (#${item.id})`)).join('');
        if (distributors.some((item) => String(item.id) === selectedDistributor)) {
            elements.distributor.value = selectedDistributor;
        }
        renderBranches();
        state.catalogsLoaded = true;
        applyPendingCatalogFilters();
    };

    const renderSummary = (summary = {}) => {
        elements.units.textContent = integer.format(Number(summary.unidades_vendidas || 0));
        elements.financed.textContent = currency.format(Number(summary.monto_financiado || 0));
        elements.downPayment.textContent = currency.format(Number(summary.enganche || 0));
        elements.bikePrice.textContent = currency.format(Number(summary.precio_motos || 0));
        elements.branches.textContent = integer.format(Number(summary.sucursales || 0));
    };

    const renderRows = (rows) => {
        if (!Array.isArray(rows) || rows.length === 0) {
            elements.rows.innerHTML = `
                <tr><td colspan="20">
                    <div class="atlas-sales-empty">
                        <div>
                            <i class="fa-solid fa-receipt"></i>
                            <strong>Sin ventas en el periodo</strong>
                            <span>Ajusta el rango o los filtros y vuelve a consultar.</span>
                        </div>
                    </div>
                </td></tr>`;
            return;
        }
        elements.rows.innerHTML = rows.map((row) => `
            <tr>
                <td>${escapeHtml(row.id_persona)}</td>
                <td><span class="atlas-sales-main">#${escapeHtml(row.id_oferta)}</span></td>
                <td><span class="atlas-sales-main">${escapeHtml(row.nombre_cliente || 'Sin nombre')}</span></td>
                <td>
                    <span class="atlas-sales-main">${escapeHtml(formatDate(row.fecha_contabilizacion_venta))}</span>
                    <span class="atlas-sales-rule-tag"><i class="fa-solid fa-check"></i>${escapeHtml(ruleLabel(row.criterio_fecha_venta))}</span>
                </td>
                <td>
                    <span class="atlas-sales-main">${escapeHtml(row.sucursal || 'Sin sucursal')}</span>
                    <span class="atlas-sales-sub">ID ${escapeHtml(row.pk_sucursal)}</span>
                </td>
                <td>
                    <span class="atlas-sales-main">${escapeHtml(row.distribuidor || 'Sin distribuidor')}</span>
                    <span class="atlas-sales-sub">ID ${escapeHtml(row.fk_distribuidor)}</span>
                </td>
                <td>${escapeHtml(formatDate(row.fecha_oferta))}</td>
                <td>${escapeHtml(formatDate(row.fecha_etapa_actual))}</td>
                <td><span class="atlas-sales-stage"><i class="fa-solid fa-flag"></i>${escapeHtml(row.etapa || 'Sin etapa')}</span></td>
                <td>${escapeHtml(currency.format(Number(row.precio_moto || 0)))}</td>
                <td>${escapeHtml(currency.format(Number(row.enganche || 0)))}</td>
                <td><span class="atlas-sales-main">${escapeHtml(currency.format(Number(row.monto_financiar || 0)))}</span></td>
                <td>${escapeHtml(row.semanas || 'Sin dato')}</td>
                <td>${escapeHtml(row.oferta || 'Sin dato')}</td>
                <td>${escapeHtml(row.modelo_moto || 'Sin dato')}</td>
                <td>${escapeHtml(row.marca_moto || 'Sin dato')}</td>
                <td>${escapeHtml(row.usuario || 'Sin dato')}</td>
                <td>${escapeHtml(row.nombre_vendedor || 'Sin dato')}</td>
                <td>${escapeHtml(row.pk_sucursal)}</td>
                <td>${escapeHtml(row.fk_distribuidor)}</td>
            </tr>
        `).join('');
    };

    const renderPagination = (pagination = {}) => {
        state.page = Number(pagination.page || 1);
        state.totalPages = Number(pagination.total_pages || 1);
        state.total = Number(pagination.total || 0);
        elements.paginationInfo.textContent = `P\u00e1gina ${state.page} de ${state.totalPages} \u00b7 ${integer.format(state.total)} ventas`;
        elements.tableMeta.textContent = `${integer.format(state.total)} ventas encontradas`;
        elements.previous.disabled = state.page <= 1;
        elements.next.disabled = state.page >= state.totalPages;
    };

    const syncUrl = () => {
        const params = paramsFromFilters(true);
        history.replaceState(null, '', `${location.pathname}?${params.toString()}`);
    };

    const loadSales = async () => {
        if (!elements.start.value || !elements.end.value) {
            showError('Selecciona una fecha inicial y una fecha final.');
            return;
        }
        if (elements.start.value > elements.end.value) {
            showError('La fecha inicial no puede ser posterior a la fecha final.');
            return;
        }
        if (state.request) state.request.abort();
        const request = new AbortController();
        state.request = request;
        setLoading(true);
        showError('');

        try {
            const params = paramsFromFilters(true);
            const response = await fetch(`/Atlas/getVentas?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                signal: request.signal,
                cache: 'no-store',
            });
            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload?.success) {
                throw new Error(payload?.mensaje || 'No se pudo consultar Ventas.');
            }
            const data = payload.datos || {};
            renderCatalogs(data.catalogos || {});
            renderSummary(data.resumen || {});
            renderRows(data.filas || []);
            renderPagination(data.paginacion || {});
            syncUrl();
        } catch (error) {
            if (error.name === 'AbortError') return;
            showError(error.message || 'No se pudo consultar Ventas.');
            renderSummary({});
            renderRows([]);
            renderPagination({ page: 1, total_pages: 1, total: 0 });
        } finally {
            if (state.request === request) {
                setLoading(false);
                state.request = null;
            }
        }
    };

    const restoreFiltersFromUrl = () => {
        const params = new URLSearchParams(location.search);
        if (/^\d{4}-\d{2}-\d{2}$/.test(params.get('fecha_inicio') || '')) elements.start.value = params.get('fecha_inicio');
        if (/^\d{4}-\d{2}-\d{2}$/.test(params.get('fecha_fin') || '')) elements.end.value = params.get('fecha_fin');
        if (params.get('fk_distribuidor')) elements.distributor.dataset.pending = params.get('fk_distribuidor');
        if (params.get('fk_sucursal')) elements.branch.dataset.pending = params.get('fk_sucursal');
        if (params.get('search')) elements.search.value = params.get('search');
        if (['25', '50', '100'].includes(params.get('page_size'))) elements.pageSize.value = params.get('page_size');
        state.page = Math.max(1, Number(params.get('page') || 1));
    };

    const applyPendingCatalogFilters = () => {
        const distributor = elements.distributor.dataset.pending || '';
        const branch = elements.branch.dataset.pending || '';
        if (distributor && [...elements.distributor.options].some((option) => option.value === distributor)) {
            elements.distributor.value = distributor;
            renderBranches();
        }
        if (branch && [...elements.branch.options].some((option) => option.value === branch)) {
            elements.branch.value = branch;
        }
        delete elements.distributor.dataset.pending;
        delete elements.branch.dataset.pending;
    };

    elements.form.addEventListener('submit', (event) => {
        event.preventDefault();
        state.page = 1;
        loadSales();
    });
    elements.distributor.addEventListener('change', () => {
        renderBranches();
        state.page = 1;
    });
    elements.pageSize.addEventListener('change', () => {
        state.page = 1;
        loadSales();
    });
    elements.previous.addEventListener('click', () => {
        if (state.page <= 1) return;
        state.page--;
        loadSales();
    });
    elements.next.addEventListener('click', () => {
        if (state.page >= state.totalPages) return;
        state.page++;
        loadSales();
    });
    elements.export.addEventListener('click', () => {
        const params = paramsFromFilters(false);
        window.location.assign(`/Atlas/exportarVentas?${params.toString()}`);
    });

    restoreFiltersFromUrl();
    loadSales();
})();
</script>
