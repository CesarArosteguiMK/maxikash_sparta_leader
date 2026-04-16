<?php
/**
 * Estadísticas Gastos Cobranza — mismo patrón que CapHum (`caphum_estadisticas.php`) y Sabueso (`sabueso_estadisticas.php`):
 * vista PHP, lógica y gráficas en JavaScript en esta misma vista, datos vía `http.request` al controlador (JSON).
 * Chart.js ya cargado en el layout del tema. Sin React ni JSX.
 *
 * @var string $titulo
 */
?>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fa-solid fa-chart-column me-2 text-primary"></i>Estadísticas Gastos Cobranza
                </h4>
                <p class="text-muted mb-0 small">Indicadores de cargos, recuperación y cartera según el periodo seleccionado.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group" aria-label="Periodo" id="gcDashPeriodo">
                    <button type="button" class="btn btn-outline-primary" data-periodo="semana">Semana</button>
                    <button type="button" class="btn btn-outline-primary active" data-periodo="mes">Mes</button>
                    <button type="button" class="btn btn-outline-primary" data-periodo="trimestre">Trimestre</button>
                    <button type="button" class="btn btn-outline-primary" data-periodo="anio">Año</button>
                </div>
            </div>
        </div>

        <!-- Carga: mismo modal global que el resto del sistema (comunes.js → showWait / Swal al usar http.request con showLoader: true) -->
        <div id="gcDashError" class="alert alert-danger d-none" role="alert"></div>

        <!-- KPIs siempre visibles (no van dentro de #gcDashMain con d-none: antes parecían «vacíos» hasta cargar gráficas) -->
        <div id="gcDashKpisWrap" class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Total generado</span>
                        <div class="gc-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div class="fs-4 fw-bold text-body" id="gcKpiTotalMonto">—</div>
                        <div class="small text-muted mt-1 flex-grow-1" id="gcKpiTotalSub">—</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Recuperado</span>
                        <div class="gc-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div class="fs-4 fw-bold text-success" id="gcKpiRecMonto">—</div>
                        <div class="small text-muted mt-1"><span id="gcKpiRecCount">—</span> cargos</div>
                        <div class="mt-auto pt-2"><span class="badge rounded-pill bg-label-success text-success" id="gcKpiRecPctBadge">—</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Condonado</span>
                        <div class="gc-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div class="fs-4 fw-bold text-body" id="gcKpiCondMonto">—</div>
                        <div class="small text-muted mt-1"><span id="gcKpiCondCount">—</span> cargos</div>
                        <div class="mt-auto pt-2"><span class="badge rounded-pill bg-label-secondary text-secondary" id="gcKpiCondPctBadge">—</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-2 d-flex flex-column">
                        <span class="badge rounded-pill bg-label-warning text-warning fw-bold mb-2 py-2 px-2 w-100 text-center lh-sm" style="font-size:.88rem;letter-spacing:.06em;line-height:1.25;white-space:normal">Pendiente</span>
                        <div class="gc-kpi-period-badge mb-2 text-start align-self-start w-100" style="font-size:.62rem;font-weight:700;letter-spacing:.04em;color:var(--bs-secondary-color);line-height:1.25">—</div>
                        <div class="fs-4 fw-bold text-danger" id="gcKpiPenMonto">—</div>
                        <div class="small text-muted mt-1"><span id="gcKpiPenCount">—</span> cargos</div>
                        <div class="mt-auto pt-2"><span class="badge rounded-pill bg-label-danger text-danger" id="gcKpiPenPctBadge">—</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="gcDashMain" class="d-none">
            <!-- Fila 1: mismo alto entre barras y donut (sin estirar el donut hasta Indicadores) -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-xl-7 d-flex">
                    <div class="card shadow-sm flex-fill d-flex flex-column">
                        <div class="card-header py-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 min-w-0 flex-grow-1">
                                    <span class="mb-0" style="font-size:.72rem;font-weight:700;letter-spacing:.06em;color:var(--bs-secondary-color)">Recuperación por periodo</span>
                                    <span class="badge rounded-pill bg-label-warning text-warning gc-chart-period-badge small fw-bold text-truncate">—</span>
                                </div>
                                <div class="btn-group btn-group-sm flex-shrink-0" role="group" aria-label="Agrupación serie" id="gcDashSerieGrupo">
                                    <button type="button" class="btn btn-outline-secondary active" data-serie-grupo="semana">Semana</button>
                                    <button type="button" class="btn btn-outline-secondary" id="gcBtnSerieMes" data-serie-grupo="mes">Mes</button>
                                </div>
                            </div>
                            <div class="small text-muted" id="gcRecuperacionSerieHint">Semana</div>
                        </div>
                        <div class="card-body d-flex flex-column flex-grow-1">
                            <div class="position-relative flex-grow-1" style="min-height: 300px;">
                                <canvas id="gcChartBar"></canvas>
                            </div>
                            <p id="gcRecuperacionLunNota" class="small mb-0 mt-1 text-muted d-none" role="note">Verde: recuperado por <strong>fecha de pago</strong> en la semana (mismo criterio que el KPI). Rojo: pendiente o parcial por <strong>fecha del cargo</strong> en la semana.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-5 d-flex">
                    <div class="card shadow-sm flex-fill d-flex flex-column">
                        <div class="card-header py-3">
                            <div class="d-flex flex-wrap align-items-center gap-2 min-w-0">
                                <span class="mb-0" style="font-size:.72rem;font-weight:700;letter-spacing:.06em;color:var(--bs-secondary-color)">Distribución de cartera</span>
                                <span class="badge rounded-pill bg-label-warning text-warning gc-chart-period-badge small fw-bold text-truncate">—</span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column flex-grow-1">
                            <!-- Chart.js: caja con alto real para el canvas -->
                            <div class="position-relative mx-auto flex-shrink-0" style="width:280px;max-width:100%;height:220px;min-height:220px;">
                                <canvas id="gcChartDonut"></canvas>
                            </div>
                            <ul class="list-unstyled small mb-2 mt-2 flex-shrink-0" id="gcDonutLegend"></ul>
                            <div class="small text-muted mb-1 flex-shrink-0">Recuperación real (pagado / total generado)</div>
                            <div class="progress flex-shrink-0" style="height: 0.65rem;">
                                <div id="gcMetaBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="small text-end text-muted mt-1 flex-shrink-0" id="gcMetaPct">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-12 col-xl-7">
                    <div class="card shadow-sm">
                        <div class="card-header py-3">
                            <div class="d-flex flex-wrap align-items-center gap-2 min-w-0">
                                <span class="mb-0" style="font-size:.72rem;font-weight:700;letter-spacing:.06em;color:var(--bs-secondary-color)">Indicadores clave</span>
                                <span class="badge rounded-pill bg-label-warning text-warning gc-chart-period-badge small fw-bold text-truncate">—</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush small" id="gcIndList">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof http === 'undefined' || !http.request) {
        var el = document.getElementById('gcDashError');
        if (el) {
            el.classList.remove('d-none');
            el.textContent = 'No se cargó el cliente HTTP (comunes).';
        }
        return;
    }
    if (typeof Chart === 'undefined') {
        document.getElementById('gcDashError').classList.remove('d-none');
        document.getElementById('gcDashError').textContent = 'Chart.js no está disponible.';
        return;
    }

    var gcState = { periodo: 'mes', serie_grupo: 'semana' };
    var gcCharts = { bar: null, donut: null };

    /** Acepta `{ success, datos }` o el objeto `datos` plano si el front ya lo desenvuelve. */
    function gcNormalizeDashboardResp(resp) {
        if (!resp || typeof resp !== 'object') {
            return null;
        }
        if (resp.kpis != null && typeof resp.kpis === 'object') {
            return resp;
        }
        if (resp.datos != null && typeof resp.datos === 'object') {
            return resp.datos;
        }

        return null;
    }
    /** Última respuesta completa del servidor (para cambiar Semana/Mes del gráfico sin nueva petición). */
    var gcLastDatos = null;
    var gcDashEverLoaded = false;

    function fmtCount(n) {
        var x = parseInt(n, 10);
        if (isNaN(x)) return '—';
        return x.toLocaleString('es-MX');
    }

    /** Montos en KPIs: compacto $8.56M / $23.8K (mismo criterio que antes en esta pantalla). */
    function fmtMoneyCompact(n) {
        var x = parseFloat(n);
        if (isNaN(x)) return '—';
        var ax = Math.abs(x);
        var sign = x < 0 ? '-' : '';
        if (ax >= 1e6) {
            return sign + '$' + Number((ax / 1e6).toFixed(2)) + 'M';
        }
        if (ax >= 1e3) {
            return sign + '$' + Number((ax / 1e3).toFixed(1)) + 'K';
        }
        return fmtMoney(x);
    }

    function cssColor(name) {
        var v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return v || '#6c757d';
    }

    /** Color «pendiente / sin pago» en gráficas (sin CSS propio en la vista). */
    function gcWineColor() {
        var c = getComputedStyle(document.documentElement).getPropertyValue('--bs-danger').trim();
        return c || '#dc2626';
    }

    function chartPalette() {
        return {
            success: cssColor('--bs-success'),
            warning: cssColor('--bs-warning'),
            danger: gcWineColor(),
            secondary: cssColor('--bs-secondary'),
            primary: cssColor('--bs-primary')
        };
    }

    function fmtMoney(n) {
        var x = parseFloat(n);
        if (isNaN(x)) return '—';
        return '$' + x.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtPct(n) {
        var x = parseFloat(n);
        if (isNaN(x)) return '—';
        return x.toFixed(2) + '%';
    }

    /** Porcentaje en badges KPI: entero si redondea ≥1%; si no, decimales para no mostrar 0% con monto > 0. */
    function fmtKpiPctBadge(p) {
        var x = parseFloat(p);
        if (isNaN(x)) return '—';
        if (x <= 0) return '0%';
        var r = Math.round(x);
        if (r > 0) return r + '%';
        var s = String(Number(x.toFixed(2)));
        if (s === '0') return '<0.01%';
        return s + '%';
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function syncPeriodButtons() {
        document.querySelectorAll('#gcDashPeriodo [data-periodo]').forEach(function (b) {
            var on = b.getAttribute('data-periodo') === gcState.periodo;
            b.classList.toggle('active', on);
            b.classList.toggle('btn-primary', on);
            b.classList.toggle('btn-outline-primary', !on);
        });
    }

    function syncSerieButtons() {
        document.querySelectorAll('#gcDashSerieGrupo [data-serie-grupo]').forEach(function (b) {
            if (b.classList.contains('d-none')) return;
            var on = b.getAttribute('data-serie-grupo') === gcState.serie_grupo;
            b.classList.toggle('active', on);
            b.classList.toggle('btn-secondary', on);
            b.classList.toggle('btn-outline-secondary', !on);
        });
    }

    /**
     * Filtro superior «Semana»: solo agrupación semanal en el gráfico (un botón).
     * Mes / Trimestre / Año: se muestran Semana y Mes para la serie temporal.
     */
    function updateSerieGrupoUI() {
        var btnMes = document.getElementById('gcBtnSerieMes');
        var grpSerie = document.getElementById('gcDashSerieGrupo');
        var soloSemana = gcState.periodo === 'semana';
        if (soloSemana) {
            gcState.serie_grupo = 'semana';
        }
        if (btnMes) {
            btnMes.classList.toggle('d-none', soloSemana);
        }
        if (grpSerie) {
            grpSerie.classList.toggle('d-none', soloSemana);
        }
        var hint = document.getElementById('gcRecuperacionSerieHint');
        if (hint) {
            hint.textContent = soloSemana ? 'Por día' : 'Semana / Mes';
        }
        var lunNota = document.getElementById('gcRecuperacionLunNota');
        if (lunNota) {
            lunNota.classList.toggle('d-none', !soloSemana);
        }
        syncSerieButtons();
    }

    function destroyChart(key) {
        if (gcCharts[key]) {
            try { gcCharts[key].destroy(); } catch (e) {}
            gcCharts[key] = null;
        }
    }

    /** Tras mostrar el panel o cambiar layout flex, Chart.js debe recalcular tamaño del canvas. */
    function gcResizeDashboardCharts() {
        requestAnimationFrame(function () {
            try {
                if (gcCharts.bar && typeof gcCharts.bar.resize === 'function') gcCharts.bar.resize();
            } catch (e1) {}
            try {
                if (gcCharts.donut && typeof gcCharts.donut.resize === 'function') gcCharts.donut.resize();
            } catch (e2) {}
        });
    }

    function labelSerieRow(row, serieGrupo) {
        if (!row) return '';
        if (serieGrupo === 'dia' && row.periodo_ini) {
            var s = String(row.periodo_ini).slice(0, 10);
            if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
                var dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                var d = new Date(s + 'T12:00:00');
                if (!isNaN(d.getTime())) {
                    return dias[d.getDay()] + ' ' + s.slice(8, 10) + '/' + s.slice(5, 7);
                }
            }
            return s;
        }
        if (serieGrupo === 'mes' && row.periodo) {
            var p = String(row.periodo);
            var m = p.match(/^(\d{4})-(\d{2})$/);
            if (m) {
                var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                var mi = parseInt(m[2], 10) - 1;
                if (mi >= 0 && mi < 12) return meses[mi] + ' ' + m[1];
            }
            return p;
        }
        if (row.periodo_ini) {
            var d = String(row.periodo_ini).slice(0, 10);
            return d;
        }
        return String(row.periodo || '');
    }

    /** Badges dorados: KPIs + cabeceras de gráficas (mismo texto que `periodo_badge` del servidor). */
    function setGcPeriodBadgeText(dat) {
        var pl = '—';
        if (dat && typeof dat === 'object') {
            if (dat.periodo_badge != null && String(dat.periodo_badge).trim() !== '') {
                pl = String(dat.periodo_badge).trim();
            } else if (dat.periodo_label != null && String(dat.periodo_label).trim() !== '') {
                pl = String(dat.periodo_label).trim();
            }
        } else if (dat != null && String(dat).trim() !== '') {
            pl = String(dat).trim();
        }
        document.querySelectorAll('.gc-kpi-period-badge, .gc-chart-period-badge').forEach(function (el) {
            el.textContent = pl;
        });
    }

    function renderKpis(kpis, dat) {
        kpis = kpis || {};
        setGcPeriodBadgeText(dat || {});
        function setKpi(baseId, block, badgeId) {
            if (!block) return;
            var elM = document.getElementById(baseId + 'Monto');
            var elC = document.getElementById(baseId + 'Count');
            var elB = badgeId ? document.getElementById(badgeId) : null;
            if (elM) elM.textContent = fmtMoneyCompact(block.monto);
            if (elC) elC.textContent = block.count != null ? fmtCount(block.count) : '—';
            if (elB) {
                elB.textContent = fmtKpiPctBadge(block.pct);
            }
        }
        var tot = kpis.total_generado || {};
        var tM = document.getElementById('gcKpiTotalMonto');
        var tSub = document.getElementById('gcKpiTotalSub');
        if (tM) tM.textContent = fmtMoneyCompact(tot.monto);
        if (tSub) {
            tSub.textContent = (tot.count != null ? fmtCount(tot.count) : '—') + ' cargos por pagos tardíos';
        }
        setKpi('gcKpiRec', kpis.recuperado, 'gcKpiRecPctBadge');
        setKpi('gcKpiPen', kpis.pendiente, 'gcKpiPenPctBadge');
        setKpi('gcKpiCond', kpis.condonado, 'gcKpiCondPctBadge');
    }

    function renderDonutLegend(donut, kpis, pal) {
        var ul = document.getElementById('gcDonutLegend');
        if (!ul) return;
        donut = donut || {};
        var mRec = parseFloat(donut.recuperado) || 0;
        var mPar = parseFloat(donut.pago_parcial) || 0;
        var mPen = parseFloat(donut.pendiente) || 0;
        var mCond = parseFloat(donut.condonado) || 0;
        var mPendienteVis = mPar + mPen;
        var total = mRec + mPar + mPen + mCond;
        var rows = [
            { label: 'Recuperado', color: pal.success, m: mRec },
            { label: 'Pendiente', color: pal.danger, m: mPendienteVis },
            { label: 'Condonado', color: pal.secondary, m: mCond }
        ];
        ul.innerHTML = rows.map(function (r) {
            var m = r.m;
            var pct = total > 0 ? (parseFloat(m) / total) * 100 : 0;
            return '<li class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">' +
                '<span><span class="d-inline-block rounded-circle me-2" style="width:0.55rem;height:0.55rem;background:' + r.color + '"></span>' + esc(r.label) + '</span>' +
                '<span class="text-muted">' + fmtMoney(m) + ' · ' + fmtPct(pct) + '</span></li>';
        }).join('');
    }

    function renderIndicadores(ind) {
        var ul = document.getElementById('gcIndList');
        if (!ul || !ind) return;
        var rows = [
            { label: 'Cargo base unitario', val: fmtMoney(ind.cargo_base_unitario), cls: 'fw-semibold' },
            { label: 'Cargos este periodo', val: ind.total_cargos_periodo != null ? fmtCount(ind.total_cargos_periodo) : '—', cls: 'fw-semibold' },
            { label: 'Tasa de condonación', val: fmtPct(ind.tasa_condonacion_pct), cls: 'fw-bold text-warning' },
            { label: '% recuperación real total', val: fmtPct(ind.pct_recuperacion_real), cls: 'fw-bold text-success' },
            { label: 'Promedio días de mora', val: ind.mora_promedio_dias != null ? String(ind.mora_promedio_dias) + ' días' : '—', cls: 'fw-bold text-danger' }
        ];
        ul.innerHTML = rows.map(function (r) {
            return '<li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">' +
                '<span class="text-muted pe-2">' + esc(r.label) + '</span>' +
                '<span class="text-end ' + r.cls + '">' + esc(r.val) + '</span></li>';
        }).join('');
    }

    /** Serie del gráfico: filtro «Semana» → por días; si no, según toggle Semana/Mes. */
    function serieActivaArr(dat) {
        dat = dat || {};
        if (gcState.periodo === 'semana') {
            if (Array.isArray(dat.serie_dias) && dat.serie_dias.length) return dat.serie_dias;
            return [];
        }
        var sg = gcState.serie_grupo === 'mes' ? 'mes' : 'semana';
        if (sg === 'mes' && Array.isArray(dat.serie_mes) && dat.serie_mes.length) {
            return dat.serie_mes;
        }
        if (sg === 'semana' && Array.isArray(dat.serie_semana) && dat.serie_semana.length) {
            return dat.serie_semana;
        }
        return Array.isArray(dat.serie) ? dat.serie : [];
    }

    function serieGrupoParaEjes() {
        if (gcState.periodo === 'semana') return 'dia';
        return gcState.serie_grupo === 'mes' ? 'mes' : 'semana';
    }

    function renderCharts(dat) {
        var pal = chartPalette();
        var serieGrupo = serieGrupoParaEjes();
        var serie = serieActivaArr(dat);
        var labels = serie.map(function (r) { return labelSerieRow(r, serieGrupo); });
        var ds1 = serie.map(function (r) { return parseFloat(r.monto_pagado) || 0; });
        var dsSinPago = serie.map(function (r) {
            return (parseFloat(r.monto_parcial) || 0) + (parseFloat(r.monto_sin_pago) || 0);
        });

        destroyChart('bar');
        var ctxB = document.getElementById('gcChartBar');
        if (ctxB) {
            gcCharts.bar = new Chart(ctxB.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels.length ? labels : ['—'],
                    datasets: [
                        { label: 'Pagado', data: labels.length ? ds1 : [0], backgroundColor: pal.success },
                        { label: 'Sin pago', data: labels.length ? dsSinPago : [0], backgroundColor: pal.danger }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    var v = ctx.parsed.y != null ? ctx.parsed.y : ctx.parsed;
                                    return (ctx.dataset.label || '') + ': ' + fmtMoney(v);
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, ticks: { maxRotation: 45, minRotation: 0, font: { size: 10 } } },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                callback: function (val) {
                                    return '$' + Number(val).toLocaleString('es-MX', { maximumFractionDigits: 0 });
                                }
                            }
                        }
                    }
                }
            });
        }

        var donut = dat.donut || {};
        var dPar = parseFloat(donut.pago_parcial) || 0;
        var dPen = parseFloat(donut.pendiente) || 0;
        var dVals = [
            parseFloat(donut.recuperado) || 0,
            dPar + dPen,
            parseFloat(donut.condonado) || 0
        ];
        var dLabels = ['Recuperado', 'Pendiente', 'Condonado'];
        var dColors = [pal.success, pal.danger, pal.secondary];
        if (dVals.reduce(function (a, b) { return a + b; }, 0) === 0) {
            dVals = [1];
            dLabels = ['Sin datos'];
            dColors = [cssColor('--bs-secondary-bg') || pal.secondary];
        }

        destroyChart('donut');
        var ctxD = document.getElementById('gcChartDonut');
        if (ctxD) {
            gcCharts.donut = new Chart(ctxD.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dLabels,
                    datasets: [{ data: dVals, backgroundColor: dColors, borderWidth: 0 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1,
                    layout: { padding: 4 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    var v = ctx.parsed != null ? ctx.parsed : 0;
                                    return fmtMoney(v);
                                }
                            }
                        }
                    }
                }
            });
        }

        renderDonutLegend(dat.donut || {}, dat.kpis || {}, pal);
        gcResizeDashboardCharts();

        var pctMeta = dat.indicadores && dat.indicadores.pct_recuperacion_real != null
            ? Math.min(100, Math.max(0, parseFloat(dat.indicadores.pct_recuperacion_real)))
            : 0;
        var bar = document.getElementById('gcMetaBar');
        var tx = document.getElementById('gcMetaPct');
        if (bar) {
            bar.style.width = pctMeta + '%';
            bar.setAttribute('aria-valuenow', String(Math.round(pctMeta)));
        }
        if (tx) tx.textContent = fmtPct(pctMeta) + ' del total generado en el periodo';
    }

    function applyDashboard(dat) {
        if (!dat) return;
        gcLastDatos = dat;
        updateSerieGrupoUI();
        renderKpis(dat.kpis || {}, dat);
        renderIndicadores(dat.indicadores || {});
        renderCharts(dat);
    }

    function loadDashboard() {
        var mainEl = document.getElementById('gcDashMain');
        var errEl = document.getElementById('gcDashError');
        if (errEl) errEl.classList.add('d-none');
        if (mainEl && !gcDashEverLoaded) {
            mainEl.classList.add('d-none');
        }

        syncPeriodButtons();
        updateSerieGrupoUI();

        http.request({
            endpoint: '/gastoscobranza/getdashboardestadistica',
            metodo: 'POST',
            data: JSON.stringify({ periodo: gcState.periodo, serie_grupo: gcState.serie_grupo }),
            contentType: 'application/json; charset=UTF-8',
            processData: false,
            showLoader: true,
            onSuccess: function (resp) {
                if (!resp || resp.success === false) {
                    return;
                }
                var datos = gcNormalizeDashboardResp(resp);
                if (!datos || typeof datos.kpis !== 'object') {
                    if (errEl) {
                        errEl.classList.remove('d-none');
                        errEl.textContent = 'El servidor no devolvió KPIs válidos para el tablero.';
                    }
                    return;
                }
                if (errEl) errEl.classList.add('d-none');
                if (mainEl) mainEl.classList.remove('d-none');
                applyDashboard(datos);
                gcDashEverLoaded = true;
            },
            onError: function (msg) {
                if (errEl) {
                    errEl.classList.remove('d-none');
                    errEl.textContent = typeof msg === 'string' ? msg : 'Error al cargar el dashboard.';
                }
            }
        });
    }

    document.querySelectorAll('#gcDashPeriodo [data-periodo]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var p = btn.getAttribute('data-periodo');
            if (p) {
                gcState.periodo = p;
                loadDashboard();
            }
        });
    });

    document.querySelectorAll('#gcDashSerieGrupo [data-serie-grupo]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var s = btn.getAttribute('data-serie-grupo');
            if (!s) return;
            gcState.serie_grupo = s;
            syncSerieButtons();
            if (gcLastDatos) {
                renderCharts(gcLastDatos);
                return;
            }
            loadDashboard();
        });
    });

    loadDashboard();
});
</script>
