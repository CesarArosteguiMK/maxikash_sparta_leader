<style>
    .madj-dash-page {
        --madj-navy: #24334f;
        --madj-teal: #0f9d92;
        --madj-cyan: #14b8d4;
        --madj-soft: #eefafa;
    }
    .madj-dash-hero {
        border-radius: .85rem;
        background: linear-gradient(120deg, #24334f 0%, #2f63d9 100%);
        color: #fff;
        box-shadow: 0 .65rem 1.6rem rgba(36, 51, 79, .18);
    }
    .madj-dash-hero .text-muted { color: rgba(255,255,255,.74) !important; }
    .madj-dash-filter {
        border: 1px solid rgba(20, 184, 212, .28);
        background: var(--madj-soft);
        border-radius: .75rem;
    }
    .madj-kpi {
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        min-height: 132px;
        box-shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .05);
    }
    .madj-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: .7rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 157, 146, .12);
        color: var(--madj-teal);
    }
    .madj-kpi-value {
        color: var(--madj-navy);
        font-weight: 800;
        font-size: clamp(1.35rem, 2vw, 2rem);
        letter-spacing: 0;
    }
    .madj-panel {
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        box-shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .05);
    }
    .madj-panel-title {
        color: var(--madj-navy);
        font-weight: 800;
        letter-spacing: 0;
    }
    .madj-alert-row {
        border: 1px solid var(--bs-border-color);
        border-radius: .6rem;
        padding: .75rem .85rem;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: center;
    }
    .madj-state-bar {
        height: .52rem;
        border-radius: 999px;
        background: #edf2f7;
        overflow: hidden;
    }
    .madj-state-bar > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--madj-teal), #2f63d9);
    }
    html.dark-mode .madj-dash-filter,
    body.dark-mode .madj-dash-filter,
    html.dark-mode .madj-panel,
    body.dark-mode .madj-panel,
    html.dark-mode .madj-kpi,
    body.dark-mode .madj-kpi {
        background: #111827;
        border-color: #243246;
    }
    html.dark-mode .madj-kpi-value,
    body.dark-mode .madj-kpi-value,
    html.dark-mode .madj-panel-title,
    body.dark-mode .madj-panel-title {
        color: #e5edf7;
    }
</style>

