<?php
/**
 * Histórico primeros pagos por semana (tbl_histo_primeros_pagos) — mismo criterio de métricas que Lunes de cierre.
 *
 * Vista: comportamiento de **nacimiento vs corte actual** de las últimas 5 semanas cerradas
 * (excluye la semana ISO en curso). Sin selector de periodo.
 * Gráficas de jerarquía y tipos de gráfica solo en esta pantalla.
 *
 * @var string $titulo
 */
?>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i><?= htmlspecialchars($titulo ?? 'Primeros pagos — Histórico', ENT_QUOTES, 'UTF-8'); ?>
                </h4>
                <p id="pphSubtitulo" class="text-muted mb-0 small">
                    Últimas 5 semanas cerradas — nacimiento vs corte actual
                </p>
            </div>
            <div>
                <a href="/analitica/PrimerosPagos" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>

        <div id="pphAlert" class="alert alert-danger d-none" role="alert"></div>

        <div id="pphMain" class="d-none">
            <div class="card mb-3">
                <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <span class="fw-semibold" style="font-size:.82rem;">
                            <i class="fa fa-chart-line text-primary me-1"></i>
                            Comportamiento semanal — nacimiento contra el corte actual
                        </span>
                        <div class="text-muted" style="font-size:.72rem;">
                            Elija el tipo de gráfica; los datos son los mismos en todas.
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <label for="pphTipoGraficaComportamiento" class="form-label small text-muted mb-0">Tipo de gráfica</label>
                        <select id="pphTipoGraficaComportamiento" class="form-select form-select-sm" style="min-width:14rem;max-width:22rem;">
                            <option value="dual_stack">Barras apiladas (nacimiento | cierre semana)</option>
                            <option value="full_stack">Todo en una columna apilada</option>
                            <option value="grouped">Barras agrupadas (sin apilar)</option>
                            <option value="line">Tendencia (líneas)</option>
                            <option value="nacimiento_5">Solo nacimiento: 5 buckets apilados</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative w-100" style="min-height:320px;">
                        <canvas id="pphChartComportamiento"></canvas>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <span class="fw-semibold" style="font-size:.82rem;">
                            <i class="fa fa-ranking-star text-danger me-1"></i>
                            Seguimiento por jerarquía
                        </span>
                        <div class="text-muted" style="font-size:.72rem;">
                            Solo en histórico por semana — misma lógica que la tabla (cobrados vs pendientes por efectividad).
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <label for="pphSemanaJerarquia" class="form-label small text-muted mb-0">Semana</label>
                        <select id="pphSemanaJerarquia" class="form-select form-select-sm" style="min-width:11rem;"></select>
                        <label for="pphTipoGraficaJerarquia" class="form-label small text-muted mb-0">Gráfica</label>
                        <select id="pphTipoGraficaJerarquia" class="form-select form-select-sm" style="min-width:15rem;max-width:20rem;">
                            <option value="territorial_stack">Territorial — cobrados vs pendientes</option>
                            <option value="territorial_pct">Territorial — % efectividad (barra)</option>
                            <option value="gestores_top">Top 15 gestores — cobrados vs pendientes</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative w-100" style="min-height:280px;">
                        <canvas id="pphChartJerarquia"></canvas>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-2">
                    <span class="fw-semibold" style="font-size:.82rem;">
                        <i class="fa fa-table text-primary me-1"></i>
                        Resumen por semana
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle" id="pphTablaComparativo">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:11rem;">Semana</th>
                                <th class="text-end">Créditos</th>
                                <th class="text-end text-success">Nacieron Current</th>
                                <th class="text-end text-danger">Nacieron 1–7d</th>
                                <th class="text-end text-primary">Cierre semana</th>
                                <th class="text-end text-warning">cierre 1 7d</th>
                                <th class="text-end">Recuperación 1–7d</th>
                            </tr>
                        </thead>
                        <tbody id="pphTablaComparativoBody"></tbody>
                    </table>
                </div>
            </div>

            <div id="pphCardsSemanas" class="row g-3"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var BUCKET_META = {
        'a) Current':      { cls: 'bg-label-success',   icon: 'fa-circle-check',         short: 'Current' },
        'b) 1 a 7 dias':   { cls: 'bg-label-danger',    icon: 'fa-clock',                short: '1-7d'    },
        'c) 8 a 30 dias':  { cls: 'bg-label-warning',   icon: 'fa-triangle-exclamation', short: '8-30d'   },
        'd) 31 a 60 dias': { cls: 'bg-label-danger',    icon: 'fa-fire',                 short: '31-60d'  },
        'e) 61+ dias':     { cls: 'bg-label-secondary', icon: 'fa-skull-crossbones',     short: '61+d'    }
    };
    var BUCKET_ORDER = Object.keys(BUCKET_META);
    var NAC_COLORS = ['#28a745', '#ff3e1d', '#ffab00', '#dc3545', '#8592a3'];
    var pphCharts = { comp: null, jer: null };
    var pphState = { semanas: [] };

    function showErr(msg) {
        var el = document.getElementById('pphAlert');
        if (!el) return;
        el.textContent = msg || 'Error';
        el.classList.remove('d-none');
    }
    function hideErr() {
        var el = document.getElementById('pphAlert');
        if (!el) return;
        el.classList.add('d-none');
        el.textContent = '';
    }
    function fmtInt(n) {
        var x = parseInt(n, 10);
        if (isNaN(x)) return '—';
        return x.toLocaleString('es-MX');
    }
    function pct(v, t) {
        var tt = parseInt(t, 10);
        var vv = parseInt(v, 10);
        if (isNaN(tt) || tt <= 0 || isNaN(vv)) return 0;
        return Math.round((vv / tt) * 100);
    }
    function destroyChart(key) {
        if (pphCharts[key]) {
            try { pphCharts[key].destroy(); } catch (e) {}
            pphCharts[key] = null;
        }
    }
    function semanasVisibles(semanas) {
        var v = (semanas || []).filter(function (s) { return s.disponible; });
        v.sort(function (a, b) { return (a.ini || '') < (b.ini || '') ? -1 : 1; });
        return v;
    }
    function datosSeriesSemanas(visibles) {
        return {
            labels: visibles.map(function (s) { return s.semana; }),
            nacCur: visibles.map(function (s) { return parseInt((s.nacimiento || {}).current, 10) || 0; }),
            nac17: visibles.map(function (s) { return parseInt((s.nacimiento || {}).d1_7, 10) || 0; }),
            curCorte: visibles.map(function (s) { return parseInt((s.corte || {}).current_al_corte, 10) || 0; }),
            pend: visibles.map(function (s) { return parseInt((s.corte || {}).pendientes_primeros_pagos, 10) || 0; })
        };
    }

    function renderTabla(semanas) {
        var tb = document.getElementById('pphTablaComparativoBody');
        if (!tb) return;
        var html = '';
        semanas.forEach(function (s) {
            if (!s.disponible) {
                html += ''
                    + '<tr class="text-muted">'
                    + '<td><strong>' + (s.semana || '—') + '</strong><div class="small">' + (s.ini || '') + ' → ' + (s.fin || '') + '</div></td>'
                    + '<td colspan="6" class="text-center">Sin datos (' + (s.mensaje || '—') + ')</td>'
                    + '</tr>';
                return;
            }
            var nac = s.nacimiento || {};
            var co = s.corte || {};
            var rec = s.recuperacion || {};
            var total = parseInt(s.total, 10) || 0;
            html += ''
                + '<tr>'
                + '<td>'
                    + '<strong>' + (s.semana || '—') + '</strong>'
                    + '<div class="small text-muted">' + (s.ini || '') + ' → ' + (s.fin || '') + '</div>'
                + '</td>'
                + '<td class="text-end fw-semibold">' + fmtInt(total) + '</td>'
                + '<td class="text-end">' + fmtInt(nac.current) + ' <span class="text-muted small">(' + (nac.pct_current || 0) + '%)</span></td>'
                + '<td class="text-end">' + fmtInt(nac.d1_7) + ' <span class="text-muted small">(' + (nac.pct_1_7 || 0) + '%)</span></td>'
                + '<td class="text-end">' + fmtInt(co.current_al_corte) + ' <span class="text-muted small">(' + (co.pct_current_al_corte || 0) + '%)</span></td>'
                + '<td class="text-end">' + fmtInt(co.pendientes_primeros_pagos) + ' <span class="text-muted small">(' + (co.pct_pendientes || 0) + '%)</span></td>'
                + '<td class="text-end"><span class="badge bg-label-info">' + (rec.pct_sobre_1_7 || 0) + '%</span></td>'
                + '</tr>';
        });
        tb.innerHTML = html || '<tr><td colspan="7" class="text-center text-muted py-3">Sin datos</td></tr>';
    }

    function renderCards(semanas) {
        var wrap = document.getElementById('pphCardsSemanas');
        if (!wrap) return;
        var html = '';
        semanas.forEach(function (s) {
            if (!s.disponible) return;
            var nac = s.nacimiento || {};
            var co = s.corte || {};
            var rec = s.recuperacion || {};
            var nd = nac.nac_dist || {};
            var total = parseInt(s.total, 10) || 0;

            var distribHtml = '';
            BUCKET_ORDER.forEach(function (b) {
                var cnt = parseInt(nd[b], 10) || 0;
                if (!cnt) return;
                var m = BUCKET_META[b] || {};
                var p = pct(cnt, total);
                distribHtml += ''
                    + '<div class="col-6 col-md-4">'
                        + '<div class="border rounded p-2 text-center h-100">'
                            + '<div class="badge ' + (m.cls || 'bg-label-secondary') + ' mb-1" style="font-size:.62rem;">'
                                + '<i class="fa ' + (m.icon || 'fa-question') + ' me-1"></i>' + (m.short || b)
                            + '</div>'
                            + '<div class="fw-bold" style="font-size:1.1rem;">' + fmtInt(cnt)
                                + '<span class="text-muted fw-semibold small ms-1">(' + p + '%)</span></div>'
                        + '</div>'
                    + '</div>';
            });

            html += ''
            + '<div class="col-12 col-xl-6">'
                + '<div class="card h-100 shadow-sm">'
                    + '<div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">'
                        + '<span class="fw-semibold" style="font-size:.82rem;">'
                            + '<i class="fa fa-calendar-week text-primary me-1"></i>' + (s.semana || '—')
                        + '</span>'
                        + '<span class="text-muted small">' + (s.ini || '') + ' → ' + (s.fin || '') + '</span>'
                    + '</div>'
                    + '<div class="card-body py-2">'
                        + '<div class="row g-2 mb-2">'
                            + '<div class="col-4">'
                                + '<div class="border rounded text-center p-2">'
                                    + '<div class="text-muted" style="font-size:.66rem;letter-spacing:.5px;">CRÉDITOS</div>'
                                    + '<div class="fw-bold text-primary" style="font-size:1.25rem;">' + fmtInt(total) + '</div>'
                                + '</div>'
                            + '</div>'
                            + '<div class="col-4">'
                                + '<div class="border rounded text-center p-2">'
                                    + '<div class="text-muted" style="font-size:.66rem;letter-spacing:.5px;">Cierre semana</div>'
                                    + '<div class="fw-bold text-success" style="font-size:1.25rem;">' + fmtInt(co.current_al_corte)
                                        + '<span class="text-muted small ms-1">(' + (co.pct_current_al_corte || 0) + '%)</span></div>'
                                + '</div>'
                            + '</div>'
                            + '<div class="col-4">'
                                + '<div class="border rounded text-center p-2">'
                                    + '<div class="text-muted" style="font-size:.66rem;letter-spacing:.5px;">cierre 1 7d</div>'
                                    + '<div class="fw-bold text-warning" style="font-size:1.25rem;">' + fmtInt(co.pendientes_primeros_pagos)
                                        + '<span class="text-muted small ms-1">(' + (co.pct_pendientes || 0) + '%)</span></div>'
                                + '</div>'
                            + '</div>'
                        + '</div>'
                        + '<div class="small text-muted mb-1">Distribución de nacimiento</div>'
                        + '<div class="row g-2">' + (distribHtml || '<div class="col-12 text-center text-muted small">Sin distribución</div>') + '</div>'
                        + '<div class="mt-2 small text-muted">'
                            + 'Recuperación de 1–7d al corte: <span class="badge bg-label-info">' + (rec.pct_sobre_1_7 || 0) + '%</span>'
                        + '</div>'
                    + '</div>'
                + '</div>'
            + '</div>';
        });
        wrap.innerHTML = html;
    }

    function renderChartComportamiento(semanas) {
        if (typeof Chart === 'undefined') return;
        var canvas = document.getElementById('pphChartComportamiento');
        if (!canvas) return;
        destroyChart('comp');
        var tipo = (document.getElementById('pphTipoGraficaComportamiento') || {}).value || 'dual_stack';
        var visibles = semanasVisibles(semanas);
        var ds = datosSeriesSemanas(visibles);
        if (!ds.labels.length) return;

        var datasets;
        var opts = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index', intersect: false } },
            interaction: { mode: 'index', intersect: false }
        };

        if (tipo === 'nacimiento_5') {
            var pal = NAC_COLORS;
            datasets = BUCKET_ORDER.map(function (b, i) {
                return {
                    label: (BUCKET_META[b] && BUCKET_META[b].short) ? BUCKET_META[b].short : b,
                    data: visibles.map(function (s) {
                        var nd = (s.nacimiento && s.nacimiento.nac_dist) ? s.nacimiento.nac_dist : {};
                        return parseInt(nd[b], 10) || 0;
                    }),
                    backgroundColor: pal[i % pal.length],
                    stack: 'nac5'
                };
            });
            opts.scales = { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } };
            pphCharts.comp = new Chart(canvas, { type: 'bar', data: { labels: ds.labels, datasets: datasets }, options: opts });
            return;
        }

        if (tipo === 'line') {
            datasets = [
                { label: 'Nacieron Current', data: ds.nacCur, borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,.12)', tension: 0.25, fill: false },
                { label: 'Nacieron 1–7d', data: ds.nac17, borderColor: '#ff3e1d', backgroundColor: 'rgba(255,62,29,.08)', tension: 0.25, fill: false },
                { label: 'Cierre semana', data: ds.curCorte, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.08)', tension: 0.25, fill: false },
                { label: 'cierre 1 7d', data: ds.pend, borderColor: '#ffab00', backgroundColor: 'rgba(255,171,0,.12)', tension: 0.25, fill: false }
            ];
            opts.scales = { y: { beginAtZero: true, ticks: { precision: 0 } } };
            pphCharts.comp = new Chart(canvas, { type: 'line', data: { labels: ds.labels, datasets: datasets }, options: opts });
            return;
        }

        if (tipo === 'grouped') {
            datasets = [
                { label: 'Nacieron Current', data: ds.nacCur, backgroundColor: '#28a745' },
                { label: 'Nacieron 1–7d', data: ds.nac17, backgroundColor: '#ff3e1d' },
                { label: 'Cierre semana', data: ds.curCorte, backgroundColor: '#0d6efd' },
                { label: 'cierre 1 7d', data: ds.pend, backgroundColor: '#ffab00' }
            ];
            opts.scales = { x: { stacked: false }, y: { stacked: false, beginAtZero: true, ticks: { precision: 0 } } };
            pphCharts.comp = new Chart(canvas, { type: 'bar', data: { labels: ds.labels, datasets: datasets }, options: opts });
            return;
        }

        if (tipo === 'full_stack') {
            datasets = [
                { label: 'Nacieron Current', data: ds.nacCur, backgroundColor: '#28a745', stack: 'uno' },
                { label: 'Nacieron 1–7d', data: ds.nac17, backgroundColor: '#ff3e1d', stack: 'uno' },
                { label: 'Cierre semana', data: ds.curCorte, backgroundColor: '#0d6efd', stack: 'uno' },
                { label: 'cierre 1 7d', data: ds.pend, backgroundColor: '#ffab00', stack: 'uno' }
            ];
            opts.scales = { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } };
            pphCharts.comp = new Chart(canvas, { type: 'bar', data: { labels: ds.labels, datasets: datasets }, options: opts });
            return;
        }

        datasets = [
            { label: 'Nacieron Current', data: ds.nacCur, backgroundColor: '#28a745', stack: 'nacimiento' },
            { label: 'Nacieron 1–7d', data: ds.nac17, backgroundColor: '#ff3e1d', stack: 'nacimiento' },
            { label: 'Cierre semana', data: ds.curCorte, backgroundColor: '#0d6efd', stack: 'corte' },
            { label: 'cierre 1 7d', data: ds.pend, backgroundColor: '#ffab00', stack: 'corte' }
        ];
        opts.scales = { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } };
        pphCharts.comp = new Chart(canvas, { type: 'bar', data: { labels: ds.labels, datasets: datasets }, options: opts });
    }

    function esTerritorialPlaceholder(name) {
        var t = String(name || '').trim();
        if (!t || t === '(Sin territorial)') return true;
        return /^current$/i.test(t);
    }
    function truncLabel(s, n) {
        s = String(s || '');
        if (s.length <= n) return s;
        return s.substring(0, n - 1) + '…';
    }
    function agregarPorCampo(rows, campo) {
        var m = {};
        (rows || []).forEach(function (r) {
            var k = String(r[campo] != null ? r[campo] : '');
            if (campo === 'Territorial' && esTerritorialPlaceholder(k)) return;
            if (!k) k = '(Sin dato)';
            if (!m[k]) m[k] = { total: 0, cobrados: 0 };
            m[k].total += parseInt(r.total, 10) || 0;
            m[k].cobrados += parseInt(r.cobrados, 10) || 0;
        });
        var list = Object.keys(m).map(function (k) {
            var t = m[k].total;
            var c = m[k].cobrados;
            return {
                name: k,
                total: t,
                cobrados: c,
                pendientes: Math.max(0, t - c),
                ratio: t > 0 ? c / t : 0,
                pct: t > 0 ? Math.round(c / t * 100) : 0
            };
        });
        list.sort(function (a, b) { return a.ratio - b.ratio; });
        return list;
    }

    function semanaSeleccionadaJerarquia() {
        var sel = document.getElementById('pphSemanaJerarquia');
        var v = sel ? String(sel.value || '') : '';
        var list = pphState.semanas || [];
        for (var i = 0; i < list.length; i++) {
            if (list[i].disponible && list[i].semana === v) return list[i];
        }
        return null;
    }

    function poblarSelectJerarquia(semanas) {
        var sel = document.getElementById('pphSemanaJerarquia');
        if (!sel) return;
        var dis = semanas.filter(function (s) { return s.disponible && (s.jerarquia_agregada || []).length; });
        dis.sort(function (a, b) { return (a.ini || '') < (b.ini || '') ? 1 : -1; });
        sel.innerHTML = '';
        if (!dis.length) {
            sel.innerHTML = '<option value="">(sin datos de jerarquía)</option>';
            sel.disabled = true;
            return;
        }
        sel.disabled = false;
        dis.forEach(function (s) {
            var o = document.createElement('option');
            o.value = s.semana;
            o.textContent = s.semana;
            sel.appendChild(o);
        });
        sel.value = dis[0].semana;
    }

    function renderChartJerarquia() {
        if (typeof Chart === 'undefined') return;
        var canvas = document.getElementById('pphChartJerarquia');
        if (!canvas) return;
        destroyChart('jer');
        var tipo = (document.getElementById('pphTipoGraficaJerarquia') || {}).value || 'territorial_stack';
        var s = semanaSeleccionadaJerarquia();
        var rows = (s && s.jerarquia_agregada) ? s.jerarquia_agregada : [];
        if (!rows.length) {
            pphCharts.jer = new Chart(canvas, {
                type: 'bar',
                data: { labels: ['—'], datasets: [{ label: 'Sin datos', data: [0], backgroundColor: '#dee2e6' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
            return;
        }

        var list;
        var labels;
        var opts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index' } } };

        if (tipo === 'gestores_top') {
            list = agregarPorCampo(rows, 'Gestor_Asignado');
            list.sort(function (a, b) { return b.total - a.total; });
            list = list.slice(0, 15);
            labels = list.map(function (x) { return truncLabel(x.name, 22); });
            opts.indexAxis = 'y';
            opts.scales = { x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }, y: { stacked: true } };
            pphCharts.jer = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Cobrados', data: list.map(function (x) { return x.cobrados; }), backgroundColor: '#28a745', stack: 'g' },
                        { label: 'Pendientes', data: list.map(function (x) { return x.pendientes; }), backgroundColor: '#ffab00', stack: 'g' }
                    ]
                },
                options: opts
            });
            return;
        }

        list = agregarPorCampo(rows, 'Territorial');
        if (tipo === 'territorial_pct') {
            labels = list.map(function (x) { return truncLabel(x.name, 20); });
            opts.indexAxis = 'y';
            opts.scales = { x: { max: 100, beginAtZero: true, ticks: { callback: function (v) { return v + '%'; } } }, y: {} };
            var bg = list.map(function (x) {
                if (x.pct >= 70) return '#28a745';
                if (x.pct >= 40) return '#fd7e14';
                return '#dc3545';
            });
            pphCharts.jer = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '% efectividad (cobrados / total)',
                        data: list.map(function (x) { return x.pct; }),
                        backgroundColor: bg
                    }]
                },
                options: opts
            });
            return;
        }

        labels = list.map(function (x) { return truncLabel(x.name, 20); });
        opts.indexAxis = 'y';
        opts.scales = { x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }, y: { stacked: true } };
        pphCharts.jer = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Cobrados', data: list.map(function (x) { return x.cobrados; }), backgroundColor: '#28a745', stack: 't' },
                    { label: 'Pendientes', data: list.map(function (x) { return x.pendientes; }), backgroundColor: '#ffab00', stack: 't' }
                ]
            },
            options: opts
        });
    }

    function fusionarJerarquias(mapa) {
        if (!mapa || typeof mapa !== 'object') return;
        (pphState.semanas || []).forEach(function (s) {
            if (!s.disponible || !s.semana) return;
            if (Object.prototype.hasOwnProperty.call(mapa, s.semana) && Array.isArray(mapa[s.semana])) {
                s.jerarquia_agregada = mapa[s.semana];
            }
        });
    }

    /** Jerarquía en 2.ª petición: el comparativo ya pintó tablas/gráficas; esto solo alimenta la gráfica de jerarquía. */
    function cargarJerarquiasDespues() {
        var claves = (pphState.semanas || []).filter(function (s) { return s.disponible; }).map(function (s) { return s.semana; });
        if (!claves.length) return;
        fetch('/analitica/getPrimerosPagosHistoricoJerarquias', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify({ semanas: claves })
        })
            .then(function (r) {
                return r.text().then(function (t) {
                    var j = null; try { j = JSON.parse(t); } catch (e) { j = null; }
                    return { ok: r.ok, json: j };
                });
            })
            .then(function (w) {
                var resp = w.json;
                if (!w.ok || !resp || !resp.success || !resp.datos) return;
                fusionarJerarquias(resp.datos);
                poblarSelectJerarquia(pphState.semanas);
                renderChartJerarquia();
            })
            .catch(function () {});
    }

    function enlazarControlesGraficas() {
        var sc = document.getElementById('pphTipoGraficaComportamiento');
        if (sc && !sc._pphBound) {
            sc._pphBound = true;
            sc.addEventListener('change', function () { renderChartComportamiento(pphState.semanas); });
        }
        var sj = document.getElementById('pphSemanaJerarquia');
        if (sj && !sj._pphBound) {
            sj._pphBound = true;
            sj.addEventListener('change', function () { renderChartJerarquia(); });
        }
        var tj = document.getElementById('pphTipoGraficaJerarquia');
        if (tj && !tj._pphBound) {
            tj._pphBound = true;
            tj.addEventListener('change', function () { renderChartJerarquia(); });
        }
    }

    function cargarComparativo() {
        fetch('/analitica/getPrimerosPagosHistoricoComparativo', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Front-Request': 'true' }
        })
            .then(function (r) {
                return r.text().then(function (t) {
                    var j = null; try { j = JSON.parse(t); } catch (e) { j = null; }
                    return { ok: r.ok, json: j };
                });
            })
            .then(function (w) {
                var resp = w.json;
                if (!w.ok || !resp || !resp.success || !resp.datos) {
                    var m = (resp && resp.mensaje) ? resp.mensaje : 'No se pudo cargar el comparativo.';
                    showErr(m + (resp && resp.error ? ' (' + resp.error + ')' : ''));
                    return;
                }
                hideErr();
                var semanas = (resp.datos.semanas || []).slice().sort(function (a, b) {
                    return (a.ini || '') < (b.ini || '') ? 1 : -1;
                });
                pphState.semanas = semanas;
                document.getElementById('pphMain').classList.remove('d-none');
                renderTabla(semanas);
                renderCards(semanas);
                enlazarControlesGraficas();
                renderChartComportamiento(semanas);
                poblarSelectJerarquia(semanas);
                renderChartJerarquia();
                cargarJerarquiasDespues();
            })
            .catch(function () {
                showErr('Error de red al consultar el comparativo.');
            })
            .finally(function () {
                if (typeof Swal !== 'undefined' && typeof Swal.close === 'function') {
                    try { Swal.close(); } catch (eSw) {}
                }
            });
    }

    if (typeof showWait === 'function') {
        showWait();
    } else if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
        Swal.fire({
            title: 'Procesando su petición',
            text: 'Espere un momento...',
            imageUrl: '/assets/img/wait.svg',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
    }

    cargarComparativo();
});
</script>
