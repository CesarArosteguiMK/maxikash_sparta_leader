<?php
$atlasVentasNow = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
$atlasVentasStart = $atlasVentasNow->format('Y-m-01');
$atlasVentasEnd = $atlasVentasNow->format('Y-m-d');
$atlasVentasHeaders = [
    'Cliente',
    'Fecha de dispersión',
    'Sucursal / Distribuidor',
    'Fecha de oferta',
    'Fecha de etapa actual',
    'Detalles',
];
$atlasVentasColumnCount = count($atlasVentasHeaders);
?>

<div class="container-fluid py-3 atlas-sales-page is-loading" id="atlasSalesPage">
    <style>
        .atlas-sales-page { color:#22303e; }
        .atlas-sales-page.is-loading .atlas-sales-workspace { visibility:hidden; }
        .atlas-sales-page:not(.is-loading) .atlas-sales-inline-loader { display:none; }
        .atlas-sales-inline-loader { display:grid; min-height:18rem; place-items:center; color:#64748b; }
        .atlas-sales-inline-loader-content { display:flex; align-items:center; gap:.7rem; font-size:.84rem; font-weight:800; }
        .atlas-sales-workspace { visibility:visible; }
        .atlas-sales-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .atlas-sales-head-actions { display:flex; align-items:center; gap:.5rem; }
        .atlas-sales-title { display:flex; align-items:center; gap:.65rem; margin:0; color:#173756; font-size:1.35rem; font-weight:900; }
        .atlas-sales-title i { color:#15803d; }
        .atlas-sales-subtitle { margin:.2rem 0 0; color:#64748b; font-size:.86rem; font-weight:700; }
        .atlas-sales-filter-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#f8fafc; padding:.9rem; margin-bottom:.8rem; }
        .atlas-sales-filters { display:grid; grid-template-columns:minmax(15rem, .95fr) repeat(2, minmax(12rem, 1fr)) minmax(16rem, 1.35fr); gap:.7rem; align-items:end; }
        .atlas-sales-filters-secondary { margin-top:.75rem; padding-top:.75rem; border-top:1px solid #e2e8f0; }
        .atlas-sales-date-control .input-group-text { border-color:#d9dee3; background:#fff; color:#2563eb; }
        .atlas-sales-date-control .form-control { background:#fff; cursor:pointer; }
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
        .atlas-sales-table { min-width:1080px; margin:0; table-layout:fixed; }
        .atlas-sales-table th { position:sticky; top:0; z-index:2; background:#f8fafc; color:#566a7f; font-size:.67rem; font-weight:900; white-space:nowrap; }
        .atlas-sales-table td { color:#566a7f; font-size:.75rem; font-weight:700; vertical-align:middle; overflow-wrap:anywhere; transition:background-color .15s ease; }
        .atlas-sales-table tbody tr:hover td { background:#f6f9ff; }
        .atlas-sales-table th:nth-child(1), .atlas-sales-table td:nth-child(1) { width:15rem; }
        .atlas-sales-table th:nth-child(2), .atlas-sales-table td:nth-child(2),
        .atlas-sales-table th:nth-child(4), .atlas-sales-table td:nth-child(4) { width:10rem; }
        .atlas-sales-table th:nth-child(3), .atlas-sales-table td:nth-child(3) { width:14rem; }
        .atlas-sales-table th:nth-child(5), .atlas-sales-table td:nth-child(5) { width:12rem; }
        .atlas-sales-table th:nth-child(6), .atlas-sales-table td:nth-child(6) { width:5.5rem; text-align:center; }
        .atlas-sales-main { color:#22303e; font-weight:900; line-height:1.2; }
        .atlas-sales-sub { display:block; margin-top:.2rem; color:#8a99aa; font-size:.68rem; font-weight:700; line-height:1.2; }
        .atlas-sales-client-cell { display:flex; align-items:flex-start; gap:.55rem; min-width:0; }
        .atlas-sales-client-content { min-width:0; }
        .atlas-sales-cell-icon { display:inline-grid; width:1.75rem; height:1.75rem; flex:0 0 1.75rem; place-items:center; border-radius:.4rem; font-size:.72rem; }
        .atlas-sales-data-line { display:flex; align-items:flex-start; gap:.38rem; line-height:1.3; }
        .atlas-sales-data-line > i { margin-top:.08rem; flex:0 0 auto; font-size:.72rem; }
        .atlas-sales-location-label { display:flex; align-items:center; gap:.35rem; color:#8a99aa; font-size:.64rem; font-weight:900; text-transform:uppercase; }
        .atlas-sales-location-value { display:block; margin-top:.15rem; color:#22303e; font-weight:900; line-height:1.25; }
        .atlas-sales-location-divider { margin:.55rem 0; border-top:1px solid #dfe5ec; }
        .atlas-sales-stage { display:inline-flex; align-items:center; gap:.32rem; max-width:100%; margin-top:.35rem; padding:.24rem .52rem; border-radius:999px; font-size:.66rem; font-weight:900; line-height:1.2; }
        .atlas-sales-stage.is-info { background:#e8f5ff; color:#0877a8; }
        .atlas-sales-stage.is-warning { background:#fff4dc; color:#a86400; }
        .atlas-sales-stage.is-success { background:#e8f7ef; color:#177847; }
        .atlas-sales-stage.is-danger { background:#ffeded; color:#c72e2e; }
        .atlas-sales-stage.is-secondary { background:#edf0f4; color:#66788a; }
        .atlas-sales-tone-primary { background:#edf3ff; color:#2563eb; }
        .atlas-sales-tone-info { background:#e8f7fb; color:#0f8297; }
        .atlas-sales-tone-success { background:#e9f7ef; color:#16834d; }
        .atlas-sales-tone-warning { background:#fff4dc; color:#b56b00; }
        .atlas-sales-tone-danger { background:#ffeded; color:#d53a3a; }
        .atlas-sales-tone-secondary { background:#edf0f4; color:#66788a; }
        .atlas-sales-text-primary { color:#2563eb; }
        .atlas-sales-text-info { color:#0f8297; }
        .atlas-sales-text-success { color:#16834d; }
        .atlas-sales-details-button { width:2rem; height:2rem; padding:0; }
        .atlas-sales-detail-header { min-width:0; }
        .atlas-sales-detail-client { margin:0; color:var(--bs-heading-color, #384551); font-size:1.3rem; font-weight:600; overflow-wrap:anywhere; }
        .atlas-sales-detail-offer { margin:.15rem 0 0; color:#8a99aa; font-size:.72rem; font-weight:700; }
        .atlas-sales-detail-section + .atlas-sales-detail-section { margin-top:1.25rem; padding-top:1rem; border-top:1px solid #e5e7eb; }
        .atlas-sales-detail-section-title { margin:0 0 .9rem; color:var(--bs-heading-color, #384551); font-size:.975rem; font-weight:600; text-transform:uppercase; }
        .atlas-sales-detail-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.75rem 1rem; margin:0; }
        .atlas-sales-detail-item { display:grid; grid-template-columns:2rem minmax(0, 1fr); align-items:center; gap:.55rem; min-width:0; }
        .atlas-sales-detail-item-icon { display:inline-grid; width:2rem; height:2rem; place-items:center; border-radius:.45rem; font-size:.76rem; }
        .atlas-sales-detail-item-content { min-width:0; }
        .atlas-sales-detail-item dt { margin:0 0 .16rem; color:#8a99aa; font-size:.68rem; font-weight:800; }
        .atlas-sales-detail-item dd { margin:0; color:#22303e; font-size:.82rem; font-weight:800; overflow-wrap:anywhere; }
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
        .atlas-sales-page .select2-container { width:100% !important; }
        .atlas-sales-page .select2-container .select2-selection--single { min-height:2.35rem; display:flex; align-items:center; border-color:#d9dee3; }
        .atlas-sales-page .select2-container--default .select2-selection--single .select2-selection__rendered { width:100%; padding-left:.75rem; color:#334155; font-size:.79rem; }
        .atlas-sales-page .select2-container--default .select2-selection--single .select2-selection__arrow { height:2.25rem; }
        .atlas-sales-page .select2-container--default.select2-container--focus .select2-selection--single,
        .atlas-sales-page .select2-container--default.select2-container--open .select2-selection--single { border-color:#2563eb; box-shadow:0 0 0 .15rem rgba(37,99,235,.12); }
        .atlas-sales-alert { margin-bottom:1rem; border-radius:.45rem; }
        .atlas-sales-calendar-footer { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.65rem .75rem; border-top:1px solid #e5e7eb; background:#fff; }
        .atlas-sales-calendar-footer .btn { min-width:5.5rem; }
        .flatpickr-calendar .flatpickr-day.inRange,
        .flatpickr-calendar .flatpickr-day.prevMonthDay.inRange,
        .flatpickr-calendar .flatpickr-day.nextMonthDay.inRange { border-color:#dbeafe; background:#dbeafe; box-shadow:-5px 0 0 #dbeafe, 5px 0 0 #dbeafe; color:#1d4ed8; }
        .flatpickr-calendar .flatpickr-day.selected,
        .flatpickr-calendar .flatpickr-day.startRange,
        .flatpickr-calendar .flatpickr-day.endRange { border-color:#2563eb; background:#2563eb; color:#fff; }
        @media (max-width: 1399.98px) {
            .atlas-sales-filters { grid-template-columns:repeat(2, minmax(12rem, 1fr)); }
            .atlas-sales-metrics { grid-template-columns:repeat(3, minmax(9rem, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .atlas-sales-filters,
            .atlas-sales-metrics { grid-template-columns:1fr; }
            .atlas-sales-head-actions { width:100%; }
            .atlas-sales-head-actions .btn-success { flex:1; }
            .atlas-sales-scroll { max-height:58vh; }
            .atlas-sales-detail-grid { grid-template-columns:1fr; }
        }
    </style>

    <div class="atlas-sales-inline-loader" role="status" aria-live="polite">
        <div class="atlas-sales-inline-loader-content">
            <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
            <span>Cargando ventas...</span>
        </div>
    </div>

    <div class="atlas-sales-workspace" id="atlasSalesWorkspace" aria-busy="true">
    <header class="atlas-sales-head">
        <div>
            <h1 class="atlas-sales-title">
                <i class="fa-solid fa-chart-column"></i>
                <span>Ventas</span>
            </h1>
            <p class="atlas-sales-subtitle">Ventas realizadas en Maxi, conciliadas con las reglas vigentes de Atlas.</p>
        </div>
        <div class="atlas-sales-head-actions">
            <button type="button" class="btn btn-outline-primary btn-sm" id="atlasSalesRefresh"
                    title="Actualizar datos" aria-label="Actualizar datos">
                <i class="fa-solid fa-rotate"></i>
            </button>
            <button type="button" class="btn btn-success btn-sm" id="atlasSalesExport">
                <i class="fa-solid fa-file-excel me-2"></i>Exportar reporte
            </button>
        </div>
    </header>

    <div class="atlas-sales-filter-panel" id="atlasSalesFilters">
        <div class="atlas-sales-filters">
            <div class="atlas-sales-date-control">
                <label class="form-label mb-1" for="atlasSalesDates">Fechas</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
                    <input class="form-control form-control-sm" type="text" id="atlasSalesDates"
                           data-start="<?= htmlspecialchars($atlasVentasStart, ENT_QUOTES, 'UTF-8') ?>"
                           data-end="<?= htmlspecialchars($atlasVentasEnd, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Todo el hist&oacute;rico" readonly>
                </div>
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
        </div>
        <div class="atlas-sales-filters atlas-sales-filters-secondary">
            <div>
                <label class="form-label mb-1" for="atlasSalesStage">Etapa actual</label>
                <select class="form-select form-select-sm" id="atlasSalesStage">
                    <option value="">Todas las etapas</option>
                </select>
            </div>
        </div>
    </div>

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
                        <?php foreach ($atlasVentasHeaders as $header): ?>
                            <th><?= htmlspecialchars($header, ENT_QUOTES, 'UTF-8') ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody id="atlasSalesRows">
                    <tr><td colspan="<?= $atlasVentasColumnCount ?>"><div class="atlas-sales-loading"><span class="spinner-border spinner-border-sm"></span>Consultando ventas...</div></td></tr>
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

    <div class="modal fade" id="atlasSalesDetailsModal" tabindex="-1"
         aria-labelledby="atlasSalesDetailsTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="atlas-sales-detail-header">
                        <h2 class="modal-title atlas-sales-detail-client" id="atlasSalesDetailsTitle">Detalle de venta</h2>
                        <p class="atlas-sales-detail-offer" id="atlasSalesDetailsOffer">ID de oferta: Sin dato</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="atlasSalesDetailsContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (window.location.pathname.toLowerCase() === '/atlas/ventas'
    && (window.location.search || window.location.hash)) {
    window.history.replaceState(null, '', '/Atlas/ventas');
}

const initializeAtlasSales = () => {
    const elements = {
        page: document.getElementById('atlasSalesPage'),
        workspace: document.getElementById('atlasSalesWorkspace'),
        dates: document.getElementById('atlasSalesDates'),
        distributor: document.getElementById('atlasSalesDistributor'),
        branch: document.getElementById('atlasSalesBranch'),
        stage: document.getElementById('atlasSalesStage'),
        search: document.getElementById('atlasSalesSearch'),
        refresh: document.getElementById('atlasSalesRefresh'),
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
        detailsTitle: document.getElementById('atlasSalesDetailsTitle'),
        detailsOffer: document.getElementById('atlasSalesDetailsOffer'),
        detailsContent: document.getElementById('atlasSalesDetailsContent'),
    };
    const state = {
        page: 1,
        totalPages: 1,
        total: 0,
        request: null,
        allRows: [],
        filteredRows: [],
        branches: [],
        filterFrame: null,
        picker: null,
        start: elements.dates.dataset.start,
        end: elements.dates.dataset.end,
        minDate: elements.dates.dataset.start,
        maxDate: elements.dates.dataset.end,
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
        const databaseDate = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?/.exec(text);
        if (databaseDate) {
            const [, year, month, day, hour, minute, second] = databaseDate;
            return `${day}/${month}/${year}${hour ? ` ${hour}:${minute}:${second || '00'}` : ''}`;
        }
        const normalized = text.includes('T') ? text : text.replace(' ', 'T');
        const parsed = new Date(normalized);
        if (Number.isNaN(parsed.getTime())) return text;
        return new Intl.DateTimeFormat('es-MX', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
        }).format(parsed).replace(',', '');
    };

    const dateFromIso = (value) => {
        const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(value || ''));
        return match ? new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3])) : null;
    };

    const dateToIso = (value) => [
        value.getFullYear(),
        String(value.getMonth() + 1).padStart(2, '0'),
        String(value.getDate()).padStart(2, '0'),
    ].join('-');

    const normalizeSearch = (value) => String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('es-MX')
        .trim();

    const setLoading = (loading) => {
        elements.refresh.disabled = loading;
        elements.export.disabled = loading;
        elements.pageSize.disabled = loading;
        elements.workspace.setAttribute('aria-busy', loading ? 'true' : 'false');
        elements.page.classList.toggle('is-loading', loading);
    };

    const showPreload = () => {
        if (typeof Swal === 'undefined') return null;
        const existing = window.__atlasVentasPreload;
        if (existing?.active && Number.isFinite(existing.startedAt) && Swal.isVisible()) {
            return existing.startedAt;
        }
        const startedAt = performance.now();
        window.__atlasVentasPreload = { active: true, startedAt };
        Swal.fire({
            title: 'Cargando ventas',
            text: 'Preparando la informaci\u00f3n...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });
        return startedAt;
    };

    const closePreload = async (startedAt) => {
        if (startedAt !== null) {
            const remaining = 500 - (performance.now() - startedAt);
            if (remaining > 0) {
                await new Promise((resolve) => setTimeout(resolve, remaining));
            }
        }
        if (typeof Swal !== 'undefined' && Swal.isVisible()) Swal.close();
        window.__atlasVentasPreload = null;
    };

    const showError = (message = '') => {
        elements.error.textContent = message;
        elements.error.classList.toggle('d-none', !message);
    };

    const paramsFromFilters = () => {
        const params = new URLSearchParams();
        if (state.start && state.end) {
            params.set('fecha_inicio', state.start);
            params.set('fecha_fin', state.end);
        } else {
            params.set('historico', '1');
        }
        const distributor = elements.distributor.value || '';
        const branch = elements.branch.value || '';
        const stage = elements.stage.value || '';
        if (distributor) params.set('fk_distribuidor', distributor);
        if (branch) params.set('fk_sucursal', branch);
        if (stage) params.set('etapa', stage);
        if (elements.search.value.trim()) params.set('search', elements.search.value.trim());
        return params;
    };

    const optionHtml = (value, label) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`;

    const notifySelectChanged = (element) => {
        if (window.jQuery?.fn?.select2 && window.jQuery(element).hasClass('select2-hidden-accessible')) {
            window.jQuery(element).trigger('change.select2');
        }
    };

    const renderBranches = () => {
        const selected = elements.branch.value;
        const distributor = Number(elements.distributor.value || 0);
        const options = state.branches.filter((item) => !distributor || Number(item.fk_distribuidor) === distributor);
        elements.branch.innerHTML = optionHtml('', 'Todas las sucursales')
            + options.map((item) => optionHtml(item.id, `${item.nombre} (#${item.id})`)).join('');
        elements.branch.value = options.some((item) => String(item.id) === selected) ? selected : '';
        notifySelectChanged(elements.branch);
    };

    const stageKey = (value) => String(value || '').trim().toUpperCase();
    const stagePresentations = {
        S2CREDIT: { label: 'S2Credit', icon: 'fa-solid fa-credit-card', tone: 'info' },
        'POR DISPERSAR': { label: 'Por dispersar', icon: 'fa-solid fa-hourglass-half', tone: 'warning' },
        DISPERSADO: { label: 'Dispersado', icon: 'fa-solid fa-money-bill-transfer', tone: 'success' },
        CANCELADO: { label: 'Cancelado', icon: 'fa-solid fa-circle-xmark', tone: 'danger' },
        CANCELADA: { label: 'Cancelada', icon: 'fa-solid fa-circle-xmark', tone: 'danger' },
        APROBADO: { label: 'Aprobado', icon: 'fa-solid fa-circle-check', tone: 'success' },
        RECHAZADO: { label: 'Rechazado', icon: 'fa-solid fa-ban', tone: 'danger' },
    };

    const stagePresentation = (value) => {
        const stage = stageKey(value);
        const fallbackLabel = stage
            ? stage.toLocaleLowerCase('es-MX').replaceAll('_', ' ').replace(/^./, (letter) => letter.toUpperCase())
            : 'Sin etapa';
        return stagePresentations[stage] || {
            label: fallbackLabel,
            icon: 'fa-solid fa-flag',
            tone: 'secondary',
        };
    };

    const renderStages = (rows) => {
        const selected = elements.stage.value;
        const stages = [...new Set(rows.map((row) => stageKey(row.etapa)).filter(Boolean))]
            .map((value) => ({ value, label: stagePresentation(value).label }))
            .sort((a, b) => a.label.localeCompare(b.label, 'es-MX'));
        elements.stage.innerHTML = optionHtml('', 'Todas las etapas')
            + stages.map((item) => optionHtml(item.value, item.label)).join('');
        elements.stage.value = stages.some((item) => item.value === selected) ? selected : '';
        notifySelectChanged(elements.stage);
    };

    const renderCatalogs = (rows) => {
        const distributorMap = new Map();
        const branchMap = new Map();
        rows.forEach((row) => {
            const distributorId = Number(row.fk_distribuidor || 0);
            const branchId = Number(row.pk_sucursal || 0);
            if (distributorId > 0) {
                distributorMap.set(distributorId, {
                    id: distributorId,
                    nombre: row.distribuidor || `Distribuidor ${distributorId}`,
                });
            }
            if (branchId > 0) {
                branchMap.set(branchId, {
                    id: branchId,
                    nombre: row.sucursal || `Sucursal ${branchId}`,
                    fk_distribuidor: distributorId,
                });
            }
        });
        const distributors = [...distributorMap.values()]
            .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es-MX'));
        state.branches = [...branchMap.values()]
            .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es-MX'));
        const selectedDistributor = elements.distributor.value;
        elements.distributor.innerHTML = optionHtml('', 'Todos los distribuidores')
            + distributors.map((item) => optionHtml(item.id, `${item.nombre} (#${item.id})`)).join('');
        if (distributors.some((item) => String(item.id) === selectedDistributor)) {
            elements.distributor.value = selectedDistributor;
        }
        notifySelectChanged(elements.distributor);
        renderBranches();
        renderStages(rows);
    };

    const renderSummary = (summary = {}) => {
        elements.units.textContent = integer.format(Number(summary.unidades_vendidas || 0));
        elements.financed.textContent = currency.format(Number(summary.monto_financiado || 0));
        elements.downPayment.textContent = currency.format(Number(summary.enganche || 0));
        elements.bikePrice.textContent = currency.format(Number(summary.precio_motos || 0));
        elements.branches.textContent = integer.format(Number(summary.sucursales || 0));
    };

    const summarizeRows = (rows) => {
        const branches = new Set();
        return rows.reduce((summary, row) => {
            summary.unidades_vendidas++;
            summary.monto_financiado += Number(row.monto_financiar || 0);
            summary.enganche += Number(row.enganche || 0);
            summary.precio_motos += Number(row.precio_moto || 0);
            if (Number(row.pk_sucursal || 0) > 0) branches.add(Number(row.pk_sucursal));
            summary.sucursales = branches.size;
            return summary;
        }, {
            unidades_vendidas: 0,
            monto_financiado: 0,
            enganche: 0,
            precio_motos: 0,
            sucursales: 0,
        });
    };

    const prepareRows = (rows) => rows.map((row, detailIndex) => ({
        ...row,
        _detailIndex: detailIndex,
        _saleDate: String(row.fecha_contabilizacion_venta || '').slice(0, 10),
        _stage: stageKey(row.etapa),
        _search: normalizeSearch([
            row.id_persona,
            row.id_oferta,
            row.nombre_cliente,
            row.sucursal,
            row.distribuidor,
            row.etapa,
            row.oferta,
            row.modelo_moto,
            row.marca_moto,
            row.usuario,
            row.nombre_vendedor,
            row.pk_sucursal,
            row.fk_distribuidor,
        ].join(' ')),
    }));

    const stageHtml = (value) => {
        const presentation = stagePresentation(value);
        return `<span class="atlas-sales-stage is-${presentation.tone}">
            <i class="${presentation.icon}"></i>${escapeHtml(presentation.label)}
        </span>`;
    };

    const detailItem = (label, value, icon, tone = 'secondary') => `
        <div class="atlas-sales-detail-item">
            <span class="atlas-sales-detail-item-icon atlas-sales-tone-${escapeHtml(tone)}">
                <i class="${escapeHtml(icon)}"></i>
            </span>
            <div class="atlas-sales-detail-item-content">
                <dt>${escapeHtml(label)}</dt>
                <dd>${escapeHtml(value === null || value === undefined || value === '' ? 'Sin dato' : value)}</dd>
            </div>
        </div>`;

    const detailSection = (title, items) => `
        <section class="atlas-sales-detail-section">
            <h3 class="atlas-sales-detail-section-title">${escapeHtml(title)}</h3>
            <dl class="atlas-sales-detail-grid">${items.join('')}</dl>
        </section>`;

    const renderDetails = (row) => {
        elements.detailsTitle.textContent = row.nombre_cliente || 'Detalle de venta';
        elements.detailsOffer.textContent = `ID de oferta: ${row.id_oferta || 'Sin dato'}`;
        elements.detailsContent.innerHTML = [
            detailSection('Cr\u00e9dito', [
                detailItem('Precio de moto', currency.format(Number(row.precio_moto || 0)), 'fa-solid fa-tags', 'danger'),
                detailItem('Enganche', currency.format(Number(row.enganche || 0)), 'fa-solid fa-hand-holding-dollar', 'success'),
                detailItem('Monto financiado', currency.format(Number(row.monto_financiar || 0)), 'fa-solid fa-money-bill-transfer', 'primary'),
                detailItem('Plazo', row.semanas ? `${row.semanas} semanas` : 'Sin dato', 'fa-regular fa-calendar', 'warning'),
                detailItem('Oferta', row.oferta, 'fa-solid fa-receipt', 'info'),
            ]),
            detailSection('Motocicleta', [
                detailItem('Modelo de moto', row.modelo_moto, 'fa-solid fa-motorcycle', 'success'),
                detailItem('Marca de moto', row.marca_moto, 'fa-solid fa-industry', 'info'),
            ]),
            detailSection('Responsable', [
                detailItem('Usuario', row.usuario, 'fa-regular fa-user', 'primary'),
                detailItem('Vendedor', row.nombre_vendedor, 'fa-solid fa-user-tie', 'success'),
            ]),
            detailSection('Referencias', [
                detailItem('ID de sucursal', row.pk_sucursal, 'fa-solid fa-store', 'info'),
                detailItem('ID de distribuidor', row.fk_distribuidor, 'fa-solid fa-building', 'secondary'),
            ]),
        ].join('');
    };

    const renderRows = (rows) => {
        if (!Array.isArray(rows) || rows.length === 0) {
            elements.rows.innerHTML = `
                <tr><td colspan="<?= $atlasVentasColumnCount ?>">
                    <div class="atlas-sales-empty">
                        <div>
                            <i class="fa-solid fa-receipt"></i>
                            <strong>Sin ventas en el periodo</strong>
                            <span>Ajusta las fechas o los filtros.</span>
                        </div>
                    </div>
                </td></tr>`;
            return;
        }
        elements.rows.innerHTML = rows.map((row) => `
            <tr>
                <td>
                    <div class="atlas-sales-client-cell">
                        <span class="atlas-sales-cell-icon atlas-sales-tone-primary"><i class="fa-solid fa-user"></i></span>
                        <div class="atlas-sales-client-content">
                            <span class="atlas-sales-main">${escapeHtml(row.nombre_cliente || 'Sin nombre')}</span>
                            <span class="atlas-sales-sub"><i class="fa-solid fa-tag me-1"></i>ID de oferta: ${escapeHtml(row.id_oferta || 'Sin dato')}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="atlas-sales-data-line atlas-sales-text-primary">
                        <i class="fa-solid fa-calendar-check"></i>${escapeHtml(formatDate(row.fecha_dispersion))}
                    </span>
                </td>
                <td>
                    <div class="atlas-sales-location-label atlas-sales-text-info">
                        <i class="fa-solid fa-store"></i><span>Sucursal</span>
                    </div>
                    <span class="atlas-sales-location-value">${escapeHtml(row.sucursal || 'Sin sucursal')}</span>
                    <div class="atlas-sales-location-divider"></div>
                    <div class="atlas-sales-location-label atlas-sales-text-success">
                        <i class="fa-solid fa-building"></i><span>Distribuidor</span>
                    </div>
                    <span class="atlas-sales-location-value">${escapeHtml(row.distribuidor || 'Sin distribuidor')}</span>
                </td>
                <td>
                    <span class="atlas-sales-data-line atlas-sales-text-info">
                        <i class="fa-solid fa-calendar-plus"></i>${escapeHtml(formatDate(row.fecha_oferta))}
                    </span>
                </td>
                <td>
                    <span class="atlas-sales-data-line">
                        <i class="fa-regular fa-clock atlas-sales-text-primary"></i>${escapeHtml(formatDate(row.fecha_etapa_actual))}
                    </span>
                    ${stageHtml(row.etapa)}
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary atlas-sales-details-button"
                            data-atlas-sale-detail="${row._detailIndex}"
                            data-bs-toggle="modal" data-bs-target="#atlasSalesDetailsModal"
                            title="Ver detalles" aria-label="Ver detalles de la oferta ${escapeHtml(row.id_oferta || '')}">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    };

    const renderPagination = (pagination = {}) => {
        state.page = Number(pagination.page || 1);
        state.totalPages = Number(pagination.total_pages || 1);
        state.total = Number(pagination.total || 0);
        const saleLabel = state.total === 1 ? 'venta' : 'ventas';
        const foundLabel = state.total === 1 ? 'encontrada' : 'encontradas';
        elements.paginationInfo.textContent = `P\u00e1gina ${state.page} de ${state.totalPages} \u00b7 ${integer.format(state.total)} ${saleLabel}`;
        elements.tableMeta.textContent = `${integer.format(state.total)} ${saleLabel} ${foundLabel}`;
        elements.previous.disabled = state.page <= 1;
        elements.next.disabled = state.page >= state.totalPages;
    };

    const renderCurrentPage = () => {
        const pageSize = Number(elements.pageSize.value || 25);
        state.total = state.filteredRows.length;
        state.totalPages = Math.max(1, Math.ceil(state.total / pageSize));
        state.page = Math.min(Math.max(1, state.page), state.totalPages);
        const offset = (state.page - 1) * pageSize;
        renderRows(state.filteredRows.slice(offset, offset + pageSize));
        renderPagination({
            page: state.page,
            page_size: pageSize,
            total: state.total,
            total_pages: state.totalPages,
        });
    };

    const applyFilters = (resetPage = true) => {
        if (resetPage) state.page = 1;
        const distributor = Number(elements.distributor.value || 0);
        const branch = Number(elements.branch.value || 0);
        const stage = stageKey(elements.stage.value);
        const search = normalizeSearch(elements.search.value);
        state.filteredRows = state.allRows.filter((row) => (
            (!state.start || row._saleDate >= state.start)
            && (!state.end || row._saleDate <= state.end)
            && (!distributor || Number(row.fk_distribuidor) === distributor)
            && (!branch || Number(row.pk_sucursal) === branch)
            && (!stage || row._stage === stage)
            && (!search || row._search.includes(search))
        ));
        renderSummary(summarizeRows(state.filteredRows));
        renderCurrentPage();
    };

    const scheduleFilter = () => {
        if (state.filterFrame !== null) cancelAnimationFrame(state.filterFrame);
        state.filterFrame = requestAnimationFrame(() => {
            state.filterFrame = null;
            applyFilters(true);
        });
    };

    const initDatePicker = (minDate, maxDate) => {
        if (typeof flatpickr !== 'function') {
            throw new Error('No se pudo iniciar el calendario de fechas.');
        }
        if (state.picker) state.picker.destroy();
        let applyButton = null;
        let clearButton = null;
        let calendarCommitted = false;
        const updateApplyButton = (selectedDates) => {
            if (applyButton) applyButton.disabled = selectedDates.length !== 2;
        };
        const selectedRange = () => [dateFromIso(state.start), dateFromIso(state.end)].filter(Boolean);
        const locale = {
            ...(flatpickr.l10ns?.es || flatpickr.l10ns?.default || {}),
            rangeSeparator: ' a ',
        };
        state.picker = flatpickr(elements.dates, {
            mode: 'range',
            dateFormat: 'd/m/Y',
            defaultDate: selectedRange(),
            minDate: dateFromIso(minDate),
            maxDate: dateFromIso(maxDate),
            locale,
            allowInput: false,
            closeOnSelect: false,
            disableMobile: true,
            onReady: (selectedDates, _dateText, instance) => {
                const footer = document.createElement('div');
                footer.className = 'atlas-sales-calendar-footer';
                clearButton = document.createElement('button');
                clearButton.type = 'button';
                clearButton.className = 'btn btn-outline-secondary btn-sm';
                clearButton.innerHTML = '<i class="fa-solid fa-calendar-xmark me-2"></i>Limpiar';
                clearButton.addEventListener('click', () => {
                    instance.clear(false);
                    state.start = '';
                    state.end = '';
                    calendarCommitted = true;
                    instance.close();
                    applyFilters(true);
                });
                applyButton = document.createElement('button');
                applyButton.type = 'button';
                applyButton.className = 'btn btn-primary btn-sm';
                applyButton.innerHTML = '<i class="fa-solid fa-check me-2"></i>Listo';
                applyButton.addEventListener('click', () => {
                    if (instance.selectedDates.length !== 2) return;
                    state.start = dateToIso(instance.selectedDates[0]);
                    state.end = dateToIso(instance.selectedDates[1]);
                    calendarCommitted = true;
                    instance.close();
                    applyFilters(true);
                });
                footer.append(clearButton, applyButton);
                instance.calendarContainer.appendChild(footer);
                updateApplyButton(selectedDates);
            },
            onOpen: (_selectedDates, _dateText, instance) => {
                calendarCommitted = false;
                instance.setDate(selectedRange(), false);
                updateApplyButton(instance.selectedDates);
            },
            onChange: (selectedDates) => updateApplyButton(selectedDates),
            onClose: (_selectedDates, _dateText, instance) => {
                if (!calendarCommitted) instance.setDate(selectedRange(), false);
                calendarCommitted = false;
            },
        });
    };

    const loadSales = async (forceRefresh = false) => {
        if (state.request) state.request.abort();
        const request = new AbortController();
        state.request = request;
        setLoading(true);
        showError('');
        const preloadStartedAt = showPreload();

        try {
            const preloadUrl = forceRefresh
                ? '/Atlas/getVentas?carga_completa=1&actualizar=1'
                : '/Atlas/getVentas?carga_completa=1';
            const response = await fetch(preloadUrl, {
                headers: { Accept: 'application/json' },
                signal: request.signal,
                cache: 'no-store',
            });
            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload?.success) {
                throw new Error(payload?.mensaje || 'No se pudo consultar Ventas.');
            }
            const data = payload.datos || {};
            state.allRows = prepareRows(Array.isArray(data.filas) ? data.filas : []);
            renderCatalogs(state.allRows);

            const availableDates = state.allRows.map((row) => row._saleDate).filter(Boolean);
            const period = data.periodo || {};
            let minDate = /^\d{4}-\d{2}-\d{2}$/.test(period.fecha_inicio || '')
                ? period.fecha_inicio
                : (availableDates.length
                    ? availableDates.reduce((minimum, date) => date < minimum ? date : minimum)
                    : state.start);
            const maxDate = /^\d{4}-\d{2}-\d{2}$/.test(period.fecha_fin || '')
                ? period.fecha_fin
                : elements.dates.dataset.end;
            if (minDate > maxDate) minDate = maxDate;
            state.minDate = minDate;
            state.maxDate = maxDate;
            if (state.start && state.end) {
                if (state.start < minDate) state.start = minDate;
                if (state.end > maxDate) state.end = maxDate;
                if (state.start > state.end) {
                    state.start = maxDate;
                    state.end = maxDate;
                }
            }

            initDatePicker(minDate, maxDate);
            applyFilters(false);
            state.request = null;
            await closePreload(preloadStartedAt);
            setLoading(false);
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (state.request === request) {
                state.request = null;
            }
            window.__atlasVentasPreload = null;
            const message = error.message || 'No se pudo consultar Ventas.';
            if (typeof Swal === 'undefined') {
                setLoading(false);
                showError(message);
                return;
            }
            const action = await Swal.fire({
                icon: 'error',
                title: 'No se pudieron cargar las ventas',
                text: message,
                confirmButtonText: 'Reintentar',
                cancelButtonText: 'Volver',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
            });
            if (action.isConfirmed) {
                loadSales(forceRefresh);
            } else {
                window.location.assign('/Inicio');
            }
        }
    };

    const initializeSearchableSelects = () => {
        if (!window.jQuery?.fn?.select2) return;
        const placeholders = new Map([
            [elements.distributor, 'Todos los distribuidores'],
            [elements.branch, 'Todas las sucursales'],
            [elements.stage, 'Todas las etapas'],
        ]);
        placeholders.forEach((placeholder, element) => {
            window.jQuery(element).select2({
                width: '100%',
                allowClear: true,
                placeholder,
                minimumResultsForSearch: 0,
                dropdownParent: window.jQuery('#atlasSalesFilters'),
                language: {
                    noResults: () => 'Sin resultados',
                    searching: () => 'Buscando...',
                },
            });
        });
    };

    window.jQuery(elements.distributor).on('change.atlasSales', () => {
        elements.branch.value = '';
        renderBranches();
        applyFilters(true);
    });
    window.jQuery(elements.branch).on('change.atlasSales', () => applyFilters(true));
    window.jQuery(elements.stage).on('change.atlasSales', () => applyFilters(true));
    elements.search.addEventListener('input', scheduleFilter);
    elements.refresh.addEventListener('click', () => loadSales(true));
    elements.rows.addEventListener('click', (event) => {
        const button = event.target.closest?.('[data-atlas-sale-detail]');
        if (!button) return;
        const row = state.allRows[Number(button.dataset.atlasSaleDetail)];
        if (row) renderDetails(row);
    });
    elements.pageSize.addEventListener('change', () => {
        state.page = 1;
        renderCurrentPage();
    });
    elements.previous.addEventListener('click', () => {
        if (state.page <= 1) return;
        state.page--;
        renderCurrentPage();
    });
    elements.next.addEventListener('click', () => {
        if (state.page >= state.totalPages) return;
        state.page++;
        renderCurrentPage();
    });
    elements.export.addEventListener('click', () => {
        const params = paramsFromFilters();
        const link = document.createElement('a');
        link.href = `/Atlas/exportarVentas?${params.toString()}`;
        link.download = '';
        link.hidden = true;
        document.body.appendChild(link);
        link.click();
        link.remove();
    });

    initializeSearchableSelects();
    loadSales();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAtlasSales, { once: true });
} else {
    initializeAtlasSales();
}
</script>