<div class="container-fluid py-3 madj-dash-page" id="madjDashApp">
    <div class="madj-dash-hero p-4 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 text-white"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard de Motos Adjudicadas</h3>
                <p class="mb-0 text-muted">Vista ejecutiva de operaciones, ubicaciones, tracking y seguimiento operativo.</p>
            </div>
            <button type="button" class="btn btn-light" id="madjDashRefresh">
                <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
            </button>
        </div>
    </div>

    <div class="madj-dash-filter p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold" for="madjDashDesde">Desde</label>
                <input type="date" class="form-control" id="madjDashDesde">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" for="madjDashHasta">Hasta</label>
                <input type="date" class="form-control" id="madjDashHasta">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="button" class="btn btn-primary w-100" id="madjDashAplicar">
                    <i class="fa-solid fa-filter me-1"></i>Aplicar
                </button>
                <button type="button" class="btn btn-outline-secondary" id="madjDashLimpiar" title="Limpiar filtros">
                    <i class="fa-solid fa-eraser"></i>
                </button>
            </div>
            <div class="col-md-3 text-md-end text-muted small">
                <span id="madjDashActualizado">Sin cargar</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3" id="madjKpis">
        <div class="col-sm-6 col-xl-3">
            <div class="card madj-kpi"><div class="card-body">
                <div class="d-flex justify-content-between gap-2">
                    <span class="madj-kpi-icon"><i class="fa-solid fa-motorcycle"></i></span>
                    <span class="badge bg-label-primary">Total</span>
                </div>
                <div class="madj-kpi-value mt-3" data-kpi="total">0</div>
                <div class="text-muted fw-semibold">Operaciones adjudicadas</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card madj-kpi"><div class="card-body">
                <div class="d-flex justify-content-between gap-2">
                    <span class="madj-kpi-icon"><i class="fa-solid fa-truck-fast"></i></span>
                    <span class="badge bg-label-info">Tracking</span>
                </div>
                <div class="madj-kpi-value mt-3" data-kpi="rutas_activas">0</div>
                <div class="text-muted fw-semibold">Rutas activas</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card madj-kpi"><div class="card-body">
                <div class="d-flex justify-content-between gap-2">
                    <span class="madj-kpi-icon"><i class="fa-solid fa-location-dot"></i></span>
                    <span class="badge bg-label-warning">Alerta</span>
                </div>
                <div class="madj-kpi-value mt-3" data-kpi="ubicacion_incompleta">0</div>
                <div class="text-muted fw-semibold">Ubicaciones incompletas</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card madj-kpi"><div class="card-body">
                <div class="d-flex justify-content-between gap-2">
                    <span class="madj-kpi-icon"><i class="fa-solid fa-warehouse"></i></span>
                    <span class="badge bg-label-success">Almacen</span>
                </div>
                <div class="madj-kpi-value mt-3" data-kpi="llegada_almacen">0</div>
                <div class="text-muted fw-semibold">Llegadas registradas</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="card madj-panel h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="madj-panel-title"><i class="fa-solid fa-chart-column me-2 text-primary"></i>Operaciones por estatus</div>
                </div>
                <div class="card-body"><div id="madjChartEstatus" style="min-height: 320px;"></div></div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card madj-panel h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="madj-panel-title"><i class="fa-solid fa-map-location-dot me-2 text-primary"></i>Top estados</div>
                </div>
                <div class="card-body" id="madjTopEstados">
                    <div class="text-muted">Cargando...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card madj-panel h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="madj-panel-title"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>Altas de los últimos 14 días</div>
                </div>
                <div class="card-body"><div id="madjChartDias" style="min-height: 280px;"></div></div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card madj-panel h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="madj-panel-title"><i class="fa-solid fa-triangle-exclamation me-2 text-warning"></i>Alertas operativas</div>
                </div>
                <div class="card-body d-flex flex-column gap-2" id="madjAlertas">
                    <div class="text-muted">Cargando...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const app = document.getElementById('madjDashApp');
    if (!app) return;

    const charts = {};
    const fmt = new Intl.NumberFormat('es-MX');
    const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 });

    function setKpi(key, value) {
        const el = app.querySelector(`[data-kpi="${key}"]`);
        if (el) el.textContent = fmt.format(Number(value || 0));
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    function chartOptionsBase() {
        return {
            chart: { toolbar: { show: false }, fontFamily: 'inherit' },
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
            grid: { borderColor: 'rgba(148, 163, 184, .22)' }
        };
    }

    function renderChart(id, options) {
        const el = document.querySelector(id);
        if (!el || typeof ApexCharts === 'undefined') return;
        if (charts[id]) charts[id].destroy();
        charts[id] = new ApexCharts(el, options);
        charts[id].render();
    }

    function renderEstados(rows) {
        const cont = document.getElementById('madjTopEstados');
        const max = Math.max(...(rows || []).map(r => Number(r.total || 0)), 1);
        cont.innerHTML = (rows || []).length ? rows.map(r => {
            const total = Number(r.total || 0);
            const pct = Math.round((total / max) * 100);
            return `<div class="mb-3">
                <div class="d-flex justify-content-between gap-2 mb-1">
                    <strong>${escapeHtml(r.label)}</strong>
                    <span class="badge bg-label-primary">${fmt.format(total)}</span>
                </div>
                <div class="madj-state-bar"><span style="width:${pct}%"></span></div>
            </div>`;
        }).join('') : '<div class="text-muted">Sin datos por estado.</div>';
    }

    function renderAlertas(rows, resumen) {
        const cont = document.getElementById('madjAlertas');
        const baseRows = rows && rows.length ? rows : [
            { alerta: 'Sin alertas relevantes', total: 0 }
        ];
        cont.innerHTML = baseRows.map(r => `<div class="madj-alert-row">
            <div>
                <div class="fw-bold">${escapeHtml(r.alerta)}</div>
                <div class="small text-muted">Seguimiento operativo</div>
            </div>
            <span class="badge ${Number(r.total || 0) > 0 ? 'bg-label-warning' : 'bg-label-success'}">${fmt.format(r.total || 0)}</span>
        </div>`).join('') + `<div class="madj-alert-row mt-2">
            <div>
                <div class="fw-bold">Adeudo total</div>
                <div class="small text-muted">Saldo agregado de operaciones filtradas</div>
            </div>
            <span class="badge bg-label-primary">${money.format(resumen.adeudo_total || 0)}</span>
        </div>`;
    }

    async function cargar() {
        const qs = new URLSearchParams();
        const desde = document.getElementById('madjDashDesde').value;
        const hasta = document.getElementById('madjDashHasta').value;
        if (desde) qs.set('desde', desde);
        if (hasta) qs.set('hasta', hasta);
        document.getElementById('madjDashActualizado').textContent = 'Cargando informacion...';

        const resp = await fetch('/MotosAdjudicadas/dashboardDatos?' + qs.toString(), { headers: { 'Accept': 'application/json' } });
        const json = await resp.json();
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el dashboard.');
        const datos = json.datos || {};
        const resumen = datos.resumen || {};
        const tracking = datos.tracking || {};

        setKpi('total', resumen.total);
        setKpi('rutas_activas', tracking.rutas_activas);
        setKpi('ubicacion_incompleta', resumen.ubicacion_incompleta);
        setKpi('llegada_almacen', resumen.llegada_almacen);

        const estatus = datos.por_estatus || [];
        renderChart('#madjChartEstatus', Object.assign(chartOptionsBase(), {
            chart: { type: 'donut', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
            labels: estatus.map(r => r.label),
            series: estatus.map(r => Number(r.total || 0)),
            colors: ['#24334f', '#0f9d92', '#14b8d4', '#2f63d9', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b']
        }));

        const dias = datos.por_dia || [];
        renderChart('#madjChartDias', Object.assign(chartOptionsBase(), {
            chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#0f9d92'],
            fill: { type: 'gradient', gradient: { opacityFrom: .35, opacityTo: .05 } },
            xaxis: { categories: dias.map(r => r.label || '') },
            yaxis: { labels: { formatter: v => fmt.format(v) } },
            series: [{ name: 'Altas', data: dias.map(r => Number(r.total || 0)) }]
        }));

        renderEstados(datos.por_estado || []);
        renderAlertas(datos.alertas || [], resumen);
        document.getElementById('madjDashActualizado').textContent = 'Actualizado: ' + (datos.actualizado_at || '');
    }

    document.getElementById('madjDashAplicar').addEventListener('click', cargar);
    document.getElementById('madjDashRefresh').addEventListener('click', cargar);
    document.getElementById('madjDashLimpiar').addEventListener('click', () => {
        document.getElementById('madjDashDesde').value = '';
        document.getElementById('madjDashHasta').value = '';
        cargar();
    });

    cargar().catch(err => {
        document.getElementById('madjDashActualizado').textContent = 'Error al cargar';
        if (typeof Swal !== 'undefined') Swal.fire('Sin informacion', err.message, 'warning');
    });
})();
</script>
